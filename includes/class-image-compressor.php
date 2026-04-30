<?php
namespace MRISiteOptimizer;

if ( ! defined( 'ABSPATH' ) ) exit;

class Image_Compressor {

	public function __construct() {
		// Auto-compress on upload
		if ( get_option( 'mri_siteoptimizer_auto_compress_upload', '1' ) ) {
			add_filter( 'wp_handle_upload', [ $this, 'compress_on_upload' ] );
		}

		// Lazy load images
		if ( get_option( 'mri_siteoptimizer_lazy_load', '1' ) ) {
			add_filter( 'the_content',          [ $this, 'add_lazy_load' ] );
			add_filter( 'post_thumbnail_html',  [ $this, 'add_lazy_load' ] );
			add_filter( 'widget_text',          [ $this, 'add_lazy_load' ] );
		}
	}

	// -----------------------------------------------------------------------
	// Compression
	// -----------------------------------------------------------------------

	/**
	 * Compress a single image file in place.
	 *
	 * @param string $file_path  Absolute path to the image.
	 * @param int    $quality    JPEG/WebP quality 1–100.
	 * @param string $type       'lossy' or 'lossless'.
	 * @return array  [ 'original_size', 'new_size', 'saved', 'success' ]
	 */
	public function compress_image( $file_path, $quality = null, $type = null ) {
		if ( ! file_exists( $file_path ) ) {
			return [ 'success' => false, 'message' => __( 'File not found.', 'mri-siteoptimizer' ) ];
		}

		$quality  = $quality ?? (int) get_option( 'mri_siteoptimizer_compression_quality', 82 );
		$type     = $type    ?? get_option( 'mri_siteoptimizer_compression_type', 'lossy' );
		$original = filesize( $file_path );
		$mime     = mime_content_type( $file_path );

		// Use WordPress image editor
		$editor = wp_get_image_editor( $file_path );
		if ( is_wp_error( $editor ) ) {
			return [ 'success' => false, 'message' => $editor->get_error_message() ];
		}

		if ( $type === 'lossless' ) {
			// For lossless we still use WP editor but at max quality
			$quality = 100;
		}

		$editor->set_quality( $quality );
		$saved = $editor->save( $file_path );

		if ( is_wp_error( $saved ) ) {
			return [ 'success' => false, 'message' => $saved->get_error_message() ];
		}

		$new_size = filesize( $file_path );
		$diff     = max( 0, $original - $new_size );

		return [
			'success'       => true,
			'original_size' => $original,
			'new_size'      => $new_size,
			'saved'         => $diff,
		];
	}

	/**
	 * Hook: compress image right after upload.
	 */
	public function compress_on_upload( $upload ) {
		if ( strpos( $upload['type'], 'image/' ) === false ) {
			return $upload;
		}

		$result = $this->compress_image( $upload['file'] );

		if ( ! empty( $result['saved'] ) && $result['saved'] > 0 ) {
			Image_Scanner::log( 'compress_upload', sprintf(
				/* translators: %s is the amount of space freed */
				__( 'Compressed uploaded image, saved %s.', 'mri-siteoptimizer' ),
				size_format( $result['saved'] )
			), $result['saved'] );
		}

		return $upload;
	}

	/**
	 * Bulk compress all images in the media library.
	 *
	 * @param int $batch  Maximum number of images to process.
	 * @return array
	 */
	public function bulk_compress( $batch = 50 ) {
		global $wpdb;
		$ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts}
			 WHERE post_type = 'attachment'
			   AND post_mime_type LIKE %s
			   AND ID NOT IN (
				   SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s
			   )
			 LIMIT %d",
			'image/%', '_mri_siteoptimizer_compressed', $batch
		) );

		$processed = 0;
		$saved     = 0;

		foreach ( $ids as $id ) {
			$file   = get_attached_file( $id );
			if ( ! $file ) continue;

			$result = $this->compress_image( $file );
			if ( $result['success'] ) {
				update_post_meta( $id, '_mri_siteoptimizer_compressed', time() );
				$processed++;
				$saved += $result['saved'];
			}

			// Also compress all generated sizes
			$meta = wp_get_attachment_metadata( $id );
			if ( ! empty( $meta['sizes'] ) ) {
				$dir = trailingslashit( dirname( $file ) );
				foreach ( $meta['sizes'] as $size ) {
					$this->compress_image( $dir . $size['file'] );
				}
			}
		}

		Image_Scanner::log( 'bulk_compress', sprintf(
			/* translators: %1$d is the number of images, %2$s is the space freed */
			__( 'Bulk compressed %1$d images, saved %2$s.', 'mri-siteoptimizer' ),
			$processed, size_format( $saved )
		), $saved );

		return [
			'processed' => $processed,
			'saved'     => $saved,
			'remaining' => max( 0, count( $ids ) - $processed ),
		];
	}

	// -----------------------------------------------------------------------
	// Lazy loading
	// -----------------------------------------------------------------------

	/**
	 * Add loading="lazy" to all <img> tags in content.
	 *
	 * @param string $content
	 * @return string
	 */
	public function add_lazy_load( $content ) {
		if ( empty( $content ) || is_admin() ) return $content;

		return preg_replace_callback(
			'/<img([^>]+)>/i',
			function( $matches ) {
				$tag = $matches[0];

				// Already has loading attribute
				if ( stripos( $tag, 'loading=' ) !== false ) return $tag;

				// Skip images with data-no-lazy
				if ( stripos( $tag, 'data-no-lazy' ) !== false ) return $tag;

				// Insert loading="lazy" before the closing >
				return str_replace( '<img', '<img loading="lazy"', $tag );
			},
			$content
		);
	}

	// -----------------------------------------------------------------------
	// Restore original (remove compression meta so next bulk re-processes)
	// -----------------------------------------------------------------------

	public function reset_attachment( $attachment_id ) {
		delete_post_meta( (int) $attachment_id, '_mri_siteoptimizer_compressed' );
	}
}
