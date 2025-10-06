<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Elementor Widget: One-Page Checkout
 * Renders via the [onepaquc_checkout] shortcode so cart ops (auto-add, clear, etc.) are consistent.
 */
class Plugincy_OPQC_Checkout_Widget extends Widget_Base {

	public function get_name() {
		return 'onepaquc_checkout';
	}

	public function get_title() {
		return esc_html__( 'One-Page Checkout', 'one-page-quick-checkout-for-woocommerce' );
	}

	public function get_icon() {
		// Uses your injected dashicon class; Elementor falls back if not found
		return 'dashicons-onepaquc_one_page_cart';
	}

	public function get_categories() {
		// You already define the "plugincy" category
		return [ 'plugincy' ];
	}

	public function get_keywords() {
		return [ 'checkout', 'woocommerce', 'one page', 'quick checkout', 'cart' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_checkout',
			[
				'label' => esc_html__( 'Checkout Settings', 'one-page-quick-checkout-for-woocommerce' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'auto_add',
			[
				'label'        => esc_html__( 'Auto add to cart', 'one-page-quick-checkout-for-woocommerce' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'one-page-quick-checkout-for-woocommerce' ),
				'label_off'    => esc_html__( 'No', 'one-page-quick-checkout-for-woocommerce' ),
				'return_value' => 'yes',
				'default'      => 'yes', // matches shortcode default (enabled)
			]
		);

		$this->add_control(
			'clear_cart',
			[
				'label'        => esc_html__( 'Clear cart before adding', 'one-page-quick-checkout-for-woocommerce' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'one-page-quick-checkout-for-woocommerce' ),
				'label_off'    => esc_html__( 'No', 'one-page-quick-checkout-for-woocommerce' ),
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'If enabled and a Product ID is set, the cart will be emptied first.', 'one-page-quick-checkout-for-woocommerce' ),
			]
		);

		$this->add_control(
			'product_id',
			[
				'label'       => esc_html__( 'Product ID', 'one-page-quick-checkout-for-woocommerce' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'e.g. 123',
				'default'     => '',
				'description' => esc_html__( 'If set, this product will be added to the cart. For variations, also set Variation ID.', 'one-page-quick-checkout-for-woocommerce' ),
			]
		);

		$this->add_control(
			'variation_id',
			[
				'label'       => esc_html__( 'Variation ID (optional)', 'one-page-quick-checkout-for-woocommerce' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'e.g. 456',
				'default'     => '',
				'condition'   => [
					'product_id!' => '',
				],
			]
		);

		$this->add_control(
			'qty',
			[
				'label'       => esc_html__( 'Quantity', 'one-page-quick-checkout-for-woocommerce' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 1,
				'step'        => 1,
				'default'     => 1,
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		// If shortcode renderer is missing, show an informative notice in the editor/front-end.
		if ( ! function_exists( 'onepaquc_display_one_page_checkout_form' ) ) {
			echo '<div class="elementor-alert elementor-alert-warning">' .
				esc_html__( 'Checkout renderer not available.', 'one-page-quick-checkout-for-woocommerce' ) .
				'</div>';
			return;
		}

		$settings = $this->get_settings_for_display();

		// Normalize + sanitize incoming settings.
		$auto_add     = ( isset( $settings['auto_add'] ) && 'yes' === $settings['auto_add'] );
		$clear_cart   = ( isset( $settings['clear_cart'] ) && 'yes' === $settings['clear_cart'] );
		$product_id   = isset( $settings['product_id'] ) ? preg_replace( '/\D+/', '', (string) $settings['product_id'] ) : '';
		$variation_id = isset( $settings['variation_id'] ) ? preg_replace( '/\D+/', '', (string) $settings['variation_id'] ) : '';
		$qty          = isset( $settings['qty'] ) && is_numeric( $settings['qty'] ) ? max( 1, (int) $settings['qty'] ) : 1;

		// Build shortcode reflecting only non-default / filled values.
		$parts = [ 'onepaquc_checkout' ];
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

		// Execute the shortcode; it handles cart ops & rendering within your existing logic.
		$html = do_shortcode( $shortcode );

		// If nothing is output (e.g. empty cart), show a clear editor-only placeholder.
		$is_editor = isset( \Elementor\Plugin::$instance )
			&& \Elementor\Plugin::$instance->editor
			&& \Elementor\Plugin::$instance->editor->is_edit_mode();

		if ( '' === trim( (string) $html ) ) {
			if ( $is_editor ) {
				$esc_sc = esc_html( $shortcode );
				echo '<div class="onepaquc-checkout-placeholder" style="border:1px dashed #c3c4c7;padding:12px;border-radius:6px;background:#fff;">' .
					'<strong>' . esc_html__( 'Checkout (Preview)', 'one-page-quick-checkout-for-woocommerce' ) . '</strong><br>' .
					esc_html__( 'The checkout form appears on the front end when the cart is not empty.', 'one-page-quick-checkout-for-woocommerce' ) .
					'<div style="margin-top:8px;padding:8px;background:#f6f7f7;border:1px solid #e0e0e0;border-radius:4px;font-family:monospace;"><em>' . $esc_sc . '</em></div>' .
					'</div>';
			}
			return;
		}

		// Safe HTML from your renderer + Woo templates.
		global $onepaquc_onepaquc_allowed_tags;
		echo wp_kses( $html, $onepaquc_onepaquc_allowed_tags );
	}
}
