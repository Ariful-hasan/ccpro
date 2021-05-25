<script type="text/javascript">
function closeWindow() {
	<?php if (isset($refreshParent)):?>
	window.opener.location.href = window.opener.location.href;
	if (window.opener.progressWindow) {
		window.opener.progressWindow.close();
	}
	<?php endif;?>
	window.close();
}
</script>

<?php
	$fname_options = array('' => 'Select', 'DT'=>'Date time', 'CM'=>'Campaign', 'LD'=>'Lead', 'DN'=>'Dial number');
	$fname1 = 'DT';
	$fname2 = 'CM';
	$fname3 = 'LD';
	$fname4 = 'DN';
?>
<b>Total record(s) found: <?php echo $num_records;?></b><br /><br />
<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_disposition" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="dial_num" value="<?php echo $dial_num;?>" />
<input type="hidden" name="camid" value="<?php echo $camid;?>" />
<input type="hidden" name="leadid" value="<?php echo $leadid;?>" />
<input type="hidden" name="disp_code" value="<?php echo $disp_code;?>" />

<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row">
		<td align="center"><br /><b>File name:</b>

			<select name="fname1" id="fname1">
			<?php
			if (is_array($fname_options)) {
				foreach ($fname_options as $key=>$val) {
					echo '<option value="'.$key.'"';
					if ($key == $fname1) echo ' selected';
					echo '>'.$val.'</option>';
				}
			}
			?>
			</select> - 
            <select name="fname2" id="fname2">
			<?php
			if (is_array($fname_options)) {
				foreach ($fname_options as $key=>$val) {
					echo '<option value="'.$key.'"';
					if ($key == $fname2) echo ' selected';
					echo '>'.$val.'</option>';
				}
			}
			?>
			</select> - 
            <select name="fname3" id="fname3">
			<?php
			if (is_array($fname_options)) {
				foreach ($fname_options as $key=>$val) {
					echo '<option value="'.$key.'"';
					if ($key == $fname3) echo ' selected';
					echo '>'.$val.'</option>';
				}
			}
			?>
			</select> - 
            <select name="fname4" id="fname4">
			<?php
			if (is_array($fname_options)) {
				foreach ($fname_options as $key=>$val) {
					echo '<option value="'.$key.'"';
					if ($key == $fname4) echo ' selected';
					echo '>'.$val.'</option>';
				}
			}
			?>
			</select>
<br /><br />
		</td>
	</tr>
    

	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="Download voice files" name="submitservice">  &nbsp; &nbsp;
            <input type="button" class="form_submit_button" value="Close" onClick="parent.$.colorbox.close();" />
            <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>
