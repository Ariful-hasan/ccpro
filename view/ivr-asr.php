<form action="<?php echo $this->getCurrentUrl();?>" class="form " method="post"  enctype="multipart/form-data">
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-left:15px">
			<div class="form-group">
				<label>ASR Text</label>
			</div>
			<div id="asr-text-wrapper" class="form-group">
				<textarea name="asr_text" id="asr_text" cols="95" rows="5"><?php echo isset($_POST['asr_text']) ? $_POST['asr_text'] : $ivrAsrWordsData->words; ?></textarea>
			</div>	
		</div>
    </div>
    <div class="form-group text-center">
        <button type="submit" class="btn btn-success">Submit</button>
    </div>
</form>