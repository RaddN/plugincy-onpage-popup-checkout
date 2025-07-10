<?php

/**
 * Plugin Analytics Integration Class
 * 
 * This class handles both tracking and deactivation analytics
 * for your WordPress plugin using the Product Analytics Pro API.
 */

if (!defined('ABSPATH')) {
    exit;
}

class onepaquc_cart_anaylytics
{

    private $product_id;
    private $analytics_api_url;
    private $plugin_version;
    private $plugin_name;
    private $plugin_file;

    public function __construct($product_id, $analytics_api_url, $plugin_version, $plugin_name, $plugin_file = null)
    {
        $this->product_id = $product_id;
        $this->analytics_api_url = rtrim($analytics_api_url, '/');
        $this->plugin_version = $plugin_version;
        $this->plugin_name = $plugin_name;
        $this->plugin_file = $plugin_file;

        // Hook into plugin activation/deactivation using the correct file path
        if ($this->plugin_file && get_option('rmenu_allow_analytics', 1)) {
            register_activation_hook($this->plugin_file, array($this, 'on_plugin_activation'));
            register_deactivation_hook($this->plugin_file, array($this, 'on_plugin_deactivation'));
        }
        if (get_option('rmenu_allow_analytics', 1)) {
            // Send tracking data periodically (weekly)
            add_action('wp_loaded', array($this, 'schedule_tracking'));
            add_action('send_plugin_analytics_' . $this->product_id, array($this, 'send_tracking_data'));
        }

        // Add deactivation feedback form
        add_action('admin_footer', array($this, 'add_deactivation_feedback_form'));
    }

    /**
     * Called when plugin is activated
     */
    public function on_plugin_activation()
    {
        // Send initial tracking data
        $this->send_tracking_data();

        // Schedule weekly tracking
        if (!wp_next_scheduled('send_plugin_analytics_' . $this->product_id)) {
            wp_schedule_event(time(), 'weekly', 'send_plugin_analytics_' . $this->product_id);
        }
    }

    /**
     * Called when plugin is deactivated
     */
    public function on_plugin_deactivation()
    {
        // Clear scheduled event
        wp_clear_scheduled_hook('send_plugin_analytics_' . $this->product_id);

        // Note: Deactivation reason will be sent via AJAX from the feedback form
    }

    /**
     * Schedule tracking if not already scheduled
     */
    public function schedule_tracking()
    {
        if (!wp_next_scheduled('send_plugin_analytics_' . $this->product_id)) {
            wp_schedule_event(time(), 'weekly', 'send_plugin_analytics_' . $this->product_id);
        }
    }

    /**
     * Send tracking data to analytics API
     */
    public function send_tracking_data()
    {
        $data = $this->collect_site_data();

        $response = wp_remote_post($this->analytics_api_url . '/track/' . $this->product_id, array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode($data),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return false;
        }

        return true;
    }

    /**
     * Send deactivation data to analytics API
     */
    public function send_deactivation_data($reason = '')
    {
        $data = array(
            'site_url' => home_url(),
            'reason' => $reason,
        );

        $response = wp_remote_post($this->analytics_api_url . '/deactivate/' . $this->product_id, array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode($data),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            return false;
        }

        return true;
    }

    /**
     * Collect comprehensive site data
     */
    private function collect_site_data()
    {
        global $wpdb;

        return array(
            'site_url' => home_url(),
            'multisite' => is_multisite(),
            'wp_version' => get_bloginfo('version'),
            'php_version' => phpversion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'mysql_version' => $wpdb->db_version(),
            'location' => $this->get_site_location(),
            'plugin_version' => $this->plugin_version,
            'other_plugins' => $this->get_other_plugins(),
            'active_theme' => get_option('stylesheet'),
            'using_pro' => "0",
            'license_key' => $this->get_license_key(),
        );
    }

    /**
     * Get site location based on timezone
     */
    private function get_site_location()
    {
        $timezone = get_option('timezone_string');
        if (empty($timezone)) {
            return 'Unknown';
        }

        // Extract country/region from timezone
        $parts = explode('/', $timezone);
        return isset($parts[0]) ? $parts[0] : 'Unknown';
    }

    /**
     * Get list of other active plugins
     */
    private function get_other_plugins()
    {
        $active_plugins = get_option('active_plugins', array());
        $plugins = array();

        foreach ($active_plugins as $plugin_path) {
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_path);
            if (!empty($plugin_data['Name']) && $plugin_data['Name'] !== $this->plugin_name) {
                $plugins[] = array(
                    'name' => $plugin_data['Name'],
                    'version' => $plugin_data['Version'],
                );
            }
        }

        return $plugins;
    }

    /**
     * Get license key if available
     * Override this method based on your plugin's license system
     */
    private function get_license_key()
    {
        // Example: Get license from options
        return get_option('onepaquc_license_key', '');
    }

    /**
     * Add deactivation feedback form
     */
    public function add_deactivation_feedback_form()
    {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'plugins') {
            // Get the correct plugin basename
            $plugin_file = $this->plugin_file;
            $plugin_basename = plugin_basename($plugin_file);
            $plugin_slug = dirname($plugin_basename);
?>
            <div id="plugin-deactivation-feedback" style="display:none;">
                <div class="feedback-overlay">
                    <div class="feedback-modal">
                        <div class="modal-header">
                            <div style="display: flex; gap:10px;align-items: center;">
                                <div class="plugincy_icon" style=" line-height: 1; "><svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="24" height="24" viewBox="0 0 24 24">
                                        <path d="M0 0h24v24H0z" fill="#7409B9" />
                                        <path d="m12.246 6.163 0.611 -0.001 0.643 0.002 0.643 -0.002 0.611 0.001 0.565 0.001C15.75 6.188 15.75 6.188 15.938 6.375c0.044 0.284 0.077 0.57 0.106 0.856l0.047 0.469L16.125 8.063h-1.5v2.063h2.438v-1.125h0.188q0.011 0.656 0 1.313c-0.188 0.188 -0.188 0.188 -0.762 0.199L15.938 10.5v0.75h1.688c-0.086 1.019 -0.322 1.645 -0.902 2.484l-0.391 0.577c-0.51 0.647 -0.838 1.027 -1.677 1.127l-0.607 -0.009 -0.659 -0.006c-0.226 -0.005 -0.452 -0.009 -0.685 -0.014l-0.694 -0.007Q11.161 15.392 10.313 15.375v0.563h5.625v0.563h-0.563l-0.07 0.445C15.188 17.438 15.188 17.438 14.813 17.813c-0.469 0.047 -0.469 0.047 -0.938 0 -0.375 -0.375 -0.375 -0.375 -0.398 -0.867L13.5 16.5h-1.5l-0.07 0.445C11.813 17.438 11.813 17.438 11.438 17.813c-0.469 0.047 -0.469 0.047 -0.938 0 -0.516 -0.516 -0.594 -0.982 -0.75 -1.688 0.056 -0.471 0.171 -0.883 0.306 -1.337 0.094 -0.729 -0.047 -1.382 -0.193 -2.093 -0.029 -0.148 -0.058 -0.295 -0.088 -0.447a179.063 179.063 0 0 0 -0.186 -0.932c-0.097 -0.476 -0.191 -0.953 -0.284 -1.429l-0.18 -0.906 -0.086 -0.433C8.896 7.816 8.896 7.816 8.625 7.125l-0.375 0.375c-0.529 0.037 -0.529 0.037 -1.148 0.023l-0.622 -0.01L6 7.5c-0.117 -0.352 -0.117 -0.352 -0.188 -0.75 0.375 -0.375 0.375 -0.375 0.973 -0.418l0.715 0.008 0.715 0.004C8.813 6.375 8.813 6.375 9.188 6.563a25.125 25.125 0 0 1 0.398 1.617c0.034 0.149 0.068 0.298 0.103 0.452C9.884 9.525 9.974 10.336 9.938 11.25h1.125l-0.003 -0.444q-0.005 -0.991 -0.009 -1.982l-0.005 -0.7 -0.002 -0.665 -0.003 -0.616c0.039 -0.851 0.454 -0.68 1.205 -0.681" fill="#FBF8FD" />
                                        <path d="M10.125 11.625h7.125c-0.323 0.807 -0.654 1.398 -1.137 2.109l-0.405 0.601C15.375 14.813 15.375 14.813 15.188 15c-0.334 0.016 -0.668 0.02 -1.003 0.018l-0.613 -0.002 -0.646 -0.005 -0.648 -0.003A517.875 517.875 0 0 1 10.688 15q-0.142 -0.685 -0.281 -1.371l-0.081 -0.392C10.214 12.689 10.125 12.185 10.125 11.625" fill="#7A11BC" />
                                        <path d="M11.438 6.563h4.125v1.313l-1.125 0.188 -0.188 2.25h1.313v0.938H11.438z" fill="#8E34C5" />
                                        <path d="M10.313 13.125h0.188c0.042 0.292 0.08 0.586 0.117 0.879l0.066 0.494C10.688 15 10.688 15 10.313 15.938h5.625v0.563h-0.563l-0.07 0.445C15.188 17.438 15.188 17.438 14.813 17.813c-0.469 0.047 -0.469 0.047 -0.938 0 -0.375 -0.375 -0.375 -0.375 -0.398 -0.867L13.5 16.5h-1.5l-0.07 0.445C11.813 17.438 11.813 17.438 11.438 17.813c-0.469 0.047 -0.469 0.047 -0.938 0 -0.516 -0.516 -0.594 -0.982 -0.75 -1.688 0.059 -0.492 0.059 -0.492 0.188 -0.938 0.141 -0.685 0.262 -1.372 0.375 -2.063" fill="#EDE0F6" />
                                        <path d="M17.32 6.703C17.813 6.75 17.813 6.75 18.152 6.961c0.322 0.508 0.285 0.888 0.223 1.477l-0.375 0.375h-0.563v1.313H14.625V8.063l0.715 -0.035C15.751 7.989 15.751 7.989 16.125 7.875c0.377 -0.445 0.377 -0.445 0.563 -0.938 0.188 -0.188 0.188 -0.188 0.633 -0.234" fill="#DDC0ED" />
                                        <path d="m6.785 6.332 0.715 0.008 0.715 0.004C8.813 6.375 8.813 6.375 9.188 6.563c0.152 0.544 0.282 1.077 0.398 1.629l0.103 0.457c0.177 0.812 0.328 1.579 0.248 2.414l-0.375 0.375 -0.066 -0.355q-0.148 -0.795 -0.298 -1.59l-0.104 -0.559 -0.101 -0.535 -0.092 -0.494c-0.075 -0.411 -0.075 -0.411 -0.278 -0.779l-0.375 0.375c-0.529 0.037 -0.529 0.037 -1.148 0.023l-0.622 -0.01L6 7.5c-0.117 -0.352 -0.117 -0.352 -0.188 -0.75 0.375 -0.375 0.375 -0.375 0.973 -0.418" fill="#EADBF4" />
                                        <path d="m14.625 8.25 0.809 -0.035 0.455 -0.02C16.313 8.25 16.313 8.25 16.649 8.463 16.875 8.813 16.875 8.813 16.875 9.938H14.625z" fill="#B66565" />
                                        <path d="M15.938 16.125v0.375h-0.563l-0.07 0.445C15.188 17.438 15.188 17.438 14.813 17.813c-0.469 0.047 -0.469 0.047 -0.938 0 -0.375 -0.375 -0.375 -0.375 -0.398 -0.867L13.5 16.5h-1.5l-0.188 0.75v-0.938c1.38 -0.171 2.736 -0.209 4.125 -0.188" fill="#E9D8F4" />
                                        <path d="m11.684 6.166 0.568 0.003 0.613 0.002 0.646 0.005 0.648 0.003Q14.955 6.182 15.75 6.188l-0.188 0.375H11.438v4.688h-0.188c-0.037 -0.808 -0.071 -1.617 -0.105 -2.426l-0.032 -0.7 -0.028 -0.665 -0.027 -0.616c0.005 -0.612 0.018 -0.648 0.626 -0.678" fill="#E8D7F4" />
                                        <path d="M6.188 6.375c0.447 0.045 0.447 0.045 0.961 0.164l0.517 0.116L8.063 6.75l0.188 0.75H6l-0.188 -0.75z" fill="#C79FE3" />
                                        <path d="M14.625 8.25h1.688l0.188 0.375h-1.5v0.563l0.375 0.188h-0.375v0.375l1.875 -0.188v0.375H14.625z" fill="#7A0DB7" />
                                        <path d="m17.063 12 0.563 0.188 -1.688 2.625 -0.375 -0.188c0.249 -0.679 0.552 -1.244 0.961 -1.84l0.306 -0.449z" fill="#EBDAF5" />
                                        <path d="M17.063 7.125c0.398 0.07 0.398 0.07 0.75 0.188a16.5 16.5 0 0 1 0.188 0.938c-0.188 0.188 -0.188 0.188 -0.668 0.199L16.875 8.438c-0.117 -0.445 -0.117 -0.445 -0.188 -0.938z" fill="#9E439C" />
                                        <path d="M10.313 13.125h0.188c0.296 2.063 0.296 2.063 -0.188 2.813l-0.563 0.188c0.188 -0.938 0.188 -0.938 0.281 -1.394 0.104 -0.534 0.194 -1.069 0.281 -1.606" fill="#E7D5F3" />
                                        <path d="M6.563 6.375h2.25v0.375h-0.375v0.563h-0.375v-0.563h-1.688z" fill="#FBF9FD" />
                                        <path d="M12 7.125h0.938v0.938h-0.938z" fill="#DD9E2D" />
                                        <path d="m14.063 16.5 0.938 0.188 -0.375 0.75 -0.75 -0.188z" fill="#821FC0" />
                                        <path d="m10.688 16.5 0.938 0.188 -0.375 0.75 -0.75 -0.188z" fill="#7E1DBF" />
                                        <path d="M12 12.375h0.375l-0.188 1.875h-0.375z" fill="#E4D0F1" />
                                        <path d="M13.313 12.375h0.375l-0.188 1.875h-0.375c-0.023 -1.242 -0.023 -1.242 0.188 -1.875" fill="#DFC6EF" />
                                        <path d="m16.313 9.188 0.188 0.563h-1.5v-0.375c0.462 -0.231 0.802 -0.201 1.313 -0.188" fill="#BF7258" />
                                        <path d="M9.938 15.563c0.509 0.302 1.008 0.609 1.5 0.938l-1.313 0.375z" fill="#F0E5F7" />
                                        <path d="M14.625 12.375h0.188c0.023 1.453 0.023 1.453 -0.188 1.875h-0.375q0.04 -0.416 0.082 -0.832c0.015 -0.154 0.03 -0.309 0.046 -0.468C14.438 12.563 14.438 12.563 14.625 12.375" fill="#E6D3F2" />
                                        <path d="M18 7.313h0.375c0.023 0.539 0.023 0.539 0 1.125l-0.375 0.375c-0.398 -0.07 -0.398 -0.07 -0.75 -0.188l0.563 -0.375c0.137 -0.481 0.137 -0.481 0.188 -0.938" fill="#F4EBF9" />
                                    </svg></div>
                                <h3>Quick Feedback</h3>
                            </div>
                            <button type="button" class="close-button" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>If you have a moment, please share why you are deactivating <?php echo esc_html($this->plugin_name); ?>:</p>
                            <form id="deactivation-feedback-form">
                                <div class="feedback-options">
                                    <label class="feedback-option">
                                        <input type="radio" name="reason" value="temporary">
                                        <span class="radio-button"></span>
                                        It's a temporary deactivation.
                                    </label>
                                    <label class="feedback-option">
                                        <input type="radio" name="reason" value="not-working">
                                        <span class="radio-button"></span>
                                        The plugin isn't working properly.
                                    </label>
                                    <label class="feedback-option">
                                        <input type="radio" name="reason" value="better-plugin">
                                        <span class="radio-button"></span>
                                        I found a better alternative plugin.
                                    </label>
                                    <label class="feedback-option">
                                        <input type="radio" name="reason" value="missing-feature">
                                        <span class="radio-button"></span>
                                        It's missing a specific feature.
                                    </label>
                                    <label class="feedback-option">
                                        <input type="radio" name="reason" value="other">
                                        <span class="radio-button"></span>
                                        Other
                                    </label>
                                </div>
                                <div class="other-reason-container" style="display:none;">
                                    <textarea name="other_reason" placeholder="Please tell us more..." rows="3"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Submit & Deactivate</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .feedback-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    z-index: 999999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .feedback-modal {
                    background: #ffffff;
                    border-radius: 8px;
                    max-width: 500px;
                    width: 90%;
                    max-height: 90vh;
                    overflow-y: auto;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                }

                .modal-header {
                    padding: 24px 24px 8px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }

                .modal-header h3 {
                    margin: 0;
                    font-size: 18px;
                    font-weight: 600;
                    color: #1a1a1a;
                }

                .close-button {
                    background: none;
                    border: none;
                    font-size: 20px;
                    color: #666;
                    cursor: pointer;
                    padding: 4px;
                    border-radius: 4px;
                    transition: background-color 0.2s ease;
                }

                .close-button:hover {
                    background: #f5f5f5;
                }

                .modal-body {
                    padding: 16px 24px 24px;
                }

                .modal-body p {
                    margin: 0 0 20px;
                    color: #555;
                    font-size: 14px;
                    line-height: 1.5;
                }

                .feedback-options {
                    margin-bottom: 16px;
                }

                .feedback-option {
                    display: flex;
                    align-items: center;
                    margin: 0 0 12px;
                    padding: 0;
                    cursor: pointer;
                    font-size: 14px;
                    color: #333;
                    line-height: 1.4;
                }

                .feedback-option:hover {
                    color: #0073aa;
                }

                .feedback-option input[type="radio"] {
                    position: absolute;
                    opacity: 0;
                    cursor: pointer;
                    height: 0;
                    width: 0;
                }

                .radio-button {
                    height: 16px;
                    width: 16px;
                    background: #ffffff;
                    border: 2px solid #ddd;
                    border-radius: 50%;
                    margin-right: 12px;
                    flex-shrink: 0;
                    position: relative;
                    transition: all 0.2s ease;
                }

                .feedback-option input[type="radio"]:checked+.radio-button {
                    border-color: #0073aa;
                    background: #0073aa;
                }

                .feedback-option input[type="radio"]:checked+.radio-button:after {
                    content: "";
                    position: absolute;
                    display: block;
                    left: 50%;
                    top: 50%;
                    transform: translate(-50%, -50%);
                    width: 6px;
                    height: 6px;
                    border-radius: 50%;
                    background: white;
                }

                .other-reason-container {
                    margin-top: 16px;
                    animation: slideDown 0.3s ease-out;
                }

                @keyframes slideDown {
                    from {
                        opacity: 0;
                        max-height: 0;
                        transform: translateY(-10px);
                    }

                    to {
                        opacity: 1;
                        max-height: 100px;
                        transform: translateY(0);
                    }
                }

                .other-reason-container textarea {
                    width: 100%;
                    padding: 8px 12px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    resize: vertical;
                    font-family: inherit;
                    font-size: 14px;
                    line-height: 1.4;
                    transition: border-color 0.2s ease;
                    box-sizing: border-box;
                }

                .other-reason-container textarea:focus {
                    outline: none;
                    border-color: #0073aa;
                    box-shadow: 0 0 0 1px #0073aa;
                }

                .modal-footer {
                    display: flex;
                    justify-content: flex-end;
                    margin-top: 20px;
                    padding-top: 16px;
                    border-top: 1px solid #eee;
                }

                .btn {
                    padding: 8px 16px;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 500;
                    transition: all 0.2s ease;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 120px;
                }

                .btn-primary {
                    background: #0073aa;
                    color: white;
                }

                .btn-primary:hover {
                    background: #005a87;
                }

                /* Responsive design */
                @media (max-width: 640px) {
                    .feedback-modal {
                        margin: 20px;
                        width: calc(100% - 40px);
                    }

                    .modal-header {
                        padding: 20px 20px 8px;
                    }

                    .modal-body {
                        padding: 16px 20px 20px;
                    }

                    .btn {
                        width: 100%;
                    }
                }
            </style>

            <script>
                jQuery(document).ready(function($) {
                    var pluginBasename = '<?php echo esc_js($plugin_basename); ?>';
                    var pluginSlug = '<?php echo esc_js($plugin_slug); ?>';
                    var deactivateUrl = '';

                    // Multiple selectors to catch the deactivation link
                    var selectors = [
                        'tr[data-slug="' + pluginSlug + '"] .deactivate a',
                        'tr[data-plugin="' + pluginBasename + '"] .deactivate a',
                        '.wp-list-table.plugins tr[data-slug="' + pluginSlug + '"] .row-actions .deactivate a'
                    ];

                    // Try each selector
                    selectors.forEach(function(selector) {
                        $(selector).on('click', function(e) {
                            e.preventDefault();
                            deactivateUrl = $(this).attr('href');
                            $('#plugin-deactivation-feedback').show();
                        });
                    });

                    // Fallback: Find deactivation link by searching for plugin basename in the URL
                    $('a[href*="action=deactivate"]').each(function() {
                        var href = $(this).attr('href');
                        if (href.indexOf(encodeURIComponent(pluginBasename)) > -1) {
                            $(this).on('click', function(e) {
                                e.preventDefault();
                                deactivateUrl = $(this).attr('href');
                                $('#plugin-deactivation-feedback').show();
                            });
                        }
                    });

                    // Handle feedback form submission
                    $('#deactivation-feedback-form').on('submit', function(e) {
                        e.preventDefault();

                        var reason = $('input[name="reason"]:checked').val();
                        var otherReason = $('textarea[name="other_reason"]').val();

                        if (reason === 'other' && otherReason) {
                            reason = otherReason;
                        }

                        $(this).find("button.btn.btn-primary").text("Deactivating...");

                        // Send deactivation data
                        $.ajax({
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            type: 'POST',
                            data: {
                                action: 'send_deactivation_feedback',
                                reason: reason || 'no-reason-provided',
                                nonce: '<?php echo wp_create_nonce('deactivation_feedback'); ?>'
                            },
                            complete: function() {
                                // Proceed with deactivation
                                window.location.href = deactivateUrl;
                            }
                        });
                    });

                    // Handle other reason text area
                    $('input[name="reason"]').change(function() {
                        if ($(this).val() === 'other') {
                            $('.other-reason-container').slideDown(300);
                        } else {
                            $('.other-reason-container').slideUp(300);
                        }
                    });

                    // Handle close button
                    $('.close-button').click(function() {
                        $('#plugin-deactivation-feedback').hide();
                    });

                    // Handle overlay click to close
                    $('.feedback-overlay').click(function(e) {
                        if (e.target === this) {
                            $('#plugin-deactivation-feedback').hide();
                        }
                    });

                    // Handle escape key
                    $(document).keyup(function(e) {
                        if (e.keyCode === 27) { // ESC key
                            $('#plugin-deactivation-feedback').hide();
                        }
                    });
                });
            </script>
<?php
        }
    }
}
