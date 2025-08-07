<?php

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

class ModelSmartSeoSchemaSmartSeoSchemaTabs extends Model
{
    public function getProductSchemaTabs($product_id)
    {
        if (!(int) $product_id) {
            return ['faq' => null, 'howto' => null];
        }

        $query = $this->db->query("
            SELECT faq_content, howto_content, 
                   show_faq_tab_frontend, show_howto_tab_frontend
            FROM " . DB_PREFIX . "seo_schema_content 
            WHERE product_id = " . (int)$product_id . "
            AND (show_faq_tab_frontend = 1 OR show_howto_tab_frontend = 1)
            LIMIT 1
        ");
        
        if (!$query->num_rows) {
            return ['faq' => null, 'howto' => null];
        }
        
        $data = $query->row;
        $tabs = ['faq' => null, 'howto' => null];
        
        if ($data['show_faq_tab_frontend'] && !empty(trim($data['faq_content']))) {
            $tabs['faq'] = [
                'id' => 'smart_seo_faq',
                'title' => 'FAQ',
                'content' => nl2br(htmlspecialchars($data['faq_content'])),
                'enabled' => true,
                'type' => 'faq'
            ];
        }
        
        if ($data['show_howto_tab_frontend'] && !empty(trim($data['howto_content']))) {
            $tabs['howto'] = [
                'id' => 'smart_seo_howto', 
                'title' => 'How To Use',
                'content' => nl2br(htmlspecialchars($data['howto_content'])),
                'enabled' => true,
                'type' => 'howto'
            ];
        }
        
        return $tabs;
    }
    
    public function isSchemaExtensionEnabled()
    {
        return (bool)$this->config->get('smart_seo_schema_status');
    }
}