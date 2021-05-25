<link href="css/form.css?v=2" rel="stylesheet" type="text/css">
<style type="text/css">
.contents{
	background: #E5F1F4;
	border-radius: 8px;
	-moz-border-radius: 8px;
	-webkit-border-radius: 8px;
    box-shadow: 0 0 2px #999999;
	-webkit-box-shadow: 0 0 2px #999999;
	-moz-box-shadow: 0 0 2px #999999;
    padding: 10px 0;
    width: 99%;
}
.alert-success {
    margin-bottom: 10px;
}
.dataTab{
	font-family: Verdana;
    font-size: 12px;
    margin-top: -10px;
    width: 100%;
}
.dataTab td{
	padding: 6px;
	border: 1px solid #DDDDDD;
}
.dataTab .textLabel{
	background-color: #FFFFFF;
	font-weight: bold;
}
.dataTab .dataLabel{
	background-color: #F0F8F0;
	font-weight: bold;
}
.dataTab .passLabel{
	background-color: #F0F8F0;
	font-weight: bold;
	font-size: 14px;
	color: #FF0000;
}
.dataTab tbody tr:first-child td:first-child {
	border-top-left-radius: 6px;
}
.dataTab tbody tr:first-child td:last-child {
	border-top-right-radius: 6px;
}
</style>
<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_pass_reset" method="post" action="<?php echo $this->url('task=extuser&act=resetPassword');?>">
<?php 
if (!$resetted){
?>	
	<input type="hidden" name="agentid" value="<?php echo $agentId;?>" />
	<input type="hidden" name="loginid" value="<?php echo $loginid;?>" />
	<table class="form_table" style="margin-top: 25px;">
	<tr class="form_row_alt">
		<td class="form_column_caption" style="text-align:center; font-size:12px;">
			Do you confirm that you want to reset the password of <?php echo $agentName; ?>?
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_submit" style="text-align:center;padding:20px 0;">
			<input class="form_submit_button" type="submit" value="Confirmed" name="submit" style="padding:3px;" />  &nbsp; &nbsp;
            <input class="form_submit_button" type="button" value="Cancel" name="submitcancel" onclick="parent.$.colorbox.close();" />           
		</td>
	</tr>
	</table>
	
	<?php if (empty($errMsg)){?>
	<p style="display: block; height: 80px;">&nbsp;</p>
	<?php }else { ?>
	<p style="display: block; height: 40px;">&nbsp;</p>
	<?php } ?>
<?php }else { ?>
<div class="contents">
	<table class="dataTab">
	<tbody>
		<tr>
			<td class="textLabel" width="40%">User Login ID</td>
			<td class="dataLabel" width="60%"><?php echo $loginid; ?></td>
		</tr>
		<tr>
			<td class="textLabel">User Name</td>
			<td class="dataLabel"><?php echo $agentName; ?></td>
		</tr>
		<tr>
			<td class="textLabel">New password</td>
			<td class="passLabel"><?php echo $curPass; ?></td>
		</tr>
	</tbody>
	</table>
	<p>&nbsp;</p>
	<p align="center">
		<a class="form_submit_button" href="#" onclick="parent.$.colorbox.close();">Close</a>
	</p>
</div>	
<?php } ?>	
</form>
