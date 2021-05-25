<?php
include_once "lib/jqgrid.php";
$grid = new jQGrid();
$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
$grid->url =  $this->url('task=get-tools-data&act=SDisposition&tid='.$tid);
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->ShowDownloadButtonInTitle=true;
$grid->CustomSearchOnTopGrid=false;

$grid->AddModelNonSearchable("Title", "titleTxt", 80, "left");
$grid->AddModel("Disposition Code", "disposition_id", 30, "center");
$grid->AddModelNonSearchable("Type", "disposition_type", 50, "center");
$grid->AddModelNonSearchable("Partition", "partition_id", 50, "center");
$grid->AddModelNonSearchable("Status", "status", 40, "center");
//$grid->AddModelNonSearchable("Action", "action", 40,"center");
$grid->show("#searchBtn");
?>
<script type="text/javascript">
    $(function(){

        $(document).on("click","#cboxClose", function (e) {
            $("#refresh_<?php echo $grid->GetGridId(true)?>").trigger('click');
        });

        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
    });
</script>
