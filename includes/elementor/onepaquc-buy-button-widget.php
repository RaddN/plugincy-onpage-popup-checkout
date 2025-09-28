<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

/**
 * Elementor Widget: OnePaQUC – Buy Now Button
 * Renders via your existing onepaquc_button_shortcode_handler().
 */
class Plugincy_OPQC_Buy_Button_Widget extends Widget_Base {

    public function get_name() {
        return 'onepaquc_buy_button';
    }

    public function get_title() {
        return esc_html__( 'Buy Now Button', 'one-page-quick-checkout-for-woocommerce' );
    }

    public function get_icon() {
        // Use your injected dashicon class (falls back to default if not present)
        return 'dashicons-onepaquc_buy_btn';
    }

    public function get_categories() {
        // You already have this category via includes/elementor/elementor-category.php
        return [ 'plugincy' ];
    }

    public function get_keywords() {
        return [ 'buy', 'cart', 'checkout', 'button', 'one page', 'quick' ];
    }

    protected function register_controls() {

        /* --- Product --- */
        $this->start_controls_section( 'sec_product', [
            'label' => esc_html__( 'Product', 'one-page-quick-checkout-for-woocommerce' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'product_id', [
            'label'       => esc_html__( 'Product ID', 'one-page-quick-checkout-for-woocommerce' ),
            'type'        => Controls_Manager::NUMBER,
            'default'     => '',
            'min'         => 1,
            'description' => esc_html__( 'Leave empty to detect from current product/loop.', 'one-page-quick-checkout-for-woocommerce' ),
        ] );

        $this->add_control( 'variation_id', [
            'label'       => esc_html__( 'Variation ID', 'one-page-quick-checkout-for-woocommerce' ),
            'type'        => Controls_Manager::NUMBER,
            'default'     => '',
            'min'         => 1,
        ] );

        $this->add_control( 'detect_product', [
            'label'        => esc_html__( 'Detect Product from Context', 'one-page-quick-checkout-for-woocommerce' ),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'one-page-quick-checkout-for-woocommerce' ),
            'label_off'    => esc_html__( 'No', 'one-page-quick-checkout-for-woocommerce' ),
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'detect_variation', [
            'label'        => esc_html__( 'Auto-pick Variation (default / first in-stock)', 'one-page-quick-checkout-for-woocommerce' ),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'one-page-quick-checkout-for-woocommerce' ),
            'label_off'    => esc_html__( 'No', 'one-page-quick-checkout-for-woocommerce' ),
            'return_value' => 'yes',
            'default'      => '',
        ] );

        $this->end_controls_section();

        /* --- UI --- */
        $this->start_controls_section( 'sec_ui', [
            'label' => esc_html__( 'Button UI', 'one-page-quick-checkout-for-woocommerce' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'text', [
            'label'       => esc_html__( 'Text', 'one-page-quick-checkout-for-woocommerce' ),
            'type'        => Controls_Manager::TEXT,
            'placeholder' => esc_html__( 'Buy Now', 'one-page-quick-checkout-for-woocommerce' ),
            'default'     => '',
        ] );

        $this->add_control( 'qty', [
            'label'   => esc_html__( 'Quantity', 'one-page-quick-checkout-for-woocommerce' ),
            'type'    => Controls_Manager::NUMBER,
            'default' => 1,
            'min'     => 1,
        ] );

        $this->add_control( 'icon', [
            'label'   => esc_html__( 'Icon', 'one-page-quick-checkout-for-woocommerce' ),
            'type'    => Controls_Manager::SELECT,
            'default' => '',
            'options' => [
                ''          => esc_html__( '(Use plugin default)', 'one-page-quick-checkout-for-woocommerce' ),
                'none'      => esc_html__( 'None', 'one-page-quick-checkout-for-woocommerce' ),
                'cart'      => esc_html__( 'Cart', 'one-page-quick-checkout-for-woocommerce' ),
                'checkout'  => esc_html__( 'Checkout', 'one-page-quick-checkout-for-woocommerce' ),
                'arrow'     => esc_html__( 'Arrow', 'one-page-quick-checkout-for-woocommerce' ),
            ],
        ] );

        $this->add_control( 'icon_position', [
            'label'   => esc_html__( 'Icon Position', 'one-page-quick-checkout-for-woocommerce' ),
            'type'    => Controls_Manager::SELECT,
            'default' => '',
            'options' => [
                ''        => esc_html__( '(Use plugin default)', 'one-page-quick-checkout-for-woocommerce' ),
                'left'    => esc_html__( 'Left', 'one-page-quick-checkout-for-woocommerce' ),
                'right'   => esc_html__( 'Right', 'one-page-quick-checkout-for-woocommerce' ),
                'top'     => esc_html__( 'Top', 'one-page-quick-checkout-for-woocommerce' ),
                'bottom'  => esc_html__( 'Bottom', 'one-page-quick-checkout-for-woocommerce' ),
            ],
        ] );

        $this->add_control( 'class', [
            'label'       => esc_html__( 'Extra CSS Classes', 'one-page-quick-checkout-for-woocommerce' ),
            'type'        => Controls_Manager::TEXT,
            'placeholder' => 'my-btn another-class',
            'default'     => '',
        ] );

        $this->add_control( 'style', [
            'label'       => esc_html__( 'Inline Style (advanced)', 'one-page-quick-checkout-for-woocommerce' ),
            'type'        => Controls_Manager::TEXTAREA,
            'placeholder' => 'border-radius:8px; background-color:#111; color:#fff;',
            'rows'        => 2,
            'default'     => '',
        ] );

        // Optional quick style helpers (converted into inline style)
        $this->add_control( 'bg_color', [
            'label' => esc_html__( 'Background Color', 'one-page-quick-checkout-for-woocommerce' ),
            'type'  => Controls_Manager::COLOR,
        ] );

        $this->add_control( 'text_color', [
            'label' => esc_html__( 'Text Color', 'one-page-quick-checkout-for-woocommerce' ),
            'type'  => Controls_Manager::COLOR,
        ] );

        $this->add_control( 'border_color', [
            'label' => esc_html__( 'Border Color', 'one-page-quick-checkout-for-woocommerce' ),
            'type'  => Controls_Manager::COLOR,
        ] );

        $this->add_control( 'border_radius', [
            'label' => esc_html__( 'Border Radius', 'one-page-quick-checkout-for-woocommerce' ),
            'type'  => Controls_Manager::SLIDER,
            'range' => [
                'px' => [ 'min' => 0, 'max' => 48 ],
            ],
            'size_units' => [ 'px' ],
        ] );

        $this->end_controls_section();

        /* --- Behavior --- */
        $this->start_controls_section( 'sec_behavior', [
            'label' => esc_html__( 'Behavior', 'one-page-quick-checkout-for-woocommerce' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'show_for', [
            'label'       => esc_html__( 'Show For Types (comma-separated)', 'one-page-quick-checkout-for-woocommerce' ),
            'type'        => Controls_Manager::TEXT,
            'placeholder' => 'simple,variable',
            'default'     => '',
        ] );

        $this->add_control( 'force', [
            'label'        => esc_html__( 'Force Display', 'one-page-quick-checkout-for-woocommerce' ),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'one-page-quick-checkout-for-woocommerce' ),
            'label_off'    => esc_html__( 'No', 'one-page-quick-checkout-for-woocommerce' ),
            'return_value' => 'yes',
            'default'      => '',
        ] );

        $this->end_controls_section();
    }

    protected function render() {
        if ( ! function_exists( 'onepaquc_button_shortcode_handler' ) ) {
            echo '<div class="elementor-alert elementor-alert-warning">'.esc_html__( 'Renderer not available.', 'one-page-quick-checkout-for-woocommerce' ).'</div>';
            return;
        }

        $s = $this->get_settings_for_display();

        // Build inline style string from helper controls + raw "style"
        $style_parts = [];
        if ( ! empty( $s['bg_color'] ) )      $style_parts[] = 'background-color:' . sanitize_hex_color( $s['bg_color'] ) . ';';
        if ( ! empty( $s['text_color'] ) )    $style_parts[] = 'color:' . sanitize_hex_color( $s['text_color'] ) . ';';
        if ( ! empty( $s['border_color'] ) )  $style_parts[] = 'border-color:' . sanitize_hex_color( $s['border_color'] ) . ';';
        if ( ! empty( $s['border_radius']['size'] ) ) $style_parts[] = 'border-radius:' . intval( $s['border_radius']['size'] ) . 'px;';

        $style_final = '';
        if ( ! empty( $style_parts ) ) {
            $style_final .= implode( '', $style_parts );
        }
        if ( ! empty( $s['style'] ) ) {
            $clean_user = rtrim( (string) $s['style'], ';' ) . ';';
            $style_final .= $clean_user;
        }

        // Map Elementor values to shortcode atts
        $atts = [
            'product_id'       => ! empty( $s['product_id'] ) ? (string) absint( $s['product_id'] ) : '',
            'variation_id'     => ! empty( $s['variation_id'] ) ? (string) absint( $s['variation_id'] ) : '',
            'detect_product'   => ( isset( $s['detect_product'] ) && $s['detect_product'] === 'yes' ) ? '1' : '0',
            'detect_variation' => ( isset( $s['detect_variation'] ) && $s['detect_variation'] === 'yes' ) ? '1' : '0',
            'text'             => isset( $s['text'] ) ? (string) $s['text'] : '',
            'qty'              => isset( $s['qty'] ) ? (string) max( 1, absint( $s['qty'] ) ) : '1',
            'icon'             => isset( $s['icon'] ) ? (string) $s['icon'] : '',
            'icon_position'    => isset( $s['icon_position'] ) ? (string) $s['icon_position'] : '',
            'class'            => isset( $s['class'] ) ? (string) $s['class'] : '',
            'style'            => $style_final,
            'show_for'         => isset( $s['show_for'] ) ? (string) $s['show_for'] : '',
            'force'            => ( isset( $s['force'] ) && $s['force'] === 'yes' ) ? '1' : '0',
        ];

        // Render through your existing handler (echo safe HTML)
        echo onepaquc_button_shortcode_handler( $atts );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}



