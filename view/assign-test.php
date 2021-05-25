<div class="row">
<div class="col-md-12">
<form action="" id="debug-form" class="form form-horizontal">
	<div class="form-group">
	    <label for="suid" class="control-label col-md-5">SEAT </label>
        <div class="col-md-7">
        	<input type="text" name="seat" id="suid" placeholder="SEAT" class="form-control" />
        </div>
	</div> 	
	<div class="form-group">
	    <label for="suid" class="control-label col-md-5">PCMAC</label>
        <div class="col-md-7">
        	<input type="text" name="pcmac" id="pcmac" placeholder=" pcmac"" class="form-control" />
        </div>
	</div> 
	
	<div class="form-group">
	    <label for="suid" class="control-label col-md-5">SUID </label>
        <div class="col-md-7">
        	<input type="text" name="suid" id="suid" placeholder=" SUID" class="form-control" />
        </div>
	</div> 
	<div class="form-group">
	    <label for="suid" class="control-label col-md-5">SU PASS </label>
        <div class="col-md-7">
        	<input type="text" name="supass" id="supass" placeholder="SU PASS"" class="form-control" />
        </div>
	</div> 
	<div class="col-md-12 text-center">
		<button class="btn btn-success" type="submit" >Submit</button>
	</div>
</form>
<div class="col-md-12"><br/></div>
</div>

<div class=" col-md-12">
	<textarea id="debug" class="form-control"  rows="" cols=""></textarea>	
</div>
</div>
<script type="text/javascript">
$(function(){
	$("#debug-form").on("submit",function(e){
		console.log($( "#debug-form" ).serialize());
		e.preventDefault();
		$.post( "<?php echo $this->url('task=agent&act=assignseat');?>", $( "#debug-form" ).serialize())
		  .done(function( data ) {
		    $("#debug").val(data);
		  });
	});
	
});
</script>