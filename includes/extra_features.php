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


/**
 * Comprehensive WooCommerce variation validation with deduplication
 * 
 * @param WC_Product $product Product object
 * @return array Validated and deduplicated variations
 */
function onepaquc_get_validated_variations( $product ) {
    // Skip if product is not published or is scheduled
    if ( ! $product || 'publish' !== $product->get_status() ) {
        return [];
    }

    // Get all variations (including potentially invalid ones)
    $all_variations = $product->get_available_variations();
    if ( empty( $all_variations ) ) {
        return [];
    }

    // Normalize attribute values to the slugs WooCommerce expects.
    $normalize_attribute_value = static function ( $value ) {
        if ( is_array( $value ) ) {
            return '';
        }

        if ( is_bool( $value ) ) {
            $value = $value ? 'yes' : 'no';
        }

        if ( is_numeric( $value ) ) {
            $value = (string) $value;
        } else {
            $value = trim( (string) $value );
        }

        if ( '' === $value ) {
            return '';
        }

        return function_exists( 'sanitize_title' ) ? sanitize_title( $value ) : strtolower( $value );
    };

    // Get product attributes used for variations
    $product_attributes   = $product->get_attributes();
    $variation_attributes = [];

    // Prepare attribute validation data
    foreach ( $product_attributes as $attribute_name => $attribute ) {
        if ( $attribute->get_variation() ) {
            if ( $attribute->is_taxonomy() ) {
                // Terms (slugs) for taxonomy attributes
                $terms = wc_get_product_terms(
                    $product->get_id(),
                    $attribute_name,
                    [ 'fields' => 'slugs' ]
                );

                $variation_attributes[ $attribute_name ] = [
                    'type'               => 'taxonomy',
                    'options'            => $terms,
                    'normalized_options' => array_values(
                        array_filter(
                            array_map( $normalize_attribute_value, $terms ),
                            static function ( $value ) {
                                return '' !== $value;
                            }
                        )
                    ),
                    'required'           => true,
                ];
            } else {
                // Options for custom attributes
                $options = array_map( 'trim', $attribute->get_options() );

                $variation_attributes[ $attribute_name ] = [
                    'type'               => 'custom',
                    'options'            => $options,
                    'normalized_options' => array_values(
                        array_filter(
                            array_map( $normalize_attribute_value, $options ),
                            static function ( $value ) {
                                return '' !== $value;
                            }
                        )
                    ),
                    'required'           => true,
                ];
            }
        }
    }

    // No variation attributes? just return default available variations
    if ( empty( $variation_attributes ) ) {
        return $all_variations;
    }

    $combination_map   = [];
    $combination_order = [];

    foreach ( $all_variations as $variation ) {

        $variation_id  = $variation['variation_id'];
        $variation_obj = wc_get_product( $variation_id );

        // 1. Basic checks
        if ( ! $variation_obj ) {
            continue;
        }

        // Status
        if ( 'publish' !== $variation_obj->get_status() ) {
            continue;
        }

        // Catalog visibility
        if ( 'hidden' === $variation_obj->get_catalog_visibility() ) {
            continue;
        }

        // Stock / backorder
        $stock_status       = $variation_obj->get_stock_status();
        $backorders_allowed = $variation_obj->get_backorders();

        if ( 'outofstock' === $stock_status && 'no' === $backorders_allowed ) {
            continue;
        }

        // Price
        $price = $variation_obj->get_price();
        if ( '' === $price || ! is_numeric( $price ) || $price < 0 ) {
            continue;
        }

        // Tax class
        $tax_class = $variation_obj->get_tax_class();
        if ( ! empty( $tax_class ) ) {
            $tax_classes = WC_Tax::get_tax_classes(); // names, not slugs
            if ( ! in_array( $tax_class, $tax_classes, true ) ) {
                continue;
            }
        }

        // Shipping class
        $shipping_class_id = $variation_obj->get_shipping_class_id();
        if ( $shipping_class_id > 0 && ! term_exists( $shipping_class_id, 'product_shipping_class' ) ) {
            continue;
        }

        // Downloadable file check
        if ( $variation_obj->is_downloadable() ) {
            $files = $variation_obj->get_downloads();
            if ( empty( $files ) ) {
                continue;
            }
        }

        // 2. Normalize attribute keys and values
        //    convert "attribute_pa_cpu" => "pa_cpu"
        $raw_attributes = $variation_obj->get_attributes();
        $attributes     = [];

        foreach ( $raw_attributes as $attr_name => $attr_value ) {

            if ( 0 === strpos( $attr_name, 'attribute_' ) ) {
                $attr_name = substr( $attr_name, 10 ); // remove "attribute_"
            }

            $attributes[ $attr_name ] = $normalize_attribute_value( $attr_value );
        }

        // 3. Validate attributes against product definitions
        $is_valid = true;

        foreach ( $variation_attributes as $attr_name => $attr_data ) {
            $attr_value = isset( $attributes[ $attr_name ] ) ? $attributes[ $attr_name ] : '';

            // "any" (empty) is allowed, as long as the product has options for that attribute
            if ( '' === $attr_value ) {
                if ( empty( $attr_data['normalized_options'] ) ) {
                    $is_valid = false;
                    break;
                }
                continue;
            }

            if ( ! in_array( $attr_value, $attr_data['normalized_options'], true ) ) {
                $is_valid = false;
                break;
            }
        }

        // Missing required attributes => invalid
        if ( $is_valid ) {
            foreach ( $variation_attributes as $attr_name => $attr_data ) {
                if ( ! array_key_exists( $attr_name, $attributes ) ) {
                    $is_valid = false;
                    break;
                }
            }
        }

        if ( ! $is_valid ) {
            continue;
        }

        // 4. Expand wildcard attributes to actual combinations so we can dedupe properly.
        $values_per_attribute = [];
        $specificity          = 0;

        foreach ( $variation_attributes as $attr_name => $attr_data ) {
            $attr_value = isset( $attributes[ $attr_name ] ) ? $attributes[ $attr_name ] : '';

            if ( '' === $attr_value ) {
                $options = $attr_data['normalized_options'];
                if ( empty( $options ) ) {
                    $options = [ '' ];
                }
                $values_per_attribute[ $attr_name ] = $options;
            } else {
                $specificity++;
                $values_per_attribute[ $attr_name ] = [ $attr_value ];
            }
        }

        if ( empty( $values_per_attribute ) ) {
            continue;
        }

        $combinations = [ [] ];

        foreach ( $values_per_attribute as $attr_name => $possible_values ) {
            $new_combinations = [];

            foreach ( $combinations as $combination ) {
                foreach ( $possible_values as $possible_value ) {
                    $new_combination               = $combination;
                    $new_combination[ $attr_name ] = $possible_value;
                    $new_combinations[]            = $new_combination;
                }
            }

            $combinations = $new_combinations;
        }

        foreach ( $combinations as $combination_values ) {
            $key_parts = [];

            foreach ( $combination_values as $attr_name => $attr_value ) {
                $key_parts[ $attr_name ] = $attr_name . '=' . $attr_value;
            }

            ksort( $key_parts );
            $combination_key = implode( '&', $key_parts );

            $variation_for_combo = $variation;
            $variation_for_combo['attributes'] = isset( $variation_for_combo['attributes'] ) && is_array( $variation_for_combo['attributes'] )
                ? $variation_for_combo['attributes']
                : [];

            foreach ( $combination_values as $attr_name => $attr_value ) {
                $attr_key = 0 === strpos( $attr_name, 'attribute_' ) ? $attr_name : 'attribute_' . $attr_name;
                $variation_for_combo['attributes'][ $attr_key ] = $attr_value;
            }

            if ( ! isset( $combination_map[ $combination_key ] ) ) {
                $combination_order[]                = $combination_key;
                $combination_map[ $combination_key ] = [
                    'specificity' => $specificity,
                    'variation'   => $variation_for_combo,
                ];
                continue;
            }

            if ( $specificity >= $combination_map[ $combination_key ]['specificity'] ) {
                $combination_map[ $combination_key ] = [
                    'specificity' => $specificity,
                    'variation'   => $variation_for_combo,
                ];
            }
        }
    }

    if ( empty( $combination_order ) ) {
        return [];
    }

    $result = [];
    foreach ( $combination_order as $combination_key ) {
        if ( isset( $combination_map[ $combination_key ] ) ) {
            $result[] = $combination_map[ $combination_key ]['variation'];
        }
    }

    return $result;
}

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
    global $onepaquc_variation_buttons_on_archive;
    $onepaquc_variation_buttons_on_archive = array();
    add_filter('woocommerce_loop_add_to_cart_link', 'onepaquc_add_variation_buttons_to_loop', 100, 2);
    new onepaquc_add_variation_buttons_on_archive();
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
        $position = get_option("rmenu_wc_direct_checkout_position", "after_product");
        if ($position === 'after_add_to_cart' || $position === 'before_add_to_cart' || $position === 'bottom_add_to_cart' || $position === 'replace_add_to_cart') {
            $position = 'after_product';
        }

        switch ($position) {
            case 'overlay_thumbnail':
            case 'overlay_thumbnail_hover':
            case 'after_product':
                add_action('woocommerce_after_shop_loop_item', array($this, 'onepaquc_variation_buttons'), 110);
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
        global $onepaquc_variation_buttons_on_archive;

        $product_id = $product->get_id();

        static $loop_counter = 0;
        $loop_counter++;
        $context_key = $product_id . '_' . $loop_counter;


        if (!$product || !$product->is_type('variable')) {
            return;
        }

        if(isset($onepaquc_variation_buttons_on_archive[$context_key])){
            return;
        }

        $available_variations = onepaquc_get_validated_variations( $product );
        if (empty($available_variations)) {
            return;
        }

        $onepaquc_variation_buttons_on_archive[$context_key] = true;

        $position     = get_option('rmenu_wc_direct_checkout_position', 'after_product');
        $layout       = get_option('rmenu_variation_layout', 'separate'); // 'combine' | 'separate'
        $show_titles  = (bool) get_option('rmenu_show_variation_title', 0);

        $container_class = 'archive-variations-container';
        if (in_array($position, ['overlay_thumbnail', 'overlay_thumbnail_hover'], true)) {
            $container_class .= ' overlay-variations';
        }
        if (in_array($position, ['after_product'], true)) {
            $container_class .= ' bottom-48';
        }

        echo '<div class="' . esc_attr($container_class) . '" data-layout="' . esc_attr($layout) . '">';

        if ($layout === 'combine') {
            foreach ($available_variations as $variation) {
                $vid = $variation['variation_id'];

                // Build options per attribute (expand "Any" to options ASSIGNED to this product only)
                $attr_options = []; // [attr_key => [ ['slug'=>..., 'label'=>...], ... ]]
                foreach ($variation['attributes'] as $attribute_name => $attribute_value) {
                    $attr_key = str_replace('attribute_', '', $attribute_name);

                    // Specific value chosen -> just that one
                    if ($attribute_value !== '') {
                        $slug  = $attribute_value;
                        $label = $slug;

                        if (taxonomy_exists($attr_key)) {
                            $term = get_term_by('slug', $slug, $attr_key);
                            if ($term && !is_wp_error($term)) {
                                $label = $term->name;
                            }
                        }
                        $attr_options[$attr_key][] = ['slug' => $slug, 'label' => $label];
                        continue;
                    }

                    // "Any ..." chosen -> expand to options that are ASSIGNED on THIS product
                    $prod_attrs = $product->get_attributes();
                    if (isset($prod_attrs[$attr_key])) {
                        /** @var WC_Product_Attribute $pa */
                        $pa = $prod_attrs[$attr_key];

                        if ($pa->is_taxonomy()) {
                            // taxonomy attribute: options are term IDs
                            $term_ids = (array) $pa->get_options();
                            if (!empty($term_ids)) {
                                $terms = get_terms([
                                    'taxonomy'   => $attr_key,
                                    'hide_empty' => false,
                                    'include'    => $term_ids,
                                ]);
                                if (!is_wp_error($terms) && !empty($terms)) {
                                    foreach ($terms as $term) {
                                        $attr_options[$attr_key][] = [
                                            'slug'  => $term->slug,
                                            'label' => $term->name,
                                        ];
                                    }
                                }
                            }
                        } else {
                            // custom (non-taxonomy) attribute: options are raw strings
                            $values = (array) $pa->get_options();
                            foreach ($values as $val) {
                                $val = (string) $val;
                                $attr_options[$attr_key][] = [
                                    'slug'  => sanitize_title($val),
                                    'label' => $val,
                                ];
                            }
                        }
                    }
                }

                // If for some reason we have no options, skip this variation
                if (empty($attr_options)) {
                    continue;
                }

                // Cartesian product across attributes to produce concrete button labels
                $combinations = [[]]; // each combo: [ attr_key => ['slug'=>..., 'label'=>...] ]
                foreach ($attr_options as $key => $opts) {
                    $next = [];
                    foreach ($combinations as $combo) {
                        foreach ($opts as $opt) {
                            $tmp = $combo;
                            $tmp[$key] = $opt;
                            $next[] = $tmp;
                        }
                    }
                    $combinations = $next;
                }

                // Render one button per concrete combination (store attrs for JS if needed)
                foreach ($combinations as $combo) {
                    $labels = [];
                    $attrs  = [];
                    foreach ($combo as $k => $opt) {
                        $labels[]  = $opt['label'];
                        $attrs['attribute_'.$k] = $opt['slug'];
                    }

                    echo '<button type="button" class="variation-button" data-id="' . esc_attr($vid) . '" data-attrs="' . esc_attr(wp_json_encode($attrs)) . '">'
                        . esc_html(implode(' / ', $labels))
                        . '</button>';
                }
            }

            // Keep the hidden input as before
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
                        $prod_attrs = $product->get_attributes();

                        if (isset($prod_attrs[$attr_key])) {
                            /** @var WC_Product_Attribute $pa */
                            $pa = $prod_attrs[$attr_key];

                            if ($pa->is_taxonomy()) {
                                // taxonomy attribute: options are term IDs
                                $term_ids = (array) $pa->get_options();
                                if (!empty($term_ids)) {
                                    $terms = get_terms([
                                        'taxonomy'   => $attr_key,
                                        'hide_empty' => false,
                                        'include'    => $term_ids,
                                    ]);
                                    if (!is_wp_error($terms) && !empty($terms)) {
                                        foreach ($terms as $term) {
                                            if (!isset($attributes_terms[$attr_key])) {
                                                $attributes_terms[$attr_key] = [
                                                    'label' => wc_attribute_label($attr_key, $product),
                                                    'terms' => [],
                                                ];
                                            }
                                            $attributes_terms[$attr_key]['terms'][$term->slug] = $term->name;
                                        }
                                    }
                                }
                            } else {
                                // custom (non-taxonomy) attribute: options are raw strings
                                $values = (array) $pa->get_options();
                                if (!empty($values)) {
                                    if (!isset($attributes_terms[$attr_key])) {
                                        $attributes_terms[$attr_key] = [
                                            'label' => wc_attribute_label($attr_key, $product) ?: ucfirst(str_replace('pa_', '', $attr_key)),
                                            'terms' => [],
                                        ];
                                    }
                                    foreach ($values as $val) {
                                        $val = (string) $val;
                                        $val_slug = sanitize_title($val);
                                        $attributes_terms[$attr_key]['terms'][$val_slug] = $val;
                                    }
                                }
                            }
                        }
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
    global $onepaquc_variation_buttons_on_archive;
    $product_id = $product->get_id();

    static $loop_counter = 0;
    $loop_counter++;
    $context_key = $product_id . '_' . $loop_counter;
    
    if (!$product || !$product->is_type('variable')) {
        return $link;
    }

    if(isset($onepaquc_variation_buttons_on_archive[$context_key])){
        return $link;
    }

    $onepaquc_variation_buttons_on_archive[$context_key] = true;

    $available_variations = onepaquc_get_validated_variations( $product );
    if (empty($available_variations)) {
        return $link;
    }

    $layout      = get_option('rmenu_variation_layout', 'separate'); // 'combine' | 'separate'
    $show_titles = (bool) get_option('rmenu_show_variation_title', 0);

    ob_start();

    echo '<div class="archive-variations-container" data-layout="' . esc_attr($layout) . '">';

    if ($layout === 'combine') {
        foreach ($available_variations as $variation) {
            $vid = $variation['variation_id'];

            // Build options per attribute (expand "Any" to options ASSIGNED to this product only)
            $attr_options = []; // [attr_key => [ ['slug'=>..., 'label'=>...], ... ]]
            foreach ($variation['attributes'] as $attribute_name => $attribute_value) {
                $attr_key = str_replace('attribute_', '', $attribute_name);

                // Specific value chosen -> just that one
                if ($attribute_value !== '') {
                    $slug  = $attribute_value;
                    $label = $slug;

                    if (taxonomy_exists($attr_key)) {
                        $term = get_term_by('slug', $slug, $attr_key);
                        if ($term && !is_wp_error($term)) {
                            $label = $term->name;
                        }
                    }
                    $attr_options[$attr_key][] = ['slug' => $slug, 'label' => $label];
                    continue;
                }

                // "Any ..." chosen -> expand to options that are ASSIGNED on THIS product
                $prod_attrs = $product->get_attributes();
                if (isset($prod_attrs[$attr_key])) {
                    /** @var WC_Product_Attribute $pa */
                    $pa = $prod_attrs[$attr_key];

                    if ($pa->is_taxonomy()) {
                        // taxonomy attribute: options are term IDs
                        $term_ids = (array) $pa->get_options();
                        if (!empty($term_ids)) {
                            $terms = get_terms([
                                'taxonomy'   => $attr_key,
                                'hide_empty' => false,
                                'include'    => $term_ids,
                            ]);
                            if (!is_wp_error($terms) && !empty($terms)) {
                                foreach ($terms as $term) {
                                    $attr_options[$attr_key][] = [
                                        'slug'  => $term->slug,
                                        'label' => $term->name,
                                    ];
                                }
                            }
                        }
                    } else {
                        // custom (non-taxonomy) attribute: options are raw strings
                        $values = (array) $pa->get_options();
                        foreach ($values as $val) {
                            $val = (string) $val;
                            $attr_options[$attr_key][] = [
                                'slug'  => sanitize_title($val),
                                'label' => $val,
                            ];
                        }
                    }
                }
            }

            // If for some reason we have no options, skip this variation
            if (empty($attr_options)) {
                continue;
            }

            // Cartesian product across attributes to produce concrete button labels
            $combinations = [[]]; // each combo: [ attr_key => ['slug'=>..., 'label'=>...] ]
            foreach ($attr_options as $key => $opts) {
                $next = [];
                foreach ($combinations as $combo) {
                    foreach ($opts as $opt) {
                        $tmp = $combo;
                        $tmp[$key] = $opt;
                        $next[] = $tmp;
                    }
                }
                $combinations = $next;
            }

            // Render one button per concrete combination (store attrs for JS if needed)
            foreach ($combinations as $combo) {
                $labels = [];
                $attrs  = [];
                foreach ($combo as $k => $opt) {
                    $labels[]  = $opt['label'];
                    $attrs['attribute_'.$k] = $opt['slug'];
                }

                echo '<button type="button" class="variation-button" data-id="' . esc_attr($vid) . '" data-attrs="' . esc_attr(wp_json_encode($attrs)) . '">'
                    . esc_html(implode(' / ', $labels))
                    . '</button>';
            }
        }

        // Keep the hidden input as before
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
                    $prod_attrs = $product->get_attributes();

                    if (isset($prod_attrs[$attr_key])) {
                        /** @var WC_Product_Attribute $pa */
                        $pa = $prod_attrs[$attr_key];

                        if ($pa->is_taxonomy()) {
                            // taxonomy attribute: options are term IDs
                            $term_ids = (array) $pa->get_options();
                            if (!empty($term_ids)) {
                                $terms = get_terms([
                                    'taxonomy'   => $attr_key,
                                    'hide_empty' => false,
                                    'include'    => $term_ids,
                                ]);
                                if (!is_wp_error($terms) && !empty($terms)) {
                                    foreach ($terms as $term) {
                                        if (!isset($attributes_terms[$attr_key])) {
                                            $attributes_terms[$attr_key] = [
                                                'label' => wc_attribute_label($attr_key, $product),
                                                'terms' => [],
                                            ];
                                        }
                                        $attributes_terms[$attr_key]['terms'][$term->slug] = $term->name;
                                    }
                                }
                            }
                        } else {
                            // custom (non-taxonomy) attribute: options are raw strings
                            $values = (array) $pa->get_options();
                            if (!empty($values)) {
                                if (!isset($attributes_terms[$attr_key])) {
                                    $attributes_terms[$attr_key] = [
                                        'label' => wc_attribute_label($attr_key, $product) ?: ucfirst(str_replace('pa_', '', $attr_key)),
                                        'terms' => [],
                                    ];
                                }
                                foreach ($values as $val) {
                                    $val = (string) $val;
                                    $val_slug = sanitize_title($val);
                                    $attributes_terms[$attr_key]['terms'][$val_slug] = $val;
                                }
                            }
                        }
                    }
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



add_filter( 'woocommerce_post_class', 'onepaquc_add_non_purchasable_product_class', 10, 2 );

function onepaquc_add_non_purchasable_product_class( $classes, $product ) {
    // Check if product object exists
    if ( ! is_object( $product ) ) {
        $product = wc_get_product( get_the_ID() );
    }
    
    // Check if product is not purchasable
    if ( $product && ! $product->is_purchasable() ) {
        $classes[] = 'plugincy-not-purchaseable';
    }
    
    return $classes;
}