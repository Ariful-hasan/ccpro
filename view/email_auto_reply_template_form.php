

<link href="js/summernote/summernote.css" rel="stylesheet"></link>
<script src="js/summernote/summernote.js"></script>
<?php include_once ('lib/summer_note_lib.php')?>
<script>
$( document ).ready( function() {
    Initialize_Summer_note('textarea#mail_body', ['view'],"<?php echo $this->url('task=emailartemplate&act=upload-auto-reply-image&tid='.$tstamp_code); ?>", "<?php echo $this->url('task=emailartemplate&act=delete-auto-reply-image'); ?>");
} );
$(window).load(function() {
    var text = $('textarea#mail_body').val();
    $('textarea#mail_body').summernote('reset');
    $("textarea#mail_body").summernote('code',text);
});
</script>
<link rel="stylesheet" href="css/form.css" type="text/css">
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_ivr_service" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="tid" value="<?php if (isset($tstamp_code)) echo $tstamp_code;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Template Information</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="33%" valign="top"><span class="required">*</span> Text:</td>
		<td>
			<textarea name="mail_body" id="mail_body" style="width:600px;height:200px;"><?php echo $service->mail_body;?></textarea>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Status</td>
		<td>
			<select id="status" name="status">
				<option value="N"<?php if ($service->status != 'Y') echo ' selected="selected"';?>>Disable</option>
				<option value="Y"<?php if ($service->status == 'Y') echo ' selected="selected"';?>>Enable</option>
			</select>
		</td>
	</tr>
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if (!empty($tstamp_code)):?>Update<?php else:?>Add<?php endif;?>  " name="submitservice"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>
