<?php
$viewable_skills = !empty($settings_data['viewable_priority_skill']->value) ? explode(",", $settings_data['viewable_priority_skill']->value) : [];
?>
<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>

<link href="ccd/select2/4.0.12/select2.min.css" rel="stylesheet" />
<script src="ccd/select2/4.0.12/select2.min.js"></script>

<form id="frm_email_settings" class="form-horizontal" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
	<div class="panel-body">
	    <div class="row">
	        <div class="col-md-12 col-sm-12">                
	            <?php if(isset($errMsg) && !empty($errMsg)){ ?>
	            <div class="alert <?php if ($errType === 0){ ?>alert-success <?php }else{ ?>alert-danger <?php } ?> alert-dismissible" role="alert">
	                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	                <strong><?php if ($errType === 0){ ?>Success<?php }else{ ?>Error<?php } ?>!</strong> <?=$errMsg?>
	            </div>
	            <?php } ?>
	        </div>
	    </div>
	    <div class="row">
	    	<div class="col-md-6 col-sm-6">
	            <div class="form-group form-group-sm mb-15">
	                <label for="attachment_save_path" class="control-label col-md-3 col-sm-3 pr-0">
	                	Attachment save path:
	                </label>
	                <div class="col-md-9 col-sm-9">
	                    <input type="text" id="attachment_save_path" name="attachment_save_path" class="form-control" value="<?php echo (isset($settings_data['attachment_save_path'])) ? base64_decode($settings_data['attachment_save_path']->value) : ''; ?>">
	                </div>
	            </div>
	        </div>
	        <div class="col-md-6 col-sm-6">
	            <div class="form-group form-group-sm mb-15">
	                <label for="replace_text_pattern" class="control-label col-md-3 col-sm-3 pr-0">
	                	Replace text pattern:
	                </label>
	                <div class="col-md-9 col-sm-9">
	                    <input type="text" id="replace_text_pattern" name="replace_text_pattern" class="form-control" value="<?php echo (isset($settings_data['replace_text_pattern'])) ? $settings_data['replace_text_pattern']->value : ''; ?>">
	                </div>
	            </div>	            
	        </div>
        </div>
        <hr>
        <div class="row">	        
	        <div class="col-md-6 col-sm-6">
	            <div class="form-group form-group-sm mb-15">
	                <label for="form_name" class="control-label col-md-3 col-sm-3 pr-0">
	                	Form Name:
	                </label>
	                <div class="col-md-9 col-sm-9">
	                    <input type="text" id="form_name" name="form_name" class="form-control" value="<?php echo (isset($settings_data['form_name'])) ? $settings_data['form_name']->value : ''; ?>">
	                </div>
	            </div>
	            <div class="form-group form-group-sm mb-15">
	                <label for="smtp_host" class="control-label col-md-3 col-sm-3 pr-0">
	                	SMTP Host:
	                </label>
	                <div class="col-md-9 col-sm-9">
	                    <input type="text" id="smtp_host" name="smtp_host" class="form-control" value="<?php echo (isset($settings_data['smtp_host'])) ? $settings_data['smtp_host']->value : ''; ?>">
	                </div>
	            </div>
	            <div class="form-group form-group-sm mb-15">
	                <label for="smtp_username" class="control-label col-md-3 col-sm-3 pr-0">
	                	SMTP Username:
	                </label>
	                <div class="col-md-9 col-sm-9">
	                    <input type="text" id="smtp_username" name="smtp_username" class="form-control" value="<?php echo (isset($settings_data['smtp_username'])) ? $settings_data['smtp_username']->value : ''; ?>">
	                </div>
	            </div>	            
	            <div class="form-group form-group-sm mb-15">
	                <label for="smtp_secure_opton" class="control-label col-md-3 col-sm-3 pr-0">
	                	SMTP Secure Option:
	                </label>
	                <div class="col-md-9 col-sm-9">
	                    <select id="smtp_secure_opton" name="smtp_secure_opton" class="form-control" >
							<option value="ssl" <?php if (isset($settings_data['smtp_secure_opton']) && $settings_data['smtp_secure_opton']->value == 'ssl') echo ' selected="selected"'; ?> >SSL</option>
							<option value="tls" <?php if (isset($settings_data['smtp_secure_opton']) && $settings_data['smtp_secure_opton']->value == 'tls') echo ' selected="selected"'; ?> >TLS</option>
						</select>
	                </div>
	            </div>
	        </div>
	        <div class="col-md-6 col-sm-6">
	            <div class="form-group form-group-sm mb-15">
	                <label for="form_email" class="control-label col-md-3 col-sm-3 pr-0">
	                	Form Email:
	                </label>
	                <div class="col-md-9 col-sm-9">
	                    <input type="text" id="form_email" name="form_email" class="form-control" value="<?php echo (isset($settings_data['form_email'])) ? $settings_data['form_email']->value : ''; ?>" data-rule-email="true", data-msg-email="Please enter valid email address!">
	                </div>
	            </div>
	            <div class="form-group form-group-sm mb-15">
	                <label for="smtp_port" class="control-label col-md-3 col-sm-3 pr-0">
	                	SMTP Port:
	                </label>
	                <div class="col-md-9 col-sm-9">
	                    <input type="text" id="smtp_port" name="smtp_port" class="form-control" value="<?php echo (isset($settings_data['smtp_port'])) ? $settings_data['smtp_port']->value : ''; ?>">
	                </div>
	            </div>
	            <div class="form-group form-group-sm mb-15">
	                <label for="smtp_password" class="control-label col-md-3 col-sm-3 pr-0">
	                	SMTP Password:
	                </label>
	                <div class="col-md-9 col-sm-9">
	                    <input type="password" id="smtp_password" class="form-control" name="smtp_password" value="<?php echo (isset($settings_data['smtp_password'])) ? $settings_data['smtp_password']->value : ''; ?>">
	                </div>
	            </div>

                <div class="form-group form-group-sm mb-15">
                    <label for="viewable_priority_skill" class="control-label col-md-3 col-sm-3 pr-0">
                        Priorioty Email Showable Skills:
                    </label>
                    <div class="col-md-9 col-sm-9">
                        <select id="viewable_priority_skill" name="viewable_priority_skill[]" class="select2 form-control" multiple="multiple">
                            <option value="">Select</option>
                            <?php if (!empty($skills)) { ?>
                                <?php foreach ($skills as $key){ ?>
                                        <option value="<?php echo $key->skill_id?>" <?php echo !empty($viewable_skills) && in_array($key->skill_id, $viewable_skills) ? "selected='selected'":"" ?> ><?php echo $key->skill_name?></option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                        <span id="helpBlock" class="help-block">Only use when set priority emails</span>
                    </div>
                </div>

                <div class="form-group form-group-sm mb-15">
                    <label for="priority_email_skill" class="control-label col-md-3 col-sm-3 pr-0">
                        Priorioty Email Skill:
                    </label>
                    <div class="col-md-9 col-sm-9">
                        <select id="priority_email_skill" name="priority_email_skill" class="form-control">
                            <option value="">Select</option>
                            <?php if (!empty($skills)) { ?>
                                <?php foreach ($skills as $key){ ?>
                                    <option value="<?php echo $key->skill_id?>" <?php echo isset($settings_data['priority_email_skill']) && $settings_data['priority_email_skill']->value==$key->skill_id ? "selected='selected'":"" ?> ><?php echo $key->skill_name?></option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                        <span id="helpBlock" class="help-block">priority emails set on this skill.</span>
                    </div>
                </div>

                <div class="form-group form-group-sm mb-15">
                    <label for="priority_email" class="control-label col-md-3 col-sm-3 pr-0">
                        Priority Emails:
                    </label>
                    <div class="col-md-9 col-sm-9">
                        <select id="priority_email" name="priority_email[]" class="form-control" multiple="" data-select2-id="10" tabindex="-1" aria-hidden="true">
                            <?php if (!empty($settings_data['priority_email']->value)) {
                                $settings_data['priority_email'] = explode(",", $settings_data['priority_email']->value); ?>
                                <?php foreach ($settings_data['priority_email'] as $key => $email) { ?>
                                    <option value="<?php echo $email?>" selected><?php echo $email?></option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                        <span id="helpBlock" class="help-block">Only for priority sender emails address.</span>
                    </div>
                </div>

	        </div>
	    </div>
		<div class="row">
        	<div class="col-md-12 col-sm-12 text-center">
	            <input class="btn btn-success form_submit_button" type="submit" value="Update" name="submitservice">
	        </div>
        </div>
	</div>
</form>

<script type="text/javascript">
	$( document ).ready(function() {
		$("#frm_email_settings").validate({
	        errorClass: "form-error",
	        errorElement: "span",
	    });

        $('.select2').select2();
        $("#priority_email").select2({
            tags: true,
            tokenSeparators: [',', ' '],
            createTag: function (params) {
                // Don't offset to create a tag if there is no @ symbol
                if (params.term.indexOf('@') === -1) {
                    return null;
                }
                return {
                    id: params.term,
                    text: params.term
                }
            }
        });
	});

</script>