# Compatibility Test Matrix

Use this matrix before release builds that touch checkout, cart, product loops, AJAX, or frontend assets.

## Runtime Versions

| Area | Versions | Required checks | Status |
| --- | --- | --- | --- |
| PHP | 7.2, 7.4, 8.1, 8.2, 8.3 | Activation, PHP lint, checkout render, AJAX add to cart, coupon apply/remove, cart quantity changes | Not run |
| WordPress | Minimum supported, latest stable | Plugin activation, admin settings save, shortcode/block render | Not run |
| WooCommerce | Minimum supported, latest stable | Classic checkout, Checkout Blocks, cart drawer, product archive, single product, variable product | Not run |
| Database | MySQL 5.7, MySQL 8.0, MariaDB 10.4+ | Activation tables/options, cart recovery tables, checkout/order placement | Not run |

## Themes

| Theme | Required checks | Status |
| --- | --- | --- |
| Storefront | Shop loop button placement, single product button placement, classic checkout, Checkout Blocks | Not run |
| Astra | Shop loop button placement, Elementor compatibility, classic checkout, Checkout Blocks | Partially run locally |
| Blocksy | Product cards, archive AJAX/filter plugins, quick checkout fallback, Checkout Blocks | Not run |
| Kadence | Product cards, archive AJAX/filter plugins, quick checkout fallback, Checkout Blocks | Not run |

## Checkout And Payment Plugins

| Plugin / Surface | Required checks | Status |
| --- | --- | --- |
| WooPayments | Classic checkout payment render, Checkout Blocks payment render, order placement | Not run |
| Stripe for WooCommerce | Express checkout, payment fields, order placement, field visibility settings | Not run |
| PayPal Payments | Express buttons, checkout redirect, order placement, field visibility settings | Not run |
| Classic checkout shortcode | Quantity controls, product links, coupon apply/remove, order review refresh | Not run |
| WooCommerce Checkout Blocks | Product-name accessibility text, quantity controls, product links, payment render | Partially run locally |

## Builders, Caching, And Dynamic Loops

| Plugin / Surface | Required checks | Status |
| --- | --- | --- |
| Elementor | Product widgets, one-page checkout widget, Buy Now button widget, AJAX-rendered sections | Partially run locally |
| LiteSpeed Cache | Cart/checkout exclusions, minify/defer JS, product loop fallback after AJAX/filter updates | Not run |
| WP Rocket | Cart/checkout exclusions, delay JS, minify/defer JS, product loop fallback after AJAX/filter updates | Not run |
| Product filter / infinite scroll plugins | Product-card detection, duplicate button prevention, MutationObserver fallback | Not run |

## Release Gate

- No escaped plugin HTML inside visible or screen-reader Checkout Blocks text.
- No unbounded product query from `[plugincy_one_page_checkout]` unless `allow_empty_query="yes"` is explicitly set.
- Product-loop fallback must not depend on global `ajaxComplete`.
- Public AJAX requests used by plugin assets must use `onepaquc_`-prefixed actions.
- Legacy AJAX aliases must not terminate unrelated third-party AJAX requests.
