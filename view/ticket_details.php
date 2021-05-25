<?php
/**
 * Created by PhpStorm.
 * User: arif
 * Date: 12/5/2018
 * Time: 2:01 PM
 */
//dd($disposition);
?>

<script src="js/jquery.MultiFile.pack.js"></script>
<link rel="stylesheet" href="assets/css/ticket_details.css">
<!-- Include the plugin's CSS and JS: -->
<script type="text/javascript" src="js/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="js/bootstrap-multiselect/css/bootstrap-multiselect.css" type="text/css"/>

<link rel="stylesheet" type="text/css" media="screen" href="css/form.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/report.css">
<?php include_once ('lib/summer_note_lib.php')?>
<?php include_once ('template_modal.php');?>

<table width="90%" cellspacing="0" cellpadding="5" border="0" class="form_table" style="margin-bottom: 5px;">
    <tbody>
    <tr class="form_row_head">
        <td>Tiket Details :: <?php echo $eTickets->ticket_id; ?></td>
    </tr>
    <tr class="form_row">
        <td>
            <table width="100%">
                <tbody>
                <tr>
                    <td width="25%"><b>Mobile Number:</b> <?php echo $eTickets->created_for; ?></td>
                    <td width="50%"><b>Created:</b> <?php if ($eTickets->create_time!="" && $eTickets->created_by!=""){echo date("Y-m-d H:i:s", $eTickets->create_time). " by ".$eTickets->created_by;} ?></td>
                    <td width="50%"><b>Customer:</b> <?php echo $eTickets->name ?></td>
                </tr>
                <tr>
                    <td><b>Skill:</b> <?php echo $eTickets->skill_name; ?></td>
                    <td>
                        <b>Status:</b> <?php if ($eTickets->last_update_time!=""){echo '<font style="background-color:#eee;padding:1px;"><i>' . $eTicket_model->getTicketStatusLabel($eTickets->status)."</i></font> on ".date("Y-m-d H:i:s", $eTickets->last_update_time). " by ".$eTickets->status_updated_by;} ?>
                        <?php
                            $changable_status = $mainobj->getChangableTicketStatus($eTickets->status);
                            if ($eTickets->status == 'E' && (UserAuth::hasRole('admin') || UserAuth::hasRole('supervisor'))) {
                                $status_updatable = true;
                            } else {
                                $status_updatable = false;
                            }
                            if ($status_updatable || ($update_privilege && is_array($changable_status) && count($changable_status) > 0)):
                        ?>
                            <a class="set-options" href="<?php echo $this->url('task='.$request->getControllerName()."&act=status&tid=".$eTickets->ticket_id);?>">
                                Change status
                            </a>
                        <?php endif;?>
                    </td>
                    <td width="50%"><b>Source:</b> <?php echo array_key_exists($eTickets->source,$source_name)?$source_name[$eTickets->source]:"" ?></td>
                </tr>
                <tr>
                    <td>
                        <b>Assigned To:</b>
                        <?php if (isset($assigned_to) && !empty($assigned_to)) echo $assigned_to->agent_id . ' - ' . $assigned_to->nick; ?>
                        <?php if ($update_privilege && is_array($changable_status) && count($changable_status) > 0):?>
                            <a class="set-options" href="<?php echo $this->url('task='.$request->getControllerName()."&act=assign&tid=".$eTickets->ticket_id);?>">
                                <?php if (empty($eTickets->assigned_to)) echo 'Assign'; else echo 'Change';?>
                            </a>
                        <?php endif;?>
                    </td>
                    <td>
                        <b>Disposition:</b>
                        <?php if (!empty($disposition)) {

                            $path = $eTicket_model->getDispositionPath($disposition->disposition_id);
                            if (!empty($path)) echo $path . ' -> ';
                            //echo ;
                            echo $disposition->title;

                        }

                        ?>
                        <?php if ($update_privilege && is_array($changable_status) && count($changable_status) > 0):?>
                            <a class="set-options" href="<?php echo $this->url('task='.$request->getControllerName()."&act=disposition&tid=".$eTickets->ticket_id);?>">
                                <?php if (empty($eTickets->disposition_id)) echo 'Add'; else echo 'Change';?>
                            </a>
                        <?php endif;?>
                    </td>

                    <td>
                        <b>Ticket Category:</b>
                        <?php /*if (isset($assigned_to) && !empty($assigned_to)) echo $assigned_to->agent_id . ' - ' . $assigned_to->nick; */?>
                        <?php if ($update_privilege && is_array($changable_status) && count($changable_status) > 0):?>
                            <a class="set-options" href="<?php echo $this->url('task='.$request->getControllerName()."&act=ticket-category&tid=".$eTickets->ticket_id);?>">
                                <?php if (empty($eTickets->title)) echo 'Add'; else echo $eTickets->title;?>
                            </a>
                        <?php endif;?>
                    </td>

                </tr>
                </tbody>
            </table>
    </tr>
    </tbody>
</table>

<table class="form_table">
    <?php if ($update_privilege && is_array($changable_status) && count($changable_status) > 0):?>
        <tr class="form_row">
            <td valign="top">
                <form method="post" enctype="multipart/form-data" name="ticket_message" id="ticket_message">
                    <label for="message">Message <font class="required">*</font></label><br>
                    <input type="hidden" name="st" id="st" value="" maxlength="1" />
                    <textarea style="width:90%; height: 145px;" id="message" name="message"><?php if (!empty($signature) && $signature->status == 'Y') echo '<br /><br /><br />' . $signature->signature_text; ?></textarea>
<!--                    --><?php //$tsk = "task=".$request->getControllerName();$act = "&act=templates"; ?>
<!--                    <button style="margin-bottom: 1%" class="btn btn-default" type="button" id="temp" url="--><?php //echo $this->url($tsk.$act); ?><!--"> Template </button>-->
                    <input type="file" id="att_file" name="att_file[]" /><br />
                    <input class="form_submit_button" type="button" onclick="checkMsg()" value="Reply" id="submitTicket" name="submitTicket" style="font-size:14px;padding:8px 0.84615em;" />

                    <input type="hidden" name="dd" id="dd" value="<?php echo $eTickets->disposition_id?>" maxlength="4" />
                </form>
            </td>
        </tr>
    <?php else:?>
        <tr>
            <td>&nbsp;</td>
        </tr>
    <?php endif;?>
    <?php
    if (is_array($eTicketEmail)){
        $i = 0;
        foreach ($eTicketEmail as $eEmail){
            $display = $i > 0 ? 'none' : 'block';
            $full_time_details = strlen($eEmail->tstamp)==10 ? date("M d ' Y, h:i:s a ", $eEmail->tstamp) : "processing"
            ?>
            <tr class="form_row_head">
                <td style="cursor:pointer;" class="email_title" id="m<?php echo $eEmail->ticket_sl;?>">&nbsp;<div style="display:inline-block;width:300px;"> <i class="fa fa-user" aria-hidden="true"></i> <?php echo $eEmail->nick=="" ? $eEmail->from_email : $eEmail->nick;?> </div>&nbsp; <div class="pull-right"><span class="tic-sts" style="padding: 5px 5px 5px;background: #60d0e7;border-radius: 3px;"><?php echo  !empty($status_options[$eEmail->status]) ? $status_options[$eEmail->status] : '' ;?></span> &nbsp;<?php echo strlen($eEmail->tstamp)==10 ? $full_time_details: "";?> <span><i class="fa <?php echo $display=='none'? 'fa-minus-circle':'fa-plus-circle'?> plus-minus" aria-hidden="true"></i></span> </div></td>
            </tr>
            <tr>
                <td bgcolor="#FFFFFF" style="text-align:left;padding:5px;display:<?php echo $display;?>;" id="bm<?php echo $eEmail->ticket_sl;?>"><span>
                <?php if ($eEmail->has_attachment == 'Y') {
                    $attachemnts = $mainobj->getAttachments($eEmail->ticket_id, $eEmail->ticket_sl);
                    if (is_array($attachemnts)) {
                        echo '<div style="background-color:#E5F1F4;padding:3px 5px;margin-bottom:4px;"><font color="green">Attachments:</font> ';
                        foreach ($attachemnts as $att) echo '<a onclick="removeMainLoader()" href="' .
                            $this->url('task='.$request->getControllerName()."&act=attachment&tid=".$eEmail->ticket_id . "&sl=" . $att->ticket_sl) . "&p=" . $att->part_position . '">' .$att->file_name . '</a>' . ' &nbsp; ';
                        echo '</div>';
                    }
                }?>
               <?php echo str_replace('\r\n','',nl2br(base64_decode($eEmail->ticket_body)));?><br></span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <?php
            $i++;
        }
    }
    ?>
</table>

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
                        <tr class="form_row_alt">
                            <td class="form_column_caption" style="text-align:center;"><b>Status:</b>
                                <select name="pstatus" id="pstatus">
                                    <option value="">Select</option>
                                    <option value="<?php echo $eTickets->status;?>" selected="selected"><?php echo $mainobj->getTicketStatusLabel($eTickets->status);?></option>
                                    <?php if (is_array($changable_status)) {
                                        foreach ($changable_status as $st) {
                                            echo '<option value="' . $st . '"';
                                            if ($eTickets->status == $st) echo ' selected';
                                            echo '>' . $eTicket_model->getTicketStatusLabel($st) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr class="form_row">
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


<script type="text/javascript">
    var isSubmit = false;
    var ticketStatus = '';
    var text_area_id = "'textarea#message'";
    var did = '';
    var sid = '';
    var isValidate = false;


    function removeMainLoader() {
        $("#MainLoader").remove();
    }

    function setStatus() {
        ticketStatus = $('#pstatus').val();
        if (typeof ticketStatus!=='undefined' && ticketStatus.length > 0){
            $.colorbox.close();
            isValidate = true;
        }
    }

    function submit_form() {
        if (isSubmit && isValidate) {
            $("#st").val(ticketStatus);
            if($("#message").val() != ""){
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
        var pattern = "<?php echo $msg_pattern; ?>";
        if($("#message").val() != ""){
            var msg = $("#message").val().toLowerCase();
            if (pattern != "" && pattern != null && msg.indexOf(pattern.toLowerCase()) >= 0){
                alert("You must replace the fixed text: "+pattern);
                return false;
            }else{
                isSubmit = true;
                ticketStatus = '';
                $.colorbox({inline:true, width:"450", href:"#inline_content", onClosed:function(){ submit_form(); }});
                return false;
            }
        }else{
            alert("Message can not be empty!");
            return false;
        }
    }

    $(document).ready(function() {
        $('#att_file').MultiFile({
            STRING: {
                remove: '<i class="fa fa-minus-circle"></i>'
            }
        });

        $(".email_title").click(function() {
            $("#b"+$(this).attr('id')).toggle();

            var tdid = $("#"+$(this).attr('id')).find('span i');
            if (tdid.hasClass('fa fa-minus-circle')){
                tdid.removeClass('fa fa-minus-circle');
                tdid.addClass('fa fa-plus-circle');
            }else {
                tdid.removeClass('fa fa-plus-circle');
                tdid.addClass('fa fa-minus-circle');
            }
        });

        $("#status_change").click(function() {
            $("#spn_new_status").show();
        });

        $("#btn_status_change").click(function() {
            var txt = $("#new_status option:selected").text();
            var val = $("#new_status").val();

            if (confirm('Are you sure to change the status to "' + txt + '"?')) {
                $.post('<?php echo $this->url('task='.$request->getControllerName()."&act=statusupdate");?>', { 'tid':'<?php echo $eTickets->ticket_id;?>', 'status':val },
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
            iframe:true, width:"450", height:"400"
        });

        $('.multiselect').multiselect({buttonClass:'btn'});



        Initialize_Summer_note('textarea#message', ['view','insert'], "<?php //echo $this->url('task=email&act=uploadEmailImage&type=new'); ?>", "<?php //echo $this->url('task=email&act=deleteUploadedImage'); ?>", "<?php //echo $attachment_save_path; ?>");
        did = $("#dd").val();
        //click_template(text_area_id, did, sid);
    });
</script>


