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

    add_submenu_page(
        'onepaquc_cart',
        'Get Pro',
        'Get Pro',
        'manage_options',
        'onepaquc_cart_get_pro',
        'onepaquc_cart_get_pro'
    );
}


// Callback function for Get Pro page
function onepaquc_cart_get_pro()
{
?>
    <div class="wrap">
        <h1>Upgrade to Onepage Checkout Pro</h1>


        <div class="onepaquc-pro-banner" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; margin: 20px 0;">
            <h2 style="color: white; margin-top: 0;">🚀 Unlock Premium Features</h2>
            <p style="font-size: 18px; margin-bottom: 30px;">Take your checkout experience to the next level with our premium features designed to boost conversions and enhance user experience.</p>
            <a target="_blank" href="https://plugincy.com/one-page-quick-checkout-for-woocommerce/" class="button button-primary button-hero" style="background: #ff6b6b; border-color: #ff6b6b; text-shadow: none; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);">Get Pro Version</a>
        </div>

        <div class="onepaquc-features-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 30px 0;">

            <div class="feature-card" style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #667eea;">
                <h3 style="color: #333; margin-top: 0;">🎨 Advanced Customization</h3>
                <ul style="color: #666; line-height: 1.6;">
                    <li>Customizable checkout layout</li>
                    <li>Advanced customizable checkout form</li>
                    <li>Quick view popup modal size customization</li>
                    <li>Customizable notifications on add to cart</li>
                </ul>
            </div>

            <div class="feature-card" style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #764ba2;">
                <h3 style="color: #333; margin-top: 0;">⚡ Enhanced Checkout</h3>
                <ul style="color: #666; line-height: 1.6;">
                    <li>Direct checkout for grouped products</li>
                    <li>Popup checkout</li>
                    <li>One click purchase</li>
                    <li>Single checkout without clear cart</li>
                    <li>Advanced checkout options</li>
                </ul>
            </div>

            <div class="feature-card" style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #ff6b6b;">
                <h3 style="color: #333; margin-top: 0;">🛒 Smart Shopping Features</h3>
                <ul style="color: #666; line-height: 1.6;">
                    <li>Express Checkout Options</li>
                    <li>Multi-Step Checkout </li>
                    <li>Address auto complete</li>
                    <li>Default quantity change</li>
                    <li>Quantity selector in archives</li>
                </ul>
            </div>

            <div class="feature-card" style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #51cf66;">
                <h3 style="color: #333; margin-top: 0;">📱 Mobile & UX Optimization</h3>
                <ul style="color: #666; line-height: 1.6;">
                    <li>Quick view mobile optimization</li>
                    <li>Add to cart mobile optimization</li>
                    <li>Quick view loading effects</li>
                    <li>Quick view close on add to cart</li>
                </ul>
            </div>

            <div class="feature-card" style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #ffd93d;">
                <h3 style="color: #333; margin-top: 0;">🔐 Security & Control</h3>
                <ul style="color: #666; line-height: 1.6;">
                    <li>Force Login Before Checkout</li>
                    <li>Enable captcha on checkout</li>
                    <li>Remove Product Button in checkout form</li>
                    <li>Trusted badge in checkout form</li>
                </ul>
            </div>

            <div class="feature-card" style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #ff8cc8;">
                <h3 style="color: #333; margin-top: 0;">📊 Analytics & Sharing</h3>
                <ul style="color: #666; line-height: 1.6;">
                    <li>Quick view analytics</li>
                    <li>Quick view shortcode</li>
                    <li>Social share in quick view popup</li>
                    <li>Product attributes in quick view</li>
                </ul>
            </div>

        </div>

        <div class="onepaquc-cta-section" style="text-align: center; margin: 40px 0; padding: 30px; background: #f8f9fa; border-radius: 10px;">
            <h2 style="color: #333;">Ready to Boost Your Sales?</h2>
            <p style="font-size: 16px; color: #666; margin-bottom: 25px;">Join thousands of store owners who have increased their conversion rates with our pro features.</p>
            <a target="_blank" href="https://plugincy.com/one-page-quick-checkout-for-woocommerce/" class="button button-primary button-hero" style="background: #667eea; border-color: #667eea; margin-right: 15px;">Upgrade Now</a>
            <a target="_blank" href="https://demo.plugincy.com/one-page-quick-checkout-for-woocommerce/" class="button button-secondary button-hero">View Demo</a>
        </div>

        <div class="onepaquc-testimonials" style="background: white; padding: 30px; border-radius: 10px; margin: 20px 0;">
            <h3 style="text-align: center; color: #333; margin-bottom: 30px;">What Our Customers Say</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div style="padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #667eea;">
                    <p style="font-style: italic; color: #666;">"The one-click purchase feature increased our conversion rate by 35%!"</p>
                    <strong style="color: #333;">- Sarah M.</strong>
                </div>
                <div style="padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #764ba2;">
                    <p style="font-style: italic; color: #666;">"Mobile optimization is fantastic. Our mobile sales doubled!"</p>
                    <strong style="color: #333;">- John D.</strong>
                </div>
                <div style="padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #ff6b6b;">
                    <p style="font-style: italic; color: #666;">"The customizable checkout layout perfectly matches our brand."</p>
                    <strong style="color: #333;">- Maria L.</strong>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Display the form for Side Cart and PopUp settings
function onepaquc_cart_text_change_form($textvariable)
{
    $onepaquc_helper = new onepaquc_helper();

    echo '<div class="plugincy_card">';
    $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<svg fill="#fff" width="18" height="18" viewBox="0 0 0.27 0.27" xmlns="http://www.w3.org/2000/svg"><path d="M.27.022v.045a.022.022 0 0 1-.045 0V.045H.158v.18H.18a.022.022 0 0 1 0 .045H.09a.022.022 0 0 1 0-.045h.022v-.18H.045v.022a.022.022 0 1 1-.045 0V.022A.02.02 0 0 1 .022 0h.225a.02.02 0 0 1 .022.022"/></svg>', esc_html__('Cart Text Changes', 'one-page-quick-checkout-for-woocommerce'));

    echo '<div class="plugincy_grid" style="row-gap:10px">';

    foreach (array_chunk($textvariable, 4, true) as $column) {
        foreach ($column as $name => $label) {
            $value = esc_attr(get_option($name, ''));
    ?>
            <label>
                <?php $onepaquc_helper->sec_head('p', '', '', esc_html($label), 'You can find "' . esc_html($label) . '" in the checkout form or drawer' . ($name === "txt-complete_your_purchase" ? " on single product pages." : ".")); ?>
                <input type="text" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" />
            </label>
    <?php
        }
    }

    echo '</div> </div>';
}

// Dashboard page
function onepaquc_cart_dashboard()
{
    global $onepaquc_checkoutformfields,
        $onepaquc_productpageformfields;

    $onepaquc_helper = new onepaquc_helper();
    ?>

    <div class="welcome-banner">
        <div class="welcome-title">Welcome to One Page Quick Checkout for WooCommerce <span class="version-tag">v1.3.4</span></div>
        <p style="max-width: 70%; margin:0 auto;">Thank you for installing One Page Quick Checkout for WooCommerce! Streamline your WooCommerce checkout process and boost your conversion rates with our easy-to-configure solution.</p>

        <div class="feature-grid">
            <div class="feature-item">
                <div class="feature-icon">
                    <svg fill="#fff" width="30" height="30" viewBox="-0.282 -0.132 0.9 0.9" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMinYMin" class="jam jam-thunder-f">
                        <path d="M.214.186h.068a.057.057 0 0 1 .051.078L.184.611A.039.039 0 0 1 .109.596v-.26L.054.334A.06.06 0 0 1 0 .279V.057A.06.06 0 0 1 .057 0h.101a.06.06 0 0 1 .057.057z" />
                    </svg>
                </div>
                <h3>Fast Setup</h3>
                <p>Configure your checkout in minutes with our intuitive options.</p>
            </div>
            <div class="feature-item" style="background: #ebfcf1;">
                <div class="feature-icon" style="background: #22c55e;">
                    <svg width="30" height="30" viewBox="0 0 0.75 0.75" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#fff" d="M.563 0a.075.075 0 0 1 .075.075v.6A.075.075 0 0 1 .563.75H.188A.075.075 0 0 1 .113.675v-.6A.075.075 0 0 1 .188 0zm.022.577h-.42v.098a.02.02 0 0 0 .022.022h.375A.02.02 0 0 0 .584.675zM.375.6a.037.037 0 1 1 0 .075.037.037 0 0 1 0-.075M.563.053H.188a.02.02 0 0 0-.022.022v.45h.42v-.45A.02.02 0 0 0 .564.053" />
                    </svg>
                </div>
                <h3>Mobile Friendly</h3>
                <p>Responsive design works perfectly on all devices.</p>
            </div>
            <div class="feature-item" style="background: #f8f2ff;">
                <div class="feature-icon" style="background: #a855f7;">
                    <svg width="30" height="30" viewBox="0 0 0.9 0.9" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M.075.319h.75m-.6.3H.3m.094 0h.15" stroke="#fff" stroke-width=".056" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M.241.131h.417c.134 0 .167.033.167.165v.308c0 .132-.033.165-.167.165H.241C.108.769.074.736.074.605V.297c0-.132.033-.165.167-.165" stroke="#fff" stroke-width=".056" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <h3>Payment Gateways</h3>
                <p>Supports all major payment processors.</p>
            </div>
        </div>

        <div class="button-row">
            <a href="https://plugincy.com/one-page-quick-checkout-for-woocommerce/" target="_blank" class="button" style="background: #ff5a36; color: #fff;">
                <span class="dashicons dashicons-star-filled" style=" margin-right: 5px; "></span>
                <?php echo esc_html__('Upgrade to Pro', 'one-page-quick-checkout-for-woocommerce'); ?>
            </a>
            <a target="_blank" href="https://demo.plugincy.com/one-page-quick-checkout-for-woocommerce/" class="button" style="background: #ed8936;color: white;"><span class="dashicons dashicons-visibility" style=" margin-right: 5px; "></span> View Demo</a>
            <a target="_blank" href="https://plugincy.com/documentations/one-page-quick-checkout-for-woocommerce/" class="button"><span class="dashicons dashicons-book" style=" margin-right: 5px; "></span> View Documentation</a>
            <a href="https://plugincy.com/support" target="_blank" class="button button-secondary"><span class="dashicons dashicons-sos" style=" margin-right: 5px; "></span> Get Support</a>
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
            <div class="tab active" data-tab="4">
                <span class="dashicons dashicons-cart"></span>
                Direct Checkout
            </div>
            <div class="tab" data-tab="2">
                <span class="dashicons dashicons-admin-page"></span>
                One Page Checkout
            </div>
            <div class="tab" data-tab="9">
                <span class="dashicons dashicons-archive"></span>
                Floating Cart
            </div>

            <div class="tab" data-tab="7">
                <span class="dashicons dashicons-visibility"></span>
                Quick View
            </div>
            <div class="tab" data-tab="5">
                <span class="dashicons dashicons-star-filled"></span>
                Features
            </div>
            <div class="tab" data-tab="8">
                <span class="dashicons dashicons-plus-alt"></span>
                Add To Cart
            </div>
            <div class="tab" data-tab="1">
                <span class="dashicons dashicons-forms"></span>
                Checkout Form
            </div>
            <div class="tab" data-tab="3">
                <span class="dashicons dashicons-edit"></span>
                Text Manage
            </div>
            <div class="tab" data-tab="6">
                <span class="dashicons dashicons-admin-settings"></span>
                Advanced Settings
            </div>
            <div class="tab" data-tab="100">
                <span class="dashicons dashicons-admin-network"></span>
                Plugin License
            </div>
        </div>
        <script>
            function toggleDisabledClass(isDisabled, allinputFields) {
                if (!Array.isArray(allinputFields)) {
                    if (isDisabled) {
                        allinputFields.classList.add('disabled');
                        allinputFields.readOnly = true;
                    } else {
                        allinputFields.classList.remove('disabled');
                        allinputFields.readOnly = false;
                    }
                } else {
                    allinputFields.forEach(field => {
                        if (isDisabled) {
                            field.classList.add('disabled');
                            field.readOnly = true;
                        } else {
                            field.classList.remove('disabled');
                            field.readOnly = false;
                        }
                    });
                }
            }

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
        <div class="tab-content" id="tab-100">
            <?php
            $license_manager = new onepaquc_License_Manager();
            $license_manager->render_license_form();
            ?>
        </div>
        <form method="post" action="options.php">
            <!-- Add nonce field for security -->
            <?php wp_nonce_field('onepaquc_cart_settings'); ?>
            <?php settings_fields('onepaquc_cart_settings'); ?>
            <div class="tab-content plugincy_card" id="tab-1" style="margin-top: 20px;">
                <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head', '<span class="dashicons dashicons-forms"></span>', 'Checkout Form Manage', 'Customize the checkout form fields and headings to enhance user experience.'); ?>
                <table class="form-table plugincy_table mb-4">
                    <tbody class="plugincy_grid">
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
                                    <?php $onepaquc_helper->switcher(esc_attr($key), 0); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '', 'Heading Manage', 'Customize the headings of the checkout form fields to enhance user experience.'); ?>
                <table class="form-table plugincy_table">
                    <?php foreach (onepaquc_onpcheckout_heading() as $key => $field) : ?>
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html($field['title']); ?></th>
                            <td>
                                <?php $onepaquc_helper->switcher(esc_attr($key), 0); ?>
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

                <div class="plugincy_row">
                    <div class="plugincy_col-5 plugincy_card">
                        <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head', '<svg fill="#fff" height="16" width="16" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10.24 10.24" xml:space="preserve">
                                    <path d="M8.424 2.56H7.57v.426a.855.855 0 0 1-1.708 0V2.56H4.156v.426a.855.855 0 0 1-1.708 0V2.56h-.85c0 4.266-.426 7.68-.426 7.68h7.68c-.002 0-.428-3.414-.428-7.68m-5.12.854c.236 0 .426-.19.426-.426v-.854c0-.708.572-1.28 1.28-1.28s1.28.572 1.28 1.28v.854a.425.425 0 1 0 .852 0v-.854C7.144.956 6.188 0 5.01 0S2.876.956 2.876 2.134v.854a.43.43 0 0 0 .428.426" />
                                </svg>', 'One Page Checkout in Single Product', 'Configure one-page checkout for individual product pages. Enable one-page checkout for specific products from the WooCommerce product edit screen.'); ?>
                        <table class="form-table plugincy_table">
                            <tr valign="top">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Enable One Page Checkout', 'Enable one-page checkout for single product page.'); ?>
                                <td>
                                    <?php
                                    $onepaquc_helper->switcher('onpage_checkout_enable');
                                    ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Enable for All Products', 'Enable one-page checkout for all products without individual selection.'); ?>
                                <td>
                                    <?php $onepaquc_helper->switcher('onpage_checkout_enable_all', 0); ?>

                                </td>
                            </tr>
                            <tr valign="top">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Form Position', 'Set the priority of the one-page checkout form. The default value is 9, but not all values work with every theme. So, adjust it based on your theme.'); ?>
                                <td>
                                    <?php
                                    // Get the saved value or default to 9 if not set or empty
                                    $onpage_checkout_position = get_option("onpage_checkout_position", '');
                                    if ($onpage_checkout_position === '' || $onpage_checkout_position === false) {
                                        $onpage_checkout_position = 9;
                                    }
                                    ?>
                                    <input type="number" name="onpage_checkout_position" value="<?php echo esc_attr($onpage_checkout_position); ?>" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Empty Cart on Page Load', 'Clear existing cart items when the one-page checkout product page loads.'); ?>
                                <td>
                                    <?php $onepaquc_helper->switcher('onpage_checkout_cart_empty', 0); ?>
                                </td>
                            </tr>

                            </td>
                            </tr>
                            <tr valign="top">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Add to Cart on Page Load', 'Automatically add the product to cart when the page loads.'); ?>
                                <td>
                                    <?php $onepaquc_helper->switcher('onpage_checkout_cart_add', 1, "If you turn it off one page checkout will not work properly."); ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Hide Add to Cart Button', 'Hide the regular Add to Cart button on one-page checkout product pages.'); ?>
                                <td>
                                    <?php $onepaquc_helper->switcher('onpage_checkout_hide_cart_button', 0); ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Checkout Layout', 'Select the layout for the one-page checkout form.'); ?>
                                <td class="pro-only">
                                    <select disabled name="onpage_checkout_layout">
                                        <option value="two_column" <?php selected(get_option('onpage_checkout_layout', 'two_column'), 'two_column'); ?>>Two Columns (Product & Checkout)</option>
                                        <option value="one_column" <?php selected(get_option('onpage_checkout_layout', 'two_column'), 'one_column'); ?>>One Column (Stacked)</option>
                                        <option value="product_first" <?php selected(get_option('onpage_checkout_layout', 'two_column'), 'product_first'); ?>>Product First, Then Checkout</option>
                                    </select>
                                    <span class="dashicons dashicons-lock plugincy_lock-icon"></span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                            const enableCheckout = document.querySelector('div#tab-2 input[name="onpage_checkout_enable"]');

                            const allinputFields = Array.from(document.querySelectorAll('div#tab-2 table:nth-of-type(1) input')).filter(
                                el => !(el.name === "onpage_checkout_enable")
                            );
                            toggleDisabledClass(!enableCheckout.checked, allinputFields); // Set initial state
                            enableCheckout.addEventListener('change', function() {
                                toggleDisabledClass(!this.checked, allinputFields);
                            });
                        });
                    </script>
                    <div class="plugincy_col-5 plugincy_card">
                        <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head', '<span class="dashicons dashicons-forms"></span>', 'Multi-product One Page Checkout', 'Configure settings for the multi-product one-page checkout shortcode. Use: [plugincy_one_page_checkout product_ids="152,153,151,142" template="product-tabs"]'); ?>
                        <table class="form-table plugincy_table">
                            <tr valign="top">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Empty Cart on Page Load', 'Clear existing cart items when a multi-product one-page checkout page loads.'); ?>
                                <td>
                                    <?php $onepaquc_helper->switcher('onpage_checkout_widget_cart_empty'); ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Add to Cart on Page Load', 'Automatically add the first product to cart when the page loads.'); ?>
                                <td>
                                    <?php $onepaquc_helper->switcher('onpage_checkout_widget_cart_add'); ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="tab-content" id="tab-9">
                <div class="mb-4">
                    <?php $onepaquc_helper->sec_head('h2', '', '<span class="dashicons dashicons-cart"></span>', 'Floating Cart Settings', ''); ?>
                    <p class="description">Configure the appearance and behavior of your floating cart. Enable the floating cart to provide users with quick access to their cart from any page on your site.</p>
                </div>

                <div class="plugincy_row mb-4">
                    <div class="rmenu-settings-section plugincy_card plugincy_col-5">
                        <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-generic"></span>', 'General Settings', 'General settings for the floating cart.'); ?>

                        <table class="form-table plugincy_table">
                            <tr>
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Enable Sticky Cart', 'Enable or disable the sticky cart functionality.'); ?>
                                <td class="rmenu-settings-control">
                                    <?php $onepaquc_helper->switcher('rmenu_enable_sticky_cart', 0); ?>
                                </td>
                            </tr>

                            <tr>
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Cart Checkout Behavior', 'Choose the behavior of the cart checkout process.'); ?>
                                <td class="pro-only">
                                    <select name="rmenu_cart_checkout_behavior" class="rmenu-select">
                                        <option value="direct_checkout" <?php selected(get_option('rmenu_cart_checkout_behavior', 'direct_checkout'), 'direct_checkout'); ?>>Direct Checkout</option>
                                        <option disabled value="pro" <?php selected(get_option('rmenu_cart_checkout_behavior', 'side_cart'), 'pro'); ?>>Popup Checkout (Pro Features)</option>
                                    </select>
                                    <span class="dashicons dashicons-lock plugincy_lock-icon"></span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="rmenu-settings-section plugincy_card plugincy_col-5">
                        <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head', '<span class="dashicons dashicons-move"></span>', 'Position Settings', 'Configure the position of the floating cart.'); ?>

                        <table class="form-table plugincy_table">
                            <tr>
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Top Position', 'Set the top position of the floating cart.'); ?>
                                <td class="rmenu-settings-control">
                                    <input type="text" name="rmenu_cart_top_position" value="<?php echo esc_attr(get_option('rmenu_cart_top_position', '50%')); ?>" class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <?php $onepaquc_helper->sec_head('th', '', '', 'Left Position', 'Set the left position of the floating cart.'); ?>
                                <td class="rmenu-settings-control">
                                    <input type="text" name="rmenu_cart_left_position" value="<?php echo esc_attr(get_option('rmenu_cart_left_position', '100%')); ?>" class="regular-text" />
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="rmenu-settings-section plugincy_card">
                    <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-appearance"></span>', 'Button Style Settings', 'Configure the style of the floating cart button.'); ?>

                    <div class="rmenu-settings-row rmenu-settings-row-columns">
                        <div class="rmenu-settings-column">
                            <div class="rmenu-settings-field plugincy_row items_center">
                                <label class="rmenu-settings-label">Background Color</label>
                                <div class="rmenu-settings-control">
                                    <input type="color" name="rmenu_cart_bg_color" value="<?php echo esc_attr(get_option('rmenu_cart_bg_color', '#96588a')); ?>" class="rmenu-color-picker" />
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-column">
                            <div class="rmenu-settings-field plugincy_row items_center">
                                <label class="rmenu-settings-label">Text Color</label>
                                <div class="rmenu-settings-control">
                                    <input type="color" name="rmenu_cart_text_color" value="<?php echo esc_attr(get_option('rmenu_cart_text_color', '#ffffff')); ?>" class="rmenu-color-picker" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-row rmenu-settings-row-columns">
                        <div class="rmenu-settings-column">
                            <div class="rmenu-settings-field plugincy_row items_center">
                                <label class="rmenu-settings-label">Hover Background</label>
                                <div class="rmenu-settings-control">
                                    <input type="color" name="rmenu_cart_hover_bg" value="<?php echo esc_attr(get_option('rmenu_cart_hover_bg', '#f8f8f8')); ?>" class="rmenu-color-picker" />
                                </div>
                            </div>
                        </div>

                        <div class="rmenu-settings-column">
                            <div class="rmenu-settings-field plugincy_row items_center">
                                <label class="rmenu-settings-label">Hover Text Color</label>
                                <div class="rmenu-settings-control">
                                    <input type="color" name="rmenu_cart_hover_text" value="<?php echo esc_attr(get_option('rmenu_cart_hover_text', '#000000')); ?>" class="rmenu-color-picker" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field plugincy_row items_center">
                            <label class="rmenu-settings-label">Border Radius</label>
                            <div class="rmenu-settings-control">
                                <input type="text" name="rmenu_cart_border_radius" value="<?php echo esc_attr(get_option('rmenu_cart_border_radius', '5px 0 0 5px')); ?>" placeholder="0px 0px 0px 0px" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <div class="rmenu-settings-section">
                    <div class="rmenu-settings-section-header">
                        <h3><span class="dashicons dashicons-admin-settings"></span> Additional Settings (coming soon)</h3>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">Show Cart Icon</label>
                            <div class="rmenu-settings-control">
                                <label class="rmenu-toggle-switch">
                                    <input type="checkbox" name="rmenu_show_cart_icon" value="1" <?php //checked(1, get_option("rmenu_show_cart_icon", 1), true); 
                                                                                                    ?> />
                                    <span class="rmenu-toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">Show Cart Count</label>
                            <div class="rmenu-settings-control">
                                <label class="rmenu-toggle-switch">
                                    <input type="checkbox" name="rmenu_show_cart_count" value="1" <?php //checked(1, get_option("rmenu_show_cart_count", 1), true); 
                                                                                                    ?> />
                                    <span class="rmenu-toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div> -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // if the "Enable floating cart" checkbox is checked
                        const enableCheckout = document.querySelector('div#tab-9 input[name="rmenu_enable_sticky_cart"]');

                        const allinputFields = Array.from(document.querySelectorAll('div#tab-9 input,div#tab-9 select')).filter(
                            el => !(el.name === "rmenu_enable_sticky_cart")
                        );
                        toggleDisabledClass(!enableCheckout.checked, allinputFields); // Set initial state
                        enableCheckout.addEventListener('change', function() {
                            toggleDisabledClass(!this.checked, allinputFields);
                        });
                    });
                </script>
            </div>

            <div class="tab-content" id="tab-3">
                <!-- <div class="d-flex space-between">
                    <h2>Checkout Form Text</h2> <button id="reset-defaults" class="button button-primary" style="background:red;">Reset Default</button>
                </div> -->
                <?php
                onepaquc_cart_text_change_form($onepaquc_checkoutformfields);

                ?>
            </div>
            <div class="tab-content active" id="tab-4">
                <div class="plugincy_nav_card mb-4">
                    <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head2', '<svg fill="#fff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" xml:space="preserve" width="16" height="16">
                                <path d="M13.984 5.25a.73.73 0 0 0-.65-.402H9.662l1.898-3.796A.727.727 0 0 0 10.909 0H6.545a.73.73 0 0 0-.65.402L2.016 8.16a.727.727 0 0 0 .65 1.052h3.949L5.349 15.12a.726.726 0 0 0 .41.814.73.73 0 0 0 .883-.226l7.273-9.697a.73.73 0 0 0 .069-.762" />
                            </svg>', 'WooCommerce Direct Checkout', '', 'Enable direct checkout to allow customers to bypass the cart and proceed directly to checkout, streamlining the purchasing process.'); ?>
                    <div class="rmenu-settings-tabs">
                        <ul class="rmenu-settings-tab-list">
                            <li class="rmenu-settings-tab-item active" data-tab="direct-general-settings">
                                <span class="dashicons dashicons-admin-generic"></span> General Settings
                            </li>
                            <li class="rmenu-settings-tab-item" data-tab="direct-advanced">
                                <span class="dashicons dashicons-admin-tools"></span> Advanced
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="tab-content" id="direct-general-settings" style="padding: 0;">
                    <div class="plugincy_row mb-4">
                        <div class="rmenu-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-generic"></span>', 'General Settings', ''); ?>
                            <table class="form-table plugincy_table">
                                <tr valign="top">
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Enable Direct Checkout', 'Enable or disable the direct checkout functionality across your WooCommerce store.'); ?>
                                    <td><?php $onepaquc_helper->switcher('rmenu_add_direct_checkout_button'); ?></td>
                                </tr>
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Button Text', 'Customize the text displayed on the direct checkout button.'); ?>
                                    </th>
                                    <td>
                                        <div class="rmenu-settings-control">
                                            <?php
                                            $direct_checkout_text = get_option('txt-direct-checkout', '');
                                            if (empty($direct_checkout_text)) {
                                                $direct_checkout_text = 'Buy Now';
                                            }
                                            ?>
                                            <input type="text" name="txt-direct-checkout" value="<?php echo esc_attr($direct_checkout_text); ?>" class="regular-text" />
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Button Position on Archive Page', 'Choose where to display the direct checkout button on product pages.'); ?>
                                    <td>
                                        <div class="rmenu-settings-control">
                                            <select name="rmenu_wc_direct_checkout_position" class="rmenu-select">
                                                <option value="overlay_thumbnail" <?php selected(get_option('rmenu_wc_direct_checkout_position', 'after_add_to_cart'), 'overlay_thumbnail'); ?>>Overlay on Product Image</option>
                                                <option value="overlay_thumbnail_hover" <?php selected(get_option('rmenu_wc_direct_checkout_position', 'after_add_to_cart'), 'overlay_thumbnail_hover'); ?>>Overlay on Product Image Hover</option>
                                                <option value="after_product" <?php selected(get_option('rmenu_wc_direct_checkout_position', 'after_add_to_cart'), 'after_product'); ?>>After Product</option>
                                                <option value="after_product_title" <?php selected(get_option('rmenu_wc_direct_checkout_position', 'after_add_to_cart'), 'after_product_title'); ?>>After Product Title</option>
                                                <option value="before_product_title" <?php selected(get_option('rmenu_wc_direct_checkout_position', 'after_add_to_cart'), 'before_product_title'); ?>>Before Product Title</option>
                                                <option value="after_product_excerpt" <?php selected(get_option('rmenu_wc_direct_checkout_position', 'after_add_to_cart'), 'after_product_excerpt'); ?>>After Product Excerpt</option>
                                                <option value="after_product_rating" <?php selected(get_option('rmenu_wc_direct_checkout_position', 'after_add_to_cart'), 'after_product_rating'); ?>>After Product Rating</option>
                                                <option value="after_product_price" <?php selected(get_option('rmenu_wc_direct_checkout_position', 'after_add_to_cart'), 'after_product_price'); ?>>After Product Price</option>
                                                <option value="after_add_to_cart" <?php selected(get_option('rmenu_wc_direct_checkout_position', 'after_add_to_cart'), 'after_add_to_cart'); ?>>After Add to Cart Button</option>
                                                <option value="before_add_to_cart" <?php selected(get_option('rmenu_wc_direct_checkout_position', 'after_add_to_cart'), 'before_add_to_cart'); ?>>Before Add to Cart Button</option>
                                                <option value="bottom_add_to_cart" <?php selected(get_option('rmenu_wc_direct_checkout_position', 'after_add_to_cart'), 'bottom_add_to_cart'); ?>>Bottom of Add to Cart Button</option>
                                                <option value="replace_add_to_cart" <?php selected(get_option('rmenu_wc_direct_checkout_position', 'after_add_to_cart'), 'replace_add_to_cart'); ?>>Replace Add to Cart Button</option>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Button Position on Single Page', 'Choose where to display the direct checkout button on single pages.'); ?>
                                    </th>
                                    <td>
                                        <div class="rmenu-settings-control">
                                            <select name="rmenu_wc_direct_checkout_single_position" class="rmenu-select">
                                                <option value="after_add_to_cart" <?php selected(get_option('rmenu_wc_direct_checkout_single_position', 'after_add_to_cart'), 'after_add_to_cart'); ?>>After Add to Cart Button</option>
                                                <option value="before_add_to_cart" <?php selected(get_option('rmenu_wc_direct_checkout_single_position', 'after_add_to_cart'), 'before_add_to_cart'); ?>>Before Add to Cart Button</option>
                                                <option value="bottom_add_to_cart" <?php selected(get_option('rmenu_wc_direct_checkout_single_position', 'after_add_to_cart'), 'bottom_add_to_cart'); ?>>Bottom of Add to Cart Button</option>
                                                <option value="replace_add_to_cart" <?php selected(get_option('rmenu_wc_direct_checkout_single_position', 'after_add_to_cart'), 'replace_add_to_cart'); ?>>Replace Add to Cart Button</option>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="rmenu-settings-section plugincy_col-5 plugincy_card" id="rmenu-direct-button-display-section">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-visibility"></span>', 'Display Settings', ''); ?>

                            <div class="rmenu-settings-row">
                                <div class="rmenu-settings-field">
                                    <label class="rmenu-settings-label">Product Types</label>
                                    <?php $product_types_option = get_option('rmenu_show_quick_checkout_by_types', ["simple", "variable", "external"]); ?>
                                    <div class="rmenu-settings-control rmenu-checkbox-group">
                                        <label class="rmenu-checkbox-container">
                                            <input type="checkbox" name="rmenu_show_quick_checkout_by_types[]" value="simple" <?php checked(in_array('simple', $product_types_option)); ?> />
                                            <span class="rmenu-checkbox-label">Simple Products</span>
                                        </label>

                                        <label class="rmenu-checkbox-container">
                                            <input type="checkbox" name="rmenu_show_quick_checkout_by_types[]" value="variable" <?php checked(in_array('variable', $product_types_option)); ?> />
                                            <span class="rmenu-checkbox-label">Variable Products</span>
                                        </label>

                                        <!-- <label class="rmenu-checkbox-container pro-only">
                                        <input disabled type="checkbox" name="rmenu_show_quick_checkout_by_types[]" value="coming_grouped" <?php //checked(in_array('grouped', $product_types_option)); 
                                                                                                                                            ?> />
                                        <span class="rmenu-checkbox-label">Grouped Products (Pro Feature)</span>
                                    </label> -->

                                        <!-- <label class="rmenu-checkbox-container">
                                            <input type="checkbox" name="rmenu_show_quick_checkout_by_types[]" value="external" <?php //checked(in_array('external', $product_types_option)); ?> />
                                            <span class="rmenu-checkbox-label">External/Affiliate Products</span>
                                        </label> -->
                                    </div>
                                </div>
                            </div>

                            <div class="rmenu-settings-row">
                                <div class="rmenu-settings-field">
                                    <?php $product_types_option = get_option('rmenu_show_quick_checkout_by_page', ["single", "related", "upsells", "shop-page", "category-archives", "tag-archives", "featured-products", "on-sale", "recent", "widgets", "shortcodes"]); ?>
                                    <div class="rmenu-settings-control rmenu-checkbox-group">
                                        <div class="rmenu-checkbox-column">
                                            <h4>Product Pages</h4>
                                            <label class="rmenu-checkbox-container">
                                                <input type="checkbox" name="rmenu_show_quick_checkout_by_page[]" value="single" <?php checked(in_array('single', $product_types_option)); ?> />
                                                <span class="rmenu-checkbox-label">Single Product Pages</span>
                                            </label>

                                            <label class="rmenu-checkbox-container">
                                                <input type="checkbox" name="rmenu_show_quick_checkout_by_page[]" value="upsells" <?php checked(in_array('upsells', $product_types_option)); ?> />
                                                <span class="rmenu-checkbox-label">Upsells</span>
                                            </label>

                                            <!-- <label class="rmenu-checkbox-container pro-only">
                                            <input disabled type="checkbox" name="rmenu_show_quick_checkout_by_page[]" value="cross-sells" <?php //checked(in_array('cross-sells', $product_types_option)); 
                                                                                                                                            ?> />
                                            <span class="rmenu-checkbox-label">Cross-sells (Pro Features)</span>
                                        </label> -->
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
                                            <h4>Others</h4>
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

                                            <label class="rmenu-checkbox-container">
                                                <input type="checkbox" name="rmenu_show_quick_checkout_by_page[]" value="shortcodes" <?php checked(in_array('shortcodes', $product_types_option)); ?> />
                                                <span class="rmenu-checkbox-label">Other Pages</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="plugincy_row">
                        <div class="plugincy_col-5 items_center  space_between plugincy_card">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-cart"></span>', 'Quick Checkout Behavior', ''); ?>

                            <table class="form-table plugincy_table">
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Checkout Method', 'Choose how the quick checkout process should behave when a customer clicks the button.'); ?>
                                    </th>
                                    <td class="rmenu-settings-control">
                                        <select name="rmenu_wc_checkout_method" class="rmenu-select" id="rmenu-checkout-method">
                                            <option value="direct_checkout" <?php selected(get_option('rmenu_wc_checkout_method', 'direct_checkout'), 'direct_checkout'); ?>>Redirect to Checkout</option>
                                            <option value="ajax_add" <?php selected(get_option('rmenu_wc_checkout_method', 'direct_checkout'), 'ajax_add'); ?>>AJAX Add to Cart</option>
                                            <!-- rmenu_disable_cart_page is it's on disable below option & show cart page is disabled -->
                                            <?php
                                            $disable_cart_page = get_option('rmenu_disable_cart_page', '0');
                                            ?>
                                            <?php if (!$disable_cart_page) : ?>
                                                <option value="cart_redirect" <?php selected(get_option('rmenu_wc_checkout_method', 'direct_checkout'), 'cart_redirect'); ?>>Redirect to Cart Page</option>
                                            <?php else : ?>
                                                <option value="cart_redirect" disabled <?php selected(get_option('rmenu_wc_checkout_method', 'direct_checkout'), 'cart_redirect'); ?>>Redirect to Cart Page (Disabled)</option>
                                            <?php endif; ?>
                                            <option disabled value="popup_checkout_pro" <?php selected(get_option('rmenu_wc_checkout_method', 'direct_checkout'), 'popup_checkout_pro'); ?>>Popup Checkout (Pro Features)</option>
                                            <!-- <option disabled value="advanced_pro" <?php //selected(get_option('rmenu_wc_checkout_method', 'direct_checkout'), 'advanced_pro'); 
                                                                                        ?>>Advanced Checkout (Pro Features)</option> -->
                                            <option value="side_cart" <?php selected(get_option('rmenu_wc_checkout_method', 'direct_checkout'), 'side_cart'); ?>>Side Cart Slide-in</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Clear Cart Before Adding', 'When enabled, the cart will be emptied before adding the new product. This creates a single-product checkout experience.'); ?>
                                    <td class="rmenu-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenu_wc_clear_cart', 0); ?>
                                    </td>
                                </tr>

                                <!-- <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">Single Checkout without Clear Cart</label>
                            <div class="rmenu-settings-control pro-only">
                                <label class="rmenu-toggle-switch">
                                    <input disabled type="checkbox" name="rmenu_wc_single_checkout" value="1" <?php //checked(1, get_option("rmenu_wc_single_checkout", 0), true); 
                                                                                                                ?> />
                                    <span class="rmenu-toggle-slider"></span>
                                </label>
                                <p class="rmenu-field-description">When enabled, the cart will not be emptied before adding the new product.</p>
                            </div>
                        </div>
                    </div> -->

                                <!-- <div class="rmenu-settings-row">
                        <div class="rmenu-settings-field">
                            <label class="rmenu-settings-label">One-Click Purchase</label>
                            <div class="rmenu-settings-control pro-only">
                                <label class="rmenu-toggle-switch">
                                    <input disabled type="checkbox" name="rmenu_wc_one_click_purchase" value="1" <?php //checked(1, get_option("rmenu_wc_one_click_purchase", 0), true); 
                                                                                                                    ?> />
                                    <span class="rmenu-toggle-slider"></span>
                                </label>
                                <p class="rmenu-field-description">When enabled, returning customers can bypass the checkout form and use their last saved payment method. Requires WooCommerce Payments or compatible gateway.</p>
                            </div>
                        </div>
                    </div> -->

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Show Confirmation Dialog', 'When enabled, customers will see a confirmation dialog before proceeding to checkout.'); ?>
                                    <td class="rmenu-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenu_wc_add_confirmation', 0); ?>
                                    </td>
                                </tr>
                            </table>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const checkoutMethodSelect = document.getElementById('rmenu-checkout-method');
                                    const stickyCartCheckbox = document.querySelector('input[name="rmenu_enable_sticky_cart"]');
                                    const sideCartOption = checkoutMethodSelect.querySelector('option[value="side_cart"]');

                                    function updateSideCartOption() {
                                        if (!stickyCartCheckbox.checked) {
                                            sideCartOption.disabled = true;
                                            if (checkoutMethodSelect.value === 'side_cart') {
                                                showDirectCheckoutWarning(
                                                    checkoutMethodSelect.closest('tr'),
                                                    'Enable Sticky Cart in Floating Cart settings to use Side Cart Slide-in or use others Checkout Method.'
                                                );
                                            }
                                        } else {
                                            sideCartOption.disabled = false;
                                            removeDirectCheckoutWarning(checkoutMethodSelect.closest('tr'));
                                        }
                                    }

                                    stickyCartCheckbox.addEventListener('change', updateSideCartOption);
                                    checkoutMethodSelect.addEventListener('change', updateSideCartOption);

                                    // Initial check
                                    updateSideCartOption();
                                });
                            </script>

                        </div>
                        <div class="rmenu-settings-section direct-button-style-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-appearance"></span>', 'Button Style', ''); ?>

                            <table class="form-table plugincy_table">
                                <tbody class="plugincy_grid">
                                    <tr style="grid-column: span 2;">
                                        <th class="rmenu-settings-label">Button Style</th>
                                        <td class="rmenu-settings-control">
                                            <select name="rmenu_wc_checkout_style" class="rmenu-select" id="rmenu-style-select">
                                                <option value="default" <?php selected(get_option('rmenu_wc_checkout_style', 'default'), 'default'); ?>>Default WooCommerce Style</option>
                                                <option value="alt" <?php selected(get_option('rmenu_wc_checkout_style', 'default'), 'alt'); ?>>Alternative Style</option>
                                                <option value="custom" <?php selected(get_option('rmenu_wc_checkout_style', 'default'), 'custom'); ?>>Custom Style</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="rmenu-settings-label">Button Color</th>
                                        <td class="rmenu-settings-control">
                                            <input type="color" name="rmenu_wc_checkout_color" value="<?php echo esc_attr(get_option('rmenu_wc_checkout_color', '#000')); ?>" class="rmenu-color-picker" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="rmenu-settings-label">Text Color</th>
                                        <td class="rmenu-settings-control">
                                            <input type="color" name="rmenu_wc_checkout_text_color" value="<?php echo esc_attr(get_option('rmenu_wc_checkout_text_color', '#ffffff')); ?>" class="rmenu-color-picker" />
                                        </td>
                                    </tr>

                                    <tr class="rmenu-settings-row rmenu-custom-css-row" id="rmenu-custom-css-row" style="<?php echo (get_option('rmenu_wc_checkout_style', 'default') == 'custom') ? 'display:flex;' : 'display:none;'; ?> grid-column: span 2;">
                                        <th class="rmenu-settings-label">Custom CSS</th>
                                        <td class="rmenu-settings-control" style="max-width: 100% !important;">
                                            <textarea name="rmenu_wc_checkout_custom_css" class="rmenu-textarea-code" rows="6"><?php echo esc_textarea(get_option('rmenu_wc_checkout_custom_css', '')); ?></textarea>
                                            <p class="rmenu-field-description">Add custom CSS for advanced button styling. Use the class <code>.opqcfw-btn</code> to target the button.</p>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="rmenu-settings-label">Button Icon</th>
                                        <td class="rmenu-settings-control">
                                            <select name="rmenu_wc_checkout_icon" class="rmenu-select">
                                                <option value="none" <?php selected(get_option('rmenu_wc_checkout_icon', 'none'), 'none'); ?>>No Icon</option>
                                                <option value="cart" <?php selected(get_option('rmenu_wc_checkout_icon', 'none'), 'cart'); ?>>Cart Icon</option>
                                                <option value="checkout" <?php selected(get_option('rmenu_wc_checkout_icon', 'none'), 'checkout'); ?>>Checkout Icon</option>
                                                <option value="arrow" <?php selected(get_option('rmenu_wc_checkout_icon', 'none'), 'arrow'); ?>>Arrow Icon</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="rmenu-settings-label">Icon Position</th>
                                        <td class="rmenu-settings-control">
                                            <select name="rmenu_wc_checkout_icon_position" class="rmenu-select">
                                                <option value="left" <?php selected(get_option('rmenu_wc_checkout_icon_position', 'left'), 'left'); ?>>Left</option>
                                                <option value="right" <?php selected(get_option('rmenu_wc_checkout_icon_position', 'left'), 'right'); ?>>Right</option>
                                                <option value="top" <?php selected(get_option('rmenu_wc_checkout_icon_position', 'left'), 'top'); ?>>Top</option>
                                                <option value="bottom" <?php selected(get_option('rmenu_wc_checkout_icon_position', 'left'), 'bottom'); ?>>Bottom</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                            const button_style = document.querySelector('div#tab-4 select[name="rmenu_wc_checkout_style"]');

                            const buttonStyleSection = document.querySelector('.rmenu-settings-section.direct-button-style-section table tbody');
                            const allFields = Array.from(buttonStyleSection ? buttonStyleSection.children : []).slice(1);
                            // if rmenu_wc_checkout_icon is 'none', disable the icon position field
                            const iconSelect = document.querySelector('select[name="rmenu_wc_checkout_icon"]');
                            const iconPositionField = document.querySelector('select[name="rmenu_wc_checkout_icon_position"]');
                            if (iconSelect && iconPositionField) {
                                iconSelect.addEventListener('change', function() {
                                    toggleDisabledClass(this.value === 'none', iconPositionField);
                                });

                                // Trigger change event on page load to set initial visibility
                                setTimeout(() => {
                                    iconSelect.dispatchEvent(new Event('change'));
                                }, 1000);
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



                <div class="tab-content" id="direct-advanced" style="padding: 0;">
                    <div class="plugincy_row">
                        <div class="rmenu-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-category"></span>', 'Checkout in Variable Product'); ?>
                            <table class="form-table plugincy_table">
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Show Variation Selection in Archive pages', 'When enabled, the variation selection will be shown on archive pages.'); ?>
                                    <td class="rmenu-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenu_variation_show_archive', 1); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Variation Selection Layout', 'Choose variation selection Layout.'); ?>
                                    <td>
                                        <div class="rmenu-settings-control">
                                            <select name="rmenu_variation_layout" class="rmenu-select">
                                                <option value="combine" <?php selected(get_option('rmenu_variation_layout', 'separate'), 'combine'); ?>>Combine</option>
                                                <option value="separate" <?php selected(get_option('rmenu_variation_layout', 'separate'), 'separate'); ?>>Separate</option>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Show Variation Title', 'When enabled, the variation title will be shown on archive pages.'); ?>
                                    <td class="rmenu-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenu_show_variation_title', 0); ?>
                                    </td>
                                </tr>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const variationLayoutSelect = document.querySelector('select[name="rmenu_variation_layout"]');
                                        const variationTitleSwitch = document.querySelector('input[name="rmenu_show_variation_title"]');

                                        // Initial check
                                        setTimeout(() => {
                                            toggleDisabledClass(variationLayoutSelect.value === 'combine', variationTitleSwitch);
                                            variationLayoutSelect.dispatchEvent(new Event('change'));
                                        }, 1000);

                                        // Event listener for dropdown change
                                        variationLayoutSelect.addEventListener('change', function() {
                                            toggleDisabledClass(this.value === 'combine', variationTitleSwitch);
                                        });
                                    });
                                </script>
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Hide Select Option Button', 'When enabled, the select option button will be hidden on variable product pages.'); ?>
                                    <td class="rmenu-settings-control">
                                        <div class="rmenu-settings-control pro-only">
                                            <?php $onepaquc_helper->switcher('rmenu_wc_hide_select_option', 0, '', true); ?>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                            const enableCheckout = document.querySelector('div#tab-4 input[name="rmenu_variation_show_archive"]');

                            const allinputFields = Array.from(document.querySelectorAll('div#tab-4 table:nth-of-type(1) input, div#tab-4 table:nth-of-type(1) select')).filter(
                                el => !(el.name === "rmenu_variation_show_archive")
                            );
                            toggleDisabledClass(!enableCheckout.checked, allinputFields); // Set initial state
                            enableCheckout.addEventListener('change', function() {
                                toggleDisabledClass(!this.checked, allinputFields);
                            });
                        });
                    </script>
                        </div>

                        <div class="rmenu-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-tools"></span>', 'Advanced Options'); ?>

                            <!-- <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field">
                                <label class="rmenu-settings-label">Mobile Optimization</label>
                                <div class="rmenu-settings-control pro-only">
                                    <label class="rmenu-toggle-switch">
                                        <input disabled type="checkbox" name="rmenu_wc_checkout_mobile_optimize" value="1" <?php //checked(1, get_option("rmenu_wc_checkout_mobile_optimize", 1), true); 
                                                                                                                            ?> />
                                        <span class="rmenu-toggle-slider"></span>
                                    </label>
                                    <p class="rmenu-field-description">When enabled, the direct checkout button will be optimized for mobile devices.</p>
                                </div>
                            </div>
                        </div> -->
                            <table class="form-table plugincy_table">
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Enable for Guest Checkout', 'When enabled, the direct checkout button will be available for guest users. When disabled, only logged-in users will see the button.'); ?>
                                    <td class="rmenu-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenu_wc_checkout_guest_enabled', 1); ?>
                                    </td>
                                </tr>
                            </table>
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
                        padding: 10px;
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

                    .rmenu-settings-label {
                        font-weight: 600;
                        margin-bottom: 8px;
                        font-size: 14px;
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
                        background-color: #4caf50;
                    }

                    input:focus+.rmenu-toggle-slider {
                        box-shadow: 0 0 1px #4caf50;
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
                                $('#rmenu-custom-css-row').css('display', 'flex');
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
                    toggleDisabledClass(!enableCheckout.checked, allinputFields);
                    enableCheckout.addEventListener('change', function() {
                        toggleDisabledClass(!this.checked, allinputFields);
                    });
                });
            </script>
            <div class="tab-content plugincy_card" id="tab-5" style="margin-top: 20px;">
                <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head', '<span class="dashicons dashicons-star-filled"></span>', 'Manage Features', 'Enhance your WooCommerce checkout experience with these powerful features.'); ?>
                <table class="form-table plugincy_table">
                    <tbody style="display: grid; grid-template-columns: repeat(auto-fill, minmax(410px, 1fr));">
                        <tr valign="top">
                            <?php $onepaquc_helper->sec_head('th', '', '', 'Product Quantity Controller', 'Enable "Product Quantity Controller" to manage product quantities in the checkout form.'); ?>
                            <td>
                                <?php $onepaquc_helper->switcher('rmenu_quantity_control', 1); ?>
                            </td>
                        </tr>
                        <tr valign="top" class="pro-only top-50">
                            <?php $onepaquc_helper->sec_head('th', '', '', 'Remove Product Button', 'Enable "Remove Product Button" to allow customers to remove products from the checkout form.'); ?>
                            <td>
                                <?php $onepaquc_helper->switcher('rmenu_remove_product', 0); ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <?php $onepaquc_helper->sec_head('th', '', '', 'Add Image Before Product', 'Enable "Add Image Before Product" to display product images before their titles in the checkout form.'); ?>
                            <td>
                                <?php $onepaquc_helper->switcher('rmenu_add_img_before_product', 0); ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <?php $onepaquc_helper->sec_head('th', '', '', 'At Least One Product in Cart', 'Enable "At Least One Product in Cart" to add at least one product in the cart.'); ?>
                            <td>
                                <?php $onepaquc_helper->switcher('rmenu_at_one_product_cart', 0); ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <?php $onepaquc_helper->sec_head('th', '', '', 'Disable Cart Page', 'Enable "Disable Cart Page" to remove the cart page from your WooCommerce store. This will redirect customers directly to the checkout page.'); ?>
                            <td>
                                <?php $onepaquc_helper->switcher('rmenu_disable_cart_page', 0); ?>
                            </td>
                        </tr>
                        <!-- <tr valign="top" class="pro-only top-50">
                        <th scope="row">Express Checkout Options</th>
                        <td>
                            <label class="switch">
                                <input disabled type="checkbox" name="rmenu_express_checkout" value="1" <?php //checked(1, get_option("rmenu_express_checkout", 0), true); 
                                                                                                        ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "Express Checkout Options" to allow customers to use express checkout methods like PayPal, Stripe, etc.</span>
                            </span>
                        </td>
                    </tr> -->
                        <!-- <tr valign="top" class="pro-only top-50">
                        <th scope="row">Address Auto-Complete</th>
                        <td>
                            <label class="switch">
                                <input disabled type="checkbox" name="rmenu_address_auto_complete" value="1" <?php //checked(1, get_option("rmenu_address_auto_complete", 0), true); 
                                                                                                                ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "Address Auto-Complete" to automatically fill in the address fields based on the customer's input.</span>
                            </span>
                        </td>
                    </tr> -->
                        <!-- <tr valign="top" class="pro-only top-50">
                        <th scope="row">Multi-Step Checkout </th>
                        <td>
                            <label class="switch">
                                <input disabled type="checkbox" name="rmenu_multi_step_checkout" value="1" <?php //checked(1, get_option("rmenu_multi_step_checkout", 0), true); 
                                                                                                            ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "Multi-Step Checkout " to split the checkout process into multiple steps for a better user experience.</span>
                            </span>
                        </td>
                    </tr> -->
                        <tr valign="top" class="pro-only top-50">
                            <?php $onepaquc_helper->sec_head('th', '', '', 'Force Login Before Checkout', 'Enable "Force Login Before Checkout" to require customers to log in before they can access the checkout page.'); ?>
                            <td>
                                <?php $onepaquc_helper->switcher('rmenu_force_login', 0); ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <?php $onepaquc_helper->sec_head('th', '', '', 'Link Product Name in Checkout Page', 'Enable "Link Product Name in Checkout Page" to make the product names clickable, redirecting customers to the product page.'); ?>
                            <td>
                                <?php $onepaquc_helper->switcher('rmenu_link_product', 1); ?>
                            </td>
                        </tr>
                        <!-- <tr valign="top" class="pro-only top-50">
                        <th scope="row">Enable Captcha on Checkout Page</th>
                        <td>
                            <label class="switch">
                                <input disabled type="checkbox" name="rmenu_enable_captcha" value="1" <?php //checked(1, get_option("rmenu_enable_captcha", 0), true); 
                                                                                                        ?> />
                                <span class="slider round"></span>
                            </label>
                            <span class="tooltip">
                                <span class="question-mark">?</span>
                                <span class="tooltip-text">Enable "Enable Captcha on Checkout Page" to add a captcha verification step to the checkout process, helping to prevent spam and automated submissions.</span>
                            </span>
                        </td>
                    </tr> -->
                    </tbody>
                </table>
            </div>
            <div class="tab-content plugincy_card" id="tab-6" style="margin-top: 20px;">
                <?php onepaquc_trust_badges_settings_content(); ?>
                <table style="padding-top: 1rem;">
                    <tr valign="top" style="display: flex; gap: 51px;">
                        <?php $onepaquc_helper->sec_head('th', '', '', 'Contribute to Plugincy', 'We collect non-sensitive technical details from your website, like the PHP version and features usage, to help us troubleshoot issues faster, make informed development decisions, and build features that truly benefit you. <a href="https://plugincy.com/usage-tracking/" target="_blank" style="color:#fff;">Learn more…</a>'); ?>
                        <td>
                            <?php $onepaquc_helper->switcher('rmenu_allow_analytics', 1); ?>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="tab-content" id="tab-7">
                <div class="plugincy_nav_card mb-4">
                    <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head2', '<span class="dashicons dashicons-visibility"></span>', 'WooCommerce Quick View', '', 'Manage the quick view settings for your WooCommerce products.'); ?>
                    <div class="rmenu-settings-tabs">
                        <ul class="rmenu-settings-tab-list">
                            <li class="rmenu-settings-tab-item active" data-tab="quick-general-settings">
                                <span class="dashicons dashicons-admin-generic"></span> General Settings
                            </li>
                            <li class="rmenu-settings-tab-item" data-tab="quick-popup">
                                <span class="dashicons dashicons-admin-comments"></span> Popup Manage
                            </li>
                            <li class="rmenu-settings-tab-item" data-tab="quick-advanced">
                                <span class="dashicons dashicons-admin-tools"></span> Advanced
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="tab-content" id="quick-general-settings" style="padding: 0;">
                    <div class="plugincy_row">
                        <div class="rmenu-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-generic"></span>', 'General Settings', ''); ?>
                            <table class="form-table plugincy_table">
                                <tr id="rmenu-quick-view-enable-field">
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Enable Quick View', 'Enable or disable the quick view functionality across your WooCommerce store.'); ?>
                                    <td class="rmenu-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenu_enable_quick_view', 0); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Button Text', 'Customize the text displayed on the quick view button.'); ?>
                                    <td class="rmenu-settings-control">
                                        <?php
                                        $quick_view_button_text = get_option('rmenu_quick_view_button_text', '');
                                        if (empty($quick_view_button_text)) {
                                            $quick_view_button_text = 'Quick View';
                                        }
                                        ?>
                                        <input type="text" name="rmenu_quick_view_button_text" value="<?php echo esc_attr($quick_view_button_text); ?>" class="regular-text" />
                                    </td>
                                </tr>
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Button Position', 'Choose where to display the quick view button on product listings.'); ?>
                                    <td class="rmenu-settings-control">
                                        <select name="rmenu_quick_view_button_position" class="rmenu-select">
                                            <option value="after_image" <?php selected(get_option('rmenu_quick_view_button_position', 'image_overlay'), 'after_image'); ?>>After Product Image</option>
                                            <!-- <option disabled value="before_title" <?php //selected(get_option('rmenu_quick_view_button_position', 'image_overlay'), 'before_title'); 
                                                                                        ?>>Before Product Title (Pro Features)</option>
                                        <option disabled value="after_title" <?php //selected(get_option('rmenu_quick_view_button_position', 'image_overlay'), 'after_title'); 
                                                                                ?>>After Product Title (Pro Features)</option>
                                        <option disabled value="before_price" <?php //selected(get_option('rmenu_quick_view_button_position', 'image_overlay'), 'before_price'); 
                                                                                ?>>Before Product Price (Pro Features)</option>
                                        <option disabled value="after_price" <?php //selected(get_option('rmenu_quick_view_button_position', 'image_overlay'), 'after_price'); 
                                                                                ?>>After Product Price (Pro Features)</option>
                                        <option disabled value="before_add_to_cart" <?php //selected(get_option('rmenu_quick_view_button_position', 'image_overlay'), 'before_add_to_cart'); 
                                                                                    ?>>Before Add to Cart Button (Pro Features)</option> -->
                                            <option value="after_add_to_cart" <?php selected(get_option('rmenu_quick_view_button_position', 'image_overlay'), 'after_add_to_cart'); ?>>After Add to Cart Button</option>
                                            <option value="image_overlay" <?php selected(get_option('rmenu_quick_view_button_position', 'image_overlay'), 'image_overlay'); ?>>Overlay on Product Image</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Display Type', 'Choose how the quick view trigger should appear to customers.'); ?>
                                    <td class="rmenu-settings-control">
                                        <select name="rmenu_quick_view_display_type" class="rmenu-select">
                                            <option value="button" <?php selected(get_option('rmenu_quick_view_display_type', 'icon'), 'button'); ?>>Button</option>
                                            <option value="icon" <?php selected(get_option('rmenu_quick_view_display_type', 'icon'), 'icon'); ?>>Icon Only</option>
                                            <option value="text_icon" <?php selected(get_option('rmenu_quick_view_display_type', 'icon'), 'text_icon'); ?>>Text with Icon</option>
                                            <!-- <option value="hover_icon" <?php //selected(get_option('rmenu_quick_view_display_type', 'icon'), 'hover_icon'); 
                                                                            ?>>Hover Icon</option> -->
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="rmenu-settings-section quick-view-button-style plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-appearance"></span>', 'Button Style', ''); ?>

                            <table class="form-table plugincy_table">
                                <tbody class="plugincy_grid">
                                    <tr style="grid-column: span 2;">
                                        <?php $onepaquc_helper->sec_head('th', '', '', 'Button Style', ''); ?>
                                        <td class="rmenu-settings-control">
                                            <select name="rmenu_quick_view_button_style" class="rmenu-select" id="rmenu-qv-style-select">
                                                <option value="default" <?php selected(get_option('rmenu_quick_view_button_style', 'default'), 'default'); ?>>Default WooCommerce Style</option>
                                                <option value="alt" <?php selected(get_option('rmenu_quick_view_button_style', 'default'), 'alt'); ?>>Alternative Style</option>
                                                <option value="custom" <?php selected(get_option('rmenu_quick_view_button_style', 'default'), 'custom'); ?>>Custom Style</option>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr>
                                        <?php $onepaquc_helper->sec_head('th', '', '', 'Button Color', ''); ?>
                                        <td class="rmenu-settings-control">
                                            <input type="color" name="rmenu_quick_view_button_color" value="<?php echo esc_attr(get_option('rmenu_quick_view_button_color', '#000')); ?>" class="rmenu-color-picker" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <?php $onepaquc_helper->sec_head('th', '', '', 'Text Color', ''); ?>
                                        <td class="rmenu-settings-control">
                                            <input type="color" name="rmenu_quick_view_text_color" value="<?php echo esc_attr(get_option('rmenu_quick_view_text_color', '#ffffff')); ?>" class="rmenu-color-picker" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <?php $onepaquc_helper->sec_head('th', '', '', 'Button Icon', ''); ?>
                                        <td class="rmenu-settings-control">
                                            <select name="rmenu_quick_view_button_icon" class="rmenu-select">
                                                <option value="none" <?php selected(get_option('rmenu_quick_view_button_icon', 'eye'), 'none'); ?>>No Icon</option>
                                                <option value="eye" <?php selected(get_option('rmenu_quick_view_button_icon', 'eye'), 'eye'); ?>>Eye Icon</option>
                                                <option value="search" <?php selected(get_option('rmenu_quick_view_button_icon', 'eye'), 'search'); ?>>Search Icon</option>
                                                <option value="zoom" <?php selected(get_option('rmenu_quick_view_button_icon', 'eye'), 'zoom'); ?>>Zoom Icon</option>
                                                <option value="preview" <?php selected(get_option('rmenu_quick_view_button_icon', 'eye'), 'preview'); ?>>Preview Icon</option>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr>
                                        <?php $onepaquc_helper->sec_head('th', '', '', 'Icon Position', ''); ?>
                                        <td class="rmenu-settings-control">
                                            <select name="rmenu_quick_view_icon_position" class="rmenu-select">
                                                <option value="left" <?php selected(get_option('rmenu_quick_view_icon_position', 'left'), 'left'); ?>>Left</option>
                                                <option value="right" <?php selected(get_option('rmenu_quick_view_icon_position', 'left'), 'right'); ?>>Right</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr id="rmenu-qv-custom-css-row" style="<?php echo (get_option('rmenu_quick_view_button_style', 'default') == 'custom') ? 'display:block;' : 'display:none;'; ?> grid-column: span 2;">
                                        <?php $onepaquc_helper->sec_head('th', '', '', 'Custom CSS', ''); ?>
                                        <td class="rmenu-settings-control">
                                            <textarea name="rmenu_quick_view_custom_css" class="rmenu-textarea-code" rows="6"><?php echo esc_textarea(get_option('rmenu_quick_view_custom_css', '')); ?></textarea>
                                            <p class="rmenu-field-description">Add custom CSS for advanced button styling. Use the class <code>.opqvfw-btn</code> to target the button and <code>.opqvfw-modal</code> to target the modal.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                            const button_style = document.querySelector('div#tab-7 select[name="rmenu_quick_view_button_style"]');

                            const buttonStyleSection = document.querySelector('.rmenu-settings-section.quick-view-button-style table tbody');
                            const allFields = Array.from(buttonStyleSection ? buttonStyleSection.children : []).slice(1);

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
                <div class="tab-content" id="quick-popup" style="padding: 0;">
                    <div class="plugincy_row mb-4">
                        <div class="rmenu-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-visibility"></span>', 'Quick View Content', ''); ?>
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

                                            <!-- <label class="rmenu-checkbox-container">
                                        <input type="checkbox" name="rmenu_quick_view_content_elements[]" value="quantity" <?php //checked(in_array('quantity', $content_elements_option)); 
                                                                                                                            ?> />
                                        <span class="rmenu-checkbox-label">Quantity Selector</span>
                                    </label> -->
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
                                                <input type="checkbox" id="view_details_checkbox" name="rmenu_quick_view_content_elements[]" value="view_details" <?php checked(in_array('view_details', $content_elements_option)); ?> />
                                                <span class="rmenu-checkbox-label">View Details Link</span>
                                            </label>

                                            <!-- <label class="rmenu-checkbox-container pro-only">
                                        <input disabled type="checkbox" name="rmenu_quick_view_content_elements[]" value="attributes" <?php //checked(in_array('attributes', $content_elements_option)); 
                                                                                                                                        ?> />
                                        <span class="rmenu-checkbox-label">Product Attributes</span>
                                    </label> -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <table class="form-table plugincy_table">
                                <tbody class="plugincy_grid">
                                    <tr>
                                        <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Modal Size', 'Choose the size of the quick view modal popup.'); ?>
                                        <td class="rmenu-settings-control pro-only">
                                            <select disabled name="rmenu_quick_view_modal_size" class="rmenu-select">
                                                <option value="small" <?php selected(get_option('rmenu_quick_view_modal_size', 'medium'), 'small'); ?>>Small</option>
                                                <option value="medium" <?php selected(get_option('rmenu_quick_view_modal_size', 'medium'), 'medium'); ?>>Medium</option>
                                                <option value="large" <?php selected(get_option('rmenu_quick_view_modal_size', 'medium'), 'large'); ?>>Large</option>
                                                <option value="full" <?php selected(get_option('rmenu_quick_view_modal_size', 'medium'), 'full'); ?>>Full Width</option>
                                                <option value="custom" <?php selected(get_option('rmenu_quick_view_modal_size', 'medium'), 'custom'); ?>>Custom</option>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr id="rmenu-custom-size-row" style="<?php echo (get_option('rmenu_quick_view_modal_size', 'medium') == 'custom') ? 'display:flex;' : 'display:none;'; ?>">
                                        <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Custom Width', 'Custom width in pixels (e.g., 800).'); ?>
                                        <td class="rmenu-settings-control pro-only">
                                            <input disabled type="text" name="rmenu_quick_view_custom_width" value="<?php echo esc_attr(get_option('rmenu_quick_view_custom_width', '800')); ?>" class="regular-text" />
                                        </td>
                                    </tr>
                                    <tr id="rmenu-custom-size-row" style="<?php echo (get_option('rmenu_quick_view_modal_size', 'medium') == 'custom') ? 'display:flex;' : 'display:none;'; ?>">
                                        <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Custom Height', 'Custom height in pixels (e.g., 600) or \'auto\'.'); ?>
                                        <td class="rmenu-settings-control pro-only">
                                            <input disabled type="text" name="rmenu_quick_view_custom_height" value="<?php echo esc_attr(get_option('rmenu_quick_view_custom_height', '600')); ?>" class="regular-text" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Loading Effect', 'Choose the animation effect when opening the quick view modal.'); ?>
                                        <td class="rmenu-settings-control pro-only">
                                            <select disabled name="rmenu_quick_view_loading_effect" class="rmenu-select">
                                                <option value="fade" <?php selected(get_option('rmenu_quick_view_loading_effect', 'fade'), 'fade'); ?>>Fade</option>
                                                <option value="slide" <?php selected(get_option('rmenu_quick_view_loading_effect', 'fade'), 'slide'); ?>>Slide</option>
                                                <option value="zoom" <?php selected(get_option('rmenu_quick_view_loading_effect', 'fade'), 'zoom'); ?>>Zoom</option>
                                                <option value="none" <?php selected(get_option('rmenu_quick_view_loading_effect', 'fade'), 'none'); ?>>None</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="rmenu-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-layout"></span>', 'Display Settings', ''); ?>
                            <div class="rmenu-settings-row">
                                <div class="rmenu-settings-field">
                                    <label class="rmenu-settings-label">Product Types</label>
                                    <?php $product_types_option = get_option('rmenu_show_quick_view_by_types', ['simple', 'variable', "grouped", "external"]); ?>
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
                                </div>
                            </div>

                            <div class="rmenu-settings-row">
                                <div class="rmenu-settings-field">
                                    <?php $product_pages_option = get_option('rmenu_show_quick_view_by_page', ['shop-page', 'category-archives', "tag-archives", 'search', "featured-products", "on-sale", "recent", "widgets", "shortcodes"]); ?>
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
                    </div>
                    <div class="rmenu-settings-section plugincy_card">
                        <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-translation"></span>', 'Translations', ''); ?>
                        <table class="form-table plugincy_table">
                            <tr>
                                <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', '"Quick View" Text', 'Customize the text for the quick view button or link.'); ?>
                                <td class="rmenu-settings-control">
                                    <?php
                                    $details_text = get_option('rmenu_quick_view_details_text', '');
                                    if (empty($details_text)) {
                                        $details_text = 'View Full Details';
                                    }
                                    ?>
                                    <input type="text" id="view_details_text" name="rmenu_quick_view_details_text" value="<?php echo esc_attr($details_text); ?>" class="regular-text" />
                                </td>
                            </tr>
                        </table>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Select the checkbox and the text input
                            const enableCheckout = document.getElementById('view_details_checkbox');
                            const allInputFields = document.getElementById('view_details_text');

                            // Function to toggle the disabled class
                            function toggleDisabledClass(disable, inputField) {
                                inputField.disabled = disable; // Toggle the disabled property
                            }

                            // Set initial state
                            toggleDisabledClass(!enableCheckout.checked, allInputFields);

                            // Add event listener for checkbox change
                            enableCheckout.addEventListener('change', function() {
                                toggleDisabledClass(!this.checked, allInputFields);
                            });
                        });
                    </script>
                </div>
                <div class="tab-content" id="quick-advanced" style="padding: 0;">
                    <div class="plugincy_row mb-4">
                        <div class="rmenu-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-tools"></span>', 'Advanced Options', ''); ?>

                            <table class="form-table plugincy_table">
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Mobile Optimization', 'When enabled, the quick view functionality will be optimized for mobile devices.'); ?>
                                    <td class="rmenu-settings-control pro-only">
                                        <?php $onepaquc_helper->switcher('rmenu_quick_view_mobile_optimize', 1); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Close on Add to Cart', 'When enabled, the quick view popup will automatically close after adding a product to cart.'); ?>
                                    <td class="rmenu-settings-control pro-only">
                                        <?php $onepaquc_helper->switcher('rmenu_quick_view_close_on_add', 0); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Keyboard Navigation', 'When enabled, customers can use keyboard arrows to navigate between products in quick view and ESC to close.'); ?>
                                    <td class="rmenu-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenu_quick_view_keyboard_nav', 1); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="rmenu-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-analytics"></span>', 'Analytics Integration', ''); ?>

                            <table class="form-table plugincy_table">
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Track Quick View Events', 'Track when customers use quick view in Google Analytics or other analytics tools.'); ?>
                                    <td class="rmenu-settings-control pro-only">
                                        <?php $onepaquc_helper->switcher('rmenu_quick_view_track_events', 0); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Event Category', 'The event category name used for analytics tracking.'); ?>
                                    <td class="rmenu-settings-control pro-only">
                                        <input disabled type="text" name="rmenu_quick_view_event_category" value="<?php echo esc_attr(get_option('rmenu_quick_view_event_category', 'one-page-quick-checkout-for-woocommerce')); ?>" class="regular-text" />
                                        <span class="dashicons dashicons-lock plugincy_lock-icon"></span>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Event Action', 'The event action name used for analytics tracking.'); ?>
                                    <td class="rmenu-settings-control pro-only">
                                        <input disabled type="text" name="rmenu_quick_view_event_action" value="<?php echo esc_attr(get_option('rmenu_quick_view_event_action', 'Quick View')); ?>" class="regular-text" />
                                        <span class="dashicons dashicons-lock plugincy_lock-icon"></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="rmenu-settings-section plugincy_card">
                        <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-generic"></span>', 'Compatibility Settings'); ?>
                        <table class="form-table plugincy_table">
                            <tr>
                                <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Load Scripts On', 'Control where quick view scripts are loaded to improve compatibility and performance.'); ?>
                                <td class="rmenu-settings-control pro-only">
                                    <select disabled name="rmenu_quick_view_load_scripts" class="rmenu-select">
                                        <option value="all" <?php selected(get_option('rmenu_quick_view_load_scripts', 'wc-only'), 'all'); ?>>All Pages</option>
                                        <option value="wc-only" <?php selected(get_option('rmenu_quick_view_load_scripts', 'wc-only'), 'wc-only'); ?>>WooCommerce Pages Only</option>
                                        <option value="specific" <?php selected(get_option('rmenu_quick_view_load_scripts', 'wc-only'), 'specific'); ?>>Specific Pages Only</option>
                                    </select>
                                    <span class="dashicons dashicons-lock plugincy_lock-icon"></span>
                                </td>
                            </tr>
                            <tr class="rmenu-settings-row" id="rmenu-specific-pages-row" style="<?php echo (get_option('rmenu_quick_view_load_scripts', 'wc-only') == 'specific') ? 'display:block;' : 'display:none;'; ?>">
                                <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Specific Pages IDs', 'Enter the IDs of the specific pages where you want to load the quick view.'); ?>
                                <td class="rmenu-settings-control pro-only">
                                    <input disabled type="text" name="rmenu_quick_view_specific_pages" value="<?php echo esc_attr(get_option('rmenu_quick_view_specific_pages', '')); ?>" class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Theme Compatibility Mode', 'Enable this if you experience display issues with your theme.'); ?>
                                <td class="rmenu-settings-control">
                                    <?php $onepaquc_helper->switcher('rmenu_quick_view_theme_compat', 0); ?>
                                </td>
                            <tr>
                        </table>
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
                        toggleDisabledClass(this.value === 'icon', quickViewButtonText);

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

                    // Trigger change event on page load to set initial visibility
                    quickViewDisplayType.dispatchEvent(new Event('change'));

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
                    toggleDisabledClass(!enableCheckout.checked, allinputFields);
                    enableCheckout.addEventListener('change', function() {
                        toggleDisabledClass(!this.checked, allinputFields);
                    });

                    // Show/hide custom CSS row based on selected style after page load
                    const styleSelect = document.querySelector('select[name="rmenu_quick_view_button_style"]');
                    const customCssRow = document.querySelector('textarea[name="rmenu_quick_view_custom_css"]').closest('tr');
                    const quickViewButtonIconPos = document.querySelector('div#tab-7 select[name="rmenu_quick_view_icon_position"]');
                    const quickViewButtonIconRow = quickViewButtonIcon.closest('tr');
                    const quickViewButtonIconPosRow = quickViewButtonIconPos.closest('tr');

                    const updateQuickViewDisplayType = () => {
                        if (quickViewDisplayType.value === 'button') {
                            quickViewButtonIconRow.style.display = 'none';
                            quickViewButtonIconPosRow.style.display = 'none';

                        } else if (quickViewDisplayType.value === 'icon') {
                            quickViewButtonIconRow.style.display = 'flex';
                            quickViewButtonIconPosRow.style.display = 'none';
                        } else {
                            quickViewButtonIconRow.style.display = 'flex';
                            quickViewButtonIconPosRow.style.display = 'flex';
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
                <div class="plugincy_nav_card mb-4">
                    <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head2', '<span class="dashicons dashicons-cart"></span>', 'WooCommerce Add To Cart', '', 'Easily modify the Add to Cart button text, style, and functionality for a more engaging shopping experience.'); ?>
                    <div class="rmenu-settings-tabs">
                        <ul class="rmenu-settings-tab-list">
                            <li class="rmenu-settings-tab-item active" data-tab="general-settings">
                                <span class="dashicons dashicons-admin-generic"></span> General Settings
                            </li>
                            <li class="rmenu-settings-tab-item" data-tab="button-behavior">
                                <span class="dashicons dashicons-controls-play"></span> Button Behavior
                            </li>
                            <li class="rmenu-settings-tab-item" data-tab="advanced">
                                <span class="dashicons dashicons-admin-tools"></span> Advanced
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="tab-content" id="general-settings" style="padding: 0;">
                    <div class="plugincy_row">
                        <div class="rmenu-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-generic"></span>', 'General Settings', ''); ?>

                            <table class="form-table plugincy_table">
                                <tr id="rmenu-enable-custom-add-to-cart">
                                    <?php $onepaquc_helper->sec_head('th', '', '', 'Customizable Add to Cart', 'Enable or disable custom Add to Cart styling and functionality.'); ?>
                                    <td class="rmenu-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenu_enable_custom_add_to_cart', 1); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Button Text', 'Customize the text displayed on the Add to Cart button for simple products.'); ?>
                                    <td class="rmenu-settings-control">
                                        <input type="text" name="txt-add-to-cart" value="<?php echo esc_attr(get_option('txt-add-to-cart', 'Add to Cart')); ?>" class="regular-text" />
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Variable Product Button Text', 'Customize the text displayed on the Add to Cart button for variable products.'); ?>
                                    <td class="rmenu-settings-control">
                                        <input type="text" name="txt-select-options" value="<?php echo esc_attr(get_option('txt-select-options', 'Select Options')); ?>" class="regular-text" />
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Read More Button Text', 'Customize the text displayed on the Read More button.'); ?>
                                    <td class="rmenu-settings-control">
                                        <input type="text" name="txt-read-more" value="<?php echo esc_attr(get_option('txt-read-more', 'Read More')); ?>" class="regular-text" />
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Grouped Product Button Text', 'Customize the text displayed on the Add to Cart button for grouped products.'); ?>
                                    <td class="rmenu-settings-control">
                                        <input type="text" name="rmenu_grouped_add_to_cart_text" value="<?php echo esc_attr(get_option('rmenu_grouped_add_to_cart_text', 'View Products')); ?>" class="regular-text" />
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="rmenu-settings-section button-style-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-admin-appearance"></span>', 'Button Style', ''); ?>

                            <table class="form-table plugincy_table">
                                <tbody class="plugincy_grid">
                                    <tr style="grid-column: span 2;">
                                        <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Button Style', ''); ?>
                                        <td class="rmenu-settings-control">
                                            <select name="rmenu_add_to_cart_style" class="rmenu-select" id="rmenu-atc-style-select">
                                                <option value="default" <?php selected(get_option('rmenu_add_to_cart_style', 'default'), 'default'); ?>>Default WooCommerce Style</option>
                                                <option disabled value="modern" <?php selected(get_option('rmenu_add_to_cart_style', 'default'), 'modern'); ?>>Modern Style (Pro Feature)</option>
                                                <option value="rounded" <?php selected(get_option('rmenu_add_to_cart_style', 'default'), 'rounded'); ?>>Rounded Style</option>
                                                <option disabled value="minimal" <?php selected(get_option('rmenu_add_to_cart_style', 'default'), 'minimal'); ?>>Minimal Style (Pro Feature)</option>
                                                <option value="custom" <?php selected(get_option('rmenu_add_to_cart_style', 'default'), 'custom'); ?>>Custom Style</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="rmenu-settings-label">Button Color</th>
                                        <td class="rmenu-settings-control">
                                            <input type="color" name="rmenu_add_to_cart_bg_color" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_bg_color', '#000')); ?>" class="rmenu-color-picker" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="rmenu-settings-label">Text Color</th>
                                        <td class="rmenu-settings-control">
                                            <input type="color" name="rmenu_add_to_cart_text_color" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_text_color', '#ffffff')); ?>" class="rmenu-color-picker" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="rmenu-settings-label">Hover Background Color</th>
                                        <td class="rmenu-settings-control">
                                            <input type="color" name="rmenu_add_to_cart_hover_bg_color" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_hover_bg_color', '#7f4579')); ?>" class="rmenu-color-picker" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="rmenu-settings-label">Hover Text Color</th>
                                        <td class="rmenu-settings-control">
                                            <input type="color" name="rmenu_add_to_cart_hover_text_color" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_hover_text_color', '#ffffff')); ?>" class="rmenu-color-picker" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="rmenu-settings-label">Border Radius</th>
                                        <td class="rmenu-settings-control">
                                            <input type="number" name="rmenu_add_to_cart_border_radius" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_border_radius', '3')); ?>" class="small-text" min="0" max="50" step="1" />
                                            <span class="rmenu-unit">px</span>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="rmenu-settings-label">Button Font Size</th>
                                        <td class="rmenu-settings-control">
                                            <input type="number" name="rmenu_add_to_cart_font_size" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_font_size', '14')); ?>" class="small-text" min="10" max="24" step="1" />
                                            <span class="rmenu-unit">px</span>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="rmenu-settings-label">Button Width</th>
                                        <td class="rmenu-settings-control">
                                            <select name="rmenu_add_to_cart_width" class="rmenu-select">
                                                <option value="auto" <?php selected(get_option('rmenu_add_to_cart_width', 'auto'), 'auto'); ?>>Auto</option>
                                                <option value="full" <?php selected(get_option('rmenu_add_to_cart_width', 'auto'), 'full'); ?>>Full Width</option>
                                                <option value="custom" <?php selected(get_option('rmenu_add_to_cart_width', 'auto'), 'custom'); ?>>Custom Width</option>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr id="rmenu-atc-custom-width-row" style="<?php echo (get_option('rmenu_add_to_cart_width', 'auto') == 'custom') ? 'display:block;' : 'display:none;'; ?>">
                                        <th class="rmenu-settings-label">Custom Width Value</th>
                                        <td class="rmenu-settings-control">
                                            <input type="number" name="rmenu_add_to_cart_custom_width" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_custom_width', '150')); ?>" class="small-text" min="50" max="500" step="1" />
                                            <span class="rmenu-unit">px</span>
                                        </td>
                                    </tr>

                                    <!-- <div class="rmenu-settings-row rmenu-settings-row-columns">
                            <div class="rmenu-settings-column">
                                <div class="rmenu-settings-field">
                                    <label class="rmenu-settings-label">Button Icon</label>
                                    <div class="rmenu-settings-control">
                                        <select name="rmenu_add_to_cart_icon" class="rmenu-select">
                                            <option value="none" <?php //selected(get_option('rmenu_add_to_cart_icon', 'none'), 'none'); 
                                                                    ?>>No Icon</option>
                                            <option value="cart" <?php //selected(get_option('rmenu_add_to_cart_icon', 'none'), 'cart'); 
                                                                    ?>>Cart Icon</option>
                                            <option value="plus" <?php //selected(get_option('rmenu_add_to_cart_icon', 'none'), 'plus'); 
                                                                    ?>>Plus Icon</option>
                                            <option value="bag" <?php //selected(get_option('rmenu_add_to_cart_icon', 'none'), 'bag'); 
                                                                ?>>Shopping Bag Icon</option>
                                            <option value="basket" <?php //selected(get_option('rmenu_add_to_cart_icon', 'none'), 'basket'); 
                                                                    ?>>Basket Icon</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="rmenu-settings-column" id="rmenu-atc-icon-position-row">
                                <div class="rmenu-settings-field">
                                    <label class="rmenu-settings-label">Icon Position</label>
                                    <div class="rmenu-settings-control">
                                        <select name="rmenu_add_to_cart_icon_position" class="rmenu-select">
                                            <option value="left" <?php //selected(get_option('rmenu_add_to_cart_icon_position', 'left'), 'left'); 
                                                                    ?>>Left</option>
                                            <option value="right" <?php //selected(get_option('rmenu_add_to_cart_icon_position', 'left'), 'right'); 
                                                                    ?>>Right</option>
                                            <option value="top" <?php //selected(get_option('rmenu_add_to_cart_icon_position', 'left'), 'top'); 
                                                                ?>>Top</option>
                                            <option value="bottom" <?php //selected(get_option('rmenu_add_to_cart_icon_position', 'left'), 'bottom'); 
                                                                    ?>>Bottom</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                                    <tr class="rmenu-settings-row rmenu-custom-css-row" id="rmenu-atc-custom-css-row" style="<?php echo (get_option('rmenu_add_to_cart_style', 'default') == 'custom') ? 'display:block;' : 'display:none;'; ?> grid-column: span 2;">
                                        <th class="rmenu-settings-label">Custom CSS</th>
                                        <td class="rmenu-settings-control">
                                            <textarea name="rmenu_add_to_cart_custom_css" class="rmenu-textarea-code" rows="6"><?php echo esc_textarea(get_option('rmenu_add_to_cart_custom_css', '')); ?></textarea>
                                            <p class="rmenu-field-description">Add custom CSS for advanced button styling. Use the class <code>.product_type_simple.add_to_cart_button</code> to target the button.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // if the "Enable One Page Checkout" checkbox is checked, enable the "Checkout Layout" select
                            const button_style = document.querySelector('div#tab-8 select[name="rmenu_add_to_cart_style"]');

                            // Select all children except the first two in the .button-style-section & except div#rmenu-atc-custom-width-row
                            const buttonStyleSection = document.querySelector('.button-style-section table tbody');
                            const allFields = Array.from(buttonStyleSection ? buttonStyleSection.children : []).slice(1);
                            const customWidthRow = document.getElementById('rmenu-atc-custom-width-row');
                            // if rmenu_add_to_cart_icon is none, hide the rmenu-atc-icon-position-row
                            const iconPositionRow = document.getElementById('rmenu-atc-icon-position-row');
                            const iconSelect = document.querySelector('select[name="rmenu_add_to_cart_icon"]');
                            const customwidth = document.querySelector('select[name="rmenu_add_to_cart_width"]');

                            if (iconSelect && iconPositionRow) {
                                iconSelect.addEventListener('change', function() {
                                    if (this.value === 'none') {
                                        iconPositionRow.style.display = 'none';
                                    } else {
                                        iconPositionRow.style.display = 'block';
                                    }
                                });

                                // Trigger change event on page load to set initial visibility
                                setTimeout(() => {
                                    iconSelect.dispatchEvent(new Event('change'));
                                }, 1000);
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
                                        customwidth.dispatchEvent(new Event('change'));
                                    } else {
                                        allFields.forEach(field => field.style.display = 'none');
                                        customWidthRow.style.display = 'none';
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
                    <div class="rmenu-settings-section plugincy_card mb-4">
                        <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-visibility"></span>', 'Display Settings', 'Control how Add to Cart buttons appear on product archive pages.'); ?>

                        <div class="rmenu-settings-row">
                            <div class="rmenu-settings-field" style="display: flex; align-items: center; justify-content: space-between; gap: 20px;">
                                <label class="rmenu-settings-label">Button Display on Archive Pages
                                    <span class="tooltip">
                                        <span class="question-mark">?</span>
                                        <span class="tooltip-text">Control how Add to Cart buttons appear on product archive pages.</span>
                                    </span>
                                </label>
                                <div class="rmenu-settings-control">
                                    <select name="rmenu_add_to_cart_catalog_display" class="rmenu-select">
                                        <option value="default" <?php selected(get_option('rmenu_add_to_cart_catalog_display', 'default'), 'default'); ?>>Default (WooCommerce Setting)</option>
                                        <option value="show" <?php selected(get_option('rmenu_add_to_cart_catalog_display', 'default'), 'show'); ?>>Always Show</option>
                                        <option value="hide" <?php selected(get_option('rmenu_add_to_cart_catalog_display', 'default'), 'hide'); ?>>Always Hide</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="plugincy_row">
                        <div class="rmenu-settings-section plugincy_card plugincy_col-5" id="add_to_cart_behave">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-controls-play"></span>', 'Add To Cart Behavior', 'Control how Add to Cart buttons behave on product archive pages.'); ?>
                            <table class="form-table plugincy_table">
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Enable AJAX Add to Cart', 'Add products to cart without page reload using AJAX.'); ?>
                                    <td>
                                        <?php $onepaquc_helper->switcher('rmenu_enable_ajax_add_to_cart', 1); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Default Quantity', 'Set the default quantity when adding products to cart from archive pages.'); ?>
                                    <td>
                                        <div class="rmenu-settings-control pro-only">
                                            <input disabled type="number" name="rmenu_add_to_cart_default_qty" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_default_qty', '1')); ?>" class="small-text" min="1" max="100" step="1" />
                                            <span class="dashicons dashicons-lock plugincy_lock-icon"></span>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Quantity Selector on Archives', 'Display quantity selector on shop/archive pages before adding to cart.'); ?>
                                    <td class="rmenu-settings-control pro-only">
                                        <?php $onepaquc_helper->switcher('rmenu_show_quantity_archive', 0); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Redirect After Add to Cart', 'Choose whether to redirect customers after adding products to cart.'); ?>
                                    <td class="rmenu-settings-control">
                                        <select name="rmenu_redirect_after_add" class="rmenu-select">
                                            <option value="none" <?php selected(get_option('rmenu_redirect_after_add', 'none'), 'none'); ?>>No Redirect</option>
                                            <!-- rmenu_disable_cart_page is it's on disable below option & show cart page is disabled -->
                                            <?php
                                            $disable_cart_page = get_option('rmenu_disable_cart_page', '0');
                                            ?>
                                            <option value="cart" <?php selected(get_option('rmenu_redirect_after_add', 'none'), 'cart'); ?> <?php echo ($disable_cart_page == '1') ? 'disabled' : ''; ?>>Cart Page <?php echo ($disable_cart_page == '1') ? '(Disabled)' : ''; ?></option>
                                            <option value="checkout" <?php selected(get_option('rmenu_redirect_after_add', 'none'), 'checkout'); ?>>Checkout Page</option>
                                        </select>
                                    </td>
                                </tr>

                                <!-- <tr>
                                    <?php //$onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Add to Cart Animation', 'Choose the animation effect when products are added to cart.'); 
                                    ?>
                                    <td class="rmenu-settings-control">
                                        <select name="rmenu_add_to_cart_animation" class="rmenu-select">
                                            <option value="none" <?php //selected(get_option('rmenu_add_to_cart_animation', 'none'), 'none'); 
                                                                    ?>>None</option>
                                            <option value="slide" <?php //selected(get_option('rmenu_add_to_cart_animation', 'none'), 'slide'); 
                                                                    ?>>Slide Effect</option>
                                            <option value="fade" <?php //selected(get_option('rmenu_add_to_cart_animation', 'none'), 'fade'); 
                                                                    ?>>Fade Effect</option>
                                            <option value="fly" <?php //selected(get_option('rmenu_add_to_cart_animation', 'none'), 'fly'); 
                                                                ?>>Fly to Cart Effect</option>
                                        </select>
                                    </td>
                                </tr> -->
                            </table>
                        </div>


                        <div class="rmenu-settings-section plugincy_card plugincy_col-5" id="add_to_cart_notification">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-bell"></span>', 'Notifications', 'Customize how notifications are displayed when products are added to cart.'); ?>
                            <table class="form-table plugincy_table">
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Notification Style', 'Choose how to display notifications when products are added to cart.'); ?>
                                    <td class="rmenu-settings-control pro-only">
                                        <select disabled name="rmenu_add_to_cart_notification_style" class="rmenu-select">
                                            <option value="default" <?php selected(get_option('rmenu_add_to_cart_notification_style', 'default'), 'default'); ?>>Default WooCommerce Notices</option>
                                            <option value="popup" <?php selected(get_option('rmenu_add_to_cart_notification_style', 'default'), 'popup'); ?>>Popup Message</option>
                                            <option value="toast" <?php selected(get_option('rmenu_add_to_cart_notification_style', 'default'), 'toast'); ?>>Toast Notification</option>
                                            <option value="mini_cart" <?php selected(get_option('rmenu_add_to_cart_notification_style', 'default'), 'mini_cart'); ?>>Mini Cart Preview</option>
                                        </select>
                                        <span class="dashicons dashicons-lock plugincy_lock-icon"></span>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Success Message', 'Customize the success message shown after adding to cart. Use {product} as a placeholder for the product name.'); ?>
                                    <td class="rmenu-settings-control pro-only">
                                        <input disabled type="text" name="rmenu_add_to_cart_success_message" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_success_message', '{product} has been added to your cart.')); ?>" class="regular-text" />
                                        <span class="dashicons dashicons-lock plugincy_lock-icon"></span>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Show View Cart Link', 'Display a "View Cart" link in the notification message.'); ?>
                                    <td class="rmenu-settings-control pro-only">
                                        <?php $onepaquc_helper->switcher('rmenu_show_view_cart_link', 1); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Show Checkout Link', 'Display a "Checkout" link in the notification message.'); ?>
                                    <td class="rmenu-settings-control pro-only">
                                        <?php $onepaquc_helper->switcher('rmenu_show_checkout_link', 0); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Notification Duration', 'How long to display the notification for (in milliseconds).'); ?>
                                    <td class="rmenu-settings-control pro-only">
                                        <input disabled type="number" name="rmenu_add_to_cart_notification_duration" value="<?php echo esc_attr(get_option('rmenu_add_to_cart_notification_duration', '3000')); ?>" class="small-text" min="1000" max="10000" step="500" />
                                        <span class="rmenu-unit">ms</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const checkbox = document.querySelector('input[name="rmenu_enable_ajax_add_to_cart"]');
                            const redirect_atc = document.querySelector('select[name="rmenu_redirect_after_add"]');
                            const settingsInputs = Array.from(document.querySelectorAll('#add_to_cart_behave input, #add_to_cart_behave select, #add_to_cart_notification input, #add_to_cart_notification select')).filter(
                                el => !(el.name === "rmenu_enable_ajax_add_to_cart")
                            );

                            function updateSettings() {
                                const enabled_checkbox = document.querySelector('input[name="rmenu_enable_ajax_add_to_cart"]').checked;

                                toggleDisabledClass(!enabled_checkbox, settingsInputs);

                            }

                            function notificationVisibilityHandle() {
                                const redirectAtcVal = document.querySelector('select[name="rmenu_redirect_after_add"]').value; // Use .value

                                const notiSettings = Array.from(document.querySelectorAll('#add_to_cart_notification input, #add_to_cart_notification select'));
                                toggleDisabledClass(redirectAtcVal !== 'none', notiSettings);
                            }

                            // Initial update on page load
                            setTimeout(updateSettings, 2000);
                            setTimeout(notificationVisibilityHandle, 2000);

                            // Update when the checkbox changes
                            checkbox.addEventListener('change', updateSettings);
                            redirect_atc.addEventListener('change', notificationVisibilityHandle);
                        });
                    </script>
                </div>
                <div class="tab-content" id="advanced" style="padding: 0;">
                    <div class="plugincy_row mb-4">
                        <div class="rmenu-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-smartphone"></span>', 'Mobile Settings'); ?>

                            <table class="form-table plugincy_table">
                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Sticky Add to Cart on Mobile', 'Keep the Add to Cart button visible at the bottom of the screen on mobile devices.'); ?>
                                    <td class="rmenu-settings-control pro-only">
                                        <?php $onepaquc_helper->switcher('rmenu_sticky_add_to_cart_mobile', 0, true); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Mobile Button Text', 'Set a different button text for mobile devices. Leave empty to use the default text.'); ?>
                                    <td class="rmenu-settings-control pro-only">
                                        <input disabled type="text" name="rmenu_mobile_add_to_cart_text" value="<?php echo esc_attr(get_option('rmenu_mobile_add_to_cart_text', '')); ?>" class="regular-text" />
                                        <span class="dashicons dashicons-lock plugincy_lock-icon"></span>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Mobile Button Size', 'Choose button size optimization for mobile devices.'); ?>
                                    <td class="rmenu-settings-control pro-only">
                                        <select disabled name="rmenu_mobile_button_size" class="rmenu-select">
                                            <option value="default" <?php selected(get_option('rmenu_mobile_button_size', 'default'), 'default'); ?>>Same as Desktop</option>
                                            <option value="larger" <?php selected(get_option('rmenu_mobile_button_size', 'default'), 'larger'); ?>>Larger</option>
                                            <option value="smaller" <?php selected(get_option('rmenu_mobile_button_size', 'default'), 'smaller'); ?>>Smaller</option>
                                            <option value="full" <?php selected(get_option('rmenu_mobile_button_size', 'default'), 'full'); ?>>Full Width</option>
                                        </select>
                                        <span class="dashicons dashicons-lock plugincy_lock-icon"></span>
                                    </td>
                                </tr>

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Mobile Button Icon Only', 'Show only the icon (without text) on mobile devices to save space.'); ?>
                                    <td class="rmenu-settings-control pro-only">
                                        <?php $onepaquc_helper->switcher('rmenu_mobile_icon_only', 0, true); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="rmenu-settings-section plugincy_card plugincy_col-5">
                            <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-welcome-widgets-menus"></span>', 'Advanced Options', ''); ?>

                            <table class="form-table plugincy_table">
                                <!-- <tr>
                                    <?php //$onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Add to Cart Load Effect', 'Choose an animation effect while adding to cart is in progress.'); 
                                    ?>
                                    <td class="rmenu-settings-control pro-only">
                                        <select disabled name="rmenu_add_to_cart_loading_effect" class="rmenu-select">
                                            <option value="none" <?php //selected(get_option('rmenu_add_to_cart_loading_effect', 'spinner'), 'none'); 
                                                                    ?>>None</option>
                                            <option value="spinner" <?php //selected(get_option('rmenu_add_to_cart_loading_effect', 'spinner'), 'spinner'); 
                                                                    ?>>Spinner</option>
                                            <option value="dots" <?php //selected(get_option('rmenu_add_to_cart_loading_effect', 'spinner'), 'dots'); 
                                                                    ?>>Dots</option>
                                            <option value="pulse" <?php //selected(get_option('rmenu_add_to_cart_loading_effect', 'spinner'), 'pulse'); 
                                                                    ?>>Pulse</option>
                                        </select>
                                        <span class="dashicons dashicons-lock plugincy_lock-icon"></span>
                                    </td>
                                </tr> -->

                                <tr>
                                    <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Disable continue shopping button', 'WooCommerce shows a continue shopping button after a product is added to cart, with this option you can disable that link so user remain on checkout page'); ?>
                                    <td class="rmenu-settings-control">
                                        <?php $onepaquc_helper->switcher('rmenu_disable_btn_out_of_stock', 1); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="rmenu-settings-section plugincy_card">
                        <?php $onepaquc_helper->sec_head('h3', 'plugincy_sec_head', '<span class="dashicons dashicons-translation"></span>', 'Compatibility Settings', ''); ?>

                        <table class="form-table plugincy_table">
                            <tr>
                                <?php $onepaquc_helper->sec_head('th', 'rmenu-settings-label', '', 'Force Button CSS', 'Use !important CSS rules to override theme styling (use only if needed).'); ?>
                                <td class="rmenu-settings-control">
                                    <?php $onepaquc_helper->switcher('rmenu_force_button_css', 0); ?>
                                </td>
                            </tr>
                        </table>
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
                                        toggleDisabledClass(input !== btn_display, input);
                                    });
                                });
                            } else {
                                // enable all inputs in the tab
                                tabContents.forEach(function(content) {
                                    const inputs = content.querySelectorAll('input, select, textarea');
                                    inputs.forEach(function(input) {
                                        toggleDisabledClass(false, input);
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
                    const btn_display = document.querySelector('select[name="rmenu_add_to_cart_catalog_display"]');
                    const btn_display_hidden = btn_display && btn_display.value === 'hide';

                    const allinputFields = Array.from(document.querySelectorAll('div#tab-8 input, div#tab-8 select, div#tab-8 textarea')).filter(
                        el => !(el.name === "rmenu_enable_custom_add_to_cart" || el.name === "rmenu_add_to_cart_catalog_display")
                    );
                    toggleDisabledClass(!enableCheckout.checked || btn_display_hidden, allinputFields);
                    enableCheckout.addEventListener('change', function() {
                        toggleDisabledClass(!this.checked, allinputFields);
                    });
                });
            </script>
            <div style="text-align: right;display: flex;justify-content: flex-end;width: 98.5%;margin-top: 42px;align-items: center;padding: 20px;box-sizing: border-box;">
                <button type="submit" class="button button-primary" style="padding: 4px 20px;border: none;display: flex;gap: 5px;align-items: center;">
                    <span style="margin-bottom: -7px;"><svg fill="#fff" width="16" height="16" viewBox="0 0 0.48 0.48" xmlns="http://www.w3.org/2000/svg">
                            <path d="M.474.124.356.006A.03.03 0 0 0 .341 0H.022A.02.02 0 0 0 0 .022v.436c0 .013.01.023.022.023h.436A.022.022 0 0 0 .481.459v-.32A.02.02 0 0 0 .475.124zM.131.044h.131V.16H.131zm0 .393V.32h.218v.116zm.306 0H.393V.299A.022.022 0 0 0 .371.276H.109a.02.02 0 0 0-.022.022v.138H.044V.044h.044v.139q.001.02.021.021h.174A.022.022 0 0 0 .306.182V.044h.027l.104.104z" />
                        </svg></span>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
        <form method="post" action="" onsubmit="return confirm('Are you sure you want to reset all settings to default? This action cannot be undone.');" style="display: none;">
            <!-- nonces -->
            <?php wp_nonce_field('onepaquc_reset_settings', 'onepaquc_reset_settings_nonce'); ?>
            <input type="hidden" name="onepaquc_reset_settings" value="1">
            <?php
            submit_button('Reset Settings', 'button-primary', '', false, array_merge(array('style' => 'margin-left: 20px;background:#dc3545;color:#fff;border-color:#dc3545;')));
            ?>
        </form>

        <div>
            <div class="plugincy_nav_card mb-4" style="padding-bottom: 1px;">
                <?php $onepaquc_helper->sec_head('h2', 'plugincy_sec_head2', '<span class="dashicons dashicons-video-alt3"></span>', 'Tutorials', '', 'Learn how to use our plugin features with these step-by-step video tutorials.'); ?>
            </div>

            <div class="tutorial-container">
                <!-- Tutorial Topic 1 -->
                <div class="tutorial-topic">
                    <div class="tutorial-video">
                        <iframe
                            width="100%"
                            height="200"
                            src="https://www.youtube.com/embed/IJU9EEIT8MA?autoplay=1&mute=1&loop=1&playlist=IJU9EEIT8MA"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <h3>Buy Now Button</h3>
                    <p>Transform your WooCommerce store with instant purchase buttons that bypass the traditional cart process. Our plugin automatically adds buy now buttons on single product and archive pages, but you have complete control over placement and customization.</p>

                    <div class="tutorial-shortcode">
                        <h4>Basic Shortcode:</h4>
                        <code>[onepaquc_button]</code>
                        <p>This shortcode will display a buy now button for the current product when used on product pages.</p>
                    </div>
                </div>

                <!-- Tutorial Topic 2 -->
                <div class="tutorial-topic">
                    <div class="tutorial-video">
                        <iframe
                            width="100%"
                            height="200"
                            src="https://www.youtube.com/embed/FY_-CSI03vk?autoplay=1&mute=1&loop=1&playlist=FY_-CSI03vk"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <h3>Buy Now Button Anywhere</h3>
                    <p>You can use buy now button anywhere on your site with this shortcode.</p>
                    <div class="tutorial-shortcode">
                        <h4>Shortcode:</h4>
                        <code>[onepaquc_button product_id="123" variation_id="456" qty="2"]</code>
                    </div>
                    <div class="tutorial-tips">
                        <h4>Tips:</h4>
                        <p>Use our widget or block (Buy Now Button) for easier implementation.</p>
                    </div>
                </div>

                <!-- Tutorial Topic 3 -->
                <div class="tutorial-topic">
                    <div class="tutorial-video">
                        <iframe
                            width="100%"
                            height="200"
                            src="https://www.youtube.com/embed/nXmUvcNzDb8?autoplay=1&mute=1&loop=1&playlist=nXmUvcNzDb8"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <h3>One Page Checkout</h3>
                    <p>You can use one page checkout anywhere on your site with this shortcode.</p>
                    <div class="tutorial-shortcode">
                        <h4>Shortcode:</h4>
                        <code>[onepaquc_checkout product_id="123" variation_id="456" qty="2" clear_cart="yes" auto_add="yes"]</code>
                    </div>
                    <div class="tutorial-tips">
                        <h4>Tips:</h4>
                        <p>Use our widget or block (One-page checkout) for easier implementation.</p>
                    </div>
                </div>

                <div class="tutorial-topic">
                    <div class="tutorial-video">
                        <iframe
                            width="100%"
                            height="200"
                            src="https://www.youtube.com/embed/S96e1Nj5Kxc?autoplay=1&mute=1&loop=1&playlist=S96e1Nj5Kxc"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <h3>Floating Cart</h3>
                    <p>A modern, stylish side-drawer cart that users can access from any page for improved convenience.</p>
                    <div class="tutorial-tips">
                        <h4>Floating Cart Benefits:</h4>
                        <ul>
                            <li><strong>Reduced Abandonment:</strong> Customers can review their cart without losing their place on your site</li>
                            <li><strong>Improved UX:</strong> No page reloads or redirects needed to manage cart contents</li>
                            <li><strong>Mobile Friendly:</strong> Touch-optimized interface perfect for mobile shopping</li>
                            <li><strong>Sales Boost:</strong> Easy access to cart encourages completion of purchases</li>
                        </ul>
                    </div>
                </div>
            </div>

            <style>
                .tutorial-container {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 25px;
                    margin-top: 20px;
                }

                .tutorial-topic {
                    background: #fff;
                    border: 1px solid #e5e5e5;
                    border-radius: 8px;
                    padding: 20px;
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
                    transition: box-shadow 0.3s ease;
                }

                .tutorial-topic:hover {
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                }

                .tutorial-video {
                    margin-bottom: 15px;
                    border-radius: 5px;
                    overflow: hidden;
                    background: #000;
                }

                .tutorial-video video {
                    width: 100%;
                    display: block;
                }

                .tutorial-topic h3 {
                    margin-top: 0;
                    margin-bottom: 10px;
                    color: #23282d;
                    font-size: 18px;
                }

                .tutorial-topic p {
                    margin-bottom: 15px;
                    color: #555;
                    line-height: 1.5;
                }

                .tutorial-shortcode,
                .tutorial-tips {
                    background: #f8f9fa;
                    border-left: 4px solid #0073aa;
                    padding: 12px 15px;
                    margin-top: 15px;
                    border-radius: 0 4px 4px 0;
                }

                .tutorial-shortcode h4,
                .tutorial-tips h4 {
                    margin-top: 0;
                    margin-bottom: 8px;
                    font-size: 14px;
                    color: #23282d;
                }

                .tutorial-shortcode code {
                    display: block;
                    background: #eaeaea;
                    padding: 8px 12px;
                    border-radius: 4px;
                    font-family: monospace;
                    font-size: 14px;
                    color: #0073aa;
                    overflow-x: auto;
                }

                .tutorial-tips p {
                    margin-bottom: 0;
                    font-size: 14px;
                }
            </style>
        </div>

    </div>
<?php
    // }
}
add_action('admin_init', 'onepaquc_cart_settings');
// add_action('wp_head', 'onepaquc_cart_custom_css');

function onepaquc_handle_reset_settings()
{
    // verify nonce
    if (!isset($_POST['onepaquc_reset_settings_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['onepaquc_reset_settings_nonce'])), 'onepaquc_reset_settings')) {
        return;
    }

    if (isset($_POST['onepaquc_reset_settings']) && $_POST['onepaquc_reset_settings'] == '1') {
        global $onepaquc_string_settings_fields;
        foreach (onepaquc_rmenu_fields() as $key => $field) {
            delete_option($key);
        }
        foreach (onepaquc_onpcheckout_heading() as $key => $field) {
            delete_option($key);
        }

        foreach ($onepaquc_string_settings_fields as $field) {
            delete_option($field);
        }

        global $onepaquc_checkoutformfields, $onepaquc_productpageformfields;
        $settings = array_merge(array_keys($onepaquc_checkoutformfields), array_keys($onepaquc_productpageformfields));

        foreach ($settings as $setting) {
            delete_option($setting);
        }

        // List of settings to reset
        $settings_to_reset = [
            'onepaquc_checkout_fields',
            'rmenu_show_quick_checkout_by_types',
            'rmenu_show_quick_checkout_by_page',
            'rmenu_add_to_cart_by_types',
            'rmenu_quick_view_content_elements',
            'rmenu_show_quick_view_by_types',
            'rmenu_show_quick_view_by_page',
            'onepaquc_my_trust_badges_items',
            'checkout_form_setup',
            'onepaquc_trust_badge_custom_html',
        ];

        // Reset each setting
        foreach ($settings_to_reset as $setting) {
            delete_option($setting);
        }

        // Redirect to the same page to avoid resubmission
        $current_url = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';

        // Check if the URL already has a query string
        if (strpos($current_url, '?') !== false) {
            // Append the new action parameter
            $redirect_url = $current_url . '&action=reset_success';
        } else {
            // Add the action parameter as the first query parameter
            $redirect_url = $current_url . '?action=reset_success';
        }
        wp_redirect($redirect_url);
        exit;
    }
}
add_action('admin_init', 'onepaquc_handle_reset_settings');


function onepaquc_cart_settings()
{
    global $onepaquc_string_settings_fields;
    foreach (onepaquc_rmenu_fields() as $key => $field) {
        register_setting('onepaquc_cart_settings', $key, 'sanitize_text_field');
    }
    foreach (onepaquc_onpcheckout_heading() as $key => $field) {
        register_setting('onepaquc_cart_settings', $key, 'sanitize_text_field');
    }

    foreach ($onepaquc_string_settings_fields as $field) {
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
        'hide_coupon_toggle'          => ['selector' => '.woocommerce-form-coupon-toggle, .col-form-coupon,.woocommerce-form-coupon-toggle, .col-form-coupon', 'title' => 'Hide Top Coupon'],
        'hide_customer_details_col2'  => ['selector' => ' .woocommerce-shipping-fields, .woocommerce-shipping-fields', 'title' => 'Hide Shipping Address'],
        'hide_privacy_policy_text'    => ['selector' => '.woocommerce-privacy-policy-text,.woocommerce-privacy-policy-text', 'title' => 'Hide Privacy Policy Text'],
        'hide_payment'                 => ['selector' => 'div#payment ul,div#payment ul', 'title' => 'Hide Payment Options'],
        'hide_product'                 => ['selector' => 'table.shop_table,table.shop_table', 'title' => 'Hide Product Table']

    ];
}

function onepaquc_onpcheckout_heading()
{
    return [
        'hide_billing_details'          => ['selector' => '.woocommerce-billing-fields h3,.woocommerce-billing-fields h3', 'title' => 'Hide Billing details'],
        'hide_additional_details'          => ['selector' => ' .woocommerce-additional-fields h3,.woocommerce-additional-fields h3', 'title' => 'Hide Additional Details'],
        'hide_order_review_heading'   => ['selector' => 'h3#order_review_heading,h3#order_review_heading', 'title' => 'Hide Order Review Heading'],
    ];
}
