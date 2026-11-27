<?php
/**
 * Core frontend helpers, option accessors, sanitization, and dynamic asset builders.
 *
 * @package Ajax_Load_More_Anything
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve plugin options or a specific option key.
 *
 * @param string $key           Optional top-level key inside ald_options.
 * @param mixed  $default_value Default value if key is missing.
 * @return mixed
 */
function get_ald_options( $key = '', $default_value = '' ) {
	$ald_options = get_option( 'ald_options', [] );
	if ( ! is_array( $ald_options ) ) {
		$ald_options = [];
	}

	if ( '' !== $key ) {
		return isset( $ald_options[ $key ] ) ? $ald_options[ $key ] : $default_value;
	}

	return $ald_options;
}

/**
 * Check whether Load More Anything Pro is active.
 *
 * @return bool
 */
function ald_is_pro() {
	return defined( 'ALD_PRO_PLUGIN_VERSION' );
}

/**
 * List of supported CSS display types for General Load More wrappers.
 *
 * @return array
 */
function ald_display_types() {
	return [
		'default'            => [
			'label' => __( 'Default (Slide Down)', 'ajax-load-more-anything' ),
			'value' => 'default',
			'pro'   => false,
		],
		'flex'               => [
			'label' => __( 'Flex', 'ajax-load-more-anything' ),
			'value' => 'flex',
			'pro'   => false,
		],
		'block'              => [
			'label' => __( 'Block', 'ajax-load-more-anything' ),
			'value' => 'block',
			'pro'   => true,
		],
		'grid'               => [
			'label' => __( 'Grid', 'ajax-load-more-anything' ),
			'value' => 'grid',
			'pro'   => true,
		],
		'inline'             => [
			'label' => __( 'Inline', 'ajax-load-more-anything' ),
			'value' => 'inline',
			'pro'   => true,
		],
		'inline-block'       => [
			'label' => __( 'Inline Block', 'ajax-load-more-anything' ),
			'value' => 'inline-block',
			'pro'   => true,
		],
		'inline-flex'        => [
			'label' => __( 'Inline Flex', 'ajax-load-more-anything' ),
			'value' => 'inline-flex',
			'pro'   => true,
		],
		'inline-grid'        => [
			'label' => __( 'Inline Grid', 'ajax-load-more-anything' ),
			'value' => 'inline-grid',
			'pro'   => true,
		],
		'inline-table'       => [
			'label' => __( 'Inline Table', 'ajax-load-more-anything' ),
			'value' => 'inline-table',
			'pro'   => true,
		],
		'contents'           => [
			'label' => __( 'Contents', 'ajax-load-more-anything' ),
			'value' => 'contents',
			'pro'   => true,
		],
		'list-item'          => [
			'label' => __( 'List Item', 'ajax-load-more-anything' ),
			'value' => 'list-item',
			'pro'   => true,
		],
		'run-in'             => [
			'label' => __( 'Run In', 'ajax-load-more-anything' ),
			'value' => 'run-in',
			'pro'   => true,
		],
		'table'              => [
			'label' => __( 'Table', 'ajax-load-more-anything' ),
			'value' => 'table',
			'pro'   => true,
		],
		'table-caption'      => [
			'label' => __( 'Table Caption', 'ajax-load-more-anything' ),
			'value' => 'table-caption',
			'pro'   => true,
		],
		'table-column-group' => [
			'label' => __( 'Table Column Group', 'ajax-load-more-anything' ),
			'value' => 'table-column-group',
			'pro'   => true,
		],
		'table-header-group' => [
			'label' => __( 'Table Header Group', 'ajax-load-more-anything' ),
			'value' => 'table-header-group',
			'pro'   => true,
		],
		'table-footer-group' => [
			'label' => __( 'Table Footer Group', 'ajax-load-more-anything' ),
			'value' => 'table-footer-group',
			'pro'   => true,
		],
		'table-row-group'    => [
			'label' => __( 'Table Row Group', 'ajax-load-more-anything' ),
			'value' => 'table-row-group',
			'pro'   => true,
		],
		'table-cell'         => [
			'label' => __( 'Table Cell', 'ajax-load-more-anything' ),
			'value' => 'table-cell',
			'pro'   => true,
		],
		'table-column'       => [
			'label' => __( 'Table Column', 'ajax-load-more-anything' ),
			'value' => 'table-column',
			'pro'   => true,
		],
		'table-row'          => [
			'label' => __( 'Table Row', 'ajax-load-more-anything' ),
			'value' => 'table-row',
			'pro'   => true,
		],
		'initial'            => [
			'label' => __( 'Initial', 'ajax-load-more-anything' ),
			'value' => 'initial',
			'pro'   => true,
		],
		'inherit'            => [
			'label' => __( 'Inherit', 'ajax-load-more-anything' ),
			'value' => 'inherit',
			'pro'   => true,
		],
	];
}

/**
 * Sanitize a General Load More display type against the active plan allowlist.
 *
 * @param string $display_type Raw display type.
 * @return string
 */
function ald_sanitize_display_type( $display_type = '' ) {
	$display_type = sanitize_key( (string) $display_type );
	$all_types    = ald_display_types();

	if ( 'normal' === $display_type ) {
		return 'default';
	}

	if ( ! isset( $all_types[ $display_type ] ) ) {
		return 'default';
	}

	if ( ! ald_is_pro() && ! empty( $all_types[ $display_type ]['pro'] ) ) {
		return 'default';
	}

	return $display_type;
}

/**
 * Sanitize a CSS selector so it cannot inject rules or break out of <style> blocks.
 *
 * Preserves valid selector combinators (e.g. `.parent > .item`, `a[rel="next"]`, `:nth-child(2n)`).
 *
 * @param string $selector Raw CSS selector.
 * @return string
 */
function ald_sanitize_css_selector( $selector ) {
	$selector = (string) $selector;
	$selector = wp_check_invalid_utf8( $selector );
	$selector = preg_replace( '#</?(?:style|script)\b[^>]*>#i', '', $selector );
	$selector = str_replace( [ "\0", "\r", "\n", '{', '}', ';', '<' ], '', $selector );

	return trim( $selector );
}

/**
 * Sanitize custom CSS while preserving valid CSS combinators like `>` and quotes.
 *
 * @param string $css Raw CSS string.
 * @return string
 */
function ald_sanitize_custom_css( $css ) {
	$css = (string) $css;
	$css = wp_check_invalid_utf8( $css );
	$css = str_replace( "\0", '', $css );
	$css = preg_replace( '#</?(?:style|script)\b[^>]*>#i', '', $css );
	$css = str_ireplace( [ '</style', '<style', '</script', '<script' ], '', $css );

	return trim( $css );
}

/**
 * Sanitize a space-separated list of HTML classes.
 *
 * @param string $classes Raw class list.
 * @return string
 */
function ald_sanitize_class_list( $classes ) {
	$classes = str_replace( [ '.', '#', ',' ], ' ', (string) $classes );
	$parts   = preg_split( '/\s+/', trim( $classes ) );
	$clean   = [];

	if ( is_array( $parts ) ) {
		foreach ( $parts as $part ) {
			$token = sanitize_html_class( $part );
			if ( '' !== $token ) {
				$clean[] = $token;
			}
		}
	}

	return implode( ' ', array_unique( $clean ) );
}

/**
 * Lightweight CSS minifier retained for backward compatibility.
 *
 * @param string $input Raw CSS string.
 * @return string
 */
function ald_minify_css( $input ) {
	$input = (string) $input;
	if ( '' === trim( $input ) ) {
		return $input;
	}

	$input = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $input );
	$input = preg_replace( '/\s+/', ' ', $input );
	$input = str_replace( [ ' {', '{ ', ' }', '} ', ' ;', '; ', ' :', ': ' ], [ '{', '{', '}', '}', ';', ';', ':', ':' ], $input );

	return trim( $input );
}

/**
 * Detect whether Elementor's editor preview mode is active safely.
 *
 * @return bool
 */
function ald_is_elementor_preview() {
	if ( ! defined( 'ELEMENTOR_VERSION' ) || ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}

	$instance = \Elementor\Plugin::$instance;
	if ( empty( $instance ) || empty( $instance->preview ) || ! method_exists( $instance->preview, 'is_preview_mode' ) ) {
		return false;
	}

	return (bool) $instance->preview->is_preview_mode();
}

/**
 * Translate and format General Load More button label (supports `[count]` placeholder).
 *
 * Preserves legacy WPML string domain `'Load More Anyting'` for existing translations.
 *
 * @param string $label Raw button label.
 * @return string Escaped HTML-safe button label markup.
 */
function ald_button_label( $label = '' ) {
	$label          = (string) $label;
	$wpml_string_id = 'General Wrapper: ' . sanitize_title( $label );

	if ( function_exists( 'icl_register_string' ) ) {
		icl_register_string( 'Load More Anyting', $wpml_string_id, $label );
	}

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WPML standard filter hook.
	$label = (string) apply_filters( 'wpml_translate_single_string', $label, 'Load More Anyting', $wpml_string_id );

	$escaped = esc_html( $label );

	return str_replace( '[count]', '<span class="ald-count"></span>', $escaped );
}

/**
 * Translate Ajax Load More button label.
 *
 * Preserves legacy WPML string domain `'Load More Anyting'` for existing translations.
 *
 * @param string $label Raw Ajax button label.
 * @return string Plain translated label string.
 */
function ald_ajax_button_label( $label = '' ) {
	$label          = (string) $label;
	$wpml_string_id = 'Ajax Wrapper: ' . sanitize_title( $label );

	if ( function_exists( 'icl_register_string' ) ) {
		icl_register_string( 'Load More Anyting', $wpml_string_id, $label );
	}

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WPML standard filter hook.
	return (string) apply_filters( 'wpml_translate_single_string', $label, 'Load More Anyting', $wpml_string_id );
}

/**
 * Build normalized list of active General wrappers honoring Free/Pro caps.
 *
 * @return array
 */
function ald_get_active_general_wrappers() {
	$general_loadmore = get_ald_options( 'general_loadmore', [] );
	if ( ! is_array( $general_loadmore ) ) {
		return [];
	}

	$is_pro   = ald_is_pro();
	$wrappers = [];

	foreach ( $general_loadmore as $key => $value ) {
		if ( ! is_array( $value ) ) {
			continue;
		}

		if ( ! $is_pro && count( $wrappers ) >= 6 ) {
			break;
		}

		$btn_selector  = isset( $value['btn_selector'] ) ? ald_sanitize_css_selector( $value['btn_selector'] ) : '';
		$load_selector = isset( $value['load_selector'] ) ? ald_sanitize_css_selector( $value['load_selector'] ) : '';

		if ( '' === $btn_selector || '' === $load_selector ) {
			continue;
		}

		$visible_items = isset( $value['visible_items'] ) && '' !== $value['visible_items'] ? max( 0, absint( $value['visible_items'] ) ) : 3;
		$load_items    = isset( $value['load_items'] ) && '' !== $value['load_items'] ? max( 1, absint( $value['load_items'] ) ) : 3;
		$raw_label     = ! empty( $value['button_label'] ) ? sanitize_text_field( $value['button_label'] ) : __( 'Load More', 'ajax-load-more-anything' );
		$display_type  = ! empty( $value['display_type'] ) ? ald_sanitize_display_type( $value['display_type'] ) : 'default';

		$btn_class = 'btn loadMoreBtn';
		if ( $is_pro ) {
			$btn_class = (string) apply_filters( 'lmag_button_class', $btn_class . ' ', $value );
			$btn_class = ald_sanitize_class_list( $btn_class );
			if ( false === strpos( ' ' . $btn_class . ' ', ' loadMoreBtn ' ) ) {
				$btn_class = trim( 'btn loadMoreBtn ' . $btn_class );
			}
		}

		$wrappers[] = [
			'key'           => sanitize_key( (string) $key ),
			'btn_selector'  => $btn_selector,
			'load_selector' => $load_selector,
			'visible_items' => $visible_items,
			'load_items'    => $load_items,
			'button_label'  => ald_button_label( $raw_label ),
			'display_type'  => $display_type,
			'button_class'  => $btn_class,
		];
	}

	return $wrappers;
}

/**
 * Build normalized list of active Ajax wrappers honoring Free/Pro caps.
 *
 * @return array
 */
function ald_get_active_ajax_wrappers() {
	$ajax_loadmore = get_ald_options( 'ajax_loadmore', [] );
	if ( ! is_array( $ajax_loadmore ) ) {
		return [];
	}

	$is_pro   = ald_is_pro();
	$wrappers = [];

	foreach ( $ajax_loadmore as $key => $value ) {
		if ( ! is_array( $value ) ) {
			continue;
		}

		if ( ! $is_pro && count( $wrappers ) >= 1 ) {
			break;
		}

		$event_type = isset( $value['event_type'] ) ? sanitize_key( $value['event_type'] ) : 'selectors_click';
		if ( ! in_array( $event_type, [ 'selectors_click', 'scroll_to_load', 'custom_button' ], true ) ) {
			$event_type = 'selectors_click';
		}

		if ( ! $is_pro && 'custom_button' === $event_type ) {
			$event_type = 'selectors_click';
		}

		$custom_button_append    = isset( $value['custom_button_append'] ) ? ald_sanitize_css_selector( $value['custom_button_append'] ) : '';
		$button_trigger_selector = isset( $value['button_trigger_selector'] ) ? ald_sanitize_css_selector( $value['button_trigger_selector'] ) : '';
		$click_selector          = isset( $value['click_selector'] ) ? ald_sanitize_css_selector( $value['click_selector'] ) : '';
		$active_click_selector   = ( $is_pro && 'custom_button' === $event_type ) ? $button_trigger_selector : $click_selector;

		if ( '' === $active_click_selector ) {
			continue;
		}

		$button_label_raw = ! empty( $value['button_label'] ) ? sanitize_text_field( $value['button_label'] ) : __( 'Load More', 'ajax-load-more-anything' );
		$no_more_text_raw = ! empty( $value['no_more_text'] ) ? sanitize_text_field( $value['no_more_text'] ) : __( 'No more items to load', 'ajax-load-more-anything' );

		$raw_dis   = isset( $value['data_implement_selectors'] ) && is_array( $value['data_implement_selectors'] ) ? $value['data_implement_selectors'] : [];
		$clean_dis = [];
		foreach ( $raw_dis as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$data_selector = isset( $row['data_selector'] ) ? ald_sanitize_css_selector( $row['data_selector'] ) : '';
			if ( '' === $data_selector ) {
				continue;
			}
			$implement_type = isset( $row['implement_type'] ) ? sanitize_key( $row['implement_type'] ) : 'replace_data';
			if ( ! in_array( $implement_type, [ 'replace_data', 'insert_after', 'insert_before' ], true ) ) {
				$implement_type = 'replace_data';
			}
			$clean_dis[] = [
				'data_selector'  => $data_selector,
				'implement_type' => $implement_type,
			];
		}

		$wrappers[] = [
			'key'                      => sanitize_key( (string) $key ),
			'event_type'               => $event_type,
			'custom_button_append'     => $custom_button_append,
			'button_trigger_selector'  => $button_trigger_selector,
			'button_label'             => ald_ajax_button_label( $button_label_raw ),
			'no_more_text'             => ald_ajax_button_label( $no_more_text_raw ),
			'click_selector'           => $active_click_selector,
			'trigger_offset'           => ( $is_pro && isset( $value['trigger_offset'] ) && '' !== $value['trigger_offset'] ) ? absint( $value['trigger_offset'] ) : 0,
			'hide_selector_wrapper'    => ( isset( $value['hide_selector_wrapper'] ) && 'yes' === $value['hide_selector_wrapper'] ) ? 'yes' : 'no',
			'wrapper_to_hide'          => isset( $value['wrapper_to_hide'] ) ? ald_sanitize_css_selector( $value['wrapper_to_hide'] ) : '',
			'update_browser_url'       => ( $is_pro && isset( $value['update_browser_url'] ) && 'yes' === $value['update_browser_url'] ) ? 'yes' : 'no',
			'update_page_title'        => ( $is_pro && isset( $value['update_page_title'] ) && 'yes' === $value['update_page_title'] ) ? 'yes' : 'no',
			'scroll_to_top'            => ( isset( $value['scroll_to_top'] ) && 'yes' === $value['scroll_to_top'] ) ? 'yes' : 'no',
			'data_implement_selectors' => $clean_dis,
		];
	}

	return $wrappers;
}

/**
 * Determine whether any frontend Load More rules or custom assets should be loaded.
 *
 * @return bool
 */
function ald_has_frontend_config() {
	$has_config = ! empty( ald_get_active_general_wrappers() )
		|| ! empty( ald_get_active_ajax_wrappers() )
		|| '' !== ald_sanitize_custom_css( get_ald_options( 'custom_css', '' ) )
		|| ( ald_is_pro() && '' !== trim( (string) get_ald_options( 'custom_js', '' ) ) );

	return (bool) apply_filters( 'ald_has_frontend_config', $has_config, get_ald_options() );
}

/**
 * Build dynamic inline CSS for hiding initial General items, hidden Ajax wrappers, and Custom CSS.
 *
 * @return string
 */
function ald_get_frontend_inline_css() {
	$css_chunks = [];

	if ( ! ald_is_elementor_preview() ) {
		$general_selectors = [];
		foreach ( ald_get_active_general_wrappers() as $wrapper ) {
			if ( '' !== $wrapper['load_selector'] ) {
				$general_selectors[] = $wrapper['load_selector'];
			}
		}
		if ( ! empty( $general_selectors ) ) {
			$css_chunks[] = implode( ', ', array_unique( $general_selectors ) ) . ' { display: none; }';
		}
	}

	$ajax_hide_selectors = [];
	foreach ( ald_get_active_ajax_wrappers() as $wrapper ) {
		if ( 'selectors_click' !== $wrapper['event_type'] && 'yes' === $wrapper['hide_selector_wrapper'] && '' !== $wrapper['wrapper_to_hide'] ) {
			$ajax_hide_selectors[] = $wrapper['wrapper_to_hide'];
		}
	}
	if ( ! empty( $ajax_hide_selectors ) ) {
		$css_chunks[] = implode( ', ', array_unique( $ajax_hide_selectors ) ) . ' { visibility: hidden; }';
	}

	$custom_css = ald_sanitize_custom_css( get_ald_options( 'custom_css', '' ) );
	if ( '' !== $custom_css ) {
		$css_chunks[] = $custom_css;
	}

	if ( empty( $css_chunks ) ) {
		return '';
	}

	return ald_minify_css( implode( "\n", $css_chunks ) );
}

/**
 * Build the structured frontend JS configuration localized to `ald_params`.
 *
 * @return array
 */
function ald_get_frontend_config() {
	return [
		'nonce'              => wp_create_nonce( 'ald_nonce' ),
		'ajaxurl'            => admin_url( 'admin-ajax.php' ),
		'ald_pro'            => ald_is_pro() ? '1' : '0',
		'isElementorPreview' => ald_is_elementor_preview(),
		'general'            => ald_get_active_general_wrappers(),
		'ajax'               => ald_get_active_ajax_wrappers(),
		'i18n'               => [
			'noMoreData'  => __( 'No more items to load', 'ajax-load-more-anything' ),
			'loading'     => __( 'Loading more items…', 'ajax-load-more-anything' ),
			'loadedItems' => __( 'Additional items loaded.', 'ajax-load-more-anything' ),
			'loadError'   => __( 'Unable to load more items. Please try again.', 'ajax-load-more-anything' ),
		],
	];
}

/**
 * Print top Ajax progress bar and polite ARIA live region only when Ajax wrappers are active.
 *
 * @return void
 */
function ald_ajax_laser_loader() {
	$has_ajax    = ! empty( ald_get_active_ajax_wrappers() );
	$has_general = ! empty( ald_get_active_general_wrappers() );

	if ( ! $has_ajax && ! $has_general ) {
		return;
	}

	if ( $has_ajax ) {
		echo '<div class="ald_laser_loader" aria-hidden="true"><div class="ald_loader_progress"></div></div>';
	}

	echo '<div class="ald-sr-live" aria-live="polite" aria-atomic="true"></div>';
}
add_action( 'wp_footer', 'ald_ajax_laser_loader' );
