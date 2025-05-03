<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
function onepaquc_rmenu_checkout($isonepagewidget=false)
{
?>
  <div class="popup-content">
    <?php if(!$isonepagewidget){ ?>  
    <div class="close_button">
      <h2><?php echo get_option("txt_checkout") ? esc_attr(get_option("txt_checkout", 'Checkout')) : "Checkout"; ?></h2>
      <svg onclick="closeCheckoutPopup()" xmlns="https://www.w3.org/2000/svg" viewBox="0 0 320 512" role="graphics-symbol" aria-hidden="false" aria-label=""><path d="M310.6 361.4c12.5 12.5 12.5 32.75 0 45.25C304.4 412.9 296.2 416 288 416s-16.38-3.125-22.62-9.375L160 301.3L54.63 406.6C48.38 412.9 40.19 416 32 416S15.63 412.9 9.375 406.6c-12.5-12.5-12.5-32.75 0-45.25l105.4-105.4L9.375 150.6c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0L160 210.8l105.4-105.4c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25l-105.4 105.4L310.6 361.4z"></path></svg>
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
