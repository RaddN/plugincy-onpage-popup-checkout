<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

// shortcode to display one page checkout [plugincy_one_page_checkout product_ids="" template=""]
function onepaquc_one_page_checkout_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'product_ids' => '',
        'template' => 'product-table'
    ), $atts);
    ob_start();
    // empty cart if no product IDs are provided
    if (empty($atts['product_ids'])) {
        return
            '<div class="rmenu-one-page-checkout"><p>' . esc_html__('Please provide product IDs.', 'one-page-quick-checkout-for-woocommerce') . '</p></div>';
    } else {
        // Get the product IDs from the shortcode attribute
        $product_ids = explode(',', $atts['product_ids']);
        //remove any whitespace from product IDs
        $product_ids = array_map('trim', $product_ids);

        if (!empty($atts['product_ids']) && class_exists('WooCommerce') && WC()->cart && get_option("onpage_checkout_widget_cart_empty", "1") === "1") {
            WC()->cart->empty_cart();
        }
        // Loop through each product ID and add it to the cart
        foreach ($product_ids as $product_id) {
            $product_id = intval($product_id);
            // check if the product ID is valid & wc initialized & WC is active & WC()cart is initialized
            if ($product_id > 0 && class_exists('WooCommerce') && WC()->cart && get_option("onpage_checkout_widget_cart_add", "1") === "1") {
                WC()->cart->add_to_cart($product_id);
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
