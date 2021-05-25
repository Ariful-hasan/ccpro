<link href="css/report.css" rel="stylesheet" type="text/css">
<style type="text/css">
.copyText{
	font-weight: bold;
	text-align: left;
	padding: 0 10px;
}
.selectTitle{
	color: #000000;
	font-weight: bold;
    margin: 10px 0 0 15px;
    text-align: left;
}
.disTitle{
	color: #328AA4;
	font-weight: bold;
}
</style>
<?php $_controllerName = $request->getControllerName(); ?>
<script type="text/javascript">
function beforeSubmit(){
	var imgSrc = "<img src='js/facebox/loading.gif' border='0' />";
	document.getElementsByClassName("form_column_submit")[0].innerHTML = imgSrc;
}
function onloadPage(){
	document.getElementById('preloader').style.display = 'none';
}
function copyCode(did)
{
	var didText = document.getElementById("did_"+did).value;
	if (did.length > 0) {
		window.location = "<?php echo $this->url("task=$_controllerName&act=confirm&sid=".$sid."&didfrom=".$disposId."&cpfrom=".$copyText."&didto=");?>" + did + "&cp2txt="+urlencode(didText);
	}
}
function urlencode(str) {
	str = (str + '').toString();

	return encodeURIComponent(str)
	.replace(/!/g, '%21')
	.replace(/'/g, '%27')
	.replace(/\(/g, '%28')
	.replace(/\)/g, '%29')
	.replace(/\*/g, '%2A')
	.replace(/%20/g, '+');
}
window.onload = onloadPage;
</script>
<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<div id='preloader'><img src='js/facebox/loading.gif' border='0' /></div>
<?php 
if (isset($pagepart) && $pagepart == "skills"){ ?>
<div class="copyText">Copy <span class="disTitle"><?php echo $copyText; ?></span> to:</div>
<div class="selectTitle">Select Skill</div>
<table class="report_table" width="98%" border="0" align="center" cellpadding="1" cellspacing="1">
<tr class="report_row_head">
	<td>SL</td>
	<td>Name</td>
	<?php if ($this->email_module):?>
	<td>Skill Type</td>
	<?php endif;?>
	<td>Status</td>
</tr>

<?php if (is_array($skills)):?>

<?php
	$i = 0;
	foreach ($skills as $skill):
		if (!$this->email_module && $skill->qtype == 'E') continue;
		$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
?>
<tr class="<?php echo $_class;?>">
	<td align="center">&nbsp;<?php echo $i;?></td>
	<td align="left">
	<?php if (UserAuth::hasRole('admin')):?>
		<a href="<?php echo $this->url('task='.$request->getControllerName()."&act=dispositions&skillid=".$skill->skill_id."&did=".$disposId."&cptxt=".urlencode($copyText));?>"><?php echo $skill->skill_name;?></a>
	<?php else:?>
		<?php echo $skill->skill_name;?>
	<?php endif;?>
	</td>
	<?php if ($this->email_module):?>
	<td align="left">&nbsp;
	<?php 
	if ($skill->qtype == 'E') echo 'Email';
	elseif ($skill->qtype == 'C') echo 'Chat';
	else echo 'Voice';
	?>
	</td>
	<?php endif;?>
	<td align="left">&nbsp;
	<?php
		if ($skill->active == 'Y') {
			echo 'Active';
		} else {
			echo '<font class=\"inactive\">Inactive</font>';
		}
	?>
	</td>
</tr>
<?php endforeach;?>
<?php else:?>
<tr class="report_row_empty">
	<td colspan="10">No Skill Found!</td>
</tr>
<?php endif;?>
</table>
<?php }elseif (isset($pagepart) && $pagepart == "dispositions"){
$colspan = 4;
?>
<div class="copyText">Copy <span class="disTitle"><?php echo $copyText; ?></span> to:</div>
<div class="selectTitle">Select Disposition</div>
<?php 
if (is_array($dispositions)):
?>
<table class="report_extra_info" width="96%">
<tr>
	<td align="left"><?php echo $tabTitle; ?></td>
	<td align="right">
		<?php
        	echo 'Record(s) ' . $pagination->getCurrentRecordsIndex() . ' of <b>' . $pagination->num_records . '</b> &nbsp;::&nbsp; ' . 
				'Page <b>' . $pagination->current_page . '</b> of <b>' . $pagination->getTotalPageCount() . '</b>';
		?>
	</td>
</tr>
</table>
<?php endif;?>
<table class="report_table" border="0" align="center" cellpadding="1" cellspacing="1">
<tr class="report_row_head">
	<td class="cntr">SL</td>
	<td>Title</td>
	<td class="cntr">Disposition Code</td>
</tr>

<?php 
if (count($dispositions) > 0):

	$i = $pagination->getOffset();
	$parents = array();
	foreach ($dispositions as $service):
		$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
		$parent = '';
		if (!empty($service->parent_id)) {
			if (isset($parents[$service->parent_id])) {
				$parent = $parents[$service->parent_id];
			} else {
				$parent = $dc_model->getDispositionPath($service->disposition_id);
				$parents[$service->parent_id] = $parent;
			}
		}
?>
<tr class="<?php echo $_class;?>">
	<td class="cntr">&nbsp;<?php echo $i;?></td>
	<td align="left">&nbsp;
		<a href="#" onClick="copyCode('<?php echo $service->disposition_id;?>');"><?php if (!empty($parent)) echo $parent . ' -> ';?><?php echo $service->title;?></a>
		<input type="hidden" id="did_<?php echo $service->disposition_id; ?>" value="<?php if (!empty($parent)) echo $parent . ' -> ';?><?php echo $service->title;?>">
	</td>
	<td class="cntr">&nbsp;<?php echo $service->disposition_id;?></td>
</tr>
<?php endforeach;?>
</table>
<table class="report_extra_info" width="96%">
<tr>
	<td align="right"><?php echo $pagination->createLinks();?></td>
</tr>
<?php else:?>
<tr class="report_row_empty">
	<td colspan="<?php echo $colspan;?>">No record found!</td>
</tr>
<?php endif;?>
</table>
<?php if (count($dispositions) <= 0){echo "<br>";} ?>
<div>
	<a class="form_submit_button" href="<?php echo $this->url("task=$_controllerName&act=confirm2skill&sid=$sid&didfrom=".$disposId."&cpfrom=".urlencode($copyText)."&cp2txt=".urlencode($copyToTxt));?>">Copy to this Skill</a>  &nbsp; &nbsp;
	<a class="form_submit_button" href="<?php echo $this->url("task=$_controllerName&did=".$disposId."&cptxt=".urlencode($copyText));?>">Back to Skill List</a>
</div>
<?php }elseif (isset($pagepart) && $pagepart == "confirmation"){ ?>

<table class="form_table" style="margin-top: 25px;">
	<tr class="form_row_alt">
		<td class="form_column_caption" style="text-align:center; font-size:12px; padding: 8px 0;">
			Are you sure to copy <span class="disTitle"><?php echo $copyFromTxt."</span> <br>to <span class='disTitle'>".$copyToTxt; ?></span>?
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_submit" style="text-align:center;padding:20px 0;">
			<a class="form_submit_button" id="copynow" onclick="javascript:beforeSubmit()" href="<?php echo $this->url("task=$_controllerName&act=copynow&skillid=$sid&didto=".$didTo."&didfrom=".$didFrom."&cpfrom=".urlencode($copyFromTxt)."&cp2txt=".urlencode($copyToTxt));?>">Copy</a>  &nbsp; &nbsp;
            <a class="form_submit_button" id="back" href="<?php echo $this->url("task=$_controllerName&act=dispositions&skillid=$sid&did=".$disposId."&cptxt=".urlencode($copyToTxt));?>">Back</a>  &nbsp; &nbsp;
            <input class="form_submit_button" type="button" value="Cancel" name="submitcancel" onclick="parent.$.colorbox.close();" />           
		</td>
	</tr>
</table>
<?php }elseif (isset($pagepart) && $pagepart == "confirm2skill"){ ?>

<table class="form_table" style="margin-top: 25px;">
	<tr class="form_row_alt">
		<td class="form_column_caption" style="text-align:center; font-size:12px; padding: 8px 0;">
			Are you sure to copy <span class="disTitle"><?php echo $copyFromTxt."</span> <br>to skill <span class='disTitle'>".$copyToTxt; ?></span>?
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_submit" style="text-align:center;padding:20px 0;">
			<a class="form_submit_button" id="copynow" onclick="javascript:beforeSubmit()" href="<?php echo $this->url("task=$_controllerName&act=copytoskill&skillid=$sid&didfrom=".$didFrom."&cpfrom=".urlencode($copyFromTxt)."&cp2txt=".urlencode($copyToTxt));?>">Copy</a>  &nbsp; &nbsp;
            <a class="form_submit_button" id="back" href="<?php echo $this->url("task=$_controllerName&did=".$didFrom."&cptxt=".urlencode($copyFromTxt));?>">Back</a>  &nbsp; &nbsp;
            <input class="form_submit_button" type="button" value="Cancel" name="submitcancel" onclick="parent.$.colorbox.close();" />           
		</td>
	</tr>
</table>

<?php }else { ?>
<table class="form_table" style="margin-top: 25px;">
	<tr class="form_row_alt">
		<td class="form_column_caption" style="text-align:center; font-size:12px; padding: 8px 0;">
			Email disposition code <span class="disTitle"><?php echo $copyFromTxt; ?></span> copy to <span class="disTitle"><?php echo $copyToTxt; ?></span>.
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_submit" style="text-align:center;padding:20px 0;">
			<input class="form_submit_button" type="button" value="Close" name="submitcancel" onclick="parent.$.colorbox.close();" />           
		</td>
	</tr>
</table>
<?php } ?>