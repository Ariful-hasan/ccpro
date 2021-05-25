<?php
	include_once "lib/jqgrid.php";
	$grid = new jQGrid();
	$grid->url = isset($dataUrl) ? $dataUrl : "";
	$grid->width="auto";
	$grid->height = "auto";
	$grid->rowNum = 100;
	$grid->pager = "#pagerb";
	$grid->container = ".content-body";
	$grid->CustomSearchOnTopGrid=false;
	$grid->multisearch=false;	
	
	$grid->AddModelNonSearchable("Title", "title", 80,"center");
	$grid->AddModelNonSearchable("ID", "title_id", 80,"center");
	$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
    $(document).on('click', '#cboxClose', function () {
        $("#refresh_<?php echo $grid->GetGridId(true)?>").trigger('click');
    });

});
</script>