<link href="css/form.css" rel="stylesheet" type="text/css">
<link href="css/report.css" rel="stylesheet" type="text/css">
<style type="text/css">
div.input-tag {
	width: 30px;
	display:inline-block;
	text-align:right;
	padding-right: 3px;
}
</style>
<?php if (!empty($flid)):?>
<script type="text/javascript">
	function checkUploadableFile(formObj) {
		var filename_en = formObj.musicfile_en.value;
		var filename_bn = formObj.musicfile_bn.value;
		if (filename_en.length <= 0 && filename_bn.length <= 0) {
			alert ("Please select a mp3 file first", "musicfile_en");
			//formObj.musicfile.focus();
			return false;
		}
		
		if (filename_en.length > 0) {
			var filelength = parseInt(filename_en.length) - 3;
			var fileext = filename_en.substring(filelength,filelength + 3);

			if (fileext != "mp3"){
				alert ("You can upload only mp3 files","musicfile_en");
				return false;
			}
		}

		if (filename_bn.length > 0) {
			var filelength = parseInt(filename_bn.length) - 3;
			var fileext = filename_bn.substring(filelength,filelength + 3);

			if (fileext != "mp3"){
				alert ("You can upload only mp3 files","musicfile_bn");
				return false;
			}
		}
		
		return true;
	}
	
	function checkFolderName(name)
	{
		if(document.getElementById('music_folder').value.length==0)
		{
			alert('Folder Name Could not be blank!!','music_folder');
			document.getElementById('music_folder').value = name;
			//document.getElementById('music_folder').focus();
			return false;
		}
		return true;
	}
</script>
<?php endif;?>

<?php $music_folder = null;?>
<table width="100%" cellpadding="0" cellspacing="0">
<tr style="height:40px;">
	<td align="center">
		<form name="supportform" id="selectForm" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>" style="margin:0px;" method="post">
		<table class="form_table" cellpadding="0" cellspacing="0" width="65%">
		<tr class="form_row">
			<td>
				<strong>Select Folder</strong> 
				<select name="flid" onChange="document.supportform.submit();">
					<option value="">Select</option>
					<?php
						if (is_array($music_directories)) {

							foreach ($music_directories as $music_dir) {
								echo "<option value=\"$music_dir->fl_id\"";
								if ($flid == $music_dir->fl_id) {
									if (!empty($flid)) {
										$music_folder = $music_dir;
									}
									echo ' selected';
								}
								echo ">$music_dir->name</option>";
							}
						}
					?>
				</select>
			</td>
		</tr>
		</table>
		</form>
	</td>
</tr>

<?php if (!empty($flid)):?>
<tr>
	<td>
		<table class="form_table" width="100%" border="0" cellpadding="0" cellspacing="0">
		<form action="<?php echo $this->url('task='.$request->getControllerName()."&option=folder&act=".$this->getActionName());?>" style="margin:0px;" method="post" onsubmit="return checkFolderName('<?php echo $music_folder->name;?>')">
		<tr class="form_row">
			<td class="form_column_caption">Folder name :</td>
			<td width="280">
				<input type="hidden" name="flid" value="<?php echo $flid;?>" /> 
				<input type="text" name="name" id="name" value="<?php echo $music_folder->name;?>" /> &nbsp;
                
			</td>
			<td><input type="submit" class="form_submit_button" name="EditMusicFolder" value="Update Folder Name" /></td>
		</tr>
		</form>
		<form action="<?php echo $this->url('task='.$request->getControllerName()."&option=file&act=".$this->getActionName());?>" style="margin:0px;" method="post" enctype="multipart/form-data" onsubmit="return checkUploadableFile(this);">
		<tr class="form_row_alt">
			<td class="form_column_caption" valign="top">Select file :</td>
			<td align="left">
				<?php //	<input name="MAX_FILE_SIZE" value="1048576" type="hidden" /> ?>
				<input type="hidden" name="flid" value="<?php echo $flid;?>" /> 
				<div class="input-tag">ENG:</div><input name="musicfile_en" id="musicfile_en" type="file" /> &nbsp;
   				<br /><div class="input-tag">BAN:</div><input name="musicfile_bn" id="musicfile_bn" type="file" /> &nbsp;
			</td>
			<td valign="top"><input type="submit" class="form_submit_button" name="upload" id="upload" value="   Upload Music File  " /></td>
		</tr>
		</form>
		</table><br />
		<?php if (!empty($errMsg)):?><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
        <table class="report_table" width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr class="report_row_head">
			<td colspan="4"> 
				Following file(s) are in the folder: <?php echo $music_folder->name;?>
			</td>
		</tr>
		<?php if (is_array($music_files) && count($music_files)>0):?>
				<?php $i = 0;
				
				
				function cmp($a, $b) {
					$al = strtolower($a->name);
	    		    $bl = strtolower($b->name);
		    	    if ($al == $bl) {
        			    return 0;
        			}
			        return ($al > $bl) ? +1 : -1;
				}

				usort($music_files, "cmp");				
				?>
				<?php foreach ($music_files as $music_file):?>
				<?php
					$i++;
					$class = $i%2 == 0 ? 'report_row' : 'report_row_alt';
				?>
		<tr class="<?php echo $class;?>">
		<td class="cntr">&nbsp;<?php echo $i;?>&nbsp;</td>
		<td><a  href="<?php echo $this->url('task='.$request->getControllerName()."&option=file&download=".urlencode($music_file->name)."&flid=$flid&lang=$music_file->language&act=".$this->getActionName());?>"><?php echo $music_file->name;?></a></td>
		<td align="left"><?php echo $music_file->language == 'B' ? 'BAN' : 'ENG';?></td>
		<td class="cntr">
						<a  href="<?php echo $this->url('task='.$request->getControllerName()."&option=file&del=".urlencode($music_file->name)."&flid=$flid&lang=$music_file->language&act=".$this->getActionName());?>" 
                        	onclick="return confirm('Are you sure to remove the file <?php echo $music_file->name;?>?');"><img src="image/cancel.png" class="bottom" border="0" width="14" height="14" title="Remove" /></a>
					</td>
				</tr>
				<?php endforeach;?>
		<?php else:?>
		<tr class="report_row_empty">
			<td colspan="4">
				<b>No File Found!</b>
			</td>
		</tr>
		<?php endif;?>
		</table> 
	</td>
</tr>
<?php endif;?>
</table>