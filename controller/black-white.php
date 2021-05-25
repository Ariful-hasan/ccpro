<?php
class blackWhite extends Controller
{
    function __construct() {
        parent::__construct();
    }

    function init()
    {
        $this->actionBlackList();
    }
    function actionBlackList()
    {
        include_once ('model/MBlackwhite.php');

        $data['pageTitle'] = 'Black List';

        $data['topMenuItems'] = array(array('href'=>'task=black-white&act=add-black-list', 'img'=>'fa fa-file-text-o', 'label'=>'Upload Black List Data','class'=>'lightboxWIFR','dataattr'=>array('w'=>500,'h'=>300)));
        $data['side_menu_index'] = 'settings';
        $data['dataUrl'] = $this->url('task=get-tools-data&act=black-list');
        $this->getTemplate()->display('black_list', $data);
    }

    function actionAddBlackList()
    {
        include_once('model/MBlackwhite.php');
        $blackwhite_model = new MBlackwhite();
        $data = array();
        $data['pageTitle'] = 'Add Black List';

        if (count($_FILES) > 0) {
            if ($_FILES["black_list"]["name"] == null) {
                $data['errMsg'] = "No file was uploaded";
                $data['errType'] = 0;
                $this->getTemplate()->display_popup('msg', $data);
            } else {
                $fileType = end(explode(".", $_FILES["black_list"]["name"]));
                $fileSize = $_FILES["black_list"]["size"];
                if ($fileType == "csv" && $fileSize <= 10000000) { //csv file less than 10 MB size
                    $fileHandler = fopen($_FILES["black_list"]["tmp_name"], "r");
                    $row_count = count(file($_FILES["black_list"]["tmp_name"]));
                    if($row_count > 20000){
                        $data['errType'] = 0;
                        $data['errMsg'] = "Error! Uploaded File Has More Than 20000 Data.";
                        $this->getTemplate()->display_popup('add-black-list', $data, true);
                    }

                    $file_black_list = array();
                    $error_black_list = array();
                    $duplicate_black_list = array();
                    while (!feof($fileHandler)) {
                        $result = fgetcsv($fileHandler);
                        $black_data = array_map("trim", $result); //trim all elements of array
                        if (sizeof($black_data) == 3) {
                            if (strlen($black_data[0]) >= 10 && strlen($black_data[0]) <= 15 && strlen($black_data[1]) > 0) {
                                    array_push($file_black_list, $black_data);
                            } else {
                                array_push($error_black_list, $black_data[0]);
                            }
                        } else {
                            if (sizeof($black_data) > 0) {
                                array_push($error_black_list, $black_data[0]);
                            }
                        }
                    }
                    fclose($fileHandler);

                    if (count($file_black_list) > 0) {
                        if($blackwhite_model->saveBlackListData($file_black_list)){
                            $duplicate_black_list = $this->removeBlackDuplicates($blackwhite_model);
                            $data['errMsg'] = "Black List Uploading Complete. Successfully Added : " . count($file_black_list) . " Data";
                            $data['errData'] = $error_black_list;
                            $data['duplicateData'] = $duplicate_black_list;
                            $this->getTemplate()->display_popup('add-black-list', $data, true);
                        }else{
                            $data['errType'] = 0;
                            $data['errMsg'] = "Black List Uploading Is Not Successful";
                            $data['errData'] = $error_black_list;
                            $this->getTemplate()->display_popup('add-black-list', $data, true);
                        }
                    } else {
                        $data['errData'] = $error_black_list;
                        $data['duplicateData'] = $duplicate_black_list;
                        $this->getTemplate()->display_popup('add-black-list', $data, true);
                    }
                } else {
                    $data['errMsg'] = "Uploaded file must be csv and not bigger than 10 MB";
                    $data['errType'] = 0;
                }
            }
        }
        $this->getTemplate()->display_popup('add-black-list', $data, true);
    }

    function actionWhiteList()
    {
        include_once ('model/MBlackwhite.php');

        $data['pageTitle'] = 'White List';

        $data['topMenuItems'] = array(array('href'=>'task=black-white&act=add-white-list', 'img'=>'fa fa-file-text-o', 'label'=>'Upload White List Data','class'=>'lightboxWIFR','dataattr'=>array('w'=>500,'h'=>300)));
        $data['side_menu_index'] = 'settings';
        $data['dataUrl'] = $this->url('task=get-tools-data&act=white-list');
        $this->getTemplate()->display('white_list', $data);
    }

    function actionAddWhiteList()
    {
        include_once('model/MBlackwhite.php');
        $blackwhite_model = new MBlackwhite();
        $data = array();
        $data['pageTitle'] = 'Add White List';
        if (count($_FILES) > 0) {
            if ($_FILES["white_list"]["name"] == null) {
                $data['errMsg'] = "No file was uploaded";
                $data['errType'] = 0;
                $this->getTemplate()->display_popup('msg', $data);
            } else {
                $fileType = end(explode(".", $_FILES["white_list"]["name"]));
                $fileSize = $_FILES["white_list"]["size"];
                if ($fileType == "csv" && $fileSize <= 10000000) { //csv file less than 10 MB size
                    $fileHandler = fopen($_FILES["white_list"]["tmp_name"], "r");
                    $row_count = count(file($_FILES["white_list"]["tmp_name"]));
                    if($row_count > 20000){
                        $data['errType'] = 0;
                        $data['errMsg'] = "Error! Uploaded File Has More Than 20000 Data.";
                        $this->getTemplate()->display_popup('add-white-list', $data, true);
                    }

                    $file_white_list = array();
                    $error_white_list = array();
                    $duplicate_white_list = array();

                    while (!feof($fileHandler)) {
                        $result = fgetcsv($fileHandler);
                        $white_data = array_map("trim", $result); //trim all elements of array

                        if (sizeof($white_data) == 3) {
                            if (strlen($white_data[0]) >= 10 && strlen($white_data[0]) <= 15 && strlen($white_data[1]) > 0) {
                                array_push($file_white_list, $white_data);
                            } else {
                                array_push($error_white_list, $white_data[0]);
                            }
                        } else {
                            if (sizeof($white_data) > 0) {
                                array_push($error_white_list, $white_data[0]);
                            }
                        }
                    }
                    fclose($fileHandler);

                    if (count($file_white_list) > 0) {
                        if ($blackwhite_model->saveWhiteListData($file_white_list)) {
                            $duplicate_white_list = $this->removeWhiteDuplicates($blackwhite_model);
                            $data['errMsg'] = "White List Uploading Complete. Successfully Added : " . count($file_white_list) . " Data";
                            $data['errData'] = $error_white_list;
                            $data['duplicateData'] = $duplicate_white_list;
                            $this->getTemplate()->display_popup('add-white-list', $data, true);
                        } else {
                            $data['errType'] = 0;
                            $data['errMsg'] = "White List Uploading Is Not Successful";
                            $data['errData'] = $error_white_list;
                            $this->getTemplate()->display_popup('add-white-list', $data, true);
                        }
                    } else {
                        $data['errData'] = $error_white_list;
                        $data['duplicateData'] = $duplicate_white_list;
                        $this->getTemplate()->display_popup('add-white-list', $data, true);
                    }
                } else {
                    $data['errMsg'] = "Uploaded file must be csv and not bigger than 10 MB";
                    $data['errType'] = 0;
                }
            }
        }
        $this->getTemplate()->display_popup('add-white-list', $data, true);
    }


    private function removeBlackDuplicates($model)
    {
        $duplicate_data = $model->getBlackDuplicateData();
        $deleted_list = array();
        foreach ($duplicate_data as $data) {
            if ($model->removeBlackDuplicates($data)) {
                $deleted_list [] = $data->cli;
            }
        }
        return $deleted_list;
    }

    private function removeWhiteDuplicates($model)
    {
        $duplicate_data = $model->getWhiteDuplicateData();
        $deleted_list = array();
        foreach ($duplicate_data as $data) {
            if ($model->removeWhiteDuplicates($data)) {
                $deleted_list [] = $data->cli;
            }
        }
        return $deleted_list;
    }
}