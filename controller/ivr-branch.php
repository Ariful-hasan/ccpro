<?php
class IvrBranch extends Controller
{
    /**
     * IvrBranch constructor.
     */
    public function __construct()
    {
		parent::__construct();
	}
	
	public function init()
	{
		$this->actionTitles();
	}

    /**
     * Returns all the ivr branch titles
     */
    public function actionTitles()
	{
        $this->setPageTitle("IVR Branch Titles");
        $this->setGridDataUrl($this->url('task=get-ivr-branch-data&act=get-titles'));
        $this->display('ivr_branch_titles');
	}

    public function actionCreate()
    {
        include('model/MIvrBranch.php');
        $ivr_branch_model = new MIvrBranch();

        $mainobj = NULL;
        $request = $this->getRequest();
        if ($request->isPost())
        {
            $title = $request->getRequest('title','');
            $validation_message = $this->validateTitle($title);

            if ($validation_message){
                AddError($validation_message);
            }

            if (! $validation_message)
            {
                if ($ivr_branch_model->saveTitle($title))
                {
                    AddInfo("IVR Branch Title Added Successfully");
                    $this->getTemplate()->display_popup_msg([], true);
                }
                else{
                    AddError("Failed to Added IVR Branch Title");
                }
            }
        }

        $data['isAutoResize'] = true;
        $data['pageTitle'] = 'Add IVR Branch Title';
        $this->getTemplate()->display_popup('ivr_branch_add_edit', $data,true);
    }

    public function actionUpdate()
    {
        include_once('model/MIvrBranch.php');

        $ivr_branch_model = new MIvrBranch();

        $request = $this->getRequest();
        $title_id = $request->getRequest('title_id','');
        $data = [];
        $data['pageTitle'] =  "Update IVR Branch Title";
        $data['request'] =  $request;
        $data['mainobj'] =  $ivr_branch_model->findByID($title_id);
        $data['title_id'] = $title_id;

        if ($request->isPost()){
            $title = $request->getRequest('title','');

            $validation_error = $this->validateTitle($title);

            if ($validation_error){
                AddError($validation_error);
            }

            if (! $validation_error){
                if($ivr_branch_model->updateBranchTitle($title_id, $title)){
                    AddInfo("IVR Branch Title Updated Successfully");

                    $this->getTemplate()->display_popup_msg([],true);
                }else{
                    AddError("Failed to Update IVR Branch Title");
                }
            }
        }

        $this->getTemplate()->display_popup('ivr_branch_add_edit', $data, true);

    }

    /**
     * @param $title
     * @return string
     */
    protected function validateTitle($title)
    {
        if(! $title){
            return "IVR Branch Title is Required";
        }

        if (strlen($title) > 50){
            return "Maximum 50 Characters are Allowed for IVR Branch Title";
        }

        return "";
	}



	
}