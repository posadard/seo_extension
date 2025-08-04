<?php

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

if (!class_exists('ExtensionRichSnippets')) {
    include_once('core/rich_snippets.php');
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
        'english/rich_snippets/rich_snippets',
    ],
];

