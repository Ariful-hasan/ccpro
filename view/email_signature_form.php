<?php include_once ('lib/summer_note_lib.php')?>


<link rel="stylesheet" href="css/form.css" type="text/css">
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_signature" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="sid" value="<?php if (isset($sid)) echo $sid;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Signature Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" width="33%" valign="top">Signature Text:</td>
		<td>
			<textarea name="signature_text" id="signature_text" style="width:600px;height:200px;"><?php echo $service->signature_text;?></textarea>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Status</td>
		<td>
			<select id="status" name="status">
				<option value="N"<?php if ($service->status != 'Y') echo ' selected="selected"';?>>Disable</option>
				<option value="Y"<?php if ($service->status == 'Y') echo ' selected="selected"';?>>Enable</option>
			</select>
		</td>
	</tr>

	<tr class="form_row_alt">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  Save  " name="submitservice"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>
    <script>
        $( document ).ready( function() {
            Initialize_Summer_note('textarea#signature_text', ['table','view'],"<?php echo $this->url('task=emailsignature&act=uploadSignatureImage&sid='.$sid); ?>", "<?php echo $this->url('task=emailsignature&act=DeleteSignatureImage'); ?>");
        } );
        $(window).load(function() {
            var text = $('textarea#signature_text').val();
            $('textarea#signature_text').summernote('reset');
            $("textarea#signature_text").summernote('code',text);
        });
    </script>