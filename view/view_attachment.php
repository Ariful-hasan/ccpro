<?php
/**
 * Created by PhpStorm.
 * User: arif
 * Date: 7/23/2018
 * Time: 3:48 PM
 */


include_once "lib/jqgrid.php";
$grid = new jQGrid();
//$grid->caption = "Agent List";
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 100;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
//$grid->hidecaption=true;
$grid->CustomSearchOnTopGrid=false;
$grid->multisearch=false;

$grid->AddModelNonSearchable("File Name", "file_name", 100,"center");
$grid->AddModelNonSearchable("Action", "downloadUrl", 100,"center");
//$grid->AddModelNonSearchable("IVR timeout (sec)", "ivr_timeout", 80,"center");
//$grid->AddModelNonSearchable("Language", "languages", 80,"center");
//$grid->AddModelNonSearchable("Action", "editLink", 80,"center");

$grid->show("#searchBtn");
?>
<script type="text/javascript">
    $(function(){
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
    });
    
    function removeMainLoader() {
        $("#MainLoader").remove();
    }
</script>