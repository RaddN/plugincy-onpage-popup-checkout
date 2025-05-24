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
        
        <table class="form-table pro-only">
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Enable Trust Badges', 'one-page-quick-checkout-for-woocommerce'); ?></th>
                <td>
                    <label class="switch">
                        <input disabled type="checkbox" name="onepaquc_trust_badges_enabled" value="1"
                            <?php checked(1, get_option('onepaquc_trust_badges_enabled', 0), true); ?> />
                        <span class="slider round"></span>
                    </label>
                    <p class="description"><?php esc_html_e('Display trust signals and security badges on the checkout page.', 'one-page-quick-checkout-for-woocommerce'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php esc_html_e('Badge Position', 'one-page-quick-checkout-for-woocommerce'); ?></th>
                <td>
                    <select disabled name="onepaquc_trust_badge_position">
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
                    <select disabled name="onepaquc_trust_badge_style">
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
                                    <input disabled type="hidden" name="onepaquc_my_trust_badges_items[<?php echo esc_attr($index); ?>][enabled]" 
                                           value="<?php echo !empty($badge['enabled']) ? '1' : '0'; ?>" />
                                    
                                    <p>
                                        <label><?php esc_html_e('Icon:', 'one-page-quick-checkout-for-woocommerce'); ?></label>
                                        <select disabled name="onepaquc_my_trust_badges_items[<?php echo esc_attr($index); ?>][icon]" class="badge-icon-select">
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
                                        <input disabled type="text" name="onepaquc_my_trust_badges_items[<?php echo esc_attr($index); ?>][text]" 
                                               value="<?php echo esc_attr($badge['text']); ?>" class="regular-text" />
                                    </p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button disabled type="button" class="button button-secondary add-new-badge">
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
                            <input disabled type="checkbox" id="show-custom-html" name="show_custom_html" value="1" 
                                   <?php checked(1, !empty(get_option('onepaquc_trust_badge_custom_html')), true); ?> />
                            <?php esc_html_e('I want to use custom HTML instead', 'one-page-quick-checkout-for-woocommerce'); ?>
                        </label>
                    </p>
                    
                    <div id="custom-html-editor" style="<?php echo empty(get_option('onepaquc_trust_badge_custom_html')) ? 'display:none;' : ''; ?>">
                        <textarea disabled name="onepaquc_trust_badge_custom_html" rows="6" class="large-text code"><?php echo esc_textarea(get_option('onepaquc_trust_badge_custom_html', '')); ?></textarea>
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