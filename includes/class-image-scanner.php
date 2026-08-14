<?php
namespace MRISiteOptimizer;

if ( ! defined( 'ABSPATH' ) ) exit;

class Image_Scanner {

	public function __construct() {
		add_action( 'mri_siteoptimizer_scheduled_scan', [ $this, 'run_scan' ] );
	}

	/**
	 * Get all attachment IDs that are not referenced anywhere in post content,
	 * post meta, widget options, customizer settings, or as featured images.
	 *
	 * @return array List of attachment IDs that appear unlinked.
	 */
	public function get_unlinked_images() {
		global $wpdb;

		// All image attachments
		$all_ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts}
			 WHERE post_type = 'attachment'
			   AND post_mime_type LIKE %s
			   AND post_status = 'inherit'",
			'image/%'
		) );

		if ( empty( $all_ids ) ) return [];

		$used_ids = [];

		// 1. Attached to a parent post (post_parent set)
		$parent_attached = $wpdb->get_col(
			"SELECT ID FROM {$wpdb->posts}
			 WHERE post_type = 'attachment'
			   AND post_parent > 0"
		);
		$used_ids = array_merge( $used_ids, $parent_attached );

		// 2. Featured images (post thumbnails)
		$thumbnails = $wpdb->get_col(
			"SELECT meta_value FROM {$wpdb->postmeta}
			 WHERE meta_key = '_thumbnail_id'"
		);
		$used_ids = array_merge( $used_ids, array_map( 'intval', $thumbnails ) );

		// 3. Referenced in post content (src in img tags, shortcodes, Gutenberg blocks)
		$post_content_rows = $wpdb->get_results(
			"SELECT ID, post_content FROM {$wpdb->posts}
			 WHERE post_status NOT IN ('trash','auto-draft')
			   AND post_type NOT IN ('attachment','revision')"
		);

		foreach ( $post_content_rows as $row ) {
			if ( empty( $row->post_content ) ) continue;
			// Extract all attachment IDs from wp-image-{id} classes and data-id attributes
			if ( preg_match_all( '/wp-image-(\d+)/i', $row->post_content, $m ) ) {
				$used_ids = array_merge( $used_ids, array_map( 'intval', $m[1] ) );
			}
			if ( preg_match_all( '/"id"\s*:\s*(\d+)/i', $row->post_content, $m ) ) {
				$used_ids = array_merge( $used_ids, array_map( 'intval', $m[1] ) );
			}
		}

		// 4. Post meta (gallery fields, ACF image fields, etc.)
		$meta_values = $wpdb->get_col( $wpdb->prepare(
			"SELECT meta_value FROM {$wpdb->postmeta}
			 WHERE meta_key NOT LIKE %s
			   AND meta_value REGEXP %s",
			'\_%',
			'^[0-9]+$'
		) );
		$used_ids = array_merge( $used_ids, array_map( 'intval', $meta_values ) );

		// 5. Widget options
		$widget_opts = $wpdb->get_col( $wpdb->prepare(
			"SELECT option_value FROM {$wpdb->options}
			 WHERE option_name LIKE %s",
			'widget_%'
		) );
		foreach ( $widget_opts as $opt ) {
			if ( preg_match_all( '/"attachment_id"\s*:\s*(\d+)/', $opt, $m ) ) {
				$used_ids = array_merge( $used_ids, array_map( 'intval', $m[1] ) );
			}
		}

		$used_ids  = array_unique( array_map( 'intval', $used_ids ) );
		$unlinked  = array_diff( array_map( 'intval', $all_ids ), $used_ids );

		return array_values( $unlinked );
	}

	/**
	 * Returns detailed info for a list of attachment IDs.
	 *
	 * @param array $ids
	 * @return array
	 */
	public function get_attachment_details( array $ids ) {
		if ( empty( $ids ) ) return [];

		$results = [];
		foreach ( $ids as $id ) {
			$file = get_attached_file( $id );
			$results[] = [
				'id'        => $id,
				'title'     => get_the_title( $id ),
				'url'       => wp_get_attachment_url( $id ),
				'file'      => $file,
				'size'      => $file && file_exists( $file ) ? filesize( $file ) : 0,
				'mime'      => get_post_mime_type( $id ),
				'date'      => get_the_date( 'Y-m-d', $id ),
				'thumbnail' => wp_get_attachment_image_url( $id, 'thumbnail' ),
			];
		}
		return $results;
	}

	/**
	 * Delete or trash a list of attachment IDs.
	 *
	 * @param array $ids
	 * @param bool  $force_delete  If false, moves to trash.
	 * @return array Results summary.
	 */
	public function delete_attachments( array $ids, $force_delete = false ) {
		$deleted   = 0;
		$failed    = 0;
		$bytes     = 0;

		foreach ( $ids as $id ) {
			$file = get_attached_file( $id );
			$size = ( $file && file_exists( $file ) ) ? filesize( $file ) : 0;

			$result = wp_delete_attachment( (int) $id, $force_delete );
			if ( $result ) {
				$deleted++;
				$bytes += $size;
			} else {
				$failed++;
			}
		}

	self::log( 'image_delete', sprintf(
		/* translators: %1$d is the number of images, %2$s is the space freed */
		__( 'Deleted %1$d unlinked images, freed %2$s.', 'mri-siteoptimizer' ),
		$deleted, size_format( $bytes )
	), $bytes );

	return compact( 'deleted', 'failed', 'bytes' );
		self::log( 'scan', sprintf(
			/* translators: %d is the number of unlinked images found */
			__( 'Scheduled scan: found %d unlinked images.', 'mri-siteoptimizer' ),
			count( $unlinked )
		) );
	}

	// -----------------------------------------------------------------------

	public static function log( $type, $message, $bytes = 0 ) {
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'mri_siteoptimizer_log',
			[
				'action_type' => sanitize_key( $type ),
				'message'     => sanitize_text_field( $message ),
				'saved_bytes' => (int) $bytes,
				'created_at'  => current_time( 'mysql' ),
			],
			[ '%s', '%s', '%d', '%s' ]
		);
	}
}
