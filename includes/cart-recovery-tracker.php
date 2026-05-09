<?php
if (! defined('ABSPATH')) {
    exit;
}

class Onepaqucpro_Cart_Recovery_Tracker
{
    const SCHEMA_VERSION = '1.1.9.27';
    const VERSION_OPTION = 'onepaqucpro_cart_recovery_schema_version';
    const SETTINGS_OPTION = 'onepaqucpro_cart_recovery_settings';
    const TEMPLATES_OPTION = 'onepaqucpro_cart_recovery_templates';
    const CRON_HOOK = 'onepaqucpro_cart_recovery_process_queue';
    const SESSION_TRACKING_KEY = 'onepaqucpro_cr_tracking_key';
    const SESSION_RESTORE_TOKEN = 'onepaqucpro_cr_restore_token';

    public static function init()
    {
        add_action('init', array(__CLASS__, 'maybe_install'), 5);
        add_filter('cron_schedules', array(__CLASS__, 'register_cron_schedule'));
        add_action('wp_loaded', array(__CLASS__, 'schedule_queue'));
        add_action(self::CRON_HOOK, array(__CLASS__, 'process_queue'));

        add_action('woocommerce_after_calculate_totals', array(__CLASS__, 'track_current_cart'), 1100);
        add_action('woocommerce_cart_loaded_from_session', array(__CLASS__, 'track_current_cart'), 1100);
        add_action('woocommerce_checkout_update_order_review', array(__CLASS__, 'capture_checkout_post_data'), 10, 1);
        add_action('woocommerce_checkout_process', array(__CLASS__, 'capture_checkout_request'));
        add_action('woocommerce_checkout_order_processed', array(__CLASS__, 'handle_checkout_order_processed'), 10, 3);
        add_action('woocommerce_store_api_checkout_order_processed', array(__CLASS__, 'handle_store_api_order_processed'));
        add_action('woocommerce_cart_emptied', array(__CLASS__, 'handle_cart_emptied'));

        add_action('template_redirect', array(__CLASS__, 'handle_public_requests'), 1);
    }

    private static function is_free_mode()
    {
        return defined('ONEPAQUC_CART_RECOVERY_FREE_MODE') && ONEPAQUC_CART_RECOVERY_FREE_MODE;
    }

    private static function can_use_premium_features()
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

    public static function install()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $carts_table     = self::get_carts_table();
        $emails_table    = self::get_emails_table();
        $events_table    = self::get_events_table();

        dbDelta(
            "CREATE TABLE {$carts_table} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                cart_key varchar(64) NOT NULL,
                session_id varchar(191) NOT NULL,
                customer_id bigint(20) unsigned NOT NULL DEFAULT 0,
                customer_name varchar(255) NOT NULL DEFAULT '',
                email varchar(190) NOT NULL DEFAULT '',
                cart_total decimal(18,2) NOT NULL DEFAULT 0.00,
                currency varchar(12) NOT NULL DEFAULT '',
                item_count int(11) unsigned NOT NULL DEFAULT 0,
                product_context text NULL,
                ip_address varchar(100) NOT NULL DEFAULT '',
                user_agent text NULL,
                status varchar(24) NOT NULL DEFAULT 'active',
                cart_hash varchar(64) NOT NULL DEFAULT '',
                recovery_token varchar(64) NOT NULL DEFAULT '',
                unsubscribed tinyint(1) NOT NULL DEFAULT 0,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                abandoned_at datetime NULL DEFAULT NULL,
                restored_at datetime NULL DEFAULT NULL,
                recovered_at datetime NULL DEFAULT NULL,
                recovered_order_id bigint(20) unsigned NOT NULL DEFAULT 0,
                cart_snapshot longtext NULL,
                checkout_data longtext NULL,
                metadata longtext NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY cart_key (cart_key),
                UNIQUE KEY recovery_token (recovery_token),
                KEY session_id (session_id),
                KEY status (status),
                KEY updated_at (updated_at),
                KEY abandoned_at (abandoned_at),
                KEY recovered_at (recovered_at),
                KEY email (email)
            ) {$charset_collate};"
        );

        dbDelta(
            "CREATE TABLE {$emails_table} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                cart_id bigint(20) unsigned NOT NULL,
                template_id varchar(64) NOT NULL DEFAULT '',
                template_name varchar(255) NOT NULL DEFAULT '',
                recipient varchar(190) NOT NULL DEFAULT '',
                subject text NULL,
                status varchar(24) NOT NULL DEFAULT 'sent',
                open_token varchar(64) NOT NULL DEFAULT '',
                click_token varchar(64) NOT NULL DEFAULT '',
                discount_code varchar(100) NOT NULL DEFAULT '',
                sent_at datetime NOT NULL,
                opened_at datetime NULL DEFAULT NULL,
                clicked_at datetime NULL DEFAULT NULL,
                payload longtext NULL,
                delivery_error text NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY open_token (open_token),
                UNIQUE KEY click_token (click_token),
                KEY cart_id (cart_id),
                KEY template_id (template_id),
                KEY sent_at (sent_at),
                KEY status (status)
            ) {$charset_collate};"
        );

        dbDelta(
            "CREATE TABLE {$events_table} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                cart_id bigint(20) unsigned NOT NULL,
                event_type varchar(64) NOT NULL DEFAULT '',
                event_title varchar(255) NOT NULL DEFAULT '',
                event_time datetime NOT NULL,
                payload longtext NULL,
                PRIMARY KEY  (id),
                KEY cart_id (cart_id),
                KEY event_type (event_type),
                KEY event_time (event_time)
            ) {$charset_collate};"
        );

        update_option(self::VERSION_OPTION, self::SCHEMA_VERSION, false);
        self::schedule_queue();
    }

    public static function deactivate()
    {
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }

    public static function maybe_install()
    {
        if (get_option(self::VERSION_OPTION) !== self::SCHEMA_VERSION) {
            self::install();
        }
    }

    public static function register_cron_schedule($schedules)
    {
        if (! isset($schedules['onepaqucpro_every_five_minutes'])) {
            $schedules['onepaqucpro_every_five_minutes'] = array(
                'interval' => 5 * MINUTE_IN_SECONDS,
                'display'  => __('Every 5 Minutes', 'one-page-quick-checkout-for-woocommerce-pro'),
            );
        }

        return $schedules;
    }

    public static function schedule_queue()
    {
        if (! wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time() + MINUTE_IN_SECONDS, 'onepaqucpro_every_five_minutes', self::CRON_HOOK);
        }
    }

    public static function track_current_cart($unused = null)
    {
        self::capture_cart(array());
    }

    public static function capture_checkout_post_data($post_data)
    {
        $checkout_data = array();
        if (is_string($post_data) && '' !== $post_data) {
            parse_str($post_data, $checkout_data);
        }

        self::capture_cart(
            array(
                'checkout_data' => $checkout_data,
            )
        );
    }

    public static function capture_checkout_request()
    {
        $checkout_data = isset($_POST) ? wp_unslash($_POST) : array();
        self::capture_cart(
            array(
                'checkout_data' => is_array($checkout_data) ? $checkout_data : array(),
            )
        );
    }

    public static function handle_checkout_order_processed($order_id, $posted_data, $order)
    {
        if (! $order instanceof WC_Order) {
            $order = wc_get_order($order_id);
        }

        self::mark_cart_recovered_from_order($order);
    }

    public static function handle_store_api_order_processed($order)
    {
        if (! $order instanceof WC_Order) {
            return;
        }

        self::mark_cart_recovered_from_order($order);
    }

    public static function handle_cart_emptied()
    {
        if (! self::has_session()) {
            return;
        }

        $tracking_key = WC()->session->get(self::SESSION_TRACKING_KEY);
        $cart_row     = $tracking_key ? self::get_cart_row_by_key($tracking_key) : null;

        if ($cart_row && 'active' === $cart_row['status'] && empty($cart_row['abandoned_at'])) {
            self::delete_cart_rows(array((int) $cart_row['id']));
        }

        self::clear_session_tracking();
    }

    public static function handle_public_requests()
    {
        if (is_admin()) {
            return;
        }

        $open_token = isset($_GET['onepaqucpro_cr_open']) ? sanitize_text_field(wp_unslash($_GET['onepaqucpro_cr_open'])) : '';
        if ($open_token) {
            self::handle_email_open($open_token);
        }

        $restore_token = isset($_GET['onepaqucpro_cr_restore']) ? sanitize_text_field(wp_unslash($_GET['onepaqucpro_cr_restore'])) : '';
        if ($restore_token) {
            self::handle_restore_request($restore_token);
        }

        $unsubscribe_token = isset($_GET['onepaqucpro_cr_unsubscribe']) ? sanitize_text_field(wp_unslash($_GET['onepaqucpro_cr_unsubscribe'])) : '';
        if ($unsubscribe_token) {
            self::handle_unsubscribe_request($unsubscribe_token);
        }
    }

    public static function process_queue()
    {
        self::maybe_install();

        $settings = self::get_settings();
        self::cleanup_expired_rows($settings);

        if (empty($settings['enabled'])) {
            return;
        }

        global $wpdb;

        $rows = $wpdb->get_results(
            "SELECT * FROM " . self::get_carts_table() . " WHERE status <> 'recovered' ORDER BY updated_at ASC",
            ARRAY_A
        );

        if (empty($rows)) {
            return;
        }

        $now       = current_time('timestamp');
        $templates = self::get_templates();

        foreach ($rows as $row) {
            if (empty($row['cart_key'])) {
                continue;
            }

            $metadata    = self::decode_json(isset($row['metadata']) ? $row['metadata'] : '', array());
            $admin_state = isset($metadata['admin_state']) ? $metadata['admin_state'] : '';

            if (in_array($admin_state, array('archived', 'ignored'), true)) {
                continue;
            }

            if ('active' === $row['status']) {
                if (self::should_skip_cart($row, $settings, $metadata)) {
                    continue;
                }

                $updated_at = self::to_timestamp($row['updated_at']);
                if ($updated_at && ($updated_at + ((int) $settings['inactivity_timeout'] * MINUTE_IN_SECONDS)) <= $now) {
                    $row = self::mark_cart_abandoned($row);
                }
            }

            if ('abandoned' !== $row['status'] || ! empty($row['unsubscribed'])) {
                continue;
            }

            if (self::should_skip_cart($row, $settings, $metadata)) {
                continue;
            }

            if (! self::can_use_premium_features()) {
                continue;
            }

            if (empty($row['email']) || ! is_email($row['email'])) {
                continue;
            }

            if (! self::is_within_sending_window($settings, $now)) {
                continue;
            }

            if ((int) self::count_sent_emails((int) $row['id']) >= (int) $settings['max_emails_per_cart']) {
                continue;
            }

            if (! empty($settings['stop_after_restore']) && ! empty($row['restored_at'])) {
                continue;
            }

            foreach ($templates as $template) {
                if (empty($template['enabled'])) {
                    continue;
                }

                if (self::has_email_been_sent($row['id'], $template['id'])) {
                    continue;
                }

                $send_at = self::to_timestamp($row['abandoned_at']) + self::delay_to_seconds($template['delay_value'], $template['delay_unit']);
                if ($send_at > $now) {
                    continue;
                }

                if ((int) self::count_sent_emails((int) $row['id']) >= (int) $settings['max_emails_per_cart']) {
                    break;
                }

                self::send_recovery_email($row, $template);
            }
        }
    }

    public static function get_admin_carts()
    {
        self::process_queue();

        global $wpdb;

        $carts = $wpdb->get_results(
            "SELECT * FROM " . self::get_carts_table() . " ORDER BY COALESCE(recovered_at, abandoned_at, updated_at) DESC",
            ARRAY_A
        );

        if (empty($carts)) {
            return array();
        }

        $cart_ids  = array_map('intval', wp_list_pluck($carts, 'id'));
        $events_map = self::get_events_for_cart_ids($cart_ids);
        $emails_map = self::get_emails_for_cart_ids($cart_ids);
        $formatted  = array();

        foreach ($carts as $cart) {
            $items         = self::decode_json(isset($cart['cart_snapshot']) ? $cart['cart_snapshot'] : '', array());
            $formatted_items = self::format_items_for_admin($items);
            $events        = isset($events_map[(int) $cart['id']]) ? $events_map[(int) $cart['id']] : array();
            $email_history = isset($emails_map[(int) $cart['id']]) ? $emails_map[(int) $cart['id']] : array();

            $formatted[] = array(
                'id'              => $cart['cart_key'],
                'customer_name'   => $cart['customer_name'] ? $cart['customer_name'] : __('Guest Customer', 'one-page-quick-checkout-for-woocommerce-pro'),
                'customer_id'     => ! empty($cart['customer_id']) ? (string) $cart['customer_id'] : '',
                'email'           => $cart['email'],
                'cart_total'      => (float) $cart['cart_total'],
                'currency'        => $cart['currency'] ? $cart['currency'] : get_woocommerce_currency(),
                'status'          => $cart['status'],
                'unsubscribed'    => (int) $cart['unsubscribed'],
                'created_at'      => $cart['created_at'],
                'updated_at'      => $cart['updated_at'],
                'abandoned_at'    => $cart['abandoned_at'],
                'restored_at'     => $cart['restored_at'],
                'recovered_at'    => $cart['recovered_at'],
                'recovered_order_id' => (int) $cart['recovered_order_id'],
                'session_id'      => $cart['session_id'],
                'product_context' => ! empty($formatted_items) ? implode(', ', array_filter(wp_list_pluck($formatted_items, 'name'))) : $cart['product_context'],
                'ip_address'      => $cart['ip_address'],
                'user_agent'      => $cart['user_agent'],
                'items'           => $formatted_items,
                'journey'         => $events,
                'email_history'   => $email_history,
                'checkout_data'   => self::decode_json(isset($cart['checkout_data']) ? $cart['checkout_data'] : '', array()),
                'metadata'        => self::decode_json(isset($cart['metadata']) ? $cart['metadata'] : '', array()),
            );
        }

        return $formatted;
    }

    public static function cart_exists($cart_key)
    {
        return (bool) self::get_cart_row_by_key($cart_key);
    }

    public static function update_cart_status($cart_key, $status)
    {
        $cart = self::get_cart_row_by_key($cart_key);
        if (! $cart || ! in_array($status, array('abandoned', 'recovered'), true)) {
            return false;
        }

        global $wpdb;

        $now    = current_time('mysql');
        $update = array(
            'status'     => $status,
            'updated_at' => $now,
        );

        if ('recovered' === $status) {
            $update['recovered_at'] = $cart['recovered_at'] ? $cart['recovered_at'] : $now;
            $update['abandoned_at'] = $cart['abandoned_at'] ? $cart['abandoned_at'] : $now;
            self::merge_cart_metadata((int) $cart['id'], array(
                'recovery_source' => 'admin',
            ));
        } else {
            $update['recovered_at'] = null;
            $update['abandoned_at'] = $cart['abandoned_at'] ? $cart['abandoned_at'] : $now;
            $update['recovered_order_id'] = 0;
        }

        $formats = array();
        foreach ($update as $key => $value) {
            $formats[] = 'recovered_order_id' === $key ? '%d' : '%s';
        }

        $updated = $wpdb->update(self::get_carts_table(), $update, array('id' => (int) $cart['id']), $formats, array('%d'));

        if (false === $updated) {
            return false;
        }

        self::insert_event(
            (int) $cart['id'],
            'recovered' === $status ? 'cart_recovered' : 'cart_abandoned',
            'recovered' === $status ? __('Cart Recovered', 'one-page-quick-checkout-for-woocommerce-pro') : __('Cart Abandoned', 'one-page-quick-checkout-for-woocommerce-pro'),
            $now,
            array(
                __('Source', 'one-page-quick-checkout-for-woocommerce-pro') => __('Updated from admin', 'one-page-quick-checkout-for-woocommerce-pro'),
                __('Status', 'one-page-quick-checkout-for-woocommerce-pro') => ucfirst($status),
            )
        );

        return true;
    }

    public static function perform_cart_action($cart_key, $action)
    {
        $cart = self::get_cart_row_by_key($cart_key);
        if (! $cart) {
            return false;
        }

        if (! self::can_use_premium_features()) {
            return false;
        }

        switch ($action) {
            case 'mark_abandoned':
                return self::update_cart_status($cart_key, 'abandoned');
            case 'mark_recovered':
                return self::update_cart_status($cart_key, 'recovered');
            case 'archive':
                return self::update_cart_admin_state($cart, 'archived', __('Cart Archived', 'one-page-quick-checkout-for-woocommerce-pro'));
            case 'ignore':
                return self::update_cart_admin_state($cart, 'ignored', __('Cart Ignored', 'one-page-quick-checkout-for-woocommerce-pro'));
            case 'activate':
                return self::update_cart_admin_state($cart, '', __('Cart Reactivated', 'one-page-quick-checkout-for-woocommerce-pro'));
            case 'delete':
                self::delete_cart_rows(array((int) $cart['id']));
                return true;
            case 'resend_last':
                return self::send_last_template_now($cart);
            case 'send_next':
                return self::send_next_template_now($cart);
        }

        return false;
    }

    public static function perform_email_activity_action($email_id, $action)
    {
        if (! self::can_use_premium_features()) {
            return false;
        }

        $email_id = absint($email_id);
        $action   = sanitize_key($action);
        $email = self::get_email_row_by_id($email_id);
        if (! $email || ! in_array($action, array('resend', 'retry_failed'), true)) {
            return false;
        }

        if ('retry_failed' === $action && 'failed' !== $email['status']) {
            return false;
        }

        $cart = self::get_cart_row_by_id((int) $email['cart_id']);
        if (! $cart) {
            return false;
        }

        foreach (self::get_templates() as $template) {
            if ($template['id'] === $email['template_id']) {
                return (bool) self::send_recovery_email($cart, $template);
            }
        }

        return false;
    }

    public static function save_cart_meta($cart_key, $notes, $tags)
    {
        if (! self::can_use_premium_features()) {
            return false;
        }

        $cart = self::get_cart_row_by_key($cart_key);
        if (! $cart) {
            return false;
        }

        return self::merge_cart_metadata(
            (int) $cart['id'],
            array(
                'notes' => $notes,
                'tags'  => $tags,
            )
        );
    }

    public static function send_test_email($template_id, $recipient)
    {
        if (! self::can_use_premium_features()) {
            return false;
        }

        if (! is_email($recipient)) {
            return false;
        }

        $cart = self::get_latest_cart_row();
        if (! $cart) {
            return false;
        }

        foreach (self::get_templates() as $template) {
            if ($template['id'] === $template_id) {
                $template['send_to']          = 'custom';
                $template['custom_recipient'] = $recipient;

                return (bool) self::send_recovery_email($cart, $template);
            }
        }

        return false;
    }

    public static function run_manual_cleanup()
    {
        self::cleanup_expired_rows(self::get_settings());
    }

    public static function anonymize_expired_carts()
    {
        global $wpdb;

        $settings       = self::get_settings();
        $retention_days = max(1, absint(isset($settings['retention_days']) ? $settings['retention_days'] : 30));
        $cutoff         = wp_date('Y-m-d H:i:s', current_time('timestamp') - ($retention_days * DAY_IN_SECONDS), wp_timezone());
        $rows           = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, metadata FROM " . self::get_carts_table() . " WHERE updated_at < %s",
                $cutoff
            ),
            ARRAY_A
        );

        foreach ($rows as $row) {
            $metadata = self::decode_json(isset($row['metadata']) ? $row['metadata'] : '', array());
            unset($metadata['customer_profile']);
            $metadata['privacy_state'] = 'anonymized';

            self::update_cart_row(
                (int) $row['id'],
                array(
                    'customer_name' => __('Anonymized Customer', 'one-page-quick-checkout-for-woocommerce-pro'),
                    'email'         => '',
                    'ip_address'    => '',
                    'user_agent'    => '',
                    'checkout_data' => wp_json_encode(array()),
                    'metadata'      => wp_json_encode($metadata),
                )
            );
        }
    }

    private static function capture_cart($context)
    {
        if (! self::should_capture_cart()) {
            return;
        }

        $settings = self::get_settings();
        if (empty($settings['enabled'])) {
            return;
        }

        $cart = WC()->cart;
        if (! $cart || $cart->is_empty()) {
            return;
        }

        $snapshot = self::build_cart_snapshot($cart);
        if (empty($snapshot['items'])) {
            return;
        }

        if ($snapshot['cart_total'] <= 0 && empty($settings['track_free_carts'])) {
            return;
        }

        $checkout_data = isset($context['checkout_data']) && is_array($context['checkout_data']) ? $context['checkout_data'] : array();
        $identity      = self::build_customer_identity($checkout_data);

        if (empty($identity['email']) && empty($identity['customer_id']) && empty($identity['phone'])) {
            return;
        }

        $session_id = self::get_session_identifier();
        if (! $session_id) {
            return;
        }

        $cart_hash = md5(wp_json_encode(array(
            'items'      => $snapshot['items'],
            'total'      => $snapshot['cart_total'],
            'currency'   => $snapshot['currency'],
            'customer'   => $identity,
            'session_id' => $session_id,
        )));

        $cart_key = self::resolve_cart_key($session_id);
        if (! $cart_key) {
            return;
        }

        $existing          = self::get_cart_row_by_key($cart_key);
        $now               = current_time('mysql');
        $existing_metadata = $existing ? self::decode_json(isset($existing['metadata']) ? $existing['metadata'] : '', array()) : array();
        $metadata          = self::build_metadata($snapshot, $checkout_data, $identity, $existing_metadata);

        $data = array(
            'session_id'      => $session_id,
            'customer_id'     => (int) $identity['customer_id'],
            'customer_name'   => $identity['customer_name'],
            'email'           => $identity['email'],
            'cart_total'      => $snapshot['cart_total'],
            'currency'        => $snapshot['currency'],
            'item_count'      => count($snapshot['items']),
            'product_context' => $snapshot['product_context'],
            'ip_address'      => self::get_client_ip(),
            'user_agent'      => self::get_user_agent(),
            'cart_hash'       => $cart_hash,
            'cart_snapshot'   => wp_json_encode($snapshot['items']),
            'checkout_data'   => wp_json_encode($checkout_data),
            'metadata'        => wp_json_encode($metadata),
            'updated_at'      => $now,
        );

        if (! $existing) {
            $data['cart_key']           = $cart_key;
            $data['recovery_token']     = self::generate_token();
            $data['status']             = 'active';
            $data['created_at']         = $now;
            $data['abandoned_at']       = null;
            $data['restored_at']        = null;
            $data['recovered_at']       = null;
            $data['recovered_order_id'] = 0;
            self::insert_cart_row($data);

            $created = self::get_cart_row_by_key($cart_key);
            if ($created) {
                self::insert_event(
                    (int) $created['id'],
                    'cart_created',
                    __('Cart Created', 'one-page-quick-checkout-for-woocommerce-pro'),
                    $now,
                    self::build_cart_event_meta($snapshot, $identity, $data['ip_address'], $data['user_agent'])
                );
            }

            return;
        }

        $should_update  = $existing['cart_hash'] !== $cart_hash;
        $was_abandoned  = 'abandoned' === $existing['status'] && empty($existing['recovered_at']);

        if ($was_abandoned) {
            $data['status']       = 'active';
            $data['abandoned_at'] = null;
            $data['restored_at']  = $now;
        }

        if ($was_abandoned && empty($existing['restored_at'])) {
            $recovery_source = WC()->session->get(self::SESSION_RESTORE_TOKEN) ? 'email' : 'site_revisit';

            self::insert_event(
                (int) $existing['id'],
                'cart_restored',
                __('Cart Restored', 'one-page-quick-checkout-for-woocommerce-pro'),
                $now,
                array(
                    __('Recovery Source', 'one-page-quick-checkout-for-woocommerce-pro') => 'email' === $recovery_source ? __('Recovery email link', 'one-page-quick-checkout-for-woocommerce-pro') : __('Site revisit', 'one-page-quick-checkout-for-woocommerce-pro'),
                    __('Cart Total', 'one-page-quick-checkout-for-woocommerce-pro')      => wc_price($snapshot['cart_total'], array('currency' => $snapshot['currency'])),
                )
            );

            self::merge_cart_metadata((int) $existing['id'], array(
                'recovery_source' => $recovery_source,
            ));
        }

        self::update_cart_row((int) $existing['id'], $data);

        if ($should_update) {
            self::insert_event(
                (int) $existing['id'],
                'cart_updated',
                __('Cart Updated', 'one-page-quick-checkout-for-woocommerce-pro'),
                $now,
                self::build_cart_update_meta($snapshot, $identity, $data['ip_address'], $data['user_agent'])
            );
        }
    }

    private static function mark_cart_recovered_from_order($order)
    {
        if (! $order instanceof WC_Order) {
            return;
        }

        $tracking_key = self::has_session() ? WC()->session->get(self::SESSION_TRACKING_KEY) : '';
        $cart         = $tracking_key ? self::get_cart_row_by_key($tracking_key) : null;

        if (! $cart && $order->get_meta('_onepaqucpro_cart_recovery_key')) {
            $cart = self::get_cart_row_by_key($order->get_meta('_onepaqucpro_cart_recovery_key'));
        }

        if (! $cart) {
            return;
        }

        if (empty($cart['abandoned_at']) && empty($cart['restored_at']) && ! self::cart_has_email_activity((int) $cart['id'])) {
            self::delete_cart_rows(array((int) $cart['id']));
            self::clear_session_tracking();
            return;
        }

        global $wpdb;

        $now = $order->get_date_created() ? $order->get_date_created()->date('Y-m-d H:i:s') : current_time('mysql');
        $order_identity = self::build_customer_identity_from_order($order);

        $wpdb->update(
            self::get_carts_table(),
            array(
                'status'             => 'recovered',
                'customer_id'        => (int) $order->get_customer_id(),
                'customer_name'      => $order_identity['customer_name'],
                'email'              => $order->get_billing_email(),
                'recovered_at'       => $now,
                'recovered_order_id' => (int) $order->get_id(),
                'updated_at'         => $now,
            ),
            array('id' => (int) $cart['id']),
            array('%s', '%d', '%s', '%s', '%s', '%d', '%s'),
            array('%d')
        );

        $existing_metadata = self::decode_json(isset($cart['metadata']) ? $cart['metadata'] : '', array());
        self::merge_cart_metadata((int) $cart['id'], array(
            'customer_profile' => self::build_customer_profile($order_identity, $existing_metadata),
        ));

        self::insert_event(
            (int) $cart['id'],
            'cart_recovered',
            __('Cart Recovered', 'one-page-quick-checkout-for-woocommerce-pro'),
            $now,
            array(
                __('Order', 'one-page-quick-checkout-for-woocommerce-pro')             => '#' . $order->get_order_number(),
                __('Recovered Revenue', 'one-page-quick-checkout-for-woocommerce-pro') => wc_price($order->get_total(), array('currency' => $order->get_currency())),
                __('Phone', 'one-page-quick-checkout-for-woocommerce-pro')             => $order_identity['phone'],
                __('Status', 'one-page-quick-checkout-for-woocommerce-pro')            => __('Recovered', 'one-page-quick-checkout-for-woocommerce-pro'),
            )
        );

        $metadata = self::decode_json(isset($cart['metadata']) ? $cart['metadata'] : '', array());
        if (! empty($metadata['last_clicked_email_id'])) {
            $wpdb->update(
                self::get_emails_table(),
                array(
                    'status' => 'recovered',
                ),
                array('id' => (int) $metadata['last_clicked_email_id']),
                array('%s'),
                array('%d')
            );
        }

        $order->update_meta_data('_onepaqucpro_cart_recovery_key', $cart['cart_key']);
        $order->save_meta_data();

        self::clear_session_tracking();
    }

    private static function mark_cart_abandoned($row)
    {
        if (! is_array($row) || empty($row['id']) || 'recovered' === $row['status']) {
            return $row;
        }

        global $wpdb;

        $now = current_time('mysql');

        $wpdb->update(
            self::get_carts_table(),
            array(
                'status'       => 'abandoned',
                'abandoned_at' => $row['abandoned_at'] ? $row['abandoned_at'] : $now,
                'updated_at'   => $row['updated_at'],
            ),
            array('id' => (int) $row['id']),
            array('%s', '%s', '%s'),
            array('%d')
        );

        if (empty($row['abandoned_at'])) {
            self::insert_event(
                (int) $row['id'],
                'cart_abandoned',
                __('Cart Abandoned', 'one-page-quick-checkout-for-woocommerce-pro'),
                $now,
                array(
                    __('Cart Total', 'one-page-quick-checkout-for-woocommerce-pro') => wc_price((float) $row['cart_total'], array('currency' => $row['currency'] ? $row['currency'] : get_woocommerce_currency())),
                    __('IP Address', 'one-page-quick-checkout-for-woocommerce-pro') => $row['ip_address'],
                    __('User Agent', 'one-page-quick-checkout-for-woocommerce-pro') => $row['user_agent'],
                )
            );
            $row['abandoned_at'] = $now;
        }

        $row['status'] = 'abandoned';

        return $row;
    }

    private static function send_recovery_email($cart, $template)
    {
        if (! self::can_use_premium_features()) {
            return false;
        }

        $recipient = 'custom' === $template['send_to'] ? $template['custom_recipient'] : $cart['email'];
        if (! $recipient || ! is_email($recipient)) {
            return false;
        }

        $settings = self::get_settings();

        $open_token  = self::generate_token();
        $click_token = self::generate_token();

        $merge_tags = self::build_merge_tags($cart, $template, $open_token, $click_token);
        $subject    = wp_strip_all_tags(self::replace_merge_tags($template['subject'], $merge_tags));
        $heading    = self::replace_merge_tags($template['heading'] ? $template['heading'] : __('We saved your cart', 'one-page-quick-checkout-for-woocommerce-pro'), $merge_tags);
        $message    = $template['message'] ? $template['message'] : self::get_default_message_template();
        $body       = self::replace_merge_tags($message, $merge_tags);
        $body       = wp_kses_post($body);

        if (false === stripos($body, '<p') && false === stripos($body, '<ul') && false === stripos($body, '<ol') && false === stripos($body, '<div') && false === stripos($body, '<table')) {
            $body = wpautop($body);
        }

        if (! empty($settings['tracking_pixel_enabled'])) {
            $body .= '<img src="' . esc_url(self::get_open_url($open_token)) . '" alt="" width="1" height="1" style="display:block;border:0;width:1px;height:1px;" />';
        }

        if (class_exists('WC_Emails') && WC()->mailer()) {
            $mailer = WC()->mailer();
            $body   = $mailer->wrap_message($heading, $body);
            if (method_exists($mailer, 'style_inline')) {
                $body = $mailer->style_inline($body);
            }
        }

        $headers = array('Content-Type: text/html; charset=UTF-8');
        $sender_email = self::get_template_sender_email($template, $settings);
        $sender_name  = ! empty($settings['sender_name']) ? sanitize_text_field($settings['sender_name']) : wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
        $reply_to     = self::get_template_reply_to($template, $settings);

        if ($sender_email) {
            $headers[] = 'From: ' . $sender_name . ' <' . $sender_email . '>';
        }

        if ($reply_to) {
            $headers[] = 'Reply-To: ' . $reply_to;
        }

        $sent_at = current_time('mysql');
        $result  = wp_mail($recipient, $subject, $body, $headers);

        if (! $result) {
            self::insert_email_log(
                array(
                    'cart_id'        => (int) $cart['id'],
                    'template_id'    => $template['id'],
                    'template_name'  => $template['name'],
                    'recipient'      => $recipient,
                    'subject'        => $subject,
                    'status'         => 'failed',
                    'open_token'     => $open_token,
                    'click_token'    => $click_token,
                    'discount_code'  => $template['discount_code'],
                    'sent_at'        => $sent_at,
                    'opened_at'      => null,
                    'clicked_at'     => null,
                    'payload'        => wp_json_encode(array(
                        'heading'      => $heading,
                        'cart_link'    => $merge_tags['{cart_link}'],
                        'template_id'  => $template['id'],
                        'body'         => $body,
                        'sender_email' => $sender_email,
                        'reply_to'     => $reply_to,
                    )),
                    'delivery_error' => __('wp_mail returned false.', 'one-page-quick-checkout-for-woocommerce-pro'),
                )
            );

            self::insert_event(
                (int) $cart['id'],
                'email_failed',
                __('Recovery Email Failed', 'one-page-quick-checkout-for-woocommerce-pro'),
                $sent_at,
                array(
                    __('Email', 'one-page-quick-checkout-for-woocommerce-pro')     => $template['name'],
                    __('Recipient', 'one-page-quick-checkout-for-woocommerce-pro') => $recipient,
                )
            );

            return false;
        }

        $email_id = self::insert_email_log(
            array(
                'cart_id'        => (int) $cart['id'],
                'template_id'    => $template['id'],
                'template_name'  => $template['name'],
                'recipient'      => $recipient,
                'subject'        => $subject,
                'status'         => 'sent',
                'open_token'     => $open_token,
                'click_token'    => $click_token,
                'discount_code'  => $template['discount_code'],
                'sent_at'        => $sent_at,
                'opened_at'      => null,
                'clicked_at'     => null,
                'payload'        => wp_json_encode(array(
                    'heading'      => $heading,
                    'cart_link'    => $merge_tags['{cart_link}'],
                    'template_id'  => $template['id'],
                    'body'         => $body,
                    'sender_email' => $sender_email,
                    'reply_to'     => $reply_to,
                )),
                'delivery_error' => '',
            )
        );

        self::insert_event(
            (int) $cart['id'],
            'email_sent',
            __('Recovery Email Sent', 'one-page-quick-checkout-for-woocommerce-pro'),
            $sent_at,
            array(
                __('Email', 'one-page-quick-checkout-for-woocommerce-pro')      => $template['name'],
                __('Subject', 'one-page-quick-checkout-for-woocommerce-pro')    => $subject,
                __('Recipient', 'one-page-quick-checkout-for-woocommerce-pro')  => $recipient,
                __('Destination', 'one-page-quick-checkout-for-woocommerce-pro') => self::get_restore_url($click_token),
            )
        );

        return $email_id;
    }

    private static function handle_email_open($open_token)
    {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT emails.*, carts.id AS cart_id_ref FROM " . self::get_emails_table() . " AS emails LEFT JOIN " . self::get_carts_table() . " AS carts ON carts.id = emails.cart_id WHERE emails.open_token = %s LIMIT 1",
                $open_token
            ),
            ARRAY_A
        );

        if ($row && empty($row['opened_at'])) {
            $opened_at = current_time('mysql');
            $status    = ! empty($row['clicked_at']) ? 'clicked' : 'opened';

            $wpdb->update(
                self::get_emails_table(),
                array(
                    'opened_at' => $opened_at,
                    'status'    => $status,
                ),
                array('id' => (int) $row['id']),
                array('%s', '%s'),
                array('%d')
            );

            self::insert_event(
                (int) $row['cart_id_ref'],
                'email_opened',
                __('Recovery Email Opened', 'one-page-quick-checkout-for-woocommerce-pro'),
                $opened_at,
                array(
                    __('Email', 'one-page-quick-checkout-for-woocommerce-pro') => $row['template_name'],
                )
            );
        }

        nocache_headers();
        header('Content-Type: image/gif');
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
        exit;
    }

    private static function handle_restore_request($click_token)
    {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT emails.id AS email_id, emails.cart_id AS email_cart_id, emails.template_id AS email_template_id, emails.template_name AS email_template_name, emails.click_token AS email_click_token, emails.discount_code AS email_discount_code, emails.clicked_at AS email_clicked_at, carts.* FROM " . self::get_emails_table() . " AS emails INNER JOIN " . self::get_carts_table() . " AS carts ON carts.id = emails.cart_id WHERE emails.click_token = %s LIMIT 1",
                $click_token
            ),
            ARRAY_A
        );

        if (! $row) {
            wp_safe_redirect(wc_get_checkout_url());
            exit;
        }

        if (empty($row['email_clicked_at'])) {
            $clicked_at = current_time('mysql');

            $wpdb->update(
                self::get_emails_table(),
                array(
                    'clicked_at' => $clicked_at,
                    'status'     => 'clicked',
                ),
                array('id' => (int) $row['email_id']),
                array('%s', '%s'),
                array('%d')
            );

            self::insert_event(
                (int) $row['email_cart_id'],
                'email_clicked',
                __('Recovery Email Clicked', 'one-page-quick-checkout-for-woocommerce-pro'),
                $clicked_at,
                array(
                    __('Email', 'one-page-quick-checkout-for-woocommerce-pro')      => $row['email_template_name'],
                    __('Destination', 'one-page-quick-checkout-for-woocommerce-pro') => wc_get_checkout_url(),
                )
            );

            self::merge_cart_metadata((int) $row['id'], array(
                'recovery_source'          => 'email',
                'last_clicked_email_id'    => (int) $row['email_id'],
                'last_clicked_template_id' => sanitize_key($row['email_template_id']),
                'last_clicked_at'          => $clicked_at,
            ));
        }

        self::restore_cart_from_saved_snapshot($row, $row['email_discount_code']);
    }

    private static function handle_unsubscribe_request($recovery_token)
    {
        $cart = self::get_cart_row_by_recovery_token($recovery_token);
        if ($cart) {
            self::update_cart_row(
                (int) $cart['id'],
                array(
                    'unsubscribed' => 1,
                )
            );

            self::insert_event(
                (int) $cart['id'],
                'cart_unsubscribed',
                __('Cart Unsubscribed', 'one-page-quick-checkout-for-woocommerce-pro'),
                current_time('mysql'),
                array(
                    __('Email', 'one-page-quick-checkout-for-woocommerce-pro') => $cart['email'],
                )
            );
        }

        wp_safe_redirect(home_url('/'));
        exit;
    }

    private static function restore_cart_from_saved_snapshot($row, $discount_code = '')
    {
        if (! function_exists('WC')) {
            wp_safe_redirect(home_url('/'));
            exit;
        }

        if (null === WC()->cart && function_exists('wc_load_cart')) {
            wc_load_cart();
        }

        if (! WC()->cart || ! self::has_session()) {
            wp_safe_redirect(wc_get_checkout_url());
            exit;
        }

        $items  = self::decode_json(isset($row['cart_snapshot']) ? $row['cart_snapshot'] : '', array());
        $loaded = 0;

        WC()->cart->empty_cart();

        WC()->session->set(self::SESSION_TRACKING_KEY, $row['cart_key']);
        WC()->session->set(self::SESSION_RESTORE_TOKEN, $row['email_click_token']);

        foreach ($items as $item) {
            $product_id     = isset($item['product_id']) ? absint($item['product_id']) : 0;
            $variation_id   = isset($item['variation_id']) ? absint($item['variation_id']) : 0;
            $quantity       = isset($item['quantity']) ? max(1, absint($item['quantity'])) : 1;
            $variation      = isset($item['variation']) && is_array($item['variation']) ? $item['variation'] : array();
            $cart_item_data = isset($item['cart_item_data']) && is_array($item['cart_item_data']) ? $item['cart_item_data'] : array();

            if (! $product_id) {
                continue;
            }

            $added = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation, $cart_item_data);
            if ($added) {
                $loaded++;
            }
        }

        if ($discount_code && function_exists('wc_format_coupon_code')) {
            $coupon_code = wc_format_coupon_code($discount_code);
            if ($coupon_code) {
                WC()->cart->apply_coupon($coupon_code);
            }
        }

        WC()->cart->calculate_totals();

        if ($loaded > 0) {
            wc_add_notice(__('Your saved cart has been restored.', 'one-page-quick-checkout-for-woocommerce-pro'), 'success');
        } else {
            wc_add_notice(__('We could not restore every item from your saved cart.', 'one-page-quick-checkout-for-woocommerce-pro'), 'notice');
        }

        wp_safe_redirect(wc_get_checkout_url());
        exit;
    }

    private static function get_carts_table()
    {
        global $wpdb;

        return $wpdb->prefix . 'onepaqucpro_cr_carts';
    }

    private static function get_emails_table()
    {
        global $wpdb;

        return $wpdb->prefix . 'onepaqucpro_cr_emails';
    }

    private static function get_events_table()
    {
        global $wpdb;

        return $wpdb->prefix . 'onepaqucpro_cr_events';
    }

    private static function should_capture_cart()
    {
        if (! function_exists('WC') || ! WC()->cart || ! self::has_session()) {
            return false;
        }

        if (defined('DOING_CRON') && DOING_CRON) {
            return false;
        }

        if (is_admin() && ! wp_doing_ajax() && ! (defined('REST_REQUEST') && REST_REQUEST)) {
            return false;
        }

        return true;
    }

    private static function has_session()
    {
        return function_exists('WC') && WC()->session;
    }

    private static function resolve_cart_key($session_id)
    {
        if (! self::has_session()) {
            return '';
        }

        $tracking_key = sanitize_key((string) WC()->session->get(self::SESSION_TRACKING_KEY));
        if ($tracking_key) {
            return $tracking_key;
        }

        $active_cart = self::get_latest_active_cart_by_session_id($session_id);
        if ($active_cart && ! empty($active_cart['cart_key'])) {
            WC()->session->set(self::SESSION_TRACKING_KEY, $active_cart['cart_key']);
            return $active_cart['cart_key'];
        }

        $cart_key = 'cr_' . substr(str_replace('-', '', wp_generate_uuid4()), 0, 28);
        WC()->session->set(self::SESSION_TRACKING_KEY, $cart_key);

        return $cart_key;
    }

    private static function clear_session_tracking()
    {
        if (! self::has_session()) {
            return;
        }

        WC()->session->set(self::SESSION_TRACKING_KEY, null);
        WC()->session->set(self::SESSION_RESTORE_TOKEN, null);
    }

    private static function get_session_identifier()
    {
        if (! self::has_session()) {
            return '';
        }

        if (method_exists(WC()->session, 'get_customer_unique_id')) {
            return (string) WC()->session->get_customer_unique_id();
        }

        if (method_exists(WC()->session, 'get_customer_id')) {
            return (string) WC()->session->get_customer_id();
        }

        return '';
    }

    private static function build_cart_snapshot($cart)
    {
        $items = array();
        foreach ($cart->get_cart() as $cart_item) {
            if (empty($cart_item['data']) || ! is_object($cart_item['data'])) {
                continue;
            }

            $product       = $cart_item['data'];
            $product_id    = isset($cart_item['product_id']) ? absint($cart_item['product_id']) : 0;
            $variation_id  = isset($cart_item['variation_id']) ? absint($cart_item['variation_id']) : 0;
            $quantity      = isset($cart_item['quantity']) ? max(1, absint($cart_item['quantity'])) : 1;
            $line_total    = isset($cart_item['line_total']) ? (float) $cart_item['line_total'] : (float) $product->get_price() * $quantity;
            $line_subtotal = isset($cart_item['line_subtotal']) ? (float) $cart_item['line_subtotal'] : $line_total;
            $variation     = isset($cart_item['variation']) && is_array($cart_item['variation']) ? array_map('wc_clean', $cart_item['variation']) : array();
            $custom_data   = $cart_item;
            $parent_product = $product_id ? wc_get_product($product_id) : null;
            $image_id       = method_exists($product, 'get_image_id') ? absint($product->get_image_id()) : 0;

            if (! $image_id && $parent_product && method_exists($parent_product, 'get_image_id')) {
                $image_id = absint($parent_product->get_image_id());
            }

            $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
            if (! $image_url && function_exists('wc_placeholder_img_src')) {
                $image_url = wc_placeholder_img_src('thumbnail');
            }

            $product_url = method_exists($product, 'get_permalink') ? $product->get_permalink() : '';
            if (! $product_url && $product_id) {
                $product_url = get_permalink($product_id);
            }

            $categories = $product_id ? wp_get_post_terms($product_id, 'product_cat', array('fields' => 'names')) : array();
            if (is_wp_error($categories)) {
                $categories = array();
            }

            unset($custom_data['data']);
            unset($custom_data['line_tax_data']);
            unset($custom_data['data_hash']);
            unset($custom_data['key']);
            unset($custom_data['product_id']);
            unset($custom_data['variation_id']);
            unset($custom_data['variation']);
            unset($custom_data['quantity']);
            unset($custom_data['line_total']);
            unset($custom_data['line_subtotal']);
            unset($custom_data['line_tax']);
            unset($custom_data['line_subtotal_tax']);

            $items[] = array(
                'product_id'     => $product_id,
                'variation_id'   => $variation_id,
                'name'           => self::get_item_name($product, $variation, $product_id),
                'quantity'       => $quantity,
                'unit_price'     => $quantity > 0 ? $line_total / $quantity : $line_total,
                'subtotal'       => $line_subtotal,
                'price'          => $line_total,
                'discount'       => max(0, $line_subtotal - $line_total),
                'sku'            => method_exists($product, 'get_sku') ? $product->get_sku() : '',
                'product_url'    => $product_url ? esc_url_raw($product_url) : '',
                'image_url'      => $image_url ? esc_url_raw($image_url) : '',
                'product_type'   => method_exists($product, 'get_type') ? $product->get_type() : '',
                'stock_status'   => method_exists($product, 'get_stock_status') ? $product->get_stock_status() : '',
                'categories'     => array_values(array_filter(array_map('sanitize_text_field', $categories))),
                'variation'      => $variation,
                'cart_item_data' => self::sanitize_recursive($custom_data),
            );
        }

        return array(
            'items'           => $items,
            'cart_total'      => (float) $cart->get_total('edit'),
            'currency'        => get_woocommerce_currency(),
            'product_context' => implode(', ', array_map(function ($item) {
                return isset($item['name']) ? $item['name'] : '';
            }, $items)),
        );
    }

    private static function build_customer_identity($checkout_data)
    {
        $customer_id = get_current_user_id();
        $billing     = self::build_customer_address_profile($checkout_data, 'billing', $customer_id);
        $shipping    = self::build_customer_address_profile($checkout_data, 'shipping', $customer_id);
        $email       = isset($billing['email']) ? sanitize_email($billing['email']) : '';
        $first_name  = isset($billing['first_name']) ? sanitize_text_field($billing['first_name']) : '';
        $last_name   = isset($billing['last_name']) ? sanitize_text_field($billing['last_name']) : '';
        $phone       = isset($billing['phone']) ? self::sanitize_phone_number($billing['phone']) : '';
        $company     = isset($billing['company']) ? sanitize_text_field($billing['company']) : '';
        $order_notes = self::get_checkout_value($checkout_data, array('order_comments', 'order-notes', 'customer_note'));

        if (function_exists('WC') && WC()->customer) {
            if (! $email && method_exists(WC()->customer, 'get_billing_email')) {
                $email = sanitize_email(WC()->customer->get_billing_email());
            }
            if (! $first_name && method_exists(WC()->customer, 'get_billing_first_name')) {
                $first_name = sanitize_text_field(WC()->customer->get_billing_first_name());
            }
            if (! $last_name && method_exists(WC()->customer, 'get_billing_last_name')) {
                $last_name = sanitize_text_field(WC()->customer->get_billing_last_name());
            }
            if (! $phone && method_exists(WC()->customer, 'get_billing_phone')) {
                $phone = self::sanitize_phone_number(WC()->customer->get_billing_phone());
            }
            if (! $company && method_exists(WC()->customer, 'get_billing_company')) {
                $company = sanitize_text_field(WC()->customer->get_billing_company());
            }
        }

        if ($customer_id) {
            $user = get_userdata($customer_id);
            if ($user) {
                if (! $email) {
                    $email = sanitize_email($user->user_email);
                }
                if (! $first_name) {
                    $first_name = sanitize_text_field(get_user_meta($customer_id, 'first_name', true));
                }
                if (! $last_name) {
                    $last_name = sanitize_text_field(get_user_meta($customer_id, 'last_name', true));
                }
                if (! $phone) {
                    $phone = self::sanitize_phone_number(get_user_meta($customer_id, 'billing_phone', true));
                }
                if (! $company) {
                    $company = sanitize_text_field(get_user_meta($customer_id, 'billing_company', true));
                }
                if (! $first_name && ! $last_name) {
                    $first_name = sanitize_text_field($user->display_name);
                }
            }
        }

        $customer_name = trim($first_name . ' ' . $last_name);
        if (! $customer_name) {
            $customer_name = $first_name ? $first_name : ($email ? $email : __('Guest Customer', 'one-page-quick-checkout-for-woocommerce-pro'));
        }

        return array(
            'customer_id'   => $customer_id,
            'email'         => $email,
            'customer_name' => $customer_name,
            'first_name'    => $first_name ? $first_name : $customer_name,
            'last_name'     => $last_name,
            'phone'         => $phone,
            'company'       => $company,
            'billing'       => $billing,
            'shipping'      => $shipping,
            'order_notes'   => $order_notes,
        );
    }

    private static function build_customer_identity_from_order($order)
    {
        $billing  = self::build_order_address_profile($order, 'billing');
        $shipping = self::build_order_address_profile($order, 'shipping');
        $email    = $order->get_billing_email();
        $phone    = method_exists($order, 'get_billing_phone') ? $order->get_billing_phone() : '';
        $company  = method_exists($order, 'get_billing_company') ? $order->get_billing_company() : '';

        $first_name = $order->get_billing_first_name();
        $last_name  = $order->get_billing_last_name();
        $name       = trim($first_name . ' ' . $last_name);

        if (! $name) {
            $name = $email ? $email : __('Guest Customer', 'one-page-quick-checkout-for-woocommerce-pro');
        }

        return array(
            'customer_id'   => (int) $order->get_customer_id(),
            'email'         => sanitize_email($email),
            'customer_name' => sanitize_text_field($name),
            'first_name'    => sanitize_text_field($first_name ? $first_name : $name),
            'last_name'     => sanitize_text_field($last_name),
            'phone'         => self::sanitize_phone_number($phone),
            'company'       => sanitize_text_field($company),
            'billing'       => $billing,
            'shipping'      => $shipping,
            'order_notes'   => method_exists($order, 'get_customer_note') ? sanitize_textarea_field($order->get_customer_note()) : '',
        );
    }

    private static function build_order_address_profile($order, $type)
    {
        $type   = 'shipping' === $type ? 'shipping' : 'billing';
        $fields = array('first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country', 'phone', 'email');
        $profile = array();

        foreach ($fields as $field) {
            if ('shipping' === $type && 'email' === $field) {
                continue;
            }

            $method = 'get_' . $type . '_' . $field;
            if (! method_exists($order, $method)) {
                continue;
            }

            $value = $order->{$method}();
            if ('email' === $field) {
                $value = sanitize_email($value);
            } elseif ('phone' === $field) {
                $value = self::sanitize_phone_number($value);
            } else {
                $value = sanitize_text_field($value);
            }

            if ('' !== $value) {
                $profile[$field] = $value;
            }
        }

        return $profile;
    }

    private static function build_customer_address_profile($checkout_data, $type, $customer_id = 0)
    {
        $type = 'shipping' === $type ? 'shipping' : 'billing';

        if ('billing' === $type) {
            $field_aliases = array(
                'first_name' => array('billing_first_name', 'first-name', 'first_name', 'billing-first-name'),
                'last_name'  => array('billing_last_name', 'last-name', 'last_name', 'billing-last-name'),
                'company'    => array('billing_company', 'company', 'billing-company'),
                'email'      => array('billing_email', 'email', 'billing-email'),
                'phone'      => array('billing_phone', 'phone', 'billing-phone'),
                'address_1'  => array('billing_address_1', 'address', 'address_1', 'billing-address'),
                'address_2'  => array('billing_address_2', 'address2', 'address_2', 'billing-address2'),
                'city'       => array('billing_city', 'city', 'billing-city'),
                'state'      => array('billing_state', 'state', 'billing-state'),
                'postcode'   => array('billing_postcode', 'postcode', 'billing-postcode'),
                'country'    => array('billing_country', 'country', 'billing-country'),
            );
        } else {
            $field_aliases = array(
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
        }

        $profile = array();
        foreach ($field_aliases as $field => $aliases) {
            $value = self::get_checkout_value($checkout_data, $aliases);

            if (! $value && function_exists('WC') && WC()->customer) {
                $method = 'get_' . $type . '_' . $field;
                if (method_exists(WC()->customer, $method)) {
                    $value = WC()->customer->{$method}();
                }
            }

            if (! $value && $customer_id) {
                $value = get_user_meta($customer_id, $type . '_' . $field, true);
            }

            if ('email' === $field) {
                $value = sanitize_email($value);
            } elseif ('phone' === $field) {
                $value = self::sanitize_phone_number($value);
            } else {
                $value = sanitize_text_field($value);
            }

            if ('' !== $value) {
                $profile[$field] = $value;
            }
        }

        return $profile;
    }

    private static function get_checkout_value($checkout_data, $keys)
    {
        if (! is_array($checkout_data)) {
            return '';
        }

        foreach ((array) $keys as $key) {
            if (! isset($checkout_data[$key]) || ! is_scalar($checkout_data[$key])) {
                continue;
            }

            $value = trim((string) wp_unslash($checkout_data[$key]));
            if ('' !== $value) {
                return sanitize_text_field($value);
            }
        }

        return '';
    }

    private static function sanitize_phone_number($phone)
    {
        $phone = is_scalar($phone) ? trim((string) $phone) : '';
        if ('' === $phone) {
            return '';
        }

        if (function_exists('wc_sanitize_phone_number')) {
            return wc_sanitize_phone_number($phone);
        }

        return trim(preg_replace('/[^0-9+()\-\s.]/', '', sanitize_text_field($phone)));
    }

    private static function build_customer_profile($identity, $existing_metadata = array())
    {
        $existing_profile = isset($existing_metadata['customer_profile']) && is_array($existing_metadata['customer_profile'])
            ? $existing_metadata['customer_profile']
            : array();

        $billing  = isset($identity['billing']) && is_array($identity['billing']) ? $identity['billing'] : array();
        $shipping = isset($identity['shipping']) && is_array($identity['shipping']) ? $identity['shipping'] : array();
        $company  = isset($identity['company']) ? sanitize_text_field($identity['company']) : '';

        if (! $company && ! empty($billing['company'])) {
            $company = sanitize_text_field($billing['company']);
        }

        $profile = array(
            'customer_id'      => isset($identity['customer_id']) ? absint($identity['customer_id']) : 0,
            'customer_name'    => isset($identity['customer_name']) ? sanitize_text_field($identity['customer_name']) : '',
            'first_name'       => isset($identity['first_name']) ? sanitize_text_field($identity['first_name']) : '',
            'last_name'        => isset($identity['last_name']) ? sanitize_text_field($identity['last_name']) : '',
            'email'            => isset($identity['email']) ? sanitize_email($identity['email']) : '',
            'phone'            => isset($identity['phone']) ? self::sanitize_phone_number($identity['phone']) : '',
            'company'          => $company,
            'billing_address'  => $billing,
            'shipping_address' => $shipping,
            'order_notes'      => isset($identity['order_notes']) ? sanitize_textarea_field($identity['order_notes']) : '',
        );

        if (empty($profile['phone']) && ! empty($billing['phone'])) {
            $profile['phone'] = self::sanitize_phone_number($billing['phone']);
        }

        return self::merge_profile_data($existing_profile, self::sanitize_recursive($profile));
    }

    private static function merge_profile_data($existing, $incoming)
    {
        $existing = is_array($existing) ? $existing : array();
        $incoming = is_array($incoming) ? $incoming : array();
        $merged   = $existing;

        foreach ($incoming as $key => $value) {
            if (is_array($value)) {
                $merged[$key] = self::merge_profile_data(
                    isset($merged[$key]) && is_array($merged[$key]) ? $merged[$key] : array(),
                    $value
                );
                continue;
            }

            if ('customer_id' === $key && empty($value)) {
                continue;
            }

            if ('' === (string) $value) {
                continue;
            }

            $merged[$key] = $value;
        }

        return $merged;
    }

    private static function build_metadata($snapshot, $checkout_data, $identity, $existing_metadata = array())
    {
        $product_ids  = array();
        $category_ids = array();

        foreach ($snapshot['items'] as $item) {
            $product_id = isset($item['product_id']) ? absint($item['product_id']) : 0;
            if (! $product_id) {
                continue;
            }

            $product_ids[] = $product_id;
            $item_category_ids = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
            if (! is_wp_error($item_category_ids)) {
                $category_ids = array_merge($category_ids, $item_category_ids);
            }
        }

        $customer_profile = self::build_customer_profile($identity, $existing_metadata);

        return array(
            'coupon_codes'    => function_exists('WC') && WC()->cart ? array_values(WC()->cart->get_applied_coupons()) : array(),
            'checkout_data'   => self::sanitize_recursive($checkout_data),
            'customer_profile' => $customer_profile,
            'item_count'      => count($snapshot['items']),
            'product_ids'     => array_values(array_unique(array_filter(array_map('absint', $product_ids)))),
            'category_ids'    => array_values(array_unique(array_filter(array_map('absint', $category_ids)))),
            'customer_type'   => ! empty($identity['customer_id']) ? 'registered' : 'guest',
            'browser'         => self::detect_browser(self::get_user_agent()),
            'device_type'     => self::detect_device_type(self::get_user_agent()),
            'referrer_url'    => self::get_http_referrer(),
            'entry_url'       => self::get_current_request_url(),
            'admin_state'     => isset($existing_metadata['admin_state']) ? sanitize_key($existing_metadata['admin_state']) : '',
            'notes'           => isset($existing_metadata['notes']) ? sanitize_textarea_field($existing_metadata['notes']) : '',
            'tags'            => isset($existing_metadata['tags']) ? self::sanitize_text_list($existing_metadata['tags']) : array(),
            'recovery_source' => isset($existing_metadata['recovery_source']) ? sanitize_key($existing_metadata['recovery_source']) : '',
            'privacy_state'   => isset($existing_metadata['privacy_state']) ? sanitize_key($existing_metadata['privacy_state']) : '',
            'last_clicked_email_id' => isset($existing_metadata['last_clicked_email_id']) ? absint($existing_metadata['last_clicked_email_id']) : 0,
            'last_clicked_template_id' => isset($existing_metadata['last_clicked_template_id']) ? sanitize_key($existing_metadata['last_clicked_template_id']) : '',
            'last_clicked_at' => isset($existing_metadata['last_clicked_at']) ? sanitize_text_field($existing_metadata['last_clicked_at']) : '',
        );
    }

    private static function build_cart_event_meta($snapshot, $identity, $ip_address, $user_agent)
    {
        return array(
            __('Cart Total', 'one-page-quick-checkout-for-woocommerce-pro')    => wc_price($snapshot['cart_total'], array('currency' => $snapshot['currency'])),
            __('Item Count', 'one-page-quick-checkout-for-woocommerce-pro')    => (string) count($snapshot['items']),
            __('Email', 'one-page-quick-checkout-for-woocommerce-pro')         => $identity['email'],
            __('Phone', 'one-page-quick-checkout-for-woocommerce-pro')         => isset($identity['phone']) ? $identity['phone'] : '',
            __('Customer Name', 'one-page-quick-checkout-for-woocommerce-pro') => $identity['customer_name'],
            __('IP Address', 'one-page-quick-checkout-for-woocommerce-pro')    => $ip_address,
            __('User Agent', 'one-page-quick-checkout-for-woocommerce-pro')    => $user_agent,
        );
    }

    private static function build_cart_update_meta($snapshot, $identity, $ip_address, $user_agent)
    {
        $meta = array(
            __('Cart Total', 'one-page-quick-checkout-for-woocommerce-pro') => wc_price($snapshot['cart_total'], array('currency' => $snapshot['currency'])),
            __('Item Count', 'one-page-quick-checkout-for-woocommerce-pro') => (string) count($snapshot['items']),
            __('IP Address', 'one-page-quick-checkout-for-woocommerce-pro') => $ip_address,
            __('User Agent', 'one-page-quick-checkout-for-woocommerce-pro') => $user_agent,
        );

        if (! empty($identity['email'])) {
            $meta[__('Email', 'one-page-quick-checkout-for-woocommerce-pro')] = $identity['email'];
        }

        if (! empty($identity['customer_name'])) {
            $meta[__('Customer Name', 'one-page-quick-checkout-for-woocommerce-pro')] = $identity['customer_name'];
        }

        if (! empty($identity['phone'])) {
            $meta[__('Phone', 'one-page-quick-checkout-for-woocommerce-pro')] = $identity['phone'];
        }

        return $meta;
    }

    private static function get_latest_active_cart_by_session_id($session_id)
    {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . self::get_carts_table() . " WHERE session_id = %s AND status = 'active' ORDER BY updated_at DESC LIMIT 1",
                $session_id
            ),
            ARRAY_A
        );
    }

    private static function get_cart_row_by_key($cart_key)
    {
        global $wpdb;

        if (! $cart_key) {
            return null;
        }

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . self::get_carts_table() . " WHERE cart_key = %s LIMIT 1",
                $cart_key
            ),
            ARRAY_A
        );
    }

    private static function get_cart_row_by_recovery_token($token)
    {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . self::get_carts_table() . " WHERE recovery_token = %s LIMIT 1",
                $token
            ),
            ARRAY_A
        );
    }

    private static function insert_cart_row($data)
    {
        global $wpdb;

        return $wpdb->insert(
            self::get_carts_table(),
            $data,
            array(
                '%s', '%d', '%s', '%s', '%f', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d',
            )
        );
    }

    private static function update_cart_row($cart_id, $data)
    {
        global $wpdb;

        if (empty($data)) {
            return false;
        }

        $formats = array();
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'customer_id':
                case 'item_count':
                case 'recovered_order_id':
                case 'unsubscribed':
                    $formats[] = '%d';
                    break;
                case 'cart_total':
                    $formats[] = '%f';
                    break;
                default:
                    $formats[] = '%s';
                    break;
            }
        }

        return $wpdb->update(
            self::get_carts_table(),
            $data,
            array('id' => (int) $cart_id),
            $formats,
            array('%d')
        );
    }

    private static function insert_event($cart_id, $event_type, $event_title, $event_time, $payload)
    {
        global $wpdb;

        $inserted = $wpdb->insert(
            self::get_events_table(),
            array(
                'cart_id'     => (int) $cart_id,
                'event_type'  => $event_type,
                'event_title' => $event_title,
                'event_time'  => $event_time,
                'payload'     => wp_json_encode(self::sanitize_recursive($payload)),
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );

        if ($inserted) {
            self::dispatch_webhook($event_type, array(
                'cart_id'    => (int) $cart_id,
                'event_type' => $event_type,
                'title'      => $event_title,
                'event_time' => $event_time,
                'payload'    => $payload,
            ));
        }

        return $inserted;
    }

    private static function insert_email_log($data)
    {
        global $wpdb;

        $wpdb->insert(
            self::get_emails_table(),
            $data,
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        return (int) $wpdb->insert_id;
    }

    private static function has_email_been_sent($cart_id, $template_id)
    {
        global $wpdb;

        return (bool) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM " . self::get_emails_table() . " WHERE cart_id = %d AND template_id = %s LIMIT 1",
                (int) $cart_id,
                $template_id
            )
        );
    }

    private static function cart_has_email_activity($cart_id)
    {
        global $wpdb;

        return (bool) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM " . self::get_emails_table() . " WHERE cart_id = %d LIMIT 1",
                (int) $cart_id
            )
        );
    }

    private static function get_events_for_cart_ids($cart_ids)
    {
        global $wpdb;

        if (empty($cart_ids)) {
            return array();
        }

        $placeholders = implode(',', array_fill(0, count($cart_ids), '%d'));
        $query        = $wpdb->prepare(
            "SELECT * FROM " . self::get_events_table() . " WHERE cart_id IN ({$placeholders}) ORDER BY event_time DESC",
            $cart_ids
        );
        $rows         = $wpdb->get_results($query, ARRAY_A);
        $grouped      = array();

        foreach ($rows as $row) {
            $payload = self::decode_json(isset($row['payload']) ? $row['payload'] : '', array());
            $meta    = array();

            if (is_array($payload)) {
                foreach ($payload as $label => $value) {
                    $meta[(string) $label] = is_scalar($value) ? (string) $value : wp_json_encode($value);
                }
            }

            $grouped[(int) $row['cart_id']][] = array(
                'type'  => $row['event_type'],
                'title' => $row['event_title'],
                'time'  => $row['event_time'],
                'meta'  => $meta,
            );
        }

        return $grouped;
    }

    private static function get_emails_for_cart_ids($cart_ids)
    {
        global $wpdb;

        if (empty($cart_ids)) {
            return array();
        }

        $placeholders = implode(',', array_fill(0, count($cart_ids), '%d'));
        $query        = $wpdb->prepare(
            "SELECT * FROM " . self::get_emails_table() . " WHERE cart_id IN ({$placeholders}) ORDER BY sent_at DESC",
            $cart_ids
        );
        $rows         = $wpdb->get_results($query, ARRAY_A);
        $grouped      = array();

        foreach ($rows as $row) {
            $open_count  = ! empty($row['opened_at']) ? 1 : 0;
            $click_count = ! empty($row['clicked_at']) ? 1 : 0;
            $engagement  = '-';

            if ($open_count && $click_count) {
                $engagement = sprintf(__('1 open, 1 click', 'one-page-quick-checkout-for-woocommerce-pro'));
            } elseif ($click_count) {
                $engagement = sprintf(__('1 click', 'one-page-quick-checkout-for-woocommerce-pro'));
            } elseif ($open_count) {
                $engagement = sprintf(__('1 open', 'one-page-quick-checkout-for-woocommerce-pro'));
            }

            $grouped[(int) $row['cart_id']][] = array(
                'id'            => 'email_' . (int) $row['id'],
                'log_id'        => (int) $row['id'],
                'template_id'   => $row['template_id'],
                'name'          => $row['template_name'],
                'subject'       => $row['subject'],
                'recipient'     => $row['recipient'],
                'discount_code' => $row['discount_code'],
                'sent_at'       => $row['sent_at'],
                'status'        => $row['status'],
                'engagement'    => $engagement,
                'opened_at'     => $row['opened_at'],
                'clicked_at'    => $row['clicked_at'],
                'payload'       => self::decode_json(isset($row['payload']) ? $row['payload'] : '', array()),
                'delivery_error' => $row['delivery_error'],
            );
        }

        return $grouped;
    }

    private static function format_items_for_admin($items)
    {
        $formatted = array();

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $product_id   = isset($item['product_id']) ? absint($item['product_id']) : 0;
            $variation_id = isset($item['variation_id']) ? absint($item['variation_id']) : 0;
            $product      = function_exists('wc_get_product') ? wc_get_product($variation_id ? $variation_id : $product_id) : null;
            $parent_product = $product_id && function_exists('wc_get_product') ? wc_get_product($product_id) : null;
            $image_url    = isset($item['image_url']) ? esc_url_raw($item['image_url']) : '';
            $product_url  = isset($item['product_url']) ? esc_url_raw($item['product_url']) : '';
            $sku          = isset($item['sku']) ? sanitize_text_field($item['sku']) : '';
            $categories   = isset($item['categories']) && is_array($item['categories']) ? array_values(array_filter(array_map('sanitize_text_field', $item['categories']))) : array();
            $display_name = isset($item['name']) ? sanitize_text_field($item['name']) : '';

            if ($product) {
                if (! $image_url) {
                    $image_id = method_exists($product, 'get_image_id') ? absint($product->get_image_id()) : 0;
                    if (! $image_id && $product_id) {
                        $image_id       = $parent_product && method_exists($parent_product, 'get_image_id') ? absint($parent_product->get_image_id()) : 0;
                    }
                    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
                }

                if (! $product_url && method_exists($product, 'get_permalink')) {
                    $product_url = $product->get_permalink();
                }

                if (! $sku && method_exists($product, 'get_sku')) {
                    $sku = $product->get_sku();
                }
            }

            if ($variation_id && $product && method_exists($product, 'get_name')) {
                $display_name = sanitize_text_field($product->get_name());
            }

            if (! empty($item['variation']) && is_array($item['variation'])) {
                $display_name = self::strip_variation_suffix_from_item_name($display_name, $item['variation']);
            }

            if (! $image_url && function_exists('wc_placeholder_img_src')) {
                $image_url = wc_placeholder_img_src('thumbnail');
            }

            if (empty($categories) && $product_id) {
                $category_names = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'names'));
                if (! is_wp_error($category_names)) {
                    $categories = array_values(array_filter(array_map('sanitize_text_field', $category_names)));
                }
            }

            $price    = isset($item['price']) ? (float) $item['price'] : 0;
            $discount = isset($item['discount']) ? (float) $item['discount'] : 0;
            $subtotal = isset($item['subtotal']) ? (float) $item['subtotal'] : $price + $discount;
            $quantity = isset($item['quantity']) ? max(1, (int) $item['quantity']) : 1;

            $formatted[] = array(
                'product_id'     => $product_id,
                'variation_id'   => $variation_id,
                'name'           => $display_name,
                'quantity'       => $quantity,
                'unit_price'     => isset($item['unit_price']) ? (float) $item['unit_price'] : ($quantity > 0 ? $price / $quantity : $price),
                'subtotal'       => $subtotal,
                'price'          => $price,
                'discount'       => $discount,
                'sku'            => sanitize_text_field($sku),
                'product_url'    => $product_url ? esc_url_raw($product_url) : '',
                'image_url'      => $image_url ? esc_url_raw($image_url) : '',
                'product_type'   => isset($item['product_type']) ? sanitize_text_field($item['product_type']) : ($product && method_exists($product, 'get_type') ? $product->get_type() : ''),
                'stock_status'   => isset($item['stock_status']) ? sanitize_text_field($item['stock_status']) : ($product && method_exists($product, 'get_stock_status') ? $product->get_stock_status() : ''),
                'categories'     => $categories,
                'variation'      => isset($item['variation']) && is_array($item['variation']) ? self::sanitize_recursive($item['variation']) : array(),
                'cart_item_data' => isset($item['cart_item_data']) && is_array($item['cart_item_data']) ? self::sanitize_recursive($item['cart_item_data']) : array(),
            );
        }

        return $formatted;
    }

    private static function strip_variation_suffix_from_item_name($name, $variation)
    {
        $name = sanitize_text_field($name);
        if (! $name || empty($variation) || ! is_array($variation)) {
            return $name;
        }

        $pairs  = array();

        foreach ($variation as $key => $value) {
            if (! is_scalar($value) || '' === (string) $value) {
                continue;
            }

            $clean_value = sanitize_text_field($value);

            $label = function_exists('wc_attribute_label') ? wc_attribute_label(str_replace('attribute_', '', $key)) : str_replace(array('attribute_', 'pa_', '_', '-'), array('', '', ' ', ' '), $key);
            $pairs[] = preg_quote($label, '/') . '\s*:\s*' . preg_quote($clean_value, '/');
        }

        if (! empty($pairs)) {
            $name = preg_replace('/\s*\(\s*' . implode('\s*,\s*', $pairs) . '\s*\)\s*$/i', '', $name);
        }

        return trim($name);
    }

    private static function cleanup_expired_rows($settings)
    {
        global $wpdb;

        $retention_days = max(1, absint(isset($settings['retention_days']) ? $settings['retention_days'] : 30));
        $cutoff         = wp_date('Y-m-d H:i:s', current_time('timestamp') - ($retention_days * DAY_IN_SECONDS), wp_timezone());

        $expired_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT id FROM " . self::get_carts_table() . " WHERE (recovered_at IS NOT NULL AND recovered_at < %s) OR (recovered_at IS NULL AND updated_at < %s)",
                $cutoff,
                $cutoff
            )
        );

        if (! empty($expired_ids)) {
            self::delete_cart_rows(array_map('intval', $expired_ids));
        }
    }

    private static function delete_cart_rows($cart_ids)
    {
        global $wpdb;

        $cart_ids = array_filter(array_map('intval', $cart_ids));
        if (empty($cart_ids)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($cart_ids), '%d'));

        $wpdb->query($wpdb->prepare("DELETE FROM " . self::get_events_table() . " WHERE cart_id IN ({$placeholders})", $cart_ids));
        $wpdb->query($wpdb->prepare("DELETE FROM " . self::get_emails_table() . " WHERE cart_id IN ({$placeholders})", $cart_ids));
        $wpdb->query($wpdb->prepare("DELETE FROM " . self::get_carts_table() . " WHERE id IN ({$placeholders})", $cart_ids));
    }

    private static function get_settings()
    {
        return wp_parse_args(get_option(self::SETTINGS_OPTION, array()), array(
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
        ));
    }

    private static function get_templates()
    {
        $templates = get_option(self::TEMPLATES_OPTION, false);
        if (false === $templates) {
            $templates = self::get_default_templates();
        }

        if (! is_array($templates)) {
            return array();
        }

        $sanitized = array();
        foreach ($templates as $template) {
            if (! is_array($template)) {
                continue;
            }

            $id = sanitize_key(isset($template['id']) ? $template['id'] : '');
            if (! $id) {
                continue;
            }

            $message = isset($template['message']) ? wp_kses_post($template['message']) : '';
            if (self::is_default_template_id($id) && ('' === trim(wp_strip_all_tags($message)) || self::is_legacy_default_message($message))) {
                $message = self::get_default_message_template($id);
            }

            $sanitized[] = array(
                'id'               => $id,
                'name'             => sanitize_text_field(isset($template['name']) ? $template['name'] : ''),
                'delay_value'      => max(1, absint(isset($template['delay_value']) ? $template['delay_value'] : 60)),
                'delay_unit'       => self::sanitize_delay_unit(isset($template['delay_unit']) ? $template['delay_unit'] : 'hours'),
                'subject'          => sanitize_text_field(isset($template['subject']) ? $template['subject'] : ''),
                'discount_code'    => sanitize_text_field(isset($template['discount_code']) ? $template['discount_code'] : ''),
                'from_email'       => sanitize_email(isset($template['from_email']) ? $template['from_email'] : ''),
                'heading'          => sanitize_text_field(isset($template['heading']) ? $template['heading'] : ''),
                'send_to'          => 'custom' === (isset($template['send_to']) ? $template['send_to'] : '') ? 'custom' : 'customer',
                'custom_recipient' => sanitize_email(isset($template['custom_recipient']) ? $template['custom_recipient'] : ''),
                'cart_items_layout' => self::sanitize_cart_items_layout(isset($template['cart_items_layout']) ? $template['cart_items_layout'] : self::get_default_cart_items_layout($id)),
                'message'          => $message,
                'enabled'          => empty($template['enabled']) ? 0 : 1,
            );
        }

        usort($sanitized, function ($left, $right) {
            $left_delay  = self::delay_to_seconds($left['delay_value'], $left['delay_unit']);
            $right_delay = self::delay_to_seconds($right['delay_value'], $right['delay_unit']);

            return $left_delay <=> $right_delay;
        });

        return $sanitized;
    }

    private static function get_default_templates()
    {
        return array(
            array(
                'id'               => 'immediate_recovery',
                'name'             => __('Immediate Recovery', 'one-page-quick-checkout-for-woocommerce-pro'),
                'delay_value'      => 60,
                'delay_unit'       => 'minutes',
                'subject'          => __('You left something behind', 'one-page-quick-checkout-for-woocommerce-pro'),
                'discount_code'    => '',
                'from_email'       => '',
                'heading'          => __('We saved your cart', 'one-page-quick-checkout-for-woocommerce-pro'),
                'send_to'          => 'customer',
                'custom_recipient' => '',
                'cart_items_layout' => 'table',
                'message'          => self::get_default_message_template('immediate_recovery'),
                'enabled'          => 1,
            ),
            array(
                'id'               => 'value_reinforcement',
                'name'             => __('Value Reinforcement', 'one-page-quick-checkout-for-woocommerce-pro'),
                'delay_value'      => 24,
                'delay_unit'       => 'hours',
                'subject'          => __('Still thinking it over?', 'one-page-quick-checkout-for-woocommerce-pro'),
                'discount_code'    => '',
                'from_email'       => '',
                'heading'          => __('We saved your cart', 'one-page-quick-checkout-for-woocommerce-pro'),
                'send_to'          => 'customer',
                'custom_recipient' => '',
                'cart_items_layout' => 'cards',
                'message'          => self::get_default_message_template('value_reinforcement'),
                'enabled'          => 1,
            ),
            array(
                'id'               => 'final_attempt',
                'name'             => __('Final Attempt', 'one-page-quick-checkout-for-woocommerce-pro'),
                'delay_value'      => 72,
                'delay_unit'       => 'hours',
                'subject'          => __('Your cart is about to expire', 'one-page-quick-checkout-for-woocommerce-pro'),
                'discount_code'    => '',
                'from_email'       => '',
                'heading'          => __('We saved your cart', 'one-page-quick-checkout-for-woocommerce-pro'),
                'send_to'          => 'customer',
                'custom_recipient' => '',
                'cart_items_layout' => 'compact',
                'message'          => self::get_default_message_template('final_attempt'),
                'enabled'          => 1,
            ),
        );
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

        return '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%;max-width:640px;margin:0 auto;font-family:Arial,Helvetica,sans-serif;color:#111827;">' .
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
            '</td></tr></table>';
    }

    private static function build_merge_tags($cart, $template, $open_token, $click_token)
    {
        $items = self::decode_json(isset($cart['cart_snapshot']) ? $cart['cart_snapshot'] : '', array());
        $metadata = self::decode_json(isset($cart['metadata']) ? $cart['metadata'] : '', array());
        $profile = isset($metadata['customer_profile']) && is_array($metadata['customer_profile']) ? $metadata['customer_profile'] : array();
        $settings = self::get_settings();
        $cart_link = self::append_tracking_params(self::get_restore_url($click_token), $settings);
        $currency = ! empty($cart['currency']) ? $cart['currency'] : get_woocommerce_currency();
        $customer_name = ! empty($profile['customer_name'])
            ? sanitize_text_field($profile['customer_name'])
            : sanitize_text_field(isset($cart['customer_name']) ? $cart['customer_name'] : '');
        $customer_email = ! empty($profile['email']) ? sanitize_email($profile['email']) : sanitize_email(isset($cart['email']) ? $cart['email'] : '');
        $first_name = ! empty($profile['first_name']) ? sanitize_text_field($profile['first_name']) : self::get_first_name($customer_name);
        $last_name = ! empty($profile['last_name']) ? sanitize_text_field($profile['last_name']) : self::get_last_name($customer_name);
        $phone = ! empty($profile['phone']) ? sanitize_text_field($profile['phone']) : '';
        $company = ! empty($profile['company']) ? sanitize_text_field($profile['company']) : '';
        $item_count = isset($cart['item_count']) ? absint($cart['item_count']) : count($items);
        if (! $item_count) {
            $item_count = count($items);
        }

        return array(
            '{customer_firstname}' => $first_name,
            '{customer_lastname}'  => $last_name,
            '{customer_name}'      => $customer_name ? $customer_name : $first_name,
            '{customer_email}'     => $customer_email,
            '{customer_phone}'     => $phone,
            '{customer_company}'   => $company,
            '{cart_items}'         => self::build_cart_items_markup($items, $currency, isset($template['cart_items_layout']) ? $template['cart_items_layout'] : 'table'),
            '{cart_total}'         => wp_strip_all_tags(wc_price((float) $cart['cart_total'], array('currency' => $currency))),
            '{cart_item_count}'    => (string) $item_count,
            '{cart_currency}'      => $currency,
            '{cart_created_at}'    => self::format_merge_tag_datetime(isset($cart['created_at']) ? $cart['created_at'] : ''),
            '{cart_abandoned_at}'  => self::format_merge_tag_datetime(isset($cart['abandoned_at']) ? $cart['abandoned_at'] : ''),
            '{cart_link}'          => $cart_link,
            '{discount_code}'      => isset($template['discount_code']) ? sanitize_text_field($template['discount_code']) : '',
            '{unsubscribe_link}'   => self::get_unsubscribe_url($cart['recovery_token']),
            '{sitename}'           => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
            '{site_url}'           => home_url('/'),
            '{store_email}'        => self::get_store_email(),
        );
    }

    private static function get_open_url($token)
    {
        return add_query_arg('onepaqucpro_cr_open', rawurlencode($token), home_url('/'));
    }

    private static function get_restore_url($token)
    {
        return add_query_arg('onepaqucpro_cr_restore', rawurlencode($token), home_url('/'));
    }

    private static function get_unsubscribe_url($token)
    {
        return add_query_arg('onepaqucpro_cr_unsubscribe', rawurlencode($token), home_url('/'));
    }

    private static function build_cart_items_markup($items, $currency, $layout = 'table')
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
                'price'       => wp_kses_post(wc_price(isset($item['price']) ? (float) $item['price'] : 0, array('currency' => $currency))),
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

    private static function replace_merge_tags($content, $tags)
    {
        return strtr((string) $content, $tags);
    }

    private static function get_first_name($customer_name)
    {
        $parts = preg_split('/\s+/', trim((string) $customer_name));

        return ! empty($parts[0]) ? $parts[0] : __('Customer', 'one-page-quick-checkout-for-woocommerce-pro');
    }

    private static function get_last_name($customer_name)
    {
        $parts = preg_split('/\s+/', trim((string) $customer_name));

        return count($parts) > 1 ? end($parts) : '';
    }

    private static function format_merge_tag_datetime($date)
    {
        $timestamp = self::to_timestamp($date);
        if (! $timestamp) {
            return '';
        }

        $format = trim(get_option('date_format') . ' ' . get_option('time_format'));

        return wp_date($format, $timestamp, wp_timezone());
    }

    private static function get_store_email()
    {
        $store_email = get_option('woocommerce_email_from_address');

        return $store_email ? sanitize_email($store_email) : sanitize_email(get_option('admin_email'));
    }

    private static function get_item_name($product, $variation, $product_id = 0)
    {
        $name = $product->get_name();
        return ! empty($variation) ? self::strip_variation_suffix_from_item_name($name, $variation) : $name;
    }

    private static function get_client_ip()
    {
        $keys = array('HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        foreach ($keys as $key) {
            if (empty($_SERVER[$key])) {
                continue;
            }

            $raw = sanitize_text_field(wp_unslash($_SERVER[$key]));
            if ('HTTP_X_FORWARDED_FOR' === $key && false !== strpos($raw, ',')) {
                $parts = explode(',', $raw);
                $raw   = trim($parts[0]);
            }

            if ($raw) {
                return $raw;
            }
        }

        return '';
    }

    private static function get_user_agent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
    }

    private static function get_http_referrer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : '';
    }

    private static function get_current_request_url()
    {
        if (empty($_SERVER['HTTP_HOST']) || empty($_SERVER['REQUEST_URI'])) {
            return '';
        }

        return esc_url_raw((is_ssl() ? 'https://' : 'http://') . wp_unslash($_SERVER['HTTP_HOST']) . wp_unslash($_SERVER['REQUEST_URI']));
    }

    private static function should_skip_cart($row, $settings, $metadata)
    {
        $premium_enabled = self::can_use_premium_features();

        if ($row['cart_total'] <= 0 && (! $premium_enabled || empty($settings['track_free_carts']))) {
            return true;
        }

        if (! $premium_enabled) {
            return false;
        }

        if (self::email_domain_is_excluded(isset($row['email']) ? $row['email'] : '', $settings)) {
            return true;
        }

        if (self::cart_has_excluded_products($metadata, $settings)) {
            return true;
        }

        if (self::cart_has_excluded_categories($metadata, $settings)) {
            return true;
        }

        if (self::customer_has_excluded_role(isset($row['customer_id']) ? (int) $row['customer_id'] : 0, $settings)) {
            return true;
        }

        return false;
    }

    private static function email_domain_is_excluded($email, $settings)
    {
        if (! $email || empty($settings['excluded_domains']) || ! is_array($settings['excluded_domains'])) {
            return false;
        }

        $parts  = explode('@', sanitize_email($email));
        $domain = isset($parts[1]) ? strtolower(trim($parts[1])) : '';

        return $domain && in_array($domain, array_map('strtolower', $settings['excluded_domains']), true);
    }

    private static function cart_has_excluded_products($metadata, $settings)
    {
        $cart_products     = isset($metadata['product_ids']) && is_array($metadata['product_ids']) ? array_map('absint', $metadata['product_ids']) : array();
        $excluded_products = isset($settings['excluded_product_ids']) && is_array($settings['excluded_product_ids']) ? array_map('absint', $settings['excluded_product_ids']) : array();

        return (bool) array_intersect($cart_products, $excluded_products);
    }

    private static function cart_has_excluded_categories($metadata, $settings)
    {
        $cart_categories     = isset($metadata['category_ids']) && is_array($metadata['category_ids']) ? array_map('absint', $metadata['category_ids']) : array();
        $excluded_categories = isset($settings['excluded_category_ids']) && is_array($settings['excluded_category_ids']) ? array_map('absint', $settings['excluded_category_ids']) : array();

        return (bool) array_intersect($cart_categories, $excluded_categories);
    }

    private static function customer_has_excluded_role($customer_id, $settings)
    {
        if (! $customer_id || empty($settings['excluded_roles']) || ! is_array($settings['excluded_roles'])) {
            return false;
        }

        $user = get_userdata($customer_id);
        if (! $user) {
            return false;
        }

        return (bool) array_intersect((array) $user->roles, $settings['excluded_roles']);
    }

    private static function is_within_sending_window($settings, $timestamp)
    {
        if (empty($settings['quiet_hours_enabled'])) {
            return true;
        }

        $start = isset($settings['send_window_start']) ? $settings['send_window_start'] : '08:00';
        $end   = isset($settings['send_window_end']) ? $settings['send_window_end'] : '20:00';
        $time  = wp_date('H:i', $timestamp, wp_timezone());

        if ($start === $end) {
            return true;
        }

        if ($start < $end) {
            return $time >= $start && $time <= $end;
        }

        return $time >= $start || $time <= $end;
    }

    private static function count_sent_emails($cart_id)
    {
        global $wpdb;

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(id) FROM " . self::get_emails_table() . " WHERE cart_id = %d AND status <> 'failed'",
                (int) $cart_id
            )
        );
    }

    private static function update_cart_admin_state($cart, $state, $event_title)
    {
        self::merge_cart_metadata(
            (int) $cart['id'],
            array(
                'admin_state' => $state,
            )
        );

        self::insert_event(
            (int) $cart['id'],
            $state ? 'cart_' . $state : 'cart_reactivated',
            $event_title,
            current_time('mysql'),
            array(
                __('Source', 'one-page-quick-checkout-for-woocommerce-pro') => __('Updated from admin', 'one-page-quick-checkout-for-woocommerce-pro'),
                __('State', 'one-page-quick-checkout-for-woocommerce-pro') => $state ? ucfirst($state) : __('Active', 'one-page-quick-checkout-for-woocommerce-pro'),
            )
        );

        return true;
    }

    private static function send_last_template_now($cart)
    {
        global $wpdb;

        $email = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . self::get_emails_table() . " WHERE cart_id = %d ORDER BY sent_at DESC LIMIT 1",
                (int) $cart['id']
            ),
            ARRAY_A
        );

        if (! $email) {
            return false;
        }

        foreach (self::get_templates() as $template) {
            if ($template['id'] === $email['template_id']) {
                return (bool) self::send_recovery_email($cart, $template);
            }
        }

        return false;
    }

    private static function send_next_template_now($cart)
    {
        foreach (self::get_templates() as $template) {
            if (empty($template['enabled']) || self::has_email_been_sent($cart['id'], $template['id'])) {
                continue;
            }

            return (bool) self::send_recovery_email($cart, $template);
        }

        return false;
    }

    private static function get_cart_row_by_id($cart_id)
    {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . self::get_carts_table() . " WHERE id = %d LIMIT 1",
                (int) $cart_id
            ),
            ARRAY_A
        );
    }

    private static function get_email_row_by_id($email_id)
    {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . self::get_emails_table() . " WHERE id = %d LIMIT 1",
                (int) $email_id
            ),
            ARRAY_A
        );
    }

    private static function get_latest_cart_row()
    {
        global $wpdb;

        return $wpdb->get_row(
            "SELECT * FROM " . self::get_carts_table() . " ORDER BY updated_at DESC LIMIT 1",
            ARRAY_A
        );
    }

    private static function merge_cart_metadata($cart_id, $updates)
    {
        $cart = self::get_cart_row_by_id($cart_id);
        if (! $cart) {
            return false;
        }

        $metadata = self::decode_json(isset($cart['metadata']) ? $cart['metadata'] : '', array());

        foreach ($updates as $key => $value) {
            if ('tags' === $key) {
                $value = self::sanitize_text_list($value);
            } elseif ('notes' === $key) {
                $value = sanitize_textarea_field($value);
            }

            $metadata[sanitize_key((string) $key)] = is_array($value) ? self::sanitize_recursive($value) : $value;
        }

        return false !== self::update_cart_row(
            (int) $cart_id,
            array(
                'metadata' => wp_json_encode($metadata),
            )
        );
    }

    private static function get_template_sender_email($template, $settings)
    {
        if (! empty($template['from_email']) && is_email($template['from_email'])) {
            return sanitize_email($template['from_email']);
        }

        if ('store' === (isset($settings['sender']) ? $settings['sender'] : 'default')) {
            $store_email = get_option('woocommerce_email_from_address');
            if ($store_email) {
                return sanitize_email($store_email);
            }
        }

        return sanitize_email(get_option('admin_email'));
    }

    private static function get_template_reply_to($template, $settings)
    {
        return ! empty($settings['reply_to']) && is_email($settings['reply_to']) ? sanitize_email($settings['reply_to']) : '';
    }

    private static function append_tracking_params($url, $settings)
    {
        if (empty($settings['append_utm'])) {
            return $url;
        }

        return add_query_arg(
            array(
                'utm_source'   => isset($settings['utm_source']) ? $settings['utm_source'] : 'cart-recovery',
                'utm_medium'   => isset($settings['utm_medium']) ? $settings['utm_medium'] : 'email',
                'utm_campaign' => isset($settings['utm_campaign']) ? $settings['utm_campaign'] : 'recover-cart',
            ),
            $url
        );
    }

    private static function detect_browser($user_agent)
    {
        $user_agent = strtolower((string) $user_agent);

        if (false !== strpos($user_agent, 'edg/')) {
            return 'Edge';
        }
        if (false !== strpos($user_agent, 'chrome/')) {
            return 'Chrome';
        }
        if (false !== strpos($user_agent, 'safari/') && false === strpos($user_agent, 'chrome/')) {
            return 'Safari';
        }
        if (false !== strpos($user_agent, 'firefox/')) {
            return 'Firefox';
        }

        return 'Other';
    }

    private static function detect_device_type($user_agent)
    {
        $user_agent = strtolower((string) $user_agent);

        if (false !== strpos($user_agent, 'ipad') || false !== strpos($user_agent, 'tablet')) {
            return 'tablet';
        }
        if (false !== strpos($user_agent, 'mobile') || false !== strpos($user_agent, 'android')) {
            return 'mobile';
        }

        return 'desktop';
    }

    private static function dispatch_webhook($event_type, $payload)
    {
        $settings = self::get_settings();
        $url      = isset($settings['webhook_url']) ? esc_url_raw($settings['webhook_url']) : '';

        if (! $url) {
            return;
        }

        wp_remote_post($url, array(
            'timeout'  => 5,
            'blocking' => false,
            'headers'  => array(
                'Content-Type' => 'application/json',
            ),
            'body'     => wp_json_encode(array(
                'event'   => $event_type,
                'payload' => $payload,
                'site'    => home_url('/'),
            )),
        ));
    }

    private static function delay_to_seconds($value, $unit)
    {
        $value = max(1, absint($value));

        switch ($unit) {
            case 'days':
                return $value * DAY_IN_SECONDS;
            case 'minutes':
                return $value * MINUTE_IN_SECONDS;
            case 'hours':
            default:
                return $value * HOUR_IN_SECONDS;
        }
    }

    private static function sanitize_delay_unit($unit)
    {
        return in_array($unit, array('minutes', 'hours', 'days'), true) ? $unit : 'hours';
    }

    private static function sanitize_cart_items_layout($layout)
    {
        return in_array($layout, array('table', 'list', 'compact', 'cards'), true) ? $layout : 'table';
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

    private static function is_default_template_id($template_id)
    {
        return in_array(sanitize_key($template_id), array('immediate_recovery', 'value_reinforcement', 'final_attempt'), true);
    }

    private static function is_legacy_default_message($message)
    {
        $message = (string) $message;

        return false !== strpos($message, 'Looks like you left something behind')
            && false !== strpos($message, '{cart_items}')
            && false !== strpos($message, 'Resume Checkout')
            && false === strpos($message, 'max-width:640px');
    }

    private static function generate_token()
    {
        return wp_generate_password(32, false, false);
    }

    private static function decode_json($value, $fallback)
    {
        if (! $value) {
            return $fallback;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : $fallback;
    }

    private static function sanitize_recursive($value)
    {
        if (is_array($value)) {
            $sanitized = array();
            foreach ($value as $key => $item) {
                $sanitized[sanitize_key((string) $key)] = self::sanitize_recursive($item);
            }

            return $sanitized;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_int($value) || is_float($value)) {
            return $value + 0;
        }

        if (is_string($value)) {
            return wc_clean($value);
        }

        return '';
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
        if (! $date) {
            return 0;
        }

        try {
            $datetime = new DateTimeImmutable($date, wp_timezone());
        } catch (Exception $exception) {
            return 0;
        }

        return $datetime->getTimestamp();
    }
}

Onepaqucpro_Cart_Recovery_Tracker::init();
