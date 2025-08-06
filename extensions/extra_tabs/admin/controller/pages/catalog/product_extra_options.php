<?php

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}
class ControllerPagesCatalogProductExtraOptions extends AController
{
    private $error = [];
    private $attribute_manager;
    public $data = [];

    public function main()
    {
        // init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('catalog/product');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->loadModel('catalog/product');
        if (has_value($product_id) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $product_info = $this->model_catalog_product->getProduct((int) $product_id);
            if (!$product_info) {
                $this->session->data['warning'] = $this->language->get('error_product_not_found');
                (version_compare(VERSION, '1.4.0') >= 0) ? redirect($this->html->getSecureURL('catalog/product')) : $this->redirect($this->html->getSecureURL('catalog/product'));
            }
        }

        $this->data['product_description'] = $this->model_catalog_product->getProductDescriptions($product_id);
        $this->data['heading_title'] = $this->language->get('text_edit') . '&nbsp;' . $this->language->get('text_product');
        $this->view->assign('error_warning', $this->error['warning']);
        $this->view->assign('success', $this->session->data['success']);
        if (isset($this->session->data['success'])) {
            unset($this->session->data['success']);
        }
        // breadcrumb
        // load tabs controller
        $this->data['extra_tab'] = true;
        $tabs_obj = $this->dispatch('pages/catalog/product_tabs', [$this->data]);
        $this->data['product_tabs'] = $tabs_obj->dispatchGetOutput();
        unset($tabs_obj);

        // update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
        $this->loadModel('extra_tabs/product');
        $this->attribute_manager = new AAttribute_Manager();

        // add new extra option or add from template global extra attributes
        // index.php?rt=catalog/product_extra_options&product_id=50&s=admin
        // post data params for add from templates
        // name="attribute_id" =13  global attribute id EXTRA
        // name="status" =1
        // name="option_name" empty?
        // name="element_type" =S
        // name="sort_order" Empty
        // name="required" =0

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->_validateForm()) {
            $this->model_extra_tabs_product->addProductOption($this->request->get['product_id'], $this->request->post);
            // break;
            $this->session->data['success'] = $this->language->get('text_success');

            (version_compare(VERSION, '1.4.0') >= 0) ? redirect($this->html->getSecureURL('catalog/product_extra_options', '&product_id=' . $this->request->get['product_id'])) : $this->redirect($this->html->getSecureURL('catalog/product_extra_options', '&product_id=' . $this->request->get['product_id']));
        }

        $product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
        if (!$product_info) {
            $this->session->data['warning'] = $this->language->get('error_product_not_found');
            (version_compare(VERSION, '1.4.0') >= 0) ? redirect($this->html->getSecureURL('catalog/product')) : $this->redirect($this->html->getSecureURL('catalog/product'));
        }

        $this->data['attributes'] = [
            'new' => $this->language->get('text_add_new_option'),
        ];
        $results = $this->attribute_manager->getAttributes(// get tab templates
            [
                'search' => " ga.attribute_type_id = '" . $this->attribute_manager->getAttributeTypeID('extra_tab') . "'
				AND ga.status = 1
				AND ga.attribute_parent_id = 0 ",
                'sort' => 'sort_order',
                'order' => 'ASC',
                'limit' => 1000, // !we can not have unlimited, so set 1000
            ],
            $this->session->data['content_language_id']
        );
        foreach ($results as $type) {
            $this->data['attributes'][$type['attribute_id']] = $type['name'];
        }

        $this->data['product_description'] = $this->model_catalog_product->getProductDescriptions($this->request->get['product_id']);
        $product_options = $this->model_extra_tabs_product->getProductOptions($this->request->get['product_id']);

        foreach ($product_options as &$option) {
            $option_name = trim($option['language'][$this->session->data['content_language_id']]['name']);
            $option['language'][$this->session->data['content_language_id']]['name'] = $option_name ? $option_name : 'n/a';
            $option_name = trim($option['language'][$this->session->data['language_id']]['name']);
            $option['language'][$this->session->data['language_id']]['name'] = $option_name ? $option_name : 'n/a';
        }
        unset($option);

        $this->data['product_options'] = $product_options;
        $this->data['language_id'] = $this->session->data['content_language_id'];
        $this->data['url']['load_option'] = $this->html->getSecureURL('load_extra/product/load_option', '&product_id=' . $this->request->get['product_id']);
        $this->data['url']['update_option'] = $this->html->getSecureURL('load_extra/product/update_option', '&product_id=' . $this->request->get['product_id']);
        $this->data['url']['get_options_list'] = $this->html->getSecureURL('load_extra/product/get_options_list', '&product_id=' . $this->request->get['product_id']);

        $this->view->assign('error', $this->error);
        $this->view->assign('success', $this->session->data['success']);
        if (isset($this->session->data['success'])) {
            unset($this->session->data['success']);
        }

        $this->document->initBreadcrumb(
            [
                'href' => $this->html->getSecureURL('index/home'),
                'text' => $this->language->get('text_home'),
                'separator' => false,
            ]
        );
        $this->document->addBreadcrumb(
            [
                'href' => $this->html->getSecureURL('catalog/product'),
                'text' => $this->language->get('heading_title'),
                'separator' => ' :: ',
            ]
        );
        $this->document->addBreadcrumb(
            [
                'href' => $this->html->getSecureURL('catalog/product/update', '&product_id=' . $this->request->get['product_id']),
                'text' => $this->language->get('text_edit') . '&nbsp;' . $this->language->get('text_product') . ' - ' . $this->data['product_description'][$this->session->data['content_language_id']]['name'],
                'separator' => ' :: ',
            ]
        );
        $this->document->addBreadcrumb(
            [
                'href' => $this->html->getSecureURL('catalog/product_extra_options', '&product_id=' . $this->request->get['product_id']),
                'text' => $this->language->get('tab_extra_option'),
                'separator' => ' :: ',
            ]
        );

        $results = HtmlElementFactory::getAvailableElements();
        $element_types = ['' => $this->language->get('text_select')];
        foreach ($results as $key => $type) {
            // allowed field types
            // if ( in_array($key,array('I','T','S','M','R','C','G','H', 'D', 'O', 'F', 'L', 'P')) ) {
            if (in_array($key, ['S'])) {
                $element_types[$key] = $type['type'];
            }
        }

        $this->data['button_add_option'] = $this->html->buildButton(
            [
                'text' => $this->language->get('button_add_option'),
                'style' => 'button1',
            ]
        );
        $this->data['button_add_option_value'] = $this->html->buildButton(
            [
                'text' => $this->language->get('button_add_option_value'),
                'style' => 'button1',
            ]
        );
        $this->data['button_remove'] = $this->html->buildButton(
            [
                'text' => $this->language->get('button_remove'),
                'style' => 'button1',
            ]
        );
        $this->data['button_reset'] = $this->html->buildButton(
            [
                'text' => $this->language->get('button_reset'),
                'style' => 'button2',
            ]
        );

        $form = new AForm('HT');

        $form->setForm(
            [
                'form_name' => 'new_option_form',
                'update' => '',
            ]
        );
        $this->data['attributes'] = $form->getFieldHtml(
            [
                'type' => 'selectbox',
                'name' => 'attribute_id',
                'options' => $this->data['attributes'],
                'style' => 'large-field',
            ]
        );
        $this->data['option_name'] = $form->getFieldHtml(
            [
                'type' => 'input',
                'name' => 'option_name',
                'required' => true,
            ]
        );
        $this->data['status'] = $form->getFieldHtml(
            [
                'type' => 'checkbox',
                'name' => 'status',
                'value' => 1,
                'style' => 'btn_switch',
            ]
        );
        $this->data['sort_order'] = $form->getFieldHtml(
            [
                'type' => 'input',
                'name' => 'sort_order',
                'style' => 'small-field',
            ]
        );
        $this->data['required'] = $form->getFieldHtml(
            [
                'type' => 'checkbox',
                'name' => 'required',   // add new option
                'style' => 'btn_switch',
            ]
        );
        $this->data['element_type'] = $form->getFieldHtml(
            [
                'type' => 'selectbox',
                'name' => 'element_type',
                'required' => true,
                'options' => $element_types,
            ]
        );

        $this->data['action'] = $this->html->getSecureURL('catalog/product_extra_options', '&product_id=' . $this->request->get['product_id']);
        $this->data['form_title'] = $this->language->get('text_edit') . '&nbsp;' . $this->language->get('text_product');
        $this->data['update'] = '';
        $form = new AForm('HT');

        $form->setForm(
            [
                'form_name' => 'product_form',
                'update' => $this->data['update'],
            ]
        );

        $this->data['form']['id'] = 'product_form';
        $this->data['form']['form_open'] = $form->getFieldHtml(
            [
                'type' => 'form',
                'name' => 'product_form',
                'action' => $this->data['action'],
                'attr' => 'confirm-exit="true"',
            ]
        );
        $this->data['form']['submit'] = $form->getFieldHtml(
            [
                'type' => 'button',
                'name' => 'submit',
                'text' => $this->language->get('button_add_option'),
                'style' => 'button1',
            ]
        );
        $this->data['form']['cancel'] = $form->getFieldHtml(
            [
                'type' => 'button',
                'name' => 'cancel',
                'text' => $this->language->get('button_cancel'),
                'style' => 'button2',
            ]
        );

        $this->addChild('pages/catalog/product_summary', 'summary_form', 'pages/catalog/product_summary.tpl');

        $this->addChild('responses/common/resource_library/get_resources_html', 'resources_html', 'responses/common/resource_library_scripts.tpl');
        $resources_scripts = $this->dispatch(
            'responses/common/resource_library/get_resources_scripts',
            [
                'object_name' => 'product_option_value',
                'object_id' => '',
                'types' => 'image',
            ]
        );

        $object_title = $this->language->get('text_product') . ' ' . $this->language->get('text_option_value');

        $this->view->assign('resources_scripts', $resources_scripts->dispatchGetOutput());
        $this->view->assign('rl', $this->html->getSecureURL('common/resource_library', '&object_name=&object_id&type=image&mode=url&object_title=' . $object_title));
        // pathes for js function

        $this->data['rl_unmap_path'] = $this->html->getSecureURL('common/resource_library/unmap', '&object_name=product_option_value&object_title=' . $object_title);
        $this->data['rl_rl_path'] = $this->html->getSecureURL('common/resource_library', '&object_name=product_option_value&object_title=' . $object_title);
        $this->data['rl_resources_path'] = $this->html->getSecureURL('common/resource_library/resources', '&object_name=product_option_value&object_title=' . $object_title);

        $this->view->assign('help_url', $this->gen_help_url('product_options'));
        $this->view->assign('form_language_switch', $this->html->getContentLanguageSwitcher());
        $this->view->batchAssign($this->data);
        $this->processTemplate('pages/extension/product_options.tpl');

        // update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    private function _validateForm()
    {
        if (!$this->user->canModify('catalog/product_options')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if ($this->model_catalog_product->isProductGroupOption($this->request->get['product_id'], $this->request->post['attribute_id'])) {
            $this->error['warning'] = $this->language->get('error_option_in_group');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }
}
