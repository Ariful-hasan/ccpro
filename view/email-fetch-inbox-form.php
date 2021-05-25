<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>

<link href="ccd/select2/select2.min.css" rel="stylesheet" />
<script src="ccd/select2/select2.min.js"></script>

<style>
    .select2-container--default {
        width: 100% !important;
        line-height: 120% !important;
    }
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #e1e1e1!important;
        border-radius: 2px;
    }
</style>

<?php
$form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());

if(isset($ef_inbox_data->id) && !empty($ef_inbox_data->id)){
    $form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName()."&id=".$ef_inbox_data->id);
}

$CRUD_list = get_CRUD_list();
?>
<form id="frm_ef_inbox" class="form-horizontal" enctype="multipart/form-data" class="form" action="<?php echo $form_url; ?>" method="post">
	<input type="hidden" name="id" value="<?php echo (isset($ef_inbox_data->id) && !empty($ef_inbox_data->id)) ? $ef_inbox_data->id : ''; ?>" />
	<div class="panel-body">
        <div class="row">
            <div class="col-md-12 col-sm-12">                
                <?php if(isset($errMsg) && !empty($errMsg)){ ?>
                <div class="alert <?php if ($errType === 0){ ?>alert-success <?php }else{ ?>alert-danger <?php } ?> alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>Error!</strong> <?=$errMsg?>
                </div>
                <?php } ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-sm-6">
                <div class="form-group form-group-sm mb-15">
                    <label for="name" class="control-label col-md-3 col-sm-3 pr-0">Name:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <input type="text" name="name"  class="form-control <?php echo isset($error_data['name']) ? 'form-error' : ''; ?>" maxlength="25" data-rule-required='true' data-msg-required='Name is required!' data-rule-maxlength='25' data-msg-maxlength='Please enter no more than 25 characters!' value="<?php echo (isset($ef_inbox_data->name) && !empty($ef_inbox_data->name)) ? $ef_inbox_data->name : ''; ?>" />
                        <?php 
                        if(isset($error_data['name'])){ 
                            foreach ($error_data['name'] as $key => $value) {
                        ?>
                            <span class="form-error"><?=$value?></span>
                        <?php 
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15">
                    <label for="host" class="control-label col-md-3 col-sm-3 pr-0">Host:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <input type="text" name="host"  class="form-control <?php echo isset($error_data['host']) ? 'form-error' : ''; ?>" maxlength="50" data-rule-required='true' data-msg-required='Host is required!' data-rule-maxlength='50' data-msg-maxlength='Please enter no more than 50 characters!' value="<?php echo (isset($ef_inbox_data->host) && !empty($ef_inbox_data->host)) ? $ef_inbox_data->host : ''; ?>" />
                        <?php 
                        if(isset($error_data['host'])){ 
                            foreach ($error_data['host'] as $key => $value) {
                        ?>
                            <span class="form-error"><?=$value?></span>
                        <?php 
                            }
                        }
                        ?>
                    </div>
                </div>

                <div class="form-group form-group-sm mb-15">
                    <label for="username" class="control-label col-md-3 col-sm-3 pr-0">Username:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <input type="text" name="username"  class="form-control <?php echo isset($error_data['username']) ? 'form-error' : ''; ?>" maxlength="35" data-rule-required='true' data-msg-required='Username is required!' data-rule-maxlength='35' data-msg-maxlength='Please enter no more than 35 characters!' value="<?php echo (isset($ef_inbox_data->username) && !empty($ef_inbox_data->username)) ? $ef_inbox_data->username : ''; ?>" />
                        <?php 
                        if(isset($error_data['username'])){ 
                            foreach ($error_data['username'] as $key => $value) {
                        ?>
                            <span class="form-error"><?=$value?></span>
                        <?php 
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15">
                    <label for="fetch_method" class="control-label col-md-3 col-sm-3 pr-0">Fetch method:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <select id="fetch_method" name="fetch_method" class="form-control <?php echo isset($error_data['fetch_method']) ? 'form-error' : ''; ?>" data-rule-required='true' data-msg-required='Fetch method is required!'>
                        	<option value="">---Select---</option>
                        	<?php foreach ($fetch_method_list as $key => $value) { ?>
                        	<option value="<?php echo $key; ?>" <?php echo (isset($ef_inbox_data->fetch_method) && $ef_inbox_data->fetch_method == $key) ? 'selected="selected"' : ''; ?> ><?php echo $value; ?></option>
                        	<?php } ?>
                        </select>
                        <?php 
                        if(isset($error_data['fetch_method'])){ 
                            foreach ($error_data['fetch_method'] as $key => $value) {
                        ?>
                            <span class="form-error"><?=$value?></span>
                        <?php 
                            }
                        }
                        ?>
                    </div>
                </div>
                <?php
                $checkedYes = "";
                $checkedNo = "";
                if(isset($ef_inbox_data->email_delete) && $ef_inbox_data->email_delete == MSG_YES){
                    $checkedYes = 'checked="checked"';
                }else{
                    $checkedNo = 'checked="checked"';
                }
                ?>

                <div class="form-group form-group-sm mb-15">
                    <label for="skill_id" class="control-label col-md-3 col-sm-3 pr-0">Skill:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <select id="skill_id" name="skill_id" class="form-control <?php echo isset($error_data['skill_id']) ? 'form-error' : ''; ?>" data-rule-required='true' data-msg-required='Skill is required!'>
                            <option value="">---Select---</option>
                            <?php
                            if (is_array($skills)) {
                                foreach ($skills as $key => $skill) {
                                    if (isset($skill->active) && $skill->active == 'Y'){
                                        ?>
                                        <option data-skill_name="<?php echo $skill->skill_name?>" value="<?php echo $skill->skill_id; ?>" <?php echo (isset($ef_inbox_data->skill_id) && $ef_inbox_data->skill_id == $skill->skill_id) ? 'selected="selected"' : ''; ?> ><?php echo $skill->skill_name; ?></option>
                                        <?php
                                    }
                                }
                            }
                            ?>
                        </select>
                        <?php
                        if(isset($error_data['skill_id'])){
                            foreach ($error_data['skill_id'] as $key => $value) {
                                ?>
                                <span class="form-error"><?=$value?></span>
                                <?php
                            }
                        }
                        ?>
                        <input type="hidden" id="skill_name" name="skill_name" value="<?php echo !empty($ef_inbox_data->skill_name) ? $ef_inbox_data->skill_name : ''; ?>">
                    </div>
                </div>

                <div class="form-group form-group-sm mb-15">
                    <label for="email_delete" class="control-label col-md-3 col-sm-3 pr-0">Email Delete:</label>
                    <div class="col-md-9 col-sm-9">
                        <div class="checkbox-inline language-check">
                            <label class="radio-container">Yes
                                <input id="email_delete_yes" name="email_delete" type="radio" value="<?=MSG_YES?>" <?=$checkedYes?> >
                                <span class="radio-checkmark"></span>
                            </label>
                            <label class="radio-container">No
                                <input id="email_delete_no" name="email_delete" type="radio" value="<?=MSG_NO?>" <?=$checkedNo?> >
                                <span class="radio-checkmark"></span>
                            </label>
                        </div>
                        <span class="help-block">After fetch email, those email is delete or not.</span>
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15">
                    <label for="port" class="control-label col-md-3 col-sm-3 pr-0">Port:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <input type="text" name="port"  class="form-control <?php echo isset($error_data['port']) ? 'form-error' : ''; ?>" maxlength="5" data-rule-required='true' data-msg-required='Port is required!' data-rule-maxlength='5' data-msg-maxlength='Please enter no more than 5 characters!' value="<?php echo (isset($ef_inbox_data->port) && !empty($ef_inbox_data->port)) ? $ef_inbox_data->port : ''; ?>" />
                        <?php
                        if(isset($error_data['port'])){
                            foreach ($error_data['port'] as $key => $value) {
                                ?>
                                <span class="form-error"><?=$value?></span>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-6 col-lg-6">
                <div class="form-group form-group-sm mb-15">
                    <label for="password" class="control-label col-md-3 col-sm-3 pr-0">Password:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <input type="password" name="password"  class="form-control <?php echo isset($error_data['password']) ? 'form-error' : ''; ?>" maxlength="48" data-rule-required='true' data-msg-required='Port is required!' data-rule-maxlength='48' data-msg-maxlength='Please enter no more than 48 characters!' value="<?php echo (isset($ef_inbox_data->password) && !empty($ef_inbox_data->password)) ? $ef_inbox_data->password : ''; ?>" />
                        <?php 
                        if(isset($error_data['password'])){ 
                            foreach ($error_data['password'] as $key => $value) {
                        ?>
                            <span class="form-error"><?=$value?></span>
                        <?php 
                            }
                        }
                        ?>
                    </div>
                </div> 

                <div class="form-group form-group-sm mb-15">
                    <label for="status" class="control-label col-md-3 col-sm-3 pr-0">Status:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <select id="status" name="status" class="form-control <?php echo isset($error_data['status']) ? 'form-error' : ''; ?>" data-rule-required='true' data-msg-required='Status is required!'>
                            <option value="">---Select---</option>
                            <?php foreach ($status_list as $key => $value) { ?>
                            <option value="<?php echo $key; ?>" <?php echo (isset($ef_inbox_data->status) && $ef_inbox_data->status == $key) ? 'selected="selected"' : ''; ?> ><?php echo $value; ?></option>
                            <?php } ?>
                        </select>
                        <?php 
                        if(isset($error_data['status'])){ 
                            foreach ($error_data['status'] as $key => $value) {
                        ?>
                            <span class="form-error"><?=$value?></span>
                        <?php 
                            }
                        }
                        ?>
                    </div>
                </div>

                <div class="form-group form-group-sm mb-15">
                    <label for="in_kpi_time" class="control-label col-md-3 col-sm-3 pr-0">In Kpi Time:</label>
                    <div class="col-md-9 col-sm-9">
                        <input type="text" name="in_kpi_time"  class="form-control <?php echo isset($error_data['in_kpi_time']) ? 'form-error' : ''; ?>" maxlength="10"  data-rule-maxlength='10' data-msg-maxlength='Please enter no more than 10 digits!' value="<?php echo (isset($ef_inbox_data->in_kpi_time) && !empty($ef_inbox_data->in_kpi_time)) ? $ef_inbox_data->in_kpi_time : ''; ?>" />
                        <?php
                        if(isset($error_data['in_kpi_time'])){
                            foreach ($error_data['in_kpi_time'] as $key => $value) {
                                ?>
                                <span class="form-error"><?=$value?></span>
                                <?php
                            }
                        }
                        ?>
                        <span class="help-block">Only integer(sec). No need if you want global IN KPI time.</span>
                    </div>
                </div>

                <div class="form-group form-group-sm mb-15">
                    <label for="status" class="control-label col-md-3 col-sm-3 pr-0">Show Email List:</label>
                    <div class="col-md-9 col-sm-9">
                        <select id="show_email_list" name="show_email_list" class="form-control <?php echo isset($error_data['show_email_list']) ? 'form-error' : ''; ?>" data-rule-required='false'>
                            <option value="">---Select---</option>
                            <?php foreach ($yes_no_list as $key => $value) { ?>
                                <option value="<?php echo $key; ?>" <?php echo (isset($ef_inbox_data->show_email_list) && $ef_inbox_data->show_email_list == $key) ? 'selected="selected"' : ''; ?> ><?php echo $value; ?></option>
                            <?php } ?>
                        </select>
                        <?php
                        if(isset($error_data['show_email_list'])){
                            foreach ($error_data['show_email_list'] as $key => $value) {
                                ?>
                                <span class="form-error"><?=$value?></span>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15">
                    <label for="name" class="control-label col-md-3 col-sm-3 pr-0">Title:</label>
                    <div class="col-md-9 col-sm-9">
                        <input type="text" name="title"  class="form-control <?php echo isset($error_data['title']) ? 'form-error' : ''; ?>" maxlength="30"   data-rule-maxlength='30' data-msg-maxlength='Please enter no more than 30 characters!' value="<?php echo (isset($ef_inbox_data->title) && !empty($ef_inbox_data->title)) ? $ef_inbox_data->title : ''; ?>" />
                        <?php
                        if(isset($error_data['title'])){
                            foreach ($error_data['title'] as $key => $value) {
                                ?>
                                <span class="form-error"><?=$value?></span>
                                <?php
                            }
                        }
                        ?>
                        <span class="help-block">Should Be filled when 'show email list' is YES.</span>
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15">
                    <label for="status" class="control-label col-md-3 col-sm-3 pr-0">Title Viewable Skills:</label>
                    <div class="col-md-9 col-sm-9">
                        <select id="viewable_skills" name="viewable_skills[]" class="select2 form-control <?php echo isset($error_data['viewable_skills']) ? 'form-error' : ''; ?>" data-rule-required='false' multiple="multiple">
                            <option value="">---Select---</option>
                            <?php
                            $viewable_list = !empty($ef_inbox_data->viewable_skills) ? explode(",",$ef_inbox_data->viewable_skills) : [];
                            if (is_array($skills)) {
                                foreach ($skills as $key => $skill) {
                                    if (isset($skill->active) && $skill->active == 'Y'){
                                        ?>
                                        <option  value="<?php echo $skill->skill_id; ?>" <?php echo (isset($viewable_list) && in_array($skill->skill_id, $viewable_list)) ? 'selected="selected"' : ''; ?> ><?php echo $skill->skill_name; ?></option>
                                        <?php
                                    }
                                }
                            }
                            ?>
                        </select>
                        <?php
                        if(isset($error_data['viewable_skills'])){
                            foreach ($error_data['viewable_skills'] as $key => $value) {
                                ?>
                                <span class="form-error"><?=$value?></span>
                                <?php
                            }
                        }
                        ?>
                        <span class="help-block">Should Be filled when 'show email list' is YES.</span>
                    </div>
                    </div>

                    <div class="form-group form-group-sm ">
                        <label for="status" class="control-label col-md-3 col-sm-3 pr-0">Email Row Color:</label>
                        <div class="col-md-9 col-sm-9">
                            <select id="inbox_row_color" name="inbox_row_color" class=" form-control <?php echo isset($error_data['inbox_color']) ? 'form-error' : ''; ?>" data-rule-required='false'>
                                <option value="">Select</option>
                                <?php if (!empty($inbox_row_color)){ ?>
                                    <?php foreach ($inbox_row_color as $key => $val){ ?>
                                        <option value="<?php echo $key?>" <?php echo $ef_inbox_data->inbox_row_color==$key?"selected":""?>><?php echo $val?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                            <span class="help-block">Should Be filled when 'show email list' is YES.</span>
                        </div>
                    </div>
                </div>


            </div>
        </div>
        <div class="row">
        	<div class="col-md-12 col-sm-12 col-lg-12 text-center">
	            <input class="btn btn-success" type="submit" value="<?php echo (isset($ef_inbox_data->id) && !empty($ef_inbox_data->id)) ? 'Update' : 'Add'; ?>" name="submit" >
	        </div>
        </div>
    </div>
</form>

<script type="text/javascript">
	$( document ).ready(function() {
	    $('#frm_ef_inbox').validate({
            errorClass: "form-error",
            errorElement: "span",
        });
        $("#skill_id").on('change', function(event){
            //$("#skill_name").val($(this).val());
            var skill_name = $(this).find(':selected').data('skill_name');
            $("#skill_name").val(skill_name);
        });


        $('.select2').select2();
	});
</script>