<?php

class MAutoDial extends Model
{
	function __construct() {
		parent::__construct();
	}

	function getBatchCount()
	{
		$sql = "SELECT COUNT(batch_id) AS total FROM dialout_batch";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->total) ? 0 : $result[0]->total;
		}

		return 0;
	}
	
	function getBatchesList($offset=0, $rowsPerPage=20)
	{
		$sql = "SELECT bh.batch_id, bh.action_type, bh.var1, bh.active, bh.stop_time, bh.skill_id, bh.retry, bh.retry_interval, bh.mode_of_dial, COUNT(nb.batch_id) AS total ".
			"FROM dialout_batch AS bh LEFT JOIN dialout_number AS nb ON nb.batch_id=bh.batch_id GROUP BY bh.batch_id ORDER BY bh.batch_id DESC";
		$sql .= " LIMIT $offset, $rowsPerPage";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function getDialoutById($bid)
	{
		$sql = "SELECT * FROM dialout_batch WHERE batch_id='$bid'";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return $result[0];
		}
		return null;
	}
	
	function numDialoutNumbersByBatch($batchid)
	{
		$sql = "SELECT COUNT(batch_id) AS total FROM dialout_number WHERE batch_id='$batchid'";
		$result = $this->getDB()->query($sql);
		//echo $sql;
		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->total) ? 0 : $result[0]->total;
		}

		return 0;
	}
	
	function getDialoutNumbersByBatch($batchid, $offset=0, $rowsPerPage=0, $orderBy='number_1', $orderType='ASC')
	{
		$sql = "SELECT * FROM dialout_number WHERE batch_id='$batchid' ORDER BY $orderBy $orderType";
		if ($rowsPerPage > 0) $sql .= " LIMIT $offset, $rowsPerPage";
		return $this->getDB()->query($sql);
	}
	
	function removeBatchFile($flid)
	{
		if (empty($flid)) return false;

		$sql = "UPDATE dialout_batch SET skill_id='' WHERE skill_id='$flid' AND action_type='PF'";
		//echo $sql;
		return $this->getDB()->query($sql);
	}
	
	function updateBatch($bid, $batch, $ltext)
	{
		if (empty($bid)) return false;

		$stop_time = $batch->stop_date . ' ' . $batch->stop_hhmm . ':00';
		if ($batch->action_type == 'SQ') $batch->var1 = '';

		$sql = "UPDATE dialout_batch SET action_type='$batch->action_type', skill_id='$batch->skill_id', retry='$batch->retry', ".
			"retry_interval='$batch->retry_interval', stop_time='$stop_time', var1='$batch->var1' WHERE batch_id='$bid'";
		//echo $sql;
		if ($this->getDB()->query($sql)) {
			$this->addToAuditLog('Batch', 'U', "Batch=$bid", $ltext);
			return true;
		}
		return false;
	}
	
	function addBatch($batch, $ltext)
	{
		$batch_id = $this->getMaxBatchID();
		
		if (empty($batch_id)) { $batch_id ='AAAAAA'; }
		else if ( $batch_id == 'ZZZZZZ') { return false; }
		else { $batch_id++; }
		
		$stop_time = $batch->stop_date . ' ' . $batch->stop_hhmm . ':00';
		if ($batch->action_type == 'SQ') $batch->var1 = '';

		$sql = "INSERT INTO dialout_batch SET batch_id='$batch_id', action_type='$batch->action_type', skill_id='$batch->skill_id', retry='$batch->retry', ".
			"retry_interval='$batch->retry_interval', stop_time='$stop_time', active='N', var1='$batch->var1'";
		if ($this->getDB()->query($sql)) {
			$this->addToAuditLog('Batch', 'A', "Batch=$batch_id", $ltext);
			return $batch_id;
		}
		
		return false;
	}
	
	function getMaxBatchID()
	{
		$sql = "SELECT MAX(batch_id) AS numrows FROM dialout_batch";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			return empty($result[0]->numrows) ? '' : $result[0]->numrows;
		}
		return '';
	}
	
	function addDialoutNumber($batch_id, $num)
	{
		if (!is_array($num)) return false;
		$num1 = isset($num['number1']) ? trim($num['number1']) : '';
		if (!empty($num1)) {
			$num2 = isset($num['number2']) ? trim($num['number2']) : '';
			$num3 = isset($num['number3']) ? trim($num['number3']) : '';
			$sql = "INSERT IGNORE INTO dialout_number SET batch_id='$batch_id', number_1='$num1', number_2='$num2', number_3='$num3'";
			return $this->getDB()->query($sql);
		}
		return false;
	}
	
	function deleteBatch($bid)
	{
		$batchinfo = $this->getDialoutById($bid); 
		if (!empty($batchinfo)) {
			$sql = "DELETE FROM dialout_batch WHERE batch_id='$bid'";
			if ($this->getDB()->query($sql)) {
				$sql = "DELETE FROM dialout_number WHERE batch_id='$bid'";
				$this->getDB()->query($sql);
				
				$num_numbers =  $this->getDB()->getAffectedRows();
				$this->addToAuditLog('Batch', 'D', "Batch=$bid", "Numbers=$num_numbers;Retry=".$batchinfo->retry);
				return true;
			}
		}
		return false;
	}
	
	function deleteNumber($bid, $num)
	{
		$sql = "DELETE FROM dialout_number WHERE batch_id='$bid' AND number_1='$num' AND LENGTH(callid)=0";
		if ($this->getDB()->query($sql)) {
			$this->addToAuditLog('Batch', 'D', "Batch=$bid", "Number=$num");
			return true;
		}
		return false;
	}
	
	function updateBatchStatus($bid, $status='')
	{
		if (empty($bid)) return false;
		if ($status=='Y' || $status=='N') {
			$sql = "UPDATE dialout_batch SET active='$status' WHERE batch_id='$bid'";
			if ($this->getDB()->query($sql)) {

				$ltxt = $status=='Y' ? 'Inactive to Active' : 'Active to Inactive';
				$this->addToAuditLog('Batch', 'U', "Batch=$bid", "Status=".$ltxt);
				return true;
			}
		}
		return false;
	}

}

?>