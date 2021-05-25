
<?php
include('controller/randomColor.php');

$num_select_box = 6;
$address_book_emails = [];
if (!empty($ccmails)){
    foreach ($ccmails as $item) {
        $address_book_emails[] = $item->name . ' (' . $item->email . ')';
    }
}
$email_signature = !empty($signature) && $signature->status == 'Y' ? nl2br(base64_decode($signature->signature_text)) : "";
$email_signature = !empty($email_signature) ? '<br/>' .$agent_name.'<br/><br/>'.$email_signature.'<br/>' : '<br/>'.$agent_name.'<br/>';
$email_signature = str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"",$email_signature);
$email_signature = addslashes($email_signature);
$last_email_body = str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"",$last_email_body);
$last_email_body = addslashes($last_email_body);

preg_match_all('/<img .*?>/',$last_email_body, $matches);
$search = [];
$replace = [];
foreach ($matches[0] as $key => $item) {
    $search[] = $item;
    $replace[] = stripslashes($item);
}
$last_email_body = str_replace($search, $replace, $last_email_body);

$email_sl = !empty($eTicketEmail) ? $eTicketEmail[0]->mail_sl : '';
$to_email_arr = explode(",", $eTickets->mail_to);

function getSessionWiseMail($data){
    $data_2 = $data;
    $response = [];
    $temp = [];
    $mail_list = [];
    $mail_bg_color = [];
    $last_sl = [];
    $mail_count = [];
    foreach ($data as $item){
        $temp_sl = 0;
        $count = 0;
        foreach ($data_2 as $key){
            if (!in_array($item->session_id, $temp) && $item->session_id == $key->session_id){
                $mail_list[] = $key;
                $temp_sl = $temp_sl < $key->mail_sl ? $key->mail_sl : $temp_sl;
                $count++;
            }
        }
        if (!in_array($item->session_id, $temp)){
            $mail_bg_color[$item->session_id] = RandomColor::many(1, array('luminosity'=>'light'))[0];
            $last_sl[$item->session_id] = $temp_sl;
            $mail_count[$item->session_id] = $count;
        }
        $temp[] = $item->session_id;
    }
    $response['mail_list'] = $mail_list;
    $response['mail_bg_color'] = $mail_bg_color;
    $response['last_sl'] = $last_sl;
    $response['session_mail_count'] = $mail_count;
    return $response;
}

?>

<!-- <script src="js/jquery.MultiFile.pack.js"></script> -->
<script src="assets/plugins/multiple-file/jquery.MultiFile.min.js"></script>
<!--<script src="assets/js/email_details.js"></script>-->
<link rel="stylesheet" href="assets/css/email_details.css">

<!-- Include the plugin's CSS and JS: -->
<script type="text/javascript" src="js/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="js/bootstrap-multiselect/css/bootstrap-multiselect.css" type="text/css"/>
<!--Added CDN for New Icons-->

<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
<link rel="stylesheet" href="assets/plugins/typeahead/typeahead.min.css">
<link rel="stylesheet" href="assets/plugins/typeahead/bootstrap-tagsinput.css">
<script src="assets/plugins/typeahead/bootstrap3-typeahead.min.js"></script>
<script src="assets/plugins/typeahead/bootstrap-tagsinput.js"></script>
<link rel="stylesheet" href="js/toastr/toastr.min.css">
<script src="js/toastr/toastr.min.js"></script>

<?php include_once ('lib/summer_note_lib.php')?>
<script type="text/javascript">

    //////send udp msg///////
    var email_id = '<?php echo $eTickets->ticket_id; ?>';
    var call_id = '<?php echo $callid; ?>';
    var agent_id = '<?php echo $agent_id; ?>';
    var attachment_save_path = "<?php echo $attachment_save_path; ?>";
    var forwarded_attachment_path = '';
    var fwd_multi_files_arr = [];
    var to_email_arr = '<?php echo json_encode($to_email_arr);?>';
    var skill_id = '<?php echo $eTickets->skill_id; ?>';

    var cc_tags_email_arr = [];
    var bcc_tags_email_arr = [];
    var fwd_tags_email_arr = [];
    //var last_email_body = '<?php //echo $last_email_body?>//';
    var mail_sl = '<?php echo $email_sl?>';
    var notification_showed_for_this_num = 0;

    <?php
    $str = explode('/',$_SERVER['SCRIPT_FILENAME']);
    array_pop($str);
    $base_path = implode('/',$str);
    ?>
    <?php //if (!empty($callid)) {?>
    function sendUdpMsg() {
        $.ajax({
            dataType: "JSON",
            type:     "POST",
            url:      "<?php echo site_url().$this->url('task=email&act=email-module-udp-msg');//site_url().base_url().'/udp.php'; ?>",
            data:     {method:"EMAIL_PING", email_id:email_id, call_id:call_id, agent_id:agent_id},
            success : function (res) {
                console.log( res);
            }
        });
        showNewEmailArriveNotification();
    }
    <?php //} ?>
    //////send udp msg///////

    var isSubmit = false;
    var ticketStatus = '';
    var ticketDID = '';
    var isValidate = false;

    function setStatus()
    {
        ticketStatus = $('#pstatus').val();
        if (typeof ticketStatus!=='undefined' && ticketStatus.length > 0){
            $.colorbox.close();
            isValidate = true;
        } else {
            $("#status_empty_div").text("Please Select Status.");
            $("#status_empty_div").removeClass('hide');
            $("#status_empty_div").show();
        }
    }

    function submit_form()
    {
        ////   isValidate is added for test purpose of outside click not subumit  ////
        if (isSubmit && isValidate) {
            $("#st").val(ticketStatus);
            $("#dd").val(ticketDID);
            var reschedule_datetime = $("#sdate").val();
            $("#reschedule_datetime").val(reschedule_datetime);
            if($("#message").val() != ""){
//		return false;
                $("#ticket_message").attr('action', '<?php echo $sublink; ?>');
                $("#ticket_message").submit();
            }else{
                alert("Message can not be empty!");
                return false;
            }
        }
        isSubmit = false;
    }

    function checkMsg(){
        isNewEmailArrive();

        //// For empty status ////
        $("#status_empty_div").removeClass('show');
        $("#status_empty_div").hide();
        //// For empty status ////

        var pattern = "<?php echo $msg_pattern; ?>";
        if($("#message").val() != ""){
            var msg = $("#message").val().toLowerCase();
            if (pattern != "" && pattern != null && msg.indexOf(pattern.toLowerCase()) >= 0){
                alert("You must replace the fixed text: "+pattern);
                return false;
            }else{
                isSubmit = true;
                ticketStatus = '';
                $.colorbox({inline:true, width:"450", height:"450", href:"#inline_content",onOpen: function(){show_reschedule_date();}, onClosed:function(){ submit_form(); }});
                return false;
            }
        }else{
            alert("Message can not be empty!");
            return false;
        }
    }
    agentCallStack = [];
    $(document).ready(function() {
        defaultFirstOpenTime();
        $('#att_file').MultiFile({
            STRING: {
                remove: '<i class="fa fa-minus-circle"></i>'
            },
            list: '#T7-list'
        });

        $(".email_title").click(function() {
            $("#b"+$(this).attr('id')).toggle();
            var short_body = $("#sh"+$(this).attr('id'));
            var tdid = $("#"+$(this).attr('id')).find('i');
            if (tdid.hasClass('fa fa-minus-circle')){
                tdid.removeClass('fa fa-minus-circle');
                tdid.addClass('fa fa-plus-circle');
                short_body.css("display", "none");
            }else {
                tdid.removeClass('fa fa-plus-circle');
                tdid.addClass('fa fa-minus-circle');
                short_body.css("display", "block");
            }

            ////   Update First Open Time   ////
            var session_id = $(this).attr('sid');
            var mail_sl = $(this).attr('id');
            mail_sl= mail_sl.substring(1, mail_sl.length);
            updateFirstOpenTime(session_id,mail_sl,skill_id);
        });

        $("#status_change").click(function() {
            $("#spn_new_status").show();
        });

        $("#btn_status_change").click(function() {
            var txt = $("#new_status option:selected").text();
            var val = $("#new_status").val();

            if (confirm('Are you sure to change the status to "' + txt + '"?')) {
                $.post('<?php echo site_url().$this->url('task='.$request->getControllerName()."&act=statusupdate");?>', { 'tid':'<?php echo $eTickets->ticket_id;?>', 'status':val },
                    function(data, status){
                        if(status=='success' && data == 'Y') {
                            window.location.href=window.location.href;
                        } else {
                            alert('Failed to update status!!' + status);
                        }
                    }
                );
            }
        });

        $(".set-options").colorbox({
            //onClosed:function(){ window.location = window.location.href;},
            onClosed:function(){ $.colorbox.close()},
            iframe:true, width:"470", height:"400"
        });


        $('.multiselect').multiselect({buttonClass:'btn'});

        <?php $mail_sl = !empty($eTicketEmail)?count($eTicketEmail):''?>

        //////RND MODAL//////
        //$("#myModal").modal();
        var did = '<?php echo json_encode($eTickets->disposition_id)?>';
        var sid = '<?php echo json_encode($eTickets->skill_id)?>';
        text_area_id = "'textarea#message'";
        click_template(text_area_id, did, sid);//////////////////From template_modal.php

        load_sels();  ////DID CHANGE STATUS//////
        <?php //include_once ('js/summer_note_call.js')?>
        Initialize_Summer_note('textarea#message', ['view'], "<?php echo site_url().$this->url('task=email&act=uploadEmailImage&tid='.$eTickets->ticket_id.'&mid='.$mail_sl); ?>", "<?php echo site_url().$this->url('task=email&act=deleteUploadedImage'); ?>", "<?php echo $attachment_save_path; ?>");

        //////06-08-18///////
        $("#sdate").datetimepicker({
            defaultDate: new Date(),
            format:"d/m/Y H:i"
        });
        <?php if (!empty($is_setinterval_active)) { ?>
        setInterval(function () {
            sendUdpMsg();
        },20000);
        <?php } ?>
        //////06-08-18///////

        //////   START  22-09-18     //////
        <?php
        $js_cc = !empty($cc_emails) ? explode(',', $cc_emails) : '';
        $ds_cc = !empty($js_cc) ? implode('","',$js_cc) : "";

        $js_bcc = !empty($bcc_emails) ? explode(',', $bcc_emails) : '';
        $ds_bcc = !empty($js_bcc) ? implode('","',$js_bcc) : "";
        ?>
        var ds_cc = <?php echo  !empty($ds_cc) ? '["'.$ds_cc.'"]' : "''" ;?>;
        var ds_bcc = '<?php echo  !empty($ds_bcc) ? '["'.$ds_bcc.'"]' : "" ;?>';

        $("#reply").on('click', function () {
            showEditingPart();
            unselectOptions("#email_cc");
            unselectOptions("#email_bcc");
            unselectOptions("#forward");
            unset_hidden_value("#specific_email_sl");
            hide_specific_email_text();
            add_last_email_body();
            unset_hidden_value("#is_reply_all");
        });
        $("#reply-all").on('click', function () {
            showEditingPart();
            //unselectOptions("#forward");
            //selectMails("#email_bcc", ds_bcc);
            //selectMails("#email_cc", ds_cc);
            unselectOptions("#forward");
            selectMails("#email_cc", "<?php echo $cc_emails?>");
            selectMails("#email_bcc", "<?php echo $bcc_emails?>");
            unset_hidden_value("#specific_email_sl");
            hide_specific_email_text();
            add_last_email_body();
            set_value("#is_reply_all", "Y");
        });
        $("#btn-forward").on('click', function () {
            showEditingPart();
            unselectOptions("#email_cc");
            unselectOptions("#email_bcc");
            unset_hidden_value("#specific_email_sl");
            hide_specific_email_text();
            add_last_email_body('FWD');
            get_attachment(email_id, '<?php echo $email_sl?>');
            unset_hidden_value("#is_reply_all");
        });
        //////   START  22-09-18     //////

        ///////  START every email TO-CC-Froward ///////
        $('div.to_cc_bcc a').on('click', function (event) {
            $(this).parent().toggleClass('open');
        });
        $('body').on('click', function (e) {
            if (!$('div.dropdown.to_cc_bcc').is(e.target)
                && $('div.dropdown.to_cc_bcc').has(e.target).length === 0
                && $('.open').has(e.target).length === 0
            ) {
                $('div.dropdown.to_cc_bcc').removeClass('open');
            }
        });
        ///////  END every email TO-CC-Froward ///////

        var address_book_emails = <?php echo  !empty($address_book_emails) ? json_encode($address_book_emails) : '""';?>;
        set_CC_BCC_FWD('input#email_cc', cc_tags_email_arr, '#cc_tags_email_arr', address_book_emails);
        set_CC_BCC_FWD('input#email_bcc', bcc_tags_email_arr, '#bcc_tags_email_arr', address_book_emails);
        set_CC_BCC_FWD('input#forward', fwd_tags_email_arr, '#fwd_tags_email_arr', address_book_emails);

        $(".specific_reply").on('click', function (e) {
            unselectOptions("#forward");
            unselectOptions("#email_cc");
            unselectOptions("#email_bcc");
            var specific_reply_sl = $(this).data("email_sl");
            showEditingPart();
            go_to_editing_part("#editing_part");
            //show_specific_email_text();
            set_value("#session_id",$(this).data("session_id"));
            //set_value("#specific_email_sl", specific_reply_sl);
            get_specific_reply_email(email_id, specific_reply_sl);
        });
        $(".specific_reply_all").on('click', function (e) {
            unselectOptions("#forward");
            selectMails("#email_cc", "<?php echo $cc_emails?>");
            selectMails("#email_bcc", "<?php echo $bcc_emails?>");
            var specific_reply_sl = $(this).data("email_sl");
            showEditingPart();
            go_to_editing_part("#editing_part");
            //show_specific_email_text();
            set_value("#session_id",$(this).data("session_id"));
            //set_value("#specific_email_sl", specific_reply_sl);
            get_specific_reply_email(email_id, specific_reply_sl);
        });
        $(".specific_forward").on('click', function (e) {
            unselectOptions("#forward");
            unselectOptions("#email_cc");
            unselectOptions("#email_bcc");
            var specific_reply_sl = $(this).data("email_sl");
            showEditingPart();
            go_to_editing_part("#editing_part");
            //show_specific_email_text();
            set_value("#session_id",$(this).data("session_id"));
            //set_value("#specific_email_sl", specific_reply_sl);
            get_specific_reply_email(email_id, specific_reply_sl);
        });


    });

    function updateFirstOpenTime(session_id,mail_sl, skill_id) {
        var skill_id = JSON.parse(skill_id);
        $.ajax({
            dataType    : "JSON",
            type        : "POST",
            url         : "<?php echo site_url().$this->url("task=" . $request->getControllerName() . '&act=update-first-open-time'); ?>",
            data        : {tid:email_id, sid:session_id, mail_sl:mail_sl, skill_id:skill_id},
            success     : function (res) {
                console.log(res);
            }
        });
    }
    function defaultFirstOpenTime() {
        if( $(".email_title").length > 0 && $(".email_title").css('display').toLowerCase() == 'block') {
            var session_id = $(".email_title").data("sid");
            var mail_sl = $(".email_title").attr("id");
            mail_sl= mail_sl.substring(1, mail_sl.length);
            updateFirstOpenTime(session_id,mail_sl,skill_id);
        }
    }

    function get_specific_reply_email(ticket_id, mail_sl) {
        if (typeof ticket_id !=='undefined' && ticket_id.length > 0 && typeof mail_sl !=='undefined' && mail_sl.length == 3){
            $.ajax({
                dataType    : "JSON",
                type        : "POST",
                url         : "<?php echo site_url().$this->url("task=" . $request->getControllerName() . '&act=get-email-text'); ?>",
                data        : {ticket_id:ticket_id, mail_sl:mail_sl},
                success     : function (res) {
                    console.log(res);
                    var str = '<b>---------- Previous Message -----------</b><br/><br/>';
                    str = '<?php echo '<br/><br/><br/><br/>'.$email_signature;?>' +str+ res;
                    $('#message').summernote('reset');
                    $("#message").summernote('code',  str);
                    $("#message").summernote({focus: true});
                    set_language_bar_in_summernote();
                }
            });
        }
    }
    function delete_forwarded_attachment(id) {
        var file = $("#attachment_div"+id).data('fname');
        console.log(file);
        var idx = fwd_multi_files_arr.indexOf(file);
        fwd_multi_files_arr.splice(idx,1);
        $("#forwarded_attachment_hidden").val(JSON.stringify(fwd_multi_files_arr));
        $("#attachment_div"+id).remove();
    }
    function get_attachment(ticket_id, mail_sl) {

        if (typeof mail_sl !=='undefined' && mail_sl.length===3){
            $.ajax({
                dataType    : "JSON",
                type        : "POST",
                url         : "<?php echo site_url().$this->url("task=" . $request->getControllerName() . '&act=get-attachment'); ?>",
                data        : {ticket_id:ticket_id, mail_sl:mail_sl},
                success : function (res) {
                    if (typeof res.data !=='undefined' && res.data.length > 0){
                        var STRING = '';
                        var sl = 0;
                        $.each(res.data, function (key, value) {
                            STRING = '<div class="MultiFile-label" id="attachment_div'+sl+'" data-fname="'+value.file_path+'">'+
                                '<a class="MultiFile-remove" href="#att_file" onclick="delete_forwarded_attachment(' + sl + ')">' +
                                '<i class="fa fa-minus-circle"></i>' +
                                '</a>' +
                                '<span>' +
                                '<span class="MultiFile-label" title="File selected:' + value.file_name + '">' +
                                '<span class="MultiFile-title">' +
                                value.file_name +
                                '</span>' +
                                '</span>' +
                                '</span>' +
                                '</div>';
                            $("#prev_attach").append(STRING);
                            fwd_multi_files_arr.push(value.file_path);
                            sl++;
                        });
                        set_forwarded_attachment_path(res.forwarded_attachment_path);
                        $("#forwarded_attachment_hidden").val(JSON.stringify(fwd_multi_files_arr));
                    }
                }
            });
        }
    }
    function set_forwarded_attachment_path(path) {
        forwarded_attachment_path = path;
    }
    function add_last_email_body(type) {
        var str = type=="FWD" ? '<b>---------- Forwarded Message -----------</b><br/><br/>' :'<b>---------- Previous Message -----------</b><br/><br/>';
        str = '<?php echo '<br/><br/><br/><br/>'.$email_signature;?>'+str+'<?php echo $last_email_body; ?>';
        $('#message').summernote('reset');
        $("#message").summernote('code',  str);
        $("#message").summernote({focus: true});
        set_language_bar_in_summernote();
    }
    function show_specific_email_text() {
        $("#replying_email_text").removeClass('hide');
        $("#replying_email_text").show();
    }
    function hide_specific_email_text() {
        $("#replying_email_text").removeClass('show');
        $("#replying_email_text").hide();
    }
    function unset_hidden_value(element) {
        $(element).val('');
    }
    function set_value(element, value) {
        $(element).val(value);
    }
    function go_to_editing_part(element) {
        $('html , body').animate({
            scrollTop: $(element).offset().top
        }, 1000);
    }

    function set_CC_BCC_FWD(element, tempArray, store_element, data) {
        $(element).tagsinput({
            typeahead: {
                source: data,
            },
            confirmKeys: [13, 44, 186]
        });
        $(".bootstrap-tagsinput input").on('blur', function() {
            $(this).trigger(jQuery.Event('keypress', {which: 13}));
        });
        $(element).on('itemAdded', function(event) {
            if (tempArray.indexOf(event.item) < 0){
                tempArray.push(event.item);
                console.log(tempArray);
                $(store_element).val(JSON.stringify(tempArray));
            }
        });
        $(element).on('itemRemoved', function(event) {
            var idx = tempArray.indexOf(event.item);
            tempArray.splice(idx,1);
            $(store_element).val(JSON.stringify(tempArray));
        });
    }


    //////   START  22-09-18     //////
    function showEditingPart() {
        if ($("#editing_part").hasClass('hide')){
            $("#editing_part").removeClass('hide');
            $("#editing_part").addClass('show');
        }
    }
    function unselectOptions(element) {
        //$(element+" option:selected").prop("selected", false).change();
        $('input'+element).tagsinput('removeAll');
    }
    function selectMails(element, mails) {
        if (typeof mails !== 'undefined' && mails.length > 0){
            //$(element).val(mails).change();
            $('input'+element).tagsinput('add', mails);
            $('input'+element).tagsinput('refresh');
        }
    }
    //////   START  22-09-18     //////

    function show_reschedule_date() {
        $("#pstatus").on('change', function (e) {
            var status_val = $(this).val();
            if (status_val == 'R'){
                $("#sdate").removeClass('hide');
                $("#sdate").addClass('show');
            } else {
                $("#sdate").removeClass('show');
                $("#sdate").addClass('hide');
            }
        });
        $("#pstatus").change();
    }

    ////DID CHANGE STATUS//////
    function reload_sels(i, val) {
        if (i < <?php echo $num_select_box;?>) {
            hide_sels(i);
            var j = i+1;
            $select = $("#disposition_id" + j);
            ticketDID = val;
            $select.html('<option value="">Select</option>');
            if (val.length > 0) {
                var jqxhr = $.ajax({
                    type: "POST",
                    url: "<?php echo site_url().$this->url("task=" . $request->getControllerName() . '&act=dispositionchildren');?>",
                    data: { did: val },
                    dataType: "json"
                })
                    .done(function(data) {
                        show_sels(j+1);
                        if (data.length == 0) {
                            //console.log('0');
                            $select.hide();
                        } else {
                            $.each(data, function(key, val){
                                $select.append('<option value="' + key + '">' + val + '</option>');
                            })
                        }
                    })
                    .fail(function() {
                        //
                    })
                    .always(function() {
                    });
            }
        }
    }
    function load_sels() {
        var num_dids = <?php echo count($disposition_ids);?>;
        if (num_dids == 0) num_dids++;
        show_sels(num_dids);
    }
    function hide_sels(i) {
        for (j=i+2; j < <?php echo $num_select_box;?>; j++) {
            $("#disposition_id" + j).hide();
        }
    }
    function show_sels(i) {
        for (j=0; j < i; j++) {
            $("#disposition_id" + j).show();
        }
    }
    /////DID END////

    function removeMainLoader() {
        $("#MainLoader").remove();
    }

    function skipEmail(is_setinterval_active, evt_type='') {
        <?php if (!empty($callid)) {?>
        var skip='skip';
        var close = 'close';
        $.ajax({
            dataType    :   "JSON",
            type        :   "POST",
            url         :   "<?php echo site_url().$this->url('task=email&act=email-module-udp-msg')//echo site_url().base_url().'/udp.php'; ?>",
            data        :   {method:"AG_EMAIL_CLOSE", email_id:email_id, call_id:call_id, agent_id:agent_id, skip:skip},
            success : function (res) {
            }
        });
        <?php } else { ?>
        if (typeof is_setinterval_active !=='undefined' && is_setinterval_active!==''){
            $.ajax({
                dataType    :   "JSON",
                type        :   "POST",
                url         :   "<?php echo site_url().$this->url('task=email&act=EmptyCurrentUser');?>",
                data        :   {tid: email_id},
                success : function (res) {
                }
            });
        }
        <?php } ?>
        window.location.href = "<?php echo site_url().$this->url('task='.$request->getControllerName().'&callid='.$callid.'&cookie=Y');?>";
    }

    function workOnThisEmail() {
        $.ajax({
            dataType    :   "JSON",
            type        :   "POST",
            url         :   "<?php echo site_url().$this->url('task='.$request->getControllerName().'&act=manuallyWorkOnEmail') ?>",
            data        :   {tid: email_id},
            success     : function (res) {
                if (typeof res.msg !== 'undefined' && res.msg !== null && res.msg === 'success') {
                    sendUdpMsg();
                }
                window.location.href= "<?php echo site_url().$this->url('task='.$request->getControllerName().'&act=details-multi&tid='.$eTickets->ticket_id.'&callid='.$callid) ?>";
            }
        });
    }

    function isNewEmailArrive(){
        $.ajax({
            dataType    :   "JSON",
            type        :   "POST",
            url         :   "<?php echo site_url().$this->url('task='.$request->getControllerName().'&act=is-new-email-arrive') ?>",
            data        :   {tid: email_id, mail_sl: mail_sl},
            success     : function (res) {
                if (typeof res !== 'undefined' && res !== null ) {
                    var old_email_num = parseInt(mail_sl);
                    if (res > old_email_num){
                        var mail_count = parseInt(res-old_email_num);
                        $("#new_email_arrive_msg").html("You have "+mail_count+" new Email !");
                        $(".reload_page_tr").removeClass('hide');
                        $(".reload_page_tr").show();
                    } else {
                        $(".reload_page_tr").removeClass('show');
                        $(".reload_page_tr").hide();
                    }
                }
            }
        });
    }

    function showNewEmailArriveNotification() {
        $.ajax({
            dataType    :   "JSON",
            type        :   "POST",
            url         :   "<?php echo site_url().$this->url('task='.$request->getControllerName().'&act=is-new-email-arrive') ?>",
            data        :   {tid: email_id, mail_sl: mail_sl},
            success     : function (res) {
                if (typeof res !== 'undefined' && res !== null ) {
                    var old_email_num = parseInt(mail_sl);
                    if ( (res > old_email_num) && (notification_showed_for_this_num != res)){
                        toastr.options.timeOut = 30000;
                        var number_of_mail_count = parseInt(res-old_email_num);
                        toastr.success('You have '+number_of_mail_count+' new email!');
                        setNotificationNumber(res);
                    }
                }
            }
        });
    }
    function setNotificationNumber(value){
        this.notification_showed_for_this_num = value;
    }


</script>
<?php include_once ('template_modal.php');?>
<link rel="stylesheet" type="text/css" media="screen" href="css/form.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/report.css">
<div class="">
    <?php if ((!empty($queue_msg) || !empty($agent_msg)) && empty($callid) && empty($current_user)) { ?>
        <div class="alert alert-danger"><?php echo  !empty($queue_msg) ? $queue_msg : $agent_msg; ?></div>
    <?php }?>
    <table width="90%" cellspacing="0" cellpadding="5" border="0" class="form_table" style="margin-bottom: 5px;">
        <tbody>
        <tr class="form_row_head">
            <td>
                <div class="row">
                    <div class="col-lg-6 col-md-6 pull-left">
                        <?php if(!empty($isAllowedToPull) && empty($is_setinterval_active)): ?>
                        <?php else: ?>
                            <span class=""><i class="fa fa-user <?php echo empty($is_setinterval_active)?'other':'own'?> "></i></span> <?php echo $current_user?>
                            <?php  if (empty($is_setinterval_active)){ ?><div class="help"><div class="typing-loader text"></div></div><?php } ?>
                        <?php endif; ?>
                    </div>
                    <div class=" col-md-6" style="margin-right: 0px">
                        <?php if(!empty($isAllowedToPull) && empty($is_setinterval_active)): ?>
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-sm btn-warning pull-right" id="skip" onclick="skipEmail('<?php echo $is_setinterval_active?>', 'skip')"><i class="fa fa-arrow-left" aria-hidden="true"></i> skip </button>
                                </div>
                                <div class=" col-md-4">
                                    <button  class="btn btn-sm btn-success  pull-right" id="work_on" onclick="workOnThisEmail()"><i class="fa fa-pencil-square" aria-hidden="true"></i>  Pull</button>
                                </div>
                                <div class="col-md-4">
                                    <a  href="<?php echo site_url().$this->url('task=email&act=transfer&tid='.$eTickets->ticket_id.'&callid='.$callid)?>" class="btn btn-sm btn-info set-options" id="work_on"><i class="fa fa-exchange" aria-hidden="true"></i> Transfer</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php if (!empty($is_setinterval_active)):?><button type="button" class="btn btn-sm btn-danger pull-right" id="skip" onclick="skipEmail('<?php echo $is_setinterval_active?>', 'close')"><i class="fa fa-times" aria-hidden="true"></i> Close</button><?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </td>
        </tr>
        <tr class="form_row">
            <td>
                <table width="100%">
                    <tbody>
                    <tr>
                        <td width="25%"><b>User:</b> <?php echo $eTickets->created_for; ?></td>
                        <td width="50%"><b>Created: </b> <?php if ($eTickets->create_time!="" && $eTickets->created_by!=""){echo date("Y-m-d H:i:s", $eTickets->create_time). " by ".$eTickets->created_by;} ?></td>
                        <td width="50%">

                            <?php
                            if (!empty($eTickets->mail_to)){ ?>
                                <?php if (count($to_email_arr) > 1) { ?>
                                    <div class="dropdown to_cc_bcc">
                                        <?php echo "<b>To:</b>".$to_email_arr[0] ;?>
                                        <a href="javascript:void(0)" id="dropdownMenuButton" type="button"  aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-caret-down" aria-hidden="true"></i>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <div class="">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="col-md-8">
                                                            <?php foreach ($to_email_arr as $_to) {?>
                                                                <?php echo $_to;?>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } else {
                                    echo "<b>To:</b>".$to_email_arr[0] ;
                                    ?>
                                <?php } ?>
                                <?php
                            } elseif (!empty($eTickets->fetch_box_email)) { ?>
                                <?php echo "<b>To:</b>".$eTickets->fetch_box_email;?>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Skill:</b> <?php echo $eTickets->skill_name; ?>
                            <?php if ($update_privilege && ($acd_status != 'N' || $dist_status=='R') && !empty($is_setinterval_active)):?>
                                <a class="set-options" href="<?php echo site_url().$this->url('task='.$request->getControllerName()."&act=skill&tid=".$eTickets->ticket_id);?>">
                                    <?php if (empty($eTickets->skill_name)) echo 'Add'; else echo 'Transfered';?>
                                </a>
                            <?php endif;?>
                        </td>
                        <td>
                            <?php $status  = !empty($eTickets->show_status) ? $eTicket_model->getTicketStatusLabel($eTickets->show_status) : $eTicket_model->getTicketStatusLabel($eTickets->status) ;?>
                            <b>Status:</b> <?php if ($eTickets->last_update_time!=""){echo '<font style="background-color:#eee;padding:1px;"><i>' . $status."</i></font> on ".date("Y-m-d H:i:s", $eTickets->last_update_time). " by ".$status_updated_by;} ?>
                            <?php
                            $changable_status = $eTicket_model->getChangableTicketStatus('K');  /////Every status it can be pullable.
                            if ($eTickets->status == 'E' && (UserAuth::hasRole('admin') || UserAuth::hasRole('supervisor'))) {
                                $status_updatable = true;
                            } else {
                                $status_updatable = false;
                            }

                            //////   Change Status /////////
                            //                            if ($status_updatable || ($update_privilege && is_array($changable_status) && count($changable_status) > 0)):
                            //                                if (($acd_status != 'N' || $dist_status =='R') && !empty($is_setinterval_active)): ?>
                            <!--                                    <a class="set-options" href="--><?php //echo site_url().$this->url('task='.$request->getControllerName()."&act=status&tid=".$eTickets->ticket_id.'&callid='.$callid);?><!--">-->
                            <!--                                        Change status-->
                            <!--                                    </a>-->
                            <!--                                --><?php //endif; ?>
                            <!--                            --><?php //endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <?php if ( ($acd_status=='' || $acd_status == 'N') && (!empty($is_setinterval_active))) {?>
                            <td>
                                <b>Agent:</b>
                                <?php if (isset($assigned_to) && !empty($assigned_to)) echo $assigned_to->agent_id . ' - ' . $assigned_to->nick; ?>
                                <?php if ($update_privilege && is_array($changable_status) && count($changable_status) > 0):?>
                                    <a class="set-options" href="<?php echo site_url().$this->url('task='.$request->getControllerName()."&act=assign&tid=".$eTickets->ticket_id);?>">
                                        <?php if (empty($eTickets->assigned_to)) echo 'Transfered'; else echo 'Transfered';?>
                                    </a>
                                <?php endif;?>
                            </td>
                        <?php } ?>
                        <td>
                            <b>Disposition:</b>
                            <?php if (!empty($disposition)) {
                                $path = $eTicket_model->getDispositionPath($disposition->disposition_id);
                                if (!empty($path)) echo $path . ' -> ';
                                echo $disposition->title;
                            }

                            ?>
                            <?php /*if ($update_privilege && is_array($changable_status) && count($changable_status) > 0 && $acd_status != 'N' && !empty($is_setinterval_active)):*/?>
                            <?php if ($update_privilege && is_array($changable_status) && count($changable_status) > 0 && ($acd_status != 'N' || $dist_status=='R') && !empty($is_setinterval_active)):?>
                                <a class="set-options" href="<?php echo site_url().$this->url('task='.$request->getControllerName()."&act=disposition&tid=".$eTickets->ticket_id);?>">
                                    <?php if (empty($eTickets->disposition_id)) echo 'Add'; else echo 'Change';?>
                                </a>
                            <?php endif;?>
                        </td>
                    </tr>
                    <?php if (!empty($callid)) { ?>
                        <tr>
                            <td><b>Subject:</b> <?php echo !empty($pageTitle2) ? $pageTitle2 :'' ?></td>
                        </tr>
                    <?php } ?>
                </table>
        </tr>
        </tbody>
    </table>
</div>

<div class="row">
    <div class="col-md-12 col-sm-12">
        <?php if ($update_privilege && is_array($changable_status) && count($changable_status) > 0): ;?>
            <?php if ($acd_status != 'N' && !empty($is_setinterval_active)) { ?>
                <div class="col-md-12 col-sm-12" style="padding-bottom: 1%; margin-left: -44px">
                    <div class="col-md-6 col-sm-8">
                    </div>
                    <div class="col-md-6"><div id="replying_email_text" class="hide replying_email_text"><b>Replying only single email</b></div></div>
                </div>
                <div id="editing_part" class="col-md-12 col-sm-12 hide" style="padding-bottom: 1%">
                    <form method="post" enctype="multipart/form-data" name="ticket_message" id="ticket_message">
                        <div class="row">
                            <div class="col-md-12" style="margin-left: -26px;">
                                <div class="col-md-12">
                                    <label for="email_cc" class="col-md-1">CC</label>
                                    <div class="col-md-11 form-group">
                                        <input class="form-control" id="email_cc" name="cc" type="text" value="<?php //echo !empty($eTicketEmail[0]->mail_cc) ? $cc_emails: ''?>"/>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label for="email_bcc" class="col-md-1">BCC</label>
                                    <div class="col-md-11 form-group">
                                        <input class="form-control" id="email_bcc" name="bcc" type="text" value="<?php //echo !empty($eTicketEmail[0]->mail_bcc) ? $bcc_emails: ''?>"/>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label for="forward" class="col-md-1">Forward</label>
                                    <div class="col-md-11 form-group">
                                        <input class="form-control" id="forward" name="forward" type="text" value=""/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5 hide" style="margin-top: 6px">
                                <div class="col-md-12">
                                    <?php
                                    $tsk = "task=".$request->getControllerName(); $act = "&act=templates";$dd = !empty($disposition->disposition_id)?$disposition->disposition_id:'';
                                    $sid = !empty($eTickets->skill_id)?$eTickets->skill_id:"";
                                    ?>
                                    <label for="temp" class="col-md-1">
                                        <!--<button class="btn btn-default" type="button" id="temp" url="<?php /*echo $this->url($tsk.$act.'&did='.$dd.'&sid='.$sid); */?>"> Template </button>-->
                                    </label>
                                </div>
                            </div>
                        </div>
                        <?php
                        $tsk = "task=".$request->getControllerName();
                        $act = "&act=templates";
                        $dd = !empty($disposition->disposition_id)?$disposition->disposition_id:'';
                        $sid = !empty($eTickets->skill_id)?$eTickets->skill_id:"";
                        ?>

                        <label for="message">Message <font class="required">*</font></label><br>
                        <input type="hidden" name="st" id="st" value="" maxlength="1" />
                        <input type="hidden" name="dd" id="dd" value="<?php echo !empty($disposition->disposition_id) ? $disposition->disposition_id:'' ?>" maxlength="4" />
                        <input type="hidden" name="reschedule_datetime" id="reschedule_datetime" value="" />
                        <input type="hidden" name="cc_tags_email_arr" id="cc_tags_email_arr" value=""/>
                        <input type="hidden" name="bcc_tags_email_arr" id="bcc_tags_email_arr" value=""/>
                        <input type="hidden" name="fwd_tags_email_arr" id="fwd_tags_email_arr" value=""/>
                        <input type="hidden" name="specific_email_sl" id="specific_email_sl" value=""/>
                        <input type="hidden" name="forwarded_attachment_hidden" id="forwarded_attachment_hidden" value=""/>
                        <input type="hidden" name="is_reply_all" id="is_reply_all" value=""/>
                        <input type="hidden" name="total_email_num" id="" value="<?php echo $mail_sl?>"/>
                        <input type="hidden" name="session_id" id="session_id" value=""/>

                        <div class=" summernote-area"><!--col-md-10 col-sm-10-->
                            <textarea style="width:90%; height: 145px;" id="message" name="message"></textarea>
                        </div>

                        <?php if ($acd_status != 'N' && !empty($is_setinterval_active)) { ?>
                            <div class="col-md-12" style="margin-left: -28px">
                                <div class="col-md-1">
                                    <input class="form_submit_button pull-left" type="button" onclick="checkMsg()" value="Send" id="submitTicket" name="submitTicket" style="font-size:14px;padding:5px 0.84615em;" />
                                </div>
                                <div class="col-md-1">
                                    <button class="btn btn-default" type="button" id="temp" url="<?php echo site_url().$this->url($tsk.$act.'&did='.$dd.'&sid='.$sid); ?>"> Template </button>
                                </div>
                                <div class="col-md-10">
                                    <input type="file" id="att_file" name="att_file[]" multiple="multiple" class="col-md-12 pull-left maxsize-10240" style="width: 100%"/><br />
                                    <div id="prev_attach" class="col-md-12"></div>
                                    <div id="T7-list" class="col-md-12"></div>
                                </div>
                            </div>
                        <?php } ?>

                    </form>
                </div>
            <?php } ?>
        <?php else:?>
            <tr>
                <td>&nbsp;</td>
            </tr>
        <?php endif;?>
    </div>

    <div class="col-md-12 col-sm-12">
        <?php
        if (is_array($eTicketEmail)){
            $organize_email_data = getSessionWiseMail($eTicketEmail);
            $organize_eTicketEmail = $organize_email_data['mail_list'];
            $mail_bg_color = $organize_email_data['mail_bg_color'];
            $last_sl = $organize_email_data['last_sl'];
            $session_mail_count = $organize_email_data['session_mail_count'];
            $i = 0;
            foreach ($organize_eTicketEmail as $eEmail){    //////   New Organize MAIL   ///////
                $display = $i > 0 ? 'none' : 'block';
                $full_time_details = strlen($eEmail->tstamp)==10 ? date("M d ' Y, h:i:s a ", $eEmail->tstamp).get_time_ago($eEmail->tstamp): "processing"
                ?>
                <div class="col-md-12 col-sm-12 reply-header" style="background-color: <?php echo $mail_bg_color[$eEmail->session_id]?>">
                    <div style="cursor:pointer;" class="email_title col-md-12" id="m<?php echo $eEmail->mail_sl;?>" data-sid="<?php echo $eEmail->session_id;?>">&nbsp;
                        <div class="col-md-6 font-normal ml--33" style="display:inline-block;">
                            <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                            <?php echo $eEmail->agent_id=="" ? $eTickets->first_name.' '.$eTickets->last_name."[".$eEmail->from_email."]" : $eEmail->agent_id;?>
                        </div>
                        <div class="col-md-5 pull-right font-normal mr--30">

                        <span class="pull-right">
                            <span class="status"><?php echo  !empty($status_option_list[$eEmail->email_status]) ? $status_option_list[$eEmail->email_status] : 'New' ;?></span>
                            <?php echo $full_time_details?> <i class="fa <?php echo $display=='none'? 'fa-minus-circle':'fa-plus-circle'?> bl-nn" aria-hidden="true"></i>
                        </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-sm-12 reply-body" style="display:<?php echo $display;?>;" id="bm<?php echo $eEmail->mail_sl;?>">
                    <div class="col-md-12" style="overflow: scroll; min-height: 250px;">
                        <div class="dropdown to_cc_bcc">
                            <?php
                            echo "to ";
                            $_mail_to_arr = explode(",", $eEmail->mail_to);
                            $new_mail_to_arr = [];
                            foreach ($_mail_to_arr as $item) {
                                $new_mail_to_arr[] = substr($item, 0, strpos($item, "@"));
                            }
                            echo implode(',', $new_mail_to_arr);
                            if (!empty($eEmail->mail_cc)) {
                                $_CCs = explode(",", $eEmail->mail_cc);
                                $new_mail_to_arr = [];
                                foreach ($_CCs as $cc) {
                                    $new_mail_to_arr[] = substr($cc, 0, strpos($cc, "@"));
                                }
                                echo ','.implode(',', $new_mail_to_arr);
                            }
                            ?>
                            <a href="javascript:void(0)" id="dropdownMenuButton" type="button"  aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-caret-down" aria-hidden="true"></i>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <div class="cc-bcc-popup">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="col-md-2"><lable for="control-label">From</lable></div>
                                            <div class="col-md-10 pr-0">:<?php echo $eEmail->from_email; ?></div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="col-md-2"><lable for="control-label">To</lable></div>
                                            <div class="col-md-10 pr-0">
                                                :<?php
                                                $_mail_to_arr = explode(",",$eEmail->mail_to);
                                                $new_mail_to_arr = [];
                                                foreach ($_mail_to_arr as $item) {
                                                    $new_mail_to_arr[] = $item;
                                                }
                                                echo implode(', ', $new_mail_to_arr);
                                                if (empty($eEmail->mail_to) && !empty($eTickets->fetch_box_email)) {
                                                    echo $eTickets->fetch_box_email;
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <?php if ($eEmail->is_forward=="Y" && !empty($eEmail->mail_to)){
                                            $froward_arr = explode(",",$eEmail->mail_to); ?>
                                            <div class="col-md-12">
                                                <div class="col-md-2"><lable for="control-label">Fwd</lable></div>
                                                <div class="col-md-10">
                                                    :<?php foreach ($froward_arr as $fwd) {?>
                                                        <?php echo $fwd;?>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        <?php }?>
                                        <?php if (!empty($eEmail->mail_cc)){
                                            $_CCs = explode(",",$eEmail->mail_cc);
                                            $new_mail_to_arr = [];
                                            ?>
                                            <div class="col-md-12">
                                                <div class="col-md-2"><lable for="control-label">CC</lable></div>
                                                <div class="col-md-10 pr-0">
                                                    :<?php foreach ($_CCs as $cc) {?>
                                                        <?php
                                                        $new_mail_to_arr[] = $cc;
                                                        ?>
                                                    <?php } echo implode(', ', $new_mail_to_arr); ?>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-12">
                                            <div class="col-md-2"><lable for="control-label">Date</lable></div>
                                            <div class="col-md-10 pr-0">:<?php echo strlen($eEmail->tstamp)==10 ? date("M d ' Y, h:i:s a ", $eEmail->tstamp) : "Processing";?></div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="col-md-2"><lable for="control-label">Subject</lable></div>
                                            <div class="col-md-10 pr-0">:<?php echo $pageTitle2; ?></div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="col-md-2"><lable for="control-label">Status</lable></div>
                                            <div class="col-md-10 pr-0">:<?php echo isset($status_option_list[$eEmail->email_status])?$status_option_list[$eEmail->email_status]:'New' ; ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if(!empty($isAllowedToPull) && empty($is_setinterval_active)): ?>
                            <?php else: ?>
                                <?php if (!empty($is_setinterval_active)):?>
                                    <div class="col-md-2 pull-right" style="margin-right: -32px;">
                                        <div class="col-md-3 cursor-pointer" data-email_sl="<?php echo $eEmail->mail_sl?>">
                                            <?php if (!empty($last_sl[$eEmail->session_id]) && $last_sl[$eEmail->session_id]==$eEmail->mail_sl): ?>
                                                <?php if ( $status_updatable || ($update_privilege && is_array($changable_status) && count($changable_status) > 0)): ?>
                                                    <?php if (($acd_status != 'N' || $dist_status =='R') && !empty($is_setinterval_active)): ?>
                                                        <a class="set-options" title="Change Status" href="<?php echo $this->url('task='.$request->getControllerName().'&act=status-multi&tid='.$eTickets->ticket_id.'&callid='.$callid.'&sid='.$eEmail->session_id.'&msl='.$eEmail->mail_sl.'&mc='.$session_mail_count[$eEmail->session_id]);?>">
                                                            <i class="fa fa-list-ul pull-right" aria-hidden="true"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-3 cursor-pointer specific_reply" data-email_sl="<?php echo $eEmail->mail_sl?>" data-session_id="<?php echo $eEmail->session_id?>">
                                            <i class="fa fa-reply" aria-hidden="true" title="reply"></i>
                                        </div>
                                        <?php if (!empty($eTicketEmail[0]->mail_cc) || count($to_email_arr)>1) { ?>
                                            <div class="col-md-3 cursor-pointer specific_reply_all" data-email_sl="<?php echo $eEmail->mail_sl?>" data-session_id="<?php echo $eEmail->session_id?>">
                                                <i class="fa fa-reply-all" aria-hidden="true" title="reply-all"></i>
                                            </div>
                                        <?php }?>
                                        <div class="col-md-3 cursor-pointer specific_forward" data-email_sl="<?php echo $eEmail->mail_sl?>" data-session_id="<?php echo $eEmail->session_id?>">
                                            <i class="fa fa-arrow-right" aria-hidden="true" title="forward"></i>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php
                        $mail_body = '<br/>'.str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"",base64_decode($eEmail->mail_body));
                        echo stripslashes(nl2br($mail_body));
                        ?>
                    </div>
                    <?php if ($eEmail->has_attachment == 'Y') {
                        $attachemnts = $eTicket_model->getAttachments($eEmail->ticket_id, $eEmail->mail_sl);
                        ?>
                        <?php if (is_array($attachemnts)) {?>
                            <hr>
                            <div class="row">
                                <div class="col-md-12 mt-10">
                                    <ul class="mailbox-attachments clearfix">
                                        <?php
                                        foreach ($attachemnts as $att) {
                                            $obj = new stdClass();
                                            $obj->path = $attachment_save_path;
                                            $obj->tstamp = $eEmail->tstamp;
                                            $obj->tid = $eEmail->ticket_id;
                                            $obj->sl = $eEmail->mail_sl;
                                            $obj->fname = $att->file_name;

                                            $attachment_link = site_url().$this->url('task='.$request->getControllerName()."&act=attachment&tid=".$eEmail->ticket_id . "&sl=" . $att->mail_sl). "&p=" . $att->part_position;
                                            ?>
                                            <li>
                                                <span class="mailbox-attachment-icon has-img"><?php echo getFileTypeIcon($att->attach_subtype, $obj)?></span>
                                                <div class="mailbox-attachment-info">
                                                    <a class="mailbox-attachment-name" href="<?php echo $attachment_link; ?>" onclick="removeMainLoader()">
                                                        <i class="fa fa-paperclip"> <?php echo strlen($att->file_name) > 25? substr($att->file_name,0,22)."...":$att->file_name  ?></i>
                                                    </a>
                                                    <?php if(isImageFile($att->attach_subtype)){
                                                        $ym = date("ym",$obj->tstamp);
                                                        $day = date("d",$obj->tstamp);
                                                        $image_link = $read_image_file_path.'?type=attachment' . '&name=' . $obj->fname . '&ym='.$ym.'&day='.$day.'&tid=' . $eEmail->ticket_id . '&sl=' . $att->mail_sl;
                                                        ?>
                                                        <div align="center" ><a data-toggle="modal" data-target="#showImgModal" data-image-src="<?php echo $image_link;?>" style="cursor: pointer;"><i class="fa fa-eye" >View</i></a></div>
                                                    <?php }?>
                                                </div>
                                            </li>
                                        <?php }?>
                                    </ul>
                                </div>
                            </div>

                            <div class="modal fade" id="showImgModal" role="dialog">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">View Image</h4>
                                        </div>
                                        <div class="modal-body">
                                            <img id="showImg" src=""  style="max-width: 100%;height: auto;" />
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php }?>
                    <?php }?>
                    <br>
                </div>
                <div class="col-md-12 col-sm-12 reply-short">
                    <div class="pt-5 pb-5" style="display:<?php echo $display=='block'?'none':'block'?>;" id="shm<?php echo $eEmail->mail_sl;?>">
                        <span><?php $short_str = str_replace('\r\n','',nl2br(base64_decode($eEmail->mail_body))); echo substr(strip_tags($short_str),0,30)."......." ?></span>
                    </div>
                </div>
                <?php
                $i++;
            }
        }
        ?>
    </div>
</div>

<?php if ($update_privilege):?>
    <div style='display:none'>
        <div id='inline_content'>
            <div class="popup-body">
                <center>
                    <div class="title-area">
                        <div class="top-title">Update status of ticket</div>
                        <div class="clearb"></div>
                    </div>
                    <table class="form_table" style="width:90%;">
                        <div class="col-md-12 alert alert-warning reload_page_tr hide"><span id="new_email_arrive_msg"></span></div>
                        <tr class="form_row_alt light_element">
                            <td class="form_column_caption" style="text-align:center;"><b>Status:</b>
                                <div class="col-md-12 alert alert-danger hide" id="status_empty_div"><span id="status_empty_msg"></span></div>
                                <select name="pstatus" id="pstatus">
                                    <option value="">Select</option>
                                    <?php if (is_array($changable_status)) {?>
                                        <?php foreach ($changable_status as $st)  {?>
                                            <option value="<?php echo $st ?>"><?php echo $eTicket_model->getTicketStatusLabel($st)?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                                <input style="margin-top: 3%" type="text" name="sdate" id="sdate" class="form-control hide" value="<?php echo !empty($eTickets->reschedule_time) ? date("d/m/Y H:i", $eTickets->reschedule_time) : date("d/m/Y H:i")?>" autocomplete="off">
                            </td>
                        </tr>

                        <tr class="form_row_alt light_element">
                            <td class="form_column_caption " style="text-align:center;"><b> Disposition:</b>
                            </td>
                        </tr>
                        <tr class="form_row light_element">
                            <td class="form_column_caption" style="text-align:center;">
                                <?php
                                for ($i=0; $i<$num_select_box; $i++) {
                                    echo '<select name="disposition_id'.$i.'" id="disposition_id'.$i.'" style="display:none;" onchange="reload_sels('.$i.', this.value);"><option value="">Select</option>';
                                    if ($i == 0 || isset($disposition_ids[$i])) {
                                        if (!isset(${'dispositions'.$i})) {
                                            if ($i > 0) {
                                                $dispositions = $eTicket_model->getDispositionChildrenOptions($eTickets->skill_id, $disposition_ids[$i-1][0]);
                                            } else {
                                                $dispositions = $eTicket_model->getDispositionChildrenOptions($eTickets->skill_id, '');
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
                                    }
                                    echo '</select>';
                                }
                                ?>
                            </td>
                        </tr>

                        <tr class="form_row light_element">
                            <td class="form_column_submit" style="text-align:center;padding:20px 0;">
                                <input class="form_submit_button" type="button" value="OK" name="submitagent" onclick="setStatus();" />
                            </td>
                        </tr>
                    </table>
                </center>
            </div>
        </div>
    </div>
<?php endif;?>


<script>
    $('#showImgModal').on('show.bs.modal', function (e) {
        var imgSrc = $(e.relatedTarget).data('image-src');
        $(e.currentTarget).find('#showImg').attr('src', imgSrc);
    });
</script>





