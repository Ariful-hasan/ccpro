<link rel="stylesheet" href="css/form.css" type="text/css">
<style>
* {
	font-family: Verdana,Arial,Helvetica,sans-serif;
}
.section
{
	width:100%;
	clear:both;
}
.sec-head {
	/*border-bottom: 3px solid #9AC9DE;*/
	border-bottom: 1px solid #D3D6DB;
    color: #2583AD;
    font-size: 17px;
    font-weight: bold;
    
}
.sec-type {
	display:none;
}
.sec-fields
{
	width:100%;

}
.sec-field
{
	width:50%;
	float:left;
	padding: 1px 0px;
}
.sec-fld-label {
	display:inline-block;
	width:40%;
	font-size: 15px;
}
.sec-fld-val {
	display:inline;
	font-size: 15px;
	font-weight:bold;
}
.sec-clear {
	clear:both;
	padding-bottom: 20px;
}
.sec-api {
	display: inline;
	font-size:14px;
	font-style:italic;
	font-weight:bold;
	padding-bottom: 3px;
}
div.add-sec-title {
	display:inline-block;
	width: 120px;
	font-weight:bold;
	font-size:13px;
}
div.add-sec-field{
height: 40px;
text-align:left;
}
div.field {
padding:5px;
}
div.field span{
font-size: 13px;
}
div.field span.flabel{
font-weight:bold;
display:inline-block;
width:110px;
}
div.field span.fkey{
display:inline-block;
width: 110px;
}
div.field span.ftype {
display:none;
}
div.field span.fdel{
	width: 20px;
	text-decoration:underline;
	cursor:pointer;
	color:#0033CC;
}
input.flabel {
width:160px;
}
input.fkey {
width:360px;
}
</style>
<?php $max_num_of_values = 5; ?>
<script src="js/jquery.min.js" type="text/javascript"></script>
<script src="js/jquery-ui.min.js" type="text/javascript"></script>
<script src="js/jquery.watermark.min.js" type="text/javascript"></script>
<script type="text/javascript">
var secTabId = "<?php echo $tabid; ?>";

$(document).ready(function() {
	$('.flabel').watermark('Name');
	$('.fkey').watermark('API');
});

function add_section()
{
	var sid = $('#section_id').val();
	var opts = [];
	var vname = '';
	var vapi = '';
	for (var i=1;i<=<?php echo $max_num_of_values;?>;i++) {
		vname = $('#flabel'+i).val();
		vapi = $('#fkey'+i).val();
		if (vname.length > 0 && vapi.length > 0) {
			opts.push({name:vname, api:vapi});
		}
	}
	
	var jsonObj = {id:sid, values:opts};
	
	$.post( "<?php echo $this->url('task=stemplate&act=save-set-values&tid=' . $tid);?>", {data:jsonObj})
		.done(function( resp ) {
			//alert( "Data Saved");
			//parent.window.location = parent.window.location.href;
			if(secTabId != ""){
				parent.window.location.href = "<?php echo $this->url('task=stemplate&act=section-tabdata&tid='.$tid.'&sid='.$tab_sec_id.'&tabid='.$tabid);?>";
			}else{
				parent.window.location.href = "<?php echo $this->url('task=stemplate&act=details&tid=' . $tid);?>";
			}
			//parent.$.colorbox.close();
	});
}

</script>

<div id="add-sec-elm">
<input type="hidden" name="section_id" id="section_id" value="<?php echo $sid;?>"  />
<div class="sec-head">Value Name-API:</div><br>
<?php
$i = 1; 
if (is_array($fields)) {
	foreach ($fields as $fld) {
		echo '<div class="add-sec-field"><input type="text" class="flabel" name="flabel'.$i.'" id="flabel'.
			$i.'" value="'.$fld->name.'" maxlength="20"  /> - <input type="text" class="fkey" name="fkey'.
			$i.'" id="fkey'.$i.'" value="'.$fld->api.'" maxlength="255"  />  </div>';
		$i++;
	}
}

for (;$i<=$max_num_of_values;$i++) {
	echo '<div class="add-sec-field"><input type="text" class="flabel" name="flabel'.
		$i.'" id="flabel'.$i.'" value="" maxlength="20"  /> - <input type="text" class="fkey" name="fkey'.
		$i.'" id="fkey'.$i.'" value="" maxlength="255"  />  </div>';
}
?>

<div class="add-sec-field" style="text-align:center; padding-top:20px;"><input type="button" class="btn" value="Save Values" onclick="add_section();" /></div>
</div>
