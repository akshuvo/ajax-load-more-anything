# Known Issues, Modernization Record & Verification Guide

> **Last updated:** 2026-11-27 after completing **Phases 1–4** across Free (`ajax-load-more-anything` `v3.3.10`) and Pro (`load-more-anything-pro` `v1.2.5`).

---

## 1. Completed Modernization Summary (Phases 1–4)

### Phase 1 — Tooling, Security & Core Stabilization (Free + Pro)
- Added non-hidden [`phpcs.xml`](../phpcs.xml) (Free) and [`load-more-anything-pro/phpcs.xml`](../../load-more-anything-pro/phpcs.xml) (Pro), `.gitignore`, and `.distignore`; removed `.DS_Store`.
- Synced WP.org `3.3.9` Appsero SDK (`appsero/src/{Client,Insights,License}.php`) into Free, bumped Free to **`3.3.10`** (`Requires at least: 5.8`, `Tested up to: 7.1`, `Requires PHP: 7.4`), and fixed the `"Post Grid Master"` copy-paste typo in `README.txt`'s `== Privacy Policy ==`.
- Resolved **100% of WPCS violations** on first-party code:
  - Free: `2,214 errors / 161 warnings` → **`0 errors / 0 warnings`** (all 5 first-party PHP files including `tests/smoke.php`).
  - Pro: `263 errors / 30 warnings` → **`0 errors / 0 warnings`** (`load-more-anything-pro.php`).
- Resolved **100% of first-party WordPress Plugin Check (`PCP 2.2.0 --include-experimental`) findings** on Free (`7 errors / 10 warnings` → **`0 errors / 0 warnings`** across `ajax-load-div.php`, `admin/Menu.php`, `admin/functions.php`, `inc/ald-functions.php`, `README.txt`; remaining PCP notices belong strictly to bundled third-party `appsero/src/` and dev-only files excluded from the release ZIP).
- Hardened security across both plugins:
  - `ABSPATH` direct-access guards in every PHP file.
  - Independent `current_user_can( 'manage_options' )` + `wp_verify_nonce()` on all admin AJAX endpoints (`ald_save_settings`, `ald_add_general_loadmore`, `ald_add_ajax_loadmore`, and Pro `Licenser\License::refresh_license_api()`).
  - Proper `wp_unslash()` + deep sanitization of `general_loadmore`, `ajax_loadmore`, `custom_css`, and Pro `custom_js` (preventing `</style>`/`<script>` breakouts while preserving CSS `>` combinators and JS backslashes/regexes).
  - Eliminated `extract()`, dynamic `_e()` variable calls, PHP 8 `$button_classes` warning, global `admin_footer` Go Pro modal leak, inline `<script>`/`<style>` tags in PHP templates, and duplicate DOM IDs.
- Replaced PHP-generated inline frontend `<script>` with a real data-driven runtime in [`assets/scripts.js`](../assets/scripts.js) (`DOMParser`, `IntersectionObserver`, per-wrapper XHR error recovery, same-origin URL checks, polite ARIA live announcements, `post-load` & Elementor widget re-init, conditional asset enqueueing, jQuery 3.7 compatibility).

### Phase 2 — Guarded WordPress.org Auto-Deploy Pipeline (Free)
- Installed deterministic WP.org release tooling modeled on `ajax-filter-posts` & `licenser`:
  - [`.github/workflows/wporg.yml`](../.github/workflows/wporg.yml), [`.github/release-files.txt`](../.github/release-files.txt) (18 runtime files), [`.github/release-baseline.json`](../.github/release-baseline.json) pinned to live WP.org **`3.3.9`** (18 verified SHA-256 hashes), [`.github/release-removals.json`](../.github/release-removals.json) (`{}`).
  - [`scripts/release.py`](../scripts/release.py), [`scripts/lint_package.py`](../scripts/lint_package.py), [`scripts/smoke_test.py`](../scripts/smoke_test.py), [`tests/smoke.php`](../tests/smoke.php), [`tests/test_release.py`](../tests/test_release.py).
  - Refreshed `.wordpress/` listing assets (`icon.svg`, `icon-128x128.png`, `icon-256x256.png`, `banner-772x250.png`, `banner-1544x500.png`, and 4 PII-free admin `screenshot-1.png`–`screenshot-4.png`).

### Phase 3 — Pro Reconciliation, Licenser Hardening & Release Packaging
- Fast-forwarded `load-more-anything-pro` `main` from stale `1.2.1` (`1d1977e`) to shipped `origin/licenser` `1.2.4` (`04b6787`), removing `license-manager-sdk/` and `.DS_Store`.
- Upgraded Pro to **`1.2.5`**: ported the `gridmaster-pro` security patch into `licenser/src/License.php` (`refresh_license_api()`), moved `.pot` to `languages/load-more-anything-pro.pot`, standardized text domain to `load-more-anything-pro`, fixed double-slash script URL, replaced raw `wp_footer` `<script>` with `wp_add_inline_script()`, hardened `js/lma-pro.js` (`DOMParser` title sync, post-success `history.pushState` + `popstate`, quote-safe button hiding), and added [`scripts/build_pro_zip.py`](../../load-more-anything-pro/scripts/build_pro_zip.py).

### Phase 4 — Cupertino × WordPress-Native Admin UI & Feature Enhancements
- Redesigned `page=ald_setting` in the unified AddonMaster Cupertino × WP-Native design system (`assets/admin.css` & `assets/admin.js`): frosted sticky topbar, live unsaved-changes pill, `Ctrl/Cmd+S` save with CodeMirror buffer sync & frosted toast notifications, WAI-ARIA tab navigation, One-Click Selector Presets (Blog Archive, Gutenberg Query Loop, WooCommerce Shop Grid, Elementor Posts/Loop, List Items, Table Rows, Flex Card Grid, WP Comments), optional smooth scroll-to-container after Ajax load, and customizable "No more items" text.

---

## 2. Remaining Notes & Optional Follow-Ups

1. **Bundled Third-Party Appsero SDK (`appsero/src/`):** Kept as requested (`7e201c93-ae5f-4a3a-8ead-d1ccf4ad5e30`), matching `ajax-filter-posts`. Its upstream files still emit PCP notices inside `appsero/src/{Insights,License}.php`; avoid incidental formatting edits to upstream SDK code.
2. **GitHub Actions Secrets for Live WP.org Deploy:** Ensure `SVN_USERNAME` and `SVN_PASSWORD` secrets are configured in `akshuvo/ajax-load-more-anything` before publishing tag `v3.3.10`.
3. **Pro Release Upload to Licenser Portal:** After reviewing and committing `load-more-anything-pro` `1.2.5`, generate the release archive with `python3 -B scripts/build_pro_zip.py --output /tmp/alma-pro-dist` and upload `load-more-anything-pro-1.2.5.zip` to Product ID `1` on Licenser.

---

## 3. Automated Checks

```sh
FREE="/Users/akshuvo/Local Sites/grid-master/app/public/wp-content/plugins/ajax-load-more-anything"
PRO="/Users/akshuvo/Local Sites/grid-master/app/public/wp-content/plugins/load-more-anything-pro"
GM_ROOT="/Users/akshuvo/Local Sites/grid-master/app/public"
GM_SOCK="/Users/akshuvo/Library/Application Support/Local/run/T9DCTDfna/mysql/mysqld.sock"
PHPCS="$HOME/.config/composer/vendor/bin/phpcs"

# 1. PHP & JS syntax checks
find "$FREE" -type f -name '*.php' -not -path '*/.git/*' -exec php -l {} \;
find "$PRO"  -type f -name '*.php' -not -path '*/.git/*' -exec php -l {} \;
for f in "$FREE"/assets/*.js "$PRO"/js/*.js; do node --check "$f"; done

# 2. WPCS (0 errors / 0 warnings)
(cd "$FREE" && "$PHPCS" --report=summary)
(cd "$PRO"  && "$PHPCS" --report=summary)

# 3. Free release pipeline unit tests & dry-run SVN stage
(cd "$FREE" && python3 -B -m unittest discover -s tests -p 'test_release.py' -v)
(cd "$FREE" && python3 -B scripts/release.py build --output /tmp/alma-check --allow-untracked && python3 -B scripts/lint_package.py /tmp/alma-check/ajax-load-more-anything && python3 -B scripts/release.py stage --output /tmp/alma-check)

# 4. Pro deterministic ZIP build
python3 -B "$PRO/scripts/build_pro_zip.py" --output /tmp/almapro-check

# 5. WordPress Plugin Check (in-memory, no DB mutation)
php -d mysqli.default_socket="$GM_SOCK" "$(which wp)" \
  --path="$GM_ROOT" --url="http://grid-master.local" \
  --exec='WP_CLI::add_wp_hook("option_active_plugins", function($p){ if(is_array($p) && !in_array("plugin-check/plugin.php", $p, true)){ $p[] = "plugin-check/plugin.php"; } return $p; });' \
  plugin check ajax-load-more-anything --include-experimental
```

---

## 4. Verification Record (2026-11-27)

- **`php -l` & `node --check`:** PASS across all 12 PHP files (Free + Pro + SDKs + `tests/smoke.php`) and all 6 JS files (`admin.js`, `admin.min.js`, `scripts.js`, `scripts.min.js`, `lma-pro.js`, `lma-pro.min.js`).
- **WPCS (`phpcs` 3.13.6 / WPCS 3.4.1):** PASS — Free `0 errors / 0 warnings` (5/5 files), Pro `0 errors / 0 warnings` (1/1 file).
- **WordPress Plugin Check (`PCP 2.2.0 --include-experimental`):** PASS on all first-party Free files (`0 errors / 0 warnings` across `ajax-load-div.php`, `admin/Menu.php`, `admin/functions.php`, `inc/ald-functions.php`, `README.txt`); only bundled third-party `appsero/src/` and excluded repo dotfiles flagged.
- **Release Pipeline Unit Tests (`tests/test_release.py`):** **16/16 PASS** (disposable Git + SVN boundary tests).
- **Live WP.org SVN Dry-Run Stage (`scripts/release.py`):** PASS — built 18-file reproducible `ajax-load-more-anything.zip` + 9 `.wordpress/` listing assets, parsed 7 packaged PHP files, and staged against live WP.org SVN `r3828600` matching pinned `3.3.9` baseline with **0 unexplained deletions** (no commit made).
- **Pro Deterministic ZIP Build (`scripts/build_pro_zip.py`):** PASS — built clean 9-file `load-more-anything-pro-1.2.5.zip` with `php -l` passing on all 5 packaged PHP files.
- **Paired WordPress Integration & Security Checks (`http://grid-master.local/`, WP `7.1.5`, PHP `8.4.11`):**
  - Free alone: `tests/smoke.php` PASS; XSS breakout (`</style><script>`), selector brace stripping, CSS `>` combinator preservation, and Free wrapper caps (`6` General / `1` Ajax) verified PASS.
  - Free + Pro paired: `ALD_PLUGIN_VERSION === '3.3.10'`, `ALD_PRO_PLUGIN_VERSION === '1.2.5'`, `grid` display type, `custom_button`, backslash preservation in `custom_js`, `lmag_button_class`, single-slash `lma-pro.min.js` URL verified PASS.
  - Local Licenser Portal (`http://portal-addonmaster.local/` via `licenser_endpoint` filter — production never touched): `Licenser\Client` query for Product UUID `2947ad14-5532-4742-8b20-5959651d0d11` returned HTTP 200 (`new_version: "1.2.4"`).
  - Pro alone (Free deactivated): zero PHP notices/fatals; `lma-pro` safely skipped on frontend.
- **Live Browser QA (`http://grid-master.local/` at `1440x900`):**
  - WP Admin `page=ald_setting`: all 5 tabs (`Overview`, `General Selectors`, `Ajax Based`, `Settings & Code`, `License & Updates`), conditional field toggling, CodeMirror CSS & Pro JS editors, AJAX Save Changes + `"Settings saved successfully."` toast verified with **0 console errors**.
  - Frontend WooCommerce Shop (`http://grid-master.local/shop/`): verified `.ald-ajax-btn` ("Load More Products") injected after `ul.products`, `.woocommerce-pagination` hidden, clicking button loaded `/shop/page/2/` (`12` → `17` products), synced browser URL (`/shop/page/2/`) & page title (`Shop – Page 2 – Grid Master`), auto-hid button after last page, with **0 console errors**.
