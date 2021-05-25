<?php
    $positive_ice_count = !empty($ice_feedback->positive_ice) ? $ice_feedback->positive_ice : 0;
    $negative_ice_count = !empty($ice_feedback->negative_ice) ? $ice_feedback->negative_ice : 0;
?>

<link rel="stylesheet" type="text/css" media="screen" href="css/form.css">
<style>
    .tdw{
        width:250px;
    }
</style>
<div class="panel panel-default">
    <div class="panel-body">
        <table class="form_table table table">
            <tbody>
                <tr class="form_row_head">
                    <td colspan="2"> Information </td>
                </tr>
                <tr class="form_row_alt">
                    <td class="form_column_caption tdw">Shift </td>
                    <td>&nbsp;: <?php echo !empty($agent->shift_name) ? $agent->shift_name:"" ?></td>
                </tr>
                <tr class="form_row_alt">
                    <td class="form_column_caption tdw">First Login </td>
                    <td>&nbsp;: <?php echo !empty($agent->first_login) ? date(REPORT_DATE_FORMAT." h:i:s A", strtotime($agent->first_login)) : "Not yet logged in" ?></td>
                </tr>
                <tr class="form_row_alt">
                    <td class="form_column_caption tdw">Session Count </td>
                    <td>&nbsp;: <?php echo !empty($agent->login_count) ? $agent->login_count : 0 ?></td>
                </tr>
                <tr class="form_row_alt">
                    <td class="form_column_caption tdw">Calls Answered </td>
                    <td>&nbsp;: <?php echo !empty($agent->calls_in_ans) ? $agent->calls_in_ans : 0 ?></td>
                </tr>
                <tr class="form_row_alt">
                    <td class="form_column_caption tdw">AHT </td>
                    <td>&nbsp;: <?php echo $agent->calls_in_ans > 0 ? ceil(($agent->ring_in_time + $agent->calls_in_time +  $agent->wrap_up_time) /$agent->calls_in_ans) : ($agent->ring_in_time + $agent->calls_in_time +  $agent->wrap_up_time) ?></td>
                </tr>
                <tr class="form_row_alt">
                    <td class="form_column_caption tdw">Staff Time   </td>
                    <td>: &nbsp;<?php echo !empty($agent->staff_time) ? gmdate('H:i:s', $agent->staff_time) : "00:00:00"  ?></td>
                </tr>
                <tr class="form_row_alt">
                    <td class="form_column_caption tdw">Not Ready Time </td>
                    <td>&nbsp;:
                        <?php
                            if (!empty($agent)){
                                $nrt = $agent->total_aux_in_time + $agent->total_aux_out_time + $agent->total_break_time;
                                echo !empty($nrt) ? gmdate('H:i:s', $nrt) : "00:00:00";
                            }
                        ?>
                    </td>
                </tr>
                <tr class="form_row_alt">
                    <td class="form_column_caption tdw">ICE Score</td>
                    <td>: <?php echo !empty($positive_ice_percentage) ? $positive_ice_percentage."% ($positive_ice_count positive, $negative_ice_count negative)"  : "0%" ?></td>
                </tr>

            </tbody>
        </table>
        <p class="help-block">
            <?php if(DAY_REPORT_FORMULA=='Y'){ ?>
                AHT = ( Ring Time + Talk Time + Wrapup Time ) / Total Answered Calls.<br>
                Not Ready Time = Total AUX Time + Total Break Time.
            <?php } ?>
        </p>
    </div>
</div>
