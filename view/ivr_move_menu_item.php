<link href="css/form.css" rel="stylesheet" type="text/css">
<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_trunk" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="ivrid" value="<?php echo $ivrid;?>" />

<table class="form_table" border="0" width="450" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_alt">
	  <td class="form-header"  colspan=2>Node Information</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="33%"><span class="required">*</span> Source node:</td>
		<td>
			<input type="text" name="from_node" size="30" maxlength="21" value="<?php echo $from_node;?>" /> 
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption"><span class="required">*</span> New node: </td>
		<td>
			<input type="text" name="to_node" size="30" maxlength="21" value="<?php echo $to_node;?>" /> 
		</td>
	</tr>
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value=" Move  " name="movenode" /> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>
