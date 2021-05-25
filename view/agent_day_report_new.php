<link rel="stylesheet" type="text/css" media="screen" href="css/form.css">
<script src="js/date.js" type="text/javascript"></script>
<style>
    .tdw{width: 235px !important;}
    .text-center{text-align: center}
    .text-danger{color: red}
</style>
<?php
    $today = $sdate === date("d/m/Y");
    $positive_ice_count = !empty($ice_feedback->positive_ice) ? $ice_feedback->positive_ice : 0;
    $negative_ice_count = !empty($ice_feedback->negative_ice) ? $ice_feedback->negative_ice : 0;
?>

<form name="frm_search" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>">
    <table class="form_table">
        <tr class="form_row">
            <td>
                Date:
                <input type="text" name="sdate" id="sdate" value="<?php echo $sdate;?>" class="date-pick" size="12" maxlength="10" />
                &nbsp;
                <input class="form_submit_button" type="submit" name="search" value="Search" />
            </td>
        </tr>
    </table>
</form>

<div class="panel panel-default">
    <div class="panel-box-header">Information</div>
    <div class="panel-body">
        <?php if(!empty($agent)) : ?>
            <div class="row">
            <div class="<?php echo $today ? 'col-md-12' : 'col-md-6' ?>">
                <table class="form_table table table">
                    <tbody>
                    <tr class="form_row_head">
                        <td colspan="2">Shift </td>
                    </tr>
                    <tr class="form_row_alt">
                        <td class="form_column_caption tdw">Shift Name</td>
                        <td>&nbsp;: <?php echo !empty($agent->shift_name) ? $agent->shift_name:"" ?></td>
                    </tr>
                    <tr class="form_row_alt">
                        <td class="form_column_caption tdw">Start Time </td>
                        <td>&nbsp;: <?php echo !empty($agent->shift_start) ? $agent->shift_start:"" ?></td>
                    </tr>
                    <tr class="form_row_alt">
                        <td class="form_column_caption tdw">End Time </td>
                        <td>&nbsp;: <?php echo !empty($agent->shift_end) ? $agent->shift_end:"" ?></td>
                    </tr>
                    <tr class="form_row_alt">
                        <td class="form_column_caption tdw">Shift Time </td>
                        <td>&nbsp;: <?php echo !empty($agent->shift_time) ? gmdate('H:i:s', $agent->shift_time):"" ?></td>
                    </tr>
                    <tr class="form_row_alt">
                        <td class="form_column_caption tdw">Login </td>
                        <td>&nbsp;: <?php echo !empty($agent->first_login) ? $agent->first_login:"" ?></td>
                    </tr>
                    <tr class="form_row_alt">
                        <td class="form_column_caption tdw">Logout </td>
                        <td>&nbsp;: <?php echo !empty($agent->last_logout) ? $agent->last_logout:"Not yet logged out" ?></td>
                    </tr>
                    <tr class="form_row_alt">
                        <td class="form_column_caption tdw"> Staff Time </td>
<!--                        <td>&nbsp;: --><?php //echo !empty($agent->session_id) && $current_session_id != $agent->session_id ? gmdate('H:i:s', $agent->staff_time) :"00:00:00" ?><!--</td>-->
                        <td> : <?php echo !empty($agent->session_id) && $current_session_id != $agent->session_id && !empty($agent_session_info->staffed_time) ? gmdate('H:i:s', $agent_session_info->staffed_time) :"00:00:00" ?></td>
                    </tr>
                    <tr class="form_row_alt">
                        <td class="form_column_caption tdw">Regular Shift </td>
                        <td>&nbsp;: <?php echo !empty($agent->is_regular_shift) ? $agent->is_regular_shift=='Y' ?"Yes":"No" : "" ?></td>
                    </tr>
                    <tr class="form_row_alt">
                        <td class="form_column_caption tdw">Late Login </td>
                        <td>&nbsp;: <?php echo !empty($agent->late_login) ? $agent->late_login=='Y'?"Yes":"No" : ""?></td>
                    </tr>
                    <tr class="form_row_alt">
                        <td class="form_column_caption tdw">Early Logout </td>
                        <td>&nbsp;: <?php echo !empty($agent->early_logout) ? $agent->early_logout=='Y'?"Yes":"No" : "" ?></td>
                    </tr>
                    <tr class="form_row_alt">
                        <td class="form_column_caption tdw">Session Count </td>
                        <td>&nbsp;: <?php echo !empty($agent->login_count) ? $agent->login_count: 0 ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <?php if(!$today) : ?>
            <div class="col-md-6">
                <table class="form_table table table" >
                    <tbody>
                        <tr class="form_row_head">
                            <td colspan="2">Call</td>
                        </tr>
                        <!--<tr class="form_row_alt">
                            <td class="form_column_caption tdw">Call Offered </td>
                            <td>&nbsp;: <?php /*echo !empty($agent->fcr_call_count) ? $agent->fcr_call_count:"" */?></td>
                        </tr>-->
                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw">Call Answered </td>
                            <td>&nbsp;: <?php echo !empty($agent->calls_in_ans) ? $agent->calls_in_ans: 0 ?></td>
                        </tr>
                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw">Longest Outobund Call Time  </td>
                            <td>&nbsp;: <?php echo !empty($agent->longest_ob_call_time) ? gmdate('H:i:s', $agent->longest_ob_call_time) : "00:00:00" ?></td>
                        </tr>
                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw"> Shortest Outobund Call Time </td>
                            <td>&nbsp;: <?php echo !empty($agent->shortest_ob_call_time) ? gmdate('H:i:s', $agent->shortest_ob_call_time):"00:00:00" ?></td>
                        </tr>
                        <!--<tr class="form_row_alt">
                            <td class="form_column_caption tdw">Ringing Less Than 6 Times </td>
                            <td>&nbsp;: <?php /*echo !empty($agent->ring_lt_6_time) ? $agent->ring_lt_6_time:"" */?></td>
                        </tr>
                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw">Ringing Greater Than 6 Times  </td>
                            <td>&nbsp;: <?php /*echo !empty($agent->ring_gt_6_time) ? $agent->ring_gt_6_time:"" */?></td>
                        </tr>-->
                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw"> Repeat Call  </td>
                            <td>&nbsp;: <?php echo !empty($agent->repeat_call_count) ? $agent->repeat_call_count : 0 ?></td>
                        </tr>
                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw"> Short Call </td>
                            <td>&nbsp;: <?php echo !empty($agent->short_call_count) ? $agent->short_call_count : 0 ?></td>
                        </tr>
                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw"> Outbound call Attempt </td>
                            <td>&nbsp;: <?php echo !empty($agent->calls_out_attempt) ? $agent->calls_out_attempt : 0 ?></td>
                        </tr>
                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw"> Outbound call Reached </td>
                            <td>&nbsp;: <?php echo !empty($agent->calls_out_reached) ? $agent->calls_out_reached : 0 ?></td>
                        </tr>
                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw"> Repeat Outbound Call  </td>
                            <td>&nbsp;: <?php echo !empty($agent->repeated_ob_call_count) ? $agent->repeated_ob_call_count : 0 ?></td>
                        </tr>
                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw"> Drop ACD Call  </td>
                            <td>&nbsp;: <?php echo !empty($agent->drop_acd_calls) ? $agent->drop_acd_calls : 0 ?></td>
                        </tr>
                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw">ICE Score</td>
                            <td>: <?php echo !empty($positive_ice_percentage) ? $positive_ice_percentage."% ($positive_ice_count positive, $negative_ice_count negative)" : "0%" ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-12">
                <table class="form_table table table" style="margin-top: 2%;">
                    <tbody>
                        <tr class="form_row_head">
                            <td colspan="4" style="text-align: center">General</td>
                        </tr>
                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw">Available Time </td>
                            <td>&nbsp;:
                                <?php echo $agent_session_info->login_duration > $agent_session_info->not_ready_time ? gmdate("H:i:s", $agent_session_info->login_duration - $agent_session_info->not_ready_time) : "00:00:00" ?>
                            </td>

                            <td class="form_column_caption tdw">Queue Hold Time </td>
                            <td>&nbsp;: <?php echo !empty($agent->hold_time_in_queue) ? gmdate('H:i:s',$agent->hold_time_in_queue ) :"00:00:00" ?></td>
                        </tr>
                        <!--<tr class="form_row_alt">
                            <td class="form_column_caption tdw">Queue Hold Time </td>
                            <td>&nbsp;: <?php /*echo !empty($agent->hold_time_in_queue) ? date('H:s',$agent->hold_time_in_queue ) :"" */?></td>
                        </tr>-->
                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw">Hold Time </td>
                            <td>&nbsp;: <?php echo !empty($agent->hold_time) ? gmdate('H:i:s', $agent->hold_time) : "00:00:00" ?></td>

                            <td class="form_column_caption tdw">Hold call </td>
                            <td>&nbsp;: <?php echo !empty($agent->hold_count) ?  $agent->hold_count : 0 ?></td>
                        </tr>
                        <!--<tr class="form_row_alt">
                            <td class="form_column_caption tdw">Hold call </td>
                            <td>&nbsp;: <?php /*echo !empty($agent->hold_count) ?  $agent->hold_count : "" */?></td>
                        </tr>-->
                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw"> Calls in Time </td>
                            <td>&nbsp;: <?php echo !empty($agent->calls_in_time) ? gmdate('H:i:s', $agent->calls_in_time) : "00:00:00" ?></td>

                            <td class="form_column_caption tdw"> Calls out Time </td>
                            <td>&nbsp;: <?php echo !empty($agent->calls_out_time) ? gmdate('H:i:s', $agent->calls_out_time) :"00:00:00" ?></td>
                        </tr>
                        <!--<tr class="form_row_alt">
                            <td class="form_column_caption tdw"> Calls out Time </td>
                            <td>&nbsp;: <?php /*echo !empty($agent->calls_out_time) ? date('H:s', $agent->calls_out_time) :"" */?></td>
                        </tr>-->
                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw"> Ring in Time </td>
                            <td>&nbsp;: <?php echo !empty($agent->ring_in_time) ? gmdate('H:i:s', $agent->ring_in_time ) :"00:00:00" ?></td>

                            <td class="form_column_caption tdw"> Ring out Time </td>
                            <td>&nbsp;: <?php echo !empty($agent->ring_out_time) ? gmdate('H:i:s', $agent->ring_out_time) : "00:00:00" ?></td>
                        </tr>
                        <!--<tr class="form_row_alt">
                            <td class="form_column_caption tdw"> Ring out Time </td>
                            <td>&nbsp;: <?php /*echo !empty($agent->ring_out_time) ? date('H:s', $agent->ring_out_time) : "" */?></td>
                        </tr>-->
                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw">AHT </td>
                            <td>&nbsp;: <?php
                                if (!empty($agent)){
                                    $aht  = $agent->calls_in_ans > 0 ? ceil(($agent->ring_in_time +$agent->calls_in_time + $agent->hold_time + $agent->wrap_up_time) /$agent->calls_in_ans) : ($agent->ring_in_time + $agent->calls_in_time + $agent->hold_time + $agent->wrap_up_time);
                                    echo !empty($aht) ?  $aht : '00:00:00';
                                }
                                else echo "00:00:00";
                                ?>
                            </td>

                            <td class="form_column_caption tdw">Not Ready Time </td>
                            <td>&nbsp;: <?php
//                                if (!empty($agent)){
//                                    $nrt = $agent->total_aux_in_time + $agent->total_aux_out_time + $agent->total_break_time;
//                                    echo !empty($nrt) ? gmdate('H:i:s',$nrt) : '00:00:00';
//                                }
//                                else echo "";
                                echo !empty($agent_session_info->not_ready_time) ? gmdate('H:i:s', $agent_session_info->not_ready_time) : "00:00:00";
                                ?>
                            </td>
                        </tr>

                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw"> Wrapup Time </td>
                            <td>&nbsp;: <?php echo !empty($agent->wrap_up_time) ? gmdate('H:i:s', $agent->wrap_up_time) :"00:00:00" ?></td>

                            <td class="form_column_caption tdw"> Total Wrapup </td>
                            <td>&nbsp;: <?php echo !empty($agent->wrap_up_count) ?  $agent->wrap_up_count : 0 ?></td>
                        </tr>

                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw"> Staff Time </td>
                            <!--                            <td>&nbsp;: --><?php //echo !empty($agent->session_id) && $current_session_id != $agent->session_id ? gmdate('H:i:s', $agent->staff_time) :"00:00:00" ?><!--</td>-->
                            <td>: <?php echo !empty($agent->session_id) && $current_session_id != $agent->session_id && !empty($agent_session_info->login_duration) ? gmdate('H:i:s', $agent_session_info->login_duration) :"00:00:00" ?></td>

                            <td class="form_column_caption tdw">Logout By </td>
                            <td>&nbsp;: <?php echo !empty($agent->logout_by) ?  $agent->logout_by=='A'?'Agent':'Supervisor' :"" ?></td>
                        </tr>

                        <tr class="form_row_alt">
                            <td class="form_column_caption tdw"> Break Time </td>
                            <td>&nbsp;: <?php echo !empty($agent->total_break_time) ? gmdate('H:i:s', $agent->total_break_time) :"00:00:00" ?></td>

<!--                            <td class="form_column_caption tdw"> AUX Time </td>-->
<!--                            <td>&nbsp;:-->
<!--                                --><?php //if (!empty($agent)){
//                                    $aux = $agent->total_aux_in_time + $agent->total_aux_out_time ;
//                                    echo !empty($aux) ? gmdate('H:i:s',$aux) : '00:00:00';
//                                }
//                                else echo "00:00:00";
//                                ?>
<!--                            </td>-->
                            <td ></td>
                            <td ></td>
                        </tr>


                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="row">
            <h2 class="text-danger text-center">No record found!</h2>
        </div>
        <?php endif; ?>
    </div>
</div>



<script type="text/javascript">Date.format = 'yyyy-mm-dd';
    $(function()
    {
        $('#sdate').bind(
            'dpClosed',
            function(e, selectedDates)
            {
                var d = selectedDates[0];
                if (d) {
                    d = new Date(d);
                    $('#edate').dpSetStartDate(d.addDays(0).asString());
                }
            }
        );
    });
</script>