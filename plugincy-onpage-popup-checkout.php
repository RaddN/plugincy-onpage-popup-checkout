<?php
/*
/**
 * Plugin Name: PlugincyPopup Checkout
 * Description: Shows a popup checkout form on button click.
 * Version: 1.0
 * Author: plugincy
 * Author URI: https://plugincy.com/
 */
// Include the admin notice file
require_once plugin_dir_path(__FILE__) . 'includes/admin-notice.php';

// admin menu
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';


// Enqueue scripts and styles
function rmenu_cart_enqueue_scripts() {
    wp_enqueue_style('rmenu-cart-style', plugin_dir_url(__FILE__) . 'assets/css/rmenu-cart.css',true);
    wp_enqueue_script('rmenu-cart-script', plugin_dir_url(__FILE__) . 'assets/js/rmenu-cart.js', array('jquery'), null, true);
    wp_enqueue_script('cart-script', plugin_dir_url(__FILE__). 'assets/js/cart.js', array('jquery'), null, true);
    // Localize script for AJAX URL and WooCommerce cart variables
     wp_localize_script('cart-script', 'wc_cart_params', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
    // Retrieve the rmsg_editor value
    $rmsg_editor_value = get_option('rmsg_editor', '');

    // Localize the script with the rmsg_editor value
    wp_localize_script('rmenu-cart-script', 'rmsgValue', array(
        'rmsgEditor' => $rmsg_editor_value,
    ));
    wp_localize_script('rmenu-cart-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'rmenu_cart_enqueue_scripts',20);

// add shortcode
if (get_option('bd_affiliate_api_key') && get_option('bd_affiliate_validity_days')!=="0"){
require_once plugin_dir_path(__FILE__) . 'includes/rmenu-shortcode.php';

// change the positon of coupon button
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form');

add_action( 'woocommerce_review_order_before_payment', 'woocommerce_checkout_coupon_form' );


// update cart content

add_action('wp_ajax_get_cart_content', 'get_cart_content');
add_action('wp_ajax_nopriv_get_cart_content', 'get_cart_content');
function get_cart_content() {
    ob_start();

    // Use include to load the template from your plugin's directory
    include plugin_dir_path(__FILE__) . 'includes/cart-template.php';

    $cart_html = ob_get_clean();

    // Send response with cart HTML and count
    wp_send_json_success([
        'cart_html' => $cart_html,
        'cart_count' => WC()->cart->get_cart_contents_count()
    ]);
}

// update quantity

add_action('wp_ajax_update_cart_item_quantity', 'update_cart_item_quantity');
add_action('wp_ajax_nopriv_update_cart_item_quantity', 'update_cart_item_quantity');
function update_cart_item_quantity() {
    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    $quantity = (int)$_POST['quantity'];

    if (WC()->cart->set_quantity($cart_item_key, $quantity)) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Could not update quantity.');
    }
}


// remove cart item
add_action('wp_ajax_remove_cart_item', 'handle_remove_cart_item');
add_action('wp_ajax_nopriv_remove_cart_item', 'handle_remove_cart_item');
function handle_remove_cart_item() {
    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);

    if (WC()->cart->remove_cart_item($cart_item_key)) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Could not remove item.');
    }
}

// update checkout form on ajax complete
function update_checkout_form() {
    ob_start();

    // Use include to load the template from your plugin's directory
    include plugin_dir_path(__FILE__) . 'includes/popup-template.php';

    $checkout_form = ob_get_clean();

    // Send response with cart HTML and count
    wp_send_json_success(array('checkout_form' => $checkout_form));
}

add_action('wp_ajax_update_checkout', 'update_checkout_form');
add_action('wp_ajax_nopriv_update_checkout', 'update_checkout_form');


// Customize WooCommerce checkout text labels
function custom_woocommerce_checkout_text($translated_text, $text, $domain) {
    if ($domain === 'woocommerce') {
        switch ($text) {
            case 'Product':
                $translated_text = get_option("txt_product")? esc_attr(get_option("txt_product", 'Product')):"Product"; 
                break;
            case 'Subtotal':
                $translated_text =  get_option("txt_subtotal")? esc_attr(get_option("txt_subtotal", 'Subtotal')):"Subtotal"; 
                break;
            case 'Shipping':
                $translated_text =  get_option("txt_shipping")? esc_attr(get_option("txt_shipping", 'Shipping')):"Shipping"; 
                break;
            case 'Total':
                $translated_text =  get_option("txt_total")? esc_attr(get_option("txt_total", 'Total')):"Total"; 
                break;
            case 'Place order':
                $translated_text =  get_option("btn_place_order")? esc_attr(get_option("btn_place_order", 'Place order')):"Place order"; 
                break;
            case 'Billing details':
                $translated_text =  get_option("txt_billing_details")? esc_attr(get_option("txt_billing_details", 'Billing details')):"Billing details"; 
                break;
        }
    }
    return $translated_text;
}
add_filter('gettext', 'custom_woocommerce_checkout_text', 20, 3);


// Change "Shipping" label in WooCommerce shipping totals section
function custom_woocommerce_shipping_label($label, $package_name) {
    return get_option("txt_shipping")? esc_attr(get_option("txt_shipping", 'Shipping')):"Shipping"; // Change "Shipping" to "Delivery Charges"
}
add_filter('woocommerce_shipping_package_name', 'custom_woocommerce_shipping_label', 10, 2);



}else{
    require_once plugin_dir_path(__FILE__) . 'includes/without_api_short_code';
}


?>


