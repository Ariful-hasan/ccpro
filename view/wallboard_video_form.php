<link rel="stylesheet" href="css/form.css" type="text/css">
<link rel="stylesheet" href="js/lightbox/css/colorbox.css" />
<script src="js/lightbox/js/jquery.colorbox-min.js"></script>

<script type="text/javascript">
$(document).ready(function() {
	$("#source_type").change(function(){
		var ext = $(this).val();
		if(ext == 'YouTube') {
			$("#spn_src").show();
			$("#spn_up").hide();
		} else {
			$("#spn_up").show();
			$("#spn_src").hide();
		}
    });
	$(".local").colorbox({
		iframe:true, width:"660", height:"350"
	});
});
</script>
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_wallboard" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>" enctype="multipart/form-data">
<input type="hidden" name="vid" value="<?php if (isset($vid)) echo $vid;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Wallboard Video Information</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption"><span class="required">*</span> Title:</td>
		<td>
			<input type="text" name="title" size="30" maxlength="20" value="<?php echo $video->title;?>" />
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Source Type:</td>
		<td>
			<?php $this->html_options('source_type', 'source_type', $st_options, $video->source_type);?>
     	</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption"><span class="required">*</span> Source:</td>
		<td>
			<span id="spn_src" style="display:<?php if ($video->source_type != 'YouTube') echo 'none'; else echo 'inline';?>;">
			<input type="text" name="source" id="source" size="30" maxlength="200" value="<?php echo $video->source;?>" />
			</span>
			<span id="spn_up" style="display:<?php if ($video->source_type == 'YouTube') echo 'none'; else echo 'inline';?>;">
			<input type='file' id="video" name="video" />
			</span>

            <?php if (!empty($vid)) {
				$files = glob('wallboard/video/' . 'a' . $vid . '.{' . implode(",", $video_types) . '}', GLOB_BRACE);
				if (!empty($files)) {
					echo '<a class="local" href="' . $this->url('task='.$request->getControllerName()."&act=play-video&vid=".$video->id) . '">Show Existing Video</a>';
				}
			}?>
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" width="33%">Active:</td>
		<td>
			<?php $this->html_options('active', 'active', $yn_options, $video->active);?>
		</td>
	</tr>
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if (!empty($vid)):?>Update<?php else:?>Add<?php endif;?>  " name="submittxt"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>
