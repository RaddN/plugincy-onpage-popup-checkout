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
            $args['tax_query'] = $tax_query;
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
