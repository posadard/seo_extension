<?php
/*------------------------------------------------------------------------------
  Smart SEO Schema Assistant - Admin Controller CORREGIDO
  
  Controller for product tab integration in AbanteCart admin
  Handles Schema.org configuration per product with AI assistance
  VERSIÓN CON MANEJO ROBUSTO DE JSON Y ERRORES COMPLETAMENTE CORREGIDA
------------------------------------------------------------------------------*/

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

/**
 * Class ControllerPagesCatalogSmartSeoSchema
 * 
 * @property ModelExtensionSmartSeoSchema $model_extension_smart_seo_schema
 * @property ModelCatalogProduct $model_catalog_product
 */
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
        
        // Get product info
        $product_info = $this->model_catalog_product->getProduct($product_id);
        $this->data['product_description'] = $this->model_catalog_product->getProductDescriptions($product_id);
        $this->data['heading_title'] = $this->language->get('text_edit') . '&nbsp;' . $this->language->get('text_product');

        $this->view->assign('error', $this->error);
        $this->view->assign('success', $this->session->data['success']);
        if (isset($this->session->data['success'])) {
            unset($this->session->data['success']);
        }

        // Breadcrumb
        $this->setupBreadcrumb($product_id);

        // Load tabs controller
        $this->data['active'] = 'smart_seo_schema';
        $tabs_obj = $this->dispatch('pages/catalog/product_tabs', array($this->data));
        $this->data['product_tabs'] = $tabs_obj->dispatchGetOutput();
        unset($tabs_obj);

        // Get current schema settings
        $this->data['schema_settings'] = $this->getSchemaSettings($product_id);
        
        // Get product variants for hasVariant[] preview
        $this->data['product_variants'] = $this->getProductVariants($product_id);
        
        // AI Status check
        $this->data['ai_status'] = $this->checkAIConnection();
        
        // Setup form
        $this->setupForm($product_id);

        // Add child controllers
        $this->addChild('pages/catalog/product_summary', 'summary_form', 'pages/catalog/product_summary.tpl');

        $this->view->batchAssign($this->data);
        $this->processTemplate('pages/smart_seo_schema/smart_seo_schema_form.tpl');
    }

    /**
     * AJAX endpoint para testing AI connection - CORREGIDO
     */
    public function testAIConnection()
    {
        // Limpiar cualquier output previo
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Forzar JSON headers
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $this->loadLanguage('smart_seo_schema/smart_seo_schema');
            
            $api_key = $this->config->get('smart_seo_schema_groq_api_key');
            $model = $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant';
            
            $json = array();
            
            // Log inicial del test
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
                $response = $this->callGroqAPI($api_key, $model, 'Test connection - please respond with "Connection successful"');
                
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

        // Asegurar output JSON válido
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit(); // Importante: terminar aquí para evitar HTML adicional
    }

    /**
     * AJAX endpoint for generating AI content - CORREGIDO COMPLETAMENTE
     */
    public function generateAIContent()
    {
        // Limpiar cualquier output previo
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Forzar JSON headers
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $product_id = $this->request->get['product_id'];
            $content_type = $this->request->post['content_type']; // description, custom_description, faq, howto, review
            
            $this->logDebug("=== GENERANDO CONTENIDO IA ===");
            $this->logDebug("Tipo: " . $content_type . ", Producto: " . $product_id);
            $this->logDebug("POST data: " . print_r($this->request->post, true));
            
            // Validación básica
            if (!$product_id || !$content_type) {
                throw new Exception('Missing required parameters: product_id=' . $product_id . ', content_type=' . $content_type);
            }
            
            $this->loadModel('catalog/product');
            $product_info = $this->model_catalog_product->getProduct($product_id);
            
            if (!$product_info) {
                throw new Exception('Product not found: ' . $product_id);
            }
            
            $this->logDebug("Producto encontrado: " . $product_info['name']);
            
            $json = array();
            
            // MAPEO CORRECTO DE CONTENT_TYPE
            switch ($content_type) {
                case 'description':
                case 'custom_description':
                    $content = $this->generateAIDescription($product_info);
                    break;
                case 'faq':
                    $content = $this->generateAIFAQ($product_info);
                    break;
                case 'howto':
                    $content = $this->generateAIHowTo($product_info);
                    break;
                case 'review':
                    $content = $this->generateAIReview($product_info);
                    break;
                default:
                    throw new Exception('Invalid content type: ' . $content_type . '. Valid types: description, custom_description, faq, howto, review');
            }
            
            if (empty($content)) {
                throw new Exception('AI generated empty content. Please try again or check your API configuration.');
            }
            
            $json = array(
                'error' => false,
                'content' => $content,
                'content_length' => strlen($content),
                'content_type' => $content_type,
                'product_name' => $product_info['name'],
                'timestamp' => date('Y-m-d H:i:s')
            );
            
            $this->logDebug("Contenido generado exitosamente - Longitud: " . strlen($content));
            $this->logDebug("Primeros 100 chars: " . substr($content, 0, 100));
            
        } catch (Exception $e) {
            $json = array(
                'error' => true,
                'message' => $e->getMessage(),
                'debug' => array(
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ),
                'timestamp' => date('Y-m-d H:i:s')
            );
            $this->logDebug("Error generando contenido: " . $e->getMessage());
            $this->logDebug("Stack trace: " . $e->getTraceAsString());
        } catch (Error $e) {
            $json = array(
                'error' => true,
                'message' => "Fatal error: " . $e->getMessage(),
                'debug' => array(
                    'error' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ),
                'timestamp' => date('Y-m-d H:i:s')
            );
            $this->logDebug("Error fatal: " . $e->getMessage());
        }

        // Asegurar output JSON válido
        echo json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit(); // Importante: terminar aquí para evitar HTML adicional
    }

    /**
     * AJAX endpoint for variants preview - CORREGIDO
     */
    public function getVariants()
    {
        // Limpiar cualquier output previo
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Forzar JSON headers
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

    /**
     * AJAX endpoint for schema preview - CORREGIDO
     */
    public function previewSchema()
    {
        // Limpiar cualquier output previo
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Forzar JSON headers
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
            
            // Generate complete schema using extension logic
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

    /**
     * Setup form fields and actions
     */
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

        // Incluir configuraciones para el frontend
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

        // AI test button
        $this->data['test_ai_button'] = $form->getFieldHtml(array(
            'type' => 'button',
            'name' => 'test_ai',
            'text' => $this->language->get('button_test_ai_connection'),
            'style' => 'btn btn-info',
            'attr' => 'type="button" onclick="testAIConnection()"'
        ));

        // Schema preview button
        $this->data['preview_schema_button'] = $form->getFieldHtml(array(
            'type' => 'button',
            'name' => 'preview_schema',
            'text' => $this->language->get('button_preview_schema'),
            'style' => 'btn btn-success',
            'attr' => 'type="button" onclick="previewSchema()"'
        ));

        // Setup form fields for schema configuration
        $this->setupSchemaFields($form);
    }

    /**
     * Setup schema configuration fields
     */
    private function setupSchemaFields($form)
    {
        // Language entries for form
        $this->data['entry_custom_description'] = $this->language->get('entry_custom_description') ?: 'Custom Description:';
        $this->data['entry_enable_variants'] = $this->language->get('entry_enable_variants') ?: 'Enable Product Variants:';
        $this->data['entry_faq_content'] = $this->language->get('entry_faq_content') ?: 'FAQ Content:';
        $this->data['entry_howto_content'] = $this->language->get('entry_howto_content') ?: 'HowTo Content:';
        $this->data['entry_review_content'] = $this->language->get('entry_review_content') ?: 'Review Content:';
        
        $this->data['text_section_basic'] = $this->language->get('text_section_basic') ?: 'Basic Settings';
        $this->data['text_section_ai'] = $this->language->get('text_section_ai') ?: 'AI Content Generation';
        
        $this->data['button_test_ai_connection'] = $this->language->get('button_test_ai_connection') ?: 'Test AI Connection';
        $this->data['button_preview_schema'] = $this->language->get('button_preview_schema') ?: 'Preview Schema';

        // Custom description field
        $this->data['form']['fields']['custom_description'] = $form->getFieldHtml(array(
            'type' => 'textarea',
            'name' => 'custom_description',
            'value' => $this->data['schema_settings']['custom_description'] ?? '',
            'style' => 'large-field'
        ));

        // FAQ fields
        $this->data['form']['fields']['faq_content'] = $form->getFieldHtml(array(
            'type' => 'textarea',
            'name' => 'faq_content',
            'value' => $this->data['schema_settings']['faq_content'] ?? '',
            'style' => 'large-field'
        ));

        // HowTo fields
        $this->data['form']['fields']['howto_content'] = $form->getFieldHtml(array(
            'type' => 'textarea',
            'name' => 'howto_content',
            'value' => $this->data['schema_settings']['howto_content'] ?? '',
            'style' => 'large-field'
        ));

        // Review fields
        $this->data['form']['fields']['review_content'] = $form->getFieldHtml(array(
            'type' => 'textarea',
            'name' => 'review_content',
            'value' => $this->data['schema_settings']['review_content'] ?? '',
            'style' => 'large-field'
        ));

        // Additional schema options
        $this->data['form']['fields']['enable_variants'] = $form->getFieldHtml(array(
            'type' => 'checkbox',
            'name' => 'enable_variants',
            'value' => $this->data['schema_settings']['enable_variants'] ?? 1
        ));
    }

    /**
     * Setup breadcrumb navigation
     */
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

    /**
     * Get current schema settings for product
     */
    private function getSchemaSettings($product_id)
    {
        // Implementation to retrieve custom schema settings from database
        // For now, return empty array - would need custom table for per-product settings
        return array();
    }

    /**
     * Save schema settings for product
     */
    private function saveSchemaSettings($product_id)
    {
        // Implementation to save custom schema settings to database
        // Would need custom table for per-product schema configurations
    }

    /**
     * Get product variants for preview
     */
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

    /**
     * Check AI connection status
     */
    private function checkAIConnection()
    {
        $api_key = $this->config->get('smart_seo_schema_groq_api_key');
        return !empty($api_key);
    }

    /**
     * Get admin default language ID
     */
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

    /**
     * Validate form data
     */
    protected function validateForm()
    {
        if (!$this->user->hasPermission('modify', 'catalog/smart_seo_schema')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    /**
     * Generate AI description - MEJORADO
     */
    private function generateAIDescription($product_info)
    {
        $this->logDebug("Generando descripción IA para: " . $product_info['name']);
        
        // Crear prompt más específico y detallado
        $existing_description = '';
        if (!empty($product_info['description'])) {
            $existing_description = strip_tags($product_info['description']);
            $existing_description = substr($existing_description, 0, 300); // Limitar longitud
        }
        
        $prompt = "Create an SEO-optimized product description for the product: " . $product_info['name'] . ". ";
        
        if ($existing_description) {
            $prompt .= "Here's the current description for reference: " . $existing_description . ". ";
        }
        
        $prompt .= "Please create a new, improved description that:
1. Is SEO-friendly and keyword-rich
2. Highlights key features and benefits
3. Is between 100-200 words
4. Uses professional, clear language
5. Includes technical details when relevant
6. Focuses on what makes this product unique

Write only the description, no additional commentary.";

        $this->logDebug("Prompt enviado: " . substr($prompt, 0, 200) . "...");
        
        return $this->callGroqAPI(
            $this->config->get('smart_seo_schema_groq_api_key'),
            $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant',
            $prompt
        );
    }

    /**
     * Generate AI FAQ - MEJORADO
     */
    private function generateAIFAQ($product_info)
    {
        $this->logDebug("Generando FAQ IA para: " . $product_info['name']);
        
        $count = $this->config->get('smart_seo_schema_faq_count') ?: 3;
        
        $prompt = "Create " . $count . " frequently asked questions and answers for the product: " . $product_info['name'] . ". 

Format each Q&A as:
Q: [Question]
A: [Answer]

Make the questions realistic, practical, and focused on:
1. Product specifications
2. Usage instructions
3. Compatibility/requirements
4. Benefits and features
5. Shipping/availability

Keep answers concise but informative (2-3 sentences each). Write only the Q&A pairs, no additional commentary.";

        return $this->callGroqAPI(
            $this->config->get('smart_seo_schema_groq_api_key'),
            $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant',
            $prompt
        );
    }

    /**
     * Generate AI HowTo - MEJORADO
     */
    private function generateAIHowTo($product_info)
    {
        $this->logDebug("Generando HowTo IA para: " . $product_info['name']);
        
        $steps = $this->config->get('smart_seo_schema_howto_steps_count') ?: 5;
        
        $prompt = "Create " . $steps . " step-by-step instructions for using/installing/setting up: " . $product_info['name'] . ". 

Format as:
Step 1: [Instruction]
Step 2: [Instruction]
etc.

Make the instructions:
1. Clear and actionable
2. Logically ordered
3. Include safety considerations if relevant
4. Mention tools/requirements if needed
5. Be specific and practical

Write only the numbered steps, no additional commentary.";

        return $this->callGroqAPI(
            $this->config->get('smart_seo_schema_groq_api_key'),
            $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant',
            $prompt
        );
    }

    /**
     * Generate AI Review - MEJORADO
     */
    private function generateAIReview($product_info)
    {
        $this->logDebug("Generando Review IA para: " . $product_info['name']);
        
        $prompt = "Write a professional, objective product review for: " . $product_info['name'] . ". 

The review should include:
1. Overall assessment
2. Key strengths/pros
3. Any limitations/cons (if relevant)
4. Best use cases
5. Value for money consideration
6. Professional recommendation

Keep it balanced, informative, and around 150-200 words. Write in a professional, third-person style as if from a technical expert. Write only the review text, no additional commentary.";

        return $this->callGroqAPI(
            $this->config->get('smart_seo_schema_groq_api_key'),
            $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant',
            $prompt
        );
    }

    /**
     * Método mejorado para llamar a Groq API con debugging completo
     */
    private function callGroqAPI($api_key, $model, $prompt)
    {
        $url = 'https://api.groq.com/openai/v1/chat/completions';
        
        // Verificar que cURL esté disponible
        if (!function_exists('curl_init')) {
            throw new Exception('cURL extension is not available on this server');
        }
        
        $data = [
            'model' => $model,
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'max_tokens' => (int)$this->config->get('smart_seo_schema_ai_max_tokens') ?: 200,
            'temperature' => (float)$this->config->get('smart_seo_schema_ai_temperature') ?: 0.7
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key,
            'User-Agent: AbanteCart-SmartSEOSchema/2.0'
        ];

        $this->logDebug("Preparando llamada a API:");
        $this->logDebug("URL: " . $url);
        $this->logDebug("Modelo: " . $model);
        $this->logDebug("Max tokens: " . $data['max_tokens']);
        $this->logDebug("Temperature: " . $data['temperature']);
        $this->logDebug("Prompt length: " . strlen($prompt));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int)$this->config->get('smart_seo_schema_ai_timeout') ?: 15);
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

        $this->logDebug("Respuesta de cURL:");
        $this->logDebug("HTTP Code: " . $http_code);
        $this->logDebug("Response length: " . strlen($response));
        $this->logDebug("cURL Error: " . ($curl_error ?: 'None'));
        $this->logDebug("Total time: " . $curl_info['total_time'] . "s");
        $this->logDebug("Connect time: " . $curl_info['connect_time'] . "s");

        if ($curl_error) {
            throw new Exception("cURL Error: " . $curl_error);
        }

        if ($response === false) {
            throw new Exception("cURL failed to get response");
        }

        if ($response) {
            $this->logDebug("Raw response (first 500 chars): " . substr($response, 0, 500));
        }

        if ($http_code == 200 && $response) {
            $decoded = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("JSON decode error: " . json_last_error_msg() . ". Response: " . substr($response, 0, 200));
            }
            
            if (isset($decoded['choices'][0]['message']['content'])) {
                $content = $decoded['choices'][0]['message']['content'];
                $this->logDebug("Contenido extraído exitosamente: " . strlen($content) . " caracteres");
                return $content;
            } else {
                $this->logDebug("Estructura de respuesta inesperada: " . print_r($decoded, true));
                throw new Exception("Unexpected response structure from API. Missing choices[0].message.content");
            }
        }

        // Enhanced error handling with response details
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

    /**
     * Método de logging mejorado
     */
    private function logDebug($message)
    {
        try {
            // Log a archivo específico
            $log_file = DIR_LOGS . 'smart_seo_schema_debug.log';
            $timestamp = date('Y-m-d H:i:s');
            $log_entry = "[{$timestamp}] {$message}" . PHP_EOL;
            
            // Crear directorio si no existe
            $log_dir = dirname($log_file);
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0755, true);
            }
            
            file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            // Si falla el logging a archivo, intentar con AbanteCart
            try {
                if ($this->config && $this->config->get('smart_seo_schema_debug_mode')) {
                    $warning = new AWarning('Smart SEO Schema Debug: ' . $message);
                    $warning->toLog();
                }
            } catch (Exception $e2) {
                // Si todo falla, al menos escribir a error_log de PHP
                error_log("Smart SEO Schema Debug: " . $message);
            }
        }
    }
}