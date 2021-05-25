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
	$fname_options = array('' => 'Select', 'DT'=>'Date time', 'AI'=>'Agent ID', 'CL'=>'Caller ID', 'DD'=>'DID');
	$fname1 = 'DT';
	$fname2 = 'AI';
	$fname3 = 'CL';
	$fname4 = 'DD';
?>
<b>Total record(s) found: <?php echo $num_records;?></b><br /><br />
<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_disposition" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="skillid" value="<?php echo $skillid;?>" />
<input type="hidden" name="cli" value="<?php echo $cli;?>" />
<input type="hidden" name="did" value="<?php echo $did;?>" />
<input type="hidden" name="aid" value="<?php echo $aid;?>" />
<input type="hidden" name="sdate" value="<?php echo $dateinfo->sdate;?>" />
<input type="hidden" name="edate" value="<?php echo $dateinfo->edate;?>" />
<input type="hidden" name="stime" value="<?php echo $dateinfo->stime;?>" />
<input type="hidden" name="etime" value="<?php echo $dateinfo->etime;?>" />

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
            <input class="form_submit_button" type="button" value="Close" onClick="parent.$.colorbox.close();" />
            <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>
