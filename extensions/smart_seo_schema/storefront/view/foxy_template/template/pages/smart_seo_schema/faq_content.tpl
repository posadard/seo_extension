<?php
$first = $all_options;
if (is_array($first) && array_shift($first) > 0) {
    foreach ($all_options as $value) {
        if ($value['product_option_id'] == 'smart_seo_faq' || $value['product_option_id'] == 'smart_seo_howto') {
            echo '<div id="tab_'.$value['product_option_id'].'" class="tab-pane">
            <div class="content">			
                <table width="95%" >';
                echo '<tr><th align="left">';
                echo $value['error_text'];
                echo '</th></tr>';
            foreach($value['option_value'] as $data) {
                echo '<tr><td>'; 
                echo html_entity_decode($data['name'], ENT_QUOTES, 'UTF-8');
                echo '</td></tr>';
            }
            echo '</table></div></div>';
        }
    }
}
?>