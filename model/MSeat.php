<?php

class MSeat extends Model
{
	function __construct() {
		parent::__construct();
	}

	

	function updateSeatMac($seat_id,$mac)
	{
		if (empty($seat_id)) return false;
		if (empty($mac)) return false;
		$sql = "UPDATE seat SET pc_mac='$mac' WHERE seat_id='$seat_id'";
		return $this->getDB()->query($sql);
	}
	
	function getAllSeat($limit=10)
	{		
		if(!empty($limit)){
			$limit="LIMIT $limit";
		}
		$sql = "SELECT agent_id,pc_ip,pc_local_ip,pc_mac FROM seat $limit";
		return $this->getDB()->query($sql);
	}

    public function getSeatIdLabelAsKeyValue($active = 'Y')
    {
        $seats = [];
        $response = $this->getDB()->query("SELECT seat_id, label FROM seat WHERE active='Y'");
        if (!empty($response)){
            foreach ($response as $seat_id => $seat_label){
                $seats[$seat_id] = $seat_label;
            }
        }

        return $seats;
	}

}

?>