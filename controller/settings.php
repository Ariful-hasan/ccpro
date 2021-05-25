<?php

class Settings extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		include('model/MSetting.php');
		$setting_model = new MSetting();

		/*
		$request = $this->getRequest();
		if ($request->isPost()) {
			$setting = isset($_POST['setting']) ? trim($_POST['setting']) : '';
			$msg = $this->updateGlobalSettings($setting, $setting_model);
			
			exit($msg);
		}
		*/

		$settings = $this->getDBGlobalSettings($setting_model);
		//$data['cti_types'] = array('SS7'=>'SS7','SIP'=>'SIP');
		$data['enable_disable_options'] = array('Y'=>'Enable','N'=>'Disable');
		$data['settings'] = $settings;
		$data['pageTitle'] = 'Global Settings';
		$data['side_menu_index'] = 'settings';
		if ($setting_model->getDB()->getCCType() == 'CCPRO') {
		    $this->getTemplate()->display('settings_ccpro', $data);
		} else {
		    $this->getTemplate()->display('settings_form', $data);
		}
	}
	
	
	
	function actionServer()
	{
	    include('model/MSetting.php');
	    $settings = new MSetting();
	    $request = $this->getRequest();
	    if ($request->isPost()) {
	        $active_sip_srv = isset($_REQUEST['active_sip_srv']) ? trim($_REQUEST['active_sip_srv']) : '';
	        if (in_array($active_sip_srv, array('P', 'D'))) {
	            //exit('a');
	            $url = $this->getTemplate()->url("task=settings&act=server");
	            if ($settings->updateActiveServer($active_sip_srv)) {
	                $this->getTemplate()->display('msg', array('pageTitle'=>'Server Settings', 'isError'=>false, 'msg'=>'Settings updated successfully', 'redirectUri'=>$url));
                    } else {
                        $this->getTemplate()->display('msg', array('pageTitle'=>'Server Settings', 'isError'=>true, 'msg'=>'Failed to update settings', 'redirectUri'=>$url));
	            }
	        }
	    }
	    $data['settings'] = $settings->getCCSettings();
	    $data['pageTitle'] = 'Server Settings';
	 //   $data['side_menu_index'] = 'settings';
	    $data['request'] = $request;
	    
	    $this->getTemplate()->display('settings_server', $data);
	}
	
	function actionMusic()
	{
		include('model/MMusic.php');
		include('lib/FileManager.php');
		$music_model = new MMusic();
		$errMsg = '';
		$errType = 1;
		$flid = isset($_REQUEST['flid']) ? trim($_REQUEST['flid']) : '';
		$option = isset($_REQUEST['option']) ? trim($_REQUEST['option']) : '';
		$request = $this->getRequest();

		if (!empty($option) && !empty($flid)) {
			$music_folder = $music_model->getMusicFolders($flid);
			if (!empty($music_folder)) {
				if ($option == 'folder') {
					$name = isset($_REQUEST['name']) ? trim($_REQUEST['name']) : '';
					$flinfo = $music_model->getMusicFolders($flid);
					$status = $music_model->updateFolder($flid, $name);
					if ($status) {
						$errType = 0;
						$errMsg = "Folder name updated succesfully";
						$folder_name = empty($flinfo) ? '' : $flinfo->name;
						$music_model->addToAuditLog('Music Directory', 'U', "Folder=$folder_name", "Folder=$folder_name to $name");
					} else {
						$errMsg = "No change found";
					}
				} else if ($option == 'file') {
					$del = isset($_REQUEST['del']) ? trim($_REQUEST['del']) : '';
					
					if (!empty($del)) {
						$lang = isset($_REQUEST['lang']) ? trim($_REQUEST['lang']) : '';
						$music_folder_path = $this->getTemplate()->file_upload_path . 'MUSIC/' . $flid . '/' . $lang . '/';
					
						$filepath = $music_folder_path . $del;
						if (file_exists($filepath)) {
							$result = FileManager::unlink_file($filepath);
							if ($result) {
								//require_once('cc_web_agi.php');
								//mreload();
								$errType = 0;
								$errMsg = "File <b>\"$del\"</b> removed succesfully";
								$flinfo = $music_model->getMusicFolders($flid);
								$folder_name = empty($flinfo) ? '' : $flinfo->name;
								$music_model->addToAuditLog('Music Directory', 'D', "Folder=$folder_name", "File=$del removed");
							}
						}
					} else if (isset($_GET['download'])) {
						$download = isset($_REQUEST['download']) ? trim($_REQUEST['download']) : '';
						$lang = isset($_REQUEST['lang']) ? trim($_REQUEST['lang']) : '';
						$music_folder_path = $this->getTemplate()->file_upload_path . 'MUSIC/' . $flid . '/' . $lang . '/';
						$filepath = $music_folder_path . $download;
						
						if (file_exists($filepath)) {
							header('Content-Description: File Transfer');
						    header('Content-Type: application/octet-stream');
						    header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
						    header('Content-Transfer-Encoding: binary');
						    header('Expires: 0');
						    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
						    header('Pragma: public');
						    header('Content-Length: ' . filesize($filepath));
						    ob_clean();
						    flush();
						    readfile($filepath);
						}
						exit;
					} else if (isset($_FILES['musicfile_en']['name']) || isset($_FILES['musicfile_bn']['name'])) {
						
						$is_en_file_uploaded = false;
						$is_bn_file_uploaded = false;
						$music_folder_path = $this->getTemplate()->file_upload_path . 'MUSIC/' . $flid . '/';

						if (!file_exists($music_folder_path)) exit;;

						$fileName_en = $_FILES['musicfile_en']['name'];
						$tmpName_en = $_FILES['musicfile_en']['tmp_name'];

						$fileName_bn = $_FILES['musicfile_bn']['name'];
						$tmpName_bn = $_FILES['musicfile_bn']['tmp_name'];
						
						
						if (empty($fileName_en) && empty($fileName_bn)) {
							$errMsg = "File not specified";
						} else {
						
							if (!empty($fileName_en)) {
								$filePath = $music_folder_path . 'E/' . $fileName_en;
								$result = FileManager::save_uploaded_file('musicfile_en', $filePath);
								if (!$result) {
									$errMsg = "Failed to upload file";
								} else {
									$is_en_file_uploaded = true;
								}
							}
							
							if (!empty($fileName_bn)) {
								$filePath = $music_folder_path . 'B/' . $fileName_bn;
								$result = FileManager::save_uploaded_file('musicfile_bn', $filePath);
								if (!$result) {
									$errMsg = "Failed to upload file";
								} else {
									$is_bn_file_uploaded = true;
								}
							}
						}
						
						if ($is_en_file_uploaded || $is_bn_file_uploaded) {
							$errType = 0;
							$fileName = '';
							if ($is_en_file_uploaded) $fileName .= $fileName_en;
							if ($is_bn_file_uploaded) {
								if (!empty($fileName)) $fileName .= ' & ';
								$fileName .= $fileName_bn;
							}
							
							$flinfo = $music_model->getMusicFolders($flid);
							$folder_name = empty($flinfo) ? '' : $flinfo->name;
							$music_model->addToAuditLog('Music Directory', 'A', "Folder=$folder_name", "File=$fileName uploaded");
								
							$errMsg = "File <b>\"$fileName\"</b> uploaded successfully";
						}
						
					}
				}
			}
		}
		
		$data['music_files'] = array();
		
		if (!empty($flid)) {
			$music_folder_path = $this->getTemplate()->file_upload_path . 'MUSIC/' . $flid . '/';
			$data['music_files'] = $this->readMusicDirectory($music_folder_path, 'E');
			$data['music_files'] = array_merge($data['music_files'], $this->readMusicDirectory($music_folder_path, 'B'));
		}

		//rsort($data['music_files']);
		
		
		$data['music_directories'] = $music_model->getMusicFolders();
		$data['request'] = $request;
		$data['pageTitle'] = 'Music Directory Management';
		$data['flid'] = $flid;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['side_menu_index'] = 'settings';
		$this->getTemplate()->display('settings_music', $data);
	}
	
	function readMusicDirectory($music_folder_path, $language)
	{
		if (!file_exists($music_folder_path)) {
			mkdir($music_folder_path, 0700);
		}
		
		$music_folder_path .= $language . '/';
		$music_files = array();
		if (file_exists($music_folder_path)) {
			$dir = dir($music_folder_path);
			while ($entry=$dir->read()) {
				if (substr($entry, -4) == ".mp3") {
					$fl = new stdClass();
					$fl->language = $language;
					$fl->name = $entry;
					array_push($music_files, $fl);
				}
			}
		} else {
			mkdir($music_folder_path, 0700);
		}
		return $music_files;
	}
	/*
	function actionRating()
	{
		include('model/MSetting.php');
		$setting_model = new MSetting();
		$errMsg = '';
		$request = $this->getRequest();
		if ($request->isPost()) {
			$data = $this->getRatingSettings('post', $setting_model);
			$errMsg = $this->getRatingValidationMsg($data);
			if (empty($errMsg)) {
				$value = "";
				$value .= $data['working_minute'];
				$value .= ','. $data['working_point'];
				$value .= ','. $data['talk_minute'];
				$value .= ','. $data['talk_point'];
				$value .= ','. $data['no_of_calls'];
				$value .= ','. $data['no_of_calls_point'];
				$value .= ','. $data['alarm_count'];
				$value .= ','. $data['alarm_count_point'];
				$status = $setting_model->setSetting('rating', $value);
				if ($status) {
					$errMsg = 'Policy Updated Successfully';
				} else {
					$errMsg = 'Failed to Update Policy';
				}
			}
		} else {
			$data = $this->getRatingSettings('db', $setting_model);
		}

		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['pageTitle'] = 'Point Calculating Policy';
		$data['side_menu_index'] = 'settings';
		$this->getTemplate()->display('settings_rating', $data);
	}
	*/
	
	function getRatingValidationMsg($rating)
	{
		$err = '';
		if (empty($rating['working_minute']) || !ctype_digit($rating['working_minute'])) $err = 'Provide Valid Work Minute';
		if (empty($rating['working_point']) || !ctype_digit($rating['working_point'])) $err = 'Provide Valid Work Point';
		if (empty($rating['talk_minute']) || !ctype_digit($rating['talk_minute'])) $err = 'Provide Valid Talk Minute';
		if (empty($rating['talk_point']) || !ctype_digit($rating['talk_point'])) $err = 'Provide Valid Talk Point';
		if (empty($rating['no_of_calls']) || !ctype_digit($rating['no_of_calls'])) $err = 'Provide Valid No. of Calls';
		if (empty($rating['no_of_calls_point']) || !ctype_digit($rating['no_of_calls_point'])) $err = 'Provide Valid No. of Calls Point';
		if (empty($rating['alarm_count']) || !ctype_digit($rating['alarm_count'])) $err = 'Provide Valid Alarm Number';
		if (empty($rating['alarm_count_point']) || !ctype_digit($rating['alarm_count_point'])) $err = 'Provide Valid Alarm Point';
		return $err;
	}

	function getRatingSettings($method, $setting_model)
	{
		$data = array();
		if ($method == 'db') {
			$rating_data = $setting_model->getSetting('rating');
			if (!empty($rating_data)) {
				$str_rating = $rating_data->value;
				$rating_array = explode(',', $str_rating);
				$data['working_minute'] = ($rating_array[0] != null) ? $rating_array[0] : 30;
				$data['working_point'] = isset($rating_array[1]) ? $rating_array[1] : 1;
				$data['talk_minute'] = isset($rating_array[2]) ? $rating_array[2] : 10;
				$data['talk_point'] = isset($rating_array[3]) ? $rating_array[3] : 1;
				$data['no_of_calls'] = isset($rating_array[4]) ? $rating_array[4] : 5;
				$data['no_of_calls_point'] = isset($rating_array[5]) ? $rating_array[5] : 1;
				$data['alarm_count'] = isset($rating_array[6]) ? $rating_array[6] : 1;
				$data['alarm_count_point'] = isset($rating_array[7]) ? $rating_array[7] : 3;
			}
		} else if ($method == 'post') {
			$posts = $this->getRequest()->getPost();
			if (is_array($posts)) {
				foreach ($posts as $key=>$val) {
					$data[$key] = trim($val);
				}
			}
		}
		return $data;
	}
	
	function updateGlobalSettings($setting, $setting_model)
	{
		if (!empty($setting)) {
			$value = "";
			if ($setting == 'autodial') {
				if (isset($_POST['max_concurrent_calls']) && !empty($_POST['cti_type'])) {
					$value .= $_POST['max_concurrent_calls'];
					$value .= ',' . $_POST['cti_type'];
					$status = $setting_model->setSetting($setting, $value);
					return $status ? "Update Successfull" : "Update Failed";
				}
				return "Provide valid data";
			} else if ($setting == 'voicemail') {
				if (!empty($_POST['vm_agent_dial']) && !empty($_POST['vm_agent_free'])) {
					$value .= $_POST['vm_agent_dial'];
					$value .= ',' . $_POST['vm_agent_free'];
					$status = $setting_model->setSetting($setting, $value);
					return $status ? "Update Successfull" : "Update Failed";
				}
				return "Provide valid data";
			}
		}
		return "Invalid Option";
	}
	
	function getDBGlobalSettings($setting_model)
	{
	        if ($setting_model->getDB()->getCCType() == 'CCPRO') {
	            return $setting_model->getCCSettings();
	        }
		$settings_array = $setting_model->getSetting();
		$settings = new stdClass();
		if (!empty($settings_array)) {
			foreach ($settings_array as $setting) {
				$item = $setting->item;
				$settings->$item = $setting->value;
			}
			//var_dump($settings);
			/*
			$autodial_array = explode(',', $settings->autodial);
			$settings->autodial = null;
			$settings->autodial->max_concurrent_calls = empty($autodial_array[0]) ? 0: $autodial_array[0];
			$settings->autodial->cti_type = isset($autodial_array[1]) ? $autodial_array[1] : 'SS7';

			$voicemail_array = explode(',', $settings->voicemail);
			$settings->voicemail = null;
			$settings->voicemail->vm_agent_dial = empty($voicemail_array[0]) ? 'Y' : $voicemail_array[0];
			$settings->voicemail->vm_agent_free = isset($voicemail_array[1]) ? $voicemail_array[1] : '1';

			$settings->sys_time = date('Y-m-d H:i:s');
			*/
		}
		//var_dump($settings);
		return $settings;
	}
	
	function actionSetReportDay()
	{
	    include('model/MSetting.php');
		$setting_model = new MSetting();
		$errMsg = '';
		$errType = 1;
		$data['report_day'] = 0;		
		
		$request = $this->getRequest();
		if ($request->isPost()) {
		    $repDays = isset($_POST['reportDate']) ? trim($_POST['reportDate']) : '';
		    $oldRepDays = isset($_POST['old_reportDate']) ? trim($_POST['old_reportDate']) : '';
		    
		    if ($repDays == ""){
		        $errMsg = 'Report showing day is empty!';
		    }elseif (!preg_match("/^[0-9]+$/", $repDays)){
		        $errMsg = 'Report showing day is not valid number!';
		    }elseif (strlen($repDays) > 3){
		        $errMsg = 'Report showing day is not valid!';
		    }elseif ($repDays == $oldRepDays){
		        $errMsg = 'No change to update!';
		    }else {
		        if ($setting_model->setReportDay('report_day', $repDays)){
		            $errMsg = 'Report showing days successfully updated.';
		            $url = $this->getTemplate()->url('task=settings&act=setreportday');
		            UserAuth::setReportDay($repDays);
		            $this->getTemplate()->display('msg', array('pageTitle'=>'Set Report Show Day', 'isError'=>false, 'msg'=>$errMsg, 'redirectUri'=>$url));
		        }		        
		    }		    
		    $data['report_day'] = $repDays;
		}else {
		    $settingObj = $setting_model->getSetting('report_day');
		    if (isset($settingObj->item) && $settingObj->item == "report_day"){
		        if (isset($settingObj->value) && !empty($settingObj->value)){
		            $data['report_day'] = $settingObj->value;
		        }
		    }
		}

		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Set Report Show Day';
		$data['side_menu_index'] = 'settings';
		$this->getTemplate()->display('set_report_day', $data);
	}
	
	function actionUploadlogo()
	{
	    include('lib/FileManager.php');
	    $request = $this->getRequest();
	    $errMsg = '';
	    $errType = 1;

	    if ($request->isPost()) {
	        $srv_file_name_en = 'agentImage';
	        $max_file_size = 1024*50;
	        	
	        if (!empty($_FILES[$srv_file_name_en]) && $_FILES[$srv_file_name_en]["error"] <= 0) {
                $extention = FileManager::findexts(basename( $_FILES[$srv_file_name_en]['name']));
                if ($extention != 'png') $errMsg = 'Only png file is allowed to upload';
                if($_FILES[$srv_file_name_en]['size'] > $max_file_size){
                    $errMsg = 'Please upload image smaller than 50KB';
                }
            }
	        	
	        if (empty($errMsg)) {
	            $is_success = false;
	            $pic_logo_id = 'ccowner_logo';
	            if (!empty($_FILES[$srv_file_name_en]) && $_FILES[$srv_file_name_en]["error"] <= 0) {
	                $target_path = 'agents_picture/' . $pic_logo_id . '.' . $extention;
	                if (move_uploaded_file($_FILES[$srv_file_name_en]['tmp_name'], $target_path)) $is_success = true;
	            }
	            
	            if ($is_success) {
	                $errType = 0;
	                $errMsg = 'Logo uploaded successfully !!';
	                $url = $this->getTemplate()->url("task=settings&act=uploadlogo");
	                $data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
	            }
	        }
	    }
	
	    $data['owner_logo'] = 'ccowner_logo';
	    $data['errMsg'] = $errMsg;
	    $data['errType'] = $errType;
	    $data['request'] = $this->getRequest();
	    $data['pageTitle'] = "Upload Logo";
	    $this->getTemplate()->display('ownerlogo_form', $data);
	}
	
	function actionDellogopic()
	{
	    $pic_logo_id = 'ccowner_logo';
	    $url = $this->getTemplate()->url("task=settings&act=uploadlogo");	
	    $file_name = 'agents_picture/' . $pic_logo_id . '.png';
	    if (unlink($file_name)) {
	        $this->getTemplate()->display('msg', array('pageTitle'=>'Logo Image', 'isError'=>false, 'msg'=>'Logo Image removed successfully', 'redirectUri'=>$url));
	    } else {
	        $this->getTemplate()->display('msg', array('pageTitle'=>'Logo Image', 'isError'=>true, 'msg'=>'Failed to remove Logo Image', 'redirectUri'=>$url));
	    }
	}
	
}
?>