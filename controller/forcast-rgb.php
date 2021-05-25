<?php
class ForcastRgb extends Controller
{
    function __construct() {
        parent::__construct();
    }

    function init()
    {
        $this->actionForcastRgb();
    }

    function actionForcastRgb()
    {
        $data['pageTitle'] = 'Forcast Rgb Data';

        $data['topMenuItems'] = array(
            array('href' => 'task=forcast-rgb&act=add-forcast-rgb-data&list_type=forcast', 'img' => 'fa fa-file-text-o', 'label' => 'Upload Forcast Data', 'class' => 'lightboxWIFR', 'dataattr' => array('w' => 500, 'h' => 300)),
            array('href' => 'task=forcast-rgb&act=add-forcast-rgb-data&list_type=rgb', 'img' => 'fa fa-file-text-o', 'label' => 'Upload RGB Data', 'class' => 'lightboxWIFR', 'dataattr' => array('w' => 500, 'h' => 300)));

        $data['side_menu_index'] = 'settings';
        $data['dataUrl'] = $this->url('task=get-tools-data&act=forcast-rgb');
        $data['report_date_format'] = get_report_date_format();

        $this->getTemplate()->display('forcast_rgb_data', $data);
    }

    function actionAddForcastRgbData()
    {
        include_once('model/MForcastRgb.php');
        include_once ('model/MSkill.php');

        $forcast_rgb_model = new MForcastRgb();
        $skill_model = new MSkill();
        $skill_data = $skill_model->getSkillsNamesArray();
        $data_type = $this->getRequest()->getRequest('list_type');
        $data = array();
        $data['pageTitle'] = 'Add ' . $data_type . ' Data';
        $data['list_type'] = $data_type;
        $data_uploaded = false;
        if (count($_FILES) > 0) {
            if ($_FILES["forcast_rgb_list"]["name"] == null) {
                $data['errMsg'] = "No file was uploaded";
                $data['errType'] = 0;
                $this->getTemplate()->display_popup('msg', $data);
            } else {
                $fileType = end(explode(".", $_FILES["forcast_rgb_list"]["name"]));
                $fileSize = $_FILES["forcast_rgb_list"]["size"];
                $error_data_list = array();
                if ($fileType == "csv" && $fileSize <= 10000000) { //csv file less than 10 MB size
                    if ($data_type == null) {
                        $data['errMsg'] = "Please use the correct url!";
                        $data['errType'] = 0;
                        $this->getTemplate()->display_popup('add-forcast-rgb-data', $data, true);
                    }
                    $fileHandler = fopen($_FILES["forcast_rgb_list"]["tmp_name"], "r");
                    while (!feof($fileHandler)) {
                        $result = fgetcsv($fileHandler);
                        $forcast_rgb_data = array_map("trim", $result);
                        if (sizeof($forcast_rgb_data) == 3) {
                            $date = date("Y-m-d", strtotime($forcast_rgb_data[0]));
                            $skill_name = $forcast_rgb_data[1];
                            $skill_id = $this->getSkillIdFromName($skill_data, $skill_name);
                            if ($skill_id != null) {
                                $data_uploaded = true;
                                if ($this->isDuplicate($date, $skill_id, $skill_name)) {
                                    if ($data_type == "forcast") {

                                        $forcast_value = $forcast_rgb_data[2];
                                        $forcast_rgb_model->updateForcastData($date, $skill_id, $skill_name, $forcast_value);
                                    } elseif ($data_type == "rgb") {
                                        $rgb_value = $forcast_rgb_data[2];
                                        $forcast_rgb_model->updateRgbData($date, $skill_id, $skill_name, $rgb_value);
                                    }
                                } else {
                                    if ($data_type == "forcast") {
                                        $forcast_value = $forcast_rgb_data[2];
                                        $forcast_rgb_model->storeForcastData($date, $skill_id, $skill_name, $forcast_value);
                                    } elseif ($data_type == "rgb") {
                                        $rgb_value = $forcast_rgb_data[2];
                                        $forcast_rgb_model->storeRgbData($date, $skill_id, $skill_name, $rgb_value);
                                    }
                                }
                            }else{
                                //skill not found error
                                $error_data = new stdClass();
                                $error_data->date = $date;
                                $error_data->skill_name = $skill_name;
                                $error_data->value = $forcast_rgb_data[2];
                                $error_data->error = "Skill Not Found";
                                array_push($error_data_list, $error_data);

                            }
                        }else{
                            //error data format
                            if (sizeof($forcast_rgb_data) == 0) continue;
                            $error_data = new stdClass();
                            $error_data->date = sizeof($forcast_rgb_data) == 1 ? $forcast_rgb_data[0] : null;
                            $error_data->skill_name = sizeof($forcast_rgb_data) == 2 ? $forcast_rgb_data[1] : null;
                            $error_data->value = sizeof($forcast_rgb_data) == 3 ? $forcast_rgb_data[2] : null;
                            $error_data->error = "Error Data Format";
                            array_push($error_data_list, $error_data);
                        }
                    }
                    fclose($fileHandler);
                    if ($data_uploaded) $data['errMsg'] = "File Upload Complete";
                    $data['errData'] = $error_data_list;
                    $this->getTemplate()->display_popup('add-forcast-rgb-data', $data, true);
                } else {
                    $data['errMsg'] = "Uploaded file must be csv and not bigger than 10 MB";
                    $data['errType'] = 0;
                }
            }
        }
        $this->getTemplate()->display_popup('add-forcast-rgb-data', $data, true);
    }

    private function isDuplicate($date, $skill_id, $skill_name)
    {
        $forcast_rgb_model = new MForcastRgb();
        if ($forcast_rgb_model->hasDuplicate($date, $skill_id, $skill_name) != 0) {
            return true;
        }
        return false;
    }

    private function getSkillIdFromName($skill_data, $skill_full_name)
    {
        foreach ($skill_data as $skill_id => $skill_name) {
            if ($skill_name == $skill_full_name) {
                return $skill_id;
            }
        }
        return null;
    }

}