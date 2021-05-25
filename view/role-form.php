<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>

<?php
$form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());

if(isset($role_data->id) && !empty($role_data->id)){
    $form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName()."&id=".$role_data->id);
}

$module_list = get_module_list();
$CRUD_list = get_CRUD_list();
?>
<form id="frm_role" class="form-horizontal" enctype="multipart/form-data" class="form" action="<?php echo $form_url; ?>" method="post">
	<input type="hidden" name="id" value="<?php echo (isset($role_data->id) && !empty($role_data->id)) ? $role_data->id : ''; ?>" />

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
                        <input type="text" name="name"  class="form-control <?php echo isset($error_data['name']) ? 'form-error' : ''; ?>" maxlength="25" data-rule-required='true' data-msg-required='Name is required!' data-rule-maxlength='25' data-msg-maxlength='Please enter no more than 25 characters!' value="<?php echo (isset($role_data->name) && !empty($role_data->name)) ? $role_data->name : ''; ?>" />
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
            </div>

            <div class="col-md-6 col-sm-6">
                <div class="form-group form-group-sm mb-15">
                    <label for="status" class="control-label col-md-3 col-sm-3 pr-0">Status:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <select id="status" name="status" class="form-control <?php echo isset($error_data['status']) ? 'form-error' : ''; ?>" data-rule-required='true' data-msg-required='Status is required!'>
                            <option value="">---Select---</option>
                            <?php foreach ($status_list as $key => $value) { ?>
                            <option value="<?php echo $key; ?>" <?php echo (isset($role_data->status) && $role_data->status == $key) ? 'selected="selected"' : ''; ?> ><?php echo $value; ?></option>
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
            </div>       
        </div>

        <div class="row">
            <div class="col-md-12 col-sm-12">
                <?php
                foreach ($module_list as $key => $value) {
                    if($key == MOD_AGENTS)
                        $CRUD_list_update = array_merge($CRUD_list, get_extra_CRUD_list());
                    else
                        $CRUD_list_update = $CRUD_list;
                ?>
                <div class="col-md-6 col-sm-6">
                    <div class="panel-box panel-box-solid">
                        <div class="panel-box-header with-border">
                          <h3 class="panel-box-title"><?=$value?></h3>
                        </div>
                        <div class="panel-box-body">
                            <div class="checkbox-inline language-check">
                                <?php
                                if((isset($role_data->id) && !empty($role_data->id))){
                                    foreach ($CRUD_list_update as $crud_key => $crud_item) {
                                        $checked = "";
                                        if(isset($role_data->access_info->$key))
                                            $checked = (in_array($crud_key, $role_data->access_info->$key)) ? 'checked="checked"' : '';
                                ?>
                                    <label class="checkbox-container"><?=$crud_item?>
                                        <input name="access_info[<?=$key?>][]" type="checkbox" value="<?=$crud_key?>" <?=$checked?> >
                                        <span class="checkbox-checkmark"></span>
                                    </label>
                                <?php 
                                    }
                                } else {
                                    foreach ($CRUD_list_update as $crud_key => $crud_item) {
                                ?>
                                    <label class="checkbox-container"><?=$crud_item?>
                                        <input name="access_info[<?=$key?>][]" type="checkbox" value="<?=$crud_key?>" checked="checked">
                                        <span class="checkbox-checkmark"></span>
                                    </label>
                                <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <div class="row">
        	<div class="col-md-12 col-sm-12 text-center">
	            <input class="btn btn-success" type="submit" value="<?php echo (isset($role_data->id) && !empty($role_data->id)) ? 'Update' : 'Add'; ?>" name="submit" >
	        </div>
        </div>
    </div>
</form>

<script type="text/javascript">
	$( document ).ready(function() {
	    $('#frm_role').validate({
            errorClass: "form-error",
            errorElement: "span",
        });
	});
</script>