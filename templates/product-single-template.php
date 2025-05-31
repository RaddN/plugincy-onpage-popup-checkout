<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
// product single template
// shortcode to display one page checkout [plugincy_one_page_checkout product_ids="" template="product-single"]
?>
<div class = "product-single-template">
<div class="one-page-checkout-container">
    <div class="one-page-checkout-products">
        <?php
        //remove any whitespace from product IDs
        $product_ids = array_map('trim', $product_ids);

        // Loop through each product ID
        foreach ($product_ids as $item_id) {
            $product_id = intval($item_id);
            $product = wc_get_product($product_id);

            if ($product) {
        ?>
                <div class="one-page-checkout-product-single">
                    <div class="one-page-checkout-product-image-container">
                        <?php echo wp_kses_post($product->get_image('woocommerce_single')); ?>
                    </div>

                    <div class="one-page-checkout-product-details">
                        <h1 class="one-page-checkout-product-title"><?php echo esc_html($product->get_name()); ?></h1>

                        <div class="one-page-checkout-product-price">
                            <?php echo wp_kses_post($product->get_price_html()); ?>
                        </div>

                        <div class="one-page-checkout-product-description">
                            <?php echo wp_kses_post($product->get_short_description()); ?>
                        </div>

                        <div class="one-page-checkout-product-form">
                            <?php
                            // Using the add_to_cart shortcode instead of custom button
                            echo do_shortcode('[add_to_cart id="' . $product_id . '" style="" show_price="false" quantity="1" class="add-to-order-button"]');
                            ?>

                            <a href="#" class="modify-complete-order"><?php echo esc_html__('Modify & complete order below', 'one-page-quick-checkout-for-woocommerce'); ?></a>
                        </div>

                        <div class="one-page-checkout-product-meta">
                            <span class="sku-wrapper"><?php echo esc_html__('SKU', 'one-page-quick-checkout-for-woocommerce'); ?>: <?php echo esc_html($product->get_sku()); ?></span>
                            <span class="category-wrapper"><?php echo esc_html__('Category', 'one-page-quick-checkout-for-woocommerce'); ?>:
                                <?php echo wp_kses_post(wc_get_product_category_list($product->get_id(), ', ')); ?>
                            </span>
                        </div>
                    </div>
                </div>
        <?php
                // Add a separator between products if this isn't the last product
                if (end($product_ids) !== $item_id) {
                    echo '<hr class="product-separator">';
                }
            }
        }
        ?>
        <?php onepaquc_rmenu_checkout_popup(true); ?>
    </div>
</div>
</div>


<?php $inline_script = "
    jQuery(document).ready(function($) {
        $(document).on('click', '.modify-complete-order', function(e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $(document).height()
            }, 800);
        });

        // Update checkout when product is added to cart
        $(document.body).on('added_to_cart', function() {
            $(document.body).trigger('update_checkout');
            $('html, body').animate({
                scrollTop: $(document).height()
            }, 800);
        });
    });";
    // Enqueue the inline script
    wp_add_inline_script('rmenu-cart-script', $inline_script,99);