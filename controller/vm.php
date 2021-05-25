<?php

class Vm extends Controller
{
	function __construct() {		
		parent::__construct();
	}

	function init()
	{
		$st = isset($_REQUEST['st']) ? $_REQUEST['st'] : "";
		if ($st!='S') {
			$st = 'N';
		}
		$data['pageTitle'] = 'Voice Mail';
		$data['dataUrl'] = $this->url('task=get-agent-data&act=vmlog&status='.$st);
		$data['topMenuItems'] = array(
			array('href'=>'task=vm&st=N', 'img'=>'fa fa-list-alt', 'label'=>'New Voioce Mail'),
			array('href'=>'task=vm&st=S', 'img'=>'fa fa-list-alt', 'label'=>'Served Voice Mail'),
			array('href'=>'task=vm&act=my-vm', 'img'=>'fa fa-user', 'label'=>'Personal Voice Mail', 'color'=>'danger')
		);
		
		$data['smi_selection'] = 'vm_';
		//$data['side_menu_index'] = 'vm';
		$this->getTemplate()->display('voice_mails', $data);
	}
	
	function actionMyVm()
	{
		$st = isset($_REQUEST['st']) ? $_REQUEST['st'] : "";
		$data['pageTitle'] = 'Personal Voice Mail';
		$data['dataUrl'] = $this->url('task=get-agent-data&act=my-vmlog&status='.$st);
		$data['smi_selection'] = 'vm_';
		//$data['side_menu_index'] = 'vm';
		$this->getTemplate()->display('voice_mails_my', $data);
	}
	
	function actionVoice()
	{
		include('model/MAgentReport.php');
		$report_model = new MAgentReport();
		$agentid = UserAuth::getCurrentUser();

		$cid = isset($_REQUEST['cid']) ? $_REQUEST['cid'] : "";
		$ts = isset($_REQUEST['ts']) ? $_REQUEST['ts'] : "";
		$op = isset($_REQUEST['op']) ? $_REQUEST['op'] : "";
		
		$isSkill = $op == 'A' ? false : true;
		
		$vm = null;
		if (!empty($cid) && !empty($ts))
		{
			$vm = $report_model->getVoiceLogByCallID($cid, $ts, $isSkill);
		}
		
		$report_model->listenVMFile($cid, $ts, $vm, $agentid, $this->getTemplate()->voice_logger_path, $isSkill);

		/*		
		//if ($report_model->updateVoiceLogStatus($cid, $ts, 'R', $agentid)) {
		if (!empty($vm)) {
			//exit('1234');
			//echo $vm->status . '<';exit;
			if ($vm->status != 'S') {
				$report_model->updateVoiceLogStatus($cid, $ts, 'R', $agentid);
			}
			$report_model->addToAuditLog('Voice Mail', "V", "", "Listen voice file");
			$yyyy = date("Y", $ts);
			$yyyy_mm_dd = date("Y_m_d", $ts);
			$sound_file = $this->getTemplate()->voice_logger_path . "VM/$yyyy/$yyyy_mm_dd/" . $cid . ".wav";
			if (!file_exists($sound_file)) {
				$sound_file = $this->getTemplate()->voice_logger_path . "VM/$yyyy/$yyyy_mm_dd/" . $cid . ".mp3";
				if (!file_exists($sound_file)) {
					$sound_file = '';
				}
			}
			//echo $sound_file;exit;
			if (!empty($sound_file)) {
				$fp = fopen($sound_file, "rb");
				header("Content-type: application/octet-stream");
				header('Content-disposition: attachment; filename="vm-voice-'.$ts.'.mp3"');
				header("Content-transfer-encoding: binary");
				header("Content-length: ".filesize($sound_file)."    ");
				fpassthru($fp);
				fclose($fp);
			}
		}
		*/
		
		exit;
	}
	
}
