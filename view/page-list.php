<script type="text/javascript" src="js/bootbox/bootbox.min.js"></script>
<script type="text/javascript" src="js/toastr/toastr.min.js"></script>

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
	$grid->afterInsertRow="AfterInsertRow";
	$grid->ShowReloadButtonInTitle=true;
	$grid->ShowDownloadButtonInTitle=true;
	$grid->DownloadFileName = $pageTitle;	
	$grid->CustomSearchOnTopGrid=true;
	$grid->ShowDownloadButtonInBottom=false;
	$grid->multisearch=false;
	$grid->AddHiddenProperty("usertypemain");
	
	$grid->AddModel('Page ID', "id", 80,"center");
	
	$grid->AddModel('Page Name', "name", 80, "left");
	$grid->AddModelNonSearchable('Path', "path", 80,"left");
	$grid->AddModelNonSearchable('Parent', "parent", 80,"center");
	$grid->AddModelNonSearchable('Icon', "icon", 80,"left");
	$grid->AddModelNonSearchable('Active Class', "active_class", 80,"left");
	$grid->AddModelNonSearchable('Status', "status", 80,"center");
	include('view/grid-tool-tips.php');
	$grid->addModelTooltips($tooltips);	
?>

<?php 	$grid->show("#searchBtn");?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);	
});


function AfterInsertRow(rowid, rowData, rowelem) {		 
	if(rowData.usertypemain=="S"){
		$('tr#' + rowid).addClass('bg-supervisor');    		
	}
} 

function confirm_status(event){
	var msg = $(event.currentTarget).attr('data-msg');
	var href = $(event.currentTarget).attr('data-href');

	bootbox.confirm({
	    message: msg,
	    buttons: {
	        confirm: {
	            label: 'Yes',
	            className: 'btn-success'
	        },
	        cancel: {
	            label: 'No',
	            className: 'btn-danger'
	        }
	    },
	    callback: function (result) {
	    	if(result){
	    		 $.ajax({
					type:"POST",
					url:href,
					dataType:"text",
					success:function(resp) {
						console.log(resp);
						var data = JSON.parse(resp);

						if(data.result){
							toastr.success(data.message);
							ReloadAll();
						}else{
							toastr.error(data.message);
						}
					}
				});
	    	}
	    }
	});
};
</script>
