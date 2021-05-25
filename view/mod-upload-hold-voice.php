<link href="js/mini-music-player/css/styles.css" rel="stylesheet" type="text/css" />  
<script type="text/javascript" src="js/mini-music-player/js/musicplayer.js"></script>
<div class="col-sm-12">
	<form action="<?php echo $this->getCurrentUrl();?>" class="form " method="post"  enctype="multipart/form-data">
		<?php GetHiddenFields();?>
		<div class="row form-inline">			
			<div class="col-xs-12">
				<div class="form-group">			        	          			     	
			              <label>
			              	Audio 1 
			              	<?php if(!empty($f_moh1)){?>
			              	<a href="<?php echo $this->url("task=confirm-response&act=delete-moh-file&pro=da&f=1&sid=".$skill);?>" attr-file-id="moh1"  oncompleted="DeleteResponse" msg="Are you sure to delete?" class="ConfirmAjaxWR btn btn-xs btn-danger"><i class="fa fa-times"></i></a> 
			              	<?php }?>
			              	<input type="file" name="moh1" id="moh1"  class="form-control2" />
			              </label>
			              <label>Audio 2  
			              	<?php if(!empty($f_moh2)){?>
			              	<a href="<?php echo $this->url("task=confirm-response&act=delete-moh-file&pro=da&f=2&sid=".$skill);?>" attr-file-id="moh2" oncompleted="DeleteResponse"  msg="Are you sure to delete?" class="ConfirmAjaxWR btn btn-xs btn-danger"><i class="fa fa-times"></i></a> 
			              	<?php }?> 
			              	<input type="file" name="moh2" id="moh2" class="form-control2" />
			              </label>
			              <label>Audio 3  	
			              	<?php if(!empty($f_moh3)){?>
			              		<a href="<?php echo $this->url("task=confirm-response&act=delete-moh-file&pro=da&f=3&sid=".$skill);?>" attr-file-id="moh3" attr-lang="moh3" oncompleted="DeleteResponse" msg="Are you sure to delete?" class="ConfirmAjaxWR btn btn-xs btn-danger"><i class="fa fa-times"></i></a> 
			              	<?php }?> 
			             	 <input type="file" name="moh3" id="moh3" class="form-control2" />
			              </label>             
				</div> 
				<div class="form-group text-center">
				    <button type="submit" class="btn btn-success">Upload Music on Hold Voice</button>
				</div>
			</div>
		</div>
	</form>
</div>
<script type="text/javascript">
 function DeleteResponse(rdta,obj){	
	 if(rdta.status){		
		var attrFileId = obj.context.getAttribute('attr-file-id');	
		jQuery(".playlist li."+attrFileId+"-media-file").remove();
		obj.remove();
		jQuery(".player .controls div.fwd").click();
		jQuery(".player .controls div.stop").click();
	 }
	 alert(rdta.msg);
 }
</script>

<?php
if( !empty($f_moh1) || !empty($f_moh2) || !empty($f_moh3) ){
?>

<div class="music-player col-sm-12 mt-20">

	<ul class="playlist">  
	<?php
	if(!empty($f_moh1)){
	?>
		<li class="moh1-media-file" data-cover="js/mini-music-player/images/music.png" data-artist="<?php echo $f_moh1_filename; ?>">
			<a href="<?php echo $f_moh1; ?>"><?php echo "Audio 1"; ?></a>
		</li>
	<?php
	}
	if(!empty($f_moh2)){
	?>
		<li class="moh2-media-file" data-cover="js/mini-music-player/images/music.png" data-artist="<?php echo $f_moh2_filename; ?>">
			<a href="<?php echo $f_moh2; ?>"><?php echo "Audio 2"; ?></a>
		</li>
	<?php
	}
	if(!empty($f_moh3)){
	?>
		<li class="moh3-media-file" data-cover="js/mini-music-player/images/music.png" data-artist="<?php echo $f_moh3_filename; ?>">
			<a href="<?php echo $f_moh3; ?>"><?php echo "Audio 3"; ?></a>
		</li>
	<?php
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