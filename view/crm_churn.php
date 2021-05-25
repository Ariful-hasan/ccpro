<form name="frm_campaign" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="dial_num" value="<?php echo $dial_num;?>" />
<input type="hidden" name="camid" value="<?php echo $camid;?>" />
<input type="hidden" name="leadid" value="<?php echo $leadid;?>" />
<input type="hidden" name="disp_code" value="<?php echo $disp_code;?>" />
<input type="hidden" name="num_selected" value="<?php echo $num_selected;?>" />
<?php
if ($num_selected == 1) {
	$rec = 'record';
	$self = 'this';
} else {
	$rec = 'records';
	$self = 'these';
}
?>
<h1>Number of <?php echo $rec;?> selected : <?php echo $num_selected;?><br /></h1>

<div style=" font-size:15px;">Do you want to churn <?php echo $self . ' ' . $rec;?>?</div>

<br /><br />

<input class="form_submit_button" type="submit" value=" Churn " name="churn" /> &nbsp; &nbsp;
<input class="form_submit_button" type="button" value=" Cancel " name="submitcancel" onClick="parent.$.colorbox.close();" /> 

</form>