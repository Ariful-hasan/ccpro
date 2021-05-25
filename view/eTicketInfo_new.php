<?php
$num_select_box = 6;
$address_book_emails = [];
if (!empty($ccmails)){
    $address_book_emails = [];
    foreach ($ccmails as $item) {
        $address_book_emails[] = $item->name . ' (' . $item->email . ')';
        //$address_book_emails[] = mb_convert_encoding($cc_item, "UTF-8", "UTF-8");
    }
}

$email_signature_for_xhr = !empty($signature) && $signature->status == 'Y' ? $signature->signature_text : "";
$email_signature = !empty($signature) && $signature->status == 'Y' ? nl2br(base64_decode($signature->signature_text),TRUE ) : "";
//$email_signature = !empty($email_signature) ? '<br/>With regards,<br/><b>' .$agent_name.'</b><br/><br/>'.$email_signature.'<br/>' : '<br/>With regards,<br/><b>'.$agent_name.'</b><br/>';
$email_signature = str_replace("|AGENT-NAME|", "<br><br>".$agent_name, $email_signature);
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

$str = explode('/',$_SERVER['SCRIPT_FILENAME']);
array_pop($str);
$base_path = implode('/',$str);
$mail_sl = !empty($eTicketEmail)?count($eTicketEmail):'';

$to_email_arr = explode(",", $eTickets->mail_to);
$cc_email_arr = explode(",", $cc_emails);
$reply_email = $eTickets->created_for;

$reply_all_email = explode(",", $eTickets->mail_to);
$idx = array_search($eTickets->fetch_box_email, $reply_all_email);
if ($idx !== FALSE ){
    unset($reply_all_email[$idx]);
}
$reply_all_email[] = $eTickets->created_for;
$reply_all_email = !empty($reply_all_email) ? implode(",", $reply_all_email) : "";

$reply_all_cc_email = !empty($cc_emails) ? explode(",", $cc_emails) : "";
$idx = !empty($reply_all_cc_email) ? array_search($eTickets->fetch_box_email, $reply_all_cc_email) : FALSE;
if ($idx !== FALSE ){
    unset($reply_all_cc_email[$idx]);
}
$reply_all_cc_email = !empty($reply_all_cc_email) ? implode(",", $reply_all_cc_email) : "";
?>


<script src="assets/plugins/multiple-file/jquery.MultiFile.min.js"></script>
<link rel="stylesheet" href="assets/css/email_details.css?v=1.0.1">
<!-- Include the plugin's CSS and JS: -->
<script type="text/javascript" src="js/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="js/bootstrap-multiselect/css/bootstrap-multiselect.css" type="text/css"/>
<!--Added CDN for New Icons-->
<!--<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">-->
<link rel="stylesheet" href="ccd/bootstrap-select/bootstrap-select.min.css"/>
<script src="ccd/bootstrap-select/bootstrap-select.min.js"></script>
<link rel="stylesheet" href="assets/plugins/typeahead/typeahead.min.css">
<link rel="stylesheet" href="assets/plugins/typeahead/bootstrap-tagsinput.css">
<script src="assets/plugins/typeahead/bootstrap3-typeahead.min.js"></script>
<script src="assets/plugins/typeahead/bootstrap-tagsinput.js"></script>
<link rel="stylesheet" href="js/toastr/toastr.min.css">
<script src="js/toastr/toastr.min.js"></script>

<?php include_once ('lib/summer_note_lib.php')?>
<?php include_once ('template_modal.php');?>

<link rel="stylesheet" type="text/css" media="screen" href="css/form.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/report.css">

<?php if (!empty($callid)) { ?>
    <style>
        .note-toolbar-wrapper panel-default{
            text-align: left !important;
        }
        .note-editing-area{
            text-align: left !important;
        }
        .panel-heading{
            text-align: left !important;
        }
        .top-title{
            text-align: left !important;
        }
        .bootstrap-tagsinput{
            text-align: left !important;
        }
        .reply-header{
            text-align: left !important;
        }
    </style>
<?php } ?>

<div class="">

    <?php if ((!empty($queue_msg) || !empty($agent_msg)) && empty($callid) && empty($current_user)) { ?>
        <div class="alert alert-danger"><?php echo  !empty($queue_msg) ? $queue_msg : $agent_msg; ?></div>
    <?php }?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-12">
                    <?php if(!empty($isAllowedToPull) && empty($is_setinterval_active)): ?>
                    <?php else: ?>
                        <div class="col-md-1 pull-left">
                            <span class=""><i class="fa fa-user <?php echo empty($is_setinterval_active)?'other':'own'?> "></i></span> <b><?php echo $current_user?></b>
                            <?php  if (empty($is_setinterval_active)){ ?><div class="help"><div class="typing-loader text"></div></div><?php } ?>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($isAllowedToPull) && empty($is_setinterval_active)): ?>
                    <?php else: ?>
                        <div class="col-md-1 pull-right"><?php if (!empty($is_setinterval_active)):?><button type="button" class="btn btn-md btn-danger btn-rds red-shadow pull-right" id="skip" onclick="skipEmail('<?php echo $is_setinterval_active?>', 'close')"><i class="fa fa-times" aria-hidden="true"></i> <b>Close</b></button><?php endif; ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="panel-body">
            <div class="row"></div>
            <div class="col-md-12">
                <div class="col-md-10 pdl-0">
                    <div class="row">
                        <div class="col-md-12 col-sm-12 pdl-0">
                            <div class="col-md-4 col-sm-12 form-group pdl-0">
                                <label for="user" class="col-md-2 col-sm-4 control-label"><b>User</b></label>
                                <lebel class="col-md-10 col-sm-8 control-label"><?php echo ":".$eTickets->created_for; ?></lebel>
                            </div>
                            <div class="col-md-8 col-sm-12 form-group pdr-0">
                                <label for="user" class="col-md-2 col-sm-4 control-label"><b>Created</b></label>
                                <label class="col-md-10 control-label col-sm-8"><?php if ($eTickets->create_time!="" && $eTickets->created_by!=""){echo ":".date("Y-m-d H:i:s", $eTickets->create_time). " by ".$eTickets->created_by;} ?></label>
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12 pdl-0">
                            <div class="col-md-4 col-sm-12 form-group pdl-0">
                                <label for="user" class="col-md-2 col-sm-4 control-label "><b>To</b></label>
                                <label class="col-md-10 col-sm-8 control-label"><?php
                                    if (!empty($eTickets->mail_to)){ ?>
                                        <?php if (count($to_email_arr) > 1) { ?>
                                            <div class="dropdown to_cc_bcc">
                                                <?php echo ": ".$to_email_arr[0] ;?>
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
                                            echo ":".$to_email_arr[0] ;
                                            ?>
                                        <?php } ?>
                                        <?php
                                    } elseif (!empty($eTickets->fetch_box_email)) { ?>
                                        <?php echo ": ".$eTickets->fetch_box_email;?>
                                    <?php } ?>
                                </label>
                            </div>
                            <div class="col-md-8 col-sm-12 form-group pdr-0">
                                <label for="user" class="col-md-2 col-sm-4 control-label"><b>Status</b></label>
                                <label class="col-md-10 col-sm-8 control-label">
                                    <?php if ($eTickets->last_update_time!=""){echo ': <span class="label-prmy"> ' . $eTicket_model->getTicketStatusLabel($eTickets->status)."</span> on ".date("Y-m-d H:i:s", $eTickets->last_update_time). " By ".$status_updated_by;} ?>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12 pdl-0">
                            <div class="col-md-4 col-sm-12 pdl-0">
                                <label for="user" class="col-md-2 col-sm-4 control-label"><b>Skill</b></label>
                                <label class="col-md-10 col-sm-8 control-label"><?php echo ":".$eTickets->skill_name; ?></label>
                            </div>
                            <div class="col-md-8 col-sm-12 form-group pdr-0">
                                <label for="user" class="col-md-2 col-sm-4 control-label"><b>Disposition</b></label>
                                <label class="col-md-10 col-sm-8 control-label">
                                    <?php
                                    echo ": ";
                                    if (!empty($disposition)) {
                                        $path = $eTicket_model->getDispositionPath($disposition->disposition_id);
                                        if (!empty($path)) echo $path . ' -> ';
                                        echo $disposition->title;
                                    } ?>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12 form-group pdl-0">
                            <label for="user" class="col-md-2 col-sm-4 control-label"><b>Agent</b></label>
                            <label class="col-md-10 col-sm-8 control-label"><?php echo ":"; if (isset($assigned_to) && !empty($assigned_to)) echo $assigned_to->agent_id . ' - ' . $assigned_to->nick; ?></label>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <?php if(!empty($isAllowedToPull) && empty($is_setinterval_active)){ ?>
                        <div class="col-md-12 pdtb-3"><button type="button" class="btn btn-md btn-warning yellow-shadow btn-rds  chng-btn-width" id="skip" onclick="skipEmail('<?php echo $is_setinterval_active?>', 'skip')"><i class="fa fa-arrow-left" aria-hidden="true"></i> <b>skip</b> </button></div>
                        <div class="col-md-12 pdtb-3"><button  class="btn btn-md btn-success green-shadow btn-rds chng-btn-width" id="work_on" onclick="workOnThisEmail()"><i class="fa fa-pencil-square" aria-hidden="true"></i>  <b>Pull</b></button></div>
                        <div class="col-md-12 pdtb-3"><a  href="<?php echo site_url().$this->url('task=email&act=transfer&tid='.$eTickets->ticket_id.'&callid='.$callid)?>" class="btn btn-md btn-prpl btn-rds prpl-shadow set-options chng-btn-width" id="work_on"><i class="fa fa-exchange" aria-hidden="true"></i><b> Transfer</b></a></div>
                    <?php } ?>

                    <div class="col-md-12 pdtb-3">
                        <?php
                        $changable_status = $eTicket_model->getChangableTicketStatus('K');  /////Every status it can be pullable.
                        if ($eTickets->status == 'E' && (UserAuth::hasRole('admin') || UserAuth::hasRole('supervisor'))) {
                            $status_updatable = true;
                        } else {
                            $status_updatable = false;
                        }
                        if ($status_updatable || ($update_privilege && is_array($changable_status) && count($changable_status) > 0)):
                            if (($acd_status != 'N' || $dist_status =='R') && !empty($is_setinterval_active)): ?>
                                <a class="set-options btn btn-md btn-prpl btn-rds chng-btn-width prpl-shadow" href="<?php echo site_url().$this->url('task='.$request->getControllerName()."&act=status&tid=".$eTickets->ticket_id.'&callid='.$callid);?>">
                                    <i class="fa fa-pencil" aria-hidden="true"></i> <b>Change status</b>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-12 pdtb-3">
                        <?php if ($update_privilege && ($acd_status != 'N' || $dist_status=='R') && !empty($is_setinterval_active)):?>
                            <a class="set-options btn btn-md btn-success btn-rds chng-btn-width green-shadow" href="<?php echo site_url().$this->url('task='.$request->getControllerName()."&act=skill&tid=".$eTickets->ticket_id);?>">
                                <i class="fa fa-pencil" aria-hidden="true"></i> <b><?php if (empty($eTickets->skill_name)) echo 'Add Skill'; else echo 'Skill Transfer';?></b>
                            </a>
                        <?php endif;?>
                    </div>
                    <div class="col-md-12 pdtb-3">
                        <?php if ($update_privilege && is_array($changable_status) && count($changable_status) > 0 && ($acd_status != 'N' || $dist_status=='R') && !empty($is_setinterval_active)):?>
                            <a class="set-options btn btn-md btn-warning btn-rds chng-btn-width yellow-shadow" href="<?php echo site_url().$this->url('task='.$request->getControllerName()."&act=disposition&tid=".$eTickets->ticket_id);?>">
                                <i class="fa fa-pencil" aria-hidden="true"></i><b><?php if (empty($eTickets->disposition_id)) echo 'Change Disp.'; else echo 'Change Disp.';?></b>
                            </a>
                        <?php endif;?>
                    </div>
                    <div class="col-md-12 pdtb-3">
                        <?php if ( ($acd_status=='' || $acd_status == 'N') && (!empty($is_setinterval_active))) {?>
                            <?php if ($update_privilege && is_array($changable_status) && count($changable_status) > 0):?>
                                <a class="set-options btn btn-md btn-grn btn-rds chng-btn-width grn-shadow" href="<?php echo site_url().$this->url('task='.$request->getControllerName()."&act=assign&tid=".$eTickets->ticket_id);?>">
                                    <i class="fa fa-pencil" aria-hidden="true"></i><b><?php if (empty($eTickets->assigned_to)) echo 'Agent Transfer'; else echo 'Agent Transfer';?></b>
                                </a>
                            <?php endif;?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php if (!empty($callid)) { ?>
            <div class="col-md-12">
                <label for="user" class="col-md-1 control-label pdl-0"><b>Subject</b></label>
                <label class="col-md-11 control-label"><?php echo ": "; echo !empty($pageTitle2) ? $pageTitle2 :'' ?></label>
            </div>
        <?php } ?>
    </div>
</div>


<div class="row">
    <div class="col-md-12 col-sm-12 pdl-0 pdr-0">
        <?php if ($update_privilege && is_array($changable_status) && count($changable_status) > 0): ;?>
            <?php if ($acd_status != 'N' && !empty($is_setinterval_active)) { ?>

                <div class="col-lg-12 col-md-12 col-sm-12 pdl-0" style="padding-bottom: 1%;">
                    <div class="col-lg-6 col-md-6 col-sm-8  pdl-0">
                        <div class="btn-group-vertical ">
                            <div class="col-lg-4 col-md-4 col-sm-3 <?php if (empty($eTicketEmail[0]->mail_cc) && count($to_email_arr)==1) { echo 'mr-3'; } ?>"><button class="btn btn-sm btn-light-prmy btn-rds" id="reply"><i class="fa fa-reply" aria-hidden="true"></i> Reply</button></div>
                            <?php if (!empty($eTicketEmail[0]->mail_cc) || count($to_email_arr)>1) { ?><div class="col-lg-4 col-md-4 col-sm-3"><button class="btn btn-sm btn-light-warning btn-rds" id="reply-all"><i class="fa fa-reply-all" aria-hidden="true"></i> ReplyAll</button></div><?php }?>
                            <div class="col-lg-4 col-md-4 col-sm-3"><button class="btn btn-sm btn-light-success btn-rds" id="btn-forward"><i class="fa fa-arrow-right" aria-hidden="true"></i> Froward</button></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3"><div id="replying_email_text" class="hide replying_email_text"><b>Replying only single email</b></div></div>
                    <div class="col-lg-3 col-md-3 pdr-0">
                        <div class="col-lg-6 col-lg-offset-6 col-md-6 col-md-offset-6 hide" id="cc_bcc_btn_div">
                            <div class="btn-group-vertical">
                                <div class="col-lg-6 col-md-6" id="cc_btn"><button class="btn btn-sm btn-light-info btn-rds">CC</button></div>
                                <div class="col-lg-6 col-md-6" id="bcc_btn"><button class="btn btn-sm btn-light-info btn-rds">Bcc</button></div>
                            </div>
                        </div>
                    </div>
                </div>


                <div id="editing_part" class="col-md-12 col-sm-12 hide" style="padding-bottom: 1%">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <form method="post" enctype="multipart/form-data" name="ticket_message" id="ticket_message">
                                <div class="row">
                                    <div class="col-md-12 pdr-0 pdl-0" style="">

                                        <div class="col-md-12 pdr-0" id="to_div">
                                            <label for="email_cc" class="col-md-1">To</label>
                                            <div class="col-md-11 form-group">
                                                <input class="form-control" id="email_to" name="to" type="text" value=""/>
                                            </div>
                                        </div>
                                        <div class="col-md-12 pdr-0" id="fwd_div">
                                            <label for="forward" class="col-md-1">Forward</label>
                                            <div class="col-md-11 form-group">
                                                <input class="form-control" id="forward" name="forward" type="text" value=""/>
                                            </div>
                                        </div>
                                        <div class="col-md-12 pdr-0" id="cc_div">
                                            <label for="email_cc" class="col-md-1">CC</label>
                                            <div class="col-md-11 form-group">
                                                <input class="form-control" id="email_cc" name="cc" type="text" value=""/><i class="fa fa-trash-o trash cursor-pointer" id="cc_div_trash" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-12 pdr-0" id="bcc_div">
                                            <label for="email_bcc" class="col-md-1">BCC</label>
                                            <div class="col-md-11 form-group">
                                                <input class="form-control" id="email_bcc" name="bcc" type="text" value=""/><i class="fa fa-trash-o trash cursor-pointer" id="bcc_div_trash" aria-hidden="true"></i>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-md-5 hide" style="margin-top: 6px">
                                        <div class="col-md-12">
                                            <?php
                                            $tsk = "task=".$request->getControllerName(); $act = "&act=templates";$dd = !empty($disposition->disposition_id)?$disposition->disposition_id:'';
                                            $sid = !empty($eTickets->skill_id)?$eTickets->skill_id:"";
                                            ?>
                                            <label for="temp" class="col-md-1"></label>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                $tsk = "task=".$request->getControllerName();
                                $act = "&act=templates";
                                $dd = !empty($disposition->disposition_id)?$disposition->disposition_id:'';
                                $sid = !empty($eTickets->skill_id)?$eTickets->skill_id:"";
                                ?>
                                <!--                        <label for="message">Message <font class="required">*</font></label><br>-->
                                <input type="hidden" name="st" id="st" value="" maxlength="1" />
                                <input type="hidden" name="dd" id="dd" value="<?php echo !empty($disposition->disposition_id) ? $disposition->disposition_id:'' ?>" maxlength="4" />
                                <input type="hidden" name="reschedule_datetime" id="reschedule_datetime" value="" />
                                <input type="hidden" name="to_tags_email_arr" id="to_tags_email_arr" value=""/>
                                <input type="hidden" name="cc_tags_email_arr" id="cc_tags_email_arr" value=""/>
                                <input type="hidden" name="bcc_tags_email_arr" id="bcc_tags_email_arr" value=""/>
                                <input type="hidden" name="fwd_tags_email_arr" id="fwd_tags_email_arr" value=""/>
                                <input type="hidden" name="specific_email_sl" id="specific_email_sl" value=""/>
                                <input type="hidden" name="forwarded_attachment_hidden" id="forwarded_attachment_hidden" value=""/>
                                <input type="hidden" name="is_reply_all" id="is_reply_all" value=""/>
                                <input type="hidden" name="total_email_num" id="" value="<?php echo $mail_sl?>"/>
                                <input type="hidden" name="customer_phone_number" id="customer_phone_number" value="" maxlength="15"/>


                                <div class=" summernote-area"><!--col-md-10 col-sm-10-->
                                    <textarea style="width:90%; height: 145px;" id="message" name="message"></textarea>
                                </div>
                                <?php if ($acd_status != 'N' && !empty($is_setinterval_active)) { ?>
                                    <div class="col-md-12" style="margin-left: -28px">
                                        <div class="col-md-1">
                                            <!--                                            <input class="form_submit_button pull-left btn-rds" type="button" onclick="checkMsg()" value="Send" id="submitTicket" name="submitTicket" style="font-size:14px;padding:5px 0.84615em;" />-->
                                            <button class="btn btn-md btn-grn grn-shadow btn-rds pull-left" type="button" onclick="checkMsg()" id="submitTicket" name="submitTicket">Send</button>
                                        </div>
                                        <div class="col-md-1">
                                            <button class="btn btn-md btn-prmy prpl-shadow btn-rds" type="button" id="temp" url="<?php echo site_url().$this->url($tsk.$act.'&did='.$dd.'&sid='.$sid); ?>"> Template </button>
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
                    </div>
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
            $i = 0;
            foreach ($eTicketEmail as $eEmail){
                $display = $i > 0 ? 'none' : 'block';

                $full_time_details = strlen($eEmail->tstamp)==10 ? date("M d ' Y, h:i:s a ", $eEmail->tstamp).get_time_ago($eEmail->tstamp): "processing"
                ?>
                <div class="col-md-12 col-sm-12 reply-header">
                    <div style="cursor:pointer;" class="email_title col-md-12" id="m<?php echo $eEmail->mail_sl;?>">&nbsp;
                        <div class="col-md-6 font-normal ml--33" style="display:inline-block;">
                            <i class="fa fa-envelope circle-user" aria-hidden="true"></i>
                            <?php echo $eEmail->agent_id=="" ? $eTickets->first_name.' '.$eTickets->last_name."[".$eEmail->from_email."]" : $eEmail->agent_id;?>
                        </div>
                        <div class="col-md-5 pull-right font-normal mr--30">

                            <span class="pull-right">
                                <span class="label-prmy-solid"><?php echo  !empty($status_option_list[$eEmail->email_status]) ? $status_option_list[$eEmail->email_status] : 'New' ;?></span>
                                <?php echo $full_time_details?> <i class="pls-mns fa <?php echo $display=='none'? 'fa-minus-circle':'fa-plus-circle'?> bl-nn" aria-hidden="true"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12 reply-body" style="display:<?php echo $display;?>;" id="bm<?php echo $eEmail->mail_sl;?>">
                    <div class="col-md-12" style="min-height: 250px;overflow: auto;">
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
                                    <div class="pull-right cursor-pointer specific_reply" data-email_sl="<?php echo $eEmail->mail_sl?>">
                                        <i class="fa fa-reply" aria-hidden="true" ></i>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                        </div>
                        <?php

                        $tidy = tidy_parse_string(base64_decode($eEmail->mail_body));
                        $tidy->cleanRepair();
                        $mail_body = '<br/>'.str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"",$tidy);
                        echo stripslashes(nl2br($mail_body));
                        ?>
                    </div>
                    <?php if ($eEmail->has_attachment == 'Y') {
                        $attachemnts = $eTicket_model->getAttachments($eEmail->ticket_id, $eEmail->mail_sl);
                        ?>
                        <?php if (is_array($attachemnts)) {?>
                            <hr style="color: #0D5F76; border: 1px ridge">
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
                            <td class="form_column_caption center"><b>Phone Number:</b></td>
                        </tr>
                        <tr class="form_row light_element">
                            <td class="form_column_caption center">
                                <input type="text" name="phone_number" id="phone_number">
                            </td>
                        </tr>

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
                            <td class="form_column_caption center">
                                <select class="selectpicker" name="dis_id" id="dis_id" data-live-search="true">
                                    <option value="">Select</option>
                                    <?php if (!empty($disposition_list)) { ?>
                                        <?php foreach ($disposition_list as $index => $value) { ?>
                                            <option value="<?php echo $index ?>" <?php echo $index == $eTickets->disposition_id ? "selected" : "" ?>><?php echo $value ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
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
<?php endif; ?>


<script type="text/javascript">

    let email_id = '<?php echo $eTickets->ticket_id; ?>';
    let call_id = '<?php echo $callid; ?>';
    let agent_id = '<?php echo $agent_id; ?>';
    let attachment_save_path = "<?php echo $attachment_save_path; ?>";
    let forwarded_attachment_path = '';
    let fwd_multi_files_arr = [];
    let to_email_arr = '<?php echo json_encode($to_email_arr);?>';

    let to_tags_email_arr = [];
    let cc_tags_email_arr = [];
    let bcc_tags_email_arr = [];
    let fwd_tags_email_arr = [];
    let mail_sl = '<?php echo $email_sl?>';
    let notification_showed_for_this_num = 0;

    let isSubmit = false;
    let ticketStatus = '';
    let ticketDID = '';
    let isValidate = false;
    let phone_number = '';
    let did = '<?php echo json_encode($eTickets->disposition_id)?>';
    let sid = '<?php echo json_encode($eTickets->skill_id)?>';
    let address_book_emails = <?php echo !empty($address_book_emails) ? json_encode($address_book_emails) : '""';?>;
    //let address_book_emails = '<?php //json_encode($address_book_emails); ?>//';
    //console.log(address_book_emails);

    let reply_email = "<?php echo  $reply_email;?>";
    let reply_all_email = "<?php echo $reply_all_email;?>";
    let reply_all_cc_email = "<?php echo $reply_all_cc_email;?>";

    $(document).ready(function() {
        email_attachment();
        emails_collapse_uncollapse();
        colorbox_close();
        reply_replyall_forward();

        text_area_id = "'textarea#message'";
        click_template(text_area_id, did, sid);//////////////////From template_modal.php
        load_sels();  ////DID CHANGE STATUS//////
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

        cc_bcc_to_dropdown();
        set_CC_BCC_FWD('input#email_to', to_tags_email_arr, '', address_book_emails);
        set_CC_BCC_FWD('input#email_cc', cc_tags_email_arr, '#cc_tags_email_arr', address_book_emails);
        set_CC_BCC_FWD('input#email_bcc', bcc_tags_email_arr, '#bcc_tags_email_arr', address_book_emails);
        set_CC_BCC_FWD('input#forward', fwd_tags_email_arr, '#fwd_tags_email_arr', address_book_emails);

        $('#showImgModal').on('show.bs.modal', function (e) {
            var imgSrc = $(e.relatedTarget).data('image-src');
            $(e.currentTarget).find('#showImg').attr('src', imgSrc);
        });

        showhide("#cc_btn", "#cc_div", "#cc_btn");
        showhide("#bcc_btn", "#bcc_div", "#bcc_btn");
        showhide("#cc_div_trash", "#cc_btn", "#cc_div", true);
        showhide("#bcc_div_trash", "#bcc_btn", "#bcc_div", true);
        toastr.options.newestOnTop = false;
        toastr.options.progressBar = true;
        toastr.options.closeButton = true;
    });

    function get_specific_reply_email(ticket_id, mail_sl) {
        if (typeof ticket_id !=='undefined' && ticket_id.length > 0 && typeof mail_sl !=='undefined' && mail_sl.length == 3){
            $.ajax({
                dataType    : "JSON",
                type        : "POST",
                url         : "<?php echo site_url().$this->url("task=" . $request->getControllerName() . '&act=get-email-text'); ?>",
                data        : {ticket_id:ticket_id, mail_sl:mail_sl},
                success     : function (res) {
                    //console.log(res);
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
                    //console.log(res.data);
                    if (typeof res.data !=='undefined' && res.data.length > 0){
                        var STRING = '';
                        var sl = 0;
                        $.each(res.data, function (key, value) {
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
    //function add_last_email_body(type) {
    //    var str = type=="FWD" ? '<b>---------- Forwarded Message -----------</b><br/><br/>' :'<b>---------- Previous Message -----------</b><br/><br/>';
    //    str = '<?php //echo '<br/><br/><br/><br/>'.$email_signature;?>//'+str+'<?php //echo $last_email_body; ?>//';
    //    $('#message').summernote('reset');
    //    $("#message").summernote('code',  str);
    //    $("#message").summernote({focus: true});
    //    set_language_bar_in_summernote();
    //}

    function add_last_email_body(type) {
        $.ajax({
            dataType    :   "JSON",
            type        :   "POST",
            url         :   "<?php echo site_url().$this->url('task=email&act=last-email-body')?>",
            data        :   {email_id:email_id, skill_id:sid, type:type},
            success : function (res) {
                if (typeof res!=='undefined' && res!==null && res.length>0){
                    // console.log(res);
                    $('#message').summernote('reset');
                    $("#message").summernote('code',res);
                    $("#message").summernote({focus: true});
                    set_language_bar_in_summernote();
                }
            }
        });
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
        if ($("#cc_bcc_btn_div").hasClass('hide')){
            $("#cc_bcc_btn_div").removeClass('hide');
            $("#cc_bcc_btn_div").addClass('show');
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
            data        :   {method:"AG_EMAIL_CLOSE", email_id:email_id, call_id:call_id, agent_id:agent_id, evt_type:evt_type},
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
        window.location.href = "<?php echo site_url().$this->url('task='.$request->getControllerName().'&callid='.$callid.'&cookie=Y&info='.$info);?>";
    }

    function workOnThisEmail() {
        //console.log('pull');
        $.ajax({
            dataType    :   "JSON",
            type        :   "POST",
            url         :   "<?php echo site_url().$this->url('task='.$request->getControllerName().'&act=manuallyWorkOnEmail') ?>",
            data        :   {tid: email_id},
            success     :   function (res) {
                if (typeof res.msg !== 'undefined' && res.msg !== null && res.msg === 'success') {
                    sendUdpMsg();
                }
                window.location.href= "<?php echo site_url().$this->url('task='.$request->getControllerName().'&act=details&tid='.$eTickets->ticket_id.'&callid='.$callid) ?>";
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
                        //$(".light_element").removeClass('show');
                        //$(".light_element").hide();

                        $(".reload_page_tr").removeClass('hide');
                        $(".reload_page_tr").show();
                    } else {
                        //$(".light_element").removeClass('hide');
                        //$(".light_element").show();

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
    function setStatus()
    {
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

    function submit_form()
    {
        ////   isValidate is added for test purpose of outside click not subumit  ////
        if (isSubmit && isValidate) {
            $("#st").val(ticketStatus);
            //$("#dd").val(ticketDID);
            $("#dd").val($("#dis_id").val());
            $("#customer_phone_number").val(phone_number);
            var reschedule_datetime = $("#sdate").val();
            $("#reschedule_datetime").val(reschedule_datetime);
            if($("#message").val() != ""){
                $("#ticket_message").attr('action', '<?php echo $sublink; ?>');
                $("#ticket_message").submit();
            }else{
                toastr.error("Message can not be empty!");
                return false;
            }
        }
        isSubmit = false;
    }

    //agentCallStack = [];
    function email_attachment(){
        $('#att_file').MultiFile({
            STRING: {
                remove: '<i class="fa fa-minus-circle"></i>'
            },
            list: '#T7-list'
        });
        //$('.multiselect').multiselect({buttonClass:'btn'});
    }
    function emails_collapse_uncollapse(){
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
        });
    }
    function colorbox_close(){
        $(".set-options").colorbox({
            onClosed:function(){ $.colorbox.close()},
            iframe:true, width:"550", height:"600"
        });
    }
    function empty_cc_bcc() {
        unselectOptions("#forward");
        unselectOptions("#email_cc");
        unselectOptions("#email_bcc");
        unselectOptions("#email_to");
    }
    function addOptions(element, data) {
        data = typeof data == 'object' ? JSON.stringify(data) : data;
        $('input'+element).tagsinput('add', data);
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
    function reply_replyall_forward(){
        $("#reply").on('click', function () {
            showEditingPart();
            add_last_email_body();
            unset_hidden_value("#is_reply_all");
            unset_hidden_value("#specific_email_sl");
            hide_specific_email_text();
            empty_cc_bcc();
            addOptions("#email_to", reply_email);
            showhide("", "#cc_btn", "#cc_div");
            showhide("", "#bcc_btn", "#bcc_div");
            showhide("", "#to_div", "#fwd_div");
        });

        $("#reply-all").on('click', function () {
            showEditingPart();
            // unselectOptions("#forward");
            //selectMails("#email_cc", "<?php //echo $cc_emails?>//");
            //selectMails("#email_bcc", "<?php //echo $bcc_emails?>//");
            unset_hidden_value("#specific_email_sl");
            hide_specific_email_text();
            add_last_email_body();
            set_value("#is_reply_all", "Y");
            empty_cc_bcc();
            addOptions("#email_to", reply_all_email);
            addOptions("#email_cc", reply_all_cc_email);
            if (typeof reply_all_cc_email !== "undefined" && reply_all_cc_email.length > 0){
                showhide("", "#cc_div", "#cc_btn");
            }
            showhide("", "#bcc_btn", "#bcc_div");
            showhide("", "#to_div", "#fwd_div");
        });
        $("#btn-forward").on('click', function () {
            showEditingPart();
            // unselectOptions("#email_cc");
            // unselectOptions("#email_bcc");
            unset_hidden_value("#specific_email_sl");
            hide_specific_email_text();
            add_last_email_body('FWD');
            get_attachment(email_id, '<?php echo $email_sl?>');
            unset_hidden_value("#is_reply_all");

            empty_cc_bcc();
            showhide("", "#cc_btn", "#cc_div");
            showhide("", "#bcc_btn", "#bcc_div");
            showhide("", "#fwd_div", "#to_div");
        });
        $(".specific_reply").on('click', function (e) {
            // unselectOptions("#email_cc");
            // unselectOptions("#email_bcc");
            var specific_reply_sl = $(this).data("email_sl");
            showEditingPart();
            go_to_editing_part("#editing_part");
            show_specific_email_text();
            //set_value("#specific_email_sl", specific_reply_sl);
            get_specific_reply_email(email_id, specific_reply_sl);


            empty_cc_bcc();
            addOptions("#email_to", reply_email);
            showhide("", "#cc_btn", "#cc_div");
            showhide("", "#bcc_btn", "#bcc_div");
            showhide("", "#to_div", "#fwd_div");
        });
    }
    function cc_bcc_to_dropdown() {
        $('div.to_cc_bcc a').on('click', function (event) {
            $(this).parent().toggleClass('open');
        });
        $('body').on('click', function (e) {
            if (!$('div.dropdown.to_cc_bcc').is(e.target) && $('div.dropdown.to_cc_bcc').has(e.target).length === 0 && $('.open').has(e.target).length === 0) {
                $('div.dropdown.to_cc_bcc').removeClass('open');
            }
        });
    }
    function isEmptyEmail() {
        let to_email = $("#email_to").val();
        let fwd_email = $("#forward").val();
        if ((typeof to_email !== 'undefined' && to_email.length > 0) || (typeof fwd_email !== 'undefined' && fwd_email.length > 0)){
            return true;
        }
        return false
    }
    function checkMsg(){
        isNewEmailArrive();
        //// For empty status ////
        $("#status_empty_div").removeClass('show');
        $("#status_empty_div").hide();
        //// For empty status ////

        var pattern = "<?php echo $msg_pattern; ?>";
        if (isEmptyEmail() === true){
            if($("#message").val() != ""){
                var msg = $("#message").val().toLowerCase();
                if (pattern != "" && pattern != null && msg.indexOf(pattern.toLowerCase()) >= 0){
                    alert("You must replace the fixed text: "+pattern);
                    return false;
                }else{
                    isSubmit = true;
                    ticketStatus = '';
                    $.colorbox({inline:true, width:"550", height:"600", href:"#inline_content",onOpen: function(){show_reschedule_date();}, onClosed:function(){ submit_form(); }});
                    return false;
                }
            } else {
                toastr.error("Message can not be empty!");
                return false;
            }
        } else {
            toastr.error("Email address field is empty!");
            return false;
        }
    }

    function setNotificationNumber(value){
        this.notification_showed_for_this_num = value;
    }
</script>





