<?php
	include_once "lib/jqgrid.php";
	$grid = new jQGrid();
	//$grid->caption = "Agent List";
	$grid->url =  $this->url('task=get-tools-data&act=seats');
	$grid->width="auto";//$grid->minWidth = 800;
	$grid->height = "auto";//390;
	$grid->rowNum = 20;
	$grid->pager = "#pagerb";
	$grid->container = ".content-body";
	//$grid->hidecaption=true;
	$grid->CustomSearchOnTopGrid=true;
	$grid->multisearch=true;	
	$grid->IsCSVDownload=true;
	$grid->ShowDownloadButtonInTitle=true;
	$grid->ShowReloadButtonInTitle=true;
	
	$grid->AddModel("Seat ID", "seat_id", 60,"center");
	$grid->AddModel("Label", "label", 80,"center");
	$grid->AddModel("DID", "did", 80,"center");
	$grid->AddModelNonSearchable("Device MAC", "device_mac", 80,"center");	
	$grid->AddModelNonSearchable("Device IP", "device_ip", 80,"center");
	$grid->AddModelNonSearchable("Activation<br> Code", "code", 80,"center");
	$grid->AddModelNonSearchable("Outgoing<br> Control", "allow_call_without_login", 60,"center");
	$grid->AddModelNonSearchable("Status", "active", 60,"center");
	if (UserAuth::hasRole('admin')) {
	    $grid->AddModelNonSearchable("BLF", "blf_url", 60, "center");
		$grid->AddTitleRightHtml('<a class="btn btn-xs btn-info ConfirmAjaxWR" msg="Are you sure?" href="'.$this->url( "task=confirm-response&act=seats&pro=ga").'"><i class="fa fa-gears"></i>Generate Activation Code</a>');
		$grid->AddModelNonSearchable("Action", "action", 200,"center");
	}
	
	$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>