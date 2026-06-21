<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// admin-notice.php

// Include the function to check plugin activation
include_once(ABSPATH . 'wp-admin/includes/plugin.php');


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









if (!function_exists('onepaquc_is_new_year_deal_active')) {
    /**
     * Check if New Year deal is active.
     */
    function onepaquc_is_new_year_deal_active()
    {
        $start_timestamp  = strtotime('2025-12-07 00:00:00'); //2025-12-08 00:00:00
        $expiry_timestamp = strtotime('2026-01-09 23:59:00');

        $now = current_time('timestamp');

        return $now >= $start_timestamp && $now <= $expiry_timestamp;
    }
}

// AJAX handler for dismissing notice
add_action('wp_ajax_onepaquc_dismiss_ny_notice', 'onepaquc_dismiss_ny_notice_handler');

function onepaquc_dismiss_ny_notice_handler()
{
    check_ajax_referer('onepaquc_dismiss_ny_notice', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $hours = isset($_POST['hours']) && is_scalar($_POST['hours']) ? absint(wp_unslash($_POST['hours'])) : 3;
    $hours = in_array($hours, array(3, 12, 24), true) ? $hours : 3;
    $dismiss_until = time() + ($hours * 3600);
    
    update_user_meta(get_current_user_id(), 'onepaquc_ny_notice_dismissed_until', $dismiss_until);
    
    wp_send_json_success('Notice dismissed for ' . $hours . ' hours');
}

add_action('admin_notices', 'onepaquc_show_new_year_notice');
add_action('admin_enqueue_scripts', 'onepaquc_enqueue_new_year_notice_assets');

function onepaquc_should_show_new_year_notice($screen = null)
{
    if (!current_user_can('manage_options')) {
        return false;
    }

    if (function_exists('onepaquc_is_plugin_admin_screen') && !onepaquc_is_plugin_admin_screen($screen)) {
        return false;
    }

    if (!function_exists('onepaquc_is_new_year_deal_active') || !onepaquc_is_new_year_deal_active()) {
        return false;
    }

    $dismissed_until = get_user_meta(get_current_user_id(), 'onepaquc_ny_notice_dismissed_until', true);

    return !($dismissed_until && time() < $dismissed_until);
}

function onepaquc_enqueue_new_year_notice_assets($hook)
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!onepaquc_should_show_new_year_notice($screen)) {
        return;
    }

    wp_enqueue_style(
        'onepaquc-admin-inline',
        ONEPAQUC_PLUGIN_URL . 'assets/css/admin-inline.css',
        array(),
        function_exists('onepaquc_asset_version') ? onepaquc_asset_version('assets/css/admin-inline.css') : ONEPAQUC_VERSION
    );

    wp_enqueue_script(
        'onepaquc-admin-notice',
        ONEPAQUC_PLUGIN_URL . 'assets/js/admin-notice.js',
        array('jquery'),
        function_exists('onepaquc_asset_version') ? onepaquc_asset_version('assets/js/admin-notice.js') : ONEPAQUC_VERSION,
        true
    );

    wp_localize_script(
        'onepaquc-admin-notice',
        'onepaqucNyNotice',
        array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('onepaquc_dismiss_ny_notice'),
        )
    );
}

function onepaquc_show_new_year_notice()
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!current_user_can('manage_options') || !onepaquc_should_show_new_year_notice($screen)) {
        return;
    }

    $dismissed_until = get_user_meta(get_current_user_id(), 'onepaquc_ny_notice_dismissed_until', true);
    if ($dismissed_until && time() < $dismissed_until) {
        return;
    }

    echo '<div class="notice notice-info is-dismissible" style="padding:0;border:none;background:transparent;box-shadow:none;">';
    echo '  <div class="notice-dismiss-wrapper">';
    echo '      <button type="button" class="notice-dismiss onepaquc-ny-dismiss-trigger"><span class="screen-reader-text">Dismiss this notice.</span></button>';
    echo '      <div class="onepaquc-ny-dismiss-menu">';
    echo '          <button type="button" data-hours="3">Show again in 3 hours</button>';
    echo '          <button type="button" data-hours="12">Show again in 12 hours</button>';
    echo '          <button type="button" data-hours="24">Show again in 1 day</button>';
    echo '      </div>';
    echo '  </div>';
    echo '  <div class="onepaquc-ny-wrap">';
    echo '      <span class="dashicons dashicons-megaphone onepaquc-ny-icon"></span>';
    echo '      <div class="onepaquc-ny-content">';
    echo '          <div class="onepaquc-ny-chip">Happy New Year 2026</div>';
    echo '          <p class="onepaquc-ny-heading">Celebrate with 40% off One Page Quick Checkout for WooCommerce Pro</p>';
    echo '          <p class="onepaquc-ny-sub">Use code <code>NYP40</code> at checkout.</p>';
    echo '      </div>';
    echo '      <a class="button button-primary onepaquc-ny-cta" target="_blank" href="https://plugincy.com/one-page-quick-checkout-new-year-deal/">Claim 40% Off</a>';
    
    // Blast effects
    echo '      <span class="onepaquc-ny-blast"></span>';
    
    // Confetti particles
    echo '      <span class="onepaquc-ny-confetti" style="--x:8%;--delay:0s;--duration:5.2s;--r:18deg;"></span>';
    echo '      <span class="onepaquc-ny-confetti" style="--x:22%;--delay:0.7s;--duration:5.6s;--r:-15deg;"></span>';
    echo '      <span class="onepaquc-ny-confetti" style="--x:38%;--delay:1.2s;--duration:5.3s;--r:25deg;"></span>';
    echo '      <span class="onepaquc-ny-confetti" style="--x:54%;--delay:0.4s;--duration:5.8s;--r:-20deg;"></span>';
    echo '      <span class="onepaquc-ny-confetti" style="--x:68%;--delay:1.8s;--duration:5.1s;--r:12deg;"></span>';
    echo '      <span class="onepaquc-ny-confetti" style="--x:82%;--delay:0.9s;--duration:5.5s;--r:-18deg;"></span>';
    echo '      <span class="onepaquc-ny-confetti" style="--x:94%;--delay:1.5s;--duration:5.4s;--r:22deg;"></span>';
    
    // Sparkle effects
    echo '      <span class="onepaquc-ny-sparkle" style="--x:15%;--y:25%;--delay:0s;--duration:2.5s;"></span>';
    echo '      <span class="onepaquc-ny-sparkle" style="--x:85%;--y:30%;--delay:0.8s;--duration:2.2s;"></span>';
    echo '      <span class="onepaquc-ny-sparkle" style="--x:45%;--y:15%;--delay:1.5s;--duration:2.8s;"></span>';
    echo '      <span class="onepaquc-ny-sparkle" style="--x:70%;--y:70%;--delay:1s;--duration:2.4s;"></span>';
    echo '      <span class="onepaquc-ny-sparkle" style="--x:25%;--y:65%;--delay:1.8s;--duration:2.6s;"></span>';
    
    // Firework effects
    echo '      <span class="onepaquc-ny-firework" style="--x:20%;--y:20%;--delay:0s;--duration:3s;"></span>';
    echo '      <span class="onepaquc-ny-firework" style="--x:80%;--y:25%;--delay:1s;--duration:3.2s;"></span>';
    echo '      <span class="onepaquc-ny-firework" style="--x:50%;--y:15%;--delay:2s;--duration:2.8s;"></span>';
    
    echo '  </div>';
    echo '</div>';
    
}
