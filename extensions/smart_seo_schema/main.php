<?php

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

if (!class_exists('ExtensionSmartSeoSchema')) {
    include_once('core/smart_seo_schema.php');
}
$controllers = [
    'storefront' => [],
    'admin'      => [],
];

$models = [
    'storefront' => [],
    'admin'      => [],
];

$templates = [
    'storefront' => [
        'pages/product/product.post.tpl',
    ],
    'admin'      => [],
];

$languages = [
    'storefront' => [],
    'admin'      => [
        'english/smart_seo_schema/smart_seo_schema',
    ],
];

