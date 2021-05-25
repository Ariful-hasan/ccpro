<link href="css/form.css" rel="stylesheet" type="text/css">
<link href="css/report.css" rel="stylesheet" type="text/css">
<script>
var winHandle;
function readFile()
{
	var param= document.getElementById('fl_announcement').value;
	var ext = param.substr(-3);
	if(ext == 'wav' || ext == 'gsm' || ext == '729'){
		return true;
	} else {
		alert("Invalid file type provided");
		document.getElementById('fl_announcement').value='';
		return false;
	}
}
function showColumnDef(val)
{
	if (val=="csv") document.getElementById('tbl_csv_def').style.display = 'block';
	else document.getElementById('tbl_csv_def').style.display = 'none';
}


function adjust_display()
{
	var param = document.getElementById('dial_engine').value;
	document.getElementById('tr_ann_fl').style.display = 'none';
	document.getElementById('tr_pc_ratio').style.display = 'none';
	document.getElementById('tr_dp_ratio').style.display = 'none';
	document.getElementById('tr_skill').style.display = 'none';
	document.getElementById('sp_skill').style.display = 'none';
	document.getElementById('sp_ivr').style.display = 'none';
	
	if (param == 'PG' || param == 'PD' || param == 'RS' || param == 'AN' || param == 'AA') {
		document.getElementById('tr_skill').style.display = 'table-row';
		document.getElementById('sp_skill').style.display = 'inline';
		document.getElementById('skill_id').style.display = 'inline';
		document.getElementById('ivr_id').style.display = 'none';
		document.getElementById('tr_agent_id').className = 'form_row';
		document.getElementById('tr_max_ob_calls').className = 'form_row_alt';
	} else if (param == 'SR') {
		document.getElementById('tr_skill').style.display = 'table-row';
		document.getElementById('sp_ivr').style.display = 'inline';
		document.getElementById('skill_id').style.display = 'none';
		document.getElementById('ivr_id').style.display = 'inline';
	} else {
		document.getElementById('tr_agent_id').className = 'form_row_alt';
		document.getElementById('tr_max_ob_calls').className = 'form_row';
	}
	/*
	if (param == 'AN' || param == 'AA') {
		document.getElementById('tr_ann_fl').className = 'form_row';
	} else {
		document.getElementById('tr_ann_fl').className = 'form_row_alt';
	}
	*/
	document.getElementById('tr_ann_fl').className = 'form_row_alt';
	
	if (param == 'RS' || param == 'AN' || param == 'AA') {
		document.getElementById('tr_ann_fl').style.display = 'table-row';
	}

	if (param == 'PD' || param == 'RS') {
		document.getElementById('tr_pc_ratio').style.display = 'table-row';
		document.getElementById('tr_dp_ratio').style.display = 'table-row';
	}
}
</script>

<style type="text/css">
.ld_title, .ld_prio, .ld_rhour, .ld_st, .ld_weight, .ld_del {
 display:inline-block;
 float:left;
}
.ld_title {
	width: 22%;
}
.ld_prio, .ld_weight {
	width: 10%;
}
.ld_rhour {
	width: 45%;
}
.ld_st {
	width: 10%;
}
.label-right {
	width: 80px !important;
}

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
}
.table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
	padding: 5px;
}
</style>
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_campaign" enctype="multipart/form-data" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="cid" value="<?php if (!empty($cid)) echo $cid;?>" />
<input type="hidden" name="max_out_bound_calls" size="30" maxlength="3" value="<?php echo $campaign->max_out_bound_calls;?>" />
<input type="hidden" name="max_drop_rate" size="30" maxlength="5" value="<?php echo $campaign->max_drop_rate;?>" />
<input type="hidden" name="retry_interval_drop" size="30" maxlength="4" value="<?php echo $campaign->retry_interval_drop;?>" />
<input type="hidden" name="retry_interval_noanswer" size="30" maxlength="4" value="<?php echo $campaign->retry_interval_noanswer;?>" />
<input type="hidden" name="retry_interval_unreachable" size="30" maxlength="4" value="<?php echo $campaign->retry_interval_unreachable;?>" />

<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Campaign Information </td>
	</tr>
<?php if (!empty($cid)):?>
	<tr class="form_row">
		<td class="form_column_caption" width="33%">Campaign ID:</td>
		<td>
			<?php echo $cid;?>
		</td>
	</tr>
<?php endif;?>
	<tr class="form_row_alt">
		<td class="form_column_caption">Title: </td>
		<td>
			<input type="text" name="title" size="30" maxlength="25" value="<?php echo $campaign->title;?>" />
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Primary Language:</td>
		<td>
			<select name="primary_language">
				<?php foreach ($languages as $language){ ?>
					<option value="<?php echo $language->lang_key;?>"<?php if ($language->lang_key==$campaign->primary_language) echo ' selected'; ?>><?php echo $language->lang_title;?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Dial Engine: </td>
		<td>
			<select name="dial_engine" id="dial_engine" onchange="adjust_display();">
				<option value="">Select</option>
				<?php
					if (is_array($dial_engine_options)) {
						foreach ($dial_engine_options as $key=>$value) {
							echo '<option value="'.$key.'"';
							if ($key == $campaign->dial_engine) echo ' selected';
							echo '>'.$value.'</option>';
						}
					}
				?>
			</select>
		</td>
	</tr>
	<tr class="form_row_alt" id="tr_skill">
		<td class="form_column_caption"><span id="sp_skill">Skill:</span><span id="sp_ivr"></span> </td>
		<td>
			<?php
				if ($campaign->dial_engine == 'SR') {
					$ivr_val = $campaign->skill_id;
					$skill_val = '';
				} else {
					$skill_val = $campaign->skill_id;
					$ivr_val = '';
				}
			?>
			<select name="skill_id" id="skill_id">
				<option value="">Select</option>
				<?php
					if (is_array($skill_options)) {
						foreach ($skill_options as $key=>$value) {
							echo '<option value="'.$key.'"';
							if ($key == $skill_val) echo ' selected';
							echo '>'.$value.'</option>';
						}
					}
				?>
			</select>
			<?php /*
            <select name="ivr_id" id="ivr_id">
				<option value="">Select</option>
				<?php
					if (is_array($ivr_options)) {
						foreach ($ivr_options as $key=>$value) {
							echo '<option value="'.$key.'"';
							if ($key == $ivr_val) echo ' selected';
							echo '>'.$value.'</option>';
						}
					}
				?>
			</select>
			
			*/ ?>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Caller ID: </td>
		<td>
			<select name="cli" id="cli">
				<option value="">Select</option>
				<?php
					if (is_array($did_options)) {
						foreach ($did_options as $value) {
							echo '<option value="'.$value.'"';
							if ($value == $campaign->cli) echo ' selected';
							echo '>'.$value.'</option>';
						}
					}
				?>
			</select>
		</td>
	</tr>
	<?php /*
	<tr class="form_row" id="tr_agent_id">
		<td class="form_column_caption">Agent ID: </td>
		<td>
			<input type="text" name="agent_id" size="30" maxlength="4" value="<?php echo $campaign->agent_id;?>" />
		</td>
	</tr>
	*/
	/*
	?>
	<tr class="form_row_alt" id="tr_max_ob_calls">
		<td class="form_column_caption">MAX outbound call(s): </td>
		<td>
			<input type="text" name="max_out_bound_calls" size="30" maxlength="3" value="<?php echo $campaign->max_out_bound_calls;?>" />
		</td>
	</tr>
	*/
	?>
	<tr class="form_row" id="tr_pc_ratio">
		<td class="form_column_caption">MAX pacing ratio: </td>
		<td>
			<input type="text" name="max_pacing_ratio" size="30" maxlength="5" value="<?php echo $campaign->max_pacing_ratio;?>" />
		</td>
	</tr>
	<?php /*
	<tr class="form_row_alt" id="tr_dp_ratio">
		<td class="form_column_caption">MAX drop ratio: </td>
		<td>
			<input type="text" name="max_drop_rate" size="30" maxlength="5" value="<?php echo $campaign->max_drop_rate;?>" />
		</td>
	</tr>
	<tr class="form_row_alt" id="tr_max_callp_agent">
		<td class="form_column_caption">Max call per agent: </td>
		<td>
			<input type="text" name="max_call_per_agent" size="30" maxlength="4" value="<?php echo $campaign->max_call_per_agent;?>" />
		</td>
	</tr>
	<tr class="form_row" id="tr_drop_call_act">
		<td class="form_column_caption">Action on call drop: </td>
		<td>
			<select name="drop_call_action" id="drop_call_action">
				<option value="">Select</option>
				<?php
				if (is_array($call_drop_opt)) {
					foreach ($call_drop_opt as $key=>$value) {
						echo '<option value="'.$key.'"';
						if ($key == $campaign->drop_call_action) echo ' selected';
						echo '>'.$value.'</option>';
					}
				}
				?>
			</select>
		</td>
	</tr>
	<tr class="form_row" id="tr_ann_fl">
		<td class="form_column_caption">Announcement file: </td>
		<td>
			<input type="file" id="fl_announcement" name="fl_announcement" size="30" value="" onChange=" return readFile()" style="float:left" >
		</td>
	</tr>
	*/
	?>
	<tr class="form_row_head">
	  <td colspan=2>Retry </td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Count: </td>
		<td>
			<input class="label-right" type="text" name="retry_count" size="6" maxlength="1" value="<?php echo $campaign->retry_count;?>" />
		</td>
	</tr>
	<?php /*
	<tr class="form_row_alt">
		<td class="form_column_caption">Interval drop: </td>
		<td>
			<input class="label-right" type="text" name="retry_interval_drop" size="30" maxlength="4" value="<?php echo $campaign->retry_interval_drop;?>" />  (min)
		</td>
	</tr>
	*/ ?>
	<tr class="form_row">
		<td class="form_column_caption">Interval VM: </td>
		<td>
			<input class="label-right" type="text" name="retry_interval_vm" size="6" maxlength="4" value="<?php echo $campaign->retry_interval_vm;?>" />  (min)
		</td>
	</tr>
	<?php /*
	<tr class="form_row_alt">
		<td class="form_column_caption">Interval noanswer: </td>
		<td>
			<input class="label-right" type="text" name="retry_interval_noanswer" size="30" maxlength="4" value="<?php echo $campaign->retry_interval_noanswer;?>" />  (min)
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Interval unreachable: </td>
		<td>
			<input class="label-right" type="text" name="retry_interval_unreachable" size="30" maxlength="4" value="<?php echo $campaign->retry_interval_unreachable;?>" />  (min)
		</td>
	</tr>
	*/ ?>
    <tr class="form_row_head">
        <td colspan=2>Active Hour </td>
    </tr>

    <tr class="form_row">
		<td class="form_column_caption">Time zone: </td>
		<td>
			<select name="timezone" id="timezone">
				<option value="">Select</option>
				<?php
				if (is_array($timezones)) {
					foreach ($timezones as $tz) {
						echo '<option value="'.$tz.'"';
						if ($tz == $campaign->timezone) echo ' selected';
						echo '>'.$tz.'</option>';
					}
				}
				?>
			</select>
		</td>
	</tr>
	
    <tr class="form_row_alt">
        <td class="form_column_caption">Active hour: </td>
        <td><div class="col-xs-12 ">
                <?php
                $per_row_col = 7;
                for ($i = 0; $i <= 23; $i++) {
                    $padd = $i < 10 ? '0' : '';
                    if ($i%$per_row_col == 0 && $i != 0) echo '<br>';
                    echo '<div class="col-md-2"><input type="checkbox" name="rhour'.$i . '" value="1"';
                    if ($campaign->run_hour[$i] == '1') echo ' checked="true"';
                    echo ' /> ' . $padd . $i . '</div>';
                    //if ($i%$per_row_col == 0) echo '</div>';
                }
                ?>
            </div></td>
    </tr>

	<tr class="form_row_alt">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value=" <?php if (!empty($cid)):?>Update<?php else:?>Add<?php endif;?>  " name="submitlead" /> <br>
		</td>
	</tr>
</tbody>
</table>
</form>
<br />

<?php if(!empty($cid)):?>
<br />
<table width="100%" style="width: 100%; margin-bottom: 3px;">
	<tr>
		<td style="text-align: left" width="50%" class="top-title">Lead(s) of Campaign :: <?php echo $cid;?></td>
		<td style="text-align: right" width="50%"><a href="<?php echo $this->url('task='.$request->getControllerName()."&act=leadadd&cid=".$cid);?>" class="btn btn-sm btn-info"><i class="fa fa-plus-square-o"></i> add lead</a></td>
	</tr>
</table>
<table class="report_table" width="60%" border="0" align="center" cellpadding="1" cellspacing="1">
<tr class="report_row_head">
	<td class="cntr">SL</td>
	<td>Lead</td>
    <!--<td class="cntr">Priority</td>
 !--<td class="cntr">Weight</td>
    <td class="cntr">Status</td>-->
    <td class="cntr">Action</td>
</tr>
<?php
if (is_array($campaign_leads)) {
	$i = 0;
	foreach ($campaign_leads as $cl) {
		$i++;
		$_class = $i%2 == 1 ? 'report_row_alt' : 'report_row';

		if (empty($cl->status) || $cl->status == 'N') {
			$status = '<a href="' . $this->url('task='.$request->getControllerName()."&act=leadstatus&status=A&cid=".$cl->campaign_id."&lid=".$cl->lead_id) . '" onclick="return confirm(\'Are you sure to activate lead ' . $cl->lead_id . '?\');"><span class="text-danger">Inactive</span></a>';
		} else if ($cl->status == 'A') {
			$status = '<a href="' . $this->url('task='.$request->getControllerName()."&act=leadstatus&status=N&cid=".$cl->campaign_id."&lid=".$cl->lead_id) . '" onclick="return confirm(\'Are you sure to inactivate lead ' . $cl->lead_id . '?\');"><span class="text-success">Active</span></a>';
		} else {
			$status = isset($status_options[$cl->status]) ? $status_options[$cl->status] : $cl->status;
			$status = '<a href="' . $this->url('task='.$request->getControllerName()."&act=leadstatus&status=A&cid=".$cl->campaign_id."&lid=".$cl->lead_id) . '" onclick="return confirm(\'Are you sure to activate lead ' . $cl->lead_id . '?\');">' . $status . '</a>';
		}
		
		//$lead_title = '<a href="' . $this->url('task='.$request->getControllerName()."&act=leadupdate&cid=".$cl->campaign_id."&lid=".$cl->lead_id) . '">' . $cl->title . '</a>';
		$lead_title = $cl->title;
		
		$action = '<a href="' . $this->url('task='.$request->getControllerName()."&act=leadreload&cid=".$cl->campaign_id."&lid=".$cl->lead_id) . '" onclick="return confirm(\'Are you sure to reload & restart lead '.$cl->title.'?\');" class="btn btn-success btn-xs"><i class="fa fa-refresh"></i> Reload & Restart</a> &nbsp; ';
		$action .= '<a href="' . $this->url('task='.$request->getControllerName()."&act=leaddelete&cid=".$cl->campaign_id."&lid=".$cl->lead_id) . '" onclick="return confirm(\'Are you sure to delete lead '.$cl->title.'?\');" class="btn btn-danger btn-xs"><i class="fa fa-times"></i> Delete</a>';
		
		echo "<tr class=\"$_class\"><td class=\"cntr\">$i</td><td align=\"left\">&nbsp;$lead_title</td>";
		//echo "<!--<td class=\"cntr\">$cl->priority</td><td class=\"cntr\">$cl->weight</td><td style='text-align: center;'>$status</td>-->";
		echo "<td style='text-align: center;'>$action</td></tr>";
	}
} else {
	echo '<tr class="report_extra_info"><td colspan="3" align="center" style="text-align: center;">No lead found</td></tr>';
}
?>
</table>
<?php endif;?>

<script>
adjust_display();
</script>