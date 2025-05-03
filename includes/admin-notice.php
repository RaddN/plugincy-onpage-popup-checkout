<?php
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// admin-notice.php

// Include the function to check plugin activation
include_once(ABSPATH . 'wp-admin/includes/plugin.php');



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
