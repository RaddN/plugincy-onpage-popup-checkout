<?php
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
// Shortcode to display cart icon and drawer
function onepaquc_cart_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'drawer' => 'right',
            'cart_icon'=>"cart",
            "product_title_tag"=>"cart",
            "position" => "",
            "top" => "",
            "left" => ""
        ), $atts);

    ob_start(); ?>

<div class="rmenu-cart">
    <?php 
    onepaquc_cart($atts['drawer'],$atts['cart_icon'],$atts['product_title_tag'],$atts['position'],$atts['top'],$atts['left']); ?>
</div>
    <?php
    return ob_get_clean();
}
add_shortcode('plugincy_cart', 'onepaquc_cart_shortcode');