<link rel="stylesheet" href="css/form.css" type="text/css">
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_wallboard" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="tid" value="<?php if (isset($tid)) echo $tid;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Wallboard Scroll Text</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" style="vertical-align:top;"><span class="required">*</span> Text:</td>
		<td>
			<textarea name="txt" id="txt" cols="28"><?php echo $scroll_text->txt;?></textarea>
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" width="33%">Active:</td>
		<td>
			<?php $this->html_options('active', 'active', $yn_options, $scroll_text->active);?>
		</td>
	</tr>

	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if (!empty($tid)):?>Update<?php else:?>Add<?php endif;?>  " name="submittxt"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>
