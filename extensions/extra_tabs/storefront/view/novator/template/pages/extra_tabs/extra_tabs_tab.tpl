<?php
/* Tab */
$first = $all_options;
if (is_array($first) && array_shift($first) > 0) {
    echo '<style>
            a[href^="#collapsetab_"] {
                color: inherit;
                text-decoration: none; 
            }
      </style>';
    // extensions/novator/storefront/view/novator/template/pages/product/product.tpl#L355
    /*  foreach ($all_options as $value) {
         if ($value['required'] == 1 && $this->customer->isLogged()) {
             echo '<a href="#collapsetab_' . $value['product_option_id'] . '">' . $value['name'] . '</a>';
         } elseif ($value['required'] == 0) {
             echo '<a href="#collapsetab_' . $value['product_option_id'] . '">' . $value['name'] . '</a>';
         }
     } */
    foreach ($all_options as $value) {
        if ($value['required'] == 1 && $this->customer->isLogged()) {
            echo $value['name'];
        } elseif ($value['required'] == 0) {
            echo $value['name'];
        }
    }
}
