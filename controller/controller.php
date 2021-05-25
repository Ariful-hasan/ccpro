<?php

class Controller
{
    private  $data = [];

	public $_request;

	public $request;

	public $_template;
	
	private static $instance=null;

    public $last_update_difference_time = 2;

	function __construct()
	{
		self::$instance =& $this;
		$this->request = new Request();
	}

	public static function &getInstance()
    {
		if(!self::$instance){
			self::$instance=new self();
		}
		return self::$instance;
	}

	public function getRequest()
	{
		return $this->request;
	}
	
	public function setTemplate(&$template)
	{
		$this->_template = $template;
	}
	
	public function getTemplate()
	{
		return $this->_template;
	}

    public function url($path)
	{
		return $this->getTemplate()->url($path);
	}
	public function init()
    {
        $this->getTemplate()->display('msg', array('pageTitle'=>'Page not implemented !!', 'isError'=>true, 'msg'=>'Page not implemented !!'));
	}

	protected function setTopMenu($url = '', $icon = 'fa fa-bar', $label ='New Munu', $title ='')
    {
        $this->data['topMenuItems'][] = [
                'href' => $url,
                'img' => $icon,
                'label' => $label,
                'title' => $title,
        ];
    }

    protected function display($view)
    {
        $this->getTemplate()->display($view, $this->data);
    }

    protected function displayPopup($view,$isFullLoad=true,$isAutoResize=true)
    {
        $this->getTemplate()->display_popup($view, $this->data, $isFullLoad, $isAutoResize);
    }

    protected function displayReport($view)
    {
        $this->getTemplate()->display_report($view, $this->data);
    }

    protected function displayPopupMsg($isFullLoad = true)
    {
        $this->getTemplate()->display_popup_msg($this->data, $isFullLoad);
    }

    protected function setViewData($key, $value='')
    {
        $this->data[$key] = $value;
    }

    protected function setPageTitle($title='Page Title')
    {
        $this->data['pageTitle'] = $title;
    }

    protected function setGridDataUrl($url)
    {
        $this->data['dataUrl'] = $url;
    }

    protected function setSelectedSideMenuIndex($smi_selection='')
    {
        $this->data['smi_selection'] = $smi_selection;
    }

    protected function setSideMenuIndex($side_menu_index = '')
    {
        $this->data['side_menu_index'] = $side_menu_index;
    }

}

class Request
{
	public $_posts;
	public $_gets;
	public $_request;
	public $_is_post;
	public $_controller_name;
	public $_action_name;
	
	
	public function __construct()
	{
		$this->_is_post = false;
		$this->_gets = array();
		$this->_posts = array();
		
		if (isset($_POST) && !empty($_POST)) {
			$this->_is_post = true;
			foreach ($_POST as $key => $val) {
				$this->_posts[$key] = $val;
			}
		}
		
		if (isset($_GET) && !empty($_GET)) {			
			foreach ($_GET as $key => $val) {
				$this->_gets[$key] = $val;
			}
		}
		if (isset($_REQUEST) && !empty($_REQUEST)) {			
			foreach ($_REQUEST as $key => $val) {
				$this->_request[$key] = $val;
			}
		}
	}

	public static function IsAjax()
    {
		return (isset ( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) && $_SERVER ['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest");
	} 
	
	public function setActionName($name)
	{
		$this->_action_name = $name;
	}

	public function setControllerName($name)
	{
		$this->_controller_name = $name;
	}
	
	public function getPost($key='')
	{
		if (empty($key)) return $this->_posts;
		return isset($this->_posts[$key]) ?  $this->_posts[$key] : null;
	}

	public function getRequest($key='',$defaultValue='')
	{
		if (empty($key)) return $this->_request;
		return isset($this->_request[$key]) ? $this->_request[$key] : $defaultValue;
	}

	public function getGet($key='')
	{
		if (empty($key)) return $this->_gets;
		return isset($this->_gets[$key]) ? $this->_gets[$key] : null;
	}
	
	public function isPost()
	{
		return $this->_is_post ? true : false;
	}
	
	public function getControllerName()
	{
		return $this->_controller_name;
	}

	public function getActionName()
	{
		return $this->_action_name;
	}

}


class AjaxConfirmResponse
{
	public $status=false;
	public $msg="unknown";
	public $data=null;
}

class GridRequest
{
	public $orderBy = "";
	public $order = "ASC";
	public $rows = 20;
	public $pageNo = 1;
	public $limit = 20;
	public $limitStart = 0;
	public $srcItem = "";
	public $srcText = "";
	public $srcOption = "";
	public $searchOper = "eq";
	public $toDate = "";
	public $isMultisearch = false;
	public $multiparam = "";
	public $isDownloadCSV = false;
	public $filename="";

	public function __construct()
    {
		$this->isDownloadCSV=isset($_GET["download_csv"])?true:false;
		$this->filename=$this->getRequest ("filename","downloadcsv");

		if (count ( $_POST ) > 0 || $this->isDownloadCSV) {
			$this->orderBy = $this->getRequest ( 'sidx' );
			$this->order = $this->getRequest ( 'sord' );
			$this->limit=$this->rows = $this->getRequest ( 'rows' );			
			$this->pageNo = $this->getRequest ( 'page',1 );
			$this->limitStart=$this->pageNo<=1?0:((($this->pageNo-1)*$this->limit)+1);
			$this->srcItem = $this->getRequest ( 'searchField' );
			$this->srcText = $this->getRequest ( 'searchString' );
			$this->srcText = trim ( $this->srcText );
			$this->searchOper = $this->getRequest ( 'searchOper' ,"eq");
			$this->toDate = $this->getRequest ( 'toString' );
			$this->srcText = $this->srcText=="*" ? "" : $this->srcText;
		}
		
		if ($this->getRequest ( 'isMultiSearch' ) != "") {
			$this->isMultisearch = $this->getRequest ( 'isMultiSearch' ) == "true";
			if ($this->isMultisearch) {
				$ptext = $this->getRequest ( 'ms' );
				if (! empty ( $ptext )) {
					$multiparam = array ();
					parse_str ( $ptext, $multiparam );
					$this->multiparam = $multiparam ['ms'];
					foreach ($this->multiparam as &$vl){
						if(is_string($vl)){
							$vl=trim($vl);
							$vl = $vl=="*" ? "" : $vl;
						}
					}
                    if (!empty($multiparam['op'])) {
                        foreach ($multiparam ['op'] as &$operator) {
                            if (is_string($operator)) {
                                $operator = trim($operator);
                                $operator = $operator == "*" ? "" : $operator;
                            }
                        }
                        $this->multiparam['op'] = $multiparam ['op'];
                    }
				}
			}
		}
	}

	public function getMultiParam($key = '', $defaultValue = '') {
	    if(empty($key)){
            return $this->multiparam;
        }
	    return isset ( $this->multiparam [$key] ) ?  $this->multiparam [$key] : $defaultValue;
	}

	public function getRequest($key = '', $defaultValue = '')
    {
		if (empty ( $key )){
		    return $_REQUEST;
        }

		return isset($_REQUEST [$key]) ? trim($_REQUEST [$key]) : $defaultValue;
	}
}

