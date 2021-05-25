<?php
/**
 * Created by PhpStorm.
 * User: arif
 * Date: 12/5/2018
 * Time: 10:32 AM
 */

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
$grid->shrinkToFit=false;

//RND
//$grid->colModel['name']="comments";


$grid->ShowDownloadButtonInTitle=true;


$grid->AddModel("Date & Time", "create_time", 100, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
$grid->AddModel("Ticket Ref#", "ticket_id", 100, "center");
$grid->AddModel("Customer Name", "name", 100,"center");
$grid->AddModel("Contact Number", "created_for", 100,"center");
$grid->AddModel("Account/Card", "account_id", 80,"center");
$grid->AddModelCustomSearchable("Category", "category_id", 80,"center",'select',$ticket_category);

//$grid->AddSearhProperty("Email", "email");
//$grid->AddSearhProperty("Number", "created_for");
$grid->AddSearhProperty("Disposition", "did", "select", $did_options);
$grid->AddSearhProperty("Status", "status", "select", $status_options);
$grid->SetDefaultValue("status", $status);
$grid->AddSearhProperty("Source", "source", "select", $source_name);
//$grid->AddSearhProperty("Sender", "created_by", "select", $agent_list);
//$grid->AddCustomPropertyOfModel($index, $property, $value);

$grid->AddModelNonSearchable("Skill", "skill_name", 100, "center");
//$grid->AddModelNonSearchable("Sender", "created_by", 100, "center");
//$grid->AddModelNonSearchable("Name", "sender_name", 80, "center");
if (isset($todaysDate) && !empty($todaysDate)){
    $grid->SetDefaultValue("create_time", date("Y-m-d", strtotime("-1 month")), $todaysDate);
}
$grid->AddModel("Last Update", "last_update_time", 100, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
$grid->SetDefaultValue("last_update_time", $sdate, $edate);

$grid->AddModelNonSearchable("Subject", "disposition_id", 100,"center");
$grid->AddModelNonSearchable("Count", "num_tickets", 100,"center");
$grid->AddModelNonSearchable("Assigned", "assigned_to", 80,"center");
$grid->AddModelNonSearchable("Source", "source", 100,"center");
$grid->AddModelCustomSearchable("Maker", "agent_name", 100,'center', 'select',$agent_list);
$grid->AddModelNonSearchable("Memo Status", "emailStatus", 100,"center");
$grid->AddModelNonSearchable("Last Update", "last_agent", 80,"center");
$grid->AddModelNonSearchable("Details", "detailsUrl", 100,"center");
$grid->AddDownloadHiddenProperty('Comments');

$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;
?>
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>