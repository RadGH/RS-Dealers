<?php
/*
 * Plugin Name: RS Dealers
 * Plugin URI: http://radleysustaire.com/
 * Description: Display dealers on your site using Google Maps.
 * Version: 1.0.0
 * Author: Radley Sustaire
 * Author URI: http://radleysustaire.com/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Domain Path: /languages
 *
 * Requires at least: 3.8
 * Tested up to: 4.8
 */

if ( !defined( 'ABSPATH' ) ) exit; // Do not allow direct access

/**
 * Main plugin class for the RS Dealers plugin.
 * @class RS_Dealers
 */
if ( !class_exists( 'RS_Dealers' ) ) {
	class RS_Dealers
	{
		// Plugin settings
		public $version = '1.0.0';
		public $plugin_dir = null;
		public $plugin_url = null;
		public $plugin_basename = null;
		
		/**
		 * RS_Dealers constructor
		 */
		public function __construct() {
			$this->plugin_dir = untrailingslashit( plugin_dir_path( __FILE__ ) );
			$this->plugin_url = plugins_url( '', __FILE__ );
			$this->plugin_basename = plugin_basename( __FILE__ );
			
			// Finish setting up the plugin once other plugins have loaded, for compatibility.
			add_action( 'plugins_loaded', array( &$this, 'setup_plugin' ), 20 );
		}
		
		/**
		 * Initializes the rest of our plugin
		 */
		public function setup_plugin() {
			if ( !class_exists( 'acf' ) ) {
				add_action( 'admin_notices', 'rsd_acf_not_running' );
				return;
			}
			
			include( $this->plugin_dir . '/includes/dealers.php' );
			include( $this->plugin_dir . '/includes/shortcode.php' );
			include( $this->plugin_dir . '/includes/enqueue.php' );
		}
	}
}

function rsd_acf_not_running() {
	?>
	<div class="error">
		<p><strong>RS Dealers: Error</strong></p>
		<p>The required plugin <strong>Advanced Custom Fields Pro</strong> is not running. Please activate this required plugin, or disable RS Dealers.</p>
	</div>
	<?php
}

// Create our plugin object, accessible via a global variable.
global $RS_Dealers;
$RS_Dealers = new RS_Dealers();