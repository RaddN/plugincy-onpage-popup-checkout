<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Add Quick Checkout button on single product pages and product listings
 * with consistent positioning based on settings
 */

/**
 * data attribute for external/affiliate products so JS can navigate without AJAX add-to-cart.
 *
 * @param WC_Product $product Product object.
 * @return string HTML fragment for the anchor tag (empty when not external).
 */
function onepaquc_checkout_btn_external_data_attr($product)
{
    if (!$product instanceof WC_Product || $product->get_type() !== 'external') {
        return '';
    }

    return ' data-external-product-url="' . esc_attr($product->get_product_url()) . '"';
}

/**
 * Check if button should display based on product type, current page, and user login status
 *
 * @param WC_Product $product The product object
 * @return bool Whether the button should be displayed
 */
function onepaquc_should_display_button($product)
{
    if (is_numeric($product) && function_exists('wc_get_product')) {
        $product = wc_get_product(absint($product));
    }

    if (!$product instanceof WC_Product || get_option("rmenu_add_checkout_button", "1") !== "1") {
        return false;
    }

    if (!$product->is_in_stock() || !$product->is_purchasable()) {
        return false;
    }

    

    // Check user login status
    $guest_checkout_enabled = get_option('rmenu_wc_checkout_guest_enabled', '1');
    if ($guest_checkout_enabled !== '1' && !is_user_logged_in()) {
        return false; // Only show for logged-in users when guest checkout is disabled
    }
    

    // Check product type
    $allowed_product_types = onepaquc_normalize_key_list(
        get_option('rmenu_show_quick_checkout_by_types', ["simple", "variable", "external"]),
        ["simple", "variable", "external"]
    );
    $product_type = $product->get_type();

    if (!in_array($product_type, $allowed_product_types, true)) {
        return false;
    }

    // If product is variable, ensure there's at least one purchasable in-stock variation.
    if ($product_type === 'variable') {
        // Use helper to pick a suitable variation when we have a product object.
        if ($product instanceof WC_Product_Variable) {
            $variation_id = onepaquc_pick_variation_id($product);
            if (empty($variation_id)) {
                return false;
            }
        } else {
            // Try to obtain a product object (in case an ID was passed)
            $prod_obj = is_numeric($product) ? wc_get_product((int) $product) : null;
            if ($prod_obj instanceof WC_Product_Variable) {
                $variation_id = onepaquc_pick_variation_id($prod_obj);
                if (empty($variation_id)) {
                    return false;
                }
            } else {
                // Cannot validate a variable product without a proper product object
                return false;
            }
        }
    }

    // Get allowed pages
    $allowed_pages = onepaquc_normalize_key_list(
        get_option('rmenu_show_quick_checkout_by_page', ["single", "related", "upsells", "shop-page", "category-archives", "tag-archives", "featured-products", "on-sale", "recent", "widgets", "shortcodes"]),
        ["single", "related", "upsells", "shop-page", "category-archives", "tag-archives", "featured-products", "on-sale", "recent", "widgets", "shortcodes"]
    );

    // Check current page type

    if (is_shop() && in_array('shop-page', $allowed_pages, true)) {
        return true;
    }

    if (is_product_category() && in_array('category-archives', $allowed_pages, true)) {
        return true;
    }

    if (is_product_tag() && in_array('tag-archives', $allowed_pages, true)) {
        return true;
    }

    if (is_product() && in_array('single', $allowed_pages, true)) {
        return true;
    }

    if (is_cart() && in_array('cross-sells', $allowed_pages, true)) {
        return true;
    }

    // Check for related products
    global $woocommerce_loop;
    if (isset($woocommerce_loop['name'])) {
        if ($woocommerce_loop['name'] === 'related' && in_array('related', $allowed_pages, true)) {
            return true;
        }

        if ($woocommerce_loop['name'] === 'up-sells' && in_array('upsells', $allowed_pages, true)) {
            return true;
        }

        if ($woocommerce_loop['name'] === 'cross-sells' && in_array('cross-sells', $allowed_pages, true)) {
            return true;
        }
    }

    // Check for featured products
    if (isset($woocommerce_loop['featured']) && $woocommerce_loop['featured'] && in_array('featured-products', $allowed_pages, true)) {
        return true;
    }

    // Check for on-sale products
    if (isset($woocommerce_loop['name']) && $woocommerce_loop['name'] === 'sale_products' && in_array('on-sale', $allowed_pages, true)) {
        return true;
    }

    // Check for recent products
    if (isset($woocommerce_loop['name']) && $woocommerce_loop['name'] === 'recent_products' && in_array('recent', $allowed_pages, true)) {
        return true;
    }

    // Check for WooCommerce shortcodes in post content
    if (in_array('shortcodes', $allowed_pages, true) && !is_shop() && !is_product_category() && !is_product_tag() && !is_product()) {
        return true;
    }

    return false;
}

/**
 * Get the CSS classes and styling for the direct checkout button
 * 
 * @return array Array with 'classes', 'style', and 'icon' for the button.
 */

function onepaquc_get_direct_checkout_style_options()
{
    $button_style = get_option('rmenu_wc_checkout_style', 'default');
    if (!in_array($button_style, ['default', 'alt', 'custom'], true)) {
        $button_style = 'default';
    }

    $width_mode = get_option('rmenu_wc_checkout_width', 'auto');
    if (!in_array($width_mode, ['auto', 'full', 'custom'], true)) {
        $width_mode = 'auto';
    }

    $bg_color = onepaquc_sanitize_hex_color(get_option('rmenu_wc_checkout_color', '#000000'), '#000000');
    $text_color = onepaquc_sanitize_hex_color(get_option('rmenu_wc_checkout_text_color', '#ffffff'), '#ffffff');
    $hover_bg_color = onepaquc_sanitize_hex_color(get_option('rmenu_wc_checkout_hover_bg_color', '#222222'), '#222222');
    $hover_text_color = onepaquc_sanitize_hex_color(get_option('rmenu_wc_checkout_hover_text_color', '#ffffff'), '#ffffff');

    $bg_color = $bg_color ? $bg_color : '#000000';
    $text_color = $text_color ? $text_color : '#ffffff';
    $hover_bg_color = $hover_bg_color ? $hover_bg_color : '#222222';
    $hover_text_color = $hover_text_color ? $hover_text_color : '#ffffff';

    $border_radius = (int) onepaquc_get_numeric_option('rmenu_wc_checkout_border_radius', 4, 0, 50);

    $font_size = (int) onepaquc_get_numeric_option('rmenu_wc_checkout_font_size', 14, 10, 30);

    $custom_width = (int) onepaquc_get_numeric_option('rmenu_wc_checkout_custom_width', 220, 50, 500);

    return [
        'button_style' => $button_style,
        'bg_color' => $bg_color,
        'text_color' => $text_color,
        'hover_bg_color' => $hover_bg_color,
        'hover_text_color' => $hover_text_color,
        'border_radius' => $border_radius,
        'font_size' => $font_size,
        'width_mode' => $width_mode,
        'custom_width' => $custom_width,
    ];
}

function onepaquc_get_button_styling()
{
    // Basic button classes
    $classes = "button single_add_to_cart_button direct-checkout-button opqcfw-btn wp-element-button";
    $style = "cursor:pointer;text-align: center;";
    $icon = '';
    $style_options = onepaquc_get_direct_checkout_style_options();
    $uses_custom_style = $style_options['button_style'] !== 'default';

    // Apply button style settings
    $button_style = $style_options['button_style'];

    if ($button_style === 'alt') {
        $classes .= " alt-style";
    }

    // Apply color settings if not using default style
    if ($uses_custom_style) {
        $bg_color = $style_options['bg_color'];
        $text_color = $style_options['text_color'];
        $style .= "background-color:{$bg_color};color:{$text_color};border-color:{$bg_color};";
        $style .= "border-radius:{$style_options['border_radius']}px;font-size:{$style_options['font_size']}px;";

        if ($style_options['width_mode'] === 'full') {
            $style .= "width:100%;display:block;box-sizing:border-box;";
        } elseif ($style_options['width_mode'] === 'custom') {
            $style .= "width:{$style_options['custom_width']}px;max-width:100%;display:inline-block;box-sizing:border-box;";
        }
    }

    // Handle button icon
    $icon_type = get_option('rmenu_wc_checkout_icon', 'none');
    $icon_position = get_option('rmenu_wc_checkout_icon_position', 'left');

    if ($uses_custom_style && $icon_type !== 'none') {
        // Define icon HTML based on type
        switch ($icon_type) {
            case 'cart':
                $icon_content = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#ffffff" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rmenu-icon"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>';
                break;
            case 'checkout':
                $icon_content = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="#fff" xml:space="preserve" width="16" height="16"><path d="M7.436 10.207a.507.507 0 0 0 .556.452.507.507 0 0 0 .452-.556L8.21 7.841a.507.507 0 0 0-1.008.104zm3.172.452q.027.003.053.003a.507.507 0 0 0 .504-.455l.234-2.262a.507.507 0 0 0-.452-.556.507.507 0 0 0-.556.452l-.234 2.262a.507.507 0 0 0 .452.556m-3.926 2.203c-.865 0-1.569.704-1.569 1.569S5.818 16 6.683 16s1.569-.704 1.569-1.569-.704-1.569-1.569-1.569m0 2.124a.556.556 0 1 1 .002-1.112.556.556 0 0 1-.002 1.112m5.234-2.124c-.865 0-1.569.704-1.569 1.569S11.052 16 11.917 16s1.569-.704 1.569-1.569-.704-1.569-1.569-1.569m0 2.124a.556.556 0 1 1 .002-1.112.556.556 0 0 1-.002 1.112"/><path d="M14.948 5.698a.5.5 0 0 0-.401-.197H4.445L4.021 3.87a.51.51 0 0 0-.491-.379H1.453a.507.507 0 0 0 0 1.014h1.685l.42 1.617.008.03 1.564 6.016a.51.51 0 0 0 .491.379h7.357a.51.51 0 0 0 .491-.379l1.569-6.031a.5.5 0 0 0-.09-.437m-2.361 5.834H6.013L4.708 6.516h9.183zm-5.383-8.88h2.968l-.775.775a.507.507 0 0 0 .358.865.5.5 0 0 0 .358-.148l1.64-1.64a.507.507 0 0 0 0-.717L10.112.148a.507.507 0 0 0-.717.717l.775.775H7.203a.507.507 0 0 0 0 1.014"/></svg>';
                break;
            case 'arrow':
                $icon_content = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#ffffff" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rmenu-icon"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';
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

    return [
        'classes' => $classes,
        'style' => $style,
        'icon' => $icon,
    ];
}

/**
 * Add button-specific CSS based on settings
 */
function onepaquc_add_button_css()
{
    $style_options = onepaquc_get_direct_checkout_style_options();

    if (!wp_style_is('rmenu-cart-style', 'enqueued')) {
        return;
    }

    $css = '.opqcfw-btn{transition:all 0.3s ease;text-decoration:none;}';
    $css .= '.opqcfw-btn.alt-style{border-radius:4px;letter-spacing:1px;font-weight:600;}';

    if ($style_options['button_style'] !== 'default') {
        $hover_bg_color = sanitize_hex_color($style_options['hover_bg_color']) ?: '#000000';
        $hover_text_color = sanitize_hex_color($style_options['hover_text_color']) ?: '#ffffff';
        $css .= '.direct-checkout-button:hover,.direct-checkout-button:focus,.direct-checkout-button:active{';
        $css .= 'background-color:' . $hover_bg_color . ' !important;';
        $css .= 'color:' . $hover_text_color . ' !important;';
        $css .= 'border-color:' . $hover_bg_color . ' !important;';
        $css .= '}';
    }

    $css .= '.opqcfw-btn .rmenu-icon{display:inline-block;vertical-align:middle;}';
    $css .= '.opqcfw-btn.icon-position-left .rmenu-icon{margin-right:8px;}';
    $css .= '.opqcfw-btn.icon-position-right .rmenu-icon{margin-left:8px;}';
    $css .= '.opqcfw-btn.icon-position-top .rmenu-icon{display:block;margin:0 auto 5px;}';
    $css .= '.opqcfw-btn.icon-position-bottom .rmenu-icon{display:block;margin:5px auto 0;}';
    $css .= '.opqcfw-btn.icon-position-top,.opqcfw-btn.icon-position-bottom{text-align:center;}';
    $arrow_color = onepaquc_sanitize_hex_color(get_option('rmenu_wc_checkout_color', '#000'), '#000000');
    $css .= '.plugincy-quick-checkout.overlay_thumbnail a .onepaquc-button-text:before,.plugincy-quick-checkout.overlay_thumbnail_hover a .onepaquc-button-text:before{content:"";width:0;height:0;border-left:5px solid transparent;border-right:5px solid transparent;border-bottom:10px solid ' . $arrow_color . ';}';

    wp_add_inline_style('rmenu-cart-style', $css);
}

add_action('wp_enqueue_scripts', 'onepaquc_add_button_css', 45);

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
// Track if button was already rendered to avoid duplicates
global $onepaquc_button_rendered;
$onepaquc_button_rendered = false;

function onepaquc_add_checkout_button()
{
    global $onepaquc_button_rendered;

    if ($onepaquc_button_rendered) {
        return;
    }
    if (onepaquc_render_checkout_button()) {
        $onepaquc_button_rendered = true;
    }
}

function onepaquc_add_checkout_button_fallback()
{
    global $onepaquc_button_rendered;

    // Only render if primary hook didn't work
    if ($onepaquc_button_rendered) {
        return;
    }

    if (onepaquc_render_checkout_button()) {
        $onepaquc_button_rendered = true;
    }
}

function onepaquc_render_checkout_button(): bool
{
    global $product, $onepaquc_onepaquc_allowed_tags;

    if (!is_product() || !$product) {
        return false;
    }

    if (!onepaquc_should_display_button($product)) {
        return false;
    }

    $product_id = $product->get_id();
    $product_type = $product->get_type();
    $product_title = $product->get_name();
    $button_styling = onepaquc_get_button_styling();
    $icon = $button_styling["icon"];

    $icon_content = isset($icon['content']) ? $icon['content'] : '';
    $icon_position = isset($icon['position']) ? $icon['position'] : 'left';

    // Button text
    $button_text = esc_html(onepaquc_get_text_option('txt-direct-checkout', __('Buy Now', 'one-page-quick-checkout-for-woocommerce')));

    // Prepare icon HTML
    if (!empty($icon_content)) {
        $icon_html = '<span class="onepaquc-icon">' . $icon_content . '</span>';
    } else {
        $icon_html = '';
    }

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

    $one_page_checkout = get_post_meta($product_id, '_one_page_checkout', true);
    $onpage_checkout_cart_add = get_option('onpage_checkout_cart_add', "1");

    // if ($one_page_checkout === 'yes' && $onpage_checkout_cart_add === "1") {
    //     // Remove 'single_add_to_cart_button' and 'direct-checkout-button' from classes
    //     $button_classes = preg_replace('/\b(direct-checkout-button)\b/', '', $button_styling['classes']);
    //     $button_classes = trim(preg_replace('/\s+/', ' ', $button_classes));
    //     echo '<a class="' . esc_attr($button_classes) . ' onepaquc-checkout-btn" style="' . esc_attr($button_styling['style']) . '">' . wp_kses($button_inner, $onepaquc_onepaquc_allowed_tags) . '</a>';
    // } else {
    // Output the button with fallback identifier
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- onepaquc_checkout_btn_external_data_attr() returns a pre-escaped attribute fragment for external products.
    echo '<a class="' . esc_attr($button_styling['classes']) . ' onepaquc-checkout-btn" data-product-id="' . esc_attr($product_id) . '" data-product-type="' . esc_attr($product_type) . '" data-title="' . esc_attr($product_title) . '"' . onepaquc_checkout_btn_external_data_attr($product) . ' style="' . esc_attr($button_styling['style']) . '">' . wp_kses($button_inner, $onepaquc_onepaquc_allowed_tags) . '</a>';
    // }

    // Optional: a tiny marker helps debugging/JS checks, doesn’t affect layout
    echo '<span class="onepaquc-rendered-marker" hidden></span>';

    return true;
}

// Add JavaScript fallback for themes that don't support any of the hooks
function onepaquc_add_js_fallback()
{
    if (!is_product()) {
        return;
    }

    global $onepaquc_onepaquc_allowed_tags;

    global $product;
    if (!$product || !onepaquc_should_display_button($product)) {
        return;
    }

    $product_id = $product->get_id();
    $product_type = $product->get_type();
    $product_title = $product->get_name();
    $button_styling = onepaquc_get_button_styling();
    $icon = $button_styling["icon"];

    $icon_content = isset($icon['content']) ? $icon['content'] : '';
    $icon_position = isset($icon['position']) ? $icon['position'] : 'left';
    $button_text = esc_html(onepaquc_get_text_option('txt-direct-checkout', __('Buy Now', 'one-page-quick-checkout-for-woocommerce')));

    // Prepare icon HTML
    if (!empty($icon_content)) {
        $icon_html = '<span class="onepaquc-icon">' . $icon_content . '</span>';
    } else {
        $icon_html = '';
    }

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

    if (!wp_script_is('rmenu-cart-script', 'enqueued')) {
        return;
    }

    $button_html = '<a class="' . esc_attr($button_styling['classes']) . ' onepaquc-checkout-btn" data-product-id="' . esc_attr($product_id) . '" data-product-type="' . esc_attr($product_type) . '" data-title="' . esc_attr($product_title) . '"' . onepaquc_checkout_btn_external_data_attr($product) . ' style="' . esc_attr($button_styling['style']) . '">' . wp_kses($button_inner, $onepaquc_onepaquc_allowed_tags) . '</a>';
    $config = array(
        'buttonHtml' => $button_html,
    );

    $inline_script = 'jQuery(function($){'
        . 'var config=' . wp_json_encode($config) . ';'
        . 'window.setTimeout(function(){'
        . 'if($(".onepaquc-checkout-btn").length>0){return;}'
        . 'var selectors=[".quantity",".single_add_to_cart_button","button[name=\"add-to-cart\"]","input[name=\"add-to-cart\"]",".add_to_cart_button"];'
        . 'var buttonInserted=false;'
        . 'for(var i=0;i<selectors.length&&!buttonInserted;i++){var $target=$(selectors[i]).first();if($target.length>0){$target.after(config.buttonHtml);buttonInserted=true;break;}}'
        . 'if(!buttonInserted){var fallbackSelectors=["form.cart",".summary",".product-summary",".single-product-summary"];for(var j=0;j<fallbackSelectors.length&&!buttonInserted;j++){var $fallback=$(fallbackSelectors[j]).first();if($fallback.length>0){$fallback.append("<div class=\"onepaquc-button-wrapper\" style=\"margin-top: 15px;\">"+config.buttonHtml+"</div>");buttonInserted=true;break;}}}'
        . 'if(!buttonInserted&&window.console&&window.console.log){window.console.log("OnePaqUC: Could not find suitable location to insert button");}'
        . '},500);'
        . '});';

    wp_add_inline_script('rmenu-cart-script', $inline_script);
}
if (get_option('rmenu_add_direct_checkout_button', 1)) {
    add_action('wp_enqueue_scripts', 'onepaquc_add_js_fallback', 55);
}

// Reset early on page bootstrap, not in <head>
add_action('wp', function () {
    $GLOBALS['onepaquc_button_rendered'] = false;
});


// Function to position the checkout button on single product page
function onepaquc_modify_add_to_cart_button()
{
    $position = get_option("rmenu_wc_direct_checkout_single_position", "after_add_to_cart");

    switch ($position) {
        // case 'before_add_to_cart':
        //     add_action('woocommerce_before_add_to_cart_button', 'onepaquc_add_checkout_button');
        //     break;

        case 'after_add_to_cart':
        case 'replace_add_to_cart':
        case 'bottom_add_to_cart':
        case 'before_add_to_cart':
            // Primary hook - most common
            add_action('woocommerce_after_add_to_cart_button', 'onepaquc_add_checkout_button', 10);

            // Fallback hooks for different themes
            add_action('wp', function () {
                add_action('woocommerce_single_product_summary', 'onepaquc_add_checkout_button_fallback', 35);
                add_action('woocommerce_after_single_product_summary', 'onepaquc_add_checkout_button_fallback', 5);
            });

            break;
    }
}

// Apply the checkout button modifications if enabled
if (get_option('rmenu_add_direct_checkout_button', 1)) {
    add_action('wp_enqueue_scripts', 'onepaquc_position_wise_css', 50);
    add_action('woocommerce_before_single_product', 'onepaquc_modify_add_to_cart_button', 0);
}

if (get_option('rmenu_add_to_cart_catalog_display') == "hide") {
    add_action('wp_enqueue_scripts', 'onepaquc_position_wise_css', 50);
}

// Function to hide the original add to cart button when in replace mode
function onepaquc_position_wise_css()
{

    $position = get_option("rmenu_wc_direct_checkout_single_position", "after_add_to_cart");

    if (!is_singular('product')) {
        $position = get_option("rmenu_wc_direct_checkout_position", "after_add_to_cart");
    }

    if (!wp_style_is('rmenu-cart-style', 'enqueued')) {
        return;
    }

    if ($position === "replace_add_to_cart" || (get_option('rmenu_enable_custom_add_to_cart', 0) && get_option('rmenu_add_to_cart_catalog_display') == "hide")) {
        wp_add_inline_style(
            'rmenu-cart-style',
            'button.single_add_to_cart_button,a.button.product_type_simple.add_to_cart_button{display:none !important;}.single .direct-checkout-button,.single .opqcfw-btn{margin:0 0 1rem !important;}'
        );
    } elseif ($position === "before_add_to_cart") {
        wp_add_inline_style('rmenu-cart-style', 'button.single_add_to_cart_button{order:3;}');

        if (wp_script_is('rmenu-cart-script', 'enqueued')) {
            wp_add_inline_script(
                'rmenu-cart-script',
                'document.addEventListener("DOMContentLoaded",function(){var quantityElement=document.querySelector(".quantity");if(quantityElement&&quantityElement.parentElement){quantityElement.parentElement.style.display="flex";}});'
            );
        }
    } elseif ($position === "bottom_add_to_cart") {
        wp_add_inline_style('rmenu-cart-style', 'a.button.opqcfw-btn{width:100% !important;margin:1rem 0 !important;}');
    }
}

// Function to add checkout button to product loops (listings)
function onepaquc_add_checkout_button_to_add_to_cart_shortcode($link, $product)
{
    global $onepaquc_buy_now_button_in_loop;
    if (!$product instanceof WC_Product) {
        return $link;
    }

    $product_id = $product->get_id();

    if (!onepaquc_should_display_button($product)) {
        return $link;
    }

    

    // Create a unique context key based on current loop iteration
    // This allows the same product to appear in different loops
    static $loop_counter = 0;
    $loop_counter++;
    $context_key = $product_id . '_' . $loop_counter;

    // Check if button was already added in THIS specific context
    if (isset($onepaquc_buy_now_button_in_loop[$context_key])) {
        return $link;
    }

    $onepaquc_buy_now_button_in_loop[$context_key] = true;

    $position = get_option("rmenu_wc_direct_checkout_position", "after_add_to_cart");
    $product_id = $product->get_id();
    $product_type = $product->get_type();
    $product_title = $product->get_name(); // Get the product title
    $button_styling = onepaquc_get_button_styling();
    $icon = $button_styling["icon"];

    $icon_content = isset($icon['content']) ? $icon['content'] : '';
    $icon_position = isset($icon['position']) ? $icon['position'] : 'left';

    // Button text
    $button_text = esc_html(onepaquc_get_text_option('txt-direct-checkout', __('Buy Now', 'one-page-quick-checkout-for-woocommerce')));

    // Prepare icon HTML
    if (!empty($icon_content)) {
        $icon_html = '<span class="onepaquc-icon">' . $icon_content . '</span>';
    } else {
        $icon_html = '';
    }

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
    $checkout_button = '<a class="' . esc_attr($button_styling['classes']) . '" data-product-id="' . esc_attr($product_id) . '" data-product-type="' . esc_attr($product_type) . '" data-title="' . esc_attr($product_title) . '"' . onepaquc_checkout_btn_external_data_attr($product) . ' style="' . esc_attr($button_styling['style']) . '">' . $button_inner . '</a>';
    // Apply the position setting
    switch ($position) {
        case 'before_add_to_cart':
            return $checkout_button . ' ' . $link;

        case 'after_add_to_cart':
        case 'bottom_add_to_cart':
            return $link . ' ' . $checkout_button;

        case 'replace_add_to_cart':
            return $checkout_button;
    }

    return $link;
}

/**
 * Render Quick Checkout button after loop item (archives) as a fallback.
 * Runs only if the add_to_cart_link filter didn't already output the button.
 */
function onepaquc_add_checkout_button_after_loop_item()
{
    global $product, $onepaquc_buy_now_button_in_loop, $onepaquc_onepaquc_allowed_tags;

    // Must have a product and pass display rules.
    if (! $product instanceof WC_Product || ! onepaquc_should_display_button($product)) {
        return;
    }

    $product_id    = $product->get_id();

    // Create a unique context key for this specific render
    static $loop_counter = 0;
    $loop_counter++;
    $context_key = $product_id . '_' . $loop_counter;

    // Check if already rendered in this context
    if (isset($onepaquc_buy_now_button_in_loop[$context_key])) {
        return;
    }

    $onepaquc_buy_now_button_in_loop[$context_key] = true;

    $product_type  = $product->get_type();
    $button_styles = onepaquc_get_button_styling();

    // Button text
    $button_text = esc_html(onepaquc_get_text_option('txt-direct-checkout', __('Buy Now', 'one-page-quick-checkout-for-woocommerce')));

    // Icon handling
    $icon         = isset($button_styles['icon']) ? $button_styles['icon'] : [];
    $icon_content = isset($icon['content']) ? $icon['content'] : '';
    $icon_pos     = isset($icon['position']) ? $icon['position'] : 'left';

    $icon_html = $icon_content ? '<span class="onepaquc-icon">' . $icon_content . '</span>' : '';

    switch ($icon_pos) {
        case 'right':
            $inner = $button_text . ' ' . $icon_html;
            break;
        case 'top':
            $inner = $icon_html . '<br>' . $button_text;
            break;
        case 'bottom':
            $inner = $button_text . '<br>' . $icon_html;
            break;
        case 'left':
        default:
            $inner = $icon_html . ' ' . $button_text;
            break;
    }

    // Minimal safe default for KSES if not set elsewhere
    if (empty($onepaquc_onepaquc_allowed_tags)) {
        $onepaquc_onepaquc_allowed_tags = wp_kses_allowed_html('post');
    }

    // Output the button just after the loop item’s default content.
    echo '<div class="onepaquc-loop-btn-wrap" style="margin-top:8px;">';
    echo '<a class="' . esc_attr($button_styles['classes']) . ' onepaquc-checkout-btn"'
        . ' data-product-id="' . esc_attr($product_id) . '"'
        . ' data-product-type="' . esc_attr($product_type) . '"'
        . ' data-title="' . esc_attr($product->get_name()) . '"'
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- onepaquc_checkout_btn_external_data_attr() returns a pre-escaped attribute fragment for external products.
        . onepaquc_checkout_btn_external_data_attr($product)
        . ' style="' . esc_attr($button_styles['style']) . '">'
        . wp_kses($inner, $onepaquc_onepaquc_allowed_tags)
        . '</a>';
    echo '</div>';
}


// Apply the checkout button to product loops if enabled
if (get_option('rmenu_add_direct_checkout_button', 1) && (get_option("rmenu_wc_direct_checkout_position", "after_add_to_cart") === "after_add_to_cart" || get_option("rmenu_wc_direct_checkout_position", "after_add_to_cart") === "bottom_add_to_cart" || get_option("rmenu_wc_direct_checkout_position", "after_add_to_cart") === "before_add_to_cart" || get_option("rmenu_wc_direct_checkout_position", "after_add_to_cart") === "replace_add_to_cart")) {
    global $onepaquc_buy_now_button_in_loop;
    $onepaquc_buy_now_button_in_loop = array();
    add_filter('woocommerce_loop_add_to_cart_link', 'onepaquc_add_checkout_button_to_add_to_cart_shortcode', 100, 2);
    add_action('woocommerce_after_shop_loop_item', 'onepaquc_add_checkout_button_after_loop_item', 111);
} else {
    new onepaquc_add_checkout_button_on_archive();
}

class onepaquc_add_checkout_button_on_archive
{
    private $is_btn_add_hook_works;
    /**
     * Constructor
     */
    public function __construct()
    {
        if (get_option('rmenu_add_direct_checkout_button', 1)) {
            $this->add_checkout_button_on_archive();
            $this->is_btn_add_hook_works = false;
        }
    }

    function add_checkout_button_on_archive()
    {
        $position = get_option("rmenu_wc_direct_checkout_position", "overlay_thumbnail_hover");

        switch ($position) {
            case 'overlay_thumbnail':
            case 'overlay_thumbnail_hover':
            case 'after_product':
                break;
            case 'after_product_title':
                add_action('woocommerce_shop_loop_item_title', array($this, 'onepaquc_add_checkout_button'), 15);
                break;
            case 'before_product_title':
                add_action('woocommerce_shop_loop_item_title', array($this, 'onepaquc_add_checkout_button'), 9);
                break;
            case 'after_product_excerpt':
                add_action('woocommerce_after_shop_loop_item_title', array($this, 'onepaquc_add_checkout_button'), 9);
                break;
            case 'after_product_rating':
                add_action('woocommerce_after_shop_loop_item_title', array($this, 'onepaquc_add_checkout_button'), 7);
                break;
            case 'after_product_price':
                add_action('woocommerce_after_shop_loop_item_title', array($this, 'onepaquc_add_checkout_button'), 15);
                break;
        }

        $fallback_positions = array(
            'overlay_thumbnail',
            'overlay_thumbnail_hover',
            'after_product',
            'after_product_title',
            'before_product_title',
            'after_product_excerpt',
            'after_product_rating',
            'after_product_price',
        );
        if (in_array($position, $fallback_positions, true)) {
            add_action('wp_enqueue_scripts', array($this, 'display_overlay_quick_checkout_button_footer'), 30);
        }
    }

    public function display_overlay_quick_checkout_button_footer()
    {
        if ($this->is_btn_add_hook_works) {
            return;
        }

        global $onepaquc_onepaquc_allowed_tags;

        // if isn't wc archive pages then return
        if (is_singular('product')) {
            return;
        }
        $button_contents = $this->button_contents();

        // Check if current page is allowed
        $allowed_pages = onepaquc_normalize_key_list(
            get_option('rmenu_show_quick_checkout_by_page', ['shop-page', 'category-archives', "tag-archives", 'search', "featured-products", "on-sale", "recent", "widgets", "shortcodes"]),
            ['shop-page', 'category-archives', "tag-archives", 'search', "featured-products", "on-sale", "recent", "widgets", "shortcodes"]
        );
        $display = false;

        if (in_array('shop-page', $allowed_pages, true) && is_shop()) {
            $display = true;
        } elseif (in_array('category-archives', $allowed_pages, true) && (is_product_category() || (is_product_taxonomy() && !is_product_tag()))) {
            $display = true;
        } elseif (in_array('tag-archives', $allowed_pages, true) && is_product_tag()) {
            $display = true;
        } elseif (in_array('search', $allowed_pages, true) && is_search()) {
            $display = true;
        } elseif (in_array('featured-products', $allowed_pages, true) && wc_get_loop_prop('is_featured')) {
            $display = true;
        } elseif (in_array('on-sale', $allowed_pages, true) && wc_get_loop_prop('is_on_sale')) {
            $display = true;
        } elseif (in_array('recent', $allowed_pages, true) && wc_get_loop_prop('is_recent')) {
            $display = true;
        } elseif (in_array('shortcodes', $allowed_pages, true) && is_singular()) {
            $display = true;
        }

        if (!$display) {
            return;
        }

        if (!wp_script_is('rmenu-cart-script', 'enqueued')) {
            return;
        }

        $config = array(
            'buttonPos'    => sanitize_key(is_scalar(get_option('rmenu_wc_direct_checkout_position', 'overlay_thumbnail_hover')) ? get_option('rmenu_wc_direct_checkout_position', 'overlay_thumbnail_hover') : 'overlay_thumbnail_hover'),
            'contents'     => wp_kses($button_contents['button_content'], $onepaquc_onepaquc_allowed_tags),
            'buttonClass'  => sanitize_text_field($button_contents['button_classes']),
            'buttonStyle'  => wp_strip_all_tags($button_contents['button_style']),
            'allowedTypes' => onepaquc_normalize_key_list(get_option('rmenu_show_quick_checkout_by_types', ['simple', 'variable', 'grouped', 'external']), ['simple', 'variable', 'grouped', 'external']),
        );

        $inline_script = 'jQuery(function($){'
            . 'var quickCheckoutConfig=' . wp_json_encode($config) . ';'
            . 'function initQuickCheckoutButtons(container){container=container||$(document);container.find(".product").each(function(){var $this=$(this);if($this.has(".plugincy-quick-checkout").length||$this.has(".outofstock").length||this.classList.contains("outofstock")||this.classList.contains("plugincy-not-purchaseable")){return;}var productIdMatch=($this.attr("class")||"").match(/post-(\d+)/);var productId=productIdMatch?productIdMatch[1]:($this.find(".button").length?$this.find(".button").data("product_id"):null);var productTypeMatch=($this.attr("class")||"").match(/product-type-(\w+)/);var productType=productTypeMatch?productTypeMatch[1]:($this.find(".button").length?$this.find(".button").data("product-type"):null);if(quickCheckoutConfig.allowedTypes.indexOf(productType)!==-1&&productId){var $wrap=$("<div/>",{"class":"plugincy-quick-checkout "+quickCheckoutConfig.buttonPos}).css("text-align","center");var $button=$("<a/>",{"class":quickCheckoutConfig.buttonClass,"data-product-id":productId,"data-product-type":productType,"data-title":""}).attr("style",quickCheckoutConfig.buttonStyle).html(quickCheckoutConfig.contents);$wrap.append($button);$this.append($wrap);}});cleanupOrphanedButtons();}'
            . 'function cleanupOrphanedButtons(){$(".plugincy-quick-checkout").each(function(){if(!$(this).closest(".product").length){$(this).remove();}});}'
            . 'function refreshQuickCheckoutButtons(container){container=container||$(document);container.find(".plugincy-quick-checkout").remove();initQuickCheckoutButtons(container);}'
            . 'initQuickCheckoutButtons();'
            . '$(document).ajaxComplete(function(){window.setTimeout(function(){initQuickCheckoutButtons();},100);});'
            . '$("body").on("wc_fragments_loaded wc_fragments_refreshed",function(){initQuickCheckoutButtons();});'
            . '$("body").on("post-load",function(event,data){if(data&&data.length){initQuickCheckoutButtons($(data));}});'
            . 'window.initQuickCheckoutButtons=initQuickCheckoutButtons;window.refreshQuickCheckoutButtons=refreshQuickCheckoutButtons;window.cleanupOrphanedButtons=cleanupOrphanedButtons;'
            . '});';

        wp_add_inline_script('rmenu-cart-script', $inline_script);
    }

    public function button_contents()
    {
        $position = get_option("rmenu_wc_direct_checkout_position", "overlay_thumbnail_hover");
        $button_styling = onepaquc_get_button_styling();
        $icon = $button_styling["icon"];

        $icon_content = isset($icon['content']) ? $icon['content'] : '';
        if ($icon_content == '' && ($position === "overlay_thumbnail" || $position === "overlay_thumbnail_hover" || $position === "after_product")) {
            $icon_content = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#ffffff" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rmenu-icon"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>';
        }
        $icon_position = isset($icon['position']) ? $icon['position'] : 'left';

        // Button text
        $button_text = '<span class="onepaquc-button-text" style="' . esc_attr($button_styling['style']) . '">' . esc_html(onepaquc_get_text_option('txt-direct-checkout', __('Buy Now', 'one-page-quick-checkout-for-woocommerce'))) . '</span>';

        // Prepare icon HTML
        if (!empty($icon_content)) {
            $icon_html = '<span class="onepaquc-icon">' . $icon_content . '</span>';
        } else {
            $icon_html = '';
        }

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
        // $checkout_button = '<a class="' . esc_attr($button_styling['classes']) . '" data-product-id="' . esc_attr($product_id) . '" data-product-type="' . esc_attr($product_type) . '" data-title="' . esc_html($product_title) . '" style="' . esc_attr($button_styling['style']) . '">' . $button_inner . '</a>';

        return [
            "button_classes" => $button_styling['classes'],
            "button_style" => $button_styling['style'],
            "button_content" => $button_inner
        ];
    }


    public function onepaquc_add_checkout_button()
    {
        global $product;
        global $onepaquc_onepaquc_allowed_tags;

        if (!onepaquc_should_display_button($product)) {
            return;
        }

        static $displayed_products = array();

        // Use product ID to track if we already displayed button for this product
        $product_id = $product->get_id();

        if (isset($displayed_products[$product_id])) {
            return; // Already displayed for this product
        }

        // Mark as displayed
        $displayed_products[$product_id] = true;

        $this->is_btn_add_hook_works = true;

        $product_type = $product->get_type();
        $product_title = $product->get_name(); // Get the product title
        $button_styling = onepaquc_get_button_styling();
        $icon = $button_styling["icon"];

        $icon_content = isset($icon['content']) ? $icon['content'] : '';
        $icon_position = isset($icon['position']) ? $icon['position'] : 'left';

        // Button text
        $button_text = esc_html(onepaquc_get_text_option('txt-direct-checkout', __('Buy Now', 'one-page-quick-checkout-for-woocommerce')));

        // Prepare icon HTML
        if (!empty($icon_content)) {
            $icon_html = '<span class="onepaquc-icon">' . $icon_content . '</span>';
        } else {
            $icon_html = '';
        }

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

        $one_page_checkout = get_post_meta($product_id, '_one_page_checkout', true);
        $onpage_checkout_cart_add = get_option('onpage_checkout_cart_add', "1");

        // if ($one_page_checkout === 'yes' && $onpage_checkout_cart_add === "1") {
        //     // Remove 'single_add_to_cart_button' and 'direct-checkout-button' from classes
        //     $button_classes = preg_replace('/\b(direct-checkout-button)\b/', '', $button_styling['classes']);
        //     $button_classes = trim(preg_replace('/\s+/', ' ', $button_classes));
        //     echo '<a class="' . esc_attr($button_classes) . '" style="' . esc_attr($button_styling['style']) . '">' . wp_kses($button_inner, $onepaquc_onepaquc_allowed_tags) . '</a>';
        // } else {
        // Output the button
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- onepaquc_checkout_btn_external_data_attr() returns a pre-escaped attribute fragment for external products.
        echo '<a class="' . esc_attr($button_styling['classes']) . '" data-product-id="' . esc_attr($product_id) . '" data-product-type="' . esc_attr($product_type) . '" data-title="' . esc_attr($product_title) . '"' . onepaquc_checkout_btn_external_data_attr($product) . ' style="' . esc_attr($button_styling['style']) . '">' . wp_kses($button_inner, $onepaquc_onepaquc_allowed_tags) . '</a>';
        // }
    }
}





















// === Shortcode: [onepaquc_button] ==========================================
// Usage examples:
// [onepaquc_button]                           ; auto-detect product on single page/loops
// [onepaquc_button product_id="123"]          ; specific product
// [onepaquc_button product_id="123" variation_id="456" qty="2"]
// [onepaquc_button product_id="123" detect_variation="1"] ; pick default/first in-stock variation
// [onepaquc_button text="Quick Buy" icon="cart" icon_position="right"]
// [onepaquc_button class="my-btn" style="border-radius:8px"]

add_action('init', function () {
    add_shortcode('onepaquc_button', 'onepaquc_button_shortcode_handler');
});

/**
 * Shortcode handler for [onepaquc_button]
 *
 * @param array $atts
 * @return string
 */
function onepaquc_button_shortcode_handler($atts = [])
{
    $atts = is_array($atts) ? $atts : array();

    // Sensible defaults
    $atts = shortcode_atts([
        // Product/variation
        'product_id'       => '',       // explicit product id
        'variation_id'     => '',       // explicit variation id
        'detect_product'   => '1',      // try to auto-detect product from context if not given
        'detect_variation' => '0',      // for variable products, try to pick default/first in-stock
        // UI
        'text'             => '',       // override button text
        'qty'              => '1',
        'icon'             => '',       // 'none'|'cart'|'checkout'|'arrow' (overrides plugin option)
        'icon_position'    => '',       // 'left'|'right'|'top'|'bottom' (overrides plugin option)
        'class'            => '',       // extra classes
        'style'            => '',       // extra inline styles
        // Behavior
        'show_for'         => '',       // comma list of allowed types to force show: simple,variable,external,grouped
        'force'            => '0',      // '1' to bypass onepaquc_should_display_button product/page checks
    ], $atts, 'onepaquc_button');

    // Resolve product
    $resolved_product = null;

    // 1) Explicit ID
    if (!empty($atts['product_id']) && is_scalar($atts['product_id']) && function_exists('wc_get_product')) {
        $resolved_product = wc_get_product(absint($atts['product_id']));
    }

    // 2) Contextual auto-detect (single product, loops, etc.)
    if (!$resolved_product && isset($atts['detect_product']) && is_scalar($atts['detect_product']) && '1' === (string) $atts['detect_product']) {
        global $product;
        if ($product instanceof WC_Product) {
            $resolved_product = $product;
        } elseif (is_singular('product')) {
            $pid = get_the_ID();
            $resolved_product = wc_get_product($pid);
        }
    }

    // 3) Last fallback: bail if still no product
    if (!$resolved_product instanceof WC_Product) {
        return '';
    }

    $product = $resolved_product;

    // Basic checks unless forced
    if ((!isset($atts['force']) || !is_scalar($atts['force']) || '1' !== (string) $atts['force']) && !onepaquc_should_display_button($product)) {
        return '';
    }

    // Optional show_for filter
    if (!empty($atts['show_for']) && is_scalar($atts['show_for'])) {
        $types = onepaquc_normalize_key_list(explode(',', strtolower((string) $atts['show_for'])));
        if (!in_array($product->get_type(), $types, true)) {
            return '';
        }
    }

    $product_id   = $product->get_id();
    $product_type = $product->get_type();

    // Resolve variation if requested/provided
    $variation_id = '';
    if (!empty($atts['variation_id']) && is_scalar($atts['variation_id'])) {
        $variation_id = absint($atts['variation_id']);
    } elseif ($product_type === 'variable' && isset($atts['detect_variation']) && is_scalar($atts['detect_variation']) && '1' === (string) $atts['detect_variation']) {
        $variation_id = onepaquc_pick_variation_id($product);
    }
    if ($variation_id) {
        $variation_product = wc_get_product($variation_id);
        if (!$variation_product instanceof WC_Product_Variation || $variation_product->get_parent_id() !== $product_id || !$variation_product->is_purchasable()) {
            return '';
        }
    }

    // Styling & icon from plugin settings (with shortcode overrides)
    $styling = onepaquc_get_button_styling();

    // Override icon type/position via shortcode if provided
    if (!empty($atts['icon']) && is_scalar($atts['icon'])) {
        $valid_icons = ['none', 'cart', 'checkout', 'arrow'];
        $icon_type = in_array($atts['icon'], $valid_icons, true) ? $atts['icon'] : 'none';
        if ($icon_type === 'none') {
            $styling['icon'] = [];
        } else {
            // Minimal SVG set (same as plugin) — re-use the switch from onepaquc_get_button_styling
            switch ($icon_type) {
                case 'cart':
                    $icon_content = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#ffffff" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rmenu-icon"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>';
                    break;
                case 'checkout':
                    $icon_content = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#ffffff" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rmenu-icon"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>';
                    break;
                case 'arrow':
                    $icon_content = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#ffffff" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rmenu-icon"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';
                    break;
            }
            $requested_position = !empty($atts['icon_position']) && is_scalar($atts['icon_position'])
                ? sanitize_key($atts['icon_position'])
                : sanitize_key(is_scalar(get_option('rmenu_wc_checkout_icon_position', 'left')) ? get_option('rmenu_wc_checkout_icon_position', 'left') : 'left');
            $requested_position = in_array($requested_position, array('left', 'right', 'top', 'bottom'), true) ? $requested_position : 'left';
            $styling['icon'] = [
                'content'  => isset($icon_content) ? $icon_content : '',
                'position' => $requested_position,
            ];
            $styling['classes'] .= ' icon-position-' . $styling['icon']['position'];
        }
    } elseif (!empty($atts['icon_position']) && is_scalar($atts['icon_position']) && !empty($styling['icon'])) {
        // Only adjust position if icon already present by settings
        $requested_position = sanitize_key($atts['icon_position']);
        $styling['icon']['position'] = in_array($requested_position, array('left', 'right', 'top', 'bottom'), true) ? $requested_position : 'left';
        $styling['classes'] .= ' icon-position-' . $styling['icon']['position'];
    }

    // Extra classes/styles from shortcode
    if (!empty($atts['class']) && is_scalar($atts['class'])) {
        $extra_classes = preg_split('/\s+/', (string) $atts['class']);
        $extra_classes = array_filter(array_map('sanitize_html_class', is_array($extra_classes) ? $extra_classes : array()));
        if ($extra_classes) {
            $styling['classes'] .= ' ' . implode(' ', $extra_classes);
        }
    }
    if (!empty($atts['style']) && is_scalar($atts['style'])) {
        $extra_style = function_exists('safecss_filter_attr') ? safecss_filter_attr((string) $atts['style']) : '';
        if ('' !== $extra_style) {
            $styling['style'] .= rtrim($extra_style, ';') . ';';
        }
    }

    // Button text (shortcode override > plugin option > fallback)
    $button_text = isset($atts['text']) && is_scalar($atts['text']) && '' !== (string) $atts['text']
        ? wp_kses_post((string) $atts['text'])
        : esc_html(onepaquc_get_text_option('txt-direct-checkout', __('Buy Now', 'one-page-quick-checkout-for-woocommerce')));

    // Compose inner HTML with icon
    $icon_html     = '';
    $icon_position = 'left';
    if (!empty($styling['icon']) && !empty($styling['icon']['content'])) {
        $icon_html     = '<span class="onepaquc-icon">' . $styling['icon']['content'] . '</span>';
        $icon_position = $styling['icon']['position'];
    }

    switch ($icon_position) {
        case 'right':
            $inner = $button_text . ' ' . $icon_html;
            break;
        case 'top':
            $inner = $icon_html . '<br>' . $button_text;
            break;
        case 'bottom':
            $inner = $button_text . '<br>' . $icon_html;
            break;
        case 'left':
        default:
            $inner = $icon_html . ' ' . $button_text;
            break;
    }

    // Quantity
    $qty = isset($atts['qty']) && is_scalar($atts['qty']) ? max(1, absint($atts['qty'])) : 1;

    // One-page checkout mode (mirror existing behavior)
    $one_page_checkout       = get_post_meta($product_id, '_one_page_checkout', true);
    $onpage_checkout_cart_add = get_option('onpage_checkout_cart_add', "1");

    // Build final button
    $classes = $styling['classes'];
    // If one-page checkout requires removing some classes (match existing behavior)
    // if ($one_page_checkout === 'yes' && $onpage_checkout_cart_add === "1") {
    //     $classes = preg_replace('/\b(direct-checkout-button)\b/', '', $classes);
    //     $classes = trim(preg_replace('/\s+/', ' ', $classes));
    // }

    $attrs = [
        'class'           => $classes . ' onepaquc-checkout-btn',
        'data-product-id' => $product_id,
        'data-product-type' => esc_attr($product_type),
        'data-quantity'   => $qty,
        'style'           => $styling['style'],
    ];

    // Include title and variation if present
    $attrs['data-title'] = esc_attr($product->get_name());
    if (!empty($variation_id)) {
        $attrs['data-variation-id'] = absint($variation_id);
    }
    if ('external' === $product_type) {
        $attrs['data-external-product-url'] = esc_url($product->get_product_url());
    }

    // Render
    $attr_html = '';
    foreach ($attrs as $k => $v) {
        $attr_html .= ' ' . $k . '="' . esc_attr($v) . '"';
    }

    // Allow the same sanitization rules used elsewhere in the plugin
    global $onepaquc_onepaquc_allowed_tags;
    if (empty($onepaquc_onepaquc_allowed_tags)) {
        // Minimal safe default if not set by theme/plugin
        $onepaquc_onepaquc_allowed_tags = wp_kses_allowed_html('post');
    }

    return '<a' . $attr_html . '>' . wp_kses($inner, $onepaquc_onepaquc_allowed_tags) . '</a>';
}

/**
 * Pick a variation id for a variable product:
 * 1) Exact match to default attributes and in-stock
 * 2) Otherwise first in-stock variation
 *
 * @param WC_Product_Variable $product
 * @return int|'' variation id or empty if none suitable
 */
function onepaquc_pick_variation_id($product)
{
    if (!($product instanceof WC_Product_Variable)) {
        return '';
    }

    $default_attrs = array_filter((array) $product->get_default_attributes());
    $variations    = onepaquc_get_validated_variations( $product );

    // Normalize default keys to 'attribute_{taxonomy}' style
    $normalized_defaults = [];
    foreach ($default_attrs as $key => $val) {
        $norm_key = strpos($key, 'attribute_') === 0 ? $key : 'attribute_' . $key;
        $normalized_defaults[$norm_key] = (string) $val;
    }

    // 1) Try default match first
    foreach ($variations as $var) {
        if (!is_array($var) || empty($var['variation_id'])) {
            continue;
        }
        if (!empty($var['is_in_stock']) && $var['is_in_stock']) {
            $attrs = isset($var['attributes']) ? (array) $var['attributes'] : [];
            // If no defaults set, accept the first in-stock
            if (empty($normalized_defaults)) {
                return (int) $var['variation_id'];
            }
            // Require all default attributes to match (when provided)
            $all_match = true;
            foreach ($normalized_defaults as $k => $v) {
                if (!isset($attrs[$k]) || $attrs[$k] === '' || ($v !== '' && strtolower((string) $attrs[$k]) !== strtolower((string) $v))) {
                    $all_match = false;
                    break;
                }
            }
            if ($all_match) {
                return (int) $var['variation_id'];
            }
        }
    }

    // 2) Otherwise return the first in-stock variation
    foreach ($variations as $var) {
        if (is_array($var) && !empty($var['variation_id']) && !empty($var['is_in_stock'])) {
            return (int) $var['variation_id'];
        }
    }

    return '';
}
