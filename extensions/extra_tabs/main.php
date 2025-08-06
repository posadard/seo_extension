<?php

/* Main extension driver containing details about extension files */

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

if (!class_exists('ExtensionExtraTabs')) {
    include 'core/extra_tabs.php';
}

$controllers = [
    'storefront' => [''],
    'admin' => [
        'pages/catalog/product_extra_options',
        'responses/load_extra/product',
    ],
];

$models = [
    'storefront' => ['extra_tabs/extra_tabs'],
    'admin' => ['extra_tabs/product'],
];

$languages = [
    'storefront' => [],
    'admin' => ['extra_tabs/extra_tabs'],
];

$templates = [
    'storefront' => [
        'pages/extra_tabs/extra_tabs_tab.tpl',
        'pages/extra_tabs/extra_tabs_outside.tpl', // ignore
        'pages/extra_tabs/extra_tabs_content.tpl',
    ],
    'admin' => [
        'pages/extension/tabs.tpl',
        'pages/extension/product_options.tpl',
        'responses/load_extra/add_option_value.tpl',
        'responses/load_extra/option_value_row.tpl',
        'responses/load_extra/option_values.tpl',
        'responses/load_extra/product_file_row.tpl',
        'responses/load_extra/global_attribute_product_option_subform.tpl',
    ],
];
