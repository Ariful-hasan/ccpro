/**
 * 
 */
isDisableFAnimation = false;
phoneFormat="999 999 9999";
var MyAjaxChange = false;
var isWaitAdded = false;
var showlog=false;
var lboxOnCloseMethods=new Array();
var pageResizeMethod=new Array();
if(typeof(console)=="undefined"){
	console={log:function(){}};
}
window.onbeforeunload = function(){
	try{
		ShowWait(true,"Loading..");
		sleep(50);
	}catch(e){
		
	}	  
};

function reloadRatingData(){
	try{;}catch(e){;}
}
function gcl(msg,checkenable){
	if(checkenable && !showlog){
		return;
	}
	if(typeof(console.log) !="undefined"){
		console.log(msg);
	}
}
function DownloadFile(url){
	if(jQuery("#difrm").length==0){
 		jQuery("body").append("<iframe id='difrm' style='border:none;height:0;width:0'></iframe>");
		}
	jQuery("#difrm").attr("src",url);
}

$(function(){	
	try{
	SetTable();
	HideMe();	
	FromOnSubmit();
	SetLightBox();
	Legend();
	SetConfirm();
    SetDateTimePicker();
	SetSlider();
	}catch(e){ gcl(e.message,true);}	
	try{
	$.extend($.gritter.options, { 
        position: 'top-right' // defaults to 'top-right' but can be 'bottom-left', 'bottom-right', 'top-left', 'top-right' (added in 1.7.1)
	
	});
	}catch(e){ gcl(e.message,true);}
	SetSelectBox();
	SetAjaxForm();
	$(document).on("click",".hideme",function(){
		$(this).parent().hide('slow');
	});
	try{
		$('.tooltip').tooltipster({maxWidth:500,position:"bottom"});
	}catch(e){ gcl(e.message,true);}
	$("body").prepend('<div id="MainLoader"><div class="msgText"></div></div>');
	try{
		$(".gs-icheck").iCheck({
	        checkboxClass: 'icheckbox_square-red',
	        radioClass: 'iradio_square-red'
	    });
	}catch(e){}
	SetDatePicker();
    SetReportDatePicker();
    // SetReportDateTimePicker();
	SetColorPicker();
	try{
		$('.GSTooltip').tooltip();
	    $('.GSPopover').popover();
	    $('.GSPopoverHtml').popover({       
	        html: true,
	        content: function () {
	        	var content=$(this).data("custom-content");
	        	//console.log($(content));
	        	content=$(content).html();
	        	return content;           
	        }
	    });
	}catch(e){
		
	}
	try{
		SetFromValidation();
		// */
		setMask();
	   

	    $(".alert").alert();
	}catch(e){
		
	}
	try{ $.gritter.options.position="bottom-right";}catch(e){}
	
	try{ $(".report_table").addClass('table'); }catch(e){}
	try{
		$("body").on('click','i.fa-refresh',function(){ 
			var obj=$(this);
			obj.addClass("fa-spin");
			setTimeout(function(){
				obj.removeClass("fa-spin");
			},2000)
			});
	}catch(e){}
	
	SetPOPUPWindow();
	
});
function SetSlider(){
	try{
		$(".slider").slider();
	}catch(e){}
}
function SetDatePicker(){
	try{		
		$(".gs-date-picker:not(.added-picker),.date-pick:not(.added-picker)").datetimepicker({
				pickTime: false,
				timepicker:false,
				useStrict:true,
				format:"Y-m-d"
		});	
		$(".gs-date-picker:not(.added-picker)").addClass("added-picker");
		(".gs-datetime-picker:not(.added-picker)").datetimepicker({		
			pickTime: false,
			timepicker:false,
			useStrict:true,
			format:"Y-m-d"
		});	
		$(".gs-datetime-picker:not(.added-picker)").addClass("added-picker");
	}catch(e){	
		gcl(e.message,true);
	}
}

function SetDateTimePicker(){
	try{
		$(".gs-datetime-picker:not(.added-picker),.date-pick:not(.added-picker)").datetimepicker({
				pickTime: true,
				timepicker:true,
				useStrict:true,
				format:"Y-m-d H:i:s"
		});
		$(".gs-datetime-picker:not(.added-picker)").addClass("added-picker");
		(".gs-datetimetime-picker:not(.added-picker)").datetimepicker({
			pickTime: true,
			timepicker:true,
			useStrict:true,
			format:"Y-m-d H:i:s"
		});
		$(".gs-datetime-picker:not(.added-picker)").addClass("added-picker");
	}catch(e){
		gcl(e.message,true);
	}
}
function SetColorPicker(){
	try{		
		$(".gs-color-picker:not(.added-color-picker)").each(function(e){
			try{
			var target=$(this).data("target");
			
			if(target && target!=""){
				var inputtarget=$(target);
				$(this).colorpicker({
					input:inputtarget
				});	
			}else{
				$(this).colorpicker();
			}
			$(this).addClass("added-color-picker");
			}catch(e){
				gcl(e.message);
			}
		});		
	}catch(e){		
	}
}
function SetDateGridPicker(){
	try{		
		$(".gs-date-picker-grid-options input, .gs-date-from-picker-grid-options input, .gs-date-to-picker-grid-options input").each(function(e){
			if(!$(this).hasClass("addedDate")){
				$(this).addClass("addedDate");
				$(this).datetimepicker({
					pickTime: false,
					timepicker:false,
					useStrict:true,
					format:"Y-m-d"
					
				});
			}
		});	
	}catch(e){gsl(e.message);}
	try{		
		$(".gs-date-custom-picker-grid-options input").each(function(e){
			if(!$(this).hasClass("addedDate")){
				$(this).addClass("addedDate");
				$(this).datetimepicker({
					pickTime: false,
					timepicker:false,
					useStrict:true,
					format:$(this).data('format')
					
				});
			}
		});	
	}catch(e){gsl(e.message);}
	try{		
		$(".gs-datetime-from-picker-grid-options input").each(function(e){
			if(!$(this).hasClass("addedDate")){
				$(this).addClass("addedDate");
				$(this).datetimepicker({
					pickTime: true,
					timepicker:true,
					useStrict:true,				
					defaultTime:'00:00',
					format:"Y-m-d H:00"
					//,allowTimes:['00:00', '01:00', '02:00','03:00', '04:00', '05:00', '06:00', '07:00','08:00', '09:00', '10:00','11:00', '12:00', '13:00', '14:00', '15:00','16:00', '17:00','18:00', '19:00', '20:00', '22:00', '23:00']
				});
			}
		});		
		$(".gs-datetime-to-picker-grid-options input").each(function(e){
			if(!$(this).hasClass("addedDate")){
				$(this).addClass("addedDate");
				$(this).datetimepicker({
					pickTime: true,
					timepicker:true,
					useStrict:true,							
					defaultTime:'23:00',
					format:"Y-m-d H:59",
					allowTimes:['00:59', '01:59', '02:59','03:59', '04:59', '05:59', '06:59', '07:59','08:59', '09:59', '10:59','11:59', '12:59', '13:59', '14:59', '15:59','16:59', '17:59','18:59', '19:59', '20:59', '22:59', '23:59']
				});
			}
		});
	}catch(e){gsl(e.message);}
}
function SetSelectBox(){
	try{
	//$("select").chosen();
	}catch(e){ gcl(e.message,true);}
}
function sleep(milliseconds) {
	  var start = new Date().getTime();
	  for (var i = 0; i < 1e7; i++) {
	    if ((new Date().getTime() - start) > milliseconds){
	      break;
	    }
	  }
	}
function ShowWait(isShow,msg){
	if(typeof(isShow)=="undefined"){
		isShow=true;
	}
	if(typeof(msg)=="undefined"){
		msg="Please Wait";
	}
	if(isShow){
		$("#MainLoader .msgText").html(msg);
		$("#MainLoader").fadeIn();
	}else{
		$("#MainLoader").fadeOut();
	}
	
}
function ShowSideBarLoaderWait(selector,isShow,msg){
	var element=$(selector);
	if(typeof(isShow)=="undefined"){
		isShow=true;
	}
	if(typeof(msg)=="undefined"){
		msg="Please Wait";
	}
	if(isShow){
		element.find(".msgText").html(msg);
		element.fadeIn();
	}else{
		element.fadeOut();
	}
	
}
function ShowGritterMsg(msg,IsError){
		try{
			if(typeof(IsError)=='undefined'){
				IsError=false;
			}	
		$.gritter.add({
			position: 'bottom-left',
			// (string | mandatory) the heading of the notification
			//title: 'This is a regular notice!',
			// (string | mandatory) the text inside the notification
			text: msg,
			/*// (string | optional) the image to display on the left*/
			image: IsError?base_url+'/images/statuserror.png':base_url+'/images/statusOk.png',
			
			// (bool | optional) if you want it to fade out on its own or just sit there
			sticky: false,
			// (int | optional) the time you want it to be alive for before fading out
			time: 5000
		});	
		}catch(e){gcl(e.message);}
					    		
}
function SetConfirm(){
	$("body").on("click",".Confirm",function(e){
		var msg = $(this).attr('msg');
		if (confirm(msg) == false) {
			e.stopPropagation();
			e.preventDefault();
		}
	});
	$("body").on("click",".ConfirmAjaxWR",function(e){	
		e.stopPropagation();
		e.preventDefault();		
		var obj=$(this);
		var msg = $(this).attr('msg');
		var msgUndefined=(typeof(msg)=="undefined");		
		var callAfterProcess = $(this).attr('oncompleted');		
		if (!msgUndefined ){
			if(confirm(msg) == false) {
				return;
			}
		}
		var url=$(this).attr("href");	
		if(typeof(url)=="undefined" || url==""){
				alert("Target url is empty");
				return;
		}
			//ShowWait(true, "Please wait <br>Processing....");
			ShowWait();	
			
			$.ajax({
				url : url,
		        type : "GET", 
		        scriptCharset: "utf-8",
		        dataType :"json",
				beforeSend: function() {
					ShowWait();
			    },		   
			    success: function(rdata){			    	
			    	ShowWait(false);			    	
			    	
			    		try{
			    			if(rdata.status){
			    				showSuccess(rdata.msg);
			    			}else{
			    				showErrorMessage(rdata.msg);
			    			}
			    		}catch(e){ gcl(e.message,true);}
			    		
			    		if(rdata.status){
			    			ReloadAll();
			    		}			    		
			    	
			    	try{
				    	if(callAfterProcess){
				    		var com=eval(callAfterProcess);
				    		com(rdata,obj);
				    	}
				    	}catch(e){cl(e.message);}
			    }
			});
			
		
		
		
	});
}
function SetAjaxForm(){
	return;
	var Ajaxbostrapvalidator=$("form.gs-ajax-form").bootstrapValidator({	
		excluded: ':disabled',
	    message: 'This value is not valid',   
	    feedbackIcons: {
	        valid: 'fa fa-check',
	        invalid: 'fa fa-times',
	        validating: 'fa fa-refresh'
	    },fields: {
	    	cc_exp_date: {
	            validators: {
	                callback: {
	                    message: 'Invalid MMYY',
	                    callback: function(value, validator) {
	                        var m = new moment(value, 'MMYY', true);                           
	                        if (!m.isValid()) {
	                            return false;
	                        }
	                       var m2=moment();
	                        // US independence day is July 4
	                        return m>m2;
	                    }
	                }
	            }
	        }
	    },
	    submitHandler:function(validator, form, submitButton){	  
	    	var rtype=form.attr("request-type");
	    	if(!rtype){rtype="json";}	    
	           $.ajax({
				   type: "POST",
				   url: form.attr('action'),		  
				   data: form.serialize(),
				   cache:false,	
				   dataType :rtype,
				   beforeSend:function() {
						//	ShowWait();
					   form.find("[type=submit]").addClass("Loading");	
					   form.find("[type=submit]").attr("disabled","disabled");
				    },		   	  
				   success: function(rdata){
					   try{
			    			var oncomplete=form.attr("oncomplete");
			    			if(oncomplete){		
			    				eval(oncomplete+"(rdata);");
			    				return;
			    			}
			    		}catch(e){
			    			
			    		}
			    		if(rtype!="json")return;
					   //ShowWait(false);
				    	if(rdata.status){			    		
				    		$.gritter.add({
				    			position: 'top-right',text: rdata.msg,
				    			image: base_url+'/images/statusOk.png',sticky: false,time: 5000
							});
				    		
				    		ReloadAll();			    		
				    	}else{
				    		$.gritter.add({
				    			position: 'top-right',text: rdata.msg,		
								image: base_url+'/images/statuserror.png',sticky: false,time: 5000
							});
				    	}
				   },
				   complete:function(){
					   form.find("[type=submit]").removeClass("Loading");
					   form.find("[type=submit]").removeAttr("disabled");
				   }
		        });
			 }
	}).find('.cbox-control')
	// Init iCheck elements
	.iCheck({
	    checkboxClass: 'icheckbox_square-green',
	    radioClass: 'iradio_square-green'
	})
	// Called when the radios/checkboxes are changed
	.on('ifChanged', function(e) {
	    // Get the field name
		try{
	    var field = $(this).attr('name');
	    var fromobj=$(this).closest("form");
	    fromobj    
	    // Mark the field as not validated
	        .bootstrapValidator('updateStatus', field, 'NOT_VALIDATED')
	        // Validate field
	        .bootstrapValidator('validateField', field);
		}catch(e){}
	});		
	
}
function HideMe(){
	$(".HideME").click(function(){
		$(this).parent().hide('slow');
	});
}
function AddOnCloseMethod(method){
	lboxOnCloseMethods.push(method);
}

function AddOnPageResize(method){
	pageResizeMethod.push(method);
}

function OnCloseError() {
	$(".formError").hide();
}
function ReloadAll(){
	try {
		for(i in lboxOnCloseMethods){
			lboxOnCloseMethods[i]();
		}			
	} catch (e) {
		console.log(e.message);
	}
}
function ResizeAll(){
	try {			
		for(i in pageResizeMethod){
			pageResizeMethod[i]();
		}			
	} catch (e) {
		console.log(e.message);
	}
}
function OnClosed() {
	var onclosemainevent=$(this).attr('onclose');
	if(onclosemainevent){		
		eval(onclosemainevent+"()");
		return;
	}
		
	if (MyAjaxChange) {		
		var data = "";
		try {			
			for(i in lboxOnCloseMethods){
				lboxOnCloseMethods[i]();
			}
			data = onRefresh();		
		} catch (e) {
			data = "";
		}
		MyAjaxChange=false;
	}
	/*
	 * $.ajax({ type: "POST", url: document.URL, cache:false, data:data,
	 * beforeSend:beforeSend, success: function(data){ Loading(false);
	 * $('body').html(data); } });
	 */
	// window.location.reload(true);
	OnCloseError();
}
function LegendIn(){	
	$(this).animate({
		width:"+=16"
	},200,function(){
		$min=$(this).attr("minimize");
		if($min){
			$(this).find(".maximize").fadeIn();
			$(this).find(".minimize").fadeOut();
		}else{
			$(this).find(".maximize").fadeOut();
			$(this).find(".minimize").fadeIn();
		}
	});
}
function LegendOut(){
	console.log("out");
	$(this).find(".maximize,.minimize").hide();
	$(this).animate({
		width:"-=16"
	},200);
}
function Legend(){		
	//$(".Dashboard .GSFieldset legend").append("<span class='minimize'> </span><span class='maximize'> </span>")
	//$(".Dashboard .GSFieldset legend").attr("minimize",false);
	//$(".Dashboard .GSFieldset legend").hover(LegendIn,LegendOut);
	
}
function FromOnSubmit(){
	
	$("form input[type=submit]").click(function(e){
		var isOk=true;
		try{
			isOk = $(".mainForm").validationEngine('validate');
		}catch(e){
			isOk=true;
		}		
		if(isOk){			
			if(!$(".mainForm").hasClass("NoLoading")){
				$(this).addClass("Loading");
			}
		}
		//return false;
	});
		
	
}
function SetValidation(selector,option){	
	try{
	var d=1;
	if(!selector){
		selector=".mainForm";
	}	
	var defaultObtion={fadeDuration:300, validationEventTrigger: "submit" ,focusFirstField:false};
	if(option){
		$.extend(defaultObtion,option);
	}
	jQuery(selector+" input,"+selector+"  select").each(function(e){
		var id=$(this).attr("id");
		if(!id){
			$(this).attr("id","id"+d);
			d++;
		}
	});
	jQuery(selector).validationEngine(defaultObtion);	
	$(selector).bind("jqv.field.result", function(obj,field, isError, promptText){		
		if(!field.hasClass("vajax")){
			if(isError){
				field.css("border","1px solid red");
			}else{
				field.css("border","1px solid #666666");
			}
		}
		
	});
	
	/*jQuery(selector+"  input,"+selector+"  select").live("focus",function(e){
		HideAllError();
	});*/
	}catch(e){
		gcl(e.message,true);
	}
}
function HideAllError(){
	/*jQuery(".mainForm").validationEngine('hideAll');*/
	$(".formError").fadeOut(500, function() {
		// remove prompt once invisible
		$(this).parent('.formErrorOuter').remove();
		$(this).remove();
	});
}

function SetLightBox() {	
	SetTable();	
	SetValidation();
	try{
		reloadRatingData();
		$(".lightbox").colorbox({
			top : "10%"
		});
	}catch(e){gcl(e.message);}
	// $(".lightboxWRM").live("click",function(e){
	$(".lightboxWRM").each(function(e) {

		try{
			var url = $(this).attr("href");
			var width = $(this).attr("width");
			if (currentUrl == url)
				return;
			currentUrl = url;
			if ($.browser.mozilla) {
				$(this).colorbox({
					overlayClose : false,
					top : "10%",
					onClosed : OnClosed
				});
			} else {
				if (width) {
					$(this).colorbox({
						width : width,
						overlayClose : false,
						top : "10%",
						onClosed : OnClosed
					});
				} else {
					$(this).colorbox({
						width : '100%',
						overlayClose : false,
						top : "10%",
						onClosed : OnClosed
					});
				}

			}
		}catch(e){gcl(e.message);}
	});
	try{
	$(".lightboxWR,.LightboxWR").colorbox({
		overlayClose : false,
		top : "10%",
		onClosed : OnClosed
	});
	
	$(".lightboxWIF").colorbox({
		iframe : true,
		overlayClose : false,
		width : "1060px",
		height : "100%",
		onClosed : OnCloseError
	});
	$(".lightboxWIFR,.LightboxWIFR").colorbox({
		iframe : true,
		overlayClose : false,
		width : "1060px",
		height : "100%",
		onClosed : ReloadAll
	});
	$(".LightBoxWIF").colorbox({
		iframe : true,
		overlayClose : false,
		width : "1060px",
		height : "100%",
		onClosed : OnCloseError
	});
	$(".lightboxIF").colorbox({
		iframe : true,
		overlayClose : false,
		width : "650px",
		height : "410px",
		onClosed : OnCloseError
	});
}catch(e){gcl(e.message);}

}
function cl(str){
	try{
		console.log(str);
	}catch(e){
		alert(str);
	}
}

function SetTable(){
	$(".GSTable thead tr:first td:first,.GSTable tfoot tr:first td:first").addClass("FirstCell");
	$(".GSTable thead tr:last td:last,.GSTable tfoot tr:last td:last").addClass("LastCell");
	$(".GSTable:not(.noformat) tbody tr:odd").removeClass("even").addClass("odd");
	$(".GSTable:not(.noformat)  tbody tr:even").removeClass("odd").addClass("even");
	$(".GSTable thead tr:first td:first,.GSTable tfoot tr:first td:first").each(function(){
		if($(this).hasClass("FirstCell") && $(this).hasClass("LastCell")){
			$(this).removeClass("FirstCell").removeClass("LastCell").addClass("BothCell");
		}
	});
	$(".GSTable tfoot tr:first td:first,.GSTable tfoot tr:first td:first").each(function(){
		if($(this).hasClass("FirstCell") && $(this).hasClass("LastCell")){
			$(this).removeClass("FirstCell").removeClass("LastCell").addClass("BothCell");
		}
	});
	try{
		SetDatePicker();
        SetReportDatePicker();
        // SetReportDateTimePicker()
	}catch(e){ gcl(e.message,true);}
}
function ReloadSiteUrl(){
	location.reload(true);
}
function CallMyAjax(url,data,beforeSend,Success,JSONData,Complete,IsMsgBox,MsgContainer){
	if(!beforeSend){
		beforeSend=function(){};
	}
	if(!Success){
		Success=function(){};
	}
	if(typeof(JSONData)=="undefined"){
		JSONData=true;
	}

	$.ajax({
		url : url,              
        data : data,                   
        type : "POST", 
        scriptCharset: "utf-8",
        dataType : JSONData?"json":"html",
		beforeSend: function() {
			beforeSend();
	    },		   
	    success: function(rdata){	    	
	    	if(JSONData){
	    		
	    			//RequestCompleted(rdata,IsMsgBox,MsgContainer);
	    		
	    	};
	    	Success(rdata);
	    },  
	    complete:function(jqXHR, textStatus){
	    	if(typeof(Complete)!="undefined"){
	    		Complete(jqXHR,textStatus);
	    	}
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
}
//converts number to (xxx)xxx-xxxx or xxx-xxx-xxxx
function formatPhoneNumber(cellvalue, options, rowObject) {	
	try{
	 var re = new RegExp("([0-9]{3})([0-9]{3})([0-9]{3,6})", "g");
	 cellvalue=cellvalue.replace(re, "($1) $2-$3");
	}catch(e){ gcl(e.message,true);}
    return cellvalue;
}
function GetData(){
	
}

$(function(){
	$(document).on("click",".showhidebtn",function(){
		if($(".DMHeader").hasClass("hide")){
			$.removeCookie("headerhide");
			$(".showhidebtn").removeClass("clickedt");
			$(".DMBody").animate({marginTop:0},300,function(){	
				$(".DMHeader").slideDown("slow",function(){					
					$(".DMHeader").removeClass("hide");
					$(".onHideHeader").fadeOut('show');
				});
			});
		}else{	
			$(".showhidebtn").addClass("clickedt");
				$(".DMBody").animate({marginTop:20},300,function(){	
				$(".DMHeader").slideUp("slow",function(){
					$.cookie("headerhide",1);
					$(".onHideHeader").fadeIn('show');
					$(".DMHeader").addClass("hide");				
				});
			});
			
		}
	});
	$(document).on("click",".showhideleftbtn",function(){	
		if($(".DMContainer .DMBody .LeftMenu").hasClass("hide")){
			$(".showhideleftbtn").removeClass("clickedl");
			$.removeCookie("lefthide");
			$(".DMContainer .DMBody .ContentBody").animate({marginLeft:200},500,function(){					
				
			});
			$(".DMContainer .DMBody .LeftMenu").animate({marginLeft:0},500,function(){
				$(this).removeClass("hide");
				ResizeAll();
			});
		}else{	
			$(".showhideleftbtn").addClass("clickedl");
				$(".DMContainer .DMBody .LeftMenu").animate({marginLeft:-200},500,function(){
					$.cookie("lefthide",1);
					ResizeAll();
					$(this).addClass("hide");
				});
				$(".DMContainer .DMBody .ContentBody").animate({marginLeft:20},500,function(){					
					
				});
			
		}
	});
	
	
	
	$(".clps .menulist").css("display","none");	
	$(document).on("click",".MenuGroup .title",function(){
		var title =$(this);
		var menugroup=$(this).parent();
		var menulist=$(this).parent().find('.menulist');
		if(menugroup.hasClass("clps")){
			menugroup.removeClass("clps");
			menulist.slideDown('slow',function(){
				$.removeCookie(menugroup.attr("id"));
			});
			
		}else{
			menulist.slideUp('slow',function(){
				menugroup.addClass("clps");
				$.cookie(menugroup.attr("id"), 1);
			});	
		}
	});
	
	SetFullScreen();
	
	$("body").on("click",".remove-parents",function(e){
		e.preventDefault();
		var thisobj=$(this);
		var con=confirm("Are you sure?");		
		if(con){
			$(this).parent().animate({
				opacity:0
			},500,function(){
				$(this).remove();
			});
		}
	});
	
});

function SetiCheckBox(selector){
	$(selector).iCheck({
        checkboxClass: 'icheckbox_square-greed',
        radioClass: 'iradio_square-green'
    });
}
function SetFromValidation(){
	$('.bv-form').each(function(){
		var  submitHandler=$(this).data("submit-handler");
		if(submitHandler){
			submitHandler=eval(submitHandler);
		}
		$(this).bootstrapValidator({
		submitHandler:submitHandler,
		excluded: ':disabled,:hidden',
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'fa fa-check',
            invalid: 'fa fa-times',
            validating: 'fa fa-refresh'
        },fields: {
        	cc_expdate: {
                validators: {
                    callback: {
                        message: 'Invalid MMYY',
                        callback: function(value, validator) {
                            var m = new moment(value, 'MMYY', true);                           
                            if (!m.isValid()) {
                                return false;
                            }
                           var m2=moment();
                            // US independence day is July 4
                            return m>m2;
                        }
                    }
                }
            },
        "step[3][cc_expdate]": {
                validators: {
                    callback: {
                        message: 'Invalid MMYY',
                        callback: function(value, validator) {
                            var m = new moment(value, 'MMYY', true);                           
                            if (!m.isValid()) {
                                return false;
                            }
                           var m2=moment();
                            // US independence day is July 4
                            return m>m2;
                        }
                    }
                }
            }
        }
    }).find('.cbox-control')
    // Init iCheck elements
    .iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green'
    })
    // Called when the radios/checkboxes are changed
    .on('ifChanged', function(e) {
        // Get the field name
    	try{
        var field = $(this).attr('name');
        var fromobj=$(this).closest("form");
        fromobj    
        // Mark the field as not validated
            .bootstrapValidator('updateStatus', field, 'NOT_VALIDATED')
            // Validate field
            .bootstrapValidator('validateField', field);
    	}catch(e){}
    });
	});
}


function printPDF(pdfUrl)
{
	var w = window.open(pdfUrl);
	w.print();
	w.close();
}
function ReloadIfAjaxWindowChanged(){
	if(MyAjaxChange){
		ReloadSiteUrl();
		MyAjaxChange=false;
	}	
}
function SetFullScreen(){
	try{
	$(".full-screen").prepend('<a type="button" href="#" class="full-screen-btn btn-xs btn"><i class="fa"></i></a>');
	$("body").on("click",".full-screen-btn",function(e){
		e.preventDefault();		
		$("body").toggleClass("full-screen-body");
		
	});
	}catch(e){
		gcl(e.message);
	}
}
function GetTimeSpendDate(date1,date2) {
    var diff = Math.floor(date1.getTime() - date2.getTime());
    var secs = Math.floor(diff/1000);
    var mins = Math.floor(secs/60);
    var hours = Math.floor(mins/60);
    var days = Math.floor(hours/24);
    var months = Math.floor(days/31);
    var years = Math.floor(months/12);
    months=Math.floor(months%12);
    days = Math.floor(days%31);
    hours = Math.floor(hours%24);
    mins = Math.floor(mins%60);
    secs = Math.floor(secs%60); 
    var message = ""; 
    if(days<=0){
    message += secs + " sec "; 
    message += mins + " min "; 
    message += hours + " hours "; 
    }else{
        message += days + " days "; 
        if(months>0 || years>0){
            message += months + " months ";
        }
        if(years>0){
            message += years + " years ago";    
        }
    }
    return message
}

function getCookie(w){
	cName = "";
	pCOOKIES = new Array();
	pCOOKIES = document.cookie.split('; ');
	for(bb = 0; bb < pCOOKIES.length; bb++){
		NmeVal  = new Array();
		NmeVal  = pCOOKIES[bb].split('=');
		if(NmeVal[0] == w){
			cName = unescape(NmeVal[1]);
		}
	}
	return cName;
}

function printCookies(w){
	cStr = "";
	pCOOKIES = new Array();
	pCOOKIES = document.cookie.split('; ');
	for(bb = 0; bb < pCOOKIES.length; bb++){
		NmeVal  = new Array();
		NmeVal  = pCOOKIES[bb].split('=');
		if(NmeVal[0]){
			cStr += NmeVal[0] + '=' + unescape(NmeVal[1]) + '; ';
		}
	}
	return cStr;
}

function setCookie(name, value, expires, path, domain, secure){
	cookieStr = name + "=" + escape(value) + "; ";
	
	if(expires){
		expires = setExpiration(expires);
		cookieStr += "expires=" + expires + "; ";
	}
	if(path){
		cookieStr += "path=" + path + "; ";
	}
	if(domain){
		cookieStr += "domain=" + domain + "; ";
	}
	if(secure){
		cookieStr += "secure; ";
	}
	
	document.cookie = cookieStr;
}
function detCookie(name) {
    document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
};

function setExpiration(cookieLife){
    var today = new Date();
    var expr = new Date(today.getTime() + cookieLife * 24 * 60 * 60 * 1000);
    return  expr.toGMTString();
}
function showErrorMessage(msg) {
    Messenger({
        extraClasses: 'messenger-fixed messenger-on-right messenger-on-bottom',
        theme: 'flat'
    }).post({
        message: msg,
        type: 'error',
        showCloseButton: true
    });
}

function progressMessage() {
    var i = 0;
    Messenger({
        extraClasses: 'messenger-fixed messenger-on-right messenger-on-bottom',
        theme: 'flat'
    }).run({
        errorMessage: 'Error destroying alien planet. Retrying...',
        successMessage: 'Alien planet destroyed!',
        action: function(opts) {
            if (++i < 2) {
                return opts.error({
                    status: 500,
                    readyState: 0,
                    responseText: 0
                });
            } else {
                return opts.success();
            }
        }
    });
}

function showSuccess(msg) {
    Messenger({
        extraClasses: 'messenger-fixed messenger-on-right messenger-on-bottom',
        theme: 'flat'
    }).post( {message: msg,           
            showCloseButton: true});
}

function OpenInNewTab(url) {
	  var win = window.open(url, '_blank');
	  //win.focus();
}
var popid=0;
var polwin=new Array();
function SetPOPUPWindow(){
	
	$("body").on("click",".p-up",function(e){		
		e.preventDefault();
		
		if($(this).attr('data-pid')==null){
			$(this).attr('data-pid',popid++);			
		}
		var wpid=$(this).attr('data-pid');
		var URL=$(this).attr("href");
		var name=$(this).text();
		OpenInNewWindow(URL,name,wpid);
	});
	
	$("body").on("click",".cw-p-up",function(e){		
		e.preventDefault();
		var URL=$(this).attr("href");

		openCallController(URL);
	});
}
function popupWindow(URL){
	OpenInNewWindow(URL);
}
function OpenInNewWindow(URL,name,ppid){	
	if(polwin[ppid]==null || polwin[ppid].closed){
		polwin[ppid]=window.open(URL,name,"toolbar=no,titlebar=no, height="+screen.height+",  width="+screen.width+", scrollbars=yes, resizable=yes,fullscreen=yes");
	}
	polwin[ppid].focus();
}

function openCallController(URL){
	var newWin = window.open("", "child_window_name", "toolbar=no,titlebar=no, height="+screen.height+",  width="+screen.width+", scrollbars=yes, resizable=yes,fullscreen=yes");
	if (newWin.location.href === "about:blank") {
		newWin = window.open(URL, "child_window_name", "toolbar=no,titlebar=no, height="+screen.height+",  width="+screen.width+", scrollbars=yes, resizable=yes,fullscreen=yes");
	} else {
		// We've already obtained the reference.
		// However, IE and FireFox won't put focus on an already opened window.
		// So we have to do that explicitly:
		newWin.focus();
	}	
}

function focusCallController(){
	console.log("call controller refresh");
	$(".cw-p-up").click();
	/*
	var newWin = window.open("", "child_window_name", "toolbar=no,titlebar=no, height="+screen.height+",  width="+screen.width+", scrollbars=yes, resizable=yes,fullscreen=yes");
	if (newWin.location.href === "about:blank") {
		
	} else {
		newWin.focus();
	}*/
}

function toggleFullScreen(elem) {
    // ## The below if statement seems to work better ## if ((document.fullScreenElement && document.fullScreenElement !== null) || (document.msfullscreenElement && document.msfullscreenElement !== null) || (!document.mozFullScreen && !document.webkitIsFullScreen)) {
    if ((document.fullScreenElement !== undefined && document.fullScreenElement === null) || (document.msFullscreenElement !== undefined && document.msFullscreenElement === null) || (document.mozFullScreen !== undefined && !document.mozFullScreen) || (document.webkitIsFullScreen !== undefined && !document.webkitIsFullScreen)) {
        if (elem.requestFullScreen) {
            elem.requestFullScreen();
        } else if (elem.mozRequestFullScreen) {
            elem.mozRequestFullScreen();
        } else if (elem.webkitRequestFullScreen) {
            elem.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
        } else if (elem.msRequestFullscreen) {
            elem.msRequestFullscreen();
        }
    } else {
        if (document.cancelFullScreen) {
            document.cancelFullScreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitCancelFullScreen) {
            document.webkitCancelFullScreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }
}

function openDashboardWindow(URL) {
	var dt = new Date();
	var cur_ts = Math.floor(dt.getTime()/1000);
	
	var old_ts = getCookie("isDashBoardAlreadyOpen");
	
	var diff = 20;
	if(old_ts!=null && old_ts!="" && old_ts.length!=0) {
		//var date = new Date(parseInt(old_ts));
		//date.setSeconds(date.getSeconds() + 1);
		//old_ts = Math.floor(date.getTime());
		old_ts = parseInt(old_ts);
		
		//var diff = Math.abs(old_ts-cur_ts);
		diff = cur_ts-old_ts;
	}

	var popupName = URL.indexOf('dialer') !== -1 ? 'dialer' : 'dashboard';

    //console.log(diff);
    window.open(URL,popupName,"toolbar=no,titlebar=no, height="+screen.height+",  width="+screen.width+", scrollbars=yes, resizable=yes,fullscreen=yes");
  /*  if(diff>15) {
		window.open(URL,popupName,"toolbar=no,titlebar=no, height="+screen.height+",  width="+screen.width+", scrollbars=yes, resizable=yes,fullscreen=yes");
	} else {
		alert("Dashboard already openned");
	} */
}

function SetReportDatePicker(){
    try{
        $(".gs-report-date-picker:not(.added-picker),.date-pick:not(.added-picker)").datetimepicker({
            pickTime: false,
            timepicker:false,
            useStrict:true,
            format:"d/m/Y"
        });
        $(".gs-report-date-picker:not(.added-picker)").addClass("added-picker");
        (".gs-report-date-picker:not(.added-picker)").datetimepicker({
            pickTime: false,
            timepicker:false,
            useStrict:true,
            format:"d/m/Y"
        });
        $(".gs-report-date-picker:not(.added-picker)").addClass("added-picker");
    }catch(e){
        gcl(e.message,true);
    }
}
function SetReportDateTimePicker(){
    try{
        //class for report date time--------------

        $(".gs-report-datetime-from-picker-grid-options input").each(function(e){
            if(!$(this).hasClass("addedDate")){
                $(this).addClass("addedDate");
                $(this).datetimepicker({
                    pickTime: true,
                    timepicker:true,
                    useStrict:true,
                    defaultTime:'00:00',
                    format:"d/m/Y H:00"
                    //,allowTimes:['00:00', '01:00', '02:00','03:00', '04:00', '05:00', '06:00', '07:00','08:00', '09:00', '10:00','11:00', '12:00', '13:00', '14:00', '15:00','16:00', '17:00','18:00', '19:00', '20:00', '22:00', '23:00']
                });
            }
        });
        $(".gs-report-datetime-to-picker-grid-options input").each(function(e){
            if(!$(this).hasClass("addedDate")){
                $(this).addClass("addedDate");
                $(this).datetimepicker({
                    pickTime: true,
                    timepicker:true,
                    useStrict:true,
                    defaultTime:'23:00',
                    format:"d/m/Y H:59",
                    allowTimes:['00:59', '01:59', '02:59','03:59', '04:59', '05:59', '06:59', '07:59','08:59', '09:59', '10:59','11:59', '12:59', '13:59', '14:59', '15:59','16:59', '17:59','18:59', '19:59', '20:59', '22:59', '23:59']
                });
            }
        });
        //----------------

    }catch(e){
        gcl(e.message,true);
    }
}
function SetNewReportDateTimePicker(format, selector_prefix='.gs-report'){
	var current_date = new Date();
    var start_default_date = '';
    var end_default_date = '';
    date_format = format;

    if(date_format == 'd/m/Y'){
        var month = +current_date.getMonth()+1;
        month = ((''+month).length==1 ? '0'+month : month);
        var day = current_date.getDate();
        day = ((''+day).length==1 ? '0'+day : day);
        start_default_date = day+'/'+month+'/'+current_date.getFullYear();

        current_date.setDate(current_date.getDate() + 1);
        month = +current_date.getMonth()+1;
        month = ((''+month).length==1 ? '0'+month : month); 
        day = current_date.getDate();
        day = ((''+day).length==1 ? '0'+day : day);
        end_default_date = day+'/'+month+'/'+current_date.getFullYear();
    }else if(date_format == 'm/d/Y'){
        var month = +current_date.getMonth()+1;
        month = ((''+month).length==1 ? '0'+month : month);
        var day = current_date.getDate();      
        start_default_date = month+'/'+day+'/'+current_date.getFullYear();
        
        current_date.setDate(current_date.getDate() + 1);
        month = +current_date.getMonth()+1;
        month = ((''+month).length==1 ? '0'+month : month); 
        day = current_date.getDate();
        day = ((''+day).length==1 ? '0'+day : day);
        end_default_date = month+'/'+day+'/'+current_date.getFullYear();
    }
	// console.log(format);
	// console.log(start_default_date);
	// console.log(end_default_date);

    try{
        //class for report date time--------------
        $(selector_prefix+"-datetime-from-picker-grid-options input").each(function(e){
            if(!$(this).hasClass("addedDate"))
                $(this).addClass("addedDate");

            $(this).datetimepicker({
                pickTime: true,
                timepicker:true,
                useStrict:true,
                defaultTime:'00:00',
                format: format+" H:00"
            });
            $(this).val(start_default_date+' 00:00');
        });
        $(selector_prefix+"-datetime-to-picker-grid-options input").each(function(e){
            if(!$(this).hasClass("addedDate"))
                $(this).addClass("addedDate");
            
            $(this).datetimepicker({
                pickTime: true,
                timepicker:true,
                useStrict:true,
                defaultTime:'00:00',
                format: format+" H:00",
                // allowTimes:['00:00', '01:00', '02:00','03:00', '04:00', '05:00', '06:00', '07:00','08:00', '09:00', '10:00','11:00', '12:00', '13:00', '14:00', '15:00','16:00', '17:00','18:00', '19:00', '20:00', '21:00', '22:00', '23:00']
            });
            $(this).val(end_default_date+' 00:00');
        });
        //----------------
    }catch(e){
        gcl(e.message,true);
    }
}

function SetNewReportDatePicker(format){
	var current_date = new Date();
    var start_default_date = '';
    var end_default_date = '';
    date_format = format;

    if(date_format == 'd/m/Y'){
        var month = +current_date.getMonth()+1;
        month = ((''+month).length==1 ? '0'+month : month);
        var day = current_date.getDate();
        day = ((''+day).length==1 ? '0'+day : day);
        start_default_date = day+'/'+month+'/'+current_date.getFullYear();

        current_date.setDate(current_date.getDate() + 1);
        month = +current_date.getMonth()+1;
        month = ((''+month).length==1 ? '0'+month : month); 
        day = current_date.getDate();
        day = ((''+day).length==1 ? '0'+day : day);
        end_default_date = day+'/'+month+'/'+current_date.getFullYear();
    }else if(date_format == 'm/d/Y'){
        var month = +current_date.getMonth()+1;
        month = ((''+month).length==1 ? '0'+month : month);
        var day = current_date.getDate();      
        start_default_date = month+'/'+day+'/'+current_date.getFullYear();
        
        current_date.setDate(current_date.getDate() + 1);
        month = +current_date.getMonth()+1;
        month = ((''+month).length==1 ? '0'+month : month); 
        day = current_date.getDate();
        day = ((''+day).length==1 ? '0'+day : day);
        end_default_date = month+'/'+day+'/'+current_date.getFullYear();
    }
	// console.log(format);
	// console.log(start_default_date);
	// console.log(end_default_date);

    try{
        //class for report date time--------------
        $(".gs-date-from-picker-grid-options input").each(function(e){
            if(!$(this).hasClass("addedDate"))
                $(this).addClass("addedDate");

            $(this).datetimepicker({
                pickTime: false,
                timepicker:false,
                useStrict:true,
                format: format
            });
            $(this).val(start_default_date);
        });
        $(".gs-date-to-picker-grid-options input").each(function(e){
            if(!$(this).hasClass("addedDate"))
                $(this).addClass("addedDate");
            
            $(this).datetimepicker({
                pickTime: false,
                timepicker:false,
                useStrict:true,
                format: format
            });
            $(this).val(end_default_date);
        });
        //----------------
    }catch(e){
        gcl(e.message,true);
    }
}

function elementAddMore(add_more_id, parent_id, child_id, child_class, option_field_name, elm_field_arr, delete_item_class){
	// console.log(add_more_id);
    // console.log(parent_id);
    // console.log(child_id); 
    // console.log(child_class); 

	$('#'+add_more_id).on('click', function(e){
        var cloneTr = $('#'+parent_id+' .'+child_class+':last-child').clone();
        var cloneCount = cloneTr.attr('data-elm-num');
        var newCount = +cloneCount +1; 
        // console.log(cloneTr);
        // console.log(cloneCount);
        // console.log(newCount);           
        
        $('#'+parent_id).append(cloneTr);
        
        // change count value element item
        cloneTr.attr('id', child_id+'_'+newCount);
        cloneTr.attr('data-elm-num',newCount);
        
        //set element name&id
        for (var i = 0; i < elm_field_arr.length; i++) {
        	cloneTr.find('#'+elm_field_arr[i]+'_'+cloneCount).attr('id', elm_field_arr[i]+'_'+newCount);
        	cloneTr.find('#'+elm_field_arr[i]+'_'+newCount).attr('name', option_field_name+'['+newCount+']['+elm_field_arr[i]+']');
        	cloneTr.find('#'+elm_field_arr[i]+'_'+newCount).val('');
        }        
        // remove button show
        cloneTr.find(delete_item_class).removeClass('hide');
        
        removeMoreOptios(delete_item_class, child_id+'_'+newCount);            
    });
}

function removeMoreOptios(delete_item_class){
	// console.log(delete_item_class);
    // console.log(elm_child_id);

	$(delete_item_class).on('click', function(e){
		// console.log($(this).parents('#'+elm_child_id));
        $(this).parents('.from-elm').remove();
    });
}