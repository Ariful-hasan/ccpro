<link rel="stylesheet" href="css/form.css" type="text/css">
<style type="text/css">
* {
	font-family: Verdana,Arial,Helvetica,sans-serif;
}
.section {
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
.sec-fields {
	width:100%;
}
.sec-field {
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
	border-bottom: 1px solid #efefef;
	padding:5px;
	text-align: left;
}
div.field span{
    font-size: 13px;
	min-width: 25px;
	max-width: 100px;
}
div.field span.flabel{
    font-weight:bold;
    display:inline-block;
    width:220px;
    padding-right: 5px;
    text-align: right;
}
div.field span.fkey{
    display:inline-block;
    width: 200px;
    padding-left: 5px;
    text-align: left;
}
div.field span.fmask {
    display:none;
}
div.field span.fsession {
	display:none;
}
div.field span.fapi {
    display:none;
}
div.field span.fdel{
	width: 20px;
	text-decoration:underline;
	cursor:pointer;
	color:#0033CC;
}
.secDataTable td{
	min-width: 25px;
}
.popup-body input[type="text"]{
    width: 156px;
}
#btn-add-field {
    font-family: Verdana;
    font-size: 12px;
    font-weight: bold;
    line-height: 14px;
}
</style>

<script src="js/jquery.min.js" type="text/javascript"></script>
<script src="js/jquery-ui.min.js" type="text/javascript"></script>
<script src="js/jquery.watermark.min.js" type="text/javascript"></script>
<script type="text/javascript">
var secTabId = "<?php echo $tabid; ?>";
var totalSession = 0;

$(document).ready(function() {
	$('#section_title').watermark('Text');
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
		var field_mask = $('#field_mask').val();
		var field_session = $('#save_in_session').val();
		var isTabUrl = $('#field_tab').prop('checked') ? 'Y' : 'N';
		var field_api = $('#field_api').val();

		var txt_fmask = '';
		if (field_mask.length > 0) txt_fmask = '('+field_mask+')';
		if (field_session.length > 0) {
			totalSession++;
			if(totalSession >= 3){
				$('.sess-field').hide();
			}
			if (txt_fmask.length > 0) txt_fmask += '  CARD:'+field_session;
			else txt_fmask += 'CARD:'+field_session;
		}
		if (field_api.length > 0) {
			field_api = field_api.replace(/&/g, "&amp;").replace(/>/g, "&gt;").replace(/</g, "&lt;").replace(/"/g, "&quot;");
			txt_fmask = txt_fmask + ' ['+field_api+']';
		}

		var txt_tab_url = '';
		if(isTabUrl == 'Y') txt_tab_url = '  TAB';
		
		if (fkey.length>0 && flabel.length>0) {
			var appendableTxt = '<div class="field form" id="'+fkey+'">';
			appendableTxt += '<table class="secDataTable"><tbody><tr>';
			appendableTxt += '<td><span class="flabel">'+flabel+'</span> :</td>';

			appendableTxt += '<td> <span class="fkey">'+fkey+'</span></td>';

			appendableTxt += '<td><span class="txt_fmask">'+txt_fmask+'</span><span class="fmask">'+field_mask+'</span><span class="fsession">'+field_session+'</span><span class="fapi">'+field_api+'</span></td>';

			appendableTxt += '<td style="width:32px;"><span class="fTabUrl">'+txt_tab_url+'</span></td>';

			appendableTxt += '<td><span class="fdel" onclick="del_field(\''+fkey+'\');">X</span></td>';

			appendableTxt += '</tr></tbody></table></div>';
			
			$('#fields').append(appendableTxt);
			
			$('#flabel').val('');
			$('#fkey').val('');
			$('#field_api').val('');
			$('#fmask').attr('checked', false);
			$('#field_tab').attr('checked', false);
			$('#field_mask').val('');
			$('#field_mask').hide();
			$('#fsession').attr('checked', false);
			$('#save_in_session').val('');
			$('#save_in_session').hide();
		}
	});

	$("#save_in_session").on('input',function(e){
		this.value = this.value.replace(/[^a-zA-Z0-9_]/g,'');
	});
});

function add_section()
{
	var stitle = $('#section_title').val();
	var sid = $('#section_id').val();
	//var api = $('#api').val();
	var api = $('#api').val();
	var isDebug = $('#debug_mode').prop('checked') ? 'Y' : 'N';
    var secNode = $('#section_node').val();
	var sec_type = '';
	var fTabTxt = '';

	if($(this).find('span.fTabUrl').length > 0){
		fTabTxt = $( this ).find('span.fTabUrl').text();
	}
	
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

	var opts = [];
	$( "#add-sec-elm div.field" ).each(function( index ) {
		opts.push({label:$(this).find('span.flabel').text(), key:$(this).find('span.fkey').text(), mask:$(this).find('span.fmask').text(), csession:$(this).find('span.fsession').text(), api:$( this ).find('span.fapi').text(), ftab:$(this).find('span.fTabUrl').text()});
	});
	var jsonObj = {id:sid, title:stitle, api:api, stype:sec_type, dmode:isDebug, fields:opts, snode:secNode};
	
	$.post( "<?php echo $this->url('task=stemplate&act=save-section&tid=' . $tid.'&tabid='.$tabid);?>", {data:jsonObj})
		.done(function( resp ) {
			//alert( "Data Saved");
			//parent.window.location = parent.window.location.href;
			if(secTabId != ""){
				parent.window.location.href = "<?php echo $this->url('task=stemplate&act=section-tabdata&tid=' . $tid.'&sid='.$tab_sec_id.'&tabid='.$tabid);?>";
			}else{
			    parent.window.location.href = "<?php echo $this->url('task=stemplate&act=details&tid=' . $tid);?>";
			}
			//parent.$.colorbox.close();
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
function adjustSession(cbox)
{
	if (cbox.checked) {
		$('#save_in_session').show();
	} else {
		$('#save_in_session').val('');
		$('#save_in_session').hide();
	}
}

function del_field(fkey)
{
	var cardSession = $('#'+fkey+' .fsession').text();
	if(typeof cardSession != 'undefined' && cardSession != '' && cardSession != null){
		cardSession = $.trim(cardSession);
		if (cardSession.length > 0){
			totalSession--;
			$('.sess-field').show();
		}
	}
	$('#'+fkey).remove();
}
</script>
<?php
$sec_type = isset($csection->section_type) ? $csection->section_type : '';
?>
<div id="add-sec-elm">
<div class="add-sec-field"><div class="add-sec-title">Section Type:</div> <label for="st-F"><input id="st-F" type="radio" name="section_type" value="F"<?php if ($sec_type=='F') echo ' checked="checked"';?> /> Information</label> &nbsp; <label for="st-G"><input id="st-G" type="radio" name="section_type" value="G"<?php if ($sec_type=='G') echo ' checked="checked"';?> /> Table</label> </div>

<div class="add-sec-field"><div class="add-sec-title">Section Title:</div><input type="text" name="section_title" id="section_title" value="<?php if (isset($csection->section_title)) echo $csection->section_title;?>"  /> - <input type="text" name="api" id="api" value="<?php if (isset($csection->api)) echo str_replace('"', "'", $csection->api);?>"  /><input type="hidden" name="section_id" id="section_id" value="<?php if (isset($csection->section_id)) echo $csection->section_id;?>"  /></div>
<div class="add-sec-field"><div class="add-sec-title">Section Node:</div><input type="text" name="section_node" id="section_node" value="<?php if (isset($csection->section_node)) echo $csection->section_node;?>" style="width: 325px;" /></div>
<div class="add-sec-field"><div class="add-sec-title">Section Field:</div><input type="text" name="flabel" id="flabel" value=""  /> - <input type="text" name="fkey" id="fkey" value=""  />  &nbsp;<input type="button" class="btn" id="btn-add-field" value="Add Field" /></div>
<div class="add-sec-field"><div class="add-sec-title">Field API:</div><input type="text" name="field_api" id="field_api" value="" style="width: 325px;"  /></div>
<?php if (empty($tabid)){ ?>
<div class="add-sec-field">
    <div class="add-sec-title"></div><input type="checkbox" name="field_tab" id="field_tab" style="vertical-align: text-top;" /><label for="field_tab"> Field Use As Tab URL</label>
</div>
<?php } ?>
<div class="add-sec-field"><div class="add-sec-title"></div><input type="checkbox" name="fmask" id="fmask" style="vertical-align: text-top;" onclick="adjustMask(this);" /><label for="fmask"> Enable Masking</label> &nbsp; <input type="text" name="field_mask" id="field_mask" value="" style="display: none;"  /></div>
<div class="add-sec-field sess-field"><div class="add-sec-title"></div><input type="checkbox" name="fsession" id="fsession" style="vertical-align: text-top;" onclick="adjustSession(this);" /><label for="fsession"> Enable Card Session</label> &nbsp; <input type="text" name="save_in_session" id="save_in_session" maxlength="12" value="" style="display: none;" /></div>
<div class="add-sec-field"><div class="add-sec-title"></div><input type="checkbox" name="debug_mode" id="debug_mode" <?php if (isset($csection->debug_mode) && $csection->debug_mode == 'Y') echo 'checked="checked" ';?>style="vertical-align: text-top;" /><label for="debug_mode"> Enable Debug</label></div>
<div class="sec-head">Section Fields</div>
<div id="fields">
<?php
$totalSession = 0;
if (is_array($fields)) {
    foreach ($fields as $fld)  {

	if (!empty($fld->field_mask)) $txt_ftype = '('.$fld->field_mask.')';
	else $txt_ftype = '';

	if (!empty($fld->save_in_session)){
		$totalSession++;
		if (!empty($txt_ftype)) {
			$txt_ftype .= '  ';
		}
		$txt_ftype .= 'CARD:'.$fld->save_in_session;
	}

	if (!empty($fld->api)){
		if (!empty($txt_ftype)) {
			$txt_ftype .= '  ';
		}
		if (!empty($fld->api)) $txt_ftype .= '['.htmlspecialchars($fld->api).']';
	}

	$txt_field_tab = '';
	if (!empty($fld->field_tab_id)) $txt_field_tab = '  TAB';

	echo '<div class="field form" id="'.$fld->field_key.'">';
	echo '<table class="secDataTable"><tbody><tr>';
	echo '<td>';
	echo '<span class="flabel">'.$fld->field_label.'</span> :';
	echo '</td>';

	echo '<td> ';
	echo '<span class="fkey">'.$fld->field_key.'</span>';
	echo '</td>';
	
	echo '<td>';
	echo '<span class="txt_fmask">'.$txt_ftype.'</span><span class="fmask">'.$fld->field_mask.'</span><span class="fsession">'.$fld->save_in_session.'</span><span class="fapi">'.htmlspecialchars($fld->api).'</span>';
	echo '</td>';
	
	echo '<td style="width:32px;">';
	echo '<span class="fTabUrl">'.$txt_field_tab.'</span>';
	echo '</td>';
	
	echo '<td>';
	echo '<span class="fdel" onclick="del_field(\''.$fld->field_key.'\');">X</span>';
	echo '</td>';
	
	echo '</tr></tbody></table>';
	echo '</div>';
	
    /* echo '<div class="field form" id="'.$fld->field_key.'"><span class="flabel">'.$fld->field_label.
	 '</span> : <span class="fkey">'.$fld->field_key.'</span> <span class="txt_fmask">'.$txt_ftype.
     '</span><span class="fmask">'.$fld->field_mask.'</span><span class="ftab">'.$txt_field_tab.'</span> <span class="fdel" onclick="del_field(\''.$fld->field_key.'\');">X</span></div>';
     */
    } 
} ?>
</div>
<div class="add-sec-field" style="text-align:center; padding-top:20px;"><input type="button" class="btn" value="Save Section" onclick="add_section();" /></div>
</div>
<script type="text/javascript">
	totalSession = <?php echo $totalSession; ?>;
	if(totalSession >= 3){
		$('.sess-field').hide();
	}
</script>