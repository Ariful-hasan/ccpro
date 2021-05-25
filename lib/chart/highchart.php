<?php
if(!class_exists('GSHtmlForm')){
	require_once dirname(__FILE__).'/gshtmlform.php';
}
class HighChartData{	
	/**
	 * Enter description here ...
	 * @var ChartMain
	 */
	var $chart;
	var $title;
	var $subtitle;
	var $xAxis; 
	var $yAxis;
	  /**
	   * Chart Legend
	   * @var ChartLegend
	   */
	var  $legend;
	var  $tooltip;
	var  $plotOptions;
	/**
	 * @var PIESeries
	 */
	private $piedataseries;	
	var  $series=array();	  
	  function __construct(){
	  	$this->title=new stdClass();
	  	$this->subtitle=new stdClass();
	  	$this->legend=new ChartLegend();
	  	$this->yAxis=new stdClass();	  		 
	   	$this->yAxis->min= 0;
	   	$this->yAxis->title=new stdClass();
	   	$this->yAxis->title->text= 'Not Defined';	   	
	   	$this->chart=new ChartMain();
	   	$this->plotOptions=new stdClass();
   		$this->plotOptions->column=new stdClass();
   		$this->plotOptions->column->pointPadding= 0.2;
        $this->plotOptions->column->borderWidth= 0;
        $this->tooltip=new stdClass();
        $this->tooltip->formatter=null;
        $this->piedataseries=new PIESeries();
	          
	  }
	  function SetTooltipFormatter($jsMethod){
	  	 $this->tooltip->formatter=$jsMethod;
	  }
	  function SetPlotOption($pointPadding,$borderWidth){
	  	$this->plotOptions->column->pointPadding= $pointPadding;
        $this->plotOptions->column->borderWidth= $borderWidth;
	  }
	function SetPlotOptionByObject($plotoption,$type="pie"){	
		$this->plotOptions=new stdClass();	
	  	$this->plotOptions->$type=$plotoption;        
	  }
	
 	/**
 	 * Enter description here ...
 	 * @param unknown_type $Title
 	 */
 	function SetTitle($Title=""){ 		
	  	$this->title->text= $Title;
	}
 	/**
 	 * Enter description here ...
 	 * @param unknown_type $subTitle
 	 */
 	function SetSubTitle($subTitle=""){
	  	$this->subtitle->text= $subTitle;
	}
	  
	  /**
	   * Enter description here ...
	   * @param array $categoriesArray
	   */
	  function SetXAxiscategories($categoriesArray=array()){
	  	 $this->xAxis=new stdClass();
	  	 $this->xAxis->categories=$categoriesArray;	  	
	  }
	  
	  /**
	   * Set YAxis Title ...
	   * @param string $title
	   */
	  function SetYAxisTitle($title=""){
	  	$this->yAxis->title->text= $title;
	  }
	  /**
	   * Enter description here ...
	   * @param string $seriesName
	   * @param Array $seriesData
	   */
	  function AddSeries($seriesName,$seriesData){
	  	$obj=new stdClass();
	  	$obj->name=$seriesName;
	  	$obj->data=$seriesData;
	  	array_push($this->series, $obj); 
	  }
 	 function AddSeriesObject($series){
	  	array_push($this->series, $series);
	  	
	  }
	  function AddPieSeries($pieSeriesData){
	  	$this->piedataseries->AddSeries($pieSeriesData);	  
	  }
	  function AddPieSeriesArray($pieSeriesData){
		  foreach ($pieSeriesData as $se){
			$this->piedataseries->AddSeries($se);
		}	  
	  }
	  function SetPieData(){
	  	//$this->AddSeriesObject($this->piedataseries);
	  	$this->series[]=&$this->piedataseries;
	  }
	  function GetJSON(){
	  	$this->exporting=new stdClass();
	  	$this->exporting->enabled=false;
	  	$str=json_encode($this);
	  	$str=str_replace(array('"%','%"','\n','\r','\t'), "", $str);
	  	return $str;
	  }
}
class  ChartMain {
	var $renderTo= '';
	var $type='line';
	var $events; 
	function __construct(){
		$this->events=new stdClass();
	}
	function SetEvent($eventName,$javascriptMethod){
		$this->events->$eventName=$javascriptMethod;
	}
	           
}
class ChartLegend{	
	var $layout='vertical';
	var $backgroundColor='#FFFFFF';
	var $align='left';
	var $verticalAlign='top';
	var $x=100;
	var $y= 0;
	var $floating= true;
	var $shadow= true;	
	var $enabled=true;       
}

class  PiePlotOption{			
		var $allowPointSelect= true;
		var $cursor= 'pointer';
		/**
		 * Enter description here ...
		 * @var DataLabel
		 */
		var $dataLabels;
		var $showInLegend=true;
		function __construct(){
			$this->dataLabels=new DataLabel();
		}

}
class PIESeries{
	var $type='pie';
	var $name='';
	var $data=array();
	/**
	 * Enter description here ...
	 * @var PIESeriesData  $seriesData
	 */
	
	function AddSeries($seriesData){
		array_push($this->data, $seriesData);
	}	
}
class PIESeriesData{
	var $name= '';
	var $y= 0;
	var $sliced=false;
	var $selected= false;
				
}
class DataLabel{
	var $enabled=true;
	//var $color='#000000';
	//var $connectorColor= '#000000';

}
class HighChart{
	private static $count=0;
	public $id;	
	public $IsAjax=false;
	public $DataUrl="";
	/**
	 * @var HighChartData
	 */
	public $ChartData;
	public $param_method="";
	
	/**
	 * @var GSHtmlForm;
	 */
	public $searchform;
	public $showDownloadCSVButtonInTop=true;
	private static $isLoadedJs=false;
	function __construct($chart_id="",$type="line"){
		$this->ChartData=new HighChartData();		
		if(empty($chart_id)){			
			$chart_id="chart_".self::$count;
		}
		$this->id=$chart_id;
		$this->ChartData->chart->renderTo=$this->id;
		self::$count++;
		$this->SetChartType($type);
		$this->searchform=new GSHtmlForm();
	}
	function SetParamMethod($jsmethod){
		$this->param_method=$jsmethod;
	}
	
	static function LoadJs(){
		if(!self::$isLoadedJs){
			self::$isLoadedJs=true;
		?>
		<link href="<?php echo lib_url()?>chart/chart.css" rel="stylesheet" type="text/css" media="screen"/>
		<script src="<?php echo lib_url()?>chart/highcharts.js"></script>			
		<?php 
		}
	}
	function SetChartType($str){
		$str=strtolower($str);
		$type=array("line","pie","bar","column");
		if(in_array($str, $type)){
			$this->ChartData->chart->type=$str;
			if($str=="pie"){
				$piePloatOption=new PiePlotOption();
				$this->ChartData->SetPlotOptionByObject($piePloatOption);
				$this->ChartData->SetPieData();
				$this->ChartData->legend->enabled=false;
			}
		}else{
			$this->ChartData->chart->type="line";
		}
	}	
	function in_string($needle, $haystack){
		//echo $haystack."-".$needle;
		if(strpos($haystack, $needle) !== FALSE){
			return true;
		}else{
			return false;
		}
	
	}
	function GetReloadMethod($isBracket=true){
		return 'LoadData_'.$this->id.($isBracket?'();':'');
	}
	function ShowChart($chartClass=""){	
		self::LoadJs();	
		$searchpanelshowed=false;	
		if($this->searchform->IsAddedSearchProperty()){	
			$searchpanelshowed=true;
			$this->searchform->Show();
			
		}
		?>		
		
		<script type="text/javascript">
			var chartconfig_<?php echo $this->id;?>={};
			$(function () {
				<?php if($searchpanelshowed){?>
				$("#<?php echo $this->searchform->GetFormId()?>").on("submit",function(e){
					e.preventDefault();
					e.stopPropagation();
					LoadData_<?php echo $this->id ?>();
				});
				<?php }?>				
				<?php if(!$this->IsAjax){
					if(!empty($this->ChartData)){
					?>
					try{
						$('#<?php echo $this->id?>').css({"max-width":$('#<?php echo $this->id?>').parent().width()-20});
						chartconfig_<?php echo $this->id;?>=<?php echo $this->ChartData->GetJSON();?>;
					
						LoadData_<?php echo $this->id ?>();
					}catch(e){
						if(typeof(console)!="undefined"){
							console.log(e.message);
						}
					}
					<?php 
					}	
			}else{?>				
				LoadData_<?php echo $this->id ?>();
				<?php }?>

				<?php if($this->ChartData->chart->type=="bar" || $this->ChartData->chart->type=="line" || $this->ChartData->chart->type=="column"){?>
				$('#<?php echo $this->id?>_select').on("change",function(){
					chartconfig_<?php echo $this->id;?>.chart.type=$(this).val();
					$('#<?php echo $this->id?>').highcharts(chartconfig_<?php echo $this->id;?>);
				});
				<?php }?>
				try{
					AddOnPageResize(ResizeChart_<?php echo $this->id ?>);
				}catch(e){
					if(typeof(console)!="undefined"){
						console.log("resize:"+e.message);
					}
				}
				
			});
			<?php if($this->IsAjax){?>
			function LoadData_<?php echo $this->id ?>(param,is_download){
				
				if(typeof(is_download)=="undefined"){
					is_download=false;
				}
				if(typeof(param)=="undefined"){
					param={};
				}
				<?php if($searchpanelshowed){?>
					param.ms=$("#<?php echo $this->searchform->GetFormId()?>").serialize();
				<?php }?>
				<?php if(!empty($this->param_method)){?>
				param=<?php echo $this->param_method;?>(param);
				<?php }?>				
				if(!is_download){
					param.is_highchart=1;					
					jQuery.ajax({
						url:"<?php echo $this->DataUrl;?>",
						type:"GET",
						data:param,
						dataType:"json",
						scriptCharset: "utf-8",
						cache:false,
						beforeSend:function(){
							$("#<?php echo $this->id;?>_loader").fadeIn();
						},
						success:function(rData){							
							chartconfig_<?php echo $this->id;?>=rData;
							chartconfig_<?php echo $this->id;?>.chart.renderTo="<?php echo $this->id;?>";					
							var chartwidth=$('#<?php echo $this->id?>').parent().width()-20;
							chartconfig_<?php echo $this->id;?>.chart.width=chartwidth;;
							jQuery("#<?php echo $this->id;?>_select").val(chartconfig_<?php echo $this->id;?>.chart.type);		
							$('#<?php echo $this->id?>').highcharts(chartconfig_<?php echo $this->id;?>);
						},
						complete:function(jqXHR, textStatus){	
							$("#<?php echo $this->id;?>_loader").fadeOut();						    	
					    	if(textStatus=="error"){	    	
					    		if(jqXHR.status=="404"){
					    			MsgBox("Error: Page does not found");
					    		}else if(jqXHR.status=="408"){
					    			alertMsgBox("Error: Sarver does not active.");
					    		}
					    		else{
					    			alert("Error: May be connection lost.");
					    		}					    		
					    	}
						 }
						
						});
				}else{
					param.download_csv=true;
					if(jQuery("#difrm").length==0){
				 		jQuery("body").append("<iframe id='difrm' style='border:none;height:0;width:0'></iframe>");
			 		}
					serialize = function(obj) {
			 			  var str = [];
			 			  for(var p in obj)
			 			    if (obj.hasOwnProperty(p)) {
			 			      str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
			 			    }
			 			  return str.join("&");
			 		};
			 		
			 		var durl="<?php echo $this->DataUrl.(strpos($this->DataUrl,"?")?"&":"?");?>"+serialize(param);			 		
					//console.log(durl);
			 		jQuery("#difrm").attr("src",durl);			 		
			 			
				}
			}	
			function ResizeChart_<?php echo $this->id ?>(){
				try{
				var chartwidth=$('#<?php echo $this->id?>').parent().width()-20;
				if(chartconfig_<?php echo $this->id;?>.chart.width!=chartwidth){
					chartconfig_<?php echo $this->id;?>.chart.width=chartwidth;	
					$('#<?php echo $this->id?>').highcharts(chartconfig_<?php echo $this->id;?>);
				}
				}catch(e){}
				
			}
			function DownloadCSVChart_<?php echo $this->id ?>(){
				try{
					LoadData_<?php echo $this->id ?>({},true);
				}catch(e){}
				
			}
			<?php }?>
				
		</script>
		<div class="chart-container"  style ="position:relative;">
			<?php if($this->IsAjax){?>
			<div id="<?php echo $this->id;?>_loader" class="chart-loader" style="z-index:999; position: absolute; top:0; right:0; left:0; bottom: 0; background-color: rgba(0, 0, 0, 0.26);">
				<i style=" color:rgba(254, 251, 255, 0.57); font-size: 60px;  left: 50%;   margin-left: -40px;  margin-top: -30px;  position: absolute;  top: 50%;" class="fa fa-refresh fa-spin"></i>
			</div>
			<?php }?>
			<?php if($this->ChartData->chart->type=="column" || $this->ChartData->chart->type=="line"){?>		
			<div class="typeselector" style ="position: absolute; right: 30px; z-index: 99; top:14px;">
				<?php if($this->showDownloadCSVButtonInTop){?>
				<button class="btn btn-xs btn-success" onclick="DownloadCSVChart_<?php echo $this->id ?>();" style="margin-left: 6px; margin-top: -5px;" type="button" ><i class="fa fa-download"></i> <?php _e("Download CSV") ; ?></button>
				<?php }?>
				<label for="<?php echo $this->id;?>_select">Choose Type</label>
				<select name="<?php echo $this->id;?>_select" id="<?php echo $this->id;?>_select">
					<option <?php echo $this->ChartData->chart->type=="column"?'selected="selected"':'';?> value="column"> Column</option>
					<option <?php echo $this->ChartData->chart->type=="line"?'selected="selected"':'';?> value="line"> Line</option>
										
				</select>
			</div>
			<?php }?>
			<div id="<?php echo $this->id;?>"  class="<?php echo $chartClass;?>">	
				
			</div>
		</div>
		<?php 
	}
	public static function GetChart($dataurl,$chart_id=""){
		$obj= new self();
		$obj->ShowChart();
		
	}
}
