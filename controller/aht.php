<?php

class Aht extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }


    public function actionGetBySkills()
    {
        AddModel('MAverageHandlingTime');
        $aht_model = new MAverageHandlingTime();

        $r = $this->getRequest()->getRequest('r', '');
        $dashboard_skills = "CATEGORY_". $r ."_SKILLS";

        if (!defined($dashboard_skills)) {
            die(json_encode(false));
        }

        $aht = $aht_model->getBySkills(constant($dashboard_skills));

        die(json_encode($aht));
    }


    public function actionFor777()
    {
        AddModel('MAverageHandlingTime');
        $aht_model = new MAverageHandlingTime();

        $aht = $aht_model->getFor777Category();

        die(json_encode($aht));
    }

    public function actionFor786()
    {
        AddModel('MAverageHandlingTime');
        $aht_model = new MAverageHandlingTime();

        $aht = $aht_model->getFor786Category();

        die(json_encode($aht));
    }
}
