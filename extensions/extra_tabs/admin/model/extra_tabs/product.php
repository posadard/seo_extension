<?php

if (!defined('DIR_CORE') || !IS_ADMIN) {
    header('Location: static_pages/');
}
/** @noinspection PhpUndefinedClassInspection */
class ModelExtraTabsProduct extends Model
{
    // add from extra template
    // index.php?rt=catalog/product_extra_options&product_id=50&s=admin
    // post data params for add from templates
    // name="attribute_id" =13  global attribute id EXTRA
    // name="status" =1

    // or add new
    public function addProductOption($product_id, $data)
    {
        $am = new AAttribute_Manager();
        $attribute = $am->getAttribute($data['attribute_id']);
        if ($attribute) {
            $data['element_type'] = $attribute['element_type'];
            $data['required'] = $attribute['required'];
            $data['regexp_pattern'] = $attribute['regexp_pattern'];
            // $data['placeholder'] = $attribute['placeholder'];
            $data['sort_order'] = $attribute['sort_order'];
            // $data['settings'] = $attribute['settings'];
        }
        // $this->log->write('new option');
        $this->db->query(
            'INSERT INTO ' . $this->db->table('product_extra_options') . "
							(product_id,
							 attribute_id,
							 element_type,
							 required,
							 sort_order,
							 group_id,
							 status,
							 regexp_pattern)
						VALUES ('" . (int) $product_id . "',
							'" . (int) $data['attribute_id'] . "',
							'" . $this->db->escape($data['element_type']) . "',
							'" . (int) $data['required'] . "',
							'" . (int) $data['sort_order'] . "',
							'" . (int) $data['group_id'] . "',
                            '" . (int) $data['status'] . "',
                            '" . $this->db->escape($data['regexp_pattern']) . "'
                            )"
        );
        $product_option_id = $this->db->getLastId();

        if (!empty($data['option_name'])) {
            $attributeDescriptions = [
                $this->language->getContentLanguageID() => [
                    'name' => $data['option_name'],
                    'error_text' => $data['error_text'],
                ],
            ];
        } else { // if add from extra?
            $attributeDescriptions = $am->getAttributeDescriptions($data['attribute_id']);
            // $this->log->write('hint only when copy from extra global templates');
            // $this->log->write(print_r($attributeDescriptions, true).'data template description');
            /*Array
            (
            [1] => Array
            (
            [name] => Eng tab gl
            [error_text] =>
            [placeholder] =>
            )
            [2] => Array
            (
            [name] => Ital tab gl
            [error_text] =>
            [placeholder] =>
            )
            )
            data template description*/
            // only option names. values ignored
        }

        // insert Initial one value for all languages
        $pd_opt_val_id = $this->insertProductOptionValue((int) $product_id, (int) $product_option_id, '', '', []);
        foreach ($attributeDescriptions as $language_id => $desc) {
            $this->language->replaceDescriptions(
                'product_extra_option_descriptions',
                [
                    'product_option_id' => (int) $product_option_id,
                    'product_id' => (int) $product_id,
                ],
                [
                    $language_id => [
                        'name' => $desc['name'],
                        'error_text' => $desc['error_text'],
                    ],
                ]
            );
            // $this->log->write($language_id.'mylanguage_id');
            $attributeValueDescription = $am->getAttributeValues($data['attribute_id'], $language_id);
            // $this->log->write(print_r($attributeValueDescription, true) . 'data template values');
            /*Array
            (
            [0] => Array
                (
                [attribute_value_id] => 98
                [attribute_id] => 14
                [sort_order] => 0
                [value] => spanish text
                )
            )
            data template values*/

            // $option_value_data_template = array('attribute_value_id' => '', 'name' => $attributeValueDescription['value'], 'default' => 0, );
            // prepare data for value
            /*
            Array
            (
            [attribute_value_id] =>
            [grouped_attribute_data] =>
            [name] => Hello text
            [sku] =>
            [quantity] =>
            [subtract] =>
            [price] =>
            [prefix] =>
            [sort_order] =>
            [weight] =>
            [weight_type] => g
            [default] => 0
            )
            dataexampleforvalueinsert*/
            // $this->addProductOptionValueAndDescription((int)$product_id, (int)$product_option_id, $option_value_data_template);
            // $this->insertProductOptionValueDescriptions($product_id, $product_option_id, $name, $language_id);

            // $this->log->write($attributeValueDescription[0]['value'].'value to be inserted with lang'.$language_id);
            // insert value description
            $this->insertProductOptionValueDescriptions($product_id, $pd_opt_val_id, $attributeValueDescription[0]['value'], $language_id);
        }

        // insert global template values
        /*$values = $this->getProductOptionValues($product_id, $product_option_id);
        foreach ($values as $v) {
            //$this->addProductOptionValueAndDescription($product_id, (int)$product_option_id, $data);
        }
        */

        // END

        // add empty option value for single value attributes
        $elements_with_options = HtmlElementFactory::getElementsWithOptions();
        if (!in_array($data['element_type'], $elements_with_options)) {
            $this->insertProductOptionValue($product_id, $product_option_id, '', '', []);
        }

        // if add from extra tempalte
        // public function insertProductOptionValue($product_id, $option_id, $attribute_value_id, $pd_opt_val_id, $data)
        /*if((int)$data['attribute_id'] > 0){
            $this->insertProductOptionValue($product_id, $product_option_id, '', '', array());
        }
        */

        (version_compare(VERSION, '1.4.0') >= 0) ? $this->cache->remove('product') : $this->cache->delete('product');

        // $this->_touch_product($product_id);
        return $product_option_id;
    }

    /**
     * @param int $product_id
     * @param int $product_option_id
     *
     * @throws AException
     */
    public function deleteProductOption($product_id, $product_option_id)
    {
        $am = new AAttribute_Manager();
        $attribute = $am->getAttributeByProductOptionId($product_option_id);
        $group_attribute = $am->getAttributes(['limit' => null], 0, $attribute['attribute_id']);
        if (count($group_attribute)) {
            // delete children options/values
            $children = $this->db->query(
                'SELECT product_option_id
                FROM ' . $this->db->table('product_extra_options') . "
                WHERE product_id = '" . (int) $product_id . "'
                    AND group_id = '" . (int) $product_option_id . "'"
            );
            foreach ($children->rows as $g_attribute) {
                $this->_deleteProductOption($product_id, $g_attribute['product_option_id']);
            }
        }

        $this->_deleteProductOption($product_id, $product_option_id);

        (version_compare(VERSION, '1.4.0') >= 0) ? $this->cache->remove('product') : $this->cache->delete('product');
    }

    /**
     * @param int $product_id
     * @param int $product_option_id
     *
     * @throws AException
     */
    protected function _deleteProductOption($product_id, $product_option_id)
    {
        $values = $this->getProductOptionValues($product_id, $product_option_id);
        foreach ($values as $v) {
            $this->deleteProductOptionValue($product_id, $v['product_option_value_id']);
        }

        $this->db->query(
            'DELETE FROM ' . $this->db->table('product_extra_options') . "
            WHERE product_id = '" . (int) $product_id . "'
                AND product_option_id = '" . (int) $product_option_id . "'"
        );
        $this->db->query(
            'DELETE FROM ' . $this->db->table('product_extra_option_descriptions') . "
            WHERE product_id = '" . (int) $product_id . "'
                AND product_option_id = '" . (int) $product_option_id . "'"
        );
    }

    // Add new product option value and value descriptions for all global attributes languages or current language
    /**
     * @param int $product_id
     * @param int $option_id
     * @param array $data
     *
     * @return int|null
     *
     * @throws AException
     */
    public function addProductOptionValueAndDescription($product_id, $option_id, $data)
    {
        if (empty($product_id) || empty($option_id) || empty($data)) {
            return null;
        }

        $attribute_value_id = $data['attribute_value_id'];
        if (is_array($data['attribute_value_id'])) {
            $attribute_value_id = '';
        }

        $am = new AAttribute_Manager();
        // build grouped attributes if this is a parent attribute
        if (is_array($data['attribute_value_id'])) {
            // add children option values from global attributes
            $groupData = [];
            foreach ($data['attribute_value_id'] as $child_option_id => $attribute_value_id) {
                // special serialized data for grouped options
                $groupData[] = [
                    'attr_id' => $child_option_id,
                    'attr_v_id' => $attribute_value_id,
                ];
            }
            $data['grouped_attribute_data'] = serialize($groupData);
        }

        $pd_opt_val_id = $this->insertProductOptionValue($product_id, $option_id, $attribute_value_id, '', $data);

        // Build options value descriptions
        if (is_array($data['attribute_value_id'])) {
            // add children option values description from global attributes
            $group_description = [];
            $descr_names = [];
            foreach ($data['attribute_value_id'] as $child_option_id => $attribute_value_id) {
                // special insert for grouped options
                foreach ($am->getAttributeValueDescriptions($attribute_value_id) as $language_id => $name) {
                    $group_description[$language_id][] = [
                        'attr_v_id' => $attribute_value_id,
                        'name' => $name];
                    $descr_names[$language_id][] = $name;
                }
            }

            // Insert generic merged name
            $grouped_names = null;
            foreach ($descr_names as $language_id => $name) {
                if (count($group_description[$language_id])) {
                    $grouped_names = serialize($group_description[$language_id]);
                }
                $this->insertProductOptionValueDescriptions(
                    $product_id, $pd_opt_val_id, implode(' / ', $name),
                    $language_id, $grouped_names
                );
                // $this->log->write('inserted extra option VALUE');
            }
        } else {
            if (!$data['attribute_value_id']) {
                // We save custom option value for current language
                $valueDescriptions = [
                    $this->language->getContentLanguageID() => $data['name'],
                ];
            } else {
                // We have global attributes, copy option value text from there.
                $valueDescriptions = $am->getAttributeValueDescriptions((int) $data['attribute_value_id']);
            }
            foreach ($valueDescriptions as $language_id => $name) {
                $this->insertProductOptionValueDescriptions($product_id, $pd_opt_val_id, $name, $language_id);
                // $this->log->write($name.'name inserted new description');
            }
        }

        (version_compare(VERSION, '1.4.0') >= 0) ? $this->cache->remove('product') : $this->cache->delete('product');

        return $pd_opt_val_id;
    }

    /**
     * @param int $product_id
     * @param int $pd_opt_val_id
     * @param int $language_id
     *
     * @return stdClass|null
     *
     * @throws AException
     */
    public function getProductOptionValueDescriptions($product_id, $pd_opt_val_id, $language_id)
    {
        if (empty($product_id) || empty($pd_opt_val_id) || empty($language_id)) {
            return null;
        }

        return $this->db->query(
            'SELECT *
            FROM ' . $this->db->table('product_extra_option_value_descriptions') . "
            WHERE product_option_value_id = '" . (int) $pd_opt_val_id . "'
                AND product_id = '" . (int) $product_id . "'
                AND language_id = '" . (int) $language_id . "' "
        );
    }

    /**
     * @param int $product_id
     * @param int $pd_opt_val_id
     * @param string $name
     * @param int $language_id
     * @param string|null $grp_attr_names
     *
     * @return int|null
     *
     * @throws AException
     */
    public function insertProductOptionValueDescriptions(
        $product_id,
        $pd_opt_val_id,
        $name,
        $language_id,
        $grp_attr_names = null,
    ) {
        if (empty($product_id) || empty($pd_opt_val_id) || empty($language_id)) {
            return null;
        }

        $this->language->replaceDescriptions(
            'product_extra_option_value_descriptions',
            ['product_option_value_id' => (int) $pd_opt_val_id,
                'product_id' => (int) $product_id],
            [$language_id => [
                'name' => $name,
                'grouped_attribute_names' => $grp_attr_names,
            ]]);

        (version_compare(VERSION, '1.4.0') >= 0) ? $this->cache->remove('product') : $this->cache->delete('product');

        return $this->db->getLastId();
    }

    /**
     * @param int $product_id
     * @param int $pd_opt_val_id
     * @param string $name
     * @param int $language_id
     * @param string|null $grp_attr_names
     *
     * @return int|null
     *
     * @throws AException
     */
    public function updateProductOptionValueDescriptions(
        $product_id,
        $pd_opt_val_id,
        $name,
        $language_id,
        $grp_attr_names = null)
    {
        if (empty($product_id) || empty($pd_opt_val_id) || empty($language_id)) {
            return null;
        }
        $this->language->replaceDescriptions(
            'product_extra_option_value_descriptions',
            ['product_option_value_id' => (int) $pd_opt_val_id,
                'product_id' => (int) $product_id],
            [$language_id => [
                'name' => $name,
                'grouped_attribute_names' => $grp_attr_names,
            ]]);

        (version_compare(VERSION, '1.4.0') >= 0) ? $this->cache->remove('product') : $this->cache->delete('product');

        return $pd_opt_val_id;
    }

    /**
     * @param int $product_id
     * @param int $pd_opt_val_id
     * @param int $language_id
     *
     * @return null
     *
     * @throws AException
     */
    public function deleteProductOptionValueDescriptions($product_id, $pd_opt_val_id, $language_id = 0)
    {
        if (empty($product_id) || empty($pd_opt_val_id)) {
            return false;
        }
        $add_language = '';
        if ($language_id) {
            $add_language = " AND language_id = '" . (int) $language_id . "'";
        }
        $this->db->query(
            'DELETE FROM ' . $this->db->table('product_extra_option_value_descriptions') . "
            WHERE product_id = '" . (int) $product_id . "'
                AND product_option_value_id = '" . (int) $pd_opt_val_id . "'" . $add_language
        );
        (version_compare(VERSION, '1.4.0') >= 0) ? $this->cache->remove('product') : $this->cache->delete('product');

        return true;
    }

    /**
     * @param int $product_id
     * @param int $option_id
     * @param int $attribute_value_id
     * @param int $pd_opt_val_id
     * @param array $data
     *
     * @return int|false
     *
     * @throws AException
     */
    public function insertProductOptionValue($product_id, $option_id, $attribute_value_id, $pd_opt_val_id, $data)
    {
        if (empty($product_id) || empty($option_id)) {
            return false;
        }

        $sql = 'INSERT INTO ' . $this->db->table('product_extra_option_values') . "
	        SET product_option_id = '" . (int) $option_id . "',
	            product_id = '" . (int) $product_id . "',
                group_id = '" . (int) $pd_opt_val_id . "',
	            sku = '" . $this->db->escape($data['sku']) . "',
	            quantity = '" . $this->db->escape($data['quantity']) . "',
	            subtract = '" . $this->db->escape($data['subtract']) . "',
	            price = '" . preformatFloat($data['price'], $this->language->get('decimal_point')) . "',
	            prefix = '" . $this->db->escape($data['prefix']) . "',
	            weight = '" . preformatFloat($data['weight'], $this->language->get('decimal_point')) . "',
	            weight_type = '" . $this->db->escape($data['weight_type']) . "',
	            attribute_value_id = '" . $this->db->escape($attribute_value_id) . "',
	            grouped_attribute_data = '" . $this->db->escape($data['grouped_attribute_data']) . "',
	            sort_order = '" . (int) $data['sort_order'] . "',
	            `default` = '" . (int) $data['default'] . "'";
        // $this->log->write($sql . 'sql');
        $this->db->query($sql);

        (version_compare(VERSION, '1.4.0') >= 0) ? $this->cache->remove('product') : $this->cache->delete('product');

        return $this->db->getLastId();
    }

    /**
     *  Update singe product option value
     *
     * @param int $pd_opt_val_id
     * @param int $attribute_value_id
     * @param array $data
     *
     * @return int|null
     */
    public function updateProductOptionValue($pd_opt_val_id, $attribute_value_id, $data)
    {
        if (empty($pd_opt_val_id) || empty($data)) {
            return null;
        }
        // If se have grouped (parent/child) options save no main attribute id
        if (is_array($attribute_value_id)) {
            $attribute_value_id = '';
        }

        $this->db->query(
            'UPDATE ' . DB_PREFIX . "product_extra_option_values
	        SET sku = '" . $this->db->escape($data['sku']) . "',
	            quantity = '" . $this->db->escape($data['quantity']) . "',
	            subtract = '" . $this->db->escape($data['subtract']) . "',
	            price = '" . $this->db->escape($data['price']) . "',
	            prefix = '" . $this->db->escape($data['prefix']) . "',
	            weight = '" . preformatFloat($data['weight'], $this->language->get('decimal_point')) . "',
	            weight_type = '" . $this->db->escape($data['weight_type']) . "',
	            attribute_value_id = '" . $this->db->escape($attribute_value_id) . "',
	            grouped_attribute_data = '" . $this->db->escape($data['grouped_attribute_data']) . "',
	            sort_order = '" . (int) $data['sort_order'] . "',
	            `default` = '" . (int) $data['default'] . "'
	        WHERE product_option_value_id = '" . (int) $pd_opt_val_id . "'  "
        );

        return $pd_opt_val_id;
    }

    /**
     *    Update product option value and value descriptions for set language
     *
     * @param int $product_id
     * @param int $pd_opt_val_id
     * @param array $data
     * @param int $language_id
     *
     * @throws AException
     */
    public function updateProductOptionValueAndDescription($product_id, $pd_opt_val_id, $data, $language_id)
    {
        $attribute_value_id = $data['attribute_value_id'];
        if (is_array($data['attribute_value_id'])) {
            $attribute_value_id = '';
        }

        $am = new AAttribute_Manager();
        // build grouped attributes if this is a parent attribute
        if (is_array($data['attribute_value_id'])) {
            // update children option values from global attributes
            $groupData = [];
            foreach ($data['attribute_value_id'] as $child_option_id => $attr_val_id) {
                // special serialized data for grouped options
                $groupData[] = [
                    'attr_id' => $child_option_id,
                    'attr_v_id' => $attr_val_id,
                ];
            }
            $data['grouped_attribute_data'] = serialize($groupData);
        }

        $this->updateProductOptionValue($pd_opt_val_id, $attribute_value_id, $data);

        if (is_array($data['attribute_value_id'])) {
            // update children option values description from global attributes
            $group_description = [];
            $descr_names = [];
            foreach ($data['attribute_value_id'] as $child_option_id => $attr_val_id) {
                // special insert for grouped options
                foreach ($am->getAttributeValueDescriptions($attr_val_id) as $lang_id => $name) {
                    if ($language_id == $lang_id) {
                        $group_description[$language_id][] = [
                            'attr_v_id' => $attr_val_id,
                            'name' => $name,
                        ];
                        $descr_names[$language_id][] = $name;
                    }
                }
            }
            // Insert generic merged name
            foreach ($descr_names as $lang_id => $name) {
                if ($language_id == $lang_id && count($group_description[$language_id])) {
                    $group_description[$language_id][] = $name;
                    $grouped_names = serialize($group_description[$language_id]);
                    $this->updateProductOptionValueDescriptions(
                        $product_id, $pd_opt_val_id, implode(' / ', $name),
                        $language_id, $grouped_names
                    );
                }
            }
        } else {
            if (!$data['attribute_value_id']) {
                $exist = $this->getProductOptionValueDescriptions($product_id, $pd_opt_val_id, $language_id);
                if ($exist->num_rows) {
                    $this->updateProductOptionValueDescriptions(
                        $product_id, $pd_opt_val_id, $data['name'],
                        $language_id);
                } else {
                    $this->insertProductOptionValueDescriptions(
                        $product_id, $pd_opt_val_id, $data['name'],
                        $language_id);
                }
            } else {
                $valueDescriptions = $am->getAttributeValueDescriptions((int) $data['attribute_value_id']);
                foreach ($valueDescriptions as $lang_id => $name) {
                    if ($language_id == $lang_id) {
                        // Update only language that we currently work with
                        $this->updateProductOptionValueDescriptions($product_id, $pd_opt_val_id, $name, $language_id);
                    }
                }
            }
        }

        (version_compare(VERSION, '1.4.0') >= 0) ? $this->cache->remove('product') : $this->cache->delete('product');
    }

    /**
     * @param int $product_id
     * @param int $pd_opt_val_id
     * @param int $language_id
     *
     * @return null
     */
    public function deleteProductOptionValue($product_id, $pd_opt_val_id, $language_id = 0)
    {
        if (empty($product_id) || empty($pd_opt_val_id)) {
            return false;
        }

        $this->_deleteProductOptionValue($product_id, $pd_opt_val_id, $language_id);

        // delete children values
        $children = $this->db->query(
            'SELECT product_option_value_id 
            FROM ' . $this->db->table('product_extra_option_values') . "
            WHERE product_id = '" . (int) $product_id . "'
                AND group_id = '" . (int) $pd_opt_val_id . "'"
        );
        foreach ($children->rows as $g_attribute) {
            $this->_deleteProductOptionValue($product_id, $g_attribute['product_option_value_id'], $language_id);
        }

        (version_compare(VERSION, '1.4.0') >= 0) ? $this->cache->remove('product') : $this->cache->delete('product');

        return true;
    }

    /**
     * @param int $product_id
     * @param int $pd_opt_val_id
     * @param int $language_id
     *
     * @return bool
     *
     * @throws AException
     */
    public function _deleteProductOptionValue($product_id, $pd_opt_val_id, $language_id)
    {
        if (empty($product_id) || empty($pd_opt_val_id)) {
            return false;
        }
        $add_language = '';
        if ($language_id) {
            $add_language = " AND language_id = '" . (int) $language_id . "'";
        }

        $this->db->query(
            'DELETE FROM ' . $this->db->table('product_extra_option_value_descriptions') . "
            WHERE product_id = '" . (int) $product_id . "'
                AND product_option_value_id = '" . (int) $pd_opt_val_id . "'" . $add_language
        );

        // Delete product_option_values that have no values left in descriptions
        $sql = 'DELETE FROM ' . $this->db->table('product_extra_option_values') . " 
                WHERE product_option_value_id = '" . (int) $pd_opt_val_id . "' 
                    AND product_option_value_id NOT IN 
                                                ( SELECT product_option_value_id 
                                                  FROM " . $this->db->table('product_extra_option_value_descriptions') . "
                                                  WHERE product_id = '" . (int) $product_id . "'
                                                    AND product_option_value_id = '" . (int) $pd_opt_val_id . "')";
        $this->db->query($sql);
        // get product resources
        $rm = new AResourceManager();
        $resources = $rm->getResourcesList(
            [
                'object_name' => 'product_option_value',
                'object_id' => (int) $pd_opt_val_id,
            ]
        );
        foreach ($resources as $r) {
            $rm->unmapResource(
                'product_option_value',
                $pd_opt_val_id,
                $r['resource_id']
            );
        }

        return true;
    }

    protected function _clone_product_options($product_id, $data)
    {
        // Do not use before close review.
        // Note: This is done only after product clonning. This is not to be used on existing product.
        $this->db->query('DELETE FROM ' . DB_PREFIX . "product_extra_options WHERE product_id = '" . (int) $product_id . "'");
        $this->db->query('DELETE FROM ' . DB_PREFIX . "product_extra_option_descriptions WHERE product_id = '" . (int) $product_id . "'");
        $this->db->query('DELETE FROM ' . DB_PREFIX . "product_extra_option_values WHERE product_id = '" . (int) $product_id . "'");
        $this->db->query('DELETE FROM ' . DB_PREFIX . "product_extra_option_value_descriptions WHERE product_id = '" . (int) $product_id . "'");

        if (isset($data['product_option'])) {
            foreach ($data['product_option'] as $product_option) {
                $sql = 'INSERT INTO ' . $this->db->table('product_extra_options') . " 
						SET product_id = '" . (int) $product_id . "',
							sort_order = '" . (int) $product_option['sort_order'] . "'";
                if ($product_option['attribute_id']) {
                    $sql .= ", attribute_id = '" . (int) $product_option['attribute_id'] . "'";
                }
                if ($product_option['group_id']) {
                    $sql .= ", group_id = '" . (int) $product_option['group_id'] . "'";
                }
                if ($product_option['element_type']) {
                    $sql .= ", element_type = '" . $this->db->escape($product_option['element_type']) . "'";
                }
                if ($product_option['required']) {
                    $sql .= ", required = '" . (int) $product_option['required'] . "'";
                }
                if ($product_option['regexp_pattern']) {
                    $sql .= ", regexp_pattern = '" . $this->db->escape($product_option['regexp_pattern']) . "'";
                }
                $this->db->query($sql);
                $product_option_id = $this->db->getLastId();

                foreach ($product_option['language'] as $language_id => $language) {
                    $this->language->replaceDescriptions(
                        'product_extra_option_descriptions',
                        ['product_option_id' => (int) $product_option_id,
                            'product_id' => (int) $product_id],
                        [$language_id => [
                            'name' => $language['name'],
                            'error_text' => $language['error_text'],
                        ],
                        ]
                    );
                }

                if (isset($product_option['product_option_value'])) {
                    // get product resources
                    $rm = new AResourceManager();
                    foreach ($product_option['product_option_value'] as $pd_opt_vals) {
                        $pd_opt_vals['price'] = str_replace(' ', '', $pd_opt_vals['price']);

                        $this->db->query(
                            'INSERT INTO ' . $this->db->table('product_extra_option_values') . " 
                            SET product_option_id = '" . (int) $product_option_id . "',
												product_id = '" . (int) $product_id . "',
												sku = '" . $this->db->escape($pd_opt_vals['sku']) . "',
												quantity = '" . (int) $pd_opt_vals['quantity'] . "',
												subtract = '" . (int) $pd_opt_vals['subtract'] . "',
												price = '" . preformatFloat(
                                $pd_opt_vals['price'],
                                $this->language->get('decimal_point')
                            ) . "',
												prefix = '" . $this->db->escape($pd_opt_vals['prefix']) . "',
												attribute_value_id = '" . $this->db->escape($pd_opt_vals['attribute_value_id']) . "',
	            								grouped_attribute_data = '" . $this->db->escape($pd_opt_vals['grouped_attribute_data']) . "',
	            								group_id = '" . $this->db->escape($pd_opt_vals['group_id']) . "',
												sort_order = '" . (int) $pd_opt_vals['sort_order'] . "',
												`default` = '" . (int) $pd_opt_vals['default'] . "'"
                        );

                        $pd_opt_val_id = $this->db->getLastId();
                        // clone resources of option value
                        if ($pd_opt_vals['product_option_value_id']) {
                            $resources = $rm->getResourcesList([
                                'object_name' => 'product_option_value',
                                'object_id' => $pd_opt_vals['product_option_value_id']]);
                            foreach ($resources as $r) {
                                $rm->mapResource(
                                    'product_option_value',
                                    $pd_opt_val_id,
                                    $r['resource_id']
                                );
                            }
                        }

                        foreach ($pd_opt_vals['language'] as $language_id => $lang_data) {
                            $grouped_attribute_names = serialize($lang_data['children_options_names']);

                            $this->language->replaceDescriptions(
                                'product_extra_option_value_descriptions',
                                ['product_option_value_id' => (int) $pd_opt_val_id,
                                    'product_id' => (int) $product_id],
                                [$language_id => [
                                    'name' => $lang_data['name'],
                                    'grouped_attribute_names' => $grouped_attribute_names,
                                ]]);
                        }
                    }
                }
            }
        }
        (version_compare(VERSION, '1.4.0') >= 0) ? $this->cache->remove('product') : $this->cache->delete('product');
    }

    /**
     * @param int $product_id
     *
     * @return array
     *
     * @throws AException
     */
    public function getProduct($product_id)
    {
        $query = $this->db->query(
            'SELECT DISTINCT *, p.product_id, COALESCE(pf.product_id, 0) as featured,
										(SELECT keyword
                 FROM ' . $this->db->table('url_aliases') . " 
										 WHERE query = 'product_id=" . (int) $product_id . "'
										 	AND language_id='" . (int) $this->language->getContentLanguageID() . "' ) AS keyword
            FROM " . $this->db->table('products') . ' p
            LEFT JOIN ' . $this->db->table('products_featured') . ' pf 
                ON pf.product_id = p.product_id
            LEFT JOIN ' . $this->db->table('product_descriptions') . " pd
											ON (p.product_id = pd.product_id
													AND pd.language_id = '" . (int) $this->config->get('storefront_language_id') . "')
									WHERE p.product_id = '" . (int) $product_id . "'"
        );

        return $query->row;
    }

    public function isProductGroupOption($product_id, $attribute_id)
    {
        $product_option = $this->db->query(
            'SELECT COUNT(*) as total 
            FROM ' . $this->db->table('product_extra_options') . "
            WHERE product_id = '" . (int) $product_id . "'
                AND attribute_id = '" . (int) $attribute_id . "'
                AND group_id != 0
            ORDER BY sort_order"
        );

        return $product_option->row['total'];
    }

    /**
     * @param int $attribute_id
     * @param int $group_id
     *
     * @return int
     *
     * @throws AException
     */
    public function getProductOptionByAttributeId($attribute_id, $group_id)
    {
        $product_option = $this->db->query(
            'SELECT product_option_id 
            FROM ' . $this->db->table('product_extra_options') . "
            WHERE attribute_id = '" . (int) $attribute_id . "'
                AND group_id = '" . (int) $group_id . "'
            ORDER BY sort_order"
        );

        return $product_option->row['product_option_id'];
    }

    /**
     *    Get single option data
     *
     * @param int $product_id
     * @param int $option_id
     *
     * @return array|null
     *
     * @throws AException
     */
    public function getProductOption($product_id, $option_id = 0)
    {
        // $this->log->write('extra getProductOption:' . $product_id . ' $option_id:' . $option_id);
        $product_option = $this->db->query(
            'SELECT *
            FROM ' . $this->db->table('product_extra_options') . " 
            WHERE product_id = '" . (int) $product_id . "'
                AND product_option_id = '" . (int) $option_id . "'
            ORDER BY sort_order"
        );

        $product_option_description = $this->db->query(
            'SELECT *
            FROM ' . $this->db->table('product_extra_option_descriptions') . "
            WHERE product_option_id = '" . (int) $option_id . "'"
        );

        // $this->log->write('extra product_option_description:' . print_r($product_option_description, true));

        $product_option_description_data = [];
        foreach ($product_option_description->rows as $result) {
            // $this->log->write('foreach extra product_option_description:' . print_r($result, true));
            $product_option_description_data[$result['language_id']] = [
                'name' => $result['name'],
                'option_placeholder' => $result['option_placeholder'],
                'error_text' => $result['error_text'],
            ];
        }

        if ($product_option->num_rows) {
            $row = $product_option->row;
            $row['language'] = $product_option_description_data;

            // $this->log->write('return extra product_option_description:' . print_r($row, true));

            return $row;
        } else {
            return null;
        }
    }

    /**
     * @param int $product_option_id
     * @param array $data
     *
     * @throws AException
     */
    public function updateProductOption($product_option_id, $data)
    {
        $fields = [
            'sort_order',
            'status',
            'required',
            'regexp_pattern',
        ];
        $update = [];
        foreach ($fields as $f) {
            if (isset($data[$f])) {
                $update[] = $f . " = '" . $this->db->escape($data[$f]) . "'";
            }
        }
        if (!empty($update)) {
            $this->db->query(
                'UPDATE ' . $this->db->table('product_extra_options') . ' 
                SET ' . implode(',', $update) . "
                WHERE product_option_id = '" . (int) $product_option_id . "'"
            );
        }

        if (!empty($data['name'])) {
            $language_id = $this->language->getContentLanguageID();

            $this->language->replaceDescriptions(
                'product_extra_option_descriptions',
                ['product_option_id' => (int) $product_option_id],
                [(int) $language_id => [
                    'name' => $data['name'],
                    'error_text' => $data['error_text'],
                    'option_placeholder' => $data['option_placeholder'],
                ]]);
        }

        (version_compare(VERSION, '1.4.0') >= 0) ? $this->cache->remove('product') : $this->cache->delete('product');
    }

    /**
     * Main method to get complete options data for product
     *
     * @param int $product_id
     * @param int $group_id
     *
     * @return array
     *
     * @throws AException
     */
    public function getProductOptions($product_id, $group_id = 0)
    {
        $product_option_data = [];
        $group_select = '';
        if (is_int($group_id)) {
            $group_select = "AND group_id = '" . (int) $group_id . "'";
        }
        $product_option = $this->db->query(
            'SELECT *
             FROM ' . $this->db->table('product_extra_options') . " 
             WHERE product_id = '" . (int) $product_id . "' "
            . $group_select .
            ' ORDER BY sort_order'
        );
        foreach ($product_option->rows as $product_option) {
            $option_data = $this->getProductOption($product_id, $product_option['product_option_id']);
            $option_data['product_option_value'] = $this->getProductOptionValues(
                $product_id,
                $product_option['product_option_id']
            );
            $product_option_data[] = $option_data;
        }

        return $product_option_data;
    }

    /**
     *    Main function to be called to update option values.
     *
     * @param int $product_id
     * @param int $option_id
     * @param array $data
     *
     * @return null
     *
     * @throws AException
     */
    public function updateProductOptionValues($product_id, $option_id, $data)
    {
        if (!is_array($data['product_option_value_id']) || !$option_id || !$product_id) {
            return false;
        }
        $language_id = $this->language->getContentLanguageID();

        foreach ($data['product_option_value_id'] as $opt_val_id => $status) {
            $option_value_data = [
                'attribute_value_id' => $data['attribute_value_id'][$opt_val_id],
                'grouped_attribute_data' => $data['grouped_attribute_data'][$opt_val_id],
                'name' => $data['name'][$opt_val_id],
                'sku' => $data['sku'][$opt_val_id],
                'quantity' => $data['quantity'][$opt_val_id],
                'subtract' => $data['subtract'][$opt_val_id],
                'price' => $data['price'][$opt_val_id],
                'prefix' => $data['prefix'][$opt_val_id],
                'sort_order' => $data['sort_order'][$opt_val_id],
                'weight' => $data['weight'][$opt_val_id],
                'weight_type' => $data['weight_type'][$opt_val_id],
                'default' => ($data['default'] == $opt_val_id ? 1 : 0),
            ];

            // Check if new, delete or update
            if ($status == 'delete' && strpos($opt_val_id, 'new') === false) {
                // delete this option value for all languages
                $this->deleteProductOptionValue($product_id, $opt_val_id);
            } elseif ($status == 'new') {
                // Need to create new oprion value
                $this->addProductOptionValueAndDescription(
                    $product_id,
                    $option_id,
                    $option_value_data
                );
            } else {
                // Existing need to update
                $this->updateProductOptionValueAndDescription(
                    $product_id, $opt_val_id, $option_value_data,
                    $language_id
                );
            }
        }

        (version_compare(VERSION, '1.4.0') >= 0) ? $this->cache->remove('product') : $this->cache->delete('product');

        return true;
    }

    /**
     * @param int $product_id
     * @param int $option_value_id
     *
     * @return array
     *
     * @throws AException
     */
    public function getProductOptionValue($product_id, $option_value_id)
    {
        $product_option_value = $this->db->query(
            'SELECT *
            FROM ' . $this->db->table('product_extra_option_values') . "
            WHERE product_id = '" . (int) $product_id . "'
                AND product_option_value_id = '" . (int) $option_value_id . "'
                AND group_id = 0
            ORDER BY sort_order"
        );
        /*
         $query = $this->db->query("SELECT *
                             FROM " . $this->db->table("product_extra_option_values") . " pov
                             WHERE pov.product_option_id = '" . (int)$product_option_id . "'
                                 AND pov.product_id = '" . (int)$product_id . "'
                             ORDER BY pov.sort_order");  */

        $option_value = $product_option_value->row;
        $value_description_data = [];
        $value_description = $this->db->query(
            'SELECT *
            FROM ' . $this->db->table('product_extra_option_value_descriptions') . "
            WHERE product_option_value_id = '" . (int) $option_value['product_option_value_id'] . "'"
        );

        foreach ($value_description->rows as $description) {
            // regular option value name
            $value_description_data[$description['language_id']]['name'] = $description['name'];
            // get children (grouped options) individual names array
            if ($description['grouped_attribute_names']) {
                $value_description_data[$description['language_id']]['children_options_names'] =
        unserialize($description['grouped_attribute_names']);
            }
        }

        $result = [
            'product_option_value_id' => $option_value['product_option_value_id'],
            'language' => $value_description_data,
            'sku' => $option_value['sku'],
            'quantity' => $option_value['quantity'],
            'subtract' => $option_value['subtract'],
            'price' => $option_value['price'],
            'prefix' => $option_value['prefix'],
            'weight' => $option_value['weight'],
            'weight_type' => $option_value['weight_type'],
            'attribute_value_id' => $option_value['attribute_value_id'],
            'grouped_attribute_data' => $option_value['grouped_attribute_data'],
            'sort_order' => $option_value['sort_order'],
            'default' => $option_value['default'],
        ];

        // get children (grouped options) data
        $child_option_values = unserialize($result['grouped_attribute_data']);
        if (is_array($child_option_values) && sizeof($child_option_values)) {
            $result['children_options'] = [];
            foreach ($child_option_values as $child_value) {
                $result['children_options'][$child_value['attr_id']] = (int) $child_value['attr_v_id'];
            }
        }

        return $result;
    }

    /**
     * @param int $product_id
     * @param int $option_id
     *
     * @return array
     *
     * @throws AException
     */
    public function getProductOptionValues($product_id, $option_id)
    {
        $result = [];

        $product_option_value = $this->db->query(
            'SELECT product_option_value_id FROM ' . DB_PREFIX . "product_extra_option_values
            WHERE product_option_id = '" . (int) $option_id . "'
                AND product_id = '" . (int) $product_id . "'
            ORDER BY sort_order");

        foreach ($product_option_value->rows as $option_value) {
            $result[] = $this->getProductOptionValue($product_id, $option_value['product_option_value_id']);
        }

        return $result;
    }

    public function getProducts($data = [], $mode = 'default')
    {
        $language_id = (int) $data['content_language_id'] ?: $this->config->get('storefront_language_id');
        $store_id = (int) ($data['store_id'] ?? $this->config->get('current_store_id'));

        if ($data || $mode == 'total_only') {
            $match = '';
            $filter = $data['filter'] ?? [];

            if ($mode == 'total_only') {
                $sql = 'SELECT COUNT(*) as total ';
            } else {
                $sql = 'SELECT ' . $this->db->getSqlCalcTotalRows() . ' DISTINCT pd.*, p.* ';
                $sql .= ', (SELECT 
                                CASE WHEN SUM(COALESCE(ppov.subtract,0))>0
                                 THEN SUM( CASE WHEN ppov.quantity > 0 THEN ppov.quantity ELSE 0 END)
                                ELSE pp.quantity END as quantity
                            FROM ' . $this->db->table('products') . ' pp
                            LEFT JOIN ' . $this->db->table('product_options') . ' ppo
                                ON ppo.product_id = pp.product_id
                            LEFT JOIN  ' . $this->db->table('product_option_values') . ' ppov
                                ON (ppo.product_option_id = ppov.product_option_id AND ppov.subtract>0)
                            WHERE pp.product_id = p.product_id
                            GROUP BY pp.product_id) as quantity ';
            }
            $sql .= ' FROM ' . $this->db->table('products') . ' p
                    LEFT JOIN ' . $this->db->table('product_descriptions') . " pd
                        ON (p.product_id = pd.product_id AND pd.language_id = '" . $language_id . "')
                    INNER JOIN " . $this->db->table('products_to_stores') . " ps
                        ON (p.product_id = ps.product_id AND ps.store_id = '" . $store_id . "') ";

            if ($filter['category']) {
                $sql .= ' INNER JOIN ' . $this->db->table('products_to_categories') . ' p2c 
                            ON (p.product_id = p2c.product_id) ';
            }

            $sql .= ' WHERE 1=1 ';

            if (!empty($data['subsql_filter'])) {
                $sql .= ' AND ' . $data['subsql_filter'];
            }

            if (isset($filter['match'])) {
                $match = $filter['match'];
            }

            if (isset($filter['exclude']['product_id'])) {
                $exclude = $filter['exclude']['product_id'];
                $excludes = [];
                if (is_array($exclude)) {
                    foreach ($exclude as $ex) {
                        $excludes[] = (int) $ex;
                    }
                } elseif ((int) $exclude) {
                    $excludes = [(int) $exclude];
                }

                if ($excludes) {
                    $sql .= ' AND p.product_id NOT IN (' . implode(',', $excludes) . ') ';
                }
            }

            if (isset($filter['keyword'])) {
                $keywords = explode(' ', $filter['keyword']);

                if ($match == 'any') {
                    $sql .= ' AND (';
                    foreach ($keywords as $k => $keyword) {
                        $sql .= $k > 0 ? ' OR' : '';
                        $sql .= " (LCASE(pd.name) LIKE '%" . $this->db->escape(mb_strtolower($keyword), true) . "%'";
                        $sql .= " OR LCASE(p.model) LIKE '%" . $this->db->escape(mb_strtolower($keyword), true) . "%'";
                        $sql .= " OR LCASE(p.sku) LIKE '%" . $this->db->escape(mb_strtolower($keyword), true) . "%')";
                    }
                    $sql .= ' )';
                } else {
                    if ($match == 'all') {
                        $sql .= ' AND (';
                        foreach ($keywords as $k => $keyword) {
                            $sql .= $k > 0 ? ' AND' : '';
                            $sql .= " (LCASE(pd.name) LIKE '%" . $this->db->escape(mb_strtolower($keyword), true) . "%'";
                            $sql .= " OR LCASE(p.model) LIKE '%" . $this->db->escape(mb_strtolower($keyword), true) . "%'";
                            $sql .= " OR LCASE(p.sku) LIKE '%" . $this->db->escape(mb_strtolower($keyword), true) . "%')";
                        }
                        $sql .= ' )';
                    } else {
                        if ($match == 'exact') {
                            $sql .= " AND (LCASE(pd.name) LIKE '%" . $this->db->escape(
                                mb_strtolower($filter['keyword']),
                                true
                            ) . "%'";
                            $sql .= " OR LCASE(p.model) LIKE '%" . $this->db->escape(
                                mb_strtolower($filter['keyword']),
                                true
                            ) . "%'";
                            $sql .= " OR LCASE(p.sku) LIKE '%" . $this->db->escape(
                                mb_strtolower($filter['keyword']),
                                true
                            ) . "%')";
                        } else {
                            if ($match == 'begin') {
                                $sql .= " AND (LCASE(pd.name) LIKE '"
                                    . $this->db->escape(
                                        mb_strtolower($filter['keyword']), true
                                    ) . "%'";
                                $sql .= " OR LCASE(p.model) LIKE '" . $this->db->escape(
                                    mb_strtolower($filter['keyword']),
                                    true
                                ) . "%'";
                                $sql .= " OR LCASE(p.sku) LIKE '" . $this->db->escape(
                                    mb_strtolower($filter['keyword']),
                                    true
                                ) . "%')";
                            }
                        }
                    }
                }
            }

            if (isset($filter['pfrom'])) {
                $sql .= " AND p.price >= '" . (float) $filter['pfrom'] . "'";
            }
            if (isset($filter['pto'])) {
                $sql .= " AND p.price <= '" . (float) $filter['pto'] . "'";
            }
            if ($filter['category']) {
                /** @var ModelCatalogCategory $mdl */
                $mdl = $this->load->model('catalog/category', 'storefront');
                $childrenIds = $mdl->getChildrenIds($filter['category'], 'all');
                $childrenIds[] = (int) $filter['category'];
                $childrenIds = array_filter(array_unique($childrenIds));
                $sql .= ' AND p2c.category_id IN (' . implode(',', $childrenIds) . ')';
            }
            if ($filter['sku']) {
                $sql .= " AND p.sku LIKE '%" . $this->db->escape($filter['sku']) . "%'";
            }
            if (isset($filter['status'])) {
                $sql .= " AND p.status = '" . (int) $filter['status'] . "'";
            }

            // If for total, we're done building the query
            if ($mode == 'total_only') {
                $query = $this->db->query($sql);

                return $query->row['total'];
            }

            $sort_data = [
                'product_id' => 'p.product_id',
                'name' => 'pd.name',
                'model' => 'p.model',
                'sku' => 'p.sku',
                'quantity' => 'quantity',
                'price' => 'p.price',
                'status' => 'p.status',
                'sort_order' => 'p.sort_order',
                'date_modified' => 'p.date_modified',
            ];

            if (isset($data['sort']) && array_key_exists($data['sort'], $sort_data)) {
                $sql .= ' ORDER BY ' . $sort_data[$data['sort']];
            } else {
                // for faster SQL set default to ID based order
                $sql .= ' ORDER BY p.product_id';
            }

            if (isset($data['order']) && ($data['order'] == 'DESC')) {
                $sql .= ' DESC';
            } else {
                $sql .= ' ASC';
            }

            if (isset($data['start']) || isset($data['limit'])) {
                $data['start'] = max(0, (int) $data['start']);
                $data['limit'] = $data['limit'] < 1 ? 20 : (int) $data['limit'];
                $sql .= ' LIMIT ' . $data['start'] . ',' . $data['limit'];
            }
            $query = $this->db->query($sql);
            $totalRows = $this->db->getTotalNumRows();
            $output = [];
            foreach ($query->rows as $row) {
                $row['total_num_rows'] = $totalRows;
                $output[] = $row;
            }

            return $output;
        } else {
            $cache_key = 'product.lang_' . $language_id;
            $product_data = $this->cache->pull($cache_key);
            if ($product_data === false) {
                $query = $this->db->query(
                    'SELECT *, p.product_id
                    FROM ' . $this->db->table('products') . ' p
                    LEFT JOIN ' . $this->db->table('product_descriptions') . " pd
                        ON (p.product_id = pd.product_id AND pd.language_id = '" . $language_id . "')
                    ORDER BY pd.name"
                );
                $product_data = $query->rows;
                $this->cache->push($cache_key, $product_data);
            }

            return $product_data;
        }
    }

    public function getTotalProductsByOptionId($option_id)
    {
        $query = $this->db->query('SELECT COUNT(*) AS total
      								FROM ' . DB_PREFIX . "product_to_option
      								WHERE option_id = '" . (int) $option_id . "'");

        return $query->row['total'];
    }
}
