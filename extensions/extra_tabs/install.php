<?php

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

$valgl = $this->db->query('SELECT * FROM `' . DB_PREFIX . "global_attributes_types` WHERE `type_key` = 'extra_tab' ");

if (empty($valgl->num_rows)) {
    $this->db->query('INSERT INTO ' . DB_PREFIX . "global_attributes_types (`type_key`, `controller`, `sort_order`, `status`) VALUES ('extra_tab', 'responses/load_extra/product/getProductOptionSubform', '3', '1');");

    $last_id = $this->db->getLastId();
    $this->language->replaceDescriptions('global_attributes_type_descriptions',
        ['attribute_type_id' => (int) $last_id],
        [(int) $this->language->getDefaultLanguageID() => [
            'type_name' => 'Extra Tab',
        ]]);
} else {
}

$val = $this->db->query("show tables like '" . DB_PREFIX . "product_extra_options'");

if (empty($val->num_rows)) {// not exist
    $this->db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "product_extra_options(
 `product_option_id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL DEFAULT '0',
  `sort_order` int(3) NOT NULL DEFAULT '0',
  `status` int(1) NOT NULL DEFAULT '1',
  `element_type` char(1) NOT NULL DEFAULT 'I',
  `required` smallint(1) NOT NULL default '0',
  `regexp_pattern` varchar(255) NOT NULL default '',
  PRIMARY KEY (`product_option_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1 ");

    $this->db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "product_extra_option_descriptions(
 `product_option_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_general_ci NOT NULL COMMENT 'translatable',
  `option_placeholder` varchar(255) COLLATE utf8_general_ci DEFAULT '' COMMENT 'translatable',
  `error_text` 	varchar(255) COLLATE utf8_general_ci NOT NULL COMMENT 'translatable',
  PRIMARY KEY (`product_option_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");

    $this->db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "product_extra_option_values(
 `product_option_value_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_option_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL DEFAULT '0',
  `sku` varchar(255) DEFAULT NULL,
  `quantity` int(4) NOT NULL DEFAULT '0',
  `subtract` int(1) NOT NULL DEFAULT '0',
  `price` decimal(15,4) NOT NULL,
  `prefix` char(1) COLLATE utf8_general_ci NOT NULL, -- % or $
  `weight` decimal(15,8) NOT NULL,
  `weight_type` varchar(3) COLLATE utf8_general_ci NOT NULL, -- lbs or %
  `attribute_value_id` int(11),
  `grouped_attribute_data` text DEFAULT NULL,
  `sort_order` int(3) NOT NULL,
  `default` smallint DEFAULT 0,
  PRIMARY KEY (`product_option_value_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1 ");

    $this->db->query('CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . "product_extra_option_value_descriptions(
 `product_option_value_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` text COLLATE utf8_general_ci DEFAULT NULL COMMENT 'translatable',
  `grouped_attribute_names` text COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`product_option_value_id`,`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ");
} else { // exist
}

(version_compare(VERSION, '1.4.0') >= 0) ? $this->cache->remove('*') : $this->cache->delete('*');
