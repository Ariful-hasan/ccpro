<?php
class MohFiller extends Controller
{
	function __construct(){
		parent::__construct();
		define('MAX_BRANCH', 21);
		
	}
	function init(){
		$skillid=$this->getRequest()->getRequest("skill",null);
		$data['pageTitle'] = 'MOH Filler';
		$data['dataUrl'] = $this->url('task=get-home-data&act=moh-filler&skill='.$skillid);
		$data['topMenuItems'] = array(//Q=Queue; H=Hold;X=End Call
				array('href'=>'task=moh-filler&act=upload-moh-filler&type=Q&skill='.$skillid,  'img'=>'fa fa-play', 'label'=>'Add New Queue', 'class'=>"lightboxWIFR",'dataattr'=>array('w'=>'600px','h'=>'450px')),
				array('href'=>'task=moh-filler&act=upload-moh-filler&type=H&skill='.$skillid, 'color'=>'success','img'=>'fa fa-play', 'label'=>'Add New Hold','class'=>"lightboxWIFR"),
				array('href'=>'task=moh-filler&act=upload-moh-filler&type=X&skill='.$skillid, 'color'=>'danger','img'=>'fa fa-play', 'label'=>'Add New End Call','class'=>"lightboxWIFR"),
		);
		$this->getTemplate()->display('moh-filler', $data);
	}
	function actionUploadMohFiller(){	
		$isedit=$this->getRequest()->getGet('e')==1;
		$mtype=$this->getRequest()->getGet('type');	
		$skill_id=$this->getRequest()->getGet('skill');
		$fillertype=array("Q"=>"Queue","H"=>"Hold","X"=>"End Call");
		AddModel("MMOHFiller");
		AddModel("MLanguage");		
		$mh=new MMOHFiller();
		include('model/MSkill.php');
		include('model/MIvr.php');
		$skill_model = new MSkill();
		$ivr_model = new MIvr();
		$data=array();
		$data['fullload']=true;		
		$data['type']=$mtype;
		$data['skill']=$skill_id;
		$data['branch']=$this->getRequest()->getGet('branch');
		$data['usedbranched']=$mh->getUsedKey($data['branch']);
		$data['isEdit']=&$isedit;
		$data['skill_options'] = $skill_model->getAllSkillOptions('', 'array');
		$data['ivr_options'] = $ivr_model->getIvrOptions();
		if(isset($_GET['skill'])){
			unset($data['skill_options'][$_GET['skill']]);
		}
	
		if($mtype=="X"){
			$data['pageTitle'] = (!empty($isedit)?"Edit ":"").'Queue Timeout';	
			$data['btntext'] = (!empty($isedit)?"Update ":"Add ").'Queue Timeout';
		}else{
			$data['pageTitle'] = (!empty($isedit)?"Edit ":"").'MOH Filler';
			$data['pageSubTitle'] = '- Type:'.GetStatusText($mtype, $fillertype);
			$data['btntext'] = (!empty($isedit)?"Update ":"Add ").'MOH Filler';
		}
		
		$mh=new MMOHFiller();
		$branchinfo=$mh->GetMOHFillerByBranch($data['branch']);
		if($isedit){
			$data['branchinfo']=&$branchinfo;
		}else{
			$data['branchinfo']=null;
		}
		
		if($this->getRequest()->isPost()){
						$mh=new MMOHFiller();
						//$filter_id=$skill_id.$mtype.$this->getRequest()->getPost('filler_id');
						$pbrach=$this->getRequest()->getPost('branch');
						$event=$this->getRequest()->getPost('event');
						if(!$isedit){
							$param=array(
									"skill_id"=>$skill_id,
									"filler_type"=>$mtype,
									"branch"=>$pbrach,
									"event"=>$event,
									"event_key"=>$skill_id.rand(1000,9999).time(),
									"filler_interval"=>$this->getRequest()->getPost('filler_interval'),
								//	"loop_count"=>$this->getRequest()->getPost('loop_count'),
									"replay_count"=>$this->getRequest()->getPost('replay_count'),
									"text"=>$this->getRequest()->getPost('text'),
									"TTS_update"=>'N'						
							);
							if($event=="GO" || $event=="LN"){
								$param['event_key']=$this->getRequest()->getPost('event_key');
							} else {
							//$mh
							        $varfileupload=!empty($_FILES["uploadfile"])?$_FILES["uploadfile"]:null;
							        if(!$varfileupload){
								        $param["event_key"]="";
                                                                }
							}
							//var_dump($param["event_key"]);exit;
							
							if($mh->AddNewEntry($param,$varfileupload,$this->getTemplate()->file_upload_path)){
								AddInfo("MOH Filler added successfully");
								$this->getTemplate()->display_popup_msg($data);
							}else{
								AddError("Failed to add MOH FIller");
							}	
						}else{
							$varfileupload=!empty($_FILES["uploadfile"])?$_FILES["uploadfile"]:null;
							/*
							if($varfileupload){
							        if ($branchinfo->event_key) {
							        }
							}
							*/
							
							if($mh->UpdateByBranchFromPost($data['branch'], $branchinfo, $varfileupload, $this->getTemplate()->file_upload_path)){
								AddInfo("MOH Filler updated successfully");
								$this->getTemplate()->display_popup_msg($data);
							}
						}					
						
					
			
			
			//AddInfo("test");
			//AddInfo(print_r($_POST,true));
			//$this->getTemplate()->display_popup_msg($data);
		}
			
		$target_path = $this->getTemplate()->file_upload_path.'Q/' . $data['skill']."/FILLER/";		
		$languages=MLanguage::getActiveLanguageList('A');		
		
		
		foreach ($languages as &$language){
			if($isedit){
				$extension_type = pathinfo($varfileupload, PATHINFO_EXTENSION);
				$fle = $target_path."{$language->lang_key}/{$branchinfo->event_key}";
				$fle_with_ext = $fle.".".$this->getTemplate()->voice_store_file_extension; 			
			}
			//echo $fle;
			$language->file_info['file_name'] = $isedit ? $branchinfo->event_key : ''; 
			$language->file_info['file_path'] = site_url().$this->url("task=ivrs&act=get-voice-file&objType=Q&objId={$data['skill']}&lang=FILLER/{$language->lang_key}&fileName={$language->file_info['file_name']}"); 
			$language->file_info['file_dir'] = $target_path."{$language->lang_key}/"; 
			
			if($isedit && file_exists($fle_with_ext)){
				$language->file_delete_url=$this->url("task=confirm-response&act=delete-mohf-data&pro=da&lan={$language->lang_key}&branch=".$data['branch']);
			}else{
				$language->file_delete_url="";
			}
			
		}
		
		$data['languages']=&$languages;
		
		$this->getTemplate()->display_popup('mod-filler-upload', $data);
		
	}
	function ShowTree($type="Q")
	{
		include('model/MIvr.php');
		include('model/MSkill.php');
		include('model/MIvrService.php');
		include('conf.extras.php');
		AddModel("MMOHFiller");
		AddModel("MSkill");
		$ivr_model = new MIvr();
		$moh_model = new MMOHFiller();
		$skill_model = new MSkill();
		$ivr_service_model = new MIvrService();
		$skill_model = new MSkill();
	
		$skillid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		$skill = $skill_model->getSkillById($skillid);
		if (empty($skill)) exit;
		$data['tree_details'] = $moh_model->getTreeDetails($skillid,$skill->skill_name,$type);
		/*if (empty($data['ivr_details'])) {
		 $ivr_model->addIVRDetailsByBranch(array('ivr_id'=>$ivrid, 'branch'=>$ivrid, 'event'=>'AN', 'event_key'=>'', 'text'=>'IVR Root'));
		 $data['ivr_details'] = $ivr_model->getIVRDetails($ivrid);
		}*/
		//GPrint($data['tree_details']);die;
		$data['skill_options'] = $skill_model->getSkillOptions();
		$data['sr_options'] = $ivr_service_model->getServiceOptions();
		$data['topMenuItems'] = array(				
				array('href'=>'task=skills&act=upload-welcome-voice&skill='.$skillid,  'img'=>'fa fa-play', 'label'=>'Welcome Voice','color'=>'success','class'=>"lightboxWIFR",'dataattr'=>array('w'=>'900px','h'=>'auto')),
				array('href'=>'task=skills&act=upload-hold-voice&skill='.$skillid,  'img'=>'fa fa-play', 'label'=>'Music on Hold','color'=>'success', 'class'=>"lightboxWIFR",'dataattr'=>array('w'=>'900px','h'=>'auto')),				
				array('href'=>'task=skills&act=update&skillid='.$skillid,  'img'=>'fa fa-gear','color'=>'success', 'label'=>'Properties', 'class'=>""),			
					
		);
		//$data['topMenuItems'][] =array('href'=>'task=moh-filler&act=queue-timeout&sid='.$skillid, 'color'=>'success','img'=>'fa fa-play', 'label'=>'Queue Timeout','class'=>"","property"=>array("disabled"=>"disabled"));
		if($type=="Q"){
			$data['pageTitle'] = 'Filler of Skill ::' . $skill->skill_name;
			$data['topMenuItems'][] =array('href'=>'task=moh-filler&act=queue-timeout&sid='.$skillid, 'color'=>'success','img'=>'fa fa-play', 'label'=>'Queue Timeout');
			$data['topMenuItems'][] =array('href'=>'task=moh-filler&act=config&sid='.$skillid, 'color'=>'success','img'=>'fa fa-play', 'label'=>'Filler','class'=>"","property"=>array("disabled"=>"disabled"));
			
			
		}elseif($type=="H"){
			$data['pageTitle'] = 'Filler of MOH::' . $skill->skill_name;
			$data['topMenuItems'][] =array('href'=>'task=moh-filler&act=queue-timeout&sid='.$skillid, 'color'=>'success','img'=>'fa fa-play', 'label'=>'Queue Timeout','class'=>"","property"=>array("disabled"=>"disabled"));
			$data['topMenuItems'][] =array('href'=>'task=moh-filler&act=config&sid='.$skillid, 'color'=>'success','img'=>'fa fa-play', 'label'=>'Filler','class'=>"");
			
		
		}elseif($type=="X"){
			$data['pageTitle'] = 'Queue Timeout of Skill::' . $skill->skill_name;
			$data['topMenuItems'][] =array('href'=>'task=moh-filler&act=queue-timeout&sid='.$skillid, 'color'=>'success','img'=>'fa fa-play', 'label'=>'Queue Timeout','class'=>"","property"=>array("disabled"=>"disabled"));
			$data['topMenuItems'][] =array('href'=>'task=moh-filler&act=config&sid='.$skillid, 'color'=>'success','img'=>'fa fa-play', 'label'=>'Filler','class'=>"");
			
		}
		
		
		$data['ivrid'] = $skillid;
		$data['skill'] = $skill;
		//$data['topMenuItems'] =array();
		$data['smi_selection'] = 'skills_';
		$this->getTemplate()->display('moh_config', $data);
	}
	function actionConfig()
	{
		$this->ShowTree('Q');
	}
	function actionQueueTimeout()
	{
		$this->ShowTree('X');
	}
	
}