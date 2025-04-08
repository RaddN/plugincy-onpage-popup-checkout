jQuery(document).ready(function ($) {

    $isonepagewidget = $('.checkout-popup').data('isonepagewidget');
    // Function to fetch and update cart contents
    function updateCartContent(isdrawer = true) {
        // get data values from the cart button & send them to the server <button class="rwc_cart-button" data-cart-icon="cart" data-product_title_tag="p" data-drawer-position="right" onclick="openCartDrawer('right')"></button>
        var cartIcon = $('.rwc_cart-button').data('cart-icon');
        var productTitleTag = $('.rwc_cart-button').data('product_title_tag');
        var drawerPosition = $('.rwc_cart-button').data('drawer-position');

        $.ajax({
            url: wc_cart_params.ajax_url,
            type: 'POST',
            data: { 
                action: 'plugincyopc_get_cart_content',
                cart_icon: cartIcon,
                product_title_tag: productTitleTag,
                drawer_position: drawerPosition
            },
            success: function (response) {
                if (response.success) {
                    $('.rmenu-cart').html(response.data.cart_html);
                    if (isdrawer) {
                        $('.cart-drawer').addClass('open');
                    }
                }
            }
        });
    }
    updateCartContent(false);
    updateCheckoutForm();
    // Event handler for adding/removing items from the cart
    $(document.body).on('added_to_cart removed_from_cart', function () {

        $isonepagewidget ? updateCartContent(false) : updateCartContent();
        updateCheckoutForm();
    });

    // Function to update the checkout form
    function updateCheckoutForm() {
        $.ajax({
            url: ajax_object.ajax_url,
            method: 'POST',
            data: { action: 'plugincyopc_update_checkout' },
            success: function (response) {
                if (response.success) {
                    // $('.checkout-popup').html(response.data.checkout_form);
                    $(document.body).trigger('plugincyopc_update_checkout');
                } else {
                    console.error('Error updating checkout:', response.data);
                }
            },
            error: function () {
                console.error('AJAX request failed.');
            }
        });
    }

    // Handle quantity change
    $('.rmenu-cart').on('change', '.item-quantity', function () {
        const cartItemKey = $(this).closest('.cart-item').find('.remove-item').data('cart-item-key');
        const quantity = $(this).val();

        $.ajax({
            url: wc_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'plugincyopc_update_cart_item_quantity',
                cart_item_key: cartItemKey,
                quantity: quantity
            },
            success: function (response) {
                if (response.success) {
                    updateCartContent();
                }
            }
        });
    });

    // Handle remove item
    $('.rmenu-cart').on('click', '.remove-item', function (e) {
        e.preventDefault();
        const cartItemKey = $(this).data('cart-item-key');

        $.ajax({
            url: wc_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'plugincyopc_remove_cart_item',
                cart_item_key: cartItemKey
            },
            success: function (response) {
                if (response.success) {
                    updateCartContent();
                }
            }
        });
    });
    // handle quantity change
    // Handle the plus button click
    $(document).on('click', '.checkout-qty-plus', function () {
        var input = $(this).prev('.checkout-qty-input');
        var val = parseFloat(input.val());
        var max = parseFloat(input.attr('max'));
        var step = parseFloat(input.attr('step')) || 1;

        if (max && (max <= val)) {
            input.val(max);
        } else {
            input.val(val + step);
        }

        updateQuantity($(this).data('cart-item'), val + step);
    });

    // Handle the minus button click
    $(document).on('click', '.checkout-qty-minus', function () {
        var input = $(this).next('.checkout-qty-input');
        var val = parseFloat(input.val());
        var min = parseFloat(input.attr('min')) || 1;
        var step = parseFloat(input.attr('step')) || 1;

        if (min && (min >= val)) {
            input.val(min);
        } else if (val > 0) {
            input.val(val - step);
        }

        updateQuantity($(this).data('cart-item'), Math.max(min, val - step));
    });

    // Handle direct input changes
    $(document).on('change', '.checkout-qty-input', function () {
        var val = parseFloat($(this).val());
        var min = parseFloat($(this).attr('min')) || 1;

        if (val < min) {
            $(this).val(min);
            val = min;
        }

        updateQuantity($(this).closest('.checkout-qty-btn').data('cart-item'), val);
    });

    // Function to update quantity
    function updateQuantity(cartItemKey, qty) {
        if (!cartItemKey) {
            return;
        }

        // Block the checkout while updating
        $('.woocommerce-checkout-review-order-table').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });

        // Update via AJAX
        $.ajax({
            type: 'POST',
            url: wc_checkout_params.ajax_url,
            data: {
                action: 'plugincyopc_update_cart_item_quantity',
                cart_item_key: cartItemKey,
                quantity: qty,
                security: wc_checkout_params.update_order_review_nonce
            },
            success: function () {
                $('body').trigger('plugincyopc_update_checkout');
            },
            complete: function () {
                $('.woocommerce-checkout-review-order-table').unblock();
            }
        });
    }

    // Handle click on remove item button
    $(document).on('click', '.remove-item-checkout', function (e) {
        e.preventDefault();
        var cartItemKey = $(this).data('cart-item');

        $.ajax({
            type: 'POST',
            url: wc_add_to_cart_params.ajax_url,
            data: {
                action: 'plugincyopc_remove_cart_item',
                cart_item_key: cartItemKey,
                security: wc_add_to_cart_params.wc_ajax_nonce
            },
            success: function (response) {
                // Get product IDs from data attribute
                var product_ids = $('.one-page-checkout-product-list').data('product-ids');

                // Refresh the list of products
                $.ajax({
                    type: 'POST',
                    url: wc_add_to_cart_params.ajax_url,
                    data: {
                        action: 'plugincyopc_refresh_checkout_product_list',
                        product_ids: product_ids
                    },
                    success: function (html) {
                        $('ul.one-page-checkout-product-list').html(html);

                        // Update checkbox states based on cart contents
                        $('.one-page-checkout-product-item').each(function () {
                            var productId = $(this).data('product-id');
                            var inCart = $(this).data('cart-item-key') !== '';
                            $(this).find('.one-page-checkout-product-checkbox').prop('checked', inCart);
                        });
                    }
                });

                // Update WooCommerce fragments
                if (response.fragments) {
                    $.each(response.fragments, function (key, value) {
                        $(key).replaceWith(value);
                    });
                }

                // Update checkout totals
                $(document.body).trigger('plugincyopc_update_checkout');
            }
        });
    });
});