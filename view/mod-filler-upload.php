<link href="js/mini-music-player/css/styles.css" rel="stylesheet" type="text/css" />  
<script type="text/javascript" src="js/mini-music-player/js/musicplayer.js"></script>
<?php
	if(empty($branchinfo)){
		$branchinfo =new stdClass();
		$branchinfo->skill_id="";
		$branchinfo->filler_type="";
		$branchinfo->branch="";
		$branchinfo->filler_interval=0;
		$branchinfo->replay_count=0;
		$branchinfo->event="";
		$branchinfo->event_key="";
		$branchinfo->arg="";
		$branchinfo->text="";
	}
	if(empty($branchinfo->filler_interval)){
		$branchinfo->filler_interval=0;
	}
	if(empty($branchinfo->filler_interval)){
		$branchinfo->filler_interval=0;
	}
	
?>
<style >
	.EV{
		display: none;
	}
</style>
<div class="col-sm-12">
	<form action="<?php echo $this->getCurrentUrl();?>" class="form " method="post"  enctype="multipart/form-data">
		<?php GetHiddenFields();?>
		<div class="row form-inline">
			<div class="col-xs-8">
				<div class="form-group">
					<label for="text" class="control-label">   
			                <?php _e("Branch" );?>             			     		
			       </label>
			     
					 <div class="input-group">
					 	<?php
					 	$selectededitbranch="";
					 	if($isEdit){
					 		$strlen=strlen($branchinfo->branch);
					 		$branch=substr($branchinfo->branch, 0,$strlen-1);
					 		$selectededitbranch=substr($branchinfo->branch, -1);
					 	} 
					 	?>
					      <div class="input-group-addon"><?php echo $branch;?></div>
					      <select tabindex="1" name="branch" id="text"  class="form-control" style="max-width: 73px">
							<?php 
							
							//$branchesarray=array_merge(array("*","."),range(1, 99));
							$branchesarray=range(1, 9);
							foreach ($branchesarray as $v){	
							if(in_array($branch.$v,$usedbranched)){
								continue;
							}						
							?>
							<option value="<?php echo $branch.$v;?>" <?php echo PostValue("branch",$selectededitbranch)==$v?'selected="selected"':"";?>><?php echo $v;?></option>
							<?php }?>
					</select>
					    </div>	
					
							
				</div>				
			</div>
			</div>
			<div class="row form-horizontal">
			<div class="col-xs-6">
				<div class="form-group">
					<label for="text" class="control-label col-md-5">   
			                <?php _e("Filler Name" );?>             			     		
			       </label>
					<div class="col-md-7">
						<input type="text" tabindex="1" name="text" id="text" value="<?php echo PostValue("text",$branchinfo->text);?>"
							placeholder=" <?php _e("Filler Name" );?>" class="form-control" />
					</div>					
				</div>
				<div class="form-group">
				     <label for="event" class="control-label col-md-5">   
				                <?php _e("Event" );?>              			     		
				       </label>
				        <div class="col-md-7">                   			     	
				                <?php /*?> <input type="text" value="<?php echo PostValue("event",$branchinfo->event);?>" maxlength="3" tabindex="3" name="event" id="event" placeholder=" <?php _e("Event" );?>" class="form-control" /> <?php */?>
				                 <select   class="form-control"  tabindex="3" name="event" id="event">
								 <?php 
								//  $events= array('AN' => 'Announcement', 'PF' => 'Get DTMF', 'GO'=>'Go',  'LN'=>'Language', 'UI'=>'User Input', 'VM'=>'Voice mail');
								$events= array('AN' => 'Announcement', 'PF' => 'Get DTMF', 'GO'=>'Go', 'IV' => 'IVR',  'LN'=>'Language', 'SQ' => 'Skill', 'UI'=>'User Input');
								if( $type == 'H'){
									$events= array('AN' => 'Announcement');
								}
								 ?>
				                 <?php foreach ($events as $key=>$ev){
									GetHTMLOption($key, $ev,$branchinfo->event);
				                 }?>
				                 </select>
				          </div>
				</div> 
			 
			</div>
			<div class="col-xs-6">
				<div class="form-group">
					<label for="filler_interval" class="control-label col-md-5">   
		                <?php _e("Filler Interval" );?>              			     		
		     		</label>
					<div class="col-md-7">
						<input value="<?php echo PostValue("filler_interval",$branchinfo->filler_interval);?>" type="number" tabindex="2"  maxlength="3"
							name="filler_interval" id="filler_interval"
							placeholder=" <?php _e("Filler Interval" );?>"
							class="form-control" />
					</div>
				</div>	
				<div class="form-group">
				     <label for="event" class="control-label col-md-5">   
				                <?php _e("Replay Count" );?>              			     		
				       </label>
				        <div class="col-md-7">                   			     	
				                 <input type="number" value="<?php echo PostValue("replay_count",$branchinfo->replay_count);?>" maxlength="3" tabindex="4" name="replay_count" id="event" placeholder=" <?php _e("Replay Count" );?>" class="form-control" />
				          </div>
				</div>			
			</div>
			</div>
			
			<div class="row form-horizontal">
				<div class="col-xs-6 ">
					<div class="form-group EV GO">
				     <label for="event" class="control-label col-md-5">   
				                <?php _e("Node ID" );?>              			     		
				       </label>
				        <div class="col-md-7">    <?php 
				       			$defultevgo=$branchinfo->event=="GO"?$branchinfo->event_key:"";
				        ?>              			     	
				                 <input type="text" value="<?php echo PostValue("event_key",$defultevgo);?>" tabindex="8" name="event_key" id="event_key" placeholder=" <?php _e("Node ID" );?>" class="form-control" />
				          </div>
					</div>
					
					<div class="form-group EV SQ">
				     	<label for="event" class="control-label col-md-5">   
				                <?php _e("Skill" );?>              			     		
				       </label>
				        <div class="col-md-7"> 
			                 <select  tabindex="8" name="event_key" id="event_key"  class="form-control"  >
									 <?php 
									 foreach ($skill_options as $key => $value){
			                 			GetHTMLOption($key, $value, $branchinfo->event_key);
									 }
									 ?>
			                 </select>
				       </div>
					</div>
					<div class="form-group EV LN">
				     	<label for="event" class="control-label col-md-5">   
				                <?php _e("Language" );?>              			     		
				       </label>
				        <div class="col-md-7"> 
			                 <select  tabindex="8" name="event_key" id="event_key"  class="form-control"  >
			                 		<?php foreach ($languages as $language){
			                 			GetHTMLOption($language->lang_key, $language->lang_title,$branchinfo->event_key);
			                 		}?>
			                 </select>
				       </div>
					</div>
					<div class="form-group EV IV">
				     	<label for="event" class="control-label col-md-5">   
				                <?php _e("IVR" );?>              			     		
				       </label>
				        <div class="col-md-7"> 
			                 <select  tabindex="8" name="event_key" id="event_key"  class="form-control"  >
									<?php 
									 foreach ($ivr_options as $key => $value){
			                 			GetHTMLOption($key, $value, $branchinfo->event_key);
									 }
									?>
			                 </select>
				       </div>
					</div>
				</div>			
				<div class="col-xs-12 EV AN VM PF UI">
				<div class="form-group">
				     <label for="uploadfile" class="control-label col-md-5">   
				                <?php _e("Audio File" );?>              			     		
				       </label>
				        <div class="col-md-7">    
							<?php 
							$hasFile = false;
							foreach ($languages as $language){
								// GPrint($language);
							?>
				        	           			     	
								 <label><?php 
								 $fileName = $language->lang_title;
								 $file = $language->file_info;
								 $isFileExists = isFileExists($file['file_name'].".wav", $file['file_dir']);

								 if($isFileExists){
									$fileName = getFileNameFromTxtFile($file['file_name'], $file['file_dir'])." (".$language->lang_title.")"; 
									$hasFile = true;
								 }
								 
								 ?>
								 <span class='<?php echo $language->lang_key;?>-media-file-name'><?php echo $fileName; ?></span>
								 <?php 
				                 if(!empty($language->file_delete_url)){?>
			              			<a href="<?php echo $language->file_delete_url;?>" attr-lang-title="<?php echo $language->lang_title;?>" attr-lang="<?php echo $language->lang_key;?>"  oncompleted="DeleteResponse" msg="Are you sure to delete?" class="ConfirmAjaxWR btn btn-xs btn-danger"><i class="fa fa-times"></i></a> 
			              		<?php }?> 
			              		<input type="file" name="uploadfile[<?php echo $language->lang_key;?>]" id="uploadfile" placeholder=" <?php _e("Audio File" );?>" class="form-control2" /></label>
				                <?php }?>
				          </div>
				</div>
			</div>
			</div>
			<div class="row form-horizontal">
			<div class="col-xs-12"> 
				<div class="form-group text-center">
				    <button type="submit" class="btn btn-success"><?php echo !empty($btntext)?$btntext:$pageTitle;?></button>
				</div>
			</div>
		</div>
	</form>
</div>
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
 $(function(e){	 
	 $("#event").change(function(){
		 UpdateEvent();
	  });
	 UpdateEvent();
 });
 function UpdateEvent(){
	 $(".EV").hide().find("input,select").attr("disabled","disabled");
	 var ev=$("#event").val();
	 $("."+ev).show().find("input,select").removeAttr("disabled");
	 try{
		 parent.$.colorbox.resize({
		        innerWidth:$('body').width(),
		        innerHeight:$('body > div > .row > .box').height()
		   });
		 
	 }catch(e){
	 }
 }
</script>


<?php 
if($hasFile && $isEdit){
?>
<div class="music-player">

	<ul class="playlist">
	<?php
	foreach ($languages as $language){ 
		$file = $language->file_info;
		$isFileExists = isFileExists($file['file_name'].".wav", $file['file_dir']); 
		if($isFileExists){
			$fileName = getFileNameFromTxtFile($file['file_name'], $file['file_dir']);  
	?>   
		<li class="<?php echo $language->lang_key;?>-media-file" data-cover="js/mini-music-player/images/music.png" data-artist="<?php echo $language->lang_title; ?>">
			<a href="<?php echo $file['file_path']; ?>"><?php echo $fileName." (".$language->lang_title.")"; ?></a>
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