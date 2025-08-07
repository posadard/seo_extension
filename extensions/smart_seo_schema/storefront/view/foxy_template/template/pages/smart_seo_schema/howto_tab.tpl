<?php
if (!empty($howto_data) && $howto_data['enabled']) { ?>
<li class="smart-seo-howto-tab">
    <a href="#<?php echo $howto_data['id']; ?>" class="smart-seo-tab-link">
        <?php echo $howto_data['title']; ?>
    </a>
</li>
<?php } ?>