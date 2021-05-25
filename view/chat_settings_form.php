<link rel="stylesheet" href="css/form.css" type="text/css">

<?php if (!empty($errMsg)):?>
	<br />
	<?php if ($errType === 0):?>
		<div class="alert alert-success">
	<?php else: ?>
		<div class="alert alert-error">
	<?php endif;?>	
	<?php echo $errMsg;?>
	</div>
<?php endif; ?>

<form name="frm_ivr_service" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
	<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
		<tbody>
			<tr class="form_row_head">
			  <td colspan=4>Global Chat Settings Information</td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">
					Disposition Window
				</td>
				<td>
					<select id="chat_disposition" name="chat_disposition" style="width:150px; min-width:60px;">
						<option value="Y" <?php if (isset($settings_data['chat_disposition']) && $settings_data['chat_disposition']->value == 'Y') echo ' selected="selected"';?>>Yes</option>
						<option value="N" <?php if (isset($settings_data['chat_disposition']) && $settings_data['chat_disposition']->value == 'N') echo ' selected="selected"';?>>No</option>
					</select>
				</td>
				<td class="form_column_caption">
					Chat Return url
				</td>
				<td>
					<input type="text" id="chat_return_url" name="chat_return_url" value="<?php echo (isset($settings_data['chat_return_url'])) ? $settings_data['chat_return_url']->value : ''; ?>" />
				</td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">
					Greetings text
				</td>
				<td colspan="3">
					<div id="greeting_head" class="row">
						<div class="col-md-2 text-center">Start Time (00:00)</div>
						<div class="col-md-2 text-center">End Time (00:00)</div>
						<div class="col-md-8 text-center"></div>
					</div>
					<div id="greeting_body" class="mb-10">
						<?php
						if(isset($settings_data['greetings_start_time']) || isset($settings_data['greetings_end_time']) || isset($settings_data['greetings_message'])){
							$greetings_start_time = explode(',', $settings_data['greetings_start_time']->value);
							$greetings_end_time = explode(',', $settings_data['greetings_end_time']->value);
							$greetings_message = explode(',', $settings_data['greetings_message']->value);

							foreach ($greetings_start_time as $key => $value) {
						?>
							<div data-row="<?php echo $key; ?>" class="row greeting-row mb-10">
								<div class="col-md-2">
									<input class="start-time greetings-timepicker" type="text" name="greetings[start_time][<?php echo $key; ?>]" value="<?php echo $greetings_start_time[$key]; ?>">
								</div>
								<div class="col-md-2">
									<input class="end-time greetings-timepicker" type="text" name="greetings[end_time][<?php echo $key; ?>]" value="<?php echo $greetings_end_time[$key]; ?>">
								</div>
								<div class="col-md-7">
									<input class="message greetings-message" type="text" name="greetings[message][<?php echo $key; ?>]" value="<?php echo $greetings_message[$key]; ?>">
								</div>
								<div class="col-md-1">
									<a class="btn btn-sm btn-danger greetings-delete">
										<i class="fa fa-trash" aria-hidden="true"></i>
									</a>
								</div>
							</div>
							<?php } ?>
						<?php }else{ ?>
						<div data-row="0" class="row greeting-row mb-10">
							<div class="col-md-2">
								<input class="start-time greetings-timepicker" type="text" name="greetings[start_time][0]">
							</div>
							<div class="col-md-2">
								<input class="end-time greetings-timepicker" type="text" name="greetings[end_time][0]">
							</div>
							<div class="col-md-7">
								<input class="message greetings-message" type="text" name="greetings[message][0]">
							</div>
							<div class="col-md-1">
								<a class="btn btn-sm btn-danger greetings-delete">
									<i class="fa fa-trash" aria-hidden="true"></i>
								</a>
							</div>
						</div>
						<?php } ?>
					</div>
					<div id="greeting_footer" class="row">
						<div class="col-md-12">
							<a id="greetings_add_more" class="btn btn-sm btn-primary pull-right">Add More</a>
						</div>
					</div>
				</td>
			</tr>
            <tr>
                <td class="form_column_caption">
                    Off Hour
                </td>
                <td colspan="3">
                    <div id="greeting_body" class="mb-10">
                        <div data-row="0" class="row greeting-row mb-10">
                            <div class="col-md-2">
                                <input class="start-time greetings-timepicker" type="text" name="offtime_from"
                                       value="<?php echo $settings_data["offtime_from"]->value; ?>">
                            </div>
                            <div class="col-md-2">
                                <input class="start-time greetings-timepicker" type="text" name="offtime_to"
                                       value="<?php echo $settings_data["offtime_to"]->value; ?>">
                            </div>
                        </div>
                    </div>
                </td>
            </tr>


			<tr class="form_row_alt">
				<td class="form_column_caption">OTP Form Name</td>
				<td>
					<input type="text" name="otp_form_name" value="<?php echo (isset($settings_data['otp_form_name'])) ? $settings_data['otp_form_name']->value : ''; ?>">
				</td>
				<td class="form_column_caption">OTP Form Email</td>
				<td>
					<input type="text" name="otp_form_email" value="<?php echo (isset($settings_data['otp_form_email'])) ? $settings_data['otp_form_email']->value : ''; ?>">
				</td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">SMTP Host</td>
				<td>
					<input type="text" name="smtp_host" value="<?php echo (isset($settings_data['smtp_host'])) ? $settings_data['smtp_host']->value : ''; ?>">
				</td>
				<td class="form_column_caption">SMTP Port</td>
				<td>
					<input type="text" name="smtp_port" value="<?php echo (isset($settings_data['smtp_port'])) ? $settings_data['smtp_port']->value : ''; ?>">
				</td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">SMTP Username</td>
				<td>
					<input type="text" name="smtp_username" value="<?php echo (isset($settings_data['smtp_username'])) ? $settings_data['smtp_username']->value : ''; ?>">
				</td>
				<td class="form_column_caption">SMTP Password</td>
				<td>
					<input type="password" class="form-control" name="smtp_password" value="<?php echo (isset($settings_data['smtp_password'])) ? $settings_data['smtp_password']->value : ''; ?>">
				</td>
			</tr>
			<tr class="form_row_alt">
				<td class="form_column_caption">SMTP Secure Option</td>
				<td>
					<select name="smtp_secure_opton">
						<option value="ssl" <?php if (isset($settings_data['smtp_secure_opton']) && $settings_data['smtp_secure_opton']->value == 'ssl') echo ' selected="selected"'; ?> >SSL</option>
						<option value="tls" <?php if (isset($settings_data['smtp_secure_opton']) && $settings_data['smtp_secure_opton']->value == 'tls') echo ' selected="selected"'; ?> >TLS</option>
					</select>
				</td>
				<td class="form_column_caption"></td>
				<td></td>
			</tr>
            <tr class="form_row_alt">
                <td class="form_column_caption"> Google Play Store Link</td>
                <td>
                    <input type="text" name="playstore_link" value="<?php echo (isset($settings_data['playstore_link'])) ? $settings_data['playstore_link']->value : ''; ?>" />
                </td>
                <td class="form_column_caption"> Apple App Store Link</td>
                <td>
                    <input type="text" name="appstore_link" value="<?php echo (isset($settings_data['appstore_link'])) ? $settings_data['appstore_link']->value : ''; ?>" />
                </td>
            </tr>

            <tr class="form_row_alt">
                <td class="form_column_caption"> Chat Queue Text</td>
                <td colspan="3">
                    <textarea type="text" name="chat_queue_text" style="width: 100%" ><?php echo (isset($settings_data['chat_queue_text'])) ? $settings_data['chat_queue_text']->value : ''; ?></textarea>
                </td>
            </tr>

            <tr class="form_row_alt">
                <td class="form_column_caption"> ICE Feedback Message </td>
                <td colspan="3">
                    <textarea type="text" name="ice_feedback_msg" style="width: 100%" ><?php echo (isset($settings_data['ice_feedback_msg'])) ? $settings_data['ice_feedback_msg']->value : ''; ?></textarea>
                </td>
            </tr>

            <tr class="form_row_alt">
                <td class="form_column_caption">  ICE Feedback Message For Blank Webchat </td>
                <td colspan="3">
                    <textarea type="text" name="blank_ice_feedback_msg" style="width: 100%" ><?php echo (isset($settings_data['blank_ice_feedback_msg'])) ? $settings_data['blank_ice_feedback_msg']->value : ''; ?></textarea>
                </td>
            </tr>
			<tr class="form_row">
				<td colspan="2" class="form_column_submit">
					<input class="form_submit_button" type="submit" value="  Update  " name="submitservice"> <br><br>
				</td>
			</tr>
		</tbody>
	</table>
</form>

<script type="text/javascript">
	function remove_greetings_row(){
		$('a.greetings-delete').on('click', function(){
			$(this).parents('div.greeting-row').remove();
			var length = parseInt($("div.greeting-row").length);

			$.each($('.greeting-row'), function(idx, item){
				console.log(idx);
				$(this).attr('data-row', idx);

				$(this).find('input.start-time').attr('name', 'greetings[start_time]['+idx+']');
				$(this).find('input.end-time').attr('name', 'greetings[end_time]['+idx+']');
				$(this).find('input.message').attr('name', 'greetings[message]['+idx+']');
			});
		});
	}

	function set_timepicker(){
		$('.greetings-timepicker').datetimepicker({
		  	datepicker:false,
		  	format:'H:i',
		  	mask:true
		});
	}

	$( document ).ready(function() {
	    $('.greetings-timepicker').datetimepicker({
		  	datepicker:false,
		  	format:'H:i',
		  	mask:true
		});
	    remove_greetings_row();

		$('#greetings_add_more').on('click', function(){
			var $container = $("#greeting_body");
   			var clone_dom = $(".greeting-row:last", $container).clone().appendTo($container);
			var length = parseInt($("div.greeting-row").length);

			clone_dom.attr('data-row', length-1);

			clone_dom.find('input.start-time').attr('name', 'greetings[start_time]['+(length-1)+']');
			clone_dom.find('input.end-time').attr('name', 'greetings[end_time]['+(length-1)+']');
			clone_dom.find('input.message').attr('name', 'greetings[message]['+(length-1)+']');

			remove_greetings_row();
			set_timepicker();
		});
	});
</script>