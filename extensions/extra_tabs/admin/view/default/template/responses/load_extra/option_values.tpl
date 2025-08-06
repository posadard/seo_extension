<script type="text/javascript">
$(document).ready(function () {

var inputs = document.getElementsByTagName("textarea");
for (var i=0; i < inputs.length; i++)
{
var findid = inputs[i].getAttribute('id');
//alert(findid);
if (document.getElementById(findid)){ //&&  findid !="option_value_form_name\\[\\]"
var re = /(\d+)/;
var found = findid.match(re);
	if (found){
	//alert(found[1]);
	var onlyid = found[1];
	//$('#option_value_form_name\\['+onlyid+'\\]').parents('.afield').addClass("myclass");
	//$('.myclass').css('width','750px');
    //$('#option_value_form_name\\['+onlyid+'\\]').parents('.afield').removeClass('mask2');

	//    $('#option_value_form_name\\['+onlyid+'\\]').cleditor();
	//var editor = CKEDITOR.instances[findid];
	//if (editor) { editor.destroy(true); }
	//CKEDITOR.replace(findid);


/*	if (document.getElementById(findid)){
	CKEDITOR.replace(findid,{
        language:'<?php //echo $language_code; ?>'
    });
}
/*
	/*CKEDITOR.replace(findid,
    {//uiColor: '#0B46B5',
	    filebrowserBrowseUrl:false,
        filebrowserImageBrowseUrl:'<?php echo $rl; ?>',
        filebrowserWindowWidth:'920',
        filebrowserWindowHeight:'520',
        language:'<?php echo $language_code; ?>',
		toolbar:'Full',
		colorButton_enableMore:true,
		//http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.config.html
    }); */

	}
	else{//new row

	//$('#option_value_form_name\\[\\]').parents('.afield').addClass("myclass");
	//$('.myclass').css('width','750px');
    //$('#option_value_form_name\\[\\]').parents('.afield').removeClass('mask2');
	//$('#option_value_form_name\\[\\]').cleditor();
	}




   // alert(findid);
	}
}


/*
if (typeof(tinyMCE) != "undefined") {
  if (tinyMCE.activeEditor == null || tinyMCE.activeEditor.isHidden() != false) {
    tinyMCE.editors=[]; // remove any existing references
  }
}
*/


});

//save event
function UpdateTextArea() {
  //  save fix. Load textarea for submit
      var div = $('td.ml_ckeditor div:first-child').attr( 'id' );
      $('a[href="#text_'+div+'"]').click();



			setTimeout(myFunctiondelaysave, 1000)
			//alert('N Page load');
			tinymce.remove();
			//tinymce.init({selector:'textarea'});
			setTimeout(myFunctiondelaysave, 1000);
			function myFunctiondelaysave() {
				//tinymce.init({selector:".visual_editor"});
				tinymce.init({selector:".visual_editor",
				//theme: 'silver',
				// plugins: [
					// 'advlist autolink lists link image charmap print preview hr anchor pagebreak',
					// 'searchreplace wordcount visualblocks visualchars code fullscreen',
					// 'insertdatetime media nonbreaking save table contextmenu directionality',
					// 'emoticons template paste textcolor colorpicker textpattern imagetools codesample'
					// ],
				toolbar1: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
				toolbar2: 'print preview media | emoticons | codesample',
				image_advtab: true,
				});
				/*
				tinymce.init({selector:".visual_editor",
														setup: function (editor) {
														editor.on('change', function () {
														editor.save();
														});
														}
											});
				*/
			}


}


//hide Addmedia button
$('.add_media').css('display', 'none');

//fix editor width
$('.zennable').css('position', 'relative');



</script>


<div id="notify" class="align_center success" style="display: none;"></div>
<?php /*if ($success) { ?>
<script type="text/javascript">
	$('#notify').html('<?php echo_html2view($success);?>').fadeIn(500).delay(2000).fadeOut(500);
</script>

<?php } */?>

<?php

$url = $remove_option;
// Parse the url into an array
$url_parts = parse_url($url);
// Parse the query portion of the url into an assoc. array
parse_str($url_parts['query'], $path_parts);

//echo 'option_id='.$path_parts['option_id']; // 2683322
$optid = $path_parts['option_id'];
//echo $path_parts['xpage']; // 2

?>
<div class="flt_right" style="float: right;">&nbsp;<a it="<?php echo $optid; ?>" id="button_remove_option" href="<?php echo $remove_option; ?>" class="pull-right btn btn-default tooltips dropdown-toggle"><i class="fa fa-trash-o"></i>&nbsp;<?php echo $this->language->get('entry_admin_tab_remove');//$button_remove_option; ?></a></div>
<h2 id="option_name"><?php echo $option_data['language'][$language_id]['name']; ?></h2>
<b><?php echo $this->language->get('entry_admin_tab_settings'); //$text_option_type; ?></b>: <?php //echo $option_type; ?>
<table cellpadding="4" cellspacing="0" class="editOption" >
	<tr>
		<td>
            <div class="option_form_div"><?php echo $entry_status; ?>&nbsp;<div class="input-group afield "><?php echo $status; ?></div></div>
            <div class="option_form_div"><span style="width:110px;"><?php echo $this->language->get('entry_admin_new_tab_name').':';//$entry_option_name; ?></span><?php echo $option_name; ?></div>
			<?php if((string)$option_placeholder){
					//echo '<div class="option_form_div">'.$entry_option_placeholder. $option_placeholder.'</div>';
			}?>
			<div class="option_form_div"><?php echo $entry_sort_order; ?>&nbsp;<?php echo $option_sort_order; ?></div>
		</td>
	</tr>
	<tr>
		<td><div class="option_form_div"><label for="required"><?php echo $this->language->get('entry_admin_require_login').':'; ?></label>&nbsp;<div class="input-group afield "><?php echo $required; ?></div></div>
            <!--<div class="option_form_div"><?php //echo $entry_regexp_pattern; ?><?php //echo $option_regexp_pattern; ?></div>-->
            <div class="option_form_div"><?php echo $this->language->get('entry_error_text_extra'); ?>&nbsp;<?php echo $option_error_text; ?></div>

		<!--<div class="option_form_div flt_right" style="float: right; margin-right: 10px;"><a id="update_option" href="#" class="pull-right btn btn-default tooltips dropdown-toggle"><?php //echo $button_save; ?></a></div>--></td>
	</tr>
</table>

<h3><?php //echo $this->language->get('entry_admin_tab_content'); ?></h3>
<?php echo $update_option_values_form['open']; ?>
<style>
/*#option_values_tblfix  a {

    height: 20px;
    line-height: 20px;
    margin: 2px 6px;
    padding: 0;
    vertical-align: middle;

}*/
#option_values_tblfix {
	margin-bottom: 30px;
}
#option_values_tblfix .toDelete {
    opacity: 0.3;
}
</style>
<table id="option_values_tblfix" class="list option ">
    <tr>
		<?php if($with_default){?>
        <!--<td class="left"><?php //echo $text_default; ?>&nbsp;&nbsp;<!--<span class="default_uncheck">[x]</span></td>-->
		<?php }?>
        <td class="left"><?php echo $this->language->get('entry_admin_tab_html'); //$entry_option_value; ?></td>
        <td class="left"><?php //echo $entry_option_quantity; ?></td>
        <td class="left"><?php //echo $entry_track_option_stock; ?></td>
        <td class="left"><?php //echo $entry_option_price; ?></td>
        <td class="left"><?php //echo $entry_option_prefix; ?></td>
        <td class="left"><?php //echo $entry_sort_order; ?></td>
        <td class="left"></td>
<?php if ($selectable){?>
        <td class="left"><?php echo $column_action; ?></td>
<?php }?>
    </tr>
	  <?php foreach ($option_values as $item) { ?>
        <?php
		echo $item['row']; ?>
    <?php } ?>

</table>
<div style="margin-top: 13px;" align="center" style="width: 80%; float:right;">
	<!--<button type="submit" class="pull-right btn btn-default tooltips dropdown-toggle"><?php //echo $button_save; ?></button>-->
	<button class="btn btn-primary" type="submit" onclick="UpdateTextArea()"><i class="fa fa-save"></i> <?php echo $this->language->get('button_save'); ?></button>
	<!--<a href="#" id="reset_option" class="pull-right btn btn-default tooltips dropdown-toggle"><?php //echo $button_reset; ?></a>-->
	<a href="" class="btn btn-default" id="reset_option"><i class="fa fa-refresh"></i> <?php echo $this->language->get('button_reset'); ?></a>
<?php if (in_array($option_data['element_type'], $elements_with_options)) { ?>
	<!--<a href="#" id="add_option_value" class="flt_right add" style="float: right;"></a>
	<a class="btn btn-success" id="add_option_value" title="Add" href="#"><i class="fa fa-plus-circle fa-lg"></i></a>-->
<?php } ?>
</div>
</form>

<table style="display:none;" id="new_row_table">
	<?php echo $new_option_row ?>

</table>
