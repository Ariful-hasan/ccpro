<?php $num_select_box = 6;?>
<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>
<script type="text/javascript" src="js/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="js/bootstrap-multiselect/css/bootstrap-multiselect.css" type="text/css"/>

<script type="text/javascript" src="js/toastr/toastr.min.js"></script>

<script src="assets/plugins/multiple-file/jquery.MultiFile.min.js"></script>

<link rel="stylesheet" href="css/multiselect.css" type="text/css">



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

</style>

<script type="text/javascript">

    $(document).ready(function() {
        $('.multiselect').multiselect({buttonClass:'btn'});

        $("#frm_ivr_service").validate({
            errorClass: "form-error",
            errorElement: "span",
        });

        $('#att_file').MultiFile({
            STRING: {
                remove: '<i class="fa fa-minus-circle"></i>'
            },
            list: '#T7-list'
        });
    });

</script>

<div class="panel panel-default">
    <div class="panel-heading">Client Information</div>
    <div class="panel-body" style="padding: 0 15px !important;">
        <?php if (!empty($msg)) : ?>
            <div class="alert <?php echo $msg_type ? 'alert-success' : 'alert-danger'; ?> text-center alert-dismissable">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong><?php echo $msg; ?></strong>
            </div>
        <?php endif; ?>

        <form id="frm_ivr_service" name="frm_ivr_service" class="form-horizontal" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>" enctype="multipart/form-data">
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
                        <label for="email" class="control-label col-md-2 col-sm-2 pr-0">Recipient:<span class='error'>*</span></label>
                        <div class="col-md-10 col-sm-10">
                            <input class="form-control" value="<?php echo $email;?>" maxlength="50" id="email" name="email" readonly type="text" data-rule-required='true' data-msg-required='Ticket Owner Email is required!' />
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
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="bcc_emails" class="control-label col-md-2 col-sm-2 pr-0">BCC:</label>
                        <div class="col-md-10 col-sm-10">
                            <input class="form-control" id="bcc_emails" name="bcc" type="text"/>
                        </div>
                    </div>
                </div>


                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="mail_body" class="control-label col-md-2 col-sm-2 pr-0">Template:</label>
                        <div class="col-md-10 col-sm-10 text-left">
                            <select name="template" id="template" class="form-control">
                                <option value="">---Select---</option>
                                <?php if (!empty($templates)): ?>
                                <?php foreach ($templates as $template) { ?>
                                        <option value="<?php echo $template->tstamp ?>"
                                                data-mail-body='<?php echo base64_decode($template->mail_body) ?>'
                                                data-mail-title='<?php echo $template->title ?>'
                                            <?php echo $request->getRequest('template') == $template->tstamp ? 'selected': '' ?>>
                                            <?php echo $template->title ?>
                                        </option>
                                <?php } ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="title" class="control-label col-md-2 col-sm-2 pr-0">Subject:<span class="error">*</span></label>
                        <div class="col-md-10 col-sm-10">
                            <input type="text" id="title" name="title" size="30" maxlength="100" value="<?php echo $title;?>" class="form-control" data-rule-required="true" data-msg-required="Ticket Title is required!" data-rule-maxlength='100' data-msg-maxlength='Please enter no more than 100 characters!' />
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="mail_body" class="control-label col-md-2 col-sm-2 pr-0">
                            Text:<span class="error">*</span>
                        </label>
                        <div class="col-md-10 col-sm-10 summernote-area">
                            <textarea name="mail_body" id="mail_body" data-rule-required="true" data-msg-required="Text is required!" class="" style="height: 200px; width: 100%"><?php echo $mail_body;?></textarea>
                            <input type="file" id="att_file" name="att_file[]" multiple="multiple" class="col-md-12 pull-left" style="width: 100%"/><br />
                            <div id="T7-list" class="col-md-12 text-left" style="padding-left: 0">

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-sm-12">
                    <div class="col-md-12 col-sm-12 text-right">
                        <input class="btn btn-success form_submit_button" type="submit" value="Send" name="submitservice">
                    </div>
                </div>
            </div>

    </form>
    </div>
</div>

<script type="text/javascript">
    $(function() {
        $("#mail_body").val($('option:selected', this).data('mail-body'));
        $("#title").val($('option:selected', this).data('mail-title'));

        $('#template').change(function(){
            $("#mail_body").val($('option:selected', this).data('mail-body'));
            $("#title").val($('option:selected', this).data('mail-title'));
        });

        $("#pop-up-body").css({"padding": '0px', 'margin' : '0px', 'border' : '0px'});

        $(document).on('click','#submitservice',function (e) {
            e.preventDefault();
            if (!$("#mail_body").val()){
                alert('Select a Mail Template');
            }else {
                $('.form').submit();
            }
        });
    });

</script>

<link href="js/lightbox/colorbox.css" rel="stylesheet" media="screen">
<script src="js/lightbox/jquery.colorbox-min.js" type="text/javascript"></script>