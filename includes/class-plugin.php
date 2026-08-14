<?php
namespace MRISiteOptimizer;

if ( ! defined( 'ABSPATH' ) ) exit;

class Plugin {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	private function init() {
		// Load all modules
		new Image_Scanner();
		new File_Cleaner();
		new Image_Compressor();
		new DB_Optimizer();
		new Scheduler();
		new Ajax_Handler();

		if ( is_admin() ) {
			new Admin_Menu();
		}
	}
}
