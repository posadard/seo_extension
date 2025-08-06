<style>

textarea.large-field, textarea.xl-field {
    /*max-width: 550px;*/
}

.zennable {
    min-width: 530px;
  }
#product_form_option {
  margin-top: 24px;
}
table {

    width: 100%;
}

.content_language {
  margin: 20px;
  z-index: 999;
}
#option_values {
  margin-top: auto;
}
.help {
    color: #999999;
    display: block;
    font-family: Verdana,Geneva,sans-serif;
    font-size: 10px;
    font-weight: normal;
}

.editOption {
    background-color: #EDF0F0;
    margin: 10px 0;
    padding: 5px;
    width: 99%;
}

.option_field select.attribute_list {
   // border: medium none;
    width: 265px;
}

.fieldset {
    margin: 0 0 10px;
    padding: 12px 0 0;
    position: relative;
}
.fieldset .heading {
    background: none repeat scroll 0 0 #FFFFFF;
    color: #11558F;
    display: inline-block;
    font: 18px Arial,Helvetica,sans-serif;
    left: 15px;
    padding: 0 5px;
    position: absolute;
    top: 0;
}

.options_buttons {
    border-top: 1px solid #DDDDDD;
    margin-top: 15px;
   // overflow: hidden;
    padding-top: 10px;
   // width: 235px;
}
.options_buttons button {
    margin-top: 10px;
}

.option_form_div {
    display: inline-flex;
    margin-right: 20px;
    margin: 10px;
    white-space: nowrap;
}
.editOption {
    background-color: #EDF0F0;
    margin: 10px 0;
    padding: 5px;
    width: 100%;
}
</style>

<?php echo $summary_form; ?>
<?php echo $product_tabs ?>
<?php if (!empty($error['warning'])) { ?>
	<div class="warning alert alert-error"><?php echo_html2view($error['warning']); ?></div>
<?php } ?>
<?php if ($success) { ?>
	<div class="success alert alert-success"><?php echo_html2view($success); ?></div>
<?php } ?>
<a name="top"></a>

<div id="content" class="panel panel-default" style="min-height:665px;">




	<div class="cbox_tl">
		<div class="cbox_tr">
			<div class="cbox_tc">
				<div class="heading icon_title_product"><?php //echo $form_title; ?></div>
				<?php //echo $product_tabs ?>
				<div class="common_content_actions pull-right">&nbsp;
					<!--<?php /* if (!empty ($help_url)) : ?>
						<div class="help_element"><a href="<?php //echo $help_url; ?>" target="new"><img
										src="<?php //echo $template_dir; ?>image/icons/help.png"/></a></div>
					<?php endif;  */?>-->
					<?php echo $form_language_switch; ?>
					<!--<div style="display: none;" id="message">
			<div role="alert" class="alert alert-success growl-animated animated fadeInDown" data-growl="container" data-growl-position="top-right" style="position: fixed; margin: 0px; z-index: 99999; display: inline-block; top: 83px; right: 20px;"><button data-growl="dismiss" class="close" type="button" style="display: inline-block; position: absolute; top: 5px; right: 10px; z-index: 99998;"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button><span data-growl="icon" class="fa fa-check"></span><span data-growl="title"></span><span data-growl="message">&nbsp;&nbsp;Saved Successfully&nbsp;&nbsp;&nbsp;</span><a data-growl="url" href="#"></a></div>
			</div>-->
				</div>
			</div>
		</div>
	</div>
	<div class="cbox_cl" style="padding:10px;">
		<div class="cbox_cr">
			<div class="cbox_cc">

				<?php //echo $summary_form; ?>

				<div class="fieldset">
					<div class="heading"><div id="tab_option"><?php echo $this->language->get('entry_admin_tab_name_products'); ?></div></div>
					<div class="top_left">
						<div class="top_right">
							<div class="top_mid"></div>
						</div>
					</div>
					<div class="cont_left">
						<div class="cont_right">
							<div class="cont_mid">

								<div class="option_form">
									<div class="option_field" style=" float: left; width: 250px;">
										<div class="aform">
											<div class="afield mask2">
												<div class="tl">
													<div class="tr">
														<div class="tc"></div>
													</div>
												</div>
												<div class="cl">
													<div class="cr">
														<div class="cc">
															<select id="product_form_option" size="10"
																	class="attribute_list static_field">
																<?php foreach ($product_options as $product_option) { ?>
																	<option value="<?php echo $product_option['product_option_id']; ?>"><?php echo $product_option['language'][$language_id]['name']; ?></option>
																<?php } ?>
															</select>
														</div>
													</div>
												</div>
												<div class="bl">
													<div class="br">
														<div class="bc"></div>
													</div>
												</div>
											</div>
										</div>




										<div class="options_buttons">
											<?php echo $form['form_open']; ?>
											<table cellpadding="3" cellspacing="0">
												<tr>
													<td colspan="2"><?php
													echo $this->language->get('entry_admin_tab_templates_select').'<br />';
													echo $attributes;   ?></td>
												</tr>
											</table>
											<table cellpadding="4" cellspacing="0" id="option_name_block" style="width:230px; margin: 10px;">
												<tr>
													<td><?php echo $entry_status; ?></td>
													<td><div class="input-group afield"><?php echo $status; ?></div><br></td>
												</tr>
												<tr>
													<td><?php echo $this->language->get('entry_admin_new_tab_name'); ?></td>
													<td>
														<?php echo $option_name; ?>
														<div class="error"
															 style="display:none"><?php echo $error_required ?></div><br>
													</td>
												</tr>
												<tr>
													<td><?php //echo $entry_element_type; ?></td>
													<td style="display:none">
														<?php echo $element_type; ?>
														<div class="error" style="display:none;"><?php echo $error_required ?></div>
													</td>
												</tr>
												<tr>
													<td><?php echo $entry_sort_order; ?></td>
													<td><?php echo $sort_order; ?></td>
												</tr>
												<tr>
													<td><?php echo $this->language->get('entry_admin_require_login'); ?></td>
													<td><div class="input-group afield "><?php echo $required; ?></div></td>
												</tr>
											</table>
											<button type="submit" class="pull-right btn btn-default tooltips dropdown-toggle"><?php
													$form['submit'] = '<span title="Add Option" class="button1" id="product_form_submit"><span><i class="fa fa-th-list"></i> '.$this->language->get('entry_admin_add_tab_button').'</span></span>';
													echo $form['submit']; ?></button>
											<!--<button type="reset" class="btn btn-default" style="display:none"
													id="option_name_reset"><i class="fa fa-refresh"></i><?php //echo $button_reset; ?></button>-->
											</form>
										</div>
									</div>

									<div id="options" style="margin-left: 330px;">
										<div id="option_values"></div>
									</div>
								</div>

							</div>
						</div>
					</div>
					<div class="bottom_left">
						<div class="bottom_right">
							<div class="bottom_mid"></div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
	<div class="cbox_bl">
		<div class="cbox_br">
			<div class="cbox_bc"></div>
		</div>
	</div>
</div>
<?php //echo $resources_scripts; ?>
<script type="text/javascript"><!--
/*var setRLparams = function(attr_val_id) {
	urls.resource_library = '<?php echo $rl_rl_path; ?>&object_id=' + attr_val_id;
	urls.resources = '<?php echo $rl_resources_path; ?>&object_id=' + attr_val_id;
	urls.unmap = '<?php echo $rl_unmap_path; ?>&object_id=' + attr_val_id;
	urls.attr_val_id = attr_val_id;
}

var openRL = function(attr_val_id) {
	setRLparams(attr_val_id);
	mediaDialog('image', 'add', attr_val_id);
}


// override rl js-script function
var loadMedia = function (type) {
	if (!urls.attr_val_id) return;
	var type = "image";
	$.ajax({
		url: urls.resources,
		type: 'GET',
		data: { type: type },
		dataType: 'json',
		success: function (json) {


			var html = '';
			$(json.items).each(function (index, item) {
				var src = '<img src="' + item['thumbnail_url'] + '" title="' + item['name'] + '" />';
				if (type == 'image' && item['resource_code']) {
					src = item['thumbnail_url'];
				}
				html += '<span id="image_row' + item['resource_id'] + '" class="image_block">\
                <a class="resource_edit" type="' + type + '" id="' + item['resource_id'] + '">' + src + '</a><br /></span>';
			});
			html += '<span class="image_block"><a class="resource_add" type="' + type + '"><img src="<?php echo $template_dir.'/image/icons/icon_add_media.png'; ?>" alt="<?php echo $text_add_media; ?>"/></a></span>';

			$('#rl_' + urls.attr_val_id).html(html);
			if ($(json.items).length) {
				$('a.resource_edit').unbind('click');
				$('a.resource_edit').click(function () {
					setRLparams($(this).parent().parent().prop('id').replace('rl_', ''));
					mediaDialog($(this).prop('type'), 'update', $(this).prop('id'));
					return false;
				})
			}
			$('a.resource_add').unbind('click');
			$('a.resource_add').click(function () {
				setRLparams($(this).parent().parent().prop('id').replace('rl_', ''));
				mediaDialog($(this).prop('type'), 'add', $(this).prop('id'));
				return false;
			});
		},
		error: function (jqXHR, textStatus, errorThrown) {
			$('#type_' + type).show();
			$('#rl_' + urls.attr_val_id).html('<div class="error" align="center"><b>' + textStatus + '</b>  ' + errorThrown + '</div>');
		}
	});

}


var mediaDialog = function (type, action, id) {
	$('#dialog').remove();

	var src = urls.resource_library + '&' + action + '=1&type=' + type;

	if (id) {
		src += '&resource_id=' + id;
	}
	$('#content').prepend('<div id="dialog" style="padding: 3px 0px 0px 0px;"><iframe src="' + src + '" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no" scrolling="auto"></iframe></div>');
	$('#dialog iframe').load(function (e) {
		try {
			var error_data = $.parseJSON($(this).contents().find('body').html());
		} catch (e) {
			var error_data = null;
		}
		if (error_data && error_data.error_code) {
			$('#dialog').dialog('close');
			httpError(error_data);
		}
	});

	$('#dialog').dialog({
		title: '<?php echo_html2view($text_resource_library); ?>',
		close: function (event, ui) {
			loadMedia(type);
		},
		width: 900,
		height: 500,
		resizable: false,
		modal: true
	});
};*/

var text = {
	error_attribute_not_selected: '<?php echo_html2view($error_attribute_not_selected); ?>',
	text_expand: '<?php echo_html2view($text_expand); ?>',
	text_hide: '<?php echo_html2view($text_hide); ?>'
};
var opt_urls = {
	load_option: '<?php echo $url['load_option'] ?>',
	update_option: '<?php echo $url['update_option'] ?>',
	get_options_list: '<?php echo $url['get_options_list'] ?>'
};
var current_option_id = null;
var row_id = 1;






var is_editor_active = function(editor_id){

        if(typeof tinyMCE == 'undefined'){
            return false;
        }

        if( typeof editor_id == 'undefined' ){
            editor = tinyMCE.activeEditor;
        }else{
            editor = tinyMCE.EditorManager.get(editor_id);
        }

        if(editor == null){
            return false;
        }

         return !editor.isHidden();

    };


    function myFunctiondelay() {
      if(is_editor_active()){
        console.log( "active editor found" );
        // do stuff
          tinymce.remove(); //added to fix multiply editors on product page
          //editor.remove();
          tinymce.init({selector:".visual_editor",
          height:"330",
          //theme: 'silver',
            //plugins: [
             // 'advlist autolink lists link image charmap print preview hr anchor pagebreak',
              // 'searchreplace wordcount visualblocks visualchars code fullscreen',
              // 'insertdatetime media nonbreaking save table contextmenu directionality',
              // 'emoticons template paste textcolor colorpicker textpattern imagetools codesample'
			  // ],
            toolbar1: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
            toolbar2: 'print preview media | emoticons | codesample',
            image_advtab: true,
          });

        }
        else{
            console.log( "active editor search delay" );
            setTimeout(myFunctiondelay, 1000);
        }

    }
    //myfunction




jQuery(function ($) {

	$("#option_name_block").hide();
	$("#product_form").submit(function () {
	//var data = CKEDITOR.instances.cke_option_value_form_name23.getData();
	//CKEDITOR.instances['findid'].getData();
		if ($("#new_option_form_attribute_id").val() == 'new' && ( $("#new_option_form_option_name").val() == '' || $("#new_option_form_element_type").val() == ''  )) {
			if (!$("#option_name_block").is(':visible')) {
				$("#option_name_block").show();
				$("#option_name_reset").show();
				return false;
			}
			if ($("#new_option_form_option_name").val() == '') {
				$("#new_option_form_option_name").focus();
				$("#new_option_form_option_name").closest("span").next().next().show();
			} else {
				$("#new_option_form_option_name").closest("span").next().next().hide();
			}

			if ($("#new_option_form_element_type").val() == '') {
				$("#new_option_form_element_type").focus();
				$("#new_option_form_element_type").closest("span").next().next().show();
			} else {
				$("#new_option_form_element_type").closest("span").next().next().hide();
			}

			return false;
		}

    console.log( "submit trigger" );
    //setTimeout(myFunctiondelay, 500);
    tinymce.remove(); //added to fix multiply editors on product page
    myFunctiondelay();
	});

var updateOptions = function() {

		$.ajax({
			url: opt_urls.get_options_list,
			type: 'GET',
			dataType: 'json',
			success: function (json) {
				$("#product_form_option option").remove();
				for (var key in json) {
					$("#product_form_option").append($('<option value="' + key + '">' + json[key] + '</option>'));
				}
				if ( json ) {
					//$('#message').show();
					success_alert('<?php echo_html2view($text_success_option); ?>',true);
						//window.location.href = result.href;
					} else {
					//	$('#track_ship_loading').hide();
					//	$('#track_ship_submit').show();
						$('#message').html('<?php echo_html2view($error_service_unavailable); ?>');
						$('#message').removeAttr('class').addClass('warning');
						$('#message_td').show();
					}
			},
			global: false,
			error: function (jqXHR, textStatus, errorThrown) {
				error_alert(errorThrown);
			}
		});

    console.log( "update trigger" );
    //setTimeout(myFunctiondelay, 500);
    tinymce.remove(); //added to fix multiply editors on product page
    myFunctiondelay();
	}

	var editOption = function(id) {
		$('#notify_error').remove();

		$.ajax({
			url: opt_urls.update_option,
			data: {
				option_id: current_option_id,
				status: ( $('#status').val() ),
				sort_order: $('#sort_order').val(),
				name: $('#name').val(),
				required: ( $('#required').val() ),
				option_placeholder: ($('#option_placeholder') ? $('#option_placeholder').val() : ''),
				regexp_pattern: ($('#regexp_pattern') ? $('#regexp_pattern').val() : ''),
				error_text: ($('#error_text') ? $('#error_text').val() : ''),
				//required: ($('#required').is(':checked') ? 1 : 0) notw
			},
			type: 'GET',
			success: function (html) {
				$('#option_name').html($('#name').val());
				updateOptions();
				$('#notify').html('<?php echo_html2view($text_success_option);?>').fadeIn(500).delay(2000).fadeOut(500);
			},
			error: function (jqXHR, textStatus, errorThrown) {
				$('#notify').after('<div id="notify_error" class="warning error" align="center">' + errorThrown + '</div>');
			}
		});
		return false;
	}

	//$("#option_values_tblfix a.remove").live('click', function () {
	$("#option_values_tblfix").on('click', 'a.remove', function () {
		if ($(this).closest('tr').find('input[name^=product_option_value_id]').val() == 'new') {
			//remove new completely
			$(this).closest('tr').next().remove();
			$(this).closest('tr').remove();
		} else {
			$(this).closest('tr').toggleClass('toDelete');
		}
		$(this).parent().parent().next().find('div.additionalRow').toggleClass('toDelete').hide();
		//$(this).parent().parent().find('a.expandRow').click();
		return false;
	});

	/*$("#option_values_tblfix a.expandRow").live('click', function () {
		var additional_row = $(this).parent().parent().next().find('div.additionalRow');
		if ($(additional_row).is(':visible')) {
			$(additional_row).hide();
			$(this).text(text.text_expand);
			$(this).parent().parent().next().find('div.add_resource').html();
		} else {
			$(additional_row).show();
			$(this).text(text.text_hide);
			$('div.aform', additional_row).show();
			setRLparams($(this).attr('id'));

			loadMedia('image');
		}

		return false;
	});*/

	$('.open_newtab').on('click', function () {
		var href = $(this).attr('link');
		top.open(href, '_blank');
		return false;
	});


	/*$('.default_uncheck').live('click', function () {
		$("input[name='default']").removeAttr('checked');
	});*/

	$("#add_option_value").on('click', function () {

		var new_row = $('#new_row').parent().find('tr').clone();


		$(new_row).attr('id', 'new' + row_id);

		var so = $('#option_values_tblfix').find("input[name^='sort_order']");
		if(so.length>0){
			var highest = 0;
			so.each(function() {
				highest = Math.max(highest, parseInt(this.value));
			});
			$(new_row).find("input[name^='sort_order']").val(highest+1);
		}

		//$('#option_values_tblfix tr:last-child').after(new_row);
		$('#option_values_tblfix tr:last').after(new_row);
		$("input, checkbox, select", new_row).aform({triggerChanged: true, showButtons: false });
		$('div.aform', new_row).show();
		//Mark rows to be new
		$('#new' + row_id + ' input[name=default]').last()
				.val('new' + row_id)
				.attr('id', 'option_value_form_default_new' + row_id)
				.removeAttr('checked')
				.parent('label')
				.attr('for', 'option_value_form_default_new' + row_id);
		$('#new' + row_id + ' input[name^=product_option_value_id]').val('new');
		$("#new" + row_id + " input, #new" + row_id + " textarea, #new" + row_id + " select").each(function (i) {
			var new_name = $(this).attr('name');
			new_name = new_name.replace("[]", "[new" + row_id + "]");
			$(this).attr('name', new_name);
		});
		row_id++;
		return false;
	});

	// $('#product_form_option').aform({ triggerChanged: false });
	$('#product_form_option').change(function () {
    //alert('optionselect');
		current_option_id = $(this).val();



  //  tinymce.EditorManager.editors = [];  //remove editors

      //tinymce.init();





		$.ajax({
			url: opt_urls.load_option,
			type: 'GET',
			data: { option_id: current_option_id },
			success: function (html) {
				$('#option_values').html(html);
				$("input, checkbox, select", '#option_values_tblfix').aform({triggerChanged: true, showButtons: false});
				$("input, checkbox, select", '.editOption').aform({triggerChanged: true, showButtons: false});
        //  tinymce.remove();

          //console.log( "option selected and ready!" );
          //setTimeout(myFunctiondelay, 500);
          //alert('1st Page load and Onselect load');
          //tinymce.remove();
			},
			error: function (jqXHR, textStatus, errorThrown) {
				$('#option_values').html('<div class="error" align="center"><b>' + textStatus + '</b>  ' + errorThrown + '</div>');
			}
		});



      //tinymce.remove();
      //tinymce.baseURL = "<?php echo $template_dir; ?>javascript/tinymce"; //safetoremove

      //setTimeout(myFunctiondelay, 1000);
      //alert('N Page load');
      //

      //tinymce.init({selector:'textarea'});
      //https://www.tinymce.com/docs/demo/full-featured/
      //setTimeout(myFunctiondelay, 1000);




    //tinyMCE.init({ mode: "none", theme: "advanced" });
  //  tinyMCE.execCommand('mceAddControl', true, "option_value_form_name3");
      //tinymce.init();

      console.log( "newoption selected" );
      //setTimeout(myFunctiondelay, 500);
      tinymce.remove(); //added to fix multiply editors on product page
      //editor.remove();
      myFunctiondelay();
	}); //END OF CHANGE FUNCTION


	//select option and load data for it

	$('#product_form_option option:first-child').attr("selected", "selected").change();


	$('#update_option').on('click', function () {
		editOption('#update_option');
	});

	$('#reset_option').on('click', function () {
		$('#product_form_option').change();

		return false;
	});

	//$(document).on('click', "#option_values_tbl a.remove", function () {
	$('#option_values a').on('click', function () {
		if ($(this).attr('id') == 'update_option' || $(this).attr('id') == 'add_option_value' ||
				$(this).attr('id') == 'reset_option' || $(this).hasClass('remove') || $(this).hasClass('expandRow')) {
			return false;
		}
		if ($(this).attr('id') == 'button_remove_option' && !confirm('<?php echo_html2view($text_delete_confirm); ?>')) {
			return false;
		}
		var that = this;
		if ($(that).attr('it')) {
		var optid = $(that).attr('it'); // hide removed from list

		$.ajax({
			url: $(that).attr('href'),
			type: 'GET',
			success: function (html) {
				if ($(that).attr('id') == 'button_remove_option') {
					$('#product_form_option option:selected').remove();
					$('#product_form_option option[value="' + optid +'"]').remove();
				//alert(optid);
					$('#product_form_option option:eq(' + optid + ')').remove();
					$("#product_form_option").remove("#option1");
				}
				$('#option_values').html(html);
				$("input, checkbox", '#option_values_tblfix').aform({triggerChanged: true, showButtons: false});

			},
			error: function (jqXHR, textStatus, errorThrown) {
				//disable cos ckeditor issue
				//$('#option_values').html('<div class="error" align="center"><b>' + textStatus + '</b>  ' + errorThrown + '</div>');
			}
		});
		}
		return false;
	});

	$('#option_values button[type="submit"]').on('click', function () {

	//alert(fff);
	//CKEDITOR.instances['findid'].getData();
		//var value = CKEDITOR.instances['findid'].getData();
		//Mark rows to be deleted
		$('#option_values_tblfix .toDelete input[name^=product_option_value_id]').val('delete');
		$(this).attr('disabled', 'disabled');

		editOption('#update_option');
		//CKEDITOR.instances.option_value_form_name23.getData();
	//CKEDITOR.instances['findid'].getData();
		//$('#option_values_tblfix tr.toDelete').remove();



		var that = this;
		$.ajax({
		//async: false,
			url: $(that).closest('form').attr('action'),
			type: 'POST',
			data: $(that).closest('form').serializeArray(),
			success: function (html) {
				$('#option_values').html(html);
				$("input, checkbox, select", '#option_values_tblfix').aform({triggerChanged: true, showButtons: false});
				$("input, checkbox, select", '.editOption').aform({triggerChanged: true, showButtons: false});
			},
			error: function (jqXHR, textStatus, errorThrown) {
				$('#option_values').html('<div class="error" align="center"><b>' + textStatus + '</b>  ' + errorThrown + '</div>');
			}
		});
		return false;
	});

	//$.aform.styleGridForm('#product_form_option');

});
//-->


//select selectbox and hide
$("#new_option_form_element_type").val('S');
$('#new_option_form_element_type').addClass("hide");
$('.hide').css('display','none');


$('#option_value_form_name').css('max-width','520px');


</script>
