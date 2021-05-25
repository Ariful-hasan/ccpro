<?php

class Setlicense extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		$model = new Model();
		$data['request'] = $this->getRequest();
		if (isset($_POST['license_text']) && !empty($_POST['license_text'])) {
			if (!is_dir("license")) {
				if (!mkdir("license", 777)) {
					exit("{error:'Failed to Update License',msg:''}");
				}
			}
			if (!is_writable("./license")) exit("{error:'Failed to Update License',msg:''}");

			$license_text = $_POST['license_text'];
			$textFile = "license/gPlex_license.txt";
			$Handle = fopen($textFile, 'w');
			if (!$Handle) {
				exit("{error:'File could not open!',msg:''}");
			} else {
				fwrite($Handle, $license_text);
				fclose($Handle);
			
				$model->addToAuditLog('License', 'U', '', 'License upgraded');
				//$updated_fields = array(array('License upgraded',"",""));
				//ActivityLogManager::logInsert($login_id, "License", "U", $updated_fields, $db_manager);

				exit("{error:'',msg:'File Uploaded Successfully'}");
			}
		} else {
		
			if (!empty($_FILES["license_file"]) && $_FILES["license_file"]["error"] == 0) {
				if (!is_dir("license")) {
					if (!mkdir("license",777)) {
						 exit("{error:'Failed to Update License',msg:''}");
					}
				}
				if (!is_writable("./license")) exit("{error:'Failed to Update License',msg:''}");
				$file_name = basename( $_FILES['license_file']['name']);
				$target_path = 'license/' . $file_name;
				include('lib/FileManager.php');
				if (FileManager::save_uploaded_file('license_file', $target_path)) {
					$model->addToAuditLog('License', 'U', '', 'License upgraded');
					//$updated_fields = array(array('License upgraded',"",""));
					//ActivityLogManager::logInsert($login_id, "License", "U", $updated_fields, $db_manager);
					exit("{error:'',msg:'File Uploaded Successfully'}");
				} else {
					exit("{error:'Failed to Upload File',msg:''}");
				}
			}
		}
		
		$data['pageTitle'] = 'Upload License File';
		$data['side_menu_index'] = 'settings';
		$this->getTemplate()->display('license_upgrade', $data);
	}

}
