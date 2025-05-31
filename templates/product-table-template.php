<?php
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div class="one-page-checkout-container">
                <!-- products in table format image -- title -- price -- add to cart -->
                <div class="one-page-checkout-products">
                    <h2><?php echo esc_html__('Products', 'one-page-quick-checkout-for-woocommerce'); ?></h2>
                    <table class="one-page-checkout-product-table" style="border: none;">                        
                        <tbody>
                            <?php
                            //remove any whitespace from product IDs
                            $product_ids = array_map('trim', $product_ids);
                            // Loop through each product ID and add it to the cart
                            foreach ($product_ids as $item_id){
                                echo '<tr>';
                                $product_id = intval($item_id);
                                echo '<td>';
                                echo '<div class="one-page-checkout-product-image">';
                                $product = wc_get_product($product_id);
                                if ($product) {
                                    $product_image = $product->get_image(array(100, 100), array('class' => 'one-page-checkout-product-image'));
                                    echo wp_kses_post($product_image);
                                }
                                echo '</div>';
                                echo '</td>';
                                echo '<td>';
                                echo '<div class="one-page-checkout-product-title">';
                                if ($product) {
                                    $product_name = $product->get_name();
                                    echo esc_html($product_name);
                                }
                                echo '</div>';
                                echo '</td>';                               
                                echo '<td>';
                                echo '<div class="one-page-checkout-product-add-to-cart">';
                                if ($product) {
                                    $add_to_cart = do_shortcode('[add_to_cart id="' . $product_id . '" style=""]');
                                    echo $add_to_cart;
                                }
                                echo '</div>';
                                echo '</td>';
                                echo '</tr>';                                
                            } ?>
                        </tbody>
                    </table>
                <?php onepaquc_rmenu_checkout_popup(true) ?>
            </div>