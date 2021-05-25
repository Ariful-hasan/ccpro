<?php
	

/*
	<img src="image/tn_add.gif" class="bottom" border="0" width="16" height="16" /> 
    <a href="<?php echo $this->url('task=report&act=agentinboundlog&option=monthly');?>">Add records to new lead</a> &nbsp;&nbsp; 

	<?php if (isset($side_menu_index) && $side_menu_index == 'reports'):?>
	<a href="<?php echo $this->url('task=report&act=agentinboundlog&option=monthly');?>" class="btn btn-small">
		<img src="image/report.png" class="bottom" border="0" width="16" height="16" /> Report
	</a>

	<?php endif;?>
*/
?>
	<?php if ($this->getControllerName()=='setttrunk' && $this->getActionName()== 'init'):?>
	<a href="<?php echo $this->url('task=setttrunk&act=add');?>" class="btn btn-purple">
		<i class="fa fa-user"></i> Add New Trunk
	</a>
<?php
/*
	<?php elseif ($this->getControllerName()=='emailreport'):?>
	<img src="image/icon/house.png" class="bottom" border="0" width="16" height="16" /> 
    <a href="<?php echo $this->url('task=email');?>">Email Home</a> &nbsp;&nbsp; 

	<?php elseif ($this->getControllerName()=='autodialer' && ($this->getActionName()== 'batch' || $this->getActionName()== 'init')):?>
	<img src="image/batch_add.gif" class="bottom" border="0" width="16" height="16" /> 
    <a href="<?php echo $this->url('task=autodialer&act=add');?>">Add New Batch</a> &nbsp;&nbsp; 
	<img src="image/music.png" class="bottom" border="0" width="16" height="16" /> 
    <a href="<?php echo $this->url('task=autodialer&act=mfiles');?>">Voice Files</a> &nbsp;&nbsp; 

*/
?>
	<?php elseif ($this->getControllerName()=='campaign' && $this->getActionName()== 'init'):?>
	<a href="<?php echo $this->url('task=campaign&act=add');?>" class="btn btn-purple">
		<i class="fa fa-plus-square-o"></i> Add New Campaign
	</a>

	<?php elseif ($this->getControllerName()=='lead' && $this->getActionName()== 'init'):?>
        <a href="<?php echo $this->url('task=lead&act=add');?>" class="btn btn-purple">
            <i class="fa fa-plus-square-o"></i> Add New Lead
        </a>

    <?php elseif ($this->getControllerName()=='predictive-dial' && in_array($this->getActionName(),array('init','numbers'))):?>
        <a title="Manage Predictive Dial" href="<?php echo $this->url('task=predictive-dial&act=manage&sid='.$request->getRequest('sid').'&sname='.$request->getRequest('sname'));?>" class="btn btn-purple">
            <i class="fa fa-phone-square"></i> Manage PD
        </a>
        <a title="Download Predictive Dial Numbers of Skill <?php echo $request->getRequest('sname') ?>" href="<?php echo $this->url('task=predictive-dial&act=download&sid='.$request->getRequest('sid').'&sname'.$request->getRequest('sname').'&download='.md5('Predictive Dial:: Numbers'.session_id()));?>" class="btn btn-purple">
            <i class="fa fa-cloud-download"></i> Download
        </a>

    <?php elseif ($this->getControllerName()=='predictive-dial' && $this->getActionName() == 'manage' ): ?>
        <!--<a title="Download Predictive Dial Numbers" class="btn btn-md btn-purple m-b-2 download" href="<?php /*echo $this->url( "task=predictive-dial&act=download&sid=".$request->getRequest('sid')."&sname=".$request->getRequest('sname')."&download=".md5('Predictive Dial:: Numbers'.session_id()))*/?>"> <i class="fa fa-cloud-download"></i> Download</a>-->
        <!-- <a title="Skill List" class="btn btn-md btn-purple m-b-2" href="<?php echo $this->url( "task=skills")?>"><i class="fa fa-list"></i> Skills</a> -->
        <!--<a title="Predictive Dial Number List" class="btn btn-md btn-purple m-b-2" href="<?php /*echo $this->url( "task=predictive-dial&act=numbers&sid=".$request->getRequest('sid')."&sname=".$request->getRequest('sname'))*/?>">PD Numbers</a>-->
        <!-- <a title="Add Predictive Dial Numbers" class="btn btn-md btn-purple m-b-2" href="<?php echo $this->url( "task=predictive-dial&act=add&sid=".$request->getRequest('sid')."&sname=".$request->getRequest('sname'))?>"><i class="fa fa-upload"></i> Upload Numbers</a>
        <a title="Predictive Dial Profile" class="btn btn-md btn-purple m-b-2" href="<?php echo $this->url( "task=predictive-dial&act=add-profile&sid=".$request->getRequest('sid')."&sname=".$request->getRequest('sname'))?>"><?php echo !empty($profile[0]->skill_id) ? '<i class="fa fa-edit"></i> Update ' : '<i class="fa fa-plus"></i> Add '; ?> Profile</a> -->

	<?php /*
	<img src="image/table.gif" class="bottom" border="0" width="16" height="16" /> 
    <a href="<?php echo $this->url('task=cdr&type=pabx');?>">PABX CDR</a> &nbsp;&nbsp; 
	*/ ?>

   <?php
    elseif ($this->getControllerName()=='report-new'):
        $selected_partition = UserAuth::getPartition();
        $label = !empty($selected_partition['name']) ? $selected_partition['name'] : "All";
    ?>
        <a href="<?php echo $this->url('task=report-new&act=date-format');?>" class="lightboxIF cboxElement" ><i class="fa fa-clock-o" aria-hidden="true"></i></a>
        &nbsp;|&nbsp;
        <b><tt>Selected Partition: </b><a href="<?php echo $this->url('task=report-partition&act=choose');?>" class="lightboxIF cboxElement" ><b><tt><i class="fa fa-cog"></i> <?php echo $label; ?></tt></b></a>
    <?php
    elseif ($this->getControllerName()=='report-partition' && in_array($this->getActionName(),array('init'))):?>
    <a title="Add Partition" data-w='500' data-h="300" href="<?php echo $this->url('task=report-partition&act=create');?>" class="btn btn-purple lightboxWIFR cboxElement">
        <i class="fa fa-plus"></i> Add Partition
    </a>
    <?php
    elseif ($this->getControllerName()=='report-partition' && in_array($this->getActionName(),array('create'))):?>
    <a title="Add Partition" href="<?php echo $this->url('task=report-partition');?>" class="btn btn-purple ">
        <i class="fa fa-table"></i> Partitions
    </a>
    <?php
    elseif ($this->getControllerName()=='report-partition' && in_array($this->getActionName(),array('partition-records'))):?>
    <a title="Add Partition" href="<?php echo $this->url('task=report-partition&act=add-partition-records');?>" class="btn btn-purple">
        <i class="fa fa-table"></i> Add Partition Record
    </a>
    <?php
    elseif ($this->getControllerName()=='report-partition' && in_array($this->getActionName(),array('add-partition-records','record-update'))):?>
    <a title="Add Partition" href="<?php echo $this->url('task=report-partition&act=partition-records');?>" class="btn btn-purple">
        <i class="fa fa-table"></i> Partition Records
    </a>

	<?php endif;?>

<?php if ($this->getControllerName()=='ivr-branch' && in_array($this->getActionName(), ['init','titles'])):?>
    <a  href="<?php echo $this->url('task=ivr-branch&act=create');?>" class="btn btn-purple lightboxIF">
        <i class="fa fa-plus"></i> Add IVR Branch Title
    </a>
<?php endif; ?>
