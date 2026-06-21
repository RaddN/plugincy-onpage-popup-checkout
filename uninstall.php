<?php
/**
 * Uninstall cleanup for One Page Quick Checkout for WooCommerce.
 *
 * @package OnePageQuickCheckout
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

function onepaquc_uninstall_is_pro_active()
{
    $pro_plugin_file = 'one-page-quick-checkout-for-woocommerce-pro/one-page-quick-checkout-for-woocommerce-pro.php';
    $active_plugins  = (array) get_option('active_plugins', array());

    if (in_array($pro_plugin_file, $active_plugins, true)) {
        return true;
    }

    if (is_multisite()) {
        $network_plugins = (array) get_site_option('active_sitewide_plugins', array());
        return isset($network_plugins[$pro_plugin_file]);
    }

    return false;
}

wp_clear_scheduled_hook('onepaqucpro_cart_recovery_process_queue');
delete_transient('onepaquc_admin_notice');

$onepaquc_preserve_shared_data = onepaquc_uninstall_is_pro_active();

delete_metadata('user', 0, 'onepaquc_ny_notice_dismissed_until', '', true);

if ($onepaquc_preserve_shared_data) {
    return;
}

$onepaquc_option_names = array(
    'onepaquc_editor',
    'onepaquc_checkout_fields',
    'onepaquc_my_trust_badges_items',
    'onepaquc_trust_badges_enabled',
    'onepaquc_trust_badge_position',
    'onepaquc_trust_badge_style',
    'onepaqucpro_cart_recovery_schema_version',
    'onepaqucpro_cart_recovery_settings',
    'onepaqucpro_cart_recovery_templates',
    'onepaqucpro_cart_recovery_cart_overrides',
    'checkout_form_setup',
    'hide_product',
    'txt_Selected',
    'txt-direct-checkout',
    'onpage_checkout_position',
    'onpage_checkout_cart_empty',
    'onpage_checkout_enable',
    'onpage_checkout_enable_all',
    'onpage_checkout_cart_add',
    'onpage_checkout_widget_cart_empty',
    'onpage_checkout_widget_cart_add',
    'onpage_checkout_hide_cart_button',
);

foreach ($onepaquc_option_names as $onepaquc_option_name) {
    delete_option($onepaquc_option_name);
}

foreach (array('onepaquc_', 'rmenu_') as $onepaquc_prefix) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup removes plugin-owned options by prefix.
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like($onepaquc_prefix) . '%'
        )
    );
}

$onepaquc_tables = array(
    $wpdb->prefix . 'onepaqucpro_cr_events',
    $wpdb->prefix . 'onepaqucpro_cr_emails',
    $wpdb->prefix . 'onepaqucpro_cr_carts',
);

foreach ($onepaquc_tables as $onepaquc_table_name) {
    if (!preg_match('/^[A-Za-z0-9_]+$/', $onepaquc_table_name)) {
        continue;
    }

    $onepaquc_table_name = esc_sql($onepaquc_table_name);

    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Uninstall cleanup removes fixed plugin-owned tables validated above.
    $wpdb->query("DROP TABLE IF EXISTS `{$onepaquc_table_name}`");
}
