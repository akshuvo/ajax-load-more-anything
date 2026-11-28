# Load More Anything — Architecture

> **Source baseline:** 2026-11-27. Free `dev` (uncommitted Phases 1–4 modernization, version `3.3.10`, WPCS `0E/0W`, first-party PCP `0E/0W`). Pro `main` (reconciled with `origin/licenser` `04b6787` + uncommitted modernization, version `1.2.5`, WPCS `0E/0W`).

---

## 1. Plugin Bootstrap & File Layout

### Free (`ajax-load-more-anything/`)

Entrypoint: [`ajax-load-div.php`](../ajax-load-div.php) (**never rename** — live WP.org plugin slug/file path).

```text
ajax-load-more-anything/
├── ajax-load-div.php                     # Plugin header (3.3.10), Ajax_Load_More_Anything singleton, conditional assets, Appsero SDK init
├── admin/
│   ├── Menu.php                          # ALD_Menu: Cupertino x WP-Native settings page, Free/Pro caps, deep options_save() sanitizer
│   └── functions.php                     # Legacy v1/v2 migration, capability+nonce AJAX handlers, Selector Presets, wrapper templates, scoped Go Pro modal
├── inc/
│   └── ald-functions.php                 # get_ald_options(), ald_is_pro(), allowlists, CSS/selector sanitizers, JSON config & inline CSS builders
├── appsero/src/                          # Opt-in Appsero telemetry SDK synced from WP.org 3.3.9 (Client, Insights, License)
├── assets/
│   ├── admin.css / admin.min.css         # Scoped Cupertino x WP-Native admin design system
│   ├── admin.js / admin.min.js           # Tab ARIA controller, preset applier, CodeMirror sync, AJAX save + toasts, Go Pro modal
│   ├── styles.css / styles.min.css       # Frontend loader, button & ARIA live region styles
│   └── scripts.js / scripts.min.js       # Data-driven General & Ajax frontend runtime (DOMParser + IntersectionObserver)
├── languages/
│   └── ajax-load-more-anything.pot       # Translation template
├── scripts/                              # Guarded WP.org release pipeline (release.py, lint_package.py, smoke_test.py)
├── tests/                                # Release boundary unit tests (test_release.py) & disposable WP smoke test (smoke.php)
├── .github/                              # wporg.yml workflow, release-files.txt (18 files), release-baseline.json (3.3.9), release-removals.json
├── .wordpress/                           # WP.org listing assets (icon.svg, icon-128/256, banner-772/1544, screenshot-1..4)
├── phpcs.xml                             # Non-hidden WPCS 3.4.1 ruleset (0 errors / 0 warnings)
├── README.txt                            # WordPress.org readme (Stable tag: 3.3.10, Requires at least: 5.8, Tested up to: 7.1, Requires PHP: 7.4)
└── README.md                             # GitHub readme
```

### Bootstrap Sequence (`ajax-load-div.php`)

1. Guards `ABSPATH` and initializes singleton `Ajax_Load_More_Anything::init()`.
2. Defines `ALD_PLUGIN_VERSION` (`'3.3.10'`, with canonical `Ajax_Load_More_Anything::VERSION` and legacy `::version`), `ALD_PLUGIN_FILE`, `ALD_PLUGIN_PATH`, `ALD_PLUGIN_URL`, and `ALD_PLUGIN_ASSETS`. Dashboard upgrade links use `ald_get_pro_url( $surface )`.
3. `plugins_loaded` loads `inc/ald-functions.php` always and loads `admin/functions.php` + `admin/Menu.php` only inside `is_admin()` (which includes `admin-ajax.php`).
4. `wp_enqueue_scripts` (prio `999`) registers `ald-styles` and `ald-scripts` (choosing `.min` when `! SCRIPT_DEBUG`, honoring `disable_jquery_dep`), and enqueues them **conditionally only when `ald_has_frontend_config()` is true**:
   - Attaches sanitized `custom_css` via `wp_add_inline_style( 'ald-styles', ald_get_frontend_inline_css() )`.
   - Localizes full structured runtime config into `ald_params` via `wp_localize_script( 'ald-scripts', 'ald_params', ald_get_frontend_config() )`.
5. `admin_enqueue_scripts` (prio `100`) enqueues `ald-admin-styles`, `ald-admin-scripts`, WordPress CodeMirror (`text/css` + `text/javascript`), and `alda_params` (including `presets`, `freeLimits`, `i18n`) **strictly on `toplevel_page_ald_setting`**.
6. `ama_appsero_init_tracker_ajax_load_more_anything()` initializes opt-in `Appsero\Client` (`7e201c93-ae5f-4a3a-8ead-d1ccf4ad5e30`).

### Pro (`load-more-anything-pro/`)

Entrypoint: [`load-more-anything-pro.php`](../../load-more-anything-pro/load-more-anything-pro.php) (`v1.2.5`).

- Defines `ALD_PRO_PLUGIN_URL`, `ALD_PRO_PLUGIN_VERSION` (`'1.2.5'`), and `ALD_PRO_PRODUCT_UUID` (`'2947ad14-5532-4742-8b20-5959651d0d11'`).
- Uses `LicenserWP/client` v2.0.1 in `licenser/src/` (hardened `License::refresh_license_api()` with `manage_options` + nonce check). Legacy `license-manager-sdk/` has been removed.
- Enqueues `js/lma-pro(.min).js` on `wp_enqueue_scripts` (prio `1000`) only when Free is active and `ald-scripts` is enqueued, and attaches optional `custom_js` via `wp_add_inline_script( 'lma-pro', $custom_js, 'after' )`.
- Translation template lives under `languages/load-more-anything-pro.pot`; deterministic Licenser release ZIP builds via `scripts/build_pro_zip.py`.

---

## 2. Options, Storage & Legacy Migration

No custom DB tables or shortcodes are created. Primary option is `ald_options` in `wp_options`:

```php
[
	'general_loadmore' => [
		'gen_<key>' => [
			'wrapper_title'    => 'Demo Cards',
			'enabled'          => 'yes', // Defaults to yes when absent
			'button_placement' => 'after', // Defaults to inside when absent
			'btn_selector'     => '.demo-cards',
			'load_selector'    => '.demo-cards .card',
			'visible_items'    => '3',
			'load_items'       => '3',
			'button_label'     => 'Show +[count] More',
			'display_type'     => 'default', // Free: default|flex; Pro: +21 CSS display values
			'button_classes'   => 'button custom-pro-btn', // Pro
		],
	],
	'ajax_loadmore' => [
		'ajax_<key>' => [
			'wrapper_title'            => 'WooCommerce Shop Grid',
			'enabled'                  => 'yes', // Defaults to yes when absent
			'event_type'               => 'selectors_click', // selectors_click | scroll_to_load | custom_button (Pro)
			'custom_button_append'     => 'ul.products',
			'button_trigger_selector'  => '.woocommerce-pagination a.next',
			'button_label'             => 'Load More Products',
			'no_more_text'             => 'All products loaded',
			'click_selector'           => '.woocommerce-pagination a.next',
			'trigger_offset'           => '300', // Pro
			'hide_selector_wrapper'    => 'yes',
			'wrapper_to_hide'          => '.woocommerce-pagination',
			'scroll_to_top'            => 'no',  // Free + Pro smooth scroll to first updated container
			'update_browser_url'       => 'yes', // Pro
			'update_page_title'        => 'yes', // Pro
			'data_implement_selectors' => [
				'r0' => [ 'data_selector' => 'ul.products', 'implement_type' => 'insert_after' ],
				'r1' => [ 'data_selector' => '.woocommerce-pagination', 'implement_type' => 'replace_data' ],
			],
		],
	],
	'disable_jquery_dep' => 'on' | '',
	'custom_css'         => '',
	'custom_js'          => '', // Pro
]
```

- **Deep Sanitization (`ALD_Menu::options_save()`):** Unslashes request input, enforces Free caps (`6` General, `1` Ajax), sanitizes CSS selectors via `ald_sanitize_css_selector()` (preserving `>`, `:nth-child()`, attribute selectors while stripping `{};<` and script/style tags), sanitizes `custom_css` via `ald_sanitize_custom_css()` (preserving `>` combinators while preventing `</style>` breakout), and strips `<script>` tags from Pro `custom_js` while preserving backslashes/regexes.
- **Legacy v1/v2 Migration:** `ALD_Menu::plugin_page()` merges pre-3.0 `ald_wrapper_class*` and `asr_ald_css_class` options if present; `update_option_ald_options` and `add_option_ald_options` archive them to `ald_old_options` and delete the 31 flat keys.

---

## 3. Admin Settings UI (`page=ald_setting`)

- **Cupertino × WordPress-Native Design System (`assets/admin.css` & `assets/admin.js`):** Strictly scoped under `.ald-app` / `body.toplevel_page_ald_setting` with frosted sticky topbar, unsaved-changes badge, primary Save Changes button with live spinner, `Ctrl/Cmd + S` save shortcut, WAI-ARIA tablist (`Overview`, `General Selectors`, `Ajax Based`, `Settings & Code`, plus Pro `License & Updates`), bottom-right frosted toasts, and accessible Go Pro comparison modal rendered strictly inside `plugin_page()` (zero global `admin_footer` leaks, zero inline `<script>`/`<style>`).
- **One-Click Selector Presets (`ald_get_selector_presets()`):**
  - General presets include lists, table rows, Flex or Grid cards, comments, and Post Grid Master cards. The card recipes place the button after the container.
  - Ajax presets include WordPress Blog Archive, Gutenberg Query Loop, WooCommerce classic shop and Product Collection, Elementor, and theme-specific archives and builders.
  - Overview recipes switch tabs and add a populated wrapper. Suggested presets use the active parent theme and the WooCommerce, Elementor, Block Theme, and Post Grid Master checks.
- **Wrapper controls:** Enable or disable each saved wrapper and duplicate settings within the existing Free wrapper limits. Disabled wrappers remain editable and count toward those limits.
- **Overview and upgrade cards:** The Pro dialog resolves localized copy and a tagged upgrade URL from each control. Overview contains a Post Grid Master link and a review card for administrators with a configured wrapper and an install/tracking timestamp at least 14 days old. Dismissal is stored in `ald_review_dismissed` user meta.
- **Hardened Admin AJAX (`admin/functions.php`):** `ald_save_settings`, `ald_add_general_loadmore`, `ald_add_ajax_loadmore`, and `ald_dismiss_review` all enforce `current_user_can( 'manage_options' )` and `wp_verify_nonce()` independently and return JSON responses (`wp_send_json_success` / `wp_send_json_error`).

---

## 4. Modern Frontend Runtime Engine (`assets/scripts.js`)

- **General Load More:** Checks that both selectors match, inserts `.loadMoreBtn` inside the container or in an adjacent `.ald-btn-wrap`, and applies `.ald-item-hidden` only to the deferred items. Removing the class restores native CSS display, or applies an explicit allowlisted display type. The counter tracks the class even under a collapsed ancestor. Public button classes and `ald_general_content_loaded` remain available.
- **Selector diagnostics:** Frontend wrappers report zero matches with their key and selector field to administrators with `manage_options` in the browser console.
- **Ajax Load More & Infinite Scroll:**
  - Isolated per-wrapper `state` (`isLoading`, `xhr`, `observer`) with full `error` / abort recovery (never gets stuck).
  - Same-origin URL validation and safe link extraction (`$(e.target).closest('a').attr('href')` — no jQuery 3 removed `.context`).
  - Safe HTML parsing via `DOMParser` (prevents script execution on parse and matches both root & nested selectors via `parsedDoc.querySelectorAll()`).
  - Infinite scroll uses `IntersectionObserver` with `rootMargin` for Pro `trigger_offset` and a scroll fallback. Pagination can use `.ald-sr-only` to stay accessible without occupying layout space. A one-pixel sentinel outside the clipped wrapper marks the position of the next link.
  - `insert_after_elements` inserts complete matched elements after the last current match. The WordPress Blog Archive preset uses this for articles and a separate replacement rule for pagination.
  - Triggers `ald_ajax_content_ready` (`[data, args, parsedDoc]`), `ald_ajax_content_loaded` (`[data, args]`), `ald_ajax_content_success` (`[args, parsedDoc]`), `ald_ajax_content_error`, plus `$(document.body).trigger('post-load')` and Elementor `runReadyTrigger` re-initialization.
  - Optional smooth `scroll_to_top` to the first updated container and customizable `no_more_text`.
