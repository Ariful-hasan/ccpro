<?php
require_once 'BaseTableDataController.php';

class AgentForecasts extends BaseTableDataController
{
	public function __construct() {
		parent::__construct();
	}

	public function init()
	{
		$this->actionGenerate();
	}
	public function actionGenerate(){		
		include_once('model/MForecastSkill.php');
		include_once('model/MHisData.php');
		include_once('lib/Erlang.php');
		include_once('model/MForcastData.php');

		$forecast_skill_model = new MForecastSkill();
		$his_data_model = new MHisData();
		$erlang = new Erlang();
		$forecast_skill_model = new MForecastSkill();
		$forecast_model = new MForcastData();

		$generate_type_list = fc_generate_type_list();
		$model_list = fc_model_list();
		$day_type = fc_day_type();
		$trend_list = fc_trend_list();
		$service_type_list = fc_service_type_list();
		$fc_skill_list = $forecast_skill_model->getFcSkillList();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$error_data = '';
		$fc_generate_data = new stdclass();
		$fc_generate_data->service_type = 'V';
		$min_forecast_date_h = $forecast_model->getMinDateHourly($fc_generate_data->service_type);
		$min_forecast_date_d = $forecast_model->getMinDate($fc_generate_data->service_type);

		$data['pageTitle'] = 'Agent Forecast';
		$data['dataUrl'] = $this->url('task=forecasts&act=generate');
		$data['userColumn'] = "Generate Forecast"; 
		$data['request'] = $this->getRequest();
		$data['generate_type_list'] = $generate_type_list;
		$data['model_list'] = $model_list;
		$data['day_type'] = $day_type;
		$data['trend_list'] = $trend_list;
		$data['fc_skill_list'] = $fc_skill_list;
		$data['service_type_list'] = $service_type_list;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['fc_generate_data'] = $fc_generate_data;
		$data['error_data'] = $error_data;
		$data['min_forecast_date_h'] = date('Y/m/d', strtotime($min_forecast_date_h));
		$data['min_forecast_date_d'] = date('Y/m/d', strtotime($min_forecast_date_d));

		$this->getTemplate()->display('fc-generate-agent-forecast-form', $data);
	}

	public function actionGenerateData(){
		include_once('model/MAgentForecastGenerate.php');
		include_once('lib/FormValidator.php');
		include_once('model/MForcastData.php');
		include_once('model/MForecastSkill.php');
		include_once('lib/Erlang.php');
		$ag_fc_generate_model = new MAgentForecastGenerate();
		$forecast_skill_model = new MForecastSkill();	

		$request = $this->getRequest();
		$respType = '';
		$respMsg = '';
		$error_data = '';
		$fc_generate_data = new stdclass();
		$fc_generate_data->service_type = 'V';		

		if ($request->isPost()) {
			$post_data = $this->getRequest()->getPost();
			// Gprint($post_data);
			$validate = $ag_fc_generate_model->validate($post_data);
			// Gprint($validate);
			// die();

			if($validate[MSG_RESULT]){
				$respType = 1;
				$respMsg = '';	
				$validate[MSG_DATA]->sdate = generic_date_format($validate[MSG_DATA]->sdate);
				$validate[MSG_DATA]->edate = generic_date_format($validate[MSG_DATA]->edate);
				$validate[MSG_DATA]->skill_id = json_decode($validate[MSG_DATA]->skill_id, true);

				Gprint($validate[MSG_DATA]->skill_id);
				foreach ($validate[MSG_DATA]->skill_id as $key => $value) {
					$skill_info = $forecast_skill_model->getSkill($value);
					$validate[MSG_DATA]->skill_name[$value] = $skill_info[0]->name;
					$validate[MSG_DATA]->gplex_fc_group_ids[$value] = $skill_info[0]->gplex_fc_group_ids;
				}
				Gprint($validate[MSG_DATA]);
				die();
				
				$return_resp_data = [];
				if($request->getRequest('action') == 'Generate' || $request->getRequest('action') == 'Re-generate'){
					$response = $this->generate_ag_fc($validate[MSG_DATA]);
					$return_resp_data['chart_data'] = $response['chart_data'];
				} elseif($request->getRequest('action') == 'Save'){
					$response = $this->save_ag_fc($validate[MSG_DATA]);
				} else{
					$response[MSG_RESULT] = 0;
					$response[MSG_MSG] = "Your request is wrong!";
				}
				
				$return_resp_data[MSG_RESULT] = isset($response[MSG_RESULT]) ? $response[MSG_RESULT] : 0;
				$return_resp_data[MSG_MSG] = isset($response[MSG_MSG]) ? $response[MSG_MSG] : "";
				$return_resp_data[MSG_DATA] = isset($response[MSG_DATA]) ? $response[MSG_DATA] : [];
				$return_resp_data[MSG_ERROR_DATA] = isset($response[MSG_ERROR_DATA]) ? $response[MSG_ERROR_DATA] : [];

				die(json_encode($return_resp_data));
			}else{
				$respType = 0;
				$respMsg = 'You have some form errors. Please check below!';
			}

			$ag_fc_generate_data = $validate[MSG_DATA];
			$error_data = (isset($validate[MSG_ERROR_DATA])) ? $validate[MSG_ERROR_DATA] : [];
			die(json_encode([
				MSG_RESULT => $respType,
				MSG_MSG => $respMsg,
				MSG_DATA => $ag_fc_generate_data,
				MSG_ERROR_DATA => $error_data
			]));
		}else{
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'Your request is wrong!'
			]));
		}
	}

	private function generate_ag_fc($post_data){

	}

	private function save_ag_fc($post_data){

	}
}