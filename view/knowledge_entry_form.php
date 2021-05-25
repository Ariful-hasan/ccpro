<?php $num_select_box = 6;?>
<script src="js/ckeditor/ckeditor.js"></script>
<script src="js/ckeditor/adapters/jquery.js"></script>
<script>
$( document ).ready( function() {
	$( 'textarea#description' ).ckeditor();
	load_sels();
} );

function load_sels()
{
	var num_dids = <?php echo count($kbase_ids);?>;
	if (num_dids == 0) num_dids++;
	show_sels(num_dids);
}

function hide_sels(i)
{
	for (j=i+2; j < <?php echo $num_select_box;?>; j++) {
		$("#kbase_id" + j).hide();
	}
}

function show_sels(i)
{
	for (j=0; j < i; j++) {
		$("#kbase_id" + j).show();
	}
}

function reload_sels(i, val)
{
	if (i < <?php echo $num_select_box;?>) {
		hide_sels(i);
		var j = i+1;
		$select = $("#kbase_id" + j);
		$select.html('<option value="">Select</option>');

		if (val.length > 0) {
			var jqxhr = $.ajax({
				type: "POST",
				url: "<?php echo $this->url("task=knowledge&act=kbasechildren");?>",
				data: { did: val },
				dataType: "json"
			})
			.done(function(data) {
				show_sels(j+1);
				if (data.length == 0) {
					//console.log('0');
					$select.hide();
			 	} else {
					$.each(data, function(key, val){
						$select.append('<option value="' + key + '">' + val + '</option>');
					})
				}
			})
			.fail(function() {
				//
			})
			.always(function() {
			});
		}
	}
}
</script>

<link rel="stylesheet" href="css/form.css" type="text/css">
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_ivr_service" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="did" value="<?php if (isset($dis_code)) echo $dis_code; else $dis_code = '';?>" />
<input type="hidden" name="sid" value="<?php if (isset($sid)) echo $sid;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Knowledge Entry Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" width="33%"><span class="required">*</span> Title:</td>
		<td>
			<input type="text" name="title" size="30" maxlength="50" value="<?php echo $service->title;?>" />
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" valign="top">Text:</td>
		<td>
			<textarea name="description" id="description" style="width:600px;height:200px;"><?php echo $service->description;?></textarea>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Tags:</td>
		<td>
			<input type="text" name="tags" size="30" maxlength="200" value="<?php echo $service->tags;?>" />
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Parent:</td>
		<td>
<?php
			
				for ($i=0; $i<$num_select_box; $i++) {
					echo '<select name="kbase_id'.$i.'" id="kbase_id'.$i.'" style="display:none;" onchange="reload_sels('.$i.', this.value);"><option value="">Select</option>';
					if ($i == 0 || isset($kbase_ids[$i])) {
						if (!isset(${'kbase_id'.$i})) {
							if ($i > 0) {
								$_kbids = $kb_model->getKBaseChildrenOptions($sid, $kbase_ids[$i-1][0]);
//var_dump($dispositions);
							} else {
								$_kbids = $kb_model->getDispositionChildrenOptions($sid, '');
							}
						} else {
							$_kbids = ${'kbase_id'.$i};
						}
					
						foreach ($_kbids as $_dispositionid=>$_title) {
							$_did = isset($kbase_ids[$i]) ? $kbase_ids[$i][0] : '';
							echo '<option value="' . $_dispositionid . '"';
							if ($_did == $_dispositionid) echo ' selected';
							echo '>' . $_title . '</option>';
						}
					}
					echo '</select> ';
				}
				
?>
				</td>
	</tr>
	<tr class="form_row_alt">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if (!empty($dis_code)):?>Update<?php else:?>Add<?php endif;?>  " name="submitservice"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>
