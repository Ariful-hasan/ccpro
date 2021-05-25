<link rel="stylesheet" href="css/form.css" type="text/css">
<link rel="stylesheet" href="css/report.css" type="text/css">
<link rel="stylesheet" href="js/lightbox/css/colorbox.css" />

<script src="js/lightbox/js/jquery.colorbox-min.js"></script>
<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript">
$.validator.methods.ip_port = function(value, element, param) {
	var ipp = /^\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3}(\:\d{1,5})?$/;
	if (value.match(ipp)) return true;
	return false;
};
	
jQuery(document).ready(function($) {
	$(".cli").colorbox({
		onClosed:function(){ window.location = window.location.href;},
		iframe:true, width:"600", height:"420"
	});
	
	$( "#frm_trunk" ).validate({
		rules: {
			label: {
				required: true,
			},
			ip: {
				ip_port: true
			},
			prefix_len: {
				digits: true,
			},
			min_number_len: {
				digits: true,
			},
			max_number_len: {
				digits: true,
			},
			ch_capacity: {
				digits: true,
			},
			priority: {
				digits: true,
			}
		},
		messages: {
			label: {
				required: "Provide Label"
			},
			ip: {
				ip_port: "Provide valid IP address"
			},
			prefix_len: {
				digits: "Only digits allowed",
			},
			min_number_len: {
				digits: "Only digits allowed",
			},
			max_number_len: {
				digits: "Only digits allowed",
			},
			ch_capacity: {
				digits: "Only digits allowed",
			},
			priority: {
				digits: "Only digits allowed",
			}
		}
	});
});

function displayIP(dialtype)
{
	if (dialtype == 'SIP') {
		$('#trunk_ip').show();
		$('#ch_cap').removeClass('form_row_alt').addClass('form_row');
		$('#trunk_pr').removeClass('form_row').addClass('form_row_alt');
		$('#tr_submit').removeClass('form_row_alt').addClass('form_row');
	} else {
		$('#trunk_ip').hide();
		$('#ch_cap').removeClass('form_row').addClass('form_row_alt');
		$('#trunk_pr').removeClass('form_row_alt').addClass('form_row');
		$('#tr_submit').removeClass('form_row').addClass('form_row_alt');
	}
}
var winHandle;
	function requestNewWindow( str_URL, width, height )
	{
		if(winHandle)	winHandle.close();
		var winl = (screen.width - width) / 2;
		var wint = (screen.height - height) / 2;
		winHandle = window.open( str_URL, 'str_winName1','toolbar=no,menubar=no,left='+winl+',top='+wint+',status=no,location=no,scrollbars=1,resizable=no,width='+width+',height='+height);
		winHandle.focus();
		return false;
	}
$(document).ready(function() {
	$('.del').click(function(event) {
		event.preventDefault();
		var did = $(this).attr('id');
		var dname = $(this).attr('name');
		if (confirm("Are you sure to delete number " + dname + "?")) {
			//alert("OK");
			$.ajax({
				url: '<?php echo $this->url('task='.$request->getControllerName()."&act=delcli&id=");?>' + did,
				success: function(data) {
					//$('.result').html(data);
					//alert('Load was performed.');
					if (data == 'Y') {
						window.location.href = window.location.href;
					}
				}
			});
		}
	});
});
</script>

<?php
	$tr1 = 'form_row';
	$tr2 = 'form_row_alt';
	
	if ($trunk->trunk_type == 'SS7')
	{
		$tr1 = 'form_row_alt';
		$tr2 = 'form_row';
	}
?>
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_trunk" id="frm_trunk" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="trunkid" value="<?php if (isset($trunkid)) echo $trunkid;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Trunk Information</td>
	</tr>
	
	<tr class="form_row_alt">
		<td class="form_column_caption" width="33%">Label:</td>
		<td>
			<input type="text" name="label" size="30" maxlength="12" value="<?php echo $trunk->label;?>" />
		</td>
	</tr>
	<tr class="form_row_alt" id="trunk_ip">
		<td class="form_column_caption">IP: </td>
		<td>
        	<input type="text" name="ip" size="30" maxlength="21" value="<?php echo $trunk->ip;?>" />
		</td>
	</tr>

	<tr class="<?php echo $tr1;?>" id="ch_cap">
		<td class="form_column_caption">Channel Capacity: </td>
		<td>
        	<input type="text" name="ch_capacity" size="30" maxlength="3" value="<?php echo $trunk->ch_capacity;?>" />
		</td>
	</tr>
	<tr>
		<td class="form_column_caption">Priority: </td>
		<td>
        	<input type="text" name="priority" size="30" maxlength="3" value="<?php echo $trunk->priority;?>" />
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="33%">Prefix Length:</td>
		<td>
			<input type="text" name="prefix_len" size="30" maxlength="1" value="<?php echo $trunk->prefix_len;?>" /> 
		</td>
	</tr>
	<tr class="<?php echo $tr2;?>" id="trunk_pr">
		<td class="form_column_caption">Dial in Prefix: </td>
		<td>
        	<input type="text" name="dial_in_prefix" size="30" maxlength="8" value="<?php echo $trunk->dial_in_prefix;?>" />
		</td>
	</tr>
	<tr class="<?php echo $tr1;?>" id="trunk_opr">
		<td class="form_column_caption">Dial out Prefix: </td>
		<td>
        	<input type="text" name="dial_out_prefix" size="30" maxlength="8" value="<?php echo $trunk->dial_out_prefix;?>" />
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="33%">Min Number Length:</td>
		<td>
			<input type="text" name="min_number_len" size="30" maxlength="2" value="<?php echo $trunk->min_number_len;?>" /> 
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="33%">Max Number Length:</td>
		<td>
			<input type="text" name="max_number_len" size="30" maxlength="2" value="<?php echo $trunk->max_number_len;?>" /> 
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="33%">Rewrite CLI:</td>
		<td>
			<input type="text" name="rewrite_cli" size="30" maxlength="11" value="<?php echo $trunk->rewrite_cli;?>" /> 
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Do Register: </td>
		<td>
			<select name="do_register" id="do_register">
				<option value="0"<?php if ($trunk->do_register == '0') echo ' selected';?>>Do not register</option>
				<option value="1"<?php if ($trunk->do_register == '1') echo ' selected';?>>Register-out to Carrier</option>
				<option value="2"<?php if ($trunk->do_register == '2') echo ' selected';?>>Register-in to PBX</option>
			</select>
		</td>
	</tr>
	<tr class="<?php echo $tr2;?>" id="tr_submit">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if (!empty($trunkid)):?>Update<?php else:?>Add<?php endif;?>  " name="submitagent"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>

<?php if (!empty($trunkid)):?>
<br />

<div class="col-md-12" style="padding: 0 2px;">
    <h4 class="title pull-left">CLI Settings</h4>
    <div class="pull-right"><a href="<?php echo $this->url('task='.$request->getControllerName()."&act=addcli&trunkid=$trunkid");?>" class="btn btn-info btn-sm cli"><i class="fa fa-plus-square-o"></i> Add New CLI</a></div>
</div>

<?php 
include_once "lib/jqgrid.php";
$grid = new jQGrid();

$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->CustomSearchOnTopGrid=false;
$grid->multisearch=false;
$grid->ShowReloadButtonInTitle=true;

$grid->AddModelNonSearchable("Skill", "skillname", 80, "center");
$grid->AddModelNonSearchable("Dial in prefix", "dial_prefix", 80, "center");
$grid->AddModelNonSearchable("CLI", "cli", 80, "center");
$grid->AddModelNonSearchable("Action", "act_links", 80, "center");

$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>
<?php endif;?>