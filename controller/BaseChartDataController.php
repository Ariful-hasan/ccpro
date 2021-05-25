<?php
require_once  BASEPATH.'lib/chart/highchart.php'; 
class BaseChartDataController extends Controller {
	public $download_csv=false;
	public $is_highchart=false;
	public $multiparam=array();
	/**
	 * @var HighChartData
	 */
	public $hData;
	function __construct(){
		parent::__construct();		
		$this->hData=new HighChartData();
		if($this->request->getRequest('download_csv',0)=='true'){
			$this->is_highchart=$this->download_csv=true;
		}
		if($this->request->getRequest('is_highchart',0)==1){
			$this->is_highchart=true;
		}
		$this->isDownloadCSV=isset($_GET["download_csv"])?true:false;
		$this->filename=$this->request->getRequest("filename","downloadcsv");		
		$ptext = $this->request->getRequest ( 'ms' );
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
		}
			
		
	}
	function getMultiParam($key = '', $defaultValue = '') {
	    if (empty ( $key )) return $this->multiparam;
	    if (isset ( $this->multiparam [$key] )) return $this->multiparam [$key];
	    
	    return $defaultValue;
	}
	function ShowHighchartResponse(){
		if($this->isDownloadCSV){			
			DownloadChartCSV($this->hData);			
		}else{
			die($this->hData->GetJSON());
		}
	}
	/**
	 * @param GS_Chart $chart
	 */
	function ConvertToHighChartResponse($chart){
		/*
		title
				
		Object { text="Channel Report of 2015-08-12",  style="{font-size: 20px; color:...a; text-align: center;}"}
		elements
		
		[Object { type="line",  values=[13],  text="Channel Count",  more...}]
		bg_colour
		
		"#E5F1F4"
		x_axis
		
		Object { labels={...}}
		x_legend
		
		Object { text="Hour (GMT)",  style="{color: #736AFF; font-size: 15px;}"}
		y_legend
		
		Object { text="Channels",  style="{color: #736AFF; font-size: 12px;}"}
		y_axis
		
		
$this->hData->AddSeries($ylabel,$results->series);
$this->hData->SetTitle("Call-Statistics");
$this->hData->SetSubTitle($results->subTitle);
$this->hData->SetYAxisTitle($ylabel);
$this->hData->SetXAxiscategories($results->yAxis);
		*/	
		//echo $chart->x_axis->labels->labels[0];
		$this->hData->SetTitle($chart->title->text);
		$this->hData->SetSubTitle($chart->x_legend->text);
		$this->hData->SetYAxisTitle($chart->y_legend->text);
		$this->hData->SetXAxiscategories($chart->x_axis->labels->labels);
		$totallabels=count($chart->x_axis->labels->labels);		
		foreach ($chart->elements as $elm){
			$this->hData->chart->type= $elm->type;
			$countElem=count($elm->values);
			if($totallabels>$countElem){
				for($i=$countElem; $i<$totallabels; $i++){
					$elm->values[]=0;
				}
			}
			$this->hData->AddSeries($elm->text, $elm->values);
		}
		$this->ShowHighchartResponse();
	}
	
}
