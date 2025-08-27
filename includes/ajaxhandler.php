<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
// ajaxhandler.php

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
        $cart = WC()->cart;

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
        wp_send_json_error('Could not update quantity.');
    }
}



// remove cart item(s)
add_action('wp_ajax_onepaquc_remove_cart_item', 'onepaquc_handle_remove_cart_item');
add_action('wp_ajax_nopriv_onepaquc_remove_cart_item', 'onepaquc_handle_remove_cart_item');
function onepaquc_handle_remove_cart_item()
{
    check_ajax_referer('remove_cart_item', 'nonce');
    $cart_item_keys = array();
    if (isset($_POST['cart_item_key']) && is_array($_POST['cart_item_key'])) {
        $cart_item_keys = array_map('sanitize_text_field', wp_unslash($_POST['cart_item_key']));
    } elseif (isset($_POST['cart_item_key'])) {
        $cart_item_keys = array(sanitize_text_field(wp_unslash($_POST['cart_item_key'])));
    } else {
        wp_send_json_error('No cart item key provided.');
    }
    $sanitized_keys = array();
    foreach ($cart_item_keys as $key) {
        $sanitized_keys[] = sanitize_text_field(wp_unslash($key));
    }

    $failed_keys = array();

    foreach ($sanitized_keys as $key) {
        if (!WC()->cart->remove_cart_item($key)) {
            $failed_keys[] = $key;
        }
    }


    $cart = WC()->cart;

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


add_action('wp_ajax_woocommerce_clear_cart', 'onepaquc_clear_cart');
add_action('wp_ajax_nopriv_woocommerce_clear_cart', 'onepaquc_clear_cart');

function onepaquc_clear_cart()
{
    WC()->cart->empty_cart();
    wp_send_json_success();
}


// add to cart

add_action('wp_ajax_onepaquc_ajax_add_to_cart', 'onepaquc_ajax_add_to_cart');
add_action('wp_ajax_nopriv_onepaquc_ajax_add_to_cart', 'onepaquc_ajax_add_to_cart');


function onepaquc_ajax_add_to_cart()
{
    check_ajax_referer('rmenu-ajax-nonce', 'nonce');

    $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint(isset($_POST['product_id']) ? $_POST['product_id'] : 0));

    // Get default quantity from settings if quantity is not provided
    $default_qty = 1;

    // Use posted quantity if available, otherwise use default
    $quantity = empty($_POST['quantity']) ? $default_qty : (int) sanitize_text_field(wp_unslash($_POST['quantity']));

    $variation_id = empty($_POST['variation_id']) ? 0 : absint($_POST['variation_id']);
    $variations = !empty($_POST['variations']) ? array_map('sanitize_text_field', wp_unslash($_POST['variations'])) : array();

    $product_status = get_post_status($product_id);

    $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations);

    if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variations) && 'publish' === $product_status) {

        do_action('woocommerce_ajax_added_to_cart', $product_id);

        // Get product name for the message
        $product = wc_get_product($product_id);
        $product_name = $product ? $product->get_name() : '';

        // Get cart URL
        $cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : WC()->cart->get_cart_url();

        // Get checkout URL
        $checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : WC()->cart->get_checkout_url();

        // Render cart items
        $cart_items_html = "";
        $cart_count = WC()->cart->get_cart_contents_count();
        $cart_items = WC()->cart->get_cart();

        foreach ($cart_items as $cart_item_key => $cart_item) {
            $_product = $cart_item['data'];
            $thumbnail = $_product->get_image();
            $product_price = wc_price($_product->get_price());
            $product_quantity = $cart_item['quantity'];
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
            'cart_total' => WC()->cart->get_cart_total(),
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

    $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field(wp_unslash($_POST['coupon_code'])) : '';

    // Apply coupon
    $cart = WC()->cart;
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

    $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field(wp_unslash($_POST['coupon_code'])) : '';

    // Remove coupon
    $cart = WC()->cart;
    $cart->remove_coupon($coupon_code);

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
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'rmenu_quick_view_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }
    
    // Get product IDs from the request
    $product_ids = isset($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : array();
    
    if (empty($product_ids)) {
        wp_send_json_error(array('message' => 'No product IDs provided'));
    }
    
    $products_data = array();
    
    foreach ($product_ids as $product_id) {
        $product = wc_get_product($product_id);
        
        if (!$product) {
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
                $category_links[] = '<a href="' . esc_url(get_term_link($category)) . '">' . esc_html($category->name) . '</a>';
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
                    $brand_links[] = '<a href="' . esc_url(get_term_link($brand)) . '">' . esc_html($brand->name) . '</a>';
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
                $tag_links[] = '<a href="' . esc_url(get_term_link($tag)) . '">' . esc_html($tag->name) . '</a>';
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