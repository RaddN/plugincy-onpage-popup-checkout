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
                <p style="display: inline;"><?php echo esc_html($label); ?></p>
                <span class="tooltip" style="display: inline;">
                    <span class="question-mark">?</span>
                    <span class="tooltip-text">You can find "<?php echo esc_html($label); ?>" in the checkout form & drawer<?php echo $name === "txt-complete_your_purchase" ? " on single product pages." : "."; ?></span>
                </span>
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
        <h1>Welcome to One Page Quick Checkout for WooCommerce <span class="version-tag">v1.0.5</span></h1>
        <p>Thank you for installing One Page Quick Checkout for WooCommerce! Streamline your WooCommerce checkout process and boost your conversion rates with our easy-to-configure solution.</p>
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
            <a target="_blank" href="https://plugincy.com/documentations/one-page-quick-checkout-for-woocommerce/" class="button">View Documentation</a>
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
            <div class="tab" data-tab="3">Text Manage</div>
            <div class="tab" data-tab="2">One Page Checkout</div>
            <div class="tab" data-tab="8">Add To Cart</div>
            <div class="tab" data-tab="4">Direct Checkout Manage</div>
            <div class="tab" data-tab="7">Quick View</div>
            <div class="tab" data-tab="5">Features</div>
            <div class="tab" data-tab="6">Advanced Settings</div>
            <div class="tab" data-tab="100">Plugin License</div>
        </div>
        <script>
            function isColorDark(color) {
                if (!color) return false;

                // Remove # if present and convert to lowercase
                const hex = color.replace('#', '').toLowerCase();

                // Handle 3-digit hex
                let fullHex = hex;
                if (hex.length === 3) {
                    fullHex = hex.split('').map(char => char + char).join('');
                }

                // Validate hex format
                if (fullHex.length !== 6 || !/^[0-9a-f]{6}$/.test(fullHex)) {
                    return false;
                }

                // Parse RGB values
                const r = parseInt(fullHex.slice(0, 2), 16);
                const g = parseInt(fullHex.slice(2, 4), 16);
                const b = parseInt(fullHex.slice(4, 6), 16);

                // Calculate relative luminance
                const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

                // Color is dark if luminance is less than 0.5
                return luminance < 0.5;
            }
            function checkColors(checkoutColor, checkoutTextColor, is_warn_show = true) {
                const bgColor = checkoutColor.value;
                const textColor = checkoutTextColor.value;

                if (!is_warn_show) {
                    return;
                }

                if (isColorDark(bgColor) && isColorDark(textColor)) {
                    showDirectCheckoutWarning(
                        checkoutColor.closest('.rmenupro-settings-row'),
                        'Warning: Both background and text colors are dark. This may affect readability.'
                    );
                } else if (!isColorDark(bgColor) && !isColorDark(textColor)) {
                    showDirectCheckoutWarning(
                        checkoutTextColor.closest('.rmenupro-settings-row'),
                        'Warning: Both background and text colors are light. This may affect readability.'
                    );
                } else {
                    removeDirectCheckoutWarning(checkoutColor.closest('.rmenupro-settings-row'));
                    removeDirectCheckoutWarning(checkoutTextColor.closest('.rmenupro-settings-row'));
                }
            }
        </script>
         <div class="tab-content active" id="tab-100">
            <?php
            $license_manager = new onepaquc_License_Manager();
                    $license_manager->render_license_form();
            ?>
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
                        <th scope="row">Enable One Page Checkout</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="onpage_checkout_enable" value="1" <?php checked(1, get_option("onpage_checkout_enable", 1), true); ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable one-page checkout for all products without individual selection.</span>
                            </span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Form Position</th>
                        <td>
                            <?php
                            // Get the saved value or default to 9 if not set or empty
                            $onpage_checkout_position = get_option("onpage_checkout_position", '');
                            if ($onpage_checkout_position === '' || $onpage_checkout_position === false) {
                                $onpage_checkout_position = 9;
                            }
                            ?>
                            <input type="number" name="onpage_checkout_position" value="<?php echo esc_attr($onpage_checkout_position); ?>" />
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
                        <th scope="row">Checkout Layout</th>
                        <td class="pro-only">
                            <select disabled name="onpage_checkout_layout">
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

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                        const enableCheckout = document.querySelector('div#tab-2 input[name="onpage_checkout_enable"]');

                        const allinputFields = Array.from(document.querySelectorAll('div#tab-2 table:nth-of-type(1) input')).filter(
                            el => !(el.name === "onpage_checkout_enable")
                        );
                        allinputFields.forEach(field => {
                            field.disabled = !enableCheckout.checked;
                        });
                        enableCheckout.addEventListener('change', function() {
                            allinputFields.forEach(field => {
                                field.disabled = !this.checked;
                            });
                        });
                    });
                </script>
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
            </div>
            <div class="tab-content" id="tab-4">
                <div class="rmenu-settings-header">
                    <h2>WooCommerce Direct Checkout</h2>
                    <p class="rmenu-settings-description">Configure how the quick checkout functionality works with your WooCommerce store.</p>
                </div>

                <div class="rmenu-settings-tabs">
                    <ul class="rmenu-settings-tab-list" style="color: #135e96;display: flex; gap: 10px; cursor: pointer;text-decoration: underline;">
                        <li class="rmenu-settings-tab-item active" data-tab="direct-general-settings">General Settings</li>
                        <li class="rmenu-settings-tab-item" data-tab="direct-button-behavior">Button Behavior</li>
                        <li class="rmenu-settings-tab-item" data-tab="direct-advanced">Advanced</li>
                    </ul>
                </div>

                <div class="tab-content" id="direct-general-settings" style="padding: 0;">
                    <div class="rmenu-settings-section">
                        <div class="rmenu-settings-section-header">
                            <h3><span class="dashicons dashicons-admin-generic"></span> General Settings</h3>
                        </div>

                        <div class="rmenu-settings-row" id="rmenu-direct-checkout-enable-field">
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
                                    <?php
                                    $direct_checkout_text = get_option('txt-direct-checkout', '');
                                    if (empty($direct_checkout_text)) {
                                        $direct_checkout_text = 'Quick Checkout';
                                    }
                                    ?>
                                    <input type="text" name="txt-direct-checkout" value="<?php echo esc_attr($direct_checkout_text); ?>" class="regular-text" />
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
                    <div class="rmenu-settings-section" id="rmenu-direct-button-display-section">
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

                                    <label class="rmenu-checkbox-container pro-only">
                                        <input disabled type="checkbox" name="rmenu_show_quick_checkout_by_types[]" value="coming_grouped" <?php checked(in_array('grouped', $product_types_option)); ?> />
                                        <span class="rmenu-checkbox-label">Grouped Products (Pro Feature)</span>
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

                                        <label class="rmenu-checkbox-container pro-only">
                                            <input disabled type="checkbox" name="rmenu_show_quick_checkout_by_page[]" value="cross-sells" <?php checked(in_array('cross-sells', $product_types_option)); ?> />
                                            <span class="rmenu-checkbox-label">Cross-sells (Pro Features)</span>
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

                    <div class="rmenu-settings-section direct-button-style-section">
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
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                            const button_style = document.querySelector('div#tab-4 select[name="rmenu_wc_checkout_style"]');

                            // Select all children except the first two in the .button-style-section & except div#rmenu-atc-custom-width-row
                            const buttonStyleSection = document.querySelector('.rmenu-settings-section.direct-button-style-section');
                            const allFields = Array.from(buttonStyleSection ? buttonStyleSection.children : []).slice(2);
                            // if rmenu_wc_checkout_icon is 'none', disable the icon position field
                            const iconSelect = document.querySelector('select[name="rmenu_wc_checkout_icon"]');
                            const iconPositionField = document.querySelector('select[name="rmenu_wc_checkout_icon_position"]');
                            if (iconSelect && iconPositionField) {
                                iconSelect.addEventListener('change', function() {
                                    if (this.value === 'none') {
                                        iconPositionField.disabled = true;
                                    } else {
                                        iconPositionField.disabled = false;
                                    }
                                });

                                // Trigger change event on page load to set initial visibility
                                iconSelect.dispatchEvent(new Event('change'));
                            }

                            // if button_style !== 'custom', none all fields except the first two
                            if (button_style) {
                                button_style.addEventListener('change', function() {
                                    if (this.value !== 'default') {
                                        allFields.forEach(field => field.style.display = 'flex');
                                        document.querySelector('#rmenu-custom-css-row').style.display = (this.value === 'custom') ? 'block' : 'none';
                                    } else {
                                        allFields.forEach(field => field.style.display = 'none');
                                    }
                                });

                                // Trigger change event on page load to set initial visibility
                                button_style.dispatchEvent(new Event('change'));

                            }

                            // if rmenu_wc_checkout_color (which is bg color) & rmenu_wc_checkout_text_color (which is text color) both are dark or light, show a warning message
                            const checkoutColor = document.querySelector('input[name="rmenu_wc_checkout_color"]');
                            const checkoutTextColor = document.querySelector('input[name="rmenu_wc_checkout_text_color"]');
                            if (checkoutColor && checkoutTextColor) {
                                checkoutColor.addEventListener('change', function() {
                                    checkColors(checkoutColor, checkoutTextColor, button_style && button_style.value !== 'default');
                                });
                                checkoutTextColor.addEventListener('change', function() {
                                    checkColors(checkoutColor, checkoutTextColor, button_style && button_style.value !== 'default');
                                });

                                // Initial check on page load
                                checkColors(checkoutColor, checkoutTextColor, button_style && button_style.value !== 'default');
                            }

                        });
                    </script>
                </div>

                <div class="rmenu-settings-section tab-content" id="direct-button-behavior" style="padding: 0;">
                    <div class="rmenu-settings-section-header">
                        <h3><span class="dashicons dashicons-cart"></span> Quick Checkout Behavior</h3>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">Checkout Method</label>
                            <div class="rmenu-settings-control">
                                <select name="rmenu_wc_checkout_method" class="rmenu-select" id="rmenu-checkout-method">
                                    <option value="direct_checkout" <?php selected(get_option('rmenu_wc_checkout_method', 'direct_checkout'), 'direct_checkout'); ?>>Redirect to Checkout</option>
                                    <option value="ajax_add" <?php selected(get_option('rmenu_wc_checkout_method', 'direct_checkout'), 'ajax_add'); ?>>AJAX Add to Cart</option>
                                    <!-- rmenu_disable_cart_page is it's on disable below option & show cart page is disabled -->
                                    <?php
                                    $disable_cart_page = get_option('rmenu_disable_cart_page', '0');
                                    ?>
                                    <?php if (!$disable_cart_page) : ?>
                                        <option value="cart_redirect" <?php selected(get_option('rmenu_wc_checkout_method', 'popup_checkout'), 'cart_redirect'); ?>>Redirect to Cart Page</option>
                                    <?php else : ?>
                                        <option value="cart_redirect" disabled <?php selected(get_option('rmenu_wc_checkout_method', 'popup_checkout'), 'cart_redirect'); ?>>Redirect to Cart Page (Disabled)</option>
                                    <?php endif; ?>
                                    <option disabled value="popup_checkout_pro" <?php selected(get_option('rmenu_wc_checkout_method', 'direct_checkout'), 'popup_checkout_pro'); ?>>Popup Checkout (Pro Features)</option>
                                    <option disabled value="advanced_pro" <?php selected(get_option('rmenu_wc_checkout_method', 'direct_checkout'), 'advanced_pro'); ?>>Advanced Checkout (Pro Features)</option>
                                    <option value="side_cart" <?php selected(get_option('rmenu_wc_checkout_method', 'direct_checkout'), 'side_cart'); ?>>Side Cart Slide-in</option>
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
                            <label class="rmenu-settings-label">Single Checkout without Clear Cart</label>
                            <div class="rmenu-settings-control pro-only">
                                <label class="rmenu-toggle-switch">
                                    <input disabled type="checkbox" name="rmenu_wc_single_checkout" value="1" <?php checked(1, get_option("rmenu_wc_single_checkout", 0), true); ?> />
                                    <span class="rmenu-toggle-slider"></span>
                                </label>
                                <p class="rmenu-field-description">When enabled, the cart will not be emptied before adding the new product.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">One-Click Purchase</label>
                            <div class="rmenu-settings-control pro-only">
                                <label class="rmenu-toggle-switch">
                                    <input disabled type="checkbox" name="rmenu_wc_one_click_purchase" value="1" <?php checked(1, get_option("rmenu_wc_one_click_purchase", 0), true); ?> />
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

                <div class="tab-content" id="direct-advanced" style="padding: 0;">
                    <div class="rmenu-settings-section">
                        <div class="rmenu-settings-section-header">
                            <h3><span class="dashicons dashicons-category"></span>Quick Checkout in Variable Product</h3>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Show Variation Selection in Archive pages</label>
                                <div class="rmenu-settings-control">
                                    <label class="rmenu-toggle-switch">
                                        <input type="checkbox" name="rmenu_variation_show_archive" value="1" <?php checked(1, get_option("rmenu_variation_show_archive", 1), true); ?> />
                                        <span class="rmenu-toggle-slider"></span>
                                    </label>
                                    <p class="rmenu-field-description">When enabled, the variation selection will be shown on archive pages.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Hide Select Option Button</label>
                                <div class="rmenu-settings-control pro-only">
                                    <label class="rmenu-toggle-switch">
                                        <input disabled type="checkbox" name="rmenu_wc_hide_select_option" value="1" <?php checked(1, get_option("rmenu_wc_hide_select_option", 1), true); ?> />
                                        <span class="rmenu-toggle-slider"></span>
                                    </label>
                                    <p class="rmenu-field-description">When enabled, the select option button will be hidden on variable product pages.</p>
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
                                <div class="rmenu-settings-control pro-only">
                                    <label class="rmenu-toggle-switch">
                                        <input disabled type="checkbox" name="rmenu_wc_checkout_mobile_optimize" value="1" <?php checked(1, get_option("rmenu_wc_checkout_mobile_optimize", 1), true); ?> />
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
                <script>
                    function showDirectCheckoutWarning(highlightSection, message) {
                        let popup = document.getElementById('rmenu-enable-atc-popup');
                        if (highlightSection) {
                            highlightSection.style.border = '2px solid #dc3545';
                            highlightSection.style.padding = '10px';
                        }
                        if (!popup) {
                            popup = document.createElement('div');
                            popup.id = 'rmenu-enable-atc-popup';
                            popup.innerHTML = `
                                    <div style="
                                        display: flex;
                                        align-items: center;
                                        gap: 10px;
                                        background: #dc3545;
                                        color: #fff;
                                        padding: 16px 28px 16px 16px;
                                        border-radius: 6px;
                                        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
                                        font-size: 15px;
                                        position: fixed;
                                        top: 30px;
                                        right: 30px;
                                        z-index: 99999;
                                    ">
                                        <span style="font-size:18px;vertical-align:middle;" class="dashicons dashicons-warning"></span>
                                        <span>
                                            <b>${message}</b>
                                        </span>
                                        <span id="rmenu-enable-atc-popup-close" style="margin-left:12px;cursor:pointer;font-size:18px;">&times;</span>
                                    </div>
                                `;
                            document.body.appendChild(popup);
                            document.getElementById('rmenu-enable-atc-popup-close').onclick = function() {
                                popup.remove();
                            };
                            // Only remove the popup if it's not being hovered
                            let isHovered = false;
                            popup.addEventListener('mouseenter', function() {
                                isHovered = true;
                            });
                            popup.addEventListener('mouseleave', function() {
                                isHovered = false;
                            });
                            setTimeout(function() {
                                if (popup && !isHovered) popup.remove();
                            }, 3500);
                        }
                    }
                    // Reusable function to remove warning popup and highlight
                    function removeDirectCheckoutWarning(highlightSection) {
                        if (highlightSection) {
                            highlightSection.style.border = '';
                            highlightSection.style.padding = '';
                        }
                        const existingPopup = document.getElementById('rmenu-enable-atc-popup');
                        if (existingPopup) {
                            existingPopup.remove();
                        }
                    }
                    document.addEventListener('DOMContentLoaded', function() {
                        // Tab click handler for direct checkout settings tabs
                        const tabItems = document.querySelectorAll('#tab-4 .rmenu-settings-tab-item');
                        const tabContents = document.querySelectorAll('#tab-4 .tab-content');
                        const enableDirectCheckout = document.querySelector('input[name="rmenu_add_direct_checkout_button"]');
                        const typeCheckboxes = document.querySelectorAll('input[name="rmenu_show_quick_checkout_by_types[]"]');
                        const highlightSection = document.getElementById('rmenu-direct-button-display-section');
                        const highlight_enableSection = document.getElementById('rmenu-direct-checkout-enable-field');
                        // rmenu_show_quick_checkout_by_page[]
                        const pageCheckboxes = document.querySelectorAll('input[name="rmenu_show_quick_checkout_by_page[]"]');

                        enableDirectCheckout.addEventListener('change', function() {
                            if (enableDirectCheckout.checked) {
                                removeDirectCheckoutWarning(highlight_enableSection);
                                const allTypeUnchecked = Array.from(typeCheckboxes).every(checkbox => !checkbox.checked);
                                const allPageUnchecked = Array.from(pageCheckboxes).every(checkbox => !checkbox.checked);
                                if (allTypeUnchecked || allPageUnchecked) {
                                    showDirectCheckoutWarning(
                                        highlightSection,
                                        'Please select at least one product type and one page to show changes.'
                                    );
                                }
                            }
                        });

                        // On change pageCheckboxes & typeCheckboxes & if at least one of them is checked, remove highlight and popup
                        function handleDirectCheckoutCheckboxChange() {
                            const allChecked = Array.from(typeCheckboxes).some(checkbox => checkbox.checked);
                            const allPageChecked = Array.from(pageCheckboxes).some(checkbox => checkbox.checked);
                            if (allChecked && allPageChecked) {
                                removeDirectCheckoutWarning(highlightSection);
                            } else {
                                if (enableDirectCheckout.checked) {
                                    const allTypeUnchecked = Array.from(typeCheckboxes).every(checkbox => !checkbox.checked);
                                    const allPageUnchecked = Array.from(pageCheckboxes).every(checkbox => !checkbox.checked);
                                    if (allTypeUnchecked || allPageUnchecked) {
                                        showDirectCheckoutWarning(
                                            highlightSection,
                                            'Please select at least one product type and one page to show changes.'
                                        );
                                    }
                                }
                            }
                        }
                        typeCheckboxes.forEach(checkbox => {
                            checkbox.addEventListener('change', handleDirectCheckoutCheckboxChange);
                        });
                        pageCheckboxes.forEach(checkbox => {
                            checkbox.addEventListener('change', handleDirectCheckoutCheckboxChange);
                        });

                        tabItems.forEach(function(tab) {
                            tab.addEventListener('click', function() {
                                // Remove active class from all tabs
                                tabItems.forEach(function(t) {
                                    t.classList.remove('active');
                                });
                                // Hide all tab contents
                                tabContents.forEach(function(content) {
                                    content.style.display = 'none';
                                });

                                // Add active class to clicked tab
                                tab.classList.add('active');
                                // Show the corresponding tab content
                                const tabId = tab.getAttribute('data-tab');

                                if (tabId !== "direct-general-settings" && enableDirectCheckout && !enableDirectCheckout.checked) {

                                    showDirectCheckoutWarning(
                                        highlight_enableSection,
                                        '<b>Enable Direct Checkout</b> in the general settings tab to access these options.'
                                    );
                                } else {
                                    // Hide any existing popup
                                    const existingPopup = document.getElementById('rmenu-enable-atc-popup');
                                    if (existingPopup) {
                                        existingPopup.remove();
                                    }
                                }
                                if (enableDirectCheckout.checked) {
                                    const allTypeUnchecked = Array.from(typeCheckboxes).every(checkbox => !checkbox.checked);
                                    const allPageUnchecked = Array.from(pageCheckboxes).every(checkbox => !checkbox.checked);
                                    if (allTypeUnchecked || allPageUnchecked) {
                                        showDirectCheckoutWarning(
                                            highlightSection,
                                            'Please select at least one product type and one page to show changes.'
                                        );
                                    }
                                }
                                const content = document.getElementById(tabId);
                                if (content) {
                                    content.style.display = 'block';
                                }
                            });
                        });

                        // Show only the first tab content by default
                        tabContents.forEach(function(content, idx) {
                            content.style.display = (idx === 0) ? 'block' : 'none';
                        });
                    });
                </script>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                    const enableCheckout = document.querySelector('div#tab-4 input[name="rmenu_add_direct_checkout_button"]');

                    const allinputFields = Array.from(document.querySelectorAll('div#tab-4 input, div#tab-4 select')).filter(
                        el => !(el.name === "rmenu_add_direct_checkout_button")
                    );
                    allinputFields.forEach(field => {
                        field.disabled = !enableCheckout.checked;
                    });
                    enableCheckout.addEventListener('change', function() {
                        allinputFields.forEach(field => {
                            field.disabled = !this.checked;
                        });
                    });
                });
            </script>
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
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "Product Quantity Controller" to manage product quantities in the checkout form.</span>
                            </span>
                        </td>
                    </tr>
                    <tr valign="top" class="pro-only top-50">
                        <th scope="row">Remove product Button</th>
                        <td>
                            <label class="switch">
                                <input disabled type="checkbox" name="rmenu_remove_product" value="1" <?php checked(1, get_option("rmenu_remove_product", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "Remove Product Button" to allow customers to remove products from the checkout form.</span>
                            </span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Add Image Before Product</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="rmenu_add_img_before_product" value="1" <?php checked(1, get_option("rmenu_add_img_before_product", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "Add Image Before Product" to display product images before their titles in the checkout form.</span>
                            </span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">at least one product in cart</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="rmenu_at_one_product_cart" value="1" <?php checked(1, get_option("rmenu_at_one_product_cart", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "At least one product in cart" to add at least one product in the cart.</span>
                            </span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Disable Cart Page</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="rmenu_disable_cart_page" value="1" <?php checked(1, get_option("rmenu_disable_cart_page", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "Disable Cart Page" to remove the cart page from your WooCommerce store. This will redirect customers directly to the checkout page.</span>
                            </span>
                        </td>
                    </tr>
                    <tr valign="top" class="pro-only top-50">
                        <th scope="row">Express Checkout options</th>
                        <td>
                            <label class="switch">
                                <input disabled type="checkbox" name="rmenu_express_checkout" value="1" <?php checked(1, get_option("rmenu_express_checkout", 0), true); ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "Express Checkout options" to allow customers to use express checkout methods like PayPal, Stripe, etc.</span>
                            </span>
                        </td>
                    </tr>
                    <tr valign="top" class="pro-only top-50">
                        <th scope="row">Address Auto-Complete</th>
                        <td>
                            <label class="switch">
                                <input disabled type="checkbox" name="rmenu_address_auto_complete" value="1" <?php checked(1, get_option("rmenu_address_auto_complete", 0), true); ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "Address Auto-Complete" to automatically fill in the address fields based on the customer's input.</span>
                            </span>
                        </td>
                    </tr>
                    <tr valign="top" class="pro-only top-50">
                        <th scope="row">Multi-step checkout</th>
                        <td>
                            <label class="switch">
                                <input disabled type="checkbox" name="rmenu_multi_step_checkout" value="1" <?php checked(1, get_option("rmenu_multi_step_checkout", 0), true); ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "Multi-step checkout" to split the checkout process into multiple steps for a better user experience.</span>
                            </span>
                        </td>
                    </tr>
                    <tr valign="top" class="pro-only top-50">
                        <th scope="row">Force login before checkout</th>
                        <td>
                            <label class="switch">
                                <input disabled type="checkbox" name="rmenu_force_login" value="1" <?php checked(1, get_option("rmenu_force_login", 0), true); ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "Force login before checkout" to require customers to log in before they can access the checkout page.</span>
                            </span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Link product name in checkout page</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="rmenu_link_product" value="1" <?php checked(1, get_option("rmenu_link_product", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "Link product name in checkout page" to make the product names clickable, redirecting customers to the product page.</span>
                            </span>
                        </td>
                    </tr>
                    <tr valign="top" class="pro-only top-50">
                        <th scope="row">Enable captcha on checkout page</th>
                        <td>
                            <label class="switch">
                                <input disabled type="checkbox" name="rmenu_enable_captcha" value="1" <?php checked(1, get_option("rmenu_enable_captcha", 0), true); ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "Enable captcha on checkout page" to add a captcha verification step to the checkout process, helping to prevent spam and automated submissions.</span>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="tab-content" id="tab-6">
                <?php onepaquc_trust_badges_settings_content(); ?>
            </div>
            <div class="tab-content" id="tab-7">
                <div class="rmenu-settings-header">
                    <h2>WooCommerce Quick View</h2>
                    <p class="rmenu-settings-description">Configure how customers can quickly preview products without visiting the product page.</p>
                </div>
                <div class="rmenu-settings-tabs">
                    <ul class="rmenu-settings-tab-list" style="color: #135e96;display: flex; gap: 10px; cursor: pointer;text-decoration: underline;">
                        <li class="rmenu-settings-tab-item active" data-tab="quick-general-settings">General Settings</li>
                        <li class="rmenu-settings-tab-item" data-tab="quick-popup">Popup Manage</li>
                        <li class="rmenu-settings-tab-item" data-tab="quick-display">Display</li>
                        <li class="rmenu-settings-tab-item" data-tab="quick-advanced">Advanced</li>
                    </ul>
                </div>
                <div class="tab-content" id="quick-general-settings" style="padding: 0;">
                    <div class="rmenu-settings-section">
                        <div class="rmenu-settings-section-header">
                            <h3><span class="dashicons dashicons-admin-generic"></span> General Settings</h3>
                        </div>

                        <div class="rmenu-settings-row" id="rmenu-quick-view-enable-field">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Enable Quick View</label>
                                <div class="rmenu-settings-control">
                                    <label class="rmenu-toggle-switch">
                                        <input type="checkbox" name="rmenu_enable_quick_view" value="1" <?php checked(1, get_option("rmenu_enable_quick_view", 0), true); ?> />
                                        <span class="rmenu-toggle-slider"></span>
                                    </label>
                                    <p class="rmenu-field-description">Enable or disable the quick view functionality across your WooCommerce store.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Button Text</label>
                                <div class="rmenu-settings-control">
                                    <?php
                                    $quick_view_button_text = get_option('rmenu_quick_view_button_text', '');
                                    if (empty($quick_view_button_text)) {
                                        $quick_view_button_text = 'Quick View';
                                    }
                                    ?>
                                    <input type="text" name="rmenu_quick_view_button_text" value="<?php echo esc_attr($quick_view_button_text); ?>" class="regular-text" />
                                    <p class="rmenu-field-description">Customize the text displayed on the quick view button.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Button Position</label>
                                <div class="rmenu-settings-control">
                                    <select name="rmenu_quick_view_button_position" class="rmenu-select">
                                        <option value="after_image" <?php selected(get_option('rmenu_quick_view_button_position', 'after_image'), 'after_image'); ?>>After Product Image</option>
                                        <option disabled value="before_title" <?php selected(get_option('rmenu_quick_view_button_position', 'after_image'), 'before_title'); ?>>Before Product Title (Pro Features)</option>
                                        <option disabled value="after_title" <?php selected(get_option('rmenu_quick_view_button_position', 'after_image'), 'after_title'); ?>>After Product Title (Pro Features)</option>
                                        <option disabled value="before_price" <?php selected(get_option('rmenu_quick_view_button_position', 'after_image'), 'before_price'); ?>>Before Product Price (Pro Features)</option>
                                        <option disabled value="after_price" <?php selected(get_option('rmenu_quick_view_button_position', 'after_image'), 'after_price'); ?>>After Product Price (Pro Features)</option>
                                        <option disabled value="before_add_to_cart" <?php selected(get_option('rmenu_quick_view_button_position', 'after_image'), 'before_add_to_cart'); ?>>Before Add to Cart Button (Pro Features)</option>
                                        <option value="after_add_to_cart" <?php selected(get_option('rmenu_quick_view_button_position', 'after_image'), 'after_add_to_cart'); ?>>After Add to Cart Button</option>
                                        <option value="image_overlay" <?php selected(get_option('rmenu_quick_view_button_position', 'after_image'), 'image_overlay'); ?>>Overlay on Product Image</option>
                                    </select>
                                    <p class="rmenu-field-description">Choose where to display the quick view button on product listings.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Display Type</label>
                                <div class="rmenu-settings-control">
                                    <select name="rmenu_quick_view_display_type" class="rmenu-select">
                                        <option value="button" <?php selected(get_option('rmenu_quick_view_display_type', 'button'), 'button'); ?>>Button</option>
                                        <option value="icon" <?php selected(get_option('rmenu_quick_view_display_type', 'button'), 'icon'); ?>>Icon Only</option>
                                        <option value="text_icon" <?php selected(get_option('rmenu_quick_view_display_type', 'button'), 'text_icon'); ?>>Text with Icon</option>
                                        <option value="hover_icon" <?php selected(get_option('rmenu_quick_view_display_type', 'button'), 'hover_icon'); ?>>Hover Icon</option>
                                    </select>
                                    <p class="rmenu-field-description">Choose how the quick view trigger should appear to customers.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-section quick-view-button-style">
                        <div class="rmenu-settings-section-header">
                            <h3><span class="dashicons dashicons-admin-appearance"></span> Button Style</h3>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Button Style</label>
                                <div class="rmenu-settings-control">
                                    <select name="rmenu_quick_view_button_style" class="rmenu-select" id="rmenu-qv-style-select">
                                        <option value="default" <?php selected(get_option('rmenu_quick_view_button_style', 'default'), 'default'); ?>>Default WooCommerce Style</option>
                                        <option value="alt" <?php selected(get_option('rmenu_quick_view_button_style', 'default'), 'alt'); ?>>Alternative Style</option>
                                        <option value="custom" <?php selected(get_option('rmenu_quick_view_button_style', 'default'), 'custom'); ?>>Custom Style</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row rmenu-settings-row-columns">
                            <div class="rmenu-settings-column">
                                <div class="rmenu-settings-field">
                                    <label class="rmenu-settings-label">Button Color</label>
                                    <div class="rmenu-settings-control">
                                        <input type="color" name="rmenu_quick_view_button_color" value="<?php echo esc_attr(get_option('rmenu_quick_view_button_color', '#96588a')); ?>" class="rmenu-color-picker" />
                                    </div>
                                </div>
                            </div>

                            <div class="rmenu-settings-column">
                                <div class="rmenu-settings-field">
                                    <label class="rmenu-settings-label">Text Color</label>
                                    <div class="rmenu-settings-control">
                                        <input type="color" name="rmenu_quick_view_text_color" value="<?php echo esc_attr(get_option('rmenu_quick_view_text_color', '#ffffff')); ?>" class="rmenu-color-picker" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row rmenu-settings-row-columns">
                            <div class="rmenu-settings-column">
                                <div class="rmenu-settings-field">
                                    <label class="rmenu-settings-label">Button Icon</label>
                                    <div class="rmenu-settings-control">
                                        <select name="rmenu_quick_view_button_icon" class="rmenu-select">
                                            <option value="none" <?php selected(get_option('rmenu_quick_view_button_icon', 'eye'), 'none'); ?>>No Icon</option>
                                            <option value="eye" <?php selected(get_option('rmenu_quick_view_button_icon', 'eye'), 'eye'); ?>>Eye Icon</option>
                                            <option value="search" <?php selected(get_option('rmenu_quick_view_button_icon', 'eye'), 'search'); ?>>Search Icon</option>
                                            <option value="zoom" <?php selected(get_option('rmenu_quick_view_button_icon', 'eye'), 'zoom'); ?>>Zoom Icon</option>
                                            <option value="preview" <?php selected(get_option('rmenu_quick_view_button_icon', 'eye'), 'preview'); ?>>Preview Icon</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="rmenu-settings-column">
                                <div class="rmenu-settings-field">
                                    <label class="rmenu-settings-label">Icon Position</label>
                                    <div class="rmenu-settings-control">
                                        <select name="rmenu_quick_view_icon_position" class="rmenu-select">
                                            <option value="left" <?php selected(get_option('rmenu_quick_view_icon_position', 'left'), 'left'); ?>>Left</option>
                                            <option value="right" <?php selected(get_option('rmenu_quick_view_icon_position', 'left'), 'right'); ?>>Right</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row rmenu-custom-css-row" id="rmenu-qv-custom-css-row" style="<?php echo (get_option('rmenu_quick_view_button_style', 'default') == 'custom') ? 'display:block;' : 'display:none;'; ?>">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Custom CSS</label>
                                <div class="rmenu-settings-control">
                                    <textarea name="rmenu_quick_view_custom_css" class="rmenu-textarea-code" rows="6"><?php echo esc_textarea(get_option('rmenu_quick_view_custom_css', '')); ?></textarea>
                                    <p class="rmenu-field-description">Add custom CSS for advanced button styling. Use the class <code>.rmenu-quick-view-btn</code> to target the button and <code>.rmenu-quick-view-modal</code> to target the modal.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                            const button_style = document.querySelector('div#tab-7 select[name="rmenu_quick_view_button_style"]');

                            // Select all children except the first two in the .button-style-section & except div#rmenu-atc-custom-width-row
                            const buttonStyleSection = document.querySelector('.rmenu-settings-section.quick-view-button-style');
                            const allFields = Array.from(buttonStyleSection ? buttonStyleSection.children : []).slice(2);

                            // if button_style !== 'custom', none all fields except the first two
                            if (button_style) {
                                button_style.addEventListener('change', function() {
                                    if (this.value !== 'default') {
                                        allFields.forEach(field => field.style.display = 'flex');
                                    } else {
                                        allFields.forEach(field => field.style.display = 'none');
                                    }
                                });

                                // Trigger change event on page load to set initial visibility
                                button_style.dispatchEvent(new Event('change'));

                            }

                            // if rmenu_quick_view_button_color (which is bg color) & rmenu_quick_view_text_color (which is text color) both are dark or light, show a warning message
                            const checkoutColor = document.querySelector('input[name="rmenu_quick_view_button_color"]');
                            const checkoutTextColor = document.querySelector('input[name="rmenu_quick_view_text_color"]');
                            if (checkoutColor && checkoutTextColor) {
                                checkoutColor.addEventListener('change', function() {
                                    checkColors(checkoutColor, checkoutTextColor, button_style && button_style.value !== 'default');
                                });
                                checkoutTextColor.addEventListener('change', function() {
                                    checkColors(checkoutColor, checkoutTextColor, button_style && button_style.value !== 'default');
                                });

                                // Initial check on page load
                                checkColors(checkoutColor, checkoutTextColor, button_style && button_style.value !== 'default');
                            }
                        });
                    </script>
                </div>

                <div class="rmenu-settings-section tab-content" id="quick-popup" style="padding: 0;">
                    <div class="rmenu-settings-section-header">
                        <h3><span class="dashicons dashicons-visibility"></span> Quick View Content</h3>
                    </div>

                    <div class="rmenu-settings-row" id="rmenu-quick-view-content-elements">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">Content Elements</label>
                            <?php $content_elements_option = get_option('rmenu_quick_view_content_elements', ['image', 'title', 'rating', 'price', 'excerpt', 'add_to_cart', 'meta']); ?>
                            <div class="rmenu-settings-control rmenu-checkbox-group">
                                <div class="rmenu-checkbox-column">
                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_quick_view_content_elements[]" value="image" <?php checked(in_array('image', $content_elements_option)); ?> />
                                        <span class="rmenu-checkbox-label">Product Image</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_quick_view_content_elements[]" value="gallery" <?php checked(in_array('gallery', $content_elements_option)); ?> />
                                        <span class="rmenu-checkbox-label">Product Gallery</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_quick_view_content_elements[]" value="title" <?php checked(in_array('title', $content_elements_option)); ?> />
                                        <span class="rmenu-checkbox-label">Product Title</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_quick_view_content_elements[]" value="rating" <?php checked(in_array('rating', $content_elements_option)); ?> />
                                        <span class="rmenu-checkbox-label">Product Rating</span>
                                    </label>
                                </div>

                                <div class="rmenu-checkbox-column">
                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_quick_view_content_elements[]" value="price" <?php checked(in_array('price', $content_elements_option)); ?> />
                                        <span class="rmenu-checkbox-label">Product Price</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_quick_view_content_elements[]" value="excerpt" <?php checked(in_array('excerpt', $content_elements_option)); ?> />
                                        <span class="rmenu-checkbox-label">Short Description</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_quick_view_content_elements[]" value="add_to_cart" <?php checked(in_array('add_to_cart', $content_elements_option)); ?> />
                                        <span class="rmenu-checkbox-label">Add to Cart Button</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_quick_view_content_elements[]" value="quantity" <?php checked(in_array('quantity', $content_elements_option)); ?> />
                                        <span class="rmenu-checkbox-label">Quantity Selector</span>
                                    </label>
                                </div>

                                <div class="rmenu-checkbox-column">
                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_quick_view_content_elements[]" value="meta" <?php checked(in_array('meta', $content_elements_option)); ?> />
                                        <span class="rmenu-checkbox-label">Product Meta</span>
                                    </label>

                                    <label class="rmenu-checkbox-container pro-only">
                                        <input disabled type="checkbox" name="rmenu_quick_view_content_elements[]" value="sharing" <?php checked(in_array('sharing', $content_elements_option)); ?> />
                                        <span class="rmenu-checkbox-label">Social Sharing</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_quick_view_content_elements[]" value="view_details" <?php checked(in_array('view_details', $content_elements_option)); ?> />
                                        <span class="rmenu-checkbox-label">View Details Link</span>
                                    </label>

                                    <label class="rmenu-checkbox-container pro-only">
                                        <input disabled type="checkbox" name="rmenu_quick_view_content_elements[]" value="attributes" <?php checked(in_array('attributes', $content_elements_option)); ?> />
                                        <span class="rmenu-checkbox-label">Product Attributes</span>
                                    </label>
                                </div>
                            </div>
                            <p class="rmenu-field-description">Select which product elements should be displayed in the quick view popup.</p>
                        </div>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">Modal Size</label>
                            <div class="rmenu-settings-control pro-only">
                                <select disabled name="rmenu_quick_view_modal_size" class="rmenu-select">
                                    <option value="small" <?php selected(get_option('rmenu_quick_view_modal_size', 'medium'), 'small'); ?>>Small</option>
                                    <option value="medium" <?php selected(get_option('rmenu_quick_view_modal_size', 'medium'), 'medium'); ?>>Medium</option>
                                    <option value="large" <?php selected(get_option('rmenu_quick_view_modal_size', 'medium'), 'large'); ?>>Large</option>
                                    <option value="full" <?php selected(get_option('rmenu_quick_view_modal_size', 'medium'), 'full'); ?>>Full Width</option>
                                    <option value="custom" <?php selected(get_option('rmenu_quick_view_modal_size', 'medium'), 'custom'); ?>>Custom</option>
                                </select>
                                <p class="rmenu-field-description">Choose the size of the quick view modal popup.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-row rmenu-settings-row-columns rmenu-custom-size-row" id="rmenu-custom-size-row" style="<?php echo (get_option('rmenu_quick_view_modal_size', 'medium') == 'custom') ? 'display:flex;' : 'display:none;'; ?>">
                        <div class="rmenu-settings-column">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Custom Width</label>
                                <div class="rmenu-settings-control pro-only">
                                    <input disabled type="text" name="rmenu_quick_view_custom_width" value="<?php echo esc_attr(get_option('rmenu_quick_view_custom_width', '800')); ?>" class="regular-text" />
                                    <p class="rmenu-field-description">Custom width in pixels (e.g., 800).</p>
                                </div>
                            </div>
                        </div>
                        <div class="rmenu-settings-column">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Custom Height</label>
                                <div class="rmenu-settings-control pro-only">
                                    <input disabled type="text" name="rmenu_quick_view_custom_height" value="<?php echo esc_attr(get_option('rmenu_quick_view_custom_height', '600')); ?>" class="regular-text" />
                                    <p class="rmenu-field-description">Custom height in pixels (e.g., 600) or 'auto'.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">Loading Effect</label>
                            <div class="rmenu-settings-control pro-only">
                                <select disabled name="rmenu_quick_view_loading_effect" class="rmenu-select">
                                    <option value="fade" <?php selected(get_option('rmenu_quick_view_loading_effect', 'fade'), 'fade'); ?>>Fade</option>
                                    <option value="slide" <?php selected(get_option('rmenu_quick_view_loading_effect', 'fade'), 'slide'); ?>>Slide</option>
                                    <option value="zoom" <?php selected(get_option('rmenu_quick_view_loading_effect', 'fade'), 'zoom'); ?>>Zoom</option>
                                    <option value="none" <?php selected(get_option('rmenu_quick_view_loading_effect', 'fade'), 'none'); ?>>None</option>
                                </select>
                                <p class="rmenu-field-description">Choose the animation effect when opening the quick view modal.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="quick-display" style="padding: 0px;">
                    <div class="rmenu-settings-section">
                        <div class="rmenu-settings-section-header">
                            <h3><span class="dashicons dashicons-layout"></span> Display Settings</h3>
                        </div>

                        <div class="rmenu-settings-row" >
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Product Types</label>
                                <?php $product_types_option = get_option('rmenu_show_quick_view_by_types', ['simple', 'variable']); ?>
                                <div class="rmenu-settings-control rmenu-checkbox-group">
                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_show_quick_view_by_types[]" value="simple" <?php checked(in_array('simple', $product_types_option)); ?> />
                                        <span class="rmenu-checkbox-label">Simple Products</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_show_quick_view_by_types[]" value="variable" <?php checked(in_array('variable', $product_types_option)); ?> />
                                        <span class="rmenu-checkbox-label">Variable Products</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_show_quick_view_by_types[]" value="grouped" <?php checked(in_array('grouped', $product_types_option)); ?> />
                                        <span class="rmenu-checkbox-label">Grouped Products</span>
                                    </label>

                                    <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_show_quick_view_by_types[]" value="external" <?php checked(in_array('external', $product_types_option)); ?> />
                                        <span class="rmenu-checkbox-label">External/Affiliate Products</span>
                                    </label>
                                </div>
                                <p class="rmenu-field-description">Select which WooCommerce product types should display the quick view button.</p>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <?php $product_pages_option = get_option('rmenu_show_quick_view_by_page', ['shop-page', 'category-archives', 'search']); ?>
                                <div class="rmenu-settings-control rmenu-checkbox-group">
                                    <div class="rmenu-checkbox-column">
                                        <h4>Archive Pages</h4>
                                        <label class="rmenu-checkbox-container">
                                            <input type="checkbox" name="rmenu_show_quick_view_by_page[]" value="shop-page" <?php checked(in_array('shop-page', $product_pages_option)); ?> />
                                            <span class="rmenu-checkbox-label">Main Shop Page</span>
                                        </label>

                                        <label class="rmenu-checkbox-container">
                                            <input type="checkbox" name="rmenu_show_quick_view_by_page[]" value="category-archives" <?php checked(in_array('category-archives', $product_pages_option)); ?> />
                                            <span class="rmenu-checkbox-label">Product Category Archives</span>
                                        </label>

                                        <label class="rmenu-checkbox-container">
                                            <input type="checkbox" name="rmenu_show_quick_view_by_page[]" value="tag-archives" <?php checked(in_array('tag-archives', $product_pages_option)); ?> />
                                            <span class="rmenu-checkbox-label">Product Tag Archives</span>
                                        </label>
                                    </div>

                                    <div class="rmenu-checkbox-column">
                                        <h4>Other Pages</h4>
                                        <label class="rmenu-checkbox-container">
                                            <input type="checkbox" name="rmenu_show_quick_view_by_page[]" value="search" <?php checked(in_array('search', $product_pages_option)); ?> />
                                            <span class="rmenu-checkbox-label">Search Results</span>
                                        </label>

                                        <label class="rmenu-checkbox-container">
                                            <input type="checkbox" name="rmenu_show_quick_view_by_page[]" value="featured-products" <?php checked(in_array('featured-products', $product_pages_option)); ?> />
                                            <span class="rmenu-checkbox-label">Featured Products</span>
                                        </label>

                                        <label class="rmenu-checkbox-container">
                                            <input type="checkbox" name="rmenu_show_quick_view_by_page[]" value="on-sale" <?php checked(in_array('on-sale', $product_pages_option)); ?> />
                                            <span class="rmenu-checkbox-label">On-Sale Products</span>
                                        </label>

                                        <label class="rmenu-checkbox-container">
                                            <input type="checkbox" name="rmenu_show_quick_view_by_page[]" value="recent" <?php checked(in_array('recent', $product_pages_option)); ?> />
                                            <span class="rmenu-checkbox-label">Recent Products</span>
                                        </label>
                                    </div>

                                    <div class="rmenu-checkbox-column">
                                        <h4>Widgets & Shortcodes</h4>
                                        <label class="rmenu-checkbox-container">
                                            <input type="checkbox" name="rmenu_show_quick_view_by_page[]" value="widgets" <?php checked(in_array('widgets', $product_pages_option)); ?> />
                                            <span class="rmenu-checkbox-label">Widgets</span>
                                        </label>

                                        <label class="rmenu-checkbox-container">
                                            <input type="checkbox" name="rmenu_show_quick_view_by_page[]" value="shortcodes" <?php checked(in_array('shortcodes', $product_pages_option)); ?> />
                                            <span class="rmenu-checkbox-label">Shortcodes</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="rmenu-settings-section">
                        <div class="rmenu-settings-section-header">
                            <h3><span class="dashicons dashicons-translation"></span> Translations</h3>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">"View Details" Text</label>
                                <div class="rmenu-settings-control">
                                    <?php
                                    $details_text = get_option('rmenu_quick_view_details_text', '');
                                    if (empty($details_text)) {
                                        $details_text = 'View Full Details';
                                    }
                                    ?>
                                    <input type="text" name="rmenu_quick_view_details_text" value="<?php echo esc_attr($details_text); ?>" class="regular-text" />
                                    <p class="rmenu-field-description">Customize the text for the "View Full Details" link in the quick view popup.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="quick-advanced" style="padding: 0;">
                    <div class="rmenu-settings-section">
                        <div class="rmenu-settings-section-header">
                            <h3><span class="dashicons dashicons-admin-tools"></span> Advanced Options</h3>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Mobile Optimization</label>
                                <div class="rmenu-settings-control pro-only">
                                    <label class="rmenu-toggle-switch">
                                        <input disabled type="checkbox" name="rmenu_quick_view_mobile_optimize" value="1" <?php checked(1, get_option("rmenu_quick_view_mobile_optimize", 1), true); ?> />
                                        <span class="rmenu-toggle-slider"></span>
                                    </label>
                                    <p class="rmenu-field-description">When enabled, the quick view functionality will be optimized for mobile devices.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Close on Add to Cart</label>
                                <div class="rmenu-settings-control pro-only">
                                    <label class="rmenu-toggle-switch">
                                        <input disabled type="checkbox" name="rmenu_quick_view_close_on_add" value="1" <?php checked(1, get_option("rmenu_quick_view_close_on_add", 0), true); ?> />
                                        <span class="rmenu-toggle-slider"></span>
                                    </label>
                                    <p class="rmenu-field-description">When enabled, the quick view popup will automatically close after adding a product to cart.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Keyboard Navigation</label>
                                <div class="rmenu-settings-control">
                                    <label class="rmenu-toggle-switch">
                                        <input type="checkbox" name="rmenu_quick_view_keyboard_nav" value="1" <?php checked(1, get_option("rmenu_quick_view_keyboard_nav", 1), true); ?> />
                                        <span class="rmenu-toggle-slider"></span>
                                    </label>
                                    <p class="rmenu-field-description">When enabled, customers can use keyboard arrows to navigate between products in quick view and ESC to close.</p>
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="rmenu-settings-section">
                        <div class="rmenu-settings-section-header">
                            <h3><span class="dashicons dashicons-analytics"></span> Analytics Integration</h3>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Track Quick View Events</label>
                                <div class="rmenu-settings-control pro-only">
                                    <label class="rmenu-toggle-switch">
                                        <input disabled type="checkbox" name="rmenu_quick_view_track_events" value="1" <?php checked(1, get_option("rmenu_quick_view_track_events", 0), true); ?> />
                                        <span class="rmenu-toggle-slider"></span>
                                    </label>
                                    <p class="rmenu-field-description">Track when customers use quick view in Google Analytics or other analytics tools.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Event Category</label>
                                <div class="rmenu-settings-control pro-only">
                                    <input disabled type="text" name="rmenu_quick_view_event_category" value="<?php echo esc_attr(get_option('rmenu_quick_view_event_category', 'one-page-quick-checkout-for-woocommerce')); ?>" class="regular-text" />
                                    <p class="rmenu-field-description">The event category name used for analytics tracking.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Event Action</label>
                                <div class="rmenu-settings-control pro-only">
                                    <input disabled type="text" name="rmenu_quick_view_event_action" value="<?php echo esc_attr(get_option('rmenu_quick_view_event_action', 'Quick View')); ?>" class="regular-text" />
                                    <p class="rmenu-field-description">The event action name used for analytics tracking.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-section">
                        <div class="rmenu-settings-section-header">
                            <h3><span class="dashicons dashicons-admin-generic"></span> Compatibility Settings</h3>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Load Scripts On</label>
                                <div class="rmenu-settings-control pro-only">
                                    <select disabled name="rmenu_quick_view_load_scripts" class="rmenu-select">
                                        <option value="all" <?php selected(get_option('rmenu_quick_view_load_scripts', 'wc-only'), 'all'); ?>>All Pages</option>
                                        <option value="wc-only" <?php selected(get_option('rmenu_quick_view_load_scripts', 'wc-only'), 'wc-only'); ?>>WooCommerce Pages Only</option>
                                        <option value="specific" <?php selected(get_option('rmenu_quick_view_load_scripts', 'wc-only'), 'specific'); ?>>Specific Pages Only</option>
                                    </select>
                                    <p class="rmenu-field-description">Control where quick view scripts are loaded to improve compatibility and performance.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row" id="rmenu-specific-pages-row" style="<?php echo (get_option('rmenu_quick_view_load_scripts', 'wc-only') == 'specific') ? 'display:block;' : 'display:none;'; ?>">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Specific Pages IDs</label>
                                <div class="rmenu-settings-control pro-only">
                                    <input disabled type="text" name="rmenu_quick_view_specific_pages" value="<?php echo esc_attr(get_option('rmenu_quick_view_specific_pages', '')); ?>" class="regular-text" />
                                    <p class="rmenu-field-description">Enter page IDs separated by commas (e.g., 10, 15, 21).</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Theme Compatibility Mode</label>
                                <div class="rmenu-settings-control">
                                    <label class="rmenu-toggle-switch">
                                        <input type="checkbox" name="rmenu_quick_view_theme_compat" value="1" <?php checked(1, get_option("rmenu_quick_view_theme_compat", 0), true); ?> />
                                        <span class="rmenu-toggle-slider"></span>
                                    </label>
                                    <p class="rmenu-field-description">Enable this if you experience display issues with your theme.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="rmenu-settings-section">
                        <div class="rmenu-settings-section-header">
                            <h3><span class="dashicons dashicons-info"></span> Documentation & Support</h3>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <div class="rmenu-settings-info-box">
                                    <h4>Quick View Documentation</h4>
                                    <p>For detailed instructions on customizing the Quick View feature, please visit our documentation:</p>
                                    <a target="_blank" href="https://plugincy.com/documentations/one-page-quick-checkout-for-woocommerce/quick-view/woocommerce-quick-view-general-settings/" class="button button-secondary">View Documentation</a>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <div class="rmenu-settings-info-box pro-only">
                                    <h4>Shortcode Reference</h4>
                                    <p>You can use the following shortcode to display the Quick View button anywhere on your site:</p>
                                    <code>[plugincy_quick_view product_id="123" button_text="Quick Preview"]</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    jQuery(document).ready(function($) {
                        // Show/hide custom size fields based on modal size selection
                        $('#rmenu-qv-style-select').on('change', function() {
                            if ($(this).val() === 'custom') {
                                $('#rmenu-qv-custom-css-row').show();
                            } else {
                                $('#rmenu-qv-custom-css-row').hide();
                            }
                        });

                        // Show/hide specific pages field
                        $('select[name="rmenu_quick_view_load_scripts"]').on('change', function() {
                            if ($(this).val() === 'specific') {
                                $('#rmenu-specific-pages-row').show();
                            } else {
                                $('#rmenu-specific-pages-row').hide();
                            }
                        });

                        // Show/hide custom size fields based on modal size selection
                        $('select[name="rmenu_quick_view_modal_size"]').on('change', function() {
                            if ($(this).val() === 'custom') {
                                $('#rmenu-custom-size-row').show();
                            } else {
                                $('#rmenu-custom-size-row').hide();
                            }
                        });
                    });
                </script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Tab click handler for Add To Cart settings tabs
                        const tabItems = document.querySelectorAll('#tab-7 .rmenu-settings-tab-item');
                        const tabContents = document.querySelectorAll('#tab-7 > .tab-content');
                        const enableCustomQuickView = document.querySelector('input[name="rmenu_enable_quick_view"]');
                        const highlight_quick_view_enable_Section = document.querySelector('#rmenu-quick-view-enable-field');
                        const highlight_popup_content_element_Section = document.querySelector('#rmenu-quick-view-content-elements');
                        const highlight_quick_view_display_Section = document.querySelector('#quick-display');
                        const productTypes = document.querySelectorAll('input[name="rmenu_show_quick_view_by_types[]"]');
                        const productPages = document.querySelectorAll('input[name="rmenu_show_quick_view_by_page[]"]');

                        tabItems.forEach(function(tab) {
                            tab.addEventListener('click', function() {
                                // Remove active class from all tabs
                                tabItems.forEach(function(t) {
                                    t.classList.remove('active');
                                });
                                // Hide all tab contents
                                tabContents.forEach(function(content) {
                                    content.style.display = 'none';
                                });

                                // Add active class to clicked tab
                                tab.classList.add('active');
                                // Show the corresponding tab content
                                const tabId = tab.getAttribute('data-tab');
                                if (tabId !== "quick-general-settings" && enableCustomQuickView && !enableCustomQuickView.checked) {
                                    showDirectCheckoutWarning(
                                        highlight_quick_view_enable_Section,
                                        '<b>Enable Custom Quick View</b> in the general settings tab to access these options.'
                                    );
                                }
                                const content = document.getElementById(tabId);
                                if (content) {
                                    content.style.display = 'block';
                                }
                            });
                        });

                        enableCustomQuickView.addEventListener('change', function() {
                            if (this.checked) {
                                removeDirectCheckoutWarning(highlight_quick_view_enable_Section);
                                // in rmenu_quick_view_content_elements[] if no checkbox is checked. show a warning
                                const contentElements = document.querySelectorAll('input[name="rmenu_quick_view_content_elements[]"]');
                                let isAnyChecked = false;
                                contentElements.forEach(function(element) {
                                    if (element.checked) {
                                        isAnyChecked = true;
                                    }
                                });
                                if (!isAnyChecked) {
                                    showDirectCheckoutWarning(
                                        highlight_popup_content_element_Section,
                                        '<b>Select at least one content element</b> in the Popup Manage tab to view the changes.'
                                    );
                                }

                                // if rmenu_show_quick_view_by_types[] & rmenu_show_quick_view_by_page[] are empty, show a warning
                                let isAnyTypeChecked = false;
                                let isAnyPageChecked = false;
                                productTypes.forEach(function(element) {
                                    if (element.checked) {
                                        isAnyTypeChecked = true;
                                    }
                                });
                                productPages.forEach(function(element) {
                                    if (element.checked) {
                                        isAnyPageChecked = true;
                                    }
                                });
                                if (!isAnyTypeChecked && !isAnyPageChecked) {
                                    showDirectCheckoutWarning(
                                        highlight_quick_view_display_Section,
                                        '<b>Select at least one product type</b> and <b>one product page</b> in the Display tab to view the changes.'
                                    );
                                }
                            }
                        });

                        // on changes in each productPages & productTypes. if productPages & productTypes  are empty show warning
                        productTypes.forEach(function(element) {
                            element.addEventListener('change', function() {
                                let isAnyTypeChecked = false;
                                let isAnyPageChecked = false;
                                productTypes.forEach(function(el) {
                                    if (el.checked) {
                                        isAnyTypeChecked = true;
                                    }
                                });
                                productPages.forEach(function(el) {
                                    if (el.checked) {
                                        isAnyPageChecked = true;
                                    }
                                });
                                if (!isAnyTypeChecked || !isAnyPageChecked) {
                                    showDirectCheckoutWarning(
                                        highlight_quick_view_display_Section,
                                        '<b>Select at least one product type</b> and <b>one product page</b> in the Display tab to view the changes.'
                                    );
                                } else {
                                    removeDirectCheckoutWarning(highlight_quick_view_display_Section);
                                }
                            });
                        });
                        productPages.forEach(function(element) {
                            element.addEventListener('change', function() {
                                let isAnyTypeChecked = false;
                                let isAnyPageChecked = false;
                                productTypes.forEach(function(el) {
                                    if (el.checked) {
                                        isAnyTypeChecked = true;
                                    }
                                });
                                productPages.forEach(function(el) {
                                    if (el.checked) {
                                        isAnyPageChecked = true;
                                    }
                                });
                                if (!isAnyTypeChecked || !isAnyPageChecked) {
                                    showDirectCheckoutWarning(
                                        highlight_quick_view_display_Section,
                                        '<b>Select at least one product type</b> and <b>one product page</b> in the Display tab to view the changes.'
                                    );
                                } else {
                                    removeDirectCheckoutWarning(highlight_quick_view_display_Section);
                                }
                            });
                        });



                        // on changes in rmenu_quick_view_content_elements[] if no checkbox is checked. show a warning
                        const contentElements = document.querySelectorAll('input[name="rmenu_quick_view_content_elements[]"]');
                        contentElements.forEach(function(element) {
                            element.addEventListener('change', function() {
                                let isAnyChecked = false;
                                contentElements.forEach(function(el) {
                                    if (el.checked) {
                                        isAnyChecked = true;
                                        removeDirectCheckoutWarning(highlight_popup_content_element_Section);
                                    }
                                });
                                if (!isAnyChecked) {
                                    showDirectCheckoutWarning(
                                        highlight_popup_content_element_Section,
                                        '<b>Select at least one content element</b> in the Popup Manage tab to view the changes.'
                                    );
                                }

                                // if rmenu_show_quick_view_by_types[] & rmenu_show_quick_view_by_page[] are empty, show a warning                                
                                let isAnyTypeChecked = false;
                                let isAnyPageChecked = false;
                                productTypes.forEach(function(element) {
                                    if (element.checked) {
                                        isAnyTypeChecked = true;
                                    }
                                });
                                productPages.forEach(function(element) {
                                    if (element.checked) {
                                        isAnyPageChecked = true;
                                    }
                                });
                                if (!isAnyTypeChecked && !isAnyPageChecked) {
                                    showDirectCheckoutWarning(
                                        highlight_quick_view_display_Section,
                                        '<b>Select at least one product type</b> and <b>one product page</b> in the Display tab to view the changes.'
                                    );
                                }
                            });
                        });

                        // Show only the first tab content by default
                        tabContents.forEach(function(content, idx) {
                            content.style.display = (idx === 0) ? 'block' : 'none';
                        });
                    });
                </script>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                    const enableCheckout = document.querySelector('div#tab-7 input[name="rmenu_enable_quick_view"]');
                    // if rmenu_quick_view_display_type is icon, disable the rmenu_quick_view_button_text
                    const quickViewDisplayType = document.querySelector('div#tab-7 select[name="rmenu_quick_view_display_type"]');
                    const quickViewButtonText = document.querySelector('div#tab-7 input[name="rmenu_quick_view_button_text"]');
                    // if rmenu_quick_view_button_icon is none, show warning
                    const quickViewButtonIcon = document.querySelector('div#tab-7 select[name="rmenu_quick_view_button_icon"]');
                    const heighlight_quick_view_button_style = document.querySelector('#rmenu-quick-view-button-style-section');
                    quickViewDisplayType.addEventListener('change', function() {
                        if (this.value === 'icon') {
                            quickViewButtonText.disabled = true;
                        } else {
                            quickViewButtonText.disabled = false;
                        }

                        if (this.value !== 'button') {
                            if (quickViewButtonIcon.value === 'none') {
                                showDirectCheckoutWarning(
                                    heighlight_quick_view_button_style,
                                    'Please select an icon for the Quick View button.'
                                );
                            }
                        } else {
                            removeDirectCheckoutWarning(heighlight_quick_view_button_style);
                        }
                    });

                    quickViewButtonIcon.addEventListener('change', function() {
                        if (this.value === 'none' && quickViewDisplayType.value !== 'button') {
                            showDirectCheckoutWarning(
                                heighlight_quick_view_button_style,
                                'Please select an icon for the Quick View button.'
                            );
                        } else {
                            removeDirectCheckoutWarning(heighlight_quick_view_button_style);
                        }
                    });

                    const allinputFields = Array.from(document.querySelectorAll('div#tab-7 input, div#tab-7 select')).filter(
                        el => !(el.name === "rmenu_enable_quick_view")
                    );
                    allinputFields.forEach(field => {
                        field.disabled = !enableCheckout.checked;
                    });
                    enableCheckout.addEventListener('change', function() {
                        allinputFields.forEach(field => {
                            field.disabled = !this.checked;
                        });
                    });

                    // Show/hide custom CSS row based on selected style after page load
                    const styleSelect = document.querySelector('select[name="rmenu_quick_view_button_style"]');
                    const customCssRow = document.querySelector('textarea[name="rmenu_quick_view_custom_css"]').closest('.rmenu-settings-row');
                    // if rmenu_quick_view_display_type is button, hide the rmenu_quick_view_button_icon & rmenu_quick_view_icon_position
                    const quickViewButtonIconRow = quickViewButtonIcon.closest('.rmenu-settings-row');
                    const updateQuickViewDisplayType = () => {
                        if (quickViewDisplayType.value === 'button') {
                            quickViewButtonIconRow.style.display = 'none';
                        } else {
                            quickViewButtonIconRow.style.display = 'flex';
                        }
                    };

                    const updateCustomCssRowVisibility = () => {
                        if (styleSelect.value === 'custom') {
                            customCssRow.style.display = 'block';
                        } else {
                            customCssRow.style.display = 'none';
                        }
                        updateQuickViewDisplayType();
                    };

                    // Initial check
                    updateCustomCssRowVisibility();

                    // Add change event listener
                    styleSelect.addEventListener('change', updateCustomCssRowVisibility);

                    updateQuickViewDisplayType();
                    quickViewDisplayType.addEventListener('change', updateQuickViewDisplayType);
                });
            </script>
            <div class="tab-content" id="tab-8">
                <div class="rmenu-settings-header">
                    <h2>WooCommerce Add To Cart</h2>
                    <p class="rmenu-settings-description">Customize the Add to Cart button appearance and behavior throughout your store.</p>
                </div>

                <div class="rmenu-settings-tabs">
                    <ul class="rmenu-settings-tab-list" style="color: #135e96;display: flex; gap: 10px; cursor: pointer;text-decoration: underline;">
                        <li class="rmenu-settings-tab-item active" data-tab="general-settings">General Settings</li>
                        <li class="rmenu-settings-tab-item" data-tab="button-behavior">Button Behavior</li>
                        <li class="rmenu-settings-tab-item" data-tab="advanced">Advanced</li>
                    </ul>
                </div>

                <div class="tab-content" id="general-settings" style="padding: 0;">
                    <div class="rmenu-settings-section">
                        <div class="rmenu-settings-section-header">
                            <h3><span class="dashicons dashicons-admin-generic"></span> General Settings</h3>
                        </div>

                        <div class="rmenu-settings-row" id="rmenu-enable-custom-add-to-cart">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Enable Custom Add to Cart</label>
                                <div class="rmenu-settings-control">
                                    <label class="rmenu-toggle-switch">
                                        <input type="checkbox" name="rmenu_enable_custom_add_to_cart" value="1" <?php checked(1, get_option("rmenu_enable_custom_add_to_cart", 0), true); ?> />
                                        <span class="rmenu-toggle-slider"></span>
                                    </label>
                                    <p class="rmenu-field-description">Enable or disable custom Add to Cart styling and functionality.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Button Text</label>
                                <div class="rmenu-settings-control">
                                    <input type="text" name="txt-add-to-cart" value="<?php echo esc_attr(get_option('txt-add-to-cart', 'Add to Cart')); ?>" class="regular-text" />
                                    <p class="rmenu-field-description">Customize the text displayed on the Add to Cart button for simple products.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Variable Product Button Text</label>
                                <div class="rmenu-settings-control">
                                    <input type="text" name="txt-select-options" value="<?php echo esc_attr(get_option('txt-select-options', 'Select Options')); ?>" class="regular-text" />
                                    <p class="rmenu-field-description">Customize the text displayed on the Add to Cart button for variable products.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Read More Button Text</label>
                                <div class="rmenu-settings-control">
                                    <input type="text" name="txt-read-more" value="<?php echo esc_attr(get_option('txt-read-more', 'Select Options')); ?>" class="regular-text" />
                                    <p class="rmenu-field-description">Customize the text displayed on the Add to Cart button for variable products.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Grouped Product Button Text</label>
                                <div class="rmenu-settings-control">
                                    <input type="text" name="rmenu_grouped_add_to_cart_text" value="<?php echo esc_attr(get_option('rmenu_grouped_add_to_cart_text', 'View Products')); ?>" class="regular-text" />
                                    <p class="rmenu-field-description">Customize the text displayed on the Add to Cart button for grouped products.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-section button-style-section">
                        <div class="rmenu-settings-section-header">
                            <h3><span class="dashicons dashicons-admin-appearance"></span> Button Style</h3>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Button Style</label>
                                <div class="rmenu-settings-control">
                                    <select name="rmenu_add_to_cart_style" class="rmenu-select" id="rmenu-atc-style-select">
                                        <option value="default" <?php selected(get_option('rmenu_add_to_cart_style', 'default'), 'default'); ?>>Default WooCommerce Style</option>
                                        <option disabled value="modern" <?php selected(get_option('rmenu_add_to_cart_style', 'default'), 'modern'); ?>>Modern Style (Pro Feature)</option>
                                        <option value="rounded" <?php selected(get_option('rmenu_add_to_cart_style', 'default'), 'rounded'); ?>>Rounded Style</option>
                                        <option disabled value="minimal" <?php selected(get_option('rmenu_add_to_cart_style', 'default'), 'minimal'); ?>>Minimal Style (Pro Feature)</option>
                                        <option value="custom" <?php selected(get_option('rmenu_add_to_cart_style', 'default'), 'custom'); ?>>Custom Style</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row rmenu-settings-row-columns">
                            <div class="rmenu-settings-column">
                                <div class="rmenu-settings-field">
                                    <label class="rmenu-settings-label">Button Color</label>
                                    <div class="rmenu-settings-control">
                                        <input type="color" name="rmenu_add_to_cart_bg_color" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_bg_color', '#96588a')); ?>" class="rmenu-color-picker" />
                                    </div>
                                </div>
                            </div>

                            <div class="rmenu-settings-column">
                                <div class="rmenu-settings-field">
                                    <label class="rmenu-settings-label">Text Color</label>
                                    <div class="rmenu-settings-control">
                                        <input type="color" name="rmenu_add_to_cart_text_color" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_text_color', '#ffffff')); ?>" class="rmenu-color-picker" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row rmenu-settings-row-columns">
                            <div class="rmenu-settings-column">
                                <div class="rmenu-settings-field">
                                    <label class="rmenu-settings-label">Hover Background Color</label>
                                    <div class="rmenu-settings-control">
                                        <input type="color" name="rmenu_add_to_cart_hover_bg_color" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_hover_bg_color', '#7f4579')); ?>" class="rmenu-color-picker" />
                                    </div>
                                </div>
                            </div>

                            <div class="rmenu-settings-column">
                                <div class="rmenu-settings-field">
                                    <label class="rmenu-settings-label">Hover Text Color</label>
                                    <div class="rmenu-settings-control">
                                        <input type="color" name="rmenu_add_to_cart_hover_text_color" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_hover_text_color', '#ffffff')); ?>" class="rmenu-color-picker" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row rmenu-settings-row-columns">
                            <div class="rmenu-settings-column">
                                <div class="rmenu-settings-field">
                                    <label class="rmenu-settings-label">Border Radius</label>
                                    <div class="rmenu-settings-control">
                                        <input type="number" name="rmenu_add_to_cart_border_radius" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_border_radius', '3')); ?>" class="small-text" min="0" max="50" step="1" />
                                        <span class="rmenu-unit">px</span>
                                    </div>
                                </div>
                            </div>

                            <div class="rmenu-settings-column">
                                <div class="rmenu-settings-field">
                                    <label class="rmenu-settings-label">Button Font Size</label>
                                    <div class="rmenu-settings-control">
                                        <input type="number" name="rmenu_add_to_cart_font_size" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_font_size', '14')); ?>" class="small-text" min="10" max="24" step="1" />
                                        <span class="rmenu-unit">px</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Button Width</label>
                                <div class="rmenu-settings-control">
                                    <select name="rmenu_add_to_cart_width" class="rmenu-select">
                                        <option value="auto" <?php selected(get_option('rmenu_add_to_cart_width', 'auto'), 'auto'); ?>>Auto</option>
                                        <option value="full" <?php selected(get_option('rmenu_add_to_cart_width', 'auto'), 'full'); ?>>Full Width</option>
                                        <option value="custom" <?php selected(get_option('rmenu_add_to_cart_width', 'auto'), 'custom'); ?>>Custom Width</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row" id="rmenu-atc-custom-width-row" style="<?php echo (get_option('rmenu_add_to_cart_width', 'auto') == 'custom') ? 'display:block;' : 'display:none;'; ?>">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Custom Width Value</label>
                                <div class="rmenu-settings-control">
                                    <input type="number" name="rmenu_add_to_cart_custom_width" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_custom_width', '150')); ?>" class="small-text" min="50" max="500" step="1" />
                                    <span class="rmenu-unit">px</span>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row rmenu-settings-row-columns">
                            <div class="rmenu-settings-column">
                                <div class="rmenu-settings-field">
                                    <label class="rmenu-settings-label">Button Icon</label>
                                    <div class="rmenu-settings-control">
                                        <select name="rmenu_add_to_cart_icon" class="rmenu-select">
                                            <option value="none" <?php selected(get_option('rmenu_add_to_cart_icon', 'none'), 'none'); ?>>No Icon</option>
                                            <option value="cart" <?php selected(get_option('rmenu_add_to_cart_icon', 'none'), 'cart'); ?>>Cart Icon</option>
                                            <option value="plus" <?php selected(get_option('rmenu_add_to_cart_icon', 'none'), 'plus'); ?>>Plus Icon</option>
                                            <option value="bag" <?php selected(get_option('rmenu_add_to_cart_icon', 'none'), 'bag'); ?>>Shopping Bag Icon</option>
                                            <option value="basket" <?php selected(get_option('rmenu_add_to_cart_icon', 'none'), 'basket'); ?>>Basket Icon</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="rmenu-settings-column" id="rmenu-atc-icon-position-row">
                                <div class="rmenu-settings-field">
                                    <label class="rmenu-settings-label">Icon Position</label>
                                    <div class="rmenu-settings-control">
                                        <select name="rmenu_add_to_cart_icon_position" class="rmenu-select">
                                            <option value="left" <?php selected(get_option('rmenu_add_to_cart_icon_position', 'left'), 'left'); ?>>Left</option>
                                            <option value="right" <?php selected(get_option('rmenu_add_to_cart_icon_position', 'left'), 'right'); ?>>Right</option>
                                            <option value="top" <?php selected(get_option('rmenu_add_to_cart_icon_position', 'left'), 'top'); ?>>Top</option>
                                            <option value="bottom" <?php selected(get_option('rmenu_add_to_cart_icon_position', 'left'), 'bottom'); ?>>Bottom</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row rmenu-custom-css-row" id="rmenu-atc-custom-css-row" style="<?php echo (get_option('rmenu_add_to_cart_style', 'default') == 'custom') ? 'display:block;' : 'display:none;'; ?>">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Custom CSS</label>
                                <div class="rmenu-settings-control">
                                    <textarea name="rmenu_add_to_cart_custom_css" class="rmenu-textarea-code" rows="6"><?php echo esc_textarea(get_option('rmenu_add_to_cart_custom_css', '')); ?></textarea>
                                    <p class="rmenu-field-description">Add custom CSS for advanced button styling. Use the class <code>.rmenu-add-to-cart-btn</code> to target the button.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                            const button_style = document.querySelector('div#tab-8 select[name="rmenu_add_to_cart_style"]');

                            // Select all children except the first two in the .button-style-section & except div#rmenu-atc-custom-width-row
                            const buttonStyleSection = document.querySelector('.rmenu-settings-section.button-style-section');
                            const allFields = Array.from(buttonStyleSection ? buttonStyleSection.children : []).slice(2);
                            const customWidthRow = document.getElementById('rmenu-atc-custom-width-row');
                            // if rmenu_add_to_cart_icon is none, hide the rmenu-atc-icon-position-row
                            const iconPositionRow = document.getElementById('rmenu-atc-icon-position-row');
                            const iconSelect = document.querySelector('select[name="rmenu_add_to_cart_icon"]');

                            if (iconSelect && iconPositionRow) {
                                iconSelect.addEventListener('change', function() {
                                    if (this.value === 'none') {
                                        iconPositionRow.style.display = 'none';
                                    } else {
                                        iconPositionRow.style.display = 'block';
                                    }
                                });

                                // Trigger change event on page load to set initial visibility
                                iconSelect.dispatchEvent(new Event('change'));
                            }

                            if (customWidthRow) {
                                allFields.splice(allFields.indexOf(customWidthRow), 1); // Remove custom width row from the list
                            }


                            // if button_style !== 'custom', none all fields except the first two
                            if (button_style) {
                                button_style.addEventListener('change', function() {
                                    if (this.value !== 'default') {
                                        allFields.forEach(field => field.style.display = 'flex');
                                        if (this.value !== 'custom') {
                                            document.getElementById('rmenu-atc-custom-css-row').style.display = 'none';
                                        } else {
                                            document.getElementById('rmenu-atc-custom-css-row').style.display = 'block';
                                        }
                                    } else {
                                        allFields.forEach(field => field.style.display = 'none');
                                    }
                                });

                                // Trigger change event on page load to set initial visibility
                                button_style.dispatchEvent(new Event('change'));

                            }

                            // if rmenu_add_to_cart_bg_color (which is bg color) & rmenu_add_to_cart_text_color (which is text color) both are dark or light, show a warning message
                            const checkoutColor = document.querySelector('input[name="rmenu_add_to_cart_bg_color"]');
                            const checkoutTextColor = document.querySelector('input[name="rmenu_add_to_cart_text_color"]');
                            if (checkoutColor && checkoutTextColor) {
                                checkoutColor.addEventListener('change', function() {
                                    checkColors(checkoutColor, checkoutTextColor, button_style && button_style.value !== 'default');
                                });
                                checkoutTextColor.addEventListener('change', function() {
                                    checkColors(checkoutColor, checkoutTextColor, button_style && button_style.value !== 'default');
                                });

                                // Initial check on page load
                                checkColors(checkoutColor, checkoutTextColor, button_style && button_style.value !== 'default');
                            }

                            // if rmenu_add_to_cart_hover_bg_color (which is bg color) & rmenu_add_to_cart_hover_text_color (which is text color) both are dark or light, show a warning message
                            const checkoutHoverColor = document.querySelector('input[name="rmenu_add_to_cart_hover_bg_color"]');
                            const checkoutHoverTextColor = document.querySelector('input[name="rmenu_add_to_cart_hover_text_color"]');
                            if (checkoutHoverColor && checkoutHoverTextColor) {
                                checkoutHoverColor.addEventListener('change', function() {
                                    checkColors(checkoutHoverColor, checkoutHoverTextColor, button_style && button_style.value !== 'default');
                                });
                                checkoutHoverTextColor.addEventListener('change', function() {
                                    checkColors(checkoutHoverColor, checkoutHoverTextColor, button_style && button_style.value !== 'default');
                                });

                                // Initial check on page load
                                checkColors(checkoutHoverColor, checkoutHoverTextColor, button_style && button_style.value !== 'default');
                            }
                        });
                    </script>
                </div>
                <div class="tab-content" id="button-behavior" style="padding: 0;">
                    <div class="rmenu-settings-section">
                        <div class="rmenu-settings-section-header">
                            <h3><span class="dashicons dashicons-layout"></span> Display Settings</h3>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Button Display on Archive Pages</label>
                                <div class="rmenu-settings-control">
                                    <select name="rmenu_add_to_cart_catalog_display" class="rmenu-select">
                                        <option value="default" <?php selected(get_option('rmenu_add_to_cart_catalog_display', 'default'), 'default'); ?>>Default (WooCommerce Setting)</option>
                                        <option value="show" <?php selected(get_option('rmenu_add_to_cart_catalog_display', 'default'), 'show'); ?>>Always Show</option>
                                        <option value="hide" <?php selected(get_option('rmenu_add_to_cart_catalog_display', 'default'), 'hide'); ?>>Always Hide</option>
                                    </select>
                                    <p class="rmenu-field-description">Control how Add to Cart buttons appear on product archive pages.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-section" id="add_to_cart_behave">
                        <div class="rmenu-settings-section-header">
                            <h3><span class="dashicons dashicons-cart"></span> Add To Cart Behavior</h3>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Enable AJAX Add to Cart</label>
                                <div class="rmenu-settings-control">
                                    <label class="rmenu-toggle-switch">
                                        <input type="checkbox" name="rmenu_enable_ajax_add_to_cart" value="1" <?php checked(1, get_option("rmenu_enable_ajax_add_to_cart", 1), true); ?> />
                                        <span class="rmenu-toggle-slider"></span>
                                    </label>
                                    <p class="rmenu-field-description">Add products to cart without page reload using AJAX.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Default Quantity</label>
                                <div class="rmenu-settings-control pro-only">
                                    <input disabled type="number" name="rmenu_add_to_cart_default_qty" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_default_qty', '1')); ?>" class="small-text" min="1" max="100" step="1" />
                                    <p class="rmenu-field-description">Set the default quantity when adding products to cart from archive pages.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Quantity Selector on Archives</label>
                                <div class="rmenu-settings-control pro-only">
                                    <label class="rmenu-toggle-switch">
                                        <input disabled type="checkbox" name="rmenu_show_quantity_archive" value="1" <?php checked(1, get_option("rmenu_show_quantity_archive", 0), true); ?> />
                                        <span class="rmenu-toggle-slider"></span>
                                    </label>
                                    <p class="rmenu-field-description">Display quantity selector on shop/archive pages before adding to cart.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Redirect After Add to Cart</label>
                                <div class="rmenu-settings-control">
                                    <select name="rmenu_redirect_after_add" class="rmenu-select">
                                        <option value="none" <?php selected(get_option('rmenu_redirect_after_add', 'none'), 'none'); ?>>No Redirect</option>
                                        <!-- rmenu_disable_cart_page is it's on disable below option & show cart page is disabled -->
                                        <?php
                                        $disable_cart_page = get_option('rmenu_disable_cart_page', '0');
                                        ?>
                                        <option value="cart" <?php selected(get_option('rmenu_redirect_after_add', 'none'), 'cart'); ?> <?php echo ($disable_cart_page == '1') ? 'disabled' : ''; ?>>Cart Page <?php echo ($disable_cart_page == '1') ? '(Disabled)' : ''; ?></option>
                                        <option value="checkout" <?php selected(get_option('rmenu_redirect_after_add', 'none'), 'checkout'); ?>>Checkout Page</option>
                                    </select>
                                    <p class="rmenu-field-description">Choose whether to redirect customers after adding products to cart.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Add to Cart Animation</label>
                                <div class="rmenu-settings-control">
                                    <select name="rmenu_add_to_cart_animation" class="rmenu-select">
                                        <option value="none" <?php selected(get_option('rmenu_add_to_cart_animation', 'slide'), 'none'); ?>>None</option>
                                        <option value="slide" <?php selected(get_option('rmenu_add_to_cart_animation', 'slide'), 'slide'); ?>>Slide Effect</option>
                                        <option value="fade" <?php selected(get_option('rmenu_add_to_cart_animation', 'slide'), 'fade'); ?>>Fade Effect</option>
                                        <option value="fly" <?php selected(get_option('rmenu_add_to_cart_animation', 'slide'), 'fly'); ?>>Fly to Cart Effect</option>
                                    </select>
                                    <p class="rmenu-field-description">Choose the animation effect when products are added to cart.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-section" id="add_to_cart_notification">
                        <div class="rmenu-settings-section-header">
                            <h3><span class="dashicons dashicons-megaphone"></span> Notifications</h3>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Notification Style</label>
                                <div class="rmenu-settings-control pro-only">
                                    <select disabled name="rmenu_add_to_cart_notification_style" class="rmenu-select">
                                        <option value="default" <?php selected(get_option('rmenu_add_to_cart_notification_style', 'default'), 'default'); ?>>Default WooCommerce Notices</option>
                                        <option value="popup" <?php selected(get_option('rmenu_add_to_cart_notification_style', 'default'), 'popup'); ?>>Popup Message</option>
                                        <option value="toast" <?php selected(get_option('rmenu_add_to_cart_notification_style', 'default'), 'toast'); ?>>Toast Notification</option>
                                        <option value="mini_cart" <?php selected(get_option('rmenu_add_to_cart_notification_style', 'default'), 'mini_cart'); ?>>Mini Cart Preview</option>
                                    </select>
                                    <p class="rmenu-field-description">Choose how to display notifications when products are added to cart.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Success Message</label>
                                <div class="rmenu-settings-control pro-only">
                                    <input disabled type="text" name="rmenu_add_to_cart_success_message" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_success_message', '{product} has been added to your cart.')); ?>" class="regular-text" />
                                    <p class="rmenu-field-description">Customize the success message shown after adding to cart. Use {product} as a placeholder for the product name.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Show View Cart Link</label>
                                <div class="rmenu-settings-control pro-only">
                                    <label class="rmenu-toggle-switch">
                                        <input disabled type="checkbox" name="rmenu_show_view_cart_link" value="1" <?php checked(1, get_option("rmenu_show_view_cart_link", 1), true); ?> />
                                        <span class="rmenu-toggle-slider"></span>
                                    </label>
                                    <p class="rmenu-field-description">Display a "View Cart" link in the notification message.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Show Checkout Link</label>
                                <div class="rmenu-settings-control pro-only">
                                    <label class="rmenu-toggle-switch">
                                        <input disabled type="checkbox" name="rmenu_show_checkout_link" value="1" <?php checked(1, get_option("rmenu_show_checkout_link", 0), true); ?> />
                                        <span class="rmenu-toggle-slider"></span>
                                    </label>
                                    <p class="rmenu-field-description">Display a "Checkout" link in the notification message.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Notification Duration</label>
                                <div class="rmenu-settings-control pro-only">
                                    <input disabled type="number" name="rmenu_add_to_cart_notification_duration" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_notification_duration', '3000')); ?>" class="small-text" min="1000" max="10000" step="500" />
                                    <span class="rmenu-unit">ms</span>
                                    <p class="rmenu-field-description">How long to display the notification for (in milliseconds).</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const checkbox = document.querySelector('input[name="rmenu_enable_ajax_add_to_cart"]');
                            const settingsRows = document.querySelectorAll('#add_to_cart_behave .rmenu-settings-row, #add_to_cart_notification .rmenu-settings-row');
                            const settingsInputs = document.querySelectorAll('#add_to_cart_behave .rmenu-settings-row input, #add_to_cart_notification .rmenu-settings-row input');

                            function updateSettings() {
                                for (let i = 1; i < settingsRows.length; i++) { // Start loop at index 1 (second element)
                                    const row = settingsRows[i];
                                    const inputs = row.querySelectorAll('input'); // Get inputs within this row
                                    const selects = row.querySelectorAll('select'); // Get selects within this row

                                    if (checkbox.checked) {
                                        row.style.opacity = '1';
                                        inputs.forEach(input => {
                                            input.disabled = false;
                                        });
                                        selects.forEach(select => {
                                            select.disabled = false;
                                        });
                                    } else {
                                        row.style.opacity = '0.5';
                                        inputs.forEach(input => {
                                            input.disabled = true;
                                        });
                                        selects.forEach(select => {
                                            select.disabled = true;
                                        });
                                    }
                                }
                            }

                            // Initial update on page load
                            updateSettings();

                            // Update when the checkbox changes
                            checkbox.addEventListener('change', updateSettings);
                        });
                    </script>
                </div>
                <div class="tab-content" id="advanced" style="padding: 0;">
                    <div class="rmenu-settings-section">
                        <div class="rmenu-settings-section-header">
                            <h3><span class="dashicons dashicons-smartphone"></span> Mobile Settings</h3>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Sticky Add to Cart on Mobile</label>
                                <div class="rmenu-settings-control pro-only">
                                    <label class="rmenu-toggle-switch">
                                        <input disabled type="checkbox" name="rmenu_sticky_add_to_cart_mobile" value="1" <?php checked(1, get_option("rmenu_sticky_add_to_cart_mobile", 0), true); ?> />
                                        <span class="rmenu-toggle-slider"></span>
                                    </label>
                                    <p class="rmenu-field-description">Keep the Add to Cart button visible at the bottom of the screen on mobile devices.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Mobile Button Text</label>
                                <div class="rmenu-settings-control pro-only">
                                    <input disabled type="text" name="rmenu_mobile_add_to_cart_text" value="<?php echo esc_attr(get_option('rmenu_mobile_add_to_cart_text', '')); ?>" class="regular-text" />
                                    <p class="rmenu-field-description">Set a different button text for mobile devices. Leave empty to use the default text.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Mobile Button Size</label>
                                <div class="rmenu-settings-control pro-only">
                                    <select disabled name="rmenu_mobile_button_size" class="rmenu-select">
                                        <option value="default" <?php selected(get_option('rmenu_mobile_button_size', 'default'), 'default'); ?>>Same as Desktop</option>
                                        <option value="larger" <?php selected(get_option('rmenu_mobile_button_size', 'default'), 'larger'); ?>>Larger</option>
                                        <option value="smaller" <?php selected(get_option('rmenu_mobile_button_size', 'default'), 'smaller'); ?>>Smaller</option>
                                        <option value="full" <?php selected(get_option('rmenu_mobile_button_size', 'default'), 'full'); ?>>Full Width</option>
                                    </select>
                                    <p class="rmenu-field-description">Choose button size optimization for mobile devices.</p>
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Mobile Button Icon Only</label>
                                <div class="rmenu-settings-control pro-only">
                                    <label class="rmenu-toggle-switch">
                                        <input disabled type="checkbox" name="rmenu_mobile_icon_only" value="1" <?php checked(1, get_option("rmenu_mobile_icon_only", 0), true); ?> />
                                        <span class="rmenu-toggle-slider"></span>
                                    </label>
                                    <p class="rmenu-field-description">Show only the icon (without text) on mobile devices to save space.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-section">
                        <div class="rmenu-settings-section-header">
                            <h3><span class="dashicons dashicons-welcome-widgets-menus"></span> Advanced Options</h3>
                        </div>
                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Add to Cart Load Effect</label>
                                <div class="rmenu-settings-control pro-only">
                                    <select disabled name="rmenu_add_to_cart_loading_effect" class="rmenu-select">
                                        <option value="none" <?php selected(get_option('rmenu_add_to_cart_loading_effect', 'spinner'), 'none'); ?>>None</option>
                                        <option value="spinner" <?php selected(get_option('rmenu_add_to_cart_loading_effect', 'spinner'), 'spinner'); ?>>Spinner</option>
                                        <option value="dots" <?php selected(get_option('rmenu_add_to_cart_loading_effect', 'spinner'), 'dots'); ?>>Dots</option>
                                        <option value="pulse" <?php selected(get_option('rmenu_add_to_cart_loading_effect', 'spinner'), 'pulse'); ?>>Pulse</option>
                                    </select>
                                    <p class="rmenu-field-description">Choose an animation effect while adding to cart is in progress.</p>
                                </div>
                            </div>
                        </div>
                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Disable continue shopping button</label>
                                <div class="rmenu-settings-control">
                                    <label class="rmenu-toggle-switch">
                                        <input type="checkbox" name="rmenu_disable_btn_out_of_stock" value="1" <?php checked(1, get_option("rmenu_disable_btn_out_of_stock", 1), true); ?> />
                                        <span class="rmenu-toggle-slider"></span>
                                    </label>
                                    <p class="rmenu-field-description">WooCommerce shows a continue shopping button after a product is added to cart, with this option you can disable that link so user remain on checkout page</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-section">
                        <div class="rmenu-settings-section-header">
                            <h3><span class="dashicons dashicons-translation"></span> Compatibility Settings</h3>
                        </div>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Force Button CSS</label>
                                <div class="rmenu-settings-control">
                                    <label class="rmenu-toggle-switch">
                                        <input type="checkbox" name="rmenu_force_button_css" value="1" <?php checked(1, get_option("rmenu_force_button_css", 0), true); ?> />
                                        <span class="rmenu-toggle-slider"></span>
                                    </label>
                                    <p class="rmenu-field-description">Use !important CSS rules to override theme styling (use only if needed).</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Tab click handler for Add To Cart settings tabs
                        const tabItems = document.querySelectorAll('#tab-8 .rmenu-settings-tab-item');
                        const tabContents = document.querySelectorAll('#tab-8  .tab-content');
                        const btn_display = document.querySelector('select[name="rmenu_add_to_cart_catalog_display"]');

                        btn_display.addEventListener('change', function() {
                            if (this.value === 'hide') {
                                // disable all inputs in the tab except btn_display
                                tabContents.forEach(function(content) {
                                    const inputs = content.querySelectorAll('input, select, textarea');
                                    inputs.forEach(function(input) {
                                        if (input !== btn_display) {
                                            input.disabled = true;
                                        }
                                    });
                                });
                            } else {
                                // enable all inputs in the tab
                                tabContents.forEach(function(content) {
                                    const inputs = content.querySelectorAll('input, select, textarea');
                                    inputs.forEach(function(input) {
                                        input.disabled = false;
                                    });
                                });
                            }
                        });

                        tabItems.forEach(function(tab) {
                            tab.addEventListener('click', function() {
                                // Remove active class from all tabs
                                tabItems.forEach(function(t) {
                                    t.classList.remove('active');
                                });
                                // Hide all tab contents
                                tabContents.forEach(function(content) {
                                    content.style.display = 'none';
                                });

                                // Add active class to clicked tab
                                tab.classList.add('active');
                                // Show the corresponding tab content
                                const tabId = tab.getAttribute('data-tab');
                                const highlight_add_to_cart_enable_Section = document.querySelector('#rmenu-enable-custom-add-to-cart');
                                // If "Enable Custom Add to Cart" is not enabled, show a popup message and prevent tab switching
                                const enableCustomAddToCart = document.querySelector('input[name="rmenu_enable_custom_add_to_cart"]');
                                if (tabId !== "general-settings" && enableCustomAddToCart && !enableCustomAddToCart.checked) {
                                    showDirectCheckoutWarning(
                                        highlight_add_to_cart_enable_Section,
                                        '<b>Enable Custom Add to Cart</b> in the general settings tab to access these options.'
                                    );
                                }
                                enableCustomAddToCart.addEventListener('change', function() {
                                    if (this.checked) {
                                        removeDirectCheckoutWarning(highlight_add_to_cart_enable_Section);
                                    }
                                });

                                const highlight_rmenu_add_to_cart_archive_display_Section = document.querySelector('#rmenu-add-to-cart-archive-display');

                                // If the tab is not "button-behavior" and the button display is set to hide, show a popup message
                                if (tabId !== "button-behavior" && btn_display && btn_display.value === 'hide') {
                                    showDirectCheckoutWarning(
                                        highlight_rmenu_add_to_cart_archive_display_Section,
                                        '<b>Button Display is set to Hide</b>. You can change this in the Button Behavior -> Display Settings.'
                                    );
                                }
                                btn_display.addEventListener('change', function() {
                                    if (this.value !== 'hide') {
                                        removeDirectCheckoutWarning(highlight_rmenu_add_to_cart_archive_display_Section);
                                    }
                                });

                                const content = document.getElementById(tabId);
                                if (content) {
                                    content.style.display = 'block';
                                }
                            });
                        });

                        // Show only the first tab content by default
                        tabContents.forEach(function(content, idx) {
                            content.style.display = (idx === 0) ? 'block' : 'none';
                        });
                    });
                </script>

            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                    const enableCheckout = document.querySelector('div#tab-8 input[name="rmenu_enable_custom_add_to_cart"]');

                    const allinputFields = Array.from(document.querySelectorAll('div#tab-8 input, div#tab-8 select,div#tab-8 textarea')).filter(
                        el => !(el.name === "rmenu_enable_custom_add_to_cart")
                    );
                    allinputFields.forEach(field => {
                        field.disabled = !enableCheckout.checked;
                    });
                    enableCheckout.addEventListener('change', function() {
                        allinputFields.forEach(field => {
                            field.disabled = !this.checked;
                        });
                    });
                });
            </script>
            <?php submit_button(); ?>
        </form>
        <p style="text-align: center;font-size: 15px;">To add menu cart to your page, use the shortcode <b>[plugincy_cart drawer="right" cart_icon="cart" product_title_tag="h4"]</b> or use Plugincy Cart Widget/Block</p>
        <p style="text-align: center;padding-bottom:20px; font-size: 15px;">[plugincy_one_page_checkout product_ids="152,153,151,142" template="product-tabs"] or use <b>Plugincy One Page Checkout</b> widget/block <a target="_blank" href="https://plugincy.com/documentations/one-page-quick-checkout-for-woocommerce/one-page-checkout/multi-product-one-page-checkout/">view documentation</a></p>
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
        "onpage_checkout_enable",
        "onpage_checkout_enable_all",
        "onpage_checkout_cart_add",
        "onpage_checkout_widget_cart_empty",
        "onpage_checkout_widget_cart_add",
        "onpage_checkout_hide_cart_button",
        "rmenu_quantity_control",
        "rmenu_at_one_product_cart",
        "rmenu_disable_cart_page",
        "rmenu_link_product",
        "rmenu_remove_product",
        "rmenu_add_img_before_product",
        "rmenu_add_direct_checkout_button",
        "rmenu_enable_custom_add_to_cart",
        "rmenu_wc_checkout_guest_enabled",
        "rmenu_wc_checkout_mobile_optimize",
        "rmenu_wc_direct_checkout_position",
        "rmenu_variation_show_archive",
        "rmenu_wc_hide_select_option",
        "txt-direct-checkout",
        "rmenu_wc_checkout_color",
        "rmenu_add_to_cart_bg_color",
        "rmenu_wc_checkout_text_color",
        "rmenu_add_to_cart_text_color",
        "rmenu_add_to_cart_hover_bg_color",
        "rmenu_add_to_cart_hover_text_color",
        "rmenu_add_to_cart_border_radius",
        "rmenu_add_to_cart_font_size",
        "rmenu_add_to_cart_width",
        "rmenu_add_to_cart_icon",
        "rmenu_add_to_cart_icon_position",
        "rmenu_add_to_cart_catalog_display",
        "rmenu_wc_checkout_style",
        "rmenu_add_to_cart_style",
        "rmenu_wc_checkout_icon",
        "rmenu_wc_checkout_icon_position",
        "rmenu_wc_checkout_method",
        "rmenu_wc_clear_cart",
        "rmenu_wc_one_click_purchase",
        "rmenu_wc_add_confirmation",
        "rmenu_enable_ajax_add_to_cart",
        "rmenu_add_to_cart_default_qty",
        "rmenu_show_quantity_archive",
        "rmenu_redirect_after_add",
        "rmenu_add_to_cart_animation",
        "rmenu_add_to_cart_notification_style",
        "rmenu_add_to_cart_success_message",
        "rmenu_show_view_cart_link",
        "rmenu_add_to_cart_notification_duration",
        "rmenu_show_checkout_link",
        "rmenu_sticky_add_to_cart_mobile",
        "rmenu_mobile_add_to_cart_text",
        "rmenu_mobile_button_size",
        "rmenu_hide_on_mobile_options",
        "rmenu_mobile_icon_only",
        "rmenu_add_to_cart_loading_effect",
        "rmenu_disable_btn_out_of_stock",
        "rmenu_force_button_css",
        "rmenu_enable_quick_view",
        "rmenu_quick_view_button_text",
        "rmenu_quick_view_button_position",
        "rmenu_quick_view_display_type",
        "rmenu_quick_view_modal_size",
        "rmenu_quick_view_enable_lightbox",
        "rmenu_quick_view_loading_effect",
        "rmenu_quick_view_button_style",
        "rmenu_quick_view_button_color",
        "rmenu_quick_view_text_color",
        "rmenu_quick_view_button_icon",
        "rmenu_quick_view_icon_position",
        "rmenu_quick_view_ajax_add_to_cart",
        "rmenu_quick_view_direct_checkout",
        "rmenu_quick_view_mobile_optimize",
        "rmenu_quick_view_close_on_add",
        "rmenu_quick_view_keyboard_nav",
        "rmenu_quick_view_preload",
        "rmenu_quick_view_enable_cache",
        "rmenu_quick_view_cache_expiration",
        "rmenu_quick_view_lazy_load",
        "rmenu_quick_view_details_text",
        "rmenu_quick_view_close_text",
        "rmenu_quick_view_prev_text",
        "rmenu_quick_view_next_text",
        "rmenu_quick_view_track_events",
        "rmenu_quick_view_event_category",
        "rmenu_quick_view_event_action",
        "rmenu_quick_view_load_scripts",
        "rmenu_quick_view_theme_compat",
        "onepaquc_trust_badges_enabled",
        "onepaquc_trust_badge_position",
        "onepaquc_trust_badge_style",
        "show_custom_html",
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
    register_setting('onepaquc_cart_settings', "rmenu_add_to_cart_by_types", 'onepaquc_sanitize_array_of_text');
    register_setting('onepaquc_cart_settings', "rmenu_quick_view_content_elements", 'onepaquc_sanitize_array_of_text');
    register_setting('onepaquc_cart_settings', "rmenu_show_quick_view_by_types", 'onepaquc_sanitize_array_of_text');
    register_setting('onepaquc_cart_settings', "rmenu_show_quick_view_by_page", 'onepaquc_sanitize_array_of_text');
    register_setting('onepaquc_cart_settings', "onepaquc_my_trust_badges_items", 'onepaquc_sanitize_trust_badges_items');
    register_setting('onepaquc_cart_settings', 'onepaquc_trust_badge_custom_html', [
        'type' => 'string',
        'sanitize_callback' => function ($value) {
            // Allow HTML, CSS, JS (no sanitization)
            return $value;
        },
        'show_in_rest' => false,
        'default' => '<!-- Custom Trust Badges HTML with CSS --> <div class="custom-trust-badges"> <!-- Payment Security Badge --> <div class="trust-badge payment-badge"> <div class="badge-icon"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect> <path d="M7 11V7a5 5 0 0 1 10 0v4"></path> </svg> </div> <div class="badge-content"> <h4>Secure Payment</h4> <p>Your payment information is encrypted</p> </div> </div> <!-- Money Back Guarantee Badge --> <div class="trust-badge guarantee-badge"> <div class="badge-icon"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <circle cx="12" cy="12" r="10"></circle> <path d="M8 14s1.5 2 4 2 4-2 4-2"></path> <line x1="9" y1="9" x2="9.01" y2="9"></line> <line x1="15" y1="9" x2="15.01" y2="9"></line> </svg> </div> <div class="badge-content"> <h4>30-Day Guarantee</h4> <p>Not satisfied? Get a full refund</p> </div> </div> <!-- Fast Shipping Badge --> <div class="trust-badge shipping-badge"> <div class="badge-icon"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <rect x="1" y="3" width="15" height="13"></rect> <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon> <circle cx="5.5" cy="18.5" r="2.5"></circle> <circle cx="18.5" cy="18.5" r="2.5"></circle> </svg> </div> <div class="badge-content"> <h4>Fast Shipping</h4> <p>Delivery within 2-4 business days</p> </div> </div> <!-- Privacy Badge --> <div class="trust-badge privacy-badge"> <div class="badge-icon"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path> </svg> </div> <div class="badge-content"> <h4>Privacy Protected</h4> <p>Your data is never shared with third parties</p> </div> </div> </div> <style> .custom-trust-badges { display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between; margin: 30px 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; } .custom-trust-badges .trust-badge { flex: 1; min-width: 200px; display: flex; align-items: center; padding: 15px; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; position: relative; overflow: hidden; } .custom-trust-badges .trust-badge::before { content: \'\'; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: currentColor; opacity: 0.8; } .custom-trust-badges .trust-badge:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); } .custom-trust-badges .badge-icon { display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; border-radius: 50%; margin-right: 15px; flex-shrink: 0; } .custom-trust-badges .badge-icon svg { width: 28px; height: 28px; } .custom-trust-badges .badge-content { flex-grow: 1; } .custom-trust-badges .badge-content h4 { margin: 0 0 4px 0; font-size: 16px; font-weight: 600; } .custom-trust-badges .badge-content p { margin: 0; font-size: 13px; opacity: 0.7; line-height: 1.4; } ge specific colors */ .custom-trust-badges .payment-badge { color: #3498db; } .custom-trust-badges .payment-badge .badge-icon { background-color: rgba(52, 152, 219, 0.1); } .custom-trust-badges .guarantee-badge { color: #2ecc71; } .custom-trust-badges .guarantee-badge .badge-icon { background-color: rgba(46, 204, 113, 0.1); } .custom-trust-badges .shipping-badge { color: #e67e22; } .custom-trust-badges .shipping-badge .badge-icon { background-color: rgba(230, 126, 34, 0.1); } .custom-trust-badges .privacy-badge { color: #9b59b6; } .custom-trust-badges .privacy-badge .badge-icon { background-color: rgba(155, 89, 182, 0.1); } ponsive design */ @media (max-width: 768px) { .custom-trust-badges { flex-direction: column; gap: 15px; } .custom-trust-badges .trust-badge { width: 100%; } } </style>'
    ]);
}

function onepaquc_sanitize_trust_badges_items($items)
{
    // Only accept an array
    if (!is_array($items)) {
        return [];
    }

    // Remove empty items and sanitize
    $sanitized = [];
    foreach ($items as $item) {
        // Only accept arrays with at least 'icon' or 'text'
        if (!is_array($item)) {
            continue;
        }
        // Remove empty trust badge rows (all fields empty)
        $has_content = false;
        foreach ($item as $value) {
            if (trim($value) !== '') {
                $has_content = true;
                break;
            }
        }
        if (!$has_content) {
            continue;
        }
        // Remove if text is "New Badge"
        if (isset($item['text']) && trim($item['text']) === 'New Badge') {
            continue;
        }
        // Sanitize each field
        $sanitized_item = [];
        foreach ($item as $key => $value) {
            $sanitized_item[$key] = sanitize_text_field($value);
        }
        $sanitized[] = $sanitized_item;
    }

    // Remove duplicates (same icon and text)
    $unique = [];
    foreach ($sanitized as $item) {
        $hash = md5($item['icon'] . '|' . $item['text']);
        $unique[$hash] = $item;
    }

    // Re-index array
    return array_values($unique);
}

function onepaquc_sanitize_array_of_text($value)
{
    if (!is_array($value)) {
        return [];
    }

    return array_map('sanitize_text_field', $value);
}


function onepaquc_cart_custom_css()
{
    global $onepaquc_rcheckoutformfields;

    // Initialize an empty string for the custom CSS
    $custom_css = '.checkout-popup .woocommerce-privacy-policy-text { display: none !important; }.onepagecheckoutwidget .woocommerce-privacy-policy-text { display: block !important; }';

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
