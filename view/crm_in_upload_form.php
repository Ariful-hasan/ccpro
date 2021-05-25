<link rel="stylesheet" type="text/css" media="screen" href="css/form.css">
<script>
function closeWindow() {
	<?php if (isset($refreshParent)):?>
	window.opener.location.href = window.opener.location.href;
	if (window.opener.progressWindow) {
		window.opener.progressWindow.close();
	}
	<?php endif;?>
	window.close();
}

function readFile()
{
	var param= document.getElementById('number').value;
	var ext = param.substr(-3);
	if(ext == 'csv'){
		return true;
	} else {
		alert("Only csv file allowed");
		document.getElementById('number').value='';
	}
	return false;
}
</script>
<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_crm" enctype="multipart/form-data" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<table class="form_table" border="0" width="500" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>CRM Information</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="45%">Upload record file</td>
		<td>
			<input type="file" id="number" name="number" size="30" value="" onChange="readFile()" style="float:left" >
		</td>
	</tr>
	<tr class="form_row_head">
	  <td colspan=2>Column Mapping</td>
	</tr>
<?php
$i = 0;
foreach ($headings as $hkey=>$hlabel) {
	//$cls = $i%2 == 0 ? 'form_row_alt' : 'form_row';
	$cls = 'form_row';
	echo '<tr class="' . $cls . '">
		<td class="form_column_caption">' . $hlabel . ': </td>
		<td>
			<input type="text" id="' . $hkey . '_heading" name="' . $hkey . '_heading" size="20" value="' . $heading->{$hkey . '_heading'} . '" />
		</td>
	</tr>';
	$i++;
}
$cls = $i%2 == 0 ? 'form_row_alt' : 'form_row';
?>

	<tr class="<?php echo $cls;?>">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value=" Upload  " name="submitcrm" /> <br>
		</td>
	</tr>
</tbody>
</table>
</form>
