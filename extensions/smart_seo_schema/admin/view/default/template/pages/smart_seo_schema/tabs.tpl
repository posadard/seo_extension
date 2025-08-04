<!-- Smart SEO Schema Tabs Template --> 
<!-- Basado en patron AvaTax para integracion de tabs --> 
 
<?php if ( !empty($tabs) ){ 
    foreach ( $tabs as $tab ){ ?> 
        <li <?php echo ( $tab['active'] ? 'class="active"' : '' ) ?>> 
            <a href="^<?php echo $tab['href']; ?^>"><span><?php echo $tab['text']; ?></span></a> 
        </li> 
<?php }} ?> 
