<?php
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 

 function onepaquc_wc_checkout_block_register() {
    // Skip block registration if Gutenberg is not available
    if ( !function_exists( 'register_block_type' ) ) {
        return;
    }

    wp_register_script(
        'wc-checkout-block',
        plugins_url('plugincy_cart_block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor'),
        filemtime(plugin_dir_path(__FILE__) . 'plugincy_cart_block.js'),
        true
    );

    register_block_type('wc/checkout-block', array(
        'editor_script' => 'wc-checkout-block',
        'render_callback' => 'onepaquc_wc_checkout_block_render',
        'attributes' => array(
            // General
            'cartIcon' => array(
                'type' => 'string',
                'default' => 'cart'
            ),
            'drawerPosition' => array(
                'type' => 'string',
                'default' => 'right'
            ),
            'productTitleTag' => array(
                'type' => 'string',
                'default' => 'h4'
            ),
            // Style - Cart
            'cartIconSize' => array(
                'type' => 'number',
                'default' => 24
            ),
            'cartIconColor' => array(
                'type' => 'string',
                'default' => '#000000'
            ),
            // Style - Drawer
            'drawerWidth' => array(
                'type' => 'number',
                'default' => 400
            ),
            'drawerBackground' => array(
                'type' => 'string',
                'default' => '#ffffff'
            ),
            'drawerPadding' => array(
                'type' => 'number',
                'default' => 20
            ),
            'drawerMargin' => array(
                'type' => 'number',
                'default' => 0
            ),
            // Style - Product Image
            'productImageWidth' => array(
                'type' => 'number',
                'default' => 80
            ),
            'productImageHeight' => array(
                'type' => 'number',
                'default' => 80
            ),
            // Style - Product Title
            'productTitleFontSize' => array(
                'type' => 'number',
                'default' => 16
            ),
            'productTitleLineHeight' => array(
                'type' => 'number',
                'default' => 1.5
            ),
            'productTitleColor' => array(
                'type' => 'string',
                'default' => '#333333'
            ),
            // Style - Product Price
            'productPriceFontSize' => array(
                'type' => 'number',
                'default' => 14
            ),
            'productPriceLineHeight' => array(
                'type' => 'number',
                'default' => 1.4
            ),
            'productPriceColor' => array(
                'type' => 'string',
                'default' => '#666666'
            ),
            // Style - Quantity
            'quantityWidth' => array(
                'type' => 'number',
                'default' => 80
            ),
            'quantityHeight' => array(
                'type' => 'number',
                'default' => 40
            ),
            'quantityPadding' => array(
                'type' => 'number',
                'default' => 5
            ),
            // Style - Remove Button
            'removeButtonSize' => array(
                'type' => 'number',
                'default' => 16
            ),
            'removeButtonPadding' => array(
                'type' => 'number',
                'default' => 5
            ),
            // Style - Subtotal
            'subtotalFontSize' => array(
                'type' => 'number',
                'default' => 18
            ),
            'subtotalLineHeight' => array(
                'type' => 'number',
                'default' => 1.5
            ),
            'subtotalColor' => array(
                'type' => 'string',
                'default' => '#333333'
            ),
            'subtotalPadding' => array(
                'type' => 'number',
                'default' => 10
            ),
            'subtotalMargin' => array(
                'type' => 'number',
                'default' => 20
            ),
            // Style - Checkout Button
            'checkoutBtnBackground' => array(
                'type' => 'string',
                'default' => '#333333'
            ),
            'checkoutBtnFontSize' => array(
                'type' => 'number',
                'default' => 16
            ),
            'checkoutBtnLineHeight' => array(
                'type' => 'number',
                'default' => 1.5
            ),
            'checkoutBtnColor' => array(
                'type' => 'string',
                'default' => '#ffffff'
            )
        )
    ));
}
add_action('init', 'onepaquc_wc_checkout_block_register', 10);

 /**
 * Render the block
 *
 * @param array $attributes Block attributes.
 * @return string Rendered block HTML.
 */

function onepaquc_wc_checkout_block_render($attributes = array()) {
    // Extract attributes with defaults
    $attributes = wp_parse_args($attributes, array(
        // General
        'cartIcon' => 'cart',
        'drawerPosition' => 'right',
        'productTitleTag' => 'p',
        // Style - Cart
        'cartIconSize' => 24,
        'cartIconColor' => '#000000',
        // Style - Drawer
        'drawerWidth' => 400,
        'drawerBackground' => '#ffffff',
        'drawerPadding' => 20,
        'drawerMargin' => 0,
        // Style - Product Image
        'productImageWidth' => 80,
        'productImageHeight' => 80,
        // Style - Product Title
        'productTitleFontSize' => 16,
        'productTitleLineHeight' => 1.5,
        'productTitleColor' => '#333333',
        // Style - Product Price
        'productPriceFontSize' => 14,
        'productPriceLineHeight' => 1.4,
        'productPriceColor' => '#666666',
        // Style - Quantity
        'quantityWidth' => 80,
        'quantityHeight' => 40,
        'quantityPadding' => 5,
        // Style - Remove Button
        'removeButtonSize' => 16,
        'removeButtonPadding' => 5,
        // Style - Subtotal
        'subtotalFontSize' => 18,
        'subtotalLineHeight' => 1.5,
        'subtotalColor' => '#333333',
        'subtotalPadding' => 10,
        'subtotalMargin' => 20,
        // Style - Checkout Button
        'checkoutBtnBackground' => '#333333',
        'checkoutBtnFontSize' => 16,
        'checkoutBtnLineHeight' => 1.5,
        'checkoutBtnColor' => '#ffffff'
    ));

    // Generate custom CSS based on attributes
    $custom_css = "
        .rmenu-cart .rwc_cart-button .cart-icon svg {
            width: {$attributes['cartIconSize']}px !important;
            fill: {$attributes['cartIconColor']} !important;
        }
        
        .rmenu-cart .cart-drawer {
            width: {$attributes['drawerWidth']}px !important;
            background-color: {$attributes['drawerBackground']} !important;
        }
        
        .rmenu-cart .cart-drawer .cart-content {
            padding: {$attributes['drawerPadding']}px !important;
            margin: {$attributes['drawerMargin']}px !important;
        }
        
        .rmenu-cart .cart-item .thumbnail img {
            width: {$attributes['productImageWidth']}px !important;
            height: {$attributes['productImageHeight']}px !important;
        }
        
        .rmenu-cart .cart-item .item-title {
            font-size: {$attributes['productTitleFontSize']}px !important;
            line-height: {$attributes['productTitleLineHeight']} !important;
            color: {$attributes['productTitleColor']} !important;
        }
        
        .rmenu-cart .cart-item .item-price {
            font-size: {$attributes['productPriceFontSize']}px !important;
            line-height: {$attributes['productPriceLineHeight']} !important;
            color: {$attributes['productPriceColor']} !important;
        }
        
        .rmenu-cart .cart-item .quantity input.item-quantity {
            width: {$attributes['quantityWidth']}px !important;
            height: {$attributes['quantityHeight']}px !important;
            padding: {$attributes['quantityPadding']}px !important;
        }
        
        .rmenu-cart .cart-item .remove-item svg {
            width: {$attributes['removeButtonSize']}px !important;
        }
        
        .rmenu-cart .cart-item .remove-item {
            padding: {$attributes['removeButtonPadding']}px !important;
        }
        
        .rmenu-cart .cart-subtotal {
            font-size: {$attributes['subtotalFontSize']}px !important;
            line-height: {$attributes['subtotalLineHeight']} !important;
            color: {$attributes['subtotalColor']} !important;
            padding: {$attributes['subtotalPadding']}px !important;
            margin: {$attributes['subtotalMargin']}px !important;
        }
        
        .rmenu-cart .checkout-button {
            background-color: {$attributes['checkoutBtnBackground']} !important;
            font-size: {$attributes['checkoutBtnFontSize']}px !important;
            line-height: {$attributes['checkoutBtnLineHeight']} !important;
            color: {$attributes['checkoutBtnColor']} !important;
        }
    ";
    

    // Add data attributes to pass to the shortcode
    add_filter('onepaquc_cart_data_attributes', function($attributes_array) use ($attributes) {
        return array_merge($attributes_array, array(
            'data-cart-icon' => $attributes['cartIcon'],
            'data-drawer-position' => $attributes['drawerPosition'],
            'data-product-title-tag' => $attributes['productTitleTag'],
            // You can add more data attributes here if needed
        ));
    });

    // Enqueue the custom CSS
    wp_register_style(
        'rmenu-cart-block-style',
        false, // No actual CSS file
        array(), // No dependencies
        '1.0.3' // Version
    );
    
    // Now enqueue it
    wp_enqueue_style('rmenu-cart-block-style');
    wp_add_inline_style( 'rmenu-cart-block-style', esc_html($custom_css), 999 );
    
    // Generate a unique ID for this cart instance
    $cart_id = 'plugincy-cart-' . uniqid();
    
    // Output the shortcode with custom wrapper for targeting
    ob_start();
    echo '<div id="' . esc_attr($cart_id) . '" class="plugincy-customized-cart">';
    echo do_shortcode('[plugincy_cart drawer="' . esc_attr($attributes['drawerPosition']) . '" cart_icon="' . esc_attr($attributes['cartIcon']) . '" product_title_tag="' . esc_attr($attributes['productTitleTag']) . '"]');
    echo '</div>';
    
    return ob_get_clean();
}