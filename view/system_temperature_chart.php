<link href="css/form.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/ofc/swfobject.js"></script>
<script type="text/javascript">
<?php if (isset($day)):?>
function reload_chart()
{
	tmp = findSWF("my_chart");
	tmp2 = findSWF("my_chart2");
	var time_obj = new Date();
	var sdate = document.getElementById('sdate').value;
	x = tmp.reload("<?php echo $this->url('task=system&act=tempdata&opt='.$opt.'&day=');?>"+sdate+"&tstamp="+time_obj.getTime());
	x2 = tmp2.reload("<?php echo $this->url('task=system&act=loaddata&opt='.$opt.'&day=');?>"+sdate+"&tstamp="+time_obj.getTime());
}
<?php else:?>
function reload_chart()
{
	tmp = findSWF("my_chart");
	tmp2 = findSWF("my_chart2");
	var time_obj = new Date();
	var syear = document.getElementById('year').value;
	var smonth = document.getElementById('month').value;
	x = tmp.reload("<?php echo $this->url('task=system&act=tempdata&opt='.$opt.'&month=');?>"+smonth+"&year="+syear+"&timestamp="+time_obj.getTime());
	x2 = tmp2.reload("<?php echo $this->url('task=system&act=loaddata&opt='.$opt.'&month=');?>"+smonth+"&year="+syear+"&timestamp="+time_obj.getTime());
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
	{"data-file":"<?php echo urlencode($this->url('task=system&act=tempdata&opt='.$opt.'&day='.$day.'&tstamp='.time()));?>"});
swfobject.embedSWF("swf/open-flash-chart.swf", "my_chart2", "96%", "350", "9.0.0", "expressInstall.swf",
	{"data-file":"<?php echo urlencode($this->url('task=system&act=loaddata&opt='.$opt.'&day='.$day.'&tstamp='.time()));?>"});

<?php else:?>
swfobject.embedSWF("swf/open-flash-chart.swf", "my_chart", "96%", "350", "9.0.0", "expressInstall.swf",
  {"data-file":"<?php echo urlencode($this->url('task=system&act=tempdata&opt='.$opt.'&month='.$month.'&year='.$year.'&tstamp='.time()));?>"});
swfobject.embedSWF("swf/open-flash-chart.swf", "my_chart2", "96%", "350", "9.0.0", "expressInstall.swf",
	{"data-file":"<?php echo urlencode($this->url('task=system&act=loaddata&opt='.$opt.'&month='.$month.'&year='.$year.'&tstamp='.time()));?>"});
<?php endif;?>

</script>
<form name="frm_search" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>" 
	onsubmit="reload_chart();return false;">
<table class="form_table" border="0" cellpadding="0" cellspacing="0" valign="top">
	<tr class="form_row">
		<td>
			<?php if (isset($day)):?>
			Date: <input type="text" name="sdate" id="sdate" value="<?php echo $dateinfo->sdate;?>" size="12" maxlength="10" /> &nbsp;
            <?php else:?>
<?php
		$month_options = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', 
			'06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October',
			'11' => 'November', '12' => 'December');
		$thisyear = date('Y');
		$prevyear = date('Y') - 1;
		$year_options = array($thisyear, $prevyear);
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
			<input class="form_submit_button" type=submit name=search value="Search">
		</td>										
	</tr>
</table>
</form>
<br />
<div style="text-align:left; padding-left: 20px;">
<span style="font-size:15px; font-weight:bold;">Current Temperature:</span><br />
<?php
if (is_array($current_temperatures)) {
	foreach($current_temperatures as $ctid=>$ctval) 
		echo'<b>' . $ctval->item . ': ' . $ctval->value . ' ' . $ctval->unit . '</b><br />';
}
?>
</div>


<table class="form_table" border="0" cellpadding="0" cellspacing="0" valign="top"><tr class="form_row"><td>
<div id="my_chart"></div>
</td></tr></table>
<br /><br /><br />
<div style="text-align:left; padding-left: 20px;">
<span style="font-size:15px; font-weight:bold;">Current Load:</span><br />
<?php
if (is_array($current_loads)) {
	foreach($current_loads as $ctid=>$ctval) 
		echo'<b>' . $ctval->item . ': ' . $ctval->value . ' ' . $ctval->unit . '</b><br />';
}
?>
</div><br />
<table class="form_table" border="0" cellpadding="0" cellspacing="0" valign="top"><tr class="form_row"><td>
<div id="my_chart2"></div>
</td></tr></table>
<br />