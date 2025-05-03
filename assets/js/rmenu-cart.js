jQuery(document).ready(function ($) {

    $isonepagewidget = $('.checkout-popup').data('isonepagewidget');

    // Click event to close cart drawer and checkout popup
    $(document).click(function (event) {
        if (!$(event.target).closest('.cart-drawer, .rwc_cart-button, .checkout-popup').length) {
            closeCartAndCheckout();
        }
    });

    $(document).on('click', '.rwc_cart-button', function () {
        openCartDrawer();
    });

    // Open the cart drawer
    window.openCartDrawer = function () {
        $('.cart-drawer').addClass('open');
        $('.overlay').show();
    };

    // Close the checkout popup
    window.closeCheckoutPopup = function () {
        closeCartAndCheckout();
    };

    // Open the checkout popup
    window.openCheckoutPopup = function () {
        $('.checkout-popup').show();
        $('.cart-drawer').removeClass('open');
    };

    // Function to close the cart drawer and checkout popup
    function closeCartAndCheckout() {
        if (!$isonepagewidget) {
            $('.checkout-popup').hide();           
        }
        $('.cart-drawer').removeClass('open');
        $('.overlay').hide(); // Hide overlay when cart is closed
    }

    // Intercept the form submission for checkout
    $(document).on('submit', 'form.woocommerce-checkout', function (e) {
        e.preventDefault();
        $('#place_order').text('').append('<div class="spinner"></div>');
        const formData = $(this).serialize();

        // AJAX request to process the order
        $.ajax({
            type: 'POST',
            url: woocommerce_params.ajax_url,
            data: formData + '&action=woocommerce_checkout',
            success: function (response) {
                handleCheckoutResponse(response);
            },
            error: function (res) {
                handleCheckoutError(res);
            }
        });
    });

    // Handle successful checkout response
    function handleCheckoutResponse(response) {
        $('.spinner').remove();
        if (response.result === 'success') {
            $('.popup-message').html('<div class="Confirm_message">' + onepaquc_rmsgValue.rmsgEditor + '</div>');
            $('#checkout-form').remove();
        } else {
            $('.popup-message').html('<p>' + response.messages + '</p>');
        }
    }

    // Handle checkout error
    function handleCheckoutError(res) {
        $('.spinner').remove();
        $('.popup-message').html('<p>Error processing your order: <pre>' + JSON.stringify(res) + res.messages + '</pre></p>');
    }
});