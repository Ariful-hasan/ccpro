<link href="css/report.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="js/lightbox/css/colorbox.css" />
<script src="js/lightbox/js/jquery.colorbox-min.js"></script>
<?php
include('conf.extras.php');
$colspan = $extra->tele_banking ? 8 : 7;
/*
{php}
	$this->assign('top_menu_items', array(array('image'=>'user_add','url'=>'user_add.php?usertype=Agent','title'=>'Add New Agent')));
{/php}
{include file="header.html"}
{include file="alert_confirm.html"}
*/
?>
<script type="text/javascript">
$(document).ready(function($) {
	$(".resetter").colorbox({
		iframe:true, width:"500", height:"330"
	});
});

function delAgent(aid)
{
	var ag_title = 'root';

	if (aid.length >= 1) {
		if (confirm('Are you sure to delete '+ag_title+' ID : ' + aid + '?')) {
			window.location = "<?php echo $this->url('task='.$request->getControllerName()."&act=del&agentid=");?>" + aid;
		}
	}
}

function statusAgent(aid, status)
{
	var ag_title = 'root';

	if (aid.length >= 4) {
		var ctext = status == 'Y' ? 'Be confirm to do ACTIVE '+ag_title+' : ' + aid : 'Be confirm to do INACTIVE '+ag_title+' : ' + aid;
		if (confirm(ctext)) {
			window.location = "<?php echo $this->url('task='.$request->getControllerName()."&act=activate&agentid=");?>" + aid + "&status="+status;
		}
	}

}
<?php if ($extra->tele_banking) { ?>
function teleBanking(aid, status)
{
	var ag_title = 'root';

	if (aid.length >= 4) {
		var ctext = status == 'Y' ? 'Be confirm to do ACTIVE TeleBanking for '+ag_title+' : ' + aid : 'Be confirm to do INACTIVE TeleBanking for '+ag_title+' : ' + aid;
		if (confirm(ctext)) {
			window.location = "<?php echo $this->url('task='.$request->getControllerName()."&act=telebank&agentid=");?>" + aid + "&status="+status;
		}
	}

}
<?php } ?>
</script>

<?php if (is_array($agents)):?>
<table class="report_extra_info">
<tr>
	<td align="left">
		<?php
        	echo 'Total Record(s) <b>' . $num_records . '</b>';
		?>
	</td>
</tr>
</table>
<?php endif;?>

<table class="report_table" width="80%" border="0" align="center" cellpadding="1" cellspacing="1">
<?php
	$_display_user_type = 'Root';
?>
<tr class="report_row_head">
	<td>SL</td>
	<td><?php echo $_display_user_type;?> ID</td>
	<td>Name</td>
	<td class="cntr">Status</td>
	<td class="cntr">Start time</td>
	<td class="cntr">Stop time</td>
	<td class="cntr">Duration (min)</td>
	<td class="cntr">Password</td>
	<td class="cntr">Delete</td>
</tr>

<?php if (is_array($agents)):?>

<?php
	$i = 0;
	foreach ($agents as $agent):
		$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
?>
<tr class="<?php echo $_class;?>">
	<td align="center">&nbsp;<?php echo $i;?></td>
	<td align="left">
		<a href="<?php echo $this->url('task='.$request->getControllerName()."&act=update&agentid=".$agent->agent_id);?>"><?php echo $agent->agent_id;?></a>
		<?php if (!empty($agent->nick)):?>&nbsp;[<?php echo $agent->nick;?>]<?php endif;?>
	</td>
	<td align="left"><?php echo $agent->nick;?></td>
	<td class="cntr">
	<?php if ($agent->active == 'Y'):?>
		<a href='#' onclick="statusAgent('<?php echo $agent->agent_id;?>', 'N');">Active</a>
	<?php else:?>
		<a href='#' onclick="statusAgent('<?php echo $agent->agent_id;?>', 'Y');">Inactive</a>
	<?php endif;?>
	</td>
	<?php
		$access_limit = $agent_model->getAgentAccessRestrictions($agent->agent_id);
	?>
	<?php if (!empty($access_limit)) {?>
	<td class="cntr">&nbsp;<?php echo $access_limit->start_time?></td>
	<td class="cntr">&nbsp;<?php echo $access_limit->stop_time?></td>
	<td class="cntr">&nbsp;<?php echo (strtotime($access_limit->stop_time)-strtotime($access_limit->start_time))/60;?></td>
	<?php } else {?>
	<td class="cntr">&nbsp;0000-00-00 00:00:00</td>
	<td class="cntr">&nbsp;0000-00-00 00:00:00</td>
	<td class="cntr">&nbsp;0</td>
	<?php }?>
	<td class="cntr">
		<a href="<?php echo $this->url("task=agents&act=resetPassword&agentid=".$agent->agent_id."&type=".$_display_user_type);?>" class="resetter">Reset</a>
	</td>
	<td class="report_row_del cntr"><a href="#" onclick="delAgent('<?php echo $agent->agent_id;?>');"><img src="image/cancel.png" class="bottom" border="0" width="14" height="14" /></a>
	</td>
</tr>
<?php endforeach;?>
</table>
<?php else:?>
<table class="report_extra_info">
<tr class="report_row_empty">
	<td colspan="<?php echo $colspan;?>">No <?php echo $_display_user_type;?> Found!</td>
</tr>
</table>
<?php endif;?>

