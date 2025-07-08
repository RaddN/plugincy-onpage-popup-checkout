<?php

/**
 * Plugin Analytics Integration Class
 * 
 * This class handles both tracking and deactivation analytics
 * for your WordPress plugin using the Product Analytics Pro API.
 */

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
            error_log('Analytics tracking failed: ' . $response->get_error_message());
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            error_log('Analytics tracking failed with status: ' . $response_code);
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

        error_log(json_encode($data));

        $response = wp_remote_post($this->analytics_api_url . '/deactivate/' . $this->product_id, array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode($data),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            error_log('Deactivation tracking failed: ' . $response->get_error_message());
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
            'using_pro' => $this->is_pro_version(),
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
     * Check if this is a pro version
     * Override this method based on your plugin's pro detection logic
     */
    private function is_pro_version()
    {
        // Example: Check for pro features, license, or specific constants
        return defined('YOUR_PLUGIN_PRO') && YOUR_PLUGIN_PRO;
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
                            <h3>We'd Love Your Feedback</h3>
                            <button type="button" class="close-button" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Help us improve <?php echo esc_html($this->plugin_name); ?>. What's the main reason for deactivating?</p>
                            <form id="deactivation-feedback-form">
                                <div class="feedback-options">
                                    <label class="feedback-option">
                                        <input type="radio" name="reason" value="temporary">
                                        <span class="checkmark"></span>
                                        <div class="option-content">
                                            <strong>Temporary deactivation</strong>
                                            <small>I'll be back soon</small>
                                        </div>
                                    </label>
                                    <label class="feedback-option">
                                        <input type="radio" name="reason" value="not-working">
                                        <span class="checkmark"></span>
                                        <div class="option-content">
                                            <strong>Plugin not working</strong>
                                            <small>Experiencing technical issues</small>
                                        </div>
                                    </label>
                                    <label class="feedback-option">
                                        <input type="radio" name="reason" value="better-plugin">
                                        <span class="checkmark"></span>
                                        <div class="option-content">
                                            <strong>Found a better plugin</strong>
                                            <small>Switching to an alternative</small>
                                        </div>
                                    </label>
                                    <label class="feedback-option">
                                        <input type="radio" name="reason" value="no-longer-needed">
                                        <span class="checkmark"></span>
                                        <div class="option-content">
                                            <strong>No longer needed</strong>
                                            <small>Don't need this functionality anymore</small>
                                        </div>
                                    </label>
                                    <label class="feedback-option">
                                        <input type="radio" name="reason" value="too-complicated">
                                        <span class="checkmark"></span>
                                        <div class="option-content">
                                            <strong>Too complicated</strong>
                                            <small>Hard to use or configure</small>
                                        </div>
                                    </label>
                                    <label class="feedback-option">
                                        <input type="radio" name="reason" value="other">
                                        <span class="checkmark"></span>
                                        <div class="option-content">
                                            <strong>Other reason</strong>
                                            <small>Please specify below</small>
                                        </div>
                                    </label>
                                </div>
                                <div class="other-reason-container" style="display:none;">
                                    <textarea name="other_reason" placeholder="Please tell us more about your reason..." rows="3"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary feedback-cancel">Cancel</button>
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
                    background: rgba(0, 0, 0, 0.6);
                    backdrop-filter: blur(2px);
                    z-index: 999999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    animation: fadeIn 0.2s ease-out;
                }

                @keyframes fadeIn {
                    from {
                        opacity: 0;
                    }

                    to {
                        opacity: 1;
                    }
                }

                @keyframes slideIn {
                    from {
                        opacity: 0;
                        transform: translateY(-20px) scale(0.95);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0) scale(1);
                    }
                }

                .feedback-modal {
                    background: #ffffff;
                    border-radius: 12px;
                    max-width: 480px;
                    width: 90%;
                    max-height: 90vh;
                    overflow-y: auto;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
                    animation: slideIn 0.3s ease-out;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                }

                .modal-header {
                    padding: 24px 24px 16px;
                    border-bottom: 1px solid #e5e7eb;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }

                .modal-header h3 {
                    margin: 0;
                    font-size: 20px;
                    font-weight: 600;
                    color: #1f2937;
                    line-height: 1.2;
                }

                .close-button {
                    background: none;
                    border: none;
                    font-size: 24px;
                    color: #6b7280;
                    cursor: pointer;
                    padding: 4px;
                    border-radius: 6px;
                    transition: all 0.2s ease;
                    line-height: 1;
                }

                .close-button:hover {
                    background: #f3f4f6;
                    color: #374151;
                }

                .modal-body {
                    padding: 20px 24px 24px;
                }

                .modal-body p {
                    margin: 0 0 20px;
                    color: #6b7280;
                    font-size: 15px;
                    line-height: 1.5;
                }

                .feedback-options {
                    margin-bottom: 20px;
                }

                .feedback-option {
                    display: flex;
                    align-items: flex-start;
                    margin: 0 0 12px;
                    padding: 16px;
                    border: 1px solid #e5e7eb;
                    border-radius: 8px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    position: relative;
                    background: #ffffff;
                }

                .feedback-option:hover {
                    border-color: #d1d5db;
                    background: #f9fafb;
                }

                .feedback-option input[type="radio"] {
                    position: absolute;
                    opacity: 0;
                    cursor: pointer;
                    height: 0;
                    width: 0;
                }

                .feedback-option input[type="radio"]:checked+.checkmark {
                    background: #3b82f6;
                    border-color: #3b82f6;
                }

                .feedback-option input[type="radio"]:checked+.checkmark:after {
                    display: block;
                }

                .feedback-option input[type="radio"]:checked~.option-content {
                    color: #1f2937;
                }

                .feedback-option:has(input[type="radio"]:checked) {
                    border-color: #3b82f6;
                    background: #eff6ff;
                }

                .checkmark {
                    height: 20px;
                    width: 20px;
                    background: #ffffff;
                    border: 2px solid #d1d5db;
                    border-radius: 50%;
                    margin-right: 12px;
                    margin-top: 2px;
                    flex-shrink: 0;
                    position: relative;
                    transition: all 0.2s ease;
                }

                .checkmark:after {
                    content: "";
                    position: absolute;
                    display: none;
                    left: 50%;
                    top: 50%;
                    transform: translate(-50%, -50%);
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                    background: white;
                }

                .option-content {
                    flex: 1;
                    color: #6b7280;
                    transition: color 0.2s ease;
                }

                .option-content strong {
                    display: block;
                    font-weight: 600;
                    color: #374151;
                    margin-bottom: 2px;
                    font-size: 15px;
                }

                .option-content small {
                    font-size: 13px;
                    color: #9ca3af;
                    line-height: 1.3;
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
                    padding: 12px 16px;
                    border: 2px solid #e5e7eb;
                    border-radius: 8px;
                    resize: vertical;
                    font-family: inherit;
                    font-size: 14px;
                    line-height: 1.4;
                    transition: border-color 0.2s ease;
                    box-sizing: border-box;
                }

                .other-reason-container textarea:focus {
                    outline: none;
                    border-color: #3b82f6;
                    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
                }

                .modal-footer {
                    display: flex;
                    gap: 12px;
                    justify-content: flex-end;
                    margin-top: 24px;
                    padding-top: 20px;
                    border-top: 1px solid #e5e7eb;
                }

                .btn {
                    padding: 10px 20px;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 500;
                    transition: all 0.2s ease;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 100px;
                }

                .btn-primary {
                    background: #3b82f6;
                    color: white;
                }

                .btn-primary:hover {
                    background: #2563eb;
                    transform: translateY(-1px);
                    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
                }

                .btn-secondary {
                    background: #ffffff;
                    color: #6b7280;
                    border: 1px solid #d1d5db;
                }

                .btn-secondary:hover {
                    background: #f9fafb;
                    color: #374151;
                    border-color: #9ca3af;
                }

                /* Responsive design */
                @media (max-width: 640px) {
                    .feedback-modal {
                        margin: 20px;
                        width: calc(100% - 40px);
                    }

                    .modal-header {
                        padding: 20px 20px 16px;
                    }

                    .modal-body {
                        padding: 16px 20px 20px;
                    }

                    .modal-footer {
                        flex-direction: column-reverse;
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

                    // Debug logging
                    console.log('Plugin basename:', pluginBasename);
                    console.log('Plugin slug:', pluginSlug);

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
                            console.log('Deactivation intercepted via selector:', selector);
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
                                console.log('Deactivation intercepted via fallback');
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

                    // Handle cancel
                    $('.feedback-cancel').click(function() {
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
