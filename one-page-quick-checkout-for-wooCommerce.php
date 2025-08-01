<?php

/**
 * Plugin Name: One Page Quick Checkout for WooCommerce
 * Plugin URI:  https://plugincy.com/one-page-quick-checkout-for-woocommerce/
 * Description: Enhance WooCommerce with popup checkout, cart drawer, and flexible checkout templates to boost conversions.
 * Version: 1.1.0
 * Author: plugincy
 * Author URI: https://plugincy.com
 * license: GPL2
 * Text Domain: one-page-quick-checkout-for-woocommerce
 * Requires Plugins: woocommerce
 */


if (! defined('ABSPATH')) exit; // Exit if accessed directly
define("RMENU_VERSION", "1.1.0");

// Include the admin notice file
require_once plugin_dir_path(__FILE__) . 'includes/admin-notice.php';

// admin menu
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';

// include one page checkout shortcode
require_once plugin_dir_path(__FILE__) . 'includes/one-page-checkout-shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/add-to-cart-button.php';

global $onepaquc_checkoutformfields, $onepaquc_productpageformfields, $onepaquc_rcheckoutformfields, $onepaquc_string_settings_fields;

$onepaquc_string_settings_fields = [
    "rmsg_editor",
    "onpage_checkout_position",
    "onpage_checkout_cart_empty",
    "onpage_checkout_enable",
    "onpage_checkout_enable_all",
    "onpage_checkout_cart_add",
    "onpage_checkout_widget_cart_empty",
    "onpage_checkout_widget_cart_add",
    "onpage_checkout_hide_cart_button",
    "rmenu_quantity_control",
    "rmenu_at_one_product_cart",
    "rmenu_disable_cart_page",
    "rmenu_link_product",
    "rmenu_allow_analytics",
    "rmenu_remove_product",
    "rmenu_add_img_before_product",
    "rmenu_add_direct_checkout_button",
    "rmenu_enable_custom_add_to_cart",
    "rmenu_wc_checkout_guest_enabled",
    "rmenu_wc_checkout_mobile_optimize",
    "rmenu_wc_direct_checkout_position",
    "rmenu_variation_show_archive",
    "rmenu_wc_hide_select_option",
    "txt-direct-checkout",
    "rmenu_wc_checkout_color",
    "rmenu_add_to_cart_bg_color",
    "rmenu_wc_checkout_text_color",
    "rmenu_wc_checkout_custom_css",
    "rmenu_add_to_cart_text_color",
    "rmenu_add_to_cart_hover_bg_color",
    "rmenu_add_to_cart_hover_text_color",
    "rmenu_add_to_cart_border_radius",
    "rmenu_add_to_cart_font_size",
    "rmenu_add_to_cart_width",
    "rmenu_add_to_cart_custom_css",
    "rmenu_add_to_cart_icon",
    "rmenu_add_to_cart_icon_position",
    "rmenu_add_to_cart_catalog_display",
    "rmenu_wc_checkout_style",
    "rmenu_add_to_cart_style",
    "rmenu_wc_checkout_icon",
    "rmenu_wc_checkout_icon_position",
    "rmenu_wc_checkout_method",
    "rmenu_wc_clear_cart",
    "rmenu_wc_one_click_purchase",
    "rmenu_wc_add_confirmation",
    "rmenu_enable_ajax_add_to_cart",
    "rmenu_add_to_cart_default_qty",
    "rmenu_show_quantity_archive",
    "rmenu_redirect_after_add",
    "rmenu_add_to_cart_animation",
    "rmenu_add_to_cart_notification_style",
    "rmenu_add_to_cart_success_message",
    "rmenu_show_view_cart_link",
    "rmenu_add_to_cart_notification_duration",
    "rmenu_show_checkout_link",
    "rmenu_sticky_add_to_cart_mobile",
    "rmenu_mobile_add_to_cart_text",
    "rmenu_mobile_button_size",
    "rmenu_hide_on_mobile_options",
    "rmenu_mobile_icon_only",
    "rmenu_add_to_cart_loading_effect",
    "rmenu_disable_btn_out_of_stock",
    "rmenu_force_button_css",
    "rmenu_enable_quick_view",
    "rmenu_quick_view_button_text",
    "rmenu_quick_view_button_position",
    "rmenu_quick_view_display_type",
    "rmenu_quick_view_modal_size",
    "rmenu_quick_view_enable_lightbox",
    "rmenu_quick_view_loading_effect",
    "rmenu_quick_view_button_style",
    "rmenu_quick_view_button_color",
    "rmenu_quick_view_text_color",
    "rmenu_quick_view_button_icon",
    "rmenu_quick_view_icon_position",
    "rmenu_quick_view_custom_css",
    "rmenu_quick_view_ajax_add_to_cart",
    "rmenu_quick_view_direct_checkout",
    "rmenu_quick_view_mobile_optimize",
    "rmenu_quick_view_close_on_add",
    "rmenu_quick_view_keyboard_nav",
    "rmenu_quick_view_preload",
    "rmenu_quick_view_enable_cache",
    "rmenu_quick_view_cache_expiration",
    "rmenu_quick_view_lazy_load",
    "rmenu_quick_view_details_text",
    "rmenu_quick_view_close_text",
    "rmenu_quick_view_prev_text",
    "rmenu_quick_view_next_text",
    "rmenu_quick_view_track_events",
    "rmenu_quick_view_event_category",
    "rmenu_quick_view_event_action",
    "rmenu_quick_view_load_scripts",
    "rmenu_quick_view_theme_compat",
    "onepaquc_trust_badges_enabled",
    "onepaquc_trust_badge_position",
    "onepaquc_trust_badge_style",
    "show_custom_html",
    "rmenu_enable_sticky_cart",
    "rmenu_cart_layout",
    "rmenu_cart_top_position",
    "rmenu_cart_left_position",
    "rmenu_cart_bg_color",
    "rmenu_cart_text_color",
    "rmenu_cart_hover_bg",
    "rmenu_cart_hover_text",
    "rmenu_cart_border_radius",
    "rmenu_show_cart_icon",
    "rmenu_show_cart_count",
    "rmenu_show_cart_total",
    "rmenu_cart_animation",
];

require_once plugin_dir_path(__FILE__) . 'includes/global-values.php';
require_once plugin_dir_path(__FILE__) . 'includes/quickview.php';
require_once plugin_dir_path(__FILE__) . 'admin/license-tab.php';
require_once plugin_dir_path(__FILE__) . 'includes/analytics.php';

// Enqueue scripts and styles
function onepaquc_cart_enqueue_scripts()
{
    $checkout_page_id = wc_get_page_id('checkout');

    // Check if checkout page exists and has [woocommerce_checkout] shortcode
    if ($checkout_page_id === -1) {
        // Create a new checkout page if it doesn't exist
        $new_checkout_id = wp_insert_post([
            'post_title'   => __('Checkout'),
            'post_content' => '[woocommerce_checkout]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
        if ($new_checkout_id && !is_wp_error($new_checkout_id)) {
            update_option('woocommerce_checkout_page_id', $new_checkout_id);
        }
    }

    wp_enqueue_style('rmenu-cart-style', plugin_dir_url(__FILE__) . 'assets/css/rmenu-cart.css', array(), "1.1.0");
    wp_enqueue_script('rmenu-cart-script', plugin_dir_url(__FILE__) . 'assets/js/rmenu-cart.js', array('jquery'), "1.1.0", true);
    wp_enqueue_script('cart-script', plugin_dir_url(__FILE__) . 'assets/js/cart.js', array('jquery'), "1.1.0", true);
    $direct_checkout_behave = [
        'rmenu_wc_checkout_method' => get_option('rmenu_wc_checkout_method', 'direct_checkout'),
        'rmenu_wc_clear_cart' => get_option('rmenu_wc_clear_cart', 0),
        'rmenu_wc_one_click_purchase' => 0,
        'rmenu_wc_add_confirmation' => get_option('rmenu_wc_add_confirmation', 0),
    ];
    // Localize script for AJAX URL and WooCommerce cart variables
    wp_localize_script('cart-script', 'onepaquc_wc_cart_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'get_cart_content_none' => wp_create_nonce('get_cart_content_none'),
        'update_cart_item_quantity' => wp_create_nonce('update_cart_item_quantity'),
        'remove_cart_item' => wp_create_nonce('remove_cart_item'),
        'onepaquc_refresh_checkout_product_list' => wp_create_nonce('onepaquc_refresh_checkout_product_list'),
        'get_variations_nonce' => wp_create_nonce('get_variations_nonce'), // Add this line
        'direct_checkout_behave' => $direct_checkout_behave,
        'checkout_url' => wc_get_checkout_url(),
        'cart_url'     => wc_get_cart_url(),
        'nonce' => wp_create_nonce('rmenu-ajax-nonce'),
    ));
    // Retrieve the rmsg_editor value
    $rmsg_editor_value = get_option('rmsg_editor', '');

    // Localize the script with the rmsg_editor value
    wp_localize_script('rmenu-cart-script', 'onepaquc_rmsgValue', array(
        'rmsgEditor' => $rmsg_editor_value,
        'checkout_url' => wc_get_checkout_url()
    ));
    wp_localize_script('rmenu-cart-script', 'onepaquc_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

    wp_enqueue_style('dashicons');
}
add_action('wp_enqueue_scripts', 'onepaquc_cart_enqueue_scripts', 20);

add_action('admin_enqueue_scripts', 'onepaquc_cart_admin_styles');

// Enqueue the admin stylesheet only for this settings page
function onepaquc_cart_admin_styles($hook)
{
    if ($hook === 'toplevel_page_onepaquc_cart') {
        wp_enqueue_style('onepaquc_cart_admin_css', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css', array(), "1.1.0");
        wp_enqueue_style('select2-css', plugin_dir_url(__FILE__) . 'assets/css/select2.min.css', array(), "1.1.0");
        wp_enqueue_script('select2-js', plugin_dir_url(__FILE__) . 'assets/js/select2.min.js', array('jquery'), "1.1.0", true);
    }
    wp_enqueue_style('onepaquc_cart_admin_css', plugin_dir_url(__FILE__) . 'assets/css/admin-documentation.css', array(), "1.1.0");
    wp_enqueue_script('rmenu-admin-script', plugin_dir_url(__FILE__) . 'assets/js/admin-documentation.js', array('jquery'), "1.1.0", true);
}

// add shortcode
// if (get_option('onepaquc_api_key') && get_option('onepaquc_validity_days')!=="0"){
require_once plugin_dir_path(__FILE__) . 'includes/rmenu-shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajaxhandler.php';
require_once plugin_dir_path(__FILE__) . 'includes/label-change.php';



// }else{
//     require_once plugin_dir_path(__FILE__) . 'includes/without_api_short_code';
// }

function onepaquc_editor_script()
{
    wp_enqueue_script(
        'plugincy-custom-editor',
        plugin_dir_url(__FILE__) . 'includes/blocks/editor.js',
        array('wp-blocks', 'wp-element', 'wp-edit-post', 'wp-dom-ready', 'wp-plugins'),
        '1.1.0',
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

require_once plugin_dir_path(__FILE__) . 'admin/product_edit_page_setup.php';


function onepaquc_display_checkout_on_single_product()
{
    // Only run on single product pages
    if (!is_product()) {
        global $post;
        if (isset($post) && is_object($post) && strpos($post->post_content, 'plugincy_one_page_checkout') === false) {
            add_action('wp_head', 'onepaquc_rmenu_checkout_popup');
        }
        return;
    }

    $product_id = get_the_ID();
    $product = wc_get_product($product_id);

    if (!$product || !is_a($product, 'WC_Product')) {
        global $post;
        if (isset($post) && is_object($post) && strpos($post->post_content, 'plugincy_one_page_checkout') === false) {
            add_action('wp_head', 'onepaquc_rmenu_checkout_popup');
        }
        return;
    }

    $one_page_checkout = get_post_meta($product_id, '_one_page_checkout', true);
    $isallproduct_checkout_enable = get_option("onpage_checkout_enable_all", 0);

    if ($one_page_checkout === 'yes' || $isallproduct_checkout_enable) {

        if (!WC()->cart->is_empty() && get_option("onpage_checkout_cart_empty", "1") === "1") {
            WC()->cart->empty_cart();
        }

        if (get_option("onpage_checkout_cart_add", "1") === "1") {
            if ($product->is_type('variable')) {
                $available_variations = $product->get_available_variations();
                if (!empty($available_variations)) {
                    $variation = $available_variations[0];
                    $variation_id = $variation['variation_id'];
                    $variation_attributes = $variation['attributes'];
                    WC()->cart->add_to_cart($product_id, 1, $variation_id, $variation_attributes);
                }
            } elseif ($product->is_type('grouped')) {
                $children_ids = $product->get_children();
                foreach ($children_ids as $child_id) {
                    $child_product = wc_get_product($child_id);
                    if ($child_product && $child_product->is_purchasable() && $child_product->is_in_stock()) {
                        WC()->cart->add_to_cart($child_id, 1);
                    }
                }
            } else {
                // Simple, downloadable, etc.
                WC()->cart->add_to_cart($product_id, 1);
            }
        }

        add_action('wp_enqueue_scripts', 'onepaquc_add_checkout_inline_styles', 99);
        if (get_option("onpage_checkout_enable", "1") === "1") {
            add_action('woocommerce_after_single_product_summary', 'onepaquc_display_one_page_checkout_form',  get_option("onpage_checkout_position", '9'));
        }
        if (get_option("onpage_checkout_hide_cart_button") === "1") {
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
            // add_filter('woocommerce_is_purchasable', function ($is_purchasable, $product) {
            //     return false;
            // }, 10, 2);
            add_action('wp_head', function () {
                echo '<style>
                    .quantity, 
                    button.single_add_to_cart_button.button {
                        display: none !important;
                    }
                </style>';
            });
        }
    } else {
        global $post;
        if (isset($post) && is_object($post) && strpos($post->post_content, 'plugincy_one_page_checkout') === false) {
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
    // check if cart is empty
    if (WC()->cart->is_empty()) {
        return;
    }
?>
    <div class="one-page-checkout-container" id="checkout-popup" data-isonepagewidget="true">
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
        $new_html .= '<input type="text" name="cart[' . esc_attr($cart_item_key) . '][qty]" class="checkout-qty-input" value="' . esc_attr($quantity) . '" min="1" step="1" size="4">';
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

require_once plugin_dir_path(__FILE__) . 'includes/extra_features.php';
require_once plugin_dir_path(__FILE__) . 'includes/quick_checkout_button.php';
require_once plugin_dir_path(__FILE__) . 'includes/trusted-badge.php';
require_once plugin_dir_path(__FILE__) . 'includes/elementor/plugincy-cart-widget.php';
require_once plugin_dir_path(__FILE__) . 'includes/elementor/one-page-checkout.php';
require_once plugin_dir_path(__FILE__) . 'includes/elementor/elementor-category.php';


add_filter('woocommerce_checkout_fields', 'onepaquc_remove_required_checkout_fields');

function onepaquc_remove_required_checkout_fields($fields)
{
    if (get_option('onepaquc_checkout_fields')) {
        $removed_fields = get_option('onepaquc_checkout_fields');

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


// add_settings_link
function onepaquc_add_settings_link($links)
{
    if (!is_array($links)) {
        $links = [];
    }
    $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=onepaquc_cart')) . '">' . esc_html__('Settings', 'one-page-quick-checkout-for-woocommerce') . '</a>';
    $pro_link = '<a href="https://plugincy.com/one-page-quick-checkout-for-woocommerce" style="color: #ff5722; font-weight: bold;" target="_blank">' . esc_html__('Get Pro', 'one-page-quick-checkout-for-woocommerce') . '</a>';
    $links[] = $settings_link;
    $links[] = $pro_link;
    return $links;
}


// add settings button after deactivate button in plugins page

add_action('plugin_action_links_' . plugin_basename(__FILE__), 'onepaquc_add_settings_link');
add_action('admin_init', 'onepaquc_add_settings_link');


add_action('wp_head', function () {
    if (isset($_GET['hide_header_footer']) && $_GET['hide_header_footer'] == '1') {
        echo '<style>
            div#ast-scroll-top, div#wpadminbar, header, .site-header, #masthead, footer, .site-footer, #colophon,.rmenu-cart,iframe {
                display: none !important;
            }
            html {
                margin: 0 !important;
                padding-bottom: 100px;
            }
            .ast-container {
                padding: 0 !important;
            }
            .woocommerce-info {
                margin: 0 !important;
            }
            .form-row.place-order {
                position: fixed;
                bottom: 0;
                background: #fff;
                width: 100%;
                left: 0;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 20px 0 !important;
                box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
            }
            .form-row.place-order p.order-total-price {
                margin: 0 !important;
            }
            button#place_order {
                width: max-content !important;
                margin: 0 !important;
            }
        </style>
        ';
        // Add JS to append ?hide_header_footer=1 to all links
    ?>
        <script>
            const elementsToHide = [
                'div#ast-scroll-top',
                'div#wpadminbar',
                'header',
                '.site-header',
                '#masthead',
                'footer',
                '.site-footer',
                '#colophon',
                '.rmenu-cart',
                'iframe'
            ];

            elementsToHide.forEach(selector => {
                const elements = document.querySelectorAll(selector);
                elements.forEach(element => {
                    element.style.setProperty('display', 'none', 'important');
                });
            });
            // Define a function to append ?hide_header_footer=1 to all links
            function appendHideHeaderFooterParam() {
                // Store current screen width & height in localStorage
                localStorage.setItem('screen_width', window.innerWidth);
                localStorage.setItem('screen_height', window.innerHeight);

                var links = document.querySelectorAll('a[href]');
                links.forEach(function(link) {
                    var href = link.getAttribute('href');
                    // Ignore anchors, mailto, tel, javascript, and already set param
                    if (
                        href.indexOf('mailto:') === 0 ||
                        href.indexOf('tel:') === 0 ||
                        href.indexOf('#') === 0 ||
                        href.indexOf('hide_header_footer=1') !== -1
                    ) {
                        return;
                    }

                    // Add param
                    if (href.indexOf('?') !== -1) {
                        href += '&hide_header_footer=1';
                    } else {
                        href += '?hide_header_footer=1';
                    }
                    link.setAttribute('href', href);
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                appendHideHeaderFooterParam();
            });

            // Also run after every AJAX completion (jQuery required)
            if (window.jQuery) {
                jQuery(document).ajaxComplete(function() {
                    appendHideHeaderFooterParam();
                });
            }
        </script>
    <?php
    } else {
    ?>
        <script>
            const elements2ToHide = [
                'div#ast-scroll-top',
                'div#wpadminbar',
                'header',
                '.site-header',
                '#masthead',
                'footer',
                '.site-footer',
                '#colophon',
                '.rmenu-cart',
                'iframe'
            ];

            elements2ToHide.forEach(selector => {
                const elements = document.querySelectorAll(selector);
                elements.forEach(element => {
                    element.style.setProperty('display', 'none', 'important');
                });
            });
            // Get stored screen width & height from localStorage
            const stored_width = localStorage.getItem('screen_width');
            const stored_height = localStorage.getItem('screen_height');

            // Compare with current window size
            if (
                stored_width && stored_height &&
                parseInt(stored_width, 10) === window.innerWidth &&
                parseInt(stored_height, 10) === window.innerHeight
            ) {
                // Hide header and footer elements if sizes match
                var style = document.createElement('style');
                style.innerHTML = `
                        div#ast-scroll-top, div#wpadminbar, header, .site-header, #masthead, footer, .site-footer, #colophon,.rmenu-cart,iframe {
                            display: none !important;
                        }
                        html {
                            margin: 0 !important;
                            padding-bottom: 100px;
                        }
                        .ast-container {
                            padding: 0 !important;
                        }
                        .woocommerce-info {
                            margin: 0 !important;
                        }
                        .form-row.place-order {
                            position: fixed;
                            bottom: 0;
                            background: #fff;
                            width: 100%;
                            left: 0;
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            padding: 20px 0 !important;
                            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
                        }
                        .form-row.place-order p.order-total-price {
                            margin: 0 !important;
                        }
                        button#place_order {
                            width: max-content !important;
                            margin: 0 !important;
                        }
                    `;
                document.head.appendChild(style);
            }
        </script>
<?php
    }
});

if (get_option("rmenu_enable_sticky_cart", 1)) {
    function onepaquc_display_cart()
    {
        if (class_exists('WooCommerce')) {
            echo do_shortcode('[plugincy_cart drawer="right" cart_icon="cart" product_title_tag="h4" position="fixed"]');
        }
    }

    add_action('wp_footer', 'onepaquc_display_cart');
}






class onepaquc_cart_analytics_main
{
    private $analytics;

    public function __construct()
    {
        // Initialize analytics with the correct plugin file path
        $this->analytics = new onepaquc_cart_anaylytics(
            '03',
            'https://plugincy.com/wp-json/product-analytics/v1',
            RMENU_VERSION,
            'One Page Quick Checkout for WooCommerce',
            __FILE__ // Pass the main plugin file
        );

        add_action('admin_footer',  array($this->analytics, "add_deactivation_feedback_form"));

        // Plugin hooks
        add_action('init', array($this, 'init'));
        if (get_option('rmenu_allow_analytics', 1)) {
            add_action('admin_init', array($this, 'admin_init'));
        }

        // Handle deactivation feedback AJAX
        add_action('wp_ajax_send_deactivation_feedback', array($this, 'handle_deactivation_feedback'));
    }

    public function init()
    {
        // Any initialization code
    }

    public function admin_init()
    {
        // Send analytics data on first activation or weekly
        $this->maybe_send_analytics();
    }

    private function maybe_send_analytics()
    {
        $last_sent = get_option('onepaquc_analytics_last_sent', 0);
        $week_ago = strtotime('-1 week');

        if ($last_sent < $week_ago) {
            $this->analytics->send_tracking_data();
            update_option('onepaquc_analytics_last_sent', time());
        }
    }

    public function handle_deactivation_feedback()
    {
        check_ajax_referer('deactivation_feedback', 'nonce');

        $reason = sanitize_text_field($_POST['reason'] ?? '');
        $this->analytics->send_deactivation_data($reason);

        wp_die();
    }
}

new onepaquc_cart_analytics_main();
