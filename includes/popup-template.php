<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
function onepaquc_rmenu_checkout($isonepagewidget = false)
{
?>
  <div class="popup-content">
    <?php if (!$isonepagewidget) { ?>
      <div style=" display: flex; justify-content: space-between; ">
        <h2><?php echo esc_html(onepaquc_get_text_option('txt_checkout', __('Checkout', 'one-page-quick-checkout-for-woocommerce'))); ?></h2>
        <div class="close_button" onclick="closeCheckoutPopup()">
        </div>
      </div>

    <?php } ?>

    <div class="popup-message"></div>
    <div id="checkout-form"><?php echo do_shortcode('[woocommerce_checkout]'); ?></div>
    <?php if (get_option("hide_product")) { ?>
      <div class="cart-subtotal">
        <?php $onepaquc_popup_cart = function_exists('onepaquc_get_wc_cart') ? onepaquc_get_wc_cart() : null; ?>
        <span><?php echo esc_html(onepaquc_get_text_option('txt_total', __('Subtotal:', 'one-page-quick-checkout-for-woocommerce'))); ?> <?php echo $onepaquc_popup_cart ? wp_kses_post(wc_price($onepaquc_popup_cart->get_subtotal())) : ''; ?></span>
      </div><?php } ?>
  </div>
<?php
}
