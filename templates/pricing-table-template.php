<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
// pricing table template - using actual product data
// shortcode to display one page checkout [plugincy_one_page_checkout product_ids="" template="pricing-table"]
?>

<div class="one-page-checkout-container">
    <div class="product-comparison-table">
        <?php
        $product_ids = explode(',', $atts['product_ids']);
        //remove any whitespace from product IDs
        $product_ids = array_map('trim', $product_ids);
        $products = [];

        // Get all products first
        foreach ($product_ids as $item_id) {
            $product_id = intval($item_id);
            $product = wc_get_product($product_id);
            if ($product) {
                $products[] = $product;
            }
        }

        // Only proceed if we have products
        if (!empty($products)) {
            // Get all attributes across all products for comparison
            $all_attributes = array();
            $attribute_taxonomies = array();

            foreach ($products as $product) {
                $product_attributes = $product->get_attributes();
                foreach ($product_attributes as $attribute_name => $attribute) {
                    if ($attribute->is_taxonomy()) {
                        $tax_name = str_replace('pa_', '', $attribute_name);
                        $attribute_taxonomies[$tax_name] = wc_get_attribute(wc_attribute_taxonomy_id_by_name($tax_name));
                    }
                    $all_attributes[$attribute_name] = true;
                }
            }
        ?>
            <table class="comparison-table">
                <!-- Product Headers -->
                <tr class="product-header-row">
                    <th class="feature-column"></th>
                    <?php foreach ($products as $product) : ?>
                        <th class="product-column">
                            <div class="product-image-container">
                                <?php echo wp_kses_post($product->get_image('thumbnail')); ?>
                                <?php if ($product->is_on_sale()) : ?>
                                    <span class="new-badge">NEW</span>
                                <?php endif; ?>
                            </div>
                            <h3 class="product-title"><?php echo esc_html($product->get_name()); ?></h3>
                            <div class="product-price"><?php echo wp_kses_post($product->get_price_html()); ?></div>

                            <?php
                            // Display color/variation options if available
                            $attributes = $product->get_attributes();
                            foreach ($attributes as $attribute_name => $attribute) {
                                if ($attribute->get_visible() && $attribute->is_taxonomy()) {
                                    $taxonomy = str_replace('pa_', '', $attribute_name);

                                    // If this is a color-type attribute, display as color dots
                                    if (in_array($taxonomy, array('color', 'colours', 'colors', 'color-family'))) {
                                        echo '<div class="product-variations">';
                                        $terms = wc_get_product_terms($product->get_id(), $attribute_name, array('fields' => 'all'));
                                        foreach ($terms as $term) {
                                            echo '<span class="variation-option ' . esc_attr($term->slug) . '" title="' . esc_attr($term->name) . '"></span>';
                                        }
                                        echo '</div>';
                                    }
                                }
                            }
                            ?>

                            <div class="add-to-cart-container">
                                <?php
                                echo do_shortcode('[add_to_cart id="' . $product->get_id() . '" style="" show_price="false" quantity="1" class="add-to-cart-button"]');
                                ?>
                            </div>
                        </th>
                    <?php endforeach; ?>
                </tr>

                <?php
                // Create attribute sections for comparison
                // Get basic product data first
                ?>
                <tr class="section-header">
                    <td colspan="<?php echo count($products) + 1; ?>">PRODUCT DETAILS</td>
                </tr>

                <!-- SKU Row -->
                <tr class="feature-row">
                    <td class="feature-name">SKU</td>
                    <?php foreach ($products as $product) : ?>
                        <td class="feature-value"><?php echo $product->get_sku() ? esc_html($product->get_sku()) : '—'; ?></td>
                    <?php endforeach; ?>
                </tr>

                <!-- Stock Status Row -->
                <tr class="feature-row">
                    <td class="feature-name">Stock Status</td>
                    <?php foreach ($products as $product) : ?>
                        <td class="feature-value">
                            <?php
                            if ($product->is_in_stock()) {
                                echo '<span class="in-stock">In Stock</span>';
                            } else {
                                echo '<span class="out-of-stock">Out of Stock</span>';
                            }
                            ?>
                        </td>
                    <?php endforeach; ?>
                </tr>

                <!-- Weight Row (if applicable) -->
                <?php if (array_reduce($products, function ($carry, $product) {
                    return $carry || $product->get_weight();
                }, false)) : ?>
                    <tr class="feature-row">
                        <td class="feature-name">Weight</td>
                        <?php foreach ($products as $product) : ?>
                            <td class="feature-value">
                                <?php echo $product->get_weight() ? esc_html($product->get_weight() . ' ' . get_option('woocommerce_weight_unit')) : '—'; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endif; ?>

                <!-- Dimensions Row (if applicable) -->
                <?php if (array_reduce($products, function ($carry, $product) {
                    return $carry || ($product->get_length() || $product->get_width() || $product->get_height());
                }, false)) : ?>
                    <tr class="feature-row">
                        <td class="feature-name">Dimensions</td>
                        <?php foreach ($products as $product) : ?>
                            <td class="feature-value">
                                <?php
                                if ($product->get_length() || $product->get_width() || $product->get_height()) {
                                    $dimensions = array(
                                        wc_format_localized_decimal($product->get_length()),
                                        wc_format_localized_decimal($product->get_width()),
                                        wc_format_localized_decimal($product->get_height())
                                    );
                                    echo esc_html(implode(' × ', array_filter($dimensions)) . ' ' . get_option('woocommerce_dimension_unit'));
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endif; ?>

                <?php
                // Display each standard taxonomy attribute
                foreach ($attribute_taxonomies as $taxonomy_slug => $taxonomy_obj) {
                    if (!$taxonomy_obj) continue;

                    $taxonomy_name = $taxonomy_obj->name ? $taxonomy_obj->name : $taxonomy_slug;
                    $taxonomy_label = isset($taxonomy_obj->label) ? $taxonomy_obj->label : ucfirst($taxonomy_slug);

                    echo '<tr class="feature-row">';
                    echo '<td class="feature-name">' . esc_html($taxonomy_label ?? "") . '</td>';

                    foreach ($products as $product) {
                        echo '<td class="feature-value">';
                        $attribute_slug = 'pa_' . $taxonomy_slug;
                        if ($product->get_attribute($attribute_slug)) {
                            echo esc_html($product->get_attribute($attribute_slug));
                        } else {
                            echo '—';
                        }
                        echo '</td>';
                    }

                    echo '</tr>';
                }

                // Get all custom product attributes (non-taxonomy)
                $custom_attributes = array();
                foreach ($products as $product) {
                    foreach ($product->get_attributes() as $attribute_name => $attribute) {
                        if (!$attribute->is_taxonomy()) {
                            $custom_attributes[$attribute_name] = true;
                        }
                    }
                }

                // Display custom product attributes
                foreach (array_keys($custom_attributes) as $attribute_name) {
                    echo '<tr class="feature-row">';
                    echo '<td class="feature-name">' . esc_html(wc_attribute_label($attribute_name)) . '</td>';

                    foreach ($products as $product) {
                        echo '<td class="feature-value">';
                        $attributes = $product->get_attributes();
                        if (isset($attributes[$attribute_name])) {
                            echo esc_html($product->get_attribute($attribute_name));
                        } else {
                            echo '—';
                        }
                        echo '</td>';
                    }

                    echo '</tr>';
                }

                // Check if products have categories
                $has_categories = false;
                foreach ($products as $product) {
                    if (!empty($product->get_category_ids())) {
                        $has_categories = true;
                        break;
                    }
                }

                if ($has_categories) {
                    echo '<tr class="feature-row">';
                    echo '<td class="feature-name">Categories</td>';

                    foreach ($products as $product) {
                        echo '<td class="feature-value">';
                        echo wc_get_product_category_list($product->get_id(), ', ') ?: '—';
                        echo '</td>';
                    }

                    echo '</tr>';
                }

                // Check if products have tags
                $has_tags = false;
                foreach ($products as $product) {
                    if (!empty($product->get_tag_ids())) {
                        $has_tags = true;
                        break;
                    }
                }

                if ($has_tags) {
                    echo '<tr class="feature-row">';
                    echo '<td class="feature-name">Tags</td>';

                    foreach ($products as $product) {
                        echo '<td class="feature-value">';
                        echo wc_get_product_tag_list($product->get_id(), ', ') ?: '—';
                        echo '</td>';
                    }

                    echo '</tr>';
                }
                ?>
            </table>
        <?php } ?>
        <?php rmenu_checkout_popup(true); ?>
    </div>
</div>

<style>
    .product-comparison-table {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }

    .comparison-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 40px;
    }

    /* Header section */
    .product-header-row {
        border-bottom: 1px solid #eee;
    }

    .product-header-row th {
        padding: 15px;
        text-align: center;
        vertical-align: top;
    }

    .feature-column {
        width: 200px;
        text-align: left !important;
    }

    .product-column {
        padding: 25px 15px !important;
    }

    /* Product image and info */
    .product-image-container {
        position: relative;
        margin-bottom: 15px;
        text-align: center;
    }

    .product-image-container img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 0 auto;
    }

    .new-badge {
        position: absolute;
        top: 0;
        right: 0;
        background-color: #ff4081;
        color: white;
        padding: 3px 8px;
        font-size: 12px;
        border-radius: 3px;
    }

    .product-title {
        font-size: 18px;
        font-weight: 500;
        margin: 10px 0;
        text-align: center;
    }

    .product-price {
        font-size: 16px;
        font-weight: 500;
        margin-bottom: 15px;
        text-align: center;
    }

    .product-variations {
        display: flex;
        justify-content: center;
        margin: 10px 0;
        gap: 5px;
    }

    .variation-option {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: inline-block;
        border: 1px solid #ddd;
    }

    /* Common color variations - will be extended by actual product colors */
    .black {
        background-color: #000;
    }

    .blue {
        background-color: #1e88e5;
    }

    .pink {
        background-color: #e91e63;
    }

    .red {
        background-color: #f44336;
    }

    .green {
        background-color: #4caf50;
    }

    .yellow {
        background-color: #ffeb3b;
    }

    .purple {
        background-color: #9c27b0;
    }

    .orange {
        background-color: #ff9800;
    }

    .brown {
        background-color: #795548;
    }

    .gray,
    .grey {
        background-color: #9e9e9e;
    }

    .white {
        background-color: #ffffff;
    }

    /* Additional colors */
    .gold {
        background-color: #ffd700;
    }

    .multicolor {
        background: linear-gradient(to right, red, orange, yellow, green, blue, indigo, violet);
    }

    .off-white {
        background-color: #f5f5f5;
    }

    .silver {
        background-color: #c0c0c0;
    }

    .navy {
        background-color: #000080;
    }

    .teal {
        background-color: #008080;
    }

    .olive {
        background-color: #808000;
    }

    .maroon {
        background-color: #800000;
    }

    .aqua {
        background-color: #00ffff;
    }

    .lime {
        background-color: #00ff00;
    }

    .coral {
        background-color: #ff7f50;
    }

    .lavender {
        background-color: #e6e6fa;
    }

    .turquoise {
        background-color: #40e0d0;
    }

    .beige {
        background-color: #f5f5dc;
    }

    /* Extended color palette */
    .amber {
        background-color: #ffc107;
    }

    .azure {
        background-color: #f0ffff;
    }

    .bronze {
        background-color: #cd7f32;
    }

    .burgundy {
        background-color: #800020;
    }

    .charcoal {
        background-color: #36454f;
    }

    .chartreuse {
        background-color: #7fff00;
    }

    .cobalt {
        background-color: #0047ab;
    }

    .copper {
        background-color: #b87333;
    }

    .crimson {
        background-color: #dc143c;
    }

    .cyan {
        background-color: #00ffff;
    }

    .emerald {
        background-color: #50c878;
    }

    .fuchsia {
        background-color: #ff00ff;
    }

    .forest-green {
        background-color: #228b22;
    }

    .hot-pink {
        background-color: #ff69b4;
    }

    .indigo {
        background-color: #4b0082;
    }

    .ivory {
        background-color: #fffff0;
    }

    .jade {
        background-color: #00a86b;
    }

    .khaki {
        background-color: #f0e68c;
    }

    .lemon {
        background-color: #fff700;
    }

    .lilac {
        background-color: #c8a2c8;
    }

    .magenta {
        background-color: #ff00ff;
    }

    .mahogany {
        background-color: #c04000;
    }

    .mint {
        background-color: #3eb489;
    }

    .mustard {
        background-color: #ffdb58;
    }

    .neon-green {
        background-color: #39ff14;
    }

    .neon-pink {
        background-color: #ff6ec7;
    }

    .ochre {
        background-color: #cc7722;
    }

    .olive-drab {
        background-color: #6b8e23;
    }

    .peach {
        background-color: #ffcba4;
    }

    .periwinkle {
        background-color: #ccccff;
    }

    .plum {
        background-color: #8e4585;
    }

    .rose {
        background-color: #ff007f;
    }

    .rust {
        background-color: #b7410e;
    }

    .salmon {
        background-color: #fa8072;
    }

    .sapphire {
        background-color: #0f52ba;
    }

    .scarlet {
        background-color: #ff2400;
    }

    .sea-green {
        background-color: #2e8b57;
    }

    .sky-blue {
        background-color: #87ceeb;
    }

    .slate {
        background-color: #708090;
    }

    .tan {
        background-color: #d2b48c;
    }

    .taupe {
        background-color: #483c32;
    }

    .terracotta {
        background-color: #e2725b;
    }

    .thistle {
        background-color: #d8bfd8;
    }

    .violet {
        background-color: #ee82ee;
    }

    .wheat {
        background-color: #f5deb3;
    }

    /* Material design colors */
    .amber-100 {
        background-color: #ffecb3;
    }

    .amber-500 {
        background-color: #ffc107;
    }

    .amber-900 {
        background-color: #ff6f00;
    }

    .blue-100 {
        background-color: #bbdefb;
    }

    .blue-500 {
        background-color: #2196f3;
    }

    .blue-900 {
        background-color: #0d47a1;
    }

    .cyan-100 {
        background-color: #b2ebf2;
    }

    .cyan-500 {
        background-color: #00bcd4;
    }

    .cyan-900 {
        background-color: #006064;
    }

    .deep-orange-100 {
        background-color: #ffccbc;
    }

    .deep-orange-500 {
        background-color: #ff5722;
    }

    .deep-orange-900 {
        background-color: #bf360c;
    }

    .deep-purple-100 {
        background-color: #d1c4e9;
    }

    .deep-purple-500 {
        background-color: #673ab7;
    }

    .deep-purple-900 {
        background-color: #311b92;
    }

    .light-blue-100 {
        background-color: #b3e5fc;
    }

    .light-blue-500 {
        background-color: #03a9f4;
    }

    .light-blue-900 {
        background-color: #01579b;
    }

    .light-green-100 {
        background-color: #dcedc8;
    }

    .light-green-500 {
        background-color: #8bc34a;
    }

    .light-green-900 {
        background-color: #33691e;
    }

    /* Add to cart button */
    .add-to-cart-container {
        text-align: center;
        margin-top: 15px;
    }

    /* Feature categories and rows */
    .section-header {
        background-color: #f5f5f5;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.5px;
    }

    .section-header td {
        padding: 10px 15px;
        color: #616161;
    }

    .feature-row td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
        text-align: center;
        vertical-align: middle;
    }

    .feature-name {
        font-size: 14px;
        color: #424242;
        text-align: left !important;
        font-weight: 500;
    }

    .feature-value {
        font-size: 14px;
        color: #757575;
    }

    .in-stock {
        color: #4caf50;
        font-weight: 500;
    }

    .out-of-stock {
        color: #f44336;
        font-weight: 500;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .comparison-table {
            display: block;
            overflow-x: auto;
        }

        .feature-column {
            width: 150px;
        }

        .product-title {
            font-size: 16px;
        }

        .product-price {
            font-size: 14px;
        }
    }
</style>

<script>
    jQuery(document).ready(function($) {
        // Update checkout when product is added to cart
        $(document.body).on('added_to_cart', function() {
            $(document.body).trigger('update_checkout');
            // Small delay to allow cart to update
            setTimeout(function() {
                $('html, body').animate({
                    scrollTop: $(document).height()
                }, 800);
            }, 300);
        });
    });
</script>