<?php

/**
 * WooCommerce Quick View Implementation
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class RMENU_Quick_View
{
    private $is_btn_add_hook_works;
    /**
     * Constructor
     */
    public function __construct()
    {
        // Initialize only if quick view is enabled
        if (get_option('rmenu_enable_quick_view', 0)) {
            // Add quick view button to product loops
            $this->add_quick_view_button();
            $this->is_btn_add_hook_works = false;

            // Enqueue scripts and styles
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

            // Add quick view modal container to footer
            add_action('wp_footer', array($this, 'quick_view_modal_container'));

            // Add product data to each product element
            add_action('woocommerce_after_shop_loop_item', array($this, 'add_product_data'), 20);
            // fallback
            add_action('wp', function () {
                add_action('woocommerce_shop_loop', array($this, 'add_product_data'), 5);
                add_action('woocommerce_before_shop_loop_item', array($this, 'add_product_data'), 25);
                add_action('woocommerce_before_shop_loop_item_title', array($this, 'add_product_data'), 25);
                add_action('woocommerce_shop_loop_item_title', array($this, 'add_product_data'), 25);
                add_action('woocommerce_after_shop_loop_item_title', array($this, 'add_product_data'), 25);
                add_action('woocommerce_before_single_product_summary', array($this, 'add_product_data'), 5);
                add_action('woocommerce_single_product_summary', array($this, 'add_product_data'), 5);
                add_action('woocommerce_output_related_products_args', array($this, 'add_product_data'), 5);
                add_action('woocommerce_cross_sell_display', array($this, 'add_product_data'), 5);
                add_action('woocommerce_upsell_display', array($this, 'add_product_data'), 5);
            });
        }
    }

    /**
     * Add quick view button to appropriate locations
     */
    private function add_quick_view_button()
    {
        global $displayed_quick_view_buttons;
        $displayed_quick_view_buttons = false;
        $button_position = get_option('rmenu_quick_view_button_position', 'image_overlay');

        switch ($button_position) {
            case 'after_image':
                // add_action('woocommerce_after_shop_loop_item_title', array($this, 'display_quick_view_button'), 11);
                if (!$this->is_btn_add_hook_works) {
                    add_action('wp_footer', array($this, 'display_overlay_quick_view_button_footer'), 25);
                }
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
            case 'after_add_to_cart':
                add_filter('woocommerce_loop_add_to_cart_link', array($this, 'onepaquc_display_quick_view_button_to_add_to_cart'), 100, 2);
                break;
            case 'image_overlay':
                // // Primary hook
                // add_action('woocommerce_after_shop_loop_item_title', array($this, 'display_overlay_quick_view_button'), 11);
                // add_action('woocommerce_shop_loop_item_title', array($this, 'display_overlay_quick_view_button'), 30);
                // // Add fallback hooks with lower priority in case the primary doesn't work
                // add_action('woocommerce_after_shop_loop_item_title', array($this, 'display_overlay_quick_view_button'), 5);
                // add_action('woocommerce_before_shop_loop_item_title', array($this, 'display_overlay_quick_view_button'), 11);
                // add_action('woocommerce_after_shop_loop_item', array($this, 'display_overlay_quick_view_button'), 11);
                // add_action('woocommerce_before_shop_loop_item', array($this, 'display_overlay_quick_view_button'), 25);

                add_action('wp_footer', array($this, 'display_overlay_quick_view_button_footer'), 25);

                break;
        }

        if (!$displayed_quick_view_buttons) {
            add_action('wp_footer', array($this, 'display_overlay_quick_view_button_footer'), 110);
        }
    }


    public function onepaquc_display_quick_view_button_to_add_to_cart($link, $product)
    {
        global $displayed_quick_view_buttons;
        $displayed_quick_view_buttons = true;
        // Check if product type is allowed
        $allowed_types = get_option('rmenu_show_quick_view_by_types', ['simple', 'variable', "grouped", "external"]);
        if (!in_array($product->get_type(), $allowed_types)) {
            return $link;
        }

        $this->is_btn_add_hook_works = true;

        // Check if current page is allowed
        $allowed_pages = get_option('rmenu_show_quick_view_by_page', ['shop-page', 'category-archives', "tag-archives", 'search', "featured-products", "on-sale", "recent", "widgets", "shortcodes"]);
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
            return $link;
        }

        $position = get_option('rmenu_quick_view_button_position', 'image_overlay');

        $this->is_btn_add_hook_works = true;

        // Final button HTML
        $quick_view_button = $this->display_overlay_quick_view_button(true);
        switch ($position) {
            case 'before_add_to_cart':
                return $quick_view_button . ' ' . $link;

            default:
                return $link . ' ' . $quick_view_button;
        }
    }

    public function display_overlay_quick_view_button_footer()
    {
        if ($this->is_btn_add_hook_works) {
            return;
        }

        global $onepaquc_allowed_tags;

        if (is_singular('product')) {
            return;
        }

        $button_contents = $this->button_contents();

        // Check if current page is allowed
        $allowed_pages = get_option('rmenu_show_quick_view_by_page', ['shop-page', 'category-archives', "tag-archives", 'search', "featured-products", "on-sale", "recent", "widgets", "shortcodes"]);
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
?>
        <script>
            jQuery(document).ready(function($) {
                // Configuration variables
                const quickViewConfig = {
                    buttonPos: "<?php echo esc_attr(get_option('rmenu_quick_view_button_position', 'image_overlay')); ?>",
                    contents: '<?php echo wp_kses($button_contents['button_content'], $onepaquc_allowed_tags); ?>',
                    buttonClass: "<?php echo esc_attr(implode(' ', $button_contents['button_classes'])); ?>",
                    allowedTypes: <?php echo wp_json_encode(get_option('rmenu_show_quick_view_by_types', ['simple', 'variable', "grouped", "external"])); ?>
                };

                /**
                 * Initialize quick view buttons for products
                 * @param {jQuery} container - Optional container to limit scope (defaults to entire document)
                 */
                function initQuickViewButtons(container = $(document)) {
                    container.find(".product").each(function() {
                        let $this = $(this);

                        // Skip if this product already has a quick view button or conflicting plugin button
                        if ($this.has(".rmenu-quick-view-overlay").length || $this.has(".opqvfw-btn").length) {
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
                        if (quickViewConfig.allowedTypes.includes(product_type) && product_id) {
                            addQuickViewButton($this, product_id);
                        }
                    });

                    // Clean up any orphaned quick view buttons
                    cleanupOrphanedQuickViewButtons();
                }

                /**
                 * Add quick view button based on position setting
                 * @param {jQuery} $product - Product element
                 * @param {string} product_id - Product ID
                 */
                function addQuickViewButton($product, product_id) {
                    const buttonHtml = `<div class='rmenu-quick-view-overlay ${quickViewConfig.buttonPos}'>
            <a href="#" class="${quickViewConfig.buttonClass}" data-product-id="${product_id}">
                ${quickViewConfig.contents}
            </a>
        </div>`;

                    if (quickViewConfig.buttonPos === 'after_image') {
                        // Find the image and add button after it
                        let $image = $product.find('img').first();
                        if ($image.length) {
                            $image.after(buttonHtml);
                        } else {
                            // Fallback: append to product if no image found
                            $product.append(buttonHtml);
                        }
                    } else {
                        // Default behavior for other positions (overlay, etc.)
                        $product.append(buttonHtml);
                    }
                }

                /**
                 * Remove any quick view buttons that aren't children of .product elements
                 */
                function cleanupOrphanedQuickViewButtons() {
                    $(".rmenu-quick-view-overlay").each(function() {
                        if (!$(this).closest('.product').length) {
                            $(this).remove();
                        }
                    });
                }

                /**
                 * Refresh quick view buttons (remove existing and reinitialize)
                 * @param {jQuery} container - Optional container to limit scope
                 */
                function refreshQuickViewButtons(container = $(document)) {
                    container.find(".rmenu-quick-view-overlay").remove();
                    initQuickViewButtons(container);
                }

                // Initial load
                initQuickViewButtons();

                // Re-initialize after AJAX complete (global)
                $(document).ajaxComplete(function(event, xhr, settings) {
                    // Add a small delay to ensure DOM is updated
                    setTimeout(function() {
                        initQuickViewButtons();
                    }, 100);
                });

                // Re-initialize when new content is loaded via AJAX (WooCommerce specific)
                $('body').on('wc_fragments_loaded wc_fragments_refreshed', function() {
                    initQuickViewButtons();
                });

                // Re-initialize for infinite scroll or pagination
                $('body').on('post-load', function(e, data) {
                    if (data && data.length) {
                        initQuickViewButtons($(data));
                    }
                });

                // Re-initialize when products are updated/filtered
                $('body').on('woocommerce_updated_cart_totals updated_checkout updated_shipping_method', function() {
                    setTimeout(function() {
                        initQuickViewButtons();
                    }, 100);
                });

                // Make functions globally available for manual calls
                window.initQuickViewButtons = initQuickViewButtons;
                window.refreshQuickViewButtons = refreshQuickViewButtons;
                window.cleanupOrphanedQuickViewButtons = cleanupOrphanedQuickViewButtons;

            });
        </script>
    <?php
    }

    /**
     * Display quick view button
     */
    public function display_quick_view_button()
    {

        global $product;

        // Check if product type is allowed
        $allowed_types = get_option('rmenu_show_quick_view_by_types', ['simple', 'variable', "grouped", "external"]);
        if (!in_array($product->get_type(), $allowed_types)) {
            return;
        }

        $this->is_btn_add_hook_works = true;

        // Check if current page is allowed
        $allowed_pages = get_option('rmenu_show_quick_view_by_page', ['shop-page', 'category-archives', "tag-archives", 'search', "featured-products", "on-sale", "recent", "widgets", "shortcodes"]);
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
    public function display_overlay_quick_view_button($is_return = false)
    {
        global $product;

        $button_position = get_option('rmenu_quick_view_button_position', 'image_overlay');

        $this->is_btn_add_hook_works = true;

        // Same checks as display_quick_view_button
        $allowed_types = get_option('rmenu_show_quick_view_by_types', ['simple', 'variable', "grouped", "external"]);
        if (!in_array($product->get_type(), $allowed_types)) {
            return;
        }

        if (!$is_return) {
            echo '<div class="rmenu-quick-view-overlay ' . esc_attr($button_position) . '">';
            $this->render_quick_view_button($product);
            echo '</div>';
        } else {
            ob_start();
            echo '<div class="rmenu-quick-view-overlay ' . esc_attr($button_position) . '">';
            $this->render_quick_view_button($product);
            echo '</div>';

            return ob_get_clean();
        }
    }

    /**
     * Render the actual quick view button HTML
     */
    private function render_quick_view_button($product)
    {
        static $displayed_products = array();

        // Use product ID to track if we already displayed button for this product
        $product_id = $product->get_id();

        if (isset($displayed_products[$product_id])) {
            return; // Already displayed for this product
        }

        // Mark as displayed
        $displayed_products[$product_id] = true;

        $button_contents = $this->button_contents();

        // Output button HTML with data-product-id attribute
        $button_html = sprintf(
            '<a href="#" class="%1$s" data-product-id="%2$s">%3$s</a>',
            esc_attr(implode(' ', $button_contents['button_classes'])),
            esc_attr($product->get_id()),
            $button_contents['button_content']
        );


        echo wp_kses_post(apply_filters('rmenu_quick_view_button_html', $button_html, $product));
    }

    public function button_contents()
    {
        $display_type = get_option('rmenu_quick_view_display_type', 'icon');
        $button_icon = get_option('rmenu_quick_view_button_icon', 'eye');
        $icon_position = get_option('rmenu_quick_view_icon_position', 'left');
        $button_position = get_option('rmenu_quick_view_button_position', 'image_overlay');
        $button_text = get_option('rmenu_quick_view_button_text', '');
        if (empty($button_text)) {
            $button_text = 'Quick View';
        }

        // Generate icon HTML if needed
        $icon_html = '';
        if (empty($icon_html)) {
            if ($button_icon !== 'none' && ($display_type === 'icon' || $display_type === 'text_icon' || $display_type === 'hover_icon')) {
                $icon_html = '<span class="ricons ricons-' . $button_icon . '"></span>';
            }
        }



        // Generate button classes
        $button_classes = array('opqvfw-btn');
        $button_style = get_option('rmenu_quick_view_button_style', 'default');

        if ($button_style === 'default') {
            $button_classes[] = 'button';
        } elseif ($button_style === 'alt') {
            $button_classes[] = 'button alt';
        } else {
            $button_classes[] = 'custom-style';
        }

        $button_classes[] = $button_position . ' display-' . $display_type;

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
        }
        // elseif ($display_type === 'hover_icon') {
        //     $button_content = '<span class="text">' . esc_html($button_text) . '</span><span class="icon">' . $icon_html . '</span>';
        //     $button_classes[] = 'hover-effect';
        // } 
        else {
            $button_content = esc_html($button_text);
        }

        return [
            "button_classes" => $button_classes,
            "button_content" => $button_content
        ];
    }

    /**
     * Add product data to product elements
     */
    public function add_product_data()
    {
        global $product;

        static $displayed_product_data = array();

        if (!$product) {
            return;
        }

        $product_id = $product->get_id();

        if (isset($displayed_product_data[$product_id])) {
            return; // Already displayed for this product
        }

        // Mark as displayed
        $displayed_product_data[$product_id] = true;

        // Get elements to display
        $elements = get_option('rmenu_quick_view_content_elements', ['image', 'title', 'rating', 'price', 'excerpt', 'add_to_cart', 'meta']);

        // Prepare product data
        $product_data = array(
            'id' => $product_id,
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
            // Categories with HTML links
            'categories_html' => wc_get_product_category_list($product_id),
            'categories_text' => wp_strip_all_tags(wc_get_product_category_list($product_id)),
            // Tags with HTML links
            'tags_html' => wc_get_product_tag_list($product_id),
            'tags_text' => wp_strip_all_tags(wc_get_product_tag_list($product_id)),
            // Brands with HTML links (if using a brand taxonomy or plugin)
            'brands_html' => '',
            'brands_text' => '',
        );

        // Get brands - this depends on your setup
        // Option 1: If using a custom taxonomy 'product_brand'
        $brand_terms = get_the_terms($product->get_id(), 'product_brand');
        if ($brand_terms && !is_wp_error($brand_terms)) {
            $brand_links = array();
            $brand_names = array();
            foreach ($brand_terms as $brand) {
                $brand_links[] = '<a href="' . get_term_link($brand) . '">' . $brand->name . '</a>';
                $brand_names[] = $brand->name;
            }
            $product_data['brands_html'] = implode(', ', $brand_links);
            $product_data['brands_text'] = implode(', ', $brand_names);
        }

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
        echo '<div class="rmenu-product-data" style="display:none;" data-product-info="' . esc_attr(wp_json_encode($product_data)) . '"></div>';
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts()
    {

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
        $onepaquc_ajax_add_to_cart = get_option('rmenu_quick_view_ajax_add_to_cart', 1);
        $close_on_add = false;
        $keyboard_nav = get_option('rmenu_quick_view_keyboard_nav', 1);
        $effect =  'fade';
        $mobile_optimize = false;
        $debug_mode = get_option('rmenu_quick_view_debug_mode', 0);
        $lightbox = get_option('rmenu_quick_view_enable_lightbox', 1);
        $elements_in_popup = get_option('rmenu_quick_view_content_elements', ['image', 'title', 'rating', 'price', 'excerpt', 'add_to_cart', 'meta']);

        // Localize script with settings
        wp_localize_script('rmenu-quick-view-scripts', 'rmenu_quick_view_params', array(
            'onepaquc_ajax_add_to_cart' => (bool) $onepaquc_ajax_add_to_cart,
            'close_on_add' => (bool) $close_on_add,
            'keyboard_nav' => (bool) $keyboard_nav,
            'effect' => $effect,
            'mobile_optimize' => (bool) $mobile_optimize,
            'debug' => (bool) $debug_mode,
            'lightbox' => (bool) $lightbox,
            'elements_in_popup' => $elements_in_popup,
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => esc_js(wp_create_nonce('rmenu_quick_view_nonce')),
            'i18n' => array(
                'close' => get_option('rmenu_quick_view_close_text') !== '' ? get_option('rmenu_quick_view_close_text') : 'Close',
                'prev' => get_option('rmenu_quick_view_prev_text') !== '' ? get_option('rmenu_quick_view_prev_text') : 'Previous Product',
                'next' => get_option('rmenu_quick_view_next_text') !== '' ? get_option('rmenu_quick_view_next_text') : 'Next Product',
                'add_to_cart' => esc_html__('Add to cart', 'one-page-quick-checkout-for-woocommerce'),
                'select_options' => esc_html__('Select options', 'one-page-quick-checkout-for-woocommerce'),
                'view_details' => get_option('rmenu_quick_view_details_text') !== '' ? get_option('rmenu_quick_view_details_text') : 'View Full Details',
                'out_of_stock' => esc_html__('Out of stock', 'one-page-quick-checkout-for-woocommerce'),
                'error_loading' => esc_html__('Error loading product information. Please try again.', 'one-page-quick-checkout-for-woocommerce'),
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
    private function add_dynamic_css()
    {
        $button_style = get_option('rmenu_quick_view_button_style', 'default');

        if ($button_style === 'default') {
            return; // Only add dynamic CSS for custom style
        }
        $button_color = get_option('rmenu_quick_view_button_color', '#000');
        $text_color = get_option('rmenu_quick_view_text_color', '#ffffff');

        $custom_css = "
            .opqvfw-btn {
                background-color: {$button_color}4a !important;
                color: {$text_color} !important;
            }
            .opqvfw-btn:hover {
                background-color: {$button_color} !important;
                color: {$text_color} !important;
            }
        ";

        wp_add_inline_style('rmenu-quick-view-styles', $custom_css);
    }

    /**
     * Add quick view modal container to footer
     */
    public function quick_view_modal_container()
    {
    ?>
        <div class="opqvfw-modal-container">
            <div class="opqvfw-modal-overlay"></div>
            <div class="opqvfw-modal">
                <div class="rmenu-quick-view-close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 5 5 15M5 5l10 10" />
                    </svg>
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
                        <svg width="22" height="22" viewBox="0 0 1.32 1.32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#fff" fill-opacity=".01" d="M0 0h1.32v1.32H0z" />
                            <path d="M.853.99.523.66l.33-.33" stroke="#fff" stroke-width=".11" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span class="screen-reader-text"><?php echo esc_html(get_option('rmenu_quick_view_prev_text', 'Previous Product')); ?></span>
                    </a>
                    <a href="#" class="rmenu-quick-view-next">
                        <svg width="22" height="22" viewBox="0 0 1.32 1.32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#fff" fill-opacity=".01" d="M0 0h1.32v1.32H0z" />
                            <path d="m.522.33.33.33-.33.33" stroke="#fff" stroke-width=".11" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
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