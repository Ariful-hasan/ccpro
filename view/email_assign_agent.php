<link href="css/form.css" rel="stylesheet" type="text/css">

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_agent" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="tid" value="<?php echo $tid;?>" />
<input type="hidden" name="msl" value="<?php echo !empty($msl)?$msl:"";?>" />
<table class="form_table">
	<tr class="form_row_alt">
		<td class="form_column_caption" style="text-align:center;"><b>Agent:</b>
			<select name="agent_id" id="agent_id">
				<option value="">Select</option>
				<?php if (is_array($agents)) {
					foreach ($agents as $agent) {
						echo '<option value="' . $agent->agent_id . '"';
						if ($ticket_info->assigned_to == $agent->agent_id) echo ' selected';
						echo '>' . $agent->agent_id . ' - ' . $agent->nick . '</option>';
					}
				}
				?>
			</select>
		</td>
	</tr>
<tr class="form_row">
		<td class="form_column_submit" style="text-align:center;padding:20px 0;">
			<input class="form_submit_button" type="submit" value="Save" name="submitagent" />  &nbsp; &nbsp;
            <input class="form_submit_button" type="button" value="Cancel" name="submitcancel" onclick="parent.$.colorbox.close();" />
            
		</td>
	</tr>
</table>
</form>
