<?php

class AgentPerformance extends Controller
{
	function __construct()
    {
		parent::__construct();
	}


    function init()
    {
        include('lib/DateHelper.php');
        AddModel('MAgent');

        $agent_model = new MAgent();

        $data['agents'] = $agent_model->get_as_key_value();
        $data['report_date_format_list'] = get_report_date_format_list();
        $data['report_date_format'] = get_report_date_format();
        $data['pageTitle'] = 'Agent Performance Summary';
        $data['dataUrl'] = $this->url('task=get-agent-performance-data&act=get');
        $this->getTemplate()->display_report('agent_performance_report', $data);

	}



}
