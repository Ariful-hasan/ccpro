<?php
class GSHtmlForm{
	public  $multisearch=true;
	private $form_id;
	private $form_title="";
	private $form_url="";
	private $method="get";
	private $colModel=array();
	private $MultiSearchOperator;
	private $src_button_text;
	static $totalform=1;
	private $tempIndex=array();
  	function __construct($form_id=""){  
  		if(empty($form_id)){
  			$form_id="form_".self::$totalform;
  		}
  		$this->form_id=	$form_id;  		
  		self::$totalform++;
  		$this->MultiSearchOperator=array();
  		
	}
	function IsAddedSearchProperty(){
		return count($this->colModel)>0;
	}
	function SetDefaultValue($name,$default,$default2=''){
		$this->AddCustomPropertyOfModel($name, "dvalue", $default);
		$this->AddCustomPropertyOfModel($name, "dvalue2", $default2);
	}
	function SetFrom($title,$url,$method='get',$src_button_text="Search"){
		if(empty($method)){
			$method="get";
		}
		$method=strtolower($method);
		$this->form_title=$title;
		$this->form_url=$url;
		$this->method=$method;
		$this->src_button_text=$src_button_text;
	}	
  	function AddMultisearchOperator($property){
  		$this->MultiSearchOperator[]=$property;
  	} 
	function AddSearhProperty($title,$index_id,$search_type="",$options=array(),$def1="",$def2=""){
		//$this->AddModel($title, $index_id, $width,$align,"","","",true,$search_type,$searctionObtionValue,$isSortable);
		/*
		$searchoptions=null; 
		if($search_type=="select"){
			$selectOptions = isset($options['options']) ? $options['options'] : null;
			if (is_array($selectOptions) && (count($selectOptions)>0)) {
				$searchoptions=new stdClass();
				$searchoptions->value=new stdClass();
				foreach ($selectOptions as $key=>$val){
					$searchoptions->value->$key=$val;
				}
			}
		}
		*/
		//if (!isset($options['default'])) $options['default'] = '';
		
		$this->AddCustomModel(
			array(  "Title"=>$title,
				"search"=>true,
				"sortable"=>false,								
				"index"=>$index_id,
				"name"=>$index_id,								
				"stype"=>"$search_type",
				"options"=>$options,
				"dvalue"=>$def1,
				"dvalue2"=>$def2,
				//"searchoptions"=>$searchoptions
		));
		
	}	
	function AddCustomPropertyOfModel($index,$property,$value){
		if(!empty($this->tempIndex[$index])){
			$rowid=$this->tempIndex[$index];
			$this->colModel[$rowid]->$property=$value;
		}
	}
	function AddCustomModel($properties=array()){
		if(empty($properties["index"])){
			return;
		}
		$Model=new FromControlModel();
		foreach ($properties as $key=>$property){
			$Model->$key=$property;
		}
		$keyy=$properties["index"];
		$this->tempIndex[$keyy]=count($this->colModel);
		array_push($this->colModel,$Model);
	}
  	function GetFormId(){
  		$tableId=$this->form_id;
  		$srcDivId="src_$tableId";
  		$srcMFromId="ms_$tableId";
  		return  $srcMFromId;
  	}
  	/* end toolbar*/
	function GetCustomSearch(){
  		$tableId=$this->form_id;	
		 $srcDivId="src_$tableId";	
		 $srcMFromId="ms_$tableId";
		 ob_start();
		 if(!$this->multisearch){		 
		?>		
		<div class="grid-search-panel">
<div class="gs-grid-serach row form-horizontal" id="<?php echo $srcDivId;?>"	style="padding: 5px;">
	<div class="col-xs-4 ">
		<div class="form-group">
			<label for="selectpropery" class="control-label first-label col-sm-6 hidden-xs"><span class="hidden-sm"><?php _e("Select"); ?></span> <?php _e("Property"); ?></label>
			<div class="col-sm-6 row">
			<select class="input-sm srcOptionList form-control">
			 	<?php 
			 	$already=array();
			 	foreach ($this->colModel as $model){
					if(in_array($model->name, $already)){continue;}	
			 		if(!isset($model->search) ||$model->search){
			 			$datatest="";
			 			array_push($already, $model->name);
			 			if(!empty($model->searchoptions)){
			 				if(!empty($model->options) && count($model->options)>0){
			 					$datatest="data='".json_encode($model->options)."'";
			 				}
			 			}
			 			echo "<option stype='".(!empty($model->stype)?$model->stype:"")."' $datatest  value='$model->name'>$model->Title</option>";
			 		}
			 		?>		 		
			 	<?php }?>
			 	</select>
			 	</div>
		</div>
	</div>
	<div class="col-xs-8 ">	
	<form id="<?php echo $srcMFromId;?>" onsubmit="return false;">
	<div class="row">	
	<div id="<?php echo  $srcDivId."_text"?>" class="col-xs-8">
		<div class="form-group">
			<label for="srcText" class="control-label col-sm-6 hidden-xs"><span class="hidden-sm"><?php _e("Select"); ?></span> <?php _e("Value"); ?></label>
			<div class="col-sm-6">			
				<input autocomplete="off" class="srcTextValue form-control input-sm"
					type="text" name="srcText" value="" /> <select
					class="input-sm srcSelectValue form-control" style="display: none;"
					name="srcText">					

				</select>
			</div>
		</div>
	</div>	
	<div id="<?php echo  $srcDivId."_from"?>" class="col-xs-4 hidden">
		<div class="form-group">
			<label for="srcText" class="control-label col-sm-2">From</label>
			<div class="col-sm-10">
				<div class="input-group date gs-date-picker-grid-options">
					<input autocomplete="off" class="input-xs srcTextValue form-control srcFrom input-sm" 	type="text" name="srcFrom" value="" /> 
					<span class="input-group-addon" style="height: 24px !important; padding:6px 3px 0px 1px!important; line-height: 4px !important;"><span style="font-size: 8px !important;" class="fa fa-calendar"></span></span>
				</div>
			</div>
		</div>
	</div>
	<div id="<?php echo $srcDivId."_to"?>" class="col-xs-4 hidden">
		<div class="form-group">
			<label for="srcText" class="control-label col-sm-2"><?php _e("To"); ?></label>
			<div class="col-sm-10">
				<div class="input-group date gs-date-picker-grid-options" data-date-format="YYYY-MM-DD" >
				<input autocomplete="off" class="srcTextValue form-control  srcTo input-sm"  	type="text" name="srcTo" value="" />
				<span class="input-group-addon" style="height: 24px !important; padding:6px 3px 0px 1px!important; line-height: 4px !important;"><span style="font-size: 8px !important;" class="fa fa-calendar"></span></span>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-4">
		<?php /*?>		
		<span class="hidden-xs hidden-sm"><input class="gs-icheck " id="autosearch_<?php echo $tableId;?>"
			type="checkbox"></span>
		<label class="text-xs hidden-xs hidden-sm"	for="autosearch_<?php echo $tableId;?>"><?php _e("Auto Search") ; ?></label>
		<?php // */?>
		
		<button class="btn btn-xs btn-warning" type="submit"	><i	class="fa fa-search"></i><?php _e("Search") ; ?></button>
		<button class="btn btn-xs btn-danger" type="reset" ><i class="fa fa-times"> </i> <?php _e("Reset") ; ?></button>	
	</div>
	
	</div>	
	</form>
	</div>
	<div class="clear"></div>
</div>
</div>

<?php 
}else{
	$already=array();	
	?>
	<div class="grid-search-panel">
	<div class="gs-grid-serach row form-horizontal" id="<?php echo $srcDivId;?>"	style="padding: 5px;">
		<div class="col-xs-12 ">
		 <form id="<?php echo $srcMFromId;?>" >		
			<div class="panel panel-default">
			  <div class="src-heading panel-heading" style="padding:4px 15px !important;"><?php _e("Search"); ?>			  	
			  	<button class="pull-right btn btn-xs btn-warning" style="margin-right: 5px;" type="submit"	><i	class="fa fa-search"></i><?php _e("Search") ; ?></button> &nbsp;
				<button class="pull-right btn btn-xs btn-danger"  style="margin-right: 5px;" type="reset" ><i class="fa fa-times"> </i> <?php _e("Reset") ; ?></button>
			  </div>
			  
			  <div class="panel-body p-l-zero p-r-zero">			 
			  	<?php 
			  	$leftCol="";
			  	$rightCol="";
			  	$mi=1;			  
			  	foreach ($this->colModel as $model){
					if(in_array($model->name, $already)){continue;}
				  	if(!isset($model->search) ||$model->search){			
						array_push($already, $model->name);
				  		$datatest="";
				  
							ob_start();
							?>
							<div class="form-group ">
				      		    <label class="col-sm-2 col-md-4 control-label" for="name"><?php echo $model->Title; ?></label>
				      		    <?php if(empty($model->stype) ||$model->stype=="text" ){?>
				      		    <div class="col-sm-10 col-md-8">	
					      		   	<?php if(in_array($model->name,$this->MultiSearchOperator)){?> 
					      		   	 <div class="input-group input-group-sm">					      		     
	      								<div class="input-group-addon operator">
	      									<select name="op[<?php echo $model->name;?>]" class="">
	      										<option value="eq">=</option>
	      										<option value="gr">></option>
	      										<option value="lg"><</option>
	      									</select>
	      								</div>	
	      								<?php }?>	      										      		    	
					      		    	<input type="text" value="<?php echo $model->dvalue;?>" class="form-control input-sm" id="<?php echo $model->name;?>" name="ms[<?php echo $model->name;?>]" placeholder="<?php echo $model->Title; ?>"></input>
					      		 			<?php if(in_array($model->name,$this->MultiSearchOperator)){?> 
					      		 	</div>
					      		 	<?php }?>
				      		 	</div>
				      		 	<?php }elseif(strtolower($model->stype)=="dateonly" || strtolower($model->stype)=="date" || strtolower($model->stype)=="datetime"){
				      		 		$maintype=strtolower($model->stype);
				      		 		$placeholder="Form";
				      		 		if($maintype=='dateonly'){
				      		 			$model->stype="date";
				      		 			$placeholder=$model->Title;
				      		 		}
				      		 		if( strtolower($model->stype)=="datetime"){
				      		 			$model->dvalue=!empty($model->dvalue)?date('Y-m-d H:i',strtotime($model->dvalue)):"";
				      		 			$model->dvalue2=!empty($model->dvalue2)?date('Y-m-d H:i',strtotime($model->dvalue2)):"";
				      		 		}
				      		 	?>
				      		 	 <div class="col-sm-<?php echo $maintype=="dateonly"?10:5;?> col-md-<?php echo $maintype=="dateonly"?8:4;?>">
				      		 		 <div class="input-group <?php echo strtolower($model->stype)?> gs-<?php echo strtolower($model->stype);?>-picker-grid-options">
										<input autocomplete="off" class="srcTextValue form-control srcMFrom" 	type="text" name="ms[<?php echo $model->name;?>]<?php if($maintype!="dateonly"){?>[from]<?php }?>" value="<?php echo $model->dvalue;?>" placeholder="<?php echo $placeholder;?>" /> 
										<span class="input-group-addon" style="height: 24px !important; padding:2px 2px 0px 1px ; line-height: 4px !important;"><span style="font-size: 12px !important;" class="fa <?php echo strtolower($model->stype)=="date"?' fa-calendar ':' fa-clock-o '?>"></span></span>
									</div>
				      		 	 </div>
				      		 	 <?php if($maintype!="dateonly"){?>
				      		 	 <div class="col-sm-5 col-md-4">
				      		 	 	 <div class="input-group <?php echo strtolower($model->stype)?> gs-<?php echo strtolower($model->stype);?>-picker-grid-options">
										<input autocomplete="off" class="srcTextValue form-control srcMFrom" 	type="text" name="ms[<?php echo $model->name;?>][to]" value="<?php echo $model->dvalue2;?>"  placeholder="To"  />										
										<span class="input-group-addon" style="height: 24px !important; padding:2px 2px 0px 1px ; line-height: 4px !important;"><span style="font-size: 12px !important;" class="fa <?php echo strtolower($model->stype)=="date"?' fa-calendar ':' fa-clock-o '?>"></span></span>										
									</div>
				      		 	 </div>
				      		 	 <?php }?>
				      		 	<?php }elseif(strtolower($model->stype)=="select"){
				      		 		
				      		 	?>
				      		 	 	<div class="col-sm-10 col-md-8">				      		 	 					      		    	
				      		    		<select class="form-control input-sm" id="<?php echo $model->name;?>" name="ms[<?php echo $model->name;?>]">
				      		    			<?php
				      		    			if(!empty($model->options) && count($model->options)>0){											
				      		    				foreach ($model->options as  $value=>$title){
													?>
													<option <?php echo $model->dvalue==$value?' selected="selected" ':'';?> value="<?php echo $value?>"><?php echo $title;?></option>
													<?php 
												}	
				      		    			} 
				      		    			?>
				      		    		</select>
				      		 		</div>
				      		 	<?php }?>
			      		 	</div>			      		 	
							<?php 
							if($mi==1){
								$leftCol.=ob_get_clean();
								$mi=0;
							}else{
								$rightCol.=ob_get_clean();
								$mi=1;
							}
							
						
					}
				}
			  	?>
			      <div class="col-md-6 form form-horizontal">
			      		<?php echo $leftCol;?>
			      </div>
			      <div class="col-md-6">
			      		<?php echo $rightCol;?>
			      </div>
			     
			  </div>
			</div>
			</form>
		</div>
	</div>
	</div>
	<?php 
}
		 $output = ob_get_contents();
		 ob_end_clean();
	
		echo $output;
  	}
  	function Show(){
		$this->GetCustomSearch();
		?>
		<script type="text/javascript"	>
			jQuery(function($){
				try{
					SetDateGridPicker();
				}catch(e){}
			});
		</script>
	<?php 
	}
	
	function getData()
	{
		$response = new stdClass();
		foreach ($this->colModel as $model){
			$response->{$model->name} = $this->GetValueByProperty($model->name, $model->options['default']);
			//echo $model->name . '<br>';
		}
		//var_dump($response);
		return $response;
	}
	
	function GetValueByProperty($property,$default=''){
		if($this->method=="post"){
			return !empty($_POST[$property])?$_POST[$property]:$default;
		}else{
			return !empty($_GET[$property])?$_GET[$property]:$default;
		}
	}
}

class FromControlModel{
	var $Title;
	var $objectName;
	var $width;
	var $name;
  	var $index;
  	var $formater="number";
  	var $options;
  	var $sortable;
  	var $dvalue="";
  	var $dvalue2="";
}
if(!function_exists('_e')){
	function _e($var){
		echo $var;
	}
}