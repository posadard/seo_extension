<?php
/* Tab content */
$first = $all_options;
if (is_array($first) && array_shift($first) > 0) {
    // if (array_shift($first) > 0) {
    foreach ($all_options as $value) {
        if ($value['required'] == 1 && $this->customer->isLogged()) {
        } elseif ($value['required'] == 0) {
        }
    }
}
