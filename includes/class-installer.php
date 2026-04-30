<?php
namespace MRISiteOptimizer;

if ( ! defined( 'ABSPATH' ) ) exit;

class Installer {

	public static function activate() {
		$defaults = [
			'auto_compress_upload'  => '1',
			'lazy_load'             => '1',
			'compression_quality'   => '82',
			'compression_type'      => 'lossy',
			'scan_schedule'         => 'weekly',
			'trash_before_delete'   => '1',
			'remove_temp_files'     => '1',
			'remove_unused_sizes'   => '1',
			'db_revisions'          => '1',
			'db_auto_drafts'        => '1',
			'db_trash_posts'        => '1',
			'db_spam_comments'      => '1',
			'db_trash_comments'     => '1',
			'db_transients'         => '1',
		];

		foreach ( $defaults as $key => $val ) {
			if ( get_option( 'mri_siteoptimizer_' . $key ) === false ) {
				update_option( 'mri_siteoptimizer_' . $key, $val );
			}
		}

		global $wpdb;
		$table   = $wpdb->prefix . 'mri_siteoptimizer_log';
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table} (
			id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			action_type VARCHAR(50)     NOT NULL,
			message     TEXT            NOT NULL,
			saved_bytes BIGINT          DEFAULT 0,
			created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'mri_siteoptimizer_version', MRI_SITEOPTIMIZER_VERSION );
		update_option( 'mri_siteoptimizer_activated_at', current_time( 'mysql' ) );
	}

	public static function deactivate() {
		wp_clear_scheduled_hook( 'mri_siteoptimizer_scheduled_scan' );
		wp_clear_scheduled_hook( 'mri_siteoptimizer_scheduled_cleanup' );
	}

	public static function uninstall() {
		global $wpdb;
		$table = $wpdb->prefix . 'mri_siteoptimizer_log';

		if ( ! preg_match( '/^[A-Za-z0-9_]+$/', $table ) ) {
			return;
		}

		$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" );

		$options = $wpdb->get_col( $wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
			'mri_siteoptimizer_%'
		) );
		foreach ( $options as $opt ) {
			delete_option( $opt );
		}
	}
}
