<?php

// Admin Menu
add_action('admin_menu', 'rmenu_cart_menu');
add_action('admin_enqueue_scripts', 'rmenu_cart_admin_styles');

// Enqueue the admin stylesheet only for this settings page
function rmenu_cart_admin_styles($hook)
{
    if ($hook === 'toplevel_page_rmenu_cart') {
        wp_enqueue_style('rmenu_cart_admin_css', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css');
    }
}

function rmenu_cart_menu()
{

    add_menu_page('Onpage Checkout', 'Onpage Checkout', 'manage_options', 'rmenu_cart', 'rmenu_cart_dashboard');
    add_submenu_page('rmenu_cart', 'API Key', 'API Key', 'manage_options', 'rmenu_cart_settings', 'rmenu_cart_admin_settings');
    if (get_option('bd_affiliate_validity_days') !== "0") {
        // add_submenu_page('bd-affiliate-marketing', 'Manage Posts', 'Manage Posts', 'manage_options', 'bd-manage-posts', 'bd_affiliate_marketing_manage_posts');
        // add_submenu_page('bd-affiliate-marketing', 'Send Notification', 'Send Notification', 'manage_options', 'bd-send-notification', 'bd_affiliate_marketing_send_notification');
    }
}

// Display the form for Side Cart and PopUp settings
function rmenu_cart_text_change_form()
{
    $fields = [
        "your_cart" => "Your Cart",
        "btn_remove" => "Remove Button",
        "txt_subtotal" => "Subtotal",
        "txt_checkout" => "Checkout Button",
        "txt_billing_details" => "Billing Details",
        "txt_product" => "Product",
        "txt_shipping" => "Shipping",
        "txt_total" => "Total",
        "btn_place_order" => "Place Order Button"
    ];

    echo '<div class="d-flex">';

    foreach (array_chunk($fields, 4, true) as $column) {
        echo '<div>';

        foreach ($column as $name => $label) {
            $value = esc_attr(get_option($name, ''));
            echo "
                <label>
                    <p>$label</p>
                    <input type='text' name='$name' value='$value' />
                </label>
            ";
        }

        echo '</div>';
    }

    echo '</div>';
}

// Dashboard page
function rmenu_cart_dashboard()
{ ?>
    <h1>Dashboard</h1>
    <?php
    if (get_option('bd_affiliate_validity_days') === "0" || !get_option('bd_affiliate_api_key')) {
        echo "<p style='color:red;'>To use the plugin please active your API key first.</p>";
    } else { ?>
        <div class="tab-container">
            <div class="tabs">
                <div class="tab active" data-tab="1">Checkout Form Manage</div>
                <div class="tab" data-tab="2">Form Style</div>
                <div class="tab" data-tab="3">Form Text</div>
                <div class="tab" data-tab="4">Confirm Message</div>

            </div>
            <form method="post" action="options.php">
                <?php settings_fields('rmenu_cart_settings'); ?>
                <div class="tab-content active" id="tab-1">
                    <h2>Checkout Form Manage</h2>
                    <table class="form-table">
                        <?php foreach (rmenu_fields() as $key => $field) : ?>
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
                    <h2>Manage Features</h2>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">On page checkout</th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" name="onpage_checkout" value="1" <?php checked(1, get_option("onpage_checkout"), true); ?> />
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="tab-content" id="tab-3">
                    <div class="d-flex space-between">
                        <h2>Manage All Text</h2> <button id="reset-defaults" class="button button-primary" style="background:red;">Reset Default</button>
                    </div>
                    <style>
                        .space-between {
                            justify-content: space-between;
                        }

                        /* General container styling */
                        .d-flex {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 20px;
                            padding: 20px;
                            background-color: #f9f9f9;
                            /* Light background */
                            max-width: 800px;
                            margin: auto;
                            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                        }

                        /* Each column styling */
                        .d-flex>div {
                            flex: 1;
                            min-width: 200px;
                        }

                        /* Label styling */
                        .d-flex label {
                            display: block;
                            margin-bottom: 15px;
                        }

                        .d-flex p {
                            font-size: 14px;
                            font-weight: 600;
                            color: #333;
                            margin-bottom: 8px;
                        }

                        /* Input styling */
                        .d-flex input[type="text"] {
                            width: 100%;
                            padding: 10px;
                            font-size: 14px;
                            border: 1px solid #ddd;
                            border-radius: 4px;
                            transition: border-color 0.3s ease, box-shadow 0.3s ease;
                        }

                        .d-flex input[type="text"]:focus {
                            border-color: #0073e6;
                            box-shadow: 0 0 4px rgba(0, 115, 230, 0.2);
                            outline: none;
                        }

                        /* Mobile responsiveness */
                        @media (max-width: 600px) {
                            .d-flex {
                                flex-direction: column;
                            }
                        }
                    </style>
                    <?php
                    rmenu_cart_text_change_form();

                    ?>
                </div>
                <div class="tab-content" id="tab-4">
                        <h2>Manage Order Confirmation Message - thank you message</h2>
                    <div>
                        <?php $message = get_option('rmsg_editor', '');
                        wp_editor($message,'rmsg_id',array(
                            'media_buttons' => true,
                            'textarea_name' => 'rmsg_editor',
                            'textarea_rows' => 10
                        ));
?>
                    </div>
                
                </div>
                <?php submit_button(); ?>
            </form>
            <script type="text/javascript">
                document.getElementById('reset-defaults').addEventListener('click', function() {
                    if (confirm('Are you sure you want to reset all settings to their default values?')) {
                        // Send AJAX request to reset settings
                        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>?action=reset_rmenu_cart_settings', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                credentials: 'same-origin'
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('Settings have been reset to default.');
                                    location.reload(); // Reload to show the reset values
                                } else {
                                    alert('An error occurred while resetting settings.');
                                }
                            });
                    }
                });
            </script>
            <h4 style="text-align: center;padding-bottom:20px; color:red; font-size: 15px;">To add menu cart to your page, use the shortcode <b>[rmenu_cart]</b> or the Elementor on-page menu cart widget. Block editor support will be available soon.</h4>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const tabs = document.querySelectorAll(".tab");
                const contents = document.querySelectorAll(".tab-content");

                tabs.forEach(tab => {
                    tab.addEventListener("click", () => {
                        // Remove active class from all tabs and tab contents
                        tabs.forEach(t => t.classList.remove("active"));
                        contents.forEach(c => c.classList.remove("active"));

                        // Add active class to clicked tab and its corresponding content
                        tab.classList.add("active");
                        const content = document.querySelector(`#tab-${tab.dataset.tab}`);
                        content.classList.add("active");
                    });
                });
            });
        </script>
    <?php
    }
}
add_action('admin_init', 'rmenu_cart_settings');
add_action('wp_head', 'rmenu_cart_custom_css');




function rmenu_cart_settings()
{
    foreach (rmenu_fields() as $key => $field) {
        register_setting('rmenu_cart_settings', $key);
    }
    register_setting('rmenu_cart_settings', "rmsg_editor");
    register_setting('rmenu_cart_settings', "onpage_checkout");
    // form text settings
    $settings = [
        "your_cart",
        "btn_remove",
        "txt_subtotal",
        "txt_checkout",
        "txt_billing_details",
        "txt_product",
        "txt_shipping",
        "txt_total",
        "btn_place_order"
    ];

    foreach ($settings as $setting) {
        register_setting('rmenu_cart_settings', $setting);
    }
}

// Function to reset the settings to default values
function reset_rmenu_cart_settings()
{
    // List of settings to reset
    $settings = [
        "your_cart",
        "btn_remove",
        "txt_subtotal",
        "txt_checkout",
        "txt_billing_details",
        "txt_product",
        "txt_shipping",
        "txt_total",
        "btn_place_order"
    ];

    // Reset each setting
    foreach ($settings as $setting) {
        update_option($setting, ''); // Reset each option to an empty string, or set a default value here
    }

    // Send a JSON response back to the client
    wp_send_json_success();
}
add_action('wp_ajax_reset_rmenu_cart_settings', 'reset_rmenu_cart_settings');

function rmenu_cart_custom_css()
{
    echo '<style>';
    foreach (rmenu_fields() as $key => $field) {
        if (get_option($key)) {
            echo esc_attr("{$field['selector']} { display: none !important; }");
        }
    }
    echo '</style>';
}

function rmenu_fields()
{
    return [
        'hide_country_field'          => ['selector' => '#checkout-form p#billing_country_field', 'title' => 'Hide Country Field'],
        'hide_coupon_toggle'          => ['selector' => '#checkout-form .woocommerce-form-coupon-toggle, #checkout-form .col-form-coupon', 'title' => 'Hide Top Coupon'],
        'hide_order_review_heading'   => ['selector' => '#checkout-form h3#order_review_heading', 'title' => 'Hide Order Review Heading'],
        'hide_customer_details_col2'  => ['selector' => '#checkout-form div#customer_details .col-2', 'title' => 'Hide Shipping Address'],
        'hide_billing_company_field'  => ['selector' => '#checkout-form p#billing_company_field', 'title' => 'Hide Billing Company Field'],
        'hide_billing_address_2_field' => ['selector' => '#checkout-form p#billing_address_2_field,.checkout-shipping', 'title' => 'Hide Billing Address 2 Field'],
        'hide_billing_postcode_field' => ['selector' => '#checkout-form p#billing_postcode_field', 'title' => 'Hide Billing Postcode Field'],
        'hide_order_review_paragraph' => ['selector' => '#checkout-form div#order_review > p:first-child', 'title' => 'Hide Order Review Paragraph'],
        'hide_notices_wrapper'        => ['selector' => '.woocommerce-notices-wrapper', 'title' => 'Hide Notices Wrapper'],
        'hide_privacy_policy_text'    => ['selector' => '.woocommerce-privacy-policy-text', 'title' => 'Hide Privacy Policy Text'],
        'hide_coupon'                 => ['selector' => 'div#order_review>p', 'title' => 'Hide Buttom Coupon'],
        'hide_payment'                 => ['selector' => 'div#payment ul', 'title' => 'Hide Payment Options'],
        'hide_product'                 => ['selector' => 'table.shop_table', 'title' => 'Hide Product Table']

    ];
}


if (get_option('bd_affiliate_api_key')) {
    rmenu_cart_getapi(get_option('bd_affiliate_api_key'));
}


function rmenu_cart_getapi($Api)
{
    $api_key = $Api;
    $current_domain = home_url();
    // Validate API key before saving
    $response = wp_remote_get(
        'https://app.blenddoit.top/api/api.php',
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
        echo '<script>console.log("Error connecting to API: ' . $error_message . '");</script>';
    } else {
        $body = json_decode(wp_remote_retrieve_body($response), true);

        // Validate the response API key matches the submitted API key
        if (isset($body['api_key']) && $body['api_key'] === $api_key) {
            // Save the API key to the database
            update_option('bd_affiliate_api_key', $api_key);
            update_option('bd_affiliate_package_type', sanitize_text_field($body['package_type']));
            update_option('bd_affiliate_price', sanitize_text_field($body['price']));
            update_option('bd_affiliate_validity_days', intval($body['validity_days']));
            return "success";
        } else {
            if (is_admin()) {
                if ($body['error'] === "Already connected with another domain.") {
                    echo '<div class="notice notice-error is-dismissible"><p> Api key already connected with another website, <a href="https://app.blenddoit.top/api/package.php" target="_blank" >Purchase api key</a></p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Invalid Api key, Please input correct api key, you don\'t have api key, <a href="https://app.blenddoit.top/api/package.php" target="_blank" >get api</a></p></div>';
                }
            }
            update_option('bd_affiliate_api_key', "");
            update_option('bd_affiliate_package_type', "");
            update_option('bd_affiliate_price', "");
            update_option('bd_affiliate_validity_days', "");
        }
    }
}

// API Key settings page
function rmenu_cart_admin_settings()
{
    // Check if 'Save API Key' button was clicked
    if (isset($_POST['bd_affiliate_api_key_submit'])) {
        $api_key = sanitize_text_field($_POST['bd_affiliate_api_key']);
        $getapi = rmenu_cart_getapi($api_key);
        if ($getapi === "success") {
            echo '<div class="notice notice-success is-dismissible"><p>API Key validated and updated successfully.</p></div>';
            echo '<div class="notice notice-info is-dismissible"><p>Package Type: ' . esc_html(get_option('bd_affiliate_package_type')) . '</p></div>';
            echo '<p>Price: ' . esc_html(get_option('bd_affiliate_price')) . '</p>';
            echo '<p>Validity Days: ' . esc_html(get_option('bd_affiliate_validity_days')) . '</p></div>';
        }
    }

    // Check if 'Unlink API Key' button was clicked
    if (isset($_POST['bd_affiliate_api_key_unlink'])) {
        delete_option('bd_affiliate_api_key');
        delete_option('bd_affiliate_package_type');
        delete_option('bd_affiliate_price');
        delete_option('bd_affiliate_validity_days');
        echo '<div class="notice notice-success is-dismissible"><p>API Key unlinked successfully.</p></div>';
    }

    // Get the current API key and additional information (if any)
    $current_api_key = get_option('bd_affiliate_api_key');
    $package_type = get_option('bd_affiliate_package_type');
    $price = get_option('bd_affiliate_price');
    $validity_days = get_option('bd_affiliate_validity_days');
    ?>
    <div class="wrap">
        <h1>API Key Settings</h1>
        <form method="post" class="api-key-form">
            <label for="bd_affiliate_api_key">API Key:</label>
            <input type="text" name="bd_affiliate_api_key" value="<?php echo esc_attr($current_api_key); ?>" <?php echo $current_api_key ? 'readonly' : ''; ?> required>
            <br>
            <div style="padding-top: 20px;">
                <input type="submit" name="bd_affiliate_api_key_submit" class="button button-primary" value="Activate API Key" <?php echo $current_api_key ? 'disabled' : ''; ?>>
                <input type="submit" name="bd_affiliate_api_key_unlink" class="button button-secondary" value="Deactivate API Key">
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
