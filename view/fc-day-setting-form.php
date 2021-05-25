<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>

<?php
$form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());

$opt='';
if(isset($day_setting_data->id) && !empty($day_setting_data->id)){
    $form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName()."&id=".$day_setting_data->id);
    $opt = $day_setting_data->id;
}

?>
<form id="frm_day_setting" class="form-horizontal" enctype="multipart/form-data" class="form" action="<?php echo $form_url; ?>" method="post" autocomplete="off">
	<input type="hidden" name="id" value="<?php echo (isset($day_setting_data->id) && !empty($day_setting_data->id)) ? $day_setting_data->id : ''; ?>" />

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
                    <label for="type" class="control-label col-md-3 col-sm-3 pr-0">Type:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <select id="type" name="type" class="form-control <?php echo isset($error_data['type']) ? 'form-error' : ''; ?>" data-rule-required='true' data-msg-required='Type is required!'>
                            <option value="">---Select---</option>
                            <?php foreach ($type_list as $key => $value) { ?>
                            <option value="<?php echo $key; ?>" <?php echo (isset($day_setting_data->type) && $day_setting_data->type == $key) ? 'selected="selected"' : ''; ?> ><?php echo $value; ?></option>
                            <?php } ?>
                        </select>
                        <?php 
                        if(isset($error_data['type']) && isset($error_data['type'][0])){ 
                        ?>
                            <span class="form-error"><?=$error_data['type'][0]?></span>
                        <?php } ?>
                    </div>
                </div> 
                <div class="form-group form-group-sm mb-15">
                    <label for="day_id" class="control-label col-md-3 col-sm-3 pr-0">Name:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <select id="day_id" name="day_id" class="form-control <?php echo isset($error_data['day_id']) ? 'form-error' : ''; ?>" data-rule-required='true' data-msg-required='Name is required!'>
                            <option value="">---Select---</option>
                            <?php
                            if(!empty($day_setting_data->type) && !empty($day_setting_data->day_id)){                                
                                foreach ($day_names[$day_setting_data->type] as $key => $item) { 
                            ?>
                            <option value="<?php echo $key; ?>" <?php echo (isset($day_setting_data->day_id) && $day_setting_data->day_id == $key) ? 'selected="selected"' : ''; ?> ><?php echo $item; ?></option>
                            <?php 
                                }
                            }
                            ?>
                        </select>
                        <?php 
                        if(isset($error_data['day_id']) && isset($error_data['day_id'][0])){ 
                        ?>
                            <span class="form-error"><?=$error_data['day_id'][0]?></span>
                        <?php } ?>
                    </div>
                </div> 
                <div class="form-group form-group-sm mb-15">
                    <label for="mdate" class="control-label col-md-3 col-sm-3 pr-0">Date:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <input id="mdate" type="text" name="mdate"  
                            class="form-control <?php echo isset($error_data['mdate']) ? 'form-error' : ''; ?>"
                            data-rule-required='true' 
                            data-msg-required='Date is required!'
                            value="<?php echo (isset($day_setting_data->mdate) && !empty($day_setting_data->mdate)) ? $day_setting_data->mdate : ''; ?>"
                        >
                        <?php 
                        if(isset($error_data['mdate']) && isset($error_data['mdate'][0])){ 
                        ?>
                            <span class="form-error"><?=$error_data['mdate'][0]?></span>
                        <?php } ?>
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15">
                    <label for="l_window" class="control-label col-md-3 col-sm-3 pr-0">Lower Window:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <input id="l_window" type="number" name="l_window"  
                            class="form-control <?php echo isset($error_data['l_window']) ? 'form-error' : ''; ?>"
                            data-rule-required='true' 
                            data-msg-required='Lower Window is required!'
                            data-rule-min=0
                            data-msg-min='Please enter a value greater than or equal to 0!'
                            data-rule-max=9
                            data-msg-max='Please enter a value less than or equal to 9!'
                            value="<?php echo isset($day_setting_data->l_window) ? $day_setting_data->l_window : ''; ?>"
                        >
                        <?php 
                        if(isset($error_data['l_window']) && isset($error_data['l_window'][0])){ 
                        ?>
                            <span class="form-error"><?=$error_data['l_window'][0]?></span>
                        <?php } ?>
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15">
                    <label for="u_window" class="control-label col-md-3 col-sm-3 pr-0">Upper Window:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <input id="u_window" type="number" name="u_window"  
                            class="form-control <?php echo isset($error_data['u_window']) ? 'form-error' : ''; ?>"
                            data-rule-required='true' 
                            data-msg-required='Upper Window is required!'
                            data-rule-min=0
                            data-msg-min='Please enter a value greater than or equal to 0!'
                            data-rule-max=9
                            data-msg-max='Please enter a value less than or equal to 9!'
                            value="<?php echo isset($day_setting_data->u_window) ? $day_setting_data->u_window : ''; ?>"
                        >
                        <?php 
                        if(isset($error_data['u_window']) && isset($error_data['u_window'][0])){ 
                        ?>
                            <span class="form-error"><?=$error_data['u_window'][0]?></span>
                        <?php } ?>
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15">
                    <label for="priority" class="control-label col-md-3 col-sm-3 pr-0">Priority:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <input id="priority" type="number" name="priority"  
                            class="form-control <?php echo isset($error_data['priority']) ? 'form-error' : ''; ?>"
                            data-rule-required='true' 
                            data-msg-required='Priority is required!'
                            data-rule-min=1
                            data-msg-min='Please enter a value greater than or equal to 1!'
                            data-rule-max=100
                            data-msg-max='Please enter a value less than or equal to 100!'
                            value="<?php echo isset($day_setting_data->priority) ? $day_setting_data->priority : ''; ?>"
                        >
                        <?php 
                        if(isset($error_data['priority']) && isset($error_data['priority'][0])){ 
                        ?>
                            <span class="form-error"><?=$error_data['priority'][0]?></span>
                        <?php } ?>
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15">
                    <label for="status" class="control-label col-md-3 col-sm-3 pr-0">Status:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <select id="status" name="status" class="form-control <?php echo isset($error_data['status']) ? 'form-error' : ''; ?>" data-rule-required='true' data-msg-required='Status is required!'>
                            <option value="">---Select---</option>
                            <?php foreach ($status_list as $key => $value) { ?>
                            <option value="<?php echo $key; ?>" <?php echo (isset($day_setting_data->status) && $day_setting_data->status == $key) ? 'selected="selected"' : ''; ?> ><?php echo $value; ?></option>
                            <?php } ?>
                        </select>
                        <?php 
                        if(isset($error_data['status']) && isset($error_data['status'][0])){ 
                        ?>
                            <span class="form-error"><?=$error_data['status'][0]?></span>
                        <?php } ?>
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15 text-center">
                	<div class="col-md-3 col-sm-3"></div>
                	<div class="col-md-9 col-sm-9">
                		<input class="btn btn-success" type="submit" value="<?php echo (isset($day_setting_data->id) && !empty($day_setting_data->id)) ? 'Update' : 'Add'; ?>" name="submit" >
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
        var day_name_list = <?php echo json_encode($day_names);?>;
	    $('#frm_day_setting').validate({
            errorClass: "form-error",
            errorElement: "span",
        });

        $("#type").on("change", function(){
            var val = $(this).val();

            if(val && typeof(day_name_list[val]) !='undefined'){
                var str = '<option value="">---Select---</option>';
                $.each(day_name_list[val], function(idx, item){
                    str += '<option value="'+idx+'">'+item+'</option>';
                });

                $("#day_id").html(str);
            }else{
                var str = '<option value="">---Select---</option>';
                $("#day_id").html(str);
            }
        });

        jQuery('#mdate').datetimepicker({
            timepicker:false,
            format:'d/m/Y'
        });
	});
</script>