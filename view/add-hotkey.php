<div class="col-sm-12">
	<form action="<?php echo $this->getCurrentUrl();?>" class="form " method="post"  enctype="multipart/form-data">
		<?php GetHiddenFields();?>
		<div class="row ">
			<div class="col-xs-12">
				<div class="form-group">
					<label for="text" class="control-label">   
			                <?php _e("Branch" );?>             			     		
			       </label>
			     
					 <div class="input-group">
					      <div class="input-group-addon">Action</div>
					      <div class="form-control" style="max-width: 100px"><?php echo $hotkey->action;?></div>
					 </div>								
				</div>							
			</div>
			</div>
			<div class="row">
			<div class="col-xs-6">
				<div class="form-group">
					<label for="text" class="control-label ">  <?php _e("Hotkey" );?> </label>	
					<div class="input-group">
					      <div class="input-group-addon">CTRL+</div>
					      <?php 
					     	 $this->html_options('hot_key', 'hot_key', $hotkeyoptions, $hotkey->hot_key,'',false,'form-control');
					      ?>
					 </div>										
				</div>
			</div>
			<div class="col-xs-6">
				<div class="form-group">
					<label for="text" class="control-label ">  <?php _e("Status" );?> </label>					
				 <?php 
					$this->html_options('status', 'status', array("A"=>"Active","I"=>"Inactive"), $hotkey->status,'',false,'form-control');
					?>
									
				</div>		
			</div>
			<div class="col-xs-12">			
				<div class="form-group text-center">
				    <button type="submit" class="btn btn-success">Update Hotkey</button>
				</div>
			</div>
		</div>
	</form>
</div>