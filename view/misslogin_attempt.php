<?php
	include_once "lib/jqgrid.php";
	$grid = new jQGrid();
	//$grid->caption = "Agent List";
	$grid->url =  $this->url('task=get-tools-data&act=MissloginAttempt');
	$grid->width="auto";//$grid->minWidth = 800;
	$grid->height = "auto";//390;
	$grid->rowNum = 20;
	$grid->pager = "#pagerb";
	$grid->container = ".content-body";
	//$grid->hidecaption=true;
	$grid->CustomSearchOnTopGrid=false;
	$grid->multisearch=false;	
	//$grid->AddTitleRightHtml('<a class="btn btn-xs btn-info" href="'."add-faq-item".'" ><i class="fa fa-plus"></i>Add New</a>');
	$grid->AddModelNonSearchable("ID", "agent_id", 80,"center");
	$grid->AddModelNonSearchable("Name", "nick", 80,"center");
	$grid->AddModelNonSearchable("Attempt(s)", "tried", 80,"center");
	$grid->AddModelNonSearchable("IP", "ip", 80,"center");
	$grid->AddModelNonSearchable("Unlock", "action", 80,"center");
	$grid->show("#searchBtn");
?>
<table class="report_extra_info">
<tr>
	<td align="left">
		<?php
		if (is_array($mislogins)) {
			$session_id = session_id();
			$download = md5($pageTitle.$session_id);
			$dl_link = "download=$download";
	    	$url = $pagination->base_link . "&$dl_link";
			echo '<img class="bottom" height="11" width="20" border="0" src="image/down_excel.gif"/><a class="btn-link" href="' . $url . '">download current records</a>';
		}
		echo '&nbsp; &nbsp;' . $pagination->createLinks();
		?>
	</td>
</tr>
</table>

<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>




<?php
if (isset($_REQUEST['download'])) {
	require_once('lib/DownloadHelper.php');
	$dl_helper = new DownloadHelper($pageTitle, $this);
	$dl_helper->create_file('failed_login_attempts.csv');
	$dl_helper->write_in_file("SL,ID,Name,Attempt(s),IP\n");

	$loginAttempts = $pass_model->getMissLogins();

	if (is_array($loginAttempts)) {
		$i = 0;
		foreach ($loginAttempts as $attempt) {
			$i++;			
			$dl_helper->write_in_file("$i,$attempt->agent_id,$attempt->nick,$attempt->tried,$attempt->ip\n");
		}
	}
	$dl_helper->download_file();
	exit;
}
?>