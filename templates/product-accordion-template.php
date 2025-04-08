<?php
if (!defined('ABSPATH')) exit;
// Shortcode: [plugincy_one_page_checkout product_ids="" template="product-accordion"]
?>

<div class="one-page-checkout-container">
    <h2><?php echo esc_html__('Products', 'rmenu'); ?></h2>

    <div class="one-page-checkout-accordion">
        <?php
        $product_ids = explode(',', $atts['product_ids']);
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
                        <?php echo wpautop($product->get_short_description()); ?>
                    </div>

                    <div class="opc-product-meta">
                        <?php if ($sku) : ?>
                            <p><strong>SKU:</strong> <?php echo esc_html($sku); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($categories)) : ?>
                            <p><strong>Categories:</strong> <?php echo $categories; ?></p>
                        <?php endif; ?>

                        <?php if (!empty($tags)) : ?>
                            <p><strong>Tags:</strong> <?php echo $tags; ?></p>
                        <?php endif; ?>

                        <?php if (!empty($attributes)) : ?>
                            <div class="opc-product-attributes">
                                <ul>
                                    <?php foreach ($attributes as $attribute) {

                                        $terms = wc_get_product_terms($product_id, $attribute->get_name(), ['fields' => 'names']);
                                        if (!empty($terms)) {
                                            echo '<li>' . wc_attribute_label($attribute->get_name()) . ': ' . implode(', ', $terms) . '</li>';
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

    <?php plugincyopc_rmenu_checkout_popup(true); ?>
</div>

<!-- Accordion Script -->
<script>
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
            $(document.body).trigger('plugincyopc_update_checkout');
            $('html, body').animate({
                scrollTop: $(document).height()
            }, 800);
        });
    });
</script>

<!-- Styles -->
<style>
    .opc-product-add-to-cart a {
        margin: 0 !important;
    }

    .one-page-checkout-container {
        padding: 20px;
    }

    .one-page-checkout-accordion {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .opc-accordion-item {
        border: 1px solid #ddd;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .opc-accordion-header {
        display: flex;
        align-items: center;
        cursor: pointer;
        padding: 15px;
        position: relative;
        gap: 15px;
    }

    .opc-product-image img {
        width: 80px;
        height: auto;
        border-radius: 8px;
    }

    .opc-product-details {
        flex-grow: 1;
        text-align: left;
    }

    .opc-product-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .opc-product-price {
        color: #444;
        margin-bottom: 5px;
    }

    .opc-product-add-to-cart {
        display: inline-block;
    }

    .opc-toggle-icon {
        font-size: 22px;
        font-weight: bold;
        color: #555;
        padding: 0 10px;
        cursor: pointer;
        user-select: none;
    }

    .opc-accordion-body {
        display: none;
        padding: 15px;
        border-top: 1px solid #eee;
        background-color: #f9f9f9;
    }

    .opc-product-meta p,
    .opc-product-attributes ul {
        margin: 5px 0;
        font-size: 14px;
    }

    .opc-product-attributes ul {
        padding-left: 20px;
        list-style-type: disc;
    }

    .opc-accordion-item.active .opc-toggle-icon {
        color: #007cba;
    }

    @media (max-width: 768px) {
        .opc-accordion-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .opc-toggle-icon {
            position: absolute;
            top: 15px;
            right: 15px;
        }
    }
</style>