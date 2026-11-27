# Load More Anything — WordPress Plugin

Add **Load More buttons**, **Ajax pagination**, and **Infinite Scroll** to blog posts, Gutenberg Query Loop blocks, WooCommerce products, comments, lists, tables, or any custom CSS selector — without writing code.

- **WordPress.org:** https://wordpress.org/plugins/ajax-load-more-anything/
- **Pro Edition:** https://addonmaster.com/load-more-anything/
- **Documentation:** https://addonmaster.com/docs-category/load-more-anything/

## Features

- **General (DOM) Load More:** Progressively reveal items already rendered on the page with a live `+[count]` remaining counter (up to 6 wrappers in Free; unlimited in Pro).
- **Ajax Pagination & Infinite Scroll:** Fetch paginated posts, WooCommerce shop grids, Gutenberg Query Loops, comments, and custom post types without full-page reloads (1 wrapper in Free; unlimited in Pro).
- **One-Click Selector Presets:** Starter configurations for WordPress Blog Archives, Gutenberg Query Loop, WooCommerce Shop Grid, Elementor Posts/Loop, Comments, Lists, and Table Rows.
- **Modern Frontend Runtime:** Data-driven `assets/scripts.js` powered by `DOMParser`, `IntersectionObserver`, per-wrapper XHR error recovery, polite ARIA live announcements, and conditional asset loading.
- **Pro Extensions:** Unlimited wrappers, custom Ajax Load More buttons, browser URL & page title sync, infinite scroll trigger offset, 21 extra CSS display types, button classes, and custom JavaScript.

## Installation

1. In WordPress Admin, go to **Plugins → Add New** and search for **Load More Anything**.
2. Click **Install Now** and **Activate**.
3. Open **Load More Anything** from the admin menu to configure General or Ajax selectors.

## Development

See [AGENTS.md](AGENTS.md) and [docs/development.md](docs/development.md) for local testing, WPCS (`phpcs.xml`), WordPress Plugin Check, and guarded WordPress.org release workflows.
