<link rel="stylesheet" href="css/form.css" type="text/css">
<script type="text/javascript">
var ticketStatus = "<?php echo $service->status_ticketing;?>";

$(document).ready(function(){
	if(ticketStatus == "Y"){
		$(".ticketing").show();
	}else{
		$(".ticketing").hide();
	}

	$("#status_ticketing").change(function(){
		var newStatus = $(this).val();
		if(newStatus == "Y"){
			$(".ticketing").show();
		}else{
			$(".ticketing").hide();
		}
	});
});
</script>
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_ivr_service" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="gid" value="<?php if (isset($dis_code)) echo $dis_code;?>" />
<input type="hidden" name="tid" value="<?php if (isset($tid)) echo $tid;?>" />
<?php if (!$this->chat_module){ ?>
<input type="hidden" name="use_email_module" value="N" />
<?php } ?>
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Service Type Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" width="33%"><span class="required">*</span> Service Type Title:</td>
		<td>
			<input type="text" name="title" size="30" maxlength="30" value="<?php echo $service->title;?>" placeholder="Service Type Title" />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" width="33%"><span class="required">*</span> Service Type:</td>
		<td>
		    <select name="status_ticketing" id="status_ticketing">
		        <option value="N" <?php echo $service->status_ticketing=='N' ? "selected='selected'" : "";?>>Normal</option>
		        <option value="Y" <?php echo $service->status_ticketing=='Y' ? "selected='selected'" : "";;?>>Ticketing</option>
		    </select>
		</td>
	</tr>
<?php if ($this->chat_module){ ?>
	<tr class="form_row_alt ticketing">
		<td class="form_column_caption" width="33%"><span class="required">*</span> Use Email Module:</td>
		<td>
		    <select name="use_email_module" id="use_email_module">
		        <option value="N" <?php echo $service->use_email_module=='N' ? "selected='selected'" : "";?>>No</option>
		        <option value="Y" <?php echo $service->use_email_module=='Y' ? "selected='selected'" : "";;?>>Yes</option>
		    </select>
		</td>
	</tr>
<?php } ?>
	<tr class="form_row_alt ticketing">
		<td class="form_column_caption" width="33%"><span class="required">*</span> From Email:</td>
		<td>
			<input type="text" name="from_email_name" size="30" maxlength="60" value="<?php echo $service->from_email_name;?>" placeholder="Name <Email>, " />
		</td>
	</tr>
	<tr class="form_row_alt ticketing">
		<td class="form_column_caption" width="33%"><span class="required">*</span> To Email:</td>
		<td>
			<input type="text" name="to_email_name" size="30" maxlength="255" value="<?php echo $service->to_email_name;?>" placeholder="Name <Email>, " />
		</td>
	</tr>
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if (!empty($dis_code)):?>Update<?php else:?>Add<?php endif;?>  " name="submitservice"> <br /><br />
		</td>
	</tr>
</tbody>
</table>
</form>
