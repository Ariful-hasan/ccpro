<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(isset($_POST['caller_id']) && !empty($_POST['caller_id'])){
	$cli = trim($_POST['caller_id']);

	//include('db_conf.php');
	//include('lib/UserAuth.php');
	//db_conn();
	//$sql = "SELECT * FROM ivr_api WHERE conn_name='MNP' LIMIT 1";
	//$mnp_api = db_select($sql);

	if(strlen($cli) == 10){
		$cli = "880". $cli;
	}elseif (strlen($cli) == 11){
		$cli = "88" . $cli;
	}

	$url ="http://172.18.0.209:81/api/mnpdata/getmnpdata";
	$data = '{"UserId":"apiuser", "Password":"123456", "Msisdn":"'.$cli.'"}';
	//$data = '{"UserId":"'.$mnp_api->submit_user.'", "Password":"'.$mnp_api->submit_pass.'", "Msisdn":"'.$cli.'"}';

	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POST, true);
	//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");  //for updating we have to use PUT method.
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	  'Content-Type: application/json',
	));

	curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$header_info = curl_getinfo($ch);
	curl_close($ch);

	if ($result == false) {
	   	echo json_encode(['result_type'=>0, 'msg'=>curl_error($ch)]);
	} else {
	   	$json = json_decode($result);
	   	$brand = ['Airtel', 'Robi'];

	   	if(isset($json->IsSuccess) && !empty($json->IsSuccess)){
	   		if(isset($json->DataList[0]) && in_array($json->DataList[0]->ServiceProvider, $brand)){
	   			echo json_encode(['result_type'=>1, 'msg'=>$json->DataList[0]->ServiceProvider]);
	   		}else{
	   			echo json_encode(['result_type'=>0, 'msg'=>'API request is wrong!']);
	   		}
	   	}else{
	   		echo json_encode(['result_type'=>0, 'msg'=>'API request is wrong!']);
	   	}
	}
}else{
	echo json_encode(['result_type'=>0, 'msg'=>'Your request is wrong!']);
}
