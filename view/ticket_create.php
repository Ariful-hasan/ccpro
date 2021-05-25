<?php $num_select_box = 6;?>
<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>
<script type="text/javascript" src="js/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="js/bootstrap-multiselect/css/bootstrap-multiselect.css" type="text/css"/>
<!--<script type="text/javascript" src="js/toastr/toastr.min.js"></script>-->

<script type="text/javascript" src="js/jquery.browser.min.js"></script>
<script type="text/javascript" src="js/bnKb/driver.phonetic.js?v=1.0" charset="utf-8"></script>
<script type="text/javascript" src="js/bnKb/engine.js?v=1.0" charset="utf-8"></script>


<?php include_once ('lib/summer_note_lib.php')?>
<script src="js/dpselections.js"></script>
<link rel="stylesheet" href="css/multiselect.css" type="text/css">
<?php include_once ('template_modal.php');?>

<form id="frm_ivr_service" name="frm_ivr_service" class="form-horizontal" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>" onsubmit="return checkMsg();">

        <div class="panel-body">
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <?php if(isset($errMsg) && !empty($errMsg)){ ?>
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <strong>Error!</strong> <?=$errMsg?>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="name" class="control-label col-md-2 col-sm-2 pr-0">
                            Customer Name:<span class='error'>*</span>
                        </label>
                        <div class="col-md-10 col-sm-10">
                            <input type="text" id="name" name="name" size="30" maxlength="50" value="<?php echo $name;?>" class="form-control" <?php if (!empty($type)){ ?>data-rule-required='true' data-msg-required='Customer Name is required!'<?php } ?> data-rule-maxlength='50' data-msg-maxlength='Please enter no more than 50 characters!' />
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="account_id" class="control-label col-md-2 col-sm-2 pr-0">
                            Account/Card No:<span class='error'>*</span>
                        </label>
                        <div class="col-md-10 col-sm-10">
                            <input type="text" id="account_id" name="account_id" size="30" maxlength="20" value="<?php echo $account_id;?>" class="form-control" data-rule-required='true' data-msg-required='Account/Card No is required!' data-rule-maxlength='20' data-msg-maxlength='Please enter no more than 20 characters!' />
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="created_for" class="control-label col-md-2 col-sm-2 pr-0">
                            Mobile No:<span class='error'>*</span>
                        </label>
                        <div class="col-md-10 col-sm-10">
                            <input type="text" id="created_for" name="created_for" size="30" maxlength="50" value="<?php echo $account_id;?>" class="form-control" data-rule-required='true' data-msg-required='Mobile No is required!' data-rule-maxlength='50' data-msg-maxlength='Please enter no more than 50 characters!' />
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="skill_id" class="control-label col-md-2 col-sm-2 pr-0">
                            Skill:<span class="error">*</span>
                        </label>
                        <div class="col-md-10 col-sm-10">
                            <select class="form-control" name="skill_id" id="skill_id" onChange="reload_skill_emails(this.value);" data-rule-required='true' data-msg-required='Skill is required!'>
                                <option value="">Select</option>
                                <?php if (is_array($skills)) {
                                    foreach ($skills as $skill) {
                                        if (isset($skill->active) && $skill->active == 'Y'){
                                            echo '<option value="' . $skill->skill_id . '" pup="'. $skill->popup_url. '"';
                                            if ($skill_id == $skill->skill_id) echo ' selected';
                                            echo '>' . $skill->skill_name . '</option>';
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="disposition_id" class="control-label col-md-2 col-sm-2 pr-0">
                            Disposition:<span class="error">*</span>
                        </label>
                        <div class="col-md-10 col-sm-10">
                            <?php
                                for ($i=0; $i<$num_select_box; $i++) {
                                    echo '<select name="disposition_id'.$i.'" id="disposition_id'.$i.'" style="display:none;" onchange="reload_sels_e('.$i.', this.value);" class="form-control" data-rule-required="true" data-msg-required="Disposition is required!"><option value="">Select</option>';
                                    echo '</select> ';
                                }
                            ?>
                        </div>
                    </div>
                </div>

<!--                <div class="col-md-12 col-sm-12">-->
<!--                    <div class="form-group form-group-sm mb-15">-->
<!--                        <label for="mail_body" class="control-label col-md-2 col-sm-2 pr-0">-->
<!--                            Template:-->
<!--                        </label>-->
<!--                        <div class="col-md-10 col-sm-10">-->
<!--                            --><?php
//                                $tsk = "task=".$request->getControllerName();
//                                $act = "&act=templates";
//                                $dd = !empty($disposition->disposition_id)?$disposition->disposition_id:'';
//                                $sid = !empty($eTickets->skill_id)?$eTickets->skill_id:"";
//                            ?>
<!--                            <button class="btn btn-default" type="button" id="temp" url="--><?php //echo $this->url($tsk.$act); ?><!--"> Template </button>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->

                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="mail_body" class="control-label col-md-2 col-sm-2 pr-0">
                            Text:<span class="error">*</span>
                        </label>
                        <div class="col-md-10 col-sm-10 summernote-area">
                            <textarea name="mail_body" id="mail_body" data-rule-required="true" data-msg-required="Text is required!" class="bangla summernote-mini"><?php echo $ticket_body;?></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="status" class="control-label col-md-2 col-sm-2 pr-0">
                            Status:<span class="error">*</span>
                        </label>
                        <div class="col-md-10 col-sm-10">
                            <select name="status" id="status" class="form-control" data-rule-required="true" data-msg-required="Status is required!">
                                <option value="">Select</option>
                                <?php if (is_array($changable_status)) {
                                    foreach ($changable_status as $st) {
                                        echo '<option value="' . $st . '"';
                                        if ($status == $st) echo ' selected';
                                        echo '>' . $email_model->getTicketStatusLabel($st) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="category_id" class="control-label col-md-2 col-sm-2 pr-0">
                            Category:<span class="error">*</span>
                        </label>
                        <div class="col-md-10 col-sm-10">
                            <select name="category_id" id="category_id" class="form-control" data-rule-required="true" data-msg-required="Category is required!">
                                <option value="">Select</option>
                                <?php if (!empty($ticket_category)){?>
                                    <?php foreach ($ticket_category as $item => $value){?>
                                        <option value="<?php echo $item?>" <?php echo $category_id==$item?"selected='selected'":"" ?> ><?php echo $value?></option>
                                    <?php }?>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-12 col-sm-12 text-center">
                        <input class="btn btn-success form_submit_button" type="submit" value="Generate Ticket" name="submitservice">
                    </div>
                </div>
            </div>
        </div>
    <input type="hidden" name="dd" id="dd" value="<?php //echo $dd?>" maxlength="4" />
    </form>



    <script type="text/javascript">
        set_loadurl("<?php echo $this->url("task=email&act=dispositionchildren");?>");
        set_num_select_box(<?php echo $num_select_box;?>);
        var default_lan = '';
        var text_area_id = "'textarea#mail_body'";
        var did = '';
        var sid = '';
        var isSubmit = false;

        function reload_sels_e(i, val) {
            reload_sels(i, val);
        }

        $(document).ready(function() {
            //$('.multiselect').multiselect({buttonClass:'btn'});
            load_sels(<?php echo count($disposition_ids);?>);

            $("#frm_ivr_service").validate({
                errorClass: "form-error",
                errorElement: "span",
            });

            Initialize_Summer_note('textarea#mail_body', ['view','insert'], "<?php //echo $this->url('task=email&act=uploadEmailImage&type=new'); ?>", "<?php //echo $this->url('task=email&act=deleteUploadedImage'); ?>", "<?php //echo $attachment_save_path; ?>");
            did = $("#dd").val();
            click_template(text_area_id, did, sid);//////////////////From template_modal.php
        });

        function reload_skill_emails(val='') {
            if (typeof val != 'undefined' && val.length > 0) {

                var disposition_url = "<?php echo $this->url("task=ticket&act=skillDispositions");?>"
                $.ajax({
                    type: "POST",

                    url: disposition_url,
                    data: { sid: val },
                    dataType: "json"
                })
                    .done(function(data) {
                        //console.log(data);
                        if (typeof data != 'undefined' && data != null && data != null) {
                            $selector = $("#disposition_id0");
                            $selector.html('<option value="">Select</option>');
                            $.each(data, function(key, val){
                                $selector.append('<option value="' + key + '">' + val + '</option>');
                            });
                            var totalBox = "<?php echo $num_select_box; ?>";
                            totalBox = parseInt( totalBox );
                            for ( var idnum = 1; idnum < totalBox; idnum++) {
                                option = $('<option></option>').attr("value", "").text("Select");
                                selectId = "#disposition_id" + idnum;
                                $(selectId).empty().append(option);
                                $(selectId).hide();
                            }
                        }
                    })
                    .fail(function() {

                    })
                    .always(function() {

                    });
            }
        }
        function checkMsg() {
            var pattern = "<?php echo $replace_text_pattern; ?>";
            if($("#mail_body").val() != ""){
                var msg = $("#mail_body").val().toLowerCase();
                if (pattern != "" && pattern != null && msg.indexOf(pattern.toLowerCase()) >= 0) {
                    toastr.error("You must replace the fixed text: "+pattern);
                } else {
                    var st = $("#status").val();
                    if (st.length == 0) {
                        toastr.error('Please set the status of the ticket!');
                        $( "#status" ).focus();
                        return false;
                    }
                    return true;
                }
            } else {
                toastr.error("Message can not be empty!");
            }
            return false;
        }

        function get_templates_by_disp(val, selector) {
            var current_select_num = selector.substr(selector.length-1)-1;
            if (typeof val!= 'undefined' && val == '' && current_select_num > 0){
                var previous_select_num = current_select_num-1;
                var previous_select_val = $("#disposition_id"+previous_select_num).val();
                $("#dd").val(previous_select_val);
            } else {
                $("#dd").val(val);
            }
        }

    </script>