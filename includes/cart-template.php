<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

// Shortcode to display cart icon and drawer
function rmenu_cart()
{ ?>

    <button class="rwc_cart-button" onclick="openCartDrawer('right')">
        <span class="cart-icon"><svg style="width: 18px; fill: #fff;" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 576 512" role="graphics-symbol" aria-hidden="false" aria-label="">
                <path d="M96 0C107.5 0 117.4 8.19 119.6 19.51L121.1 32H541.8C562.1 32 578.3 52.25 572.6 72.66L518.6 264.7C514.7 278.5 502.1 288 487.8 288H170.7L179.9 336H488C501.3 336 512 346.7 512 360C512 373.3 501.3 384 488 384H159.1C148.5 384 138.6 375.8 136.4 364.5L76.14 48H24C10.75 48 0 37.25 0 24C0 10.75 10.75 0 24 0H96zM128 464C128 437.5 149.5 416 176 416C202.5 416 224 437.5 224 464C224 490.5 202.5 512 176 512C149.5 512 128 490.5 128 464zM512 464C512 490.5 490.5 512 464 512C437.5 512 416 490.5 416 464C416 437.5 437.5 416 464 416C490.5 416 512 437.5 512 464z"></path>
            </svg></span>
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
            <div class="close_button"> <img onclick="closeCheckoutPopup()" src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/image/close.png'); ?>" /> </div>
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
                                    <h3 class="item-title"><?= esc_html($_product->get_name()); ?></h3>
                                    <p class="item-price"><?= wp_kses_post(wc_price($_product->get_price())); ?></p>
                                    <div class="quantity">
                                        <input type="number" class="item-quantity" value="<?= esc_attr($cart_item['quantity']); ?>" min="1">
                                        <button class="remove-item" data-cart-item-key="<?= esc_attr($cart_item_key); ?>"><?php echo get_option("btn_remove") ? esc_attr(get_option("btn_remove", 'Remove')) : '<svg style="width: 16px; fill: #ff0000;" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M135.2 17.69C140.6 6.848 151.7 0 163.8 0H284.2C296.3 0 307.4 6.848 312.8 17.69L320 32H416C433.7 32 448 46.33 448 64C448 81.67 433.7 96 416 96H32C14.33 96 0 81.67 0 64C0 46.33 14.33 32 32 32H128L135.2 17.69zM31.1 128H416V448C416 483.3 387.3 512 352 512H95.1C60.65 512 31.1 483.3 31.1 448V128zM111.1 208V432C111.1 440.8 119.2 448 127.1 448C136.8 448 143.1 440.8 143.1 432V208C143.1 199.2 136.8 192 127.1 192C119.2 192 111.1 199.2 111.1 208zM207.1 208V432C207.1 440.8 215.2 448 223.1 448C232.8 448 240 440.8 240 432V208C240 199.2 232.8 192 223.1 192C215.2 192 207.1 199.2 207.1 208zM304 208V432C304 440.8 311.2 448 320 448C328.8 448 336 440.8 336 432V208C336 199.2 328.8 192 320 192C311.2 192 304 199.2 304 208z"></path></svg>'; ?></button>
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
                        <span><?php echo get_option("txt_subtotal") ? esc_attr(get_option("txt_subtotal", 'Subtotal: ')) : "Subtotal: "; ?><?= wp_kses_post(wc_price(WC()->cart->get_subtotal())); ?></span>
                    </div>
                    <a href="#checkout-popup" style="display: none;flex-direction: column;justify-content: center;align-items: center;" class="checkout-button checkout-button-drawer-link"><?php echo get_option("txt_checkout") ? esc_attr(get_option("txt_checkout", 'Checkout')) : "Checkout"; ?></a>
                    <button class="checkout-button checkout-button-drawer" onclick="openCheckoutPopup()"><?php echo get_option("txt_checkout") ? esc_attr(get_option("txt_checkout", 'Checkout')) : "Checkout"; ?></button>
            <?php }?>
                                                                                                                                                                                                                                                                                
        </div>
    </div>
    <div class="overlay"></div>
<?php
}
}
