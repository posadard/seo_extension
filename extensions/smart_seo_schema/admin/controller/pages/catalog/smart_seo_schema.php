<?php
/*------------------------------------------------------------------------------
  Smart SEO Schema Assistant - Admin Controller
  
  Controller for product tab integration in AbanteCart admin
  Handles Schema.org configuration per product with AI assistance
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
     * AJAX endpoint for testing AI connection
     */
    public function testAIConnection()
    {
        $this->loadLanguage('smart_seo_schema/smart_seo_schema');
        
        $api_key = $this->config->get('smart_seo_schema_groq_api_key');
        $model = $this->config->get('smart_seo_schema_groq_model') ?: 'llama-3.1-8b-instant';
        
        $json = array();
        
        if (!$api_key) {
            $json['error'] = true;
            $json['message'] = 'No API key configured';
        } else {
            try {
                $response = $this->callGroqAPI($api_key, $model, 'Test connection');
                if ($response) {
                    $json['error'] = false;
                    $json['message'] = $this->language->get('text_ai_connection_success');
                } else {
                    $json['error'] = true;
                    $json['message'] = $this->language->get('text_ai_connection_failed');
                }
            } catch (Exception $e) {
                $json['error'] = true;
                $json['message'] = $this->language->get('text_ai_connection_failed') . ': ' . $e->getMessage();
            }
        }

        $this->load->library('json');
        $this->response->setOutput(AJson::encode($json));
    }

    /**
     * AJAX endpoint for generating AI content
     */
    public function generateAIContent()
    {
        $product_id = $this->request->get['product_id'];
        $content_type = $this->request->post['content_type']; // description, faq, howto, review
        
        $this->loadModel('catalog/product');
        $product_info = $this->model_catalog_product->getProduct($product_id);
        
        $json = array();
        
        try {
            switch ($content_type) {
                case 'description':
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
                    throw new Exception('Invalid content type');
            }
            
            $json['error'] = false;
            $json['content'] = $content;
        } catch (Exception $e) {
            $json['error'] = true;
            $json['message'] = $e->getMessage();
        }

        $this->load->library('json');
        $this->response->setOutput(AJson::encode($json));
    }

    /**
     * AJAX endpoint for schema preview
     */
    public function previewSchema()
    {
        $product_id = $this->request->get['product_id'];
        
        $this->loadModel('catalog/product');
        $product_info = $this->model_catalog_product->getProduct($product_id);
        
        // Generate complete schema using extension logic
        $extension = new ExtensionSmartSeoSchema();
        $schema = $extension->generateCompleteSchemaForProduct($product_info);
        
        $json = array(
            'error' => false,
            'schema' => $schema
        );

        $this->load->library('json');
        $this->response->setOutput(AJson::encode($json));
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
        $this->data['form_title'] = $this->language->get('smart_seo_schema_name');

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
            LIMIT 5
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
     * Generate AI description
     */
    private function generateAIDescription($product_info)
    {
        $prompt = "Create an SEO-optimized product description for: " . $product_info['name'] . ". Focus on key features and benefits.";
        return $this->callGroqAPI(
            $this->config->get('smart_seo_schema_groq_api_key'),
            $this->config->get('smart_seo_schema_groq_model'),
            $prompt
        );
    }

    /**
     * Generate AI FAQ
     */
    private function generateAIFAQ($product_info)
    {
        $count = $this->config->get('smart_seo_schema_faq_count') ?: 3;
        $prompt = "Create " . $count . " FAQ questions and answers for product: " . $product_info['name'] . ". Format as JSON array.";
        return $this->callGroqAPI(
            $this->config->get('smart_seo_schema_groq_api_key'),
            $this->config->get('smart_seo_schema_groq_model'),
            $prompt
        );
    }

    /**
     * Generate AI HowTo
     */
    private function generateAIHowTo($product_info)
    {
        $steps = $this->config->get('smart_seo_schema_howto_steps_count') ?: 5;
        $prompt = "Create " . $steps . " step-by-step instructions for using: " . $product_info['name'] . ". Format as JSON array.";
        return $this->callGroqAPI(
            $this->config->get('smart_seo_schema_groq_api_key'),
            $this->config->get('smart_seo_schema_groq_model'),
            $prompt
        );
    }

    /**
     * Generate AI Review
     */
    private function generateAIReview($product_info)
    {
        $prompt = "Create a professional product review for: " . $product_info['name'] . ". Include pros, cons, and rating.";
        return $this->callGroqAPI(
            $this->config->get('smart_seo_schema_groq_api_key'),
            $this->config->get('smart_seo_schema_groq_model'),
            $prompt
        );
    }

    /**
     * Call Groq API
     */
    private function callGroqAPI($api_key, $model, $prompt)
    {
        $url = 'https://api.groq.com/openai/v1/chat/completions';
        
        $data = [
            'model' => $model,
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'max_tokens' => (int)$this->config->get('smart_seo_schema_ai_max_tokens') ?: 200,
            'temperature' => (float)$this->config->get('smart_seo_schema_ai_temperature') ?: 0.7
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
        curl_setopt($ch, CURLOPT_TIMEOUT, (int)$this->config->get('smart_seo_schema_ai_timeout') ?: 10);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200 && $response) {
            $decoded = json_decode($response, true);
            return $decoded['choices'][0]['message']['content'] ?? null;
        }

        throw new Exception("API call failed with code: " . $http_code);
    }
}