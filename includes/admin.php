<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly


// Admin Menu
add_action('admin_menu', 'plugincyopc_cart_menu');


function plugincyopc_cart_menu()
{

    add_menu_page(
        'Onpage Checkout',
        'Onpage Checkout',
        'manage_options',
        'plugincyopc_cart',
        'plugincyopc_cart_dashboard',
        'dashicons-cart' // Shopping cart icon
    );
    add_submenu_page(
        'plugincyopc_cart',
        'API Key',
        'API Key',
        'manage_options',
        'plugincyopc_cart_settings',
        'plugincyopc_cart_admin_settings'
    );
    add_submenu_page(
        'plugincyopc_cart',
        'Documentation',
        'Documentation',
        'manage_options',
        'plugincyopc_cart_documentation',
        'plugincyopc_cart_documentation'
    );
    if (get_option('plugincyopc_validity_days') !== "0") {
        // add_submenu_page('bd-affiliate-marketing', 'Manage Posts', 'Manage Posts', 'manage_options', 'bd-manage-posts', 'plugincyopc_marketing_manage_posts');
        // add_submenu_page('bd-affiliate-marketing', 'Send Notification', 'Send Notification', 'manage_options', 'bd-send-notification', 'plugincyopc_marketing_send_notification');
    }
}

// Display the form for Side Cart and PopUp settings
function plugincyopc_cart_text_change_form($textvariable)
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
function plugincyopc_cart_dashboard()
{
    global $plugincyopc_checkoutformfields,
        $plugincyopc_productpageformfields;
    ?>

    <div class="welcome-banner">
        <h1>Welcome to Onpage Checkout <span class="version-tag">v2.5</span></h1>
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
            <a href="/wp-admin/admin.php?page=plugincyopc_cart_documentation" class="button">View Documentation</a>
            <a href="https://plugincy.com/support" target="_blank" class="button button-secondary">Get Support</a>
        </div>
    </div>

    <h1 style="padding-top: 3rem;">Dashboard</h1>
    <?php
    // if (get_option('plugincyopc_validity_days') === "0" || !get_option('plugincyopc_api_key')) {
    //     echo "<p style='color:red;'>To use the plugin please active your API key first.</p>";
    // } else { 
    ?>
    <div class="tab-container">
        <div class="tabs">
            <div class="tab active" data-tab="1">Checkout Form Manage</div>
            <div class="tab" data-tab="2">One Page Checkout</div>
            <div class="tab" data-tab="3">Text Manage</div>
            <div class="tab" data-tab="4">Confirm Message</div>
            <div class="tab" data-tab="5">Features</div>

        </div>
        <form method="post" action="options.php">
            <!-- Add nonce field for security -->
            <?php wp_nonce_field('plugincyopc_cart_settings'); ?>
            <?php settings_fields('plugincyopc_cart_settings'); ?>
            <div class="tab-content active" id="tab-1">
                <h2>Checkout Form Manage</h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Remove checkout fields</th>
                        <td>
                            <!-- multiple select options -->
                            <select class="remove_checkout_fields chosen_select select2-hidden-accessible enhanced" name="plugincyopc_checkout_fields[]" id="qlwcdc_remove_checkout_fields" multiple>
                                <?php
                                global $plugincyopc_rcheckoutformfields;
                                $selected_fields = get_option('plugincyopc_checkout_fields', []) ?? [];
                                foreach ($plugincyopc_rcheckoutformfields as $key => $field) {
                                    echo '<option value="' . esc_attr($key) . '" ' . (is_array($selected_fields) && in_array($key, $selected_fields) ? 'selected' : '') . '>' . esc_html($field['title']) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <?php foreach (plugincyopc_rmenu_fields() as $key => $field) : ?>
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
                    <?php foreach (plugincyopc_onpcheckout_heading() as $key => $field) : ?>
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
                <h2>One Page Checkout in Single Product Manage</h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Form Position</th>
                        <td>
                            <input type="number" name="onpage_checkout_position" value="<?php echo esc_attr(get_option("onpage_checkout_position", '9')); ?>" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Empty Cart On page load</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="onpage_checkout_cart_empty" value="1" <?php checked(1, get_option("onpage_checkout_cart_empty"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Add to Cart On page load</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="onpage_checkout_cart_add" value="1" <?php checked(1, get_option("onpage_checkout_cart_add"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Hide Add to cart</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="onpage_checkout_hide_cart_button" value="1" <?php checked(1, get_option("onpage_checkout_hide_cart_button"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                </table>
                <hr />
                <h2>One Page Checkout widget/shortcode Manage</h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Empty Cart On page load</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="onpage_checkout_widget_cart_empty" value="1" <?php checked(1, get_option("onpage_checkout_widget_cart_empty", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Add to Cart On page load</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="onpage_checkout_widget_cart_add" value="1" <?php checked(1, get_option("onpage_checkout_widget_cart_add", "1"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="tab-content" id="tab-3">
                <div class="d-flex space-between">
                    <h2>Checkout Form Text</h2> <button id="reset-defaults" class="button button-primary" style="background:red;">Reset Default</button>
                </div>
                <?php
                plugincyopc_cart_text_change_form($plugincyopc_checkoutformfields);

                ?>
                <div class="d-flex space-between">
                    <h2>Archive & Single Product Page Text</h2>
                </div>
                <?php
                plugincyopc_cart_text_change_form($plugincyopc_productpageformfields);

                ?>
            </div>
            <div class="tab-content" id="tab-4">
                <h2>Manage Order Confirmation Message - thank you message</h2>
                <div>
                    <?php $message = get_option('rmsg_editor', '');
                    wp_editor($message, 'rmsg_id', array(
                        'media_buttons' => true,
                        'textarea_name' => 'rmsg_editor',
                        'textarea_rows' => 10
                    ));
                    ?>
                </div>

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
                        <th scope="row">Add Direct Checkout Button</th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="rmenu_add_direct_checkout_button" value="1" <?php checked(1, get_option("rmenu_add_direct_checkout_button"), true); ?> />
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
            <?php submit_button(); ?>
        </form>
        <?php $inline_script = '
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("reset-defaults").addEventListener("click", function() {
            if (confirm("Are you sure you want to reset all settings to their default values?")) {
                // Send AJAX request to reset settings
                fetch("' . esc_url(admin_url('admin-ajax.php')) . '?action=plugincyopc_reset_plugincyopc_cart_settings", {
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
        <p style="text-align: center;font-size: 15px;">To add menu cart to your page, use the shortcode <b>[plugincy_cart]</b> or the Elementor on-page menu cart widget. Block editor support will be available soon.</p>
        <p style="text-align: center;padding-bottom:20px; font-size: 15px;">[plugincy_one_page_checkout product_ids="152,153,151,142 " template="product-tabs"] <a href="/wp-admin/admin.php?page=plugincyopc_cart_documentation#multiple-products">view documentation</a></p>
    </div>
<?php
    // }
}
add_action('admin_init', 'plugincyopc_cart_settings');
// add_action('wp_head', 'plugincyopc_cart_custom_css');




function plugincyopc_cart_settings()
{
    foreach (plugincyopc_rmenu_fields() as $key => $field) {
        register_setting('plugincyopc_cart_settings', $key, 'sanitize_text_field');
    }
    foreach (plugincyopc_onpcheckout_heading() as $key => $field) {
        register_setting('plugincyopc_cart_settings', $key, 'sanitize_text_field');
    }

    $string_fields = [
        "rmsg_editor",
        "onpage_checkout_position",
        "onpage_checkout_cart_empty",
        "onpage_checkout_cart_add",
        "onpage_checkout_widget_cart_empty",
        "onpage_checkout_widget_cart_add",
        "onpage_checkout_hide_cart_button",
        "rmenu_quantity_control",
        "rmenu_remove_product",
        "rmenu_add_img_before_product",
        "rmenu_add_direct_checkout_button"
    ];

    foreach ($string_fields as $field) {
        register_setting('plugincyopc_cart_settings', $field, 'sanitize_text_field');
    }

    global $plugincyopc_checkoutformfields, $plugincyopc_productpageformfields;
    $settings = array_merge(array_keys($plugincyopc_checkoutformfields), array_keys($plugincyopc_productpageformfields));

    foreach ($settings as $setting) {
        register_setting('plugincyopc_cart_settings', $setting, 'sanitize_text_field');
    }
    // Register the setting for the checkout fields values of array
    register_setting('plugincyopc_cart_settings', "plugincyopc_checkout_fields", 'plugincyopc_sanitize_array_of_text');

}

function plugincyopc_sanitize_array_of_text($value) {
    if (!is_array($value)) {
        return [];
    }

    return array_map('sanitize_text_field', $value);
}



// Function to reset the settings to default values
function plugincyopc_reset_plugincyopc_cart_settings()
{
    global $plugincyopc_checkoutformfields,
        $plugincyopc_productpageformfields;
    // List of settings to reset
    $settings = array_merge(array_keys($plugincyopc_checkoutformfields), array_keys($plugincyopc_productpageformfields));

    // Reset each setting
    foreach ($settings as $setting) {
        update_option($setting, ''); // Reset each option to an empty string, or set a default value here
    }

    // Send a JSON response back to the client
    wp_send_json_success();
}
add_action('wp_ajax_plugincyopc_reset_plugincyopc_cart_settings', 'plugincyopc_reset_plugincyopc_cart_settings');

function plugincyopc_cart_custom_css()
{
    global $plugincyopc_rcheckoutformfields;

    // Initialize an empty string for the custom CSS
    $custom_css = '';

    // Loop through the fields to generate CSS
    foreach (plugincyopc_rmenu_fields() as $key => $field) {
        if (get_option($key)) {
            $custom_css .= "{$field['selector']} { display: none !important; }\n";
        }
    }

    foreach (plugincyopc_onpcheckout_heading() as $key => $field) {
        if (get_option($key)) {
            $custom_css .= "{$field['selector']} { display: none !important; }\n";
        }
    }

    if (get_option('plugincyopc_checkout_fields')) {
        $checkout_fields = get_option('plugincyopc_checkout_fields');
        foreach ($checkout_fields as $field) {
            if (isset($plugincyopc_rcheckoutformfields[$field])) {
                $selector = $plugincyopc_rcheckoutformfields[$field]['selector'];
                $custom_css .= "{$selector} { display: none !important; }\n";
            }
        }
    }

    // Add the inline styles
    wp_add_inline_style('rmenu-cart-style', $custom_css);
}

// Hook to enqueue the styles
add_action('wp_enqueue_scripts', 'plugincyopc_cart_custom_css', 9999999);

function plugincyopc_rmenu_fields()
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

function plugincyopc_onpcheckout_heading()
{
    return [
        'hide_billing_details'          => ['selector' => '#checkout-form .woocommerce-billing-fields h3,.one-page-checkout-container .woocommerce-billing-fields h3', 'title' => 'Hide Billing details'],
        'hide_additional_details'          => ['selector' => '.checkout-popup .woocommerce-additional-fields h3,.one-page-checkout-container .woocommerce-additional-fields h3', 'title' => 'Hide Additional Details'],
        'hide_order_review_heading'   => ['selector' => '#checkout-form h3#order_review_heading,.one-page-checkout-container h3#order_review_heading', 'title' => 'Hide Order Review Heading'],
    ];
}


// if (get_option('plugincyopc_api_key')) {
//     plugincyopc_cart_getapi(get_option('plugincyopc_api_key'));
// }


function plugincyopc_cart_getapi($Api)
{
    $api_key = $Api;
    $current_domain = home_url();
    // Validate API key before saving
    $response = wp_remote_get(
        'https://plugincy.com/api/api.php',
        [
            'headers' => [
                'API-Key' => $api_key,
                'domain'  => $current_domain,
            ],
        ]
    );

    // Check if the request was successful
    if (is_wp_error($response)) {
        $error_message = esc_js($response->get_error_message());
    } else {
        $body = json_decode(wp_remote_retrieve_body($response), true);

        // Validate the response API key matches the submitted API key
        if (isset($body['api_key']) && $body['api_key'] === $api_key) {
            // Save the API key to the database
            update_option('plugincyopc_api_key', $api_key);
            update_option('plugincyopc_package_type', sanitize_text_field($body['package_type']));
            update_option('plugincyopc_price', sanitize_text_field($body['price']));
            update_option('plugincyopc_validity_days', intval($body['validity_days']));
            return "success";
        } else {
            if (is_admin()) {
                if ($body['error'] === "Already connected with another domain.") {
                    echo '<div class="notice notice-error is-dismissible"><p> Api key already connected with another website, <a href="https://plugincy.com/api/package.php" target="_blank" >Purchase api key</a></p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Invalid Api key, Please input correct api key, you don\'t have api key, <a href="https://plugincy.com/api/package.php" target="_blank" >get api</a></p></div>';
                }
            }
            update_option('plugincyopc_api_key', "");
            update_option('plugincyopc_package_type', "");
            update_option('plugincyopc_price', "");
            update_option('plugincyopc_validity_days', "");
        }
    }
}

// API Key settings page
function plugincyopc_cart_admin_settings()
{
    // check nonece for security
    if (!isset($_POST['plugincyopc_api_key_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['plugincyopc_api_key_nonce'])), 'plugincyopc_api_key_nonce')) {
        return;
    }
    // Check if 'Save API Key' button was clicked
    // if (isset($_POST['plugincyopc_api_key_submit'])) {
    $api_key = isset($_POST['plugincyopc_api_key']) ? sanitize_text_field(wp_unslash($_POST['plugincyopc_api_key'])) : '';
    $getapi = plugincyopc_cart_getapi($api_key);
    if ($getapi === "success") {
        echo '<div class="notice notice-success is-dismissible"><p>API Key validated and updated successfully.</p></div>';
        echo '<div class="notice notice-info is-dismissible"><p>Package Type: ' . esc_html(get_option('plugincyopc_package_type')) . '</p></div>';
        echo '<p>Price: ' . esc_html(get_option('plugincyopc_price')) . '</p>';
        echo '<p>Validity Days: ' . esc_html(get_option('plugincyopc_validity_days')) . '</p></div>';
    }
    // }

    // Check if 'Unlink API Key' button was clicked
    if (isset($_POST['plugincyopc_api_key_unlink'])) {
        delete_option('plugincyopc_api_key');
        delete_option('plugincyopc_package_type');
        delete_option('plugincyopc_price');
        delete_option('plugincyopc_validity_days');
        echo '<div class="notice notice-success is-dismissible"><p>API Key unlinked successfully.</p></div>';
    }

    // Get the current API key and additional information (if any)
    $current_api_key = get_option('plugincyopc_api_key');
    $package_type = get_option('plugincyopc_package_type');
    $price = get_option('plugincyopc_price');
    $validity_days = get_option('plugincyopc_validity_days');
?>
    <div class="wrap">
        <h1>API Key Settings</h1>
        <form method="post" class="api-key-form">
            <?php wp_nonce_field('plugincyopc_api_key_nonce'); ?>
            <label for="plugincyopc_api_key">API Key:</label>
            <input type="text" name="plugincyopc_api_key" value="<?php echo esc_attr($current_api_key); ?>" <?php echo $current_api_key ? 'readonly' : ''; ?> required>
            <br>
            <div style="padding-top: 20px;">
                <input type="submit" name="plugincyopc_api_key_submit" class="button button-primary" value="Activate API Key" <?php echo $current_api_key ? 'disabled' : ''; ?>>
                <input type="submit" name="plugincyopc_api_key_unlink" class="button button-secondary" value="Deactivate API Key">
            </div>
        </form>
        <?php if ($current_api_key) : ?>
            <h2>Current Package Information</h2>
            <p><strong>Package Type:</strong> <?php echo esc_html($package_type); ?></p>
            <p><strong>Price:</strong> <?php echo esc_html($price); ?></p>
            <p><strong>Validity Days:</strong> <?php echo esc_html($validity_days); ?></p>
        <?php endif; ?>
    </div>
<?php
}
