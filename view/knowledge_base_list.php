<link href="css/report.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="js/lightbox/css/colorbox.css" />
<script src="js/lightbox/js/jquery.colorbox-min.js"></script>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$(".agents").colorbox({
		onClosed:function(){ window.location = window.location.href;},
		iframe:true, width:"800", height:"100%", overlayClose:false
	});
});
</script>
<table class="report_table" width="80%" border="0" align="center" cellpadding="1" cellspacing="1">

<tr class="report_row_head">
	<td>SL</td>
	<td>Knowledge List</td>
	<?php if ($this->email_module):?>
	<td>Type</td>
	<?php endif;?>
</tr>

<?php if (is_array($skills)):?>

<?php
	$i = 0;
	foreach ($skills as $skill):
		//if (!$this->email_module && $skill->qtype == 'E') continue;
		$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
?>
<tr class="<?php echo $_class;?>">
	<td align="center">&nbsp;<?php echo $i;?></td>
	<td align="left">
		<a href="<?php echo $this->url('task='.$request->getControllerName()."&sid=".$skill->skill_id);?>"><?php echo $skill->skill_name;?></a>
	</td>
	<?php if ($this->email_module):?>
	<td align="left">&nbsp;<?php echo ($skill->qtype == 'E') ? 'Email' : 'Voice';?></td>
	<?php endif;?>
</tr>
<?php endforeach;?>
<?php else:?>
<tr class="report_row_empty">
	<td colspan="10">No Skill Found!</td>
</tr>
<?php endif;?>
</table>
