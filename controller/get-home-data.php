<?php
require_once 'BaseTableDataController.php';
class GetHomeData extends BaseTableDataController
{
	var $pagination;
	
	function __construct() {
		parent::__construct();		
	}

	function actionAgents()
	{
		include('model/MAgent.php');
		include('model/MSkill.php');
		
		$agent_model = new MAgent();
		$skill_model = new MSkill();
		
		$utype = '';
		$agent_id = '';
		$did = '';
		$nickName = '';
		$supervisor_id = '';
		
		//$utype = isset($_POST['utype']) && $_POST['utype'] == 'S' ? 'S' : 'A';
		//$utype=$this->gridRequest->srcItem=="usertype"?$this->gridRequest->srcText:"";
		//$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&utype=$utype");
		if ($this->gridRequest->srcItem=="agent_id") {
		    $agent_id = $this->gridRequest->srcText;
		} elseif ($this->gridRequest->srcItem=="did") {
		    $did = $this->gridRequest->srcText;
		} elseif ($this->gridRequest->srcItem=="usertype") {
		    $utype = $this->gridRequest->srcText;
		} elseif ($this->gridRequest->srcItem=="nick") {
		    $nickName = $this->gridRequest->srcText;
		} elseif ($this->gridRequest->srcItem=="supervisor_id") {
		    $supervisor_id = $this->gridRequest->srcText;
		}
		
		$this->pagination->num_records = $agent_model->numAgents($utype, $agent_id, $did, '', $nickName, $supervisor_id);
		$agents = $this->pagination->num_records > 0 ?
		$agent_model->getAgents($utype, $agent_id, $did, '', $this->pagination->getOffset(), $this->pagination->rows_per_page, $nickName, $supervisor_id) : null;
		$skill_options = $skill_model->getAllAgentSkillOptions($this->getTemplate()->email_module, '', 'array');
		
		$responce = $this->getTableResponse();
		$responce->records = $this->pagination->num_records;
		$usertypes= get_role_list();//array("S"=>"Supervisor","A"=>"Agent");
		$result=&$agents;
		if(count($result)>0){
		        $curLoggedUserRND = UserAuth::getDBSuffix();
			foreach ( $result as &$data ) {				
				$skills = $agent_model->getAgentSkill($data->agent_id);
				$num_skills = 0;
				$dtstr="";
				if (is_array($skills)) {
					foreach ($skills as $skill) {						
						if ($num_skills > 0) $dtstr.= ', ';
						$dtstr.= isset($skill_options[$skill->skill_id]) ? $skill_options[$skill->skill_id] : $skill->skill_id;
						$dtstr.=' ('.$skill->priority.')';
						
						$num_skills++;
					}
				} else {
					$dtstr='No Skill';
				}
				$data->skill= ' ' . $dtstr;
				/*
				<a href="<?php echo $this->url('task='.$request->getControllerName()."&act=update&agentid=".$agent->agent_id);?>"><?php echo $agent->agent_id;?></a>
						<?php if (!empty($agent->nick)):?>&nbsp;[<?php echo $agent->nick;?>]<?php endif;?>
					*/
				
			$data->usertypemain=$data->usertype;
			
			

			
			if (empty($data->nick)) {
			    $data->nick = '-';
			} else {
			    $data->nick = ' ' . $data->nick;
			}
			if (empty($data->name)) {
                            $data->name = '-';
                        } else {
                            $data->name = ' ' . $data->name;
                        }
                        $data->did = empty($data->did) ? '-' : $data->did;
			//$data->web_password_priv=$data->web_password_priv=="Y"?'<a href="#">Yes</a>':'<a class="text-danger"  href="#">No</a>';
				
//			$data->web_password_priv="<a class='ConfirmAjaxWR' msg='Are you sure to ".($data->web_password_priv=="Y"?"Inactive":"Active")." TeleBanking for agent :".$data->agent_id."?' href='" . $this->url( "task=confirm-response&act=telebank&pro=rp&utype=$utype&agentid=". $data->agent_id . "&status=".($data->web_password_priv=="Y"?"N":"Y"))."'>".($data->web_password_priv=="Y"?"Yes":"No")."</a>";
			//$data->active="<a class='ConfirmAjaxWR' msg='Be confirm to do ".($data->active=="Y"?"Inactive":"Active")." agent :".$data->agent_id."?' href='" . $this->url( "task=confirm-response&act=agents&pro=cs&utype=$utype&agentid=". $data->agent_id . "&status=".($data->active=="Y"?"N":"Y"))."'>".($data->active=="Y"?"<span class='text-success'>Active</span>":"<span class='text-danger'>Inactive</span>")."</a>";
			if ($data->agent_id == Userauth::getCurrentUser()){
                $data->active = ($data->active == "Y" ? "<span class='text-success'>Active</span>" : "<span class='text-danger'>Inactive</span>");
            }else {
                $data->active = "<a class='ConfirmAjaxWR' msg='Do you confirm that you want to " . ($data->active == "Y" ? "inactivate" : "activate") . " agent :" . $data->agent_id . "?' href='" . $this->url("task=confirm-response&act=agents&pro=cs&utype=" . $data->usertype . "&agentid=" . $data->agent_id . "&status=" . ($data->active == "Y" ? "N" : "Y")) . "'>" . ($data->active == "Y" ? "<span class='text-success'>Active</span>" : "<span class='text-danger'>Inactive</span>") . "</a>";
            }
			if ($data->usertype == 'A' || $data->usertype == 'S' || $data->usertype == 'P' || $data->usertype == 'G') {
                if ($data->agent_id == Userauth::getCurrentUser()){
                    $data->password="-";
                }else{
                    $data->password="<a data-w='500' data-h='330' class='lightboxWIF' href='" . $this->url( "task=agents&act=resetPassword&agentid=". $data->agent_id . "&type=".($data->usertype=="A"?"Agent":"Supervisor"))."'>Reset</a>";
                }
			}
			if ($data->usertype == 'A' || $data->usertype == 'S') {
				$data->login_pin="<a data-w='500' data-h='330' class='lightboxWIF' href='" . $this->url( "task=agents&act=reset-login-pin-password&agentid=". $data->agent_id . "&type=".($data->usertype=="A"?"Agent":"Supervisor"))."'>Reset</a>";
			}

			if ($data->usertype == 'P' || $data->usertype == 'G'){
				$data->login_pin='';
			}

			if (UserAuth::getRoleID()=="R"){
				$data->skill_list="<a data-w='800' class='lightboxWIF' href='" . $this->url( "task=agents&act=agent-skill&agentid=". $data->agent_id)."'>[view]</a>";
			}
				//$data->nick="<a class='grid-btn btn btn-danger btn-xs' href='". $this->url("task=agents&act=update&agentid=".$data->agent_id)."'><i class='fa fa-minus-circle'></i>".$data->agent_id.(empty($data->nick)?"[".$data->nick."]":"")."</a>";
					
				//$deletebutton="<a class='ConfirmAjaxWR grid-btn btn btn-danger btn-xs' msg='Are you sure to delete(".$data->number.")?' href='" . client_url ( "user/confirmresponse?m=blist&act=d&dn=".$data->number) ."'><i class='fa fa-minus-circle'></i>  Delete</a>";
				//$editbtn="<a class='lightboxWR grid-btn btn btn-info btn-xs' href='" . client_url ( "user/addblocknumber?dn=".$data->number). "'><i class='fa fa-edit'></i> Edit</a>";			
				//$data->action=$editbtn.$deletebutton;
				//$deletebutton="<a class='ConfirmAjaxWR grid-btn btn btn-danger btn-xs' msg='Are you sure to delete(".$data->number.")?' href='" . client_url ( "user/confirmresponse?m=blist&act=d&dn=".$data->number) ."'><i class='fa fa-minus-circle'></i>  Delete</a>";
				//$data->action="<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete ".$type_name.$data->agent_id."?' href='" . $this->url( "task=confirm-response&act=agents&pro=da&utype={$data->usertype}&agentid=". $data->agent_id)."'><i class='fa fa-times'></i> Delete</a>";
                if ($data->agent_id == Userauth::getCurrentUser()) {
                    $data->action = "-";
                }else{
                    $data->action = "<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure that you want to delete " . $data->agent_id . "?' href='" . $this->url("task=confirm-response&act=agents&pro=da&utype={$data->usertype}&agentid=" . $data->agent_id) . "'><i class='fa fa-times'></i> Delete</a>";
                }
				if(UserAuth::getRoleID()=="R" || $data->usertypemain!="S"){
					$data->agent_id="<a class='' href='". $this->url("task=agents&act=update&agentid=".$data->agent_id)."'>".$data->agent_id."</a>";
				}
				/*
				if ($curLoggedUserRND == 'AC') {
				    if ($data->seat_id!='') $data->action .= "Logged In";
				}
				*/
				$data->usertype=!empty($usertypes[$data->usertype])?$usertypes[$data->usertype]:$data->usertype;
					
			}
		}
		$responce->rowdata = $result;		
		$this->ShowTableResponse();
	}

	function actionExt()
	{
		include('model/MReportUser.php');
		include('model/MReportUserDid.php');

		$agent_model = new MReportUser();
		$skill_model = new MReportUserDid();
		
		$user_id = '';
		$did = '';
		$name = '';
		$status = '';

		if ($this->gridRequest->srcItem=="login_id") {
			$user_id = $this->gridRequest->srcText;
		} elseif ($this->gridRequest->srcItem=="did") {
			$did = $this->gridRequest->srcText;
		} elseif ($this->gridRequest->srcItem=="name") {
			$name = $this->gridRequest->srcText;
		} elseif ($this->gridRequest->srcItem=="status") {
			$status = $this->gridRequest->srcText;
		}

		$this->pagination->num_records = $agent_model->numExternals($user_id, $did, $status, $name);
		$agents = $this->pagination->num_records > 0 ?
			$agent_model->getExternals($user_id, $did, $status, $name, $this->pagination->getOffset(), $this->pagination->rows_per_page, $nickName) : null;
		$skill_options = $skill_model->getAllReportUserDid();
		//var_dump($agents);
		$responce = $this->getTableResponse();
		$responce->records = $this->pagination->num_records;
		$usertypes=array("S"=>"Supervisor","A"=>"Agent");
		$result=&$agents;
		if(count($result)>0){
			$curLoggedUserRND = UserAuth::getDBSuffix();
			$skills = $agent_model->getUserDid();
			foreach ( $result as &$data ) {
				//$skills = $agent_model->getUserDid($data->user_id);
				$num_skills = 0;
				$didList = "";
				if (is_array($skills)) {
					foreach ($skills as $skill) {
						if ($skill->user_id == $data->user_id){
							$didList = !empty($didList) ? $didList.", ".$skill->did : $skill->did;
						}
					}
				}
				$data->did = $didList;

				//$data->usertypemain=$data->usertype;

				if (empty($data->name)) {
					$data->name = '-';
				} else {
					$data->name = ' ' . $data->name;
				}
				//$data->did = empty($data->did) ? '-' : $data->did;

				$data->active="<a class='ConfirmAjaxWR' msg='Do you confirm that you want to ".($data->status=="A"?"lock":"unlock")." user :".$data->login_id."?' href='" . $this->url( "task=confirm-response&act=extuser&pro=cs&userid=". $data->user_id . "&status=".($data->status=="A"?"L":"A"))."'>".($data->status=="A"?"<span class='text-success'>Active</span>":"<span class='text-danger'>Locked</span>")."</a>";
				$data->password="<a data-w='500' data-h='330' class='lightboxWIF' href='" . $this->url( "task=extuser&act=resetPassword&agentid=". $data->user_id . "&loginid=".$data->login_id)."'>Reset</a>";



				//$data->nick="<a class='grid-btn btn btn-danger btn-xs' href='". $this->url("task=agents&act=update&agentid=".$data->agent_id)."'><i class='fa fa-minus-circle'></i>".$data->agent_id.(empty($data->nick)?"[".$data->nick."]":"")."</a>";

				//$deletebutton="<a class='ConfirmAjaxWR grid-btn btn btn-danger btn-xs' msg='Are you sure to delete(".$data->number.")?' href='" . client_url ( "user/confirmresponse?m=blist&act=d&dn=".$data->number) ."'><i class='fa fa-minus-circle'></i>  Delete</a>";
				//$editbtn="<a class='lightboxWR grid-btn btn btn-info btn-xs' href='" . client_url ( "user/addblocknumber?dn=".$data->number). "'><i class='fa fa-edit'></i> Edit</a>";
				//$data->action=$editbtn.$deletebutton;
				//$deletebutton="<a class='ConfirmAjaxWR grid-btn btn btn-danger btn-xs' msg='Are you sure to delete(".$data->number.")?' href='" . client_url ( "user/confirmresponse?m=blist&act=d&dn=".$data->number) ."'><i class='fa fa-minus-circle'></i>  Delete</a>";
				//$data->action="<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete ".$type_name.$data->agent_id."?' href='" . $this->url( "task=confirm-response&act=agents&pro=da&utype={$data->usertype}&agentid=". $data->agent_id)."'><i class='fa fa-times'></i> Delete</a>";
				$data->action="<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure that you want to delete ".$data->login_id."?' href='" . $this->url( "task=confirm-response&act=extuser&pro=da&userid=". $data->user_id)."'><i class='fa fa-times'></i> Delete</a>";
				$data->login_id="<a class='' href='". $this->url("task=extuser&act=update&agentid=".$data->login_id)."'>".$data->login_id."</a>";

				/*
				if ($curLoggedUserRND == 'AC') {
				    if ($data->seat_id!='') $data->action .= "Logged In";
				}
				*/
				//$data->usertype=!empty($usertypes[$data->usertype])?$usertypes[$data->usertype]:$data->usertype;

			}
		}
		$responce->rowdata = $result;
		$this->ShowTableResponse();
	}
	function actionCtis()
	{
	    include('model/MCti.php');
            include('model/MSkill.php');
            include('model/MIvr.php');
            $cti_model = new MCti();
            $skill_model = new MSkill();
            $ivr_model = new MIvr();

            $ctis = $cti_model->getCtis();
            $skill_options = $skill_model->getAllSkillOptions('', 'array');
            $ivr_options = $ivr_model->getIvrOptions();

	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;
	
	    $result=&$ctis;
	    if(count($result)>0){
	        $ctiType = array('DD'=>'Direct Dial', 'ED'=>'External Dial', 'SQ'=>'Skill', 'VM' => 'Voice Mail', 'XF' => 'External Transfer', 'IV' => 'IVR');
	        foreach ( $result as &$data ) {
	            $data->cti_did = $cti_model->getDID($data->cti_id, 'string');
	            $data->actionType = isset($ctiType[$data->action_type]) ? $ctiType[$data->action_type] : $data->action_type;
	            if ($data->action_type == 'SQ' || $data->action_type == 'VM') {
	                $data->description = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
	            } elseif ($data->action_type == 'XF') {
	                $data->description = $data->param;
	            } elseif ($data->action_type == 'IV') {
	                $data->description = isset($ivr_options[$data->ivr_id]) ? $ivr_options[$data->ivr_id] : $data->ivr_id;
	            } else {
	                $data->description = '-';
	            }
	            $data->active = $data->active=="Y" ? "<span class='text-success'>Enable</span>" : "<span class='text-danger'>Disable</span>";
	            /*
	            if ($data->is_priority == 'Y') {
	                $data->priority = "<a class='ConfirmAjaxWR' msg='Confirm to do DISABLE Priority of CTI ".$data->cti_name."' ";
	                $data->priority .= "href='" . $this->url("task=confirm-response&act=ctis&pro=priority&ctiid=".$data->cti_id."&priority=N")."'><span class='text-success'>Enable</span></a>";
	            } else {
	                $data->priority = "<a class='ConfirmAjaxWR text-danger' msg='Confirm to do ENABLE Priority of CTI ".$data->cti_name."' ";
	                $data->priority .= "href='" . $this->url("task=confirm-response&act=ctis&pro=priority&ctiid=".$data->cti_id."&priority=Y")."'><span class='text-danger'>Disable</span></a>";
	            }
	            */
	            $data->cti_name = "<a href='". $this->url("task=ctis&act=update&ctiid=".$data->cti_id)."'>".$data->cti_name."</a>";
	        }
	    }
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}
	
	function actionSkills()
	{
	    include('model/MSkill.php');
		$skill_model = new MSkill();

		if (UserAuth::hasRole('admin')) {
			$skills = $skill_model->getSkills('', '', 0, 100);
		} else {
			$skills = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), '', 0, 100);
		}

	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;
	
	    $result=&$skills;
	    if(count($result) > 0){
	        $qTypes = array('V'=>'Voice', 'E'=> 'Email', 'C'=>'Chat', 'P' => 'Dialer');
	        $routeType = array('R'=>'Round Robin', 'L'=>'Longest Idle', 'A'=>'Random');
	        $vLogger = array('Y'=>'Enable', 'M'=>'Manual');
	        $skillType = '';
	        foreach ( $result as &$data ) {
                $data->action = '';
	            if (UserAuth::hasRole('admin')){
	                $data->skillName = "<a href='". $this->url("task=skills&act=update&skillid=".$data->skill_id). "'>".$data->skill_name."</a>";
	            }else {
	                $data->skillName = $data->skill_name;
	            }
	            
	            /*
	            if ($this->getTemplate()->email_module){
	                $data->qtype = $data->qtype=='E' ? "Email" : "Voice";
	            }else {
	                $data->qtype = "";
	            }
	            */
	            $skillType = $data->qtype;
	            if (isset($qTypes[$data->qtype])) {
	                $data->qtype = $qTypes[$data->qtype];
	            }
	            $data->popup_url = empty($data->popup_url) ? "No" : "Yes";
	            //$data->caller_priority = $data->caller_priority=="Y" ? "<span class='text-success'>Enable</span>" : "<span class='text-danger'>Disable</span>";
	            $data->acd_mode = isset($routeType[$data->acd_mode]) ? $routeType[$data->acd_mode] : $data->acd_mode;
	            $data->auto_answer = $data->auto_answer == 'Y' ? 'Yes' : 'No';
	            $data->record_all_calls = isset($vLogger[$data->record_all_calls]) ? $vLogger[$data->record_all_calls] : "Disable";
	            $data->find_last_agent = $data->find_last_agent <= 0 ? 'No' : 'Yes (' . $data->find_last_agent . ')';
	            if ($data->active == 'Y') {
	                if (UserAuth::hasRole('admin')){
    	                $data->active = "<a class='ConfirmAjaxWR' msg='Be confirm to do INACTIVE Service: ". $data->skill_name ."' ";
    	                $data->active .= "href='" . $this->url("task=confirm-response&act=skills&pro=activate&skillid=".$data->skill_id."&active=N")."'><span class='text-success'>Active</span></a>";
	                }else {
	                    $data->active = "<span class='text-success'>Active</span>";
	                }
	            } else {
	                if (UserAuth::hasRole('admin')){
	                    $data->active = "<a class='ConfirmAjaxWR' msg='Be confirm to do ACTIVE Service: ".$data->skill_name."' ";
	                    $data->active .= "href='" . $this->url("task=confirm-response&act=skills&pro=activate&skillid=".$data->skill_id."&active=Y")."'><span class='text-danger'>Inactive</span></a>";
	                }else {
	                    $data->active = "<span class='text-danger'>Inactive</span>";
	                }
	            }
	            $data->agentList = "<a data-w='800' class='lightboxWIF' href='" . $this->url( "task=skills&act=agent&skillid=". $data->skill_id)."'>[view]</a>";
	            
	            $data->action .= "<a class='btn btn-success btn-xs' href='" . $this->url( "task=moh-filler&act=config&sid={$data->skill_id}")."'> MOH Filler <i class='fa fa-angle-double-right'></i></a>";
	            if ($skillType == "P")
                {
                    $data->action .= "<a title='Manage Predictive Dial' class='btn btn-purple btn-xs' href='" . $this->url( "task=predictive-dial&act=manage&sid={$data->skill_id}&sname={$data->skill_name}")."'>Manage PD</a>";
                }
	            $data->dispList = "";
	            if ($skillType == 'E'){
	                $data->dispList = "<a href='" . $this->url( "task=emaildc&sid=". $data->skill_id)."'>[view]</a>";
	            }elseif ($skillType == 'C'){
	                $data->dispList = "<a href='" . $this->url( "task=chatdc&sid=". $data->skill_id)."'>[view]</a>";
	            }	            
	        }
	    }
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}
	function actionMohFiller()
	{
		include('model/MMOHFiller.php');
		$moh_model = new MMOHFiller();
		$skill_id=$this->getRequest()->getRequest("skill",null);
		$this->pagination->num_records = $moh_model->numTotal($skill_id);
		$result = $this->pagination->num_records > 0 ?$moh_model->getAll($skill_id,'',$this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
		$this->pagination->num_current_records = is_array($result) ? count($result) : 0;
		
		$responce = $this->getTableResponse();
		$responce->records = $this->pagination->num_records;	
		if($skill_id){	
			if(count($result) > 0){	
				$fillertype=array("Q"=>"Queue","H"=>"Hold","X"=>"End Call");
				include('model/MSkill.php');
				$skill_model = new MSkill();
				$skills = $skill_model->getSkillsNamesArray();
				
				foreach ( $result as &$data ) {
					$data->filler_type=GetStatusText($data->filler_type, $fillertype);	
					$data->skill_id=GetStatusText($data->skill_id, $skills);
					//$data->action="<a class='btn btn-success btn-xs' href='" . $this->url( "task=moh-filler&skill={$data->skill_id}")."'><i class='fa fa-angle-double-right'></i> MOH Filler</a>";
					//$data->action="<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete this MOH Filler?' href='" . $this->url( "task=confirm-response&act=moh-filler&pro=da&filler_id={$data->filler_id}")."'><i class='fa fa-times'></i> Delete</a>";
				}
			}
			$responce->rowdata = $result;
		}
		$this->ShowTableResponse();
	}
	
	function actionIvrs()
	{
	    include('model/MIvr.php');
		$ivr_model = new MIvr();

		$ivrsData = $ivr_model->getIvrs('', 0, 100);

	    $responce = $this->getTableResponse();
	   $responce->records = $this->pagination->num_records;
	
	    $result=&$ivrsData;
	    if(count($result) > 0){
	        $languages = array('E'=>'ENG', 'B'=>'BAN');
	        foreach ( $result as &$data ) {
	            $data->ivr_name = "<a href='". $this->url("task=ivrs&act=config&ivrid=".$data->ivr_id). "'>".$data->ivr_name."</a>";

	            $lang1 = strlen($data->language) > 0 ? substr($data->language, 0, 1) : '';
	            if (isset($languages[$lang1])) $lang1 = $languages[$lang1];
	            $lang2 = strlen($data->language) == 2 ? substr($data->language, 1, 1) : '';
	            if (isset($languages[$lang2])) $lang2 = $languages[$lang2];
	            $data->languages = $lang1;
	            if (!empty($lang2)) $data->languages .= ", ".$lang2;
	            
	            $data->editLink = "<a href='". $this->url("task=ivrs&act=update&ivrid=".$data->ivr_id). "'>Edit</a>";
	        }
	    }
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}
	
	function actionIvrtransfer()
	{
	    include('model/MIvr.php');
	    include('model/MSkill.php');
		$skill_model = new MSkill();
	    $ivr_model = new MIvr();
	    
	    $title = "";
	    $skill_id = "";
	     
	    if ($this->gridRequest->isMultisearch){
	        $title = $this->gridRequest->getMultiParam('title');
	        $skill_id = $this->gridRequest->getMultiParam('skillid');
	    }
	    
	    $selectArr = array('0'=>'Select');
	    $skill_options = array_merge ( $selectArr, $skill_model->getAllSkillOptions('', 'array') );
	    $this->pagination->num_records = $ivr_model->numTransferToIVR($title, $skill_id);
	     
	    $records = $this->pagination->num_records > 0 ?
	    $ivr_model->getTransferToIVR($title, $skill_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
	    $this->pagination->num_current_records = is_array($records) ? count($records) : 0;
	     
	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;
	     
	    $result=&$records;
	    if(count($result) > 0){
	        foreach ( $result as &$data ) {
	        	$barnch_skill_list = $ivr_model->getTransferBranchSkillList($data->ivr_branch);
	        	// GPrint($barnch_skill_list);

	        	$skill_name = [];
	        	foreach ($barnch_skill_list as $key => $item) {
	        		if(empty($item->skill_id))
	        			$skill_name[] = 'All Skill';
	        		else
	        			$skill_name[] = $skill_options[$item->skill_id];
	        	}
	        	$data->skill_name = implode(', ', $skill_name);

	            // if (isset($skill_options[$data->skill_id])) {
	            //     $data->skill_name = $skill_options[$data->skill_id];
	            // } else {
	            //     if (empty($data->skill_id)) {
	            //         $data->skill_name = 'All Skill';
	            //     } else {
	            //         $data->skill_name = $data->skill_id;
	            //     }
	            // }
	            
	            //$data->skill_name = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : empty($data->skill_id) ? 'All Skill' : "";
	            
	            if ($data->verified_call_only == 'Y') {
	            	$data->verified_call_txt = "<span class='text-success'>Yes</span>";
	            } else {
	            	$data->verified_call_txt = "<span class='text-danger'>No</span>";
	            }
	            
	            if ($data->active == 'Y') {
	            	$data->active_txt = "<span class='text-success'>Active</span>";
	            } else {
	            	$data->active_txt = "<span class='text-danger'>Inactive</span>";
	            }
	            
	            $data->actUrl = "<a class='btn btn-info btn-xs' title='Edit' href='".$this->url("task=ivrs&act=edit-transfer-ivr&tid=".$data->ivr_branch)."'><i class='fa fa-pencil-square-o'></i></a> &nbsp; ";
	            $data->actUrl .= "<a class='ConfirmAjaxWR btn btn-danger btn-xs' title='Delete' msg='Are you sure to Delete Transfer to IVR " . $data->title . "?' ";
	            $data->actUrl .= "href='".$this->url("task=confirm-response&act=ivr-transfer&pro=delete&tid=".$data->ivr_branch)."'><i class='fa fa-times'></i></a>";
	        }
	    }
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}
	
	/* function actionAgentSkillCdr()
	{
	    include('model/MAgentReport.php');
	    include('model/MSkill.php');
	    include('model/MIvr.php');
	    include('model/MAgent.php');
	    include('lib/Pagination.php');
	    include('lib/DateHelper.php');
	    
	    $report_model = new MAgentReport();
	    $skill_model = new MSkill();
	    $ivr_model = new MIvr();
	    $agent_model = new MAgent();
	    $pagination = new Pagination();
	    
	    $dateinfo = DateHelper::get_input_time_details();
	    $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "inbound";
	    $cli = isset($_REQUEST['cli']) ? trim($_REQUEST['cli']) : "";
	    $did = isset($_REQUEST['did']) ? trim($_REQUEST['did']) : "";
	    $aid = UserAuth::getCurrentUser();
	    $skills = $agent_model->getAgentSkill($aid);
	    
	    if ($type=='outbound') {
	        $pageTitle = 'Skill CDR - Outbound';
	        $ivr_options = null;
	        $template_file = 'agent_skill_cdr_outbound';
	    } else {
	        $type = 'inbound';
	        $pageTitle = 'Skill CDR - Inbound';
	        $ivr_options = $ivr_model->getIvrOptions();
	        $template_file = 'agent_skill_cdr_inbound';
	    }
	    
	    $skill_options = $skill_model->getAllSkillOptions('', 'array');
	    $pagination->num_records = $report_model->numSkillCDRs($type, $skills, $cli, $did, $aid, $dateinfo);
	    
	    $callsData = $pagination->num_records > 0 ?
	    $report_model->getSkillCDRs($type, $skills, $cli, $did, $aid, $dateinfo, $pagination->getOffset(), $pagination->rows_per_page) : null;	    
	
	    $responce = $this->getTableResponse();
	    $responce->records = $pagination->num_records;
	
	    $result=&$callsData;
	    if(count($result) > 0){
	        foreach ( $result as &$data ) {

	        }
	    }
	    $responce->rowdata = $result;
	    die(json_encode($responce));
	} */
	
	function actionCrm_inInit(){
	    include('model/MCrmIn.php');
	    include('lib/DateHelper.php');
	    include('model/MSkillCrmTemplate.php');
	    $crm_dp_model = new MSkillCrmTemplate();
	    $crm_model = new MCrmIn();
	    
	    $dateTimeArray = array();
	    if ($this->gridRequest->isMultisearch){
	        $dateTimeArray = $this->gridRequest->getMultiParam('tstamp');
	    }
	    $dateinfo = DateHelper::get_cc_time_details($dateTimeArray, true);
	    $account_id = "";
	    $dcode = "";
	    
	    if ($this->gridRequest->isMultisearch){
	        $account_id = $this->gridRequest->getMultiParam('account_id');
	        $dcode = $this->gridRequest->getMultiParam('dcode');
	    }
	    
	    $dp_options = $crm_dp_model->getDispositionSelectOptions();
	    $this->pagination->num_records = $crm_model->numCrmRecords($account_id, $dcode, $dateinfo);
	    
	    $records = $this->pagination->num_records > 0 ?
	    $crm_model->getCrmRecords($account_id, $dcode, $this->pagination->getOffset(), $this->pagination->rows_per_page, $dateinfo) : null;
	    $this->pagination->num_current_records = is_array($records) ? count($records) : 0;
	    
	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;
	    
	    $result=&$records;
	    if(count($result) > 0){
	        foreach ( $result as &$data ) {
	            $data->disposition = $data->disposition_id . ' - ';
	            if (!empty($data->disposition_id) && isset($dp_options[$data->disposition_id])){
	                $data->disposition .= $dp_options[$data->disposition_id];
	            }
	            $data->tstamp = empty($data->tstamp) ? '-' : date("Y-m-d H:i:s", $data->tstamp);
	            $data->disposUrl = "<a href='".$this->url("task=crm_in&act=disposition&rid=".$data->record_id)."' data-w='850' data-h='450' class='lightboxWIF'><img width='16' height='16' border='0' class='bottom' src='image/icon/application_view_list.png' alt='History' title='History' /></a>";
	        }
	        
	        //Download Voice File need to implement
	        /* if (is_array($records) && !isset($is_popup)) {
	            $session_id = session_id();
	            $download = md5($pageTitle.$session_id);
	            $dl_link = "download=$download";
	            $url = $pagination->base_link . "&$dl_link";
	            echo '<img class="bottom" height="11" width="20" border="0" src="image/down_excel.gif"/><a href="' . $url . '" class="btn-link">download current records</a>';
	            if ($this->is_voice_file_downloadable && (!empty($dial_num) || !empty($camid) || !empty($leadid) || !empty($disp_code))) {
	                $url = "index.php?task=crm&act=downloadcrmvoice&dial_num=$dial_num&camid=$camid&leadid=$leadid&disp_code=$disp_code&vdl=voice";
	                echo '&nbsp; &nbsp; <img class="bottom" height="11" width="20" border="0" src="image/down_excel.gif"/><a href="'.$url.'" class="btn-link dl-voice">download voice files</a>';
	            }
	        } */
	    }
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}	
	
	function actionCrm_Init(){
	    include('model/MCrm.php');
	    include('model/MCampaign.php');
	    include('model/MCrmDisposition.php');
	    
	    $crm_model = new MCrm();
	    $campaign_model = new MCampaign();
	    $crm_dp_model = new MCrmDisposition();
	    
	    $pageTitle = 'CRM Record(s)';
	    $campaign_options = $campaign_model->getCampaignSelectOptions();
	    $lead_options = $campaign_model->getLeadSelectOptions();
	    $dp_options = $crm_dp_model->getDispositions();
	    
	    $dial_num = "";
	    $camid = "";
	    $leadid = "";
	    $disp_code = "";
	    
	    if ($this->gridRequest->isMultisearch){
	        $dial_num = $this->gridRequest->getMultiParam('dial_number');
	        $camid = $this->gridRequest->getMultiParam('cp_title');
	        $leadid = $this->gridRequest->getMultiParam('lp_title');
	        $disp_code = $this->gridRequest->getMultiParam('disp_code');
	        
	        $audit_text = $this->getCRMAuditText($dial_num, $camid, $leadid, $disp_code, $campaign_options, $lead_options, $dp_options);
	        $crm_model->addToAuditLog($pageTitle, 'V', "", $audit_text);
	    }	    
	    
	    $this->pagination->num_records = $crm_model->numCrmRecords($dial_num, $camid, $leadid, $disp_code);
	    $records = $this->pagination->num_records > 0 ?
	    $crm_model->getCrmRecords($dial_num, $camid, $leadid, $disp_code, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
	    $this->pagination->num_current_records = is_array($records) ? count($records) : 0;
	     
	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;
	    

	    $result=&$records;
	    if(count($result) > 0){
	        foreach ( $result as &$data ) {
	            $data->dial_number = '<a target="_blank" href="'.$this->url('task=crm&act=details&record_id='.$data->record_id).'">'.$data->dial_number.'</a>';
	            $data->disposition_id = '[' . $data->last_disposition_id . '] ' . $log->dp_title;
	            $data->last_dial_time = date("Y-m-d H:i:s", $data->last_dial_time);
	            
	            if ($data->status == 'N') $data->status =  'New';
	            elseif ($data->status == 'P') $data->status =  'Progress';
	            elseif ($data->status == 'C') $data->status =  'Complete';
	            else $data->status =  $data->status;
	            
	            if (!empty($data->last_callid)) {
	                $file_timestamp = substr($data->last_callid, 0, 10);
	                $yyyy = date("Y", $file_timestamp);
	                $yyyy_mm_dd = date("Y_m_d", $file_timestamp);
	                $sound_file_gsm = $this->voice_logger_path . "$yyyy/$yyyy_mm_dd/" . $data->last_callid . ".gsm";
	                if (file_exists($sound_file_gsm) && filesize($sound_file_gsm) > 0) {
	                    $data->audio_callid = "<a href=\"#\" onClick=\"playFile('$data->last_callid')\"><img src=\"image/play2.gif\" title=\"gsm\" border=0></a>";
	                    if ($this->is_voice_file_downloadable) {
	                        $data->audio_callid .= " <a target=\"blank\" href=\"$sound_file_gsm\"><img src=\"image/play_in_browser2.gif\" title=\"Play in browser\" border=0 width=\"15\"></a>";
	                    }
	            
	                } else {
	                    $data->audio_callid = "NONE";
	                }
	            } else {
	                $data->audio_callid = "NONE";
	            }
	        }
	    }

	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}
	
	function getCRMAuditText($dial_num, $camid, $leadid, $disp_code, $campaign_options, $lead_options, $dp_options)
	{
	    $audit_text = '';
	    	
	    if (!empty($dial_num)) $audit_text .= "Dial number=$dial_num;";
	    if (!empty($camid)) {
	        $txt = isset($campaign_options[$camid]) ? $campaign_options[$camid] : $camid;
	        $audit_text .= "Campaign=" . $txt . ";";
	    }
	    if (!empty($leadid)) {
	        $txt = isset($lead_options[$leadid]) ? $lead_options[$leadid] : $leadid;
	        $audit_text .= "Lead=" . $txt . ";";
	    }
	
	    if (!empty($disp_code)) {
	        $txt = '';
	        //$txt = isset($data['dp_options'][$disp_code]) ? $data['dp_options'][$disp_code] : $disp_code;
	        if (is_array($dp_options)) foreach ($dp_options as $dp) {
	            if ($dp->disposition_id == $disp_code) {
	
	                if ($dp->campaign_id == '0000') $txt = 'System';
	                else if ($dp->campaign_id == '1111') $txt = 'General';
	                else $txt = $dp->campaign_title;
	                $txt = $txt . ' - ' . $dp->title;
	            }
	        }
	        	
	        if (empty($txt)) $txt = $disp_code;
	        $audit_text .= "Disposition=" . $txt . ";";
	    }
	
	    return $audit_text;
	}
	
	function actionCampaign(){
	    include('model/MCampaign.php');
	    include('model/MSkill.php');
	    include('model/MIvr.php');
	    $ivr_model = new MIvr();
	    $campaign_model = new MCampaign();
	    $skill_model = new MSkill();

		$title = "";
		if ($this->gridRequest->srcItem == "title") {
			$title = $this->gridRequest->srcText;
		}
	    
	    $this->pagination->num_records = $campaign_model->getCampaignCount($title);
	    $records = $this->pagination->num_records > 0 ?
	    $campaign_model->getCampaignList($title, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
	    $this->pagination->num_current_records = is_array($records) ? count($records) : 0;
	    
	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;

	    $skill_options = $skill_model->getAllSkillOptions('', 'array');
	    $ivr_options = $ivr_model->getIvrOptions();	     
	    $dial_engine_options = array('PG'=>'Progressive', 'PD'=>'Predictive', 'RS'=>'Responsive', 'AN'=>'Announcement', 'AA'=>'Acknowledgement', 'SR'=>'Survey');
	    $status_options = array('A'=>'Active', 'T'=>'Timeout', 'C'=>'Completed', 'N'=>'Inactive', 'E'=>'Error');
	    
	    $result=&$records;
	    if(count($result) > 0){
	        $pd_files_path = $this->getTemplate()->file_upload_path . 'PD/';
	        foreach ( $result as &$data ) {
	            $data->campaign_id_url = '<a href="'.$this->url('task=campaign&act=update&cid='.$data->campaign_id).'">'.$data->campaign_id.'</a>';
	            
	            $dialEngine = "";
				if($data->dial_engine == 'PD' || $data->dial_engine == 'RS') {
					$data->max_pacing_ratio = $data->max_pacing_ratio;
					$data->max_drop_ratio = $data->max_drop_rate;
				}else{
					$data->max_pacing_ratio = "-";
					$data->max_drop_ratio = "-";
				}
	            $dialEngine = isset($dial_engine_options[$data->dial_engine]) ? $dial_engine_options[$data->dial_engine] : $data->dial_engine;
	            if ($data->dial_engine == 'PG' || $data->dial_engine == 'PD' || $data->dial_engine == 'RS') {
	                $dialEngine .= '<br />';
	                $dialEngine .= isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
	            } else if ($data->dial_engine == 'SR') {
	                $dialEngine .= '<br />';
	                $dialEngine .= isset($ivr_options[$data->skill_id]) ? $ivr_options[$data->skill_id] : $data->skill_id;
	            }
	            $data->dial_engine = $dialEngine;	            
	            $data->retry_interval = "<a href='".$this->url('task=campaign&act=rinterval&cid='.$data->campaign_id)."' class='lightboxWIF' data-w='600' data-h='400'>[view]</a>";
	            
	            if (empty($data->status) || $data->status == 'N') {
	                $data->statusTxt = "<a class='ConfirmAjaxWR' msg='Are you sure that you want to activate campaign " . $data->campaign_id . "?' ";
	                $data->statusTxt .= "href='" . $this->url("task=confirm-response&act=campaign&pro=activate&status=A&cid=".$data->campaign_id)."'><span class='text-danger'>Inactive</span></a>";
	            } elseif ($data->status == 'A') {
	                $data->statusTxt = "<a class='ConfirmAjaxWR' msg='Are you sure that you want to inactivate campaign " . $data->campaign_id . "?' ";
	                $data->statusTxt .= "href='" . $this->url("task=confirm-response&act=campaign&pro=activate&status=N&cid=".$data->campaign_id)."'><span class='text-success'>Active</span></a>";
	            } else {
	                $dataStatus = isset($status_options[$data->status]) ? $status_options[$data->status] : $data->status;
	                $data->statusTxt = "<a class='ConfirmAjaxWR' msg='Are you sure that you want to activate campaign " . $data->campaign_id . "?' ";
	                $data->statusTxt .= "href='" . $this->url("task=confirm-response&act=campaign&pro=activate&status=A&cid=".$data->campaign_id)."'>$dataStatus</a>";
	            }
	            
	            $data->stopTimeTxt = '-';
				$data->startTimeTxt = '-';
	            if ($data->status == 'A') {
					$data->startTimeTxt = !empty($data->start_time) && $data->start_time != '0000-00-00 00:00:00' ? $data->start_time : '-';
	                //$data->stopTimeTxt = "<a data-w='600' class='lightboxWIF' href='" . $this->url("task=campaign&act=setetime&cid=".$data->campaign_id) . "'>";
	                //$data->stopTimeTxt .= $data->stop_time . '</a>';
	                $data->stopTimeTxt = $data->stop_time;
	            }
	            
	            $data->VMTxt = "<a data-w='600' class='lightboxWIF' href='" . $this->url("task=campaign&act=upload-voice-file&cid=".$data->campaign_id) . "'>";
	            if (file_exists($pd_files_path . $data->campaign_id . '/' . 'vm.wav')) {
	                $data->VMTxt .= 'Yes';
	            } else {
	                $data->VMTxt .= 'No';
	            }
                    $data->VMTxt .= '</a>';

				$data->action = "";
				//$data->action .= "<a class='ConfirmAjaxWR' href='".$this->url("task=confirm-response&act=campaign&pro=refresh&cid=".$data->campaign_id)."' ";
	            //$data->action .= "msg='Do you want to refresh lead(s) of ".$data->campaign_id."?' style='color:green'><i class='fa fa-refresh'></i></a> &nbsp; &nbsp;";
	            if(strlen($data->campaign_id) >= 4){
	                $data->action .= "<a class='ConfirmAjaxWR' href='" . $this->url("task=confirm-response&act=campaign&pro=delete&cid=".$data->campaign_id)."' ";
	                $data->action .= "msg='Are you sure to delete campaign # ".$data->campaign_id ."?' style='color:red'><i class='fa fa-trash-o'></i></a>";
	            }
	        }
	    }
	    
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}

	function actionCampaignLead(){
		include('model/MCampaign.php');
		$campaign_model = new MCampaign();

		$title = "";
		if ($this->gridRequest->srcItem == "title") {
			$title = $this->gridRequest->srcText;
		}

		$this->pagination->num_records = $campaign_model->getLeadCount($title);
		$records = $this->pagination->num_records > 0 ? $campaign_model->getLeadsList($title, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
		$this->pagination->num_current_records = is_array($records) ? count($records) : 0;

		$responce = $this->getTableResponse();
		$responce->records = $this->pagination->num_records;
		$result = &$records;
		
		if(count($result) > 0){
			$session_id = session_id();
			foreach ( $result as &$data ) {
				$data->action = "";
				$data->batchdl = "";
				$data->lead_id_txt = '<a href="'.$this->url("task=lead&act=update&lid=".$data->lead_id).'">'.$data->lead_id.'</a>';
				$data->number_count_txt = $data->number_count;

				if(!is_numeric($data->lead_id)){
					$data->action .= "<a class='ConfirmAjaxWR btn btn-danger btn-xs' href='" . $this->url("task=confirm-response&act=campaignlead&pro=delete&lid=".$data->lead_id."&nums=".$data->number_count)."' ";
					$data->action .= "msg='Are you sure to delete lead # ".$data->lead_id ."?' style='padding: 0 5px;'><i class='fa fa-times'></i></a>";
				}
				if ($data->number_count > 0){
					$data->number_count_txt = '<a href="'.$this->url("task=lead&act=numbers&lid=".$data->lead_id).'">details ('.$data->number_count.')</a>';

					$dl = md5('Numbers for Lead ' . $data->lead_id . $session_id);
					$data->batchdl = " &nbsp; <a class='btn btn-success btn-xs' target='_blank' href='" . $this->url("task=lead&act=download&lid=".$data->lead_id."&download=".$dl)."' ";
					$data->batchdl .= "title=\"Download\" style='padding: 0 5px;'><i class='fa fa-download'></i></a>";
				}
			}
		}

		$responce->rowdata = $result;
		$this->ShowTableResponse();
	}

	function actionCampaignDisposition(){
		include('model/MCrmDisposition.php');
		$disposition_model = new MCrmDisposition();

		$title = "";
		if ($this->gridRequest->srcItem == "title") {
			$title = $this->gridRequest->srcText;
		}

		$this->pagination->num_records = $disposition_model->numDispositions($title);
		$records = $this->pagination->num_records > 0 ?
			$disposition_model->getDispositions($title, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
		$this->pagination->num_current_records = is_array($records) ? count($records) : 0;

		$responce = $this->getTableResponse();
		$responce->records = $this->pagination->num_records;
		$result = &$records;

		if(count($result) > 0){
			foreach ( $result as &$data ) {
				$data->campaign_id_txt = "";
				$data->title_txt = "";

				if ($data->campaign_id == '0000') $data->campaign_id_txt = 'System';
				elseif ($data->campaign_id == '1111') $data->campaign_id_txt = 'General';
				else $data->campaign_id_txt = $data->campaign_title;

				if ($data->campaign_id == '0000'){
					$data->title_txt = $data->title;
				}else{
					$data->title_txt = '<a href="'.$this->url("task=disposition&act=update&sid=".$data->disposition_id).'">'.$data->title.'</a>';
				}

				$data->action = "";
				//if ($data->campaign_id == '0000'){
					$data->action .= "<a class='ConfirmAjaxWR btn btn-danger btn-xs' href='" . $this->url("task=confirm-response&act=campaigndisp&pro=delete&dcode=".$data->disposition_id)."' ";
					$data->action .= "msg='Are you sure to delete disposition code ".$data->disposition_id ."?' style='padding: 0 5px;'><i class='fa fa-times'></i></a>";
				//}
			}
		}

		$responce->rowdata = $result;
		$this->ShowTableResponse();
	}
	
	function actionCrminDetails(){
	    include('model/MCrmIn.php');
	    include('model/MSkillCrmTemplate.php');
	    $crm_dp_model = new MSkillCrmTemplate();
	    $crm_model = new MCrmIn();
	    
	    $account_id = "";
	    $first_name = "";
	    $last_name = "";
	    $mobile_no = "";
	    $home_phone = "";
	    $emailAddr = "";
	    $office_phone = "";
	    $status = "";
	     
	    if ($this->gridRequest->isMultisearch){
	        $account_id = $this->gridRequest->getMultiParam('account_id');
	        $first_name = $this->gridRequest->getMultiParam('first_name');
	        $last_name = $this->gridRequest->getMultiParam('last_name');
	        $mobile_no = $this->gridRequest->getMultiParam('mobile_phone');
            $home_phone = $this->gridRequest->getMultiParam('home_phone');
            $emailAddr = $this->gridRequest->getMultiParam('email');
            $office_phone = $this->gridRequest->getMultiParam('office_phone');
            $status = $this->gridRequest->getMultiParam('status');
	    }
		$sessObject = new stdClass();
		$sessObject->account_id = $account_id;
		$sessObject->first_name = $first_name;
		$sessObject->last_name = $last_name;
		$sessObject->mobile_no = $mobile_no;

        $sessObject->home_phone = $home_phone;
        $sessObject->email = $emailAddr;
        $sessObject->office_phone = $office_phone;
        $sessObject->status = $status;
		UserAuth::setCDRSearchParams($sessObject);
	     
	    $dp_options = $crm_dp_model->getDispositionSelectOptions();
	    $this->pagination->num_records = $crm_model->numCrmSkillRecords($account_id, $first_name, $last_name, $mobile_no, $home_phone, $emailAddr, $office_phone, $status);
	     
	    $records = $this->pagination->num_records > 0 ?
	    $crm_model->getCrmSkillRecords($account_id, $first_name, $last_name, $mobile_no, $home_phone, $emailAddr, $office_phone, $status, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
	    $this->pagination->num_current_records = is_array($records) ? count($records) : 0;
	     
	    $responce = $this->getTableResponse();
	    $responce->records = $this->pagination->num_records;
	     
	    $result=&$records;
	    if(count($result) > 0){
	        foreach ( $result as &$data ) {
	            
	            if ($data->status == 'I') {
	                $data->statusTxt = "<a class='ConfirmAjaxWR' msg='Are you sure to set status active of account " . $data->account_id . "?' ";
	                $data->statusTxt .= "href='" . $this->url("task=confirm-response&act=crmin-record&pro=activate&status=A&rid=".$data->record_id)."'><span class='text-danger'>Inactive</span></a>";
	            }elseif ($data->status == 'N') {
                    $data->statusTxt = "<a class='ConfirmAjaxWR' msg='Are you sure to set status active of account " . $data->account_id . "?' ";
                    $data->statusTxt .= "href='" . $this->url("task=confirm-response&act=crmin-record&pro=activate&status=A&rid=".$data->record_id)."'><span class='text-danger'>Disabled</span></a>";
                } else {
	                $data->statusTxt = "<a class='ConfirmAjaxWR' msg='Are you sure to set status inactive of account " . $data->account_id . "?' ";
	                $data->statusTxt .= "href='" . $this->url("task=confirm-response&act=crmin-record&pro=activate&status=I&rid=".$data->record_id)."'><span class='text-success'>Active</span></a>";
	            }
	            $data->actUrl = "<a class='btn btn-info btn-xs' title='Edit' href='".$this->url("task=crm_in&act=edit-skill-crm&rid=".$data->record_id)."'><i class='fa fa-pencil-square-o'></i></a> &nbsp; ";
	            $data->actUrl .= "<a class='ConfirmAjaxWR btn btn-danger btn-xs' title='Delete' msg='Are you sure to Delete account " . $data->account_id . "?' ";
	            $data->actUrl .= "href='".$this->url("task=confirm-response&act=crmin-record&pro=delete&isdel=Y&rid=".$data->record_id)."'><i class='fa fa-times'></i></a>";
	            /* 
	            $data->disposition = $data->disposition_id . ' - ';
	            if (!empty($data->disposition_id) && isset($dp_options[$data->disposition_id])){
	                $data->disposition .= $dp_options[$data->disposition_id];
	            }
	            $data->tstamp = empty($data->tstamp) ? '-' : date("Y-m-d H:i:s", $data->tstamp);
	            $data->disposUrl = "<a href='".$this->url("task=crm_in&act=disposition&rid=".$data->record_id)."' data-w='850' data-h='450' class='lightboxWIF'><img width='16' height='16' border='0' class='bottom' src='image/icon/application_view_list.png' alt='History' title='History' /></a>";
	             */
	        }
	    }
	    $responce->rowdata = $result;
	    $this->ShowTableResponse();
	}

	function actionShiftProfile(){
        include('model/MAgent.php');

        $agent_model = new MAgent();
        $overlap = "*";
        if ($this->gridRequest->isMultisearch){
            $overlap = $this->gridRequest->getMultiParam('day_overlap');
        }

        $this->pagination->num_records = $agent_model->numShiftProfile($overlap);
        $shift = $this->pagination->num_records > 0 ?
            $agent_model->getShiftProfile($overlap,$this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        $result=&$shift;
        $temp_data_array = array();
        $already_executed_array = array();
        $show_result = array();
        if(count($result)>0){
            $curLoggedUserRND = UserAuth::getDBSuffix();
            foreach ($result as $key){
                if ($key->day_overlap =="1"){
                    if(!in_array($key->shift_code,$temp_data_array)){
                        $temp_data_array[] = $key->shift_code;
                    }
                }
            }

            foreach ( $result as &$data ) {
                if (  !empty($temp_data_array) && in_array($data->shift_code, $temp_data_array) ) {
                    if (!in_array($data->shift_code,$already_executed_array)){
                        $already_executed_array[] = $data->shift_code;
                        $obj = $this->getOverlapData($result, $data->shift_code);
                        $data->shift_code = "<a href='".$this->url('task=agents&act=update-shift-profile&scode='.$data->shift_code.'&doverlap='.$data->day_overlap)."' > $data->shift_code</a>";
                        $data->start_time = $obj->start_time;
                        $data->end_time = $obj->end_time;
                        $data->early_login_cutoff_time = $obj->early_login_cutoff_time;
                        $data->late_login_cutoff_time = $obj->late_login_cutoff_time;
                        $data->label = $obj->label;
                        $data->shift_duration = !empty($data->shift_duration)?date('H:i:s', mktime(0, 0, $data->shift_duration)):"00:00:00";
                        $data->tardy_cutoff_sec = !empty($data->tardy_cutoff_sec)?date('H:i:s', mktime(0, 0, $data->tardy_cutoff_sec)):"00:00:00";
                        $data->early_leave_cutoff_sec = !empty($data->early_leave_cutoff_sec)?date('H:i:s', mktime(0, 0, $data->early_leave_cutoff_sec)):"00:00:00";
                        $data->day_overlap = "1";
                        $resojb = new stdClass();
                        $resojb = $data;
                        $show_result[] = $resojb;
                    }
                }else {
                    $data->shift_code = "<a href='".$this->url('task=agents&act=update-shift-profile&scode='.$data->shift_code.'&doverlap='.$data->day_overlap)."' > $data->shift_code</a>";
                    $data->shift_duration = !empty($data->shift_duration)?date('H:i:s', mktime(0, 0, $data->shift_duration)):"00:00:00";
                    $data->tardy_cutoff_sec = !empty($data->tardy_cutoff_sec)?date('H:i:s', mktime(0, 0, $data->tardy_cutoff_sec)):"00:00:00";
                    $data->early_leave_cutoff_sec = !empty($data->early_leave_cutoff_sec)?date('H:i:s', mktime(0, 0, $data->early_leave_cutoff_sec)):"00:00:00";
                    $resojb = new stdClass();
                    $resojb = $data;
                    $show_result[] = $resojb;
                }
                //$data->day_overlap = $data->day_overlap==1?"True":"False";
            }
        }
        //GPrint($show_result);die;

        $responce->rowdata = $show_result;
        $this->ShowTableResponse();
    }

    function getOverlapData($data, $shift_code){
        $start_time ="00:00";
        $end_time ="23:59";
        $early_login_cutoff_time ="00:00";
        $late_login_cutoff_time ="23:59";
        $label = "*";
        $obj = new stdClass();
        foreach ($data as $key){
            if ($key->shift_code == $shift_code){
                if (strtotime($start_time) < strtotime($key->start_time)){
                    $start_time = $key->start_time;
                }
                if (strtotime($end_time) > strtotime($key->end_time)){
                    $end_time = $key->end_time;
                }
                if (strtotime($early_login_cutoff_time) < strtotime($key->early_login_cutoff_time)){
                    $early_login_cutoff_time = $key->early_login_cutoff_time;
                }
                if ( ($key->late_login_cutoff_time != "00:00") && (strtotime($late_login_cutoff_time) > strtotime($key->late_login_cutoff_time))){
                    $late_login_cutoff_time = $key->late_login_cutoff_time;
                }
                if ($key->label != "*"){
                    $label = $key->label;
                }
            }
        }
        $obj->start_time = $start_time;
        $obj->end_time = $end_time;
        $obj->early_login_cutoff_time = $early_login_cutoff_time;
        $obj->late_login_cutoff_time = $late_login_cutoff_time;
        $obj->label = $label;
        return $obj;
    }

    function actionRatingProfile(){
        include('model/MAgent.php');
        $agent_model = new MAgent();

        $this->pagination->num_records = $agent_model->numCallQualityProfile();
        $result = $this->pagination->num_records > 0 ?
            $agent_model->getCallQualityProfile($this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        if(count($result)>0){
            foreach ( $result as &$data ) {
                $data->action = "";
                $data->status = "<a class='ConfirmAjaxWR' msg='Do you confirm that you want to " . ($data->status == "Y" ? "Inactivate" : "Activate") . " Call Quality Profile :" . $data->rating_id . "?' href='" . $this->url("task=confirm-response&act=call-quality-profile&pro=update&id={$data->rating_id}&cstatus=" . ($data->status == "Y" ? "N" : "Y")) . "'>" . ($data->status == "Y" ? "<span class='text-success'>Active</span>" : "<span class='text-danger'>Inactive</span>") . "</a>";
                $data->action = "<a data-width='500' class='lightboxWIF' href='".$this->url("task=call-quality&act=update-profile&id={$data->rating_id}")."'><i class='fa fa-edit'></i> Update</a>";
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    function actionPredictiveDialList(){
        include('model/MSkill.php');
        $skill_model = new MSkill();

        if (UserAuth::hasRole('admin')) {
            $skills = $skill_model->getSkills('', '', 0, 100);
        } else {
            $skills = $skill_model->getAllowedSkills(UserAuth::getCurrentUser(), '', 0, 100);
        }

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $result=&$skills;
        if(count($result) > 0){
            $qTypes = array('V'=>'Voice', 'E'=> 'Email', 'C'=>'Chat', 'P' => 'Dialer');
            $routeType = array('R'=>'Round Robin', 'L'=>'Longest Idle', 'A'=>'Random');
            $vLogger = array('Y'=>'Enable', 'M'=>'Manual');
            $skillType = '';
            foreach ( $result as &$data ) {
                $data->action = '';
                if (UserAuth::hasRole('admin')){
                    $data->skillName = "<a href='". $this->url("task=skills&act=update&skillid=".$data->skill_id). "'>".$data->skill_name."</a>";
                }else {
                    $data->skillName = $data->skill_name;
                }
                $skillType = $data->qtype;
                if (isset($qTypes[$data->qtype])) {
                    $data->qtype = $qTypes[$data->qtype];
                }
                $data->popup_url = empty($data->popup_url) ? "No" : "Yes";
                $data->acd_mode = isset($routeType[$data->acd_mode]) ? $routeType[$data->acd_mode] : $data->acd_mode;
                $data->auto_answer = $data->auto_answer == 'Y' ? 'Yes' : 'No';
                $data->record_all_calls = isset($vLogger[$data->record_all_calls]) ? $vLogger[$data->record_all_calls] : "Disable";
                $data->find_last_agent = $data->find_last_agent <= 0 ? 'No' : 'Yes (' . $data->find_last_agent . ')';
                if ($data->active == 'Y') {
                    if (UserAuth::hasRole('admin')) {
                        $data->active = "<a class='ConfirmAjaxWR' msg='Be confirm to do INACTIVE Service: ". $data->skill_name ."' ";
                        $data->active .= "href='" . $this->url("task=confirm-response&act=skills&pro=activate&skillid=".$data->skill_id."&active=N")."'><span class='text-success'>Active</span></a>";
                    } else {
                        $data->active = "<span class='text-success'>Active</span>";
                    }
                } else {
                    if (UserAuth::hasRole('admin')){
                        $data->active = "<a class='ConfirmAjaxWR' msg='Be confirm to do ACTIVE Service: ".$data->skill_name."' ";
                        $data->active .= "href='" . $this->url("task=confirm-response&act=skills&pro=activate&skillid=".$data->skill_id."&active=Y")."'><span class='text-danger'>Inactive</span></a>";
                    }else {
                        $data->active = "<span class='text-danger'>Inactive</span>";
                    }
                }
                $data->agentList = "<a data-w='800' class='lightboxWIF' href='" . $this->url( "task=skills&act=agent&skillid=". $data->skill_id)."'>[view]</a>";
                $data->action .= "<a class='btn btn-success btn-xs' href='" . $this->url( "task=moh-filler&act=config&sid={$data->skill_id}")."'> MOH Filler <i class='fa fa-angle-double-right'></i></a>";
                if ($skillType == "P") {
                    $data->action .= "<a title='Manage Predictive Dial' class='btn btn-purple btn-xs' href='" . $this->url( "task=predictive-dial&act=manage&sid={$data->skill_id}&sname={$data->skill_name}")."'>Manage PD</a>";
                }
                $data->dispList = "";
                if ($skillType == 'E'){
                    $data->dispList = "<a href='" . $this->url( "task=emaildc&sid=". $data->skill_id)."'>[view]</a>";
                }elseif ($skillType == 'C'){
                    $data->dispList = "<a href='" . $this->url( "task=chatdc&sid=". $data->skill_id)."'>[view]</a>";
                }
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }
}
