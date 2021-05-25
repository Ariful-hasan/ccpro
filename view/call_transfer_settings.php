<?php
include_once "lib/jqgrid.php";
$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = true;
$grid->footerRow = false;
$grid->CustomSearchOnTopGrid=false;
$grid->multisearch=false;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;
$grid->loadComplete="";
$grid->addHeaderColumnGroup('agents',4, 'Permission to transfer call to:');

$grid->AddModelNonSearchable('User Role', "user_type", 220, "center");
$grid->AddModelNonSearchable('Agents', "agents", 220, "center");
$grid->AddModelNonSearchable('Supervisors', "supervisors", 220, "center");
$grid->AddModelNonSearchable('Skills', "skills", 220, "center");
$grid->AddModelNonSearchable('IVRs', "ivrs", 220, "center");
//$grid->AddModelNonSearchable('Action', "action", 220, "center");

$grid->show();
?>

<script type="text/javascript">
    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
    });
    function reloadSite() {
        Grid_<?php echo $grid->GetGridId(true)?>_reload()

    }
</script>

