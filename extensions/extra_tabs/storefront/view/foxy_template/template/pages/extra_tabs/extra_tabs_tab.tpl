<?php

/* Tab */
$first = $all_options;
if (is_array($first) && array_shift($first) > 0) {
//if (array_shift($first) > 0) {
    foreach ($all_options as $value) {
        if ($value['required'] == 1 && $this->customer->isLogged()) {
            echo '<li><a href="#tab_' . $value['product_option_id'] . '">' . $value['name'] . '</a></li>';
        } elseif ($value['required'] == 0) {
            echo '<li><a href="#tab_' . $value['product_option_id'] . '">' . $value['name'] . '</a></li>';
        }
    }
}
