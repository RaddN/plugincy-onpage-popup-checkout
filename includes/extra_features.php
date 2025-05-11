<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Add product image to WooCommerce checkout page cart items
 */
function onepaquc_add_product_image_to_checkout_cart_items($product_name, $cart_item, $cart_item_key)
{
    if (get_option("rmenu_add_img_before_product") !== "1") {
        return $product_name;
    }
    // Get the product
    $product = $cart_item['data'];

    // Get product thumbnail
    $thumbnail = $product->get_image(array(50, 50));

    // Return the image followed by the product name
    return '<div class="checkout-product-item"><div class="checkout-product-image">' . $thumbnail . '</div><div class="checkout-product-name">' . $product_name . '</div></div>';
}
add_filter('woocommerce_cart_item_name', 'onepaquc_add_product_image_to_checkout_cart_items', 10, 3);

// add a random product in cart

if (get_option('rmenu_at_one_product_cart',1)) {
    add_action('template_redirect', 'onepaquc_add_random_product_if_cart_empty');
}

function onepaquc_add_random_product_if_cart_empty()
{

    // If cart is empty
    if (WC()->cart->is_empty()) {

        // Get one random product ID
        $random_product = wc_get_products(array(
            'status'    => 'publish',
            'limit'     => 1,
            'orderby'   => 'rand',
            'return'    => 'ids',
            'type'      => 'simple', // Change to 'variable' if needed
        ));

        if (!empty($random_product)) {
            WC()->cart->add_to_cart($random_product[0], 1);
        }
    }
}

if (get_option('rmenu_disable_cart_page',0)) {
add_action('template_redirect', 'disable_cart_page_redirect');
function disable_cart_page_redirect() {
    if (is_cart()) {
        wp_redirect(wc_get_checkout_url());
        exit;
    }
}
}

if (get_option('rmenu_link_product',0)) {
add_filter( 'woocommerce_cart_item_name', 'link_product_name_on_checkout', 10, 3 );
function link_product_name_on_checkout( $product_name, $cart_item, $cart_item_key ) {
    // Only apply on the checkout page
    if ( is_checkout() ) {
        $product = $cart_item['data'];
        $product_link = get_permalink( $product->get_id() );
        $product_name = sprintf( '<a href="%s">%s</a>', esc_url( $product_link ), $product_name );
    }
    return $product_name;
}
}

