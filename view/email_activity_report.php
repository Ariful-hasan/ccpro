<?php
/**
 * Created by PhpStorm.
 * User: arif
 * Date: 11/1/2018
 * Time: 10:28 AM
 */

include_once "lib/jqgrid.php";
$grid = new jQGrid();
//$grid->caption = "Agent List";
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 200;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
//$grid->hidecaption=true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->shrinkToFit=true;

//RND
//$grid->colModel['name']="comments";


$grid->ShowDownloadButtonInTitle=true;


$grid->AddModel("Date Time", "activity_time", 130, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
$grid->SetDefaultValue("activity_time", date("Y-m-d", strtotime("-1 month")), date("Y-m-d 23:59"));
$grid->AddModel("Email ID", "ticket_id", 150,"center");
if (empty($isAgent)){
    $grid->AddSearhProperty("Agent", "agent_id", "select", $agent_list);
    $grid->SetDefaultValue("agent_id", $agent_id);
}
$grid->AddModelNonSearchable("Agent", "agent_id", 150,"center");
$grid->AddSearhProperty("Status", "status", "select", $status_options);
$grid->SetDefaultValue("status", $status);
$grid->AddModelNonSearchable("Status", "emailStatus", 100,"center");


$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;
?>
<script type="text/javascript">
    $(function(){
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
    });
</script>