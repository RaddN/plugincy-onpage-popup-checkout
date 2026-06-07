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
        onepaquc_asset_version( 'includes/blocks/one-page-checkout-block.js' ),
        true
    );

    // Register optional block editor styles
    wp_register_style(
        'plugincy-one-page-checkout-editor',
        plugins_url( 'one-page-checkout-editor.css', __FILE__ ),
        array(),
        onepaquc_asset_version( 'includes/blocks/one-page-checkout-editor.css' )
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
function onepaquc_render_one_page_checkout_block($attributes)
{
    $attributes = onepaquc_validate_block_attributes(is_array($attributes) ? $attributes : array());

    // Extract and sanitize attributes
    $product_ids = isset($attributes['product_ids']) ? $attributes['product_ids'] : '';
    $category = isset($attributes['category']) ? $attributes['category'] : '';
    $tags = isset($attributes['tags']) ? $attributes['tags'] : '';
    $attribute = isset($attributes['attribute']) ? $attributes['attribute'] : '';
    $terms = isset($attributes['terms']) ? $attributes['terms'] : '';
    $template = isset($attributes['template']) ? $attributes['template'] : 'product-tabs';
    $borderRadius = isset($attributes['borderRadius']) && is_numeric($attributes['borderRadius']) ? (int) $attributes['borderRadius'] : 4;
    $boxShadow = isset($attributes['boxShadow']) ? (bool) $attributes['boxShadow'] : false;
    $primaryColor = onepaquc_sanitize_hex_color(isset($attributes['primaryColor']) ? $attributes['primaryColor'] : '', '#4CAF50');
    $secondaryColor = onepaquc_sanitize_hex_color(isset($attributes['secondaryColor']) ? $attributes['secondaryColor'] : '', '#2196F3');
    $buttonStyle = isset($attributes['buttonStyle']) && is_scalar($attributes['buttonStyle']) ? sanitize_key($attributes['buttonStyle']) : 'filled';
    $spacing = isset($attributes['spacing']) && is_numeric($attributes['spacing']) ? (int) $attributes['spacing'] : 15;

    $buttonStyle    = in_array($buttonStyle, array('filled', 'outlined', 'text'), true) ? $buttonStyle : 'filled';
    $wrapper_styles = array(
        '--onepaquc-block-border-radius:' . min(100, max(0, $borderRadius)) . 'px',
        '--onepaquc-block-spacing:' . min(200, max(0, $spacing)) . 'px',
        '--onepaquc-primary-color:' . $primaryColor,
        '--onepaquc-secondary-color:' . $secondaryColor,
        '--onepaquc-block-shadow:' . ($boxShadow ? '0 2px 10px rgba(0, 0, 0, 0.1)' : 'none'),
    );

    // Build shortcode attributes array
    $shortcode_atts = array();

    if (!empty($product_ids)) {
        $shortcode_atts[] = 'product_ids="' . esc_attr($product_ids) . '"';
    }

    if (!empty($category)) {
        $shortcode_atts[] = 'category="' . esc_attr($category) . '"';
    }

    if (!empty($tags)) {
        $shortcode_atts[] = 'tags="' . esc_attr($tags) . '"';
    }

    if (!empty($attribute)) {
        $shortcode_atts[] = 'attribute="' . esc_attr($attribute) . '"';
    }

    if (!empty($terms)) {
        $shortcode_atts[] = 'terms="' . esc_attr($terms) . '"';
    }

    if (!empty($template)) {
        $shortcode_atts[] = 'template="' . esc_attr($template) . '"';
    }

    // Generate the shortcode
    $shortcode = '[plugincy_one_page_checkout';
    if (!empty($shortcode_atts)) {
        $shortcode .= ' ' . implode(' ', $shortcode_atts);
    }
    $shortcode .= ']';

    return '<div class="plugincy-one-page-checkout-block button-style-' . esc_attr($buttonStyle) . '" style="' . esc_attr(implode(';', $wrapper_styles)) . '">' . $shortcode . '</div>';
}
/**
 * Add custom block category for Plugincy blocks
 */
function onepaquc_add_block_category( $categories ) {
    $categories = is_array( $categories ) ? $categories : array();

    // Check if the category already exists
    foreach ( $categories as $category ) {
        if ( is_array( $category ) && isset( $category['slug'] ) && 'plugincy' === $category['slug'] ) {
            return $categories;
        }
    }
    
    // Add Plugincy category at the beginning
    return array_merge(
        array(
            array(
                'slug'  => 'plugincy',
                'title' => esc_html__( 'Plugincy', 'one-page-quick-checkout-for-woocommerce' ),
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
    $attributes = is_array( $attributes ) ? $attributes : array();
    $validated = array();
    
    // Validate product_ids (comma-separated numbers)
    if ( isset( $attributes['product_ids'] ) && is_scalar( $attributes['product_ids'] ) ) {
        $product_ids = sanitize_text_field( $attributes['product_ids'] );
        if ( preg_match( '/^[\d,\s]*$/', $product_ids ) ) {
            $validated['product_ids'] = $product_ids;
        }
    }
    
    // Validate category (comma-separated slugs)
    if ( isset( $attributes['category'] ) && is_scalar( $attributes['category'] ) ) {
        $category = sanitize_text_field( $attributes['category'] );
        if ( preg_match( '/^[a-zA-Z0-9\-_,\s]*$/', $category ) ) {
            $validated['category'] = $category;
        }
    }
    
    // Validate tags (comma-separated slugs)
    if ( isset( $attributes['tags'] ) && is_scalar( $attributes['tags'] ) ) {
        $tags = sanitize_text_field( $attributes['tags'] );
        if ( preg_match( '/^[a-zA-Z0-9\-_,\s]*$/', $tags ) ) {
            $validated['tags'] = $tags;
        }
    }
    
    // Validate attribute (single attribute name)
    if ( isset( $attributes['attribute'] ) && is_scalar( $attributes['attribute'] ) ) {
        $attribute = sanitize_text_field( $attributes['attribute'] );
        if ( preg_match( '/^[a-zA-Z0-9\-_]*$/', $attribute ) ) {
            $validated['attribute'] = $attribute;
        }
    }
    
    // Validate terms (comma-separated terms)
    if ( isset( $attributes['terms'] ) && is_scalar( $attributes['terms'] ) ) {
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
    if ( isset( $attributes['template'] ) && is_scalar( $attributes['template'] ) && in_array( $attributes['template'], $valid_templates, true ) ) {
        $validated['template'] = (string) $attributes['template'];
    } else {
        $validated['template'] = 'product-tabs'; // Default fallback
    }

    $validated['borderRadius'] = isset( $attributes['borderRadius'] ) && is_numeric( $attributes['borderRadius'] )
        ? min( 100, max( 0, (int) $attributes['borderRadius'] ) )
        : 4;
    $validated['boxShadow'] = isset( $attributes['boxShadow'] ) && is_bool( $attributes['boxShadow'] )
        ? $attributes['boxShadow']
        : false;
    $validated['primaryColor'] = onepaquc_sanitize_hex_color(
        isset( $attributes['primaryColor'] ) ? $attributes['primaryColor'] : '',
        '#4CAF50'
    );
    $validated['secondaryColor'] = onepaquc_sanitize_hex_color(
        isset( $attributes['secondaryColor'] ) ? $attributes['secondaryColor'] : '',
        '#2196F3'
    );
    $button_style = isset( $attributes['buttonStyle'] ) && is_scalar( $attributes['buttonStyle'] )
        ? sanitize_key( (string) $attributes['buttonStyle'] )
        : 'filled';
    $validated['buttonStyle'] = in_array( $button_style, array( 'filled', 'outlined', 'text' ), true ) ? $button_style : 'filled';
    $validated['spacing'] = isset( $attributes['spacing'] ) && is_numeric( $attributes['spacing'] )
        ? min( 200, max( 0, (int) $attributes['spacing'] ) )
        : 15;

    return array_merge(
        array(
            'product_ids'    => '',
            'category'       => '',
            'tags'           => '',
            'attribute'      => '',
            'terms'          => '',
            'template'       => 'product-tabs',
            'borderRadius'   => 4,
            'boxShadow'      => false,
            'primaryColor'   => '#4CAF50',
            'secondaryColor' => '#2196F3',
            'buttonStyle'    => 'filled',
            'spacing'        => 15,
        ),
        $validated
    );
}
