<style type="text/css">
    .tipr_content
    {
        font: 13px/1.7 'Helvetica Neue', Helvetica, Arial, sans-serif;
        color: #333;
        background-color: #fff;
        color: #333;
        padding: 6px 17px;
        width: 540px;
    }
    .tipr_container_bottom
    {
        display: none;
        position: absolute;
        margin-top: 13px;
        z-index: 1000;
    }
    .tipr_container_top
    {
        display: none;
        position: absolute;
        margin-top: -75px;
        z-index: 1000;
    }
    .tipr_point_top, .tipr_point_bottom
    {
        position: relative;
        background: #fff;
        border: 1px solid #dcdcdc;
    }
    .tipr_point_top:after, .tipr_point_top:before
    {
        position: absolute;
        pointer-events: none;
        border: solid transparent;
        top: 100%;
        content: "";
        height: 0;
        width: 0;
    }
    .tipr_point_top:after
    {
        border-top-color: #fff;
        border-width: 8px;
        left: 20%;
        margin-left: -8px;
    }
    .tipr_point_top:before
    {
        border-top-color: #dcdcdc;
        border-width: 9px;
        left: 20%;
        margin-left: -9px;
    }
    .tipr_point_bottom:after, .tipr_point_bottom:before
    {
        position: absolute;
        pointer-events: none;
        border: solid transparent;
        bottom: 100%;
        content: "";
        height: 0;
        width: 0;
    }
    .tipr_point_bottom:after
    {
        border-bottom-color: #fff;
        border-width: 8px;
        left: 20%;
        margin-left: -8px;
    }
    .tipr_point_bottom:before
    {
        border-bottom-color: #dcdcdc;
        border-width: 9px;
        left: 20%;
        margin-left: -9px;
    }
    .table {
        margin-bottom: -26px !important;
    }
</style>
<div class="modal" tabindex="-1" role="dialog" id="myModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Email</h5>
            </div>
            <div class="modal-body">
                <table class="report_table" width="60%" border="0" align="center" cellpadding="1" cellspacing="1">

                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    var skill_id = '<?php echo !empty($eTickets->skill_id) ? json_encode($eTickets->skill_id) : ''?>';

    function addText(tstamp, editor_id)
    {
        //parent._tstamp = tstamp;
        tstamp = tstamp.toString();
        if (tstamp.length == 10) {
            setText(tstamp, editor_id);
        }
        //parent.$.colorbox.close();
        return false;
    }

    /*function getSignature(skill_id) {
     $.ajax({
     type       :"POST",
     dataType   :"JSON",
     url        :"index.php?task=emailsignature&act=get-email-signature",
     data       :{sid:skill_id},
     success    :function (res) {
     }
     });
     }*/

    function setText(tstamp, editor_id) {
        $.ajax({
            type: "POST",
            url: "index.php?task=email&act=templatetext",
            data: { tid: tstamp, skillid:skill_id }
        })
            .done(function( msg ) {
                //$(".note-editable").text(msg);
                $previous_data_in_editor = $(editor_id).val();
                //$(editor_id).summernote('editor.insertText', msg);
                $(editor_id).summernote('code', msg+$previous_data_in_editor);
                $('#myModal').modal('toggle');
            });
    }
    
    function click_template(text_area_id, did, sid) {
        $("#temp").on('click', function (e) {
            did = typeof did!='undefined'&&did.length!=''? did : $("#dd").val();
            $("#myModal").modal();
            var url = $(this).attr('url');
            $.ajax({
                dataType: "JSON",
                data: {did:did, sid:sid},
                url: url,
                type: "POST",
                success: function (res) {
                    //console.log(res);
                    $(".report_table tr").remove();
                    if (typeof res !== 'undefined' && res.emails !=null && res.emails.length != ''){
                        var i = 0;
                        $.each(res.emails, function (key,value) {
                            i++;
                            var _class = i%2 == 1 ? 'report_row' : 'report_row_alt';
                            $(".report_table").append('<tr class="_class"><td class="cntr">&nbsp;'+i+'</td>' +
                                '<td align="left" class="tip" data-tip="'+value.mail_body+'">' +
                                '<a href="#" onclick=" addText('+value.tstamp+','+text_area_id+')">'+value.title+'</a></td>' +
                                '</tr>');
                        })
                    }
                }
            });
            did = '';
        });
    }
    
    (function($){$.fn.tipr=function(options){var set=$.extend({"speed":200,"mode":"bottom"},options);return this.each(function(){var tipr_cont=".tipr_container_"+set.mode;$(this).hover(function(){var out='<div class="tipr_container_'+set.mode+'"><div class="tipr_point_'+set.mode+'"><div class="tipr_content">'+$(this).attr("data-tip")+"</div></div></div>";$(this).append(out);var w_t=$(tipr_cont).outerWidth();var w_e=$(this).width();var m_l=w_e/2-w_t/2-50;$(tipr_cont).css("margin-left",m_l+"px");$(this).removeAttr("title");
        $(tipr_cont).fadeIn(set.speed)},function(){$(tipr_cont).remove()})})}})(jQuery);

    $(document).ready(function() {
        $('.tip').tipr();
    });
</script>