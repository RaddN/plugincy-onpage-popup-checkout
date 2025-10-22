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

    if ((is_array($product) || is_object($product)) && !$product->is_in_stock()) {
        return false;
    }

    // Check user login status
    $guest_checkout_enabled = get_option('rmenu_wc_checkout_guest_enabled', '1');
    if ($guest_checkout_enabled !== '1' && !is_user_logged_in()) {
        return false; // Only show for logged-in users when guest checkout is disabled
    }

    // Check product type
    $allowed_product_types = get_option('rmenu_show_quick_checkout_by_types', ["simple", "variable", "external"]);
    $product_type = (is_array($product) || is_object($product)) ? $product->get_type() : "simple";

    if (!in_array($product_type, $allowed_product_types)) {
        return false;
    }

    // Get allowed pages
    $allowed_pages = get_option('rmenu_show_quick_checkout_by_page', ["single", "related", "upsells", "shop-page", "category-archives", "tag-archives", "featured-products", "on-sale", "recent", "widgets", "shortcodes"]);

    // Check current page type

    if (is_shop() && in_array('shop-page', $allowed_pages)) {
        return true;
    }

    if (is_product_category() && in_array('category-archives', $allowed_pages)) {
        return true;
    }

    if (is_product_tag() && in_array('tag-archives', $allowed_pages)) {
        return true;
    }

    if (is_product() && in_array('single', $allowed_pages)) {
        return true;
    }

    if (is_cart() && in_array('cross-sells', $allowed_pages)) {
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
    if (isset($woocommerce_loop['name']) && $woocommerce_loop['name'] === 'sale_products' && in_array('on-sale', $allowed_pages)) {
        return true;
    }

    // Check for recent products
    if (isset($woocommerce_loop['name']) && $woocommerce_loop['name'] === 'recent_products' && in_array('recent', $allowed_pages)) {
        return true;
    }

    // Check for widgets and shortcodes
    $is_widget_or_shortcode = false;
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
    foreach ($backtrace as $trace) {
        if (isset($trace['function']) && (
            (strpos($trace['function'], 'widget') !== false && in_array('widgets', $allowed_pages)) ||
            (strpos($trace['function'], 'shortcode') !== false && in_array('shortcodes', $allowed_pages))
        ) && !is_shop() && !is_product_category() && !is_product_tag() && !is_product()) {
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
    $classes = "button single_add_to_cart_button direct-checkout-button opqcfw-btn wp-element-button has-small-font-size";
    $style = "cursor:pointer;text-align: center;";
    $icon = '';
    $additional_css = '';

    // Apply button style settings
    $button_style = get_option('rmenu_wc_checkout_style', 'default');

    if ($button_style === 'alt') {
        $classes .= " alt-style";
    }

    // Apply color settings if not using default style
    if ($button_style !== 'default') {
        $bg_color = get_option('rmenu_wc_checkout_color', '#000');
        $text_color = get_option('rmenu_wc_checkout_text_color', '#ffffff');
        $style .= "background-color:{$bg_color};color:{$text_color};border-color:{$bg_color};";
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

    global $onepaquc_allowed_tags;

    // Start output buffer for CSS
    ob_start();

    // Basic styles for the button
?>
    <style>
        /* Base styling for direct checkout button */
        .opqcfw-btn {
            transition: all 0.3s ease;
            text-decoration: none;
        }

        /* Alternative button style */
        .opqcfw-btn.alt-style {
            border-radius: 4px;
            letter-spacing: 1px;
            font-weight: 600;
        }

        /* Icon positioning styles */
        .opqcfw-btn .rmenu-icon {
            display: inline-block;
            vertical-align: middle;
        }

        .opqcfw-btn.icon-position-left .rmenu-icon {
            margin-right: 8px;
        }

        .opqcfw-btn.icon-position-right .rmenu-icon {
            margin-left: 8px;
        }

        .opqcfw-btn.icon-position-top .rmenu-icon {
            display: block;
            margin: 0 auto 5px;
        }

        .opqcfw-btn.icon-position-bottom .rmenu-icon {
            display: block;
            margin: 5px auto 0;
        }

        .opqcfw-btn.icon-position-top,
        .opqcfw-btn.icon-position-bottom {
            text-align: center;
        }

        <?php echo wp_kses($additional_css, array()); ?>
    </style>
<?php

    // Output the CSS
    echo wp_kses(ob_get_clean(), $onepaquc_allowed_tags);
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
    global $product, $onepaquc_allowed_tags;

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
    $button_text = esc_html(get_option('txt-direct-checkout') !== "" ? get_option('txt-direct-checkout', 'Buy Now') : "Buy Now");

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
    //     echo '<a class="' . esc_attr($button_classes) . ' onepaquc-checkout-btn" style="' . esc_attr($button_styling['style']) . '">' . wp_kses($button_inner, $onepaquc_allowed_tags) . '</a>';
    // } else {
        // Output the button with fallback identifier
        echo '<a class="' . esc_attr($button_styling['classes']) . ' onepaquc-checkout-btn" data-product-id="' . esc_attr($product_id) . '" data-product-type="' . esc_attr($product_type) . '" data-title="' . esc_html($product_title) . '" style="' . esc_attr($button_styling['style']) . '">' . wp_kses($button_inner, $onepaquc_allowed_tags) . '</a>';
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

    global $onepaquc_allowed_tags;

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
    $button_text = esc_html(get_option('txt-direct-checkout') !== "" ? get_option('txt-direct-checkout', 'Buy Now') : "Buy Now");

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

?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Wait a bit to ensure page is fully loaded
            setTimeout(function() {
                // Check if button already exists (from PHP hooks)
                if ($('.onepaquc-checkout-btn').length > 0) {
                    return; // Button already rendered by PHP
                }

                // Define button HTML
                var buttonHtml = '';
                // <?php //if ($one_page_checkout === 'yes' && $onpage_checkout_cart_add === "1"): ?>
                //     var buttonClasses = '<?php //echo esc_js(preg_replace('/\b(direct-checkout-button)\b/', '', trim(preg_replace('/\s+/', ' ', $button_styling['classes'])))); ?>';
                //     buttonHtml = '<a class="' + buttonClasses + ' onepaquc-checkout-btn" style="<?php //echo esc_js($button_styling['style']); ?>"><?php //echo wp_kses($button_inner, $onepaquc_allowed_tags); ?></a>';
                // <?php //else: ?>
                    buttonHtml = '<a class="<?php echo esc_js($button_styling['classes']); ?> onepaquc-checkout-btn" data-product-id="<?php echo esc_js($product_id); ?>" data-product-type="<?php echo esc_js($product_type); ?>" data-title="<?php echo esc_js($product_title); ?>" style="<?php echo esc_js($button_styling['style']); ?>"><?php echo wp_kses($button_inner, $onepaquc_allowed_tags); ?></a>';
                <?php //endif; ?>

                // Try multiple selectors to find the best place to insert the button
                var selectors = [
                    '.quantity', // Most common
                    '.single_add_to_cart_button',
                    'button[name="add-to-cart"]',
                    'input[name="add-to-cart"]',
                    '.add_to_cart_button'
                ];

                var buttonInserted = false;

                for (var i = 0; i < selectors.length && !buttonInserted; i++) {
                    var $target = $(selectors[i]).first();

                    if ($target.length > 0) {
                        // Insert after the target element
                        $target.after(buttonHtml);
                        buttonInserted = true;
                        break;
                    }
                }

                // Final fallback - append to product summary or form
                if (!buttonInserted) {
                    var fallbackSelectors = [
                        'form.cart',
                        '.summary',
                        '.product-summary',
                        '.single-product-summary'
                    ];

                    for (var j = 0; j < fallbackSelectors.length && !buttonInserted; j++) {
                        var $fallback = $(fallbackSelectors[j]).first();
                        if ($fallback.length > 0) {
                            $fallback.append('<div class="onepaquc-button-wrapper" style="margin-top: 15px;">' + buttonHtml + '</div>');
                            buttonInserted = true;
                            break;
                        }
                    }
                }

                if (!buttonInserted) {
                    console.log('OnePaqUC: Could not find suitable location to insert button');
                }

            }, 500); // 500ms delay to ensure DOM is ready
        });
    </script>
    <?php
}
if (get_option('rmenu_add_direct_checkout_button', 1)) {
    // Add the JavaScript fallback to wp_footer
    add_action('wp_footer', 'onepaquc_add_js_fallback');
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
    add_action('wp_footer', 'onepaquc_position_wise_css');
    add_action('woocommerce_before_single_product', 'onepaquc_modify_add_to_cart_button', 0);
}

if (get_option('rmenu_add_to_cart_catalog_display') == "hide") {
    add_action('wp_footer', 'onepaquc_position_wise_css');
}

// Function to hide the original add to cart button when in replace mode
function onepaquc_position_wise_css()
{

    global $product;

    if (!onepaquc_should_display_button($product)) {
        return;
    }

    $position = get_option("rmenu_wc_direct_checkout_single_position", "after_add_to_cart");

    if (!is_singular('product')) {
        $position = get_option("rmenu_wc_direct_checkout_position", "after_add_to_cart");
    }

    if ($position === "replace_add_to_cart" || (get_option('rmenu_enable_custom_add_to_cart', 0) && get_option('rmenu_add_to_cart_catalog_display') == "hide")) {
    ?>
        <style>
            button.single_add_to_cart_button,
            a.button.product_type_simple.add_to_cart_button {
                display: none !important;
            }

            .single .direct-checkout-button,
            .single .opqcfw-btn {
                margin: 0 0 1rem !important;

            }
        </style>
    <?php
    } else if ($position === "before_add_to_cart") { ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const quantityElement = document.querySelector('.quantity');
                if (quantityElement) {
                    const parent = quantityElement.parentElement;
                    parent.style.display = 'flex';
                }
            });
        </script>
        <style>
            button.single_add_to_cart_button {
                order: 3;
            }
        </style>
    <?php } else if ($position === "bottom_add_to_cart") { ?>
        <style>
            a.button.opqcfw-btn {
                width: 100% !important;
                margin: 1rem 0 !important;
            }
        </style>
    <?php
    } else if ($position === "after_add_to_cart") { ?>
        <style>

        </style>
    <?php
    }
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
    $product_title = $product->get_name(); // Get the product title
    $button_styling = onepaquc_get_button_styling();
    $icon = $button_styling["icon"];

    $icon_content = isset($icon['content']) ? $icon['content'] : '';
    $icon_position = isset($icon['position']) ? $icon['position'] : 'left';

    // Button text
    $button_text = esc_html(get_option('txt-direct-checkout') !== "" ? get_option('txt-direct-checkout', 'Buy Now') : "Buy Now");

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
    $checkout_button = '<a class="' . esc_attr($button_styling['classes']) . '" data-product-id="' . esc_attr($product_id) . '" data-product-type="' . esc_attr($product_type) . '" data-title="' . esc_html($product_title) . '" style="' . esc_attr($button_styling['style']) . '">' . $button_inner . '</a>';
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
}


// Apply the checkout button to product loops if enabled
if (get_option('rmenu_add_direct_checkout_button', 1) && (get_option("rmenu_wc_direct_checkout_position", "after_add_to_cart") === "after_add_to_cart" || get_option("rmenu_wc_direct_checkout_position", "after_add_to_cart") === "bottom_add_to_cart" || get_option("rmenu_wc_direct_checkout_position", "after_add_to_cart") === "before_add_to_cart" || get_option("rmenu_wc_direct_checkout_position", "after_add_to_cart") === "replace_add_to_cart")) {
    add_filter('woocommerce_loop_add_to_cart_link', 'onepaquc_add_checkout_button_to_add_to_cart_shortcode', 99, 2);
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
                if (!$this->is_btn_add_hook_works) {
                    add_action('wp_footer', array($this, 'display_overlay_quick_checkout_button_footer'), 25);
                }
                break;
            case 'after_product_title':
                add_action('woocommerce_shop_loop_item_title', array($this, 'onepaquc_add_checkout_button'), 15);
                if (!$this->is_btn_add_hook_works) {
                    add_action('wp_footer', array($this, 'display_overlay_quick_checkout_button_footer'), 25);
                }
                break;
            case 'before_product_title':
                add_action('woocommerce_shop_loop_item_title', array($this, 'onepaquc_add_checkout_button'), 9);
                if (!$this->is_btn_add_hook_works) {
                    add_action('wp_footer', array($this, 'display_overlay_quick_checkout_button_footer'), 25);
                }
                break;
            case 'after_product_excerpt':
                add_action('woocommerce_after_shop_loop_item_title', array($this, 'onepaquc_add_checkout_button'), 9);
                if (!$this->is_btn_add_hook_works) {
                    add_action('wp_footer', array($this, 'display_overlay_quick_checkout_button_footer'), 25);
                }
                break;
            case 'after_product_rating':
                add_action('woocommerce_after_shop_loop_item_title', array($this, 'onepaquc_add_checkout_button'), 7);
                if (!$this->is_btn_add_hook_works) {
                    add_action('wp_footer', array($this, 'display_overlay_quick_checkout_button_footer'), 25);
                }
                break;
            case 'after_product_price':
                add_action('woocommerce_after_shop_loop_item_title', array($this, 'onepaquc_add_checkout_button'), 15);
                if (!$this->is_btn_add_hook_works) {
                    add_action('wp_footer', array($this, 'display_overlay_quick_checkout_button_footer'), 25);
                }
                break;
        }
    }

    public function display_overlay_quick_checkout_button_footer()
    {
        if ($this->is_btn_add_hook_works) {
            return;
        }

        global $onepaquc_allowed_tags;

        // if isn't wc archive pages then return
        if (is_singular('product')) {
            return;
        }
        $button_contents = $this->button_contents();

        // Check if current page is allowed
        $allowed_pages = get_option('rmenu_show_quick_checkout_by_page', ['shop-page', 'category-archives', "tag-archives", 'search', "featured-products", "on-sale", "recent", "widgets", "shortcodes"]);
        $display = false;

        if (in_array('shop-page', $allowed_pages) && is_shop()) {
            $display = true;
        } elseif (in_array('category-archives', $allowed_pages) && is_product_category()) {
            $display = true;
        } elseif (in_array('tag-archives', $allowed_pages) && is_product_tag()) {
            $display = true;
        } elseif (in_array('search', $allowed_pages) && is_search()) {
            $display = true;
        } elseif (in_array('featured-products', $allowed_pages) && wc_get_loop_prop('is_featured')) {
            $display = true;
        } elseif (in_array('on-sale', $allowed_pages) && wc_get_loop_prop('is_on_sale')) {
            $display = true;
        } elseif (in_array('recent', $allowed_pages) && wc_get_loop_prop('is_recent')) {
            $display = true;
        } elseif (in_array('shortcodes', $allowed_pages) && is_singular()) {
            $display = true;
        }

        if (!$display) {
            return;
        }
    ?>
        <script>
            jQuery(document).ready(function($) {
                // Configuration variables
                const quickCheckoutConfig = {
                    buttonPos: "<?php echo esc_attr(get_option('rmenu_wc_direct_checkout_position', 'overlay_thumbnail_hover')); ?>",
                    contents: '<?php echo wp_kses($button_contents['button_content'], $onepaquc_allowed_tags); ?>',
                    buttonClass: "<?php echo esc_attr($button_contents['button_classes']); ?>",
                    buttonStyle: "<?php echo esc_attr($button_contents['button_style']); ?>",
                    allowedTypes: <?php echo wp_json_encode(get_option('rmenu_show_quick_checkout_by_types', ['simple', 'variable', "grouped", "external"])); ?>
                };

                /**
                 * Initialize quick checkout buttons for products
                 * @param {jQuery} container - Optional container to limit scope (defaults to entire document)
                 */
                function initQuickCheckoutButtons(container = $(document)) {
                    container.find(".product").each(function() {
                        let $this = $(this);

                        // Skip if this product already has a quick checkout button
                        if ($this.has(".plugincy-quick-checkout").length) {
                            return;
                        }

                        // Extract product ID from class or button data
                        let productIdMatch = $this.attr('class').match(/post-(\d+)/);
                        let product_id = productIdMatch ? productIdMatch[1] :
                            ($this.find(".button").length ? $this.find(".button").data("product_id") : null);

                        // Extract product type from class or button data
                        let productTypeMatch = $this.attr('class').match(/product-type-(\w+)/);
                        let product_type = productTypeMatch ? productTypeMatch[1] :
                            ($this.find(".button").length ? $this.find(".button").data("product-type") : null);

                        // Only add button if product type is allowed and we have a product ID
                        if (quickCheckoutConfig.allowedTypes.includes(product_type) && product_id) {
                            $this.append(
                                `<div class='plugincy-quick-checkout ${quickCheckoutConfig.buttonPos}' style='text-align:center;'>
                        <a class="${quickCheckoutConfig.buttonClass}" 
                           data-product-id="${product_id}" 
                           data-product-type="${product_type}" 
                           data-title="" 
                           style="${quickCheckoutConfig.buttonStyle}">
                            ${quickCheckoutConfig.contents}
                        </a>
                    </div>`
                            );
                        }
                    });

                    // Clean up any orphaned quick checkout buttons
                    cleanupOrphanedButtons();
                }

                /**
                 * Remove any quick checkout buttons that aren't children of .product elements
                 */
                function cleanupOrphanedButtons() {
                    $(".plugincy-quick-checkout").each(function() {
                        if (!$(this).closest('.product').length) {
                            $(this).remove();
                        }
                    });
                }

                /**
                 * Refresh quick checkout buttons (remove existing and reinitialize)
                 * @param {jQuery} container - Optional container to limit scope
                 */
                function refreshQuickCheckoutButtons(container = $(document)) {
                    container.find(".plugincy-quick-checkout").remove();
                    initQuickCheckoutButtons(container);
                }

                // Initial load
                initQuickCheckoutButtons();

                // Re-initialize after AJAX complete (global)
                $(document).ajaxComplete(function(event, xhr, settings) {
                    // Add a small delay to ensure DOM is updated
                    setTimeout(function() {
                        initQuickCheckoutButtons();
                    }, 100);
                });

                // Re-initialize when new content is loaded via AJAX (WooCommerce specific)
                $('body').on('wc_fragments_loaded wc_fragments_refreshed', function() {
                    initQuickCheckoutButtons();
                });

                // Re-initialize for infinite scroll or pagination
                $('body').on('post-load', function(e, data) {
                    if (data && data.length) {
                        initQuickCheckoutButtons($(data));
                    }
                });

                // Make functions globally available for manual calls
                window.initQuickCheckoutButtons = initQuickCheckoutButtons;
                window.refreshQuickCheckoutButtons = refreshQuickCheckoutButtons;
                window.cleanupOrphanedButtons = cleanupOrphanedButtons;
            });
        </script>
<?php
    }

    public function button_contents()
    {
        $position = get_option("rmenu_wc_direct_checkout_position", "overlay_thumbnail_hover");
        $button_styling = onepaquc_get_button_styling();
        $icon = $button_styling["icon"];

        $icon_content = isset($icon['content']) ? $icon['content'] : '';
        if ($icon_content == '' && ($position === "overlay_thumbnail" || $position === "overlay_thumbnail_hover" || $position === "after_product")) {
            $icon_content = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rmenu-icon"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>';
        }
        $icon_position = isset($icon['position']) ? $icon['position'] : 'left';
        $bg_color = get_option('rmenu_wc_checkout_color', '#000');

        // Button text
        $button_text = '<style> .plugincy-quick-checkout.overlay_thumbnail a .onepaquc-button-text:before,.plugincy-quick-checkout.overlay_thumbnail_hover a .onepaquc-button-text:before { content: ""; width: 0; height: 0; border-left: 5px solid transparent; border-right: 5px solid transparent; border-bottom: 10px solid ' . $bg_color . '; } </style><span class="onepaquc-button-text" style="' . esc_attr($button_styling['style']) . '">' . esc_html(get_option('txt-direct-checkout') !== "" ? get_option('txt-direct-checkout', 'Buy Now') : "Buy Now") . '</span>';

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
        global $onepaquc_allowed_tags;

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
        $button_text = esc_html(get_option('txt-direct-checkout') !== "" ? get_option('txt-direct-checkout', 'Buy Now') : "Buy Now");

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
        //     echo '<a class="' . esc_attr($button_classes) . '" style="' . esc_attr($button_styling['style']) . '">' . wp_kses($button_inner, $onepaquc_allowed_tags) . '</a>';
        // } else {
            // Output the button
            echo '<a class="' . esc_attr($button_styling['classes']) . '" data-product-id="' . esc_attr($product_id) . '" data-product-type="' . esc_attr($product_type) . '" data-title="' . esc_html($product_title) . '" style="' . esc_attr($button_styling['style']) . '">' . wp_kses($button_inner, $onepaquc_allowed_tags) . '</a>';
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
    $product = null;

    // 1) Explicit ID
    if (!empty($atts['product_id'])) {
        $product = wc_get_product(absint($atts['product_id']));
    }

    // 2) Contextual auto-detect (single product, loops, etc.)
    if (!$product && $atts['detect_product'] === '1') {
        global $product;
        if (!empty($product) && $product instanceof WC_Product) {
            $product = $product;
        } elseif (is_singular('product')) {
            $pid = get_the_ID();
            $product = wc_get_product($pid);
        }
    }

    // 3) Last fallback: bail if still no product
    if (!$product) {
        return 'No product found'; // Silently fail—no product context available
    }

    // Basic checks unless forced
    if ($atts['force'] !== '1' && !onepaquc_should_display_button($product)) {
        return '';
    }

    // Optional show_for filter
    if (!empty($atts['show_for'])) {
        $types = array_map('trim', explode(',', strtolower($atts['show_for'])));
        if (!in_array($product->get_type(), $types, true)) {
            return '';
        }
    }

    $product_id   = $product->get_id();
    $product_type = $product->get_type();

    // Resolve variation if requested/provided
    $variation_id = '';
    if (!empty($atts['variation_id'])) {
        $variation_id = absint($atts['variation_id']);
    } elseif ($product_type === 'variable' && $atts['detect_variation'] === '1') {
        $variation_id = onepaquc_pick_variation_id($product);
    }

    // Styling & icon from plugin settings (with shortcode overrides)
    $styling = onepaquc_get_button_styling();

    // Override icon type/position via shortcode if provided
    if (!empty($atts['icon'])) {
        $valid_icons = ['none', 'cart', 'checkout', 'arrow'];
        $icon_type = in_array($atts['icon'], $valid_icons, true) ? $atts['icon'] : 'none';
        if ($icon_type === 'none') {
            $styling['icon'] = [];
        } else {
            // Minimal SVG set (same as plugin) — re-use the switch from onepaquc_get_button_styling
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
            }
            $styling['icon'] = [
                'content'  => isset($icon_content) ? $icon_content : '',
                'position' => !empty($atts['icon_position']) ? $atts['icon_position'] : get_option('rmenu_wc_checkout_icon_position', 'left'),
            ];
            $styling['classes'] .= ' icon-position-' . esc_attr($styling['icon']['position']);
        }
    } elseif (!empty($atts['icon_position']) && !empty($styling['icon'])) {
        // Only adjust position if icon already present by settings
        $styling['icon']['position'] = $atts['icon_position'];
        $styling['classes'] .= ' icon-position-' . esc_attr($styling['icon']['position']);
    }

    // Extra classes/styles from shortcode
    if (!empty($atts['class'])) {
        $styling['classes'] .= ' ' . sanitize_html_class($atts['class']);
    }
    if (!empty($atts['style'])) {
        $styling['style'] .= rtrim($atts['style'], ';') . ';';
    }

    // Button text (shortcode override > plugin option > fallback)
    $button_text = $atts['text'] !== ''
        ? wp_kses_post($atts['text'])
        : esc_html(get_option('txt-direct-checkout') !== "" ? get_option('txt-direct-checkout', 'Buy Now') : "Buy Now");

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
    $qty = max(1, absint($atts['qty']));

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
        'href'            => '#checkout-popup',
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

    // Render
    $attr_html = '';
    foreach ($attrs as $k => $v) {
        $attr_html .= ' ' . $k . '="' . esc_attr($v) . '"';
    }

    // Allow the same sanitization rules used elsewhere in the plugin
    global $onepaquc_allowed_tags;
    if (empty($onepaquc_allowed_tags)) {
        // Minimal safe default if not set by theme/plugin
        $onepaquc_allowed_tags = wp_kses_allowed_html('post');
    }

    return '<a' . $attr_html . '>' . wp_kses($inner, $onepaquc_allowed_tags) . '</a>';
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
    $variations    = $product->get_available_variations();

    // Normalize default keys to 'attribute_{taxonomy}' style
    $normalized_defaults = [];
    foreach ($default_attrs as $key => $val) {
        $norm_key = strpos($key, 'attribute_') === 0 ? $key : 'attribute_' . $key;
        $normalized_defaults[$norm_key] = (string) $val;
    }

    // 1) Try default match first
    foreach ($variations as $var) {
        if (empty($var['variation_id'])) {
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
        if (!empty($var['variation_id']) && !empty($var['is_in_stock'])) {
            return (int) $var['variation_id'];
        }
    }

    return '';
}
