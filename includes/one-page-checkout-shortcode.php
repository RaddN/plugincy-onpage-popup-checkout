<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

// shortcode to display one page checkout [plugincy_one_page_checkout product_ids="" category="" tags="" attribute="" terms="" template=""]
function onepaquc_one_page_checkout_shortcode($atts)
{
    $atts = is_array($atts) ? $atts : array();
    $atts = shortcode_atts(array(
        'product_ids' => '',
        'category'    => '',
        'tags'        => '',
        'attribute'   => '',
        'terms'       => '',
        'template'    => 'product-table',
        'limit'       => 100,
    ), $atts);
    foreach (array('product_ids', 'category', 'tags', 'attribute', 'terms', 'template') as $attribute_key) {
        $atts[$attribute_key] = is_scalar($atts[$attribute_key]) ? sanitize_text_field((string) $atts[$attribute_key]) : '';
    }
    $query_limit = is_scalar($atts['limit']) ? min(200, max(1, absint($atts['limit']))) : 100;

    ob_start();

    // Collect product IDs from attributes if product_ids is empty
    $product_ids = array();

    if (!empty($atts['product_ids'])) {
        $product_ids = explode(',', $atts['product_ids']);
        $product_ids = array_slice(array_values(array_unique(array_filter(array_map('absint', $product_ids)))), 0, 200);
    } else {
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => $query_limit,
            'fields'         => 'ids',
            'post_status'    => 'publish',
        );

        $tax_query = array();

        if (!empty($atts['category'])) {
            $tax_query[] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => array_values(array_filter(array_map('sanitize_title', explode(',', $atts['category'])))),
            );
        }

        if (!empty($atts['tags'])) {
            $tax_query[] = array(
                'taxonomy' => 'product_tag',
                'field'    => 'slug',
                'terms'    => array_values(array_filter(array_map('sanitize_title', explode(',', $atts['tags'])))),
            );
        }

        if (!empty($atts['attribute']) && !empty($atts['terms'])) {
            $tax_query[] = array(
                'taxonomy' => 'pa_' . wc_sanitize_taxonomy_name($atts['attribute']),
                'field'    => 'slug',
                'terms'    => array_values(array_filter(array_map('sanitize_title', explode(',', $atts['terms'])))),
            );
        }

        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
        }

        $query = new WP_Query($args);
        $product_ids = $query->posts;
    }

    if (empty($product_ids)) {
        return '<div class="rmenu-one-page-checkout"><p>' . esc_html__('Please provide product IDs, category, tags, or attribute terms.', 'one-page-quick-checkout-for-woocommerce') . '</p></div>';
    }

    $cart = function_exists('onepaquc_get_wc_cart') ? onepaquc_get_wc_cart() : null;
    if ($cart && get_option("onpage_checkout_widget_cart_empty", "1") === "1") {
        $cart->empty_cart();
    }

    foreach ($product_ids as $product_id) {
        $product_id = intval($product_id);
        if ($product_id > 0 && $cart && get_option("onpage_checkout_widget_cart_add", "1") === "1") {
            $product = wc_get_product($product_id);
            if ($product && $product->is_type('variable')) {
                $available_variations = onepaquc_get_validated_variations( $product );
                if (!empty($available_variations)) {
                    $variation_id = $available_variations[0]['variation_id'];
                    $variation = wc_get_product($variation_id);
                    $variation_attributes = isset($available_variations[0]['attributes']) && is_array($available_variations[0]['attributes'])
                        ? $available_variations[0]['attributes']
                        : array();
                    if ($variation instanceof WC_Product_Variation && $variation->get_parent_id() === $product_id && $variation->is_purchasable()) {
                        $cart->add_to_cart($product_id, 1, $variation_id, $variation_attributes);
                    }
                }
            } else {
                // Check if the product is purchasable before adding to cart
                if ($product && $product->is_purchasable()) {
                    $cart->add_to_cart($product_id);
                }
            }
        }
    }
?>
    <div class="rmenu-one-page-checkout" id="checkout-popup">
        <?php
        // Include the checkout template based on the selected template
        $templates = array(
            'product-table'     => 'product-table-template.php',
            'product-list'      => 'product-list-template.php',
            'product-single'    => 'product-single-template.php',
            'product-slider'    => 'product-slider-template.php',
            'product-accordion' => 'product-accordion-template.php',
            'product-tabs'      => 'product-tabs-template.php',
            'pricing-table'     => 'pricing-table-template.php',
        );
        $template_key  = isset($templates[$atts['template']]) ? $atts['template'] : 'product-table';
        $template_file = plugin_dir_path(__FILE__) . '../templates/' . $templates[$template_key];
        if (is_readable($template_file)) {
            include $template_file;
        }
        ?>
    </div>
<?php

    return ob_get_clean();
}
add_shortcode('plugincy_one_page_checkout', 'onepaquc_one_page_checkout_shortcode', 99999);




// single product one page checkout

// Register: [onepaquc_checkout product_id="123" variation_id="456" qty="2" clear_cart="yes" auto_add="yes"]
add_action('init', function () {
    add_shortcode('onepaquc_checkout', function ($atts = []) {

        // --- Shortcode attributes ---
        $atts = shortcode_atts([
            // default enable auto add
            'auto_add'     => 'yes',   // yes|no
            'clear_cart'   => 'no',    // yes|no
            'product_id'   => 0,       // parent product (required to add)
            'variation_id' => 0,       // optional variation id
            'qty'          => 1,       // optional qty
        ], $atts, 'onepaquc_checkout');

        // Basic sanitization / normalization
        $auto_add_value = is_scalar($atts['auto_add']) ? strtolower((string) $atts['auto_add']) : '';
        $clear_cart_value = is_scalar($atts['clear_cart']) ? strtolower((string) $atts['clear_cart']) : '';
        $auto_add     = in_array($auto_add_value, ['yes', 'true', '1'], true);
        $clear_cart   = in_array($clear_cart_value, ['yes', 'true', '1'], true);
        $product_id   = is_scalar($atts['product_id']) ? absint($atts['product_id']) : 0;
        $variation_id = is_scalar($atts['variation_id']) ? absint($atts['variation_id']) : 0;
        $qty          = is_scalar($atts['qty']) ? max(1, absint($atts['qty'])) : 1;

        // --- Add to cart behavior ---
        if ( function_exists('WC') && !is_admin() && !wp_doing_ajax() ) {
            // Only do cart ops if we have a product_id and auto_add is enabled
            if ( $auto_add && $product_id > 0 ) {
                $cart = function_exists('onepaquc_get_wc_cart') ? onepaquc_get_wc_cart() : null;
                if ( $clear_cart && $cart ) {
                    $cart->empty_cart();
                }

                // If variation_id is given, add that specific variation.
                // Otherwise add the simple/parent product.
                if ( $cart ) {
                    // NB: $variation is optional here (empty array ok if attributes aren’t needed)
                    $variation  = [];
                    $cart_item_data = [];

                    // Add and ignore errors silently (avoid breaking the page)
                    try {
                        // When adding a variation, Woo expects the parent variable product ID as $product_id,
                        // and the specific $variation_id you want to add.
                        $product = wc_get_product($product_id);
                        if (!$product instanceof WC_Product || !$product->is_purchasable()) {
                            return '';
                        }
                        if ( $variation_id > 0 ) {
                            $variation_product = wc_get_product($variation_id);
                            if (!$variation_product instanceof WC_Product_Variation || $variation_product->get_parent_id() !== $product_id || !$variation_product->is_purchasable()) {
                                return '';
                            }
                            $variation = $variation_product->get_variation_attributes();
                            $cart->add_to_cart($product_id, $qty, $variation_id, $variation, $cart_item_data);
                        } else {
                            $cart->add_to_cart($product_id, $qty, 0, $variation, $cart_item_data);
                        }
                    } catch ( \Throwable $e ) {
                        // You could log this if needed: error_log($e->getMessage());
                    }
                }
            }
        }

        // --- Render the checkout form ---
        if ( ! function_exists('onepaquc_display_one_page_checkout_form') ) {
            return '';
        }

        // Make sure we capture output (the function echoes)

        ob_start();
        onepaquc_display_one_page_checkout_form();
        $html = ob_get_clean();

        return $html;
    });
});
