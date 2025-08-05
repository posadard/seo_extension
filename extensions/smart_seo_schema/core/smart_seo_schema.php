<?php

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

/**
 * Smart SEO Schema Assistant - Core Extension Class OPTIMIZADO CON DIVISIÓN DE TOKENS
 * 
 * Generates Schema.org structured data for AbanteCart products
 * Includes AI-powered content generation with token optimization
 * VERSIÓN CON PERSISTENCIA DE DATOS Y CAMPOS ADICIONALES CORREGIDOS
 * 
 * @version 2.0.2
 */
class ExtensionSmartSeoSchema extends Extension
{
    protected $registry;

    public function __construct()
    {
        $this->registry = Registry::getInstance();
    }

    /**
     * Hook para tabs en admin cuando se edita un producto
     */
    public function onControllerPagesCatalogProductTabs_InitData()
    {
        /** @var ControllerPagesCatalogProduct $that */
        $that = $this->baseObject;
        
        if (!$that->config->get('smart_seo_schema_status')) {
            return;
        }

        // Cargar archivo de idioma para asegurar que el texto del tab aparezca correctamente
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

    /**
     * Hook para tab de product tabs en admin
     */
    public function onControllerPagesCatalogProductTabs_UpdateData()
    {
        /** @var ControllerPagesCatalogProduct $that */
        $that = $this->baseObject;
        
        if (!$that->config->get('smart_seo_schema_status')) {
            return;
        }

        // Cargar archivo de idioma para asegurar que el texto del tab aparezca correctamente
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

    /**
     * Hook principal para generar Schema.org en frontend - CON PERSISTENCIA
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
     * Genera Schema.org completo PARA EL PREVIEW ADMIN
     */
    public function generateCompleteSchemaForProduct($product_info)
    {
        // Cargar registry y dependencias necesarias
        $registry = Registry::getInstance();
        $db = $registry->get('db');
        $config = $registry->get('config');
        
        // Crear un objeto mock del controlador con las propiedades necesarias
        $mock_controller = new stdClass();
        $mock_controller->config = $config;
        $mock_controller->db = $db;
        $mock_controller->currency = $registry->get('currency');
        $mock_controller->language = $registry->get('language');
        
        // Mock de load->library para AJson
        $mock_controller->load = new stdClass();
        $mock_controller->load->library = function($library) {
            if ($library === 'json') {
                return true;
            }
        };
        
        // Mock de model_catalog_review si no existe
        $mock_controller->model_catalog_review = new stdClass();
        $mock_controller->model_catalog_review->getTotalReviewsByProductId = function($product_id) {
            return 0;
        };
        
        // Datos del producto mock si no se pasan todos
        $mock_controller->data = [
            'product_info' => $product_info,
            'average' => $product_info['rating'] ?? 0,
            'stock' => 'In Stock',
            'product_review_url' => ''
        ];
        
        // Generar schema usando el método principal
        return $this->generateCompleteSchema($mock_controller);
    }

    /**
     * Genera Schema.org completo con persistencia y IA - CORE METHOD
     */
    private function generateCompleteSchema($that)
    {
        $product_info = $that->data['product_info'];
        $product_id = $product_info['product_id'];

        // Cargar contenido guardado desde base de datos
        $saved_content = $this->getSavedSchemaContent($product_id);

        // Generar snippet principal del producto
        $product_snippet = $this->generateProductSnippet($product_info, $that, $saved_content);

        // Generar variantes si están habilitadas y existen
        if ($that->config->get('smart_seo_schema_enable_variants') && 
            (!empty($saved_content['enable_variants']) || $saved_content === null)) {
            $variants = $this->getProductVariants($product_info['product_id'], $that, $saved_content);
            if (!empty($variants)) {
                $product_snippet["hasVariant"] = $variants;
            }
        }

        // Offer principal (si no hay variantes o como fallback)
        if ($that->config->get('smart_seo_schema_show_offer')) {
            $offer = $this->getProductOffer($that, $product_info, $saved_content);
            if ($offer && empty($product_snippet["hasVariant"])) {
                $product_snippet["offers"] = $offer;
            }
        }

        // APLICAR OTHERS_CONTENT - Propiedades adicionales desde JSON
        if ($saved_content && !empty($saved_content['others_content'])) {
            $others_data = json_decode($saved_content['others_content'], true);
            if (is_array($others_data)) {
                foreach ($others_data as $key => $value) {
                    // Solo agregar si no existe ya en el schema principal
                    if (!isset($product_snippet[$key]) && !empty($value)) {
                        $product_snippet[$key] = $value;
                    }
                }
            }
        }

        // Esquemas adicionales con IA (USAR CONTENIDO GUARDADO)
        $additional_schemas = $this->generateAdditionalSchemas($product_info, $that, $saved_content);
        
        return array_merge([$product_snippet], $additional_schemas);
    }

    /**
     * Cargar contenido Schema guardado de la base de datos - INCLUYE OTHERS_CONTENT
     */
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
                    enable_review
                FROM " . DB_PREFIX . "seo_schema_content 
                WHERE product_id = " . (int)$product_id . "
                LIMIT 1
            ");

            if ($query->num_rows) {
                $content = $query->row;
                
                // Convertir flags a boolean
                $content['enable_variants'] = (bool)$content['enable_variants'];
                $content['enable_faq'] = (bool)$content['enable_faq'];
                $content['enable_howto'] = (bool)$content['enable_howto'];
                $content['enable_review'] = (bool)$content['enable_review'];
                
                return $content;
            }
        } catch (Exception $e) {
            // En caso de error, continuar sin contenido guardado
            if ($this->registry->get('config')->get('smart_seo_schema_debug_mode')) {
                $warning = new AWarning('Smart SEO Schema - Error loading saved content: ' . $e->getMessage());
                $warning->toLog();
            }
        }
        
        return null; // No hay contenido guardado
    }

    /**
     * Genera el snippet principal del producto con Schema.org
     */
    private function generateProductSnippet($product_info, $that, $saved_content = null)
    {
        $product_snippet = [
            "@context" => "https://schema.org",
            "@type"    => "Product"
        ];

        // Nombre del producto
        $product_snippet["name"] = $product_info['name'];

        // Descripción (usar custom si existe)
        if ($saved_content && !empty($saved_content['custom_description'])) {
            $description = trim($saved_content['custom_description']);
        } else {
            $description = $this->getProductDescription($that, $product_info['product_id']);
        }
        
        if (!empty($description)) {
            $product_snippet["description"] = $description;
        }

        // Imagen del producto
        if ($that->config->get('smart_seo_schema_show_image')) {
            $images = $this->getProductImages($that, $product_info);
            if (!empty($images)) {
                $product_snippet["image"] = count($images) === 1 ? $images[0] : $images;
            }
        }

        // SKU y MPN
        if ($that->config->get('smart_seo_schema_show_sku') && !empty($product_info['sku'])) {
            $product_snippet["sku"] = $product_info['sku'];
        }

        if (!empty($product_info['model'])) {
            $product_snippet["mpn"] = $product_info['model'];
        }

        // Reseñas y calificaciones
        if ($that->config->get('smart_seo_schema_show_review')) {
            $aggregateRating = $this->getAggregateRating($that, $product_info);
            if ($aggregateRating) {
                $product_snippet["aggregateRating"] = $aggregateRating;
            }
        }

        return $product_snippet;
    }

    /**
     * Obtiene variantes desde tablas nativas de AbanteCart con formato mejorado
     */
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

        // Obtener datos base del producto
        $base_product_query = $db->query("
            SELECT price, sku, name 
            FROM " . DB_PREFIX . "products p
            LEFT JOIN " . DB_PREFIX . "product_descriptions pd ON p.product_id = pd.product_id 
            WHERE p.product_id = " . (int)$product_id . " AND pd.language_id = " . (int)$language_id . "
            LIMIT 1
        ");
        $base_price = $base_product_query->row['price'] ?? 0;
        $main_sku = $base_product_query->row['sku'] ?? '';
        $product_name = $base_product_query->row['name'] ?? '';

        // Obtener imagen principal
        $main_image = $this->getProductMainImage($that, $product_id);
        
        // Obtener valores por defecto para shipping y return policy
        $default_shipping = $this->getDefaultShippingDetails();
        $default_return_policy = $this->getDefaultReturnPolicy();
        
        // Si hay contenido guardado, intentar extraer desde others_content
        $shipping_details = $this->getShippingDetailsFromOthers($saved_content) ?? $default_shipping;
        $return_policy = $this->getReturnPolicyFromOthers($saved_content) ?? $default_return_policy;

        $variants = [];
        foreach ($query->rows as $row) {
            $variant_price = $this->calculateVariantPrice($base_price, $row['price'], $row['prefix']);
            
            // Formato mejorado para el nombre de la variante: "1 Pack - Methylene Blue Powder (25gr)"
            $variant_full_name = $row['variant_name'] . ' - ' . $product_name;
            
            // Descripción concisa: "Methylene Blue Powder (25gr) - 1 Pack"
            $variant_description = $product_name . ' - ' . $row['variant_name'];
            
            $variant = [
                "@type" => "Product",
                "name"  => $variant_full_name,
                "sku"   => $row['sku'] ?: ($main_sku . "-" . $row['product_option_value_id']),
                "image" => $main_image,
                "description" => $variant_description,
                "productGroupID" => $main_sku,
                "offers" => [
                    "@type"        => "Offer",
                    "price"        => number_format($variant_price, 2, '.', ''),
                    "priceCurrency" => $that->currency->getCode(),
                    "availability" => $this->getVariantAvailability($row['quantity']),
                    "shippingDetails" => $shipping_details,
                    "hasMerchantReturnPolicy" => $return_policy
                ]
            ];

            $variants[] = $variant;
        }

        return $variants;
    }

    /**
     * Obtiene valores por defecto para shippingDetails
     */
    private function getDefaultShippingDetails()
    {
        return [
            "@type" => "OfferShippingDetails",
            "shippingRate" => [
                "@type" => "MonetaryAmount",
                "value" => "5.99",
                "currency" => "USD"
            ],
            "shippingDestination" => [
                "@type" => "DefinedRegion",
                "addressCountry" => "US"
            ],
            "deliveryTime" => [
                "@type" => "ShippingDeliveryTime",
                "handlingTime" => [
                    "@type" => "QuantitativeValue",
                    "minValue" => 1,
                    "maxValue" => 2,
                    "unitCode" => "d"
                ],
                "transitTime" => [
                    "@type" => "QuantitativeValue",
                    "minValue" => 3,
                    "maxValue" => 5,
                    "unitCode" => "d"
                ]
            ]
        ];
    }

    /**
     * Obtiene valores por defecto para hasMerchantReturnPolicy
     */
    private function getDefaultReturnPolicy()
    {
        return [
            "@type" => "MerchantReturnPolicy",
            "applicableCountry" => "US",
            "returnPolicyCategory" => "https://schema.org/MerchantReturnFiniteReturnWindow",
            "merchantReturnDays" => 30,
            "returnMethod" => "https://schema.org/ReturnByMail",
            "returnFees" => "https://schema.org/FreeReturn"
        ];
    }

    /**
     * Obtiene la imagen principal del producto
     */
    private function getProductMainImage($that, $product_id)
    {
        $db = $that->db;
        $config = $that->config;
        
        // Obtener primera imagen del producto
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

    /**
     * Obtiene descripción del producto según configuración
     */
    private function getProductDescription($that, $product_id)
    {
        $db = $that->db;
        $language_id = $this->getAdminDefaultLanguageId($that);
        $description_source = $that->config->get('smart_seo_schema_description');

        switch ($description_source) {
            case 'description':
                $field = 'description';
                break;
            case 'blurb':
                $field = 'blurb';
                break;
            default: // 'auto'
                $field = 'blurb';
                break;
        }

        $query = $db->query("
            SELECT " . $field . " as description
            FROM " . DB_PREFIX . "product_descriptions 
            WHERE product_id = " . (int)$product_id . " 
            AND language_id = " . (int)$language_id . "
            LIMIT 1
        ");

        if ($query->num_rows && !empty(trim($query->row['description']))) {
            return strip_tags($query->row['description']);
        }

        // Fallback a description si blurb está vacío
        if ($field === 'blurb') {
            $fallback_query = $db->query("
                SELECT description
                FROM " . DB_PREFIX . "product_descriptions 
                WHERE product_id = " . (int)$product_id . " 
                AND language_id = " . (int)$language_id . "
                LIMIT 1
            ");
            
            if ($fallback_query->num_rows) {
                return strip_tags($fallback_query->row['description']);
            }
        }

        return '';
    }

    /**
     * Obtiene calificación agregada del producto
     */
    private function getAggregateRating($that, $product_info)
    {
        $rating = $that->data['average'] ?? null;
        
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
     * Genera offer principal con campos mejorados para IA y campos requeridos
     */
    private function getProductOffer($that, $product_info, $saved_content = null)
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
        $priceValidUntil->add(new DateInterval('P1Y')); // Valid for 1 year

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

        // Agregar shippingDetails desde others_content o default
        $shipping_details = $this->getShippingDetailsFromOthers($saved_content);
        if (!$shipping_details) {
            $shipping_details = $this->getDefaultShippingDetails();
        }
        $offer["shippingDetails"] = $shipping_details;

        // Agregar returnPolicy desde others_content o default
        $return_policy = $this->getReturnPolicyFromOthers($saved_content);
        if (!$return_policy) {
            $return_policy = $this->getDefaultReturnPolicy();
        }
        $offer["hasMerchantReturnPolicy"] = $return_policy;

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
     * Genera esquemas adicionales (FAQ, HowTo, Review, Organization) - CON PERSISTENCIA
     */
    private function generateAdditionalSchemas($product_info, $that, $saved_content = null)
    {
        $schemas = [];
        $config = $that->config;

        // FAQPage Schema - USAR CONTENIDO GUARDADO
        if (($config->get('smart_seo_schema_enable_faq_schema') && !$saved_content) ||
            ($saved_content && $saved_content['enable_faq'] && !empty($saved_content['faq_content']))) {
            $faq = $this->generateFAQSchema($product_info, $saved_content);
            if ($faq) {
                $schemas[] = $faq;
            }
        }

        // HowTo Schema - USAR CONTENIDO GUARDADO
        if (($config->get('smart_seo_schema_enable_howto_schema') && !$saved_content) ||
            ($saved_content && $saved_content['enable_howto'] && !empty($saved_content['howto_content']))) {
            $howto = $this->generateHowToSchema($product_info, $saved_content);
            if ($howto) {
                $schemas[] = $howto;
            }
        }

        // Review Schema - USAR CONTENIDO GUARDADO
        if (($config->get('smart_seo_schema_enable_review_schema') && !$saved_content) ||
            ($saved_content && $saved_content['enable_review'] && !empty($saved_content['review_content']))) {
            $review = $this->generateReviewSchema($product_info, $saved_content);
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
     * Genera FAQ Schema con IA - CON PERSISTENCIA
     */
    private function generateFAQSchema($product_info, $saved_content = null)
    {
        // USAR CONTENIDO GUARDADO SI EXISTE
        if ($saved_content && !empty($saved_content['faq_content'])) {
            return $this->parseFAQContent($saved_content['faq_content']);
        }

        // Implementación básica - fallback
        return [
            "@type" => "FAQPage",
            "mainEntity" => [
                [
                    "@type" => "Question",
                    "name" => "What is " . $product_info['name'] . "?",
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => "Basic information about " . $product_info['name']
                    ]
                ]
            ]
        ];
    }

    /**
     * Genera HowTo Schema con IA - CON PERSISTENCIA
     */
    private function generateHowToSchema($product_info, $saved_content = null)
    {
        // USAR CONTENIDO GUARDADO SI EXISTE
        if ($saved_content && !empty($saved_content['howto_content'])) {
            return $this->parseHowToContent($product_info['name'], $saved_content['howto_content']);
        }

        // Implementación básica - fallback
        return [
            "@type" => "HowTo",
            "name" => "How to use " . $product_info['name'],
            "step" => [
                [
                    "@type" => "HowToStep",
                    "name" => "Step 1",
                    "text" => "Basic usage instructions for " . $product_info['name']
                ]
            ]
        ];
    }

    /**
     * Genera Review Schema básico
     */
    private function generateReviewSchema($product_info, $saved_content = null)
    {
        // USAR CONTENIDO GUARDADO SI EXISTE
        if ($saved_content && !empty($saved_content['review_content'])) {
            return [
                "@type" => "Review",
                "reviewBody" => $saved_content['review_content'],
                "author" => [
                    "@type" => "Person",
                    "name" => "Verified Customer"
                ],
                "reviewRating" => [
                    "@type" => "Rating",
                    "ratingValue" => "5",
                    "bestRating" => "5"
                ]
            ];
        }

        return null;
    }

    /**
     * Parsea contenido FAQ guardado y convierte a Schema.org
     */
    private function parseFAQContent($faq_content)
    {
        $questions = [];
        $lines = explode("\n", $faq_content);
        
        $current_question = '';
        $current_answer = '';
        $expecting_answer = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Detectar pregunta (Q:, Question:, ¿, ?)
            if (preg_match('/^(?:Q:|Question:|¿)(.+)$/i', $line, $matches) || substr($line, -1) === '?') {
                // Guardar pregunta anterior si existe
                if ($current_question && $current_answer) {
                    $questions[] = [
                        "@type" => "Question",
                        "name" => $current_question,
                        "acceptedAnswer" => [
                            "@type" => "Answer",
                            "text" => $current_answer
                        ]
                    ];
                }
                
                $current_question = isset($matches[1]) ? trim($matches[1]) : $line;
                $current_answer = '';
                $expecting_answer = true;
            }
            // Detectar respuesta (A:, Answer:, o línea normal después de pregunta)
            elseif (preg_match('/^(?:A:|Answer:)(.+)$/i', $line, $matches)) {
                $current_answer = trim($matches[1]);
                $expecting_answer = false;
            }
            elseif ($expecting_answer || !empty($current_question)) {
                $current_answer .= ($current_answer ? ' ' : '') . $line;
            }
        }
        
        // Guardar última pregunta
        if ($current_question && $current_answer) {
            $questions[] = [
                "@type" => "Question",
                "name" => $current_question,
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => $current_answer
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

    /**
     * Parsea contenido HowTo guardado y convierte a Schema.org
     */
    private function parseHowToContent($product_name, $howto_content)
    {
        $steps = [];
        $lines = explode("\n", $howto_content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Detectar paso (Step 1:, 1., etc.)
            if (preg_match('/^(?:Step\s*)?(\d+)[:.]?\s*(.+)$/i', $line, $matches)) {
                $steps[] = [
                    "@type" => "HowToStep",
                    "name" => "Step " . $matches[1],
                    "text" => trim($matches[2])
                ];
            }
            // Si no hay numeración, agregar como paso simple
            elseif (!empty($steps)) {
                // Agregar al último paso si es continuación
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

    /**
     * Genera Organization Schema con nombre corregido desde config_owner
     */
    private function generateOrganizationSchema($that)
    {
        $config = $that->config;
        $db = $that->db;
        
        // Obtener nombre de la organización desde config_owner
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
        
        // Fallback al config_name si config_owner está vacío
        if (empty($org_name)) {
            $org_name = $config->get('config_name');
        }
        
        // Si aún está vacío, usar un valor por defecto
        if (empty($org_name)) {
            $org_name = 'Online Store';
        }
        
        return [
            "@type" => "Organization",
            "name" => $org_name,
            "url" => $config->get('config_url')
        ];
    }

    /**
     * Obtiene imágenes del producto (principal y adicionales) - CORREGIDO PARA ABANTECART
     */
    private function getProductImages($that, $product_info)
    {
        $db = $that->db;
        $config = $that->config;
        $product_id = $product_info['product_id'];
        
        // Obtener imagen principal - SINTAXIS COMPATIBLE CON ABANTECART
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
            
            // Verificar si necesitamos imágenes adicionales
            if ($that->config->get('smart_seo_schema_show_image')) {
                // Obtener imágenes adicionales (máximo 5) - SINTAXIS COMPATIBLE
                $additionalImagesQuery = "SELECT DISTINCT rd.resource_path 
                                         FROM " . DB_PREFIX . "resource_map rm 
                                         JOIN " . DB_PREFIX . "resource_descriptions rd ON rm.resource_id = rd.resource_id
                                         WHERE rm.object_name = 'products' AND rm.object_id = " . (int)$product_id . " 
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

    /**
     * Extrae shippingDetails de others_content
     */
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

    /**
     * Extrae returnPolicy de others_content
     */
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

    /**
     * Calcula precio de variante según prefix mejorado (% y $)
     */
    private function calculateVariantPrice($basePrice, $variantPrice, $prefix)
    {
        switch ($prefix) {
            case '%':
                // Porcentaje: precio_base * (1 + precio_variante)
                return $basePrice * (1 + $variantPrice);
            case '$':
                // Suma fija: precio_base + precio_variante
                return $basePrice + $variantPrice;
            case '+':
                // Compatibilidad con sistema anterior
                return $basePrice + $variantPrice;
            case '-':
                // Compatibilidad con sistema anterior
                return $basePrice - $variantPrice;
            default:
                // Sin modificación
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
            WHERE s.key = 'config_storefront_language' 
            AND s.store_id = 0 
            LIMIT 1
        ");

        return $query->num_rows ? $query->row['language_id'] : 1;
    }
}