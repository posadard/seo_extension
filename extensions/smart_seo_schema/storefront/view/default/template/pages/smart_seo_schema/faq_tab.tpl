<?php
if (!empty($faq_data) && $faq_data['enabled']) { ?>
<li><a href="#tab_<?php echo $faq_data['id']; ?>"><?php echo $faq_data['title']; ?></a></li>
<?php } ?>