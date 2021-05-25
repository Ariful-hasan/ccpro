<?php

class MCoBrowser extends Model
{
	public function __construct() {
		parent::__construct();
	}

    public function getCoBrowserLink(){
        $sql = "SELECT * FROM co_browser_link";
        // echo $sql;
        // die();

        return $this->getDB()->query($sql);
    }
}

?>