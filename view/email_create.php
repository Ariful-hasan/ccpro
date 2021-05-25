<?php $num_select_box = 6;?>
<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>
<script type="text/javascript" src="js/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="js/bootstrap-multiselect/css/bootstrap-multiselect.css" type="text/css"/>
<link href="js/summernote/summernote.css" rel="stylesheet">
<script src="js/summernote/summernote.js"></script>
<script type="text/javascript" src="js/toastr/toastr.min.js"></script>

<script type="text/javascript" src="js/jquery.browser.min.js"></script>
<script type="text/javascript" src="js/bnKb/driver.phonetic.js?v=1.0" charset="utf-8"></script>
<script type="text/javascript" src="js/bnKb/engine.js?v=1.0" charset="utf-8"></script>

<script src="js/dpselections.js"></script>
<link rel="stylesheet" href="css/multiselect.css" type="text/css">

<script type="text/javascript">
set_loadurl("<?php echo $this->url("task=email&act=dispositionchildren");?>");
set_num_select_box(<?php echo $num_select_box;?>);
var default_lan = '';

$(document).ready(function() {
	$('.multiselect').multiselect({buttonClass:'btn'});
	load_sels(<?php echo count($disposition_ids);?>);

	$('#mail_body').summernote({
        height: 200, // set editor height
        minHeight: null, // set minimum height of editor
        maxHeight: null, // set maximum height of editor
        focus: true,
        callbacks: {
            onImageUpload: function (files) {
                var data = new FormData();
                var summernote = this;
                data.append("file", files[0]);
                $.ajax({
                    data: data,
                    type: "POST",
                    url: '',
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(data) {
                        if(data.result==true){
                            var image = $('<img>').attr('src', data.url).attr('style','width:100%');
                            $('#mail_body').summernote("insertNode", image[0]);
                        }else{
                            INSTA.Lib.iniToastrNotification("error", "Error", data.message);
                        }
                    },
                    beforeSend: function(xhr){

                    }
                });
            },
//           	onFocus: function(){
//           		if(default_lan == ''){
//           			console.log(default_lan);
//				    $('.summernote-area .note-codable').bnKb({
//				        'switchkey': {"webkit":"k","mozilla":"y","safari":"k","chrome":"k","msie":"y"},
//				        'driver': phonetic,
//						'writingMode': 'b'
//				    });
//				  	$('.note-editable').bnKb({
//				        'switchkey': {"webkit":"k","mozilla":"y","safari":"k","chrome":"k","msie":"y"},
//				        'driver': phonetic,
//						'writingMode': 'b'
//				    });
//				    default_lan = 'b';
//				    console.log(default_lan);
//				}
//           	},
            onChange: function(contents, $editable) {
                console.log('onChange:', contents, $editable);
            }
        },
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['insert', ['link', 'picture']],
            ['codeview', ['codeview']]
        ]
    });

   	$('.summernote-area .note-codable').unbind('keyup');
   	$('.summernote-area .note-codable').keyup(function(){
       	$(this).closest('.note-editing-area').find('.note-editable').html(this.value);
       	$(this).closest('.summernote-area').find('.summernote-mini').html(this.value);
       	$('#mail_body').val(this.value);
    	// console.log(this.value);
   	});
   	$("#frm_ivr_service").validate({
        errorClass: "form-error",
        errorElement: "span",
    });

   	var font_falg_html = '';
   	font_falg_html +='<div class="note-btn-group btn-group note-codeview"><div class="checkbox-inline language-check"><label class="radio-container">Bangla';
   	font_falg_html +='<input id="language_bn" name="language" type="radio" value="bn" onclick="setBangla()" checked="checked" >';
   	font_falg_html +='<span class="radio-checkmark"></span></label>';
    font_falg_html +='<label class="radio-container">English';
    font_falg_html +='<input id="language_en" name="language" type="radio" value="en" onclick="setEnglish()"  >';
    font_falg_html +='<span class="radio-checkmark"></span></label></div></div>';
    $('.note-toolbar').append(font_falg_html);
    setBangla();
});

function setEnglish(){
//    if(default_lan === 'b'){
     console.log(default_lan);
        $('.summernote-area .note-codable').bnKb({
            'switchkey': {"webkit":"k","mozilla":"y","safari":"k","chrome":"k","msie":"y"},
            'driver': null,
            'writingMode': 'en'
        });
            $('.note-editable').bnKb({
            'switchkey': {"webkit":"k","mozilla":"y","safari":"k","chrome":"k","msie":"y"},
            'driver': null,
            'writingMode': 'en'
        });
        default_lan = 'en';
        console.log(default_lan);
//    }
}
function setBangla(){
//    if(default_lan == ''){
    console.log(default_lan);
        $('.summernote-area .note-codable').bnKb({
            'switchkey': {"webkit":"k","mozilla":"y","safari":"k","chrome":"k","msie":"y"},
            'driver': phonetic,
            'writingMode': 'b'
        });
            $('.note-editable').bnKb({
            'switchkey': {"webkit":"k","mozilla":"y","safari":"k","chrome":"k","msie":"y"},
            'driver': phonetic,
            'writingMode': 'b'
        });
        default_lan = 'b';
        console.log(default_lan);
//    }
}

function setDefaultLanguage(){
	
}

function reload_sels_e(i, val) {
	// CKEDITOR.config._disp_id = val;
	reload_sels(i, val);
}

function reload_skill_emails(val='')
{
	$select = $("#skill_email");
	$select.html('<option value="">Select</option>');
	if (typeof val != 'undefined' && val.length > 0) {

		// CKEDITOR.config._skillid = val;
		
		$.ajax({
			type: "POST",
			url: "<?php echo $this->url("task=email&act=skillemails");?>",
			data: { sid: val },
			dataType: "json"
		})
		.done(function(data) {
			if (typeof data != 'undefined' && data != null && data.length > 0) {
				$.each(data, function(key, val){
					$select.append('<option value="' + val + '">' + val + '</option>');
				})
			}
		})
		.fail(function() {
			
		})
		.always(function() {

		});

		$.ajax({
			type: "POST",
			url: "<?php echo $this->url("task=email&act=signature-text");?>",
			data: { sid: val }
		})
		.done(function(data) {
			if (typeof data != 'undefined') {
				$( 'textarea#mail_body' ).val(data);
			}
		})
		.fail(function() {

		})
		.always(function() {

		});

		var disposition_url = "";
        <?php if (!empty($type)): ?>
            disposition_url = "<?php echo $this->url("task=email&act=skillDispositions&type=ticket");?>"
        <?php else:?>
            disposition_url = "<?php echo $this->url("task=email&act=skillDispositions");?>"
        <?php endif;?>
        
		$.ajax({
			type: "POST",

			url: disposition_url,
			data: { sid: val },
			dataType: "json"
		})
		.done(function(data) {
			if (typeof data != 'undefined' && data != null && data != null) {
				$selector = $("#disposition_id0");
				$selector.html('<option value="">Select</option>');
				$.each(data, function(key, val){
					$selector.append('<option value="' + key + '">' + val + '</option>');
				});
				var totalBox = "<?php echo $num_select_box; ?>";
				totalBox = parseInt( totalBox );
				for ( var idnum = 1; idnum < totalBox; idnum++) {
					option = $('<option></option>').attr("value", "").text("Select");
					selectId = "#disposition_id" + idnum;
					$(selectId).empty().append(option);
					$(selectId).hide();
				}
			}
		})
		.fail(function() {

		})
		.always(function() {

		});
	}
}

function checkMsg() {
	var pattern = "<?php echo $replace_text_pattern; ?>";
	if($("#mail_body").val() != ""){
		var msg = $("#mail_body").val().toLowerCase();
		if (pattern != "" && pattern != null && msg.indexOf(pattern.toLowerCase()) >= 0) {
			toastr.error("You must replace the fixed text: "+pattern);
		} else {
			var st = $("#status").val();
			if (st.length == 0) {
				toastr.error('Please set the status of the ticket!');
				$( "#status" ).focus();
				return false;
			}
			return true;
		}
	} else {
		toastr.error("Message can not be empty!");
	}
	return false;
}
</script>

<?php if (!empty($type)){ ?>
<form id="frm_ivr_service" name="frm_ivr_service" class="form-horizontal" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName()."&type=ticket");?>" onsubmit="return checkMsg();">
<?php } else {?>
<form id="frm_ivr_service" name="frm_ivr_service" class="form-horizontal" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>" onsubmit="return checkMsg();">
<?php }?>

	<div class="panel-body">
	    <div class="row">
	        <div class="col-md-12 col-sm-12">                
	            <?php if(isset($errMsg) && !empty($errMsg)){ ?>
	            <div class="alert alert-danger alert-dismissible" role="alert">
	                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	                <strong>Error!</strong> <?=$errMsg?>
	            </div>
	            <?php } ?>
	        </div>
	    </div>
	    <div class="row">
	        <div class="col-md-12 col-sm-12">
	            <div class="form-group form-group-sm mb-15">
	                <label for="name" class="control-label col-md-2 col-sm-2 pr-0">
	                	<?php if (!empty($type)){ ?>Customer Name:<span class='error'>*</span><?php } else {?>Ticket Owner's Name:<?php }?>
	                </label>
	                <div class="col-md-10 col-sm-10">
	                    <input type="text" id="name" name="name" size="30" maxlength="50" value="<?php echo $name;?>" class="form-control" <?php if (!empty($type)){ ?>data-rule-required='true' data-msg-required='Customer Name is required!'<?php } ?> data-rule-maxlength='50' data-msg-maxlength='Please enter no more than 50 characters!' />
	                </div>
	            </div>
	        </div>

	        <?php if (!empty($type)){ ?>
	        <div class="col-md-12 col-sm-12">
	            <div class="form-group form-group-sm mb-15">
	                <label for="account_id" class="control-label col-md-2 col-sm-2 pr-0">
	                	Account/Card No:<span class='error'>*</span>
	                </label>
	                <div class="col-md-10 col-sm-10">
	                    <input type="text" id="account_id" name="account_id" size="30" maxlength="20" value="<?php echo $account_id;?>" class="form-control" data-rule-required='true' data-msg-required='Account/Card No is required!' data-rule-maxlength='20' data-msg-maxlength='Please enter no more than 20 characters!' />
	                </div>
	            </div>
	        </div>
	        <div class="col-md-12 col-sm-12">
	            <div class="form-group form-group-sm mb-15">
	                <label for="created_for" class="control-label col-md-2 col-sm-2 pr-0">
	                	Mobile No:<span class='error'>*</span>
	                </label>
	                <div class="col-md-10 col-sm-10">
	                    <input type="text" id="created_for" name="created_for" size="30" maxlength="50" value="<?php echo $account_id;?>" class="form-control" data-rule-required='true' data-msg-required='Mobile No is required!' data-rule-maxlength='50' data-msg-maxlength='Please enter no more than 50 characters!' />
	                </div>
	            </div>
	        </div>
	    	<?php }?>
	    	<?php if (empty($type)){ ?>    	
	        <div class="col-md-12 col-sm-12">
	            <div class="form-group form-group-sm mb-15">
	                <label for="email" class="control-label col-md-2 col-sm-2 pr-0">
	                	Ticket Owner's Email:<span class='error'>*</span>
	                </label>
	                <div class="col-md-10 col-sm-10">
	                    <input type="text" id="email" name="email" size="30" maxlength="50" value="<?php echo $email;?>" class="form-control" data-rule-required='true' data-msg-required='Ticket Owner Email is required!' data-rule-maxlength='50' data-msg-maxlength='Please enter no more than 50 characters!' />
	                </div>
	            </div>
	        </div>
	        <div class="col-md-12 col-sm-12">
	            <div class="form-group form-group-sm mb-15">
	                <label for="cc_emails" class="control-label col-md-2 col-sm-2 pr-0">
	                	CC:
	                </label>
	                <div class="col-md-10 col-sm-10">
	                    <select id="cc_emails" class="multiselect" multiple="multiple" name="cc[]">
	                    	<?php 
							if (is_array($ccmails)) { 
								foreach ($ccmails as $ccmail) { 
									echo '<option value="'.$ccmail->email.'">'.$ccmail->name.'</option>';
								}
							} 
							?>							
						</select>
	                </div>
	            </div>
	        </div>
	        <div class="col-md-12 col-sm-12">
	            <div class="form-group form-group-sm mb-15">
	                <label for="bcc_emails" class="control-label col-md-2 col-sm-2 pr-0">
	                	BCC:
	                </label>
	                <div class="col-md-10 col-sm-10">
	                    <select id="bcc_emails" class="multiselect" multiple="multiple" name="bcc[]">
	                    	<?php 
							if (is_array($ccmails)) { 
								foreach ($ccmails as $ccmail) { 
									echo '<option value="'.$ccmail->email.'">'.$ccmail->name.'</option>';
								}
							} 
							?>
						</select>
	                </div>
	            </div>
	        </div>
	    	<?php }?>
	        <div class="col-md-12 col-sm-12">
	            <div class="form-group form-group-sm mb-15">
	                <label for="skill_id" class="control-label col-md-2 col-sm-2 pr-0">
	                	Skill:<span class="error">*</span>
	                </label>
	                <div class="col-md-10 col-sm-10">
	                    <?php if (!empty($type)):?>
			                <select class="form-control" name="skill_id" id="skill_id" onChange="reload_skill_emails($(this).find('option:selected').attr('pup'));" data-rule-required='true' data-msg-required='Skill is required!'>
			            <?php else:?>
			                <select class="form-control" name="skill_id" id="skill_id" onChange="reload_skill_emails(this.value);" data-rule-required='true' data-msg-required='Skill is required!'>
			            <?php endif;?>
							<option value="">Select</option>
							<?php if (is_array($skills)) {
								foreach ($skills as $skill) {
									if (isset($skill->active) && $skill->active == 'Y'){
										echo '<option value="' . $skill->skill_id . '" pup="'. $skill->popup_url. '"';
										if ($skill_id == $skill->skill_id) echo ' selected';
										echo '>' . $skill->skill_name . '</option>';
									}
								}
							}
							?>
						</select>
	                </div>
	            </div>
	        </div>
	        <?php if (empty($type)){ ?>
		        <?php  /* if (count($emails) > 0) { */ ?>
		        <div class="col-md-12 col-sm-12">
		            <div class="form-group form-group-sm mb-15">
		                <label for="skill_email" class="control-label col-md-2 col-sm-2 pr-0">
		                	Skill Email:<span class="error">*</span>
		                </label>
		                <div class="col-md-10 col-sm-10">
		                    <select name="skill_email" id="skill_email" class="form-control" data-rule-required='true' data-msg-required='Skill Email is required!'>
			                    <option value="">Select</option>
			                    <?php if (is_array($emails)) {
			                        foreach ($emails as $semail) {
			                            echo '<option value="' . $semail . '"';
			                            if ($skill_email == $semail) echo ' selected';
			                            echo '>' . $semail . '</option>';
			                        }
			                    }
			                    ?>
			                </select>
		                </div>
		            </div>
		        </div>
		        <?php /*}*/ ?>
		    <?php }?>
		    <div class="col-md-12 col-sm-12">
	            <div class="form-group form-group-sm mb-15">
	                <label for="disposition_id" class="control-label col-md-2 col-sm-2 pr-0">
	                	Disposition:<span class="error">*</span>
	                </label>
	                <div class="col-md-10 col-sm-10">
	                    <?php
						//var_dump($num_select_box);
						for ($i=0; $i<$num_select_box; $i++) {
							echo '<select name="disposition_id'.$i.'" id="disposition_id'.$i.'" style="display:none;" onchange="reload_sels_e('.$i.', this.value);" class="form-control" data-rule-required="true" data-msg-required="Disposition is required!"><option value="">Select</option>';
							/*if ($i == 0 || isset($disposition_ids[$i])) {
								if (!isset(${'dispositions'.$i})) {
									if ($i > 0) {
										$dispositions = $email_model->getDispositionChildrenOptions($skill_id, $disposition_ids[$i-1][0], false);
		//var_dump($dispositions);
									} else {
										$dispositions = $email_model->getDispositionChildrenOptions($skill_id, '', false);
									}
								} else {
									$dispositions = ${'dispositions'.$i};
								}
							
								foreach ($dispositions as $_dispositionid=>$_title) {
									$did = isset($disposition_ids[$i]) ? $disposition_ids[$i][0] : '';
									echo '<option value="' . $_dispositionid . '"';
									if ($did == $_dispositionid) echo ' selected';
									echo '>' . $_title . '</option>';
								}
							}*/
							echo '</select> ';
						}			
						?>
	                </div>
	            </div>
	        </div>
	        <?php if (empty($type)){ ?>
	        <div class="col-md-12 col-sm-12">
	            <div class="form-group form-group-sm mb-15">
	                <label for="title" class="control-label col-md-2 col-sm-2 pr-0">
	                	Ticket Title:<span class="error">*</span>
	                </label>
	                <div class="col-md-10 col-sm-10">
	                    <input type="text" id="title" name="title" size="30" maxlength="100" value="<?php echo $title;?>" class="form-control" data-rule-required="true" data-msg-required="Ticket Title is required!" data-rule-maxlength='100' data-msg-maxlength='Please enter no more than 100 characters!' />
	                </div>
	            </div>
	        </div>
		    <?php } ?>
	        <div class="col-md-12 col-sm-12">
	            <div class="form-group form-group-sm mb-15">
	                <label for="mail_body" class="control-label col-md-2 col-sm-2 pr-0">
	                	Text:<span class="error">*</span>
	                </label>
	                <div class="col-md-10 col-sm-10 summernote-area">
	                    <textarea name="mail_body" id="mail_body" data-rule-required="true" data-msg-required="Text is required!" class="bangla summernote-mini"><?php echo $mail_body;?></textarea>
	                </div>
	            </div>
	        </div>
	        <div class="col-md-12 col-sm-12">
	            <div class="form-group form-group-sm mb-15">
	                <label for="status" class="control-label col-md-2 col-sm-2 pr-0">
	                	Status:<span class="error">*</span>
	                </label>
	                <div class="col-md-10 col-sm-10">
	                    <select name="status" id="status" class="form-control" data-rule-required="true" data-msg-required="Status is required!">
							<option value="">Select</option>
							<?php if (is_array($changable_status)) {
								foreach ($changable_status as $st) {
									echo '<option value="' . $st . '"';
									if ($status == $st) echo ' selected';
									echo '>' . $email_model->getTicketStatusLabel($st) . '</option>';
								}
							}
							?>
						</select>
	                </div>
	            </div>
	        </div>
	        <?php if (!empty($type)){ ?>
	        <div class="col-md-12 col-sm-12">
	            <div class="form-group form-group-sm mb-15">
	                <label for="category_id" class="control-label col-md-2 col-sm-2 pr-0">
	                	Category:<span class="error">*</span>
	                </label>
	                <div class="col-md-10 col-sm-10">
	                    <select name="category_id" id="category_id" class="form-control" data-rule-required="true" data-msg-required="Category is required!">
		                    <option value="">Select</option>
		                    <?php if (!empty($ticket_category)){?>
		                        <?php foreach ($ticket_category as $item => $value){?>
		                            <option value="<?php echo $item?>" <?php echo $category_id==$item?"selected='selected'":"" ?> ><?php echo $value?></option>
		                        <?php }?>
		                    <?php }?>
		                </select>
	                </div>
	            </div>
	        </div>
		    <?php }?>

	        <div class="row">
	        	<div class="col-md-12 col-sm-12 text-center">
		            <input class="btn btn-success form_submit_button" type="submit" value="Generate Ticket" name="submitservice">
		        </div>
	        </div>
	    </div>
	</div>
</form>
