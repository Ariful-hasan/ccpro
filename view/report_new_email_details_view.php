<?php
/**
 * Created by PhpStorm.
 * User: arif
 * Date: 12/3/2018
 * Time: 12:18 PM
 */
$GLOBALS['status_option_list'] = $status_option_list;
function get_to_cc($email, $fetch_box_name){
    $response = "";
    $_mail_to_arr = explode(",",$email);
    $new_mail_to_arr = [];
    foreach ($_mail_to_arr as $item) {
        $new_mail_to_arr[] = $item;
    }
    $response = implode(', ', $new_mail_to_arr);
    if (empty($email->mail_to) && !empty($fetch_box_name)) {
        $response = $fetch_box_name;
    }
    return $response;
}
function getEmailString($email){
    $_mail_to_arr = explode(",", $email);
    $new_mail_to_arr = [];
    foreach ($_mail_to_arr as $item) {
        $new_mail_to_arr[] = substr($item, 0, strpos($item, "@"));
    }
    return implode(',', $new_mail_to_arr);
}
function getFormatEmailData($eEmail, $eTickets){
    $obj = new stdClass();
    $obj->tstamp = strlen($eEmail->tstamp)==10 ? date("M d ' Y, h:i:s a ", $eEmail->tstamp).get_time_ago($eEmail->tstamp): "processing";
    $obj->user = $eEmail->agent_id=="" ? $eTickets->first_name.' '.$eTickets->last_name."[".$eEmail->from_email."]" : $eEmail->agent_id;
    $obj->status = !empty($GLOBALS['status_option_list'][$eEmail->email_status]) ? $GLOBALS['status_option_list'][$eEmail->email_status] : 'New';
    $obj->cc_string = getEmailString($eEmail->mail_to);
    $obj->cc_string .= getEmailString($eEmail->mail_cc);
    $obj->to = get_to_cc($eEmail->mail_to, $eTickets->fetch_box_email);
    $obj->cc = !empty($eEmail->mail_cc) ? get_to_cc($eEmail->mail_cc, $eTickets->fetch_box_email) : "";

    $mail_body = '<br/>'.str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"",base64_decode($eEmail->mail_body));
    $obj->mail_body = stripslashes(nl2br($mail_body));

    return $obj;
}
?>

<style>
    .status {
        border-radius: 3px;
        background: #60d0e7; padding: 4px 4px 4px;
        color: #ffffff;
        padding-top: 5px;
        padding-bottom: 5px;
        padding-left: 20px;
        padding-right: 20px;
    }
    .email-dropdown{
        min-width: 348px !important;
        height: auto;
        font-size: 13px;
    }
</style>


<?php if (!empty($email_list)) { ?>

    <div class="panel panel-default">
        <div class="panel-heading"><b><?php echo base64_decode($eTickets->subject); ?></b></div>
        <div class="panel-body">
            <div class="col-md-12">
                <div class="col-md-5"><b>User: </b> <?php echo $eTickets->created_for; ?></div>
                <div class="col-md-4"><b>Skill: </b><?php echo $eTickets->skill_name?></div>
                <div class="col-md-3"><b> Created: </b><?php echo  $eTickets->create_time!=""? date("Y-m-d H:i:s", $eTickets->create_time): "" ?></div>
            </div>
            <div class="col-md-12" style="margin-top: 1%">
                <div class="col-md-5"><b> Customer ID: </b><?php echo  $eTickets->customer_id; ?></div>
                <div class="col-md-7"><b> To: </b><?php echo  get_to_cc($eTickets->mail_to, $eTickets->fetch_box_email); ?></div>
            </div>
        </div>
    </div>

    <?php foreach ($email_list as $email) {?>
        <?php $format_email = getFormatEmailData($email, $eTickets); ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-user-circle-o" aria-hidden="true"></i><?php echo $format_email->user; ?>
                <div class="pull-right">
                    <span class="status"><?php echo $format_email->status; ?></span>
                    <?php echo $format_email->tstamp; ?>
                </div>
            </div>

            <div class="panel-body">
                <div class="col-md-12" >
                    <div class="dropdown to_cc_bcc">
                        <?php echo $format_email->cc_string?>
                        <a href="javascript:void(0)" id="dropdownMenuButton" type="button"  aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-caret-down" aria-hidden="true"></i>
                        </a>
                        <div class="dropdown-menu  email-dropdown" aria-labelledby="dropdownMenuButton">
                            <div class="cc-bcc-popup">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="col-md-2"><lable for="control-label">From</lable></div>
                                        <div class="col-md-10 pr-0">:<?php echo $email->from_email; ?></div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="col-md-2"><lable for="control-label">To</lable></div>
                                        <div class="col-md-10 pr-0">
                                            :<?php echo $format_email->to; ?>
                                        </div>
                                    </div>
                                    <?php if ($email->is_forward=="Y" && !empty($email->mail_to)){
                                        $froward_arr = explode(",",$email->mail_to); ?>
                                        <div class="col-md-12">
                                            <div class="col-md-2"><lable for="control-label">Fwd</lable></div>
                                            <div class="col-md-10">
                                                :<?php foreach ($froward_arr as $fwd) {?>
                                                    <?php echo $fwd;?>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    <?php }?>
                                    <?php if (!empty($email->mail_cc)) { ?>
                                        <div class="col-md-12">
                                            <div class="col-md-2"><lable for="control-label">CC</lable></div>
                                            <div class="col-md-10 pr-0">
                                                :<?php  echo $format_email->cc; ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php echo $format_email->mail_body; ?>
                </div>
            </div>
        </div>
    <?php } ?>
<?php } else { ?>
    <div class="alert alert-danger"> No Email Found!</div>
<?php } ?>


<script>
    $(document).ready(function () {
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
    })
</script>