<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
// pricing table template - using actual product data
// shortcode to display one page checkout [plugincy_one_page_checkout product_ids="" template="pricing-table"]
?>

<div class="one-page-checkout-container">
    <div class="product-comparison-table">
        <?php
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
                        echo wp_kses_post(wc_get_product_category_list($product->get_id(), ', ') ?: '—');
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
                        echo wp_kses_post(wc_get_product_tag_list($product->get_id(), ', ') ?: '—');
                        echo '</td>';
                    }

                    echo '</tr>';
                }
                ?>
            </table>
        <?php } ?>
        <?php onepaquc_rmenu_checkout_popup(true); ?>
    </div>
</div>


<?php $inline_script = '
    jQuery(document).ready(function($) {
        // Update checkout when product is added to cart
        $(document.body).on("added_to_cart", function() {
            $(document.body).trigger("update_checkout");
            // Small delay to allow cart to update
            setTimeout(function() {
                $("html, body").animate({
                    scrollTop: $(document).height()
                }, 800);
            }, 300);
        });
    });';
    // Enqueue the script
    wp_add_inline_script('rmenu-cart-script', $inline_script, 99);
    
