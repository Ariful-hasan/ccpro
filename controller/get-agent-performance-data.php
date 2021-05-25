<?php
require_once 'BaseTableDataController.php';
class GetAgentPerformanceData extends BaseTableDataController
{
    protected $date_format;

	function __construct()
    {
		parent::__construct();
        $this->date_format = get_report_date_format();
	}	
	
	public function actionGet()
    {
		AddModel('MAgentPerformance');
		$agent_performance_model = new MAgentPerformance();

        $date_format = get_report_date_format();

        $agent_id = $this->gridRequest->getMultiParam('agent_id','');
        $datetime_range = $this->gridRequest->getMultiParam('sdate');


        $sdate = !empty($datetime_range['from']) ? date_format(date_create_from_format($date_format." H:i", $datetime_range['from']),'Y-m-d H:i') : date('Y-m-d H:i');
        $edate = !empty($datetime_range['to']) ? date_format(date_create_from_format($date_format." H:i", $datetime_range['to']),'Y-m-d H:i') : date('Y-m-d H:i');

        $sdate = $sdate.':00';
        $edate = $edate.":59";

        $report_daterange_diff = strtotime($edate) - strtotime($sdate);

        if ($report_daterange_diff > (3600 * 72)){
            $edate = date("Y-m-d H:i:s", strtotime($sdate. " + 72 hours"));
        }


		$this->pagination->num_records = !empty($agent_id) ? 1 : $agent_performance_model->numAgents();
		$result = $this->pagination->num_records > 0 ?
            $agent_performance_model->getAgents('agent_id, name', $agent_id, $this->pagination->getOffset(), $this->pagination->rows_per_page) : null;

		$this->pagination->num_current_records = is_array($result) ? count($result) : 0;
		$response = $this->getTableResponse();
		$response->records = $this->pagination->num_records;

		$user_data = [];


		if(is_array($result) && count($result) > 0){

            $agent_skill_set = $agent_performance_model->getAgentSkillSetAsString(array_column($result,'agent_id'));

            foreach ( $result as &$data ) {
                $session_events = $agent_performance_model->getAgentsSessionEvents([$data->agent_id],$sdate,$edate);
                $agent_info = $this->calcAgentSessionInfo($data->agent_id, $session_events,$sdate ,$edate);
                if (is_array($agent_info)){
                    $first_session = array_shift($agent_info);
                    $data->skill_set = !empty($agent_skill_set[$data->agent_id]->skill_set) ? $agent_skill_set[$data->agent_id]->skill_set : "No Skill";
                    $data = array_merge((array)$first_session, (array)$data);

                    foreach ($agent_info as $session){
                        $agent_session = (object)array_merge((array)$data, (array)$session);
                        $agent_session->skill_set = !empty($agent_skill_set[$session->agent_id]->skill_set) ? $agent_skill_set[$session->agent_id]->skill_set : "No Skill";
                        $user_data[] = $agent_session;
                    }
                }else{
                    $data->skill_set = !empty($agent_skill_set[$data->agent_id]->skill_set) ? $agent_skill_set[$data->agent_id]->skill_set : "No Skill";
                    $data = (object)array_merge((array)$data, (array)$agent_info);
                }
            }
		}

        $response->rowdata = !empty($user_data) && is_array($user_data) ? array_merge($result, $user_data) : $result;
        $response->userdata = $user_data; // array_shift($user_data);
        array_push($response->hideCol,'sdate');
        $response->records += count($user_data);
		$this->ShowTableResponse($response);
	}



    function calcAgentSessionInfo($agent_id, $result, $stime='', $etime='')
    {
        AddModel('MAgentPerformance');
        $performance_model = new MAgentPerformance();

        $agent_sessions = [];

        $agent = new AgentPerformanceTemplate();



        $last_session_details = $performance_model->getAgentLastStatus($agent_id, $stime);
        if (!empty($last_session_details) && $last_session_details[0]->type == 'I'){
            $previous_login_info = $last_session_details;
        }else{
            $previous_login_info = $performance_model->getAgentPreviousLoginStatus($agent_id, $stime);
        }




        if (empty($result)){

            if (is_array($last_session_details)){
                $last_event = $last_session_details[0]->type;

                $agent->first_login = !empty($previous_login_info) ? $previous_login_info[0]->tstamp : $agent->first_login;

                if ($last_event == 'I'){ // Last event is login
                    $agent->staffed_time += $this->diff_in_sec($stime, $etime);
                    $agent->first_login = !empty($agent->first_login) ? $agent->first_login : $last_session_details[0]->tstamp;
                }elseif ($last_event == 'X'){ //Agent last event is busy
                    $agent->not_ready_count += 1;
                    $agent->staffed_time += $this->diff_in_sec($stime, $etime);
                    $agent->not_ready_time = $this->diff_in_sec($stime, $etime);
                }elseif ($last_event == 'R'){
                    $agent->staffed_time += $this->diff_in_sec($stime, $etime);
                    $agent->idle_time += $this->diff_in_sec($stime, $etime);
                }
            }

            $agent_sessions[] = $this->mergeAgentInformation($agent_id, $agent, $stime, $etime);

            return $agent_sessions;
        }


        $last_time = !empty($last_session_details[0]->tstamp) ? ($stime > $last_session_details[0]->tstamp  ? $stime : $last_session_details[0]->tstamp) : 0;
        $last_event = !empty($last_session_details[0]->type) ? $last_session_details[0]->type : '';

        if (!empty($previous_login_info)){
            $login = array_shift($previous_login_info);
            $agent->first_login = $login->tstamp;
        }

        if ($result[0]->type == 'I'){
            $agent->first_login = $result[0]->tstamp;
        }

        foreach($result as $index => $session) {

            if ($session->type == "O"){
                $agent->logout_count += 1;
            }

            if($index == 0 && $session->type == 'I') {
                $agent->login_count += 1;
                $agent->first_login = !empty($agent->first_login) ? $agent->first_login : $session->tstamp;

            }elseif ($index > 0 && $session->type == 'I'){
                // Agent has more than one session in time span

                $agent_sessions[] = $this->mergeAgentInformation($agent_id, $agent, $stime, $session->tstamp);
                $agent = new AgentPerformanceTemplate();
                $agent->first_login = $session->tstamp;
                $stime = $session->tstamp;
                $agent->login_count += 1;
            }
            else if($session->type=='O') {

                $agent->logout_time = $session->tstamp;

                if ($last_event == 'I'){
                    $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                    $agent->not_ready_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                }elseif ($last_event == 'R'){
                    $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                    $agent->idle_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                }elseif ($last_event == 'X'){
                    $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                    $agent->not_ready_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                }
            } else if($session->type=='X') {
                $agent->not_ready_count += 1;
                $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;

                if ($last_event == 'I'){
                    $agent->not_ready_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                }elseif ($last_event == 'R'){
                    $agent->idle_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                }
            } else if($session->type=='R') {
                if ($last_event == 'I'){
                    $agent->not_ready_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                    $agent->staffed_time += $this->diff_in_sec($last_time, $session->tstamp);
                }elseif ($last_event == 'X'){
                    $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                    $agent->not_ready_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $session->tstamp) : 0;
                }
            }

            $last_event = $session->type;
            $last_time = $session->tstamp;
        }

        $total_record = count($result);
        $index = $total_record - 1;



        if ($result[$index]->type != 'O'){

            $agent_next_status = $performance_model->getAgentNextStatus($agent_id, $etime);
            $agent_next_status = !empty($agent_next_status) ? array_shift($agent_next_status) : $agent_next_status;

            if (!empty($agent_next_status)){
                if($agent_next_status->type=='R'){
                    if ($last_event == 'I'){
                        $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $etime) : 0;
                    }elseif ($last_event == 'X'){
                        $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $etime) : 0;
                        $agent->not_ready_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $etime) : 0;
                    }
                }elseif ($agent_next_status->type=='X'){
                    $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $etime) : 0;
                    if ($last_event == 'X'){
                        $agent->not_ready_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $etime) : 0;
                    }
                }
            }else{
                $agent->staffed_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $etime) : 0;

                if (in_array($last_event, array('X','I'))){
                    $agent->not_ready_time += $last_time > 0 ?  $this->diff_in_sec($last_time, $etime) : 0;
                }
            }

        }


        if ($result[$index]->type != 'O'){
            $next_logout_info = $performance_model->getAgentNextLogoutStatus($agent_id, $last_time);

            $logout = !empty($next_logout_info) ? array_shift($next_logout_info) : $next_logout_info;
            $agent->logout_time = !empty($logout->tstamp) ? $logout->tstamp : '';
        }


        $agent_sessions[] = $this->mergeAgentInformation($agent_id, $agent, $stime, $last_time);

        return $agent_sessions;
    }


    protected function diff_in_sec($start_time, $end_time)
    {
        return strtotime($end_time) - strtotime($start_time);
    }

    protected function mergeAgentInformation($agent_id, AgentPerformanceTemplate $agent, $stime, $etime)
    {
	    AddModel('MAgentPerformance');
        $performance_model = new MAgentPerformance();

        $call_info = $performance_model->getAgentPerformanceSummary($agent_id, $stime, $etime);

        if (empty($call_info)){
            return $agent;
        }


        $call_info = array_shift($call_info);
        $agent->agent_id = $agent_id;
        $agent->first_login = !empty($agent->first_login) ? date($this->date_format." H:i:s", strtotime($agent->first_login)) : "-";
        $agent->logout_time = $agent->first_login != '-' ? (!empty($agent->logout_time) ? date($this->date_format." H:i:s", strtotime($agent->logout_time)) : "In Progress...") : "-";
        $agent->calls_answered = $call_info->calls_answered ?: 0;
        $agent->ring_time = $call_info->ring_time ?: 0;
        $agent->talk_time = $call_info->talk_time ?: 0;
        $agent->wrap_up_time = $call_info->wrap_up_time ?: 0;
        $agent->hold_time = $call_info->hold_time ?: 0;
        $agent->time_in_queue = $call_info->hold_time_in_queue ?: 0;
        $agent->agent_hangup = $call_info->agent_hangup ?: 0;
        $agent->aht = $call_info->calls_answered > 0 ? round(($call_info->ring_time + $call_info->talk_time + $call_info->hold_time + $call_info->wrap_up_time) / $call_info->calls_answered) : 0;
        $agent->agent_reject_calls = $call_info->agent_reject_calls ?: 0;
        $agent->agent_disconnect_calls = $call_info->agent_disc_calls ?: 0;
        $agent->login_time = gmdate("H:i:s", $agent->staffed_time);
        $agent->available_time = $agent->staffed_time > $agent->not_ready_time ? gmdate("H:i:s", $agent->staffed_time - $agent->not_ready_time) : gmdate("H:i:s", 0);
        $agent->total_idle_time = $agent->staffed_time - ($agent->not_ready_time + $call_info->ring_time +  $call_info->talk_time + $call_info->wrap_up_time);
        $agent->total_idle_time = $agent->total_idle_time > 0 ? gmdate("H:i:s", $agent->total_idle_time) : 0;
        $agent->short_call = $call_info->short_call ?: 0;
        $agent->repeated_call = $call_info->repeated_call ?: 0;
        $agent->repeated_percent = $call_info->calls_answered > 0 ? round(($call_info->repeated_call /  $call_info->calls_answered) * 100,2) : 0;
        $agent->fcr_call = $call_info->fcr_call ?: 0;
        $agent->fcr_percent = $call_info->calls_answered > 0 ? round(($call_info->fcr_call /  $call_info->calls_answered) * 100,2) : 0;
        $agent->workcode_count = $call_info->workcode_count ?: 0;
        $agent->workcode_percent = $call_info->calls_answered > 0 ? round(($call_info->workcode_count /  $call_info->calls_answered) * 100,2) : 0;

        return $agent;

    }
}

class AgentPerformanceTemplate
{

    public $agent_id = '';
    public $staffed_time = 0;
    public $idle_time = 0;
    public $logout_time = '';
    public $not_ready_time = 0;
    public $available_time = 0;
    public $not_ready_count = 0;
    public $first_login = '';
    public $skill_set = 'No Skill';
    public $calls_answered = 0;
    public $ring_time = 0;
    public $talk_time = 0;
    public $wrap_up_time = 0;
    public $hold_time = 0;
    public $time_in_queue = 0;
    public $aht = 0;
    public $total_idle_time = 0;
    public $agent_hangup = 0;
    public $agent_disconnect_calls = 0;
    public $agent_reject_calls = 0;
    public $short_call = 0;
    public $workcode_count = 0;
    public $workcode_percent = 0;
    public $repeated_call = 0;
    public $repeated_percent = 0;
    public $fcr_call = 0;
    public $fcr_percent = 0;
    public $login_count = 0;
    public $login_time = 0;
    public $logout_count = 0;
}
