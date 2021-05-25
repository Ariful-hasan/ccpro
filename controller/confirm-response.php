<?php

class ConfirmResponse extends Controller
{
	/**
	 * @var AjaxConfirmResponse
	 */
	public $_cresponse; 	
	public $pro;
	
	function __construct() {
		parent::__construct();				
		//if(!Request::IsAjax())die("direct access is not allowed");
			
		//default response
		$this->_cresponse=new AjaxConfirmResponse();
		$this->pro=$this->request->getGet("pro");
		if(empty($this->pro)){		
			$this->SetMessage("Parametter Error",false);
			$this->ReturnResponse();
		}
	}	
	function init(){
		$this->SetMessage("Method doesn't exists",false);
		$this->ReturnResponse();
	}
	function ReturnResponse(){
		die(json_encode($this->_cresponse));		
	}
	function SetMessage($msg,$isSuccess=false){
		$this->_cresponse->status=$isSuccess;
		$this->_cresponse->msg=$msg;
	}
	/* start codde here*/
	
	function actionTelebank(){
		//index.php?utype=A&page=1&agentid=" + aid + "&status="+status
		
		if($this->pro=="rp"){			
			$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
			$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
			$utype = isset($_REQUEST['utype']) ? trim($_REQUEST['utype']) : '';
			
			$agent_title = $utype == 'S' ? 'Supervisor' : 'Agent';
			if ($status == 'Y' || $status == 'N') {
				include('model/MAgent.php');
				$agent_model = new MAgent();
				if ($agent_model->updateAgentTBankStatus($agentid, $status, $utype, $agent_title)) {					
					$this->SetMessage('TeleBanking Status Updated Successfully',true);
				} else {
					$this->SetMessage('Failed to Update TeleBanking Status Option',false);					
				}
			}
			
		}
		$this->ReturnResponse();
	}
	
	function actionAgents(){
		if($this->pro=="cs"){	// change status		
			$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
			$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
			$utype = isset($_REQUEST['utype']) ? trim($_REQUEST['utype']) : '';
			
			$agent_title = $utype == 'S' ? 'Supervisor' : 'Agent';
			
			if ($status == 'Y' || $status == 'N') {
				include('model/MAgent.php');
				$agent_model = new MAgent();
				if ($agent_model->updateAgentStatus($agentid, $status, $utype, $agent_title)) {					
					$this->SetMessage('Status Updated Successfully',true);
				} else {
					$this->SetMessage('Failed to Update Status Option', false);
				}
			}
			
		}elseif($this->pro=="da"){ // delete agent
			include('model/MAgent.php');
			$agent_model = new MAgent();
			
			$agentid = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
			$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';
			$utype = isset($_REQUEST['utype']) ? trim($_REQUEST['utype']) : '';
			//$utype = 'A';
			$agent_title = $utype == 'S' ? 'Supervisor' : 'Agent';	
			$user_type = $utype == 'A' ? 'A' : 'S';
			if ($agent_model->deleteAgent($agentid, $utype)) {
				//$file_name = $this->getTemplate()->file_upload_path . 'Agents/' . $agentid . '.png';
				$file_name = 'agents_picture/' . $agentid . '.png';
				if (file_exists($file_name)) {
					unlink($file_name);
				}
				$this->SetMessage($agent_title.' Deleted Successfully',true);
			} else {
				$this->SetMessage('Failed to Delete '.$agent_title, false);
			}
		}
		$this->ReturnResponse();
	}

	function actionExtuser(){
		if($this->pro=="cs"){	// change status		
			$agentid = isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '';
			$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';

			$agent_title = 'External User';

			if ($status == 'A' || $status == 'L') {
				include('model/MReportUser.php');
				$agent_model = new MReportUser();
				if ($agent_model->updateUserStatus($agentid, $status, $agent_title)) {
					$this->SetMessage('Status Updated Successfully',true);
				} else {
					$this->SetMessage('Failed to Update Status Option', false);
				}
			}

		}elseif($this->pro=="da"){ // delete agent
			include('model/MReportUser.php');
			$agent_model = new MReportUser();

			$agentid = isset($_REQUEST['userid']) ? trim($_REQUEST['userid']) : '';
			$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';
			//$utype = isset($_REQUEST['utype']) ? trim($_REQUEST['utype']) : '';
			//$utype = 'A';
			$agent_title = 'External User';
			if ($agent_model->deleteExtUser($agentid)) {
				//$file_name = $this->getTemplate()->file_upload_path . 'Agents/' . $agentid . '.png';
				$file_name = 'agents_picture/' . $agentid . '.png';
				if (file_exists($file_name)) {
					unlink($file_name);
				}
				$this->SetMessage($agent_title.' Deleted Successfully',true);
			} else {
				$this->SetMessage('Failed to Delete '.$agent_title, false);
			}
		}
		$this->ReturnResponse();
	}
	
	function actionCtis()
	{
	    if($this->pro=="priority"){
    	    $ctiid = isset($_REQUEST['ctiid']) ? trim($_REQUEST['ctiid']) : '';
    	    $priority = isset($_REQUEST['priority']) ? trim($_REQUEST['priority']) : '';
    	
    	    if ($priority == 'Y' || $priority == 'N') {
    	        include('model/MCti.php');
    	        $cti_model = new MCti();   	
    	       
    	        if ($cti_model->updateCtiPriorityStatus($ctiid, $priority)) {
    	            $this->SetMessage('Priority Option Updated Successfully', true);
    	        } else {
    	            $this->SetMessage('Failed to Update Priority Option', false);
    	        }
    	    }
	    }
	    $this->ReturnResponse();
	}
	
	function actionCampaign()
	{
	    include('model/MCampaign.php');
	    $campaign_model = new MCampaign();
	    $cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
	    
	    if($this->pro=="activate"){
            	    $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
	
    	            if ($status == 'A' || $status == 'N') {
    	                    if ($campaign_model->updateCampaignStatus($cid, $status)) {
    	                            $this->SetMessage('Status Updated Successfully', true);
                            } else {
    	                            $this->SetMessage('Failed to Update Status Option', false);
                            }
                    }
	    }elseif($this->pro=="delete"){
            	    if (!empty($cid)) {   	    
    	                if ($campaign_model->deleteCampaign($cid)) {
    	                    $this->SetMessage('Campaign Deleted Successfully', true);
            	        } else {
    	                    $this->SetMessage('Failed to Delete Campaign', false);
    	                }
            	    }
	    }elseif($this->pro=="refresh"){
                    if (!empty($cid)) {        
                            if ($campaign_model->refreshCampaign($cid)) {
                                    $this->SetMessage('Campaign lead refreshed successfully', true);
                            } else {
                                    $this->SetMessage('Failed to refresh campaign lead', false);
                            }
                    }
	    }
	    
	    $this->ReturnResponse();
	}

	function actionCampaignlead()
	{
		include('model/MCampaign.php');
		$campaign_model = new MCampaign();

		$lid = isset($_REQUEST['lid']) ? trim($_REQUEST['lid']) : '';
		$nums = isset($_REQUEST['nums']) ? trim($_REQUEST['nums']) : 0;

		if($this->pro=="delete") {
			if (!is_numeric($lid)) {
				$nums_db = $campaign_model->numLeadNumbers($lid);
				if ($nums == $nums_db) {
					if ($campaign_model->deleteLead($lid)) {
						$this->SetMessage('Lead Deleted Successfully', true);
					} else {
						$this->SetMessage('Failed to Delete Lead', false);
					}
				}
			}
		}

		$this->ReturnResponse();
	}

	function actionCampaigndisp()
	{
		include('model/MCrmDisposition.php');
		$dc_model = new MCrmDisposition();

		$dcode = isset($_REQUEST['dcode']) ? trim($_REQUEST['dcode']) : '';
		if($this->pro=="delete" && !empty($dcode)) {
			if ($dc_model->deleteDispositionCode($dcode)) {
				$this->SetMessage('Disposition Code Deleted Successfully', true);
			} else {
				$this->SetMessage('Failed to Delete  Disposition Code', false);
			}
		}
		$this->ReturnResponse();
	}
	
	function actionChatpage()
	{
	    include('model/MChatTemplate.php');
	    $ct_model = new MChatTemplate();
	
	    $pid = isset($_REQUEST['pid']) ? trim(urldecode($_REQUEST['pid'])) : '';
	    $oldchatpage = new stdClass();
    	$chatpage = new stdClass();
	    
	    if($this->pro=="activate"){  
    	    $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';  	    
    	    if ($status == 'Y' || $status == 'N') {
    	        $oldchatpage->page_id = $pid;
    	        $oldchatpage->active = $status == 'Y' ? 'N' : 'Y';
    	        $chatpage->active = $status;
    	        $chatpage->page_id = $pid;
    	        if ($ct_model->updateChatPage($oldchatpage, $chatpage)) {
    	            $this->SetMessage('Status Updated Successfully', true);
    	        } else {
    	            $this->SetMessage('Failed to Update Status', false);
    	        }
    	    }
	    }
	    $this->ReturnResponse();
	}
	
	function actionChatservice()
	{
	    include('model/MChatTemplate.php');
	    $ct_model = new MChatTemplate();
	
	    $page_id = isset($_REQUEST['pid']) ? trim(urldecode($_REQUEST['pid'])) : '';
	    $service_id = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
	    $oldchatpage = new stdClass();
	    $chatpage = new stdClass();
	     
	    if($this->pro=="activate"){
	        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
	        if ($status == 'Y' || $status == 'N') {
	            $oldchatpage->page_id = $page_id;
	            $oldchatpage->service_id = $service_id;
	            $oldchatpage->status = $status == 'Y' ? 'N' : 'Y';	            
	            $chatpage->page_id = $page_id;
	            $chatpage->service_id = $service_id;
	            $chatpage->status = $status;
	            if ($ct_model->updateChatService($oldchatpage, $chatpage)) {
	                $this->SetMessage('Status Updated Successfully', true);
	            } else {
	                $this->SetMessage('Failed to Update Status', false);
	            }
	        }
	    }elseif($this->pro=="delete"){
	        $status = isset($_REQUEST['isdel']) ? trim($_REQUEST['isdel']) : '';
	        if ($status == 'Y') {
	            $oldchatpage->page_id = $page_id;
	            $oldchatpage->service_id = $service_id;
	            $oldchatpage->status = $status == 'Y' ? 'N' : 'Y';	            
	            $chatpage->page_id = $page_id;
	            $chatpage->service_id = $service_id;
	            $chatpage->status = $status;
	            if ($ct_model->deleteChatService($page_id, $service_id)) {
	                $this->SetMessage('Chat Service Deleted Successfully', true);
	            } else {
	                $this->SetMessage('Failed to Delete Chat Service', false);
	            }
	        }
	    }
	    $this->ReturnResponse();
	}
	
	function actionSetttrunk()
	{
	    include('model/MSetting.php');
		$setting_model = new MSetting();
	    $trunkid = isset($_REQUEST['trunkid']) ? trim($_REQUEST['trunkid']) : '';
	    
	    if($this->pro=="activate" && !empty($trunkid)){
    	    $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';	
    	    if ($status == 'A' || $status == 'I') {
    	        if ($setting_model->updateTrunkStatus($trunkid, $status)) {
    	            $this->SetMessage('Status Updated Successfully', true);
    	        } else {
    	            $this->SetMessage('Failed to Update Status Option', false);
    	        }
    	    }
	    }elseif($this->pro=="delete" && !empty($trunkid)){
	        if ($setting_model->deleteTrunk($trunkid)) {
	            $this->SetMessage('Trunk Deleted Successfully', true);
	        } else {
	            $this->SetMessage('Failed to Delete Trunk', false);
	        }
	    }elseif($this->pro=="delcli"){
	        $id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
	        list($trunkid, $skillid, $dialprefix) = explode('_', $id);
	        if ($setting_model->deleteTrunkCLI($trunkid, $skillid, $dialprefix)) {
	            $this->SetMessage('CLI Number Deleted Successfully', true);
	        } else {
	            $this->SetMessage('Failed to Delete CLI Number', false);
	        }
	    }
	    
	    $this->ReturnResponse();
	}
	
	
	function actionSettingsMacro()
	{
	    include('model/MSetting.php');
	    $setting_model = new MSetting();
	    $macro_code = isset($_REQUEST['macro_code']) ? trim($_REQUEST['macro_code']) : '';
	    
	    if($this->pro=="delete" && !empty($macro_code)){
	        if ($setting_model->deleteMacro($macro_code)) {
	            $this->SetMessage('Macro Deleted Successfully', true);
	        } else {
	            $this->SetMessage('Failed to Delete Macro', false);
	        }
	    }elseif($this->pro=="deljob"){
	        $id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
	        list($code, $ctiid) = explode('_', $id);
	        if ($setting_model->deleteMacroJob($code, $ctiid)) {
	            $this->SetMessage('Macro CTI Deleted Successfully', true);
	        } else {
	            $this->SetMessage('Failed to Delete Macro CTI', false);
	        }
	    }
	    
	    $this->ReturnResponse();
	}
	
	function actionSkills()
	{
	    if($this->pro=="activate"){
	        $skillid = isset($_REQUEST['skillid']) ? trim($_REQUEST['skillid']) : '';
	        $status = isset($_REQUEST['active']) ? trim($_REQUEST['active']) : '';
	        $type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
	        
	        if ($status == 'Y' || $status == 'N') {
	            include('model/MSkill.php');
	            $skill_model = new MSkill();
	            if ($skill_model->updateSkillStatus($skillid, $type, $status)) {
	                $this->SetMessage('Status Updated Successfully', true);
	            } else {
	                $this->SetMessage('Failed to Update Status', false);
	            }
	        }
	    }
	    $this->ReturnResponse();
	}
	function actionSeats()
	{
		$seatid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		
	    if($this->pro=="cs"){	        
	        $seatid = isset($_REQUEST['seatid']) ? trim($_REQUEST['seatid']) : '';
	        $status = isset($_REQUEST['active']) ? trim($_REQUEST['active']) : '';
	        
	        if ($status == 'Y' || $status == 'N') {
	            include('model/MSetting.php');
	            $setting_model = new MSetting();	        
	            if ($setting_model->updateSeatStatus($seatid, $status)) {
	                $this->SetMessage('Status Updated Successfully', true);	               
	            } else {
	                $this->SetMessage('Failed to Update Status', false);
	            }
	        }	        
	     
	    } elseif($this->pro=="coc"){
	        $seatid = isset($_REQUEST['seatid']) ? trim($_REQUEST['seatid']) : '';
                $status = isset($_REQUEST['active']) ? trim($_REQUEST['active']) : '';
                
                if ($status == 'Y' || $status == 'N') {
                        include('model/MSetting.php');
                        $setting_model = new MSetting();
                        if ($setting_model->updateSeatOutgoingControl($seatid, $status)) {
                                $this->SetMessage('Outgoing Control Updated Successfully', true);
                        } else {
                                $this->SetMessage('Failed to Update Outgoing Control', false);
                        }
                }
	    } elseif($this->pro=="da"){
	        $seatid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
	        
	        include('model/MSetting.php');
	        $setting_model = new MSetting();
	        
	        $oldseat = empty($seatid) ? null : MSetting::getInitialSeat($seatid, $setting_model);
	        
	        if (!empty($oldseat)) {
	            $seat=new stdClass();
	            $seat->seat_id = $oldseat->seat_id;
	            $seat->label = $oldseat->label;	           
	            $seat->device_mac = '';
	            $seat->device_ip = '';
	            $seat->device_type = '';
	            $seat->active = 'N';
	            $seatActivationCodeDeleted=false;
	            if($setting_model->DeleteActivationCodeBySeatId($oldseat->seat_id)){
	            	$seatActivationCodeDeleted=true;
	            }
	            if ($setting_model->updateSeat($oldseat, $seat) || $seatActivationCodeDeleted) {	            	           	
	                $this->SetMessage('Seat Updated Successfully', true);
	            } else { 
	                $this->SetMessage('Failed to Update Seat', false);
	            }
	        }
	    }elseif($this->pro=="ga"){ //generate activation code	       	        
	       include('model/MSetting.php');
	       $setting_model = new MSetting(); 
           $response=$setting_model->GenerateActivationCode();           
           //if($response->tried>0 &&  $response->successfullCounter>0){
                $this->SetMessage('Total ('.$response->tried.') Generated ('.$response->successfullCounter.') failed ('.$response->faildCounter.')', true);
            /*} else { 
                $this->SetMessage('Failed to generate activation code', false);
            }	*/        
	    } elseif($this->pro=="rb") {
	    	include('model/MSetting.php');
	    	$setting_model = new MSetting();
	    	if ($setting_model->sip_notify($seatid, 'reboot')) {
		    	$this->SetMessage('Seat rebooted successfully', true);
	    	} else {
	    		$this->SetMessage('Failed to reboot seat', false);
	    	}
	    } elseif($this->pro=="rs") {
	    	include('model/MSetting.php');
		$setting_model = new MSetting();
		if ($setting_model->sip_notify($seatid, 'resync')) {
			$this->SetMessage('Seat resynced successfully', true);
		} else {
			$this->SetMessage('Failed to resync seat', false);
		}
	    } elseif($this->pro=="lo") {
	            //ini_set('display_errors', '1');
	            //error_reporting(E_ALL);
	            include('lib/DBNotify.php');
	            //include('model/Model.php');
	            $seatid = isset($_REQUEST['seatid']) ? trim($_REQUEST['seatid']) : '';
	            $agent_id = isset($_REQUEST['aid']) ? trim($_REQUEST['aid']) : '';
	            if (DBNotify::NotifyLogout(UserAuth::getDBSuffix(), $seatid, $agent_id, Model::getDB())) {
	                    $this->SetMessage('Agent logged out successfully', true);
	            } else {
	                    $this->SetMessage('Failed to logged out agent', false);
	            }
	    }
	    $this->ReturnResponse();
	}
	
	function actionIvrs()
	{
        if($this->pro=="da"){
	    		include('model/MIvrAPI.php');
        		$ivr_model = new MIvrAPI();
        		$cid = isset($_REQUEST['cid']) ? trim($_REQUEST['cid']) : '';
        		if ($ivr_model->deleteConnection($cid)) {
        		    $this->SetMessage('Connection Deleted Successfully', true);
        		} else {
        		    $this->SetMessage('Failed to Delete Connection', false);
        		}
	    }
	    $this->ReturnResponse();
	}
	
	function actionVm()
	{
		$msg = '';
		$is_success = false;
		if ($this->pro=="st") {
			$st = $this->request->getGet("st");
			$ts = $this->request->getGet("ts");
			$cid = $this->request->getGet("cid");
			if ($st == 'S') {
				include('model/MAgentReport.php');
				$report_model = new MAgentReport();
                                $agentid = UserAuth::getCurrentUser();				
                                if ($report_model->updateVoiceLogStatus($cid, $ts, 'S', $agentid)) {
					$report_model->addToAuditLog('Voice Mail', "U", "", "Served voice mail");
					$msg = 'Successfully marked the voice mail as served';
					$is_success = true;
				}
			}
		}
		if (empty($msg)) {
			$msg = 'Failed to mark the voice mail as served';
		}
		$this->SetMessage($msg, $is_success);
		$this->ReturnResponse();
	}
	
	function actionBusymsg(){
	    if($this->pro=="cs"){
	        $bid = isset($_REQUEST['bid']) ? trim($_REQUEST['bid']) : '';
	        $status = isset($_REQUEST['active']) ? trim($_REQUEST['active']) : '';	    
	        
	        if ($status == 'Y' || $status == 'N' && $bid<20) {
	            include('model/MSetting.php');
	            $setting_model = new MSetting();	           
	            if ($setting_model->updateBusyMessageStatus($bid, $status)) {
	                $this->SetMessage('Status Updated Successfully', true);	               
	            } else { 
	                $this->SetMessage('Failed to Update Status', false);  
	            }
	        }
	    }
	    $this->ReturnResponse();
	}
	
	function actionWboard()
	{
        if($this->pro=="da"){
            include('model/MWallboard.php');
    		$wb_model = new MWallboard();
    
    		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
    		if ($wb_model->deleteScrollText($tid)) {
    		    $this->SetMessage('Scroll Text Deleted Successfully', true);
    		} else {
    		    $this->SetMessage('Failed to Delete Scroll Text', false);
    		}
	    }
	    $this->ReturnResponse();
	}
	
	
	function actionWboardImgDel()
	{
	    if($this->pro=="da"){
    	    $nid = isset($_REQUEST['nid']) ? trim($_REQUEST['nid']) : '';
    	    $file_name = 'wallboard/notice/' . $nid . '.png';
    	
    	    $is_update = false;
    	
    	    if (file_exists($file_name)) {
    	        if (unlink($file_name)) $is_update = true;
    	    }
    	
    	    if ($is_update) {
    	        $this->SetMessage('Notice image removed successfully', true);
    	    } else {
    	        $this->SetMessage('Failed to remove notice image', false);
    	    }
	    }
	    $this->ReturnResponse();
	}
	
	function actionWboardNoticeDel()
	{
	    if($this->pro=="da"){
    	    include('model/MWallboard.php');
    		$wb_model = new MWallboard();
    		$nid = isset($_REQUEST['nid']) ? trim($_REQUEST['nid']) : '';
    		
    		if ($wb_model->deleteNotice($nid)) {
    			$file_name = 'wallboard/notice/' . $nid . '.png';
    			if (file_exists($file_name)) {
    				unlink($file_name);
    			}
    			$this->SetMessage('Notice Deleted Successfully', true);
    		} else {
    		    $this->SetMessage('Failed to Delete Notice', false);
    		}
	    }
	    $this->ReturnResponse();
	}

	function actionWboardVideoDel()
	{
	    if($this->pro=="da"){
    	    include('model/MWallboard.php');
    		$wb_model = new MWallboard();
    
    		$vid = isset($_REQUEST['vid']) ? trim($_REQUEST['vid']) : '';
    		if ($wb_model->deleteVideo($vid)) {
    			
    			$wbfiles = glob('wallboard/video/' . 'a' . $vid . '.{' . implode(",", $this->supported_video_types) . '}', GLOB_BRACE);
    			if (!empty($wbfiles)) {
    				foreach ($wbfiles as $wbfile) {
    					unlink($wbfile);
    				}
    			}
    			$this->SetMessage('Video Deleted Successfully', true);
    		} else {
    		    $this->SetMessage('Failed to Delete Video', false);
    		}
	    }
	    $this->ReturnResponse();
	}
	
	function actionDeltedy()
	{
	    include('model/MSkillCrmTemplate.php');
	    $template_model = new MSkillCrmTemplate();
	
	    $tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
	    $cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';
	
	    $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&page=".$cur_page);
	
	    if ($template_model->deleteTemplate($tid)) {
	        $this->getTemplate()->display('msg', array('pageTitle'=>'Delete Template', 'isError'=>false, 'msg'=>'Template Deleted Successfully', 'redirectUri'=>$url));
	    } else {
	        $this->getTemplate()->display('msg', array('pageTitle'=>'Delete Template', 'isError'=>true, 'msg'=>'Failed to Delete Template', 'redirectUri'=>$url));
	    }
	}
	
	function actionTempDel()
	{
	    if($this->pro=="da"){
    	    include('model/MSkillCrmTemplate.php');
    	    $template_model = new MSkillCrmTemplate();
    	    $tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
    	
    	    if ($template_model->deleteTemplate($tid)) {
    	        $this->SetMessage('Template Deleted Successfully', true);
    	    } else {
    	        $this->SetMessage('Failed to Delete Template', false);
    	    }
	    }
	    $this->ReturnResponse();
	}
	
	function actionDelCode()
	{
	    if($this->pro=="da"){
    	    include('model/MSkillCrmTemplate.php');
    		$dc_model = new MSkillCrmTemplate();
    
    		$dcode = isset($_REQUEST['dcode']) ? trim($_REQUEST['dcode']) : '';
    		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
    		
    		if ($dc_model->deleteDispositionId($dcode, $tid)) {
    		    $this->SetMessage('Disposition Code Deleted Successfully', true);
    		} else {
    		    $this->SetMessage('Failed to Delete  Disposition Code', false);
    		}
	    }
	    $this->ReturnResponse();
	}
	
	function actionDelConf()
	{
	    if($this->pro=="da"){
    	    include('model/MConference.php');
    		$conf_model = new MConference();
    
    		$confid = isset($_REQUEST['confid']) ? trim($_REQUEST['confid']) : '';
    
    		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
    		
    		if ($conf_model->deleteConference($confid)) {
    		    $this->SetMessage('Conference Deleted Successfully', true);
    		} else {
    		    $this->SetMessage('Failed to Delete Conference', false);
    		}
	    }
	    $this->ReturnResponse();
	}
	
	function actionUnlock()
	{
 	    if($this->pro=="da"){
 	        include('model/MAgent.php');
 	        $agent_model = new MAgent();
     	    $isUnlock = isset($_REQUEST['unlock']) ? trim($_REQUEST['unlock']) : '';
    		$agentId = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
    		$agentIP = isset($_REQUEST['aip']) ? trim($_REQUEST['aip']) : '';
    		
    		if (!empty($agentId) && !empty($agentIP) && $isUnlock == "yes"){
    			if($agent_model->clearMisslogin2Unlock($agentId, $agentIP)){
    				$agent_model->addToAuditLog('Misslogin', 'U', $agentId, "Account unblocked");
    				$url = $this->getTemplate()->url("task=password&act=misslogin");
    				$this->SetMessage('Account Unblocked Successfully', true);
    			} else {
    			    $this->SetMessage('Failed to Unblock Account', false);
    			}
    		}
	    }
	    $this->ReturnResponse(); 
	}
	
	function actionEmailTempDel()
	{
	    if($this->pro=="del"){
    	    include('model/MEmailTemplate.php');
    	    $et_model = new MEmailTemplate();
    	
    	    $tstamp = isset($_REQUEST['tstamp']) ? trim($_REQUEST['tstamp']) : '';
    
    	    if ($et_model->deleteTemplate($tstamp)) {
    	        $this->SetMessage('Template Deleted Successfully', true);
    	    } else {
    	        $this->SetMessage('Failed to Delete Template', false);
    	    }
	    }
	    $this->ReturnResponse();
	}

	function actionSmsTempDel()
	{
		if($this->pro=="del"){
			include('model/MEmailTemplate.php');
			$et_model = new MEmailTemplate();

			$tstamp = isset($_REQUEST['tstamp']) ? trim($_REQUEST['tstamp']) : '';

			if ($et_model->deleteSmsTemplate($tstamp)) {
				$this->SetMessage('Template Deleted Successfully', true);
			} else {
				$this->SetMessage('Failed to Delete Template', false);
			}
		}
		$this->ReturnResponse();
	}
	
	function actionChatTempDel()
	{
	    if($this->pro=="del"){
    	    include('model/MChatTemplate.php');
    	    $et_model = new MChatTemplate();
    	
    	    $tstamp = isset($_REQUEST['tstamp']) ? trim($_REQUEST['tstamp']) : '';
    
    	    if ($et_model->deleteTemplate($tstamp)) {
    	        $this->SetMessage('Template Deleted Successfully', true);
    	    } else {
    	        $this->SetMessage('Failed to Delete Template', false);
    	    }
	    }
	    $this->ReturnResponse();
	}
	
	function actionEmailDsDel()
	{
	    if($this->pro=="del"){
    	    include('model/MEmail.php');
    	    $dc_model = new MEmail();
    	
    	    $dcode = isset($_REQUEST['dcode']) ? trim($_REQUEST['dcode']) : '';
    	    $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';

    	    if ($dc_model->deleteDispositionId($dcode, $sid)) {
    	        $this->SetMessage('Disposition Code Deleted Successfully', true);
    	    } else {
    	        $this->SetMessage('Failed to Delete  Disposition Code', false);
    	    }
	    }
	    $this->ReturnResponse();
	}
	
	function actionChatDsDel()
	{
	    if($this->pro=="del"){
    	    include('model/MChatTemplate.php');
    	    $dc_model = new MChatTemplate();
    	
    	    $dcode = isset($_REQUEST['dcode']) ? trim($_REQUEST['dcode']) : '';
    	    $sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';

    	    if ($dc_model->deleteDispositionId($dcode, $sid)) {
    	        $this->SetMessage('Disposition Code Deleted Successfully', true);
    	    } else {
    	        $this->SetMessage('Failed to Delete  Disposition Code', false);
    	    }
	    }
	    $this->ReturnResponse();
	}
	
	function actionEmailAllowedDel()
	{
	    if($this->pro=="del"){
    	    include('model/MEmail.php');
    		$email_model = new MEmail();    
    		$eid = isset($_REQUEST['eid']) ? trim($_REQUEST['eid']) : '';

    		if ($email_model->deleteAllowedEmail($eid)) {
    		    $this->SetMessage('CC/BCC Email Deleted Successfully', true);
    		} else {
    		    $this->SetMessage('Failed to Delete CC/BCC Email', false);
    		}
	    }
	    $this->ReturnResponse();
	}
	function actionLang()
	{
		if($this->pro=="cs"){
			$key =$this->request->getRequest('key');	
			$status =$this->request->getRequest('status');
			include('model/MLanguage.php');
			$lang_model = new MLanguage();
			if (!empty($key) && !empty($status)) {				
				if ($lang_model->updateStatusLanguage($key,$status)) {
					$this->SetMessage('Language status updated successfully', true);
				} else {
					$this->SetMessage('Failed to update language status', false);
				}
			}
			
	
		} elseif($this->pro=="da"){
			$key =$this->request->getRequest('key');			 
			include('model/MLanguage.php');
			$lang_model = new MLanguage();
			if (!empty($key)) {				
				if ($lang_model->deleteLanguage($key)) {
					$this->SetMessage('Language Deleted Successfully', true);
				} else {
					$this->SetMessage('Failed to Delete Language', false);
				}
			}
		}
		$this->ReturnResponse();
	}
	function actionMohFiller()
	{
		if($this->pro=="da"){
			AddModel("MMOHFiller");
			$mainObj = new MMOHFiller();	
			$filler_id = $this->getRequest()->getRequest('filler_id',null);
			if($filler_id){
				if ($mainObj->DeleteMOHFillerWithFile($filler_id)) {
					$this->SetMessage('MOH Filler Deleted Successfully', true);
				} else {
					$this->SetMessage('Failed to Delete MOH Filler', false);
				}
			}
		}
		$this->ReturnResponse();
	}
	
	
	function actionDeleteMohFile()
	{ 
		if($this->pro=="da"){		
			$skill_id = $this->getRequest()->getRequest('sid',null);
			$fileid = $this->getRequest()->getRequest('f',null);
			if($skill_id){
				$fileid = $fileid - 1;
				$fileWithOutExt = $this->getTemplate()->file_upload_path . 'Q/' . $skill_id ."/MOH/moh{$fileid}";
				$target_path = $fileWithOutExt .".".$this->getTemplate()->voice_store_file_extension;
				
				if(file_exists($target_path) && unlink($target_path)){		
					unlink($fileWithOutExt.".ulaw");
					unlink($fileWithOutExt.".alaw");
					unlink($fileWithOutExt.".txt"); 

					AddModel("MSkill");
					$skill_obj=new MSkill();
					$skill_obj->notifyVoiceFileUpdate($skill_id, 'moh'.$fileid);
					$this->SetMessage('File deleted successfully', true);
				} else {
					$this->SetMessage('Failed to Delete file', false);
				}
			}
		}
		$this->ReturnResponse();
	}
	
	function actionDeleteCampaignVmFile()
	{
		if($this->pro=="df"){		
			$cid = $this->getRequest()->getRequest('cid',null);
			$fileid = $this->getRequest()->getRequest('f',null);
			if (!empty($cid) && ctype_digit($cid)) {
				$target_path = $this->getTemplate()->file_upload_path . 'PD/' . $cid ."/vm.wav";
				if(file_exists($target_path) && unlink($target_path)){					
					//AddModel("MSkill");
					//$skill_obj=new MSkill();
					//$skill_obj->notifyVoiceFileUpdate($skill_id, 'moh'.$fileid);
					$this->SetMessage('File deleted successfully', true);
				} else {
					$this->SetMessage('Failed to Delete file', false);
				}
			}
		}
		$this->ReturnResponse();
	}
	
	function actionDeleteWelcomeFile()
	{
		if($this->pro=="da"){
			$skill_id = $this->getRequest()->getRequest('sid',null);
			$lang = $this->getRequest()->getRequest('lan',null);
			if($skill_id){
				$fileWithOutExt = $this->getTemplate()->file_upload_path . 'Q/' . $skill_id ."/{$lang}/welcome";
				$target_path = $fileWithOutExt.".".$this->getTemplate()->voice_store_file_extension;
				if(file_exists($target_path) && unlink($target_path)){
					unlink($fileWithOutExt.".ulaw");
					unlink($fileWithOutExt.".alaw");
					unlink($fileWithOutExt.".txt");
					
					AddModel("MSkill");
					$skill_obj=new MSkill();
					$skill_obj->notifyVoiceFileUpdate($skill_id, 'welcome');
				        
					$this->SetMessage('File deleted successfully', true);
				} else {
					$this->SetMessage('Failed to Delete file', false);
				}
			}
		}
		$this->ReturnResponse();
	}
	function actionDeleteWelcomeVoiceFile()
	{
		if($this->pro=="da"){
			$skill_id = $this->getRequest()->getRequest('sid',null);
			$lang = $this->getRequest()->getRequest('lan',null);
			if($skill_id){
				$fileWithOutExt = $this->getTemplate()->file_upload_path . 'Q/' . $skill_id ."/welcome";
				$target_path = $fileWithOutExt.".".$this->getTemplate()->voice_store_file_extension;
				if(file_exists($target_path) && unlink($target_path)){
					unlink($fileWithOutExt.".ulaw");
					unlink($fileWithOutExt.".alaw");
					unlink($fileWithOutExt.".txt");
					
					AddModel("MSkill");
					$skill_obj=new MSkill();
					$skill_obj->notifyVoiceFileUpdate($skill_id, 'welcome');
				        
					$this->SetMessage('File deleted successfully', true);
				} else {
					$this->SetMessage('Failed to Delete file', false);
				}
			}
		}
		$this->ReturnResponse();
	}
	function actionDeleteIvrWelcomeFile()
	{
		if($this->pro=="da"){
			$ivrid = $this->getRequest()->getRequest('ivrid',null);
			$lang = $this->getRequest()->getRequest('lan',null);
			$ivrType = $this->getRequest()->getRequest('ivrtype',null);
			if($ivrid){
				$target = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrid ."/welcome";
				if($ivrType && $ivrType == "skill"){
					$target = $this->getTemplate()->file_upload_path . 'Q/' . $ivrid ."/welcome";
				}
				$target_path = $target.".".$this->getTemplate()->voice_store_file_extension;
				if(file_exists($target_path)){
					unlink($target_path);
					unlink($target.".alaw");
					unlink($target.".ulaw");
					unlink($target.".txt");
					
					AddModel("MMOHFiller");
					$mh_obj=new MMOHFiller();
					$mh_obj->notifyIVRVoiceFileUpdate($ivrid, 'welcome');
					$this->SetMessage('File deleted successfully', true);
				} else {
					$this->SetMessage('Failed to Delete file', false);
				}
			}
		}
		$this->ReturnResponse();
	}
	function actionDeleteIvrFile()
	{
		///Projects/gplex-cloudcc/index.php?task=confirm-response&act=delete-ivr-file&pro=da&lan=BN&branch=A1..
		if($this->pro=="da"){
			$branch = $this->getRequest()->getRequest('branch',null);
			$lang = $this->getRequest()->getRequest('lan',null);
			$fileKeyName = "";
			$fileid = $this->getRequest()->getRequest('fileid',null);
			
			if($branch){				
				AddModel("MIvr");
				$ivrmodel=new MIvr();
				$ivrbranch=$ivrmodel->getIVRDetailsByBranch($branch);	
				$eventKey = $ivrbranch->event_key;
				
				if($fileid){
					$eventKey = $fileid;
				}
				
				$fileWithOutExt = 	$this->getTemplate()->file_upload_path . 'IVR/' . $ivrbranch->ivr_id ."/$lang/".$eventKey;		
				$target_path = $fileWithOutExt.".".$this->getTemplate()->voice_store_file_extension;
				$ttsFileWithOutExt = 	$this->getTemplate()->file_upload_path . 'IVR/' . $ivrbranch->ivr_id ."/$lang/"."tts_".$ivrbranch->event_key;
				
				if($ivrbranch && !empty($eventKey) && file_exists($target_path) && unlink($target_path)){
					unlink($fileWithOutExt.".ulaw");
					unlink($fileWithOutExt.".alaw");
					unlink($fileWithOutExt.".txt");
					unlink($ttsFileWithOutExt.".txt");
					
					$moreFileExist = false;
					AddModel("MLanguage");
					$languages=MLanguage::getActiveLanguageList(); 
					foreach ($languages as &$language){
						$target_path = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrbranch->ivr_id ."/$language->lang_key/".$ivrbranch->event_key.".".$this->getTemplate()->voice_store_file_extension;
						if (file_exists($target_path)) { 
							$moreFileExist = true;
							break;
						}
					}
					if ($fileid) {
						$enfilePath = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrbranch->ivr_id ."/EN/".$eventKey.".".$this->getTemplate()->voice_store_file_extension;		
						$bnfilePath = $this->getTemplate()->file_upload_path . 'IVR/' . $ivrbranch->ivr_id ."/BN/".$eventKey.".".$this->getTemplate()->voice_store_file_extension;		
						if( !file_exists($enfilePath) && !file_exists($bnfilePath) ){
							$ivrmodel->clearBranchMultiFile($branch, $ivrbranch->event_key, $eventKey, $ivrbranch->arg2);
						}
						
					}
					if (!$moreFileExist) {
						$ivrmodel->clearBranchFile($branch, $ivrbranch->event_key);
					}
					
					
					AddModel("MMOHFiller");
	                $mh_obj=new MMOHFiller();
	                $mh_obj->notifyIVRVoiceFileUpdate($ivrbranch->ivr_id, '');
					$this->SetMessage('File deleted successfully', true);
				} else {
					$this->SetMessage('Failed to Delete file', false);
				}
				
			}
		}
		$this->ReturnResponse();
	}

	function actionDeleteIvrAsrFile()
	{
		if($this->pro=="da"){
			$branch = $this->getRequest()->getRequest('branch',null);
			$lang = $this->getRequest()->getRequest('lan',null);
			
			if($branch){				
				AddModel("MIvr");
				$ivrmodel=new MIvr();
				$ivrbranch = $ivrmodel->getIVRDetailsByBranch($branch);	
				$eventKey = $ivrbranch->event_key;

				$fileWithOutExt = 	$this->getTemplate()->file_upload_path . 'IVR/' . $ivrbranch->ivr_id ."/ASR/$lang/".$eventKey;		
				$target_path = $fileWithOutExt.".".$this->getTemplate()->voice_store_file_extension;
				
				if($ivrbranch && !empty($eventKey) && file_exists($target_path) && unlink($target_path)){
					unlink($fileWithOutExt.".ulaw");
					unlink($fileWithOutExt.".alaw");
					unlink($fileWithOutExt.".txt");
					$this->SetMessage('ASR File deleted successfully', true);
				} else {
					$this->SetMessage('Failed to Delete ASR file', false);
				}
				
			}
		}
		$this->ReturnResponse();
	}
	function actionDeleteCivrFile()
	{
		if($this->pro=="da"){
			$branch = $this->getRequest()->getRequest('branch',null);
			$lang = $this->getRequest()->getRequest('lan',null);
			
			if($branch){				
				AddModel("MIvr");
				$ivrmodel=new MIvr();
				$ivrbranch = $ivrmodel->getIVRDetailsByBranch($branch);	
				$eventKey = $ivrbranch->event_key;

				$fileWithOutExt = 	$this->getTemplate()->file_upload_path . 'IVR/' . $ivrbranch->ivr_id ."/CIVR/$lang/".$eventKey;		
				$target_path = $fileWithOutExt.".".$this->getTemplate()->voice_store_file_extension;
				
				if($ivrbranch && !empty($eventKey) && file_exists($target_path) && unlink($target_path)){
					unlink($fileWithOutExt.".ulaw");
					unlink($fileWithOutExt.".alaw");
					unlink($fileWithOutExt.".txt");
					$this->SetMessage('CIVR File deleted successfully', true);
				} else {
					$this->SetMessage('Failed to Delete CIVR file', false);
				}
				
			}
		}
		$this->ReturnResponse();
	}
	/**
	 * MOH FIller data
	 */
	function actionDeleteMohfData()
	{ 
		///Projects/gplex-cloudcc/index.php?task=confirm-response&act=delete-ivr-file&pro=da&lan=BN&branch=A1..
		if($this->pro=="da"){
			$branch = $this->getRequest()->getRequest('branch',null);
			$lang = $this->getRequest()->getRequest('lan',null);
			if($branch){
				AddModel("MMOHFiller");
				$moh_model=new MMOHFiller();
				$mohdata=$moh_model->GetMOHFillerByBranch($branch);		
				$fileWithOutExt = $this->getTemplate()->file_upload_path.'Q/' . $mohdata->skill_id."/FILLER/"."$lang/".$mohdata->event_key;					
				$target_path = $fileWithOutExt.".".$this->getTemplate()->voice_store_file_extension;		

				if($mohdata && !empty($mohdata->event_key) && file_exists($target_path) && unlink($target_path)){
					unlink($fileWithOutExt.".ulaw");
					unlink($fileWithOutExt.".alaw");
					unlink($fileWithOutExt.".txt");
					$this->SetMessage('File deleted successfully', true);
				} else {
					$this->SetMessage('Failed to Delete file', false);
				}
	
			}
		}elseif($this->pro=="dn"){ //delete node
			$branch = $this->getRequest()->getRequest('branch',null);			
			if($branch){
				AddModel("MMOHFiller");
				$moh_model=new MMOHFiller();
				if($moh_model->DeleteMOHFillerWithFile($branch,$this->getTemplate()->file_upload_path)){
					$this->SetMessage('Deleted successfully', true);
				} else {
					$this->SetMessage('Failed to Delete', false);
				}
			
			}
		}
		$this->ReturnResponse();
	}
	function actionHotkey()
	{
		if($this->pro=="cs"){
			$key =$this->request->getRequest('key');
			$status =$this->request->getRequest('status');
			AddModel("MAgentHotKey");
			$lang_model = new MAgentHotKey();
			if (!empty($key) && !empty($status)) {
				if ($lang_model->updateStatus($key,$status)) {
					$this->SetMessage('Language status updated successfully', true);
				} else {
					$this->SetMessage('Failed to update language status', false);
				}
			}
				
	
		}
		$this->ReturnResponse();
	}
	
	function actionIvrdcDel()
	{
	    if($this->pro == "del"){
	        include('model/MIvrService.php');
	        $ivr_model = new MIvrService();	        
	        $dcode = isset($_REQUEST['dcode']) ? trim($_REQUEST['dcode']) : '';

	        if ($ivr_model->deleteDispositionCode($dcode)) {
	            $this->SetMessage('Disposition Code Deleted Successfully', true);
	        } else {
	            $this->SetMessage('Failed to Delete  Disposition Code', false);
	        }
	    }
	    $this->ReturnResponse();
	}

	function actionCrminRecord()
	{
	    include('model/MCrmIn.php');
	    $crm_model = new MCrmIn();	
	    $record_id = isset($_REQUEST['rid']) ? trim($_REQUEST['rid']) : '';
	
	    if($this->pro=="activate"){
	        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
	        if ($status == 'Y' || $status == 'N') {
	            $dbColmnVals = array();
	            $dbColmnVals['status'] = $status;
	            
	            if ($crm_model->addEditSkillCrm($dbColmnVals, $record_id)) {
	                $this->SetMessage('Status Updated Successfully', true);
	            } else {
	                $this->SetMessage('Failed to Update Status', false);
	            }
	        }
	    }elseif($this->pro=="delete"){
	        $status = isset($_REQUEST['isdel']) ? trim($_REQUEST['isdel']) : '';
	        if ($status == 'Y') {
	            if ($crm_model->deleteSkillCrm($record_id)) {
	                $this->SetMessage('CRM Record Deleted Successfully', true);
	            } else {
	                $this->SetMessage('Failed to Delete CRM Record', false);
	            }
	        }
	    }
	    $this->ReturnResponse();
	}
	
	function actionScrmdg()
	{
	    include('model/MSkillCrmTemplate.php');
		$dc_model = new MSkillCrmTemplate();
	    $templateId = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$serviceId = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
	
	    if($this->pro=="activate"){
	        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
	        if ($status == 'Y' || $status == 'N') {
	            $oldservice = new stdClass();
	            $service = new stdClass();
	            $oldservice->service_type_id = $serviceId;
	            $oldservice->template_id = $templateId;
	            $oldservice->status_ticketing = $status=='Y' ? 'N' : 'Y';
	            $service->status_ticketing = $status;
	            
	            if ($dc_model->updateDispositionGroup($oldservice, $service, true)) {
	                $this->SetMessage('Ticketing Status Updated Successfully', true);
	            } else {
	                $this->SetMessage('Failed to Update Ticketing Status', false);
	            }
	        }
	    }elseif($this->pro=="delete"){
	        if ($dc_model->deleteDispositionGroup($templateId, $serviceId)) {
                $this->SetMessage('Service Type Deleted Successfully', true);
            } else {
                $this->SetMessage('Failed to Delete Service Type', false);
            }
	    }
	    $this->ReturnResponse();
	}

	function actionBlfsettings()
	{
		include('model/MBlf_key.php');
		$blf_model = new MBlf_key();
		 
		$seat = isset($_REQUEST['seat']) ? trim($_REQUEST['seat']) : '';
		$unit = isset($_REQUEST['unit']) ? trim($_REQUEST['unit']) : '';
		$bkey = isset($_REQUEST['bkey']) ? trim($_REQUEST['bkey']) : '';
	
		if($this->pro=="delete"){
			if ($blf_model->deleteBlfSettings($seat, $unit, $bkey)) {
				$this->SetMessage('BLF Settings Deleted Successfully', true);
			} else {
				$this->SetMessage('Failed to Delete BLF Settings', false);
			}
		}
		$this->ReturnResponse();
	}
	
	function actionIvrTransfer()
	{
		if($this->pro=="delete"){
			include('model/MIvr.php');
			$ivr_model = new MIvr();

			$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
			if ($ivr_model->deleteTransferToIVR($tid)) {
				$this->SetMessage('Transfer to IVR Deleted Successfully', true);
			} else {
				$this->SetMessage('Failed to Delete Transfer to IVR', false);
			}
		}
		$this->ReturnResponse();
	}

	function actionCallQualityProfile()
	{
	    if (empty($this->pro) || $this->pro != 'update')
        {
            $this->SetMessage('Failed to Update Call Quality Profile Status', false);
            $this->ReturnResponse();
        }

        include('model/MAgent.php');
        $agent_model = new MAgent();

        $id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
        $change_status = isset($_REQUEST['cstatus']) ? trim($_REQUEST['cstatus']) : '';
        if (empty($id) || empty($change_status))
        {
            $this->SetMessage('Failed to Update Call Quality Profile Status', false);
            $this->ReturnResponse();
        }

        if ($agent_model->changeCallQualityProfileStatus($id,$change_status)) {
            $this->SetMessage('Call Quality Profile Status Updated Successfully', true);
            $this->ReturnResponse();
        }
        $this->SetMessage('Failed to Update Call Quality Profile Status', false);
        $this->ReturnResponse();
	}

	function actionTicketCategory(){
        if (empty($this->pro) || $this->pro != 'cs')
        {
            $this->SetMessage('Failed to Update Status', false);
            $this->ReturnResponse();
        }

        //if($this->pro=="cs"){
            if(!in_array(UserAuth::getRoleID(), array("R","S"))){
                $this->SetMessage('You are not authorised', false);
            }else {
                $id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
                $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
                //GPrint($status);die;
                if ($status == 'A' || $status == 'I') {
                    include('model/MEmail.php');
                    $email_model = new MEmail();
                    if ($email_model->updateTicketCategoryStatus($id,$status)) {
                        $this->SetMessage('Status Updated Successfully',true);
                    } else {
                        $this->SetMessage('Failed to Update Status', false);
                    }
                }
            }
        //}
        $this->ReturnResponse();
    }

    function actionskillDomainDel(){
        $domain = isset($_REQUEST['dmn']) ? trim($_REQUEST['dmn']) : '';
        $skill_id = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        if (empty($this->pro) || $this->pro != 'del' || empty($domain) || empty($skill_id)) {
            $this->SetMessage('Failed to Delete', false);
            $this->ReturnResponse();
        }
        $domain = urldecode(base64_decode($domain));
        include('model/MEmail.php');
        if ( MEmail::deleteSkillDomain($skill_id, $domain) ){
            $this->SetMessage('Successfully Deleted',true);
        }else {
            $this->SetMessage('Failed to Delete',false);
        }
        $this->ReturnResponse();
    }


    function actionskillKeywordDel(){
        $keyword = isset($_REQUEST['kwd']) ? trim($_REQUEST['kwd']) : '';
        $skill_id = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
        if (empty($this->pro) || $this->pro != 'del' || empty($keyword) || empty($skill_id)) {
            $this->SetMessage('Failed to Delete', false);
            $this->ReturnResponse();
        }
        $keyword = urldecode(base64_decode($keyword));
        include('model/MEmail.php');
        if ( MEmail::deleteSkillKeyword($skill_id, $keyword) ){
            $this->SetMessage('Successfully Deleted',true);
        }else {
            $this->SetMessage('Failed to Delete',false);
        }
        $this->ReturnResponse();
    }

    function actionDeleteblackwhite()
    {
        $key = $this->request->getRequest('cli');
        $list = $this->request->getRequest('list');

        include('model/MBlackwhite.php');
        $black_white_model = new MBlackwhite();

        if (!empty($key)) {
            if ($list == "black") {
                if ($black_white_model->deleteBlackListData($key)) {
                    $this->SetMessage('Black List Data Deleted Successfully', true);
                } else {
                    $this->SetMessage('Failed to Delete Black List Data', false);
                }
            } else if ($list == "white") {
                if ($black_white_model->deleteWhiteListData($key)) {
                    $this->SetMessage('White List Data Deleted Successfully', true);
                } else {
                    $this->SetMessage('Failed to Delete White List Data', false);
                }
            }
        }
        $this->ReturnResponse();
    }

}