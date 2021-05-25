<link href="css/form.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
$(document).ready(function() {
	$("#reportDate").on('input',function(e){
    	this.value = this.value.replace(/[^0-9]/g,'');
    });
	$("#frm_reportday").submit(function(event){
		var isPassInvalid = false;
		var reportDay = $("#reportDate").val();
		var oldReportDay = $("#old_reportDate").val();
		var errMsg = "";

		/* if(reportDay == "" || reportDay == null){
			errMsg = "Report showing day is empty!";
			isPassInvalid = true;
		}else if(!reportDay.match(/^[0-9]+$/)){
			errMsg = "Report showing day is not valid number!";
			isPassInvalid = true;
		}else if(reportDay.length > 3){
			errMsg = "Report showing day is not valid!";
			isPassInvalid = true;
		}

		if(isPassInvalid == true && errMsg != ""){
			event.preventDefault();
			alert(errMsg);
			return false;
		}
		if(reportDay == oldReportDay){
			event.preventDefault();
			alert("No change found!");
			return false;
		} */		        
	});
});
</script>

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_reportday" id="frm_reportday" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">

<table class="form_table" border="0" width="480" align="center" cellpadding="6" cellspacing="0">
	<tbody>
		<tr class="form_row_head">
		  <td colspan="2">Report Showing Days</td>
		</tr>
		<tr class="form_row">
			<td class="form_column_caption">Day: </td>
			<td>
			    <input type="hidden" name="old_reportDate" id="old_reportDate" value="<?php echo $report_day; ?>">
            	<input type="text" value="<?php echo $report_day; ?>" maxlength="3" size="20" name="reportDate" id="reportDate">
            </td>
        </tr>
		<tr class="form_row_alt" id="tr-submit">
			<td colspan="2" class="form_column_submit">			    
				<input class="form_submit_button" type="submit" value=" Update" name="update_psettings"> <br>
			</td>
		</tr>
	</tbody>
</table>
</form>
