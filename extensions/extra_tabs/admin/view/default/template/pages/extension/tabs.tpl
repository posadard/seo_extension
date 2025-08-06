<?php $product_id = (int)$this->request->get['product_id'];
if (strpos($_SERVER['REQUEST_URI'], "product_extra_options") !== false)
{$classname = 'active';}
?>
<li class="<?php echo $classname; ?>"><a href="<?php echo $this->html->getSecureURL('catalog/product_extra_options', '&product_id=' . $product_id ); ?>"><span><?php echo $this->language->get('entry_admin_tab_name'); ?></span></a></li>