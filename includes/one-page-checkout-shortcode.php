<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

// shortcode to display one page checkout [plugincy_one_page_checkout product_ids="" category="" tags="" attribute="" terms="" template=""]
function onepaquc_one_page_checkout_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'product_ids' => '',
        'category'    => '',
        'tags'        => '',
        'attribute'   => '',
        'terms'       => '',
        'template'    => 'product-table'
    ), $atts);

    ob_start();

    // Collect product IDs from attributes if product_ids is empty
    $product_ids = array();

    if (!empty($atts['product_ids'])) {
        $product_ids = explode(',', $atts['product_ids']);
        $product_ids = array_map('trim', $product_ids);
    } else {
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'post_status'    => 'publish',
        );

        $tax_query = array();

        if (!empty($atts['category'])) {
            $tax_query[] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => array_map('trim', explode(',', $atts['category'])),
            );
        }

        if (!empty($atts['tags'])) {
            $tax_query[] = array(
                'taxonomy' => 'product_tag',
                'field'    => 'slug',
                'terms'    => array_map('trim', explode(',', $atts['tags'])),
            );
        }

        if (!empty($atts['attribute']) && !empty($atts['terms'])) {
            $tax_query[] = array(
                'taxonomy' => 'pa_' . wc_sanitize_taxonomy_name($atts['attribute']),
                'field'    => 'slug',
                'terms'    => array_map('trim', explode(',', $atts['terms'])),
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

    if (class_exists('WooCommerce') && WC()->cart && get_option("onpage_checkout_widget_cart_empty", "1") === "1") {
        WC()->cart->empty_cart();
    }

    foreach ($product_ids as $product_id) {
        $product_id = intval($product_id);
        if ($product_id > 0 && class_exists('WooCommerce') && WC()->cart && get_option("onpage_checkout_widget_cart_add", "1") === "1") {
            $product = wc_get_product($product_id);
            if ($product && $product->is_type('variable')) {
                $available_variations = $product->get_available_variations();
                if (!empty($available_variations)) {
                    $variation_id = $available_variations[0]['variation_id'];
                    $variation = wc_get_product($variation_id);
                    if ($variation && $variation->is_purchasable()) {
                        WC()->cart->add_to_cart($product_id, 1, $variation_id);
                    }
                }
            } else {
                // Check if the product is purchasable before adding to cart
                if ($product && $product->is_purchasable()) {
                    WC()->cart->add_to_cart($product_id);
                }
            }
        }
    }
?>
    <div class="rmenu-one-page-checkout" id="checkout-popup">
        <?php
        // Include the checkout template based on the selected template
        if ($atts['template'] === 'product-table') {
            include plugin_dir_path(__FILE__) . '../templates/product-table-template.php';
        } elseif ($atts['template'] === 'product-list') {
            include plugin_dir_path(__FILE__) . '../templates/product-list-template.php';
        } elseif ($atts['template'] === 'product-single') {
            include plugin_dir_path(__FILE__) . '../templates/product-single-template.php';
        } elseif ($atts['template'] === 'product-slider') {
            include plugin_dir_path(__FILE__) . '../templates/product-slider-template.php';
        } elseif ($atts['template'] === 'product-accordion') {
            include plugin_dir_path(__FILE__) . '../templates/product-accordion-template.php';
        } elseif ($atts['template'] === 'product-tabs') {
            include plugin_dir_path(__FILE__) . '../templates/product-tabs-template.php';
        } else {
            include plugin_dir_path(__FILE__) . '../templates/pricing-table-template.php';
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
        $auto_add     = in_array(strtolower($atts['auto_add']), ['yes', 'true', '1'], true);
        $clear_cart   = in_array(strtolower($atts['clear_cart']), ['yes', 'true', '1'], true);
        $product_id   = absint($atts['product_id']);
        $variation_id = absint($atts['variation_id']);
        $qty          = max(1, absint($atts['qty']));

        // --- Add to cart behavior ---
        if ( function_exists('WC') && !is_admin() && !wp_doing_ajax() ) {
            // Only do cart ops if we have a product_id and auto_add is enabled
            if ( $auto_add && $product_id > 0 ) {
                if ( $clear_cart && WC()->cart ) {
                    WC()->cart->empty_cart();
                }

                // If variation_id is given, add that specific variation.
                // Otherwise add the simple/parent product.
                if ( WC()->cart ) {
                    // NB: $variation is optional here (empty array ok if attributes aren’t needed)
                    $variation  = [];
                    $cart_item_data = [];

                    // Add and ignore errors silently (avoid breaking the page)
                    try {
                        // When adding a variation, Woo expects the parent variable product ID as $product_id,
                        // and the specific $variation_id you want to add.
                        if ( $variation_id > 0 ) {
                            WC()->cart->add_to_cart($product_id, $qty, $variation_id, $variation, $cart_item_data);
                        } else {
                            WC()->cart->add_to_cart($product_id, $qty, 0, $variation, $cart_item_data);
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
