<?php

/* SQL */
if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

class ModelExtraTabsExtraTabs extends Model
{
    public function getProductGroupOptions($product_id, $option_id, $option_value_id)
    {
        if (empty($product_id) || empty($option_id)) {
            return [];
        }
        $product_option = $this->db->query(
            'SELECT group_id FROM ' . $this->db->table('product_extra_options') . "
			WHERE product_id = '" . (int) $product_id . "'
				AND product_option_id = '" . (int) $option_id . "' ");
        if (!$product_option->row['group_id']) {
            return [];
        }
        // get all option values of group
        $option_values = $this->db->query(
            'SELECT pov.*, povd.name
			 FROM ' . $this->db->table('product_extra_options') . ' po
			 LEFT JOIN ' . $this->db->table('product_extra_option_values') . ' pov ON (po.product_option_id = pov.product_option_id)
			 LEFT JOIN  ' . $this->db->table('product_extra_option_value_descriptions') . " povd
					ON (pov.product_option_value_id = povd.product_option_value_id AND povd.language_id = '" . (int) $this->config->get('storefront_language_id') . "' )
			 WHERE po.group_id = '" . (int) $product_option->row['group_id'] . "'
			 ORDER BY pov.sort_order ");

        $result = [];
        $attribute_value_id = null;
        foreach ($option_values->rows as $row) {
            if ($row['product_option_value_id'] == $option_value_id) {
                $attribute_value_id = $row['attribute_value_id'];
                break;
            }
        }
        $groups = [];
        foreach ($option_values->rows as $row) {
            if ($row['attribute_value_id'] == $attribute_value_id) {
                $groups[] = $row['group_id'];
            }
        }
        $groups = array_unique($groups);
        foreach ($groups as $group_id) {
            foreach ($option_values->rows as $row) {
                if ($row['group_id'] == $group_id && $row['product_option_id'] != $option_id) {
                    $result[$row['product_option_id']][$row['product_option_value_id']] = [
                        'name' => $row['name'],
                        'price' => $row['price'],
                        'prefix' => $row['prefix'],
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * @param int $product_id
     *
     * @return array
     */
    public function getProductOptions($product_id)
    {
        if (!(int) $product_id) {
            return [];
        }
        $language_id = (int) $this->config->get('storefront_language_id');

        if (version_compare(VERSION, '1.4.0') >= 0) {
            // $this->cache->remove('*');
            $product_option_data = $this->cache->pull('product.extra.options.' . $product_id, $language_id);
        } else {
            // $this->cache->delete('*');
            $product_option_data = $this->cache->get('product.extra.options.' . $product_id, $language_id);
        }
        
        $elements = HtmlElementFactory::getAvailableElements();
        if (empty($product_option_data)) {
            
            $product_option_data = [];
            $product_option_query = $this->db->query(
                'SELECT po.*, pod.option_placeholder, pod.error_text
                FROM ' . $this->db->table('product_extra_options') . ' po
                LEFT JOIN ' . $this->db->table('product_extra_option_descriptions') . " pod
                	ON pod.product_option_id = po.product_option_id AND pod.language_id =  '" . $language_id . "'
                WHERE po.product_id = '" . (int) $product_id . "'
                    AND po.group_id = 0
                    AND po.status = 1
                ORDER BY po.sort_order"
            );
            if ($product_option_query) {
                foreach ($product_option_query->rows as $product_option) {
                    $attribute_values = [];
                    $product_option_value_data = [];
                    $product_option_value_query = $this->db->query(
                        'SELECT *
                            FROM ' . $this->db->table('product_extra_option_values') . "
                            WHERE product_option_id = '" . (int) $product_option['product_option_id'] . "'
                            ORDER BY sort_order"
                    );
                    if ($product_option_value_query) {
                        foreach ($product_option_value_query->rows as $product_option_value) {
                            if ($product_option_value['attribute_value_id']) {
                                // skip duplicate attributes values if it is not grouped parent/child
                                if (in_array($product_option_value['attribute_value_id'], $attribute_values)) {
                                    continue;
                                }
                                $attribute_values[] = $product_option_value['attribute_value_id'];
                            }
                            $pd_opt_val_description_qr = $this->db->query(
                                'SELECT *
                                    FROM ' . $this->db->table('product_extra_option_value_descriptions') . "
                                    WHERE product_option_value_id = '" . (int) $product_option_value['product_option_value_id'] . "'
                                    AND language_id = '" . (int) $language_id . "'"
                            );

                            // ignore option value with 0 quantity and disabled subtract
                            if ((!$product_option_value['subtract'])
                                || ($product_option_value['quantity'] && $product_option_value['subtract'])
                            ) {
                                $product_option_value_data[$product_option_value['product_option_value_id']] = [
                                    'product_option_value_id' => $product_option_value['product_option_value_id'],
                                    'attribute_value_id' => $product_option_value['attribute_value_id'],
                                    'grouped_attribute_data' => $product_option_value['grouped_attribute_data'],
                                    'group_id' => $product_option_value['group_id'],
                                    'name' => $pd_opt_val_description_qr->row['name'],
                                    'option_placeholder' => $product_option['option_placeholder'],
                                    'regexp_pattern' => $product_option['regexp_pattern'],
                                    'error_text' => $product_option['error_text'],
                                    'children_options_names' => $pd_opt_val_description_qr->row['children_options_names'],
                                    'sku' => $product_option_value['sku'],
                                    'price' => $product_option_value['price'],
                                    'prefix' => $product_option_value['prefix'],
                                    'weight' => $product_option_value['weight'],
                                    'weight_type' => $product_option_value['weight_type'],
                                    'quantity' => $product_option_value['quantity'],
                                    'subtract' => $product_option_value['subtract'],
                                    'default' => $product_option_value['default'],
                                ];
                            }
                        }
                    }
                    $prd_opt_description_qr = $this->db->query(
                        'SELECT *
                        FROM ' . $this->db->table('product_extra_option_descriptions') . "
                        WHERE product_option_id = '" . (int) $product_option['product_option_id'] . "'
                            AND language_id = '" . (int) $language_id . "'"
                    );

                    $product_option_data[$product_option['product_option_id']] = [
                        'product_option_id' => $product_option['product_option_id'],
                        'attribute_id' => $product_option['attribute_id'],
                        'group_id' => $product_option['group_id'],
                        'name' => $prd_opt_description_qr->row['name'],
                        'option_placeholder' => $product_option['option_placeholder'],
                        'option_value' => $product_option_value_data,
                        'sort_order' => $product_option['sort_order'],
                        'element_type' => $product_option['element_type'],
                        'html_type' => $elements[$product_option['element_type']]['type'],
                        'required' => $product_option['required'],
                        'regexp_pattern' => $product_option['regexp_pattern'],
                        'error_text' => $product_option['error_text'],
                    ];
                }
            }

            $this->cache->push('product.extra.options.' . $product_id, $product_option_data, $language_id);
        }

        return $product_option_data;
    }

    /**
     * @param int $product_id
     * @param int $product_option_id
     *
     * @return array
     */
    public function getProductOption($product_id, $product_option_id)
    {
        if (!(int) $product_id || !(int) $product_option_id) {
            return [];
        }

        $query = $this->db->query('SELECT *
						FROM ' . $this->db->table('product_extra_options') . ' po
						LEFT JOIN ' . $this->db->table('product_extra_option_descriptions') . " pod ON (po.product_option_id = pod.product_option_id)
						WHERE po.product_option_id = '" . (int) $product_option_id . "'
							AND po.product_id = '" . (int) $product_id . "'
							AND pod.language_id = '" . (int) $this->config->get('storefront_language_id') . "'
						ORDER BY po.sort_order");

        return $query->row;
    }

    /**
     * @param $product_id
     * @param $product_option_id
     *
     * @return array
     */
    public function getProductOptionValues($product_id, $product_option_id)
    {
        if (!(int) $product_id || !(int) $product_option_id) {
            return [];
        }

        $query = $this->db->query('SELECT *
                            FROM ' . $this->db->table('product_extra_option_values') . " pov
                            WHERE pov.product_option_id = '" . (int) $product_option_id . "'
                                AND pov.product_id = '" . (int) $product_id . "'
                            ORDER BY pov.sort_order");

        return $query->rows;
    }

    /**
     * @param int $product_id
     * @param int $product_option_value_id
     *
     * @return array
     */
    public function getProductOptionValue($product_id, $product_option_value_id)
    {
        if (!(int) $product_id || !(int) $product_option_value_id) {
            return [];
        }

        $query = $this->db->query('SELECT *, COALESCE(povd.name,povd2.name) as name
                        FROM ' . $this->db->table('product_extra_option_values') . ' pov
                        LEFT JOIN ' . $this->db->table('product_extra_option_value_descriptions') . " povd
                        		ON (pov.product_option_value_id = povd.product_option_value_id
                        				AND povd.language_id = '" . (int) $this->config->get('storefront_language_id') . "' )
                        LEFT JOIN " . $this->db->table('product_extra_option_value_descriptions') . " povd2
                        		ON (pov.product_option_value_id = povd2.product_option_value_id
                        				AND povd2.language_id = '1' )
                        WHERE pov.product_option_value_id = '" . (int) $product_option_value_id . "'
                            AND pov.product_id = '" . (int) $product_id . "'
                        ORDER BY pov.sort_order");

        return $query->row;
    }

    /**
     * Check if any of inputed options are required and provided
     *
     * @param int $product_id
     * @param array $input_options
     *
     * @return array
     */
    public function validateProductOptions($product_id, $input_options)
    {
        $errors = [];
        if (empty($product_id) && empty($input_options)) {
            return [];
        }
        $product_options = $this->getProductOptions($product_id);
        if (is_array($product_options) && $product_options) {
            foreach ($product_options as $option) {
                if ($option['required']) {
                    if (empty($input_options[$option['product_option_id']])) {
                        $errors[] = $option['name'] . ': ' . $this->language->get('error_required_options');
                    } else {
                        // check default value for input and textarea
                        if (in_array($option['element_type'], ['I', 'T'])) {
                            reset($option['option_value']);
                            $key = key($option['option_value']);
                            $option_value = $option['option_value'][$key];

                            if ($option_value['name'] == $input_options[$option['product_option_id']]) {
                                $errors[] = $option['name'] . ': ' . $this->language->get('error_required_options');
                            }
                        }
                    }
                }

                if ($option['regexp_pattern'] && !preg_match($option['regexp_pattern'], (string) $input_options[$option['product_option_id']])) {
                    $errors[] = $option['name'] . ': ' . $option['error_text'];
                }
            }
        }

        return $errors;
    }
}
