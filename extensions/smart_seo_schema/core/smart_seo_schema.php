<?php

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

class ExtensionSmartSeoSchema extends Extension
{
    protected $registry;

    public function __construct()
    {
        $this->registry = Registry::getInstance();
    }

    public function onControllerPagesCatalogProductTabs_InitData()
    {
        /** @var ControllerPagesCatalogProduct $that */
        $that = $this->baseObject;
        
        if (!$that->config->get('smart_seo_schema_status')) {
            return;
        }

        $that->loadLanguage('smart_seo_schema/smart_seo_schema');

        $that->data['tabs'][] = [
            'name'   => 'smart_seo_schema',
            'text'   => $that->language->get('smart_seo_schema_name') ?: 'Smart SEO Schema',
            'href'   => $that->html->getSecureURL(
                'catalog/smart_seo_schema',
                '&product_id=' . $that->request->get['product_id']
            ),
        ];
    }

    public function onControllerPagesCatalogProductTabs_UpdateData()
    {
        /** @var ControllerPagesCatalogProduct $that */
        $that = $this->baseObject;
        
        if (!$that->config->get('smart_seo_schema_status')) {
            return;
        }

        $that->loadLanguage('smart_seo_schema/smart_seo_schema');

        $this->data = [
            'active' => $that->data['active'],
        ];

        $this->data['tabs'][] = [
            'name'   => 'smart_seo_schema',
            'href'   => $that->html->getSecureURL(
                'catalog/smart_seo_schema',
                '&product_id=' . $that->request->get['product_id']
            ),
            'text'   => $that->language->get('smart_seo_schema_name') ?: 'Smart SEO Schema',
            'active' => ($that->data['active'] == 'smart_seo_schema'),
        ];

        $view = new AView(Registry::getInstance(), 0);
        $view->batchAssign($this->data);
        $that->view->addHookVar('extension_tabs', $view->fetch('pages/smart_seo_schema/tabs.tpl'));
    }

    public function onControllerPagesProductProduct_UpdateData()
    {
        /** @var ControllerPagesProductProduct $that */
        $that = $this->baseObject;

        if (!$that->config->get('smart_seo_schema_status')) {
            return;
        }

        // Generate Schema.org JSON-LD
        $schema_data = $this->generateCompleteSchema($that);
        $that->load->library('json');
        $that->view->assign('smart_seo_schema_data', AJson::encode($schema_data));

        // Prepare Open Graph data
        $og_data = $this->prepareOpenGraphData($that);
        $that->view->assign('smart_seo_schema_og', $og_data);

        // Register frontend tabs if enabled
        $this->registerProductTabs($that);
    }

    private function prepareOpenGraphData($that)
    {
        $product_info = $that->data['product_info'];
        $product_id = $product_info['product_id'];

        $store_url = rtrim($that->config->get('config_ssl_url') ?: $that->config->get('config_url'), '/');
        $product_url = $store_url . '/index.php?rt=product/product&product_id=' . $product_id;

        $og = [
            'og:title' => $product_info['name'],
            'og:type'  => 'product',
            'og:url'   => $product_url,
        ];

        $images = $this->getProductImages($that, $product_info);
        if (!empty($images)) {
            $og['og:image'] = $images[0];
        }

        if (!empty($product_info['meta_description'])) {
            $og['og:description'] = $product_info['meta_description'];
        }

        if (!empty($product_info['price'])) {
            $og['product:price:amount'] = number_format($product_info['price'], 2, '.', '');
            $og['product:price:currency'] = $that->currency->getCode();
        }

        return $og;
    }

    private function registerProductTabs($that)
    {
        $that->loadModel('smart_seo_schema/smart_seo_schema_tabs');

        $product_id = $that->request->get['product_id'];
        if (empty($product_id)) {
            $product_id = $that->request->get['key'];
        }

        if (!$product_id) {
            return;
        }

        $schema_tabs = $that->model_smart_seo_schema_smart_seo_schema_tabs->getProductSchemaTabs((int) $product_id);

        if (!$schema_tabs['faq'] && !$schema_tabs['howto']) {
            return;
        }

        $this->injectTabsForCurrentTemplate($schema_tabs);
    }

    public function onControllerPagesProductProduct_InitData()
    {
        if (!$this->baseObject->config->get('smart_seo_schema_status')) {
            return;
        }

        $this->baseObject->loadModel('smart_seo_schema/smart_seo_schema_tabs');
        $product_id = $this->baseObject->request->get['product_id'];
        
        if (empty($product_id)) {
            $product_id = $this->baseObject->request->get['key'];
        }

        if (!$product_id) {
            return;
        }

        $schema_tabs = $this->baseObject->model_smart_seo_schema_smart_seo_schema_tabs->getProductSchemaTabs((int) $product_id);

        if (!$schema_tabs['faq'] && !$schema_tabs['howto']) {
            return;
        }

        $this->injectTabsForCurrentTemplate($schema_tabs);
    }

    private function injectTabsForCurrentTemplate($schema_tabs)
    {
        $template = $this->baseObject->config->get('config_storefront_template');
        
        switch ($template) {
            case 'foxy_template':
            case 'foxy':
                $this->injectForFoxy($schema_tabs);
                break;
                
            default:
                $this->injectForDefault($schema_tabs);
        }
    }

    private function injectForFoxy($schema_tabs)
    {
        $view = new AView($this->registry, 0);
        $data = [];
        
        // Simular estructura similar a extra_tabs
        $data['all_options'] = [];
        
        if ($schema_tabs['faq']) {
            $data['all_options'][] = [
                'product_option_id' => 'smart_seo_faq',
                'name' => $schema_tabs['faq']['title'],
                'error_text' => $schema_tabs['faq']['title'],
                'required' => 0,
                'option_value' => [
                    ['name' => $schema_tabs['faq']['content']]
                ]
            ];
        }
        
        if ($schema_tabs['howto']) {
            $data['all_options'][] = [
                'product_option_id' => 'smart_seo_howto',
                'name' => $schema_tabs['howto']['title'],
                'error_text' => $schema_tabs['howto']['title'],
                'required' => 0,
                'option_value' => [
                    ['name' => $schema_tabs['howto']['content']]
                ]
            ];
        }
        
        if (!empty($data['all_options'])) {
            $view->batchAssign($data);
            $tab_headers = $view->fetch('pages/smart_seo_schema/faq_tab.tpl');
            $tab_content = $view->fetch('pages/smart_seo_schema/faq_content.tpl');
            
            $this->baseObject->view->addHookVar('product_features_tab', $tab_headers);
            $this->baseObject->view->addHookVar('product_features', $tab_content);
        }
    }

    private function injectForDefault($schema_tabs)
    {
        $view = new AView($this->registry, 0);
        
        $tab_headers = '';
        $tab_content = '';
        
        if ($schema_tabs['faq']) {
            $view->batchAssign(['faq_data' => $schema_tabs['faq']]);
            $tab_headers .= $view->fetch('pages/smart_seo_schema/faq_tab.tpl');
            $tab_content .= $view->fetch('pages/smart_seo_schema/faq_content.tpl');
        }
        
        if ($schema_tabs['howto']) {
            $view->batchAssign(['howto_data' => $schema_tabs['howto']]);
            $tab_headers .= $view->fetch('pages/smart_seo_schema/howto_tab.tpl');
            $tab_content .= $view->fetch('pages/smart_seo_schema/howto_content.tpl');
        }
        
        if (!empty($tab_headers)) {
            $this->baseObject->view->addHookVar('product_features_tab', $tab_headers);
        }
        if (!empty($tab_content)) {
            $this->baseObject->view->addHookVar('product_features', $tab_content);
        }
    }

    public function generateCompleteSchemaForProduct($product_info)
    {
        $registry = Registry::getInstance();
        $db = $registry->get('db');
        $config = $registry->get('config');
        
        $mock_controller = new stdClass();
        $mock_controller->config = $config;
        $mock_controller->db = $db;
        $mock_controller->currency = $registry->get('currency');
        $mock_controller->language = $registry->get('language');
        
        $mock_controller->load = new stdClass();
        $mock_controller->load->library = function($library) {
            if ($library === 'json') {
                return true;
            }
        };
        
        $mock_controller->model_catalog_review = new stdClass();
        $mock_controller->model_catalog_review->getTotalReviewsByProductId = function($product_id) {
            return 0;
        };
        
        if (!isset($mock_controller->db)) {
            $mock_controller->db = $db;
        }
        
        $mock_controller->data = [
            'product_info' => $product_info,
            'average' => $product_info['rating'] ?? 0,
            'stock' => 'In Stock',
            'product_review_url' => ''
        ];
        
        return $this->generateCompleteSchema($mock_controller);
    }

    private function generateCompleteSchema($that)
    {
        $product_info = $that->data['product_info'];
        $product_id = $product_info['product_id'];

        $saved_content = $this->getSavedSchemaContent($product_id);
        $product_snippet = $this->generateProductSnippet($product_info, $that, $saved_content);

        $variants = [];
        if ($that->config->get('smart_seo_schema_enable_variants') && 
            (!empty($saved_content['enable_variants']) || $saved_content === null)) {
            $variants = $this->getProductVariants($product_info['product_id'], $that, $saved_content);
            if (!empty($variants)) {
                $product_snippet["hasVariant"] = $variants;
            }
        }

        if ($that->config->get('smart_seo_schema_show_offer')) {
            $offer = $this->getProductOffer($that, $product_info, $saved_content, $variants);
            if ($offer) {
                $product_snippet["offers"] = $offer;
            }
        }

        $aggregateRating = $this->generateAggregateRating($that, $product_info);
        if ($aggregateRating) {
            $product_snippet["aggregateRating"] = $aggregateRating;
        }

        if ($saved_content && !empty($saved_content['others_content'])) {
            $others_data = json_decode($saved_content['others_content'], true);
            if (is_array($others_data)) {
                foreach ($others_data as $key => $value) {
                    if (!isset($product_snippet[$key]) && !empty($value)) {
                       $product_snippet[$key] = $value;
                    }
                }
            }
        }

        $additional_schemas = $this->generateAdditionalSchemas($product_info, $that, $saved_content);
        
        return array_merge([$product_snippet], $additional_schemas);
    }

    private function getSavedSchemaContent($product_id)
    {
        try {
            $db = $this->registry->get('db');
            
            $query = $db->query("
                SELECT 
                    custom_description,
                    faq_content,
                    howto_content,
                    review_content,
                    others_content,
                    enable_variants,
                    enable_faq,
                    enable_howto,
                    enable_review,
                    show_faq_tab_frontend,
                    show_howto_tab_frontend
                FROM " . DB_PREFIX . "seo_schema_content 
                WHERE product_id = " . (int)$product_id . "
                LIMIT 1
            ");

            if ($query->num_rows) {
                $content = $query->row;
                
                $content['enable_variants'] = (bool)$content['enable_variants'];
                $content['enable_faq'] = (bool)$content['enable_faq'];
                $content['enable_howto'] = (bool)$content['enable_howto'];
                $content['enable_review'] = (bool)$content['enable_review'];
                $content['show_faq_tab_frontend'] = (bool)$content['show_faq_tab_frontend'];
                $content['show_howto_tab_frontend'] = (bool)$content['show_howto_tab_frontend'];
                
                return $content;
            }
        } catch (Exception $e) {
            if ($this->registry->get('config')->get('smart_seo_schema_debug_mode')) {
                $warning = new AWarning('Smart SEO Schema - Error loading saved content: ' . $e->getMessage());
                $warning->toLog();
            }
        }
        
        return null;
    }

    private function generateProductSnippet($product_info, $that, $saved_content = null)
    {
        $product_snippet = [
            "@context" => "https://schema.org",
            "@type"    => "Product"
        ];

        $product_snippet["name"] = $product_info['name'];

        if ($saved_content && !empty($saved_content['custom_description'])) {
            $description = trim($saved_content['custom_description']);
        } else {
            $description = $this->getProductDescription($that, $product_info['product_id']);
        }
        
        if (!empty($description)) {
            $product_snippet["description"] = $description;
        }

        if ($that->config->get('smart_seo_schema_show_image')) {
            $images = $this->getProductImages($that, $product_info);
            if (!empty($images)) {
                $product_snippet["image"] = count($images) === 1 ? $images[0] : $images;
            }
        }

        if ($that->config->get('smart_seo_schema_show_sku') && !empty($product_info['sku'])) {
            $product_snippet["sku"] = $product_info['sku'];
        }

        if (!empty($product_info['model'])) {
            $product_snippet["mpn"] = $product_info['model'];
        }

        if (!empty($product_info['sku'])) {
            $product_snippet["productGroupID"] = $product_info['sku'];
        }

        $brand = $this->getProductBrand($that, $product_info);
        if (!empty($brand)) {
            $product_snippet["brand"] = [
                "@type" => "Brand",
                "name" => $brand
            ];
        }

        $category = $this->getProductCategory($product_info['product_id'], $that);
        if (!empty($category)) {
            $product_snippet["category"] = $category;
        }

        return $product_snippet;
    }

    private function getProductCategory($product_id, $that)
    {
        $db = $that->db;
        
        $query = $db->query("
            SELECT cd.name
            FROM " . DB_PREFIX . "category_descriptions cd
            INNER JOIN " . DB_PREFIX . "products_to_categories p2c 
              ON cd.category_id = p2c.category_id
            INNER JOIN " . DB_PREFIX . "categories c 
              ON cd.category_id = c.category_id
            WHERE p2c.product_id = " . (int)$product_id . " 
              AND cd.language_id = 1
              AND c.status = 1
              AND c.category_id NOT IN (
                  SELECT DISTINCT parent_id 
                  FROM " . DB_PREFIX . "categories 
                  WHERE parent_id > 0
              )
            ORDER BY c.sort_order ASC
            LIMIT 1
        ");

        if (!$query->num_rows) {
            $query = $db->query("
                SELECT cd.name
                FROM " . DB_PREFIX . "category_descriptions cd
                INNER JOIN " . DB_PREFIX . "products_to_categories p2c 
                  ON cd.category_id = p2c.category_id
                INNER JOIN " . DB_PREFIX . "categories c 
                  ON cd.category_id = c.category_id
                WHERE p2c.product_id = " . (int)$product_id . " 
                  AND cd.language_id = 1
                  AND c.status = 1
                ORDER BY c.sort_order ASC
                LIMIT 1
            ");
        }

        return $query->num_rows ? trim($query->row['name']) : null;
    }

    private function getProductBrand($that, $product_info)
    {
        if (empty($product_info['manufacturer_id'])) {
            return null;
        }

        $db = $that->db;
        $manufacturer_id = (int)$product_info['manufacturer_id'];

        $query = $db->query("
            SELECT name 
            FROM " . DB_PREFIX . "manufacturers 
            WHERE manufacturer_id = " . $manufacturer_id . "
            LIMIT 1
        ");

        if ($query->num_rows) {
            return trim($query->row['name']);
        }

        return null;
    }

    private function getProductVariants($product_id, $that, $saved_content = null)
    {
        $db = $that->db;
        $language_id = $this->getAdminDefaultLanguageId($that);

        $sql = "
            SELECT 
                pov.product_option_value_id,
                pov.sku,
                pov.price,
                pov.prefix,
                pov.quantity,
                COALESCE(povd.name, CONCAT('Variant ', pov.product_option_value_id)) as variant_name
            FROM " . DB_PREFIX . "product_option_values pov
            LEFT JOIN " . DB_PREFIX . "product_option_value_descriptions povd 
                ON pov.product_option_value_id = povd.product_option_value_id 
                AND povd.language_id = " . (int)$language_id . "
            WHERE pov.product_id = " . (int)$product_id . "
            ORDER BY pov.product_option_value_id
        ";

        $query = $db->query($sql);
        
        if (!$query->num_rows) {
            return [];
        }

        $base_product_query = $db->query("
            SELECT p.price, p.sku, p.manufacturer_id, pd.name 
            FROM " . DB_PREFIX . "products p
            LEFT JOIN " . DB_PREFIX . "product_descriptions pd ON p.product_id = pd.product_id 
            WHERE p.product_id = " . (int)$product_id . " AND pd.language_id = " . (int)$language_id . "
            LIMIT 1
        ");
        $base_price = $base_product_query->row['price'] ?? 0;
        $main_sku = $base_product_query->row['sku'] ?? '';
        $product_name = $base_product_query->row['name'] ?? '';
        $manufacturer_id = $base_product_query->row['manufacturer_id'] ?? null;

        $brand = null;
        if ($manufacturer_id) {
            $brand_query = $db->query("
                SELECT name 
                FROM " . DB_PREFIX . "manufacturers 
                WHERE manufacturer_id = " . (int)$manufacturer_id . "
                LIMIT 1
            ");
            if ($brand_query->num_rows) {
                $brand = $brand_query->row['name'];
            }
        }

        $main_image = $this->getProductMainImage($that, $product_id);
        $default_shipping = $this->getDefaultShippingDetails($that);
        $default_return_policy = $this->getDefaultReturnPolicy($that);
        
        $shipping_details = $this->getShippingDetailsFromOthers($saved_content) ?? $default_shipping;
        $return_policy = $this->getReturnPolicyFromOthers($saved_content) ?? $default_return_policy;
        $price_valid_until = date('Y-m-d', strtotime('+1 year'));

        $variants = [];
        foreach ($query->rows as $row) {
            $variant_price = $this->calculateVariantPrice($base_price, $row['price'], $row['prefix']);
            $variant_full_name = $row['variant_name'] . ' - ' . $product_name;
            $variant_description = $product_name . ' - ' . $row['variant_name'];
            
            $variant = [
                "@type" => "Product",
                "name"  => $variant_full_name,
                "sku"   => $row['sku'] ?: ($main_sku . "-" . $row['product_option_value_id']),
                "image" => $main_image,
                "description" => $variant_description,
                "offers" => [
                    "@type"        => "Offer",
                    "price"        => number_format($variant_price, 2, '.', ''),
                    "priceCurrency" => $that->currency->getCode(),
                    "priceValidUntil" => $price_valid_until,
                    "availability" => $this->getVariantAvailability($row['quantity']),
                    "shippingDetails" => $shipping_details,
                    "hasMerchantReturnPolicy" => $return_policy
                ]
            ];

            if (!empty($brand)) {
                $variant["brand"] = [
                    "@type" => "Brand",
                    "name" => $brand
                ];
            }

            $variants[] = $variant;
        }

        return $variants;
    }

    private function getDefaultShippingDetails($that = null)
    {
        // Si no tenemos acceso al controlador, usar configuraci¨®n desde registry
        if (!$that) {
            $config = $this->registry->get('config');
        } else {
            $config = $that->config;
        }

        return [
            "@type" => "OfferShippingDetails",
            "shippingRate" => [
                "@type" => "MonetaryAmount",
                "value" => $config->get('smart_seo_schema_shipping_rate') ?: "5.99",
                "currency" => $config->get('smart_seo_schema_shipping_currency') ?: "USD"
            ],
            "shippingDestination" => [
                "@type" => "DefinedRegion",
                "addressCountry" => $config->get('smart_seo_schema_shipping_country') ?: "US"
            ],
            "deliveryTime" => [
                "@type" => "ShippingDeliveryTime",
                "handlingTime" => [
                    "@type" => "QuantitativeValue",
                    "minValue" => (int)($config->get('smart_seo_schema_handling_min_days') ?: 1),
                    "maxValue" => (int)($config->get('smart_seo_schema_handling_max_days') ?: 2),
                    "unitCode" => "d"
                ],
                "transitTime" => [
                    "@type" => "QuantitativeValue",
                    "minValue" => (int)($config->get('smart_seo_schema_transit_min_days') ?: 3),
                    "maxValue" => (int)($config->get('smart_seo_schema_transit_max_days') ?: 5),
                    "unitCode" => "d"
                ]
            ]
        ];
    }

    private function getDefaultReturnPolicy($that = null)
    {
        // Si no tenemos acceso al controlador, usar configuraci¨®n desde registry
        if (!$that) {
            $config = $this->registry->get('config');
        } else {
            $config = $that->config;
        }

        return [
            "@type" => "MerchantReturnPolicy",
            "applicableCountry" => $config->get('smart_seo_schema_return_country') ?: "US",
            "returnPolicyCategory" => "https://schema.org/MerchantReturnFiniteReturnWindow",
            "merchantReturnDays" => (int)($config->get('smart_seo_schema_return_days') ?: 30),
            "returnMethod" => $config->get('smart_seo_schema_return_method') ?: "https://schema.org/ReturnByMail",
            "returnFees" => $config->get('smart_seo_schema_return_fees') ?: "https://schema.org/FreeReturn"
        ];
    }

    private function getProductMainImage($that, $product_id)
    {
        $db = $that->db;
        $config = $that->config;
        
        $imageQuery = "SELECT rd.resource_path 
                      FROM " . DB_PREFIX . "resource_map rm 
                      JOIN " . DB_PREFIX . "resource_descriptions rd ON rm.resource_id = rd.resource_id
                      WHERE rm.object_name = 'products' AND rm.object_id = " . (int)$product_id . " AND rm.sort_order = 1
                      LIMIT 1";
        $imageStmt = $db->query($imageQuery);
        
        if ($imageStmt->num_rows) {
            $storeUrl = rtrim($config->get('config_ssl_url') ?: $config->get('config_url'), '/');
            return $storeUrl . '/resources/image/' . $imageStmt->row['resource_path'];
        }
        
        return null;
    }

    private function getProductDescription($that, $product_id)
    {
        $db = $that->db;
        $language_id = $this->getAdminDefaultLanguageId($that);
        $description_mode = $that->config->get('smart_seo_schema_description') ?: 'auto';

        $sql = "
            SELECT description, blurb
            FROM " . DB_PREFIX . "product_descriptions
            WHERE product_id = " . (int)$product_id . " 
            AND language_id = " . (int)$language_id . "
            LIMIT 1
        ";

        $query = $db->query($sql);
        
        if (!$query->num_rows) {
            return '';
        }

        $row = $query->row;

        switch ($description_mode) {
            case 'description':
                return strip_tags($row['description']);
            case 'blurb':
                return strip_tags($row['blurb']);
            case 'auto':
            default:
                if (!empty($row['blurb'])) {
                    return strip_tags($row['blurb']);
                } else {
                    return strip_tags($row['description']);
                }
        }
    }

    private function generateAggregateRating($that, $product_info)
    {
        try {
            $db = $that->db ?? $this->registry->get('db');
            if (!$db) {
                return null;
            }

            $query = $db->query("
                SELECT 
                    COUNT(*) as total_reviews, 
                    AVG(rating) as avg_rating,
                    MAX(rating) as best_rating,
                    MIN(rating) as worst_rating
                FROM " . DB_PREFIX . "reviews 
                WHERE product_id = " . (int)$product_info['product_id'] . " 
                AND status = 1
            ");

            if ($query->num_rows && $query->row['total_reviews'] > 0) {
                $total_reviews = (int)$query->row['total_reviews'];
                $avg_rating = (float)$query->row['avg_rating'];
                $best_rating = (int)$query->row['best_rating'];
                $worst_rating = (int)$query->row['worst_rating'];

                return [
                    "@type"      => "AggregateRating",
                    "ratingValue" => number_format($avg_rating, 1),
                    "reviewCount" => $total_reviews,
                    "bestRating"  => (string)$best_rating,
                    "worstRating" => (string)$worst_rating
                ];
            }
        } catch (Exception $e) {
            if ($this->registry->get('config')->get('smart_seo_schema_debug_mode')) {
                $warning = new AWarning('Smart SEO Schema - Error generating aggregate rating: ' . $e->getMessage());
                $warning->toLog();
            }
        }

        return null;
    }

    private function getProductOffer($that, $product_info, $saved_content = null, $variants = [])
    {
        if (!$that->config->get('smart_seo_schema_show_offer')) {
            return null;
        }

        $base_price = (float)$product_info['price'];
        $currency = $that->currency->getCode();
        $availability = $this->getProductAvailability($that);

        if ($base_price == 0 && !empty($variants)) {
            $variant_prices = [];
            foreach ($variants as $variant) {
                if (isset($variant['offers']['price'])) {
                    $variant_prices[] = (float)$variant['offers']['price'];
                }
            }
            if (!empty($variant_prices)) {
                $base_price = min($variant_prices);
            }
        }

        $shipping_details = $this->getShippingDetailsFromOthers($saved_content) ?? $this->getDefaultShippingDetails($that);
        $return_policy = $this->getReturnPolicyFromOthers($saved_content) ?? $this->getDefaultReturnPolicy($that);
        $price_valid_until = date('Y-m-d', strtotime('+1 year'));

        $offer = [
            "@type"        => "Offer",
            "price"        => number_format($base_price, 2, '.', ''),
            "priceCurrency" => $currency,
            "priceValidUntil" => $price_valid_until,
            "availability" => $availability,
            "shippingDetails" => $shipping_details,
            "hasMerchantReturnPolicy" => $return_policy
        ];

        if (!empty($variants)) {
            $variant_prices = [];
            foreach ($variants as $variant) {
                if (isset($variant['offers']['price'])) {
                    $variant_prices[] = (float)$variant['offers']['price'];
                }
            }
            
            if (!empty($variant_prices) && count($variant_prices) > 1) {
                $max_price = max($variant_prices);
                if ($max_price > $base_price) {
                    $offer["highPrice"] = number_format($max_price, 2, '.', '');
                }
            }
        }

        return $offer;
    }

    private function getProductAvailability($that)
    {
        $stockStatuses = [
            'InStock'             => 'https://schema.org/InStock',
            'OutOfStock'          => 'https://schema.org/OutOfStock',
            'Discontinued'        => 'https://schema.org/Discontinued',
            'LimitedAvailability' => 'https://schema.org/LimitedAvailability',
            'Pre-Order'           => 'https://schema.org/PreOrder',
            'Pre-Sale'            => 'https://schema.org/PreSale'
        ];

        $stock = isset($that->data['stock']) ? $that->data['stock'] : '';

        if (preg_match("/".$that->language->get('text_instock')."/i", $stock)) {
            return $stockStatuses['InStock'];
        } elseif (preg_match("/".$that->language->get('text_out_of_stock')."/i", $stock)) {
            return $stockStatuses['OutOfStock'];
        } elseif (preg_match("/discontinued/i", $stock)) {
            return $stockStatuses['Discontinued'];
        } elseif (preg_match("/limited/i", $stock)) {
            return $stockStatuses['LimitedAvailability'];
        } elseif (preg_match("/Pre[\s-]*Order/i", $stock)) {
            return $stockStatuses['Pre-Order'];
        } elseif (preg_match("/Pre[\s-]*Sale/i", $stock)) {
            return $stockStatuses['Pre-Sale'];
        }

        return $stockStatuses['InStock'];
    }

    private function generateAdditionalSchemas($product_info, $that, $saved_content = null)
    {
        $schemas = [];
        $config = $that->config;

        if (($config->get('smart_seo_schema_enable_faq_schema') && !$saved_content) ||
            ($saved_content && $saved_content['enable_faq'] && !empty($saved_content['faq_content']))) {
            $faq = $this->generateFAQSchema($product_info, $saved_content);
            if ($faq) {
                $schemas[] = $faq;
            }
        }

        if (($config->get('smart_seo_schema_enable_howto_schema') && !$saved_content) ||
            ($saved_content && $saved_content['enable_howto'] && !empty($saved_content['howto_content']))) {
            $howto = $this->generateHowToSchema($product_info, $saved_content);
            if ($howto) {
                $schemas[] = $howto;
            }
        }

        if (($config->get('smart_seo_schema_enable_review_schema') && !$saved_content) ||
            ($saved_content && $saved_content['enable_review'] && !empty($saved_content['review_content']))) {
            $review = $this->generateReviewSchema($product_info, $saved_content);
            if ($review) {
                $schemas[] = $review;
            }
        }

        if ($config->get('smart_seo_schema_enable_organization')) {
            $organization = $this->generateOrganizationSchema($that);
            if ($organization) {
                $schemas[] = $organization;
            }
        }

        return $schemas;
    }

    private function generateFAQSchema($product_info, $saved_content = null)
    {
        if ($saved_content && !empty($saved_content['faq_content'])) {
            return $this->parseFAQContent($saved_content['faq_content']);
        }

        return [
            "@type" => "FAQPage",
            "mainEntity" => [
                [
                    "@type" => "Question",
                    "name" => "What is " . $product_info['name'] . "?",
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => "This is a high-quality product designed to meet your needs."
                    ]
                ]
            ]
        ];
    }

    private function parseFAQContent($faq_content)
    {
        $lines = explode("\n", trim($faq_content));
        $questions = [];
        
        $current_question = null;
        $current_answer = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            if (substr($line, -1) === '?') {
                if ($current_question && !empty($current_answer)) {
                    $questions[] = [
                        "@type" => "Question",
                        "name" => $current_question,
                        "acceptedAnswer" => [
                            "@type" => "Answer",
                            "text" => implode(' ', $current_answer)
                        ]
                    ];
                }
                
                $current_question = $line;
                $current_answer = [];
            } else {
                $current_answer[] = $line;
            }
        }
        
        if ($current_question && !empty($current_answer)) {
            $questions[] = [
                "@type" => "Question",
                "name" => $current_question,
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => implode(' ', $current_answer)
                ]
            ];
        }
        
        if (empty($questions)) {
            return null;
        }
        
        return [
            "@type" => "FAQPage",
            "mainEntity" => $questions
        ];
    }

    private function generateHowToSchema($product_info, $saved_content = null)
    {
        if ($saved_content && !empty($saved_content['howto_content'])) {
            return $this->parseHowToContent($saved_content['howto_content'], $product_info['name']);
        }

        return [
            "@type" => "HowTo",
            "name" => "How to use " . $product_info['name'],
            "step" => [
                [
                    "@type" => "HowToStep",
                    "name" => "Step 1",
                    "text" => "Follow the included instructions."
                ]
            ]
        ];
    }

    private function parseHowToContent($howto_content, $product_name)
    {
        $lines = explode("\n", trim($howto_content));
        $steps = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            if (preg_match('/^(\d+)\.\s*(.+)/', $line, $matches)) {
                $steps[] = [
                    "@type" => "HowToStep",
                    "name" => "Step " . $matches[1],
                    "text" => trim($matches[2])
                ];
            } elseif (!empty($steps)) {
                $last_key = count($steps) - 1;
                $steps[$last_key]['text'] .= ' ' . $line;
            }
        }
        
        if (empty($steps)) {
            return null;
        }
        
        return [
            "@type" => "HowTo",
            "name" => "How to use " . $product_name,
            "step" => $steps
        ];
    }

    private function generateReviewSchema($product_info, $saved_content = null)
    {
        if ($saved_content && !empty($saved_content['review_content'])) {
            return $this->parseReviewContent($saved_content['review_content'], $product_info['name']);
        }

        return [
            "@type" => "Review",
            "author" => [
                "@type" => "Person",
                "name" => "Verified Customer"
            ],
            "reviewRating" => [
                "@type" => "Rating",
                "ratingValue" => "5",
                "bestRating" => "5"
            ],
            "reviewBody" => "Excellent product, highly recommended!"
        ];
    }

    private function parseReviewContent($review_content, $product_name)
    {
        return [
            "@type" => "Review",
            "author" => [
                "@type" => "Person",
                "name" => "Verified Customer"
            ],
            "reviewRating" => [
                "@type" => "Rating",
                "ratingValue" => "5",
                "bestRating" => "5"
            ],
            "reviewBody" => trim($review_content)
        ];
    }

    private function generateOrganizationSchema($that)
    {
        $config = $that->config;
        $db = $that->db;
        
        $org_name = null;
        $query = $db->query("
            SELECT value 
            FROM " . DB_PREFIX . "settings 
            WHERE `key` = 'config_owner' 
            AND store_id = 0 
            LIMIT 1
        ");
        
        if ($query->num_rows) {
            $org_name = trim($query->row['value']);
        }
        
        if (empty($org_name)) {
            $org_name = $config->get('config_name');
        }
        
        if (empty($org_name)) {
            $org_name = 'Online Store';
        }
        
        return [
            "@type" => "Organization",
            "name" => $org_name,
            "url" => $config->get('config_url')
        ];
    }

    private function getProductImages($that, $product_info)
    {
        $db = $that->db;
        $config = $that->config;
        $product_id = $product_info['product_id'];
        
        $imageQuery = "SELECT rd.resource_path 
                      FROM " . DB_PREFIX . "resource_map rm 
                      JOIN " . DB_PREFIX . "resource_descriptions rd ON rm.resource_id = rd.resource_id
                      WHERE rm.object_name = 'products' AND rm.object_id = " . (int)$product_id . " AND rm.sort_order = 1
                      LIMIT 1";
        $imageStmt = $db->query($imageQuery);
        $mainImage = $imageStmt->num_rows ? $imageStmt->row['resource_path'] : null;
        
        if ($mainImage) {
            $storeUrl = rtrim($config->get('config_ssl_url') ?: $config->get('config_url'), '/');
            $imageUrl = $storeUrl . '/resources/image/' . $mainImage;
            
            if ($config->get('smart_seo_schema_show_image')) {
                $additionalImagesQuery = "SELECT rd.resource_path 
                                         FROM " . DB_PREFIX . "resource_map rm 
                                         JOIN " . DB_PREFIX . "resource_descriptions rd ON rm.resource_id = rd.resource_id
                                         WHERE rm.object_name = 'products' 
                                         AND rm.object_id = " . (int)$product_id . " 
                                         AND rd.resource_path != '" . $db->escape($mainImage) . "'
                                         ORDER BY rm.sort_order 
                                         LIMIT 4";
                $additionalStmt = $db->query($additionalImagesQuery);
                
                $images = [$imageUrl];
                
                if ($additionalStmt->num_rows) {
                    foreach ($additionalStmt->rows as $row) {
                        $images[] = $storeUrl . '/resources/image/' . $row['resource_path'];
                    }
                }
                
                return $images;
            }
            
            return [$imageUrl];
        }
        
        return [];
    }

    private function getShippingDetailsFromOthers($saved_content)
    {
        if (!$saved_content || empty($saved_content['others_content'])) {
            return null;
        }
        
        $others_data = json_decode($saved_content['others_content'], true);
        if (is_array($others_data) && isset($others_data['shippingDetails'])) {
            return $others_data['shippingDetails'];
        }
        
        return null;
    }

    private function getReturnPolicyFromOthers($saved_content)
    {
        if (!$saved_content || empty($saved_content['others_content'])) {
            return null;
        }
        
        $others_data = json_decode($saved_content['others_content'], true);
        if (is_array($others_data) && isset($others_data['hasMerchantReturnPolicy'])) {
            return $others_data['hasMerchantReturnPolicy'];
        }
        
        return null;
    }

    private function calculateVariantPrice($basePrice, $variantPrice, $prefix)
    {
        switch ($prefix) {
            case '%':
                return $basePrice * (1 + $variantPrice);
            case '$':
                return $basePrice + $variantPrice;
            case '+':
                return $basePrice + $variantPrice;
            case '-':
                return $basePrice - $variantPrice;
            default:
                return $basePrice;
        }
    }

    private function getVariantAvailability($quantity)
    {
        if ($quantity > 0) {
            return "https://schema.org/InStock";
        } else {
            return "https://schema.org/OutOfStock";
        }
    }

    private function getAdminDefaultLanguageId($that)
    {
        $db = $that->db;
        $config = $that->config;

        $query = $db->query("
            SELECT l.language_id 
            FROM " . DB_PREFIX . "languages l 
            WHERE l.status = 1 
            ORDER BY l.sort_order ASC 
            LIMIT 1
        ");

        if ($query->num_rows) {
            return $query->row['language_id'];
        }

        return 1;
    }
}