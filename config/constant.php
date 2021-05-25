<?php
//return value 
define('MSG_RESULT', 'result');
define('MSG_MSG', 'message');
define('MSG_DATA', 'data');
define('MSG_TYPE', 'type');
define('MSG_SUCCESS', 'success');
define('MSG_ERROR', 'errors');
define('MSG_WARNING', 'warning');
define('MSG_ERROR_DATA', 'error_data');
define('MSG_FIELDS', 'fields');
define('MSG_VALUES', 'values');
define('MSG_COND_PARAM', 'cond_params');
define('MSG_LOG_TEXT', 'log_txt');
define('MSG_YES', 'Y');
define('MSG_NO', 'N');
define('REPORT_DATA_PER_PAGE', 20);

//status
define('STATUS_ACTIVE', 'A');
define('STATUS_INACTIVE', 'I');
define('STATUS_NEW', 'N');
define('STATUS_READ', 'R');
define('STATUS_COMPLETE', 'C');
define('STATUS_WAITING', 'W');
define('STATUS_SERVED', 'S');
define('STATUS_EXIT', 'E');

//otp/otc
define('OTP', 'OTP');
define('OTC', 'OTC');

//otp/otc module type
define('OM_CHAT', 'CH');

//otp/otc sent type
define('OS_EMAIL', 'EM');
define('OS_MOBILE', 'MO');

// random string
define('STR_NUMBER', '0123456789');

//settings or common module name
define('MOD_EMAIL', 'MDE');
define('MOD_CHAT', 'MCH');

//email inbox type
define('EMAIL_POP3', 'EP3');
define('EMAIL_IMAP', 'EIM');
define('EMAIL_EWS', 'EWS');

//page layout
define('PAGE_DEFAULT', 'PDE');
define('PAGE_REPORT', 'PRE');

//User Access CRUD
define('ACCESS_READ', 'ARD');
define('ACCESS_ADD', 'AAD');
define('ACCESS_UPDATE', 'AUP');
define('ACCESS_DELETE', 'ADE');
define('ACCESS_STATUS', 'AST');
define('ACCESS_PASSWORD_RESET', 'APA');
define('ACCESS_PIN_RESET', 'API');

/** 
 * User Access Group configuration 
 */
define('MOD_AGENTS', 'MAG');
define('MOD_CTI', 'MCT');
define('MOD_IVR', 'MIR');
define('MOD_SKILL', 'MSK');
define('MOD_CHAT_DISP', 'MCD');
define('MOD_EMAIL_DISP', 'MED');
define('MOD_CAMPAIGN', 'MCP');
define('MOD_LEAD', 'MLE');
define('MOD_DISPOSITION', 'MDP');
define('MOD_DASHBOARD', 'MDH');
define('MOD_SETTINGS', 'MST');
define('MOD_LANGUAGE', 'MLG');
define('MOD_EXTERNAL_USER', 'MEU');
define('MOD_REPORT_PARTITIONS', 'MRP');
define('MOD_CALL_QUALITY_PROFILE', 'MCQ');
define('MOD_AGENT_SHIFT_PROFILE', 'MAS');
define('MOD_AGENT_HOTKEY', 'MAH');
define('MOD_IVR_DISPOSITION', 'MID');
define('MOD_IVR_API_CONNECTIONS', 'MIP');
define('MOD_SEAT', 'MSE');
define('MOD_SERVICE_LEVEL_CALCULATION', 'MSC');
define('MOD_PASSWORD', 'MPA');
define('MOD_APP_CONFIGURATION', 'MAC');
define('MOD_MAP_ADDRESS', 'MMA');
define('MOD_TRUNK', 'MTU');
define('MOD_CC_BCC_EMAILS', 'MCB');
define('MOD_SMS_API', 'MSA');
define('MOD_CRM', 'MCM');
define('MOD_CRM_INBOUND', 'MCI');
define('MOD_VOICE_MAIL', 'MVM');
define('MOD_TICKET', 'MTK');
define('MOD_TICKET_DASHBOARD', 'MTD');
define('MOD_TICKET_CATEGORY', 'MTC');
define('MOD_TICKET_REPORT', 'MTR');
define('MOD_EMAIL_MANAGEMENT', 'MEM');
define('MOD_EMAIL_TEMPLATE', 'MET');
define('MOD_CHAT_PAGE', 'MCG');
define('MOD_CHAT_TEMPLATE', 'MCE');
define('MOD_CHAT_REPORT', 'MCR');
define('MOD_BUSY_MESSAGE', 'MBM');
define('MOD_CALL_CONTROL', 'MCC');
define('MOD_AGENT_PROFILE', 'MAP');
define('MOD_SKILL_CRM_TEMPLATE', 'MSL');
define('MOD_EMAIL_FETCH_INBOX', 'MEI');

// Date format
define('REPORT_DATE_FORMAT', 'd/m/Y');
define('REPORT_DATETIME_FORMAT', 'd/m/Y H:i:s');
define('REPORT_DATE_FORMAT2', 'm/d/Y');

define('REPORT_YEAR_MONTH_FORMAT', 'F,Y');
define('REPORT_YEAR_FORMAT', 'Y');
define('DB_DATE_FORMAT', 'Y-m-d');
define('DB_DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DB_DATE_HHMM_FORMAT', 'Y-m-d H:i');
define('DB_DATE_HHMM_START_FORMAT', 'Y-m-d 00:00');
define('DB_DATE_HHMM_END_FORMAT', 'Y-m-d 23:59');

//report type list
define('REPORT_15_MIN_INV', 'R15');
define('REPORT_HALF_HOURLY', 'RHH');
define('REPORT_HOURLY', 'RHR');
define('REPORT_DAILY', 'RDA');
define('REPORT_MONTHLY', 'RMN');
define('REPORT_QUARTERLY', 'RQR');
define('REPORT_YEARLY', 'RYR');

//wrap-up time
define('DELAY_BETWEEN_CALLS', 8);
define('DOWNLOAD_PER_PAGE', 10000);
define('TOTAL_DOWNLOAD', 200000);

// 777 and 786 category skills
const CATEGORY_777_SKILLS = ['AG', 'AQ', 'AU'];
const CATEGORY_786_SKILLS = ['AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AH', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AR', 'AS'];
const CATEGORY_8382_SKILLS = ['AR'];
const CATEGORY_121_SKILLS = ['AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AV'];
const CATEGORY_01819400400_SKILLS = ['AS', 'AT', 'AU'];

// For Email
define('IN_KPI_TIME', 1800);

// Agent session interval in second
define('AGENT_SESSION_INTERVAL', 14400); //14400 = 4 hour
const MARK_AS_CLOSED = '0000';

// default report day
define('SUMMARY_REPORT_DAY', 90);
define('EMAIL_SUMMARY_REPORT_DAY', 1);
define('DETAILS_REPORT_DAY', 15);
define('REPORT_MULTIPLE_MENU_OFF', 'Y');
define('REPORT_15_30_OFF', 'Y');
define('REPORT_WORKECODE_DAY', 7);
define('DAY_REPORT_FORMULA', 'N');
define('WEB_CHAT_SL_TIME', 20);
define('WEB_CHAT_SL_PERCENTAGE', 100);
define('SMS_SL_TIME', 600);
define('WEBCHAT_AGENT_RESPONSE', '2020-01-01');
// show or hide service level calculation formula in report
const SHOW_SL_FORMULA_IN_REPORT = false;

// SMS Chat related constants
const ICE_FEEDBACK_NUMBER = 28888;
const ICE_FEEDBACK_MSG = "প্রিয় গ্রাহক, কল সেন্টার থেকে পাওয়া এজেন্ট এর সেবাটি ভালো লাগলে Y, আর ভালো না লাগলে N লিখে ফ্রি রিপ্লাই করুন 28888 এ| আপনার মতামতের জন্য ধন্যবাদ, রবি";


/*
 * Customer Journey Modules
 */
const  customer_journey_module = ["IV"=>"IVR", "VI"=>"Visual IVR", "AC"=>"Agent Call", "PD"=>"Agent OB Call", "CT"=>"Chat", "EM"=>"Email"];


/*
 * Temporary for webchat (By Sanaul Bhai)
 * done by Arif
 */
const webchat_temporary_callid = array();
const webchat_temporary_date = array();
const webchat_temporary_date_value = array();

define('INBOUND_EVALUATION_TYPE', 'I');
define('OUTBOUND_EVALUATION_TYPE', 'O');
define('WEBCHAT_EVALUATION_TYPE', 'WC');
define('EMAIL_EVALUATION_TYPE', 'E');
define('SMS_EVALUATION_TYPE', 'S');
define('PD_EVALUATION_TYPE', 'P');

define('UNATTENDED_CDR_TIME_RANGE', '+30 minutes');
define('UNATTENDED_CDR_DISPOSITION_TEMPLATE', 'AAA');


/*
 * Email first open-time from first pull time.
 */
const FIRST_OPEN_TIME_SET_FROM_FIRST_PULL = true;
const TEST_CONST = "This is test constant!!";


/*
 * lead upload field labels
 */
const PD_FIELD_LABELS = [
    'agent_id'=>'Agent Id', 'title'=>'Title', 'customer_id'=>'Customer Id', 'first_name'=>'First Name',
    'last_name'=>'Last Name', 'street'=>'Street', 'city'=>'City', 'state'=>'State', 'zip'=>'Zip', 'language'=>'Language',
    'number_1'=>'Contact Number', 'number_2'=>'Secondary Contact 1', 'number_3'=>'Secondary Contact 2',
    'custom_value_1'=>'Custom Value 1', 'custom_value_2'=>'Custom Value 2', 'custom_value_3'=>'Custom Value 3',
    'custom_value_4'=>'Custom Value 4', 'custom_value_5'=>'Custom Value 5', 'custom_value_6'=>'Custom Value 6',
    'agent_altid'=>'Agent Alt. Id', 'disposition'=>'Disposition'
];

/*
 * Unattended cdr and Call back
 */
const UNATTENDED_CALLBACK_TYPE = ['U'=>'Unattended CDR', 'C'=>'Call Back'];