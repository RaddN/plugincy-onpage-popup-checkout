<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

$onepaquc_checkoutformfields = [
    "your_cart" => "Your Cart",
    "btn_remove" => "Remove Button",
    "txt_subtotal" => "Subtotal",
    "txt_checkout" => "Place order",
    "txt_billing_details" => "Billing details",
    "txt_email_address" => "Email address",
    "txt_first_name" => "First name",
    "txt_last_name" => "Last name",
    "txt_country" => "Country / Region",
    "txt_street" => "Street address",
    "txt_city" => "Town / City",
    "txt_district" => "District",
    "txt_postcode" => 'Postcode / ZIP',
    "txt_phone_number" => "Phone",
    "txt_notes" => "Order notes",
    "txt_product" => "Product",
    "txt_shipping" => "Shipping",
    "txt_shipping_address_head" => "Ship to a different address?",
    "txt_total" => "Total",
    "btn_place_order" => "Place Order Button",
    "txt-woocommerce-privacy-policy-text" => "woocommerce privacy policy text",
    "txt-have_coupon" => "Have a coupon?",
    "txt-apply_coupon_below" => "If you have a coupon code, please apply it below.",
    "txt-complete_your_purchase" => "Complete your purchase using the form below.",
];

// archive & single product page text

$onepaquc_productpageformfields = [
    "txt-add-to-cart" => "Add to cart",
    "txt-select-options" => "Select options",
    "txt-read-more" => "Read more",
    "rmenu_grouped_add_to_cart_text" => "View products",
    "rmenu_out_of_stock_text" => "Out of stock"
];

$onepaquc_rcheckoutformfields = [
    'first_name' => ['title' => 'First Name', 'selector' => '#billing_first_name_field, #shipping_first_name_field'],
    'last_name'  => ['title' => 'Last Name', 'selector' => '#billing_last_name_field, #shipping_last_name_field'],
    'country'      => ['title' => 'Country', 'selector' => '#billing_country_field, #shipping_country_field'],
    'state'      => ['title' => 'State / District', 'selector' => '#billing_state_field, #shipping_state_field'],
    'city'       => ['title' => 'City', 'selector' => '#billing_city_field, #shipping_city_field'],
    'postcode'   => ['title' => 'Postcode', 'selector' => '#billing_postcode_field, #shipping_postcode_field'],
    'address_1'  => ['title' => 'Address 1', 'selector' => '#billing_address_1_field, #shipping_address_1_field'],
    'address_2'  => ['title' => 'Address 2', 'selector' => '#billing_address_2_field, #shipping_address_2_field'],
    'phone'      => ['title' => 'Phone', 'selector' => '#billing_phone_field'],
    'email'      => ['title' => 'Email', 'selector' => '#billing_email_field'],
    'company'    => ['title' => 'Company', 'selector' => '#billing_company_field'],
    'notes'     => ['title' => 'Notes', 'selector' => '#order_comments_field'],
];
