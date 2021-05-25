<?php
include_once "lib/jqgrid.php";
$grid = new jQGrid();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 50;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
//$grid->hidecaption=true;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=false;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;

$grid->AddModelNonSearchable("Campaign", "campaign_id_txt", 120, "center");
$grid->AddModel("Title", "title_txt", 180, "center");
$grid->AddModelNonSearchable("Disposition Code", "disposition_id", 80, "center");
$grid->AddModelNonSearchable("Action", "action", 60,"center");

$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;
?>
<script type="text/javascript">
	$(function(){
		AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
	});
</script>

<!--
<link href="css/report.css" rel="stylesheet" type="text/css">
<style type="text/css">
	.report_table tr.report_row_head td {
		background-image: -webkit-linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
		background-image: -moz-linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
		background-image: -o-linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
		background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#fff), color-stop(0.25, #fff), to(#e6e6e6)) !important;
		background-image: linear-gradient(#fff, #fff 25%, #e6e6e6) !important;
		color: #333333;
		box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05) !important;
		-webkit-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05) !important;
		-moz-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05) !important;
		border-bottom: 1px solid #ccc !important;
		border-top: 0 none;
		padding: 0.6em 0.5em;
		vertical-align: middle;
		text-align: center;
	}
	.table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
		padding: 5px;
	}
</style>
<script>
function delAgent(did)
{
	if (did.length > 0) {
		if (confirm('Are you sure to delete disposition code : ' + did + '?')) {
			window.location = "<?php echo $this->url('task='.$request->getControllerName()."&act=del&page=".$pagination->current_page."&dcode=");?>" + did;
		}
	}
}
</script>
<?php
$colspan = 5;
?>

<?php if (is_array($dispositions)):?>
<table class="report_extra_info">
<tr>
	<td>
		<?php
        	echo 'Record(s) ' . $pagination->getCurrentRecordsIndex() . ' of <b>' . $pagination->num_records . '</b> &nbsp;::&nbsp; ' . 
				'Page <b>' . $pagination->current_page . '</b> of <b>' . $pagination->getTotalPageCount() . '</b>';
		?>
	</td>
</tr>
</table>
<?php endif;?>
<table class="report_table" width="100%" border="0" align="center" cellpadding="1" cellspacing="1">
<tr class="report_row_head">
	<td class="cntr">SL</td>
	<td>Campaign</td>
	<td>Title</td>
	<td class="cntr">Disposition Code</td>
	<td class="cntr">Action</td>
</tr>

<?php if (is_array($dispositions)):?>

<?php
	$i = $pagination->getOffset();
	foreach ($dispositions as $service):
		$i++;
		$_class = $i%2 == 1 ? 'report_row_alt' : 'report_row';
?>
<tr class="<?php echo $_class;?>">
	<td class="cntr">&nbsp;<?php echo $i;?></td>
	<td align="left">&nbsp;<?php 
		if ($service->campaign_id == '0000') echo 'System';
		else if ($service->campaign_id == '1111') echo 'General';
		else echo $service->campaign_title;?></td>
	<td align="left">&nbsp;
		<?php if ($service->campaign_id == '0000'):?>
		<?php echo $service->title;?>
		<?php else:?>
		<a href="<?php echo $this->url('task='.$request->getControllerName()."&act=update&sid=".$service->disposition_id);?>"><?php echo $service->title;?></a>
		<?php endif;?>
	</td>
	<td class="cntr">&nbsp;<?php echo $service->disposition_id;?></td>
	<td class="cntr" class="report_row_del" style="text-align:center"><?php if ($service->campaign_id == '0000'):?>&nbsp;<?php else:?><a href="#" onClick="delAgent('<?php echo $service->disposition_id;?>');" class="btn btn-danger btn-xs" title="Delete"> <i class="fa fa-times"></i> Delete</a><?php endif;?>
	</td>
</tr>
<?php endforeach;?>
</table>
<table class="report_extra_info">
<tr>
	<td><?php echo $pagination->createLinks();?></td>
</tr>
<?php else:?>
<tr class="report_row_empty">
	<td colspan="<?php echo $colspan;?>">No record found!</td>
</tr>
<?php endif;?>
</table>

