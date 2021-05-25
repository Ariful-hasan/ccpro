<script src="js/jquery.min.js"></script>
<script src="js/datetimepicker/jquery.datetimepicker.js"></script>
<link rel="stylesheet" href="js/datetimepicker/jquery.datetimepicker.css" type="text/css"/>
<script type="text/javascript" src="js/bootstrap-multiselect/js/bootstrap-3.1.1.min.js"></script>
<link rel="stylesheet" href="js/bootstrap-multiselect/css/bootstrap-3.1.1.min.css" type="text/css"/>
<?php $num_select_box = 6;?>

<link href="css/form.css" rel="stylesheet" type="text/css">

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

    <form name="frm_agent" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
        <input type="hidden" name="tid" value="<?php echo $tid;?>" />
        <input type="hidden" name="sid" value="<?php echo $session_id;?>" />
        <input type="hidden" name="msl" value="<?php echo $mail_sl;?>" />
        <input type="hidden" name="mc" value="<?php echo $session_mail_count;?>" />
        <table class="form_table" style="padding-top: 5%!important;">
            <tr class="form_row_alt" style="margin-top: 10px!important;">
                <td class="form_column_caption" style="text-align:center;"><b>Status:</b>
                    <select name="status" id="status">
                        <option value="">Select</option>
                        <?php if (is_array($statuses)) {
                            foreach ($statuses as $st) {
                                echo '<option value="' . $st . '"';
                                if ($ticket_info->status == $st) echo ' selected';
                                echo '>' . $eTicket_model->getTicketStatusLabel($st) . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <input type="text" name="sdate" id="sdate" class="form-control hide" value="<?php echo !empty($ticket_info->reschedule_time) ? date("d/m/Y H:i", $ticket_info->reschedule_time) : date("d/m/Y H:i") ?>" autocomplete="off" style="margin-top: 10px">
                </td>
            </tr>



            <!--  Disposition START -->
            <tr class="form_row_alt">
                <td class="form_column_caption" style="text-align:center;"><b>Select Disposition:</b>
                </td>
            </tr>
            <tr class="form_row">
                <td class="form_column_caption" style="text-align:center;">
                    <?php
                    for ($i=0; $i<$num_select_box; $i++) {
                        echo '<select name="disposition_id'.$i.'" id="disposition_id'.$i.'" style="display:none;" onchange="reload_sels('.$i.', this.value);"><option value="">Select</option>';
                        if ($i == 0 || isset($disposition_ids[$i])) {
                            if (!isset(${'dispositions'.$i})) {
                                if ($i > 0) {
                                    $dispositions = $eTicket_model->getDispositionChildrenOptions($ticket_info->skill_id, $disposition_ids[$i-1][0]);
                                } else {
                                    $dispositions = $eTicket_model->getDispositionChildrenOptions($ticket_info->skill_id, '');
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
                </td>
            </tr>
            <!--  Disposition END -->

            <tr class="form_row">
                <td class="form_column_submit" style="text-align:center;padding:20px 0;">
                    <!--<input class="form_submit_button" type="submit" value="Save Only" name="submitagent_only" onclick=" linkTo();"/> --> &nbsp; &nbsp;
                    <input class="form_submit_button" type="submit" value="Save" name="submitagent" />  &nbsp; &nbsp;
                    <input class="form_submit_button" type="button" value="Cancel" name="submitcancel" onclick="parent.$.colorbox.close();" />

                </td>
            </tr>
        </table>
    </form>

    <script>
        function linkTo() {
//            window.top.location.href = "<?php //echo $this->url('task='.$request->getControllerName().'&callid='.$callid);?>//";
        }
        function show_reschedule_date() {
            $("#status").on('change', function (e) {
                var status_val = $(this).val();
                if (status_val == 'R'){
                    $("#sdate").removeClass('hide');
                    $("#sdate").addClass('show');
                } else {
                    $("#sdate").removeClass('show');
                    $("#sdate").addClass('hide');
                }
            });
            $("#status").change();
        }
        $(document).ready(function () {
            $("#sdate").datetimepicker({
                defaultDate: new Date(),
//                format:"<?php //echo REPORT_DATE_FORMAT; ?>// H:00"
                format:"d/m/Y H:i"
            });
            show_reschedule_date();

            load_sels();  //// Disposition
        });




        /*  Disposition START*/
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
        function reload_sels(i, val) {
            if (i < <?php echo $num_select_box;?>) {
                hide_sels(i);
                var j = i+1;
                $select = $("#disposition_id" + j);
                $select.html('<option value="">Select</option>');
                if (val.length > 0) {
                    var jqxhr = $.ajax({
                        type: "POST",
                        url: "<?php echo $this->url("task=" . $request->getControllerName() . '&act=dispositionchildren');?>",
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
        /*  Disposition END*/

    </script>
