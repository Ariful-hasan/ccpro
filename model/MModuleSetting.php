<?php

class MModuleSetting extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function generateId()
    {
        return time() . rand(10000, 99999) . rand(10000, 99999);
    }

    private function getCurrentTime($format = "Y-m-d H:i:s")
    {
        return date($format);
    }

    private function setModulesData($moduleId = null, $limit = 0, $offset = 0)
    {
        $this->tables = "modules";
        $this->columns = "*";
        if (!empty($moduleId)) {
            $this->conditions = "module_id = '{$moduleId}'";
        }
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function numModules($moduleId = null)
    {
        $this->setModulesData($moduleId);
        return $this->getResultCount("module_id");
    }

    public function getModules($moduleId = null, $limit = 0, $offset = 0)
    {
        $this->setModulesData($moduleId, $limit, $offset);
        return $this->getResultData();
    }

    private function setAddModuleData($moduleCode, $moduleTitle)
    {
        $currentDateTime = $this->getCurrentTime();
        $this->tables = "modules";
        $this->columns = "module_id = '{$this->generateId()}',";
        $this->columns .= "code = '{$moduleCode}',";
        $this->columns .= "title = '{$moduleTitle}',";
        $this->columns .= "created_at = '{$currentDateTime}',";
        $this->columns .= "updated_at = '{$currentDateTime}'";
    }

    public function addModule($moduleCode, $moduleTitle)
    {
        $this->setAddModuleData($moduleCode, $moduleTitle);
        return $this->getInsertResult();
    }

    private function setUpdateModuleData($moduleId, $moduleCode, $moduleTitle)
    {
        $this->tables = "modules";
        $this->columns .= "code = '{$moduleCode}',";
        $this->columns .= "title = '{$moduleTitle}',";
        $this->columns .= "updated_at = '{$this->getCurrentTime()}'";
        $this->conditions = "module_id = '{$moduleId}'";
        $this->limit = 1;
    }

    public function updateModule($moduleId, $moduleCode, $moduleTitle)
    {
        $this->setUpdateModuleData($moduleId, $moduleCode, $moduleTitle);
        return $this->getUpdateResult();
    }

    private function setDeleteModuleData($moduleId)
    {
        $this->tables = "modules";
        $this->conditions = "module_id = '{$moduleId}'";
        $this->limit = 1;
    }

    public function deleteModule($moduleId)
    {
        $this->setDeleteModuleData($moduleId);
        return $this->getDeleteResult();
    }

    private function setModuleSettingsData($moduleId = null, $limit = 0, $offset = 0)
    {
        $this->tables = "module_settings";
        $this->columns = "*";
        if (!empty($moduleId)) {
            $this->conditions = "module_id = '{$moduleId}'";
        }
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function numModuleSettings($moduleId = null)
    {
        $this->setModuleSettingsData($moduleId);
        return $this->getResultCount("settings_id");
    }

    public function getModuleSettings($moduleId = null, $limit = 0, $offset = 0)
    {
        $this->setModuleSettingsData($moduleId, $limit, $offset);
        return $this->getResultData();
    }

    private function setAddModuleSettingsData($addSettingsData)
    {
        $currentDateTime = $this->getCurrentTime();
        $this->tables = "module_settings";
        $this->columns = "settings_id = '{$this->generateId()}',";
        $this->columns .= "module_id = '{$addSettingsData->moduleId}',";
        $this->columns .= "type = '{$addSettingsData->type}',";
        $this->columns .= "title = '{$addSettingsData->title}',";
        $this->columns .= "name = '{$addSettingsData->name}',";
        $this->columns .= "value = '{$addSettingsData->value}',";
        $this->columns .= "created_at = '{$currentDateTime}',";
        $this->columns .= "updated_at = '{$currentDateTime}'";
    }

    public function addModuleSettings($addSettingsData)
    {
        $this->setAddModuleSettingsData($addSettingsData);
        return $this->getInsertResult();
    }

    private function setUpdateModuleSettingsData($updateSettingsData)
    {
        $currentDateTime = $this->getCurrentTime();
        $this->tables = "module_settings";
        $this->columns .= "type = '{$updateSettingsData->type}',";
        $this->columns .= "title = '{$updateSettingsData->title}',";
        $this->columns .= "name = '{$updateSettingsData->name}',";
        $this->columns .= "value = '{$updateSettingsData->value}',";
        $this->columns .= "updated_at = '{$currentDateTime}'";
        $this->conditions = "settings_id = '{$updateSettingsData->settingsId}'";
        $this->limit = 1;
    }

    public function updateModuleSettings($updateSettingsData)
    {
        $this->setUpdateModuleSettingsData($updateSettingsData);
        return $this->getUpdateResult();
    }

    private function setDeleteModuleSettingsData($settingsId)
    {
        $this->tables = "module_settings";
        $this->conditions = "settings_id = '{$settingsId}'";
        $this->limit = 1;
    }

    public function deleteModuleSettings($settingsId)
    {
        $this->setDeleteModuleSettingsData($settingsId);
        return $this->getDeleteResult();
    }

    private function setSettingsByIdData($settingsId = null)
    {
        $this->tables = "module_settings";
        $this->columns = "*";
        if (!empty($settingsId)) {
            $this->conditions = "settings_id = '{$settingsId}'";
        }
        $this->limit = 1;
    }

    public function getSettingsById($settingsId = null)
    {
        $this->setSettingsByIdData($settingsId);
        return $this->getResultData();
    }
}