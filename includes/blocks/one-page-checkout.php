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

    // Register optional block editor styles
    wp_register_style(
        'plugincy-one-page-checkout-editor',
        plugins_url( 'one-page-checkout-editor.css', __FILE__ ),
        array(),
        filemtime( plugin_dir_path( __FILE__ ) . 'one-page-checkout-editor.css' )
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
            'category' => array(
                'type' => 'string',
                'default' => '',
            ),
            'tags' => array(
                'type' => 'string',
                'default' => '',
            ),
            'attribute' => array(
                'type' => 'string',
                'default' => '',
            ),
            'terms' => array(
                'type' => 'string',
                'default' => '',
            ),
            'template' => array(
                'type' => 'string',
                'default' => 'product-tabs',
            ),
            // Style attributes
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
    // Extract and sanitize attributes
    $product_ids = isset( $attributes['product_ids'] ) ? sanitize_text_field( $attributes['product_ids'] ) : '';
    $category = isset( $attributes['category'] ) ? sanitize_text_field( $attributes['category'] ) : '';
    $tags = isset( $attributes['tags'] ) ? sanitize_text_field( $attributes['tags'] ) : '';
    $attribute = isset( $attributes['attribute'] ) ? sanitize_text_field( $attributes['attribute'] ) : '';
    $terms = isset( $attributes['terms'] ) ? sanitize_text_field( $attributes['terms'] ) : '';
    $template = isset( $attributes['template'] ) ? sanitize_text_field( $attributes['template'] ) : 'product-tabs';
    
    // Build shortcode attributes array
    $shortcode_atts = array();
    
    if ( !empty( $product_ids ) ) {
        $shortcode_atts[] = 'product_ids="' . esc_attr( $product_ids ) . '"';
    }
    
    if ( !empty( $category ) ) {
        $shortcode_atts[] = 'category="' . esc_attr( $category ) . '"';
    }
    
    if ( !empty( $tags ) ) {
        $shortcode_atts[] = 'tags="' . esc_attr( $tags ) . '"';
    }
    
    if ( !empty( $attribute ) ) {
        $shortcode_atts[] = 'attribute="' . esc_attr( $attribute ) . '"';
    }
    
    if ( !empty( $terms ) ) {
        $shortcode_atts[] = 'terms="' . esc_attr( $terms ) . '"';
    }
    
    if ( !empty( $template ) ) {
        $shortcode_atts[] = 'template="' . esc_attr( $template ) . '"';
    }
    
    // Generate the shortcode
    $shortcode = '[plugincy_one_page_checkout';
    if ( !empty( $shortcode_atts ) ) {
        $shortcode .= ' ' . implode( ' ', $shortcode_atts );
    }
    $shortcode .= ']';
    
    return $shortcode;
}

/**
 * Add custom block category for Plugincy blocks
 */
function onepaquc_add_block_category( $categories ) {
    // Check if the category already exists
    foreach ( $categories as $category ) {
        if ( $category['slug'] === 'plugincy' ) {
            return $categories;
        }
    }
    
    // Add Plugincy category at the beginning
    return array_merge(
        array(
            array(
                'slug'  => 'plugincy',
                'title' => __( 'Plugincy', 'plugincy-one-page-checkout' ),
                'icon'  => 'cart',
            ),
        ),
        $categories
    );
}
add_filter( 'block_categories_all', 'onepaquc_add_block_category' );

/**
 * Enqueue block editor assets
 */
function onepaquc_enqueue_block_editor_assets() {
    // Add custom styles to the block editor
    $custom_css = '
    .plugincy-block-preview {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        padding: 16px;
        margin: 16px 0;
    }
    
    .plugincy-color-option {
        margin-bottom: 16px;
    }
    
    .plugincy-color-option label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
    }
    
    .plugincy-tabs .components-tab-panel__tab {
        font-size: 13px;
        font-weight: 500;
    }
    
    .plugincy-tabs .components-tab-panel__tab.active-tab {
        box-shadow: inset 0 -2px 0 0 #007cba;
    }
    
    .components-panel__body-title {
        font-size: 14px;
        font-weight: 600;
    }
    
    .components-base-control__help {
        font-size: 12px;
        font-style: italic;
        color: #757575;
    }
    
    .plugincy-shortcode-preview {
        font-family: monospace;
        font-size: 12px;
        background: #fff;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 3px;
        margin: 0;
        word-break: break-all;
        line-height: 1.4;
    }
    ';
    
    wp_add_inline_style( 'wp-edit-blocks', $custom_css );
}
add_action( 'enqueue_block_editor_assets', 'onepaquc_enqueue_block_editor_assets' );

/**
 * Validate block attributes before rendering
 *
 * @param array $attributes Block attributes.
 * @return array Validated attributes.
 */
function onepaquc_validate_block_attributes( $attributes ) {
    $validated = array();
    
    // Validate product_ids (comma-separated numbers)
    if ( isset( $attributes['product_ids'] ) ) {
        $product_ids = sanitize_text_field( $attributes['product_ids'] );
        if ( preg_match( '/^[\d,\s]*$/', $product_ids ) ) {
            $validated['product_ids'] = $product_ids;
        }
    }
    
    // Validate category (comma-separated slugs)
    if ( isset( $attributes['category'] ) ) {
        $category = sanitize_text_field( $attributes['category'] );
        if ( preg_match( '/^[a-zA-Z0-9\-_,\s]*$/', $category ) ) {
            $validated['category'] = $category;
        }
    }
    
    // Validate tags (comma-separated slugs)
    if ( isset( $attributes['tags'] ) ) {
        $tags = sanitize_text_field( $attributes['tags'] );
        if ( preg_match( '/^[a-zA-Z0-9\-_,\s]*$/', $tags ) ) {
            $validated['tags'] = $tags;
        }
    }
    
    // Validate attribute (single attribute name)
    if ( isset( $attributes['attribute'] ) ) {
        $attribute = sanitize_text_field( $attributes['attribute'] );
        if ( preg_match( '/^[a-zA-Z0-9\-_]*$/', $attribute ) ) {
            $validated['attribute'] = $attribute;
        }
    }
    
    // Validate terms (comma-separated terms)
    if ( isset( $attributes['terms'] ) ) {
        $terms = sanitize_text_field( $attributes['terms'] );
        if ( preg_match( '/^[a-zA-Z0-9\-_,\s]*$/', $terms ) ) {
            $validated['terms'] = $terms;
        }
    }
    
    // Validate template (predefined options)
    $valid_templates = array(
        'product-table', 'product-list', 'product-single', 
        'product-slider', 'product-accordion', 'product-tabs', 'pricing-table'
    );
    if ( isset( $attributes['template'] ) && in_array( $attributes['template'], $valid_templates ) ) {
        $validated['template'] = $attributes['template'];
    } else {
        $validated['template'] = 'product-tabs'; // Default fallback
    }
    
    return array_merge( $attributes, $validated );
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

