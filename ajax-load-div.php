<?php
/**
 * Plugin Name:       Load More Anything
 * Plugin URI:        https://wordpress.org/plugins/ajax-load-more-anything/
 * Author:            AddonMaster
 * Author URI:        https://addonmaster.com/contact
 * Version:           3.3.10
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Description:       A lightweight plugin that lets you add Load More buttons, Ajax pagination, and Infinite Scroll to blog posts, WooCommerce products, comments, lists, tables, or any CSS selector.
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ajax-load-more-anything
 * Domain Path:       /languages
 *
 * @package Ajax_Load_More_Anything
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main plugin bootstrap class.
 */
final class Ajax_Load_More_Anything {

	/**
	 * Plugin version (uppercase canonical constant).
	 *
	 * @var string
	 */
	const VERSION = '3.3.10';

	/**
	 * Legacy lowercase plugin version constant retained for backward compatibility.
	 *
	 * @var string
	 */
	const version = self::VERSION; // phpcs:ignore Generic.NamingConventions.UpperCaseConstantName.ClassConstantNotUpperCase

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->define_constants();

		add_action( 'plugins_loaded', [ $this, 'plugin_loaded' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 999 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ], 100 );

		register_activation_hook( __FILE__, [ $this, 'plugin_activation' ] );

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'plugin_action_links' ] );
	}

	/**
	 * Return singleton instance.
	 *
	 * @return Ajax_Load_More_Anything
	 */
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Define required plugin constants.
	 *
	 * @return void
	 */
	public function define_constants() {
		if ( ! defined( 'ALD_PLUGIN_VERSION' ) ) {
			define( 'ALD_PLUGIN_VERSION', self::VERSION );
		}
		if ( ! defined( 'ALD_PLUGIN_FILE' ) ) {
			define( 'ALD_PLUGIN_FILE', __FILE__ );
		}
		if ( ! defined( 'ALD_PLUGIN_PATH' ) ) {
			define( 'ALD_PLUGIN_PATH', __DIR__ );
		}
		if ( ! defined( 'ALD_PLUGIN_URL' ) ) {
			define( 'ALD_PLUGIN_URL', plugin_dir_url( ALD_PLUGIN_FILE ) );
		}
		if ( ! defined( 'ALD_PLUGIN_ASSETS' ) ) {
			define( 'ALD_PLUGIN_ASSETS', ALD_PLUGIN_URL . 'assets' );
		}
		if ( ! defined( 'ALD_GOPRO_URL' ) ) {
			define( 'ALD_GOPRO_URL', 'https://addonmaster.com/load-more-anything/?utm_source=dashboard&utm_medium=popuptop&utm_campaign=wpuser' );
		}
	}

	/**
	 * Load plugin files once WordPress plugins are loaded.
	 *
	 * @return void
	 */
	public function plugin_loaded() {
		require_once __DIR__ . '/inc/ald-functions.php';

		if ( is_admin() ) {
			require_once __DIR__ . '/admin/functions.php';
			require_once __DIR__ . '/admin/Menu.php';
		}
	}

	/**
	 * Register and enqueue frontend assets when Load More rules or custom styles are configured.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$suffix     = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$jquery_dep = ( 'on' === get_ald_options( 'disable_jquery_dep' ) ) ? [] : [ 'jquery' ];

		wp_register_style( 'ald-styles', ALD_PLUGIN_ASSETS . '/styles' . $suffix . '.css', [], ALD_PLUGIN_VERSION );
		wp_register_script( 'ald-scripts', ALD_PLUGIN_ASSETS . '/scripts' . $suffix . '.js', $jquery_dep, ALD_PLUGIN_VERSION, true );

		if ( ! ald_has_frontend_config() ) {
			return;
		}

		wp_enqueue_style( 'ald-styles' );

		$inline_css = ald_get_frontend_inline_css();
		if ( '' !== $inline_css ) {
			wp_add_inline_style( 'ald-styles', $inline_css );
		}

		wp_enqueue_script( 'ald-scripts' );
		wp_localize_script( 'ald-scripts', 'ald_params', ald_get_frontend_config() );
	}

	/**
	 * Enqueue admin scripts and styles strictly on the Load More Anything settings screen.
	 *
	 * @param string $hook Current admin page hook suffix.
	 * @return void
	 */
	public function admin_scripts( $hook ) {
		if ( 'toplevel_page_ald_setting' !== $hook ) {
			return;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_style( 'ald-admin-styles', ALD_PLUGIN_ASSETS . '/admin' . $suffix . '.css', [], ALD_PLUGIN_VERSION );
		wp_enqueue_script( 'ald-admin-scripts', ALD_PLUGIN_ASSETS . '/admin' . $suffix . '.js', [ 'jquery' ], ALD_PLUGIN_VERSION, true );

		$cmcss_settings = [ 'codeEditor' => wp_enqueue_code_editor( [ 'type' => 'text/css' ] ) ];
		$cmjs_settings  = [ 'codeEditor' => wp_enqueue_code_editor( [ 'type' => 'text/javascript' ] ) ];

		wp_localize_script( 'ald-admin-scripts', 'cmcss_settings', $cmcss_settings );
		wp_localize_script( 'ald-admin-scripts', 'cmjs_settings', $cmjs_settings );

		wp_localize_script(
			'ald-admin-scripts',
			'alda_params',
			[
				'nonce'      => wp_create_nonce( 'ald_nonce' ),
				'ajaxurl'    => admin_url( 'admin-ajax.php' ),
				'ald_pro'    => ald_is_pro() ? '1' : '0',
				'freeLimits' => [
					'general' => 6,
					'ajax'    => 1,
				],
				'presets'    => function_exists( 'ald_get_selector_presets' ) ? ald_get_selector_presets() : [],
				'i18n'       => [
					'saving'          => __( 'Saving…', 'ajax-load-more-anything' ),
					'saveChanges'     => __( 'Save Changes', 'ajax-load-more-anything' ),
					'savedSuccess'    => __( 'Settings saved successfully.', 'ajax-load-more-anything' ),
					'saveError'       => __( 'Could not save settings. Please try again.', 'ajax-load-more-anything' ),
					'addError'        => __( 'Could not add wrapper. Please refresh and try again.', 'ajax-load-more-anything' ),
					'presetApplied'   => __( 'Preset applied. Remember to save changes.', 'ajax-load-more-anything' ),
					'wrapperRemoved'  => __( 'Wrapper removed. Save changes to apply.', 'ajax-load-more-anything' ),
					'unsavedChanges'  => __( 'Unsaved changes', 'ajax-load-more-anything' ),
					'allChangesSaved' => __( 'All changes saved', 'ajax-load-more-anything' ),
					'confirmDelete'   => __( 'Remove this wrapper?', 'ajax-load-more-anything' ),
					'clickEvent'      => __( 'Selector Click', 'ajax-load-more-anything' ),
					'scrollEvent'     => __( 'Infinite Scroll', 'ajax-load-more-anything' ),
					'buttonEvent'     => __( 'Load More Button', 'ajax-load-more-anything' ),
					'untitledWrapper' => __( 'Untitled Wrapper', 'ajax-load-more-anything' ),
				],
			]
		);
	}

	/**
	 * Record installation metadata on plugin activation.
	 *
	 * @return void
	 */
	public function plugin_activation() {
		if ( ! get_option( 'ald_installed' ) ) {
			update_option( 'ald_installed', time() );
		}
		update_option( 'ald_plugin_version', ALD_PLUGIN_VERSION );
	}

	/**
	 * Add Settings and Go Pro links on the Plugins screen.
	 *
	 * @param array $links Existing action links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = [];

		if ( ! defined( 'ALD_PRO_PLUGIN_VERSION' ) ) {
			$plugin_links[] = '<a target="_blank" rel="noopener noreferrer" href="' . esc_url( ALD_GOPRO_URL ) . '"><b style="color:#b45309;">&#9733; ' . esc_html__( 'Go Pro', 'ajax-load-more-anything' ) . '</b></a>';
		}

		$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=ald_setting' ) ) . '">' . esc_html__( 'Settings', 'ajax-load-more-anything' ) . '</a>';

		return array_merge( $plugin_links, $links );
	}
}

/**
 * Initialize plugin singleton.
 *
 * @return Ajax_Load_More_Anything
 */
function ajax_load_more_anything() {
	return Ajax_Load_More_Anything::init();
}

ajax_load_more_anything();

/**
 * Initialize the Appsero telemetry SDK (opt-in only).
 *
 * @return void
 */
function ama_appsero_init_tracker_ajax_load_more_anything() {
	if ( ! file_exists( __DIR__ . '/appsero/src/Client.php' ) ) {
		return;
	}

	if ( ! class_exists( 'Appsero\Client' ) ) {
		require_once __DIR__ . '/appsero/src/Client.php';
	}

	$client = new Appsero\Client( '7e201c93-ae5f-4a3a-8ead-d1ccf4ad5e30', 'Load More Anything', __FILE__ );
	$client->insights()->init();
}

ama_appsero_init_tracker_ajax_load_more_anything();
