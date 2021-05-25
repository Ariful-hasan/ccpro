<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>
<?php
$form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());

if(isset($page_data->id) && !empty($page_data->id)){
    $form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName()."&id=".$page_data->id);
}

$CRUD_list = get_CRUD_list();
?>
<form id="frm_page" class="form-horizontal" enctype="multipart/form-data" class="form" action="<?php echo $form_url; ?>" method="post">
	<input type="hidden" name="id" value="<?php echo (isset($page_data->id) && !empty($page_data->id)) ? $page_data->id : ''; ?>" />
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
                        <input type="text" name="name"  class="form-control <?php echo isset($error_data['name']) ? 'form-error' : ''; ?>" maxlength="40" data-rule-required='true' data-msg-required='Name is required!' data-rule-maxlength='40' data-msg-maxlength='Please enter no more than 40 characters!' value="<?php echo (isset($page_data->name) && !empty($page_data->name)) ? $page_data->name : ''; ?>" />
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
                    <label for="path" class="control-label col-md-3 col-sm-3 pr-0">Path:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <input type="text" name="path"  class="form-control <?php echo isset($error_data['path']) ? 'form-error' : ''; ?>" maxlength="50" data-rule-required='true' data-msg-required='Path is required!' data-rule-maxlength='50' data-msg-maxlength='Please enter no more than 50 characters!' value="<?php echo (isset($page_data->path) && !empty($page_data->path)) ? $page_data->path : ''; ?>" />
                        <?php 
                        if(isset($error_data['path'])){ 
                            foreach ($error_data['path'] as $key => $value) {
                        ?>
                            <span class="form-error"><?=$value?></span>
                        <?php 
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15">
                    <label for="ajax_path" class="control-label col-md-3 col-sm-3 pr-0">Ajax Path:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <input type="text" name="ajax_path" class="form-control <?php echo isset($error_data['ajax_path']) ? 'form-error' : ''; ?>" maxlength="60" data-rule-required='false' data-msg-required='Ajax Path is required!' data-rule-maxlength='60' data-msg-maxlength='Please enter no more than 60 characters!' value="<?php echo (isset($page_data->ajax_path) && !empty($page_data->ajax_path)) ? $page_data->ajax_path : ''; ?>" />
                        <?php
                        if(isset($error_data['ajax_path'])){
                            foreach ($error_data['ajax_path'] as $key => $value) {
                                ?>
                                <span class="form-error"><?=$value?></span>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15">
                    <label for="icon" class="control-label col-md-3 col-sm-3 pr-0">Icon:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <input type="text" name="icon"  class="form-control <?php echo isset($error_data['icon']) ? 'form-error' : ''; ?>" maxlength="25" data-rule-required='true' data-msg-required='Icon is required!' data-rule-maxlength='25' data-msg-maxlength='Please enter no more than 25 characters!' value="<?php echo (isset($page_data->icon) && !empty($page_data->icon)) ? $page_data->icon : ''; ?>" />
                        <?php 
                        if(isset($error_data['icon'])){ 
                            foreach ($error_data['icon'] as $key => $value) {
                        ?>
                            <span class="form-error"><?=$value?></span>
                        <?php 
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15">
                    <label for="active_class" class="control-label col-md-3 col-sm-3 pr-0">Active Class:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <input type="text" name="active_class"  class="form-control <?php echo isset($error_data['active_class']) ? 'form-error' : ''; ?>" maxlength="50" data-rule-required='true' data-msg-required='Active Class is required!' data-rule-maxlength='50' data-msg-maxlength='Please enter no more than 50 characters!' value="<?php echo (isset($page_data->active_class) && !empty($page_data->active_class)) ? $page_data->active_class : ''; ?>" />
                        <?php 
                        if(isset($error_data['active_class'])){ 
                            foreach ($error_data['active_class'] as $key => $value) {
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
                        	<option value="<?php echo $key; ?>" <?php echo (isset($page_data->status) && $page_data->status == $key) ? 'selected="selected"' : ''; ?> ><?php echo $value; ?></option>
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
                    <label for="layout" class="control-label col-md-3 col-sm-3 pr-0">Layout:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <select id="layout" name="layout" class="form-control <?php echo isset($error_data['layout']) ? 'form-error' : ''; ?>" data-rule-required='true' data-msg-required='Layout is required!'>
                            <option value="">---Select---</option>
                            <?php foreach ($layout_list as $key => $value) { ?>
                            <option value="<?php echo $key; ?>" <?php echo (isset($page_data->layout) && $page_data->layout == $key) ? 'selected="selected"' : ''; ?> ><?php echo $value; ?></option>
                            <?php } ?>
                        </select>
                        <?php 
                        if(isset($error_data['layout'])){ 
                            foreach ($error_data['layout'] as $key => $value) {
                        ?>
                            <span class="form-error"><?=$value?></span>
                        <?php 
                            }
                        }
                        ?>
                    </div>
                </div>

                <?php /* ?>
                <div class="form-group form-group-sm mb-15">
                    <label for="access_code" class="control-label col-md-3 col-sm-3 pr-0">Access Code:</label>
                    <div class="col-md-9 col-sm-9">
                        <input type="text" name="access_code"  class="form-control" maxlength="25" value="<?php echo (isset($page_data->access_code) && !empty($page_data->access_code)) ? $page_data->access_code : ''; ?>" />
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15">
                    <label for="crud_function" class="control-label col-md-3 col-sm-3 pr-0">CRUD Function:</label>
                    <div class="col-md-9 col-sm-9">
                        <?php 
                        $crud_function = (isset($page_data->crud_function) && !empty($page_data->crud_function)) ? json_decode($page_data->crud_function, false) : [];
                        foreach($CRUD_list as $key=>$value){ 
                            $checked = '';
                            // $required = '';
                            if(in_array($key, $crud_function)){
                                $checked = 'checked="checked"';
                                // $required = 'required="true"';
                            }
                        ?>
                            <label class="checkbox-container"><?=$value?>                                      
                                <input name="crud_function[]" type="checkbox" value="<?=$key?>" <?=$checked?> >
                                <span class="checkmark"></span>
                            </label>
                        <?php } ?>
                    </div>
                </div>
                <?php */ ?>
                <?php
                $checkedYes = "";
                $checkedNo = "";
                if(isset($page_data->pop_out) && $page_data->pop_out == MSG_YES){
                    $checkedYes = 'checked="checked"';
                }else{
                    $checkedNo = 'checked="checked"';
                }
                ?>
                <div class="form-group form-group-sm mb-15">
                    <label for="pop_out" class="control-label col-md-3 col-sm-3 pr-0">Page pop out:</label>
                    <div class="col-md-9 col-sm-9">
                        <div class="checkbox-inline language-check">
                            <label class="radio-container">Yes
                                <input id="pop_out_yes" name="pop_out" type="radio" value="<?=MSG_YES?>" <?=$checkedYes?> >
                                <span class="radio-checkmark"></span>
                            </label>
                            <label class="radio-container">No
                                <input id="pop_out_no" name="pop_out" type="radio" value="<?=MSG_NO?>" <?=$checkedNo?> >
                                <span class="radio-checkmark"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div id="report_fields_div" class="form-group mb-15 <?php echo (isset($page_data->layout) && $page_data->layout == PAGE_REPORT) ? '' : 'hide'; ?>">
                    <label for="report_fields" class="control-label col-md-3 col-sm-3 pr-0">Report Field:</label>
                    <div class="col-md-9 col-sm-9">
                        <textarea name="report_fields" rows="4" class="form-control"><?php echo isset($page_data->report_fields) ? $page_data->report_fields : ''; ?></textarea>
                        <span class="help-block">example: field1,field2</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-6 col-lg-6">
                <div class="page-list">
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="headingOne">
                            <h4 class="panel-title">Page</h4>
                        </div>
                        <div class="panel-body panel-scroll">
                            <ul id="page_parent_list">
                                <?php echo $parent_page_list; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>       
        </div>
        <div class="row">
        	<div class="col-md-12 col-sm-12 col-lg-12 text-center">
	            <input class="btn btn-success" type="submit" value="<?php echo (isset($page_data->id) && !empty($page_data->id)) ? 'Update' : 'Add'; ?>" name="submit" >
	        </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    var report_fields = '<?php echo isset($page_data->report_fields) ? $page_data->report_fields : ''; ?>';
	$( document ).ready(function() {
	    $('#frm_page').validate({
            errorClass: "form-error",
            errorElement: "span",
        });

        $('#layout').on('change', function(){
            var value = $(this).val();

            if(value=='PRE'){
                $("#report_fields_div").removeClass('hide');
                $("#report_fields_div").find('textarea').text(report_fields);
            }else{
                $("#report_fields_div").addClass('hide');
                $("#report_fields_div").find('textarea').text('');
            }
        });
	});
</script>