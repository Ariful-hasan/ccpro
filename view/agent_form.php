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

	$agent_title = 'Agent';
	if ($agent->usertype == 'S') {
		$agent_title = 'Supervisor';
	} else if ($agent->usertype == 'D') {
		$agent_title = 'Dashboard User';
	} else if ($agent->usertype == 'P') {
		$agent_title = 'Report User';
	} else if ($agent->usertype == 'G') {
		$agent_title = 'Digicon Report User';
	}
	
	$image_src = "data/profile/profile.png?v=1";
	$custom_image = false;
	if (isset($agentid) && !empty($agentid)) {
		if (file_exists('agents_picture/'. $agentid.'.png')) {
			$image_src = 'agents_picture/'.$agentid.'.png?t=' . time();
			$custom_image = true;
		}
	}
	if(isset($agent->altid)){
	    $altid = $agent->altid;
	} else {
	    $altid = "";
	}
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
.form_table td.form_column_caption {
    width: 215px;
}
.form_table input[type="password"] {
    background-color: #ffffff;
    background-image: none;
    border: 1px solid #cccccc;
    border-radius: 4px;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;
    color: #555555;
    display: inline;
    font-size: 14px;
    height: 34px;
    line-height: 1.42857;
    padding: 6px 12px;
    transition: border-color 0.15s ease-in-out 0s, box-shadow 0.15s ease-in-out 0s;
    width: 100%;
}

 .cancel-btn {
     position: absolute;
     right: 8px;
     top: -5px;
     z-index: 50;
     width: 20px;
     height: 20px;
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
	            //^(?=.*\d{2})(?=.*[a-z]{2})(?=.*[A-Z]{2}).{4,10}$
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
			name: {
				required: true,
				minlength: 5,
				maxlength: 35,
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
				email: false
			},
			max_chat_session: {
				digits: true,
			},
			chat_session_limit_with_call: {
				digits: true,
			},
			role_id: {
				required: true
			},
			screen_logger: {
				required: true
			},
			ob_call: {
				required: true
			}
		},
		messages: {
			agent_id: {
				required: "<?php echo $agent_title;?> ID is required!",
				remote: "This ID already exist!"
			},
			name: {
				required: "Name is required!",
				minlength: "Please enter at least 5 characters!",
				maxlength: "Password must not more than 35 characters!"
			},
			password: {
				required: "Password is required!",
				minlength: "Please enter at least "+passMinLength+" characters!",
				maxlength: "Password must not more than "+passMaxLength+" characters!"
			},
			password_re: {
				required: "Repeat your password!",
				equalTo: "Enter the same password as above!"
			},
			max_chat_session: {
				digits: "Only digits allowed!"
			},
			chat_session_limit_with_call: {
				digits: "Only digits allowed!"
			},
			role_id: {
				required: "Please select a role!",
			},
			screen_logger: {
				required: "Please select a screen logger!",
			},
			ob_call: {
				required: "Please select a outbound call!",
			}
		}
<?php else: ?>
		rules: {
			password_re: {
				equalTo: "#password"
			},
<?php if ($is_telebank) { ?>
			web_password_re: {
				equalTo: "#web_password"
			},
<?php }?>
			name: {
				required: true,
				minlength: 5,
				maxlength: 35,
			},
			max_chat_session: {
				digits: true,
			},
			chat_session_limit_with_call: {
				digits: true,
			},
			role_id: {
				required: true
			},
			screen_logger: {
				required: true
			},
			ob_call: {
				required: true
			}
		},
		messages: {
			password_re: {
				equalTo: "Enter the same password as above!"
			},
<?php if ($is_telebank) { ?>
			web_password_re: {
				equalTo: "Enter the same password as above!"
			},
<?php }?>
			name: {
				required: "Name is required!",
				minlength: "Please enter at least 5 characters!",
				maxlength: "Password must not more than 35 characters!"
			},
			max_chat_session: {
				digits: "Only digits allowed!"
			},
			chat_session_limit_with_call: {
				digits: "Only digits allowed!"
			},
			role_id: {
				required: "Please select a role!",
			},
			screen_logger: {
				required: "Please select a screen logger!",
			},
			ob_call: {
				required: "Please select a outbound call!",
			}
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

<form name="frm_agent" id="frm_agent" class="form form-vertical small" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>" enctype="multipart/form-data">
<input type="hidden" name="agentid" value="<?php if (isset($agentid)) echo $agentid;?>" />



<input type="hidden" name="active" value="<?php echo $agent->active;?>" />

    <div class="panel panel-default">
        <div class="panel-heading">Agent Information</div>
        <div class="panel-body">
            <div class="col-md-12 col-sm-12 col-lg-12">

                <div class="row">
                    <div class="col-md-10 col-sm-10 col-lg-10">
                        <div class="row">
                            <div class="col-md-6 col-sm-6 col-lg-6">
                                <div class="form-group form-group-sm form-group form-group-sm-sm">
                                    <label for="agent_id" class="control-label "><?php echo $agent_title;?>ID: <?php if (empty($agentid)):?><span class="error">*</span> <?php endif;?></label>
                                    <div class="">
                                        <input type="text" name="agent_id" <?php echo !empty($agentid) ? 'disabled' : ''; ?>  class="form-control" maxlength="4" value="<?php echo $agent->agent_id;?>" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6">
                                <div class="form-group form-group-sm form-group form-group-sm-sm">
                                    <label for="supervisor_id" class="control-label "> Supervisor:</label>
                                    <div class="">
                                        <select name="supervisor_id" id="supervisor_id" class="form-control">
                                            <option value="">---Select---</option>
                                            <?php if(!empty($supervisors)): ?>
                                                <?php foreach ( $supervisors as $supervisor): ?>
                                                    <option value="<?php echo $supervisor->agent_id; ?>" <?php echo $agent->supervisor_id == $supervisor->agent_id ? 'selected' : '';?>><?php echo $supervisor->name; ?> (<?php echo $supervisor->agent_id; ?>)</option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6 col-sm-6 col-lg-6">
                                <div class="form-group form-group-sm">
                                    <label for="name" class="control-label ">Name: <span class="error">*</span></label>
                                    <div class="">
                                        <input type="text" name="name"  class="form-control" maxlength="35" value="<?php echo $agent->name;?>" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6">
                                <div class="form-group form-group-sm">
                                    <label for="nick" class="control-label "> Nick Name: </label>
                                    <div class="">
                                        <input type="text" name="nick"  class="form-control" maxlength="10" value="<?php echo $agent->nick;?>" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-sm-6 col-lg-6">
                                <div class="form-group form-group-sm form-group form-group-sm-sm">
                                    <label for="email" class="control-label "> Email: </label>
                                    <div class="">
                                        <input type="email" name="email" class="form-control" maxlength="50" value="<?php echo $agent->email;?>" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-lg-6">
                                <div class="form-group form-group-sm form-group form-group-sm-sm">
                                    <label for="partition_id" class="control-label "> Partition: </label>
                                    <div class="">
                                        <select name="partition_id" id="partition_id" class="form-control">
                                            <option value="">---Select---</option>
                                            <?php if (!empty($partitions)) : ?>
                                                <?php foreach ($partitions as $partition_id => $partition_name) : ?>
                                                    <option value="<?php echo $partition_id ?>" <?php echo $agent->partition_id == $partition_id ? 'selected' : ''?> ><?php echo $partition_name; ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-2 col-sm-2 col-lg-2">
                            <input type='file' id="agentImage" class="hide" name="agentImage" />
                            <div class="profile-picture" ">
                                <?php if ($custom_image):?>
                                    <a href="<?php echo $this->url('task='.$request->getControllerName()."&act=delpic&agentid=$agentid");?>" onclick="return confirm('Are you sure to remove this profile picture?');">
                                        <img src="image/cancel.png" class="bottom cancel-btn" /></a>
                                <?php endif;?>
                                <img id="agentImageView" class="img-thumbnail pull-right" src="<?php echo $image_src;?>" style="width: 150px; height: 150px;" alt="No image found" />
                                <div class="text-center"><small>Best view size: 320 x 480 px</small></div>
                            </div>


                    </div>
                </div>





                <?php if (!$agentid) : ?>

                    <div class="form-group form-group-sm">
                        <label for="password" class="control-label ">Password: <?php if (empty($agentid)):?><span class="error">*</span><?php endif;?> </label>
                        <div class="">
                            <input type="password" id="password" class="form-control" name="password"  maxlength="<?php echo $maxlength; ?>" value="" autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <label for="password_re" class="control-label">Retype Password: <?php if (empty($agentid)):?><span class="error">*</span><?php endif;?></label>
                        <div class="">
                            <input type="password" id="password_re" class="form-control" name="password_re"  maxlength="<?php echo $maxlength; ?>" value="" autocomplete="off">
                        </div>
                    </div>

                <?php endif; ?>

                <?php if ($is_telebank) : ?>

                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-lg-6">
                            <div class="form-group form-group-sm">
                                <label for="web_password" class="control-label"> TeleBanking password: </label>
                                <div class="">
                                    <input type="password" id="web_password" class="form-control" name="web_password" size="30" maxlength="25" value="" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-lg-6">
                            <div class="form-group form-group-sm">
                                <label for="web_password_re" class="control-label "> Retype TeleBanking password:  </label>
                                <div class="">
                                    <input type="password" id="web_password_re" class="form-control" name="web_password_re" size="30" maxlength="25" value="" />
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>

                <div class="form-group form-group-sm">

                    <label for="password_re" class="control-label "> Date of Birth:  </label>

                    <div class="row">

                            <div class="col-md-6 col-sm-6 col-lg-6">
                                <select name="bmonth" class="form-control">
                                    <option value="">Select Month</option>
                                    <?php for ($i=1; $i <= 12; $i++) {
                                        $j = sprintf("%02d", $i);
                                        echo '<option value="' . $j . '"';
                                        if ($agent->bmonth == $j) echo ' selected';
                                        echo '>'.date("F", strtotime("2012-$i-01")).'</option>';
                                    } ?>
                                </select>
                            </div>
                            <div class="col-md-6 col-sm-6 col-lg-6">
                                <select name="bday" class="form-control">
                                    <option value="">Select Day</option>
                                    <?php for ($i=1; $i <= 31; $i++) {
                                        $j = sprintf("%02d", $i);
                                        echo '<option value="' . $j . '"';
                                        if ($agent->bday == $j) echo ' selected';
                                        echo '>' . $j . '</option>';

                                    } ?>
                                </select>
                            </div>


                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-sm-6 col-lg-6">
                        <div class="form-group form-group-sm">
                            <label for="web_password_re" class="control-label "> DID:  </label>
                            <div class="">
                                <input type="text" name="did" class="form-control" maxlength="10" value="<?php echo $agent->did;?>" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-6 col-lg-6">
                        <div class="form-group form-group-sm">
                            <label for="web_password_re" class="control-label "> Telephone:  </label>
                            <div class="">
                                <input type="text" name="telephone" class="form-control" maxlength="12" value="<?php echo $agent->telephone;?>" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-sm-6 col-lg-6">
                        <div class="form-group form-group-sm">
                            <label for="max_chat_session" class="control-label "> Agent Type:  </label>
                            <div class="">
                                <select <?php echo UserAuth::getRoleID()!="R"?'disabled="disabled" ':'';?> name="usertype" class="form-control">
                                    <option <?php echo $agent->usertype=="A"?' selected="selected"':"";?> value="A">Agent</option>
                                    <option <?php echo $agent->usertype=="S"?' selected="selected"':"";?> value="S">Supervisor</option>
                                    <option <?php echo $agent->usertype=="P"?' selected="selected"':"";?> value="P">Report</option>
                                    <option <?php echo $agent->usertype=="G"?' selected="selected"':"";?> value="G">Digicon Report</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-6 col-lg-6">
                        <div class="form-group form-group-sm">
                            <label for="altid" class="control-label "> Alternative ID:  </label>
                            <div class="">
                                <input type="text" name="altid" class="form-control" maxlength="9" value="<?php echo $altid;?>" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-sm-6 col-lg-6">
                        <div class="form-group form-group-sm">
                            <label for="max_chat_session" class="control-label "> Max Chat Session:  </label>
                            <div class="">
                                <input type="text" name="max_chat_session" class="form-control" maxlength="2" value="<?php echo $agent->max_chat_session;?>" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-6 col-lg-6">
                        <div class="form-group form-group-sm">
                            <label for="chat_session_limit_with_call" class="control-label "> Chat Session Limit With Call:  </label>
                            <div class="">
                                <input type="text" name="chat_session_limit_with_call" class="form-control" maxlength="1" value="<?php echo $agent->chat_session_limit_with_call;?>" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-lg-6">
                        <div class="form-group form-group-sm">
                            <label for="role_id" class="control-label ">Role Id: <span class="error">*</span></label>
                            <div class="">
                                <select id="role_id" name="role_id" class="form-control">
	                            	<option value="">---Select---</option>
	                            	<?php foreach ($role_list as $key => $item) { ?>
	                            		<option value="<?php echo $item->id; ?>" <?php echo (isset($agent->role_id) && $agent->role_id == $item->id) ? 'selected="selected"' : ''; ?> ><?php echo $item->name; ?></option>
	                            	<?php } ?>
	                        	</select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-6 col-lg-6">
                        <div class="form-group form-group-sm">
                            <label for="screen_logger" class="control-label ">Screen Logger: <span class="error">*</span></label>
                            <div class="">
                                <select id="screen_logger" name="screen_logger" class="form-control">
                                    <option value="">---Select---</option>
                                    <option value="Y" <?php echo !empty($agent->screen_logger) && $agent->screen_logger == 'Y' ? 'selected' : ''?>>Enable</option>
                                    <option value="N" <?php echo !empty($agent->screen_logger) && $agent->screen_logger == 'N' ? 'selected' : ''?>>Disable</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-sm-6 col-lg-6">
                        <div class="form-group form-group-sm">
                            <label for="ob_call" class="control-label ">Outbound Call Permission: <span class="error">*</span></label>
                            <div class="">
                                <select id="ob_call" name="ob_call" class="form-control">
                                    <option value="">---Select---</option>
                                    <option value="Y" <?php echo !empty($agent->ob_call) && $agent->ob_call == 'Y' ? 'selected' : ''?>>Enable</option>
                                    <option value="N" <?php echo !empty($agent->ob_call) && $agent->ob_call == 'N' ? 'selected' : ''?>>Disable</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">Language Proficiency</div>
        <div class="panel-body">
            <?php
            $i_max = count($languages) > 3 ? 3 : count($languages);

            for ($i=1;$i<=$i_max;$i++) { ?>

                <div class="col-md-6 col-sm-6 col-lg-6">
                    <div class="form-group form-group-sm">
                        <label for="language_<?php echo $i; ?>" class="control-label "> Priority <?php echo $i;?>: </label>
                        <div class="">
                            <select name="language_<?php echo $i;?>" class="form-control">
                                <option value="">Select</option>
                                <?php foreach ($languages as $language): ?>
                                    <option value="<?php echo $language->lang_key;?>"<?php if ($language->lang_key==$agent->{'language_'.$i}) echo ' selected'; ?>><?php echo $language->lang_title;?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php    for (;$i<=3;$i++) {
                echo '<input type="hidden" name="language_'.$i.'" value="" />';
            } ?>

            <div class="col-md-12 col-sm-12 col-lg-12">
                <input class="btn btn-success pull-right" type="submit" value="<?php if (!empty($agentid)):?>Update<?php else:?>Add<?php endif;?>  " name="submitagent" >
            </div>
        </div>
    </div>
</form>

<script>
    $(function () {
        $("body").on("click",".profile-picture",function(){
            $("#agentImage").trigger("click");
        });
    });
</script>

