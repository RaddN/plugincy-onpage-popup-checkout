<?php
/*
/**
 * Plugin Name: One Page Quick Checkout for WooCommerce
 * Description: Shows a popup checkout form on button click.
 * Version: 1.0
 * Author: plugincy
 * Author URI: https://plugincy.com/
 * license: GPL2
 * Text Domain: one-page-quick-checkout-for-wooCommerce
 */


if (! defined('ABSPATH')) exit; // Exit if accessed directly


// Include the admin notice file
require_once plugin_dir_path(__FILE__) . 'includes/admin-notice.php';

// admin menu
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';

// include one page checkout shortcode
require_once plugin_dir_path(__FILE__) . 'includes/one-page-checkout-shortcode.php';

global $plugincyopc_checkoutformfields, $plugincyopc_productpageformfields, $plugincyopc_rcheckoutformfields;

$plugincyopc_checkoutformfields = [
    "your_cart" => "Your Cart",
    "btn_remove" => "Remove Button",
    "txt_subtotal" => "Subtotal",
    "txt_checkout" => "Place order",
    "txt_billing_details" => "Billing details",
    "txt_email_address" => "Email address",
    "txt_first_name" => "First name",
    "txt_last_name" => "Last name",
    "txt_country" => "Country / Region",
    "txt_street" => "Street address",
    "txt_city" => "Town / City",
    "txt_district" => "District",
    "txt_postcode" => 'Postcode / ZIP',
    "txt_phone_number" => "Phone",
    "txt_notes" => "Order notes",
    "txt_product" => "Product",
    "txt_shipping" => "Shipping",
    "txt_shipping_address_head" => "Ship to a different address?",
    "txt_total" => "Total",
    "btn_place_order" => "Place Order Button",
    "txt-woocommerce-privacy-policy-text" => "woocommerce privacy policy text",
    "txt-have_coupon" => "Have a coupon?",
    "txt-apply_coupon_below" => "If you have a coupon code, please apply it below.",
    "txt-complete_your_purchase" => "Complete your purchase using the form below.",
];

// archive & single product page text

$plugincyopc_productpageformfields = [
    "txt-add-to-cart" => "Add to cart",
    "txt-direct-checkout" => "Direct Checkout"
];

$plugincyopc_rcheckoutformfields = [
    'first_name' => ['title' => 'First Name', 'selector' => '#billing_first_name_field, #shipping_first_name_field'],
    'last_name'  => ['title' => 'Last Name', 'selector' => '#billing_last_name_field, #shipping_last_name_field'],
    'country'      => ['title' => 'Country', 'selector' => '#billing_country_field, #shipping_country_field'],
    'state'      => ['title' => 'State / District', 'selector' => '#billing_state_field, #shipping_state_field'],
    'city'       => ['title' => 'City', 'selector' => '#billing_city_field, #shipping_city_field'],
    'postcode'   => ['title' => 'Postcode', 'selector' => '#billing_postcode_field, #shipping_postcode_field'],
    'address_1'  => ['title' => 'Address 1', 'selector' => '#billing_address_1_field, #shipping_address_1_field'],
    'address_2'  => ['title' => 'Address 2', 'selector' => '#billing_address_2_field, #shipping_address_2_field'],
    'phone'      => ['title' => 'Phone', 'selector' => '#billing_phone_field'],
    'email'      => ['title' => 'Email', 'selector' => '#billing_email_field'],
    'company'    => ['title' => 'Company', 'selector' => '#billing_company_field'],
    'notes'     => ['title' => 'Notes', 'selector' => '#order_comments_field'],
];


// Enqueue scripts and styles
function plugincyopc_cart_enqueue_scripts()
{
    wp_enqueue_style('rmenu-cart-style', plugin_dir_url(__FILE__) . 'assets/css/rmenu-cart.css', array(), "1.0.0");
    wp_enqueue_script('rmenu-cart-script', plugin_dir_url(__FILE__) . 'assets/js/rmenu-cart.js', array('jquery'), "1.0.0", true);
    wp_enqueue_script('cart-script', plugin_dir_url(__FILE__) . 'assets/js/cart.js', array('jquery'), "1.0.0", true);
    // Localize script for AJAX URL and WooCommerce cart variables
    wp_localize_script('cart-script', 'wc_cart_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'get_cart_content_none' => wp_create_nonce('get_cart_content_none'),
        'update_cart_item_quantity' => wp_create_nonce('update_cart_item_quantity'),
        'remove_cart_item' => wp_create_nonce('remove_cart_item'),
        'plugincyopc_refresh_checkout_product_list' => wp_create_nonce('plugincyopc_refresh_checkout_product_list'),
    ));
    // Retrieve the rmsg_editor value
    $rmsg_editor_value = get_option('rmsg_editor', '');

    // Localize the script with the rmsg_editor value
    wp_localize_script('rmenu-cart-script', 'rmsgValue', array(
        'rmsgEditor' => $rmsg_editor_value,
    ));
    wp_localize_script('rmenu-cart-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'plugincyopc_cart_enqueue_scripts', 20);

add_action('admin_enqueue_scripts', 'plugincyopc_cart_admin_styles');

// Enqueue the admin stylesheet only for this settings page
function plugincyopc_cart_admin_styles($hook)
{
    if ($hook === 'toplevel_page_plugincyopc_cart') {
        wp_enqueue_style('plugincyopc_cart_admin_css', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css', array(), "1.0.0");
        wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], '1.0.5');
        wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '1.0.5', true);
    }
    wp_enqueue_style('plugincyopc_cart_admin_css', plugin_dir_url(__FILE__) . 'assets/css/admin-documentation.css', array(), "1.0.0");
    wp_enqueue_script('rmenu-admin-script', plugin_dir_url(__FILE__) . 'assets/js/admin-documentation.js', array('jquery'), "1.0.0", true);
}

// add shortcode
// if (get_option('plugincyopc_api_key') && get_option('plugincyopc_validity_days')!=="0"){
require_once plugin_dir_path(__FILE__) . 'includes/rmenu-shortcode.php';

// update cart content

add_action('wp_ajax_plugincyopc_get_cart_content', 'plugincyopc_get_cart_content');
add_action('wp_ajax_nopriv_plugincyopc_get_cart_content', 'plugincyopc_get_cart_content');
function plugincyopc_get_cart_content()
{
    check_ajax_referer('get_cart_content_none', 'nonce');
    //get the values from the ajax request cart_icon: cartIcon, product_title_tag: productTitleTag, drawer_position: drawerPosition
    $cartIcon = isset($_POST['cart_icon']) ? sanitize_text_field(wp_unslash($_POST['cart_icon'])) : 'cart';
    $productTitleTag = isset($_POST['product_title_tag']) ? sanitize_text_field(wp_unslash($_POST['product_title_tag'])) : 'h2';
    $drawerPosition = isset($_POST['drawer_position']) ? sanitize_text_field(wp_unslash($_POST['drawer_position'])) : 'right';
    ob_start();

    // Use include to load the template from your plugin's directory
    plugincyopc_cart($drawerPosition, $cartIcon, $productTitleTag);

    $cart_html = ob_get_clean();

    // Send response with cart HTML and count
    wp_send_json_success([
        'cart_html' => $cart_html,
        'cart_count' => WC()->cart->get_cart_contents_count()
    ]);
}

// update quantity

add_action('wp_ajax_plugincyopc_update_cart_item_quantity', 'plugincyopc_update_cart_item_quantity');
add_action('wp_ajax_nopriv_plugincyopc_update_cart_item_quantity', 'plugincyopc_update_cart_item_quantity');
function plugincyopc_update_cart_item_quantity()
{
    check_ajax_referer('update_cart_item_quantity', 'nonce');
    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field(wp_unslash($_POST['cart_item_key'])) : '';
    $quantity = isset($_POST['quantity']) ? (int)sanitize_text_field(wp_unslash($_POST['quantity'])) : 0;

    if (WC()->cart->set_quantity($cart_item_key, $quantity)) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Could not update quantity.');
    }
}


// remove cart item
add_action('wp_ajax_plugincyopc_remove_cart_item', 'plugincyopc_handle_remove_cart_item');
add_action('wp_ajax_nopriv_plugincyopc_remove_cart_item', 'plugincyopc_handle_remove_cart_item');
function plugincyopc_handle_remove_cart_item()
{
    check_ajax_referer('remove_cart_item', 'nonce');
    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field(wp_unslash($_POST['cart_item_key'])) : '';

    if (WC()->cart->remove_cart_item($cart_item_key)) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Could not remove item.');
    }
}

// update checkout form on ajax complete
function plugincyopc_update_checkout_form()
{
    ob_start();

    // Use include to load the template from your plugin's directory
    plugincyopc_rmenu_checkout();

    $checkout_form = ob_get_clean();

    // Send response with cart HTML and count
    wp_send_json_success(array('checkout_form' => $checkout_form));
}

add_action('wp_ajax_plugincyopc_update_checkout', 'plugincyopc_update_checkout_form');
add_action('wp_ajax_nopriv_plugincyopc_update_checkout', 'plugincyopc_update_checkout_form');


// Customize WooCommerce checkout text labels
function plugincyopc_custom_woocommerce_checkout_text($translated_text, $text, $domain)
{
    global $plugincyopc_checkoutformfields, $plugincyopc_productpageformfields;
    // convert $plugincyopc_checkoutformfields to array $mapping
    $mapping = array_merge(array_flip($plugincyopc_checkoutformfields), array_flip($plugincyopc_productpageformfields));


    if ($domain === 'woocommerce' && array_key_exists($text, $mapping)) {
        $option_key = $mapping[$text];
        $translated_text = get_option($option_key) ? esc_attr(get_option($option_key)) : $plugincyopc_checkoutformfields[$option_key] ?? $plugincyopc_productpageformfields[$option_key];
    }

    return $translated_text;
}
add_filter('gettext', 'plugincyopc_custom_woocommerce_checkout_text', 20, 3);


// Change "Shipping" label in WooCommerce shipping totals section
function plugincyopc_custom_woocommerce_shipping_label($label, $package_name)
{
    return get_option("txt_shipping") ? esc_attr(get_option("txt_shipping", 'Shipping')) : "Shipping"; // Change "Shipping" to "Delivery Charges"
}
add_filter('woocommerce_shipping_package_name', 'plugincyopc_custom_woocommerce_shipping_label', 10, 2);



// }else{
//     require_once plugin_dir_path(__FILE__) . 'includes/without_api_short_code';
// }

function plugincyopc_editor_script()
{
    wp_enqueue_script(
        'plugincy-custom-editor',
        plugin_dir_url(__FILE__) . 'includes/blocks/editor.js',
        array('wp-blocks', 'wp-element', 'wp-edit-post', 'wp-dom-ready', 'wp-plugins'),
        '1.0',
        true
    );
}
add_action('enqueue_block_editor_assets', 'plugincyopc_editor_script');


require_once plugin_dir_path(__FILE__) . 'includes/cart-template.php';
require_once plugin_dir_path(__FILE__) . 'includes/popup-template.php';
require_once plugin_dir_path(__FILE__) . 'includes/documentation.php';
require_once plugin_dir_path(__FILE__) . 'includes/blocks/plugincy-cart-blocks.php';
require_once plugin_dir_path(__FILE__) . 'includes/blocks/one-page-checkout.php';

// checkout popup form

function plugincyopc_rmenu_checkout_popup($isonepagewidget = false)
{
?>
    <div class="checkout-popup <?php echo $isonepagewidget ? 'onepagecheckoutwidget' : ''; ?>" data-isonepagewidget="<?php echo esc_attr($isonepagewidget); ?>" style="<?php echo $isonepagewidget ? 'display: block; position: unset; transform: unset; box-shadow: none; background: unset; width: 100%; max-width: 100%; height: 100%;overflow: hidden;' : 'display:none'; ?>;">
        <?php
        plugincyopc_rmenu_checkout($isonepagewidget);
        ?>
    </div>
<?php
}

/**
 * One Page Quick Checkout for WooCommerce
 * 
 * Adds a checkbox to product settings and displays checkout form directly on product page
 * when enabled, creating a streamlined purchasing experience.
 */

/**
 * Add One Page Checkout checkbox to product type options
 */
function plugincyopc_add_one_page_checkout_to_product_type_options($product_type_options)
{
    $product_type_options['one_page_checkout'] = array(
        'id'            => '_one_page_checkout',
        'wrapper_class' => '',
        'label'         => __('One Page Checkout', 'one-page-quick-checkout-for-wooCommerce'),
        'description'   => __('Enable one page checkout for this product', 'one-page-quick-checkout-for-wooCommerce'),
        'default'       => 'no'
    );

    
    wp_nonce_field('plugincyopc_save_meta', 'plugincyopc_nonce');

    return $product_type_options;
}
add_filter('product_type_options', 'plugincyopc_add_one_page_checkout_to_product_type_options');

/**
 * Save One Page Checkout option
 */
function plugincyopc_save_one_page_checkout_option($post_id)
{
    // Check if our nonce is set and valid
    check_ajax_referer('plugincyopc_save_meta', 'plugincyopc_nonce');

    $is_one_page_checkout = isset($_POST['_one_page_checkout']) ? 'yes' : 'no';
    update_post_meta($post_id, '_one_page_checkout', $is_one_page_checkout);
}
add_action('woocommerce_process_product_meta', 'plugincyopc_save_one_page_checkout_option', 10);


/**
 * Display checkout form on single product pages when One Page Checkout is enabled
 */
function plugincyopc_display_checkout_on_single_product()
{
    // Only run on single product pages
    if (!is_product()) {
        global $post;
        // if post content is not contains plugincyopc_one_page_checkout shortcode
        if (strpos($post->post_content, 'plugincy_one_page_checkout') === false) {
            add_action('wp_head', 'plugincyopc_rmenu_checkout_popup');
        }
        return;
    }

    // Get product ID and ensure we have a valid product object
    $product_id = get_the_ID();
    $product = wc_get_product($product_id);

    if (!$product || !is_a($product, 'WC_Product')) {
        global $post;
        // if post content is not contains plugincyopc_one_page_checkout shortcode
        if (strpos($post->post_content, 'plugincy_one_page_checkout') === false) {
            add_action('wp_head', 'plugincyopc_rmenu_checkout_popup');
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
        add_action('wp_enqueue_scripts', 'plugincyopc_add_checkout_inline_styles', 99);
        // Add checkout form before product tabs
        add_action('woocommerce_after_single_product_summary', 'plugincyopc_display_one_page_checkout_form',  get_option("onpage_checkout_position", '9'));

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
        // if post content is not contains plugincyopc_one_page_checkout shortcode
        if (strpos($post->post_content, 'plugincy_one_page_checkout') === false) {
            add_action('wp_head', 'plugincyopc_rmenu_checkout_popup');
        }
    }
}
add_action('wp', 'plugincyopc_display_checkout_on_single_product', 99);

/**
 * Display the checkout form
 */
function plugincyopc_display_one_page_checkout_form()
{

?>
    <div class="one-page-checkout-container" id="checkout-popup">
        <h2>Checkout</h2>
        <p class="one-page-checkout-description"><?php echo get_option('txt-complete_your_purchase') ? esc_attr(get_option('txt-complete_your_purchase')) : 'Complete your purchase using the form below.'; ?></p>
        <?php echo do_shortcode('[woocommerce_checkout]'); ?>
    </div>
    <?php
}

function plugincyopc_add_checkout_inline_styles()
{
    // Make sure style is enqueued before adding inline styles
    if (wp_style_is('rmenu-cart-style', 'enqueued')) {
        wp_add_inline_style('rmenu-cart-style', '.checkout-button-drawer {display: none !important; } a.checkout-button-drawer-link { display: flex !important; }');
    }
}

// if current page contains the shortcode plugincy_one_page_checkout
function check_shortcode_and_enqueue_styles()
{
    if (is_page() && has_shortcode(get_post()->post_content, 'plugincy_one_page_checkout')) {
        add_action('wp_enqueue_scripts', 'plugincyopc_add_checkout_inline_styles', 99);
    }
}
add_action('wp', 'check_shortcode_and_enqueue_styles', 99);

/**
 * Replace the default quantity display with quantity controls in checkout
 */
function plugincyopc_custom_quantity_input_on_checkout($html, $cart_item, $cart_item_key)
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
        $remove_button = ' <a class="remove-item-checkout" data-cart-item="' . esc_attr($cart_item_key) . '" aria-label="' . esc_attr__('Remove this item', 'one-page-quick-checkout-for-wooCommerce') . '"><svg style="width: 12px; fill: #ff0000;" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M135.2 17.69C140.6 6.848 151.7 0 163.8 0H284.2C296.3 0 307.4 6.848 312.8 17.69L320 32H416C433.7 32 448 46.33 448 64C448 81.67 433.7 96 416 96H32C14.33 96 0 81.67 0 64C0 46.33 14.33 32 32 32H128L135.2 17.69zM31.1 128H416V448C416 483.3 387.3 512 352 512H95.1C60.65 512 31.1 483.3 31.1 448V128zM111.1 208V432C111.1 440.8 119.2 448 127.1 448C136.8 448 143.1 440.8 143.1 432V208C143.1 199.2 136.8 192 127.1 192C119.2 192 111.1 199.2 111.1 208zM207.1 208V432C207.1 440.8 215.2 448 223.1 448C232.8 448 240 440.8 240 432V208C240 199.2 232.8 192 223.1 192C215.2 192 207.1 199.2 207.1 208zM304 208V432C304 440.8 311.2 448 320 448C328.8 448 336 440.8 336 432V208C336 199.2 328.8 192 320 192C311.2 192 304 199.2 304 208z"></path></svg></a>';
        $new_html .= $remove_button;
    }
    return $new_html;
}
add_filter('woocommerce_checkout_cart_item_quantity', 'plugincyopc_custom_quantity_input_on_checkout', 10, 3);


/**
 * Force checkout mode across all pages
 * 
 * Forces WooCommerce to treat all pages as checkout pages
 * Useful for custom checkout implementations
 * 
 * @param bool $is_checkout Original checkout status
 * @return bool Always returns true
 */
add_filter('woocommerce_is_checkout', 'plugincyopc_force_woocommerce_checkout_mode', 999);

function plugincyopc_force_woocommerce_checkout_mode($is_checkout)
{
    return true;
}

// Add AJAX handler for refreshing product list
add_action('wp_ajax_plugincyopc_refresh_checkout_product_list', 'plugincyopc_refresh_checkout_product_list');
add_action('wp_ajax_nopriv_plugincyopc_refresh_checkout_product_list', 'plugincyopc_refresh_checkout_product_list');

function plugincyopc_refresh_checkout_product_list()
{
    // Check nonce for security
    check_ajax_referer('plugincyopc_refresh_checkout_product_list', 'nonce');
    if (!isset($_POST['product_ids'])) {
        wp_die();
    }

    $product_ids = explode(',', sanitize_text_field(wp_unslash($_POST['product_ids'])));
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
                        <input type="checkbox" class="one-page-checkout-product-checkbox" value="<?php echo esc_attr($product_id); ?>" <?php echo esc_attr($checked); ?>>
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
    $allowed_html = array(
        'li' => array(
            'class' => array(),
            'data-product-id' => array(),
            'data-cart-item-key' => array(),
        ),
        'div' => array(
            'class' => array(),
        ),
        'label' => array(
            'class' => array(),
        ),
        'input' => array(
            'type' => array(),
            'class' => array(),
            'value' => array(),
            'checked' => array(),
        ),
        'span' => array(
            'class' => array(),
        ),
        'img' => array(
            'src' => array(),
            'alt' => array(),
            'class' => array(),
            'width' => array(),
            'height' => array(),
            'srcset' => array(),
            'sizes' => array(),
            'loading' => array(),
        ),
    );

    echo wp_kses($html, $allowed_html);
    wp_die();
}


/**
 * Add product image to WooCommerce checkout page cart items
 */
function plugincyopc_add_product_image_to_checkout_cart_items($product_name, $cart_item, $cart_item_key)
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
add_filter('woocommerce_cart_item_name', 'plugincyopc_add_product_image_to_checkout_cart_items', 10, 3);


function plugincyopc_add_checkout_button_after_add_to_cart()
{
    global $product;
    $product_id = $product->get_id();
    $product_type = $product->get_type();


    // For single product pages
    if (get_option("rmenu_add_checkout_button", "1") === "1") {
        echo '<a href="#checkout-popup" class="button single_add_to_cart_button direct-checkout-button button button-secondary" data-product-id="' . esc_attr($product_id) . '" data-product-type="' . esc_attr($product_type) . '" style="cursor:pointer;">' . esc_html(get_option('txt-direct-checkout', 'Direct Checkout')) . '</a>';
    }
}
add_action('woocommerce_after_add_to_cart_button', 'plugincyopc_add_checkout_button_after_add_to_cart');

function plugincyopc_add_checkout_button_to_add_to_cart_shortcode($link, $product)
{
    if (get_option("rmenu_add_checkout_button", "1") === "1") {
        $product_id = $product->get_id();
        $product_type = $product->get_type();
        $checkout_button = '<a class="button direct-checkout-button button button-secondary" data-product-id="' . esc_attr($product_id) . '" data-product-type="' . esc_attr($product_type) . '" style="cursor:pointer;">' . esc_html(get_option('txt-direct-checkout', 'Direct Checkout')) . '</a>';
        return $link . ' ' . $checkout_button;
    }
    return $link;
}
add_filter('woocommerce_loop_add_to_cart_link', 'plugincyopc_add_checkout_button_to_add_to_cart_shortcode', 10, 2);


add_action('template_redirect', 'plugincyopc_add_random_product_if_cart_empty');

function plugincyopc_add_random_product_if_cart_empty()
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
