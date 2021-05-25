<?php $num_select_box = 6; ?>
<script src="assets/js/jquery-1.11.2.min.js" type="text/javascript"></script> 

<script src="js/ckeditor/ckeditor.js"></script>
<script src="js/ckeditor/adapters/jquery.js"></script>

<link rel="stylesheet" href="js/bootstrap-multiselect/css/bootstrap-multiselect.css" type="text/css"/>
<script type="text/javascript" src="js/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
<script src="js/cdpselections.js"></script>
<link rel="stylesheet" href="ccd/bootstrap.min.css" type="text/css">

<link rel="stylesheet" href="ccd/select2/select2.min.css" type="text/css"/>
<script type="text/javascript" src="ccd/select2/select2.min.js"></script>
<link rel="stylesheet" href="css/form.css" type="text/css">

<style type="text/css">
.form-group { height:40px; }
.multiselect-container > li > a > label > input[type="checkbox"] {
    margin-left: -30px;
    margin-top: 3px;
}
.form_table tr, .form_table td  { border: none !important; }
.form_table { border:1px solid #B6B6B6; }
</style>




<form name='dispositionForm' id='dispositionForm' action="<?php echo $this->url("task=agent&act=dispositionSubmit");?>" method='post'>
<table class="form_table" cellspacing="0" cellpadding="6" border="0" align="center">
	<tbody>
		<tr class="form_row_head" width='100'>
			<td colspan="2">Closing Disposition</td>
		</tr>
	<tr class="form_row_head" width='100' id='errMsg' style='display:none;'>
			<td colspan="2"></td>
		</tr>
	<tr class="form_row">
		<td class="form_column_caption" align='right'>Skill</td> 
		<td>
			<select name="skill_id" id="skill_id" onChange="reload_skill_emails(this.value);">
				<option value="">Select</option>
				<?php if (is_array($skills)) {
					if (count($skills) == 1) {
						$skill_id = $skills[0]->skill_id;
					}
					foreach ($skills as $skill) {
						if (isset($skill->active) && $skill->active == 'Y'){
							echo '<option value="' . $skill->skill_id . '"';
							if ($skill_id == $skill->skill_id) echo ' selected';
							echo '>' . $skill->skill_name . '</option>';
						}
					}
				}
				?>
			</select>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" align='right'>Disposition</td> 
		<td>
			<?php
			for ($i=0; $i<$num_select_box; $i++) {
					echo '<div id="disposition_id'.$i.'" style="display:none;">';
					echo '<select name="disposition_id'.$i.'" onchange="reload_sels_e('.$i.', this.value);" style="width:100%;">
					<option value="">Select</option>';
					echo '</select> ';
					echo "</div>";
				}
			?>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" align='right'>Note</td>
		<td class='col-md-9'>
			<textarea id='mail_body' name='mail_body' class='note' rows='5'></textarea>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" align='right'>Email</td>
		<td>
			<select id='skill_email' name='skill_email[]' multiple="multiple" class='multiselect'>
			</select>
		</td>
	</tr>
	<tr class="form_row">
		<td></td><td>
		<input type='hidden' name='web_site_url' value="<?php echo $web_site_url; ?>">
		<input type='hidden' name='user_arival_duration' value="<?php echo $user_arival_duration; ?>">
		<input type='hidden' name='cid' value="<?php echo $cid; ?>">
		<input type='hidden' name='email' value="<?php echo $email; ?>">
		<input type='hidden' name='contact' value="<?php echo $contact; ?>">
		<input type='hidden' name='service_id' value="<?php echo $service_id; ?>">
		<input id='btnSend' type='submit' class='btn btn-default btn-send' value='Save Disposition'>
		<!--
		&nbsp;&nbsp;&nbsp;<input id='btnCancel' type='submit' class='btn btn-primary btn-send' value='Cancel'></td>
		-->
	</tr>
	</tbody>
</table>
</form>
<link href="js/lightbox/colorbox.css" rel="stylesheet" media="screen">
<script src="js/lightbox/jquery.colorbox-min.js" type="text/javascript"></script>
<script>
var skill_id = 'C';
$( document ).ready( function() {
	set_loadurl("<?php echo $this->url("task=email&act=chatdispositionchildren");?>");
	set_num_select_box(<?php echo $num_select_box;?>);
	reload_skill_emails('<?php echo $skill_id;?>');
	
	CKEDITOR.config._skillid = '';
	CKEDITOR.config._disp_id = '';
	CKEDITOR.config.height = '125px';
	CKEDITOR.config.extraPlugins = 'cmtemplate';

	$( 'textarea#mail_body' ).ckeditor();
	$('.multiselect').multiselect({buttonClass:'btn'});
	
	load_sels(<?php echo count($disposition_ids);?>);
	/*
	$.ajax({
		type:"POST",
		url: "<?php //echo $this->url("task=email&act=chatSkillDispositions");?>",
		data: { sid: skill_id },
		dataType: "json",
		success:function(data) {
			var option = "";
			option += "<option value=''>Select</option>";
			for (var id in data) {
				option += "<option value='"+id+"'>"+data[id]+"</option>";
			}
			$('#disposition_id0').html(option);
			
		}
	});*/
	/*
	$.ajax({
		type:"POST",
		url: "<?php //echo $this->url("task=email&act=emaillist");?>",
		data: { sid: skill_id },
		dataType: "json",
		success:function(data) {
			var option = "";
			for (var id in data) {
				option += "<option value='"+data[id].email+"'>"+data[id].name+"</option>";
			}
			$('#email').html(option);
			$('.multiselect').multiselect({buttonClass:'btn'});
		}
	});*/
	
	$('#dispositionForm').on('submit', function (e) {
		/*
		var mail_body = $("#mail_body").val();
		var skill_email = $("#skill_email").val();
		if(mail_body.length==0) {
			$("#errMsg").html("Note empty!");
			return false;
		}
		if(skill_email.length==0) {
			$("#errMsg").html("Email address empty!");
			return false;
		}*/
		
        e.preventDefault();
		ShowWait(true,"Please wait...");
		$.ajax({
			type: 'post',
			url: $(this).attr('action'),
			data: $(this).serialize(),
			success: function (data) {
				parent.closeChatBox("<?php echo $cid; ?>");
				parent.$.colorbox({html:"<div class='colorbox-success-msg'>Disposition Saved Successfully!</div>", escKey:true, overlayClose:true});
			
			//, onClosed:function(){ top.parent.closeChatBox("<?php echo $cid; ?>"); }
			}
		});

    });
	/*
	function methodOnClose(user_id) {
		closeChatBox(user_id);
	}*/

	// $(".disposition_select2").select2();
});


function reload_sels_e(i, val) {
	CKEDITOR.config._skillid = skill_id;
	CKEDITOR.config._disp_id = val;
	reload_sels_web_chat(i, skill_id, val);
}

function reload_sels_web_chat(i, sid, val)
{
	if (i < num_select_box) {
		hide_sels(i);
		var j = i+1;
		$select = $("#disposition_id" + j+" select");
		$select.html('<option value="">Select</option>');

		if (val.length > 0) {
			var jqxhr = $.ajax({
				type: "POST",
				url: loadurl, 
				data: { sid: sid, did: val },
				dataType: "json"
			})
			.done(function(data) {
				show_sels(j+1);
				console.log(data.length);
				if (data.length == 0) {
					//console.log('0');
					$select.hide();
					$("#disposition_id" + j).find('.select2-container').remove();
			 	} else {
			 		console.log(data);
					$.each(data, function(key, val){
						$select.append('<option value="' + key + '">' + val + '</option>');
					});
					$select.select2();
				}
			})
			.fail(function() {
				//
			})
			.always(function() {
			});
		}
	
	
	}
}

function reload_skill_emails(val)
{
	skill_id = val;
	$select = $("#skill_email");
	$select.html('<option value="">Select</option>');

	if (val.length > 0) {
		
		CKEDITOR.config._skill_id = val;
		
		$.ajax({
			type: "POST",
			url: "<?php echo $this->url("task=email&act=emaillist");?>",
			data: { sid: val },
			dataType: "json"
		})
		.done(function(data) {
			if (data.length > 0) {
				var option = "";
				for (var id in data) {
					option += "<option value='"+data[id].email+"'>"+data[id].name+"</option>";
				}
				$select.html(option);
				$select.multiselect('destroy');
				$select.multiselect({buttonClass:'btn'});
			}
		})
		.fail(function() {
			//
		})
		.always(function() {
		});
		
		/*
		$.ajax({
			type: "POST",
			url: "<?php //echo $this->url("task=email&act=signature-text");?>",
			data: { sid: val }
		})
		.done(function(data) {
			if (typeof data != 'undefined') {
				$( 'textarea#mail_body' ).val(data);
			}
		})
		.fail(function() {
			//
		})
		.always(function() {
		});
		*/
		
		$.ajax({
			type: "POST",
			url: "<?php echo $this->url("task=email&act=chatSkillDispositions");?>",
			data: { sid: val },
			dataType: "json"
		})
		.done(function(data) {
			if (typeof data != 'undefined' && data != null && data != null) {
				$selector = $("#disposition_id0 select");
				$selector.html('<option value="">Select</option>');
				$.each(data, function(key, val){
					$selector.append('<option value="' + key + '">' + val + '</option>');
				});
				$("#disposition_id0 select").select2();
				
				var totalBox = "<?php echo $num_select_box; ?>";
				totalBox = parseInt( totalBox );
				for ( var idnum = 1; idnum < totalBox; idnum++) {
					option = $('<option></option>').attr("value", "").text("Select");
					selectId = "#disposition_id" + idnum+" select";
					$(selectId).empty().append(option);
					$(selectId).hide();
					$(selectId).select2();
				}
			}
		})
		.fail(function() {
		})
		.always(function() {
		});
	}
}
</script>

