<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

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
    <div class="cart-drawer right">
        <div class="cart-content">
            <div class="close_button"> <svg onclick="closeCheckoutPopup()" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 320 512" role="graphics-symbol" aria-hidden="false" aria-label="">
                    <path d="M310.6 361.4c12.5 12.5 12.5 32.75 0 45.25C304.4 412.9 296.2 416 288 416s-16.38-3.125-22.62-9.375L160 301.3L54.63 406.6C48.38 412.9 40.19 416 32 416S15.63 412.9 9.375 406.6c-12.5-12.5-12.5-32.75 0-45.25l105.4-105.4L9.375 150.6c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L160 210.8l105.4-105.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-105.4 105.4L310.6 361.4z"></path>
                </svg> </div>
            <h2><?php echo get_option("your_cart") ? esc_attr(get_option("your_cart", 'Your Cart')) : "Your Cart"; ?></h2>
            <div class="cart-items">
                <?php
                if (function_exists('WC') && WC() && WC()->cart) {
                    if (WC()->cart->is_empty()) {
                ?>
                        <p>Your cart is currently empty.</p>
                        <?php
                    } else {
                        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                            $_product = $cart_item['data'];
                            $thumbnail = $_product->get_image();
                        ?>
                            <div class="cart-item">
                                <div class="thumbnail">
                                    <?php echo wp_kses($thumbnail, array(
                                        'img' => array(
                                            'src' => array(),
                                            'alt' => array(),
                                            'class' => array(),
                                            // Add other attributes as needed
                                        ),
                                    )); ?>
                                </div>
                                <div>
                                    <<?php echo esc_attr($product_title_tag); ?> class="item-title"><?php echo esc_html($_product->get_name()); ?></<?php echo esc_attr($product_title_tag); ?>>
                                    <p class="item-price"><?php echo wp_kses_post(wc_price($_product->get_price())); ?></p>
                                    <div class="quantity">
                                        <input type="number" class="item-quantity" value="<?php echo esc_attr($cart_item['quantity']); ?>" min="1">
                                        <button class="remove-item" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"><?php echo get_option("btn_remove") ? esc_attr(get_option("btn_remove", 'Remove')) : '<svg style="width: 16px; fill: #ff0000;" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M135.2 17.69C140.6 6.848 151.7 0 163.8 0H284.2C296.3 0 307.4 6.848 312.8 17.69L320 32H416C433.7 32 448 46.33 448 64C448 81.67 433.7 96 416 96H32C14.33 96 0 81.67 0 64C0 46.33 14.33 32 32 32H128L135.2 17.69zM31.1 128H416V448C416 483.3 387.3 512 352 512H95.1C60.65 512 31.1 483.3 31.1 448V128zM111.1 208V432C111.1 440.8 119.2 448 127.1 448C136.8 448 143.1 440.8 143.1 432V208C143.1 199.2 136.8 192 127.1 192C119.2 192 111.1 199.2 111.1 208zM207.1 208V432C207.1 440.8 215.2 448 223.1 448C232.8 448 240 440.8 240 432V208C240 199.2 232.8 192 223.1 192C215.2 192 207.1 199.2 207.1 208zM304 208V432C304 440.8 311.2 448 320 448C328.8 448 336 440.8 336 432V208C336 199.2 328.8 192 320 192C311.2 192 304 199.2 304 208z"></path></svg>'; ?></button>
                                    </div>
                                </div>
                            </div>
                <?php }
                    }
                } else {
                    // Fallback when WooCommerce is not initialized
                    echo '<p>Your cart is currently empty.</p>';
                } ?>
            </div>

        </div>
        <div>
            <?php if (function_exists('WC') && WC() && WC()->cart) {
                if (!WC()->cart->is_empty()) { ?>
                    <div class="cart-subtotal">
                        <span><?php echo get_option("txt_subtotal") ? esc_attr(get_option("txt_subtotal", 'Subtotal: ')) : "Subtotal: "; ?><?php echo wp_kses_post(wc_price(WC()->cart->get_subtotal())); ?></span>
                    </div>
                    <a href="#checkout-popup" style="display: none;flex-direction: column;justify-content: center;align-items: center;" class="checkout-button checkout-button-drawer-link"><?php echo get_option("txt_checkout") ? esc_attr(get_option("txt_checkout", 'Checkout')) : "Checkout"; ?></a>
                    <button class="checkout-button checkout-button-drawer" onclick="openCheckoutPopup()"><?php echo get_option("txt_checkout") ? esc_attr(get_option("txt_checkout", 'Checkout')) : "Checkout"; ?></button>
                <?php } ?>

        </div>
    </div>
    <div class="overlay"></div>
    <?php if (get_option("rmenu_enable_sticky_cart", 1)) : ?>
        <style>
            :root {
                --cart-top: <?php echo esc_attr(get_option('rmenu_cart_top_position', '50%')); ?>;
                --cart-left: <?php echo esc_attr(get_option('rmenu_cart_left_position', '97%')); ?>;
                <?php
                    $border_radius = get_option('rmenu_cart_border_radius', '5');
                    if ($border_radius == '50') {
                        echo '--cart-radius: 50%;';
                        echo '--cart-width: 50px;';
                        echo '--cart-height: 50px;';
                        echo '--cart-padding: 0;';
                    } else {
                        echo '--cart-radius: ' . esc_attr($border_radius) . 'px;';
                        echo '--cart-width: auto;';
                        echo '--cart-height: auto;';
                        echo '--cart-padding: 10px 15px;';
                    }
                ?>--cart-bg: <?php echo esc_attr(get_option('rmenu_cart_bg_color', '#96588a')); ?>;
                --cart-text: <?php echo esc_attr(get_option('rmenu_cart_text_color', '#ffffff')); ?>;
                --cart-hover-bg: <?php echo esc_attr(get_option('rmenu_cart_hover_bg', '#f8f8f8')); ?>;
                --cart-hover-text: <?php echo esc_attr(get_option('rmenu_cart_hover_text', '#000000')); ?>;
            }

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
            }

            .plugincy_pos_:hover,
            .plugincy_pos_fixed:hover {
                background: var(--cart-hover-bg);
                color: var(--cart-hover-text);
                transform: translateY(-2px);
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
                margin-right: <?php echo ($border_radius == '50') ? '0' : '8px'; ?>;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            span.cart-count {
                position: absolute;
                top: <?php echo ($border_radius == '50') ? '-8px' : '-5px'; ?>;
                <?php echo ($border_radius == '50') ? 'right: -8px; left: auto;' : 'left: -6px;'; ?>padding: 3px 7px;
                border-radius: 50%;
                background: #ff4757;
                color: white;
                font-size: 12px;
                font-weight: bold;
                min-width: 20px;
                text-align: center;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {

                .plugincy_pos_,
                .plugincy_pos_fixed {
                    top: auto;
                    bottom: 20px;
                    left: 50%;
                    transform: translateX(-50%);
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

                <?php endif; ?>
            }
        </style>
<?php endif;
            }
        }
