<?php

class Emailreport extends Controller
{
	function __construct() {
		parent::__construct();
		
		$licenseInfo = UserAuth::getLicenseSettings();
		if ($licenseInfo->email_module == 'N') {
			header("Location: ./index.php");
			exit;
		}
	}

	function init()
	{
	}
	
	function actionStatus()
	{
		include('model/MSkill.php');
		include('model/MEmail.php');
		$email_model = new MEmail();
		$skill_model = new MSkill();

		$data['pageTitle'] = 'Status Report';
		$data['side_menu_index'] = 'email';
		$data['sdate'] = isset($_REQUEST['sdate']) ? trim($_REQUEST['sdate']) : '';
		$data['edate'] = isset($_REQUEST['edate']) ? trim($_REQUEST['edate']) : '';
		$data['skill_options'] = $skill_model->getEmailSkillOptions(true);
		$data['st_options'] = $email_model->getTicketStatusOptions(true);
		$data['dataUrl'] = $this->url('task=get-email-data&act=emailreportstatus');
		$this->getTemplate()->display('email_report_status', $data);
	}
	
	function actionAgentactivity()
	{
		include('model/MAgent.php');
		$agent_model = new MAgent();

		$options = array('*' => 'Select');
		$data['agent_options'] = array_merge($options, $agent_model->getAgentNames('', '', 0, 0, 'array'));
		$data['pageTitle'] = 'Agent Email Activity Report';
		$data['side_menu_index'] = 'email';
		$data['dataUrl'] = $this->url('task=get-email-data&act=emailreportagentactivity');
		
		$this->getTemplate()->display('email_report_agent_activity', $data);
	}
	
	function actionDisposition()
	{
		include('model/MSkill.php');
		include('model/MEmail.php');
		$skill_model = new MSkill();
		$email_model = new MEmail();

		$options = array('*' => 'Select');
		$skillid = isset($_REQUEST['skillid']) ? trim($_REQUEST['skillid']) : "";
		$data['skill_options'] = $skill_model->getEmailSkillOptions(true);
		$data['dp_options'] = array_merge($options, $email_model->getDispositionTreeOptions($skillid));
		$data['pageTitle'] = 'Disposition Report';
		$data['side_menu_index'] = 'email';
		$data['dataUrl'] = $this->url('task=get-email-data&act=emailreportdisposition');
		
		$this->getTemplate()->display('email_report_disposition', $data);
	}

    function actionEmailActivityReport(){
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $data['skill_list'] = [];
        $data['agent_list'] = [];
        $data['status'] = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
        $data['agent_id'] = isset($_REQUEST['agent_id']) ? trim($_REQUEST['agent_id']) : '';
        if (UserAuth::hasRole('supervisor')){
            $data['agent_list'] = array("*"=>"All")+$eTicket_model->getEmailAgents(UserAuth::getCurrentUser());
            //$skill_list = $eTicket_model->getEmailSkill(UserAuth::getCurrentUser());
        } elseif(UserAuth::hasRole('admin')) {
            $data['agent_list'] = array("*"=>"All")+$eTicket_model->getEmailAgents();
            //$skill_list = $eTicket_model->getEmailSkill();
        }

        $data['isAgent'] = UserAuth::hasRole('agent');
        $data['status'] = "S";
        $data['status_options'] = array_merge ( array('*'=>'Select'), $eTicket_model->getTicketStatusOptions());
        $data['dataUrl'] = $this->url('task=get-email-data&act=email-activity-report');
        $data['side_menu_index'] = 'ticketmng';
        $data['pageTitle'] = 'Email Activity Report';
        $this->getTemplate()->display('email_activity_report', $data);
    }

    function actionEmailDayWiseReport(){
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $data['skill_list'] = [];
        $data['agent_list'] = [];
        $data['status'] = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';

        if (UserAuth::hasRole('supervisor')){
            $data['agent_list'] = array("*"=>"All")+$eTicket_model->getEmailAgents(UserAuth::getCurrentUser());
            $skill_list = $eTicket_model->getEmailSkill(UserAuth::getCurrentUser());
        } elseif(UserAuth::hasRole('admin')) {
            $data['agent_list'] = array("*"=>"All")+$eTicket_model->getEmailAgents();
            $skill_list = $eTicket_model->getEmailSkill();
        }

        //$data['isAgent'] = UserAuth::hasRole('agent');
        $data['status'] = "O";
        $data['status_options'] = array_merge ( array('*'=>'Select'), $eTicket_model->getTicketStatusOptions());
        $data['dataUrl'] = $this->url('task=get-email-data&act=email-day-wise-report');
        $data['side_menu_index'] = 'ticketmng';
        $data['pageTitle'] = 'Email Day Wise Report';
        $this->getTemplate()->display('email_day_wise_report', $data);
    }

    function actionEmailActivityDashboard(){
        include('model/MEmailReport.php');
        include('model/MEmail.php');
        $mainobj = new MEmailReport();
        $email_obj = new MEmail();
        $skill_names = [];
        $_SESSION['agent_skills'] = [];

        $request = $this->getRequest();
        $isAdmin = UserAuth::hasRole('admin');
        if (!$isAdmin){
            if (empty($_SESSION['agent_skills'])){
                $skills = $email_obj->getEmailSkill(UserAuth::getCurrentUser());
                if (!empty($skills)){
                    $temp = [];
                    foreach ($skills as $key){
                        $temp[] = $key->skill_id;
                    }
                    $_SESSION['agent_skills'] = $temp;
                }
            }
        }

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $time = new stdClass();
            $time->ststamp = strtotime(date("Y-m-d 00:00:00"));
            $time->etstamp  = strtotime(date("Y-m-d 23:59:59"));
            $time->isAdmin = UserAuth::hasRole('admin');
            $time->skill = $_SESSION['agent_skills'];
            $result = $this::getEmailActivityFormateData($mainobj->getEmailActivityDashboardData($time));
            echo json_encode($result);exit;
        }

        $skill_list = $email_obj->getEmailSkill();
        if (!empty($skill_list)){
            foreach ($skill_list as $key){
                $skill_names[$key->skill_id] = substr($key->skill_name, strpos($key->skill_name,'EB'), strlen($key->skill_name));
            }
        }

        $data['skill_names'] = $skill_names;
        $data['pageTitle'] = 'Email Activity Dashboard';
        $data['suffix'] = strtolower(UserAuth::getDBSuffix());
        $data['request'] = $request;
        $this->getTemplate()->display_only('email_activity_Dashboard', $data);
    }

    static function getEmailActivityFormateData($data){
	    $sl_response = [];
	    $aht_response = [];
	    $kpi_response = [];
	    $totalObj = new stdClass();
        $totalObj->total_closed = 0;
        $totalObj->total_in_kpi = 0;
        $totalObj->total_out_kpi = 0;

        if (!empty($data)) {
            foreach ($data as $key){
                $sl_response[$key->skill_id] =  !empty($key->total_closed) && !empty($key->total_in_kpi)? round(($key->total_in_kpi/$key->total_closed)*100,'2') . "%" : "0%";
                $aht_response[$key->skill_id] = !empty($key->total_closed) && !empty($key->total_open_duration) ? get_timestamp_to_hour_format($key->total_open_duration/$key->total_closed) : "00:00:00";

                $skill_wise_kpi_obj = new stdClass();
                $skill_wise_kpi_obj->total_closed = $key->total_closed;
                $skill_wise_kpi_obj->total_in_kpi = $key->total_in_kpi;
                $skill_wise_kpi_obj->total_out_kpi = $key->total_out_kpi;
                $kpi_response[$key->skill_id] = $skill_wise_kpi_obj;

                $totalObj->total_closed += $key->total_closed;
                $totalObj->total_in_kpi += $key->total_in_kpi;
                $totalObj->total_out_kpi += $key->total_out_kpi;
            }

            $obj = new stdClass();
            $obj->sl = $sl_response;
            $obj->aht = $aht_response;
            $obj->total = $totalObj;
            $obj->swkpi = $kpi_response;
            return $obj;
        }
        return false;
    }

}
