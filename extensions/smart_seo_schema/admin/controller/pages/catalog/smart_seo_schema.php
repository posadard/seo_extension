<?php

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

class ControllerPagesCatalogSmartSeoSchema extends AController
{
    public $error = array();
    public $data = array();

    public function main()
    {
        $this->loadLanguage('smart_seo_schema/smart_seo_schema');
        $this->loadLanguage('catalog/product');
        $this->loadModel('catalog/product');
        
        $product_id = $this->request->get['product_id'];
        
        if ($this->request->is_POST() && $this->validateForm()) {
            $this->saveSchemaSettings($product_id);
            $this->session->data['success'] = $this->language->get('text_success');
            redirect($this->html->getSecureURL('catalog/smart_seo_schema', '&product_id=' . $product_id));
        }

        $this->document->setTitle($this->language->get('smart_seo_schema_name'));
        
        $product_info = $this->model_catalog_product->getProduct($product_id);
        $this->data['product_description'] = $this->model_catalog_product->getProductDescriptions($product_id);
        $this->data['heading_title'] = $this->language->get('text_edit') . '&nbsp;' . $this->language->get('text_product');

        $this->view->assign('error', $this->error);
        $this->view->assign('success', $this->session->data['success']);
        if (isset($this->session->data['success'])) {
            unset($this->session->data['success']);
        }

        $this->setupBreadcrumb($product_id);

        $this->data['active'] = 'smart_seo_schema';
        $tabs_obj = $this->dispatch('pages/catalog/product_tabs', array($this->data));
        $this->data['product_tabs'] = $tabs_obj->dispatchGetOutput();
        unset($tabs_obj);

        $this->data['schema_settings'] = $this->getSchemaSettings($product_id);
        $this->data['product_variants'] = $this->getProductVariants($product_id);
        $this->data['product_reviews'] = $this->getProductReviews($product_id);
        $this->data['ai_status'] = $this->checkAIConnection();
        
        $this->setupForm($product_id);

        $this->addChild('pages/catalog/product_summary', 'summary_form', 'pages/catalog/product_summary.tpl');

        $this->view->batchAssign($this->data);
        $this->processTemplate('pages/smart_seo_schema/smart_seo_schema_form.tpl');
    }

    public function testAIConnection()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $this->loadLanguage('smart_seo_schema/smart_seo_schema');
            
            $api_key = $this->config->get('smart_seo_schema_groq_api_key');
            $model = $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant';
            
            $json = array();
            
            $this->logDebug("=== INICIO TEST CONEXIÓN IA ===");
            $this->logDebug("API Key presente: " . (!empty($api_key) ? 'Sí (' . strlen($api_key) . ' chars)' : 'No'));
            $this->logDebug("Modelo configurado: " . $model);
            
            if (!$api_key) {
                $json = array(
                    'error' => true,
                    'message' => 'No API key configured. Please enter your Groq API key in extension settings.',
                    'debug' => 'No API key found in configuration'
                );
                $this->logDebug("Error: No API key configurado");
            } else {
                $this->logDebug("Llamando a Groq API con fallback...");
                $response = $this->callGroqAPIWithFallback($api_key, $model, 'Test connection - please respond with "Connection successful"', 50);
                
                if ($response['success']) {
                    $json = array(
                        'error' => false,
                        'message' => "Connection successful! Model '{$response['model_used']}' is working properly. Response: " . substr($response['content'], 0, 100) . "...",
                        'response_length' => strlen($response['content']),
                        'model_used' => $response['model_used'],
                        'fallback_used' => $response['fallback_used']
                    );
                    $this->logDebug("Éxito: " . $json['message']);
                } else {
                    $json = array(
                        'error' => true,
                        'message' => "Connection failed after trying all models. " . $response['error'],
                        'debug' => $response['debug'] ?? 'All fallback attempts failed'
                    );
                    $this->logDebug("Error: " . $response['error']);
                }
            }
        } catch (Exception $e) {
            $json = array(
                'error' => true,
                'message' => "Connection failed: " . $e->getMessage(),
                'debug' => array(
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            );
            $this->logDebug("Excepción capturada: " . $e->getMessage());
        } catch (Error $e) {
            $json = array(
                'error' => true,
                'message' => "Fatal error: " . $e->getMessage(),
                'debug' => array(
                    'error' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            );
            $this->logDebug("Error fatal: " . $e->getMessage());
        }

        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function generateDescriptionContent()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $product_id = $this->request->get['product_id'];
            
            if (!$product_id) {
                throw new Exception('Missing product_id parameter');
            }
            
            $this->loadModel('catalog/product');
            $product_info = $this->model_catalog_product->getProduct($product_id);
            
            if (!$product_info) {
                throw new Exception('Product not found: ' . $product_id);
            }
            
            $this->logDebug("=== GENERANDO DESCRIPCIÓN INDIVIDUAL (150-160 CARACTERES) ===");
            $this->logDebug("Producto: " . $product_info['name']);
            
            $content = $this->generateDescriptionWithAI($product_info);
            
            // Validar longitud objetivo
            $length = strlen($content);
            $is_optimal_length = ($length >= 150 && $length <= 160);
            
            $json = array(
                'error' => false,
                'content' => $content,
                'length' => $length,
                'optimal_length' => $is_optimal_length,
                'target_range' => '150-160 characters',
                'word_count' => str_word_count($content),
                'timestamp' => date('Y-m-d H:i:s')
            );
            
            $this->logDebug("Descripción generada - Longitud: {$length} caracteres (Objetivo: 150-160) - Óptima: " . ($is_optimal_length ? 'Sí' : 'No'));
            
        } catch (Exception $e) {
            $json = array(
                'error' => true,
                'message' => $e->getMessage(),
                'debug' => array(
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            );
            $this->logDebug("Error generando descripción: " . $e->getMessage());
        }

        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function generateFAQContent()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $product_id = $this->request->get['product_id'];
            
            if (!$product_id) {
                throw new Exception('Missing product_id parameter');
            }
            
            $this->loadModel('catalog/product');
            $product_info = $this->model_catalog_product->getProduct($product_id);
            
            if (!$product_info) {
                throw new Exception('Product not found: ' . $product_id);
            }
            
            $this->logDebug("=== GENERANDO FAQ INDIVIDUAL ===");
            $this->logDebug("Producto: " . $product_info['name']);
            
            $content = $this->generateFAQWithAI($product_info);
            
            $json = array(
                'error' => false,
                'content' => $content,
                'length' => strlen($content),
                'word_count' => str_word_count($content),
                'timestamp' => date('Y-m-d H:i:s')
            );
            
            $this->logDebug("FAQ generado exitosamente - Longitud: " . strlen($content) . " caracteres");
            
        } catch (Exception $e) {
            $json = array(
                'error' => true,
                'message' => $e->getMessage(),
                'debug' => array(
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            );
            $this->logDebug("Error generando FAQ: " . $e->getMessage());
        }

        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function generateHowToContent()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $product_id = $this->request->get['product_id'];
            
            if (!$product_id) {
                throw new Exception('Missing product_id parameter');
            }
            
            $this->loadModel('catalog/product');
            $product_info = $this->model_catalog_product->getProduct($product_id);
            
            if (!$product_info) {
                throw new Exception('Product not found: ' . $product_id);
            }
            
            $this->logDebug("=== GENERANDO HOWTO INDIVIDUAL ===");
            $this->logDebug("Producto: " . $product_info['name']);
            
            $content = $this->generateHowToWithAI($product_info);
            
            $json = array(
                'error' => false,
                'content' => $content,
                'length' => strlen($content),
                'word_count' => str_word_count($content),
                'timestamp' => date('Y-m-d H:i:s')
            );
            
            $this->logDebug("HowTo generado exitosamente - Longitud: " . strlen($content) . " caracteres");
            
        } catch (Exception $e) {
            $json = array(
                'error' => true,
                'message' => $e->getMessage(),
                'debug' => array(
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            );
            $this->logDebug("Error generando HowTo: " . $e->getMessage());
        }

        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function generateAdditionalProperties()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $product_id = $this->request->get['product_id'];
            if (!$product_id) {
                throw new Exception('Missing product_id parameter');
            }
            
            $this->loadModel('catalog/product');
            $product_info = $this->model_catalog_product->getProduct($product_id);
            if (!$product_info) {
                throw new Exception('Product not found: ' . $product_id);
            }
            
            $this->logDebug("=== GENERANDO additionalProperty IA ===");
            $this->logDebug("Producto: " . $product_info['name']);
            
            $content = $this->generateAdditionalPropertiesWithAI($product_info);
            
            $json = array(
                'error' => false,
                'content' => $content,
                'length' => strlen($content),
                'property_count' => 0,
                'timestamp' => date('Y-m-d H:i:s')
            );
            
            // Validar y contar propiedades generadas
            try {
                $decoded = json_decode($content, true);
                if (is_array($decoded) && isset($decoded['additionalProperty'])) {
                    $json['property_count'] = count($decoded['additionalProperty']);
                    $this->logDebug("additionalProperty generado exitosamente - " . $json['property_count'] . " propiedades, " . strlen($content) . " caracteres");
                }
            } catch (Exception $e) {
                $this->logDebug("Error validando contenido generado: " . $e->getMessage());
            }
            
        } catch (Exception $e) {
            $json = array(
                'error' => true,
                'message' => $e->getMessage(),
                'debug' => array(
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            );
            $this->logDebug("Error generando additionalProperty: " . $e->getMessage());
        }
        
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function getReviewsAjax()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $product_id = $this->request->get['product_id'];
            
            if (!$product_id) {
                throw new Exception('Missing product_id parameter');
            }
            
            $reviews = $this->getProductReviews($product_id);
            
            $json = array(
                'error' => false,
                'reviews' => $reviews,
                'count' => count($reviews)
            );
            
        } catch (Exception $e) {
            $json = array(
                'error' => true,
                'message' => $e->getMessage()
            );
        }

        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function optimizeReview()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $review_id = $this->request->post['review_id'];
            $review_text = $this->request->post['review_text'];
            $product_id = $this->request->get['product_id'];
            
            if (!$review_id || !$review_text || !$product_id) {
                throw new Exception('Missing required parameters');
            }
            
            $this->loadModel('catalog/product');
            $product_info = $this->model_catalog_product->getProduct($product_id);
            
            if (!$product_info) {
                throw new Exception('Product not found');
            }
            
            $optimized_text = $this->optimizeReviewWithAI($review_text, $product_info);
            
            $json = array(
                'error' => false,
                'optimized_text' => $optimized_text,
                'original_length' => strlen($review_text),
                'optimized_length' => strlen($optimized_text)
            );
            
        } catch (Exception $e) {
            $json = array(
                'error' => true,
                'message' => $e->getMessage()
            );
        }

        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function generateExampleReview()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $product_id = $this->request->get['product_id'];
            
            if (!$product_id) {
                throw new Exception('Missing product_id parameter');
            }
            
            $this->loadModel('catalog/product');
            $product_info = $this->model_catalog_product->getProduct($product_id);
            
            if (!$product_info) {
                throw new Exception('Product not found');
            }
            
            $example_review = $this->generateExampleReviewWithAI($product_info);
            
            $json = array(
                'error' => false,
                'review' => $example_review
            );
            
        } catch (Exception $e) {
            $json = array(
                'error' => true,
                'message' => $e->getMessage()
            );
        }

        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function saveReview()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $review_id = $this->request->post['review_id'];
            $product_id = $this->request->post['product_id'];
            $author = $this->request->post['author'];
            $text = $this->request->post['text'];
            $rating = (int)$this->request->post['rating'];
            $verified_purchase = isset($this->request->post['verified_purchase']) ? 1 : 0;
            $status = isset($this->request->post['status']) ? 1 : 0;
            
            if (!$product_id || !$author || !$text || !$rating) {
                throw new Exception('Missing required fields');
            }
            
            if ($rating < 1 || $rating > 5) {
                throw new Exception('Rating must be between 1 and 5');
            }
            
            if ($review_id) {
                $this->updateReview($review_id, $author, $text, $rating, $verified_purchase, $status);
                $message = 'Review updated successfully';
            } else {
                $this->createReview($product_id, $author, $text, $rating, $verified_purchase, $status);
                $message = 'Review created successfully';
            }
            
            $json = array(
                'error' => false,
                'message' => $message
            );
            
        } catch (Exception $e) {
            $json = array(
                'error' => true,
                'message' => $e->getMessage()
            );
        }

        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function deleteReview()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $review_id = $this->request->post['review_id'];
            
            if (!$review_id) {
                throw new Exception('Missing review_id parameter');
            }
            
            $this->db->query("DELETE FROM " . DB_PREFIX . "reviews WHERE review_id = " . (int)$review_id);
            
            $json = array(
                'error' => false,
                'message' => 'Review deleted successfully'
            );
            
        } catch (Exception $e) {
            $json = array(
                'error' => true,
                'message' => $e->getMessage()
            );
        }

        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function getVariants()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $product_id = $this->request->get['product_id'];
            
            if (!$product_id) {
                throw new Exception('Missing product_id parameter');
            }
            
            $variants = $this->getProductVariants($product_id);
            
            $json = array(
                'error' => false,
                'variants' => $variants,
                'count' => count($variants)
            );
            
        } catch (Exception $e) {
            $json = array(
                'error' => true,
                'message' => $e->getMessage()
            );
        }

        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function previewSchema()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $product_id = $this->request->get['product_id'];
            
            if (!$product_id) {
                throw new Exception('Missing product_id parameter');
            }
            
            $this->loadModel('catalog/product');
            $product_info = $this->model_catalog_product->getProduct($product_id);
            
            if (!$product_info) {
                throw new Exception('Product not found: ' . $product_id);
            }
            
            if (!class_exists('ExtensionSmartSeoSchema')) {
                throw new Exception('ExtensionSmartSeoSchema class not found. Please check if core extension file exists.');
            }
            
            $extension = new ExtensionSmartSeoSchema();
            $schema = $extension->generateCompleteSchemaForProduct($product_info);
            
            $json = array(
                'error' => false,
                'schema' => $schema
            );
            
        } catch (Exception $e) {
            $json = array(
                'error' => true,
                'message' => 'Schema generation failed: ' . $e->getMessage(),
                'debug' => array(
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            );
            $this->logDebug("Error generando schema: " . $e->getMessage());
        }

        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function validateAdditionalPropertyJSON()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $others_content = trim($this->request->post['others_content'] ?? '');
            
            $json = array();
            
            if (empty($others_content)) {
                $json = array(
                    'error' => false,
                    'valid' => true,
                    'message' => 'JSON field is empty.'
                );
            } else {
                $decoded = json_decode($others_content, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    
                    // Validación específica para additionalProperty
                    if (isset($decoded['additionalProperty'])) {
                        $validation_result = $this->validateAdditionalPropertyStructure($decoded['additionalProperty']);
                        
                        if (!$validation_result['valid']) {
                            $json = array(
                                'error' => true,
                                'valid' => false,
                                'message' => 'additionalProperty validation failed: ' . $validation_result['error']
                            );
                        } else {
                            $json = array(
                                'error' => false,
                                'valid' => true,
                                'message' => 'Valid JSON with proper additionalProperty structure!',
                                'property_count' => count($decoded['additionalProperty']),
                                'parsed_data' => $decoded
                            );
                        }
                    } else {
                        $json = array(
                            'error' => false,
                            'valid' => true,
                            'message' => 'Valid JSON format!',
                            'parsed_data' => $decoded
                        );
                    }
                    
                } else {
                    $json = array(
                        'error' => true,
                        'valid' => false,
                        'message' => 'Invalid JSON: ' . json_last_error_msg()
                    );
                }
            }
            
        } catch (Exception $e) {
            $json = array(
                'error' => true,
                'valid' => false,
                'message' => 'Validation error: ' . $e->getMessage()
            );
        }

        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function validateOthersJSON()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $others_content = trim($this->request->post['others_content'] ?? '');
            
            $json = array();
            
            if (empty($others_content)) {
                $json = array(
                    'error' => false,
                    'valid' => true,
                    'message' => 'JSON field is empty.'
                );
            } else {
                $decoded = json_decode($others_content, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Validación específica para additionalProperty si está presente
                    if (isset($decoded['additionalProperty'])) {
                        $validation_result = $this->validateAdditionalPropertyStructure($decoded['additionalProperty']);
                        if (!$validation_result['valid']) {
                            $json = array(
                                'error' => true,
                                'valid' => false,
                                'message' => 'additionalProperty validation failed: ' . $validation_result['error']
                            );
                        } else {
                            $json = array(
                                'error' => false,
                                'valid' => true,
                                'message' => 'Valid JSON with proper additionalProperty structure!',
                                'property_count' => count($decoded['additionalProperty']),
                                'parsed_data' => $decoded
                            );
                        }
                    } else {
                        $json = array(
                            'error' => false,
                            'valid' => true,
                            'message' => 'Valid JSON format!',
                            'parsed_data' => $decoded
                        );
                    }
                } else {
                    $json = array(
                        'error' => true,
                        'valid' => false,
                        'message' => 'Invalid JSON: ' . json_last_error_msg()
                    );
                }
            }
            
        } catch (Exception $e) {
            $json = array(
                'error' => true,
                'valid' => false,
                'message' => 'Validation error: ' . $e->getMessage()
            );
        }

        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit();
    }

    private function callGroqAPIWithFallback($api_key, $primary_model, $prompt, $max_tokens = null)
    {
        // Obtener modelos de fallback desde configuración
        $fallback_model_1 = $this->config->get('smart_seo_schema_groq_fallback_model_1') ?: 'gemma2-9b-it';
        $fallback_model_2 = $this->config->get('smart_seo_schema_groq_fallback_model_2') ?: 'llama-3.1-70b-versatile';
        
        // Array de modelos para intentar en orden
        $models_to_try = [
            ['model' => $primary_model, 'timeout' => 30, 'label' => 'primary'],
            ['model' => $fallback_model_1, 'timeout' => 45, 'label' => 'fallback_1'],
            ['model' => $fallback_model_2, 'timeout' => 60, 'label' => 'fallback_2']
        ];
        
        $this->logDebug("=== INICIO LLAMADA API CON FALLBACK ===");
        $this->logDebug("Modelo principal: " . $primary_model);
        $this->logDebug("Fallback 1: " . $fallback_model_1);
        $this->logDebug("Fallback 2: " . $fallback_model_2);
        
        $last_error = '';
        $all_errors = [];
        
        foreach ($models_to_try as $index => $model_config) {
            $model = $model_config['model'];
            $timeout = $model_config['timeout'];
            $label = $model_config['label'];
            
            $this->logDebug("--- Intento " . ($index + 1) . "/3: {$model} (timeout: {$timeout}s) ---");
            
            try {
                $content = $this->callGroqAPI($api_key, $model, $prompt, $max_tokens, $timeout);
                
                if (!empty($content)) {
                    $this->logDebug("ÉXITO con modelo: {$model} (" . strlen($content) . " caracteres)");
                    
                    return [
                        'success' => true,
                        'content' => $content,
                        'model_used' => $model,
                        'fallback_used' => ($index > 0),
                        'attempt_number' => $index + 1,
                        'total_attempts' => count($models_to_try)
                    ];
                }
                
            } catch (Exception $e) {
                $error_msg = $e->getMessage();
                $this->logDebug("FALLO modelo {$model}: " . $error_msg);
                
                $all_errors[] = "{$label} ({$model}): " . $error_msg;
                $last_error = $error_msg;
                
                // Verificar si el error es recuperable (servidor) o fatal (auth/config)
                if ($this->isFatalError($e)) {
                    $this->logDebug("Error fatal detectado, abortando fallback: " . $error_msg);
                    break;
                }
                
                // Continuar con siguiente modelo si es error recuperable
                $this->logDebug("Error recuperable, intentando siguiente modelo...");
                continue;
            }
        }
        
        $this->logDebug("=== TODOS LOS MODELOS FALLARON ===");
        $this->logDebug("Errores por modelo: " . print_r($all_errors, true));
        
        return [
            'success' => false,
            'error' => "All models failed. Last error: " . $last_error,
            'all_errors' => $all_errors,
            'models_tried' => array_column($models_to_try, 'model'),
            'debug' => 'Tried ' . count($models_to_try) . ' models: ' . implode(', ', array_column($models_to_try, 'model'))
        ];
    }
    
    private function isFatalError($exception)
    {
        $message = $exception->getMessage();
        
        // Errores fatales que no se resuelven con fallback
        $fatal_patterns = [
            '/Invalid API key/',
            '/Unauthorized/',
            '/cURL Error/',
            '/Network error/',
            '/JSON decode error/',
            '/cURL extension is not available/'
        ];
        
        foreach ($fatal_patterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }
        
        return false;
    }

    private function validateAdditionalPropertyStructure($additionalProperty)
    {
        if (!is_array($additionalProperty)) {
            return ['valid' => false, 'error' => 'additionalProperty must be an array'];
        }
        
        foreach ($additionalProperty as $index => $property) {
            if (!is_array($property)) {
                return ['valid' => false, 'error' => "Item $index is not an object"];
            }
            
            // Validar @type
            if (!isset($property['@type']) || $property['@type'] !== 'PropertyValue') {
                return ['valid' => false, 'error' => "Item $index missing or invalid @type (must be 'PropertyValue')"];
            }
            
            // Validar name (requerido)
            if (!isset($property['name']) || !is_string($property['name']) || trim($property['name']) === '') {
                return ['valid' => false, 'error' => "Item $index missing or invalid name field"];
            }
            
            // Validar value (requerido)
            if (!isset($property['value'])) {
                return ['valid' => false, 'error' => "Item $index missing value field"];
            }
            
            if (!is_string($property['value']) && !is_numeric($property['value'])) {
                return ['valid' => false, 'error' => "Item $index has invalid value type (must be string or number)"];
            }
            
            // Validar description (opcional)
            if (isset($property['description']) && !is_string($property['description'])) {
                return ['valid' => false, 'error' => "Item $index has invalid description type (must be string)"];
            }
            
            // Validar unitCode (opcional)
            if (isset($property['unitCode']) && !is_string($property['unitCode'])) {
                return ['valid' => false, 'error' => "Item $index has invalid unitCode type (must be string)"];
            }
            
            // Validar que no tenga propiedades no permitidas
            $allowed_properties = ['@type', 'name', 'value', 'description', 'unitCode'];
            foreach ($property as $key => $value) {
                if (!in_array($key, $allowed_properties)) {
                    return ['valid' => false, 'error' => "Item $index has unexpected property '$key'"];
                }
            }
        }
        
        return ['valid' => true];
    }

    private function cleanJsonContent($json_string)
    {
        if (empty($json_string)) {
            return '';
        }
        
        // Múltiples niveles de decodificación HTML
        $cleaned = $json_string;
        $max_iterations = 3; // Máximo 3 iteraciones para evitar loops infinitos
        $iteration = 0;
        
        while ($iteration < $max_iterations && 
               (strpos($cleaned, '&quot;') !== false || 
                strpos($cleaned, '&amp;') !== false || 
                strpos($cleaned, '&#039;') !== false)) {
            
            $cleaned = html_entity_decode($cleaned, ENT_QUOTES, 'UTF-8');
            $iteration++;
        }
        
        return $cleaned;
    }

    private function generateAdditionalPropertiesWithAI($product_info)
    {
        $api_key = $this->config->get('smart_seo_schema_groq_api_key');
        if (!$api_key) {
            throw new Exception('No API key configured');
        }
        
        $existing_description = '';
        if (!empty($product_info['description'])) {
            $existing_description = strip_tags($product_info['description']);
            $existing_description = preg_replace('/\s+/', ' ', trim($existing_description));
        }
        
        $prompt = "Extract the most relevant technical and commercial product properties from the following product description. ";
        $prompt .= "Create detailed PropertyValue objects with enhanced information.\n\n";
        $prompt .= "Return ONLY a JSON object with this EXACT structure:\n";
        $prompt .= "{\n";
        $prompt .= '  "additionalProperty": [\n';
        $prompt .= '    {\n';
        $prompt .= '      "@type": "PropertyValue",\n';
        $prompt .= '      "name": "Property Name",\n';
        $prompt .= '      "value": "Property Value",\n';
        $prompt .= '      "description": "Brief explanation",\n';
        $prompt .= '      "unitCode": "UNIT"\n';
        $prompt .= '    }\n';
        $prompt .= '  ]\n';
        $prompt .= '}\n\n';
        
        $prompt .= "IMPORTANT RULES:\n";
        $prompt .= "- For percentages: use value as number (e.g., \"99\") and unitCode \"PERCENT\"\n";
        $prompt .= "- For weights: use value as number with unitCode \"GRM\" or \"KGM\"\n";
        $prompt .= "- For volumes: use value as number with unitCode \"MLT\" or \"LTR\"\n";
        $prompt .= "- For identifiers (CAS, SKU): no unitCode needed\n";
        $prompt .= "- Keep descriptions concise and informative\n\n";
        
        $prompt .= "Example format:\n";
        $prompt .= "{\n";
        $prompt .= '  "additionalProperty": [\n';
        $prompt .= '    {\n';
        $prompt .= '      "@type": "PropertyValue",\n';
        $prompt .= '      "name": "Purity",\n';
        $prompt .= '      "value": "99",\n';
        $prompt .= '      "description": "typical analysis on dry basis",\n';
        $prompt .= '      "unitCode": "PERCENT"\n';
        $prompt .= '    },\n';
        $prompt .= '    {\n';
        $prompt .= '      "@type": "PropertyValue",\n';
        $prompt .= '      "name": "CAS Number",\n';
        $prompt .= '      "value": "6484-52-2",\n';
        $prompt .= '      "description": "Chemical Abstracts Service registry number"\n';
        $prompt .= '    }\n';
        $prompt .= '  ]\n';
        $prompt .= '}\n\n';
        
        $prompt .= "Product: " . $product_info['name'] . "\n";
        $prompt .= "Model: " . $product_info['model'] . "\n";
        if ($existing_description) {
            $prompt .= "Description: " . substr($existing_description, 0, 500) . "\n";
        }
        $prompt .= "\nReturn ONLY the JSON object. No explanations:";
        
        $model = $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant';
        $result = $this->callGroqAPIWithFallback($api_key, $model, $prompt, 600);
        
        if (!$result['success']) {
            throw new Exception('Failed to generate additionalProperty: ' . $result['error']);
        }
        
        $response = trim($result['content']);
        
        // Limpiar la respuesta para asegurar que sea JSON válido
        $response = preg_replace('/^[^{]*/', '', $response); // Remover todo antes del primer {
        $response = preg_replace('/[^}]*$/', '', $response); // Remover todo después del último }
        
        // Validar que es un objeto JSON válido con additionalProperty
        $json_decoded = json_decode($response, true);
        if (!isset($json_decoded['additionalProperty']) || !is_array($json_decoded['additionalProperty'])) {
            throw new Exception('AI did not return valid additionalProperty structure. Response: ' . substr($response, 0, 200));
        }
        
        // Validar estructura usando el método de validación
        $validation_result = $this->validateAdditionalPropertyStructure($json_decoded['additionalProperty']);
        if (!$validation_result['valid']) {
            throw new Exception('Invalid additionalProperty structure: ' . $validation_result['error']);
        }
        
        // Re-encode SIN htmlspecialchars para evitar HTML entities
        return json_encode($json_decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function generateDescriptionWithAI($product_info)
    {
        $api_key = $this->config->get('smart_seo_schema_groq_api_key');
        
        if (!$api_key) {
            throw new Exception('No API key configured');
        }
        
        // Analizar descripción original para extraer información clave
        $existing_description = '';
        if (!empty($product_info['description'])) {
            $existing_description = strip_tags($product_info['description']);
            $existing_description = preg_replace('/\s+/', ' ', trim($existing_description));
        }
        
        $this->logDebug("Descripción original: " . strlen($existing_description) . " caracteres");
        
        $prompt = "Create a concise product summary that captures the MOST IMPORTANT information about this product.\n\n";
        $prompt .= "Product: " . $product_info['name'] . "\n";
        $prompt .= "Model: " . $product_info['model'] . "\n";
        
        if ($existing_description) {
            $prompt .= "Full Description: " . $existing_description . "\n\n";
            $prompt .= "Extract and summarize the KEY FEATURES and ESSENTIAL INFORMATION only. Prioritize:\n";
        } else {
            $prompt .= "\nCreate a professional summary focusing on:\n";
        }
        
        $prompt .= "- Core functionality and primary benefits\n";
        $prompt .= "- Technical specifications that matter most\n";
        $prompt .= "- Target use cases\n";
        $prompt .= "- Key differentiators\n\n";
        
        $prompt .= "CRITICAL REQUIREMENTS:\n";
        $prompt .= "- Exactly 150-160 characters total (including spaces)\n";
        $prompt .= "- Focus on INFORMATION over decorative language\n";
        $prompt .= "- No marketing fluff or unnecessary adjectives\n";
        $prompt .= "- Professional, direct tone\n";
        $prompt .= "- Count every character carefully\n\n";
        
        $prompt .= "Return ONLY the 150-160 character summary, no explanations:";
        
        $this->logDebug("Generando descripción con prompt optimizado para 150-160 caracteres");
        
        $model = $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant';
        $result = $this->callGroqAPIWithFallback($api_key, $model, $prompt, 100);
        
        if (!$result['success']) {
            throw new Exception('Failed to generate description: ' . $result['error']);
        }
        
        $response = $result['content'];
        
        // Limpieza y validación de la respuesta
        $response = trim($response);
        $response = preg_replace('/\s+/', ' ', $response); // Normalizar espacios
        
        // Si la respuesta excede el límite, intentar recortar manteniendo sentido
        if (strlen($response) > 160) {
            $this->logDebug("Respuesta excede 160 caracteres (" . strlen($response) . "), aplicando recorte inteligente");
            $response = $this->intelligentTruncate($response, 160);
        }
        
        // Si es muy corta, realizar un segundo intento más específico
        if (strlen($response) < 150) {
            $this->logDebug("Respuesta muy corta (" . strlen($response) . " caracteres), generando versión expandida");
            $response = $this->expandDescription($product_info, $response, $api_key);
        }
        
        return $response;
    }
    
    private function intelligentTruncate($text, $max_length)
    {
        if (strlen($text) <= $max_length) {
            return $text;
        }
        
        // Buscar punto de corte inteligente (espacio, punto, coma)
        $truncated = substr($text, 0, $max_length);
        $last_space = strrpos($truncated, ' ');
        $last_punct = max(strrpos($truncated, '.'), strrpos($truncated, ','));
        
        if ($last_punct > 0 && $last_punct > ($max_length * 0.8)) {
            // Cortar en puntuación si está en el último 20%
            return substr($text, 0, $last_punct + 1);
        } elseif ($last_space > 0 && $last_space > ($max_length * 0.9)) {
            // Cortar en espacio si está en el último 10%
            return substr($text, 0, $last_space);
        } else {
            // Corte forzado pero evitar cortar palabras
            return substr($text, 0, $max_length - 3) . '...';
        }
    }
    
    private function expandDescription($product_info, $short_description, $api_key)
    {
        $current_length = strlen($short_description);
        $needed_chars = 150 - $current_length;
        
        if ($needed_chars <= 0) {
            return $short_description;
        }
        
        $expand_prompt = "Expand this product description to exactly 150-160 characters by adding the most important missing information:\n\n";
        $expand_prompt .= "Current description (" . $current_length . " chars): " . $short_description . "\n";
        $expand_prompt .= "Product: " . $product_info['name'] . "\n";
        $expand_prompt .= "Model: " . $product_info['model'] . "\n\n";
        $expand_prompt .= "Add approximately " . $needed_chars . " more characters with essential product details.\n";
        $expand_prompt .= "Return ONLY the expanded 150-160 character description:";
        
        $model = $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant';
        $result = $this->callGroqAPIWithFallback($api_key, $model, $expand_prompt, 80);
        
        if (!$result['success']) {
            return $short_description; // Devolver original si falla la expansión
        }
        
        $expanded = trim($result['content']);
        $expanded = preg_replace('/\s+/', ' ', $expanded);
        
        // Si la expansión funcionó y está en rango, usarla
        if (strlen($expanded) >= 150 && strlen($expanded) <= 160) {
            return $expanded;
        }
        
        // Si no, devolver la descripción original (mejor corta que mala)
        return $short_description;
    }

    private function generateFAQWithAI($product_info)
    {
        $api_key = $this->config->get('smart_seo_schema_groq_api_key');
        
        if (!$api_key) {
            throw new Exception('No API key configured');
        }
        
        $max_tokens = (int)$this->config->get('smart_seo_schema_ai_max_tokens') ?: 800;
        $min_tokens = (int)$this->config->get('smart_seo_schema_ai_min_tokens_per_content') ?: 100;
        $faq_count = (int)$this->config->get('smart_seo_schema_faq_count') ?: 3;
        
        $existing_description = '';
        if (!empty($product_info['description'])) {
            $existing_description = strip_tags($product_info['description']);
            $existing_description = substr($existing_description, 0, 400);
        }
        
        $prompt = "Create {$faq_count} useful FAQ questions and answers for this product:\n\n";
        $prompt .= "Product: " . $product_info['name'] . "\n";
        $prompt .= "Model: " . $product_info['model'] . "\n";
        
        if ($existing_description) {
            $prompt .= "Description: " . $existing_description . "\n\n";
        }
        
        $prompt .= "Format each Q&A as:\n";
        $prompt .= "Q: [question]\n";
        $prompt .= "A: [detailed answer]\n\n";
        
        $prompt .= "Cover topics like:\n";
        $prompt .= "- Product specifications and compatibility\n";
        $prompt .= "- Installation or setup instructions\n";
        $prompt .= "- Common usage questions\n";
        $prompt .= "- Warranty or support information\n\n";
        
        $prompt .= "Write between {$min_tokens} and {$max_tokens} tokens. Return only the FAQ content:";
        
        $this->logDebug("Generando FAQ con {$min_tokens}-{$max_tokens} tokens, {$faq_count} preguntas");
        
        $model = $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant';
        $result = $this->callGroqAPIWithFallback($api_key, $model, $prompt, $max_tokens);
        
        if (!$result['success']) {
            throw new Exception('Failed to generate FAQ: ' . $result['error']);
        }
        
        return $result['content'];
    }

    private function generateHowToWithAI($product_info)
    {
        $api_key = $this->config->get('smart_seo_schema_groq_api_key');
        
        if (!$api_key) {
            throw new Exception('No API key configured');
        }
        
        $max_tokens = (int)$this->config->get('smart_seo_schema_ai_max_tokens') ?: 800;
        $min_tokens = (int)$this->config->get('smart_seo_schema_ai_min_tokens_per_content') ?: 100;
        $steps_count = (int)$this->config->get('smart_seo_schema_howto_steps_count') ?: 5;
        
        $existing_description = '';
        if (!empty($product_info['description'])) {
            $existing_description = strip_tags($product_info['description']);
            $existing_description = substr($existing_description, 0, 400);
        }
        
        $prompt = "Create step-by-step instructions for using this product:\n\n";
        $prompt .= "Product: " . $product_info['name'] . "\n";
        $prompt .= "Model: " . $product_info['model'] . "\n";
        
        if ($existing_description) {
            $prompt .= "Description: " . $existing_description . "\n\n";
        }
        
        $prompt .= "Create {$steps_count} clear, actionable steps for installation, setup, or usage.\n\n";
        
        $prompt .= "Format as:\n";
        $prompt .= "Step 1: [detailed instruction]\n";
        $prompt .= "Step 2: [detailed instruction]\n";
        $prompt .= "Continue for all steps...\n\n";
        
        $prompt .= "Focus on:\n";
        $prompt .= "- Clear, actionable instructions\n";
        $prompt .= "- Proper sequence and safety considerations\n";
        $prompt .= "- Practical tips and best practices\n\n";
        
        $prompt .= "Write between {$min_tokens} and {$max_tokens} tokens. Return only the step-by-step instructions:";
        
        $this->logDebug("Generando HowTo con {$min_tokens}-{$max_tokens} tokens, {$steps_count} pasos");
        
        $model = $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant';
        $result = $this->callGroqAPIWithFallback($api_key, $model, $prompt, $max_tokens);
        
        if (!$result['success']) {
            throw new Exception('Failed to generate HowTo: ' . $result['error']);
        }
        
        return $result['content'];
    }

    private function generateOthersContentData($product_info)
    {
        $others_data = array();
        
        // Generar automáticamente weight y dimensions desde la base de datos
        $additionalProperties = $this->generateAutomaticAdditionalProperties($product_info);
        
        if (!empty($additionalProperties)) {
            $others_data['additionalProperty'] = $additionalProperties;
        }
        
        return $others_data;
    }

    private function generateAutomaticAdditionalProperties($product_info)
    {
        $additionalProperties = array();
        
        // Obtener weight con unidad desde la base de datos
        if (!empty($product_info['weight']) && $product_info['weight'] > 0) {
            $weight_unit = $this->getWeightUnit($product_info['weight_class_id']);
            $additionalProperties[] = array(
                '@type' => 'PropertyValue',
                'name' => 'Weight',
                'value' => $product_info['weight'] . ' ' . $weight_unit
            );
        }
        
        // Obtener dimensions con unidad desde la base de datos
        $has_dimensions = (
            (!empty($product_info['length']) && $product_info['length'] > 0) ||
            (!empty($product_info['width']) && $product_info['width'] > 0) ||
            (!empty($product_info['height']) && $product_info['height'] > 0)
        );
        
        if ($has_dimensions) {
            $length_unit = $this->getLengthUnit($product_info['length_class_id']);
            $dimensions = trim(
                ($product_info['length'] ?? '0') . ' x ' . 
                ($product_info['width'] ?? '0') . ' x ' . 
                ($product_info['height'] ?? '0')
            );
            
            if ($dimensions !== '0 x 0 x 0') {
                $additionalProperties[] = array(
                    '@type' => 'PropertyValue',
                    'name' => 'Dimensions',
                    'value' => $dimensions . ' ' . $length_unit
                );
            }
        }
        
        return $additionalProperties;
    }

    private function getWeightUnit($weight_class_id)
    {
        if (empty($weight_class_id)) {
            return 'kg'; // default
        }
        
        $query = $this->db->query("
            SELECT unit 
            FROM " . DB_PREFIX . "weight_class_descriptions 
            WHERE weight_class_id = " . (int)$weight_class_id . " 
            AND language_id = " . (int)$this->getAdminDefaultLanguageId() . "
            LIMIT 1
        ");
        
        if ($query->num_rows) {
            return $query->row['unit'];
        }
        
        return 'kg'; // fallback
    }

    private function getLengthUnit($length_class_id)
    {
        if (empty($length_class_id)) {
            return 'cm'; // default
        }
        
        $query = $this->db->query("
            SELECT unit 
            FROM " . DB_PREFIX . "length_class_descriptions 
            WHERE length_class_id = " . (int)$length_class_id . " 
            AND language_id = " . (int)$this->getAdminDefaultLanguageId() . "
            LIMIT 1
        ");
        
        if ($query->num_rows) {
            return $query->row['unit'];
        }
        
        return 'cm'; // fallback
    }

    private function setupForm($product_id)
    {
        $form = new AForm('HT');
        $form->setForm(array(
            'form_name' => 'smart_seo_schema_form',
            'update' => ''
        ));

        $this->data['action'] = $this->html->getSecureURL('catalog/smart_seo_schema', '&product_id=' . $product_id);
        $this->data['cancel'] = $this->html->getSecureURL('catalog/product/update', '&product_id=' . $product_id);
        $this->data['form_title'] = $this->language->get('smart_seo_schema_name');
        $this->data['product_id'] = $product_id;

        $this->data['smart_seo_schema_groq_api_key'] = $this->config->get('smart_seo_schema_groq_api_key') ?: '';

        $this->data['form']['id'] = 'smart_seo_schema_form';
        $this->data['form']['form_open'] = $form->getFieldHtml(array(
            'type' => 'form',
            'name' => 'smart_seo_schema_form',
            'action' => $this->data['action'],
            'attr' => 'data-confirm-exit="true" class="aform form-horizontal"'
        ));

        $this->data['form']['submit'] = $form->getFieldHtml(array(
            'type' => 'button',
            'name' => 'submit',
            'text' => $this->language->get('button_save'),
            'style' => 'button1'
        ));

        $this->data['form']['cancel'] = $form->getFieldHtml(array(
            'type' => 'button',
            'name' => 'cancel',
            'text' => $this->language->get('button_cancel'),
            'style' => 'button2'
        ));

        $this->data['test_ai_button'] = $form->getFieldHtml(array(
            'type' => 'button',
            'name' => 'test_ai',
            'text' => $this->language->get('button_test_ai_connection'),
            'style' => 'btn btn-info',
            'attr' => 'type="button" onclick="testAIConnection()"'
        ));

        $this->data['preview_schema_button'] = $form->getFieldHtml(array(
            'type' => 'button',
            'name' => 'preview_schema',
            'text' => $this->language->get('button_preview_schema'),
            'style' => 'btn btn-success',
            'attr' => 'type="button" onclick="previewSchema()"'
        ));

        $this->setupSchemaFields($form);
    }

    private function setupSchemaFields($form)
    {
        $this->data['entry_custom_description'] = $this->language->get('entry_custom_description') ?: 'Custom Description (150-160 chars):';
        $this->data['entry_enable_variants'] = $this->language->get('entry_enable_variants') ?: 'Enable Product Variants:';
        $this->data['entry_faq_content'] = $this->language->get('entry_faq_content') ?: 'FAQ Content:';
        $this->data['entry_howto_content'] = $this->language->get('entry_howto_content') ?: 'HowTo Content:';
        $this->data['entry_review_content'] = $this->language->get('entry_review_content') ?: 'Review Content:';
        $this->data['entry_show_faq_tab_frontend'] = $this->language->get('entry_show_faq_tab_frontend') ?: 'Show FAQ Tab in Storefront:';
        $this->data['entry_show_howto_tab_frontend'] = $this->language->get('entry_show_howto_tab_frontend') ?: 'Show HowTo Tab in Storefront:';
        
        $this->data['text_section_basic'] = $this->language->get('text_section_basic') ?: 'Basic Settings';
        $this->data['text_section_ai'] = $this->language->get('text_section_ai') ?: 'AI Content Generation';
        
        $this->data['button_test_ai_connection'] = $this->language->get('button_test_ai_connection') ?: 'Test AI Connection';
        $this->data['button_preview_schema'] = $this->language->get('button_preview_schema') ?: 'Preview Schema';

        $this->data['form']['fields']['custom_description'] = $form->getFieldHtml(array(
            'type' => 'textarea',
            'name' => 'custom_description',
            'value' => $this->data['schema_settings']['custom_description'] ?? '',
            'style' => 'large-field'
        ));

        $this->data['form']['fields']['faq_content'] = $form->getFieldHtml(array(
            'type' => 'textarea',
            'name' => 'faq_content',
            'value' => $this->data['schema_settings']['faq_content'] ?? '',
            'style' => 'large-field'
        ));

        $this->data['form']['fields']['howto_content'] = $form->getFieldHtml(array(
            'type' => 'textarea',
            'name' => 'howto_content',
            'value' => $this->data['schema_settings']['howto_content'] ?? '',
            'style' => 'large-field'
        ));

        $this->data['form']['fields']['review_content'] = $form->getFieldHtml(array(
            'type' => 'textarea',
            'name' => 'review_content',
            'value' => $this->data['schema_settings']['review_content'] ?? '',
            'style' => 'large-field'
        ));

        $this->data['form']['fields']['enable_variants'] = $form->getFieldHtml(array(
            'type' => 'checkbox',
            'name' => 'enable_variants',
            'value' => 1,
            'checked' => $this->data['schema_settings']['enable_variants'] ? true : false
        ));

        $this->data['form']['fields']['show_faq_tab_frontend'] = $form->getFieldHtml(array(
            'type' => 'checkbox',
            'name' => 'show_faq_tab_frontend',
            'value' => 1,
            'checked' => $this->data['schema_settings']['show_faq_tab_frontend'] ? true : false
        ));

        $this->data['form']['fields']['show_howto_tab_frontend'] = $form->getFieldHtml(array(
            'type' => 'checkbox',
            'name' => 'show_howto_tab_frontend',
            'value' => 1,
            'checked' => $this->data['schema_settings']['show_howto_tab_frontend'] ? true : false
        ));
    }

    private function setupBreadcrumb($product_id)
    {
        $this->document->initBreadcrumb(array(
            'href' => $this->html->getSecureURL('index/home'),
            'text' => $this->language->get('text_home'),
            'separator' => false
        ));

        $this->document->addBreadcrumb(array(
            'href' => $this->html->getSecureURL('catalog/product'),
            'text' => $this->language->get('heading_title'),
            'separator' => ' :: '
        ));

        $this->document->addBreadcrumb(array(
            'href' => $this->html->getSecureURL('catalog/product/update', '&product_id=' . $product_id),
            'text' => $this->language->get('text_edit') . '&nbsp;' . $this->language->get('text_product'),
            'separator' => ' :: '
        ));

        $this->document->addBreadcrumb(array(
            'href' => $this->html->getSecureURL('catalog/smart_seo_schema', '&product_id=' . $product_id),
            'text' => $this->language->get('smart_seo_schema_name'),
            'separator' => ' :: ',
            'current' => true
        ));
    }

    private function getSchemaSettings($product_id)
    {
        $this->logDebug("Cargando configuración schema para producto: " . $product_id);
        
        try {
            $query = $this->db->query("
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
                    show_howto_tab_frontend,
                    created_date,
                    updated_date
                FROM " . DB_PREFIX . "seo_schema_content 
                WHERE product_id = " . (int)$product_id . "
                LIMIT 1
            ");

            if ($query->num_rows) {
                $settings = $query->row;
                
                $settings['enable_variants'] = (bool)$settings['enable_variants'];
                $settings['enable_faq'] = (bool)$settings['enable_faq'];
                $settings['enable_howto'] = (bool)$settings['enable_howto'];
                $settings['enable_review'] = (bool)$settings['enable_review'];
                $settings['show_faq_tab_frontend'] = (bool)$settings['show_faq_tab_frontend'];
                $settings['show_howto_tab_frontend'] = (bool)$settings['show_howto_tab_frontend'];
                
                $this->logDebug("Configuración cargada exitosamente. Campos con contenido: " . 
                    implode(', ', array_filter([
                        $settings['custom_description'] ? 'description' : null,
                        $settings['faq_content'] ? 'faq' : null,
                        $settings['howto_content'] ? 'howto' : null,
                        $settings['review_content'] ? 'review' : null,
                        $settings['others_content'] ? 'others' : null
                    ]))
                );
                
                return $settings;
            } else {
                $this->logDebug("No se encontró configuración para el producto. Retornando valores por defecto.");
                
                return array(
                    'custom_description' => '',
                    'faq_content' => '',
                    'howto_content' => '',
                    'review_content' => '',
                    'others_content' => '',
                    'enable_variants' => true,
                    'enable_faq' => false,
                    'enable_howto' => false,
                    'enable_review' => false,
                    'show_faq_tab_frontend' => false,
                    'show_howto_tab_frontend' => false
                );
            }
        } catch (Exception $e) {
            $this->logDebug("Error cargando configuración: " . $e->getMessage());
            
            return array(
                'custom_description' => '',
                'faq_content' => '',
                'howto_content' => '',
                'review_content' => '',
                'others_content' => '',
                'enable_variants' => true,
                'enable_faq' => false,
                'enable_howto' => false,
                'enable_review' => false,
                'show_faq_tab_frontend' => false,
                'show_howto_tab_frontend' => false
            );
        }
    }

    private function saveSchemaSettings($product_id)
    {
        $this->logDebug("=== GUARDANDO CONFIGURACIÓN SCHEMA ===");
        $this->logDebug("Producto ID: " . $product_id);
        $this->logDebug("POST data: " . print_r($this->request->post, true));
        
        try {
            $query = $this->db->query("
                SELECT id 
                FROM " . DB_PREFIX . "seo_schema_content 
                WHERE product_id = " . (int)$product_id . "
                LIMIT 1
            ");

            $others_content = trim($this->request->post['others_content'] ?? '');
            
            if (!empty($others_content)) {
                $others_content = html_entity_decode($others_content, ENT_QUOTES, 'UTF-8');
                
                $decoded = json_decode($others_content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    if (isset($decoded['additionalProperty'])) {
                        $validation = $this->validateAdditionalPropertyStructure($decoded['additionalProperty']);
                        if (!$validation['valid']) {
                            throw new Exception("Invalid additionalProperty: " . $validation['error']);
                        }
                    }
                    $others_content = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $this->logDebug("others_content normalizado: " . substr($others_content, 0, 200) . "...");
                } else {
                    $this->logDebug("Error JSON en others_content: " . json_last_error_msg());
                    throw new Exception("Invalid JSON format in others_content: " . json_last_error_msg());
                }
            }

            $data = array(
                'custom_description' => trim($this->request->post['custom_description'] ?? ''),
                'faq_content' => trim($this->request->post['faq_content'] ?? ''),
                'howto_content' => trim($this->request->post['howto_content'] ?? ''),
                'review_content' => trim($this->request->post['review_content'] ?? ''),
                'others_content' => $others_content,
                'enable_variants' => isset($this->request->post['enable_variants']) ? 1 : 0,
                'enable_faq' => !empty($this->request->post['faq_content']) ? 1 : 0,
                'enable_howto' => !empty($this->request->post['howto_content']) ? 1 : 0,
                'enable_review' => !empty($this->request->post['review_content']) ? 1 : 0,
                'show_faq_tab_frontend' => isset($this->request->post['show_faq_tab_frontend']) ? 1 : 0,
                'show_howto_tab_frontend' => isset($this->request->post['show_howto_tab_frontend']) ? 1 : 0
            );

            $this->logDebug("Datos procesados: " . print_r($data, true));

            if ($query->num_rows) {
                $this->logDebug("Actualizando registro existente...");
                
                $update_sql = "
                    UPDATE " . DB_PREFIX . "seo_schema_content 
                    SET 
                        custom_description = '" . $this->db->escape($data['custom_description']) . "',
                        faq_content = '" . $this->db->escape($data['faq_content']) . "',
                        howto_content = '" . $this->db->escape($data['howto_content']) . "',
                        review_content = '" . $this->db->escape($data['review_content']) . "',
                        others_content = '" . $this->db->escape($data['others_content']) . "',
                        enable_variants = " . (int)$data['enable_variants'] . ",
                        enable_faq = " . (int)$data['enable_faq'] . ",
                        enable_howto = " . (int)$data['enable_howto'] . ",
                        enable_review = " . (int)$data['enable_review'] . ",
                        show_faq_tab_frontend = " . (int)$data['show_faq_tab_frontend'] . ",
                        show_howto_tab_frontend = " . (int)$data['show_howto_tab_frontend'] . ",
                        updated_date = NOW()
                    WHERE product_id = " . (int)$product_id
                ;
                
                $this->db->query($update_sql);
                $this->logDebug("Registro actualizado exitosamente.");
                
            } else {
                $this->logDebug("Creando nuevo registro...");
                
                $insert_sql = "
                    INSERT INTO " . DB_PREFIX . "seo_schema_content 
                    (
                        product_id, 
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
                        show_howto_tab_frontend,
                        created_date,
                        updated_date
                    ) VALUES (
                        " . (int)$product_id . ",
                        '" . $this->db->escape($data['custom_description']) . "',
                        '" . $this->db->escape($data['faq_content']) . "',
                        '" . $this->db->escape($data['howto_content']) . "',
                        '" . $this->db->escape($data['review_content']) . "',
                        '" . $this->db->escape($data['others_content']) . "',
                        " . (int)$data['enable_variants'] . ",
                        " . (int)$data['enable_faq'] . ",
                        " . (int)$data['enable_howto'] . ",
                        " . (int)$data['enable_review'] . ",
                        " . (int)$data['show_faq_tab_frontend'] . ",
                        " . (int)$data['show_howto_tab_frontend'] . ",
                        NOW(),
                        NOW()
                    )
                ";
                
                $this->db->query($insert_sql);
                $this->logDebug("Nuevo registro creado exitosamente.");
            }

            $verify_query = $this->db->query("
                SELECT 
                    LENGTH(custom_description) as desc_len,
                    LENGTH(faq_content) as faq_len,
                    LENGTH(howto_content) as howto_len,
                    LENGTH(review_content) as review_len,
                    LENGTH(others_content) as others_len,
                    show_faq_tab_frontend,
                    show_howto_tab_frontend,
                    updated_date,
                    others_content
                FROM " . DB_PREFIX . "seo_schema_content 
                WHERE product_id = " . (int)$product_id
            );
            
            if ($verify_query->num_rows) {
                $verification = $verify_query->row;
                $this->logDebug("Verificación exitosa - Longitudes: desc={$verification['desc_len']}, faq={$verification['faq_len']}, howto={$verification['howto_len']}, review={$verification['review_len']}, others={$verification['others_len']}, show_faq_tab={$verification['show_faq_tab_frontend']}, show_howto_tab={$verification['show_howto_tab_frontend']}, updated={$verification['updated_date']}");
                
                if (!empty($verification['others_content'])) {
                    $saved_others = json_decode($verification['others_content'], true);
                    if (isset($saved_others['additionalProperty']) && is_array($saved_others['additionalProperty'])) {
                        $this->logDebug("✅ additionalProperty guardado correctamente como array con " . count($saved_others['additionalProperty']) . " elementos");
                    } else {
                        $this->logDebug("⚠️ additionalProperty no tiene estructura de array válida");
                    }
                }
            }

        } catch (Exception $e) {
            $this->logDebug("ERROR guardando configuración: " . $e->getMessage());
            $this->logDebug("SQL Error Info: " . $this->db->error);
            
            throw new Exception("Error saving schema settings: " . $e->getMessage());
        }
    }

    private function getProductReviews($product_id)
    {
        $query = $this->db->query("
            SELECT 
                review_id,
                product_id,
                customer_id,
                author,
                text,
                rating,
                verified_purchase,
                status,
                date_added,
                date_modified
            FROM " . DB_PREFIX . "reviews 
            WHERE product_id = " . (int)$product_id . " 
            AND status = 1
            ORDER BY date_added DESC
        ");

        return $query->rows;
    }

    private function getReviewVariationProfile($product_info)
    {
        $seed = crc32($product_info['name'] . $product_info['model']);
        srand($seed);
        
        $archetypes = [
            'satisfied' => [
                'tone' => 'positive',
                'focus' => 'general_satisfaction',
                'structure' => 'simple',
                'length_preference' => 'medium'
            ],
            'detailed' => [
                'tone' => 'informative',
                'focus' => 'specific_features',
                'structure' => 'two_paragraph',
                'length_preference' => 'long'
            ],
            'practical' => [
                'tone' => 'straightforward',
                'focus' => 'real_use',
                'structure' => 'single_paragraph',
                'length_preference' => 'medium'
            ],
            'experienced' => [
                'tone' => 'knowledgeable',
                'focus' => 'comparison_context',
                'structure' => 'two_paragraph',
                'length_preference' => 'long'
            ],
            'brief' => [
                'tone' => 'direct',
                'focus' => 'bottom_line',
                'structure' => 'single_paragraph',
                'length_preference' => 'short'
            ]
        ];
        
        $archetype_keys = array_keys($archetypes);
        $selected_archetype = $archetypes[$archetype_keys[rand(0, count($archetype_keys) - 1)]];
        
        $variations = [
            'mentions_timeframe' => (rand(1, 3) == 1),
            'includes_specifics' => (rand(1, 2) == 1),
            'personal_context' => (rand(1, 4) == 1),
            'minor_issue' => (rand(1, 5) == 1),
            'recommends' => (rand(1, 2) == 1),
            'compares' => (rand(1, 4) == 1)
        ];
        
        $paragraph_count = ($selected_archetype['structure'] === 'two_paragraph') ? 2 : 1;
        
        return [
            'archetype' => $selected_archetype,
            'variations' => $variations,
            'paragraph_count' => $paragraph_count,
            'seed' => $seed
        ];
    }

    private function optimizeReviewWithAI($review_text, $product_info)
    {
        $api_key = $this->config->get('smart_seo_schema_groq_api_key');
        
        if (!$api_key) {
            throw new Exception('No API key configured');
        }
        
        $profile = $this->getReviewVariationProfile($product_info);
        
        $prompt = $this->buildOptimizationPrompt($review_text, $product_info, $profile);
        
        $model = $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant';
        $result = $this->callGroqAPIWithFallback($api_key, $model, $prompt, 350);
        
        if (!$result['success']) {
            throw new Exception('Failed to optimize review: ' . $result['error']);
        }
        
        return $result['content'];
    }

    private function generateExampleReviewWithAI($product_info)
    {
        $api_key = $this->config->get('smart_seo_schema_groq_api_key');
        
        if (!$api_key) {
            throw new Exception('No API key configured');
        }
        
        $rating = rand(3, 5);
        $profile = $this->getReviewVariationProfile($product_info);
        
        $prompt = $this->buildGenerationPrompt($product_info, $rating, $profile);
        
        $model = $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant';
        $result = $this->callGroqAPIWithFallback($api_key, $model, $prompt, 450);
        
        if (!$result['success']) {
            throw new Exception('Failed to generate example review: ' . $result['error']);
        }
        
        return $this->parseReviewResponse($result['content'], $rating, $product_info['name']);
    }

    private function buildOptimizationPrompt($review_text, $product_info, $profile)
    {
        $archetype = $profile['archetype'];
        $variations = $profile['variations'];
        
        $prompt = "Optimize this product review to sound natural and helpful like a real customer wrote it:\n\n";
        $prompt .= "Product: " . $product_info['name'] . "\n";
        $prompt .= "Original review: " . $review_text . "\n\n";
        
        $prompt .= "REQUIREMENTS:\n";
        $prompt .= "• Length: 400-1800 characters total\n";
        $prompt .= "• Write in " . ($profile['paragraph_count'] == 2 ? '2 paragraphs' : '1 paragraph') . "\n";
        $prompt .= "• Use simple, natural language like people actually write\n";
        $prompt .= "• No bullet points, special formatting, or excessive punctuation\n";
        $prompt .= "• Keep the original rating sentiment\n";
        $prompt .= "• Sound like " . $archetype['tone'] . " customer focused on " . $archetype['focus'] . "\n";
        
        if ($variations['mentions_timeframe']) {
            $prompt .= "• Mention how long you've used/had the product\n";
        }
        
        if ($variations['includes_specifics']) {
            $prompt .= "• Include specific details about features or performance\n";
        }
        
        if ($variations['personal_context']) {
            $prompt .= "• Briefly mention why you needed this product\n";
        }
        
        if ($variations['minor_issue']) {
            $prompt .= "• Include one small realistic complaint to sound authentic\n";
        }
        
        if ($variations['recommends']) {
            $prompt .= "• Include whether you'd recommend it\n";
        }
        
        if ($variations['compares']) {
            $prompt .= "• Briefly compare to other similar products if relevant\n";
        }
        
        $prompt .= "\nWrite naturally like a real person posting an online review. No fancy formatting:";
        
        return $prompt;
    }

    private function buildGenerationPrompt($product_info, $rating, $profile)
    {
        $archetype = $profile['archetype'];
        $variations = $profile['variations'];
        
        $prompt = "Generate a realistic product review that sounds like a real customer wrote it:\n\n";
        $prompt .= "Product: " . $product_info['name'] . "\n";
        $prompt .= "Model: " . $product_info['model'] . "\n";
        
        if (!empty($product_info['description'])) {
            $description = strip_tags($product_info['description']);
            $prompt .= "Description: " . substr($description, 0, 300) . "\n";
        }
        
        $prompt .= "\nRating: " . $rating . " stars\n\n";
        
        $prompt .= "REQUIREMENTS:\n";
        $prompt .= "• Length: 400-1800 characters total\n";
        $prompt .= "• Write in " . ($profile['paragraph_count'] == 2 ? '2 paragraphs' : '1 paragraph') . "\n";
        $prompt .= "• Use natural everyday language like real customers use\n";
        $prompt .= "• No bullet points, special symbols, or fancy formatting\n";
        $prompt .= "• Sound " . $archetype['tone'] . " and focus on " . $archetype['focus'] . "\n";
        
        if ($variations['mentions_timeframe']) {
            $prompt .= "• Mention how long you've been using it\n";
        }
        
        if ($variations['includes_specifics']) {
            $prompt .= "• Include specific details about the product\n";
        }
        
        if ($variations['personal_context']) {
            $prompt .= "• Briefly explain why you bought this product\n";
        }
        
        if ($variations['minor_issue']) {
            $prompt .= "• Mention one small realistic issue for authenticity\n";
        }
        
        if ($variations['recommends']) {
            $prompt .= "• Say whether you'd recommend it to others\n";
        }
        
        if ($variations['compares']) {
            $prompt .= "• Compare it to similar products you know\n";
        }
        
        $simple_names = [
            'Mike', 'Sarah', 'Dave', 'Lisa', 'Tom', 'Amy', 'Chris', 'Kim', 
            'Jake', 'Jen', 'Sam', 'Alex', 'Pat', 'Jordan', 'Casey', 'Taylor'
        ];
        $selected_name = $simple_names[array_rand($simple_names)];
        
        $prompt .= "\nWrite like a normal person posting a review online. Return ONLY valid JSON:\n";
        $prompt .= '{"author": "' . $selected_name . '", "text": "review_text_here", "rating": ' . $rating . ', "verified_purchase": 1, "status": 1}';
        
        return $prompt;
    }

    private function parseReviewResponse($response, $rating, $product_name)
    {
        $clean_response = trim($response);
        $clean_response = preg_replace('/^[^{]*/', '', $clean_response);
        $clean_response = preg_replace('/[^}]*$/', '', $clean_response);
        
        $review_data = json_decode($clean_response, true);
        
        if (!$review_data || !isset($review_data['author']) || !isset($review_data['text']) || !isset($review_data['rating'])) {
            $this->logDebug("Failed to parse JSON response: " . $clean_response);
            
            $fallback_names = ['Verified Customer', 'Product User', 'Customer Review', 'Satisfied Buyer'];
            $fallback_texts = [
                "Great product! The " . $product_name . " works exactly as described and meets all my expectations. Good build quality and reliable performance. Used it for several weeks now and very satisfied with the purchase. Would definitely recommend to others looking for this type of product.",
                "Really impressed with this " . $product_name . ". Easy to use and delivers consistent results. Had it for about a month now and it's been working perfectly. The features are well-designed and practical. Minor complaint would be the packaging could be better, but the product itself is excellent.",
                "Solid choice for the price point. The " . $product_name . " has all the features I needed and works reliably. Setup was straightforward and it's been performing well in daily use. Would buy again and recommend to friends."
            ];
            
            $fallback_data = array(
                'author' => $fallback_names[array_rand($fallback_names)],
                'text' => $fallback_texts[array_rand($fallback_texts)],
                'rating' => $rating,
                'verified_purchase' => 1,
                'status' => 1
            );
            return $fallback_data;
        }
        
        $review_data['rating'] = (int)$review_data['rating'];
        $review_data['verified_purchase'] = isset($review_data['verified_purchase']) ? (int)$review_data['verified_purchase'] : 1;
        $review_data['status'] = isset($review_data['status']) ? (int)$review_data['status'] : 1;
        
        $text_length = strlen($review_data['text']);
        if ($text_length < 400 || $text_length > 1800) {
            $this->logDebug("Review text length out of range: " . $text_length . " characters");
            $fallback_data = array(
                'author' => $review_data['author'],
                'text' => $this->adjustReviewLength($review_data['text'], $product_name),
                'rating' => $review_data['rating'],
                'verified_purchase' => $review_data['verified_purchase'],
                'status' => $review_data['status']
            );
            return $fallback_data;
        }
        
        return $review_data;
    }

    private function adjustReviewLength($text, $product_name)
    {
        $current_length = strlen($text);
        
        if ($current_length < 100) {
            $expansions = [
                " The build quality feels solid and reliable.",
                " Setup was straightforward and user-friendly.", 
                " Been using it regularly and very satisfied with performance.",
                " Would definitely recommend to others.",
                " Good value for the price point."
            ];
            
            while (strlen($text) < 100 && !empty($expansions)) {
                $addition = array_shift($expansions);
                if (strlen($text . $addition) <= 200) {
                    $text .= $addition;
                }
            }
        } elseif ($current_length > 200) {
            $sentences = preg_split('/[.!?]+/', $text);
            $shortened = '';
            
            foreach ($sentences as $sentence) {
                $potential = $shortened . trim($sentence) . '.';
                if (strlen($potential) <= 200) {
                    $shortened = $potential;
                } else {
                    break;
                }
            }
            
            $text = $shortened ?: substr($text, 0, 197) . '...';
        }
        
        return $text;
    }

    private function createReview($product_id, $author, $text, $rating, $verified_purchase, $status)
    {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "reviews 
            (product_id, customer_id, author, text, rating, verified_purchase, status, date_added, date_modified) 
            VALUES (
                " . (int)$product_id . ",
                0,
                '" . $this->db->escape($author) . "',
                '" . $this->db->escape($text) . "',
                " . (int)$rating . ",
                " . (int)$verified_purchase . ",
                " . (int)$status . ",
                NOW(),
                NOW()
            )
        ");
    }

    private function updateReview($review_id, $author, $text, $rating, $verified_purchase, $status)
    {
        $this->db->query("
            UPDATE " . DB_PREFIX . "reviews 
            SET 
                author = '" . $this->db->escape($author) . "',
                text = '" . $this->db->escape($text) . "',
                rating = " . (int)$rating . ",
                verified_purchase = " . (int)$verified_purchase . ",
                status = " . (int)$status . ",
                date_modified = NOW()
            WHERE review_id = " . (int)$review_id
        );
    }

    private function getProductVariants($product_id)
    {
        $db = $this->db;
        $language_id = $this->getAdminDefaultLanguageId();

        $sql = "
            SELECT 
                pov.product_option_value_id,
                pov.sku,
                pov.price,
                pov.prefix,
                COALESCE(povd.name, CONCAT('Variant ', pov.product_option_value_id)) as variant_name
            FROM " . DB_PREFIX . "product_option_values pov
            LEFT JOIN " . DB_PREFIX . "product_option_value_descriptions povd 
                ON pov.product_option_value_id = povd.product_option_value_id 
                AND povd.language_id = " . (int)$language_id . "
            WHERE pov.product_id = " . (int)$product_id . "
            ORDER BY pov.product_option_value_id
            LIMIT 10
        ";

        $query = $db->query($sql);
        return $query->rows;
    }

    private function checkAIConnection()
    {
        $api_key = $this->config->get('smart_seo_schema_groq_api_key');
        return !empty($api_key);
    }

    private function getAdminDefaultLanguageId()
    {
        $query = $this->db->query("
            SELECT l.language_id 
            FROM " . DB_PREFIX . "settings s
            INNER JOIN " . DB_PREFIX . "languages l ON s.value = l.code
            WHERE s.key = 'admin_language'
            LIMIT 1
        ");
        
        if ($query->num_rows) {
            return (int)$query->row['language_id'];
        }
        
        return (int)$this->config->get('config_language_id');
    }

    protected function validateForm()
    {
        if (!$this->user->hasPermission('modify', 'catalog/smart_seo_schema')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    private function callGroqAPI($api_key, $model, $prompt, $max_tokens = null, $timeout = null)
    {
        $url = 'https://api.groq.com/openai/v1/chat/completions';
        
        if (!function_exists('curl_init')) {
            throw new Exception('cURL extension is not available on this server');
        }
        
        $tokens_to_use = $max_tokens ?: (int)$this->config->get('smart_seo_schema_ai_max_tokens') ?: 800;
        $timeout_to_use = $timeout ?: (int)$this->config->get('smart_seo_schema_ai_timeout') ?: 30;
        
        $data = [
            'model' => $model,
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'max_tokens' => $tokens_to_use,
            'temperature' => (float)$this->config->get('smart_seo_schema_ai_temperature') ?: 0.7
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key,
            'User-Agent: AbanteCart-SmartSEOSchema/2.0'
        ];

        $this->logDebug("=== LLAMADA API ===");
        $this->logDebug("URL: " . $url);
        $this->logDebug("Modelo: " . $model);
        $this->logDebug("Max tokens: " . $tokens_to_use);
        $this->logDebug("Timeout: " . $timeout_to_use . "s");
        $this->logDebug("Temperature: " . $data['temperature']);
        $this->logDebug("Prompt length: " . strlen($prompt) . " caracteres");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout_to_use);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        $curl_info = curl_getinfo($ch);
        curl_close($ch);

        $this->logDebug("=== RESPUESTA API ===");
        $this->logDebug("HTTP Code: " . $http_code);
        $this->logDebug("Response length: " . strlen($response) . " caracteres");
        $this->logDebug("cURL Error: " . ($curl_error ?: 'None'));
        $this->logDebug("Total time: " . $curl_info['total_time'] . "s");

        if ($curl_error) {
            throw new Exception("cURL Error: " . $curl_error);
        }

        if ($response === false) {
            throw new Exception("cURL failed to get response");
        }

        if ($http_code == 200 && $response) {
            $decoded = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("JSON decode error: " . json_last_error_msg() . ". Response: " . substr($response, 0, 200));
            }
            
            if (isset($decoded['choices'][0]['message']['content'])) {
                $content = $decoded['choices'][0]['message']['content'];
                $this->logDebug("Contenido extraído exitosamente: " . strlen($content) . " caracteres");
                
                if (isset($decoded['usage'])) {
                    $usage = $decoded['usage'];
                    $this->logDebug("Tokens utilizados: prompt=" . ($usage['prompt_tokens'] ?? 'N/A') . 
                                  ", completion=" . ($usage['completion_tokens'] ?? 'N/A') . 
                                  ", total=" . ($usage['total_tokens'] ?? 'N/A'));
                }
                
                return $content;
            } else {
                $this->logDebug("Estructura de respuesta inesperada: " . print_r($decoded, true));
                throw new Exception("Unexpected response structure from API. Missing choices[0].message.content");
            }
        }

        $error_details = null;
        if ($response) {
            $error_details = json_decode($response, true);
        }

        $is_server_error = ($http_code >= 500 && $http_code <= 503) || $http_code == 0;
        if ($is_server_error) {
            $this->logDebug("Error de servidor detectado (HTTP {$http_code}) - candidato para fallback");
        }

        switch ($http_code) {
            case 400:
                $error_msg = "Bad Request. Model '{$model}' may not exist or request is malformed.";
                if ($error_details && isset($error_details['error']['message'])) {
                    $error_msg .= " API Error: " . $error_details['error']['message'];
                }
                $error_msg .= " Check available models at https://console.groq.com/docs/models";
                throw new Exception($error_msg);
                
            case 401:
                $error_msg = "Unauthorized. Invalid API key.";
                if ($error_details && isset($error_details['error']['message'])) {
                    $error_msg .= " API Error: " . $error_details['error']['message'];
                }
                throw new Exception($error_msg);
                
            case 429:
                $error_msg = "Rate limit exceeded. Please try again later.";
                if ($error_details && isset($error_details['error']['message'])) {
                    $error_msg .= " API Error: " . $error_details['error']['message'];
                }
                throw new Exception($error_msg);
                
            case 422:
                $error_msg = "Unprocessable Entity. Model '{$model}' may not be available.";
                if ($error_details && isset($error_details['error']['message'])) {
                    $error_msg .= " API Error: " . $error_details['error']['message'];
                }
                $error_msg .= " Check https://console.groq.com/docs/models";
                throw new Exception($error_msg);
                
            case 500:
            case 502:
            case 503:
                $error_msg = "Server Error (HTTP {$http_code}). Temporary server issue.";
                if ($error_details && isset($error_details['error']['message'])) {
                    $error_msg .= " API Error: " . $error_details['error']['message'];
                }
                throw new Exception($error_msg);
                
            case 0:
                throw new Exception("Network error: Could not connect to Groq API. Check internet connection and firewall.");
                
            default:
                $error_msg = "API call failed with HTTP code: " . $http_code;
                if ($error_details && isset($error_details['error']['message'])) {
                    $error_msg .= " - " . $error_details['error']['message'];
                } elseif ($response) {
                    $error_msg .= " - Response: " . substr($response, 0, 200);
                }
                throw new Exception($error_msg);
        }
    }

    private function logDebug($message)
    {
        try {
            $log_file = DIR_LOGS . 'smart_seo_schema_debug.log';
            $timestamp = date('Y-m-d H:i:s');
            $log_entry = "[{$timestamp}] {$message}" . PHP_EOL;
            
            $log_dir = dirname($log_file);
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0755, true);
            }
            
            file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            try {
                if ($this->config && $this->config->get('smart_seo_schema_debug_mode')) {
                    $warning = new AWarning('Smart SEO Schema Debug: ' . $message);
                    $warning->toLog();
                }
            } catch (Exception $e2) {
                error_log("Smart SEO Schema Debug: " . $message);
            }
        }
    }
}