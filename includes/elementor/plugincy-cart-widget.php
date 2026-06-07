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
    class onepaquc_Cart_Widget extends \Elementor\Widget_Base
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
            return esc_html__('Floating Cart', 'one-page-quick-checkout-for-woocommerce');
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
                    'label' => esc_html__('General Settings', 'one-page-quick-checkout-for-woocommerce'),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                ]
            );

            $this->add_control(
                'cart_icon',
                [
                    'label' => esc_html__('Cart Icon', 'one-page-quick-checkout-for-woocommerce'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => 'cart',
                    'options' => [
                        'cart' => esc_html__('Cart', 'one-page-quick-checkout-for-woocommerce'),
                        'shopping-bag' => esc_html__('Shopping Bag', 'one-page-quick-checkout-for-woocommerce'),
                        'basket' => esc_html__('Basket', 'one-page-quick-checkout-for-woocommerce'),
                    ],
                ]
            );

            $this->add_control(
                'drawer_position',
                [
                    'label' => esc_html__('Drawer Position', 'one-page-quick-checkout-for-woocommerce'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => 'right',
                    'options' => [
                        'left' => esc_html__('Left', 'one-page-quick-checkout-for-woocommerce'),
                        'right' => esc_html__('Right', 'one-page-quick-checkout-for-woocommerce'),
                    ],
                ]
            );

            $this->add_control(
                'product_title_tag',
                [
                    'label' => esc_html__('Product Title Tag', 'one-page-quick-checkout-for-woocommerce'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => 'h4',
                    'options' => [
                        'h1' => esc_html__('H1', 'one-page-quick-checkout-for-woocommerce'),
                        'h2' => esc_html__('H2', 'one-page-quick-checkout-for-woocommerce'),
                        'h3' => esc_html__('H3', 'one-page-quick-checkout-for-woocommerce'),
                        'h4' => esc_html__('H4', 'one-page-quick-checkout-for-woocommerce'),
                        'h5' => esc_html__('H5', 'one-page-quick-checkout-for-woocommerce'),
                        'h6' => esc_html__('H6', 'one-page-quick-checkout-for-woocommerce'),
                        'p' => esc_html__('P', 'one-page-quick-checkout-for-woocommerce'),
                        'div' => esc_html__('DIV', 'one-page-quick-checkout-for-woocommerce'),
                    ],
                ]
            );

            $this->end_controls_section();

            // Cart Icon Style Section
            $this->start_controls_section(
                'cart_icon_style_section',
                [
                    'label' => esc_html__('Cart Icon', 'one-page-quick-checkout-for-woocommerce'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'cart_icon_size',
                [
                    'label' => esc_html__('Icon Size', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Color', 'one-page-quick-checkout-for-woocommerce'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#000000',
                ]
            );

            $this->end_controls_section();

            // Drawer Style Section
            $this->start_controls_section(
                'drawer_style_section',
                [
                    'label' => esc_html__('Drawer', 'one-page-quick-checkout-for-woocommerce'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'drawer_width',
                [
                    'label' => esc_html__('Width', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Background Color', 'one-page-quick-checkout-for-woocommerce'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#ffffff',
                ]
            );

            $this->add_control(
                'drawer_padding',
                [
                    'label' => esc_html__('Padding', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Margin', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Product Image', 'one-page-quick-checkout-for-woocommerce'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'product_image_width',
                [
                    'label' => esc_html__('Width', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Height', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Product Title', 'one-page-quick-checkout-for-woocommerce'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'product_title_font_size',
                [
                    'label' => esc_html__('Font Size', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Line Height', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Color', 'one-page-quick-checkout-for-woocommerce'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#333333',
                ]
            );

            $this->end_controls_section();

            // Product Price Style Section
            $this->start_controls_section(
                'product_price_style_section',
                [
                    'label' => esc_html__('Product Price', 'one-page-quick-checkout-for-woocommerce'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'product_price_font_size',
                [
                    'label' => esc_html__('Font Size', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Line Height', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Color', 'one-page-quick-checkout-for-woocommerce'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#666666',
                ]
            );

            $this->end_controls_section();

            // Quantity Style Section
            $this->start_controls_section(
                'quantity_style_section',
                [
                    'label' => esc_html__('Quantity', 'one-page-quick-checkout-for-woocommerce'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'quantity_width',
                [
                    'label' => esc_html__('Width', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Height', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Padding', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Remove Button', 'one-page-quick-checkout-for-woocommerce'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'remove_button_size',
                [
                    'label' => esc_html__('Size', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Padding', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Subtotal', 'one-page-quick-checkout-for-woocommerce'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'subtotal_font_size',
                [
                    'label' => esc_html__('Font Size', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Line Height', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Color', 'one-page-quick-checkout-for-woocommerce'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#333333',
                ]
            );

            $this->add_control(
                'subtotal_padding',
                [
                    'label' => esc_html__('Padding', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Margin', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Checkout Button', 'one-page-quick-checkout-for-woocommerce'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'checkout_btn_background',
                [
                    'label' => esc_html__('Background Color', 'one-page-quick-checkout-for-woocommerce'),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#333333',
                ]
            );

            $this->add_control(
                'checkout_btn_font_size',
                [
                    'label' => esc_html__('Font Size', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Line Height', 'one-page-quick-checkout-for-woocommerce'),
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
                    'label' => esc_html__('Text Color', 'one-page-quick-checkout-for-woocommerce'),
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
                $shortcode .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
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
            $css = '';

            $add_size_rule = function ($selector, $property, $control, $unit = 'px') use (&$css, $settings) {
                if (empty($settings[$control]['size']) || !is_numeric($settings[$control]['size'])) {
                    return;
                }

                $value = (float) $settings[$control]['size'];
                if ($value < 0) {
                    return;
                }

                $css .= $selector . '{' . $property . ':' . esc_attr($value . $unit) . ';}';
            };

            $add_color_rule = function ($selector, $property, $control) use (&$css, $settings) {
                if (empty($settings[$control])) {
                    return;
                }

                $color = sanitize_hex_color($settings[$control]);
                if (!$color) {
                    return;
                }

                $css .= $selector . '{' . $property . ':' . esc_attr($color) . ';}';
            };

            $add_size_rule('.cart-icon', 'font-size', 'cart_icon_size');
            $add_color_rule('.cart-icon', 'color', 'cart_icon_color');

            $add_size_rule('.cart-drawer', 'width', 'drawer_width');
            $add_color_rule('.cart-drawer', 'background-color', 'drawer_background');
            $add_size_rule('.cart-drawer', 'padding', 'drawer_padding');
            $add_size_rule('.cart-drawer', 'margin', 'drawer_margin');

            $add_size_rule('.cart-item img', 'width', 'product_image_width');
            $add_size_rule('.cart-item img', 'height', 'product_image_height');

            $add_size_rule('.rmenu-cart .cart-item .item-title', 'font-size', 'product_title_font_size');
            $add_size_rule('.rmenu-cart .cart-item .item-title', 'line-height', 'product_title_line_height', '');
            $add_color_rule('.rmenu-cart .cart-item .item-title', 'color', 'product_title_color');

            $add_size_rule('.rmenu-cart .cart-item .item-price', 'font-size', 'product_price_font_size');
            $add_size_rule('.rmenu-cart .cart-item .item-price', 'line-height', 'product_price_line_height', '');
            $add_color_rule('.rmenu-cart .cart-item .item-price', 'color', 'product_price_color');

            $add_size_rule('.rmenu-cart .cart-item .quantity input', 'width', 'quantity_width');
            $add_size_rule('.rmenu-cart .cart-item .quantity input', 'height', 'quantity_height');
            $add_size_rule('.rmenu-cart .cart-item .quantity input', 'padding', 'quantity_padding');

            $add_size_rule('.cart-item .remove-item', 'font-size', 'remove_button_size');
            $add_size_rule('.cart-item .remove-item', 'padding', 'remove_button_padding');

            $add_size_rule('.cart-subtotal', 'font-size', 'subtotal_font_size');
            $add_size_rule('.cart-subtotal', 'line-height', 'subtotal_line_height', '');
            $add_color_rule('.cart-subtotal', 'color', 'subtotal_color');
            $add_size_rule('.cart-subtotal', 'padding', 'subtotal_padding');
            $add_size_rule('.cart-subtotal', 'margin', 'subtotal_margin');

            $add_color_rule('.checkout-button', 'background-color', 'checkout_btn_background');
            $add_size_rule('.checkout-button', 'font-size', 'checkout_btn_font_size');
            $add_size_rule('.checkout-button', 'line-height', 'checkout_btn_line_height', '');
            $add_color_rule('.checkout-button', 'color', 'checkout_btn_color');

            if ($css && wp_style_is('rmenu-cart-style', 'enqueued')) {
                wp_add_inline_style('rmenu-cart-style', $css);
            }
        }
    }
    // Register the widget
    \Elementor\Plugin::instance()->widgets_manager->register(new onepaquc_Cart_Widget());

}

add_action('elementor/init', 'onepaquc_plugincy_cart_elementor_widget', 100);
