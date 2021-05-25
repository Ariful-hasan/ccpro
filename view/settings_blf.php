<?php 
$unit = !empty($_REQUEST ['unit']) ? $_REQUEST ['unit'] : 1;
$seat = !empty($_REQUEST ['seat']) ? $_REQUEST ['seat'] : "";

include_once "lib/jqgrid.php";

$grid = new jQGrid();
$grid->caption = "BLF Settings";
$grid->url =  $this->url('task=get-tools-data&act=blfsettings&seat='.$seat.'&unit='.$unit);
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->CustomSearchOnTopGrid=false;
$grid->multisearch=false;
$grid->IsCSVDownload=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->ShowReloadButtonInTitle=true;

$blfAddUrl = $this->url("task=blfsettings&act=add&seat=$seat&unit=$unit");
$grid->AddTitleRightHtml('<a class="lightboxIF btn btn-xs btn-info" href="'.$blfAddUrl.'"><i class="fa fa-plus"></i> Add New</a>');

$grid->AddModelNonSearchable("Name", "seat_id", 100 ,"center");
$grid->AddModelNonSearchable("Monitor", "monitor", 100 ,"center");
$grid->AddModelNonSearchable("Key", "key_id", 100 ,"center");
$grid->AddModelNonSearchable("Type", "type", 100 ,"center");
$grid->AddModelNonSearchable("Action", "action", 100,"center");
?>
<div class="col-lg-12">
	<section class="box ">				
		<header class="panel_header">
		    <h2 class="title pull-left"><?php echo !empty($tabTitle) ? $tabTitle : "BLF Settings"; ?></h2>
			<div class="actions panel_actions pull-right">
				<i class="box_toggle fa fa-chevron-down"></i>
				<i class="box_close fa fa-times"></i>
			</div>
		</header>	
     
        <div class="content-body">
		    <div class="row">			
                <ul class="nav nav-tabs">
                    <li class="<?php echo $unit==1?'active':'';?>"><a href="<?php echo $this->url("task=blfsettings&seat=$seat&unit=1");?>">Main</a></li>
                    <li class="<?php echo $unit==2?'active':'';?>"><a href="<?php echo $this->url("task=blfsettings&seat=$seat&unit=2");?>">Expansion1</a></li>
                    <li class="<?php echo $unit==3?'active':'';?>"><a href="<?php echo $this->url("task=blfsettings&seat=$seat&unit=3");?>">Expansion2</a></li>
                </ul> 
                <div class="tab-content">
        			<div class="grid-body"><?php $grid->show(); ?></div>
    			</div>
		    </div>
	    </div>
		
	</section>
</div>
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
})
</script>


