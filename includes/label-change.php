<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly


// Customize WooCommerce checkout text labels
function onepaquc_custom_woocommerce_checkout_text($translated_text, $text, $domain)
{
    global $onepaquc_checkoutformfields, $onepaquc_productpageformfields;
    $onepaquc_checkoutformfields    = is_array($onepaquc_checkoutformfields) ? array_filter($onepaquc_checkoutformfields, 'is_scalar') : array();
    $onepaquc_productpageformfields = is_array($onepaquc_productpageformfields) ? array_filter($onepaquc_productpageformfields, 'is_scalar') : array();

    // convert $onepaquc_checkoutformfields to array $mapping
    $mapping = array_merge(array_flip($onepaquc_checkoutformfields), array_flip($onepaquc_productpageformfields));


    if ($domain === 'woocommerce' && array_key_exists($text, $mapping)) {
        $option_key = $mapping[$text];
        $fallback = isset($onepaquc_checkoutformfields[$option_key])
            ? $onepaquc_checkoutformfields[$option_key]
            : (isset($onepaquc_productpageformfields[$option_key]) ? $onepaquc_productpageformfields[$option_key] : $translated_text);
        $translated_text = onepaquc_get_text_option($option_key, is_scalar($fallback) ? (string) $fallback : (string) $translated_text);
    }

    return $translated_text;
}
add_filter('gettext', 'onepaquc_custom_woocommerce_checkout_text', 20, 3);


// Change "Shipping" label in WooCommerce shipping totals section
function onepaquc_custom_woocommerce_shipping_label($label, $package_name)
{
    return onepaquc_get_text_option('txt_shipping', __('Shipping', 'one-page-quick-checkout-for-woocommerce'));
}
add_filter('woocommerce_shipping_package_name', 'onepaquc_custom_woocommerce_shipping_label', 10, 2);

