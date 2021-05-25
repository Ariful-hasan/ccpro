<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
    <link href="css/form.css" rel="stylesheet" type="text/css">

    <link rel="stylesheet" type="text/css" media="screen" href="css/datePicker.css">
    <script src="js/date.js" type="text/javascript"></script>
    <script src="js/jquery.datePicker.js" type="text/javascript"></script>
    <?php $isUpdate?>


    <?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

        <form name="frm_shift_profile" id="frm_shift_profile" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName()."&scode={$request->getRequest('scode')}&doverlap={$request->getRequest('doverlap')}");?>">

            <table class="form_table table">
                <tbody>
                <tr class="form_row_head">
                    <td colspan=3>Shift Profile Information</td>
                </tr>

                <?php if (!$isUpdate): ?>
                    <tr class="form_row_alt">
                        <td class="form_column_caption">Shift Code:</td>
                        <td>
                            <input type="text"  maxlength="4" id="shift_code" name="shift_code" value="<?php echo $shift_profile->shift_code; ?>">
                        </td>
                    </tr>
                <?php endif; ?>

                <tr class="form_row">
                    <td class="form_column_caption">Lable:</td>
                    <td>
                        <input type="text" maxlength="20" id="label" name="label" value="<?php echo $shift_profile->label; ?>">
                    </td>
                </tr>

                <tr class="form_row">
                    <td class="form_column_caption">Start Time:</td>
                    <td>
                        <input class="only-time" maxlength="5" type="text" id="start_time" name="start_time" value="<?php echo $shift_profile->start_time; ?>">
                    </td>
                </tr>

                <tr class="form_row_alt">
                    <td class="form_column_caption">End Time:</td>
                    <td>
                        <input class="only-time" maxlength="5" type="text" id="end_time" name="end_time" value="<?php echo $shift_profile->end_time; ?>">
                    </td>
                </tr>

                <tr class="form_row_alt">
                    <td class="form_column_caption">Allowed Early login time:</td>
                    <td>
                        <input class="only-time" type="text" id="early_login_cutoff_time" name="early_login_cutoff_time" value="<?php echo $shift_profile->early_login_cutoff_time; ?>">
                    </td>
                </tr>

                <tr class="form_row_alt">
                    <td class="form_column_caption">Allowed Late login time:</td>
                    <td>
                        <input class="only-time" type="text" id="late_login_cutoff_time" name="late_login_cutoff_time" value="<?php echo $shift_profile->late_login_cutoff_time; ?>">
                    </td>
                </tr>

                <tr class="form_row_alt">
                    <td class="form_column_caption">Late Login Mark (Seconds):</td>
                    <td>
                        <input type="text" id="tardy_cutoff_sec" name="tardy_cutoff_sec" value="<?php echo $shift_profile->tardy_cutoff_sec; ?>">
                    </td>
                </tr>

                <tr class="form_row_alt">
                    <td class="form_column_caption">Early Logout Mark (Seconds):</td>
                    <td>
                        <input type="text" id="early_leave_cutoff_sec" name="early_leave_cutoff_sec" value="<?php echo $shift_profile->early_leave_cutoff_sec; ?>">
                    </td>
                </tr>
                <?php /*if (!$isUpdate): */?><!--
            <tr class="form_row_alt">
                <td class="form_column_caption">Day Overlap:</td>
                <td>
                    <select  name="day_overlap" maxlength="1">
                        <option <?php /*echo $shift_profile->day_overlap == "1" ?' selected="selected"':"";*/?> value="1">True</option>
                        <option <?php /*echo $shift_profile->day_overlap == "0" ?' selected="selected"':"";*/?> value="0">False</option>
                    </select>
                </td>
            </tr>
            --><?php /*endif; */?>
                <tr class="form_row">
                    <td colspan="2" class="form_column_submit">
                        <input class="form_submit_button btn btn-success" type="submit" value="  <?php if (!empty($isUpdate)):?>Update<?php else:?>Add<?php endif;?>  " name="submitagent"> <br><br>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>

        <script>
            $(document).ready(function () {
                $(".only-time").datetimepicker({
                    datepicker:false,
                    format: "H:i",
                });
            });
            /*$(function () {
             $(document).on('change',"#shift_code",function () {
             var start_time = $(this).find(':selected').data('start-time');
             $("#shift_start").empty();
             $("#shift_start").append("<option value='"+ start_time +"'>"+ start_time +"</option><option value='N'>Now</option>");
             });
             });*/
        </script>

