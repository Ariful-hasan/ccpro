<link rel="stylesheet" href="css/report.css" type="text/css">
<table class="report_table" width="78%" border="0" align="center" cellpadding="1" cellspacing="1">
	<tr class="report_row_head">
		<td>Priority</td>
		<td>CTI action</td>	
		<td>Service/IVR</td>
        <td class="cntr">Edit</td>
	</tr>

	<tr class="report_row">
		<td align="left">&nbsp;<b>High</b></td>
		<td align="left">&nbsp;
			<?php
			if (!empty($priority_policy)) {
				if ($priority_policy->high->cti_action == 'SQ') {
    	        	echo 'Skill';
        	    } else if ($priority_policy->high->cti_action == 'IV') {
            		echo 'IVR';
				} else {
            		echo '-';
				}
            } else {
				echo '-';
			}
			?>
        </td>
		<td align="left">&nbsp;
			<?php if (!empty($priority_policy)) echo $priority_policy->high->value;?>
        </td>
		<td class="cntr">
			<a class="btn-link" href="<?php echo $this->url('task='.$request->getControllerName()."&act=update&plevel=high");?>">Edit</a>
        </td>
	</tr>
	<tr class="report_row_alt">
		<td align="left">&nbsp;<b>Regular</b></td>
		<td class="cntr" colspan="3"><b>Regular Setting</b>
        </td>
	</tr>
	<tr class="report_row">
		<td align="left">&nbsp;<b>Low</b></td>
		<td align="left">&nbsp;
			<?php
			if (!empty($priority_policy)) {
				if ($priority_policy->low->cti_action == 'SQ') {
    	        	echo 'Skill';
        	    } else if ($priority_policy->low->cti_action == 'IV') {
            		echo 'IVR';
        	    } else if ($priority_policy->low->cti_action == 'BL') {
            		echo 'Call Block';
				} else {
            		echo '-';
				}
            } else {
				echo '-';
			}
			?>
        </td>
		<td align="left">&nbsp;
			<?php if (!empty($priority_policy)) echo $priority_policy->low->value;?>
        </td>
		<td class="cntr">
			<a class="btn-link" href="<?php echo $this->url('task='.$request->getControllerName()."&act=update&plevel=low");?>">Edit</a>
        </td>
	</tr>
</table>