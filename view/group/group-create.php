<!-- <link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<script src="assets/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>  -->
<script type="text/javascript" src="ccd/jquery-validation/jquery.validate.min.js"></script>

<!-- Toaster -->
<link rel="stylesheet" href="assets/plugins/toastr/toastr.min.css" />
<script type="text/javascript" src="assets/plugins/toastr/toastr.min.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
    	$('#GroupForm').validate();
    });
</script>

<script type="text/javascript">
	var errType = <?php echo $errType; ?>;
	var errMsg = '<?php echo $errMsg; ?>';
	if(errMsg){
		errType ? toastr.error(errMsg) : toastr.success(errMsg);
	}
</script>

<form action="<?php echo $formUrl; ?>" method="post" id="GroupForm">
	<div class="row">
		<div class="col-sm-6">
			<div class="row form-group">
			    <label for="title" class="col-sm-2 col-form-label">Name</label>
			    <div class="col-sm-10">
			    	<input type="text" class="form-control" id="title" name="title" data-rule-required="true" data-msg-required="Group name is required." value="<?php if(isset($group)) echo $group->title; ?>">
			    </div>
	  		</div>
		</div>

	  	<div class="col-sm-6">
			<div class="row form-group">
				<label for="status" class="col-sm-1 col-form-label" style="margin-right: 15px;">Status:</label>
				<div class="col-sm-10">
					<div class="language-check" style="margin-top: 3px">
						<label class="radio-container">Active
							<input id="active" name="status" type="radio" value="Y" checked>
							<span class="radio-checkmark"></span>
						</label>
						<label class="radio-container">Inactive
							<input id="inactive" name="status" type="radio" value="N" <?php if(isset($group)) echo $group->status == 'N' ? 'checked' : ''; ?> >
							<span class="radio-checkmark"></span>
						</label>
					</div>
				</div>
			</div>
	  	</div>
	</div>

	<div style="text-align: center;">
		<button id="submit" type="submit" class="btn btn-primary"><?php echo $btnName; ?></button>
	</div>
</form>