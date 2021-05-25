<?php
class Helper
{
    protected static $page_idx_list=[];
    protected static $url;
    protected static $current_page;
    protected static $myjob_count_list;
    protected static $isLoginAgentEmailSkill;
    protected static $isLoginAgentChatSkill;

	public static function getMenuContent($menu_info, $page_idx_list){
        self::$page_idx_list = $page_idx_list;
		return self::getMenuGenerateForAdmin($menu_info);
	}

	protected static function getMenuGenerateForAdmin($data){
        $mainData = "";
        if(!empty($data)){
            foreach($data as $key => $d){
                $page_item = (array)self::$page_idx_list[$d['id']];

                if(!empty($d['children'])){
                    $mainData .= '<li class="dd-item" data-id="'.$page_item['id'].'" data-type="'.$d['type'].'" data-name="'.$page_item['name'].'" data-path="'.$page_item['path'].'" data-icon="'.$page_item['icon'].'" data-active-class="'.$page_item['active_class'].'" data-layout="'.$page_item['layout'].'" data-pop-out="'.$page_item['pop_out'].'" > <span data-idx="'.$key.'" class="pull-left custom-menu-remove btn btn-xs red remove-custom-menu"><i class="fa fa-remove"></i></span> <div class="dd-handle"> '.$page_item['name'].' </div> <ol class="dd-list">' . self::getMenuGenerateForAdmin($d['children']) . '</ol></li>';
                }else{
                    $mainData .= '<li class="dd-item" data-id="'.$page_item['id'].'" data-type="'.$d['type'].'" data-name="'.$page_item['name'].'" data-path="'.$page_item['path'].'" data-icon="'.$page_item['icon'].'" data-active-class="'.$page_item['active_class'].'" data-layout="'.$page_item['layout'].'" data-pop-out="'.$page_item['pop_out'].'" > <span data-idx="'.$key.'" class="pull-left custom-menu-remove btn btn-xs red remove-custom-menu"><i class="fa fa-remove"></i></span> <div class="dd-handle"> '.$page_item['name'].' </div> </li>'; 
                }
            }
        }
        return $mainData; 
    }

    public static function LeftMenu($menu_info, $url, $myjob_count_list, $isLoginAgentEmailSkill, $isLoginAgentChatSkill){
        self::$url = $url;
        self::$myjob_count_list = $myjob_count_list;
        self::$isLoginAgentEmailSkill = $isLoginAgentEmailSkill;
        self::$isLoginAgentChatSkill = $isLoginAgentChatSkill;

        return self::getMenuGenerateForLeft($menu_info, 0);
    }
    protected static function getMenuGenerateForLeft($data, $parent){
        $mainData = "";
        $current_user_role = UserAuth::getRoleID();

        if(!empty($data)){
            foreach($data as $key => $d){
				$site_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ?  "https" : "http");
				$site_url .= "://".$_SERVER['HTTP_HOST'];
                $url = (!empty($d->path) && $d->path != '#') ? self::$url.'?'.$d->path : '#';
				if($url!='#')
					$url = $site_url.$url;
				else{
					$ctlr=Controller::getInstance();
					$url = $site_url.$_SERVER['REQUEST_URI'].$url;
				}
				
                $li_class = ($parent == 0) ? 'gs-menu-title' : '';
                $a_class = ($parent == 0) ? 'gs-menu-title-a' : '';

                //current page 
                $task = isset($_GET['task']) ? trim($_GET['task']) : '';
                $action = isset($_GET['act']) ? trim($_GET['act']) : '';
                // $type = isset($_GET['type']) ? trim($_GET['type']) : '';
                $current_page1 = $task.(!empty($action) ? '_'.$action : '');
                // $current_page2 = $task.(!empty($type) ? '_'.$type : '');

                if($current_page1 == $d->activeClass){
                    // $li_class .= " active";
                    $a_class .= " active";
                }

                //Extra text add in My Ticket menu
                $extraTxt = "";
                if($d->activeClass == 'email_myjob'){
                    $newJob = !empty(self::$myjob_count_list->num_news) ? self::$myjob_count_list->num_news : 0;
                    $pendingJob = !empty(self::$myjob_count_list->num_pendings) ? self::$myjob_count_list->num_pendings : 0;
                    $numclientpendingsJob = !empty(self::$myjob_count_list->num_client_pendings) ? self::$myjob_count_list->num_client_pendings : 0;

                    $extraTxt = "<div class='pull-right' style='padding-right:10px;'><span class='badge badge-info' title='New Email'>".$newJob."</span> <span class='badge badge-danger' title='Pending Email'>".$pendingJob." </span> <span class='badge badge-warning' title='Client Pending Email'>".$numclientpendingsJob." </span></div>";
                }

                if(!empty($d->children)){                    
                    if(UserAuth::getRoleID() == 'A'){
                        if(!self::$isLoginAgentEmailSkill && (strpos($d->path, 'email') > -1 || strpos(strtolower($d->name), 'email') > -1)){
                            continue;
                        }
                        if(!self::$isLoginAgentChatSkill && (strpos($d->path, 'chat') > -1 || strpos(strtolower($d->name), 'chat') > -1)){
                            continue;
                        }
                    }

                    $class_submenu = str_replace([' ','_'], '-', strtolower($d->name)); 
                    $mainData .= '<li class="'.$li_class.'">';

                    if($d->popOut==MSG_YES)
                        $mainData .= '<a href="javascript:void(0);" onclick="openDashboardWindow(\''.$url.'\')" class="'.$a_class.'">';
                    else
                        $mainData .= '<a href="'.$url.'" class="'.$a_class.'">';

                    $mainData .= '<i class="'.$d->icon.'"></i>';
                    $mainData .= '<span class="title">'.$d->name.'</span>';
                    $mainData .= '<span class="arrow"></span>';
                    $mainData .= '</a> ';


                    $mainData .= '<ul class="sub-menu '.$class_submenu.'" >';
                    $mainData .= self::getMenuGenerateForLeft($d->children, 1);
                    $mainData .= '</ul>';

                    $mainData .= '</li>';
                }else{                    
                    if(UserAuth::getRoleID() == 'A'){
                        if(!self::$isLoginAgentEmailSkill && (strpos($d->path, 'email') > -1 || strpos(strtolower($d->name), 'email') > -1)){
                            continue;
                        }

                        if(!self::$isLoginAgentChatSkill && (strpos($d->path, 'chat') > -1 || strpos(strtolower($d->name), 'chat') > -1)){
                            continue;
                        }
                    }

                    $mainData .= '<li class="'.$li_class.'">';

                    if($d->popOut==MSG_YES)
                        $mainData .= '<a href="javascript:void(0);" onclick="openDashboardWindow(\''.$url.'\')" class="'.$a_class.'">';
                    else
                        $mainData .= '<a href="'.$url.'" class="'.$a_class.'">';

                    $mainData .= '<i class="'.$d->icon.'"></i>';
                    $mainData .= '<span class="title">'.$d->name.'</span>'.$extraTxt;
                    $mainData .= '</a> ';
                    $mainData .= '</li>';
                }
            }
        }
        return $mainData; 
    }

    public static function TopMenu($menu_info, $url, $myjob_count_list){
        self::$url = $url;
        self::$myjob_count_list = $myjob_count_list;

        return self::getMenuGenerateForTop($menu_info, 0);
    }

    protected static function getMenuGenerateForTop($data, $parent){
        $mainData = "";
        $current_user_role = UserAuth::getRoleID();

        if(!empty($data)){
            foreach($data as $key => $d){
                $url = (!empty($d->path) && $d->path != '#') ? self::$url.'?'.$d->path : '#';
                $li_class = ($parent == 0) ? 'gs-menu-title' : '';
                $a_class = ($parent == 0) ? 'gs-menu-title-a' : '';

                //current page 
                $task = isset($_GET['task']) ? trim($_GET['task']) : '';
                $action = isset($_GET['act']) ? trim($_GET['act']) : '';
                $current_page1 = $task.(!empty($action) ? '_'.$action : '');

                if($current_page1 == $d->activeClass){
                    $a_class .= " active";
                }

                //Extra text add in My Ticket menu
                $extraTxt = "";
                if($d->activeClass == 'email_myjob'){
                    $newJob = !empty(self::$myjob_count_list->num_news) ? self::$myjob_count_list->num_news : 0;
                    $pendingJob = !empty(self::$myjob_count_list->num_pendings) ? self::$myjob_count_list->num_pendings : 0;
                    $numclientpendingsJob = !empty(self::$myjob_count_list->num_client_pendings) ? self::$myjob_count_list->num_client_pendings : 0;

                    $extraTxt = "<div class='pull-right' style='padding-right:10px;'><span class='badge badge-info' title='New Email'>".$newJob."</span> <span class='badge badge-danger' title='Pending Email'>".$pendingJob." </span> <span class='badge badge-warning' title='Client Pending Email'>".$numclientpendingsJob." </span></div>";
                }

                if(!empty($d->children)){
                    $mainData .= '<li class="has-sub '.$li_class.' '.$a_class.'">';

                    if($d->popOut==MSG_YES)
                        $mainData .= '<a href="javascript:void(0);" onclick="openDashboardWindow(\''.$url.'\')" class="'.$a_class.'">';
                    else
                        $mainData .= '<a href="'.$url.'">';

                    $mainData .= '<span>'.$d->name.'</span>';
                    $mainData .= '</a> ';
                    $mainData .= '<ul>';
                    $mainData .= self::getMenuGenerateForTop($d->children, 1);
                    $mainData .= '</ul>';
                    $mainData .= '</li>';
                }else{
                    $mainData .= '<li class="'.$li_class.' '.$a_class.'">';
                    if($d->popOut==MSG_YES)
                        $mainData .= '<a href="javascript:void(0);" onclick="openDashboardWindow(\''.$url.'\')" class="'.$a_class.'">';
                    else
                        $mainData .= '<a href="'.$url.'">';

                    $mainData .= '<span>'.$d->name.'</span>'.$extraTxt;
                    $mainData .= '</a> ';
                    $mainData .= '</li>';
                }
            }
        }
        return $mainData; 
    }
}

?>