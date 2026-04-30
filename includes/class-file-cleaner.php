<?php
namespace MRISiteOptimizer;

if ( ! defined( 'ABSPATH' ) ) exit;

class File_Cleaner {

	/** Extensions treated as temporary / junk. */
	const JUNK_EXTENSIONS = [ 'tmp', 'log', 'bak', 'old', 'orig', 'swp', '~' ];

	public function __construct() {
		add_action( 'mri_siteoptimizer_scheduled_cleanup', [ $this, 'run_cleanup' ] );
	}

	// -----------------------------------------------------------------------
	// 1. Orphaned files in uploads
	// -----------------------------------------------------------------------

	/**
	 * Find files in the uploads directory that have no matching attachment record.
	 *
	 * @return array  [ ['path'=>..., 'size'=>..., 'url'=>...], ... ]
	 */
	public function get_orphaned_files() {
		global $wpdb;

		$upload_dir  = wp_upload_dir();
		$base_dir    = $upload_dir['basedir'];

		// All files stored as attachments
		$registered = $wpdb->get_col( $wpdb->prepare(
			"SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
			'_wp_attached_file'
		) );
		$registered_set = array_flip( $registered );

		$orphans = [];

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $base_dir, \FilesystemIterator::SKIP_DOTS )
		);

		foreach ( $iterator as $file ) {
			if ( ! $file->isFile() ) continue;

			$path     = $file->getRealPath();
			$relative = ltrim( str_replace( $base_dir, '', $path ), DIRECTORY_SEPARATOR . '/' );

			// Skip thumbnails (contain -widthxheight before extension)
			if ( preg_match( '/-\d+x\d+\.[a-z]+$/i', $relative ) ) continue;

			if ( ! isset( $registered_set[ $relative ] ) ) {
				$orphans[] = [
					'path' => $path,
					'size' => $file->getSize(),
					'url'  => $upload_dir['baseurl'] . '/' . $relative,
					'rel'  => $relative,
				];
			}
		}

		return $orphans;
	}

	// -----------------------------------------------------------------------
	// 2. Junk / temp files
	// -----------------------------------------------------------------------

	/**
	 * Scan the uploads folder (and optionally ABSPATH) for junk files.
	 *
	 * @param bool $include_root  Also scan WordPress root.
	 * @return array
	 */
	public function get_junk_files( $include_root = false ) {
		$dirs = [ wp_upload_dir()['basedir'] ];
		if ( $include_root ) {
			$dirs[] = ABSPATH;
		}

		$junk = [];

		foreach ( $dirs as $dir ) {
			if ( ! is_dir( $dir ) ) continue;

			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS )
			);

			foreach ( $iterator as $file ) {
				if ( ! $file->isFile() ) continue;

				$ext = strtolower( $file->getExtension() );
				if ( in_array( $ext, self::JUNK_EXTENSIONS, true ) ) {
					$junk[] = [
						'path' => $file->getRealPath(),
						'size' => $file->getSize(),
						'ext'  => $ext,
					];
				}
			}
		}

		return $junk;
	}

	// -----------------------------------------------------------------------
	// 3. Unused image sizes
	// -----------------------------------------------------------------------

	/**
	 * Detect registered image sizes that are not referenced in any post content
	 * and delete their thumbnail files.
	 *
	 * @return array  summary
	 */
	public function remove_unused_image_size_files() {
		global $wpdb;
		$upload_dir = wp_upload_dir();
		$base_dir   = $upload_dir['basedir'];

		// Collect all thumbnail paths from attachment meta
		$meta_rows = $wpdb->get_col( $wpdb->prepare(
			"SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
			'_wp_attachment_metadata'
		) );

		$deleted = 0;
		$bytes   = 0;

		foreach ( $meta_rows as $serialized ) {
			$meta = maybe_unserialize( $serialized );
			if ( empty( $meta['sizes'] ) || empty( $meta['file'] ) ) continue;

			$sub_dir = trailingslashit( $base_dir . '/' . dirname( $meta['file'] ) );

			foreach ( $meta['sizes'] as $size_name => $size_data ) {
				// Keep sizes that are still registered
				if ( has_image_size( $size_name ) ) continue;

				$thumb_path = $sub_dir . $size_data['file'];
				if ( file_exists( $thumb_path ) ) {
					$bytes += filesize( $thumb_path );
					wp_delete_file( $thumb_path );
					$deleted++;
				}
			}
		}

		Image_Scanner::log( 'unused_sizes', sprintf(
			/* translators: %1$d is the number of files, %2$s is the space freed */
			__( 'Removed %1$d unused image size files, freed %2$s.', 'mri-siteoptimizer' ),
			$deleted, size_format( $bytes )
		), $bytes );

		return compact( 'deleted', 'bytes' );
	}

	// -----------------------------------------------------------------------
	// 4. Delete files
	// -----------------------------------------------------------------------

	/**
	 * Delete a list of absolute file paths.
	 *
	 * @param array $paths
	 * @return array
	 */
	public function delete_files( array $paths ) {
		$deleted = 0;
		$failed  = 0;
		$bytes   = 0;

		foreach ( $paths as $path ) {
			$path = realpath( $path );
			if ( ! $path ) { $failed++; continue; }

			// Safety: must be inside uploads or WP root
			$upload_base = wp_upload_dir()['basedir'];
			$is_safe     = ( strpos( $path, $upload_base ) === 0 )
						|| ( strpos( $path, ABSPATH ) === 0 );

			if ( ! $is_safe ) { $failed++; continue; }

			$size = filesize( $path );
			if ( wp_delete_file( $path ) || ! file_exists( $path ) ) {
				$deleted++;
				$bytes += $size;
			} else {
				$failed++;
			}
		}

		Image_Scanner::log( 'file_delete', sprintf(
			/* translators: %1$d is the number of files, %2$s is the space freed */
			__( 'Deleted %1$d files, freed %2$s.', 'mri-siteoptimizer' ),
			$deleted, size_format( $bytes )
		), $bytes );

		return compact( 'deleted', 'failed', 'bytes' );
	}

	// -----------------------------------------------------------------------
	// 5. Scheduled cleanup runner
	// -----------------------------------------------------------------------

	public function run_cleanup() {
		if ( get_option( 'mri_siteoptimizer_remove_temp_files' ) ) {
			$junk = $this->get_junk_files();
			$this->delete_files( array_column( $junk, 'path' ) );
		}

		if ( get_option( 'mri_siteoptimizer_remove_unused_sizes' ) ) {
			$this->remove_unused_image_size_files();
		}
	}
}
