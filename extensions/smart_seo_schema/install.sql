-- Smart SEO Schema - Database Installation
-- Creates table for persisting AI-generated content per product

DROP TABLE IF EXISTS `ac_seo_schema_content`;
CREATE TABLE IF NOT EXISTS `ac_seo_schema_content` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    
    -- AI-generated content fields
    `custom_description` text DEFAULT NULL,
    `faq_content` text DEFAULT NULL,
    `howto_content` text DEFAULT NULL,
    `review_content` text DEFAULT NULL,
    
    -- Schema configuration flags
    `enable_variants` tinyint(1) DEFAULT 1,
    `enable_faq` tinyint(1) DEFAULT 0,
    `enable_howto` tinyint(1) DEFAULT 0,
    `enable_review` tinyint(1) DEFAULT 0,
    
    -- Timestamps
    `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `product_id` (`product_id`),
    KEY `idx_product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;