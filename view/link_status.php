<link rel="stylesheet" type="text/css" media="screen" href="css/report.css">
<table class="report_table" width="50%" border="0" align="center" cellpadding="1" cellspacing="1">
<tr class="report_row_head">
	<td>Label</td>
	<td class="cntr">Status</td>
</tr>

<?php if (is_array($link_status_array)):?>

<?php
	$i = 0;
	$total_st = count($link_status_array);
	foreach ($link_status_array as $status):
		$st_label = '';
		$st = '';
		$st_array = explode(':', $status);
		if (is_array($st_array)) {
			$st_label = $st_array[0];
			$st = isset($st_array[1]) ? $st_array[1] : '';
		}
		$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
		if ($i == $total_st) {
			$cmp = 'IN';
		} else {
			$cmp = 'GR';
		}
		$fontcolor = substr($st, 0, 2) == $cmp ? 'green' : '#B90000';
?>
<tr class="<?php echo $_class;?>">
	<td align="left">&nbsp;<?php echo $st_label;?></td>
	<td class="cntr">&nbsp;<font color="<?php echo $fontcolor;?>"><b><?php echo $st;?></b></font></td>
</tr>
<?php endforeach;?>
<?php else:?>
<tr class="report_row_empty">
	<td colspan="2">No Status Found!</td>
</tr>
<?php endif;?>
</table>
