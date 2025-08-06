<?php

/* Tab */
$first = $all_options;
if (is_array($first) && array_shift($first) > 0) {
    echo '<style>
            a[href^="#tab_"] {
                color: inherit;
                text-decoration: none; 
            }
          li a[href="#tab_fb_comment"] {
 
          }
      </style>';
   // storefront/view/default/template/pages/product/product.tpl#L522
    foreach ($all_options as $value) {
        if ($value['required'] == 1 && $this->customer->isLogged()) {
            echo '<a href="#tab_' . $value['product_option_id'] . '">' . $value['name'] . '</a>';
        } elseif ($value['required'] == 0) {
            echo '<a href="#tab_' . $value['product_option_id'] . '">' . $value['name'] . '</a>';
        }
    }
}
