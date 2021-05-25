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
//$grid->hidecaption=true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;



//$grid->AddCustomPropertyOfModel($index, $property, $value);





$grid->AddSearhProperty("Skill", "skill_id","select", $skills);
$grid->AddSearhProperty("Disposition", "disposition_id","select", $dispositions);

if (isset($todaysDate) && !empty($todaysDate)){
    $grid->SetDefaultValue("create_time", $todaysDate);
}
$grid->AddSearhProperty("Status", "status","select", $statusOptions);
$grid->SetDefaultValue("status", $status);
$grid->SetDefaultValue("last_update_time", $sdate, $edate);


$grid->AddModel("Open Time", "create_time", 80, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
$grid->SetDefaultValue("create_time", $sdate, $edate);
$grid->AddModelNonSearchable("Created By", "created_by", 80,"center");
$grid->AddModelNonSearchable("Created For", "created_for", 80,"center");
$grid->AddModelNonSearchable("Status <br>Updated By", "status_updated_by", 80,"center");
$grid->AddModelNonSearchable("Skill", "skill_name", 80, "center");
$grid->AddModelNonSearchable("Disposition", "disposition_id", 80, "center");
$grid->AddModelNonSearchable("Subject", "subject", 80,"center");
$grid->AddModelNonSearchable("Status", "status", 80,"center");
$grid->AddModel("Last Update", "last_update_time", 80, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
$grid->AddModelNonSearchable("Action", "action", 80, "center");

$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;
?>
<script type="text/javascript">
    $(function(){
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
    });
</script>