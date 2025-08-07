<?php

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

class ExtensionSmartSeoSchemaTabs extends Extension
{
    protected $registry;

    public function __construct()
    {
        $this->registry = Registry::getInstance();
    }

    public function onControllerCommonHead_InitData()
    {
        if (!IS_ADMIN && $this->baseObject->config->get('smart_seo_schema_status')) {
            $template = $this->baseObject->config->get('config_storefront_template');
            $css_file = $this->getCSSFileForTemplate($template);
            
            if (file_exists(DIR_EXTENSIONS . 'smart_seo_schema/storefront/view/css/' . $css_file)) {
                $this->baseObject->document->addStyle([
                    'href' => DIR_EXTENSIONS . 'smart_seo_schema/storefront/view/css/' . $css_file,
                    'rel' => 'stylesheet',
                    'media' => 'screen',
                ]);
            }
        }
    }

    public function onControllerPagesProductProduct_InitData()
    {
        // Debug básico para confirmar que el hook se ejecuta
        file_put_contents('/tmp/smart_seo_debug.log', date('Y-m-d H:i:s') . " - Hook ejecutándose\n", FILE_APPEND);
        
        if (!$this->baseObject->config->get('smart_seo_schema_status')) {
            file_put_contents('/tmp/smart_seo_debug.log', date('Y-m-d H:i:s') . " - Extensión deshabilitada\n", FILE_APPEND);
            return;
        }

        $this->baseObject->loadModel('smart_seo_schema/smart_seo_schema_tabs');
        $product_id = $this->baseObject->request->get['product_id'];
        
        if (empty($product_id)) {
            $product_id = $this->baseObject->request->get['key'];
        }

        if (!$product_id) {
            file_put_contents('/tmp/smart_seo_debug.log', date('Y-m-d H:i:s') . " - No product_id\n", FILE_APPEND);
            return;
        }

        file_put_contents('/tmp/smart_seo_debug.log', date('Y-m-d H:i:s') . " - Product ID: " . $product_id . "\n", FILE_APPEND);

        $schema_tabs = $this->baseObject->model_smart_seo_schema_smart_seo_schema_tabs->getProductSchemaTabs((int) $product_id);

        file_put_contents('/tmp/smart_seo_debug.log', date('Y-m-d H:i:s') . " - FAQ enabled: " . ($schema_tabs['faq'] ? 'yes' : 'no') . "\n", FILE_APPEND);
        file_put_contents('/tmp/smart_seo_debug.log', date('Y-m-d H:i:s') . " - HowTo enabled: " . ($schema_tabs['howto'] ? 'yes' : 'no') . "\n", FILE_APPEND);

        if (!$schema_tabs['faq'] && !$schema_tabs['howto']) {
            file_put_contents('/tmp/smart_seo_debug.log', date('Y-m-d H:i:s') . " - No tabs to inject\n", FILE_APPEND);
            return;
        }

        file_put_contents('/tmp/smart_seo_debug.log', date('Y-m-d H:i:s') . " - Injecting tabs\n", FILE_APPEND);
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

    private function getCSSFileForTemplate($template)
    {
        $css_files = [
            'foxy_template' => 'smart_seo_schema_foxy.css',
            'foxy' => 'smart_seo_schema_foxy.css',
            'novator' => 'smart_seo_schema_novator.css',
            'default' => 'smart_seo_schema_default.css'
        ];
        
        return isset($css_files[$template]) ? $css_files[$template] : 'smart_seo_schema_default.css';
    }
}