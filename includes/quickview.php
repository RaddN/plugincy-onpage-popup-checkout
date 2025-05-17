<?php
/**
 * WooCommerce Quick View Implementation
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class RMENU_Quick_View {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize only if quick view is enabled
        if (get_option('rmenu_enable_quick_view', 0)) {
            // Add quick view button to product loops
            $this->add_quick_view_button();
            
            // Enqueue scripts and styles
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            
            // Add quick view modal container to footer
            add_action('wp_footer', array($this, 'quick_view_modal_container'));
            
            // Add product data to each product element
            add_action('woocommerce_after_shop_loop_item', array($this, 'add_product_data'), 20);
        }
    }
    
    /**
     * Add quick view button to appropriate locations
     */
    private function add_quick_view_button() {
        $button_position = get_option('rmenu_quick_view_button_position', 'after_image');
        
        switch ($button_position) {
            case 'after_image':
                add_action('woocommerce_before_shop_loop_item_title', array($this, 'display_quick_view_button'), 11);
                break;
            case 'before_title':
                add_action('woocommerce_before_shop_loop_item_title', array($this, 'display_quick_view_button'), 9);
                break;
            case 'after_title':
                add_action('woocommerce_shop_loop_item_title', array($this, 'display_quick_view_button'), 11);
                break;
            case 'before_price':
                add_action('woocommerce_after_shop_loop_item_title', array($this, 'display_quick_view_button'), 9);
                break;
            case 'after_price':
                add_action('woocommerce_after_shop_loop_item_title', array($this, 'display_quick_view_button'), 11);
                break;
            case 'before_add_to_cart':
                add_action('woocommerce_after_shop_loop_item', array($this, 'display_quick_view_button'), 9);
                break;
            case 'after_add_to_cart':
                add_action('woocommerce_after_shop_loop_item', array($this, 'display_quick_view_button'), 11);
                break;
            case 'image_overlay':
                add_action('woocommerce_before_shop_loop_item_title', array($this, 'display_overlay_quick_view_button'), 10);
                break;
        }
    }
    
    /**
     * Display quick view button
     */
    public function display_quick_view_button() {
        global $product;
        
        // Check if product type is allowed
        $allowed_types = get_option('rmenu_show_quick_view_by_types', array('simple', 'variable'));
        if (!in_array($product->get_type(), $allowed_types)) {
            return;
        }
        
        // Check if current page is allowed
        $allowed_pages = get_option('rmenu_show_quick_view_by_page', array('shop-page', 'category-archives', 'search'));
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
        } elseif (in_array('widgets', $allowed_pages) && (is_active_widget(false, false, 'woocommerce_products', true) || is_active_widget(false, false, 'woocommerce_top_rated_products', true))) {
            $display = true;
        } elseif (in_array('shortcodes', $allowed_pages) && is_singular()) {
            $display = true;
        }
        
        if (!$display) {
            return;
        }
        
        // Create button
        $this->render_quick_view_button($product);
    }
    
    /**
     * Display overlay quick view button on image
     */
    public function display_overlay_quick_view_button() {
        global $product;
        
        // Same checks as display_quick_view_button
        $allowed_types = get_option('rmenu_show_quick_view_by_types', array('simple', 'variable'));
        if (!in_array($product->get_type(), $allowed_types)) {
            return;
        }
        
        echo '<div class="rmenu-quick-view-overlay">';
        $this->render_quick_view_button($product);
        echo '</div>';
    }
    
    /**
     * Render the actual quick view button HTML
     */
    private function render_quick_view_button($product) {
        $button_text = get_option('rmenu_quick_view_button_text', 'Quick View');
        $display_type = get_option('rmenu_quick_view_display_type', 'button');
        $button_icon = get_option('rmenu_quick_view_button_icon', 'eye');
        $icon_position = get_option('rmenu_quick_view_icon_position', 'left');
        
        // Generate icon HTML if needed
        $icon_html = '';
        if ($button_icon !== 'none' && ($display_type === 'icon' || $display_type === 'text_icon' || $display_type === 'hover_icon')) {
            $icon_class = '';
            switch ($button_icon) {
                case 'eye':
                    $icon_class = 'dashicons-visibility';
                    break;
                case 'search':
                    $icon_class = 'dashicons-search';
                    break;
                case 'zoom':
                    $icon_class = 'dashicons-zoom';
                    break;
                case 'preview':
                    $icon_class = 'dashicons-welcome-view-site';
                    break;
            }
            $icon_html = '<span class="dashicons ' . esc_attr($icon_class) . '"></span>';
        }
        
        // Generate button classes
        $button_classes = array('rmenu-quick-view-btn');
        $button_style = get_option('rmenu_quick_view_button_style', 'default');
        
        if ($button_style === 'default') {
            $button_classes[] = 'button';
        } elseif ($button_style === 'alt') {
            $button_classes[] = 'button alt';
        } else {
            $button_classes[] = 'custom-style';
        }
        
        $button_classes[] = 'display-' . $display_type;
        
        // Build button content
        $button_content = '';
        
        if ($display_type === 'icon') {
            $button_content = $icon_html;
        } elseif ($display_type === 'text_icon') {
            if ($icon_position === 'left') {
                $button_content = $icon_html . ' ' . esc_html($button_text);
            } else {
                $button_content = esc_html($button_text) . ' ' . $icon_html;
            }
        } elseif ($display_type === 'hover_icon') {
            $button_content = '<span class="text">' . esc_html($button_text) . '</span><span class="icon">' . $icon_html . '</span>';
            $button_classes[] = 'hover-effect';
        } else {
            $button_content = esc_html($button_text);
        }
        
        // Output button HTML with data-product-id attribute
        $button_html = sprintf(
            '<a href="#" class="%1$s" data-product-id="%2$s">%3$s</a>',
            esc_attr(implode(' ', $button_classes)),
            esc_attr($product->get_id()),
            $button_content
        );
        
        echo apply_filters('rmenu_quick_view_button_html', $button_html, $product);
    }
    
    /**
     * Add product data to product elements
     */
    public function add_product_data() {
        global $product;
        
        if (!$product) {
            return;
        }
        
        // Get elements to display
        $elements = get_option('rmenu_quick_view_content_elements', array('image', 'title', 'rating', 'price', 'excerpt', 'add_to_cart', 'meta'));
        
        // Prepare product data
        $product_data = array(
            'id' => $product->get_id(),
            'title' => $product->get_name(),
            'price_html' => $product->get_price_html(),
            'type' => $product->get_type(),
            'excerpt' => $product->get_short_description(),
            'permalink' => $product->get_permalink(),
            'images' => array(),
            'variations' => array(),
            'attributes' => array(),
            'is_purchasable' => $product->is_purchasable(),
            'is_in_stock' => $product->is_in_stock(),
            'rating_html' => '',
            'add_to_cart_url' => $product->add_to_cart_url(),
            'stock_quantity' => $product->get_stock_quantity(),
            'min_purchase_quantity' => $product->get_min_purchase_quantity(),
            'max_purchase_quantity' => $product->get_max_purchase_quantity(),
            'categories' => wp_strip_all_tags(wc_get_product_category_list($product->get_id())),
            'tags' => wp_strip_all_tags(wc_get_product_tag_list($product->get_id())),
        );
        
        // Add rating if available
        if ($product->get_rating_count() > 0) {
            $product_data['rating_html'] = wc_get_rating_html($product->get_average_rating(), $product->get_rating_count());
            $product_data['rating_count'] = $product->get_rating_count();
            $product_data['average_rating'] = $product->get_average_rating();
        }
        
        // Add SKU if enabled
        if (wc_product_sku_enabled() && $product->get_sku()) {
            $product_data['sku'] = $product->get_sku();
        }
        
        // Get product images
        $image_id = $product->get_image_id();
        if ($image_id) {
            $image_src = wp_get_attachment_image_src($image_id, 'woocommerce_single');
            $image_thumb = wp_get_attachment_image_src($image_id, 'shop_thumbnail');
            $image_full = wp_get_attachment_image_src($image_id, 'full');
            
            $product_data['images'][] = array(
                'id' => $image_id,
                'src' => $image_src[0],
                'thumb' => $image_thumb[0],
                'full' => $image_full[0],
                'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true),
            );
        }
        
        // Get gallery images
        $gallery_ids = $product->get_gallery_image_ids();
        if (!empty($gallery_ids)) {
            foreach ($gallery_ids as $gallery_id) {
                $image_src = wp_get_attachment_image_src($gallery_id, 'woocommerce_single');
                $image_thumb = wp_get_attachment_image_src($gallery_id, 'shop_thumbnail');
                $image_full = wp_get_attachment_image_src($gallery_id, 'full');
                
                if ($image_src) {
                    $product_data['images'][] = array(
                        'id' => $gallery_id,
                        'src' => $image_src[0],
                        'thumb' => $image_thumb[0],
                        'full' => $image_full[0],
                        'alt' => get_post_meta($gallery_id, '_wp_attachment_image_alt', true),
                    );
                }
            }
        }
        
        // Add variation data for variable products
        if ($product->is_type('variable')) {
            $product_data['attributes'] = $product->get_variation_attributes();
            $product_data['default_attributes'] = $product->get_default_attributes();
            
            // Get available variations
            $available_variations = $product->get_available_variations();
            if (!empty($available_variations)) {
                foreach ($available_variations as $variation) {
                    $variation_id = $variation['variation_id'];
                    $variation_obj = wc_get_product($variation_id);
                    
                    $product_data['variations'][] = array(
                        'variation_id' => $variation_id,
                        'attributes' => $variation['attributes'],
                        'price_html' => $variation_obj->get_price_html(),
                        'is_in_stock' => $variation_obj->is_in_stock(),
                        'image_id' => $variation['image_id'],
                        'image_src' => $variation['image']['src'],
                        // 'image_full_src' => $variation['image']['full_src'],
                    );
                }
            }
        }
        
        // Output data attribute with JSON encoded product data
        echo '<div class="rmenu-product-data" style="display:none;" data-product-info="' . esc_attr(json_encode($product_data)) . '"></div>';
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Load scripts based on setting
        $load_scripts = get_option('rmenu_quick_view_load_scripts', 'wc-only');
        
        $load = false;
        
        if ($load_scripts === 'all') {
            $load = true;
        } elseif ($load_scripts === 'wc-only' && (is_shop() || is_product_category() || is_product_tag() || is_product() || is_cart() || is_checkout())) {
            $load = true;
        } elseif ($load_scripts === 'specific') {
            $specific_pages = get_option('rmenu_quick_view_specific_pages', '');
            $specific_page_ids = array_map('trim', explode(',', $specific_pages));
            
            if (is_page($specific_page_ids)) {
                $load = true;
            }
        }
        
        if (!$load) {
            return;
        }
        
        // Register and enqueue styles
        wp_register_style(
            'rmenu-quick-view-styles',
            plugin_dir_url(__FILE__) . '../assets/css/quick-view.css',
            array(),
            RMENU_VERSION
        );
        wp_enqueue_style('rmenu-quick-view-styles');
        
        // Register and enqueue scripts
        wp_register_script(
            'rmenu-quick-view-scripts',
            plugin_dir_url(__FILE__) . '../assets/js/quick-view.js',
            array('jquery'),
            RMENU_VERSION,
            true
        );
        
        // Get settings for JS
        $ajax_add_to_cart = get_option('rmenu_quick_view_ajax_add_to_cart', 1);
        $close_on_add = get_option('rmenu_quick_view_close_on_add', 0);
        $keyboard_nav = get_option('rmenu_quick_view_keyboard_nav', 1);
        $effect = get_option('rmenu_quick_view_loading_effect', 'fade');
        $mobile_optimize = get_option('rmenu_quick_view_mobile_optimize', 1);
        $debug_mode = get_option('rmenu_quick_view_debug_mode', 0);
        $lightbox = get_option('rmenu_quick_view_enable_lightbox', 1);
        $elements_in_popup = get_option('rmenu_quick_view_content_elements', array('image', 'title', 'rating', 'price', 'excerpt', 'add_to_cart', 'meta', 'title','quantity','sharing', 'view_details', 'attributes'));

        // Localize script with settings
        wp_localize_script('rmenu-quick-view-scripts', 'rmenu_quick_view_params', array(
            'ajax_add_to_cart' => (bool) $ajax_add_to_cart,
            'close_on_add' => (bool) $close_on_add,
            'keyboard_nav' => (bool) $keyboard_nav,
            'effect' => $effect,
            'mobile_optimize' => (bool) $mobile_optimize,
            'debug' => (bool) $debug_mode,
            'lightbox' => (bool) $lightbox,
            'elements_in_popup' => $elements_in_popup,
            'i18n' => array(
                'close' => get_option('rmenu_quick_view_close_text', 'Close'),
                'prev' => get_option('rmenu_quick_view_prev_text', 'Previous Product'),
                'next' => get_option('rmenu_quick_view_next_text', 'Next Product'),
                'add_to_cart' => esc_html__('Add to cart', 'one-page-quick-checkout-for-woocommerce'),
                'select_options' => esc_html__('Select options', 'one-page-quick-checkout-for-woocommerce'),
                'view_details' => get_option('rmenu_quick_view_details_text', 'View Full Details'),
                'out_of_stock' => esc_html__('Out of stock', 'one-page-quick-checkout-for-woocommerce'),
            )
        ));
        
        wp_enqueue_script('rmenu-quick-view-scripts');
        
        // Custom JS from settings
        $custom_js = get_option('rmenu_quick_view_custom_js', '');
        if (!empty($custom_js)) {
            wp_add_inline_script('rmenu-quick-view-scripts', $custom_js);
        }
        
        // Custom CSS from settings
        if (get_option('rmenu_quick_view_button_style', 'default') === 'custom') {
            $custom_css = get_option('rmenu_quick_view_custom_css', '');
            if (!empty($custom_css)) {
                wp_add_inline_style('rmenu-quick-view-styles', $custom_css);
            }
        }
        
        // Add dynamic CSS
        $this->add_dynamic_css();
    }
    
    /**
     * Add dynamic CSS for button styling
     */
    private function add_dynamic_css() {
        $button_color = get_option('rmenu_quick_view_button_color', '#96588a');
        $text_color = get_option('rmenu_quick_view_text_color', '#ffffff');
        
        $custom_css = "
            .rmenu-quick-view-btn.custom-style {
                background-color: {$button_color};
                color: {$text_color};
            }
            .rmenu-quick-view-btn.custom-style:hover {
                background-color: " . $this->adjust_brightness($button_color, -15) . ";
            }
        ";
        
        wp_add_inline_style('rmenu-quick-view-styles', $custom_css);
    }
    
    /**
     * Adjust color brightness
     */
    private function adjust_brightness($hex, $steps) {
        // Convert hex to rgb
        $hex = str_replace('#', '', $hex);
        
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // Adjust brightness
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));
        
        // Convert back to hex
        return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
    }
    
    /**
     * Add quick view modal container to footer
     */
    public function quick_view_modal_container() {
        ?>
        <div class="rmenu-quick-view-modal-container">
            <div class="rmenu-quick-view-modal-overlay"></div>
            <div class="rmenu-quick-view-modal">
                <div class="rmenu-quick-view-close">
                    <span class="dashicons dashicons-no-alt"></span>
                    <span class="screen-reader-text"><?php echo esc_html(get_option('rmenu_quick_view_close_text', 'Close')); ?></span>
                </div>
                <div class="rmenu-quick-view-content">
                    <div class="rmenu-quick-view-loading">
                        <div class="rmenu-loader"></div>
                    </div>
                    <div class="rmenu-quick-view-inner"></div>
                </div>
                <div class="rmenu-quick-view-nav">
                    <a href="#" class="rmenu-quick-view-prev">
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                        <span class="screen-reader-text"><?php echo esc_html(get_option('rmenu_quick_view_prev_text', 'Previous Product')); ?></span>
                    </a>
                    <a href="#" class="rmenu-quick-view-next">
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                        <span class="screen-reader-text"><?php echo esc_html(get_option('rmenu_quick_view_next_text', 'Next Product')); ?></span>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize the quick view class
$rmenu_quick_view = new RMENU_Quick_View();

/**
 * Add shortcode for quick view button
 */
// function rmenu_quick_view_shortcode($atts) {
//     $atts = shortcode_atts(array(
//         'product_id' => '',
//         'button_text' => get_option('rmenu_quick_view_button_text', 'Quick View')
//     ), $atts, 'rmenu_quick_view');
    
//     $product_id = absint($atts['product_id']);
    
//     if (!$product_id) {
//         return '';
//     }
    
//     $product = wc_get_product($product_id);
    
//     if (!$product) {
//         return '';
//     }
    
//     // Create temporary instance to use the method
//     $quick_view = new RMENU_Quick_View();
    
//     ob_start();
//     $quick_view->render_quick_view_button($product);
//     $quick_view->add_product_data();
//     return ob_get_clean();
// }
// add_shortcode('plugincy_quick_view', 'rmenu_quick_view_shortcode');