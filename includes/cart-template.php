<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

// Shortcode to display cart icon and drawer
function onepaquc_cart($drawer_position = 'right',$cart_icon = 'cart',$product_title_tag = 'p' )
{ 
    $cart_icons = array(
        'cart' => '<svg xmlns="https://www.w3.org/2000/svg" viewBox="0 0 576 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M96 0C107.5 0 117.4 8.19 119.6 19.51L121.1 32H541.8C562.1 32 578.3 52.25 572.6 72.66L518.6 264.7C514.7 278.5 502.1 288 487.8 288H170.7L179.9 336H488C501.3 336 512 346.7 512 360C512 373.3 501.3 384 488 384H159.1C148.5 384 138.6 375.8 136.4 364.5L76.14 48H24C10.75 48 0 37.25 0 24C0 10.75 10.75 0 24 0H96zM128 464C128 437.5 149.5 416 176 416C202.5 416 224 437.5 224 464C224 490.5 202.5 512 176 512C149.5 512 128 490.5 128 464zM512 464C512 490.5 490.5 512 464 512C437.5 512 416 490.5 416 464C416 437.5 437.5 416 464 416C490.5 416 512 437.5 512 464z"></path></svg>',
        
        'shopping-bag' => '<svg xmlns="https://www.w3.org/2000/svg" viewBox="0 0 448 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M112 112C112 50.14 162.1 0 224 0C285.9 0 336 50.14 336 112V160H400C426.5 160 448 181.5 448 208V416C448 469 405 512 352 512H96C42.98 512 0 469 0 416V208C0 181.5 21.49 160 48 160H112V112zM160 160H288V112C288 76.65 259.3 48 224 48C188.7 48 160 76.65 160 112V160zM136 256C149.3 256 160 245.3 160 232C160 218.7 149.3 208 136 208C122.7 208 112 218.7 112 232C112 245.3 122.7 256 136 256zM312 208C298.7 208 288 218.7 288 232C288 245.3 298.7 256 312 256C325.3 256 336 245.3 336 232C336 218.7 325.3 208 312 208z"></path></svg>',
        
        'basket' => '<svg xmlns="https://www.w3.org/2000/svg" viewBox="0 0 576 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M171.7 191.1H404.3L322.7 35.07C316.6 23.31 321.2 8.821 332.9 2.706C344.7-3.409 359.2 1.167 365.3 12.93L458.4 191.1H544C561.7 191.1 576 206.3 576 223.1C576 241.7 561.7 255.1 544 255.1L492.1 463.5C484.1 492 459.4 512 430 512H145.1C116.6 512 91 492 83.88 463.5L32 255.1C14.33 255.1 0 241.7 0 223.1C0 206.3 14.33 191.1 32 191.1H117.6L210.7 12.93C216.8 1.167 231.3-3.409 243.1 2.706C254.8 8.821 259.4 23.31 253.3 35.07L171.7 191.1zM191.1 303.1C191.1 295.1 184.8 287.1 175.1 287.1C167.2 287.1 159.1 295.1 159.1 303.1V399.1C159.1 408.8 167.2 415.1 175.1 415.1C184.8 415.1 191.1 408.8 191.1 399.1V303.1zM271.1 303.1V399.1C271.1 408.8 279.2 415.1 287.1 415.1C296.8 415.1 304 408.8 304 399.1V303.1C304 295.1 296.8 287.1 287.1 287.1C279.2 287.1 271.1 295.1 271.1 303.1zM416 303.1C416 295.1 408.8 287.1 400 287.1C391.2 287.1 384 295.1 384 303.1V399.1C384 408.8 391.2 415.1 400 415.1C408.8 415.1 416 408.8 416 399.1V303.1z"></path></svg>'
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

    <button class="rwc_cart-button" data-cart-icon = "<?php echo esc_attr($cart_icon); ?>" data-product_title_tag = "<?php echo esc_attr($product_title_tag); ?>" data-drawer-position = "<?php echo esc_attr($drawer_position); ?>" onclick="openCartDrawer('<?php echo esc_attr($drawer_position); ?>')">
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
            <div class="close_button"> <svg onclick="closeCheckoutPopup()" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 320 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M310.6 361.4c12.5 12.5 12.5 32.75 0 45.25C304.4 412.9 296.2 416 288 416s-16.38-3.125-22.62-9.375L160 301.3L54.63 406.6C48.38 412.9 40.19 416 32 416S15.63 412.9 9.375 406.6c-12.5-12.5-12.5-32.75 0-45.25l105.4-105.4L9.375 150.6c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L160 210.8l105.4-105.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-105.4 105.4L310.6 361.4z"></path></svg> </div>
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
            <?php }?>
                                                                                                                                                                                                                                                                                
        </div>
    </div>
    <div class="overlay"></div>
<?php
}
}
