<?php
/**
 * Disposable WordPress smoke test for the installed Load More Anything ZIP.
 *
 * @package Ajax_Load_More_Anything
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( 1 );
}

if ( ! class_exists( 'Ajax_Load_More_Anything' ) || ! defined( 'ALD_PLUGIN_VERSION' ) ) {
	WP_CLI::error( 'Missing Ajax_Load_More_Anything bootstrap class or ALD_PLUGIN_VERSION constant.' );
}

if ( ! function_exists( 'get_ald_options' ) || ! function_exists( 'ald_get_frontend_config' ) ) {
	WP_CLI::error( 'Missing core frontend helper functions.' );
}

if ( ! class_exists( 'Appsero\Client' ) ) {
	WP_CLI::error( 'Missing Appsero SDK Client class.' );
}

update_option(
	'ald_options',
	[
		'general_loadmore' => [
			'smoke_1' => [
				'wrapper_title' => 'Smoke General',
				'btn_selector'  => '.smoke-list',
				'load_selector' => '.smoke-list li',
				'visible_items' => '3',
				'load_items'    => '3',
				'button_label'  => 'Load +[count] More',
				'display_type'  => 'flex',
			],
		],
		'ajax_loadmore'    => [
			'smoke_a' => [
				'wrapper_title'            => 'Smoke Ajax',
				'event_type'               => 'selectors_click',
				'click_selector'           => '.nav-links a.next',
				'data_implement_selectors' => [
					[
						'data_selector'  => '.site-main',
						'implement_type' => 'insert_after',
					],
				],
			],
		],
		'custom_css'       => '.smoke-list { margin: 0; }',
	]
);

$ald_smoke_cfg = ald_get_frontend_config();
$ald_smoke_css = ald_get_frontend_inline_css();

if ( empty( $ald_smoke_cfg['general'] ) || empty( $ald_smoke_cfg['ajax'] ) ) {
	WP_CLI::error( 'Frontend config failed to hydrate general or ajax wrappers.' );
}

if ( false === strpos( $ald_smoke_css, '.smoke-list li' ) ) {
	WP_CLI::error( 'Frontend inline CSS missing expected hidden selector rule.' );
}

delete_option( 'ald_options' );

WP_CLI::success( 'Installed Load More Anything ZIP activated cleanly; helpers, Appsero SDK, config, and inline CSS verified.' );
