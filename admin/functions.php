<?php
/**
 * Admin AJAX handlers, legacy option migration, selector presets, and wrapper templates.
 *
 * @package Ajax_Load_More_Anything
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve legacy pre-3.0 plugin options if still stored in wp_options.
 *
 * @return array
 */
function ald_old_options() {
	$old_options = [];
	$legacy_sets = [
		''  => 'default',
		'a' => 'default',
		'b' => 'default',
		'c' => 'default',
		'd' => 'default',
		'e' => 'flex',
	];

	foreach ( $legacy_sets as $suffix => $display_type ) {
		$wrapper_key = 'ald_wrapper_class' . $suffix;
		$wrapper_val = get_option( $wrapper_key );
		if ( ! empty( $wrapper_val ) ) {
			$old_options[ $wrapper_key ] = [
				'btn_selector'  => $wrapper_val,
				'load_selector' => get_option( 'ald_load_class' . $suffix ),
				'visible_items' => get_option( 'ald_item_show' . $suffix ),
				'load_items'    => get_option( 'ald_item_load' . $suffix ),
				'button_label'  => get_option( 'ald_load_label' . $suffix ),
				'display_type'  => $display_type,
			];
		}
	}

	$old_css = get_option( 'asr_ald_css_class' );
	if ( ! empty( $old_css ) ) {
		$old_options['custom_css'] = $old_css;
	}

	return $old_options;
}

/**
 * List all legacy pre-3.0 flat option keys.
 *
 * @return array
 */
function ald_old_options_keys() {
	$keys     = [];
	$suffixes = [ '', 'a', 'b', 'c', 'd', 'e' ];

	foreach ( $suffixes as $suffix ) {
		$keys[] = 'ald_wrapper_class' . $suffix;
		$keys[] = 'ald_load_class' . $suffix;
		$keys[] = 'ald_item_show' . $suffix;
		$keys[] = 'ald_item_load' . $suffix;
		$keys[] = 'ald_load_label' . $suffix;
	}

	$keys[] = 'asr_ald_css_class';

	return $keys;
}

/**
 * Back up and remove legacy flat options once ald_options is created or updated.
 *
 * @return void
 */
function update_option_ald_options_hook() {
	$legacy = ald_old_options();
	if ( empty( $legacy ) ) {
		return;
	}

	if ( false === get_option( 'ald_old_options' ) ) {
		update_option( 'ald_old_options', $legacy );
	}

	foreach ( ald_old_options_keys() as $option_key ) {
		if ( false !== get_option( $option_key ) ) {
			delete_option( $option_key );
		}
	}
}
add_action( 'update_option_ald_options', 'update_option_ald_options_hook', 10, 0 );
add_action( 'add_option_ald_options', 'update_option_ald_options_hook', 10, 0 );

/**
 * Provide one-click starter selector presets for common WordPress ecosystems.
 *
 * @return array
 */
function ald_get_selector_presets() {
	$presets = [
		'general' => [
			'list_items' => [
				'title'         => __( 'List Items', 'ajax-load-more-anything' ),
				'wrapper_title' => __( 'List Items', 'ajax-load-more-anything' ),
				'btn_selector'  => '.ald-list-wrapper',
				'load_selector' => '.ald-list-wrapper li',
				'visible_items' => '5',
				'load_items'    => '5',
				'button_label'  => __( 'Show +[count] More', 'ajax-load-more-anything' ),
				'display_type'  => 'default',
			],
			'table_rows' => [
				'title'         => __( 'Table Rows', 'ajax-load-more-anything' ),
				'wrapper_title' => __( 'Table Rows', 'ajax-load-more-anything' ),
				'btn_selector'  => '.ald-table-wrapper',
				'load_selector' => '.ald-table-wrapper tbody tr',
				'visible_items' => '6',
				'load_items'    => '6',
				'button_label'  => __( 'Load +[count] Rows', 'ajax-load-more-anything' ),
				'display_type'  => 'default',
			],
			'card_grid'  => [
				'title'            => __( 'Flex Card Grid', 'ajax-load-more-anything' ),
				'wrapper_title'    => __( 'Flex Card Grid', 'ajax-load-more-anything' ),
				'btn_selector'     => '.ald-cards-section',
				'load_selector'    => '.ald-cards-section .ald-card-item',
				'visible_items'    => '6',
				'load_items'       => '3',
				'button_label'     => __( 'Load +[count] More', 'ajax-load-more-anything' ),
				'display_type'     => 'default',
				'button_placement' => 'after',
			],
			'comments'   => [
				'title'         => __( 'WP Comments', 'ajax-load-more-anything' ),
				'wrapper_title' => __( 'Post Comments', 'ajax-load-more-anything' ),
				'btn_selector'  => '.comment-list',
				'load_selector' => '.comment-list > li.comment',
				'visible_items' => '5',
				'load_items'    => '5',
				'button_label'  => __( 'Load +[count] Comments', 'ajax-load-more-anything' ),
				'display_type'  => 'default',
			],
		],
		'ajax'    => [
			'wp_blog'     => [
				'title'                    => __( 'WordPress Blog Archive', 'ajax-load-more-anything' ),
				'wrapper_title'            => __( 'WordPress Blog Archive', 'ajax-load-more-anything' ),
				'event_type'               => 'selectors_click',
				'click_selector'           => '.site-main .navigation.pagination a.next, .site-main .nav-links a.next',
				'hide_selector_wrapper'    => 'no',
				'wrapper_to_hide'          => '.site-main .navigation.pagination, .site-main .navigation:not(.pagination) > .nav-links, .site-main > .nav-links',
				'data_implement_selectors' => [
					[
						'data_selector'  => '.site-main > article',
						'implement_type' => 'insert_after_elements',
					],
					[
						'data_selector'  => '.site-main .navigation.pagination, .site-main .navigation:not(.pagination) > .nav-links, .site-main > .nav-links',
						'implement_type' => 'replace_data',
					],
				],
			],
			'gutenberg'   => [
				'title'                    => __( 'Gutenberg Query Loop', 'ajax-load-more-anything' ),
				'wrapper_title'            => __( 'Gutenberg Query Loop', 'ajax-load-more-anything' ),
				'event_type'               => 'scroll_to_load',
				'click_selector'           => '.wp-block-query-pagination-next',
				'hide_selector_wrapper'    => 'yes',
				'wrapper_to_hide'          => '.wp-block-query-pagination',
				'data_implement_selectors' => [
					[
						'data_selector'  => '.wp-block-post-template',
						'implement_type' => 'insert_after',
					],
					[
						'data_selector'  => '.wp-block-query-pagination',
						'implement_type' => 'replace_data',
					],
				],
			],
			'woocommerce' => [
				'title'                    => __( 'WooCommerce Shop Grid', 'ajax-load-more-anything' ),
				'wrapper_title'            => __( 'WooCommerce Shop Grid', 'ajax-load-more-anything' ),
				'event_type'               => 'scroll_to_load',
				'click_selector'           => '.woocommerce-pagination a.next',
				'hide_selector_wrapper'    => 'yes',
				'wrapper_to_hide'          => '.woocommerce-pagination',
				'data_implement_selectors' => [
					[
						'data_selector'  => 'ul.products',
						'implement_type' => 'insert_after',
					],
					[
						'data_selector'  => '.woocommerce-pagination',
						'implement_type' => 'replace_data',
					],
				],
			],
			'elementor'   => [
				'title'                    => __( 'Elementor Posts / Loop', 'ajax-load-more-anything' ),
				'wrapper_title'            => __( 'Elementor Posts / Loop', 'ajax-load-more-anything' ),
				'event_type'               => 'selectors_click',
				'click_selector'           => '.elementor-pagination a.next',
				'hide_selector_wrapper'    => 'no',
				'wrapper_to_hide'          => '.elementor-pagination',
				'data_implement_selectors' => [
					[
						'data_selector'  => '.elementor-posts-container, .elementor-loop-container',
						'implement_type' => 'insert_after',
					],
					[
						'data_selector'  => '.elementor-pagination',
						'implement_type' => 'replace_data',
					],
				],
			],
		],
	];

	$collection                        = '.wp-block-woocommerce-product-collection';
	$presets['ajax']['woo_collection'] = ald_ajax_archive_preset(
		__( 'WooCommerce Product Collection', 'ajax-load-more-anything' ),
		$collection . ' .wc-block-product-template',
		$collection . ' .wp-block-query-pagination-next',
		$collection . ' .wp-block-query-pagination',
		true
	);

	$presets['ajax']['astra']         = ald_ajax_archive_preset( __( 'Astra Blog Archive', 'ajax-load-more-anything' ), '.site-main > .ast-row', '.ast-pagination a.next', '.ast-pagination' );
	$presets['ajax']['kadence']       = ald_ajax_archive_preset( __( 'Kadence Blog Archive', 'ajax-load-more-anything' ), '#archive-container', '.navigation.pagination a.next', '.navigation.pagination' );
	$presets['ajax']['generatepress'] = ald_ajax_archive_preset( __( 'GeneratePress Blog Archive', 'ajax-load-more-anything' ), '.site-main > article', '.paging-navigation a.next', '.paging-navigation', false, 'insert_after_elements' );
	$presets['ajax']['bricks']        = ald_ajax_archive_preset( __( 'Bricks Query Loop', 'ajax-load-more-anything' ), '.brxe-posts .bricks-layout-wrapper', '.bricks-pagination a.next, .brxe-pagination a.next', '.brxe-pagination, .bricks-pagination:not(.brxe-pagination .bricks-pagination)' );
	$presets['ajax']['divi']          = ald_ajax_archive_preset( __( 'Divi Blog Module (Fullwidth)', 'ajax-load-more-anything' ), '.et_pb_posts .et_pb_ajax_pagination_container > article, .et_pb_posts > article', '.et_pb_posts .pagination .alignleft a, .et_pb_posts .pagination a.next', '.et_pb_posts .pagination', false, 'insert_after_elements' );
	$presets['ajax']['blocksy']       = ald_ajax_archive_preset( __( 'Blocksy Blog Archive', 'ajax-load-more-anything' ), '.entries', '.ct-pagination a.next', '.ct-pagination' );
	$presets['ajax']['storefront']    = ald_ajax_archive_preset( __( 'Storefront Blog Archive', 'ajax-load-more-anything' ), '.site-main > article', '.site-main .navigation.pagination a.next', '.site-main .navigation.pagination', false, 'insert_after_elements' );

	$presets['general']['post_grid_master'] = [
		'title'            => __( 'Post Grid Master Cards', 'ajax-load-more-anything' ),
		'wrapper_title'    => __( 'Post Grid Master Cards', 'ajax-load-more-anything' ),
		'btn_selector'     => '.am_post_grid',
		'load_selector'    => '.am_post_grid > .am_grid_col',
		'visible_items'    => '6',
		'load_items'       => '3',
		'button_label'     => __( 'Load +[count] More', 'ajax-load-more-anything' ),
		'display_type'     => 'default',
		'button_placement' => 'after',
	];

	return $presets;
}

/**
 * Build an Ajax preset from a list and pagination selectors.
 *
 * @param string $title      Preset title.
 * @param string $items      Content selector.
 * @param string $next       Next-page link selector.
 * @param string $pagination Pagination selector.
 * @param bool   $scroll     Whether to use infinite scroll.
 * @param string $mode       Insertion mode for the content.
 * @return array
 */
function ald_ajax_archive_preset( $title, $items, $next, $pagination, $scroll = false, $mode = 'insert_after' ) {
	return [
		'title'                    => $title,
		'wrapper_title'            => $title,
		'event_type'               => $scroll ? 'scroll_to_load' : 'selectors_click',
		'click_selector'           => $next,
		'hide_selector_wrapper'    => $scroll ? 'yes' : 'no',
		'wrapper_to_hide'          => $pagination,
		'data_implement_selectors' => [
			[
				'data_selector'  => $items,
				'implement_type' => $mode,
			],
			[
				'data_selector'  => $pagination,
				'implement_type' => 'replace_data',
			],
		],
	];
}

/**
 * Suggest presets based on active plugins and the parent theme.
 *
 * @return array
 */
function ald_get_stack_presets() {
	$presets = [];
	$block   = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
	$theme   = strtolower( get_template() );

	if ( defined( 'WC_VERSION' ) ) {
		$presets[] = [ 'ajax', $block ? 'woo_collection' : 'woocommerce' ];
	}
	if ( defined( 'ELEMENTOR_VERSION' ) ) {
		$presets[] = [ 'ajax', 'elementor' ];
	}
	if ( $block ) {
		$presets[] = [ 'ajax', 'gutenberg' ];
	}
	if ( in_array( $theme, [ 'astra', 'kadence', 'generatepress', 'blocksy', 'storefront', 'divi', 'bricks' ], true ) ) {
		$presets[] = [ 'ajax', $theme ];
	}
	if ( defined( 'GRIDMASTER_VERSION' ) ) {
		$presets[] = [ 'general', 'post_grid_master' ];
	}

	return $presets;
}

/**
 * AJAX handler: Save plugin settings.
 *
 * @return void
 */
function ald_save_option_ajax_function() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => __( 'Permission denied.', 'ajax-load-more-anything' ) ], 403 );
	}

	$nonce = isset( $_POST['ald_nonce'] ) ? sanitize_key( wp_unslash( $_POST['ald_nonce'] ) ) : '';
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'alma_settings_nonce' ) ) {
		wp_send_json_error( [ 'message' => __( 'Security check failed. Please refresh and try again.', 'ajax-load-more-anything' ) ], 403 );
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized recursively via ald_before_options_save -> ALD_Menu::options_save().
	$raw_options = isset( $_POST['ald_options'] ) && is_array( $_POST['ald_options'] ) ? wp_unslash( $_POST['ald_options'] ) : [];
	$options     = apply_filters( 'ald_before_options_save', $raw_options );

	update_option( 'ald_options', $options );

	wp_send_json_success(
		[
			'message'       => __( 'Settings saved successfully.', 'ajax-load-more-anything' ),
			'general_count' => isset( $options['general_loadmore'] ) ? count( $options['general_loadmore'] ) : 0,
			'ajax_count'    => isset( $options['ajax_loadmore'] ) ? count( $options['ajax_loadmore'] ) : 0,
		]
	);
}
add_action( 'wp_ajax_ald_save_settings', 'ald_save_option_ajax_function' );

/**
 * AJAX handler: Render a new General wrapper card.
 *
 * @return void
 */
function ald_add_general_loadmore_action() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => __( 'Permission denied.', 'ajax-load-more-anything' ) ], 403 );
	}

	$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'lma-general-nonce' ) ) {
		wp_send_json_error( [ 'message' => __( 'Security check failed.', 'ajax-load-more-anything' ) ], 403 );
	}

	$thiskey = isset( $_POST['thiskey'] ) ? absint( wp_unslash( $_POST['thiskey'] ) ) : 0;

	if ( $thiskey >= 6 && ! ald_is_pro() ) {
		wp_send_json_success(
			[
				'limit_reached' => true,
				'html'          => '',
			]
		);
	}

	$key = 'gen_' . strtolower( wp_generate_password( 6, false ) );

	ob_start();
	ald_add_general_loadmore_wrap(
		[
			'key'      => $key,
			'thiskey'  => $thiskey,
			'selector' => [],
			'expanded' => true,
		]
	);
	$html = ob_get_clean();

	wp_send_json_success(
		[
			'limit_reached' => false,
			'html'          => $html,
		]
	);
}
add_action( 'wp_ajax_ald_add_general_loadmore', 'ald_add_general_loadmore_action' );

/**
 * Render a single General Load More wrapper card.
 *
 * @param array $args Wrapper configuration and index metadata.
 * @return void
 */
function ald_add_general_loadmore_wrap( $args ) {
	$args = wp_parse_args(
		$args,
		[
			'thiskey'  => 0,
			'key'      => '',
			'selector' => [],
			'expanded' => false,
		]
	);

	$selector = is_array( $args['selector'] ) ? $args['selector'] : [];
	$thiskey  = absint( $args['thiskey'] );
	$key      = sanitize_key( (string) $args['key'] );
	if ( '' === $key ) {
		$key = 'gen_' . strtolower( wp_generate_password( 6, false ) );
	}
	$group = 'general_loadmore';

	/* translators: %d: Wrapper number. */
	$default_title    = sprintf( __( 'General Wrapper #%d', 'ajax-load-more-anything' ), $thiskey + 1 );
	$wrapper_title    = ! empty( $selector['wrapper_title'] ) ? (string) $selector['wrapper_title'] : $default_title;
	$btn_selector     = isset( $selector['btn_selector'] ) ? (string) $selector['btn_selector'] : '';
	$load_selector    = isset( $selector['load_selector'] ) ? (string) $selector['load_selector'] : '';
	$visible_items    = isset( $selector['visible_items'] ) ? (string) $selector['visible_items'] : '';
	$load_items       = isset( $selector['load_items'] ) ? (string) $selector['load_items'] : '';
	$button_label     = isset( $selector['button_label'] ) ? (string) $selector['button_label'] : '';
	$display_type     = ald_sanitize_display_type( $selector['display_type'] ?? 'default' );
	$button_placement = $selector['button_placement'] ?? 'inside';
	$button_classes   = isset( $selector['button_classes'] ) ? (string) $selector['button_classes'] : '';
	$is_pro           = ald_is_pro();
	$expanded_class   = ! empty( $args['expanded'] ) ? ' opened' : '';

	ob_start();
	do_action( 'ald_general_loadmore_before_wrap', $args );
	?>
	<div class="ald-wrapper-card postbox tf_gen_sel_field<?php echo esc_attr( $expanded_class ); ?>" data-wrapper-key="<?php echo esc_attr( $key ); ?>">
		<div class="ald-wrapper-card__head ald-toggle-head" role="button" tabindex="0" aria-expanded="<?php echo ! empty( $args['expanded'] ) ? 'true' : 'false'; ?>">
			<div class="ald-wrapper-card__title-group">
				<span class="ald-wrapper-card__index" aria-hidden="true">#<?php echo esc_html( (string) ( $thiskey + 1 ) ); ?></span>
				<input
					type="text"
					class="ald_ajax_wrap_title"
					name="ald_options[general_loadmore][<?php echo esc_attr( $key ); ?>][wrapper_title]"
					value="<?php echo esc_attr( $wrapper_title ); ?>"
					aria-label="<?php esc_attr_e( 'Wrapper title', 'ajax-load-more-anything' ); ?>"
					title="<?php esc_attr_e( 'Rename this wrapper (e.g. Homepage List)', 'ajax-load-more-anything' ); ?>"
				/>
				<span class="ald-wrapper-card__summary">
					<?php
					if ( '' !== $load_selector ) {
						echo esc_html( $load_selector );
					} else {
						esc_html_e( 'No selector configured yet', 'ajax-load-more-anything' );
					}
					?>
				</span>
			</div>
			<div class="ald-wrapper-card__controls">
				<?php ald_wrapper_controls( $group, $key, $selector ); ?>
				<button type="button" class="ald-icon-btn ald-icon-btn--danger delete_field" title="<?php esc_attr_e( 'Remove wrapper', 'ajax-load-more-anything' ); ?>" aria-label="<?php esc_attr_e( 'Remove wrapper', 'ajax-load-more-anything' ); ?>">
					<span class="dashicons dashicons-trash" aria-hidden="true"></span>
				</button>
				<span class="ald-chevron indicator_field dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
			</div>
		</div>

		<div class="ald-wrapper-card__body ald-toggle-wrap" <?php echo empty( $args['expanded'] ) ? 'hidden' : ''; ?>>
			<div class="ald-field-grid">
				<div class="ald-field">
					<label for="general_loadmore-btn_selector-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Button Insert Wrapper Selector', 'ajax-load-more-anything' ); ?></label>
					<input id="general_loadmore-btn_selector-<?php echo esc_attr( $key ); ?>" class="ald-input ald-mono" type="text" name="ald_options[general_loadmore][<?php echo esc_attr( $key ); ?>][btn_selector]" value="<?php echo esc_attr( $btn_selector ); ?>" placeholder="#selector" />
					<p class="desc"><?php esc_html_e( 'The container used to position the Load More button.', 'ajax-load-more-anything' ); ?></p>
				</div>

				<div class="ald-field">
					<label for="general_loadmore-button_placement-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Button Placement', 'ajax-load-more-anything' ); ?></label>
					<select id="general_loadmore-button_placement-<?php echo esc_attr( $key ); ?>" class="ald-select" name="ald_options[general_loadmore][<?php echo esc_attr( $key ); ?>][button_placement]">
						<option value="inside" <?php selected( $button_placement, 'inside' ); ?>><?php esc_html_e( 'Inside the container', 'ajax-load-more-anything' ); ?></option>
						<option value="after" <?php selected( $button_placement, 'after' ); ?>><?php esc_html_e( 'After the container (Flex or Grid)', 'ajax-load-more-anything' ); ?></option>
					</select>
				</div>

				<div class="ald-field">
					<label for="general_loadmore-load_selector-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Repeated Items Selector', 'ajax-load-more-anything' ); ?></label>
					<input id="general_loadmore-load_selector-<?php echo esc_attr( $key ); ?>" class="ald-input ald-mono" type="text" name="ald_options[general_loadmore][<?php echo esc_attr( $key ); ?>][load_selector]" value="<?php echo esc_attr( $load_selector ); ?>" placeholder="#selector .repeated-item" />
					<p class="desc">
						<?php
						echo wp_kses(
							__( 'CSS selector matching each repeated item. Example: <code>.parent_selector .items</code>', 'ajax-load-more-anything' ),
							[ 'code' => [] ]
						);
						?>
					</p>
				</div>

				<div class="ald-field">
					<label for="general_loadmore-visible_items-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Visible Items Initially', 'ajax-load-more-anything' ); ?></label>
					<input id="general_loadmore-visible_items-<?php echo esc_attr( $key ); ?>" class="ald-input" type="number" min="0" name="ald_options[general_loadmore][<?php echo esc_attr( $key ); ?>][visible_items]" value="<?php echo esc_attr( $visible_items ); ?>" placeholder="6" />
					<p class="desc"><?php esc_html_e( 'How many items are visible before clicking Load More.', 'ajax-load-more-anything' ); ?></p>
				</div>

				<div class="ald-field">
					<label for="general_loadmore-load_items-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Items Per Click', 'ajax-load-more-anything' ); ?></label>
					<input id="general_loadmore-load_items-<?php echo esc_attr( $key ); ?>" class="ald-input" type="number" min="1" name="ald_options[general_loadmore][<?php echo esc_attr( $key ); ?>][load_items]" value="<?php echo esc_attr( $load_items ); ?>" placeholder="3" />
					<p class="desc"><?php esc_html_e( 'How many additional items reveal on each click.', 'ajax-load-more-anything' ); ?></p>
				</div>

				<div class="ald-field">
					<label for="general_loadmore-button_label-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Load More Button Label', 'ajax-load-more-anything' ); ?></label>
					<input id="general_loadmore-button_label-<?php echo esc_attr( $key ); ?>" class="ald-input" type="text" name="ald_options[general_loadmore][<?php echo esc_attr( $key ); ?>][button_label]" value="<?php echo esc_attr( $button_label ); ?>" placeholder="<?php esc_attr_e( 'Load +[count] more', 'ajax-load-more-anything' ); ?>" />
					<p class="desc">
						<?php
						echo wp_kses(
							__( 'Use <code>[count]</code> to display the remaining hidden count (e.g. <code>Load +[count] more</code>).', 'ajax-load-more-anything' ),
							[ 'code' => [] ]
						);
						?>
					</p>
				</div>

				<div class="ald-field">
					<div class="ald-field__label-row">
						<label for="general_loadmore-display_type-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Item Display Type', 'ajax-load-more-anything' ); ?></label>
						<?php ald_available_in_pro( 'display_type' ); ?>
					</div>
					<select id="general_loadmore-display_type-<?php echo esc_attr( $key ); ?>" class="ald-select" name="ald_options[general_loadmore][<?php echo esc_attr( $key ); ?>][display_type]">
						<?php
						foreach ( ald_display_types() as $option_value => $option ) :
							$option_is_pro = ! empty( $option['pro'] );
							$locked        = $option_is_pro && ! $is_pro;
							$pro_suffix    = $locked ? ' ' . __( '(Pro)', 'ajax-load-more-anything' ) : '';
							?>
							<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $display_type, $option_value ); ?> <?php disabled( $locked, true ); ?>>
								<?php echo esc_html( $option['label'] . $pro_suffix ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="desc"><?php esc_html_e( 'CSS display property applied when revealing hidden items.', 'ajax-load-more-anything' ); ?></p>
				</div>

				<div class="ald-field ald-field--full ald-pro-field">
					<div class="ald-field__label-row">
						<label for="general_loadmore-button_classes-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Custom Button Classes', 'ajax-load-more-anything' ); ?></label>
						<?php ald_available_in_pro( 'button_classes' ); ?>
					</div>
					<input id="general_loadmore-button_classes-<?php echo esc_attr( $key ); ?>" class="ald-input ald-mono" type="text" name="ald_options[general_loadmore][<?php echo esc_attr( $key ); ?>][button_classes]" value="<?php echo esc_attr( $button_classes ); ?>" placeholder="button btn-primary" <?php disabled( ! $is_pro, true ); ?> />
					<p class="desc"><?php esc_html_e( 'Space-separated CSS classes appended to the Load More button.', 'ajax-load-more-anything' ); ?></p>
				</div>
			</div>
		</div>
	</div>
	<?php
	$output = ob_get_clean();
	do_action( 'ald_general_loadmore_after_wrap', $output, $args );
}

/**
 * AJAX handler: Render a new Ajax wrapper card.
 *
 * @return void
 */
function ald_add_ajax_loadmore_action() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => __( 'Permission denied.', 'ajax-load-more-anything' ) ], 403 );
	}

	$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'lma-ajax-nonce' ) ) {
		wp_send_json_error( [ 'message' => __( 'Security check failed.', 'ajax-load-more-anything' ) ], 403 );
	}

	$thiskey = isset( $_POST['thiskey'] ) ? absint( wp_unslash( $_POST['thiskey'] ) ) : 0;

	if ( $thiskey >= 1 && ! ald_is_pro() ) {
		wp_send_json_success(
			[
				'limit_reached' => true,
				'html'          => '',
			]
		);
	}

	$key = 'ajax_' . strtolower( wp_generate_password( 6, false ) );

	ob_start();
	ald_add_ajax_loadmore_wrap(
		[
			'key'      => $key,
			'thiskey'  => $thiskey,
			'selector' => [],
			'expanded' => true,
		]
	);
	$html = ob_get_clean();

	wp_send_json_success(
		[
			'limit_reached' => false,
			'html'          => $html,
		]
	);
}
add_action( 'wp_ajax_ald_add_ajax_loadmore', 'ald_add_ajax_loadmore_action' );

/**
 * Render a single Ajax Load More wrapper card.
 *
 * @param array $args Wrapper configuration and index metadata.
 * @return void
 */
function ald_add_ajax_loadmore_wrap( $args ) {
	$args = wp_parse_args(
		$args,
		[
			'thiskey'  => 0,
			'key'      => '',
			'selector' => [],
			'expanded' => false,
		]
	);

	$selector = is_array( $args['selector'] ) ? $args['selector'] : [];
	$thiskey  = absint( $args['thiskey'] );
	$key      = sanitize_key( (string) $args['key'] );
	if ( '' === $key ) {
		$key = 'ajax_' . strtolower( wp_generate_password( 6, false ) );
	}
	$group = 'ajax_loadmore';

	/* translators: %d: Wrapper number. */
	$default_title            = sprintf( __( 'Ajax Wrapper #%d', 'ajax-load-more-anything' ), $thiskey + 1 );
	$wrapper_title            = ! empty( $selector['wrapper_title'] ) ? (string) $selector['wrapper_title'] : $default_title;
	$event_type               = ! empty( $selector['event_type'] ) ? (string) $selector['event_type'] : 'selectors_click';
	$custom_button_append     = isset( $selector['custom_button_append'] ) ? (string) $selector['custom_button_append'] : '';
	$button_trigger_selector  = isset( $selector['button_trigger_selector'] ) ? (string) $selector['button_trigger_selector'] : '';
	$button_label             = ! empty( $selector['button_label'] ) ? (string) $selector['button_label'] : __( 'Load More', 'ajax-load-more-anything' );
	$no_more_text             = isset( $selector['no_more_text'] ) ? (string) $selector['no_more_text'] : '';
	$click_selector           = isset( $selector['click_selector'] ) ? (string) $selector['click_selector'] : '';
	$trigger_offset           = isset( $selector['trigger_offset'] ) && '' !== $selector['trigger_offset'] ? (string) absint( $selector['trigger_offset'] ) : '300';
	$hide_selector_wrapper    = isset( $selector['hide_selector_wrapper'] ) ? (string) $selector['hide_selector_wrapper'] : 'no';
	$wrapper_to_hide          = isset( $selector['wrapper_to_hide'] ) ? (string) $selector['wrapper_to_hide'] : '';
	$update_browser_url       = isset( $selector['update_browser_url'] ) ? (string) $selector['update_browser_url'] : 'no';
	$update_page_title        = isset( $selector['update_page_title'] ) ? (string) $selector['update_page_title'] : 'no';
	$scroll_to_top            = isset( $selector['scroll_to_top'] ) ? (string) $selector['scroll_to_top'] : 'no';
	$data_implement_selectors = isset( $selector['data_implement_selectors'] ) && is_array( $selector['data_implement_selectors'] ) ? $selector['data_implement_selectors'] : [];
	$is_pro                   = ald_is_pro();
	if ( ! $is_pro ) {
		$update_browser_url = 'no';
		$update_page_title  = 'no';
		if ( 'custom_button' === $event_type ) {
			$event_type = 'selectors_click';
		}
	}
	$expanded_class = ! empty( $args['expanded'] ) ? ' opened' : '';

	$event_labels = [
		'selectors_click' => __( 'Selector Click', 'ajax-load-more-anything' ),
		'scroll_to_load'  => __( 'Infinite Scroll', 'ajax-load-more-anything' ),
		'custom_button'   => __( 'Load More Button', 'ajax-load-more-anything' ),
	];
	$event_badge  = isset( $event_labels[ $event_type ] ) ? $event_labels[ $event_type ] : $event_labels['selectors_click'];

	ob_start();
	do_action( 'ald_ajax_loadmore_before_wrap', $args );
	?>
	<div class="ald-wrapper-card postbox tf_ajax_sel_field<?php echo esc_attr( $expanded_class ); ?>" data-wrapper-key="<?php echo esc_attr( $key ); ?>">
		<div class="ald-wrapper-card__head ald-toggle-head" role="button" tabindex="0" aria-expanded="<?php echo ! empty( $args['expanded'] ) ? 'true' : 'false'; ?>">
			<div class="ald-wrapper-card__title-group">
				<span class="ald-wrapper-card__index" aria-hidden="true">#<?php echo esc_html( (string) ( $thiskey + 1 ) ); ?></span>
				<input
					type="text"
					class="ald_ajax_wrap_title"
					name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][wrapper_title]"
					value="<?php echo esc_attr( $wrapper_title ); ?>"
					aria-label="<?php esc_attr_e( 'Ajax wrapper title', 'ajax-load-more-anything' ); ?>"
					title="<?php esc_attr_e( 'Rename this wrapper (e.g. Blog Posts)', 'ajax-load-more-anything' ); ?>"
				/>
				<span class="ald-pill ald-pill--accent ald-event-pill"><?php echo esc_html( $event_badge ); ?></span>
			</div>
			<div class="ald-wrapper-card__controls">
				<?php ald_wrapper_controls( $group, $key, $selector ); ?>
				<button type="button" class="ald-icon-btn ald-icon-btn--danger delete_field" title="<?php esc_attr_e( 'Remove wrapper', 'ajax-load-more-anything' ); ?>" aria-label="<?php esc_attr_e( 'Remove wrapper', 'ajax-load-more-anything' ); ?>">
					<span class="dashicons dashicons-trash" aria-hidden="true"></span>
				</button>
				<span class="ald-chevron indicator_field dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
			</div>
		</div>

		<div class="ald-wrapper-card__body ald-toggle-wrap" <?php echo empty( $args['expanded'] ) ? 'hidden' : ''; ?>>
			<div class="ald-field-grid">
				<div class="ald-field">
					<div class="ald-field__label-row">
						<label for="ajax_loadmore-event_type-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Event Trigger Type', 'ajax-load-more-anything' ); ?></label>
						<?php ald_available_in_pro( 'custom_button' ); ?>
					</div>
					<select id="ajax_loadmore-event_type-<?php echo esc_attr( $key ); ?>" data-pro-val="custom_button" class="option-select-lmapro-modal-trigger ald-select ajax_loadmore-event_type" name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][event_type]">
						<option value="selectors_click" <?php selected( $event_type, 'selectors_click' ); ?>><?php esc_html_e( 'Selector(s) Click (Ajax Pagination)', 'ajax-load-more-anything' ); ?></option>
						<option value="scroll_to_load" <?php selected( $event_type, 'scroll_to_load' ); ?>><?php esc_html_e( 'Scroll to Load (Infinite Scroll)', 'ajax-load-more-anything' ); ?></option>
						<option value="custom_button" <?php selected( $event_type, 'custom_button' ); ?>><?php echo esc_html( __( 'Add Load More Button', 'ajax-load-more-anything' ) . ( $is_pro ? '' : ' ' . __( '(Pro)', 'ajax-load-more-anything' ) ) ); ?></option>
					</select>
				</div>

				<div class="ald-field" data-id="click_selector">
					<label for="ajax_loadmore-click_selector-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Pagination Link Selector', 'ajax-load-more-anything' ); ?></label>
					<input id="ajax_loadmore-click_selector-<?php echo esc_attr( $key ); ?>" class="ald-input ald-mono" type="text" name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][click_selector]" value="<?php echo esc_attr( $click_selector ); ?>" placeholder=".nav-links a.next" />
					<p class="desc"><?php esc_html_e( 'Selector of the next-page or pagination link to intercept.', 'ajax-load-more-anything' ); ?></p>
				</div>

				<div class="ald-field" data-id="custom_button_append">
					<label for="ajax_loadmore-custom_button_append-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Button Insert After Selector', 'ajax-load-more-anything' ); ?></label>
					<input id="ajax_loadmore-custom_button_append-<?php echo esc_attr( $key ); ?>" class="ald-input ald-mono" type="text" name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][custom_button_append]" value="<?php echo esc_attr( $custom_button_append ); ?>" placeholder=".posts-wrapper" />
					<p class="desc"><?php esc_html_e( 'The custom Ajax button is inserted immediately after this element.', 'ajax-load-more-anything' ); ?></p>
				</div>

				<div class="ald-field" data-id="button_trigger_selector">
					<label for="ajax_loadmore-button_trigger_selector-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Underlying Next-Link Selector', 'ajax-load-more-anything' ); ?></label>
					<input id="ajax_loadmore-button_trigger_selector-<?php echo esc_attr( $key ); ?>" class="ald-input ald-mono" type="text" name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][button_trigger_selector]" value="<?php echo esc_attr( $button_trigger_selector ); ?>" placeholder=".nav-links a.next" />
					<p class="desc"><?php esc_html_e( 'Triggered automatically when visitors click your Load More button.', 'ajax-load-more-anything' ); ?></p>
				</div>

				<div class="ald-field" data-id="button_label_row">
					<label for="ajax_loadmore-button_label-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Button Label', 'ajax-load-more-anything' ); ?></label>
					<input id="ajax_loadmore-button_label-<?php echo esc_attr( $key ); ?>" class="ald-input" type="text" name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][button_label]" value="<?php echo esc_attr( $button_label ); ?>" placeholder="<?php esc_attr_e( 'Load More', 'ajax-load-more-anything' ); ?>" />
				</div>

				<div class="ald-field" data-id="no_more_text_row">
					<label for="ajax_loadmore-no_more_text-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'No More Items Label', 'ajax-load-more-anything' ); ?></label>
					<input id="ajax_loadmore-no_more_text-<?php echo esc_attr( $key ); ?>" class="ald-input" type="text" name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][no_more_text]" value="<?php echo esc_attr( $no_more_text ); ?>" placeholder="<?php esc_attr_e( 'No more items to load', 'ajax-load-more-anything' ); ?>" />
				</div>

				<div class="ald-field ajax_loadmore-trigger_offset ald-pro-field" data-id="trigger_offset">
					<div class="ald-field__label-row">
						<label for="ajax_loadmore-trigger_offset-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Infinite Scroll Trigger Offset (px)', 'ajax-load-more-anything' ); ?></label>
						<?php ald_available_in_pro( 'scroll_offset' ); ?>
					</div>
					<input id="ajax_loadmore-trigger_offset-<?php echo esc_attr( $key ); ?>" class="ald-input" type="number" min="0" name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][trigger_offset]" value="<?php echo esc_attr( $trigger_offset ); ?>" placeholder="300" <?php disabled( ! $is_pro, true ); ?> />
					<p class="desc"><?php esc_html_e( 'Pixels before the pagination link enters the viewport to pre-fetch the next page.', 'ajax-load-more-anything' ); ?></p>
				</div>

				<div class="ald-field" data-id="hide_selector_wrapper">
					<label for="ajax_loadmore-hide_selector_wrapper-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Hide Original Pagination Wrapper?', 'ajax-load-more-anything' ); ?></label>
					<select id="ajax_loadmore-hide_selector_wrapper-<?php echo esc_attr( $key ); ?>" class="ald-select ajax_loadmore-hide_selector_wrapper" name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][hide_selector_wrapper]">
						<option value="no" <?php selected( $hide_selector_wrapper, 'no' ); ?>><?php esc_html_e( 'No', 'ajax-load-more-anything' ); ?></option>
						<option value="yes" <?php selected( $hide_selector_wrapper, 'yes' ); ?>><?php esc_html_e( 'Yes', 'ajax-load-more-anything' ); ?></option>
					</select>
				</div>

				<div class="ald-field" data-id="wrapper_to_hide">
					<label for="ajax_loadmore-wrapper_to_hide-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Pagination Wrapper to Hide', 'ajax-load-more-anything' ); ?></label>
					<input id="ajax_loadmore-wrapper_to_hide-<?php echo esc_attr( $key ); ?>" class="ald-input ald-mono" type="text" name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][wrapper_to_hide]" value="<?php echo esc_attr( $wrapper_to_hide ); ?>" placeholder=".navigation-area" />
					<p class="desc"><?php esc_html_e( 'Hidden visually via CSS while staying accessible to the trigger logic.', 'ajax-load-more-anything' ); ?></p>
				</div>

				<div class="ald-field">
					<label for="ajax_loadmore-scroll_to_top-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Scroll to Container After Load?', 'ajax-load-more-anything' ); ?></label>
					<select id="ajax_loadmore-scroll_to_top-<?php echo esc_attr( $key ); ?>" class="ald-select" name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][scroll_to_top]">
						<option value="no" <?php selected( $scroll_to_top, 'no' ); ?>><?php esc_html_e( 'No', 'ajax-load-more-anything' ); ?></option>
						<option value="yes" <?php selected( $scroll_to_top, 'yes' ); ?>><?php esc_html_e( 'Yes (smooth scroll to top of updated container)', 'ajax-load-more-anything' ); ?></option>
					</select>
				</div>

				<div class="ald-field">
					<div class="ald-field__label-row">
						<label for="ajax_loadmore-update_browser_url-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Update Browser URL?', 'ajax-load-more-anything' ); ?></label>
						<?php ald_available_in_pro( 'url_sync' ); ?>
					</div>
					<select id="ajax_loadmore-update_browser_url-<?php echo esc_attr( $key ); ?>" class="ald-select" name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][update_browser_url]">
						<option value="no" <?php selected( $update_browser_url, 'no' ); ?>><?php esc_html_e( 'No', 'ajax-load-more-anything' ); ?></option>
						<option value="yes" <?php disabled( ! $is_pro, true ); ?> <?php selected( $update_browser_url, 'yes' ); ?>><?php echo esc_html( __( 'Yes', 'ajax-load-more-anything' ) . ( $is_pro ? '' : ' ' . __( '(Pro)', 'ajax-load-more-anything' ) ) ); ?></option>
					</select>
				</div>

				<div class="ald-field">
					<div class="ald-field__label-row">
						<label for="ajax_loadmore-update_page_title-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Update Page Title?', 'ajax-load-more-anything' ); ?></label>
						<?php ald_available_in_pro( 'page_title' ); ?>
					</div>
					<select id="ajax_loadmore-update_page_title-<?php echo esc_attr( $key ); ?>" class="ald-select" name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][update_page_title]">
						<option value="no" <?php selected( $update_page_title, 'no' ); ?>><?php esc_html_e( 'No', 'ajax-load-more-anything' ); ?></option>
						<option value="yes" <?php disabled( ! $is_pro, true ); ?> <?php selected( $update_page_title, 'yes' ); ?>><?php echo esc_html( __( 'Yes', 'ajax-load-more-anything' ) . ( $is_pro ? '' : ' ' . __( '(Pro)', 'ajax-load-more-anything' ) ) ); ?></option>
					</select>
				</div>

				<div class="ald-field ald-field--full">
					<label><?php esc_html_e( 'Data Implement Selectors', 'ajax-load-more-anything' ); ?></label>
					<p class="desc"><?php esc_html_e( 'Choose which containers from the fetched page should be appended, prepended, or replaced (tip: add a second row with "Replace Data" for the pagination container).', 'ajax-load-more-anything' ); ?></p>

					<div class="data_implement_selectors_wrap ald-dis-box">
						<table class="ald-dis-table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Target CSS Selector', 'ajax-load-more-anything' ); ?></th>
									<th><?php esc_html_e( 'Insert Mode', 'ajax-load-more-anything' ); ?></th>
									<th class="ald-dis-actions-col"><?php esc_html_e( 'Actions', 'ajax-load-more-anything' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$rendered_rows = 0;
								foreach ( $data_implement_selectors as $dis_key => $row ) :
									$data_selector  = isset( $row['data_selector'] ) ? (string) $row['data_selector'] : '';
									$implement_type = isset( $row['implement_type'] ) ? (string) $row['implement_type'] : 'replace_data';
									if ( '' === $data_selector ) {
										continue;
									}
									++$rendered_rows;
									?>
									<tr class="data_implement_selectors_row">
										<td>
											<input class="ald-input ald-mono" type="text" name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][data_implement_selectors][<?php echo esc_attr( $dis_key ); ?>][data_selector]" value="<?php echo esc_attr( $data_selector ); ?>" placeholder=".posts-wrapper" />
										</td>
										<td>
											<select class="ald-select" name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][data_implement_selectors][<?php echo esc_attr( $dis_key ); ?>][implement_type]">
												<option value="replace_data" <?php selected( $implement_type, 'replace_data' ); ?>><?php esc_html_e( 'Replace Data', 'ajax-load-more-anything' ); ?></option>
												<option value="insert_after" <?php selected( $implement_type, 'insert_after' ); ?>><?php esc_html_e( 'Insert After (Append)', 'ajax-load-more-anything' ); ?></option>
																<option value="insert_after_elements" <?php selected( $implement_type, 'insert_after_elements' ); ?>><?php esc_html_e( 'Insert After Matching Elements', 'ajax-load-more-anything' ); ?></option>
												<option value="insert_before" <?php selected( $implement_type, 'insert_before' ); ?>><?php esc_html_e( 'Insert Before (Prepend)', 'ajax-load-more-anything' ); ?></option>
											</select>
										</td>
										<td>
											<div class="disr_action">
												<button type="button" class="ald-mini-btn delete_disr" title="<?php esc_attr_e( 'Remove row', 'ajax-load-more-anything' ); ?>" aria-label="<?php esc_attr_e( 'Remove row', 'ajax-load-more-anything' ); ?>">&minus;</button>
												<button type="button" class="ald-mini-btn add_disr" title="<?php esc_attr_e( 'Add row', 'ajax-load-more-anything' ); ?>" aria-label="<?php esc_attr_e( 'Add row', 'ajax-load-more-anything' ); ?>">&plus;</button>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>

								<?php if ( 0 === $rendered_rows ) : ?>
									<tr class="data_implement_selectors_row">
										<td>
											<input class="ald-input ald-mono" type="text" name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][data_implement_selectors][0][data_selector]" placeholder="<?php esc_attr_e( 'e.g. ul.products or .posts-wrapper', 'ajax-load-more-anything' ); ?>" />
										</td>
										<td>
											<select class="ald-select" name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][data_implement_selectors][0][implement_type]">
												<option value="insert_after"><?php esc_html_e( 'Insert After (Append)', 'ajax-load-more-anything' ); ?></option>
												<option value="insert_after_elements"><?php esc_html_e( 'Insert After Matching Elements', 'ajax-load-more-anything' ); ?></option>
												<option value="replace_data"><?php esc_html_e( 'Replace Data', 'ajax-load-more-anything' ); ?></option>
												<option value="insert_before"><?php esc_html_e( 'Insert Before (Prepend)', 'ajax-load-more-anything' ); ?></option>
											</select>
										</td>
										<td>
											<div class="disr_action">
												<button type="button" class="ald-mini-btn delete_disr" title="<?php esc_attr_e( 'Remove row', 'ajax-load-more-anything' ); ?>" aria-label="<?php esc_attr_e( 'Remove row', 'ajax-load-more-anything' ); ?>">&minus;</button>
												<button type="button" class="ald-mini-btn add_disr" title="<?php esc_attr_e( 'Add row', 'ajax-load-more-anything' ); ?>" aria-label="<?php esc_attr_e( 'Add row', 'ajax-load-more-anything' ); ?>">&plus;</button>
											</div>
										</td>
									</tr>
								<?php endif; ?>

								<tr class="data_implement_selectors_row disr_empty-row" hidden>
									<td>
										<input class="ald-input ald-mono" type="text" disabled name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][data_implement_selectors][__ROW__][data_selector]" placeholder="<?php esc_attr_e( 'e.g. .woocommerce-pagination', 'ajax-load-more-anything' ); ?>" />
									</td>
									<td>
										<select class="ald-select" disabled name="ald_options[ajax_loadmore][<?php echo esc_attr( $key ); ?>][data_implement_selectors][__ROW__][implement_type]">
											<option value="replace_data"><?php esc_html_e( 'Replace Data', 'ajax-load-more-anything' ); ?></option>
											<option value="insert_after"><?php esc_html_e( 'Insert After (Append)', 'ajax-load-more-anything' ); ?></option>
											<option value="insert_after_elements"><?php esc_html_e( 'Insert After Matching Elements', 'ajax-load-more-anything' ); ?></option>
											<option value="insert_before"><?php esc_html_e( 'Insert Before (Prepend)', 'ajax-load-more-anything' ); ?></option>
										</select>
									</td>
									<td>
										<div class="disr_action">
											<button type="button" class="ald-mini-btn delete_disr" title="<?php esc_attr_e( 'Remove row', 'ajax-load-more-anything' ); ?>" aria-label="<?php esc_attr_e( 'Remove row', 'ajax-load-more-anything' ); ?>">&minus;</button>
											<button type="button" class="ald-mini-btn add_disr" title="<?php esc_attr_e( 'Add row', 'ajax-load-more-anything' ); ?>" aria-label="<?php esc_attr_e( 'Add row', 'ajax-load-more-anything' ); ?>">&plus;</button>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	$output = ob_get_clean();
	do_action( 'ald_ajax_loadmore_after_wrap', $output, $args );
}

/**
 * Render controls shared by General and Ajax wrapper cards.
 *
 * @param string $group    Saved wrapper group.
 * @param string $key      Wrapper key.
 * @param array  $selector Saved wrapper configuration.
 * @return void
 */
function ald_wrapper_controls( $group, $key, $selector ) {
	$enabled = 'no' !== ( $selector['enabled'] ?? 'yes' );
	?>
	<button type="button" class="ald-mini-btn ald-wrapper-enable" aria-pressed="<?php echo $enabled ? 'true' : 'false'; ?>" aria-label="<?php esc_attr_e( 'Enable wrapper', 'ajax-load-more-anything' ); ?>">
		<?php
		echo $enabled
			? esc_html__( 'Enabled', 'ajax-load-more-anything' )
			: esc_html__( 'Disabled', 'ajax-load-more-anything' );
		?>
	</button>
	<input type="hidden" class="ald-wrapper-enabled" name="ald_options[<?php echo esc_attr( $group ); ?>][<?php echo esc_attr( $key ); ?>][enabled]" value="<?php echo $enabled ? 'yes' : 'no'; ?>" />
	<button type="button" class="ald-mini-btn ald-wrapper-duplicate"><?php esc_html_e( 'Duplicate', 'ajax-load-more-anything' ); ?></button>
	<?php
}

/**
 * Render the primary Save Changes submit button.
 *
 * @return void
 */
function ald_ajax_save_btn() {
	?>
	<button type="submit" class="ald-btn ald-btn--primary am_submit_button">
		<span class="am_spinner" aria-hidden="true"></span>
		<span class="ald-submit-label"><?php esc_html_e( 'Save Changes', 'ajax-load-more-anything' ); ?></span>
	</button>
	<?php
}

/**
 * Render the plugin title inside the topbar.
 *
 * @return void
 */
function ald_plugin_name_data() {
	?>
	<h2 class="ald-brand-title">
		<?php esc_html_e( 'Load More Anything', 'ajax-load-more-anything' ); ?>
	</h2>
	<?php
}
add_action( 'ald_left_panel', 'ald_plugin_name_data', 10 );

/**
 * Get the localized copy and upgrade URL for each Pro entry point.
 *
 * @return array
 */
function ald_get_pro_contexts() {
	if ( ald_is_pro() ) {
		return [];
	}

	$contexts = [
		'general'        => [
			'title'       => __( 'Upgrade to Load More Anything Pro', 'ajax-load-more-anything' ),
			'description' => __( 'Unlock unlimited General & Ajax wrappers, custom Ajax buttons, browser URL/Title sync, trigger offset, and custom JavaScript.', 'ajax-load-more-anything' ),
		],
		'sidebar_card'   => [
			'title'       => __( 'Compare Free and Pro', 'ajax-load-more-anything' ),
			'description' => __( 'See which wrapper limits, pagination controls, and customization options come with each edition.', 'ajax-load-more-anything' ),
		],
		'limit_general'  => [
			'title'       => __( 'Add Unlimited General Wrappers', 'ajax-load-more-anything' ),
			'description' => __( 'Use separate Load More settings for more than six lists, tables, or grids.', 'ajax-load-more-anything' ),
		],
		'limit_ajax'     => [
			'title'       => __( 'Add Unlimited Ajax Wrappers', 'ajax-load-more-anything' ),
			'description' => __( 'Configure more than one paginated archive or grid with its own Ajax settings.', 'ajax-load-more-anything' ),
		],
		'custom_button'  => [
			'title'       => __( 'Add an Ajax Load More Button', 'ajax-load-more-anything' ),
			'description' => __( 'Place a custom button after your content and use your existing pagination link to fetch the next page.', 'ajax-load-more-anything' ),
		],
		'url_sync'       => [
			'title'       => __( 'Update the Browser URL as Items Load', 'ajax-load-more-anything' ),
			'description' => __( 'Sync the address bar with the page that just loaded so visitors can bookmark it or navigate back.', 'ajax-load-more-anything' ),
		],
		'page_title'     => [
			'title'       => __( 'Update the Page Title as Items Load', 'ajax-load-more-anything' ),
			'description' => __( 'Show the title of the fetched page in the browser tab.', 'ajax-load-more-anything' ),
		],
		'scroll_offset'  => [
			'title'       => __( 'Start Loading Before Pagination Enters View', 'ajax-load-more-anything' ),
			'description' => __( 'Set the number of pixels before the pagination trigger at which the next page should load.', 'ajax-load-more-anything' ),
		],
		'display_type'   => [
			'title'       => __( 'Unlock More Item Display Types', 'ajax-load-more-anything' ),
			'description' => __( 'Choose a CSS display value such as grid, inline-block, or table-row for revealed items.', 'ajax-load-more-anything' ),
		],
		'button_classes' => [
			'title'       => __( 'Style the Load More Button With Your Own Classes', 'ajax-load-more-anything' ),
			'description' => __( 'Add CSS classes from your theme or design system to the General Load More button.', 'ajax-load-more-anything' ),
		],
		'custom_js'      => [
			'title'       => __( 'Run Custom JavaScript After Items Load', 'ajax-load-more-anything' ),
			'description' => __( 'Add callbacks for your lightbox, layout, or analytics after content loads.', 'ajax-load-more-anything' ),
		],
	];

	foreach ( $contexts as $surface => &$context ) {
		$context['url'] = ald_get_pro_url( $surface );
	}

	return $contexts;
}

/**
 * Render the review request when the current administrator is eligible.
 *
 * @return void
 */
function ald_review_card() {
	$installed = absint( get_option( 'ald_installed', 0 ) );
	if ( ! $installed ) {
		add_option( 'ald_installed', time(), '', false );
		return;
	}

	if ( time() - $installed < 14 * DAY_IN_SECONDS || get_user_meta( get_current_user_id(), 'ald_review_dismissed', true ) ) {
		return;
	}

	foreach ( [ 'general_loadmore', 'ajax_loadmore' ] as $group ) {
		foreach ( (array) get_ald_options( $group, [] ) as $wrapper ) {
			if ( is_array( $wrapper ) && ( ( ! empty( $wrapper['btn_selector'] ) && ! empty( $wrapper['load_selector'] ) ) || ! empty( $wrapper['click_selector'] ) || ! empty( $wrapper['button_trigger_selector'] ) ) ) {
				?>
				<div class="ald-card ald-review-card">
					<h3><?php esc_html_e( 'How is Load More Anything working for you?', 'ajax-load-more-anything' ); ?></h3>
					<p><?php esc_html_e( 'Share your experience to help other WordPress users decide whether the plugin fits their site.', 'ajax-load-more-anything' ); ?></p>
					<a class="ald-btn ald-btn--secondary" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( 'https://wordpress.org/support/plugin/ajax-load-more-anything/reviews/#new-post' ); ?>">
						<?php esc_html_e( 'Write a Review', 'ajax-load-more-anything' ); ?>
					</a>
					<button type="button" class="ald-btn ald-btn--ghost ald-dismiss-review" data-nonce="<?php echo esc_attr( wp_create_nonce( 'ald_dismiss_review' ) ); ?>">
						<?php esc_html_e( 'Dismiss', 'ajax-load-more-anything' ); ?>
					</button>
				</div>
				<?php
				return;
			}
		}
	}
}

/**
 * Dismiss the review card for the current administrator.
 *
 * @return void
 */
function ald_dismiss_review() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => __( 'Permission denied.', 'ajax-load-more-anything' ) ], 403 );
	}

	$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'ald_dismiss_review' ) ) {
		wp_send_json_error( [ 'message' => __( 'Security check failed.', 'ajax-load-more-anything' ) ], 403 );
	}

	$user_id = get_current_user_id();
	if ( ! update_user_meta( $user_id, 'ald_review_dismissed', 'yes' ) && 'yes' !== get_user_meta( $user_id, 'ald_review_dismissed', true ) ) {
		wp_send_json_error( [ 'message' => __( 'The review card could not be dismissed. Please try again.', 'ajax-load-more-anything' ) ], 500 );
	}

	wp_send_json_success();
}
add_action( 'wp_ajax_ald_dismiss_review', 'ald_dismiss_review' );

/**
 * Render the Free vs Pro comparison modal (scoped to the plugin settings screen only).
 *
 * @return void
 */
function ald_plugin_pro_modal() {
	if ( ald_is_pro() ) {
		return;
	}
	?>
	<div id="ald_go-pro" class="am_go-pro-modal-outer ald-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="ald-pro-modal-title" hidden>
		<div class="am_shadow am-modal-close"></div>
		<div class="am_go-pro-modal-inner ald-modal-card">
			<button type="button" class="am-modal-close ald-modal-close" aria-label="<?php esc_attr_e( 'Close modal', 'ajax-load-more-anything' ); ?>">&times;</button>
			<div class="ald-modal-header">
				<span class="ald-badge ald-badge--pro"><?php esc_html_e( 'Pro Upgrade', 'ajax-load-more-anything' ); ?></span>
				<h2 id="ald-pro-modal-title"><?php esc_html_e( 'Upgrade to Load More Anything Pro', 'ajax-load-more-anything' ); ?></h2>
				<p class="ald-pro-modal-description"><?php esc_html_e( 'Unlock unlimited General & Ajax wrappers, custom Ajax buttons, browser URL/Title sync, trigger offset, and custom JavaScript.', 'ajax-load-more-anything' ); ?></p>
				<a class="ald-btn ald-btn--primary ald-pro-modal-cta" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( ald_get_pro_url() ); ?>">
					<?php esc_html_e( 'Upgrade to Pro', 'ajax-load-more-anything' ); ?>
					<span class="dashicons dashicons-external" aria-hidden="true"></span>
				</a>
			</div>
			<div class="ald-modal-body">
				<table class="ald-compare-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Feature', 'ajax-load-more-anything' ); ?></th>
							<th><?php esc_html_e( 'Free', 'ajax-load-more-anything' ); ?></th>
							<th><?php esc_html_e( 'Pro', 'ajax-load-more-anything' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?php esc_html_e( 'General (DOM) Wrappers', 'ajax-load-more-anything' ); ?></td>
							<td><?php esc_html_e( 'Up to 6', 'ajax-load-more-anything' ); ?></td>
							<td><strong><?php esc_html_e( 'Unlimited', 'ajax-load-more-anything' ); ?></strong></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Ajax Based Wrappers', 'ajax-load-more-anything' ); ?></td>
							<td><?php esc_html_e( '1', 'ajax-load-more-anything' ); ?></td>
							<td><strong><?php esc_html_e( 'Unlimited', 'ajax-load-more-anything' ); ?></strong></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Custom Ajax Load More Button', 'ajax-load-more-anything' ); ?></td>
							<td><span class="dashicons dashicons-no-alt" aria-label="<?php esc_attr_e( 'Not included', 'ajax-load-more-anything' ); ?>"></span></td>
							<td><span class="dashicons dashicons-yes-alt" aria-label="<?php esc_attr_e( 'Included', 'ajax-load-more-anything' ); ?>"></span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Dynamic Browser URL & Page Title Sync', 'ajax-load-more-anything' ); ?></td>
							<td><span class="dashicons dashicons-no-alt" aria-label="<?php esc_attr_e( 'Not included', 'ajax-load-more-anything' ); ?>"></span></td>
							<td><span class="dashicons dashicons-yes-alt" aria-label="<?php esc_attr_e( 'Included', 'ajax-load-more-anything' ); ?>"></span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Infinite Scroll Trigger Offset', 'ajax-load-more-anything' ); ?></td>
							<td><span class="dashicons dashicons-no-alt" aria-label="<?php esc_attr_e( 'Not included', 'ajax-load-more-anything' ); ?>"></span></td>
							<td><span class="dashicons dashicons-yes-alt" aria-label="<?php esc_attr_e( 'Included', 'ajax-load-more-anything' ); ?>"></span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Extra CSS Display Types & Button Classes', 'ajax-load-more-anything' ); ?></td>
							<td><span class="dashicons dashicons-no-alt" aria-label="<?php esc_attr_e( 'Not included', 'ajax-load-more-anything' ); ?>"></span></td>
							<td><span class="dashicons dashicons-yes-alt" aria-label="<?php esc_attr_e( 'Included', 'ajax-load-more-anything' ); ?>"></span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Custom CSS', 'ajax-load-more-anything' ); ?></td>
							<td><span class="dashicons dashicons-yes-alt" aria-label="<?php esc_attr_e( 'Included', 'ajax-load-more-anything' ); ?>"></span></td>
							<td><span class="dashicons dashicons-yes-alt" aria-label="<?php esc_attr_e( 'Included', 'ajax-load-more-anything' ); ?>"></span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Custom JavaScript & Priority Support', 'ajax-load-more-anything' ); ?></td>
							<td><span class="dashicons dashicons-no-alt" aria-label="<?php esc_attr_e( 'Not included', 'ajax-load-more-anything' ); ?>"></span></td>
							<td><span class="dashicons dashicons-yes-alt" aria-label="<?php esc_attr_e( 'Included', 'ajax-load-more-anything' ); ?>"></span></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render locked Custom JavaScript card when Pro is inactive.
 *
 * @return void
 */
function ald_plugin_options_custom_js() {
	if ( ald_is_pro() ) {
		return;
	}
	?>
	<div class="ald-card tf-field-wrap ald-locked-card">
		<div class="ald-card__head">
			<div>
				<h3><?php esc_html_e( 'Custom JavaScript', 'ajax-load-more-anything' ); ?></h3>
				<p class="desc"><?php esc_html_e( 'Trigger custom callbacks after Ajax or General items load (e.g. lightbox, masonry, or analytics hooks).', 'ajax-load-more-anything' ); ?></p>
			</div>
			<?php ald_available_in_pro( 'custom_js' ); ?>
		</div>
		<div class="ald-pro-overlay-box ald-trigger-pro" data-pro-surface="custom_js" role="button" tabindex="0" aria-haspopup="dialog">
			<textarea class="wfull ald-code-textarea" rows="4" id="ald_options_custom_js" disabled placeholder="// Available in Load More Anything Pro"></textarea>
			<span class="ald-pro-overlay-cta">
				<span class="dashicons dashicons-lock" aria-hidden="true"></span>
				<?php esc_html_e( 'Unlock Custom JavaScript in Pro', 'ajax-load-more-anything' ); ?>
			</span>
		</div>
	</div>
	<?php
}
add_action( 'ald_options_js', 'ald_plugin_options_custom_js', 10 );

/**
 * Render an accessible "Pro" lock pill button.
 *
 * @param string $surface Upgrade context.
 * @return void
 */
function ald_available_in_pro( $surface = 'general' ) {
	if ( ald_is_pro() ) {
		return;
	}
	?>
	<button type="button" class="ald-pro-lock ald-trigger-pro" data-pro-surface="<?php echo esc_attr( $surface ); ?>" aria-haspopup="dialog">
		<span class="dashicons dashicons-lock" aria-hidden="true"></span>
		<span><?php esc_html_e( 'Pro', 'ajax-load-more-anything' ); ?></span>
	</button>
	<?php
}
