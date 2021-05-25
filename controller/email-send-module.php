<?php
class EmailSendModule extends Controller{

    public function __construct(){
        parent::__construct();
    }

    public function setPostData($data=''){
        $emailObj = new stdClass();
        $emailObj->to = !empty($data['to']) ? trim($data['to']) : '';
        $emailObj->cc = !empty($data['cc']) ? trim($data['cc'] ): '';
        $emailObj->bcc = !empty($data['bcc']) ? trim($data['bcc']) : '';
        $emailObj->subject = !empty($data['subject']) ? trim($data['subject']) : '';
        $emailObj->body = !empty($data['body']) ? trim($data['body']) : '';
        $emailObj->tstamp = time();
        return $emailObj;
    }

    public function actionEmail(){
        include_once('conf.email.php');
        include_once('model/MEmailSendModule.php');
        $err = '';
        $errType = 1;

        if ($this->getRequest()->isPost()) {
            $emailObj = $this->setPostData($_POST);
            $err = $this->validatePostData($emailObj);

            if (empty($err)) {
                $emailModel = new MEmailSendModule();
                $emailObj->from = $from_email;
                if ($emailModel->Send($emailObj, $_FILES['att_file'])) {
                    $errType = 0;
                    $url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
                    $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
                }
            }
        } else {
            $emailObj = $this->setPostData('');
        }
        $data['mainobj'] = $emailObj;
        $data['attachment_save_path'] = $attachment_save_path;
        $data['errMsg'] = $err;
        $data['errType'] = $errType;
        $data['pageTitle'] = 'Email';
        $data['side_menu_index'] = 'email';
        $this->getTemplate()->display('email_send_module', $data);
    }

    function validatePostData($data) {
        if (empty($data->to)) return 'Owner\'s Email is required';
        if (empty($data->subject)) return 'Subject is required';
        if (empty($data->body)) return 'Email body is required';

        if (!empty($data->to)) {
            $emails = explode(",",$data->to);
            foreach ($emails as $email){
                if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)){
                    return 'Provide valid email address';
                }
            }
        }
        return '';
    }

    function actionUploadEmailImage() {
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
                $ticket_id = !empty($_REQUEST['tid']) ? $_REQUEST['tid'] : "";
                $mail_id = !empty($_REQUEST['mid']) ? $_REQUEST['mid'] : "";
                $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : "";
                $attachment_save_path = !empty($_REQUEST['attachment_save_path']) ? $_REQUEST['attachment_save_path'] : "";

                if ( !empty($type) || (!empty($ticket_id) && !empty($mail_id)) ) {
                    $data = $this->CreateSummerNoteImagePath($ticket_id, $mail_id, $_FILES, $type, $extension, $attachment_save_path);
                    if (!empty($data) && $data != "error-1" && is_array($data)){
                        $res->result = true;
                        $res->url = $this->getTemplate()->read_email_body_image."?".$data['return_path'];
                        $res->name = $data['name'];
                    } elseif (!empty($data) && $data == "error-1") {
                        $res->msg = 'Total Image Upload not more than 10MB';
                    }
                }
            }
        }

        echo json_encode($res);
        exit();
    }

    function CreateSummerNoteImagePath($ticket_id, $sl, $file, $type, $extension, $attachment_save_path=null, $debug=false){
        $ten_MB = 10;
        include_once('conf.email.php');
        $now = time();
        $sl +=1;
        $sl = sprintf('%03d', $sl);
        $yy = date("y", $now);
        $mm = date("m", $now);
        $dd = date("d", $now);
        // var_dump($attachment_save_path);

        $dir = $attachment_save_path.'/'.$yy.$mm.'/'.$dd.'/'.$ticket_id.'/'.$sl.'/';
        $dir = !empty($type) ? $create_email_image_save_path.'/'.$yy.$mm.'/'.$dd.'/' : $dir;
        // var_dump($dir);

        // if (!is_dir($dir)) mkdir($dir, 0750, true);
        if(isset($file['file']) && $file['file']['error'] == 0) {
            $fname = $file['file']['name'];
            $fname = !empty($type) ? $now.'.'.$extension : $file['file']['name'];
            $mid = str_replace('.','_', microtime(true));
            $fname = $mid."_".$fname;

            $current_file_size = $file['file']['size']/(1024*1024);

            //if (($this->measureFileSize($dir) + $current_file_size) < $ten_MB){
            if ($current_file_size < $ten_MB){
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
                    $return_path = "type=attachment&name=".$fname."&ym=".$yy.$mm.'&day='.$dd.'&tid='.$ticket_id."&sl=".$sl;
                    $return_path = !empty($type) ? "type=".$type."&name=".$fname."&ym=".$yy.$mm.'&day='.$dd : $return_path;
                    return ['return_path' => $return_path, 'name' => $fname];
                }
            }
            return "error-1";
        }
        return '';
    }

    function actionDeleteUploadedImage () {
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
}
