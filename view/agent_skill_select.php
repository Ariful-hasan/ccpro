<link href="css/form.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
function LoadListBoxValues( priority, values ) {
	// console.log(priority);
	var TSel = document.getElementById('skills_selected' + priority);

	var FSel0 = document.getElementById('skills_available0');
	var FSel1 = document.getElementById('skills_available1');
	var FSel2 = document.getElementById('skills_available2');
	var FSel3 = document.getElementById('skills_available3');
	var FSel4 = document.getElementById('skills_available4');
	var FSel5 = document.getElementById('skills_available5');
	var FSel6 = document.getElementById('skills_available6');
	var FSel7 = document.getElementById('skills_available7');
	var FSel8 = document.getElementById('skills_available8');
	var FSel9 = document.getElementById('skills_available9');
	var FSel10 = document.getElementById('skills_available10');

	var val_arr = values.split(',');
	var val_len = val_arr.length;
	if(val_len <= 1) {
		if(val_arr[0].length == 0)
			return false;
	}
	
	for(i=0; i<val_len; i++) {
		var theVal = val_arr[i];
		if(theVal.length == 0) continue;

		var if_added = false;
		if(priority < 10){
			for(j=0; j<FSel1.length; j++) {
				if(FSel1.options[j].value == theVal) {
					newValue=FSel1.options[j].value;
					newText=FSel1.options[j].text;
					var newOpt1 = new Option(newText, newValue);
					TSel.options[TSel.length] = newOpt1;
					FSel1.options[j] = null;
					if_added = true;
					break;
				}
			}
		}else if(priority == 10){
			for(j=0; j<FSel10.length; j++) {
				if(FSel10.options[j].value == theVal) {
					newValue=FSel10.options[j].value;
					newText=FSel10.options[j].text;
					var newOpt10 = new Option(newText, newValue);
					TSel.options[TSel.length] = newOpt10;
					FSel10.options[j] = null;
					if_added = true;
					break;
				}
			}
		}

		// console.log(theVal);
		// console.log(FSel0);

		remove_value(FSel0, theVal);
		remove_value(FSel2, theVal);
		remove_value(FSel3, theVal);
		remove_value(FSel4, theVal);
		remove_value(FSel5, theVal);
		remove_value(FSel6, theVal);
		remove_value(FSel7, theVal);
		remove_value(FSel8, theVal);
		remove_value(FSel9, theVal);
		remove_value(FSel10, theVal);

		/*
		for(j=0; j<FSel2.length; j++) {
			if(FSel2.options[j].value == theVal) {
				if (!if_added) {
					newValue=FSel2.options[j].value;
					newText=FSel2.options[j].text;
					var newOpt1 = new Option(newText, newValue);
					TSel.options[TSel.length] = newOpt1;
					if_added = true;
				}
				FSel2.options[j] = null;
				break;
			}
		}
		
		for(j=0; j<FSel3.length; j++) {
			if(FSel3.options[j].value == theVal) {
				if (!if_added) {
					newValue=FSel3.options[j].value;
					newText=FSel3.options[j].text;
					var newOpt1 = new Option(newText, newValue);
					TSel.options[TSel.length] = newOpt1;
					if_added = true;
				}
				FSel3.options[j] = null;
				break;
			}
		}
		*/
		
	}
}

function remove_value(elm, val)
{
    for (var j=0; j<elm.length; j++) {
        if (elm.options[j].value == val) {
            elm.options[j] = null;
            break;
        }
    }
}

function remove_sel(priority) {
	var toElm0 = document.getElementById('skills_available0');
	var toElm1 = document.getElementById('skills_available1');
	var toElm2 = document.getElementById('skills_available2');
	var toElm3 = document.getElementById('skills_available3');
	var toElm4 = document.getElementById('skills_available4');
	var toElm5 = document.getElementById('skills_available5');
	var toElm6 = document.getElementById('skills_available6');
	var toElm7 = document.getElementById('skills_available7');
	var toElm8 = document.getElementById('skills_available8');
	var toElm9 = document.getElementById('skills_available9');
	var toElm10 = document.getElementById('skills_available10');

	var frmElm = document.getElementById('skills_selected' + priority);

	var newValue, newText;
	var selIndex = frmElm.selectedIndex;
	
	if (selIndex>=0) {
		newValue = frmElm.options[selIndex].value;
		newText = frmElm.options[selIndex].text;

		if(priority==10){
			var newOpt10 = new Option(newText, newValue);

			toElm10.options[toElm10.length] = newOpt10;
			toElm10.selectedIndex =toElm10.length-1;
		}else{
			var newOpt0 = new Option(newText, newValue);
			var newOpt1 = new Option(newText, newValue);
			var newOpt2 = new Option(newText, newValue);
			var newOpt3 = new Option(newText, newValue);
			var newOpt4 = new Option(newText, newValue);
			var newOpt5 = new Option(newText, newValue);
			var newOpt6 = new Option(newText, newValue);
			var newOpt7 = new Option(newText, newValue);
			var newOpt8 = new Option(newText, newValue);
			var newOpt9 = new Option(newText, newValue);

			toElm0.options[toElm0.length] = newOpt0;
			toElm0.selectedIndex =toElm0.length-1;

			toElm1.options[toElm1.length] = newOpt1;
			toElm1.selectedIndex =toElm1.length-1;

			toElm2.options[toElm2.length] = newOpt2;
			toElm2.selectedIndex =toElm2.length-1;

			toElm3.options[toElm3.length] = newOpt3;
			toElm3.selectedIndex =toElm3.length-1;

			toElm4.options[toElm4.length] = newOpt4;
			toElm4.selectedIndex =toElm4.length-1;

			toElm5.options[toElm5.length] = newOpt5;
			toElm5.selectedIndex =toElm5.length-1;

			toElm6.options[toElm6.length] = newOpt6;
			toElm6.selectedIndex =toElm6.length-1;

			toElm7.options[toElm7.length] = newOpt7;
			toElm7.selectedIndex =toElm7.length-1;

			toElm8.options[toElm8.length] = newOpt8;
			toElm8.selectedIndex =toElm8.length-1;

			toElm9.options[toElm9.length] = newOpt9;
			toElm9.selectedIndex =toElm9.length-1;
		}

		frmElm.options[selIndex] = null;
		if (frmElm.length > 0) {
			frmElm.selectedIndex = selIndex == 0 ? 0 : selIndex - 1;
		}
		
		remove_value(frmElm, newValue);
		
		calcValue(frmElm, 'agent_skills' + priority);
	}
}

function append(priority) {

	var frmElm0 = document.getElementById('skills_available0');
	var frmElm1 = document.getElementById('skills_available1');
	var frmElm2 = document.getElementById('skills_available2');
	var frmElm3 = document.getElementById('skills_available3');
	var frmElm4 = document.getElementById('skills_available4');
	var frmElm5 = document.getElementById('skills_available5');
	var frmElm6 = document.getElementById('skills_available6');
	var frmElm7 = document.getElementById('skills_available7');
	var frmElm8 = document.getElementById('skills_available8');
	var frmElm9 = document.getElementById('skills_available9');
	var frmElm10 = document.getElementById('skills_available10');

	var toElm = document.getElementById('skills_selected' + priority);
	
	if (priority == 9) frmElm = frmElm9;
	else if (priority == 10) frmElm = frmElm10;
	else if (priority == 8) frmElm = frmElm8;
	else if (priority == 7) frmElm = frmElm7;
	else if (priority == 6) frmElm = frmElm6;
	else if (priority == 5) frmElm = frmElm5;
	else if (priority == 4) frmElm = frmElm4;
	else if (priority == 3) frmElm = frmElm3;
	else if (priority == 2) frmElm = frmElm2;
	else if (priority == 0) frmElm = frmElm0;
	else frmElm = frmElm1;
	
	var newValue, newText;
	var selIndex = frmElm.selectedIndex;
	
	if (selIndex>=0) {
		newValue = frmElm.options[selIndex].value;
		newText = frmElm.options[selIndex].text;
		var newOpt1 = new Option(newText, newValue);
		toElm.options[toElm.length] = newOpt1;
		toElm.selectedIndex =toElm.length-1;
		
		frmElm.options[selIndex] = null;
		if (frmElm.length > 0) {
			frmElm.selectedIndex = selIndex == 0 ? 0 : selIndex - 1;
		}

		remove_value(frmElm0, newValue);		
		remove_value(frmElm1, newValue);
		remove_value(frmElm2, newValue);
        remove_value(frmElm3, newValue);
		remove_value(frmElm4, newValue);
		remove_value(frmElm5, newValue);
		remove_value(frmElm6, newValue);
		remove_value(frmElm7, newValue);
		remove_value(frmElm8, newValue);
		remove_value(frmElm9, newValue);
		remove_value(frmElm10, newValue);

		calcValue(toElm, 'agent_skills' + priority);
	}
	
}

function calcValue(toElm, valFld) {
	var retVal = '';
	//var toElm = document.getElementById(toList);

	for(i = 0 ; i < toElm.length ; i++) {
		var val = toElm.options[i].value;
		
		if(val.length > 0) {
			if (retVal.length > 0) retVal += ',';
			retVal += val;
		}
	}
	document.getElementById(valFld).value = retVal;
	//return retVal;
}
</script>

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_skill" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="agentid" value="<?php echo $agentid;?>" />
<table class="form_table">
<?php for ($i=1; $i<=9; $i++):
$class =  'form_row';
$agent_var_name = 'agent_skills' . $i;
?>
	<tr class="form_row_alt"><td>Priority <?php echo $i;?>:</td></tr>
	<tr class="<?php echo $class;?>">
		<td colspan="2">
			<table align="center">
			<tr>
				<td>
					<select class="sel-multi-box" name="skills_available<?php echo $i;?>" id="skills_available<?php echo $i;?>" multiple="multiple" size="5">
					<?php if (is_array($skill_options)): ?>
						<?php foreach ($skill_options as $skill_id => $skill_option): ?>
							<?php if ($skill_option['qtype'] != 'P'): ?>
								<option value="<?php echo $skill_id;?>"><?php echo $skill_option['name'];?></option>
							<?php endif;?>
						<?php endforeach;?>
					<?php endif;?>
					</select>
				</td>
				<td style="text-align:center;padding:0;">
					<input type="button" class="submit-button" value="Add>>" style="width:100px; margin-bottom:5px;" onclick="append(<?php echo $i;?>);" />
					<input type="button" class="submit-button" value="<<Remove" style="width:100px;" onclick="remove_sel(<?php echo $i;?>);" />
				</td>
				<td>
					<select class="sel-multi-box" name="skills_selected<?php echo $i;?>" id="skills_selected<?php echo $i;?>" multiple="multiple" size="5" style="width:250px;"></select>
					<input type="hidden" id="agent_skills<?php echo $i;?>" name="agent_skills<?php echo $i;?>" value="<?php echo ${$agent_var_name};?>" />
				</td>
			</tr>
			</table>
		</td>
	</tr>
<?php  endfor; ?>

<?php
$i = 0;
$class = 'form_row';
$agent_var_name = 'agent_skills' . $i;
// var_dump($skill_options);
?>
	
    <tr class="form_row_alt "><td>Outbound Only:</td></tr>
    <tr class="<?php echo $class;?> ">
        <td colspan="2">
            <table align="center">
            <tr>
                <td>
                    <select class="sel-multi-box" name="skills_available<?php echo $i;?>" id="skills_available<?php echo $i;?>" multiple="multiple" size="5">
                    <?php if (is_array($skill_options)): ?>
						<?php foreach ($skill_options as $skill_id => $skill_option): ?>
							<?php if ($skill_option['qtype'] != 'P'): ?>
								<option value="<?php echo $skill_id;?>"><?php echo $skill_option['name'];?></option>
							<?php endif;?>
						<?php endforeach;?>
					<?php endif;?>
                    </select>
                </td>
                <td style="text-align:center;padding:0;">
                    <input type="button" class="submit-button" value="Add>>" style="width:100px; margin-bottom:5px;" onclick="append(<?php echo $i;?>);" />
                    <input type="button" class="submit-button" value="<<Remove" style="width:100px;" onclick="remove_sel(<?php echo $i;?>);" />
                </td>
                <td>
                    <select class="sel-multi-box" name="skills_selected<?php echo $i;?>" id="skills_selected<?php echo $i;?>" multiple="multiple" size="5" style="width:250px;"></select>
                    <input type="hidden" id="agent_skills<?php echo $i;?>" name="agent_skills<?php echo $i;?>" value="<?php echo ${$agent_var_name};?>" />
                </td>
            </tr>
            </table>
        </td>
    </tr>

    <?php
	$i = 10;
	$class = 'form_row';
	$agent_var_name = 'agent_skills' . $i;
	// var_dump($skill_options);
	?>

    <tr class="form_row_alt "><td>Predictive Only:</td></tr>
    <tr class="<?php echo $class;?> ">
        <td colspan="2">
            <table align="center">
            <tr>
                <td>
                    <select class="sel-multi-box" name="skills_available<?php echo $i;?>" id="skills_available<?php echo $i;?>" multiple="multiple" size="5">
                    <?php if (is_array($skill_options)): ?>
						<?php foreach ($skill_options as $skill_id => $skill_option): ?>
							<?php if ($skill_option['qtype'] == 'P'): ?>
								<option value="<?php echo $skill_id;?>"><?php echo $skill_option['name'];?></option>
							<?php endif;?>
						<?php endforeach;?>
					<?php endif;?>
                    </select>
                </td>
                <td style="text-align:center;padding:0;">
                    <input type="button" class="submit-button" value="Add>>" style="width:100px; margin-bottom:5px;" onclick="append(<?php echo $i;?>);" />
                    <input type="button" class="submit-button" value="<<Remove" style="width:100px;" onclick="remove_sel(<?php echo $i;?>);" />
                </td>
                <td>
                    <select class="sel-multi-box" name="skills_selected<?php echo $i;?>" id="skills_selected<?php echo $i;?>" multiple="multiple" size="5" style="width:250px;"></select>
                    <input type="hidden" id="agent_skills<?php echo $i;?>" name="agent_skills<?php echo $i;?>" value="<?php echo ${$agent_var_name};?>" />
                </td>
            </tr>
            </table>
        </td>
    </tr>

	<tr class="form_row">
		<td colspan="2" class="form_column_submit" style="text-align:center;padding:20px 0;">
			<input class="form_submit_button" type="submit" value="Save" name="submitagent" />  &nbsp; &nbsp;
            <input class="form_submit_button" type="button" value="Cancel" name="submitcancel" onclick="parent.$.colorbox.close();" />
            
		</td>
	</tr>
</table>
</form>
<script type="text/javascript">
    $(function () {
        $(".hide_priority").hide();
    });
	LoadListBoxValues('0', '<?php echo $agent_skills0;?>');
	LoadListBoxValues('1', '<?php echo $agent_skills1;?>');
	LoadListBoxValues('2', '<?php echo $agent_skills2;?>');
	LoadListBoxValues('3', '<?php echo $agent_skills3;?>');
	LoadListBoxValues('4', '<?php echo $agent_skills4;?>');
	LoadListBoxValues('5', '<?php echo $agent_skills5;?>');
	LoadListBoxValues('6', '<?php echo $agent_skills6;?>');
	LoadListBoxValues('7', '<?php echo $agent_skills7;?>');
	LoadListBoxValues('8', '<?php echo $agent_skills8;?>');
	LoadListBoxValues('9', '<?php echo $agent_skills9;?>');
	LoadListBoxValues('10', '<?php echo $agent_skills10;?>');
</script>