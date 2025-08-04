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

    /**
     * Hook para agregar tab en admin de productos (siguiendo patrón AvaTax)
     */
    public function onControllerPagesCatalogProductTabs_InitData()
    {
        /** @var ControllerPagesCatalogProductTabs $that */
        $that = &$this->baseObject;
        $that->loadLanguage('smart_seo_schema/smart_seo_schema');

        $this->data = [];
        $this->data['tabs'][] = [
            'href'   => $that->html->getSecureURL(
                'catalog/smart_seo_schema',
                '&product_id=' . $that->request->get['product_id']
            ),
            'text'   => $that->language->get('smart_seo_schema_name'),
            'active' => ($that->data['active'] == 'smart_seo_schema'),
        ];

        $view = new AView(Registry::getInstance(), 0);
        $view->batchAssign($this->data);
        $that->view->addHookVar('extension_tabs', $view->fetch('pages/smart_seo_schema/tabs.tpl'));
    }

    /**
     * Hook principal para generar Schema.org en frontend
     */
    public function onControllerPagesProductProduct_UpdateData()
    {
        /** @var ControllerPagesProductProduct $that */
        $that = $this->baseObject;
        
        if (!$that->config->get('smart_seo_schema_status')) {
            return;
        }

        // Generar Schema.org completo con variantes y IA
        $schema_data = $this->generateCompleteSchema($that);
        
        $that->load->library('json');
        $that->view->assign('smart_seo_schema_data', AJson::encode($schema_data));
    }

    /**
     * Genera Schema.org completo con hasVariant[], IA y esquemas adicionales
     */
    private function generateCompleteSchema($that)
    {
        $product_info = $that->data['product_info'];
        
        // Schema.org Product base
        $product_snippet = [
            "@context" => "https://schema.org",
            "@type"    => "Product",
            "name"     => $product_info['name'],
        ];

        // Descripción (con IA opcional)
        $description = $this->getOptimizedDescription($that, $product_info);
        if ($description) {
            $product_snippet["description"] = $description;
        }

        // Imagen
        if ($that->config->get('smart_seo_schema_show_image') && isset($that->data['image_main']['thumb_url'])) {
            $product_snippet["image"] = $that->data['image_main']['thumb_url'];
        }

        // MPN y Brand
        if ($product_info['model']) {
            $product_snippet["mpn"] = $product_info['model'];
        }

        if ($product_info['manufacturer']) {
            $product_snippet["brand"] = [
                "@type" => "Brand",
                "name"  => $product_info['manufacturer']
            ];
        }

        // SKU principal
        if ($that->config->get('smart_seo_schema_show_sku') && $product_info['sku']) {
            $product_snippet["sku"] = $product_info['sku'];
        }

        // Aggregated Rating
        if ($that->config->get('smart_seo_schema_show_review')) {
            $rating = $this->getProductRating($that, $product_info);
            if ($rating) {
                $product_snippet["aggregateRating"] = $rating;
            }
        }

        // hasVariant[] - Sistema nativo de AbanteCart
        if ($that->config->get('smart_seo_schema_enable_variants')) {
            $variants = $this->getProductVariants($product_info['product_id'], $that);
            if (!empty($variants)) {
                $product_snippet["hasVariant"] = $variants;
            }
        }

        // Offer principal (si no hay variantes o como fallback)
        if ($that->config->get('smart_seo_schema_show_offer')) {
            $offer = $this->getProductOffer($that, $product_info);
            if ($offer && empty($product_snippet["hasVariant"])) {
                $product_snippet["offers"] = $offer;
            }
        }

        // Esquemas adicionales con IA
        $additional_schemas = $this->generateAdditionalSchemas($product_info, $that);
        
        return array_merge([$product_snippet], $additional_schemas);
    }

    /**
     * Obtiene variantes desde tablas nativas de AbanteCart
     */
    private function getProductVariants($product_id, $that)
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

        // Obtener precio base del producto
        $base_price_query = $db->query("
            SELECT price 
            FROM " . DB_PREFIX . "products 
            WHERE product_id = " . (int)$product_id
        );
        $base_price = $base_price_query->row['price'] ?? 0;

        $variants = [];
        foreach ($query->rows as $row) {
            $variant_price = $this->calculateVariantPrice($base_price, $row['price'], $row['prefix']);
            
            $variant = [
                "@type" => "Product",
                "name"  => $row['variant_name'],
                "sku"   => $row['sku'] ?: "VAR-" . $row['product_option_value_id'],
                "offers" => [
                    "@type"        => "Offer",
                    "price"        => number_format($variant_price, 2, '.', ''),
                    "priceCurrency" => $that->currency->getCode(),
                    "availability" => $this->getVariantAvailability($row['quantity']),
                ]
            ];

            $variants[] = $variant;
        }

        return $variants;
    }

    /**
     * Calcula precio de variante según prefix (+/-)
     */
    private function calculateVariantPrice($basePrice, $variantPrice, $prefix)
    {
        switch ($prefix) {
            case '+':
                return $basePrice + $variantPrice;
            case '-':
                return $basePrice - $variantPrice;
            default:
                return $basePrice;
        }
    }

    /**
     * Determina disponibilidad de variante
     */
    private function getVariantAvailability($quantity)
    {
        if ($quantity > 0) {
            return "https://schema.org/InStock";
        } else {
            return "https://schema.org/OutOfStock";
        }
    }

    /**
     * Obtiene idioma por defecto del admin
     */
    private function getAdminDefaultLanguageId($that)
    {
        $db = $that->db;
        $config = $that->config;

        $query = $db->query("
            SELECT l.language_id 
            FROM " . DB_PREFIX . "settings s
            INNER JOIN " . DB_PREFIX . "languages l ON s.value = l.code
            WHERE s.key = 'admin_language'
            LIMIT 1
        ");
        
        if ($query->num_rows) {
            return (int)$query->row['language_id'];
        }
        
        // Fallback al idioma por defecto de configuración
        return (int)$config->get('config_language_id');
    }

    /**
     * Descripción optimizada con IA opcional
     */
    private function getOptimizedDescription($that, $product_info)
    {
        $description = '';

        // Lógica original de descripción
        if ($that->config->get('smart_seo_schema_description') == 'auto') {
            if ($product_info['blurb']) {
                $description = strip_tags(html_entity_decode($product_info['blurb']));
            } else {
                $description = strip_tags(html_entity_decode($product_info['description']));
            }
        } elseif ($that->config->get('smart_seo_schema_description') == 'blurb') {
            $description = strip_tags(html_entity_decode($product_info['blurb']));
        } elseif ($that->config->get('smart_seo_schema_description') == 'description') {
            $description = strip_tags(html_entity_decode($product_info['description']));
        }

        // Mejora con IA si está habilitada
        if ($that->config->get('smart_seo_schema_ai_auto_generate')) {
            $ai_description = $this->generateAIDescription($product_info, $description, $that);
            if ($ai_description) {
                $description = $ai_description;
            }
        }

        return $description;
    }

    /**
     * Genera descripción mejorada con Groq IA
     */
    private function generateAIDescription($product_info, $original_description, $that)
    {
        $api_key = $that->config->get('smart_seo_schema_groq_api_key');
        $model = $that->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant';

        if (!$api_key) {
            return null;
        }

        $prompt = "Improve this product description for SEO and Schema.org structured data. Make it concise but informative, focusing on key features and benefits. Original: " . $original_description . " Product name: " . $product_info['name'];

        try {
            $response = $this->callGroqAPI($api_key, $model, $prompt, $that);
            return $response ?: $original_description;
        } catch (Exception $e) {
            // Log error y usar descripción original
            return $original_description;
        }
    }

    /**
     * Cliente API para Groq
     */
    private function callGroqAPI($api_key, $model, $prompt, $that)
    {
        $url = 'https://api.groq.com/openai/v1/chat/completions';
        
        $data = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 200,
            'temperature' => 0.7
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200 && $response) {
            $decoded = json_decode($response, true);
            return $decoded['choices'][0]['message']['content'] ?? null;
        }

        return null;
    }

    /**
     * Obtiene rating del producto
     */
    private function getProductRating($that, $product_info)
    {
        $rating = $that->data['average'] ?? $product_info['rating'] ?? null;
        
        if (!$rating) {
            return null;
        }

        $total_reviews = $that->model_catalog_review->getTotalReviewsByProductId($product_info['product_id']);

        return [
            "@type"       => "AggregateRating",
            "ratingValue" => $rating,
            "reviewCount" => $total_reviews,
        ];
    }

    /**
     * Genera offer principal
     */
    private function getProductOffer($that, $product_info)
    {
        $price = $product_info['final_price'] > 0.00 ? $product_info['final_price'] : $product_info['price'];
        
        if ($price <= 0.00) {
            return null;
        }

        $stockStatuses = [
            'Discontinued'        => 'Discontinued',
            'InStock'             => 'InStock',
            'InStoreOnly'         => 'InStoreOnly',
            'LimitedAvailability' => 'LimitedAvailability',
            'OnlineOnly'          => 'OnlineOnly',
            'OutOfStock'          => 'OutOfStock',
            'Pre-Order'           => 'PreOrder',
            'Pre-Sale'            => 'PreSale',
            'SoldOut'             => 'SoldOut',
        ];

        $stockStatus = $this->determineStockStatus($that, $stockStatuses);
        $priceValidUntil = new DateTime();

        $offer = [
            "@type"           => "Offer",
            "price"           => number_format($price, 2, '.', ''),
            "priceCurrency"   => $that->currency->getCode(),
            "priceValidUntil" => $priceValidUntil->format('c'),
            "url"             => $that->data['product_review_url'] ?? '',
        ];

        if ($that->config->get('smart_seo_schema_show_availability')) {
            $offer["availability"] = "https://schema.org/" . $stockStatus;
        }

        return $offer;
    }

    /**
     * Determina estado de stock
     */
    private function determineStockStatus($that, $stockStatuses)
    {
        $stock = $that->data['stock'] ?? '';

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

        return $stockStatuses['InStock']; // Default
    }

    /**
     * Genera esquemas adicionales (FAQ, HowTo, Review, Organization)
     */
    private function generateAdditionalSchemas($product_info, $that)
    {
        $schemas = [];
        $config = $that->config;

        // FAQPage Schema
        if ($config->get('smart_seo_schema_enable_faq_schema')) {
            $faq = $this->generateFAQSchema($product_info);
            if ($faq) {
                $schemas[] = $faq;
            }
        }

        // HowTo Schema
        if ($config->get('smart_seo_schema_enable_howto_schema')) {
            $howto = $this->generateHowToSchema($product_info);
            if ($howto) {
                $schemas[] = $howto;
            }
        }

        // Review Schema
        if ($config->get('smart_seo_schema_enable_review_schema')) {
            $review = $this->generateReviewSchema($product_info);
            if ($review) {
                $schemas[] = $review;
            }
        }

        // Organization Schema
        if ($config->get('smart_seo_schema_enable_organization')) {
            $organization = $this->generateOrganizationSchema($that);
            if ($organization) {
                $schemas[] = $organization;
            }
        }

        return $schemas;
    }

    /**
     * Genera FAQ Schema con IA
     */
    private function generateFAQSchema($product_info)
    {
        // Implementación básica - expandir con IA
        return [
            "@type" => "FAQPage",
            "mainEntity" => [
                [
                    "@type" => "Question",
                    "name" => "What is " . $product_info['name'] . "?",
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => "This is a high-quality " . $product_info['name'] . " designed for professional use."
                    ]
                ]
            ]
        ];
    }

    /**
     * Genera HowTo Schema con IA
     */
    private function generateHowToSchema($product_info)
    {
        // Implementación básica - expandir con IA
        return [
            "@type" => "HowTo",
            "name" => "How to use " . $product_info['name'],
            "step" => [
                [
                    "@type" => "HowToStep",
                    "text" => "Follow the manufacturer instructions for proper usage."
                ]
            ]
        ];
    }

    /**
     * Genera Review Schema
     */
    private function generateReviewSchema($product_info)
    {
        return [
            "@type" => "Review",
            "reviewRating" => [
                "@type" => "Rating",
                "ratingValue" => "5",
                "bestRating" => "5"
            ],
            "author" => [
                "@type" => "Person",
                "name" => "Technical Review"
            ]
        ];
    }

    /**
     * Genera Organization Schema
     */
    private function generateOrganizationSchema($that)
    {
        $config = $that->config;
        
        return [
            "@type" => "Organization",
            "name" => $config->get('config_name'),
            "url" => $config->get('config_url')
        ];
    }

    /**
     * Método público para generar Schema completo (usado desde admin controller)
     */
    public function generateCompleteSchemaForProduct($product_info)
    {
        // Mock object para compatibilidad con admin
        $mock_that = new stdClass();
        $mock_that->config = Registry::getInstance()->get('config');
        $mock_that->db = Registry::getInstance()->get('db');
        $mock_that->currency = Registry::getInstance()->get('currency');
        $mock_that->language = Registry::getInstance()->get('language');
        $mock_that->data = ['product_info' => $product_info];
        
        return $this->generateCompleteSchema($mock_that);
    }
}