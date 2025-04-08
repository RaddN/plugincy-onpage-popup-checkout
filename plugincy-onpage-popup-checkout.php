<?php
/*
/**
 * Plugin Name: PlugincyPopup Checkout
 * Description: Shows a popup checkout form on button click.
 * Version: 1.0
 * Author: plugincy
 * Author URI: https://plugincy.com/
 */


if (! defined('ABSPATH')) exit; // Exit if accessed directly


// Include the admin notice file
require_once plugin_dir_path(__FILE__) . 'includes/admin-notice.php';

// admin menu
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';

// include one page checkout shortcode
require_once plugin_dir_path(__FILE__) . 'includes/one-page-checkout-shortcode.php';


// Enqueue scripts and styles
function rmenu_cart_enqueue_scripts()
{
    wp_enqueue_style('rmenu-cart-style', plugin_dir_url(__FILE__) . 'assets/css/rmenu-cart.css', "1.0.0");
    wp_enqueue_script('rmenu-cart-script', plugin_dir_url(__FILE__) . 'assets/js/rmenu-cart.js', array('jquery'), "1.0.0", true);
    wp_enqueue_script('cart-script', plugin_dir_url(__FILE__) . 'assets/js/cart.js', array('jquery'), "1.0.0", true);
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
add_action('wp_enqueue_scripts', 'rmenu_cart_enqueue_scripts', 20);

// add shortcode
// if (get_option('bd_affiliate_api_key') && get_option('bd_affiliate_validity_days')!=="0"){
require_once plugin_dir_path(__FILE__) . 'includes/rmenu-shortcode.php';

// change the positon of coupon button

// function custom_move_coupon_section() {
//     remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
//     add_action('woocommerce_review_order_before_payment', 'woocommerce_checkout_coupon_form', 10);
// }
// add_action('woocommerce_checkout_init', 'custom_move_coupon_section');

// update cart content

add_action('wp_ajax_get_cart_content', 'get_cart_content');
add_action('wp_ajax_nopriv_get_cart_content', 'get_cart_content');
function get_cart_content()
{
    ob_start();

    // Use include to load the template from your plugin's directory
    rmenu_cart();

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
function update_cart_item_quantity()
{
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
function handle_remove_cart_item()
{
    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);

    error_log("Removing cart item: " . $cart_item_key); // Log the cart item key

    if (WC()->cart->remove_cart_item($cart_item_key)) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Could not remove item.');
    }
}

// update checkout form on ajax complete
function update_checkout_form()
{
    ob_start();

    // Use include to load the template from your plugin's directory
    rmenu_checkout();

    $checkout_form = ob_get_clean();

    // Send response with cart HTML and count
    wp_send_json_success(array('checkout_form' => $checkout_form));
}

add_action('wp_ajax_update_checkout', 'update_checkout_form');
add_action('wp_ajax_nopriv_update_checkout', 'update_checkout_form');


// Customize WooCommerce checkout text labels
function custom_woocommerce_checkout_text($translated_text, $text, $domain)
{
    if ($domain === 'woocommerce') {
        switch ($text) {
            case 'Product':
                $translated_text = get_option("txt_product") ? esc_attr(get_option("txt_product", 'Product')) : "Product";
                break;
            case 'Subtotal':
                $translated_text =  get_option("txt_subtotal") ? esc_attr(get_option("txt_subtotal", 'Subtotal')) : "Subtotal";
                break;
            case 'Shipping':
                $translated_text =  get_option("txt_shipping") ? esc_attr(get_option("txt_shipping", 'Shipping')) : "Shipping";
                break;
            case 'Total':
                $translated_text =  get_option("txt_total") ? esc_attr(get_option("txt_total", 'Total')) : "Total";
                break;
            case 'Place order':
                $translated_text =  get_option("btn_place_order") ? esc_attr(get_option("btn_place_order", 'Place order')) : "Place order";
                break;
            case 'Billing details':
                $translated_text =  get_option("txt_billing_details") ? esc_attr(get_option("txt_billing_details", 'Billing details')) : "Billing details";
                break;
        }
    }
    return $translated_text;
}
add_filter('gettext', 'custom_woocommerce_checkout_text', 20, 3);


// Change "Shipping" label in WooCommerce shipping totals section
function custom_woocommerce_shipping_label($label, $package_name)
{
    return get_option("txt_shipping") ? esc_attr(get_option("txt_shipping", 'Shipping')) : "Shipping"; // Change "Shipping" to "Delivery Charges"
}
add_filter('woocommerce_shipping_package_name', 'custom_woocommerce_shipping_label', 10, 2);



// }else{
//     require_once plugin_dir_path(__FILE__) . 'includes/without_api_short_code';
// }

// Add JavaScript to handle the AJAX removal without reloading the page (optional but recommended)
add_action('wp_footer', 'add_remove_button_checkout_js');

function add_remove_button_checkout_js()
{
    if (!is_checkout()) return;
?>
    <!-- <script type="text/javascript">
        jQuery(document).ready(function($) {
            $(document.body).on('click', '.remove-item-checkout', function(e) {
                e.preventDefault();

                var removeUrl = $(this).attr('href');

                $.ajax({
                    type: 'GET',
                    url: removeUrl,
                    dataType: 'html',
                    success: function() {
                        $(document.body).trigger('update_checkout');
                    }
                });
            });
        });
    </script> -->
<?php
}





function wc_checkout_block_register()
{
    wp_register_script(
        'wc-checkout-block',
        plugins_url('block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor'),
        filemtime(plugin_dir_path(__FILE__) . 'block.js')
    );

    register_block_type('wc/checkout-block', array(
        'editor_script' => 'wc-checkout-block',
        'render_callback' => 'wc_checkout_block_render',
    ));
}
add_action('init', 'wc_checkout_block_register');

function wc_checkout_block_render()
{
    ob_start(); ?>

<?
    return ob_get_clean();
}

require_once plugin_dir_path(__FILE__) . 'includes/cart-template.php';
require_once plugin_dir_path(__FILE__) . 'includes/popup-template.php';

// checkout popup form

function rmenu_checkout_popup($isonepagewidget = false)
{
?>
    <div class="checkout-popup <?php echo $isonepagewidget ? 'onepagecheckoutwidget' : ''; ?>" data-isonepagewidget="<?php echo $isonepagewidget; ?>" style="<?php echo $isonepagewidget ? 'display: block; position: unset; transform: unset; box-shadow: none; background: unset; width: 100%; max-width: 100%; height: 100%;' : 'display:none'; ?>;">
        <?php
        rmenu_checkout($isonepagewidget);
        ?>
    </div>
<?php
}

/**
 * One Page Checkout for WooCommerce
 * 
 * Adds a checkbox to product settings and displays checkout form directly on product page
 * when enabled, creating a streamlined purchasing experience.
 */

/**
 * Add One Page Checkout checkbox to product type options
 */
function add_one_page_checkout_to_product_type_options($product_type_options)
{
    $product_type_options['one_page_checkout'] = array(
        'id'            => '_one_page_checkout',
        'wrapper_class' => '',
        'label'         => __('One Page Checkout', 'woocommerce'),
        'description'   => __('Enable one page checkout for this product', 'woocommerce'),
        'default'       => 'no'
    );

    return $product_type_options;
}
add_filter('product_type_options', 'add_one_page_checkout_to_product_type_options');

/**
 * Save One Page Checkout option
 */
function save_one_page_checkout_option($post_id)
{
    $is_one_page_checkout = isset($_POST['_one_page_checkout']) ? 'yes' : 'no';
    update_post_meta($post_id, '_one_page_checkout', $is_one_page_checkout);
}
add_action('woocommerce_process_product_meta', 'save_one_page_checkout_option', 10);


/**
 * Display checkout form on single product pages when One Page Checkout is enabled
 */
function display_checkout_on_single_product()
{
    // Only run on single product pages
    if (!is_product()) {
        global $post;
        // if post content is not contains plugincy_one_page_checkout shortcode
        if (strpos($post->post_content, '[plugincy_one_page_checkout') === false) {
            add_action('wp_head', 'rmenu_checkout_popup');
        }
        return;
    }

    // Get product ID and ensure we have a valid product object
    $product_id = get_the_ID();
    $product = wc_get_product($product_id);

    if (!$product || !is_a($product, 'WC_Product')) {
        global $post;
        // if post content is not contains plugincy_one_page_checkout shortcode
        if (strpos($post->post_content, '[plugincy_one_page_checkout') === false) {
            add_action('wp_head', 'rmenu_checkout_popup');
        }
        return;
    }

    // Check if One Page Checkout is enabled for this product
    $one_page_checkout = get_post_meta($product_id, '_one_page_checkout', true);

    if ($one_page_checkout === 'yes') {

        if (!WC()->cart->is_empty() && get_option("onpage_checkout_cart_empty", "1") === "1") {
            // Empty the cart
            WC()->cart->empty_cart();
        }

        // Add this product to cart
        // Check if the product is already in the cart
        $cart_item_key = WC()->cart->generate_cart_id($product_id);
        $cart_item = WC()->cart->get_cart_item($cart_item_key);
        if (!$cart_item && get_option("onpage_checkout_cart_add", "1") === "1") {
            WC()->cart->add_to_cart($product_id, 1);
        }
        // Add checkout form before product tabs
        add_action('woocommerce_after_single_product_summary', 'display_one_page_checkout_form',  get_option("onpage_checkout_position", '9'));

        if (get_option("onpage_checkout_hide_cart_button") === "1") {
            // Hide the add to cart button
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
            // Optionally, hide the add to cart button
            add_filter('woocommerce_is_purchasable', function ($is_purchasable, $product) {
                return false;
            }, 10, 2);
        }

        // Optionally, hide the add to cart form
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    } else {
        global $post;
        // if post content is not contains plugincy_one_page_checkout shortcode
        if (strpos($post->post_content, '[plugincy_one_page_checkout') === false) {
            add_action('wp_head', 'rmenu_checkout_popup');
        }
    }
}
add_action('wp', 'display_checkout_on_single_product', 10);

/**
 * Display the checkout form
 */
function display_one_page_checkout_form()
{
?>
    <style>
        .checkout-button-drawer {
            display: none;
        }

        a.checkout-button-drawer-link {
            display: flex !important;
        }
    </style>
    <div class="one-page-checkout-container" id="checkout-popup">
        <h2>Checkout</h2>
        <p class="one-page-checkout-description">Complete your purchase using the form below.</p>
        <?php echo do_shortcode('[woocommerce_checkout]'); ?>
    </div>
    <?php
}

/**
 * Replace the default quantity display with quantity controls in checkout
 */
function custom_quantity_input_on_checkout($html, $cart_item, $cart_item_key)
{

    // Get current quantity
    $quantity = $cart_item['quantity'];
    $new_html = '<strong class="product-quantity">× ' . esc_attr($quantity) . '</strong>';
    if (get_option("rmenu_quantity_control", "1") === "1") {
        // Build custom quantity input
        $new_html = '<div class="checkout-quantity-control">';
        $new_html .= '<button type="button" class="checkout-qty-btn checkout-qty-minus" data-cart-item="' . esc_attr($cart_item_key) . '">-</button>';
        $new_html .= '<input type="number" name="cart[' . esc_attr($cart_item_key) . '][qty]" class="checkout-qty-input" value="' . esc_attr($quantity) . '" min="1" step="1" size="4">';
        $new_html .= '<button type="button" class="checkout-qty-btn checkout-qty-plus" data-cart-item="' . esc_attr($cart_item_key) . '">+</button>';
        $new_html .= '</div>';
    }
    if (get_option("rmenu_remove_product", "1") === "1") {
        // remove button
        $remove_button = ' <a class="remove-item-checkout" data-cart-item="' . esc_attr($cart_item_key) . '" aria-label="' . esc_attr__('Remove this item', 'woocommerce') . '"><svg style="width: 12px; fill: #ff0000;" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M135.2 17.69C140.6 6.848 151.7 0 163.8 0H284.2C296.3 0 307.4 6.848 312.8 17.69L320 32H416C433.7 32 448 46.33 448 64C448 81.67 433.7 96 416 96H32C14.33 96 0 81.67 0 64C0 46.33 14.33 32 32 32H128L135.2 17.69zM31.1 128H416V448C416 483.3 387.3 512 352 512H95.1C60.65 512 31.1 483.3 31.1 448V128zM111.1 208V432C111.1 440.8 119.2 448 127.1 448C136.8 448 143.1 440.8 143.1 432V208C143.1 199.2 136.8 192 127.1 192C119.2 192 111.1 199.2 111.1 208zM207.1 208V432C207.1 440.8 215.2 448 223.1 448C232.8 448 240 440.8 240 432V208C240 199.2 232.8 192 223.1 192C215.2 192 207.1 199.2 207.1 208zM304 208V432C304 440.8 311.2 448 320 448C328.8 448 336 440.8 336 432V208C336 199.2 328.8 192 320 192C311.2 192 304 199.2 304 208z"></path></svg></a>';
        $new_html .= $remove_button;
    }
    return $new_html;
}
add_filter('woocommerce_checkout_cart_item_quantity', 'custom_quantity_input_on_checkout', 10, 3);


/**
 * Force checkout mode across all pages
 * 
 * Forces WooCommerce to treat all pages as checkout pages
 * Useful for custom checkout implementations
 * 
 * @param bool $is_checkout Original checkout status
 * @return bool Always returns true
 */
add_filter('woocommerce_is_checkout', 'force_woocommerce_checkout_mode', 999);

function force_woocommerce_checkout_mode($is_checkout)
{
    return true;
}

// Add AJAX handler for refreshing product list
add_action('wp_ajax_refresh_checkout_product_list', 'refresh_checkout_product_list');
add_action('wp_ajax_nopriv_refresh_checkout_product_list', 'refresh_checkout_product_list');

function refresh_checkout_product_list()
{
    if (!isset($_POST['product_ids'])) {
        wp_die();
    }

    $product_ids = explode(',', sanitize_text_field($_POST['product_ids']));
    $product_ids = array_map('trim', $product_ids);

    ob_start();

    // Loop through each product ID
    foreach ($product_ids as $item_id) {
        $product_id = intval($item_id);
        $product = wc_get_product($product_id);

        if ($product) {
            $product_name = $product->get_name();
            $product_image = $product->get_image(array(60, 60), array('class' => 'one-page-checkout-product-image'));

            // Check if product is in cart
            $in_cart = false;
            $cart_item_key = '';

            foreach (WC()->cart->get_cart() as $key => $cart_item) {
                if ($cart_item['product_id'] == $product_id) {
                    $in_cart = true;
                    $cart_item_key = $key;
                    break;
                }
            }

            $checked = $in_cart ? 'checked' : '';
    ?>
            <li class="one-page-checkout-product-item" data-product-id="<?php echo esc_attr($product_id); ?>" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                <div class="one-page-checkout-product-container">
                    <label class="one-page-checkout-product-label">
                        <input type="checkbox" class="one-page-checkout-product-checkbox" value="<?php echo esc_attr($product_id); ?>" <?php echo $checked; ?>>
                        <span class="one-page-checkout-product-image-wrap"><?php echo wp_kses_post($product_image); ?></span>
                        <span class="one-page-checkout-product-name"><?php echo esc_html($product_name); ?></span>
                        <span class="one-page-checkout-product-price"><?php echo wp_kses_post($product->get_price_html()); ?></span>
                    </label>
                </div>
            </li>
    <?php
        }
    }

    $html = ob_get_clean();
    echo $html;
    wp_die();
}


/**
 * Add product image to WooCommerce checkout page cart items
 */
function add_product_image_to_checkout_cart_items($product_name, $cart_item, $cart_item_key)
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
add_filter('woocommerce_cart_item_name', 'add_product_image_to_checkout_cart_items', 10, 3);

/**
 * Add some basic CSS to style the checkout cart items
 */
function checkout_product_image_css()
{
    ?>
    <style>
        .checkout-product-item {
            display: flex;
            align-items: center;
        }

        .checkout-product-image {
            margin-right: 10px;
            min-width: 50px;
        }

        .checkout-product-name {
            flex: 1;
        }
    </style>
<?php
}
add_action('wp_head', 'checkout_product_image_css');
