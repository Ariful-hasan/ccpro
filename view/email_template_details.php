<link href="css/form.css" rel="stylesheet" type="text/css">
<style type="text/css">
.textcontent{
	background-color: #ffffff;
    border: 1px solid #cccccc;
	border-radius: 3px;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;
    transition: border 0.2s linear 0s, box-shadow 0.2s linear 0s;
	width: 500px;
	padding: 8px;
}
</style>
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Template Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Title:</td>
		<td>
			<?php echo isset($service->title) ? $service->title : "";?>
     	</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="33%" valign="top">Text:</td>
		<td>
			<div class="textcontent">
				<?php echo isset($service->mail_body) ? $service->mail_body : "";?>
			</div>
		</td>
	</tr>	
	<tr class="form_row_alt">
		<td class="form_column_caption">Status:</td>
		<td>
			<?php echo isset($service->status) && $service->status == "Y" ? "Enable" : "Disable"; ?>
		</td>
	</tr>
</tbody>
</table>
<br>
<p align="center">
	<a class="form_submit_button" href="#" onclick="parent.$.colorbox.close();">Close</a>
</p>
