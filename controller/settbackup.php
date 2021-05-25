<?php

class Settbackup extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		$errMsg = '';
		$errType = 1;
		$deviceid = isset($_REQUEST['deviceid']) ? trim($_REQUEST['deviceid']) : '';
		$pin = isset($_REQUEST['pin']) ? trim($_REQUEST['pin']) : '';
		
		$page_part = 1;
		 
		if (isset($_POST['pin'])) {
			$errMsg = $this->getValidationMsg($deviceid, $pin);
			if (empty($errMsg)) {
				if (file_exists("./dashvar/backup.txt")) unlink("./dashvar/backup.txt");
				$this->writeInfo($deviceid, $pin, 'INFO');
				$page_part = 2;
			}
		}

		$data['request'] = $this->getRequest();
		$data['deviceid'] = $deviceid;
		$data['pin'] = $pin;
		$data['page_part'] = $page_part;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = 'Backup Data';
		$data['side_menu_index'] = 'settings';
		$data['topMenuItems'] = array(array('href'=>'task=settbackup&act=log', 'img'=>'page_white_database.png', 'label'=>'Backup Log'));
		$this->getTemplate()->display('settings_backup_form', $data);

	}

	function actionLog()
	{
		include('model/MSetting.php');
		include('lib/Pagination.php');
		$setting_model = new MSetting();
		$pagination = new Pagination();
		$pagination->rows_per_page = 20;
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=log");
		$pagination->num_records = $setting_model->numBackups();
		$data['logs'] = $pagination->num_records > 0 ? 
			$setting_model->getBackups($pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['logs']) ? count($data['logs']) : 0;
		$data['pagination'] = $pagination;
		$data['pageTitle'] = 'Backup Log';
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'settbackup_';
		$this->getTemplate()->display('settings_backup', $data);
	}
	
	function actionStatus()
	{
		//$txt = '';
		//echo '{"status":"RUNNING","txt":"Test Line1<br>Test Line2<br>","percent":"20"}';exit;
		$fp = @fopen("./dashvar/backup.txt", "r");
		if ($fp)  {
			$text = fread($fp, 1024);

			$values = explode("\n", $text);
			if (is_array($values)) {
				$st = isset($values[0]) ? trim($values[0]) : 'ERROR';
				$ln_count = count($values);
				//$txt = isset($values[1]) ? trim($values[1]) . '<br />' : '';
				//$txt .= isset($values[2]) ? trim($values[2]) : '';
				$txt = '';
				for ($i=1; $i<$ln_count; $i++) {
					$tx = isset($values[$i]) ? trim($values[$i]) : '';
					if (!empty($tx) && !is_numeric($tx)) {
						$txt .= $tx . '<br />';
					}
				}
				if ($st == 'START' || $st == 'CANCEL' || $st == 'END') {
					//$percent = isset($values[$ln_count-1]) ? trim($values[$ln_count-1]) : 0;
					$percent = end($values);
					echo '{"status":"' . $st . '","txt":"' . $txt . '","percent":"' . $percent . '"}';
				} else {
					$txt .= isset($values[$i]) ? trim($values[$i]) : '';
					echo '{"status":"' . $st . '","txt":"' . $txt . '"}';
				}
			}
			fclose($fp);
		}
		
		//echo '{"status":"RUNNING","txt":"Test Line1<br>Test Line2<br>","percent":"20"}';
	}
	
	function writeInfo($deviceid, $pin, $info)
	{
		$fp = @fopen("./dashvar/backup.id", "w");
		if ($fp)  {
			fwrite($fp, "$deviceid|$pin|$info");
			fclose($fp);
		}
	}
	
	function actionUpdate()
	{
		$deviceid = isset($_REQUEST['deviceid']) ? trim($_REQUEST['deviceid']) : '';
		$pin = isset($_REQUEST['pin']) ? trim($_REQUEST['pin']) : '';
		$info = isset($_REQUEST['st']) ? trim($_REQUEST['st']) : '';
		if ($info == 'START' || $info == 'CANCEL') {
			$this->writeInfo($deviceid, $pin, $info);
		}
		echo '{"status":"DONE"}';
	}

	function getValidationMsg($deviceid, $pin)
	{
		if (empty($deviceid)) return 'Provide device ID';
		if (empty($pin)) return 'Provide PIN';
		if (strlen($deviceid) != 6 || !ctype_digit($deviceid)) return 'Invalid device ID';
		if (strlen($pin) != 4 || !ctype_digit($pin)) return 'Invalid PIN';
		return '';
	}

}
