<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly


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