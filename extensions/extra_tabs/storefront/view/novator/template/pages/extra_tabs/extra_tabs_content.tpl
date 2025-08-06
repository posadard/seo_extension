<?php
/* Tab content */
$first = $all_options;
if (is_array($first) && array_shift($first) > 0) 
//if (array_shift($first) > 0)
{
foreach($all_options as $value)
{
		
		
	if($value['required'] == 1 && $this->customer->isLogged() )
	{
	echo '<div id="tab_'.$value['product_option_id'].'" class="tab-pane">
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
	}
	elseif ($value['required'] == 0)
	{
	echo '<div id="tab_'.$value['product_option_id'].'" class="tab-pane">
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
	}

	
}
}
?>