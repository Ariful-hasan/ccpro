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
//$grid->AddTitleRightHtml('<a class="btn btn-xs btn-info" href="'."add-faq-item".'" ><i class="fa fa-plus"></i>Add New</a>');
$grid->AddHiddenProperty("usertypemain");

//$grid->AddModelNonSearchable((isset($userColumn) ? $userColumn : ""), "agent_id", 80,"center");

$grid->AddModel('Login ID', "login_id", 80,"center");

//$grid->AddModel('Nick Name', "nick", 80, "left");
$grid->AddModel('Name', "name", 80,"left");
//$grid->AddModelNonSearchable('Skill(s)', "skill", 200,"left");
//$grid->AddModelNonSearchable("TeleBanking", "web_password_priv", 80,"center");
$grid->AddModelNonSearchable('DID', "did", 80,"center");
$grid->AddModelNonSearchable('Status', "active", 80,"center");
$grid->AddModelNonSearchable('Password', "password", 80,"center");
$grid->AddModelNonSearchable('Action', "action", 80,"center");
/*
$curLoggedUserRND = UserAuth::getDBSuffix();

if ($curLoggedUserRND == 'AC') {
    $grid->AddModelNonSearchable('Login Status', "active", 80,"center");
}
*/
include('view/grid-tool-tips.php');
$grid->addModelTooltips($tooltips);

?>

<?php //echo print_r($_SESSION);?>
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
</script>
