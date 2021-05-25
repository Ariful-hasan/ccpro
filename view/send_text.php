<script src="assets/js/jquery-1.11.2.min.js" type="text/javascript"></script> 

<!--script src="js/ckeditor/ckeditor.js"></script>
<script src="js/ckeditor/adapters/jquery.js"></script>


<script type="text/javascript" src="js/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="js/bootstrap-multiselect/css/bootstrap-multiselect.css" type="text/css"/>
<script src="js/cdpselections.js"></script-->
<link rel="stylesheet" href="ccd/bootstrap.min.css" type="text/css">
<link rel="stylesheet" href="css/form.css" type="text/css">

<style type="text/css">
.form-group { height:40px; }
.multiselect-container > li > a > label > input[type="checkbox"] {
    margin-left: -30px;
    margin-top: 3px;
}
.form_table tr, .form_table td  { border: none !important; }
.form_table { border:1px solid #B6B6B6; }
.note { width:357px !important; height:120px !important; }
.form_table td.form_column_caption { width:80px; }
body { height:99% !important; }
</style>

<form name='textForm' id='textForm' action="<?php echo $this->url("task=agent&act=textsubmit");?>" method='post'>
<table class="form_table" cellspacing="0" cellpadding="6" border="0" align="center">
	<tbody>
		<tr class="form_row_head">
			<td colspan="2">Send Text</td>
		</tr>
	<tr class="form_row_head" id='errMsg' style='display:none'>
			<td colspan="2"></td>
		</tr>
	<tr class="form_row">
		<td class="form_column_caption" align='right' width='50'>To</td> 
		<td>
			<input type='text' name='to' id='to' onkeypress='return numericOnly(event)' class="form-control">
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" align='right'>Text</td>
		<td class='col-md-10'>
			<textarea id='text' name='text' class='note' class="form-control"></textarea>
		</td>
	</tr>
	<tr class="form_row">
		<td></td><td>
		<input id='btnSend' type='submit' class='btn btn-default btn-send' value='Send'></td>
	</tr>
	</tbody>
</table>
</form>

<script>
$('#textForm').on('submit', function (e) {

          e.preventDefault();
		  ShowWait(true,"Please wait...");
          $.ajax({
            type: 'post',
            url: $(this).attr('action'),
            data: $(this).serialize(),
			dataType:"json",
            success: function (data) {
				if(!data.status) {
					parent.$.colorbox({html:"<div class='colorbox-success-msg'>"+data.message+"</div>", escKey:true, overlayClose:true});
				} else {
					$("#errMsg").show();
					$("#errMsg td").html("<div style='color:#AA3333;'>"+data.message+"</div>");
					ShowWait(false,"");
				}
            }
          });

    });
	
function numericOnly(e)
{
	var k = e.keyCode==0 ? e.which : e.keyCode;
	if((k>=48 && k<=57) || k==32 || k==35 || k==36 || k==8 || k==46 || k==37 || k==39 || k==43 || k==45 || k==40 || k==41 || k==116) {
		return true;
	}
	return false;	
}
</script>