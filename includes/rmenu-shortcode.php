<?php

// Shortcode to display cart icon and drawer
function rmenu_cart_shortcode($atts) {
    $atts = shortcode_atts(array('drawer' => 'right'), $atts);

    ob_start(); ?>

<div class="rmenu-cart">
    <?php require_once plugin_dir_path(__FILE__) . 'cart-template.php'; ?>
</div>


    <div class="checkout-popup" style="display:none;">
    <?php require_once plugin_dir_path(__FILE__) . 'popup-template.php'; ?>
</div>

    <?php
    return ob_get_clean();
}
add_shortcode('rmenu_cart', 'rmenu_cart_shortcode');