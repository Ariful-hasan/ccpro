<?php 
include_once "lib/jqgrid.php";

$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;

$grid->AddModel("Date", "log_date", 100, "center", ".date", "Y-m-d", "Y-m-d", true, "date");
$grid->AddSearhProperty("Disposition Code", "dcode", "select", $dp_options);

$grid->AddModelNonSearchable("Disposition Code", "disposition_code", 80,"center");
$grid->AddModelNonSearchable("Service Count", "service_count", 80,"center");

if ($_SERVER['REMOTE_ADDR'] == '72.48.199.118')
$grid->AddTitleRightHtml('
        <a id="" class="monthselect btn btn-xs btn-info" href="" >Last Month</a>
        <a id="" class="monthselect btn btn-xs btn-info" href="" >Last Week</a>
');

$grid->show("#searchBtn");
?>
<script>
function LoadData(month){
        var data = jQuery("<?php echo $grid->GetGridId();?>").jqGrid("getGridParam", "postData");
        data.dateRange = month;
        data._search = true;
        jQuery("<?php echo $grid->GetGridId();?>").jqGrid("setGridParam", { "postData": data });
        jQuery("<?php echo $grid->GetGridId();?>").trigger("reloadGrid");
}
$(function(){
        $("body").on("click",".monthselect",function(e){
                e.preventDefault();
                if(!$(this).hasClass("mselected")){
                        $(".monthselect").removeClass("mselected");
                        $(".monthselect").removeClass("btn-primary").addClass("btn-info");
                        $(this).removeClass("btn-info").addClass("mselected btn-primary");

                        var cap=$(this).text();
                        //alert(cap);
                        LoadData(cap);
                }
        });
});
</script>
