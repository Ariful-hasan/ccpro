<link rel="stylesheet" href="css/form.css" type="text/css">
<script src="js/jquery.js" type="text/javascript"></script>
<script>

window.onload=function(){
	var opener = parent;//window.opener;
	if (opener) {
		var oDom = opener.document;
		var elem_val = oDom.getElementById("agents").value;
		
		var ags = elem_val.split(',');
		for(var key in ags) {
			//alert(ags[key]);
			if (ags[key].length > 0) {
				//var agname = (typeof available_agents[ags[key]]=='undefined') ? ags[key] : available_agents[ags[key]];
				//$("#div_agent").append('<div class="agsel" id="a' + ags[key] + '">' + agname + '</div>');
				$('#c'+ags[key]).attr('checked', true);
			}
		}
		//if (elem) {
			//var val = elem.value;
		//}
		//var agents = opener.agents_avaliables;
		
	}
};

$(function()
{
	 $('#check_all').click(function () {
		$('.chk_req').attr('checked', this.checked);
		show_move_button();
    });
	
	$('.chk_req').change(function() {
		show_move_button();
	});
});

function show_move_button()
{
	if ($("input[name='chk_agents[]']:not(:checked)").length > 0) {
		$('#check_all').attr('checked', false);
	} else {
		$('#check_all').attr('checked', true);
	}
}


function selectAgents()
{
	var agents_vls = '';
	var oDom = parent.document;
	var elem = oDom.getElementById("agents");
	$("input[name='chk_agents[]']").each(function(i,e) {
		if ($(e).is(':checked')) {
			var comma = agents_vls.length===0?'':',';
			agents_vls += (comma+e.value);
		}
	});
	//elem = agents_vls;
	//alert();
	parent.display_agents(agents_vls);

	//if (window.opener.progressWindow) {
		//window.opener.progressWindow.close();
	//}
	//window.close();
	parent.$.colorbox.close();
}
</script>

<?php
$num_cols_per_row = 3;

?>
<table class="form_table">

<tr class="form_row_head">
	<td colspan="<?php echo $num_cols_per_row;?>" align="left"><?php if (is_array($agent_options)):?><input type="checkbox" id="check_all" name="check_all" value="Y" /><label for="check_all"> Select All</label><?php else:?>&nbsp;<?php endif;?></td>
</tr>

<?php
	if (is_array($agent_options)) {
	
		$cols = 0;
		$rows = 0;
		//$class = '';
		foreach ($agent_options as $agentinfo) {
			if ($cols%$num_cols_per_row == 0) {
				$class = $rows %2 == 0 ? 'report_row' : 'report_row_alt';
				echo '<tr class="' . $class . '">';
				$rows++;
			}
			echo "<td><input type=\"checkbox\" class=\"chk_req\" id=\"c$agentinfo->agent_id\" name=\"chk_agents[]\" value=\"$agentinfo->agent_id\" /> <label for=\"c$agentinfo->agent_id\">[$agentinfo->agent_id] $agentinfo->nick - ";
			if ($agentinfo->busy_status == 'A') echo 'Available'; else if ($agentinfo->busy_status == 'B') echo 'Busy'; else if (empty($agentinfo->busy_status)) echo 'Unavailable';
			echo "</label></td>";
			$cols++;
			if ($cols%$num_cols_per_row == 0) {
				echo '</tr>';
			}
		}
		
		while($cols%$num_cols_per_row != 0) {
			echo "<td>&nbsp;</td>";
			$cols++;
		}
		
	} else {
		echo '<tr calss="report_row_empty"><td colspan="' . $num_cols_per_row . '">No agent found</td></tr>';
	}
?>



</table>
<?php
if (is_array($agent_options)) {
	echo '<br /><input class="form_submit_button" type="button" value="Use Selected Agent(s)" name="submitagent" onclick="javascript:selectAgents();" /> &nbsp; 
	<input class="form_submit_button" type="button" value="Cancel" name="submitcancel" onclick="parent.$.colorbox.close();" />
	';
}
?>