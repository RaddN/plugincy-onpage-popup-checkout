<?php
if (!defined('ABSPATH')) {
    exit;
}

class onepaquc_License_Manager
{
    private $api_url = 'https://plugincy.com/';
    private $item_id = 4042;
    private $pro_plugin_file = 'dynamic-ajax-product-filters-for-woocommerce-pro/dynamic-ajax-product-filters-for-woocommerce-pro.php';
    private $pro_plugin_dir = 'dynamic-ajax-product-filters-for-woocommerce-pro';

    public function __construct()
    {
        add_action('admin_init', array($this, 'handle_license_actions'));
        // Move admin notices to proper hook
        add_action('admin_notices', array($this, 'show_admin_notices'));
    }

    public function handle_license_actions()
    {
        if (!isset($_POST['dapfforwcpro_license_action']) || (isset($_POST['dapfforwcpro_license_nonce']) && !wp_verify_nonce($_POST['dapfforwcpro_license_nonce'], 'dapfforwcpro_license_nonce'))) {
            return;
        }

        $action = sanitize_text_field($_POST['dapfforwcpro_license_action']);

        // Start output buffering to prevent header issues
        ob_start();

        try {
            if ($action === 'activate') {
                $license_key = sanitize_text_field($_POST['dapfforwcpro_license_key']);
                
                if (empty($license_key)) {
                    $this->set_transient_notice('Please enter a valid license key.', 'error');
                    return;
                }
                
                $this->activate_license($license_key);
            } elseif ($action === 'deactivate') {
                $this->deactivate_license();
            } elseif ($action === 'reinstall') {
                $this->reinstall_pro_plugin();
            }
        } catch (Exception $e) {
            $this->set_transient_notice('An error occurred: ' . $e->getMessage(), 'error');
        }

        // Clean output buffer
        ob_end_clean();

        // Redirect to prevent form resubmission
        wp_redirect(add_query_arg(array('updated' => 1), wp_get_referer()));
        exit;
    }

    private function set_transient_notice($message, $type = 'success')
    {
        set_transient('dapfforwcpro_admin_notice', array(
            'message' => $message,
            'type' => $type
        ), 30);
    }

    public function show_admin_notices()
    {
        $notice = get_transient('dapfforwcpro_admin_notice');
        if ($notice) {
            $class = $notice['type'] === 'error' ? 'notice-error' : 'notice-success';
            echo '<div class="notice ' . esc_attr($class) . ' is-dismissible">';
            echo '<p>' . esc_html($notice['message']) . '</p>';
            echo '</div>';
            delete_transient('dapfforwcpro_admin_notice');
        }
    }

    private function activate_license($license_key)
    {
        // Step 1: Activate the license
        $api_params = array(
            'edd_action' => 'activate_license',
            'license' => $license_key,
            'item_id' => $this->item_id,
            'url' => home_url()
        );

        $response = wp_remote_get(add_query_arg($api_params, $this->api_url), array(
            'timeout' => 30,
            'sslverify' => true,
            'user-agent' => 'DAPF/' . (defined('RMENU_VERSION') ? RMENU_VERSION : '1.0.0')
        ));

        if (is_wp_error($response)) {
            $this->set_transient_notice('Unable to connect to licensing server. Please try again later.', 'error');
            return;
        }

        $license_data = json_decode(wp_remote_retrieve_body($response));

        if ($license_data && $license_data->license == 'valid') {
            // Step 2: Get version info to get download URL
            $version_info = $this->get_version_info($license_key);
            
            if ($version_info && isset($version_info->download_link)) {
                // Step 3: Download and install the pro plugin
                $install_result = $this->download_and_install_pro_plugin($version_info->download_link);
                
                if ($install_result) {
                    update_option('dapfforwcpro_license_key', $license_key);
                    update_option('dapfforwcpro_license_status', 'valid');
                    $this->set_transient_notice('License activated and Pro plugin installed successfully!', 'success');
                } else {
                    $this->set_transient_notice('License activated but failed to install Pro plugin. Please try again.', 'error');
                }
            } else {
                $this->set_transient_notice('License activated but unable to get download link. Please contact support.', 'error');
            }
        } else {
            $error_message = $this->get_license_error_message($license_data);
            $this->set_transient_notice($error_message, 'error');
        }
    }

    private function reinstall_pro_plugin()
    {
        $license_key = get_option('dapfforwcpro_license_key', '');
        
        if (empty($license_key)) {
            $this->set_transient_notice('No valid license found. Please activate your license first.', 'error');
            return;
        }

        // Get version info to get download URL using existing license
        $version_info = $this->get_version_info($license_key);
        
        if ($version_info && isset($version_info->download_link)) {
            // Download and install the pro plugin
            $install_result = $this->download_and_install_pro_plugin($version_info->download_link);
            
            if ($install_result) {
                $this->set_transient_notice('Pro plugin reinstalled and activated successfully!', 'success');
            } else {
                $this->set_transient_notice('Failed to reinstall Pro plugin. Please try again or contact support.', 'error');
            }
        } else {
            $this->set_transient_notice('Unable to get download link. Please contact support.', 'error');
        }
    }

    private function deactivate_license()
    {
        $license_key = get_option('dapfforwcpro_license_key', '');
        
        if (empty($license_key)) {
            $this->set_transient_notice('No license key found to deactivate.', 'error');
            return;
        }

        // Step 1: Deactivate license on server
        $api_params = array(
            'edd_action' => 'deactivate_license',
            'license' => $license_key,
            'item_id' => $this->item_id,
            'url' => home_url()
        );

        $response = wp_remote_get(add_query_arg($api_params, $this->api_url), array(
            'timeout' => 30,
            'sslverify' => true,
            'user-agent' => 'DAPF/' . (defined('RMENU_VERSION') ? RMENU_VERSION : '1.0.0')
        ));

        // Step 2: Deactivate Pro plugin if it's active (before removing license data)
        if (is_plugin_active($this->pro_plugin_file)) {
            deactivate_plugins($this->pro_plugin_file, true); // Silent deactivation
        }

        // Step 3: Remove local license data
        delete_option('dapfforwcpro_license_key');
        delete_option('dapfforwcpro_license_status');

        if (is_wp_error($response)) {
            $this->set_transient_notice('License removed locally but unable to connect to server. License may still be active on server.', 'error');
        } else {
            $license_data = json_decode(wp_remote_retrieve_body($response));
            if ($license_data && $license_data->license == 'deactivated') {
                $this->set_transient_notice('License deactivated successfully!', 'success');
            } else {
                $this->set_transient_notice('License removed locally. Server response: ' . ($license_data->license ?? 'unknown'), 'error');
            }
        }
    }

    private function get_version_info($license_key)
    {
        $api_params = array(
            'edd_action' => 'get_version',
            'license' => $license_key,
            'item_id' => $this->item_id,
            'url' => home_url()
        );

        $response = wp_remote_get(add_query_arg($api_params, $this->api_url), array(
            'timeout' => 30,
            'sslverify' => true,
            'user-agent' => 'DAPF/' . (defined('RMENU_VERSION') ? RMENU_VERSION : '1.0.0')
        ));

        if (is_wp_error($response)) {
            return false;
        }

        return json_decode(wp_remote_retrieve_body($response));
    }

    private function remove_existing_plugin_directory()
    {
        $plugin_dir = WP_PLUGIN_DIR . '/' . $this->pro_plugin_dir;
        
        if (is_dir($plugin_dir)) {
            // Use WordPress filesystem API if available
            if (function_exists('WP_Filesystem')) {
                global $wp_filesystem;
                if (WP_Filesystem()) {
                    return $wp_filesystem->rmdir($plugin_dir, true);
                }
            }
        }
        
        return true;
    }

    private function download_and_install_pro_plugin($download_url)
    {
        if (!current_user_can('install_plugins')) {
            return false;
        }

        // Include necessary WordPress files
        if (!function_exists('download_url')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if (!class_exists('Plugin_Upgrader')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        }

        // Step 1: Deactivate the pro plugin if it's currently active
        if (is_plugin_active($this->pro_plugin_file)) {
            deactivate_plugins($this->pro_plugin_file, true); // Silent deactivation
        }

        // Step 2: Remove existing plugin directory if it exists
        if (!$this->remove_existing_plugin_directory()) {
        }

        // Step 3: Download the plugin zip file
        $temp_file = download_url($download_url);
        
        if (is_wp_error($temp_file)) {
            return false;
        }

        // Step 4: Install the plugin using WordPress upgrader with silent skin
        $upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
        $install_result = $upgrader->install($temp_file);

        // Clean up temp file
        if (file_exists($temp_file)) {
            unlink($temp_file);
        }

        if (is_wp_error($install_result) || !$install_result) {
            return false;
        }

        // Step 5: Get the plugin file path and activate
        $plugin_file = $upgrader->plugin_info();
        
        if ($plugin_file) {
            // Activate the pro plugin silently
            $activate_result = activate_plugin($plugin_file, '', false, true);
            
            if (is_wp_error($activate_result)) {
                return false;
            }
            
            // Deactivate the free version if it exists and is different from pro
            $free_plugin = plugin_basename(__FILE__);
            if (is_plugin_active($free_plugin) && $free_plugin !== $plugin_file) {
                deactivate_plugins($free_plugin, true);
            }
        }

        return true;
    }

    private function get_license_error_message($license_data)
    {
        if (!$license_data || !isset($license_data->license)) {
            return 'Invalid response from license server. Please try again.';
        }

        switch ($license_data->license) {
            case 'expired':
                return 'Your license key has expired. Please renew your license.';
            case 'disabled':
            case 'revoked':
                return 'Your license key has been disabled.';
            case 'missing':
                return 'Invalid license key. Please check your license key and try again.';
            case 'invalid':
            case 'site_inactive':
                return 'Your license is not active for this URL.';
            case 'item_name_mismatch':
                return 'This license key does not belong to this product.';
            case 'no_activations_left':
                return 'Your license key has reached its activation limit.';
            default:
                return 'License validation failed: ' . ($license_data->license ?? 'unknown error');
        }
    }

    private function is_pro_version_active()
    {
        return is_plugin_active($this->pro_plugin_file);
    }

    public function render_license_form()
    {
        $license_key = get_option('dapfforwcpro_license_key', '');
        $license_status = get_option('dapfforwcpro_license_status', '');
        $is_pro_active = $this->is_pro_version_active();
        ?>
        <div style="background: #fff; padding: 1rem;">
            <h2>One Page Quick Checkout For WooCommerce - License Activation</h2>
            
            <?php if ($license_status === 'valid' && $is_pro_active): ?>
                <div class="notice notice-success">
                    <p><strong>✓ Pro Version Activated!</strong> Your license is active and the Pro plugin is running.</p>
                </div>
                <form method="post" action="">
                    <?php wp_nonce_field('dapfforwcpro_license_nonce', 'dapfforwcpro_license_nonce'); ?>
                    <table class="form-table">
                        <tbody style="display: block;box-shadow:none;">
                            <tr>
                                <th scope="row">
                                    <label>Current License Key</label>
                                </th>
                                <td>
                                    <code><?php echo esc_html(substr($license_key, 0, 8) . '...' . substr($license_key, -8)); ?></code>
                                    <p class="description">Your license is currently active.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <input type="hidden" name="dapfforwcpro_license_action" value="deactivate" />
                    <input type="submit" class="button button-secondary" value="Deactivate License" onclick="return confirm('Are you sure you want to deactivate your license? This will deactivate the Pro plugin.');" />
                </form>
            <?php elseif ($license_status === 'valid' && !$is_pro_active): ?>
                <div class="notice notice-warning">
                    <p><strong>⚠ License Active but Pro Plugin Not Running!</strong> You have a valid license but the Pro plugin is not active.</p>
                </div>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label>Current License Key</label>
                            </th>
                            <td>
                                <code><?php echo esc_html(substr($license_key, 0, 8) . '...' . substr($license_key, -8)); ?></code>
                                <p class="description">License is valid but Pro plugin is not active. Click below to reinstall and activate the Pro plugin.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Reinstall Form -->
                <form method="post" action="" style="display: inline-block; padding: 10px 0; border: none;">
                    <?php wp_nonce_field('dapfforwcpro_license_nonce', 'dapfforwcpro_license_nonce'); ?>
                    <input type="hidden" name="dapfforwcpro_license_action" value="reinstall" />
                    <input type="submit" class="button button-primary" value="Reinstall & Activate Pro Plugin" />
                </form>
                
                <!-- Deactivate Form -->
                <form method="post" action="" style="display: inline-block; margin-left: 10px; padding: 0; border: none;">
                    <?php wp_nonce_field('dapfforwcpro_license_nonce', 'dapfforwcpro_license_nonce'); ?>
                    <input type="hidden" name="dapfforwcpro_license_action" value="deactivate" />
                    <input type="submit" class="button button-secondary" value="Deactivate License" onclick="return confirm('Are you sure you want to deactivate your license?');" />
                </form>
            <?php else: ?>
                <form method="post" action="">
                    <?php wp_nonce_field('dapfforwcpro_license_nonce', 'dapfforwcpro_license_nonce'); ?>
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="dapfforwcpro_license_key">License Key</label>
                                </th>
                                <td>
                                    <input type="text" id="dapfforwcpro_license_key" name="dapfforwcpro_license_key" value="<?php echo esc_attr($license_key); ?>" class="regular-text" placeholder="Enter your license key" />
                                    <p class="description">Enter your license key to download and activate the Pro version.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <input type="hidden" name="dapfforwcpro_license_action" value="activate" />
                    <input type="submit" class="button button-primary" value="Activate License & Install Pro" />
                </form>
            <?php endif; ?>
            
            <p class="description">
                <strong>Need help?</strong> <a href="https://plugincy.com/support" target="_blank">Contact Support</a> | <a href="https://plugincy.com/my-account" target="_blank">Manage Your Licenses</a>
            </p>
        </div>
        <?php
    }
}

new onepaquc_License_Manager();