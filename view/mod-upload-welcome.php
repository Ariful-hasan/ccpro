<link href="js/mini-music-player/css/styles.css" rel="stylesheet" type="text/css" />  
<script type="text/javascript" src="js/mini-music-player/js/musicplayer.js"></script>

<form action="<?php echo $this->getCurrentUrl();?>" class="form " method="post"  enctype="multipart/form-data">
	<?php GetHiddenFields();?>
	<?php 
		$hasFile = false;
	
		$enLang = $languages[0];
		$bnLang = $languages[1];
		
		foreach($enLang->file_info as $fkey => $file){	
			
		?>  
	<div class="row file-upload-content">
		<div class="file-upload-wrapper">  
			<div class="col-lg-5 col-md-5 col-sm-5 col-xs-5"> 
			<?php 
				$fileName = $enLang->lang_title;
				$isFileExists = isFileExists($file['file_name'].".wav", $file['file_dir']);

				if($isFileExists){
				$fileName = getFileNameFromTxtFile($file['file_name'], $file['file_dir'])." (".$enLang->lang_title.")"; 
				$hasFile = true;
				}	
			?>	
				<label class="eng-label"><?php  echo $fileName; ?></label>
				<?php  
				if(!empty($file['file_delete_url'])){?>
				<a href="<?php echo $file['file_delete_url'];?>" attr-lang-title="<?php echo $enLang->lang_title;?>"  attr-lang="<?php echo $enLang->lang_key;?>" oncompleted="DeleteResponse" msg="Are you sure to delete?" class="ConfirmAjaxWR btn btn-xs btn-danger delete-url"><i class="fa fa-times"></i></a> 
				<?php 
				}
				?> 
				<input type="file" name="uploadfile[<?php echo $enLang->lang_key;?>][<?php echo $file['file_sl'];  ?>]" id="uploadfile" placeholder=" <?php _e("Audio File" );?>" class="en-file-upload form-control2" />
				
			</div>	
			<div class="col-lg-5 col-md-5 col-sm-5 col-xs-5"> 
				<?php
				$bnFile = $bnLang->file_info[$fkey];
				$fileName = $bnLang->lang_title;
				$isFileExists = isFileExists($bnFile['file_name'].".wav", $bnFile['file_dir']);

				if($isFileExists){
				$fileName = getFileNameFromTxtFile($bnFile['file_name'], $bnFile['file_dir'])." (".$bnLang->lang_title.")"; 
				$hasFile = true;
				}
				?>
				<label class="bn-label"><?php echo $fileName; ?></label>
				<?php
				if(!empty($bnFile['file_delete_url'])){?>
				<a href="<?php echo $bnFile['file_delete_url'];?>" attr-lang-title="<?php echo $bnLang->lang_title;?>"  attr-lang="<?php echo $bnLang->lang_key;?>" oncompleted="DeleteResponse" msg="Are you sure to delete?" class="ConfirmAjaxWR btn btn-xs btn-danger delete-url"><i class="fa fa-times"></i></a> 
				<?php 
				}
				?> 
				<input type="file" name="uploadfile[<?php echo $bnLang->lang_key;?>][<?php echo $file['file_sl']; ?>]" id="uploadfile" placeholder=" <?php _e("Audio File" );?>" class="bn-file-upload form-control2" />
			</div>	
		</div>	
	</div>
	<?php 
			
	}
	
	?>

			
		<div id="add-more-wrapper" class="row" style="margin-top:15px">
			<?php
			if($multiFileUpload == true){
			?>
				<button type="button" id="add-more" class="btn btn-success">
					<i class="fa fa-plus" aria-hidden="true"></i>
					Add More
				</button>
			<?php
			}
			?>
		</div>
		
	</div>

	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-left:15px">
			<?php 
			if(isset($hasTtsUpload) && ($hasTtsUpload == true)){
			?>
			<div class="form-group">
				<input type="checkbox" name="tts" id="tts"/>
				<label for="tts">TTS upload</label>
			</div>
			<div id="tts-text-wrapper" class="form-group hide">
				<textarea name="tts_text" id="tts_text" cols="95" rows="5"><?php
					$ttsTxtFileName = "tts_".$languages[0]->file_info[0]['file_name'];
					$ttsTxtFileNameWithExt = $ttsTxtFileName.".txt";
					$ttsTxtFilePath = $languages[0]->file_info[0]['file_dir'].$ttsTxtFileNameWithExt;

					if(file_exists($ttsTxtFilePath)){
						echo trim(getFileNameFromTxtFile($ttsTxtFileName, $languages[0]->file_info[0]['file_dir'])); 
					}
				?></textarea>
			</div>
			<?php
			}
			?>		
			
			<div class="form-group text-center">
				<button type="submit" class="btn btn-success">Upload <?php echo $buttonTitle;?></button>
			</div>
		</div>
	</div>		

</form>

<script type="text/javascript">
 function DeleteResponse(rdta,obj){	
	 if(rdta.status){		
		var langKey = obj.context.getAttribute('attr-lang');	
		var langTitle = obj.context.getAttribute('attr-lang-title');

		jQuery("."+langKey+"-media-file-name").text(langTitle);
		jQuery(".playlist li."+langKey+"-media-file").remove();
		obj.remove();
		jQuery(".player .controls div.fwd").click();
		jQuery(".player .controls div.stop").click();
	 }
	 alert(rdta.msg);
 }

 
jQuery("#tts").change(function(){
	if(jQuery(this).prop("checked") == true){
		jQuery("#tts-text-wrapper").removeClass("hide");
	}else{
		jQuery("#tts-text-wrapper").addClass("hide");
	}
});

jQuery(document).on("click","#add-more",function(){
	var removeHtml = '<div class="remove-item-wrapper col-lg-2 col-md-2 col-sm-2 col-xs-2" style="margin-top:15px">'+
		'<button type="button" class="remove-item btn btn-danger">'+
			'<i class="fa fa-times" aria-hidden="true"></i> Remove'+	
		'</button>'+			
	'</div>';
	var html = jQuery(".file-upload-wrapper:first").clone();
	html.find(".delete-url").remove();
	html.find(".eng-label").text("English");
	html.find(".bn-label").text("Bangla");

	html.find(".en-file-upload").attr("name","uploadfile[EN][]");
	html.find(".bn-file-upload").attr("name","uploadfile[BN][]");

	var fullHtml = '<div class="row file-upload-content"><div class="file-upload-wrapper">'+html.html()+'</div>'+removeHtml+'</div>';
	jQuery(".file-upload-content:last").after(fullHtml);
});
jQuery(document).on("click",".remove-item",function(){
	jQuery(this).closest('.file-upload-content').remove();
});
		

</script>

<?php 
if($hasFile){
?>

<div class="music-player col-sm-12">

	<ul class="playlist">
	<?php 
		foreach($enLang->file_info as $fkey => $file){
			$isFileExists = isFileExists($file['file_name'].".wav", $file['file_dir']); 
			if($isFileExists){
				$fileName = getFileNameFromTxtFile($file['file_name'], $file['file_dir']);  
			?>   
			<li class="<?php echo $enLang->lang_key;?>-media-file" data-cover="js/mini-music-player/images/music.png" data-artist="<?php echo $enLang->lang_title; ?>">
				<a href="<?php echo $file['file_path']; ?>"><?php echo $fileName." (".$enLang->lang_title.")"; ?></a>
			</li>

			<?php
			}
			$bnFile = $bnLang->file_info[$fkey];
			$isFileExists = isFileExists($bnFile['file_name'].".wav", $bnFile['file_dir']); 
			if($isFileExists){
				$fileName = getFileNameFromTxtFile($bnFile['file_name'], $bnFile['file_dir']);  
			?>   
			<li class="<?php echo $bnLang->lang_key;?>-media-file" data-cover="js/mini-music-player/images/music.png" data-artist="<?php echo $bnLang->lang_title; ?>">
				<a href="<?php echo $bnFile['file_path']; ?>"><?php echo $fileName." (".$bnLang->lang_title.")"; ?></a>
			</li>
		<?php
			}
		}
		
	?>	
		
	</ul>

</div>



<script>
	$(".music-player").musicPlayer({
		//volume: 10,
		//elements: ['artwork', 'controls', 'progress', 'time', 'volume'],
		//playerAbovePlaylist: false,
		onLoad: function() {
			//Add Audio player
			plElem  = "<div class='pl'></div>";
			$('.music-player').find('.player').append(plElem);
			// show playlist
			$('.pl').click(function (e) {
				e.preventDefault();

				$('.music-player').find('.playlist').toggleClass("hidden");
			});
		},

	});
	
</script>

<?php
}
?>