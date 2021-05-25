<link rel="stylesheet" href="assets/plugins/typeahead/typeahead.min.css">
<link rel="stylesheet" href="assets/plugins/typeahead/bootstrap-tagsinput.css">

<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/toastr/toastr.min.js"></script>

<script src="assets/plugins/typeahead/bootstrap3-typeahead.min.js"></script>
<script src="assets/plugins/typeahead/bootstrap-tagsinput.min.js"></script>
<script src="assets/plugins/multiple-file/jquery.MultiFile.min.js"></script>
<?php include_once ('lib/emailsendmodule_summernote_lib.php')?>
<style>
    .tag {
        border-radius: 10px!important;
        background-color: #a8a8a8!important;
        font-size: 12px!important;
    }
</style>

    <form id="frm_ivr_service" name="frm_ivr_service" class="form-horizontal" method="post" action="<?php echo $this->url('task=email-send-module&act=email');?>" onsubmit="return checkMsg();" enctype="multipart/form-data">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <?php if(isset($errMsg) && !empty($errMsg)){ ?>
                        <div class="alert <?php if ($errType === 0){ ?>alert-success <?php }else{ ?>alert-danger <?php } ?> alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <strong><?php if ($errType === 0){ ?>Success<?php }else{ ?>Error<?php } ?>!</strong> <?=$errMsg?>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="email" class="control-label col-md-2 col-sm-2 pr-0">
                            To:<span class='error'>*</span>
                        </label>
                        <div class="col-md-10 col-sm-10">
                            <input type="text" id="to_emails" name="to" value="<?php echo $mainobj->to?>" class="form-control" data-rule-required="true" data-msg-required="To email address is required!!"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="cc_emails" class="control-label col-md-2 col-sm-2 pr-0">
                            CC:
                        </label>
                        <div class="col-md-10 col-sm-10">
                            <input class="form-control" id="cc_emails" name="cc" type="text" value="<?php echo $mainobj->cc?>" />
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="bcc_emails" class="control-label col-md-2 col-sm-2 pr-0">
                            BCC:
                        </label>
                        <div class="col-md-10 col-sm-10">
                            <input class="form-control" id="bcc_emails" name="bcc" type="text" value="<?php echo $mainobj->bcc?>" />
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="title" class="control-label col-md-2 col-sm-2 pr-0">
                            Subject:<span class="error">*</span>
                        </label>
                        <div class="col-md-10 col-sm-10">
                            <input type="text" id="subject" name="subject" value="<?php echo $mainobj->subject?>" class="form-control" data-rule-required="true" data-msg-required="Subject is required!" />
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="mail_body" class="control-label col-md-2 col-sm-2 pr-0">
                            Text:<span class="error">*</span>
                        </label>
                        <div class="col-md-10 col-sm-10 summernote-area">
                            <textarea name="body" id="mail_body" data-rule-required="true" data-msg-required="Text is required!" class="bangla summernote-mini"><?php echo $mail_body;?>
                                <?php echo $mainobj->body?>
                            </textarea>
                            <input type="file" id="att_file" name="att_file[]" multiple="multiple" class="col-md-12 pull-left" style="width: 100%"/><br />
                            <div id="T7-list" class="col-md-12"></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-sm-12 text-center">
                        <input class="btn btn-success form_submit_button btn-rds" type="submit" value="Send" name="submitservice">
                    </div>
                </div>
            </div>
        </div>
    </form>

<script>

    $(document).ready(function() {
        $("#frm_ivr_service").validate({
            errorClass: "form-error",
            errorElement: "span",
        });

        Initialize_Summer_note('textarea#mail_body', ['view'], "<?php echo $this->url('task=email-send-module&act=uploadEmailImage&type=new'); ?>", "<?php echo $this->url('task=email-send-module&act=deleteUploadedImage'); ?>", "<?php echo $attachment_save_path; ?>");
        $('#att_file').MultiFile({
            STRING: {remove: '<i class="fa fa-minus-circle"></i>'},
            list: '#T7-list'
        });
        set_CC_BCC_FWD('input#to_emails', '', '', '');
        set_CC_BCC_FWD('input#cc_emails', '', '', '');
        set_CC_BCC_FWD('input#bcc_emails', '', '', '');
    });
    $(window).load(function() {
        $('textarea#mail_body').summernote('reset');
    });

    let set_CC_BCC_FWD = (element, tempArray=null, store_element=null, data) => {
        $(element).tagsinput({
            typeahead: {
                source: data,
            },
            confirmKeys: [13, 44]
        });
        $(".bootstrap-tagsinput input").on('blur', function() {
            $(this).trigger(jQuery.Event('keypress', {which: 13}));
        });
    };

    function validateEmail(email) {
        console.log(email);
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }

    function checkMsg() {
        if ($("#to_emails").val() == ''){
            toastr.error("Invalid email address!!");
            return false;
        }

        let all_emails = $("#to_emails").val()+","+$("#cc_emails").val()+","+$("#bcc_emails").val();
        let subject = $("#subject").val();
        let mail_body = $("#mail_body").val();

        if (typeof mail_body !== 'undefined' && mail_body.length > 0) {
            if (typeof subject !== 'undefined' && subject.length > 0) {
                if (typeof all_emails !== 'undefined' && all_emails.length > 0){
                    all_emails = all_emails.trim().split(",");
                    if (all_emails.length > 0){
                        let isvalid = false;
                        all_emails.forEach(function (email, key) {
                            if (typeof email !== 'undefined' && email.length > 0){
                                var index = email.lastIndexOf(",");
                                email = email.substring(0, index) + email.substring(index + 1);
                                email = email.trim();
                                if (validateEmail(email) == false) {
                                    toastr.error("Input email address is invalid!!");
                                    isvalid = false;
                                    return false;
                                } else {
                                    isvalid = true;
                                }
                            }
                        });
                        if (isvalid)
                            return true;
                    }
                }
            } else {
                toastr.error("Invalid Subject!!");
            }
        } else {
            toastr.error("Message can not be empty!");
        }
        return false;
    }
</script>