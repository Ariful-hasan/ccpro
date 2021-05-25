<link rel="stylesheet" href="css/form.css" type="text/css">
<style type="text/css">
.popup-body .form_row_alt {
    border-width: 1px;
}
</style>
<?php if (isset($redirectURL) && !empty($redirectURL)): ?>
<script type="text/javascript">
	var redirectUrl = "<?php echo $redirectURL; ?>";
	$(document).ready(function() {
		setTimeout("parent.location.reload();", 1000);
	});
</script>
<?php endif; ?>
<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_ivr_service" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="seat" value="<?php if (isset($seat)) echo $seat;?>" />
<input type="hidden" name="unit" value="<?php if (isset($unit)) echo $unit;?>" />
<input type="hidden" name="bkey" value="<?php if (isset($bkey)) echo $bkey;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_alt">
		<td class="form_column_caption" width="20%"><span class="required">*</span> Key:</td>
		<td width="80%">
			<select id="key_id" name="key_id">
      		<?php 
  		      for ($i=1; $i<41; $i++){
  		          $isSelected = "";
  		          if($service->key_id != $i && in_array($i, $keylist)) continue;
  		          if($service->key_id == $i) $isSelected = "selected='selected'";
  		          echo "<option value='$i' $isSelected>$i</option>";
  		      }
      		?>		      		
      		</select>
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><span class="required">*</span> Title:</td>
		<td>
			<input type="text" name="label" size="30" maxlength="12" value="<?php echo $service->label;?>" />
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><span class="required">*</span> Monitor:</td>
		<td>
			<input type="text" name="monitor" size="30" maxlength="4" value="<?php echo $service->monitor;?>" />
     	</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Type:</td>
		<td>
			<select id="type" name="type">
			    <option value="SPD" <?php if ($service->type == 'SPD') echo 'selected="selected"';?>>Speed Dial</option>
			</select>
		</td>
	</tr>

	<tr class="form_row_alt">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if (!empty($service_id)):?>Update<?php else:?>Add<?php endif;?>  " name="submitservice"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>
