<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

// shortcode to display one page checkout [plugincy_one_page_checkout product_ids="" template=""]
function rmenu_one_page_checkout_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'product_ids' => '',
        'template' => 'default'
    ), $atts);
    ob_start();
    // empty cart if no product IDs are provided
    if (empty($atts['product_ids'])) {
        WC()->cart->empty_cart();
    } else {
        // Get the product IDs from the shortcode attribute
        $product_ids = explode(',', $atts['product_ids']);
        //remove any whitespace from product IDs
        $product_ids = array_map('trim', $product_ids);

        if (!empty($atts['product_ids']) && get_option("onpage_checkout_cart_empty")==="1") {
            WC()->cart->empty_cart();
        }
        // Loop through each product ID and add it to the cart
        foreach ($product_ids as $product_id) {
            $product_id = intval($product_id);
            // check if the product ID is valid & wc initialized & WC is active & WC()cart is initialized
            if ($product_id > 0 && class_exists('WooCommerce') && WC()->cart) {
                WC()->cart->add_to_cart($product_id);
            }
        }
    }
?>
    <div class="rmenu-one-page-checkout">
        <?php
        // Include the checkout template based on the selected template
        if ($atts['template'] === 'default') { ?>
            <div class="one-page-checkout-container">
                <?php echo do_shortcode('[woocommerce_checkout]'); ?>
            </div><?php
                } else {
                    include plugin_dir_path(__FILE__) . '../templates/' . sanitize_file_name($atts['template']) . '-template.php';
                }
                    ?>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('plugincy_one_page_checkout', 'rmenu_one_page_checkout_shortcode');
