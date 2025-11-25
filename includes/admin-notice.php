<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// admin-notice.php

// Include the function to check plugin activation
include_once(ABSPATH . 'wp-admin/includes/plugin.php');

if (!function_exists('onepaquc_is_cyber_monday_deal_active')) {
    /**
     * Check if Cyber Monday deal is still active.
     *
     * Ends automatically at 11:59 PM, December 2, 2025 (WordPress local time).
     */
    function onepaquc_is_cyber_monday_deal_active()
    {
        $expiry_timestamp = strtotime('2025-12-02 23:59:00');
        return current_time('timestamp') <= $expiry_timestamp;
    }
}

add_action('admin_notices', 'onepaquc_show_cyber_monday_notice');

function onepaquc_show_cyber_monday_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!function_exists('onepaquc_is_cyber_monday_deal_active') || !onepaquc_is_cyber_monday_deal_active()) {
        return;
    }

    echo '<div class="notice notice-info is-dismissible" style="padding:0;border:none;background:transparent;box-shadow:none;">';
    echo '  <div style="padding:18px 20px;border-radius:14px;background:linear-gradient(120deg,#5d87ff 0%,#764ba2 55%,#fcd34d 105%);color:#0b132b;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;box-shadow:0 16px 34px rgba(0,0,0,0.15);border:1px solid rgba(255,255,255,0.18);position:relative;overflow:hidden;">';
    echo '      <span class="dashicons dashicons-megaphone" style="font-size:24px;height:24px;width:24px;color:#0b132b;background:#fdfdfd;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;padding:10px;"></span>';
    echo '      <div style="flex:1;min-width:260px;position:relative;z-index:1;">';
    echo '          <div style="display:inline-flex;align-items:center;gap:8px;margin-bottom:6px;font-weight:700;text-transform:uppercase;font-size:12px;letter-spacing:0.5px;color:#0b132b;background:rgba(255,255,255,0.88);padding:4px 12px;border-radius:999px;">Cyber Monday • Ends Dec 2, 2025</div>';
    echo '          <div style="font-size:15px;line-height:1.6;color:#fff;font-weight:600;display: flex;gap:5px;">Save 40% on One Page Quick Checkout Pro with code <code style="background:#0b132b;color:#fefce8;padding:3px 8px;border-radius:8px;">CMP40</code>. Valid until 11:59 PM, December 2, 2025.</div>';
    echo '      </div>';
    echo '      <a class="button button-primary" target="_blank" href="https://plugincy.com/best-cyber-monday-plugin-deals-in-2025/" style="background:#fcd34d;border-color:#fcd34d;color:#0b132b;box-shadow:0 12px 22px rgba(0,0,0,0.18);padding:11px 20px;font-weight:800;border-radius:12px;text-transform:uppercase;letter-spacing:0.3px;">Claim 40% Off</a>';
    echo '  </div>';
    echo '</div>';
}


// Admin notice for successful installation
add_action('admin_notices', 'onepaquc_cart_notice');

function onepaquc_cart_notice()
{
    // $page_param = onepaquc_get_page_parameter_from_current_url();
    // if (!get_option('onepaquc_api_key') && $page_param !== "onepaquc_cart_settings") {
    //     echo '<div class="notice notice-success is-dismissible">
    //         <p>PlugincyOnpage Checkout plugin installed successfully! Please add your API key to activate the plugin. <a href="" target="_blank">Get API Key</a></p>
    //     </div>';
    // }
    // elseif (get_option('onepaquc_validity_days')==="0") {
    //     echo '<div class="notice notice-success is-dismissible">
    //         <p>Your Onpage Checkout API validity is expired <a href="" target="_blank">get new api key</a></p></div>';
    // }
}



function onepaquc_get_page_parameter_from_current_url() {
    // // Get the full current URL
    // $current_url = (is_ssl() ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    // // Parse the URL to get query parameters
    // $parsed_url = wp_parse_url($current_url);
    // if (isset($parsed_url['query'])) {
    //     parse_str($parsed_url['query'], $query_params);
        
    //     // Check if 'page' parameter exists and return its value
    //     if (isset($query_params['page'])) {
    //         return sanitize_text_field($query_params['page']);
    //     }
    // }
    // return null; // Return null if 'page' parameter is not found
}
