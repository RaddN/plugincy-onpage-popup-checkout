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
        if ($('form.checkout.woocommerce-checkout').length) {
            $('.checkout-popup').show();
            $('.cart-drawer').removeClass('open');
        } else {
            $('.cart-drawer').removeClass('open');
            $('.checkout-popup').show();

            console.log(onepaquc_rmsgValue.checkout_url);

            // Create iframe for checkout
            var iframe = $('<iframe>', {
                src: onepaquc_rmsgValue.checkout_url+'?hide_header_footer=1',
                id: 'checkout-iframe',
                frameborder: 0,
                style: 'width: 100%; min-height: 0%; height:0;'
            });
            // Replace content with iframe
            // Show loading spinner before iframe loads
            $('.checkout-popup #checkout-form').html('<style>div#checkout-form { overflow: hidden !important; display: flex ; flex-direction: column; justify-content: center; }</style><div class="plugincy_preloader"> <svg class="plugincy_cart" role="img" aria-label="Shopping plugincy_cart line animation" viewBox="0 0 128 128" width="128px" height="128px" xmlns="http://www.w3.org/2000/svg"> <g fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="8"> <g class="plugincy_cart__track" stroke="hsla(0,10%,10%,0.1)"> <polyline points="4,4 21,4 26,22 124,22 112,64 35,64 39,80 106,80" /> <circle cx="43" cy="111" r="13" /> <circle cx="102" cy="111" r="13" /> </g> <g class="plugincy_cart__lines" stroke="currentColor"> <polyline class="plugincy_cart__top" points="4,4 21,4 26,22 124,22 112,64 35,64 39,80 106,80" stroke-dasharray="338 338" stroke-dashoffset="-338" /> <g class="plugincy_cart__wheel1" transform="rotate(-90,43,111)"> <circle class="plugincy_cart__wheel-stroke" cx="43" cy="111" r="13" stroke-dasharray="81.68 81.68" stroke-dashoffset="81.68" /> </g> <g class="plugincy_cart__wheel2" transform="rotate(90,102,111)"> <circle class="plugincy_cart__wheel-stroke" cx="102" cy="111" r="13" stroke-dasharray="81.68 81.68" stroke-dashoffset="81.68" /> </g> </g> </g> </svg> <div class="plugincy_preloader__text"> <p class="plugincy_preloader__msg">Bringing you the goods…</p> <p class="plugincy_preloader__msg plugincy_preloader__msg--last">This is taking long. Something’s wrong.</p> </div> </div>');
            $('.checkout-popup #checkout-form').append(iframe);

            // Remove spinner when iframe is loaded
            iframe.on('load', function () {
                $('.plugincy_preloader').remove();
                $('#checkout-iframe').css('min-height', '100%').css('height', '100%');
            });

            // Optional: handle iframe load/error
            iframe.on('error', function () {
                $('.checkout-popup #checkout-form').html('<p>Error loading checkout. Please try again.</p>');
            });
        }
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