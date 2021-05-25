<link href="css/report.css" rel="stylesheet" type="text/css">
<style type="text/css">
	.report_table tr.report_row_head td {
		background-image: -webkit-linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
		background-image: -moz-linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
		background-image: -o-linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
		background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#fff), color-stop(0.25, #fff), to(#e6e6e6)) !important;
		background-image: linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
		color: #333333;
		box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05) !important;
		-webkit-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05) !important;
		-moz-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05) !important;
		border-bottom: 1px solid #ccc !important;
		border-top: 0 none;
		padding: 0.6em 0.5em;
		vertical-align: middle;
		text-align: center;
		font-size: 12px;
	}
	.table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
		padding: 5px;
		font-size: 12px;
	}
</style>
<script type="text/javascript">
function deleteNumber(bid, num1)
{
	if (bid.length >= 4) {
		var ctext = 'Are you sure to delete number : ' + num1 + '?';
		if (confirm(ctext)) {
			window.location = "<?php echo $this->url('task='.$request->getControllerName()."&act=delnumber&page=".$pagination->current_page."&lid=");?>" + bid + "&num="+num1;
		}
	}
	return false;
}
</script>
<?php
$colspan = 14;
//var_dump($skills_out);
?>

<?php if (is_array($numbers)):?>
<table class="report_extra_info">
<tr>
	<td>
		<?php
        	echo 'Record(s) ' . $pagination->getCurrentRecordsIndex() . ' of <b>' . $pagination->num_records . '</b> &nbsp;::&nbsp; ' . 
				'Page <b>' . $pagination->current_page . '</b> of <b>' . $pagination->getTotalPageCount() . '</b>';
		?>
	</td>
</tr>
</table>
<?php endif;?>
<?php 
$columns = array();
if (!empty($leadinfo->custom_label_1)) $columns['custom_value_1'] = $leadinfo->custom_label_1;
if (!empty($leadinfo->custom_label_2)) $columns['custom_value_2'] = $leadinfo->custom_label_2;
if (!empty($leadinfo->custom_label_3)) $columns['custom_value_3'] = $leadinfo->custom_label_3;
if (!empty($leadinfo->custom_label_4)) $columns['custom_value_4'] = $leadinfo->custom_label_4;

$colspan = $colspan + count($columns);
//var_dump($leadinfo);
?>
<table class="report_table" width="80%" border="0" align="center" cellpadding="1" cellspacing="1">
<tr class="report_row_head">
	<td class="cntr">SL</td>
	<td>Customer ID</td>
	<td>Number 1</td>
	<td>Number 2</td>
	<td>Number 3</td>
	<td>Number 4</td>
	<td>Title</td>
	<td>FName</td>
	<td>LName</td>
	<td>Street</td>
	<td>City</td>
	<td>State</td>
	<td>Zip</td>
<?php foreach ($columns as $key=>$val) {
	echo '<td>'.$val.'</td>';
} ?>
	<td>Agent Alt. ID</td>
	<td class="cntr">Action</td>
<?php /*	<td>Audio</td> */ ?>
</tr>

<?php if (is_array($numbers)):?>

<?php
	$i = $pagination->getOffset();
	foreach ($numbers as $num):
		$i++;
		$_class = $i%2 == 1 ? 'report_row_alt' : 'report_row';
?>
<tr class="<?php echo $_class;?>">
	<td class="cntr">&nbsp;<?php echo $i;?></td>
	<td align="left">&nbsp;<?php echo $num->customer_id;?></td>
	<td align="left">&nbsp;<?php echo $num->dial_number;?></td>
	<td align="left">&nbsp;<?php echo $num->dial_number_2;?></td>
	<td align="left">&nbsp;<?php echo $num->dial_number_3;?></td>
	<td align="left">&nbsp;<?php echo $num->dial_number_4;?></td>
	<td align="left">&nbsp;<?php echo $num->title;?></td>
	<td align="left">&nbsp;<?php echo $num->first_name;?></td>
	<td align="left">&nbsp;<?php echo $num->last_name;?></td>
	<td align="left">&nbsp;<?php echo $num->street;?></td>
	<td align="left">&nbsp;<?php echo $num->city;?></td>
	<td align="left">&nbsp;<?php echo $num->state;?></td>
	<td align="left">&nbsp;<?php echo $num->zip;?></td>
<?php foreach ($columns as $key=>$val) {
	echo '<td align="left">&nbsp;'.$num->{$key}.'</td>';
} ?>
	<td align="left">&nbsp;<?php echo $num->agent_altid;?></td>
	<td class="cntr" style="text-align: center;">&nbsp;<?php
		//if (empty($num->callid)) {
		if (1) {
	?>
	<a href="#" onClick="return deleteNumber('<?php echo $lid;?>','<?php echo $num->dial_number;?>');"><img src="image/cancel.png" class="bottom" border="0" width="14" height="14" title="Delete" /></a>
	<?php } ?>
    </td>
</tr>
<?php endforeach;?>
</table>
<table class="report_extra_info">
<tr>
	<td><?php echo $pagination->createLinks();?></td>
</tr>
<?php else:?>
<tr class="report_row_empty">
	<td colspan="<?php echo $colspan;?>" style="font-weight: normal;">No number found!</td>
</tr>
<?php endif;?>
</table>

<script type="text/javascript">
	var luser = '<?php echo UserAuth::getCurrentUser();?>';
</script>