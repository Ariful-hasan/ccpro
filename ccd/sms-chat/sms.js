function createSmsWindow(session_id, callid, phone_number, did) {

    var ping_param = {"session_id": session_id, "call_id": callid};
    $.ajax({
        type: "POST",
        url: user_sms_session_url,
        data: {"cli": phone_number},
        dataType: "json"
    }).success(function (data) {
        if (data) {

        	 data.sort(function(a, b){
                var keyA = new Date(a.msg_time),
                    keyB = new Date(b.msg_time);
                // Compare the 2 dates
                if(keyA < keyB) return -1;
                if(keyA > keyB) return 1;
                return 0;
            });

            var interval_id = setInterval(sms_ping, 20000, ping_param);

            var html = '<div class="row chat-window col-xs-5 col-md-3" id="s-' + session_id + '" data-cid="c-' + callid + '" data-phone="' + phone_number + '" data-did="' + did + '" data-iid="'+ interval_id +'" ><div class="col-xs-12 col-md-12"><div class="panel panel-default"><div class="panel-heading top-bar"><div class="col-md-7 col-xs-7" style="padding-left: 0"> <h4 class="panel-title"><img src="assets/images/chat_agent_logo.png"/> ' + phone_number + '</h4></div><div class="col-md-5 col-xs-5 btn-group" style="float: right"> <button type="button" class="btn btn-danger sms-session-close"> <i class="fa fa-times text-danger pull-right"> </i></button> <button type="button" class="btn btn-primary sms-session-minimize"><i class="fa fa-minus text-primary pull-right"> </i></button></div></div>';
            html += '<div class="panel-body msg_container_base">';
            html += "<div class='sms-disposition-container' style='display:none'><select class='sms-disposition-class form-control input-md' id='sms-disposition' name='sms-disposition'><option value=''>---select---</option></select><button type='button' data-sid='s-"+ session_id +"' class='btn-sm pull-right btn-success save-sms-disposition'>Save</button><button type='button' data-sid='s-"+ session_id +"' class='btn-sm pull-right btn-danger cancel-sms-disposition'>Cancel</button></div>";
            var smsCounter = 0;
            var lastMsg = "";
            data.forEach(function(conversation) {
                if (conversation.direction === "O") {
                    html += '<div class="row msg_container base_sent" title="'+ conversation.agent_id +' replied on '+ moment(conversation.msg_time, "YYYY-MM-DD HH:mm:ss").format('Do-MMMM-YYYY, h:mm:ss a') +'"><div class="col-md-10 col-xs-10"><div class="messages msg_sent"><p>' + conversation.message + '</p><time datetime="">' + moment(conversation.msg_time, "YYYY-MM-DD HH:mm:ss").calendar() + '</time></div></div><div class="col-md-2 col-xs-2 avatar"><img src="assets/images/chat_agent_logo.png" class=" img-responsive "> </div>  </div>';
                    lastMsg = conversation.message;
                } else {
                    html += '<div class="row msg_container base_receive" title="'+ moment(conversation.msg_time, "YYYY-MM-DD HH:mm:ss").format('Do-MMMM-YYYY, h:mm:ss a') +'"><div class="col-md-2 col-xs-2 avatar"><img src="ccd/image/pic.png" class=" img-responsive "></div> <div class="col-md-10 col-xs-10"><div class="messages msg_receive"><p>' + conversation.message + ' </p><time datetime="">' + moment(conversation.msg_time, "YYYY-MM-DD HH:mm:ss").calendar() + '</time></div></div></div>';
                    lastMsg = conversation.message;
                }
                smsCounter++;
            });
            $.ajax({
                type: "POST",
                url: sms_log_text_url,
                data: {"phone_number": phone_number, "counter": smsCounter, "last_msg": lastMsg},
                dataType: "json"
            }).success(function () {
                console.log("sms log updated");
            });
            html += '';

            html += '</div><div class="panel-footer">';
            html += ' <div class="predefined-sms-container"><select class="form-control sms-templates" id="sms-templates"><option value="">---Select---</option></select></div>';
            html += ' <div class="input-group"><textarea id="btn-input" type="text" class="form-control input-sm chat_input" placeholder="Write your message here..."> </textarea><span class="input-group-btn"><button style="height: 65px;" class="btn btn-primary btn-sm btn-sms-send" data-sid="s-' + session_id + '"><i class="fa fa-paper-plane" aria-hidden="true"></i></button> </span>  </div>  </div>  </div>';
            html += '</div>';

            if($('#sms-container #s-'+session_id)){
                $('#sms-container #s-'+session_id).remove();
            }
            $('#sms-container').append(html);

            var chat_box = document.querySelector('.msg_container_base');
            chat_box.scrollTop = chat_box.scrollHeight;
        }

        $.ajax({
            type: "POST",
            url: predefined_sms_template_url,
            dataType: "json"
        }).success(function (templates) {
            if (templates){
                var options = '';
                templates.forEach(function (template) {
                    options += "<option data-id='"+ template.tstamp +"' data-sms-body='"+ template.sms_body +"' value='"+ template.sms_body +"'>"+ template.title +"</option>";
                });

                $('#sms-container #s-'+session_id +" .sms-templates").append(options);
            }
        });
    });
}


function sms_ping(param) {
    $.ajax({
        type: "POST",
        url: sms_ping_url,
        data: param,
        dataType: "json"
    }).success(function (data) {
        console.log("Pinged sms.... "+ param.session_id);
    });
}



function appendSentSMStoChatWindow(session_id, message){

    var current_time = moment().calendar();

    var html = '<div class="row msg_container base_sent"><div class="col-md-10 col-xs-10"><div class="messages msg_sent"><p>'+ message +'</p><time datetime="'+ current_time +'">'+ current_time +'</time></div></div><div class="col-md-2 col-xs-2 avatar"><img src="assets/images/chat_agent_logo.png" class=" img-responsive "> </div>  </div>';
    $("#"+ session_id +" .msg_container_base").append(html);

    var chat_box = document.querySelector('.msg_container_base');
    chat_box.scrollTop = chat_box.scrollHeight;
}

function appendReceivedSMStoChatWindow(session_id, message){
    var current_time = moment().calendar();

    var html = '<div class="row msg_container base_receive"><div class="col-md-2 col-xs-2 avatar">';
    html += '<img src="ccd/image/pic.png" class=" img-responsive "></div> <div class="col-md-10 col-xs-10"><div class="messages msg_receive"><p>'+ message +' </p><time datetime="'+ current_time +'">'+ current_time +'</time></div></div></div>';
    $("#s-"+ session_id +" .msg_container_base").append(html);
    var chat_box = document.querySelector('.msg_container_base');
    chat_box.scrollTop = chat_box.scrollHeight;

}

function getSMSDispositionBySkillId(session_id, skill_id){
    $.ajax({
        type: "POST",
        url: skill_wise_disposition_url + skill_id,
        dataType: "json"
    }).done(function (data) {
        console.log("disposition length:" + Object.keys(data).length);
        if (Object.keys(data).length > 0){
            $("#"+session_id +" #sms-disposition").empty();
            $("#"+session_id +" #sms-disposition").append("<option value=''>---Select---</option>");
            $.each(data, function (index, disposition) {
                $("#"+session_id +" #sms-disposition").append("<option value='"+disposition.disposition_id+"'>"+ disposition.title +"</option>");
            });
        }
    });
}





$(function (e){
    /*=====================================SMs window start===========================================*/
    $(document).on('click', '.panel-heading .sms-session-minimize', function (e) {
        var $this = $(this);
        if (!$this.parents('.panel').find('.sms-session-minimize').hasClass('panel-collapsed')) {

            $this.parents('.panel').find('.panel-body, .panel-footer').slideUp();
            $this.parents('.panel').find('.sms-session-minimize ').addClass('panel-collapsed');
            $this.parents('.panel').find('.sms-session-minimize .fa').removeClass('fa-minus').addClass('fa-plus');
        } else {
            $this.parents('.panel').find('.panel-body, .panel-footer').slideDown();
            $this.parents('.panel').find('.sms-session-minimize ').removeClass('panel-collapsed');
            $this.parents('.panel').find('.sms-session-minimize .fa').removeClass('fa-plus').addClass('fa-minus');
        }
    });
    $(document).on('focus', '.panel-footer input.chat_input', function (e) {
        var $this = $(this);
        if ($('#minim_chat_window').hasClass('panel-collapsed')) {
            $this.parents('.panel').find('.panel-body').slideDown();
            $('#minim_chat_window').removeClass('panel-collapsed');
            $('#minim_chat_window').removeClass('fa-plus').addClass('fa-minus');
        }
    });

    $(document).on("click", '.sms-session-close', function(e){
        var session_id = $(this).closest('.chat-window').attr('id');
        getSMSDispositionBySkillId(session_id, "AA");

        $("#"+ session_id + " .panel-footer").hide();
        $("#"+ session_id + " .msg_container_base .msg_container").hide();
        $("#"+ session_id + " .sms-disposition-container").show();
    });

    $(document).on('click', '.cancel-sms-disposition', function (e) {
        var session_id = $(this).data("sid");
        $("#"+ session_id + " .msg_container_base .msg_container, .panel-footer").show();
        $("#"+ session_id + " .sms-disposition-container").hide();
    });

    $(document).on('click', '.save-sms-disposition', function (e) {
        $that = $(this);

        var session_id = $(this).data("sid");
        var cid = $("#"+ session_id).data("cid");
        var disposition = $("#"+ session_id + " option:selected").val();
        var interval_id = $("#"+ session_id).data("iid");

        if(!disposition){
            alert("SMS disposition is required.");
            return false;
        }
        var data = {
            "session_id": session_id.substring(2),
            "callid": cid.substring(2),
            "disposition": disposition
        };

        $.ajax({
            type: "POST",
            url: sms_disposition_store_url,
            data: data,
            dataType: "json"
        }).success(function (data) {
            console.log(data);
            if (data.status) {
                clearInterval(interval_id);
                $that.parent().parent().parent().parent().parent().remove();
            }
        });
    });


    $(document).on('click', '.btn-sms-send', function (e) {

        var id = $(this).data("sid");

        var sms_selector = "#"+ id +" .chat_input";
        var sms_text = $(sms_selector).val();
        var cid = $("#"+ id).data("cid");
        var phone_number = $("#"+ id).data("phone");
        var did = $("#"+ id).data("did");

        if (!sms_text){
            alert('SMS text is required.');
            return;
        }

        var sms_meta_data = {
            "session_id": id.substring(2), "callid": cid.substring(2),
            "msg": sms_text, "phone_number": phone_number, "did": did
        };

        $.ajax({
            type: "POST",
            url: sms_send_url,
            data: sms_meta_data,
            dataType: "json"
        }).success(function (data) {
            console.log(data);
            if (data.status) {
                appendSentSMStoChatWindow(id, sms_text);
                $(sms_selector).val('');
            }else{
                alert(data.msg);
            }
        });
    });


    $(document).on("change", ".sms-templates", function (e) {
        var session_id = $(this).closest('.chat-window').attr('id');
        $("#"+session_id + " .chat_input").val($(this).find(':selected').val());
    });

    $(document).on('mouseenter','#sms-disposition',function (e) {
        $(this).select2();
    });

    /*=====================================SMs window end===========================================*/

});