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
                $this->logDebug("Llamando a Groq API...");
                $response = $this->callGroqAPI($api_key, $model, 'Test connection - please respond with "Connection successful"', 50);
                
                if ($response) {
                    $json = array(
                        'error' => false,
                        'message' => "Connection successful! Model '{$model}' is working properly. Response: " . substr($response, 0, 100) . "...",
                        'response_length' => strlen($response)
                    );
                    $this->logDebug("Éxito: " . $json['message']);
                } else {
                    $json = array(
                        'error' => true,
                        'message' => "Connection failed. No response from API. Check your API key and model '{$model}'.",
                        'debug' => 'Empty response from API'
                    );
                    $this->logDebug("Error: Sin respuesta de la API");
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
                    $json = array(
                        'error' => false,
                        'valid' => true,
                        'message' => 'Valid JSON format!',
                        'parsed_data' => $decoded
                    );
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
        
        $response = $this->callGroqAPI(
            $api_key,
            $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant',
            $prompt,
            100 // Tokens limitados para forzar concisión
        );
        
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
        
        $expanded = $this->callGroqAPI(
            $api_key,
            $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant',
            $expand_prompt,
            80
        );
        
        $expanded = trim($expanded);
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
        
        return $this->callGroqAPI(
            $api_key,
            $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant',
            $prompt,
            $max_tokens
        );
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
        
        return $this->callGroqAPI(
            $api_key,
            $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant',
            $prompt,
            $max_tokens
        );
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

            $data = array(
                'custom_description' => trim($this->request->post['custom_description'] ?? ''),
                'faq_content' => trim($this->request->post['faq_content'] ?? ''),
                'howto_content' => trim($this->request->post['howto_content'] ?? ''),
                'review_content' => trim($this->request->post['review_content'] ?? ''),
                'others_content' => trim($this->request->post['others_content'] ?? ''),
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
                    updated_date
                FROM " . DB_PREFIX . "seo_schema_content 
                WHERE product_id = " . (int)$product_id
            );
            
            if ($verify_query->num_rows) {
                $verification = $verify_query->row;
                $this->logDebug("Verificación exitosa - Longitudes: desc={$verification['desc_len']}, faq={$verification['faq_len']}, howto={$verification['howto_len']}, review={$verification['review_len']}, others={$verification['others_len']}, show_faq_tab={$verification['show_faq_tab_frontend']}, show_howto_tab={$verification['show_howto_tab_frontend']}, updated={$verification['updated_date']}");
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

    /**
     * Sistema de diversificación de reviews para evitar patrones repetitivos
     * Implementa múltiples arquetipos de reviewer y variaciones naturales
     */
    private function getReviewVariationProfile($product_info)
    {
        // Crear semilla determinística basada en el producto para consistencia
        $seed = crc32($product_info['name'] . $product_info['model']);
        srand($seed);
        
        // Seleccionar arquetipo de reviewer
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
        
        // Variaciones naturales sin formateo especial
        $variations = [
            'mentions_timeframe' => (rand(1, 3) == 1),  // 33% chance
            'includes_specifics' => (rand(1, 2) == 1),  // 50% chance
            'personal_context' => (rand(1, 4) == 1),    // 25% chance
            'minor_issue' => (rand(1, 5) == 1),         // 20% chance
            'recommends' => (rand(1, 2) == 1),          // 50% chance
            'compares' => (rand(1, 4) == 1)             // 25% chance
        ];
        
        // Determinar estructura de párrafos
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
        
        // Obtener perfil de variación para mantener consistencia
        $profile = $this->getReviewVariationProfile($product_info);
        
        $prompt = $this->buildOptimizationPrompt($review_text, $product_info, $profile);
        
        return $this->callGroqAPI(
            $api_key,
            $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant',
            $prompt,
            350
        );
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
        
        $response = $this->callGroqAPI(
            $api_key,
            $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant',
            $prompt,
            450
        );
        
        return $this->parseReviewResponse($response, $rating, $product_info['name']);
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
        
        // Nombres simples y realistas
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
            
            // Fallback con variación
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
        
        // Validar y limpiar datos
        $review_data['rating'] = (int)$review_data['rating'];
        $review_data['verified_purchase'] = isset($review_data['verified_purchase']) ? (int)$review_data['verified_purchase'] : 1;
        $review_data['status'] = isset($review_data['status']) ? (int)$review_data['status'] : 1;
        
        // Validar longitud del texto
        $text_length = strlen($review_data['text']);
        if ($text_length < 400 || $text_length > 1800) {
            $this->logDebug("Review text length out of range: " . $text_length . " characters");
            // Usar fallback si está fuera del rango óptimo
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
            // Expandir texto corto
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
            // Recortar texto largo manteniendo sentido
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

        // Validar JSON antes de guardar
        $others_content = trim($this->request->post['others_content'] ?? '');
        if (!empty($others_content)) {
            $decoded = json_decode($others_content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error['others_content'] = 'Invalid JSON format: ' . json_last_error_msg();
            }
        }

        return !$this->error;
    }

    private function callGroqAPI($api_key, $model, $prompt, $max_tokens = null)
    {
        $url = 'https://api.groq.com/openai/v1/chat/completions';
        
        if (!function_exists('curl_init')) {
            throw new Exception('cURL extension is not available on this server');
        }
        
        $tokens_to_use = $max_tokens ?: (int)$this->config->get('smart_seo_schema_ai_max_tokens') ?: 800;
        
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

        $this->logDebug("=== LLAMADA API INDIVIDUAL ===");
        $this->logDebug("URL: " . $url);
        $this->logDebug("Modelo: " . $model);
        $this->logDebug("Max tokens: " . $tokens_to_use);
        $this->logDebug("Temperature: " . $data['temperature']);
        $this->logDebug("Prompt length: " . strlen($prompt) . " caracteres");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int)$this->config->get('smart_seo_schema_ai_timeout') ?: 30);
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