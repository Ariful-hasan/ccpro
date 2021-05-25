<link href="ccd/select2/select2.min.css"  rel="stylesheet" />
<script type="text/javascript" src="ccd/select2/select2.min.js"></script>
<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>
<?php
$form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());

if(isset($sticky_note->id) && !empty($sticky_note->id)){
    $form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName()."&id=".$sticky_note->id);
}

$selected_agents = isset($sticky_note->agent_id) ? explode(",", $sticky_note->agent_id) : [];
?>

<form id="StickyNoteForm" class="form-horizontal" enctype="multipart/form-data" class="form" action="<?php echo $form_url; ?>" method="post">
	<input type="hidden" name="id" value="<?php echo (isset($sticky_note->id) && !empty($sticky_note->id)) ? $sticky_note->id : ''; ?>" />
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
                <div class="row form-group">		
					<label for="title" class="col-sm-2 control-label pr-0">Title:<span class='error'>*</span></label>
				    <div class="col-sm-10">
						<input type="text" class="form-control" id="title" name="title" maxlength="30" data-rule-required="true" 
						data-msg-required="Title is required." data-rule-maxlength="30" data-msg-maxlength="Please enter no more than 30 characters!"
						value="<?php echo (isset($sticky_note->title) && !empty($sticky_note->title)) ? $sticky_note->title : ''; ?>">
						<?php 
                        if(isset($error_data['title'])){ 
                            foreach ($error_data['title'] as $key => $value) {
                        ?>
                            <span class="form-error"><?=$value?></span>
                        <?php 
                            }
                        }
                        ?>
					</div>
			  	</div>
			  	<div class="row form-group">
					<label for="description" class="col-sm-2 control-label pr-0">Description:<span class='error'>*</span></label>
				    <div class="col-sm-10">
						<textarea class="form-control" id="description" name="description" maxlength="500" rows="5" 
						data-rule-required="true" data-msg-required="Description is required." data-rule-maxlength="500" 
						data-msg-maxlength="Please enter no more than 500 characters!" ><?php echo (isset($sticky_note->description) && !empty($sticky_note->description)) ? $sticky_note->description : ''; ?></textarea>
						<div>
							<?php 
	                        if(isset($error_data['description'])){ 
	                            foreach ($error_data['description'] as $key => $value) {
	                        ?>
	                            <span class="form-error"><?=$value?></span>
	                        <?php 
	                            }
	                        }
	                        ?>
							<p class="text-right">
								<span id="count">500</span> characters only
							</p>
						</div>
					</div>
				</div>
            </div>

            <div class="col-md-6 col-sm-6 col-lg-6">
                <div class="row form-group">
					<label for="status" class="control-label col-md-2 col-sm-2 pr-0">Status:<span class='error'>*</span></label>
                    <div class="col-md-10 col-sm-10">
                        <select id="status" name="status" class="form-control <?php echo isset($error_data['status']) ? 'form-error' : ''; ?>" data-rule-required='true' data-msg-required='Status is required!'>
                        	<option value="">---Select---</option>
                        	<?php foreach ($status_list as $key => $value) { ?>
                        	<option value="<?php echo $key; ?>" <?php echo (isset($sticky_note->status) && $sticky_note->status == $key) ? 'selected="selected"' : ''; ?> ><?php echo $value; ?></option>
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
			
				<div class="row form-group">			
				  	<label for="agent_id" class="control-label col-sm-2 pr-0">Notify Person:<span class='error'>*</span></label>
				  	<div class="col-sm-10" id="selectagent">
				    	<select id="agent_id" class="wd-100 select2-error-span" name="agent_id[]" multiple="multiple" data-rule-required="true" data-msg-required="Notify Person is required.">				  	
						  	<?php foreach($agent_list as $key => $agent): ?> 
				            	<option value="<?php echo $key; ?>" <?php if(isset($selected_agents)) echo in_array($key, $selected_agents) ? 'selected' : ''; ?>><?php echo $key; ?> - <?php echo $agent; ?></option>
				            <?php endforeach; ?>
						</select> 
						<?php 
                        if(isset($error_data['agent_id'])){ 
                            foreach ($error_data['agent_id'] as $key => $value) {
                        ?>
                            <span class="form-error"><?=$value?></span>
                        <?php 
                            }
                        }
                        ?>
					</div>
					<div class="col-sm-10 col-md-offset-2 text-right">						
						<a href="javascript:void(0)" id="select_all_agent" class="btn btn-xs btn-purple">Select All</a>
						<a href="javascript:void(0)" id="clear_all_agent" class="btn btn-xs btn-purple">Clear Select</a>
					</div>
				</div>
            </div>       
        </div>
        <div class="row">
        	<div class="col-md-12 col-sm-12 col-lg-12 text-center">	            
	            <input class="btn btn-success" type="submit" value="<?php echo (isset($sticky_note->id) && !empty($sticky_note->id)) ? 'Update' : 'Add'; ?>" name="submit" >
	        </div>
        </div>
    </div>
</form>

<script type="text/javascript">
	$( document ).ready(function() {
	    // $('#StickyNoteForm').validate({
     //        errorClass: "form-error",
     //        errorElement: "span",
     //    });

        $('#agent_id').select2();

    	$('#description').bind('input propertychange', function(){    		
    		var val = $('#description').val();
    		var length = val.length;

    		$('#description').val(val.substring(0, 500));
    		
    		var count = Math.max(0, 500 - length);
    		$("#count").text(count);
    	});
    	$("#select_all_agent").on('click', function(){
	        $("#agent_id > option").prop("selected","selected");
	        $("#agent_id").trigger("change");
		});

		$("#clear_all_agent").on('click',function(){
			$("#agent_id > option").removeAttr("selected");
			$("#agent_id").trigger("change");
		});
	});
</script>
