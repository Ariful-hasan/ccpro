<link rel="stylesheet" href="css/report.css" type="text/css">
<script>
function playFile(evt)
{
	$.post('cc_web_agi.php', { 'page':'CDR', 'agent_id':'<?php echo UserAuth::getCurrentUser();?>', 'option':evt },
		function(data, status) {
			if (status == 'success') {
				if(data != 'Y') alert(data);
			} else {
				alert("Failed to communicate!");
			}
	});
}
</script>
<table width="100%" align="center" border="0" class="profile-details" cellpadding="1">
<?php if (!is_array($records)) { ?><tr><td colspan="4">&nbsp;</td></tr><?php } ?>
<tr><td colspan="4" align="center">
<?php
if (is_array($records)) {
?>
<table class="report_extra_info" style="width:96%;">
<tr>
	<td><small>
		<?php
        	echo 'Record(s) ' . $pagination->getCurrentRecordsIndex() . ' of <b>' . $pagination->num_records . '</b> &nbsp;::&nbsp; ' . 
				'Page <b>' . $pagination->current_page . '</b> of <b>' . $pagination->getTotalPageCount() . '</b>';
		?></small>
	</td>
</tr>
</table>

<table class="report_table">
<tr class="report_row_head"><td class="cntr">SL</td><td class="cntr">Time</td><td>Disposition</td><td>Agent</td><td>Note</td><td class="cntr">Audio</td></tr>
<?php
$i = $pagination->getOffset();
$log = new stdClass();
foreach ($records as $rec) {
$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
?>
<tr class="<?php echo $_class;?>"><td class="cntr"><?php echo $i;?></td><td class="cntr"><?php echo date("Y-m-d H:i:s", $rec->tstamp);?></td><td align="left">&nbsp;<?php echo $rec->title;?></td><td><?php echo $rec->agent_id . ' ['.$rec->nick.']';?></td><td align="left">&nbsp;<?php echo $rec->note;?></td><td class="cntr"><?php
		if (!empty($rec->callid)) {
			$file_timestamp = substr($rec->callid, 0, 10);
			$yyyy = date("Y", $file_timestamp);
			$yyyy_mm_dd = date("Y_m_d", $file_timestamp);
			$sound_file_gsm = $this->voice_logger_path . "$yyyy/$yyyy_mm_dd/" . $rec->callid . ".gsm";
			if (file_exists($sound_file_gsm) && filesize($sound_file_gsm) > 0) {
				$log->audio = "<a href=\"#\" onClick=\"playFile('$rec->callid')\"><img src=\"image/play2.gif\" title=\"gsm\" border=0></a>";
				if ($is_voice_file_downloadable) {
					$log->audio .= " <a target=\"blank\" href=\"$sound_file_gsm\"><img src=\"image/play_in_browser2.gif\" title=\"Play in browser\" border=0 width=\"15\"></a>";
				}

			} else {
				$log->audio = "NONE";
			}
		} else {
			$log->audio = "NONE";
		}
    	echo $log->audio;
	?>
	</td></tr>
<?php
}
?>
</table>
<table class="report_extra_info" style="width:96%;">
<tr>
	<td><small>
	<?php echo $pagination->createLinks();?>
	</small></td>
</tr>
</table>
<?php
} else {
	echo '<b>No record found !!</b>';
}
?>
</td></tr>

</table>
