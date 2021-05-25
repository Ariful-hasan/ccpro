<?php
if(!function_exists("base_url")){
	/**
	 * Base URL
	 * @return it return the url of current site
	 * @example: http://www.genuitysystems.com/
	 */
	function base_url(){
		$ctlr=Controller::getInstance(); 
		return $ctlr->url(null);
	}
}
if(!function_exists("site_url")){
	function site_url(){
		$url =  ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ?  "https" : "http");
		return $url .=  "://".$_SERVER['HTTP_HOST'];
	}
}
if(!function_exists("DownloadCSV")){
	function DownloadCSV($cols,&$response,$filename,$delimiter=",",$isRemoveTag=true){
        setcookie('download_csv',1);
		error_reporting(0);
		header('Content-Type: application/csv');
		header('Content-Disposition: attachement; filename="'.$filename.'";');
		$f = fopen('php://output', 'w');
		$maindlarray=array();
		$titles=array();
		if(count($cols)>0){
			foreach ($cols as $key=>$value){
				$value=preg_replace("/&.*?;|<.*?>/", "", $value);
				array_push($titles,$value);
			}
			fputcsv($f, $titles, $delimiter);
			if(count($response->rowdata)>0){
				foreach ($response->rowdata as $cdata){
					$row=array();
					foreach ($cols as $key=>$value){
						$rvalue="";
						if($isRemoveTag){
							if(isset($cdata->$key)){
								$rvalue=strip_tags($cdata->$key);
								if ($key == 'callid') $rvalue = 'ID-' . $rvalue;
							}elseif(is_array($cdata) && isset($cdata[$key])){
								$rvalue=strip_tags($cdata[$key]);
								if ($key == 'callid') $rvalue = 'ID-' . $rvalue;
							}
						}else{
							if(isset($cdata->$key)){
								$rvalue=$cdata->$key;
							}elseif(is_array($cdata) && isset($cdata[$key])){
                                $rvalue=$cdata[$key];
                            }else{
								$rvalue="";
							}
						}
						$rvalue=preg_replace("/&.*?; /", "", $rvalue);
						array_push($row, $rvalue);
					}
					fputcsv($f, $row, $delimiter);
				}
			}
			fclose($f);
		}
	}
}
if(!function_exists("DownloadChartCSV")){
	/**
	 * @param HighChartData $response
	 * @param string $delimiter
	 * @param string $isRemoveTag
	 */
	function DownloadChartCSV(&$response,$delimiter=",",$isRemoveTag=true){		
		error_reporting(0);		
		$filename=$response->title->text."_".date('Y-m-d_H-i-s').".csv";
		header('Content-Type: application/csv');
		header('Content-Disposition: attachement; filename="'.$filename.'";');
		$f = fopen('php://output', 'w');
		$maindlarray=array();
		$titles=array();
		if(count($response->xAxis->categories)>0){
			array_push($titles,"Series Name");
			foreach ( $response->xAxis->categories as $key=>$value){
				$value=preg_replace("/&.*?;/", "", $value);
				array_push($titles,$value);
			}
			fputcsv($f, $titles, $delimiter);
			if(count($response->series)>0){
				foreach ($response->series as $cdata){
					$row=array();
					array_push($row, $cdata->name);
					foreach ($cdata->data as $value){
						$rvalue=$value;
						if(is_string($rvalue)){					
							if(is_string($rvalue) && $isRemoveTag){							
								$rvalue=strip_tags($rvalue);							
							}else{
							
							}
						}elseif(is_array($rvalue) || is_object($rvalue)){							
							$rvalue="";
						}
						
						$rvalue=preg_replace("/&.*?; /", "", $rvalue);
						array_push($row, $rvalue);
					}
					fputcsv($f, $row, $delimiter);
				}
			}
			fclose($f);
		}
	}
}
if(!function_exists("lib_url")){
	function lib_url(){
		$ctlr=Controller::getInstance();
		return $ctlr->url(null)."lib/";
	}
}
if (!function_exists("is_public_ip")) {
        function is_public_ip ($ip) {
                return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE |  FILTER_FLAG_NO_RES_RANGE);
        }
}
if(!function_exists("ShowTableFromArray")){
	function ShowTableFromArray($objectsarray){
		$skiped=array("settedPropertyforLog","db","Authenticator");
		if(is_array($objectsarray)){
			?>
			<style>
				.d-table{border: 1px solid #ccc;	border-collapse: collapse;	}
				.d-table thead{	background: #ccc; }
				.d-table td{border: 1px solid #ccc;	}
				.d-table th{border: 1px solid #AEAAAA;}
				.d-table td,.d-table th{padding:5px;}
			</style>
			<table class="d-table table">	
			<thead>	<tr>
			<?php 			
			foreach ($objectsarray as $objth){
				foreach ($objth as $key=>$value){
						if(in_array($key, $skiped) || is_array($value)||is_object($value))continue;
					?>
					<th><?php echo $key;?></th>
					<?php 
				}
				break;
			}
			?></tr>
			</thead>
			<tbody>
			<?php 
			foreach ($objectsarray as $tr){			
				?>
				<tr>
				<?php foreach ( $tr as $tdkey=>$td){
					if(in_array($tdkey, $skiped) || is_array($td)||is_object($td))continue;					
					if(is_double($td)||is_float($td)){
						$td=sprintf("%.6f",$td);
					}
				?>
				<td><?php echo isset($td)?$td:"&nbsp;";?></td>
				<?php }?>				
				</tr>
				<?php 
			}
			?>
			</tbody>
				</table>
			<?php 
		}elseif(is_object($objectsarray)){
			$thead="";
			$tbody="";	
			foreach ( $objectsarray as $tdkey=>$td){
				if(in_array($tdkey, $skiped) || is_array($td)||is_object($td))continue;					
				if(is_double($td)||is_float($td)){
					$td=sprintf("%.6f",$td);
				}
				$td=!empty($td)?$td:"&nbsp;";
				$thead.="<th>".$tdkey."</th>";
				$tbody.="<td>".$td."</td>";
			 }
			 $thead="<tr>".$thead."</tr>";
			 $tbody="<tr>".$tbody."</tr>";
			 ?>				
			
				<style>
					.d-table{border: 1px solid #ccc;	border-collapse: collapse;	}
					.d-table thead{	background: #ccc; }
					.d-table td{border: 1px solid #ccc;	}
					.d-table th{border: 1px solid #AEAAAA;}
					.d-table td,.d-table th{padding:5px;}
				</style>
				<table class="d-table table">	
				<thead>	
					<?php echo $thead;?>
				</thead>
				<tbody>
					<?php echo $tbody;?>
				</tbody>
					</table>
				<?php 
			}
	}
}
if(!function_exists("GPrint")){
	function GPrint($obj){
		echo"<pre>".print_r($obj,true)."</pre>";
	}
}

if(!function_exists("dd"))
{
    function dd($object)
    {
        "<pre>".var_dump($object)."</pre>"; die;
    }
}

if(!function_exists("GetStatusText")){
	function GetStatusText($key,&$status_array){
		return !empty($status_array[$key])?$status_array[$key]:$key;
		/*if($type=="S"){
			return '<span class="text-success">'.$rtn.'</span>';
		}elseif($type=="I"){
			return '<span class="text-info">'.$rtn.'</span>';
		}elseif($type=="W"){
			return '<span class="text-warning">'.$rtn.'</span>';
		}
		return $rtn;*/
	}
}

if(!function_exists("GetLanguageList")){
	function GetLanguageList(){
		return array(
			'AR' =>	'Arabic',
			'BN' =>	'Bangla',	
			'ZH' =>	'Chinese',	
			'NL' => 'Dutch',	
			'EN' => 'English',
			'FR' => 'French',	
			'DE' => 'German',		
			'HI' => 'Hindi',	
			'GA' => 'Irish',	
			'IT' =>	'Italian',	
			'JA' => 'Japanese',	
			'KO' => 'Korean',	
			'PT' => 'Portuguese',	
			'RU' => 'Russian',	
			'ES' => 'Spanish'	
		);
	}
}
if(!function_exists("_e")){
	function _e($msg)
	{
		echo $msg;
	}
}
if(!function_exists("AddError")){
	function AddError($msg){
		return TemplateManager::AddError($msg);
	}
}
if(!function_exists("AddInfo")){
	function AddInfo($msg){
		return TemplateManager::AddInfo($msg);
	}
}
if(!function_exists("GetError")){
	function GetError($prefix='',$postfix=''){
		return TemplateManager::GetError($prefix,$postfix);
	}
}
if(!function_exists("GetError")){
	function GetInfo($prefix='',$postfix=''){
		return TemplateManager::GetInfo($prefix,$postfix);
	}
}
if(!function_exists("GetMsg")){
	function GetMsg($prefix1='<div class="alert alert-success alert-dismissible fade in" role="alert"><i class="fa fa-check"> </i> ',$prefix2='<div class="alert alert-error" role="alert" ><i class="fa fa-times"> </i> ',$postfix='</div>'){		
		return TemplateManager::GetMsg($prefix1,$prefix2,$postfix);
	}
}
if(!function_exists("HasUIMsg")){
	function HasUIMsg(){
		return TemplateManager::HasUIMsg();
	}
}


if(!function_exists("AddHiddenFields")){
	function AddHiddenFields($key, $value){
		return TemplateManager::AddHiddenFields($key, $value);
	}
}
if(!function_exists("AddOldFields")){
	function AddOldFields($key, $value){
		return TemplateManager::AddOldFields($key, $value);
	}
}
if(!function_exists("GetHiddenFields")){
	function GetHiddenFields(){
		echo  TemplateManager::GetHiddenFields();
	}
}
if(!function_exists("AddModel")){
	function AddModel($modelname){
		$modelname=rtrim($modelname,".php");
		$modelname.=".php";
		if(file_exists(dirname(__FILE__)."/../model/".$modelname)){
			include_once dirname(__FILE__)."/../model/".$modelname;
		}elseif(file_exists(dirname(__FILE__)."/../model/".strtolower($modelname))){
			include_once dirname(__FILE__)."/../model/".strtolower($modelname);
		}else{
			AddError("Failed to add model:$modelname");
		}
	}
}
if(!function_exists("PostValue")){
	function PostValue($name,$Default=""){
		if(!empty($_POST[$name])){
			return $_POST[$name];
		}
		return $Default;
	}
}

if(!function_exists("GetHiddenFields")){
	function GetHiddenFields(){
		echo  TemplateManager::GetHiddenFields();
	}
}

if(!function_exists("GetHTMLOption")){
	function GetHTMLOption($value,$text,$selected="",$isDisabled=false){
		?>
	<option  <?php echo $isDisabled?' disabled ':""; echo $selected==$value?"selected='selected'":"";?>
	value="<?php echo $value;?>"><?php echo $text;?></option>
<?php 
		
	}
}

if(!function_exists("GetHTMLOptionByArray")){
	function GetHTMLOptionByArray(array $options,$selected=''){
		foreach ($options as $key => $value)
        {
            GetHTMLOption($key,$value,$selected);
        }
	}
}


if(!function_exists("maskCreditCard")){
	function maskCreditCard($cardNumber, $mask_with="*", $unmask_left_length = 4, $unmask_right_length=4){
	    $should_mask = substr($cardNumber,$unmask_left_length,- $unmask_right_length);

        return str_replace(substr($cardNumber, $unmask_left_length, -$unmask_right_length),str_repeat($mask_with, strlen($should_mask)), $cardNumber);
	}
}
if(!function_exists("getFileNameFromTxtFile")){
	function getFileNameFromTxtFile($txtFileName, $txtFileDir){
	    return TemplateManager::getFileNameFromTxtFile($txtFileName, $txtFileDir);
	}
}
if(!function_exists("isFileExists")){
	function isFileExists($fileName, $fileDir){
	    return TemplateManager::isFileExists($fileName, $fileDir);
	}
}

if(!function_exists("isFileExists")){
	function isFileExists($fileName, $fileDir){
	    return TemplateManager::isFileExists($fileName, $fileDir);
	}
}

if(!function_exists("fractionFormat")){
	function fractionFormat($data, $format, $isDownload){
	    if($isDownload){
	    	return $data;
	    }else{
	    	return sprintf($format, $data);
	    }
	}
}

if(!function_exists("numberFormat")){
	function numberFormat($data, $decimals, $dec_point, $thousands_sep, $isDownload){
	    if($isDownload){
	    	return $data;
	    }else{
	    	return number_format ($data, $decimals, $dec_point, $thousands_sep);
	    }
	}
}

if(!function_exists("get_report_date_format")){
	function get_report_date_format(){
	    return (isset($_COOKIE['report_date_format']) && !empty($_COOKIE['report_date_format'])) ? $_COOKIE['report_date_format'] : REPORT_DATE_FORMAT;
	    // return REPORT_DATE_FORMAT;
	}
}

if (!function_exists("get_comparison_types")) {
    function get_comparison_types()
    {
        $comparison_list = array(
            "=" => "=",
            ">" => ">",
            "<" => "<"
        );
        return $comparison_list;
    }
}

