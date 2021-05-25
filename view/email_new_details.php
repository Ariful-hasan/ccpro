<?php
$num_select_box = 6;
$address_book_emails = [];
if (!empty($ccmails)){
    foreach ($ccmails as $item) {
        $address_book_emails[] = $item->name . ' (' . $item->email . ')';
    }
}
$email_signature = !empty($signature) && $signature->status == 'Y' ? nl2br(base64_decode($signature->signature_text)) : "";
$email_signature = !empty($email_signature) ? '<br/>With regards,<br/><b>' .$agent_name.'</b><br/><br/>'.$email_signature.'<br/>' : '<br/>With regards,<br/><b>'.$agent_name.'</b><br/>';
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

$email_sl = !empty($eTickets->mail_sl) ? $eTickets->mail_sl : '';
$to_email_arr = explode(",", $eTickets->mail_to);
$cc_email_arr = explode(",", $eTickets->mail_cc);
$reply_email = $eTickets->from_email;

$reply_all_email = explode(",", $eTickets->mail_to);
$idx = array_search($eTickets->fetch_box_email, $reply_all_email);
if ($idx !== FALSE ){
    unset($reply_all_email[$idx]);
}
$reply_all_email[] = $eTickets->from_email;
$reply_all_email = !empty($reply_all_email) ? implode(",", $reply_all_email) : "";

$reply_all_cc_email = !empty($eTickets->mail_cc) ? explode(",", $eTickets->mail_cc) : "";
$idx = !empty($reply_all_cc_email) ? array_search($eTickets->fetch_box_email, $reply_all_cc_email) : FALSE;
if ($idx !== FALSE ){
    unset($reply_all_cc_email[$idx]);
}
$reply_all_cc_email = !empty($reply_all_cc_email) ? implode(",", $reply_all_cc_email) : "";
//GPrint($eTickets);


/////   Show CC/BCC/TO    //////
$_temp = explode(",", $eTickets->mail_to);
$_short_cc_bcc_to = [];
foreach ($_temp as $item) {
    $_short_cc_bcc_to[] = substr($item, 0, strpos($item, "@"));
}
$_temp = explode(",", $eTickets->mail_cc);
foreach ($_temp as $item) {
    $_short_cc_bcc_to[] = substr($item, 0, strpos($item, "@"));
}
?>

<script src="assets/plugins/multiple-file/jquery.MultiFile.min.js"></script>
<!--<link href="assets/plugins/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet" type="text/css"/>-->
<!--<script src="assets/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>-->
<link rel="stylesheet" href="assets/css/email_details.css">

<script src="js/datetimepicker/jquery.datetimepicker.js"></script>
<link rel="stylesheet" href="js/datetimepicker/jquery.datetimepicker.css" type="text/css"/>

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

<!--<link rel="stylesheet" href="ccd/select2/select2.min.css">-->
<!--<script src="ccd/select2/select2.min.js"></script>-->

<link rel="stylesheet" type="text/css" media="screen" href="css/form.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/report.css">

<?php include_once ('lib/summer_note_lib.php')?>
<?php include_once ('template_modal.php');?>
<!--<link rel="stylesheet" type="text/css" media="screen" href="css/form.css">-->
<!--<link rel="stylesheet" type="text/css" media="screen" href="css/report.css">-->


<div class="">
    <?php if ((!empty($queue_msg) || !empty($agent_msg)) && empty($callid) && empty($current_user)) { ?>
        <div class="alert alert-danger"><?php echo  !empty($queue_msg) ? $queue_msg : $agent_msg; ?></div>
    <?php }?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="row ">
                <div class="col-md-12">
                <?php $_working_user = !empty($isAllowedToPull) && empty($is_setinterval_active) ? true : false; ?>
                <?php if (!$_working_user){ ?>
                     <div class="col-md-1 pull-left">
                        <span class=""><i class="fa fa-user <?php echo empty($is_setinterval_active)?'other':'own'?> "></i></span> <?php echo $current_user?>
                        <?php  if (empty($is_setinterval_active)){ ?><div class="help"><div class="typing-loader text"></div></div><?php } ?>
                     </div>
                <?php } ?>
                <?php if( !empty($isAllowedToPull) && empty($is_setinterval_active)){ ?>
                    <div class="">
                        <div class="col-md-1 col-md-offset-8"><button type="button" class="btn btn-sm btn-cus-wrn pull-right" id="skip" onclick="skipEmail('<?php echo $is_setinterval_active?>','skip')"><i class="fa fa-arrow-left" aria-hidden="true"></i> skip </button></div>
                        <div class="col-md-1"><button  class="btn btn-sm btn-cus-succ pull-right" id="work_on" onclick="workOnThisEmail()"><i class="fa fa-pencil-square" aria-hidden="true"></i>  Pull</button></div>
                        <div class="col-md-2"><a  href="<?php echo site_url().$this->url('task=email&act=transfer&tid='.$eTickets->ticket_id.'&callid='.$callid)?>" class="btn btn-sm btn-cus-pri set-options" id="work_on"><i class="fa fa-exchange" aria-hidden="true"></i> Transfer</a></div>
                    </div>
                <?php } else {?>
                <div class="col-md-1 pull-right">
                    <?php if (!empty($is_setinterval_active)){ ?><button type="button" class="btn btn-sm btn-danger" id="skip" onclick="skipEmail('<?php echo $is_setinterval_active?>','close')"><i class="fa fa-times" aria-hidden="true"></i> Close</button><?php } ?>
                </div>
                <?php } ?>
                </div>
            </div>
        </div>

        <div class="panel-body pb-0">
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-4 form-group">
                        <label for="user" class="col-md-2 control-label pdl-0">From</label>
                        <div class="col-md-10">
                            <p for="form-control pdl-0"><?php echo ": ".$eTickets->from_email; ?></p>
                        </div>
                    </div>

                    <div class="col-md-4 form-group">
                        <label for="user" class="col-md-2 control-label pdl-0">Created</label>
                        <div class="col-md-10">
                            <?php $created_by = !empty($eTickets->agent_id) ? $eTickets->agent_id : $eTickets->from_email?>
                            <p for="form-control pdl-0"><?php echo $eTickets->tstamp!="" && $eTickets->from_email!="" ? ": ".date("Y-m-d h:i:s a ", $eTickets->tstamp) : "" ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="user" class="col-md-2 control-label pdl-0">Inbox</label>
                        <div class="col-md-10">
                            <p for="form-control pdl-0"><?php echo $eTickets->fetch_box_email!="" ? ": ".$eTickets->fetch_box_name : "" ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="col-md-4 form-group">
                        <label for="user" class="col-md-2 control-label pdl-0">Skill</label>
                        <div class="col-md-10">
                            <p for="form-control pdl-0">
                                <?php echo $eTickets->skill_name!="" ? ": ".$eTickets->skill_name : "" ?>
                                <?php if ($update_privilege && ($acd_status != 'N' || $dist_status=='R') && !empty($is_setinterval_active)):?>
                                    <a class="set-options" href="<?php echo site_url().$this->url('task='.$request->getControllerName()."&act=skill&tid=".$eTickets->ticket_id."&msl=".$eTickets->mail_sl);?>">
                                        <?php echo empty($eTickets->skill_name) ? 'Add' : 'Transfered';?>
                                    </a>
                                <?php endif;?>
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4 form-group">
                        <label for="user" class="col-md-2 control-label pdl-0">Disposition</label>
                        <div class="col-md-10">
                            <p for="form-control pdl-0">
                                <?php echo ": $disposition_name"; ?>
                                <?php if (($eTickets->email_status != "S" && $eTickets->email_status!="E") && $update_privilege && ($acd_status != 'N' || $dist_status=='R') && !empty($is_setinterval_active)){ ?>
                                    <a class="set-options" href="<?php echo site_url().$this->url('task='.$request->getControllerName()."&act=disposition&tid=".$eTickets->ticket_id."&msl=".$eTickets->mail_sl);?>">
                                        <?php echo (empty($eTickets->disposition_id)) ? 'Add' : 'Change';?>
                                    </a>
                                <?php } ?>
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4 form-group">
                        <label for="user" class="col-md-2 control-label pdl-0">Status</label>
                        <div class="col-md-10">
                            <p for="form-control pdl-0">
                                <?php echo $eTickets->email_status=="" ? ": New": ": ".$eTicket_model->getTicketStatusLabel($eTickets->email_status) ?>
                                <?php if (($eTickets->email_status != "S" && $eTickets->email_status!="E") && $update_privilege && (($acd_status != 'N' || $dist_status =='R') && !empty($is_setinterval_active))) { ?>
                                    <a class="set-options" href="<?php echo site_url().$this->url('task='.$request->getControllerName()."&act=status&tid=".$eTickets->ticket_id.'&callid='.$callid."&msl=".$eTickets->mail_sl);?>">
                                        Change status
                                    </a>
                                <?php } ?>
                            </p>
                        </div>
                    </div>

                </div>
                <div class="col-md-12">
                        <div class="col-md-4 form-group">
                            <label for="user" class="col-md-2 control-label pdl-0">Agent</label>
                            <div class="col-md-10">
                                <p for="form-control pdl-0">
                                    <?php echo !empty($agent_names[$eTickets->assigned_to]) ? ": ".$agent_names[$eTickets->assigned_to] : ": ".$eTickets->assigned_to ?>
                                    <?php if ( ($acd_status=='' || $acd_status == 'N') && (!empty($is_setinterval_active))) {?>
                                        <?php if ($update_privilege){ ?>
                                            <a class="set-options" href="<?php echo site_url().$this->url('task='.$request->getControllerName()."&act=assign&tid=".$eTickets->ticket_id."&msl=".$eTickets->mail_sl);?>">
                                                <?php echo 'Transfered';?>
                                            </a>
                                        <?php } ?>
                                    <?php } ?>
                                </p>
                            </div>
                        </div>
                    <div class="col-md-4 form-group">
                        <label for="user" class="col-md-3 control-label pdl-0">Last Pull</label>
                        <div class="col-md-9">
                            <p for="form-control pdl-0"><?php echo !empty($eTickets->last_pull_time) ? ": ".date("Y-m-d h:i:s a ", $eTickets->last_pull_time) : ": Not pulled yet." ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="user" class="col-md-3 control-label pdl-0">First Seen</label>
                        <div class="col-md-9">
                            <p for="form-control pdl-0"><?php echo !empty($agent_names[$eTickets->first_seen_by]) ? ": ".$agent_names[$eTickets->first_seen_by] : ": ".$eTickets->first_seen_by ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="user" class="col-md-2 control-label pdl-0">Type</label>
                        <div class="col-md-10">
                            <p for="form-control pdl-0"><?php echo $eTickets->status=="N" ? ": Inbound" : ": Outbound" ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="user" class="col-md-3 control-label pdl-0 pdr-0">Customer ID</label>
                        <div class="col-md-9">
                            <p for="form-control pdl-0 "><?php echo !empty($eTickets->customer_id) ? ": ".$eTickets->customer_id : ":" ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--  Edit Panel START -->
        <div class="panel panel-default hide" id="editing_part">
            <div class="panel-body">
                <form method="post" enctype="multipart/form-data" name="ticket_message" id="ticket_message">

                    <div class="row">
                        <div class="col-md-12 pr-25">
                            <div class="col-md-2 col-md-offset-10">
                                    <div class="col-md-3 col-md-offset-6" id="cc_btn"><b class="cursor-pointer blue">Cc</b></div>
                                    <div class="col-md-3 cursor-pointer pull-right" id="bcc_btn"><b class="cursor-pointer blue">Bcc</b></div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="to_div">
                        <div class="col-md-12">
                            <label for="To" class="col-md-1 control-label">To</label>
                            <div class="col-md-11">
                                <input class="form-control" id="email_to" name="to" type="text" value=""/>
                            </div>
                        </div>
                    </div>
                    <div class="row hide" id="fwd_div">
                        <div class="col-md-12">
                            <label for="Bcc" class="col-md-1 control-label">Forward</label>
                            <div class="col-md-11 form-group">
                                <input class="form-control" id="email_forward" name="forward" type="text" value=""/>
                            </div>
                        </div>
                    </div>
                    <div class="row hide" id="cc_div">
                        <div class="col-md-12">
                            <label for="Cc" class="col-md-1 control-label">Cc</label>
                            <div class="col-md-11">
                                <input class="form-control" id="email_cc" name="cc" type="text" value=""/><i class="fa fa-trash-o trash cursor-pointer" id="cc_div_trash" aria-hidden="true"></i>
                            </div>
                        </div>

                    </div>
                    <div class="row hide" id="bcc_div">
                        <div class="col-md-12">
                            <label for="Bcc" class="col-md-1 control-label">Bcc</label>
                            <div class="col-md-11 form-group">
                                <input class="form-control" id="email_bcc" name="bcc" type="text" value=""/><i class="fa fa-trash-o trash cursor-pointer" id="bcc_div_trash" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label for="Template" class="col-md-1 control-label">Template</label>
                            <div class="col-md-11 form-group">
                                <select name="" id="email_temp" class="form-control email_temp">
                                    <option value="">Select</option>
                                    <?php if (!empty($email_template)){ ?>
                                        <?php foreach ($email_template as $key){ ?>
                                            <option value="<?php echo $key->tstamp?>"><?php echo $key->title?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 mt-20">
                        <div class=" summernote-area">
                            <textarea style="width:90%; height: 145px!important;" id="message" name="message"></textarea>
                        </div>
                    </div>

                    <!--<input type="hidden" name="to_tags_email_arr" id="to_tags_email_arr" value=""/>
                    <input type="hidden" name="cc_tags_email_arr" id="cc_tags_email_arr" value=""/>
                    <input type="hidden" name="bcc_tags_email_arr" id="bcc_tags_email_arr" value=""/>
                    <input type="hidden" name="fwd_tags_email_arr" id="fwd_tags_email_arr" value=""/>-->

                    <div class="row">
                        <div class="col-md-12">
                            <?php if ($acd_status != 'N' && !empty($is_setinterval_active)) { ?>
                                <div class="col-md-1">
                                    <input class="form_submit_button btn btn-md btn-cus-succ pull-left" type="button" onclick="checkMsg()" value="Send" id="submitTicket" name="submitTicket" />
                                </div>
                                <div class="col-md-11">
                                    <input type="file" id="att_file" name="att_file[]" multiple="multiple" class="col-md-12 pull-left maxsize-10240" style="width: 100%"/><br />
                                    <div id="prev_attach" class="col-md-12"></div>
                                    <div id="T7-list" class="col-md-12"></div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>


                    <input type="hidden" name="st" id="st" value="" maxlength="1" />
                    <input type="hidden" name="dd" id="dd" value="<?php echo !empty($eTickets->email_did) ? $eTickets->email_did : '' ?>" maxlength="4" />
                    <input type="hidden" name="reschedule_datetime" id="reschedule_datetime" value="" />
                    <input type="hidden" name="forwarded_attachment_hidden" id="forwarded_attachment_hidden" value=""/>
                </form>
            </div>
        </div>
</div>
<!--  Edit Panel END -->


    <!--  Readable Message Body  -->
    <div class="panel panel-default">
        <div class="panel-heading"></div>
        <div class="panel-body">
            <div class="col-md-12">
                <div class="col-md-10">
                    <div class="row">
                        <?php
                        $_from_addr = !empty($eTickets->from_name)?$eTickets->from_name:"";
                        echo  $_from_addr .= !empty($_from_addr) ? " < ".$eTickets->from_email." >" : $eTickets->from_email;
                        ?>
                    </div>
                    <div class="row"><?php echo !empty($eTickets->tstamp) ? date("D d/m/Y H:i a", $eTickets->tstamp) : ""?></div>
                    <div class="row" id="short_cc_bcc_to">
                        <?php echo !empty($_short_cc_bcc_to) ? implode(",",$_short_cc_bcc_to) : ""?>
                        <i class="fa fa-angle-double-down" id="caret_down" aria-hidden="true"></i>
                    </div>
                    <div class="row hide" id="expand_cc_bcc_to">
                        <div class="col-mf-12">
                            <?php
                            $_temp = explode(",", $eTickets->mail_to);
                            echo !empty($_temp) ? "To: ".implode(",", $_temp) : "";
                            ?>
                            <?php echo empty($eTickets->mail_cc) ? '<i class="fa fa-angle-double-up" id="caret_up" aria-hidden="true"></i>' : "";?>
                        </div>
                        <?php if (!empty($eTickets->mail_cc)) {?>
                            <div class="col-mf-12">
                                <?php
                                $_temp = explode(",", $eTickets->mail_cc);
                                echo !empty($_temp) ? "Cc: ".implode(",", $_temp) : "";
                                ?>
                                <?php echo empty($eTickets->mail_bcc) ? '<i class="fa fa-angle-double-up" id="caret_up" aria-hidden="true"></i>' : "";?>
                            </div>
                        <?php } ?>
                        <?php if (!empty($eTickets->mail_bcc)) {?>
                            <div class="col-mf-12">
                                <?php
                                $_temp = explode(",", $eTickets->mail_bcc);
                                echo !empty($_temp) ? "BCc: ".implode(",", $_temp) : "";
                                ?>
                                <i class="fa fa-angle-double-up" id="caret_up" aria-hidden="true"></i>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-md-2">
                    <?php $is_not_replyable = !empty($isAllowedToPull) && empty($is_setinterval_active) ? true : false; ?>
                    <?php if ($acd_status != 'N' && !$is_not_replyable && !empty($is_setinterval_active)) {?>
                        <div class="col-md-3 cursor-pointer hvr_div pull-right" id="forward">
                            <i class="fa fa-arrow-right fnt-20" aria-hidden="true" title="forward"></i>
                        </div>
                        <?php if (!empty($to_email_arr) || count($cc_email_arr)>1) { ?>
                            <div class="col-md-3 cursor-pointer hvr_div pull-right" id="reply_all">
                                <i class="fa fa-reply-all fnt-20 " aria-hidden="true" title="reply-all"></i>
                            </div>
                        <?php }?>
                        <div class="col-md-3 cursor-pointer  hvr_div pull-right" id="reply">
                            <i class="fa fa-reply fnt-20" aria-hidden="true" title="reply"></i>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="col-md-12">
                <hr>
                <?php
                    $mail_body = '<br/>'.str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"",base64_decode($eTickets->mail_body));
                    echo stripslashes(nl2br($mail_body));
                ?>
            </div>
            <?php
                if (!empty($attachments['data'])) {
                $count = 1;
                $div_count=1;
             ?>
                <div class="col-md-12" >
                    <?php foreach ($attachments['data'] as $attch) {?>
                        <?php
                            if ($count%6==0||$count==1){
                                echo "<div class='row'>";
                                $div_count = 1;
                            }
                        ?>
                            <div class="col-md-2 attachment-body">
                                <?php
                                    $attachment_link = site_url().$this->url('task='.$request->getControllerName()."&act=attachment&tid=".$attch->ticket_id . "&sl=" . $attch->mail_sl). "&p=" . $attch->part_position."&tsm=".$attch->tstamp."&fname=".urlencode($attch->file_name);
                                    $ym = date("ym",$attch->tstamp);
                                    $day = date("d",$attch->tstamp);
                                    $image_link = $read_image_file_path.'?type=attachment' . '&name=' . $attch->file_name . '&ym='.$ym.'&day='.$day.'&tid=' . $attch->ticket_id . '&sl=' . $attch->mail_sl;
                                ?>
                                <div class="col-md-12">
                                    <div class="center">
                                        <a href="<?php echo $attachment_link; ?>" onclick="removeMainLoader()">
                                            <span class="icon"><?php echo getFileTypeIcon($attch->attach_subtype, $attch)?></span>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="center"> <?php echo strlen($attch->file_name) > 20? substr($attch->file_name,0,15)."...":$attch->file_name  ?></div>
                                </div>
                                <?php if(isImageFile($attch->attach_subtype)){ ?>
                                    <div  class="col-md-12">
                                        <a class="cursor-pointer" data-toggle="modal" data-target="#showImgModal" data-image-src="<?php echo $image_link;?>">
                                            <div class="center"><i class="fa fa-eye" >View</i></div>
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php echo $div_count==5 ? "</div>" : ""?>
                    <?php
                        $count++;
                        $div_count++;
                    }
                    ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>


<?php if ($update_privilege):?>
    <div class="col-md-12" style='display:none'>
        <div id='inline_content'>
            <div class="popup-body">
                <div class="center">
                    <div class="title-area">
                        <div class="top-title">Update status of ticket</div>
                        <hr>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-12 alert alert-danger hide" id="status_empty_div"><span id="status_empty_msg"></span></div>
                            <div class="col-md-3">
                                <label for="Status" class="control-label">Status</label>
                            </div>
                            <div class="col-md-9">
                                <select name="pstatus" id="pstatus" class="form-control">
                                    <option value="">Select</option>
                                    <?php if (is_array($changable_status)) {?>
                                        <?php foreach ($changable_status as $key => $value)  {?>
                                            <option value="<?php echo $key ?>"><?php echo $value?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                                <input  type="text" name="sdate" id="sdate" class="form-control hide mt-10" value="<?php echo !empty($eTickets->reschedule_time) ? date("d/m/Y H:i", $eTickets->reschedule_time) : date("d/m/Y H:i")?>" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="col-md-3">
                                <label for="Disposition" class="control-label">Disposition</label>
                            </div>
                            <div class="col-md-9">
                                <?php
                                for ($i=0; $i<$num_select_box; $i++) {
                                    echo '<select class="form-control mt-5" name="disposition_id'.$i.'" id="disposition_id'.$i.'" style="display:none;" onchange="reload_sels('.$i.', this.value);"><option value="">Select</option>';
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
                            </div>
                        </div>
                        <div class="col-md-12">
                            <input class="form_submit_button btn btn-md btn-default mt-10" type="button" value="OK" name="submitagent" onclick="setStatus();" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif;?>

<script>

    let email_id = '<?php echo $eTickets->ticket_id; ?>';
    let call_id = '<?php echo $callid; ?>';
    let agent_id = '<?php echo $agent_id; ?>';
    let mail_sl = '<?php echo $eTickets->mail_sl?>';
    let total_email = '<?php echo $total_email?>';
    let pattern = "<?php echo $msg_pattern; ?>";
    let attachments = <?php echo json_encode($attachments)?>;

    let resp_data = "";
    let send_data = {email_id:email_id, call_id:call_id, agent_id:agent_id, mail_sl:mail_sl, total_email:total_email};
    let url = "";
    let original_text = "";
    let isValidate = false;
    let ticketStatus = '';
    let ticketDID = '';

    let to_tags_email_arr = [];
    let cc_tags_email_arr = [];
    let bcc_tags_email_arr = [];
    let fwd_tags_email_arr = [];
    let fwd_multi_files_arr = [];
    let address_book_emails = <?php echo  !empty($address_book_emails) ? json_encode($address_book_emails) : '""';?>;
    let reply_email = "<?php echo  $reply_email;?>";
    let reply_all_email = "<?php echo $reply_all_email;?>";
    let reply_all_cc_email = "<?php echo $reply_all_cc_email;?>";

    $(document).ready(function () {
        Initialize_Summer_note('textarea#message', ['view'], "<?php echo site_url().$this->url('task='.$request->getControllerName().'&act=uploadEmailImage&tid='.$eTickets->ticket_id.'&mid='.$eTickets->mail_sl); ?>", "<?php echo site_url().$this->url('task='.$request->getControllerName().'&act=deleteUploadedImage'); ?>",  "<?php echo $attachment_save_path; ?>");

        <?php if (!empty($is_setinterval_active)) { ?>
            setInterval(function () {
                sendUdpMsg("pull");
            },20000);
        <?php } ?>
        cc_bcc_show_hide();
        set_CC_BCC_FWD('input#email_to', to_tags_email_arr, '', address_book_emails);
        set_CC_BCC_FWD('input#email_cc', cc_tags_email_arr, '', address_book_emails);
        set_CC_BCC_FWD('input#email_bcc', bcc_tags_email_arr, '', address_book_emails);
        set_CC_BCC_FWD('input#email_forward', fwd_tags_email_arr, '', address_book_emails);

        reply();
        replyall();
        forward();
        showhide("#cc_btn", "#cc_div", "#cc_btn");
        showhide("#bcc_btn", "#bcc_div", "#bcc_btn");
        showhide("#cc_div_trash", "#cc_btn", "#cc_div", true);
        showhide("#bcc_div_trash", "#bcc_btn", "#bcc_div", true);

        popup();
        getEmailTemaplate();
        selectFile();
        load_sels();
        modal_date();
    });

    function modal_date() {
        $("#sdate").datetimepicker({
            defaultDate: new Date(),
            format:"d/m/Y H:i"
        });
    }

    function submit_form() {
        if (isSubmit && isValidate) {
            $("#st").val(ticketStatus);
            if (typeof ticketDID!=='undefined' && ticketDID!==''){
                $("#dd").val(ticketDID);
            }

            $("#customer_phone_number").val(phone_number);
            var reschedule_datetime = $("#sdate").val();
            $("#reschedule_datetime").val(reschedule_datetime);
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

    function setStatus() {
        ticketStatus = $('#pstatus').val();
        phone_number = $('#phone_number').val();
        if (typeof ticketStatus!=='undefined' && ticketStatus.length > 0){
            $.colorbox.close();
            isValidate = true;
        } else {
            $("#status_empty_div").text("Please Select Status.");
            $("#status_empty_div").removeClass('hide');
            $("#status_empty_div").show();
        }
    }

    function checkMsg(){
        $("#status_empty_div").removeClass('show');
        $("#status_empty_div").hide();

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
        } else {
            alert("Message can not be empty!");
            return false;
        }
    }

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

    function selectFile() {
        $('#att_file').MultiFile({
            STRING: {
                remove: '<i class="fa fa-minus-circle"></i>'
            },
            list: '#T7-list'
        });
    }

    function getEmailTemaplate() {
        //$("#email_temp").select2();
        $("#email_temp").on('change', function (e) {
            e.preventDefault();
            var temp_id = $(this).val();
            url = "<?php echo site_url().$this->url('task='.$request->getControllerName().'&act=get-email-template')?>";
            getXHRresponse(url, temp_id);

            resp_data.done(function (res) {
                if (typeof res !== 'undefined' && res !== null && res.length > 0 ) {
                    var new_msg = res+"<br/>"+original_text;
                    setEditor(new_msg);
                }
            });
        })
    }
    function popup() {
        $(".set-options").colorbox({
            onClosed:function(){ $.colorbox.close()},
            iframe:true, width:"470", height:"400"
        });
    }

    function cc_bcc_show_hide() {
        $("#caret_down").on('click', function (e) {
            $("#expand_cc_bcc_to").removeClass('hide');
            $("#expand_cc_bcc_to").show();
            $("#short_cc_bcc_to").hide();
        });
        $("#caret_up").on("click", function (e) {
            $("#short_cc_bcc_to").removeClass('hide');
            $("#short_cc_bcc_to").show();
            $("#expand_cc_bcc_to").hide();
        })
    }

    function showhide(click_element, show_element, hide_element, empty=false) {
        if (typeof click_element !== "undefined" && click_element !== ""){
            $(click_element).on('click', function (e) {
                $(show_element).removeClass('hide');
                $(show_element).show();
                $(hide_element).hide();
                if (empty){
                    unselectOptions(hide_element);
                }
            });
        } else {
            if (typeof show_element !== "undefined" && show_element !== "") {
                $(show_element).removeClass('hide');
                $(show_element).show();
            }
            if (typeof hide_element !== 'undefined' && hide_element !== ""){
                $(hide_element).hide();
                if (empty){
                    unselectOptions(hide_element);
                }
            }
        }
    }

    function skipEmail(is_setinterval_active, evt_type='') {
        <?php if (!empty($callid)) {?>
            url = "<?php echo site_url().$this->url('task='.$request->getControllerName().'&act=email-module-udp-msg')?>";
            send_data.method = "AG_EMAIL_CLOSE";
            send_data.event = evt_type;
            getXHRresponse(url, send_data);
        <?php } else { ?>
            if (typeof is_setinterval_active !=='undefined' && is_setinterval_active!==''){
                let url = "<?php echo site_url().$this->url('task='.$request->getControllerName().'&act=EmptyCurrentUser');?>";
                getXHRresponse(url, send_data);
            }
        <?php } ?>
        window.location.href = "<?php echo site_url().$this->url('task='.$request->getControllerName().'&callid='.$callid.'&cookie=Y&info='.$info);?>";
    }

    function workOnThisEmail() {
        url = "<?php echo site_url().$this->url('task='.$request->getControllerName().'&act=manuallyWorkOnEmail') ?>";
        getXHRresponse(url, send_data);
        resp_data.done(function(res) {
            if (typeof res.msg !== 'undefined' && res.msg !== null && res.msg === 'success') {
                sendUdpMsg("pull");
            }
            window.location.href= "<?php echo site_url().$this->url('task='.$request->getControllerName().'&act=details&tid='.$eTickets->ticket_id.'&callid='.$callid."&msl=".$eTickets->mail_sl) ?>";
        });
    }

    function sendUdpMsg(event) {
        url = "<?php echo site_url().$this->url('task='.$request->getControllerName().'&act=email-module-udp-msg');?>";
        send_data.method = "EMAIL_PING";
        send_data.event = event;
        getXHRresponse(url, send_data);
        showNewEmailArriveNotification();
    }

    function showNewEmailArriveNotification() {
        url = "<?php echo site_url().$this->url('task='.$request->getControllerName().'&act=is-new-email-arrive') ?>";
        getXHRresponse(url, send_data);
        resp_data.done(function (res) {
            if (typeof res !== 'undefined' && res !== null ) {
                var old_total_email = parseInt(total_email);
                if ( (res > old_total_email) ){
                    toastr.options.timeOut = 30000;
                    var number_of_mail_count = parseInt(res-old_email_num);
                    toastr.success('You have '+number_of_mail_count+' new email!');
                    //setNotificationNumber(res);
                }
            }
        });
    }

    function reply() {
        $("#reply").on('click', function (e) {
            empty_cc_bcc();
            showEditingPart();
            add_last_email_body();
            unset_hidden_value("#is_reply_all");
            go_to_editing_part("#editing_part");
            addOptions("#email_to", reply_email);

            showhide("", "#cc_btn", "#cc_div");
            showhide("", "#bcc_btn", "#bcc_div");
            showhide("", "#to_div", "#fwd_div");
        });
    }
    
    function replyall() {
        $("#reply_all").on('click', function (e) {
            empty_cc_bcc();
            showEditingPart();
            add_last_email_body();
            unset_hidden_value("#is_reply_all");
            go_to_editing_part("#editing_part");
            addOptions("#email_to", reply_all_email);
            addOptions("#email_cc", reply_all_cc_email);

            if (typeof reply_all_cc_email !== "undefined" && reply_all_cc_email.length > 0){
                showhide("", "#cc_div", "#cc_btn");
            }
            showhide("", "#bcc_btn", "#bcc_div");
            showhide("", "#to_div", "#fwd_div");
        });
    }

    function forward() {
        $("#forward").on('click', function (e) {
            empty_cc_bcc();
            showEditingPart();
            add_last_email_body("FWD");
            unset_hidden_value("#is_reply_all");
            go_to_editing_part("#editing_part");

            showhide("", "#cc_btn", "#cc_div");
            showhide("", "#bcc_btn", "#bcc_div");
            showhide("", "#fwd_div", "#to_div");

            get_attachment();
        });
    }

    function showEditingPart() {
        if ($("#editing_part").hasClass('hide')){
            $("#editing_part").removeClass('hide');
            $("#editing_part").addClass('show');
        }
    }
    function empty_cc_bcc() {
        unselectOptions("#forward");
        unselectOptions("#email_cc");
        unselectOptions("#email_bcc");
        unselectOptions("#email_to");
    }
    function unselectOptions(element) {
        $('input'+element).tagsinput('removeAll');
    }
    function addOptions(element, data) {
        data = typeof data == 'object' ? JSON.stringify(data) : data;
        $('input'+element).tagsinput('add', data);
    }
    function unset_hidden_value(element) {
        $(element).val('');
    }
    function go_to_editing_part(element) {
        $('html , body').animate({
            scrollTop: $(element).offset().top
        }, 1000);
    }

    function add_last_email_body(type) {
        var str = type=="FWD" ? '<b>---------- Forwarded Message -----------</b><br/><br/>' :'<b>---------- Previous Message -----------</b><br/><br/>';
        str = '<?php echo '<br/><br/><br/><br/>'.$email_signature;?>'+str+'<?php echo $last_email_body; ?>';
        setEditor(str);
        original_text = str;
    }
    function setEditor(str) {
        $('#message').summernote('reset');
        $("#message").summernote('code',  str);
        $("#message").summernote({focus: true});
        set_language_bar_in_summernote();
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
                if (store_element){
                    $(store_element).val(JSON.stringify(tempArray));
                }
            }
        });
        $(element).on('itemRemoved', function(event) {
            var idx = tempArray.indexOf(event.item);
            tempArray.splice(idx,1);
            if (store_element){
                $(store_element).val(JSON.stringify(tempArray));
            }
        });
    }

    function delete_forwarded_attachment(id) {
        var file = $("#attachment_div"+id).data('fname');
        console.log(file);
        var idx = fwd_multi_files_arr.indexOf(file);
        fwd_multi_files_arr.splice(idx,1);
        $("#forwarded_attachment_hidden").val(JSON.stringify(fwd_multi_files_arr));
        $("#attachment_div"+id).remove();
    }

    function get_attachment() {

        if (typeof attachments.data !=='undefined' && attachments.data.length>0){
            var STRING = '';
            var sl = 0;
            console.log(attachments.data);
            $.each(attachments.data, function (key, value) {
                STRING = '<div class="MultiFile-label" id="attachment_div'+sl+'" data-fname="'+value.file_path+'">'+
                    '<a class="MultiFile-remove" href="#att_file" onclick="delete_forwarded_attachment('+sl+')">'+
                    '<i class="fa fa-minus-circle"></i>'+
                    '</a>'+
                    '<span>'+
                    '<span class="MultiFile-label" title="File selected:'+ value.file_name +'">'+
                    '<span class="MultiFile-title">'+
                    value.file_name+
                    '</span>'+
                    '</span>'+
                    '</span>'+
                    '</div>';
                $("#prev_attach").append(STRING);
                fwd_multi_files_arr.push(value.file_path);
                sl++;
            });
            $("#forwarded_attachment_hidden").val(JSON.stringify(fwd_multi_files_arr));
        }
    }

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

    async function getXHRresponse(url, data) {
        resp_data = "";
        resp_data = $.ajax({
            dataType  : "JSON",
            type      : "POST",
            url       : url,
            data      : { data:data},
            success : function (res) {
                return res;
            }
        });
    }
</script>





