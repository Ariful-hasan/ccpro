<?php
/**
 * Created by PhpStorm.
 * User: arif
 * Date: 7/24/2018
 * Time: 11:17 AM
 */

include_once "lib/jqgrid.php";
$grid = new jQGrid();
//$grid->caption = "Agent List";
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 200;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
//$grid->hidecaption=true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->shrinkToFit=true;
$grid->ShowDownloadButtonInTitle=true;



$grid->AddModel("Customer ID", "customer_id", 100, "center");
$grid->AddModel("Customer Name", "name", 100,"center");
$grid->AddModel("Email", "email",100,"center");
$grid->AddModelNonSearchable("Attachement", "attachment_list_url",100,"center");

$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;
?>
<script type="text/javascript">
    $(function(){
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
    });
</script>