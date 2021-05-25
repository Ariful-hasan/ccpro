<script src="js/jquery.min.js" type="text/javascript"></script>
<link rel="stylesheet" href="css/form.css" type="text/css">

<?php if ($readonly):?>
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Disposition</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Disposition:</td>
		<td>
			Test
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" width="33%">Note:</td>
		<td>
			This is test text
		</td>
	</tr>
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<a href="#" class="btn" onclick="parent.$.colorbox.close();">Close</a>
		</td>
	</tr>
</tbody>
</table>
<?php else:?>
<script type="text/javascript">
$(document).ready(function() {
	
	$("#set_disposition").click(function (e) {
		e.stopPropagation();
		var disposition = $("#disposition").val();
		if (disposition.length > 0) {
			$.ajax({
				type: "POST",
				url: "<?php echo $this->url('task=crm_in&act=savedisposition&cid='.$cid);?>",
				data: { disposition: disposition, callid: '<?php $cid;?>', note: $("#comment").val() }
			}).done(function( msg ) {
				window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&act=add-disposition&readonly=1&cid=".$cid);?>";
			});
		} else {
			alert('Select disposition !!');
		}
	});
	
	adjust_disposition();
});
</script>


<script>
var dispositions = [<?php
if (is_array($dp_options)) {
	$i = 0;
	foreach ($dp_options as $row) {
		if ($i>0) echo ',';
		echo '{di:"'.$row->disposition_id.'", gi:"'.$row->group_id.'", t:"'.$row->title.'"}';
		$i++;
	}
}
?>];
function adjust_disposition()
{
	var len = dispositions.length;
	var dgroup = document.getElementById('dgroup').value;
	var targetBox = document.getElementById('disposition');
	targetBox.options.length = 0;
	$('#disposition')
		.append($("<option></option>")
		.attr("value",'')
         .text('Select'));
	for(var i = 0; i < len; i++) {
		var obj = dispositions[i];
		if (dgroup == obj.gi) {
			console.log(dgroup+obj.di);
			$('#disposition')
				.append($("<option></option>")
				.attr("value",obj.di)
		         .text(obj.t));
		}
	}
}
</script>
<label class="act-head">Set Disposition:</label><br /><br />
<div>Group: </div>
<select name="dgroup" id="dgroup" onchange="adjust_disposition();" style="width:90%;">
<?php
if (is_array($group_options)) {
	foreach ($group_options as $key=>$val) {
		echo '<option value="'.$key.'">'.$val.'</option>';
	}
}
?>
</select> 

<div style="margin-top:5px;">Disposition</div>
<select name="disposition" id="disposition" style="width:90%;">
	<option value="">Select</option>
</select> 

<br /><br /><label class="act-head">Note:</label><br /><br />
<textarea name="note" id="comment" maxlength="255" style="width:90%; height:140px;"></textarea>
<br /><br /><div style="text-align:center;"><input type="button" class="btn btn-info" id="set_disposition" name="set_disposition" value="Save" /></div>
<?php endif;?>