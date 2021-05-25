<?php
	$image_src = 'image/blank_logo.png';
	$custom_image = false;
	if (isset($owner_logo) && !empty($owner_logo)) {
		if (file_exists('agents_picture/'. $owner_logo.'.png')) {
			$image_src = 'agents_picture/'.$owner_logo.'.png?t=' . time();
			$custom_image = true;
		}
	}
?>
<style type="text/css">
label.error {
    float: right;
    font-size: 12px;
    margin-top: 4px;
    width: 56%;
}
.form_table td.form_column_caption {
    padding-top: 14px;
    vertical-align: top;
}
</style>
<script type="text/javascript">
$(document).ready(function() {
	$("#agentImage").change(function(){
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
			$('#agentImageView').attr('src', e.target.result);
		}
		reader.readAsDataURL(input.files[0]);
	}
}
</script>

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_agent" id="frm_agent" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>" enctype="multipart/form-data">

<table class="form_table">
<tbody>
    <tr class="form_row_head">
	  <td colspan=2 style="background-color:#2583AD; color:#ffffff; font-size:15px; font-weight:bold; padding:3px;">Upload Logo Image</td>
	</tr>
	<tr class="form_row_alt">
        <td align="left" style="vertical-align:top;" width="50%">
        
        <div style="width:220px; text-align:center;">
            <div style="padding:5px 18px;"><input type='file' id="agentImage" name="agentImage" style="height: 25px;" /></div>
            <div style="clear:both;margin-bottom:-8px;">&nbsp;</div>
            <div style="width:100%; position:relative;">
                <?php if ($custom_image):?>
                <a href="<?php echo $this->url("task=settings&act=dellogopic");?>" onclick="return confirm('Are you sure to remove this logo?');">
                    <img src="image/cancel.png" class="bottom" border="0" width="16" height="16" style="position:absolute; right:7px; top:-10px; z-index:50;" />
                </a>
        		<?php endif;?>
        		<img id="agentImageView" src="<?php echo $image_src;?>" style="width:180px; height:46px; border:1px solid #eeeeee;" alt="No image found" /><br />
        		<small style="clear:both; margin-top:5px; display:block;">Best view size: 180 x 46 px</small>
            </div>
		</div>
        </td>
        <td width="50%">&nbsp;</td>
	</tr>
   
	<tr class="form_row_alt">
		<td align="left" style="padding-left: 70px;">
		    <br>
			<input class="form_submit_button" type="submit" value="  Upload  " name="submitagent">
			<br><br>
		</td>
		<td>&nbsp;</td>
	</tr>
</tbody>
</table>
</form>

