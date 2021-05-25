<?php

class Settseats extends Controller
{
	function __construct() {
		parent::__construct();
	}

	
	function init()
	{
	    $this->actionSettSeats();
	}
	
	function actionSettSeats()
	{
	    $data['pageTitle'] = 'Seat List';
	    //$data['topMenuItems'] = array(array('href'=>'task=agents&act=add', 'img'=>'fa fa-user', 'label'=>'Add New Agent'));
	    if (UserAuth::hasRole('admin')) {
	    $data['side_menu_index'] = 'settings';
	    }
	    $this->getTemplate()->display('settings_seats', $data);
	}
	

	function actionActivate()
	{
		$seatid = isset($_REQUEST['seatid']) ? trim($_REQUEST['seatid']) : '';
		$status = isset($_REQUEST['active']) ? trim($_REQUEST['active']) : '';
		$pageNum = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
		
		if ($status == 'Y' || $status == 'N') {
			include('model/MSetting.php');
			$setting_model = new MSetting();

			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName()."&page=$pageNum");
			if ($setting_model->updateSeatStatus($seatid, $status)) {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update Seat', 'isError'=>false, 'msg'=>'Status Updated Successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update Seat', 'isError'=>true, 'msg'=>'Failed to Update Status', 'redirectUri'=>$url));
			}
		}
	}

	function actionClear()
	{
		$seatid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		$pageNum = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;

		include('model/MSetting.php');
		$setting_model = new MSetting();

		$oldseat = empty($seatid) ? null : $this->getInitialSeat($seatid, $setting_model);

		if (!empty($oldseat)) {

			$seat->seat_id = $oldseat->seat_id;
			$seat->label = $oldseat->label;
			$seat->ip = '';
			$seat->active = 'N';
			
			$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName()."&page=$pageNum");

			if ($setting_model->updateSeat($oldseat, $seat)) {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update Seat', 'isError'=>false, 'msg'=>'Seat Updated Successfully', 'redirectUri'=>$url));
			} else {
				$this->getTemplate()->display('msg', array('pageTitle'=>'Update Seat', 'isError'=>true, 'msg'=>'Failed to Update Seat', 'redirectUri'=>$url));
			}
		}
	}
	
	function actionUpdate()
	{
		$seatid = isset($_REQUEST['seatid']) ? trim($_REQUEST['seatid']) : '';
		$this->saveSeat($seatid);
	}

	function saveSeat($seatid='')
	{
		include('model/MSetting.php');
		$setting_model = new MSetting();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {

			$seat = $this->getSubmittedSeat($seatid);
			$errMsg = $this->getValidationMsg($seat, $seatid, $setting_model);

			if (empty($errMsg)) {
				$oldseat = $this->getInitialSeat($seatid, $setting_model);
				if (!empty($oldseat)) {
					$is_success = false;

					if ($setting_model->updateSeat($oldseat, $seat)) {
						$errMsg = 'Seat updated successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'No change found !!';
					}
					
					if ($is_success) {
						$errType = 0;
						$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
						$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
					}

				} else {
					$errMsg = 'Invalid Seat !!';
				}
			}

		} else {
			$seat = $this->getInitialSeat($seatid, $setting_model);
		}

		$data['seatid'] = $seatid;
		$data['seat'] = $seat;
		$data['request'] = $this->getRequest();
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = 'Update Seat : ' . $seat->label;
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'settseats_';
		$this->getTemplate()->display('settings_seat_form', $data);
	}

	function getInitialSeat($seatid, $setting_model)
	{
		$seat = null;

		$seat = $setting_model->getSeatById($seatid);
		if (empty($seat)) {
			exit;
		}
		return $seat;
	}

	function getSubmittedSeat($seatid)
	{
		$posts = $this->getRequest()->getPost();
		$seat = new stdClass();

		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$seat->$key = trim($val);
			}
		}
		if ($seat->did==$seat->forward_number) {
                    $seat->forward_number = '';
		}
		if (!isset($seat->forward_rule) || empty($seat->did) || empty($seat->forward_number)) $seat->forward_rule = '';
                
		$seat->seat_id = $seatid;

		return $seat;
	}

	function getValidationMsg($seat, $seatid='', $setting_model)
	{
		$err = '';
		
		if (empty($seat->label)) $err = 'Label is Required';
		if (!empty($seat->forward_number)) {
		        if (!preg_match("/^[0-9][0-9]{0,12}$/", $seat->forward_number)){
		                $err = 'Forward Number is Invalid';
		        }
		}
		if (!empty($seat->did)) {
		        if (!preg_match("/^[0-9][0-9]{0,12}$/", $seat->did)){
		                $err = 'DID is Invalid';
		        }
		}
		/*if( !empty($seat->ip) )	{
			$is_valid = $this->isValidateIP($seat->ip);
			if (!$is_valid)	$err = 'Invalid IP';
		}
		if (empty($err)) {
			if (empty($seat->ip) && $seat->active == 'Y') $err = 'To Activate Seat Please Provide IP';
		}
		if (empty($err) && !empty($seat->ip)) {
			$seat_by_ip = $setting_model->getSeatByIP($seat->ip);
			if (!empty($seat_by_ip)) {
				if ($seat_by_ip->seat_id != $seatid) $err = 'IP '.$seat->ip.' Already Exists';
			}
		}*/
		return $err;
	}

	function isValidateIP($ip)
	{
	   $return = true;
	   $tmp = explode(".", $ip);
	   if(count($tmp) < 4){
		  $return = false;
	   } else {
		  foreach($tmp AS $sub){
			 if($return != false){
				if(!preg_match("/^[0-9][0-9]{0,2}$/", $sub)){
				   $return = false;
				} else {
				   $return = true;
				}
			 }
		  }
	   }
	   return $return;
	}

}
