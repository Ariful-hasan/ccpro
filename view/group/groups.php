<?php

    include_once "lib/jqgrid.php";
    $grid = new jQGrid();
    // $grid->caption = "Groups";
    $grid->url = isset($dataUrl) ? $dataUrl : "";
    $grid->width="auto";
    $grid->height = "auto";
    $grid->rowNum = 20;
    $grid->pager = "#pagerb";
    $grid->container = ".content-body";
    $grid->hidecaption=false;
    $grid->CustomSearchOnTopGrid=true;
    
    $grid->ShowReloadButtonInTitle=true;
    $grid->ShowDownloadButtonInTitle=true;
    $grid->DownloadFileName = $pageTitle;   
    
    $grid->ShowDownloadButtonInBottom=false;
    $grid->multisearch=false;   

    $grid->AddModel("Id", "id", 80,"center");
    $grid->AddModel("Title", "title", 80,"center");
    $grid->AddModelNonSearchable("Status", "status", 80,"center");
    $grid->AddModelNonSearchable("Action", "action", 80,"center"); 
    
?>

<?php $grid->show("#searchBtn"); ?>  

<!-- Toaster -->
<link rel="stylesheet" href="assets/plugins/toastr/toastr.min.css" />
<script type="text/javascript" src="assets/plugins/toastr/toastr.min.js"></script>

<!-- Bootbox -->
<script type="text/javascript" src="ccd/bootbox/bootbox.min.js"></script>

<script type="text/javascript">
    $(function(){
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
    });
</script>