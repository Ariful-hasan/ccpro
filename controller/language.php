<?php
class Language extends Controller
{
	function __construct() {		
		parent::__construct();
	}
	
	function init()
	{
		$this->actionLanguageList();
	}
	function actionLanguageList()
	{
		include_once ('model/MLanguage.php');
		$lang_model = new MLanguage();
		$data['pageTitle'] = 'Language List';
		if($lang_model->IsCanAddNewLanguage()>0){
			$data['topMenuItems'] = array(array('href'=>'task=language&act=add', 'img'=>'fa fa-user', 'label'=>'Add New Language','class'=>'lightboxWIFR','dataattr'=>array('w'=>500,'h'=>300)));
		}
		$data['side_menu_index'] = 'settings';
		$data['dataUrl'] = $this->url('task=get-tools-data&act=language');		
		$this->getTemplate()->display('language_list', $data);
	}	
	function actionAdd()
	{		
		include_once ('model/MLanguage.php');
		$lang_model = new MLanguage();
		$data=array();
		if($this->request->isPost()){
			
			$key=$this->request->getPost('lang_key');
			if(!empty($key)){
				$options=GetLanguageList();
				if(isset($options[$key])){
					$lang_title=$options[$key];
					
					if($lang_model->AddLanguage($key, $lang_title)){					
						$data=array(
								'pageTitle'=>'Add Language',
								'isError'=>false,
								'msg'=>'Successfully added');
							
						$this->getTemplate()->display_popup('msg',$data);
						return;
					}else{
						$data['errMsg']="Failed to add language";
						$data['errType']=0;
					}
				}
				
			}
			
			
		}		
		$data['pageTitle'] = 'Choose Language';	
		$data['lan_result']=$lang_model->getAllLanguage();
		$this->getTemplate()->display_popup('add-language', $data,true);
	}
	
}