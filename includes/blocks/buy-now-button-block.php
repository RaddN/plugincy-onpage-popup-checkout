<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register: wc/buy-btn block
 * Path: includes/blocks/buy-now-button-block.php
 */
function onepaquc_buy_now_button_block_register() {
    // Skip if Gutenberg not available
    if ( ! function_exists( 'register_block_type' ) ) {
        return;
    }

    // Editor script
    wp_register_script(
        'onepaquc-buy-now-button-block',
        plugins_url( 'buy_now_button_block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor' ),
        filemtime( plugin_dir_path( __FILE__ ) . 'buy_now_button_block.js' ),
        true
    );

    register_block_type( 'wc/buy-btn', array(
        'editor_script'   => 'onepaquc-buy-now-button-block',
        'render_callback' => 'onepaquc_buy_now_button_block_render',
        'attributes'      => array(
            // Product / Variation
            'product_id'       => array( 'type' => 'number',  'default' => null ),
            'variation_id'     => array( 'type' => 'number',  'default' => null ),
            'detect_product'   => array( 'type' => 'boolean', 'default' => true ),
            'detect_variation' => array( 'type' => 'boolean', 'default' => false ),

            // UI
            'text'             => array( 'type' => 'string',  'default' => '' ),
            'qty'              => array( 'type' => 'number',  'default' => 1 ),
            'icon'             => array( 'type' => 'string',  'default' => '' ),
            'icon_position'    => array( 'type' => 'string',  'default' => '' ),
            'class'            => array( 'type' => 'string',  'default' => '' ),
            'style'            => array( 'type' => 'string',  'default' => '' ),

            // Behavior
            'show_for'         => array( 'type' => 'string',  'default' => '' ),
            'force'            => array( 'type' => 'boolean', 'default' => false ),
        ),
    ) );
}
add_action( 'init', 'onepaquc_buy_now_button_block_register', 10 );

/**
 * Render callback: reuse your shortcode handler
 */
function onepaquc_buy_now_button_block_render( $attrs = array() ) {
    // Normalize to the shortcode handler’s expected strings
    $atts = array(
        'product_id'       => isset( $attrs['product_id'] ) ? (string) absint( $attrs['product_id'] ) : '',
        'variation_id'     => isset( $attrs['variation_id'] ) ? (string) absint( $attrs['variation_id'] ) : '',
        'detect_product'   => ! empty( $attrs['detect_product'] )   ? '1' : '0',
        'detect_variation' => ! empty( $attrs['detect_variation'] ) ? '1' : '0',
        'text'             => isset( $attrs['text'] )          ? (string) $attrs['text'] : '',
        'qty'              => isset( $attrs['qty'] )           ? (string) max( 1, absint( $attrs['qty'] ) ) : '1',
        'icon'             => isset( $attrs['icon'] )          ? (string) $attrs['icon'] : '',
        'icon_position'    => isset( $attrs['icon_position'] ) ? (string) $attrs['icon_position'] : '',
        'class'            => isset( $attrs['class'] )         ? (string) $attrs['class'] : '',
        'style'            => isset( $attrs['style'] )         ? (string) $attrs['style'] : '',
        'show_for'         => isset( $attrs['show_for'] )      ? (string) $attrs['show_for'] : '',
        'force'            => ! empty( $attrs['force'] )       ? '1' : '0',
    );

    // Prefer calling the PHP renderer directly to avoid shortcode parsing
    if ( function_exists( 'onepaquc_button_shortcode_handler' ) ) {
        return onepaquc_button_shortcode_handler( $atts );
    }

    // Fallback: build a shortcode string if handler isn’t in scope
    $shortcode = '[onepaquc_button'
        . ( $atts['product_id']       !== '' ? ' product_id="' . esc_attr( $atts['product_id'] ) . '"' : '' )
        . ( $atts['variation_id']     !== '' ? ' variation_id="' . esc_attr( $atts['variation_id'] ) . '"' : '' )
        . ( $atts['detect_product']   !== '' ? ' detect_product="' . esc_attr( $atts['detect_product'] ) . '"' : '' )
        . ( $atts['detect_variation'] !== '' ? ' detect_variation="' . esc_attr( $atts['detect_variation'] ) . '"' : '' )
        . ( $atts['text']             !== '' ? ' text="' . esc_attr( $atts['text'] ) . '"' : '' )
        . ( $atts['qty']              !== '' ? ' qty="' . esc_attr( $atts['qty'] ) . '"' : '' )
        . ( $atts['icon']             !== '' ? ' icon="' . esc_attr( $atts['icon'] ) . '"' : '' )
        . ( $atts['icon_position']    !== '' ? ' icon_position="' . esc_attr( $atts['icon_position'] ) . '"' : '' )
        . ( $atts['class']            !== '' ? ' class="' . esc_attr( $atts['class'] ) . '"' : '' )
        . ( $atts['style']            !== '' ? ' style="' . esc_attr( $atts['style'] ) . '"' : '' )
        . ( $atts['show_for']         !== '' ? ' show_for="' . esc_attr( $atts['show_for'] ) . '"' : '' )
        . ( $atts['force']            !== '' ? ' force="' . esc_attr( $atts['force'] ) . '"' : '' )
        . ']';

    return do_shortcode( $shortcode );
}
