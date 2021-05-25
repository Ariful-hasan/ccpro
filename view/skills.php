<?php
	include_once "lib/jqgrid.php";
	$grid = new jQGrid();
	//$grid->caption = "Agent List";
	$grid->url = isset($dataUrl) ? $dataUrl : "";
	$grid->width="auto";//$grid->minWidth = 800;
	$grid->height = "auto";//390;
	$grid->rowNum = 100;
	$grid->pager = "#pagerb";
	$grid->container = ".content-body";
	//$grid->hidecaption=true;
	$grid->CustomSearchOnTopGrid=false;
	$grid->multisearch=false;

	$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" title="Name of skill.">Skill name</span>', "skillName", 80,"center");
	//if ($this->email_module){
		$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" title="Type of skill.">Skill type</span>', "qtype", 80,"center");
	//}
	$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" title="The web page that will pop-up on receiving a call.">Popup URL</span>', "popup_url", 80,"center");
	//$grid->AddModelNonSearchable("Caller priority", "caller_priority", 80,"center");
	$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" title="Call routing option set for the skill.">Call routing</span>', "acd_mode", 80,"center");
	$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" title="Auto answer option set for the skill.">Auto answer</span>', "auto_answer", 80,"center");
	$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" title="If voice logger is enabled for this skill.">Voice logger</span>', "record_all_calls", 80,"center");
	$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" title="If \'Find Last Agent\' feature is set for this skill \'Yes\' or \'No\'.">Find last agent</span>', "find_last_agent", 80,"center");
	$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" title="Status of the skill if Active or Inactive.">Status</span>', "active", 60,"center");
	$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" title="Clicking on this header the Supervisor may assign skills and priorities to the agents.">Agent list</span>', "agentList", 60,"center");
	$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" title="Disposition add or edit for the skill.">Disposition</span>', "dispList", 80,"center");
	$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" title="Clicking on \'MOH Filler\' you can configure welcome voice, music on hold, filler music and queue timeout action.">Action</span>', "action", 80,"center");
	
	$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>