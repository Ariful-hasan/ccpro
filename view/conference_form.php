<link rel="stylesheet" href="css/form.css" type="text/css">
<link rel="stylesheet" href="js/lightbox/css/colorbox.css" />
<script src="js/jquery.min.js"></script>
<script src="js/lightbox/js/jquery.colorbox-min.js"></script>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$(".agents").colorbox({
		iframe:true, width:"800", height:"620"
	});
});

</script>

<style>
div.agsel {
	width: 200px;
	display:block;
	float:left;
	padding: 3px;
	border: 1px solid #C8C8C8;
	border-radius: 4px 4px 4px 4px;
	margin-left: 2px;
	margin-top: 10px;
}
span.xExt {
float:right;font-weight:bold;color:brown;cursor:pointer;
}
</style>

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_conf" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="cid" value="<?php if (isset($confid)) echo $confid;?>" />

<table class="form_table">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Conference Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" width="20%"><b>Conference number:</b></td>
		<td>
		<?php if (empty($confid)):?>
			<input type="text" disabled value="#50" size="2" style="border-right:0; border-radius: 4px 0 0 4px;" /><input type="text" name="bridge_number" size="5" maxlength="3" value="<?php echo $conference->bridge_number;?>" style="border-left:0; border-radius: 0 4px 4px 0;" />
		<?php else:?>
			<b><?php echo $conference->bridge_number;?></b><input type="hidden" name="bridge_number" value="<?php echo $conference->bridge_number;?>" />
		<?php endif;?>
     	</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Title:</td>
		<td>
			<input type="text" name="title" size="30" maxlength="30" value="<?php echo $conference->title;?>" />
		</td>
	</tr>

	<tr class="form_row_alt">
		<td class="form_column_caption">Agent(s):</td>
		<td>
			<input type="hidden" id="agents" name="agents" value="<?php echo $conference->agents;?>" />
            <a class="btn-link agents" href="<?php echo $this->url('task='.$request->getControllerName()."&act=selectagent");?>">Select agents from list</a>
			<div id="div_agent"></div>
			<script>
			var available_agents = new Array();
			<?php if (is_array($agent_options)):?>
			<?php foreach ($agent_options as $ag):?>
			available_agents['<?php echo $ag->agent_id;?>'] = '<?php echo '[' . $ag->agent_id . '] ' . $ag->nick . ' - '; if ($ag->busy_status == 'A') echo 'Available'; else if ($ag->busy_status == 'B') echo 'Busy'; else if (empty($ag->busy_status)) echo 'Unavailable';  ?>';
			<?php endforeach;?>
			<?php endif;?>
            function display_agents(ags_selected)
			{
				document.getElementById('agents').value = ags_selected;
				$("#div_agent").empty();
				var ags = ags_selected.split(',');
				for(var key in ags) {
					//alert(ags[key]);
					if (ags[key].length > 0) {
						var agname = (typeof available_agents[ags[key]]=='undefined') ? ags[key] : available_agents[ags[key]];
						$("#div_agent").append('<div class="agsel" id="a' + ags[key] + '">' + agname + '</div>');
					}
				}
			}
			
            </script>
		</td>
	</tr>

	<tr class="form_row">
		<td class="form_column_caption">External number(s):</td>
		<td><?php 
			$exts_selected = explode(',', $conference->ext_numbers);
			$pre_populated_str = '';
			$j = 0;
			foreach ($exts_selected as $exts) {
				if (!empty($exts)) {
				if ($j > 0) $pre_populated_str .= ',';
				$pre_populated_str .= "{id: $exts, name:\"$exts\"}";
				$j++;
				}
			}
		?>
			<input type="text" name="ext_num" id="ext_num" size="30" maxlength="15" value="" /> <input type="button" id="add_ext_num" class="btn btn-small btn-info" value="Add" />
			<input type="hidden" id="ext_numbers" name="ext_numbers" value="<?php echo $conference->ext_numbers;?>" />
            <div id="div_num"></div>
			<script type="text/javascript">
	        function display_ext_nums(nums) {
				document.getElementById('ext_numbers').value = nums;
				$("#div_num").empty();
				var ags = nums.split(',');
				for(var key in ags) {
					//alert(ags[key]);
					if (ags[key].length > 0) {
						$("#div_num").append('<div class="agsel" id="n' + ags[key] + '">' + ags[key] + '<span class="xExt" onclick="return delExn(\''+ags[key]+'\');">X</span></div>');
					}
				}
			}
			
			function delExn(extn)
			{
				if (confirm("Are you sure to delete external number " + extn + "?")) {
					var nums = document.getElementById('ext_numbers').value;
					var ags = nums.split(',');
					var new_nums = '';
					var i = 0;
					for(var key in ags) {
						if (ags[key].length > 0) {
							if (ags[key] != extn) {
								if (i > 0) new_nums = new_nums + ',';
								new_nums = new_nums + ags[key];
								i++;
							}
						}
					}
					display_ext_nums(new_nums);
				}
				return false;
			}
			
			$(document).ready(function() {
				$("#add_ext_num").click(function () {
					var extnum = $("#ext_num").val();
					$("#ext_num").val('');
					
					if (extnum.length > 0) {
						var priv_val = document.getElementById('ext_numbers').value;
						var ags = priv_val.split(',');
						var j  = 0;
						for(var key in ags) {
							if (ags[key].length > 0) {
								j++;
								if (extnum == ags[key]) {
									alert('Number ' + extnum + ' already exist');
									return false;
								}
							}
						}
						if (j > 5) {
							alert('No. of external dial(s) must be less than 6');
							return false;
						}
						if (priv_val.length > 0) extnum = ',' + extnum;
						display_ext_nums(priv_val + extnum);
					}
                	return false;
	            });
	        });
	        </script>
		</td>
	</tr>

	<tr class="form_row_alt">
		<td class="form_column_caption">Delay dial:</td>
		<td>
			<input type="text" name="delay_ext_dial" size="30" maxlength="3" value="<?php echo $conference->delay_ext_dial;?>" /> <small>(sec)</small>
		</td>
	</tr>

	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  Save Conference  " name="save_conf" /> &nbsp; &nbsp; &nbsp;
			<?php if (!empty($confid)):?>
			<?php if ($conference->active != 'Y'):?>
			<input class="form_submit_button" type="submit" value="  Start Conference  " name="start_conf" />
			<?php else:?>
			<input class="form_submit_button" type="submit" value="  Stop Conference  " name="stop_conf" />
			<?php endif;?>
			<?php endif;?>
            <br><br>
            <input type="hidden" name="active" value="<?php echo $conference->active;?>" />
		</td>
	</tr>
</tbody>
</table>
</form>
<script>
display_agents('<?php echo $conference->agents;?>');
display_ext_nums('<?php echo $conference->ext_numbers;?>');
</script>