<?php
namespace MRISiteOptimizer;

if ( ! defined( 'ABSPATH' ) ) exit;

class Ajax_Handler {

	public function __construct() {
		$actions = [
			'mri_siteoptimizer_scan_images',
			'mri_siteoptimizer_delete_images',
			'mri_siteoptimizer_scan_orphans',
			'mri_siteoptimizer_delete_orphans',
			'mri_siteoptimizer_scan_junk',
			'mri_siteoptimizer_delete_junk',
			'mri_siteoptimizer_bulk_compress',
			'mri_siteoptimizer_db_stats',
			'mri_siteoptimizer_db_optimize',
			'mri_siteoptimizer_get_log',
			'mri_siteoptimizer_save_settings',
			'mri_siteoptimizer_get_dashboard_stats',
			'mri_siteoptimizer_run_full_scan',
		];

		foreach ( $actions as $action ) {
			add_action( 'wp_ajax_' . $action, [ $this, str_replace( 'mri_siteoptimizer_', 'handle_', $action ) ] );
		}
	}

	private function verify() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized.', 'mri-siteoptimizer' ) ], 403 );
		}
	}

	// -----------------------------------------------------------------------

	public function handle_scan_images() {
		check_ajax_referer( 'mri_siteoptimizer_nonce', 'nonce' );
		$this->verify();
		$scanner  = new Image_Scanner();
		$ids      = $scanner->get_unlinked_images();
		$details  = $scanner->get_attachment_details( $ids );
		wp_send_json_success( [
			'count'   => count( $ids ),
			'items'   => $details,
			'total_size' => array_sum( array_column( $details, 'size' ) ),
		] );
	}

	public function handle_delete_images() {
		check_ajax_referer( 'mri_siteoptimizer_nonce', 'nonce' );
		$this->verify();
		$ids   = array_map( 'intval', (array) ( isset( $_POST['ids'] ) ? wp_unslash( $_POST['ids'] ) : [] ) );
		$force = ! get_option( 'mri_siteoptimizer_trash_before_delete' );

		$scanner = new Image_Scanner();
		$result  = $scanner->delete_attachments( $ids, $force );
		wp_send_json_success( $result );
	}

	public function handle_scan_orphans() {
		check_ajax_referer( 'mri_siteoptimizer_nonce', 'nonce' );
		$this->verify();
		$cleaner = new File_Cleaner();
		$items   = $cleaner->get_orphaned_files();
		wp_send_json_success( [
			'count'      => count( $items ),
			'items'      => $items,
			'total_size' => array_sum( array_column( $items, 'size' ) ),
		] );
	}

	public function handle_delete_orphans() {
		check_ajax_referer( 'mri_siteoptimizer_nonce', 'nonce' );
		$this->verify();
		$paths   = array_map( 'sanitize_text_field', (array) ( isset( $_POST['paths'] ) ? wp_unslash( $_POST['paths'] ) : [] ) );
		$cleaner = new File_Cleaner();
		wp_send_json_success( $cleaner->delete_files( $paths ) );
	}

	public function handle_scan_junk() {
		check_ajax_referer( 'mri_siteoptimizer_nonce', 'nonce' );
		$this->verify();
		$cleaner = new File_Cleaner();
		$items   = $cleaner->get_junk_files();
		wp_send_json_success( [
			'count'      => count( $items ),
			'items'      => $items,
			'total_size' => array_sum( array_column( $items, 'size' ) ),
		] );
	}

	public function handle_delete_junk() {
		check_ajax_referer( 'mri_siteoptimizer_nonce', 'nonce' );
		$this->verify();
		$paths   = array_map( 'sanitize_text_field', (array) ( isset( $_POST['paths'] ) ? wp_unslash( $_POST['paths'] ) : [] ) );
		$cleaner = new File_Cleaner();
		wp_send_json_success( $cleaner->delete_files( $paths ) );
	}

	public function handle_bulk_compress() {
		check_ajax_referer( 'mri_siteoptimizer_nonce', 'nonce' );
		$this->verify();
		$batch      = (int) ( isset( $_POST['batch'] ) ? intval( wp_unslash( $_POST['batch'] ) ) : 50 );
		$compressor = new Image_Compressor();
		wp_send_json_success( $compressor->bulk_compress( $batch ) );
	}

	public function handle_db_stats() {
		check_ajax_referer( 'mri_siteoptimizer_nonce', 'nonce' );
		$this->verify();
		$db = new DB_Optimizer();
		wp_send_json_success( $db->get_stats() );
	}

	public function handle_db_optimize() {
		check_ajax_referer( 'mri_siteoptimizer_nonce', 'nonce' );
		$this->verify();
		$db = new DB_Optimizer();
		$db->run_optimization();
		wp_send_json_success( [ 'message' => __( 'Database optimized successfully.', 'mri-siteoptimizer' ) ] );
	}

	public function handle_get_log() {
		check_ajax_referer( 'mri_siteoptimizer_nonce', 'nonce' );
		$this->verify();
		global $wpdb;
		$limit = (int) ( isset( $_POST['limit'] ) ? intval( wp_unslash( $_POST['limit'] ) ) : 20 );
		$rows  = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}mri_siteoptimizer_log ORDER BY id DESC LIMIT %d",
			$limit
		), ARRAY_A );
		wp_send_json_success( $rows );
	}

	public function handle_save_settings() {
		check_ajax_referer( 'mri_siteoptimizer_nonce', 'nonce' );
		$this->verify();

		$fields = [
			'auto_compress_upload', 'lazy_load', 'compression_quality',
			'compression_type', 'scan_schedule', 'trash_before_delete',
			'remove_temp_files', 'remove_unused_sizes',
			'db_revisions', 'db_auto_drafts', 'db_trash_posts',
			'db_spam_comments', 'db_trash_comments', 'db_transients',
		];

		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_option( 'mri_siteoptimizer_' . $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
			}
		}

		wp_send_json_success( [ 'message' => __( 'Settings saved.', 'mri-siteoptimizer' ) ] );
	}

	public function handle_get_dashboard_stats() {
		check_ajax_referer( 'mri_siteoptimizer_nonce', 'nonce' );
		$this->verify();

		global $wpdb;

		$scanner  = new Image_Scanner();
		$unlinked = $scanner->get_unlinked_images();

		$cleaner  = new File_Cleaner();
		$orphans  = $cleaner->get_orphaned_files();
		$junk     = $cleaner->get_junk_files();

		$db       = new DB_Optimizer();
		$db_stats = $db->get_stats();

		$total_revisions = $db_stats['revisions']
			+ $db_stats['auto_drafts']
			+ $db_stats['trash_posts']
			+ $db_stats['spam_comments']
			+ $db_stats['trash_comments']
			+ $db_stats['expired_transients'];

		$log_rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}mri_siteoptimizer_log ORDER BY id DESC LIMIT %d",
			10
		), ARRAY_A );

		$total_saved = (int) $wpdb->get_var(
			"SELECT SUM(saved_bytes) FROM {$wpdb->prefix}mri_siteoptimizer_log"
		);

		wp_send_json_success( [
			'unlinked_count'  => count( $unlinked ),
			'unlinked_size'   => array_sum( array_column( $scanner->get_attachment_details( array_slice( $unlinked, 0, 100 ) ), 'size' ) ),
			'orphan_count'    => count( $orphans ),
			'orphan_size'     => array_sum( array_column( $orphans, 'size' ) ),
			'junk_count'      => count( $junk ),
			'junk_size'       => array_sum( array_column( $junk, 'size' ) ),
			'db_items'        => $total_revisions,
			'db_stats'        => $db_stats,
			'total_saved'     => $total_saved,
			'last_scan'       => get_option( 'mri_siteoptimizer_last_scan_time', '' ),
			'activity'        => $log_rows,
		] );
	}

	public function handle_run_full_scan() {
		check_ajax_referer( 'mri_siteoptimizer_nonce', 'nonce' );
		$this->verify();
		$scanner = new Image_Scanner();
		$scanner->run_scan();
		$cleaner = new File_Cleaner();
		$cleaner->run_cleanup();
		wp_send_json_success( [ 'message' => __( 'Full scan and cleanup complete.', 'mri-siteoptimizer' ) ] );
	}
}
