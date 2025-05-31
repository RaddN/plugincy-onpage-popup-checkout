<?php
if (!defined('ABSPATH')) exit;
// Shortcode: [plugincy_one_page_checkout product_ids="" template="product-accordion"]
?>
<div class="product-accordion-template">
    <div class="one-page-checkout-container">
        <h2><?php echo esc_html__('Products', 'one-page-quick-checkout-for-woocommerce'); ?></h2>

        <div class="one-page-checkout-accordion">
            <?php
            $product_ids = array_map('trim', $product_ids);

            foreach ($product_ids as $item_id) {
                $product_id = intval($item_id);
                $product = wc_get_product($product_id);
                if (!$product) continue;

                $sku = $product->get_sku();
                $categories = wc_get_product_category_list($product_id, ', ');
                $tags = wc_get_product_tag_list($product_id, ', ');
                $attributes = $product->get_attributes();
            ?>

                <div class="opc-accordion-item">
                    <div class="opc-accordion-header">
                        <div class="opc-product-image">
                            <?php echo wp_kses_post($product->get_image('woocommerce_thumbnail')); ?>
                        </div>
                        <div class="opc-product-details">
                            <div class="opc-product-title"><?php echo esc_html($product->get_name()); ?></div>
                            <div class="opc-product-price"><?php echo wp_kses_post($product->get_price_html()); ?></div>
                            <div class="opc-product-add-to-cart">
                                <?php echo do_shortcode('[add_to_cart id="' . $product_id . '" show_price="false" style=""]'); ?>
                            </div>
                        </div>
                        <span class="opc-toggle-icon">+</span>
                    </div>
                    <div class="opc-accordion-body">
                        <div class="opc-product-description">
                            <?php echo wp_kses_post(wpautop($product->get_short_description())); ?>
                        </div>

                        <div class="opc-product-meta">
                            <?php if ($sku) : ?>
                                <p><strong>SKU:</strong> <?php echo esc_html($sku); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($categories)) : ?>
                                <p><strong>Categories:</strong> <?php echo wp_kses_post($categories); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($tags)) : ?>
                                <p><strong>Tags:</strong> <?php echo wp_kses_post($tags); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($attributes)) : ?>
                                <div class="opc-product-attributes">
                                    <ul>
                                        <?php foreach ($attributes as $attribute) {

                                            $terms = wc_get_product_terms($product_id, $attribute->get_name(), ['fields' => 'names']);
                                            if (!empty($terms)) {
                                                echo '<li>' . esc_attr(wc_attribute_label($attribute->get_name())) . ': ' . esc_attr(implode(', ', $terms)) . '</li>';
                                            }
                                        } ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php
            }
            ?>
        </div>

        <?php onepaquc_rmenu_checkout_popup(true); ?>
    </div>
</div>

<!-- Accordion Script -->
<?php $inline_script = "
    jQuery(document).ready(function($) {
        $('.opc-accordion-header').on('click', function(e) {
            if ($(e.target).closest('.opc-product-add-to-cart').length) return;

            var parent = $(this).closest('.opc-accordion-item');
            var body = parent.find('.opc-accordion-body');
            var icon = $(this).find('.opc-toggle-icon');

            if (parent.hasClass('active')) {
                parent.removeClass('active');
                body.slideUp();
                icon.text('+');
            } else {
                $('.opc-accordion-item').removeClass('active');
                $('.opc-accordion-body').slideUp();
                $('.opc-toggle-icon').text('+');

                parent.addClass('active');
                body.slideDown();
                icon.text('−');
            }
        });
        $(document.body).on('added_to_cart', function() {
            $(document.body).trigger('update_checkout');
            $('html, body').animate({
                scrollTop: $(document).height()
            }, 800);
        });
    });";
    wp_add_inline_script('rmenu-cart-script', $inline_script,99);