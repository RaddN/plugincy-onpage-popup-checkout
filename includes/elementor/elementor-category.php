<?php

/**
 * Custom Elementor category registration.
 */

if (!defined('ABSPATH')) {
    exit;
}

class onepaquc_Elementor_Category_Alternative
{
    public function __construct()
    {
        add_action('elementor/init', [$this, 'init_category'], 1);
        add_action('elementor/editor/after_enqueue_styles', [$this, 'add_category_styles']);
    }

    private function get_svg_icon()
    {
        return '<span class="onepaquc-elementor-category-icon" aria-hidden="true"></span>';
    }

    public function add_category_styles()
    {
        $handle = 'onepaquc-elementor-category';
        $css    = 'div#elementor-panel-category-plugincy button.elementor-panel-heading.elementor-panel-category-title span.elementor-panel-heading-title{display:flex;gap:10px;align-items:center;}'
            . '.onepaquc-elementor-category-icon:before,i.dashicons-onepaquc_one_page_cart:before,i.dashicons-onepaquc_cart:before,i.dashicons-onepaquc_buy_btn:before{font-family:dashicons;content:"\f174";color:#6a3df6;font-size:22px;line-height:1;}';

        if (!wp_style_is($handle, 'registered')) {
            wp_register_style($handle, false, array(), ONEPAQUC_VERSION);
        }

        wp_enqueue_style($handle);
        wp_add_inline_style($handle, $css);
    }

    public function init_category()
    {
        add_action('elementor/elements/categories_registered', [$this, 'add_category_with_priority'], 1);
    }

    public function add_category_with_priority($elements_manager)
    {
        $elements_manager->add_category(
            'plugincy',
            [
                'title' => esc_html__('Plugincy', 'one-page-quick-checkout-for-woocommerce') . $this->get_svg_icon(),
                'icon'  => 'plugincy-icon',
            ]
        );

        $categories = $elements_manager->get_categories();
        if (!isset($categories['plugincy'])) {
            return;
        }

        $plugincy_cat = $categories['plugincy'];
        unset($categories['plugincy']);

        $reordered = [];
        $index     = 0;
        foreach ($categories as $key => $category) {
            if ($index === 1) {
                $reordered['plugincy'] = $plugincy_cat;
            }
            $reordered[$key] = $category;
            $index++;
        }

        if (!isset($reordered['plugincy'])) {
            $reordered = ['plugincy' => $plugincy_cat] + $reordered;
        }

        try {
            $reflection = new ReflectionClass($elements_manager);
            $property   = $reflection->getProperty('categories');
            $property->setAccessible(true);
            $property->setValue($elements_manager, $reordered);
        } catch (ReflectionException $e) {
            return;
        }
    }
}

new onepaquc_Elementor_Category_Alternative();

add_action('elementor/widgets/register', function ($widgets_manager) {
    require_once __DIR__ . '/onepaquc-checkout-widget.php';
    $widgets_manager->register(new \Plugincy_OPQC_Checkout_Widget());

    require_once __DIR__ . '/onepaquc-buy-button-widget.php';
    $widgets_manager->register(new \Plugincy_OPQC_Buy_Button_Widget());
}, 10);
