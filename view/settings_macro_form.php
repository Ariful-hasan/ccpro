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
<form name="frm_macro" id="frm_macro" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="macro_code" value="<?php if (isset($macro_code)) echo $macro_code;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Macro Information</td>
	</tr>
	
	<tr class="form_row_alt">
		<td class="form_column_caption" width="33%">Label:</td>
		<td>
			<input type="text" name="title" size="30" maxlength="30" value="<?php echo $macro->title;?>" />
		</td>
	</tr>
	<tr class="form_row" id="code">
		<td class="form_column_caption">Code: </td>
		<td>
        	<input type="text" name="code" size="30" maxlength="3" value="<?php echo $macro->code;?>" />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if (!empty($macro_code)):?>Update<?php else:?>Add<?php endif;?>  " name="submitmacro"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>

<?php if (!empty($macro_code)):?>
<br />

<div class="col-md-12" style="padding: 0 2px;">
    <h4 class="title pull-left">Macro Settings</h4>
    <div class="pull-right"><a href="<?php echo $this->url('task='.$request->getControllerName()."&act=add-macro-job&macro_code=$macro_code");?>" class="btn btn-info btn-sm cli"><i class="fa fa-plus-square-o"></i> Add New Action</a></div>
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

$grid->AddModelNonSearchable("CTI", "cti_id", 80, "center");
$grid->AddModelNonSearchable("Action Type", "action_type", 80, "center");
$grid->AddModelNonSearchable("Description", "desc", 80, "center");
$grid->AddModelNonSearchable("Action", "act_links", 80, "center");

$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});
</script>
<?php endif;?>