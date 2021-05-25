<link rel="stylesheet" type="text/css" media="screen" href="css/form.css">
<?php
if ($num_selected == 1) {
	$rec = 'record(s)';
	$self = 'this';
} else {
	$rec = 'records';
	$self = 'these';
}
?>
<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_campaign" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="dial_num" value="<?php echo $dial_num;?>" />
<input type="hidden" name="camid" value="<?php echo $camid;?>" />
<input type="hidden" name="leadid" value="<?php echo $leadid;?>" />
<input type="hidden" name="disp_code" value="<?php echo $disp_code;?>" />
<input type="hidden" name="num_selected" value="<?php echo $num_selected;?>" />
<table class="form_table" border="0" width="500" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Lead Information</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="45%">Number of <?php echo $rec;?> selected</td>
		<td>
			<?php echo $num_selected;?>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Lead title: </td>
		<td>
			<input type="text" name="title" size="30" maxlength="30" value="<?php echo $lead->title;?>" />
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Reference: </td>
		<td>
			<input type="text" name="reference" size="30" maxlength="12" value="<?php echo $lead->reference;?>" />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Country code: </td>
		<td>
			<input type="text" name="country_code" size="30" maxlength="2" value="<?php echo $lead->country_code;?>" />
		</td>
	</tr>
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value=" <?php if (!empty($lid)):?>Update<?php else:?>Add<?php endif;?>  " name="submitlead" /> <br>
		</td>
	</tr>
</tbody>
</table>
</form>
