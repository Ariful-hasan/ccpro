<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>

<?php
$form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());

$opt='';
if(isset($day_name_data->id) && !empty($day_name_data->id)){
    $form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName()."&id=".$day_name_data->id);
    $opt = $day_name_data->id;
}

?>
<form id="frm_day_name" class="form-horizontal" enctype="multipart/form-data" class="form" action="<?php echo $form_url; ?>" method="post" autocomplete="off">
	<input type="hidden" name="id" value="<?php echo (isset($day_name_data->id) && !empty($day_name_data->id)) ? $day_name_data->id : ''; ?>" />

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
                        <input type="text" name="name"  
                        	class="form-control <?php echo isset($error_data['name']) ? 'form-error' : ''; ?>" 
                        	maxlength="35" 
                        	data-rule-required='true' 
                        	data-msg-required='Name is required!' 
                        	data-rule-maxlength='35' 
                        	data-msg-maxlength='Please enter no more than 35 characters!' 
                        	value="<?php echo (isset($day_name_data->name) && !empty($day_name_data->name)) ? $day_name_data->name : ''; ?>" 
                        	data-rule-remote="<?php echo $this->url('task='.$request->getControllerName()."&act=unique-day-name&id=".$day_name_data->id); ?>"
                            data-msg-remote="Name already exists!"
                        >
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
                    <label for="type" class="control-label col-md-3 col-sm-3 pr-0">Type:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <select id="type" name="type" class="form-control <?php echo isset($error_data['type']) ? 'form-error' : ''; ?>" data-rule-required='true' data-msg-required='Type is required!'>
                            <option value="">---Select---</option>
                            <?php foreach ($type_list as $key => $value) { ?>
                            <option value="<?php echo $key; ?>" <?php echo (isset($day_name_data->type) && $day_name_data->type == $key) ? 'selected="selected"' : ''; ?> ><?php echo $value; ?></option>
                            <?php } ?>
                        </select>
                        <?php 
                        if(isset($error_data['type'])){ 
                            foreach ($error_data['type'] as $key => $value) {
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
                            <option value="<?php echo $key; ?>" <?php echo (isset($day_name_data->status) && $day_name_data->status == $key) ? 'selected="selected"' : ''; ?> ><?php echo $value; ?></option>
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
                <div class="form-group form-group-sm mb-15 text-center">
                	<div class="col-md-3 col-sm-3"></div>
                	<div class="col-md-9 col-sm-9">
                		<input class="btn btn-success" type="submit" value="<?php echo (isset($day_name_data->id) && !empty($day_name_data->id)) ? 'Update' : 'Add'; ?>" name="submit" >
                	</div>
                </div>
            </div>   
        </div>

        <div class="row">
        	<div class="col-md-12 col-sm-12">
	            
	        </div>
        </div>
    </div>
</form>

<script type="text/javascript">
	$( document ).ready(function() {
	    $('#frm_day_name').validate({
            errorClass: "form-error",
            errorElement: "span",
        });
	});
</script>