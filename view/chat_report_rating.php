<?php 
include_once "lib/jqgrid.php";
$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
//$grid->hidecaption=false;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName=$pageTitle;

$grid->AddSearhProperty("Date Time", "tstamp", "datetime");
$grid->SetDefaultValue("tstamp", date("Y-m-d 00:00"));
$grid->AddSearhProperty("Agent ID", "agent_option_id", "select", $dp_options);

$grid->AddModelNonSearchable('Agent ID', "agent_id", 80, "center");
$grid->AddModelNonSearchable('No Rating', "no_rating", 80, "center");
$grid->AddModelNonSearchable('Very Bad', "very_bad_rating", 80, "center");
$grid->AddModelNonSearchable('Bad', "bad_rating", 80,"center");
$grid->AddModelNonSearchable('Normal', "normal_rating", 80,"center");
$grid->AddModelNonSearchable('Good', "good_rating", 80,"center");
$grid->AddModelNonSearchable('Very Good', "very_good_rating", 80,"center");
$grid->AddModelNonSearchable('Average', "average_rating", 100,"center");

include('view/grid-tool-tips.php');
$grid->addModelTooltips($tooltips);

$grid->show("#searchBtn");

?>
<link href="assets/plugins/star-rating/star-rating.min.css" rel="stylesheet" type="text/css"/>
<script src="assets/plugins/star-rating/star-rating.min.js" type="text/javascript"></script>
<!-- <input id="teach_rating" class="rating form-control" data-show-caption="false" data-show-clear="false" value="3.5" type="number" data-size="sm" data-stars="5" readonly="readonly"> -->
<style type="text/css">
#agent_option_id{
	max-width: 180px;
}
.rating-sm {
    font-size: 1.5em;
	display: inline-block;
}
.rating-container .rating-stars {
    color: #11780f;
}
</style>
<script type="text/javascript">
function reloadRatingData(){
	try{
		if ($(".rating").length) {
			$('.rating').each(function(i, obj) {
				var inputId = $(obj).attr('id');
				$('#'+inputId).rating('update', $(obj).val());
			    //console.log(i);console.log();console.log($(obj).val());
			});
		}
	}catch(e){;}
}
</script>