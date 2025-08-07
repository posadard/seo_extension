<?php
if (!empty($howto_data) && $howto_data['enabled']) { ?>
<div id="<?php echo $howto_data['id']; ?>" class="tab-pane smart-seo-tab-content">
    <div class="content">
        <div class="smart-seo-howto-content">
            <?php echo $howto_data['content']; ?>
        </div>
    </div>
</div>
<?php } ?>