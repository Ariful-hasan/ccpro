<link href="css/form.css" rel="stylesheet" type="text/css">

<script type="text/javascript">
<?php
if (!isset($arg)){
    $arg = "";
}
if (!isset($arg2)){
    $arg2 = "";
}
//GPrint($dtmf_options);die;
?>
<?php if ($is_edit):?>
	/*
$(document).ready(function(){
	$('.record_voice').click(function(){
		var elm_id = $(this).attr('id');
		if(confirm('Are you sure to record file?')) {
			$.post('cc_web_agi.php', { 'page':'ivr', 'agent_id':luser, 'option':elm_id },
				function(data, status){
					if(status == 'success') {
						if(data!='Y')
							alert(data);
					} else {
						alert('Failed to comminicate!!');
					}
				});
		}
	});
});
	*/

<?php endif;?>

function loadEventKeyField(selection, index)
{
	selection = selection.toLowerCase();
	document.getElementById('spn_dh'+index).style.display = 'none';
	document.getElementById('spn_sq'+index).style.display = 'none';
	document.getElementById('spn_vm'+index).style.display = 'none';	
        document.getElementById('spn_xf'+index).style.display = 'none';
	document.getElementById('spn_sr'+index).style.display = 'none';
	document.getElementById('spn_sl'+index).style.display = 'none';
	document.getElementById('spn_fp'+index).style.display = 'none';
	document.getElementById('spn_mc'+index).style.display = 'none';
	document.getElementById('spn_rv'+index).style.display = 'none';
	document.getElementById('spn_go'+index).style.display = 'none';
	document.getElementById('spn_dd'+index).style.display = 'none';
	document.getElementById('spn_ed'+index).style.display = 'none';
	document.getElementById('spn_ln'+index).style.display = 'none';
	document.getElementById('spn_vt'+index).style.display = 'none';
	document.getElementById('spn_txt_dh'+index).style.display = 'none';
	
	document.getElementById('spn_txt_sq'+index).style.display = 'none';
	document.getElementById('spn_txt_vm'+index).style.display = 'none';
        document.getElementById('spn_txt_xf'+index).style.display = 'none';
        
	document.getElementById('spn_txt_sr'+index).style.display = 'none';
	document.getElementById('spn_txt_sl'+index).style.display = 'none';
	document.getElementById('spn_txt_fp'+index).style.display = 'none';
	document.getElementById('spn_txt_mc'+index).style.display = 'none';
	document.getElementById('spn_txt_rv'+index).style.display = 'none';
	document.getElementById('spn_txt_go'+index).style.display = 'none';
	document.getElementById('spn_txt_dd'+index).style.display = 'none';
	document.getElementById('spn_txt_vt'+index).style.display = 'none';
	document.getElementById('spn_txt_ed'+index).style.display = 'none';
	document.getElementById('spn_txt_ln'+index).style.display = 'none';

	document.getElementById('spn_txt_authtxt').style.display = 'none';
	document.getElementById('spn_txt_dayhourtxt').style.display = 'none';
	document.getElementById('spn_txt_otherauthtxt').style.display = 'none';
	document.getElementById('spn_txt_dayhourtxt_example').style.display = 'none';
	
	if(selection == 'ca') document.getElementById('spn_txt_authtxt').style.display = 'inline';
	else if(selection == 'dh') {document.getElementById('spn_txt_dayhourtxt').style.display = 'inline';document.getElementById('spn_txt_dayhourtxt_example').style.display = 'inline';}
	else document.getElementById('spn_txt_otherauthtxt').style.display = 'inline';
	try{
	if (selection != 'ca' && selection != 'sv' && selection != 'sy' && selection != 'if' && selection != 'au') {
		document.getElementById('spn_'+selection+index).style.display = 'inline';
		document.getElementById('spn_txt_'+selection+index).style.display = 'inline';
	}
	}catch(e){}
}

function checkForm() {
	var event_file = document.getElementById('event_pf_en').value;
	if(event_file.length > 0) {
		if(!checkExt('event_pf_en')) return false;
	}
	var event_file = document.getElementById('event_pf_bn').value;
	if(event_file.length > 0) {
		if(!checkExt('event_pf_bn')) return false;
	}

	if(document.getElementById('event').value == 'CA') {
		if(!isValidUrl(document.getElementById('text').value)) {
			alert('Please provide the validation url');
			return false;
		}
	} else if(document.getElementById('event').value == 'DH') {
		if(!isValidDayHour(document.getElementById('text').value)) {
			alert('Please provide valid day hour');
			return false;
		}
	}
	
	return true;
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

function isValidUrl(url) {
	//return /^(https?):/.test(url);
	return true;
}

function playFile(evt)
{
	$.post('cc_web_agi.php', { 'page':'IVR', 'agent_id':luser, 'option':evt },
		function(data, status) {
			if (status == 'success') {
				if(data != 'Y') alert(data);
			} else {
				alert("Failed to communicate!");
			}
	});
}

function display_node_input(selected_val)
{
	if (selected_val == 'T_NODE') {
		document.getElementById('spn_the_node').style.display = 'inline';
	} else {
		document.getElementById('spn_the_node').style.display = 'none';
	}
}
</script>

<?php if (isset($errMsg) && !empty($errMsg)):?><div class="form_error_message"><?php echo $errMsg;?></div><?php endif;?>

<form name="ivr_form" enctype="multipart/form-data" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>" onsubmit="return checkForm();">
<input type="hidden" name="ivrname" value="<?php echo $ivrname;?>" />
			<table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">
			<tbody>
				<tr class="form_row_alt"><td class="form-header" colspan=2>Item Information<?php if ($save_type == 'update') echo ' [' . $menu . ']';?></td></tr>
				<?php if ($is_root):?>
				<?php else:?>
					<?php /*if ($pevent == 'AN' || $pevent == 'ID'):
    	            <input type="hidden" name="dtmf" value=".">  */ ?>
        	        <?php if ($pevent == 'NDH'):?>
            	    <input type="hidden" name="dtmf" value="<?php echo $dtmf;?>">
                	<?php else:?>
					<tr class="form_row">
						<td class="form_column_caption">DTMF: </td>
						<td>
							<?php
                            
							//$this->html_options('dtmf', 'dtmf', $dtmf_options, $dtmf);
							
							echo '<select name="dtmf" id="dtmf">';
							if (is_array($dtmf_options)) {
			//var_dump($options);
			foreach ($dtmf_options as $key=>$val) {
				if (gettype($dtmf) == 'string') $key = (string) $key;
				echo '<option value="'.$key.'"';
				if ($key == $dtmf) {echo ' selected';}
				echo '>'.$val.'</option>';
				//echo 'key-value=' . $key . ',' . $selected . ',';
			}
		}
		echo '</select>';
		
							?>
						</td>
					</tr>
    	            <?php endif;?>
				<?php endif;?>

				<tr class="form_row">
					<td width="35%" class="form_column_caption" valign="top">
                    	<span id="spn_txt_authtxt">
                    	URL: 
                        </span>
                        <span id="spn_txt_dayhourtxt">
                       	Day Hour:
                        </span>
                        <span id="spn_txt_otherauthtxt">
                        Text: 
                        </span>
                    </td>
					<td>
						<input type="text" name="text" id="text" value="<?php echo htmlentities($text);?>" size="40" maxlength="255" />
                        <span id="spn_txt_dayhourtxt_example"><br /><font style="font-size:9px;">example: MON-WED[09:00-18:00],THU[09:00-17:00]</font></span>
						<input type="hidden" name="menu" value="<?php echo $menu;?>" />
                        <input type="hidden" name="pevent" value="<?php echo isset($pevent) ? $pevent : '';?>" />
                        <input type="hidden" name="old_text" id="old_text" value="<?php echo htmlentities($text);?>" />
					</td>
				</tr>
				<tr class="form_row">
					<td class="form_column_caption">Arg:</td>
					<td>
                    	<input type="text" name="arg" id="text" value="<?php echo htmlentities($arg);?>" size="40" maxlength="255" />
                        <input type="hidden" name="old_arg" value="<?php echo $event;?>" />
					</td>
				</tr>
				<tr class="form_row">
					<td class="form_column_caption">Arg2:</td>
					<td>
						<input type="text" name="arg2" id="text" value="<?php echo htmlentities($arg2);?>" size="40" maxlength="255" />
					</td>
				</tr>
				<tr class="form_row">
					<td class="form_column_caption">Event: </td>
					<td>
                    	<?php 
						//if ($event == 'GV' || $event == 'DH' || $event == 'SR' || $event == 'MC')
						//if ($event == 'DH'):
						if (0):
						?>
							<?php $this->html_options('event_disable', 'event_disable', $event_options, $event, "loadEventKeyField(this.value, '');", true);?>
                            <input type="hidden" id='event' name="event" value="<?php echo $event;?>" />
						<?php else:?>
							<?php $this->html_options('event', 'event', $event_options, $event, "loadEventKeyField(this.value, '');");?>
						<?php endif;?>
                        <input type="hidden" name="old_event" value="<?php echo $event;?>" />
					</td>
				</tr>
				
				<tr class="form_row">
					<td class="form_column_caption">
						<b>						
						<span id="spn_txt_dh">
							&nbsp;
						</span>
						<span id="spn_txt_sq">
							Select skill :
						</span>
						<span id="spn_txt_vm">
						        Select skill :
						</span>
						<span id="spn_txt_sr">
							Select service :
						</span>
						<span id="spn_txt_sl">
							Select service :
						</span>
						<span id="spn_txt_fp">
							Select service :
						</span>
						<span id="spn_txt_mc">
							Select module :
						</span>
						<span id="spn_txt_rv">
							Key :
						</span>
						<span id="spn_txt_go">
							Select go options :
						</span>
						<span id="spn_txt_dd">
							Agent ID :
						</span>
						<span id="spn_txt_ed">
							External Dial Number :
						</span>
						<span id="spn_txt_xf">
						        External Transfer Number:
						</span>
						<span id="spn_txt_ln">
							Language :
						</span>
                        <span id="spn_txt_vt">
                            Seat ID :
						</span>
						 </b>
					</td>
					<td>
					
						<span id="spn_dh">
							<input type="hidden" name="event_dh" id="event_dh" value="<?php echo isset($event_dh) ? htmlentities($event_dh) : '';?>" />
                            <input type="hidden" name="old_event_dh" id="old_event_dh" value="<?php echo isset($event_dh) ? htmlentities($event_dh) : '';?>" />
						</span>

						<span id="spn_sq">
                        	<?php $event_sq = isset($event_sq) ? $event_sq : '';?>
							<?php $this->html_options('event_sq', 'event_sq', $sq_options, $event_sq);?>
							<input type="hidden" name="old_event_sq" id="old_event_sq" value="<?php echo htmlentities($event_sq);?>" />
						</span>
                                                <span id="spn_vm">
                                                        <?php $event_vm = isset($event_vm) ? $event_vm : '';?>
                                                        <?php $this->html_options('event_vm', 'event_vm', $sq_options, $event_vm);?>
                                                        <input type="hidden" name="old_event_vm" id="old_event_vm" value="<?php echo htmlentities($event_vm);?>" />
                                                </span>
						<span id="spn_sr">
                        	<?php $event_sr = isset($event_sr) ? $event_sr : '';?>
							<?php $this->html_options('event_sr', 'event_sr', $sr_options, $event_sr);?>
                            <input type="hidden" name="old_event_sr" id="old_event_sr" value="<?php echo htmlentities($event_sr);?>" />
						</span>

						<span id="spn_sl">
                        	<?php $event_sl = isset($event_sl) ? $event_sl : '';?>
							<?php $this->html_options('event_sl', 'event_sl', $sl_options, $event_sl);?>
                            <input type="hidden" name="old_event_sl" id="old_event_sl" value="<?php echo htmlentities($event_sl);?>" />
						</span>

                                                <span id="spn_fp">
                        	<?php $event_fp = isset($event_fp) ? $event_fp : '';?>
							<?php $this->html_options('event_fp', 'event_fp', $fp_options, $event_fp);?>
                            <input type="hidden" name="old_event_fp" id="old_event_fp" value="<?php echo htmlentities($event_fp);?>" />
						</span>


						<span id="spn_mc">
                        	<?php $event_mc = isset($event_mc) ? $event_mc : '';?>
							<?php $this->html_options('event_mc', 'event_mc', $mc_options, $event_mc);?>
                            <input type="hidden" name="old_event_mc" id="old_event_mc" value="<?php echo htmlentities($event_mc);?>" />
						</span>

						<span id="spn_rv">
							<input type="text" name="event_rv" id="event_rv" maxlength="20" value="<?php echo isset($event_rv) ? htmlentities($event_rv) : '';?>" />
                            <input type="hidden" name="old_event_rv" id="old_event_rv" value="<?php echo isset($event_rv) ? htmlentities($event_rv) : '';?>" />
						</span>
                        
						<span id="spn_go">
                        	<?php $event_go = isset($event_go) ? $event_go : '';?>
							<?php $this->html_options('event_go', 'event_go', $go_options, $event_go, 'display_node_input(this.value);');?>
							<span id="spn_the_node">Node Value: <input type="text" name="the_node" id="the_node" value="<?php echo htmlentities($the_node);?>" size="14" maxlength="20" /></span>
							<input type="hidden" name="old_event_go" id="old_event_go" value="<?php echo htmlentities($event_go);?>" />
						</span>
						<span id="spn_dd">
							<input type="text" name="event_dd" id="event_dd" value="<?php echo isset($event_dd) ? htmlentities($event_dd) : '';?>" maxlength="20" />
							<input type="hidden" name="old_event_dd" id="old_event_dd" value="<?php echo isset($event_dd) ? htmlentities($event_dd) : '';?>" />
						</span>
                        <span id="spn_vt">
							<input type="text" name="event_vt" id="event_vt" value="<?php echo isset($event_vt) ? htmlentities($event_vt) : '';?>" maxlength="20" />
							<input type="hidden" name="old_event_vt" id="old_event_vt" value="<?php echo isset($event_vt) ? htmlentities($event_vt) : '';?>" />
						</span>
						<span id="spn_ed">
							<input type="text" name="event_ed" id="event_ed" value="<?php echo isset($event_ed) ? htmlentities($event_ed) : '';?>" maxlength="20" />
                            <input type="hidden" name="old_event_ed" id="old_event_ed" value="<?php echo isset($event_ed) ? htmlentities($event_ed) : '';?>" />
						</span>
						
						<span id="spn_xf">
							<input type="text" name="event_xf" id="event_xf" value="<?php echo isset($event_xf) ? htmlentities($event_xf) : '';?>" maxlength="14" />
							<input type="hidden" name="old_event_xf" id="old_event_xf" value="<?php echo isset($event_xf) ? htmlentities($event_xf) : '';?>" />
						</span>
						
						<span id="spn_ln">							
                             <select  tabindex="8"  name="event_ln" id="event_ln"   >
			                 		<?php foreach (MLanguage::getActiveLanguageList('A') as $language){
			                 			GetHTMLOption($language->lang_key, $language->lang_title,$event_ln);
			                 		}?>
			                 </select>
                            <input type="hidden" name="old_event_ln" id="old_event_ln" value="<?php echo isset($event_ed) ? htmlentities($event_ln) : '';?>" />
						</span>
					</td>
				</tr>

                <tr class="form_row">
                    <td class="form_column_caption">Branch Title: </td>
                    <td  class="">
                        <?php $this->html_options('title_id', 'title_id', $branch_titles, $ivr_info->title_id);?>
                    </td>
                </tr>

                <tr class="form_row">
                    <td></td>
                    <td  class="">
                        <label class="checkbox-inline"><input type="checkbox" <?php echo $ivr_info->is_srv_log == 'Y' ? 'checked' : '' ?> name='log_service' value="Y">Log Service</label>
                        <label class="checkbox-inline"><input type="checkbox" <?php echo $ivr_info->is_foot_print == 'Y' ? 'checked' : '' ?> name='log_footprint' value="Y">Log Footprint</label>
                        <label class="checkbox-inline"><input type="checkbox" <?php echo $ivr_info->is_trace == 'Y' ? 'checked' : '' ?> name='log_trace' value="Y">Log Trace</label>
                    </td>
                </tr>

                <tr class="form_row disposition-box">
                    <td class="form_column_caption">Disposition ID: </td>
                    <td  class="">
                         <?php $this->html_options('disposition_id', 'disposition_id', $sl_options, $ivr_info->disposition_id);?>
                    </td>
                </tr>

                <tr class="form_row">
                    <td colspan="2" class="form_column_submit">
                        <input class="form_submit_button" type="submit" value="  Save  " name="editivr">
                    </td>
                </tr>
			</tbody>
			</table>
</form>

</center>
<script type="text/javascript">
var luser = '<?php echo UserAuth::getCurrentUser();?>';
loadEventKeyField('<?php echo $event;?>', '');
display_node_input('<?php echo $event_go;?>');
//manageDispositionBox();



$(function () {
   /* $(document).on('change','#event', function () {
        manageDispositionBox();
    }); */
});

function manageDispositionBox(){
    var event = $("#event").val();
    console.log(event);
    if (event == 'AN' || event == 'PF'){
        $(".disposition-box").show();
    } else{
        $(".disposition-box").hide();
    }
}

</script>
