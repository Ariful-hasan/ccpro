<?php

class callQuality extends Controller
{
	function __construct()
    {
		parent::__construct();
	}

	function init()
	{
		$this->actionProfiles();
	}
	
	function actionProfiles()
	{
		$data['pageTitle'] = 'Rating Profile';
		$data['topMenuItems'] = array(array('href'=>'task=call-quality&act=add-profile', 'img'=>'fa fa-plus', 'label'=>'Add New Profile', 'title'=>'Add new call quality profile','class'=>'lightboxWIF'));
		$data['dataUrl'] = $this->url('task=get-home-data&act=rating-profile');
        $data['side_menu_index'] = 'settings';
        $data['smi_selection'] = 'call_quality';
		$this->getTemplate()->display('call_quality_profile', $data);
	}

	function ActionAddProfile()
    {
        include('model/MAgent.php');
        $agent_model = new MAgent();


        $request = $this->getRequest();
        $id = $request->getRequest('id');
        $initial_profile = $this->getInitialProfile($id,$agent_model);
        if ($request->isPost())
        {
            $label = trim($request->getRequest('label'));
            $status = $request->getRequest('status');

            $msg = $this->validateCallQualityProfile($label,$status);
            if (empty($msg))
            {
                if ($agent_model->addCallQualityProfile($label,$status)){
                    AddInfo("Call Quality Rating Profile Added Successfully");
                    $this->getTemplate()->display_popup_msg(array(),TRUE);
                }else{
                    AddError("Failed to Add Call Quality Rating Profile.");
                }
            }
            else{
                AddError($msg);
            }

            $initial_profile->label = $label;
            $initial_profile->status = $status;

        }
        $data['request'] = $request;
        $data['mainobj'] = $initial_profile;
        $data['pageTitle'] = 'Create Call Quality Profile';
        $this->getTemplate()->display_popup('call_quality_profile_create', $data,TRUE);
    }

    function ActionUpdateProfile()
    {
        include('model/MAgent.php');
        $agent_model = new MAgent();


        $request = $this->getRequest();
        $id = $request->getRequest('id');
        $initial_profile = $this->getInitialProfile($id,$agent_model);
        if ($request->isPost())
        {
            $label = trim($request->getRequest('label'));
            $status = $request->getRequest('status');

            $msg = $this->validateCallQualityProfile($label,$status);
            if (empty($msg))
            {
                if ($agent_model->updateCallQualityProfile($id,$label,$status)){
                    AddInfo("Call Quality Rating Profile Updated Successfully");
                    $this->getTemplate()->display_popup_msg(array("isAutoResize"=>"true"),TRUE);
                }else{
                    AddError("Failed to Update Call Quality Rating Profile.");
                }
            }
            else{
                AddError($msg);
            }

            $initial_profile->label = $label;
            $initial_profile->status = $status;

        }
        $data['request'] = $request;
        $data['mainobj'] = $initial_profile;
        $data['pageTitle'] = 'Create Call Quality Profile';
        $this->getTemplate()->display_popup('call_quality_profile_create', $data,TRUE);
    }

    public function getInitialProfile($id='',$agent_model)
    {

        if (empty($id))
        {
            $initial_profile = new stdClass();
            $initial_profile->rating_id=null;
            $initial_profile->status="N";
            $initial_profile->label="Call Quality Profile Title";

            return $initial_profile;
        }

        $initial_profile = $agent_model->getCallQualityProfileById($id);
        return $initial_profile[0];
    }

    private function validateCallQualityProfile($label,$status)
    {
        if (empty($label)) return "Profile label is required.";
        if (strlen($label) > 50) return "Maximum 50 character(s) are allowed for profile label";
        if (empty($status)) return "Profile status is required";
        if (!in_array($status,array("N","Y"))) return "Invalid status";

        return "";
    }
	

}
