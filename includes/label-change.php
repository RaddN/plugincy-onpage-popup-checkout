<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly


// Customize WooCommerce checkout text labels
function onepaquc_custom_woocommerce_checkout_text($translated_text, $text, $domain)
{
    global $onepaquc_checkoutformfields, $onepaquc_productpageformfields;
    // convert $onepaquc_checkoutformfields to array $mapping
    $mapping = array_merge(array_flip($onepaquc_checkoutformfields), array_flip($onepaquc_productpageformfields));


    if ($domain === 'woocommerce' && array_key_exists($text, $mapping)) {
        $option_key = $mapping[$text];
        $translated_text = get_option($option_key) ? esc_attr(get_option($option_key)) : $onepaquc_checkoutformfields[$option_key] ?? $onepaquc_productpageformfields[$option_key];
    }

    return $translated_text;
}
add_filter('gettext', 'onepaquc_custom_woocommerce_checkout_text', 20, 3);


// Change "Shipping" label in WooCommerce shipping totals section
function onepaquc_custom_woocommerce_shipping_label($label, $package_name)
{
    return get_option("txt_shipping") ? esc_attr(get_option("txt_shipping", 'Shipping')) : "Shipping"; // Change "Shipping" to "Delivery Charges"
}
add_filter('woocommerce_shipping_package_name', 'onepaquc_custom_woocommerce_shipping_label', 10, 2);

