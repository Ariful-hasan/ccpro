<script src="js/ajaxfileupload.js" type="text/javascript"></script>

<script type="text/javascript">

window.onload = function() {
	document.getElementById('license_text').focus();
}

function ajaxFileUpload(elemId)
{
	//starting setting some animation when the ajax starts and completes
	$("#loading").ajaxStart(function(){
		$(this).show();
	})
	.ajaxComplete(function(){
		$(this).hide();
	});

	$.ajaxFileUpload
	(
		{
			url:'<?php echo $this->url('task='.$request->getControllerName());?>',
			secureuri:false,
			fileElementId: elemId,
			dataType: 'json',
			success: function (data, status)
			{
				if (typeof(data.error) != 'undefined')
				{
					if(data.error != '')
					{
						alert(data.error);
					}else
					{
						//alert(data.msg);
						$.ajax({type: "POST", url: "cc_web_agi.php", data: "page=settings&event=UpdateLicense", success: function(msgData){
							if (confirm(msgData)) {
								redirectUrl();
							}
						}});
					}
				}
			},
			error: function (data, status, e)
			{
				alert(e);
			}
		}
	)
	return false;
}


function redirectUrl() {
	window.location.href = '<?php echo $this->url('task=settings');?>';
}

function checkForm()
{
	var license_file = document.getElementById('license_file').value;
	
	if(document.getElementById('license_text').value.length==0 && license_file.length == 0) {
		alert('Please enter license text or select any file');
		document.getElementById('license_text').focus();
		return false;
	}
	if(license_file.length > 0) {
		if(!checkFile('license_file')) return false;
	}
	
	//alert(license_file.length);
	//return true;
	if (document.getElementById('license_text').value.length > 0 && license_file.length == 0) {
		return ajaxFileUpload('license_text');
	}
	else if(license_file.length > 0 && document.getElementById('license_text').value.length==0) {
		return ajaxFileUpload('license_file');
	}
	 
}

function checkFile(fileId) {

	var filename = document.getElementById(fileId).value;

	var filelength = parseInt(filename.length) - 17;
	var fileext = filename.substring(filelength,filelength + 17);

	if (fileext != 'gPlex_license.txt'){
		alert('Please Select a Valid License File.');
		document.getElementById(fileId).focus();
		return false;
	}

	return true;
}

function hideShowFileBrowser() {
	if(document.getElementById('license_text').value.length > 0)
		document.getElementById('license_file').disabled=true;
	else
		document.getElementById('license_file').disabled=false;
}

</script>

<?php if (!empty($errMsg)):?><div class="error-msg"><?php echo $errMsg;?></div><?php endif;?>

<form action="<?php echo $this->url('task='.$request->getControllerName());?>" method="post" enctype="multipart/form-data">
<table align="center" width="80%" cellspacing="0" cellpadding="0">
	<tr>
		<td valign="top" align="center">

			<table cellspacing="0" cellpadding="0" align="center" width="600" height="100">
				<tr style="background:#436974; color:#FFFFFF;" align="center">
                	<td style="height:30px; font-weight:bold;">Enter License text or select the license file you want to upgrade with</td>					
				</tr>
                <tr>
                	<td align="center" style="border:2px solid #436974">
                    	<table cellpadding="0" cellspacing="0" width="100%" height="100%">
                        	<tr style="background:#C4CACC">
                                <td class="menu-settings" style="height:30px;">
                                   &nbsp;Enter license text bellow
                                </td>
                            </tr>
                        	<tr style="background:#90A3A9">
                                <td align="center" height="30">
                                	<textarea name="license_text" id="license_text" style="width:600px; height:300px;" onkeyup="hideShowFileBrowser()"></textarea>
                                </td>
                            </tr>
                            <tr style="background:#C4CACC">
                                <td class="menu-settings" style="height:35px; text-align:left;">
                                    <img id="loading" src="images/loading.gif" style="display:none;"> &nbsp; <b>Or</b>, select a license file&nbsp; <input type="file" name="license_file" id="license_file" onChange="return checkForm();" />
                                </td>
                            </tr>
                            <tr style="background:#90A3A9">
                                <td align="center" height="30"><button style="cursor:pointer;" name="upgrade_license" id="upgrade_license" onclick="return checkForm();">Upgrade</button></td>
                            </tr>
                    	</table>
               		</td>
              	</tr>
			</table>

		</td>
	</tr>
</table>
</form>
