<link href="css/form.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/ofc/swfobject.js"></script>
<script type="text/javascript">
var tid = '';
var reValidDate = "<?php echo $rday_date; ?>";
var repErrMsg = "<?php echo $repDayMsg; ?>";
if(repErrMsg == "" || repErrMsg == null){
	repErrMsg = "Report date range exceed!";
}
<?php if (isset($day)):?>
function reload_chart()
{
	tmp = findSWF("my_chart");
	var time_obj = new Date();
	var sdate = document.getElementById('sdate').value;
	if(reValidDate != "" && sdate < reValidDate){
		alert(repErrMsg);
		return false;
	}
	tid = document.getElementById('tid').value;
	//document.write("<?php echo $this->url('task=gdata&act=channelday&day=');?>"+sdate+"&tid="+tid+"&tstamp="+time_obj.getTime());
	x = tmp.reload("<?php echo $this->url('task=gdata&act=channelday&day=');?>"+sdate+"&tid="+tid+"&tstamp="+time_obj.getTime());
}
<?php else:?>
function reload_chart()
{
	tmp = findSWF("my_chart");
	var time_obj = new Date();
	var syear = document.getElementById('year').value;
	var smonth = document.getElementById('month').value;
	var yyyymm = syear + smonth;
	if(reValidDate != "" && yyyymm < reValidDate){
		alert(repErrMsg);
		return false;
	}
	tid = document.getElementById('tid').value;
	x = tmp.reload("<?php echo $this->url('task=gdata&act=channelmonth&month=');?>"+smonth+"&year="+syear+"&tid="+tid+"&timestamp="+time_obj.getTime());
}
<?php endif;?>

function findSWF(movieName) {
	if (navigator.appName.indexOf("Microsoft")!= -1) {
		return window[movieName];
	} else {
		return document[movieName];
	}
}

<?php if (isset($day)):?>
swfobject.embedSWF("swf/open-flash-chart.swf", "my_chart", "96%", "350", "9.0.0", "expressInstall.swf",
	{"data-file":"<?php echo urlencode($this->url('task=gdata&act=channelday&day='.$day.'&tid='.$tid.'&tstamp='.time()));?>"});
<?php else:?>
swfobject.embedSWF("swf/open-flash-chart.swf", "my_chart", "96%", "350", "9.0.0", "expressInstall.swf",
  {"data-file":"<?php echo urlencode($this->url('task=gdata&act=channelmonth&month='.$month.'&year='.$year.'&tid='.$tid.'&tstamp='.time()));?>"});
<?php endif;?>

</script>

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_search" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>" onsubmit="reload_chart();return false;">
<table class="form_table">
	<tr class="form_row">
		<td>
			Trunk: <select name="tid" id="tid">
			<?php
				echo "<option value=\"\"";
				if (empty($tid)) echo ' selected';
				echo ">All</option>";

				if (is_array($trunk_options)) {
					foreach ($trunk_options as $trunkid => $trunkname) {
						echo "<option value=\"$trunkid\"";
						if ($tid == $trunkid) echo ' selected';
						echo ">$trunkname</option>";
					}
				}
			?>
			</select> &nbsp;
			<?php if (isset($day)):?>
			Date: <input type="text" name="sdate" id="sdate" value="<?php echo $dateinfo->sdate;?>" size="12" maxlength="10" />
            <?php else:?>
<?php
		$month_options = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', 
			'06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October',
			'11' => 'November', '12' => 'December');
		$thisyear = date('Y');
		$prevyear = date('Y') - 5;
		
		$year_options = array_reverse(range($prevyear, $thisyear));
		$topBar = "Month: <select name=\"month\" id=\"month\">";
		foreach ($month_options as $key=>$value) {
			$topBar .= "<option value=\"$key\"";
			if ($key==$month) $topBar .= " selected";
			$topBar .= ">$value</option>";
		}
		$topBar .= "
	        </select>
    	    &nbsp; <select name=\"year\" id=\"year\">
		";
		foreach ($year_options as $yr) {
			$topBar .= "<option value=\"$yr\"";
			if ($yr==$year) $topBar .= " selected";
			$topBar .= ">$yr</option>";
		}
		$topBar .= "
			</select>
    	    			 &nbsp; ";
		echo $topBar;
?>
            <?php endif;?>
			&nbsp; <input class="form_submit_button" type=submit name=search value="Search">
		</td>										
	</tr>
</table>
</form>

<table class="form_table"><tr class="form_row"><td>
<div id="my_chart"></div>
</td></tr></table>