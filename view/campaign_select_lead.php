<script src="js/jquery.js" type="text/javascript"></script>
<script>

window.onload=function(){
	var opener = window.opener;
	if (opener) {
		var oDom = opener.document;
		//var elem_val = oDom.getElementById("agents").value;
		var leads_sel = opener.leads_global;

		for (var key in leads_sel) {
		
			if (leads_sel[key].length > 0) {
				$('#c'+leads_sel[key]).attr('checked', true);
			}
		}
		show_move_button();
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
	if ($("input[name='chk_leads[]']:not(:checked)").length > 0) {
		$('#check_all').attr('checked', false);
	} else {
		$('#check_all').attr('checked', true);
	}
}


function selectLeads()
{
	var agents_vls = '';
	//var oDom = opener.document;
	//var elem = oDom.getElementById("agents");
	var leads = new Array();
	$("input[name='chk_leads[]']").each(function(i,e) {
		if ($(e).is(':checked')) {
			var comma = agents_vls.length===0?'':',';
			agents_vls += (comma+e.value);
			//leads[e.value] = e.value;
		}
	});
	//elem = agents_vls;
	//alert();
	opener.display_leads(agents_vls);

	if (window.opener.progressWindow) {
		window.opener.progressWindow.close();
	}
	window.close();
}
</script>

<?php
$num_cols_per_row = 2;

?>
<table class="report_table" width="100%" border="0" align="center" cellpadding="1" cellspacing="1">

<tr class="report_row_head">
	<td colspan="<?php echo $num_cols_per_row;?>">Check agents <?php if (is_array($campaign_leads)):?><input type="checkbox" id="check_all" name="check_all" value="Y" /><?php else:?>&nbsp;<?php endif;?></td>
</tr>

<?php
	if (is_array($campaign_leads)) {
	
		$cols = 0;
		$rows = 0;
		//$class = '';
		foreach ($campaign_leads as $leadinfo) {
			if ($cols%$num_cols_per_row == 0) {
				$class = $rows %2 == 0 ? 'report_row' : 'report_row_alt';
				echo '<tr class="' . $class . '">';
				$rows++;
			}
			echo "<td><input type=\"checkbox\" class=\"chk_req\" id=\"c$leadinfo->lead_id\" name=\"chk_leads[]\" value=\"$leadinfo->lead_id\" /> <label for=\"c$leadinfo->lead_id\">$leadinfo->title</label></td>";
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
		echo '<tr calss="report_row_empty"><td colspan="' . $num_cols_per_row . '">No lead found</td></tr>';
	}
?>



</table>
<?php
if (is_array($campaign_leads)) {
	echo '<br /><input class="form_submit_button" type="button" value="Use Selected Lead(s)" name="submitagent" onclick="javascript:selectLeads();" /> &nbsp; 
	<input class="form_submit_button" type="button" value="Cancel" name="submitcancel" onclick="window.close();" />
	';
}
?>