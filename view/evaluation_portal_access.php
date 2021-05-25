<?php
include_once "lib/jqgrid.php";
$grid = new jQGrid();
//$grid->caption = "Agent List";
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->hidecaption=false;
$grid->CustomSearchOnTopGrid=true;

$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=false;
$grid->DownloadFileName = $pageTitle;

$grid->ShowDownloadButtonInBottom=false;
$grid->multisearch=false;

$grid->AddModel('Agent ID', "agent_id", 80,"center");
$grid->AddModel("Agent Name", "name", 100,"center");

if(UserAuth::getRoleID() == 'R')
$grid->AddModelNonSearchable('Skill(s)', "skill", 200,"left");

$grid->AddSearhProperty('Type', "usertype", "select", array("*" => "All", "S" => "Supervisor", "A" => "Agent"));
$grid->AddModelNonSearchable('Type', "usertype", 80, "center");
$grid->AddSearhProperty('Role', "role", "select", $role_list);
$grid->AddModelNonSearchable("Role", "role_id", 100, "center");

$grid->AddModelNonSearchable("Action", "action", 80,"center");

$grid->show("#searchBtn");
?>
<script type="text/javascript">
    $(function(){
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
    });
</script>
