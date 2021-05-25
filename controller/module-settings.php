<?php
require_once 'BaseTableDataController.php';

class ModuleSettings extends BaseTableDataController
{
    public $moduleSettingsModel;
    public $moduleId;
    public $errorResponseObject;
    public $results;

    public function __construct()
    {
        parent::__construct();
        $this->includeClassFiles();
        $this->errorResponseObject = new AjaxResponse();
        $this->moduleSettingsModel = new MModuleSetting();
    }

    public function init()
    {
        $this->actionModules();
    }

    private function includeClassFiles()
    {
        include("controller/ajax-response.php");
        include("model/MModuleSetting.php");
    }

    private function getMenuButton($href, $title)
    {
        return array(
            'href' => $href,
            'label' => $title,
            'title' => $title
        );
    }

    public function actionModules()
    {
        $data['pageTitle'] = 'Module Settings Panel';
        $data['topMenuItems'] = array($this->getMenuButton('task=module-settings&act=add-module', 'Add New Module'));

        $data['dataUrl'] = $this->url('task=module-settings&act=get-modules');
        $view = $this->request->getControllerName() . '/modules';
        $this->getTemplate()->display($view, $data);
    }

    public function actionGetModules()
    {
        $this->pagination->num_records = $this->moduleSettingsModel->numModules("");
        $this->results = $this->pagination->num_records > 0 ?
            $this->moduleSettingsModel->getModules("", $this->pagination->rows_per_page, $this->pagination->getOffset()) : null;

        $this->modifyModuleData();
        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;
        $response->rowdata = $this->results;
        $this->ShowTableResponse();
    }

    private function modifyModuleData()
    {
        foreach ($this->results as $result) {
            $result->action = "<a title='Update' class='btn btn-xs btn-primary' href='" .
                              $this->url("task=module-settings&act=update-module&moduleId=" . $result->module_id) .
                              "'><i class='fa fa-edit'></i> Update </a> ";

            $result->action .= "<a class='btn btn-xs btn-danger confirm-status-link' onclick='confirm_status(event)' 
                            data-msg='Do you confirm that you want to delete {$result->title}?' 
                            data-href='" . $this->url("task=module-settings&act=delete-module&moduleId=" .
                            $result->module_id) . "' title='Delete'> <i class='fa fa-trash'></i> Delete </a>";

            $result->title = "<a  href='" . $this->url("task=module-settings&act=module-settings&moduleId=" .
                            $result->module_id) . "'> $result->title </a> ";
        }
    }

    public function actionAddModule()
    {
        if ($this->getRequest()->isPost()) {
            if ($this->addModule()) {
                $this->errorResponseObject->setSuccessResponse("Module Added Successfully!");
            } else {
                $this->errorResponseObject->setFailedResponse("Module Add Failed!");
            }
        }
        $data['topMenuItems'] = array($this->getMenuButton('task=module-settings&act=modules', 'Previous Page'));

        $data['errorData'] = $this->errorResponseObject->getResponseData();
        $data['pageTitle'] = 'Add New Module';
        $view = $this->request->getControllerName() . '/add_module';
        $this->getTemplate()->display($view, $data);
    }

    private function addModule()
    {
        $request = $this->getRequest();
        $moduleCode = $request->getRequest('moduleCode');
        $modeuleTitle = $request->getRequest('moduleTitle');
        if ($this->moduleSettingsModel->addModule($moduleCode, $modeuleTitle)) {
            return true;
        }
        return false;
    }

    public function actionUpdateModule()
    {
        $request = $this->getRequest();
        $moduleId = $request->getRequest('moduleId');
        if ($this->getRequest()->isPost()) {
            $moduleCode = $request->getRequest('moduleCode');
            $moduleTitle = $request->getRequest('moduleTitle');
            if ($this->updateModule($moduleId, $moduleCode, $moduleTitle)) {
                $this->errorResponseObject->setSuccessResponse("Module Updated Successfully!");
            } else {
                $this->errorResponseObject->setFailedResponse("Module Update Failed!");
            }
        }
        $moduleData = reset($this->moduleSettingsModel->getModules($moduleId));

        $data['topMenuItems'] = array($this->getMenuButton('task=module-settings&act=modules', 'Previous Page'));
        $data['moduleCode'] = $moduleData->code;
        $data['moduleTitle'] = $moduleData->title;
        $data['moduleId'] = $moduleData->module_id;
        $data['errorData'] = $this->errorResponseObject->getResponseData();
        $data['pageTitle'] = 'Update New Module';
        $view = $this->request->getControllerName() . '/update_module';
        $this->getTemplate()->display($view, $data);
    }

    private function updateModule($moduleId, $moduleCode, $moduleTitle)
    {
        if ($this->moduleSettingsModel->updateModule($moduleId, $moduleCode, $moduleTitle)) {
            return true;
        }
        return false;
    }

    public function actionDeleteModule()
    {
        $this->moduleId = $this->getRequest()->getRequest('moduleId');
        if ($this->moduleSettingsModel->deleteModule($this->moduleId)) {
            $this->errorResponseObject->printSuccessResponse("Module Removed Successfully!");
        }
        $this->errorResponseObject->printFailedResponse("Module Removed Failed!");
    }

    public function actionModuleSettings()
    {
        $this->moduleId = $this->getRequest()->getRequest('moduleId');
        $moduleData = $this->moduleSettingsModel->getModules($this->moduleId, 1);
        $moduleName = $moduleData[0]->title;
        $data['pageTitle'] = $moduleName.' : Settings';
        $data['topMenuItems'] = array(
            $this->getMenuButton('task=module-settings&act=add-module-settings&moduleId=' . $this->moduleId, 'Add New Settings'),
            $this->getMenuButton('task=module-settings&act=modules', 'Previous Page')
        );
        $data['dataUrl'] = $this->url('task=module-settings&act=get-module-settings&moduleId=' . $this->moduleId);
        $view = $this->request->getControllerName() . '/module_settings';
        $this->getTemplate()->display($view, $data);
    }

    public function actionGetModuleSettings()
    {
        $moduleId = $this->getRequest()->getRequest('moduleId');
        $this->pagination->num_records = $this->moduleSettingsModel->numModuleSettings($moduleId);
        $this->results = $this->pagination->num_records > 0 ?
            $this->moduleSettingsModel->getModuleSettings($moduleId, $this->pagination->rows_per_page, $this->pagination->getOffset()) : null;

        $this->modifyModuleSettingsData();
        $response = $this->getTableResponse();
        $response->records = $this->pagination->num_records;
        $response->rowdata = $this->results;
        $this->ShowTableResponse();
    }

    private function modifyModuleSettingsData()
    {
        foreach ($this->results as $result) {
            $result->action = "<a title='Update' class='btn btn-xs btn-primary' href='" .
                $this->url("task=module-settings&act=update-module-settings&settingsId=" . $result->settings_id) .
                "'><i class='fa fa-edit'></i> Update </a> ";

            $result->action .= "<a title='Delete' class='confirm-status-link' onclick='confirm_status(event)' 
                               data-msg='Do you confirm that you want to delete {$result->title}?' data-href='" .
                               $this->url("task=module-settings&act=delete-module-settings&settingsId=" .
                               $result->settings_id ) . "'> <i class='fa fa-trash'></i> Delete </a>";
        }
    }

    public function actionAddModuleSettings()
    {
        $this->moduleId = $this->getRequest()->getRequest('moduleId');

        if ($this->getRequest()->isPost()) {
            if ($this->addModuleSettings()) {
                $this->errorResponseObject->setSuccessResponse("Settings Added Successfully!");
            } else {
                $this->errorResponseObject->setFailedResponse("Settings Add Failed!");
            }
        }
        $data['topMenuItems'] = array($this->getMenuButton('task=module-settings&act=module-settings&moduleId=' . $this->moduleId, 'Previous Page'));
        $data['errorData'] = $this->errorResponseObject->getResponseData();
        $data['moduleId'] = $this->moduleId;
        $data['pageTitle'] = 'Add New Settings';
        $view = $this->request->getControllerName() . '/add_settings';
        $this->getTemplate()->display($view, $data);
    }

    private function addModuleSettings()
    {
        $request = $this->getRequest();
        $addSettingsData = new stdClass();
        $addSettingsData->type = $request->getRequest('type');
        $addSettingsData->title = $request->getRequest('title');
        $addSettingsData->name = $request->getRequest('name');
        $addSettingsData->value = $request->getRequest('value');
        $addSettingsData->moduleId = $this->moduleId;
        if ($this->moduleSettingsModel->addModuleSettings($addSettingsData)) {
            return true;
        }
        return false;
    }

    public function actionUpdateModuleSettings()
    {
        $settingsId = $this->getRequest()->getRequest('settingsId');

        if ($this->getRequest()->isPost()) {
            if ($this->updateModuleSettings($settingsId)) {
                $this->errorResponseObject->setSuccessResponse("Settings Updated Successfully!");
            } else {
                $this->errorResponseObject->setFailedResponse("Settings Update Failed!");
            }
        }
        $settingsData = reset($this->moduleSettingsModel->getSettingsById($settingsId));

        $data['topMenuItems'] = array($this->getMenuButton('task=module-settings&act=module-settings&moduleId=' . $settingsData->module_id, 'Previous Page'));
        $data['errorData'] = $this->errorResponseObject->getResponseData();
        $data['moduleId'] = $settingsData->module_id;
        $data['settingsId'] = $settingsId;
        $data['type'] = $settingsData->type;
        $data['title'] = $settingsData->title;
        $data['name'] = $settingsData->name;
        $data['value'] = $settingsData->value;
        $data['pageTitle'] = 'Update Settings';
        $view = $this->request->getControllerName() . '/update_settings';
        $this->getTemplate()->display($view, $data);
    }

    private function updateModuleSettings($settingsId)
    {
        $request = $this->getRequest();
        $addSettingsData = new stdClass();
        $addSettingsData->type = $request->getRequest('type');
        $addSettingsData->title = $request->getRequest('title');
        $addSettingsData->name = $request->getRequest('name');
        $addSettingsData->value = $request->getRequest('value');
        $addSettingsData->settingsId = $settingsId;
        if ($this->moduleSettingsModel->updateModuleSettings($addSettingsData)) {
            return true;
        }
        return false;
    }

    public function actionDeleteModuleSettings()
    {
        $settingsId = $this->getRequest()->getRequest('settingsId');
        if ($this->moduleSettingsModel->deleteModuleSettings($settingsId)) {
            $this->errorResponseObject->printSuccessResponse("Settings Removed Successfully!");
        }
        $this->errorResponseObject->printFailedResponse("Settings Removed Failed!");
    }
}