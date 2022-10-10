<?php
/**
 * Plugin Name:  Load More Anything
 * Plugin URI:   https://wordpress.org/plugins/ajax-load-more-anything/
 * Author:       Addon Master
 * Author URI:   https://addonmaster.com/contact
 * Version: 	  3.0.2
 * Description:  A simple plugin that help you to Load more any item with jQuery/Ajax. You can use Ajaxify Load More button for your blog post, Comments, page, Category, Recent Posts, Sidebar widget Data, Woocommerce Product, Images, Photos, Videos, custom selector or whatever you want.
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  ajax-load-more-anything
 * Domain Path:  /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
* Including Plugin file for security
* Include_once
*
* @since 1.0.0
*/
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

define( 'ALD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( !defined('ALD_PLUGIN_VERSION') ) {
	define('ALD_PLUGIN_VERSION', '3.0.2' );
}

// GO PRO URL
define( 'ALD_GOPRO_URL', 'https://addonmaster.com/load-more-anything/?utm_source=dashboard&utm_medium=popuptop&utm_campaign=wpuser' );

/**
 *	Plugin Main Class
 */

final class Ajax_Load_More_Anything {

	private function __construct() {
		// Loaded textdomain
		add_action('plugins_loaded', array( $this, 'plugin_loaded_action' ), 10, 2);

		// Enqueue frontend scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 100 );

		// trigger upon plugin activation/deactivation
		register_activation_hook( __FILE__, array( $this, 'plugin_activation' ) );
		//register_deactivation_hook( __FILE__, array( $this, 'plugin_deactivation' ) );

		// Action link
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

	}

	/**
	 * Initialization
	 */
	public static function init(){
     	static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
	}

	/**
	 * Plugin Loaded Action
	 */
	function plugin_loaded_action() {
		// Loading Text Domain for Internationalization
		load_plugin_textdomain( 'ajax-load-more-anything', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );

		require_once( dirname( __FILE__ ) . '/inc/ald-functions.php' );
		require_once( dirname( __FILE__ ) . '/admin/functions.php' );
		require_once( dirname( __FILE__ ) . '/admin/Menu.php' );

	}

	/**
	 * Enqueue Frontend Scripts
	 */
	function enqueue_scripts() {

	    wp_enqueue_style( 'ald-styles', ALD_PLUGIN_URL . 'assets/css/styles.css', null, ALD_PLUGIN_VERSION );
	    wp_enqueue_script( 'ald-scripts', ALD_PLUGIN_URL . 'assets/js/scripts.js', array('jquery'), ALD_PLUGIN_VERSION );

		wp_localize_script( 'ald-scripts', 'ald_params',
         	array(
         	    'nonce' => wp_create_nonce( 'ald_nonce' ),
         	    'ajaxurl' => admin_url( 'admin-ajax.php' ),
         	    'ald_pro' => ( defined('ALD_PRO_PLUGIN_VERSION') ) ? '1' : '0',
         	)
        );

	}

	/**
	 *  Plugin Activation
	 */
	function plugin_activation() {
		// Set default options
        update_option( 'ald_installed', time() );
        update_option( 'ald_plugin_version', ALD_PLUGIN_VERSION );
	}

	/**
	 *  Plugin Deactivation
	 */
	function plugin_deactivation() {

	}

	/**
	 * Enqueue admin script
	 */
	function admin_scripts( $hook ) {
	    if ( 'toplevel_page_ald_setting' != $hook ) {
	        return;
	    }

	    wp_register_style( 'ald-admin-styles', ALD_PLUGIN_URL . 'admin/assets/css/admin.css', null, ALD_PLUGIN_VERSION );
	    wp_register_script( 'ald-admin-scripts', ALD_PLUGIN_URL . 'admin/assets/js/admin.js', array('jquery'), ALD_PLUGIN_VERSION );

	    // Ajax Params
	    wp_localize_script( 'ald-admin-scripts', 'alda_params',
         	array(
         	    'nonce' => wp_create_nonce( 'ald_nonce' ),
         	    'ajaxurl' => admin_url( 'admin-ajax.php' ),
         	    'ald_pro' => ( defined('ALD_PRO_PLUGIN_VERSION') ) ? '1' : '0',
         	)
        );
	}

	/**
	 * Adds plugin action links.
	 *
	 * @since 1.0.0
	 * @version 4.0.0
	 */
	function plugin_action_links( $links ) {

		if( !defined('ALD_PRO_PLUGIN_VERSION') ){
			$plugin_links[] = '<a target="_blank" href="'. ALD_GOPRO_URL .'"><b style=" color: #7e3434; ">&#9733;' . esc_html__( 'GO PRO', 'ajax-load-more-anything' ) . '</b></a>';
		}
		
		$plugin_links[] = '<a href="admin.php?page=ald_setting">' . esc_html__( 'Settings', 'ajax-load-more-anything' ) . '</a>';

		return array_merge( $plugin_links, $links );
	}

}

/**
 * Initialize plugin
 */
function ajax_load_more_anything(){
	return Ajax_Load_More_Anything::init();
}

// Let's start it
ajax_load_more_anything();

