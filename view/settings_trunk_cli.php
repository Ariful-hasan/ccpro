<link rel="stylesheet" href="css/form.css" type="text/css">
<script type="text/javascript">
function checkForm() {
	return true;
}

</script>

<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="cli_form" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>" onSubmit="return checkForm();">
<input type="hidden" name="trunkid" value="<?php echo $trunkid;?>" />
<input type="hidden" name="old_skill" value="<?php echo $old_skill;?>" />
<input type="hidden" name="old_dial_prefix" value="<?php echo $old_dial_prefix;?>" />
<input type="hidden" name="old_cli" value="<?php echo $old_cli;?>" />

			<table class="form_table" border="0" width="460" align="center" cellspacing="0" cellpadding="6">
			<tbody>
				<tr class="form_row_head"><td colspan=2>CLI Options</td></tr>
				<tr class="form_row_alt">
					<td class="form_column_caption">Skill: </td>
					<td>
						<?php $this->html_options('skill', 'skill', $skill_options, $skill);?>
					</td>
				</tr>

				<tr class="form_row">
					<td width="38%" class="form_column_caption">
                    	Dial in prefix: 
                    </td>
					<td>
						<input type="text" style="border-right:0;text-align:right;border-radius: 4px 0 0 4px;width:<?php echo 12+12*$prefix_len;?>px;" value="<?php echo $fixed_prefix;?>" readonly="readonly" onfocus="document.getElementById('dial_prefix').focus();" />
						<input type="text" style="border-left:0;border-radius: 0 4px 4px 0; width:80%; margin-left: -4px;" name="dial_prefix" id="dial_prefix" value="<?php echo $dial_prefix;?>" size="20" maxlength="<?php echo 6-$prefix_len;?>" />
					</td>
				</tr>
				<tr class="form_row_alt">
					<td class="form_column_caption">
						<b>
							CLI :</b>
					</td>
					<td>
							<input type="text" name="cli" id="cli" value="<?php echo $cli;?>" size="20" maxlength="15" />
					</td>
				</tr>
				<tr class="form_row_alt">
					<td colspan="2" class="form_column_submit">
						<input class="form_submit_button" type="submit" value="  Save  " name="editivr">
					</td>
				</tr>
			</tbody>
			</table>
</form>

</center>

