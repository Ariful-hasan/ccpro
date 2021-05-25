<link rel="stylesheet" href="ccd/select2/select2.min.css">
<script src="ccd/select2/select2.min.js"></script>


<?php
	include_once "lib/jqgrid_report.php";
	$grid = new jQGridReport();
	//$grid->caption = "Agent List";

	$grid->url = isset($dataUrl) ? $dataUrl : $this->url("task=priority&act=agent-skills");
	$grid->width="auto";//$grid->minWidth = 800;
	$grid->height = "auto";//390;
	$grid->rowNum = 500;
	$grid->pager = "#pagerb";
	$grid->container = ".content-body";
	$grid->hidecaption=false;
	$grid->CustomSearchOnTopGrid=true;
	
	$grid->ShowReloadButtonInTitle=true;
	$grid->ShowDownloadButtonInTitle= true;
	$grid->DownloadFileName = $pageTitle;	
	
	$grid->ShowDownloadButtonInBottom=false;
	$grid->multisearch=true;

	$grid->AddModelCustomSearchable("Agent ID", "agent_id", 80,"center",'select',$agents);
	$grid->AddSearhProperty('Skill','skill_id','select',$skills);
	$grid->AddModelNonSearchable("SkillSet", "skill_priority", 200,"center");
	if(UserAuth::getRoleID() == 'R')
		$grid->AddModelNonSearchable("Action", "action", 80,"center");	
	
?>

    <?php //echo print_r($_SESSION);?>
<?php 
$grid->show("#searchBtn");
?>

<script type="text/javascript">
$(function(){
    $('.select2').select2();
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>
