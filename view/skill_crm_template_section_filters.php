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
div.field span.ftype, div.field span.fapi {
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
<script type="text/javascript">
var secTabId = "<?php echo $tabid; ?>";

$(document).ready(function() {
	$('#search_submit_label').watermark('Label');
	$('#api').watermark('API');	
	$('#flabel').watermark('Label');
	$('#fkey').watermark('var');
	
	$( "#fields" ).sortable({
		revert: true
	});
	
	$("#btn-add-field").click(function (e) {
		e.stopPropagation();
		var fkey = $('#fkey').val();
		var flabel = $('#flabel').val();
		var field_api = $('#field_api').val();
		var ftype = '';
		var txt_ftype = '';
		var selected = $("input[type='radio'][name='filter_type']:checked");
		if (selected.length > 0) {
			ftype = selected.val();
		}
		if (ftype == 'T') txt_ftype = 'Text';
		else if (ftype == 'D') txt_ftype = 'Date';
		else if (ftype == 'S') txt_ftype = 'Select';
		
		if (field_api.length > 0) {
			field_api = field_api.replace(/&/g, "&amp;").replace(/>/g, "&gt;").replace(/</g, "&lt;").replace(/"/g, "&quot;");
			txt_ftype = txt_ftype + ' - '+field_api;
		}
		
		if (fkey.length>0 && flabel.length>0) {
			$('#fields').append('<div class="field form" id="'+fkey+'"><span class="flabel">'+flabel+
				'</span> : <span class="fkey">'+fkey+'</span> (<span class="txt_ftype">'+txt_ftype+'</span><span class="ftype">'+ftype+'</span><span class="fapi">'+field_api+'</span>) <span class="fdel" onclick="del_field(\''+fkey+'\');">X</span></div>');
			$('#flabel').val('');
			$('#fkey').val('');
			$('#field_api').val('');
			$("#st-T").prop("checked", true)
		}
	});

});

function del_section()
{
	if (confirm("Are you sure to delete this filter?")) {
		var sid = $('#section_id').val();
		var jsonObj = {id:sid};
	
		$.post("<?php echo $this->url('task=stemplate&act=delete-filter&tid=' . $tid);?>", {data:jsonObj})
		.done(function( resp ) {
			if(secTabId != ""){
				parent.window.location.href = "<?php echo $this->url('task=stemplate&act=section-tabdata&tid='.$tid.'&sid='.$tab_sec_id.'&tabid='.$tabid);?>";
			}else{
				parent.window.location.href = "<?php echo $this->url('task=stemplate&act=details&tid=' . $tid);?>";
			}
		});
	}
}

function add_section()
{
	var stitle = $('#search_submit_label').val();
	var sid = $('#section_id').val();
	//var api = $('#api').val();
	//var api = $('#api').val();
	//var sec_type = '';

	/*
	var selected = $("input[type='radio'][name='section_type']:checked");
	if (selected.length > 0) {
		sec_type = selected.val();
	}
*/
	if (sid.length>0 && stitle.length == 0) {
		alert('Please provide submit label');
		return;
	}
	/*
	if (sec_type.length == 0) {
		alert('Please select section type');
		return;
	}
	*/
	var opts = [];
	$( "#add-sec-elm div.field" ).each(function( index ) {
		opts.push({label:$( this ).find('span.flabel').text(), key:$( this ).find('span.fkey').text(), type:$( this ).find('span.ftype').text(), api:$( this ).find('span.fapi').text()});
	});

	var api = '';
	if (sid.length == 0) api = $('#api').val();

	var jsonObj = {id:sid, slabel:stitle, fields:opts, api:api};
	
	$.post( "<?php echo $this->url('task=stemplate&act=save-filter&tid=' . $tid);?>", {data:jsonObj})
		.done(function( resp ) {
			if(secTabId != ""){
				parent.window.location.href = "<?php echo $this->url('task=stemplate&act=section-tabdata&tid='.$tid.'&sid='.$tab_sec_id.'&tabid='.$tabid);?>";
			}else{
				parent.window.location.href = "<?php echo $this->url('task=stemplate&act=details&tid=' . $tid);?>";
			}
	});
}

function del_field(fkey)
{
	$('#'+fkey).remove();
}
</script>

<div id="add-sec-elm">

<?php if (!empty($csection)):?>
<div class="add-sec-field"><div class="add-sec-title">Section API:</div><?php if (isset($csection->api)) echo $csection->api;?><input type="hidden" name="section_id" id="section_id" value="<?php if (isset($csection->section_id)) echo $csection->section_id;?>"  /></div>
<div class="add-sec-field"><div class="add-sec-title">Submit Label:</div><input type="text" name="search_submit_label" id="search_submit_label" value="<?php if (isset($csection->search_submit_label)) echo $csection->search_submit_label;?>"  /></div>
<?php else:?>
<div class="add-sec-field"><div class="add-sec-title">API:</div><input type="text" name="api" id="api" value="<?php if (isset($search_api)) echo $search_api;?>"  /><input type="hidden" name="section_id" id="section_id" value=""  /></div>
<?php endif;?>
<div class="add-sec-field"><div class="add-sec-title">Section Filter:</div><input type="text" name="flabel" id="flabel" value=""  /> - <input type="text" name="fkey" id="fkey" value=""  />  </div>
<div class="add-sec-field"><div class="add-sec-title">Filter API:</div><input type="text" name="field_api" id="field_api" value="" style="width: 325px;"  /></div>
<div class="add-sec-field"><div class="add-sec-title">&nbsp;</div> <label for="st-T"><input id="st-T" type="radio" name="filter_type" value="T" checked="checked" /> Text</label> &nbsp; <label for="st-D"><input id="st-D" type="radio" name="filter_type" value="D" /> Date</label> &nbsp; <label for="st-S"><input id="st-S" type="radio" name="filter_type" value="S" /> Select</label> </div>

<div class="add-sec-field"><div class="add-sec-title">&nbsp;</div><input type="button" class="btn" id="btn-add-field" value="Add Filter" /></div>

<div class="sec-head">Section Filters</div>

<div id="fields"><?php if (is_array($fields)) { foreach ($fields as $fld) {
	
	$txt_ftype = '';
	if ($fld->field_type == 'T') $txt_ftype = 'Text';
	else if ($fld->field_type == 'D') $txt_ftype = 'Date';
	else if ($fld->field_type == 'S') $txt_ftype = 'Select';
	
	if (!empty($fld->api)) $txt_ftype .= ' - '.htmlspecialchars($fld->api);

echo '<div class="field form" id="'.$fld->field_key.'"><span class="flabel">'.$fld->field_label.
				'</span> : <span class="fkey">'.$fld->field_key.'</span> (<span class="txt_ftype">'.$txt_ftype.'</span><span class="ftype">'.$fld->field_type.'</span><span class="fapi">'.htmlspecialchars($fld->api).'</span>) <span class="fdel" onclick="del_field(\''.$fld->field_key.'\');">X</span></div>';
}
}?></div>
<div class="add-sec-field" style="text-align:center; padding-top:20px;"><?php if (!empty($csection)):?><input type="button" class="btn" value="Delete Filter" onclick="del_section();" /> <?php endif;?><input type="button" class="btn" value="Save Filters" onclick="add_section();" /></div>
</div>
