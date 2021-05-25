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
var usertype = '<?php echo $usertype;?>';
function delAgent(aid, num_skills)
{
	var ag_title = 'agent';
	if (usertype == 'S') {
		ag_title =  'supervisor';
	} else if (usertype == 'D') {
		ag_title =  'dashboard user';
	}

	if (aid.length >= 1) {
		var ctext = num_skills > 0 ? 'This '+ ag_title + ' is member of some skills. \n\n' : '';
		if (confirm(ctext+'Are you sure to delete '+ag_title+' ID : ' + aid + '?')) {
			window.location = "<?php echo $this->url('task='.$request->getControllerName()."&act=del&utype=$usertype&page=".$pagination->current_page."&agentid=");?>" + aid;
		}
	}
}

function statusAgent(aid, status)
{
	var ag_title = 'agent';
	if (usertype == 'S') {
		ag_title =  'supervisor';
	} else if (usertype == 'D') {
		ag_title =  'dashboard user';
	}

	if (aid.length >= 4) {
		var ctext = status == 'Y' ? 'Be confirm to do ACTIVE '+ag_title+' : ' + aid : 'Be confirm to do INACTIVE '+ag_title+' : ' + aid;
		if (confirm(ctext)) {
			window.location = "<?php echo $this->url('task='.$request->getControllerName()."&act=activate&utype=$usertype&page=".$pagination->current_page."&agentid=");?>" + aid + "&status="+status;
		}
	}

}
<?php if ($extra->tele_banking) { ?>
function teleBanking(aid, status)
{
	var ag_title = 'agent';
	if (usertype == 'S') {
		ag_title =  'supervisor';
	} else if (usertype == 'D') {
		ag_title =  'dashboard user';
	}

	if (aid.length >= 4) {
		var ctext = status == 'Y' ? 'Be confirm to do ACTIVE TeleBanking for '+ag_title+' : ' + aid : 'Be confirm to do INACTIVE TeleBanking for '+ag_title+' : ' + aid;
		if (confirm(ctext)) {
			window.location = "<?php echo $this->url('task='.$request->getControllerName()."&act=telebank&utype=$usertype&page=".$pagination->current_page."&agentid=");?>" + aid + "&status="+status;
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
        	echo 'Record(s) ' . $pagination->getCurrentRecordsIndex() . ' of <b>' . $pagination->num_records . '</b> &nbsp;::&nbsp; ' . 
				'Page <b>' . $pagination->current_page . '</b> of <b>' . $pagination->getTotalPageCount() . '</b>';
		?>
	</td>
</tr>
</table>
<?php endif;?>

<table class="report_table" width="80%" border="0" align="center" cellpadding="1" cellspacing="1">
<?php
	$_display_user_type = 'Agent';
	if ($usertype == 'S') {
		$_display_user_type = 'Supervisor';
	} else if ($usertype == 'D') {
		$_display_user_type = 'Dashboard User';
	}
?>
<tr class="report_row_head">
	<td>SL</td>
	<td><?php echo $_display_user_type;?> ID</td>
	<td>Name</td>
	<td>Skill(s)</td>
<?php if ($extra->tele_banking) { ?>
	<td class="cntr">TeleBanking</td>
<?php } ?>
	<td class="cntr">Status</td>
	<td class="cntr">Password</td>
	<td class="cntr">Delete</td>
</tr>

<?php if (is_array($agents)):?>

<?php
	$i = $pagination->getOffset();
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
	<td align="left"><?php
		$skills = $agent_model->getAgentSkill($agent->agent_id);
		$num_skills = 0;
		if (is_array($skills)) {
			foreach ($skills as $skill) {
				if ($num_skills > 0) echo ', ';
				echo isset($skill_options[$skill->skill_id]) ? $skill_options[$skill->skill_id] : $skill->skill_id;
				echo ' ('.$skill->priority.')';
				$num_skills++;
			}
		} else {
			echo 'No Skill';
		}
		?>&nbsp;
	</td>
<?php if ($extra->tele_banking) { ?>
	<td class="cntr">
	<?php if ($agent->web_password_priv == 'Y'):?>
		<a href='#' onclick="teleBanking('<?php echo $agent->agent_id;?>', 'N');">Yes</a>
	<?php else:?>
		<a href='#' onclick="teleBanking('<?php echo $agent->agent_id;?>', 'Y');">No</a>
	<?php endif;?>
	</td>
<?php } ?>
	<td class="cntr">
	<?php if ($agent->active == 'Y'):?>
		<a href='#' onclick="statusAgent('<?php echo $agent->agent_id;?>', 'N');">Active</a>
	<?php else:?>
		<a href='#' onclick="statusAgent('<?php echo $agent->agent_id;?>', 'Y');">Inactive</a>
	<?php endif;?>
	</td>
	<td class="cntr">
		<a href="<?php echo $this->url("task=agents&act=resetPassword&agentid=".$agent->agent_id."&type=".$_display_user_type);?>" class="resetter">Reset</a>
	</td>
	<td class="report_row_del cntr"><a href="#" onclick="delAgent('<?php echo $agent->agent_id;?>', <?php echo $num_skills;?>);"><img src="image/cancel.png" class="bottom" border="0" width="14" height="14" /></a>
	</td>
</tr>
<?php endforeach;?>
</table>
<table class="report_extra_info">
<tr>
	<td align="left"><?php echo $pagination->createLinks();?></td>
</tr>
<?php else:?>
<tr class="report_row_empty">
	<td colspan="<?php echo $colspan;?>">No <?php echo $_display_user_type;?> Found!</td>
</tr>
<?php endif;?>
</table>
