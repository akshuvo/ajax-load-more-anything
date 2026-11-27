=== Load More Anything ===
Contributors: mdshuvo, addonmaster
Tags: infinite scroll, ajax load more, ajax pagination, load more, ajax
Requires at least: 5.8
Tested up to: 7.1
Stable tag: 3.3.10
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add Load More buttons, Ajax pagination, and Infinite Scroll to blog posts, WooCommerce products, comments, lists, tables, or any CSS selector.

== Description ==

**Load More Anything** lets you turn any WordPress content list or paginated archive into a smooth **Load More** or **Infinite Scroll** experience without writing code. Works with blog posts, Gutenberg Query Loop blocks, WooCommerce shop grids, comments, custom post types, lists, tables, galleries, and custom containers.

### Key Features

* **General (DOM) Load More** — Hide and progressively reveal items already on the page with a live `+[count]` remaining counter (up to 6 wrappers in Free; unlimited in Pro).
* **Ajax Pagination & Infinite Scroll** — Fetch paginated WordPress archives, WooCommerce shops, Gutenberg Query Loops, and custom post types dynamically without reloading the page (1 wrapper in Free; unlimited in Pro).
* **One-Click Selector Presets** — Built-in starter recipes for WordPress Blog Archives, Gutenberg Query Loop, WooCommerce Shop Grid, Elementor Posts/Loop, Comments, Lists, and Table Rows.
* **Modern Performance & Accessibility** — Uses `IntersectionObserver` infinite scroll, safe `DOMParser` HTML extraction, polite ARIA live announcements, and conditional asset loading (zero frontend footprint when unconfigured).
* **Flexible Data Implementation** — Choose Replace Data, Insert After (Append), or Insert Before (Prepend) on any number of target CSS selectors.
* **Custom CSS & Compatibility** — Built-in CodeMirror CSS editor, Elementor editor preview protection, WPML translation support, and optional scroll-to-container after load.

### Pro Features

Upgrade to [Load More Anything Pro](https://addonmaster.com/load-more-anything/) for:

* Unlimited General and Ajax wrappers
* Custom Ajax Load More button injection
* Dynamic Browser URL and Page Title sync
* Infinite Scroll Trigger Offset control
* 21 additional CSS display types (`grid`, `inline-block`, `table-row`, etc.) & custom button classes
* Custom JavaScript editor & priority support

### Live Demos

* [Infinite Scroll Blog Demo](https://plugins.addonmaster.com/ajax-load-more-anything/blog/)
* [WooCommerce Products Ajax Pagination](https://plugins.addonmaster.com/ajax-load-more/store/)
* [Load WordPress Comments](https://plugins.addonmaster.com/ajax-load-more/long-comments/)
* [Load More List Items](https://plugins.addonmaster.com/ajax-load-more/load-more-list/)
* [Load More Table Rows](https://plugins.addonmaster.com/ajax-load-more/load-more-table-data/)

### Documentation & Support

* [Plugin Documentation](https://addonmaster.com/docs-category/load-more-anything/)
* [WordPress.org Support Forum](https://wordpress.org/support/plugin/ajax-load-more-anything)
* [Submit a Support Ticket](https://addonmaster.com/submit-a-ticket/)
* [Leave a Review](https://wordpress.org/support/plugin/ajax-load-more-anything/reviews/?filter=5#new-post)

== Privacy Policy ==

Load More Anything uses the [Appsero](https://appsero.com) SDK to collect optional telemetry data only upon explicit user consent. This helps us troubleshoot issues faster and improve the plugin.

Appsero SDK **does not gather any data by default**. It collects basic telemetry data **only when an administrator opts in via the admin notice**.

Learn more about [how Appsero collects and uses data](https://appsero.com/privacy-policy/).

== Installation ==

1. Navigate to **Plugins > Add New** in your WordPress admin dashboard.
2. Search for **Load More Anything**.
3. Click **Install Now**, then **Activate**.
4. Open **Load More Anything** in the admin menu and pick a preset or enter your CSS selectors.

== Frequently Asked Questions ==

= How can I show the remaining item count on the button? =

Include `[count]` inside your General Load More button label (for example: `Load +[count] More`), and it will automatically update to show how many hidden items remain.

= How do I find the right CSS selectors for my theme? =

Start with one of the built-in Quick Presets in the General or Ajax tabs (WordPress Blog, Gutenberg Query Loop, WooCommerce Shop, Elementor Posts), or inspect your page in browser DevTools to copy the container and pagination link selectors.

= Does it work with WooCommerce, Elementor, and Gutenberg? =

Yes. Load More Anything works with any theme or page builder that outputs standard HTML wrappers or pagination links, including WooCommerce shop grids, Elementor Posts/Loop grids, and Gutenberg Query Loop blocks.

= Where can I get help? =

Browse our [Documentation](https://addonmaster.com/docs-category/load-more-anything/) or open a topic on the [Support Forum](https://wordpress.org/support/plugin/ajax-load-more-anything/).

== Screenshots ==

1. Modern Settings Overview & Quick-Start Recipes
2. General Selectors Configuration with One-Click Presets
3. Ajax Based Pagination & Infinite Scroll Configuration
4. Settings & Custom CSS Code Editor
5. Infinite Scroll in Action
6. Load WordPress Comments
7. WooCommerce Products Ajax Pagination
8. Load More Button with Live Counter
9. Load More List Items
10. Load More Table Rows

== Changelog ==

= 3.3.10 – 2026-11-27 =
* Security: Added `ABSPATH` direct-access guards to all PHP files, added `manage_options` capability checks to all admin AJAX handlers, added `wp_unslash()` and deep sanitization for General, Ajax, and Custom CSS settings, and eliminated raw inline script/style injection vectors.
* Modernization: Replaced PHP-generated inline frontend scripts with a data-driven `assets/scripts.js` runtime using `DOMParser` and `IntersectionObserver`, per-wrapper XHR error recovery, polite ARIA live announcements, and conditional frontend asset loading.
* Fixed: Resolved jQuery 3+ `$(window).load` and `.context.href` runtime errors, PHP 8 undefined `$button_classes` warning, duplicate DOM IDs, global `admin_footer` Go Pro modal leak, CodeMirror save sync, and Appsero Privacy Policy plugin name typo.
* UI/UX: Redesigned the settings panel with a responsive WordPress-native interface, sticky Save bar with toast feedback, One-Click Selector Presets (Blog, Gutenberg Query Loop, WooCommerce, Elementor, Lists, Tables, Comments), and optional smooth scroll-to-container after Ajax load.
* Compatibility: Verified with WordPress 7.1 and PHP 8.4; raised minimum requirements to WordPress 5.8 and PHP 7.4.

= 3.3.9 – Nov 20, 2025 =
* Checked with Latest WordPress
* Added optional Appsero telemetry SDK

= 3.3.8 – Apr 16, 2024 =
* Code Improvements
* Translation updates
* Checked with Latest WordPress (v6.5.2)

= 3.3.7 – Mar 29, 2024 =
* Code Improvement
* Added: Disable jQuery Dependency option
* Fixed: Elementor Editor Issue
* Checked with Latest WordPress

= 3.3.6 – Mar 21, 2024 =
* Code Improvement
* Trigger Offset option support for Pro
* Scripts and Styles enqueue improvement

= 3.3.4 – Feb 07, 2024 =
* WordPress 6.4.3 Compatibility
* Security Improvement: Nonce validation and admin capability check added to settings save

= 3.3.3 – Dec 26, 2023 =
* Tested up to: 6.4.2

= 3.3.2 – Oct 26, 2023 =
* Tested up to: 6.3

= 3.3.1 – Apr 04, 2023 =
* Tested up to: 6.2

= 3.2.0 – Feb 09, 2023 =
* WordPress 6.1.1 Compatibility
* Code optimization

= 3.1.0 – Oct 25, 2022 =
* WordPress 6.0.3 Compatibility
* Fix: Pro modal

= 3.0.0 – Oct 10, 2022 =
* New Dashboard & Ajax Based Wrapper
* Data Implement Type: Replace Data, Insert After, Insert Before
* Ajax Preloader & General Wrapper Improvements

= 2.4.7 – Jan 31, 2022 =
* WordPress 5.9 Compatibility

= 2.4.0 – Feb 06, 2021 =
* WordPress 5.6.1 Compatibility

= 2.2.6 – Oct 12, 2020 =
* Added `+[count]` button counter, removed Bootstrap assets, added `.pot` template

= 1.0 =
* Initial release.
