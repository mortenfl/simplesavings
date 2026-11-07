# Changelog

All notable changes to the Nordic Interior theme. The format follows
[Keep a Changelog](https://keepachangelog.com/sv/1.1.0/) and the project uses
[Semantic Versioning](https://semver.org/lang/sv/).

## [1.0.0] – 2026-11-07

### Added
- Logo redrawn as SVG: full version, white version, one-line variants, icon only, favicon and loader. PNG app icons.
- Design system with CSS custom properties (`--ni-*`), locally hosted Jost, Inter and DM Sans, four colour schemes (Linne, Salvia, Ek, Sand).
- Taxonomies Rum (`ni_room`), Stil (`ni_style`) and Butik (`ni_store`) with term meta; term meta for WooCommerce brands and the `pa_farg` colour swatches.
- Campaigns (`ni_campaign`) with date ranges, discount codes, a main campaign and `/go/c/{id}/` redirects with UTM/subID parameters.
- Affiliate links straight to the WooCommerce product URL (`rel="sponsored nofollow noopener"`), anonymous click beacon (`POST /wp-json/nordicinterior/v1/click`) per placement, nightly recalculation of `_ni_clicks` and `_ni_discount`, price history with a "Prissänkt" badge and a nightly link check.
- Smart search: words become filters, synonyms, Swedish stemming, SKU matching, "Menade du …?", anonymous search log, live search with an ARIA combobox.
- Product listings with AJAX filters (category, room, style, brand, store, material, designer, colour swatches, width/depth/height, price, sale), sorting, grid/list, "Ladda fler" or page numbers, `noindex, follow` on filtered URLs.
- Indexable landing pages `/rum/{rum}/kategori/{kategori}/` and `/stil/{stil}/kategori/{kategori}/` with editable titles and intros, in the core sitemap.
- Product page with gallery, store information, large "Till butiken – {butik}" CTA, tabs with specifications and a dimension sketch, and the rails "Passar ihop med", "Liknande till lägre pris", "Fler från …" and related inspiration.
- Shoppa looken as a block and shortcode (mouse, keyboard, bottom sheet on mobile).
- Front page built from sortable sections, also available as `[ni_section]`.
- Mega menu fields, "Alla rum" panel, mobile drawer and bottom navigation; "Skapa huvudmeny" button and `wp nordicinterior menu`.
- Footer with three widget areas, right-hand sidebars with default content, six theme widgets.
- Page templates: Alla varumärken (A–Ö), Alla butiker, Rea, Alla rum and Alla stilar; campaign archive.
- JSON-LD (Product, ItemList, BreadcrumbList, WebSite + SearchAction, Organization, Article), Open Graph / Rich Pins and a Pinterest "Spara" button.
- Theme Adjustments with live preview, export, import and reset.
- Admin: dashboard (clicks per product, store, brand, room, style and placement; searches; campaigns ending soon; broken links; price drops), product list columns, affiliate URL in Quick Edit, "Kom igång", demo import, landing page texts.
- WP-CLI commands `demo`, `menu`, `check-links` and `recalc`.
- `languages/nordicinterior.pot`, `phpcs.xml.dist`, `bin/check-prefix.php`, `bin/make-pot.php`.
