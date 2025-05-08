<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly


// Admin Menu
add_action('admin_menu', 'onepaquc_cart_menu');


function onepaquc_cart_menu()
{

    add_menu_page(
        'Onpage Checkout',
        'Onpage Checkout',
        'manage_options',
        'onepaquc_cart',
        'onepaquc_cart_dashboard',
        'dashicons-cart', // Shopping cart icon
        '55.50'
    );
    add_submenu_page(
        'onepaquc_cart',
        'Documentation',
        'Documentation',
        'manage_options',
        'onepaquc_cart_documentation',
        'onepaquc_cart_documentation'
    );
    if (get_option('onepaquc_validity_days') !== "0") {
        // add_submenu_page('bd-affiliate-marketing', 'Manage Posts', 'Manage Posts', 'manage_options', 'bd-manage-posts', 'onepaquc_marketing_manage_posts');
        // add_submenu_page('bd-affiliate-marketing', 'Send Notification', 'Send Notification', 'manage_options', 'bd-send-notification', 'onepaquc_marketing_send_notification');
    }
}

// Display the form for Side Cart and PopUp settings
function onepaquc_cart_text_change_form($textvariable)
{


    echo '<div class="d-flex">';

    foreach (array_chunk($textvariable, 4, true) as $column) {
        echo '<div>';

        foreach ($column as $name => $label) {
            $value = esc_attr(get_option($name, ''));
?>
            <label>
                <p><?php echo esc_html($label); ?></p>
                <input type="text" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" />
            </label>
    <?php
        }

        echo '</div>';
    }

    echo '</div>';
}

// Dashboard page
function onepaquc_cart_dashboard()
{
    global $onepaquc_checkoutformfields,
        $onepaquc_productpageformfields;
    ?>

    <div class="welcome-banner">
        <h1>Welcome to Onpage Checkout <span class="version-tag">v1.0.1</span></h1>
        <p>Thank you for installing Onpage Checkout! Streamline your WooCommerce checkout process and boost your conversion rates with our easy-to-configure solution.</p>
        <p>Get started by configuring your settings below or explore our quick setup guide.</p>

        <div class="feature-grid">
            <div class="feature-item">
                <h3>Fast Setup</h3>
                <p>Configure your checkout in minutes with our intuitive options.</p>
            </div>
            <div class="feature-item">
                <h3>Mobile Friendly</h3>
                <p>Responsive design works perfectly on all devices.</p>
            </div>
            <div class="feature-item">
                <h3>Payment Gateways</h3>
                <p>Supports all major payment processors.</p>
            </div>
        </div>

        <div class="button-row">
            <a href="/wp-admin/admin.php?page=onepaquc_cart_documentation" class="button">View Documentation</a>
            <a href="https://plugincy.com/support" target="_blank" class="button button-secondary">Get Support</a>
        </div>
    </div>

    <h1 style="padding-top: 3rem;">Dashboard</h1>
    <?php
    // if (get_option('onepaquc_validity_days') === "0" || !get_option('onepaquc_api_key')) {
    //     echo "<p style='color:red;'>To use the plugin please active your API key first.</p>";
    // } else { 
    ?>
    <div class="tab-container">
        <div class="tabs">
            <div class="tab active" data-tab="1">Checkout Form Manage</div>
            <div class="tab" data-tab="2">One Page Checkout</div>
            <div class="tab" data-tab="3">Text Manage</div>
            <div class="tab" data-tab="4">Direct Checkout Manage</div>
            <div class="tab" data-tab="6">Advanced Settings (Coming Soon)</div>
            <div class="tab" data-tab="7">Quick View (Coming Soon)</div>
            <div class="tab" data-tab="5">Features</div>

        </div>
        <form method="post" action="options.php">
            <!-- Add nonce field for security -->
            <?php wp_nonce_field('onepaquc_cart_settings'); ?>
            <?php settings_fields('onepaquc_cart_settings'); ?>
            <div class="tab-content active" id="tab-1">
                <h2>Checkout Form Manage</h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Remove checkout fields</th>
                        <td>
                            <!-- multiple select options -->
                            <select class="remove_checkout_fields chosen_select select2-hidden-accessible enhanced" name="onepaquc_checkout_fields[]" id="qlwcdc_remove_checkout_fields" multiple>
                                <?php
                                global $onepaquc_rcheckoutformfields;
                                $selected_fields = get_option('onepaquc_checkout_fields', []) ?? [];
                                foreach ($onepaquc_rcheckoutformfields as $key => $field) {
                                    echo '<option value="' . esc_attr($key) . '" ' . (is_array($selected_fields) && in_array($key, $selected_fields) ? 'selected' : '') . '>' . esc_html($field['title']) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <?php foreach (onepaquc_rmenu_fields() as $key => $field) : ?>
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html($field['title']); ?></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" name="<?php echo esc_attr($key); ?>" value="1" <?php checked(1, get_option($key), true); ?> />
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <hr />
                <h3>Heading Manage</h3>
                <table class="form-table">
                    <?php foreach (onepaquc_onpcheckout_heading() as $key => $field) : ?>
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html($field['title']); ?></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" name="<?php echo esc_attr($key); ?>" value="1" <?php checked(1, get_option($key), true); ?> />
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <div class="tab-content" id="tab-2">
                <!-- Tooltip CSS -->
                <style>
                    .tooltip {
                        position: relative;
                        display: inline-block;
                        cursor: help;
                        margin-left: 5px;
                    }

                    .tooltip .question-mark {
                        display: inline-block;
                        width: 16px;
                        height: 16px;
                        line-height: 16px;
                        text-align: center;
                        background: #f0f0f0;
                        color: #555;
                        border-radius: 50%;
                        font-size: 12px;
                        font-weight: bold;
                    }

                    .tooltip .tooltip-text {
                        visibility: hidden;
                        width: 250px;
                        background-color: #555;
                        color: #fff;
                        text-align: left;
                        border-radius: 4px;
                        padding: 8px;
                        position: absolute;
                        z-index: 1;
                        bottom: 125%;
                        left: 50%;
                        margin-left: -125px;
                        opacity: 0;
                        transition: opacity 0.3s;
                        font-weight: normal;
                        font-size: 12px;
                        line-height: 1.4;
                    }

                    .tooltip:hover .tooltip-text {
                        visibility: visible;
                        opacity: 1;
                    }
                </style>

                <h2>One Page Checkout in Single Product</h2>
                <p class="description">Configure one-page checkout for individual product pages. Enable one-page checkout for specific products from the WooCommerce product edit screen.</p>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Form Position</th>
                        <td>
                            <input type="number" name="onpage_checkout_position" value="<?php echo esc_attr(get_option("onpage_checkout_position", '9')); ?>" />
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Set the priority of the one-page checkout form hook (lower numbers = earlier appearance).</span>
                            </span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Empty Cart On page load</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="onpage_checkout_cart_empty" value="1" <?php checked(1, get_option("onpage_checkout_cart_empty"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Clear existing cart items when the one-page checkout product page loads.</span>
                            </span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Add to Cart On page load</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="onpage_checkout_cart_add" value="1" <?php checked(1, get_option("onpage_checkout_cart_add"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Automatically add the product to cart when the page loads.</span>
                            </span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Hide Add to cart</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="onpage_checkout_hide_cart_button" value="1" <?php checked(1, get_option("onpage_checkout_hide_cart_button"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Hide the regular Add to Cart button on one-page checkout product pages.</span>
                            </span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Enable for All Products</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="onpage_checkout_enable_all" value="1" <?php checked(1, get_option("onpage_checkout_enable_all"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable one-page checkout for all products without individual selection.</span>
                            </span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Checkout Layout (Coming Soon)</th>
                        <td>
                            <select name="onpage_checkout_layout">
                                <option value="two_column" <?php selected(get_option('onpage_checkout_layout', 'two_column'), 'two_column'); ?>>Two Columns (Product & Checkout)</option>
                                <option value="one_column" <?php selected(get_option('onpage_checkout_layout', 'two_column'), 'one_column'); ?>>One Column (Stacked)</option>
                                <option value="product_first" <?php selected(get_option('onpage_checkout_layout', 'two_column'), 'product_first'); ?>>Product First, Then Checkout</option>
                            </select>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Select the layout for the one-page checkout form.</span>
                            </span>
                        </td>
                    </tr>
                </table>
                <hr />

                <h2>Multi-product One Page Checkout <span class="tooltip">
                        <span class="question-mark">?</span>
                        <span class="tooltip-text">Configure settings for the multi-product one-page checkout shortcode. Use: [plugincy_one_page_checkout product_ids="152,153,151,142" template="product-tabs"]</span>
                    </span></h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Empty Cart On page load</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="onpage_checkout_widget_cart_empty" value="1" <?php checked(1, get_option("onpage_checkout_widget_cart_empty", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Clear existing cart items when a multi-product one-page checkout page loads.</span>
                            </span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Add to Cart On page load</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="onpage_checkout_widget_cart_add" value="1" <?php checked(1, get_option("onpage_checkout_widget_cart_add", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Automatically add the first product to cart when the page loads.</span>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="tab-content" id="tab-3">
                <div class="d-flex space-between">
                    <h2>Checkout Form Text</h2> <button id="reset-defaults" class="button button-primary" style="background:red;">Reset Default</button>
                </div>
                <?php
                onepaquc_cart_text_change_form($onepaquc_checkoutformfields);

                ?>
                <div class="d-flex space-between">
                    <h2>Archive & Single Product Page Text</h2>
                </div>
                <?php
                onepaquc_cart_text_change_form($onepaquc_productpageformfields);

                ?>
            </div>
            <div class="tab-content" id="tab-4">
                <div class="rmenu-settings-header">
                    <h2>WooCommerce Direct Checkout</h2>
                    <p class="rmenu-settings-description">Configure how the quick checkout functionality works with your WooCommerce store.</p>
                </div>

                <div class="rmenu-settings-section">
                    <div class="rmenu-settings-section-header">
                        <h3><span class="dashicons dashicons-admin-generic"></span> General Settings</h3>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">Enable Direct Checkout</label>
                            <div class="rmenu-settings-control">
                                <label class="rmenu-toggle-switch">
                                    <input type="checkbox" name="rmenu_add_direct_checkout_button" value="1" <?php checked(1, get_option("rmenu_add_direct_checkout_button", 0), true); ?> />
                                    <span class="rmenu-toggle-slider"></span>
                                </label>
                                <p class="rmenu-field-description">Enable or disable the direct checkout functionality across your WooCommerce store.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">Button Text</label>
                            <div class="rmenu-settings-control">
                                <input type="text" name="txt-direct-checkout" value="<?php echo esc_attr(get_option('txt-direct-checkout', 'Quick Checkout')); ?>" class="regular-text" />
                                <p class="rmenu-field-description">Customize the text displayed on the direct checkout button.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">Button Position</label>
                            <div class="rmenu-settings-control">
                                <select name="rmenu_wc_direct_checkout_position" class="rmenu-select">
                                    <option value="after_add_to_cart" <?php selected(get_option('rmenu_wc_direct_checkout_position', 'after_add_to_cart'), 'after_add_to_cart'); ?>>After Add to Cart Button</option>
                                    <option value="before_add_to_cart" <?php selected(get_option('rmenu_wc_direct_checkout_position', 'after_add_to_cart'), 'before_add_to_cart'); ?>>Before Add to Cart Button</option>
                                    <option value="replace_add_to_cart" <?php selected(get_option('rmenu_wc_direct_checkout_position', 'after_add_to_cart'), 'replace_add_to_cart'); ?>>Replace Add to Cart Button</option>
                                </select>
                                <p class="rmenu-field-description">Choose where to display the direct checkout button on product pages.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rmenu-settings-section">
                    <div class="rmenu-settings-section-header">
                        <h3><span class="dashicons dashicons-cart"></span> Quick Checkout Behavior</h3>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">Checkout Method</label>
                            <div class="rmenu-settings-control">
                                <select name="rmenu_wc_checkout_method" class="rmenu-select" id="rmenu-checkout-method">
                                    <option value="direct_checkout" <?php selected(get_option('rmenu_wc_checkout_method', 'popup_checkout'), 'direct_checkout'); ?>>Redirect to Checkout</option>
                                    <option value="ajax_add" <?php selected(get_option('rmenu_wc_checkout_method', 'popup_checkout'), 'ajax_add'); ?>>AJAX Add to Cart</option>
                                    <option value="cart_redirect" <?php selected(get_option('rmenu_wc_checkout_method', 'popup_checkout'), 'cart_redirect'); ?>>Redirect to Cart Page</option>
                                    <option value="popup_checkout" <?php selected(get_option('rmenu_wc_checkout_method', 'popup_checkout'), 'popup_checkout'); ?>>Popup Checkout</option>
                                    <option value="side_cart" <?php selected(get_option('rmenu_wc_checkout_method', 'popup_checkout'), 'side_cart'); ?>>Side Cart Slide-in</option>
                                </select>
                                <p class="rmenu-field-description">Choose how the quick checkout process should behave when a customer clicks the button.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">Clear Cart Before Adding</label>
                            <div class="rmenu-settings-control">
                                <label class="rmenu-toggle-switch">
                                    <input type="checkbox" name="rmenu_wc_clear_cart" value="1" <?php checked(1, get_option("rmenu_wc_clear_cart", 0), true); ?> />
                                    <span class="rmenu-toggle-slider"></span>
                                </label>
                                <p class="rmenu-field-description">When enabled, the cart will be emptied before adding the new product. This creates a single-product checkout experience.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">One-Click Purchase (Coming Soon)</label>
                            <div class="rmenu-settings-control">
                                <label class="rmenu-toggle-switch">
                                    <input type="checkbox" name="rmenu_wc_one_click_purchase" value="1" <?php checked(1, get_option("rmenu_wc_one_click_purchase", 0), true); ?> />
                                    <span class="rmenu-toggle-slider"></span>
                                </label>
                                <p class="rmenu-field-description">When enabled, returning customers can bypass the checkout form and use their last saved payment method. Requires WooCommerce Payments or compatible gateway.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">Show Confirmation Dialog</label>
                            <div class="rmenu-settings-control">
                                <label class="rmenu-toggle-switch">
                                    <input type="checkbox" name="rmenu_wc_add_confirmation" value="1" <?php checked(1, get_option("rmenu_wc_add_confirmation", 0), true); ?> />
                                    <span class="rmenu-toggle-slider"></span>
                                </label>
                                <p class="rmenu-field-description">When enabled, customers will see a confirmation dialog before proceeding to checkout.</p>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="rmenu-settings-section">
                    <div class="rmenu-settings-section-header">
                        <h3><span class="dashicons dashicons-visibility"></span> Display Settings</h3>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">Product Types</label>
                            <?php $product_types_option = get_option('rmenu_show_quick_checkout_by_types', []); ?>
                            <div class="rmenu-settings-control rmenu-checkbox-group">
                                <label class="rmenu-checkbox-container">
                                    <input type="checkbox" name="rmenu_show_quick_checkout_by_types[]" value="simple" <?php checked(in_array('simple', $product_types_option)); ?> />
                                    <span class="rmenu-checkbox-label">Simple Products</span>
                                </label>

                                <label class="rmenu-checkbox-container">
                                    <input type="checkbox" name="rmenu_show_quick_checkout_by_types[]" value="variable" <?php checked(in_array('variable', $product_types_option)); ?> />
                                    <span class="rmenu-checkbox-label">Variable Products</span>
                                </label>

                                <label class="rmenu-checkbox-container">
                                    <input type="checkbox" name="rmenu_show_quick_checkout_by_types[]" value="grouped" <?php checked(in_array('grouped', $product_types_option)); ?> />
                                    <span class="rmenu-checkbox-label">Grouped Products</span>
                                </label>

                                <label class="rmenu-checkbox-container">
                                    <input type="checkbox" name="rmenu_show_quick_checkout_by_types[]" value="external" <?php checked(in_array('external', $product_types_option)); ?> />
                                    <span class="rmenu-checkbox-label">External/Affiliate Products</span>
                                </label>

                                <p class="rmenu-field-description">Select which WooCommerce product types should display the direct checkout button.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <?php $product_types_option = get_option('rmenu_show_quick_checkout_by_page', []); ?>
                            <div class="rmenu-settings-control rmenu-checkbox-group">
                                <div class="rmenu-checkbox-column">
                                    <h4>Product Pages</h4>
                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_show_quick_checkout_by_page[]" value="single" <?php checked(in_array('single', $product_types_option)); ?> />
                                        <span class="rmenu-checkbox-label">Single Product Pages</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_show_quick_checkout_by_page[]" value="related" <?php checked(in_array('related', $product_types_option)); ?> />
                                        <span class="rmenu-checkbox-label">Related Products</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_show_quick_checkout_by_page[]" value="upsells" <?php checked(in_array('upsells', $product_types_option)); ?> />
                                        <span class="rmenu-checkbox-label">Upsells</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_show_quick_checkout_by_page[]" value="cross-sells" <?php checked(in_array('cross-sells', $product_types_option)); ?> />
                                        <span class="rmenu-checkbox-label">Cross-sells</span>
                                    </label>
                                </div>

                                <div class="rmenu-checkbox-column">
                                    <h4>Archive Pages</h4>
                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_show_quick_checkout_by_page[]" value="shop-page" <?php checked(in_array('shop-page', $product_types_option)); ?> />
                                        <span class="rmenu-checkbox-label">Main Shop Page</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_show_quick_checkout_by_page[]" value="category-archives" <?php checked(in_array('category-archives', $product_types_option)); ?> />
                                        <span class="rmenu-checkbox-label">Product Category Archives</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_show_quick_checkout_by_page[]" value="tag-archives" <?php checked(in_array('tag-archives', $product_types_option)); ?> />
                                        <span class="rmenu-checkbox-label">Product Tag Archives</span>
                                    </label>
                                </div>

                                <div class="rmenu-checkbox-column">
                                    <h4>Other Pages</h4>
                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_show_quick_checkout_by_page[]" value="search" <?php checked(in_array('search', $product_types_option)); ?> />
                                        <span class="rmenu-checkbox-label">Search Results</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_show_quick_checkout_by_page[]" value="featured-products" <?php checked(in_array('featured-products', $product_types_option)); ?> />
                                        <span class="rmenu-checkbox-label">Featured Products</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_show_quick_checkout_by_page[]" value="on-sale" <?php checked(in_array('on-sale', $product_types_option)); ?> />
                                        <span class="rmenu-checkbox-label">On-Sale Products</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_show_quick_checkout_by_page[]" value="recent" <?php checked(in_array('recent', $product_types_option)); ?> />
                                        <span class="rmenu-checkbox-label">Recent Products</span>
                                    </label>
                                </div>

                                <div class="rmenu-checkbox-column">
                                    <h4>Widgets & Shortcodes</h4>
                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_show_quick_checkout_by_page[]" value="widgets" <?php checked(in_array('widgets', $product_types_option)); ?> />
                                        <span class="rmenu-checkbox-label">Widgets</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_show_quick_checkout_by_page[]" value="shortcodes" <?php checked(in_array('shortcodes', $product_types_option)); ?> />
                                        <span class="rmenu-checkbox-label">Shortcodes</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rmenu-settings-section">
                    <div class="rmenu-settings-section-header">
                        <h3><span class="dashicons dashicons-admin-appearance"></span> Button Style</h3>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">Button Style</label>
                            <div class="rmenu-settings-control">
                                <select name="rmenu_wc_checkout_style" class="rmenu-select" id="rmenu-style-select">
                                    <option value="default" <?php selected(get_option('rmenu_wc_checkout_style', 'default'), 'default'); ?>>Default WooCommerce Style</option>
                                    <option value="alt" <?php selected(get_option('rmenu_wc_checkout_style', 'default'), 'alt'); ?>>Alternative Style</option>
                                    <option value="custom" <?php selected(get_option('rmenu_wc_checkout_style', 'default'), 'custom'); ?>>Custom Style</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-row rmenu-settings-row-columns">
                        <div class="rmenu-settings-column">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Button Color</label>
                                <div class="rmenu-settings-control">
                                    <input type="color" name="rmenu_wc_checkout_color" value="<?php echo esc_attr(get_option('rmenu_wc_checkout_color', '#96588a')); ?>" class="rmenu-color-picker" />
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-column">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Text Color</label>
                                <div class="rmenu-settings-control">
                                    <input type="color" name="rmenu_wc_checkout_text_color" value="<?php echo esc_attr(get_option('rmenu_wc_checkout_text_color', '#ffffff')); ?>" class="rmenu-color-picker" />
                                </div>
                            </div>
                        </div>
                    </div>

                    

                    <div class="rmenu-settings-row rmenu-custom-css-row" id="rmenu-custom-css-row" style="<?php echo (get_option('rmenu_wc_checkout_style', 'default') == 'custom') ? 'display:block;' : 'display:none;'; ?>">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">Custom CSS</label>
                            <div class="rmenu-settings-control">
                                <textarea name="rmenu_wc_checkout_custom_css" class="rmenu-textarea-code" rows="6"><?php echo esc_textarea(get_option('rmenu_wc_checkout_custom_css', '')); ?></textarea>
                                <p class="rmenu-field-description">Add custom CSS for advanced button styling. Use the class <code>.rmenu-direct-checkout-btn</code> to target the button.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-row rmenu-settings-row-columns">
                        <div class="rmenu-settings-column">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Button Icon</label>
                                <div class="rmenu-settings-control">
                                    <select name="rmenu_wc_checkout_icon" class="rmenu-select">
                                        <option value="none" <?php selected(get_option('rmenu_wc_checkout_icon', 'none'), 'none'); ?>>No Icon</option>
                                        <option value="cart" <?php selected(get_option('rmenu_wc_checkout_icon', 'none'), 'cart'); ?>>Cart Icon</option>
                                        <option value="checkout" <?php selected(get_option('rmenu_wc_checkout_icon', 'none'), 'checkout'); ?>>Checkout Icon</option>
                                        <option value="arrow" <?php selected(get_option('rmenu_wc_checkout_icon', 'none'), 'arrow'); ?>>Arrow Icon</option>
                                    </select>
                                    <p class="rmenu-field-description">Choose an optional icon to display with the direct checkout button text.</p>
                                </div>
                            </div>
                        </div>
                        <div class="rmenu-settings-column">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Icon Position</label>
                                <div class="rmenu-settings-control">
                                    <select name="rmenu_wc_checkout_icon_position" class="rmenu-select">
                                        <option value="left" <?php selected(get_option('rmenu_wc_checkout_icon_position', 'left'), 'left'); ?>>Left</option>
                                        <option value="right" <?php selected(get_option('rmenu_wc_checkout_icon_position', 'left'), 'right'); ?>>Right</option>
                                        <option value="top" <?php selected(get_option('rmenu_wc_checkout_icon_position', 'left'), 'top'); ?>>Top</option>
                                        <option value="bottom" <?php selected(get_option('rmenu_wc_checkout_icon_position', 'left'), 'bottom'); ?>>Bottom</option>
                                    </select>                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rmenu-settings-section">
                    <div class="rmenu-settings-section-header">
                        <h3><span class="dashicons dashicons-admin-tools"></span> Advanced Options</h3>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">Mobile Optimization</label>
                            <div class="rmenu-settings-control">
                                <label class="rmenu-toggle-switch">
                                    <input type="checkbox" name="rmenu_wc_checkout_mobile_optimize" value="1" <?php checked(1, get_option("rmenu_wc_checkout_mobile_optimize", 1), true); ?> />
                                    <span class="rmenu-toggle-slider"></span>
                                </label>
                                <p class="rmenu-field-description">When enabled, the direct checkout button will be optimized for mobile devices.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">Enable for Guest Checkout</label>
                            <div class="rmenu-settings-control">
                                <label class="rmenu-toggle-switch">
                                    <input type="checkbox" name="rmenu_wc_checkout_guest_enabled" value="1" <?php checked(1, get_option("rmenu_wc_checkout_guest_enabled", 1), true); ?> />
                                    <span class="rmenu-toggle-slider"></span>
                                </label>
                                <p class="rmenu-field-description">When enabled, the direct checkout button will be available for guest users. When disabled, only logged-in users will see the button.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    .rmenu-settings-header {
                        margin-bottom: 30px;
                    }

                    .rmenu-settings-header h2 {
                        font-size: 24px;
                        font-weight: 600;
                        margin: 0 0 10px 0;
                        padding: 0;
                    }

                    .rmenu-settings-description {
                        font-size: 14px;
                        color: #646970;
                        margin: 0;
                        padding: 0;
                    }

                    .rmenu-settings-section {
                        background: #fff;
                        border: 1px solid #c3c4c7;
                        border-radius: 4px;
                        box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
                        margin-bottom: 25px;
                        padding: 0;
                        position: relative;
                    }

                    .rmenu-settings-section-header {
                        border-bottom: 1px solid #c3c4c7;
                        padding: 12px 15px;
                        background: #f0f0f1;
                    }

                    .rmenu-settings-section-header h3 {
                        margin: 0;
                        font-size: 14px;
                        font-weight: 600;
                        line-height: 1.4;
                    }

                    .rmenu-settings-section-header h3 .dashicons {
                        font-size: 16px;
                        height: 16px;
                        width: 16px;
                        margin-right: 6px;
                        color: #646970;
                    }

                    .rmenu-settings-row {
                        padding: 15px;
                        border-bottom: 1px solid #f0f0f1;
                        position: relative;
                        display: flex;
                        flex-wrap: wrap;
                    }

                    .rmenu-settings-row:last-child {
                        border-bottom: none;
                    }

                    .rmenu-settings-row-columns {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 20px;
                    }

                    .rmenu-settings-column {
                        flex: 1;
                        min-width: 200px;
                    }

                    .rmenu-settings-field {
                        width: 100%;
                        display: flex;
                        flex-direction: column;
                    }

                    .rmenu-settings-label {
                        font-weight: 600;
                        margin-bottom: 8px;
                        font-size: 14px;
                    }

                    .rmenu-settings-control {
                        flex: 1;
                    }

                    .rmenu-field-description {
                        color: #646970;
                        font-size: 13px;
                        margin: 5px 0 0;
                        font-style: italic;
                    }

                    .rmenu-checkbox-group {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 20px;
                    }

                    .rmenu-checkbox-column {
                        flex: 1;
                        min-width: 200px;
                    }

                    .rmenu-checkbox-column h4 {
                        margin: 0 0 10px 0;
                        padding: 0;
                        font-size: 14px;
                        color: #3c434a;
                    }

                    .rmenu-checkbox-container {
                        display: block;
                        position: relative;
                        padding: 5px 0;
                        cursor: pointer;
                        user-select: none;
                    }

                    .rmenu-checkbox-label {
                        margin-left: 5px;
                        font-size: 13px;
                    }

                    .rmenu-toggle-switch {
                        position: relative;
                        display: inline-block;
                        width: 40px;
                        height: 22px;
                    }

                    .rmenu-toggle-switch input {
                        opacity: 0;
                        width: 0;
                        height: 0;
                    }

                    .rmenu-toggle-slider {
                        position: absolute;
                        cursor: pointer;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background-color: #ccc;
                        transition: .4s;
                        border-radius: 34px;
                    }

                    .rmenu-toggle-slider:before {
                        position: absolute;
                        content: "";
                        height: 18px;
                        width: 18px;
                        left: 2px;
                        bottom: 2px;
                        background-color: white;
                        transition: .4s;
                        border-radius: 50%;
                    }

                    input:checked+.rmenu-toggle-slider {
                        background-color: #2271b1;
                    }

                    input:focus+.rmenu-toggle-slider {
                        box-shadow: 0 0 1px #2271b1;
                    }

                    input:checked+.rmenu-toggle-slider:before {
                        transform: translateX(18px);
                    }

                    .rmenu-select {
                        min-width: 200px;
                        max-width: 100%;
                    }

                    .rmenu-color-picker {
                        padding: 0;
                        border: 1px solid #8c8f94;
                        border-radius: 4px;
                        height: 30px;
                        width: 60px;
                    }

                    .rmenu-textarea-code {
                        min-height: 100px;
                        width: 100%;
                        font-family: monospace;
                    }
                </style>

                <script type="text/javascript">
                    jQuery(document).ready(function($) {
                        $('#rmenu-style-select').on('change', function() {
                            if ($(this).val() === 'custom') {
                                $('#rmenu-custom-css-row').show();
                            } else {
                                $('#rmenu-custom-css-row').hide();
                            }
                        });
                    });
                </script>
            </div>
            <div class="tab-content" id="tab-5">
                <h2>Manage Features</h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Product Quantity Controller</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="rmenu_quantity_control" value="1" <?php checked(1, get_option("rmenu_quantity_control", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Remove product Button</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="rmenu_remove_product" value="1" <?php checked(1, get_option("rmenu_remove_product", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Add Image Before Product</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="rmenu_add_img_before_product" value="1" <?php checked(1, get_option("rmenu_add_img_before_product", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Disable Cart Page (Coming Soon)</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="rmenu_disable_cart_page" value="1" <?php checked(1, get_option("rmenu_disable_cart_page", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Express Checkout options (Coming Soon)</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="rmenu_express_checkout" value="1" <?php checked(1, get_option("rmenu_express_checkout", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Quick View (Coming Soon)</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="rmenu_quick_view" value="1" <?php checked(1, get_option("rmenu_quick_view", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Address Auto-Complete (Coming Soon)</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="rmenu_address_auto_complete" value="1" <?php checked(1, get_option("rmenu_address_auto_complete", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Multi-step checkout (Coming Soon)</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="rmenu_multi_step_checkout" value="1" <?php checked(1, get_option("rmenu_multi_step_checkout", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Trust Badge on checkout (Coming Soon)</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="rmenu_multi_step_checkout" value="1" <?php checked(1, get_option("rmenu_multi_step_checkout", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">at least one product in cart (Coming Soon)</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="rmenu_at_one_product_cart" value="1" <?php checked(1, get_option("rmenu_at_one_product_cart", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="tab-content" id="tab-6">
                <h2>Advanced Settings</h2>
                <div class="advanced-settings-content" id="trust-content">
                    <h3>Trust Badges Configuration</h3>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Enable Trust Badges</th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" name="onepaquc_trust_badges_enabled" value="1"
                                        <?php checked(1, get_option('onepaquc_trust_badges_enabled', 0), true); ?> />
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">Display trust signals and security badges on the checkout page.</p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Badge Position</th>
                            <td>
                                <select name="onepaquc_trust_badge_position">
                                    <option value="above_checkout" <?php selected(get_option('onepaquc_trust_badge_position', 'below_checkout'), 'above_checkout'); ?>>Above Checkout Form</option>
                                    <option value="below_checkout" <?php selected(get_option('onepaquc_trust_badge_position', 'below_checkout'), 'below_checkout'); ?>>Below Checkout Form</option>
                                    <option value="payment_section" <?php selected(get_option('onepaquc_trust_badge_position', 'below_checkout'), 'payment_section'); ?>>Next to Payment Methods</option>
                                    <option value="order_summary" <?php selected(get_option('onepaquc_trust_badge_position', 'below_checkout'), 'order_summary'); ?>>Below Order Summary</option>
                                </select>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Custom Trust Badge HTML</th>
                            <td>
                                <textarea name="onepaquc_trust_badge_custom_html" rows="6" class="large-text code"><?php echo esc_textarea(get_option('onepaquc_trust_badge_custom_html', '<div class="trust-badges">
    <div class="trust-badge"><i class="dashicons dashicons-lock"></i> Secure Checkout</div>
    <div class="trust-badge"><i class="dashicons dashicons-shield"></i> Money-Back Guarantee</div>
    <div class="trust-badge"><i class="dashicons dashicons-privacy"></i> Privacy Protected</div>
</div>')); ?></textarea>
                                <p class="description">Custom HTML for trust badges. You can use dashicons or include your own images.</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="advanced-settings-content" id="summary-content">
                    <h3>Order Summary Customization</h3>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Order Summary Style</th>
                            <td>
                                <select name="onepaquc_order_summary_style">
                                    <option value="default" <?php selected(get_option('onepaquc_order_summary_style', 'default'), 'default'); ?>>Default (Table Style)</option>
                                    <option value="compact" <?php selected(get_option('onepaquc_order_summary_style', 'default'), 'compact'); ?>>Compact List</option>
                                    <option value="detailed" <?php selected(get_option('onepaquc_order_summary_style', 'default'), 'detailed'); ?>>Detailed With Images</option>
                                    <option value="minimal" <?php selected(get_option('onepaquc_order_summary_style', 'default'), 'minimal'); ?>>Minimal</option>
                                </select>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Collapsible Summary</th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" name="onepaquc_order_summary_collapsible" value="1"
                                        <?php checked(1, get_option('onepaquc_order_summary_collapsible', 0), true); ?> />
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">Allow customers to collapse/expand the order summary (useful for mobile).</p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Show Product Images</th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" name="onepaquc_show_product_images" value="1"
                                        <?php checked(1, get_option('onepaquc_show_product_images', 1), true); ?> />
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Show Product SKU</th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" name="onepaquc_show_product_sku" value="1"
                                        <?php checked(1, get_option('onepaquc_show_product_sku', 0), true); ?> />
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <?php submit_button(); ?>
        </form>
        <?php $inline_script = '
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("reset-defaults").addEventListener("click", function() {
            if (confirm("Are you sure you want to reset all settings to their default values?")) {
                // Send AJAX request to reset settings
                fetch("' . esc_url(admin_url('admin-ajax.php')) . '?action=onepaquc_reset_onepaquc_cart_settings", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    credentials: "same-origin"
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Settings have been reset to default.");
                        location.reload(); // Reload to show the reset values
                    } else {
                        alert("An error occurred while resetting settings.");
                    }
                });
            }
        });
    });
    ';

        // Add the inline script
        wp_add_inline_script('rmenu-admin-script', $inline_script);

        ?>
        <p style="text-align: center;font-size: 15px;">To add menu cart to your page, use the shortcode <b>[plugincy_cart drawer="right" cart_icon="cart" product_title_tag="h4"]</b> or use Plugincy Cart Widget/Block</p>
        <p style="text-align: center;padding-bottom:20px; font-size: 15px;">[plugincy_one_page_checkout product_ids="152,153,151,142" template="product-tabs"] or use <b>Plugincy One Page Checkout</b> widget/block <a href="/wp-admin/admin.php?page=onepaquc_cart_documentation#multiple-products">view documentation</a></p>
    </div>
<?php
    // }
}
add_action('admin_init', 'onepaquc_cart_settings');
// add_action('wp_head', 'onepaquc_cart_custom_css');




function onepaquc_cart_settings()
{
    foreach (onepaquc_rmenu_fields() as $key => $field) {
        register_setting('onepaquc_cart_settings', $key, 'sanitize_text_field');
    }
    foreach (onepaquc_onpcheckout_heading() as $key => $field) {
        register_setting('onepaquc_cart_settings', $key, 'sanitize_text_field');
    }

    $string_fields = [
        "rmsg_editor",
        "onpage_checkout_position",
        "onpage_checkout_cart_empty",
        "onpage_checkout_enable_all",
        "onpage_checkout_cart_add",
        "onpage_checkout_widget_cart_empty",
        "onpage_checkout_widget_cart_add",
        "onpage_checkout_hide_cart_button",
        "rmenu_quantity_control",
        "rmenu_remove_product",
        "rmenu_add_img_before_product",
        "rmenu_add_direct_checkout_button",
        "rmenu_wc_checkout_guest_enabled",
        "rmenu_wc_checkout_mobile_optimize",
        "rmenu_wc_direct_checkout_position",
        "txt-direct-checkout",
        "rmenu_wc_checkout_color",
        "rmenu_wc_checkout_text_color",
        "rmenu_wc_checkout_style",
        "rmenu_wc_checkout_icon",
        "rmenu_wc_checkout_icon_position",
        "rmenu_wc_checkout_method",
        "rmenu_wc_clear_cart",
        "rmenu_wc_one_click_purchase",
        "rmenu_wc_add_confirmation",
    ];

    foreach ($string_fields as $field) {
        register_setting('onepaquc_cart_settings', $field, 'sanitize_text_field');
    }

    global $onepaquc_checkoutformfields, $onepaquc_productpageformfields;
    $settings = array_merge(array_keys($onepaquc_checkoutformfields), array_keys($onepaquc_productpageformfields));

    foreach ($settings as $setting) {
        register_setting('onepaquc_cart_settings', $setting, 'sanitize_text_field');
    }
    // Register the setting for the checkout fields values of array
    register_setting('onepaquc_cart_settings', "onepaquc_checkout_fields", 'onepaquc_sanitize_array_of_text');
    register_setting('onepaquc_cart_settings', "rmenu_show_quick_checkout_by_types", 'onepaquc_sanitize_array_of_text');
    register_setting('onepaquc_cart_settings', "rmenu_show_quick_checkout_by_page", 'onepaquc_sanitize_array_of_text');
}

function onepaquc_sanitize_array_of_text($value)
{
    if (!is_array($value)) {
        return [];
    }

    return array_map('sanitize_text_field', $value);
}



// Function to reset the settings to default values
function onepaquc_reset_onepaquc_cart_settings()
{
    global $onepaquc_checkoutformfields,
        $onepaquc_productpageformfields;
    // List of settings to reset
    $settings = array_merge(array_keys($onepaquc_checkoutformfields), array_keys($onepaquc_productpageformfields));

    // Reset each setting
    foreach ($settings as $setting) {
        update_option($setting, ''); // Reset each option to an empty string, or set a default value here
    }

    // Send a JSON response back to the client
    wp_send_json_success();
}
add_action('wp_ajax_onepaquc_reset_onepaquc_cart_settings', 'onepaquc_reset_onepaquc_cart_settings');

function onepaquc_cart_custom_css()
{
    global $onepaquc_rcheckoutformfields;

    // Initialize an empty string for the custom CSS
    $custom_css = '';

    // Loop through the fields to generate CSS
    foreach (onepaquc_rmenu_fields() as $key => $field) {
        if (get_option($key)) {
            $custom_css .= "{$field['selector']} { display: none !important; }\n";
        }
    }

    foreach (onepaquc_onpcheckout_heading() as $key => $field) {
        if (get_option($key)) {
            $custom_css .= "{$field['selector']} { display: none !important; }\n";
        }
    }

    if (get_option('onepaquc_checkout_fields')) {
        $checkout_fields = get_option('onepaquc_checkout_fields');
        foreach ($checkout_fields as $field) {
            if (isset($onepaquc_rcheckoutformfields[$field])) {
                $selector = $onepaquc_rcheckoutformfields[$field]['selector'];
                $custom_css .= "{$selector} { display: none !important; }\n";
            }
        }
    }

    // Add the inline styles
    wp_add_inline_style('rmenu-cart-style', esc_html($custom_css));
}

// Hook to enqueue the styles
add_action('wp_enqueue_scripts', 'onepaquc_cart_custom_css', 9999999);

function onepaquc_rmenu_fields()
{
    return [
        'hide_coupon_toggle'          => ['selector' => '#checkout-form .woocommerce-form-coupon-toggle, #checkout-form .col-form-coupon,.one-page-checkout-container .woocommerce-form-coupon-toggle, .one-page-checkout-container .col-form-coupon', 'title' => 'Hide Top Coupon'],
        'hide_customer_details_col2'  => ['selector' => '.checkout-popup .woocommerce-shipping-fields, .one-page-checkout-container .woocommerce-shipping-fields', 'title' => 'Hide Shipping Address'],
        'hide_notices_wrapper'        => ['selector' => '#checkout-form .woocommerce-notices-wrapper,.one-page-checkout-container .woocommerce-notices-wrapper', 'title' => 'Hide Notices Wrapper'],
        'hide_privacy_policy_text'    => ['selector' => '#checkout-form .woocommerce-privacy-policy-text,.one-page-checkout-container .woocommerce-privacy-policy-text', 'title' => 'Hide Privacy Policy Text'],
        'hide_payment'                 => ['selector' => '#checkout-form div#payment ul,.one-page-checkout-container div#payment ul', 'title' => 'Hide Payment Options'],
        'hide_product'                 => ['selector' => '#checkout-form table.shop_table,.one-page-checkout-container table.shop_table', 'title' => 'Hide Product Table']

    ];
}

function onepaquc_onpcheckout_heading()
{
    return [
        'hide_billing_details'          => ['selector' => '#checkout-form .woocommerce-billing-fields h3,.one-page-checkout-container .woocommerce-billing-fields h3', 'title' => 'Hide Billing details'],
        'hide_additional_details'          => ['selector' => '.checkout-popup .woocommerce-additional-fields h3,.one-page-checkout-container .woocommerce-additional-fields h3', 'title' => 'Hide Additional Details'],
        'hide_order_review_heading'   => ['selector' => '#checkout-form h3#order_review_heading,.one-page-checkout-container h3#order_review_heading', 'title' => 'Hide Order Review Heading'],
    ];
}
