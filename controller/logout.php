<?php

class Logout extends Controller
{
	function __construct() {
		parent::__construct();
	}

	function init()
	{
		//if (UserAuth::isLoggedIn()) {
			UserAuth::logout();
		//}
		
		$this->getTemplate()->redirect("./index.php?task=login");
	}
}
?>