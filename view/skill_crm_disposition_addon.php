<script src="js/jquery.min.js" type="text/javascript"></script>
<script src="js/jquery.watermark.min.js" type="text/javascript"></script>
<script src="js/jquery.clockpick.1.2.2.pack.js" type="text/javascript"></script>
<script src="js/jquery.datePicker.js" type="text/javascript"></script>
<script src="js/date.js" type="text/javascript"></script>
<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>

<link rel="stylesheet" href="css/report.css" type="text/css">
<link rel="stylesheet" href="css/form.css" type="text/css">
<link rel="stylesheet" href="css/clockpick.css" type="text/css">
<link rel="stylesheet" href="css/datePicker.css" type="text/css">
<link rel="stylesheet" href="css/crm.in.css" type="text/css">

<script type="text/javascript">
<?php 
$url_param = "act=addon&callid=$callid&tempid=$tempid&cli=$cli";
?>
$(document).ready(function() {
	Date.format = 'yyyy-mm-dd';
	$("#clk_time").clockpick({starthour: 0, endhour: 23, minutedivisions:12,military:true});
	$('#clk_date').datePicker({clickInput:true,createButton:false,startDate:'<?php echo date("Y-m-d");?>'});
	$('#clk_date').watermark('Date');
	$('#clk_time').watermark('Time');

	$("#btn_edit_disposition").click(function (e) {
		$("#div_disposition").toggle();
		var text = $(this).text();
		$(this).text(text == "Edit Disposition" ? "Hide Disposition Form" : "Edit Disposition");
	});
	
	$("#set_disposition").click(function (e) {
		e.stopPropagation();
		var disposition = $("#disposition").val();
		if (disposition.length > 0) {
		
		$.ajax({
			type: "POST",
			url: "<?php echo $this->url('task='.$request->getControllerName()."&act=savedisposition&record_id=".$cli);?>",
			data: { disposition: disposition, callid: '<?php if (!empty($callid)) echo $callid;?>', note: $("#comment").val(), status: $("#status").val() },
				beforeSend: function ( xhr ) {
					$("#fl_msg_saving").show();
				}
			}).done(function( msg ) {
				$("#fl_msg_saving").hide();
				if (msg > 0) {
					//$("#fl_msg_success").show();
					//$("#fl_msg_success").fadeOut(2000, function () {
						window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&$url_param");?>";
					//});
				} else {
					//$("#fl_msg_err").show();
					//$("#fl_msg_err").fadeOut(2000, function () {
						window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&$url_param");?>";
					//});
				}
		});
		} else {
			alert('Select disposition !!');
		}
	});
	
	<?php if ($display_disposition):?>init_disposition('<?php echo $dispositioninfo->disposition_id;?>');<?php endif;?>
});

function playFile(evt)
{
	$.post('cc_web_agi.php', { 'page':'CDR', 'agent_id':'<?php echo UserAuth::getCurrentUser();?>', 'option':evt },
		function(data, status) {
			if (status == 'success') {
				if(data != 'Y') alert(data);
			} else {
				alert("Failed to communicate!");
			}
	});
}

<?php if ($display_disposition):?>
var dispositions = [<?php

$current_disposition = 'Not Defined';
$current_disp_id = empty($dispositioninfo->disposition_id) ? '' : $dispositioninfo->disposition_id;
if (is_array($dp_options)) {
	$i = 0;
	foreach ($dp_options as $row) {
		if ($i>0) echo ',';
		echo '{di:"'.$row->disposition_id.'", gi:"'.$row->group_id.'", t:"'.$row->title.'"}';
		if ($row->disposition_id == $current_disp_id) $current_disposition = $row->title;
		$i++;
	}
}
?>];

function init_disposition(disp_code)
{
	var sgroup = '';
	var len = dispositions.length;
	for(var i = 0; i < len; i++) {
		var obj = dispositions[i];
		if (obj.di == disp_code) {
			$("#dgroup").val(obj.gi);
			break;
		}
	}
	
	adjust_disposition();
	$("#disposition").val(disp_code);
}

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
<?php endif;?>
</script>


<table width="100%" align="center" border="0" class="profile-details" cellpadding="1">
<tr><td valign="top">


<div class="act-head" style="font-size:17px; padding:5px;background: linear-gradient(to bottom, #4A98AF 44%, #328AA4 45%) repeat scroll 0 0 rgba(0, 0, 0, 0); color:#fff;">Quick Actions</div><br />

<?php if (!empty($callid)):
$is_authenticated = empty($crm_info) ? false : true;
?>
<div style="padding-bottom: 0px;">

<form id="frm_search" action="<?php echo $this->url('task='.$request->getControllerName()."&act=addon");?>" method="post">
<?php 
if (!$is_authenticated) {
?>
<a href="<?php echo $this->url('task='.$request->getControllerName()."&act=addtocrm&cli=".$cli."&tempid=$tempid&callid=$callid");?>" onclick="return confirm('Are you sure to verify this number?')">Add <?php echo $cli;?> as a verified number in CRM?</a> Or 
<?php 
} else {
?>
Phone number <?php echo $cli;?> is an existing client.  
<?php 
}
?>
<input type="hidden" name="tempid" value="<?php echo $tempid;?>" />
<input type="hidden" name="callid" value="<?php echo $callid;?>" />
<input type="text" name="cli" id="search_item" size="20" maxlength="30"<?php echo $is_authenticated ? ' disabled="disabled"' : '';?> /> <input type="submit" class="btn" id="btn_search" value="Search"<?php echo $is_authenticated ? ' disabled="disabled"' : '';?> />
</form>

</div>

<?php endif;?>

<div>
<?php if (!empty($dispositioninfo->status)):?>
Session disposition is <b><?php echo $current_disposition;?></b>  with status <b><?php if ($dispositioninfo->status == 'C') echo 'Closed'; else if ($dispositioninfo->status == 'P') echo 'Pending'; else echo $dispositioninfo->status;?></b> &nbsp;
<?php endif;?>
<a href="#" id="btn_edit_disposition"><?php if (!empty($dispositioninfo->status)):?>Edit Disposition<?php else:?>Hide Disposition Form<?php endif;?></a><br />
</div>
<div id="div_disposition" style="<?php if (!empty($dispositioninfo->status)):?>display:none;<?php endif;?>">
<?php if ($display_disposition):?>
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
<textarea name="note" id="comment" maxlength="255" style="width:90%; height:140px;"><?php echo $dispositioninfo->note;?></textarea>
<br /><br /><div style="">
<select name="status" id="status"><option value="P">Pending</option><option value="C"<?php if ($dispositioninfo->status == 'C') echo ' selected';?>>Closed</option></select>
<input type="button" class="btn btn-info" id="set_disposition" name="set_disposition" value="Save" /></div>
<?php endif;?>
</div>


</td></tr>

</table>


<table width="100%" align="center" border="0" class="profile-details" cellpadding="1">
<?php if (!is_array($records)) { ?><tr><td colspan="4">&nbsp;</td></tr><?php } ?>
<tr><td colspan="4" align="left">
<?php
if (is_array($records)) {
?>
<table class="report_extra_info">
<tr>
	<td><small>
		<?php
        	echo 'Record(s) ' . $pagination->getCurrentRecordsIndex() . ' of <b>' . $pagination->num_records . '</b> &nbsp;::&nbsp; ' . 
				'Page <b>' . $pagination->current_page . '</b> of <b>' . $pagination->getTotalPageCount() . '</b>';
		?></small>
	</td>
</tr>
</table>

<table class="report_table" cellspacing="0">
<tr class="report_row_head"><td class="cntr">SL</td><td class="cntr">Time</td><td>Disposition</td><td>Agent</td><td>Auth by</td><td>Note</td><td class="cntr">Audio</td></tr>
<?php
$i = $pagination->getOffset();
foreach ($records as $rec) {
$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
?>
<tr class="<?php echo $_class;?>"><td class="cntr"><?php echo $i;?></td><td class="cntr"><?php echo date("Y-m-d H:i:s", $rec->tstamp);?></td><td align="left">&nbsp;<?php echo $rec->title;?></td><td><?php echo empty($rec->agent_id) ? '-' : $rec->agent_id . ' ['.$rec->nick.']';?></td><td><?php if ($rec->caller_auth_by == 'I') echo 'IVR'; else if ($rec->caller_auth_by == 'A') echo 'Agent'; else echo '-';?></td><td align="left">&nbsp;<?php echo $rec->note;?></td><td class="cntr"><?php
		if (!empty($rec->callid)) {
			$file_timestamp = substr($rec->callid, 0, 10);
			$yyyy = date("Y", $file_timestamp);
			$yyyy_mm_dd = date("Y_m_d", $file_timestamp);
			$sound_file_gsm = $this->voice_logger_path . "$yyyy/$yyyy_mm_dd/" . $rec->callid . ".gsm";
			if (file_exists($sound_file_gsm) && filesize($sound_file_gsm) > 0) {
				$log->audio = "<a href=\"#\" onClick=\"playFile('$rec->callid')\"><img src=\"image/play2.gif\" title=\"gsm\" border=0></a>";
				if ($is_voice_file_downloadable) {
					$log->audio .= " <a target=\"blank\" href=\"$sound_file_gsm\"><img src=\"image/play_in_browser2.gif\" title=\"Play in browser\" border=0 width=\"15\"></a>";
				}

			} else {
				$log->audio = "NONE";
			}
		} else {
			$log->audio = "NONE";
		}
    	echo $log->audio;
	?>
	</td></tr>
<?php
}
?>
</table>
<table class="report_extra_info">
<tr>
	<td><small>
	<?php echo $pagination->createLinks();?>
	</small></td>
</tr>
</table>
<?php
} else {
	echo '<b>No record found !!</b>';
}
?>
</td></tr>

</table>
