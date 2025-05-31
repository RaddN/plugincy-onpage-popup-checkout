<?php
if (!defined('ABSPATH')) exit;
// Shortcode: [plugincy_one_page_checkout product_ids="" template="product-tabs"]
?>
<div class="product-tabs-template">
<div class="one-page-checkout-container">
    <h2><?php echo esc_html__('Products', 'one-page-quick-checkout-for-woocommerce'); ?></h2>

    <div class="one-page-checkout-tabs">
        <ul class="opc-tabs-list">
            <?php
            $product_ids = array_map('trim', $product_ids);
            $tab_id = 0; // For unique tab IDs
            foreach ($product_ids as $item_id) {
                $product_id = intval($item_id);
                $product = wc_get_product($product_id);
                if (!$product) continue;

                $tab_id++;
                $sku = $product->get_sku();
                $categories = wc_get_product_category_list($product_id, ', ');
                $tags = wc_get_product_tag_list($product_id, ', ');
                $attributes = $product->get_attributes();
            ?>
                <li class="opc-tab-link" data-tab="tab-<?php echo esc_attr($tab_id); ?>"><?php echo esc_html($product->get_name()); ?></li>
            <?php } ?>
        </ul>

        <div class="opc-tabs-content">
            <?php
            $tab_id = 0; // Reset tab counter
            foreach ($product_ids as $item_id) {
                $product_id = intval($item_id);
                $product = wc_get_product($product_id);
                if (!$product) continue;

                $tab_id++;
                $sku = $product->get_sku();
                $categories = wc_get_product_category_list($product_id, ', ');
                $tags = wc_get_product_tag_list($product_id, ', ');
                $attributes = $product->get_attributes();
            ?>
                <div class="opc-tab-pane" id="tab-<?php echo esc_attr($tab_id); ?>">
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
                                            echo '<li>' . esc_html(wc_attribute_label($attribute->get_name())) . ': ' . esc_html(implode(', ', $terms)) . '</li>';
                                        }
                                    } ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php onepaquc_rmenu_checkout_popup(true); ?>
</div>
</div>

<!-- Tabs Script -->
<?php $inline_script = "
    jQuery(document).ready(function($) {
        $('.opc-tab-link').on('click', function() {
            var tabId = $(this).data('tab');

            // Remove active class from all tabs and hide all tab content
            $('.opc-tab-link').removeClass('active');
            $('.opc-tab-pane').removeClass('active');

            // Add active class to clicked tab and show the associated tab content
            $(this).addClass('active');
            $('#' + tabId).addClass('active');
        });

        // Set the first tab to active by default
        $('.opc-tab-link').first().addClass('active');
        $('.opc-tab-pane').first().addClass('active');
    });";
wp_add_inline_script('rmenu-cart-script', $inline_script, 'after');
