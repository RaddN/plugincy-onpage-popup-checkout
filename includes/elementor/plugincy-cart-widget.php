<?php

/**
 * Elementor Cart Widget
 * 
 * Elementor widget that displays a customizable shopping cart.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Register the custom Elementor widget if Elementor is active.
 */
function onepaquc_plugincy_cart_elementor_widget()
{
    if (! onepaquc_is_elementor_active() || ! class_exists('WooCommerce') || ! function_exists('wc_get_attribute_taxonomies')) {
        return;
    }
    class Plugincy_Cart_Widget extends \Elementor\Widget_Base
    {

        /**
         * Get widget name.
         */
        public function get_name()
        {
            return 'plugincy_cart';
        }

        /**
         * Get widget title.
         */
        public function get_title()
        {
            return esc_html__('Plugincy Cart', 'plugincy');
        }

        /**
         * Get widget icon.
         */
        public function get_icon()
        {
            return 'dashicons-onepaquc_cart';
        }

        /**
         * Get custom help URL.
         */
        public function get_custom_help_url()
        {
            return 'https://plugincy.com/one-page-quick-checkout-for-woocommerce/';
        }

        /**
         * Get widget categories.
         */
        public function get_categories()
        {
            return ['plugincy'];
        }

        /**
         * Get widget keywords.
         */
        public function get_keywords()
        {
            return ['cart', 'checkout', 'woocommerce', 'shopping'];
        }

        /**
         * Register widget controls.
         */
        protected function register_controls()
        {

            // General Settings Section
            $this->start_controls_section(
                'general_section',
                [
                    'label' => esc_html__('General Settings', 'plugincy'),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                ]
            );

            $this->add_control(
                'cart_icon',
                [
                    'label' => esc_html__('Cart Icon', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => 'cart',
                    'options' => [
                        'cart' => esc_html__('Cart', 'plugincy'),
                        'shopping-bag' => esc_html__('Shopping Bag', 'plugincy'),
                        'basket' => esc_html__('Basket', 'plugincy'),
                    ],
                ]
            );

            $this->add_control(
                'drawer_position',
                [
                    'label' => esc_html__('Drawer Position', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => 'right',
                    'options' => [
                        'left' => esc_html__('Left', 'plugincy'),
                        'right' => esc_html__('Right', 'plugincy'),
                    ],
                ]
            );

            $this->add_control(
                'product_title_tag',
                [
                    'label' => esc_html__('Product Title Tag', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => 'h4',
                    'options' => [
                        'h1' => esc_html__('H1', 'plugincy'),
                        'h2' => esc_html__('H2', 'plugincy'),
                        'h3' => esc_html__('H3', 'plugincy'),
                        'h4' => esc_html__('H4', 'plugincy'),
                        'h5' => esc_html__('H5', 'plugincy'),
                        'h6' => esc_html__('H6', 'plugincy'),
                        'p' => esc_html__('P', 'plugincy'),
                        'div' => esc_html__('DIV', 'plugincy'),
                    ],
                ]
            );

            $this->end_controls_section();

            // Cart Icon Style Section
            $this->start_controls_section(
                'cart_icon_style_section',
                [
                    'label' => esc_html__('Cart Icon', 'plugincy'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'cart_icon_size',
                [
                    'label' => esc_html__('Icon Size', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 12,
                            'max' => 50,
                            'step' => 1,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 24,
                    ],
                ]
            );

            $this->add_control(
                'cart_icon_color',
                [
                    'label' => esc_html__('Color', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#000000',
                ]
            );

            $this->end_controls_section();

            // Drawer Style Section
            $this->start_controls_section(
                'drawer_style_section',
                [
                    'label' => esc_html__('Drawer', 'plugincy'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'drawer_width',
                [
                    'label' => esc_html__('Width', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 200,
                            'max' => 800,
                            'step' => 1,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 400,
                    ],
                ]
            );

            $this->add_control(
                'drawer_background',
                [
                    'label' => esc_html__('Background Color', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#ffffff',
                ]
            );

            $this->add_control(
                'drawer_padding',
                [
                    'label' => esc_html__('Padding', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 50,
                            'step' => 1,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 20,
                    ],
                ]
            );

            $this->add_control(
                'drawer_margin',
                [
                    'label' => esc_html__('Margin', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 50,
                            'step' => 1,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 0,
                    ],
                ]
            );

            $this->end_controls_section();

            // Product Image Style Section
            $this->start_controls_section(
                'product_image_style_section',
                [
                    'label' => esc_html__('Product Image', 'plugincy'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'product_image_width',
                [
                    'label' => esc_html__('Width', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 40,
                            'max' => 200,
                            'step' => 1,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 80,
                    ],
                ]
            );

            $this->add_control(
                'product_image_height',
                [
                    'label' => esc_html__('Height', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 40,
                            'max' => 200,
                            'step' => 1,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 80,
                    ],
                ]
            );

            $this->end_controls_section();

            // Product Title Style Section
            $this->start_controls_section(
                'product_title_style_section',
                [
                    'label' => esc_html__('Product Title', 'plugincy'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'product_title_font_size',
                [
                    'label' => esc_html__('Font Size', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 10,
                            'max' => 30,
                            'step' => 1,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 16,
                    ],
                ]
            );

            $this->add_control(
                'product_title_line_height',
                [
                    'label' => esc_html__('Line Height', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'range' => [
                        'px' => [
                            'min' => 1,
                            'max' => 3,
                            'step' => 0.1,
                        ],
                    ],
                    'default' => [
                        'size' => 1.5,
                    ],
                ]
            );

            $this->add_control(
                'product_title_color',
                [
                    'label' => esc_html__('Color', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#333333',
                ]
            );

            $this->end_controls_section();

            // Product Price Style Section
            $this->start_controls_section(
                'product_price_style_section',
                [
                    'label' => esc_html__('Product Price', 'plugincy'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'product_price_font_size',
                [
                    'label' => esc_html__('Font Size', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 10,
                            'max' => 30,
                            'step' => 1,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 14,
                    ],
                ]
            );

            $this->add_control(
                'product_price_line_height',
                [
                    'label' => esc_html__('Line Height', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'range' => [
                        'px' => [
                            'min' => 1,
                            'max' => 3,
                            'step' => 0.1,
                        ],
                    ],
                    'default' => [
                        'size' => 1.4,
                    ],
                ]
            );

            $this->add_control(
                'product_price_color',
                [
                    'label' => esc_html__('Color', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#666666',
                ]
            );

            $this->end_controls_section();

            // Quantity Style Section
            $this->start_controls_section(
                'quantity_style_section',
                [
                    'label' => esc_html__('Quantity', 'plugincy'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'quantity_width',
                [
                    'label' => esc_html__('Width', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 40,
                            'max' => 150,
                            'step' => 1,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 80,
                    ],
                ]
            );

            $this->add_control(
                'quantity_height',
                [
                    'label' => esc_html__('Height', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 20,
                            'max' => 80,
                            'step' => 1,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 40,
                    ],
                ]
            );

            $this->add_control(
                'quantity_padding',
                [
                    'label' => esc_html__('Padding', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 20,
                            'step' => 1,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 5,
                    ],
                ]
            );

            $this->end_controls_section();

            // Remove Button Style Section
            $this->start_controls_section(
                'remove_button_style_section',
                [
                    'label' => esc_html__('Remove Button', 'plugincy'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'remove_button_size',
                [
                    'label' => esc_html__('Size', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 10,
                            'max' => 30,
                            'step' => 1,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 16,
                    ],
                ]
            );

            $this->add_control(
                'remove_button_padding',
                [
                    'label' => esc_html__('Padding', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 20,
                            'step' => 1,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 5,
                    ],
                ]
            );

            $this->end_controls_section();

            // Subtotal Style Section
            $this->start_controls_section(
                'subtotal_style_section',
                [
                    'label' => esc_html__('Subtotal', 'plugincy'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'subtotal_font_size',
                [
                    'label' => esc_html__('Font Size', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 12,
                            'max' => 36,
                            'step' => 1,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 18,
                    ],
                ]
            );

            $this->add_control(
                'subtotal_line_height',
                [
                    'label' => esc_html__('Line Height', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'range' => [
                        'px' => [
                            'min' => 1,
                            'max' => 3,
                            'step' => 0.1,
                        ],
                    ],
                    'default' => [
                        'size' => 1.5,
                    ],
                ]
            );

            $this->add_control(
                'subtotal_color',
                [
                    'label' => esc_html__('Color', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#333333',
                ]
            );

            $this->add_control(
                'subtotal_padding',
                [
                    'label' => esc_html__('Padding', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 40,
                            'step' => 1,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 10,
                    ],
                ]
            );

            $this->add_control(
                'subtotal_margin',
                [
                    'label' => esc_html__('Margin', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 40,
                            'step' => 1,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 20,
                    ],
                ]
            );

            $this->end_controls_section();

            // Checkout Button Style Section
            $this->start_controls_section(
                'checkout_button_style_section',
                [
                    'label' => esc_html__('Checkout Button', 'plugincy'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'checkout_btn_background',
                [
                    'label' => esc_html__('Background Color', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#333333',
                ]
            );

            $this->add_control(
                'checkout_btn_font_size',
                [
                    'label' => esc_html__('Font Size', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px'],
                    'range' => [
                        'px' => [
                            'min' => 12,
                            'max' => 24,
                            'step' => 1,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 16,
                    ],
                ]
            );

            $this->add_control(
                'checkout_btn_line_height',
                [
                    'label' => esc_html__('Line Height', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'range' => [
                        'px' => [
                            'min' => 1,
                            'max' => 3,
                            'step' => 0.1,
                        ],
                    ],
                    'default' => [
                        'size' => 1.5,
                    ],
                ]
            );

            $this->add_control(
                'checkout_btn_color',
                [
                    'label' => esc_html__('Text Color', 'plugincy'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#ffffff',
                ]
            );

            $this->end_controls_section();
        }

        /**
         * Render widget output on the frontend.
         */
        protected function render()
        {
            $settings = $this->get_settings_for_display();

            // Build shortcode attributes
            $shortcode_atts = [
                'drawer' => $settings['drawer_position'],
                'cart_icon' => $settings['cart_icon'],
                'product_title_tag' => $settings['product_title_tag']
            ];

            // Generate shortcode
            $shortcode = '[plugincy_cart';
            foreach ($shortcode_atts as $key => $value) {
                $shortcode .= ' ' . $key . '="' . esc_attr($value) . '"';
            }
            $shortcode .= ']';

            // Generate CSS styles
            $this->generate_styles($settings);

            // Output the shortcode
            echo do_shortcode($shortcode);
        }

        /**
         * Generate and output CSS styles based on settings
         */
        private function generate_styles($settings)
        {
            $css = '<style>';

            // Cart Icon Styles
            if (!empty($settings['cart_icon_size']['size'])) {
                $css .= '.cart-icon { font-size: ' . $settings['cart_icon_size']['size'] . 'px; }';
            }
            if (!empty($settings['cart_icon_color'])) {
                $css .= '.cart-icon { color: ' . $settings['cart_icon_color'] . '; }';
            }

            // Drawer Styles
            if (!empty($settings['drawer_width']['size'])) {
                $css .= '.cart-drawer { width: ' . $settings['drawer_width']['size'] . 'px; }';
            }
            if (!empty($settings['drawer_background'])) {
                $css .= '.cart-drawer { background-color: ' . $settings['drawer_background'] . '; }';
            }
            if (!empty($settings['drawer_padding']['size'])) {
                $css .= '.cart-drawer { padding: ' . $settings['drawer_padding']['size'] . 'px; }';
            }
            if (!empty($settings['drawer_margin']['size'])) {
                $css .= '.cart-drawer { margin: ' . $settings['drawer_margin']['size'] . 'px; }';
            }

            // Product Image Styles
            if (!empty($settings['product_image_width']['size'])) {
                $css .= '.cart-item img { width: ' . $settings['product_image_width']['size'] . 'px; }';
            }
            if (!empty($settings['product_image_height']['size'])) {
                $css .= '.cart-item img { height: ' . $settings['product_image_height']['size'] . 'px; }';
            }

            // Product Title Styles
            if (!empty($settings['product_title_font_size']['size'])) {
                $css .= '.rmenu-cart .cart-item .item-title { font-size: ' . $settings['product_title_font_size']['size'] . 'px; }';
            }
            if (!empty($settings['product_title_line_height']['size'])) {
                $css .= '.rmenu-cart .cart-item .item-title { line-height: ' . $settings['product_title_line_height']['size'] . '; }';
            }
            if (!empty($settings['product_title_color'])) {
                $css .= '.rmenu-cart .cart-item .item-title { color: ' . $settings['product_title_color'] . '; }';
            }

            // Product Price Styles
            if (!empty($settings['product_price_font_size']['size'])) {
                $css .= '.rmenu-cart .cart-item .item-price { font-size: ' . $settings['product_price_font_size']['size'] . 'px; }';
            }
            if (!empty($settings['product_price_line_height']['size'])) {
                $css .= '.rmenu-cart .cart-item .item-price { line-height: ' . $settings['product_price_line_height']['size'] . '; }';
            }
            if (!empty($settings['product_price_color'])) {
                $css .= '.rmenu-cart .cart-item .item-price { color: ' . $settings['product_price_color'] . '; }';
            }

            // Quantity Styles
            if (!empty($settings['quantity_width']['size'])) {
                $css .= '.rmenu-cart .cart-item .quantity input { width: ' . $settings['quantity_width']['size'] . 'px; }';
            }
            if (!empty($settings['quantity_height']['size'])) {
                $css .= '.rmenu-cart .cart-item .quantity input { height: ' . $settings['quantity_height']['size'] . 'px; }';
            }
            if (!empty($settings['quantity_padding']['size'])) {
                $css .= '.rmenu-cart .cart-item .quantity input { padding: ' . $settings['quantity_padding']['size'] . 'px; }';
            }

            // Remove Button Styles
            if (!empty($settings['remove_button_size']['size'])) {
                $css .= '.cart-item .remove-item { font-size: ' . $settings['remove_button_size']['size'] . 'px; }';
            }
            if (!empty($settings['remove_button_padding']['size'])) {
                $css .= '.cart-item .remove-item { padding: ' . $settings['remove_button_padding']['size'] . 'px; }';
            }

            // Subtotal Styles
            if (!empty($settings['subtotal_font_size']['size'])) {
                $css .= '.cart-subtotal { font-size: ' . $settings['subtotal_font_size']['size'] . 'px; }';
            }
            if (!empty($settings['subtotal_line_height']['size'])) {
                $css .= '.cart-subtotal { line-height: ' . $settings['subtotal_line_height']['size'] . '; }';
            }
            if (!empty($settings['subtotal_color'])) {
                $css .= '.cart-subtotal { color: ' . $settings['subtotal_color'] . '; }';
            }
            if (!empty($settings['subtotal_padding']['size'])) {
                $css .= '.cart-subtotal { padding: ' . $settings['subtotal_padding']['size'] . 'px; }';
            }
            if (!empty($settings['subtotal_margin']['size'])) {
                $css .= '.cart-subtotal { margin: ' . $settings['subtotal_margin']['size'] . 'px; }';
            }

            // Checkout Button Styles
            if (!empty($settings['checkout_btn_background'])) {
                $css .= '.checkout-button { background-color: ' . $settings['checkout_btn_background'] . '; }';
            }
            if (!empty($settings['checkout_btn_font_size']['size'])) {
                $css .= '.checkout-button { font-size: ' . $settings['checkout_btn_font_size']['size'] . 'px; }';
            }
            if (!empty($settings['checkout_btn_line_height']['size'])) {
                $css .= '.checkout-button { line-height: ' . $settings['checkout_btn_line_height']['size'] . '; }';
            }
            if (!empty($settings['checkout_btn_color'])) {
                $css .= '.checkout-button { color: ' . $settings['checkout_btn_color'] . '; }';
            }

            $css .= '</style>';

            echo $css;
        }
    }
    // Register the widget
    \Elementor\Plugin::instance()->widgets_manager->register(new Plugincy_Cart_Widget());

}

add_action('elementor/init', 'onepaquc_plugincy_cart_elementor_widget', 10);
