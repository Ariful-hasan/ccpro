<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>

<?php
$form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());
if(isset($note_data->id) && !empty($note_data->id)){
    $form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName()."&id=".$note_data->id);
}
?>

<form id="StickyNoteForm" class="form-horizontal" class="form" action="<?php echo $form_url; ?>" method="post">
    <input type="hidden" name="id" value="<?php echo (isset($note_data->id) && !empty($note_data->id)) ? $note_data->id : ''; ?>" />
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
                    <label for="cli" class="col-sm-2 control-label pr-0">Number:<span class='error'>*</span></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="cli" name="cli" maxlength="15" data-rule-required="true"
                               data-msg-required="Number is required." data-rule-maxlength="15" data-msg-maxlength="Please enter no more than 11 digits!"
                               value="<?php echo (isset($note_data->cli) && !empty($note_data->cli)) ? $note_data->cli : ''; ?>">
                        <?php
                        if(isset($error_data['cli'])){
                            foreach ($error_data['cli'] as $key => $value) {
                                ?>
                                <span class="form-error"><?=$value?></span>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="row form-group">
                    <label for="note" class="col-sm-2 control-label pr-0">Note:<span class='error'>*</span></label>
                    <div class="col-sm-10">
						<textarea class="form-control" id="note" name="note" maxlength="300" rows="5"
                                  data-rule-required="true" data-msg-required="note is required." data-rule-maxlength="300"
                                  data-msg-maxlength="Please enter no more than 300 characters!" ><?php echo (isset($note_data->note) && !empty($note_data->note)) ? $note_data->note : ''; ?></textarea>
                        <div>
                            <?php
                            if(isset($error_data['note'])){
                                foreach ($error_data['note'] as $key => $value) {
                                    ?>
                                    <span class="form-error"><?=$value?></span>
                                    <?php
                                }
                            }
                            ?>
                            <p class="text-right">
                                <span id="count">300</span> characters only
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
                            <option value="">--Select--</option>
                            <?php foreach ($status_list as $key => $value) { ?>
                                <option value="<?php echo $key; ?>" <?php echo (isset($note_data->status) && $note_data->status == $key) ? 'selected="selected"' : ''; ?> ><?php echo $value; ?></option>
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
            <div class="col-md-12 col-sm-12 col-lg-12 text-center">
                <input class="btn btn-success" type="submit" value="<?php echo (isset($sticky_note->id) && !empty($sticky_note->id)) ? 'Update' : 'Add'; ?>" name="submit" >
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    $( document ).ready(function() {
        $('#note').bind('input propertychange', function(){
            var val = $('#note').val();
            var length = val.length;

            $('#note').val(val.substring(0, 300));

            var count = Math.max(0, 300 - length);
            $("#count").text(count);
        });
    });
</script>
