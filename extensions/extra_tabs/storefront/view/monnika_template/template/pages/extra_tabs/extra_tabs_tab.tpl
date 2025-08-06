<?php
/* Tab */
$first = $all_options;
if (is_array($first) && array_shift($first) > 0) 
//if (array_shift($first) > 0)
{
foreach($all_options as $value)
{
if($value['required'] == 1 && $this->customer->isLogged() )
{
  echo '<li class="accordion__item js-contentToggle" data-default-state="close"><!--<a href="#tab_'.$value['product_option_id'].'">--><button class="accordion__trigger js-contentToggle__trigger" type="button">'. $value['name'] . '<i class="fa fa-caret-down pull-right" aria-hidden="true"></i></button><!--</a>-->';
  
  	echo '<div class="accordion__content is-hidden js-contentToggle__content" id="producttag">
  	<div class="content">
  		<table width="95%" >';
  			//header
  		echo '<tr><th align="left">';
  		echo $value['error_text'];
  		echo '</th></tr>';
  	foreach($value['option_value'] as $data)
  	{

  		//data
  		echo '<tr><td>';
  		echo html_entity_decode($data['name'], ENT_QUOTES, 'UTF-8');
  		//$data['name'];
  		echo '</td></tr>';
  	}
  	echo	'</table></div></div>';

  echo '</li>';
}






elseif ($value['required'] == 0)
{
  echo '<li class="accordion__item js-contentToggle" data-default-state="close"><!--<a href="#tab_'.$value['product_option_id'].'">--><button class="accordion__trigger js-contentToggle__trigger" type="button">'. $value['name'] . '<i class="fa fa-caret-down pull-right" aria-hidden="true"></i></button><!--</a>-->';



  	echo '<div class="accordion__content is-hidden js-contentToggle__content" id="producttag">
  	<div class="content">
  		<table width="95%" >';
  	//header
  		echo '<tr><th align="left">';
  		echo $value['error_text'];
  		echo '</th></tr>';
  	foreach($value['option_value'] as $data)
  	{

  		//data
  		echo '<tr><td>';
  		echo html_entity_decode($data['name'], ENT_QUOTES, 'UTF-8');
  		echo '</td></tr>';
  	}
  	echo	'</table></div></div>';

  echo '</li>';
}

}
}
?>
