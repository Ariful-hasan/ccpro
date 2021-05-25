<link href="css/form.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$('#frm_password').attr('autocomplete', 'off');
	
	var pRulesArray = new Array();
	var passMaxLength = 15;
	var passMinLength = 1;
	pRulesArray = '<?php echo json_encode($passRules); ?>';
	pRulesArray = eval('('+pRulesArray+')');
	if(typeof pRulesArray.min != 'undefined' && pRulesArray.min != ""){
		passMinLength = pRulesArray.min;
	}
	if(typeof pRulesArray.max != 'undefined' && pRulesArray.max != ""){
		passMaxLength = pRulesArray.max;
	}

	$.validator.addMethod(
        "regex",
        function(value, element, regexp) {
        	var regxErrMsg = "";
        	var isPassInvalid = true;
        	var allowChars = "";
    		if(typeof pRulesArray.spe != 'undefined' && pRulesArray.spe != ""){
    			var strSp = pRulesArray.spe;
    			for (var i = 0; i < strSp.length; i++) {
    				allowChars += "\\"+strSp.charAt(i);
    			}
    		}
        	var regexCha = new RegExp('[a-zA-Z]{'+pRulesArray.cha+',}');
    		var regexSpe = new RegExp('[^a-zA-Z0-9'+allowChars+']');
    		var regexUpp = new RegExp('(?:[A-Z].*){'+pRulesArray.upp+'}');
    		var regexLow = new RegExp('(?:[a-z].*){'+pRulesArray.low+'}');
    		var regexNum = new RegExp('(?:[0-9].*){'+pRulesArray.num+'}');
        	if (typeof pRulesArray.cha != 'undefined' && pRulesArray.cha != "" && !value.match(regexCha)) {
    			isPassInvalid = false;
    			regxErrMsg = 'New Password field must contain at least '+pRulesArray.cha+' character';
    		} else if (typeof pRulesArray.spe != 'undefined' && pRulesArray.spe == "" && value.match(/[^a-zA-Z0-9]/gi)) {
    			isPassInvalid = false;
    			regxErrMsg = 'New Password field contain invalid character';
    		} else if (typeof pRulesArray.spe != 'undefined' && pRulesArray.spe != "" && value.match(regexSpe)) {
    			isPassInvalid = false;
    			regxErrMsg = 'New Password field contain invalid character';
    		} else if (typeof pRulesArray.upp != 'undefined' && pRulesArray.upp != "" && !value.match(regexUpp)){
    			isPassInvalid = false;
    			regxErrMsg = 'New Password contain at least '+pRulesArray.upp+' uppercase character';
    		} else if (typeof pRulesArray.low != 'undefined' && pRulesArray.low != "" && !value.match(regexLow)){
    			isPassInvalid = false;
    			regxErrMsg = 'New Password contain at least '+pRulesArray.low+' lowercase Character';
    		} else if (typeof pRulesArray.num != 'undefined' && pRulesArray.num != "" && !value.match(regexNum)){
    			isPassInvalid = false;
    			regxErrMsg = 'New Password contain at least '+pRulesArray.num+' number';
    		}
        	$.validator.messages['regex'] = regxErrMsg;
    		return isPassInvalid;
        }
	);

	$( "#frm_password" ).validate({
		rules: {
			opass: {
				required: true,
			},
			npass1: {
				required: true,
				minlength: passMinLength,
				maxlength: passMaxLength,
				regex: ""
			},
			npass2: {
				required: true,
				equalTo: "#npass1"
			}
		},
		messages: {
			opass: {
				required: "Provide Old Password"
			},
			npass1: {
				required: "Provide New Password",
				minlength: "Please enter at least "+passMinLength+" characters",
				maxlength: "Password must not more than "+passMaxLength+" characters"
			},
			npass2: {
				required: "Retype New Password",
				equalTo: "Enter the same password as above"
			}
		}
	});
});
</script>

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_password" id="frm_password" method="post" autocomplete="off" action="<?php echo $this->url('task='.$request->getControllerName());?>">
<input type="hidden" name="lparam" value="<?php echo $lparam;?>" />
<table class="form_table" border="0" width="450" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan="2">Change Login Password</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"  width="50%" style="min-width: 195px;">Old Password:</td>
		<td><input type="password" name="opass" id="opass" size="20" value="" autocomplete="off" /></td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">New Password:</td>
		<td>
			<input type="password" name="npass1" id="npass1" size="20" maxlength="<?php echo $maxlength; ?>" value="" autocomplete="off" />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Confirm New Password:</td>
		<td><input type="password" name="npass2" id="npass2" size="20" maxlength="<?php echo $maxlength; ?>" value="" autocomplete="off" /></td>
	</tr>
	<tr class="form_row" height="40">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" name="submit" type="submit" value="Change" /> <br />
		</td>
	</tr>
</tbody>
</table>
</form>
