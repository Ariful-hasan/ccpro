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
div.field span.fdel{
	width: 20px;
	text-decoration:underline;
	cursor:pointer;
	color:#0033CC;
}
</style>

<script src="js/jquery.min.js" type="text/javascript"></script>
<script src="js/jquery.watermark.min.js" type="text/javascript"></script>
<script>
$(document).ready(function() {

	$('#section_title').watermark('Text');
	$('#api').watermark('API');	
	
});

function add_section()
{
	var stitle = $('#section_title').val();
	var sid = $('#section_id').val();
	var api = $('#api').val();
	var isDebug = $('#debug_mode').prop('checked') ? 'Y' : 'N';
	var sec_type = '';
	
	var selected = $("input[type='radio'][name='section_type']:checked");
	if (selected.length > 0) {
		sec_type = selected.val();
	}

	if (stitle.length == 0) {
		alert('Please provide section title');
		return;
	}
	if (sec_type.length == 0) {
		alert('Please select section type');
		return;
	}

	var jsonObj = {id:sid, title:stitle, api:api, stype:sec_type, dmode:isDebug};
	
	$.post( "<?php echo $this->url('task=stemplate&act=save-defined-section&tid=' . $tid);?>", {data:jsonObj})
		.done(function( resp ) {
			//alert( "Data Saved");
			if (resp.length > 0) {
				alert(resp);
			} else {
				//parent.window.location = parent.window.location.href;
				//parent.$.colorbox.close();
				parent.window.location.href = "<?php echo $this->url('task=stemplate&act=details&tid=' . $tid);?>";
			}
	});
}
</script>
<?php
$sec_type = isset($csection->section_type) ? $csection->section_type : '';
?>
<div id="add-sec-elm">
<div class="add-sec-field"><div class="add-sec-title">Section Type:</div> <label for="st-D"><input id="st-D" type="radio" name="section_type" value="D"<?php if ($sec_type=='D') echo ' checked="checked"';?> /> Disposition</label> &nbsp; <label for="st-T"><input id="st-T" type="radio" name="section_type" value="T"<?php if ($sec_type=='T') echo ' checked="checked"';?> /> TPIN</label> &nbsp; <label for="st-I"><input id="st-I" type="radio" name="section_type" value="I"<?php if ($sec_type=='I') echo ' checked="checked"';?> /> IVR Services</label> </div><div class="add-sec-field"><div class="add-sec-title">Section Title:</div><input type="text" name="section_title" id="section_title" value="<?php if (isset($csection->section_title)) echo $csection->section_title;?>"  /> - <input type="text" name="api" id="api" value="<?php if (isset($csection->api)) echo $csection->api;?>"  /><input type="hidden" name="section_id" id="section_id" value="<?php if (isset($csection->section_id)) echo $csection->section_id;?>"  /></div><div class="add-sec-field"><div class="add-sec-title"></div><input type="checkbox" name="debug_mode" id="debug_mode" <?php if (isset($csection->debug_mode) && $csection->debug_mode == 'Y') echo 'checked="checked" ';?>style="vertical-align: text-top;" /><label for="debug_mode"> Enable Debug</label></div>
<div class="add-sec-field" style="text-align:center; padding-top:20px;"><input type="button" class="btn" value="Save Section" onclick="add_section();" /></div>
</div>
