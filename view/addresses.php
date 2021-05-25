<link href="css/report.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
function delAddress(aid, br_address)
{
	if (aid.length >= 1) {
		if (confirm('Are you sure to delete address : ' + br_address + '?')) {
			window.location = "<?php echo $this->url('task='.$request->getControllerName()."&act=del&page=".$pagination->current_page."&aid=");?>" + aid;
		}
	}
}
</script>

<?php if (is_array($addresses)):?>
<table class="report_extra_info">
<tr>
	<td align="left">
		<?php
        	echo 'Record(s) ' . $pagination->getCurrentRecordsIndex() . ' of <b>' . $pagination->num_records . '</b> &nbsp;::&nbsp; ' . 
				'Page <b>' . $pagination->current_page . '</b> of <b>' . $pagination->getTotalPageCount() . '</b>';
		?>
	</td>
</tr>
</table>
<?php endif;?>

<table class="report_table" width="80%" border="0" align="center" cellpadding="1" cellspacing="1">
<tr class="report_row_head">
	<td>SL</td>
	<td>Branch Name</td>
	<td>Address</td>
	<td>Latitude</td>
	<td>Longitude</td>
	<td>Action</td>
</tr>

<?php if (is_array($addresses)):?>

<?php
	$i = $pagination->getOffset();
	foreach ($addresses as $_address):
		$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
?>
<tr class="<?php echo $_class;?>">
	<td align="center">&nbsp;<?php echo $i;?></td>
	<td align="left">
		<a href="<?php echo $this->url('task='.$request->getControllerName()."&act=update&aid=".$_address->address_id);?>"><?php echo $_address->branch_name;?></a>
	</td>
	<td align="left"><?php echo nl2br($_address->address);?></td>
	<td><?php echo $_address->latitude;?></td>
	<td><?php echo $_address->longitude;?></td>
	<td class="report_row_del cntr"><a href="#" onclick="delAddress('<?php echo $_address->address_id;?>', '<?php echo $_address->branch_name;?>');"><img src="image/cancel.png" class="bottom" border="0" width="14" height="14" /></a>
	</td>
</tr>
<?php endforeach;?>
</table>
<table class="report_extra_info">
<tr>
	<td align="left"><?php echo $pagination->createLinks();?></td>
</tr>
<?php else:?>
<tr class="report_row_empty">
	<td colspan="6">No Address Found!</td>
</tr>
<?php endif;?>
</table>
