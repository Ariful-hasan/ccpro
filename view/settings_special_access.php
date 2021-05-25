<link rel="stylesheet" href="css/report.css" type="text/css">

<?php if (is_array($special_access)):?>
<table class="report_extra_info">
<tr>
	<td>
		<?php
        	echo 'Record(s): ' . '<b>' . $num_current_records . '</b>';
		?>
	</td>
</tr>
</table>
<?php endif;?>
<table class="report_table" width="60%" border="0" align="center" cellpadding="1" cellspacing="1">
<tr class="report_row_head">
	<td class="cntr">SL</td>
	<td>Page</td>
	<td class="cntr">Password</td>
</tr>

<?php if (is_array($special_access)):?>

<?php
	$i = 0;
	foreach ($special_access as $saccess):
		$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
?>
<tr class="<?php echo $_class;?>">
	<td class="cntr">&nbsp;<?php echo $i;?></td>
	<td align="left">&nbsp;
		<?php echo $saccess->page;?>
	</td>
	<td class="cntr">&nbsp;<a href="<?php echo $this->url('task='.$request->getControllerName()."&act=update&pageid=".$saccess->page);?>">******</a></td>
</tr>
<?php endforeach;?>
<?php else:?>
<tr class="report_row_empty">
	<td colspan="3">No Page Found!</td>
</tr>
<?php endif;?>
</table>
