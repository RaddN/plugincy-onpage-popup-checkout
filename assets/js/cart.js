jQuery(document).ready(function($) {
    // Function to fetch and update cart contents
    function updateCartContent() {
        $.ajax({
            url: wc_cart_params.ajax_url,
            type: 'POST',
            data: { action: 'get_cart_content' },
            success: function(response) {
                if (response.success) {
                    // Update cart drawer content and item count
                    $('.rmenu-cart').html(response.data.cart_html);
                    $('.cart-drawer').addClass('open');
                }
            }
        });
    }

    $(document.body).on('added_to_cart removed_from_cart', function () {
        updateCartContent();
        // $(".cart-drawer").addClass("right open");
    });

    // on ajax complete checkout form update
        $(document.body).on('added_to_cart removed_from_cart', function() {
            // Perform AJAX request
            $.ajax({
                url: ajax_object.ajax_url,
                method: 'POST',
                data: {
                    action: 'update_checkout'
                },
                success: function(response) {
                    if (response.success) {
                        // Update the checkout form with the new content
                        $('.checkout-popup').html(response.data.checkout_form);
                    } else {
                        // Handle errors if necessary
                        console.error('Error updating checkout:', response.data);
                    }
                },
                error: function() {
                    console.error('AJAX request failed.');
                }
            });
        });

    // Handle quantity change
    $('.rmenu-cart').on('change', '.item-quantity', function() {
        const cartItemKey = $(this).closest('.cart-item').find('.remove-item').data('cart-item-key');
        const quantity = $(this).val();

        $.ajax({
            url: wc_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'update_cart_item_quantity',
                cart_item_key: cartItemKey,
                quantity: quantity
            },
            success: function(response) {
                if (response.success) {
                    updateCartContent();
                    // $(".cart-drawer").addClass("right open");
                }
            }
        });
    });

    // Handle remove item
    $('.rmenu-cart').on('click', '.remove-item', function(e) {
        e.preventDefault();
        const cartItemKey = $(this).data('cart-item-key');

        $.ajax({
            url: wc_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'remove_cart_item',
                cart_item_key: cartItemKey
            },
            success: function(response) {
                if (response.success) {
                    updateCartContent();
                    // $(".cart-drawer").addClass("right open");
                }
            }
        });
    });
});