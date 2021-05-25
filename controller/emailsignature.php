<?php

class Emailsignature extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		include('model/MEmail.php');
		$dc_model = new MEmail();
		include('model/MSkill.php');
		$skill_model = new MSkill();

		$sid = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		
		$templateinfo = $skill_model->getSkillById($sid);
		if (empty($templateinfo) || $templateinfo->qtype != 'E') exit;
		
		$request = $this->getRequest();
		$groups = null;
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$service = $this->getSubmittedService();
			
			//var_dump($service);
			//exit;
			//$groups = $dc_model->getDispositionGroupOptions($tid);
			$dis_code = '';
			$errMsg = $this->getValidationMsg($service, $sid, $dis_code, $dc_model);
			
			if (empty($errMsg)) {
				$is_success = false;

                $service->signature_text = !empty($service->signature_text) ? base64_encode(nl2br($service->signature_text)) :'';
				$oldservice = $dc_model->getEmailSignature($sid);
				if ($dc_model->updateSignature($oldservice, $service, $templateinfo)) {
					$errMsg = 'Signature updated successfully !!';
					$is_success = true;
				} else {
					$errMsg = 'No change found !!';
				}
				
				if ($is_success) {
					$errType = 0;
					//$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&sid=$sid");
					//$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
					$url = $this->getTemplate()->url("task=email&act=skills");					
					$this->getTemplate()->display('msg', array('pageTitle'=>'Signature for Skill - ' . $templateinfo->skill_name, 'isError'=>false, 'msg'=>'Signature updated successfully !!', 'redirectUri'=>$url));
				}
			}
			
		} else {
			$service = $dc_model->getEmailSignature($sid);
            $service->signature_text = !empty($service->signature_text) ? nl2br(base64_decode($service->signature_text)) : '';
			if (empty($service)) {
				exit;
			}
		}
		
		$data['service'] = $service;
		$data['sid'] = $sid;
		//$data['groups'] = empty($groups) ? $dc_model->getDispositionGroupOptions($tid) : $groups;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = 'Signature';
		$data['pageTitle'] .= ' for Skill - ' . $templateinfo->skill_name;
		$data['side_menu_index'] = 'email';
		$data['smi_selection'] = 'email_skills';
		$this->getTemplate()->display('email_signature_form', $data);
	}
	
	function getSubmittedService()
	{
		$posts = $this->getRequest()->getPost();
		$service = new stdClass();
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$service->$key = trim($val);
			}
		}

		//if (!empty($dis_code)) $service->disposition_code = $dis_code;

		return $service;
	}

	function getValidationMsg($service, $sid, $dis_code='', $dc_model)
	{
		//if (empty($service->disposition_id)) return "Provide disposition code";
		//if (empty($service->title)) return "Provide disposition title";
		//if (!preg_match("/^[0-9a-zA-Z]{1,6}$/", $service->disposition_id)) return "Provide valid disposition code";
		//if (!preg_match("/^[0-9a-zA-Z_ ]{1,40}$/", $service->title)) return "Provide valid title";
		
		/*
		if ($service->disposition_id != $dis_code) {
			$existing_code = $dc_model->getDispositionById($service->disposition_id);
			if (!empty($existing_code)) return "Disposition code $service->disposition_id already exist";
		}
		
		
		if (!empty($service->group_id) && !empty($service->group_title)) {
			return "Please select only one option for disposition group";
		}
		
		if (!empty($service->group_id)) {
			if (!array_key_exists($service->group_id, $groups)) return "Invalid disposition group selected";
		} else if (!empty($service->group_title)) {
			if (in_array($service->group_title, $groups, true)) return "Disposition group $service->group_title already exist";
		}
		*/
		return '';
	}

    function actionUploadSignatureImage(){
        //GPrint($_FILES);die;
        $res = new stdClass();
        $res->result = false;
        $res->url = '';
        $res->msg = '';
        $allowed = array('png', 'jpg');
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($extension), $allowed)) {
                echo '{"status":"error"}';
                exit;
            } else {
                $skill_id = !empty($_REQUEST['sid']) ? $_REQUEST['sid'] : "";
                if (!empty($skill_id) && !empty($skill_id)){
                    $data = $this->CreateSignatureImagePath($skill_id, $_FILES);
                    if (!empty($data) && $data != "error-1" && is_array($data)){
                        $res->result = true;
                        $res->url = $this->getTemplate()->read_email_body_image."?".$data['return_path'];
                        $res->name = $data['name'];
                    } elseif (!empty($data) && $data == "error-1") {
                        $res->msg = 'Total Image Upload not more than 1MB';
                    }
                }
            }
        }
        echo json_encode($res);
        exit();
    }

    function CreateSignatureImagePath($skill_id, $file, $debug=false){
        $MAX_MB = 1;
        include('conf.email.php');
        $now = time();
        $yy = date("y", $now);
        $mm = date("m", $now);
        $dd = date("d", $now);

        $dir = $signature_image_save_path.'/'.$yy.$mm.'/'.$dd.'/'.$skill_id.'/';

        if(isset($file['file']) && $file['file']['error'] == 0) {
            $fname = $file['file']['name'];
            $fname = !empty($type) ? $now.'.'.$extension : $file['file']['name'];

            $current_file_size = $file['file']['size']/(1024*1024);
            
            //if (($this->measureFileSize($dir) + $current_file_size) < $ten_MB){
            if ($current_file_size < $MAX_MB){
            	$url = $this->getTemplate()->upload_email_body_image; 
				if (function_exists('curl_file_create')) {
				  	$cFile = curl_file_create($file['file']['tmp_name']);
				} else {
				  	$cFile = '@' . realpath($file['file']['tmp_name']);
				}
				// var_dump($fname);
				// var_dump($dir);
				// var_dump($cFile);
				$post = array('new_filename' => $fname, 'dir' => $dir,'file_contents'=> $cFile);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,$url);
				curl_setopt($ch, CURLOPT_POST,1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1);
				$result=curl_exec($ch);				
				// var_dump($result);
				curl_close ($ch);
				$result = json_decode($result);
				// var_dump($result);
				// die();

                if ($result){
                	$return_path = "type=signature&name=".$fname."&ym=".$yy.$mm.'&day='.$dd.'&tid='.$skill_id;
                    return ['return_path' => $return_path, 'name' => $fname];
                }
            }
            return "error-1";
        }
        return '';

        // if (!is_dir($dir)) mkdir($dir, 0750, true);
        // if(isset($file['file']) && $file['file']['error'] == 0){
        //     $fname = $file['file']['name'];
        //     $current_file_size = $file['file']['size']/(1024*1024);
        //     if ( $current_file_size <= $MAX_MB){
        //         if (move_uploaded_file($file['file']['tmp_name'], $dir . $fname)){
        //             return $dir.$fname;
        //         }
        //     }else {
        //         return "error-1";
        //     }
        // }
        return '';
    }
    function actionDeleteSignatureImage (){
        $response = false;
        $src = '';
        $img_src = !empty($_REQUEST['img_src']) ? urldecode($_REQUEST['img_src']) : "";
        $img_src = explode('?', $img_src);

        // GPrint($img_src);
        if(count($img_src)>1){
    		$url = $this->getTemplate()->delete_email_body_image.'?'.$img_src[1];
    		$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1);
			$result=curl_exec($ch);
			curl_close ($ch);
			if(!empty($result))
    			echo json_encode(true);
        }
        exit();
    }

    function actionGetEmailSignature(){
        $sid = !empty($_REQUEST['sid']) ? $_REQUEST['sid'] : '';
        include('model/MEmail.php');
        $eTicket_model = new MEmail();
        $signature = !empty($sid) ? $eTicket_model->getEmailSignature($sid) : '';
        GPrint($signature);die;
    }
	
}
