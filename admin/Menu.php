<?php
/**
 * Admin Menu, Settings Screen, and Option Sanitization Handler.
 *
 * @package Ajax_Load_More_Anything
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Load More Anything admin settings page and option persistence.
 */
class ALD_Menu {

	/**
	 * Initialize admin hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'ald_general_loadmore_after_wrap', [ $this, 'render_general_content' ], 10, 2 );
		add_action( 'ald_ajax_loadmore_after_wrap', [ $this, 'render_ajax_content' ], 10, 2 );

		add_filter( 'ald_before_options_save', [ $this, 'options_save' ] );
	}

	/**
	 * Register top-level admin menu page.
	 *
	 * @return void
	 */
	public function admin_menu() {
		add_menu_page(
			__( 'Load More Anything', 'ajax-load-more-anything' ),
			__( 'Load More Anything', 'ajax-load-more-anything' ),
			'manage_options',
			'ald_setting',
			[ $this, 'plugin_page' ],
			'dashicons-update'
		);
	}

	/**
	 * Render the Cupertino x WordPress-Native settings panel.
	 *
	 * @return void
	 */
	public function plugin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'ajax-load-more-anything' ) );
		}

		$ald_options = get_ald_options();

		// Merge legacy pre-3.0 flat options if they have not yet been migrated.
		$legacy = ald_old_options();
		if ( ! empty( $legacy ) ) {
			foreach ( $legacy as $legacy_key => $legacy_val ) {
				if ( 'custom_css' === $legacy_key ) {
					if ( empty( $ald_options['custom_css'] ) ) {
						$ald_options['custom_css'] = $legacy_val;
					}
				} elseif ( is_array( $legacy_val ) ) {
					$ald_options['general_loadmore'][] = $legacy_val;
				}
			}
		}

		$general_loadmore   = isset( $ald_options['general_loadmore'] ) && is_array( $ald_options['general_loadmore'] ) ? $ald_options['general_loadmore'] : [];
		$ajax_loadmore      = isset( $ald_options['ajax_loadmore'] ) && is_array( $ald_options['ajax_loadmore'] ) ? $ald_options['ajax_loadmore'] : [];
		$custom_css         = isset( $ald_options['custom_css'] ) ? (string) $ald_options['custom_css'] : '';
		$disable_jquery_dep = isset( $ald_options['disable_jquery_dep'] ) ? (string) $ald_options['disable_jquery_dep'] : '';
		$is_pro             = ald_is_pro();

		$general_active_count = 0;
		foreach ( $general_loadmore as $selector ) {
			if ( is_array( $selector ) && ( ! empty( $selector['wrapper_title'] ) || ! empty( $selector['btn_selector'] ) ) ) {
				++$general_active_count;
			}
		}

		$ajax_active_count = count( $ajax_loadmore );
		$presets           = ald_get_selector_presets();
		$stack_presets     = ald_get_stack_presets();
		$quick_recipes     = [
			'general' => [ 'list_items', 'table_rows', 'card_grid', 'comments' ],
			'ajax'    => [ 'wp_blog', 'gutenberg', 'woocommerce', 'elementor' ],
		];
		?>
		<div class="wrap ald-wrap ald-app">
			<h1 class="screen-reader-text"><?php esc_html_e( 'Load More Anything Settings', 'ajax-load-more-anything' ); ?></h1>

			<form method="post" id="ald_option_form" class="ald-option-form ald-pro-<?php echo $is_pro ? 'yes' : 'no'; ?>">
				<header class="ald-topbar">
					<div class="ald-topbar__left">
						<div class="ald-brand-mark" aria-hidden="true">
							<span class="dashicons dashicons-update"></span>
						</div>
						<div class="ald-brand-copy">
							<?php do_action( 'ald_left_panel' ); ?>
							<div class="ald-brand-meta">
								<span class="ald-badge ald-badge--neutral"><?php echo esc_html( 'v' . ALD_PLUGIN_VERSION ); ?></span>
								<?php if ( $is_pro ) : ?>
									<span class="ald-badge ald-badge--pro"><?php esc_html_e( 'Pro Active', 'ajax-load-more-anything' ); ?></span>
								<?php else : ?>
									<span class="ald-badge ald-badge--free"><?php esc_html_e( 'Free Edition', 'ajax-load-more-anything' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<div class="ald-topbar__right">
						<span class="ald-save-state" id="ald-save-state" aria-live="polite"><?php esc_html_e( 'All changes saved', 'ajax-load-more-anything' ); ?></span>
						<?php if ( ! $is_pro ) : ?>
							<button type="button" class="ald-btn ald-btn--ghost ald-trigger-pro">
								<span class="dashicons dashicons-star-filled" aria-hidden="true"></span>
								<?php esc_html_e( 'Upgrade to Pro', 'ajax-load-more-anything' ); ?>
							</button>
						<?php endif; ?>
						<?php ald_ajax_save_btn(); ?>
					</div>
				</header>

				<div class="ald-shell">
					<aside class="ald-sidebar" aria-label="<?php esc_attr_e( 'Settings navigation', 'ajax-load-more-anything' ); ?>">
						<ul class="tf-tab-nav ald-nav" role="tablist" aria-orientation="vertical">
							<li class="active" role="presentation">
								<a href="#welcome" role="tab" aria-selected="true" aria-controls="welcome" id="ald-tab-welcome">
									<span class="dashicons dashicons-dashboard" aria-hidden="true"></span>
									<span class="ald-nav__label"><?php esc_html_e( 'Overview', 'ajax-load-more-anything' ); ?></span>
								</a>
							</li>
							<li role="presentation">
								<a href="#general" role="tab" aria-selected="false" aria-controls="general" id="ald-tab-general">
									<span class="dashicons dashicons-list-view" aria-hidden="true"></span>
									<span class="ald-nav__label"><?php esc_html_e( 'General Selectors', 'ajax-load-more-anything' ); ?></span>
									<span class="ald-nav__count" data-ald-count="general"><?php echo esc_html( (string) $general_active_count ); ?></span>
								</a>
							</li>
							<li role="presentation">
								<a href="#ajax-based" role="tab" aria-selected="false" aria-controls="ajax-based" id="ald-tab-ajax">
									<span class="dashicons dashicons-update-alt" aria-hidden="true"></span>
									<span class="ald-nav__label"><?php esc_html_e( 'Ajax Based', 'ajax-load-more-anything' ); ?></span>
									<span class="ald-nav__count" data-ald-count="ajax"><?php echo esc_html( (string) $ajax_active_count ); ?></span>
								</a>
							</li>
							<li role="presentation">
								<a href="#functions" role="tab" aria-selected="false" aria-controls="functions" id="ald-tab-functions">
									<span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>
									<span class="ald-nav__label"><?php esc_html_e( 'Settings & Code', 'ajax-load-more-anything' ); ?></span>
								</a>
							</li>
							<?php do_action( 'ald_options_menu', $ald_options ); ?>
						</ul>

						<?php if ( ! $is_pro ) : ?>
							<div class="ald-side-card ald-side-card--pro">
								<div class="ald-side-card__eyebrow"><?php esc_html_e( 'Load More Anything Pro', 'ajax-load-more-anything' ); ?></div>
								<h3><?php esc_html_e( 'Unlock Unlimited Wrappers', 'ajax-load-more-anything' ); ?></h3>
								<ul class="ald-check-list">
									<li><?php esc_html_e( 'Unlimited General & Ajax wrappers', 'ajax-load-more-anything' ); ?></li>
									<li><?php esc_html_e( 'Custom Ajax Load More button', 'ajax-load-more-anything' ); ?></li>
									<li><?php esc_html_e( 'Dynamic Browser URL & Title sync', 'ajax-load-more-anything' ); ?></li>
									<li><?php esc_html_e( 'Infinite Scroll Trigger Offset & Custom JS', 'ajax-load-more-anything' ); ?></li>
								</ul>
								<div class="ald-side-card__actions">
									<a class="ald-btn ald-btn--primary ald-btn--block" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( ald_get_pro_url( 'sidebar_card' ) ); ?>">
										<?php esc_html_e( 'Get Pro', 'ajax-load-more-anything' ); ?>
										<span class="dashicons dashicons-external" aria-hidden="true"></span>
									</a>
									<button type="button" class="ald-btn ald-btn--secondary ald-btn--block ald-trigger-pro" data-pro-surface="sidebar_card">
										<?php esc_html_e( 'Compare Free vs Pro', 'ajax-load-more-anything' ); ?>
									</button>
								</div>
							</div>
						<?php endif; ?>

						<div class="ald-side-card">
							<h3><?php esc_html_e( 'Need Help?', 'ajax-load-more-anything' ); ?></h3>
							<p><?php esc_html_e( 'Check selector guides, open a support topic, or share a review.', 'ajax-load-more-anything' ); ?></p>
							<div class="ald-side-links">
								<a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( 'https://addonmaster.com/docs-category/load-more-anything/' ); ?>">
									<?php esc_html_e( 'Documentation', 'ajax-load-more-anything' ); ?>
									<span class="dashicons dashicons-external" aria-hidden="true"></span>
								</a>
								<a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( 'https://wordpress.org/support/plugin/ajax-load-more-anything' ); ?>">
									<?php esc_html_e( 'Support Forum', 'ajax-load-more-anything' ); ?>
									<span class="dashicons dashicons-external" aria-hidden="true"></span>
								</a>
								<a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( 'https://addonmaster.com/contact/' ); ?>">
									<?php esc_html_e( 'Contact AddonMaster', 'ajax-load-more-anything' ); ?>
									<span class="dashicons dashicons-external" aria-hidden="true"></span>
								</a>
								<a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( 'https://wordpress.org/support/plugin/ajax-load-more-anything/reviews/#new-post' ); ?>">
									<?php esc_html_e( 'Write a Review', 'ajax-load-more-anything' ); ?>
									<span class="dashicons dashicons-star-filled" aria-hidden="true"></span>
								</a>
							</div>
						</div>
					</aside>

					<main class="ald-main tf-tab-container">
						<!-- Overview Tab -->
						<section id="welcome" class="tf-tab-content ald-panel active" role="tabpanel" aria-labelledby="ald-tab-welcome">
							<div class="ald-panel-head">
								<div>
									<h2><?php esc_html_e( 'Welcome to Load More Anything', 'ajax-load-more-anything' ); ?></h2>
									<p class="ald-panel-desc"><?php esc_html_e( 'Turn any existing list, post grid, WooCommerce archive, or comment thread into a smooth Load More or Infinite Scroll experience.', 'ajax-load-more-anything' ); ?></p>
								</div>
							</div>

							<div class="ald-kpi-grid">
								<div class="ald-kpi-card">
									<span class="ald-kpi-card__label"><?php esc_html_e( 'General Wrappers', 'ajax-load-more-anything' ); ?></span>
									<strong class="ald-kpi-card__value ald-tabular" data-ald-kpi="general"><?php echo esc_html( (string) $general_active_count ); ?></strong>
									<span class="ald-kpi-card__sub">
										<?php
										echo $is_pro
											? esc_html__( 'Unlimited in Pro', 'ajax-load-more-anything' )
											: esc_html__( 'Up to 6 in Free', 'ajax-load-more-anything' );
										?>
									</span>
								</div>
								<div class="ald-kpi-card">
									<span class="ald-kpi-card__label"><?php esc_html_e( 'Ajax Wrappers', 'ajax-load-more-anything' ); ?></span>
									<strong class="ald-kpi-card__value ald-tabular" data-ald-kpi="ajax"><?php echo esc_html( (string) $ajax_active_count ); ?></strong>
									<span class="ald-kpi-card__sub">
										<?php
										echo $is_pro
											? esc_html__( 'Unlimited in Pro', 'ajax-load-more-anything' )
											: esc_html__( '1 wrapper in Free', 'ajax-load-more-anything' );
										?>
									</span>
								</div>
								<div class="ald-kpi-card">
									<span class="ald-kpi-card__label"><?php esc_html_e( 'Frontend Engine', 'ajax-load-more-anything' ); ?></span>
									<strong class="ald-kpi-card__value"><?php esc_html_e( 'Modern', 'ajax-load-more-anything' ); ?></strong>
									<span class="ald-kpi-card__sub"><?php esc_html_e( 'DOMParser + IntersectionObserver', 'ajax-load-more-anything' ); ?></span>
								</div>
							</div>

							<div class="ald-two-col">
								<div class="ald-card">
									<div class="ald-card__head">
										<h3><?php esc_html_e( 'General Selectors (Already on Page)', 'ajax-load-more-anything' ); ?></h3>
										<span class="ald-pill"><?php esc_html_e( 'Instant DOM', 'ajax-load-more-anything' ); ?></span>
									</div>
									<p><?php esc_html_e( 'Best when all items are already rendered in the HTML (menus, lists, table rows, galleries, comments, or widgets) and you want to reveal them in batches with a live +[count] counter.', 'ajax-load-more-anything' ); ?></p>
									<button type="button" class="ald-btn ald-btn--secondary ald-jump-tab" data-target="#general">
										<?php esc_html_e( 'Configure General Selectors', 'ajax-load-more-anything' ); ?>
									</button>
								</div>
								<div class="ald-card">
									<div class="ald-card__head">
										<h3><?php esc_html_e( 'Ajax Based (Paginated Archives)', 'ajax-load-more-anything' ); ?></h3>
										<span class="ald-pill ald-pill--accent"><?php esc_html_e( 'Infinite Scroll & Ajax', 'ajax-load-more-anything' ); ?></span>
									</div>
									<p><?php esc_html_e( 'Best for WordPress posts, Gutenberg Query Loop, WooCommerce shop grids, search results, and custom post types that already output pagination links.', 'ajax-load-more-anything' ); ?></p>
									<button type="button" class="ald-btn ald-btn--secondary ald-jump-tab" data-target="#ajax-based">
										<?php esc_html_e( 'Configure Ajax Selectors', 'ajax-load-more-anything' ); ?>
									</button>
								</div>
							</div>

							<?php if ( ! empty( $stack_presets ) ) : ?>
								<div class="ald-card">
									<h3><?php esc_html_e( 'Presets for Your Site', 'ajax-load-more-anything' ); ?></h3>
									<div class="ald-preset-bar">
										<?php foreach ( $stack_presets as [ $group, $preset_id ] ) : ?>
											<button type="button" class="ald-preset-btn" data-preset-group="<?php echo esc_attr( $group ); ?>" data-preset-key="<?php echo esc_attr( $preset_id ); ?>">
												<?php echo esc_html( $presets[ $group ][ $preset_id ]['title'] ); ?>
											</button>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endif; ?>

							<div class="ald-card">
								<div class="ald-card__head">
									<h3><?php esc_html_e( 'Quick-Start Recipes', 'ajax-load-more-anything' ); ?></h3>
								</div>
								<div class="ald-recipe-grid">
									<?php foreach ( $quick_recipes as $group => $preset_ids ) : ?>
										<?php foreach ( $preset_ids as $preset_id ) : ?>
											<button type="button" class="ald-recipe-chip ald-preset-btn" data-preset-group="<?php echo esc_attr( $group ); ?>" data-preset-key="<?php echo esc_attr( $preset_id ); ?>">
												<strong><?php echo esc_html( $presets[ $group ][ $preset_id ]['title'] ); ?></strong>
											</button>
										<?php endforeach; ?>
									<?php endforeach; ?>
								</div>
							</div>

							<div class="ald-card">
								<h3><?php esc_html_e( 'Post Grid Master', 'ajax-load-more-anything' ); ?></h3>
								<p><?php esc_html_e( 'Building a new filterable post or product grid from scratch? Try our sister plugin Post Grid Master.', 'ajax-load-more-anything' ); ?></p>
								<a class="ald-btn ald-btn--secondary" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( 'https://wordpress.org/plugins/ajax-filter-posts/' ); ?>">
									<?php esc_html_e( 'Explore Post Grid Master', 'ajax-load-more-anything' ); ?>
								</a>
							</div>

							<?php ald_review_card(); ?>
						</section>

						<!-- General Selectors Tab -->
						<section id="general" class="tf-tab-content ald-panel" role="tabpanel" aria-labelledby="ald-tab-general">
							<div class="ald-panel-head">
								<div>
									<h2><?php esc_html_e( 'General Selectors', 'ajax-load-more-anything' ); ?></h2>
									<p class="ald-panel-desc"><?php esc_html_e( 'Hide and progressively reveal elements that are already rendered on your page.', 'ajax-load-more-anything' ); ?></p>
								</div>
								<div class="ald-panel-actions">
									<button type="button" class="tf_add-general-wrapper ald-btn ald-btn--primary" data-nonce="<?php echo esc_attr( wp_create_nonce( 'lma-general-nonce' ) ); ?>">
										<span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
										<?php esc_html_e( 'Add General Wrapper', 'ajax-load-more-anything' ); ?>
									</button>
								</div>
							</div>

							<div class="ald-preset-bar" aria-label="<?php esc_attr_e( 'General selector presets', 'ajax-load-more-anything' ); ?>">
								<span class="ald-preset-bar__label"><?php esc_html_e( 'Quick presets:', 'ajax-load-more-anything' ); ?></span>
								<?php foreach ( $presets['general'] as $preset_key => $preset ) : ?>
									<button type="button" class="ald-preset-btn" data-preset-group="general" data-preset-key="<?php echo esc_attr( $preset_key ); ?>">
										<?php echo esc_html( $preset['title'] ); ?>
									</button>
								<?php endforeach; ?>
							</div>

							<div class="tf-field-wrap">
								<div class="tf_gen_sel_fields ald-wrappers-list">
									<?php
									if ( ! empty( $general_loadmore ) ) {
										$index = 0;
										foreach ( $general_loadmore as $key => $selector ) {
											if ( is_array( $selector ) && ( ! empty( $selector['wrapper_title'] ) || ! empty( $selector['btn_selector'] ) ) ) {
												ald_add_general_loadmore_wrap(
													[
														'key'      => $key,
														'thiskey'  => $index,
														'selector' => $selector,
													]
												);
												++$index;
											}
										}
									}
									?>
								</div>

								<div class="ald-empty-state" data-empty-for="general" <?php echo $general_active_count > 0 ? 'hidden' : ''; ?>>
									<span class="dashicons dashicons-list-view" aria-hidden="true"></span>
									<h3><?php esc_html_e( 'No General wrappers yet', 'ajax-load-more-anything' ); ?></h3>
									<p><?php esc_html_e( 'Click "Add General Wrapper" or pick a quick preset above to add Load More to lists, comments, tables, or card grids.', 'ajax-load-more-anything' ); ?></p>
								</div>
							</div>
						</section>

						<!-- Ajax Based Tab -->
						<section id="ajax-based" class="tf-tab-content ald-panel" role="tabpanel" aria-labelledby="ald-tab-ajax">
							<div class="ald-panel-head">
								<div>
									<h2><?php esc_html_e( 'Ajax Based Selectors', 'ajax-load-more-anything' ); ?></h2>
									<p class="ald-panel-desc"><?php esc_html_e( 'Fetch paginated posts, WooCommerce products, Gutenberg Query Loops, or comments dynamically without reloading the page.', 'ajax-load-more-anything' ); ?></p>
								</div>
								<div class="ald-panel-actions">
									<button type="button" class="tf_add-ajax-wrapper ald-btn ald-btn--primary" data-nonce="<?php echo esc_attr( wp_create_nonce( 'lma-ajax-nonce' ) ); ?>">
										<span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
										<?php esc_html_e( 'Add Ajax Wrapper', 'ajax-load-more-anything' ); ?>
									</button>
								</div>
							</div>

							<div class="ald-preset-bar" aria-label="<?php esc_attr_e( 'Ajax selector presets', 'ajax-load-more-anything' ); ?>">
								<span class="ald-preset-bar__label"><?php esc_html_e( 'Quick presets:', 'ajax-load-more-anything' ); ?></span>
								<?php foreach ( $presets['ajax'] as $preset_key => $preset ) : ?>
									<button type="button" class="ald-preset-btn" data-preset-group="ajax" data-preset-key="<?php echo esc_attr( $preset_key ); ?>">
										<?php echo esc_html( $preset['title'] ); ?>
									</button>
								<?php endforeach; ?>
							</div>

							<div class="tf-field-wrap">
								<div class="tf_ajax_sel_fields ald-wrappers-list">
									<?php
									if ( ! empty( $ajax_loadmore ) ) {
										$index = 0;
										foreach ( $ajax_loadmore as $key => $selector ) {
											if ( is_array( $selector ) ) {
												ald_add_ajax_loadmore_wrap(
													[
														'key'      => $key,
														'thiskey'  => $index,
														'selector' => $selector,
													]
												);
												++$index;
											}
										}
									}
									?>
								</div>

								<div class="ald-empty-state" data-empty-for="ajax" <?php echo $ajax_active_count > 0 ? 'hidden' : ''; ?>>
									<span class="dashicons dashicons-update-alt" aria-hidden="true"></span>
									<h3><?php esc_html_e( 'No Ajax wrappers yet', 'ajax-load-more-anything' ); ?></h3>
									<p><?php esc_html_e( 'Add an Ajax wrapper or click a WordPress / Gutenberg / WooCommerce preset above to get started.', 'ajax-load-more-anything' ); ?></p>
								</div>
							</div>
						</section>

						<!-- Settings & Custom Code Tab -->
						<section id="functions" class="tf-tab-content ald-panel" role="tabpanel" aria-labelledby="ald-tab-functions">
							<div class="ald-panel-head">
								<div>
									<h2><?php esc_html_e( 'Settings & Custom Code', 'ajax-load-more-anything' ); ?></h2>
									<p class="ald-panel-desc"><?php esc_html_e( 'Configure script compatibility and add custom CSS or JavaScript.', 'ajax-load-more-anything' ); ?></p>
								</div>
							</div>

							<div class="ald-card">
								<div class="ald-toggle-row">
									<div>
										<label class="ald-field-title" for="ald_disable_jquery_dep"><?php esc_html_e( 'Disable jQuery Script Dependency', 'ajax-load-more-anything' ); ?></label>
										<p class="desc"><?php esc_html_e( 'Enable only if your theme bundles or dequeues WordPress core jQuery in a custom way. Leave unchecked for standard sites.', 'ajax-load-more-anything' ); ?></p>
									</div>
									<label class="ald-switch">
										<input type="checkbox" id="ald_disable_jquery_dep" name="ald_options[disable_jquery_dep]" value="on" <?php checked( $disable_jquery_dep, 'on' ); ?> />
										<span class="ald-switch__slider" aria-hidden="true"></span>
									</label>
								</div>
							</div>

							<div class="ald-card tf-field-wrap">
								<div class="ald-card__head">
									<div>
										<h3><?php esc_html_e( 'Custom CSS', 'ajax-load-more-anything' ); ?></h3>
										<p class="desc"><?php esc_html_e( 'Injected cleanly via wp_add_inline_style whenever Load More rules are active.', 'ajax-load-more-anything' ); ?></p>
									</div>
								</div>
								<textarea name="ald_options[custom_css]" class="wfull ald-code-textarea" rows="7" id="ald_options_custom_css"><?php echo esc_textarea( $custom_css ); ?></textarea>
							</div>

							<?php do_action( 'ald_options_js', $ald_options ); ?>
						</section>

						<?php do_action( 'ald_options_content', $ald_options ); ?>
					</main>
				</div>

				<input type="hidden" name="action" value="ald_save_settings" />
				<?php wp_nonce_field( 'alma_settings_nonce', 'ald_nonce' ); ?>
			</form>

			<?php do_action( 'ald_form_end' ); ?>
			<?php ald_plugin_pro_modal(); ?>
			<div class="ald-toast-stack" id="ald-toast-stack" aria-live="polite" aria-atomic="true"></div>
		</div>
		<?php
	}

	/**
	 * Output a General wrapper HTML card if within Free/Pro limits.
	 *
	 * @param string $output Rendered HTML.
	 * @param array  $args   Wrapper render arguments.
	 * @return void
	 */
	public function render_general_content( $output, $args ) {
		if ( isset( $args['thiskey'] ) && intval( $args['thiskey'] ) > 5 && ! ald_is_pro() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in ald_add_general_loadmore_wrap().
		echo $output;
	}

	/**
	 * Output an Ajax wrapper HTML card if within Free/Pro limits.
	 *
	 * @param string $output Rendered HTML.
	 * @param array  $args   Wrapper render arguments.
	 * @return void
	 */
	public function render_ajax_content( $output, $args ) {
		if ( isset( $args['thiskey'] ) && intval( $args['thiskey'] ) > 0 && ! ald_is_pro() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in ald_add_ajax_loadmore_wrap().
		echo $output;
	}

	/**
	 * Deep-sanitize and enforce Free/Pro limits on ald_options before saving.
	 *
	 * @param mixed $options Raw unslashed options array.
	 * @return array
	 */
	public function options_save( $options ) {
		if ( ! is_array( $options ) ) {
			return [];
		}

		$is_pro    = ald_is_pro();
		$sanitized = [
			'general_loadmore'   => [],
			'ajax_loadmore'      => [],
			'disable_jquery_dep' => ( isset( $options['disable_jquery_dep'] ) && 'on' === $options['disable_jquery_dep'] ) ? 'on' : '',
			'custom_css'         => isset( $options['custom_css'] ) ? ald_sanitize_custom_css( $options['custom_css'] ) : '',
		];

		if ( isset( $options['custom_js'] ) && $is_pro ) {
			$raw_js                 = str_replace( "\0", '', (string) $options['custom_js'] );
			$sanitized['custom_js'] = trim( preg_replace( '#</?script\b[^>]*>#i', '', $raw_js ) );
		}

		// Sanitize General wrappers.
		if ( isset( $options['general_loadmore'] ) && is_array( $options['general_loadmore'] ) ) {
			$general_count = 0;
			foreach ( $options['general_loadmore'] as $key => $value ) {
				if ( ! is_array( $value ) ) {
					continue;
				}
				if ( ! $is_pro && $general_count >= 6 ) {
					break;
				}

				$clean_key = sanitize_key( (string) $key );
				if ( '' === $clean_key ) {
					$clean_key = 'gen_' . $general_count;
				}

				$sanitized['general_loadmore'][ $clean_key ] = [
					'wrapper_title'    => isset( $value['wrapper_title'] ) ? sanitize_text_field( $value['wrapper_title'] ) : '',
					'btn_selector'     => isset( $value['btn_selector'] ) ? ald_sanitize_css_selector( $value['btn_selector'] ) : '',
					'load_selector'    => isset( $value['load_selector'] ) ? ald_sanitize_css_selector( $value['load_selector'] ) : '',
					'visible_items'    => isset( $value['visible_items'] ) && '' !== $value['visible_items'] ? (string) absint( $value['visible_items'] ) : '',
					'load_items'       => isset( $value['load_items'] ) && '' !== $value['load_items'] ? (string) max( 1, absint( $value['load_items'] ) ) : '',
					'button_label'     => isset( $value['button_label'] ) ? sanitize_text_field( $value['button_label'] ) : '',
					'display_type'     => isset( $value['display_type'] ) ? ald_sanitize_display_type( $value['display_type'] ) : 'default',
					'enabled'          => 'no' === ( $value['enabled'] ?? 'yes' ) ? 'no' : 'yes',
					'button_placement' => 'after' === ( $value['button_placement'] ?? 'inside' ) ? 'after' : 'inside',
					'button_classes'   => ( $is_pro && isset( $value['button_classes'] ) ) ? ald_sanitize_class_list( $value['button_classes'] ) : '',
				];

				++$general_count;
			}
		}

		// Sanitize Ajax wrappers.
		if ( isset( $options['ajax_loadmore'] ) && is_array( $options['ajax_loadmore'] ) ) {
			$ajax_count = 0;
			foreach ( $options['ajax_loadmore'] as $key => $value ) {
				if ( ! is_array( $value ) ) {
					continue;
				}
				if ( ! $is_pro && $ajax_count >= 1 ) {
					break;
				}

				$clean_key = sanitize_key( (string) $key );
				if ( '' === $clean_key ) {
					$clean_key = 'ajax_' . $ajax_count;
				}

				$event_type = isset( $value['event_type'] ) ? sanitize_key( $value['event_type'] ) : 'selectors_click';
				if ( ! in_array( $event_type, [ 'selectors_click', 'scroll_to_load', 'custom_button' ], true ) ) {
					$event_type = 'selectors_click';
				}
				if ( ! $is_pro && 'custom_button' === $event_type ) {
					$event_type = 'selectors_click';
				}

				$clean_dis = [];
				if ( isset( $value['data_implement_selectors'] ) && is_array( $value['data_implement_selectors'] ) ) {
					foreach ( $value['data_implement_selectors'] as $dis_key => $dis_row ) {
						if ( ! is_array( $dis_row ) || empty( $dis_row['data_selector'] ) ) {
							continue;
						}
						$data_selector = ald_sanitize_css_selector( $dis_row['data_selector'] );
						if ( '' === $data_selector ) {
							continue;
						}
						$implement_type = isset( $dis_row['implement_type'] ) ? sanitize_key( $dis_row['implement_type'] ) : 'replace_data';
						if ( ! in_array( $implement_type, [ 'replace_data', 'insert_after', 'insert_before', 'insert_after_elements' ], true ) ) {
							$implement_type = 'replace_data';
						}
						$clean_dis_key               = sanitize_key( (string) $dis_key );
						$clean_dis[ $clean_dis_key ] = [
							'data_selector'  => $data_selector,
							'implement_type' => $implement_type,
						];
					}
				}

				$sanitized['ajax_loadmore'][ $clean_key ] = [
					'wrapper_title'            => isset( $value['wrapper_title'] ) ? sanitize_text_field( $value['wrapper_title'] ) : '',
					'event_type'               => $event_type,
					'enabled'                  => 'no' === ( $value['enabled'] ?? 'yes' ) ? 'no' : 'yes',
					'custom_button_append'     => isset( $value['custom_button_append'] ) ? ald_sanitize_css_selector( $value['custom_button_append'] ) : '',
					'button_trigger_selector'  => isset( $value['button_trigger_selector'] ) ? ald_sanitize_css_selector( $value['button_trigger_selector'] ) : '',
					'button_label'             => isset( $value['button_label'] ) ? sanitize_text_field( $value['button_label'] ) : '',
					'no_more_text'             => isset( $value['no_more_text'] ) ? sanitize_text_field( $value['no_more_text'] ) : '',
					'click_selector'           => isset( $value['click_selector'] ) ? ald_sanitize_css_selector( $value['click_selector'] ) : '',
					'trigger_offset'           => ( $is_pro && isset( $value['trigger_offset'] ) && '' !== $value['trigger_offset'] ) ? (string) absint( $value['trigger_offset'] ) : '0',
					'hide_selector_wrapper'    => ( isset( $value['hide_selector_wrapper'] ) && 'yes' === $value['hide_selector_wrapper'] ) ? 'yes' : 'no',
					'wrapper_to_hide'          => isset( $value['wrapper_to_hide'] ) ? ald_sanitize_css_selector( $value['wrapper_to_hide'] ) : '',
					'update_browser_url'       => ( $is_pro && isset( $value['update_browser_url'] ) && 'yes' === $value['update_browser_url'] ) ? 'yes' : 'no',
					'update_page_title'        => ( $is_pro && isset( $value['update_page_title'] ) && 'yes' === $value['update_page_title'] ) ? 'yes' : 'no',
					'scroll_to_top'            => ( isset( $value['scroll_to_top'] ) && 'yes' === $value['scroll_to_top'] ) ? 'yes' : 'no',
					'data_implement_selectors' => $clean_dis,
				];

				++$ajax_count;
			}
		}

		return $sanitized;
	}
}

/**
 * Initialize ALD_Menu singleton.
 *
 * @return ALD_Menu
 */
function ald_menu_init() {
	static $instance = null;
	if ( null === $instance ) {
		$instance = new ALD_Menu();
	}
	return $instance;
}

ald_menu_init();
