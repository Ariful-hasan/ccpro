<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>
<?php
$form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());

if(isset($settings_data->skill_id) && !empty($settings_data->skill_id)){
    $form_url = $this->url('task='.$request->getControllerName()."&act=".$this->getActionName()."&sid=".$settings_data->skill_id);
}
?>
<form id="frm_page" class="form-horizontal" enctype="multipart/form-data" class="form" action="<?php echo $form_url; ?>" method="post">

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
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-info">
                <div class="panel-body">
                    <div class="form-group form-group-sm">
                        <label for="name" class="control-label col-md-4 col-sm-3">Skill:<span class='error'>*</span></label>
                        <div class="col-md-8 col-sm-9">
                            <select id="skill_id" name="skill_id" class="form-control <?php echo isset($error_data['skill_id']) ? 'form-error' : ''; ?>" data-rule-required='true' data-msg-required='Skill is required!'>
                                <?php echo empty($skill_list) && !empty($settings_data->skill_id) ? '<option value="'.$settings_data->skill_id.'">'.$settings_data->skill_name.'</option>' : ''?>
                                <?php if (!empty($skill_list)){ ?>
                                    <option value="">---Select---</option>
                                    <?php foreach ($skill_list as $key => $value) { ?>
                                        <option value="<?php echo $key; ?>" <?php echo (isset($settings_data->skill_id) && $settings_data->skill_id == $key) ? 'selected="selected"' : ''; ?> ><?php echo $value; ?></option>
                                    <?php } ?>
                                <?php } ?>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <?php for ($n=1; $n<=3; $n++) { ?>
            <div class="col-md-4 col-sm-6">
                <div class="panel panel-info p-l-zero">
                    <div class="panel-body p-l-zero">
                        <?php if (!empty($pd_fields)) {
                            $item_count = 0;
                            ?>
                            <?php foreach ($pd_fields as $key => $value) {
                                $item_count++;
                                if ($item_count > 7)
                                    break;
                                ?>
                                <div class="form-group form-group-sm mb-15 p-l-zero">
                                    <label for="name" class="control-label col-md-5 col-sm-3 pr-0"><?php echo $value?>:<?php echo $key == "number_1" ? "<span class='error'>*</span>" : "" ?></label>
                                    <div class="col-md-7 col-sm-9">
                                        <input type="text" name="<?php echo $key?>"  class="form-control <?php echo isset($error_data[$key]) ? 'form-error' : ''; ?>" maxlength="30" <?php echo $key == "number_1" ? "data-rule-required='true'" : "" ?>
                                               data-msg-required='<?php echo $value?> is required!' data-rule-maxlength='30' data-msg-maxlength='Please enter no more than 30 characters!' value="<?php echo (isset($settings_data->$key) && !empty($settings_data->$key)) ? $settings_data->$key : ''; ?>" />
                                        <?php
                                            if(isset($error_data[$key])){
                                                foreach ($error_data[$key] as $key => $value) {
                                                    ?>
                                                    <span class="form-error"><?=$value?></span>
                                                    <?php
                                                }
                                            }
                                        ?>
                                    </div>
                                </div>

                            <?php unset($pd_fields[$key]); } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <div class="row">
        <div class="col-md-12 col-sm-12 col-lg-12 text-center">
            <input class="btn btn-success" type="submit" value="<?php echo (isset($settings_data->skill_id) && !empty($settings_data->skill_id)) ? 'Update' : 'Add'; ?>" name="submit" >
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