<?php
	error_reporting(7);
	set_time_limit(0);
	
	$debug = false;
	$debug_new_line = '<br />';
	//$is_live = true;
	//$script_dir = $is_live ? '' : '';
	//$script_dir = '';
	$script_dir = '';
	include_once('conf.email.php');
	include_once($script_dir . 'conf.php');
	include_once($script_dir . 'lib/DBManager.php');
	$conn = new DBManager($db);
	
	
	$previous_tstamp = time() - 172800; //48 hours
	
	$sql = "SELECT ticket_id FROM e_ticket_info WHERE last_update_time < $previous_tstamp AND status='S' LIMIT 50";
	//$sql = "UPDATE e_ticket_info SET status='E', status_updated_by='SYSTEM' WHERE last_update_time > $previous_tstamp AND status='S'";
	$result = $conn->query($sql);
	if (is_array($result)) {
		$user = 'SYSTEM';
		$now = time();
		foreach ($result as $row) {
			$sql = "UPDATE e_ticket_info SET status='E', status_updated_by='$user', last_update_time='$now' WHERE ticket_id='$row->ticket_id'";
			if ($conn->query($sql)) {
				//addETicketActivity($row->ticket_id, $user, 'S', 'E');
				$sql = "INSERT INTO e_ticket_activity SET ticket_id='$row->ticket_id', agent_id='$user', activity='S', ".
				"activity_details='E', activity_time='$now'";
				$conn->query($sql);
			}
		}
	}
