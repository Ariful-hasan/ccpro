<div class="col-sm-12">
	<form action="<?php echo $this->getCurrentUrl();?>" class="form " method="post"  enctype="multipart/form-data">
		<?php GetHiddenFields();?>
		<div class="row form-inline">			
			<div class="col-xs-12">
				<div class="form-group">
			                 <label>Voice Mail Message<?php 
			                  	if(!empty($file_delete_url)){?>
			              		<a href="<?php echo $file_delete_url;?>" oncompleted="DeleteResponse" msg="Are you sure that you want to delete VM message?" class="ConfirmAjaxWR btn btn-xs btn-danger"><i class="fa fa-times"></i></a> 
			              		<?php }?> 
			                 <input type="file" name="uploadfile" id="uploadfile" placeholder=" <?php _e("Audio File" );?>" class="form-control2" accept=".wav" />
			                 </label>
				</div> 
				<div class="form-group text-center">
				    <button type="submit" class="btn btn-success">Upload <?php echo $buttonTitle;?></button>
				</div>
			</div>
		</div>
	</form>
</div>
<script type="text/javascript">
 function DeleteResponse(rdta,obj){	
	 if(rdta.status){		
		 obj.remove();
	 }
	 alert(rdta.msg);
 }
</script>