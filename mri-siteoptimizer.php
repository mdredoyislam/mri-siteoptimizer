<?php
/**
 * Plugin Name: MRI SiteOptimizer
 * Plugin URI:  https://github.com/mdredoyislam/mri-siteoptimizer
 * Description: Clean unlinked images, remove unnecessary files, compress images, lazy load, and optimize your database — all in one lightweight plugin.
 * Version:     1.0.2
 * Author:      Md Redpy Islam
 * Author URI:  https://github.com/mdredoyislam
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mri-siteoptimizer
 * Domain Path: /languages
 * GitHub Plugin URI: mdredoyislam/mri-siteoptimizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MRI_SITEOPTIMIZER_VERSION',  '1.0.1' );
define( 'MRI_SITEOPTIMIZER_FILE',     __FILE__ );
define( 'MRI_SITEOPTIMIZER_DIR',      plugin_dir_path( __FILE__ ) );
define( 'MRI_SITEOPTIMIZER_URL',      plugin_dir_url( __FILE__ ) );
define( 'MRI_SITEOPTIMIZER_BASENAME', plugin_basename( __FILE__ ) );

// Autoload classes
spl_autoload_register( function( $class ) {
	$prefix = 'MRISiteOptimizer\\';
	if ( strpos( $class, $prefix ) !== 0 ) return;
	$relative = str_replace( $prefix, '', $class );
	$file = MRI_SITEOPTIMIZER_DIR . 'includes/class-' . strtolower( str_replace( '_', '-', $relative ) ) . '.php';
	if ( file_exists( $file ) ) require $file;
} );

// Boot
add_action( 'plugins_loaded', [ 'MRISiteOptimizer\\Plugin', 'instance' ] );

register_activation_hook(   __FILE__, [ 'MRISiteOptimizer\\Installer', 'activate'   ] );
register_deactivation_hook( __FILE__, [ 'MRISiteOptimizer\\Installer', 'deactivate' ] );
register_uninstall_hook(    __FILE__, [ 'MRISiteOptimizer\\Installer', 'uninstall'  ] );
