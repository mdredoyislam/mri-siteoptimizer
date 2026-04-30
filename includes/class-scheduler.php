<?php
namespace MRISiteOptimizer;

if ( ! defined( 'ABSPATH' ) ) exit;

class Scheduler {

	public function __construct() {
		add_action( 'update_option_mri_siteoptimizer_scan_schedule',    [ $this, 'reschedule_scan' ],    10, 2 );
		add_action( 'update_option_mri_siteoptimizer_cleanup_schedule', [ $this, 'reschedule_cleanup' ], 10, 2 );

		$this->maybe_schedule_defaults();
	}

	private function maybe_schedule_defaults() {
		if ( ! wp_next_scheduled( 'mri_siteoptimizer_scheduled_scan' ) ) {
			$this->schedule( 'mri_siteoptimizer_scheduled_scan', get_option( 'mri_siteoptimizer_scan_schedule', 'weekly' ) );
		}
		if ( ! wp_next_scheduled( 'mri_siteoptimizer_scheduled_cleanup' ) ) {
			$this->schedule( 'mri_siteoptimizer_scheduled_cleanup', 'weekly' );
		}
	}

	private function schedule( $hook, $recurrence ) {
		$valid = [ 'daily', 'weekly', 'monthly' ];
		if ( ! in_array( $recurrence, $valid, true ) ) $recurrence = 'weekly';

		wp_schedule_event( time() + HOUR_IN_SECONDS, $recurrence, $hook );
	}

	public function reschedule_scan( $old, $new ) {
		wp_clear_scheduled_hook( 'mri_siteoptimizer_scheduled_scan' );
		$this->schedule( 'mri_siteoptimizer_scheduled_scan', $new );
	}

	public function reschedule_cleanup( $old, $new ) {
		wp_clear_scheduled_hook( 'mri_siteoptimizer_scheduled_cleanup' );
		$this->schedule( 'mri_siteoptimizer_scheduled_cleanup', $new );
	}

	/**
	 * Add 'monthly' to WP cron schedules.
	 */
	public static function add_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['monthly'] ) ) {
			$schedules['monthly'] = [
				'interval' => 30 * DAY_IN_SECONDS,
				'display'  => __( 'Once Monthly', 'mri-siteoptimizer' ),
			];
		}
		return $schedules;
	}
}
add_filter( 'cron_schedules', [ 'MRISiteOptimizer\\Scheduler', 'add_cron_schedules' ] );
