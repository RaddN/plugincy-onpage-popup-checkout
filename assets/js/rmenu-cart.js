// rmenu-cart.js
jQuery(document).ready(function ($) {

    let updateCartContent_timer;

    const checkout_popup = $('.checkout-popup');
    $isonepagewidget = (checkout_popup.length) ? checkout_popup.data('isonepagewidget') : false;

    var checkoutIframe = $('<iframe>', {
        src: onepaquc_rmsgValue.checkout_url + '?hide_header_footer=1',
        id: 'checkout-iframe',
        frameborder: 0,
        style: 'width: 100%; min-height: 100%; height: 100%;'
    });
    // append iframe
    window.createCheckoutIframe = function () {
        if (!$('form.checkout.woocommerce-checkout').length && checkout_popup.length && $(".checkout-popup #checkout-form").length && !$(".checkout-popup #checkout-form #checkout-iframe").length) {
            // Show loading spinner before iframe loads
            $('.checkout-popup #checkout-form').html('<style>div#checkout-form { overflow: hidden !important; display: flex ; flex-direction: column; justify-content: center; }</style><div class="plugincy_preloader"> <svg class="plugincy_cart" role="img" aria-label="Shopping plugincy_cart line animation" viewBox="0 0 128 128" width="128px" height="128px" xmlns="http://www.w3.org/2000/svg"> <g fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="8"> <g class="plugincy_cart__track" stroke="hsla(0,10%,10%,0.1)"> <polyline points="4,4 21,4 26,22 124,22 112,64 35,64 39,80 106,80" /> <circle cx="43" cy="111" r="13" /> <circle cx="102" cy="111" r="13" /> </g> <g class="plugincy_cart__lines" stroke="currentColor"> <polyline class="plugincy_cart__top" points="4,4 21,4 26,22 124,22 112,64 35,64 39,80 106,80" stroke-dasharray="338 338" stroke-dashoffset="-338" /> <g class="plugincy_cart__wheel1" transform="rotate(-90,43,111)"> <circle class="plugincy_cart__wheel-stroke" cx="43" cy="111" r="13" stroke-dasharray="81.68 81.68" stroke-dashoffset="81.68" /> </g> <g class="plugincy_cart__wheel2" transform="rotate(90,102,111)"> <circle class="plugincy_cart__wheel-stroke" cx="102" cy="111" r="13" stroke-dasharray="81.68 81.68" stroke-dashoffset="81.68" /> </g> </g> </g> </svg> <div class="plugincy_preloader__text"> <p class="plugincy_preloader__msg">Bringing you the goods…</p> <p class="plugincy_preloader__msg plugincy_preloader__msg--last">This is taking long. Something’s wrong.</p> </div> </div>');
            $('.checkout-popup #checkout-form').append(checkoutIframe);

            $(".checkout-popup div#checkout-form").css("overflow", "hidden");

            // Remove spinner when iframe is loaded
            checkoutIframe.on('load', function () {
                $('.plugincy_preloader').remove();
            });

            checkoutIframe.on('error', function () {
                $('.checkout-popup #checkout-form').html('<p>Error loading checkout. Please try again.</p>');
            });
        }
    }

    // Function to refresh iframe
    window.refreshCheckoutIframe = function () {
        if ($('#checkout-iframe').length) {
            $('#checkout-iframe')[0].src = $('#checkout-iframe')[0].src;
        }
    };

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
        const cartDrawer = $('.cart-drawer');
        const overlay = $('.overlay');
        if (cartDrawer && cartDrawer.length){
            cartDrawer.addClass('open');
            if (overlay) overlay.show();
            document.body.style.overflow = 'hidden';
        }

    };

    // Close the checkout popup
    window.closeCheckoutPopup = function () {
        closeCartAndCheckout();
    };

    // Open the checkout popup
    window.openCheckoutPopup = function () {
        if (checkout_popup) checkout_popup.show();
        const cartDrawer = $('.cart-drawer');
        if (cartDrawer && cartDrawer.length) {
            cartDrawer.removeClass('open');
        }

        if ($('.checkout-popup iframe')) {
            updateCartContent_timer = setInterval(() => {
                window.updateCartContent(false);
            }, 2000);
        }

        $('body').append(`
            <style id="cart-drawer-style">
                .cart-drawer {
                opacity: 0 !important;
                visibility: hidden !important;
                }
            </style>
        `);
    };

    // Function to close the cart drawer and checkout popup
    function closeCartAndCheckout() {
        if (!$isonepagewidget) {
            if (checkout_popup) checkout_popup.hide();
        }
        const cartDrawer = $('.cart-drawer');
        if (cartDrawer && cartDrawer.length) cartDrawer.removeClass('open');
        $('.overlay').hide(); // Hide overlay when cart is closed
        document.body.style.overflow = '';
        if ($('#cart-drawer-style')) $('#cart-drawer-style').remove();
        clearInterval(updateCartContent_timer);
    }

    // Select & select all functionality

    $(document).on('change', '#select-all-items', function () {
        $('.item-checkbox').prop('checked', this.checked);
        updateSelectedCount();
    });

    // Individual item selection
    $(document).on('change', '.item-checkbox', function () {
        updateSelectedCount();
        updateSelectAllCheckbox();
    });

    // Update selected count
    function updateSelectedCount() {
        const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
        const selectedCountText = document.getElementById('selected-count-text');
        const removeSelectedButton = document.getElementById('remove-selected');
        const count = checkedBoxes.length;
        selectedCountText.textContent = `${count} selected`;
        removeSelectedButton.style.display = count > 0 ? 'inline-block' : 'none';
    }

    // Update select all checkbox
    function updateSelectAllCheckbox() {
        const selectAllCheckbox = document.getElementById('select-all-items');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
        selectAllCheckbox.checked = checkedBoxes.length === itemCheckboxes.length;
    }

    // Remove selected items
    $(document).on('click', '#remove-selected', function () {
        const checkedBoxes = $('.item-checkbox:checked');
        if (checkedBoxes.length === 0) return;

        const cartItemKeys = [];
        checkedBoxes.each(function () {
            const cartItemKey = $(this).data('cart-item-key');
            cartItemKeys.push(cartItemKey);
            const cartItem = $(this).closest('.cart-item');
            cartItem.addClass("removing");
            cartItem.css('transition', 'opacity 0.5s ease');
            cartItem.css('opacity', '0.5');
        });

        window.removecartitem(cartItemKeys);
    });

    // Apply coupon



    // Helper function to check applied coupons visibility

    $(document).on('click', '#apply-coupon', function () {
        const appliedCoupons = document.getElementById('applied-coupons');
        const couponInput = $('#coupon-code');
        const couponCode = couponInput.val().trim();
        if (!couponCode) return;

        setLoadingState(this, true, 'Applying...');
        showMessage('Applying coupon...', 'loading');

        const data = {
            action: 'apply_coupon',
            coupon_code: couponCode,
            security: onepaquc_rmsgValue.apply_coupon
        };

        jQuery.post(woocommerce_params.ajax_url, data, function (response) {
            setLoadingState($('#apply-coupon')[0], false, 'Apply Coupon');
            if (response.success) {
                showMessage('Coupon applied successfully!', 'success');
                window.updateCartTotals(response.data);
                // Add to applied coupons list
                if (appliedCoupons) {
                    appliedCoupons.style.display = "block";
                    if (appliedCoupons.children.length <= 0) {
                        appliedCoupons.innerHTML = `<h4>Applied Coupons:</h4>`;
                    }
                    const couponElement = document.createElement('div');
                    couponElement.className = 'applied-coupon';
                    couponElement.innerHTML = `
                        <span>${couponCode}</span>
                        <button class="remove-coupon" data-coupon="${couponCode}">Remove</button>
                    `;
                    appliedCoupons.appendChild(couponElement);
                }

                // Clear input
                couponInput.value = '';
            } else {
                showMessage(response.data.message || 'Invalid coupon code.', 'error');
            }
        }).fail(function () {
            setLoadingState($('#apply-coupon')[0], false, 'Apply Coupon');
            showMessage('Something went wrong. Please try again.', 'error');
        });
    });

    // Add event listener to remove button (using event delegation for dynamically added buttons)

    $(document).on('click', '.remove-coupon', function (e) {
        const couponCode = this.dataset.coupon;
        removeCoupon(couponCode, this);
    });

    // Remove coupon
    function removeCoupon(couponCode, buttonElement) {
        const appliedCoupons = document.getElementById('applied-coupons');
        // Show loading state for the specific remove button
        setLoadingState(buttonElement, true, 'Removing...');
        showMessage('Removing coupon...', 'loading');

        const data = {
            action: 'remove_coupon',
            coupon_code: couponCode,
            security: onepaquc_rmsgValue.apply_coupon
        };

        jQuery.post(woocommerce_params.ajax_url, data, function (response) {
            if (response.success) {
                // Remove coupon from DOM
                const couponElement = buttonElement.parentElement;
                if (couponElement) {
                    couponElement.remove();
                }
                if (appliedCoupons.children.length <= 1) {
                    appliedCoupons.style.display = 'none'; // Hide the element
                }

                // Show success message
                showMessage('Coupon removed successfully!', 'success');

                // Update cart totals
                window.updateCartTotals(response.data);
            } else {
                // Remove loading state if removal failed
                setLoadingState(buttonElement, false, 'Remove');
                showMessage('Failed to remove coupon. Please try again.', 'error');
            }
        }).fail(function () {
            // Handle AJAX error
            setLoadingState(buttonElement, false, 'Remove');
            showMessage('Something went wrong. Please try again.', 'error');
        });
    }

    // Helper function to set loading state on buttons
    function setLoadingState(button, isLoading, text) {
        if (isLoading) {
            button.disabled = true;
            button.classList.add('loading');
            button.textContent = text;
            // Add spinner if you have CSS for it
            // button.innerHTML = `<span class="spinner"></span> ${text}`;
        } else {
            button.disabled = false;
            button.classList.remove('loading');
            button.textContent = text;
        }
    }

    // Helper function to show messages with auto-clear
    function showMessage(message, type) {
        const couponMessage = document.getElementById('coupon-message');
        if (couponMessage) {
            couponMessage.textContent = message;
            couponMessage.className = `coupon-message ${type}`;
            couponMessage.style.display = "block";

            // Don't auto-clear loading messages
            setTimeout(() => {
                couponMessage.textContent = '';
                couponMessage.className = 'coupon-message';
                couponMessage.style.display = "none";
            }, 3000);
        }
    }

    // Update cart totals
    window.updateCartTotals = function (data) {
        $(document.body).trigger('update_checkout');
        // Update subtotal
        const subtotalElement = document.querySelector('.summary-row:not(.discount):not(.total) span:last-child');
        if (subtotalElement) {
            subtotalElement.innerHTML = data.subtotal; // Use innerHTML
        }

        // Update discount
        const discountElement = document.querySelector('.summary-row.discount');
        if (discountElement) {
            if (data.discount_total > 0) {
                discountElement.style.display = 'flex';
                discountElement.innerHTML = `<span>Discount</span><span>- ${onepaquc_rmsgValue.currency_symbol} ${data.discount_total}</span>`;
            } else {
                discountElement.style.display = 'none';
            }
        }

        // Update total
        const totalElement = document.querySelector('.summary-row.total span:last-child');
        if (totalElement) {
            totalElement.innerHTML = data.total; // Use innerHTML
        }
    }

    $(document).on('click', '.quantity-btn', function () {
        const $input = $(this).siblings('.item-quantity');
        let currentValue = parseInt($input.val(), 10);

        if ($(this).data('action') === 'plus') {
            currentValue += 1;
        } else if ($(this).data('action') === 'minus' && currentValue > 1) {
            currentValue -= 1;
        }

        $input.val(currentValue);
        $input.trigger('change'); // Trigger the change event to use your existing code

    });
});