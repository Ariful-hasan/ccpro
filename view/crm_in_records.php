<?php
include_once "lib/jqgrid.php";
$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit=false;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;

$statusOpt = array("*"=>"Select","A"=>"Active","I"=>"Inactive","L"=>"Locked","N"=>"Disabled");

$grid->AddModel("Account ID", "account_id", 100, "center");
$grid->AddModel("First Name", "first_name", 140, "center");
$grid->AddSearhProperty("Mobile Phone", "mobile_phone");
$grid->AddModel("Last Name", "last_name", 140, "center");
$grid->AddSearhProperty("Home Phone", "home_phone");
$grid->AddSearhProperty("Email Address", "email");
$grid->AddSearhProperty("Office Phone", "office_phone");
$grid->AddSearhProperty("Status", "status", "select", $statusOpt);

$grid->AddModelNonSearchable("Date of Birth", "DOB", 80, "center");
$grid->AddModelNonSearchable("Email Address", "email", 180,"center");
$grid->AddModelNonSearchable("House No", "house_no", 80,"center");
$grid->AddModelNonSearchable("Street", "street", 90,"center");
$grid->AddModelNonSearchable("Landmarks", "landmarks", 90,"center");
$grid->AddModelNonSearchable("City", "city", 90,"center");
$grid->AddModelNonSearchable("State", "state", 80,"center");
$grid->AddModelNonSearchable("Zip Code", "zip", 80,"center");
$grid->AddModelNonSearchable("Country", "country", 100,"center");
$grid->AddModelNonSearchable("Mobile Phone", "mobile_phone", 100,"center");
$grid->AddModelNonSearchable("Home Phone", "home_phone", 100,"center");
$grid->AddModelNonSearchable("Office Phone", "office_phone", 100,"center");
$grid->AddModelNonSearchable("Other Phone", "other_phone", 100,"center");
$grid->AddModelNonSearchable("Fax", "fax", 100,"center");
$grid->AddModelNonSearchable("Priority Label", "priority_label", 100,"center");
$grid->AddModelNonSearchable("Status", "statusTxt", 60,"center");
$grid->AddModelNonSearchable("Action", "actUrl", 100,"center");

$grid->ShowDownloadButtonInTitle=false;

$grid->AddTitleRightHtml('<a class="btn btn-xs btn-success" onclick="javascript:stopLoading()" href="'.$this->url( "task=crm_in&act=crmindatadownload" . $dataDlLink).'"><i class="fa fa-download"></i>Download CSV</a>');

$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
function stopLoading() {
    setTimeout(function(){ShowWait(false,'');}, 500);
}
</script>
