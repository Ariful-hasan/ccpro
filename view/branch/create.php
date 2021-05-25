<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>

<?php
$form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());
if(isset($branch_data->id) && !empty($branch_data->id)){
    $form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName()."&id=".$branch_data->id);
}
?>

<form id="StickyNoteForm" class="form-horizontal" class="form" action="<?php echo $form_url; ?>" method="post">
    <input type="hidden" name="id" value="<?php echo (isset($branch_data->id) && !empty($branch_data->id)) ? $branch_data->id : ''; ?>" />
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
            <div class="col-md-12 col-sm-6">
                <div class="col-md-6 form-group">
                    <label for="branch_code" class="col-sm-4 control-label pr-0">Branch Code:<span class='error'>*</span></label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="branch_code" name="branch_code" maxlength="5" data-rule-required="true"
                               data-msg-required="Number is required." data-rule-maxlength="5" data-msg-maxlength="Please enter no more than 5 digits!"
                               value="<?php echo (isset($branch_data->branch_code) && !empty($branch_data->branch_code)) ? $branch_data->branch_code : ''; ?>">
                        <?php
                        if(isset($error_data['branch_code'])){
                            foreach ($error_data['branch_code'] as $key => $value) {
                                ?>
                                <span class="form-error"><?=$value?></span>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="col-md-6 form-group">
                    <label for="branch_name" class="col-sm-4 control-label pr-0">Branch Name:<span class='error'>*</span></label>
                    <div class="col-sm-8">
						<textarea class="form-control" id="branch_name" name="branch_name" maxlength="150" rows="3"
                                  data-rule-required="true" data-msg-required="Branch name is required." data-rule-maxlength="150"
                                  data-msg-maxlength="Please enter no more than 150 characters!" ><?php echo (isset($branch_data->branch_name) && !empty($branch_data->branch_name)) ? $branch_data->branch_name : ''; ?></textarea>
                        <div>
                            <?php
                            if(isset($error_data['branch_name'])){
                                foreach ($error_data['branch_name'] as $key => $value) {
                                    ?>
                                    <span class="form-error"><?=$value?></span>
                                    <?php
                                }
                            }
                            ?>
                            <p class="text-right">
                                <span id="count">150</span> characters only
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12 col-lg-12 text-center">
                <input class="btn btn-success" type="submit" value="<?php echo (isset($branch_data->id) && !empty($branch_data->id)) ? 'Update' : 'Add'; ?>" name="submit" >
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    $( document ).ready(function() {
        $('#branch_name').bind('input propertychange', function(){
            var val = $('#branch_name').val();
            var length = val.length;

            $('#note').val(val.substring(0, 150));

            var count = Math.max(0, 150 - length);
            $("#count").text(count);
        });
    });
</script>
