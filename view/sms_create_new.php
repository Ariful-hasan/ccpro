<?php $num_select_box = 6;?>
<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>
<script type="text/javascript" src="js/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="js/bootstrap-multiselect/css/bootstrap-multiselect.css" type="text/css"/>

<script type="text/javascript" src="js/toastr/toastr.min.js"></script>

<script src="assets/plugins/multiple-file/jquery.MultiFile.min.js"></script>


<link rel="stylesheet" href="assets/plugins/typeahead/typeahead.min.css">
<link rel="stylesheet" href="assets/plugins/typeahead/bootstrap-tagsinput.css">
<script src="assets/plugins/typeahead/bootstrap3-typeahead.min.js"></script>
<script src="assets/plugins/typeahead/bootstrap-tagsinput.min.js"></script>


<script src="js/dpselections.js"></script>
<link rel="stylesheet" href="css/multiselect.css" type="text/css">
<?php include_once ('sms_template_modal.php');?>

<?php
$address_book_emails = [];

//GPrint($_REQUEST);die;
?>

<style>

    div#att_file_wrap_list {
        margin-top: 10px;
    }

    #sms_body{
        padding: 10px;
        font-size: 15px;
        color: black;
        height: 200px;
    }
</style>

<script type="text/javascript">

    set_num_select_box(<?php echo $num_select_box;?>);
    var default_lan = '';
    var ticketDID = '';
    var text_area_id = "'textarea#sms_body'";
    var did = '';
    var sid = '';
    var address_book_emails = <?php echo  !empty($address_book_emails) ? json_encode($address_book_emails) : '""';?>;
    function reload_sels_e(i, val) {
        // CKEDITOR.config._disp_id = val;
        reload_sels(i, val);
    }

    $(document).ready(function() {
        $('.multiselect').multiselect({buttonClass:'btn'});
        //load_sels(<?php //echo count($disposition_ids);?>//);

        $("#frm_ivr_service").validate({
            errorClass: "form-error",
            errorElement: "span",
        });

        did = $("#dd").val();
        click_template(text_area_id, did, sid);//////////////////From template_modal.php

        $('#att_file').MultiFile({
            STRING: {
                remove: '<i class="fa fa-minus-circle"></i>'
            },
            list: '#T7-list'
        });

        set_CC_BCC_FWD('input#cc_emails', '', '', address_book_emails);
        set_CC_BCC_FWD('input#bcc_emails', '', '', address_book_emails);
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

<form id="frm_ivr_service" name="frm_ivr_service" class="form-horizontal" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
    <div class="panel-body">
        <div class="row">

            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm mb-15">
                    <label for="cc_emails" class="control-label col-md-2 col-sm-2 pr-0">
                        From:
                    </label>
                    <div class="col-md-10 col-sm-10">
                        <input class="form-control" id="sms_from" name="sms_from" type="text" value="<?php echo $did ?>" readonly />

                    </div>
                </div>
            </div>

            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm mb-15">
                    <label for="cc_emails" class="control-label col-md-2 col-sm-2 pr-0">
                        To:<span class="error">*</span>
                    </label>
                    <div class="col-md-10 col-sm-10">
                        <input class="form-control" id="cc_emails" name="phone_numbers" value="<?php echo $request->getRequest('phone_numbers'); ?>" type="text" placeholder="Max 5 Recipient" required/>
                    </div>
                </div>
            </div>


            <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="mail_body" class="control-label col-md-2 col-sm-2 pr-0">
                            Template:
                        </label>
                        <div class="col-md-10 col-sm-10">
                            <?php
                            $tsk = "task=".$request->getControllerName();
                            $act = "&act=templates";

                            ?>

                            <button class="btn btn-default" type="button" id="temp" url="<?php echo $this->url($tsk.$act); ?>"> Template </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-sm-12">
                    <div class="form-group form-group-sm mb-15">
                        <label for="mail_body" class="control-label col-md-2 col-sm-2 pr-0">
                            Text:<span class="error">*</span>
                        </label>
                        <div class="col-md-10 col-sm-10">
                            <textarea class="form-control" id="sms_body" name="sms_body"  rows="10" cols="110" placeholder="Enter your sms here..." required> <?php echo $request->getRequest('sms_body'); ?> </textarea>
                        </div>
                    </div>
                </div>

            <div class="col-md-12 col-sm-12">
                <div class="form-group form-group-sm mb-15">
                    <label class="control-label col-md-2 col-sm-2 pr-0">
                    </label>
                    <div class="col-md-10 col-sm-10">
                        <?php
                        if($request->isPost()){
                            if($error_code == 200){ // error code = 200 means success
                                echo '
                                <div class="alert alert-success">'
                                    . $error_msg .
                                    '</div>';
                            }else{
                                echo '
                                <div class="alert alert-danger">'
                                    . $error_msg .
                                    '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>

                <div class="row">
                    <div class="col-md-12 col-sm-12 text-center">
                        <input class="btn btn-success form_submit_button" type="submit" value="Send SMS" name="submitservice">
                    </div>
                </div>
            </div>
        </div>

    </form>
