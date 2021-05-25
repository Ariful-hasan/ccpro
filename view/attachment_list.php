<?php
/**
 * Created by PhpStorm.
 * User: arif
 * Date: 7/24/2018
 * Time: 2:17 PM
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
$grid->shrinkToFit=true;
$grid->ShowDownloadButtonInTitle=true;



//$grid->AddModel("Customer ID", "customer_id", 100, "center");
$grid->AddModel("Email ID", "ticket_id", 100, "center");
$grid->AddModelNonSearchable("Subject", "subject", 100,"center");
$grid->AddModel("File Name", "file_name",100,"center");
$grid->AddModelNonSearchable("Attachement", "downloadUrl",100,"center");

$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;
?>
<script type="text/javascript">
    $(function(){
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
    });
    function removeMainLoader() {
        $("#MainLoader").remove();
    }
</script>