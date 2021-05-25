<form name='chatTemplateForm'>
<table class="form_table" width='600' cellspacing="0" cellpadding="6" border="0" align="center">
<tbody>
		<tr class="form_row_head" width='100'>
			<td colspan="2">Select Template</td>
		</tr>
<?php

$i=0;
foreach($templates as $key=>$obj) {
$i++;
?>
<tr><td width='50' align='center'><?php echo $i; ?></td><td><a href='#' onclick="selectTemplate('<?php echo $user_id; ?>','<?php echo $key; ?>')"><?php echo $obj->title; ?></a></td></tr>
<?php
}
?>
</tbody>
</table>
</form>
<script>
var obj = <?php echo json_encode($templates); ?>;
// console.log(obj);
function selectTemplate(user_id, key) {
	// console.log(key);
	// console.log(obj[key].message);
	// console.log(user_id+"|"+obj[key].message);
	//$("#chatbox_"+user_id).find('textarea').val(msg);
	parent.closeTemplateBox(user_id,obj[key].message);
}
</script>

