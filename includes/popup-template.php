<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
function rmenu_checkout()
{
?>
  <div class="popup-content">
    <div class="close_button">
      <h2><?php echo get_option("txt_checkout") ? esc_attr(get_option("txt_checkout", 'Checkout')) : "Checkout"; ?></h2>
      <img onclick="closeCheckoutPopup()" src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../assets/image/close.png'); ?>" />
    </div>

    <div class="popup-message"></div>
    <div id="checkout-form"><?php echo do_shortcode('[woocommerce_checkout]'); ?></div>
    <?php if (get_option("hide_product")) { ?>
      <div class="cart-subtotal">
        <span><?php echo get_option("txt_total") ? esc_attr(get_option("txt_total", 'Subtotal: ')) : "Subtotal: "; ?> <?= wp_kses_post(wc_price(WC()->cart->get_subtotal())); ?></span>
      </div><?php } ?>
  </div>
<?php
}
