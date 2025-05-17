<?php
/**
 * Trust Badges Settings for One Page Quick Checkout
 */

// Don't allow direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display Trust Badges settings content
 */
function onepaquc_trust_badges_settings_content() {
    // Get saved badges or load defaults
    $badges = get_option('onepaquc_my_trust_badges_items', array(
        array(
            'icon' => 'dashicons-lock',
            'text' => 'Secure Payment',
            'enabled' => 1
        ),
        array(
            'icon' => 'dashicons-shield',
            'text' => '30-Day Money Back',
            'enabled' => 1
        ),
        array(
            'icon' => 'dashicons-privacy',
            'text' => 'Privacy Protected',
            'enabled' => 1
        )
    ));
    
    // Get available dashicons for selection
    $dashicons = array(
        'dashicons-lock' => __('Lock', 'one-page-quick-checkout-for-woocommerce'),
        'dashicons-shield' => __('Shield', 'one-page-quick-checkout-for-woocommerce'),
        'dashicons-privacy' => __('Privacy', 'one-page-quick-checkout-for-woocommerce'),
        'dashicons-cart' => __('Cart', 'one-page-quick-checkout-for-woocommerce'),
        'dashicons-credit-card' => __('Credit Card', 'one-page-quick-checkout-for-woocommerce'),
        'dashicons-yes' => __('Checkmark', 'one-page-quick-checkout-for-woocommerce'),
        'dashicons-thumbs-up' => __('Thumbs Up', 'one-page-quick-checkout-for-woocommerce'),
        'dashicons-awards' => __('Award', 'one-page-quick-checkout-for-woocommerce'),
        'dashicons-star-filled' => __('Star', 'one-page-quick-checkout-for-woocommerce'),
        'dashicons-businessman' => __('Customer', 'one-page-quick-checkout-for-woocommerce'),
        'dashicons-calculator' => __('Calculator', 'one-page-quick-checkout-for-woocommerce'),
        'dashicons-clock' => __('Clock', 'one-page-quick-checkout-for-woocommerce'),
        'dashicons-phone' => __('Phone', 'one-page-quick-checkout-for-woocommerce'),
        'dashicons-email' => __('Email', 'one-page-quick-checkout-for-woocommerce'),
        'dashicons-admin-site' => __('Website', 'one-page-quick-checkout-for-woocommerce'),
    );
    
    ?>
    <div class="trust-badges-settings-wrapper">
        <h3><?php esc_html_e('Trust Badges Configuration', 'one-page-quick-checkout-for-woocommerce'); ?></h3>
        
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Enable Trust Badges', 'one-page-quick-checkout-for-woocommerce'); ?></th>
                <td>
                    <label class="switch">
                        <input type="checkbox" name="onepaquc_trust_badges_enabled" value="1"
                            <?php checked(1, get_option('onepaquc_trust_badges_enabled', 0), true); ?> />
                        <span class="slider round"></span>
                    </label>
                    <p class="description"><?php esc_html_e('Display trust signals and security badges on the checkout page.', 'one-page-quick-checkout-for-woocommerce'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php esc_html_e('Badge Position', 'one-page-quick-checkout-for-woocommerce'); ?></th>
                <td>
                    <select name="onepaquc_trust_badge_position">
                        <option value="above_checkout" <?php selected(get_option('onepaquc_trust_badge_position', 'below_checkout'), 'above_checkout'); ?>><?php esc_html_e('Above Checkout Form', 'one-page-quick-checkout-for-woocommerce'); ?></option>
                        <option value="below_checkout" <?php selected(get_option('onepaquc_trust_badge_position', 'below_checkout'), 'below_checkout'); ?>><?php esc_html_e('Below Checkout Form', 'one-page-quick-checkout-for-woocommerce'); ?></option>
                        <option value="payment_section" <?php selected(get_option('onepaquc_trust_badge_position', 'below_checkout'), 'payment_section'); ?>><?php esc_html_e('Next to Payment Methods', 'one-page-quick-checkout-for-woocommerce'); ?></option>
                        <option value="order_summary" <?php selected(get_option('onepaquc_trust_badge_position', 'below_checkout'), 'order_summary'); ?>><?php esc_html_e('Below Order Summary', 'one-page-quick-checkout-for-woocommerce'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Badge Style', 'one-page-quick-checkout-for-woocommerce'); ?></th>
                <td>
                    <select name="onepaquc_trust_badge_style">
                        <option value="horizontal" <?php selected(get_option('onepaquc_trust_badge_style', 'horizontal'), 'horizontal'); ?>><?php esc_html_e('Horizontal Row', 'one-page-quick-checkout-for-woocommerce'); ?></option>
                        <option value="grid" <?php selected(get_option('onepaquc_trust_badge_style', 'horizontal'), 'grid'); ?>><?php esc_html_e('Grid (2 columns)', 'one-page-quick-checkout-for-woocommerce'); ?></option>
                        <option value="vertical" <?php selected(get_option('onepaquc_trust_badge_style', 'horizontal'), 'vertical'); ?>><?php esc_html_e('Vertical List', 'one-page-quick-checkout-for-woocommerce'); ?></option>
                    </select>
                </td>
            </tr>
            <?php if (!empty($badges)) : ?>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Trust Badge Items', 'one-page-quick-checkout-for-woocommerce'); ?></th>
                <td>
                    <div class="trust-badges-container">
                        <div class="badge-items-wrapper">
                            <?php foreach ($badges as $index => $badge) : ?>
                            <div class="badge-item" data-index="<?php echo esc_attr($index); ?>">
                                <div class="badge-header">
                                    <span class="badge-title"><?php echo esc_html($badge['text']); ?></span>
                                    <span class="badge-controls">
                                        <a href="#" class="badge-toggle"><?php echo !empty($badge['enabled']) ? '👁️' : '👁️‍🗨️'; ?></a>
                                        <a href="#" class="badge-remove">❌</a>
                                    </span>
                                </div>
                                <div class="badge-content">
                                    <input type="hidden" name="onepaquc_my_trust_badges_items[<?php echo esc_attr($index); ?>][enabled]" 
                                           value="<?php echo !empty($badge['enabled']) ? '1' : '0'; ?>" />
                                    
                                    <p>
                                        <label><?php esc_html_e('Icon:', 'one-page-quick-checkout-for-woocommerce'); ?></label>
                                        <select name="onepaquc_my_trust_badges_items[<?php echo esc_attr($index); ?>][icon]" class="badge-icon-select">
                                            <?php foreach ($dashicons as $icon => $name) : ?>
                                                <option value="<?php echo esc_attr($icon); ?>" <?php selected($badge['icon'], $icon); ?>>
                                                    <?php echo esc_html($name); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="preview-icon">
                                            <i class="dashicons <?php echo esc_attr($badge['icon']); ?>"></i>
                                        </span>
                                    </p>
                                    
                                    <p>
                                        <label><?php esc_html_e('Text:', 'one-page-quick-checkout-for-woocommerce'); ?></label>
                                        <input type="text" name="onepaquc_my_trust_badges_items[<?php echo esc_attr($index); ?>][text]" 
                                               value="<?php echo esc_attr($badge['text']); ?>" class="regular-text" />
                                    </p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button type="button" class="button button-secondary add-new-badge">
                            <?php esc_html_e('Add New Badge', 'one-page-quick-checkout-for-woocommerce'); ?>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
            
            <tr valign="top" class="custom-html-section">
                <th scope="row"><?php esc_html_e('Advanced: Custom HTML', 'one-page-quick-checkout-for-woocommerce'); ?></th>
                <td>
                    <p>
                        <label>
                            <input type="checkbox" id="show-custom-html" name="show_custom_html" value="1" 
                                   <?php checked(1, !empty(get_option('onepaquc_trust_badge_custom_html')), true); ?> />
                            <?php esc_html_e('I want to use custom HTML instead', 'one-page-quick-checkout-for-woocommerce'); ?>
                        </label>
                    </p>
                    
                    <div id="custom-html-editor" style="<?php echo empty(get_option('onepaquc_trust_badge_custom_html')) ? 'display:none;' : ''; ?>">
                        <textarea name="onepaquc_trust_badge_custom_html" rows="6" class="large-text code"><?php echo esc_textarea(get_option('onepaquc_trust_badge_custom_html', '')); ?></textarea>
                        <p class="description"><?php esc_html_e('Custom HTML for trust badges. You can use dashicons or include your own images.', 'one-page-quick-checkout-for-woocommerce'); ?></p>
                    </div>
                </td>
            </tr>
        </table>
        
        <div id="badge-template" style="display:none;">
            <div class="badge-item" data-index="{{index}}">
                <div class="badge-header">
                    <span class="badge-title"><?php esc_html_e('New Badge', 'one-page-quick-checkout-for-woocommerce'); ?></span>
                    <span class="badge-controls">
                        <a href="#" class="badge-toggle">👁️</a>
                        <a href="#" class="badge-remove">❌</a>
                    </span>
                </div>
                <div class="badge-content">
                    <input type="hidden" name="onepaquc_my_trust_badges_items[{{index}}][enabled]" value="1" />
                    
                    <p>
                        <label><?php esc_html_e('Icon:', 'one-page-quick-checkout-for-woocommerce'); ?></label>
                        <select name="onepaquc_my_trust_badges_items[{{index}}][icon]" class="badge-icon-select">
                            <?php foreach ($dashicons as $icon => $name) : ?>
                                <option value="<?php echo esc_attr($icon); ?>">
                                    <?php echo esc_html($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="preview-icon">
                            <i class="dashicons dashicons-shield"></i>
                        </span>
                    </p>
                    
                    <p>
                        <label><?php esc_html_e('Text:', 'one-page-quick-checkout-for-woocommerce'); ?></label>
                        <input type="text" name="onepaquc_my_trust_badges_items[{{index}}][text]" 
                               value="<?php esc_html_e('New Badge', 'one-page-quick-checkout-for-woocommerce'); ?>" class="regular-text" />
                    </p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .trust-badges-container {
            margin-bottom: 20px;
        }
        .badge-items-wrapper {
            margin-bottom: 15px;
        }
        .badge-item {
            border: 1px solid #ddd;
            background: #f9f9f9;
            margin-bottom: 10px;
            border-radius: 3px;
        }
        .badge-header {
            padding: 8px 12px;
            background: #f1f1f1;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            cursor: pointer;
        }
        .badge-content {
            padding: 12px;
        }
        .badge-controls a {
            margin-left: 10px;
            text-decoration: none;
        }
        .preview-icon {
            display: inline-block;
            margin-left: 10px;
            vertical-align: middle;
        }
        .preview-icon .dashicons {
            font-size: 20px;
            width: 20px;
            height: 20px;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Toggle badge content
        $('.badge-items-wrapper').on('click', '.badge-header', function(e) {
            if (!$(e.target).hasClass('badge-toggle') && !$(e.target).hasClass('badge-remove')) {
                $(this).next('.badge-content').slideToggle();
            }
        });
        
        // Toggle badge visibility
        $('.badge-items-wrapper').on('click', '.badge-toggle', function(e) {
            e.preventDefault();
            
            var item = $(this).closest('.badge-item');
            var enabledField = item.find('input[name*="[enabled]"]');
            
            if (enabledField.val() === '1') {
                enabledField.val('0');
                $(this).text('👁️‍🗨️');
            } else {
                enabledField.val('1');
                $(this).text('👁️');
            }
        });
        
        // Remove badge
        $('.badge-items-wrapper').on('click', '.badge-remove', function(e) {
            e.preventDefault();
            $(this).closest('.badge-item').remove();
        });
        
        // Add new badge
        $('.add-new-badge').on('click', function() {
            var template = $('#badge-template').html();
            var index = $('.badge-item').length;
            
            // Replace placeholder index with actual index
            template = template.replace(/{{index}}/g, index);
            
            $('.badge-items-wrapper').append(template);
        });
        
        // Live preview icon selection
        $('.badge-items-wrapper').on('change', '.badge-icon-select', function() {
            var iconClass = $(this).val();
            $(this).next('.preview-icon').html('<i class="dashicons ' + iconClass + '"></i>');
        });
        
        // Toggle custom HTML section
        $('#show-custom-html').on('change', function() {
            if ($(this).is(':checked')) {
                $('#custom-html-editor').slideDown();
            } else {
                $('#custom-html-editor').slideUp();
            }
        });
    });
    </script>
    <?php
}

/**
 * Render trust badges on frontend
 */
function onepaquc_display_trust_badges() {
    // Check if trust badges are enabled
    if (!get_option('onepaquc_trust_badges_enabled', 0)) {
        return;
    }
    
    // Check if we're using custom HTML
    $custom_html = get_option('onepaquc_trust_badge_custom_html', '');
    if (!empty($custom_html)) {
        echo $custom_html;
        return;
    }
    
    // Otherwise, build the badges from settings
    $badges = get_option('onepaquc_my_trust_badges_items', array());
    $style = get_option('onepaquc_trust_badge_style', 'horizontal');
    
    if (empty($badges)) {
        return;
    }
    
    echo '<div class="onepaquc-trust-badges style-' . esc_attr($style) . '">';
    
    foreach ($badges as $badge) {
        if (empty($badge['enabled'])) {
            continue;
        }
        
        echo '<div class="trust-badge">';
        if (!empty($badge['icon'])) {
            echo '<i class="dashicons ' . esc_attr($badge['icon']) . '"></i>';
        }
        if (!empty($badge['text'])) {
            echo '<span>' . esc_html($badge['text']) . '</span>';
        }
        echo '</div>';
    }
    
    echo '</div>';
}

/**
 * Add trust badges to checkout page
 */
function onepaquc_add_trust_badges_to_checkout() {
    $position = get_option('onepaquc_trust_badge_position', 'below_checkout');

    switch ($position) {
        case 'above_checkout':
            add_action('woocommerce_before_checkout_form', 'onepaquc_display_trust_badges', 10);
            break;
        case 'below_checkout':
            add_action('woocommerce_after_checkout_form', 'onepaquc_display_trust_badges', 10);
            break;
        case 'payment_section':
            add_action('woocommerce_review_order_after_payment', 'onepaquc_display_trust_badges', 10);
            break;
        case 'order_summary':
            add_action('woocommerce_checkout_after_order_review', 'onepaquc_display_trust_badges', 10);
            break;
    }
}
add_action('init', 'onepaquc_add_trust_badges_to_checkout');

/**
 * Add frontend styles for trust badges
 */
function onepaquc_trust_badges_styles() {
    if (!get_option('onepaquc_trust_badges_enabled', 0)) {
        return;
    }
    
    $style = get_option('onepaquc_trust_badge_style', 'horizontal');
    
    // Get primary color from theme or use a default
    $primary_color = '#3498db'; // Default blue
    
    // Try to get theme color if available
    if (function_exists('get_theme_mod')) {
        $theme_color = get_theme_mod('primary_color', '');
        if (!empty($theme_color)) {
            $primary_color = $theme_color;
        }
    }
    
    ?>
    <style type="text/css">
        .onepaquc-trust-badges {
            margin: 25px 0;
            display: flex;
            flex-wrap: wrap;
            <?php if ($style == 'horizontal'): ?>
            flex-direction: row;
            justify-content: space-evenly;
            align-items: stretch;
            <?php elseif ($style == 'vertical'): ?>
            flex-direction: column;
            <?php elseif ($style == 'grid'): ?>
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-gap: 15px;
            <?php endif; ?>
        }
        
        .onepaquc-trust-badges .trust-badge {
            display: flex;
            align-items: center;
            padding: 15px;
            margin: 6px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 3px solid <?php echo esc_attr($primary_color); ?>;
        }
        
        .onepaquc-trust-badges .trust-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .onepaquc-trust-badges .trust-badge .dashicons {
            font-size: 22px;
            width: 22px;
            height: 22px;
            margin-right: 12px;
            color: <?php echo esc_attr($primary_color); ?>;
            padding: 8px;
            border-radius: 50%;
            background-color: rgba(52, 152, 219, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .onepaquc-trust-badges .trust-badge span {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .onepaquc-trust-badges.style-horizontal .trust-badge {
            flex: 1;
            justify-content: center;
            text-align: center;
            min-width: 140px;
            flex-direction: column;
            padding: 20px 10px;
        }
        
        .onepaquc-trust-badges.style-horizontal .trust-badge .dashicons {
            margin-right: 0;
            margin-bottom: 10px;
            font-size: 28px;
            width: 28px;
            height: 28px;
            padding: 12px;
        }
        
        .onepaquc-trust-badges.style-vertical .trust-badge {
            width: 100%;
            margin: 8px 0;
            border-left: 4px solid <?php echo esc_attr($primary_color); ?>;
        }
        
        /* Color variations based on icon type */
        .onepaquc-trust-badges .trust-badge .dashicons-lock {
            background-color: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }
        
        .onepaquc-trust-badges .trust-badge .dashicons-shield {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }
        
        .onepaquc-trust-badges .trust-badge .dashicons-privacy {
            background-color: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
        }
        
        .onepaquc-trust-badges .trust-badge .dashicons-cart {
            background-color: rgba(230, 126, 34, 0.1);
            color: #e67e22;
        }
        
        .onepaquc-trust-badges .trust-badge .dashicons-credit-card {
            background-color: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }
        
        .onepaquc-trust-badges .trust-badge .dashicons-yes,
        .onepaquc-trust-badges .trust-badge .dashicons-thumbs-up {
            background-color: rgba(39, 174, 96, 0.1);
            color: #27ae60;
        }
        
        .onepaquc-trust-badges .trust-badge .dashicons-star-filled,
        .onepaquc-trust-badges .trust-badge .dashicons-awards {
            background-color: rgba(241, 196, 15, 0.1);
            color: #f1c40f;
        }
        
        /* Responsive adjustments */
        @media screen and (max-width: 768px) {
            .onepaquc-trust-badges.style-horizontal {
                flex-direction: column;
            }
            
            .onepaquc-trust-badges.style-grid {
                grid-template-columns: 1fr;
            }
            
            .onepaquc-trust-badges .trust-badge {
                width: 100%;
                margin: 5px 0;
                justify-content: flex-start;
                flex-direction: row;
                padding: 12px;
                text-align: left;
            }
            
            .onepaquc-trust-badges.style-horizontal .trust-badge .dashicons {
                margin-right: 10px;
                margin-bottom: 0;
            }
        }
    </style>
    <?php
}
add_action('wp_head', 'onepaquc_trust_badges_styles');