<link href="css/form.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
$(document).ready(function(){
	$('#user').focus();
	$('#login').attr('autocomplete', 'off');
});
</script>

<form name="login" id="login" method="post" autocomplete="off" action="<?php echo $this->url('task='.$request->getControllerName());?>">
<input type="hidden" name="lparam" value="<?php echo $lparam;?>" />
<div style="text-align:center; padding:150px 0px;"><center>
<?php if (!empty($errMsg)):?><div class="form_error_message"><?php echo $errMsg;?></div><?php endif;?>
<table class="form_table" border=0 align="center" cellpadding="4" cellspacing="0" style="width: 320px;">
<tr class="form_row_head">
	<td>
		Enter Login Information
	</td>
</tr>
<tr>
	<td align=center width="330">
		<table border="0" width="100%" cellspacing="0" cellpadding="4">
		<tr>
			<td width="45%" class="form_column_caption">User : </td>
			<td><input type=text name="user" id="user" size="22" value="<?php echo $request->getPost('user');?>" maxlength="10" /></td>
		</tr>
		<tr>
			<td class="form_column_caption">Password : </td><td><input type=password name="pass" id="pass" size="22" value="" maxlength="15" autocomplete="off" /></td>
		</tr>
		<tr>
			<td colspan="2" align="center"><input  class="form_submit_button" type="submit" value="     Sign In     " /></td>
		</tr>
		</table>
	</td>
</tr>
</table></center>
</div>
</form>
