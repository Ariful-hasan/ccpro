<?php

class MSkillCrmTemplate extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getDispositionPathArray($child)
    {
        $path = array();
        if (empty($child)) {
            return $path;
        }

        $result_d = $this->getDB()->query("SELECT lft, rgt, title FROM skill_crm_disposition_code WHERE disposition_id='$child'");
        if (is_array($result_d)) {
            $left = $result_d[0]->lft;
            $rgt = $result_d[0]->rgt;
            $sql = "SELECT disposition_id, title FROM skill_crm_disposition_code WHERE lft < $left AND rgt > $rgt ORDER BY lft ASC";
            $result = $this->getDB()->query($sql);
            if (is_array($result)) {
                //$path = '';
                foreach ($result as $row) {
                    //if (!empty($path)) $path .= ' -> ';
                    //$path .= $row->title;
                    //array_unshift($path, array($row->disposition_id, $row->title));
                    $path[] = array($row->disposition_id, $row->title);
                }
                //return $path;
            }
            $path[] = array($child, $result_d[0]->title);
        }

        return $path;
    }

    public function getDispositionPath($child)
    {
        if (empty($child)) {
            return 'Not Selected';
        }

        $result = $this->getDB()->query("SELECT lft, rgt FROM skill_crm_disposition_code WHERE disposition_id='$child'");
        if (is_array($result)) {
            $left = $result[0]->lft;
            $rgt = $result[0]->rgt;
            $sql = "SELECT title FROM skill_crm_disposition_code WHERE lft < $left AND rgt > $rgt ORDER BY lft ASC";
            $result = $this->getDB()->query($sql);
            $path = '';
            if (is_array($result)) {
                foreach ($result as $row) {
                    if (!empty($path)) {
                        $path .= ' -> ';
                    }
                    $path .= $row->title;
                }
                return $path;
            }
            return $path;
        }

        return 'Invalid Parent';
    }

    public function getDispositionChildrenOptions($template_id='', $root='')
    {
        $partition = UserAuth::getPartition();
        $partition_id = !empty($partition['partition_id']) ? $partition['partition_id'] : '';

        $options = array();
        $sql = "SELECT disposition_id, title FROM skill_crm_disposition_code WHERE parent_id='$root' ";
        if (!empty($template_id)) {
            $sql .= "AND template_id='$template_id' ";
        }
        $sql .= !empty($partition_id) ? "AND partition_id='{$partition_id}' " : "";
        $sql .= "ORDER BY title ASC";
        $result = $this->getDB()->query($sql);
        //echo $sql;

        if (is_array($result)) {
            foreach ($result as $row) {
                $options[$row->disposition_id] = $row->title;
            }
        }

        return $options;
    }

    public function getCallSkill($callid)
    {
        $tstamp = substr($callid, 0, 10);
        $y = date("y", $tstamp);
        $table = $y == date("y") ? '' : '_' . $y;

        $sql = "SELECT popup_url, popup_type, skill_name FROM skill AS s LEFT JOIN skill_log$table AS l ON s.skill_id=l.skill_id WHERE l.callid='$callid'";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result[0];
        }
        return null;
    }

    public function getTemplateSelectOptions($star_represent_blank=false)
    {
        if ($star_represent_blank) {
            $ret = array('*'=>'Select');
        } else {
            $ret = array(''=>'Select');
        }
        $sql = "SELECT template_id, title FROM skill_crm_template";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            foreach ($result as $res) {
                $ret[$res->template_id] = $res->title;
            }
        }
        return $ret;
    }

    public function getDispositionSelectOptions($star_represent_blank=false)
    {
        if ($star_represent_blank) {
            $ret = array('*'=>'Select');
        } else {
            $ret = array(''=>'Select');
        }
        $sql = "SELECT disposition_id, title FROM skill_crm_disposition_code";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            foreach ($result as $res) {
                $ret[$res->disposition_id] = $res->title;
            }
        }
        return $ret;
    }

    public function getDisposChatSelectOptions($star_represent_blank=false)
    {
        if ($star_represent_blank) {
            $ret = array('*'=>'Select');
        } else {
            $ret = array(''=>'Select');
        }
        $sql = "SELECT disposition_id, title FROM skill_disposition_code";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            foreach ($result as $res) {
                $ret[$res->disposition_id] = $res->title;
            }
        }
        return $ret;
    }

    public function numTemplates()
    {
        $sql = "SELECT COUNT(template_id) AS numrows FROM skill_crm_template ";
        $result = $this->getDB()->query($sql);

        if ($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }

        return 0;
    }

    public function getTemplates($offset=0, $limit=0)
    {
        $sql = "SELECT * FROM skill_crm_template ORDER BY template_id ";
        if ($limit > 0) {
            $sql .= "LIMIT $offset, $limit";
        }

        return $this->getDB()->query($sql);
    }

    public function getTemplateById($tid)
    {
        $sql = "SELECT * FROM skill_crm_template WHERE template_id='$tid'";
        $result = $this->getDB()->query($sql);
        return is_array($result) ? $result[0] : null;
    }

    public function getMaxTemplateID()
    {
        $sql = "SELECT MAX(template_id) AS numrows FROM skill_crm_template";

        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return empty($result[0]->numrows) ? '' : $result[0]->numrows;
        }
        return '';
    }

    public function getSections($tid, $tabid='')
    {
        $sql = "SELECT * FROM skill_crm_template_section WHERE template_id='$tid' ";
        if (!empty($tabid)) {
            $sql .= "AND tab_id='$tabid' ";
        } else {
            $sql .= "AND tab_id='' ";
        }
        $sql .= "ORDER BY sl";
        return $this->getDB()->query($sql);
    }

    public function getSectionById($tid, $sid, $tabid='')
    {
        $sql = "SELECT * FROM skill_crm_template_section WHERE template_id='$tid' AND section_id='$sid' ";
        if (!empty($tabid)) {
            $sql .= "AND tab_id='$tabid' ";
        }
        $sql .= "LIMIT 1";
        $return = $this->getDB()->query($sql);
        if (is_array($return)) {
            return $return[0];
        }
        return null;
    }

    public function getFieldsBySection($tid, $sid)
    {
        $sql = "SELECT * FROM skill_crm_template_fields WHERE template_id='$tid' AND section_id='$sid' ORDER BY sl";
        return $this->getDB()->query($sql);
    }

    public function getFiltersBySection($tid, $sid)
    {
        $sql = "SELECT * FROM skill_crm_template_filters WHERE template_id='$tid' AND section_id='$sid' ORDER BY sl";
        return $this->getDB()->query($sql);
    }

    public function getValuesBySection($tid, $sid)
    {
        $sql = "SELECT * FROM skill_crm_template_values WHERE template_id='$tid' AND section_id='$sid'";
        return $this->getDB()->query($sql);
    }

    public function getFields($tid)
    {
        $sql = "SELECT * FROM skill_crm_template_fields WHERE template_id='$tid' ORDER BY section_id, sl";
        return $this->getDB()->query($sql);
    }

    public function getNewScetionID($tid)
    {
        if (empty($tid)) {
            return '';
        }
        for ($i='A'; $i<='Z'; $i++) {
            $sql = "SELECT section_id FROM skill_crm_template_section WHERE template_id='$tid' AND section_id='$i'";
            $result = $this->getDB()->query($sql);
            if (empty($result)) {
                return $i;
            }
        }
        return '';
    }

    public function getNewSectionSL($tid)
    {
        $sl = 11;
        $numsec = 0;
        $sql = "SELECT COUNT(section_id) AS numsec FROM skill_crm_template_section WHERE template_id='$tid' LIMIT 1";
        $result = $this->getDB()->query($sql);

        if (is_array($result)) {
            $numsec = $result[0]->numsec;
            if (empty($numsec)) {
                $numsec = 0;
            }
        }

        return $sl+$numsec;
    }

    public function getExistingSection($tid, $type)
    {
        $sql = "SELECT * FROM skill_crm_template_section WHERE template_id='$tid' AND section_type='$type' LIMIT 1";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return $result[0];
        }
        return null;
    }

    public function saveDefinedSection($tid, $data)
    {
        if (empty($tid)) {
            return false;
        }
        if (empty($data)) {
            return false;
        }
        $id = $data['id'];
        if (empty($id)) {
            $new_id = $this->getNewScetionID($tid);
            $new_sl = $this->getNewSectionSL($tid);
            $sql = "INSERT INTO skill_crm_template_section SET template_id='$tid', sl='$new_sl', section_id='$new_id', ".
                "section_title='$data[title]', section_type='$data[stype]', api='$data[api]', debug_mode='$data[dmode]', is_editable='N', active='Y'";
            if ($this->getDB()->query($sql)) {
                return true;
            }
        } else {
            $sql = "UPDATE skill_crm_template_section SET section_title='$data[title]', section_type='$data[stype]', api='$data[api]', debug_mode='$data[dmode]' WHERE template_id='$tid' AND section_id='$id'";
            if ($this->getDB()->query($sql)) {
                return true;
            }
        }

        return false;
    }

    public function deleteSection($tid, $sid)
    {
        if (empty($tid)) {
            return false;
        }
        if (empty($sid)) {
            return false;
        }

        $sql = "DELETE FROM skill_crm_template_section WHERE template_id='$tid' AND section_id='$sid'";
        $this->getDB()->query($sql);

        $sql = "DELETE FROM skill_crm_template_fields WHERE template_id='$tid' AND section_id='$sid'";
        $this->getDB()->query($sql);

        return true;
    }

    public function saveSection($tid, $tabid='', $data)
    {
        if (empty($tid)) {
            return false;
        }
        if (empty($data)) {
            return false;
        }
        $id = $data['id'];
        if (empty($id)) {
            $new_id = $this->getNewScetionID($tid);
            $new_sl = $this->getNewSectionSL($tid);
            $apiDataTxt = !empty($data['api']) ? trim($data['api']) : "";
            $sectionNode = !empty($data['snode']) ? trim($data['snode']) : "";
            $sql = "INSERT INTO skill_crm_template_section SET template_id='$tid', sl='$new_sl', section_id='$new_id', section_title='$data[title]', ".
                "section_type='$data[stype]', api='$apiDataTxt', debug_mode='$data[dmode]', is_editable='N', active='Y', tab_id='$tabid', section_node='$sectionNode'";
            if ($this->getDB()->query($sql)) {
                $fields = isset($data['fields']) ? $data['fields'] : null;
                if (is_array($fields)) {
                    $f_sl = 10;
                    foreach ($fields  as $field) {
                        if (is_array($field)) {
                            foreach ($field as $fieldKey=>$fieldVal) {
                                $field[$fieldKey] = !empty($fieldVal) ? trim($fieldVal) : "";
                            }
                        }

                        $f_sl++;
                        $field_tab_id = "";
                        if (isset($field['ftab']) && $field['ftab'] == 'TAB') {
                            $field_tab_id = $field['key']."_".$tid.$id;
                        }
                        $sql = "INSERT INTO skill_crm_template_fields SET template_id='$tid', section_id='$new_id', sl='$f_sl', field_label='$field[label]', ".
                            "field_key='$field[key]', field_mask='$field[mask]', save_in_session='$field[csession]', api='$field[api]', field_tab_id='$field_tab_id'";
                        $this->getDB()->query($sql);
                    }
                }
                return true;
            }
        } else {
            $apiDataTxt = !empty($data['api']) ? trim($data['api']) : "";
            $sectionNode = !empty($data['snode']) ? trim($data['snode']) : "";
            $apiDataTxt = $this->getDB()->escapeString($apiDataTxt);
            $sql = "UPDATE skill_crm_template_section SET section_title='{$data[title]}', section_type='{$data[stype]}', api='{$apiDataTxt}', debug_mode='{$data[dmode]}', section_node='{$sectionNode}' WHERE template_id='{$tid}' AND section_id='{$id}'";
            $this->getDB()->query($sql);

            $sql = "DELETE FROM skill_crm_template_fields WHERE template_id='$tid' AND section_id='$id'";
            $this->getDB()->query($sql);

            $fields = isset($data['fields']) ? $data['fields'] : null;
            if (is_array($fields)) {
                $f_sl = 10;
                foreach ($fields  as $field) {
                    if (is_array($field)) {
                        foreach ($field as $fieldKey=>$fieldVal) {
                            $field[$fieldKey] = !empty($fieldVal) ? trim($fieldVal) : "";
                        }
                    }

                    $f_sl++;
                    $field_tab_id = "";
                    if (isset($field['ftab']) && $field['ftab'] == 'TAB') {
                        $field_tab_id = $field['key']."_".$tid.$id;
                    }
                    $sql = "INSERT INTO skill_crm_template_fields SET template_id='$tid', section_id='$id', sl='$f_sl', field_label='$field[label]', ".
                        "field_key='$field[key]', field_mask='$field[mask]', save_in_session='$field[csession]', api='$field[api]', field_tab_id='$field_tab_id'";
                    $this->getDB()->query($sql);
                }
            }
            return true;
        }

        return false;
    }

    public function deleteFilter($tid, $data)
    {
        if (empty($tid)) {
            return false;
        }
        if (empty($data)) {
            return false;
        }
        $id = $data['id'];
        if (!empty($id)) {
            $sql = "UPDATE skill_crm_template_section SET search_submit_label='', is_searchable='N' WHERE template_id='$tid' AND section_id='$id'";
            $this->getDB()->query($sql);

            $sql = "DELETE FROM skill_crm_template_filters WHERE template_id='$tid' AND section_id='$id'";
            $this->getDB()->query($sql);

            return true;
        }

        return false;
    }

    public function saveSetValues($tid, $data)
    {
        if (empty($tid)) {
            return false;
        }
        if (empty($data)) {
            return false;
        }
        $id = $data['id'];

        $sql = "DELETE FROM skill_crm_template_values WHERE template_id='$tid' AND section_id='$id'";
        $this->getDB()->query($sql);

        $fields = isset($data['values']) ? $data['values'] : null;
        if (is_array($fields)) {
            foreach ($fields  as $field) {
                $apiDataTxt = !empty($field['api']) ? $field['api'] : "";
                $sql = "INSERT INTO skill_crm_template_values SET template_id='$tid', section_id='$id', ".
                    "name='$field[name]', api='$apiDataTxt'";
                $this->getDB()->query($sql);
            }
        }
        return true;
    }

    public function saveFilter($tid, $data)
    {
        if (empty($tid)) {
            return false;
        }
        if (empty($data)) {
            return false;
        }
        $id = $data['id'];

        if (!empty($id)) {
            $sql = "UPDATE skill_crm_template_section SET search_submit_label='$data[slabel]', is_searchable='Y' WHERE template_id='$tid' AND section_id='$id'";
            $this->getDB()->query($sql);

            $sql = "DELETE FROM skill_crm_template_filters WHERE template_id='$tid' AND section_id='$id'";
            $this->getDB()->query($sql);

            $fields = isset($data['fields']) ? $data['fields'] : null;
            if (is_array($fields)) {
                $f_sl = 0;
                foreach ($fields  as $field) {
                    $f_sl++;
                    $apiDataTxt = !empty($field['api']) ? $field['api'] : "";
                    $sql = "INSERT INTO skill_crm_template_filters SET template_id='$tid', section_id='$id', sl='$f_sl', ".
                        "field_label='$field[label]', field_key='$field[key]', field_type='$field[type]', api='$apiDataTxt'";
                    $this->getDB()->query($sql);
                }
            }
            return true;
        } else {
            $apiDataTxt = !empty($data['api']) ? $data['api'] : "";
            $sql = "UPDATE skill_crm_template SET api='$apiDataTxt' WHERE template_id='$tid'";
            $this->getDB()->query($sql);

            $sql = "DELETE FROM skill_crm_template_filters WHERE template_id='$tid' AND section_id='$id'";
            $this->getDB()->query($sql);

            $sql = "UPDATE skill_crm_template_section SET search_submit_label='$data[slabel]', is_searchable='Y' WHERE template_id='$tid' AND section_id='$id'";
            $this->getDB()->query($sql);

            $fields = isset($data['fields']) ? $data['fields'] : null;
            if (is_array($fields)) {
                $f_sl = 0;
                foreach ($fields  as $field) {
                    $f_sl++;
                    $apiDataTxt = !empty($field['api']) ? $field['api'] : "";
                    $sql = "INSERT INTO skill_crm_template_filters SET template_id='$tid', section_id='$id', sl='$f_sl', ".
                        "field_label='$field[label]', field_key='$field[key]', field_type='$field[type]', api='$apiDataTxt'";
                    $this->getDB()->query($sql);
                }
            }
            return true;
        }

        return false;
    }

    public function saveTemplate($tid, $data)
    {
        if (is_array($data)) {
            $sl = 10;
            foreach ($data as $section) {
                $sl++;
                $sql = "UPDATE skill_crm_template_section SET sl='$sl' WHERE template_id='$tid' AND section_id='$section[id]'";
                $this->getDB()->query($sql);
            }
        }
        /*
        $sql = "DELETE FROM skill_crm_template_section WHERE template_id='$tid'";
        $this->getDB()->query($sql);

        $sql = "DELETE FROM skill_crm_template_fields WHERE template_id='$tid'";
        $this->getDB()->query($sql);

        if (is_array($data)) {
            $sl = 10;
            foreach ($data as $section) {
                $sl++;
                $sql = "INSERT INTO skill_crm_template_section SET template_id='$tid', sl='$sl', section_id='$section[id]', ".
                    "section_title='$section[title]', section_type='$section[stype]', api='$section[api]', is_editable='N', active='Y'";
                $this->getDB()->query($sql);

                $fields = isset($section['fields']) ? $section['fields'] : null;
                if (is_array($fields )) {
//				var_dump($fields);
                    foreach ($fields  as $field) {
                        $sql = "INSERT INTO skill_crm_template_fields SET template_id='$tid', section_id='$section[id]', ".
                            "field_label='$field[label]', field_key='$field[key]'";
//echo $sql;
                        $this->getDB()->query($sql);
                    }
                }

            }
        }
        */
        return true;
    }

    public function addTemplate($template)
    {
        if (empty($template->title)) {
            return false;
        }

        $id = $this->getMaxTemplateID();

        if (empty($id)) {
            $id ='AAAA';
        } elseif ($id == 'ZZZZ') {
            return false;
        } else {
            $id++;
        }

        $sql = "INSERT INTO skill_crm_template SET template_id='$id', ".
            "title='$template->title'";

        if ($this->getDB()->query($sql)) {
            $this->addToAuditLog('Skill Template', 'A', "Title=".$template->title, '');
            return true;
        }

        return false;
    }
    /*
    function addTemplate($template)
    {
        if (empty($template->title)) return false;

        $id = $this->getMaxTemplateID();

        if (empty($id)) { $id ='AAAA'; }
        else if ( $id == 'ZZZZ') { return false; }
        else { $id++; }

        $sql = "INSERT INTO skill_crm_template SET template_id='$id', title='$template->title'";


        if ($this->getDB()->query($sql)) {
            $new_sl = $this->getNewSectionSL($id);
            $sql = "INSERT INTO skill_crm_template_section SET template_id='$id', sl='$new_sl', ";
            $sql .= "section_id='A', section_title='Customer Information', section_type='F', api='MSDB', ";
            $sql .= "debug_mode='N', is_editable='N', active='Y'";
            $this->getDB()->query($sql);

            $sql = "INSERT INTO skill_crm_template_fields SET template_id='$id', section_id='A', sl='11', ";
            $sql .= "field_label='First Name', field_key='first_name', field_mask='', api=''";
            $this->getDB()->query($sql);

            $sql = "INSERT INTO skill_crm_template_fields SET template_id='$id', section_id='A', sl='12', ";
            $sql .= "field_label='Last Name', field_key='last_name', field_mask='', api=''";
            $this->getDB()->query($sql);

            $this->addToAuditLog('Skill Template', 'A', "Title=".$template->title, '');
            return true;
        }

        return false;
    } */

    public function updateTemplate($oldtemplate, $template)
    {
        if (empty($oldtemplate->template_id)) {
            return false;
        }
        $is_update = false;
        $changed_fields = '';
        $ltext = '';

        if ($template->title != $oldtemplate->title) {
            $changed_fields .= "title='$template->title'";
            $ltext = $this->addAuditText($ltext, "Title=$oldtemplate->title to $template->title");
        }
        /*
                if ($template->api != $oldtemplate->api) {
                    if (!empty($changed_fields)) $changed_fields .= ', ';
                    $changed_fields .= "api='$template->api'";
                    $ltext = $this->addAuditText($ltext, "API=$oldtemplate->api to $template->api");
                }
                */

        if (!empty($changed_fields)) {
            $sql = "UPDATE skill_crm_template SET $changed_fields WHERE template_id='$oldtemplate->template_id'";
            $is_update = $this->getDB()->query($sql);
        }


        if ($is_update) {
            $this->addToAuditLog('Skill Template', 'U', $ltext, '');
        }

        return $is_update;
    }

    public function deleteTemplate($tid)
    {
        /*
            Check skill for existing template
        */
        $template_info = $this->getTemplateById($tid);
        if (!empty($template_info)) {
            $sql = "DELETE FROM skill_crm_template WHERE template_id='$tid' LIMIT 1";
            if ($this->getDB()->query($sql)) {
                $sql = "DELETE FROM skill_crm_template_section WHERE template_id='$tid'";
                $this->getDB()->query($sql);
                $sql = "DELETE FROM skill_crm_template_fields WHERE template_id='$tid'";
                $this->getDB()->query($sql);

                $sql = "DELETE FROM skill_crm_template_values WHERE template_id='$tid'";
                $this->getDB()->query($sql);

                $sql = "DELETE FROM skill_crm_service_type WHERE template_id='$tid'";
                $this->getDB()->query($sql);

                $sql = "DELETE FROM skill_crm_template_filters WHERE template_id='$tid'";
                $this->getDB()->query($sql);

                $this->addToAuditLog('Skill Template', 'D', "Template=$template_info->title", '');
                return true;
            }
        }
        return false;
    }

    public function getMaxGroupID()
    {
        $sql = "SELECT MAX(service_type_id) AS numrows FROM skill_crm_service_type";

        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            return empty($result[0]->numrows) ? '' : $result[0]->numrows;
        }
        return '';
    }

    public function addDispositionGroup($tid, $title, $tstat='N', $efrom='', $eto='', $useEmail='N')
    {
        if (empty($title)) {
            return '';
        }

        $id = $this->getMaxGroupID();

        if (empty($id)) {
            $id ='AA';
        } elseif ($id == 'ZZ') {
            return '';
        } else {
            $id++;
        }
        //$title=addslashes($title);
        $title=$title;
        $sql = "INSERT INTO skill_crm_service_type SET ".
            "template_id='$tid', ".
            "service_type_id='$id', ".
            "title='$title',".
            "status_ticketing='$tstat',".
            "use_email_module='$useEmail',".
            "from_email_name='$efrom',".
            "to_email_name='$eto'";

        if ($this->getDB()->query($sql)) {
            $this->addToAuditLog('Skill CRM Service Type', 'A', "Title=$title", "");
            return $id;
        }

        return '';
    }

    public function getDispositionGroupOptions($tmp_id, $star_represent_blank=false)
    {
        if ($star_represent_blank) {
            $return = array('*'=>'Select');
        } else {
            $return = array(''=>'Select');
        }

        $sql = "SELECT service_type_id, title FROM skill_crm_service_type";
        if (!empty($tmp_id)) {
            $sql .= " WHERE template_id='$tmp_id'";
        }
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            foreach ($result as $row) {
                $return[$row->service_type_id] = $row->title;
            }
        }

        return $return;
    }

    public function getServiceTypeOptions($tmp_id)
    {
        $return = array();

        $sql = "SELECT * FROM skill_crm_service_type";
        if (!empty($tmp_id)) {
            $sql .= " WHERE template_id='$tmp_id'";
        }
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            foreach ($result as $row) {
                $return[$row->service_type_id] = $row;
            }
        }

        return $return;
    }

    public function getDispositionOptions($tmp_id)
    {
        $return = array(''=>'Select');

        /*
        $sql = "SELECT disposition_id, title FROM skill_crm_disposition_code WHERE template_id='0000' ORDER BY title";
        $result = $this->getDB()->query($sql);

        if (is_array($result)) {
            foreach ($result as $row) {
                $return[$row->disposition_id] = $row->title;
            }
        }


        $sql = "SELECT disposition_id, title FROM disposition WHERE campaign_id='1111' ORDER BY title";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            foreach ($result as $row) {
                $return[$row->disposition_id] = $row->title;
            }
        }
        */
        //if ($cmp_id != '1111' && $cmp_id != '0000') {
        $sql = "SELECT disposition_id, title FROM skill_crm_disposition_code WHERE template_id='$tmp_id'";
        $result = $this->getDB()->query($sql);
        if (is_array($result)) {
            foreach ($result as $row) {
                $return[$row->disposition_id] = $row->title;
            }
        }
        //}

        return $return;
    }

    public function numDispositions($tid)
    {
        $sql = "SELECT COUNT(disposition_id) AS numrows FROM skill_crm_disposition_code ";
        if (!empty($tid)) {
            $sql .= "WHERE template_id='$tid'";
        }
        $result = $this->getDB()->query($sql);

        if ($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }

        return 0;
    }

    public function numDispositionGroups($tid)
    {
        $sql = "SELECT COUNT(service_type_id) AS numrows FROM skill_crm_service_type ";
        if (!empty($tid)) {
            $sql .= "WHERE template_id='$tid'";
        }
        $result = $this->getDB()->query($sql);
        //echo $sql;
        if ($this->getDB()->getNumRows() == 1) {
            return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
        }

        return 0;
    }

    public function getDispositions($tid, $offset=0, $limit=0)
    {
        $sql = "SELECT * FROM skill_crm_disposition_code ";
        $sql .= !empty($tid)  ? "WHERE template_id='{$tid}' " :"";
        $sql .= "ORDER BY lft ASC ";
        if ($limit > 0) {
            $sql .= "LIMIT $offset, $limit";
        }

        return $this->getDB()->query($sql);
    }
    public function getDispositionsBySkillId($skill_id)
    {
        $sql  = "SELECT * FROM skill_crm_disposition_code dc ";
        $sql .= " INNER JOIN skill_disposition_template dt ";
        $sql .= " ON dc.template_id = dt.template_id WHERE dt.skill_id = '{$skill_id}' AND dc.status = 'Y' ";
        return $this->getDB()->query($sql);
    }

    public function getDispositionGroups($tid, $offset=0, $limit=0)
    {
        $sql = "SELECT * FROM skill_crm_service_type ";
        if (!empty($tid)) {
            $sql .= "WHERE template_id='$tid' ";
        }
        $sql .= "ORDER BY title ";
        if ($limit > 0) {
            $sql .= "LIMIT $offset, $limit";
        }
        //echo $sql;
        return $this->getDB()->query($sql);
    }

    public function getDispositionById($disid, $tid='')
    {
        $sql = "SELECT * FROM skill_crm_disposition_code WHERE disposition_id='$disid' ";
        if (!empty($tid)) {
            $sql .=  "AND template_id='$tid' ";
        }
        $sql .= "LIMIT 1";
        $return = $this->getDB()->query($sql);
        if (is_array($return)) {
            return $return[0];
        }
        return null;
    }

    public function getDispositionGroupById($gid, $tid='')
    {
        $sql = "SELECT * FROM skill_crm_service_type WHERE service_type_id='$gid' ";
        if (!empty($tid)) {
            $sql .=  "AND template_id='$tid' ";
        }
        $sql .= "LIMIT 1";
        $return = $this->getDB()->query($sql);
        if (is_array($return)) {
            return $return[0];
        }
        return null;
    }

    public function updateService($oldservice, $service)
    {
        if (empty($oldservice->disposition_id)) {
            return false;
        }
        $is_update = false;
        $changed_fields = '';
        $ltext = '';
        if ($service->disposition_id != $oldservice->disposition_id) {
            $changed_fields .= "disposition_id='$service->disposition_id'";
            $ltext = "Disposition Code=$oldservice->disposition_id to $service->disposition_id";
        }
        if ($service->title != $oldservice->title) {
            if (!empty($changed_fields)) {
                $changed_fields .= ', ';
            }
            $stitle = $service->title;
            $changed_fields .= "title='$stitle'";
            $ltext = $this->addAuditText($ltext, "Title=$oldservice->title to $service->title");
        }

        if ($service->parent_id != $oldservice->parent_id) {
            if (!empty($changed_fields)) {
                $changed_fields .= ', ';
            }
            $changed_fields .= "parent_id='$service->parent_id'";
            $ltext = $this->addAuditText($ltext, "Parent changed");
        }

        if ($service->altr_disposition_id != $oldservice->altr_disposition_id) {
            if (!empty($changed_fields)) {
                $changed_fields .= ', ';
            }
            $changed_fields .= "altr_disposition_id='$service->altr_disposition_id'";
            $ltext = $this->addAuditText($ltext, "Altr. Disposition changed");
        }

        if ($service->disposition_type != $oldservice->disposition_type) {
            if (!empty($changed_fields)) {
                $changed_fields .= ', ';
            }
            $changed_fields .= "disposition_type='$service->disposition_type'";
            $ltext = $this->addAuditText($ltext, "Altr. Disposition changed");
        }

        if (!empty($service->parent_id)) {
            $response = $this->getDispositionById($service->parent_id, $service->template_id);
            if (!empty($changed_fields)) {
                $changed_fields .= ', ';
            }
            $changed_fields .= "partition_id = '{$response->partition_id}'";
        }

        if (!empty($changed_fields)) {
            $sql = "UPDATE skill_crm_disposition_code SET $changed_fields WHERE disposition_id='$oldservice->disposition_id'";
            $is_update = $this->getDB()->query($sql);
        }


        if ($is_update) {
            $this->rebuildDispositionTree('', 0);
            $this->addToAuditLog('Skill CRM Disposition Code', 'U', "Disposition Code=".$oldservice->disposition_id, $ltext);
        }

        return $is_update;
    }

    public function rebuildDispositionTree($parent, $left)
    {
        $right = $left+1;
        $sql = "SELECT disposition_id FROM skill_crm_disposition_code WHERE parent_id='$parent'";
        $result = $this->getDB()->query($sql);

        if (is_array($result)) {
            foreach ($result as $row) {
                $right = $this->rebuildDispositionTree($row->disposition_id, $right);
            }
        }

        $sql = "UPDATE skill_crm_disposition_code SET lft='$left', rgt='$right' WHERE disposition_id='$parent'";
        $this->getDB()->query($sql);
        return $right+1;
    }

    public function updateDispositionGroup($oldservice, $service, $isStatUp=false)
    {
        if (empty($oldservice->service_type_id)) {
            return false;
        }
        $is_update = false;
        $changed_fields = '';
        $ltext = '';
        if ($isStatUp) {
            if ($service->status_ticketing != $oldservice->status_ticketing) {
                if (!empty($changed_fields)) {
                    $changed_fields .= ", ";
                }
                $changed_fields .= "status_ticketing='$service->status_ticketing'";
                $ltext = "Status=$oldservice->status_ticketing to $service->status_ticketing";
            }
        } else {
            if ($service->title != $oldservice->title) {
                $changed_fields .= "title='$service->title'";
                $ltext = "Title=$oldservice->title to $service->title";
            }

            if ($service->status_ticketing != $oldservice->status_ticketing) {
                if (!empty($changed_fields)) {
                    $changed_fields .= ", ";
                }
                $changed_fields .= "status_ticketing='$service->status_ticketing'";
                $ltext = "Status=$oldservice->status_ticketing to $service->status_ticketing";
            }

            if ($service->from_email_name != $oldservice->from_email_name) {
                if (!empty($changed_fields)) {
                    $changed_fields .= ", ";
                }
                $changed_fields .= "from_email_name='$service->from_email_name'";
                $ltext = "From Email=$oldservice->from_email_name to $service->from_email_name";
            }

            if ($service->to_email_name != $oldservice->to_email_name) {
                if (!empty($changed_fields)) {
                    $changed_fields .= ", ";
                }
                $changed_fields .= "to_email_name='$service->to_email_name'";
                $ltext = "To Email=$oldservice->to_email_name to $service->to_email_name";
            }

            if ($service->use_email_module != $oldservice->use_email_module) {
                if (!empty($changed_fields)) {
                    $changed_fields .= ", ";
                }
                $changed_fields .= "use_email_module='$service->use_email_module'";
                $ltext = "Use Email Module=$oldservice->use_email_module to $service->use_email_module";
            }
        }

        if (!empty($changed_fields)) {
            $sql = "UPDATE skill_crm_service_type SET $changed_fields WHERE service_type_id='$oldservice->service_type_id' LIMIT 1";
            $is_update = $this->getDB()->query($sql);
        }


        if ($is_update) {
            $this->addToAuditLog('Skill CRM Service Type', 'U', $ltext, "");
        }

        return $is_update;
    }

    public function deleteDispositionGroup($tid, $serviceId)
    {
        if (empty($tid) || empty($serviceId)) {
            return false;
        }

        $sql = "DELETE FROM skill_crm_service_type WHERE template_id='$tid' AND service_type_id='$serviceId' LIMIT 1";
        $is_deleted = $this->getDB()->query($sql);
        if ($is_deleted) {
            $this->addToAuditLog('Skill CRM Service Type Deleted', 'D', "template_id=$tid, service_type_id=$serviceId", "");
        }
        return $is_deleted;
    }

    public function addService($tid, $service)
    {
        if (empty($service->disposition_id)) {
            return false;
        }

        if (!empty($service->parent_id)) {
            $response = $this->getDispositionById($service->parent_id, $tid);
            $service->partition_id = $response->partition_id;
        }

        $sql = "INSERT INTO skill_crm_disposition_code SET template_id='$tid', ".
            "disposition_id='$service->disposition_id', parent_id='$service->parent_id', ".
            "title='$service->title', altr_disposition_id='$service->altr_disposition_id',".
            " disposition_type='{$service->disposition_type}', partition_id='{$service->partition_id}' ";

        if ($this->getDB()->query($sql)) {
            $this->rebuildDispositionTree('', 0);
            $this->addToAuditLog('Skill CRM Disposition Code', 'A', "Disposition Code=".$service->disposition_id, "Title=$service->title");
            return true;
        }
        return false;
    }


    public function addTicketingLog($callid, $dataObj)
    {
        if (empty($callid)) {
            return false;
        }

        $sql = "INSERT INTO crm_ticket_log SET ".
            "callid='$callid', ".
            "template_id='$dataObj->template_id', ".
            "service_id='$dataObj->service_id', ".
            "agent_id='$dataObj->agent_id', ".
            "email_to='$dataObj->email_to', ".
            "email_subject='$dataObj->email_subject', ".
            "email_body='$dataObj->email_body', ".
            "tstamp=UNIX_TIMESTAMP()";

        if ($this->getDB()->query($sql)) {
            $this->addToAuditLog('Skill CRM Ticket Email', 'A', "Subject=".$dataObj->email_subject, "Service=$dataObj->service_id");
            return true;
        }
        return false;
    }

    public function deleteDispositionId($dcode, $tid)
    {
        $dcodeinfo = $this->getDispositionById($dcode, $tid);
        if (!empty($dcodeinfo)) {
            $sql = "DELETE FROM skill_crm_disposition_code WHERE disposition_id='$dcode' AND template_id='$tid' LIMIT 1";
            if ($this->getDB()->query($sql)) {
                $this->addToAuditLog('Skill CRM Disposition Code', 'D', "Disposition Code=$dcode", "Title=".$dcodeinfo->title);
                return true;
            }
        }
        return false;
    }

    public function getSkillCrmFields()
    {
        $dbName = $this->getDB()->db_s;
        $sql = "SELECT column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$dbName' AND TABLE_NAME='skill_crm'";
        $return = $this->getDB()->query($sql);
        if (is_array($return)) {
            return $return;
        }
        return null;
    }

    /**
     * @param $mainobj
     * @param string $partition_id
     * @return array|bool
     */
    public function changePartition($mainobj, $partition_id='')
    {
        $sql = "UPDATE skill_crm_disposition_code SET partition_id='{$partition_id}' ";
        $sql .=" WHERE template_id='{$mainobj->template_id}' AND  lft >= {$mainobj->lft} AND rgt <= {$mainobj->rgt} ";

        return $this->getDB()->query($sql);
    }

    /**
     * @param $mainobj
     * @param string $status
     * @return array|bool
     */
    public function changeStatus($mainobj, $status='')
    {
        $sql = "UPDATE skill_crm_disposition_code SET status='{$status}' ";
        $sql .=" WHERE template_id='{$mainobj->template_id}' AND  lft >= {$mainobj->lft} AND rgt <= {$mainobj->rgt} ";

        return $this->getDB()->query($sql);
    }
}
