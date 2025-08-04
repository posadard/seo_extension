-- Smart SEO Schema - Database Cleanup
-- Removes all extension data on uninstall

DELETE FROM `ac_settings` WHERE `group` = 'smart_seo_schema';
DROP TABLE IF EXISTS `ac_seo_schema_content`;