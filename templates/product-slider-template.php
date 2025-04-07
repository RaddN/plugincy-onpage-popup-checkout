<?php
if (!defined('ABSPATH')) exit;
// Shortcode: [plugincy_one_page_checkout product_ids="" template="product-slider"]
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">

<div class="one-page-checkout-container">
    <h2><?php echo esc_html__('Products', 'rmenu'); ?></h2>

    <div class="one-page-checkout-slider owl-carousel">
        <?php
        $product_ids = explode(',', $atts['product_ids']);
        $product_ids = array_map('trim', $product_ids);

        foreach ($product_ids as $item_id) {
            $product_id = intval($item_id);
            $product = wc_get_product($product_id);
            if (!$product) continue;

            echo '<div class="one-page-checkout-product">';
            echo '<div class="one-page-checkout-product-image">' . wp_kses_post($product->get_image('woocommerce_thumbnail')) . '</div>';
            echo '<div class="one-page-checkout-product-title">' . esc_html($product->get_name()) . '</div>';
            echo '<div class="one-page-checkout-product-price">' . wp_kses_post($product->get_price_html()) . '</div>';
            echo '<div class="one-page-checkout-product-add-to-cart">';
            echo do_shortcode('[add_to_cart id="' . $product_id . '" show_price="false" style=""]');
            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>

    <?php rmenu_checkout_popup(true); ?>

</div>

<!-- Initialize Owl Carousel -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script>
    (function($) {
        $(document).ready(function() {
            var owl = $('.one-page-checkout-slider');
            owl.owlCarousel({
                loop: true,
                margin: 25,
                autoplay: true,
                autoplayTimeout: 4000,
                autoplayHoverPause: true,
                nav: true,
                dots: true,
                responsive: {
                    0: {
                        items: 1
                    },
                    768: {
                        items: 2
                    },
                    1024: {
                        items: 3
                    }
                }
            });
            owl.on('mousewheel', '.owl-stage', function(e) {
                if (e.deltaY > 0) {
                    owl.trigger('next.owl');
                } else {
                    owl.trigger('prev.owl');
                }
                e.preventDefault();
            });
        });
    })(jQuery);
</script>

<!-- Optional Styling -->
<style>
    .one-page-checkout-container {
        padding: 20px;
    }

    .one-page-checkout-product {
        text-align: center;
        background: #fff;
        border: 1px solid #eee;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .one-page-checkout-product-image img {
        max-width: 100%;
        height: auto;
        border-radius: 10px;
    }

    .one-page-checkout-product-title {
        font-weight: 600;
        margin: 10px 0 5px;
    }

    .one-page-checkout-product-price {
        margin-bottom: 10px;
        color: #444;
    }

    .owl-nav button {
        position: absolute;
        top: 40%;
        background: #ccc !important;
        border-radius: 50%;
        padding: 5px 10px !important;
    }

    .owl-nav .owl-prev {
        left: -25px;
    }

    .owl-nav .owl-next {
        right: -25px;
    }
</style>