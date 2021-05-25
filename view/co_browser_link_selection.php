<form name='coBrowserLinkForm'>
	<table class="form_table" width='600' cellspacing="0" cellpadding="6" border="0" align="center">
		<tbody>
		<tr class="form_row_head" width='100'>
			<td colspan="2">Select Link</td>
		</tr>
		<?php
		$i=0;
		foreach($links as $key=>$obj) {
		$i++;
		?>
			<tr>
				<td width='50' align='center'>
					<?php echo $i; ?>			
				</td>
				<td><a href='#' onclick="selectLink('<?php echo $user_id; ?>','<?php echo $user_name; ?>','<?php echo $key; ?>')"><?php echo $obj->title."(".$obj->link_name.")"; ?></a></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</form>

<script>
var obj = <?php echo json_encode($links); ?>;
console.log(obj);
function selectLink(user_id, user_name, key) {
	console.log(key);
	console.log(obj[key].link_name);
	// console.log(user_id+"|"+obj[key].message);
	//$("#chatbox_"+user_id).find('textarea').val(msg);
	parent.closeCoBrowserLinkBox(user_id, user_name, obj[key].link_name);
}
</script>

