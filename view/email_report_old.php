<?php
/**
 * Created by PhpStorm.
 * User: arif
 * Date: 7/26/2018
 * Time: 11:05 AM
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


$grid->AddModel("Customer ID", "customer_id", 100, "center");
$grid->AddModel("Arrival Date\Time", "create_time", 130, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
//$grid->AddModel("Email Ref#", "ticket_id", 100, "center");
$grid->AddModel("Waiting Duration(mins)", "waiting_duration", 100, "center");
$grid->AddModelNonSearchable("Email ID", "ticket_id", 100, "center");
$grid->AddModelNonSearchable("Skill Set Name", "skill_name", 100, "center");

//$grid->AddSearhProperty("RS/TR", "rs_tr", "select", $rs_tr_list);
$grid->AddSearhProperty("Closed Reason Code", "did", "select", $did_options);
if (empty($isAgent)){
    $grid->AddSearhProperty("Agent", "agent_id", "select", $agent_list);
    $grid->SetDefaultValue("agent_id", $agent_id);
    $grid->AddSearhProperty("Skill", "skill_id", "select", $skill_list);
}
$grid->AddModelNonSearchable("Agent Name 1", "agent_id", 130,"center");
$grid->AddModelNonSearchable("Agent Name 2", "agent_id_2", 130,"center");

$grid->AddModel("First Open Time", "first_open_time", 130,"center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
//$grid->AddModel("Account/Card", "account_id", 80,"center");
//$grid->AddModelCustomSearchable("Category", "category_id", 80,"center",'select',$ticket_category);

//$grid->AddSearhProperty("Number", "created_for");
//$grid->AddSearhProperty("Disposition", "did", "select", $did_options);
//$grid->AddSearhProperty("Status", "status", "select", $status_options);
//$grid->SetDefaultValue("status", $status);
//$grid->AddSearhProperty("Source", "source", "select", $source_name);
//$grid->AddSearhProperty("Sender", "created_by", "select", $agent_list);
//$grid->AddCustomPropertyOfModel($index, $property, $value);

//$grid->AddModelNonSearchable("Sender", "created_by", 100, "center");
//$grid->AddModelNonSearchable("Name", "sender_name", 80, "center");
if (isset($todaysDate) && !empty($todaysDate)){
    $grid->SetDefaultValue("create_time", date("Y-m-d", strtotime("-1 month")), $todaysDate);
}
$grid->AddModel("Open Time", "last_update_time", 130, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
//$grid->SetDefaultValue("last_update_time", $sdate, $edate);
$grid->AddModel("Closed Time", "closed_time", 130, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");

$grid->AddModelCustomSearchable("In KPI", "in_kpi", 100,"center",'select', $in_kpi_list);
$grid->AddModelNonSearchable("Open Duration(mins)", "open_duration", 100,"center");
$grid->AddModelNonSearchable("Closed Reason Code", "disposition_id", 100,"center");
$grid->AddModel("Email Subject", "subject",150, "center");
$grid->AddModelNonSearchable("Mail From", "created_for", 150,"center");
$grid->AddModelNonSearchable("Mail To", "mail_to", 150,'center');
$grid->AddModelNonSearchable("RS/TR", "rs_tr", 100,"center");
$grid->AddModelNonSearchable("Rescheduled Time", "reschedule_time", 130,"center");
$grid->AddModelNonSearchable("RS/TR Creation Time", "rs_tr_create_time", 130,"center");

$grid->AddSearhProperty("Contact Status", "status", "select", $status_options);
$grid->SetDefaultValue("status", $status);
$grid->AddModelNonSearchable("Contact Status", "emailStatus", 100,"center");
$grid->AddDownloadHiddenProperty('Comments');

$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;
?>
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>