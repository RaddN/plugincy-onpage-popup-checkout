<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
// product single template
// shortcode to display one page checkout [plugincy_one_page_checkout product_ids="" template="product-single"]
?>

<div class="one-page-checkout-container">
    <div class="one-page-checkout-products">
        <?php
        $product_ids = explode(',', $atts['product_ids']);
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

                            <a href="#" class="modify-complete-order"><?php echo esc_html__('Modify & complete order below', 'rmenu'); ?></a>
                        </div>

                        <div class="one-page-checkout-product-meta">
                            <span class="sku-wrapper"><?php echo esc_html__('SKU', 'woocommerce'); ?>: <?php echo esc_html($product->get_sku()); ?></span>
                            <span class="category-wrapper"><?php echo esc_html__('Category', 'woocommerce'); ?>:
                                <?php echo wc_get_product_category_list($product->get_id(), ', '); ?>
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
        <?php rmenu_checkout_popup(true); ?>
    </div>
</div>

<style>
    .one-page-checkout-product-single {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        margin-bottom: 30px;
    }

    .one-page-checkout-product-single a.button.product_type_simple.add_to_cart_button.ajax_add_to_cart {
        margin: 0;
    }

    .product-separator {
        margin: 30px 0;
        border: 0;
        border-top: 1px solid #eee;
    }

    .one-page-checkout-product-image-container {
        flex: 0 0 45%;
        max-width: 45%;
    }

    .one-page-checkout-product-details {
        flex: 0 0 50%;
        max-width: 50%;
    }

    .one-page-checkout-product-title {
        font-size: 28px;
        margin-bottom: 10px;
        font-weight: 500;
        color: #333;
    }

    .one-page-checkout-product-price {
        font-size: 20px;
        margin-bottom: 20px;
        color: #333;
    }

    .one-page-checkout-product-description {
        margin-bottom: 20px;
        color: #666;
    }

    .one-page-checkout-product-form {
        margin-bottom: 20px;
    }


    /* Style the quantity input */
    .quantity input.qty {
        width: 70px !important;
        text-align: center !important;
        padding: 8px !important;
        border: 1px solid #ddd !important;
        margin-right: 10px !important;
    }

    .modify-complete-order {
        display: block;
        margin-top: 15px;
        color: #666;
        text-decoration: none;
        font-size: 14px;
    }

    .modify-complete-order:before {
        content: "\2193";
        /* Down arrow */
        margin-right: 5px;
    }

    .one-page-checkout-product-meta {
        margin-top: 20px;
        font-size: 14px;
        color: #666;
    }

    .one-page-checkout-product-meta span {
        display: block;
        margin-bottom: 5px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .one-page-checkout-product-single {
            flex-direction: column;
        }

        .one-page-checkout-product-image-container,
        .one-page-checkout-product-details {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }
</style>

<script>
    jQuery(document).ready(function($) {
        // Handle the "Modify & complete order below" link
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
    });
</script>