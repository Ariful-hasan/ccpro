<?php $num_select_box = 6;?>
<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>
<script type="text/javascript" src="js/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="js/bootstrap-multiselect/css/bootstrap-multiselect.css" type="text/css"/>
<!--<link href="js/summernote/summernote.css" rel="stylesheet">
<script src="js/summernote/summernote.js"></script>-->
<script type="text/javascript" src="js/toastr/toastr.min.js"></script>
<!--<link href="ccd/select2/select2.min.css" rel="stylesheet" />
<script src="ccd/select2/select2.min.js"></script>-->
<!--<script src="js/jquery.MultiFile.pack.js"></script>-->
<script src="assets/plugins/multiple-file/jquery.MultiFile.min.js"></script>


<link rel="stylesheet" href="assets/plugins/typeahead/typeahead.min.css">
<link rel="stylesheet" href="assets/plugins/typeahead/bootstrap-tagsinput.css">
<script src="assets/plugins/typeahead/bootstrap3-typeahead.min.js"></script>
<script src="assets/plugins/typeahead/bootstrap-tagsinput.min.js"></script>

<!--<script type="text/javascript" src="js/jquery.browser.min.js"></script>
<script type="text/javascript" src="js/bnKb/driver.phonetic.js?v=1.0" charset="utf-8"></script>
<script type="text/javascript" src="js/bnKb/engine.js?v=1.0" charset="utf-8"></script>-->

<!--22-07-18-->
<?php include_once ('lib/summer_note_lib.php')?>
<!--22-07-18-->
<script src="js/dpselections.js"></script>
<link rel="stylesheet" href="css/multiselect.css" type="text/css">
<?php include_once ('template_modal.php');?>

<?php
$address_book_emails = [];
if (!empty($ccmails)){
    foreach ($ccmails as $item){
        $address_book_emails[] = $item->name . ' (' . $item->email . ')';
        //$address_book_emails[] = mb_convert_encoding($cc_item, "UTF-8", "UTF-8");
    }
}
//GPrint($address_book_emails);die;
?>

<style>
    .temp-sm-btn{
        margin-top: -2px !important;
        margin-left: -12px !important;
        margin-right: -7px !important;
        margin-bottom: -8px !important;
    }
    div#att_file_wrap_list {
        margin-top: 10px;
    }
    .MultiFile-label {
        padding: 3px;
    }
    .MultiFile-title {
        font-family: sans-serif, Verdana, Arial, Helvetica;
        font-size: 13px;
    }
    a.MultiFile-remove i {
        font-size:13px;
        color: #D65959;
        padding-left: 14px;
    }
    .select2-container a.select2-choice {
        font-size: 14px;
        height: 38px;
        padding: 8px 12px;
        line-height: 1.42857;
    }
    .btn.btn-success{
        background-color: #1dc9b7!important;
        color: #ffffff;
    }
    .btn.btn-rds {
        border-radius: 2rem!important;
        outline: 0!important;
    }
</style>

<script type="text/javascript">
    set_loadurl("<?php echo $this->url("task=email&act=dispositionchildren");?>");
    set_num_select_box(<?php echo $num_select_box;?>);
    var default_lan = '';
    var ticketDID = '';
    var text_area_id = "'textarea#mail_body'";
    var did = '';
    var sid = '';
    var agent_name = "<?php echo $agent_name?>";
    var address_book_emails = <?php echo  !empty($address_book_emails) ? json_encode($address_book_emails) : '""';?>;
    function reload_sels_e(i, val) {
        // CKEDITOR.config._disp_id = val;
        reload_sels(i, val);
    }

    $(document).ready(function() {
        $('.multiselect').multiselect({buttonClass:'btn'});
        load_sels(<?php echo count($disposition_ids);?>);

        $("#frm_ivr_service").validate({
            errorClass: "form-error",
            errorElement: "span",
        });

        Initialize_Summer_note('textarea#mail_body', ['view'], "<?php echo $this->url('task=email&act=uploadEmailImage&type=new'); ?>", "<?php echo $this->url('task=email&act=deleteUploadedImage'); ?>", "<?php echo $attachment_save_path; ?>");
        did = $("#dd").val();
        click_template(text_area_id, did, sid);//////////////////From template_modal.php


        /*$("#cc_emails").select2({
            tags: true
        });
        $("#bcc_emails").select2({
            tags: true
        });*/


        /*$('#att_file').MultiFile({
            STRING: {
                remove: '<i class="fa fa-minus-circle"></i>'
            }
        });*/
        $('#att_file').MultiFile({
            STRING: {
                remove: '<i class="fa fa-minus-circle"></i>'
            },
            list: '#T7-list'
        });


        set_CC_BCC_FWD('input#cc_emails', '', '', address_book_emails);
        set_CC_BCC_FWD('input#bcc_emails', '', '', address_book_emails);
    });
    $(window).load(function() {
        $('textarea#mail_body').summernote('reset');
    });

    function set_CC_BCC_FWD(element, tempArray=null, store_element=null, data) {
        $(element).tagsinput({
            typeahead: {
                source: data,
            },
            confirmKeys: [13, 44]
        });
        $(".bootstrap-tagsinput input").on('blur', function() {
            $(this).trigger(jQuery.Event('keypress', {which: 13}));
        });
        /*$(element).on('itemAdded', function(event) {
            if (tempArray.indexOf(event.item) < 0){
                console.log(tempArray);
                tempArray.push(event.item);
                $(store_element).val(JSON.stringify(tempArray));
            }
        });
        $(element).on('itemRemoved', function(event) {
            var idx = tempArray.indexOf(event.item);
            tempArray.splice(idx,1);
            $(store_element).val(JSON.stringify(tempArray));
        });*/
    }


    function reload_skill_emails(val='')
    {
        $select = $("#skill_email");
        $select.html('<option value="">Select</option>');
        if (typeof val != 'undefined' && val.length > 0) {

            // CKEDITOR.config._skillid = val;

            $.ajax({
                type: "POST",
                url: "<?php echo $this->url("task=email&act=skillemails");?>",
                data: { sid: val },
                dataType: "json"
            })
                .done(function(data) {
                    if (typeof data != 'undefined' && data != null && data.length > 0) {
                        $.each(data, function(key, val){
                            $select.append('<option value="' + val + '">' + val + '</option>');
                        })
                    }
                })
                .fail(function() {

                })
                .always(function() {

                });

            $.ajax({
                type: "POST",
                url: "<?php echo $this->url("task=email&act=signature-text");?>",
                data: { sid: val }
            })
                .done(function(data) {
                    if (typeof data != 'undefined') {
                        data = data.replace("|AGENT-NAME|", "<br><br>"+agent_name);
                        $('textarea#mail_body').summernote('reset');
                        $('textarea#mail_body').summernote('code', data);
                    }
                })
                .fail(function() {

                })
                .always(function() {

                });

            var disposition_url = "";
            <?php if (!empty($type)): ?>
            disposition_url = "<?php echo $this->url("task=email&act=skillDispositions&type=ticket");?>"
            <?php else:?>
            disposition_url = "<?php echo $this->url("task=email&act=skillDispositions");?>"
            <?php endif;?>

            $.ajax({
                type: "POST",

                url: disposition_url,
                data: { sid: val },
                dataType: "json"
            })
                .done(function(data) {
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
        //var slct = selector.substr(0, selector.length-1);
        var current_select_num = selector.substr(selector.length-1)-1;
        if (typeof val!= 'undefined' && val == '' && current_select_num > 0){
            var previous_select_num = current_select_num-1;
            //var select = '"'+slct+'"';
            //var previous_select_val = $(select+previous_select_num).val();
            var previous_select_val = $("#disposition_id"+previous_select_num).val();
            $("#dd").val(previous_select_val);
        } else {
            $("#dd").val(val);
        }
    }
</script>

<?php if (!empty($type)){ ?>
<form id="frm_ivr_service" name="frm_ivr_service" class="form-horizontal" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName()."&type=ticket");?>" onsubmit="return checkMsg();">
    <?php } else {?>
    <form id="frm_ivr_service" name="frm_ivr_service" class="form-horizontal" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>" onsubmit="return checkMsg();" enctype="multipart/form-data">
        <?php }?>

        <div class="panel-body">
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <?php if(isset($errMsg) && !empty($errMsg)){ ?>
                        <!--<div class="alert alert-danger alert-dismissible" role="alert">-->
                        <div class="alert <?php if ($errType === 0){ ?>alert-success <?php }else{ ?>alert-danger <?php } ?> alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <!--<strong>Error!</strong> --><?/*=$errMsg*/?>
                            <strong><?php if ($errType === 0){ ?>Success<?php }else{ ?>Error<?php } ?>!</strong> <?=$errMsg?>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="name" class="control-label col-md-2 col-sm-2 pr-0">
                            <?php if (!empty($type)){ ?>Customer Name:<span class='error'>*</span><?php } else {?>Email Owner's Name:<?php }?>
                        </label>
                        <div class="col-md-10 col-sm-10">
                            <input type="text" id="name" name="name" size="30" maxlength="50" value="<?php echo $name;?>" class="form-control" <?php if (!empty($type)){ ?>data-rule-required='true' data-msg-required='Customer Name is required!'<?php } ?> data-rule-maxlength='50' data-msg-maxlength='Please enter no more than 50 characters!' />
                        </div>
                    </div>
                </div>

                <?php if (!empty($type)){ ?>
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
                <?php }?>
                <?php if (empty($type)){ ?>
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm mb-15">
                            <label for="email" class="control-label col-md-2 col-sm-2 pr-0">
                                Owner's Email:<span class='error'>*</span>
                            </label>
                            <div class="col-md-10 col-sm-10">
                                <input type="text" id="email" name="email" size="30" maxlength="50" value="<?php echo $email;?>" class="form-control" data-rule-required='true' data-msg-required='Ticket Owner Email is required!' data-rule-maxlength='50' data-msg-maxlength='Please enter no more than 50 characters!' />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm mb-15">
                            <label for="cc_emails" class="control-label col-md-2 col-sm-2 pr-0">
                                CC:
                            </label>
                            <div class="col-md-10 col-sm-10">
                                <input class="form-control" id="cc_emails" name="cc" type="text"/>
                                <!--<select id="cc_emails" class="" multiple="multiple" name="cc[]" style="width: 100%;">
                                    <?php
/*                                    if (is_array($ccmails)) {
                                        foreach ($ccmails as $ccmail) {*/?>
                                            <option value="<?php /*echo $ccmail->email*/?>"><?php /*echo $ccmail->name." ($ccmail->email)"*/?></option>
                                        <?php /*}
                                    }
                                    */?>
                                </select>-->
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm mb-15">
                            <label for="bcc_emails" class="control-label col-md-2 col-sm-2 pr-0">
                                BCC:
                            </label>
                            <div class="col-md-10 col-sm-10">
                                <input class="form-control" id="bcc_emails" name="bcc" type="text"/>
                                <!--<select id="bcc_emails" class="" multiple="multiple" name="bcc[]" style="width: 100%;">
                                    <?php
/*                                    if (is_array($ccmails)) {
                                        foreach ($ccmails as $ccmail) {*/?>
                                             <option value="<?php /*echo $ccmail->email*/?>"><?php /*echo $ccmail->name." ($ccmail->email)"*/?></option>
                                        <?php /*}
                                    }
                                    */?>
                                </select>-->
                            </div>
                        </div>
                    </div>
                <?php }?>
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="skill_id" class="control-label col-md-2 col-sm-2 pr-0">
                            Skill:<span class="error">*</span>
                        </label>
                        <div class="col-md-10 col-sm-10">
                            <?php if (!empty($type)):?>
                            <select class="form-control" name="skill_id" id="skill_id" onChange="reload_skill_emails($(this).find('option:selected').attr('pup'));" data-rule-required='true' data-msg-required='Skill is required!'>
                                <?php else:?>
                                <select class="form-control" name="skill_id" id="skill_id" onChange="reload_skill_emails(this.value);" data-rule-required='true' data-msg-required='Skill is required!'>
                                    <?php endif;?>
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
                <?php if (empty($type)){ ?>
                    <?php  /* if (count($emails) > 0) { */ ?>
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm mb-15">
                            <label for="skill_email" class="control-label col-md-2 col-sm-2 pr-0">
                                Skill Email:<span class="error">*</span>
                            </label>
                            <div class="col-md-10 col-sm-10">
                                <select name="skill_email" id="skill_email" class="form-control" data-rule-required='true' data-msg-required='Skill Email is required!'>
                                    <option value="">Select</option>
                                    <?php if (is_array($emails)) {
                                        foreach ($emails as $semail) {
                                            echo '<option value="' . $semail . '"';
                                            if ($skill_email == $semail) echo ' selected';
                                            echo '>' . $semail . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php /*}*/ ?>
                <?php }?>
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="disposition_id" class="control-label col-md-2 col-sm-2 pr-0">
                            Disposition:<span class="error">*</span>
                        </label>
                        <div class="col-md-10 col-sm-10">
                            <?php
                            //var_dump($num_select_box);
                            for ($i=0; $i<$num_select_box; $i++) {
                                echo '<select name="disposition_id'.$i.'" id="disposition_id'.$i.'" style="display:none;" onchange="reload_sels_e('.$i.', this.value);" class="form-control" data-rule-required="true" data-msg-required="Disposition is required!"><option value="">Select</option>';
                                /*if ($i == 0 || isset($disposition_ids[$i])) {
                                    if (!isset(${'dispositions'.$i})) {
                                        if ($i > 0) {
                                            $dispositions = $email_model->getDispositionChildrenOptions($skill_id, $disposition_ids[$i-1][0], false);
            //var_dump($dispositions);
                                        } else {
                                            $dispositions = $email_model->getDispositionChildrenOptions($skill_id, '', false);
                                        }
                                    } else {
                                        $dispositions = ${'dispositions'.$i};
                                    }

                                    foreach ($dispositions as $_dispositionid=>$_title) {
                                        $did = isset($disposition_ids[$i]) ? $disposition_ids[$i][0] : '';
                                        echo '<option value="' . $_dispositionid . '"';
                                        if ($did == $_dispositionid) echo ' selected';
                                        echo '>' . $_title . '</option>';
                                    }
                                }*/
                                echo '</select> ';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php if (empty($type)){ ?>
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group form-group-sm mb-15">
                            <label for="title" class="control-label col-md-2 col-sm-2 pr-0">
                                Subject:<span class="error">*</span>
                            </label>
                            <div class="col-md-10 col-sm-10">
                                <input type="text" id="title" name="title" size="30" maxlength="100" value="<?php echo $title;?>" class="form-control" data-rule-required="true" data-msg-required="Ticket Title is required!" data-rule-maxlength='100' data-msg-maxlength='Please enter no more than 100 characters!' />
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="mail_body" class="control-label col-md-2 col-sm-2 pr-0">
                            Template:
                        </label>
                        <div class="col-md-10 col-sm-10">
                            <?php
                            $tsk = "task=".$request->getControllerName();
                            $act = "&act=templates";
                            /*$dd = !empty($disposition->disposition_id)?$disposition->disposition_id:'';
                            $sid = !empty($eTickets->skill_id)?$eTickets->skill_id:"";*/
                            ?>
                            <!--<button class="btn btn-default" type="button" id="temp"> Template </button>-->
                            <button class="btn btn-default" type="button" id="temp" url="<?php echo $this->url($tsk.$act); ?>"> Template </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="mail_body" class="control-label col-md-2 col-sm-2 pr-0">
                            Text:<span class="error">*</span>
                        </label>
                        <div class="col-md-10 col-sm-10 summernote-area">
                            <textarea name="mail_body" id="mail_body" data-rule-required="true" data-msg-required="Text is required!" class="bangla summernote-mini"><?php echo $mail_body;?></textarea>
                            <!--<input type="file" id="att_file" name="att_file[]" class="col-md-12 pull-left" style="padding-left:1px;width: 100%"/><br />-->
                            <input type="file" id="att_file" name="att_file[]" multiple="multiple" class="col-md-12 pull-left" style="width: 100%"/><br />
                            <div id="T7-list" class="col-md-12">

                            </div>
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
                <?php if (!empty($type)){ ?>
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
                <?php }?>

                <div class="row">
                    <div class="col-md-12 col-sm-12 text-center">
                        <input class="btn btn-success form_submit_button btn-rds" type="submit" value="Generate Email" name="submitservice">
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="dd" id="dd" value="<?php //echo $dd?>" maxlength="4" />
    </form>
