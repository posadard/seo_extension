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
    'storefront' => [
        'smart_seo_schema/smart_seo_schema_tabs',
    ],
    'admin'      => [],
];
$templates = [
    'storefront' => [
        'pages/product/product.post.tpl',
        'pages/smart_seo_schema/faq_tab.tpl',
        'pages/smart_seo_schema/faq_content.tpl',
        'pages/smart_seo_schema/howto_tab.tpl',
        'pages/smart_seo_schema/howto_content.tpl',
    ],
    'admin'      => [
        'pages/smart_seo_schema/smart_seo_schema_form.tpl',
        'pages/smart_seo_schema/reviews_section.tpl',
        'pages/smart_seo_schema/tabs.tpl',
    ],
];
$languages = [
    'storefront' => [
        'english/smart_seo_schema/smart_seo_schema',
    ],
    'admin'      => [
        'english/smart_seo_schema/smart_seo_schema',
    ],
];