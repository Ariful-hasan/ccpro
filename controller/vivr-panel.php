<?php

class VivrPanel extends Controller
{
    public $_cresponse;

    function __construct()
    {
        parent::__construct();
    }

    function init()
    {
        $this->actionVivrDashboard();
    }

    function actionVivrDashboard()
    {
        $data['dataUrl'] = $this->url('task=get-vivr-data&act=vivrs');
        $data['pageTitle'] = 'VIVR Dashboard';
        $this->getTemplate()->display('vivrs', $data);
    }

    function actionVivrConfig()
    {
        include ("model/MVivr.php");

//        $test = new stdClass();
//        $test->name = "test";
//        $test->type = "number";
//        $test->max_length = 16;
//        $test->min_length = 4;
//        $test->max_value = '';
//        $test->min_value = '';
//
//        dd(json_encode($test));

        $vivr_model = new MVivr();
        $vivr_id = isset($_REQUEST['vivrid']) ? trim($_REQUEST['vivrid']) : '';
        $default_page = $vivr_model->getVivrDefaultPage($vivr_id);

        $vivr_page_list = array();
        if(sizeof($default_page) > 0){
            $vivr_pages = $vivr_model->getVivrPages($vivr_id);

            $default_page = reset($default_page);
            foreach ($vivr_pages as $vivr_page){
                if($vivr_page->page_id == $default_page->page_id){
                    $default_page = $vivr_page;
                    break;
                }
            }
            foreach ($vivr_pages as $vivr_page){
                array_push($vivr_page_list, $vivr_page->page_id);
            }
        } else {
            // no default page and child pages as well
        }
        $vivr_tree =  $this->getChild($default_page, $vivr_pages);

        $data['pageTitle'] = 'VIVR Pages';
        $data['root_page'] = $default_page;
        $data['vivr_pages'] = $vivr_pages;
        $data['vivr_tree'] = $vivr_tree;
        $data['vivr_id'] = $vivr_id;
        $data['vivr_page_list'] = $vivr_page_list;

        $this->getTemplate()->display('vivr_config', $data);
    }

    private function getChild($parent_page, $vivr_pages)
    {
        $has_child = $this->checkHasChild($parent_page->page_id, $vivr_pages);
        if (!$has_child) {
            $parent_page->child_node = 0;
            return $parent_page;
        }
        $child_nodes = array();

        foreach ($vivr_pages as $vivr_page) {
            if ($vivr_page->parent_page_id == $parent_page->page_id) {
                $data = $vivr_page;
                $result = $this->getChild($data, $vivr_pages);
                $child_nodes [] = $result; // child of parent
            }
        }
        $parent_page->child_node = $child_nodes;
        return $parent_page;
    }

    private function checkHasChild($page_id, $vivr_pages)
    {
        $has_child = false;
        foreach ($vivr_pages as $vivr_page) {
            if ($vivr_page->parent_page_id == $page_id) {
                $has_child = true;
                break;
            }
        }
        return $has_child;
    }

    function actionEditNode()
    {
        include("model/MVivr.php");

        $vivr_model = new MVivr();
        $vivr_id = isset($_REQUEST['vivrid']) ? trim($_REQUEST['vivrid']) : '';
        $page_id = isset($_REQUEST['page_id']) ? trim($_REQUEST['page_id']) : '';

        $error_data = null;
        if($this->getRequest()->isPost()){
            $error_data = $this->editNode();
        }
        $default_page = $vivr_model->getVivrDefaultPage($vivr_id);
        $vivr_page = $vivr_model->getVivrPageFromID($page_id);
//dd($vivr_page);
        $vivr_page_titles = $vivr_model->getVivrDispositionTitles();
        $empty_title = new stdClass();
        $empty_title->disposition_code = '';
        $empty_title->service_title = 'None';
        $vivr_page_titles = array($empty_title) + $vivr_page_titles;

        $data['vivr_page_titles'] = $vivr_page_titles;
        $data['tasks'] = array(
            "nav" => "Navigation",
            "lang" => "Language Change",
            "call" => "Call Agent"
        );
        $data['request'] = $this->getRequest();
        $data['pageTitle'] = 'VIVR Page Edit';
        $data['root_page'] = $default_page;
        $data['vivr_id'] = $vivr_id;
        $data['vivr_page'] = $vivr_page;
        $data['page_id'] = $page_id;
        $data['error_data'] = $error_data;

        $this->getTemplate()->display_popup('vivr_edit_page', $data);
    }

    private function editNode()
    {
        $vivr_model = new MVivr();

        $vivr_id = isset($_REQUEST['vivr_id']) ? trim($_REQUEST['vivr_id']) : '';
        $page_id = isset($_REQUEST['page_id']) ? trim($_REQUEST['page_id']) : '';
        $parent_page_id = isset($_REQUEST['parent_page_id']) ? trim($_REQUEST['parent_page_id']) : '';
        $page_heading_en = isset($_REQUEST['heading_en']) ? trim($_REQUEST['heading_en']) : '';
        $page_heading_bn = isset($_REQUEST['heading_bn']) ? trim($_REQUEST['heading_bn']) : '';
        $vivr_title_id = isset($_REQUEST['vivr_title']) ? trim($_REQUEST['vivr_title']) : '';
        $task = isset($_REQUEST['task']) ? trim($_REQUEST['task']) : '';
        $hase_main_page = isset($_REQUEST['hase_main_page']) ? trim($_REQUEST['hase_main_page']) : '';
        $has_previous_page = isset($_REQUEST['has_previous_page']) ? trim($_REQUEST['has_previous_page']) : '';

        $vivr_data = new stdClass();
        $vivr_data->page_id = $page_id;
        $vivr_data->parent_page_id = $parent_page_id;
        $vivr_data->vivr_id = $vivr_id;
        $vivr_data->page_heading_en = $page_heading_en;
        $vivr_data->page_heading_bn = $page_heading_bn;
        $vivr_data->task = $task;
        $vivr_data->has_main_page = $hase_main_page;
        $vivr_data->has_previous_page = $has_previous_page;

        $data = new stdClass();

        $has_updated = false;
        if ($vivr_model->editVivrPage($vivr_data)) {
            $has_updated = true;
        }
        if ($vivr_model->updateVivrPageTitle($page_id, $vivr_title_id)) {
            $has_updated = true;
        }

        if ($has_updated) {
            $data->error_code = 0;
            $data->error_msg = "VIVR Node Updated Successfully";
            return $data;
        } else {
            $data->error_code = 101;
            $data->error_msg = "Failed to update new node!";
            return $data;
        }

    }

    function actionAddNode()
    {
        include("model/MVivr.php");
        $vivr_model = new MVivr();

        $error_data = null;
        if($this->getRequest()->isPost()){
            $error_data = $this->addNewNode();
        }

        $vivr_id = isset($_REQUEST['vivrid']) ? trim($_REQUEST['vivrid']) : '';
        $page_id = isset($_REQUEST['page_id']) ? trim($_REQUEST['page_id']) : '';
        $default_page = $vivr_model->getVivrDefaultPage($vivr_id);

        $vivr_page_titles = $vivr_model->getVivrDispositionTitles();
        $empty_title = new stdClass();
        $empty_title->disposition_code = '';
        $empty_title->service_title = 'None';
        $vivr_page_titles = array($empty_title) + $vivr_page_titles;

//        GPrint("test");
//        $test = array_push($vivr_page_titles, $empty_title);

        $data['pageTitle'] = 'VIVR Page Add';
        $data['tasks'] = array(
            "nav" => "Navigation",
            "lang" => "Language Change",
            "call" => "Call Agent"
        );
        $data['vivr_page_titles'] = $vivr_page_titles;
        $data['root_page'] = $default_page;
        $data['parent_page_id'] = $page_id;
        $data['request'] = $this->getRequest();
        $data['vivr_id'] = $vivr_id;
        $data['error_data'] = $error_data;

        $this->getTemplate()->display_popup('vivr_add_page', $data);
    }

    private function addNewNode()
    {
        $vivr_model = new MVivr();

        $vivr_id = isset($_REQUEST['vivr_id']) ? trim($_REQUEST['vivr_id']) : '';
        $parent_page_id = isset($_REQUEST['parent_page_id']) ? trim($_REQUEST['parent_page_id']) : '';
        $page_heading_en = isset($_REQUEST['heading_en']) ? trim($_REQUEST['heading_en']) : '';
        $page_heading_bn = isset($_REQUEST['heading_bn']) ? trim($_REQUEST['heading_bn']) : '';
        $vivr_title_id = isset($_REQUEST['vivr_title']) ? trim($_REQUEST['vivr_title']) : '';
        $task = isset($_REQUEST['task']) ? trim($_REQUEST['task']) : '';
        $hase_main_page = isset($_REQUEST['hase_main_page']) ? trim($_REQUEST['hase_main_page']) : '';
        $has_previous_page = isset($_REQUEST['has_previous_page']) ? trim($_REQUEST['has_previous_page']) : '';
        $audio_file_en =  '';
        $audio_file_bn =  '';

        $vivr_data = new stdClass();
        $vivr_data->parent_page_id = $parent_page_id;
        $vivr_data->vivr_id = $vivr_id;
        $vivr_data->page_heading_en = $page_heading_en;
        $vivr_data->page_heading_bn = $page_heading_bn;
        $vivr_data->task = $task;
        $vivr_data->has_main_page = $hase_main_page;
        $vivr_data->has_previous_page = $has_previous_page;
        $vivr_data->audio_file_en = $audio_file_en;
        $vivr_data->audio_file_bn = $audio_file_bn;
//dd($vivr_title_id);
        $page_id = $vivr_model->addNewVivrPage($vivr_data);

        $data = new stdClass();
        if ($page_id) {
            if ($vivr_model->setVivrPageTitle($page_id, $vivr_title_id)) {
                $data->error_code = 0;
                $data->error_msg = "VIVR Node Added Successfully";
                return $data;
            }
        }
        $data->error_code = 101;
        $data->error_msg = "Failed to add new node!";
        return $data;
    }

    function actionDeleteTree()
    {
        include("model/MVivr.php");
        $vivr_model = new MVivr();

        $vivr_id = isset($_REQUEST['vivrid']) ? trim($_REQUEST['vivrid']) : '';
        $page_id = isset($_REQUEST['page_id']) ? trim($_REQUEST['page_id']) : '';

        $result = false;
        if ($vivr_model->deleteTree($page_id, $vivr_id)) {
            $vivr_model->deleteChildNodes($page_id, $vivr_id);
            $result = true;
        }

        $response = new stdClass();
        $response->success = false;
        if ($result) {
            $response->success = true;
        }
        echo json_encode($response);
        exit;
    }

    function actionNodeElements()
    {
        include("model/MVivr.php");
        $vivr_model = new MVivr();

        $page_id = isset($_REQUEST['page_id']) ? trim($_REQUEST['page_id']) : '';
        $vivr_id = isset($_REQUEST['vivrid']) ? trim($_REQUEST['vivrid']) : '';
        $page_elements = $vivr_model->getVivrPageElements($page_id);

        $data['pageTitle'] = 'VIVR Page Elements';
        $data["page_elements"] = $page_elements;
        $data['page_id'] = $page_id;
        $data['vivr_id'] = $vivr_id;
        $data['request'] = $this->getRequest();

        $this->getTemplate()->display_popup('node_elements', $data);
    }

    function actionAddElement()
    {
        include("model/MVivr.php");
        $vivr_model = new MVivr();

        $page_id = isset($_REQUEST['page_id']) ? trim($_REQUEST['page_id']) : '';
        $vivr_id = isset($_REQUEST['vivrid']) ? trim($_REQUEST['vivrid']) : '';
        $child_pages = $vivr_model->getChildNodesFromParentID($page_id);

        if ($this->getRequest()->isPost()) {
            $result = $this->addNodeElement($this->getRequest());

            if ($result) {
                $data['success'] = 100;
            } else {
                $data['success'] = 0;
            }
        }

        $data['element_types'] = array(
            "button" => "Button",
            "paragraph" => "Paragraph",
            "a" => "HyperLink",
            "table" => "Table",
            "input" => "Input"
        );

        $data['child_pages'] = $child_pages;
        $data['pageTitle'] = 'Add Page Element';
        $data['page_id'] = $page_id;
        $data['vivr_id'] = $vivr_id;
        $data['request'] = $this->getRequest();

        $this->getTemplate()->display_popup('add_vivr_element', $data);
    }

    private function addNodeElement($request){
        $vivr_model = new MVivr();

        $vivr_id = isset($_REQUEST['vivr_id']) ? trim($_REQUEST['vivr_id']) : '';
        $page_id = isset($_REQUEST['page_id']) ? trim($_REQUEST['page_id']) : '';
        $element_order = isset($_REQUEST['element_order']) ? trim($_REQUEST['element_order']) : '';
        $display_name_en = isset($_REQUEST['display_name_en']) ? trim($_REQUEST['display_name_en']) : '';
        $display_name_bn = isset($_REQUEST['display_name_bn']) ? trim($_REQUEST['display_name_bn']) : '';
        $element_type = isset($_REQUEST['element_type']) ? trim($_REQUEST['element_type']) : '';
        $background_color = isset($_REQUEST['background_color']) ? trim($_REQUEST['background_color']) : '';
        $text_color = isset($_REQUEST['text_color']) ? trim($_REQUEST['text_color']) : '';
        $element_name = isset($_REQUEST['element_name']) ? trim($_REQUEST['element_name']) : '';
        $number_of_rows = isset($_REQUEST['number_of_rows']) ? trim($_REQUEST['number_of_rows']) : '';
        $number_of_columns = isset($_REQUEST['number_of_columns']) ? trim($_REQUEST['number_of_columns']) : '';
        $is_visible = isset($_REQUEST['is_visible']) ? trim($_REQUEST['is_visible']) : '';
        $api = isset($_REQUEST['api']) ? trim($_REQUEST['api']) : '';
        $table_type = isset($_REQUEST['table_type']) ? trim($_REQUEST['table_type']) : '';

        $page_element = new stdClass();
        $page_element->page_id = $page_id;
        $page_element->type = $element_type;
        $page_element->display_name_bn = $display_name_bn;
        $page_element->display_name_en = addslashes($display_name_en);
        $page_element->background_color = $background_color;
        $page_element->text_color = $text_color;
        $page_element->name = $element_name;
        $page_element->element_order = $element_order;
        $page_element->rows = $number_of_rows;
        $page_element->columns = $number_of_columns;
        $page_element->is_visible = $is_visible;
        $page_element->api = $api;

        if ($element_type == "button") {
            if ($this->addButtonElement($vivr_model, $page_element)) {
                return true;
            }
        } else if ($element_type == "a") {
            if ($this->addHyperlinkElement($vivr_model, $page_element)) {
                return true;
            }
        } else if ($element_type == "paragraph") {
            if ($this->addParagraphElement($vivr_model, $page_element)) {
                return true;
            }
        }else if ($element_type == "table") {
            if($table_type == "static"){
                if($this->addStaticTableElement($vivr_model, $page_element)){
                    return true;
                }
            }elseif ($table_type == "dynamic"){
                if($this->addDynamicTableElement($vivr_model, $page_element)){
                    return true;
                }
            }
        }

        return false;
    }

    private function addButtonElement($vivr_model, $page_element)
    {
        $value = isset($_REQUEST['value']) ? trim($_REQUEST['value']) : '';
        $redirect_page_id = $element_name = isset($_REQUEST['redirect_page_id']) ? trim($_REQUEST['redirect_page_id']) : '';
        $is_inserted = true;

        $page_element->value = $value;
        $element_id = $vivr_model->setVivrPageElement($page_element);
        if ($element_id) {
            if (!$vivr_model->setVivrRedirectPage($element_id, $redirect_page_id)) {
                $is_inserted = false;
            }
        }
        $api_keys = isset($_REQUEST['api_key']) ? trim($_REQUEST['api_key']) : '';
        $api_comparison = isset($_REQUEST['api_comparison']) ? trim($_REQUEST['api_comparison']) : '';
        $api_calculation = isset($_REQUEST['api_calculation']) ? trim($_REQUEST['api_calculation']) : '';
        $api_keys = explode(";",$api_keys);
        $api_comparisons = explode(";",$api_comparison);
        $api_calculations = explode(";",$api_calculation);

        foreach ($api_keys as $api_key) {
            if($api_key != ""){
                if(!$vivr_model->setVivrApiKey($element_id, $api_key)){
                    $is_inserted = false;
                }
            }
        }
        foreach ($api_comparisons as $comparison) {
            if($comparison != ""){
                if(!$vivr_model->setVivrApiComparison($element_id, $comparison)){
                    $is_inserted = false;
                }
            }
        }
        foreach ($api_calculations as $calculation) {
            if($calculation != ""){
                if(!$vivr_model->setVivrApiCalculation($element_id, $calculation)){
                    $is_inserted = false;
                }
            }
        }
        return $is_inserted;
    }

    private function addParagraphElement($vivr_model, $page_element)
    {
        $page_element->value = null;
        $api_keys = isset($_REQUEST['api_key']) ? trim($_REQUEST['api_key']) : '';
        $api_comparison = isset($_REQUEST['api_comparison']) ? trim($_REQUEST['api_comparison']) : '';
        $api_calculation = isset($_REQUEST['api_calculation']) ? trim($_REQUEST['api_calculation']) : '';

        $element_id = $vivr_model->setVivrPageElement($page_element);

        if(!$element_id){
            return false;
        }

        $api_keys = explode(";",$api_keys);
        $api_comparisons = explode(";",$api_comparison);
        $api_calculations = explode(";",$api_calculation);

        $is_inserted = true;

        foreach ($api_keys as $api_key) {
            if($api_key != ""){
                if(!$vivr_model->setVivrApiKey($element_id, $api_key)){
                    $is_inserted = false;
                }
            }
        }

        foreach ($api_comparisons as $comparison) {
            if($comparison != ""){
                $calculation = str_replace("'", "\'", $calculation);
                if(!$vivr_model->setVivrApiComparison($element_id, $comparison)){
                    $is_inserted = false;
                }
            }
        }

        foreach ($api_calculations as $calculation) {
            if($calculation != ""){
                if(!$vivr_model->setVivrApiCalculation($element_id, $calculation)){
                    $is_inserted = false;
                }
            }
        }
        return $is_inserted;
    }

    private function addHyperlinkElement($vivr_model, $page_element)
    {
        $web_bn = isset($_REQUEST['web_bn']) ? trim($_REQUEST['web_bn']) : '';
        $web_en = isset($_REQUEST['web_en']) ? trim($_REQUEST['web_en']) : '';

        $web_link = new stdClass();
        $web_link->web_en = $web_en;
        $web_link->web_bn = $web_bn;

        $page_element->value = json_encode($web_link);
        $element_id = $vivr_model->setVivrPageElement($page_element);
        if ($element_id) {
            return true;
        }

        return false;
    }

    private function addStaticTableElement($vivr_model, $page_element)
    {
        $table_heading_bn = isset($_REQUEST['table_heading_bn']) ? trim($_REQUEST['table_heading_bn']) : '';
        $table_heading_en = isset($_REQUEST['table_heading_en']) ? trim($_REQUEST['table_heading_en']) : '';

        $table_row_bn = isset($_REQUEST['table_row_bn']) ? $_REQUEST['table_row_bn'] : '';
        $table_row_en = isset($_REQUEST['table_row_en']) ? $_REQUEST['table_row_en'] : '';

        $table_value = new stdClass();

        $table_heading = new stdClass();
        $table_heading->heading_bn = explode(";", $table_heading_bn);
        $table_heading->heading_en = explode(";", $table_heading_en);

        $table_row_data_bn = array();
        $table_row_data_en = array();
        $count = 0;
        while ($count < $page_element->rows) {
            $row_data_bn = new stdClass();
            $row_data_bn->rowspan = null;
            $row_data_bn->colspan = null;
            $row_data_bn->data = explode(";", $table_row_bn[$count]);
            $table_row_data_bn [] = $row_data_bn;

            $row_data_en = new stdClass();
            $row_data_en->rowspan = null;
            $row_data_en->colspan = null;
            $row_data_en->data = explode(";", $table_row_en[$count]);
            $table_row_data_en [] = $row_data_en;

            $count++;
        }

        $table_value->static = new stdClass();
        $table_value->static->table_heading = $table_heading;
        $table_value->static->table_row_data_bn = $table_row_data_bn;
        $table_value->static->table_row_data_en = $table_row_data_en;

        $page_element->value = str_replace("'","\'",json_encode($table_value, JSON_UNESCAPED_UNICODE));

        $element_id = $vivr_model->setVivrPageElement($page_element);
        if ($element_id) {
            return true;
        }
        return false;
    }

    private function addDynamicTableElement($vivr_model, $page_element)
    {
        $table_heading_bn = isset($_REQUEST['table_heading_bn']) ? trim($_REQUEST['table_heading_bn']) : '';
        $table_heading_en = isset($_REQUEST['table_heading_en']) ? trim($_REQUEST['table_heading_en']) : '';

        $table_key_id = isset($_REQUEST['table_key_id']) ? trim($_REQUEST['table_key_id']) : '';
        $table_key_comparison = isset($_REQUEST['table_key_comparison']) ? trim($_REQUEST['table_key_comparison']) : '';
        $table_key_calculation = isset($_REQUEST['table_key_calculation']) ? trim($_REQUEST['table_key_calculation']) : '';

        $heading_en = explode(";", $table_heading_en);
        $heading_bn = explode(";", $table_heading_bn);
        $key_list = explode(";", $table_key_id);
        $key_comparison_list = explode(";", $table_key_comparison);
        $key_calculation_list = explode(";", $table_key_calculation);

        $table_data = new stdClass();
        $table_data->key = array();

        $count = 0;
        while ($count < $page_element->columns) {
            $data = new stdClass();
            $data->key_id = $key_list[$count];
            $data->heading_en = $heading_en[$count];
            $data->heading_bn = $heading_bn[$count];
            $data->replace = null;
            $data->replace_default = null;
            $data->masking = null;

            $table_data->key[] = $data;
            $count++;
        }
        $page_element->value = json_encode($table_data, JSON_UNESCAPED_UNICODE);

        $element_id = $vivr_model->setVivrPageElement($page_element);
        if(!$element_id){
            return false;
        }

        $is_inserted = true;
        foreach ($key_comparison_list as $comparison) {
            if($comparison != ""){
                if(!$vivr_model->setVivrApiComparison($element_id, $comparison)){
                    $is_inserted = false;
                }
            }
        }

        foreach ($key_calculation_list as $calculation) {
            if($calculation != ""){
                $calculation = str_replace("'", "\'", $calculation);
                if(!$vivr_model->setVivrApiCalculation($element_id, $calculation)){
                    $is_inserted = false;
                }
            }
        }

        return $is_inserted;

    }

    function actionDeleteElement()
    {
        include("model/MVivr.php");
        $vivr_model = new MVivr();
        $element_id = isset($_REQUEST['element_id']) ? trim($_REQUEST['element_id']) : '';

        $result = false;
        if ($vivr_model->deleteElement($element_id)) {
            $vivr_model->deleteElementNavigation($element_id);
            $vivr_model->deleteElementApiKey($element_id);
            $vivr_model->deleteElementApiComparison($element_id);
            $vivr_model->deleteElementApiCalculation($element_id);
            $result = true;
        }

        $response = new stdClass();
        $response->success = false;
        if ($result) {
            $response->success = true;
        }
        echo json_encode($response);
        exit;
    }

    function actionEditElement()
    {
        include("model/MVivr.php");
        $vivr_model = new MVivr();
        $element_id = isset($_REQUEST['element_id']) ? trim($_REQUEST['element_id']) : '';

        if ($this->getRequest()->isPost()) {
            $result = $this->editNodeElement($this->getRequest());

            if ($result) {
                $data['success'] = 100;
            } else {
                $data['success'] = 0;
            }
        }

        $element_data = $vivr_model->getVivrElementData($element_id);
        $page_id = $element_data->page_id;
        $navigation = $vivr_model->getElementNavigation($element_id);
        $keys = $vivr_model->getElementApiKey($element_id);
        $comparisons = $vivr_model->getElementApiComparison($element_id);
        $calculations = $vivr_model->getElementApiCalculation($element_id);
        $child_pages = $vivr_model->getChildNodesFromParentID($page_id);
        $element_data->value = str_replace("'", "\'", $element_data->value);
        $table_type = null;
        $table_data = new stdClass();

        $api_keys= "";
        $api_comparisons = "";
        $api_calculations = "";
        foreach ($keys as $api_key) {
            $api_keys .= $api_key->response_key .";";
        }
        foreach ($comparisons as $comparison) {
            $api_comparisons .= $comparison->compare .";";
        }
        foreach ($calculations as $calculation) {
            $api_calculations .= $calculation->calculation .";";
        }

        $api_keys = rtrim($api_keys, ";");
        $api_comparisons = rtrim($api_comparisons, ";");
        $api_calculations = rtrim($api_calculations, ";");
        $api_calculations = str_replace("'", "\'", $api_calculations);
        if ($element_data->type == "table") {
            $element_data->value = str_replace("\'", "'", $element_data->value);
            $table_data_type = array_keys(json_decode($element_data->value, true));
            if ($table_data_type[0] == 'static') {
                $table_type = 'static';
                $table_value = json_decode($element_data->value);
                $table_data->heading_bn = implode(";", $table_value->static->table_heading->heading_bn);
                $table_data->heading_en  = implode(";", $table_value->static->table_heading->heading_en);
                $table_rows_en = array();
                $table_rows_bn = array();
                foreach ($table_value->static->table_row_data_bn as $table_data_bn){
                    $table_rows_bn [] = implode(";", $table_data_bn->data);
                }
                foreach ($table_value->static->table_row_data_en as $table_data_en){
                    $table_rows_en [] = implode(";", $table_data_en->data);
                }
                $table_data->rows_en = $table_rows_en;
                $table_data->rows_bn = $table_rows_bn;

            } else {
                $table_type = "dynamic";
                $table_value = json_decode($element_data->value);
                $table_heading_bn = "";
                $table_heading_en = "";
                $table_keys = "";
                foreach ($table_value->key as $t_value){
                    $table_heading_bn .= $t_value->heading_bn .";" ;
                    $table_heading_en .= $t_value->heading_en .";" ;
                    $table_keys .= $t_value->key_id .";" ;

                }
                $table_data->heading_bn = rtrim($table_heading_bn, ";");
                $table_data->heading_en = rtrim($table_heading_en, ";");
                $table_data->keys = rtrim($table_keys, ";");
            }
        } else if ($element_data->type == "a") {
            $value = json_decode($element_data->value);
        }

        $data['element_types'] = array(
            "button" => "Button",
            "paragraph" => "Paragraph",
            "a" => "HyperLink",
            "table" => "Table",
            "input" => "Input"
        );
        $element_data->value = str_replace("'", "\'", $element_data->value);
        $data['element_data'] = $element_data;
        $data['child_pages'] = $child_pages;
        $data['navigation'] = $navigation;
        $data['api_keys'] = $api_keys;
        $data['comparisons'] = $api_comparisons;
        $data['calculations'] = $api_calculations;
        $data['table_type'] = $table_type;
        $data['table_data'] = $table_data;
        $data['page_id'] = $page_id;

        $data['request'] = $this->getRequest();
        $data['pageTitle'] = 'Edit Page Element';
        $this->getTemplate()->display_popup('vivr_edit_element', $data);
    }

    function editNodeElement($request){

        $vivr_model = new MVivr();

        $page_id = isset($_REQUEST['page_id']) ? trim($_REQUEST['page_id']) : '';
        $element_id = isset($_REQUEST['element_id']) ? trim($_REQUEST['element_id']) : '';
        $element_order = isset($_REQUEST['element_order']) ? trim($_REQUEST['element_order']) : '';
        $display_name_en = isset($_REQUEST['display_name_en']) ? trim($_REQUEST['display_name_en']) : '';
        $display_name_bn = isset($_REQUEST['display_name_bn']) ? trim($_REQUEST['display_name_bn']) : '';
        $element_type = isset($_REQUEST['element_type']) ? trim($_REQUEST['element_type']) : '';
        $background_color = isset($_REQUEST['background_color']) ? trim($_REQUEST['background_color']) : '';
        $text_color = isset($_REQUEST['text_color']) ? trim($_REQUEST['text_color']) : '';
        $element_name = isset($_REQUEST['element_name']) ? trim($_REQUEST['element_name']) : '';
        $number_of_rows = isset($_REQUEST['number_of_rows']) ? trim($_REQUEST['number_of_rows']) : '';
        $number_of_columns = isset($_REQUEST['number_of_columns']) ? trim($_REQUEST['number_of_columns']) : '';
        $is_visible = isset($_REQUEST['is_visible']) ? trim($_REQUEST['is_visible']) : '';
        $api = isset($_REQUEST['api']) ? trim($_REQUEST['api']) : '';
        $table_type = isset($_REQUEST['table_type']) ? trim($_REQUEST['table_type']) : '';

        $page_element = new stdClass();
        $page_element->page_id = $page_id;
        $page_element->element_id = $element_id;
        $page_element->type = $element_type;
        $page_element->display_name_bn = $display_name_bn;
        $page_element->display_name_en = addslashes($display_name_en);
        $page_element->background_color = $background_color;
        $page_element->text_color = $text_color;
        $page_element->name = $element_name;
        $page_element->element_order = $element_order;
        $page_element->rows = $number_of_rows;
        $page_element->columns = $number_of_columns;
        $page_element->is_visible = $is_visible;
        $page_element->api = $api;


        if ($element_type == "button") {
            if ($this->editButtonElement($vivr_model, $page_element)) {
                return true;
            }
        } else if ($element_type == "a") {
            if ($this->editHyperlinkElement($vivr_model, $page_element)) {
                return true;
            }
        } else if ($element_type == "paragraph") {
            if ($this->editParagraphElement($vivr_model, $page_element)) {
                return true;
            }
        } else if ($element_type == "table") {
            if ($table_type == "static") {
                if ($this->editStaticTableElement($vivr_model, $page_element)) {
                    return true;
                }
            } elseif ($table_type == "dynamic") {
                if ($this->editDynamicTableElement($vivr_model, $page_element)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function editButtonElement($vivr_model, $page_element)
    {
        $value = isset($_REQUEST['value']) ? trim($_REQUEST['value']) : '';
        $redirect_page_id = $element_name = isset($_REQUEST['redirect_page_id']) ? trim($_REQUEST['redirect_page_id']) : '';

        $page_element->value = $value;

        $is_updated = false;
        if ($vivr_model->updateVivrPageElement($page_element)) {
            $is_updated = true;
        }
        if ($vivr_model->updateVivrRedirectPage($page_element->element_id, $redirect_page_id)) {
            $is_updated = true;
        }
        $api_keys = isset($_REQUEST['api_key']) ? trim($_REQUEST['api_key']) : '';
        $api_comparison = isset($_REQUEST['api_comparison']) ? trim($_REQUEST['api_comparison']) : '';
        $api_calculation = isset($_REQUEST['api_calculation']) ? trim($_REQUEST['api_calculation']) : '';

        $api_keys = explode(";",$api_keys);
        $api_comparisons = explode(";",$api_comparison);
        $api_calculations = explode(";",$api_calculation);
        $element_id = $page_element->element_id;
        $is_updated = true;
        $vivr_model->deleteElementApiKey($element_id);
        foreach ($api_keys as $api_key) {
            if ($api_key != "") {
                if (!$vivr_model->setVivrApiKey($element_id, $api_key)) {
                    $is_updated = false;
                }
            }
        }
        $vivr_model->deleteElementApiComparison($element_id);
        foreach ($api_comparisons as $comparison) {
            if ($comparison != "") {
                if (!$vivr_model->setVivrApiComparison($element_id, $comparison)) {
                    $is_updated = false;
                }
            }
        }
        $vivr_model->deleteElementApiCalculation($element_id);
        foreach ($api_calculations as $calculation) {
            if ($calculation != "") {
                if (!$vivr_model->setVivrApiCalculation($element_id, $calculation)) {
                    $is_updated = false;
                }
            }
        }
        return $is_updated;
    }

    private function editHyperlinkElement($vivr_model, $page_element)
    {
        $web_bn = isset($_REQUEST['web_bn']) ? trim($_REQUEST['web_bn']) : '';
        $web_en = isset($_REQUEST['web_en']) ? trim($_REQUEST['web_en']) : '';

        $web_link = new stdClass();
        $web_link->web_en = $web_en;
        $web_link->web_bn = $web_bn;
        $page_element->value = json_encode($web_link);

        if ($vivr_model->updateVivrPageElement($page_element)) {
            return true;
        }
        return false;
    }

    private function editParagraphElement($vivr_model, $page_element)
    {
        $page_element->value = null;
        $api_keys = isset($_REQUEST['api_key']) ? trim($_REQUEST['api_key']) : '';
        $api_comparison = isset($_REQUEST['api_comparison']) ? trim($_REQUEST['api_comparison']) : '';
        $api_calculation = isset($_REQUEST['api_calculation']) ? trim($_REQUEST['api_calculation']) : '';

        $vivr_model->updateVivrPageElement($page_element);

        $api_keys = explode(";",$api_keys);
        $api_comparisons = explode(";",$api_comparison);
        $api_calculations = explode(";",$api_calculation);
        $element_id = $page_element->element_id;

        $is_inserted = true;
        $vivr_model->deleteElementApiKey($element_id);
        foreach ($api_keys as $api_key) {
            if ($api_key != "") {
                if (!$vivr_model->setVivrApiKey($element_id, $api_key)) {
                    $is_inserted = false;
                }
            }
        }
        $vivr_model->deleteElementApiComparison($element_id);
        foreach ($api_comparisons as $comparison) {
            if ($comparison != "") {
                if (!$vivr_model->setVivrApiComparison($element_id, $comparison)) {
                    $is_inserted = false;
                }
            }
        }
        $vivr_model->deleteElementApiCalculation($element_id);
        foreach ($api_calculations as $calculation) {
            if ($calculation != "") {
                $calculation = str_replace("'", "\'", $calculation);
                if (!$vivr_model->setVivrApiCalculation($element_id, $calculation)) {
                    $is_inserted = false;
                }
            }
        }

        return $is_inserted;
    }

    private function editStaticTableElement($vivr_model, $page_element)
    {
        $table_heading_bn = isset($_REQUEST['table_heading_bn']) ? trim($_REQUEST['table_heading_bn']) : '';
        $table_heading_en = isset($_REQUEST['table_heading_en']) ? trim($_REQUEST['table_heading_en']) : '';

        $table_row_bn = isset($_REQUEST['table_row_bn']) ? $_REQUEST['table_row_bn'] : '';
        $table_row_en = isset($_REQUEST['table_row_en']) ? $_REQUEST['table_row_en'] : '';

        $table_value = new stdClass();

        $table_heading = new stdClass();
        $table_heading->heading_bn = explode(";", $table_heading_bn);
        $table_heading->heading_en = explode(";", $table_heading_en);

        $table_row_data_bn = array();
        $table_row_data_en = array();
        $count = 0;

        while ($count < $page_element->rows) {
            $row_data_bn = new stdClass();
            $row_data_bn->rowspan = null;
            $row_data_bn->colspan = null;
            $row_data_bn->data = explode(";", $table_row_bn[$count]);
            $table_row_data_bn [] = $row_data_bn;

            $row_data_en = new stdClass();
            $row_data_en->rowspan = null;
            $row_data_en->colspan = null;
            $row_data_en->data = explode(";", $table_row_en[$count]);
            $table_row_data_en [] = $row_data_en;

            $count++;
        }
        $table_value->static = new stdClass();
        $table_value->static->table_heading = $table_heading;
        $table_value->static->table_row_data_bn = $table_row_data_bn;
        $table_value->static->table_row_data_en = $table_row_data_en;
        $page_element->value = json_encode($table_value, JSON_UNESCAPED_UNICODE);
        $page_element->value = $page_element->value = str_replace("'", "\'", $page_element->value);

        if ($vivr_model->editVivrPageElement($page_element)) {
            return true;
        }

        return false;
    }

    private function editDynamicTableElement($vivr_model, $page_element)
    {
        $table_heading_bn = isset($_REQUEST['table_heading_bn']) ? trim($_REQUEST['table_heading_bn']) : '';
        $table_heading_en = isset($_REQUEST['table_heading_en']) ? trim($_REQUEST['table_heading_en']) : '';

        $table_key_id = isset($_REQUEST['table_key_id']) ? trim($_REQUEST['table_key_id']) : '';
        $table_key_comparison = isset($_REQUEST['table_key_comparison']) ? trim($_REQUEST['table_key_comparison']) : '';
        $table_key_calculation = isset($_REQUEST['table_key_calculation']) ? trim($_REQUEST['table_key_calculation']) : '';

        $heading_en = explode(";", $table_heading_en);
        $heading_bn = explode(";", $table_heading_bn);
        $key_list = explode(";", $table_key_id);
        $api_comparisons = explode(";", $table_key_comparison);
        $api_calculations = explode(";", $table_key_calculation);

        $table_data = new stdClass();
        $table_data->key = array();

        $count = 0;
        while ($count < $page_element->columns) {
            $data = new stdClass();
            $data->key_id = $key_list[$count];
            $data->heading_en = $heading_en[$count];
            $data->heading_bn = $heading_bn[$count];
            $data->replace = null;
            $data->replace_default = null;
            $data->masking = null;

            $table_data->key[] = $data;
            $count++;
        }
        $page_element->value = json_encode($table_data, JSON_UNESCAPED_UNICODE);
        $is_updated = false;

        if($vivr_model->updateVivrPageElement($page_element)){
            $is_updated = true;
        }
        $element_id = $page_element->element_id;

        $vivr_model->deleteElementApiKey($element_id);
        foreach ($key_list as $api_key) {
            if ($api_key != "") {
                if ($vivr_model->setVivrApiKey($element_id, $api_key)) {
                    $is_updated = true;
                }
            }
        }
        $vivr_model->deleteElementApiComparison($element_id);
        foreach ($api_comparisons as $comparison) {
            if ($comparison != "") {
                if ($vivr_model->setVivrApiComparison($element_id, $comparison)) {
                    $is_updated = true;
                }
            }
        }
        $vivr_model->deleteElementApiCalculation($element_id);
        foreach ($api_calculations as $calculation) {
            if ($calculation != "") {
                $calculation = str_replace("'", "\'", $calculation);
                if ($vivr_model->setVivrApiCalculation($element_id, $calculation)) {
                    $is_updated = true;
                }
            }
        }
        return $is_updated;
    }

    function actionUploadPageMediaFile()
    {
        include("model/MVivr.php");
        $vivr_model = new MVivr();
        $vivr_id = $this->request->getRequest('vivrid');
        $page_id = $this->request->getRequest('page_id');
        $has_error = null;
        $error_msg = null;
        $file_path = "content/vivr_audio/";
        $is_uploaded = false;

        if ($this->getRequest()->isPost()) {
            $audio_data = new stdClass();
            $audio_data->page_id = $page_id;

            if (($_FILES['audio_file_en']['name'] != null && $_FILES['audio_file_en']['type'] == "audio/wav") || ($_FILES['audio_file_bn']['name'] != null && $_FILES['audio_file_bn']['type'] == "audio/wav")) {
                $audio_data->audio_file_bn = basename($_FILES['audio_file_bn']['name']);
                $audio_data->audio_file_en = basename($_FILES['audio_file_en']['name']);
                if ($audio_data->audio_file_bn != null) {
                    if (move_uploaded_file($_FILES["audio_file_bn"]["tmp_name"], $file_path . $audio_data->audio_file_bn))
                        $is_uploaded = true;
                }
                if ($audio_data->audio_file_en != null) {
                    if (move_uploaded_file($_FILES["audio_file_en"]["tmp_name"], $file_path . $audio_data->audio_file_en))
                        $is_uploaded = true;
                }
            } else {
                $has_error = true;
                $error_msg = "Please Upload Files With Audio Format.";
            }

            if (!$has_error) {
                if($is_uploaded){
                    if ($vivr_model->updateVivrPageAudio($audio_data)) {
                        $has_error = false;
                        $error_msg = "Audio Files Uploaded Successfully.";
                    }
                }else{
                    $has_error = true;
                    $error_msg = "Audio Files Uploaded Failed";
                }
            }
        }
        $page_data = $vivr_model->getVivrPageFromID($page_id);
//        dd($page_data);

        $data['pageTitle'] = "VIVR Page Audio File Upload";
        $data['audio_file_bn'] = $page_data->audio_file_ban;
        $data['audio_file_en'] = $page_data->audio_file_en;
        $data['file_path'] = $file_path;
        $data['vivr_id'] = $vivr_id;
        $data['page_id'] = $page_id;
        $data['has_error'] = $has_error;
        $data['error_msg'] = $error_msg;

        $this->getTemplate()->display_popup('vivr_upload_page_media', $data, true);
    }


    function actionDeleteVivrFile()
    {
        $page_id = $this->getRequest()->getRequest('page_id');
        $language = $this->getRequest()->getRequest('language');

        if ($page_id != null) {
            include("model/MVivr.php");
            $vivr_model = new MVivr();
            $vivr_page = $vivr_model->getVivrPageFromID($page_id);
            $file_path = "content/vivr_audio/";
            $isDeleted = false;
            $audio_file_bn = $vivr_page->audio_file_ban;
            $audio_file_en = $vivr_page->audio_file_en;
            if ($language == "BN" && $audio_file_bn != null) {
                if (file_exists($file_path . $audio_file_bn) && unlink($file_path . $audio_file_bn) && $vivr_model->deletePageAudio($page_id, $language)) {
                    $isDeleted = true;
                }
            } elseif ($language == "EN" && $audio_file_en != null) {
                if (file_exists($file_path . $audio_file_en) && unlink($file_path . $audio_file_en) && $vivr_model->deletePageAudio($page_id, $language)) {
                    $isDeleted = true;
                }
            }

            if ($isDeleted) {
                $this->SetMessage('File deleted successfully', true);
            } else {
                $this->SetMessage('Failed to Delete file', false);
            }
        }
        $this->ReturnResponse();
    }

    function SetMessage($msg, $isSuccess = false)
    {
        $this->_cresponse->status = $isSuccess;
        $this->_cresponse->msg = $msg;
    }

    function ReturnResponse()
    {
        die(json_encode($this->_cresponse));
    }

    function actionUploadElementMediaFile()
    {
        include("model/MVivr.php");
        $vivr_model = new MVivr();
        $element_id = $this->request->getRequest('element_id');
        $vivr_id = $this->request->getRequest('vivrid');
        $page_id = $this->request->getRequest('page_id');
        $has_error = false;
        $error_msg = null;
        $file_path = "content/vivr_audio/";
        $is_uploaded = false;

        if ($this->getRequest()->isPost()) {
            $element_data = $vivr_model->getVivrElementData($element_id);
            $element_audio_data = json_decode($element_data->custom2);
            $audio_data = new stdClass();
            if (($_FILES['audio_file_en']['name'] != null && $_FILES['audio_file_en']['type'] == "audio/wav") || ($_FILES['audio_file_bn']['name'] != null && $_FILES['audio_file_bn']['type'] == "audio/wav")) {
                $audio_data->audio_en = basename($_FILES['audio_file_en']['name']);
                $audio_data->audio_bn = basename($_FILES['audio_file_bn']['name']);
                if ($audio_data->audio_bn != null) {
                    if (move_uploaded_file($_FILES["audio_file_bn"]["tmp_name"], $file_path . $audio_data->audio_bn))
                        $is_uploaded = true;
                } else {
                    $audio_data->audio_bn = $element_audio_data->audio_bn;
                }
                if ($audio_data->audio_en != null) {
                    if (move_uploaded_file($_FILES["audio_file_en"]["tmp_name"], $file_path . $audio_data->audio_en))
                        $is_uploaded = true;
                } else {
                    $audio_data->audio_en = $element_audio_data->audio_en;
                }
            } else {
                $has_error = true;
                $error_msg = "Please Upload Files With Audio Format.";
            }
            if (!$has_error) {
                if($is_uploaded){

                    if ($vivr_model->updateVivrElementAudio($element_id, json_encode($audio_data))) {
                        $has_error = false;
                        $error_msg = "Audio Files Uploaded Successfully.";
                    }
                }else{
                    $has_error = true;
                    $error_msg = "Audio Files Uploaded Failed";
                }
            }
        }
        $element_data = $vivr_model->getVivrElementData($element_id);
        $element_audio_data = json_decode($element_data->custom2);

        $data['pageTitle'] = "VIVR Element Audio File Upload";
        $data['audio_file_bn'] = $element_audio_data->audio_bn;
        $data['audio_file_en'] = $element_audio_data->audio_en;
        $data['file_path'] = $file_path;
        $data['vivr_id'] = $vivr_id;
        $data['page_id'] = $page_id;
        $data['element_id'] = $element_id;
        $data['has_error'] = $has_error;
        $data['error_msg'] = $error_msg;

        $this->getTemplate()->display_popup('vivr_upload_element_media', $data, true);
    }

    function actionDeleteVivrElementFile()
    {
        $page_id = $this->getRequest()->getRequest('page_id');
        $language = $this->getRequest()->getRequest('language');
        $element_id = $this->getRequest()->getRequest('element_id');

        if ($element_id != null) {
            include("model/MVivr.php");
            $vivr_model = new MVivr();
//            $vivr_page = $vivr_model->getVivrPageFromID($page_id);
            $element_data = $vivr_model->getVivrElementData($element_id);
            $element_audio_data = json_decode($element_data->custom2);
            $file_path = "content/vivr_audio/";
            $isDeleted = false;
            $audio_file_bn = $element_audio_data->audio_bn;
            $audio_file_en = $element_audio_data->audio_en;
            if ($language == "BN" && $audio_file_bn != null) {
                $element_audio_data->audio_bn = '';
                if (file_exists($file_path . $audio_file_bn) && unlink($file_path . $audio_file_bn) && $vivr_model->deleteElementAudio($element_id, json_encode($element_audio_data))) {
                    $isDeleted = true;
                }
            } elseif ($language == "EN" && $audio_file_en != null) {
                $element_audio_data->audio_en = '';
                if (file_exists($file_path . $audio_file_en) && unlink($file_path . $audio_file_en) && $vivr_model->deleteElementAudio($element_id, json_encode($element_audio_data))) {
                    $isDeleted = true;
                }
            }

            if ($isDeleted) {
                $this->SetMessage('File deleted successfully', true);
            } else {
                $this->SetMessage('Failed to Delete file', false);
            }
        }
        $this->ReturnResponse();
    }

    function actionAddRootPage()
    {
        include("model/MVivr.php");
        $vivr_model = new MVivr();

        $error_data = null;
        if($this->getRequest()->isPost()){
            $error_data = $this->addRootNode();
        }

        $vivr_id = isset($_REQUEST['vivrid']) ? trim($_REQUEST['vivrid']) : '';
        $vivr_page_titles = $vivr_model->getVivrDispositionTitles();
        $empty_title = new stdClass();
        $empty_title->disposition_code = '';
        $empty_title->service_title = 'None';
        $vivr_page_titles = array($empty_title) + $vivr_page_titles;

        $data['pageTitle'] = 'VIVR Page Add';
        $data['tasks'] = array(
            "nav" => "Navigation",
            "lang" => "Language Change",
            "call" => "Call Agent"
        );
        $data['vivr_page_titles'] = $vivr_page_titles;
        $data['request'] = $this->getRequest();
        $data['vivr_id'] = $vivr_id;
        $data['error_data'] = $error_data;

        $this->getTemplate()->display_popup('vivr_add_root_page', $data);
    }

    private function addRootNode()
    {
        $vivr_model = new MVivr();

        $vivr_id = isset($_REQUEST['vivr_id']) ? trim($_REQUEST['vivr_id']) : '';
        $page_heading_en = isset($_REQUEST['heading_en']) ? trim($_REQUEST['heading_en']) : '';
        $page_heading_bn = isset($_REQUEST['heading_bn']) ? trim($_REQUEST['heading_bn']) : '';
        $vivr_title_id = isset($_REQUEST['vivr_title']) ? trim($_REQUEST['vivr_title']) : '';
        $task = isset($_REQUEST['task']) ? trim($_REQUEST['task']) : '';
        $hase_main_page = isset($_REQUEST['hase_main_page']) ? trim($_REQUEST['hase_main_page']) : '';
        $has_previous_page = isset($_REQUEST['has_previous_page']) ? trim($_REQUEST['has_previous_page']) : '';
        $audio_file_en =  '';
        $audio_file_bn =  '';

        $vivr_data = new stdClass();
        $vivr_data->parent_page_id = null;
        $vivr_data->vivr_id = $vivr_id;
        $vivr_data->page_heading_en = $page_heading_en;
        $vivr_data->page_heading_bn = $page_heading_bn;
        $vivr_data->task = $task;
        $vivr_data->has_main_page = $hase_main_page;
        $vivr_data->has_previous_page = $has_previous_page;
        $vivr_data->audio_file_en = $audio_file_en;
        $vivr_data->audio_file_bn = $audio_file_bn;

        $page_id = $vivr_model->addNewVivrPage($vivr_data);

        $data = new stdClass();
        if ($page_id) {
            if ($vivr_model->setVivrPageTitle($page_id, $vivr_title_id) && $vivr_model->setVivrDefaultPage($vivr_id, $page_id)) {
                $data->error_code = 0;
                $data->error_msg = "VIVR Node Added Successfully";
                return $data;
            }
        }
        $data->error_code = 101;
        $data->error_msg = "Failed to add new node!";
        return $data;
    }
}