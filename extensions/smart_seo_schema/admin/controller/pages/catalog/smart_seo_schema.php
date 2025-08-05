<?php
/*------------------------------------------------------------------------------
  Smart SEO Schema Assistant - Admin Controller OPTIMIZADO CON DIVISIÓN DE TOKENS
  
  Controller for product tab integration in AbanteCart admin
  Handles Schema.org configuration per product with AI assistance
  VERSIÓN CON DIVISIÓN INTELIGENTE DE TOKENS Y PERSISTENCIA DE DATOS
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

        // Asegurar output JSON válido
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit(); // Importante: terminar aquí para evitar HTML adicional
    }

    /**
     * AJAX endpoint para generar múltiple contenido IA con DIVISIÓN INTELIGENTE DE TOKENS
     */
    public function generateMultipleAIContent()
    {
        // Limpiar cualquier output previo
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Forzar JSON headers
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $product_id = $this->request->get['product_id'];
            $content_types = $this->request->post['content_types']; // Array de tipos
            
            $this->logDebug("=== GENERANDO CONTENIDO MÚLTIPLE IA CON DIVISIÓN DE TOKENS ===");
            $this->logDebug("Tipos solicitados: " . implode(', ', $content_types));
            $this->logDebug("Producto: " . $product_id);
            
            // Validación básica
            if (!$product_id || !is_array($content_types) || empty($content_types)) {
                throw new Exception('Missing required parameters or invalid content_types array');
            }
            
            $this->loadModel('catalog/product');
            $product_info = $this->model_catalog_product->getProduct($product_id);
            
            if (!$product_info) {
                throw new Exception('Product not found: ' . $product_id);
            }
            
            $this->logDebug("Producto encontrado: " . $product_info['name']);
            
            // Generar contenido múltiple con división inteligente de tokens
            $generated_content = $this->generateMultipleAIContentWithTokenDivision($product_info, $content_types);
            
            if (empty($generated_content)) {
                throw new Exception('AI generated empty content for all requested types');
            }
            
            $json = array(
                'error' => false,
                'content' => $generated_content,
                'content_types' => $content_types,
                'product_name' => $product_info['name'],
                'timestamp' => date('Y-m-d H:i:s')
            );
            
            $this->logDebug("Contenido múltiple generado exitosamente - Tipos: " . implode(', ', array_keys($generated_content)));
            
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
            $this->logDebug("Error generando contenido múltiple: " . $e->getMessage());
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
     * AJAX endpoint for schema preview - CORREGIDO CON OTHERS_CONTENT
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
     * NUEVO: AJAX endpoint para generar y actualizar others_content automáticamente
     */
    public function generateOthersContent()
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
            
            // Generar others_content con datos técnicos y de Rich Results
            $others_content = $this->generateOthersContentData($product_info);
            
            // Guardar en base de datos
            $this->updateOthersContent($product_id, $others_content);
            
            $json = array(
                'error' => false,
                'message' => 'Others content generated and saved successfully',
                'others_content' => $others_content,
                'timestamp' => date('Y-m-d H:i:s')
            );
            
            $this->logDebug("Others content generado exitosamente para producto: " . $product_id);
            
        } catch (Exception $e) {
            $json = array(
                'error' => true,
                'message' => 'Others content generation failed: ' . $e->getMessage(),
                'debug' => array(
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            );
            $this->logDebug("Error generando others content: " . $e->getMessage());
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
     * Get current schema settings for product - INCLUYE OTHERS_CONTENT
     */
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
                    created_date,
                    updated_date
                FROM " . DB_PREFIX . "seo_schema_content 
                WHERE product_id = " . (int)$product_id . "
                LIMIT 1
            ");

            if ($query->num_rows) {
                $settings = $query->row;
                
                // Convertir flags a boolean para compatibilidad
                $settings['enable_variants'] = (bool)$settings['enable_variants'];
                $settings['enable_faq'] = (bool)$settings['enable_faq'];
                $settings['enable_howto'] = (bool)$settings['enable_howto'];
                $settings['enable_review'] = (bool)$settings['enable_review'];
                
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
                
                // Retornar valores por defecto si no existe configuración
                return array(
                    'custom_description' => '',
                    'faq_content' => '',
                    'howto_content' => '',
                    'review_content' => '',
                    'others_content' => '',
                    'enable_variants' => true,
                    'enable_faq' => false,
                    'enable_howto' => false,
                    'enable_review' => false
                );
            }
        } catch (Exception $e) {
            $this->logDebug("Error cargando configuración: " . $e->getMessage());
            
            // En caso de error, retornar array vacío para evitar fallos
            return array(
                'custom_description' => '',
                'faq_content' => '',
                'howto_content' => '',
                'review_content' => '',
                'others_content' => '',
                'enable_variants' => true,
                'enable_faq' => false,
                'enable_howto' => false,
                'enable_review' => false
            );
        }
    }

    /**
     * Save schema settings for product - INCLUYE OTHERS_CONTENT
     */
    private function saveSchemaSettings($product_id)
    {
        $this->logDebug("=== GUARDANDO CONFIGURACIÓN SCHEMA CON OTHERS_CONTENT ===");
        $this->logDebug("Producto ID: " . $product_id);
        $this->logDebug("POST data: " . print_r($this->request->post, true));
        
        try {
            // Verificar si ya existe un registro para este producto
            $query = $this->db->query("
                SELECT id 
                FROM " . DB_PREFIX . "seo_schema_content 
                WHERE product_id = " . (int)$product_id . "
                LIMIT 1
            ");

            // Preparar datos para guardar
            $data = array(
                'custom_description' => trim($this->request->post['custom_description'] ?? ''),
                'faq_content' => trim($this->request->post['faq_content'] ?? ''),
                'howto_content' => trim($this->request->post['howto_content'] ?? ''),
                'review_content' => trim($this->request->post['review_content'] ?? ''),
                'others_content' => trim($this->request->post['others_content'] ?? ''),
                'enable_variants' => isset($this->request->post['enable_variants']) ? 1 : 0,
                'enable_faq' => !empty($this->request->post['faq_content']) ? 1 : 0,
                'enable_howto' => !empty($this->request->post['howto_content']) ? 1 : 0,
                'enable_review' => !empty($this->request->post['review_content']) ? 1 : 0
            );

            $this->logDebug("Datos procesados: " . print_r($data, true));

            if ($query->num_rows) {
                // UPDATE - Actualizar registro existente
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
                        updated_date = NOW()
                    WHERE product_id = " . (int)$product_id
                ;
                
                $this->db->query($update_sql);
                $this->logDebug("Registro actualizado exitosamente.");
                
            } else {
                // INSERT - Crear nuevo registro
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
                        NOW(),
                        NOW()
                    )
                ";
                
                $this->db->query($insert_sql);
                $this->logDebug("Nuevo registro creado exitosamente.");
            }

            // Verificar que se guardó correctamente
            $verify_query = $this->db->query("
                SELECT 
                    LENGTH(custom_description) as desc_len,
                    LENGTH(faq_content) as faq_len,
                    LENGTH(howto_content) as howto_len,
                    LENGTH(review_content) as review_len,
                    LENGTH(others_content) as others_len,
                    updated_date
                FROM " . DB_PREFIX . "seo_schema_content 
                WHERE product_id = " . (int)$product_id
            );
            
            if ($verify_query->num_rows) {
                $verification = $verify_query->row;
                $this->logDebug("Verificación exitosa - Longitudes: desc={$verification['desc_len']}, faq={$verification['faq_len']}, howto={$verification['howto_len']}, review={$verification['review_len']}, others={$verification['others_len']}, updated={$verification['updated_date']}");
            }

        } catch (Exception $e) {
            $this->logDebug("ERROR guardando configuración: " . $e->getMessage());
            $this->logDebug("SQL Error Info: " . $this->db->error);
            
            // Re-lanzar la excepción para que sea manejada por el controlador principal
            throw new Exception("Error saving schema settings: " . $e->getMessage());
        }
    }

    /**
     * NUEVO: Generar others_content con datos automáticos para Rich Results
     */
    private function generateOthersContentData($product_info)
    {
        $others_data = array();
        
        // productGroupID - Basado en modelo o categoría
        if (!empty($product_info['model'])) {
            $others_data['productGroupID'] = $product_info['model'];
        }
        
        // additionalProperty - Propiedades técnicas
        $additionalProperties = array();
        
        if (!empty($product_info['weight'])) {
            $additionalProperties[] = array(
                '@type' => 'PropertyValue',
                'name' => 'Weight',
                'value' => $product_info['weight'] . ' ' . ($product_info['weight_class'] ?? 'kg')
            );
        }
        
        if (!empty($product_info['length']) || !empty($product_info['width']) || !empty($product_info['height'])) {
            $dimensions = trim(
                ($product_info['length'] ?? '0') . ' x ' . 
                ($product_info['width'] ?? '0') . ' x ' . 
                ($product_info['height'] ?? '0')
            );
            if ($dimensions !== '0 x 0 x 0') {
                $additionalProperties[] = array(
                    '@type' => 'PropertyValue',
                    'name' => 'Dimensions',
                    'value' => $dimensions . ' ' . ($product_info['length_class'] ?? 'cm')
                );
            }
        }
        
        if (!empty($additionalProperties)) {
            $others_data['additionalProperty'] = $additionalProperties;
        }
        
        // category - Información de categoría si está disponible
        if (!empty($product_info['category_id'])) {
            $others_data['category'] = 'Product Category ID: ' . $product_info['category_id'];
        }
        
        // isAccessoryOrSparePartFor - Para productos relacionados
        if (!empty($product_info['manufacturer'])) {
            $others_data['brand'] = array(
                '@type' => 'Brand',
                'name' => $product_info['manufacturer']
            );
        }
        
        // Campos mejorados para SEO
        $seo_enhancements = array(
            'audience' => array(
                '@type' => 'Audience',
                'audienceType' => 'General Public'
            ),
            'offers' => array(
                'priceSpecification' => array(
                    '@type' => 'PriceSpecification',
                    'validThrough' => date('Y-12-31', strtotime('+1 year'))
                )
            )
        );
        
        $others_data = array_merge($others_data, $seo_enhancements);
        
        return $others_data;
    }

    /**
     * NUEVO: Actualizar solo others_content en base de datos
     */
    private function updateOthersContent($product_id, $others_content)
    {
        $json_content = json_encode($others_content, JSON_UNESCAPED_UNICODE);
        
        // Verificar si existe registro
        $query = $this->db->query("
            SELECT id 
            FROM " . DB_PREFIX . "seo_schema_content 
            WHERE product_id = " . (int)$product_id . "
            LIMIT 1
        ");
        
        if ($query->num_rows) {
            // Actualizar registro existente
            $this->db->query("
                UPDATE " . DB_PREFIX . "seo_schema_content 
                SET 
                    others_content = '" . $this->db->escape($json_content) . "',
                    updated_date = NOW()
                WHERE product_id = " . (int)$product_id
            );
        } else {
            // Crear nuevo registro con solo others_content
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "seo_schema_content 
                (product_id, others_content, created_date, updated_date) 
                VALUES (
                    " . (int)$product_id . ",
                    '" . $this->db->escape($json_content) . "',
                    NOW(),
                    NOW()
                )
            ");
        }
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
     * Genera múltiple contenido IA con DIVISIÓN INTELIGENTE DE TOKENS - NUEVA IMPLEMENTACIÓN
     */
    private function generateMultipleAIContentWithTokenDivision($product_info, $content_types)
    {
        $this->logDebug("=== INICIO DIVISIÓN INTELIGENTE DE TOKENS ===");
        $this->logDebug("Producto: " . $product_info['name']);
        $this->logDebug("Tipos de contenido solicitados: " . implode(', ', $content_types));
        
        // PASO 1: Obtener configuración de tokens base
        $base_max_tokens = (int)$this->config->get('smart_seo_schema_ai_max_tokens') ?: 800;
        $content_count = count($content_types);
        
        $this->logDebug("Tokens base configurados: " . $base_max_tokens);
        $this->logDebug("Cantidad de tipos de contenido: " . $content_count);
        
        // PASO 2: Calcular división con tokens mínimos garantizados
        $min_tokens_per_content = 100; // Mínimo garantizado por tipo
        $tokens_per_content = intval($base_max_tokens / $content_count);
        
        // Verificar que cada tipo tenga al menos el mínimo
        if ($tokens_per_content < $min_tokens_per_content) {
            $tokens_per_content = $min_tokens_per_content;
            $actual_total_tokens = $tokens_per_content * $content_count;
            $this->logDebug("AJUSTE: Tokens insuficientes. Subiendo a mínimo de {$min_tokens_per_content} por tipo");
            $this->logDebug("Total real de tokens que se usarán: " . $actual_total_tokens);
        } else {
            $actual_total_tokens = $base_max_tokens;
            $this->logDebug("División estándar: {$tokens_per_content} tokens por tipo");
        }
        
        // PASO 3: Preparar contexto del producto
        $existing_description = '';
        if (!empty($product_info['description'])) {
            $existing_description = strip_tags($product_info['description']);
            $existing_description = substr($existing_description, 0, 600); // Más contexto
        }
        
        // PASO 4: Construir prompt optimizado sin mencionar límites de tokens en el contenido
        $prompt = "You are a professional content writer. Create high-quality content for this product:\n\n";
        $prompt .= "Product: " . $product_info['name'] . "\n";
        $prompt .= "Model: " . $product_info['model'] . "\n";
        
        if ($existing_description) {
            $prompt .= "Current description: " . $existing_description . "\n\n";
        }
        
        $prompt .= "Generate the following content sections with clear headers:\n\n";
        
        // PASO 5: Agregar instrucciones específicas SIN mencionar tokens
        foreach ($content_types as $type) {
            $prompt .= $this->buildCleanPromptSection($type, $tokens_per_content);
        }
        
        $prompt .= "\nIMPORTANT GUIDELINES:\n";
        $prompt .= "- Write professionally and concisely\n";
        $prompt .= "- Use clear section headers with === markers\n";
        $prompt .= "- Focus on key information and benefits\n";
        $prompt .= "- Keep each section appropriately sized for its purpose\n";
        
        $this->logDebug("Prompt final creado - Longitud: " . strlen($prompt));
        $this->logDebug("Tokens por sección: " . $tokens_per_content);
        $this->logDebug("Total máximo permitido: " . $actual_total_tokens);
        
        // PASO 6: Llamada a API con límites optimizados
        $response = $this->callGroqAPI(
            $this->config->get('smart_seo_schema_groq_api_key'),
            $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant',
            $prompt,
            $actual_total_tokens // Usar el total calculado
        );
        
        if (!$response) {
            throw new Exception('No response from AI API with token division');
        }
        
        $this->logDebug("Respuesta recibida con división de tokens - Longitud: " . strlen($response));
        
        // PASO 7: Parsear y validar contenido
        $parsed_content = $this->parseMultipleAIResponse($response, $content_types);
        
        // PASO 8: Log final de resultados
        $this->logDebug("=== RESULTADO DIVISIÓN DE TOKENS ===");
        foreach ($parsed_content as $type => $content) {
            $word_count = str_word_count($content);
            $char_count = strlen($content);
            $this->logDebug("Tipo: {$type} | Palabras: {$word_count} | Caracteres: {$char_count}");
        }
        
        return $parsed_content;
    }

    /**
     * Construye sección de prompt LIMPIA para cada tipo de contenido (SIN MENCIONAR TOKENS)
     */
    private function buildCleanPromptSection($content_type, $tokens_per_content)
    {
        $section = "";
        
        switch ($content_type) {
            case 'description':
                $section .= "===DESCRIPTION===\n";
                $section .= "Create a concise, SEO-optimized product description. Focus on:\n";
                $section .= "- Key features and benefits\n";
                $section .= "- Technical specifications\n";
                $section .= "- Use cases and target audience\n";
                $section .= "Write professionally and keep it appropriately detailed.\n\n";
                break;
                
            case 'faq':
                $faq_count = $this->config->get('smart_seo_schema_faq_count') ?: 3;
                $section .= "===FAQ===\n";
                $section .= "Create {$faq_count} useful Q&A pairs in this format:\n";
                $section .= "Q: [question]\n";
                $section .= "A: [answer]\n";
                $section .= "Cover specifications, compatibility, and common usage questions.\n\n";
                break;
                
            case 'howto':
                $steps_count = $this->config->get('smart_seo_schema_howto_steps_count') ?: 5;
                $section .= "===HOWTO===\n";
                $section .= "Create {$steps_count} clear steps for installation, setup, or usage:\n";
                $section .= "Step 1: [instruction]\n";
                $section .= "Step 2: [instruction]\n";
                $section .= "Focus on practical, actionable guidance.\n\n";
                break;
                
            case 'review':
                $section .= "===REVIEW===\n";
                $section .= "Write a professional product review including:\n";
                $section .= "- Main advantages and benefits\n";
                $section .= "- Performance highlights\n";
                $section .= "- Overall recommendation\n";
                $section .= "Keep it balanced and informative.\n\n";
                break;
        }
        
        return $section;
    }

    /**
     * Parsea respuesta múltiple de IA en contenidos separados - MEJORADO
     */
    private function parseMultipleAIResponse($response, $content_types)
    {
        $this->logDebug("=== PARSEANDO RESPUESTA CON DIVISIÓN DE TOKENS ===");
        
        $parsed_content = array();
        
        // Marcadores de sección
        $sections = array(
            'description' => '===DESCRIPTION===',
            'faq' => '===FAQ===',
            'howto' => '===HOWTO===',
            'review' => '===REVIEW==='
        );
        
        foreach ($content_types as $type) {
            if (!isset($sections[$type])) {
                $this->logDebug("Tipo no reconocido: " . $type);
                continue;
            }
            
            $marker = $sections[$type];
            $start_pos = strpos($response, $marker);
            
            if ($start_pos !== false) {
                $start_pos += strlen($marker);
                
                // Buscar el siguiente marcador o final de texto
                $end_pos = strlen($response);
                foreach ($sections as $other_marker) {
                    if ($other_marker === $marker) continue;
                    $next_pos = strpos($response, $other_marker, $start_pos);
                    if ($next_pos !== false && $next_pos < $end_pos) {
                        $end_pos = $next_pos;
                    }
                }
                
                $content = substr($response, $start_pos, $end_pos - $start_pos);
                $content = trim($content);
                
                // Limpiar contenido de posibles referencias a tokens
                $content = preg_replace('/\b(?:EXACTLY|exactly)\s+\d+\s+tokens?\s+(?:MAX|max)\b/i', '', $content);
                $content = preg_replace('/\(\s*EXACTLY\s+\d+\s+tokens?\s+MAX\s*\)/i', '', $content);
                $content = preg_replace('/\b\d+\s+tokens?\s+(?:each|MAX|max)\b/i', '', $content);
                
                // Limpiar contenido adicional
                $content = preg_replace('/^[\r\n]+/', '', $content); // Remover saltos iniciales
                $content = preg_replace('/[\r\n]+$/', '', $content); // Remover saltos finales
                $content = trim($content);
                
                if (!empty($content)) {
                    $parsed_content[$type] = $content;
                    $this->logDebug("Extraído {$type}: " . strlen($content) . " caracteres, " . str_word_count($content) . " palabras");
                } else {
                    $this->logDebug("Contenido vacío para: " . $type);
                }
            } else {
                $this->logDebug("Marcador no encontrado para: " . $type);
            }
        }
        
        // Fallback mejorado
        if (empty($parsed_content) && !empty($content_types)) {
            $this->logDebug("FALLBACK: Sin marcadores encontrados, intentando división por líneas");
            
            // Intentar dividir la respuesta en párrafos y asignar al primer tipo
            $first_type = $content_types[0];
            $lines = explode("\n", trim($response));
            $clean_lines = array_filter($lines, function($line) {
                return !empty(trim($line));
            });
            
            if (!empty($clean_lines)) {
                $fallback_content = implode("\n", array_slice($clean_lines, 0, 10)); // Primeras 10 líneas
                // Limpiar referencias a tokens del fallback también
                $fallback_content = preg_replace('/\b(?:EXACTLY|exactly)\s+\d+\s+tokens?\s+(?:MAX|max)\b/i', '', $fallback_content);
                $fallback_content = preg_replace('/\(\s*EXACTLY\s+\d+\s+tokens?\s+MAX\s*\)/i', '', $fallback_content);
                $fallback_content = trim($fallback_content);
                
                $parsed_content[$first_type] = $fallback_content;
                $this->logDebug("Fallback aplicado para {$first_type}: " . strlen($fallback_content) . " caracteres");
            }
        }
        
        $this->logDebug("Parsing completado - Tipos extraídos: " . implode(', ', array_keys($parsed_content)));
        
        return $parsed_content;
    }

    /**
     * Método mejorado para llamar a Groq API con control de tokens específico
     */
    private function callGroqAPI($api_key, $model, $prompt, $max_tokens = null)
    {
        $url = 'https://api.groq.com/openai/v1/chat/completions';
        
        // Verificar que cURL esté disponible
        if (!function_exists('curl_init')) {
            throw new Exception('cURL extension is not available on this server');
        }
        
        // Usar tokens específicos o configuración por defecto
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

        $this->logDebug("=== LLAMADA API CON CONTROL DE TOKENS ===");
        $this->logDebug("URL: " . $url);
        $this->logDebug("Modelo: " . $model);
        $this->logDebug("Max tokens (específicos): " . $tokens_to_use);
        $this->logDebug("Temperature: " . $data['temperature']);
        $this->logDebug("Prompt length: " . strlen($prompt) . " caracteres");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int)$this->config->get('smart_seo_schema_ai_timeout') ?: 30); // Más tiempo para respuestas largas
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
        $this->logDebug("Connect time: " . $curl_info['connect_time'] . "s");

        if ($curl_error) {
            throw new Exception("cURL Error: " . $curl_error);
        }

        if ($response === false) {
            throw new Exception("cURL failed to get response");
        }

        if ($response) {
            $this->logDebug("Raw response preview: " . substr($response, 0, 300) . "...");
        }

        if ($http_code == 200 && $response) {
            $decoded = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("JSON decode error: " . json_last_error_msg() . ". Response: " . substr($response, 0, 200));
            }
            
            if (isset($decoded['choices'][0]['message']['content'])) {
                $content = $decoded['choices'][0]['message']['content'];
                $this->logDebug("Contenido extraído exitosamente: " . strlen($content) . " caracteres");
                
                // Log de uso de tokens si está disponible
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