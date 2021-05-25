<?php
include_once "lib/jqgrid.php";
$grid = new jQGrid();
//$grid->caption = "Agent List";
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 100;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
//$grid->hidecaption=true;
$grid->CustomSearchOnTopGrid=false;
$grid->multisearch=false;

$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" > Page Title </span>', "", 150,"center");
$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" > Parent  Page </span>', "", 150,"center");
$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" > Heading (EN) </span>', "", 150,"center");
$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" > Heading (BN) </span>', "", 150,"center");
$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" > Task </span>', "", 100,"center");
$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" > Previous <br> Page </span>', "", 80,"center");
$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" > Main <br> Page </span>', "", 80,"center");
$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" > Audio (BN) </span>', "", 100,"center");
$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" > Audio (EN) </span>', "", 100,"center");
$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" > Created At </span>', "", 100,"center");
$grid->AddModelNonSearchable('<span class="jqgrid-hd-ttips" > Action </span>', "", 100,"center");


$grid->show("#searchBtn");
?>
<script type="text/javascript">
    $(function(){
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
    });
</script>