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
            
            // Add AJAX handlers
            add_action('wp_ajax_rmenu_load_product_quick_view', array($this, 'load_product_quick_view'));
            add_action('wp_ajax_nopriv_rmenu_load_product_quick_view', array($this, 'load_product_quick_view'));
            
            // Enqueue scripts and styles
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            
            // Add quick view modal container to footer
            add_action('wp_footer', array($this, 'quick_view_modal_container'));
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
        
        // Output button HTML
        $button_html = sprintf(
            '<a href="#" class="%1$s" data-product-id="%2$s">%3$s</a>',
            esc_attr(implode(' ', $button_classes)),
            esc_attr($product->get_id()),
            $button_content
        );
        
        echo apply_filters('rmenu_quick_view_button_html', $button_html, $product);
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
            plugin_dir_url(__FILE__) . 'assets/css/quick-view.css',
            array(),
            RMENU_VERSION
        );
        wp_enqueue_style('rmenu-quick-view-styles');
        
        // Register and enqueue scripts
        wp_register_script(
            'rmenu-quick-view-scripts',
            plugin_dir_url(__FILE__) . 'assets/js/quick-view.js',
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
        $preload = get_option('rmenu_quick_view_preload', 0);
        $debug_mode = get_option('rmenu_quick_view_debug_mode', 0);
        $lazy_load = get_option('rmenu_quick_view_lazy_load', 1);
        $lightbox = get_option('rmenu_quick_view_enable_lightbox', 1);
        
        // Localize script with settings
        wp_localize_script('rmenu-quick-view-scripts', 'rmenu_quick_view_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rmenu-quick-view-nonce'),
            'ajax_add_to_cart' => (bool) $ajax_add_to_cart,
            'close_on_add' => (bool) $close_on_add,
            'keyboard_nav' => (bool) $keyboard_nav,
            'effect' => $effect,
            'mobile_optimize' => (bool) $mobile_optimize,
            'preload' => (bool) $preload,
            'debug' => (bool) $debug_mode,
            'lazy_load' => (bool) $lazy_load,
            'lightbox' => (bool) $lightbox,
            'i18n' => array(
                'close' => get_option('rmenu_quick_view_close_text', 'Close'),
                'prev' => get_option('rmenu_quick_view_prev_text', 'Previous Product'),
                'next' => get_option('rmenu_quick_view_next_text', 'Next Product'),
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
        $container_id = get_option('rmenu_quick_view_container_id', 'rmenu-quick-view-container');
        ?>
        <div id="<?php echo esc_attr($container_id); ?>" class="rmenu-quick-view-modal-container">
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
    
    /**
     * Load product quick view via AJAX
     */
    public function load_product_quick_view() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rmenu-quick-view-nonce')) {
            wp_send_json_error('Invalid security token');
            die();
        }
        
        // Get product ID
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        if (!$product_id) {
            wp_send_json_error('Invalid product ID');
            die();
        }
        
        // Check if we should use cache
        $use_cache = get_option('rmenu_quick_view_enable_cache', 1);
        $cache_key = 'rmenu_quick_view_' . $product_id;
        $content = false;
        
        if ($use_cache) {
            $content = get_transient($cache_key);
        }
        
        // If no cached content, generate it
        if ($content === false) {
            $content = $this->generate_quick_view_content($product_id);
            
            // Cache the content if caching is enabled
            if ($use_cache) {
                $expiration = intval(get_option('rmenu_quick_view_cache_expiration', '24')) * HOUR_IN_SECONDS;
                set_transient($cache_key, $content, $expiration);
            }
        }
        
        // Track event if enabled
        if (get_option('rmenu_quick_view_track_events', 0)) {
            $this->track_quick_view_event($product_id);
        }
        
        wp_send_json_success($content);
        die();
    }
    
    /**
     * Generate quick view content for a product
     */
    private function generate_quick_view_content($product_id) {
        $product = wc_get_product($product_id);
        
        if (!$product) {
            return '';
        }
        
        // Get elements to display
        $elements = get_option('rmenu_quick_view_content_elements', array('image', 'title', 'rating', 'price', 'excerpt', 'add_to_cart', 'meta'));
        
        ob_start();
        
        echo '<div class="rmenu-quick-view-product-wrap">';
        
        // Hook before content
        do_action('rmenu_before_quick_view_content', $product);
        
        echo '<div class="rmenu-quick-view-product">';
        
        // Left column - gallery
        echo '<div class="rmenu-quick-view-left">';
        if (in_array('image', $elements) || in_array('gallery', $elements)) {
            $this->render_product_images($product, in_array('gallery', $elements));
        }
        echo '</div>';
        
        // Right column - product info
        echo '<div class="rmenu-quick-view-right">';
        
        if (in_array('title', $elements)) {
            echo '<h2 class="product_title">' . esc_html($product->get_name()) . '</h2>';
        }
        
        if (in_array('rating', $elements)) {
            if ($product->get_rating_count() > 0) {
                echo wc_get_rating_html($product->get_average_rating(), $product->get_rating_count());
            }
        }
        
        if (in_array('price', $elements)) {
            echo '<div class="price">' . $product->get_price_html() . '</div>';
        }
        
        if (in_array('excerpt', $elements)) {
            echo '<div class="woocommerce-product-details__short-description">';
            echo $product->get_short_description();
            echo '</div>';
        }
        
        if (in_array('add_to_cart', $elements)) {
            echo '<div class="rmenu-quick-view-add-to-cart">';
            
            // Add quantity field if enabled
            if (in_array('quantity', $elements) && $product->is_purchasable() && $product->get_type() !== 'grouped' && $product->get_type() !== 'external') {
                woocommerce_quantity_input(array(
                    'min_value' => $product->get_min_purchase_quantity(),
                    'max_value' => $product->get_max_purchase_quantity(),
                    'input_value' => 1,
                ), $product);
            }
            
            // Add to cart button
            echo '<div class="cart">';
            
            // Display variation select fields for variable products
            if ($product->is_type('variable')) {
                // Get available variations
                $available_variations = $product->get_available_variations();
                $attributes = $product->get_variation_attributes();
                
                // Display variation select
                wc_get_template(
                    'single-product/add-to-cart/variable.php',
                    array(
                        'available_variations' => $available_variations,
                        'attributes' => $attributes,
                        'selected_attributes' => $product->get_default_attributes()
                    )
                );
            } else {
                // Simple, grouped, external products
                wc_get_template(
                    'single-product/add-to-cart/simple.php',
                    array(
                        'product' => $product
                    )
                );
            }
            
            // Direct checkout button if enabled
            if (get_option('rmenu_quick_view_direct_checkout', 0) && has_action('rmenu_direct_checkout_button')) {
                do_action('rmenu_direct_checkout_button', $product);
            }
            
            echo '</div>'; // .cart
            echo '</div>'; // .rmenu-quick-view-add-to-cart
        }
        
        if (in_array('meta', $elements)) {
            echo '<div class="product_meta">';
            
            // SKU
            if (wc_product_sku_enabled() && $product->get_sku()) {
                echo '<span class="sku_wrapper">' . esc_html__('SKU:', 'woocommerce') . ' <span class="sku">' . esc_html($product->get_sku()) . '</span></span>';
            }
            
            // Categories
            echo wc_get_product_category_list($product->get_id(), ', ', '<span class="posted_in">' . _n('Category:', 'Categories:', count($product->get_category_ids()), 'woocommerce') . ' ', '</span>');
            
            // Tags
            echo wc_get_product_tag_list($product->get_id(), ', ', '<span class="tagged_as">' . _n('Tag:', 'Tags:', count($product->get_tag_ids()), 'woocommerce') . ' ', '</span>');
            
            echo '</div>';
        }
        
        if (in_array('attributes', $elements) && $product->has_attributes()) {
            echo '<div class="rmenu-quick-view-attributes">';
            do_action('woocommerce_product_additional_information', $product);
            echo '</div>';
        }
        
        if (in_array('sharing', $elements) && function_exists('woocommerce_template_single_sharing')) {
            woocommerce_template_single_sharing();
        }
        
        if (in_array('view_details', $elements)) {
            $details_text = get_option('rmenu_quick_view_details_text', 'View Full Details');
            echo '<a href="' . esc_url($product->get_permalink()) . '" class="button view-details">' . esc_html($details_text) . '</a>';
        }
        
        echo '</div>'; // .rmenu-quick-view-right
        
        echo '</div>'; // .rmenu-quick-view-product
        
        // Hook after content
        do_action('rmenu_after_quick_view_content', $product);
        
        echo '</div>'; // .rmenu-quick-view-product-wrap
        
        return ob_get_clean();
    }
    
    /**
     * Render product images
     */
    private function render_product_images($product, $show_gallery = true) {
        echo '<div class="rmenu-quick-view-images">';
        
        // Main image
        $image_id = $product->get_image_id();
        if ($image_id) {
            $image_size = 'woocommerce_single';
            $image_src = wp_get_attachment_image_src($image_id, $image_size);
            $image_full = wp_get_attachment_image_src($image_id, 'full');
            
            echo '<div class="rmenu-quick-view-main-image">';
            if (get_option('rmenu_quick_view_enable_lightbox', 1)) {
                echo '<a href="' . esc_url($image_full[0]) . '" class="rmenu-quick-view-lightbox">';
            }
            echo wp_get_attachment_image($image_id, $image_size, false, array('class' => 'wp-post-image'));
            if (get_option('rmenu_quick_view_enable_lightbox', 1)) {
                echo '</a>';
            }
            echo '</div>';
        } else {
            echo '<div class="rmenu-quick-view-main-image">';
            echo wc_placeholder_img($image_size);
            echo '</div>';
        }
        
        // Gallery
        if ($show_gallery) {
            $gallery_ids = $product->get_gallery_image_ids();
            if (!empty($gallery_ids)) {
                echo '<div class="rmenu-quick-view-thumbnails">';
                
                // Add main image to thumbnails
                if ($image_id) {
                    echo '<div class="rmenu-quick-view-thumbnail active" data-image-id="' . esc_attr($image_id) . '">';
                    echo wp_get_attachment_image($image_id, 'shop_thumbnail');
                    echo '</div>';
                }
                
                // Gallery thumbnails
                foreach ($gallery_ids as $gallery_id) {
                    $image_src = wp_get_attachment_image_src($gallery_id, 'woocommerce_single');
                    $image_full = wp_get_attachment_image_src($gallery_id, 'full');
                    
                    echo '<div class="rmenu-quick-view-thumbnail" data-image-id="' . esc_attr($gallery_id) . '" data-full-image="' . esc_url($image_full[0]) . '">';
                    echo wp_get_attachment_image($gallery_id, 'shop_thumbnail');
                    echo '</div>';
                }
                
                echo '</div>';
            }
        }
        
        echo '</div>';
    }
    
    /**
     * Track quick view event
     */
    private function track_quick_view_event($product_id) {
        $product = wc_get_product($product_id);
        
        if (!$product) {
            return;
        }
        
        // You can implement custom tracking here
        // For example, storing view count in post meta
        $views = get_post_meta($product_id, '_rmenu_quick_view_count', true);
        $views = $views ? $views + 1 : 1;
        update_post_meta($product_id, '_rmenu_quick_view_count', $views);
    }
}

// Initialize the quick view class
$rmenu_quick_view = new RMENU_Quick_View();

/**
 * Add shortcode for quick view button
 */
function rmenu_quick_view_shortcode($atts) {
    $atts = shortcode_atts(array(
        'product_id' => '',
        'button_text' => get_option('rmenu_quick_view_button_text', 'Quick View')
    ), $atts, 'rmenu_quick_view');
    
    $product_id = absint($atts['product_id']);
    
    if (!$product_id) {
        return '';
    }
    
    $product = wc_get_product($product_id);
    
    if (!$product) {
        return '';
    }
    
    // Create temporary instance to use the method
    $quick_view = new RMENU_Quick_View();
    
    ob_start();
    $quick_view->render_quick_view_button($product);
    return ob_get_clean();
}
add_shortcode('rmenu_quick_view', 'rmenu_quick_view_shortcode');