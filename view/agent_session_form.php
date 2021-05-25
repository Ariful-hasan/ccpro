<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<link href="css/form.css" rel="stylesheet" type="text/css">

<link rel="stylesheet" type="text/css" media="screen" href="css/datePicker.css">
<script src="js/date.js" type="text/javascript"></script>
<script src="js/jquery.datePicker.js" type="text/javascript"></script>



<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_agent" id="frm_agent" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>" enctype="multipart/form-data">

<table class="form_table table">
<tbody>
	<tr class="form_row_head">
	  <td colspan=3>Agent Information</td>
	</tr>


	<tr class="form_row_alt">
		<td class="form_column_caption">Agent ID:</td>
		<td>
			<input type="text" name="agent_id" size="30" maxlength="4" value="<?php echo $agent_session->agent_id;?>" />
		</td>
	</tr>

	
   <tr class="form_row">
		<td class="form_column_caption">Shift:</td>
		<td>
			<select id="shift_code" name="shift_code">
                <option  value="">--Select--</option>
                <?php foreach ($shifts as $shift): ?>
                    <option <?php echo $agent_session->shift_code == $shift->shift_code ?' selected="selected"':"";?>
                            value="<?php echo $shift->shift_code; ?>" data-start-time="<?php echo $shift->start_time; ?>"><?php echo $shift->label ?></option>
                <?php endforeach; ?>
			</select> 
		</td>
	</tr>

   <tr class="form_row">
		<td class="form_column_caption">Shift Type:</td>
		<td>
			<select  name="is_regular_shift" >
                <option <?php echo $agent_session->is_regular_shift == "Y" ?' selected="selected"':"";?> value="Y">Regular</option>
                <option <?php echo $agent_session->is_regular_shift == "N" ?' selected="selected"':"";?> value="N">Irregular</option>
            </select>
		</td>
   </tr>

    <tr class="form_row_alt">
        <td class="form_column_caption">Date:</td>
        <td>
            <input type="text" id="sdate" class="gs-date-picker" name="sdate" value="<?php echo $agent_session->sdate; ?>">
        </td>
    </tr>

    <tr class="form_row_alt">
        <td class="form_column_caption">Start Time:</td>
        <td>
            <select  id="shift_start" name="shift_start" >
                <option value="<?php echo $agent_session->shift_start?>"><?php echo $agent_session->shift_start == "N" ? 'Now' : $agent_session->shift_start;  ?></option>
            </select>
        </td>
    </tr>

	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button btn btn-success pull-right" type="submit" value="  <?php if (!empty($agentid)):?>Update<?php else:?>Add<?php endif;?>  " name="submitagent"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>

    <script>
        $(function () {
            $(document).on('change',"#shift_code",function () {
                var start_time = $(this).find(':selected').data('start-time');
                $("#shift_start").empty();
                $("#shift_start").append("<option value='"+ start_time +"'>"+ start_time +"</option><option value='N'>Now</option>");
            });
        });
    </script>

