<link rel="stylesheet" type="text/css" media="screen" href="css/form.css">
<link rel="stylesheet" type="text/css" media="screen" href="css/report.css">

<script type="text/javascript">
    jQuery(document).ready(function($) {
        $(".agent").colorbox({
            iframe:true, width:"900", height:"520"
        });
        $(".smstplate").colorbox({
            iframe:true, width:"800", height:"450"
        });
        $(".dl-voice").colorbox({
            iframe:true, width:"700", height:"320"
        });
    });
</script>

<?php
/*
{include file="mypopup.html"}
{include file="alert_confirm.html"}
{include file="header_without_navigation.html"}
*/
?>
<script type="text/javascript">
    var winHandle;
    function requestNewWindow( str_URL, width, height )
    {
        if(winHandle)	winHandle.close();
        var winl = (screen.width - width) / 2;
        var wint = (screen.height - height) / 2;
        winHandle = window.open( str_URL, 'str_winName1','toolbar=no,menubar=no,left='+winl+',top='+wint+',status=no,location=no,scrollbars=1,resizable=no,width='+width+',height='+height);
        winHandle.focus();
    }

    function playFile2(evt)
    {
        $.post('<?php echo $this->url('task='.$request->getControllerName()."&act=listen"); ?>', { 'page':'CDR', 'agent_id':luser, 'option':evt, 'cdr_page':pageTitle },
            function(data, status) {
                if (status == 'success') {
                    if(data != 'Y') alert(data);
                } else {
                    alert("Failed to communicate!");
                }
            });
    }

    function submitDetailsForm() {
        $("#frm_search").submit();
    }

    function resetDetailsForm() {
        $('#frm_search').trigger("reset");
    }
    
    $(function() {
        try{
            $(".gs-datetime-from-picker-grid-options input").each(function(e){
                if(!$(this).hasClass("addedDate")){
                    $(this).addClass("addedDate");
                    $(this).datetimepicker({
                        pickTime: true,
                        timepicker:true,
                        useStrict:true,
                        defaultTime:'00:00',
                        format:"Y-m-d H:00"
                        //,allowTimes:['00:00', '01:00', '02:00','03:00', '04:00', '05:00', '06:00', '07:00','08:00', '09:00', '10:00','11:00', '12:00', '13:00', '14:00', '15:00','16:00', '17:00','18:00', '19:00', '20:00', '22:00', '23:00']
                    });
                }
            });
            $(".gs-datetime-to-picker-grid-options input").each(function(e){
                if(!$(this).hasClass("addedDate")){
                    $(this).addClass("addedDate");
                    $(this).datetimepicker({
                        pickTime: true,
                        timepicker:true,
                        useStrict:true,
                        defaultTime:'23:00',
                        format:"Y-m-d H:59",
                        allowTimes:['00:59', '01:59', '02:59','03:59', '04:59', '05:59', '06:59', '07:59','08:59', '09:59', '10:59','11:59', '12:59', '13:59', '14:59', '15:59','16:59', '17:59','18:59', '19:59', '20:59', '22:59', '23:59']
                    });
                }
            });
        }catch(e){gsl(e.message);}

        $('#cidSelectorAll').click(function () {
            if ($(this).is(":checked")){
                $('.cid-selector-chk').prop('checked', true);
            }else {
                $('.cid-selector-chk').prop('checked', false);
            }
        });

        $('#send_sms_btn').click(function(){
            if($(".report_table input[name='cid_selector[]']:checked").length) {
                $("#template_chose").click();
            }else {
                alert('Please select Caller ID');
            }
        });
    });

</script>
<style type="text/css">
    select {
        width:auto;
    }
    .report_table {
        border: 1px solid #eee;
    }
    .report-table td{
        white-space:nowrap;
    }
    .report_table, .report_extra_info {
        font-size: 12px;
        width: 100%;
        margin-bottom: 5px;
    }
    .alert{
        padding: 6px 15px;
        border-radius: 3px;
    }
    .report_table tr.report_row_head td {
        background-image: -webkit-linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
        background-image: -moz-linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
        background-image: -o-linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
        background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#fff), color-stop(0.25, #fff), to(#e6e6e6)) !important;
        background-image: linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
        color: #333333;
        box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        -webkit-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        -moz-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        border-bottom: 1px solid #ccc !important;
        border-top: 0 none;
        padding: 0.6em 0.5em;
        vertical-align: middle;
    }
    .table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
        padding: 5px;
    }
</style>

<?php
if ($smsCounter != "empty"){
    if (!empty($smsCounter)){
        echo '<div class="alert alert-success">SMS send successfully to '.$smsCounter.' mobile numbers</div>';
    }else{
        echo '<div class="alert alert-error">Failed to send SMS</div>';
    }
}
?>
<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
    <form id="frm_search" name="frm_search" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>">
    <div class="gs-grid-serach row form-horizontal" id="src_tab0_1490780424" style="padding: 5px;">
        <div class="col-xs-12 ">
            <div class="panel panel-default">
                <div class="src-heading panel-heading">Search
                    <a class="pull-right btn btn-xs btn-warning" onclick="javascript:submitDetailsForm();" style="margin-left: 10px;"><i class="fa fa-search"> </i> Search</a>
                    <a class="pull-right btn btn-xs btn-danger" onclick="javascript:resetDetailsForm();"><i class="fa fa-times"> </i> Reset Search</a>
                </div>

                <div class="panel-body p-l-zero p-r-zero">
                        <div class="col-md-6 form form-horizontal">
                            <div class="form-group ">
                                <label class="col-sm-2 col-md-4 control-label" for="name">Skill</label>
                                <div class="col-sm-10 col-md-8">
                                    <select class="form-control input-sm" id="skillid" name="skillid">
                                        <option value="">Select</option>
                                        <?php
                                        if (is_array($skill_options)) {
                                            foreach ($skill_options as $sid => $sname) {
                                                if (ctype_alpha($sid)) {
                                                    echo '<option value="'.$sid.'"';
                                                    if ($skillid == $sid) echo ' selected';
                                                    echo '>'.$sname.'</option>';
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label class="col-sm-2 col-md-4 control-label" for="name">Start time</label>
                                <div class="col-sm-5 col-md-4">
                                    <div class="input-group datetime gs-datetime-from-picker-grid-options">
                                    <input class="srcTextValue form-control" name="start_time" value="<?php echo $dateinfo->sdate;?>" placeholder="From" type="text">
                                    <span class="input-group-addon" style="height: 24px !important; padding:2px 2px 0px 1px; line-height: 4px !important; min-width: 32px;"><span style="font-size: 12px !important;" class="fa  fa-clock-o "></span></span>
                                </div>
                            </div>
                            <div class="col-sm-5 col-md-4">
                                <div class="input-group datetime gs-datetime-to-picker-grid-options">
                                <input class="srcTextValue form-control" name="end_time" value="<?php echo $dateinfo->edate;?>" placeholder="To" type="text">
                                <span class="input-group-addon" style="height: 24px !important; padding:2px 2px 0px 1px; line-height: 4px !important; min-width: 32px;"><span style="font-size: 12px !important;" class="fa  fa-clock-o "></span></span>
                            </div>
                        </div>
                </div>
                <div class="form-group ">
                    <label class="col-sm-2 col-md-4 control-label" for="name">Agent ID</label>
                    <div class="col-sm-10 col-md-8">
                        <input value="<?php echo $aid;?>" class="form-control input-sm" id="aid" name="aid" placeholder="Agent ID" type="text">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group ">
                    <label class="col-sm-2 col-md-4 control-label" for="name">Caller ID</label>
                    <div class="col-sm-10 col-md-8">
                        <input value="<?php echo $cli;?>" class="form-control input-sm" id="cli" name="cli" placeholder="Caller ID" type="text">
                    </div>
                </div>
                <div class="form-group ">
                    <label class="col-sm-2 col-md-4 control-label" for="name">DID</label>
                    <div class="col-sm-10 col-md-8">

                        <input value="<?php echo $did;?>" class="form-control input-sm" id="did" name="did" placeholder="DID" type="text">
                    </div>
                </div>
                <div class="form-group ">
                    <label class="col-sm-2 col-md-4 control-label" for="name">Call ID</label>
                    <div class="col-sm-10 col-md-8">
                        <input value="<?php echo $callid;?>" class="form-control input-sm" id="callid" name="callid]" placeholder="Call ID" type="text">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-xs-12 ">
    <?php
    $colspan = 13;
    ?>
    <?php if (is_array($calls)):?>
    <!-- <form id="frm_sms_data" name="frm_sms_data" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>"> -->
        <table class="report_extra_info">
            <tr>
                <td width="60%" align="left" style="text-align: left">
                    <?php
                    echo 'Record(s) ' . $pagination->getCurrentRecordsIndex() . ' of <b>' . $pagination->num_records . '</b> &nbsp;::&nbsp; ' .
                        'Page <b>' . $pagination->current_page . '</b> of <b>' . $pagination->getTotalPageCount() . '</b>';
                    ?>
                </td>
                <td width="40%" align="right" style="text-align: right">
                    <input type="hidden" name="page" value="<?php echo $currentPage; ?>">
                    <input type="hidden" name="smsTextLast" id="smsTextLast" value="">
                    <input type="hidden" name="send_sms_now" id="send_sms_now" value="N">
                    <input id="send_sms_btn" class="btn btn-success btn-sm" type="button" value="Send SMS">
                </td>
            </tr>
        </table>
    <?php else:?>
        <br />
    <?php endif;?>
    <div class="table-responsive">
    <table class="report_table table table-hover" width="98%" border="0" align="center" cellpadding="1" cellspacing="1">
        <tr class="report_row_head">
            <td class="cntr">SL</td>
            <td class="cntr" style="padding: 5px 8px;"><input type="checkbox" name="cidSelectorAll" id="cidSelectorAll"></td>
            <td>Caller ID</td>
            <td class="cntr">Start time</td>
            <td class="cntr">Stop time</td>
            <td>DID</td>
            <td class="cntr">IVR enter time</td>
            <td class="cntr">IVR</td>
            <td class="cntr">Time in IVR</td>
            <td>IVR language</td>
            <td class="cntr">Skill enter time</td>
            <td class="cntr">Skill</td>
            <td class="cntr">Hold in queue</td>
            <td>Agent ID</td>
            <td>Call ID</td>
        </tr>

        <?php if (is_array($calls)):?>

        <?php
        $languages = array('E'=>'ENG', 'B'=>'BAN');
        $i = $pagination->getOffset();
        foreach ($calls as $call):
            $i++;
            $_class = $i%2 == 1 ? 'report_row_alt' : 'report_row';
            $skill_name = isset($skill_options[$call->skill_id]) ? $skill_options[$call->skill_id] : $call->skill_id;
            $ivr_name = isset($ivr_options[$call->ivr_id]) ? $ivr_options[$call->ivr_id] : $call->ivr_id;
            $total_time = 0;
            $ivr_enter_time = empty($call->ivr_enter_time) ? '-' : $call->ivr_enter_time;
            $time_in_ivr = empty($call->time_in_ivr) ? 0 : $call->time_in_ivr;
            $alarm = empty($call->alarm) ? 0 : $call->alarm;
            $ivr_language = empty($call->ivr_language) ? '-' : $call->ivr_language;
            if (isset($languages[$ivr_language])) $ivr_language = $languages[$ivr_language];
            $_cli = empty($call->cli) ? '-' : $call->cli;
            $_did = empty($call->did) ? '-' : $call->did;

            if (empty($call->agent_id)) {
                $agent_id =  '-';
            } else {
                $agent_id = "<a href=\"index.php?task=calldetails&act=agent&cid=$call->callid\" class=\"agent\">$call->agent_id</a>";
            }

            $start_time = empty($call->start_time) ? '-' : $call->start_time;
            $stop_time = empty($call->stop_time) ? $call->cdr_stop_time : $call->stop_time;

            if (empty($call->stop_time)) {
                $stop_time = $call->cdr_stop_time;
                if (empty($stop_time)) {
                    $stop_time = '-';
                } else {
                    $total_time = strtotime($call->cdr_stop_time) - strtotime($call->start_time);
                }
            } else {
                $stop_time = $call->stop_time;
            }

            $disc_cause = empty($call->disc_cause) ? '-' : $call->disc_cause;
            if ($call->disc_party == '1') $disc_party = 'A-Party';
            else if ($call->disc_party == '2') $disc_party = 'B-Party';
            else $disc_party = $call->disc_party;

            if (empty($call->trunkid)) $trunk_name = '-';
            else $trunk_name = isset($trunk_options[$call->trunkid]) ?  $trunk_options[$call->trunkid] : $call->trunkid;

            $total_time = $total_time <= 0 ? $time_in_ivr + $call->hold_in_q + $call->service_time : $total_time;

            if (!empty($call->skill_status)) {
                if ($call->skill_status == 'S') $skill_status = 'Served';
                else if ($call->skill_status == 'A') $skill_status = 'Abandoned';
                else $skill_status = $call->skill_status;
            } else {
                if (empty($call->skill_id)) $skill_status = '-';
                else $skill_status = 'Droped';
            }


            ?>
            <tr class="<?php echo $_class;?>">
                <td class="cntr">&nbsp;<?php echo $i;?></td>
                <td class="cntr" style="padding: 5px 8px;"><input type="checkbox" name="cid_selector[]" class="cid-selector-chk" value="<?php echo $_cli."@".$call->callid;?>"></td>
                <td align="left">&nbsp;<?php echo $_cli;?></td>
                <td class="cntr"><?php echo $start_time;?></td>
                <td class="cntr"><?php echo $stop_time;?></td>
                <td align="left">&nbsp;<?php echo $_did;?></td>
                <td class="cntr"><?php echo $ivr_enter_time;?></td>
                <td class="cntr"><?php echo $ivr_name;?></td>
                <td class="cntr"><?php echo DateHelper::get_formatted_time($time_in_ivr);?></td>
                <td align="left">&nbsp;<?php echo $ivr_language;?></td>
                <td class="cntr"><?php echo $call->skill_enter_time;?></td>
                <td class="cntr"><?php echo $skill_name;?></td>
                <td class="cntr"><?php echo DateHelper::get_formatted_time($call->hold_in_q);?></td>
                <td align="left">&nbsp;<?php echo $agent_id;?></td>
                <td align="left">&nbsp;<?php echo $call->callid;?></td>
            </tr>
        <?php endforeach;?>
    </table>
    <table class="report_extra_info">
        <tr>
            <td>
                <?php echo $pagination->createLinks(); ?>
            </td>
        </tr>
        <?php else:?>
            <tr class="report_row_empty">
                <td colspan="<?php echo $colspan;?>">No Record Found!</td>
            </tr>
        <?php endif;?>
    </table>
    </div>
</div>
</form>
<a id="template_chose" href="index.php?task=cdr&act=smstemplate" class="smstplate" style="display: none;">0</a>