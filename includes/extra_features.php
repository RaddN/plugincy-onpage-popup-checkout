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
    return '<div class="checkout-product-item"><div class="checkout-product-image">' . $thumbnail . '</div><div class="checkout-product-name">' . esc_html($product_name) . '</div></div>';
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

        if (!$product || !$product->is_type('variable')) {
            return;
        }

        $available_variations = $product->get_available_variations();
        if (empty($available_variations)) {
            return;
        }

        $position     = get_option('rmenu_wc_direct_checkout_position', 'overlay_thumbnail_hover');
        $layout       = get_option('rmenu_variation_layout', 'separate'); // 'combine' | 'separate'
        $show_titles  = (bool) get_option('rmenu_show_variation_title', 0);

        $container_class = 'archive-variations-container';
        if (in_array($position, ['overlay_thumbnail', 'overlay_thumbnail_hover', 'after_product'], true)) {
            $container_class .= ' overlay-variations';
        }
        if (in_array($position, ['after_product'], true)) {
            $container_class .= ' bottom-48';
        }

        echo '<div class="' . esc_attr($container_class) . '" data-layout="' . esc_attr($layout) . '">';

        if ($layout === 'combine') {
            // ---------- COMBINE (unchanged visual behavior) ----------
            foreach ($available_variations as $variation) {
                $variation_id = $variation['variation_id'];
                $parts = [];

                foreach ($variation['attributes'] as $attribute_name => $attribute_value) {
                    $taxonomy   = str_replace('attribute_', '', $attribute_name);
                    $value_label = $attribute_value;

                    if (taxonomy_exists($taxonomy)) {
                        $term = get_term_by('slug', $attribute_value, $taxonomy);
                        if ($term && !is_wp_error($term)) {
                            $value_label = $term->name;
                        }
                    }
                    if ($value_label !== '') {
                        $parts[] = $value_label;
                    }
                }

                echo '<button type="button" class="variation-button" data-id="' . esc_attr($variation_id) . '">' . esc_html(implode(' / ', $parts)) . '</button>';
            }

            // Hidden + visible
            echo '<input type="hidden" class="variation_id" value="">';
        } else {
            // ---------- SEPARATE (group by attribute) ----------
            $attributes_terms   = []; // attr_key => ['label'=>..., 'terms'=> [ slug => label ]]
            $attr_keys_indexed  = []; // seen keys
            $variations_for_js  = []; // [{id, attrs:{attr=>slug}}]

            foreach ($available_variations as $variation) {
                $vid       = $variation['variation_id'];
                $var_attrs = [];

                foreach ($variation['attributes'] as $attribute_name => $attribute_value) {
                    $attr_key   = str_replace('attribute_', '', $attribute_name); // e.g., pa_color or custom
                    $slug       = $attribute_value;
                    if ($slug === '') {
                        continue;
                    }

                    $attr_label = wc_attribute_label($attr_key, $product);
                    $value_label = $slug;

                    if (taxonomy_exists($attr_key)) {
                        $term = get_term_by('slug', $slug, $attr_key);
                        if ($term && !is_wp_error($term)) {
                            $value_label = $term->name;
                        }
                    }

                    if (!isset($attributes_terms[$attr_key])) {
                        $attributes_terms[$attr_key] = [
                            'label' => $attr_label ?: ucfirst(str_replace('pa_', '', $attr_key)),
                            'terms' => []
                        ];
                    }
                    $attributes_terms[$attr_key]['terms'][$slug] = $value_label;

                    $var_attrs[$attr_key] = $slug;
                    $attr_keys_indexed[$attr_key] = true;
                }

                $variations_for_js[] = ['id' => (string) $vid, 'attrs' => $var_attrs];
            }

            $attr_keys = array_keys($attr_keys_indexed);

            // Container that carries JSON safely (no HTML-entity headaches)
            echo '<div class="separate-attrs">';
            echo '<script type="application/json" class="var-map">' . wp_json_encode($variations_for_js) . '</script>';
            echo '<script type="application/json" class="attr-keys">' . wp_json_encode($attr_keys) . '</script>';

            foreach ($attributes_terms as $attr_key => $info) {
                echo '<div class="var-attr-group" data-attr="' . esc_attr($attr_key) . '">';

                if ($show_titles) {
                    echo '<span class="var-attr-title">' . esc_html($info['label']) . ':</span> ';
                }

                echo '<div class="var-attr-options">';
                foreach ($info['terms'] as $slug => $label) {
                    echo '<button type="button" class="var-attr-option" data-attr="' . esc_attr($attr_key) . '" data-value="' . esc_attr($slug) . '">' . esc_html($label) . '</button>';
                }
                echo '</div></div>';
            }

            echo '</div>'; // .separate-attrs

            // Hidden + visible
            echo '<input type="hidden" class="variation_id" value="">';
        }

        echo '</div>'; // container
    }
}

function onepaquc_add_variation_buttons_to_loop($link, $product)
{
    if (!$product || !$product->is_type('variable')) {
        return $link;
    }

    $available_variations = $product->get_available_variations();
    if (empty($available_variations)) {
        return $link;
    }

    $layout      = get_option('rmenu_variation_layout', 'separate'); // 'combine' | 'separate'
    $show_titles = (bool) get_option('rmenu_show_variation_title', 0);

    ob_start();

    echo '<div class="archive-variations-container" data-layout="' . esc_attr($layout) . '">';

    if ($layout === 'combine') {
        foreach ($available_variations as $variation) {
            $vid   = $variation['variation_id'];
            $parts = [];
            foreach ($variation['attributes'] as $attribute_name => $attribute_value) {
                $attr_key    = str_replace('attribute_', '', $attribute_name);
                $value_label = $attribute_value;
                if (taxonomy_exists($attr_key)) {
                    $term = get_term_by('slug', $attribute_value, $attr_key);
                    if ($term && !is_wp_error($term)) {
                        $value_label = $term->name;
                    }
                }
                if ($value_label !== '') {
                    $parts[] = $value_label;
                }
            }
            echo '<button type="button" class="variation-button" data-id="' . esc_attr($vid) . '">' . esc_html(implode(' / ', $parts)) . '</button>';
        }

        echo '<input type="hidden" class="variation_id" value="">';
    } else {
        $attributes_terms   = [];
        $attr_keys_indexed  = [];
        $variations_for_js  = [];

        foreach ($available_variations as $variation) {
            $vid       = $variation['variation_id'];
            $var_attrs = [];

            foreach ($variation['attributes'] as $attribute_name => $attribute_value) {
                $attr_key   = str_replace('attribute_', '', $attribute_name);
                $slug       = $attribute_value;
                if ($slug === '') {
                    continue;
                }

                $attr_label = wc_attribute_label($attr_key, $product);
                $value_label = $slug;

                if (taxonomy_exists($attr_key)) {
                    $term = get_term_by('slug', $slug, $attr_key);
                    if ($term && !is_wp_error($term)) {
                        $value_label = $term->name;
                    }
                }

                if (!isset($attributes_terms[$attr_key])) {
                    $attributes_terms[$attr_key] = [
                        'label' => $attr_label ?: ucfirst(str_replace('pa_', '', $attr_key)),
                        'terms' => []
                    ];
                }
                $attributes_terms[$attr_key]['terms'][$slug] = $value_label;

                $var_attrs[$attr_key] = $slug;
                $attr_keys_indexed[$attr_key] = true;
            }

            $variations_for_js[] = ['id' => (string) $vid, 'attrs' => $var_attrs];
        }

        $attr_keys = array_keys($attr_keys_indexed);

        echo '<div class="separate-attrs">';
        echo '<script type="application/json" class="var-map">' . wp_json_encode($variations_for_js) . '</script>';
        echo '<script type="application/json" class="attr-keys">' . wp_json_encode($attr_keys) . '</script>';

        foreach ($attributes_terms as $attr_key => $info) {
            echo '<div class="var-attr-group" data-attr="' . esc_attr($attr_key) . '">';

            if ($show_titles) {
                echo '<span class="var-attr-title">' . esc_html($info['label']) . ':</span> ';
            }

            echo '<div class="var-attr-options">';
            foreach ($info['terms'] as $slug => $label) {
                echo '<button type="button" class="var-attr-option" data-attr="' . esc_attr($attr_key) . '" data-value="' . esc_attr($slug) . '">' . esc_html($label) . '</button>';
            }
            echo '</div></div>';
        }

        echo '</div>'; // .separate-attrs

        echo '<input type="hidden" class="variation_id" value="">';
    }

    echo '</div>'; // container

    return ob_get_clean() . $link;
}
