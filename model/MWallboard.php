<?php

class MWallboard extends Model
{
	function __construct() {
		parent::__construct();
	}

	function numNotices()
	{
		$cond = '';
		$sql = "SELECT COUNT(id) AS numrows FROM wallboard_notice ";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getNotices($offset=0, $limit=0)
	{
		$sql = "SELECT * FROM wallboard_notice ORDER BY id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}

	function numScrollTexts()
	{
		$cond = '';
		$sql = "SELECT COUNT(id) AS numrows FROM wallboard_scroll_text ";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getScrollTexts($offset=0, $limit=0)
	{
		$sql = "SELECT * FROM wallboard_scroll_text ORDER BY id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}

	function getScrollTextById($tid)
	{
		$sql = "SELECT * FROM wallboard_scroll_text WHERE id='$tid'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	function addScrollText($scroll_text)
	{
		if (empty($scroll_text->txt)) return false;
		$id = time();
		$sql = "INSERT INTO wallboard_scroll_text SET ".
			"txt='$scroll_text->txt', ".
			"id='$id', ".
			"active='$scroll_text->active'";
		
		if ($this->getDB()->query($sql)) {
			$this->addToAuditLog('Wallboard Scroll Text', 'A', "Text=".$scroll_text->txt, '');
			return true;
		}
		return false;
	}
	
	function updateScrollText($oldst, $scroll_text, $value_options)
	{
		if (empty($oldst->id)) return false;
		
		if ($oldst->txt != $scroll_text->txt || $oldst->active != $scroll_text->active) {
		
			$is_update = false;
			$fields_array = array(
				'txt' => 'txt',
				'active' => 'active'
			);
		
			$changed_fields = $this->getSqlOfChangedFields($oldst, $scroll_text, $fields_array);
		
			if (!empty($changed_fields)) {
				$sql = "UPDATE wallboard_scroll_text SET $changed_fields WHERE id='$oldst->id'";
				$is_update = $this->getDB()->query($sql);
			}
			
			if ($is_update) {
				$field_names = array(
					'txt' => 'Text',
					'active' => 'Active'
				);

				$audit_text = $this->getAuditText($oldst, $scroll_text, $fields_array, $field_names, $value_options);
				$audit_text = rtrim($audit_text, ";");
				$this->addToAuditLog('Wallboard Scroll Text', 'U', "ID=".$oldst->id, $audit_text);
			}

			return $is_update;
		
		}
		
		return false;
	}

	function deleteScrollText($tid)
	{
		$textinfo = $this->getScrollTextById($tid); 
		if (!empty($textinfo)) {
			$sql = "DELETE FROM wallboard_scroll_text WHERE id='$tid' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$this->addToAuditLog('Wallboard Scroll Text', 'D', "Text=".$textinfo->txt, '');
				return true;
			}
		}
		return false;
	}

	function getNoticeById($nid)
	{
		$sql = "SELECT * FROM wallboard_notice WHERE id='$nid'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	function addNotice($notice)
	{
		if (empty($notice->title) || empty($notice->notice)) return false;
		$id = time();
		$sql = "INSERT INTO wallboard_notice SET ".
			"id='$id', ".
			"title='$notice->title', ".
			"notice='$notice->notice', ".
			"active='$notice->active'";
		
		if ($this->getDB()->query($sql)) {
			$this->addToAuditLog('Wallboard Notice', 'A', "Title=".$notice->title, '');
			return $id;
		}
		return '';
	}

	function updateNotice($oldnotice, $notice, $value_options)
	{
		if (empty($oldnotice->id)) return false;
		
		if ($oldnotice->title != $notice->title || $oldnotice->notice != $notice->notice || $oldnotice->active != $notice->active) {
		
			$is_update = false;
			$fields_array = array(
				'title' => 'title',
				'notice' => 'notice',
				'active' => 'active'
			);
		
			$changed_fields = $this->getSqlOfChangedFields($oldnotice, $notice, $fields_array);
		
			if (!empty($changed_fields)) {
				$sql = "UPDATE wallboard_notice SET $changed_fields WHERE id='$oldnotice->id'";
				$is_update = $this->getDB()->query($sql);
			}
			
			if ($is_update) {
				$field_names = array(
					'title' => 'Title',
					'notice' => 'Notice',
					'active' => 'Active'
				);

				$audit_text = $this->getAuditText($oldnotice, $notice, $fields_array, $field_names, $value_options);
				$audit_text = rtrim($audit_text, ";");
				$this->addToAuditLog('Wallboard Notice', 'U', "ID=".$oldnotice->id, $audit_text);
			}

			return $is_update;
		
		}
		
		return false;
	}

	function deleteNotice($nid)
	{
		$noticeinfo = $this->getNoticeById($nid); 
		if (!empty($noticeinfo)) {
			$sql = "DELETE FROM wallboard_notice WHERE id='$nid' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$this->addToAuditLog('Wallboard Notice', 'D', "Text=".$noticeinfo->title, '');
				return true;
			}
		}
		return false;
	}
	
	
	function numVideos()
	{
		$cond = '';
		$sql = "SELECT COUNT(id) AS numrows FROM wallboard_video ";
		$result = $this->getDB()->query($sql);

		if($this->getDB()->getNumRows() == 1) {
			return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
		}

		return 0;
	}
	
	function getVideos($offset=0, $limit=0)
	{
		$sql = "SELECT * FROM wallboard_video ORDER BY id ";
		if ($limit > 0) $sql .= "LIMIT $offset, $limit";

		return $this->getDB()->query($sql);
	}
	
	function getVideoById($vid)
	{
		$sql = "SELECT * FROM wallboard_video WHERE id='$vid'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}
	
	function getYouTubeID($source)
	{
		$pos = strpos($source, '?');
		if ($pos !== false) {
			parse_str(substr($source, $pos+1), $params);
			if (isset($params['v'])) return $params['v'];
		}
		
		return '';
	}
	
	function updateVideoReference($video_id, $ref)
	{
		$sql = "UPDATE wallboard_video SET source='$ref' WHERE id='$video_id'";
		return $this->getDB()->query($sql);
	}
	
	function addVideo($video)
	{
		if (empty($video->title)) return false;
		
		if ($video->source_type == 'YouTube') $source = $this->getYouTubeID($video->source);
		$source = $video->source;
		$id = time();
		//$id = 'a' . substr($id, 1);
		$src = $video->source_type == 'Local' ? '' : "source='$source', ";
		$sql = "INSERT INTO wallboard_video SET ".
			"id='$id', ".
			"title='$video->title', ".
			"source_type='$video->source_type', ".
			$src .
			"active='$video->active'";
		//echo $sql;
		if ($this->getDB()->query($sql)) {
			$this->addToAuditLog('Wallboard Video', 'A', "Title=".$video->title, '');
			return $id;
		}
		return '';
	}

	function updateVideo($oldvideo, $video, $value_options)
	{
		if (empty($oldvideo->id)) return false;
		
		if ($video->source_type == 'YouTube') {
			$display_source = $video->source;
			$video->source = $this->getYouTubeID($video->source);
		}
		
		$is_update = false;
		if ($oldvideo->title != $video->title || $oldvideo->source_type != $video->source_type || $oldvideo->source != $video->source || $oldvideo->active != $video->active) {
		
			
			$fields_array = array(
				'title' => 'title',
				'source_type' => 'source_type',
				'active' => 'active'
			);
			$field_names = array(
				'title' => 'Title',
				'source_type' => 'Source Type',
				'active' => 'Active'
			);
			
			if ($video->source_type == 'YouTube') {
				$fields_array['source'] = 'source';
				$field_names['source'] = 'Source';
			}
		
			$changed_fields = $this->getSqlOfChangedFields($oldvideo, $video, $fields_array);
		
			if (!empty($changed_fields)) {
				$sql = "UPDATE wallboard_video SET $changed_fields WHERE id='$oldvideo->id'";
				$is_update = $this->getDB()->query($sql);
			}
			
			if ($is_update) {
				

				$audit_text = $this->getAuditText($oldvideo, $video, $fields_array, $field_names, $value_options);
				$audit_text = rtrim($audit_text, ";");
				$this->addToAuditLog('Wallboard Video', 'U', "ID=".$oldvideo->id, $audit_text);
			}

			
		
		}
		
		if ($video->source_type == 'YouTube') {
			$video->source = $display_source;
		}
		
		return $is_update;
	}
	
	function deleteVideo($vid)
	{
		$videoinfo = $this->getVideoById($vid); 
		if (!empty($videoinfo)) {
			$sql = "DELETE FROM wallboard_video WHERE id='$vid' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$this->addToAuditLog('Wallboard Video', 'D', "Title=".$videoinfo->title, '');
				return true;
			}
		}
		return false;
	}






	function getConnectionByName($cname)
	{
		$sql = "SELECT * FROM ivr_api WHERE conn_name='$cname'";
		$result = $this->getDB()->query($sql);
		return is_array($result) ? $result[0] : null;
	}

	function addConnection($connection)
	{
		if (empty($connection->conn_name)) return false;
		
		$sql = "INSERT INTO ivr_api SET ".
			"conn_name='$connection->conn_name', ".
			"conn_method='$connection->conn_method', ".
			"url='$connection->url', ".
			"credential='$connection->credential', ".
			"pass_credential='$connection->pass_credential', ".
			"submit_method='$connection->submit_method', ".
			"submit_param='$connection->submit_param', ".
			"return_method='$connection->return_method', ".
			"return_param='$connection->return_param', ".
			"active='$connection->active'";
		
		if ($this->getDB()->query($sql)) {
			$this->addToAuditLog('IVR API', 'A', "Connection=".$connection->conn_name, "Method=$connection->conn_method");
			return true;
		}
		return false;
	}
	
	function updateConnection($oldconnection, $connection, $value_options)
	{
		if (empty($oldconnection->conn_name)) return false;
		$is_update = false;
		$fields_array = array(
			'conn_method' => 'conn_method',
			'url' => 'url',
			'credential' => 'credential',
			'pass_credential' => 'pass_credential',
			'submit_method' => 'submit_method',
			'submit_param' => 'submit_param',
			'return_method' => 'return_method',
			'return_param' => 'return_param',
			'active' => 'active'
		);
		
		$changed_fields = $this->getSqlOfChangedFields($oldconnection, $connection, $fields_array);
		
		if (!empty($changed_fields)) {
			$sql = "UPDATE ivr_api SET $changed_fields WHERE conn_name='$oldconnection->conn_name'";
			$is_update = $this->getDB()->query($sql);
		}
		
		if ($is_update) {
			$field_names = array(
				'conn_method' => 'Method',
				'url' => 'URL',
				'credential' => 'Credential',
				'pass_credential' => 'Pass Credential',
				'submit_method' => 'Submit Method',
				'submit_param' => 'Submit Param',
				'return_method' => 'Return Method',
				'return_param' => 'Return Param',
				'active' => 'Status'
			);

			$audit_text = $this->getAuditText($oldconnection, $connection, $fields_array, $field_names, $value_options);
			$audit_text = rtrim($audit_text, ";");
			$this->addToAuditLog('IVR API', 'U', "Connection=".$oldconnection->conn_name, $audit_text);
		}

		return $is_update;
	}
	
	function deleteConnection($cname)
	{
		$apiinfo = $this->getConnectionByName($cname); 
		if (!empty($apiinfo)) {
			$sql = "DELETE FROM ivr_api WHERE conn_name='$cname' LIMIT 1";
			if ($this->getDB()->query($sql)) {
				$this->addToAuditLog('IVR API', 'D', "Connection=".$apiinfo->conn_name, "Method=$apiinfo->conn_method");
				return true;
			}
		}
		return false;
	}
	
	/*
	function getServiceOptions()
	{
		$options = array(''=>'Select');
		$sql = "SELECT disposition_code, service_title FROM ivr_service_code ORDER BY disposition_code ";
		$result = $this->getDB()->query($sql);
		if (is_array($result)) {
			foreach ($result as $skill) {
				$options[$skill->disposition_code] = $skill->disposition_code . ' - ' . $skill->service_title;
			}
		}
		//var_dump($options);
		return $options;
	}
	*/

}

?>