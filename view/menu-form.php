<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>

<script type="text/javascript" src="js/bootbox/bootbox.min.js"></script>
<script type="text/javascript" src="js/jquery-nestable/jquery.nestable.min.js"></script>

<?php
$form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());
$menu_info = (isset($menu_data->menu_info) && !empty($menu_data->menu_info)) ? json_decode($menu_data->menu_info, true) : [];

if(isset($menu_data->id) && !empty($menu_data->id)){
    $form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName()."&id=".$menu_data->id);
}

?>
<form id="frm_menu" class="form-horizontal" enctype="multipart/form-data" class="form" action="<?php echo $form_url; ?>" method="post">
	<input type="hidden" name="id" value="<?php echo (isset($menu_data->id) && !empty($menu_data->id)) ? $menu_data->id : ''; ?>" />

    <input type="hidden" name="menu_info" id="menu_info" maxlength="100000" value="" />

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
                        <input type="text" name="name"  class="form-control <?php echo isset($error_data['name']) ? 'form-error' : ''; ?>" maxlength="25" data-rule-required='true' data-msg-required='Name is required!' data-rule-maxlength='25' data-msg-maxlength='Please enter no more than 25 characters!' value="<?php echo (isset($menu_data->name) && !empty($menu_data->name)) ? $menu_data->name : ''; ?>" />
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
                    <label for="status" class="control-label col-md-3 col-sm-3 pr-0">Status:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <select id="status" name="status" class="form-control <?php echo isset($error_data['status']) ? 'form-error' : ''; ?>" data-rule-required='true' data-msg-required='Status is required!'>
                        	<option value="">---Select---</option>
                        	<?php foreach ($status_list as $key => $value) { ?>
                        	<option value="<?php echo $key; ?>" <?php echo (isset($menu_data->status) && $menu_data->status == $key) ? 'selected="selected"' : ''; ?> ><?php echo $value; ?></option>
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

            <div class="col-md-6 col-sm-6">                
                <div class="form-group form-group-sm mb-15">
                    <label for="role_id" class="control-label col-md-3 col-sm-3 pr-0">Role:<span class='error'>*</span></label>
                    <div class="col-md-9 col-sm-9">
                        <select id="role_id" name="role_id" class="form-control <?php echo isset($error_data['role_id']) ? 'form-error' : ''; ?>" data-rule-required='true' data-msg-required='Role is required!'>
                            <option value="">---Select---</option>
                            <?php foreach ($role_list as $key => $item) { ?>
                            <option value="<?php echo $item->id; ?>" <?php echo (isset($menu_data->role_id) && $menu_data->role_id == $item->id) ? 'selected="selected"' : ''; ?> ><?php echo $item->name; ?></option>
                            <?php } ?>
                            <!-- <option value="R" <?php //echo (isset($menu_data->role_id) && $menu_data->role_id == 'R') ? 'selected="selected"' : ''; ?>>Admin</option> -->
                            <!-- <option value="S" <?php //echo (isset($menu_data->role_id) && $menu_data->role_id == 'S') ? 'selected="selected"' : ''; ?>>Supervisor</option> -->
                            <!-- <option value="A" <?php //echo (isset($menu_data->role_id) && $menu_data->role_id == 'A') ? 'selected="selected"' : ''; ?>>Agents</option> -->
                        </select>
                        <?php 
                        if(isset($error_data['role_id'])){ 
                            foreach ($error_data['role_id'] as $key => $value) {
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
            <div class="col-md-6 col-sm-12">
                <div class="menu-page-list">
                    <div class="box-heading">Select your menu item. <br>
                        <span class="help-block h-span">(Click checkbox &amp; 'add' then that item show on right side)</span>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="headingOne">
                            <h4 class="panel-title text-left">Page</h4>
                        </div>
                        <div class="panel-body panel-scroll">
                            <ul id="page_parent_list">
                                <?php echo $page_list; ?>
                            </ul>
                        </div>
                        <div class="btn-box text-right"> <span class="btn btn-primary add-page-menu">Add to Menu</span></div>
                    </div>
                </div>
            </div>
            <div id="menuContent" class="col-md-6 col-sm-12">
                <div class="menu-items">
                    <div class="box-heading">
                        Change your menu structure here.<br>
                        <span class="help-block h-span">(Change menu hierarchy by drag&amp;drop)</span>
                    </div>
                    <div class="portlet-body">                
                        <div class="dd" id="nestable_list">
                            <ol class="dd-list start-dd-list">
                                <?php 
                                // Helper::page_idx_list = $page_idx_list;
                                echo (count($menu_info) > 0) ? Helper::getMenuContent($menu_info, $page_idx_list) : ''; 
                                ?>
                            </ol>
                        </div>         
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
        	<div class="col-md-12 col-sm-12 text-center">
	            <input class="btn btn-success" type="submit" value="<?php echo (isset($menu_data->id) && !empty($menu_data->id)) ? 'Update' : 'Add'; ?>" name="submit" >
	        </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    function addMenuItem(){
        $('span.add-page-menu, span.add-category-menu').on('click', function(e){            
            var count = $('.dd-item').length;
            var menu_info = $('#menu_info').val();            
            if(menu_info != null && menu_info !== undefined && menu_info != '')
                menu_info = JSON.parse(menu_info);
            else
                menu_info = [];
            
            $('#page_parent_list input:checked').each(function(index) {
                count++;
                var checkData = this.value.split('###');
                var name = checkData[2];
                
                var list = '<li class="dd-item" data-type="'+checkData[0]+'" data-id="'+checkData[1]+'" data-name="'+checkData[2]+'" \n\
                            data-path="'+checkData[3]+'" data-icon="'+checkData[4]+'" data-active-class="'+checkData[5]+'" data-layout="'+checkData[6]+'" data-pop-out="'+checkData[7]+'"> \n\
                            <span data-idx="'+(count)+'" class="pull-left custom-menu-remove btn btn-xs red remove-custom-menu">\n\
                            <i class="fa fa-remove"></i></span> <div class="dd-handle"> '+name+' </div> </li>';
                
                $(list).appendTo('#nestable_list ol.start-dd-list');
                $(this).prop('checked', false);
                $(this).parents('span').removeClass('checked');  
                $('#nestable_list').trigger('change');
                
                removeMenuItem();
            });
        });
    };

    function removeMenuItem(){
        $('span.remove-custom-menu').unbind().on('click', function(e){
            var removeDom = $(this);
            bootbox.confirm("Are you sure you want to delete this item from menu structure?", function(result) {                
                if (result) {
                    var childContent = '';  
                    if(removeDom.parent('li.dd-item').find('ol.dd-list').length > 0){
                        childContent = removeDom.parent('li.dd-item').find('ol.dd-list').html(); 
                        // console.log(childContent);
                        removeDom.parent('li.dd-item').next().html(childContent);                                
                        removeDom.parent('li.dd-item').remove();
                    }else{
                        removeDom.parent('li.dd-item').remove();
                    }
                    $('#nestable_list').trigger('change');
                }
            });
        });
    };

    function updateOutput(e) {
        var list = e.length ? e : $(e.target);
        var output = list.data('output');
        var mainData = JSON.stringify(list.nestable('serialize'));
        
        // console.log(list);
        // console.log(output);
        // console.log(mainData);
        
        $('#menu_info').val(mainData);
        var count = 1;
        $('#nestable_list li.dd-item').each(function(){
            $(this).attr('data-id',count);
            count++;
        });
    };

    function menuNesteList(){
        $('#nestable_list').nestable({
            group: 1
        }).on('change', updateOutput);
         
        $('#nestable_list').trigger('change');
    };

	$( document ).ready(function() {
	    $('#frm_menu').validate({
            errorClass: "form-error",
            errorElement: "span",
        });
        menuNesteList();
        addMenuItem();
        removeMenuItem();
	});
</script>