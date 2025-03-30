<button class="cart-button" onclick="openCartDrawer()">
    <span class="cart-icon">🛒</span>
    <span class="cart-count"><?= esc_html(WC()->cart->get_cart_contents_count()); ?></span>
</button>
<div class="cart-drawer right">
    <div class="cart-content">
    <div class="close_button"> <img onclick="closeCheckoutPopup()" src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/image/close.png'); ?>"/> </div>
        <h2><?php echo get_option("your_cart")? esc_attr(get_option("your_cart", 'Your Cart')):"Your Cart";?></h2>
        <div class="cart-items">
            <?php if (WC()->cart->is_empty()) { ?>
                <p>Your cart is currently empty.</p>
            <?php } else {
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
                                <button class="remove-item" data-cart-item-key="<?= esc_attr($cart_item_key); ?>"><?php  echo get_option("btn_remove")? esc_attr(get_option("btn_remove", 'Remove')) : "Remove";?></button>
                            </div>
                        </div>
                    </div>
                <?php }
            } ?>
        </div>
       
    </div>
    <div>
    <?php if (!WC()->cart->is_empty()) { ?>
            <div class="cart-subtotal">
                <span><?php echo get_option("txt_subtotal")? esc_attr(get_option("txt_subtotal", 'Subtotal: ')):"Subtotal: ";?><?= wp_kses_post(wc_price(WC()->cart->get_subtotal())); ?></span>
            </div>
            <button class="checkout-button" onclick="openCheckoutPopup()"><?php echo get_option("txt_checkout")? esc_attr(get_option("txt_checkout", 'Checkout')):"Checkout";?></button>
        <?php } ?>
    </div>
</div>
<div class="overlay"></div>
