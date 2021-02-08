<?php
/**
 * Plugin Name:  Load More Anything
 * Plugin URI:   https://github.com/akshuvo/load-more-anything/
 * Author:       Akhtarujjaman Shuvo
 * Author URI:   https://www.facebook.com/akhterjshuvo/
 * Version: 	  2.4.1
 * Description:  A simple plugin that help you to Load more any item with jQuery. You can use Ajaxify Load More button for your blog post, Comments, page, Category, Recent Posts, Sidebar widget Data, Woocommerce Product, Images, Photos, Videos, custom Div or whatever you want.
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
	define('ALD_PLUGIN_VERSION', '2.4.0' );
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

		// Added plugin action link
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );

		// trigger upon plugin activation/deactivation
		register_activation_hook( __FILE__, array( $this, 'plugin_activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'plugin_deactivation' ) );

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
	 * Adds plugin action links.
	 */
	function action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=license-manager-wppt' ) . '">' . esc_html__( 'License Manager', 'lmfwppt' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
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
	    if ( 'options-permalink.php' != $hook ) {
	        //return;
	    }

	    $ver = current_time( 'timestamp' );

	    wp_register_style( 'ald-admin-styles', ALD_PLUGIN_URL . 'admin/assets/css/admin.css', null, $ver );
	    wp_register_script( 'ald-admin-scripts', ALD_PLUGIN_URL . 'admin/assets/js/admin.js', array('jquery'), $ver );
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


/*
* Register settings fields to database
*/
function register_ald_plugin_settings() {

	// wrapper one option data
	register_setting( 'ald-plugin-settings-group', 'ald_wrapper_class' );
	register_setting( 'ald-plugin-settings-group', 'ald_load_class' );
	register_setting( 'ald-plugin-settings-group', 'ald_item_show' );
	register_setting( 'ald-plugin-settings-group', 'ald_item_load' );
	register_setting( 'ald-plugin-settings-group', 'ald_load_label' );
	register_setting( 'ald-plugin-settings-group', 'asr_ald_css_class' );

	// wrapper two option data
	register_setting( 'ald-plugin-settings-group', 'ald_wrapper_classa' );
	register_setting( 'ald-plugin-settings-group', 'ald_load_classa' );
	register_setting( 'ald-plugin-settings-group', 'ald_item_showa' );
	register_setting( 'ald-plugin-settings-group', 'ald_item_loada' );
	register_setting( 'ald-plugin-settings-group', 'ald_load_labela' );

	// wrapper three option data
	register_setting( 'ald-plugin-settings-group', 'ald_wrapper_classb' );
	register_setting( 'ald-plugin-settings-group', 'ald_load_classb' );
	register_setting( 'ald-plugin-settings-group', 'ald_item_showb' );
	register_setting( 'ald-plugin-settings-group', 'ald_item_loadb' );
	register_setting( 'ald-plugin-settings-group', 'ald_load_labelb' );

	// wrapper four option data
	register_setting( 'ald-plugin-settings-group', 'ald_wrapper_classc' );
	register_setting( 'ald-plugin-settings-group', 'ald_load_classc' );
	register_setting( 'ald-plugin-settings-group', 'ald_item_showc' );
	register_setting( 'ald-plugin-settings-group', 'ald_item_loadc' );
	register_setting( 'ald-plugin-settings-group', 'ald_load_labelc' );

	// wrapper five option data
	register_setting( 'ald-plugin-settings-group', 'ald_wrapper_classd' );
	register_setting( 'ald-plugin-settings-group', 'ald_load_classd' );
	register_setting( 'ald-plugin-settings-group', 'ald_item_showd' );
	register_setting( 'ald-plugin-settings-group', 'ald_item_loadd' );
	register_setting( 'ald-plugin-settings-group', 'ald_load_labeld' );

	// wrapper five option data
	register_setting( 'ald-plugin-settings-group', 'ald_wrapper_classe' );
	register_setting( 'ald-plugin-settings-group', 'ald_load_classe' );
	register_setting( 'ald-plugin-settings-group', 'ald_item_showe' );
	register_setting( 'ald-plugin-settings-group', 'ald_item_loade' );
	register_setting( 'ald-plugin-settings-group', 'ald_load_labele' );
}
add_action( 'admin_init', 'register_ald_plugin_settings' );

/**
 * Adds plugin action links.
 *
 * @since 1.0.0
 * @version 4.0.0
 */
function wi_plugin_action_links( $links ) {
	$plugin_links = array(
		'<a href="options-general.php?page=ald_setting">' . esc_html__( 'Settings', 'ald' ) . '</a>',
	);
	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wi_plugin_action_links' );