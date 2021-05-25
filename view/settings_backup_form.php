<link href="css/form.css" rel="stylesheet" type="text/css">
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<?php if ($page_part == 2):?>
<script type="text/JavaScript" src="js/jquery.min.js"></script>
<script type="text/JavaScript">

var dvid = '<?php echo $deviceid;?>';
var pin = '<?php echo $pin;?>';

$(document).ready(function(){

	$("#btnBackup").click(function(){
		var label = $(this).val();
		if (label == 'Start Backup') doTask('START');
		if (label == 'Cancel') doTask('CANCEL');
		if (label == 'Back') window.location = '<?php echo $this->url('task='.$request->getControllerName()."&act=init");?>';
	});
	
	var updateMsg = function() {
		
		$.ajax({type: "POST", url: "<?php echo $this->url('task='.$request->getControllerName()."&act=status");?>", data: "dlg=2", dataType: "json", success: function(json) {
			if (json == null) {
				showProgress(-1);
				$("#label_status").html('&nbsp;');
				$("#sinfo").html('Please wait ..');
				$("#btnBackup").val('Back');
			}
			if (json.status == 'INFO') {
				showProgress(-1);
				$("#label_status").html('&nbsp;');
				$("#sinfo").html(json.txt);
				$("#btnBackup").val('Start Backup');
			} else if (json.status == 'START') {
				showProgress(json.percent);
				$("#label_status").html(': Running');
				$("#sinfo").html(json.txt);
				$("#btnBackup").val('Cancel');
			} else if (json.status == 'CANCEL') {
				showProgress(json.percent);
				$("#label_status").html(': Canceled');
				$("#sinfo").html(json.txt);
				$("#btnBackup").val('Back');
			} else if (json.status == 'END') {
				showProgress(json.percent);
				$("#label_status").html(': End');
				$("#sinfo").html(json.txt);
				$("#btnBackup").val('Back');
			} else if (json.status == 'ERROR') {
				showProgress(-1);
				$("#label_status").html('&nbsp;');
				$("#sinfo").html(json.txt);
				$("#btnBackup").val('Back');
			} else {
				showProgress(-1);
				$("#label_status").html('&nbsp;');
				if (json.txt.length>0) $("#sinfo").html(json.txt);
				else $("#sinfo").html('&nbsp;');
				$("#btnBackup").val('Back');
				//$("#btnBackup").hide();
			}
		}});
		
		setTimeout(updateMsg, 1000);
	};
	updateMsg();
});

function showProgress(val)
{
	if (val < 0) {
		$("#spnProgress").hide();
		$("#labRunning").hide();
	} else {
		$("#spnProgress").show();
		$("#labRunning").show();
		if (val>=0 && val<= 100) {
			$("#spnRunning").css('width', val+'%');
			$("#labRunning").html(val+'%');
		}
	}
}

function doTask(tsk)
{
	$.ajax({type: "POST", url: "<?php echo $this->url('task='.$request->getControllerName()."&act=update");?>", data: "deviceid="+dvid+"&pin="+pin+"&st="+tsk, 
		dataType: "json", success: function(json) {
		//do something
		
		return;
	}});
}

</script>

<table class="form_table" border="0" width="450" align="center" cellpadding="6" cellspacing="0">
	<tr class="form_row_head">
		<td colspan=2>Backup Status <span id="label_status"></span></td>
	</tr>
  	<tr class="form_row_alt">
		<td class="form_column_caption" colspan="2" style="text-align:left;">
        	<div id="sinfo"></div>
        </td>
	</tr>
  	<tr class="form_row_alt">
		<td class="form_column_caption" colspan="2" style="text-align:left;">
        	<div id="spnProgress" style="width:85%;background-color:#FFFFFF; height:15px;float:left;"><div id="spnRunning" style="background-color:#0099FF; height:15px;"></div></div>
            &nbsp;&nbsp;<span id="labRunning" style="float:right;"></span>
        </td>
	</tr>
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="button" name="backup" id="btnBackup" value="Back" />
		</td>
	</tr>
</table>

<?php else:?>
<form action="<?php echo $this->url('task='.$request->getControllerName()."&act=init");?>" method="post">
<table class="form_table" border="0" width="450" align="center" cellpadding="6" cellspacing="0">
	<tr class="form_row_head">
		<td colspan=2>Backup Information</td>
	</tr>
  	<tr class="form_row">
		<td class="form_column_caption" width="40%">Device ID : </td>
		<td>
			<input type="text" name="deviceid" value="<?php echo $deviceid;?>" maxlength="6" autocomplete="off" />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">PIN : </td>
		<td>
			<input type="text" name="pin" value="<?php echo $pin;?>" maxlength="4" autocomplete="off" />
		</td>
	</tr>
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
        	<input class="form_submit_button" type="submit" name="backup" value="Submit" />
		</td>
	</tr>
</table>
</form>
<?php endif;?>