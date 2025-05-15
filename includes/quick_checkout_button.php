<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

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
function onepaquc_should_display_button($product)
{
    if (!$product || get_option("rmenu_add_checkout_button", "1") !== "1") {
        return false;
    }

    if (!$product->is_in_stock()) {
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
function onepaquc_get_button_styling()
{
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
function onepaquc_add_button_css()
{
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
            .woocommerce-loop-product__link+.button+.mobile-optimized-checkout,
            .woocommerce-loop-product__link+.mobile-optimized-checkout {
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
function onepaquc_render_button_with_icon($icon, $button_text)
{
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
function onepaquc_add_checkout_button()
{
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
function onepaquc_modify_add_to_cart_button()
{
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

if (get_option('rmenu_add_to_cart_catalog_display') == "hide") {
    add_action('wp_footer', 'onepaquc_hide_add_to_cart_css');
}

// Function to hide the original add to cart button when in replace mode
function onepaquc_hide_add_to_cart_css()
{
    ?>
        <style>
            button.single_add_to_cart_button,
            a.button.product_type_simple.add_to_cart_button,
            .quantity {
                display: none !important;
            }
        </style>
<?php
}

// Function to add checkout button to product loops (listings)
function onepaquc_add_checkout_button_to_add_to_cart_shortcode($link, $product)
{
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



