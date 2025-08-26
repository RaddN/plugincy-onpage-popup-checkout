<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Add product image to WooCommerce checkout page cart items
 */
function onepaquc_add_product_image_to_checkout_cart_items($product_name, $cart_item, $cart_item_key)
{
    if (get_option("rmenu_add_img_before_product") !== "1") {
        return $product_name;
    }
    // Get the product
    $product = $cart_item['data'];

    // Get product thumbnail
    $thumbnail = $product->get_image(array(50, 50));

    // Return the image followed by the product name
    return '<div class="checkout-product-item"><div class="checkout-product-image">' . $thumbnail . '</div><div class="checkout-product-name">' . $product_name . '</div></div>';
}
add_filter('woocommerce_cart_item_name', 'onepaquc_add_product_image_to_checkout_cart_items', 10, 3);

// add a random product in cart

if (get_option('rmenu_at_one_product_cart', 0)) {
    add_action('template_redirect', 'onepaquc_add_random_product_if_cart_empty');
}

function onepaquc_add_random_product_if_cart_empty()
{

    // If cart is empty
    if (WC()->cart->is_empty()) {

        // Get one random product ID
        $random_product = wc_get_products(array(
            'status'    => 'publish',
            'limit'     => 1,
            'orderby'   => 'rand',
            'return'    => 'ids',
            'type'      => 'simple', // Change to 'variable' if needed
        ));

        if (!empty($random_product)) {
            WC()->cart->add_to_cart($random_product[0], 1);
        }

        // Set a flag in local storage via JavaScript to indicate a random product was added
        add_action('wp_footer', function () {
?>
            <script>
                try {
                    localStorage.setItem('random_product_added', '1');
                } catch (e) {}
            </script>
        <?php
        });
    } else {
        add_action('wp_footer', function () {
        ?>
            <script>
                try {
                    localStorage.removeItem('random_product_added');
                } catch (e) {}
            </script>
            <?php
        });
    }
}

if (get_option('rmenu_disable_cart_page', 0)) {
    add_action('template_redirect', 'disable_cart_page_redirect');
    function disable_cart_page_redirect()
    {
        if (is_cart()) {
            wp_redirect(wc_get_checkout_url());
            exit;
        }
    }
}

if (get_option('rmenu_link_product', 0)) {
    add_filter('woocommerce_cart_item_name', 'onepaquc_link_product_name_on_checkout', 10, 3);
    function onepaquc_link_product_name_on_checkout($product_name, $cart_item, $cart_item_key)
    {
        // Only apply on the checkout page
        if (is_checkout()) {
            $product = $cart_item['data'];
            $product_link = get_permalink($product->get_id());
            $product_name = sprintf('<a href="%s">%s</a>', esc_url($product_link), $product_name);
        }
        return $product_name;
    }
}




/**
 * Add variation selection buttons to product archive pages
 */
/**
 * Add variation selection buttons to product archive pages using woocommerce_loop_add_to_cart_link
 */
if (get_option('rmenu_variation_show_archive', 1) && (get_option("rmenu_wc_direct_checkout_position", "after_add_to_cart") === "after_add_to_cart" || get_option("rmenu_wc_direct_checkout_position", "after_add_to_cart") === "bottom_add_to_cart" || get_option("rmenu_wc_direct_checkout_position", "after_add_to_cart") === "before_add_to_cart" || get_option("rmenu_wc_direct_checkout_position", "after_add_to_cart") === "replace_add_to_cart")) {
    add_filter('woocommerce_loop_add_to_cart_link', 'onepaquc_add_variation_buttons_to_loop', 100, 2);
} else {
    new onepaquc_add_variation_buttons_on_archive();
}

class onepaquc_add_variation_buttons_on_archive
{
    private $is_btn_add_hook_works;
    /**
     * Constructor
     */
    public function __construct()
    {
        if (get_option('rmenu_variation_show_archive', 1)) {
            $this->add_variation_buttons_on_archive();
            $this->is_btn_add_hook_works = false;
        }
    }

    public function add_variation_buttons_on_archive()
    {
        $position = get_option("rmenu_wc_direct_checkout_position", "overlay_thumbnail_hover");

        switch ($position) {
            case 'overlay_thumbnail':
            case 'overlay_thumbnail_hover':
            case 'after_product':
                add_action('woocommerce_after_shop_loop_item', array($this, 'onepaquc_variation_buttons'), 5);
                break;
            case 'after_product_title':
                add_action('woocommerce_shop_loop_item_title', array($this, 'onepaquc_variation_buttons'), 14);
                break;
            case 'before_product_title':
                add_action('woocommerce_shop_loop_item_title', array($this, 'onepaquc_variation_buttons'), 8);
                break;
            case 'after_product_excerpt':
                add_action('woocommerce_after_shop_loop_item_title', array($this, 'onepaquc_variation_buttons'), 8);
                break;
            case 'after_product_rating':
                add_action('woocommerce_after_shop_loop_item_title', array($this, 'onepaquc_variation_buttons'), 6);
                break;
            case 'after_product_price':
                add_action('woocommerce_after_shop_loop_item_title', array($this, 'onepaquc_variation_buttons'), 14);
                break;
        }
    }

    public function onepaquc_variation_buttons()
    {
        global $product;

        // Only proceed if this is a variable product
        if ($product->is_type('variable')) {
            // Get available variations
            $available_variations = $product->get_available_variations();

            if (!empty($available_variations)) {
                $position = get_option("rmenu_wc_direct_checkout_position", "overlay_thumbnail_hover");

                // Add specific class based on position for styling
                $container_class = 'archive-variations-container';
                if (in_array($position, ['overlay_thumbnail', 'overlay_thumbnail_hover', 'after_product'])) {
                    $container_class .= ' overlay-variations';
                }
                if (in_array($position, ['after_product'])) {
                    $container_class .= ' bottom-48';
                }

                echo '<div class="' . esc_attr($container_class) . '">';

                // Loop through all variations and create a button for each
                foreach ($available_variations as $variation) {
                    $variation_id = $variation['variation_id'];

                    // Create a readable title from the attributes
                    $variation_title = array();
                    foreach ($variation['attributes'] as $attribute_name => $attribute_value) {
                        $taxonomy = str_replace('attribute_', '', $attribute_name);
                        $term_name = $attribute_value;

                        // If it's a taxonomy attribute, get the term name
                        if (taxonomy_exists($taxonomy)) {
                            $term = get_term_by('slug', $attribute_value, $taxonomy);
                            if ($term && !is_wp_error($term)) {
                                $term_name = $term->name;
                            }
                        }

                        if (!empty($term_name)) {
                            $variation_title[] = $term_name;
                        }
                    }

                    $button_text = implode(' / ', $variation_title);

                    // Create the button with data-id attribute
                    echo '<button type="button" class="variation-button" data-id="' . esc_attr($variation_id) . '">' . esc_html($button_text) . '</button>';
                }

                // Hidden input to store the selected variation ID
                echo '<input type="hidden" class="variation_id" value="">';

                echo '</div>'; // .archive-variations-container

                // Simple JS to update the variation_id when a button is clicked
            ?>
                <script type="text/javascript">
                    jQuery(document).ready(function($) {
                        // Move overlay variations to be direct child of .product
                        $('.overlay-variations').each(function() {
                            var $variations = $(this);
                            var $product = $variations.closest('.product');
                            if ($product.length) {
                                $variations.detach().appendTo($product);
                            }
                        });

                        $('.variation-button').click(function() {
                            var variation_id = $(this).data('id');
                            $(this).closest('.archive-variations-container').find('.variation_id').val(variation_id);
                            $(this).addClass('selected').siblings().removeClass('selected');
                        });
                    });
                </script>
                <style>
                    .archive-variations-container {
                        margin-top: 10px;
                        margin-bottom: 10px;
                        display: flex;
                        flex-wrap: wrap;
                        gap: 5px;
                    }

                    /* Special styling for overlay positions */
                    .overlay-variations {
                        position: absolute;
                        bottom: 10px;
                        left: 10px;
                        right: 10px;
                        background: rgba(255, 255, 255, 0.9);
                        padding: 10px;
                        border-radius: 5px;
                        z-index: 10;
                    }

                    .bottom-48 {
                        bottom: 48px;
                    }

                    /* Hide overlay variations by default, show on hover for hover position */
                    .overlay-variations {
                        opacity: 0;
                        transition: opacity 0.3s ease;
                    }

                    /* Show overlay variations on product hover for hover position */
                    .product:hover .overlay-variations {
                        opacity: 1;
                    }

                    /* Always show for non-hover overlay position */
                    .archive-variations-container:not(.overlay-variations) {
                        opacity: 1;
                    }

                    .variation-button {
                        background-color: #f7f7f7;
                        border: 1px solid #ddd;
                        padding: 5px 10px;
                        border-radius: 3px;
                        cursor: pointer;
                        transition: all 0.2s;
                        color: #000;
                        font-size: 12px;
                    }

                    .variation-button:hover {
                        background-color: #eaeaea;
                        color: #000;
                    }

                    .variation-button.selected {
                        background-color: #4CAF50;
                        color: white;
                        border-color: #4CAF50;
                    }

                    /* Ensure product container has relative positioning for overlay */
                    .product {
                        position: relative;
                    }
                </style>
        <?php
            }
        }
    }
}

function onepaquc_add_variation_buttons_to_loop($link, $product)
{
    // Only proceed if this is a variable product
    if ($product->is_type('variable')) {
        // Get available variations
        $available_variations = $product->get_available_variations();

        ob_start(); // Start output buffering

        echo '<div class="archive-variations-container">';

        // Loop through all variations and create a button for each
        foreach ($available_variations as $variation) {
            $variation_id = $variation['variation_id'];

            // Create a readable title from the attributes
            $variation_title = array();
            foreach ($variation['attributes'] as $attribute_name => $attribute_value) {
                $taxonomy = str_replace('attribute_', '', $attribute_name);
                $term_name = $attribute_value;

                // If it's a taxonomy attribute, get the term name
                if (taxonomy_exists($taxonomy)) {
                    $term = get_term_by('slug', $attribute_value, $taxonomy);
                    if ($term && !is_wp_error($term)) {
                        $term_name = $term->name;
                    }
                }

                if (!empty($term_name)) {
                    $variation_title[] = $term_name;
                }
            }

            $button_text = implode(' / ', $variation_title);

            // Create the button with data-id attribute
            echo '<button type="button" class="variation-button" data-id="' . esc_attr($variation_id) . '">' . esc_html($button_text) . '</button>';
        }

        // Hidden input to store the selected variation ID
        echo '<input type="hidden" class="variation_id" value="">';

        echo '</div>'; // .archive-variations-container

        // Simple JS to update the variation_id when a button is clicked
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.variation-button').click(function() {
                    var variation_id = $(this).data('id');
                    $(this).closest('.archive-variations-container').find('.variation_id').val(variation_id);
                    $(this).addClass('selected').siblings().removeClass('selected');
                });
            });
        </script>
        <style>
            .archive-variations-container {
                margin-top: 10px;
                margin-bottom: 10px;
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
            }

            .variation-button {
                background-color: #f7f7f7;
                border: 1px solid #ddd;
                padding: 5px 10px;
                border-radius: 3px;
                cursor: pointer;
                transition: all 0.2s;
                color: #000;
            }

            .variation-button:hover {
                background-color: #eaeaea;
                color: #000;
            }

            .variation-button.selected {
                background-color: #4CAF50;
                color: white;
                border-color: #4CAF50;
            }
        </style>
<?php

        // Append the variations container before the add to cart link
        return ob_get_clean() . $link; // Return the variations and the link
    }

    return $link; // Return the original link for non-variable products
}
