<?php

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

//$this->db->query("DELETE FROM ".DB_PREFIX."global_attributes_types WHERE type_key='extra_tab' ;");

(version_compare(VERSION, '1.4.0') >= 0) ? $this->cache->remove('*') : $this->cache->delete('*');
