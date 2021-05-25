<?php
/**
 * Created by PhpStorm.
 * User: arif
 * Date: 1/12/2019
 * Time: 4:58 PM
 */

class CustomerJourney extends Controller {

//    private $_all_module_type = ["EM"=>"Email", "VC"=>"Voice", "CT"=>"Chat", "CB"=>"ChatBot", "SM"=>"Social Mining", "CO"=>"Co-browsing", "VI"=>"Visual IVR"];
//    private $_all_module_type = ["IV"=>"IVR", "VI"=>"Visual IVR", "AC"=>"Agent Call", "PD"=>"Agent OB Call", "CT"=>"Chat", "EM"=>"Email"];
    private $_all_module_type = customer_journey_module;
    private $module_icon = ["EM"=>'<i class="fa fa-envelope iw" aria-hidden="true"></i>', "AC"=>'<i class="fa fa-phone iw" aria-hidden="true"></i>', "CT"=>'<i class="fa fa-comments iw" aria-hidden="true"></i>', "CB"=>'<i class="fa fa-android iw" aria-hidden="true"></i>', "SM"=>'<i class="fa fa-facebook iw" aria-hidden="true"></i>', "CO"=>'<i class="fa fa-desktop iw" aria-hidden="true"></i>', "IV"=>'<i class="fa fa-italic iw" aria-hidden="true"></i>', "VI"=>'<i class="fa fa-vimeo iw" aria-hidden="true"></i>', "PD"=>'<i class="fa fa-opera iw" aria-hidden="true"></i>'];
    private $module_type_email = "EM";
    private $module_type_chat = "CT";
    //private $module_type_voice = "VC";
    private $module_type_agent_call = "AC";
    private $module_type_agent_outbound_call = "PD";
    private $module_type_iv = "IV";
    private $module_type_vivr = "VI";
    private $module_type_smining = "SM";


    private $module_subtype_va = "VA";//// VOICE AGENT
    private $module_subtype_vi = "VI";//// VOICE IVR
    private $module_subtype_ca = "CA";//// CHAT AGENT
    private $module_subtype_io = "IO";//// VIVR Only
    private $module_subtype_ia = "IA";//// VIVR AGENT
    private $module_subtype_sf = "SF";//// SOCIAL FACEBOOK

    function __construct() {
        parent::__construct();
    }
    function init(){
        return $this->actionCustomerJourney();
    }

    static function actionAddCustomerJourneyActivity($obj) {
        $mainobj = new MCustomerJourney();
        if (!$mainobj->isActivityExists($obj->callid)){
            return $mainobj->addActivity($obj);
        }
        return null;
    }

    function actionCustomerJourney(){
        $errMsg = "";
        $obj = new stdClass();
        $customer_id = !empty($_REQUEST["cli"]) ? substr($_REQUEST["cli"], -10) : "";
        $obj->customer_id = $customer_id;
        $obj->callid = !empty($_REQUEST["callid"]) ? substr($_REQUEST["callid"], 0, strpos($_REQUEST["callid"],'-')) : "";
        $obj->skill_id = !empty($_REQUEST["skill"]) ? $_REQUEST["skill"] : "";
        $obj->tstamp = time();
        $obj->sdate = date("Y-m-d", time());
        $obj->agent_id  = UserAuth::getCurrentUser();

        $journey_data = [];
        $not_exist_mod_type = [];
        $serial_arr = [];
        $temp = [];
        $data['sdate'] = "";
        $data['edate'] = "";

        if (!empty($customer_id)) {
            include('model/MCustomerJourney.php');
            $mainobj = new MCustomerJourney();
            $data['sdate'] = !empty($_POST['sdate']) ? $_POST['sdate'] : "";
            $data['edate'] = !empty($_POST['edate']) ? $_POST['edate'] : "";

            //die;
            $result = $mainobj->getCustomerJourney($customer_id, $data['sdate'], $data['edate']);
            $data['last_dispositions'] = $this->getLastNthWrapup($result, 5);
            //dd($customer_id);
            $result = !empty($result) ? array_reverse($result) : [];

            if (!empty($result)){
                $n=1;
                foreach ($result as $key){
                    if (!in_array($key->module_type, $temp)){
                        $temp[] = $key->module_type;
                    }
                    $serial_arr[$key->journey_id] = $n++;
                }
                foreach ($this->_all_module_type as $key => $value){
                    if (!in_array($key, $temp)){
                        $temp[] = $key;
                        $not_exist_mod_type[] = $key;
                    }
                }
            }
            $journey_data = $result;
            $this::actionAddCustomerJourneyActivity($obj);
        }

        $data['phone_number'] = $customer_id;
        $data['serial_arr'] = $serial_arr;
        $data['module_icon'] = $this->module_icon;
        $data['not_exist_mod_type'] = $not_exist_mod_type;
        $data['module_presidency'] = $temp;
        $data['icon_array'] = $this->getFormatedData($journey_data);
        $data['position_array'] = $this->getPosition($journey_data);
        $data['all_module'] = $this->_all_module_type;
        $data['journey_data'] = $journey_data;

        $data['today'] = date("Y-m-d", time());
        $data['errMsg'] = $errMsg;
        $data['pageTitle'] = 'Customer Journey';
        $this->getTemplate()->display_popup('customer_journey', $data);
    }

    function getLastNthWrapup($data, $nth = 1){
        $mainobj = new MCustomerJourney();
        $response = [];

        if (!empty($data)){
            $n = 0;
            $count = 0;
            foreach ($data as $key => $value) {
                if ($count > $nth-1)
                    break;

                if ($value->module_type == $this->module_type_chat && $value->module_sub_type == $this->module_subtype_ca) {
                    $result = $mainobj->getDispositionForChatAgent($value->journey_id);
                    if (!empty($result)){
                        $obj = new  stdClass();
                        $obj->title = !empty($result->title) ? $result->title : "";
                        $obj->dis_type = !empty($result->disposition_type) ? disposition_type_list()[$result->disposition_type] : "";
                        $result->customer_feedback = (string)$result->customer_feedback;
                        if ($result->customer_feedback != ""){
                            $obj->ice = $result->customer_feedback > 0 ? "Yes" : "No";
                        }else {
                            $obj->ice = "";
                        }
                        //$response[$value->module_type."_".$n] = !empty($result) ? $result->title : "";
                        $response[$value->module_type."_".$n] = $obj;
                        $count++;
                    }
                }
                elseif ($value->module_type == $this->module_type_email) {
                    $result = $mainobj->getDispositionForEmail($value->journey_id);
                    if (!empty($result)){
                        $obj = new stdClass();
                        $obj->title = !empty($result->title) ? $result->title : "";
                        $obj->dis_type = "";
                        $response[$value->module_type."_".$n] = $obj;
                        $count++;
                    }
                }

                elseif ($value->module_type == $this->module_type_agent_call){
                    $result = $mainobj->getCustomerJourneyByVoiceAgent($value->journey_id);
                    if (!empty($result['disp'])) {
                        foreach ($result['disp'] as $dispo) {
                            $obj = new stdClass();
                            $obj->title = $dispo->title;
                            $obj->dis_type = !empty($dispo->disposition_type) ? disposition_type_list()[$dispo->disposition_type] : "";
                            if (!empty($result['info']->ice_feedback)){
                                $obj->ice = $result['info']->ice_feedback=="Y" ? "Yes" : "No";
                            }else {
                                $obj->ice = "";
                            }
                            //$response[$value->module_type."_".$n] = $dispo->title;
                            $response[$value->module_type."_".$n] = $obj;
                            $n++;
                            $count++;
                        }
                    } /*elseif (!empty($result['info'])) {
                        $obj = new stdClass();
                        $obj->title = !empty($result['info']->title) ? $result['info']->title : "";
                        $obj->dis_type = !empty($result['info']->disposition_type) ? disposition_type_list()[$result['info']->disposition_type] : "";
                        //$response[$value->module_type."_".$n] = $result['info']->title;
                        $response[$value->module_type."_".$n] = $obj;
                        $count++;
                    }*/
                } elseif ($value->module_type == $this->module_type_agent_outbound_call){
                    $timestamp = substr($value->journey_id, 0, 10);
                    $sdate = $timestamp - 3600;
                    $edate = $timestamp + 3600;
                    $result = $mainobj->getDispostionForCall($value->journey_id, $sdate, $edate);
                    if (!empty($result)){
                        foreach ($result as $key){
                            $obj = new stdClass();
                            $obj->title = $key->title;
                            $obj->dis_type = !empty($key->disposition_type) ? disposition_type_list()[$key->disposition_type] : "";
                            $obj->ice = "";
                            $response[$value->module_type."_".$n] = $obj;
                            $n++;
                            $count++;
                        }
                    }
                }

                $n++;
            }
        }
        return $response;
    }

//    function actionCustomerJourneyOld(){
//        $errMsg = "";
//        $customer_id = !empty($_REQUEST["cli"]) ? substr($_REQUEST["cli"], -10) : "";
//        //GPrint($customer_id);
//        $journey_data = [];
//        if (!empty($customer_id)) {
//            include('model/MCustomerJourney.php');
//            $mainobj = new MCustomerJourney();
//            $result = $mainobj->getCustomerJourney($customer_id);
//            $journey_data = array_reverse($result);
//        }
//        $data['phone_number'] = $customer_id;
//        $data['journey_data'] = $journey_data;
//        $data['today'] = date("Y-m-d", time());
//        $data['errMsg'] = $errMsg;
//        $data['pageTitle'] = 'Customer Journey';
//        $this->getTemplate()->display('customer_journey_old', $data);
//    }

    function getJourneyIdByModuleType($journey_data){
        $response = [];
        $temp = [];
        if (!empty($journey_data)){
            $mainobj = new MCustomerJourney();
            $module_type = $mainobj->_module_type;
            foreach ($module_type as $item){
                unset($temp);
                foreach ($journey_data as $key){
                    if ($item == $key->module_type){
                        $temp[] = $key->journey_id;
                    }
                }
                $response[$item] = $temp;
            }
        }
        return $response;
    }

    function actionGetInfo(){
        include('model/MCustomerJourney.php');
        include('model/MEmail.php');
        $mainobj = new MCustomerJourney();

        $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : "";
        $sub_type = !empty($_REQUEST['sub_type']) ? $_REQUEST['sub_type'] : "";
        $jid = !empty($_REQUEST['id']) ? $_REQUEST['id'] : "";
        $phone_number = !empty($_REQUEST['phone_num']) ? $_REQUEST['phone_num'] : "";
        $response = "";

        if ($type == $this->module_type_email){
            $response = $this->getResponseDataFormat($type, $mainobj->getCustomerJourneyByEmail($jid), $phone_number);
        }
        elseif ($type == $this->module_type_chat) {
            //if ($sub_type == $this->module_subtype_ca){
            $response = $this->getResponseDataFormat($type, $mainobj->getCustomerJourneyByChat($jid), $phone_number);
            //}
        }
        elseif ($type && $type == "CB") {
            $response = "Not Done Yet!";
        }
        elseif ($type == $this->module_type_agent_call){
            $response = $this->getResponseDataFormat($type, $mainobj->getCustomerJourneyByVoiceAgent($jid), $phone_number);
        }
        elseif ($type == $this->module_type_agent_outbound_call){
            //$response = $this->getResponseDataFormat($type, $mainobj->getCustomerJourneyByVoiceAgent($jid), $phone_number);
            $response = $this->getResponseDataFormat($type, $mainobj->getCustomerJourneyByOutboundAgent($jid), $phone_number);
        }
        elseif ($type == $this->module_type_iv){
            $response = $this->getResponseDataFormat($type, $mainobj->getCustomerJourneyByVoiceIVR($jid), $phone_number);
        }

        elseif ($type == $this->module_type_vivr) {
            //if ($sub_type == $this->module_subtype_io){
            $response = $this->getResponseDataFormat($type, $mainobj->getVIVRForIO($jid), $phone_number, $sub_type);
            //}
        }

//        elseif ($type == $this->module_type_smining) {
//            if ($sub_type == $this->module_subtype_sf){
//                $response = $this->getResponseDataFormat($type, $mainobj->getSMiningForSF($jid), $phone_number, $sub_type);
//            } elseif ($sub_type == $this->module_subtype_ca){
//                $response = $this->getResponseDataFormat($type, $mainobj->getCustomerJourneyByChat($jid), $phone_number, $sub_type);  ///// FOR SM -> VA
//            }
//        }

        elseif ($type && $type == "CO") {
            $response = "Not Done Yet!";
        }

        echo json_encode($response);
        exit();
    }

    function getResponseDataFormat($type, $data, $phone_number, $sub_type=false){
        $response = "";
        $today = date("Y-m-d", time());
        if ($type==$this->module_type_email && !empty($data)) {

            $email_model = new MEmail();
            $create_time = $today==date('Y-m-d', $data->create_time) ? date(' h:i A, d M', $data->create_time) : date("Y-m-d h:i A", $data->create_time);
            $close_time = "";
            if (!empty($data->close_time)){
                $close_time = $today==date('Y-m-d', $data->close_time) ? date(' h:i A, d M', $data->close_time) : date("Y-m-d h:i A", $data->close_time);
            }
            $response .= "<div class='row'><div class='col-md-12 info-header'><div class='col-md-12 ml-11 fnt-16'><i class='fa fa-envelope hdr-icon-em' aria-hidden='true'></i> Email <i class='fa fa-times pull-right info-close' aria-hidden='true'></i></div><div class='col-md-12 ml-11'> Phone : ".$phone_number."</div> </div></div>";

            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label pull-left'>Subject: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".base64_decode($data->subject)."</div></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label pull-left'>From: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$data->created_for."</div></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label pull-left'>To: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$data->fetch_box_email."</div></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class='control-label pull-left'>Create: </label></div><div class='col-md-8 plr-0 tx-left pr-0'>".$create_time."</div></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class='control-label pull-left'>Close: </label></div><div class='col-md-8 plr-0 tx-left pr-0'>".$close_time."</div></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label pull-left'>Wrap-up: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$data->title."</div></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class='control-label pull-left'>Status: </label></div><div class='col-md-8 plr-0 tx-left pr-0'>".$email_model->getTicketStatusLabel($data->status)."</div></div>";
        }
        elseif ($type == $this->module_type_chat && !empty($data)) {

            $dis_type = !empty($data->disposition_type) ? disposition_type_list()[$data->disposition_type] : "Request";
            $start_time = $today==date('Y-m-d', strtotime($data->call_start_time)) ? date(' h:i A, d M', strtotime($data->call_start_time)) : $data->call_start_time;
            $response .= "<div class='row'><div class='col-md-12 info-header'><div class='col-md-12 ml-11 fnt-16'><i class='fa fa-comment hdr-icon-ct' aria-hidden='true'></i> Chat <i class='fa fa-times pull-right info-close' aria-hidden='true'></i></div><div class='col-md-12 ml-11'>  Phone : ".$phone_number."</div> </div></div>";

            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Start Time: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$start_time."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Duration: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".gmdate("H:i:s", $data->duration)."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Agent: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$data->name."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Wrap-up: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$data->title. " (".$dis_type. ") </div></></div>";
        }
        elseif ($type == $this->module_type_agent_call){
            //dd($data);
            $ice = "N/A";
            if ($data['info']->ice_feedback == "Y") $ice = "Yes";
            elseif ($data['info']->ice_feedback == "N") $ice = "No";

            $service = !empty($data['info']->did)?$data['info']->did:'786';
            $start_time = $today==date('Y-m-d', strtotime($data['info']->call_start_time)) ? date(' h:i A, d M', strtotime($data['info']->call_start_time)) : $data['info']->call_start_time;
            $response .= "<div class='row'><div class='col-md-12 info-header'><div class='col-md-12 ml-11 fnt-16'><i class='fa fa-phone hdr-icon-ac' aria-hidden='true'></i> Agent Call <i class='fa fa-times pull-right info-close' aria-hidden='true'></i></div><div class='col-md-12 ml-11'>  Phone : ".$phone_number."</div> </div></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Start Time: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$start_time."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Service: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$service."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Duration: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".gmdate("H:i:s", $data['info']->duration)."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Agent: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$data['info']->name."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>ICE: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$ice."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Wrap-up: </label></div>";
            $response .= "<div class='col-md-9 plr-0 tx-left'>";
            if (!empty($data['disp'])){
                foreach ($data['disp'] as $disp){
                    $dis_type = !empty($data['disp']->disposition_type) ? disposition_type_list()[$data['disp']->disposition_type] : "Request";
                    $response .= $disp->title." (".$dis_type.")</br>";
                }
            }
            $response .=  "</div></></div>";
        }

        elseif ($type == $this->module_type_agent_outbound_call){
            //GPrint($data);die;
            $ice = "N/A";
            //$service = !empty($data['info']->did)?$data['info']->did:'786';
            $start_time = $today==date('Y-m-d', strtotime($data['info']->start_time)) ? date(' h:i A, d M', strtotime($data['info']->start_time)) : $data['info']->start_time;
            $response .= "<div class='row'><div class='col-md-12 info-header'><div class='col-md-12 ml-11 fnt-16'><i class='fa fa-opera hdr-icon-pd' aria-hidden='true'></i> Outbound Call <i class='fa fa-times pull-right info-close' aria-hidden='true'></i></div><div class='col-md-12 ml-11'>  Phone : ".$phone_number."</div> </div></div>";
            //$response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Service: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$service."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Start Time: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$start_time."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Duration: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".gmdate("H:i:s", $data['info']->talk_time)."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Agent: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$data['info']->name."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>ICE: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$ice."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Wrap-up: </label></div>";
            $response .= "<div class='col-md-8 plr-0 tx-left pr-0'>";
            if (!empty($data['disp'])){
                foreach ($data['disp'] as $disp){
                    $dis_type = !empty($data['disp']->disposition_type) ? disposition_type_list()[$data['disp']->disposition_type] : "Request";
                    $response .= $disp->title." (".$dis_type.")</br>";
                }
            }
            $response .=  "</div></></div>";
        }

        elseif ($type == $this->module_type_iv){

            $service = !empty($data['info']->did) ? $data['info']->did : '786';
            $start_time = $today==date('Y-m-d', strtotime($data['info']->call_start_time)) ? date(' h:i A, d M', strtotime($data['info']->call_start_time)) : $data['info']->call_start_time;
            $last_node = "";
            if (!empty($data['desp']->service_title)) $last_node = $data['desp']->service_title;
            elseif (!empty($data['desp']->text)) $last_node = $data['desp']->text;

            $response .= "<div class='row'><div class='col-md-12 info-header'><div class='col-md-12 ml-11 fnt-16'><i class='fa fa-italic hdr-icon-iv' aria-hidden='true'></i> IVR <i class='fa fa-times pull-right info-close' aria-hidden='true'></i></div><div class='col-md-12 ml-11'>  Phone : ".$phone_number."</div> </div></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Service: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$service."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Start Time: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$start_time."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Duration: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".gmdate("H:i:s", $data['info']->duration)."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>IVR: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$data['info']->ivr_name."</div></></div>";
            //$response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Last Node: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$last_node."</div></></div>";
        }

        elseif ($type == $this->module_type_vivr){
            //if ($sub_type == $this->module_subtype_io){

            $start_time = $today==date('Y-m-d', strtotime($data->start_time)) ? date(' h:i A, d M', strtotime($data->start_time)) : $data->start_time;
            $ice = "N/A";
            if ($data->ice_feedback == "Y") $ice = "Yes";
            elseif ($data->ice_feedback == "N") $ice = "No";
            $service = !empty($data->did)?$data->did:'786';
            $response .= "<div class='row'><div class='col-md-12 info-header'><div class='col-md-12 ml-11 fnt-16'><i class='fa fa-vimeo hdr-icon-vi' aria-hidden='true'></i> Visual IVR <i class='fa fa-times pull-right info-close' aria-hidden='true'></i></div><div class='col-md-12 ml-11'>  Phone : ".$phone_number."</div> </div></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Start Time: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$start_time."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Service: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$service."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>Duration: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".gmdate("H:i:s", $data->time_in_ivr)."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>IVR: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$data->ivr_name."</div></></div>";
            $response .= "<div class='row bdr-u'> <div class='col-md-4 pl-0 pr-0 tx-left'><label class=' control-label'>ICE: </label></div> <div class='col-md-8 plr-0 tx-left pr-0'>".$ice."</div></></div>";
            //}
        }
        elseif ($type == $this->module_type_smining) {
            if ($sub_type == $this->module_subtype_sf) {
                $start_time = $today == date('Y-m-d', strtotime($data['info']->start_time)) ? date(' h:i A, d M', strtotime($data['info']->start_time)) : $data['info']->start_time;
                $response .= "<div class='row'><div class='col-md-12 info-header'><div class='col-md-12 ml-11 fnt-16'><i class='fa fa-facebook hdr-icon-sm' aria-hidden='true'></i> Social Miner <i class='fa fa-times pull-right info-close' aria-hidden='true'></i></div><div class='col-md-12 ml-11'>  Phone : " . $phone_number . "</div> </div></div>";
                $response .= "<div class='row bdr-u'> <div class='col-md-3 pl-0'><label class=' control-label'>Start: </label></div> <div class='col-md-9 plr-0 tx-left'>" . $start_time . "</div></></div>";
                $response .= "<div class='row bdr-u'> <div class='col-md-3 pl-0'><label class=' control-label'>User: </label></div> <div class='col-md-9 plr-0 tx-left'>" . $data['info']->user_name . "</div></></div>";
                $response .= "<div class='row bdr-u'> <div class='col-md-3 pl-0'><label class=' control-label'>Duration: </label></div> <div class='col-md-9 plr-0 tx-left'>" . gmdate("H:i:s", $data['info']->duration) . "</div></></div>";
                $response .= "<div class='row bdr-u'> <div class='col-md-3 pl-0'><label class=' control-label'>Journey: </label></div>";
                $response .= "<div class='col-md-9 plr-0 tx-left'>";
                if (!empty($data['disp'])){
                    foreach ($data['disp'] as $disp){
                        $response .= $disp->branch_title."</br>";
                    }
                }
                $response .=  "</div></></div>";

            } elseif ($sub_type == $this->module_subtype_ca) {
                $dis_type = !empty($data->disposition_type) ? disposition_type_list()[$data->disposition_type] : "Request";
                $start_time = $today==date('Y-m-d', strtotime($data->call_start_time)) ? date(' h:i A, d M', strtotime($data->call_start_time)) : $data->call_start_time;
                $response .= "<div class='row'><div class='col-md-12 info-header'><div class='col-md-12 ml-11 fnt-16'><i class='fa fa-facebook hdr-icon-sm' aria-hidden='true'></i> Social Miner <i class='fa fa-times pull-right info-close' aria-hidden='true'></i></div><div class='col-md-12 ml-11'>  Phone : ".$phone_number."</div> </div></div>";
                $response .= "<div class='row bdr-u'> <div class='col-md-3 pl-0'><label class=' control-label'>Start: </label></div> <div class='col-md-9 plr-0 tx-left'>".$start_time."</div></></div>";
                $response .= "<div class='row bdr-u'> <div class='col-md-3 pl-0'><label class=' control-label'>Duration: </label></div> <div class='col-md-9 plr-0 tx-left'>".gmdate("H:i:s", $data->duration)."</div></></div>";
                $response .= "<div class='row bdr-u'> <div class='col-md-3 pl-0'><label class=' control-label'>Agent: </label></div> <div class='col-md-9 plr-0 tx-left'>".$data->name."</div></></div>";
                $response .= "<div class='row bdr-u'> <div class='col-md-3 pl-0'><label class=' control-label'>Wrapup: </label></div> <div class='col-md-9 plr-0 tx-left'>".$data->title. " (".$dis_type. ") </div></></div>";
            }
        }
        return $response;
    }



    function getFormatedData($data) {
        $hold = [];
        $module_icon = $this->module_icon;
        if (!empty($data)) {
            foreach ($data as $item){
                $hold[$item->journey_id] = $module_icon[$item->module_type];
            }
        }
        return $hold;
    }

    function getPosition($data){
        $response = [];
        $temp = [];
        $iteration_count = [];
        $hold_last_occur = "";
        if (!empty($data)) {
            $n = 0;
            foreach ($data as $key){
                if (array_key_exists($key->module_type, $temp)){
                    if ($hold_last_occur==$key->module_type){
                        $margin = empty($temp[$hold_last_occur]) ? $temp[$hold_last_occur]+85 : 85;
                    } else {
                        $margin = $n - $temp[$key->module_type];
                    }

                    $margin -= $iteration_count[$key->module_type]*40;
                    $response[$key->journey_id] = $margin;
                    $temp[$key->module_type] += $margin+40;
                } else {
                    $temp[$key->module_type] = $n;
                    $response[$key->journey_id] = $n;
                }
                $n +=85;
                $hold_last_occur = $key->module_type;
                $iteration_count[$key->module_type] = 1;
            }
        }
        return $response;
    }
}