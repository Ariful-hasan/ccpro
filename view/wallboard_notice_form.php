<link rel="stylesheet" href="css/form.css" type="text/css">
<script type="text/javascript">
$(document).ready(function() {
	$("#noticeImage").change(function(){
		var ext = $(this).val().split('.').pop().toLowerCase();
		if(ext != 'png') {
			alert('Only png file is allowed to upload');
		} else {
        	readURL(this);
		}
    });
});

function readURL(input) {
	if (input.files && input.files[0]) {
		var reader = new FileReader();
		reader.onload = function (e) {
			$('#noticeImageView').attr('src', e.target.result);
		}
		reader.readAsDataURL(input.files[0]);
	}
}
</script>
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_wallboard" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>" enctype="multipart/form-data">
<input type="hidden" name="nid" value="<?php if (isset($nid)) echo $nid;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Wallboard Notice Information</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" style="vertical-align:top;"><span class="required">*</span> Title:</td>
		<td>
			<input type="text" name="title" size="30" maxlength="20" value="<?php echo $notice->title;?>" />
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" style="vertical-align:top;"><span class="required">*</span> Text:</td>
		<td>
			<textarea name="notice" id="notice" cols="28"><?php echo $notice->notice;?></textarea>
     	</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="33%">Active:</td>
		<td>
			<?php $this->html_options('active', 'active', $yn_options, $notice->active);?>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" style="vertical-align:top;">Image:</td>
		<td>
			<input type='file' id="noticeImage" name="noticeImage" /><br />
			<img id="noticeImageView" src="<?php echo $notice->img;?>" style="width:190px; height:260px;" alt="No image to display" /><br />
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
