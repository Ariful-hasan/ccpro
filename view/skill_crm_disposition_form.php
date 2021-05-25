<script src="js/jquery.min.js" type="text/javascript"></script>
<?php $num_select_box = 6;?>
<link rel="stylesheet" href="css/form.css" type="text/css">
<script>
$(document).ready(function() {
	load_sels();
});

function load_sels()
{
	var num_dids = <?php echo count($disposition_ids);?>;
	if (num_dids == 0) num_dids++;
	show_sels(num_dids);
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
		$("#parent_id").val(val);
		
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
<form name="frm_ivr_service" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="sid" value="<?php if (isset($dis_code)) echo $dis_code;?>" />
<input type="hidden" name="tid" value="<?php if (isset($tid)) echo $tid;?>" />
<input type="hidden" name="parent_id" id="parent_id" value="<?php echo $service->parent_id;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Disposition Code Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><span class="required">*</span> Disposition Code:</td>
		<td>
			<input type="text" name="disposition_id" size="30" maxlength="4" value="<?php echo $service->disposition_id;?>" />
			<span><small>Allowed: A-Z & 0-9</small></span>
     	</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="33%"><span class="required">*</span> Disposition Title:</td>
		<td><input type="text" name="title" size="30" maxlength="50" value="<?php echo $service->title;?>" /></td>
	</tr>

    <tr class="form_row">
        <td class="form_column_caption" width="33%"><span class="required">*</span> Disposition Type:</td>
        <td><select name="disposition_type" id="disposition_type">
                <option value="">---Select---</option>
                <option <?php echo $service->disposition_type == 'Q' ? 'selected': '';?> value="Q">Query</option>
                <option <?php echo $service->disposition_type == 'R' ? 'selected': '';?> value="R">Request</option>
                <option <?php echo $service->disposition_type == 'C' ? 'selected': '';?> value="C">Complain</option>
            </select></td>
    </tr>

	<tr class="form_row_alt">
		<td class="form_column_caption" width="33%">Parent Disposition:</td>
		<td>
			
							<?php
			//var_dump($disposition_ids);
				for ($i=0; $i<$num_select_box; $i++) {
					echo '<select name="disposition_id'.$i.'" id="disposition_id'.$i.'" style="display:none;" onchange="reload_sels('.$i.', this.value);"><option value="">Select</option>';
					if ($i == 0 || isset($disposition_ids[$i])) {
						if (!isset(${'dispositions'.$i})) {
							if ($i > 0) {
								$dispositions = $dc_model->getDispositionChildrenOptions($tid, $disposition_ids[$i-1][0]);
//var_dump($dispositions);
							} else {
								$dispositions = $dc_model->getDispositionChildrenOptions($tid, '');
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
				
			?>
				
				<?php
				/*
					if (is_array($groups)) {
						foreach ($groups as $key=>$group) {
							echo '<option value="' . $key . '"';
							if ($service->group_id == $key) echo ' selected';
							echo '>'.$group.'</option>';
						}
					}
				*/
				?>
			
		</td>
	</tr>
    <tr class="form_row" id="partition-container">
        <td class="form_column_caption" width="33%">Partition: </td>
        <td>
            <select name="partition_id" id="partition_id">
                <?php echo GetHTMLOption("","---Select---",$service->partition_id); ?>
                <?php echo GetHTMLOptionByArray($partitions,$service->partition_id); ?>
            </select>
        </td>
    </tr>
    <tr class="form_row">
        <td class="form_column_caption" width="33%">Alternative Disposition: </td>
        <td>
            <input type="text" name="altr_disposition_id" placeholder="Alternative disposition ID " size="30" maxlength="10" value="<?php echo $service->altr_disposition_id;?>" />
        </td>
    </tr>

	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if (!empty($dis_code)):?>Update<?php else:?>Add<?php endif;?>  " name="submitservice"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>


    <script>
        $(function () {
            $(document).on("change","#disposition_id0",function () {
                var parent = $(this).val();
                controlPartitionForm(parent)
            });
        });
        
        function controlPartitionForm(parent) {
            (parent != "undefined" && parent.length > 0) ? $("#partition-container").hide(500) : $("#partition-container").show(500);
        }
    </script>
