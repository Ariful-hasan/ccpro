<?php

class TemplateManager
{
	var $_request_task;
	var $_request_action;
	var $file_upload_path;
	var $file_music_path;
	var $voice_logger_path;
	var $screen_logger_path;
	var $sip_logger_path;
	var $is_voice_file_downloadable;
	var $is_view_served;
	var $is_view_disabled;
	var $is_box_wrapper_enabled;
	var $email_module;
	var $chat_module;
	var $vm_module;
	var $abandoned_sms;
	var $agent_id_in_dashboard;
	var $supported_extn=array('g729','ulaw');
	var $store_file_extension='g729';

	var $maxFileSize = 5000000;
	var $voice_file_supported_extn = array('wav','alaw','ulaw');
	var $voice_store_file_extension = 'wav';

	// tts config
	var $ttsUsername = "";
	var $ttsPassword = "";
	var $ttsUrl = "";
	var $bkp_audio_url = "";
	var $upload_email_body_image = "";
	var $read_email_body_image = "";
	var $delete_email_body_image = "";

	//appTitle = "gPlex&#174 CallCenter"

	private static $errorMessage=array();
	private static $infoMessage=array();
	private static $hiddenFilelds=array();
	public static function AddError($msg){
		self::$errorMessage[]=$msg;
	}
	public static function AddInfo($msg){
		self::$infoMessage[]=$msg;
	}
	public static function GetError($prefix='',$postfix=''){
		if(count(self::$errorMessage)>0){
			return $prefix.implode($postfix, self::$errorMessage).$postfix;
		}
		return '';
	}
	public static function GetInfo($prefix='',$postfix=''){
		if(count(self::$infoMessage)>0){
			return $prefix.implode($postfix, self::$infoMessage).$postfix;
		}
		return '';
	}
	public static function GetMsg($prefix1='',$prefix2='',$postfix=''){
		$str=self::GetError($prefix2,$postfix);
		$str.=self::GetInfo($prefix1,$postfix);
		return $str;
	}
	public static function HasUIMsg(){
		return count(self::$infoMessage)>0 ||count(self::$errorMessage)>0;
	}
	public static function AddHiddenFields($key,$value){
		self::$hiddenFilelds[$key]=$value;
	}
	public static function AddOldFields($key,$value){
		self::AddHiddenFields("old_".$key, $value);		
	}
	public static function GetHiddenFields(){
		ob_start();
		foreach (self::$hiddenFilelds as $name=>$value){
		?>
		<input type="hidden" name="<?php echo $name;?>" value="<?php echo $value;?>" />
		<?php 
		}
		return ob_get_clean();		
	}
	
	function __construct($task='', $action='', $confs=null)
	{
		$this->_request_task = $task;
		$this->_request_action = $action;
		$account_id=UserAuth::getAccount_id();
		$account_id .= !empty($account_id)?"/":"";
		$dbprefix = UserAuth::getDBSuffix();
		$dbprefix .= !empty($dbprefix)?"/":"";
		
		$this->file_upload_path = $confs->file_upload_path.$dbprefix;
		$this->file_music_path = $confs->file_music_path.$dbprefix;
		$this->voice_logger_path = $confs->voice_logger_path.$dbprefix;
		$this->screen_logger_path = $confs->screen_logger_path.$dbprefix;
		$this->bkp_audio_url = $confs->bkp_audio_url;
		$this->upload_email_body_image = $confs->upload_email_body_image;
		$this->read_email_body_image = $confs->read_email_body_image;
		$this->delete_email_body_image = $confs->delete_email_body_image;
		// $this->fc_input_file_path = $confs->fc_input_file_path;
		// $this->fc_json_file_path = $confs->fc_json_file_path;
		// $this->fc_output_file_path = $confs->fc_output_file_path;
		// $this->fc_python_home_path = $confs->fc_python_home_path;
		// $this->fc_python_file_path = $confs->fc_python_file_path;

		// tts config
		$this->ttsUsername = $confs->ttsUsername;
		$this->ttsPassword = $confs->ttsPassword;
		$this->ttsUrl = $confs->ttsUrl;
		
		$this->backup_hdd_path = isset($confs->backup_hdd_path) ? $confs->backup_hdd_path . $dbprefix : "";
		$this->backup_transfer_aft_month = isset($confs->backup_transfer_aft_month) ? $confs->backup_transfer_aft_month : 0;
		
		$this->sip_logger_path = $confs->sip_logger_path.$dbprefix;		
		$this->is_voice_file_downloadable = $confs->is_voice_file_downloadable;
		$this->ss7_link_status = $confs->ss7_link_status;		
		$this->is_view_disabled = false;
		$this->is_view_served = false;
		$this->is_box_wrapper_enabled = true;
		$this->agent_id_in_dashboard = isset($confs->agent_id_in_dashboard) && $confs->agent_id_in_dashboard ? true : false;
		
		$licenseInfo = UserAuth::getLicenseSettings();
		$this->email_module = isset($licenseInfo->email_module) && $licenseInfo->email_module == 'Y' ? true : false;
		$this->chat_module = isset($licenseInfo->chat_module) && $licenseInfo->chat_module == 'Y' ? true : false;
		$this->vm_module = isset($licenseInfo->vm_module) && $licenseInfo->vm_module == 'Y' ? true : false;
		$this->abandoned_sms = isset($confs->abandoned_sms) && $confs->abandoned_sms ? true : false;
	}
	
	function IsIE(){
		if(!empty($_SERVER['HTTP_USER_AGENT'])){
			if(preg_match('/(?i)msie [1-8]|(?i)Trident/',$_SERVER['HTTP_USER_AGENT'])){
				return true;
			}
		}
		return false;
	}
	function disableBoxWrapper()
	{
		$this->is_box_wrapper_enabled = false;
	}
	
	function isBoxWrapperEnabled()
	{
		return $this->is_box_wrapper_enabled;
	}
	
	function getControllerName()
	{
		return $this->_request_task;
	}

	function getActionName()
	{
		return $this->_request_action;
	}
	/**
	 * added by Sarwar
	 * @return string
	 */
	function getCurrentUrl()
	{
		$extraparam=(count($_GET)>0?('&'.http_build_query($_GET)):"");
		return $this->url("task=".$this->_request_task."&act=".$this->_request_action.$extraparam);
	}
	
	
	function isViewServed()
	{
		return $this->is_view_served;
	}
	
	function isViewDisabled()
	{
		return $this->is_view_disabled;
	}
	
	function disableView()
	{
		$this->is_view_disabled = true;
	}
	
	function setViewType()
	{
		/*
		 * *
		 */
	}
	
	function sendDefaultResponse()
	{
		
	}
	
	function redirect($url)
	{
		header("Location: $url");
		exit;
	}
	
	function getBasePath()
	{
		return rtrim($_SERVER['SCRIPT_NAME'], 'index.php');
	}
	
	function url($path='')
	{ 	
		if(empty($path)){
			return $this->getBasePath();
		}else{
			return $this->getBasePath() . 'index.php?' . $path;
		}
		
	}
	
	function html_options($name, $id='', $options=null, $selected='', $onchange='', $disabled=false,$class="")
	{
		echo '<select class="'.$class.'" name="' . $name . '"';
		if (!empty($id)) echo ' id="' . $id . '"';
		if (!empty($onchange)) echo ' onchange="' . $onchange . '"';
		if ($disabled) echo ' disabled="disabled"';
		echo '>';
		if (is_array($options)) {
			foreach ($options as $key=>$val) {
				echo '<option value="'.$key.'"';
				if ($key == $selected) echo ' selected';
				echo '>'.$val.'</option>';
				//echo 'key-value=' . $key . ',' . $selected . ',';
			}
		}
		echo '</select>';
	}
	
	function display($page, $vars = array())
	{
		$this->is_view_served = true;
		
		if (isset($_REQUEST['download'])) $this->display_only($page, $vars);
		
		foreach($vars as $key => $val) {
			${$key} = $val;
		}
		
		include("view/header.php");
		include("view/$page.php");
		include("view/footer.php");
		exit;
	}

	function display_report($page, $vars = [])
	{
		$this->is_view_served = true;

		if (isset($_REQUEST['download'])) $this->display_only($page, $vars);

		extract($vars);

//		include("view/header_report.php");

		include("view/header_report_new.php");
		include("view/$page.php");
		include("view/footer_report.php");
		exit;
	}
	
	function display_cbody($page, $vars = array())
	{
	    $this->is_view_served = true;
	
	    if (isset($_REQUEST['download'])) $this->display_only($page, $vars);
	
	    foreach($vars as $key => $val) {
	        ${$key} = $val;
	    }
	
	    include("view/header_no_body.php");
	    include("view/$page.php");
	    include("view/footer_no_body.php");
	    exit;
	}

	function display_popup($page, $vars = array(),$isFullLoad=false,$isAutoResize=true)
	{
		if($isFullLoad){
				$vars['fullload']=true;
		}
		$vars['isAutoResize']=$isAutoResize;
		$this->is_view_served = true;
		
		foreach($vars as $key => $val) {
			${$key} = $val;
		}
		
		include("view/header_popup.php");
		include("view/$page.php");
		include("view/footer_popup.php");
		exit;
	}

	function display_crm($page, $vars = [])
	{
	    $this->is_view_served = true;

		extract($vars);

		include("view/header_crm.php");
		include("view/$page.php");
		include("view/footer_crm.php");
		exit;
	}
	function display_popup_chat($page, $vars = array(),$isFullLoad=false,$isAutoResize=true)
	{
	    if($isFullLoad){
	        $vars['fullload']=true;
	    }
	    $vars['isAutoResize']=$isAutoResize;
	    $this->is_view_served = true;
	
	    foreach($vars as $key => $val) {
	        ${$key} = $val;
	    }
	
	    include("view/header_popup_2.php");
	    include("view/$page.php");
	    include("view/footer_popup.php");
	    exit;
	}
	function display_popup_tab($page, $vars = array())
	{
	    $this->is_view_served = true;
	
	    foreach($vars as $key => $val) {
	        ${$key} = $val;
	    }
	
	    include("view/header_popup_tab.php");
	    include("view/$page.php");
	    include("view/footer_popup_tab.php");
	    exit;
	}
	function display_page_error($page, $vars = array())
	{
		
		$vars['fullload']=true;		
		$this->is_view_served = true;	
		foreach($vars as $key => $val) {
			${$key} = $val;
		}	
		include("view/header_account_not_found.php");		
		include("view/$page.php");	
		include("view/footer.php");
		exit;
	}
	function display_404_error($vars = array())
	{
	
		$vars['fullload']=true;
		$this->is_view_served = true;
		foreach($vars as $key => $val) {
			${$key} = $val;
		}
		//include("view/header_account_not_found.php");
		include("view/404.php");
		//include("view/footer.php");
		exit;
	}
	function display_popup_msg($vars = array(),$isFullLoad=false)
	{
		if($isFullLoad){
			$vars['fullload']=true;
		}
		$this->is_view_served = true;
	
		foreach($vars as $key => $val) {
			${$key} = $val;
		}	
		include("view/header_popup.php");		
		include("view/footer_popup.php");
		exit;
	}
	
	function display_only($page, $vars = array())
	{
		$this->is_view_served = true;
		foreach($vars as $key => $val) {
			${$key} = $val;
		}
		include("view/$page.php");
		exit;
	}

	public static function getFileNameFromTxtFile($txtFileName, $txtFileDir){
        $txtFileName = $txtFileDir.$txtFileName.".txt";
        $mediaFileName = "";
        if(file_exists($txtFileName)){
            $myfile = fopen($txtFileName, "r");
            $mediaFileName = fread($myfile,filesize($txtFileName));
            fclose($myfile);
        }    
        return $mediaFileName;
	}
	public static function isFileExists($fileName, $fileDir){
		$filePath = $fileDir.$fileName;
        if(file_exists($filePath)){
			return true;
        }    
        return false;
	}

    function display_email_popup($page, $vars = array(),$isFullLoad=false,$isAutoResize=true)
    {
        if($isFullLoad){
            $vars['fullload']=true;
        }
        $vars['isAutoResize']=$isAutoResize;
        $this->is_view_served = true;

        foreach($vars as $key => $val) {
            ${$key} = $val;
        }

        include("view/header_email_popup.php");
        include("view/$page.php");
        include("view/footer_popup.php");
        exit;
    }

	
}

?>