<?php
require_once 'BaseTableDataController.php';

class Forecasts extends BaseTableDataController
{
	public function __construct() {
		parent::__construct();
	}

	public function init()
	{
		$this->actionGenerate();
	}
	public function actionGenerate(){
		include_once('model/MForecastGenerate.php');		
		include_once('model/MForecastSkill.php');
		include_once('lib/FormValidator.php');
		include_once('model/MHisData.php');
		$forecast_generate_model = new MForecastGenerate();
		$forecast_skill_model = new MForecastSkill();
		$his_data_model = new MHisData();

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
		$last_his_data = $his_data_model->getLastHisDate($fc_generate_data->service_type);		

		$data['pageTitle'] = 'Generate Forecast';
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
		$data['last_his_data'] = date('Y/m/d', strtotime($last_his_data[0]->sdate));

		$this->getTemplate()->display('fc-generate-forecast-form', $data);
	}

	public function actionGenerateData(){
		include_once('model/MForecastGenerate.php');
		include_once('lib/FormValidator.php');
		include_once('model/MHisData.php');
		include_once('model/MDayName.php');
		include_once('model/MDaySetting.php');
		include_once('model/MForcastData.php');				
		include_once('model/MForecastSkill.php');
		include_once('model/MForecastGlobalSetting.php');
		include_once('lib/Erlang.php');
		$forecast_generate_model = new MForecastGenerate();
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
			$validate = $forecast_generate_model->validate($post_data);
			// Gprint($validate);
			// die();

			if($validate[MSG_RESULT]){
				$respType = 1;
				$respMsg = '';	
				$validate[MSG_DATA]->sdate = generic_date_format($validate[MSG_DATA]->sdate);
				$validate[MSG_DATA]->edate = generic_date_format($validate[MSG_DATA]->edate);
				$validate[MSG_DATA]->skill_id = json_decode($validate[MSG_DATA]->skill_id, true);

				// Gprint($validate[MSG_DATA]->skill_id);
				foreach ($validate[MSG_DATA]->skill_id as $key => $value) {					
					$skill_info = $forecast_skill_model->getSkill($value);
					$validate[MSG_DATA]->skill_name[$value] = $skill_info[0]->name;
					$validate[MSG_DATA]->gplex_fc_group_ids[$value] = $skill_info[0]->gplex_fc_group_ids;

					if($validate[MSG_DATA]->type=='H'){
						$validate[MSG_DATA]->fc_input_file_name[$value] = $value.'_input_file_hourly_call_count.csv';
						$validate[MSG_DATA]->fc_output_file_name[$value] = $value.'_output_file_hourly_call_count.csv';
					}elseif($validate[MSG_DATA]->type=='D'){
						$validate[MSG_DATA]->fc_input_file_name[$value] = $value.'_input_file_daily_call_count.csv';
						$validate[MSG_DATA]->fc_output_file_name[$value] = $value.'_output_file_daily_call_count.csv';
					}elseif($validate[MSG_DATA]->type=='M'){
						$validate[MSG_DATA]->fc_input_file_name[$value] = $value.'_input_file_monthly_call_count.csv';
						$validate[MSG_DATA]->fc_output_file_name[$value] = $value.'_output_file_monthly_call_count.csv';
					}
				}

				// Gprint($validate[MSG_DATA]);
				// die();
				
				$return_resp_data = [];
				if($request->getRequest('action') == 'Generate' || $request->getRequest('action') == 'Re-generate'){
					$response = $this->fc_generate($forecast_generate_model, $validate[MSG_DATA]);
					$return_resp_data['chart_data'] = $response['chart_data'];
					$return_resp_data['his_data'] = $response['his_data'];
					$return_resp_data['skill_name'] = $response['skill_name'];
					$return_resp_data['colspan'] = $response['colspan'];
				} elseif($request->getRequest('action') == 'Save'){
					$response = $this->fc_save($forecast_generate_model, $validate[MSG_DATA]);
				}  
				// elseif($request->getRequest('action') == 'View'){
					// $response = $this->fc_view($forecast_generate_model, $validate[MSG_DATA]);
				// }
				// elseif($request->getRequest('action') == 'Download'){
					// $response = $this->fc_download($forecast_generate_model, $validate[MSG_DATA]);
				// }
				else{
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

			$fc_generate_data = $validate[MSG_DATA];
			$error_data = (isset($validate[MSG_ERROR_DATA])) ? $validate[MSG_ERROR_DATA] : [];
			die(json_encode([
				MSG_RESULT => $respType,
				MSG_MSG => $respMsg,
				MSG_DATA => $fc_generate_data,
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

	private function fc_generate($model, $post_data){
		$forecast_global_settings_model = new MForecastGlobalSetting();
		$forecast_global_settings_data = $forecast_global_settings_model->getSettingsData();
		$post_data->trend = $forecast_global_settings_data['forecast_trend'];
		// Gprint($forecast_global_settings_data); 
		// Gprint($post_data);
		// die();

		// special day data 
		$sp_days = $this->get_sp_days();
		$sp_day_data = $this->get_sp_day_data();

		// forecast duration
		$his_data_model = new MHisData();
		$last_his_data = $his_data_model->getLastHisDate($post_data->service_type);	
		if($post_data->type=='H')
			$last_his_data = $his_data_model->getLastHisDateHourly($post_data->service_type);
		$f_duration = $this->get_fc_duration($post_data, $last_his_data[0]->sdate);

		// -------------------------------------- start multiple skill ------------------
		$fc_chat_data = [];
		foreach ($post_data->skill_id as $key => $skill_id) {
			// input csv file generate
			$file_path = $this->get_file_path($post_data->fc_input_file_name[$skill_id]);
			$generate_fc_csv_file = $this->generate_fc_csv_file($skill_id, $post_data, $file_path);
			// die("hourly");

			// input csv file generate
			$output_file_path = $this->get_output_file_path($post_data->fc_output_file_name[$skill_id]);			

			// json data ready for json file
			$json_file_path = $this->get_json_file_path('data.json');
			$json_data = $this->generate_json_data($post_data, $file_path, $sp_days, $sp_day_data, $f_duration);
			$json_data['output_file_src']=$output_file_path;
			// Gprint($json_data);
			// echo json_encode($json_data);
			$fp = fopen($json_file_path, 'w');
			fwrite($fp, json_encode($json_data));
			fclose($fp);

			//get forecast output value
			$python_home = $this->getTemplate()->fc_python_home_path;
			$python_file_path = $this->getTemplate()->fc_python_file_path;
			$fortel_command = "/usr/bin/cpulimit --limit=160 ".$python_home." ".$python_file_path."foretell.py";
			$fortel_output_file = $this->command($fortel_command);
			// Gprint($fortel_command);
			// Gprint($fortel_output_file);
			// die();

			$errorMsg = [];
			if(!empty($output_file_path) && file_exists($output_file_path)){
				if($post_data->type=='D'){
					$fc_chat_data = $this->getDailyForecastAndChartData($skill_id, $post_data, $output_file_path, $forecast_global_settings_data, $fc_chat_data);
				}elseif($post_data->type=='H'){
					$fc_chat_data = $this->getHourlyForecastAndChartData($skill_id, $post_data, $output_file_path, $forecast_global_settings_data, $fc_chat_data);
				}
			}else{
				$errorMsg[] = $post_data->skill_name[$skill_id];
			}
		}
		// Gprint($fc_chat_data);
		// Gprint(implode(',', $post_data->skill_name));		
		// die();
		$colspan = 0;
		if($post_data->type=='H'){
			$colspan = (count($fc_chat_data['fc_data'][0])-2)/count($post_data->skill_name);
		}elseif($post_data->type=='D'){
			$colspan = (count($fc_chat_data['fc_data'][0])-1)/count($post_data->skill_name);
		}

		if(empty($errorMsg)){
			return [
				MSG_RESULT => 1,
				MSG_DATA => $fc_chat_data['fc_data'],
				MSG_MSG => 'Forecast data has been generated successfully!',
				'chart_data' => $fc_chat_data['chart_data'],
				'his_data' =>[
					'his_sdate' => (isset($generate_fc_csv_file['sdate']) && !empty($generate_fc_csv_file['sdate'])) ? $generate_fc_csv_file['sdate'] : "",
					'his_edate' => (isset($generate_fc_csv_file['edate']) && !empty($generate_fc_csv_file['edate'])) ? $generate_fc_csv_file['edate'] : "",
				],
				'skill_name' => implode(',', $post_data->skill_name),
				'colspan' => $colspan
			];
		}else{
			return [
				MSG_RESULT => 0,
				MSG_DATA => [],
				MSG_MSG => 'Output files of '.implode(',', $errorMsg).' have not been generated successfully!'
			];
		}
		// die();
	}
	private function fc_save($model, $post_data){		
		$forecast_global_settings_model = new MForecastGlobalSetting();
		$forecast_model = new MForcastData();
		$forecast_global_settings_data = $forecast_global_settings_model->getSettingsData();

		foreach ($post_data->skill_id as $key => $skill_id) {
			$file_path = $this->getTemplate()->fc_output_file_path;
			$fortel_output_file = $file_path.$post_data->fc_output_file_name[$skill_id];
			
			// Gprint($post_data);
			// var_dump($fortel_output_file);
			// Gprint(file_exists($fortel_output_file));
			// die();

			if(!empty($fortel_output_file) && file_exists($fortel_output_file)){
				$count = 0;
				$file_content_data = file($fortel_output_file);

				if($post_data->type=='D'){
					foreach ($file_content_data as $line_num => $line) {
					    $arr_data = explode(',',$line);
						$out_date = trim($arr_data[1]);
						$out_count = trim($arr_data[2]);
						// Gprint($out_date);
					    if($count==0 || ($out_date < $post_data->sdate || $out_date > $post_data->edate)){
					    	$count++;
					    	continue;	
					    }			    
						
						$averageArrivalRate = round($out_count) / $forecast_global_settings_data['total_interval_length'];
						$volumeOfCalls = $averageArrivalRate * $forecast_global_settings_data['avg_call_duration'];
						$number_of_agents = $volumeOfCalls / ($forecast_global_settings_data['agent_occupancy'] / 100);
						
						if(!empty($forecast_global_settings_data['forecast_upper_scale'])){
							$increase_fc_data = round((round($out_count) * $forecast_global_settings_data['forecast_upper_scale'])/100);
							$increase_fc_data = round($out_count) + $increase_fc_data;
						}
						
						if(!empty($forecast_global_settings_data['forecast_lower_scale'])){
							$decrease_fc_data = round((round($out_count) * $forecast_global_settings_data['forecast_lower_scale'])/100);
							$decrease_fc_data = round($out_count) - $decrease_fc_data;
						}	

						if ($forecast_model->hasDuplicate($out_date, $skill_id, $post_data->skill_name[$skill_id])) {
		                    $forecast_model->updateForcastData($out_date, $skill_id, $post_data->skill_name[$skill_id], round($out_count), round($number_of_agents), $post_data->service_type, $increase_fc_data, $decrease_fc_data);
		                } else {
		                    $forecast_model->storeForcastData($out_date, $skill_id, $post_data->skill_name[$skill_id], round($out_count), round($number_of_agents), $post_data->service_type, $increase_fc_data, $decrease_fc_data);
		                }

						$count++;
					}
				}elseif($post_data->type=='H') {
					foreach ($file_content_data as $line_num => $line) {
					    $arr_data = explode(',',$line);
						$out_date = trim($arr_data[1]);
						$out_count = trim($arr_data[2]);
						// Gprint($out_date);

						if($count==0 || ($out_date < $post_data->sdate.' 00:00:00' || $out_date > $post_data->edate.' 23:00:00')){
					    	$count++;
					    	continue;	
					    }
					    // die();
					    $split_date = explode(" ", $out_date);
						$split_hour = explode(":", $split_date[1]);

						$erlang = new Erlang();
						$callPerHour = round($out_count);
						$averageCallDuration_Ts = $forecast_global_settings_data['avg_call_duration_hourly'];
						$interval = $forecast_global_settings_data['total_interval_length_hourly']/60;
						$wait_time = $forecast_global_settings_data['wait_time_hourly'];
						$serviceLvl = $forecast_global_settings_data['service_level_hourly']/100;
						$occupancy = $forecast_global_settings_data['agent_occupancy_hourly']/100;
						$shrinkage = $forecast_global_settings_data['shrinkage_hourly'];
						$number_of_agents = $erlang->numberOfAgentsForSL($callPerHour, $averageCallDuration_Ts, $interval, $wait_time, $serviceLvl, $occupancy, $shrinkage);
						
						if(!empty($forecast_global_settings_data['forecast_upper_scale_hourly'])){
							$increase_fc_data = round((round($out_count) * $forecast_global_settings_data['forecast_upper_scale_hourly'])/100);
							$increase_fc_data = round($out_count) + $increase_fc_data;
						}
						
						if(!empty($forecast_global_settings_data['forecast_lower_scale_hourly'])){
							$decrease_fc_data = round((round($out_count) * $forecast_global_settings_data['forecast_lower_scale_hourly'])/100);
							$decrease_fc_data = round($out_count) - $decrease_fc_data;
						}

						if ($forecast_model->hasDuplicateHourly($split_date[0], $split_hour[0], $skill_id, $post_data->skill_name[$skill_id])) {
		                    $forecast_model->updateForcastDataHourly($split_date[0], $split_hour[0], $skill_id, $post_data->skill_name[$skill_id], round($out_count), round($number_of_agents), $post_data->service_type, $increase_fc_data, $decrease_fc_data);
		                } else {
		                    $forecast_model->storeForcastDataHourly($split_date[0], $split_hour[0], $skill_id, $post_data->skill_name[$skill_id], round($out_count), round($number_of_agents), $post_data->service_type, $increase_fc_data, $decrease_fc_data);
		                }

						$count++;
					}
				}
			}
		}
		return [
			MSG_RESULT => 1,
			MSG_DATA => [],
			MSG_MSG => 'Forecast data has been saved successfully!'
		];
	}
	public function actionForecastDataDownload(){		
		include_once('model/MForecastGlobalSetting.php');				
		include_once('model/MForecastSkill.php');
		include_once('lib/Erlang.php');

		$forecast_skill_model = new MForecastSkill();
		$forecast_global_settings_model = new MForecastGlobalSetting();
		$forecast_global_settings_data = $forecast_global_settings_model->getSettingsData();

		$request = $this->getRequest();
		$sdate = generic_date_format($request->getRequest('sdate'));
		$edate = generic_date_format($request->getRequest('edate'));
		$skill_ids = explode(',', $request->getRequest('skill_ids'));
		$type = $request->getRequest('type');

		header('Content-type: text/csv');
        header('Content-Disposition: attachement; filename="Forecast_data_'.date('Y-m-d_H-i-s').".csv".'";');
        $f = fopen('php://output', 'w');
        $cols=['Date', 'Skill Name', 'Call Count']; 
        if($type=='H')
        	$cols=['Date', 'Hour', 'Skill Name', 'Call Count'];

        if($type=='D'){
			if(!empty($forecast_global_settings_data['forecast_upper_scale'])){
				$cols[count($cols)] = $forecast_global_settings_data['forecast_upper_scale'].'%'.' Increase';
			}
			if(!empty($forecast_global_settings_data['forecast_lower_scale'])){
				$cols[count($cols)] = $forecast_global_settings_data['forecast_lower_scale'].'%'.' Decrease';
			}
			$cols[count($cols)] = 'Agent Count';
            fputcsv($f, $cols, ',');
        }elseif ($type=='H') {				
			if(!empty($forecast_global_settings_data['forecast_upper_scale_hourly'])){
				$cols[count($cols)] = $forecast_global_settings_data['forecast_upper_scale_hourly'].'%'.' Increase';
			}
			if(!empty($forecast_global_settings_data['forecast_lower_scale_hourly'])){
				$cols[count($cols)] = $forecast_global_settings_data['forecast_lower_scale_hourly'].'%'.' Decrease';
			}
			$cols[count($cols)] = 'Agent Count';
            fputcsv($f, $cols, ',');
        }


		foreach ($skill_ids as $key => $skill_id) {
			$skill_info = $forecast_skill_model->getSkill($skill_id);
			$file_path = $this->getTemplate()->fc_output_file_path;
			$filename = $skill_id.'_output_file_daily_call_count.csv';
			if($type=='H')
				$filename = $skill_id.'_output_file_hourly_call_count.csv';
			$fortel_output_file = $file_path.$filename;			

			if(!empty($fortel_output_file) && file_exists($fortel_output_file)){
				$count = 0;
				$file_content_data = file($fortel_output_file);				

				if($type=='D'){
					foreach ($file_content_data as $line_num => $line) {
					    $arr_data = explode(',',$line);
						$out_date = trim($arr_data[1]);
						$out_count = trim($arr_data[2]);
					    if($count==0 || ($out_date < $sdate || $out_date > $edate)){
					    	$count++;
					    	continue;	
					    }
					    $csv_arr_data = [date('d/m/Y', strtotime($out_date)), $skill_info[0]->name, round($out_count)];

					    if(!empty($forecast_global_settings_data['forecast_upper_scale'])){
							$increase_fc_data = round((round($out_count) * $forecast_global_settings_data['forecast_upper_scale'])/100);
							$increase_fc_data = round($out_count) + $increase_fc_data;
							$csv_arr_data[count($csv_arr_data)] = $increase_fc_data;
						} 
						if(!empty($forecast_global_settings_data['forecast_lower_scale'])){
							$decrease_fc_data = round((round($out_count) * $forecast_global_settings_data['forecast_lower_scale'])/100);
							$decrease_fc_data = round($out_count) - $decrease_fc_data;
							$csv_arr_data[count($csv_arr_data)] = $decrease_fc_data;
						}
						$intensity = round($out_count) * $forecast_global_settings_data['avg_call_duration'] / $forecast_global_settings_data['total_interval_length'] ;
						$number_of_agents = $intensity / ($forecast_global_settings_data['agent_occupancy'] / 100);
						$csv_arr_data[count($csv_arr_data)] = round($number_of_agents);	
					    
						fputcsv($f, $csv_arr_data, ',');

						$count++;
					}
				}elseif ($type=='H') {
					foreach ($file_content_data as $line_num => $line) {
					    $arr_data = explode(',',$line);
						$out_date = trim($arr_data[1]);
						$out_count = trim($arr_data[2]);
					    if($count==0 || ($out_date < $sdate.' 00:00:00' || $out_date > $edate.' 23:00:00')){
					    	$count++;
					    	continue;	
					    }
					    $split_date = explode(" ", $out_date);
						$split_hour = explode(":", $split_date[1]);
					    $csv_arr_data = [date('d/m/Y', strtotime($split_date[0])), $split_hour[0], $skill_info[0]->name, round($out_count)];

					    if(!empty($forecast_global_settings_data['forecast_upper_scale_hourly'])){
							$increase_fc_data = round((round($out_count) * $forecast_global_settings_data['forecast_upper_scale_hourly'])/100);
							$increase_fc_data = round($out_count) + $increase_fc_data;
							$csv_arr_data[count($csv_arr_data)] = $increase_fc_data;
						} 
						if(!empty($forecast_global_settings_data['forecast_lower_scale_hourly'])){
							$decrease_fc_data = round((round($out_count) * $forecast_global_settings_data['forecast_lower_scale_hourly'])/100);
							$decrease_fc_data = round($out_count) - $decrease_fc_data;
							$csv_arr_data[count($csv_arr_data)] = $decrease_fc_data;
						}
						$intensity = round($out_count) * $forecast_global_settings_data['avg_call_duration_hourly'] / $forecast_global_settings_data['total_interval_length_hourly'] ;
						$number_of_agents = $intensity / ($forecast_global_settings_data['agent_occupancy_hourly'] / 100);

						$erlang = new Erlang();
						$callPerHour = round($out_count);
						$averageCallDuration_Ts = $forecast_global_settings_data['avg_call_duration_hourly'];
						$interval = $forecast_global_settings_data['total_interval_length_hourly']/60;
						$wait_time = $forecast_global_settings_data['wait_time_hourly'];
						$serviceLvl = $forecast_global_settings_data['service_level_hourly']/100;
						$occupancy = $forecast_global_settings_data['agent_occupancy_hourly']/100;
						$shrinkage = $forecast_global_settings_data['shrinkage_hourly'];
						$number_of_agents = $erlang->numberOfAgentsForSL($callPerHour, $averageCallDuration_Ts, $interval, $wait_time, $serviceLvl, $occupancy, $shrinkage);

						$csv_arr_data[count($csv_arr_data)] = round($number_of_agents);	
					    
						fputcsv($f, $csv_arr_data, ',');

						$count++;
					}
				}			
			}
		}
		
		fclose($f);
		die();
	}	

	private function generate_fc_csv_file($skill_id, $post_data, $file_path){
		$hid_data_model = new MHisData();

		if($post_data->type == 'D'){
			$result = $hid_data_model->hisDataInCsv($skill_id, $post_data);
			// var_dump($result);
	        $f = fopen($file_path, 'w');
	        fputcsv($f, ['ds', 'y'], ',');
	        foreach ($result as $key => $value) {
	        	fputcsv($f, [$value->sdate, $value->count], ',');
	        }
	        fclose($f);

	        return [
	        	'sdate'=> date('d/m/Y', strtotime($result[0]->sdate)), 
	        	'edate'=> date('d/m/Y', strtotime($result[count($result)-1]->sdate))
	        ];
	    }elseif($post_data->type == 'H'){
			$result = $hid_data_model->hourlyHisDataInCsv($skill_id, $post_data);
			// var_dump($result);
	        $f = fopen($file_path, 'w');
	        fputcsv($f, ['ds', 'y'], ',');
	        foreach ($result as $key => $value) {
	        	$sdate_hour = date_create($value->sdate.' '.$value->shour.':00:00');
	        	$sdate_hour = date_format($sdate_hour,"Y-m-d H:i:s");
	        	
	        	fputcsv($f, [trim($sdate_hour,'"'), $value->count], ',');
	        }
	        fclose($f);

	        return [
	        	'sdate'=> date('d/m/Y', strtotime($result[0]->sdate)), 
	        	'edate'=> date('d/m/Y', strtotime($result[count($result)-1]->sdate))
	        ];
	    }
	}

	private function get_file_path($file_name){
		$base_path = $this->getTemplate()->fc_input_file_path;		

		if(file_exists($base_path.'/'.$file_name))
			unlink($base_path.'/'.$file_name);

		return $base_path.'/'.$file_name;
	}
	private function get_output_file_path($file_name){
		$file_path = $this->getTemplate()->fc_output_file_path;
		if(file_exists($file_path.$file_name))
			unlink($file_path.$file_name);

		return $file_path.$file_name;
	}
	private function get_json_file_path($file_name){
		$file_path = $this->getTemplate()->fc_json_file_path;
		if(file_exists($file_path.$file_name))
			unlink($file_path.$file_name);

		return $file_path.$file_name;
	}
	private function get_sp_days(){
		$day_name = new MDayName();
		return $day_name->getDayNames('','','','Y');
	}
	private function get_sp_day_data(){
		$day_setting = new MDaySetting();
		return $day_setting->getDayDataList('', '', '', 'Y');
	}
	private function get_fc_duration($post_data, $last_his_data){
		$date_diff = date_diff(date_create($post_data->sdate), date_create($post_data->edate));
		$last_date_diff = date_diff(date_create($post_data->sdate), date_create($last_his_data));
		// var_dump($post_data->sdate);
		// var_dump($last_his_data);
		// Gprint($date_diff);
		// Gprint($last_date_diff);
		// die();
		$total_diff = $date_diff->days+$last_date_diff->days+1;

		return ($post_data->type=='H') ? $total_diff*24 : $total_diff;
	}
	private function generate_json_data($post_data, $file_path, $sp_days, $sp_day_data, $f_duration){
		$data = [
			'inp_file_src' => $file_path, 
			'model' => $post_data->model, 
			'sp_day' => [], 
			'f_duration'=> $f_duration, 
			'trend'=>$post_data->trend,
			'frequency'=> 'D',
		];

		$sp_day_arr = [];
		foreach ($sp_days as $key => $sd) {
			if(!empty($sp_day_data[$sd->type][$sd->id])){
				$sp_day_arr[$key]['category'] = $sd->type;
				$sp_day_arr[$key]['name'] = str_replace(['-',' '], '_', strtolower($sd->name));

				$date = [];
				$l_window = 0;
				$u_window = 0;
				$priority = 0;
				foreach ($sp_day_data[$sd->type][$sd->id] as $k => $value) {
					$date[]=$value['mdate'];
					$l_window = ($l_window < $value['l_window']) ? $value['l_window'] : $l_window;
					$u_window = ($u_window < $value['u_window']) ? $value['u_window'] : $u_window;
					$priority = ($priority < $value['priority']) ? $value['priority'] : $priority;
				}
				$sp_day_arr[$key]['date'] = implode(',', $date);
				$sp_day_arr[$key]['l_window'] = $l_window > 0 ? -$l_window : $l_window;
				$sp_day_arr[$key]['u_window'] = $u_window;
				$sp_day_arr[$key]['priority'] = $priority;
			}
		}
		// Gprint($sp_day_arr);
		// Gprint(array_values($sp_day_arr));
		// die();
		$data['sp_day'] = array_values($sp_day_arr);
		if($post_data->type=='H')
			$data['frequency'] = 'H';

		return $data;
	}

	private function command($cmd) {
	    $return = exec($cmd);
	    return $return;
	}

	private function getDailyForecastAndChartData($skill_id, $post_data, $output_file_path, $forecast_global_settings_data, $old_fc_data){
		$count = 0;
		if(isset($old_fc_data['fc_data'][0]) && !empty($old_fc_data['fc_data'][0])){
			$old_fc_data['fc_data'][0] = array_merge($old_fc_data['fc_data'][0], ['count_'.$skill_id => 'Call Count']);

			if(!empty($forecast_global_settings_data['forecast_upper_scale'])){
				$old_fc_data['fc_data'][0]['increase_'.$skill_id] = $forecast_global_settings_data['forecast_upper_scale'].'%'.' Increase';
			}
			if(!empty($forecast_global_settings_data['forecast_lower_scale'])){
				$old_fc_data['fc_data'][0]['decrease_'.$skill_id] = $forecast_global_settings_data['forecast_lower_scale'].'%'.' Decrease';
			}
			$old_fc_data['fc_data'][0] = array_merge($old_fc_data['fc_data'][0], ['agents_'.$skill_id => 'Agent Count']);
		}else{
			$old_fc_data['fc_data'][0] = ['date'=>'Date', 'count_'.$skill_id =>'Call Count'];
			if(!empty($forecast_global_settings_data['forecast_upper_scale'])){
				$old_fc_data['fc_data'][0]['increase_'.$skill_id] = $forecast_global_settings_data['forecast_upper_scale'].'%'.' Increase';
			}
			if(!empty($forecast_global_settings_data['forecast_lower_scale'])){
				$old_fc_data['fc_data'][0]['decrease_'.$skill_id] = $forecast_global_settings_data['forecast_lower_scale'].'%'.' Decrease';
			}
			$old_fc_data['fc_data'][0]['agents_'.$skill_id] = 'Agent Count';
		}

		$chart_labels = [];
		$chart_data = [];
		$chart_min_data = 10000000;
		$chart_max_data = 0;
		$file_content_data = file($output_file_path);
		$fc_count = 1;
		// Gprint($file_content_data);
		// die();

		foreach ($file_content_data as $line_num => $line) {
			$arr_data = explode(',',$line);
			$out_date = trim($arr_data[1]);
			$out_count = trim($arr_data[2]);
		    if($count==0 || ($out_date < $post_data->sdate || $out_date > $post_data->edate)){
		    	$count++;
		    	continue;	
		    }
		    
			$intensity = round($out_count) * $forecast_global_settings_data['avg_call_duration'] / $forecast_global_settings_data['total_interval_length'];
			$number_of_agents = $intensity / ($forecast_global_settings_data['agent_occupancy'] / 100);

			if(isset($old_fc_data['fc_data'][$fc_count]) && !empty($old_fc_data['fc_data'][$fc_count])){
				$old_fc_data['fc_data'][$fc_count] = array_merge($old_fc_data['fc_data'][$fc_count], ['count_'.$skill_id => round($out_count)]);
			}else{
				$old_fc_data['fc_data'][$fc_count] = [
					'date_'.$skill_id => date('d/m/Y', strtotime($out_date)),
					'count_'.$skill_id => round($out_count)
				];
			}		    

			if(!empty($forecast_global_settings_data['forecast_upper_scale'])){
				$increase_fc_data = round((round($out_count) * $forecast_global_settings_data['forecast_upper_scale'])/100);
				$old_fc_data['fc_data'][$fc_count] = array_merge($old_fc_data['fc_data'][$fc_count], ['increase_'.$skill_id => (round($out_count) + $increase_fc_data)]);

				$chart_data[$post_data->skill_name[$skill_id]."(".$forecast_global_settings_data['forecast_upper_scale']."% Increase)"][]= round($out_count) + $increase_fc_data;
			} 
			if(!empty($forecast_global_settings_data['forecast_lower_scale'])){
				$decrease_fc_data = round((round($out_count) * $forecast_global_settings_data['forecast_lower_scale'])/100);
				$old_fc_data['fc_data'][$fc_count] = array_merge($old_fc_data['fc_data'][$fc_count], ['decrease_'.$skill_id => (round($out_count) - $decrease_fc_data)]);

				$chart_data[$post_data->skill_name[$skill_id]."(".$forecast_global_settings_data['forecast_lower_scale']."% Decrease)"][]= round($out_count) - $decrease_fc_data;
			}

			if(isset($old_fc_data['fc_data'][$fc_count]) && !empty($old_fc_data['fc_data'][$fc_count])){
				$old_fc_data['fc_data'][$fc_count] = array_merge($old_fc_data['fc_data'][$fc_count], ['agents_'.$skill_id => round($number_of_agents)]);
			}else{
				$old_fc_data['fc_data'][$fc_count] = [
					'agents_'.$skill_id => round($number_of_agents)
				];
			}	

			$chart_labels[]= date('d/m/Y', strtotime($out_date));
			$chart_data[$post_data->skill_name[$skill_id]][]= round($out_count);
			// $chart_max_data = ($chart_max_data < trim($arr_data[2])) ? trim($arr_data[2]) : $chart_max_data;
			// $chart_min_data = ($chart_min_data > trim($arr_data[2])) ? trim($arr_data[2]) : $chart_min_data;
			
			$count++;
			$fc_count++;
		}
		$old_fc_data['chart_data']['labels'] = $chart_labels;		
		$old_fc_data['chart_data']['data'] = (isset($old_fc_data['chart_data']['data']) && !empty($old_fc_data['chart_data']['data'])) ? array_merge($old_fc_data['chart_data']['data'], $chart_data) : $chart_data;
		// $old_fc_data['chart_data']['min'] = $chart_min_data;
		// $old_fc_data['chart_data']['max'] = $chart_max_data;

		return $old_fc_data;
		// return [
		// 	'fc_data' => $fc_data,
		// 	'chart_data' => [
		// 		'labels' => $chart_labels,
		// 		'data' => $chart_data,
		// 		'min' => $chart_min_data,
		// 		'max' => $chart_max_data
		// 	]
		// ];
	}

	private function getHourlyForecastAndChartData($skill_id, $post_data, $output_file_path, $forecast_global_settings_data, $old_fc_data){
		$count = 0;
		if(isset($old_fc_data['fc_data'][0]) && !empty($old_fc_data['fc_data'][0])){
			$old_fc_data['fc_data'][0] = array_merge($old_fc_data['fc_data'][0], ['count_'.$skill_id => 'Call Count']);

			if(!empty($forecast_global_settings_data['forecast_upper_scale_hourly'])){
				$old_fc_data['fc_data'][0]['increase_'.$skill_id] = $forecast_global_settings_data['forecast_upper_scale_hourly'].'%'.' Increase';
			}
			if(!empty($forecast_global_settings_data['forecast_lower_scale_hourly'])){
				$old_fc_data['fc_data'][0]['decrease_'.$skill_id] = $forecast_global_settings_data['forecast_lower_scale_hourly'].'%'.' Decrease';
			}
			$old_fc_data['fc_data'][0] = array_merge($old_fc_data['fc_data'][0], ['agents_'.$skill_id => 'Agent Count']);
		}else{
			$old_fc_data['fc_data'][0] = ['date'=>'Date', 'hour' => 'Hour', 'count_'.$skill_id =>'Call Count'];
			if(!empty($forecast_global_settings_data['forecast_upper_scale_hourly'])){
				$old_fc_data['fc_data'][0]['increase_'.$skill_id] = $forecast_global_settings_data['forecast_upper_scale_hourly'].'%'.' Increase';
			}
			if(!empty($forecast_global_settings_data['forecast_lower_scale_hourly'])){
				$old_fc_data['fc_data'][0]['decrease_'.$skill_id] = $forecast_global_settings_data['forecast_lower_scale_hourly'].'%'.' Decrease';
			}
			$old_fc_data['fc_data'][0]['agents_'.$skill_id] = 'Agent Count';
		}

		$chart_labels = [];
		$chart_data = [];
		$chart_min_data = 10000000;
		$chart_max_data = 0;
		$file_content_data = file($output_file_path);
		$fc_count = 1;
		// Gprint($file_content_data);
		// die();

		foreach ($file_content_data as $line_num => $line) {
			$arr_data = explode(',',$line);
			$out_date = trim($arr_data[1]);
			$out_count = trim($arr_data[2]);
		    if($count==0 || ($out_date < $post_data->sdate.' 00:00:00' || $out_date > $post_data->edate.' 23:00:00')){
		    	$count++;
		    	continue;	
		    }
		    
			// $intensity = round($out_count) * $forecast_global_settings_data['avg_call_duration_hourly'] / $forecast_global_settings_data['total_interval_length_hourly'];
			// $number_of_agents = $intensity / ($forecast_global_settings_data['agent_occupancy_hourly'] / 100);
			$split_date = explode(" ", $out_date);
			$split_hour = explode(":", $split_date[1]);

			$erlang = new Erlang();
			$callPerHour = round($out_count);
			$averageCallDuration_Ts = $forecast_global_settings_data['avg_call_duration_hourly'];
			$interval = $forecast_global_settings_data['total_interval_length_hourly']/60;
			$wait_time = $forecast_global_settings_data['wait_time_hourly'];
			$serviceLvl = $forecast_global_settings_data['service_level_hourly']/100;
			$occupancy = $forecast_global_settings_data['agent_occupancy_hourly']/100;
			$shrinkage = $forecast_global_settings_data['shrinkage_hourly'];

			$number_of_agents = $erlang->numberOfAgentsForSL($callPerHour, $averageCallDuration_Ts, $interval, $wait_time, $serviceLvl, $occupancy, $shrinkage);	
			// Gprint($number_of_agents);
			// die();

			if(isset($old_fc_data['fc_data'][$fc_count]) && !empty($old_fc_data['fc_data'][$fc_count])){
				$old_fc_data['fc_data'][$fc_count] = array_merge($old_fc_data['fc_data'][$fc_count], ['count_'.$skill_id => round($out_count)]);
			}else{
				$old_fc_data['fc_data'][$fc_count] = [
					'date_'.$skill_id => date('d/m/Y', strtotime($split_date[0])),
					'hour_'.$skill_id => $split_hour[0],
					'count_'.$skill_id => round($out_count)
				];
			}		    

			if(!empty($forecast_global_settings_data['forecast_upper_scale_hourly'])){
				$increase_fc_data = round((round($out_count) * $forecast_global_settings_data['forecast_upper_scale_hourly'])/100);
				$old_fc_data['fc_data'][$fc_count] = array_merge($old_fc_data['fc_data'][$fc_count], [round($out_count) + $increase_fc_data]);

				$chart_data[$post_data->skill_name[$skill_id]."(".$forecast_global_settings_data['forecast_upper_scale_hourly']."% Increase)"][]= round($out_count) + $increase_fc_data;
			} 
			if(!empty($forecast_global_settings_data['forecast_lower_scale_hourly'])){
				$decrease_fc_data = round((round($out_count) * $forecast_global_settings_data['forecast_lower_scale_hourly'])/100);
				$old_fc_data['fc_data'][$fc_count] = array_merge($old_fc_data['fc_data'][$fc_count], [round($out_count) - $decrease_fc_data]);

				$chart_data[$post_data->skill_name[$skill_id]."(".$forecast_global_settings_data['forecast_lower_scale_hourly']."% Decrease)"][]= round($out_count) - $decrease_fc_data;
			}

			if(isset($old_fc_data['fc_data'][$fc_count]) && !empty($old_fc_data['fc_data'][$fc_count])){
				$old_fc_data['fc_data'][$fc_count] = array_merge($old_fc_data['fc_data'][$fc_count], ['agents_'.$skill_id => round($number_of_agents)]);
			}else{
				$old_fc_data['fc_data'][$fc_count] = [
					'agents_'.$skill_id => round($number_of_agents)
				];
			}

			$chart_labels[]= date('d/m/Y', strtotime($split_date[0])).' ('.$split_hour[0].')';
			$chart_data[$post_data->skill_name[$skill_id]][]= round($out_count);
			// $chart_max_data = ($chart_max_data < trim($arr_data[2])) ? trim($arr_data[2]) : $chart_max_data;
			// $chart_min_data = ($chart_min_data > trim($arr_data[2])) ? trim($arr_data[2]) : $chart_min_data;
			
			$count++;
			$fc_count++;
		}
		$old_fc_data['chart_data']['labels'] = $chart_labels;
		$old_fc_data['chart_data']['data'] = (isset($old_fc_data['chart_data']['data']) && !empty($old_fc_data['chart_data']['data'])) ? array_merge($old_fc_data['chart_data']['data'], $chart_data) : $chart_data;
		// $old_fc_data['chart_data']['min'] = $chart_min_data;
		// $old_fc_data['chart_data']['max'] = $chart_max_data;
		
		return $old_fc_data;
	}

	public function actionForecastDeviation(){		
		include_once('model/MForecastSkill.php');
		include_once('model/MForcastData.php');
		$forecast_skill_model = new MForecastSkill();
		$forecast_model = new MForcastData();

		$service_type_list = fc_service_type_list();
		$generate_type_list = fc_generate_type_list();
		$fc_skill_list = $forecast_skill_model->getFcSkillList();
		$min_date = $forecast_model->getMinDate();
		$min_date_hourly = $forecast_model->getMinDateHourly();

		$request = $this->getRequest();
		$fc_deviation_data = new stdclass();
		$fc_deviation_data->service_type = 'V';	

		$data['pageTitle'] = 'Forecast Deviation';
		$data['dataUrl'] = $this->url('task=forecasts&act=generate');
		$data['userColumn'] = "Generate Forecast";
		$data['request'] = $this->getRequest();
		$data['fc_skill_list'] = $fc_skill_list;
		$data['service_type_list'] = $service_type_list;
		$data['generate_type_list'] = $generate_type_list;
		$data['fc_deviation_data'] = $fc_deviation_data;
		$data['min_date'] = date('Y/m/d', strtotime($min_date));
		$data['min_date_hourly'] = date('Y/m/d', strtotime($min_date_hourly));

		$this->getTemplate()->display('fc-forecast-deviation', $data);
	}

	public function actionForecastDeviationData(){
		include_once('model/MForecastDeviation.php');
		include_once('lib/FormValidator.php');
		include_once('model/MForecastSkill.php');
		include_once('model/MForcastData.php');
		$forecast_deviation_model = new MForecastDeviation();
		$forecast_skill_model = new MForecastSkill();		
		$forecast_model = new MForcastData();		

		$request = $this->getRequest();
		$respType = '';
		$respMsg = '';
		$error_data = '';
		$fc_deviation_data = new stdclass();
		$fc_deviation_data->service_type = 'V';

		if ($request->isPost()) {
			$post_data = $this->getRequest()->getPost();
			$validate = $forecast_deviation_model->validate($post_data);

			if($validate[MSG_RESULT]){
				$respType = 1;
				$respMsg = '';	
				$validate[MSG_DATA]->sdate = generic_date_format($validate[MSG_DATA]->sdate);
				$validate[MSG_DATA]->edate = generic_date_format($validate[MSG_DATA]->edate);
				$validate[MSG_DATA]->skill_id = json_decode($validate[MSG_DATA]->skill_id, true);

				// Gprint($validate[MSG_DATA]->skill_id);
				foreach ($validate[MSG_DATA]->skill_id as $key => $value) {					
					$skill_info = $forecast_skill_model->getSkill($value);
					$validate[MSG_DATA]->skill_name[$value] = $skill_info[0]->name;
					$validate[MSG_DATA]->gplex_fc_group_ids[$value] = $skill_info[0]->gplex_fc_group_ids;
				}

				// Gprint($validate[MSG_DATA]);
				// Gprint($skill_info);
				// die();
				
				$return_resp_data = [];
				if($request->getRequest('action') == 'View' || $request->getRequest('action') == 'Re-generate'){
					$form_data = $validate[MSG_DATA];
					$data = [];
					$og_data = [];
					$header_col = [];
					$sub_header_col[] = 'Date';
					if($form_data->type=='H')
						$sub_header_col[] = 'Hour';

					foreach ($form_data->skill_id as $key => $skill_id) {
						$forecast_rgb[$skill_id] = $forecast_model->getForcastDataForChart($form_data, $skill_id);

						if(!empty($form_data->gplex_fc_group_ids[$skill_id])){
							$gplex_fc_skill_id = explode(",", $form_data->gplex_fc_group_ids[$skill_id]);
							$gplex_fc_skill_id = "'".implode("','", $gplex_fc_skill_id)."'";
							$gplex_fc_skill_info = $forecast_skill_model->getFcSkillListForOrinalSkillId($gplex_fc_skill_id);				
							$gplex_skill_id=[];
							foreach ($gplex_fc_skill_info as $key => $item) {
								$gplex_skill_id = array_merge($gplex_skill_id, explode(",", $item->gplex_his_skill_id));
							}						
							$s_id = "'".implode("','", $gplex_skill_id)."'";
							$original_data = $forecast_model->getOriginalDataForChart($form_data, $s_id);
							
							if(!empty($original_data)){
								foreach ($original_data as $key => $item) {
									if($item->sdate < date('Y-m-d'))
										$og_data[$skill_id][$item->sdate] = $item->calls_offered;
								}
							}
						}else{
							$s_id = "'".$skill_id."'";
							$original_data = $forecast_model->getOriginalDataForChart($form_data, $s_id);
							
							if(!empty($original_data)){
								foreach ($original_data as $key => $item) {
									if($item->sdate < date('Y-m-d'))
										$og_data[$skill_id][$item->sdate] = $item->calls_offered;
								}
							}
						}

						$header_col[]=$form_data->skill_name[$skill_id];

						$sub_header_col[]='Forecasted Call';
						$sub_header_col[]='Original Call';
						$sub_header_col[]='Deviation(%)';
					}
					// Gprint($forecast_rgb);
					// Gprint($og_data);
					// die();

					if(!empty($forecast_rgb)){										
						$chart_labels = [];
						$chart_data = [];
						if(!empty($form_data->sdate) && !empty($form_data->edate)){
							$sdate = $form_data->sdate;
							$count = 0;
							$total_forecast = [];
							$original_total = [];
							$avg_deviation = 0;

							if($form_data->type=='D'){
								while ($sdate <= $form_data->edate) {								
									$chart_labels[]=date('d/m/Y', strtotime($sdate));
									$data[$count]['date_'.$skill_id] = date('d/m/Y', strtotime($sdate));

									foreach ($forecast_rgb as $skill_id => $forecast_data) {
										$chart_data['Forecasted Call('.$form_data->skill_name[$skill_id].')'][]=$forecast_data[$count]->forcast_value;

										if(empty($chart_data['Original Call('.$form_data->skill_name[$skill_id].')']))
											$chart_data['Original Call('.$form_data->skill_name[$skill_id].')'] = [];

										// Gprint($sdate < date('Y-m-d'));
										// Gprint($og_data[$skill_id][$sdate]);
										// Gprint('Original Call('.$form_data->skill_name[$skill_id].')');

										if($sdate < date('Y-m-d') && (isset($og_data[$skill_id][$sdate]) && !empty($og_data[$skill_id][$sdate])))
											$chart_data['Original Call('.$form_data->skill_name[$skill_id].')'][]= $og_data[$skill_id][$sdate];
										
										$data[$count]['fc_count_'.$skill_id] = (isset($forecast_data[$count]->forcast_value) && !empty($forecast_data[$count]->forcast_value)) ? $forecast_data[$count]->forcast_value : '';
										$data[$count]['org_count_'.$skill_id] = (isset($og_data[$skill_id][$sdate]) && !empty($og_data[$skill_id][$sdate])) ? $og_data[$skill_id][$sdate] : '';

										if(!empty($forecast_data[$count]->forcast_value)){
											$data[$count]['deviation_'.$skill_id] = (isset($og_data[$skill_id][$sdate]) && !empty($og_data[$skill_id][$sdate])) ? (abs(round((($og_data[$skill_id][$sdate]-$forecast_data[$count]->forcast_value)/$og_data[$skill_id][$sdate]), 2)) * 100).'%' : '';
											
											if(!empty($og_data[$skill_id][$sdate])){
												$total_forecast[$skill_id] += $forecast_data[$count]->forcast_value;
												$original_total[$skill_id] += $og_data[$skill_id][$sdate];
											}

											// $deviation_total[$skill_id] += $data[$count]['deviation_'.$skill_id];
											$deviation_count++;
										}else{
											$data[$count]['deviation_'.$skill_id] = '';
										}

										// Gprint($chart_data);
									}
									$sdate = date('Y-m-d', strtotime("+1 day", strtotime($sdate)));
									$count++;
								}
							}elseif($form_data->type=='H') {
								while ($sdate <= $form_data->edate) {
									for ($i=0; $i < 24 ; $i++) {			
										$hour = (strlen($i) == 1) ? '0'.$i : $i;														
										$chart_labels[]=date('d/m/Y', strtotime($sdate)).'('.$hour.')';
										$data[$count]['date_'.$skill_id] = date('d/m/Y', strtotime($sdate));
										$data[$count]['hour_'.$skill_id] = $hour;

										foreach ($forecast_rgb as $skill_id => $forecast_data) {
											$chart_data['Forecasted Call('.$form_data->skill_name[$skill_id].')'][]=$forecast_data[$count]->forcast_value;

											if(empty($chart_data['Original Call('.$form_data->skill_name[$skill_id].')']))
												$chart_data['Original Call('.$form_data->skill_name[$skill_id].')'] = [];

											// Gprint($sdate < date('Y-m-d'));
											// Gprint($og_data[$skill_id][$sdate]);
											// Gprint('Original Call('.$form_data->skill_name[$skill_id].')');

											if($sdate < date('Y-m-d') && (isset($og_data[$skill_id][$sdate]) && !empty($og_data[$skill_id][$sdate])))
												$chart_data['Original Call('.$form_data->skill_name[$skill_id].')'][]= $og_data[$skill_id][$sdate];
											
											$data[$count]['fc_count_'.$skill_id] = (isset($forecast_data[$count]->forcast_value) && !empty($forecast_data[$count]->forcast_value)) ? $forecast_data[$count]->forcast_value : '';
											$data[$count]['org_count_'.$skill_id] = (isset($og_data[$skill_id][$sdate]) && !empty($og_data[$skill_id][$sdate])) ? $og_data[$skill_id][$sdate] : '';

											if(!empty($forecast_data[$count]->forcast_value)){
												$data[$count]['deviation_'.$skill_id] = (isset($og_data[$skill_id][$sdate]) && !empty($og_data[$skill_id][$sdate])) ? (abs(round((($og_data[$skill_id][$sdate]-$forecast_data[$count]->forcast_value)/$og_data[$skill_id][$sdate]), 2)) * 100).'%' : '';
												
												if(!empty($og_data[$skill_id][$sdate])){
													$total_forecast[$skill_id] += $forecast_data[$count]->forcast_value;
													$original_total[$skill_id] += $og_data[$skill_id][$sdate];
												}

												// $deviation_total[$skill_id] += $data[$count]['deviation_'.$skill_id];
												$deviation_count++;
											}else{
												$data[$count]['deviation_'.$skill_id] = '';
											}

											// Gprint($chart_data);
										}
										$count++;
									}
									$sdate = date('Y-m-d', strtotime("+1 day", strtotime($sdate)));
								}
							}
						}

						$deviation_total = [];
						foreach ($form_data->skill_id as $key => $skill_id) {
							// Gprint($skill_id);
							// Gprint($total_forecast[$skill_id]);
							// Gprint($original_total[$skill_id]);							
							$deviation_total[$skill_id]=(isset($original_total[$skill_id]) && !empty($original_total[$skill_id])) ? (abs(round((($original_total[$skill_id]-$total_forecast[$skill_id])/$original_total[$skill_id]), 2)) * 100).'%' : '';
						}

						// Gprint($sdate);
						// Gprint($chart_labels);
						// Gprint($chart_data);
						// Gprint($data);
						// Gprint($deviation_total);
						// die();

						// $avg_deviation = (isset($original_total) && !empty($original_total)) ? (abs(round((($original_total-$total_forecast)/$original_total), 2)) * 100).'%' : '';
						
						die(json_encode([
							MSG_RESULT => 1,
							MSG_MSG => "Deviation data and chart have been generated successfully!",
							MSG_DATA => $data,
							MSG_ERROR_DATA => [],
							'chart_data' => [
								'labels' => $chart_labels,
								'data' => $chart_data
							],
							'avg_deviation' => $avg_deviation,
							'header_col' => $header_col,
							'sub_header_col' => $sub_header_col,
							'len_header_col' => count($header_col),
							'len_sub_header_col' => count($sub_header_col),
							'deviation_total' => $deviation_total,
						]));
					}else{
						die(json_encode([
							MSG_RESULT => 0,
							MSG_MSG => "There is no forecasted data. To observe deviation please input data in forecast module.",
							MSG_DATA => [],
							MSG_ERROR_DATA => [],
							MSG_TYPE => 'Sorry'
						]));
					}
				} else{
					die(json_encode([
						MSG_RESULT => 0,
						MSG_MSG => "Your request is wrong!",
						MSG_DATA => [],
						MSG_ERROR_DATA => [],						
						MSG_TYPE => 'Error'
					]));
				}
			}else{
				$error_data = (isset($validate[MSG_ERROR_DATA])) ? $validate[MSG_ERROR_DATA] : [];
				die(json_encode([
					MSG_RESULT => 0,
					MSG_MSG => 'You have some form errors. Please check below!',
					MSG_DATA => [],
					MSG_ERROR_DATA => $error_data
				]));
			}
		}else{
			die(json_encode([
				MSG_RESULT => false,
				MSG_TYPE => MSG_ERROR,
				MSG_MSG => 'Your request is wrong!'
			]));
		}
	}

	public function actionForecastDeviationDownload(){		
		include_once('model/MForecastDeviation.php');
		include_once('lib/FormValidator.php');
		include_once('model/MForecastSkill.php');
		include_once('model/MForcastData.php');
		$forecast_deviation_model = new MForecastDeviation();
		$forecast_skill_model = new MForecastSkill();		
		$forecast_model = new MForcastData();

		$request = $this->getRequest();
		$sdate = generic_date_format($request->getRequest('sdate'));
		$edate = generic_date_format($request->getRequest('edate'));
		$skill_name = $request->getRequest('skill_name');
		$skill_ids = $request->getRequest('skill_ids');
		$skill_ids = explode(',', $skill_ids);
		$service_type = $request->getRequest('service_type');
		$type = $request->getRequest('type');

		$form_data = new stdclass();
		$form_data->sdate = $sdate;
		$form_data->edate = $edate;
		$form_data->skill_id = $skill_ids;
		$form_data->service_type = $service_type;
		$form_data->type = $type;

		foreach ($form_data->skill_id as $key => $value) {					
			$skill_info = $forecast_skill_model->getSkill($value);
			$form_data->skill_name[$value] = $skill_info[0]->name;
			$form_data->gplex_fc_group_ids[$value] = $skill_info[0]->gplex_fc_group_ids;
		}

		// Gprint($form_data);				
		header('Content-type: text/csv');
        header('Content-Disposition: attachement; filename="Forecast_Deviation_'.date('Y-m-d_H-i-s').".csv".'";');
        $f = fopen('php://output', 'w');
        $cols=['Date', 'Skill Name', 'Forecasted Call', 'Original Call', 'Deviation(%)'];
        if ($form_data->type=='H')
        	$cols=['Date', 'Hour', 'Skill Name', 'Forecasted Call', 'Original Call', 'Deviation(%)'];
        fputcsv($f, $cols, ',');

		foreach ($form_data->skill_id as $key => $skill_id) {		
			$forecast_rgb = $forecast_model->getForcastDataForChart($form_data, $skill_id);
			$data = [];
			$og_data = [];
			// Gprint($forecast_rgb);
			// die();
			
			if(!empty($form_data->gplex_fc_group_ids[$skill_id])){
				$gplex_fc_skill_id = explode(",", $form_data->gplex_fc_group_ids[$skill_id]);
				$gplex_fc_skill_id = "'".implode("','", $gplex_fc_skill_id)."'";
				$gplex_fc_skill_info = $forecast_skill_model->getFcSkillListForOrinalSkillId($gplex_fc_skill_id);				
				$gplex_skill_id=[];
				foreach ($gplex_fc_skill_info as $key => $item) {
					$gplex_skill_id = array_merge($gplex_skill_id, explode(",", $item->gplex_his_skill_id));
				}						
				$s_id = "'".implode("','", $gplex_skill_id)."'";
				$original_data = $forecast_model->getOriginalDataForChart($form_data, $_id);
				
				if(!empty($original_data)){
					foreach ($original_data as $key => $item) {
						if($item->sdate < date('Y-m-d'))
							$og_data[$item->sdate] = $item->calls_offered;
					}
				}
			}else{
				$s_id = "'".$skill_id."'";
				$original_data = $forecast_model->getOriginalDataForChart($form_data, $s_id);
				
				if(!empty($original_data)){
					foreach ($original_data as $key => $item) {
						if($item->sdate < date('Y-m-d'))
							$og_data[$item->sdate] = $item->calls_offered;
					}
				}
			}
			// Gprint($og_data);
			// die();

			if(!empty($forecast_rgb)){
				if(!empty($form_data->sdate) && !empty($form_data->edate)){
					$sdate = $form_data->sdate;
					$count = 0;

					if($form_data->type=='D'){	
						while ($sdate <= $form_data->edate) {
							$csv_date = date('d/m/Y', strtotime($sdate));
							$csv_fc_count = (isset($forecast_rgb[$count]->forcast_value) && !empty($forecast_rgb[$count]->forcast_value)) ? $forecast_rgb[$count]->forcast_value : '';
							$csv_org_count = (isset($og_data[$sdate]) && !empty($og_data[$sdate])) ? $og_data[$sdate] : '';

							if(!empty($forecast_rgb[$count]->forcast_value)){
								$csv_deviation = (isset($og_data[$sdate]) && !empty($og_data[$sdate])) ? (abs(round((($og_data[$sdate]-$forecast_rgb[$count]->forcast_value)/$og_data[$sdate]), 2)) * 100).'%' : '';
							}else{
								$csv_deviation = '';
							}

							fputcsv($f, [$csv_date, $form_data->skill_name[$skill_id], $csv_fc_count, $csv_org_count, $csv_deviation], ',');

							$sdate = date('Y-m-d', strtotime("+1 day", strtotime($sdate)));
							$count++;
						}
					}elseif ($form_data->type=='H') {
						while ($sdate <= $form_data->edate) {
							for ($i=0; $i < 24; $i++) { 
								$csv_date = date('d/m/Y', strtotime($sdate));
								$csv_hour = (strlen($i)==1) ? '0'.$i : $i;
								$csv_fc_count = (isset($forecast_rgb[$count]->forcast_value) && !empty($forecast_rgb[$count]->forcast_value)) ? $forecast_rgb[$count]->forcast_value : '';
								$csv_org_count = (isset($og_data[$sdate]) && !empty($og_data[$sdate])) ? $og_data[$sdate] : '';

								if(!empty($forecast_rgb[$count]->forcast_value)){
									$csv_deviation = (isset($og_data[$sdate]) && !empty($og_data[$sdate])) ? (abs(round((($og_data[$sdate]-$forecast_rgb[$count]->forcast_value)/$og_data[$sdate]), 2)) * 100).'%' : '';
								}else{
									$csv_deviation = '';
								}

								fputcsv($f, [$csv_date, $csv_hour, $form_data->skill_name[$skill_id], $csv_fc_count, $csv_org_count, $csv_deviation], ',');								
								$count++;
							}
							$sdate = date('Y-m-d', strtotime("+1 day", strtotime($sdate)));
						}
					}
				}
			}	
		}						
		fclose($f);
		die();
	}
}