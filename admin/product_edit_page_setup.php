<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly



/**
 * One Page Quick Checkout for WooCommerce
 * 
 * Adds a checkbox to product settings and displays checkout form directly on product page
 * when enabled, creating a streamlined purchasing experience.
 */

/**
 * Add One Page Checkout checkbox to product type options
 */
function onepaquc_add_one_page_checkout_to_product_type_options($product_type_options)
{
    $product_type_options['one_page_checkout'] = array(
        'id'            => '_one_page_checkout',
        'wrapper_class' => 'onepaquc-product-option-pro-only',
        'label'         => esc_html__('One Page Checkout (Pro)', 'one-page-quick-checkout-for-woocommerce'),
        'description'   => esc_html__('Enable one page checkout for this product. Available in Pro.', 'one-page-quick-checkout-for-woocommerce'),
        'default'       => 'no'
    );


    wp_nonce_field('onepaquc_save_meta', 'onepaquc_nonce');

    return $product_type_options;
}
add_filter('product_type_options', 'onepaquc_add_one_page_checkout_to_product_type_options');

/**
 * Save One Page Checkout option
 */
function onepaquc_save_one_page_checkout_option($post_id)
{
    return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (
        !isset($_POST['onepaquc_nonce']) ||
        !is_scalar($_POST['onepaquc_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['onepaquc_nonce'])), 'onepaquc_save_meta')
    ) {
        return;
    }

    $is_one_page_checkout = isset($_POST['_one_page_checkout']) ? 'yes' : 'no';
    update_post_meta($post_id, '_one_page_checkout', $is_one_page_checkout);
}
add_action('woocommerce_process_product_meta', 'onepaquc_save_one_page_checkout_option', 10);

function onepaquc_disable_one_page_checkout_product_option()
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!is_object($screen) || 'product' !== $screen->id) {
        return;
    }
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var option = document.getElementById('_one_page_checkout');
            if (!option) {
                return;
            }
            option.disabled = true;
            var label = option.closest('label');
            if (label) {
                label.classList.add('onepaquc-product-option-pro-only');
                if (!label.querySelector('.onepaquc-product-pro-badge')) {
                    var badge = document.createElement('span');
                    badge.className = 'onepaquc-product-pro-badge';
                    badge.textContent = 'Pro';
                    label.appendChild(badge);
                }
            }
        });
    </script>
    <style>
        label.onepaquc-product-option-pro-only {
            opacity: .55;
            position: relative;
        }
        label.onepaquc-product-option-pro-only input {
            cursor: not-allowed;
        }
        .onepaquc-product-pro-badge {
            display: inline-block;
            margin-left: 6px;
            padding: 1px 6px;
            border-radius: 4px;
            background: #d63638;
            color: #fff;
            font-size: 11px;
            line-height: 1.5;
            vertical-align: middle;
        }
    </style>
    <?php
}
add_action('admin_footer-post.php', 'onepaquc_disable_one_page_checkout_product_option');
add_action('admin_footer-post-new.php', 'onepaquc_disable_one_page_checkout_product_option');

