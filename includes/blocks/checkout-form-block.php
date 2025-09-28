<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register: wc/one-page-checkout
 * Path: includes/blocks/checkout-form-block.php
 */
function onepaquc_checkout_form_block_register() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	// Editor script for the block UI (inspector controls, preview, etc.)
	wp_register_script(
		'onepaquc-checkout-form-block',
		plugins_url( 'checkout_form_block.js', __FILE__ ),
		array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor' ),
		filemtime( plugin_dir_path( __FILE__ ) . 'checkout_form_block.js' ),
		true
	);

	register_block_type(
		'wc/one-page-checkout',
		array(
			'editor_script'   => 'onepaquc-checkout-form-block',
			'render_callback' => 'onepaquc_checkout_form_block_render',
			'attributes'      => array(
				'auto_add'     => array( 'type' => 'boolean', 'default' => true ),
				'clear_cart'   => array( 'type' => 'boolean', 'default' => false ),
				'product_id'   => array( 'type' => 'string',  'default' => '' ), // keep string to allow empty
				'variation_id' => array( 'type' => 'string',  'default' => '' ),
				'qty'          => array( 'type' => 'number',  'default' => 1 ),
			),
			'supports'        => array(
				'align'  => true,
				'anchor' => true,
				'spacing' => array( 'margin' => true, 'padding' => true ),
			),
		)
	);
}
add_action( 'init', 'onepaquc_checkout_form_block_register', 10 );

/**
 * Render callback
 *
 * @param array $attributes Block attributes from the editor.
 * @return string HTML
 */
function onepaquc_checkout_form_block_render( $attributes = array() ) {
	// If the shortcode renderer is missing, show an informative placeholder (editor only).
	if ( ! function_exists( 'onepaquc_display_one_page_checkout_form' ) ) {
		if ( is_admin() ) {
			return '<div class="onepaquc-checkout-placeholder" style="border:1px dashed #c3c4c7;padding:12px;border-radius:6px;background:#fff;">
						<strong>Checkout (Preview)</strong><br>
						<span style="color:#646970;">Renderer not available. Make sure the One-Page Checkout module is active.</span>
					</div>';
		}
		return '';
	}

	// Merge defaults with incoming attributes.
	$defaults = array(
		'auto_add'     => true,
		'clear_cart'   => false,
		'product_id'   => '',
		'variation_id' => '',
		'qty'          => 1,
	);
	$a = wp_parse_args( is_array( $attributes ) ? $attributes : array(), $defaults );

	// Sanitize + normalize.
	$auto_add     = (bool) $a['auto_add'];
	$clear_cart   = (bool) $a['clear_cart'];
	$product_id   = is_scalar( $a['product_id'] ) ? preg_replace( '/\D+/', '', (string) $a['product_id'] ) : '';
	$variation_id = is_scalar( $a['variation_id'] ) ? preg_replace( '/\D+/', '', (string) $a['variation_id'] ) : '';
	$qty          = (int) ( is_numeric( $a['qty'] ) ? $a['qty'] : 1 );
	$qty          = max( 1, $qty );

	// Build the shortcode with only the necessary attrs.
	$parts   = array( 'onepaquc_checkout' );
	if ( false === $auto_add ) {
		$parts[] = 'auto_add="no"';
	}
	if ( true === $clear_cart ) {
		$parts[] = 'clear_cart="yes"';
	}
	if ( '' !== $product_id ) {
		$parts[] = 'product_id="' . esc_attr( $product_id ) . '"';
	}
	if ( '' !== $variation_id ) {
		$parts[] = 'variation_id="' . esc_attr( $variation_id ) . '"';
	}
	if ( 1 !== $qty ) {
		$parts[] = 'qty="' . (int) $qty . '"';
	}

	$shortcode = '[' . implode( ' ', $parts ) . ']';

	// Execute the shortcode. It handles cart ops + rendering internally.
	$html = do_shortcode( $shortcode );

	/**
	 * If the shortcode outputs nothing (e.g., empty cart), show a clear preview message in the editor,
	 * but keep the front-end silent (expected behavior).
	 */
	if ( '' === trim( (string) $html ) ) {
		if ( is_admin() ) {
			$esc_sc = esc_html( $shortcode );
			return '<div class="onepaquc-checkout-placeholder" style="border:1px dashed #c3c4c7;padding:12px;border-radius:6px;background:#fff;">
						<strong>Checkout (Preview)</strong><br>
						<span style="color:#646970;">The checkout form appears on the front end when the cart is not empty.</span>
						<div style="margin-top:8px;padding:8px;background:#f6f7f7;border:1px solid #e0e0e0;border-radius:4px;font-family:monospace;"><em>' . $esc_sc . '</em></div>
					</div>';
		}
		return '';
	}

	return $html;
}
