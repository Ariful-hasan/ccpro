<link rel="stylesheet" href="css/form.css" type="text/css">
<script>
$(function(e){
var rad = document.frm_seat.forward_rule;
var prev = null;
for(var i = 0; i < rad.length; i++) {
    rad[i].onclick = function() {
        //(prev)? console.log(prev.value):null;
        if(this !== prev) {
            prev = this;
        }
        //console.log(this.value)
        if (this.value.length == '') $("#div_fwd_number").hide();
        else $("#div_fwd_number").show();
    };
}
$('#did').change(function (){
        //if (thi) {
        //}
        if (this.value.length > 0) {
                $(".div_did_ch").show();
        } else {
                $(".div_did_ch").hide();
                $("#forward_rule").attr('checked', true);
        }
});
});
</script>
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_seat" action="<?php echo $this->url('task='.$request->getControllerName()."&act=update");?>" method="post">
<input type="hidden" name="seatid" value="<?php if (isset($seatid)) echo $seatid;?>" />
<table class="form_table" border="0" width="450" align="center" cellpadding="6" cellspacing="0">
	<tr class="form_row_head">
		<td colspan=2>Seat Information</td>
	</tr>
  	<tr class="form_row">
		<td class="form_column_caption" width="40%">Label : </td>
		<td>
			<input type="text" name="label" value="<?php echo $seat->label;?>" maxlength="10">
		</td>
	</tr>

	<tr class="form_row_alt">
                <td class="form_column_caption" width="40%">DID : </td>
                <td>
                        <input type="text" name="did" id="did" value="<?php echo $seat->did;?>" maxlength="12" >
                </td>
        </tr>
        
        <tr class="form_row div_did_ch"<?php if (empty($seat->did)) { echo ' style="display:none;"';} ?> >
                <td class="form_column_caption">Forward Rule: </td>
                <td>
                        <input type="radio" name="forward_rule" id="forward_rule_U" value="U" <?php if($seat->forward_rule=='U') echo ' checked';?> /><label for="forward_rule_U"> Unconditional</label> &nbsp;
                        <input type="radio" name="forward_rule" id="forward_rule_X" value="X" <?php if($seat->forward_rule=='X') echo ' checked';?> /><label for="forward_rule_X"> Unreachable</label> &nbsp;
                        <input type="radio" name="forward_rule" id="forward_rule" value="" <?php if($seat->forward_rule=='') echo ' checked';?> /><label for="forward_rule"> Do Not Forward</label>
                </td>
        </tr>
        
        <tr class="form_row_alt div_did_ch" id="div_fwd_number"<?php if (empty($seat->did) || $seat->forward_rule=='') { echo ' style="display:none;"';} ?> >
                <td class="form_column_caption">Forward Number : </td>
                <td>
                        <input type="text" name="forward_number" value="<?php echo $seat->forward_number;?>" maxlength="12" />
                </td>
        </tr>
        
	<?php /*?>
	<tr class="form_row">
		<td class="form_column_caption">Active : </td>
		<td>
		<?php
		$str=empty($seat->device_mac)?" (You can't change)":"";
		?>
			<select name="active" id="active" <?php echo empty($seat->device_mac)?'disabled="disabled"':"";?>>
				<option value="Y"<?php if ($seat->active == 'Y') echo ' selected';?>>Yes<?php echo $str;?></option>
				<option value="N"<?php if ($seat->active == 'N') echo ' selected';?>>No<?php echo $str;?></option>
			</select>
		</td>
	</tr>  
	<?php // */?>
	<tr class="form_row">
		<td colspan="2" class="form_column_submit">
        	<input class="form_submit_button" type="submit" name="editseat" value="Update">
		</td>
	</tr>
</table>

</form>
