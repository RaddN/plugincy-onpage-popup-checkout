<?php
if (!defined('ABSPATH')) exit;
// Shortcode: [plugincy_one_page_checkout product_ids="" template="product-slider"]

wp_enqueue_style( 'owl-carousel', plugin_dir_url(__FILE__) . '../assets/css/owl.carousel.min.css', array(), "1.0.3");
wp_enqueue_style( 'owl-theme', plugin_dir_url(__FILE__) . '../assets/css/owl.theme.default.css', array(), "1.0.3" );
?>

<div class="product-slider-template">
<div class="one-page-checkout-container">
    <h2><?php echo esc_html__('Products', 'one-page-quick-checkout-for-woocommerce'); ?></h2>

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

    <?php onepaquc_rmenu_checkout_popup(true); ?>

</div>
</div>

<!-- Initialize Owl Carousel -->

<?php wp_enqueue_script('owl-carousel', plugin_dir_url(__FILE__) . '../assets/js/owl.carousel.min.js', array('jquery'), "1.0.3", true); ?>
<?php
$inline_script = "
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
    })(jQuery);";
wp_add_inline_script('owl-carousel', $inline_script, 99);