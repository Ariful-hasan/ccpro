<link href="css/form.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
$(document).ready(function() {
	$("#frm_password").submit(function(event){
		var isPassInvalid = false;
		var totalRule = $("#totalRule").val();
		totalRule = parseInt(totalRule);
		var isChanged = false;
		var errMsg = "";
		for(i=1; i<=totalRule; i++) {
			var status = "N";
			if($('#status' + i).is(':checked')){
				status = "Y";
			}
			var oldStatus = $('#old_status' + i).val();
			var ruleValue = $('#rule' + i).val();
			var oldRuleValue = $('#old_rule' + i).val();

			if (ruleValue == "" || ruleValue == null || ruleValue == '0'){
				errMsg = "Rule "+ i +"'s value is empty or 0!";
				isPassInvalid = true;
				break;
			}else if(i != 7 && !ruleValue.match(/^[0-9]+$/)){
				errMsg = "Rule "+ i +"'s value("+ ruleValue +") is not valid number!";
				isPassInvalid = true;
				break;
			}else if (i == 7 && ruleValue.match(/[a-zA-Z0-9]+/)){
				errMsg = "Rule "+ i +"'s value("+ ruleValue +") contain regular character or number!";
				isPassInvalid = true;
				break;
			}else if (i > 2 && i < 7 && ruleValue > 5){
				errMsg = "Rule "+ i +"'s value("+ ruleValue +") must be a number from 1 to 5!";
				isPassInvalid = true;
				break;
			}
			
			if(status != oldStatus || ruleValue != oldRuleValue){
				isChanged = true;
			}
		}
		var rule1value = $('#rule1').val();
		var rule2value = $('#rule2').val();
		var rule8value = $('#rule8').val();
		var rule9value = $('#rule9').val();
		if (parseInt(rule1value) >= parseInt(rule2value)){
			isPassInvalid = true;
			errMsg = "Minimum length must not greater than or equal to maximum length of password!";
		}
		if (parseInt(rule9value) >= parseInt(rule8value)){
			isPassInvalid = true;
			errMsg = "Rule 9's value must not greater than or equal to rule 8's value!";
		}

		if(isPassInvalid == true && errMsg != ""){
			event.preventDefault();
			alert(errMsg);
			return false;
		}
		if(isChanged == false){
			event.preventDefault();
			alert("No change found!");
			return false;
		}		        
	});
});
</script>

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_password" id="frm_password" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<table class="table table-striped table-bordered">
<tr class="form_row_head">
	<td class="cntr">Rule No</td>
	<td>Description</td>
	<td class="cntr">Value</td>
	<td class="cntr">Active</td>
</tr>

<?php 
if (is_array($passRules) && count($passRules) > 0){
	$i = 0;
	foreach ($passRules as $rule){
		$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
?>
<tr class="<?php echo $_class;?>">
	<td class="cntr">&nbsp;<?php echo $rule->rule_no;?></td>
	<td align="left" style="font-weight: bold;">&nbsp;<?php echo $rule->title;?></td>
	<td class="cntr">&nbsp;
		<input type="text" name="rule<?php echo $rule->rule_no;?>" id="rule<?php echo $rule->rule_no;?>" size="20" value="<?php echo $rule->value;?>">
		<input type="hidden" name="old_rule<?php echo $rule->rule_no;?>" id="old_rule<?php echo $rule->rule_no;?>" value="<?php echo $rule->value;?>">
	</td>
	<td class="cntr">&nbsp;
		<input type="checkbox" name="status<?php echo $rule->rule_no;?>" id="status<?php echo $rule->rule_no;?>" <?php echo $rule->status=='Y' ? 'checked' : '';?> value="status">
		<input type="hidden" name="old_status<?php echo $rule->rule_no;?>" id="old_status<?php echo $rule->rule_no;?>" value="<?php echo $rule->status;?>">
	</td>
</tr>
<?php 
	}
}
?>
</table>


<div align="center" style="margin-top: 15px;">
	<input type="hidden" name="totalRule" id="totalRule" value="<?php echo count($passRules); ?>">
	<input class="form_submit_button" name="submit" type="submit" value="Update" /> <br />
</div>
</form>
