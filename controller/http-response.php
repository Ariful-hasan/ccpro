<?php
class HttpResponse extends Controller{
	function init(){
		
	}
	function action404(){
	
	}
	function actionIeUpgrade(){
		$templateData=array();
		$this->getTemplate()->display_only("ie_upgrade",$templateData);
	}
}