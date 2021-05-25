<?php

class MVivr extends Model
{
	function __construct() {
		parent::__construct();
	}

    function getVivrPages($ivr_id = '')
    {
        $this->getDB()->setCharset("utf8");
//        $sql = "SELECT * FROM vivr_pages p
//                LEFT JOIN vivr_page_title pt
//                ON p.page_id = pt.page_id
//                LEFT JOIN vivr_titles t
//                ON pt.title_id = t.title_id";

        $sql = "SELECT * FROM vivr_pages ";
        if (!empty($ivr_id)) {
            $sql .= " WHERE ivr_id='$ivr_id' ";
        }
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result;
        }
        return array();
    }

    function getVivrPageFromID($page_id)
    {
        $this->getDB()->setCharset("utf8");
        $sql = "SELECT * FROM vivr_pages p
                LEFT JOIN vivr_page_title pt 
                ON p.page_id = pt.page_id ";

        $sql .= " WHERE p.page_id = '$page_id' ";

        $result = $this->getDB()->query($sql);

        if (is_array($result)) {
            return $result[0];
        }
        return array();
    }

    function getVivrDefaultPage($ivr_id)
    {
        $sql = "SELECT * FROM vivr_default_page ";
        $sql .= " WHERE ivr_id = '$ivr_id'";
        $sql .= " LIMIT 1";

        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result;
        }
        return array();
    }

    function getVivrTitles()
    {
        $sql = "SELECT * FROM vivr_titles ";

        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result;
        }
        return array();
    }

    function addNewVivrPage($vivr_data)
    {
        $this->getDB()->setCharset("utf8");
        $page_id = $this->getLastPageID() + 1;
        $current_time = date("Y-m-d");

        $query = "INSERT INTO vivr_pages SET page_id = '$page_id', 
                  parent_page_id = '$vivr_data->parent_page_id',
                  ivr_id = '$vivr_data->vivr_id', 
                  page_heading_ban = '$vivr_data->page_heading_bn', 
                  page_heading_en = '$vivr_data->page_heading_en', 
                  task = '$vivr_data->task',                   
                  has_previous_menu = '$vivr_data->has_previous_page', 
                  has_main_menu = '$vivr_data->has_main_page', 
                  audio_file_ban = '$vivr_data->audio_file_bn', 
                  audio_file_en = '$vivr_data->audio_file_en', 
                  created_at = '$current_time' ";
        if ($this->getDB()->query($query)) {
            return $page_id;
        }

        return false;
    }

    private function getLastPageID()
    {
        $sql = "SELECT MAX(page_id) as page_id FROM vivr_pages";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result[0]->page_id;
        }
        return 0;
    }

    function setVivrPageTitle($page_id, $vivr_title_id)
    {
        $query = "INSERT INTO vivr_page_title (page_id, title_id) VALUES ";
        $query .= "('$page_id', '$vivr_title_id')";

        if ($this->getDB()->query($query)) {
            return true;
        }
        return false;
    }

    function editVivrPage($vivr_data)
    {
        $this->getDB()->setCharset("utf8");

        $query = "UPDATE vivr_pages  set  
                  page_heading_ban = '$vivr_data->page_heading_bn',
                  page_heading_en = '$vivr_data->page_heading_en',
                  task = '$vivr_data->task', 
                  has_previous_menu = '$vivr_data->has_previous_page',
                  has_main_menu = '$vivr_data->has_main_page' ";
        $query .= " WHERE page_id = '$vivr_data->page_id'";

        if ($this->getDB()->query($query)) {
            return true;
        }
        return false;
    }

    function updateVivrPageTitle($page_id, $vivr_title_id)
    {
        $query = "UPDATE vivr_page_title SET title_id = '$vivr_title_id' ";
        $query .= " WHERE page_id = '$page_id' LIMIT 1";

        if ($this->getDB()->query($query)) {
            return true;
        }
        return false;
    }

    function deleteTree($page_id, $vivr_id)
    {
        $query = "DELETE FROM vivr_pages ";
        $query .= " WHERE page_id = '$page_id' AND ivr_id = '$vivr_id'";
        $query .= "  LIMIT 1 ";

        if ($this->getDB()->query($query)) {
            $this->deleteNodeTitle($page_id);
            return true;
        }
        return false;
    }

    function deleteChildNodes($parent_page_id, $vivr_id)
    {
        $query = "SELECT *  FROM vivr_pages ";
        $query .= " WHERE parent_page_id = '$parent_page_id' AND ivr_id = '$vivr_id'";

        $child_nodes = $this->getDB()->query($query);

        foreach ($child_nodes as $node) {
            $this->deleteNodeTitle($node->page_id);
        }

        $query = "DELETE FROM vivr_pages ";
        $query .= " WHERE parent_page_id = '$parent_page_id' AND ivr_id = '$vivr_id'";

        if ($this->getDB()->query($query)) {
            return true;
        }
        return false;
    }

    private function deleteNodeTitle($page_id)
    {
        $query = "DELETE FROM vivr_page_title ";
        $query .= " WHERE page_id = '$page_id' ";
        $query .= " LIMIT 1 ";

        if ($this->getDB()->query($query)) {
            return true;
        }
        return false;
    }

    function getVivrPageElements($page_id)
    {
        $this->getDB()->setCharset("utf8");

        $sql = "SELECT * FROM vivr_page_elements ";
        $sql .= " WHERE page_id = '$page_id'";
        $sql .= " ORDER BY element_order";

        $result = $this->getDB()->query($sql);

        if (is_array($result)) {
            return $result;
        }
        return array();
    }

    function deleteElement($element_id)
    {
        $query = "DELETE FROM vivr_page_elements ";
        $query .= " WHERE element_id = '$element_id' ";
        $query .= " LIMIT 1 ";

        if ($this->getDB()->query($query)) {
            return true;
        }
        return false;
    }

    function deleteElementNavigation($element_id)
    {
        $query = "DELETE FROM vivr_pages_of_buttons ";
        $query .= " WHERE button_id = $element_id ";
        $query .= " LIMIT 1 ";

        if ($this->getDB()->query($query)) {
            return true;
        }
        return false;
    }

    function deleteElementApiKey($element_id)
    {
        $query = "DELETE FROM vivr_elements_api_keys ";
        $query .= " WHERE element_id = $element_id ";
        $query .= " LIMIT 1";

        if ($this->getDB()->query($query)) {
            return true;
        }
        return false;
    }

    function deleteElementApiComparison($element_id)
    {
        $query = "DELETE FROM vivr_elements_api_comparison ";
        $query .= " WHERE element_id = $element_id ";
        $query .= " LIMIT 1";

        if ($this->getDB()->query($query)) {
            return true;
        }
        return false;
    }

    function deleteElementApiCalculation($element_id)
    {
        $query = "DELETE FROM vivr_elements_api_calculation ";
        $query .= " WHERE element_id = $element_id ";
        $query .= " LIMIT 1";

        if ($this->getDB()->query($query)) {
            return true;
        }
        return false;
    }

    function getChildNodesFromParentID($page_id)
    {
        $sql = "SELECT page_id, page_heading_en as title FROM vivr_pages ";
        $sql .= " WHERE parent_page_id = '$page_id'";

        $result = $this->getDB()->query($sql);

        if (is_array($result)) {
            return $result;
        }
        return array();
    }

    function getVivrServices($ivr_id = "*")
    {
        $sql = "SELECT page_id,parent_page_id,ivr_id, page_heading_en FROM vivr_pages";
        if (!empty($ivr_id) && $ivr_id != '*') {
            $sql .= " WHERE ivr_id = '$ivr_id'";
        }

        $result = $this->getDB()->query($sql);

        if (is_array($result)) {
            return $result;
        }
        return array();
    }

    function setVivrPageElement($page_element)
    {
        $this->getDB()->setCharset("utf8");
        $element_id = $this->getLastID("element_id","vivr_page_elements") + 1;

        $sql = "INSERT INTO vivr_page_elements SET 
                element_id = '$element_id', 
                page_id = '$page_element->page_id', 
                type = '$page_element->type', 
                display_name_bn = '$page_element->display_name_bn', 
                display_name_en = '$page_element->display_name_en', 
                background_color = '$page_element->background_color', 
                text_color = '$page_element->text_color', 
                `name` = '$page_element->name',
                `value` = '$page_element->value', 
                element_order = $page_element->element_order, 
                rows = $page_element->rows, 
                columns = $page_element->columns,
                is_visible = '$page_element->is_visible',
                data_provider_function = '$page_element->api',
                custom2 = '' ";

        if ($this->getDB()->query($sql)) {
            return $element_id;
        }
        return false;
    }

    private function getLastID($column_name, $table_name, $condition = "")
    {
        $sql = "SELECT MAX($column_name) AS id FROM $table_name ";
        if ($condition != "") {
            $sql .= " WHERE $condition";
        }
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result[0]->id;
        }
        return 0;
    }

    function setVivrRedirectPage($element_id, $redirect_page_id)
    {
        $id = $this->getLastID("id", "vivr_pages_of_buttons") + 1;
        $current_date = date("Y-m-d");

        $sql = "INSERT INTO vivr_pages_of_buttons SET id = '$id', button_id = $element_id,
                page_id = $redirect_page_id, created_at = '$current_date'";

        if ($this->getDB()->query($sql)) {
            return true;
        }
        return false;
    }

    function setVivrApiKey($element_id, $api_key)
    {
        $id = $this->getLastID("id", "vivr_elements_api_keys") + 1;
        $key_order = $this->getLastID("key_order", "vivr_elements_api_keys", "element_id = '$element_id'") + 1;

        $current_date = date("Y-m-d");
        $sql = "INSERT INTO vivr_elements_api_keys SET id = '$id', element_id = $element_id, response_key = '$api_key',
                key_order = '$key_order', created_at = '$current_date' ";

        if ($this->getDB()->query($sql)) {
            return true;
        }
        return false;
    }

    function setVivrApiComparison($element_id, $api_comparison)
    {
        $id = $this->getLastID("id", "vivr_elements_api_comparison") + 1;
        $compare_order = $this->getLastID("comparing_order", "vivr_elements_api_comparison", "element_id = '$element_id'") + 1;

        $current_date = date("Y-m-d");
        $sql = "INSERT INTO vivr_elements_api_comparison SET id = '$id', element_id = $element_id, compare = '$api_comparison',
                comparing_order = '$compare_order', created_at = '$current_date' ";

        if ($this->getDB()->query($sql)) {
            return true;
        }
        return false;
    }

    function setVivrApiCalculation($element_id, $api_calculation)
    {
        $id = $this->getLastID("id", "vivr_elements_api_calculation") + 1;
        $calculation_order = $this->getLastID("calculation_order", "vivr_elements_api_calculation", "element_id = '$element_id'") + 1;

        $current_date = date("Y-m-d");
        $sql = "INSERT INTO vivr_elements_api_calculation SET id = '$id', element_id = $element_id, calculation = '$api_calculation',
                calculation_order = '$calculation_order', created_at = '$current_date' ";

        if ($this->getDB()->query($sql)) {
            return true;
        }
        return false;
    }

    function getVivrMenuPage($ivr_id)
    {
        $sql = "SELECT page_id FROM vivr_default_page";
        $sql .= " WHERE ivr_id = '$ivr_id' LIMIT 1";

        $result = $this->getDB()->query($sql);

        if (is_array($result)) {
            return $result[0]->page_id;
        }
        return null;
    }
		
    function getVivrMainMenu($ivr_id)
    {
        $sql = "SELECT page_id,ivr_id FROM vivr_default_page";
        if (!empty($ivr_id) && $ivr_id != '*')
            $sql .= " WHERE ivr_id = '$ivr_id' ";

        $result = $this->getDB()->query($sql);

        if (is_array($result)) {
            return $result;
        }
        return null;
    }

    function getVivrMenuServices($page_id)
    {
        $sql = "SELECT page_id, page_heading_en FROM vivr_pages";
        if (!empty($page_id) && $page_id != '*') {
            $sql .= " WHERE parent_page_id = '$page_id'";
        }

        $result = $this->getDB()->query($sql);

        if (is_array($result)) {
            return $result;
        }
        return array();
    }

    function getVivrElementData($element_id)
    {
//        $this->getDB()->setCharset("utf8");

        $sql = "SELECT * FROM vivr_page_elements ";
        $sql .= " WHERE element_id = '$element_id'";

        $result = $this->getDB()->query($sql);

        if (is_array($result)) {
            return $result[0];
        }
        return array();
    }

    function getElementNavigation($element_id)
    {
        $query = "SELECT * FROM vivr_pages_of_buttons ";
        $query .= " WHERE button_id = $element_id ";

        $result = $this->getDB()->query($query);

        if (is_array($result)) {
            return $result[0];
        }
        return array();
    }

    function getElementApiKey($element_id, $key_order = null)
    {
        $query = "SELECT * FROM vivr_elements_api_keys ";
        $query .= " WHERE element_id = $element_id ";
        if ($key_order != null)
            $query .= " AND key_order ='$key_order";

        $result = $this->getDB()->query($query);

        if (is_array($result)) {
            return $result;
        }
        return array();
    }

    function getElementApiComparison($element_id)
    {
        $query = "SELECT * FROM vivr_elements_api_comparison ";
        $query .= " WHERE element_id = $element_id ";

        $result = $this->getDB()->query($query);

        if (is_array($result)) {
            return $result;
        }
        return array();
    }

    function getElementApiCalculation($element_id)
    {
        $query = "SELECT * FROM vivr_elements_api_calculation ";
        $query .= " WHERE element_id = $element_id ";

        $result = $this->getDB()->query($query);

        if (is_array($result)) {
            return $result;
        }
        return array();
    }

    function updateVivrPageElement($page_element)
    {
//        $this->getDB()->setCharset("utf8");

        $sql = "UPDATE vivr_page_elements SET 
                page_id = '$page_element->page_id', 
                type = '$page_element->type', 
                display_name_bn = '$page_element->display_name_bn', 
                display_name_en = '$page_element->display_name_en', 
                background_color = '$page_element->background_color', 
                text_color = '$page_element->text_color', 
                `name` = '$page_element->name',
                `value` = '$page_element->value', 
                element_order = $page_element->element_order, 
                rows = $page_element->rows, 
                columns = $page_element->columns,
                is_visible = '$page_element->is_visible',
                data_provider_function = '$page_element->api',
                custom2 = ''
                WHERE element_id = '$page_element->element_id'";

        if ($this->getDB()->query($sql)) {
            return true;
        }
        return false;
    }

    function updateVivrRedirectPage($element_id, $redirect_page_id)
    {
        $current_date = date("Y-m-d");

        $sql = "UPDATE vivr_pages_of_buttons SET page_id = $redirect_page_id, created_at = '$current_date' 
                WHERE button_id = $element_id LIMIT 1";

        if ($this->getDB()->query($sql)) {
            return true;
        }
        return false;
    }

    function updateVivrApiKey($element_id, $api_key)
    {
        $id = $this->getLastID("id", "vivr_elements_api_keys") + 1;
        $key_order = $this->getLastID("key_order", "vivr_elements_api_keys", "element_id = '$element_id'") + 1;

        $current_date = date("Y-m-d");
        $sql = "INSERT INTO vivr_elements_api_keys SET id = '$id', element_id = $element_id, response_key = '$api_key',
                key_order = '$key_order', created_at = '$current_date' ";

        if ($this->getDB()->query($sql)) {
            return true;
        }
        return false;
    }

    function editVivrPageElement($page_element)
    {
//        $this->getDB()->setCharset("utf8");
        $element_id = $page_element->element_id;

        $sql = "UPDATE vivr_page_elements SET 
                page_id = '$page_element->page_id', 
                type = '$page_element->type', 
                display_name_bn = '$page_element->display_name_bn', 
                display_name_en = '$page_element->display_name_en', 
                background_color = '$page_element->background_color', 
                text_color = '$page_element->text_color', 
                `name` = '$page_element->name',
                `value` = '$page_element->value', 
                element_order = $page_element->element_order, 
                rows = $page_element->rows, 
                `columns` = $page_element->columns,
                is_visible = '$page_element->is_visible',
                data_provider_function = '$page_element->api'
                WHERE element_id = '$element_id'";

        if ($this->getDB()->query($sql)) {
            return true;
        }
        return false;
    }

    function updateVivrPageAudio($vivr_data)
    {
//        $this->getDB()->setCharset("utf8");

        $query = "UPDATE vivr_pages SET";
        if ($vivr_data->audio_file_bn != '')
            $query .= " audio_file_ban = '$vivr_data->audio_file_bn'";
        if ($vivr_data->audio_file_bn != '' && $vivr_data->audio_file_en != '')
            $query .= ",";
        if ($vivr_data->audio_file_en != '')
            $query .= " audio_file_en = '$vivr_data->audio_file_en' ";
        $query .= " WHERE page_id = '$vivr_data->page_id'";
//dd($query);
        if ($this->getDB()->query($query)) {
            return true;
        }
        return false;
    }

    function deletePageAudio($page_id, $language)
    {
        $sql = "UPDATE vivr_pages SET ";
        if ($language == "BN") {
            $sql .= " audio_file_ban = '' ";
        } elseif ($language == "EN") {
            $sql .= " audio_file_en = '' ";
        }
        $sql .= " WHERE page_id = '$page_id' ";
        $sql .= " LIMIT 1 ";

        if ($this->getDB()->query($sql)) {
            return true;
        }
        return false;
    }

    function updateVivrElementAudio($element_id, $audio_data)
    {
        $query = 'UPDATE vivr_page_elements SET';
        $query .= ' custom2 = \'' . $audio_data . '\'';
        $query .= ' WHERE element_id = \'' . $element_id . '\'';

        if ($this->getDB()->query($query)) {
            return true;
        }
        return false;
    }

    function deleteElementAudio($element_id, $audio_data)
    {
        $query = 'UPDATE vivr_page_elements SET';
        $query .= ' custom2 = \'' . $audio_data . '\'';
        $query .= ' WHERE element_id = \'' . $element_id . '\'';

        if ($this->getDB()->query($query)) {
            return true;
        }
        return false;
    }

    function getVivrDispositionTitles()
    {
        $sql = "SELECT disposition_code, service_title FROM ivr_service_code ";

        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result;
        }
        return array();
    }

    function setVivrDefaultPage($ivr_id, $page_id)
    {
        $id = $this->getLastID("id", "vivr_default_page") + 1;
        $current_time = date("Y-m-d H:i:s");
        $sql = "INSERT INTO vivr_default_page ";
        $sql .= " SET id = $id,";
        $sql .= " ivr_id = '$ivr_id',";
        $sql .= " page_id = $page_id,";
        $sql .= " created_at = '$current_time'";

        if ($this->getDB()->query($sql)) {
            return true;
        }
        return false;
    }
}

?>