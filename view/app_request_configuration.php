<link rel="stylesheet" href="css/form.css" type="text/css">
<style>
* {
	font-family: Verdana,Arial,Helvetica,sans-serif;
}
.section
{
	width:100%;
	clear:both;
}
.sec-head {
	/*border-bottom: 3px solid #9AC9DE;*/
	border-bottom: 1px solid #D3D6DB;
    color: #2583AD;
    font-size: 17px;
    font-weight: bold;
    
}
.sec-type {
	display:none;
}
.sec-fields
{
	width:100%;

}
.sec-field
{
	width:50%;
	float:left;
	padding: 1px 0px;
}
.sec-fld-label {
	display:inline-block;
	width:40%;
	font-size: 15px;
}
.sec-fld-val {
	display:inline;
	font-size: 15px;
	font-weight:bold;
}
.sec-clear {
	clear:both;
	padding-bottom: 20px;
}
.sec-api {
	display: inline;
	font-size:14px;
	font-style:italic;
	font-weight:bold;
	padding-bottom: 3px;
}
div.add-sec-title {
	display:inline-block;
	width: 120px;
	font-weight:bold;
	font-size:13px;
}
div.add-sec-field{
height: 40px;
text-align:left;
}
div.field {
padding:5px;
}
div.field span{
font-size: 13px;
}
div.field span.flabel{
font-weight:bold;
display:inline-block;
width:110px;
}
div.field span.fkey{
display:inline-block;
width: 110px;
}
div.field span.fmask {
display:none;
}
div.field span.fdel{
	width: 20px;
	text-decoration:underline;
	cursor:pointer;
	color:#0033CC;
}
</style>

<script src="js/jquery.min.js" type="text/javascript"></script>
<script src="js/jquery-ui.min.js" type="text/javascript"></script>
<script src="js/jquery.watermark.min.js" type="text/javascript"></script>
<script>
$(document).ready(function() {

	$('#title').watermark('Title');
	$('#api').watermark('API');	
	$('#flabel').watermark('Label');
	$('#request_id').watermark('ID');
	$('#fkey').watermark('var');
	
	
	$( "#fields" ).sortable({
		revert: true
	});
	
	$("#btn-add-field").click(function (e) {
		e.stopPropagation();
		var fkey = $('#fkey').val();
		var flabel = $('#flabel').val();
		var field_mask = $('#field_mask').val();

		var txt_fmask = '';
		if (field_mask.length > 0) txt_fmask = '('+field_mask+')';
		
		if (fkey.length>0 && flabel.length>0) {
			$('#fields').append('<div class="field form" id="'+fkey+'"><span class="flabel">'+flabel+
				'</span> : <span class="fkey">'+fkey+'</span> <span class="txt_fmask">'+txt_fmask+'</span><span class="fmask">'+field_mask+'</span> <span class="fdel" onclick="del_field(\''+fkey+'\');">X</span></div>');
			
			$('#flabel').val('');
			$('#fkey').val('');
			$('#fmask').attr('checked', false);
			$('#field_mask').val('');
			$('#field_mask').hide();
		}
	});

});

function add_section()
{
	var stitle = $('#title').val();
	var rid = $('#request_id').val();
	var api = $('#api').val();
	var isDebug = $('#debug_mode').prop('checked') ? 'Y' : 'N';
	var sec_type = '';
	
	var selected = $("input[type='radio'][name='section_type']:checked");
	if (selected.length > 0) {
		sec_type = selected.val();
	}

	if (rid.length == 0) {
		alert('Please provide Request ID');
		return;
	}
	if (sec_type.length == 0) {
		alert('Please select response type');
		return;
	}

	var opts = [];
	$( "#add-sec-elm div.field" ).each(function( index ) {
		opts.push({label:$( this ).find('span.flabel').text(), key:$( this ).find('span.fkey').text(), mask:$( this ).find('span.fmask').text()});
	});
	var jsonObj = {id:rid, title:stitle, api:api, rtype:sec_type, dmode:isDebug, fields:opts};
	//console.log(jsonObj);
	$.post( "<?php echo $this->url('task=app&act=save-request');?>", {data:jsonObj})
		.done(function( resp ) {
		parent.window.location.href = "<?php echo $this->url('task=app');?>";
	});
}

function adjustMask(cbox)
{
	if (cbox.checked) {
		$('#field_mask').show();
	} else {
		$('#field_mask').val('');
		$('#field_mask').hide();
	}
}

function del_field(fkey)
{
	$('#'+fkey).remove();
}
</script>
<?php
$sec_type = isset($csection->section_type) ? $csection->section_type : '';
?>
<div id="add-sec-elm">
<div class="add-sec-field"><div class="add-sec-title">Response Type:</div> <label for="st-F"><input id="st-F" type="radio" name="section_type" value="F"<?php if ($sec_type=='F') echo ' checked="checked"';?> /> Information</label> &nbsp; <label for="st-G"><input id="st-G" type="radio" name="section_type" value="G"<?php if ($sec_type=='G') echo ' checked="checked"';?> /> Table</label> </div>

<div class="add-sec-field"><div class="add-sec-title">Request ID:</div><input type="text" name="request_id" id="request_id" value="<?php if (isset($csection->request_id)) echo $csection->request_id;?>" maxlength="20"  /> - <input type="text" name="title" id="title" value="<?php if (isset($csection->title)) echo $csection->title;?>" maxlength="30"  /></div>
<div class="add-sec-field"><div class="add-sec-title">Request API:</div><input type="text" name="api" id="api" value="<?php if (isset($csection->api)) echo $csection->api;?>"  /></div>
<div class="add-sec-field"><div class="add-sec-title">Response Field:</div><input type="text" name="flabel" id="flabel" value=""  /> - <input type="text" name="fkey" id="fkey" value=""  />  &nbsp;<input type="button" class="btn" id="btn-add-field" value="Add Field" /></div>
<div class="add-sec-field"><div class="add-sec-title"></div><input type="checkbox" name="fmask" id="fmask" style="vertical-align: text-top;" onclick="adjustMask(this);" /><label for="fmask"> Enable Masking</label> &nbsp; <input type="text" name="field_mask" id="field_mask" value="" style="display: none;"  /></div>
<div class="add-sec-field"><div class="add-sec-title"></div><input type="checkbox" name="debug_mode" id="debug_mode" <?php if (isset($csection->debug_mode) && $csection->debug_mode == 'Y') echo 'checked="checked" ';?>style="vertical-align: text-top;" /><label for="debug_mode"> Enable Debug</label></div>
<div class="sec-head">Response Fields to App</div>
<div id="fields"><?php if (is_array($fields)) { foreach ($fields as $fld)  {
	
	if (!empty($fld->field_mask)) $txt_ftype = '('.$fld->field_mask.')';
	else $txt_ftype = '';
	
echo '<div class="field form" id="'.$fld->field_key.'"><span class="flabel">'.$fld->field_label.
				'</span> : <span class="fkey">'.$fld->field_key.'</span> <span class="txt_fmask">'.$txt_ftype.'</span><span class="fmask">'.$fld->field_mask.'</span> <span class="fdel" onclick="del_field(\''.$fld->field_key.'\');">X</span></div>';
} } ?></div>
<div class="add-sec-field" style="text-align:center; padding-top:20px;"><input type="button" class="btn" value="Save Request" onclick="add_section();" /></div>
</div>
