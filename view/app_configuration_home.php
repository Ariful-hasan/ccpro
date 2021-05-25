<script src="js/jquery.watermark.min.js" type="text/javascript"></script>
<script src="js/jquery.clockpick.1.2.2.pack.js" type="text/javascript"></script>
<script src="js/jquery.datePicker.js" type="text/javascript"></script>
<script src="js/date.js" type="text/javascript"></script>
<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>

<link rel="stylesheet" href="css/report.css" type="text/css">
<link rel="stylesheet" href="css/form.css" type="text/css">
<link rel="stylesheet" href="css/clockpick.css" type="text/css">

<script type="text/javascript">
$(document).ready(function() {
	$( "#td-sections" ).sortable({
		revert: true
	});

	Date.format = 'yyyy-mm-dd';
	$("#clk_time").clockpick({starthour: 0, endhour: 23, minutedivisions:12,military:true});

	$('#clk_date').watermark('Date');
	$('#clk_time').watermark('Time');
	
	$( "#form_cinfo" ).validate({
		rules: {
			home_phone: {
				digits: true
			},
			office_phone: {
				digits: true
			},
			mobile_phone: {
				digits: true
			},
			other_phone: {
				digits: true
			},
			fax: {
				digits: true
			}, 
			email: {
				email: true
			}
		}
	});
	$( "#form_ainfo" ).validate({
		rules: {
			zip: {
				digits: true
			}
		}
	});
	
	var txt_edit = '<img width="16" height="16" border="0" src="image/icon/page_edit.png" class="bottom"> edit';
	var txt_save = '<img width="16" height="16" border="0" src="image/icon/page_save.png" class="bottom"> save';
	
	/*
	$("#btn_add_section").click(function (e) {
		e.stopPropagation();
		$.colorbox({inline:true, onClosed:function(){ $('#add-sec-elm').hide();}, onOpen:function(){ $('#add-sec-elm').show();}, height:500, width:620, href:"#add-sec-elm"});
	});
	*/
	
	
	$(".sec-del").click(function (e) {
		e.stopPropagation();
		var sid = $(this).parent('div').attr('id');
		//$.colorbox({inline:true, onClosed:function(){ $('#add-sec-elm').hide();}, onOpen:function(){ $('#add-sec-elm').show();}, height:500, width:620, href:"#add-sec-elm"});
		if (sid.length > 0) {
			if (confirm('Are you sure to delete request (ID: '+sid+')?')) {
				$.post( "<?php echo $this->url('task=app&act=del-request');?>", {sid:sid})
					.done(function( resp ) {
						//location.reload();
						window.location.href = "<?php echo $this->url('task=app');?>";
					});
			}
		}
	});
	
	$("a.btn_add_section").colorbox({iframe:true, height:600, width:720});
});

function adjust_callback_option()
{
	var cb_opt = $('#chk_callback').is(':checked') ? false : true;
	$("#days").prop('disabled', cb_opt).val('');
	$("#hrs").prop('disabled', cb_opt).val('');
	$("#min").prop('disabled', cb_opt).val('');
	$("#clk_date").prop('disabled', cb_opt).val('');
	$("#clk_time").prop('disabled', cb_opt).val('');
	$("#ag_self").prop('disabled', cb_opt);
	$("#ag_any").prop('disabled', cb_opt);
}

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
<style>

label.error {
display:block;
font-size:11px;
}
.popup-page-title  {
	font-size: 20px;
}
.profile-details-form td {
	font-size:15px;
	vertical-align:top;
}

.profile-details td, .personal-val, .address-val, .contact-val {
	font-size:15px;
}
.p-head td {
	border-bottom: 3px solid #9AC9DE;
	font-size: 17px;
	font-weight: bold;
	color: #2583AD;
	margin-bottom: 10px;
}
.p-value {
	font-weight: bold;
}
.p-br {
line-height: 30px;
}
.p-head-br {
line-height: 8px;
}
.personal-inp, .address-inp, .contact-inp {
	display:none;
}
#fl_msg_success {
	background-color:#009933;
	color: white;
	padding: 2px 5px;
	display: none;
}
#fl_msg_saving {
	background-color:#006699;
	color: white;
	padding: 2px 5px;
	display: none;
}
#fl_msg_err {
	background-color:#990000;
	color: white;
	padding: 2px 5px;
	display: none;
}
.act-head {
    color: #2583AD;
    font-size: 15px;
    font-weight: bold;
    margin-bottom: 10px;
}
.report_table tr.report_row td, .report_table tr.report_row_alt td {
	font-size: 13px;
}
.report_table tr.report_row_head th {
	font-size: 13px;
	padding: 3px;
}
.report_extra_info td, .report_table td {
	font-size:11px;
}
select {
	width: auto;
}
span.flash {
	z-index: 50;
	position: absolute;
    left: 310px;
    top: 20px;
}

.section
{
	width:100%;
	clear:both;
}
.sec-head {
	border-bottom: 3px solid #9AC9DE;
    color: #2583AD;
    font-size: 17px;
    font-weight: bold;
    
}
.sec-type {
	display:none;
}
.sec-fields
{
	width:100%;

}
.sec-field
{
	width:50%;
	float:left;
	padding: 1px 0px;
}
.sec-fld-label {
	display:inline-block;
	width:40%;
	font-size: 15px;
}
.sec-fld-val {
	display:inline;
	font-size: 15px;
	font-weight:bold;
}
.sec-clear {
	clear:both;
	padding-bottom: 20px;
}
.sec-api {
	display: inline;
	font-size:14px;
	font-style:italic;
	font-weight:bold;
	padding-bottom: 3px;
}
.sec-del {
	display:block;
	float:right;
	padding-top: 8px;
	width:16px;
	height:16px;
	cursor:pointer;
	background:url(image/cancel.png) no-repeat center center;
}
.section{
	margin-top: 5px !important;
}
</style>

<span class="flash" id="fl_msg_success">Information saved successfully !!</span>
<span class="flash" id="fl_msg_err">Failed to save information !!</span>
<span class="flash" id="fl_msg_saving">Saving information ...</span>


<?php
if (!empty($errMsg)) {
?>

<table width="100%" align="center" border="0" class="profile-details" cellpadding="1">
<tr class="p-head"><td colspan="4">Error</td></tr><tr><td colspan="4" class="p-head-br">&nbsp;</td></tr>
<tr><td colspan="4" align="center"><span style=" font-size:18px; font-weight:bold; background-color:brown; color:white; padding: 5px 50px;"><?php echo $errMsg;?></span></td></tr>
</table>
<?php 
} else {

	//search section id=SR, inserted empty in database	
	$flds = array();
	if (is_array($fields)) {
		foreach ($fields as $fld) {
			$secid = empty($fld->request_id) ? 'SR' : $fld->request_id;
			$flds[$secid][] = $fld;
		}
	}
	//var_dump($fields);
?>

<table width="100%">
<tr><td align="right"><a href="<?php echo $this->url('task=app&act=new-request');?>" class="btn btn-mini btn-info btn_add_section">Add Request</a>
</td></tr>
<tr><td width="100%" valign="top" id="td-sections">


<?php

if (is_array($app_requests)) {
	
	foreach ($app_requests as $sec) {
		
		$type = '';
		if ($sec->response_type == 'F') {
			$type = 'Information';
		} else if ($sec->response_type == 'G') {
			$type = 'Grid';
		} else if ($sec->response_type == 'D') {
			$type = 'Disposition';
		} else if ($sec->response_type == 'T') {
			$type = 'TPIN';
		} else if ($sec->response_type == 'I') {
			$type = 'IVR Services';
		}
?>
<div id="<?php echo $sec->request_id;?>" class="section"><div class="sec-del" alt="X"></div><div class="sec-head"><?php echo $sec->title;?></div>ID: <?php echo $sec->request_id;?>, Type: <?php echo $type;?>, API: <div class="sec-api"><?php echo htmlspecialchars($sec->api);?></div><div class="sec-type"><?php echo $sec->response_type;?></div><div class="sec-fields">
<?php

if (isset($flds[$sec->request_id])) {
	foreach ($flds[$sec->request_id] as $fld) {
?>
<div class="sec-field"><div class="sec-fld-label"><?php echo $fld->field_label;?></div><div class="sec-fld-val"><?php echo $fld->field_key;?></div></div>
<?php
	}
}
?>
</div><?php if ($sec->response_type == 'F' || $sec->response_type == 'G') {?><div><a class="btn_add_section" href="<?php echo $this->url('task=app&act=new-request&sid='.$sec->request_id);?>">edit</a></div><?php } else if ($sec->response_type == 'D' || $sec->response_type == 'T' || $sec->response_type == 'I') {
?><div><a class="btn_add_section" href="<?php echo $this->url('task=app&act=new-defined-section&sid='.$sec->request_id);?>">edit</a></div><?php
} ?>
<div><a class="btn_add_section" href="<?php echo $this->url('task=app&act=request-values&sid='.$sec->request_id);?>">values</a></div>
<div class="sec-clear"></div></div>
<?php
	}
}

?>


<td></tr></table>
<script>adjust_callback_option();</script>
<?php } ?>

<br />
<script>
$(document).ready(function() {

	$('#title').watermark('Text');
	$('#api').watermark('API');	
	$('#flabel').watermark('Label');
	$('#fkey').watermark('var');


	//$("#add-sec-href").colorbox({inline:true, height:500, width:600, href:"#add-sec-elm"});
	$(".del-field").click(function (e) {
		//$(this).remove();
		alert('1');
	});
	
	
	$("#btn-add-field").click(function (e) {
		e.stopPropagation();
		var fkey = $('#fkey').val();
		var flabel = $('#flabel').val();
		$('#fields').append('<div class="field form" id="'+fkey+'"><span class="flabel">'+flabel+'</span> : <span class="fkey">'+fkey+'</span><span class="fdel" onclick="del_field(\''+fkey+'\');">X</span></div>');
		$('#flabel').val('');
		$('#fkey').val('');
	});

});

var curSection = '';
var secType = '';

function add_section()
{
	var stitle = $('#title').val();
	var sec_id = $('#request_id').val();
	var api = $('#api').val();
	
	var sec_type = '';
	
	if (secType == 'F') {
		sec_type = 'Field';
	} else if (secType == 'G') {
		sec_type = 'Grid';
	} else if (secType == 'D') {
		sec_type = 'Disposition';
	}
	
	var content = '<div class="sec-head">'+stitle+'</div>ID: '+sec_id+', Type: '+sec_type+', API: <div class="sec-api">'+api+'</div><div class="sec-type">'+secType+'</div><div class="sec-fields">';
	
	$( "#add-sec-elm div.field" ).each(function( index ) {
		content += '<div class="sec-field"><div class="sec-fld-label">'+$( this ).find('span.flabel').text()+
			'</div><div class="sec-fld-val">'+$( this ).find('span.fkey').text()+'</div></div>';
	});

	content += '</div><a href="#" onclick="load_section(\''+sec_id+'\',\''+secType+'\');">edit</a></div><div class="sec-clear"></div>';
	
	if (curSection.length > 0) {
		$("#" + curSection).html(content);
	} else {
		$("#td-sections").append('<div class="section" id="'+sec_id+'">' + content + '</div>');
	}
	
	curSection = '';
	secType = '';
	
	$.colorbox.close();
}

function add_disposition_section()
{
	if ($(".sec-type:contains('D')").length) {
		alert('Disposition history already added!!');
	} else {
		
		var first = "A", last = "Z";
		var ch = '';
		var sid = '';
		for(var i = first.charCodeAt(0); i <= last.charCodeAt(0); i++) {
			//document.write( eval("String.fromCharCode(" + i + ")") + " " );
			ch = String.fromCharCode(i);
			//alert($('#'+ch).length);
			if ($('#'+ch).length == 0) {sid = ch; break;}
		}
		
		var content = '<div class="sec-head">Disposition History</div><div class="sec-api"></div><div class="sec-type">D</div><div class="sec-fields"></div></div><div class="sec-clear"></div>';
		$("#td-sections").append('<div class="section" id="'+sid+'">' + content + '</div>');
	}
}

function add_tpin_section()
{
	if ($(".sec-type:contains('T')").length) {
		alert('TPIN section already added!!');
	} else {
		
		var first = "A", last = "Z";
		var ch = '';
		var sid = '';
		for(var i = first.charCodeAt(0); i <= last.charCodeAt(0); i++) {
			//document.write( eval("String.fromCharCode(" + i + ")") + " " );
			ch = String.fromCharCode(i);
			//alert($('#'+ch).length);
			if ($('#'+ch).length == 0) {sid = ch; break;}
		}
		
		var content = '<div class="sec-head">TPIN</div><div class="sec-api"></div><div class="sec-type">T</div><div class="sec-fields"></div></div><div class="sec-clear"></div>';
		$("#td-sections").append('<div class="section" id="'+sid+'">' + content + '</div>');
	}
}

function del_field(fkey)
{
	$('#'+fkey).remove();
}

function save_template()
{
	var jsonObj = [];
	$( "#td-sections div.section" ).each(function( index ) {
		var sid = $(this).attr('id');
		/*
		var stitle = $(this).find('div.sec-head').text();
		var sapi = $(this).find('div.sec-api').text();
		var stype = $(this).find('div.sec-type').text();
		
		var txt = sid + ' - ' + stitle;
		var opts = [];
		$( this ).find('div.sec-fields div.sec-field').each(function (i) {
			//console.log($(this).find('div.sec-fld-label').text());
			//console.log($(this).find('div.sec-fld-val').text());
			opts.push({label:$(this).find('div.sec-fld-label').text(), key:$(this).find('div.sec-fld-val').text()});
		});
		
		//content += '<div class="sec-field"><div class="sec-fld-label">'+$( this ).find('span.flabel').text()+
			//'</div><div class="sec-fld-val">'+$( this ).find('span.fkey').text()+'</div></div>';
		//console.log(txt);
		jsonObj.push({id:sid, title:stitle, api:sapi, stype:stype, fields:opts});
		*/
		jsonObj.push({id:sid});
	});
	
}

function load_section(sec_id, stype)
{
	$('#flabel').val('');
	$('#fkey').val('');
	$('#fields').text('');
	curSection = '';
	secType = stype;
	var is_div_exist = false;
	
	if (sec_id.length > 0) {
		var sec = $('#'+sec_id);
		if (sec.length) {
			var sectitle = sec.find('div.sec-head').text();
			var api = sec.find('div.sec-api').text();
			var content = '';
			var fkey = '';
			var flabel = '';
			is_div_exist = true;
			curSection = sec_id;
			//alert(sectitle);
			$('#title').val(sectitle);
			//console.log($('#title').val());
			$('#request_id').val(sec_id);
			$('#api').val(api);
			
			sec.find('.sec-field').each(function( index ) {
				//console.log($(this).text());
				//console.log($(this).find('div.sec-fld-label').text());
				//console.log($(this).find('div.sec-fld-val').text());
				fkey = $(this).find('div.sec-fld-val').text();
				flabel = $(this).find('div.sec-fld-label').text();
				content += '<div class="field form" id="'+fkey+'"><span class="flabel">'+flabel+'</span> : <span class="fkey">'+fkey+'</span><span class="fdel" onclick="del_field(\''+fkey+'\');">X</span></div>';
			});
			
			$('#fields').html(content);
		}
	}

	if (!is_div_exist) {
		$('#title').val('');
		$('#request_id').val('');
		$('#api').val('');
		
		var first = "A", last = "Z";
		var ch = '';
		for(var i = first.charCodeAt(0); i <= last.charCodeAt(0); i++) {
			//document.write( eval("String.fromCharCode(" + i + ")") + " " );
			ch = String.fromCharCode(i);
			//alert($('#'+ch).length);
			if ($('#'+ch).length == 0) {$('#request_id').val(ch); break;}
		}
	}
	
	
	$.colorbox({inline:true, onClosed:function(){ $('#add-sec-elm').hide();}, onOpen:function(){ $('#add-sec-elm').show();}, height:500, width:620, href:"#add-sec-elm"});
}


</script>

<!--
<div class="p-head">Section Type: <select name="response_type" id="response_type"><option value="F">Form &nbsp;</option><option value="G">Grid &nbsp;</option></select></div>
-->
<style>
div.add-sec-title {
	display:inline-block;
	width: 120px;
	font-weight:bold;
	font-size:13px;
}
div.add-sec-field{
height: 40px;
}
div.field {
padding:5px;
}
div.field span{
font-size: 13px;
}
div.field span.flabel{
font-weight:bold;
display:inline-block;
width:110px;
}
div.field span.fkey{
display:inline-block;
width: 110px;
}
div.field span.fdel{
	width: 20px;
	text-decoration:underline;
	cursor:pointer;
	color:#0033CC;
}

</style>
