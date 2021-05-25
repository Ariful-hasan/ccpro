<?php 
include_once "lib/jqgrid.php";
$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 500;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit=false;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;

$grid->AddModel("Date", "cdate", 80, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
$grid->SetDefaultValue("cdate", date("Y-m-d"));
$grid->AddModelNonSearchable("Skill", "skill_name", 80, "center");
$grid->AddModelNonSearchable("Offered calls", "calls_offered", 80, "center");
$grid->AddModelNonSearchable("&lt; 5 sec", "a_l5", 80,"center");
$grid->AddModelNonSearchable("&lt; 10 sec", "a_5to10", 80, "center");
$grid->AddModelNonSearchable("&lt; 20 sec", "a_10to20", 80, "center");
$grid->AddModelNonSearchable("&lt; 30 sec", "a_20to30", 80,"center");
$grid->AddModelNonSearchable("&lt; 40 sec", "a_30to40", 80, "center");
$grid->AddModelNonSearchable("&lt; 50 sec", "a_40to50", 80, "center");
$grid->AddModelNonSearchable("&lt; 60 sec", "a_50to60", 80,"center");
$grid->AddModelNonSearchable("&lt; 70 sec", "a_60to70", 80, "center");
$grid->AddModelNonSearchable("&lt; 80 sec", "a_70to80", 80, "center");
$grid->AddModelNonSearchable("&lt; 90 sec", "a_80to90", 80,"center");
$grid->AddModelNonSearchable("&lt; 120 sec", "a_90to120", 80, "center");
$grid->AddModelNonSearchable("&gt; 120 sec", "a_g120", 80, "center");
$grid->AddModelNonSearchable("Total", "total", 80,"center");

include('view/grid-tool-tips.php');
$actname = $this->getActionName();
if ($actname == 'abdnskilldelayspectrum') $tooltips = $tooltips_override['spectrum_abd'] + $tooltips;
$grid->addModelTooltips($tooltips);

$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>