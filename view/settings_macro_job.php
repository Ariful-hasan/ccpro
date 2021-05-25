<link rel="stylesheet" href="css/form.css" type="text/css">
<script type="text/javascript">
function checkForm() {
	return true;
}

function loadNumberField()
{
        console.log("T: " + document.getElementById("action_type").value);
        if (document.getElementById("action_type").value == 'SQ' || document.getElementById("action_type").value == 'VM') {
                document.getElementById("txtSkill").style.display = 'block';
                document.getElementById("inpSkill").style.display = 'block';
                document.getElementById("txtIVR").style.display = 'none';
                document.getElementById("inpIVR").style.display = 'none';
                document.getElementById("txtParam").style.display = 'none';
                document.getElementById("inpParam").style.display = 'none';
        } else if (document.getElementById("action_type").value == 'XF') {
                document.getElementById("txtSkill").style.display = 'none';
                document.getElementById("inpSkill").style.display = 'none';
                document.getElementById("txtIVR").style.display = 'none';
                document.getElementById("inpIVR").style.display = 'none';
                document.getElementById("txtParam").style.display = 'block';
                document.getElementById("inpParam").style.display = 'block';
        } else {
                document.getElementById("txtSkill").style.display = 'none';
                document.getElementById("inpSkill").style.display = 'none';
                document.getElementById("txtIVR").style.display = 'block';
                document.getElementById("inpIVR").style.display = 'block';
                document.getElementById("txtParam").style.display = 'none';
                document.getElementById("inpParam").style.display = 'none';
        }
}
</script>

<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<?php
        if (empty($action_type)) {
                $action_type = 'IV';
        }
?>
<form name="cli_form" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>" onSubmit="return checkForm();">

	<input type="hidden" name="macro_code" value="<?php echo $macro_code;?>" />
	<input type="hidden" name="cti" value="<?php echo $cti;?>" />
	<input type="hidden" name="old_skill_id" value="<?php echo $old_skill_id;?>" />
	<input type="hidden" name="old_ivr_id" value="<?php echo $old_ivr_id;?>" />
	<input type="hidden" name="old_param" value="<?php echo $old_param;?>" />
	<input type="hidden" name="old_action_type" value="<?php echo $old_action_type;?>" />

			<table class="form_table" border="0" width="460" align="center" cellspacing="0" cellpadding="6">
			<tbody>
				<tr class="form_row_head"><td colspan=2>Macro Settings [<?php echo $macro_code;?>]</td></tr>
				<tr class="form_row">
                                        <td class="form_column_caption">CTI: </td>
                                        <td>
                                                <?php $this->html_options('cti_id', 'cti_id', $cti_options, $cti_id);?>
                                        </td>
                                </tr>
                                <tr class="form_row">
                                        <td class="form_column_caption">Action Type: </td>
                                        <td>
                                                <?php $this->html_options('action_type', 'action_type', array('IV' => 'IVR', 'SQ' => 'Skill', 'XF' => 'External Forward', 'VM' => 'Voice Mail'), $action_type, "loadNumberField();");?>
                                        </td>
                                </tr>
				<tr class="form_row">
                                        <td class="form_column_caption">
                                                <span id="txtSkill" style="display:block;">Skill: </span>
                                                <span id="txtIVR" style="display:block;">IVR: </span>
                                                <span id="txtParam" style="display:block;">Forward Number: </span>
                                        </td>
                                        <td>
                                                <span id="inpSkill" style="display:block;">
                                                <?php $this->html_options('skill_id', 'skill_id', $skill_options, $skill_id);?>
                                                </span>
                                                <span id="inpIVR" style="display:block;">
                                                <?php $this->html_options('ivr_id', 'ivr_id', $ivr_options, $ivr_id);?>
                                                </span>
                                                <span id="inpParam" style="display:block;">
                                                <input type="text" name="param" id="param" value="<?php echo $param;?>" size="20" maxlength="20" />
                                                </span>
                                        </td>
                                </tr>
				<tr class="form_row_alt">
					<td colspan="2" class="form_column_submit">
						<input class="form_submit_button" type="submit" value="  Save  " name="editivr" />
					</td>
				</tr>
			</tbody>
			</table>
</form>

</center>
<script type="text/javascript">
loadNumberField();
</script>