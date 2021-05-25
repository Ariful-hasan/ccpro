<?php

class Emailartemplate extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		if (empty($tid)) exit;
		include('model/MSkill.php');
		$skill_model = new MSkill();
		$templateinfo = $skill_model->getSkillById($tid);
		//var_dump($templateinfo);
		if (empty($templateinfo) || $templateinfo->qtype != 'E') exit;
		
		$this->saveService($tid, $templateinfo);
	}

	function saveService($tstamp_code, $templateinfo)
	{
		include('model/MEmailTemplate.php');
		$et_model = new MEmailTemplate();
		
		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		if ($request->isPost()) {
			$service = $this->getSubmittedService($tstamp_code);

			$service->mail_body = !empty($service->mail_body) ? nl2br(base64_encode($service->mail_body)) : '';
			$errMsg = $this->getValidationMsg($service);
			
			if (empty($errMsg)) {
				$is_success = false;
				
				$oldtemplate = $this->getInitialService($tstamp_code, $et_model);
				if ($et_model->updateAutoReplyTemplate($oldtemplate, $service, $templateinfo)) {
					$errMsg = 'Template updated successfully !!';
					$is_success = true;
				} else {
					$errMsg = 'No change found !!';
				}
				
				
				if ($is_success) {
					$errType = 0;
					$url = $this->getTemplate()->url("task=email&act=skills");					
					$this->getTemplate()->display('msg', array('pageTitle'=>'Auto Reply for Skill - ' . $templateinfo->skill_name, 'isError'=>false, 'msg'=>'Auto Reply updated successfully !!', 'redirectUri'=>$url));
					//$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
				}
			}
			
		} else {
			$service = $this->getInitialService($tstamp_code, $et_model);
            $service->mail_body =  !empty($service->mail_body) ? nl2br(base64_decode($service->mail_body)) : '';
			if (empty($service)) {
				exit;
			}
		}
		
		$data['service'] = $service;
		$data['tstamp_code'] = $tstamp_code;
		$data['templateinfo'] = $templateinfo;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = 'Auto Reply Template for - ' . $templateinfo->skill_name;
		$data['side_menu_index'] = 'email';
		$data['smi_selection'] = 'email_skills';
		$this->getTemplate()->display('email_auto_reply_template_form', $data);
	}
	
	function getInitialService($tstamp_code, $et_model)
	{
		$service = null;
		$service = $et_model->getAutoReplyTemplateBySkill($tstamp_code);
		if (empty($service)) {
			$et_model->addAutoReplyTemplate($tstamp_code);
			$service = $et_model->getAutoReplyTemplateBySkill($tstamp_code);
		}

		return $service;
	}

	function getSubmittedService($tstamp_code)
	{
		$posts = $this->getRequest()->getPost();
		$service = new stdClass();
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$service->$key = trim($val);
			}
		}

		//if (!empty($tstamp_code)) $service->disposition_code = $tstamp_code;

		return $service;
	}

	function getValidationMsg($service)
	{
		if (empty($service->mail_body)) return "Provide template text";
		
		return '';
	}

    function actionUploadAutoReplyImage(){
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
                $skill_id = !empty($_REQUEST['tid']) ? $_REQUEST['tid'] : "";
                if (!empty($skill_id) && !empty($skill_id)){
                    $data = $this->CreateAutoReplyImagePath($skill_id, $_FILES);
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

    function CreateAutoReplyImagePath($skill_id, $file, $debug=false){
        $MAX_MB = 1;
        include('conf.email.php');
        $now = time();
        $yy = date("y", $now);
        $mm = date("m", $now);
        $dd = date("d", $now);
        $dir = $auto_reply_image_save_path.'/'.$yy.$mm.'/'.$dd.'/'.$skill_id.'/';

        if(isset($file['file']) && $file['file']['error'] == 0) {
            $fname = $file['file']['name'];
            $fname = !empty($type) ? $now.'.'.$extension : $file['file']['name'];
            $current_file_size = $file['file']['size']/(1024*1024);
            if ($current_file_size < $MAX_MB){
                $url = $this->getTemplate()->upload_email_body_image;
                if (function_exists('curl_file_create')) {
                    $cFile = curl_file_create($file['file']['tmp_name']);
                } else {
                    $cFile = '@' . realpath($file['file']['tmp_name']);
                }
                $post = array('new_filename' => $fname, 'dir' => $dir,'file_contents'=> $cFile);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,$url);
                curl_setopt($ch, CURLOPT_POST,1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1);
                $result=curl_exec($ch);
                curl_close ($ch);
                $result = json_decode($result);
                if ($result){
                    $return_path = "type=autoreply&name=".$fname."&ym=".$yy.$mm.'&day='.$dd.'&tid='.$skill_id;
                    return ['return_path' => $return_path, 'name' => $fname];
                }
            }
            return "error-1";
        }
        return '';
    }

    function actionDeleteAutoReplyImage (){
        $response = false;
        $src = '';
        $img_src = !empty($_REQUEST['img_src']) ? urldecode($_REQUEST['img_src']) : "";
        $img_src = explode('?', $img_src);
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

    /*function CreateAutoReplyImagePath($skill_id, $file, $debug=false){
        $MAX_MB = 1;
        include('conf.email.php');

        $dir = $auto_reply_image_save_path . '/' . $skill_id  . '/';
        if (!is_dir($dir)) mkdir($dir, 0750, true);
        if(isset($file['file']) && $file['file']['error'] == 0){
            $fname = $file['file']['name'];
            $current_file_size = $file['file']['size']/(1024*1024);
            if ( $current_file_size <= $MAX_MB){
                if (move_uploaded_file($file['file']['tmp_name'], $dir . $fname)){
                    return $dir.$fname;
                }
            }else {
                return "error-1";
            }
        }
        return '';
    }*/

//    function actionDeleteAutoReplyImage (){
//        $response = false;
//        $src = '';
//        $img_src = !empty($_REQUEST['img_src']) ? urldecode($_REQUEST['img_src']) : "";
//        $links = explode('/', $img_src);
//        if (!empty($links)){
//            $position = array_search('content', $links);
//            $slice_array = array_slice($links, $position);
//            if (!empty($slice_array)){
//                foreach ($slice_array as $key){
//                    $src .= $key.'/';
//                }
//            }
//
//        }
//        $src = !empty($src) ? rtrim($src,'/') : '';
//        if (unlink($src)){
//            $response = true;
//        }
//        echo json_encode($response);
//        exit();
//    }

	
}
