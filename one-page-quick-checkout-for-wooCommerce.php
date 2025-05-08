<?php
/*
/**
 * Plugin Name: One Page Quick Checkout for WooCommerce
 * Plugin URI:  https://plugincy.com/one-page-quick-checkout-for-woocommerce/
 * Description: Enhance WooCommerce with popup checkout, cart drawer, and flexible checkout templates to boost conversions.
 * Version: 1.0.0
 * Author: plugincy
 * Author URI: https://plugincy.com
 * license: GPL2
 * Text Domain: one-page-quick-checkout-for-woocommerce
 * Requires Plugins: woocommerce
 */


if (! defined('ABSPATH')) exit; // Exit if accessed directly


// Include the admin notice file
require_once plugin_dir_path(__FILE__) . 'includes/admin-notice.php';

// admin menu
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';

// include one page checkout shortcode
require_once plugin_dir_path(__FILE__) . 'includes/one-page-checkout-shortcode.php';

global $onepaquc_checkoutformfields, $onepaquc_productpageformfields, $onepaquc_rcheckoutformfields;

$onepaquc_checkoutformfields = [
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

$onepaquc_productpageformfields = [
    "txt-add-to-cart" => "Add to cart",
    "txt-select-options" => "Select Options (Coming Soon)",
    "txt-read-more" => "Read More (Coming Soon)",
];

$onepaquc_rcheckoutformfields = [
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
function onepaquc_cart_enqueue_scripts()
{
    wp_enqueue_style('rmenu-cart-style', plugin_dir_url(__FILE__) . 'assets/css/rmenu-cart.css', array(), "1.0.0");
    wp_enqueue_script('rmenu-cart-script', plugin_dir_url(__FILE__) . 'assets/js/rmenu-cart.js', array('jquery'), "1.0.0", true);
    wp_enqueue_script('cart-script', plugin_dir_url(__FILE__) . 'assets/js/cart.js', array('jquery'), "1.0.0", true);
    $direct_checkout_behave = [
        'rmenu_wc_checkout_method' => get_option('rmenu_wc_checkout_method', 'popup_checkout'),
        'rmenu_wc_clear_cart' => get_option('rmenu_wc_clear_cart', 0),
        'rmenu_wc_one_click_purchase' => get_option('rmenu_wc_one_click_purchase', 0),
        'rmenu_wc_add_confirmation' => get_option('rmenu_wc_add_confirmation', 0),
    ];
    // Localize script for AJAX URL and WooCommerce cart variables
    wp_localize_script('cart-script', 'onepaquc_wc_cart_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'get_cart_content_none' => wp_create_nonce('get_cart_content_none'),
        'update_cart_item_quantity' => wp_create_nonce('update_cart_item_quantity'),
        'remove_cart_item' => wp_create_nonce('remove_cart_item'),
        'onepaquc_refresh_checkout_product_list' => wp_create_nonce('onepaquc_refresh_checkout_product_list'),
        'direct_checkout_behave' => $direct_checkout_behave,
        'checkout_url' => wc_get_checkout_url(),
        'cart_url'     => wc_get_cart_url(), 
    ));
    // Retrieve the rmsg_editor value
    $rmsg_editor_value = get_option('rmsg_editor', '');

    // Localize the script with the rmsg_editor value
    wp_localize_script('rmenu-cart-script', 'onepaquc_rmsgValue', array(
        'rmsgEditor' => $rmsg_editor_value,
    ));
    wp_localize_script('rmenu-cart-script', 'onepaquc_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'onepaquc_cart_enqueue_scripts', 20);

add_action('admin_enqueue_scripts', 'onepaquc_cart_admin_styles');

// Enqueue the admin stylesheet only for this settings page
function onepaquc_cart_admin_styles($hook)
{
    if ($hook === 'toplevel_page_onepaquc_cart') {
        wp_enqueue_style('onepaquc_cart_admin_css', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css', array(), "1.0.0");
        wp_enqueue_style('select2-css', plugin_dir_url(__FILE__) . 'assets/css/select2.min.css', array(), "1.0.0");
        wp_enqueue_script('select2-js', plugin_dir_url(__FILE__) . 'assets/js/select2.min.js', array('jquery'), "1.0.0", true);
    }
    wp_enqueue_style('onepaquc_cart_admin_css', plugin_dir_url(__FILE__) . 'assets/css/admin-documentation.css', array(), "1.0.0");
    wp_enqueue_script('rmenu-admin-script', plugin_dir_url(__FILE__) . 'assets/js/admin-documentation.js', array('jquery'), "1.0.0", true);
}

// add shortcode
// if (get_option('onepaquc_api_key') && get_option('onepaquc_validity_days')!=="0"){
require_once plugin_dir_path(__FILE__) . 'includes/rmenu-shortcode.php';

// update cart content

add_action('wp_ajax_onepaquc_get_cart_content', 'onepaquc_get_cart_content');
add_action('wp_ajax_nopriv_onepaquc_get_cart_content', 'onepaquc_get_cart_content');
function onepaquc_get_cart_content()
{
    check_ajax_referer('get_cart_content_none', 'nonce');
    //get the values from the ajax request cart_icon: cartIcon, product_title_tag: productTitleTag, drawer_position: drawerPosition
    $cartIcon = isset($_POST['cart_icon']) ? sanitize_text_field(wp_unslash($_POST['cart_icon'])) : 'cart';
    $productTitleTag = isset($_POST['product_title_tag']) ? sanitize_text_field(wp_unslash($_POST['product_title_tag'])) : 'h2';
    $drawerPosition = isset($_POST['drawer_position']) ? sanitize_text_field(wp_unslash($_POST['drawer_position'])) : 'right';
    ob_start();

    // Use include to load the template from your plugin's directory
    onepaquc_cart($drawerPosition, $cartIcon, $productTitleTag);

    $cart_html = ob_get_clean();

    // Send response with cart HTML and count
    wp_send_json_success([
        'cart_html' => $cart_html,
        'cart_count' => WC()->cart->get_cart_contents_count()
    ]);
}

// update quantity

add_action('wp_ajax_onepaquc_update_cart_item_quantity', 'onepaquc_update_cart_item_quantity');
add_action('wp_ajax_nopriv_onepaquc_update_cart_item_quantity', 'onepaquc_update_cart_item_quantity');
function onepaquc_update_cart_item_quantity()
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
add_action('wp_ajax_onepaquc_remove_cart_item', 'onepaquc_handle_remove_cart_item');
add_action('wp_ajax_nopriv_onepaquc_remove_cart_item', 'onepaquc_handle_remove_cart_item');
function onepaquc_handle_remove_cart_item()
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
function onepaquc_update_checkout_form()
{
    ob_start();

    // Use include to load the template from your plugin's directory
    onepaquc_rmenu_checkout();

    $checkout_form = ob_get_clean();

    // Send response with cart HTML and count
    wp_send_json_success(array('checkout_form' => $checkout_form));
}

add_action('wp_ajax_onepaquc_update_checkout', 'onepaquc_update_checkout_form');
add_action('wp_ajax_nopriv_onepaquc_update_checkout', 'onepaquc_update_checkout_form');


// Customize WooCommerce checkout text labels
function onepaquc_custom_woocommerce_checkout_text($translated_text, $text, $domain)
{
    global $onepaquc_checkoutformfields, $onepaquc_productpageformfields;
    // convert $onepaquc_checkoutformfields to array $mapping
    $mapping = array_merge(array_flip($onepaquc_checkoutformfields), array_flip($onepaquc_productpageformfields));


    if ($domain === 'woocommerce' && array_key_exists($text, $mapping)) {
        $option_key = $mapping[$text];
        $translated_text = get_option($option_key) ? esc_attr(get_option($option_key)) : $onepaquc_checkoutformfields[$option_key] ?? $onepaquc_productpageformfields[$option_key];
    }

    return $translated_text;
}
add_filter('gettext', 'onepaquc_custom_woocommerce_checkout_text', 20, 3);


// Change "Shipping" label in WooCommerce shipping totals section
function onepaquc_custom_woocommerce_shipping_label($label, $package_name)
{
    return get_option("txt_shipping") ? esc_attr(get_option("txt_shipping", 'Shipping')) : "Shipping"; // Change "Shipping" to "Delivery Charges"
}
add_filter('woocommerce_shipping_package_name', 'onepaquc_custom_woocommerce_shipping_label', 10, 2);



// }else{
//     require_once plugin_dir_path(__FILE__) . 'includes/without_api_short_code';
// }

function onepaquc_editor_script()
{
    wp_enqueue_script(
        'plugincy-custom-editor',
        plugin_dir_url(__FILE__) . 'includes/blocks/editor.js',
        array('wp-blocks', 'wp-element', 'wp-edit-post', 'wp-dom-ready', 'wp-plugins'),
        '1.0.0',
        true
    );
}
add_action('enqueue_block_editor_assets', 'onepaquc_editor_script');


require_once plugin_dir_path(__FILE__) . 'includes/cart-template.php';
require_once plugin_dir_path(__FILE__) . 'includes/popup-template.php';
require_once plugin_dir_path(__FILE__) . 'includes/documentation.php';
require_once plugin_dir_path(__FILE__) . 'includes/blocks/plugincy-cart-blocks.php';
require_once plugin_dir_path(__FILE__) . 'includes/blocks/one-page-checkout.php';

// checkout popup form

function onepaquc_rmenu_checkout_popup($isonepagewidget = false)
{
?>
    <div class="checkout-popup <?php echo $isonepagewidget ? 'onepagecheckoutwidget' : ''; ?>" data-isonepagewidget="<?php echo esc_attr($isonepagewidget); ?>" style="<?php echo $isonepagewidget ? 'display: block; position: unset; transform: unset; box-shadow: none; background: unset; width: 100%; max-width: 100%; height: 100%;overflow: hidden;' : 'display:none'; ?>;">
        <?php
        onepaquc_rmenu_checkout($isonepagewidget);
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
function onepaquc_add_one_page_checkout_to_product_type_options($product_type_options)
{
    $product_type_options['one_page_checkout'] = array(
        'id'            => '_one_page_checkout',
        'wrapper_class' => '',
        'label'         => __('One Page Checkout', 'one-page-quick-checkout-for-woocommerce'),
        'description'   => __('Enable one page checkout for this product', 'one-page-quick-checkout-for-woocommerce'),
        'default'       => 'no'
    );


    wp_nonce_field('onepaquc_save_meta', 'onepaquc_nonce');

    return $product_type_options;
}
add_filter('product_type_options', 'onepaquc_add_one_page_checkout_to_product_type_options');

/**
 * Save One Page Checkout option
 */
function onepaquc_save_one_page_checkout_option($post_id)
{
    // Check if our nonce is set and valid
    check_ajax_referer('onepaquc_save_meta', 'onepaquc_nonce');

    $is_one_page_checkout = isset($_POST['_one_page_checkout']) ? 'yes' : 'no';
    update_post_meta($post_id, '_one_page_checkout', $is_one_page_checkout);
}
add_action('woocommerce_process_product_meta', 'onepaquc_save_one_page_checkout_option', 10);


/**
 * Display checkout form on single product pages when One Page Checkout is enabled
 */
function onepaquc_display_checkout_on_single_product()
{
    // Only run on single product pages
    if (!is_product()) {
        global $post;
        // if post content is not contains onepaquc_one_page_checkout shortcode
        if (strpos($post->post_content, 'plugincy_one_page_checkout') === false) {
            add_action('wp_head', 'onepaquc_rmenu_checkout_popup');
        }
        return;
    }

    // Get product ID and ensure we have a valid product object
    $product_id = get_the_ID();
    $product = wc_get_product($product_id);

    if (!$product || !is_a($product, 'WC_Product')) {
        global $post;
        // if post content is not contains onepaquc_one_page_checkout shortcode
        if (strpos($post->post_content, 'plugincy_one_page_checkout') === false) {
            add_action('wp_head', 'onepaquc_rmenu_checkout_popup');
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
        add_action('wp_enqueue_scripts', 'onepaquc_add_checkout_inline_styles', 99);
        // Add checkout form before product tabs
        add_action('woocommerce_after_single_product_summary', 'onepaquc_display_one_page_checkout_form',  get_option("onpage_checkout_position", '9'));

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
        // if post content is not contains onepaquc_one_page_checkout shortcode
        if (strpos($post->post_content, 'plugincy_one_page_checkout') === false) {
            add_action('wp_head', 'onepaquc_rmenu_checkout_popup');
        }
    }
}
add_action('wp', 'onepaquc_display_checkout_on_single_product', 99);

/**
 * Display the checkout form
 */
function onepaquc_display_one_page_checkout_form()
{

?>
    <div class="one-page-checkout-container" id="checkout-popup">
        <h2>Checkout</h2>
        <p class="one-page-checkout-description"><?php echo get_option('txt-complete_your_purchase') ? esc_attr(get_option('txt-complete_your_purchase')) : 'Complete your purchase using the form below.'; ?></p>
        <?php echo do_shortcode('[woocommerce_checkout]'); ?>
    </div>
    <?php
}

function onepaquc_add_checkout_inline_styles()
{
    // Make sure style is enqueued before adding inline styles
    if (wp_style_is('rmenu-cart-style', 'enqueued')) {
        wp_add_inline_style('rmenu-cart-style', '.checkout-button-drawer {display: none !important; } a.checkout-button-drawer-link { display: flex !important; }');
    }
}

// if current page contains the shortcode plugincy_one_page_checkout
function onepaquc_check_shortcode_and_enqueue_styles()
{
    if (is_page() && has_shortcode(get_post()->post_content, 'plugincy_one_page_checkout')) {
        add_action('wp_enqueue_scripts', 'onepaquc_add_checkout_inline_styles', 99);
    }
}
add_action('wp', 'onepaquc_check_shortcode_and_enqueue_styles', 99);

/**
 * Replace the default quantity display with quantity controls in checkout
 */
function onepaquc_custom_quantity_input_on_checkout($html, $cart_item, $cart_item_key)
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
        $remove_button = ' <a class="remove-item-checkout" data-cart-item="' . esc_attr($cart_item_key) . '" aria-label="' . esc_attr__('Remove this item', 'one-page-quick-checkout-for-woocommerce') . '"><svg style="width: 12px; fill: #ff0000;" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M135.2 17.69C140.6 6.848 151.7 0 163.8 0H284.2C296.3 0 307.4 6.848 312.8 17.69L320 32H416C433.7 32 448 46.33 448 64C448 81.67 433.7 96 416 96H32C14.33 96 0 81.67 0 64C0 46.33 14.33 32 32 32H128L135.2 17.69zM31.1 128H416V448C416 483.3 387.3 512 352 512H95.1C60.65 512 31.1 483.3 31.1 448V128zM111.1 208V432C111.1 440.8 119.2 448 127.1 448C136.8 448 143.1 440.8 143.1 432V208C143.1 199.2 136.8 192 127.1 192C119.2 192 111.1 199.2 111.1 208zM207.1 208V432C207.1 440.8 215.2 448 223.1 448C232.8 448 240 440.8 240 432V208C240 199.2 232.8 192 223.1 192C215.2 192 207.1 199.2 207.1 208zM304 208V432C304 440.8 311.2 448 320 448C328.8 448 336 440.8 336 432V208C336 199.2 328.8 192 320 192C311.2 192 304 199.2 304 208z"></path></svg></a>';
        $new_html .= $remove_button;
    }
    return $new_html;
}
add_filter('woocommerce_checkout_cart_item_quantity', 'onepaquc_custom_quantity_input_on_checkout', 10, 3);


/**
 * Force checkout mode across all pages
 * 
 * Forces WooCommerce to treat all pages as checkout pages
 * Useful for custom checkout implementations
 * 
 * @param bool $is_checkout Original checkout status
 * @return bool Always returns true
 */
add_filter('woocommerce_is_checkout', 'onepaquc_force_woocommerce_checkout_mode', 999);

function onepaquc_force_woocommerce_checkout_mode($is_checkout)
{
    return true;
}

// Add AJAX handler for refreshing product list
add_action('wp_ajax_onepaquc_refresh_checkout_product_list', 'onepaquc_refresh_checkout_product_list');
add_action('wp_ajax_nopriv_onepaquc_refresh_checkout_product_list', 'onepaquc_refresh_checkout_product_list');

function onepaquc_refresh_checkout_product_list()
{
    // Check nonce for security
    check_ajax_referer('onepaquc_refresh_checkout_product_list', 'nonce');
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


/**
 * Add Quick Checkout button on single product pages and product listings
 * with consistent positioning based on settings
 */

/**
 * Check if button should display based on product type, current page, and user login status
 *
 * @param WC_Product $product The product object
 * @return bool Whether the button should be displayed
 */
function onepaquc_should_display_button($product) {
    if (!$product || get_option("rmenu_add_checkout_button", "1") !== "1") {
        return false;
    }
    
    // Check user login status
    $guest_checkout_enabled = get_option('rmenu_wc_checkout_guest_enabled', '1');
    if ($guest_checkout_enabled !== '1' && !is_user_logged_in()) {
        return false; // Only show for logged-in users when guest checkout is disabled
    }

    // Check product type
    $allowed_product_types = get_option('rmenu_show_quick_checkout_by_types', ['simple']);
    $product_type = $product->get_type();
    
    if (!in_array($product_type, $allowed_product_types)) {
        return false;
    }
    
    // Get allowed pages
    $allowed_pages = get_option('rmenu_show_quick_checkout_by_page', ['single']);
    
    // Check current page type
    if (is_product() && in_array('single', $allowed_pages)) {
        return true;
    }
    
    if (is_shop() && in_array('shop-page', $allowed_pages)) {
        return true;
    }
    
    if (is_product_category() && in_array('category-archives', $allowed_pages)) {
        return true;
    }
    
    if (is_product_tag() && in_array('tag-archives', $allowed_pages)) {
        return true;
    }
    
    if (is_search() && in_array('search', $allowed_pages)) {
        return true;
    }
    
    // Check for related products
    global $woocommerce_loop;
    if (isset($woocommerce_loop['name'])) {
        if ($woocommerce_loop['name'] === 'related' && in_array('related', $allowed_pages)) {
            return true;
        }
        
        if ($woocommerce_loop['name'] === 'up-sells' && in_array('upsells', $allowed_pages)) {
            return true;
        }
        
        if ($woocommerce_loop['name'] === 'cross-sells' && in_array('cross-sells', $allowed_pages)) {
            return true;
        }
    }
    
    // Check for featured products
    if (isset($woocommerce_loop['featured']) && $woocommerce_loop['featured'] && in_array('featured-products', $allowed_pages)) {
        return true;
    }
    
    // Check for on-sale products
    if (isset($woocommerce_loop['on_sale']) && $woocommerce_loop['on_sale'] && in_array('on-sale', $allowed_pages)) {
        return true;
    }
    
    // Check for recent products
    if (isset($woocommerce_loop['recent']) && $woocommerce_loop['recent'] && in_array('recent', $allowed_pages)) {
        return true;
    }
    
    // Check for widgets and shortcodes
    $is_widget_or_shortcode = false;
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
    foreach ($backtrace as $trace) {
        if (isset($trace['function']) && (
            (strpos($trace['function'], 'widget') !== false && in_array('widgets', $allowed_pages)) ||
            (strpos($trace['function'], 'shortcode') !== false && in_array('shortcodes', $allowed_pages))
        )) {
            $is_widget_or_shortcode = true;
            break;
        }
    }
    
    return $is_widget_or_shortcode;
}

/**
 * Get the CSS classes and styling for the direct checkout button
 * 
 * @return array Array with 'classes', 'style', 'icon', and 'additional_css' for the button
 */
function onepaquc_get_button_styling() {
    // Basic button classes
    $classes = "button single_add_to_cart_button direct-checkout-button rmenu-direct-checkout-btn";
    $style = "cursor:pointer;";
    $icon = '';
    $additional_css = '';
    
    // Apply button style settings
    $button_style = get_option('rmenu_wc_checkout_style', 'default');
    if ($button_style === 'alt') {
        $classes .= " alt-style";
    }
    
    // Apply color settings if not using default style
    if ($button_style !== 'default') {
        $bg_color = get_option('rmenu_wc_checkout_color', '#96588a');
        $text_color = get_option('rmenu_wc_checkout_text_color', '#ffffff');
        $style .= "background-color:{$bg_color}!important;color:{$text_color}!important;border-color:{$bg_color}!important;";
    }

    // Add mobile optimization if enabled
    $mobile_optimize = get_option('rmenu_wc_checkout_mobile_optimize', '0');
    if ($mobile_optimize === '1') {
        $style .= "display:inline-block;";
        $classes .= " mobile-optimized-checkout";
    }
    
    // Handle button icon
    $icon_type = get_option('rmenu_wc_checkout_icon', 'none');
    $icon_position = get_option('rmenu_wc_checkout_icon_position', 'left');
    
    if ($icon_type !== 'none') {
        // Define icon HTML based on type
        switch ($icon_type) {
            case 'cart':
                $icon_content = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rmenu-icon"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>';
                break;
            case 'checkout':
                $icon_content = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rmenu-icon"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>';
                break;
            case 'arrow':
                $icon_content = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rmenu-icon"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';
                break;
            default:
                $icon_content = '';
        }
        
        if (!empty($icon_content)) {
            $icon = [
                'content' => $icon_content,
                'position' => $icon_position
            ];
            
            // Add class for icon positioning
            $classes .= " icon-position-{$icon_position}";
        }
    }
    
    // Add custom CSS if selected
    if ($button_style === 'custom') {
        $additional_css = get_option('rmenu_wc_checkout_custom_css', '');
    }

    return [
        'classes' => $classes,
        'style' => $style,
        'icon' => $icon,
        'additional_css' => $additional_css
    ];
}

/**
 * Add button-specific CSS based on settings
 */
function onepaquc_add_button_css() {
    $button_styling = onepaquc_get_button_styling();
    $additional_css = $button_styling['additional_css'];
    
    // Start output buffer for CSS
    ob_start();
    
    // Basic styles for the button
    ?>
    <style>
        /* Base styling for direct checkout button */
        .rmenu-direct-checkout-btn {
            transition: all 0.3s ease;
        }
        
        /* Alternative button style */
        .rmenu-direct-checkout-btn.alt-style {
            border-radius: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        /* Icon positioning styles */
        .rmenu-direct-checkout-btn .rmenu-icon {
            display: inline-block;
            vertical-align: middle;
        }
        
        .rmenu-direct-checkout-btn.icon-position-left .rmenu-icon {
            margin-right: 8px;
        }
        
        .rmenu-direct-checkout-btn.icon-position-right .rmenu-icon {
            margin-left: 8px;
        }
        
        .rmenu-direct-checkout-btn.icon-position-top .rmenu-icon {
            display: block;
            margin: 0 auto 5px;
        }
        
        .rmenu-direct-checkout-btn.icon-position-bottom .rmenu-icon {
            display: block;
            margin: 5px auto 0;
        }
        
        .rmenu-direct-checkout-btn.icon-position-top,
        .rmenu-direct-checkout-btn.icon-position-bottom {
            text-align: center;
        }
        
        /* Mobile-optimized styles for direct checkout button */
        @media only screen and (max-width: 768px) {
            .mobile-optimized-checkout {
                width: 100%;
                margin-top: 10px;
                margin-bottom: 10px;
                padding: 12px 15px;
                font-size: 16px;
                text-align: center;
                box-sizing: border-box;
            }
            
            /* Make button more visible on mobile */
            body.single-product .mobile-optimized-checkout {
                display: block;
                clear: both;
            }
            
            /* Fix display in product loops on mobile */
            .woocommerce-loop-product__link + .button + .mobile-optimized-checkout,
            .woocommerce-loop-product__link + .mobile-optimized-checkout {
                display: block;
                margin-top: 5px;
            }
        }
        
        <?php echo $additional_css; ?>
    </style>
    <?php
    
    // Output the CSS
    echo ob_get_clean();
}

// Add button CSS to head
add_action('wp_head', 'onepaquc_add_button_css');

/**
 * Render the button icon based on settings
 * 
 * @param array $icon Icon settings with content and position
 * @param string $button_text The button text
 * @return string HTML for button with icon
 */
function onepaquc_render_button_with_icon($icon, $button_text) {
    if (empty($icon) || !isset($icon['content']) || !isset($icon['position'])) {
        return $button_text;
    }
    
    $icon_content = $icon['content'];
    $position = $icon['position'];
    
    switch ($position) {
        case 'left':
            return $icon_content . $button_text;
        case 'right':
            return $button_text . $icon_content;
        case 'top':
            return $icon_content . '<span style="display:block;">' . $button_text . '</span>';
        case 'bottom':
            return '<span style="display:block;">' . $button_text . '</span>' . $icon_content;
        default:
            return $button_text;
    }
}

// Function to add checkout button on single product page
function onepaquc_add_checkout_button() {
    global $product;

    if (!is_product() || !$product) {
        return;
    }

    if (!onepaquc_should_display_button($product)) {
        return;
    }

    $product_id = $product->get_id();
    $product_type = $product->get_type();
    $button_styling = onepaquc_get_button_styling();
    $icon = $button_styling["icon"];

    $icon_content = isset($icon['content']) ? $icon['content'] : '';
    $icon_position = isset($icon['position']) ? $icon['position'] : 'left';

    // Button text
    $button_text = esc_html(get_option('txt-direct-checkout') !== "" ? get_option('txt-direct-checkout', 'Direct Checkout') : "Direct Checkout");

    // Prepare icon HTML
    $icon_html = '<span class="onepaquc-icon">' . $icon_content . '</span>';

    // Combine icon and text based on position
    switch ($icon_position) {
        case 'right':
            $button_inner = $button_text . ' ' . $icon_html;
            break;
        case 'top':
            $button_inner = $icon_html . '<br>' . $button_text;
            break;
        case 'bottom':
            $button_inner = $button_text . '<br>' . $icon_html;
            break;
        case 'left':
        default:
            $button_inner = $icon_html . ' ' . $button_text;
            break;
    }

    // Output the button
    echo '<a href="#checkout-popup" class="' . esc_attr($button_styling['classes']) . '" data-product-id="' . esc_attr($product_id) . '" data-product-type="' . esc_attr($product_type) . '" style="' . esc_attr($button_styling['style']) . '">' . $button_inner . '</a>';
}


// Function to position the checkout button on single product page
function onepaquc_modify_add_to_cart_button() {
    $position = get_option("rmenu_wc_direct_checkout_position", "after_add_to_cart");

    switch ($position) {
        case 'before_add_to_cart':
            add_action('woocommerce_before_add_to_cart_button', 'onepaquc_add_checkout_button');
            break;

        case 'after_add_to_cart':
        case 'replace_add_to_cart':
            add_action('woocommerce_after_add_to_cart_button', 'onepaquc_add_checkout_button');
            break;
    }
}

// Apply the checkout button modifications if enabled
if (get_option('rmenu_add_direct_checkout_button')) {
    $position = get_option("rmenu_wc_direct_checkout_position", "after_add_to_cart");
    if ($position === "replace_add_to_cart") {
        add_action('wp_head', 'onepaquc_hide_add_to_cart_css');
    }
    add_action('woocommerce_before_single_product', 'onepaquc_modify_add_to_cart_button');
}

// Function to hide the original add to cart button when in replace mode
function onepaquc_hide_add_to_cart_css() {
    if (is_product()) {
        ?>
        <style>
            button.single_add_to_cart_button,
            .quantity {
                display: none !important;
            }
        </style>
        <?php
    }
}

// Function to add checkout button to product loops (listings)
function onepaquc_add_checkout_button_to_add_to_cart_shortcode($link, $product) {
    if (!onepaquc_should_display_button($product)) {
        return $link;
    }

    $position = get_option("rmenu_wc_direct_checkout_position", "after_add_to_cart");
    $product_id = $product->get_id();
    $product_type = $product->get_type();
    $button_styling = onepaquc_get_button_styling();
    $icon = $button_styling["icon"];

    $icon_content = isset($icon['content']) ? $icon['content'] : '';
    $icon_position = isset($icon['position']) ? $icon['position'] : 'left';

    // Button text
    $button_text = esc_html(get_option('txt-direct-checkout') !== "" ? get_option('txt-direct-checkout', 'Direct Checkout') : "Direct Checkout");

    // Prepare icon HTML
    $icon_html = '<span class="onepaquc-icon">' . $icon_content . '</span>';

    // Combine icon and text based on position
    switch ($icon_position) {
        case 'right':
            $button_inner = $button_text . ' ' . $icon_html;
            break;
        case 'top':
            $button_inner = $icon_html . '<br>' . $button_text;
            break;
        case 'bottom':
            $button_inner = $button_text . '<br>' . $icon_html;
            break;
        case 'left':
        default:
            $button_inner = $icon_html . ' ' . $button_text;
            break;
    }

    // Final button HTML
    $checkout_button = '<a href="#checkout-popup" class="' . esc_attr($button_styling['classes']) . '" data-product-id="' . esc_attr($product_id) . '" data-product-type="' . esc_attr($product_type) . '" style="' . esc_attr($button_styling['style']) . '">' . $button_inner . '</a>';

    // Apply the position setting
    switch ($position) {
        case 'before_add_to_cart':
            return $checkout_button . ' ' . $link;

        case 'after_add_to_cart':
            return $link . ' ' . $checkout_button;

        case 'replace_add_to_cart':
            return $checkout_button;

        default:
            return $link . ' ' . $checkout_button;
    }
}


// Apply the checkout button to product loops if enabled
if (get_option('rmenu_add_direct_checkout_button')) {
    add_filter('woocommerce_loop_add_to_cart_link', 'onepaquc_add_checkout_button_to_add_to_cart_shortcode', 10, 2);
}

add_action('template_redirect', 'onepaquc_add_random_product_if_cart_empty');

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


add_filter('woocommerce_checkout_fields', 'onepaquc_remove_required_checkout_fields');

function onepaquc_remove_required_checkout_fields($fields)
{
    if (get_option('onepaquc_checkout_fields')) {
        $removed_fields = get_option('onepaquc_checkout_fields');

        // Log the fields for debugging
        error_log(json_encode($removed_fields));
        error_log(json_encode($fields));

        foreach ($fields as $key => $field_group) {
            foreach ($field_group as $field_key => $field) {
                // Check if the field_key contains any of the removed fields
                foreach ($removed_fields as $removed_field) {
                    if (strpos($field_key, $removed_field) !== false) {
                        $fields[$key][$field_key]['required'] = false;
                    }
                }
            }
        }
    }
    return $fields;
}

// if (get_option('onepaquc_checkout_fields')) {
//     $checkout_fields = get_option('onepaquc_checkout_fields');
//     foreach ($checkout_fields as $field) {
//         if (isset($onepaquc_rcheckoutformfields[$field])) {
//             $selector = $onepaquc_rcheckoutformfields[$field]['selector'];
//             $custom_css .= "{$selector} { display: none !important; }\n";
//         }
//     }
// }

add_action('wp_ajax_woocommerce_clear_cart', 'onepaquc_clear_cart');
add_action('wp_ajax_nopriv_woocommerce_clear_cart', 'onepaquc_clear_cart');

function onepaquc_clear_cart() {
    WC()->cart->empty_cart();
    wp_send_json_success();
}
