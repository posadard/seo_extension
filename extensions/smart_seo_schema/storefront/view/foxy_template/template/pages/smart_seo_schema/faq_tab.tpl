<?php
$first = $all_options;
if (is_array($first) && array_shift($first) > 0) {
    foreach ($all_options as $value) {
        echo '<li><a href="#tab_' . $value['product_option_id'] . '">' . $value['name'] . '</a></li>';
    }
}
?>