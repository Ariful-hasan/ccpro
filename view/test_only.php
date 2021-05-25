<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>http://stackoverflow.com/q/8900040/315935</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   
    <?php /*?> 
    <link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/themes/redmond/jquery-ui.css" />
    <link rel="stylesheet" type="text/css" href="http://www.ok-soft-gmbh.com/jqGrid/jquery.jqGrid-4.3.1/css/ui.jqgrid.css" />
    */?>
    <link rel="stylesheet" type="text/css" href="http://www.ok-soft-gmbh.com/jqGrid/jquery.jqGrid-4.3.1/plugin/ui.multiselect.css" />
    
    <link href="lib/jqui/jquery-ui-1.10.0.custom.css" rel="stylesheet" media="screen"/>
	<link href="lib/grid/css/ui.jqgrid.css" rel="stylesheet" media="screen"/>

    <style type="text/css">
        html, body { font-size: 75%; }
    </style>
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>    
    
    
    <script type="text/javascript" src="http://www.ok-soft-gmbh.com/jqGrid/jquery.jqGrid-4.3.1/plugin/ui.multiselect.js"></script>
    <script type="text/javascript" src="http://www.ok-soft-gmbh.com/jqGrid/jquery.jqGrid-4.3.1/js/i18n/grid.locale-en.js"></script>   
    <script type="text/javascript" src="lib/grid/js/jquery.jqGrid.min.js"></script>

<?php
	include_once "lib/jqgrid.php";
	$grid = new jQGrid();
	//$grid->caption = "Agent List";
	$grid->url = isset($dataUrl) ? $dataUrl : "";
	$grid->width="auto";//$grid->minWidth = 800;
	$grid->height = "auto";//390;
	$grid->rowNum = 20;
	$grid->pager = "#pagerb";
	$grid->container = ".content-body";
	$grid->hidecaption=false;
	$grid->CustomSearchOnTopGrid=false;
	
	$grid->ShowReloadButtonInTitle=true;
	$grid->ShowDownloadButtonInTitle=true;
	$grid->DownloadFileName = $pageTitle;	
	
	$grid->ShowDownloadButtonInBottom=false;
	$grid->multisearch=false;	
	//$grid->AddTitleRightHtml('<a class="btn btn-xs btn-info" href="'."add-faq-item".'" ><i class="fa fa-plus"></i>Add New</a>');
	$grid->AddModelNonSearchable((isset($userColumn) ? $userColumn : ""), "nick", 80,"center");
	$grid->AddModelNonSearchable("Name", "name", 80,"center");
	$grid->AddModelNonSearchable("Skill(s)", "skill", 200,"left");
	$grid->AddModelNonSearchable("TeleBanking", "web_password_priv", 80,"center");
	$grid->AddModelNonSearchable("Status", "active", 80,"center");
	$grid->AddModelNonSearchable("Password", "password", 80,"center");	
	$grid->AddModelNonSearchable("Action", "action", 80,"center");
	
	//SL 	Agent ID 	Name 	Skill(s) 	TeleBanking 	Status 	Password 	Delete
	//seat, suid,supass, pcmac
	
	//update seat set pc_mac='' where seat_id=seat 
	
	
?>

    <?php //echo print_r($_SESSION);?>
<?php 
				$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	try{
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
	}catch(e){
	}
});
</script>
   
</head>
<body>
    <table id="list"><tr><td/></tr></table>
    <div id="pager"></div>
</body>
</html>