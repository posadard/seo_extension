<?php

/* Hooks */
if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

class ExtensionExtraTabs extends Extension
{
    public $data = [];

    public function __construct()
    {
        $this->registry = Registry::getInstance();
    }

    public function onControllerCommonListingGrid_InitData()
    {
        $this->baseObject->loadLanguage('extra_tabs/extra_tabs');

        $data = &$this->baseObject->data;
        if ('product_grid' == $data['table_id']) {
            if (version_compare(VERSION, '1.2.16') >= 0) {
                $data['actions']['dropdown']['children']['extra_tabs'] = [
                    'text' => $this->baseObject->language->get('entry_admin_tab_name'),
                    'href' => $this->baseObject->html->getSecureURL('catalog/product_extra_options', '&product_id=%ID%'), ];
            } else {
                $data['actions']['edit']['children']['extra_tabs'] = [
                    'text' => $this->baseObject->language->get('entry_admin_tab_name'),
                    'href' => $this->baseObject->html->getSecureURL('catalog/product_extra_options', '&product_id=%ID%'), ];
            }
        }
    }

    public function onControllerCommonHead_InitData()
    {
        if (!IS_ADMIN && 1 == $this->baseObject->config->get('extra_tabs_status')) {
            $this->baseObject->document->addStyle(
                [
                    'href' => DIR_EXTENSIONS . 'extra_tabs/storefront/view/css/extra_tabs.css',
                    'rel' => 'stylesheet',
                    'media' => 'screen',
                ]
            );
        }
    }

    public function onControllerPagesCatalogProductTabs_InitData()  // backfro
    {
        $this->baseObject->loadLanguage('extra_tabs/extra_tabs');

        $view = new AView(Registry::getInstance(), 0);
        $view->batchAssign($data);
        $this->baseObject->view->addHookVar('extension_tabs', $view->fetch('pages/extension/tabs.tpl'));
    }

    public function onControllerPagesProductProduct_InitData() // store
    {
        // $this->baseObject->loadLanguage('extra_tabs/extra_tabs');
        $this->baseObject->loadModel('extra_tabs/extra_tabs');
        // $data = array();
        $data['language_id'] = $this->baseObject->config->get('storefront_language_id');

        /**************************start options**************************************************/
        $product_id = $this->baseObject->request->get['product_id'];
        if (empty($product_id)) {
            $product_id = $this->baseObject->request->get['key'];
        }

        $data['all_options'] = $this->baseObject->model_extra_tabs_extra_tabs->getProductOptions((int) $product_id);

        /**************************end options****************************************************/

        // if ( $this->baseObject->config->get('weight_show_at_product_page_no_tabs') == '1' )

        if ('bootstrap5' == $this->baseObject->config->get('config_storefront_template')
        || 'default' == $this->baseObject->config->get('config_storefront_template')
    ) {
            $bs5_data = [];
            // $this->customer->isLogged()
            foreach ($data['all_options'] as $key => $value) {
                $tab_html = '';
                $tab_html .= '<div id=tab_' . $value['product_option_id'] . ' class=tab-pane><div class=content><table width=95%>';
                // header
                $tab_html .= '<tr><th align=left>';
                $tab_html .= $value['error_text'];
                $tab_html .= '</th></tr>';
                foreach ($value['option_value'] as $data) {
                    $tab_html .= '<tr><td>';
                    $tab_html .= html_entity_decode($data['name'], ENT_QUOTES, 'UTF-8');
                    $tab_html .= '</td></tr>';
                }
                $tab_html .= '</table></div></div>';

                if ($value['required'] == 0 || ($value['required'] == 1 && $this->baseObject->customer->isLogged())) {
                    $bs5_data[$key]['title'] = $value['name'];
                    $bs5_data[$key]['html'] = $tab_html;
                }
            }
            $data['bs5_data'] = $bs5_data;
            $view = new AView($this->registry, 0);
            $view->batchAssign($data);

            // string!
            $this->baseObject->view->addHookVar('product_description_array', $bs5_data);
            // convert to array
            $this->baseObject->view->replaceHookVar('product_description_array', $bs5_data);
        } else {
                /* show in Tab */
                $view = new AView($this->registry, 0);
                $view->batchAssign($data);
                $this->baseObject->view->addHookVar('product_features_tab', $view->fetch('pages/extra_tabs/extra_tabs_tab.tpl'));
                $this->baseObject->view->addHookVar('product_features', $view->fetch('pages/extra_tabs/extra_tabs_content.tpl'));
           
        }
    }
}
