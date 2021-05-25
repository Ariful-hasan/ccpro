<?php
require_once 'BaseTableDataController.php';
class GetToolsData extends BaseTableDataController
{
    public function __construct()
    {
        parent::__construct();
    }


    public function actionSeats()
    {
        include('model/MSetting.php');
        $setting_model = new MSetting();

        $seat_id = "";
        $did = "";
        $label = "";
        if ($this->gridRequest->isMultisearch) {
            $seat_id = $this->gridRequest->getMultiParam('seat_id');
            $did = $this->gridRequest->getMultiParam('did');
            $label = $this->gridRequest->getMultiParam('label');
        }

        $this->pagination->num_records = $setting_model->numSeats('', $did, $seat_id, $label, false);
        $result = $this->pagination->num_records > 0 ?
            $setting_model->getSeats('', $this->pagination->getOffset(), $this->pagination->rows_per_page, $did, $seat_id, $label, false) : null;
        $this->pagination->num_current_records = is_array($result) ? count($result) : 0;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        $deviceTypes=array("D"=>"Device","P"=>"Phone");
        if (count($result)>0) {
            $curLoggedUserRND = UserAuth::getDBSuffix();
            foreach ($result as &$data) {
                $data->device_type=!empty($deviceTypes[$data->device_type])?$deviceTypes[$data->device_type]:$data->device_type;




                //if ($curLoggedUserRND == 'AC') {

                //}

                $st_status = $data->active;

                if (UserAuth::hasRole('admin')) {
                    $data->active="<a class='ConfirmAjaxWR' msg='Are you sure that you want to ".($data->active=="Y"?"Deactivate":"Activate")." seat: ".$data->label."?' href='" . $this->url("task=confirm-response&act=seats&pro=cs&seatid=".$data->seat_id."&active=".($data->active=="Y"?"N":"Y"))."'>".($data->active=="Y"?"<span class='text-success'>Active</span>":"<span class='text-danger'>Inactive</span>")."</a>";

                    if ($data->allow_call_without_login != 'Y') {
                        $data->allow_call_without_login = "<a class='ConfirmAjaxWR' msg='Are you sure that you want to disable outgoing control for seat: ".$data->label."?' href='".$this->url("task=confirm-response&act=seats&pro=coc&seatid=".$data->seat_id."&active=Y")."'>"."<span class='text-success'>Enabled</span>"."</a>";
                    } else {
                        $data->allow_call_without_login = "<a class='ConfirmAjaxWR' msg='Are you sure that you want to enable outgoing control for seat: ".$data->label."?' href='".$this->url("task=confirm-response&act=seats&pro=coc&seatid=".$data->seat_id."&active=N")."'>"."<span class='text-danger'>Disabled</span>"."</a>";
                    }

                    $data->action="<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete seat :".$data->label."?' href='" . $this->url("task=confirm-response&act=seats&pro=da&sid={$data->seat_id}")."'><i class='fa fa-refresh'></i> Reset</a>";
                    if ($data->device_mac != '') {
                        $data->action .= " <a class='ConfirmAjaxWR btn btn-warning btn-xs' msg='Are you sure to reboot seat :".$data->label."?' href='" . $this->url("task=confirm-response&act=seats&pro=rb&sid={$data->seat_id}")."'><i class='fa fa-ban'></i> Reboot</a>";
                    }
                    if ($data->device_mac != '') {
                        $data->action .= " <a class='ConfirmAjaxWR btn btn-info btn-xs' msg='Are you sure to resync seat :".$data->label."?' href='" . $this->url("task=confirm-response&act=seats&pro=rs&sid={$data->seat_id}")."'><i class='fa fa-retweet'></i> Resync</a>";
                    }
                    $data->label = "<a href='".$this->url("task=settseats&act=update&seatid={$data->seat_id}")."'>".$data->label."</a>";
                    $data->blf_url = "<a href='".$this->url("task=blfsettings&seat={$data->seat_id}&unit=1")."'>Settings</a>";
                } else {
                    $data->action = "";
                    if ($data->active=="Y") {
                        $data->active="<span class='text-success'>Active</span>";
                    } else {
                        $data->active="<span class='text-danger'>Inactive</span>";
                    }
                    if ($data->allow_call_without_login != 'Y') {
                        $data->allow_call_without_login = "<span class='text-success'>Enabled</span>";
                    } else {
                        $data->allow_call_without_login = "<span class='text-danger'>Disabled</span>";
                    }
                }

                if (!empty($data->agent_id) && $st_status == 'Y') {
                    //$data->active = "Agent # $data->agent_id";
                    $data->active="<a class='ConfirmAjaxWR' msg='Are you sure that you want to force logout agent # ".$data->agent_id."?' href='" . $this->url("task=confirm-response&act=seats&pro=lo&seatid=".$data->seat_id."&aid=".$data->agent_id) ."'>"."<i class='fa fa-user'></i> $data->agent_id"."</a>";
                }
            }
        }


        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }


    public function actionBmsg()
    {
        include('model/MSetting.php');
        $setting_model = new MSetting();
        $this->pagination->num_records = $setting_model->numBusyMessages();
        $result = $this->pagination->num_records > 0 ?
            $setting_model->getBusyMessages('', $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($result) ? count($result) : 0;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        if (count($result)>0) {
            foreach ($result as &$data) {
                $txt_active = $data->active=="Y"?"<span class='text-success'>Active</span>":"<span class='text-danger'>Inactive</span>";
                $data->active="<a class='ConfirmAjaxWR' msg='Be confirm to do ".($data->active=="Y"?"Inactive":"Active")." message no :".$data->aux_code."?' href='" . $this->url("task=confirm-response&act=busymsg&pro=cs&bid=".$data->aux_code."&active=".($data->active=="Y"?"N":"Y"))."'>".$txt_active."</a>";
                //$data->action="<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete seat :".$data->label."?' href='" . $this->url( "task=confirm-response&act=seats&pro=da&sid={$data->seat_id}")."'><i class='fa fa-times'></i> Delete</a>";
                if (!empty($data->message)) {
                    if ($data->aux_code>11 && $data->aux_code<20) {
                        $data->message = "<a href='".$this->url("task=settbmsg&act=update&bid={$data->aux_code}")."'>".$data->message."</a>";
                    } else {
                        $data->active=$txt_active;
                    }
                } else {
                    $blank = "blank";
                    if ($data->aux_code>11 && $data->aux_code<20) {
                        $data->message = "<a href='".$this->url("task=settbmsg&act=update&bid={$data->aux_code}")."'>".$blank."</a>";
                    } else {
                        $data->message = $blank;
                        $data->active=$txt_active;
                    }
                }

                $atype = '';
                if ($data->aux_type == 'I') {
                    $atype = 'AUX-IN';
                } elseif ($data->aux_type == 'O') {
                    $atype = 'AUX-OUT';
                } elseif ($data->aux_type == 'B') {
                    $atype = 'Break';
                } else {
                    $atype = '-';
                }
                $data->aux_type = $atype;
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }
    public function init()
    {
        include('model/MIvrAPI.php');
        $ivr_model = new MIvrAPI();
        $pagination = new Pagination();
        $pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
        $pagination->num_records = $ivr_model->numConnections();

        $data['connections'] = $pagination->num_records > 0 ?
            $ivr_model->getConnections($pagination->getOffset(), $pagination->rows_per_page) : null;
        $pagination->num_current_records = is_array($data['connections']) ? count($data['connections']) : 0;
        $data['pagination'] = $pagination;

        $data['request'] = $this->getRequest();
        $data['pageTitle'] = 'IVR API Connections';
        $data['side_menu_index'] = 'settings';
        $data['topMenuItems'] = array(array('href'=>'task=ivrapi&act=add', 'img'=>'add.png', 'label'=>'Add New Connection'));
        $data['submit_methods'] = array('A'=>'Array', 'C'=>'Class', 'P'=>'Param', 'J'=>'JSON');
        $this->getTemplate()->display('ivr_apis', $data);
    }
    public function actionIvrApi()
    {
        include('model/MIvrAPI.php');
        $ivr_model = new MIvrAPI();
        $this->pagination->num_records = $ivr_model->numConnections();
        $result = $this->pagination->num_records > 0 ?
            $ivr_model->getConnections('', $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($result) ? count($result) : 0;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        $submit_methods= array('A'=>'Array', 'C'=>'Class', 'P'=>'Param', 'J'=>'JSON', 'X'=>'XML');
        if (count($result)>0) {
            foreach ($result as &$data) {
                $data->action="<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete connection :".$data->conn_name."?' href='" . $this->url("task=confirm-response&act=ivrs&pro=da&cid={$data->conn_name}")."'><i class='fa fa-times'></i> Delete</a>";
                $data->conn_name = "<a href='".$this->url("task=ivrapi&act=update&cid={$data->conn_name}")."'>".$data->conn_name."</a>";
                ;
                $data->submit_method=!empty($submit_methods[$data->submit_method])?$submit_methods[$data->submit_method]:$data->submit_method;
                $data->return_method=!empty($submit_methods[$data->return_method])?$submit_methods[$data->return_method]:$data->return_method;
                $data->active = $data->active == 'Y' ? 'Enabled' : 'Disabled';
                $data->pass_credential = $data->pass_credential == 'Y' ? 'Yes' : 'No';
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionScrollTexts()
    {
        include('model/MWallboard.php');
        include('lib/Pagination.php');
        $wb_model = new MWallboard();
        $pagination = new Pagination();

        $pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
        $pagination->num_records = $wb_model->numScrollTexts();
        $data['scroll_texts'] = $pagination->num_records > 0 ?
            $wb_model->getScrollTexts($pagination->getOffset(), $pagination->rows_per_page) : null;
        $pagination->num_current_records = is_array($data['scroll_texts']) ? count($data['scroll_texts']) : 0;
        $data['pagination'] = $pagination;
        $data['request'] = $this->getRequest();
        $data['pageTitle'] = 'Wallboard Scroll Texts';
        $data['side_menu_index'] = 'settings';
        $data['smi_selection'] = 'wallboard_';
        $data['topMenuItems'] = array($this->getNoticeMenuLink(), $this->getVideoMenuLink(),
            array('href'=>'task=wallboard&act=add-scroll-text', 'img'=>'add.png', 'label'=>'Add Scroll Text'));
        $this->getTemplate()->display('wallboard_scroll_texts', $data);
    }

    public function actionWallBoard()
    {
        include('model/MWallboard.php');
        $wb_model = new MWallboard();
        $this->pagination->num_records = $wb_model->numScrollTexts();
        $result = $this->pagination->num_records > 0 ?
            $wb_model->getScrollTexts('', $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($result) ? count($result) : 0;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        if (count($result)>0) {
            foreach ($result as &$data) {
                $data->action="<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete this text?' href='" . $this->url("task=confirm-response&act=WBoard&pro=da&tid={$data->id}")."'><i class='fa fa-times'></i> Delete</a>";
                $data->active = $data->active == 'Y' ? 'Yes' : 'No';
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionWallBoardNotice()
    {
        include('model/MWallboard.php');
        $wb_model = new MWallboard();
        $this->pagination->num_records = $wb_model->numNotices();
        $result = $this->pagination->num_records > 0 ?
            $wb_model->getNotices('', $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($result) ? count($result) : 0;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        $img_path = 'wallboard/notice/';
        if (count($result)>0) {
            foreach ($result as &$data) {
                $data->title = "<a href='".$this->url("task=wallboard&act=update-notice&nid={$data->id}")."'>".$data->title."</a>";
                ;
                $data->action="<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete this notice?' href='" . $this->url("task=confirm-response&act=WboardNoticeDel&pro=da&nid={$data->id}")."'><i class='fa fa-times'></i> Delete</a>";
                $data->active = $data->active == 'Y' ? 'Yes' : 'No';
                if (file_exists($img_path . $data->id . '.png')) {
                    $data->image="<a class='ConfirmAjaxWR' msg='Are you sure to remove this image?' href='" . $this->url("task=confirm-response&act=WboardImgDel&pro=da&nid={$data->id}")."'> Remove Image</a>";
                // 	                echo '<a href="' . $this->url('task='.$request->getControllerName()."&act=del-notice-img&nid=$notice->id") .
// 	                '" onclick="return confirm(\'Are you sure to remove this (SL=' . $i . ') image?\');">Remove Image</a>';
                } else {
                    $data->image = "-";
                }
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionWallBoardVideo()
    {
        include('model/MWallboard.php');
        $wb_model = new MWallboard();
        $this->pagination->num_records = $wb_model->numVideos();
        $result = $this->pagination->num_records > 0 ?
            $wb_model->getVideos('', $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($result) ? count($result) : 0;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        $img_path = 'wallboard/notice/';
        if (count($result)>0) {
            foreach ($result as &$data) {
                $data->title = "<a href='".$this->url("task=wallboard&act=update-video&vid={$data->id}")."'>".$data->title."</a>";
                $data->action="<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete this video?' href='" . $this->url("task=confirm-response&act=WboardVideoDel&pro=da&vid={$data->id}")."'><i class='fa fa-times'></i> Delete</a>";
                $data->active = $data->active == 'Y' ? 'Yes' : 'No';
                if ($data->source_type == 'YouTube') {
                    $data->source_type = '<a href="http://www.youtube.com/embed/'. $data->source .'" class="youtube">http://www.youtube.com/watch?v=' . $data->source . '</a>';
                } else {
                    $data->source_type = '<a class="local" href="' . $this->url("task=wallboard&act=play-video&vid=".$data->id) . '">Show</a>';
                }
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionSTemplate()
    {
        include('model/MSkillCrmTemplate.php');
        $template_model = new MSkillCrmTemplate();
        $this->pagination->num_records = $template_model->numTemplates();
        $result = $this->pagination->num_records > 0 ?
            $template_model->getTemplates('', $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($result) ? count($result) : 0;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        if (count($result)>0) {
            foreach ($result as &$data) {
                $data->title = "<a href='".$this->url("task=stemplate&act=update-template&tid={$data->template_id}")."'>".$data->title."</a>";
                $data->design = "<a href='".$this->url("task=stemplate&act=details&tid={$data->template_id}")."'>design &gt;&gt;</a>";
                $data->disposition = "<a href='".$this->url("task=scrmdc&tid={$data->template_id}")."'>code (s)</a>";
                $data->type = "<a href='".$this->url("task=scrmdg&tid={$data->template_id}")."'>service type (s)</a>";
                $data->action="<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete this template?' href='" . $this->url("task=confirm-response&act=TempDel&pro=da&tid={$data->template_id}")."'><i class='fa fa-times'></i> Delete</a>";
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }


    public function actionSDisposition()
    {
        include('model/MSkillCrmTemplate.php');
        $dc_model = new MSkillCrmTemplate();
        $tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';

        $templateinfo = $dc_model->getTemplateById($tid);

        if (!empty($templateinfo)) {


            //$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
            $this->pagination->num_records = $dc_model->numDispositions($tid);
            $result = $this->pagination->num_records > 0 ?
                $dc_model->getDispositions($tid, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
            $this->pagination->num_current_records = is_array($result) ? count($result) : 0;

            $responce = $this->getTableResponse();
            $responce->records = $this->pagination->num_records;
            $disposition_types =  [ 'Q' => 'Query', 'R' => 'Request', 'C' => 'Complaint' ];
            if (count($result)>0) {
                include('model/MReportPartition.php');
                $partition_model = new MReportPartition();
                $partitions = array("" => "All") + $partition_model->getPartitionsKeyValue();

                $parents = array();
                foreach ($result as &$data) {
                    $parent = '';
                    if (!empty($data->parent_id)) {
                        if (isset($parents[$data->parent_id])) {
                            $parent = $parents[$data->parent_id];
                        } else {
                            $parent = $dc_model->getDispositionPath($data->disposition_id);
                            $parents[$data->parent_id] = $parent;
                        }
                    }

                    $data->titleTxt = "<a href='".$this->url("task=scrmdc&act=update&tid={$tid}&sid={$data->disposition_id}")."'>";
                    if (!empty($parent)) {
                        $data->titleTxt .= $parent . ' -> ';
                    }
                    $data->titleTxt .= $data->title."</a>";
                    if (empty($data->parent_id)) {
                        $data->partition_id = "<a class='lightboxIF ' href='" . $this->url("task=scrmdc&act=change-partition&pro=da&tid={$data->template_id}&dcode={$data->disposition_id}")."'> ". $partitions[$data->partition_id]."</a>";
                    } else {
                        $data->partition_id = !empty($partitions[$data->partition_id]) ? $partitions[$data->partition_id] :  "";
                    }

                    $data->disposition_type = !empty($disposition_types[$data->disposition_type]) ? $disposition_types[$data->disposition_type] : $data->disposition_type;

                    $disposition_status = $data->status == 'Y' ? '<b class="text-success">Active</b>' : '<b class="text-danger">Inactive</b>';

                    $data->status = "<a class='lightboxIF ' href='" . $this->url("task=scrmdc&act=change-status&pro=da&tid={$data->template_id}&dcode={$data->disposition_id}&status={$data->status}")."'> ". $disposition_status  ."</a>";
                    // $data->action = "<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete this disposition code {$data->disposition_id}?' href='" . $this->url( "task=confirm-response&act=DelCode&pro=da&tid={$data->template_id}&dcode={$data->disposition_id}")."'><i class='fa fa-times'></i> Delete</a>";
                }
            }
            $responce->rowdata = $result;
        } else {
            $responce = $this->getTableResponse();
        }
        $this->ShowTableResponse();
    }


    public function actionConference()
    {
        include('model/MConference.php');
        $conf_model = new MConference();
        $result = $conf_model->getConferences();

        $responce = $this->getTableResponse();
        if (count($result)>0) {
            foreach ($result as &$data) {
                $data->active = $data->active == 'Y' ? 'Active' : 'Inactive';
                $data->bridge_number = "<a href='".$this->url("task=conference&act=update&cid={$data->conf_id}")."'>".$data->bridge_number."</a>";
                if ($data->start_time > 0) {
                    $data->start_time = date("Y-m-d H:i:s", $data->start_time);
                } else {
                    $data->start_time = '-';
                }

                if ($data->end_time > 0) {
                    $data->end_time = date("Y-m-d H:i:s", $data->end_time);
                } else {
                    $data->end_time = '-';
                }

                // 	            $data->design = "<a href='".$this->url("task=stemplate&act=details&tid={$data->template_id}")."'>design &gt;&gt;</a>";
                // 	            $data->disposition = "<a href='".$this->url("task=scrmdc&tid={$data->template_id}")."'>code (s)</a>";
                // 	            $data->type = "<a href='".$this->url("task=scrmdg&tid={$data->template_id}")."'>service type (s)</a>";
                $data->action="<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete this template?' href='" . $this->url("task=confirm-response&act=DelConf&pro=da&confid={$data->conf_id}")."'><i class='fa fa-times'></i> Delete</a>";
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionMisslogin_test()
    {
        include('model/MAgent.php');
        include('lib/Pagination.php');
        $agent_model = new MAgent();
        $pagination = new Pagination();
        $errMsg = '';
        $errType = 1;
        $isUnlock = isset($_REQUEST['unlock']) ? trim($_REQUEST['unlock']) : '';
        $agentId = isset($_REQUEST['agentid']) ? trim($_REQUEST['agentid']) : '';
        $agentIP = isset($_REQUEST['aip']) ? trim($_REQUEST['aip']) : '';

        if (!empty($agentId) && !empty($agentIP) && $isUnlock == "yes") {
            if ($agent_model->clearMisslogin2Unlock($agentId, $agentIP)) {
                $agent_model->addToAuditLog('Misslogin', 'U', $agentId, "Account unblocked");
                $url = $this->getTemplate()->url("task=password&act=misslogin");
                $msg = "User(".$agentId.") Account Unblocked Successfully";
                $this->getTemplate()->display('msg', array('pageTitle'=>'Failed Login Attempts', 'isError'=>false, 'msg'=>$msg, 'redirectUri'=>$url));
            }
        }

        $pagination->base_link = $this->getTemplate()->url("task=password&act=misslogin");
        $pagination->num_records = $agent_model->numMissLogins();

        if (!isset($_REQUEST['download'])) {
            $data['mislogins'] = $pagination->num_records > 0 ?
                $agent_model->getMissLogins($pagination->getOffset(), $pagination->rows_per_page) : null;
        } else {
            $data['pass_model'] = $agent_model;
        }

        $pagination->num_current_records = is_array($data['mislogins']) ? count($data['mislogins']) : 0;
        $data['attmNumber'] = $agent_model->attemptsNumUnlock();
        $data['pagination'] = $pagination;
        $data['errMsg'] = $errMsg;
        $data['errType'] = $errType;
        $data['request'] = $this->getRequest();
        $data['pageTitle'] = 'Failed Login Attempts';
        $data['side_menu_index'] = 'settings';
        $this->getTemplate()->display('misslogin_attempt', $data);
    }

    public function actionMissloginAttempt()
    {
        include('model/MAgent.php');
        $agent_model = new MAgent();
        $this->pagination->num_records = $agent_model->numMissLogins();
        $result = $this->pagination->num_records > 0 ?
            $agent_model->getMissLogins('', $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $attmNumber = $agent_model->attemptsNumUnlock();
        $this->pagination->num_current_records = is_array($result) ? count($result) : 0;

        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        if (count($result)>0) {
            foreach ($result as &$data) {
                if (!empty($attmNumber) && $data->tried >= $attmNumber) {
                    $data->action="<a class='ConfirmAjaxWR' msg='Are you sure to unlock?' href='" . $this->url("task=confirm-response&act=Unlock&pro=da&unlock=yes&agentid={$data->agent_id}&aip={$data->ip}")."'> Unlock</a>";
                    //$data->action = "unlock";
                }
                // 	            $data->title = "<a href='".$this->url("task=stemplate&act=update-template&tid={$data->template_id}")."'>".$data->title."</a>";
// 	            $data->design = "<a href='".$this->url("task=stemplate&act=details&tid={$data->template_id}")."'>design &gt;&gt;</a>";
// 	            $data->disposition = "<a href='".$this->url("task=scrmdc&tid={$data->template_id}")."'>code (s)</a>";
// 	            $data->type = "<a href='".$this->url("task=scrmdg&tid={$data->template_id}")."'>service type (s)</a>";
// 	            $data->action="<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete this template?' href='" . $this->url( "task=confirm-response&act=TempDel&pro=da&tid={$data->template_id}")."'><i class='fa fa-times'></i> Delete</a>";
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }
    public function actionLanguage()
    {
        include_once('model/MLanguage.php');
        $lang_model = new MLanguage();
        $this->pagination->num_records = $lang_model->numLanguage();
        $result = $this->pagination->num_records > 0 ?
            $lang_model->getAllLanguage('', $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($result) ? count($result) : 0;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        $status=array("A"=>'<strong class="text-success">Active</strong>',"I"=>'<strong class="text-danger">Inactive</strong>');
        if (count($result)>0) {
            foreach ($result as &$data) {
                $data->status="<a class='ConfirmAjaxWR' msg='Be confirm to do ".($data->status=="A"?"Inactive":"Active")." language  :".$data->lang_title."?' href='" . $this->url("task=confirm-response&act=lang&pro=cs&key={$data->lang_key}&status=".($data->status=="A"?"I":"A"))."'>".GetStatusText($data->status, $status)."</a>";
                $data->action="<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete language :".$data->lang_title."?' href='" . $this->url("task=confirm-response&act=lang&pro=da&key={$data->lang_key}")."'><i class='fa fa-times'></i> Delete</a>";
            }
        }


        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }
    public function actionHotkey()
    {
        AddModel('MAgentHotKey');
        $hotkey_model = new MAgentHotKey();
        $this->pagination->num_records = $hotkey_model->numRows();
        $result = $this->pagination->num_records > 0 ?
            $hotkey_model->getAllRows($this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($result) ? count($result) : 0;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        $status=array("A"=>'Active',"I"=>'Inactive');
        if (count($result)>0) {
            foreach ($result as &$data) {
                $data->status="<a class='ConfirmAjaxWR btn btn-xs btn-".($data->status=="A"?"success":"danger")."' msg='Be confirm to do ".($data->status=="A"?"Inactive":"Active")." hotkey  :".$data->action."?' href='" . $this->url("task=confirm-response&act=hotkey&pro=cs&key={$data->action}&status=".($data->status=="A"?"I":"A"))."'>".GetStatusText($data->status, $status)."</a>";


                $data->action2="<a class='lightboxWIFR btn btn-success btn-xs' href='" . $this->url("task=hotkey&act=Add&action={$data->action}")."'> <i class='fa fa-angle-double-right'></i> Edit </a>";
            }
        }


        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionSetttrunkInit()
    {
        include('model/MSetting.php');
        $setting_model = new MSetting();

        $this->pagination->num_records = 0;
        $result = $setting_model->getTrunks();
        $this->pagination->num_current_records = $this->pagination->num_records = is_array($result) ? count($result) : 0;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        $status = array("Y"=>'Enable',"N"=>'Disable');

        if (count($result)>0) {
            foreach ($result as &$data) {
                $data->reg_status_txt = "";
                if ($data->reg_status == 'R') {
                    $data->reg_status_txt = "<span class='text-success'>Registered</span>";
                } else {
                    $data->reg_status_txt = "<span class='text-danger'>Not Registered</span>";
                }
                if ($data->status == 'I') {
                    $data->statusTxt = "<a class='ConfirmAjaxWR' msg='Are you sure to enable " . $data->label . "?' ";
                    $data->statusTxt .= "href='" . $this->url("task=confirm-response&act=setttrunk&pro=activate&status=A&trunkid=".$data->trunkid)."'><span class='text-danger'>Disable</span></a>";
                } else {
                    $data->statusTxt = "<a class='ConfirmAjaxWR' msg='Are you sure to disable " . $data->label . "?' ";
                    $data->statusTxt .= "href='" . $this->url("task=confirm-response&act=setttrunk&pro=activate&status=I&trunkid=".$data->trunkid)."'><span class='text-success'>Enable</span></a>";
                }
                $data->trunk_name_url = "<a href='".$this->url('task=setttrunk&act=update&trunkid='.$data->trunkid)."'>".$data->label."</a>";
                //$data->status = "<a class='ConfirmAjaxWR btn btn-xs btn-".($data->active=="Y"?"success":"danger")."' msg='Are you sure to ".($data->active=="Y"?"disable":"enable")." ".$data->trunk_name."?' href='" . $this->url( "task=confirm-response&act=hotkey&pro=cs&key={$data->action}&status=".($data->status=="A"?"I":"A"))."'>".GetStatusText($data->status,$status)."</a>";
                $data->act_links = "<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete ".$data->label."?' href='" . $this->url("task=confirm-response&act=setttrunk&pro=delete&trunkid=".$data->trunkid)."'><i class='fa fa-times'></i> Delete</a>";
            }
        }


        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionSettMacroInit()
    {
        include('model/MSetting.php');
        $setting_model = new MSetting();

        $this->pagination->num_records = 0;
        $result = $setting_model->getMacros();

        $this->pagination->num_current_records = $this->pagination->num_records = is_array($result) ? count($result) : 0;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if (count($result)>0) {
            foreach ($result as &$data) {
                $data->act_links = "<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete ".$data->code."?' href='" . $this->url("task=confirm-response&act=settings-macro&pro=delete&macro_code=".$data->code)."'><i class='fa fa-times'></i> Delete</a>";
                $data->code = "<a href='".$this->url('task=settings-macro&act=update&macro_code='.$data->code)."'>".$data->code."</a>";
            }
        }


        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionSettMacroJob()
    {
        include('model/MSetting.php');
        include('model/MSkill.php');
        include('model/MIvr.php');
        include('model/MCti.php');
        $setting_model = new MSetting();
        $skill_model = new MSkill();
        $ivr_model = new MIvr();
        $cti_model = new MCti();

        $macro_code = isset($_REQUEST['macro_code']) ? trim($_REQUEST['macro_code']) : '';

        $this->pagination->num_records = 0;

        $result = $setting_model->getMacroSettings($macro_code);

        $this->pagination->num_current_records = $this->pagination->num_records = is_array($result) ? count($result) : 0;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        //$skill_options = $skill_model->getSkillOptions();

        if (count($result)>0) {
            $cti_options = $cti_model->getCtiOptions();
            $skill_options = $skill_model->getSkillOptions();
            $ivr_options = $ivr_model->getIvrOptions();
            $action_types = array('SQ' => 'Skill', 'IV' => 'IVR', 'XF' => 'External Forward', 'VM' => 'Voice Mail');
            foreach ($result as &$data) {
                $data->desc = '';
                if ($data->action_type == 'SQ' || $data->action_type == 'VM') {
                    $data->desc = isset($skill_options[$data->skill_id]) ? $skill_options[$data->skill_id] : $data->skill_id;
                } elseif ($data->action_type == 'IV') {
                    $data->desc = isset($ivr_options[$data->ivr_id]) ? $ivr_options[$data->ivr_id] : $data->ivr_id;
                } elseif ($data->action_type == 'XF') {
                    $data->desc = $data->param;
                }

                //$data->skillname = isset($skills[$data->skill_id]) ? $skills[$data->skill_id] : $data->skill_id;
                $data->act_links = "<a data-w='600' data-h='400' class='lightboxWIFR btn btn-success btn-xs' href='" . $this->url("task=settings-macro&act=add-macro-job&macro_code=".$data->code."&cti=".$data->cti_id)."'> <i class='fa fa-edit'></i> Edit </a> &nbsp; ";
                //$del_id = isset($trunkid) ? $trunkid : '';
                $del_id = $macro_code . '_' . urlencode($data->cti_id);
                $ctiname = isset($cti_options[$data->cti_id]) ? $cti_options[$data->cti_id] : $data->cti_id;
                $data->act_links .= "<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete cti ".$ctiname."?' href='" . $this->url("task=confirm-response&act=settings-macro&pro=deljob&id=".$del_id)."'><i class='fa fa-times'></i> Delete</a>";
                $data->cti_id = $ctiname;
                $data->action_type = isset($action_types[$data->action_type]) ? $action_types[$data->action_type] : $data->action_type;
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionSetttrunkCli()
    {
        include('model/MSetting.php');
        include('model/MSkill.php');
        $setting_model = new MSetting();
        $skill_model = new MSkill();
        $trunkid = isset($_REQUEST['trunkid']) ? trim($_REQUEST['trunkid']) : '';

        $this->pagination->num_records = 0;
        $result = $setting_model->getTrunkCLIs($trunkid);
        $this->pagination->num_current_records = $this->pagination->num_records = is_array($result) ? count($result) : 0;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        $skills = $skill_model->getOutSkillOptions();

        if (count($result)>0) {
            foreach ($result as &$data) {
                $data->skillname = isset($skills[$data->skill_id]) ? $skills[$data->skill_id] : $data->skill_id;
                $data->act_links = "<a data-w='600' data-h='400' class='lightboxWIFR btn btn-success btn-xs' href='" . $this->url("task=setttrunk&act=addcli&trunkid=".$trunkid."&skill=".$data->skill_id."&dial_prefix=".urlencode($data->dial_prefix))."'> <i class='fa fa-edit'></i> Edit </a> &nbsp; ";
                $del_id = isset($trunkid) ? $trunkid : '';
                $del_id .= '_' . $data->skill_id . '_' . urlencode($data->dial_prefix);
                $data->act_links .= "<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete number ".$data->cli."?' href='" . $this->url("task=confirm-response&act=setttrunk&pro=delcli&id=".$del_id)."'><i class='fa fa-times'></i> Delete</a>";
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionScrmdgInit()
    {
        include('model/MSkillCrmTemplate.php');
        $dc_model = new MSkillCrmTemplate();

        $tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
        $templateinfo = $dc_model->getTemplateById($tid);
        $result = null;
        if (!empty($templateinfo)) {
            $this->pagination->num_records = $dc_model->numDispositionGroups($tid);
            $result = $this->pagination->num_records > 0 ?
                $result = $dc_model->getDispositionGroups($tid, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
            $this->pagination->num_current_records = is_array($result) ? count($result) : 0;
            $responce = $this->getTableResponse();
            $responce->records = $this->pagination->num_records;

            if (count($result)>0) {
                foreach ($result as &$data) {
                    $data->title_link = "<a href='".$this->url("task=scrmdg&act=update&tid=$tid&gid=".$data->service_type_id)."'>".$data->title."</a>";

                    if ($data->status_ticketing == 'N') {
                        $data->status = "<span class='text-danger'>Inactive</span>";
                    } else {
                        $data->status = "<span class='text-success'>Active</span>";
                    }
                    if ($data->use_email_module == 'N') {
                        $data->email_module = "<span class='text-danger'>No</span>";
                    } else {
                        $data->email_module = "<span class='text-success'>Yes</span>";
                    }
                    /* if ($data->status_ticketing == 'N') {
                        $data->status = "<a class='ConfirmAjaxWR' msg='Are you sure to enable tickect service of " . $data->title . "?' ";
                        $data->status .= "href='" . $this->url("task=confirm-response&act=scrmdg&pro=activate&status=Y&tid=".$data->template_id."&sid=".$data->service_type_id)."'><span class='text-danger'>Inactive</span></a>";
                    } else {
                        $data->status = "<a class='ConfirmAjaxWR' msg='Are you sure to disable tickect service of " . $data->title . "?' ";
                        $data->status .= "href='" . $this->url("task=confirm-response&act=scrmdg&pro=activate&status=N&tid=".$data->template_id."&sid=".$data->service_type_id)."'><span class='text-success'>Active</span></a>";
                    } */
                    $data->from_email = htmlentities($data->from_email_name);
                    $data->to_email = htmlentities($data->to_email_name);
                    $data->action = "<a class='ConfirmAjaxWR' msg='Are you sure to delete service type ".$data->title."?' href='" . $this->url("task=confirm-response&act=scrmdg&pro=delete&tid=".$data->template_id."&sid=".$data->service_type_id)."'><i class='fa fa-times-circle icon-danger' style='font-size:16px;'></i></a>";
                }
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionIvrdc()
    {
        include('model/MIvrService.php');
        $ivr_model = new MIvrService();

        $this->pagination->num_records = $ivr_model->numServices();
        $result = $this->pagination->num_records > 0 ?
            $result = $ivr_model->getServices($this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($result) ? count($result) : 0;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;
        $report_category = array("S"=>"Summary", "D"=>"Details");
        $report_type = array("I"=>"Info", "M"=>"Menu");
        if (count($result)>0) {
            foreach ($result as &$data) {
                $data->service_type = $data->service_type=='A' ? 'Auto' : 'Manual';
                $data->edit_link = "<a href='".$this->url('task=ivrdc&act=update&sid='.$data->disposition_code)."'>".$data->service_title."</a>";
                $data->act_links = "<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete disposition code : ".$data->disposition_code."?' href='" . $this->url("task=confirm-response&act=ivrdcDel&pro=del&dcode=".$data->disposition_code)."'><i class='fa fa-times'></i> Delete</a>";
                $data->report_category = !empty($data->report_category) ? $report_category[$data->report_category] : "";
                $data->report_type = !empty($data->report_type) ? $report_type[$data->report_type] : "";
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionVccLocations()
    {
        include('model/VirtualCC.php');
        $vcc_model = new VirtualCC();

        $this->pagination->num_records = $vcc_model->numLocations();
        $result = $vcc_model->getLocations($this->pagination->getOffset(), $this->pagination->rows_per_page);
        $this->pagination->num_current_records = is_array($result) ? count($result) : 0;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        if (count($result)>0) {
            foreach ($result as &$data) {
                $data->call_ratio = $data->call_ratio . '%';
                $data->name = "<a href='".$this->url('task=location-based-routing&act=update&lid='.$data->vcc_id)."'>".$data->name."</a>";
                //$data->service_type = $data->service_type=='A' ? 'Auto' : 'Manual';
                //$data->edit_link = "<a href='".$this->url('task=ivrdc&act=update&sid='.$data->disposition_code)."'>".$data->service_title."</a>";
                //$data->act_links = "<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete disposition code : ".$data->disposition_code."?' href='" . $this->url( "task=confirm-response&act=ivrdcDel&pro=del&dcode=".$data->disposition_code)."'><i class='fa fa-times'></i> Delete</a>";
            }
        }

        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionBlfsettings()
    {
        include('model/MBlf_key.php');
        $blf_model = new MBlf_key();

        $seat = isset($_REQUEST['seat']) ? trim($_REQUEST['seat']) : '';
        $unit = isset($_REQUEST['unit']) ? trim($_REQUEST['unit']) : '';

        $this->pagination->num_records = $blf_model->numBlfSettings($seat, $unit);
        $result = $this->pagination->num_records > 0 ?
            $result = $blf_model->getBlfSettings($seat, $unit, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        $this->pagination->num_current_records = is_array($result) ? count($result) : 0;
        $responce = $this->getTableResponse();
        $responce->records = $this->pagination->num_records;

        $seatInfo = $blf_model->getSeatById($seat);
        $seatArray = $blf_model->getAllSeatArr();
        $agentArray = $blf_model->getAllAgentArr();

        if (count($result)>0) {
            foreach ($result as &$data) {
                $blfEditUrl = $this->url("task=blfsettings&act=update&seat=$seat&unit=$unit&bkey=$data->key_id");

                $data->action = "<a class='lightboxIF btn btn-xs btn-info' href='" . $blfEditUrl ."'><i class = 'fa fa-edit'></i>Edit</a>";
                //$grid->AddTitleRightHtml('<a class="lightboxWR btn btn-xs btn-info" href="'.extn_url("user/lbox/add-blf?ui=$unit").'" ><i class="fa fa-plus"></i> Add New</a>');
                $monitorTxt = !empty($seatArray[$data->monitor]) ? $seatArray[$data->monitor].' (Seat)' : (!empty($agentArray[$data->monitor]) ? $agentArray[$data->monitor].' (Agent)' : $data->monitor);
                //$data->action.=" <a class='ConfirmAjaxWR btn btn-xs btn-danger' msg='Are you sure to delete ".$monitorTxt."?' href='" . extn_url ( "user/confirmresponse?m=blf&act=d&monitor=".$data->monitor."&unit_id=".$unit."&key_id=".$data->key_id) ."'> <i class = 'fa fa-trash'></i> Delete</a>";
                //$data->user_id=!empty($userlist[$data->userid])?$userlist[$data->userid]:$data->userid;
                $data->action .= " &nbsp;<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete ".$monitorTxt."?' href='" . $this->url("task=confirm-response&act=blfsettings&pro=delete&seat=".$data->seat_id."&unit=".$data->unit_id."&bkey=".$data->key_id)."'><i class='fa fa-times'></i> Delete</a>";
                $data->monitor = $monitorTxt;
                $data->seat_id = $seatInfo->label;
            }
        }
        $responce->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionBlackList()
    {
        include('model/MBlackwhite.php');

        $black_list_model = new MBlackwhite();

        if ($this->gridRequest->isMultisearch) {
            $cli = $this->gridRequest->getMultiParam('cli');
        }

        $this->pagination->num_records = $black_list_model->numBlackList($cli);
        $result = $this->pagination->num_records > 0 ?
            $black_list_model->getBlackList($cli, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        foreach ($result as $data) {
            $data->action = "<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete CLI :" . $data->cli . "?' href='" . $this->url("task=confirm-response&act=deleteblackwhite&cli={$data->cli}"."&list=black&pro=da") . "'><i class='fa fa-times'></i> Delete</a>";
        }

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;
        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionWhiteList()
    {
        include('model/MBlackwhite.php');

        $white_list_model = new MBlackwhite();

        if ($this->gridRequest->isMultisearch) {
            $cli = $this->gridRequest->getMultiParam('cli');
        }

        $this->pagination->num_records = $white_list_model->numWhiteList($cli);
        $result = $this->pagination->num_records > 0 ?
            $white_list_model->getWhiteList($cli, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        foreach ($result as $data) {
            $data->action = "<a class='ConfirmAjaxWR btn btn-danger btn-xs' msg='Are you sure to delete CLI :" . $data->cli . "?' href='" . $this->url("task=confirm-response&act=deleteblackwhite&cli={$data->cli}"."&list=white&pro=da") . "'><i class='fa fa-times'></i> Delete</a>";
        }

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;
        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionEvaluationPortalAccess()
    {
        include_once('model/MAgent.php');
        include_once('model/MBlackwhite.php');
        include('model/MSkill.php');
        include('model/MRole.php');

        $skill_model = new MSkill();
        $agent_model = new MAgent();
        $role_model = new MRole();

        $utype = '';
        $agent_id = '';
        $did = '';
        $role_id = '';
        $name = '';
        $nickName = '';
        $usertypes = get_role_list();

        if ($this->gridRequest->srcItem == "agent_id") {
            $agent_id = $this->gridRequest->srcText;
            $this->pagination->num_records = $agent_model->numAgents($utype, $agent_id, $did, '', $nickName);
            $result = $this->pagination->num_records > 0 ?
                $agent_model->getAgents($utype, $agent_id, $did, '', $this->pagination->getOffset(), $this->pagination->rows_per_page, $nickName) : null;
        } elseif ($this->gridRequest->srcItem == "name") {
            $name = $this->gridRequest->srcText;
            $this->pagination->num_records = count($agent_model->getAgentsFromName($name));
            $result = $this->pagination->num_records > 0 ?
                $agent_model->getAgentsFromName($name, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        } elseif ($this->gridRequest->srcItem == "usertype") {
            $utype = $this->gridRequest->srcText;
            $this->pagination->num_records = $agent_model->numAgents($utype, $agent_id, $did, '', $nickName);
            $result = $this->pagination->num_records > 0 ?
                $agent_model->getAgents($utype, $agent_id, $did, '', $this->pagination->getOffset(), $this->pagination->rows_per_page, $nickName) : null;
        } elseif ($this->gridRequest->srcItem == "role") {
            $role_id = $this->gridRequest->srcText;
            $this->pagination->num_records = count($agent_model->getAgentsFromRole($role_id));
            $result = $this->pagination->num_records > 0 ?
                $agent_model->getAgentsFromRole($role_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;
        } else {
            $this->pagination->num_records = $agent_model->numAgents($utype, $agent_id, $did, '', $nickName);
            $result = $this->pagination->num_records > 0 ?
                $agent_model->getAgents($utype, $agent_id, $did, '', $this->pagination->getOffset(), $this->pagination->rows_per_page, $nickName) : null;
        }
        $skill_options = $skill_model->getAllAgentSkillOptions($this->getTemplate()->email_module, '', 'array');

        if (count($result) > 0) {
            foreach ($result as &$data) {
                $skills = $agent_model->getAgentSkill($data->agent_id);
                $num_skills = 0;
                $dtstr = "";
                if (is_array($skills)) {
                    foreach ($skills as $skill) {
                        if ($num_skills > 0) {
                            $dtstr .= ', ';
                        }
                        $dtstr .= isset($skill_options[$skill->skill_id]) ? $skill_options[$skill->skill_id] : $skill->skill_id;
                        $dtstr .= ' (' . $skill->priority . ')';

                        $num_skills++;
                    }
                } else {
                    $dtstr = 'No Skill';
                }
                $data->skill = ' ' . $dtstr;

                if (empty($data->name)) {
                    $data->name = '-';
                } else {
                    $data->name = ' ' . $data->name;
                }

                $data->usertype = !empty($usertypes[$data->usertype]) ? $usertypes[$data->usertype] : $data->usertype;

                if ($data->role_id != "") {
                    $roles = $role_model->getRoles($data->role_id);
                    $data->role_id = "";
                    foreach ($roles as $role) {
                        if ($data->role_id == "") {
                            $data->role_id .= $role->name;
                        } else {
                            $data->role_id .= ", " . $role->name;
                        }
                    }
                }
                if ($data->agent_id == Userauth::getCurrentUser()) {
                    $data->action = "-";
                } else {
                    $data->action = "<a class='lightboxWIFR btn btn-primary btn-xs' href='" . $this->url("task=ev-portal-access&act=update-access&agent_id=" . $data->agent_id) . "'><i class='fa fa-edit'></i> Update Access</a>";
                }
            }
        }

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;
        $response->rowdata = $result;
        $this->ShowTableResponse();
    }

    public function actionForcastRgb()
    {
        include('model/MForcastRgb.php');
        include('lib/DateHelper.php');

        $forcast_rgb_model = new MForcastRgb();

        if ($this->gridRequest->isMultisearch) {
            $date_range = $this->gridRequest->getMultiParam('sdate');
            $date_format = get_report_date_format();
            $date_from = !empty($date_range['from']) ? generic_date_format($date_range['from'], $date_format) : date('Y-m-d');
            $date_to = !empty($date_range['to']) ? generic_date_format($date_range['to'], $date_format) : date('Y-m-d');
        }

        $date_info = DateHelper::get_input_report_time_details(false, $date_from, $date_to, '00', '00', '', '-1 second');

        $this->pagination->num_records = $forcast_rgb_model->numForcastRgbData($date_info);
        $result = $this->pagination->num_records > 0 ?
            $forcast_rgb_model->getForcastRgbData($date_info, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

        foreach ($result as $result_data) {
            $result_data->sdate = date($date_format, strtotime($result_data->sdate));
        }

        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;
        $response->rowdata = $result;
        $this->ShowTableResponse();
    }
}
