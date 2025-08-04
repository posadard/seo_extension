<?php
/*------------------------------------------------------------------------------
  Smart SEO Schema Assistant - Installation Script
  
  Creates database table and sets up extension data
  Based on AvaTax Integration pattern
------------------------------------------------------------------------------*/

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

/**
 * @var AController $this
 */

// Create the schema content table - execute queries separately
$drop_sql = "DROP TABLE IF EXISTS `" . DB_PREFIX . "seo_schema_content`";
$this->db->query($drop_sql);

$create_sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "seo_schema_content` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `custom_description` text DEFAULT NULL,
    `faq_content` text DEFAULT NULL,
    `howto_content` text DEFAULT NULL,
    `review_content` text DEFAULT NULL,
    `enable_variants` tinyint(1) DEFAULT 1,
    `enable_faq` tinyint(1) DEFAULT 0,
    `enable_howto` tinyint(1) DEFAULT 0,
    `enable_review` tinyint(1) DEFAULT 0,
    `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `product_id` (`product_id`),
    KEY `idx_product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

$this->db->query($create_sql);

// Log installation
$warning = new AWarning('Smart SEO Schema extension installed successfully. Database table created.');
$warning->toLog();