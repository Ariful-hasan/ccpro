<?php
if(!function_exists('random_number')){
	function random_number($characters, $length){
		$charactersLength = strlen($characters);
    	$randomString = '';
    	for ($i = 0; $i < $length; $i++) {
        	$randomString .= $characters[rand(0, $charactersLength - 1)];
    	}
    	return $randomString;
    }
}

if(!function_exists('generate_base64_encode')){
	function generate_base64_encode($string, $loop){
		$encode_str = $string;
    	for ($i = 0; $i < $loop; $i++) {
        	$encode_str = base64_encode($encode_str);
        	$encode_str = strrev($encode_str);
    	}
    	return $encode_str;
    }
}

if(!function_exists('generate_base64_decode')){
	function generate_base64_decode($string, $loop){
		$decode_str = $string;
    	for ($i = 0; $i < $loop; $i++) {
        	$decode_str = strrev($decode_str);
        	$decode_str = base64_decode($decode_str);
    	}
    	return $decode_str;
    }
}

if(!function_exists('get_module_list')){
    function get_module_list($idx = null){
        $module = [
            MOD_AGENTS => 'Agents',
            MOD_CTI => 'CTI',
            MOD_IVR => 'IVR',
            MOD_SKILL => 'Skill',
            MOD_CHAT_DISP => 'Chat Disposition',
            MOD_EMAIL_DISP => 'Email Disposition',
            MOD_CAMPAIGN => 'Campaign Management',
            MOD_LEAD => 'Lead Management',
            MOD_DISPOSITION => 'Disposition Code',
            MOD_DASHBOARD => 'Dashboard',
            MOD_SETTINGS => 'Tools',
            MOD_LANGUAGE => 'Language',
            MOD_EXTERNAL_USER => 'External User',
            MOD_REPORT_PARTITIONS => 'Report Partitions',
            MOD_CALL_QUALITY_PROFILE => 'Call Quality Profile',
            MOD_AGENT_SHIFT_PROFILE => 'Agent Shift Profile',
            MOD_AGENT_HOTKEY => 'Agent Hotkey',
            MOD_BUSY_MESSAGE => 'Busy Message',
            MOD_IVR_DISPOSITION => 'IVR Disposition Code',
            MOD_IVR_API_CONNECTIONS => 'IVR API Connections',
            MOD_SEAT => 'Seat',
            MOD_SERVICE_LEVEL_CALCULATION => 'Service Level Calculation',
            MOD_PASSWORD => 'Password',
            MOD_SKILL_CRM_TEMPLATE => 'Skill CRM Templates',
            MOD_APP_CONFIGURATION => 'App Configuration',
            MOD_MAP_ADDRESS => 'Map Address',
            MOD_TRUNK => 'Trunk',
            MOD_CC_BCC_EMAILS => 'CC/BCC Emails',
            MOD_SMS_API => 'SMS Api',
            MOD_CRM => 'CRM',
            MOD_CRM_INBOUND => 'CRM-Inbound',
            MOD_VOICE_MAIL => 'Voice Mail',
            MOD_TICKET => 'Ticket Management',
            MOD_TICKET_DASHBOARD => 'Ticket Dashboard',
            MOD_TICKET_CATEGORY => 'Ticket Category',
            MOD_TICKET_REPORT => 'Ticket Report',
            MOD_EMAIL_MANAGEMENT => 'Email',
            MOD_EMAIL_TEMPLATE => 'Email Template',
            MOD_CHAT_PAGE => 'Chat Page',
            MOD_CHAT_TEMPLATE => 'Chat Template',
            MOD_CHAT_REPORT => 'Chat Report',
            MOD_CALL_CONTROL => 'Call Control',
            MOD_AGENT_PROFILE => 'Agent Profile',
            MOD_EMAIL_FETCH_INBOX => 'Email Fetch Inbox',
        ];

        if($idx)
            return $module[$idx];
        else
            return $module;
    }
}

if(!function_exists('get_CRUD_list')){
    function get_CRUD_list(){
        return [
            ACCESS_READ => "View",
            ACCESS_ADD => 'Add',
            ACCESS_UPDATE => 'Update',
            ACCESS_DELETE => 'Delete',
            ACCESS_STATUS => 'Status Change',
        ];
    }
}

if(!function_exists('get_extra_CRUD_list')){
    function get_extra_CRUD_list(){
        return [
            ACCESS_PASSWORD_RESET => 'Password Reset',
            ACCESS_PIN_RESET => 'Pin Reset'
        ];
    }
}

if(!function_exists('get_status_list')){
    function get_status_list(){
        return [
            STATUS_ACTIVE => 'Active',
            STATUS_INACTIVE => 'InActive',
        ];
    }
}

if(!function_exists('get_page_layout_list')){
    function get_page_layout_list(){
        return [
            PAGE_DEFAULT => 'Default',
            PAGE_REPORT => 'Report',
        ];
    }
}

if(!function_exists('get_email_fetch_method_list')){
    function get_email_fetch_method_list(){
        return [
            EMAIL_POP3 => 'POP3',
            EMAIL_IMAP => 'IMAP',
        ];
    }
}

if(!function_exists('redirect_page')){
    function redirect_page($url){
        header('Location: '.$url);
        die();
    }
}

if(!function_exists('dd')){
    function dd($value){
        echo "<pre>";
        var_dump($value);
        echo "</pre>";
        die();
    }
}
if(!function_exists('pr')){
    function pr($value){
        echo "<pre>";
        var_dump($value);
    }
}
// convert REPORT_DATE_FORMAT to YYYY-MM-DD format
if(!function_exists('generic_date_format')){
    function generic_date_format($value, $format = REPORT_DATE_FORMAT){
       return date_format(date_create_from_format($format, $value),'Y-m-d');
    }
}
// convert REPORT_DATE_TIME_FORMAT to YYYY-MM-DD format
if(!function_exists('generic_date_format_from_report_datetime')){
    function generic_date_format_from_report_datetime($value, $format = REPORT_DATE_FORMAT){
        return date_format(date_create_from_format($format . " H:i", $value), 'Y-m-d');
    }
}
// convert YYYY-MM-DD format to REPORT_DATE_FORMAT
if(!function_exists('report_date_format')){
    function report_date_format($value){
       return date_format(date_create_from_format('Y-m-d',$value),REPORT_DATE_FORMAT);
    }
}

if(!function_exists('report_type_list')){
    function report_type_list($except=null){
        $types = array(
            REPORT_15_MIN_INV => '15 Mins Interval',
            REPORT_HALF_HOURLY => 'Half Hourly',
            REPORT_HOURLY => 'Hourly',
            REPORT_DAILY => 'Daily',
            REPORT_MONTHLY => 'Monthly',
            // REPORT_QUARTERLY => 'Quarterly',
            // REPORT_YEARLY => 'Yearly',
        );

        if(REPORT_15_30_OFF=='Y'){
            $types = array_slice($types, 2);
        }

        $res = array();
        if (!empty($except) && is_array($except)){
            foreach ($types as $key => $value){
                if (!in_array($key, $except))
                    $res[$key] =$value;
            }
            return $res;
        }
        return $types;
    }
}

// report type list
if(!function_exists('report_sminute_list')){
    function report_sminute_list(){
       return [
            '00' => '00',
            '15' => '15',
            '30' => '30',
            '45' => '45'
        ];
    }
}

// report type list
if(!function_exists('disposition_type_list')){
    function disposition_type_list(){
       return [
            'Q' => 'Query',
            'R' => 'Request',
            'C' => 'Complaint'
        ];
    }
}


if(!function_exists('get_disc_party')){
    function get_disc_party($key){
        $disc_list = [
            'A' => 'Agent',
            'C' => 'Customer',
            'S' => 'Systems',
            'T' => 'Transfer',
            'I' => 'IVR',
            'Q' => 'Queue'
        ];
        if(empty($key))
            return $disc_list;
        else
            return $disc_list[$key];
    }
}
if(!function_exists('get_report_date_format_list')){
    function get_report_date_format_list(){
        $list = [
            REPORT_DATE_FORMAT => 'DD/MM/YYYY',
            REPORT_DATE_FORMAT2 => 'MM/DD/YYYY',
        ];
        return $list;
    }
}

if(!function_exists('date_format_option')){
    function date_format_option($default=null){
        $select = "<select name='date_format' id='date_format' class='form-control'>";
        if (!empty(get_report_date_format_list())){
            foreach (get_report_date_format_list() as $key => $value) {
                $str = $key==$default?"selected='selected'":"";
                $select .= "<option value='$key' $str > $value</option>";
            }
        }
        //var_dump($default);die;
        $select .= "<select>";
        return $select;
    }

}

if(!function_exists('get_time_ago')){
    function get_time_ago($dateTime, $isBracket=true){
        $timestamp = $dateTime;
        $strTime = array("second", "minute", "hour", "day", "month", "year");
        $length = array("60","60","24","30","12","10");

        $currentTime = time();
        if($currentTime >= $timestamp) {
            $diff     = time()- $timestamp;
            for($i = 0; $diff >= $length[$i] && $i < count($length)-1; $i++) {
                $diff = $diff / $length[$i];
            }

            $diff = round($diff);
            if (!$isBracket) return $diff . " " . $strTime[$i] . "(s) ago";
            return "( ".$diff . " " . $strTime[$i] . "'s ago )";
        }
    }

}
if(!function_exists('get_skill_type_list')){
    function get_skill_type_list(){
        $list = [
            'V' => 'Inbound',
            'P' => 'Outbound',
            'E' => 'Email',
            'C' => 'WebChat',
        ];
        return $list;
    }
}
if(!function_exists('get_role_list')){
    function get_role_list(){
        $list = [
            'A'=>'Agent',
            'R'=>'Root User',
            'S'=>'Supervisor',
            'D'=>'Dashboard User',
            'P'=>'Report User',
            'G'=>'Digicon Report User',
        ];
        return $list;
    }
}

if(!function_exists('getFileTypeIcon')){
    function getFileTypeIcon($extension, $imagePathObject){
        $extension = strtoupper($extension);
        if ($extension=='PNG' || $extension=='JPEG' || $extension=='JPG'){
            if (!empty($imagePathObject)){
                $yy = date("y", $imagePathObject->tstamp);
                $mm = date("m", $imagePathObject->tstamp);
                $dd = date("d", $imagePathObject->tstamp);
                $tid = !empty($imagePathObject->ticket_id) ? $imagePathObject->ticket_id : $imagePathObject->tid;
                $sl = !empty($imagePathObject->mail_sl) ?$imagePathObject->mail_sl : $imagePathObject->sl;
                $fname = !empty($imagePathObject->file_name) ? $imagePathObject->file_name : $imagePathObject->fname;
                $image_path = $imagePathObject->path . '' . $yy . $mm . '/' . $dd . '/' . $tid . '/' . $sl . '/' . $fname;
            }
            //return "<img src='$image_path' alt='Attachment'>";
            return '<i class="fa fa-file-image-o" aria-hidden="true"></i>';
        }
        elseif($extension=='PLAIN' || $extension=='TXT' || $extension=='DOCX'){
            return '<i class="fa fa-file-word-o" aria-hidden="true"></i>';
        }
        elseif ($extension=='X-ZIP-COMPRESSED'){
            return '<i class="fa fa-file-archive-o" aria-hidden="true"></i>';
        }
        elseif($extension=='PDF'){
            return '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>';
        }elseif($extension=='XLSX'){
            return '<i class="fa fa-file-excel-o" aria-hidden="true"></i>';
        }else {
            return '<i class="fa fa-file-o" aria-hidden="true"></i>';
        }
    }
}

if(!function_exists('get_report_skill_type_list')){
    function get_report_skill_type_list(){
        $list = [
            'V' => 'Inbound',
            'P' => 'Predictive',
            'E' => 'Email',
            'C' => 'WebChat',
        ];
        return $list;
    }
}

if(!function_exists('get_report_email_activity_list')){
    function get_report_email_activity_list(){
        $list = [
            'V'=>'View',
            'M'=>'Mail Send', 
            'A'=>'Assign Agent', 
            'D'=>'Disposition', 
            'F'=>'Forward', 
            'K'=>'Skill', 
            // 'P'=>'Pull', 
            // 'C'=>'Call Land', 
            'I'=>'Skip',
            'SO' => 'Status-New',
            'SP' => 'Status-Pending',
            'SC' => 'Status-Pending(Client)',
            'SS' => 'Status-Served',
            'SE' => 'Status-Closed',
            'SH' => 'Status-Hold',
            'SK' => 'Status-Park',
            'SR' => 'Status-Re-schedule',
            'SZ' => 'Status-Skip',
        ];
        return $list;
    }
}

if(!function_exists('get_report_email_activity_list_text')){
    function get_report_email_activity_list_text(){
        $list = [
            'View',
            'Mail Send', 
            'Assign Agent', 
            'Disposition', 
            'Forward', 
            'Skill', 
            // 'Pull', 
            // 'Call Land', 
            'Skip',
            'Status-New',
            'Status-Pending',
            'Status-Pending(Client)',
            'Status-Served',
            'Status-Closed',
            'Status-Hold',
            'Status-Park',
            'Status-Re-schedule',
            'Status-Skip',
        ];
        return $list;
    }
}

if (!function_exists('isImageFile')) {
    function isImageFile($extension)
    {
        $extension = strtoupper($extension);
        if ($extension == 'PNG' || $extension == 'JPEG' || $extension == 'JPG' || $extension == 'GIF') {
            return true;
        }
        return false;
    }
}

if (!function_exists('ev_access_list')) {
    function ev_access_list()
    {
        $access_list = array(
           'DL' => 'Download',
           'PV' => 'Play Voice',
           'PS' => 'Play Screen Log',
           'FV' => 'Form View',
           'FC' => 'Form Ceate',
           'VE' => 'View Evaluate',
           'CE' => 'Ceate Evaluate',
           'CB' => 'Chat Bot',
           'WC' => 'Web Chat',
           'VC' => 'View Chat'
		);
		
        return $access_list;
    }
}

if (!function_exists('get_timestamp_to_hour_format')) {
    function get_timestamp_to_hour_format($timestamp) {
        if ($timestamp < 86400){
            return gmdate("H:i:s", $timestamp);
        } else {
            $div = floor($timestamp/3600);
            $rem = $timestamp%3600;
            return $div.":".gmdate("i:s",$rem);
        }
    }
}

if (!function_exists('fc_day_type')) {
    function fc_day_type()
    {
        $day_type_list = array(
            'H' => 'Holiday',
            'C' => 'Campaign',
            'S' => 'Special Day',
            'P' => 'Change Point'
        );

        return $day_type_list;
    }
}

if (!function_exists('fc_model_list')) {
    function fc_model_list()
    {
        $model_list = array(
            // 'ARIMA' => 'ARIMA',
            'GPLEX' => 'Gplex',
        );

        return $model_list;
    }
}

if (!function_exists('fc_generate_type_list')) {
    function fc_generate_type_list()
    {
        $generate_type_list = array(
            'D' => 'Daily',
            'H' => 'Hourly',
        );

        return $generate_type_list;
    }
}

if (!function_exists('fc_trend_list')) {
    function fc_trend_list()
    {
        $trend_list = array(
            'L' => 'Liner', 
            // 'E' => 'Exponential',
        );

        return $trend_list;
    }
}

if(!function_exists('fc_service_type_list')){
    function fc_service_type_list(){
        $service_type_list = [
            'V' => 'Voice',
            'I' => 'IVR',
            'E' => 'Email',
            'C' => 'WebChat',
        ];
        return $service_type_list;
    }
}

if (!function_exists('get_did_list')) {
    function get_did_list()
    {
        $did_list = [
            "786" => "786",
            "7866" => "7866",
            "21313" => "21313",
            "24753" => "24753"
        ];
        return $did_list;
    }
}

if (!function_exists('get_service_id_list')) {
    function get_service_id_list()
    {
        $get_service_id_list = array(
            'A' => 'Airtel',
            'V' => 'VMS',
            'T' => 'Test',
        );

        return $get_service_id_list;
    }
}

if (!function_exists('get_disposition_type')) {
    function get_disposition_type()
    {
        $did_type_list = [
            "Q" => "Query",
            "R" => "Request",
            "C" => "Complain "
        ];
        return $did_type_list;
    }
}

if (!function_exists('get_report_config_list')) {
    function get_report_config_list()
    {
        $report_config_list = [
            "1563780159" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1563778713" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1563778621" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1563778495" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1563777987" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1563776925" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1563707033" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1563707021" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1563706994" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1543902526" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1541331563" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1540734158" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1540211844" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1539866834" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1539858730" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1538388714" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1537337988" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1530773743" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1530772919" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            "1530771846" => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            '1563978741' => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            '1564295483' => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            '1563795287' => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            '1563813988' => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            '1563814034' => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            '1554379674' => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ],
            '1561532736' => [
                "get-report-new-data_report-summary" => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                "get-report-new-data_report-category-details" => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-skill-set-summary' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-dashboard-working' => [
                    "days" => 92,
                    "hide_col" => ['rgb_call_count', 'forecasted_call_count', 'fcr_call_percentage', 'forecasted_call_percentage', 'cpc']
                ],
                'get-report-new-data_report-msisdn' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-outbound-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_outbound-details-report' => [
                    "days" => 15,
                    "hide_col" => ['disc_cause_text']
                ],
                'get-report-new-data_report-category-details2' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary' => [
                    "days" => 3,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-session-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-skill-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-details-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-raw-data' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ice-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-outbound' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-email' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-web-chat' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-details' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_web-chat-summary-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-web-chat-day-wise' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_chat-ratings-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-chat-disposition' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_call-hang-up-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'report-new_daily-email-activity-report' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_email-day-wise-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-report' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-reasons-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-top-agents-dissatisfaction' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_workcode' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_workcode-count-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-summary' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_ivr-details' => [
                    "days" => 2,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-count' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'get-report-new-data_report-daily-ivr-hit-summary' => [
                    "days" => 92,
                    "hide_col" => []
                ],
                'report-new_day-wise-ivr-hit-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-details' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_report-diameter-bill-summary' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-ivr-global-group' => [
                    "days" => 15,
                    "hide_col" => []
                ],
                'get-report-new-data_report-qa-sms' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_pd-details-report' => [
                    "days" => 1,
                    "hide_col" => ['callid', 'customer_id']
                ],
                'get-report-new-data_report-pd-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-sms-details' => [
                    "days" => 1,
                    "hide_col" => ['session_id', 'last_update_time', 'callid']
                ],
                'get-report-new-data_report-sms-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-details' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_vivr-ice-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-obm-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_agent-performance-summary2' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_email-agent-performance-summary' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-agent-activity-summary' => [
                    "days" => 7,
                    "hide_col" => []
                ],
                'get-report-new-data_customer-journey-report' => [
                    "days" => 1,
                    "hide_col" => []
                ],
                'get-report-new-data_report-pd-qa' => [
                    "days" => 1,
                    "hide_col" => []
                ]
            ]
        ];
        return $report_config_list;
    }
}


if (!function_exists('get_skill_priority_auth')) {
    function get_skill_priority_auth()
    {
        return ['R', 'S'];
    }
}
if (!function_exists('get_disconnect_cause')) {
    function get_disconnect_cause()
    {
        return [
            '708' => 'Ring Timeout',
            '750' => 'Session Expired',
            '770' => 'Agent Logout - Call Control',
            '774' => 'Agent Logout - Soft Phone',
            '716' => 'RTP Timeout',
            '780' => 'Factory Reset',
            '784' => 'Soft Phone Close',
            '788' => 'Unregister',
            '712' => 'Invite Timeout',
            '700' => 'Agent Hangup',
            '200' => 'OK',
            '19' => 'No answer from user (user alerted)',
            '31' => 'Normal, unspecified',
            '20' => 'Subscriber absent',
            '1' => 'Unallocated (unassigned) number',
			'702'=> 'Agent Hangup Using Hotkey'
        ];
    }
}

