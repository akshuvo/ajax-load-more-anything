# Development Workflow, Environments & Release Baseline

## 1. Independent Git Repositories & Branch State

**Do NOT assume a shared Git root.** Each plugin in `wp-content/plugins/` is an independent repository:

| Plugin Directory | Git Remote | Checked-Out Branch (2026-11-27) | Other Remote Branches | Critical Divergence Notes |
|---|---|---|---|---|
| `ajax-load-more-anything/` | `https://github.com/akshuvo/ajax-load-more-anything.git` | `dev` (`035dc87`, `v3.3.8`) | `main` (`14aa0f5`, tag `v3.3.5`), `phpcs-fix` (`50452fd`), `temp` (`4c1f946`) | **Live WP.org SVN is `3.3.9` (`r3399287`, Nov 2025)** — never pushed to GitHub! `main` is behind `dev` (`3.3.5` vs `3.3.8`). `.DS_Store` is tracked in `dev`. |
| `load-more-anything-pro/` | `https://github.com/akshuvo/load-more-anything-pro.git` | `main` (`1d1977e`, `v1.2.1` WIP) | `licenser` (`04b6787`, **`v1.2.4`**), `dev` | **Shipped `1.2.4` is on `origin/licenser`**, NOT `main`! Merge/reconcile `origin/licenser` before making Pro code changes in Phase 1/3. |

Always check status separately before editing:
```sh
git -C "/Users/akshuvo/Local Sites/grid-master/app/public/wp-content/plugins/ajax-load-more-anything" status -sb
git -C "/Users/akshuvo/Local Sites/grid-master/app/public/wp-content/plugins/load-more-anything-pro" status -sb
```

> **Git Rule:** Never run `git commit` or `git push` unless explicitly instructed by the user. Leave all working tree changes uncommitted for review.

---

## 2. Local Development Environments & CLI Tooling

### Sites & LocalWP MySQL Sockets (Verified 2026-11-27)

| Site | URL | WP Admin Auto-Login | WP Root | LocalWP MySQL Socket |
|---|---|---|---|---|
| **Grid Master (Dev Site)** | `http://grid-master.local/` | `http://grid-master.local/wp-admin/?localwp_auto_login=1` | `/Users/akshuvo/Local Sites/grid-master/app/public` | `/Users/akshuvo/Library/Application Support/Local/run/T9DCTDfna/mysql/mysqld.sock` |
| **AddonMaster Portal (Local Licenser)** | `http://portal-addonmaster.local/` | `http://portal-addonmaster.local/wp-admin/?localwp_auto_login=1` | `/Users/akshuvo/Local Sites/addonmaster-portal/app/public` | `/Users/akshuvo/Library/Application Support/Local/run/vq5NUpMTM/mysql/mysqld.sock` |

- **Grid Master WP environment:** WordPress `7.1.5`, active theme `Storefront` `4.6.2`, active plugins include `WooCommerce 11.3.1`, `Elementor 4.4.2`, `Beaver Builder Lite 2.11.2.2`, `FakerPress 0.10.2`, `ajax-filter-posts 3.4.26`, `gridmaster-pro 1.2.5`. Both `ajax-load-more-anything` and `load-more-anything-pro` are installed (`inactive` during Phase 0) alongside `plugin-check 2.2.0` (`inactive`).
- **Host CLI tools verified (2026-11-27):**
  - `php`: PHP `8.4.11` (`/opt/homebrew/bin/php`)
  - `node`: Node `v25.8.1`
  - `wp`: WP-CLI `2.12.0` (`/opt/homebrew/bin/wp`)
  - `svn`: Subversion CLI (verified working against `https://plugins.svn.wordpress.org/ajax-load-more-anything/`)
  - **Global PHPCS / WPCS:** `"$HOME/.config/composer/vendor/bin/phpcs"` — PHPCS `3.13.6` + WPCS `3.4.1` (`WordPress`, `WordPress-Core`, `WordPress-Extra`, `WordPress-Docs`). Note: `~/.composer` is root-owned on this machine; always use `COMPOSER_HOME="$HOME/.config/composer"` if updating global Composer packages.

### Running Baseline Quality Checks

```sh
FREE="/Users/akshuvo/Local Sites/grid-master/app/public/wp-content/plugins/ajax-load-more-anything"
PRO="/Users/akshuvo/Local Sites/grid-master/app/public/wp-content/plugins/load-more-anything-pro"
GM_ROOT="/Users/akshuvo/Local Sites/grid-master/app/public"
GM_SOCK="/Users/akshuvo/Library/Application Support/Local/run/T9DCTDfna/mysql/mysqld.sock"
PHPCS="$HOME/.config/composer/vendor/bin/phpcs"

# 1. PHP syntax lint (Free & Pro)
find "$FREE" -type f -name '*.php' -not -path '*/.git/*' -exec php -l {} \;
find "$PRO"  -type f -name '*.php' -not -path '*/.git/*' -exec php -l {} \;

# 2. JS syntax lint (Free & Pro)
find "$FREE/assets" -type f -name '*.js' -exec node --check {} \;
find "$PRO/js"      -type f -name '*.js' -exec node --check {} \;

# 3. WPCS / PHPCS (after Phase 1 adds phpcs.xml, omit --standard=WordPress)
"$PHPCS" --standard=WordPress --extensions=php -s --report=summary "$FREE"
"$PHPCS" --standard=WordPress --extensions=php -s --report=summary "$PRO/load-more-anything-pro.php"

# 4. WordPress Plugin Check (PCP 2.2.0) — runs in-memory without mutating active_plugins in DB
php -d mysqli.default_socket="$GM_SOCK" "$(which wp)" \
  --path="$GM_ROOT" --url="http://grid-master.local" \
  --exec='WP_CLI::add_wp_hook("option_active_plugins", function($p){ if(is_array($p) && !in_array("plugin-check/plugin.php", $p, true)){ $p[] = "plugin-check/plugin.php"; } return $p; });' \
  plugin check ajax-load-more-anything --include-experimental
```

### Safe Local Testing Against `http://portal-addonmaster.local/`

Never test Pro license activations, deactivations, or updater downloads against live `https://portal.addonmaster.com/`.

Once `load-more-anything-pro` is running `LicenserWP/client` v2.0.1 (`origin/licenser` or Phase 3+), route requests locally via a temporary mu-plugin or `wp-config.php` filter:
```php
add_filter( 'licenser_endpoint', function () {
	return 'http://portal-addonmaster.local/wp-json/licenser/v1/public';
} );
```
And ensure local WordPress allows local HTTP requests (`http_request_host_is_external` filter returning `true` for `portal-addonmaster.local` if blocked by WP safe HTTP).

---

## 3. WordPress.org Live Baseline (`tags/3.3.9`, SVN `r3399287`)

Inspected live on 2026-11-27 from `https://plugins.svn.wordpress.org/ajax-load-more-anything/`:

- **Published version:** `3.3.9` (`2025-11-19`, ~5,000+ active installs, `Tested up to: 6.8.3` / API `6.8.13`, `Requires at least: 4.6`, `Requires PHP: 5.6`).
- **Published `tags/3.3.9/` & `trunk/` runtime files (18 files — pin these exact SHA-256 hashes into `.github/release-baseline.json` in Phase 2):**
  1. `README.md` (`dafdc0fe43ac48fa1566a264eaf7be66f0c3e2b773343bf39f3dc59b5411e3ef`)
  2. `README.txt` (`0c7f4b4606bc8ebc49edb314d971412425b57587270c8b5aac691407e422a89f`)
  3. `admin/Menu.php` (`9e62189348ef79372fdd1784ef880eb9a9f03bc2bf9330a87ede984df0c94a3f`)
  4. `admin/functions.php` (`ba733845405927ee74f434d6357a634f0294a30b51a4dff011b6ba8bd076bf70`)
  5. `ajax-load-div.php` (`17926a34cb430b28a57b0fdfc78ae5b52240ae6f0ff1d9213265d592c3c9c5c9`)
  6. `appsero/src/Client.php` (`a8bfc8f75ad8cfdc37908657632ee20821c6fc6e03c2229495c52372a8502ed3`)
  7. `appsero/src/Insights.php` (`5396ae389025f8e7a8d61f2d38e9f0af34de7456442f6660b24d4e34c0fe65e5`)
  8. `appsero/src/License.php` (`608c5bd17ca13a2aef2f08d42711e966121eadc868cc3006089c1ccb813f7c00`)
  9. `assets/admin.css` (`b27b1546d04d1d487624a74e66b44dd5cf980f90d37f4442b5905bd74316b633`)
  10. `assets/admin.js` (`489d43efdb4032cbbe109dd6799a3962f05c662a44c7630e047868757075fa04`)
  11. `assets/admin.min.css` (`04be15601f125794d67f1a865b63e86110d7b78d0b8068fa2e7e221d9caf26dc`)
  12. `assets/admin.min.js` (`68a92c9b3ec9673f137340afa72261a099b3fb371b333f909c69f45aebc1f6e5`)
  13. `assets/scripts.js` (`e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` — 0 bytes!)
  14. `assets/scripts.min.js` (`e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` — 0 bytes!)
  15. `assets/styles.css` (`6a56371602b553ec7a4fa358277bb2c87d38484502b41453c0540df83f29f193`)
  16. `assets/styles.min.css` (`8a099f9bdf21d644333fac3dee44390804bb62185a202b722a9fc7c237107a20`)
  17. `inc/ald-functions.php` (`25df81bdfa755221faa85c05a481482822149242ae259ac39cb992d2e795136e`)
  18. `languages/ajax-load-more-anything.pot` (`05af4ee0363a8cd408e84ee3765dd78fe687d711d746fdef3da7a664dded64e3`)
- **Published SVN `/assets/` listing assets (13 files):**
  - `banner-772x250.png`, `banner-1544x500.png`, `icon-256x256.png` *(note: WP.org API currently reports `'icons': None` — adding `icon-128x128.png` + `icon.svg` alongside `icon-256x256.png` in `.wordpress/` during Phase 2 will modernize the listing)*
  - `screenshot-1.png`, `screenshot-2.png`, `screenshot-3.png`, `screenshot-4.png`, `screenshot-5.gif`, `screenshot-6.gif`, `screenshot-7.gif`, `screenshot-8.png`, `screenshot-9.gif`, `screenshot-10.gif`
- **Important Phase 2 Guardrails (Modeled on `ajax-filter-posts` & `licenser`):**
  - Never overwrite or reuse tag `3.3.9` or any earlier tag; next release must be `>= 3.3.10` / `3.4.0`.
  - Because `assets/scripts.js` and `assets/scripts.min.js` are currently 0 bytes (empty files are rejected by `ajax-filter-posts`'s `release.py` builder), populate `scripts.js` (or intentionally remove unused files via `.github/release-removals.json`) before Phase 2's builder runs.
  - If renaming `README.txt` -> `readme.txt` or removing `appsero/` / `README.md` from WP.org releases, record explicit reviewed entries in `.github/release-removals.json` (and use two-step `git mv` on macOS APFS for case-only rename).

---

## 4. Coding & Packaging Conventions

1. **Plugin identity:** Main file MUST remain `ajax-load-div.php`. Text domain is `'ajax-load-more-anything'` (Free) and `'load-more-anything-pro'` (Pro).
2. **Prefixes & Globals:** Prefix new Free functions/hooks/classes with `ald_` / `ALD_` (or namespace `LoadMoreAnything\` if introducing autoloaded classes in Phase 4); prefix new Pro symbols with `ald_pro_` / `lmap_` / `LoadMoreAnythingPro\`.
3. **WPCS Ruleset (Phase 1):** Use a non-hidden `phpcs.xml` (not `.phpcs.xml.dist`, avoiding PCP `hidden_files` errors) modeled on `licenser/phpcs.xml`, targeting `WordPress-Core`, `WordPress-Extra`, `WordPress-Docs`, `minimum_wp_version >= 5.8`, `testVersion >= 7.4-`, and short array `[]` allowed.
4. **Asset Parity:** Never edit `admin.js`/`admin.css`/`styles.css`/`lma-pro.js` without regenerating their `.min.*` counterparts (or adopting a single clean build script / unminified source strategy in Phase 1/4).
5. **No Hidden or Dev Artifacts in Releases:** Keep `.DS_Store`, `AGENTS.md`, `docs/`, `phpcs.xml`, `scripts/`, `tests/`, `.github/`, and `.wordpress/` excluded from plugin release ZIPs (via authoritative `.github/release-files.txt`).
