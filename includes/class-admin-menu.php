<?php
namespace MRISiteOptimizer;

if ( ! defined( 'ABSPATH' ) ) exit;

class Admin_Menu {

	public function __construct() {
		add_action( 'admin_menu',            [ $this, 'register_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_filter( 'plugin_action_links_' . MRI_SITEOPTIMIZER_BASENAME, [ $this, 'action_links' ] );
	}

	public function register_menu() {
		add_menu_page(
			__( 'MRI SiteOptimizer', 'mri-siteoptimizer' ),
			__( 'MRI SiteOptimizer', 'mri-siteoptimizer' ),
			'manage_options',
			'mri-siteoptimizer',
			[ $this, 'render_dashboard' ],
			'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M13 2L4.09 12.26c-.36.43-.14 1.07.39 1.22L11 15l-2 7 8.91-10.26c.36-.43.14-1.07-.39-1.22L11 9l2-7z"/></svg>' ),
			80
		);

		$pages = [
			[ 'mri-siteoptimizer-images',   __( 'Images',   'mri-siteoptimizer' ), [ $this, 'render_images'   ] ],
			[ 'mri-siteoptimizer-files',    __( 'Files',    'mri-siteoptimizer' ), [ $this, 'render_files'    ] ],
			[ 'mri-siteoptimizer-database', __( 'Database', 'mri-siteoptimizer' ), [ $this, 'render_database' ] ],
			[ 'mri-siteoptimizer-settings', __( 'Settings', 'mri-siteoptimizer' ), [ $this, 'render_settings' ] ],
			[ 'mri-siteoptimizer-log',      __( 'Activity', 'mri-siteoptimizer' ), [ $this, 'render_log'      ] ],
		];

		foreach ( $pages as $p ) {
			add_submenu_page( 'mri-siteoptimizer', $p[1] . ' – MRI SiteOptimizer', $p[1], 'manage_options', $p[0], $p[2] );
		}
	}

	public function enqueue_assets( $hook ) {
		if ( strpos( $hook, 'mri-siteoptimizer' ) === false ) return;

		wp_enqueue_style(
			'mri-siteoptimizer-admin',
			MRI_SITEOPTIMIZER_URL . 'admin/assets/css/admin.css',
			[],
			MRI_SITEOPTIMIZER_VERSION
		);

		wp_enqueue_script(
			'mri-siteoptimizer-admin',
			MRI_SITEOPTIMIZER_URL . 'admin/assets/js/admin.js',
			[ 'jquery' ],
			MRI_SITEOPTIMIZER_VERSION,
			true
		);

		wp_localize_script( 'mri-siteoptimizer-admin', 'MRISiteOptimizerData', [
			'nonce'   => wp_create_nonce( 'mri_siteoptimizer_nonce' ),
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'strings' => [
				'scanning'     => __( 'Scanning…',          'mri-siteoptimizer' ),
				'deleting'     => __( 'Deleting…',          'mri-siteoptimizer' ),
				'compressing'  => __( 'Compressing…',       'mri-siteoptimizer' ),
				'optimizing'   => __( 'Optimizing…',        'mri-siteoptimizer' ),
				'confirm_del'  => __( 'Delete selected items? This cannot be undone.', 'mri-siteoptimizer' ),
				'saved'        => __( 'Settings saved.',    'mri-siteoptimizer' ),
				'done'         => __( 'Done!',              'mri-siteoptimizer' ),
				'no_items'     => __( 'No items found.',    'mri-siteoptimizer' ),
			],
		] );
			// Inline JS for files page (replaces raw <script> tag)
			$inline_js  = 'jQuery(function($){';
			$inline_js .= '$("#so-btn-remove-sizes").on("click",function(){';
			$inline_js .= 'if(!confirm("Remove thumbnail files for unregistered image sizes?"))return;';
			$inline_js .= 'var $btn=$(this);$btn.prop("disabled",true);';
			$inline_js .= '$.post(MRISiteOptimizerData.ajaxurl,{action:"mri_siteoptimizer_db_optimize",nonce:MRISiteOptimizerData.nonce})';
			$inline_js .= '.done(function(res){$btn.prop("disabled",false);';
			$inline_js .= 'if(res.success){$("#so-sizes-result").html("<div class=\'so-notice so-notice-success\'>Done!</div>");}';
			$inline_js .= '});});});';
			wp_add_inline_script( 'mri-siteoptimizer-admin', $inline_js );
		}

	public function action_links( $links ) {
		$custom = [
			'<a href="' . admin_url( 'admin.php?page=mri-siteoptimizer' ) . '">'          . __( 'Dashboard', 'mri-siteoptimizer' ) . '</a>',
			'<a href="' . admin_url( 'admin.php?page=mri-siteoptimizer-settings' ) . '">' . __( 'Settings',  'mri-siteoptimizer' ) . '</a>',
		];
		return array_merge( $custom, $links );
	}

	// -----------------------------------------------------------------------
	// Page renderers — each loads its partial template
	// -----------------------------------------------------------------------

	public function render_dashboard() { $this->load_template( 'dashboard' ); }
	public function render_images()    { $this->load_template( 'images' );    }
	public function render_files()     { $this->load_template( 'files' );     }
	public function render_database()  { $this->load_template( 'database' );  }
	public function render_settings()  { $this->load_template( 'settings' );  }
	public function render_log()       { $this->load_template( 'log' );       }

	private function load_template( $name ) {
		$file = MRI_SITEOPTIMIZER_DIR . 'admin/' . $name . '.php';
		if ( file_exists( $file ) ) include $file;
	}
}
