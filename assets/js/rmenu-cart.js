jQuery(document).ready(function($) {
    $(document).click(function(event) {
        // Check if the clicked target is not the cart drawer or its descendants
        if (!$(event.target).closest('.cart-drawer, .cart-button, .checkout-popup').length) {
            $('.cart-drawer').removeClass('open');
            $('.checkout-popup').hide();
            $('.overlay').hide(); // Hide overlay when cart is closed
        }
    });
    window.openCartDrawer = function(side) {
        $('.cart-drawer').removeClass('open');
        $('.cart-drawer').addClass('open');
        $('.overlay').show();
    };
    window.closeCheckoutPopup = function() {
        $('.checkout-popup').hide();
        $('.popup-message').text(''); // Clear message on close
        $('.overlay').hide();
        $('.cart-drawer').removeClass('open');
    };
    window.openCheckoutPopup = function() {
        $('.checkout-popup').show();
        $('.cart-drawer').removeClass('open');
    };
    function updateCartContent() {
        $.ajax({
            url: wc_cart_params.ajax_url,
            type: 'POST',
            data: { action: 'get_cart_content' },
            success: function(response) {
                if (response.success) {
                    // Update cart drawer content and item count
                    $('.rmenu-cart').html(response.data.cart_html);
                }
            }
        });
    }
    // Intercept the form submission
    $(document).on('submit', 'form.woocommerce-checkout', function(e) {
        e.preventDefault();
        $('#place_order').text('');
        $('#place_order').append('<div class="spinner"></div>');
        var message = rmsgValue.rmsgEditor;
        var formData = $(this).serialize(); // Collect form data
        // AJAX request to process the order
        $.ajax({
            type: 'POST',
            url: woocommerce_params.ajax_url,
            data: formData + '&action=woocommerce_checkout', // Append action
            success: function(response) {
                if (response.result === 'success') {
                    // Show success message in the popup
                    $('.popup-message').html('<div class="Confirm_message">' + message + '</div>');
                    $('#checkout-form').remove();
                } else {
                    $('.spinner').remove();
                    $('.popup-message').append('<p>Error: ' + response.messages + '</p>');
                    // Show errors in the popup
                    $('.popup-message').html('<p>' + response.messages + '</p>');
                    
                }
                updateCartContent();
            },
            error: function(res) {
                $('.spinner').remove();
                $('.popup-message').append('<p>test Error'  + res.messages + ' processing your order. Please try again. </p>');
                $('.popup-message').html('<p>test2 Error ' + res.messages + ' processing your order. Please try again.</p> ');
                updateCartContent();
            }
        });
    });

});

// Function to close the popup
function closeCheckoutPopup() {
    $('.checkout-popup').fadeOut();
}