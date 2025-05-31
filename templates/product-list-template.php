<?php
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

 // product list template
 // shortcode to display one page checkout [plugincy_one_page_checkout product_ids="" template="product-list"]
?>
<div class = "product-list-template">
<div class="one-page-checkout-container">
    <div class="one-page-checkout-products">
        <h2><?php echo esc_html__('Products', 'one-page-quick-checkout-for-woocommerce'); ?></h2>
        <ul class="one-page-checkout-product-list" data-product-ids="<?php echo esc_attr($atts['product_ids']); ?>">
            <?php
            // Remove any whitespace from product IDs
            $product_ids = array_map('trim', $product_ids);
            
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
                    // Check if WooCommerce cart is initialized and not empty
                    if( WC()->cart && !WC()->cart->is_empty() ) {
                        // Loop through cart items to check if the product is already in the cart
                    foreach (WC()->cart->get_cart() as $key => $cart_item) {
                        if ($cart_item['product_id'] == $product_id) {
                            $in_cart = true;
                            $cart_item_key = $key;
                            break;
                        }
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
            ?>
        </ul>
        
        <?php onepaquc_rmenu_checkout_popup(true); ?>
    </div>
</div>
</div>

<?php $inline_script = "
jQuery(document).ready(function($) {
    // Function to add product to cart using WooCommerce AJAX
    function addProductToCart(product_id, item) {
        item.addClass('loading');
        
        $.ajax({
            type: 'POST',
            url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
            data: {
                product_id: product_id,
                quantity: 1
            },
            success: function(response) {
                if (response.error && response.product_url) {
                    window.location = response.product_url;
                    return;
                }
                
                // Update checkbox state
                item.find('.one-page-checkout-product-checkbox').prop('checked', true);
                
                // Update cart item key after adding to cart
                if (response && response.cart_item_key) {
                    item.attr('data-cart-item-key', response.cart_item_key);
                }
                
                // Update cart fragments
                if (response.fragments) {
                    $.each(response.fragments, function(key, value) {
                        $(key).replaceWith(value);
                    });
                }
                
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, null]);
                item.removeClass('loading');
            },
            error: function() {
                item.removeClass('loading');
            }
        });
    }
    
    // Function to remove product from cart
    function removeProductFromCart(product_id, cart_item_key, item) {
        item.addClass('loading');
        
        $.ajax({
            type: 'POST',
            url: wc_add_to_cart_params.ajax_url,
            data: {
                action: 'onepaquc_remove_cart_item',
                cart_item_key: cart_item_key,
                nonce: wc_add_to_cart_params.remove_cart_item
            },
            success: function(response) {
                $(document.body).trigger('update_checkout');
            }
        });
    }
    
    // Handle checkbox clicks
    $(document).on('change', '.one-page-checkout-product-checkbox', function(e) {
        e.stopPropagation();
        var product_id = $(this).val();
        var item = $(this).closest('.one-page-checkout-product-item');
        var cart_item_key = item.attr('data-cart-item-key');
        
        if($(this).is(':checked')) {
            addProductToCart(product_id, item);
        } else {
            if (cart_item_key) {
                removeProductFromCart(product_id, cart_item_key, item);
            } else {
                // Then try again after a short delay
                setTimeout(function() {
                    cart_item_key = item.attr('data-cart-item-key');
                    if (cart_item_key) {
                        removeProductFromCart(product_id, cart_item_key, item);
                    } else {
                        item.removeClass('loading');
                    }
                }, 500);
            }
        }
    });
    
    // Handle clicking on the product item (excluding checkbox)
    $(document).on('click', '.one-page-checkout-product-item', function(e) {
        if(!$(e.target).is('input:checkbox')) {
            var product_id = $(this).data('product-id');
            var item = $(this);
            var checkbox = $(this).find('.one-page-checkout-product-checkbox');
            var cart_item_key = $(this).attr('data-cart-item-key');
            
            // Toggle checkbox visually
            var newCheckedState = !checkbox.prop('checked');
            checkbox.prop('checked', newCheckedState);
            
            // Add or remove from cart based on new checkbox state
            if(newCheckedState) {
                addProductToCart(product_id, item);
            } else {
                if (cart_item_key) {
                    removeProductFromCart(product_id, cart_item_key, item);
                } else {
                    // Then try again after a short delay
                    setTimeout(function() {
                        cart_item_key = item.attr('data-cart-item-key');
                        if (cart_item_key) {
                            removeProductFromCart(product_id, cart_item_key, item);
                        } else {
                            item.removeClass('loading');
                        }
                    }, 500);
                }
            }
        }
    });

});";

    // Enqueue the inline script
    wp_add_inline_script('rmenu-cart-script', $inline_script,99);
