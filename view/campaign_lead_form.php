<link href="css/form.css" rel="stylesheet" type="text/css">

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_lead" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="cid" value="<?php if (!empty($cid)) echo $cid;?>" />
<input type="hidden" name="lid" value="<?php if (!empty($lid)) echo $lid;?>" />

<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Lead Information </td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="33%">Lead:</td>
		<td>
<?php
if (!empty($lid)) {echo '<b>' . $lead_title . '</b><input type="hidden" name="lead_id" value="'.$lead->lead_id.'" />';}
else {
?>
<select name="lead_id" id="lead_id">
<option value="">Select</option>
<?php if (is_array($campaign_leads)) {
	foreach ($campaign_leads as $cl) {
		echo '<option value="' . $cl->lead_id . '"';
		if ($cl->lead_id == $lead->lead_id) echo ' selected';
		echo '>'.$cl->title.'</option>';
	}
}
?>
</select>
<?php
}
?>
		</td>
	</tr>
<!--
	<tr class="form_row_alt">
		<td class="form_column_caption">Priority: </td>
		<td>
<select name="priority" id="priority">
<?php
/*for ($i = 0; $i <= 9; $i++) {
	echo '<option value="' . $i . '"';
	if ($i == $lead->priority) echo ' selected';
	echo '>'.$i.'</option>';
}
*/?>
</select>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Weight: </td>
		<td>
<select name="weight" id="weight">
<?php
/*for ($i = 0; $i <= 9; $i++) {
	echo '<option value="' . $i . '"';
	if ($i == $lead->weight) echo ' selected';
	echo '>'.$i.'</option>';
}
*/?>
</select>

		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Run hour: </td>
		<td><div class="col-xs-12 ">
<?php
/*	$per_row_col = 7;
	for ($i = 0; $i <= 23; $i++) {
		$padd = $i < 10 ? '0' : '';
		if ($i%$per_row_col == 0 && $i != 0) echo '<br>';
		echo '<div class="col-md-2"><input type="checkbox" name="rhour'.$i . '" value="1"';
		if ($lead->run_hour[$i] == '1') echo ' checked="true"';
		echo ' /> ' . $padd . $i . '</div>';
		//if ($i%$per_row_col == 0) echo '</div>';
	}
*/?>
		</div></td>
	</tr>-->
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value=" <?php if (!empty($lid)):?>Update<?php else:?>Add<?php endif;?>  " name="submitlead" /> <br>
		</td>
	</tr>
</tbody>
</table>
</form>
