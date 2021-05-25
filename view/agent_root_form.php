<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<link href="css/form.css" rel="stylesheet" type="text/css">

<link rel="stylesheet" type="text/css" media="screen" href="css/datePicker.css">
<script src="js/date.js" type="text/javascript"></script>
<script src="js/jquery.datePicker.js" type="text/javascript"></script>

<?php
	include('conf.extras.php');
	$is_telebank = false;
	$row_span = 9;
	if ($extra->tele_banking && isset($agent->web_password_priv) && $agent->web_password_priv == 'Y') {
		$is_telebank = true;
		$row_span = 11;
	}

	$agent_title = 'Root User';
?>
<style type="text/css">
label.error {
    float: right;
    font-size: 12px;
    margin-top: 4px;
    width: 56%;
}
.form_table td.form_column_caption {
    padding-top: 14px;
    vertical-align: top;
}
</style>
<script type="text/javascript">
Date.format = 'yyyy-mm-dd';
$(document).ready(function() {
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
	$('#frm_agent').attr('autocomplete', 'off');
	
	$('.date-pick').datePicker({clickInput:true,createButton:false,startDate:'1971-01-01', endDate: (new Date()).asString()});
	$.validator.addMethod(
	        "regex",
	        function(value, element, regexp) {
	            //var re = new RegExp(/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z]{7,}$/);
	            //return this.optional(element) || value.match(re);
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
	$( "#frm_agent" ).validate({
<?php if (empty($agentid)):?>
		rules: {
			agent_id: {
				required: true,
				minlength: 4,
				digits: true,
				remote: {
					url: "<?php echo $this->url('task='.$request->getControllerName()."&act=check");?>",
					type: "post"
				}
			},
			password: {
				required: true,
				minlength: passMinLength,
				maxlength: passMaxLength,
				regex: ""
			},
			password_re: {
				required: true,
				equalTo: "#password"
			},
			email: {
				email: true
			}
		},
		messages: {
			agent_id: {
				required: "Provide <?php echo $agent_title;?> ID",
				remote: "This ID already exist"
			},
			password: {
				required: "Provide a password",
				minlength: "Please enter at least "+passMinLength+" characters",
				maxlength: "Password must not more than "+passMaxLength+" characters"
			},
			password_re: {
				required: "Repeat your password",
				equalTo: "Enter the same password as above"
			}
		}
<?php else: ?>
		rules: {
			password_re: {
				equalTo: "#password"
			}
<?php if ($is_telebank) { ?>
			, web_password_re: {
				equalTo: "#web_password"
			}
<?php }?>
		},
		messages: {
			password_re: {
				equalTo: "Enter the same password as above"
			}
<?php if ($is_telebank) { ?>
			, web_password_re: {
				equalTo: "Enter the same password as above"
			}
<?php }?>
		}
<?php endif;?>
	});
	
	$("#agentImage").change(function(){
		var ext = $(this).val().split('.').pop().toLowerCase();
		if(ext != 'png') {
			alert('Only png file is allowed to upload');
		} else {
        	readURL(this);
		}
    });
});

function readURL(input) {
	if (input.files && input.files[0]) {
		var reader = new FileReader();
		reader.onload = function (e) {
			$('#agentImageView').attr('src', e.target.result);
		}
		reader.readAsDataURL(input.files[0]);
	}
}
</script>

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_agent" id="frm_agent" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>" enctype="multipart/form-data">
<input type="hidden" name="agentid" value="<?php if (isset($agentid)) echo $agentid;?>" />
<input type="hidden" name="active" value="<?php echo $agent->active;?>" />

<table class="form_table">
<tbody>
	<tr class="form_row_head">
	  <td colspan=3><?php echo $agent_title;?> Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><?php if (empty($agentid)):?><span class="required">*</span> <?php endif;?><?php echo $agent_title;?> ID:</td>
		<td>
		<?php if (empty($agentid)):?>
			<input type="text" name="agent_id" size="30" maxlength="4" value="<?php echo $agent->agent_id;?>" />
		<?php else:?>
			<b><?php echo $agent->agent_id;?></b>
		<?php endif;?>
     	</td>
	</tr>
    <!-- <a class="btn" style="float:left;width:105px;"><img src="image/add.png" class="bottom" border="0" width="16" height="16" /> Add</a> -->
	<tr class="form_row">
		<td class="form_column_caption">Name:</td>
		<td>
			<input type="text" name="name" size="30" maxlength="50" value="<?php echo $agent->name;?>" />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Nick Name:</td>
		<td>
			<input type="text" name="nick" size="30" maxlength="10" value="<?php echo $agent->nick;?>" />
		</td>
	</tr>
	<?php if (empty($agentid)):?>
	<tr class="form_row">
		<td class="form_column_caption"><?php if (empty($agentid)):?><span class="required">*</span><?php endif;?> Password: </td>
		<td>
        	<input type="password" id="password" name="password" size="30" maxlength="<?php echo $maxlength; ?>" value="" autocomplete="off">
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><?php if (empty($agentid)):?><span class="required">*</span><?php endif;?> Retype password: </td>
		<td>
        	<input type="password" id="password_re" name="password_re" size="30" maxlength="<?php echo $maxlength; ?>" value="" autocomplete="off">
		</td>
	</tr>
	<?php endif;?>

	<tr class="form_row_alt">
		<td class="form_column_caption">Access Start Time:</td>
		<td>
			<input type="text" name="from_time" size="30" maxlength="20" value="<?php echo $agent->from_time;?>" />
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Access Duration (min):</td>
		<td>
			<input type="text" name="access_duration" size="30" maxlength="5" value="<?php echo $agent->access_duration;?>" />
		</td>
	</tr>
	
	<tr class="form_row_alt">
		<td class="form_column_caption">Telephone:</td>
		<td>
			<input type="text" name="telephone" size="30" maxlength="30" value="<?php echo $agent->telephone;?>" />
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Email:</td>
		<td>
			<input type="text" name="email" size="30" maxlength="50" value="<?php echo $agent->email;?>" />
		</td>
	</tr>

   
	<tr class="form_row_alt">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if (!empty($agentid)):?>Update<?php else:?>Add<?php endif;?>  " name="submitagent"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>

