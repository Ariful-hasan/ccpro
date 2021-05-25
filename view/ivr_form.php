<?php
$class1 = 'form_row_alt';
$class2 = 'form_row';
//$display_ttl_text = false;
if ($tts_support) {
	$class1 = 'form_row';
	$class2 = 'form_row_alt';
}
?>
<style type="text/css">

</style>
<link href="css/form.css" rel="stylesheet" type="text/css">
<style type="text/css">
input[type="text"],input[type="file"], select {
	width:255px !important;
	display:inline-block;
	margin-bottom: 5px;
}
div.input-tag {
	width: 40px;
	display:inline-block;
	text-align:right;
	padding-right: 5px;
	margin-right: 5px;
}
.form_table td.form_column_caption { 
  width: 187px;
}
form .form_table tr td {
  border-bottom: 1px solid #F7F5F5 !important;
}
</style>
<script type="text/javascript">
function loadEventKeyField(arg) {
	arg = arg.toLowerCase();
	//document.getElementById('spn_txt_sq').style.display = 'none';
	//document.getElementById('spn_sq').style.display = 'none'; 
	//document.getElementById('spn_txt_pf').style.display = 'none';
	//document.getElementById('spn_pf').style.display = 'none'; 
	if(arg != 'hu') {
		//document.getElementById('spn_txt_'+arg).style.display = 'block';
		//document.getElementById('spn_'+arg).style.display = 'block';
	}
}
function show_off_hour()
{
// 	var dh = document.getElementById('dayhour').value;
// 	if (dh.length > 0) {
// 		document.getElementById('tr_off_hour_file').style.display = 'table-row'; 
// 	} else {
// 		document.getElementById('tr_off_hour_file').style.display = 'none'; 
// 	}
}
function playFile(evt)
{
	$.post('cc_web_agi.php', { 'page':'IVR', 'agent_id':luser, 'option':evt },
		function(data, status) {
			if (status == 'success') {
				if(data != 'Y') $.prompt(data);
			} else {
				$.prompt("Failed to communicate!");
			}
	});
}

function isValidDayHour(str) {
	if(str.length==0) return false;
	entries = str.split(",");
	var len = entries.length;
	for(var i=0; i<len; i++) {
		if(!checkValidEntry(entries[i])) {
			return false;
		}
	}
	
	return true;
}

function checkValidEntry(entry) {
	var ent = entry.split("-");
	if(ent.length > 2) return false;

	for(var i=0; i<ent.length; i++) {
		var trimmed = ent[i].replace(/^\s+|\s+$/g, '');
		if(trimmed.length==0) return false;
		if(!isDay(trimmed) && !isIntegerInRange(trimmed,0,23)) {
			return false;
		}
	}
	
	return true;
}

function isDay(day) {
	var valid_days = "SAT,SUN,MON,TUE,WED,THU,FRI".split(',');

	var u_day = day.toUpperCase();	

	
	for(var i=0; i<7; i++) {
		if(valid_days[i] == u_day) return true;
	}
	return false;
}

function isHour(hour) {
	return isIntegerInRange (hour, 0, 23);
}

function isIntegerInRange (s, a, b)
{
	if (isEmpty(s))
		if (isIntegerInRange.arguments.length == 1) return false;
		else return (isIntegerInRange.arguments[1] == true);

	// Catch non-integer strings to avoid creating a NaN below,
	// which isn't available on JavaScript 1.0 for Windows.
	if (!isInteger(s, false)) return false;

	// Now, explicitly change the type to integer via parseInt
	// so that the comparison code below will work both on
	// JavaScript 1.2 (which typechecks in equality comparisons)
	// and JavaScript 1.1 and before (which doesn't).
	var num = parseInt (s);
	return ((num >= a) && (num <= b));
}

function isInteger(s) {
	var i;
	if (isEmpty(s))
		if (isInteger.arguments.length == 1) return 0;
		else return (isInteger.arguments[1] == true);
	
	for (i = 0; i < s.length; i++) {
		var c = s.charAt(i);
		if (!isDigit(c)) return false;
	}
	
	return true;
}

function isEmpty(s) {
	return ((s == null) || (s.length == 0))
}

function isDigit (c) {
	return ((c >= "0") && (c <= "9"))
}

function checkExt(fileId) {
	var filename = document.getElementById(fileId).value;
	var filelength = parseInt(filename.length) - 3;
	var fileext = filename.substring(filelength,filelength + 3);

	if (fileext != 'wav'){
		alert ('You can only upload wav files.','fileId');
		//document.getElementById(fileId).focus();
		return false;
	}

	return true;
}

function displayWelcomeSpan(val) {
	if(val == 'Y') {
		//document.getElementById('spn_welcome_voice').style.display = 'inline';
	} else {
		//document.getElementById('spn_welcome_voice').style.display = 'none';
	}
}


</script>

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>" method="post" enctype="multipart/form-data">
<?php /* for temporay*/?>
<!-- <input type="hidden" name="debug_mode" value="N" /> -->
<input type="hidden" name="TTS" value="N" />

<?php /* end temporary*/?>

<input type="hidden" name="ivrid" value="<?php if (isset($ivrid)) echo $ivrid;?>" />
<table class="form_table" border="0" width="500" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>IVR Information</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="40%"><b>Exten : </b></td>
		<td>
			<div class="input-tag"></div><b>#<?php echo ord($ivrid);?></b>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" width="40%"><b>Name : </b></td>
		<td>
			<div class="input-tag"></div><input type="text" name="ivr_name" value="<?php echo $ivr->ivr_name;?>">
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption"><b>DTMF timeout : </b></td>
		<td>
			<div class="input-tag"></div><input type="text" name="dtmf_timeout" value="<?php echo $ivr->dtmf_timeout;?>" maxlength="2" />
		</td>
	</tr>

	<tr class="form_row_alt">
		<td class="form_column_caption"><b>IVR timeout : </b></td>
		<td>
			<div class="input-tag"></div><input type="text" name="ivr_timeout" value="<?php echo $ivr->ivr_timeout;?>" maxlength="3" />
		</td>
	</tr> 

<?php $tts_support=false; if ($tts_support):?>
	<tr class="form_row">
    	<td class="form_column_caption" valign="top">Enable TTS :</td>
		<td>
			<div class="input-tag"></div><select id="TTS" name="TTS">
				<option value="N"<?php if ($ivr->TTS == 'N') echo ' selected="selected"';?>>Disable</option>
				<option value="Y"<?php if ($ivr->TTS == 'Y') echo ' selected="selected"';?>>Enable</option>
			</select>
             &nbsp; <font style="font-size:9px;">Applicable only for English language</font>
		</td>
	</tr> 
<?php endif;?>
    <tr class="<?php echo $class1;?>">
    	<td class="form_column_caption" valign="top">Welcome voice :</td>
		<td>
			<div class="input-tag"></div><select id="welcome_voice" name="welcome_voice" onchange="displayWelcomeSpan(this.value);">
				<option value="N"<?php if ($ivr->welcome_voice == 'N') echo ' selected="selected"';?>>Disable</option>
				<option value="Y"<?php if ($ivr->welcome_voice == 'Y') echo ' selected="selected"';?>>Enable</option>
			</select>
            
			
		</td>
    </tr>
    <tr class="form_row_alt">
		<td class="form_column_caption"><b>Debug CLI Filter : </b></td>
		<td>
			<div class="input-tag"></div><input type="text" name="debug_cli_filter" value="<?php echo $ivr->debug_cli_filter;?>" maxlength="13" />
		</td>
	</tr>
    <?php /*?>
    <tr class="<?php echo $class2;?>">
    	<td class="form_column_caption" valign="top">Debug mode :</td>
		<td>
			<div class="input-tag"></div><select id="debug_mode" name="debug_mode">
				<option value="N"<?php if ($ivr->debug_mode == 'N') echo ' selected="selected"';?>>Disable</option>
				<option value="Y"<?php if ($ivr->debug_mode == 'Y') echo ' selected="selected"';?>>Enable</option>
			</select>
		</td>
    </tr> 
    <?php // */?>    
	<tr class="<?php echo $class1;?>">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" name="editivr" value="Update">
		</td>
	</tr>
</tbody>
</table>
</form>

<script type="text/javascript">
var luser = '<?php echo UserAuth::getCurrentUser();?>';
loadEventKeyField('<?php echo $ivr->timeout_action;?>');
show_off_hour();
</script>
