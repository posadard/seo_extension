<?php foreach ($form['fields'] as $name => $field) {
	//Logic to calculate fields width
	$widthcasses = "col-sm-7";
	if (is_int(stripos($field->style, 'large-field'))) {
		$widthcasses = "col-sm-7";
	} else if (is_int(stripos($field->style, 'medium-field')) || is_int(stripos($field->style, 'date'))) {
		$widthcasses = "col-sm-5";
	} else if (is_int(stripos($field->style, 'small-field')) || is_int(stripos($field->style, 'btn_switch'))) {
		$widthcasses = "col-sm-3";
	} else if (is_int(stripos($field->style, 'tiny-field'))) {
		$widthcasses = "col-sm-2";
	}
	$widthcasses .= " col-xs-12";
	?>


			<!--<div class="form-group "><label class="control-label col-sm-3 col-xs-12" for="editFrm_<?php //echo $name; ?>"><?php
			/*if ($name == 'element_type'){echo $this->language->get('entry_' . $name.'_extra');}
			if ($name == 'sort_order'){echo $this->language->get('text_sort_order_extensions');}
			if ($name == 'required'){echo $this->language->get('entry_' . $name.'_extra');}
			if ($name == 'regexp_pattern'){//echo $this->language->get('entry_' . $name.'_extra');
			}
			if ($name == 'error_text'){echo $this->language->get('entry_' . $name.'_extra');}*/
			?></label></div>-->
<div class="form-group <?php echo !empty($error[$name]) ? "has-error" :''; ?>">
		<label class="control-label col-sm-3 col-xs-12" for="<?php echo $field->element_id; ?>">
		<?php //echo $this->language->get($name); //attribute_values
			if ($name == 'element_type'){//echo $this->language->get('entry_' . $name.'_extra');
			}
			if ($name == 'sort_order'){echo $this->language->get('text_sort_order_extensions');}
			if ($name == 'required'){echo $this->language->get('entry_' . $name.'_extra');}
			if ($name == 'regexp_pattern'){//echo $this->language->get('entry_' . $name.'_extra');
			}
			if ($name == 'error_text'){echo $this->language->get('entry_' . $name.'_extra');}
		?>
		</label>
		<div class="input-group afield <?php echo $widthcasses; ?> <?php echo($name == 'description' ? 'ml_ckeditor' : '') ?>"><?php echo $field; ?></div>
				<?php if (!empty($error[$name])) {  
				//print_r($field); 
				?>
			<span class="help-block field_err"><?php echo $error[$name]; ?></span>
				<?php } ?>
	</div>

				<?php if ($name == 'element_type') { 
		if ($child_count == 0) { ?>
			<div id="values" style="display: none;">
				<label class="control-label col-sm-3 col-xs-12"></label>
				<div class="input-group afield cl-sm-7">
				<table class="table table-narrow">
					<!--<thead>
						<tr>
							<th><?php //echo $entry_element_values; ?></th>
							<th><?php echo $column_sort_order; ?></th>
							<th><?php echo $column_txt_id; ?></th>
							<th></th>
						</tr>
					</thead>--><?php echo $extra_tabs_save_note; ?>
					<tbody>
							<?php foreach ($form['fields']['attribute_values'] as $atr_val_id => $atr_field){ ?>
						<tr id="<?php echo $atr_val_id;?>"  class="value">
							<td class="texteditordiv"><?php echo $atr_field['attribute_value_ids']; ?><?php echo $atr_field['values']; ?></td>
							<!--render txt_id to be able to save -->
							<td style="display:none;"><?php $atr_field['sort_order']->style = 'col-sm-2';
								echo $atr_field['sort_order'];  ?></td>
							<td style="display:none;"><?php $atr_field['txt_id']->style = 'col-sm-2';
								echo $atr_field['txt_id']; ?>
                            					</td>
							<!--<td>&nbsp;<a class="remove btn btn-danger-alt" title="<?php //echo $button_remove; ?>">
							<i class="fa fa-minus-circle"></i></a></td>-->
						</tr>
					<?php } ?>
					<!--<tr>
						<td></td>
						<td></td>
						<td>
							<a href="#" title="<?php //echo $button_add ?>" id="add_option_value" class="btn btn-success"><i
										class="fa fa-plus-circle fa-lg"></i></a>
						</td>
					</tr>-->
					</tbody>
				</table>
				</div>
			</div>
		<?php } else { ?>
			<!--<div id="values">
				<label class="control-label col-sm-3 col-xs-12"><?php //echo $entry_children_attributes; ?></label>
				<div class="input-group afield cl-sm-7">
					<ul class="list-group">
						<?php foreach ($children as $child) { ?>
							<li class="list-group-item"><a href="<?php //echo $child['link']; ?>"><?php //echo $child['name']; ?></a></li>
						<?php } ?>
					</ul>
				</div>
			</div>-->
		<?php } ?>


	<?php } ?>

	<?php if ($name == 'attribute_parent') { ?>
		<div class="input-group afield cl-sm-7"><?php echo $text_parent_note; ?></div>
	<?php } ?>

<?php } //foreach ?>

<script type="text/javascript">
	jQuery(function ($) {

		var elements_with_options = [];
		<?php
		foreach ($elements_with_options as $el) {
			echo "elements_with_options.push('$el');\r\n";
		} ?>

		function addValue(val) {
			var add = $('#values a.add');
			$(add).before($(add).prev().clone());
			$('input', $(add).prev()).val(val);
		}

		$('#values .aform').show();
		$('#values a.remove').on('click', function () {
			var current = $(this);
			if ($('#values div.value').length > 1) {
				if ($(current).parent().find('input[name^=attribute_value_ids]').val() == 'new') {
					$(current).parent().remove();
				}
				else {
					$(current).parent().toggleClass('toDelete');
				}
			}
		});
		$('#values a.add').on('click', function () {
			$(this).before($(this).prev().clone());
			$('input', $(this).prev()).val('');
			$('input[name^=attribute_value_ids]', $(this).prev()).val('new');
			$('input[name^=attribute_value_ids]', $(this).prev()).attr("name", "attribute_value_ids[]");
			$('input[name^=values]', $(this).prev()).attr("name", "values[]");
			$('input[name^=sort_orders]', $(this).prev()).attr("name", "sort_orders[]");
			$('#values .value').last().removeClass('toDelete');
		});

		if ($.inArray($('#editFrm_element_type').val(), elements_with_options) > -1) {
			$('#values').show();
		}

		if ($('#editFrm_element_type').val() == 'U') {
			$('#file_settings').show();
		} else {
			$('#file_settings').hide();
		}

		$('#editFrm_element_type').change(function () {
			if ($.inArray($(this).val(), elements_with_options) > -1) {
				$('#values').show();
			} else {
				$('#values').hide();
			}

			if ($(this).val() == 'U') {
				$('#file_settings').show();
			} else {
				$('#file_settings').hide();
			}
		});

		$('#editFrm_attribute_parent_id').change(function () {
			var attribute_id = $(this).val();
			if (attribute_id == '') {
				$('#editFrm_attribute_type_id')
						.val('')
						.change()
						.removeAttr('disabled');
				return false;
			}
			$.ajax({
				url: '<?php echo $get_attribute_type; ?>' + '&attribute_id=' + attribute_id,
				type: 'GET',
				dataType: 'json',
				success: function (json) {
					$('#editFrm_attribute_type_id')
							.val(json)
							.change()
							.attr('disabled', 'disabled');
				},
				error: function (jqXHR, textStatus, errorThrown) {
					$('#content').prepend('<div class="error" align="center"><b>' + textStatus + '</b>  ' + errorThrown + '</div>');
				}
			});

		});
		if ($('#editFrm_attribute_parent_id').val() != '') {
			$('#editFrm_attribute_parent_id').change();
		}

		$('#editFrm').submit(function () {
			//  save fix. Load texteditor for submit
			var div = $('td.texteditordiv div').first().attr( 'id' );
			//alert(div);
			$('a[href="#visual_'+div+'"]').click();

			$('#values .danger input[name^=attribute_value_ids]').val('delete');
			$(":disabled", this).removeAttr('disabled');

		});

		$('#file_settings .aform').show();

	});



</script>

<script type="text/javascript">

var inputs = document.getElementsByTagName("textarea");
for (var i=0; i < inputs.length; i++)
{var findid = inputs[i].getAttribute('id');}



//select selectbox type and hide
$("#editFrm_element_type").val('S');
$('#editFrm_element_type').addClass("hide");
//$('.select_element').css('display','none');
$('#editFrm_attribute_parent_id').addClass("hide");
$('#editFrm_regexp_pattern').addClass("hide");
$('.hide').css('display','none');

/*


//ckeditor
if (document.getElementById(findid)){
var re = /(\d+)/;
var found = findid.match(re);
	if (found){
	var onlyid = found[1];
	//alert(found[1]);
	$('#editFrm_values\\['+onlyid+'\\]').parents('.afield').addClass("myclass");
	$('.myclass').css('width','750px');
    $('#editFrm_values\\['+onlyid+'\\]').parents('.afield').removeClass('mask2');
	$('#editFrm_values\\['+onlyid+'\\]').cleditor();
	}
	else{

	$('#editFrm_values\\[\\]').parents('.afield').addClass("myclass");
	$('.myclass').css('width','750px');
    $('#editFrm_values\\[\\]').parents('.afield').removeClass('mask2');
	$('#editFrm_values\\[\\]').cleditor();
}




CKEDITOR.replace(findid,
    {//uiColor: '#0B46B5',
	    filebrowserBrowseUrl:false,
        filebrowserImageBrowseUrl:'<?php //echo $rl; ?>',
        filebrowserWindowWidth:'920',
        filebrowserWindowHeight:'520',
        language:'<?php //echo $language_code; ?>',
		toolbar:'Full',
		colorButton_enableMore:true,

    });
	}

*/


/* parse hide label */

//hide Attribute Parent label
function parseTable() {
var tbls = document.getElementsByTagName("label");
tbls[2].style.display = "none";
for (var i=0; i<1; i++) { //(var i=0; i<tbls.length; i++)


 //for (var r=2; r<tbls[i].rows.length; r++) { // do
  //
  //tbls[i].rows[r].style.display = "none";

 // }
}
}
parseTable();



//hide <span class="input-group-addon"> required
$('.input-group-addon').css('display', 'none');

//hide array
var rspan = document.getElementsByClassName("input-group afield col-sm-7 col-xs-12 ");
rspan[5].style.display = "none";
/*for (var i=0; i<1; i++) {
rspan[0].css('display','none');
 }*/
//rspan.style.display = "none";

//hide Addmedia button
$('.add_media').css('display', 'none');


//fix editor width
$('.zennable').css('position', 'relative');

//fix textarea width on insert
$('#editFrm_values').css('width', '500px');
</script>
