<link rel="stylesheet" href="css/form.css" type="text/css">
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form action="<?php echo $this->url('task='.$request->getControllerName()."&act=update");?>" method="post">
<input type="hidden" name="pageid" value="<?php echo $pageid;?>" />
<table class="form_table" border="0" width="450" align="center" cellpadding="6" cellspacing="0">
	<tr class="form_row_head">
		<td colspan=2>Password for <?php echo $pageid;?></td>
	</tr>
  	<tr class="form_row">
		<td class="form_column_caption" width="40%">New password : </td>
		<td>
			<input type="password" name="pass1" value="" maxlength="13" />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Confirm new password : </td>
		<td>
			<input type="password" name="pass2" value="" maxlength="12" />
		</td>
	</tr>
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
        	<input class="form_submit_button" type="submit" name="resetpass" value="Save" />
		</td>
	</tr>
</table>

</form>
