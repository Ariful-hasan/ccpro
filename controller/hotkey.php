<?php
class Hotkey extends Controller
{
	function __construct() {		
		parent::__construct();
	}
	
	function init()
	{
		$this->actionList();
	}
	function actionList(){
		$data['pageTitle'] = 'Hotkey List';
		
		//$data['topMenuItems'] = array(array('href'=>'task=hotkey&act=add', 'img'=>'fa fa-user', 'label'=>'Add New Hotkey','class'=>'lightboxWIFR','dataattr'=>array('w'=>500,'h'=>300)));
		
		$data['side_menu_index'] = 'settings';
		$data['dataUrl'] = $this->url('task=get-tools-data&act=hotkey');		
		$this->getTemplate()->display('hotkey_list', $data);
	}	
	function actionAdd()
	{		
		$action=$this->getRequest()->getRequest('action');
		AddModel("MAgentHotKey");
		$hotkey_model = new MAgentHotKey();
		$hotkey=$hotkey_model->getRowByKey($action);		
		$data=array();
		
		$data['pageTitle'] = 'Edit Hotkey';
		$data['hotkeyoptions'] = array();
		$allhotkeys=MAgentHotKey::getAll();
		foreach (array_merge(range('A','Z'),range(1,9)) as $hkey){
			$data['hotkeyoptions'][$hkey]=$hkey;
		}
		foreach ($allhotkeys as $ehotkey){						
			$hhkey=str_replace('CTRL+', "", strtoupper($ehotkey->hot_key));
			if($ehotkey->hot_key!=$hotkey->hot_key){
				if(isset($data['hotkeyoptions'][$hhkey])){
					unset($data['hotkeyoptions'][$hhkey]);
				}
			}
		}
		$hotkey->hot_key=str_replace('CTRL+', "", strtoupper($hotkey->hot_key));
		$data['hotkey']=&$hotkey;
		if($this->request->isPost()){
			$selectedhotkey="CTRL+".$this->getRequest()->getPost('hot_key');
			if($hotkey_model->updateProperties(array("hot_key"=>"$selectedhotkey"), $action)){
				AddInfo("Success fully updated");
				$this->getTemplate()->display_popup_msg($data,true);
			}					
		}
			
		$this->getTemplate()->display_popup('add-hotkey', $data,true);
	}
	
}