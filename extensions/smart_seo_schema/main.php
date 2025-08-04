<?php

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

if (!class_exists('ExtensionSmartSeoSchema')) {
    require_once(DIR_EXT . 'smart_seo_schema/core/smart_seo_schema.php');
}

$controllers = [
    'storefront' => [],
    'admin'      => [
        'pages/catalog/smart_seo_schema',
    ],
];

$models = [
    'storefront' => [],
    'admin'      => [],
];

$templates = [
    'storefront' => [
        'pages/product/product.post.tpl',
    ],
    'admin'      => [
        'pages/smart_seo_schema/smart_seo_schema_form.tpl',
        'pages/smart_seo_schema/tabs.tpl',
    ],
];

$languages = [
    'storefront' => [],
    'admin'      => [
        'english/smart_seo_schema/smart_seo_schema',
    ],
];