<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
function onepaquc_rmenu_checkout($isonepagewidget = false)
{
?>
  <div class="popup-content">
    <?php if (!$isonepagewidget) { ?>
      <div style=" display: flex; justify-content: space-between; ">
        <h2><?php echo get_option("txt_checkout") ? esc_attr(get_option("txt_checkout", 'Checkout')) : "Checkout"; ?></h2>
        <div class="close_button" onclick="closeCheckoutPopup()">
        </div>
      </div>

    <?php } ?>

    <div class="popup-message"></div>
    <div id="checkout-form"><?php echo do_shortcode('[woocommerce_checkout]'); ?></div>
    <?php if (get_option("hide_product")) { ?>
      <div class="cart-subtotal">
        <span><?php echo get_option("txt_total") ? esc_attr(get_option("txt_total", 'Subtotal: ')) : "Subtotal: "; ?> <?php echo wp_kses_post(wc_price(WC()->cart->get_subtotal())); ?></span>
      </div><?php } ?>
  </div>
<?php
}
