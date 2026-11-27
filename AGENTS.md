# Load More Anything (Free) — AI Agent Development Guide

This repository owns **Load More Anything (Free)**, the live WordPress.org plugin bootstrapped by [`ajax-load-div.php`](ajax-load-div.php) (`https://wordpress.org/plugins/ajax-load-more-anything/`, `https://github.com/akshuvo/ajax-load-more-anything/`).

> **CRITICAL — NEVER rename `ajax-load-div.php`:** This plugin is live on WordPress.org (~5,000+ active installs). Renaming the main plugin bootstrap file will deactivate existing user installations on update.

Companion plugins and repositories are **separate projects with independent Git roots** — never assume one Git command covers sibling directories:

| Component | Local Path | Git Repo / Role |
|---|---|---|
| **Load More Anything (Free)** *(this repo)* | `.../grid-master/app/public/wp-content/plugins/ajax-load-more-anything` | `akshuvo/ajax-load-more-anything` — core settings panel, General (DOM) & Ajax load-more engine, Free↔Pro hooks |
| **Load More Anything Pro** | `.../grid-master/app/public/wp-content/plugins/load-more-anything-pro` | `akshuvo/load-more-anything-pro` — separate extension repo (unlimited wrappers, custom Ajax button, URL/title sync, trigger offset, custom JS, button classes, extra CSS display types, Licenser updates) |
| **Licenser Server (Local Portal)** | `.../addonmaster-portal/app/public/wp-content/plugins/licenser` | `akshuvo/licenser` — self-hosted license & update server (`http://portal-addonmaster.local/`, live `https://portal.addonmaster.com/`, Pro Product UUID `2947ad14-5532-4742-8b20-5959651d0d11`) |
| **Post Grid Master (Reference Blueprint)** | `.../grid-master/app/public/wp-content/plugins/ajax-filter-posts` | Read-only local reference for WP.org guarded release pipeline (`wporg.yml`, `scripts/release.py`, `release-baseline.json`, `.wordpress/`) |

Local dev site: `http://grid-master.local/` (WP Admin auto-login: `http://grid-master.local/wp-admin/?localwp_auto_login=1`).

---

## Read Before Changing Code

- [Architecture](docs/architecture.md) — bootstrap, file layout, `ald_options` storage & legacy v1/v2 migration, admin settings UI, frontend General & Ajax JS/CSS runtime, and asset pipeline.
- [Integrations & Free↔Pro Contract](docs/integrations.md) — constants, PHP actions/filters, JS globals & custom DOM events shared with `load-more-anything-pro`, Licenser SDK contract, and WPML/Elementor/WooCommerce/Gutenberg behavior.
- [Development Workflow](docs/development.md) — Git roots & branch divergence (`dev` vs `main` vs live WP.org SVN `3.3.9`, Pro `main` vs `origin/licenser` `1.2.4`), LocalWP sockets, global PHPCS/WPCS & WordPress Plugin Check commands, WP.org SVN baseline, and coding conventions.
- [Known Issues, Baseline Audit & Roadmap](docs/known-issues-and-testing.md) — Phase 0 baseline findings (security, bugs, jQuery 3 incompatibilities, WPCS/PCP counts), verification checklists, and the prioritized **Phase 1–4 Modernization Roadmap**.

---

## Mandatory Working Rules

1. **Verify Git root, branch, and worktree state first.** Run `git rev-parse --show-toplevel`, `git branch --show-current`, and `git status --short` separately in `ajax-load-more-anything` and `load-more-anything-pro`. Never assume a single Git operation covers both. Note: Free's active branch is `dev` (staged at `3.3.10` with Appsero synced from WP.org `3.3.9`), and Pro's `main` is reconciled with `origin/licenser` (`1.2.4`) and staged at `1.2.5`.
2. **Never commit, push, or publish unless explicitly requested.** Leave all changes uncommitted in the working tree for user review. Never edit read-only reference plugins (`ajax-filter-posts`, `licenser`, `gridmaster-pro`).
3. **Never rename `ajax-load-div.php` or break stored user data.** Preserve backward compatibility with existing `ald_options` keys (`general_loadmore`, `ajax_loadmore`, `custom_css`, `custom_js`, `disable_jquery_dep`), legacy `ald_wrapper_class*` migration, WPML string domain (`'Load More Anyting'`), and public frontend CSS classes/JS events (`.loadMoreBtn`, `.ald-ajax-btn`, `.ald-count`, `ald_ajax_content_ready`, `ald_ajax_content_loaded`, `ald_ajax_content_success`).
4. **Preserve the Free ↔ Pro contract.** Free owns the admin shell (`page=ald_setting`), `ald_options` persistence, core hooks (`ald_options_menu`, `ald_options_content`, `ald_options_js`, `ald_before_options_save`, `ald-general-loadmore-display-type-wrapper`, `after_load_more_push_ajax`, `lmag_button_class`), and frontend script handle `ald-scripts` + `ald_params`. Assess Pro impact before altering any hook signature, option shape, or DOM selector.
5. **Follow WordPress security & coding standards.** Always:
   - Include `if ( ! defined( 'ABSPATH' ) ) { exit; }` in every PHP file.
   - Enforce both `current_user_can( 'manage_options' )` and `wp_verify_nonce()` independently on every admin AJAX / form mutation.
   - Unslash (`wp_unslash()`) and sanitize/validate all request input (including nested `ajax_loadmore` and `custom_css` fields).
   - Escape all output by context (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`, `wp_json_encode` / `wp_localize_script` for JS data — never pass dynamic variables to `_e()` or embed unescaped values in `<style>`/`<script>`).
   - Keep admin JS/CSS strictly scoped to `toplevel_page_ald_setting` (no global `admin_footer` modal leaks, no inline `<script>`/`<style>` tags in PHP templates).
   - Use text domain `'ajax-load-more-anything'` for all Free strings and avoid `extract()`.
6. **Keep WPCS & WordPress Plugin Check clean.** Run `php -l`, `node --check`, global WPCS (`"$HOME/.config/composer/vendor/bin/phpcs"` against [`phpcs.xml`](phpcs.xml)), `python3 -B -m unittest discover -s tests -p 'test_release.py' -v`, and WordPress Plugin Check (`wp plugin check ajax-load-more-anything --include-experimental`). Never commit `.DS_Store` or dev tools.
7. **Respect WordPress.org release guardrails.** The live WP.org tag is `3.3.9` (pinned in [`.github/release-baseline.json`](.github/release-baseline.json); never overwrite or reuse published tags — current release candidate is `3.3.10`). Keep `ajax-load-div.php` `Version`, `Ajax_Load_More_Anything::VERSION` / `ALD_PLUGIN_VERSION`, and `README.txt` `Stable tag` synchronized. Do not bypass `.github/release-files.txt` or deletion checks in [`.github/workflows/wporg.yml`](.github/workflows/wporg.yml).
8. **Verify honestly in paired scenarios.** Test both **Free alone** and **Free + Pro** on `http://grid-master.local/` (and licensing/updates strictly against `http://portal-addonmaster.local/`, never production `https://portal.addonmaster.com/`). Report exact commands, scenarios run, and anything left untested.
9. **Update docs as behavior evolves.** Keep `docs/` and Pro's companion [`load-more-anything-pro/AGENTS.md`](../load-more-anything-pro/AGENTS.md) synchronized whenever hooks, options, assets, release baselines, or known issues change.
