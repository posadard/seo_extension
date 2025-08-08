<?php
if (!empty($howto_data) && $howto_data['enabled']) { ?>
<li><a href="#tab_<?php echo $howto_data['id']; ?>"><?php echo $howto_data['title']; ?></a></li>
<?php } ?>