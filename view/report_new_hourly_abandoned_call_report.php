<?php
include_once "lib/jqgrid.php";
$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = false;
//$grid->styleLastRow = TRUE;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;



$grid->AddSearhProperty("Date", "sdate","date");
$grid->SetDefaultValue("sdate", date("Y-m-d"),date("Y-m-d"));
$grid->AddSearhProperty("Skill", "skill_id","select", $skills);

for($i = 0; $i <= 23; $i++)
{
    $am_pm = $i < 12 ? "AM" : "PM";
    $grid->AddModelNonSearchable(sprintf("%02d",$i %12). ":00 {$am_pm}", "Shour_".sprintf("%02d",$i), 100, "center");
}




$grid->show("#searchBtn");
?>

<script type="text/javascript">
    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
    });
</script>

