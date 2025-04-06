<?php
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
// Shortcode to display cart icon and drawer
function rmenu_cart_shortcode($atts) {
    $atts = shortcode_atts(array('drawer' => 'right'), $atts);

    ob_start(); ?>

<div class="rmenu-cart">
    <?php 
    rmenu_cart();
    ?>
</div>

    <?php
    return ob_get_clean();
}
add_shortcode('rmenu_cart', 'rmenu_cart_shortcode');