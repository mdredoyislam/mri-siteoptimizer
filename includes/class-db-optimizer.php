<?php
namespace MRISiteOptimizer;

if ( ! defined( 'ABSPATH' ) ) exit;

class DB_Optimizer {

	public function __construct() {
		add_action( 'mri_siteoptimizer_scheduled_cleanup', [ $this, 'run_optimization' ] );
	}

	// -----------------------------------------------------------------------
	// Stat preview — rows to be removed
	// -----------------------------------------------------------------------

	public function get_stats() {
		global $wpdb;

		return [
			'revisions'        => (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'revision'
			) ),
			'auto_drafts'      => (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = %s",
				'auto-draft'
			) ),
			'trash_posts'      => (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = %s",
				'trash'
			) ),
			'spam_comments'    => (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = %s",
				'spam'
			) ),
			'trash_comments'   => (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = %s",
				'trash'
			) ),
			'expired_transients' => $this->count_expired_transients(),
			'orphan_postmeta'  => (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
				 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				 WHERE p.ID IS NULL"
			) ),
		];
	}

	private function count_expired_transients() {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->options}
			 WHERE option_name LIKE %s
			   AND option_value < %d",
			'_transient_timeout_%',
			time()
		) );
	}

	// -----------------------------------------------------------------------
	// Individual cleaners
	// -----------------------------------------------------------------------

	public function delete_revisions( $keep = 0 ) {
		global $wpdb;

		if ( $keep > 0 ) {
			// Keep the N most recent revisions per post
			$parent_ids = $wpdb->get_col( $wpdb->prepare(
				"SELECT DISTINCT post_parent FROM {$wpdb->posts} WHERE post_type = %s",
				'revision'
			) );
			$deleted = 0;
			foreach ( $parent_ids as $pid ) {
				$ids_to_keep = $wpdb->get_col( $wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts}
					 WHERE post_parent = %d AND post_type = 'revision'
					 ORDER BY post_modified DESC LIMIT %d",
					$pid, $keep
				) );

				if ( empty( $ids_to_keep ) ) continue;

$ids_to_keep_safe = array_map( 'absint', $ids_to_keep );
			$ids_to_keep_safe = array_unique( $ids_to_keep_safe );

			$ids_in_parent = $wpdb->get_col( $wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts}
				 WHERE post_parent = %d AND post_type = 'revision'",
				$pid
			) );

			$ids_to_delete = array_diff( $ids_in_parent, $ids_to_keep_safe );

				foreach ( $ids_to_delete as $rid ) {
					wp_delete_post_revision( (int) $rid );
					$deleted++;
				}
			}
		} else {
			$revisions = $wpdb->get_col(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'revision'"
			);
			$deleted = 0;
			foreach ( $revisions as $rid ) {
				wp_delete_post_revision( (int) $rid );
				$deleted++;
			}
		}

		return $deleted;
	}

	public function delete_auto_drafts() {
		global $wpdb;
		$ids = $wpdb->get_col(
			"SELECT ID FROM {$wpdb->posts} WHERE post_status = 'auto-draft'"
		);
		foreach ( $ids as $id ) {
			wp_delete_post( (int) $id, true );
		}
		return count( $ids );
	}

	public function delete_trash_posts() {
		global $wpdb;
		$ids = $wpdb->get_col(
			"SELECT ID FROM {$wpdb->posts} WHERE post_status = 'trash'"
		);
		foreach ( $ids as $id ) {
			wp_delete_post( (int) $id, true );
		}
		return count( $ids );
	}

	public function delete_spam_comments() {
		global $wpdb;
		return (int) $wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->comments} WHERE comment_approved = %s",
			'spam'
		) );
	}

	public function delete_trash_comments() {
		global $wpdb;
		return (int) $wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->comments} WHERE comment_approved = %s",
			'trash'
		) );
	}

	public function delete_expired_transients() {
		global $wpdb;
		$expired_keys = $wpdb->get_col( $wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options}
			 WHERE option_name LIKE %s AND option_value < %d",
			'_transient_timeout_%',
			time()
		) );

		$deleted = 0;
		foreach ( $expired_keys as $timeout_key ) {
			$transient_key = str_replace( '_transient_timeout_', '_transient_', $timeout_key );
			delete_option( $timeout_key );
			delete_option( $transient_key );
			$deleted++;
		}
		return $deleted;
	}

	public function delete_orphan_postmeta() {
		global $wpdb;
		return (int) $wpdb->query( $wpdb->prepare(
			"DELETE pm FROM {$wpdb->postmeta} pm
			 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			 WHERE p.ID IS NULL"
		) );
	}

	public function optimize_tables() {
		global $wpdb;
		$table = $wpdb->prefix . 'mri_siteoptimizer_log';

		if ( ! preg_match( '/^[A-Za-z0-9_]+$/', $table ) ) {
			return;
		}

		$wpdb->query( "OPTIMIZE TABLE `{$table}`" );
	}

	// -----------------------------------------------------------------------
	// Run all enabled optimizations
	// -----------------------------------------------------------------------

	public function run_optimization() {
		$summary = [];

		if ( get_option( 'mri_siteoptimizer_db_revisions' ) ) {
			$n = $this->delete_revisions();
			/* translators: %d is the number of revisions */
			$summary[] = sprintf( __( '%d revisions', 'mri-siteoptimizer' ), $n );
		}
		if ( get_option( 'mri_siteoptimizer_db_auto_drafts' ) ) {
			$n = $this->delete_auto_drafts();
			/* translators: %d is the number of auto-drafts */
			$summary[] = sprintf( __( '%d auto-drafts', 'mri-siteoptimizer' ), $n );
		}
		if ( get_option( 'mri_siteoptimizer_db_trash_posts' ) ) {
			$n = $this->delete_trash_posts();
			/* translators: %d is the number of trashed posts */
			$summary[] = sprintf( __( '%d trashed posts', 'mri-siteoptimizer' ), $n );
		}
		if ( get_option( 'mri_siteoptimizer_db_spam_comments' ) ) {
			$n = $this->delete_spam_comments();
			/* translators: %d is the number of spam comments */
			$summary[] = sprintf( __( '%d spam comments', 'mri-siteoptimizer' ), $n );
		}
		if ( get_option( 'mri_siteoptimizer_db_trash_comments' ) ) {
			$n = $this->delete_trash_comments();
			/* translators: %d is the number of trashed comments */
			$summary[] = sprintf( __( '%d trashed comments', 'mri-siteoptimizer' ), $n );
		}
		if ( get_option( 'mri_siteoptimizer_db_transients' ) ) {
			$n = $this->delete_expired_transients();
			/* translators: %d is the number of expired transients */
			$summary[] = sprintf( __( '%d expired transients', 'mri-siteoptimizer' ), $n );
		}

		$this->optimize_tables();

		if ( $summary ) {
			Image_Scanner::log(
				'db_optimize',
				__( 'DB optimized: ', 'mri-siteoptimizer' ) . implode( ', ', $summary ) . '.'
			);
		}
	}
}
