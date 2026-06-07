<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
// ajaxhandler.php

/**
 * Return the WooCommerce cart or end the AJAX request with a safe error.
 *
 * @return WC_Cart
 */
function onepaquc_ajax_get_cart_or_error()
{
    $cart = function_exists('onepaquc_get_wc_cart') ? onepaquc_get_wc_cart() : null;
    if (!$cart) {
        wp_send_json_error(
            array('message' => esc_html__('The cart is not available. Please reload the page and try again.', 'one-page-quick-checkout-for-woocommerce')),
            503
        );
    }

    return $cart;
}

// update cart content

add_action('wp_ajax_onepaquc_get_cart_content', 'onepaquc_get_cart_content');
add_action('wp_ajax_nopriv_onepaquc_get_cart_content', 'onepaquc_get_cart_content');
function onepaquc_get_cart_content()
{
    check_ajax_referer('get_cart_content_none', 'nonce');
    $cart = onepaquc_ajax_get_cart_or_error();

    //get the values from the ajax request cart_icon: cartIcon, product_title_tag: productTitleTag, drawer_position: drawerPosition
    $cartIcon = isset($_POST['cart_icon']) && is_scalar($_POST['cart_icon']) ? sanitize_key(wp_unslash($_POST['cart_icon'])) : 'cart';
    $cartIcon = in_array($cartIcon, array('cart', 'shopping-bag', 'basket'), true) ? $cartIcon : 'cart';
    $productTitleTag = isset($_POST['product_title_tag']) && is_scalar($_POST['product_title_tag']) ? onepaquc_sanitize_heading_tag(sanitize_text_field(wp_unslash($_POST['product_title_tag'])), 'h2') : 'h2';
    $drawerPosition = isset($_POST['drawer_position']) && is_scalar($_POST['drawer_position']) ? sanitize_key(wp_unslash($_POST['drawer_position'])) : 'right';
    $drawerPosition = in_array($drawerPosition, array('left', 'right'), true) ? $drawerPosition : 'right';
    ob_start();

    // Use include to load the template from your plugin's directory
    onepaquc_cart($drawerPosition, $cartIcon, $productTitleTag);

    $cart_html = ob_get_clean();

    // Send response with cart HTML and count
    wp_send_json_success([
        'cart_html' => $cart_html,
        'cart_count' => $cart->get_cart_contents_count()
    ]);
}

// update quantity

add_action('wp_ajax_onepaquc_update_cart_item_quantity', 'onepaquc_update_cart_item_quantity');
add_action('wp_ajax_nopriv_onepaquc_update_cart_item_quantity', 'onepaquc_update_cart_item_quantity');
function onepaquc_update_cart_item_quantity()
{
    check_ajax_referer('update_cart_item_quantity', 'nonce');
    $cart = onepaquc_ajax_get_cart_or_error();
    $cart_item_key = isset($_POST['cart_item_key']) && is_scalar($_POST['cart_item_key']) ? sanitize_text_field(wp_unslash($_POST['cart_item_key'])) : '';
    $raw_quantity = isset($_POST['quantity']) && is_scalar($_POST['quantity']) ? sanitize_text_field(wp_unslash($_POST['quantity'])) : 0;
    $quantity = function_exists('wc_stock_amount') ? wc_stock_amount($raw_quantity) : (int) $raw_quantity;

    $cart_contents = $cart->get_cart();
    if ('' === $cart_item_key || !isset($cart_contents[$cart_item_key]) || $quantity < 0) {
        wp_send_json_error(array('message' => esc_html__('Invalid cart item or quantity.', 'one-page-quick-checkout-for-woocommerce')), 400);
    }

    if ($cart->set_quantity($cart_item_key, $quantity)) {

        // Get updated cart data
        $subtotal = wc_price($cart->get_subtotal());
        $total = wc_price($cart->get_total('raw'));
        $cart_count = $cart->get_cart_contents_count();

        wp_send_json_success(array(
            'subtotal' => $subtotal,
            'discount_total' => $cart->get_discount_total(),
            'cart_count' => $cart_count,
            'total' => $total
        ));
    } else {
        wp_send_json_error(array('message' => esc_html__('Could not update quantity.', 'one-page-quick-checkout-for-woocommerce')), 400);
    }
}



// remove cart item(s)
add_action('wp_ajax_onepaquc_remove_cart_item', 'onepaquc_handle_remove_cart_item');
add_action('wp_ajax_nopriv_onepaquc_remove_cart_item', 'onepaquc_handle_remove_cart_item');
function onepaquc_handle_remove_cart_item()
{
    check_ajax_referer('remove_cart_item', 'nonce');
    $cart = onepaquc_ajax_get_cart_or_error();
    $cart_item_keys = array();
    if (isset($_POST['cart_item_key']) && is_array($_POST['cart_item_key'])) {
        $cart_item_keys = wp_unslash($_POST['cart_item_key']); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each array item is sanitized and validated below.
    } elseif (isset($_POST['cart_item_key']) && is_scalar($_POST['cart_item_key'])) {
        $cart_item_keys = array(sanitize_text_field(wp_unslash($_POST['cart_item_key'])));
    } else {
        wp_send_json_error(array('message' => esc_html__('No cart item key provided.', 'one-page-quick-checkout-for-woocommerce')), 400);
    }
    $sanitized_keys = array();
    foreach ($cart_item_keys as $key) {
        if (is_scalar($key)) {
            $sanitized_key = sanitize_text_field(wp_unslash($key));
            if ('' !== $sanitized_key) {
                $sanitized_keys[] = $sanitized_key;
            }
        }
    }
    $sanitized_keys = array_values(array_unique($sanitized_keys));

    $failed_keys = array();
    $cart_contents = $cart->get_cart();

    foreach ($sanitized_keys as $key) {
        if (!isset($cart_contents[$key]) || !$cart->remove_cart_item($key)) {
            $failed_keys[] = $key;
        }
    }

    // Get updated cart data
    $subtotal = wc_price($cart->get_subtotal());
    $total = wc_price($cart->get_total('raw'));

    if (empty($failed_keys)) {
        wp_send_json_success(array(
            'subtotal' => $subtotal,
            'discount_total' => $cart->get_discount_total(),
            'total' => $total
        ));
    } else {
        wp_send_json_error(array(
            'message' => 'Could not remove some items.',
            'failed_keys' => $failed_keys
        ));
    }
}

// update checkout form on ajax complete
function onepaquc_update_checkout_form()
{
    check_ajax_referer('onepaquc_update_checkout', 'nonce');
    onepaquc_ajax_get_cart_or_error();

    ob_start();

    // Use include to load the template from your plugin's directory
    onepaquc_rmenu_checkout();

    $checkout_form = ob_get_clean();

    // Send response with cart HTML and count
    wp_send_json_success(array('checkout_form' => $checkout_form));
}

add_action('wp_ajax_onepaquc_update_checkout', 'onepaquc_update_checkout_form');
add_action('wp_ajax_nopriv_onepaquc_update_checkout', 'onepaquc_update_checkout_form');

// Add AJAX handler for refreshing product list
add_action('wp_ajax_onepaquc_refresh_checkout_product_list', 'onepaquc_refresh_checkout_product_list');
add_action('wp_ajax_nopriv_onepaquc_refresh_checkout_product_list', 'onepaquc_refresh_checkout_product_list');

function onepaquc_refresh_checkout_product_list()
{
    // Check nonce for security
    check_ajax_referer('onepaquc_refresh_checkout_product_list', 'nonce');
    $cart = onepaquc_ajax_get_cart_or_error();
    if (!isset($_POST['product_ids']) || !is_scalar($_POST['product_ids'])) {
        wp_send_json_error(array('message' => esc_html__('Invalid product list.', 'one-page-quick-checkout-for-woocommerce')), 400);
    }

    $product_ids = explode(',', sanitize_text_field(wp_unslash($_POST['product_ids'])));
    $product_ids = array_slice(array_values(array_unique(array_filter(array_map('absint', $product_ids)))), 0, 200);
    $cart_items  = $cart->get_cart();

    ob_start();

    // Loop through each product ID
    foreach ($product_ids as $item_id) {
        $product_id = absint($item_id);
        $product = wc_get_product($product_id);

        if ($product instanceof WC_Product && 'publish' === $product->get_status()) {
            $product_name = $product->get_name();
            $product_image = $product->get_image(array(60, 60), array('class' => 'one-page-checkout-product-image'));

            // Check if product is in cart
            $in_cart = false;
            $cart_item_key = '';

            foreach ($cart_items as $key => $cart_item) {
                if (is_array($cart_item) && isset($cart_item['product_id']) && (int) $cart_item['product_id'] === $product_id) {
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
    global $onepaquc_onepaquc_allowed_tags;

    echo wp_kses($html, $onepaquc_onepaquc_allowed_tags);
    wp_die();
}


add_action('wp_ajax_woocommerce_clear_cart', 'onepaquc_clear_cart');
add_action('wp_ajax_nopriv_woocommerce_clear_cart', 'onepaquc_clear_cart');

function onepaquc_clear_cart()
{
    check_ajax_referer('onepaquc_clear_cart', 'nonce');
    $cart = onepaquc_ajax_get_cart_or_error();
    $cart->empty_cart();
    wp_send_json_success();
}


// add to cart

add_action('wp_ajax_onepaquc_ajax_add_to_cart', 'onepaquc_ajax_add_to_cart');
add_action('wp_ajax_nopriv_onepaquc_ajax_add_to_cart', 'onepaquc_ajax_add_to_cart');


function onepaquc_ajax_add_to_cart()
{
    check_ajax_referer('rmenu-ajax-nonce', 'nonce');
    $cart = onepaquc_ajax_get_cart_or_error();

    $raw_product_id = isset($_POST['product_id']) && is_scalar($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core compatibility hook.
    $product_id = apply_filters('woocommerce_add_to_cart_product_id', $raw_product_id);
    $product_id = is_numeric($product_id) ? absint($product_id) : 0;

    // Get default quantity from settings if quantity is not provided
    $default_qty = 1;

    // Use posted quantity if available, otherwise use default
    $posted_quantity = !empty($_POST['quantity']) && is_scalar($_POST['quantity'])
        ? sanitize_text_field(wp_unslash($_POST['quantity']))
        : '';
    $quantity = '' === $posted_quantity
        ? $default_qty
        : max(1, function_exists('wc_stock_amount') ? wc_stock_amount($posted_quantity) : (int) $posted_quantity);

    $variation_id = empty($_POST['variation_id']) || !is_scalar($_POST['variation_id']) ? 0 : absint(wp_unslash($_POST['variation_id']));
    $variations = array();
    if (!empty($_POST['variations']) && is_array($_POST['variations'])) {
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Variation keys and values are sanitized individually below.
        foreach (wp_unslash($_POST['variations']) as $attribute => $value) {
            if (!is_scalar($attribute) || !is_scalar($value)) {
                continue;
            }
            $attribute = sanitize_key($attribute);
            if (0 !== strpos($attribute, 'attribute_')) {
                $attribute = 'attribute_' . $attribute;
            }
            $variations[$attribute] = wc_clean($value);
        }
    }

    $product = function_exists('wc_get_product') ? wc_get_product($product_id) : false;
    if (!$product instanceof WC_Product || 'publish' !== $product->get_status() || !$product->is_purchasable()) {
        wp_send_json_error(array('message' => esc_html__('This product cannot be added to the cart.', 'one-page-quick-checkout-for-woocommerce')), 400);
    }

    if ($variation_id) {
        $variation_product = wc_get_product($variation_id);
        if (!$variation_product instanceof WC_Product_Variation || $variation_product->get_parent_id() !== $product_id) {
            wp_send_json_error(array('message' => esc_html__('Invalid product variation.', 'one-page-quick-checkout-for-woocommerce')), 400);
        }
    }

    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core compatibility hook.
    $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations);

    if ($passed_validation && $cart->add_to_cart($product_id, $quantity, $variation_id, $variations)) {

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core compatibility hook.
        do_action('woocommerce_ajax_added_to_cart', $product_id);

        // Get product name for the message
        $product_name = $product ? $product->get_name() : '';

        // Get cart URL
        $cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : '';

        // Get checkout URL
        $checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : '';

        // Render cart items
        $cart_items_html = "";
        $cart_count = $cart->get_cart_contents_count();
        $cart_items = $cart->get_cart();

        foreach ($cart_items as $cart_item_key => $cart_item) {
            $_product = is_array($cart_item) && isset($cart_item['data']) ? $cart_item['data'] : null;
            if (!$_product instanceof WC_Product) {
                continue;
            }
            $thumbnail = $_product->get_image();
            $product_price = wc_price($_product->get_price());
            $product_quantity = isset($cart_item['quantity']) && is_numeric($cart_item['quantity']) ? (float) $cart_item['quantity'] : 0;
            $product_total = wc_price($_product->get_price() * $product_quantity);

            $cart_items_html .= '<div class="cart-item" data-cart-item-key="' . esc_attr($cart_item_key) . '">
                <div class="item-select">
                    <input type="checkbox" class="item-checkbox" data-cart-item-key="' . esc_attr($cart_item_key) . '">
                </div>
                <div class="thumbnail">
                    ' . wp_kses($thumbnail, array(
                "img" => array(
                    "src" => array(),
                    "alt" => array(),
                    "class" => array(),
                ),
            )) . '
                    <button class="remove-item" data-cart-item-key="' . esc_attr($cart_item_key) . '"><svg style="width: 16px; fill: #ff0000;" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M6 18L18 6M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                    </button>
                </div>
                <div class="item-details">
                    <div style="display:flex;gap:1rem;">
                        <p class="item-title">' . esc_html($_product->get_name()) . '</p>
                        <p class="item-price">' . wp_kses_post($product_price) . '</p>
                    </div>
                    <div class="quantity-controls">
                        <button class="quantity-btn minus" data-action="minus" data-cart-item-key="' . esc_attr($cart_item_key) . '">-</button>
                        <input type="number" class="item-quantity" value="' . esc_attr($product_quantity) . '" min="1" data-cart-item-key="' . esc_attr($cart_item_key) . '">
                        <button class="quantity-btn plus" data-action="plus" data-cart-item-key="' . esc_attr($cart_item_key) . '">+</button>
                    </div>

                </div>
            </div>';
        }



        // Get redirect option
        $redirect_option = get_option('rmenu_redirect_after_add', 'none');
        $redirect_url = 'none';

        if ($redirect_option === 'cart') {
            $redirect_url = $cart_url;
        } elseif ($redirect_option === 'checkout') {
            $redirect_url = $checkout_url;
        }

        $response = array(
            'success' => true,
            'product_name' => $product_name,
            'cart_url' => $cart_url,
            'checkout_url' => $checkout_url,
            'cart_total' => $cart->get_cart_total(),
            'cart_count' => $cart_count,
            'cart_items_html' => $cart_items_html,
            'redirect' => $redirect_option !== 'none',
            'redirect_url' => $redirect_url
        );

        wp_send_json($response);
    } else {
        $data = array(
            'error' => true,
            'message' => esc_html__('Error adding product to cart', 'one-page-quick-checkout-for-woocommerce')
        );

        wp_send_json($data);
    }

    wp_die();
}


// coupon ajax handler
add_action('wp_ajax_apply_coupon', 'onepaquc_apply_coupon');
add_action('wp_ajax_nopriv_apply_coupon', 'onepaquc_apply_coupon');

function onepaquc_apply_coupon()
{
    check_ajax_referer('apply-coupon', 'security');
    $cart = onepaquc_ajax_get_cart_or_error();

    $coupon_code = isset($_POST['coupon_code']) && is_scalar($_POST['coupon_code']) ? sanitize_text_field(wp_unslash($_POST['coupon_code'])) : '';
    if ('' === $coupon_code) {
        wp_send_json_error(array('message' => esc_html__('Please enter a coupon code.', 'one-page-quick-checkout-for-woocommerce')), 400);
    }

    // Apply coupon
    $result = $cart->apply_coupon($coupon_code);

    if ($result) {
        // Get updated cart data
        $subtotal = wc_price($cart->get_subtotal());
        $discount_total = wc_price($cart->get_discount_total());
        $total = wc_price($cart->get_total('raw'));

        wp_send_json_success(array(
            'subtotal' => $subtotal,
            'discount_total' => $cart->get_discount_total(),
            'total' => $total
        ));
    } else {
        wp_send_json_error(array('message' => 'Invalid coupon code.'));
    }
}

add_action('wp_ajax_remove_coupon', 'onepaquc_remove_coupon');
add_action('wp_ajax_nopriv_remove_coupon', 'onepaquc_remove_coupon');

function onepaquc_remove_coupon()
{
    check_ajax_referer('apply-coupon', 'security');
    $cart = onepaquc_ajax_get_cart_or_error();

    $coupon_code = isset($_POST['coupon_code']) && is_scalar($_POST['coupon_code']) ? sanitize_text_field(wp_unslash($_POST['coupon_code'])) : '';
    if ('' === $coupon_code || !in_array(wc_format_coupon_code($coupon_code), $cart->get_applied_coupons(), true)) {
        wp_send_json_error(array('message' => esc_html__('Coupon is not applied to the cart.', 'one-page-quick-checkout-for-woocommerce')), 400);
    }

    // Remove coupon
    if (!$cart->remove_coupon($coupon_code)) {
        wp_send_json_error(array('message' => esc_html__('Could not remove the coupon.', 'one-page-quick-checkout-for-woocommerce')), 400);
    }

    // Get updated cart data
    $subtotal = wc_price($cart->get_subtotal());
    $discount_total = wc_price($cart->get_discount_total());
    $total = wc_price($cart->get_total('raw'));

    wp_send_json_success(array(
        'subtotal' => $subtotal,
        'discount_total' => $cart->get_discount_total(),
        'total' => $total
    ));
}


/**
 * AJAX handler for getting all products quick view data
 */
function onepaquc_get_all_products_quick_view() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !is_scalar($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'rmenu_quick_view_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }
    
    // Get product IDs from the request
    $raw_product_ids = isset($_POST['product_ids']) && is_array($_POST['product_ids'])
        ? wp_unslash($_POST['product_ids']) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Numeric product IDs are allowlisted through is_numeric() and absint() below.
        : array();
    $product_ids = array_values(array_unique(array_filter(array_map('absint', array_filter($raw_product_ids, 'is_numeric')))));
    $max_products = apply_filters('onepaquc_quick_view_max_products', 50);
    $max_products = is_numeric($max_products) ? max(1, min(100, absint($max_products))) : 50;
    $product_ids  = array_slice($product_ids, 0, $max_products);
    
    if (empty($product_ids)) {
        wp_send_json_error(array('message' => 'No product IDs provided'));
    }
    
    $products_data = array();
    
    foreach ($product_ids as $product_id) {
        $product = wc_get_product($product_id);
        
        if (!$product instanceof WC_Product || 'publish' !== $product->get_status() || !$product->is_visible()) {
            continue; // Skip if product doesn't exist
        }
        
        // Get product images
        $images = array();
        $attachment_ids = $product->get_gallery_image_ids();
        
        // Add featured image first
        $featured_image_id = $product->get_image_id();
        if ($featured_image_id) {
            array_unshift($attachment_ids, $featured_image_id);
        }
        
        // If no images, add placeholder
        if (empty($attachment_ids)) {
            $images[] = array(
                'id'    => 0,
                'src'   => wc_placeholder_img_src(),
                'thumb' => wc_placeholder_img_src('thumbnail'),
                'full'  => wc_placeholder_img_src('full'),
                'alt'   => esc_html__('Placeholder', 'one-page-quick-checkout-for-woocommerce')
            );
        } else {
            foreach ($attachment_ids as $attachment_id) {
                $image_src = wp_get_attachment_image_src($attachment_id, 'woocommerce_single');
                $thumb_src = wp_get_attachment_image_src($attachment_id, 'woocommerce_thumbnail');
                $full_src  = wp_get_attachment_image_src($attachment_id, 'full');
                
                $images[] = array(
                    'id'    => $attachment_id,
                    'src'   => $image_src ? $image_src[0] : wc_placeholder_img_src(),
                    'thumb' => $thumb_src ? $thumb_src[0] : wc_placeholder_img_src('thumbnail'),
                    'full'  => $full_src ? $full_src[0] : wc_placeholder_img_src('full'),
                    'alt'   => get_post_meta($attachment_id, '_wp_attachment_image_alt', true)
                );
            }
        }
        
        // Get product categories
        $categories_html = '';
        $categories = get_the_terms($product_id, 'product_cat');
        if ($categories && !is_wp_error($categories)) {
            $category_links = array();
            foreach ($categories as $category) {
                $term_link = get_term_link($category);
                if (!is_wp_error($term_link)) {
                    $category_links[] = '<a href="' . esc_url($term_link) . '">' . esc_html($category->name) . '</a>';
                }
            }
            $categories_html = implode(', ', $category_links);
        }
        
        // Get product brands (if using a brands plugin)
        $brands_html = '';
        if (taxonomy_exists('product_brand')) {
            $brands = get_the_terms($product_id, 'product_brand');
            if ($brands && !is_wp_error($brands)) {
                $brand_links = array();
                foreach ($brands as $brand) {
                    $term_link = get_term_link($brand);
                    if (!is_wp_error($term_link)) {
                        $brand_links[] = '<a href="' . esc_url($term_link) . '">' . esc_html($brand->name) . '</a>';
                    }
                }
                $brands_html = implode(', ', $brand_links);
            }
        }
        
        // Get product tags
        $tags_html = '';
        $tags = get_the_terms($product_id, 'product_tag');
        if ($tags && !is_wp_error($tags)) {
            $tag_links = array();
            foreach ($tags as $tag) {
                $term_link = get_term_link($tag);
                if (!is_wp_error($term_link)) {
                    $tag_links[] = '<a href="' . esc_url($term_link) . '">' . esc_html($tag->name) . '</a>';
                }
            }
            $tags_html = implode(', ', $tag_links);
        }
        
        // Compile product data
        $products_data[$product_id] = array(
            'id'                   => $product_id,
            'title'                => $product->get_name(),
            'permalink'            => $product->get_permalink(),
            'price_html'           => $product->get_price_html(),
            'excerpt'              => $product->get_short_description(),
            'rating_html'          => wc_get_rating_html($product->get_average_rating()),
            'type'                 => $product->get_type(),
            'sku'                  => $product->get_sku(),
            'images'               => $images,
            'is_in_stock'          => $product->is_in_stock(),
            'is_purchasable'       => $product->is_purchasable(),
            'min_purchase_quantity' => $product->get_min_purchase_quantity(),
            'max_purchase_quantity' => $product->get_max_purchase_quantity(),
            'brands_html'          => $brands_html,
            'categories_html'      => $categories_html,
            'tags_html'            => $tags_html
        );
    }
    
    // Send the response
    wp_send_json_success($products_data);
}
add_action('wp_ajax_rmenu_get_all_products_quick_view', 'onepaquc_get_all_products_quick_view');
add_action('wp_ajax_nopriv_rmenu_get_all_products_quick_view', 'onepaquc_get_all_products_quick_view');
