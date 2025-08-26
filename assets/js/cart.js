jQuery(document).ready(function ($) {
    let isUpdatingCart = false;
    let isUpdatingCheckout = false;
    $isonepagewidget = ($('.checkout-popup,#checkout-popup').length) ? $('.checkout-popup,#checkout-popup').data('isonepagewidget') : false;
    // Function to fetch and update cart contents
    function updateCartContent(isdrawer = true) {
        if (isUpdatingCart) return;
        isUpdatingCart = true;
        let cartIcon = $('.rwc_cart-button').data('cart-icon');
        let productTitleTag = $('.rwc_cart-button').data('product_title_tag');
        let drawerPosition = $('.rwc_cart-button').data('drawer-position');
        $.ajax({
            url: onepaquc_wc_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'onepaquc_get_cart_content',
                cart_icon: cartIcon,
                product_title_tag: productTitleTag,
                drawer_position: drawerPosition,
                nonce: onepaquc_wc_cart_params.get_cart_content_none
            },
            success: function (response) {
                if (response.success) {
                    $('.rmenu-cart').html(response.data.cart_html);
                    if (isdrawer && response.data.cart_count !== 0) {
                        window.openCartDrawer();
                    }
                    isUpdatingCart = false;
                }
            },
            error: function () {
                isUpdatingCart = false;
            }
        });
    }

    window.updateCartCount = function (isIncrement = true, Value = 1) {
        // Select the cart count element
        const cartCountElement = document.querySelector('span.cart-count');

        $(document.body).on('removed_from_cart', function () {
            isIncrement = false;
        });

        // Check if the element exists
        if (cartCountElement) {
            // Get the current count, parse it as an integer, and increase it by the increment value
            let currentCount = parseInt(cartCountElement.textContent, 10);
            if (isIncrement) {
                currentCount += Value;
            } else {
                currentCount -= Value;
            }

            // Update the cart count display
            cartCountElement.textContent = currentCount;
        } else {
            console.error('Cart count element not found.');
        }
    };
    // Event handler for adding/removing items from the cart
    $(document.body).on('added_to_cart removed_from_cart', function () {
        const cartDrawer = document.querySelector('.cart-drawer');
        if (cartDrawer && cartDrawer.classList.contains('open')) {
            window.createCheckoutIframe();
            window.refreshCheckoutIframe();
        } else {
            window.createCheckoutIframe();
            window.refreshCheckoutIframe();
            // window.updateCartCount();
            debouncedUpdate();
        }
    });
    // $(document.body).on('updated_wc_div updated_checkout', function () {
    //     debouncedUpdate();
    // });

    // Function to update the checkout form
    function updateCheckoutForm() {
        if (isUpdatingCheckout) return;
        isUpdatingCheckout = true;
        $.ajax({
            url: onepaquc_ajax_object.ajax_url,
            method: 'POST',
            data: { action: 'onepaquc_update_checkout' },
            success: function (response) {
                if (response.success) {
                    // $('.checkout-popup').html(response.data.checkout_form);
                    $(document.body).trigger('update_checkout');
                } else {
                    console.error('Error updating checkout:', response.data);
                }
                isUpdatingCheckout = false;
            },
            error: function () {
                console.error('AJAX request failed.');
                isUpdatingCheckout = false;
            }
        });
    }

    function debouncedUpdate(showdrawer = true) {
        if (!$isonepagewidget) {
            updateCartContent();
        } else {
            updateCartContent(false);
        }
        updateCheckoutForm();
    }

    window.updateCartContent = function (isdrawer = true) {
        updateCartContent(isdrawer);
    }

    // Handle quantity change
    $('.rmenu-cart').on('change', '.item-quantity', function () {
        const $input = $(this);
        const cartItemKey = $input.closest('.cart-item').find('.remove-item').data('cart-item-key');
        const quantity = $input.val();
        const cartCountElement = document.querySelector('span.cart-count');

        // Add loading class (spinner)
        $input.prop('disabled', true).parent().addClass('loading-spinner');

        $.ajax({
            url: onepaquc_wc_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'onepaquc_update_cart_item_quantity',
                cart_item_key: cartItemKey,
                quantity: quantity,
                nonce: onepaquc_wc_cart_params.update_cart_item_quantity
            },
            success: function (response) {
                if (response.success) {
                    // Update cart totals
                    window.updateCartTotals(response.data);
                    cartCountElement.textContent = response.data.cart_count;
                    // debouncedUpdate();
                    $(document.body).trigger('update_checkout');
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);
                }
            },
            complete: function () {
                // Remove loading class (spinner)
                $input.prop('disabled', false).parent().removeClass('loading-spinner');
            }
        });
    });


    // Handle remove item
    $('.rmenu-cart').on('click', '.remove-item', function (e) {
        e.preventDefault();
        const cartItemKey = $(this).data('cart-item-key');
        const cartItem = $(this).closest('.cart-item');
        cartItem.addClass("removing");
        cartItem.css('transition', 'opacity 0.5s ease'); // Optional: add transition for smooth effect
        cartItem.css('opacity', '0.5'); // Optional: fade out the item

        window.removecartitem(cartItemKey);
    });

    window.removecartitem = function (cartItemKey) {
        const removingItems = document.querySelectorAll('.removing');
        let cart_count = document.querySelector('span.cart-count');
        const selectedCountText = document.getElementById('selected-count-text');
        const removeSelectedButton = document.getElementById('remove-selected');

        $.ajax({
            url: onepaquc_wc_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'onepaquc_remove_cart_item',
                cart_item_key: cartItemKey,
                nonce: onepaquc_wc_cart_params.remove_cart_item,
            },
            success: function (response) {
                if (response.success) {
                    window.updateCartTotals(response.data);

                    removingItems.forEach(item => {
                        let currentCount = parseInt(cart_count.textContent, 10) || 0;
                        currentCount -= 1;
                        // Update the element with the new count
                        cart_count.textContent = currentCount;
                        if (currentCount === 0) {
                            window.closeCheckoutPopup();
                            cart_count.textContent = "0";
                        }
                        item.classList.add('fade-out'); // Start fade-out animation
                        setTimeout(() => {
                            item.remove(); // Remove item after animation
                        }, 500); // Match timeout with CSS transition duration
                    });

                    // Update checkout totals
                    $(document.body).trigger('update_checkout');
                    selectedCountText.textContent = `0 selected`;
                    removeSelectedButton.style.display = 'none';

                    // Trigger WooCommerce hook
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);
                }
            }
        });
    }
    // handle quantity change

    // Function to update quantity
    function updateQuantity(cartItemKey, qty) {
        if (!cartItemKey) {
            return;
        }

        var $thisButton = $(this);

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
                action: 'onepaquc_update_cart_item_quantity',
                cart_item_key: cartItemKey,
                quantity: qty,
                nonce: onepaquc_wc_cart_params.update_cart_item_quantity
            },
            success: function (response) {
                debouncedUpdate(false);
                // Trigger WC events
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisButton]);
                $('body').trigger('onepaquc_update_checkout');
                $(document.body).trigger('update_checkout');
            },
            complete: function () {
                $('.woocommerce-checkout-review-order-table').unblock();
            }
        });
    }

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

    // Handle click on remove item button
    $(document).on('click', '.remove-item-checkout', function (e) {
        e.preventDefault();
        var cartItemKey = $(this).data('cart-item');
        var $thisButton = $(this);

        $(this).closest('.cart_item').css('transition', 'opacity 0.5s ease'); // Optional: add transition for smooth effect
        $(this).closest('.cart_item').css('opacity', '0.5'); // Optional: fade out the item

        $.ajax({
            type: 'POST',
            url: wc_add_to_cart_params.ajax_url,
            data: {
                action: 'onepaquc_remove_cart_item',
                cart_item_key: cartItemKey,
                nonce: onepaquc_wc_cart_params.remove_cart_item
            },
            success: function (response) {
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisButton]);
                // Get product IDs from data attribute
                var product_ids = $('.one-page-checkout-product-list').data('product-ids');

                // Refresh the list of products
                $.ajax({
                    type: 'POST',
                    url: wc_add_to_cart_params.ajax_url,
                    data: {
                        action: 'onepaquc_refresh_checkout_product_list',
                        product_ids: product_ids,
                        nonce: onepaquc_wc_cart_params.onepaquc_refresh_checkout_product_list
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
                $(document.body).trigger('update_checkout');
                debouncedUpdate(false);
            }
        });
    });

    var $directbehave = onepaquc_wc_cart_params.direct_checkout_behave;
    var methodKey = $directbehave.rmenu_wc_checkout_method;

    function directcheckout(product_id, product_type, $button, methodKey) { // Add methodKey parameter
        var $variation_id = $button.siblings('.archive-variations-container').find('.variation_id').val() ||
            $button.siblings('.variation_id').val() ||
            $button.closest('.product').find('.archive-variations-container').find('.variation_id').val() || 0;

        // Convert to number to ensure proper comparison
        $variation_id = parseInt($variation_id) || 0;

        $('#checkout-button-drawer-link').prop('disabled', true);

        // Check for variation selection FIRST
        if (product_type === 'variable' && $variation_id === 0) {
            $('#checkout-button-drawer-link').prop('disabled', false);
            $button.removeClass('loading').prop('disabled', false);
            alert("Please select some product options before adding this product to your cart.");
            return false; // Explicitly return false
        }

        // Handle confirmation if enabled
        if ($directbehave.rmenu_wc_add_confirmation == 1) {
            var methodMap = {
                direct_checkout: "Redirect to Checkout",
                ajax_add: "AJAX Add to Cart",
                cart_redirect: "Redirect to Cart Page",
                popup_checkout: "Popup Checkout",
                side_cart: "Side Cart Slide-in"
            };

            var methodLabel = methodMap[methodKey] || "Direct Checkout";
            var confirmMessage = `Are you sure you want to proceed with ${methodLabel}?`;

            if ($directbehave.rmenu_wc_clear_cart === "1") {
                confirmMessage += ` This will clear your current cart.`;
            }

            var confirmed = confirm(confirmMessage);

            if (!confirmed) {
                $('#checkout-button-drawer-link').prop('disabled', false);
                $button.removeClass('loading').prop('disabled', false);
                return; // Stop execution if user cancels
            }
        }

        // Function to proceed with adding to cart        
        function proceedToAddToCart() {
            $.ajax({
                type: 'POST',
                url: onepaquc_wc_cart_params.ajax_url,
                data: {
                    action: 'onepaquc_ajax_add_to_cart',
                    product_id: product_id,
                    quantity: 1,
                    variation_id: $variation_id,
                    nonce: onepaquc_wc_cart_params.nonce || '',
                },
                success: function (response) {
                    if (response.success) {
                        // Handle WooCommerce fragments
                        if (response.fragments) {
                            $.each(response.fragments, function (key, value) {
                                $(key).replaceWith(value);
                            });

                            if (typeof sessionStorage !== 'undefined') {
                                sessionStorage.setItem('wc_fragments', JSON.stringify(response.fragments));
                                sessionStorage.setItem('wc_cart_hash', response.cart_hash);
                            }
                        }

                        // Update UI
                        debouncedUpdate(false);
                        $(document.body).trigger('update_checkout');

                        const cartDrawer = $('.cart-drawer');

                        // Trigger WooCommerce hook
                        $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);

                        // Redirect or UI handling based on method
                        if (methodKey === 'direct_checkout') {
                            window.location.href = onepaquc_wc_cart_params.checkout_url;
                        } else if (methodKey === 'ajax_add') {
                            if (cartDrawer.length) cartDrawer.removeClass('open');
                        } else if (methodKey === 'cart_redirect') {
                            window.location.href = onepaquc_wc_cart_params.cart_url;
                        } else if (methodKey === 'side_cart' && !$isonepagewidget) {
                            if ($('#cart-drawer2-style').length) $('#cart-drawer2-style').remove();
                            if (!cartDrawer.length) {
                                console.error('Cart drawer not found. Enable floating/sticky cart from settings.');
                            }
                            debouncedUpdate();
                        } else {
                            const checkout_popup = $('.checkout-popup');
                            if (checkout_popup.length) checkout_popup.show();
                            if (cartDrawer.length) cartDrawer.removeClass('open');
                        }
                    } else {
                        alert(response.message || 'Could not add the product to cart.');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error:', textStatus, errorThrown);
                    alert('Failed to add product to cart. Please try again later.');
                },
                complete: function () {
                    $('#checkout-button-drawer-link').prop('disabled', false);
                    $button.removeClass('loading').prop('disabled', false);

                    if ($isonepagewidget) {
                        const element = document.getElementById('checkout-form');
                        if (element) {
                            element.scrollIntoView({ behavior: 'smooth' });
                        }
                    }
                }
            });
        }

        // If clear cart is enabled, clear the cart before proceeding
        if ($directbehave.rmenu_wc_clear_cart == 1) {
            $.ajax({
                type: 'POST',
                url: wc_add_to_cart_params.ajax_url,
                data: {
                    action: 'woocommerce_clear_cart'
                },
                success: function () {
                    proceedToAddToCart();
                },
                error: function () {
                    alert('Could not clear cart. Please try again.');
                    $('#checkout-button-drawer-link').prop('disabled', false);
                    $button.removeClass('loading').prop('disabled', false);
                }
            });
        } else {
            proceedToAddToCart();
        }
    }

    // Helper function for showing variation selection popup (if needed)
    function showVariationSelectionPopup(product_id) {
        $.ajax({
            type: 'GET',
            url: onepaquc_wc_cart_params.ajax_url,
            data: {
                action: 'rmenu_get_product_variations',
                product_id: product_id
            },
            success: function (response) {
                if (response.success) {
                    var popupHtml = `
                    <div class="variation-popup-overlay">
                        <div class="variation-popup">
                            <span class="variation-popup-close">&times;</span>
                            <h3>Select Product Options</h3>
                            ${response.data}
                        </div>
                    </div>
                `;

                    $('body').append(popupHtml);

                    $('.variation-popup-close, .variation-popup-overlay').on('click', function () {
                        $('.variation-popup-overlay').remove();
                    });

                    $('.variation-popup').on('click', function (event) {
                        event.stopPropagation();
                    });
                } else {
                    alert(response.message || 'Could not load variations. Please try again.');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
                alert('Failed to load variations. Please try again later.');
            }
        });
    }

    // Helper function for showing variation selection popup (if needed)
    function showVariationSelectionPopup(product_id) {
        $.ajax({
            type: 'GET',
            url: onepaquc_wc_cart_params.ajax_url,
            data: {
                action: 'rmenu_get_product_variations',
                product_id: product_id
            },
            success: function (response) {
                if (response.success) {
                    var popupHtml = `
                    <div class="variation-popup-overlay">
                        <div class="variation-popup">
                            <span class="variation-popup-close">&times;</span>
                            <h3>Select Product Options</h3>
                            ${response.data}
                        </div>
                    </div>
                `;

                    $('body').append(popupHtml);

                    $('.variation-popup-close, .variation-popup-overlay').on('click', function () {
                        $('.variation-popup-overlay').remove();
                    });

                    $('.variation-popup').on('click', function (event) {
                        event.stopPropagation();
                    });
                } else {
                    alert(response.message || 'Could not load variations. Please try again.');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
                alert('Failed to load variations. Please try again later.');
            }
        });
    }
    // Event delegation for better performance
    $(document).on('click', '.direct-checkout-button', function (e) {
        e.preventDefault(); // Prevent the default anchor behavior
        var $button = $(this); // Cache the button reference
        var product_id = $button.data('product-id');
        var product_type = $button.data('product-type');
        // Add loading class
        $button.addClass('loading').prop('disabled', true); // Disable the button
        $('body').append(`
            <style id="cart-drawer2-style">
                .cart-drawer,.overlay {
                    opacity: 0 !important;
                    visibility: hidden !important;
                    display: none !important;
                }
                body{
                    overflow: auto !important;
                }
            </style>
        `);
        directcheckout(product_id, product_type, $button);
    });

    $(document.body).on('updated_checkout', function () {
        // Get the full HTML of the order total amount from the specified element
        var orderTotalHtml = $('.order-total .woocommerce-Price-amount').html()

        if (orderTotalHtml) orderTotalHtml.trim();
        // Check if the <p> with the class 'order-total-price' exists
        var totalPriceElement = $('.form-row.place-order p.order-total-price');
        if (totalPriceElement.length) {
            // If it exists, update the HTML
            totalPriceElement.html('<span>Total: </span>' + orderTotalHtml);
        } else {
            // If it doesn't exist, prepend a new <p> with the class
            var newTotalParagraph = '<p class="order-total-price"><span>Total: </span>' + orderTotalHtml + '</p>';
            $('.form-row.place-order').prepend(newTotalParagraph);
        }
    });

    function setbtnLoadingState($button, loading) {

        if (loading) {
            // Store the original text if not already stored
            if (!$button.data('original-text')) {
                $button.data('original-text', $button.text());
            }
            $button
                .addClass("loading")
                .prop('disabled', true)
                .text('Adding...');
        } else {
            $button
                .removeClass("loading")
                .prop('disabled', false)
                .text('Add to Cart');
        }
    }

    $(document).on('click', '.add-to-cart-button', function (e) {
        var $button = $(this);
        setbtnLoadingState($button, true);
        const couponMessage = document.getElementById('coupon-message');

        const productId = this.dataset.productId;

        const data = {
            action: 'onepaquc_ajax_add_to_cart',
            product_id: productId,
            nonce: onepaquc_wc_cart_params.rmenu_ajax_nonce
        };

        jQuery.post(onepaquc_wc_cart_params.ajax_url, data, function (response) {
            if (response.success) {
                const cart_items = document.querySelector('.cart-items');
                if (cart_items && response.cart_items_html) {
                    cart_items.innerHTML = response.cart_items_html; // Use innerHTML
                }

                setbtnLoadingState($button, false);

                // Update cart count
                document.querySelector('span.cart-count').textContent = response.cart_count;

                // Show success message
                couponMessage.textContent = 'Product added to cart!';
                couponMessage.className = 'coupon-message success';
                couponMessage.style.display = "block";

                // Trigger WooCommerce hook
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);

                debouncedUpdate();

                // Clear message after delay
                setTimeout(() => {
                    couponMessage.textContent = '';
                    couponMessage.className = 'coupon-message';
                    couponMessage.style.display = "none";
                }, 3000);
            }
        });

    });
});







