<link href="css/form.css" rel="stylesheet" type="text/css">
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_disposition" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="tid" value="<?php if (isset($tid)) echo $tid;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Template Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" width="33%"><span class="required">*</span> Title:</td>
		<td>
			<input type="text" name="title" size="30" maxlength="50" value="<?php echo $template->title;?>" />
		</td>
	</tr>
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if (!empty($tid)):?>Update<?php else:?>Add<?php endif;?>  " name="submittemplate"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>
