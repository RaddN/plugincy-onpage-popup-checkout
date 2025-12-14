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
    
    $hours = isset($_POST['hours']) ? intval($_POST['hours']) : 3;
    $dismiss_until = time() + ($hours * 3600);
    
    update_user_meta(get_current_user_id(), 'onepaquc_ny_notice_dismissed_until', $dismiss_until);
    
    wp_send_json_success('Notice dismissed for ' . $hours . ' hours');
}

add_action('admin_notices', 'onepaquc_show_new_year_notice');

function onepaquc_show_new_year_notice()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!function_exists('onepaquc_is_new_year_deal_active') || !onepaquc_is_new_year_deal_active()) {
        return;
    }

    // Check if notice is dismissed and if dismissal period has expired
    $dismissed_until = get_user_meta(get_current_user_id(), 'onepaquc_ny_notice_dismissed_until', true);
    if ($dismissed_until && time() < $dismissed_until) {
        return;
    }

    echo '<style>
.onepaquc-ny-wrap {
    padding:24px;
    border-radius:16px;
    background:linear-gradient(135deg,#0f172a 0%,#1e293b 25%,#312e81 50%,#1e1b4b 75%,#0f172a 100%);
    background-size:300% 300%;
    color:#f8fafc;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
    flex-wrap:wrap;
    box-shadow:0 20px 60px rgba(0,0,0,0.4),0 0 0 1px rgba(255,255,255,0.1) inset,0 0 80px rgba(147,51,234,0.15);
    border:1px solid rgba(168,85,247,0.2);
    position:relative;
    overflow:hidden;
    animation:onepaquc_ny_gradient 8s ease infinite;
}

.onepaquc-ny-wrap::before {
    content:"";
    position:absolute;
    top:0;
    left:-100%;
    width:100%;
    height:100%;
    background:linear-gradient(90deg,transparent,rgba(255,255,255,0.1),transparent);
    animation:onepaquc_ny_shine 3s ease-in-out infinite;
}

.onepaquc-ny-icon {
    font-size:28px;
    height:28px;
    width:28px;
    color:#1e1b4b;
    background:linear-gradient(135deg,#fbbf24 0%,#f59e0b 50%,#fbbf24 100%);
    background-size:200% 200%;
    border-radius:50%;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:14px;
    box-shadow:0 10px 30px rgba(245,158,11,0.4),0 0 30px rgba(251,191,36,0.3);
    z-index:1;
    animation:onepaquc_ny_icon_pulse 2s ease-in-out infinite,onepaquc_ny_icon_glow 3s ease infinite;
}

.onepaquc-ny-chip {
    display:inline-flex;
    align-items:center;
    gap:8px;
    margin-bottom:10px;
    font-weight:900;
    text-transform:uppercase;
    font-size:11px;
    letter-spacing:1.2px;
    color:#1e1b4b;
    background:linear-gradient(135deg,#fef3c7 0%,#fde68a 50%,#fef3c7 100%);
    background-size:200% 200%;
    padding:7px 16px;
    border-radius:999px;
    box-shadow:0 8px 20px rgba(245,158,11,0.3),0 0 20px rgba(251,191,36,0.2);
    animation:onepaquc_ny_chip_shine 4s ease infinite;
}

.onepaquc-ny-chip::before {
    content:"🎉";
    animation:onepaquc_ny_emoji_spin 3s linear infinite;
}

.onepaquc-ny-heading {
    font-size:18px;
    line-height:1.5;
    color:#ffffff;
    font-weight:900;
    margin:0 0 6px;
    display:flex;
    align-items:center;
    gap:10px;
    text-shadow:0 2px 10px rgba(0,0,0,0.3);
    background:linear-gradient(90deg,#fff 0%,#fbbf24 50%,#fff 100%);
    background-size:200% auto;
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    background-clip:text;
    animation:onepaquc_ny_text_shine 3s linear infinite;
}

.onepaquc-ny-sub {
    font-size:14px;
    line-height:1.6;
    color:#e2e8f0;
    font-weight:600;
    margin:0;
}

.onepaquc-ny-sub code {
    background:linear-gradient(135deg,#fef08a 0%,#fde047 100%);
    color:#1e1b4b;
    padding:4px 10px;
    border-radius:8px;
    font-weight:900;
    font-size:13px;
    box-shadow:0 4px 12px rgba(254,240,138,0.4);
    border:1px solid rgba(253,224,71,0.5);
    animation:onepaquc_ny_code_pulse 2s ease-in-out infinite;
}

.onepaquc-ny-cta.button.button-primary {
    background:linear-gradient(135deg,#b45309 0%,#92400e 25%,#78350f 50%,#92400e 75%,#b45309 100%) !important;
    background-size:400% 100% !important;
    border:2px solid #d97706 !important;
    color:#ffffff !important;
    box-shadow:0 0 0 3px rgba(217,119,6,0.3),0 20px 50px rgba(146,64,14,0.6),0 0 60px rgba(180,83,9,0.5),inset 0 1px 0 rgba(251,191,36,0.2) !important;
    padding:18px 40px !important;
    font-weight:900 !important;
    border-radius:16px !important;
    text-transform:uppercase !important;
    letter-spacing:1px !important;
    font-size:16px !important;
    cursor:pointer !important;
    transition:all 0.4s cubic-bezier(0.68,-0.55,0.265,1.55) !important;
    position:relative !important;
    overflow:hidden !important;
    animation:onepaquc_ny_button_glow 2s ease-in-out infinite,onepaquc_ny_button_gradient 4s linear infinite !important;
    text-decoration:none !important;
    display:inline-flex !important;
    align-items:center !important;
    gap:12px !important;
    white-space:nowrap !important;
    text-shadow:0 2px 8px rgba(0,0,0,0.4) !important;
    height:auto !important;
    line-height:1 !important;
}

.onepaquc-ny-cta.button.button-primary::before {
    content:"" !important;
    position:absolute !important;
    top:-50% !important;
    left:-50% !important;
    width:200% !important;
    height:200% !important;
    background:linear-gradient(45deg,transparent 30%,rgba(251,191,36,0.4) 50%,transparent 70%) !important;
    transform:rotate(45deg) !important;
    animation:onepaquc_ny_button_shine 3s ease-in-out infinite !important;
}

.onepaquc-ny-cta.button.button-primary::after {
    content:"→" !important;
    font-size:24px !important;
    font-weight:900 !important;
    transition:transform 0.4s cubic-bezier(0.68,-0.55,0.265,1.55) !important;
}

.onepaquc-ny-cta.button.button-primary:hover {
    transform:translateY(-5px) scale(1.1) !important;
    box-shadow:0 0 0 4px rgba(217,119,6,0.5),0 25px 60px rgba(146,64,14,0.8),0 0 80px rgba(180,83,9,0.7),inset 0 1px 0 rgba(251,191,36,0.3) !important;
    animation-play-state:paused !important;
    color:#ffffff !important;
    border-color:#f59e0b !important;
}

.onepaquc-ny-cta.button.button-primary:hover::after {
    transform:translateX(8px) scale(1.2) !important;
}

.onepaquc-ny-cta.button.button-primary:active {
    transform:translateY(-2px) scale(1.08) !important;
}

.onepaquc-ny-cta.button.button-primary:focus {
    box-shadow:0 0 0 4px rgba(217,119,6,0.5),0 25px 60px rgba(146,64,14,0.8),0 0 80px rgba(180,83,9,0.7),inset 0 1px 0 rgba(251,191,36,0.3) !important;
    color:#ffffff !important;
}

.onepaquc-ny-blast {
    position:absolute;
    width:250px;
    height:250px;
    border:3px solid rgba(168,85,247,0.5);
    border-radius:50%;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%) scale(0.3);
    opacity:0;
    animation:onepaquc_ny_blast 3s ease-out infinite;
    pointer-events:none;
}

.onepaquc-ny-confetti {
    position:absolute;
    width:10px;
    height:20px;
    border-radius:5px;
    background:linear-gradient(180deg,#fde68a 0%,#f59e0b 50%,#fcd34d 100%);
    top:-40px;
    left:var(--x,50%);
    opacity:0;
    transform:rotate(var(--r,0deg));
    animation:onepaquc_ny_confetti var(--duration,5s) ease-in infinite;
    animation-delay:var(--delay,0s);
}

.onepaquc-ny-sparkle {
    position:absolute;
    width:6px;
    height:6px;
    background:#fbbf24;
    border-radius:50%;
    top:var(--y,50%);
    left:var(--x,50%);
    box-shadow:0 0 10px #fbbf24;
    opacity:0;
    animation:onepaquc_ny_sparkle var(--duration,2s) ease-in-out infinite;
    animation-delay:var(--delay,0s);
}

.onepaquc-ny-sparkle::before,
.onepaquc-ny-sparkle::after {
    content:"";
    position:absolute;
    width:2px;
    height:12px;
    background:#fbbf24;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    box-shadow:0 0 8px #fbbf24;
}

.onepaquc-ny-sparkle::after {
    transform:translate(-50%,-50%) rotate(90deg);
}

.onepaquc-ny-firework {
    position:absolute;
    width:4px;
    height:4px;
    border-radius:50%;
    top:var(--y,20%);
    left:var(--x,20%);
    animation:onepaquc_ny_firework var(--duration,3s) ease-out infinite;
    animation-delay:var(--delay,0s);
}

.onepaquc-ny-firework::before {
    content:"";
    position:absolute;
    width:100%;
    height:100%;
    border-radius:50%;
    background:radial-gradient(circle,rgba(251,191,36,1) 0%,rgba(168,85,247,0.8) 40%,transparent 70%);
    box-shadow:0 0 20px rgba(251,191,36,0.8);
}

.onepaquc-ny-content {
    flex:1;
    min-width:280px;
    position:relative;
    z-index:1;
    animation:onepaquc_ny_slide 0.8s ease both;
}

.onepaquc-ny-cta {
    z-index:1;
    animation:onepaquc_ny_slide 1s ease both;
}

@keyframes onepaquc_ny_gradient {
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}

@keyframes onepaquc_ny_shine {
    0%{left:-100%;}
    50%,100%{left:100%;}
}

@keyframes onepaquc_ny_icon_pulse {
    0%,100%{transform:scale(1);}
    50%{transform:scale(1.1);}
}

@keyframes onepaquc_ny_icon_glow {
    0%,100%{box-shadow:0 10px 30px rgba(245,158,11,0.4),0 0 30px rgba(251,191,36,0.3);}
    50%{box-shadow:0 10px 40px rgba(245,158,11,0.6),0 0 50px rgba(251,191,36,0.5);}
}

@keyframes onepaquc_ny_chip_shine {
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}

@keyframes onepaquc_ny_emoji_spin {
    0%,100%{transform:rotate(0deg) scale(1);}
    25%{transform:rotate(-15deg) scale(1.1);}
    75%{transform:rotate(15deg) scale(1.1);}
}

@keyframes onepaquc_ny_text_shine {
    0%{background-position:0% center;}
    100%{background-position:200% center;}
}

@keyframes onepaquc_ny_code_pulse {
    0%,100%{transform:scale(1);}
    50%{transform:scale(1.05);}
}

@keyframes onepaquc_ny_blast {
    0%{transform:translate(-50%,-50%) scale(0.3);opacity:0.9;}
    50%{opacity:0.4;}
    100%{transform:translate(-50%,-50%) scale(1.3);opacity:0;}
}

@keyframes onepaquc_ny_confetti {
    0%{transform:translateY(0) rotate(var(--r,0deg)) scale(1);opacity:0;}
    10%{opacity:1;}
    70%{opacity:1;}
    100%{transform:translateY(200px) rotate(calc(var(--r,0deg) + 180deg)) scale(0.5);opacity:0;}
}

@keyframes onepaquc_ny_sparkle {
    0%,100%{opacity:0;transform:scale(0) rotate(0deg);}
    50%{opacity:1;transform:scale(1) rotate(180deg);}
}

@keyframes onepaquc_ny_firework {
    0%{transform:scale(0);opacity:1;}
    50%{opacity:0.8;}
    100%{transform:scale(3);opacity:0;}
}

@keyframes onepaquc_ny_slide {
    0%{opacity:0;transform:translateY(20px);}
    100%{opacity:1;transform:translateY(0);}
}

@keyframes onepaquc_ny_button_glow {
    0%,100%{box-shadow:0 0 0 3px rgba(217,119,6,0.3),0 20px 50px rgba(146,64,14,0.6),0 0 60px rgba(180,83,9,0.5),inset 0 1px 0 rgba(251,191,36,0.2);}
    50%{box-shadow:0 0 0 4px rgba(217,119,6,0.5),0 25px 60px rgba(146,64,14,0.8),0 0 80px rgba(180,83,9,0.7),inset 0 1px 0 rgba(251,191,36,0.3);}
}

@keyframes onepaquc_ny_button_gradient {
    0%{background-position:0% 50%;}
    100%{background-position:400% 50%;}
}

@keyframes onepaquc_ny_button_shine {
    0%{transform:translateX(-100%) translateY(-100%) rotate(45deg);}
    100%{transform:translateX(100%) translateY(100%) rotate(45deg);}
}

.onepaquc-ny-dismiss-menu {
    display:none;
    position:absolute;
    top:100%;
    right:0;
    background:#ffffff;
    border:1px solid #ddd;
    border-radius:8px;
    box-shadow:0 8px 20px rgba(0,0,0,0.15);
    min-width:180px;
    z-index:10000;
    margin-top:5px;
}

.onepaquc-ny-dismiss-menu.active {
    display:block;
}

.onepaquc-ny-dismiss-menu a {
    display:block;
    padding:10px 16px;
    color:#2c3338;
    text-decoration:none;
    font-size:13px;
    transition:background 0.2s ease;
    border-bottom:1px solid #f0f0f0;
}

.onepaquc-ny-dismiss-menu a:last-child {
    border-bottom:none;
}

.onepaquc-ny-dismiss-menu a:hover {
    background:#f6f7f7;
    color:#0073aa;
}

.notice-dismiss-wrapper {
    position:relative;
}

.notice-dismiss {
    z-index:99999999;
}
p.onepaquc-ny-sub {
    display: flex;
    gap: 6px;
    align-items: center;
}
</style>';

    echo '<div class="notice notice-info is-dismissible" style="padding:0;border:none;background:transparent;box-shadow:none;">';
    echo '  <div class="notice-dismiss-wrapper">';
    echo '      <button type="button" class="notice-dismiss onepaquc-ny-dismiss-trigger"><span class="screen-reader-text">Dismiss this notice.</span></button>';
    echo '      <div class="onepaquc-ny-dismiss-menu">';
    echo '          <a href="#" data-hours="3">Show again in 3 hours</a>';
    echo '          <a href="#" data-hours="12">Show again in 12 hours</a>';
    echo '          <a href="#" data-hours="24">Show again in 1 day</a>';
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
    
    // Add JavaScript for dismiss functionality
    echo '<script>
    jQuery(document).ready(function($) {
        var dismissMenu = $(".onepaquc-ny-dismiss-menu");
        var dismissTrigger = $(".onepaquc-ny-dismiss-trigger");
        var noticeWrapper = $(".notice-info");
        
        // Toggle menu on X button click
        dismissTrigger.on("click", function(e) {
            e.preventDefault();
            e.stopPropagation();
            dismissMenu.toggleClass("active");
        });
        
        // Handle dismiss option clicks
        dismissMenu.find("a").on("click", function(e) {
            e.preventDefault();
            var hours = $(this).data("hours");
            
            // Immediately close the menu and fade out the notice
            dismissMenu.removeClass("active");
            noticeWrapper.fadeOut(300);
            
            // Send AJAX request to save dismiss time
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "onepaquc_dismiss_ny_notice",
                    hours: hours,
                    nonce: "' . wp_create_nonce('onepaquc_dismiss_ny_notice') . '"
                }
            });
        });
        
        // Close menu when clicking outside
        $(document).on("click", function(e) {
            if (!$(e.target).closest(".notice-dismiss-wrapper").length) {
                dismissMenu.removeClass("active");
            }
        });
    });
    </script>';
}