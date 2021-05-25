<?php

class Wallboard extends Controller
{
	var $supported_video_types = array('flv', 'mp4');

	function __construct() {
		parent::__construct();
	}

	function init()
	{
	    $this->actionWallBoard();
	}
	
	function actionWallBoard()
	{
	    $data['pageTitle'] = 'Wallboard Scroll Texts';
	    $data['topMenuItems'] = array($this->getNoticeMenuLink(), $this->getVideoMenuLink(),
	        array('href'=>'task=wallboard&act=add-scroll-text', 'img'=>'add.png', 'label'=>'Add Scroll Text'));
	    $data['side_menu_index'] = 'settings';
	    $this->getTemplate()->display('wallboard_scroll_texts', $data);
	}
	
	function init2()
	{
		$this->actionScrollTexts();
	}

	function getNoticeMenuLink()
	{
		return array('href'=>'task=wallboard&act=notices', 'img'=>'script.png', 'label'=>'Notice');
	}
	
	function getVideoMenuLink()
	{
		return array('href'=>'task=wallboard&act=videos', 'img'=>'film.png', 'label'=>'Video');
	}
	
	function getScrollTextMenuLink()
	{
		return array('href'=>'task=wallboard&act=scroll-texts', 'img'=>'font_go.png', 'label'=>'Scroll Text');
	}
	
	function actionVideos2()
	{
		include('model/MWallboard.php');
		include('lib/Pagination.php');
		$wb_model = new MWallboard();
		$pagination = new Pagination();
		
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
		$pagination->num_records = $wb_model->numVideos();
		$data['videos'] = $pagination->num_records > 0 ? 
			$wb_model->getVideos($pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['videos']) ? count($data['videos']) : 0;
		$data['pagination'] = $pagination;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Wallboard Videos';
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'wallboard_';
		$data['topMenuItems'] = array($this->getScrollTextMenuLink(), $this->getNoticeMenuLink(),
			array('href'=>'task=wallboard&act=add-video', 'img'=>'add.png', 'label'=>'Add Video'));
		$this->getTemplate()->display('wallboard_videos', $data);
	}
	
	function actionVideos()
	{
	    $data['pageTitle'] = 'Wallboard Notices';
	   $data['topMenuItems'] = array($this->getScrollTextMenuLink(), $this->getNoticeMenuLink(),
			array('href'=>'task=wallboard&act=add-video', 'img'=>'add.png', 'label'=>'Add Video'));
	    $data['side_menu_index'] = 'settings';
	    $this->getTemplate()->display('wallboard_videos', $data);
	}
	
	function actionAddVideo()
	{
		$this->saveVideo();
	}
	
	function actionUpdateVideo()
	{
		$vid = isset($_REQUEST['vid']) ? trim($_REQUEST['vid']) : '';
		$this->saveVideo($vid);
	}

	function actionPlayVideo()
	{
		include('model/MWallboard.php');
		$wb_model = new MWallboard();
		
		$vid = isset($_REQUEST['vid']) ? trim($_REQUEST['vid']) : '';
		//$this->saveVideo($vid);
		//$data['pageTitle'] = 'T';
		$video = $this->getInitialVideo($vid, $wb_model);
		if (!empty($video)) {
			$data['source'] = $video->source;
			$this->getTemplate()->display_only('wallboard_play_local_video', $data);
		}
	}
	
	function saveVideo($vid='')
	{
		include('lib/FileManager.php');
		include('model/MWallboard.php');
		$wb_model = new MWallboard();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$yn_options = array('Y'=>'Yes', 'N'=>'No');
		$st_options = array('YouTube'=>'YouTube', 'Local'=>'Local');
		
		if ($request->isPost()) {
			$video = $this->getSubmittedVideo($vid);
			
			$errMsg = $this->getVideoValidationMsg($video);
			
			$_file_name = 'video';
			
			if (empty($errMsg) && $video->source_type == 'Local') {
				if (!empty($_FILES[$_file_name]) && $_FILES[$_file_name]["error"] <= 0) {
					$extention = FileManager::findexts(basename( $_FILES[$_file_name]['name']));
					if (!in_array($extention, $this->supported_video_types)) $errMsg = $extention . ' is not supported video type';
				}
			}
			
			if (empty($errMsg)) {
				$is_success = false;
				
				if (empty($vid)) {
					$video_id = $wb_model->addVideo($video);
					if (!empty($video_id)) {
						$is_success = true;
					} else {
						$errMsg = 'Failed to add video !!';
					}
				} else {
					$value_options = array(
						'st_options' => $st_options, 
						'active' => $yn_options
					);
					$video_id = $vid;
					$oldvideo = $this->getInitialVideo($vid, $wb_model);
					if ($wb_model->updateVideo($oldvideo, $video, $value_options)) {
						$is_success = true;
					} else {
						$errMsg = 'No change found !!';
					}
					
					if ($oldvideo->source_type != $video->source_type && $video->source_type == 'YouTube') {
						$wbfiles = glob('wallboard/video/' . 'a' . $video_id . '.{' . implode(",", $this->supported_video_types) . '}', GLOB_BRACE);
						if (!empty($wbfiles)) {
							foreach ($wbfiles as $wbfile) {
								unlink($wbfile);
							}
						}
					}
				}
				
				
				
				if (!empty($video_id) && $video->source_type == 'Local') {
					if (!empty($_FILES[$_file_name]) && $_FILES[$_file_name]["error"] <= 0) {
						//$target_path = $this->getTemplate()->file_upload_path . 'Wallboard/Notice/' . $notice_id . '.' . $extention;
						$target_path = 'wallboard/video/a' . $video_id . '.' . $extention;
						if (move_uploaded_file($_FILES[$_file_name]['tmp_name'], $target_path)) {
							$wb_model->updateVideoReference($video_id, 'a'.$video_id.'.'.$extention);
							$is_success = true;
							
							$wbfiles = glob('wallboard/video/' . 'a' . $video_id . '.{' . implode(",", $this->supported_video_types) . '}', GLOB_BRACE);
							if (!empty($wbfiles)) {
								foreach ($wbfiles as $wbfile) {
									if ($wbfile != $target_path) unlink($wbfile);
								}
							}
						}
					}
				}
				
				if ($is_success) {
					if (empty($vid)) {
						$errMsg = 'Video added successfully !!';
					} else {
						$errMsg = 'Video updated successfully !!';
					}
					$errType = 0;
					$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=videos");
					$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
				}
			}
			
		} else {
			$video = $this->getInitialVideo($vid, $wb_model);
			if (empty($video)) {
				exit;
			}
		}
		
		$data['video'] = $video;
		$data['yn_options'] = $yn_options;
		$data['st_options'] = $st_options;
		$data['vid'] = $vid;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = empty($vid) ? 'Add Wallboard Video' : 'Update Wallboard Video';
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'wallboard_';
		$data['video_types'] = $this->supported_video_types;
		$this->getTemplate()->display('wallboard_video_form', $data);

	}
	
	function getInitialVideo($vid, $wb_model)
	{
		$video = new stdClass();

		if (empty($vid)) {
			$video->title = '';
			$video->source_type = 'Local';
			$video->source = '';
			$video->active = 'Y';
		} else {
			$video = $wb_model->getVideoById($vid);
			if ($video->source_type == 'YouTube') $video->source = 'http://www.youtube.com/watch?v=' . $video->source;
		}
		
		return $video;
	}
	
	function getSubmittedVideo($vid)
	{
		$posts = $this->getRequest()->getPost();
		$video = new stdClass();
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$video->$key = trim($val);
			}
		}

		if (!empty($vid)) $video->id = $vid;

		return $video;
	}

	function getVideoValidationMsg($video)
	{
		if (empty($video->title)) return "Provide title";
		
		if ($video->source_type == 'Local') {
			$_file_name = 'video';
			if (empty($_FILES[$_file_name]) || $_FILES[$_file_name]["error"] > 0) return "Upload local video file";
		} else {
			if (empty($video->source)) return "Provide video source URL";
		}
		
		return '';
	}
	
	function actionDelVideo()
	{
		include('model/MWallboard.php');
		$wb_model = new MWallboard();

		$vid = isset($_REQUEST['vid']) ? trim($_REQUEST['vid']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';

		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=videos&page=".$cur_page);
		
		if ($wb_model->deleteVideo($vid)) {
			
			$wbfiles = glob('wallboard/video/' . 'a' . $vid . '.{' . implode(",", $this->supported_video_types) . '}', GLOB_BRACE);
			if (!empty($wbfiles)) {
				foreach ($wbfiles as $wbfile) {
					unlink($wbfile);
				}
			}
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Video', 'isError'=>false, 'msg'=>'Video Deleted Successfully', 'redirectUri'=>$url));
		} else {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Video', 'isError'=>true, 'msg'=>'Failed to Delete Video', 'redirectUri'=>$url));
		}
	}
	
	function actionNotices_old()
	{
		include('model/MWallboard.php');
		include('lib/Pagination.php');
		$wb_model = new MWallboard();
		$pagination = new Pagination();
		
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
		$pagination->num_records = $wb_model->numNotices();
		$data['notices'] = $pagination->num_records > 0 ? 
			$wb_model->getNotices($pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['notices']) ? count($data['notices']) : 0;
		$data['pagination'] = $pagination;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Wallboard Notices';
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'wallboard_';
		$data['topMenuItems'] = array($this->getScrollTextMenuLink(), $this->getVideoMenuLink(),
			array('href'=>'task=wallboard&act=add-notice', 'img'=>'add.png', 'label'=>'Add Notice'));
		$this->getTemplate()->display('wallboard_notices', $data);
	}
	
	function actionNotices()
	{
	    $data['pageTitle'] = 'Wallboard Notices';
	    $data['topMenuItems'] = array($this->getScrollTextMenuLink(), $this->getVideoMenuLink(),
			array('href'=>'task=wallboard&act=add-notice', 'img'=>'add.png', 'label'=>'Add Notice'));
	    $data['side_menu_index'] = 'settings';
	    $this->getTemplate()->display('wallboard_notices', $data);
	}
	
	function actionAddNotice()
	{
		$this->saveNotice();
	}
	
	function actionUpdateNotice()
	{
		$nid = isset($_REQUEST['nid']) ? trim($_REQUEST['nid']) : '';
		$this->saveNotice($nid);
	}
	
	function saveNotice($nid='')
	{
		include('lib/FileManager.php');
		include('model/MWallboard.php');
		$wb_model = new MWallboard();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$yn_options = array('Y'=>'Yes', 'N'=>'No');
		
		if ($request->isPost()) {
			$notice = $this->getSubmittedNotice($nid);
			
			$errMsg = $this->getNoticeValidationMsg($notice);
			
			$_file_name = 'noticeImage';
			
			if (empty($errMsg)) {
			
				if (!empty($_FILES[$_file_name]) && $_FILES[$_file_name]["error"] <= 0) {
					$extention = FileManager::findexts(basename( $_FILES[$_file_name]['name']));
					if ($extention != 'png') $errMsg = 'Only png file is allowed to upload';
				}
			}
			
			if (empty($errMsg)) {
				$is_success = false;
				$notice_id = '';
				
				if (empty($nid)) {
					$notice_id = $wb_model->addNotice($notice);
					if (!empty($notice_id)) {
						$is_success = true;
					} else {
						$errMsg = 'Failed to add notice !!';
					}
				} else {
					$notice_id = $nid;
					$value_options = array(
						'active' => $yn_options
					);
					
					$oldnotice = $this->getInitialNotice($nid, $wb_model);
					if ($wb_model->updateNotice($oldnotice, $notice, $value_options)) {
						$is_success = true;
					} else {
						$errMsg = 'No change found !!';
					}
				}
				
				if (!empty($notice_id)) {
					if (!empty($_FILES[$_file_name]) && $_FILES[$_file_name]["error"] <= 0) {
						//$target_path = $this->getTemplate()->file_upload_path . 'Wallboard/Notice/' . $notice_id . '.' . $extention;
						$target_path = 'wallboard/notice/' . $notice_id . '.' . $extention;
						if (move_uploaded_file($_FILES[$_file_name]['tmp_name'], $target_path)) {
							$is_success = true;
							$notice->img = $target_path . '?t=' . time();
						}
					}
				}
				
				if ($is_success) {
					if (empty($nid)) {
						$errMsg = 'Notice added successfully !!';
					} else {
						$errMsg = 'Notice updated successfully !!';
					}
					$errType = 0;
					$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=notices");
					$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
				}
			}
			
		} else {
			$notice = $this->getInitialNotice($nid, $wb_model);
			if (empty($notice)) {
				exit;
			}
		}
		
		$data['notice'] = $notice;
		$data['yn_options'] = $yn_options;
		$data['nid'] = $nid;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = empty($nid) ? 'Add Wallboard Notice' : 'Update Wallboard Notice';
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'wallboard_';
		$this->getTemplate()->display('wallboard_notice_form', $data);

	}
	
	function getInitialNotice($nid, $wb_model)
	{
		$notice = new stdClass();

		if (empty($nid)) {
			$notice->title = '';
			$notice->notice = '';
			$notice->active = 'Y';
			$notice->img = '#';
		} else {
			$notice = $wb_model->getNoticeById($nid);
			if (empty($notice)) exit;
			//$file_name = $this->getTemplate()->file_upload_path . 'Wallboard/Notice/' . $notice->id . '.png';
			$file_name = 'wallboard/notice/' . $notice->id . '.png';
			$notice->img = file_exists($file_name) ? $file_name . '?t=' . time() : '#';
		}
		
		return $notice;
	}
	
	function getSubmittedNotice($nid)
	{
		$posts = $this->getRequest()->getPost();
		$notice = new stdClass();
		if (is_array($posts)) {
			$notice->title = trim($posts['title']);
			$notice->notice = trim($posts['notice']);
			$notice->active = trim($posts['active']);
			$notice->img = '';
		}

		if (!empty($nid)) $notice->id = $nid;

		return $notice;
	}

	function getNoticeValidationMsg($notice)
	{
		if (empty($notice->title)) return "Provide title";
		if (empty($notice->notice)) return "Provide notice text";
		if (strlen($notice->notice) > 200) {
			return "Maximum notice length allowed is 200";
		}
		
		return '';
	}
	
	function actionDelNotice()
	{
		include('model/MWallboard.php');
		$wb_model = new MWallboard();

		$nid = isset($_REQUEST['nid']) ? trim($_REQUEST['nid']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';

		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=notices&page=".$cur_page);
		
		if ($wb_model->deleteNotice($nid)) {
			//$file_name = $this->getTemplate()->file_upload_path . 'Wallboard/Notice/' . $nid . '.png';
			$file_name = 'wallboard/notice/' . $nid . '.png';
			if (file_exists($file_name)) {
				unlink($file_name);
			}
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Notice', 'isError'=>false, 'msg'=>'Notice Deleted Successfully', 'redirectUri'=>$url));
		} else {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Notice', 'isError'=>true, 'msg'=>'Failed to Delete Notice', 'redirectUri'=>$url));
		}
	}
	
	function actionDelNoticeImg()
	{
		$nid = isset($_REQUEST['nid']) ? trim($_REQUEST['nid']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';
		
		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=notices&page=$cur_page");
		
		//$file_name = $this->getTemplate()->file_upload_path . 'Wallboard/Notice/' . $nid . '.png';
		$file_name = 'wallboard/notice/' . $nid . '.png';
		
		$is_update = false;
		
		if (file_exists($file_name)) {
			if (unlink($file_name)) $is_update = true;
		}
		
		if ($is_update) {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Notice Image', 'isError'=>false, 'msg'=>'Notice image removed successfully', 'redirectUri'=>$url));
		} else {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Notice Image', 'isError'=>true, 'msg'=>'Failed to remove notice image', 'redirectUri'=>$url));
		}
	}
	
	function actionScrollTexts()
	{
		include('model/MWallboard.php');
		include('lib/Pagination.php');
		$wb_model = new MWallboard();
		$pagination = new Pagination();
		
		$pagination->base_link = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName());
		$pagination->num_records = $wb_model->numScrollTexts();
		$data['scroll_texts'] = $pagination->num_records > 0 ? 
			$wb_model->getScrollTexts($pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($data['scroll_texts']) ? count($data['scroll_texts']) : 0;
		$data['pagination'] = $pagination;
		$data['request'] = $this->getRequest();
		$data['pageTitle'] = 'Wallboard Scroll Texts';
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'wallboard_';
		$data['topMenuItems'] = array($this->getNoticeMenuLink(), $this->getVideoMenuLink(), 
			array('href'=>'task=wallboard&act=add-scroll-text', 'img'=>'add.png', 'label'=>'Add Scroll Text'));
		$this->getTemplate()->display('wallboard_scroll_texts', $data);
	}
	
	function actionDelScrollText()
	{
		include('model/MWallboard.php');
		$wb_model = new MWallboard();

		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$cur_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1';

		$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=scroll-texts&page=".$cur_page);
		
		if ($wb_model->deleteScrollText($tid)) {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Scroll Text', 'isError'=>false, 'msg'=>'Scroll Text Deleted Successfully', 'redirectUri'=>$url));
		} else {
			$this->getTemplate()->display('msg', array('pageTitle'=>'Delete Scroll Text', 'isError'=>true, 'msg'=>'Failed to Delete Scroll Text', 'redirectUri'=>$url));
		}
	}
	
	function actionAddScrollText()
	{
		$this->saveScrollText();
	}
	
	function actionUpdateScrollText()
	{
		$tid = isset($_REQUEST['tid']) ? trim($_REQUEST['tid']) : '';
		$this->saveScrollText($tid);
	}
	
	function saveScrollText($tid='')
	{
		include('model/MWallboard.php');
		$wb_model = new MWallboard();

		$request = $this->getRequest();
		$errMsg = '';
		$errType = 1;
		$yn_options = array('Y'=>'Yes', 'N'=>'No');
		
		if ($request->isPost()) {
			$scroll_text = $this->getSubmittedScrollText($tid);
			
			$errMsg = $this->getScrollTextValidationMsg($scroll_text);
			
			if (empty($errMsg)) {
				$is_success = false;

				if (empty($tid)) {
					if ($wb_model->addScrollText($scroll_text)) {
						$errMsg = 'Scroll text added successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'Failed to add scroll text !!';
					}
				} else {
					$value_options = array(
						'active' => $yn_options
					);
					
					$oldst = $this->getInitialScrollText($tid, $wb_model);
					if ($wb_model->updateScrollText($oldst, $scroll_text, $value_options)) {
						$errMsg = 'Scroll text updated successfully !!';
						$is_success = true;
					} else {
						$errMsg = 'No change found !!';
					}
				}
				
				if ($is_success) {
					$errType = 0;
					$url = $this->getTemplate()->url("task=" . $this->getRequest()->getControllerName() . "&act=scroll-texts");
					$data['metaText'] = "<META HTTP-EQUIV=\"refresh\" CONTENT=\"2;URL=$url\">";
				}
			}
			
		} else {
			$scroll_text = $this->getInitialScrollText($tid, $wb_model);
			if (empty($scroll_text)) {
				exit;
			}
		}
		
		$data['scroll_text'] = $scroll_text;
		$data['yn_options'] = $yn_options;
		$data['tid'] = $tid;
		$data['request'] = $request;
		$data['errMsg'] = $errMsg;
		$data['errType'] = $errType;
		$data['pageTitle'] = empty($tid) ? 'Add Scroll Text' : 'Update Scroll Text';
		$data['side_menu_index'] = 'settings';
		$data['smi_selection'] = 'wallboard_';
		$this->getTemplate()->display('wallboard_scroll_text_form', $data);

	}
	
	function getInitialScrollText($tid, $wb_model)
	{
		$scroll_text = new stdClass();

		if (empty($tid)) {
			$scroll_text->txt = '';
			$scroll_text->active = 'Y';
		} else {
			$scroll_text = $wb_model->getScrollTextById($tid);
		}
		return $scroll_text;
	}
	
	function getSubmittedScrollText($tid)
	{
		$posts = $this->getRequest()->getPost();
		$scroll_text = new stdClass();
		if (is_array($posts)) {
			foreach ($posts as $key=>$val) {
				$scroll_text->$key = trim($val);
			}
		}

		if (!empty($tid)) $scroll_text->id = $tid;

		return $scroll_text;
	}

	function getScrollTextValidationMsg($scroll_text)
	{
		if (empty($scroll_text->txt)) return "Provide text";
		if (strlen($scroll_text->txt) > 100) {
			return "Maximum text length allowed is 100";
		}
		
		return '';
	}
	

}
