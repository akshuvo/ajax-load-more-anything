# Integrations, Free ↔ Pro Contract & Licensing

## 1. Free ↔ Pro Integration Contract

`ajax-load-more-anything` (Free) and `load-more-anything-pro` (Pro) are tightly coupled through shared constants, WordPress hooks, `ald_options`, and frontend JS events. **Never rename or change signatures on any item below without updating both repositories in lockstep.**

### Shared Constants

| Constant | Defined By | Checked By | Notes |
|---|---|---|---|
| `ALD_PLUGIN_VERSION` | Free (`ajax-load-div.php`) | Pro (`plugin_action_links`, `admin_notice`) | Indicates Free is active |
| `ALD_PRO_PLUGIN_VERSION` | Pro (`load-more-anything-pro.php`) | Free (`ald_is_pro()`, `ajax-load-div.php`, `Menu.php`, `functions.php`, `ald-functions.php`) | Canonical Pro presence check |
| `ALD_PRO_PLUGIN_URL` | Pro (`load-more-anything-pro.php`) | Free (`admin/functions.php`, `inc/ald-functions.php:450`) | Legacy inconsistent check — standardize to `ald_is_pro()` in Phase 1 while keeping constant defined in Pro |

### PHP Actions (Produced by Free → Consumed by Pro / Extensions)

| Hook | Location (Free) | Arguments | Pro Consumer / Behavior |
|---|---|---|---|
| `ald_left_panel` | `admin/Menu.php:133` | none | Free renders plugin title + `<sup>pro</sup>` |
| `ald_options_menu` | `admin/Menu.php:147` | `$ald_options` | Pro injects `Auto Updates` (`#license-menu`) nav item |
| `ald_options_content` | `admin/Menu.php:300` | `$ald_options` | Pro renders `#license-menu` tab panel & Licenser activation form |
| `ald_options_js` | `admin/Menu.php:297` | `$ald_options` | Pro renders `ald_options[custom_js]` textarea; Free renders Go Pro lock when Pro inactive |
| `ald_form_end` | `admin/Menu.php:394` | none | Extension point after `<form id="ald_option_form">` |
| `ald_general_loadmore_before_wrap` | `admin/functions.php:355` | `$args` | Extension point before each General wrapper card |
| `ald_general_loadmore_after_wrap` | `admin/functions.php:473` | `$output, $args` | Free `_general_content()` enforces 6-wrapper Free cap |
| `ald_ajax_loadmore_before_wrap` | `admin/functions.php:547` | `$args` | Extension point before each Ajax wrapper card |
| `ald_ajax_loadmore_after_wrap` | `admin/functions.php:793` | `$output, $args` | Free `_ajax_content()` enforces 1-wrapper Free cap |
| `ald-general-loadmore-display-type-wrapper` | `inc/ald-functions.php:308` | `$value` | Pro `handle_display_types()` handles non-`default`/non-`flex` CSS display types |
| `load_more_anything_ajax_loadmore` | `inc/ald-functions.php:430` | `$ajax_loadmore` | Fires inside frontend JS block before Ajax wrappers loop |
| `after_load_more_push_ajax` | `inc/ald-functions.php:478` | `$value` | Pro `update_url()` outputs `window.history.pushState(...)` when `update_browser_url == 'yes'` |

### PHP Filters (Produced by Free)

| Hook | Location (Free) | Arguments | Behavior |
|---|---|---|---|
| `ald_before_options_save` | `admin/functions.php:161` | `$options` | Free `ALD_Menu::options_save()` sanitizes & enforces Free wrapper caps |
| `lmag_button_class` | `inc/ald-functions.php:247` | `$ald_btn_class, $value` | Fired only when `ALD_PRO_PLUGIN_VERSION` is defined; Pro (`origin/licenser` 1.2.4+) appends sanitized `button_classes` |

### JavaScript Handles, Globals & Custom DOM Events

| Interface | Producer | Consumer | Contract |
|---|---|---|---|
| Script handle `ald-scripts` | Free (`ajax-load-div.php:97`) | Pro (`js/lma-pro.min.js` dependency) | Must remain registered/enqueued on frontend |
| Global `ald_params` | Free (`wp_localize_script`) | Free + Pro frontend JS | `{ nonce, ajaxurl, ald_pro: '1' \| '0' }` |
| Global `alda_params` | Free (`wp_localize_script`) | Free admin JS (`assets/admin.js`) | `{ nonce, ajaxurl, ald_pro: '1' \| '0' }` |
| Global function `lmaIsViewport(el, offset)` | Pro (`js/lma-pro.js`) | Free `scroll_to_load` loop (`inc/ald-functions.php:502`) | Called whenever `ald_params.ald_pro == "1"` |
| Event `ald_ajax_content_ready` | Free (`$(document).trigger`) | Pro (`js/lma-pro.js`) | Args: `[data, args]`; Pro updates `document.title` when `args.update_page_title == 'yes'` |
| Event `ald_ajax_content_loaded` | Free (`$(document).trigger`) | External theme/plugin listeners | Args: `data` (raw response HTML) |
| Event `ald_ajax_content_success` | Free (`$(document).trigger`) | Pro (`js/lma-pro.js`) | Args: `[args]`; Pro triggers `lma-check-ajax-btn-availability` to hide `.ald-ajax-btn` when `button_trigger_selector` is no longer in DOM |
| Event `lma-check-ajax-btn-availability` | Pro (`js/lma-pro.js`) | Pro (`js/lma-pro.js`) | Args: `[args]` |

### Free vs Pro Feature Boundary

| Capability | Free | Pro |
|---|---|---|
| General (DOM) wrappers | Up to **6** *(Go Pro modal table incorrectly states 5)* | Unlimited |
| Ajax wrappers | **1** | Unlimited |
| General `display_type` | `default`, `flex` | +21 CSS display values (`grid`, `inline-block`, `table-row`, etc.) |
| General `button_classes` | Locked UI field | Appended via `lmag_button_class` (`origin/licenser` 1.2.4+) |
| Ajax `event_type` | `selectors_click`, `scroll_to_load` | + `custom_button` (injects `.ald-ajax-btn`) |
| Ajax `trigger_offset` | Locked UI field (defaults to `0`) | Custom px offset via `lmaIsViewport()` |
| Ajax `update_browser_url` / `update_page_title` | Disabled `<option>` | Enabled (`history.pushState` / `document.title`) |
| `custom_css` | Included | Included |
| `custom_js` | Locked upsell box | Editable CodeMirror textarea + `wp_footer` output |

---

## 2. Licensing & Auto-Updates (`Licenser`)

Load More Anything Pro is sold and licensed through AddonMaster's self-hosted **Licenser** server:

- **Production portal:** `https://portal.addonmaster.com` (never hit during local testing)
- **Local portal clone:** `http://portal-addonmaster.local` (WP Admin: `http://portal-addonmaster.local/wp-admin/?localwp_auto_login=1`)
- **Product record (verified on local portal DB, 2026-11-27):**
  - Product ID: `1` | Slug: `load-more-anything-pro` | Type: `plugin` | Status: `active`
  - **Product UUID:** `2947ad14-5532-4742-8b20-5959651d0d11`
  - Packages: ID `1` (`1 website - Yearly`, limit `1`), ID `3` (`3 websites - Yearly`, limit `3`), ID `2` (`10 websites - Yearly`, limit `10`)
  - Latest release on portal: `1.2.4` (`load-more-anything-pro-1.2.4.zip`, `2024-04-16`)

### Critical Audit Finding: Branch & SDK State in `load-more-anything-pro`

| Branch | Version | Active SDK | Status & Issues |
|---|---|---|---|
| `main` (`1d1977e`, currently checked out) | `1.2.1` (`README.md` says `1.2.2`) | **Legacy `license-manager-sdk/LmfwpptAutoUpdatePlugin.php`** + commented-out older `licenser/` copy | **BROKEN against Licenser v1.0.8.** Hits defunct `/wp-json/license-manager/v1/licenses/`. Commented-out `licenser/Client.php` still references dead ngrok URL and old `appsero_endpoint` / `appsero_*_version_info` names. Missing `lmag_button_class`. |
| `origin/licenser` (`04b6787`, matches portal `1.2.4` ZIP) | `1.2.4` | **`LicenserWP/client` v2.0.1** in `licenser/src/{Client,Insights,License,Updater}.php` | **Wire-compatible with Licenser v1.0.8**, uses `licenser_endpoint` filter, `licenser_activation_fields-2947ad14-5532-4742-8b20-5959651d0d11`, `licenser_{md5}_version_info` transient, and migrates legacy `_key` options. **Missing one security fix** already patched in `gridmaster-pro/licenser/src/License.php`: `refresh_license_api()` lacks `current_user_can( 'manage_options' )` and nonce verification! |

### Wire Contract (`LicenserWP/client` v2.0.1 ↔ Licenser Server)

Verified live against `http://portal-addonmaster.local/wp-json/licenser/v1/public/products/2947ad14-5532-4742-8b20-5959651d0d11` (returns HTTP `200`, `new_version: "1.2.4"`):

1. `POST /wp-json/licenser/v1/public/products/{uuid}` — Update check (`Updater::get_project_latest_version()`, 3h transient `licenser_{md5($slug)}_version_info`, bypassed on `update-core.php`).
2. `POST /wp-json/licenser/v1/public/license/{uuid}/{check|activate|deactivate}` — License lifecycle (`License`, stored in `licenser_{md5($slug)}_manage_license`, daily cron `load-more-anything-pro_license_check_event`).
3. `GET /wp-json/licenser/v1/public/products/download/{uuid}?license_key={key}&refurl={url}` — Authenticated ZIP stream used by WordPress's plugin upgrader.
4. `Client::endpoint()` applies `apply_filters( 'licenser_endpoint', $this->api_url . '/wp-json/licenser/v1/public' )` — **use this filter to safely route local tests to `http://portal-addonmaster.local/wp-json/licenser/v1/public`**.

---

## 3. Third-Party Ecosystem Integrations

- **WPML (`inc/ald-functions.php:149-184`):**
  - Registers General & Ajax button labels via `icl_register_string()` and translates via `apply_filters( 'wpml_translate_single_string', ... )`.
  - **Backward-compatibility caveat:** The WPML string domain is historically misspelled `'Load More Anyting'`. Changing it silently would orphan existing users' WPML translations; keep it or migrate gracefully.
- **Elementor (`inc/ald-functions.php:100`):**
  - Skips emitting `display: none` for General selectors when `defined('ELEMENTOR_VERSION') && \Elementor\Plugin::$instance->preview->is_preview_mode()`.
  - **Bug to harden in Phase 1:** Does not guard against null `\Elementor\Plugin::$instance` or `->preview`, and does not re-trigger Elementor frontend widget handlers after Ajax content injection.
- **WooCommerce & Gutenberg (`README.txt` & Ajax Engine):**
  - Works via selector-based full-page HTML extraction (`jQuery(selector, data).html()`).
  - Current limitations to address in Phase 4: does not re-initialize lazy-loaded images/WooCommerce fragments/Gutenberg Interactivity directives after Ajax injection, lacks preset selector templates for standard WordPress themes, WooCommerce Shop, and Gutenberg Query Loop (`wp-block-query`).
- **Appsero Telemetry (WP.org `3.3.9` only — not in GitHub `dev`):**
  - Live WP.org `tags/3.3.9` added `appsero/src/{Client,Insights,License}.php` with hash `7e201c93-ae5f-4a3a-8ead-d1ccf4ad5e30`. Decide explicitly in Phase 1/2 whether to keep Appsero (synced to Git + fixing the `"Post Grid Master"` copy-paste typo in `README.txt` Privacy Policy) or remove it cleanly via `.github/release-removals.json`.
