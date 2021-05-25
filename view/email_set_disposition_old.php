<script src="js/jquery.min.js" type="text/javascript"></script>
<link href="css/form.css" rel="stylesheet" type="text/css">
<link href="ccd/select2/select2.min.css" rel="stylesheet" />
<script src="ccd/select2/select2.min.js"></script>
<?php $num_select_box = 6;?>

<script>
$(document).ready(function() {
	load_sels();
    $('.sel2').select2();
});

function load_sels()
{
	var num_dids = <?php echo count($disposition_ids);?>;
	if (num_dids == 0) num_dids++;
	show_sels(num_dids);
/*
	for (var j=0; j < num_dids; j++) {
		$("#"+'disposition_id' + j).show();
	}
	*/
}

function hide_sels(i)
{
	for (j=i+2; j < <?php echo $num_select_box;?>; j++) {
		$("#disposition_id" + j).hide();
	}
}

function show_sels(i)
{
	for (j=0; j < i; j++) {
		$("#disposition_id" + j).show();
	}
}

function reload_sels(i, val)
{
	if (i < <?php echo $num_select_box;?>) {
		hide_sels(i);
		var j = i+1;
		$select = $("#disposition_id" + j);
		$select.html('<option value="">Select</option>');

		if (val.length > 0) {
			var jqxhr = $.ajax({
				type: "POST",
				url: "<?php echo $this->url("task=" . $request->getControllerName() . '&act=dispositionchildren');?>",
				data: { did: val },
				dataType: "json"
			})
			.done(function(data) {
				show_sels(j+1);
				if (data.length == 0) {
					//console.log('0');
					$select.hide();
			 	} else {
					$.each(data, function(key, val){
						$select.append('<option value="' + key + '">' + val + '</option>');
					})
				}
			})
			.fail(function() {
				//
			})
			.always(function() {
			});
		}
	
	
	}
}
</script>
<style>
select {
	margin: 2px;
}
</style>
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_agent" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="tid" value="<?php echo $tid;?>" />
<input type="hidden" name="msl" value="<?php echo !empty($msl)?$msl:"";?>" />
<table class="form_table">
	<tr class="form_row_alt">
		<td class="form_column_caption" style="text-align:center;"><b>Select Disposition:</b>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" style="text-align:center;">
			<?php
            ///// PARENTWISE  DID
            /*
				for ($i=0; $i<$num_select_box; $i++) {
					echo '<select  name="disposition_id'.$i.'" id="disposition_id'.$i.'" style="display:none;" onchange="reload_sels('.$i.', this.value);"><option value="">Select</option>';
					if ($i == 0 || isset($disposition_ids[$i])) {
						if (!isset(${'dispositions'.$i})) {
							if ($i > 0) {
								$dispositions = $eTicket_model->getDispositionChildrenOptions($ticket_info->skill_id, $disposition_ids[$i-1][0]);
//var_dump($dispositions);
							} else {
								$dispositions = $eTicket_model->getDispositionChildrenOptions($ticket_info->skill_id, '');
							}
						} else {
							$dispositions = ${'dispositions'.$i};
						}
					
						foreach ($dispositions as $_dispositionid=>$_title) {
							$did = isset($disposition_ids[$i]) ? $disposition_ids[$i][0] : '';
							echo '<option value="' . $_dispositionid . '"';
							if ($did == $_dispositionid) echo ' selected';
							echo '>' . $_title . '</option>';
						}
					}
					echo '</select>';
				}
                */
			 ?>
            <select name="disposition_id" id="disposition_id">
                <option value="">Select</option>
            </select>
		</td>
	</tr>
<tr class="form_row_alt">
		<td class="form_column_submit" style="text-align:center;padding:20px 0;">
			<input class="form_submit_button" type="submit" value="Save" name="submitagent" />  &nbsp; &nbsp;
            <input class="form_submit_button" type="button" value="Cancel" name="submitcancel" onclick="parent.$.colorbox.close();" />
		</td>
	</tr>
</table>
</form>
