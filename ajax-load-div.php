<?php
/**
 * Plugin Name:  Load More Anything
 * Plugin URI:   https://github.com/akshuvo/load-more-anything/
 * Author:       Addon Master
 * Author URI:   https://addonmaster.com/contact
 * Version: 	  2.4.1
 * Description:  A simple plugin that help you to Load more any item with jQuery/Ajax. You can use Ajaxify Load More button for your blog post, Comments, page, Category, Recent Posts, Sidebar widget Data, Woocommerce Product, Images, Photos, Videos, custom selector or whatever you want.
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  aldtd
 * Domain Path:  /lang
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
	define('ALD_PLUGIN_VERSION', '2.4.1' );
}

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
		load_plugin_textdomain( 'aldtd', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );

		require_once( dirname( __FILE__ ) . '/inc/ald-functions.php' );
		require_once( dirname( __FILE__ ) . '/admin/functions.php' );
		require_once( dirname( __FILE__ ) . '/admin/Menu.php' );

	}

	/**
	 * Enqueue Frontend Scripts
	 */
	function enqueue_scripts() {
		$ver = current_time( 'timestamp' );

	    wp_enqueue_style( 'ald-styles', ALD_PLUGIN_URL . 'assets/css/styles.css', null, $ver );
	    wp_enqueue_script( 'ald-scripts', ALD_PLUGIN_URL . 'assets/js/scripts.js', array('jquery'), $ver );

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

        if ( ! get_option( 'ald_installed' ) ) {
            update_option( 'ald_installed', time() );
        }

        update_option( 'ald_plugin_version', ALD_PLUGIN_VERSION );

	}

	/**
	*  Plugin Deactivation
	*/
	function plugin_deactivation() {

	}

	/**
	 * Enqueue admin script
	 *
	 */
	function admin_scripts( $hook ) {
	    if ( 'toplevel_page_ald_setting' != $hook ) {
	        return;
	    }

	    $ver = current_time( 'timestamp' );

	    wp_register_style( 'ald-admin-styles', ALD_PLUGIN_URL . 'admin/assets/css/admin.css', null, $ver );
	    wp_register_script( 'ald-admin-scripts', ALD_PLUGIN_URL . 'admin/assets/js/admin.js', array('jquery'), $ver );

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
		$plugin_links = array(
			'<a href="admin.php?page=ald_setting">' . esc_html__( 'Settings', 'ald' ) . '</a>',
		);
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

