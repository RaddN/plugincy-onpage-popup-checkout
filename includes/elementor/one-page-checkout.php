<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


/**
 * Plugincy One Page Checkout Elementor Widget
 */
/**
 * Check if Elementor is installed and active.
 *
 * @return bool True if Elementor is active, false otherwise.
 */
function onepaquc_is_elementor_active()
{
    return defined('ELEMENTOR_VERSION');
}

/**
 * Register the custom Elementor widget if Elementor is active.
 */
function onepaquc_plugincy_one_page_checkout_widget()
{
    if (! onepaquc_is_elementor_active() || ! class_exists('WooCommerce') || ! function_exists('wc_get_attribute_taxonomies')) {
        return;
    }
class onepaquc_One_Page_Checkout_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     */
    public function get_name() {
        return 'plugincy';
    }

    /**
     * Get widget title.
     */
    public function get_title() {
        return esc_html__('Plugincy One Page Checkout', 'one-page-quick-checkout-for-woocommerce');
    }

    /**
     * Get widget icon.
     */
    public function get_icon() {
        return 'dashicons-onepaquc_one_page_cart';
    }

    /**
     * Get custom help URL.
     */
    public function get_custom_help_url() {
        return 'https://plugincy.com/one-page-quick-checkout-for-woocommerce/';
    }

    /**
     * Get widget categories.
     */
    public function get_categories() {
        return ['plugincy'];
    }

    /**
     * Get widget keywords.
     */
    public function get_keywords() {
        return ['checkout', 'one page checkout', 'plugincy', 'woocommerce', 'products'];
    }

    /**
     * Register widget controls.
     */
    protected function register_controls() {

        // ====== GENERAL SETTINGS TAB ======
        $this->start_controls_section(
            'general_section',
            [
                'label' => esc_html__('General Settings', 'one-page-quick-checkout-for-woocommerce'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'template',
            [
                'label' => esc_html__('Display Template', 'one-page-quick-checkout-for-woocommerce'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'product-tabs',
                'options' => [
                    'product-table' => esc_html__('Product Table', 'one-page-quick-checkout-for-woocommerce'),
                    'product-list' => esc_html__('Product List', 'one-page-quick-checkout-for-woocommerce'),
                    'product-single' => esc_html__('Product Single', 'one-page-quick-checkout-for-woocommerce'),
                    'product-slider' => esc_html__('Product Slider', 'one-page-quick-checkout-for-woocommerce'),
                    'product-accordion' => esc_html__('Product Accordion', 'one-page-quick-checkout-for-woocommerce'),
                    'product-tabs' => esc_html__('Product Tabs', 'one-page-quick-checkout-for-woocommerce'),
                    'pricing-table' => esc_html__('Pricing Table', 'one-page-quick-checkout-for-woocommerce'),
                ],
                'description' => esc_html__('Choose how products will be displayed on the checkout page', 'one-page-quick-checkout-for-woocommerce'),
            ]
        );

        $this->end_controls_section();

        // ====== PRODUCT QUERY TAB ======
        $this->start_controls_section(
            'product_query_section',
            [
                'label' => esc_html__('Product Query', 'one-page-quick-checkout-for-woocommerce'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // By Product IDs
        $this->add_control(
            'product_ids_heading',
            [
                'label' => esc_html__('By Product IDs', 'one-page-quick-checkout-for-woocommerce'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'product_ids',
            [
                'label' => esc_html__('Product IDs', 'one-page-quick-checkout-for-woocommerce'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => '152,153,151,142',
                'description' => esc_html__('Enter specific product IDs separated by commas (e.g., 152,153,151,142)', 'one-page-quick-checkout-for-woocommerce'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        // By Category & Tag
        $this->add_control(
            'category_tag_heading',
            [
                'label' => esc_html__('By Category & Tag', 'one-page-quick-checkout-for-woocommerce'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'category',
            [
                'label' => esc_html__('Product Categories', 'one-page-quick-checkout-for-woocommerce'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'electronics,clothing',
                'description' => esc_html__('Enter category slugs separated by commas (e.g., electronics,clothing)', 'one-page-quick-checkout-for-woocommerce'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'tags',
            [
                'label' => esc_html__('Product Tags', 'one-page-quick-checkout-for-woocommerce'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'featured,sale',
                'description' => esc_html__('Enter tag slugs separated by commas (e.g., featured,sale)', 'one-page-quick-checkout-for-woocommerce'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        // By Attribute
        $this->add_control(
            'attribute_heading',
            [
                'label' => esc_html__('By Attribute', 'one-page-quick-checkout-for-woocommerce'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'attribute',
            [
                'label' => esc_html__('Product Attribute', 'one-page-quick-checkout-for-woocommerce'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'color',
                'description' => esc_html__('Enter attribute name (e.g., color, size, brand)', 'one-page-quick-checkout-for-woocommerce'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'terms',
            [
                'label' => esc_html__('Attribute Terms', 'one-page-quick-checkout-for-woocommerce'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'red,blue,green',
                'description' => esc_html__('Enter attribute terms separated by commas (e.g., red,blue,green)', 'one-page-quick-checkout-for-woocommerce'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->end_controls_section();

        // ====== STYLE TAB - LAYOUT & SPACING ======
        $this->start_controls_section(
            'layout_spacing_section',
            [
                'label' => esc_html__('Layout & Spacing', 'one-page-quick-checkout-for-woocommerce'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'border_radius',
            [
                'label' => esc_html__('Border Radius', 'one-page-quick-checkout-for-woocommerce'),
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
                    'size' => 4,
                ],
                'description' => esc_html__('Adjust the rounded corners of elements', 'one-page-quick-checkout-for-woocommerce'),
                'selectors' => [
                    '{{WRAPPER}} .plugincy-checkout-container' => 'border-radius: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .plugincy-checkout-container .checkout-element' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'spacing',
            [
                'label' => esc_html__('Element Spacing', 'one-page-quick-checkout-for-woocommerce'),
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
                    'size' => 15,
                ],
                'description' => esc_html__('Control the spacing between elements', 'one-page-quick-checkout-for-woocommerce'),
                'selectors' => [
                    '{{WRAPPER}} .plugincy-checkout-container .checkout-element' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .plugincy-checkout-container .checkout-element:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'box_shadow',
            [
                'label' => esc_html__('Enable Box Shadow', 'one-page-quick-checkout-for-woocommerce'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'one-page-quick-checkout-for-woocommerce'),
                'label_off' => esc_html__('No', 'one-page-quick-checkout-for-woocommerce'),
                'return_value' => 'yes',
                'default' => 'no',
                'description' => esc_html__('Add subtle shadow effects to elements', 'one-page-quick-checkout-for-woocommerce'),
                'selectors' => [
                    '{{WRAPPER}} .plugincy-checkout-container' => 'box-shadow: 0 2px 8px rgba(0,0,0,0.1);',
                ],
                'condition' => [
                    'box_shadow' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // ====== STYLE TAB - COLORS & BUTTONS ======
        $this->start_controls_section(
            'colors_buttons_section',
            [
                'label' => esc_html__('Colors & Buttons', 'one-page-quick-checkout-for-woocommerce'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'primary_color',
            [
                'label' => esc_html__('Primary Color', 'one-page-quick-checkout-for-woocommerce'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#4CAF50',
                'description' => esc_html__('Main theme color for buttons and highlights', 'one-page-quick-checkout-for-woocommerce'),
                'selectors' => [
                    '{{WRAPPER}} .plugincy-checkout-container .btn-primary' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .plugincy-checkout-container .primary-element' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .plugincy-checkout-container .primary-border' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'secondary_color',
            [
                'label' => esc_html__('Secondary Color', 'one-page-quick-checkout-for-woocommerce'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#2196F3',
                'description' => esc_html__('Accent color for secondary elements', 'one-page-quick-checkout-for-woocommerce'),
                'selectors' => [
                    '{{WRAPPER}} .plugincy-checkout-container .btn-secondary' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .plugincy-checkout-container .secondary-element' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .plugincy-checkout-container .secondary-border' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_style',
            [
                'label' => esc_html__('Button Style', 'one-page-quick-checkout-for-woocommerce'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'filled',
                'options' => [
                    'filled' => esc_html__('Filled', 'one-page-quick-checkout-for-woocommerce'),
                    'outlined' => esc_html__('Outlined', 'one-page-quick-checkout-for-woocommerce'),
                    'text' => esc_html__('Text Only', 'one-page-quick-checkout-for-woocommerce'),
                ],
                'description' => esc_html__('Choose the appearance of checkout buttons', 'one-page-quick-checkout-for-woocommerce'),
            ]
        );

        // Button Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'label' => esc_html__('Button Typography', 'one-page-quick-checkout-for-woocommerce'),
                'selector' => '{{WRAPPER}} .plugincy-checkout-container .checkout-button',
            ]
        );

        // Button Padding
        $this->add_responsive_control(
            'button_padding',
            [
                'label' => esc_html__('Button Padding', 'one-page-quick-checkout-for-woocommerce'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => 12,
                    'right' => 24,
                    'bottom' => 12,
                    'left' => 24,
                    'unit' => 'px',
                    'isLinked' => false,
                ],
                'selectors' => [
                    '{{WRAPPER}} .plugincy-checkout-container .checkout-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // ====== ADVANCED SETTINGS ======
        $this->start_controls_section(
            'advanced_section',
            [
                'label' => esc_html__('Advanced', 'one-page-quick-checkout-for-woocommerce'),
                'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
            ]
        );

        $this->add_control(
            'custom_css_class',
            [
                'label' => esc_html__('CSS Classes', 'one-page-quick-checkout-for-woocommerce'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => esc_html__('Add custom CSS classes separated by spaces', 'one-page-quick-checkout-for-woocommerce'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'custom_id',
            [
                'label' => esc_html__('CSS ID', 'one-page-quick-checkout-for-woocommerce'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => esc_html__('Add a custom CSS ID', 'one-page-quick-checkout-for-woocommerce'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'custom_css_section',
            [
                'label' => esc_html__('Plugincy Custom CSS', 'one-page-quick-checkout-for-woocommerce'),
                'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
            ]
        );
        $this->add_control(
            'custom_css',
            [
                'label' => esc_html__('Custom CSS', 'one-page-quick-checkout-for-woocommerce'),
                'type' => \Elementor\Controls_Manager::CODE,
                'language' => 'css',
                'rows' => 10,
                'description' => esc_html__('Add your custom CSS styles here', 'one-page-quick-checkout-for-woocommerce'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );
        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend.
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        // Build shortcode attributes
        $shortcode_attrs = [];
        
        if (!empty($settings['product_ids'])) {
            $shortcode_attrs[] = 'product_ids="' . esc_attr($settings['product_ids']) . '"';
        }
        
        if (!empty($settings['category'])) {
            $shortcode_attrs[] = 'category="' . esc_attr($settings['category']) . '"';
        }
        
        if (!empty($settings['tags'])) {
            $shortcode_attrs[] = 'tags="' . esc_attr($settings['tags']) . '"';
        }
        
        if (!empty($settings['attribute'])) {
            $shortcode_attrs[] = 'attribute="' . esc_attr($settings['attribute']) . '"';
        }
        
        if (!empty($settings['terms'])) {
            $shortcode_attrs[] = 'terms="' . esc_attr($settings['terms']) . '"';
        }
        
        if (!empty($settings['template'])) {
            $shortcode_attrs[] = 'template="' . esc_attr($settings['template']) . '"';
        }

        // Build the shortcode
        $shortcode = '[plugincy_one_page_checkout';
        if (!empty($shortcode_attrs)) {
            $shortcode .= ' ' . implode(' ', $shortcode_attrs);
        }
        $shortcode .= ']';

        // Container classes
        $container_classes = ['plugincy-checkout-container'];
        if (!empty($settings['custom_css_class'])) {
            $container_classes[] = esc_attr($settings['custom_css_class']);
        }
        if (!empty($settings['button_style'])) {
            $container_classes[] = 'button-style-' . esc_attr($settings['button_style']);
        }

        // Container ID
        $container_id = '';
        if (!empty($settings['custom_id'])) {
            $container_id = 'id="' . esc_attr($settings['custom_id']) . '"';
        }

        //custom_css
        if (!empty($settings['custom_css'])) {
            $container_classes[] = 'custom-css';
            add_action('wp_footer', function() use ($settings) {
                echo '<style>' . wp_strip_all_tags($settings['custom_css']) . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            });
        }

        // Output the widget
        echo '<div class="' . esc_attr(implode(' ', $container_classes)) . '" ' . $container_id . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        
        // Add inline styles for dynamic styling
        $this->render_inline_styles($settings);
        
        // Execute the shortcode
        echo do_shortcode($shortcode);
        
        echo '</div>';
    }

    /**
     * Render inline styles for dynamic styling
     */
    private function render_inline_styles($settings) {
        $styles = [];
        
        // Button styles based on button_style setting
        if (!empty($settings['button_style'])) {
            $primary_color = !empty($settings['primary_color']) ? $settings['primary_color'] : '#4CAF50';
            $secondary_color = !empty($settings['secondary_color']) ? $settings['secondary_color'] : '#2196F3';
            
            switch ($settings['button_style']) {
                case 'outlined':
                    $styles[] = '.plugincy-checkout-container .checkout-button { 
                        background: transparent !important; 
                        border: 2px solid ' . esc_attr($primary_color) . ' !important; 
                        color: ' . esc_attr($primary_color) . ' !important; 
                    }';
                    $styles[] = '.plugincy-checkout-container .checkout-button:hover { 
                        background: ' . esc_attr($primary_color) . ' !important; 
                        color: white !important; 
                    }';
                    break;
                    
                case 'text':
                    $styles[] = '.plugincy-checkout-container .checkout-button { 
                        background: transparent !important; 
                        border: none !important; 
                        color: ' . esc_attr($primary_color) . ' !important; 
                        text-decoration: underline;
                    }';
                    $styles[] = '.plugincy-checkout-container .checkout-button:hover { 
                        color: ' . esc_attr($secondary_color) . ' !important; 
                    }';
                    break;
                    
                default: // filled
                    $styles[] = '.plugincy-checkout-container .checkout-button { 
                        background: ' . esc_attr($primary_color) . ' !important; 
                        border: none !important; 
                        color: white !important; 
                    }';
                    $styles[] = '.plugincy-checkout-container .checkout-button:hover { 
                        background: ' . esc_attr($secondary_color) . ' !important; 
                    }';
                    break;
            }
        }

        // Output styles if any
        if (!empty($styles)) {
            echo '<style>' . implode(' ', $styles) . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }
}

// Register the widget
\Elementor\Plugin::instance()->widgets_manager->register(new onepaquc_One_Page_Checkout_Widget());
}

add_action('elementor/init', 'onepaquc_plugincy_one_page_checkout_widget', 100);