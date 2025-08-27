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
    onepaquc_cart(esc_attr($atts['drawer']), esc_attr($atts['cart_icon']), esc_attr($atts['product_title_tag']), esc_attr($atts['position']), esc_attr($atts['top']), esc_attr($atts['left'])); ?>
</div>
    <?php
    return ob_get_clean();
}
add_shortcode('plugincy_cart', 'onepaquc_cart_shortcode');