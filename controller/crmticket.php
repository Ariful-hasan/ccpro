<?php

class Crmticket extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $licenseInfo = UserAuth::getLicenseSettings();
        if ($licenseInfo->email_module == 'N') {
            header("Location: ./index.php");
            exit;
        }
    }

    function init()
    {

        include('model/MSkill.php');
        include('model/MCrmTicket.php');
        include('model/MSkillCrmTemplate.php');
        $skill_model = new MSkill();
        $eTicket_model = new MCrmTicket();
        $template_model = new MSkillCrmTemplate();

        $urlParam = "";
        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
        $allNew = isset($_REQUEST['newall']) ? trim($_REQUEST['newall']) : '';
        $etype = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';

        $isDateToday = true;
        $data['todaysDate'] = "";
        $data['pageTitle'] = 'CRM Tickets';
        $data['smi_selection'] = 'email_init';
        if ($etype == 'myjob') {
            $data['pageTitle'] = 'My Tickets';
            $data['smi_selection'] = 'email_init_myjob';
        }

        if (!empty($status)){
            $urlParam .= "&status=".$status;
            $isDateToday = false;
        }
        if (!empty($etype)){
            $urlParam .= "&type=".$etype;
            $isDateToday = false;
        }
        if (!empty($allNew)){
            $urlParam .= "&newall=".$allNew;
            $isDateToday = false;
        }
        if ($isDateToday){
            $data['todaysDate'] = date("Y-m-d");
        }

        $disposition_list = $template_model->getDispositions();
        $dispositions["*"] = 'All';
        foreach ($disposition_list as $disposition)
        {
            $dispositions[$disposition->disposition_id] = $disposition->title;
        }

        $data['status'] = $status;
        $data['sdate'] = isset($_REQUEST['sdate']) ? trim($_REQUEST['sdate']) : '';
        $data['edate'] = isset($_REQUEST['edate']) ? trim($_REQUEST['edate']) : '';
        $selectOpt = array('*'=>'All');
        $data['skills'] = array_merge ( $selectOpt, $skill_model->getSkillsNamesArray());
        $data['statusOptions'] = array_merge ( $selectOpt, $eTicket_model->getETicketInfoStatusOptions());
        $data['dispositions'] = $dispositions;
        $data['dataUrl'] = $this->url('task=get-crmticket-data&act=crmticket-init'.$urlParam);

        $data['side_menu_index'] = 'crm_ticket';
        $this->getTemplate()->display('crmticket', $data);
    }

    public function actionDashboard()
    {
        include('model/MCrmTicket.php');
        $crm_ticket_model = new MCrmTicket();
        $data['recentjob'] = null;
        $data['alljob'] = null;

        $data['myjob'] = $crm_ticket_model->getMyJobSummary(UserAuth::getCurrentUser());
        if (UserAuth::hasRole('admin') || UserAuth::hasRole('supervisor')) {
            $data['recentjob'] = $crm_ticket_model->getRecentJobSummary(UserAuth::getCurrentUser(), UserAuth::hasRole('admin'));
            $data['alljob'] = $crm_ticket_model->getJobSummary(UserAuth::getCurrentUser(), UserAuth::hasRole('admin'));
        }

        $data['side_menu_index'] = 'crm_ticket';
        $data['smi_selection'] = 'crmticket_';
        $data['pageTitle'] = 'CRM Ticket Dashboard';
        $this->getTemplate()->display('crm_ticket_dashboard', $data);
    }


}
