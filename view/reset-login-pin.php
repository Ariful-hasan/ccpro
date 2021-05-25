<div class="col-sm-12 col-md-12">

    <form action="<?php echo $this->getCurrentUrl();?>" class="form form-horizontal bv-form " method="post"  enctype="multipart/form-data">
		<?php
            GetHiddenFields();
		    if(UserAuth::getRoleID()!=="R" && UserAuth::getRoleID()!=="S"): ?>
		
                <div class="form-group form-group-sm">
                    <label class="control-label col-xs-5" for="pass"><?php _e("Current Password"); ?></label>
                    <div class="col-xs-7">
                        <input type="password"  value="" class="form-control" required="required" id="pass" name="pass" placeholder="Password" data-bv-notempty="true" 	data-bv-notempty-message="<?php  _e("Password is required");?>">
                    </div>
                </div>
		<?php endif; ?>


        <div class="form-group form-group-sm">
            <label for="" class="control-label">Are you sure to generate login pin ? </label>
            <div class="pull-right">
                <button class=" btn btn-success" type="submit"> <i class="fa fa-save"></i> Generate &nbsp;</button> &nbsp;
                <a class=" btn btn-danger" href="#" onclick="parent.$.colorbox.close();" > <i class="fa fa-times"></i> Close </a>
            </div>
        </div>

	</form>

</div>