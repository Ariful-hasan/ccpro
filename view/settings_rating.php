<?php if (!empty($errMsg)):?><div class="error-msg"><?php echo $errMsg;?></div><?php endif;?>

<form name="frm_rating" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=rating");?>">
<table class="form_table" border="0" width="450" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=5>Point Earning Rules</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" width="40%">Work time: </td>
        <td width="10%" style="text-align:right;">min</td>
        <td width="10%">
        	<input type="text" id="working_minute" name="working_minute" size="4" maxlength="2" value="<?php echo $working_minute;?>">
       	</td>
        <td width="10%" style="text-align:right;">point</td>
        <td width="30%">
        	<input type="text" id="working_point" name="working_point" size="4" maxlength="2" value="<?php echo $working_point;?>">
       	</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Talk time: </td>
        <td style="text-align:right;">min</td>
        <td>
        	<input type="text" id="talk_minute" name="talk_minute" size="4" maxlength="2" value="<?php echo $talk_minute;?>">
        </td>
        <td style="text-align:right;">point</td>
        <td>
        	<input type="text" id="talk_point" name="talk_point" size="4" maxlength="2" value="<?php echo $talk_point;?>">
       	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">No. of calls taken: </td>
        <td style="text-align:right;">no.</td>
        <td>
        	<input type="text" id="no_of_calls" name="no_of_calls" size="4" maxlength="2" value="<?php echo $no_of_calls;?>">
      	</td>
        <td style="text-align:right;">point</td>
        <td>
        	<input type="text" id="no_of_calls_point" name="no_of_calls_point" size="4" maxlength="2" value="<?php echo $no_of_calls_point;?>">
     	</td>
	</tr>
	<tr class="form_row_head">
	  <td colspan=5>Point Deducting Rules</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Alarm count: </td>
        <td style="text-align:right;">no.</td>
        <td>
        	<input type="text" id="alarm_count" name="alarm_count" size="4" maxlength="2" value="<?php echo $alarm_count;?>">
       	</td>
        <td style="text-align:right;">point</td>
        <td>
        	<input type="text" id="alarm_count_point" name="alarm_count_point" size="4" maxlength="2" value="<?php echo $alarm_count_point;?>">
      	</td>
	</tr>
	<tr class="form_row" height="40">
		<td colspan="5" class="form_column_submit">
			<input class="form_submit_button" type="submit" value=" Save Setting " name="submitrating"> <br>
		</td>
	</tr>
</tbody>
</table>
</form>
