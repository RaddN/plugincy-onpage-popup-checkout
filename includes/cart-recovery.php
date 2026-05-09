<?php
if (! defined('ABSPATH')) {
    exit;
}

class Onepaqucpro_Cart_Recovery_Admin
{
    const PAGE_SLUG = 'onepaqucpro_cart_recovery';
    const TEMPLATE_PAGE_SLUG = 'onepaqucpro_cart_recovery_template';
    const SETTINGS_OPTION = 'onepaqucpro_cart_recovery_settings';
    const TEMPLATES_OPTION = 'onepaqucpro_cart_recovery_templates';
    const CART_OVERRIDES_OPTION = 'onepaqucpro_cart_recovery_cart_overrides';

    public static function init()
    {
        add_action('admin_post_onepaqucpro_cart_recovery_save_settings', array(__CLASS__, 'handle_save_settings'));
        add_action('admin_post_onepaqucpro_cart_recovery_save_templates', array(__CLASS__, 'handle_save_templates'));
        add_action('admin_post_onepaqucpro_cart_recovery_save_template', array(__CLASS__, 'handle_save_template'));
        add_action('admin_post_onepaqucpro_cart_recovery_template_action', array(__CLASS__, 'handle_template_action'));
        add_action('admin_post_onepaqucpro_cart_recovery_update_status', array(__CLASS__, 'handle_update_status'));
        add_action('admin_post_onepaqucpro_cart_recovery_bulk_action', array(__CLASS__, 'handle_bulk_action'));
        add_action('admin_post_onepaqucpro_cart_recovery_export_carts', array(__CLASS__, 'handle_export_carts'));
        add_action('admin_post_onepaqucpro_cart_recovery_export_activity', array(__CLASS__, 'handle_export_activity'));
        add_action('admin_post_onepaqucpro_cart_recovery_email_activity_action', array(__CLASS__, 'handle_email_activity_action'));
        add_action('admin_post_onepaqucpro_cart_recovery_email_activity_bulk_action', array(__CLASS__, 'handle_email_activity_bulk_action'));
        add_action('admin_post_onepaqucpro_cart_recovery_cart_action', array(__CLASS__, 'handle_cart_action'));
        add_action('admin_post_onepaqucpro_cart_recovery_save_cart_meta', array(__CLASS__, 'handle_save_cart_meta'));
        add_action('admin_post_onepaqucpro_cart_recovery_queue_action', array(__CLASS__, 'handle_queue_action'));
        add_action('admin_post_onepaqucpro_cart_recovery_send_test_email', array(__CLASS__, 'handle_send_test_email'));
        add_action('wp_ajax_onepaqucpro_cr_search_products', array(__CLASS__, 'handle_search_products'));
        add_action('wp_ajax_onepaqucpro_cr_search_categories', array(__CLASS__, 'handle_search_categories'));
        add_action('current_screen', array(__CLASS__, 'maybe_save_screen_options'));
        add_filter('screen_settings', array(__CLASS__, 'render_screen_settings'), 10, 2);
        add_filter('screen_options_show_screen', array(__CLASS__, 'show_screen_options'), 10, 2);
    }

    private static function is_free_mode()
    {
        return defined('ONEPAQUC_CART_RECOVERY_FREE_MODE') && ONEPAQUC_CART_RECOVERY_FREE_MODE;
    }

    private static function is_premium_unlocked()
    {
        if (self::is_free_mode()) {
            return false;
        }

        if (function_exists('onepaqucpro_premium_feature')) {
            return (bool) onepaqucpro_premium_feature();
        }

        if (function_exists('onepaqucpro_is_license_valid')) {
            return (bool) onepaqucpro_is_license_valid();
        }

        return 'valid' === get_option('onepaquc_license_status', '');
    }

    private static function is_locked_mode()
    {
        return ! self::is_premium_unlocked();
    }

    private static function get_upgrade_url()
    {
        if (defined('ONEPAQUC_CART_RECOVERY_UPGRADE_URL')) {
            return ONEPAQUC_CART_RECOVERY_UPGRADE_URL;
        }

        return 'https://plugincy.com/one-page-quick-checkout-for-woocommerce/';
    }

    private static function get_unlock_url()
    {
        if (self::is_free_mode()) {
            return self::get_upgrade_url();
        }

        return admin_url('admin.php?page=onepaqucpro_cart');
    }

    private static function get_locked_feature_context($feature = '')
    {
        $raw = is_scalar($feature) ? trim(wp_strip_all_tags((string) $feature)) : '';
        $key = preg_replace('/[^a-z0-9]+/', '', strtolower(remove_accents($raw)));
        $map = array(
            'email'             => array('label' => __('Email', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-email-alt2', 'class' => 'is-email'),
            'recipient'         => array('label' => __('Email recipient', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-email-alt2', 'class' => 'is-email'),
            'emailrecipient'    => array('label' => __('Email recipient', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-email-alt2', 'class' => 'is-email'),
            'sender'            => array('label' => __('Sender email', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-email-alt', 'class' => 'is-email'),
            'replyto'           => array('label' => __('Reply-to email', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-email-alt', 'class' => 'is-email'),
            'phone'             => array('label' => __('Phone', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-phone', 'class' => 'is-contact'),
            'company'           => array('label' => __('Company', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-store', 'class' => 'is-contact'),
            'customerid'        => array('label' => __('Customer ID', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-id', 'class' => 'is-customer'),
            'customertype'      => array('label' => __('Customer type', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-admin-users', 'class' => 'is-customer'),
            'customercontact'   => array('label' => __('Customer contact', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-id', 'class' => 'is-customer'),
            'ipaddress'         => array('label' => __('IP address', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-admin-site-alt3', 'class' => 'is-technical'),
            'useragent'         => array('label' => __('User agent', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-admin-site-alt3', 'class' => 'is-technical'),
            'device'            => array('label' => __('Device', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-desktop', 'class' => 'is-technical'),
            'browser'           => array('label' => __('Browser', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-admin-site-alt3', 'class' => 'is-technical'),
            'devicebrowser'     => array('label' => __('Device / browser', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-desktop', 'class' => 'is-technical'),
            'billingaddress'    => array('label' => __('Billing address', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-location-alt', 'class' => 'is-address'),
            'shippingaddress'   => array('label' => __('Shipping address', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-location-alt', 'class' => 'is-address'),
            'ordernotes'        => array('label' => __('Order notes', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-edit-page', 'class' => 'is-note'),
            'internalnote'      => array('label' => __('Internal note', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-edit-page', 'class' => 'is-note'),
            'tags'              => array('label' => __('Tags', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-tag', 'class' => 'is-note'),
            'emailcontent'      => array('label' => __('Email content', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-media-text', 'class' => 'is-email'),
            'emaildetails'      => array('label' => __('Email details', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-email', 'class' => 'is-email'),
            'emailautomation'   => array('label' => __('Email automation', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-email-alt2', 'class' => 'is-email'),
            'emailssent'        => array('label' => __('Emails sent', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-email-alt2', 'class' => 'is-email'),
            'cartactions'       => array('label' => __('Cart actions', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-admin-tools', 'class' => 'is-action'),
            'bulkactions'       => array('label' => __('Bulk actions', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-list-view', 'class' => 'is-action'),
        );

        if (isset($map[$key])) {
            return $map[$key];
        }

        if ('' !== $raw) {
            return array(
                'label' => $raw,
                'icon'  => 'dashicons-lock',
                'class' => 'is-generic',
            );
        }

        return array(
            'label' => '',
            'icon'  => 'dashicons-lock',
            'class' => 'is-generic',
        );
    }

    private static function get_locked_feature_label($feature = '')
    {
        $context = self::get_locked_feature_context($feature);

        if ('' !== $context['label']) {
            return self::is_free_mode()
                ? sprintf(__('%s - Pro only', 'one-page-quick-checkout-for-woocommerce-pro'), $context['label'])
                : sprintf(__('%s - Activate license', 'one-page-quick-checkout-for-woocommerce-pro'), $context['label']);
        }

        return self::is_free_mode()
            ? __('Pro feature only', 'one-page-quick-checkout-for-woocommerce-pro')
            : __('Activate license to unlock', 'one-page-quick-checkout-for-woocommerce-pro');
    }

    private static function render_locked_value($feature = '')
    {
        $context = self::get_locked_feature_context($feature);
        $classes = trim('onepaqucpro-cr-pro-value ' . $context['class']);

        return '<span class="' . esc_attr($classes) . '"><span class="dashicons ' . esc_attr($context['icon']) . '" aria-hidden="true"></span><span>' . esc_html(self::get_locked_feature_label($context['label'])) . '</span></span>';
    }

    private static function render_contact_value($value, $empty = '-', $feature = 'Customer contact')
    {
        if (self::is_locked_mode()) {
            return self::render_locked_value($feature);
        }

        $value = is_scalar($value) ? (string) $value : '';

        return esc_html('' !== $value ? $value : $empty);
    }

    private static function is_pro_only_meta_label($label)
    {
        $label = strtolower(preg_replace('/[^a-z0-9]+/', '', remove_accents(wp_strip_all_tags((string) $label))));

        return in_array($label, array(
            'email',
            'recipient',
            'phone',
            'company',
            'customerid',
            'customertype',
            'ipaddress',
            'useragent',
            'device',
            'browser',
            'devicebrowser',
            'billingaddress',
            'shippingaddress',
            'ordernotes',
        ), true);
    }

    private static function render_detail_meta_value($label, $value)
    {
        if (self::is_locked_mode() && self::is_pro_only_meta_label($label)) {
            return self::render_locked_value($label);
        }

        if (! is_scalar($value)) {
            $value = wp_json_encode($value);
        }

        if (is_string($value) && false !== strpos($value, 'Mozilla/')) {
            return '<code>' . esc_html($value) . '</code>';
        }

        return wp_kses_post($value);
    }

    private static function redirect_with_pro_required($tab = 'carts', $args = array())
    {
        $args = wp_parse_args(
            $args,
            array(
                'tab'       => $tab,
                'cr_notice' => 'pro_required',
            )
        );

        wp_safe_redirect(self::get_page_url($args));
        exit;
    }

    public static function maybe_save_screen_options($screen)
    {
        if (! self::is_cart_recovery_screen($screen)) {
            return;
        }

        $tab = self::get_active_tab();
        if (! self::supports_screen_options_for_tab($tab)) {
            return;
        }
        $screen_context = self::get_screen_option_context($tab);

        if ('POST' !== strtoupper(isset($_SERVER['REQUEST_METHOD']) ? wp_unslash($_SERVER['REQUEST_METHOD']) : '')) {
            return;
        }

        if (empty($_POST['screen-options-apply']) || empty($_POST['screenoptionnonce'])) {
            return;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST['screenoptionnonce']));
        if (! wp_verify_nonce($nonce, 'screen-options-nonce')) {
            return;
        }

        $options = isset($_POST['onepaqucpro_cr_screen_options']) ? wp_unslash($_POST['onepaqucpro_cr_screen_options']) : array();
        if (! is_array($options)) {
            $options = array();
        }

        $per_page = max(5, min(100, absint(isset($options['per_page']) ? $options['per_page'] : self::get_screen_option_per_page($screen_context))));
        $allowed_columns = array_keys(self::get_screen_option_columns($screen_context));
        $columns = isset($options['columns']) ? array_map('sanitize_key', (array) $options['columns']) : array();
        $columns = array_values(array_intersect($allowed_columns, $columns));

        update_user_meta(get_current_user_id(), self::get_screen_option_meta_key($screen_context, 'per_page'), $per_page);
        update_user_meta(get_current_user_id(), self::get_screen_option_meta_key($screen_context, 'columns'), $columns);
    }

    public static function render_screen_settings($settings, $screen)
    {
        if (! self::is_cart_recovery_screen($screen)) {
            return $settings;
        }

        $tab = self::get_active_tab();
        if (! self::supports_screen_options_for_tab($tab)) {
            return $settings;
        }

        add_filter('screen_options_show_submit', '__return_true');

        $screen_context = self::get_screen_option_context($tab);
        $primary_label = in_array($screen_context, array('activity', 'email_activity'), true)
            ? __('Item', 'one-page-quick-checkout-for-woocommerce-pro')
            : ('email_templates' === $screen_context ? __('Email', 'one-page-quick-checkout-for-woocommerce-pro') : __('Cart', 'one-page-quick-checkout-for-woocommerce-pro'));
        $visible_columns = self::get_screen_option_visible_columns($screen_context);
        $columns = self::get_screen_option_columns($screen_context);
        $per_page = self::get_screen_option_per_page($screen_context);

        ob_start();
?>
        <fieldset class="metabox-prefs">
            <legend><?php esc_html_e('Columns', 'one-page-quick-checkout-for-woocommerce-pro'); ?></legend>
            <label for="onepaqucpro-cr-screen-primary">
                <input type="checkbox" id="onepaqucpro-cr-screen-primary" checked disabled>
                <?php echo esc_html($primary_label); ?>
            </label>
            <?php foreach ($columns as $column_key => $label) : ?>
                <label for="onepaqucpro-cr-screen-<?php echo esc_attr($tab . '-' . $column_key); ?>">
                    <input
                        type="checkbox"
                        id="onepaqucpro-cr-screen-<?php echo esc_attr($tab . '-' . $column_key); ?>"
                        name="onepaqucpro_cr_screen_options[columns][]"
                        value="<?php echo esc_attr($column_key); ?>"
                        <?php checked(in_array($column_key, $visible_columns, true)); ?>>
                    <?php echo esc_html($label); ?>
                </label>
            <?php endforeach; ?>
        </fieldset>
        <fieldset class="screen-options">
            <legend><?php esc_html_e('Pagination', 'one-page-quick-checkout-for-woocommerce-pro'); ?></legend>
            <label for="onepaqucpro-cr-screen-per-page"><?php esc_html_e('Number of items per page:', 'one-page-quick-checkout-for-woocommerce-pro'); ?></label>
            <input
                type="number"
                class="screen-per-page"
                id="onepaqucpro-cr-screen-per-page"
                name="onepaqucpro_cr_screen_options[per_page]"
                value="<?php echo esc_attr($per_page); ?>"
                min="5"
                max="100"
                step="5">
        </fieldset>
    <?php

        return $settings . ob_get_clean();
    }

    public static function show_screen_options($show_screen, $screen)
    {
        if (self::is_cart_recovery_screen($screen) && self::supports_screen_options_for_tab(self::get_active_tab())) {
            return true;
        }

        return $show_screen;
    }

    public static function render_page()
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'one-page-quick-checkout-for-woocommerce-pro'));
        }

        $active_tab       = self::get_active_tab();
        $carts            = self::get_carts();
        $cart_context     = self::get_cart_table_context($carts);
        $activity_context = self::get_activity_table_context($carts);
        $analytics        = self::get_analytics_context($carts);
        $journey          = self::get_journey_context($carts);
        $settings         = self::get_settings();
        $templates        = self::get_templates();
        $active_templates = count(array_filter($templates, function ($template) {
            return ! empty($template['enabled']);
        }));
        $is_locked_mode = self::is_locked_mode();
        $status_label   = ! empty($settings['enabled']) ? __('Enabled', 'one-page-quick-checkout-for-woocommerce-pro') : __('Disabled', 'one-page-quick-checkout-for-woocommerce-pro');
        $status_class   = ! empty($settings['enabled']) ? 'is-enabled' : 'is-disabled';

        if ($is_locked_mode) {
            $status_label = self::is_free_mode()
                ? __('Details Only', 'one-page-quick-checkout-for-woocommerce-pro')
                : __('License Required', 'one-page-quick-checkout-for-woocommerce-pro');
            $status_class = 'is-disabled';
        }

    ?>
        <div class="onepaqucpro-cr-page">
            <?php self::render_notice(); ?>

            <div class="plugincy_nav_card onepaqucpro-cr-hero">
                <div class="onepaqucpro-cr-hero__intro">
                    <span class="onepaqucpro-cr-hero__eyebrow"><?php esc_html_e('Revenue Recovery Suite', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                    <h1><?php esc_html_e('Cart Recovery', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h1>
                    <p><?php esc_html_e('Track abandoned carts, review the shopper journey, and tune recovery emails from one workspace that matches the rest of your Onpage Checkout admin.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                </div>
                <div class="onepaqucpro-cr-hero__highlights">
                    <div class="onepaqucpro-cr-hero__highlight">
                        <span class="onepaqucpro-cr-hero__label"><?php esc_html_e('Recovery Status', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <strong class="<?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></strong>
                    </div>
                    <div class="onepaqucpro-cr-hero__highlight">
                        <span class="onepaqucpro-cr-hero__label"><?php esc_html_e('Tracked Carts', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <strong><?php echo esc_html(number_format_i18n(count($carts))); ?></strong>
                    </div>
                    <div class="onepaqucpro-cr-hero__highlight">
                        <span class="onepaqucpro-cr-hero__label"><?php esc_html_e('Active Emails', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <strong><?php echo $is_locked_mode ? wp_kses_post(self::render_locked_value('Email automation')) : esc_html(number_format_i18n($active_templates)); ?></strong>
                    </div>
                </div>
            </div>

            <nav class="onepaqucpro-cr-tabs" aria-label="<?php esc_attr_e('Cart recovery sections', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                <?php foreach (self::get_tabs() as $tab_key => $tab) : ?>
                    <a class="onepaqucpro-cr-tab <?php echo $active_tab === $tab_key ? 'is-active' : ''; ?>" href="<?php echo esc_url(self::get_page_url(array('tab' => $tab_key))); ?>">
                        <span class="dashicons <?php echo esc_attr($tab['icon']); ?>"></span>
                        <span><?php echo esc_html($tab['label']); ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="onepaqucpro-cr-panel">
                <?php
                switch ($active_tab) {
                    case 'analytics':
                        $is_locked_mode ? self::render_locked_feature_tab('analytics', $carts, $settings, $templates) : self::render_analytics_tab($analytics);
                        break;
                    case 'journey':
                        $is_locked_mode ? self::render_locked_feature_tab('journey', $carts, $settings, $templates) : self::render_journey_tab($journey);
                        break;
                    case 'activity':
                        self::render_activity_tab($activity_context);
                        break;
                    case 'settings':
                        self::render_settings_tab($settings);
                        break;
                    case 'email':
                        $is_locked_mode ? self::render_locked_feature_tab('email', $carts, $settings, $templates) : self::render_email_tab($settings, $templates, $carts);
                        break;
                    case 'carts':
                    default:
                        self::render_carts_tab($cart_context);
                        break;
                }
                ?>
            </div>

            <?php self::render_free_upgrade_promotion($active_tab); ?>
            <?php self::render_modal_shell(); ?>
            <?php self::render_detail_templates($carts); ?>
        </div>
    <?php
    }

    public static function handle_save_settings()
    {
        self::assert_admin_access();
        check_admin_referer('onepaqucpro_cart_recovery_save_settings');

        $context = self::sanitize_choice(isset($_POST['settings_context']) ? wp_unslash($_POST['settings_context']) : 'settings', array('settings', 'email'), 'settings');
        $posted_settings = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : array();
        $stored_settings = self::get_settings();
        $checkbox_keys = self::get_settings_checkbox_keys();
        $setting_keys  = 'email' === $context ? self::get_email_settings_keys() : self::get_general_settings_keys();

        if (! is_array($posted_settings)) {
            $posted_settings = array();
        }

        if (self::is_locked_mode()) {
            if ('email' === $context) {
                self::redirect_with_pro_required('email', array('tab' => 'email', 'cr_email_view' => 'settings'));
            }

            $setting_keys = array('enabled', 'inactivity_timeout', 'retention_days');
        }

        foreach ($setting_keys as $key) {
            if (in_array($key, $checkbox_keys, true)) {
                $stored_settings[$key] = empty($posted_settings[$key]) ? 0 : 1;
                continue;
            }

            if ('settings' === $context && in_array($key, array('excluded_product_ids', 'excluded_category_ids', 'excluded_roles'), true) && ! array_key_exists($key, $posted_settings)) {
                $stored_settings[$key] = array();
                continue;
            }

            if (array_key_exists($key, $posted_settings)) {
                $stored_settings[$key] = $posted_settings[$key];
            }
        }

        update_option(self::SETTINGS_OPTION, self::sanitize_settings($stored_settings), false);

        if ('email' === $context) {
            self::redirect_with_notice_from_referer('settings_saved', array('tab' => 'email', 'cr_email_view' => 'settings'));
        }

        self::redirect_with_notice($context, 'settings_saved');
    }

    public static function handle_save_templates()
    {
        self::assert_admin_access();
        check_admin_referer('onepaqucpro_cart_recovery_save_templates');

        if (self::is_locked_mode()) {
            self::redirect_with_pro_required('email', array('tab' => 'email', 'cr_email_view' => 'templates'));
        }

        $posted_templates = isset($_POST['templates']) ? wp_unslash($_POST['templates']) : array();
        $posted_templates = is_array($posted_templates) ? $posted_templates : array();
        $existing         = self::get_templates();
        $updated          = array();
        $posted_by_id     = array();

        foreach ($posted_templates as $template) {
            if (! is_array($template) || empty($template['id'])) {
                continue;
            }

            $posted_by_id[sanitize_key($template['id'])] = $template;
        }

        foreach ($existing as $template) {
            $template_id = ! empty($template['id']) ? sanitize_key($template['id']) : '';
            if ($template_id && isset($posted_by_id[$template_id])) {
                $updated[] = wp_parse_args($posted_by_id[$template_id], $template);
                unset($posted_by_id[$template_id]);
                continue;
            }

            $updated[] = $template;
        }

        foreach ($posted_by_id as $template) {
            $updated[] = $template;
        }

        update_option(self::TEMPLATES_OPTION, self::sanitize_templates($updated), false);

        self::redirect_with_notice_from_referer('templates_saved', array('tab' => 'email', 'cr_email_view' => 'templates'));
    }

    public static function handle_save_template()
    {
        self::assert_admin_access();
        check_admin_referer('onepaqucpro_cart_recovery_save_template');

        if (self::is_locked_mode()) {
            wp_safe_redirect(self::get_page_url(array('tab' => 'email', 'cr_email_view' => 'templates', 'cr_notice' => 'pro_required')));
            exit;
        }

        $template = isset($_POST['template']) ? wp_unslash($_POST['template']) : array();
        if (! is_array($template)) {
            $template = array();
        }

        $existing = self::get_templates();
        $updated  = array();
        $handled  = false;
        $incoming_id = sanitize_key(isset($template['id']) ? $template['id'] : '');

        foreach ($existing as $row) {
            if (! is_array($row)) {
                continue;
            }

            if ($incoming_id && isset($row['id']) && $row['id'] === $incoming_id) {
                $updated[] = $template;
                $handled   = true;
                continue;
            }

            $updated[] = $row;
        }

        if (! $handled) {
            $updated[] = $template;
        }

        update_option(self::TEMPLATES_OPTION, self::sanitize_templates($updated), false);

        $saved = self::sanitize_templates(array($template));
        $saved_id = ! empty($saved[0]['id']) ? $saved[0]['id'] : '';

        wp_safe_redirect(self::get_template_page_url($saved_id ? $saved_id : 'new', array(
            'cr_notice' => 'template_saved',
        )));
        exit;
    }

    public static function handle_template_action()
    {
        self::assert_admin_access();

        $template_id = isset($_GET['template_id']) ? sanitize_key(wp_unslash($_GET['template_id'])) : '';
        $action      = isset($_GET['template_action']) ? sanitize_key(wp_unslash($_GET['template_action'])) : '';

        check_admin_referer('onepaqucpro_cart_recovery_template_action_' . $template_id . '_' . $action);

        if (self::is_locked_mode()) {
            self::redirect_with_pro_required('email', array('tab' => 'email', 'cr_email_view' => 'templates'));
        }

        if (! $template_id || ! in_array($action, array('duplicate', 'delete'), true)) {
            self::redirect_with_notice_from_referer('invalid_template', array('tab' => 'email', 'cr_email_view' => 'templates'));
        }

        $templates = self::get_templates();
        $updated   = array();
        $handled   = false;

        foreach ($templates as $template) {
            if (empty($template['id']) || $template['id'] !== $template_id) {
                $updated[] = $template;
                continue;
            }

            $handled = true;

            if ('duplicate' === $action) {
                $updated[] = $template;
                $copy = $template;
                $copy['id'] = 'template_' . substr(md5($template['id'] . microtime(true) . wp_rand()), 0, 8);
                $copy['name'] = sprintf(
                    /* translators: %s: email template name. */
                    __('%s Copy', 'one-page-quick-checkout-for-woocommerce-pro'),
                    isset($template['name']) ? $template['name'] : __('Email', 'one-page-quick-checkout-for-woocommerce-pro')
                );
                $copy['updated_at'] = current_time('mysql');
                $updated[] = $copy;
            }
        }

        if ('delete' === $action && $handled) {
            update_option(self::TEMPLATES_OPTION, self::sanitize_templates($updated), false);
            self::redirect_with_notice_from_referer('template_deleted', array('tab' => 'email', 'cr_email_view' => 'templates'));
        }

        if ('duplicate' === $action && $handled) {
            update_option(self::TEMPLATES_OPTION, self::sanitize_templates($updated), false);
            self::redirect_with_notice_from_referer('template_duplicated', array('tab' => 'email', 'cr_email_view' => 'templates'));
        }

        self::redirect_with_notice_from_referer('invalid_template', array('tab' => 'email', 'cr_email_view' => 'templates'));
    }

    public static function render_template_page()
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'one-page-quick-checkout-for-woocommerce-pro'));
        }

        $templates = self::get_templates();
        $template_id = isset($_GET['template_id']) ? sanitize_key(wp_unslash($_GET['template_id'])) : 'new';

        $template = self::get_template_for_edit($templates, $template_id ? $template_id : 'new');

        $back_url = self::get_page_url(array('tab' => 'email', 'cr_email_view' => 'templates'));
        $title    = $template['name'] ? $template['name'] : __('New Recovery Email', 'one-page-quick-checkout-for-woocommerce-pro');
        $template_status = ! empty($template['enabled'])
            ? __('Enabled', 'one-page-quick-checkout-for-woocommerce-pro')
            : __('Disabled', 'one-page-quick-checkout-for-woocommerce-pro');

        echo '<div class="wrap onepaqucpro-cr-template-screen">';
        echo '<h1 class="screen-reader-text">' . esc_html($title) . '</h1>';
        ?>
        <div class="onepaqucpro-cr-template-page-head">
            <div class="onepaqucpro-cr-template-page-head__copy">
                <span class="onepaqucpro-cr-template-page-head__eyebrow"><?php esc_html_e('Recovery Email Builder', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                <div class="onepaqucpro-cr-template-page-head__title"><?php echo esc_html($title); ?></div>
                <p><?php esc_html_e('Edit the message, cart-items layout, delivery timing, and merge tags used for this recovery email.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
            </div>
            <div class="onepaqucpro-cr-template-page-head__aside">
                <div class="onepaqucpro-cr-template-page-head__chips">
                    <span>
                        <small><?php esc_html_e('Status', 'one-page-quick-checkout-for-woocommerce-pro'); ?></small>
                        <strong><?php echo esc_html($template_status); ?></strong>
                    </span>
                    <span>
                        <small><?php esc_html_e('Trigger', 'one-page-quick-checkout-for-woocommerce-pro'); ?></small>
                        <strong><?php echo esc_html(self::format_template_delay($template)); ?></strong>
                    </span>
                    <?php if (! empty($template['id'])) : ?>
                        <span>
                            <small><?php esc_html_e('Template ID', 'one-page-quick-checkout-for-woocommerce-pro'); ?></small>
                            <strong><?php echo esc_html($template['id']); ?></strong>
                        </span>
                    <?php endif; ?>
                </div>
                <a class="button button-secondary" href="<?php echo esc_url($back_url); ?>"><?php esc_html_e('Back to Templates', 'one-page-quick-checkout-for-woocommerce-pro'); ?></a>
            </div>
        </div>
        <?php

        self::render_notice();
        if (self::is_locked_mode()) : ?>
            <section class="onepaqucpro-cr-pro-preview">
                <div class="onepaqucpro-cr-pro-preview__content" aria-hidden="true">
                    <?php self::render_template_editor($template); ?>
                </div>
                <div class="onepaqucpro-cr-pro-preview__overlay">
                    <span class="dashicons dashicons-lock" aria-hidden="true"></span>
                    <h2><?php esc_html_e('Email template builder is a Pro feature', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h2>
                    <p><?php esc_html_e('Preview the builder interface here. Unlock Pro with an active license to create, edit, and send recovery email templates.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                    <a class="button button-primary button-hero" href="<?php echo esc_url(self::get_unlock_url()); ?>" <?php echo self::is_free_mode() ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                        <?php echo esc_html(self::is_free_mode() ? __('Upgrade to Pro', 'one-page-quick-checkout-for-woocommerce-pro') : __('Activate License', 'one-page-quick-checkout-for-woocommerce-pro')); ?>
                    </a>
                </div>
            </section>
        <?php
            self::render_free_upgrade_promotion('template');
            self::render_modal_shell();
            echo '</div>';
            return;
        endif;
        self::render_template_editor($template);
        self::render_free_upgrade_promotion('template');
        self::render_modal_shell();
        echo '</div>';
    }

    public static function handle_update_status()
    {
        self::assert_admin_access();

        $cart_id = isset($_REQUEST['cart_id']) ? sanitize_key(wp_unslash($_REQUEST['cart_id'])) : '';
        $status  = isset($_REQUEST['status']) ? sanitize_key(wp_unslash($_REQUEST['status'])) : '';

        check_admin_referer('onepaqucpro_cart_recovery_update_status_' . $cart_id);

        if (self::is_locked_mode()) {
            self::redirect_with_pro_required('carts');
        }

        if (! in_array($status, array('abandoned', 'recovered'), true) || ! self::cart_exists($cart_id)) {
            self::redirect_with_notice('carts', 'invalid_cart');
        }

        if (! class_exists('Onepaqucpro_Cart_Recovery_Tracker') || ! Onepaqucpro_Cart_Recovery_Tracker::update_cart_status($cart_id, $status)) {
            self::redirect_with_notice('carts', 'invalid_cart');
        }

        self::redirect_with_notice('carts', 'status_updated');
    }

    public static function handle_bulk_action()
    {
        self::assert_admin_access();
        check_admin_referer('onepaqucpro_cart_recovery_bulk_action');

        $bulk_action = isset($_POST['bulk_action']) ? sanitize_key(wp_unslash($_POST['bulk_action'])) : '';
        $cart_ids    = isset($_POST['cart_ids']) ? array_map('sanitize_key', (array) wp_unslash($_POST['cart_ids'])) : array();

        if (self::is_locked_mode()) {
            self::redirect_with_pro_required('carts');
        }

        if (empty($cart_ids)) {
            self::redirect_with_notice('carts', 'nothing_selected');
        }

        $supported_actions = array('mark_recovered', 'mark_abandoned', 'archive', 'ignore', 'activate', 'delete');

        if (! in_array($bulk_action, $supported_actions, true)) {
            self::redirect_with_notice('carts', 'nothing_selected');
        }

        foreach ($cart_ids as $cart_id) {
            if (! self::cart_exists($cart_id)) {
                continue;
            }
            if (class_exists('Onepaqucpro_Cart_Recovery_Tracker')) {
                Onepaqucpro_Cart_Recovery_Tracker::perform_cart_action($cart_id, $bulk_action);
            }
        }

        self::redirect_with_notice('carts', 'status_updated');
    }

    public static function handle_export_carts()
    {
        self::assert_admin_access();
        check_admin_referer('onepaqucpro_cart_recovery_export_carts', 'onepaqucpro_cart_recovery_export_carts_nonce');

        $cart_context  = self::get_cart_table_context(self::get_carts());
        $rows          = $cart_context['all_items'];
        $selected_ids  = isset($_POST['cart_ids']) ? array_map('sanitize_key', (array) wp_unslash($_POST['cart_ids'])) : array();

        if (! empty($selected_ids)) {
            $rows = array_values(array_filter($rows, function ($row) use ($selected_ids) {
                return in_array($row['id'], $selected_ids, true);
            }));
        }

        if (empty($rows)) {
            self::redirect_with_notice('carts', 'nothing_selected');
        }

        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=cart-recovery-carts-' . gmdate('Y-m-d-H-i-s') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, array('Cart ID', 'Customer', 'Email', 'Phone', 'Company', 'Billing Address', 'Shipping Address', 'Order Notes', 'Status', 'Admin State', 'Cart Total', 'Item Count', 'Customer Type', 'Device', 'Browser', 'Coupons', 'Recovery Source', 'Recovered Order', 'Abandoned At', 'Recovered At', 'Last Activity'));

        foreach ($rows as $row) {
            $locked_values = array(
                'email'            => wp_strip_all_tags(self::render_locked_value('Email')),
                'phone'            => wp_strip_all_tags(self::render_locked_value('Phone')),
                'company'          => wp_strip_all_tags(self::render_locked_value('Company')),
                'billing_address'  => wp_strip_all_tags(self::render_locked_value('Billing address')),
                'shipping_address' => wp_strip_all_tags(self::render_locked_value('Shipping address')),
                'order_notes'      => wp_strip_all_tags(self::render_locked_value('Order notes')),
                'customer_type'    => wp_strip_all_tags(self::render_locked_value('Customer type')),
                'device'           => wp_strip_all_tags(self::render_locked_value('Device')),
                'browser'          => wp_strip_all_tags(self::render_locked_value('Browser')),
            );
            fputcsv($output, array(
                $row['id'],
                $row['customer_name'],
                self::is_locked_mode() ? $locked_values['email'] : $row['email'],
                self::is_locked_mode() ? $locked_values['phone'] : $row['customer_phone'],
                self::is_locked_mode() ? $locked_values['company'] : $row['customer_company'],
                self::is_locked_mode() ? $locked_values['billing_address'] : self::format_profile_address($row['billing_address']),
                self::is_locked_mode() ? $locked_values['shipping_address'] : self::format_profile_address($row['shipping_address']),
                self::is_locked_mode() ? $locked_values['order_notes'] : $row['order_notes'],
                ucfirst($row['status']),
                ucfirst($row['admin_state'] ? $row['admin_state'] : 'none'),
                wp_strip_all_tags(self::format_currency($row['cart_total'], $row['currency'])),
                $row['item_count'],
                self::is_locked_mode() ? $locked_values['customer_type'] : ucfirst($row['customer_type']),
                self::is_locked_mode() ? $locked_values['device'] : ucfirst($row['device_type']),
                self::is_locked_mode() ? $locked_values['browser'] : $row['browser'],
                implode(', ', $row['coupon_codes']),
                $row['recovery_source'],
                $row['recovered_order_id'] ? '#' . $row['recovered_order_id'] : '',
                self::format_datetime($row['abandoned_at']),
                self::format_datetime($row['recovered_at']),
                self::format_datetime($row['last_activity_at']),
            ));
        }

        fclose($output);
        exit;
    }

    public static function handle_export_activity()
    {
        self::assert_admin_access();
        check_admin_referer('onepaqucpro_cart_recovery_export_activity');

        $activity_context = self::get_activity_table_context(self::get_carts());
        $rows             = $activity_context['all_items'];
        $selected_ids     = isset($_POST['activity_ids']) ? array_map('sanitize_text_field', (array) wp_unslash($_POST['activity_ids'])) : array();

        if (! empty($selected_ids)) {
            $rows = array_values(array_filter($rows, function ($row) use ($selected_ids) {
                return in_array($row['id'], $selected_ids, true);
            }));
        }

        if (empty($rows)) {
            self::redirect_with_notice('activity', 'nothing_selected');
        }

        self::stream_activity_csv($rows, 'cart-recovery-activity-' . gmdate('Y-m-d-H-i-s') . '.csv');
    }

    public static function handle_email_activity_action()
    {
        self::assert_admin_access();

        $email_id = isset($_REQUEST['email_id']) ? absint(wp_unslash($_REQUEST['email_id'])) : 0;
        $action   = self::sanitize_choice(isset($_REQUEST['email_activity_action']) ? wp_unslash($_REQUEST['email_activity_action']) : '', array('resend', 'retry_failed'), '');

        check_admin_referer('onepaqucpro_cart_recovery_email_activity_action_' . $email_id . '_' . $action);

        if (self::is_locked_mode()) {
            self::redirect_with_pro_required('email', array('tab' => 'email', 'cr_email_view' => 'activity'));
        }

        if (! $email_id || ! $action) {
            self::redirect_with_notice_from_referer('invalid_email_activity', array('tab' => 'email', 'cr_email_view' => 'activity'));
        }

        if (! class_exists('Onepaqucpro_Cart_Recovery_Tracker')) {
            self::redirect_with_notice_from_referer('service_unavailable', array('tab' => 'email', 'cr_email_view' => 'activity'));
        }

        if (! Onepaqucpro_Cart_Recovery_Tracker::perform_email_activity_action($email_id, $action)) {
            self::redirect_with_notice_from_referer('email_action_failed', array('tab' => 'email', 'cr_email_view' => 'activity'));
        }

        self::redirect_with_notice_from_referer('email_action_done', array('tab' => 'email', 'cr_email_view' => 'activity'));
    }

    public static function handle_email_activity_bulk_action()
    {
        self::assert_admin_access();
        check_admin_referer('onepaqucpro_cart_recovery_email_activity_bulk_action');

        if (self::is_locked_mode()) {
            self::redirect_with_pro_required('email', array('tab' => 'email', 'cr_email_view' => 'activity'));
        }

        $bulk_action = self::sanitize_choice(isset($_POST['email_activity_bulk_action']) ? wp_unslash($_POST['email_activity_bulk_action']) : '', array('export', 'resend', 'retry_failed'), '');
        $selected_ids = isset($_POST['activity_ids']) ? array_map('sanitize_text_field', (array) wp_unslash($_POST['activity_ids'])) : array();
        $activity_context = self::get_email_activity_table_context(self::get_carts());
        $rows = $activity_context['all_items'];

        if (! empty($selected_ids)) {
            $rows = array_values(array_filter($rows, function ($row) use ($selected_ids) {
                return in_array($row['id'], $selected_ids, true);
            }));
        }

        if ('export' === $bulk_action) {
            if (empty($rows)) {
                self::redirect_with_notice_from_referer('nothing_selected', array('tab' => 'email', 'cr_email_view' => 'activity'));
            }

            self::stream_activity_csv($rows, 'cart-recovery-email-activity-' . gmdate('Y-m-d-H-i-s') . '.csv');
        }

        if (! in_array($bulk_action, array('resend', 'retry_failed'), true)) {
            self::redirect_with_notice_from_referer('nothing_selected', array('tab' => 'email', 'cr_email_view' => 'activity'));
        }

        if (empty($selected_ids)) {
            self::redirect_with_notice_from_referer('nothing_selected', array('tab' => 'email', 'cr_email_view' => 'activity'));
        }

        if (! class_exists('Onepaqucpro_Cart_Recovery_Tracker')) {
            self::redirect_with_notice_from_referer('service_unavailable', array('tab' => 'email', 'cr_email_view' => 'activity'));
        }

        $processed = 0;
        foreach ($rows as $row) {
            if ('email' !== $row['row_type'] || empty($row['email_log_id'])) {
                continue;
            }

            if ('retry_failed' === $bulk_action && 'failed' !== $row['status']) {
                continue;
            }

            if (Onepaqucpro_Cart_Recovery_Tracker::perform_email_activity_action((int) $row['email_log_id'], $bulk_action)) {
                $processed++;
            }
        }

        self::redirect_with_notice_from_referer($processed ? 'email_actions_done' : 'email_action_failed', array('tab' => 'email', 'cr_email_view' => 'activity'));
    }

    private static function stream_activity_csv($rows, $filename)
    {
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . sanitize_file_name($filename));

        $output = fopen('php://output', 'w');
        fputcsv($output, array('Type', 'Item', 'Recipient', 'Subject', 'Status', 'Details', 'Occurred', 'Cart ID'));

        foreach ($rows as $row) {
            $recipient = self::is_locked_mode() ? wp_strip_all_tags(self::render_locked_value('Email recipient')) : $row['recipient'];
            fputcsv($output, array(
                $row['row_type'],
                $row['email_name'],
                $recipient,
                $row['subject'],
                ucfirst($row['status']),
                $row['details'] ? $row['details'] : $row['engagement'],
                self::format_datetime($row['occurred_at']),
                $row['cart_id'],
            ));
        }

        fclose($output);
        exit;
    }

    public static function handle_cart_action()
    {
        self::assert_admin_access();

        $cart_id = isset($_REQUEST['cart_id']) ? sanitize_key(wp_unslash($_REQUEST['cart_id'])) : '';
        $action  = isset($_REQUEST['cart_action']) ? sanitize_key(wp_unslash($_REQUEST['cart_action'])) : '';

        check_admin_referer('onepaqucpro_cart_recovery_cart_action_' . $cart_id . '_' . $action);

        if (! $cart_id || ! $action) {
            self::redirect_with_notice_from_referer('invalid_cart', array('tab' => 'carts'));
        }

        if (! class_exists('Onepaqucpro_Cart_Recovery_Tracker')) {
            self::redirect_with_notice_from_referer('service_unavailable', array('tab' => 'carts'));
        }

        if (! self::cart_exists($cart_id)) {
            self::redirect_with_notice_from_referer('invalid_cart', array('tab' => 'carts'));
        }

        if (self::is_locked_mode()) {
            self::redirect_with_pro_required('carts');
        }

        if (in_array($action, array('send_next', 'resend_last'), true)) {
            $action_state = self::get_cart_action_state_by_id($cart_id);

            if (! $action_state) {
                self::redirect_with_notice_from_referer('invalid_cart', array('tab' => 'carts'));
            }

            if ('send_next' === $action && empty($action_state['can_send_next'])) {
                self::redirect_with_notice_from_referer('no_pending_email', array('tab' => 'carts'));
            }

            if ('resend_last' === $action && empty($action_state['can_resend_last'])) {
                self::redirect_with_notice_from_referer('no_email_history', array('tab' => 'carts'));
            }
        }

        if (! Onepaqucpro_Cart_Recovery_Tracker::perform_cart_action($cart_id, $action)) {
            self::redirect_with_notice_from_referer('invalid_cart', array('tab' => 'carts'));
        }

        self::redirect_with_notice_from_referer('status_updated', array('tab' => 'carts'));
    }

    public static function handle_save_cart_meta()
    {
        self::assert_admin_access();

        $cart_id = isset($_POST['cart_id']) ? sanitize_key(wp_unslash($_POST['cart_id'])) : '';
        check_admin_referer('onepaqucpro_cart_recovery_save_cart_meta_' . $cart_id);

        if (! $cart_id || ! class_exists('Onepaqucpro_Cart_Recovery_Tracker')) {
            self::redirect_with_notice('carts', 'invalid_cart');
        }

        if (self::is_locked_mode()) {
            self::redirect_with_pro_required('carts');
        }

        $notes = isset($_POST['cart_notes']) ? wp_unslash($_POST['cart_notes']) : '';
        $tags  = isset($_POST['cart_tags']) ? wp_unslash($_POST['cart_tags']) : '';

        if (! Onepaqucpro_Cart_Recovery_Tracker::save_cart_meta($cart_id, $notes, $tags)) {
            self::redirect_with_notice('carts', 'invalid_cart');
        }

        self::redirect_with_notice('carts', 'cart_saved');
    }

    public static function handle_queue_action()
    {
        self::assert_admin_access();
        check_admin_referer('onepaqucpro_cart_recovery_queue_action');

        if (! class_exists('Onepaqucpro_Cart_Recovery_Tracker')) {
            self::redirect_with_notice('settings', 'service_unavailable');
        }

        $action = isset($_POST['queue_action']) ? sanitize_key(wp_unslash($_POST['queue_action'])) : '';

        switch ($action) {
            case 'run_queue':
                Onepaqucpro_Cart_Recovery_Tracker::process_queue();
                self::redirect_with_notice('settings', 'queue_ran');
                break;
            case 'cleanup':
                Onepaqucpro_Cart_Recovery_Tracker::run_manual_cleanup();
                self::redirect_with_notice('settings', 'cleanup_ran');
                break;
            case 'anonymize':
                Onepaqucpro_Cart_Recovery_Tracker::anonymize_expired_carts();
                self::redirect_with_notice('settings', 'privacy_ran');
                break;
        }

        self::redirect_with_notice('settings', 'nothing_selected');
    }

    public static function handle_send_test_email()
    {
        self::assert_admin_access();
        check_admin_referer('onepaqucpro_cart_recovery_send_test_email');

        if (self::is_locked_mode()) {
            self::redirect_with_pro_required('email', array('tab' => 'email', 'cr_email_view' => 'settings'));
        }

        if (! class_exists('Onepaqucpro_Cart_Recovery_Tracker')) {
            self::redirect_with_notice_from_referer('service_unavailable', array('tab' => 'email', 'cr_email_view' => 'settings'));
        }

        $template_id = isset($_POST['test_template_id']) ? sanitize_key(wp_unslash($_POST['test_template_id'])) : '';
        $recipient   = isset($_POST['test_recipient']) ? sanitize_email(wp_unslash($_POST['test_recipient'])) : '';

        if (! $template_id || ! $recipient || ! Onepaqucpro_Cart_Recovery_Tracker::send_test_email($template_id, $recipient)) {
            self::redirect_with_notice_from_referer('test_failed', array('tab' => 'email', 'cr_email_view' => 'settings'));
        }

        self::redirect_with_notice_from_referer('test_sent', array('tab' => 'email', 'cr_email_view' => 'settings'));
    }

    public static function handle_search_products()
    {
        self::assert_admin_access();
        check_ajax_referer('onepaqucpro_cart_recovery_search', 'nonce');

        if (self::is_locked_mode()) {
            wp_send_json(array('results' => array()));
        }

        $term = sanitize_text_field(isset($_GET['term']) ? wp_unslash($_GET['term']) : '');
        if (strlen($term) < 3) {
            wp_send_json(array('results' => array()));
        }

        $ids = array();
        if (class_exists('WC_Data_Store')) {
            $data_store = WC_Data_Store::load('product');
            if (is_object($data_store) && is_callable(array($data_store, 'search_products'))) {
                $ids = $data_store->search_products($term, '', true, false, 20);
            }
        }

        if (empty($ids) && function_exists('wc_get_products')) {
            $ids = wc_get_products(array(
                'status' => array('publish', 'private'),
                'limit'  => 20,
                's'      => $term,
                'return' => 'ids',
            ));
        } elseif (empty($ids)) {
            $query = new WP_Query(array(
                'post_type'      => 'product',
                'post_status'    => array('publish', 'private'),
                'posts_per_page' => 20,
                's'              => $term,
                'fields'         => 'ids',
            ));
            $ids = $query->posts;
        }

        $results = array();
        foreach (array_unique(array_map('absint', (array) $ids)) as $product_id) {
            if (! $product_id) {
                continue;
            }

            $results[] = array(
                'id'   => $product_id,
                'text' => self::get_product_label($product_id),
            );
        }

        wp_send_json(array('results' => $results));
    }

    public static function handle_search_categories()
    {
        self::assert_admin_access();
        check_ajax_referer('onepaqucpro_cart_recovery_search', 'nonce');

        if (self::is_locked_mode()) {
            wp_send_json(array('results' => array()));
        }

        $term = sanitize_text_field(isset($_GET['term']) ? wp_unslash($_GET['term']) : '');
        if (strlen($term) < 3) {
            wp_send_json(array('results' => array()));
        }

        $terms = get_terms(array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
            'number'     => 20,
            'search'     => $term,
        ));

        if (is_wp_error($terms)) {
            wp_send_json(array('results' => array()));
        }

        $results = array();
        foreach ($terms as $term_row) {
            $results[] = array(
                'id'   => (int) $term_row->term_id,
                'text' => self::get_category_label($term_row->term_id),
            );
        }

        wp_send_json(array('results' => $results));
    }

    private static function render_notice()
    {
        $notice = isset($_GET['cr_notice']) ? sanitize_key(wp_unslash($_GET['cr_notice'])) : '';
        if (! $notice) {
            return;
        }

        $messages = array(
            'settings_saved'   => array('success', __('Cart recovery settings saved.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'templates_saved'  => array('success', __('Email templates updated.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'template_saved'   => array('success', __('Email template saved.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'template_duplicated' => array('success', __('Email template duplicated.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'template_deleted' => array('success', __('Email template deleted.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'status_updated'   => array('success', __('Cart status updated.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'cart_saved'       => array('success', __('Cart notes and tags saved.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'queue_ran'        => array('success', __('Recovery queue executed.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'cleanup_ran'      => array('success', __('Recovery cleanup completed.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'privacy_ran'      => array('success', __('Privacy action completed.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'test_sent'        => array('success', __('Test email sent.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'test_failed'      => array('error', __('The test email could not be sent.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'email_action_done' => array('success', __('Email activity action completed.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'email_actions_done' => array('success', __('Selected email activity actions completed.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'email_action_failed' => array('error', __('The email activity action could not be completed.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'nothing_selected' => array('warning', __('Select at least one row before applying the action.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'no_pending_email' => array('warning', __('All enabled recovery emails have already been sent for this cart.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'no_email_history' => array('warning', __('This cart has no recovery email history to resend.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'invalid_cart'     => array('error', __('The selected cart could not be found.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'invalid_email_activity' => array('error', __('The selected email activity could not be found.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'invalid_template' => array('error', __('The selected email template could not be found.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'service_unavailable' => array('error', __('Cart recovery actions are temporarily unavailable.', 'one-page-quick-checkout-for-woocommerce-pro')),
            'pro_required'     => array('warning', __('This cart recovery feature is available in Pro with an active license.', 'one-page-quick-checkout-for-woocommerce-pro')),
        );

        if (! isset($messages[$notice])) {
            return;
        }

        list($type, $message) = $messages[$notice];
    ?>
        <div class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php
    }

    private static function render_carts_tab($context)
    {
        $filters = $context['filters'];
        $summary = $context['summary'];
        $items   = $context['items'];
        $pagination = $context['pagination'];
        $template_options = self::get_template_name_options();
        $visible_columns = self::get_screen_option_visible_columns('carts');
    ?>
        <section class="onepaqucpro-cr-section">
            <div class="onepaqucpro-cr-stat-grid">
                <div class="onepaqucpro-cr-stat-card is-showing">
                    <span class="dashicons dashicons-cart"></span>
                    <div>
                        <small><?php esc_html_e('Showing', 'one-page-quick-checkout-for-woocommerce-pro'); ?></small>
                        <strong><?php echo esc_html(number_format_i18n($summary['count'])); ?></strong>
                        <?php echo wp_kses_post(self::render_metric_delta(isset($summary['deltas']['count']) ? $summary['deltas']['count'] : array(), 'onepaqucpro-cr-kpi-card__delta onepaqucpro-cr-stat-card__delta')); ?>
                    </div>
                </div>
                <div class="onepaqucpro-cr-stat-card is-total-value">
                    <span class="dashicons dashicons-money-alt"></span>
                    <div>
                        <small><?php esc_html_e('Total Value', 'one-page-quick-checkout-for-woocommerce-pro'); ?></small>
                        <strong><?php echo wp_kses_post(self::format_currency($summary['total_value'])); ?></strong>
                        <?php echo wp_kses_post(self::render_metric_delta(isset($summary['deltas']['total_value']) ? $summary['deltas']['total_value'] : array(), 'onepaqucpro-cr-kpi-card__delta onepaqucpro-cr-stat-card__delta')); ?>
                    </div>
                </div>
                <div class="onepaqucpro-cr-stat-card is-average">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <div>
                        <small><?php esc_html_e('Average', 'one-page-quick-checkout-for-woocommerce-pro'); ?></small>
                        <strong><?php echo wp_kses_post(self::format_currency($summary['average_value'])); ?></strong>
                        <?php echo wp_kses_post(self::render_metric_delta(isset($summary['deltas']['average_value']) ? $summary['deltas']['average_value'] : array(), 'onepaqucpro-cr-kpi-card__delta onepaqucpro-cr-stat-card__delta')); ?>
                    </div>
                </div>
                <div class="onepaqucpro-cr-stat-card is-recoverable">
                    <span class="dashicons dashicons-chart-line"></span>
                    <div>
                        <small><?php esc_html_e('Recoverable Value', 'one-page-quick-checkout-for-woocommerce-pro'); ?></small>
                        <strong><?php echo wp_kses_post(self::format_currency($summary['recoverable_value'])); ?></strong>
                        <?php echo wp_kses_post(self::render_metric_delta(isset($summary['deltas']['recoverable_value']) ? $summary['deltas']['recoverable_value'] : array(), 'onepaqucpro-cr-kpi-card__delta onepaqucpro-cr-stat-card__delta')); ?>
                    </div>
                </div>
            </div>

            <form method="get" class="onepaqucpro-cr-toolbar onepaqucpro-cr-toolbar--with-quick-select plugincy_card">
                <input type="hidden" name="page" value="<?php echo esc_attr(self::PAGE_SLUG); ?>">
                <input type="hidden" name="tab" value="carts">
                <div class="onepaqucpro-cr-toolbar__filters">
                    <label class="onepaqucpro-cr-filter-field onepaqucpro-cr-filter-field--wide">
                        <span><?php esc_html_e('Search', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <input type="search" name="cr_cart_search" value="<?php echo esc_attr($filters['search']); ?>" placeholder="<?php esc_attr_e('Search carts...', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                    </label>
                    <label class="onepaqucpro-cr-filter-field">
                        <span><?php esc_html_e('Status', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <select name="cr_cart_status">
                            <option value="all" <?php selected($filters['status'], 'all'); ?>><?php esc_html_e('All', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="active" <?php selected($filters['status'], 'active'); ?>><?php esc_html_e('Active', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="abandoned" <?php selected($filters['status'], 'abandoned'); ?>><?php esc_html_e('Abandoned', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="recovered" <?php selected($filters['status'], 'recovered'); ?>><?php esc_html_e('Recovered', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="unsubscribed" <?php selected($filters['status'], 'unsubscribed'); ?>><?php esc_html_e('Unsubscribed', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="ignored" <?php selected($filters['status'], 'ignored'); ?>><?php esc_html_e('Ignored', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="archived" <?php selected($filters['status'], 'archived'); ?>><?php esc_html_e('Archived', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                        </select>
                    </label>
                    <label class="onepaqucpro-cr-filter-field">
                        <span><?php esc_html_e('Customer', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <select name="cr_cart_customer_type">
                            <option value="all" <?php selected($filters['customer_type'], 'all'); ?>><?php esc_html_e('All Customers', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="guest" <?php selected($filters['customer_type'], 'guest'); ?>><?php esc_html_e('Guests', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="registered" <?php selected($filters['customer_type'], 'registered'); ?>><?php esc_html_e('Registered', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                        </select>
                    </label>
                    <label class="onepaqucpro-cr-filter-field">
                        <span><?php esc_html_e('Device', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <select name="cr_cart_device">
                            <option value="all" <?php selected($filters['device'], 'all'); ?>><?php esc_html_e('All Devices', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="desktop" <?php selected($filters['device'], 'desktop'); ?>><?php esc_html_e('Desktop', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="mobile" <?php selected($filters['device'], 'mobile'); ?>><?php esc_html_e('Mobile', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="tablet" <?php selected($filters['device'], 'tablet'); ?>><?php esc_html_e('Tablet', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                        </select>
                    </label>
                    <label class="onepaqucpro-cr-filter-field">
                        <span><?php esc_html_e('Source', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <select name="cr_cart_source">
                            <option value="all" <?php selected($filters['source'], 'all'); ?>><?php esc_html_e('All Sources', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="email" <?php selected($filters['source'], 'email'); ?>><?php esc_html_e('Email Restore', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="site_revisit" <?php selected($filters['source'], 'site_revisit'); ?>><?php esc_html_e('Site Revisit', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="admin" <?php selected($filters['source'], 'admin'); ?>><?php esc_html_e('Admin', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                        </select>
                    </label>
                    <label class="onepaqucpro-cr-filter-field">
                        <span><?php esc_html_e('Template', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <select name="cr_cart_template">
                            <option value=""><?php esc_html_e('All Templates', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <?php foreach ($template_options as $template_name) : ?>
                                <option value="<?php echo esc_attr($template_name); ?>" <?php selected($filters['template'], $template_name); ?>><?php echo esc_html($template_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="onepaqucpro-cr-filter-field">
                        <span><?php esc_html_e('Date From', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <input type="date" name="cr_cart_from" value="<?php echo esc_attr($filters['from']); ?>">
                    </label>
                    <label class="onepaqucpro-cr-filter-field">
                        <span><?php esc_html_e('Date To', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <input type="date" name="cr_cart_to" value="<?php echo esc_attr($filters['to']); ?>">
                    </label>
                    <label class="onepaqucpro-cr-filter-field onepaqucpro-cr-filter-field--compact">
                        <span><?php esc_html_e('Min Value', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <input type="number" step="0.01" name="cr_cart_min" value="<?php echo esc_attr($filters['min']); ?>">
                    </label>
                    <label class="onepaqucpro-cr-filter-field onepaqucpro-cr-filter-field--compact">
                        <span><?php esc_html_e('Max Value', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <input type="number" step="0.01" name="cr_cart_max" value="<?php echo esc_attr($filters['max']); ?>" placeholder="<?php esc_attr_e('No limit', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                    </label>
                    <?php self::render_quick_select('cr_cart_from', 'cr_cart_to', array('tab' => 'carts'), array(
                        'cr_cart_status',
                        'cr_cart_customer_type',
                        'cr_cart_device',
                        'cr_cart_template',
                        'cr_cart_source',
                        'cr_cart_min',
                        'cr_cart_max',
                        'cr_cart_search',
                        'cr_cart_sort',
                        'cr_cart_order',
                    ), $filters['from'], $filters['to']); ?>
                    <div class="onepaqucpro-cr-filter-actions">
                        <button type="submit" class="button button-primary"><?php esc_html_e('Apply', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
                        <a class="button button-secondary" href="<?php echo esc_url(self::get_page_url(array('tab' => 'carts'))); ?>"><?php esc_html_e('Reset', 'one-page-quick-checkout-for-woocommerce-pro'); ?></a>
                    </div>
                </div>
            </form>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="onepaqucpro-cr-table-wrap">
                <input type="hidden" name="redirect_tab" value="carts">
                <?php wp_nonce_field('onepaqucpro_cart_recovery_bulk_action'); ?>
                <?php self::render_preserved_query_fields(array(
                    'cr_cart_status',
                    'cr_cart_customer_type',
                    'cr_cart_device',
                    'cr_cart_template',
                    'cr_cart_source',
                    'cr_cart_from',
                    'cr_cart_to',
                    'cr_cart_min',
                    'cr_cart_max',
                    'cr_cart_search',
                    'cr_cart_sort',
                    'cr_cart_order',
                    'cr_cart_page',
                )); ?>

                <div class="onepaqucpro-cr-table-actions">
                    <div class="onepaqucpro-cr-bulk">
                        <?php if (self::is_locked_mode()) : ?>
                            <span class="onepaqucpro-cr-action-disabled"><?php echo wp_kses_post(self::render_locked_value('Bulk actions')); ?></span>
                        <?php else : ?>
                            <select name="bulk_action">
                                <option value=""><?php esc_html_e('Bulk actions', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                                <option value="mark_recovered"><?php esc_html_e('Mark Recovered', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                                <option value="mark_abandoned"><?php esc_html_e('Mark Abandoned', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                                <option value="archive"><?php esc_html_e('Archive', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                                <option value="ignore"><?php esc_html_e('Ignore', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                                <option value="activate"><?php esc_html_e('Reactivate', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                                <option value="delete"><?php esc_html_e('Delete', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            </select>
                            <button type="submit" class="button button-secondary" name="action" value="onepaqucpro_cart_recovery_bulk_action"><?php esc_html_e('Apply', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
                        <?php endif; ?>
                    </div>
                    <div class="onepaqucpro-cr-table-tools">
                        <button type="submit" class="button button-secondary" name="action" value="onepaqucpro_cart_recovery_export_carts"><?php esc_html_e('Export CSV', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
                        <?php wp_nonce_field('onepaqucpro_cart_recovery_export_carts', 'onepaqucpro_cart_recovery_export_carts_nonce'); ?>
                        <span class="onepaqucpro-cr-table-count">
                            <?php
                            printf(
                                esc_html(_n('%s cart', '%s carts', $summary['count'], 'one-page-quick-checkout-for-woocommerce-pro')),
                                esc_html(number_format_i18n($summary['count']))
                            );
                            ?>
                        </span>
                    </div>
                </div>

                <?php if (empty($items)) : ?>
                    <div class="onepaqucpro-cr-empty">
                        <span class="dashicons dashicons-search"></span>
                        <h3><?php esc_html_e('No carts matched the current filters.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                        <p><?php esc_html_e('Try widening the date range or clearing the search terms.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                    </div>
                <?php else : ?>
                    <table class="widefat striped onepaqucpro-cr-table onepaqucpro-cr-table--carts">
                        <thead>
                            <tr>
                                <td class="check-column"><input type="checkbox" data-cr-check-all></td>
                                <th class="column-primary"><?php esc_html_e('Cart', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <?php if (in_array('cart_total', $visible_columns, true)) : ?>
                                    <th class="onepaqucpro-cr-sortable onepaqucpro-cr-col--money"><?php echo wp_kses_post(self::get_sort_link('carts', 'cart_total', __('Cart Total', 'one-page-quick-checkout-for-woocommerce-pro'), $filters['sort'], $filters['order'])); ?></th>
                                <?php endif; ?>
                                <?php if (in_array('status', $visible_columns, true)) : ?>
                                    <th class="onepaqucpro-cr-col--status"><?php esc_html_e('Status', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <?php endif; ?>
                                <?php if (in_array('last_activity', $visible_columns, true)) : ?>
                                    <th class="onepaqucpro-cr-sortable onepaqucpro-cr-col--time"><?php echo wp_kses_post(self::get_sort_link('carts', 'last_activity', __('Last Activity', 'one-page-quick-checkout-for-woocommerce-pro'), $filters['sort'], $filters['order'])); ?></th>
                                <?php endif; ?>
                                <?php if (in_array('recovery', $visible_columns, true)) : ?>
                                    <th class="onepaqucpro-cr-sortable onepaqucpro-cr-col--time"><?php echo wp_kses_post(self::get_sort_link('carts', 'recovered_at', __('Recovery', 'one-page-quick-checkout-for-woocommerce-pro'), $filters['sort'], $filters['order'])); ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $cart) : ?>
                                <?php $action_state = self::get_cart_action_state($cart); ?>
                                <tr>
                                    <th scope="row" class="check-column">
                                        <input type="checkbox" name="cart_ids[]" value="<?php echo esc_attr($cart['id']); ?>">
                                    </th>
                                    <td class="column-primary onepaqucpro-cr-cart-primary">
                                        <?php
                                        $avatar = '';
                                        if (! self::is_locked_mode() && function_exists('get_avatar')) {
                                            $avatar = get_avatar($cart['email'], 32, '', $cart['customer_name'], array('class' => 'onepaqucpro-cr-avatar'));
                                        }
                                        ?>
                                        <?php if ($avatar) : ?>
                                            <span class="onepaqucpro-cr-avatar-wrap"><?php echo wp_kses_post($avatar); ?></span>
                                        <?php endif; ?>

                                        <div class="onepaqucpro-cr-cart-primary__content">
                                            <strong class="onepaqucpro-cr-cart-primary__title">
                                                <?php echo esc_html($cart['customer_name']); ?>
                                                <span class="onepaqucpro-cr-cart-primary__id"><?php echo esc_html('#' . $cart['id']); ?></span>
                                            </strong>
                                            <div class="onepaqucpro-cr-cart-primary__meta">
                                                <span><?php echo wp_kses_post(self::render_contact_value($cart['email'] ? $cart['email'] : __('No email', 'one-page-quick-checkout-for-woocommerce-pro'), '-', 'Email')); ?></span>
                                                <?php if (! self::is_locked_mode() && ! empty($cart['customer_phone'])) : ?>
                                                    <span class="onepaqucpro-cr-separator">&bull;</span>
                                                    <span><?php echo esc_html($cart['customer_phone']); ?></span>
                                                <?php endif; ?>
                                                <span class="onepaqucpro-cr-separator">&bull;</span>
                                                <span><?php echo esc_html(number_format_i18n($cart['item_count'])); ?> <?php echo esc_html(_n('item', 'items', $cart['item_count'], 'one-page-quick-checkout-for-woocommerce-pro')); ?></span>
                                            </div>
                                            <?php if (! self::is_locked_mode() && ! empty($cart['customer_company'])) : ?>
                                                <div class="onepaqucpro-cr-cart-primary__sub"><?php echo esc_html($cart['customer_company']); ?></div>
                                            <?php endif; ?>
                                            <?php if (! empty($cart['product_context'])) : ?>
                                                <div class="onepaqucpro-cr-cart-primary__sub"><?php echo esc_html($cart['product_context']); ?></div>
                                            <?php endif; ?>
                                            <?php if (! empty($cart['coupon_codes'])) : ?>
                                                <div class="onepaqucpro-cr-cart-primary__sub"><?php echo esc_html__('Coupons:', 'one-page-quick-checkout-for-woocommerce-pro') . ' ' . esc_html(implode(', ', $cart['coupon_codes'])); ?></div>
                                            <?php endif; ?>
                                            <?php if (! empty($cart['notes']) && ! empty($cart['tags'])) : ?>
                                                <div class="onepaqucpro-cr-cart-note" tabindex="0" title="<?php echo esc_attr($cart['notes']); ?>" data-cr-note-tooltip="<?php echo esc_attr($cart['notes']); ?>">
                                                    <span class="dashicons dashicons-edit-page" aria-hidden="true"></span>
                                                    <span><?php echo esc_html(implode(', ', $cart['tags'])); ?></span>
                                                </div>
                                            <?php endif; ?>

                                            <div class="row-actions">
                                                <button type="button" class="button-link onepaqucpro-cr-open-modal" data-template="onepaqucpro-cr-detail-<?php echo esc_attr($cart['id']); ?>">
                                                    <?php esc_html_e('View Details', 'one-page-quick-checkout-for-woocommerce-pro'); ?>
                                                </button>
                                                <?php if (self::is_locked_mode()) : ?>
                                                    <span class="onepaqucpro-cr-separator">|</span>
                                                    <span class="onepaqucpro-cr-action-disabled"><?php echo wp_kses_post(self::render_locked_value('Cart actions')); ?></span>
                                                <?php else : ?>
                                                    <span class="onepaqucpro-cr-separator">|</span>
                                                    <?php if ($action_state['can_send_next']) : ?>
                                                        <a href="<?php echo esc_url(self::get_cart_action_url($cart['id'], 'send_next')); ?>"><?php esc_html_e('Send Next', 'one-page-quick-checkout-for-woocommerce-pro'); ?></a>
                                                    <?php else : ?>
                                                        <span class="onepaqucpro-cr-action-disabled"><?php esc_html_e('Send Next', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                                    <?php endif; ?>
                                                    <span class="onepaqucpro-cr-separator">|</span>
                                                    <?php if ($action_state['can_resend_last']) : ?>
                                                        <a href="<?php echo esc_url(self::get_cart_action_url($cart['id'], 'resend_last')); ?>"><?php esc_html_e('Resend Last', 'one-page-quick-checkout-for-woocommerce-pro'); ?></a>
                                                    <?php else : ?>
                                                        <span class="onepaqucpro-cr-action-disabled"><?php esc_html_e('Resend Last', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                                    <?php endif; ?>
                                                    <span class="onepaqucpro-cr-separator">|</span>
                                                    <a href="<?php echo esc_url(self::get_cart_action_url($cart['id'], 'recovered' === $cart['status'] ? 'mark_abandoned' : 'mark_recovered')); ?>">
                                                        <?php echo 'recovered' === $cart['status'] ? esc_html__('Mark Abandoned', 'one-page-quick-checkout-for-woocommerce-pro') : esc_html__('Mark Recovered', 'one-page-quick-checkout-for-woocommerce-pro'); ?>
                                                    </a>
                                                    <?php if ($cart['recovered_order_id']) : ?>
                                                        <span class="onepaqucpro-cr-separator">|</span>
                                                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $cart['recovered_order_id'] . '&action=edit')); ?>"><?php esc_html_e('View Order', 'one-page-quick-checkout-for-woocommerce-pro'); ?></a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <button type="button" class="toggle-row"><span class="screen-reader-text"><?php esc_html_e('Show more details', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span></button>
                                    </td>
                                    <?php if (in_array('cart_total', $visible_columns, true)) : ?>
                                        <td class="onepaqucpro-cr-col--money">
                                            <strong><?php echo wp_kses_post(self::format_currency($cart['cart_total'], $cart['currency'])); ?></strong>
                                            <span class="onepaqucpro-cr-meta"><?php echo wp_kses_post(self::is_locked_mode() ? self::render_locked_value('Customer type') : esc_html(ucfirst($cart['customer_type']) . ' / ' . ucfirst($cart['device_type']))); ?></span>
                                        </td>
                                    <?php endif; ?>
                                    <?php if (in_array('status', $visible_columns, true)) : ?>
                                        <td class="onepaqucpro-cr-col--status">
                                            <?php echo wp_kses_post(self::render_cart_status_badges($cart)); ?>
                                            <span class="onepaqucpro-cr-meta"><?php echo wp_kses_post(self::is_locked_mode() ? self::render_locked_value('Browser') : esc_html($cart['browser'])); ?></span>
                                        </td>
                                    <?php endif; ?>
                                    <?php if (in_array('last_activity', $visible_columns, true)) : ?>
                                        <td class="onepaqucpro-cr-col--time">
                                            <span class="onepaqucpro-cr-metric"><?php echo esc_html(self::get_relative_time($cart['last_activity_at'])); ?></span>
                                            <span class="onepaqucpro-cr-meta"><?php echo esc_html(self::format_datetime($cart['last_activity_at'])); ?></span>
                                            <span class="onepaqucpro-cr-meta"><?php echo esc_html(sprintf(__('Emails: %d', 'one-page-quick-checkout-for-woocommerce-pro'), $cart['emails_sent'])); ?></span>
                                        </td>
                                    <?php endif; ?>
                                    <?php if (in_array('recovery', $visible_columns, true)) : ?>
                                        <td class="onepaqucpro-cr-col--time">
                                            <?php if ($cart['recovered_at']) : ?>
                                                <span class="onepaqucpro-cr-metric"><?php echo esc_html(self::format_datetime($cart['recovered_at'])); ?></span>
                                                <span class="onepaqucpro-cr-meta"><?php echo esc_html(self::format_duration($cart['time_to_recovery_seconds'])); ?></span>
                                            <?php else : ?>
                                                <span class="onepaqucpro-cr-metric"><?php echo esc_html(self::format_datetime($cart['abandoned_at'])); ?></span>
                                                <span class="onepaqucpro-cr-meta"><?php echo esc_html($cart['recovery_source'] ? ucfirst(str_replace('_', ' ', $cart['recovery_source'])) : __('Waiting', 'one-page-quick-checkout-for-woocommerce-pro')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php self::render_pagination('carts', $pagination, array(
                        'cr_cart_status',
                        'cr_cart_customer_type',
                        'cr_cart_device',
                        'cr_cart_template',
                        'cr_cart_source',
                        'cr_cart_from',
                        'cr_cart_to',
                        'cr_cart_min',
                        'cr_cart_max',
                        'cr_cart_search',
                        'cr_cart_sort',
                        'cr_cart_order',
                    )); ?>
                <?php endif; ?>
            </form>
        </section>
    <?php
    }

    private static function render_analytics_tab($context)
    {
        $range          = $context['range'];
        $metrics        = $context['metrics'];
        $previous       = $context['previous_metrics'];
        $metric_configs = array(
            'tracked_carts'     => array('label' => __('Carts Tracked', 'one-page-quick-checkout-for-woocommerce-pro'), 'type' => 'number'),
            'abandoned'         => array('label' => __('Abandoned', 'one-page-quick-checkout-for-woocommerce-pro'), 'type' => 'number'),
            'recovered'         => array('label' => __('Recovered', 'one-page-quick-checkout-for-woocommerce-pro'), 'type' => 'number'),
            'recovered_revenue' => array('label' => __('Recovered Revenue', 'one-page-quick-checkout-for-woocommerce-pro'), 'type' => 'currency'),
            'recoverable_value' => array('label' => __('Recoverable Value', 'one-page-quick-checkout-for-woocommerce-pro'), 'type' => 'currency'),
            'recovery_rate'     => array('label' => __('Recovery Rate', 'one-page-quick-checkout-for-woocommerce-pro'), 'type' => 'percent'),
            'emails_sent'       => array('label' => __('Emails Sent', 'one-page-quick-checkout-for-woocommerce-pro'), 'type' => 'number'),
            'open_rate'         => array('label' => __('Open Rate', 'one-page-quick-checkout-for-woocommerce-pro'), 'type' => 'percent'),
            'click_rate'        => array('label' => __('Click Rate', 'one-page-quick-checkout-for-woocommerce-pro'), 'type' => 'percent'),
            'unsubscribe_rate'  => array('label' => __('Unsubscribe Rate', 'one-page-quick-checkout-for-woocommerce-pro'), 'type' => 'percent'),
        );
    ?>
        <section class="onepaqucpro-cr-section">
            <?php self::render_range_toolbar('analytics', $range); ?>

            <div class="onepaqucpro-cr-kpi-grid">
                <?php foreach ($metric_configs as $key => $config) : ?>
                    <article class="onepaqucpro-cr-kpi-card">
                        <span class="onepaqucpro-cr-kpi-card__label"><?php echo esc_html($config['label']); ?></span>
                        <strong><?php echo wp_kses_post(self::format_metric_value($metrics[$key], $config['type'])); ?></strong>
                        <?php echo wp_kses_post(self::render_metric_delta(self::get_analytics_delta_context($metrics[$key], isset($previous[$key]) ? $previous[$key] : null, $config['type'], $range))); ?>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="onepaqucpro-cr-chart-grid">
                <?php
                self::render_chart_card(
                    __('Recovered Revenue', 'one-page-quick-checkout-for-woocommerce-pro'),
                    __('Daily recovered revenue in the selected range.', 'one-page-quick-checkout-for-woocommerce-pro'),
                    array(
                        'type'   => 'line',
                        'labels' => $context['series']['labels'],
                        'values' => $context['series']['revenue'],
                        'color'  => '#5d87ff',
                        'format' => 'currency',
                    )
                );

                self::render_chart_card(
                    __('Emails Sent', 'one-page-quick-checkout-for-woocommerce-pro'),
                    __('Volume of recovery emails sent by day.', 'one-page-quick-checkout-for-woocommerce-pro'),
                    array(
                        'type'   => 'line',
                        'labels' => $context['series']['labels'],
                        'values' => $context['series']['sent'],
                        'color'  => '#1f9d8b',
                        'format' => 'number',
                    )
                );

                self::render_chart_card(
                    __('Emails Opened', 'one-page-quick-checkout-for-woocommerce-pro'),
                    __('Email opens recorded across the selected period.', 'one-page-quick-checkout-for-woocommerce-pro'),
                    array(
                        'type'   => 'line',
                        'labels' => $context['series']['labels'],
                        'values' => $context['series']['opened'],
                        'color'  => '#f29fb7',
                        'format' => 'number',
                    )
                );

                self::render_chart_card(
                    __('Emails Clicked', 'one-page-quick-checkout-for-woocommerce-pro'),
                    __('Clicks generated by recovery emails.', 'one-page-quick-checkout-for-woocommerce-pro'),
                    array(
                        'type'   => 'line',
                        'labels' => $context['series']['labels'],
                        'values' => $context['series']['clicked'],
                        'color'  => '#f4a51c',
                        'format' => 'number',
                    )
                );
                ?>
            </div>

            <div class="onepaqucpro-cr-kpi-grid">
                <article class="onepaqucpro-cr-kpi-card">
                    <span class="onepaqucpro-cr-kpi-card__label"><?php esc_html_e('Avg. To Abandon', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                    <strong><?php echo esc_html(self::format_duration($context['latency']['avg_to_abandon'])); ?></strong>
                </article>
                <article class="onepaqucpro-cr-kpi-card">
                    <span class="onepaqucpro-cr-kpi-card__label"><?php esc_html_e('Avg. To Restore', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                    <strong><?php echo esc_html(self::format_duration($context['latency']['avg_to_restore'])); ?></strong>
                </article>
                <article class="onepaqucpro-cr-kpi-card">
                    <span class="onepaqucpro-cr-kpi-card__label"><?php esc_html_e('Avg. To Recover', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                    <strong><?php echo esc_html(self::format_duration($context['latency']['avg_to_recover'])); ?></strong>
                </article>
            </div>

            <div class="onepaqucpro-cr-chart-grid">
                <article class="plugincy_card onepaqucpro-cr-chart-card">
                    <div class="onepaqucpro-cr-card-heading">
                        <div>
                            <h3><?php esc_html_e('Template Performance', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                            <p><?php esc_html_e('See which recovery emails generate the most engagement and revenue.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                        </div>
                    </div>
                    <table class="widefat striped onepaqucpro-cr-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Template', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <th><?php esc_html_e('Sent', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <th><?php esc_html_e('Opened', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <th><?php esc_html_e('Clicked', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <th><?php esc_html_e('Recovered', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <th><?php esc_html_e('Revenue', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($context['template_performance'] as $row) : ?>
                                <tr>
                                    <td><?php echo esc_html($row['name']); ?></td>
                                    <td><?php echo esc_html(number_format_i18n($row['sent'])); ?></td>
                                    <td><?php echo esc_html(number_format_i18n($row['opened'])); ?></td>
                                    <td><?php echo esc_html(number_format_i18n($row['clicked'])); ?></td>
                                    <td><?php echo esc_html(number_format_i18n($row['recovered'])); ?></td>
                                    <td><?php echo wp_kses_post(self::format_currency($row['revenue'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </article>

                <article class="plugincy_card onepaqucpro-cr-chart-card">
                    <div class="onepaqucpro-cr-card-heading">
                        <div>
                            <h3><?php esc_html_e('Product Recovery', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                            <p><?php esc_html_e('Top products by abandoned carts and recovered revenue.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                        </div>
                    </div>
                    <table class="widefat striped onepaqucpro-cr-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Product', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <th><?php esc_html_e('Abandoned', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <th><?php esc_html_e('Recovered', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <th><?php esc_html_e('Revenue', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($context['product_performance'] as $row) : ?>
                                <tr>
                                    <td><?php echo esc_html($row['name']); ?></td>
                                    <td><?php echo esc_html(number_format_i18n($row['abandoned'])); ?></td>
                                    <td><?php echo esc_html(number_format_i18n($row['recovered'])); ?></td>
                                    <td><?php echo wp_kses_post(self::format_currency($row['revenue'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </article>

                <article class="plugincy_card onepaqucpro-cr-chart-card">
                    <div class="onepaqucpro-cr-card-heading">
                        <div>
                            <h3><?php esc_html_e('Segment Performance', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                            <p><?php esc_html_e('Compare recovery by device and customer type.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                        </div>
                    </div>
                    <table class="widefat striped onepaqucpro-cr-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Segment', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <th><?php esc_html_e('Abandoned', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <th><?php esc_html_e('Recovered', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <th><?php esc_html_e('Revenue', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array('device', 'customer_type') as $segment_type) : ?>
                                <?php foreach ($context['segment_performance'][$segment_type] as $row) : ?>
                                    <tr>
                                        <td><?php echo esc_html(ucfirst($segment_type) . ': ' . $row['label']); ?></td>
                                        <td><?php echo esc_html(number_format_i18n($row['abandoned'])); ?></td>
                                        <td><?php echo esc_html(number_format_i18n($row['recovered'])); ?></td>
                                        <td><?php echo wp_kses_post(self::format_currency($row['revenue'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </article>
            </div>
        </section>
    <?php
    }

    private static function render_journey_tab($context)
    {
        $range       = $context['range'];
        $funnel      = $context['funnel'];
        $stage_keys  = array('created', 'abandoned', 'emailed', 'opened', 'clicked', 'restored', 'recovered');
        $dropoff_labels = array();
        $dropoff_values = array();

        foreach ($funnel['stages'] as $index => $stage) {
            if (0 === $index) {
                continue;
            }

            $dropoff_labels[] = $stage['label'];
            $dropoff_values[] = max(0, isset($stage['drop_off']) ? absint($stage['drop_off']) : 0);
        }
    ?>
        <section class="onepaqucpro-cr-section">
            <?php self::render_range_toolbar('journey', $range); ?>

            <div class="plugincy_card onepaqucpro-cr-feature-card">
                <div class="onepaqucpro-cr-card-heading">
                    <div>
                        <h2><?php esc_html_e('Funnel Performance', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h2>
                        <p><?php esc_html_e('Track how shoppers move from cart creation to recovery, and where the biggest drop-offs happen.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                    </div>
                </div>

                <div class="onepaqucpro-cr-journey-chart-grid">
                    <?php
                    self::render_chart_card(
                        sprintf(
                            /* translators: %s: date range label. */
                            __('Customer Journey Funnel - %s', 'one-page-quick-checkout-for-woocommerce-pro'),
                            $range['label']
                        ),
                        __('Unique carts that reached each stage of the recovery journey.', 'one-page-quick-checkout-for-woocommerce-pro'),
                        array(
                            'type'   => 'bar',
                            'labels' => wp_list_pluck($funnel['stages'], 'label'),
                            'values' => wp_list_pluck($funnel['stages'], 'count'),
                            'colors' => array('#317ca8', '#279d7f', '#ddb64a', '#f4a51c', '#e16262', '#a06ad7', '#8c98a5'),
                            'format' => 'number',
                        ),
                        true
                    );

                    self::render_chart_card(
                        __('Stage Drop-Off', 'one-page-quick-checkout-for-woocommerce-pro'),
                        __('Carts lost before reaching each next recovery milestone.', 'one-page-quick-checkout-for-woocommerce-pro'),
                        array(
                            'type'   => 'bar',
                            'labels' => $dropoff_labels,
                            'values' => $dropoff_values,
                            'colors' => array('#e16262', '#f4a51c', '#ddb64a', '#a06ad7', '#317ca8', '#8c98a5'),
                            'format' => 'number',
                        ),
                        true
                    );
                    ?>
                </div>
                <div class="onepaqucpro-cr-stage-table-wrap">
                    <h3><?php esc_html_e('Funnel Stage Metrics', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                    <table class="widefat fixed striped onepaqucpro-cr-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Stage', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <th><?php esc_html_e('Unique Carts', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <th><?php esc_html_e('Conversion Rate', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <th><?php esc_html_e('Drop-Off', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($funnel['stages'] as $index => $stage) : ?>
                                <tr>
                                    <td><a href="<?php echo esc_url(self::get_page_url(array('tab' => 'journey', 'cr_period' => $range['period'], 'cr_compare' => $range['compare'], 'cr_date_from' => $range['from'], 'cr_date_to' => $range['to'], 'cr_journey_stage' => isset($stage_keys[$index]) ? $stage_keys[$index] : ''))); ?>"><?php echo esc_html($stage['label']); ?></a></td>
                                    <td><?php echo esc_html(number_format_i18n($stage['count'])); ?></td>
                                    <td><?php echo esc_html(self::format_percent($stage['conversion_rate'])); ?></td>
                                    <td><?php echo esc_html($stage['drop_off'] < 0 ? '-' : number_format_i18n($stage['drop_off'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="onepaqucpro-cr-chart-grid">
                    <article class="plugincy_card onepaqucpro-cr-chart-card">
                        <div class="onepaqucpro-cr-card-heading">
                            <div>
                                <h3><?php esc_html_e('Time Between Stages', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                <p><?php esc_html_e('Average elapsed time between the key recovery milestones.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                            </div>
                        </div>
                        <table class="widefat striped onepaqucpro-cr-table">
                            <tbody>
                                <?php foreach ($context['time_between'] as $row) : ?>
                                    <tr>
                                        <td><?php echo esc_html($row['label']); ?></td>
                                        <td><?php echo esc_html(self::format_duration($row['seconds'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </article>

                    <article class="plugincy_card onepaqucpro-cr-chart-card">
                        <div class="onepaqucpro-cr-card-heading">
                            <div>
                                <h3><?php esc_html_e('Restore Sources', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                <p><?php esc_html_e('How carts came back into the checkout flow.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                            </div>
                        </div>
                        <table class="widefat striped onepaqucpro-cr-table">
                            <tbody>
                                <?php foreach ($context['source_split'] as $label => $count) : ?>
                                    <tr>
                                        <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $label))); ?></td>
                                        <td><?php echo esc_html(number_format_i18n($count)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </article>
                </div>

                <div class="onepaqucpro-cr-chart-grid">
                    <article class="plugincy_card onepaqucpro-cr-chart-card">
                        <div class="onepaqucpro-cr-card-heading">
                            <div>
                                <h3><?php esc_html_e('Drop-Off Reasons', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                <p><?php esc_html_e('Operational blockers that prevent abandoned carts from converting.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                            </div>
                        </div>
                        <table class="widefat striped onepaqucpro-cr-table">
                            <tbody>
                                <?php foreach ($context['drop_off'] as $label => $count) : ?>
                                    <tr>
                                        <td><?php echo esc_html($label); ?></td>
                                        <td><?php echo esc_html(number_format_i18n($count)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </article>

                    <article class="plugincy_card onepaqucpro-cr-chart-card">
                        <div class="onepaqucpro-cr-card-heading">
                            <div>
                                <h3><?php esc_html_e('Cohorts', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                <p><?php esc_html_e('Recovery performance grouped by abandonment date.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                            </div>
                        </div>
                        <table class="widefat striped onepaqucpro-cr-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Date', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                    <th><?php esc_html_e('Abandoned', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                    <th><?php esc_html_e('Recovered', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                    <th><?php esc_html_e('Recovery Rate', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($context['cohorts'] as $cohort) : ?>
                                    <tr>
                                        <td><?php echo esc_html($cohort['date']); ?></td>
                                        <td><?php echo esc_html(number_format_i18n($cohort['abandoned'])); ?></td>
                                        <td><?php echo esc_html(number_format_i18n($cohort['recovered'])); ?></td>
                                        <td><?php echo esc_html(self::format_percent($cohort['abandoned'] ? ($cohort['recovered'] / $cohort['abandoned']) * 100 : 0)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </article>
                </div>

                <?php if (! empty($context['stage_drilldown']['stage'])) : ?>
                    <div class="onepaqucpro-cr-stage-table-wrap">
                        <h3><?php echo esc_html(sprintf(__('Stage Drilldown: %s', 'one-page-quick-checkout-for-woocommerce-pro'), ucfirst($context['stage_drilldown']['stage']))); ?></h3>
                        <table class="widefat striped onepaqucpro-cr-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Cart', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                    <th><?php esc_html_e('Status', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                    <th><?php esc_html_e('Total', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                    <th><?php esc_html_e('Last Activity', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($context['stage_drilldown']['items'] as $cart) : ?>
                                    <tr>
                                        <td><button type="button" class="button-link onepaqucpro-cr-open-modal" data-template="onepaqucpro-cr-detail-<?php echo esc_attr($cart['id']); ?>"><?php echo esc_html($cart['customer_name'] . ' #' . $cart['id']); ?></button></td>
                                        <td><?php echo wp_kses_post(self::render_cart_status_badges($cart)); ?></td>
                                        <td><?php echo wp_kses_post(self::format_currency($cart['cart_total'], $cart['currency'])); ?></td>
                                        <td><?php echo esc_html(self::format_datetime($cart['last_activity_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php
    }

    private static function render_activity_tab($context)
    {
        $filters = $context['filters'];
        $items   = $context['items'];
        $pagination = $context['pagination'];
        $template_options = self::get_template_name_options();
        $visible_columns = self::get_screen_option_visible_columns('activity');
    ?>
        <section class="onepaqucpro-cr-section">
            <form method="get" class="onepaqucpro-cr-toolbar onepaqucpro-cr-toolbar--with-quick-select plugincy_card">
                <input type="hidden" name="page" value="<?php echo esc_attr(self::PAGE_SLUG); ?>">
                <input type="hidden" name="tab" value="activity">
                <div class="onepaqucpro-cr-toolbar__filters">
                    <label class="onepaqucpro-cr-filter-field onepaqucpro-cr-filter-field--wide">
                        <span><?php esc_html_e('Search', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <input type="search" name="cr_activity_search" value="<?php echo esc_attr($filters['search']); ?>" placeholder="<?php esc_attr_e('Search activity...', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                    </label>
                    <label class="onepaqucpro-cr-filter-field">
                        <span><?php esc_html_e('Status', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <select name="cr_activity_status">
                            <option value="all" <?php selected($filters['status'], 'all'); ?>><?php esc_html_e('All Statuses', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="sent" <?php selected($filters['status'], 'sent'); ?>><?php esc_html_e('Sent', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="opened" <?php selected($filters['status'], 'opened'); ?>><?php esc_html_e('Opened', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="clicked" <?php selected($filters['status'], 'clicked'); ?>><?php esc_html_e('Clicked', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="failed" <?php selected($filters['status'], 'failed'); ?>><?php esc_html_e('Failed', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="restored" <?php selected($filters['status'], 'restored'); ?>><?php esc_html_e('Restored', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="recovered" <?php selected($filters['status'], 'recovered'); ?>><?php esc_html_e('Recovered', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="abandoned" <?php selected($filters['status'], 'abandoned'); ?>><?php esc_html_e('Abandoned', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="unsubscribed" <?php selected($filters['status'], 'unsubscribed'); ?>><?php esc_html_e('Unsubscribed', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                        </select>
                    </label>
                    <label class="onepaqucpro-cr-filter-field">
                        <span><?php esc_html_e('Type', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <select name="cr_activity_type">
                            <option value="all" <?php selected($filters['type'], 'all'); ?>><?php esc_html_e('All Types', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="email" <?php selected($filters['type'], 'email'); ?>><?php esc_html_e('Emails', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="event" <?php selected($filters['type'], 'event'); ?>><?php esc_html_e('Cart Events', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                        </select>
                    </label>
                    <label class="onepaqucpro-cr-filter-field">
                        <span><?php esc_html_e('Template', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <select name="cr_activity_template">
                            <option value=""><?php esc_html_e('All Templates', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <?php foreach ($template_options as $template_name) : ?>
                                <option value="<?php echo esc_attr($template_name); ?>" <?php selected($filters['template'], $template_name); ?>><?php echo esc_html($template_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="onepaqucpro-cr-filter-field">
                        <span><?php esc_html_e('Date From', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <input type="date" name="cr_activity_from" value="<?php echo esc_attr($filters['from']); ?>">
                    </label>
                    <label class="onepaqucpro-cr-filter-field">
                        <span><?php esc_html_e('Date To', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <input type="date" name="cr_activity_to" value="<?php echo esc_attr($filters['to']); ?>">
                    </label>
                    <label class="onepaqucpro-cr-filter-field onepaqucpro-cr-filter-field--compact">
                        <span><?php esc_html_e('Cart ID', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <input type="text" name="cr_activity_cart" value="<?php echo esc_attr($filters['cart']); ?>" placeholder="<?php esc_attr_e('Cart ID', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                    </label>
                    <label class="onepaqucpro-cr-filter-field">
                        <span><?php esc_html_e('Recipient', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <input type="text" name="cr_activity_recipient" value="<?php echo esc_attr($filters['recipient']); ?>" placeholder="<?php esc_attr_e('Recipient email', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                    </label>
                    <?php self::render_quick_select('cr_activity_from', 'cr_activity_to', array('tab' => 'activity'), array(
                        'cr_activity_status',
                        'cr_activity_type',
                        'cr_activity_cart',
                        'cr_activity_template',
                        'cr_activity_recipient',
                        'cr_activity_search',
                        'cr_activity_sort',
                        'cr_activity_order',
                    ), $filters['from'], $filters['to']); ?>
                    <div class="onepaqucpro-cr-filter-actions">
                        <button type="submit" class="button button-primary"><?php esc_html_e('Apply', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
                        <a class="button button-secondary" href="<?php echo esc_url(self::get_page_url(array('tab' => 'activity'))); ?>"><?php esc_html_e('Reset', 'one-page-quick-checkout-for-woocommerce-pro'); ?></a>
                    </div>
                </div>
            </form>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="onepaqucpro-cr-table-wrap">
                <input type="hidden" name="action" value="onepaqucpro_cart_recovery_export_activity">
                <?php wp_nonce_field('onepaqucpro_cart_recovery_export_activity'); ?>
                <?php self::render_preserved_query_fields(array(
                    'page',
                    'tab',
                    'cr_activity_status',
                    'cr_activity_type',
                    'cr_activity_from',
                    'cr_activity_to',
                    'cr_activity_cart',
                    'cr_activity_template',
                    'cr_activity_recipient',
                    'cr_activity_search',
                    'cr_activity_sort',
                    'cr_activity_order',
                    'cr_activity_page',
                )); ?>

                <div class="onepaqucpro-cr-table-actions">
                    <div class="onepaqucpro-cr-bulk">
                        <button type="submit" class="button button-secondary"><?php esc_html_e('Export CSV', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
                    </div>
                    <span class="onepaqucpro-cr-table-count">
                        <?php
                        printf(
                            esc_html(_n('%s email', '%s emails', count($items), 'one-page-quick-checkout-for-woocommerce-pro')),
                            esc_html(number_format_i18n(count($items)))
                        );
                        ?>
                    </span>
                </div>

                <?php if (empty($items)) : ?>
                    <div class="onepaqucpro-cr-empty">
                        <span class="dashicons dashicons-email-alt"></span>
                        <h3><?php esc_html_e('No email activity matched the current filters.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                        <p><?php esc_html_e('Adjust the status or cart filters to review more events.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                    </div>
                <?php else : ?>
                    <table class="widefat fixed striped onepaqucpro-cr-table">
                        <thead>
                            <tr>
                                <td class="check-column"><input type="checkbox" data-cr-check-all></td>
                                <th><?php esc_html_e('Item', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <?php if (in_array('type', $visible_columns, true)) : ?>
                                    <th><?php esc_html_e('Type', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <?php endif; ?>
                                <?php if (in_array('recipient', $visible_columns, true)) : ?>
                                    <th><?php esc_html_e('Recipient', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <?php endif; ?>
                                <?php if (in_array('details', $visible_columns, true)) : ?>
                                    <th><?php esc_html_e('Details', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <?php endif; ?>
                                <?php if (in_array('status', $visible_columns, true)) : ?>
                                    <th><?php esc_html_e('Status', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <?php endif; ?>
                                <?php if (in_array('occurred', $visible_columns, true)) : ?>
                                    <th class="onepaqucpro-cr-sortable"><?php echo wp_kses_post(self::get_sort_link('activity', 'occurred_at', __('Occurred', 'one-page-quick-checkout-for-woocommerce-pro'), $filters['sort'], $filters['order'])); ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $row) : ?>
                                <tr>
                                    <th scope="row" class="check-column">
                                        <input type="checkbox" name="activity_ids[]" value="<?php echo esc_attr($row['id']); ?>">
                                    </th>
                                    <td class="onepaqucpro-cr-email-cell">
                                        <strong><?php echo esc_html($row['email_name']); ?></strong>
                                        <div class="row-actions">
                                            <button type="button" class="button-link onepaqucpro-cr-open-modal" data-template="onepaqucpro-cr-detail-<?php echo esc_attr($row['cart_id']); ?>">
                                                <?php esc_html_e('View Cart', 'one-page-quick-checkout-for-woocommerce-pro'); ?>
                                            </button>
                                            <?php if (! empty($row['payload'])) : ?>
                                                <span class="onepaqucpro-cr-separator">|</span>
                                                <button type="button" class="button-link onepaqucpro-cr-open-modal" data-template="onepaqucpro-cr-activity-<?php echo esc_attr($row['id']); ?>">
                                                    <?php esc_html_e('Preview', 'one-page-quick-checkout-for-woocommerce-pro'); ?>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (! self::is_locked_mode() && 'email' === $row['row_type'] && ! empty($row['email_log_id'])) : ?>
                                                <span class="onepaqucpro-cr-separator">|</span>
                                                <a href="<?php echo esc_url(self::get_email_activity_action_url($row['email_log_id'], 'resend')); ?>"><?php esc_html_e('Resend', 'one-page-quick-checkout-for-woocommerce-pro'); ?></a>
                                                <?php if ('failed' === $row['status']) : ?>
                                                    <span class="onepaqucpro-cr-separator">|</span>
                                                    <a href="<?php echo esc_url(self::get_email_activity_action_url($row['email_log_id'], 'retry_failed')); ?>"><?php esc_html_e('Retry Failed', 'one-page-quick-checkout-for-woocommerce-pro'); ?></a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <?php if (in_array('type', $visible_columns, true)) : ?>
                                        <td><?php echo esc_html('email' === $row['row_type'] ? __('Email', 'one-page-quick-checkout-for-woocommerce-pro') : __('Event', 'one-page-quick-checkout-for-woocommerce-pro')); ?></td>
                                    <?php endif; ?>
                                    <?php if (in_array('recipient', $visible_columns, true)) : ?>
                                        <td><?php echo wp_kses_post(self::render_contact_value($row['recipient'], '-', 'Email recipient')); ?></td>
                                    <?php endif; ?>
                                    <?php if (in_array('details', $visible_columns, true)) : ?>
                                        <td>
                                            <?php if (self::is_locked_mode() && 'email' === $row['row_type']) : ?>
                                                <?php echo wp_kses_post(self::render_locked_value('Email details')); ?>
                                            <?php else : ?>
                                                <?php if ($row['subject']) : ?><strong><?php echo esc_html($row['subject']); ?></strong><br><?php endif; ?>
                                                <?php if ($row['engagement']) : ?><span><?php echo esc_html($row['engagement']); ?></span><br><?php endif; ?>
                                                <?php if ($row['details']) : ?><span class="onepaqucpro-cr-meta"><?php echo esc_html($row['details']); ?></span><?php endif; ?>
                                                <?php if ($row['opened_at']) : ?><span class="onepaqucpro-cr-meta"><?php echo esc_html__('Opened:', 'one-page-quick-checkout-for-woocommerce-pro') . ' ' . esc_html(self::format_datetime($row['opened_at'])); ?></span><?php endif; ?>
                                                <?php if ($row['clicked_at']) : ?><span class="onepaqucpro-cr-meta"><?php echo esc_html__('Clicked:', 'one-page-quick-checkout-for-woocommerce-pro') . ' ' . esc_html(self::format_datetime($row['clicked_at'])); ?></span><?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                    <?php if (in_array('status', $visible_columns, true)) : ?>
                                        <td><?php echo wp_kses_post(self::render_email_status_badge($row['status'])); ?></td>
                                    <?php endif; ?>
                                    <?php if (in_array('occurred', $visible_columns, true)) : ?>
                                        <td>
                                            <span class="onepaqucpro-cr-metric"><?php echo esc_html(self::get_relative_time($row['occurred_at'])); ?></span>
                                            <span class="onepaqucpro-cr-meta"><?php echo esc_html(self::format_datetime($row['occurred_at'])); ?></span>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php self::render_pagination('activity', $pagination, array(
                        'cr_activity_status',
                        'cr_activity_type',
                        'cr_activity_from',
                        'cr_activity_to',
                        'cr_activity_cart',
                        'cr_activity_template',
                        'cr_activity_recipient',
                        'cr_activity_search',
                        'cr_activity_sort',
                        'cr_activity_order',
                    )); ?>
                <?php endif; ?>
            </form>

            <?php foreach ($items as $row) : ?>
                <?php if (empty($row['payload'])) {
                    continue;
                }

                if ('email' === $row['row_type']) {
                    self::render_email_preview_template('onepaqucpro-cr-activity-' . $row['id'], $row);
                    continue;
                }
                ?>
                <template id="onepaqucpro-cr-activity-<?php echo esc_attr($row['id']); ?>">
                    <div class="onepaqucpro-cr-detail">
                        <div class="onepaqucpro-cr-detail__header">
                            <div>
                                <h2 id="onepaqucpro-cr-modal-title"><?php echo esc_html($row['email_name']); ?></h2>
                                <p><?php echo wp_kses_post(self::render_contact_value($row['recipient'], '-', 'Email recipient')); ?></p>
                            </div>
                            <?php echo wp_kses_post(self::render_email_status_badge($row['status'])); ?>
                        </div>
                        <div class="onepaqucpro-cr-detail__section">
                            <h3><?php esc_html_e('Payload', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                            <dl class="onepaqucpro-cr-meta-grid">
                                <?php foreach ($row['payload'] as $label => $value) : ?>
                                    <div>
                                        <dt><?php echo esc_html(ucfirst(str_replace('_', ' ', (string) $label))); ?></dt>
                                        <dd><?php echo wp_kses_post(self::render_detail_meta_value($label, is_scalar($value) ? (string) $value : wp_json_encode($value))); ?></dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>
                        </div>
                    </div>
                </template>
            <?php endforeach; ?>
        </section>
    <?php
    }

    private static function render_email_activity_tab($context)
    {
        $filters = $context['filters'];
        $items   = $context['items'];
        $pagination = $context['pagination'];
        $summary = isset($context['summary']) ? $context['summary'] : self::get_email_activity_summary($context['all_items']);
        $template_options = self::get_template_name_options();
        $visible_columns = self::get_screen_option_visible_columns('email_activity');
    ?>
        <div class="plugincy_card onepaqucpro-cr-settings-card">
            <div class="onepaqucpro-cr-card-heading">
                <div>
                    <h2><?php esc_html_e('Email Activities', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h2>
                    <p><?php esc_html_e('Review sent, failed, opened, clicked, and recovered recovery emails from one place.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                </div>
            </div>

            <div class="onepaqucpro-cr-stat-grid onepaqucpro-cr-email-activity-summary">
                <article class="onepaqucpro-cr-stat-card is-sent">
                    <span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
                    <div>
                        <small><?php esc_html_e('Sent', 'one-page-quick-checkout-for-woocommerce-pro'); ?></small>
                        <strong><?php echo esc_html(number_format_i18n($summary['sent'])); ?></strong>
                        <span class="onepaqucpro-cr-meta"><?php esc_html_e('Delivered attempts, excluding failures', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <?php echo wp_kses_post(self::render_metric_delta(isset($summary['deltas']['sent']) ? $summary['deltas']['sent'] : array(), 'onepaqucpro-cr-kpi-card__delta onepaqucpro-cr-stat-card__delta')); ?>
                    </div>
                </article>
                <article class="onepaqucpro-cr-stat-card is-failed">
                    <span class="dashicons dashicons-warning" aria-hidden="true"></span>
                    <div>
                        <small><?php esc_html_e('Failed', 'one-page-quick-checkout-for-woocommerce-pro'); ?></small>
                        <strong><?php echo esc_html(number_format_i18n($summary['failed'])); ?></strong>
                        <span class="onepaqucpro-cr-meta"><?php esc_html_e('Retry these from the table', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <?php echo wp_kses_post(self::render_metric_delta(isset($summary['deltas']['failed']) ? $summary['deltas']['failed'] : array(), 'onepaqucpro-cr-kpi-card__delta onepaqucpro-cr-stat-card__delta')); ?>
                    </div>
                </article>
                <article class="onepaqucpro-cr-stat-card is-opened">
                    <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                    <div>
                        <small><?php esc_html_e('Opened', 'one-page-quick-checkout-for-woocommerce-pro'); ?></small>
                        <strong><?php echo esc_html(number_format_i18n($summary['opened'])); ?></strong>
                        <span class="onepaqucpro-cr-meta"><?php echo esc_html(sprintf(__('Open rate %s', 'one-page-quick-checkout-for-woocommerce-pro'), self::format_percent($summary['open_rate']))); ?></span>
                        <?php echo wp_kses_post(self::render_metric_delta(isset($summary['deltas']['opened']) ? $summary['deltas']['opened'] : array(), 'onepaqucpro-cr-kpi-card__delta onepaqucpro-cr-stat-card__delta')); ?>
                    </div>
                </article>
                <article class="onepaqucpro-cr-stat-card is-clicked">
                    <span class="dashicons dashicons-admin-links" aria-hidden="true"></span>
                    <div>
                        <small><?php esc_html_e('Clicked', 'one-page-quick-checkout-for-woocommerce-pro'); ?></small>
                        <strong><?php echo esc_html(number_format_i18n($summary['clicked'])); ?></strong>
                        <span class="onepaqucpro-cr-meta"><?php echo esc_html(sprintf(__('Click rate %s', 'one-page-quick-checkout-for-woocommerce-pro'), self::format_percent($summary['click_rate']))); ?></span>
                        <?php echo wp_kses_post(self::render_metric_delta(isset($summary['deltas']['clicked']) ? $summary['deltas']['clicked'] : array(), 'onepaqucpro-cr-kpi-card__delta onepaqucpro-cr-stat-card__delta')); ?>
                    </div>
                </article>
                <article class="onepaqucpro-cr-stat-card is-recovered">
                    <span class="dashicons dashicons-cart" aria-hidden="true"></span>
                    <div>
                        <small><?php esc_html_e('Recovered', 'one-page-quick-checkout-for-woocommerce-pro'); ?></small>
                        <strong><?php echo esc_html(number_format_i18n($summary['recovered'])); ?></strong>
                        <span class="onepaqucpro-cr-meta"><?php esc_html_e('Attributed recovered email logs', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <?php echo wp_kses_post(self::render_metric_delta(isset($summary['deltas']['recovered']) ? $summary['deltas']['recovered'] : array(), 'onepaqucpro-cr-kpi-card__delta onepaqucpro-cr-stat-card__delta')); ?>
                    </div>
                </article>
            </div>

            <form method="get" class="onepaqucpro-cr-toolbar onepaqucpro-cr-toolbar--with-quick-select plugincy_card">
                <input type="hidden" name="page" value="<?php echo esc_attr(self::PAGE_SLUG); ?>">
                <input type="hidden" name="tab" value="email">
                <input type="hidden" name="cr_email_view" value="activity">
                <input type="hidden" name="cr_activity_type" value="email">
                <div class="onepaqucpro-cr-toolbar__filters">
                    <label class="onepaqucpro-cr-filter-field onepaqucpro-cr-filter-field--wide">
                        <span><?php esc_html_e('Search', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <input type="search" name="cr_activity_search" value="<?php echo esc_attr($filters['search']); ?>" placeholder="<?php esc_attr_e('Search email activity...', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                    </label>
                    <label class="onepaqucpro-cr-filter-field">
                        <span><?php esc_html_e('Status', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <select name="cr_activity_status">
                            <option value="all" <?php selected($filters['status'], 'all'); ?>><?php esc_html_e('All Statuses', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="sent" <?php selected($filters['status'], 'sent'); ?>><?php esc_html_e('Sent', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="opened" <?php selected($filters['status'], 'opened'); ?>><?php esc_html_e('Opened', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="clicked" <?php selected($filters['status'], 'clicked'); ?>><?php esc_html_e('Clicked', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="failed" <?php selected($filters['status'], 'failed'); ?>><?php esc_html_e('Failed', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="recovered" <?php selected($filters['status'], 'recovered'); ?>><?php esc_html_e('Recovered', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                        </select>
                    </label>
                    <label class="onepaqucpro-cr-filter-field">
                        <span><?php esc_html_e('Template', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <select name="cr_activity_template">
                            <option value=""><?php esc_html_e('All Templates', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <?php foreach ($template_options as $template_name) : ?>
                                <option value="<?php echo esc_attr($template_name); ?>" <?php selected($filters['template'], $template_name); ?>><?php echo esc_html($template_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="onepaqucpro-cr-filter-field">
                        <span><?php esc_html_e('Date From', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <input type="date" name="cr_activity_from" value="<?php echo esc_attr($filters['from']); ?>">
                    </label>
                    <label class="onepaqucpro-cr-filter-field">
                        <span><?php esc_html_e('Date To', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <input type="date" name="cr_activity_to" value="<?php echo esc_attr($filters['to']); ?>">
                    </label>
                    <label class="onepaqucpro-cr-filter-field onepaqucpro-cr-filter-field--compact">
                        <span><?php esc_html_e('Cart ID', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <input type="text" name="cr_activity_cart" value="<?php echo esc_attr($filters['cart']); ?>" placeholder="<?php esc_attr_e('Cart ID', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                    </label>
                    <label class="onepaqucpro-cr-filter-field">
                        <span><?php esc_html_e('Recipient', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                        <input type="text" name="cr_activity_recipient" value="<?php echo esc_attr($filters['recipient']); ?>" placeholder="<?php esc_attr_e('Recipient email', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                    </label>
                    <?php self::render_quick_select('cr_activity_from', 'cr_activity_to', array(
                        'tab'              => 'email',
                        'cr_email_view'    => 'activity',
                        'cr_activity_type' => 'email',
                    ), array(
                        'cr_activity_status',
                        'cr_activity_cart',
                        'cr_activity_template',
                        'cr_activity_recipient',
                        'cr_activity_search',
                        'cr_activity_sort',
                        'cr_activity_order',
                    ), $filters['from'], $filters['to']); ?>
                    <div class="onepaqucpro-cr-filter-actions">
                        <button type="submit" class="button button-primary"><?php esc_html_e('Apply', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
                        <a class="button button-secondary" href="<?php echo esc_url(self::get_page_url(array('tab' => 'email', 'cr_email_view' => 'activity'))); ?>"><?php esc_html_e('Reset', 'one-page-quick-checkout-for-woocommerce-pro'); ?></a>
                    </div>
                </div>
            </form>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="onepaqucpro-cr-table-wrap">
                <input type="hidden" name="action" value="onepaqucpro_cart_recovery_email_activity_bulk_action">
                <?php wp_nonce_field('onepaqucpro_cart_recovery_email_activity_bulk_action'); ?>
                <?php self::render_preserved_query_fields(array(
                    'page',
                    'tab',
                    'cr_email_view',
                    'cr_activity_status',
                    'cr_activity_type',
                    'cr_activity_from',
                    'cr_activity_to',
                    'cr_activity_cart',
                    'cr_activity_template',
                    'cr_activity_recipient',
                    'cr_activity_search',
                    'cr_activity_sort',
                    'cr_activity_order',
                    'cr_activity_page',
                )); ?>

                <div class="onepaqucpro-cr-table-actions">
                    <div class="onepaqucpro-cr-bulk">
                        <select name="email_activity_bulk_action">
                            <option value=""><?php esc_html_e('Bulk actions', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="resend"><?php esc_html_e('Resend Selected', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                            <option value="retry_failed"><?php esc_html_e('Retry Failed Selected', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                        </select>
                        <button type="submit" class="button button-secondary"><?php esc_html_e('Apply', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
                        <button type="submit" class="button button-secondary" name="email_activity_bulk_action" value="export"><?php esc_html_e('Export CSV', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
                    </div>
                    <span class="onepaqucpro-cr-table-count">
                        <?php
                        printf(
                            esc_html(_n('%s email activity', '%s email activities', (int) $pagination['total_items'], 'one-page-quick-checkout-for-woocommerce-pro')),
                            esc_html(number_format_i18n((int) $pagination['total_items']))
                        );
                        ?>
                    </span>
                </div>

                <?php if (empty($items)) : ?>
                    <div class="onepaqucpro-cr-empty">
                        <span class="dashicons dashicons-email-alt"></span>
                        <h3><?php esc_html_e('No email activity matched the current filters.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                        <p><?php esc_html_e('Adjust the status, date, template, cart, recipient, or search filters to review more emails.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                    </div>
                <?php else : ?>
                    <table class="widefat fixed striped onepaqucpro-cr-table onepaqucpro-cr-table--email-activity">
                        <thead>
                            <tr>
                                <td class="check-column"><input type="checkbox" data-cr-check-all></td>
                                <th><?php esc_html_e('Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <?php if (in_array('recipient', $visible_columns, true)) : ?>
                                    <th><?php esc_html_e('Recipient', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <?php endif; ?>
                                <?php if (in_array('details', $visible_columns, true)) : ?>
                                    <th><?php esc_html_e('Details', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <?php endif; ?>
                                <?php if (in_array('status', $visible_columns, true)) : ?>
                                    <th><?php esc_html_e('Status', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                                <?php endif; ?>
                                <?php if (in_array('occurred', $visible_columns, true)) : ?>
                                    <th class="onepaqucpro-cr-sortable"><?php echo wp_kses_post(self::get_sort_link('email_activity', 'occurred_at', __('Occurred', 'one-page-quick-checkout-for-woocommerce-pro'), $filters['sort'], $filters['order'])); ?></th>
                                <?php endif; ?>
                                <th><?php esc_html_e('Actions', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $row) : ?>
                                <tr>
                                    <th scope="row" class="check-column">
                                        <input type="checkbox" name="activity_ids[]" value="<?php echo esc_attr($row['id']); ?>">
                                    </th>
                                    <td class="onepaqucpro-cr-email-cell">
                                        <strong><?php echo esc_html($row['email_name']); ?></strong>
                                    </td>
                                    <?php if (in_array('recipient', $visible_columns, true)) : ?>
                                        <td><?php echo wp_kses_post(self::render_contact_value($row['recipient'], '-', 'Email recipient')); ?></td>
                                    <?php endif; ?>
                                    <?php if (in_array('details', $visible_columns, true)) : ?>
                                        <td>
                                            <?php if (self::is_locked_mode()) : ?>
                                                <?php echo wp_kses_post(self::render_locked_value('Email details')); ?>
                                            <?php else : ?>
                                                <?php if ($row['subject']) : ?><strong><?php echo esc_html($row['subject']); ?></strong><br><?php endif; ?>
                                                <?php if ($row['engagement']) : ?><span><?php echo esc_html($row['engagement']); ?></span><br><?php endif; ?>
                                                <?php if ($row['details']) : ?><span class="onepaqucpro-cr-meta"><?php echo esc_html($row['details']); ?></span><?php endif; ?>
                                                <?php if ($row['opened_at']) : ?><span class="onepaqucpro-cr-meta"><?php echo esc_html__('Opened:', 'one-page-quick-checkout-for-woocommerce-pro') . ' ' . esc_html(self::format_datetime($row['opened_at'])); ?></span><?php endif; ?>
                                                <?php if ($row['clicked_at']) : ?><span class="onepaqucpro-cr-meta"><?php echo esc_html__('Clicked:', 'one-page-quick-checkout-for-woocommerce-pro') . ' ' . esc_html(self::format_datetime($row['clicked_at'])); ?></span><?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                    <?php if (in_array('status', $visible_columns, true)) : ?>
                                        <td><?php echo wp_kses_post(self::render_email_status_badge($row['status'])); ?></td>
                                    <?php endif; ?>
                                    <?php if (in_array('occurred', $visible_columns, true)) : ?>
                                        <td>
                                            <span class="onepaqucpro-cr-metric"><?php echo esc_html(self::get_relative_time($row['occurred_at'])); ?></span>
                                            <span class="onepaqucpro-cr-meta"><?php echo esc_html(self::format_datetime($row['occurred_at'])); ?></span>
                                        </td>
                                    <?php endif; ?>
                                    <td class="onepaqucpro-cr-actions-cell">
                                        <div class="onepaqucpro-cr-action-list">
                                            <button type="button" class="onepaqucpro-cr-icon-action onepaqucpro-cr-open-modal" data-template="onepaqucpro-cr-detail-<?php echo esc_attr($row['cart_id']); ?>" title="<?php esc_attr_e('View Cart', 'one-page-quick-checkout-for-woocommerce-pro'); ?>" aria-label="<?php esc_attr_e('View Cart', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                                                <span class="dashicons dashicons-cart" aria-hidden="true"></span>
                                                <span class="screen-reader-text"><?php esc_html_e('View Cart', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                            </button>
                                            <?php if (! empty($row['payload'])) : ?>
                                                <button type="button" class="onepaqucpro-cr-icon-action onepaqucpro-cr-open-modal" data-template="onepaqucpro-cr-email-activity-<?php echo esc_attr($row['id']); ?>" title="<?php esc_attr_e('Preview Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?>" aria-label="<?php esc_attr_e('Preview Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                                                    <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                                                    <span class="screen-reader-text"><?php esc_html_e('Preview Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (! self::is_locked_mode() && ! empty($row['email_log_id'])) : ?>
                                                <a class="onepaqucpro-cr-icon-action" href="<?php echo esc_url(self::get_email_activity_action_url($row['email_log_id'], 'resend')); ?>" title="<?php esc_attr_e('Resend Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?>" aria-label="<?php esc_attr_e('Resend Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                                                    <span class="dashicons dashicons-update" aria-hidden="true"></span>
                                                    <span class="screen-reader-text"><?php esc_html_e('Resend Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                                </a>
                                                <?php if ('failed' === $row['status']) : ?>
                                                    <a class="onepaqucpro-cr-icon-action is-warning" href="<?php echo esc_url(self::get_email_activity_action_url($row['email_log_id'], 'retry_failed')); ?>" title="<?php esc_attr_e('Retry Failed Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?>" aria-label="<?php esc_attr_e('Retry Failed Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                                                        <span class="dashicons dashicons-warning" aria-hidden="true"></span>
                                                        <span class="screen-reader-text"><?php esc_html_e('Retry Failed Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if (! self::is_locked_mode() && ! empty($row['can_send_next'])) : ?>
                                                <a class="onepaqucpro-cr-icon-action" href="<?php echo esc_url(self::get_cart_action_url($row['cart_id'], 'send_next')); ?>" title="<?php esc_attr_e('Send Next Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?>" aria-label="<?php esc_attr_e('Send Next Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                                                    <span class="dashicons dashicons-email-alt" aria-hidden="true"></span>
                                                    <span class="screen-reader-text"><?php esc_html_e('Send Next Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (! empty($row['recovered_order_id'])) : ?>
                                                <a class="onepaqucpro-cr-icon-action" href="<?php echo esc_url(admin_url('post.php?post=' . absint($row['recovered_order_id']) . '&action=edit')); ?>" title="<?php esc_attr_e('View Order', 'one-page-quick-checkout-for-woocommerce-pro'); ?>" aria-label="<?php esc_attr_e('View Order', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                                                    <span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
                                                    <span class="screen-reader-text"><?php esc_html_e('View Order', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php self::render_pagination('email_activity', $pagination, array(
                        'cr_activity_status',
                        'cr_activity_from',
                        'cr_activity_to',
                        'cr_activity_cart',
                        'cr_activity_template',
                        'cr_activity_recipient',
                        'cr_activity_search',
                        'cr_activity_sort',
                        'cr_activity_order',
                    )); ?>
                <?php endif; ?>
            </form>

            <?php foreach ($items as $row) : ?>
                <?php self::render_email_preview_template('onepaqucpro-cr-email-activity-' . $row['id'], $row); ?>
            <?php endforeach; ?>
        </div>
    <?php
    }

    private static function render_email_preview_template($template_dom_id, $row)
    {
        if (empty($row['payload']) || ! is_array($row['payload'])) {
            return;
        }

        $payload      = $row['payload'];
        $heading      = isset($payload['heading']) ? wp_strip_all_tags((string) $payload['heading']) : '';
        $body         = isset($payload['body']) ? (string) $payload['body'] : '';
        $cart_link    = isset($payload['cart_link']) ? esc_url_raw($payload['cart_link']) : '';
        $template_id  = isset($payload['template_id']) ? sanitize_key($payload['template_id']) : (isset($row['template_id']) ? sanitize_key($row['template_id']) : '');
        $sender_email = isset($payload['sender_email']) ? sanitize_email($payload['sender_email']) : '';
        $reply_to     = isset($payload['reply_to']) ? sanitize_email($payload['reply_to']) : '';
    ?>
        <template id="<?php echo esc_attr($template_dom_id); ?>">
            <div class="onepaqucpro-cr-detail onepaqucpro-cr-email-preview">
                <div class="onepaqucpro-cr-email-preview__header">
                    <div class="onepaqucpro-cr-email-preview__identity">
                        <span class="onepaqucpro-cr-email-preview__icon dashicons dashicons-email-alt2" aria-hidden="true"></span>
                        <div>
                            <span class="onepaqucpro-cr-email-preview__eyebrow"><?php esc_html_e('Email Preview', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                            <h2 id="onepaqucpro-cr-modal-title"><?php echo esc_html($row['email_name']); ?></h2>
                            <p><?php esc_html_e('To', 'one-page-quick-checkout-for-woocommerce-pro'); ?> <?php echo wp_kses_post(self::render_contact_value($row['recipient'], '-', 'Email recipient')); ?></p>
                        </div>
                    </div>
                    <div class="onepaqucpro-cr-email-preview__header-aside">
                        <span class="onepaqucpro-cr-email-preview__time"><?php echo esc_html(self::format_datetime($row['sent_at'])); ?></span>
                        <?php echo wp_kses_post(self::render_email_status_badge($row['status'])); ?>
                    </div>
                </div>

                <div class="onepaqucpro-cr-email-preview__workspace">
                    <main class="onepaqucpro-cr-email-preview__main">
                        <div class="onepaqucpro-cr-email-preview__subject">
                            <span><?php esc_html_e('Subject', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                            <strong><?php echo esc_html(! empty($row['subject']) ? $row['subject'] : __('No subject recorded', 'one-page-quick-checkout-for-woocommerce-pro')); ?></strong>
                            <?php if ($heading) : ?>
                                <small><?php echo esc_html(sprintf(__('Heading: %s', 'one-page-quick-checkout-for-woocommerce-pro'), $heading)); ?></small>
                            <?php endif; ?>
                        </div>

                        <section class="onepaqucpro-cr-email-preview__body">
                            <div class="onepaqucpro-cr-email-preview__body-head">
                                <div>
                                    <h3><?php esc_html_e('Rendered Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                    <p><?php esc_html_e('Stored HTML from this activity, shown inside a contained email frame.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                                </div>
                            </div>
                            <div class="onepaqucpro-cr-email-preview__clientbar" aria-hidden="true">
                                <span></span><span></span><span></span>
                                <strong><?php esc_html_e('HTML preview', 'one-page-quick-checkout-for-woocommerce-pro'); ?></strong>
                            </div>
                            <div class="onepaqucpro-cr-email-preview__canvas">
                                <div class="onepaqucpro-cr-email-preview__frame">
                                    <?php
                                    if (self::is_locked_mode()) {
                                        echo wp_kses_post(self::render_locked_value('Email content'));
                                    } elseif ($body) {
                                        echo wp_kses_post($body);
                                    } else {
                                        esc_html_e('No email body was stored for this activity.', 'one-page-quick-checkout-for-woocommerce-pro');
                                    }
                                    ?>
                                </div>
                            </div>
                        </section>
                    </main>

                    <aside class="onepaqucpro-cr-email-preview__sidebar">
                        <section class="onepaqucpro-cr-email-preview__panel">
                            <h3><?php esc_html_e('Delivery', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                            <dl class="onepaqucpro-cr-email-preview__list">
                                <div>
                                    <dt><?php esc_html_e('Template ID', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                    <dd><?php echo esc_html($template_id ? $template_id : '-'); ?></dd>
                                </div>
                                <div>
                                    <dt><?php esc_html_e('Sender', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                    <dd><?php echo wp_kses_post(self::is_locked_mode() ? self::render_locked_value('Sender') : esc_html($sender_email ? $sender_email : '-')); ?></dd>
                                </div>
                                <div>
                                    <dt><?php esc_html_e('Reply-To', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                    <dd><?php echo wp_kses_post(self::is_locked_mode() ? self::render_locked_value('Reply-To') : esc_html($reply_to ? $reply_to : '-')); ?></dd>
                                </div>
                                <div>
                                    <dt><?php esc_html_e('Opened', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                    <dd><?php echo esc_html(! empty($row['opened_at']) ? self::format_datetime($row['opened_at']) : '-'); ?></dd>
                                </div>
                                <div>
                                    <dt><?php esc_html_e('Clicked', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                    <dd><?php echo esc_html(! empty($row['clicked_at']) ? self::format_datetime($row['clicked_at']) : '-'); ?></dd>
                                </div>
                            </dl>
                        </section>

                        <?php if ($cart_link && ! self::is_locked_mode()) : ?>
                            <section class="onepaqucpro-cr-email-preview__panel onepaqucpro-cr-email-preview__panel--link">
                                <h3><?php esc_html_e('Recovery Link', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                <code><?php echo esc_html($cart_link); ?></code>
                                <div class="onepaqucpro-cr-email-preview__link-actions">
                                    <a class="button button-primary" href="<?php echo esc_url($cart_link); ?>" target="_blank" rel="noopener noreferrer">
                                        <span class="dashicons dashicons-external" aria-hidden="true"></span>
                                        <?php esc_html_e('Open Link', 'one-page-quick-checkout-for-woocommerce-pro'); ?>
                                    </a>
                                    <button type="button" class="button button-secondary" data-cr-copy="<?php echo esc_attr($cart_link); ?>">
                                        <span class="dashicons dashicons-admin-page" aria-hidden="true"></span>
                                        <span data-cr-copy-label><?php esc_html_e('Copy', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                    </button>
                                </div>
                            </section>
                        <?php endif; ?>
                    </aside>
                </div>
            </div>
        </template>
    <?php
    }

    private static function render_template_preview_template($template_dom_id, $template)
    {
        $template = wp_parse_args(is_array($template) ? $template : array(), self::get_default_template_shape());
        $template_id = sanitize_key(isset($template['id']) ? $template['id'] : '');
        $layout      = self::sanitize_cart_items_layout(isset($template['cart_items_layout']) ? $template['cart_items_layout'] : 'table');
        $layouts     = self::get_cart_items_layout_options();
        $layout_label = isset($layouts[$layout]) ? $layouts[$layout]['label'] : $layouts['table']['label'];
        $preview_tag_sets = array();
        foreach (array_keys($layouts) as $layout_key) {
            $preview_tag_sets[$layout_key] = self::get_template_preview_merge_tags($layout_key);
        }
        $merge_tags  = self::get_template_preview_merge_tags($layout);
        $subject     = wp_strip_all_tags(strtr($template['subject'] ? $template['subject'] : __('Recovery email', 'one-page-quick-checkout-for-woocommerce-pro'), $merge_tags));
        $heading     = wp_strip_all_tags(strtr($template['heading'] ? $template['heading'] : __('We saved your cart', 'one-page-quick-checkout-for-woocommerce-pro'), $merge_tags));
        $message     = $template['message'] ? $template['message'] : self::get_default_message_template($template_id);
        $body        = wp_kses_post(strtr($message, $merge_tags));
        $cart_link   = esc_url_raw($merge_tags['{cart_link}']);
        $updated_at  = ! empty($template['updated_at']) ? self::format_datetime($template['updated_at']) : __('Not saved yet', 'one-page-quick-checkout-for-woocommerce-pro');

        if (false === stripos($body, '<p') && false === stripos($body, '<ul') && false === stripos($body, '<ol') && false === stripos($body, '<div') && false === stripos($body, '<table')) {
            $body = wpautop($body);
        }
    ?>
        <template id="<?php echo esc_attr($template_dom_id); ?>">
            <div class="onepaqucpro-cr-detail onepaqucpro-cr-email-preview onepaqucpro-cr-template-preview">
                <script type="application/json" data-cr-template-preview-tags><?php echo wp_json_encode($preview_tag_sets, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?></script>
                <div class="onepaqucpro-cr-email-preview__header">
                    <div class="onepaqucpro-cr-email-preview__identity">
                        <span class="onepaqucpro-cr-email-preview__icon dashicons dashicons-visibility" aria-hidden="true"></span>
                        <div>
                            <span class="onepaqucpro-cr-email-preview__eyebrow"><?php esc_html_e('Template Preview', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                            <h2 id="onepaqucpro-cr-modal-title" data-cr-template-preview-name><?php echo esc_html($template['name'] ? $template['name'] : __('Untitled Email', 'one-page-quick-checkout-for-woocommerce-pro')); ?></h2>
                            <p><?php esc_html_e('Sample shopper data with the selected cart item layout.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                        </div>
                    </div>
                    <div class="onepaqucpro-cr-email-preview__header-aside">
                        <span class="onepaqucpro-cr-email-preview__time"><?php echo esc_html($updated_at); ?></span>
                        <span class="onepaqucpro-cr-email-badge is-sent"><?php esc_html_e('Preview', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                    </div>
                </div>

                <div class="onepaqucpro-cr-email-preview__workspace">
                    <main class="onepaqucpro-cr-email-preview__main">
                        <div class="onepaqucpro-cr-email-preview__subject">
                            <span><?php esc_html_e('Subject', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                            <strong data-cr-template-preview-subject><?php echo esc_html($subject); ?></strong>
                            <?php if ($heading) : ?>
                                <small data-cr-template-preview-heading><?php echo esc_html(sprintf(__('Heading: %s', 'one-page-quick-checkout-for-woocommerce-pro'), $heading)); ?></small>
                            <?php endif; ?>
                        </div>

                        <section class="onepaqucpro-cr-email-preview__body">
                            <div class="onepaqucpro-cr-email-preview__body-head">
                                <div>
                                    <h3><?php esc_html_e('Rendered Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                    <p><?php esc_html_e('Preview uses sample values for merge tags, checkout links, cart items, and totals.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                                </div>
                            </div>
                            <div class="onepaqucpro-cr-email-preview__clientbar" aria-hidden="true">
                                <span></span><span></span><span></span>
                                <strong><?php esc_html_e('Email preview', 'one-page-quick-checkout-for-woocommerce-pro'); ?></strong>
                            </div>
                            <div class="onepaqucpro-cr-email-preview__canvas">
                                <div class="onepaqucpro-cr-email-preview__frame" data-cr-template-preview-frame>
                                    <?php echo $body ? wp_kses_post($body) : esc_html__('No email body is available for this template.', 'one-page-quick-checkout-for-woocommerce-pro'); ?>
                                </div>
                            </div>
                        </section>
                    </main>

                    <aside class="onepaqucpro-cr-email-preview__sidebar">
                        <section class="onepaqucpro-cr-email-preview__panel">
                            <h3><?php esc_html_e('Template', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                            <dl class="onepaqucpro-cr-email-preview__list">
                                <div>
                                    <dt><?php esc_html_e('Template ID', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                    <dd><?php echo esc_html($template_id ? $template_id : '-'); ?></dd>
                                </div>
                                <div>
                                    <dt><?php esc_html_e('Cart Items Layout', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                    <dd data-cr-template-preview-layout><?php echo esc_html($layout_label); ?></dd>
                                </div>
                                <div>
                                    <dt><?php esc_html_e('Trigger', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                    <dd><?php echo esc_html(self::format_template_delay($template)); ?></dd>
                                </div>
                                <div>
                                    <dt><?php esc_html_e('Status', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                    <dd><?php echo esc_html(! empty($template['enabled']) ? __('Enabled', 'one-page-quick-checkout-for-woocommerce-pro') : __('Disabled', 'one-page-quick-checkout-for-woocommerce-pro')); ?></dd>
                                </div>
                            </dl>
                        </section>

                        <section class="onepaqucpro-cr-email-preview__panel onepaqucpro-cr-email-preview__panel--link">
                            <h3><?php esc_html_e('Sample Recovery Link', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                            <code><?php echo esc_html($cart_link); ?></code>
                            <div class="onepaqucpro-cr-email-preview__link-actions">
                                <a class="button button-primary" href="<?php echo esc_url($cart_link); ?>" target="_blank" rel="noopener noreferrer">
                                    <span class="dashicons dashicons-external" aria-hidden="true"></span>
                                    <?php esc_html_e('Open Link', 'one-page-quick-checkout-for-woocommerce-pro'); ?>
                                </a>
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        </template>
    <?php
    }

    private static function get_template_preview_merge_tags($cart_items_layout)
    {
        $currency = function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : 'USD';
        $items    = self::get_template_preview_items();
        $total    = 0;

        foreach ($items as $item) {
            $total += isset($item['price']) ? (float) $item['price'] : 0;
        }

        $cart_link = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout/');

        return array(
            '{customer_firstname}' => __('Alex', 'one-page-quick-checkout-for-woocommerce-pro'),
            '{customer_lastname}'  => __('Morgan', 'one-page-quick-checkout-for-woocommerce-pro'),
            '{customer_name}'      => __('Alex Morgan', 'one-page-quick-checkout-for-woocommerce-pro'),
            '{customer_email}'     => 'alex@example.com',
            '{customer_phone}'     => '+1 555 0147',
            '{customer_company}'   => __('Northstar Studio', 'one-page-quick-checkout-for-woocommerce-pro'),
            '{cart_items}'         => self::build_cart_items_preview_markup($items, $currency, $cart_items_layout),
            '{cart_total}'         => wp_strip_all_tags(self::format_currency($total, $currency)),
            '{cart_item_count}'    => (string) count($items),
            '{cart_currency}'      => $currency,
            '{cart_created_at}'    => wp_date(get_option('date_format') . ' ' . get_option('time_format'), current_time('timestamp') - HOUR_IN_SECONDS, wp_timezone()),
            '{cart_abandoned_at}'  => wp_date(get_option('date_format') . ' ' . get_option('time_format'), current_time('timestamp'), wp_timezone()),
            '{cart_link}'          => esc_url_raw($cart_link),
            '{discount_code}'      => 'SAVE10',
            '{unsubscribe_link}'   => esc_url_raw(add_query_arg('onepaqucpro_cr_unsubscribe', 'preview', home_url('/'))),
            '{sitename}'           => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
            '{site_url}'           => home_url('/'),
            '{store_email}'        => self::get_store_email(),
        );
    }

    private static function get_template_preview_items()
    {
        return array(
            array(
                'name'        => __('Simple Product 2', 'one-page-quick-checkout-for-woocommerce-pro'),
                'quantity'    => 1,
                'price'       => 50,
                'image_url'   => function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src('thumbnail') : '',
                'product_url' => home_url('/product/simple-product-2/'),
            ),
            array(
                'name'        => __('Apple Watch Series 10', 'one-page-quick-checkout-for-woocommerce-pro'),
                'quantity'    => 1,
                'price'       => 999,
                'image_url'   => function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src('thumbnail') : '',
                'product_url' => home_url('/product/apple-watch-series-10/'),
            ),
        );
    }

    private static function build_cart_items_preview_markup($items, $currency, $layout = 'table')
    {
        if (empty($items)) {
            return '';
        }

        $layout = self::sanitize_cart_items_layout($layout);
        $rows   = array();

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $rows[] = array(
                'name'        => sanitize_text_field(isset($item['name']) ? $item['name'] : ''),
                'quantity'    => max(1, absint(isset($item['quantity']) ? $item['quantity'] : 1)),
                'price'       => wp_kses_post(self::format_currency(isset($item['price']) ? (float) $item['price'] : 0, $currency)),
                'image_url'   => isset($item['image_url']) ? esc_url_raw($item['image_url']) : '',
                'product_url' => isset($item['product_url']) ? esc_url_raw($item['product_url']) : '',
            );
        }

        if (empty($rows)) {
            return '';
        }

        if ('list' === $layout) {
            $markup = '<ul style="margin:0 0 0 20px;padding:0;color:#111827;font-size:15px;line-height:1.7;">';
            foreach ($rows as $row) {
                $markup .= '<li style="margin:0 0 8px;"><strong>' . esc_html($row['name']) . '</strong> x ' . esc_html((string) $row['quantity']) . ' - ' . $row['price'] . '</li>';
            }

            return $markup . '</ul>';
        }

        if ('compact' === $layout) {
            $markup = '<div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">';
            foreach ($rows as $index => $row) {
                $border = $index + 1 < count($rows) ? 'border-bottom:1px solid #e5e7eb;' : '';
                $markup .= '<div style="display:block;padding:14px 16px;' . $border . '">' .
                    '<div style="font-size:15px;font-weight:700;color:#111827;">' . esc_html($row['name']) . '</div>' .
                    '<div style="margin-top:5px;font-size:13px;color:#6b7280;">' . esc_html__('Quantity', 'one-page-quick-checkout-for-woocommerce-pro') . ': ' . esc_html((string) $row['quantity']) . ' | ' . esc_html__('Price', 'one-page-quick-checkout-for-woocommerce-pro') . ': ' . $row['price'] . '</div>' .
                    '</div>';
            }

            return $markup . '</div>';
        }

        if ('cards' === $layout) {
            $markup = '<div style="display:block;">';
            foreach ($rows as $row) {
                $image = $row['image_url']
                    ? '<img src="' . esc_url($row['image_url']) . '" alt="" width="56" height="56" style="display:block;width:56px;height:56px;border-radius:8px;object-fit:cover;border:1px solid #e5e7eb;">'
                    : '<span style="display:block;width:56px;height:56px;border-radius:8px;background:#f3f4f6;border:1px solid #e5e7eb;"></span>';

                $markup .= '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%;margin:0 0 10px;border:1px solid #e5e7eb;border-radius:8px;background:#ffffff;">' .
                    '<tr><td width="72" style="padding:12px;vertical-align:top;">' . $image . '</td>' .
                    '<td style="padding:12px 12px 12px 0;vertical-align:top;"><strong style="display:block;font-size:15px;color:#111827;">' . esc_html($row['name']) . '</strong><span style="display:block;margin-top:6px;font-size:13px;color:#6b7280;">' . esc_html__('Quantity', 'one-page-quick-checkout-for-woocommerce-pro') . ': ' . esc_html((string) $row['quantity']) . '</span></td>' .
                    '<td align="right" style="padding:12px;vertical-align:top;font-size:15px;font-weight:700;color:#111827;">' . $row['price'] . '</td></tr></table>';
            }

            return $markup . '</div>';
        }

        $markup = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;border-bottom:1px solid #e5e7eb;">' .
            '<thead><tr>' .
            '<th align="left" style="padding:0 0 12px;font-size:13px;color:#374151;border-bottom:1px solid #e5e7eb;">' . esc_html__('Product', 'one-page-quick-checkout-for-woocommerce-pro') . '</th>' .
            '<th align="center" style="padding:0 0 12px;font-size:13px;color:#374151;border-bottom:1px solid #e5e7eb;">' . esc_html__('Quantity', 'one-page-quick-checkout-for-woocommerce-pro') . '</th>' .
            '<th align="right" style="padding:0 0 12px;font-size:13px;color:#374151;border-bottom:1px solid #e5e7eb;">' . esc_html__('Price', 'one-page-quick-checkout-for-woocommerce-pro') . '</th>' .
            '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $image = $row['image_url']
                ? '<img src="' . esc_url($row['image_url']) . '" alt="" width="44" height="44" style="display:block;width:44px;height:44px;border-radius:6px;object-fit:cover;border:1px solid #e5e7eb;">'
                : '<span style="display:block;width:44px;height:44px;border-radius:6px;background:#f3f4f6;border:1px solid #e5e7eb;"></span>';
            $name = $row['product_url']
                ? '<a href="' . esc_url($row['product_url']) . '" style="color:#111827;text-decoration:none;font-weight:700;">' . esc_html($row['name']) . '</a>'
                : '<strong style="font-weight:700;color:#111827;">' . esc_html($row['name']) . '</strong>';

            $markup .= '<tr>' .
                '<td style="padding:14px 0;border-bottom:1px solid #e5e7eb;"><table role="presentation" cellspacing="0" cellpadding="0"><tr><td style="padding:0 12px 0 0;vertical-align:middle;">' . $image . '</td><td style="vertical-align:middle;font-size:15px;">' . $name . '</td></tr></table></td>' .
                '<td align="center" style="padding:14px 10px;border-bottom:1px solid #e5e7eb;font-size:14px;color:#374151;">x ' . esc_html((string) $row['quantity']) . '</td>' .
                '<td align="right" style="padding:14px 0;border-bottom:1px solid #e5e7eb;font-size:15px;color:#111827;">' . $row['price'] . '</td>' .
                '</tr>';
        }

        return $markup . '</tbody></table>';
    }

    private static function get_cart_items_layout_options()
    {
        return array(
            'table'   => array(
                'label'       => __('Table', 'one-page-quick-checkout-for-woocommerce-pro'),
                'description' => __('Product image, quantity, and price columns.', 'one-page-quick-checkout-for-woocommerce-pro'),
                'icon'        => 'dashicons-editor-table',
            ),
            'list'    => array(
                'label'       => __('List', 'one-page-quick-checkout-for-woocommerce-pro'),
                'description' => __('Simple bullet list for short reminder emails.', 'one-page-quick-checkout-for-woocommerce-pro'),
                'icon'        => 'dashicons-list-view',
            ),
            'compact' => array(
                'label'       => __('Compact', 'one-page-quick-checkout-for-woocommerce-pro'),
                'description' => __('Stacked rows for narrow or text-heavy emails.', 'one-page-quick-checkout-for-woocommerce-pro'),
                'icon'        => 'dashicons-screenoptions',
            ),
            'cards'   => array(
                'label'       => __('Cards', 'one-page-quick-checkout-for-woocommerce-pro'),
                'description' => __('Larger product blocks with stronger visual separation.', 'one-page-quick-checkout-for-woocommerce-pro'),
                'icon'        => 'dashicons-grid-view',
            ),
        );
    }

    private static function sanitize_cart_items_layout($layout)
    {
        return self::sanitize_choice($layout, array('table', 'list', 'compact', 'cards'), 'table');
    }

    private static function get_template_merge_tag_groups()
    {
        return array(
            __('Customer', 'one-page-quick-checkout-for-woocommerce-pro') => array(
                '{customer_firstname}' => __('First name', 'one-page-quick-checkout-for-woocommerce-pro'),
                '{customer_lastname}'  => __('Last name', 'one-page-quick-checkout-for-woocommerce-pro'),
                '{customer_name}'      => __('Full name', 'one-page-quick-checkout-for-woocommerce-pro'),
                '{customer_email}'     => __('Email address', 'one-page-quick-checkout-for-woocommerce-pro'),
                '{customer_phone}'     => __('Phone number', 'one-page-quick-checkout-for-woocommerce-pro'),
                '{customer_company}'   => __('Company', 'one-page-quick-checkout-for-woocommerce-pro'),
            ),
            __('Cart', 'one-page-quick-checkout-for-woocommerce-pro') => array(
                '{cart_items}'        => __('Cart items block', 'one-page-quick-checkout-for-woocommerce-pro'),
                '{cart_total}'        => __('Cart total', 'one-page-quick-checkout-for-woocommerce-pro'),
                '{cart_item_count}'   => __('Item count', 'one-page-quick-checkout-for-woocommerce-pro'),
                '{cart_currency}'     => __('Currency code', 'one-page-quick-checkout-for-woocommerce-pro'),
                '{cart_created_at}'   => __('Cart created date', 'one-page-quick-checkout-for-woocommerce-pro'),
                '{cart_abandoned_at}' => __('Cart abandoned date', 'one-page-quick-checkout-for-woocommerce-pro'),
                '{cart_link}'         => __('Recovery checkout link', 'one-page-quick-checkout-for-woocommerce-pro'),
                '{discount_code}'     => __('Template discount code', 'one-page-quick-checkout-for-woocommerce-pro'),
            ),
            __('Store', 'one-page-quick-checkout-for-woocommerce-pro') => array(
                '{sitename}'         => __('Site name', 'one-page-quick-checkout-for-woocommerce-pro'),
                '{site_url}'         => __('Site URL', 'one-page-quick-checkout-for-woocommerce-pro'),
                '{store_email}'      => __('Store email', 'one-page-quick-checkout-for-woocommerce-pro'),
                '{unsubscribe_link}' => __('Unsubscribe link', 'one-page-quick-checkout-for-woocommerce-pro'),
            ),
        );
    }

    private static function render_template_merge_tag_codes()
    {
        $markup = '';

        foreach (self::get_template_merge_tag_groups() as $tags) {
            foreach ($tags as $tag => $label) {
                $markup .= sprintf(
                    '<code title="%1$s">%2$s</code> ',
                    esc_attr($label),
                    esc_html($tag)
                );
            }
        }

        return trim($markup);
    }

    private static function render_template_merge_tag_groups()
    {
        $markup = '';

        foreach (self::get_template_merge_tag_groups() as $group => $tags) {
            $markup .= '<div class="onepaqucpro-cr-template-taggroup">';
            $markup .= '<span class="onepaqucpro-cr-template-taggroup__label">' . esc_html($group) . '</span>';
            $markup .= '<div class="onepaqucpro-cr-template-taggroup__codes">';

            foreach ($tags as $tag => $label) {
                $markup .= sprintf(
                    '<code title="%1$s">%2$s</code>',
                    esc_attr($label),
                    esc_html($tag)
                );
            }

            $markup .= '</div></div>';
        }

        return $markup;
    }

    private static function get_locked_preview_carts($carts)
    {
        return ! empty($carts) ? $carts : self::get_default_carts();
    }

    private static function render_locked_feature_tab($feature, $carts, $settings, $templates)
    {
        $feature = sanitize_key($feature);
        $titles = array(
            'analytics' => __('Analytics is a Pro feature', 'one-page-quick-checkout-for-woocommerce-pro'),
            'journey'   => __('Customer journey insights are a Pro feature', 'one-page-quick-checkout-for-woocommerce-pro'),
            'email'     => __('Recovery email automation is a Pro feature', 'one-page-quick-checkout-for-woocommerce-pro'),
        );
        $descriptions = array(
            'analytics' => __('Preview performance dashboards, revenue trends, and conversion metrics with sample data. Unlock Pro to use your live store data.', 'one-page-quick-checkout-for-woocommerce-pro'),
            'journey'   => __('Preview the funnel and stage drop-off reports with sample data. Unlock Pro to see where your shoppers leave checkout.', 'one-page-quick-checkout-for-woocommerce-pro'),
            'email'     => __('Preview template management, email activity, and delivery settings. Unlock Pro to create, send, and optimize recovery emails.', 'one-page-quick-checkout-for-woocommerce-pro'),
        );
        $demo_carts = self::get_locked_preview_carts($carts);
    ?>
        <section class="onepaqucpro-cr-pro-preview">
            <div class="onepaqucpro-cr-pro-preview__content" aria-hidden="true">
                <?php
                if ('analytics' === $feature) {
                    self::render_analytics_tab(self::get_analytics_context($demo_carts));
                } elseif ('journey' === $feature) {
                    self::render_journey_tab(self::get_journey_context($demo_carts));
                } else {
                    self::render_email_tab($settings, ! empty($templates) ? $templates : self::get_default_templates(), $demo_carts);
                }
                ?>
            </div>
            <div class="onepaqucpro-cr-pro-preview__overlay">
                <span class="dashicons dashicons-lock" aria-hidden="true"></span>
                <h2><?php echo esc_html(isset($titles[$feature]) ? $titles[$feature] : __('Pro feature only', 'one-page-quick-checkout-for-woocommerce-pro')); ?></h2>
                <p><?php echo esc_html(isset($descriptions[$feature]) ? $descriptions[$feature] : __('Unlock Pro to use this cart recovery feature.', 'one-page-quick-checkout-for-woocommerce-pro')); ?></p>
                <a class="button button-primary button-hero" href="<?php echo esc_url(self::get_unlock_url()); ?>" <?php echo self::is_free_mode() ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                    <?php echo esc_html(self::is_free_mode() ? __('Upgrade to Pro', 'one-page-quick-checkout-for-woocommerce-pro') : __('Activate License', 'one-page-quick-checkout-for-woocommerce-pro')); ?>
                </a>
            </div>
        </section>
    <?php
    }

    private static function render_free_upgrade_promotion($context = '')
    {
        if (! self::is_free_mode()) {
            return;
        }

        $context = sanitize_key($context);
        $headline = __('Unlock full Cart Recovery with Pro', 'one-page-quick-checkout-for-woocommerce-pro');
        $copy = __('See every shopper detail, send automated recovery emails, review drop-off points, and connect advanced recovery controls from one place.', 'one-page-quick-checkout-for-woocommerce-pro');

        if ('template' === $context || 'email' === $context) {
            $headline = __('Build professional recovery emails in Pro', 'one-page-quick-checkout-for-woocommerce-pro');
            $copy = __('Use editable templates, cart-item layouts, previews, test sends, activity logs, and resend tools to turn abandoned carts into orders.', 'one-page-quick-checkout-for-woocommerce-pro');
        } elseif ('analytics' === $context || 'journey' === $context) {
            $headline = __('Find lost revenue faster with Pro insights', 'one-page-quick-checkout-for-woocommerce-pro');
            $copy = __('Unlock analytics, journey funnels, stage drop-off reports, and customer context so every abandoned cart has a clear next step.', 'one-page-quick-checkout-for-woocommerce-pro');
        } elseif ('settings' === $context) {
            $headline = __('Control recovery rules with Pro', 'one-page-quick-checkout-for-woocommerce-pro');
            $copy = __('Unlock high-value cart rules, product and category exclusions, role-based tracking control, free-cart tracking, and webhook automation.', 'one-page-quick-checkout-for-woocommerce-pro');
        }

        $features = array(
            array(
                'icon'  => 'dashicons-id',
                'title' => __('Customer contact details', 'one-page-quick-checkout-for-woocommerce-pro'),
                'copy'  => __('Email, phone, company, IP, address, device, browser, and order notes.', 'one-page-quick-checkout-for-woocommerce-pro'),
            ),
            array(
                'icon'  => 'dashicons-email-alt2',
                'title' => __('Automated recovery emails', 'one-page-quick-checkout-for-woocommerce-pro'),
                'copy'  => __('Template builder, preview, test send, activity tracking, resend, and retry tools.', 'one-page-quick-checkout-for-woocommerce-pro'),
            ),
            array(
                'icon'  => 'dashicons-chart-area',
                'title' => __('Analytics and journey funnel', 'one-page-quick-checkout-for-woocommerce-pro'),
                'copy'  => __('Revenue trends, stage drop-off, customer journey, and recovery performance.', 'one-page-quick-checkout-for-woocommerce-pro'),
            ),
            array(
                'icon'  => 'dashicons-admin-settings',
                'title' => __('Advanced recovery controls', 'one-page-quick-checkout-for-woocommerce-pro'),
                'copy'  => __('High-value thresholds, exclusions, role controls, webhooks, and free-cart tracking.', 'one-page-quick-checkout-for-woocommerce-pro'),
            ),
        );
    ?>
        <section class="onepaqucpro-cr-free-promo" aria-labelledby="onepaqucpro-cr-free-promo-title">
            <div class="onepaqucpro-cr-free-promo__content">
                <div class="onepaqucpro-cr-free-promo__copy">
                    <span class="onepaqucpro-cr-free-promo__eyebrow">
                        <span class="dashicons dashicons-star-filled" aria-hidden="true"></span>
                        <?php esc_html_e('Pro version recommended', 'one-page-quick-checkout-for-woocommerce-pro'); ?>
                    </span>
                    <h2 id="onepaqucpro-cr-free-promo-title"><?php echo esc_html($headline); ?></h2>
                    <p><?php echo esc_html($copy); ?></p>
                    <div class="onepaqucpro-cr-free-promo__actions">
                        <a class="button button-primary button-hero" href="<?php echo esc_url(self::get_upgrade_url()); ?>" target="_blank" rel="noopener noreferrer">
                            <span class="dashicons dashicons-external" aria-hidden="true"></span>
                            <?php esc_html_e('Upgrade to Pro', 'one-page-quick-checkout-for-woocommerce-pro'); ?>
                        </a>
                        <span><?php esc_html_e('Unlock email automation, contact details, analytics, and advanced rules.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                    </div>
                </div>
                <div class="onepaqucpro-cr-free-promo__features">
                    <?php foreach ($features as $feature) : ?>
                        <div class="onepaqucpro-cr-free-promo__feature">
                            <span class="dashicons <?php echo esc_attr($feature['icon']); ?>" aria-hidden="true"></span>
                            <div>
                                <strong><?php echo esc_html($feature['title']); ?></strong>
                                <small><?php echo esc_html($feature['copy']); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php
    }

    private static function get_pro_locked_class()
    {
        return self::is_locked_mode() ? 'is-pro-locked' : '';
    }

    private static function render_pro_setting_overlay()
    {
        if (! self::is_locked_mode()) {
            return;
        }
    ?>
        <div class="onepaqucpro-cr-pro-setting-lock">
            <span class="dashicons dashicons-lock" aria-hidden="true"></span>
            <strong><?php echo esc_html(self::get_locked_feature_label('Email automation')); ?></strong>
        </div>
    <?php
    }

    private static function render_settings_tab($settings)
    {
        $search_nonce = wp_create_nonce('onepaqucpro_cart_recovery_search');
        $role_options = self::get_role_options();
    ?>
        <section class="onepaqucpro-cr-section">
            <div class="plugincy_card onepaqucpro-cr-settings-card onepaqucpro-cr-settings-card--controls">
                <div class="onepaqucpro-cr-card-heading">
                    <div>
                        <h2><?php esc_html_e('Recovery Controls', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h2>
                        <p><?php esc_html_e('Control when carts are treated as lost, which customers are ignored, and which catalog items should never enter recovery.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                    </div>
                </div>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="onepaqucpro-cr-settings-form">
                    <input type="hidden" name="action" value="onepaqucpro_cart_recovery_save_settings">
                    <input type="hidden" name="settings_context" value="settings">
                    <?php wp_nonce_field('onepaqucpro_cart_recovery_save_settings'); ?>

                    <div class="onepaqucpro-cr-settings-panel">
                        <div class="onepaqucpro-cr-settings-option">
                            <div class="onepaqucpro-cr-settings-copy">
                                <h3><?php esc_html_e('Enable Cart Recovery', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                <p><?php esc_html_e('Start tracking recoverable carts and schedule follow-up emails for shoppers who leave checkout.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                            </div>
                            <div class="onepaqucpro-cr-settings-control">
                                <label class="switch">
                                    <input type="checkbox" name="settings[enabled]" value="1" <?php checked(! empty($settings['enabled'])); ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>

                        <div class="onepaqucpro-cr-settings-option <?php echo esc_attr(self::get_pro_locked_class()); ?>">
                            <div class="onepaqucpro-cr-settings-copy">
                                <h3><?php esc_html_e('Track Free Carts', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                <p><?php esc_html_e('Include zero-value carts in reporting and recovery automation.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                            </div>
                            <div class="onepaqucpro-cr-settings-control">
                                <label class="switch">
                                    <input type="checkbox" name="settings[track_free_carts]" value="1" <?php checked(! empty($settings['track_free_carts'])); ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <?php self::render_pro_setting_overlay(); ?>
                        </div>

                        <div class="onepaqucpro-cr-settings-option">
                            <div class="onepaqucpro-cr-settings-copy">
                                <h3><?php esc_html_e('Abandoned Cart Lost Time', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                <p><?php esc_html_e('Mark a cart recoverable after this many minutes without shopper activity.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                            </div>
                            <div class="onepaqucpro-cr-settings-control">
                                <div class="onepaqucpro-cr-unit-field onepaqucpro-cr-unit-field--compact">
                                    <input type="number" min="1" name="settings[inactivity_timeout]" value="<?php echo esc_attr($settings['inactivity_timeout']); ?>">
                                    <span><?php esc_html_e('minutes', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="onepaqucpro-cr-settings-option">
                            <div class="onepaqucpro-cr-settings-copy">
                                <h3><?php esc_html_e('Retention Period', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                <p><?php esc_html_e('Keep cart recovery records for this many days before cleanup.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                            </div>
                            <div class="onepaqucpro-cr-settings-control">
                                <div class="onepaqucpro-cr-unit-field onepaqucpro-cr-unit-field--compact">
                                    <input type="number" min="1" name="settings[retention_days]" value="<?php echo esc_attr($settings['retention_days']); ?>">
                                    <span><?php esc_html_e('days', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="onepaqucpro-cr-settings-option <?php echo esc_attr(self::get_pro_locked_class()); ?>">
                            <div class="onepaqucpro-cr-settings-copy">
                                <h3><?php esc_html_e('High Value Threshold', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                <p><?php esc_html_e('Highlight carts at or above this value for faster follow-up.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                            </div>
                            <div class="onepaqucpro-cr-settings-control">
                                <input type="number" min="0" step="0.01" name="settings[high_value_threshold]" value="<?php echo esc_attr($settings['high_value_threshold']); ?>">
                            </div>
                            <?php self::render_pro_setting_overlay(); ?>
                        </div>

                        <div class="onepaqucpro-cr-settings-option onepaqucpro-cr-settings-option--stacked <?php echo esc_attr(self::get_pro_locked_class()); ?>">
                            <div class="onepaqucpro-cr-settings-copy">
                                <h3><?php esc_html_e('Exclude Products', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                <p><?php esc_html_e('Ignore carts containing selected products. Type at least 3 characters to search.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                            </div>
                            <div class="onepaqucpro-cr-settings-control is-wide">
                                <select
                                    name="settings[excluded_product_ids][]"
                                    class="onepaqucpro-cr-enhanced-select"
                                    multiple
                                    data-cr-select2="ajax"
                                    data-cr-select2-action="onepaqucpro_cr_search_products"
                                    data-cr-select2-nonce="<?php echo esc_attr($search_nonce); ?>"
                                    data-minimum-input-length="3"
                                    data-placeholder="<?php esc_attr_e('Search products to exclude', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                                    <?php foreach (self::sanitize_integer_list($settings['excluded_product_ids']) as $product_id) : ?>
                                        <option value="<?php echo esc_attr($product_id); ?>" selected><?php echo esc_html(self::get_product_label($product_id)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php self::render_pro_setting_overlay(); ?>
                        </div>

                        <div class="onepaqucpro-cr-settings-option onepaqucpro-cr-settings-option--stacked <?php echo esc_attr(self::get_pro_locked_class()); ?>">
                            <div class="onepaqucpro-cr-settings-copy">
                                <h3><?php esc_html_e('Exclude Categories', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                <p><?php esc_html_e('Ignore carts containing products from selected categories. Type at least 3 characters to search.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                            </div>
                            <div class="onepaqucpro-cr-settings-control is-wide">
                                <select
                                    name="settings[excluded_category_ids][]"
                                    class="onepaqucpro-cr-enhanced-select"
                                    multiple
                                    data-cr-select2="ajax"
                                    data-cr-select2-action="onepaqucpro_cr_search_categories"
                                    data-cr-select2-nonce="<?php echo esc_attr($search_nonce); ?>"
                                    data-minimum-input-length="3"
                                    data-placeholder="<?php esc_attr_e('Search categories to exclude', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                                    <?php foreach (self::sanitize_integer_list($settings['excluded_category_ids']) as $category_id) : ?>
                                        <option value="<?php echo esc_attr($category_id); ?>" selected><?php echo esc_html(self::get_category_label($category_id)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php self::render_pro_setting_overlay(); ?>
                        </div>

                        <div class="onepaqucpro-cr-settings-option onepaqucpro-cr-settings-option--stacked <?php echo esc_attr(self::get_pro_locked_class()); ?>">
                            <div class="onepaqucpro-cr-settings-copy">
                                <h3><?php esc_html_e('Disable Tracking For', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                <p><?php esc_html_e('Logged-in users with these roles will not be included in abandonment tracking or recovery emails.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                            </div>
                            <div class="onepaqucpro-cr-settings-control is-wide">
                                <select
                                    name="settings[excluded_roles][]"
                                    class="onepaqucpro-cr-enhanced-select"
                                    multiple
                                    data-cr-select2="static"
                                    data-placeholder="<?php esc_attr_e('Select user roles', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                                    <?php foreach ($role_options as $role_key => $role_label) : ?>
                                        <option value="<?php echo esc_attr($role_key); ?>" <?php selected(in_array($role_key, (array) $settings['excluded_roles'], true)); ?>><?php echo esc_html($role_label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php self::render_pro_setting_overlay(); ?>
                        </div>

                        <div class="onepaqucpro-cr-settings-option onepaqucpro-cr-settings-option--stacked <?php echo esc_attr(self::get_pro_locked_class()); ?>">
                            <div class="onepaqucpro-cr-settings-copy">
                                <h3><?php esc_html_e('Webhook URL', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                <p><?php esc_html_e('Send cart recovery events to an external automation endpoint.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                            </div>
                            <div class="onepaqucpro-cr-settings-control is-wide">
                                <input type="url" name="settings[webhook_url]" value="<?php echo esc_attr($settings['webhook_url']); ?>" placeholder="<?php esc_attr_e('https://example.com/webhook', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                            </div>
                            <?php self::render_pro_setting_overlay(); ?>
                        </div>
                    </div>

                    <div class="onepaqucpro-cr-form-actions">
                        <button type="submit" class="button button-primary"><?php esc_html_e('Save Changes', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
                    </div>
                </form>
            </div>
        </section>
    <?php
    }

    private static function render_email_tab($settings, $templates, $carts)
    {
        $email_view = self::get_active_email_view();
        $template_context = 'templates' === $email_view ? self::get_template_table_context($templates, $carts) : array();
        $activity_context = 'activity' === $email_view ? self::get_email_activity_table_context($carts) : array();
    ?>
        <section class="onepaqucpro-cr-section">
            <div class="onepaqucpro-cr-subtabs" role="tablist" aria-label="<?php esc_attr_e('Email recovery sections', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                <a class="onepaqucpro-cr-subtab <?php echo 'templates' === $email_view ? 'is-active' : ''; ?>" role="tab" aria-selected="<?php echo 'templates' === $email_view ? 'true' : 'false'; ?>" href="<?php echo esc_url(self::get_page_url(array('tab' => 'email', 'cr_email_view' => 'templates'))); ?>">
                    <span class="dashicons dashicons-email-alt" aria-hidden="true"></span>
                    <?php esc_html_e('Templates', 'one-page-quick-checkout-for-woocommerce-pro'); ?>
                </a>
                <a class="onepaqucpro-cr-subtab <?php echo 'activity' === $email_view ? 'is-active' : ''; ?>" role="tab" aria-selected="<?php echo 'activity' === $email_view ? 'true' : 'false'; ?>" href="<?php echo esc_url(self::get_page_url(array('tab' => 'email', 'cr_email_view' => 'activity'))); ?>">
                    <span class="dashicons dashicons-backup" aria-hidden="true"></span>
                    <?php esc_html_e('Activities', 'one-page-quick-checkout-for-woocommerce-pro'); ?>
                </a>
                <a class="onepaqucpro-cr-subtab <?php echo 'settings' === $email_view ? 'is-active' : ''; ?>" role="tab" aria-selected="<?php echo 'settings' === $email_view ? 'true' : 'false'; ?>" href="<?php echo esc_url(self::get_page_url(array('tab' => 'email', 'cr_email_view' => 'settings'))); ?>">
                    <span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>
                    <?php esc_html_e('Settings', 'one-page-quick-checkout-for-woocommerce-pro'); ?>
                </a>
            </div>

            <?php if ('templates' === $email_view) : ?>
                <div class="plugincy_card onepaqucpro-cr-settings-card">
                    <div class="onepaqucpro-cr-card-heading">
                        <div>
                            <h2><?php esc_html_e('Email Templates', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h2>
                            <p><?php esc_html_e('Manage recovery emails, monitor performance, and control when each message is triggered.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                        </div>
                        <a class="button button-primary" href="<?php echo esc_url(self::get_template_page_url('new')); ?>"><?php esc_html_e('Add New Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?></a>
                    </div>

                    <?php self::render_template_list($template_context); ?>
                </div>
            <?php elseif ('activity' === $email_view) : ?>
                <?php self::render_email_activity_tab($activity_context); ?>
            <?php else : ?>
                <div class="plugincy_card onepaqucpro-cr-settings-card">
                    <div class="onepaqucpro-cr-card-heading">
                        <div>
                            <h2><?php esc_html_e('Email Delivery Settings', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h2>
                            <p><?php esc_html_e('Control sender details, send windows, tracking behavior, and domain-level email exclusions from a dedicated workspace.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                        </div>
                    </div>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="onepaqucpro-cr-settings-form">
                        <input type="hidden" name="action" value="onepaqucpro_cart_recovery_save_settings">
                        <input type="hidden" name="settings_context" value="email">
                        <?php wp_nonce_field('onepaqucpro_cart_recovery_save_settings'); ?>

                        <div class="onepaqucpro-cr-setting-grid">
                            <label>
                                <span><?php esc_html_e('Sender', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <select name="settings[sender]">
                                    <option value="default" <?php selected($settings['sender'], 'default'); ?>><?php esc_html_e('Default', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                                    <option value="wordpress" <?php selected($settings['sender'], 'wordpress'); ?>><?php esc_html_e('WordPress', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                                    <option value="store" <?php selected($settings['sender'], 'store'); ?>><?php esc_html_e('Store Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                                </select>
                            </label>
                            <label>
                                <span><?php esc_html_e('Sender Name', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <input type="text" name="settings[sender_name]" value="<?php echo esc_attr($settings['sender_name']); ?>" placeholder="<?php esc_attr_e('Store Team', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                            </label>
                            <label>
                                <span><?php esc_html_e('Reply-To', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <input type="email" name="settings[reply_to]" value="<?php echo esc_attr($settings['reply_to']); ?>" placeholder="<?php esc_attr_e('support@example.com', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                            </label>
                            <label>
                                <span><?php esc_html_e('Max Emails Per Cart', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <input type="number" min="1" name="settings[max_emails_per_cart]" value="<?php echo esc_attr($settings['max_emails_per_cart']); ?>">
                            </label>
                            <label>
                                <span><?php esc_html_e('Quiet Hours Start', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <input type="time" name="settings[send_window_start]" value="<?php echo esc_attr($settings['send_window_start']); ?>">
                            </label>
                            <label>
                                <span><?php esc_html_e('Quiet Hours End', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <input type="time" name="settings[send_window_end]" value="<?php echo esc_attr($settings['send_window_end']); ?>">
                            </label>
                            <label>
                                <span><?php esc_html_e('Excluded Domains', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <input type="text" name="settings[excluded_domains]" value="<?php echo esc_attr(implode(', ', $settings['excluded_domains'])); ?>" placeholder="<?php esc_attr_e('example.com, internal.test', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                            </label>
                            <label>
                                <span><?php esc_html_e('UTM Source', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <input type="text" name="settings[utm_source]" value="<?php echo esc_attr($settings['utm_source']); ?>">
                            </label>
                            <label>
                                <span><?php esc_html_e('UTM Medium', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <input type="text" name="settings[utm_medium]" value="<?php echo esc_attr($settings['utm_medium']); ?>">
                            </label>
                            <label>
                                <span><?php esc_html_e('UTM Campaign', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <input type="text" name="settings[utm_campaign]" value="<?php echo esc_attr($settings['utm_campaign']); ?>">
                            </label>
                        </div>

                        <div class="onepaqucpro-cr-setting-row">
                            <div>
                                <h3><?php esc_html_e('Quiet Hours', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                <p><?php esc_html_e('Only send recovery emails during the configured sending window.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="settings[quiet_hours_enabled]" value="1" <?php checked(! empty($settings['quiet_hours_enabled'])); ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <div class="onepaqucpro-cr-setting-row">
                            <div>
                                <h3><?php esc_html_e('Stop After Restore', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                <p><?php esc_html_e('Stop additional queued emails after the cart is restored.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="settings[stop_after_restore]" value="1" <?php checked(! empty($settings['stop_after_restore'])); ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <div class="onepaqucpro-cr-setting-row">
                            <div>
                                <h3><?php esc_html_e('Append UTM Parameters', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                <p><?php esc_html_e('Add UTM source, medium, and campaign tags to recovery links.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="settings[append_utm]" value="1" <?php checked(! empty($settings['append_utm'])); ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <div class="onepaqucpro-cr-setting-row">
                            <div>
                                <h3><?php esc_html_e('Open Tracking Pixel', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                <p><?php esc_html_e('Disable this if you want a privacy-first email flow without open tracking.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="settings[tracking_pixel_enabled]" value="1" <?php checked(! empty($settings['tracking_pixel_enabled'])); ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <div class="onepaqucpro-cr-form-actions">
                            <button type="submit" class="button button-primary"><?php esc_html_e('Save Changes', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
                        </div>
                    </form>
                </div>

                <div class="plugincy_card onepaqucpro-cr-settings-card">
                    <div class="onepaqucpro-cr-card-heading">
                        <div>
                            <h2><?php esc_html_e('Test Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h2>
                            <p><?php esc_html_e('Send any recovery template to a specific inbox using the latest tracked cart as sample data.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                        </div>
                    </div>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="onepaqucpro-cr-settings-form">
                        <input type="hidden" name="action" value="onepaqucpro_cart_recovery_send_test_email">
                        <?php wp_nonce_field('onepaqucpro_cart_recovery_send_test_email'); ?>
                        <div class="onepaqucpro-cr-setting-grid">
                            <label>
                                <span><?php esc_html_e('Template', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <select name="test_template_id">
                                    <?php foreach ($templates as $template) : ?>
                                        <option value="<?php echo esc_attr($template['id']); ?>"><?php echo esc_html($template['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <span><?php esc_html_e('Recipient Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <input type="email" name="test_recipient" value="<?php echo esc_attr(get_option('admin_email')); ?>">
                            </label>
                        </div>
                        <div class="onepaqucpro-cr-form-actions">
                            <button type="submit" class="button button-secondary"><?php esc_html_e('Send Test Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </section>
    <?php
    }

    private static function render_template_list($context)
    {
        $items           = $context['items'];
        $filters         = $context['filters'];
        $pagination      = $context['pagination'];
        $summary         = $context['summary'];
        $visible_columns = self::get_screen_option_visible_columns('email_templates');
    ?>
        <form method="get" class="onepaqucpro-cr-toolbar onepaqucpro-cr-toolbar--templates">
            <input type="hidden" name="page" value="<?php echo esc_attr(self::PAGE_SLUG); ?>">
            <input type="hidden" name="tab" value="email">
            <input type="hidden" name="cr_email_view" value="templates">
            <div class="onepaqucpro-cr-toolbar__filters">
                <input type="search" name="cr_template_search" value="<?php echo esc_attr($filters['search']); ?>" placeholder="<?php esc_attr_e('Search templates...', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                <select name="cr_template_status">
                    <option value="all" <?php selected($filters['status'], 'all'); ?>><?php esc_html_e('All Statuses', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                    <option value="enabled" <?php selected($filters['status'], 'enabled'); ?>><?php esc_html_e('Enabled', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                    <option value="disabled" <?php selected($filters['status'], 'disabled'); ?>><?php esc_html_e('Disabled', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                </select>
                <select name="cr_template_delay_unit">
                    <option value="all" <?php selected($filters['delay_unit'], 'all'); ?>><?php esc_html_e('All Timing', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                    <option value="minutes" <?php selected($filters['delay_unit'], 'minutes'); ?>><?php esc_html_e('Minutes', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                    <option value="hours" <?php selected($filters['delay_unit'], 'hours'); ?>><?php esc_html_e('Hours', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                    <option value="days" <?php selected($filters['delay_unit'], 'days'); ?>><?php esc_html_e('Days', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                </select>
                <button type="submit" class="button button-primary"><?php esc_html_e('Apply', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
                <a class="button button-secondary" href="<?php echo esc_url(self::get_page_url(array('tab' => 'email', 'cr_email_view' => 'templates'))); ?>"><?php esc_html_e('Reset', 'one-page-quick-checkout-for-woocommerce-pro'); ?></a>
            </div>
            <div class="onepaqucpro-cr-range-label">
                <?php echo esc_html(sprintf(_n('%s template', '%s templates', $summary['count'], 'one-page-quick-checkout-for-woocommerce-pro'), number_format_i18n($summary['count']))); ?>
            </div>
        </form>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="onepaqucpro-cr-template-form onepaqucpro-cr-template-form--list" data-cr-template-status-form>
            <input type="hidden" name="action" value="onepaqucpro_cart_recovery_save_templates">
            <?php wp_nonce_field('onepaqucpro_cart_recovery_save_templates'); ?>

            <div class="onepaqucpro-cr-template-table-wrap">
                <table class="widefat fixed striped onepaqucpro-cr-template-table onepaqucpro-cr-template-table--list">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                            <?php if (in_array('subject', $visible_columns, true)) : ?>
                                <th><?php esc_html_e('Subject', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                            <?php endif; ?>
                            <?php if (in_array('trigger_after', $visible_columns, true)) : ?>
                                <th class="onepaqucpro-cr-sortable"><?php echo wp_kses_post(self::get_sort_link('email_templates', 'trigger_after', __('Trigger After', 'one-page-quick-checkout-for-woocommerce-pro'), $filters['sort'], $filters['order'])); ?></th>
                            <?php endif; ?>
                            <?php if (in_array('sent', $visible_columns, true)) : ?>
                                <th class="onepaqucpro-cr-sortable"><?php echo wp_kses_post(self::get_sort_link('email_templates', 'sent', __('Sent', 'one-page-quick-checkout-for-woocommerce-pro'), $filters['sort'], $filters['order'])); ?></th>
                            <?php endif; ?>
                            <?php if (in_array('open_rate', $visible_columns, true)) : ?>
                                <th class="onepaqucpro-cr-sortable"><?php echo wp_kses_post(self::get_sort_link('email_templates', 'open_rate', __('Open Rate', 'one-page-quick-checkout-for-woocommerce-pro'), $filters['sort'], $filters['order'])); ?></th>
                            <?php endif; ?>
                            <?php if (in_array('click_rate', $visible_columns, true)) : ?>
                                <th class="onepaqucpro-cr-sortable"><?php echo wp_kses_post(self::get_sort_link('email_templates', 'click_rate', __('Click Rate', 'one-page-quick-checkout-for-woocommerce-pro'), $filters['sort'], $filters['order'])); ?></th>
                            <?php endif; ?>
                            <?php if (in_array('conversion_rate', $visible_columns, true)) : ?>
                                <th class="onepaqucpro-cr-sortable"><?php echo wp_kses_post(self::get_sort_link('email_templates', 'conversion_rate', __('Conversion Rate', 'one-page-quick-checkout-for-woocommerce-pro'), $filters['sort'], $filters['order'])); ?></th>
                            <?php endif; ?>
                            <?php if (in_array('unsubscribed', $visible_columns, true)) : ?>
                                <th class="onepaqucpro-cr-sortable"><?php echo wp_kses_post(self::get_sort_link('email_templates', 'unsubscribed', __('Unsubscribed', 'one-page-quick-checkout-for-woocommerce-pro'), $filters['sort'], $filters['order'])); ?></th>
                            <?php endif; ?>
                            <?php if (in_array('updated', $visible_columns, true)) : ?>
                                <th class="onepaqucpro-cr-sortable"><?php echo wp_kses_post(self::get_sort_link('email_templates', 'updated', __('Updated', 'one-page-quick-checkout-for-woocommerce-pro'), $filters['sort'], $filters['order'])); ?></th>
                            <?php endif; ?>
                            <?php if (in_array('status', $visible_columns, true)) : ?>
                                <th class="column-status"><?php esc_html_e('Status', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                            <?php endif; ?>
                            <?php if (in_array('actions', $visible_columns, true)) : ?>
                                <th><?php esc_html_e('Actions', 'one-page-quick-checkout-for-woocommerce-pro'); ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)) : ?>
                            <tr>
                                <td colspan="<?php echo esc_attr(1 + count($visible_columns)); ?>">
                                    <div class="onepaqucpro-cr-empty-state">
                                        <span class="dashicons dashicons-search" aria-hidden="true"></span>
                                        <p><?php esc_html_e('No email templates matched the current filters.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($items as $index => $row) : ?>
                            <?php
                            $template = $row['template'];
                            $edit_url = self::get_template_page_url($template['id']);
                            $preview_template_id = 'onepaqucpro-cr-template-preview-' . sanitize_html_class($template['id']);
                            ?>
                            <tr>
                                <td class="column-primary">
                                    <strong><a class="row-title" href="<?php echo esc_url($edit_url); ?>"><?php echo esc_html($template['name']); ?></a></strong>
                                    <span class="onepaqucpro-cr-meta"><?php echo esc_html($row['trigger_label'] . ' / ' . $row['status_label']); ?></span>
                                </td>
                                <?php if (in_array('subject', $visible_columns, true)) : ?>
                                    <td><?php echo esc_html($template['subject']); ?></td>
                                <?php endif; ?>
                                <?php if (in_array('trigger_after', $visible_columns, true)) : ?>
                                    <td><?php echo esc_html($row['trigger_label']); ?></td>
                                <?php endif; ?>
                                <?php if (in_array('sent', $visible_columns, true)) : ?>
                                    <td ><strong><?php echo esc_html(number_format_i18n($row['sent'])); ?></strong></td>
                                <?php endif; ?>
                                <?php if (in_array('open_rate', $visible_columns, true)) : ?>
                                    <td ><?php echo esc_html(self::format_percent($row['open_rate'])); ?></td>
                                <?php endif; ?>
                                <?php if (in_array('click_rate', $visible_columns, true)) : ?>
                                    <td ><?php echo esc_html(self::format_percent($row['click_rate'])); ?></td>
                                <?php endif; ?>
                                <?php if (in_array('conversion_rate', $visible_columns, true)) : ?>
                                    <td ><?php echo esc_html(self::format_percent($row['conversion_rate'])); ?></td>
                                <?php endif; ?>
                                <?php if (in_array('unsubscribed', $visible_columns, true)) : ?>
                                    <td ><?php echo esc_html(number_format_i18n($row['unsubscribed'])); ?></td>
                                <?php endif; ?>
                                <?php if (in_array('updated', $visible_columns, true)) : ?>
                                    <td><span class="onepaqucpro-cr-template-date"><?php echo esc_html(self::format_datetime($template['updated_at'])); ?></span></td>
                                <?php endif; ?>
                                <?php if (in_array('status', $visible_columns, true)) : ?>
                                    <td class="column-status">
                                        <label class="switch">
                                            <?php echo self::render_template_hidden_fields($template, $index); ?>
                                            <input type="checkbox" name="templates[<?php echo esc_attr($index); ?>][enabled]" value="1" <?php checked(! empty($template['enabled'])); ?> data-cr-template-autosave>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                <?php endif; ?>
                                <?php if (in_array('actions', $visible_columns, true)) : ?>
                                    <td class="onepaqucpro-cr-template-actions">
                                        <button type="button" class="onepaqucpro-cr-template-action-icon is-preview onepaqucpro-cr-open-modal" data-template="<?php echo esc_attr($preview_template_id); ?>" title="<?php esc_attr_e('Preview', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                                            <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                                            <span class="screen-reader-text"><?php esc_html_e('Preview', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                        </button>
                                        <a class="onepaqucpro-cr-template-action-icon" href="<?php echo esc_url($edit_url); ?>" title="<?php esc_attr_e('Edit', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                                            <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                                            <span class="screen-reader-text"><?php esc_html_e('Edit', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                        </a>
                                        <a class="onepaqucpro-cr-template-action-icon" href="<?php echo esc_url(self::get_template_action_url($template['id'], 'duplicate')); ?>" title="<?php esc_attr_e('Duplicate', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                                            <span class="dashicons dashicons-admin-page" aria-hidden="true"></span>
                                            <span class="screen-reader-text"><?php esc_html_e('Duplicate', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                        </a>
                                        <a class="onepaqucpro-cr-template-action-icon is-delete" href="<?php echo esc_url(self::get_template_action_url($template['id'], 'delete')); ?>" title="<?php esc_attr_e('Delete', 'one-page-quick-checkout-for-woocommerce-pro'); ?>" data-cr-confirm="<?php esc_attr_e('Delete this email template?', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                                            <span class="dashicons dashicons-trash" aria-hidden="true"></span>
                                            <span class="screen-reader-text"><?php esc_html_e('Delete', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
        <?php self::render_pagination('email_templates', $pagination, array(
            'cr_email_view',
            'cr_template_status',
            'cr_template_delay_unit',
            'cr_template_search',
            'cr_template_sort',
            'cr_template_order',
        )); ?>
        <?php foreach ($items as $row) : ?>
            <?php self::render_template_preview_template('onepaqucpro-cr-template-preview-' . sanitize_html_class($row['template']['id']), $row['template']); ?>
        <?php endforeach; ?>
    <?php
    }

    private static function get_template_page_url($template_id = 'new', $args = array())
    {
        $base = array(
            'page' => self::TEMPLATE_PAGE_SLUG,
            'template_id' => $template_id ? $template_id : 'new',
        );

        return add_query_arg(array_merge($base, $args), admin_url('admin.php'));
    }

    private static function get_template_action_url($template_id, $action)
    {
        $template_id = sanitize_key($template_id);
        $action      = sanitize_key($action);
        $url         = add_query_arg(
            array(
                'action'          => 'onepaqucpro_cart_recovery_template_action',
                'template_id'     => $template_id,
                'template_action' => $action,
            ),
            admin_url('admin-post.php')
        );

        return wp_nonce_url($url, 'onepaqucpro_cart_recovery_template_action_' . $template_id . '_' . $action);
    }

    private static function render_template_hidden_fields($template, $index)
    {
        $template = is_array($template) ? $template : array();
        $index    = absint($index);

        return sprintf(
            '<input type="hidden" name="templates[%1$d][id]" value="%2$s"><input type="hidden" name="templates[%1$d][enabled]" value="0">',
            $index,
            esc_attr(isset($template['id']) ? $template['id'] : '')
        );
    }

    private static function format_template_delay($template)
    {
        $value = max(1, absint(isset($template['delay_value']) ? $template['delay_value'] : 1));
        $unit  = self::sanitize_choice(isset($template['delay_unit']) ? $template['delay_unit'] : 'hours', array('minutes', 'hours', 'days'), 'hours');

        switch ($unit) {
            case 'minutes':
                return sprintf(
                    /* translators: %d: number of minutes. */
                    _n('After %d minute', 'After %d minutes', $value, 'one-page-quick-checkout-for-woocommerce-pro'),
                    $value
                );
            case 'days':
                return sprintf(
                    /* translators: %d: number of days. */
                    _n('After %d day', 'After %d days', $value, 'one-page-quick-checkout-for-woocommerce-pro'),
                    $value
                );
            case 'hours':
            default:
                return sprintf(
                    /* translators: %d: number of hours. */
                    _n('After %d hour', 'After %d hours', $value, 'one-page-quick-checkout-for-woocommerce-pro'),
                    $value
                );
        }
    }

    private static function template_delay_seconds($template)
    {
        $value = max(1, absint(isset($template['delay_value']) ? $template['delay_value'] : 1));
        $unit  = self::sanitize_choice(isset($template['delay_unit']) ? $template['delay_unit'] : 'hours', array('minutes', 'hours', 'days'), 'hours');

        switch ($unit) {
            case 'minutes':
                return $value * MINUTE_IN_SECONDS;
            case 'days':
                return $value * DAY_IN_SECONDS;
            case 'hours':
            default:
                return $value * HOUR_IN_SECONDS;
        }
    }

    private static function get_template_for_edit($templates, $id)
    {
        if ('new' === $id) {
            $settings = self::get_settings();
            return wp_parse_args(array(
                'id'            => '',
                'name'          => __('Custom Cart Recovery Email', 'one-page-quick-checkout-for-woocommerce-pro'),
                'delay_value'   => 24,
                'delay_unit'    => 'hours',
                'discount_code' => '',
                'from_email'    => self::get_default_from_email($settings),
                'subject'       => __('Complete your purchase — your cart is ready', 'one-page-quick-checkout-for-woocommerce-pro'),
                'heading'       => __('We saved your cart', 'one-page-quick-checkout-for-woocommerce-pro'),
                'send_to'       => 'customer',
                'custom_recipient' => '',
                'cart_items_layout' => 'table',
                'message'       => self::get_default_message_template(),
                'enabled'       => 1,
                'updated_at'    => current_time('mysql'),
            ), self::get_default_template_shape());
        }

        foreach ($templates as $template) {
            if (! empty($template['id']) && $template['id'] === $id) {
                return wp_parse_args($template, self::get_default_template_shape());
            }
        }

        return wp_parse_args(array(), self::get_default_template_shape());
    }

    private static function get_default_template_shape()
    {
        $settings = self::get_settings();
        return array(
            'id'            => '',
            'name'          => '',
            'delay_value'   => 60,
            'delay_unit'    => 'minutes',
            'subject'       => '',
            'discount_code' => '',
            'from_email'    => self::get_default_from_email($settings),
            'heading'       => '',
            'send_to'       => 'customer',
            'custom_recipient' => '',
            'cart_items_layout' => 'table',
            'message'       => '',
            'enabled'       => 1,
            'updated_at'    => current_time('mysql'),
        );
    }

    private static function get_default_from_email($settings)
    {
        $sender = isset($settings['sender']) ? $settings['sender'] : 'default';
        if ('store' === $sender && function_exists('get_option')) {
            $store_email = get_option('woocommerce_email_from_address');
            if ($store_email) {
                return $store_email;
            }
        }

        $admin_email = get_option('admin_email');
        return $admin_email ? $admin_email : '';
    }

    private static function get_store_email()
    {
        $store_email = get_option('woocommerce_email_from_address');

        return $store_email ? sanitize_email($store_email) : sanitize_email(get_option('admin_email'));
    }

    private static function get_default_message_template($template_id = '')
    {
        $template_id = sanitize_key($template_id);
        $copy = array(
            'eyebrow' => __('Cart saved for you', 'one-page-quick-checkout-for-woocommerce-pro'),
            'title'   => __('Your checkout is ready whenever you are', 'one-page-quick-checkout-for-woocommerce-pro'),
            'intro'   => __('We kept your selected items together so you can return without starting over.', 'one-page-quick-checkout-for-woocommerce-pro'),
            'note'    => __('Complete your order while the items are still available.', 'one-page-quick-checkout-for-woocommerce-pro'),
            'button'  => __('Resume Checkout', 'one-page-quick-checkout-for-woocommerce-pro'),
        );

        if ('value_reinforcement' === $template_id) {
            $copy = array(
                'eyebrow' => __('Still deciding?', 'one-page-quick-checkout-for-woocommerce-pro'),
                'title'   => __('Your picks are still waiting', 'one-page-quick-checkout-for-woocommerce-pro'),
                'intro'   => __('Here is a quick reminder of what you saved, including the current cart total.', 'one-page-quick-checkout-for-woocommerce-pro'),
                'note'    => __('Return to checkout to finish with your saved details.', 'one-page-quick-checkout-for-woocommerce-pro'),
                'button'  => __('Review My Cart', 'one-page-quick-checkout-for-woocommerce-pro'),
            );
        } elseif ('final_attempt' === $template_id) {
            $copy = array(
                'eyebrow' => __('Final reminder', 'one-page-quick-checkout-for-woocommerce-pro'),
                'title'   => __('Your saved cart may expire soon', 'one-page-quick-checkout-for-woocommerce-pro'),
                'intro'   => __('This is the last reminder for the items you left at checkout.', 'one-page-quick-checkout-for-woocommerce-pro'),
                'note'    => __('Use the secure link below if you still want to complete this purchase.', 'one-page-quick-checkout-for-woocommerce-pro'),
                'button'  => __('Finish Checkout', 'one-page-quick-checkout-for-woocommerce-pro'),
            );
        }

        return wp_kses_post(
            '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%;max-width:640px;margin:0 auto;font-family:Arial,Helvetica,sans-serif;color:#111827;">' .
            '<tr><td style="padding:8px 0 0;">' .
            '<p style="margin:0 0 12px;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#2563eb;">' . esc_html($copy['eyebrow']) . '</p>' .
            '<h2 style="margin:0 0 12px;font-size:28px;line-height:1.25;color:#111827;">' . esc_html($copy['title']) . '</h2>' .
            '<p style="margin:0 0 22px;font-size:16px;line-height:1.65;color:#4b5563;">' . esc_html($copy['intro']) . '</p>' .
            '<div style="margin:0 0 22px;">{cart_items}</div>' .
            '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%;margin:0 0 24px;border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb;">' .
            '<tr><td style="padding:16px 0;font-size:14px;font-weight:700;color:#374151;">' . esc_html__('Cart total', 'one-page-quick-checkout-for-woocommerce-pro') . '</td><td align="right" style="padding:16px 0;font-size:18px;font-weight:800;color:#111827;">{cart_total}</td></tr>' .
            '</table>' .
            '<p style="margin:0 0 18px;"><a href="{cart_link}" style="display:inline-block;padding:13px 22px;border-radius:6px;background:#2563eb;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;">' . esc_html($copy['button']) . '</a></p>' .
            '<p style="margin:0 0 18px;font-size:14px;line-height:1.6;color:#4b5563;">' . esc_html($copy['note']) . '</p>' .
            '<p style="margin:0 0 8px;font-size:14px;line-height:1.6;color:#6b7280;">' . esc_html__('If you already completed your purchase, you can ignore this email.', 'one-page-quick-checkout-for-woocommerce-pro') . '</p>' .
            '<p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#6b7280;">' . esc_html__('Thanks,', 'one-page-quick-checkout-for-woocommerce-pro') . '<br>{sitename}</p>' .
            '<p style="margin:0;font-size:12px;color:#9ca3af;"><a href="{unsubscribe_link}" style="color:#6b7280;text-decoration:underline;">' . esc_html__('Unsubscribe', 'one-page-quick-checkout-for-woocommerce-pro') . '</a></p>' .
            '</td></tr></table>'
        );
    }

    private static function render_template_editor($template)
    {
        $cart_items_layout  = self::sanitize_cart_items_layout(isset($template['cart_items_layout']) ? $template['cart_items_layout'] : 'table');
        $layout_options     = self::get_cart_items_layout_options();
        $preview_template_id = 'onepaqucpro-cr-template-preview-' . sanitize_html_class($template['id'] ? $template['id'] : 'new');
    ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="onepaqucpro-cr-template-form onepaqucpro-cr-template-form--editor">
            <input type="hidden" name="action" value="onepaqucpro_cart_recovery_save_template">
            <?php wp_nonce_field('onepaqucpro_cart_recovery_save_template'); ?>
            <input type="hidden" name="template[id]" value="<?php echo esc_attr($template['id']); ?>">

            <div class="onepaqucpro-cr-template-layout">
                <div class="onepaqucpro-cr-template-main">
                    <div class="onepaqucpro-cr-template-card onepaqucpro-cr-template-card--content plugincy_card">
                        <div class="onepaqucpro-cr-template-card__head">
                            <h2><?php esc_html_e('Email Content', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h2>
                            <p><?php esc_html_e('Write the email that will be sent to shoppers. Use merge tags like {cart_link}.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                        </div>

                        <div class="onepaqucpro-cr-template-fields">
                            <label class="onepaqucpro-cr-template-field--name">
                                <span><?php esc_html_e('Email Name', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <input type="text" name="template[name]" value="<?php echo esc_attr($template['name']); ?>" placeholder="<?php esc_attr_e('Internal name (shown in admin)', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                            </label>

                            <label class="onepaqucpro-cr-template-field--subject">
                                <span><?php esc_html_e('Subject', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <input type="text" name="template[subject]" value="<?php echo esc_attr($template['subject']); ?>">
                            </label>

                            <label class="onepaqucpro-cr-template-field--heading">
                                <span><?php esc_html_e('Heading', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <input type="text" name="template[heading]" value="<?php echo esc_attr($template['heading']); ?>">
                            </label>
                        </div>

                        <div class="onepaqucpro-cr-template-editor__editor">
                            <div class="onepaqucpro-cr-template-builder">
                                <div class="onepaqucpro-cr-template-builder__head">
                                    <div>
                                        <h3><?php esc_html_e('Cart Items Layout', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                        <p><?php esc_html_e('Control how product rows render when you place {cart_items} in the email body.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                                    </div>
                                    <span class="onepaqucpro-cr-template-builder__tag"><code>{cart_items}</code></span>
                                </div>
                                <div class="onepaqucpro-cr-layout-options">
                                    <?php foreach ($layout_options as $layout_key => $layout_option) : ?>
                                        <label class="onepaqucpro-cr-layout-option is-<?php echo esc_attr($layout_key); ?> <?php echo $cart_items_layout === $layout_key ? 'is-selected' : ''; ?>">
                                            <input type="radio" name="template[cart_items_layout]" value="<?php echo esc_attr($layout_key); ?>" <?php checked($cart_items_layout, $layout_key); ?>>
                                            <span class="onepaqucpro-cr-layout-option__check dashicons dashicons-yes" aria-hidden="true"></span>
                                            <span class="onepaqucpro-cr-layout-option__top">
                                                <span class="onepaqucpro-cr-layout-option__icon dashicons <?php echo esc_attr($layout_option['icon']); ?>" aria-hidden="true"></span>
                                                <span class="onepaqucpro-cr-layout-option__copy">
                                                    <strong><?php echo esc_html($layout_option['label']); ?></strong>
                                                    <small><?php echo esc_html($layout_option['description']); ?></small>
                                                </span>
                                            </span>
                                            <span class="onepaqucpro-cr-layout-option__preview onepaqucpro-cr-layout-option__preview--<?php echo esc_attr($layout_key); ?>" aria-hidden="true">
                                                <?php if ('table' === $layout_key) : ?>
                                                    <span class="onepaqucpro-cr-layout-preview__row is-head"><span></span><span></span><span></span></span>
                                                    <span class="onepaqucpro-cr-layout-preview__row"><span></span><span></span><span></span></span>
                                                    <span class="onepaqucpro-cr-layout-preview__row"><span></span><span></span><span></span></span>
                                                <?php elseif ('list' === $layout_key) : ?>
                                                    <span class="onepaqucpro-cr-layout-preview__bullet"><span></span><span></span></span>
                                                    <span class="onepaqucpro-cr-layout-preview__bullet"><span></span><span></span></span>
                                                    <span class="onepaqucpro-cr-layout-preview__bullet"><span></span><span></span></span>
                                                <?php elseif ('compact' === $layout_key) : ?>
                                                    <span class="onepaqucpro-cr-layout-preview__stack"><span></span><span></span></span>
                                                    <span class="onepaqucpro-cr-layout-preview__stack"><span></span><span></span></span>
                                                <?php else : ?>
                                                    <span class="onepaqucpro-cr-layout-preview__card"><span></span><span></span></span>
                                                    <span class="onepaqucpro-cr-layout-preview__card"><span></span><span></span></span>
                                                <?php endif; ?>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <?php
                            wp_editor(
                                $template['message'],
                                'onepaqucpro_cr_template_message',
                                array(
                                    'textarea_name' => 'template[message]',
                                    'textarea_rows' => 14,
                                    'media_buttons' => true,
                                    'teeny' => false,
                                    'tinymce' => true,
                                    'quicktags' => true,
                                )
                            );
                            ?>
                            <p class="description onepaqucpro-cr-template-tags-hint">
                                <?php
                                echo wp_kses_post(sprintf(
                                    __('Available tags: %s', 'one-page-quick-checkout-for-woocommerce-pro'),
                                    self::render_template_merge_tag_codes()
                                ));
                                ?>
                            </p>
                        </div>
                    </div>

                    <div class="onepaqucpro-cr-template-card plugincy_card">
                        <div class="onepaqucpro-cr-template-card__head">
                            <h2><?php esc_html_e('Timing & Delivery', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h2>
                            <p><?php esc_html_e('Control when this email is sent and who receives it.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                        </div>

                        <div class="onepaqucpro-cr-template-fields onepaqucpro-cr-template-fields--grid">
                            <label>
                                <span><?php esc_html_e('Email Delay', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <div class="onepaqucpro-cr-template-context">
                                    <input type="number" min="1" name="template[delay_value]" value="<?php echo esc_attr($template['delay_value']); ?>">
                                    <select name="template[delay_unit]">
                                        <option value="minutes" <?php selected($template['delay_unit'], 'minutes'); ?>><?php esc_html_e('minutes', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                                        <option value="hours" <?php selected($template['delay_unit'], 'hours'); ?>><?php esc_html_e('hours', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                                        <option value="days" <?php selected($template['delay_unit'], 'days'); ?>><?php esc_html_e('days', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                                    </select>
                                </div>
                                <small><?php esc_html_e('Time after abandonment before this email is sent.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></small>
                            </label>

                            <label>
                                <span><?php esc_html_e('From', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <input type="email" name="template[from_email]" value="<?php echo esc_attr($template['from_email']); ?>" placeholder="<?php esc_attr_e('contact@yourstore.com', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                            </label>

                            <label>
                                <span><?php esc_html_e('Send To', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <select name="template[send_to]">
                                    <option value="customer" <?php selected($template['send_to'], 'customer'); ?>><?php esc_html_e('Customer email', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                                    <option value="custom" <?php selected($template['send_to'], 'custom'); ?>><?php esc_html_e('Custom email', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                                </select>
                                <small><?php esc_html_e('Use "Custom" to send test emails to a specific address.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></small>
                            </label>

                            <label>
                                <span><?php esc_html_e('Custom Recipient', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <input type="email" name="template[custom_recipient]" value="<?php echo esc_attr(isset($template['custom_recipient']) ? $template['custom_recipient'] : ''); ?>" placeholder="<?php esc_attr_e('samplecustomer@yourstore.com', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                            </label>

                            <label>
                                <span><?php esc_html_e('Discount Code', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <input type="text" name="template[discount_code]" value="<?php echo esc_attr($template['discount_code']); ?>" placeholder="<?php esc_attr_e('Optional coupon code', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                                <small><?php esc_html_e('Optional: include or apply a coupon when the cart is restored.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></small>
                            </label>
                        </div>
                    </div>
                </div>

                <aside class="onepaqucpro-cr-template-sidebar">
                    <div class="onepaqucpro-cr-template-card onepaqucpro-cr-template-card--sticky">
                        <div class="onepaqucpro-cr-template-card__head">
                            <h2><?php esc_html_e('Status', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h2>
                            <p><?php esc_html_e('Enable this email and save your changes.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                        </div>

                        <div class="onepaqucpro-cr-template-sidebar__row">
                            <span><?php esc_html_e('Enabled', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                            <span class="switch">
                                <input type="checkbox" name="template[enabled]" value="1" <?php checked(! empty($template['enabled'])); ?>>
                                <span class="slider round"></span>
                            </span>
                        </div>

                        <?php if (! empty($template['id'])) : ?>
                            <div class="onepaqucpro-cr-template-sidebar__meta">
                                <div><span><?php esc_html_e('Template ID', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span><code><?php echo esc_html($template['id']); ?></code></div>
                                <div><span><?php esc_html_e('Last updated', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span><strong><?php echo esc_html(self::format_datetime($template['updated_at'])); ?></strong></div>
                            </div>
                        <?php endif; ?>

                        <div class="onepaqucpro-cr-template-sidebar__actions">
                            <button type="button" class="button button-secondary onepaqucpro-cr-open-modal onepaqucpro-cr-template-preview-button" data-template="<?php echo esc_attr($preview_template_id); ?>" data-cr-template-builder-preview="1">
                                <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                                <?php esc_html_e('Preview Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?>
                            </button>
                            <button type="submit" class="button button-primary button-hero"><?php esc_html_e('Save Template', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
                        </div>
                    </div>

                    <div class="onepaqucpro-cr-template-card">
                        <div class="onepaqucpro-cr-template-card__head">
                            <h2><?php esc_html_e('Merge Tags', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h2>
                            <p><?php esc_html_e('Copy and paste into subject or message.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                        </div>
                        <div class="onepaqucpro-cr-template-taglist">
                            <?php echo self::render_template_merge_tag_groups(); ?>
                        </div>
                    </div>
                </aside>
            </div>
        </form>
        <?php self::render_template_preview_template($preview_template_id, $template); ?>
    <?php
    }

    private static function render_range_toolbar($tab, $range)
    {
    ?>
        <form method="get" class="onepaqucpro-cr-toolbar onepaqucpro-cr-toolbar--range">
            <input type="hidden" name="page" value="<?php echo esc_attr(self::PAGE_SLUG); ?>">
            <input type="hidden" name="tab" value="<?php echo esc_attr($tab); ?>">
            <div class="onepaqucpro-cr-toolbar__filters">
                <select name="cr_period">
                    <option value="month_to_date" <?php selected($range['period'], 'month_to_date'); ?>><?php esc_html_e('Month to Date', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                    <option value="last_7_days" <?php selected($range['period'], 'last_7_days'); ?>><?php esc_html_e('Last 7 Days', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                    <option value="last_30_days" <?php selected($range['period'], 'last_30_days'); ?>><?php esc_html_e('Last 30 Days', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                    <option value="custom" <?php selected($range['period'], 'custom'); ?>><?php esc_html_e('Custom', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                </select>
                <input type="date" name="cr_date_from" value="<?php echo esc_attr($range['from']); ?>">
                <input type="date" name="cr_date_to" value="<?php echo esc_attr($range['to']); ?>">
                <label class="onepaqucpro-cr-inline-field">
                    <span><?php esc_html_e('Compared to', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                    <select name="cr_compare">
                        <option value="previous_period" <?php selected($range['compare'], 'previous_period'); ?>><?php esc_html_e('Previous period', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                        <option value="none" <?php selected($range['compare'], 'none'); ?>><?php esc_html_e('No comparison', 'one-page-quick-checkout-for-woocommerce-pro'); ?></option>
                    </select>
                </label>
                <button type="submit" class="button button-primary"><?php esc_html_e('Apply', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
            </div>
            <div class="onepaqucpro-cr-range-label"><?php echo esc_html($range['label']); ?></div>
        </form>
    <?php
    }

    private static function render_quick_select($from_key, $to_key, $base_args, $preserved_keys, $current_from, $current_to)
    {
        $ranges    = self::get_quick_select_ranges();
        $preserved = self::get_preserved_query_args($preserved_keys);
        ?>
        <div class="onepaqucpro-cr-quick-select" aria-label="<?php esc_attr_e('Quick date range select', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
            <span class="onepaqucpro-cr-quick-select__label"><?php esc_html_e('Quick Select', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
            <div class="onepaqucpro-cr-quick-select__actions">
                <?php foreach ($ranges as $range_key => $range) : ?>
                    <?php
                    $args = array_merge($base_args, $preserved, array(
                        $from_key => $range['from'],
                        $to_key   => $range['to'],
                    ));
                    $classes = 'onepaqucpro-cr-quick-select__button';
                    if ($current_from === $range['from'] && $current_to === $range['to']) {
                        $classes .= ' is-active';
                    }
                    ?>
                    <a class="<?php echo esc_attr($classes); ?>" href="<?php echo esc_url(self::get_page_url($args)); ?>" data-range="<?php echo esc_attr($range_key); ?>"><?php echo esc_html($range['label']); ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    private static function render_chart_card($title, $description, $config, $embedded = false)
    {
        $classes = 'plugincy_card onepaqucpro-cr-chart-card';
        if ($embedded) {
            $classes .= ' is-embedded';
        }
    ?>
        <article class="<?php echo esc_attr($classes); ?>">
            <div class="onepaqucpro-cr-card-heading">
                <div>
                    <h3><?php echo esc_html($title); ?></h3>
                    <p><?php echo esc_html($description); ?></p>
                </div>
            </div>
            <div class="onepaqucpro-cr-chart" data-chart-config="<?php echo esc_attr(wp_json_encode($config)); ?>"></div>
        </article>
    <?php
    }

    private static function render_modal_shell()
    {
    ?>
        <div class="onepaqucpro-cr-modal" hidden>
            <div class="onepaqucpro-cr-modal__backdrop" data-cr-modal-close></div>
            <div class="onepaqucpro-cr-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="onepaqucpro-cr-modal-title">
                <button type="button" class="onepaqucpro-cr-modal__close" data-cr-modal-close aria-label="<?php esc_attr_e('Close cart details', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
                <div class="onepaqucpro-cr-modal__content"></div>
            </div>
        </div>
        <?php
    }

    private static function render_detail_templates($carts)
    {
        foreach ($carts as $cart) {
            $tab_suffix = sanitize_html_class((string) $cart['id']);
            $tab_ids = array(
                'overview' => 'onepaqucpro-cr-tab-overview-' . $tab_suffix,
                'items'    => 'onepaqucpro-cr-tab-items-' . $tab_suffix,
                'journey'  => 'onepaqucpro-cr-tab-journey-' . $tab_suffix,
                'emails'   => 'onepaqucpro-cr-tab-emails-' . $tab_suffix,
                'admin'    => 'onepaqucpro-cr-tab-admin-' . $tab_suffix,
            );
            $item_count = isset($cart['items']) && is_array($cart['items']) ? count($cart['items']) : 0;
            $journey_count = isset($cart['journey']) && is_array($cart['journey']) ? count($cart['journey']) : 0;
            $email_count = isset($cart['email_history']) && is_array($cart['email_history']) ? count($cart['email_history']) : 0;
            $device_type = isset($cart['device_type']) ? sanitize_key($cart['device_type']) : '';
            $device_icon = 'mobile' === $device_type ? 'dashicons-smartphone' : ('tablet' === $device_type ? 'dashicons-tablet' : 'dashicons-desktop');
            $note_form_id = 'onepaqucpro-cr-note-form-' . $tab_suffix;
            $activity_url = self::get_page_url(array(
                'tab'              => 'activity',
                'cr_activity_cart' => $cart['id'],
            ));
        ?>
            <template id="onepaqucpro-cr-detail-<?php echo esc_attr($cart['id']); ?>">
                <div class="onepaqucpro-cr-detail onepaqucpro-cr-cart-detail">
                    <div class="onepaqucpro-cr-detail__header onepaqucpro-cr-detail__header--cart">
                        <div class="onepaqucpro-cr-detail__identity">
                            <div class="onepaqucpro-cr-detail__identity-icon" aria-hidden="true">
                                <span class="dashicons dashicons-cart"></span>
                            </div>
                            <div>
                                <p class="onepaqucpro-cr-detail__eyebrow"><?php esc_html_e('Cart Details', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                                <h2 id="onepaqucpro-cr-modal-title"><?php echo esc_html($cart['customer_name']); ?></h2>
                                <p class="onepaqucpro-cr-detail__subtitle">
                                    <?php echo wp_kses_post(self::render_contact_value($cart['email'] ? $cart['email'] : __('No email captured', 'one-page-quick-checkout-for-woocommerce-pro'), '-', 'Email')); ?>
                                    <?php if (! self::is_locked_mode() && ! empty($cart['customer_phone'])) : ?>
                                        <span><?php echo esc_html($cart['customer_phone']); ?></span>
                                    <?php endif; ?>
                                </p>
                                <div class="onepaqucpro-cr-detail__meta-inline">
                                    <span><span class="dashicons dashicons-tag" aria-hidden="true"></span><code>#<?php echo esc_html($cart['id']); ?></code></span>
                                    <span><span class="dashicons dashicons-admin-users" aria-hidden="true"></span><?php echo wp_kses_post(self::is_locked_mode() ? self::render_locked_value('Customer type') : esc_html(ucfirst($cart['customer_type']))); ?></span>
                                    <?php if (! self::is_locked_mode() && ! empty($cart['customer_phone'])) : ?>
                                        <span><span class="dashicons dashicons-phone" aria-hidden="true"></span><?php echo esc_html($cart['customer_phone']); ?></span>
                                    <?php endif; ?>
                                    <span><span class="dashicons <?php echo esc_attr($device_icon); ?>" aria-hidden="true"></span><?php echo wp_kses_post(self::is_locked_mode() ? self::render_locked_value('Device / browser') : esc_html(ucfirst($cart['device_type']) . ' / ' . $cart['browser'])); ?></span>
                                </div>
                                <p><?php echo esc_html($cart['customer_name']); ?> &middot; <?php echo wp_kses_post(self::render_contact_value($cart['email'], '-', 'Email')); ?></p>
                            </div>
                        </div>
                        <div class="onepaqucpro-cr-detail__header-aside">
                            <?php echo wp_kses_post(self::render_cart_status_badges($cart)); ?>
                        </div>
                    </div>

                    <div class="onepaqucpro-cr-detail__summary">
                        <article class="onepaqucpro-cr-detail-stat">
                            <span class="onepaqucpro-cr-detail-stat__icon dashicons dashicons-money-alt" aria-hidden="true"></span>
                            <div>
                                <span class="onepaqucpro-cr-detail-stat__label"><?php esc_html_e('Cart Total', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <strong><?php echo wp_kses_post(self::format_currency($cart['cart_total'], $cart['currency'])); ?></strong>
                            </div>
                        </article>
                        <article class="onepaqucpro-cr-detail-stat">
                            <span class="onepaqucpro-cr-detail-stat__icon dashicons dashicons-products" aria-hidden="true"></span>
                            <div>
                                <span class="onepaqucpro-cr-detail-stat__label"><?php esc_html_e('Items', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <strong><?php echo esc_html(number_format_i18n($item_count)); ?></strong>
                            </div>
                        </article>
                        <article class="onepaqucpro-cr-detail-stat">
                            <span class="onepaqucpro-cr-detail-stat__icon dashicons dashicons-email-alt" aria-hidden="true"></span>
                            <div>
                                <span class="onepaqucpro-cr-detail-stat__label"><?php esc_html_e('Emails Sent', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <strong><?php echo self::is_locked_mode() ? wp_kses_post(self::render_locked_value('Emails sent')) : esc_html(number_format_i18n($email_count)); ?></strong>
                            </div>
                        </article>
                        <article class="onepaqucpro-cr-detail-stat">
                            <span class="onepaqucpro-cr-detail-stat__icon dashicons dashicons-clock" aria-hidden="true"></span>
                            <div>
                                <span class="onepaqucpro-cr-detail-stat__label"><?php esc_html_e('Last Activity', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <strong><?php echo esc_html(self::get_relative_time($cart['updated_at'])); ?></strong>
                            </div>
                        </article>
                    </div>

                    <div class="onepaqucpro-cr-tabs" data-cr-tabs>
                        <div class="onepaqucpro-cr-tabs__list" role="tablist" aria-label="<?php esc_attr_e('Cart detail sections', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                            <button type="button" class="onepaqucpro-cr-tabs__tab is-active" role="tab" aria-selected="true" aria-controls="<?php echo esc_attr($tab_ids['overview']); ?>" data-cr-tab-button="<?php echo esc_attr($tab_ids['overview']); ?>">
                                <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                                <span><?php esc_html_e('Overview', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                            </button>
                            <button type="button" class="onepaqucpro-cr-tabs__tab" role="tab" aria-selected="false" aria-controls="<?php echo esc_attr($tab_ids['items']); ?>" data-cr-tab-button="<?php echo esc_attr($tab_ids['items']); ?>" tabindex="-1">
                                <span class="dashicons dashicons-cart" aria-hidden="true"></span>
                                <span><?php esc_html_e('Items', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <span class="onepaqucpro-cr-tabs__count"><?php echo esc_html(number_format_i18n($item_count)); ?></span>
                            </button>
                            <button type="button" class="onepaqucpro-cr-tabs__tab" role="tab" aria-selected="false" aria-controls="<?php echo esc_attr($tab_ids['journey']); ?>" data-cr-tab-button="<?php echo esc_attr($tab_ids['journey']); ?>" tabindex="-1">
                                <span class="dashicons dashicons-chart-pie" aria-hidden="true"></span>
                                <span><?php esc_html_e('Totals', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                            </button>
                            <button type="button" class="onepaqucpro-cr-tabs__tab" role="tab" aria-selected="false" aria-controls="<?php echo esc_attr($tab_ids['emails']); ?>" data-cr-tab-button="<?php echo esc_attr($tab_ids['emails']); ?>" tabindex="-1">
                                <span class="dashicons dashicons-location-alt" aria-hidden="true"></span>
                                <span><?php esc_html_e('Journey', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <span class="onepaqucpro-cr-tabs__count"><?php echo esc_html(number_format_i18n($journey_count)); ?></span>
                            </button>
                            <button type="button" class="onepaqucpro-cr-tabs__tab" role="tab" aria-selected="false" aria-controls="<?php echo esc_attr($tab_ids['admin']); ?>" data-cr-tab-button="<?php echo esc_attr($tab_ids['admin']); ?>" tabindex="-1">
                                <span class="dashicons dashicons-backup" aria-hidden="true"></span>
                                <span><?php esc_html_e('History & Notes', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                <span class="onepaqucpro-cr-tabs__count"><?php echo esc_html(number_format_i18n($email_count)); ?></span>
                            </button>
                        </div>

                        <div class="onepaqucpro-cr-tabs__panels">
                            <section id="<?php echo esc_attr($tab_ids['overview']); ?>" class="onepaqucpro-cr-tabs__panel is-active" role="tabpanel" data-cr-tab-panel="<?php echo esc_attr($tab_ids['overview']); ?>">
                                <div class="onepaqucpro-cr-panel-grid">
                                    <div class="onepaqucpro-cr-detail__section">
                                        <h3><?php esc_html_e('Customer', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                        <dl class="onepaqucpro-cr-meta-grid">
                                            <div>
                                                <dt><?php esc_html_e('Name', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                <dd><?php echo esc_html($cart['customer_name']); ?></dd>
                                            </div>
                                            <div>
                                                <dt><?php esc_html_e('Email', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                <dd><?php echo wp_kses_post(self::render_contact_value($cart['email'] ? $cart['email'] : '-', '-', 'Email')); ?></dd>
                                            </div>
                                            <div>
                                                <dt><?php esc_html_e('Phone', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                <dd><?php echo wp_kses_post(self::render_contact_value($cart['customer_phone'] ? $cart['customer_phone'] : '-', '-', 'Phone')); ?></dd>
                                            </div>
                                            <div>
                                                <dt><?php esc_html_e('Company', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                <dd><?php echo wp_kses_post(self::render_contact_value($cart['customer_company'] ? $cart['customer_company'] : '-', '-', 'Company')); ?></dd>
                                            </div>
                                            <div>
                                                <dt><?php esc_html_e('Customer ID', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                <dd><?php echo wp_kses_post(self::render_contact_value($cart['customer_id'] ? $cart['customer_id'] : '-', '-', 'Customer ID')); ?></dd>
                                            </div>
                                            <div>
                                                <dt><?php esc_html_e('IP Address', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                <dd><?php echo wp_kses_post(self::render_contact_value($cart['ip_address'], '-', 'IP address')); ?></dd>
                                            </div>
                                            <div>
                                                <dt><?php esc_html_e('Customer Type', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                <dd><?php echo wp_kses_post(self::is_locked_mode() ? self::render_locked_value('Customer type') : esc_html(ucfirst($cart['customer_type']))); ?></dd>
                                            </div>
                                            <div>
                                                <dt><?php esc_html_e('Device / Browser', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                <dd><?php echo wp_kses_post(self::is_locked_mode() ? self::render_locked_value('Device / browser') : esc_html(ucfirst($cart['device_type']) . ' / ' . $cart['browser'])); ?></dd>
                                            </div>
                                            <div>
                                                <dt><?php esc_html_e('Billing Address', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                <dd><?php echo wp_kses_post(self::is_locked_mode() ? self::render_locked_value('Billing address') : esc_html(self::format_profile_address($cart['billing_address']))); ?></dd>
                                            </div>
                                            <div>
                                                <dt><?php esc_html_e('Shipping Address', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                <dd><?php echo wp_kses_post(self::is_locked_mode() ? self::render_locked_value('Shipping address') : esc_html(self::format_profile_address($cart['shipping_address']))); ?></dd>
                                            </div>
                                            <div>
                                                <dt><?php esc_html_e('Order Notes', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                <dd><?php echo wp_kses_post(self::render_contact_value($cart['order_notes'] ? $cart['order_notes'] : '-', '-', 'Order notes')); ?></dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </section>

                            <section id="<?php echo esc_attr($tab_ids['items']); ?>" class="onepaqucpro-cr-tabs__panel" role="tabpanel" data-cr-tab-panel="<?php echo esc_attr($tab_ids['items']); ?>" hidden>
                                <div class="onepaqucpro-cr-panel-grid">
                                    <div class="onepaqucpro-cr-detail__section">
                                        <h3><?php esc_html_e('Cart Items', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                        <div class="onepaqucpro-cr-item-list">
                                            <?php foreach ($cart['items'] as $item) : ?>
                                                <details class="onepaqucpro-cr-item-card">
                                                    <summary class="onepaqucpro-cr-item-card__summary">
                                                        <span class="onepaqucpro-cr-item-card__image">
                                                            <?php if (! empty($item['image_url'])) : ?>
                                                                <img src="<?php echo esc_url($item['image_url']); ?>" alt="<?php echo esc_attr($item['name']); ?>">
                                                            <?php else : ?>
                                                                <span class="dashicons dashicons-format-image" aria-hidden="true"></span>
                                                            <?php endif; ?>
                                                        </span>
                                                        <span class="onepaqucpro-cr-item-card__body">
                                                            <strong><?php echo esc_html($item['name']); ?></strong>
                                                            <span><?php echo esc_html(sprintf(__('Qty: %d', 'one-page-quick-checkout-for-woocommerce-pro'), $item['quantity'])); ?></span>
                                                            <?php if (! empty($item['sku'])) : ?>
                                                                <span><?php echo esc_html(sprintf(__('SKU: %s', 'one-page-quick-checkout-for-woocommerce-pro'), $item['sku'])); ?></span>
                                                            <?php endif; ?>
                                                        </span>
                                                        <span class="onepaqucpro-cr-item-card__price">
                                                            <span><?php esc_html_e('Line Total', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                                            <strong><?php echo wp_kses_post(self::format_currency($item['price'], $cart['currency'])); ?></strong>
                                                        </span>
                                                        <span class="onepaqucpro-cr-item-card__toggle" aria-hidden="true">
                                                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                                                        </span>
                                                    </summary>
                                                    <div class="onepaqucpro-cr-item-card__details">
                                                        <dl class="onepaqucpro-cr-meta-grid is-compact">
                                                            <div>
                                                                <dt><?php esc_html_e('Product ID', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                                <dd><?php echo esc_html($item['product_id'] ? '#' . $item['product_id'] : '-'); ?></dd>
                                                            </div>
                                                            <div>
                                                                <dt><?php esc_html_e('Variation ID', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                                <dd><?php echo esc_html($item['variation_id'] ? '#' . $item['variation_id'] : '-'); ?></dd>
                                                            </div>
                                                            <div>
                                                                <dt><?php esc_html_e('Unit Price', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                                <dd><?php echo wp_kses_post(self::format_currency($item['unit_price'], $cart['currency'])); ?></dd>
                                                            </div>
                                                            <div>
                                                                <dt><?php esc_html_e('Subtotal', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                                <dd><?php echo wp_kses_post(self::format_currency($item['subtotal'], $cart['currency'])); ?></dd>
                                                            </div>
                                                            <div>
                                                                <dt><?php esc_html_e('Discount', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                                <dd><?php echo wp_kses_post(self::format_currency($item['discount'], $cart['currency'])); ?></dd>
                                                            </div>
                                                            <div>
                                                                <dt><?php esc_html_e('Stock', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                                <dd><?php echo esc_html($item['stock_status'] ? ucfirst(str_replace('_', ' ', $item['stock_status'])) : '-'); ?></dd>
                                                            </div>
                                                            <div>
                                                                <dt><?php esc_html_e('Type', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                                <dd><?php echo esc_html($item['product_type'] ? ucfirst(str_replace('_', ' ', $item['product_type'])) : '-'); ?></dd>
                                                            </div>
                                                            <div>
                                                                <dt><?php esc_html_e('Categories', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                                <dd><?php echo esc_html(! empty($item['categories']) ? implode(', ', $item['categories']) : '-'); ?></dd>
                                                            </div>
                                                            <?php if (! empty($item['product_url'])) : ?>
                                                                <div>
                                                                    <dt><?php esc_html_e('Product Link', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                                    <dd><a href="<?php echo esc_url($item['product_url']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Open product', 'one-page-quick-checkout-for-woocommerce-pro'); ?></a></dd>
                                                                </div>
                                                            <?php endif; ?>
                                                        </dl>

                                                        <?php if (! empty($item['variation'])) : ?>
                                                            <div class="onepaqucpro-cr-item-card__meta-block">
                                                                <h4><?php esc_html_e('Selected Options', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h4>
                                                                <dl class="onepaqucpro-cr-meta-grid is-compact">
                                                                    <?php foreach ($item['variation'] as $label => $value) : ?>
                                                                        <div>
                                                                            <dt><?php echo esc_html(self::format_item_meta_label($label)); ?></dt>
                                                                            <dd><?php echo esc_html(is_scalar($value) ? (string) $value : wp_json_encode($value)); ?></dd>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </dl>
                                                            </div>
                                                        <?php endif; ?>

                                                        <?php if (! empty($item['cart_item_data'])) : ?>
                                                            <div class="onepaqucpro-cr-item-card__meta-block">
                                                                <h4><?php esc_html_e('Additional Item Data', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h4>
                                                                <code><?php echo esc_html(wp_json_encode($item['cart_item_data'])); ?></code>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </details>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section id="<?php echo esc_attr($tab_ids['journey']); ?>" class="onepaqucpro-cr-tabs__panel" role="tabpanel" data-cr-tab-panel="<?php echo esc_attr($tab_ids['journey']); ?>" hidden>
                                <div class="onepaqucpro-cr-detail__section">
                                    <h3><?php esc_html_e('Totals', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                    <dl class="onepaqucpro-cr-meta-grid">
                                        <div>
                                            <dt><?php esc_html_e('Cart Total', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                            <dd><?php echo wp_kses_post(self::format_currency($cart['cart_total'], $cart['currency'])); ?></dd>
                                        </div>
                                        <div>
                                            <dt><?php esc_html_e('Currency', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                            <dd><?php echo esc_html($cart['currency']); ?></dd>
                                        </div>
                                        <div>
                                            <dt><?php esc_html_e('Item Count', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                            <dd><?php echo esc_html(number_format_i18n($cart['item_count'])); ?></dd>
                                        </div>
                                        <div>
                                            <dt><?php esc_html_e('Last Updated', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                            <dd><?php echo esc_html(self::format_datetime($cart['updated_at'])); ?></dd>
                                        </div>
                                        <div>
                                            <dt><?php esc_html_e('Coupons', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                            <dd><?php echo esc_html(! empty($cart['coupon_codes']) ? implode(', ', $cart['coupon_codes']) : '-'); ?></dd>
                                        </div>
                                        <div>
                                            <dt><?php esc_html_e('Recovery Source', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                            <dd><?php echo esc_html($cart['recovery_source'] ? ucfirst(str_replace('_', ' ', $cart['recovery_source'])) : '-'); ?></dd>
                                        </div>
                                        <div>
                                            <dt><?php esc_html_e('Recovered Order', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                            <dd><?php echo esc_html($cart['recovered_order_id'] ? '#' . $cart['recovered_order_id'] : '-'); ?></dd>
                                        </div>
                                        <div>
                                            <dt><?php esc_html_e('Time To Recover', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                            <dd><?php echo esc_html(self::format_duration($cart['time_to_recovery_seconds'])); ?></dd>
                                        </div>
                                    </dl>
                                </div>
                            </section>

                            <section id="<?php echo esc_attr($tab_ids['emails']); ?>" class="onepaqucpro-cr-tabs__panel" role="tabpanel" data-cr-tab-panel="<?php echo esc_attr($tab_ids['emails']); ?>" hidden>
                                <div class="onepaqucpro-cr-detail__section">
                                    <h3><?php esc_html_e('Customer Journey', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                    <div class="onepaqucpro-cr-journey-list">
                                        <?php foreach ($cart['journey'] as $event) : ?>
                                            <article class="onepaqucpro-cr-journey-event">
                                                <div class="onepaqucpro-cr-journey-event__icon">
                                                    <span class="dashicons <?php echo esc_attr(self::get_event_icon($event['type'])); ?>"></span>
                                                </div>
                                                <div class="onepaqucpro-cr-journey-event__body">
                                                    <div class="onepaqucpro-cr-journey-event__header">
                                                        <strong><?php echo esc_html($event['title']); ?></strong>
                                                        <span><?php echo esc_html(self::get_relative_time($event['time'])); ?></span>
                                                    </div>
                                                    <dl class="onepaqucpro-cr-meta-grid is-compact">
                                                        <?php foreach ($event['meta'] as $label => $value) : ?>
                                                            <div>
                                                                <dt><?php echo esc_html($label); ?></dt>
                                                                <dd><?php echo wp_kses_post(self::render_detail_meta_value($label, $value)); ?></dd>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </dl>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </section>

                            <section id="<?php echo esc_attr($tab_ids['admin']); ?>" class="onepaqucpro-cr-tabs__panel" role="tabpanel" data-cr-tab-panel="<?php echo esc_attr($tab_ids['admin']); ?>" hidden>
                                <div class="onepaqucpro-cr-panel-grid onepaqucpro-cr-panel-grid--sidebar">
                                    <div class="onepaqucpro-cr-detail__section">
                                        <h3><?php esc_html_e('Email History', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                        <?php if (! empty($cart['email_history'])) : ?>
                                            <div class="onepaqucpro-cr-email-history">
                                                <?php foreach ($cart['email_history'] as $email) : ?>
                                                    <article class="onepaqucpro-cr-email-history__item">
                                                        <div>
                                                            <strong><?php echo esc_html($email['subject']); ?></strong>
                                                            <span><?php echo esc_html(self::format_datetime($email['sent_at'])); ?></span>
                                                        </div>
                                                        <?php echo wp_kses_post(self::render_email_status_badge($email['status'])); ?>
                                                    </article>
                                                <?php endforeach; ?>
                                            </div>
                                            <a class="button button-secondary button-view-activity" href="<?php echo esc_url($activity_url); ?>"><span class="dashicons dashicons-backup"></span> <?php esc_html_e('View Activity', 'one-page-quick-checkout-for-woocommerce-pro'); ?></a>
                                        <?php else : ?>
                                            <div class="onepaqucpro-cr-empty-state">
                                                <span class="dashicons dashicons-email-alt" aria-hidden="true"></span>
                                                <p><?php esc_html_e('No recovery emails have been sent for this cart yet.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="onepaqucpro-cr-detail__section">
                                        <h3><?php esc_html_e('Metadata', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                        <dl class="onepaqucpro-cr-meta-grid">
                                            <div>
                                                <dt><?php esc_html_e('Session ID', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                <dd><code><?php echo esc_html($cart['session_id']); ?></code></dd>
                                            </div>
                                            <div>
                                                <dt><?php esc_html_e('Status', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                <dd><?php echo esc_html(ucfirst($cart['status'])); ?></dd>
                                            </div>
                                            <div>
                                                <dt><?php esc_html_e('Created', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                <dd><?php echo esc_html(self::format_datetime($cart['created_at'])); ?></dd>
                                            </div>
                                            <div>
                                                <dt><?php esc_html_e('Updated', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                <dd><?php echo esc_html(self::format_datetime($cart['updated_at'])); ?></dd>
                                            </div>
                                            <div>
                                                <dt><?php esc_html_e('Referrer', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                <dd><?php echo esc_html($cart['referrer_url'] ? $cart['referrer_url'] : '-'); ?></dd>
                                            </div>
                                            <div>
                                                <dt><?php esc_html_e('Entry URL', 'one-page-quick-checkout-for-woocommerce-pro'); ?></dt>
                                                <dd><?php echo esc_html($cart['entry_url'] ? $cart['entry_url'] : '-'); ?></dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div class="onepaqucpro-cr-detail__section onepaqucpro-cr-notes-panel">
                                        <div class="onepaqucpro-cr-section-heading">
                                            <div>
                                                <h3><?php esc_html_e('Marketing Notes', 'one-page-quick-checkout-for-woocommerce-pro'); ?></h3>
                                                <p><?php esc_html_e('Private context for follow-up, segmentation, and recovery decisions.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                                            </div>
                                        </div>

                                        <div class="onepaqucpro-cr-notes-summary">
                                            <div class="onepaqucpro-cr-saved-note">
                                                <div class="onepaqucpro-cr-note-header">
                                                    <span><?php esc_html_e('Saved note', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                                    <?php if (! self::is_locked_mode()) : ?>
                                                        <div class="onepaqucpro-cr-note-actions">
                                                            <button type="button" class="button button-secondary button-small" data-cr-note-edit="<?php echo esc_attr($note_form_id); ?>">
                                                                <?php echo esc_html(! empty($cart['notes']) ? __('Edit', 'one-page-quick-checkout-for-woocommerce-pro') : __('Add Note', 'one-page-quick-checkout-for-woocommerce-pro')); ?>
                                                            </button>
                                                            <?php if (! empty($cart['notes'])) : ?>
                                                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="onepaqucpro-cr-note-delete-form" data-cr-note-delete>
                                                                    <input type="hidden" name="action" value="onepaqucpro_cart_recovery_save_cart_meta">
                                                                    <input type="hidden" name="cart_id" value="<?php echo esc_attr($cart['id']); ?>">
                                                                    <input type="hidden" name="cart_tags" value="<?php echo esc_attr(implode(', ', $cart['tags'])); ?>">
                                                                    <input type="hidden" name="cart_notes" value="">
                                                                    <?php wp_nonce_field('onepaqucpro_cart_recovery_save_cart_meta_' . $cart['id']); ?>
                                                                    <button type="submit" class="button button-link-delete button-small"><?php esc_html_e('Delete', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (self::is_locked_mode()) : ?>
                                                    <p><?php echo wp_kses_post(self::render_locked_value('Internal note')); ?></p>
                                                <?php elseif (! empty($cart['notes'])) : ?>
                                                    <p><?php echo nl2br(esc_html($cart['notes'])); ?></p>
                                                <?php else : ?>
                                                    <p class="is-empty"><?php esc_html_e('No internal note has been added yet.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <span><?php esc_html_e('Tags', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                                <?php if (self::is_locked_mode()) : ?>
                                                    <p><?php echo wp_kses_post(self::render_locked_value('Tags')); ?></p>
                                                <?php elseif (! empty($cart['tags'])) : ?>
                                                    <div class="onepaqucpro-cr-note-tags">
                                                        <?php foreach ($cart['tags'] as $tag) : ?>
                                                            <span><?php echo esc_html($tag); ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else : ?>
                                                    <p class="is-empty"><?php esc_html_e('No tags assigned.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php if (! self::is_locked_mode()) : ?>
                                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="<?php echo esc_attr($note_form_id); ?>" class="onepaqucpro-cr-settings-form onepaqucpro-cr-notes-form <?php echo ! empty($cart['notes']) ? 'is-hidden' : ''; ?>" data-cr-note-form>
                                                <input type="hidden" name="action" value="onepaqucpro_cart_recovery_save_cart_meta">
                                                <input type="hidden" name="cart_id" value="<?php echo esc_attr($cart['id']); ?>">
                                                <?php wp_nonce_field('onepaqucpro_cart_recovery_save_cart_meta_' . $cart['id']); ?>
                                                <label>
                                                    <span><?php esc_html_e('Edit Tags', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                                    <input type="text" name="cart_tags" value="<?php echo esc_attr(implode(', ', $cart['tags'])); ?>" placeholder="<?php esc_attr_e('vip, warm-lead', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">
                                                    <small><?php esc_html_e('Separate multiple tags with commas.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></small>
                                                </label>
                                                <label>
                                                    <span><?php esc_html_e('Edit Internal Note', 'one-page-quick-checkout-for-woocommerce-pro'); ?></span>
                                                    <textarea name="cart_notes" rows="5" placeholder="<?php esc_attr_e('Example: Asked for a discount, prefers WhatsApp follow-up, interested in headset bundles.', 'one-page-quick-checkout-for-woocommerce-pro'); ?>"><?php echo esc_textarea($cart['notes']); ?></textarea>
                                                    <small><?php esc_html_e('Visible only to admins. Use this for marketing context, not customer-facing order notes.', 'one-page-quick-checkout-for-woocommerce-pro'); ?></small>
                                                </label>
                                                <div class="onepaqucpro-cr-form-actions">
                                                    <button type="submit" class="button button-primary"><?php esc_html_e('Save Marketing Notes', 'one-page-quick-checkout-for-woocommerce-pro'); ?></button>
                                                </div>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </template>
        <?php
        }
    }

    private static function is_cart_recovery_screen($screen = null)
    {
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        return self::PAGE_SLUG === $page;
    }

    private static function supports_screen_options_for_tab($tab)
    {
        if (in_array($tab, array('carts', 'activity'), true)) {
            return true;
        }

        return 'email' === $tab && in_array(self::get_active_email_view(), array('templates', 'activity'), true);
    }

    private static function get_screen_option_meta_key($tab, $suffix)
    {
        return sprintf('onepaqucpro_cart_recovery_%s_%s', sanitize_key($tab), sanitize_key($suffix));
    }

    private static function get_screen_option_columns($tab)
    {
        if ('activity' === $tab) {
            return array(
                'type'      => __('Type', 'one-page-quick-checkout-for-woocommerce-pro'),
                'recipient' => __('Recipient', 'one-page-quick-checkout-for-woocommerce-pro'),
                'details'   => __('Details', 'one-page-quick-checkout-for-woocommerce-pro'),
                'status'    => __('Status', 'one-page-quick-checkout-for-woocommerce-pro'),
                'occurred'  => __('Occurred', 'one-page-quick-checkout-for-woocommerce-pro'),
            );
        }

        if ('email_activity' === $tab) {
            return array(
                'recipient' => __('Recipient', 'one-page-quick-checkout-for-woocommerce-pro'),
                'details'   => __('Details', 'one-page-quick-checkout-for-woocommerce-pro'),
                'status'    => __('Status', 'one-page-quick-checkout-for-woocommerce-pro'),
                'occurred'  => __('Occurred', 'one-page-quick-checkout-for-woocommerce-pro'),
            );
        }

        if ('email_templates' === $tab) {
            return array(
                'subject'         => __('Subject', 'one-page-quick-checkout-for-woocommerce-pro'),
                'trigger_after'   => __('Trigger After', 'one-page-quick-checkout-for-woocommerce-pro'),
                'sent'            => __('Sent', 'one-page-quick-checkout-for-woocommerce-pro'),
                'open_rate'       => __('Open Rate', 'one-page-quick-checkout-for-woocommerce-pro'),
                'click_rate'      => __('Click Rate', 'one-page-quick-checkout-for-woocommerce-pro'),
                'conversion_rate' => __('Conversion Rate', 'one-page-quick-checkout-for-woocommerce-pro'),
                'unsubscribed'    => __('Unsubscribed', 'one-page-quick-checkout-for-woocommerce-pro'),
                'updated'         => __('Updated', 'one-page-quick-checkout-for-woocommerce-pro'),
                'status'          => __('Status', 'one-page-quick-checkout-for-woocommerce-pro'),
                'actions'         => __('Actions', 'one-page-quick-checkout-for-woocommerce-pro'),
            );
        }

        return array(
            'cart_total'    => __('Cart Total', 'one-page-quick-checkout-for-woocommerce-pro'),
            'status'        => __('Status', 'one-page-quick-checkout-for-woocommerce-pro'),
            'last_activity' => __('Last Activity', 'one-page-quick-checkout-for-woocommerce-pro'),
            'recovery'      => __('Recovery', 'one-page-quick-checkout-for-woocommerce-pro'),
        );
    }

    private static function get_screen_option_visible_columns($tab)
    {
        $allowed = array_keys(self::get_screen_option_columns($tab));
        $stored  = get_user_meta(get_current_user_id(), self::get_screen_option_meta_key($tab, 'columns'), true);

        if (! is_array($stored)) {
            return $allowed;
        }

        $visible = array_values(array_intersect($allowed, array_map('sanitize_key', $stored)));

        return $visible;
    }

    private static function get_screen_option_per_page($tab)
    {
        $stored = absint(get_user_meta(get_current_user_id(), self::get_screen_option_meta_key($tab, 'per_page'), true));
        if ($stored) {
            return max(5, min(100, $stored));
        }

        return max(5, min(100, absint(self::get_settings()['per_page'])));
    }

    private static function get_tabs()
    {
        return array(
            'carts'     => array('label' => __('Carts', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-cart'),
            'analytics' => array('label' => __('Analytics', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-chart-area'),
            'journey'   => array('label' => __('Journey', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-randomize'),
            'activity'  => array('label' => __('Activity', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-backup'),
            'email'     => array('label' => __('Email', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-email'),
            'settings'  => array('label' => __('Settings', 'one-page-quick-checkout-for-woocommerce-pro'), 'icon' => 'dashicons-admin-generic'),
        );
    }

    private static function get_general_settings_keys()
    {
        return array(
            'enabled',
            'track_free_carts',
            'inactivity_timeout',
            'retention_days',
            'high_value_threshold',
            'excluded_product_ids',
            'excluded_category_ids',
            'excluded_roles',
            'webhook_url',
        );
    }

    private static function get_email_settings_keys()
    {
        return array(
            'sender',
            'sender_name',
            'reply_to',
            'quiet_hours_enabled',
            'send_window_start',
            'send_window_end',
            'max_emails_per_cart',
            'stop_after_restore',
            'excluded_domains',
            'append_utm',
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'tracking_pixel_enabled',
        );
    }

    private static function get_settings_checkbox_keys()
    {
        return array(
            'enabled',
            'track_free_carts',
            'quiet_hours_enabled',
            'stop_after_restore',
            'append_utm',
            'tracking_pixel_enabled',
        );
    }

    private static function get_active_tab()
    {
        $tab  = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'carts';
        $tabs = self::get_tabs();

        return isset($tabs[$tab]) ? $tab : 'carts';
    }

    private static function get_active_email_view()
    {
        return self::sanitize_choice(
            isset($_GET['cr_email_view']) ? wp_unslash($_GET['cr_email_view']) : 'templates',
            array('templates', 'activity', 'settings'),
            'templates'
        );
    }

    private static function get_screen_option_context($tab)
    {
        if ('email' === $tab && 'templates' === self::get_active_email_view()) {
            return 'email_templates';
        }

        if ('email' === $tab && 'activity' === self::get_active_email_view()) {
            return 'email_activity';
        }

        return $tab;
    }

    private static function get_page_url($args = array())
    {
        $base = array('page' => self::PAGE_SLUG);

        return add_query_arg(array_merge($base, $args), admin_url('admin.php'));
    }

    private static function get_product_label($product_id)
    {
        $product_id = absint($product_id);
        $label      = '';

        if (function_exists('wc_get_product')) {
            $product = wc_get_product($product_id);
            if ($product) {
                $label = $product->get_name();
                if ($product->get_sku()) {
                    $label .= ' - ' . sprintf(__('SKU: %s', 'one-page-quick-checkout-for-woocommerce-pro'), $product->get_sku());
                }
            }
        }

        if (! $label) {
            $label = get_the_title($product_id);
        }

        return $label ? sprintf('#%1$d - %2$s', $product_id, $label) : sprintf(__('Product #%d', 'one-page-quick-checkout-for-woocommerce-pro'), $product_id);
    }

    private static function get_category_label($category_id)
    {
        $category_id = absint($category_id);
        $term        = get_term($category_id, 'product_cat');

        if ($term && ! is_wp_error($term)) {
            return sprintf('#%1$d - %2$s', $category_id, $term->name);
        }

        return sprintf(__('Category #%d', 'one-page-quick-checkout-for-woocommerce-pro'), $category_id);
    }

    private static function get_role_options()
    {
        $roles = array();
        if (function_exists('wp_roles')) {
            foreach (wp_roles()->roles as $role_key => $role) {
                $roles[$role_key] = isset($role['name']) ? translate_user_role($role['name']) : $role_key;
            }
        }

        return $roles;
    }

    private static function get_status_action_url($cart_id, $status)
    {
        $url = add_query_arg(
            array(
                'action'  => 'onepaqucpro_cart_recovery_update_status',
                'cart_id' => $cart_id,
                'status'  => $status,
            ),
            admin_url('admin-post.php')
        );

        return wp_nonce_url($url, 'onepaqucpro_cart_recovery_update_status_' . $cart_id);
    }

    private static function render_preserved_query_fields($allowed_keys)
    {
        foreach ($allowed_keys as $key) {
            if (! isset($_GET[$key])) {
                continue;
            }

            $value = wp_unslash($_GET[$key]);
            if (is_array($value)) {
                continue;
            }

            printf(
                '<input type="hidden" name="%1$s" value="%2$s">',
                esc_attr($key),
                esc_attr(sanitize_text_field($value))
            );
        }
    }

    private static function get_preserved_query_args($allowed_keys)
    {
        $args = array();

        foreach ($allowed_keys as $key) {
            if (! isset($_GET[$key])) {
                continue;
            }

            $value = wp_unslash($_GET[$key]);
            if (is_array($value)) {
                continue;
            }

            $args[$key] = sanitize_text_field($value);
        }

        return $args;
    }

    private static function get_settings()
    {
        return wp_parse_args(get_option(self::SETTINGS_OPTION, array()), self::get_default_settings());
    }

    private static function get_default_settings()
    {
        return array(
            'enabled'            => 1,
            'track_free_carts'   => 0,
            'inactivity_timeout' => 60,
            'retention_days'     => 30,
            'sender'             => 'default',
            'sender_name'        => '',
            'reply_to'           => '',
            'quiet_hours_enabled' => 0,
            'send_window_start'  => '08:00',
            'send_window_end'    => '20:00',
            'max_emails_per_cart' => 3,
            'stop_after_restore' => 1,
            'excluded_product_ids' => array(),
            'excluded_category_ids' => array(),
            'excluded_roles'     => array(),
            'excluded_domains'   => array(),
            'append_utm'         => 1,
            'utm_source'         => 'cart-recovery',
            'utm_medium'         => 'email',
            'utm_campaign'       => 'recover-cart',
            'tracking_pixel_enabled' => 1,
            'webhook_url'         => '',
            'high_value_threshold' => 250,
            'per_page'           => 20,
        );
    }

    private static function sanitize_settings($settings)
    {
        return array(
            'enabled'            => empty($settings['enabled']) ? 0 : 1,
            'track_free_carts'   => empty($settings['track_free_carts']) ? 0 : 1,
            'inactivity_timeout' => max(1, absint(isset($settings['inactivity_timeout']) ? $settings['inactivity_timeout'] : 60)),
            'retention_days'     => max(1, absint(isset($settings['retention_days']) ? $settings['retention_days'] : 30)),
            'sender'             => self::sanitize_choice(isset($settings['sender']) ? $settings['sender'] : 'default', array('default', 'wordpress', 'store'), 'default'),
            'sender_name'        => sanitize_text_field(isset($settings['sender_name']) ? $settings['sender_name'] : ''),
            'reply_to'           => sanitize_email(isset($settings['reply_to']) ? $settings['reply_to'] : ''),
            'quiet_hours_enabled' => empty($settings['quiet_hours_enabled']) ? 0 : 1,
            'send_window_start'  => self::sanitize_time_string(isset($settings['send_window_start']) ? $settings['send_window_start'] : '08:00', '08:00'),
            'send_window_end'    => self::sanitize_time_string(isset($settings['send_window_end']) ? $settings['send_window_end'] : '20:00', '20:00'),
            'max_emails_per_cart' => max(1, absint(isset($settings['max_emails_per_cart']) ? $settings['max_emails_per_cart'] : 3)),
            'stop_after_restore' => empty($settings['stop_after_restore']) ? 0 : 1,
            'excluded_product_ids' => self::sanitize_integer_list(isset($settings['excluded_product_ids']) ? $settings['excluded_product_ids'] : array()),
            'excluded_category_ids' => self::sanitize_integer_list(isset($settings['excluded_category_ids']) ? $settings['excluded_category_ids'] : array()),
            'excluded_roles'     => self::sanitize_text_list(isset($settings['excluded_roles']) ? $settings['excluded_roles'] : array()),
            'excluded_domains'   => self::sanitize_text_list(isset($settings['excluded_domains']) ? $settings['excluded_domains'] : array()),
            'append_utm'         => empty($settings['append_utm']) ? 0 : 1,
            'utm_source'         => sanitize_text_field(isset($settings['utm_source']) ? $settings['utm_source'] : 'cart-recovery'),
            'utm_medium'         => sanitize_text_field(isset($settings['utm_medium']) ? $settings['utm_medium'] : 'email'),
            'utm_campaign'       => sanitize_text_field(isset($settings['utm_campaign']) ? $settings['utm_campaign'] : 'recover-cart'),
            'tracking_pixel_enabled' => empty($settings['tracking_pixel_enabled']) ? 0 : 1,
            'webhook_url'         => esc_url_raw(isset($settings['webhook_url']) ? $settings['webhook_url'] : ''),
            'high_value_threshold' => max(0, (float) (isset($settings['high_value_threshold']) ? $settings['high_value_threshold'] : 250)),
            'per_page'           => max(5, min(100, absint(isset($settings['per_page']) ? $settings['per_page'] : 20))),
        );
    }

    private static function get_templates()
    {
        $templates = get_option(self::TEMPLATES_OPTION, false);

        if (false === $templates) {
            return self::get_default_templates();
        }

        return self::sanitize_templates($templates, false);
    }

    private static function get_default_templates()
    {
        return array(
            array(
                'id'          => 'immediate_recovery',
                'name'        => __('Immediate Recovery', 'one-page-quick-checkout-for-woocommerce-pro'),
                'delay_value' => 60,
                'delay_unit'  => 'minutes',
                'subject'     => __('You left something behind', 'one-page-quick-checkout-for-woocommerce-pro'),
                'discount_code' => '',
                'from_email'    => '',
                'heading'       => __('We saved your cart', 'one-page-quick-checkout-for-woocommerce-pro'),
                'send_to'       => 'customer',
                'cart_items_layout' => 'table',
                'message'       => self::get_default_message_template('immediate_recovery'),
                'enabled'     => 1,
                'updated_at'  => '2026-02-22 10:41:00',
            ),
            array(
                'id'          => 'value_reinforcement',
                'name'        => __('Value Reinforcement', 'one-page-quick-checkout-for-woocommerce-pro'),
                'delay_value' => 24,
                'delay_unit'  => 'hours',
                'subject'     => __('Still thinking it over?', 'one-page-quick-checkout-for-woocommerce-pro'),
                'discount_code' => '',
                'from_email'    => '',
                'heading'       => __('We saved your cart', 'one-page-quick-checkout-for-woocommerce-pro'),
                'send_to'       => 'customer',
                'cart_items_layout' => 'cards',
                'message'       => self::get_default_message_template('value_reinforcement'),
                'enabled'     => 1,
                'updated_at'  => '2026-02-22 10:41:00',
            ),
            array(
                'id'          => 'final_attempt',
                'name'        => __('Final Attempt', 'one-page-quick-checkout-for-woocommerce-pro'),
                'delay_value' => 72,
                'delay_unit'  => 'hours',
                'subject'     => __('Your cart is about to expire', 'one-page-quick-checkout-for-woocommerce-pro'),
                'discount_code' => '',
                'from_email'    => '',
                'heading'       => __('We saved your cart', 'one-page-quick-checkout-for-woocommerce-pro'),
                'send_to'       => 'customer',
                'cart_items_layout' => 'compact',
                'message'       => self::get_default_message_template('final_attempt'),
                'enabled'     => 1,
                'updated_at'  => '2026-02-22 10:41:00',
            ),
        );
    }

    private static function sanitize_templates($templates, $preserve_timestamps = true)
    {
        if (! is_array($templates)) {
            return array();
        }

        $existing      = array();
        $existing_rows = self::get_default_templates();

        foreach ($existing_rows as $row) {
            $existing[$row['id']] = $row;
        }

        $saved_rows = get_option(self::TEMPLATES_OPTION, array());
        if (is_array($saved_rows)) {
            foreach ($saved_rows as $row) {
                if (isset($row['id'])) {
                    $existing[$row['id']] = $row;
                }
            }
        }

        $sanitized = array();

        foreach ($templates as $template) {
            if (! is_array($template)) {
                continue;
            }

            $name    = sanitize_text_field(isset($template['name']) ? $template['name'] : '');
            $subject = sanitize_text_field(isset($template['subject']) ? $template['subject'] : '');
            $heading = sanitize_text_field(isset($template['heading']) ? $template['heading'] : '');
            $from_email = sanitize_email(isset($template['from_email']) ? $template['from_email'] : '');
            $discount_code = sanitize_text_field(isset($template['discount_code']) ? $template['discount_code'] : '');
            $send_to = self::sanitize_choice(isset($template['send_to']) ? $template['send_to'] : 'customer', array('customer', 'custom'), 'customer');
            $custom_recipient = sanitize_email(isset($template['custom_recipient']) ? $template['custom_recipient'] : '');
            $cart_items_layout_input = isset($template['cart_items_layout']) ? $template['cart_items_layout'] : '';
            $message = isset($template['message']) ? wp_kses_post($template['message']) : '';

            if ('' === $name && '' === $subject) {
                continue;
            }

            $id = sanitize_key(isset($template['id']) ? $template['id'] : '');
            if (! $id) {
                $seed = $name ? $name : $subject;
                $id   = 'template_' . substr(md5($seed . wp_rand()), 0, 8);
            }

            $cart_items_layout = self::sanitize_cart_items_layout($cart_items_layout_input ? $cart_items_layout_input : self::get_default_cart_items_layout($id));

            if (self::is_default_template_id($id) && ('' === trim(wp_strip_all_tags($message)) || self::is_legacy_default_message($message))) {
                $message = self::get_default_message_template($id);
            }

            $row = array(
                'id'          => $id,
                'name'        => $name ? $name : __('Untitled Email', 'one-page-quick-checkout-for-woocommerce-pro'),
                'delay_value' => max(1, absint(isset($template['delay_value']) ? $template['delay_value'] : 60)),
                'delay_unit'  => self::sanitize_choice(isset($template['delay_unit']) ? $template['delay_unit'] : 'hours', array('minutes', 'hours', 'days'), 'hours'),
                'subject'     => $subject ? $subject : __('Recovery email', 'one-page-quick-checkout-for-woocommerce-pro'),
                'discount_code' => $discount_code,
                'from_email'    => $from_email,
                'heading'       => $heading,
                'send_to'       => $send_to,
                'custom_recipient' => $custom_recipient,
                'cart_items_layout' => $cart_items_layout,
                'message'       => $message,
                'enabled'     => empty($template['enabled']) ? 0 : 1,
                'updated_at'  => current_time('mysql'),
            );

            if ($preserve_timestamps && isset($existing[$id])) {
                $comparison = $existing[$id];
                unset($comparison['updated_at']);
                $candidate = $row;
                unset($candidate['updated_at']);
                if ($comparison === $candidate && ! empty($existing[$id]['updated_at'])) {
                    $row['updated_at'] = $existing[$id]['updated_at'];
                }
            } elseif (! $preserve_timestamps && isset($template['updated_at'])) {
                $row['updated_at'] = sanitize_text_field($template['updated_at']);
            }

            $sanitized[] = $row;
        }

        return $sanitized;
    }

    private static function is_default_template_id($template_id)
    {
        return in_array(sanitize_key($template_id), array('immediate_recovery', 'value_reinforcement', 'final_attempt'), true);
    }

    private static function get_default_cart_items_layout($template_id)
    {
        $template_id = sanitize_key($template_id);

        if ('value_reinforcement' === $template_id) {
            return 'cards';
        }

        if ('final_attempt' === $template_id) {
            return 'compact';
        }

        return 'table';
    }

    private static function is_legacy_default_message($message)
    {
        $message = (string) $message;

        return false !== strpos($message, 'Looks like you left something behind')
            && false !== strpos($message, '{cart_items}')
            && false !== strpos($message, 'Resume Checkout')
            && false === strpos($message, 'max-width:640px');
    }

    private static function get_cart_overrides()
    {
        $overrides = get_option(self::CART_OVERRIDES_OPTION, array());

        return is_array($overrides) ? $overrides : array();
    }

    private static function get_carts()
    {
        $carts = class_exists('Onepaqucpro_Cart_Recovery_Tracker')
            ? Onepaqucpro_Cart_Recovery_Tracker::get_admin_carts()
            : array();
        $carts = apply_filters('onepaqucpro_cart_recovery_carts', $carts);

        foreach ($carts as &$cart) {
            $cart['emails_sent']   = isset($cart['email_history']) ? count($cart['email_history']) : 0;
            $cart['last_email_at'] = ! empty($cart['email_history'][0]['sent_at']) ? $cart['email_history'][0]['sent_at'] : $cart['updated_at'];
            $cart['item_count']    = isset($cart['items']) ? count($cart['items']) : 0;
            $cart['customer_id']   = isset($cart['customer_id']) ? $cart['customer_id'] : '';
            $cart['unsubscribed']  = ! empty($cart['unsubscribed']) ? 1 : 0;
            $cart['restored_at']   = isset($cart['restored_at']) ? $cart['restored_at'] : '';
            $cart['recovered_at']  = isset($cart['recovered_at']) ? $cart['recovered_at'] : '';
            $cart['journey']       = isset($cart['journey']) && is_array($cart['journey']) ? $cart['journey'] : array();
            $cart['email_history'] = isset($cart['email_history']) && is_array($cart['email_history']) ? $cart['email_history'] : array();
            $cart['items']         = isset($cart['items']) && is_array($cart['items']) ? $cart['items'] : array();
            $cart['checkout_data'] = isset($cart['checkout_data']) && is_array($cart['checkout_data']) ? $cart['checkout_data'] : array();
            $cart['metadata']      = isset($cart['metadata']) && is_array($cart['metadata']) ? $cart['metadata'] : array();
            $cart['customer_profile'] = self::normalize_customer_profile(isset($cart['metadata']['customer_profile']) ? $cart['metadata']['customer_profile'] : array(), $cart);
            $cart['billing_address']  = isset($cart['customer_profile']['billing_address']) && is_array($cart['customer_profile']['billing_address']) ? $cart['customer_profile']['billing_address'] : array();
            $cart['shipping_address'] = isset($cart['customer_profile']['shipping_address']) && is_array($cart['customer_profile']['shipping_address']) ? $cart['customer_profile']['shipping_address'] : array();
            $cart['customer_phone']   = isset($cart['customer_profile']['phone']) ? sanitize_text_field($cart['customer_profile']['phone']) : '';
            $cart['customer_company'] = isset($cart['customer_profile']['company']) ? sanitize_text_field($cart['customer_profile']['company']) : '';
            $cart['order_notes']      = isset($cart['customer_profile']['order_notes']) ? sanitize_textarea_field($cart['customer_profile']['order_notes']) : '';

            $cart['coupon_codes']   = isset($cart['metadata']['coupon_codes']) && is_array($cart['metadata']['coupon_codes']) ? array_values(array_filter(array_map('sanitize_text_field', $cart['metadata']['coupon_codes']))) : array();
            $cart['customer_type']  = isset($cart['metadata']['customer_type']) ? sanitize_key($cart['metadata']['customer_type']) : ($cart['customer_id'] ? 'registered' : 'guest');
            $cart['browser']        = isset($cart['metadata']['browser']) ? sanitize_text_field($cart['metadata']['browser']) : 'Other';
            $cart['device_type']    = isset($cart['metadata']['device_type']) ? sanitize_key($cart['metadata']['device_type']) : 'desktop';
            $cart['recovery_source'] = isset($cart['metadata']['recovery_source']) ? sanitize_key($cart['metadata']['recovery_source']) : '';
            $cart['admin_state']    = isset($cart['metadata']['admin_state']) ? sanitize_key($cart['metadata']['admin_state']) : '';
            $cart['notes']          = isset($cart['metadata']['notes']) ? sanitize_textarea_field($cart['metadata']['notes']) : '';
            $cart['tags']           = isset($cart['metadata']['tags']) && is_array($cart['metadata']['tags']) ? array_values(array_filter(array_map('sanitize_text_field', $cart['metadata']['tags']))) : array();
            $cart['category_ids']   = isset($cart['metadata']['category_ids']) && is_array($cart['metadata']['category_ids']) ? array_map('absint', $cart['metadata']['category_ids']) : array();
            $cart['product_ids']    = isset($cart['metadata']['product_ids']) && is_array($cart['metadata']['product_ids']) ? array_map('absint', $cart['metadata']['product_ids']) : array();
            $cart['referrer_url']   = isset($cart['metadata']['referrer_url']) ? esc_url_raw($cart['metadata']['referrer_url']) : '';
            $cart['entry_url']      = isset($cart['metadata']['entry_url']) ? esc_url_raw($cart['metadata']['entry_url']) : '';
            $cart['recovered_order_id'] = isset($cart['recovered_order_id']) ? absint($cart['recovered_order_id']) : 0;

            usort($cart['journey'], function ($left, $right) {
                return self::to_timestamp($right['time']) <=> self::to_timestamp($left['time']);
            });

            usort($cart['email_history'], function ($left, $right) {
                return self::to_timestamp($right['sent_at']) <=> self::to_timestamp($left['sent_at']);
            });

            $event_times = array($cart['updated_at'], $cart['abandoned_at'], $cart['restored_at'], $cart['recovered_at'], $cart['last_email_at']);
            foreach ($cart['journey'] as $event) {
                if (! empty($event['time'])) {
                    $event_times[] = $event['time'];
                }
            }

            $cart['last_activity_at']          = self::get_latest_date($event_times);
            $cart['time_to_recovery_seconds']  = $cart['abandoned_at'] && $cart['recovered_at'] ? max(0, self::to_timestamp($cart['recovered_at']) - self::to_timestamp($cart['abandoned_at'])) : 0;
            $cart['template_names_sent']       = array_values(array_unique(array_filter(array_map(function ($email) {
                return isset($email['name']) ? sanitize_text_field($email['name']) : '';
            }, $cart['email_history']))));
            $cart['activity_status']           = $cart['admin_state'] ? $cart['admin_state'] : ($cart['status'] ? $cart['status'] : 'unknown');
            $cart['is_high_value']             = ! self::is_locked_mode() && $cart['cart_total'] >= (float) self::get_settings()['high_value_threshold'];
        }

        unset($cart);

        return $carts;
    }

    private static function normalize_customer_profile($profile, $cart)
    {
        $profile = is_array($profile) ? $profile : array();
        $billing = isset($profile['billing_address']) && is_array($profile['billing_address']) ? $profile['billing_address'] : array();
        $shipping = isset($profile['shipping_address']) && is_array($profile['shipping_address']) ? $profile['shipping_address'] : array();
        $checkout_data = ! empty($cart['checkout_data']) && is_array($cart['checkout_data'])
            ? $cart['checkout_data']
            : (isset($cart['metadata']['checkout_data']) && is_array($cart['metadata']['checkout_data']) ? $cart['metadata']['checkout_data'] : array());

        $billing = array_merge(self::build_profile_address_from_checkout($checkout_data, 'billing'), $billing);
        $shipping = array_merge(self::build_profile_address_from_checkout($checkout_data, 'shipping'), $shipping);

        $profile['customer_name'] = ! empty($profile['customer_name']) ? sanitize_text_field($profile['customer_name']) : sanitize_text_field($cart['customer_name']);
        $profile['email'] = ! empty($profile['email']) ? sanitize_email($profile['email']) : sanitize_email($cart['email']);
        $profile['phone'] = ! empty($profile['phone']) ? sanitize_text_field($profile['phone']) : (isset($billing['phone']) ? sanitize_text_field($billing['phone']) : '');
        $profile['company'] = ! empty($profile['company']) ? sanitize_text_field($profile['company']) : (isset($billing['company']) ? sanitize_text_field($billing['company']) : '');
        $profile['first_name'] = ! empty($profile['first_name']) ? sanitize_text_field($profile['first_name']) : (isset($billing['first_name']) ? sanitize_text_field($billing['first_name']) : '');
        $profile['last_name'] = ! empty($profile['last_name']) ? sanitize_text_field($profile['last_name']) : (isset($billing['last_name']) ? sanitize_text_field($billing['last_name']) : '');
        $profile['order_notes'] = ! empty($profile['order_notes']) ? sanitize_textarea_field($profile['order_notes']) : self::get_checkout_profile_value($checkout_data, array('order_comments', 'order-notes', 'customer_note'), true);
        $profile['billing_address'] = self::sanitize_profile_address($billing);
        $profile['shipping_address'] = self::sanitize_profile_address($shipping);

        return $profile;
    }

    private static function build_profile_address_from_checkout($checkout_data, $type)
    {
        if (! is_array($checkout_data)) {
            return array();
        }

        $type = 'shipping' === $type ? 'shipping' : 'billing';
        $aliases = 'billing' === $type
            ? array(
                'first_name' => array('billing_first_name', 'first-name', 'first_name'),
                'last_name'  => array('billing_last_name', 'last-name', 'last_name'),
                'company'    => array('billing_company', 'company'),
                'email'      => array('billing_email', 'email'),
                'phone'      => array('billing_phone', 'phone'),
                'address_1'  => array('billing_address_1', 'address', 'address_1'),
                'address_2'  => array('billing_address_2', 'address2', 'address_2'),
                'city'       => array('billing_city', 'city'),
                'state'      => array('billing_state', 'state'),
                'postcode'   => array('billing_postcode', 'postcode'),
                'country'    => array('billing_country', 'country'),
            )
            : array(
                'first_name' => array('shipping_first_name', 'shipping-first-name'),
                'last_name'  => array('shipping_last_name', 'shipping-last-name'),
                'company'    => array('shipping_company', 'shipping-company'),
                'phone'      => array('shipping_phone', 'shipping-phone'),
                'address_1'  => array('shipping_address_1', 'shipping-address'),
                'address_2'  => array('shipping_address_2', 'shipping-address2'),
                'city'       => array('shipping_city', 'shipping-city'),
                'state'      => array('shipping_state', 'shipping-state'),
                'postcode'   => array('shipping_postcode', 'shipping-postcode'),
                'country'    => array('shipping_country', 'shipping-country'),
            );

        $address = array();
        foreach ($aliases as $field => $keys) {
            foreach ($keys as $key) {
                if (empty($checkout_data[$key]) || ! is_scalar($checkout_data[$key])) {
                    continue;
                }

                $value = 'email' === $field ? sanitize_email($checkout_data[$key]) : sanitize_text_field($checkout_data[$key]);
                if ('' !== $value) {
                    $address[$field] = $value;
                    break;
                }
            }
        }

        return $address;
    }

    private static function get_checkout_profile_value($checkout_data, $keys, $textarea = false)
    {
        if (! is_array($checkout_data)) {
            return '';
        }

        foreach ((array) $keys as $key) {
            if (empty($checkout_data[$key]) || ! is_scalar($checkout_data[$key])) {
                continue;
            }

            return $textarea ? sanitize_textarea_field($checkout_data[$key]) : sanitize_text_field($checkout_data[$key]);
        }

        return '';
    }

    private static function sanitize_profile_address($address)
    {
        $address = is_array($address) ? $address : array();
        $fields  = array('first_name', 'last_name', 'company', 'email', 'phone', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country');
        $clean   = array();

        foreach ($fields as $field) {
            if (empty($address[$field])) {
                continue;
            }

            $clean[$field] = 'email' === $field ? sanitize_email($address[$field]) : sanitize_text_field($address[$field]);
        }

        return $clean;
    }

    private static function get_cart_table_context($carts)
    {
        $filters = array(
            'status'        => self::sanitize_choice(isset($_GET['cr_cart_status']) ? wp_unslash($_GET['cr_cart_status']) : 'all', array('all', 'active', 'abandoned', 'recovered', 'unsubscribed', 'ignored', 'archived'), 'all'),
            'customer_type' => self::sanitize_choice(isset($_GET['cr_cart_customer_type']) ? wp_unslash($_GET['cr_cart_customer_type']) : 'all', array('all', 'guest', 'registered'), 'all'),
            'device'        => self::sanitize_choice(isset($_GET['cr_cart_device']) ? wp_unslash($_GET['cr_cart_device']) : 'all', array('all', 'desktop', 'mobile', 'tablet'), 'all'),
            'template'      => sanitize_text_field(isset($_GET['cr_cart_template']) ? wp_unslash($_GET['cr_cart_template']) : ''),
            'source'        => self::sanitize_choice(isset($_GET['cr_cart_source']) ? wp_unslash($_GET['cr_cart_source']) : 'all', array('all', 'email', 'site_revisit', 'admin'), 'all'),
            'from'          => self::sanitize_date(isset($_GET['cr_cart_from']) ? wp_unslash($_GET['cr_cart_from']) : ''),
            'to'            => self::sanitize_date(isset($_GET['cr_cart_to']) ? wp_unslash($_GET['cr_cart_to']) : ''),
            'min'           => self::sanitize_decimal(isset($_GET['cr_cart_min']) ? wp_unslash($_GET['cr_cart_min']) : ''),
            'max'           => self::sanitize_decimal(isset($_GET['cr_cart_max']) ? wp_unslash($_GET['cr_cart_max']) : ''),
            'search'        => sanitize_text_field(isset($_GET['cr_cart_search']) ? wp_unslash($_GET['cr_cart_search']) : ''),
            'sort'          => self::sanitize_choice(isset($_GET['cr_cart_sort']) ? wp_unslash($_GET['cr_cart_sort']) : 'last_activity', array('customer', 'cart_total', 'last_email', 'emails_sent', 'status', 'last_activity', 'abandoned_at', 'recovered_at'), 'last_activity'),
            'order'         => self::sanitize_choice(isset($_GET['cr_cart_order']) ? wp_unslash($_GET['cr_cart_order']) : 'desc', array('asc', 'desc'), 'desc'),
            'page'          => max(1, absint(isset($_GET['cr_cart_page']) ? wp_unslash($_GET['cr_cart_page']) : 1)),
            'per_page'      => self::get_screen_option_per_page('carts'),
        );

        $items      = self::sort_cart_items(self::filter_cart_items($carts, $filters), $filters);
        $summary    = self::summarize_cart_items($items);
        $pagination = self::paginate_items($items, $filters['page'], $filters['per_page']);
        $summary['deltas'] = self::get_cart_summary_deltas($carts, $filters, $summary);

        return array(
            'filters' => $filters,
            'all_items' => $items,
            'items'   => $pagination['items'],
            'pagination' => $pagination,
            'summary' => $summary,
        );
    }

    private static function filter_cart_items($carts, $filters)
    {
        return array_values(array_filter($carts, function ($cart) use ($filters) {
            if ('all' !== $filters['status']) {
                if ('unsubscribed' === $filters['status'] && empty($cart['unsubscribed'])) {
                    return false;
                }

                if (in_array($filters['status'], array('ignored', 'archived'), true) && $filters['status'] !== $cart['admin_state']) {
                    return false;
                }

                if (in_array($filters['status'], array('active', 'abandoned', 'recovered'), true) && $filters['status'] !== $cart['status']) {
                    return false;
                }
            }

            if ('all' !== $filters['customer_type'] && $filters['customer_type'] !== $cart['customer_type']) {
                return false;
            }

            if ('all' !== $filters['device'] && $filters['device'] !== $cart['device_type']) {
                return false;
            }

            if ($filters['template'] && ! in_array($filters['template'], $cart['template_names_sent'], true)) {
                return false;
            }

            if ('all' !== $filters['source'] && $filters['source'] !== $cart['recovery_source']) {
                return false;
            }

            if ($filters['from'] && self::to_timestamp($cart['last_activity_at']) < self::to_timestamp($filters['from'] . ' 00:00:00')) {
                return false;
            }

            if ($filters['to'] && self::to_timestamp($cart['last_activity_at']) > self::to_timestamp($filters['to'] . ' 23:59:59')) {
                return false;
            }

            if ('' !== $filters['min'] && $cart['cart_total'] < (float) $filters['min']) {
                return false;
            }

            if ('' !== $filters['max'] && $cart['cart_total'] > (float) $filters['max']) {
                return false;
            }

            if ($filters['search']) {
                $searchable_fields = array(
                    $cart['id'],
                    $cart['customer_name'],
                    $cart['product_context'],
                    implode(' ', $cart['coupon_codes']),
                    implode(' ', $cart['tags']),
                    $cart['recovery_source'],
                );

                if (! self::is_locked_mode()) {
                    $searchable_fields = array_merge($searchable_fields, array(
                        $cart['email'],
                        $cart['customer_phone'],
                        $cart['customer_company'],
                        self::format_profile_address($cart['billing_address']),
                        self::format_profile_address($cart['shipping_address']),
                        $cart['browser'],
                        $cart['device_type'],
                        $cart['notes'],
                    ));
                }

                $haystack = strtolower(implode(' ', $searchable_fields));

                if (false === strpos($haystack, strtolower($filters['search']))) {
                    return false;
                }
            }

            return true;
        }));
    }

    private static function sort_cart_items($items, $filters)
    {
        usort($items, function ($left, $right) use ($filters) {
            switch ($filters['sort']) {
                case 'customer':
                    $comparison = strcasecmp($left['customer_name'], $right['customer_name']);
                    break;
                case 'cart_total':
                    $comparison = $left['cart_total'] <=> $right['cart_total'];
                    break;
                case 'emails_sent':
                    $comparison = $left['emails_sent'] <=> $right['emails_sent'];
                    break;
                case 'abandoned_at':
                    $comparison = self::to_timestamp($left['abandoned_at']) <=> self::to_timestamp($right['abandoned_at']);
                    break;
                case 'recovered_at':
                    $comparison = self::to_timestamp($left['recovered_at']) <=> self::to_timestamp($right['recovered_at']);
                    break;
                case 'status':
                    $comparison = strcasecmp($left['activity_status'], $right['activity_status']);
                    break;
                case 'last_activity':
                    $comparison = self::to_timestamp($left['last_activity_at']) <=> self::to_timestamp($right['last_activity_at']);
                    break;
                case 'last_email':
                default:
                    $comparison = self::to_timestamp($left['last_email_at']) <=> self::to_timestamp($right['last_email_at']);
                    break;
            }

            return 'asc' === $filters['order'] ? $comparison : -1 * $comparison;
        });

        return $items;
    }

    private static function summarize_cart_items($items)
    {
        $count = count($items);
        $total = array_reduce($items, function ($carry, $cart) {
            return $carry + (float) $cart['cart_total'];
        }, 0);

        $recoverable_value = array_reduce($items, function ($carry, $cart) {
            if ('abandoned' === $cart['status']) {
                return $carry + (float) $cart['cart_total'];
            }

            return $carry;
        }, 0);

        return array(
            'count'             => $count,
            'total_value'       => $total,
            'average_value'     => $count ? $total / $count : 0,
            'recoverable_value' => $recoverable_value,
        );
    }

    private static function get_cart_summary_deltas($carts, $filters, $summary)
    {
        $comparison = self::get_date_filter_comparison_context($filters['from'], $filters['to']);

        if (empty($comparison)) {
            return array();
        }

        $previous_filters         = $filters;
        $previous_filters['from'] = $comparison['from'];
        $previous_filters['to']   = $comparison['to'];
        $previous_summary         = self::summarize_cart_items(self::filter_cart_items($carts, $previous_filters));

        return array(
            'count'             => self::get_metric_delta_context($summary['count'], $previous_summary['count'], $comparison['label']),
            'total_value'       => self::get_metric_delta_context($summary['total_value'], $previous_summary['total_value'], $comparison['label']),
            'average_value'     => self::get_metric_delta_context($summary['average_value'], $previous_summary['average_value'], $comparison['label']),
            'recoverable_value' => self::get_metric_delta_context($summary['recoverable_value'], $previous_summary['recoverable_value'], $comparison['label']),
        );
    }

    private static function get_activity_table_context($carts, $args = array())
    {
        $rows = self::get_activity_rows($carts);
        $args = is_array($args) ? $args : array();
        $forced_type = isset($args['type']) ? self::sanitize_choice($args['type'], array('email', 'event'), '') : '';

        $filters = array(
            'status'    => self::sanitize_choice(isset($_GET['cr_activity_status']) ? wp_unslash($_GET['cr_activity_status']) : 'all', array('all', 'sent', 'opened', 'clicked', 'recovered', 'failed', 'abandoned', 'restored', 'unsubscribed'), 'all'),
            'type'      => $forced_type ? $forced_type : self::sanitize_choice(isset($_GET['cr_activity_type']) ? wp_unslash($_GET['cr_activity_type']) : 'all', array('all', 'email', 'event'), 'all'),
            'from'      => self::sanitize_date(isset($_GET['cr_activity_from']) ? wp_unslash($_GET['cr_activity_from']) : ''),
            'to'        => self::sanitize_date(isset($_GET['cr_activity_to']) ? wp_unslash($_GET['cr_activity_to']) : ''),
            'cart'      => sanitize_text_field(isset($_GET['cr_activity_cart']) ? wp_unslash($_GET['cr_activity_cart']) : ''),
            'template'  => sanitize_text_field(isset($_GET['cr_activity_template']) ? wp_unslash($_GET['cr_activity_template']) : ''),
            'recipient' => sanitize_text_field(isset($_GET['cr_activity_recipient']) ? wp_unslash($_GET['cr_activity_recipient']) : ''),
            'search'    => sanitize_text_field(isset($_GET['cr_activity_search']) ? wp_unslash($_GET['cr_activity_search']) : ''),
            'sort'      => self::sanitize_choice(isset($_GET['cr_activity_sort']) ? wp_unslash($_GET['cr_activity_sort']) : 'occurred_at', array('occurred_at'), 'occurred_at'),
            'order'     => self::sanitize_choice(isset($_GET['cr_activity_order']) ? wp_unslash($_GET['cr_activity_order']) : 'desc', array('asc', 'desc'), 'desc'),
            'page'      => max(1, absint(isset($_GET['cr_activity_page']) ? wp_unslash($_GET['cr_activity_page']) : 1)),
            'per_page'  => self::get_screen_option_per_page('activity'),
        );

        $items      = self::sort_activity_items(self::filter_activity_items($rows, $filters), $filters);
        $summary    = self::get_email_activity_summary($items);
        $pagination = self::paginate_items($items, $filters['page'], $filters['per_page']);
        $summary['deltas'] = self::get_email_activity_summary_deltas($rows, $filters, $summary);

        return array(
            'filters' => $filters,
            'all_items' => $items,
            'items'   => $pagination['items'],
            'pagination' => $pagination,
            'summary' => $summary,
        );
    }

    private static function filter_activity_items($rows, $filters)
    {
        return array_values(array_filter($rows, function ($row) use ($filters) {
            if ('all' !== $filters['status'] && $filters['status'] !== $row['status']) {
                return false;
            }

            if ('all' !== $filters['type'] && $filters['type'] !== $row['row_type']) {
                return false;
            }

            if ($filters['from'] && self::to_timestamp($row['occurred_at']) < self::to_timestamp($filters['from'] . ' 00:00:00')) {
                return false;
            }

            if ($filters['to'] && self::to_timestamp($row['occurred_at']) > self::to_timestamp($filters['to'] . ' 23:59:59')) {
                return false;
            }

            if ($filters['cart'] && false === strpos($row['cart_id'], $filters['cart'])) {
                return false;
            }

            if ($filters['template'] && $filters['template'] !== $row['email_name']) {
                return false;
            }

            if ($filters['recipient'] && false === strpos(strtolower($row['recipient']), strtolower($filters['recipient']))) {
                return false;
            }

            if ($filters['search']) {
                $haystack = strtolower(implode(' ', array(
                    $row['email_name'],
                    $row['recipient'],
                    $row['subject'],
                    $row['engagement'],
                    $row['details'],
                )));

                if (false === strpos($haystack, strtolower($filters['search']))) {
                    return false;
                }
            }

            return true;
        }));
    }

    private static function sort_activity_items($items, $filters)
    {
        usort($items, function ($left, $right) use ($filters) {
            $comparison = self::to_timestamp($left['occurred_at']) <=> self::to_timestamp($right['occurred_at']);

            return 'asc' === $filters['order'] ? $comparison : -1 * $comparison;
        });

        return $items;
    }

    private static function get_email_activity_summary_deltas($rows, $filters, $summary)
    {
        $comparison = self::get_date_filter_comparison_context($filters['from'], $filters['to']);

        if (empty($comparison)) {
            return array();
        }

        $previous_filters         = $filters;
        $previous_filters['from'] = $comparison['from'];
        $previous_filters['to']   = $comparison['to'];
        $previous_summary         = self::get_email_activity_summary(self::filter_activity_items($rows, $previous_filters));

        return array(
            'sent'      => self::get_metric_delta_context($summary['sent'], $previous_summary['sent'], $comparison['label']),
            'failed'    => self::get_metric_delta_context($summary['failed'], $previous_summary['failed'], $comparison['label']),
            'opened'    => self::get_metric_delta_context($summary['opened'], $previous_summary['opened'], $comparison['label']),
            'clicked'   => self::get_metric_delta_context($summary['clicked'], $previous_summary['clicked'], $comparison['label']),
            'recovered' => self::get_metric_delta_context($summary['recovered'], $previous_summary['recovered'], $comparison['label']),
        );
    }

    private static function get_email_activity_table_context($carts)
    {
        return self::get_activity_table_context($carts, array('type' => 'email'));
    }

    private static function get_email_activity_summary($rows)
    {
        $summary = array(
            'total'      => 0,
            'sent'       => 0,
            'failed'     => 0,
            'opened'     => 0,
            'clicked'    => 0,
            'recovered'  => 0,
            'open_rate'  => 0,
            'click_rate' => 0,
        );

        foreach ($rows as $row) {
            if ('email' !== $row['row_type']) {
                continue;
            }

            $summary['total']++;
            $status  = isset($row['status']) ? sanitize_key($row['status']) : '';
            $opened  = ! empty($row['opened_at']) || in_array($status, array('opened', 'clicked', 'recovered'), true);
            $clicked = ! empty($row['clicked_at']) || in_array($status, array('clicked', 'recovered'), true);

            if ('failed' === $status) {
                $summary['failed']++;
            } else {
                $summary['sent']++;
            }

            if ($opened) {
                $summary['opened']++;
            }

            if ($clicked) {
                $summary['clicked']++;
            }

            if ('recovered' === $status) {
                $summary['recovered']++;
            }
        }

        if ($summary['sent']) {
            $summary['open_rate']  = ($summary['opened'] / $summary['sent']) * 100;
            $summary['click_rate'] = ($summary['clicked'] / $summary['sent']) * 100;
        }

        return $summary;
    }

    private static function get_template_table_context($templates, $carts)
    {
        $filters = array(
            'status'     => self::sanitize_choice(isset($_GET['cr_template_status']) ? wp_unslash($_GET['cr_template_status']) : 'all', array('all', 'enabled', 'disabled'), 'all'),
            'delay_unit' => self::sanitize_choice(isset($_GET['cr_template_delay_unit']) ? wp_unslash($_GET['cr_template_delay_unit']) : 'all', array('all', 'minutes', 'hours', 'days'), 'all'),
            'search'     => sanitize_text_field(isset($_GET['cr_template_search']) ? wp_unslash($_GET['cr_template_search']) : ''),
            'sort'       => self::sanitize_choice(isset($_GET['cr_template_sort']) ? wp_unslash($_GET['cr_template_sort']) : 'trigger_after', array('name', 'trigger_after', 'sent', 'open_rate', 'click_rate', 'conversion_rate', 'unsubscribed', 'updated'), 'trigger_after'),
            'order'      => self::sanitize_choice(isset($_GET['cr_template_order']) ? wp_unslash($_GET['cr_template_order']) : 'asc', array('asc', 'desc'), 'asc'),
            'page'       => max(1, absint(isset($_GET['cr_template_page']) ? wp_unslash($_GET['cr_template_page']) : 1)),
            'per_page'   => self::get_screen_option_per_page('email_templates'),
        );

        $metrics = self::get_template_metrics($templates, $carts);
        $items   = array();

        foreach ($templates as $template) {
            if (! is_array($template)) {
                continue;
            }

            $template_id = isset($template['id']) ? sanitize_key($template['id']) : '';
            if (! $template_id) {
                continue;
            }

            $enabled    = ! empty($template['enabled']);
            $delay_unit = self::sanitize_choice(isset($template['delay_unit']) ? $template['delay_unit'] : 'hours', array('minutes', 'hours', 'days'), 'hours');

            if ('enabled' === $filters['status'] && ! $enabled) {
                continue;
            }

            if ('disabled' === $filters['status'] && $enabled) {
                continue;
            }

            if ('all' !== $filters['delay_unit'] && $filters['delay_unit'] !== $delay_unit) {
                continue;
            }

            $row_metrics = isset($metrics[$template_id]) ? $metrics[$template_id] : array(
                'sent'         => 0,
                'opened'       => 0,
                'clicked'      => 0,
                'recovered'    => 0,
                'unsubscribed' => 0,
            );

            $sent            = (int) $row_metrics['sent'];
            $trigger_label   = self::format_template_delay($template);
            $status_label    = $enabled ? __('Enabled', 'one-page-quick-checkout-for-woocommerce-pro') : __('Disabled', 'one-page-quick-checkout-for-woocommerce-pro');
            $open_rate       = $sent ? ((int) $row_metrics['opened'] / $sent) * 100 : 0;
            $click_rate      = $sent ? ((int) $row_metrics['clicked'] / $sent) * 100 : 0;
            $conversion_rate = $sent ? ((int) $row_metrics['recovered'] / $sent) * 100 : 0;

            $row = array(
                'template'        => $template,
                'trigger_label'   => $trigger_label,
                'trigger_seconds' => self::template_delay_seconds($template),
                'sent'            => $sent,
                'opened'          => (int) $row_metrics['opened'],
                'clicked'         => (int) $row_metrics['clicked'],
                'recovered'       => (int) $row_metrics['recovered'],
                'unsubscribed'    => (int) $row_metrics['unsubscribed'],
                'open_rate'       => $open_rate,
                'click_rate'      => $click_rate,
                'conversion_rate' => $conversion_rate,
                'updated'         => isset($template['updated_at']) ? $template['updated_at'] : '',
                'status_label'    => $status_label,
            );

            if ($filters['search']) {
                $haystack = strtolower(implode(' ', array(
                    $template_id,
                    isset($template['name']) ? $template['name'] : '',
                    isset($template['subject']) ? $template['subject'] : '',
                    isset($template['heading']) ? $template['heading'] : '',
                    isset($template['from_email']) ? $template['from_email'] : '',
                    isset($template['discount_code']) ? $template['discount_code'] : '',
                    $trigger_label,
                    $status_label,
                )));

                if (false === strpos($haystack, strtolower($filters['search']))) {
                    continue;
                }
            }

            $items[] = $row;
        }

        usort($items, function ($left, $right) use ($filters) {
            switch ($filters['sort']) {
                case 'name':
                    $comparison = strcasecmp($left['template']['name'], $right['template']['name']);
                    break;
                case 'sent':
                    $comparison = $left['sent'] <=> $right['sent'];
                    break;
                case 'open_rate':
                    $comparison = $left['open_rate'] <=> $right['open_rate'];
                    break;
                case 'click_rate':
                    $comparison = $left['click_rate'] <=> $right['click_rate'];
                    break;
                case 'conversion_rate':
                    $comparison = $left['conversion_rate'] <=> $right['conversion_rate'];
                    break;
                case 'unsubscribed':
                    $comparison = $left['unsubscribed'] <=> $right['unsubscribed'];
                    break;
                case 'updated':
                    $comparison = self::to_timestamp($left['updated']) <=> self::to_timestamp($right['updated']);
                    break;
                case 'trigger_after':
                default:
                    $comparison = $left['trigger_seconds'] <=> $right['trigger_seconds'];
                    break;
            }

            return 'asc' === $filters['order'] ? $comparison : -1 * $comparison;
        });

        $pagination = self::paginate_items($items, $filters['page'], $filters['per_page']);
        $sent_total = array_reduce($items, function ($carry, $row) {
            return $carry + (int) $row['sent'];
        }, 0);

        return array(
            'filters'    => $filters,
            'all_items'  => $items,
            'items'      => $pagination['items'],
            'pagination' => $pagination,
            'summary'    => array(
                'count' => count($items),
                'sent'  => $sent_total,
            ),
        );
    }

    private static function get_template_metrics($templates, $carts)
    {
        $metrics    = array();
        $name_to_id = array();

        foreach ($templates as $template) {
            if (! is_array($template) || empty($template['id'])) {
                continue;
            }

            $template_id = sanitize_key($template['id']);
            $metrics[$template_id] = array(
                'sent'         => 0,
                'opened'       => 0,
                'clicked'      => 0,
                'recovered'    => 0,
                'unsubscribed' => 0,
            );

            if (! empty($template['name'])) {
                $name_to_id[strtolower(trim(wp_strip_all_tags($template['name'])))] = $template_id;
            }
        }

        foreach ($carts as $cart) {
            $email_history      = isset($cart['email_history']) && is_array($cart['email_history']) ? $cart['email_history'] : array();
            $sent_for_cart      = array();
            $recovered_for_cart = array();
            $latest_template_id = '';

            foreach ($email_history as $email) {
                $template_id = isset($email['template_id']) ? sanitize_key($email['template_id']) : '';
                if (! $template_id && ! empty($email['name'])) {
                    $name_key = strtolower(trim(wp_strip_all_tags($email['name'])));
                    $template_id = isset($name_to_id[$name_key]) ? $name_to_id[$name_key] : '';
                }

                if (! $template_id || ! isset($metrics[$template_id])) {
                    continue;
                }

                if (! $latest_template_id) {
                    $latest_template_id = $template_id;
                }

                $sent_for_cart[$template_id] = true;
                $metrics[$template_id]['sent']++;

                $status  = isset($email['status']) ? sanitize_key($email['status']) : '';
                $opened  = ! empty($email['opened_at']) || in_array($status, array('opened', 'clicked', 'recovered'), true);
                $clicked = ! empty($email['clicked_at']) || in_array($status, array('clicked', 'recovered'), true);

                if ($opened) {
                    $metrics[$template_id]['opened']++;
                }

                if ($clicked) {
                    $metrics[$template_id]['clicked']++;
                }

                if ('recovered' === $status) {
                    $metrics[$template_id]['recovered']++;
                    $recovered_for_cart[$template_id] = true;
                }
            }

            $last_clicked_template_id = isset($cart['metadata']['last_clicked_template_id']) ? sanitize_key($cart['metadata']['last_clicked_template_id']) : '';

            if (! empty($cart['recovered_at']) && $last_clicked_template_id && isset($sent_for_cart[$last_clicked_template_id]) && empty($recovered_for_cart[$last_clicked_template_id])) {
                $metrics[$last_clicked_template_id]['recovered']++;
            }

            if (! empty($cart['unsubscribed'])) {
                $unsubscribed_template_id = ($last_clicked_template_id && isset($sent_for_cart[$last_clicked_template_id])) ? $last_clicked_template_id : $latest_template_id;
                if ($unsubscribed_template_id && isset($metrics[$unsubscribed_template_id])) {
                    $metrics[$unsubscribed_template_id]['unsubscribed']++;
                }
            }
        }

        return $metrics;
    }

    private static function get_activity_rows($carts)
    {
        $rows = array();

        foreach ($carts as $cart) {
            $cart_action_state = self::get_cart_action_state($cart);

            foreach ($cart['email_history'] as $email) {
                $rows[] = array(
                    'id'          => $email['id'],
                    'row_type'    => 'email',
                    'email_log_id' => isset($email['log_id']) ? absint($email['log_id']) : absint(str_replace('email_', '', $email['id'])),
                    'template_id' => isset($email['template_id']) ? sanitize_key($email['template_id']) : '',
                    'cart_id'     => $cart['id'],
                    'cart_status' => isset($cart['status']) ? $cart['status'] : '',
                    'recovered_order_id' => isset($cart['recovered_order_id']) ? absint($cart['recovered_order_id']) : 0,
                    'can_send_next' => ! empty($cart_action_state['can_send_next']),
                    'email_name'  => $email['name'],
                    'recipient'   => isset($email['recipient']) ? $email['recipient'] : $cart['email'],
                    'subject'     => $email['subject'],
                    'status'      => $email['status'],
                    'engagement'  => $email['engagement'],
                    'sent_at'     => $email['sent_at'],
                    'occurred_at' => $email['sent_at'],
                    'opened_at'   => isset($email['opened_at']) ? $email['opened_at'] : '',
                    'clicked_at'  => isset($email['clicked_at']) ? $email['clicked_at'] : '',
                    'details'     => isset($email['delivery_error']) && $email['delivery_error'] ? $email['delivery_error'] : '',
                    'payload'     => isset($email['payload']) && is_array($email['payload']) ? $email['payload'] : array(),
                );
            }

            foreach ($cart['journey'] as $event) {
                $rows[] = array(
                    'id'          => 'event_' . $cart['id'] . '_' . md5($event['type'] . $event['time']),
                    'row_type'    => 'event',
                    'email_log_id' => 0,
                    'template_id' => '',
                    'cart_id'     => $cart['id'],
                    'cart_status' => isset($cart['status']) ? $cart['status'] : '',
                    'recovered_order_id' => isset($cart['recovered_order_id']) ? absint($cart['recovered_order_id']) : 0,
                    'can_send_next' => false,
                    'email_name'  => $event['title'],
                    'recipient'   => $cart['email'],
                    'subject'     => '',
                    'status'      => self::map_event_type_to_activity_status($event['type']),
                    'engagement'  => '',
                    'sent_at'     => $event['time'],
                    'occurred_at' => $event['time'],
                    'opened_at'   => '',
                    'clicked_at'  => '',
                    'details'     => implode(' | ', array_map(function ($label, $value) {
                        return $label . ': ' . wp_strip_all_tags((string) $value);
                    }, array_keys($event['meta']), array_values($event['meta']))),
                    'payload'     => $event['meta'],
                );
            }
        }

        return $rows;
    }

    private static function get_analytics_context($carts)
    {
        $range            = self::get_range_context();
        $previous_range   = 'previous_period' === $range['compare'] ? self::get_previous_range($range) : array();
        $metrics          = self::compute_metrics($carts, $range);
        $previous_metrics = $previous_range ? self::compute_metrics($carts, $previous_range) : array();

        return array(
            'range'            => $range,
            'metrics'          => $metrics['metrics'],
            'series'           => $metrics['series'],
            'funnel'           => self::get_funnel_counts($carts, $range),
            'previous_metrics' => $previous_metrics ? $previous_metrics['metrics'] : array(),
            'template_performance' => self::get_template_performance($carts, $range),
            'product_performance'  => self::get_product_performance($carts, $range),
            'segment_performance'  => self::get_segment_performance($carts, $range),
            'latency'              => self::get_latency_metrics($carts, $range),
        );
    }

    private static function get_journey_context($carts)
    {
        $range  = self::get_range_context();
        $funnel = self::get_funnel_counts($carts, $range);
        $stage  = sanitize_text_field(isset($_GET['cr_journey_stage']) ? wp_unslash($_GET['cr_journey_stage']) : '');

        return array(
            'range'           => $range,
            'funnel'          => $funnel,
            'stage_drilldown' => self::get_stage_drilldown($carts, $range, $stage),
            'time_between'    => self::get_journey_time_between($carts, $range),
            'source_split'    => self::get_restore_source_split($carts, $range),
            'drop_off'        => self::get_drop_off_summary($carts, $range),
            'cohorts'         => self::get_journey_cohorts($carts, $range),
        );
    }

    private static function get_range_context()
    {
        $timezone = wp_timezone();
        $today    = new DateTimeImmutable('now', $timezone);
        $period   = self::sanitize_choice(isset($_GET['cr_period']) ? wp_unslash($_GET['cr_period']) : 'month_to_date', array('month_to_date', 'last_7_days', 'last_30_days', 'custom'), 'month_to_date');
        $compare  = self::sanitize_choice(isset($_GET['cr_compare']) ? wp_unslash($_GET['cr_compare']) : 'previous_period', array('previous_period', 'none'), 'previous_period');

        switch ($period) {
            case 'last_7_days':
                $from = $today->sub(new DateInterval('P6D'))->setTime(0, 0, 0);
                $to   = $today->setTime(23, 59, 59);
                break;
            case 'last_30_days':
                $from = $today->sub(new DateInterval('P29D'))->setTime(0, 0, 0);
                $to   = $today->setTime(23, 59, 59);
                break;
            case 'custom':
                $from_value = self::sanitize_date(isset($_GET['cr_date_from']) ? wp_unslash($_GET['cr_date_from']) : '');
                $to_value   = self::sanitize_date(isset($_GET['cr_date_to']) ? wp_unslash($_GET['cr_date_to']) : '');
                $from       = $from_value ? new DateTimeImmutable($from_value . ' 00:00:00', $timezone) : $today->modify('first day of this month')->setTime(0, 0, 0);
                $to         = $to_value ? new DateTimeImmutable($to_value . ' 23:59:59', $timezone) : $today->setTime(23, 59, 59);
                break;
            case 'month_to_date':
            default:
                $from = $today->modify('first day of this month')->setTime(0, 0, 0);
                $to   = $today->setTime(23, 59, 59);
                break;
        }

        if ($from > $to) {
            $swap = $from;
            $from = $to->setTime(0, 0, 0);
            $to   = $swap->setTime(23, 59, 59);
        }

        return array(
            'period'  => $period,
            'compare' => $compare,
            'from'    => $from->format('Y-m-d'),
            'to'      => $to->format('Y-m-d'),
            'start'   => $from,
            'end'     => $to,
            'label'   => sprintf('%s - %s', wp_date('M j, Y', $from->getTimestamp(), $timezone), wp_date('M j, Y', $to->getTimestamp(), $timezone)),
            'compare_label' => self::get_range_comparison_label($period),
        );
    }

    private static function get_quick_select_ranges()
    {
        $timezone = wp_timezone();
        $today    = new DateTimeImmutable('now', $timezone);

        $this_month_start = $today->modify('first day of this month')->setTime(0, 0, 0);
        $last_month_start = $today->modify('first day of last month')->setTime(0, 0, 0);
        $last_month_end   = $today->modify('last day of last month')->setTime(23, 59, 59);

        return array(
            'today' => array(
                'label' => __('Today', 'one-page-quick-checkout-for-woocommerce-pro'),
                'from'  => $today->setTime(0, 0, 0)->format('Y-m-d'),
                'to'    => $today->setTime(23, 59, 59)->format('Y-m-d'),
            ),
            'last_7_days' => array(
                'label' => __('Last 7 Days', 'one-page-quick-checkout-for-woocommerce-pro'),
                'from'  => $today->sub(new DateInterval('P6D'))->setTime(0, 0, 0)->format('Y-m-d'),
                'to'    => $today->setTime(23, 59, 59)->format('Y-m-d'),
            ),
            'last_30_days' => array(
                'label' => __('Last 30 Days', 'one-page-quick-checkout-for-woocommerce-pro'),
                'from'  => $today->sub(new DateInterval('P29D'))->setTime(0, 0, 0)->format('Y-m-d'),
                'to'    => $today->setTime(23, 59, 59)->format('Y-m-d'),
            ),
            'this_month' => array(
                'label' => __('This Month', 'one-page-quick-checkout-for-woocommerce-pro'),
                'from'  => $this_month_start->format('Y-m-d'),
                'to'    => $today->setTime(23, 59, 59)->format('Y-m-d'),
            ),
            'last_month' => array(
                'label' => __('Last Month', 'one-page-quick-checkout-for-woocommerce-pro'),
                'from'  => $last_month_start->format('Y-m-d'),
                'to'    => $last_month_end->format('Y-m-d'),
            ),
        );
    }

    private static function get_date_filter_preset($from, $to)
    {
        foreach (self::get_quick_select_ranges() as $preset => $range) {
            if ($from === $range['from'] && $to === $range['to']) {
                return $preset;
            }
        }

        return 'custom';
    }

    private static function get_date_filter_comparison_context($from, $to)
    {
        $from = self::sanitize_date($from);
        $to   = self::sanitize_date($to);

        if (! $from || ! $to) {
            return array();
        }

        $timezone = wp_timezone();
        $start    = new DateTimeImmutable($from . ' 00:00:00', $timezone);
        $end      = new DateTimeImmutable($to . ' 23:59:59', $timezone);

        if ($start > $end) {
            $swap  = $start;
            $start = $end->setTime(0, 0, 0);
            $end   = $swap->setTime(23, 59, 59);
        }

        $days   = (int) $start->diff($end)->format('%a') + 1;
        $preset = self::get_date_filter_preset($start->format('Y-m-d'), $end->format('Y-m-d'));
        $label  = self::get_date_filter_comparison_label($preset);

        switch ($preset) {
            case 'today':
                $previous_start = $start->sub(new DateInterval('P1D'))->setTime(0, 0, 0);
                $previous_end   = $end->sub(new DateInterval('P1D'))->setTime(23, 59, 59);
                break;
            case 'this_month':
                $previous_start = $start->modify('first day of previous month')->setTime(0, 0, 0);
                $previous_end   = $previous_start->add(new DateInterval('P' . max(0, $days - 1) . 'D'))->setTime(23, 59, 59);
                $month_end      = $previous_start->modify('last day of this month')->setTime(23, 59, 59);
                if ($previous_end > $month_end) {
                    $previous_end = $month_end;
                }
                break;
            case 'last_month':
                $previous_start = $start->modify('first day of previous month')->setTime(0, 0, 0);
                $previous_end   = $previous_start->modify('last day of this month')->setTime(23, 59, 59);
                break;
            case 'last_7_days':
            case 'last_30_days':
            case 'custom':
            default:
                $previous_end   = $start->sub(new DateInterval('P1D'))->setTime(23, 59, 59);
                $previous_start = $previous_end->sub(new DateInterval('P' . max(0, $days - 1) . 'D'))->setTime(0, 0, 0);
                break;
        }

        return array(
            'from'  => $previous_start->format('Y-m-d'),
            'to'    => $previous_end->format('Y-m-d'),
            'start' => $previous_start,
            'end'   => $previous_end,
            'label' => $label,
        );
    }

    private static function get_date_filter_comparison_label($preset)
    {
        switch ($preset) {
            case 'today':
                return __('from yesterday', 'one-page-quick-checkout-for-woocommerce-pro');
            case 'last_7_days':
                return __('from last week', 'one-page-quick-checkout-for-woocommerce-pro');
            case 'last_30_days':
                return __('from previous 30 days', 'one-page-quick-checkout-for-woocommerce-pro');
            case 'this_month':
                return __('from last month', 'one-page-quick-checkout-for-woocommerce-pro');
            case 'last_month':
                return __('from previous month', 'one-page-quick-checkout-for-woocommerce-pro');
            case 'custom':
            default:
                return __('from previous period', 'one-page-quick-checkout-for-woocommerce-pro');
        }
    }

    private static function get_range_comparison_label($period)
    {
        switch ($period) {
            case 'last_7_days':
                return __('from last week', 'one-page-quick-checkout-for-woocommerce-pro');
            case 'last_30_days':
                return __('from previous 30 days', 'one-page-quick-checkout-for-woocommerce-pro');
            case 'month_to_date':
                return __('from last month', 'one-page-quick-checkout-for-woocommerce-pro');
            case 'custom':
            default:
                return __('from previous period', 'one-page-quick-checkout-for-woocommerce-pro');
        }
    }

    private static function get_previous_range($range)
    {
        $days          = (int) $range['start']->diff($range['end'])->format('%a') + 1;

        if (isset($range['period']) && 'month_to_date' === $range['period']) {
            $previous_from = $range['start']->modify('first day of previous month')->setTime(0, 0, 0);
            $previous_to   = $previous_from->add(new DateInterval('P' . max(0, $days - 1) . 'D'))->setTime(23, 59, 59);
            $month_end     = $previous_from->modify('last day of this month')->setTime(23, 59, 59);
            if ($previous_to > $month_end) {
                $previous_to = $month_end;
            }
        } else {
            $previous_to   = $range['start']->sub(new DateInterval('P1D'))->setTime(23, 59, 59);
            $previous_from = $previous_to->sub(new DateInterval('P' . max(0, $days - 1) . 'D'))->setTime(0, 0, 0);
        }

        return array(
            'start' => $previous_from,
            'end'   => $previous_to,
        );
    }

    private static function compute_metrics($carts, $range)
    {
        $labels  = array();
        $revenue = array();
        $sent    = array();
        $opened  = array();
        $clicked = array();
        $cursor  = $range['start'];

        while ($cursor <= $range['end']) {
            $key = $cursor->format('Y-m-d');
            $labels[]      = wp_date('M j', $cursor->getTimestamp(), wp_timezone());
            $revenue[$key] = 0;
            $sent[$key]    = 0;
            $opened[$key]  = 0;
            $clicked[$key] = 0;
            $cursor        = $cursor->add(new DateInterval('P1D'));
        }

        $tracked_carts     = 0;
        $abandoned         = 0;
        $recovered         = 0;
        $recovered_revenue = 0;
        $recoverable_value = 0;
        $emails_sent       = 0;
        $emails_opened     = 0;
        $emails_clicked    = 0;
        $unsubscribed      = 0;

        foreach ($carts as $cart) {
            if (self::is_in_range($cart['created_at'], $range)) {
                $tracked_carts++;
            }

            if (self::is_in_range($cart['abandoned_at'], $range)) {
                $abandoned++;
                if (empty($cart['recovered_at'])) {
                    $recoverable_value += (float) $cart['cart_total'];
                }
            }

            if (! empty($cart['recovered_at']) && self::is_in_range($cart['recovered_at'], $range)) {
                $recovered++;
                $recovered_revenue += (float) $cart['cart_total'];
                $revenue[self::format_chart_key($cart['recovered_at'])] += (float) $cart['cart_total'];
            }

            if (! empty($cart['unsubscribed']) && self::is_in_range($cart['updated_at'], $range)) {
                $unsubscribed++;
            }

            foreach ($cart['email_history'] as $email) {
                if (self::is_in_range($email['sent_at'], $range)) {
                    $emails_sent++;
                    $sent[self::format_chart_key($email['sent_at'])]++;
                }

                if (! empty($email['opened_at']) && self::is_in_range($email['opened_at'], $range)) {
                    $emails_opened++;
                    $opened[self::format_chart_key($email['opened_at'])]++;
                }

                if (! empty($email['clicked_at']) && self::is_in_range($email['clicked_at'], $range)) {
                    $emails_clicked++;
                    $clicked[self::format_chart_key($email['clicked_at'])]++;
                }
            }
        }

        return array(
            'metrics' => array(
                'tracked_carts'     => $tracked_carts,
                'abandoned'         => $abandoned,
                'recovered'         => $recovered,
                'recovered_revenue' => $recovered_revenue,
                'recoverable_value' => $recoverable_value,
                'lost_revenue'      => max(0, $recoverable_value + ($abandoned ? ($recovered_revenue * max(0, $abandoned - $recovered)) / max(1, $recovered) : 0)),
                'recovery_rate'     => $abandoned ? ($recovered / $abandoned) * 100 : 0,
                'emails_sent'       => $emails_sent,
                'open_rate'         => $emails_sent ? ($emails_opened / $emails_sent) * 100 : 0,
                'click_rate'        => $emails_sent ? ($emails_clicked / $emails_sent) * 100 : 0,
                'unsubscribe_rate'  => $emails_sent ? ($unsubscribed / $emails_sent) * 100 : 0,
            ),
            'series'  => array(
                'labels'  => $labels,
                'revenue' => array_values($revenue),
                'sent'    => array_values($sent),
                'opened'  => array_values($opened),
                'clicked' => array_values($clicked),
            ),
        );
    }

    private static function get_funnel_counts($carts, $range)
    {
        $created_ids   = array();
        $abandoned_ids = array();
        $emailed_ids   = array();
        $opened_ids    = array();
        $clicked_ids   = array();
        $restored_ids  = array();
        $recovered_ids = array();

        foreach ($carts as $cart) {
            if (self::is_in_range($cart['created_at'], $range)) {
                $created_ids[$cart['id']] = true;
            }

            if (self::is_in_range($cart['abandoned_at'], $range)) {
                $abandoned_ids[$cart['id']] = true;
            }

            if (! empty($cart['restored_at']) && self::is_in_range($cart['restored_at'], $range)) {
                $restored_ids[$cart['id']] = true;
            }

            if (! empty($cart['recovered_at']) && self::is_in_range($cart['recovered_at'], $range)) {
                $recovered_ids[$cart['id']] = true;
            }

            foreach ($cart['email_history'] as $email) {
                if (self::is_in_range($email['sent_at'], $range)) {
                    $emailed_ids[$cart['id']] = true;
                }

                if (! empty($email['opened_at']) && self::is_in_range($email['opened_at'], $range)) {
                    $opened_ids[$cart['id']] = true;
                }

                if (! empty($email['clicked_at']) && self::is_in_range($email['clicked_at'], $range)) {
                    $clicked_ids[$cart['id']] = true;
                }
            }
        }

        $counts = array(
            __('Carts Created', 'one-page-quick-checkout-for-woocommerce-pro')  => count($created_ids),
            __('Abandoned', 'one-page-quick-checkout-for-woocommerce-pro')      => count($abandoned_ids),
            __('Emails Sent', 'one-page-quick-checkout-for-woocommerce-pro')    => count($emailed_ids),
            __('Emails Opened', 'one-page-quick-checkout-for-woocommerce-pro')  => count($opened_ids),
            __('Emails Clicked', 'one-page-quick-checkout-for-woocommerce-pro') => count($clicked_ids),
            __('Carts Restored', 'one-page-quick-checkout-for-woocommerce-pro') => count($restored_ids),
            __('Carts Recovered', 'one-page-quick-checkout-for-woocommerce-pro') => count($recovered_ids),
        );

        $stages         = array();
        $previous_count = null;

        foreach ($counts as $label => $count) {
            $stages[] = array(
                'label'           => $label,
                'count'           => $count,
                'conversion_rate' => null === $previous_count ? 100 : ($previous_count ? ($count / $previous_count) * 100 : 0),
                'drop_off'        => null === $previous_count ? -1 : max($previous_count - $count, 0),
            );
            $previous_count = $count;
        }

        return array('stages' => $stages);
    }

    private static function get_template_performance($carts, $range)
    {
        $rows = array();

        foreach ($carts as $cart) {
            foreach ($cart['email_history'] as $email) {
                if (! self::is_in_range($email['sent_at'], $range)) {
                    continue;
                }

                $key = $email['name'];
                if (! isset($rows[$key])) {
                    $rows[$key] = array(
                        'name'      => $email['name'],
                        'sent'      => 0,
                        'opened'    => 0,
                        'clicked'   => 0,
                        'recovered' => 0,
                        'revenue'   => 0,
                    );
                }

                $rows[$key]['sent']++;
                $rows[$key]['opened'] += ! empty($email['opened_at']) ? 1 : 0;
                $rows[$key]['clicked'] += ! empty($email['clicked_at']) ? 1 : 0;

                if ('recovered' === $email['status']) {
                    $rows[$key]['recovered']++;
                    $rows[$key]['revenue'] += (float) $cart['cart_total'];
                }
            }
        }

        uasort($rows, function ($left, $right) {
            return $right['revenue'] <=> $left['revenue'];
        });

        return array_values($rows);
    }

    private static function get_product_performance($carts, $range)
    {
        $products = array();

        foreach ($carts as $cart) {
            foreach ($cart['items'] as $item) {
                $key = isset($item['name']) ? $item['name'] : '';
                if (! $key) {
                    continue;
                }

                if (! isset($products[$key])) {
                    $products[$key] = array(
                        'name'       => $key,
                        'abandoned'  => 0,
                        'recovered'  => 0,
                        'revenue'    => 0,
                    );
                }

                if (self::is_in_range($cart['abandoned_at'], $range)) {
                    $products[$key]['abandoned']++;
                }

                if (! empty($cart['recovered_at']) && self::is_in_range($cart['recovered_at'], $range)) {
                    $products[$key]['recovered']++;
                    $products[$key]['revenue'] += (float) $item['price'];
                }
            }
        }

        uasort($products, function ($left, $right) {
            return $right['revenue'] <=> $left['revenue'];
        });

        return array_slice(array_values($products), 0, 10);
    }

    private static function get_segment_performance($carts, $range)
    {
        $segments = array(
            'device' => array(),
            'customer_type' => array(),
        );

        foreach ($carts as $cart) {
            if (self::is_in_range($cart['abandoned_at'], $range) || self::is_in_range($cart['recovered_at'], $range)) {
                foreach (array('device' => $cart['device_type'], 'customer_type' => $cart['customer_type']) as $type => $label) {
                    if (! isset($segments[$type][$label])) {
                        $segments[$type][$label] = array('label' => ucfirst($label), 'abandoned' => 0, 'recovered' => 0, 'revenue' => 0);
                    }

                    if (self::is_in_range($cart['abandoned_at'], $range)) {
                        $segments[$type][$label]['abandoned']++;
                    }
                    if (! empty($cart['recovered_at']) && self::is_in_range($cart['recovered_at'], $range)) {
                        $segments[$type][$label]['recovered']++;
                        $segments[$type][$label]['revenue'] += (float) $cart['cart_total'];
                    }
                }
            }
        }

        return $segments;
    }

    private static function get_latency_metrics($carts, $range)
    {
        $abandon_times = array();
        $restore_times = array();
        $recovery_times = array();

        foreach ($carts as $cart) {
            if ($cart['created_at'] && $cart['abandoned_at'] && self::is_in_range($cart['abandoned_at'], $range)) {
                $abandon_times[] = max(0, self::to_timestamp($cart['abandoned_at']) - self::to_timestamp($cart['created_at']));
            }

            if ($cart['abandoned_at'] && $cart['restored_at'] && self::is_in_range($cart['restored_at'], $range)) {
                $restore_times[] = max(0, self::to_timestamp($cart['restored_at']) - self::to_timestamp($cart['abandoned_at']));
            }

            if ($cart['abandoned_at'] && $cart['recovered_at'] && self::is_in_range($cart['recovered_at'], $range)) {
                $recovery_times[] = max(0, self::to_timestamp($cart['recovered_at']) - self::to_timestamp($cart['abandoned_at']));
            }
        }

        return array(
            'avg_to_abandon' => self::average_numbers($abandon_times),
            'avg_to_restore' => self::average_numbers($restore_times),
            'avg_to_recover' => self::average_numbers($recovery_times),
        );
    }

    private static function get_stage_drilldown($carts, $range, $stage)
    {
        $stage = sanitize_text_field($stage);
        if (! $stage) {
            return array(
                'stage' => '',
                'items' => array(),
            );
        }

        $items = array_values(array_filter($carts, function ($cart) use ($range, $stage) {
            switch ($stage) {
                case 'created':
                    return self::is_in_range($cart['created_at'], $range);
                case 'abandoned':
                    return self::is_in_range($cart['abandoned_at'], $range);
                case 'emailed':
                    return $cart['emails_sent'] > 0;
                case 'opened':
                    return ! empty(array_filter($cart['email_history'], function ($email) use ($range) {
                        return ! empty($email['opened_at']) && self::is_in_range($email['opened_at'], $range);
                    }));
                case 'clicked':
                    return ! empty(array_filter($cart['email_history'], function ($email) use ($range) {
                        return ! empty($email['clicked_at']) && self::is_in_range($email['clicked_at'], $range);
                    }));
                case 'restored':
                    return self::is_in_range($cart['restored_at'], $range);
                case 'recovered':
                    return self::is_in_range($cart['recovered_at'], $range);
            }

            return false;
        }));

        return array(
            'stage' => $stage,
            'items' => $items,
        );
    }

    private static function get_journey_time_between($carts, $range)
    {
        return array(
            array('label' => __('Cart Created to Abandoned', 'one-page-quick-checkout-for-woocommerce-pro'), 'seconds' => self::get_latency_metrics($carts, $range)['avg_to_abandon']),
            array('label' => __('Abandoned to Restored', 'one-page-quick-checkout-for-woocommerce-pro'), 'seconds' => self::get_latency_metrics($carts, $range)['avg_to_restore']),
            array('label' => __('Abandoned to Recovered', 'one-page-quick-checkout-for-woocommerce-pro'), 'seconds' => self::get_latency_metrics($carts, $range)['avg_to_recover']),
        );
    }

    private static function get_restore_source_split($carts, $range)
    {
        $counts = array(
            'email' => 0,
            'site_revisit' => 0,
            'admin' => 0,
            'unknown' => 0,
        );

        foreach ($carts as $cart) {
            if (! self::is_in_range($cart['restored_at'], $range) && ! self::is_in_range($cart['recovered_at'], $range)) {
                continue;
            }

            $source = $cart['recovery_source'] ? $cart['recovery_source'] : 'unknown';
            if (! isset($counts[$source])) {
                $counts[$source] = 0;
            }
            $counts[$source]++;
        }

        return $counts;
    }

    private static function get_drop_off_summary($carts, $range)
    {
        $summary = array(
            __('Missing recovery email', 'one-page-quick-checkout-for-woocommerce-pro') => 0,
            __('Unsubscribed', 'one-page-quick-checkout-for-woocommerce-pro') => 0,
            __('Restored but not ordered', 'one-page-quick-checkout-for-woocommerce-pro') => 0,
        );

        foreach ($carts as $cart) {
            if (! self::is_in_range($cart['abandoned_at'], $range)) {
                continue;
            }

            if (! $cart['email']) {
                $summary[__('Missing recovery email', 'one-page-quick-checkout-for-woocommerce-pro')]++;
            }
            if (! empty($cart['unsubscribed'])) {
                $summary[__('Unsubscribed', 'one-page-quick-checkout-for-woocommerce-pro')]++;
            }
            if (! empty($cart['restored_at']) && empty($cart['recovered_at'])) {
                $summary[__('Restored but not ordered', 'one-page-quick-checkout-for-woocommerce-pro')]++;
            }
        }

        return $summary;
    }

    private static function get_journey_cohorts($carts, $range)
    {
        $cohorts = array();

        foreach ($carts as $cart) {
            if (! self::is_in_range($cart['abandoned_at'], $range)) {
                continue;
            }

            $key = wp_date('Y-m-d', self::to_timestamp($cart['abandoned_at']), wp_timezone());
            if (! isset($cohorts[$key])) {
                $cohorts[$key] = array(
                    'date'      => $key,
                    'abandoned' => 0,
                    'recovered' => 0,
                );
            }

            $cohorts[$key]['abandoned']++;
            if (! empty($cart['recovered_at'])) {
                $cohorts[$key]['recovered']++;
            }
        }

        return array_values($cohorts);
    }

    private static function average_numbers($numbers)
    {
        $numbers = array_filter(array_map('floatval', (array) $numbers));

        return empty($numbers) ? 0 : array_sum($numbers) / count($numbers);
    }

    private static function get_default_carts()
    {
        $ua_browser = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36';
        $ua_store   = 'WordPress/6.9.1; https://plugincy.com';

        return array(
            array(
                'id'              => 'cart_1001',
                'customer_name'   => 'Pejovicmarko21',
                'customer_id'     => '',
                'email'           => 'pejovicmarko21@gmail.com',
                'cart_total'      => 39,
                'currency'        => 'USD',
                'status'          => 'abandoned',
                'created_at'      => '2026-03-07 00:16:00',
                'updated_at'      => '2026-03-08 15:13:00',
                'abandoned_at'    => '2026-03-07 01:20:00',
                'restored_at'     => '2026-03-08 16:02:00',
                'recovered_at'    => '',
                'session_id'      => 'e_f8d0fa3f1f71f92175db0a6b063e81',
                'product_context' => 'One Page Quick Checkout For WooCommerce Pro',
                'ip_address'      => '31.223.142.171',
                'user_agent'      => $ua_browser,
                'items'           => array(
                    array(
                        'name'     => 'One Page Quick Checkout For WooCommerce Pro - 1 site license (Annual)',
                        'quantity' => 1,
                        'price'    => 39,
                        'discount' => 0,
                    ),
                ),
                'journey'         => array(
                    array(
                        'type'  => 'cart_abandoned',
                        'title' => __('Cart Abandoned', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'time'  => '2026-03-07 01:20:00',
                        'meta'  => array(
                            __('IP Address', 'one-page-quick-checkout-for-woocommerce-pro') => '2a02:4780:b:1041:0:26e9:db1f:1',
                            __('User Agent', 'one-page-quick-checkout-for-woocommerce-pro') => $ua_store,
                        ),
                    ),
                    array(
                        'type'  => 'cart_updated',
                        'title' => __('Cart Updated', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'time'  => '2026-03-07 00:48:00',
                        'meta'  => array(
                            __('Cart Total', 'one-page-quick-checkout-for-woocommerce-pro') => self::format_currency(39, 'USD'),
                            __('Item Count', 'one-page-quick-checkout-for-woocommerce-pro') => '1',
                            __('IP Address', 'one-page-quick-checkout-for-woocommerce-pro') => '31.223.142.171',
                            __('User Agent', 'one-page-quick-checkout-for-woocommerce-pro') => $ua_browser,
                        ),
                    ),
                    array(
                        'type'  => 'cart_created',
                        'title' => __('Cart Created', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'time'  => '2026-03-07 00:16:00',
                        'meta'  => array(
                            __('Cart Total', 'one-page-quick-checkout-for-woocommerce-pro')   => self::format_currency(39, 'USD'),
                            __('Item Count', 'one-page-quick-checkout-for-woocommerce-pro')   => '1',
                            __('Email', 'one-page-quick-checkout-for-woocommerce-pro')        => 'pejovicmarko21@gmail.com',
                            __('Customer Name', 'one-page-quick-checkout-for-woocommerce-pro') => 'Pejovicmarko21',
                            __('IP Address', 'one-page-quick-checkout-for-woocommerce-pro')   => '31.223.142.171',
                            __('User Agent', 'one-page-quick-checkout-for-woocommerce-pro')   => $ua_browser,
                        ),
                    ),
                ),
                'email_history'   => array(
                    array(
                        'id'         => 'cart_1001_final',
                        'name'       => __('Final Attempt', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'subject'    => __('Your cart is about to expire', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'sent_at'    => '2026-03-08 15:13:00',
                        'status'     => 'sent',
                        'engagement' => '-',
                        'opened_at'  => '',
                        'clicked_at' => '',
                    ),
                    array(
                        'id'         => 'cart_1001_value',
                        'name'       => __('Value Reinforcement', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'subject'    => __('Still thinking it over?', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'sent_at'    => '2026-03-08 01:21:00',
                        'status'     => 'opened',
                        'engagement' => __('1 open', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'opened_at'  => '2026-03-08 01:44:00',
                        'clicked_at' => '',
                    ),
                    array(
                        'id'         => 'cart_1001_immediate',
                        'name'       => __('Immediate Recovery', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'subject'    => __('You left something behind', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'sent_at'    => '2026-03-07 02:21:00',
                        'status'     => 'sent',
                        'engagement' => '-',
                        'opened_at'  => '',
                        'clicked_at' => '',
                    ),
                ),
            ),
            array(
                'id'              => 'cart_1002',
                'customer_name'   => 'Arpit Shah',
                'customer_id'     => '',
                'email'           => 'developers@justdogs.in',
                'cart_total'      => 79,
                'currency'        => 'USD',
                'status'          => 'abandoned',
                'created_at'      => '2026-03-01 09:10:00',
                'updated_at'      => '2026-03-01 18:00:00',
                'abandoned_at'    => '2026-03-01 10:02:00',
                'restored_at'     => '',
                'recovered_at'    => '',
                'session_id'      => 'b_392381fa3db9c98152648a22ff0f881',
                'product_context' => 'Multi Location Product & Inventory Management for WooCommerce Pro',
                'ip_address'      => '91.223.41.80',
                'user_agent'      => $ua_browser,
                'items'           => array(
                    array(
                        'name'     => 'Multi Location Product & Inventory Management for WooCommerce Pro',
                        'quantity' => 1,
                        'price'    => 79,
                        'discount' => 0,
                    ),
                ),
                'journey'         => array(
                    array(
                        'type'  => 'email_sent',
                        'title' => __('Recovery Email Sent', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'time'  => '2026-03-01 18:00:00',
                        'meta'  => array(
                            __('Email', 'one-page-quick-checkout-for-woocommerce-pro')      => __('Final Attempt', 'one-page-quick-checkout-for-woocommerce-pro'),
                            __('Subject', 'one-page-quick-checkout-for-woocommerce-pro')    => __('Your cart is about to expire', 'one-page-quick-checkout-for-woocommerce-pro'),
                            __('IP Address', 'one-page-quick-checkout-for-woocommerce-pro') => '91.223.41.80',
                        ),
                    ),
                    array(
                        'type'  => 'cart_abandoned',
                        'title' => __('Cart Abandoned', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'time'  => '2026-03-01 10:02:00',
                        'meta'  => array(
                            __('Cart Total', 'one-page-quick-checkout-for-woocommerce-pro') => self::format_currency(79, 'USD'),
                            __('Email', 'one-page-quick-checkout-for-woocommerce-pro')      => 'developers@justdogs.in',
                        ),
                    ),
                    array(
                        'type'  => 'cart_created',
                        'title' => __('Cart Created', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'time'  => '2026-03-01 09:10:00',
                        'meta'  => array(
                            __('Item Count', 'one-page-quick-checkout-for-woocommerce-pro')   => '1',
                            __('Customer Name', 'one-page-quick-checkout-for-woocommerce-pro') => 'Arpit Shah',
                            __('IP Address', 'one-page-quick-checkout-for-woocommerce-pro')   => '91.223.41.80',
                        ),
                    ),
                ),
                'email_history'   => array(
                    array(
                        'id'         => 'cart_1002_final',
                        'name'       => __('Final Attempt', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'subject'    => __('Your cart is about to expire', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'sent_at'    => '2026-03-01 18:00:00',
                        'status'     => 'sent',
                        'engagement' => '-',
                        'opened_at'  => '',
                        'clicked_at' => '',
                    ),
                    array(
                        'id'         => 'cart_1002_value',
                        'name'       => __('Value Reinforcement', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'subject'    => __('Still thinking it over?', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'sent_at'    => '2026-03-01 15:10:00',
                        'status'     => 'opened',
                        'engagement' => __('1 open', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'opened_at'  => '2026-03-01 15:42:00',
                        'clicked_at' => '',
                    ),
                    array(
                        'id'         => 'cart_1002_immediate',
                        'name'       => __('Immediate Recovery', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'subject'    => __('You left something behind', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'sent_at'    => '2026-03-01 11:00:00',
                        'status'     => 'sent',
                        'engagement' => '-',
                        'opened_at'  => '',
                        'clicked_at' => '',
                    ),
                ),
            ),
            array(
                'id'              => 'cart_1003',
                'customer_name'   => 'G.weulenkranenbarg',
                'customer_id'     => '',
                'email'           => 'g.weulenkranenbarg@kusterenergy.com',
                'cart_total'      => 299,
                'currency'        => 'USD',
                'status'          => 'abandoned',
                'created_at'      => '2026-03-01 10:15:00',
                'updated_at'      => '2026-03-02 11:30:00',
                'abandoned_at'    => '2026-03-01 12:01:00',
                'restored_at'     => '',
                'recovered_at'    => '',
                'session_id'      => 'c_09bc4b10c91f4921939e5ca12db78a3',
                'product_context' => 'Multi Location Product & Inventory Management for WooCommerce Pro',
                'ip_address'      => '67.11.24.18',
                'user_agent'      => $ua_browser,
                'items'           => array(
                    array(
                        'name'     => 'Multi Location Product & Inventory Management for WooCommerce Pro - Agency',
                        'quantity' => 1,
                        'price'    => 299,
                        'discount' => 0,
                    ),
                ),
                'journey'         => array(
                    array(
                        'type'  => 'email_clicked',
                        'title' => __('Recovery Email Clicked', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'time'  => '2026-03-02 11:46:00',
                        'meta'  => array(
                            __('Email', 'one-page-quick-checkout-for-woocommerce-pro')      => __('Final Attempt', 'one-page-quick-checkout-for-woocommerce-pro'),
                            __('Destination', 'one-page-quick-checkout-for-woocommerce-pro') => wc_get_checkout_url(),
                        ),
                    ),
                    array(
                        'type'  => 'email_opened',
                        'title' => __('Recovery Email Opened', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'time'  => '2026-03-02 05:22:00',
                        'meta'  => array(
                            __('Email', 'one-page-quick-checkout-for-woocommerce-pro')  => __('Value Reinforcement', 'one-page-quick-checkout-for-woocommerce-pro'),
                            __('Device', 'one-page-quick-checkout-for-woocommerce-pro') => __('Desktop', 'one-page-quick-checkout-for-woocommerce-pro'),
                        ),
                    ),
                    array(
                        'type'  => 'cart_abandoned',
                        'title' => __('Cart Abandoned', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'time'  => '2026-03-01 12:01:00',
                        'meta'  => array(
                            __('Cart Total', 'one-page-quick-checkout-for-woocommerce-pro') => self::format_currency(299, 'USD'),
                            __('IP Address', 'one-page-quick-checkout-for-woocommerce-pro') => '67.11.24.18',
                        ),
                    ),
                    array(
                        'type'  => 'cart_created',
                        'title' => __('Cart Created', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'time'  => '2026-03-01 10:15:00',
                        'meta'  => array(
                            __('Email', 'one-page-quick-checkout-for-woocommerce-pro')        => 'g.weulenkranenbarg@kusterenergy.com',
                            __('Customer Name', 'one-page-quick-checkout-for-woocommerce-pro') => 'G.weulenkranenbarg',
                            __('Item Count', 'one-page-quick-checkout-for-woocommerce-pro')   => '1',
                        ),
                    ),
                ),
                'email_history'   => array(
                    array(
                        'id'         => 'cart_1003_final',
                        'name'       => __('Final Attempt', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'subject'    => __('Your cart is about to expire', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'sent_at'    => '2026-03-02 11:30:00',
                        'status'     => 'clicked',
                        'engagement' => __('1 open, 1 click', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'opened_at'  => '2026-03-02 11:40:00',
                        'clicked_at' => '2026-03-02 11:46:00',
                    ),
                    array(
                        'id'         => 'cart_1003_value',
                        'name'       => __('Value Reinforcement', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'subject'    => __('Still thinking it over?', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'sent_at'    => '2026-03-02 05:00:00',
                        'status'     => 'opened',
                        'engagement' => __('1 open', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'opened_at'  => '2026-03-02 05:22:00',
                        'clicked_at' => '',
                    ),
                    array(
                        'id'         => 'cart_1003_immediate',
                        'name'       => __('Immediate Recovery', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'subject'    => __('You left something behind', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'sent_at'    => '2026-03-01 11:30:00',
                        'status'     => 'clicked',
                        'engagement' => __('1 open, 1 click', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'opened_at'  => '2026-03-01 11:45:00',
                        'clicked_at' => '2026-03-01 11:49:00',
                    ),
                ),
            ),
            array(
                'id'              => 'cart_1004',
                'customer_name'   => 'Jezael Melgoza Jimenez',
                'customer_id'     => '',
                'email'           => 'jezael.melgoza@masvida.org',
                'cart_total'      => 79,
                'currency'        => 'USD',
                'status'          => 'abandoned',
                'created_at'      => '2026-03-07 06:10:00',
                'updated_at'      => '2026-03-08 01:45:00',
                'abandoned_at'    => '2026-03-07 06:55:00',
                'restored_at'     => '',
                'recovered_at'    => '',
                'session_id'      => 'd_b31a9c97119a45939f0cafa763de991',
                'product_context' => 'Multi Location Product & Inventory Management for WooCommerce Pro',
                'ip_address'      => '31.94.222.17',
                'user_agent'      => $ua_browser,
                'items'           => array(
                    array(
                        'name'     => 'Multi Location Product & Inventory Management for WooCommerce Pro',
                        'quantity' => 1,
                        'price'    => 79,
                        'discount' => 0,
                    ),
                ),
                'journey'         => array(
                    array(
                        'type'  => 'email_sent',
                        'title' => __('Recovery Email Sent', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'time'  => '2026-03-08 01:45:00',
                        'meta'  => array(
                            __('Email', 'one-page-quick-checkout-for-woocommerce-pro')   => __('Final Attempt', 'one-page-quick-checkout-for-woocommerce-pro'),
                            __('Subject', 'one-page-quick-checkout-for-woocommerce-pro') => __('Your cart is about to expire', 'one-page-quick-checkout-for-woocommerce-pro'),
                        ),
                    ),
                    array(
                        'type'  => 'email_opened',
                        'title' => __('Recovery Email Opened', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'time'  => '2026-03-07 21:32:00',
                        'meta'  => array(
                            __('Email', 'one-page-quick-checkout-for-woocommerce-pro')  => __('Value Reinforcement', 'one-page-quick-checkout-for-woocommerce-pro'),
                            __('Device', 'one-page-quick-checkout-for-woocommerce-pro') => __('Mobile', 'one-page-quick-checkout-for-woocommerce-pro'),
                        ),
                    ),
                    array(
                        'type'  => 'cart_abandoned',
                        'title' => __('Cart Abandoned', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'time'  => '2026-03-07 06:55:00',
                        'meta'  => array(
                            __('Cart Total', 'one-page-quick-checkout-for-woocommerce-pro') => self::format_currency(79, 'USD'),
                            __('IP Address', 'one-page-quick-checkout-for-woocommerce-pro') => '31.94.222.17',
                        ),
                    ),
                    array(
                        'type'  => 'cart_created',
                        'title' => __('Cart Created', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'time'  => '2026-03-07 06:10:00',
                        'meta'  => array(
                            __('Email', 'one-page-quick-checkout-for-woocommerce-pro')        => 'jezael.melgoza@masvida.org',
                            __('Customer Name', 'one-page-quick-checkout-for-woocommerce-pro') => 'Jezael Melgoza Jimenez',
                            __('Item Count', 'one-page-quick-checkout-for-woocommerce-pro')   => '1',
                        ),
                    ),
                ),
                'email_history'   => array(
                    array(
                        'id'         => 'cart_1004_final',
                        'name'       => __('Final Attempt', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'subject'    => __('Your cart is about to expire', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'sent_at'    => '2026-03-08 01:45:00',
                        'status'     => 'sent',
                        'engagement' => '-',
                        'opened_at'  => '',
                        'clicked_at' => '',
                    ),
                    array(
                        'id'         => 'cart_1004_value',
                        'name'       => __('Value Reinforcement', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'subject'    => __('Still thinking it over?', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'sent_at'    => '2026-03-07 21:15:00',
                        'status'     => 'opened',
                        'engagement' => __('1 open', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'opened_at'  => '2026-03-07 21:32:00',
                        'clicked_at' => '',
                    ),
                    array(
                        'id'         => 'cart_1004_immediate',
                        'name'       => __('Immediate Recovery', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'subject'    => __('You left something behind', 'one-page-quick-checkout-for-woocommerce-pro'),
                        'sent_at'    => '2026-03-07 07:10:00',
                        'status'     => 'sent',
                        'engagement' => '-',
                        'opened_at'  => '',
                        'clicked_at' => '',
                    ),
                ),
            ),
        );
    }

    private static function get_template_name_options()
    {
        $options = array();

        foreach (self::get_templates() as $template) {
            if (! empty($template['name'])) {
                $options[] = $template['name'];
            }
        }

        return array_values(array_unique($options));
    }

    private static function get_latest_date($dates)
    {
        $timestamps = array_filter(array_map(array(__CLASS__, 'to_timestamp'), array_filter((array) $dates)));

        return empty($timestamps) ? '' : wp_date('Y-m-d H:i:s', max($timestamps), wp_timezone());
    }

    private static function paginate_items($items, $page, $per_page)
    {
        $total_items = count($items);
        $total_pages = max(1, (int) ceil($total_items / max(1, $per_page)));
        $page        = min(max(1, $page), $total_pages);
        $offset      = ($page - 1) * $per_page;

        return array(
            'items'       => array_slice($items, $offset, $per_page),
            'page'        => $page,
            'per_page'    => $per_page,
            'total_items' => $total_items,
            'total_pages' => $total_pages,
        );
    }

    private static function render_pagination($tab, $pagination, $preserved_keys)
    {
        if (empty($pagination['total_pages']) || $pagination['total_pages'] < 2) {
            return;
        }

        if ('email_templates' === $tab) {
            $page_key = 'cr_template_page';
            $base_tab = 'email';
        } elseif ('email_activity' === $tab) {
            $page_key = 'cr_activity_page';
            $base_tab = 'email';
        } else {
            $page_key = 'carts' === $tab ? 'cr_cart_page' : 'cr_activity_page';
            $base_tab = $tab;
        }

        $current_page = max(1, absint($pagination['page']));
        $total_pages  = max(1, absint($pagination['total_pages']));
        $base_args    = array('tab' => $base_tab);

        if ('email_templates' === $tab) {
            $base_args['cr_email_view'] = 'templates';
        } elseif ('email_activity' === $tab) {
            $base_args['cr_email_view'] = 'activity';
        }

        foreach ($preserved_keys as $key) {
            if (! isset($_GET[$key])) {
                continue;
            }

            $value = wp_unslash($_GET[$key]);
            if (is_array($value)) {
                continue;
            }
            $base_args[$key] = sanitize_text_field($value);
        }

        $get_page_url = function ($page) use ($base_args, $page_key) {
            $args = $base_args;
            $args[$page_key] = max(1, absint($page));

            return self::get_page_url($args);
        };

        $page_numbers = array_filter(array_unique(array(
            1,
            2,
            $current_page - 1,
            $current_page,
            $current_page + 1,
            $total_pages - 1,
            $total_pages,
        )), function ($page) use ($total_pages) {
            return $page >= 1 && $page <= $total_pages;
        });

        sort($page_numbers, SORT_NUMERIC);
        ?>
        <div class="onepaqucpro-cr-pagination">
            <span><?php echo esc_html(sprintf(__('%1$d of %2$d', 'one-page-quick-checkout-for-woocommerce-pro'), $current_page, $total_pages)); ?></span>
            <div class="onepaqucpro-cr-pagination__links">
                <?php if ($current_page > 1) : ?>
                    <a class="button button-small button-secondary onepaqucpro-cr-pagination__control" href="<?php echo esc_url($get_page_url(1)); ?>" aria-label="<?php esc_attr_e('First page', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">&laquo;</a>
                    <a class="button button-small button-secondary onepaqucpro-cr-pagination__control" href="<?php echo esc_url($get_page_url($current_page - 1)); ?>" aria-label="<?php esc_attr_e('Previous page', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">&lsaquo;</a>
                <?php else : ?>
                    <span class="button button-small button-secondary onepaqucpro-cr-pagination__control is-disabled" aria-disabled="true">&laquo;</span>
                    <span class="button button-small button-secondary onepaqucpro-cr-pagination__control is-disabled" aria-disabled="true">&lsaquo;</span>
                <?php endif; ?>

                <?php $previous_page = 0; ?>
                <?php foreach ($page_numbers as $page) : ?>
                    <?php if ($previous_page && $page > $previous_page + 1) : ?>
                        <span class="onepaqucpro-cr-pagination__ellipsis" aria-hidden="true">&hellip;</span>
                    <?php endif; ?>
                    <?php if ($page === $current_page) : ?>
                        <span class="button button-small button-primary onepaqucpro-cr-pagination__page is-current" aria-current="page"><?php echo esc_html($page); ?></span>
                    <?php else : ?>
                        <a class="button button-small button-secondary onepaqucpro-cr-pagination__page" href="<?php echo esc_url($get_page_url($page)); ?>"><?php echo esc_html($page); ?></a>
                    <?php endif; ?>
                    <?php $previous_page = $page; ?>
                <?php endforeach; ?>

                <?php if ($current_page < $total_pages) : ?>
                    <a class="button button-small button-secondary onepaqucpro-cr-pagination__control" href="<?php echo esc_url($get_page_url($current_page + 1)); ?>" aria-label="<?php esc_attr_e('Next page', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">&rsaquo;</a>
                    <a class="button button-small button-secondary onepaqucpro-cr-pagination__control" href="<?php echo esc_url($get_page_url($total_pages)); ?>" aria-label="<?php esc_attr_e('Last page', 'one-page-quick-checkout-for-woocommerce-pro'); ?>">&raquo;</a>
                <?php else : ?>
                    <span class="button button-small button-secondary onepaqucpro-cr-pagination__control is-disabled" aria-disabled="true">&rsaquo;</span>
                    <span class="button button-small button-secondary onepaqucpro-cr-pagination__control is-disabled" aria-disabled="true">&raquo;</span>
                <?php endif; ?>
            </div>
        </div>
<?php
    }

    private static function get_cart_action_url($cart_id, $action)
    {
        $url = add_query_arg(
            array(
                'action'      => 'onepaqucpro_cart_recovery_cart_action',
                'cart_id'     => $cart_id,
                'cart_action' => $action,
            ),
            admin_url('admin-post.php')
        );

        return wp_nonce_url($url, 'onepaqucpro_cart_recovery_cart_action_' . $cart_id . '_' . $action);
    }

    private static function get_email_activity_action_url($email_id, $action)
    {
        $email_id = absint($email_id);
        $action   = sanitize_key($action);
        $url = add_query_arg(
            array(
                'action'                => 'onepaqucpro_cart_recovery_email_activity_action',
                'email_id'              => $email_id,
                'email_activity_action' => $action,
            ),
            admin_url('admin-post.php')
        );

        return wp_nonce_url($url, 'onepaqucpro_cart_recovery_email_activity_action_' . $email_id . '_' . $action);
    }

    private static function get_cart_action_state($cart)
    {
        if (self::is_locked_mode()) {
            return array(
                'can_send_next'   => false,
                'can_resend_last' => false,
            );
        }

        $email_history = isset($cart['email_history']) && is_array($cart['email_history']) ? $cart['email_history'] : array();
        $sent_template_ids = array_values(array_filter(array_map(function ($email) {
            return isset($email['template_id']) ? sanitize_key($email['template_id']) : '';
        }, $email_history)));
        $can_send_next = false;

        foreach (self::get_templates() as $template) {
            $template_id = ! empty($template['id']) ? sanitize_key($template['id']) : '';

            if (! $template_id || empty($template['enabled']) || in_array($template_id, $sent_template_ids, true)) {
                continue;
            }

            $can_send_next = true;
            break;
        }

        return array(
            'can_send_next'   => $can_send_next,
            'can_resend_last' => ! empty($email_history),
        );
    }

    private static function get_cart_action_state_by_id($cart_id)
    {
        $cart = self::get_cart_by_id($cart_id);

        return $cart ? self::get_cart_action_state($cart) : null;
    }

    private static function get_cart_by_id($cart_id)
    {
        foreach (self::get_carts() as $cart) {
            if (isset($cart['id']) && $cart['id'] === $cart_id) {
                return $cart;
            }
        }

        return null;
    }

    private static function render_cart_status_badges($cart)
    {
        $badges = array(self::render_status_badge($cart['status']));

        if (! empty($cart['admin_state'])) {
            $badges[] = self::render_status_badge($cart['admin_state']);
        }

        if (! empty($cart['unsubscribed'])) {
            $badges[] = self::render_status_badge('unsubscribed');
        }

        if (! empty($cart['is_high_value'])) {
            $badges[] = self::render_status_badge('high_value');
        }

        return implode(' ', $badges);
    }

    private static function format_duration($seconds)
    {
        $seconds = max(0, (int) $seconds);
        if (! $seconds) {
            return '-';
        }

        $days = floor($seconds / DAY_IN_SECONDS);
        $hours = floor(($seconds % DAY_IN_SECONDS) / HOUR_IN_SECONDS);

        if ($days > 0) {
            return sprintf(_n('%d day', '%d days', $days, 'one-page-quick-checkout-for-woocommerce-pro'), $days) . ($hours ? ' ' . sprintf(_n('%d hour', '%d hours', $hours, 'one-page-quick-checkout-for-woocommerce-pro'), $hours) : '');
        }

        $hours = max(1, round($seconds / HOUR_IN_SECONDS));

        return sprintf(_n('%d hour', '%d hours', $hours, 'one-page-quick-checkout-for-woocommerce-pro'), $hours);
    }

    private static function map_event_type_to_activity_status($event_type)
    {
        $map = array(
            'cart_abandoned'    => 'abandoned',
            'cart_restored'     => 'restored',
            'cart_recovered'    => 'recovered',
            'cart_unsubscribed' => 'unsubscribed',
            'email_failed'      => 'failed',
            'email_opened'      => 'opened',
            'email_clicked'     => 'clicked',
            'email_sent'        => 'sent',
        );

        return isset($map[$event_type]) ? $map[$event_type] : sanitize_key($event_type);
    }

    private static function render_status_badge($status)
    {
        $labels = array(
            'active'      => __('Active', 'one-page-quick-checkout-for-woocommerce-pro'),
            'abandoned' => __('Abandoned', 'one-page-quick-checkout-for-woocommerce-pro'),
            'recovered' => __('Recovered', 'one-page-quick-checkout-for-woocommerce-pro'),
            'ignored'    => __('Ignored', 'one-page-quick-checkout-for-woocommerce-pro'),
            'archived'   => __('Archived', 'one-page-quick-checkout-for-woocommerce-pro'),
            'unsubscribed' => __('Unsubscribed', 'one-page-quick-checkout-for-woocommerce-pro'),
            'high_value' => __('High Value', 'one-page-quick-checkout-for-woocommerce-pro'),
        );

        return sprintf(
            '<span class="onepaqucpro-cr-badge is-%1$s">%2$s</span>',
            esc_attr($status),
            esc_html(isset($labels[$status]) ? $labels[$status] : ucfirst($status))
        );
    }

    private static function render_email_status_badge($status)
    {
        $labels = array(
            'sent'     => __('Sent', 'one-page-quick-checkout-for-woocommerce-pro'),
            'opened'   => __('Opened', 'one-page-quick-checkout-for-woocommerce-pro'),
            'clicked'  => __('Clicked', 'one-page-quick-checkout-for-woocommerce-pro'),
            'recovered' => __('Recovered', 'one-page-quick-checkout-for-woocommerce-pro'),
            'failed'   => __('Failed', 'one-page-quick-checkout-for-woocommerce-pro'),
            'abandoned' => __('Abandoned', 'one-page-quick-checkout-for-woocommerce-pro'),
            'restored' => __('Restored', 'one-page-quick-checkout-for-woocommerce-pro'),
            'unsubscribed' => __('Unsubscribed', 'one-page-quick-checkout-for-woocommerce-pro'),
        );

        return sprintf(
            '<span class="onepaqucpro-cr-email-badge is-%1$s">%2$s</span>',
            esc_attr($status),
            esc_html(isset($labels[$status]) ? $labels[$status] : ucfirst($status))
        );
    }

    private static function format_currency($amount, $currency = '')
    {
        if (! $currency) {
            $currency = self::get_reporting_currency();
        }

        return wc_price($amount, array('currency' => $currency));
    }

    private static function format_profile_address($address)
    {
        if (! is_array($address) || empty($address)) {
            return '-';
        }

        $country = isset($address['country']) ? sanitize_text_field($address['country']) : '';
        $state   = isset($address['state']) ? sanitize_text_field($address['state']) : '';

        if ($country && function_exists('WC') && WC()->countries) {
            $countries = WC()->countries->get_countries();
            if (isset($countries[$country])) {
                $country = $countries[$country];
            }

            $states = WC()->countries->get_states(isset($address['country']) ? $address['country'] : '');
            if ($state && is_array($states) && isset($states[$state])) {
                $state = $states[$state];
            }
        }

        $parts = array(
            isset($address['address_1']) ? sanitize_text_field($address['address_1']) : '',
            isset($address['address_2']) ? sanitize_text_field($address['address_2']) : '',
            isset($address['city']) ? sanitize_text_field($address['city']) : '',
            $state,
            isset($address['postcode']) ? sanitize_text_field($address['postcode']) : '',
            $country,
        );

        $formatted = implode(', ', array_filter($parts));

        return $formatted ? $formatted : '-';
    }

    private static function format_item_meta_label($label)
    {
        $label = preg_replace('/^attribute_/', '', (string) $label);
        $label = str_replace(array('pa_', '_', '-'), array('', ' ', ' '), $label);

        return ucwords(trim($label));
    }

    private static function format_metric_value($value, $type)
    {
        switch ($type) {
            case 'currency':
                return self::format_currency($value);
            case 'percent':
                return esc_html(self::format_percent($value));
            case 'number':
            default:
                return esc_html(number_format_i18n($value));
        }
    }

    private static function format_delta_value($current, $previous, $type, $compare_mode)
    {
        if ('previous_period' !== $compare_mode || null === $previous) {
            return __('Current period', 'one-page-quick-checkout-for-woocommerce-pro');
        }

        $delta = $current - $previous;

        if (0 == $delta) {
            return __('No change vs previous period', 'one-page-quick-checkout-for-woocommerce-pro');
        }

        switch ($type) {
            case 'currency':
                return sprintf(
                    /* translators: %s: currency delta. */
                    __('%s vs previous period', 'one-page-quick-checkout-for-woocommerce-pro'),
                    wp_strip_all_tags(self::format_currency($delta))
                );
            case 'percent':
                return sprintf(
                    /* translators: %s: percentage delta. */
                    __('%s pts vs previous period', 'one-page-quick-checkout-for-woocommerce-pro'),
                    self::format_signed_number($delta, 2)
                );
            case 'number':
            default:
                return sprintf(
                    /* translators: %s: numeric delta. */
                    __('%s vs previous period', 'one-page-quick-checkout-for-woocommerce-pro'),
                    self::format_signed_number($delta, 0)
                );
        }
    }

    private static function get_analytics_delta_context($current, $previous, $type, $range)
    {
        if (empty($range['compare']) || 'previous_period' !== $range['compare'] || null === $previous) {
            return array(
                'direction' => 'neutral',
                'text'      => __('Current period', 'one-page-quick-checkout-for-woocommerce-pro'),
                'arrow'     => '',
            );
        }

        $label = isset($range['compare_label']) ? $range['compare_label'] : self::get_range_comparison_label(isset($range['period']) ? $range['period'] : 'custom');
        $mode  = 'percent' === $type ? 'points' : 'relative_percent';

        return self::get_metric_delta_context($current, $previous, $label, $mode);
    }

    private static function get_metric_delta_context($current, $previous, $label, $mode = 'relative_percent')
    {
        $current  = (float) $current;
        $previous = (float) $previous;
        $delta    = $current - $previous;

        if (abs($delta) < 0.00001) {
            return array(
                'direction' => 'neutral',
                'text'      => sprintf(
                    /* translators: %s: comparison label. */
                    __('No change %s', 'one-page-quick-checkout-for-woocommerce-pro'),
                    $label
                ),
                'arrow'     => '',
            );
        }

        $direction = $delta > 0 ? 'increase' : 'decrease';
        $arrow     = $delta > 0 ? 'up' : 'down';

        if ('points' === $mode) {
            $value = sprintf(
                /* translators: %s: signed percentage point delta. */
                __('%s pts', 'one-page-quick-checkout-for-woocommerce-pro'),
                self::format_signed_number($delta, 2)
            );
        } elseif (0.0 === $previous) {
            $value = $delta > 0
                ? __('New', 'one-page-quick-checkout-for-woocommerce-pro')
                : self::format_signed_number(-100, 0) . '%';
        } else {
            $value = self::format_signed_number(($delta / abs($previous)) * 100, 0) . '%';
        }

        return array(
            'direction' => $direction,
            'text'      => sprintf(
                /* translators: 1: delta value, 2: comparison label. */
                __('%1$s %2$s', 'one-page-quick-checkout-for-woocommerce-pro'),
                $value,
                $label
            ),
            'arrow'     => $arrow,
        );
    }

    private static function render_metric_delta($delta, $class = 'onepaqucpro-cr-kpi-card__delta')
    {
        if (empty($delta) || empty($delta['text'])) {
            return '';
        }

        $direction = isset($delta['direction']) ? sanitize_html_class($delta['direction']) : 'neutral';
        $classes   = trim($class . ' is-' . $direction);
        $arrow     = '';

        if (! empty($delta['arrow'])) {
            $arrow = 'up' === $delta['arrow'] ? '&uarr;' : '&darr;';
        }

        return sprintf(
            '<span class="%1$s">%2$s<span class="onepaqucpro-cr-kpi-card__delta-text">%3$s</span></span>',
            esc_attr($classes),
            $arrow ? '<span class="onepaqucpro-cr-kpi-card__delta-arrow" aria-hidden="true">' . $arrow . '</span>' : '',
            esc_html($delta['text'])
        );
    }

    private static function format_percent($value)
    {
        return number_format_i18n((float) $value, 2) . '%';
    }

    private static function get_reporting_currency()
    {
        return function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : 'USD';
    }

    private static function format_signed_number($value, $decimals)
    {
        $formatted = number_format_i18n(abs((float) $value), $decimals);

        return ($value > 0 ? '+' : '-') . $formatted;
    }

    private static function format_datetime($date)
    {
        if (! $date) {
            return '-';
        }

        return wp_date('F j, Y g:i a', self::to_timestamp($date), wp_timezone());
    }

    private static function get_relative_time($date)
    {
        if (! $date) {
            return '-';
        }

        $timestamp = self::to_timestamp($date);
        if (! $timestamp) {
            return '-';
        }

        return human_time_diff($timestamp, current_time('timestamp')) . ' ' . __('ago', 'one-page-quick-checkout-for-woocommerce-pro');
    }

    private static function get_sort_link($tab, $column, $label, $current_sort, $current_order)
    {
        $next_order = ($current_sort === $column && 'asc' === $current_order) ? 'desc' : 'asc';
        $args       = array('tab' => $tab);

        if ('carts' === $tab) {
            $args['cr_cart_status']        = sanitize_text_field(isset($_GET['cr_cart_status']) ? wp_unslash($_GET['cr_cart_status']) : 'all');
            $args['cr_cart_customer_type'] = sanitize_text_field(isset($_GET['cr_cart_customer_type']) ? wp_unslash($_GET['cr_cart_customer_type']) : 'all');
            $args['cr_cart_device']        = sanitize_text_field(isset($_GET['cr_cart_device']) ? wp_unslash($_GET['cr_cart_device']) : 'all');
            $args['cr_cart_template']      = sanitize_text_field(isset($_GET['cr_cart_template']) ? wp_unslash($_GET['cr_cart_template']) : '');
            $args['cr_cart_source']        = sanitize_text_field(isset($_GET['cr_cart_source']) ? wp_unslash($_GET['cr_cart_source']) : 'all');
            $args['cr_cart_from']          = self::sanitize_date(isset($_GET['cr_cart_from']) ? wp_unslash($_GET['cr_cart_from']) : '');
            $args['cr_cart_to']            = self::sanitize_date(isset($_GET['cr_cart_to']) ? wp_unslash($_GET['cr_cart_to']) : '');
            $args['cr_cart_min']           = self::sanitize_decimal(isset($_GET['cr_cart_min']) ? wp_unslash($_GET['cr_cart_min']) : '');
            $args['cr_cart_max']           = self::sanitize_decimal(isset($_GET['cr_cart_max']) ? wp_unslash($_GET['cr_cart_max']) : '');
            $args['cr_cart_search']        = sanitize_text_field(isset($_GET['cr_cart_search']) ? wp_unslash($_GET['cr_cart_search']) : '');
            $args['cr_cart_sort']          = $column;
            $args['cr_cart_order']         = $next_order;
        } elseif ('email_templates' === $tab) {
            $args['tab']                    = 'email';
            $args['cr_email_view']          = 'templates';
            $args['cr_template_status']     = sanitize_text_field(isset($_GET['cr_template_status']) ? wp_unslash($_GET['cr_template_status']) : 'all');
            $args['cr_template_delay_unit'] = sanitize_text_field(isset($_GET['cr_template_delay_unit']) ? wp_unslash($_GET['cr_template_delay_unit']) : 'all');
            $args['cr_template_search']     = sanitize_text_field(isset($_GET['cr_template_search']) ? wp_unslash($_GET['cr_template_search']) : '');
            $args['cr_template_sort']       = $column;
            $args['cr_template_order']      = $next_order;
        } elseif ('email_activity' === $tab) {
            $args['tab']                   = 'email';
            $args['cr_email_view']         = 'activity';
            $args['cr_activity_status']    = sanitize_text_field(isset($_GET['cr_activity_status']) ? wp_unslash($_GET['cr_activity_status']) : 'all');
            $args['cr_activity_from']      = self::sanitize_date(isset($_GET['cr_activity_from']) ? wp_unslash($_GET['cr_activity_from']) : '');
            $args['cr_activity_to']        = self::sanitize_date(isset($_GET['cr_activity_to']) ? wp_unslash($_GET['cr_activity_to']) : '');
            $args['cr_activity_cart']      = sanitize_text_field(isset($_GET['cr_activity_cart']) ? wp_unslash($_GET['cr_activity_cart']) : '');
            $args['cr_activity_template']  = sanitize_text_field(isset($_GET['cr_activity_template']) ? wp_unslash($_GET['cr_activity_template']) : '');
            $args['cr_activity_recipient'] = sanitize_text_field(isset($_GET['cr_activity_recipient']) ? wp_unslash($_GET['cr_activity_recipient']) : '');
            $args['cr_activity_search']    = sanitize_text_field(isset($_GET['cr_activity_search']) ? wp_unslash($_GET['cr_activity_search']) : '');
            $args['cr_activity_sort']      = $column;
            $args['cr_activity_order']     = $next_order;
        } else {
            $args['cr_activity_status']    = sanitize_text_field(isset($_GET['cr_activity_status']) ? wp_unslash($_GET['cr_activity_status']) : 'all');
            $args['cr_activity_type']      = sanitize_text_field(isset($_GET['cr_activity_type']) ? wp_unslash($_GET['cr_activity_type']) : 'all');
            $args['cr_activity_from']      = self::sanitize_date(isset($_GET['cr_activity_from']) ? wp_unslash($_GET['cr_activity_from']) : '');
            $args['cr_activity_to']        = self::sanitize_date(isset($_GET['cr_activity_to']) ? wp_unslash($_GET['cr_activity_to']) : '');
            $args['cr_activity_cart']      = sanitize_text_field(isset($_GET['cr_activity_cart']) ? wp_unslash($_GET['cr_activity_cart']) : '');
            $args['cr_activity_template']  = sanitize_text_field(isset($_GET['cr_activity_template']) ? wp_unslash($_GET['cr_activity_template']) : '');
            $args['cr_activity_recipient'] = sanitize_text_field(isset($_GET['cr_activity_recipient']) ? wp_unslash($_GET['cr_activity_recipient']) : '');
            $args['cr_activity_search']    = sanitize_text_field(isset($_GET['cr_activity_search']) ? wp_unslash($_GET['cr_activity_search']) : '');
            $args['cr_activity_sort']      = $column;
            $args['cr_activity_order']     = $next_order;
        }

        $icon = 'dashicons-arrow-down-alt2';
        if ($current_sort === $column) {
            $icon = 'asc' === $current_order ? 'dashicons-arrow-up-alt2' : 'dashicons-arrow-down-alt2';
        }

        return sprintf(
            '<a href="%1$s">%2$s <span class="dashicons %3$s"></span></a>',
            esc_url(self::get_page_url($args)),
            esc_html($label),
            esc_attr($icon)
        );
    }

    private static function get_event_icon($type)
    {
        $icons = array(
            'cart_created'   => 'dashicons-cart',
            'cart_updated'   => 'dashicons-update',
            'cart_abandoned' => 'dashicons-clock',
            'email_sent'     => 'dashicons-email-alt',
            'email_opened'   => 'dashicons-visibility',
            'email_clicked'  => 'dashicons-admin-links',
            'email_failed'   => 'dashicons-warning',
            'cart_restored'  => 'dashicons-controls-play',
            'cart_recovered' => 'dashicons-yes-alt',
            'cart_unsubscribed' => 'dashicons-dismiss',
            'cart_archived'  => 'dashicons-archive',
            'cart_ignored'   => 'dashicons-hidden',
        );

        return isset($icons[$type]) ? $icons[$type] : 'dashicons-info';
    }

    private static function assert_admin_access()
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'one-page-quick-checkout-for-woocommerce-pro'));
        }
    }

    private static function redirect_with_notice($tab, $notice)
    {
        wp_safe_redirect(self::get_page_url(array(
            'tab'       => $tab,
            'cr_notice' => $notice,
        )));
        exit;
    }

    private static function redirect_with_notice_from_referer($notice, $fallback_args = array())
    {
        $fallback_args = is_array($fallback_args) ? $fallback_args : array();
        $url           = wp_get_referer();

        if (! $url) {
            $url = self::get_page_url($fallback_args);
        }

        $url = remove_query_arg('cr_notice', $url);
        $url = add_query_arg('cr_notice', sanitize_key($notice), $url);

        wp_safe_redirect($url);
        exit;
    }

    private static function cart_exists($cart_id)
    {
        if (class_exists('Onepaqucpro_Cart_Recovery_Tracker')) {
            return Onepaqucpro_Cart_Recovery_Tracker::cart_exists($cart_id);
        }

        foreach (self::get_carts() as $cart) {
            if ($cart['id'] === $cart_id) {
                return true;
            }
        }

        return false;
    }

    private static function is_in_range($date, $range)
    {
        if (! $date) {
            return false;
        }

        $timestamp = self::to_timestamp($date);

        return $timestamp >= $range['start']->getTimestamp() && $timestamp <= $range['end']->getTimestamp();
    }

    private static function format_chart_key($date)
    {
        return wp_date('Y-m-d', self::to_timestamp($date), wp_timezone());
    }

    private static function sanitize_choice($value, $allowed, $fallback)
    {
        $value = sanitize_key($value);

        return in_array($value, $allowed, true) ? $value : $fallback;
    }

    private static function sanitize_date($value)
    {
        $value = sanitize_text_field($value);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : '';
    }

    private static function sanitize_decimal($value)
    {
        $value = sanitize_text_field($value);

        if ('' === $value) {
            return '';
        }

        return is_numeric($value) ? (string) $value : '';
    }

    private static function sanitize_time_string($value, $fallback)
    {
        $value = sanitize_text_field($value);

        return preg_match('/^\d{2}:\d{2}$/', $value) ? $value : $fallback;
    }

    private static function sanitize_integer_list($value)
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('absint', $value)));
        }

        $parts = preg_split('/[\r\n,]+/', (string) $value);

        return array_values(array_filter(array_map('absint', $parts)));
    }

    private static function sanitize_text_list($value)
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('sanitize_text_field', $value)));
        }

        $parts = preg_split('/[\r\n,]+/', (string) $value);

        return array_values(array_filter(array_map('sanitize_text_field', $parts)));
    }

    private static function to_timestamp($date)
    {
        try {
            $datetime = new DateTimeImmutable($date, wp_timezone());
        } catch (Exception $exception) {
            return 0;
        }

        return $datetime->getTimestamp();
    }
}

Onepaqucpro_Cart_Recovery_Admin::init();

function onepaqucpro_cart_recovery_page()
{
    Onepaqucpro_Cart_Recovery_Admin::render_page();
}

function onepaqucpro_cart_recovery_template_page()
{
    Onepaqucpro_Cart_Recovery_Admin::render_template_page();
}
