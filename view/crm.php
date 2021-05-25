<?php 
include_once "lib/jqgrid.php";
$grid = new jQGrid();
//$grid->caption = "Agent List";
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 50;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
//$grid->hidecaption=true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;

$grid->AddModel("Dial Number", "dial_number", 70, "center");
$grid->AddModel("Campaign", "cp_title", 70, "center", "", "", "", true, "select", $campaign_options);
$grid->AddModel("Lead", "lp_title", 70, "center", "", "", "", true, "select", $lead_options);
$grid->AddSearhProperty("Disposition", "disp_code", "select", $dp_options);

$grid->AddModelNonSearchable("First name", "first_name", 80, "center");
$grid->AddModelNonSearchable("Last name", "last_name", 80, "center");
$grid->AddModelNonSearchable("Dial attempt", "dial_attempted", 80, "center");
$grid->AddModelNonSearchable("Last disposition", "disposition_id", 80, "center");
$grid->AddModelNonSearchable("Last dial time", "last_dial_time", 80, "center");
$grid->AddModelNonSearchable("Status", "status", 60,"center");
$grid->AddModelNonSearchable("Audio", "audio_callid", 60,"center");


$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});

function playFile(evt){
	$.post('cc_web_agi.php', { 'page':'CDR', 'agent_id':'<?php echo UserAuth::getCurrentUser();;?>', 'option':evt },
		function(data, status) {
			if (status == 'success') {
				if(data != 'Y') alert(data);
			} else {
				alert("Failed to communicate!");
			}
	});
}
</script>
