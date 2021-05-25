<link rel="stylesheet" href="css/form.css" type="text/css">
<script type="text/javascript">
function displayValueSpan(val)
{
	document.getElementById('spn_service_cap').style.display='none';
	document.getElementById('spn_ivr_cap').style.display='none';
	document.getElementById('spn_other_cap').style.display='none';
	document.getElementById('spn_service_val').style.display='none';
	document.getElementById('spn_ivr_val').style.display='none';
	document.getElementById('spn_other_val').style.display='none';
	if (val=='SQ' || val=='IV') {
		document.getElementById('tr-sk-iv').style.display = 'table-row';
		document.getElementById('tr-submit').className = 'form_row_alt';
	} else {
		document.getElementById('tr-sk-iv').style.display = 'none';
		document.getElementById('tr-submit').className = 'form_row';
	}
	
	if(val=='SQ') {
		document.getElementById('spn_service_cap').style.display='block';
		document.getElementById('spn_service_val').style.display='block';
	} else if(val=='IV') {
		document.getElementById('spn_ivr_cap').style.display='block';
		document.getElementById('spn_ivr_val').style.display='block';
	} else {
		document.getElementById('spn_other_cap').style.display='block';
		document.getElementById('spn_other_val').style.display='block';
	}
}

function checkForm()
{
	if(document.getElementById('cti_action').value.length <= 0) {
		alert('Select CTI Action!!');
		return false;
	}
	
	return true;
}

</script>

<?php if (!empty($errMsg)):?><div class="error-msg"><?php echo $errMsg;?></div><?php endif;?>

<form action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>" method="post" onsubmit="return checkForm();">
<input type="hidden" name="task" value="edit" />
<input type="hidden" name="save" value="true" />
<table class="form_table" border="0" width="480" align="center" cellpadding="6" cellspacing="0">
	<tbody>
		<tr class="form_row_head">
		  <td colspan="2">Priority Configuration</td>
		</tr>
		<tr class="form_row">
			<td class="form_column_caption">Priority: </td>
			<td>
            	<input type="hidden" name="plevel" value="<?php echo $precord->priority_level?>" />
            	<?php
                	if ($precord->priority_level == 'L') echo 'Low';
					else echo 'High';
				?>
            </td>
        </tr>

		<tr class="form_row_alt">
			<td class="form_column_caption">CTI Action: </td>
			<td>
            	<input type="hidden" name="old_cti_action" value="<?php echo $precord->cti_action;?>" />
				<?php $this->html_options('cti_action', 'cti_action', $cti_action_options, $precord->cti_action, "displayValueSpan(this.value);");?>
            </td>
        </tr>

		<tr class="form_row" id="tr-sk-iv">
			<td class="form_column_caption" valign="top">
            	<span id="spn_service_cap" style="display:<?php if ($precord->cti_action == 'SQ') echo 'block'; else echo 'none';?>;">Skill:</span>
                <span id="spn_ivr_cap" style="display:<?php if ($precord->cti_action == 'IV') echo 'block'; else echo 'none';?>;">IVR:</span>
                <span id="spn_other_cap" style="display:none;"></span>
            </td>
			<td>
            	<span id="spn_service_val" style="display:<?php if ($precord->cti_action == 'SQ') echo 'block'; else echo 'none';?>;">
				<?php $this->html_options('service_name', 'service_name', $skill_options, $precord->param);?>
                </span>
                <span id="spn_ivr_val" style="display:<?php if ($precord->cti_action == 'IV') echo 'block'; else echo 'none';?>;">
				<?php $this->html_options('ivr_name', 'ivr_name', $ivr_options, $precord->param);?>
                </span>
                <span id="spn_other_val" style="display:<?php if ($precord->cti_action == '') echo 'block'; else echo 'none';?>;">
                	&nbsp;
                </span>
                <input type="hidden" name="old_value" value="<?php echo $precord->param;?>" />
            </td>
        </tr>

		<tr class="form_row_alt" id="tr-submit">
			<td colspan="2" class="form_column_submit">
				<input class="form_submit_button" type="submit" value=" Update " name="update_psettings"> <br>
			</td>
		</tr>
	</tbody>
</table>
</form>
<script>displayValueSpan('<?php echo $precord->cti_action;?>');</script>