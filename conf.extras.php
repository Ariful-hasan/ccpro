<?php
	$extra = new stdClass();
	$extra->report_country_prefix = 'BD-';
	$extra->tele_banking = false;
	$extra->file_upload_path = "/usr/local/ccpro/";
	$extra->file_music_path = "/usr/local/ccpro/";
	$extra->voice_logger_path = "/usr/local/ccpro/";
	$extra->screen_logger_path = "/usr/local/ccpro/";
	$extra->is_voice_file_downloadable = true;
	$extra->cdr_csv_save_path = "/tmp/"; 
	$extra->ss7_link_status = true;
	$extra->email_module = true;
	$extra->chat_module = true;
	$extra->show_sccb = true; // secondary call control bar
	$extra->did_prefix_replace = [['from' => 44, 'to' => '']];
	$extra->highlight_dashboard_row_after = 25; // Highlight tabular dashboard(agent) row after serving time passes threshold

	$extra->voice_synth_module = true;
	$extra->sip_logger_path = "/usr/local/ccpro/";
		
	$extra->module_support = false;
	if (!$extra->module_support) {
		$extra->email_module = true;
		$extra->chat_module = true;
		$extra->tele_banking = true;
	}
	/* Service level calculation method */
	$extra->sl_method = 'B';
	
	// tts config
	$extra->ttsUsername = "cct";
	$extra->ttsPassword = "gPlex@124";
	$extra->ttsUrl = "http://192.168.10.11/tts/index.php";
	//$extra->bkp_audio_url = "http://192.168.10.64/generate_audio_video.php";
	$extra->bkp_audio_url = "call_av_generate_script.php";
	$extra->storage_srv_domain = '192.168.10.64';
	
	$extra->upload_email_body_image = "http://cc-gsoft.gplex.net/ccprodev/upload_email_body_image.php";
	$extra->read_email_body_image = "http://cc-gsoft.gplex.net/ccprodev/read_email_body_image.php";
	$extra->delete_email_body_image = "http://cc-gsoft.gplex.net/ccprodev/delete_email_body_image.php";


	//File Upload Path
//    $extra->file_upload_url = "/usr/local/apache2/htdocs/ccprodev/content/";
    $extra->file_upload_url = "F:/XAMPP 7.2.25/htdocs/ccpro/content/";

?>