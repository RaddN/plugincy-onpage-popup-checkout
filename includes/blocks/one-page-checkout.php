<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Register the Plugincy One Page Checkout Gutenberg Block
 */
function onepaquc_register_one_page_checkout_block() {
    // Skip block registration if Gutenberg is not available
    if ( !function_exists( 'register_block_type' ) ) {
        return;
    }

    // Register the block script
    wp_register_script(
        'plugincy-one-page-checkout-block',
        plugins_url( 'one-page-checkout-block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n' ),
        filemtime( plugin_dir_path( __FILE__ ) . 'one-page-checkout-block.js' ),
        true
    );

    // Register the block
    register_block_type( 'plugincy/one-page-checkout', array(
        'editor_script' => 'plugincy-one-page-checkout-block',
        'editor_style'  => 'plugincy-one-page-checkout-editor',
        'render_callback' => 'onepaquc_render_one_page_checkout_block',
        'attributes' => array(
            'product_ids' => array(
                'type' => 'string',
                'default' => '',
            ),
            'template' => array(
                'type' => 'string',
                'default' => 'product-tabs',
            ),
            'borderRadius' => array(
                'type' => 'number',
                'default' => 4,
            ),
            'boxShadow' => array(
                'type' => 'boolean',
                'default' => false,
            ),
            'primaryColor' => array(
                'type' => 'string',
                'default' => '#4CAF50',
            ),
            'secondaryColor' => array(
                'type' => 'string',
                'default' => '#2196F3',
            ),
            'buttonStyle' => array(
                'type' => 'string',
                'default' => 'filled',
            ),
            'spacing' => array(
                'type' => 'number',
                'default' => 15,
            ),
        ),
    ) );
}
add_action( 'init', 'onepaquc_register_one_page_checkout_block' );

/**
 * Render callback for the Plugincy One Page Checkout block
 *
 * @param array $attributes Block attributes.
 * @return string Generated shortcode.
 */
function onepaquc_render_one_page_checkout_block( $attributes ) {
    // Extract attributes
    $product_ids = isset( $attributes['product_ids'] ) ? $attributes['product_ids'] : '';
    $template = isset( $attributes['template'] ) ? $attributes['template'] : 'product-tabs';
    
    // Generate and return the shortcode
    return '[plugincy_one_page_checkout product_ids="' . esc_attr( $product_ids ) . '" template="' . esc_attr( $template ) . '"]';
}



/**
 * Add custom category for Plugincy blocks
 */
function onepaquc_block_categories( $categories, $post ) {
    // Create the new category array
    $new_category = array(
        'slug' => 'plugincy',
        'title' => __( 'Plugincy', 'one-page-quick-checkout-for-woocommerce' ),
        'icon'  => 'plugincy',
    );

    // Add the new category to the beginning of the categories array
    array_unshift( $categories, $new_category );

    return $categories;
}
add_filter( 'block_categories_all', 'onepaquc_block_categories', 0, 2 );

