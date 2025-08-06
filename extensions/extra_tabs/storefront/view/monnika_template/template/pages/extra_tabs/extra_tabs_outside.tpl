<?php
/* Tab button */
if ( !empty($weight) ){ ?>
<div class="extra_tabs">			
		<table width="80%" >
		  <tr>
			<th align="left"><?php if ($data_to_display == 0 || $data_to_display == 1) {echo $title_weight;}?></th>
			<th align="left"><?php if ($data_to_display == 0 || $data_to_display == 2) {echo $title_dimentions;}?></th>
		  </tr>
		  <tr>
			<td>
			<?php if ($weight['weight'] > 0 && ($data_to_display == 0 || $data_to_display == 1))
			{echo $weight['weight']. '&nbsp;' . $weight['weight_class'];}
			elseif ($weight['weight'] == 0 && ($data_to_display == 0 || $data_to_display == 1)) { echo $title_na;} 
			?>
			</td>
			<td>
			<?php if ($weight['length'] > 0 && ($data_to_display == 0 || $data_to_display == 2))
			{echo $title_length. '&nbsp;' . $weight['length']. '&nbsp;' . $weight['length_class'];} 
			elseif ($weight['length'] == 0 && ($data_to_display == 0 || $data_to_display == 2)) { echo $title_na;} ?></td>
		  </tr>
		  <tr>
			<td></td>
			<td><?php if ($weight['width'] > 0 && ($data_to_display == 0 || $data_to_display == 2))
			{echo $title_width. '&nbsp;' . $weight['width']. '&nbsp;' . $weight['length_class'];} 
			elseif ($weight['width'] == 0 && ($data_to_display == 0 || $data_to_display == 2)) { echo $title_na;} ?></td>
		  </tr>
		  <tr>
			<td></td>
			<td><?php if ($weight['height'] > 0 && ($data_to_display == 0 || $data_to_display == 2))
			{echo $title_height. '&nbsp;' . $weight['height']. '&nbsp;' . $weight['length_class'];}
			elseif ($weight['height'] == 0 && ($data_to_display == 0 || $data_to_display == 2)) { echo $title_na;} ?></td>
		  </tr>
		</table>
</div>
<?php } ?>