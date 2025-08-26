<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
// cart-template.php

// Shortcode to display cart icon and drawer
function onepaquc_cart($drawer_position = 'right', $cart_icon = 'cart', $product_title_tag = 'p', $position = "", $top = "", $left = "")
{
    $cart_icons = array(
        'cart' => '<svg fill="#fff" xmlns="http://www.w3.org/2000/svg" width="30px" height="30px" viewBox="0 0 1.95 1.95" enable-background="new 0 0 52 52" xml:space="preserve"><g><path d="M0.754 0.975H1.65c0.026 0 0.052 -0.019 0.056 -0.045l0.165 -0.578c0.011 -0.041 -0.019 -0.075 -0.056 -0.075H0.431l-0.022 -0.086C0.397 0.15 0.36 0.124 0.322 0.124h-0.15c-0.049 0 -0.094 0.037 -0.098 0.086C0.071 0.263 0.116 0.307 0.165 0.307h0.086l0.285 0.964c0.011 0.041 0.045 0.068 0.086 0.068h1.057c0.049 0 0.094 -0.037 0.098 -0.086 0.004 -0.052 -0.041 -0.098 -0.09 -0.098H0.757c-0.041 0 -0.075 -0.026 -0.086 -0.064V1.087c-0.019 -0.056 0.026 -0.112 0.083 -0.112"/><path cx="20.6" cy="44.6" r="4" d="M0.922 1.673A0.15 0.15 0 0 1 0.773 1.823A0.15 0.15 0 0 1 0.623 1.673A0.15 0.15 0 0 1 0.922 1.673z"/><path cx="40.1" cy="44.6" r="4" d="M1.654 1.673A0.15 0.15 0 0 1 1.504 1.823A0.15 0.15 0 0 1 1.354 1.673A0.15 0.15 0 0 1 1.654 1.673z"/></g></svg>',
        'shopping-bag' => '<svg fill="#fff" height="30px" width="30px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 19.2 19.2" enable-background="new 0 0 512 512" xml:space="preserve"><path d="M15.795 4.8h-1.601v0.799c0 0.881 -0.716 1.601 -1.601 1.601 -0.881 0 -1.601 -0.716 -1.601 -1.601V4.8h-3.199v0.799c0 0.881 -0.716 1.601 -1.601 1.601 -0.881 0 -1.601 -0.716 -1.601 -1.601V4.8H2.996c0 7.999 -0.799 14.4 -0.799 14.4h14.4c-0.004 0 -0.802 -6.401 -0.802 -14.4m-9.6 1.601c0.443 0 0.799 -0.356 0.799 -0.799v-1.601c0 -1.327 1.073 -2.4 2.4 -2.4s2.4 1.073 2.4 2.4v1.601c0 0.443 0.356 0.799 0.799 0.799s0.799 -0.356 0.799 -0.799v-1.601C13.395 1.792 11.602 0 9.394 0S5.393 1.792 5.393 4.001v1.601c0.004 0.439 0.36 0.799 0.802 0.799"/></svg>',
        'basket' => '<svg fill="#fff" height="30" width="30" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 19.2 19.2" xml:space="preserve"><path d="M15.199 7.2 12 0h-1.601l3.199 7.2zM8.801 0H7.2L4.001 7.2h1.601zm-7.2 17.599c0 .881.716 1.601 1.601 1.601h12.799c.881 0 1.601-.716 1.601-1.601l.799-7.2H.799zm12-5.599h1.601l-.802 5.599h-1.601zm-4.8 0h1.601v5.599H8.801zm-3.203 0 .799 5.599H4.8L4.001 12zM18.4 7.999H.799A.8.8 0 0 0 0 8.801V9.6h19.2v-.799a.8.8 0 0 0-.799-.802"/></svg>'
    );

    // Get selected cart icon or fallback to default
    $selected_icon = isset($cart_icons[$cart_icon]) ? $cart_icons[$cart_icon] : $cart_icons['cart'];
    $allowed_svg = array(
        'svg' => array(
            'xmlns' => array(),
            'viewBox' => array(),
            'viewbox' => array(),  // Add lowercase version just in case
            'width' => array(),
            'height' => array(),
            'role' => array(),
            'aria-hidden' => array(),
            'aria-label' => array(),
            'style' => array(),
            'class' => array(),
            'fill' => array(),
        ),
        'path' => array(
            'd' => array(),
            'fill' => array(),
            'stroke' => array(),
            'stroke-width' => array(),
        ),
    );
?>
    <button class="rwc_cart-button plugincy_pos_<?php echo $position ?>" data-cart-icon="<?php echo esc_attr($cart_icon); ?>" data-product_title_tag="<?php echo esc_attr($product_title_tag); ?>" data-drawer-position="<?php echo esc_attr($drawer_position); ?>" onclick="openCartDrawer('<?php echo esc_attr($drawer_position); ?>')">
        <span class="cart-icon">
            <?php echo wp_kses($selected_icon, $allowed_svg); ?>
        </span>
        <span class="cart-count">
            <?php
            if (function_exists('WC') && WC()->cart) {
                echo esc_html(WC()->cart->get_cart_contents_count());
            } else {
                echo '0';
            }
            ?>
        </span>
    </button>

    <div class="cart-drawer <?php echo $drawer_position ?>">
        <div class="cart-content">
            <div class="cart-header">
                <h2><?php echo get_option("your_cart") ? esc_attr(get_option("your_cart", 'Your Cart')) : "Your Cart"; ?></h2>
                <button class="close_button" onclick="closeCheckoutPopup()"></button>
            </div>

            <?php
            if (function_exists('WC') && WC() && WC()->cart) {
                if (WC()->cart->is_empty()) {
            ?>
                    <div class="cart-items empty-cart-items">
                        <div class="empty-cart">
                            <svg data-icon="icon-checkout" width="56" height="56" viewBox="0 0 24 24" class="plugincy-icon-checkout" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 2.71411C2 2.31972 2.31972 2 2.71411 2H3.34019C4.37842 2 4.97454 2.67566 5.31984 3.34917C5.55645 3.8107 5.72685 4.37375 5.86764 4.86133H20.5709C21.5186 4.86133 22.2035 5.7674 21.945 6.67914L19.809 14.2123C19.4606 15.4413 18.3384 16.2896 17.0609 16.2896H9.80665C8.51866 16.2896 7.39 15.4276 7.05095 14.185L6.13344 10.8225C6.12779 10.8073 6.12262 10.7917 6.11795 10.7758L4.64782 5.78023C4.59738 5.61449 4.55096 5.45386 4.50614 5.29878C4.36354 4.80529 4.23716 4.36794 4.04891 4.00075C3.82131 3.55681 3.61232 3.42822 3.34019 3.42822H2.71411C2.31972 3.42822 2 3.1085 2 2.71411ZM7.49529 10.3874L8.4288 13.8091C8.59832 14.4304 9.16266 14.8613 9.80665 14.8613H17.0609C17.6997 14.8613 18.2608 14.4372 18.435 13.8227L20.5709 6.28955H6.28975L7.49529 10.3874ZM12.0017 19.8577C12.0017 21.0408 11.0426 22 9.85941 22C8.67623 22 7.71708 21.0408 7.71708 19.8577C7.71708 18.6745 8.67623 17.7153 9.85941 17.7153C11.0426 17.7153 12.0017 18.6745 12.0017 19.8577ZM10.5735 19.8577C10.5735 19.4633 10.2538 19.1436 9.85941 19.1436C9.46502 19.1436 9.1453 19.4633 9.1453 19.8577C9.1453 20.2521 9.46502 20.5718 9.85941 20.5718C10.2538 20.5718 10.5735 20.2521 10.5735 19.8577ZM19.1429 19.8577C19.1429 21.0408 18.1837 22 17.0005 22C15.8173 22 14.8582 21.0408 14.8582 19.8577C14.8582 18.6745 15.8173 17.7153 17.0005 17.7153C18.1837 17.7153 19.1429 18.6745 19.1429 19.8577ZM17.7146 19.8577C17.7146 19.4633 17.3949 19.1436 17.0005 19.1436C16.6061 19.1436 16.2864 19.4633 16.2864 19.8577C16.2864 20.2521 16.6061 20.5718 17.0005 20.5718C17.3949 20.5718 17.7146 20.2521 17.7146 19.8577Z" fill="currentColor"></path>
                            </svg>
                            <div class="plugincy-zero-state-title">Your Cart is Empty</div>
                            <?php
                            // Get the shop page URL or fallback to home page
                            $shop_url = get_home_url(); // Default to home page
                            // Check if WooCommerce is active and get shop page ID
                            if (function_exists('wc_get_page_id')) {
                                $shop_page_id = wc_get_page_id('shop');
                                // If shop page exists and is published, use its URL
                                if ($shop_page_id && get_post_status($shop_page_id) === 'publish') {
                                    $shop_url = get_permalink($shop_page_id);
                                }
                            }
                            // Alternative check if WooCommerce functions aren't available
                            elseif (function_exists('get_option')) {
                                $shop_page_id = get_option('woocommerce_shop_page_id');
                                // If shop page exists and is published, use its URL
                                if ($shop_page_id && get_post_status($shop_page_id) === 'publish') {
                                    $shop_url = get_permalink($shop_page_id);
                                }
                            }
                            ?>
                            <a href="<?php echo esc_url($shop_url); ?>" class="plugincy-primary-button plugincy-shop-button plugincy-modal-close">Shop Now</a>
                        </div>
                    </div>
                <?php
                } else {
                    $cart_count = WC()->cart->get_cart_contents_count();
                    $cart_items = WC()->cart->get_cart();
                ?>
                    <div class="cart-selection-bar">
                        <div class="select-all-container">
                            <input type="checkbox" id="select-all-items" class="select-all-checkbox">
                            <label for="select-all-items">Select All</label>
                        </div>
                        <div class="selected-count">
                            <span id="selected-count-text">0 selected</span>
                            <button id="remove-selected" class="remove-selected-button" style="display:none;"><svg style="width: 16px; fill: #ffffff;" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label="">
                                    <path d="M135.2 17.69C140.6 6.848 151.7 0 163.8 0H284.2C296.3 0 307.4 6.848 312.8 17.69L320 32H416C433.7 32 448 46.33 448 64C448 81.67 433.7 96 416 96H32C14.33 96 0 81.67 0 64C0 46.33 14.33 32 32 32H128L135.2 17.69zM31.1 128H416V448C416 483.3 387.3 512 352 512H95.1C60.65 512 31.1 483.3 31.1 448V128zM111.1 208V432C111.1 440.8 119.2 448 127.1 448C136.8 448 143.1 440.8 143.1 432V208C143.1 199.2 136.8 192 127.1 192C119.2 192 111.1 199.2 111.1 208zM207.1 208V432C207.1 440.8 215.2 448 223.1 448C232.8 448 240 440.8 240 432V208C240 199.2 232.8 192 223.1 192C215.2 192 207.1 199.2 207.1 208zM304 208V432C304 440.8 311.2 448 320 448C328.8 448 336 440.8 336 432V208C336 199.2 328.8 192 320 192C311.2 192 304 199.2 304 208z"></path>
                                </svg></button>
                        </div>
                    </div>

                    <div class="cart-items">
                        <?php
                        foreach ($cart_items as $cart_item_key => $cart_item) {
                            $_product = $cart_item['data'];
                            $thumbnail = $_product->get_image();
                            $product_price = wc_price($_product->get_price());
                            $product_quantity = $cart_item['quantity'];
                            $product_total = wc_price($_product->get_price() * $product_quantity);
                        ?>
                            <div class="cart-item" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                                <div class="item-select">
                                    <input type="checkbox" class="item-checkbox" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                                </div>
                                <div class="thumbnail">
                                    <?php echo wp_kses($thumbnail, array(
                                        'img' => array(
                                            'src' => array(),
                                            'alt' => array(),
                                            'class' => array(),
                                        ),
                                    )); ?>
                                    <button class="remove-item" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"><svg style="width: 16px; fill: #ff0000;" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M6 18L18 6M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="item-details">
                                    <div style="display:flex;gap:1rem;">
                                        <<?php echo esc_attr($product_title_tag); ?> class="item-title"><?php echo esc_html($_product->get_name()); ?></<?php echo esc_attr($product_title_tag); ?>>
                                        <p class="item-price"><?php echo wp_kses_post($product_price); ?></p>
                                    </div>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn minus" data-action="minus" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">-</button>
                                        <input type="number" class="item-quantity" value="<?php echo esc_attr($product_quantity); ?>" min="1" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                                        <button class="quantity-btn plus" data-action="plus" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">+</button>
                                    </div>

                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Coupon Section -->
                    <div class="coupon-section">
                        <div class="coupon-form">
                            <input type="text" id="coupon-code" placeholder="Enter coupon code" class="coupon-input">
                            <button id="apply-coupon" class="apply-coupon-button">Apply</button>
                        </div>
                        <div id="coupon-message" class="coupon-message" style="display: none;"></div>
                        <div id="applied-coupons" class="applied-coupons" style="display: <?php echo WC()->cart->get_applied_coupons() ? "block" : "none"; ?>;">
                            <?php
                            if (WC()->cart->get_applied_coupons()) {
                                echo '<h4>Applied Coupons:</h4>';
                                foreach (WC()->cart->get_applied_coupons() as $code) {
                                    $coupon = new WC_Coupon($code);
                                    echo '<div class="applied-coupon">';
                                    echo '<span>' . esc_html($code) . '</span>';
                                    echo '<button class="remove-coupon" data-coupon="' . esc_attr($code) . '">Remove</button>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <!-- You May Also Like Section (if only one product in cart) -->
                    <?php if ($cart_count == 1) : ?>
                        <div class="you-may-also-like">
                            <h3>You May Also Like</h3>
                            <div class="recommended-products">
                                <?php
                                // Get the single product in cart
                                $cart_product = reset($cart_items);
                                $product_id = $cart_product['product_id'];

                                // Get related products
                                $related_products = wc_get_related_products($product_id, 10); // Get more initially to filter

                                if ($related_products) {
                                    $simple_products_shown = 0;
                                    $max_products = 3;

                                    foreach ($related_products as $related_product) {
                                        if ($simple_products_shown >= $max_products) {
                                            break; // Stop when we have enough simple products
                                        }

                                        $product = wc_get_product($related_product);

                                        // Check if product exists and is a simple product
                                        if ($product && $product->get_type() === 'simple') {
                                            echo '<div class="recommended-product">';
                                            echo '<a href="' . esc_url($product->get_permalink()) . '">';
                                            echo $product->get_image();
                                            echo '<h4>' . esc_html($product->get_name()) . '</h4>';
                                            echo '<span class="price">' . wp_kses_post($product->get_price_html()) . '</span>';
                                            echo '</a>';
                                            echo '<button class="add-to-cart-button" data-product-id="' . esc_attr($product->get_id()) . '">Add to Cart</button>';
                                            echo '</div>';

                                            $simple_products_shown++;
                                        }
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Cart Summary -->
                    <div class="cart-summary">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span><?php echo wp_kses_post(wc_price(WC()->cart->get_subtotal())); ?></span>
                        </div>

                        <div class="summary-row discount">
                            <?php if (WC()->cart->get_discount_total() > 0) : ?>
                                <span>Discount</span>
                                <span>- <?php echo wp_kses_post(wc_price(WC()->cart->get_discount_total())); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="summary-row total">
                            <span>Total</span>
                            <span><?php echo wp_kses_post(wc_price(WC()->cart->get_total('raw'))); ?></span>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <div class="cart-actions">
                        <a href="#checkout-popup" style="display: none;flex-direction: column;justify-content: center;align-items: center;" class="checkout-button checkout-button-drawer-link">Checkout</a>
                        <!-- <button class="checkout-button checkout-button-drawer" onclick="openCheckoutPopup()">
                            <?php //echo get_option("txt_checkout") ? esc_attr(get_option("txt_checkout", 'Checkout')) : "Checkout"; 
                            ?>
                        </button> -->
                        <a href="<?php echo wc_get_checkout_url(); ?>" class="checkout-button checkout-button-drawer">
                            <?php echo get_option("txt_checkout") ? esc_attr(get_option("txt_checkout", 'Checkout')) : "Checkout"; ?>
                        </a>
                    </div>
            <?php }
            } else {
                // Fallback when WooCommerce is not initialized
                echo '<p>Your cart is currently empty.</p>';
            } ?>
        </div>
    </div>
    <div class="overlay"></div>

    <?php if (get_option("rmenu_enable_sticky_cart", 0)) : ?>
        <style>
            :root {
                --cart-top: <?php echo esc_attr(get_option('rmenu_cart_top_position', '50%')); ?>;
                --cart-left: <?php echo esc_attr(get_option('rmenu_cart_left_position', '100%')); ?>;
                <?php
                $border_radius = get_option('rmenu_cart_border_radius', '5px 0 0 5px');
                if ($border_radius == '50') {
                    echo '--cart-radius: 50%;';
                    echo '--cart-width: 50px;';
                    echo '--cart-height: 50px;';
                    echo '--cart-padding: 0;';
                } else {
                    echo '--cart-radius: ' . esc_attr($border_radius) . ';';
                    echo '--cart-width: auto;';
                    echo '--cart-height: auto;';
                    echo '--cart-padding: 15px;';
                }
                ?>--cart-bg: <?php echo esc_attr(get_option('rmenu_cart_bg_color', '#96588a')); ?>;
                --cart-text: <?php echo esc_attr(get_option('rmenu_cart_text_color', '#ffffff')); ?>;
                --cart-hover-bg: <?php echo esc_attr(get_option('rmenu_cart_hover_bg', '#f8f8f8')); ?>;
                --cart-hover-text: <?php echo esc_attr(get_option('rmenu_cart_hover_text', '#000000')); ?>;

                /* New variables for cart drawer styling */
                --primary-color: <?php echo esc_attr(get_option('rmenu_primary_color', '#4a90e2')); ?>;
                --secondary-color: <?php echo esc_attr(get_option('rmenu_secondary_color', '#f8f8f8')); ?>;
                --text-color: <?php echo esc_attr(get_option('rmenu_text_color', '#333333')); ?>;
                --border-color: <?php echo esc_attr(get_option('rmenu_border_color', '#e1e1e1')); ?>;
                --success-color: <?php echo esc_attr(get_option('rmenu_success_color', '#4caf50')); ?>;
                --danger-color: <?php echo esc_attr(get_option('rmenu_danger_color', '#f44336')); ?>;
            }

            /* Cart Button Styles */
            .plugincy_pos_,
            .plugincy_pos_fixed {
                position: fixed;
                top: var(--cart-top);
                left: var(--cart-left);
                border-radius: var(--cart-radius);
                background: var(--cart-bg);
                color: var(--cart-text);
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                width: var(--cart-width);
                height: var(--cart-height);
                padding: var(--cart-padding);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                z-index: 999;
                transform: translateX(-100%);
            }

            .plugincy_pos_:hover,
            .plugincy_pos_fixed:hover {
                background: var(--cart-hover-bg);
                color: var(--cart-hover-text);
                transform: translate(-100%, -2px);
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            }

            .plugincy_pos_ .cart-icon svg,
            .plugincy_pos_fixed .cart-icon svg {
                fill: var(--cart-text);
                transition: fill 0.3s ease;
                width: 24px;
                height: 24px;
            }

            .plugincy_pos_:hover .cart-icon svg,
            .plugincy_pos_fixed:hover .cart-icon svg {
                fill: var(--cart-hover-text);
            }

            .cart-icon {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            span.cart-count {
                position: absolute;
                top: <?php echo ($border_radius == '50') ? '-8px' : '-5px'; ?>;
                <?php
                echo ($border_radius == '50') ? 'right: -8px; left: auto;' : 'left: -6px;';
                ?>padding: 0;
                border-radius: 50%;
                background: #ff4757;
                color: white;
                font-size: 12px;
                font-weight: bold;
                min-width: 20px;
                text-align: center;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
                aspect-ratio: 1;
                width: 20px;
                height: 20px;
                box-sizing: border-box;
            }

            /* Cart Drawer Styles */
            .cart-drawer {
                position: fixed;
                top: 0;
                width: 100%;
                max-width: 450px;
                height: 100%;
                background: #fff;
                box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
                z-index: 999999;
                transform: translateX(100%);
                transition: transform 0.3s ease;
                display: flex;
                flex-direction: column;
                padding-bottom: 20px;
                padding-top: 10px;
            }

            .cart-drawer.right {
                right: 0;
                left: auto;
                transform: translateX(100%);
            }

            .cart-drawer.left {
                left: 0;
                right: auto;
                transform: translateX(-100%);
            }

            .cart-drawer .cart-content {
                overflow-y: auto;
                flex: 1;
                display: flex;
                flex-direction: column;
            }

            .cart-drawer .cart-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 1px solid var(--border-color);
            }

            .cart-drawer .cart-header h2 {
                margin: 0;
                font-size: 24px;
                color: var(--text-color);
            }

            .cart-drawer .close_button {
                background: none;
                border: none;
                cursor: pointer;
                color: var(--text-color);
                padding: 5px;
                border-radius: 50%;
                transition: background-color 0.2s;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 20px;
                height: 20px;
            }

            .cart-drawer .close_button:hover {
                background-color: var(--secondary-color);
            }

            .cart-drawer .cart-selection-bar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
                padding: 10px;
                background-color: var(--secondary-color);
                border-radius: 8px;
            }

            .cart-drawer .select-all-container {
                display: flex;
                align-items: center;
            }

            .cart-drawer .select-all-container input {
                margin-right: 8px;
            }

            .cart-drawer .selected-count {
                display: flex;
                align-items: center;
            }

            .cart-drawer .remove-selected-button {
                background: var(--danger-color);
                color: white;
                border: none;
                border-radius: 4px;
                padding: 5px 10px;
                margin-left: 10px;
                cursor: pointer;
                font-size: 12px;
            }

            .cart-drawer .cart-items {
                margin-bottom: 20px;
                padding: 0 9px;
                min-height: 130px;
            }

            .cart-drawer .cart-item {
                display: flex;
                align-items: flex-start;
                padding: 1rem 0;
                border-bottom: 1px solid var(--border-color);
            }

            .cart-drawer .item-select {
                padding-top: 5px;
            }

            .cart-drawer .thumbnail {
                width: 80px;
                height: 80px;
                flex-shrink: 0;
                position: relative;
                border: 1px solid var(--border-color);
                border-radius: 7px;
            }

            .cart-drawer .thumbnail img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: 6px;
            }

            .cart-drawer .item-details {
                flex: 1;
            }

            .cart-drawer .item-title {
                margin: 0 0 5px;
                font-size: 16px;
                color: var(--text-color);
            }

            .cart-drawer .item-price {
                margin: 0 0 10px;
                color: var(--primary-color);
                font-weight: 500;
            }

            .cart-drawer .quantity-controls {
                display: flex;
                align-items: center;
                margin-bottom: 10px;
            }

            .cart-drawer .quantity-btn {
                width: 28px;
                height: 28px;
                border: 1px solid var(--border-color);
                background: white;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                display: flex !important;
                align-items: center;
                justify-content: center;
                visibility: visible !important;
                padding: 10px;
                color: var(--text-color);
            }

            .cart-drawer .quantity-btn:focus {
                color: var(--text-color);
            }

            .cart-drawer .quantity-btn:hover {
                background: var(--secondary-color);
            }

            .cart-drawer .item-quantity {
                width: 43px;
                text-align: center;
                border: none;
                border-radius: 4px !important;
                margin: 0 -12px 0 3px;
                padding: 0 0px 0 0 !important;
                height: min-content;
            }

            .cart-drawer .item-total {
                font-weight: 600;
                color: var(--text-color);
                margin-bottom: 10px;
            }

            .cart-drawer .remove-item {
                background: #fff;
                border: none;
                color: var(--danger-color);
                cursor: pointer;
                font-size: 14px;
                display: flex;
                align-items: center;
                position: absolute;
                top: -8px;
                left: -6px;
                border-radius: 100%;
                padding: 0;
                box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
                width: 20px;
                height: 20px;
                justify-content: center;
                z-index: 99999;
            }

            .cart-drawer .coupon-section {
                margin-bottom: 20px;
                padding: 15px;
                background-color: var(--secondary-color);
                border-radius: 8px;
            }

            .cart-drawer .coupon-form {
                display: flex;
                margin-bottom: 10px;
            }

            .cart-drawer .coupon-input {
                flex: 1;
                padding: 10px;
                border: 1px solid var(--border-color);
                border-radius: 4px 0 0 4px;
                font-size: 14px;
            }

            .cart-drawer .apply-coupon-button {
                background: var(--primary-color);
                color: white;
                border: none;
                border-radius: 0 4px 4px 0;
                padding: 0 15px;
                cursor: pointer;
                font-weight: 500;
            }

            .cart-drawer .coupon-message {
                font-size: 14px;
                margin-bottom: 10px;
                min-height: 20px;
            }

            .cart-drawer .coupon-message.success {
                color: var(--success-color);
            }

            .cart-drawer .coupon-message.error {
                color: var(--danger-color);
            }

            .cart-drawer .applied-coupons {
                margin-top: 10px;
            }

            .cart-drawer .applied-coupons h4 {
                margin: 0 0 10px;
                font-size: 14px;
                color: var(--text-color);
            }

            .cart-drawer .applied-coupon {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px;
                background: white;
                border-radius: 4px;
                margin-bottom: 5px;
            }

            .cart-drawer .remove-coupon {
                background: none;
                border: none;
                color: var(--danger-color);
                cursor: pointer;
                font-size: 12px;
            }

            .cart-drawer .cart-summary {
                padding: 15px;
                background-color: var(--secondary-color);
                border-radius: 8px;
                margin-top: auto;
            }

            .cart-drawer .summary-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 10px;
                font-size: 14px;
            }

            .cart-drawer .summary-row:last-child {
                margin-bottom: 0;
            }

            .cart-drawer .summary-row.discount {
                color: var(--success-color);
            }

            .cart-drawer .summary-row.total {
                font-weight: 600;
                font-size: 16px;
                padding-top: 10px;
                border-top: 1px solid var(--border-color);
            }

            .cart-drawer .checkout-button {
                width: 100%;
                padding: 12px;
                background: var(--primary-color);
                color: white;
                border: none;
                border-radius: 6px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: background-color 0.2s;
                text-decoration: none;
            }

            .cart-drawer .checkout-button:hover {
                background: color-mix(in srgb, var(--primary-color) 90%, black);
            }

            .cart-drawer .you-may-also-like {
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid var(--border-color);
            }

            .cart-drawer .you-may-also-like h3 {
                margin: 0 0 15px;
                font-size: 18px;
                color: var(--text-color);
            }

            .cart-drawer .recommended-products {
                display: flex;
                gap: 15px;
                flex-direction: row;
                flex-wrap: nowrap;
                width: 100%;
                overflow: auto;
                padding-bottom: 10px;
                scrollbar-width: thin;
            }

            .cart-drawer .recommended-products a {
                text-decoration: none;
            }

            .cart-drawer .recommended-product {
                text-align: center;
                max-width: 200px;
                min-width: 30%;
            }

            .cart-drawer .recommended-product img {
                width: 100%;
                height: 100px;
                object-fit: cover;
                border-radius: 6px;
                margin-bottom: 8px;
            }

            .cart-drawer .recommended-product h4 {
                margin: 0 0 5px;
                font-size: 14px;
                color: var(--text-color);
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .cart-drawer .recommended-product .price {
                display: block;
                margin-bottom: 8px;
                font-size: 14px;
                color: var(--primary-color);
            }

            .cart-drawer .add-to-cart-button {
                width: 100%;
                padding: 6px;
                background: var(--primary-color);
                color: white;
                border: none;
                border-radius: 4px;
                font-size: 12px;
                cursor: pointer;
            }

            .cart-drawer .overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                display: none;
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {

                .plugincy_pos_,
                .plugincy_pos_fixed {
                    top: var(--cart-top);
                    left: var(--cart-left);
                    transform: translate(-100%);
                    <?php if ($border_radius == '50'): ?>border-radius: 50%;
                    width: 50px;
                    height: 50px;
                    padding: 0;
                    <?php else: ?>border-radius: 50px;
                    padding: 12px 20px;
                    <?php endif; ?>
                }

                .plugincy_pos_:hover,
                .plugincy_pos_fixed:hover {
                    transform: translateX(-50%) translateY(-2px);
                }

                <?php if ($border_radius == '50'): ?>.cart-icon {
                    margin-right: 0;
                }

                span.cart-count {
                    top: -8px;
                    right: -8px;
                    left: auto;
                }

                <?php endif; ?>.cart-drawer {
                    max-width: 100%;
                }

                .cart-drawer .recommended-products {
                    grid-template-columns: repeat(2, 1fr);
                }
            }
        </style>
<?php endif;
}
