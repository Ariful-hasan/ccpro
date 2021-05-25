<script src="js/jquery.min.js"></script>
<script src="js/datetimepicker/jquery.datetimepicker.js"></script>
<link rel="stylesheet" href="js/datetimepicker/jquery.datetimepicker.css" type="text/css"/>
<script type="text/javascript" src="js/bootstrap-multiselect/js/bootstrap-3.1.1.min.js"></script>
<link rel="stylesheet" href="js/bootstrap-multiselect/css/bootstrap-3.1.1.min.css" type="text/css"/>
<link href="css/form.css" rel="stylesheet" type="text/css">

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_agent" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="tid" value="<?php echo $tid;?>" />
<table class="form_table" style="padding-top: 5%!important;">

    <tr class="form_row_alt" style="margin-top: 10px!important;">
        <td class="form_column_caption" style="text-align:center;"><b>Phone Number:</b>
            <input type="text" name="phone_number" id="phone_number" class="form-control" value=""  style="margin-top: 10px" maxlength="15">
        </td>
    </tr>

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
        })
    </script>
