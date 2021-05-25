<script src="js/jquery.watermark.min.js" type="text/javascript"></script>
<script src="js/jquery.clockpick.1.2.2.pack.js" type="text/javascript"></script>
<script src="js/jquery.datePicker.js" type="text/javascript"></script>
<script src="js/date.js" type="text/javascript"></script>
<script src="js/validation/jquery.validate.min.js" type="text/javascript"></script>

<link rel="stylesheet" href="css/report.css" type="text/css">
<link rel="stylesheet" href="css/form.css" type="text/css">
<link rel="stylesheet" href="css/clockpick.css" type="text/css">
<link rel="stylesheet" href="css/crm.in.css" type="text/css">
<style type="text/css">
.w-100{  width: 100%;}
#frm_search .flt {
    margin:5px 0px;
}
td.cntr {
    text-align:center;
}
.container {
	margin-left: 0;
    margin-right: 0;
    padding-left: 0;
    padding-right: 0;
	width: 100%;
}
.nav-tabs > li {
    position:relative;
}
.nav-tabs > li > a {
    display:inline-block;
}
.nav-tabs > li > span {
    color: #bc3131;
    cursor: pointer;
    display: none;
    position: absolute;
    right: 7px;
    top: 5px;
}
.nav-tabs > li:hover > span {
    display: inline-block;
}
table {
    font-family: Tahoma,Verdana,Segoe,sans-serif;
	font-size: 13px;
}
.profile-details td, .personal-val, .address-val, .contact-val {
    font-size: 13px;
}
.tab-url{
	text-decoration: none;
	color: #6288b5;
}
label, input, button, select, textarea {
    font-size: 13px;
    font-weight: normal;
    line-height: 20px;
}
.btn {
    line-height: 1;
    padding: 7px 14px !important;
}
.nav > li > a {
    padding: 6px 15px 6px 10px;
}
.lft-search-btn{
	border: 1px solid #3b9dd2;
    color: #ffffff;
	border-radius: 3px !important;
	background: #3dbce2;
    background: -moz-linear-gradient(top,  #3dbce2 0%, #0599c7 100%);
    background: -webkit-linear-gradient(top,  #3dbce2 0%,#0599c7 100%);
    background: linear-gradient(to bottom,  #3dbce2 0%,#0599c7 100%);
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#3dbce2', endColorstr='#0599c7',GradientType=0 );
}
.lft-search-btn:hover{
	border: 1px solid #23b7e5;
    color: #ffffff;
    background: #23b7e5; /* Old browsers */
    background: -moz-linear-gradient(top,  #23b7e5 0%, #0fb4e7 100%); /* FF3.6-15 */
    background: -webkit-linear-gradient(top,  #23b7e5 0%,#0fb4e7 100%); /* Chrome10-25,Safari5.1-6 */
    background: linear-gradient(to bottom,  #23b7e5 0%,#0fb4e7 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#23b7e5', endColorstr='#0fb4e7',GradientType=0 ); /* IE6-9 */
}
.btn-sp-search{
	padding: 5px 10px !important;
}
.contact-tab-sp{
	display: none;
}
input[type="checkbox"], input[type="radio"] {
	margin: 0;
}
.m-t-12{margin-top: 12px}
</style>
<?php //var_dump($callbacks);
$num_select_box = 6;
function uniqueIdGenerate($idPrefix=''){
    $globalTime = mt_rand(101, 9999);
    $myUniqueId = $idPrefix.'_'.$globalTime;
    
    return $myUniqueId;
}
?>

<?php if (empty($errMsg)) {?>
<script type="text/javascript">
var isErrMsgShowed = false;
var openTabArray = new Array();
var txt_edit = '<i class="fa fa-pencil-square-o"></i> edit';
var txt_save = '<i class="fa fa-floppy-o"></i> save';
var currentActCallId = "<?php if (!empty($crm_info->callid)) echo $crm_info->callid;?>";
var currentFulCallId = "<?php if (!empty($crm_info->call_id_full)) echo $crm_info->call_id_full;?>";

$(document).ready(function() {
	var chatarea = $(".page-chatapi");
    var chatwindow = $(".chatapi-windows");
    var topbar = $(".page-topbar");
    var mainarea = $("#main-content");
    var menuarea = $(".page-sidebar");

    menuarea.addClass("collapseit").removeClass("expandit").removeClass("chat_shift");
    topbar.addClass("sidebar_shift").removeClass("chat_shift");
    mainarea.addClass("sidebar_shift").removeClass("chat_shift");
    topbar.addClass("force-collpse");
    
	Date.format = 'yyyy-mm-dd';
	$('.date-pick').datePicker({clickInput:true,createButton:false,startDate:'2000-01-01'})
	$("#clk_time").clockpick({starthour: 0, endhour: 23, minutedivisions:12,military:true});
	$('#clk_date').datePicker({clickInput:true,createButton:false,startDate:'<?php echo date("Y-m-d");?>'});
	$('#clk_date').watermark('Date');
	$('#clk_time').watermark('Time');

	$( "#form_cinfo" ).validate({
		rules: {
			home_phone: {
				digits: true
			},
			office_phone: {
				digits: true
			},
			mobile_phone: {
				digits: true
			},
			other_phone: {
				digits: true
			},
			fax: {
				digits: true
			}, 
			email: {
				email: true
			}
		}
	});

	infoEditorProcess();
	load_sels();
	
	$("#set_disposition").click(function (e) {
		e.stopPropagation();
		var disposition = $("#disposition").val();
        var dial_time = $("#call_back_time").val();
        var agent_id = "O";
        var save_callback_request = "N";
        var open_crm_ticket = "N";
        //var alternative_disposition = $("#alternative_disposition").val();
        var alternative_disposition = '';
        var template_id = "<?php echo $crm_info->template_id; ?>";
        var last_name = $("#last-name").val();
        var account_id = $("#account-id").val();
        var category_id = $("#category-id").val();
        var served_account = $("#served_account_number").val();
        //var category_id = '';
        //console.log(disposition);

        if ( $("#agent_id").is(':checked')){
            var agent_id = "M";
        }

        if ( $("#callback-request").is(':checked')){
            save_callback_request = "Y";
        }

        if ( $("#crm-ticket").is(':checked')){
            open_crm_ticket = "Y";
        }
        var skill_id = '<?php echo $crm_info->skill_name; ?>';
        var number = '<?php echo $crm_info->caller_id; ?>';
		if (disposition.length <= 0 && alternative_disposition.length <= 0) {
            alert('Select disposition !!');
            return;
        }
		
		$.ajax({
			type: "POST",
            dataType: "JSON",
			url: "<?php echo $this->url('task='.$request->getControllerName()."&act=savedisposition&record_id=".$crm_info->crm_record_id);?>",
			data: { category_id:category_id, last_name : last_name, account_id : account_id,
                open_crm_ticket: open_crm_ticket, save_callback_request: save_callback_request,
                dial_time: dial_time, number:number, skill_id:skill_id,agent_id:agent_id,
                disposition: disposition, served_account: served_account,
                callid: '<?php if (!empty($crm_info->callid)) echo $crm_info->callid;?>',
                note: $("#comment").val(), alternative_disposition: alternative_disposition,
                template_id:template_id
                },
				beforeSend: function ( xhr ) {
					$("#fl_msg_saving").show();
					$("#set_disposition").attr('value', 'Please Wait ..');
				}
			}).done(function( msg ) {
			    if (typeof msg === 'object'){
			        if(msg.type){
                        $("#fl_msg_success").html(msg.message);
                        msg = 1;
                    }else {
                        $("#fl_msg_err").html(msg.message);
                        msg = -1;
                    }
                }
				$("#fl_msg_saving").hide();
				if (msg > 0 ) {
					$("#fl_msg_success").show();
					$("#fl_msg_success").fadeOut(2000, function () {
						try {
		    					parent.DispositionSaved(currentFulCallId);
	    					} catch (ex) {
		    					console.log("Error: " + ex.message);
	    					}
						$("#set_disposition").attr('value', 'Save');
						//window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&act=details&param=".$param);?>";
					});
				} else {
					$("#fl_msg_err").show();
					$("#fl_msg_err").fadeOut(2000, function () {
					        $("#set_disposition").attr('value', 'Save');
						//window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&act=details&param=".$param);?>";
					});
				}
		});

	});

	$(document).on('change',"#crm-ticket", function (e) {
        if ( $(this).is(':checked')){
            $("#note-label").html('Description:');
            $("#subject-container, .name-account-container").removeClass('hide');
        }else{
            $("#note-label").html('Note:');
            $("#subject-container, .name-account-container").addClass('hide');
        }
    });

	$(document).on('change',"#callback-request", function (e) {
        if ( $(this).is(':checked')){
            $("#callback-request-input-container").removeClass('hide disabled');
            $("#callback-request-input-container").removeClass('disabled');
        }else{
            $("#callback-request-input-container").addClass('hide disabled');
            $("#callback-request-input-container").addClass('disabled');
        }
    });

	$(document).on('change',"#call_back_time", function (e) {
        var selected_date = $(this).val();
        selected_date = selected_date.split(' ');
        var date_part = selected_date[0].split('-');
        var time_part = selected_date[1].split(':');
        var start = Date.now();
        var end = new Date(date_part[0], date_part[1]-1, date_part[2], time_part[0], time_part[1], time_part[2]);

        var ms = Math.abs(end - start);
        var d, h, m, s;
        s = Math.floor(ms / 1000);
        m = Math.floor(s / 60);
        s = s % 60;
        h = Math.floor(m / 60);
        m = m % 60;
        d = Math.floor(h / 24);
        h = h % 24;
        var difference = d+ " day(s) "+ h + " hour(s) " + m + " minute(s) from now";
        $("#time-span").html(difference);
    });


	$(".del-callback-number").click(function (e) {
		e.stopPropagation();
		e.preventDefault();
		$thisobj = $(this);
		var url = $(this).attr('href');
        if (url.length > 0 && url.length != "undefined") {

		$.ajax({
			type: "POST",
			url: url,
			data: {  },
			}).done(function( msg ) {
				if (msg > 0) {
					$("#fl_msg_del").show();
                    $thisobj.parent().parent().hide("slow",function () {
                        $("#fl_msg_del").hide();
                    });
				}else {
					$("#fl_msg_err").show();
					$("#fl_msg_err").fadeOut(2000, function () {
					});
				}
		});
		}
	});
	
	
	$( "#frm_search" ).submit(function( event ) {
		try {
		    if(!parent.AgentInService(currentActCallId)){
		        alert("Agent not in service");
		        return false;
		        event.preventDefault();
		    }
		} catch (ex) {
			console.log("Error: " + ex.message);
		}
		
		var data = {};
		$( "#frm_search .flt").each(function( index ) {
			data[$( this ).attr('name')] = $( this ).val();
		});
		data['template_id'] = '<?php if (!empty($crm_info->template_id)) echo $crm_info->template_id;?>';
		//console.log(data);
		//return false;
		//if (accid.length > 0) {
			$.post("<?php echo $this->url('task='.$request->getControllerName().'&act=search');?>", 
				{callid:'<?php if ($crm_info->callid) echo $crm_info->callid;?>', data:data})
			.done(function( resp ) {
				if (resp == '200|OK') {
					window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&act=details&param=".$param);?>";
				} else {
					alert(resp.substring(4));
					//return false;
				}
			});
		//}
		return false;
		event.preventDefault();
	});
	
	$( "#btn_auth" ).click(function( event ) {
		/*var agentInService = false;
		try {
			if (parent.AgentInService(currentActCallId)) {
				agentInService = true;
			}
		} catch (ex) {
			console.log("Error: " + ex.message);
		}
		if (!agentInService) {
			alert("Agent not in service");
			return false;
			event.preventDefault();
		}
		try {
			if (!parent.AgentInService(currentActCallId)) {
				alert("Agent not in service");
				return false;
				event.preventDefault();
			}
		} catch (ex) {
			console.log("Error: " + ex.message);
		}*/
		
		var accid = '<?php echo !empty($crm_info->account_id) ? $crm_info->account_id : "";?>';
		var callid = '<?php echo !empty($crm_info->callid) ? $crm_info->callid : "";?>';
        var caller_id = '<?php echo !empty($crm_info->caller_id) ? $crm_info->caller_id : "";?>';
		var record_id = '<?php echo !empty($crm_info->crm_record_id) ? $crm_info->crm_record_id : '';?>';
		var all_accids = '<?php echo !empty($crm_info->account_id_all) ? $crm_info->account_id_all : '';?>';
		if(record_id == '' || record_id == null){
			record_id = '<?php echo !empty($crm_info->data_record_id) ? $crm_info->data_record_id : "";?>';
		}
		//var record_id = '<?php echo !empty($crm_info->data_record_id) ? $crm_info->data_record_id : "";?>';

		if (accid.length > 0 && callid.length > 0) {
			if (confirm('Caller is verified?')) {
				$.post("<?php echo $this->url('task='.$request->getControllerName().'&act=verified');?>", 
					{callid:callid, accountid:accid, caller:caller_id, crm_record_id:record_id, all_ids:all_accids})
				.done(function( resp ) {
					if (resp == '200|OK') {
						try {
		    				parent.AuthenticateUser();
						} catch (ex) {
							console.log("Error: " + ex.message);
						}
						window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&act=details&param=".$param);?>";
					} else {
						alert(resp.substring(4));
					}
				});
			}
		}
		event.preventDefault();
	});

	$(".nav-tabs").on("click", "a", function (e) {
        e.preventDefault();
        if (!$(this).hasClass('add-contact')) {
            $(this).tab('show');
        }
    }).on("click", "span", function (event) {
		try {
		    if(!parent.AgentInService(currentActCallId)){
		        alert("Agent not in service");
		        return false;
		        event.preventDefault();
		    }
		} catch (ex) {
			console.log("Error: " + ex.message);
		}
		
        var anchor = $(this).siblings('a');
        var secTabId = $(anchor).attr('rel');
        $(anchor.attr('href')).remove();
        $(this).parent().remove();
        $(".nav-tabs li").children('a').first().click();

        var index = openTabArray.indexOf(secTabId);
        openTabArray.splice(index, 1);
    });

    /*$('.contact-val').on("click", "a.add-contact", function (e) {
        e.preventDefault();
        var id = $(".nav-tabs").children().length + 1;
        var tabId = 'contact_' + id;
        var tabTitle = $(this).text();
        var secTabId = $(this).attr('rel');
        var uniqueId = $(this).attr('unique_id');
        var tabArrLength = openTabArray.length;console.log(uniqueId);
        if(tabArrLength > 0){
        	if($.inArray(uniqueId, openTabArray) >= 0 ){
        		$("ul.nav-tabs").find("[rel='"+uniqueId+"']").click();
            	return false;
        	}
        }

        $.ajax({
    		type: "POST",
    		url: "<?php //echo $this->url('task='.$request->getControllerName()."&act=tab-details&param=".$param);?>",
    		dataType: "json",
    		data: {tabid:secTabId, tabDefaultVal:tabTitle},
    			beforeSend: function ( xhr ) {
    				//$("#fl_msg_saving").show();
    			}
    		}).done(function( tabData ) {
        		if(typeof tabData === 'undefined' || tabData == "" || tabData == null){
            		console.log('Data Error');
        		}else if(typeof tabData['error'] !== 'undefined' && tabData['error'] != "" && tabData['error'] != null){
            		showHideErrMsg('Y', 'Y', 'No section data found!!');
            	}else{
            		if(tabArrLength > 0 && $.inArray(uniqueId, openTabArray) < 0){
                    	openTabArray[tabArrLength] = uniqueId;
                    }else{
                    	openTabArray[tabArrLength] = uniqueId;
                    }
            		for(var xkey in tabData){
                		if(xkey == 'section'){
                    		var sectionData = tabData[xkey];
                    		var sectionHtml = processTabData(sectionData, secTabId, tabId, tabTitle);

                  			$('.nav-tabs li:last-child').after('<li><a href="#' + tabId + '" rel="' + uniqueId + '">' + tabTitle + '</a> <span><i class="fa fa-times"></i></span></li>');
                            $('.tab-content').append('<div class="tab-pane" id="' + tabId + '">' + sectionHtml + '</div>');
                            $('.nav-tabs li:nth-child(' + id + ') a').click();
                            infoEditorProcess();
                		}
          			}
        		}
 			});
    });*/

	<?php /* if ($display_disposition):?>init_disposition('<?php echo $dispositioninfo->disposition_id;?>');<?php endif; */ ?>
});

function infoEditorProcess(){
	$(".btn-info-editor").unbind("click").bind("click",function(event){
		event.stopPropagation();
		var sectionId = $(this).attr('rel');
		var curCrmRecord = $(this).attr('crm_rid');
		var thisObj = $(this);
		var val = $(this).html();
		val = val.substr(val.length - 4);

		if(typeof curCrmRecord === 'undefined' || curCrmRecord == null || curCrmRecord == ""){
			curCrmRecord = "<?php echo $crm_info->data_record_id; ?>";
		}

		if (val == 'edit') {
			$("#editable_tab_"+sectionId+" .contact-val").hide();
			$("#editable_tab_"+sectionId+" .contact-inp").show();
			$(this).html(txt_save);
		} else {
			var postedData = $('#form_cinfo_'+sectionId).serializeArray();
			var dataJson = JSON.stringify(postedData);

			$.ajax({
				type: "POST",
				url: "<?php echo $this->url("task=".$request->getControllerName()."&act=savecustinfo&cli=".$crm_info->caller_id);?>&record_id="+curCrmRecord,
				data: {jsonData : dataJson},
				beforeSend: function ( xhr ) {
					$("#fl_msg_saving").show();
				}
			}).done(function( msg ) {
				$("#fl_msg_saving").fadeOut(400);
				if (msg > 0) {
					$("#fl_msg_success").show();
					for(key in postedData){
    					var rowData = postedData[key];
    					if ($('#editable_tab_'+sectionId+' #sp_'+rowData['name']).length > 0) {
    						updatedValue = rowData['value'];
    						if ($('#tabk_url_'+rowData['name']).length > 0) {
    							$('#tabk_url_'+rowData['name']+' a.tab-url').text(updatedValue);
    							updatedValue = $('#tabk_url_'+rowData['name']).html();
    						}
    						$('#editable_tab_'+sectionId+' #sp_'+rowData['name']).html(updatedValue);
    					}
    				}
    				$("#editable_tab_"+sectionId+" .contact-val").show();
    				$("#editable_tab_"+sectionId+" .contact-inp").hide();
    				thisObj.html(txt_edit);
					$("#fl_msg_success").fadeOut(4000, function () {
						//window.location.href = "<?php echo $this->url("task=".$request->getControllerName()."&act=details".$url_cond);?>";
					});
				} else {
					$("#fl_msg_err").show();
					$("#fl_msg_err").fadeOut(4000, function () {
						//window.location.href = "<?php echo $this->url("task=".$request->getControllerName()."&act=details".$url_cond);?>";
					});
				}
				/* for(key in postedData){
					var rowData = postedData[key];
					if ($('#sp_'+rowData['name']).length > 0) {
						$('#sp_'+rowData['name']).html(rowData['value']);
					}
				}
				$("#editable_tab_"+sectionId+" .contact-val").show();
				$("#editable_tab_"+sectionId+" .contact-inp").hide();
				thisObj.html(txt_edit); */
			});
		}
	});

	$(".tab-data-refresh").unbind("click").bind("click",function(event){
		var sectionTabId = $(this).attr('rel');
		var actTabId = $(this).attr('tabid');
		var actTabTitle = $(this).attr('tabTitleTxt');
		try {
		    if(!parent.AgentInService(currentActCallId)){
		        alert("Agent not in service");
		        return false;
		        event.preventDefault();
		    }
		} catch (ex) {
			console.log("Error: " + ex.message);
		}

		$.ajax({
    		type: "POST",
    		url: "<?php echo $this->url('task='.$request->getControllerName()."&act=tab-details&param=".$param);?>",
    		dataType: "json",
    		data: {tabid:sectionTabId, tabDefaultVal:actTabTitle},
    			beforeSend: function ( xhr ) {
    				//$("#fl_msg_saving").show();
    			}
    		}).done(function( tabData ) {
        		if(typeof tabData === 'undefined' || tabData == "" || tabData == null){
            		console.log('Data Error');
        		}else if(typeof tabData['error'] !== 'undefined' && tabData['error'] != "" && tabData['error'] != null){
            		showHideErrMsg('Y', 'Y', 'No section data found!!');
            	}else{
            		for(var xkey in tabData){
                		if(xkey == 'section'){
                    		var sectionData = tabData[xkey];
                    		var sectionHtml = processTabData(sectionData, sectionTabId, actTabId, actTabTitle);

                    		if(typeof sectionHtml !== 'undefined' && sectionHtml != "" && sectionHtml != null){
                    			$('#'+actTabId).fadeOut("slow", function(){
									$('#'+actTabId).html(sectionHtml);
                    			    $('#'+actTabId).fadeIn("slow");
									$('#'+actTabId).removeAttr('style');
                    			    infoEditorProcess();
                    			});
                    		}
                		}
          			}
        		}
 			});
	});
}

function saveInSession(thisElm, i) {
		/*var agentInService = false;
		try {
			if(parent.AgentInService(currentActCallId)){
				agentInService = true;
			}
		} catch (ex) {
			console.log("Error: " + ex.message);
		}
		//Uncomment when in live
		if (!agentInService){
			$("#id_"+thisElm.name + i).prop('checked', false);
			alert("Agent not in service");
			return false;
			event.preventDefault();
		}*/
		try {
			if(!parent.AgentInService(currentActCallId)){
				$("#id_"+thisElm.name + i).prop('checked', false);
				alert("Agent not in service");
				return false;
				event.preventDefault();
			}
		} catch (ex) {
			console.log("Error: " + ex.message);
		}
	
        if (!thisElm.checked) return false;
        //console.log("#id_"+thisElm.checked);
        $.ajax({
                type: "POST",
                url: "<?php echo $this->url('task='.$request->getControllerName()."&act=saveVarInCallSession");?>",
                data: { var_name: thisElm.name, callid: currentActCallId, var_value: thisElm.value },
                beforeSend: function ( xhr ) {
                }
        }).done(function( msg ) {
                $(".cls_"+thisElm.name).prop('checked', false);
                $("#id_"+thisElm.name + i).prop('checked', true);
        });
        
}

function addTabContent(thisElm){
    var id = $(".nav-tabs").children().length + 1;
    var tabId = 'contact_' + id;
    var tabTitle = $(thisElm).text();
    var secTabId = $(thisElm).attr('rel');
    var uniqueId = $(thisElm).attr('unique_id');
    var tabArrLength = openTabArray.length;
    if(tabArrLength > 0){
    	if($.inArray(uniqueId, openTabArray) >= 0 ){
    		$("ul.nav-tabs").find("[rel='"+uniqueId+"']").click();
        	return false;
    	}
    }

    $.ajax({
		type: "POST",
		url: "<?php echo $this->url('task='.$request->getControllerName()."&act=tab-details&param=".$param);?>",
		dataType: "json",
		data: {tabid:secTabId, tabDefaultVal:tabTitle},
			beforeSend: function ( xhr ) {
                $('.nav-tabs li:last-child').after('<li><a href="#' + tabId + '" rel="' + uniqueId + '">' + tabTitle + '</a> <span><i class="fa fa-times"></i></span></li>');
                $('.tab-content').append('<div class="tab-pane" id="' + tabId + '"><div style="width: 100%; text-align: center;"><img src="image/preloader.svg" alt="loading"></div></div>');
                $('.nav-tabs li:nth-child(' + id + ') a').click();
                $("html, body").animate({ scrollTop: 0 }, "slow");
			}
		}).done(function( tabData ) {
    		if(typeof tabData === 'undefined' || tabData == "" || tabData == null){
        		console.log('Data Error');
    		}else if(typeof tabData['error'] !== 'undefined' && tabData['error'] != "" && tabData['error'] != null){
        		showHideErrMsg('Y', 'Y', 'No section data found!!');
        	}else{
        		if(tabArrLength > 0 && $.inArray(uniqueId, openTabArray) < 0){
                	openTabArray[tabArrLength] = uniqueId;
                }else{
                	openTabArray[tabArrLength] = uniqueId;
                }
        		for(var xkey in tabData){
            		if(xkey == 'section'){
                		var sectionData = tabData[xkey];
                		var sectionHtml = processTabData(sectionData, secTabId, tabId, tabTitle);

                        $('.tab-content #' + tabId).html( sectionHtml );
              			//$('.nav-tabs li:last-child').after('<li><a href="#' + tabId + '" rel="' + uniqueId + '">' + tabTitle + '</a> <span><i class="fa fa-times"></i></span></li>');
                        //$('.tab-content').append('<div class="tab-pane" id="' + tabId + '">' + sectionHtml + '</div>');
                        //$('.nav-tabs li:nth-child(' + id + ') a').click();
                        infoEditorProcess();
            		}
      			}
    		}
	});
}

function processTabData(sectionData, secTabId, tabId, tabDefaultVal){
	var sectionHtml = '';
	if(typeof sectionData !== 'undefined' && sectionData != "" && sectionData != null){
		sectionHtml = '<div class="reloaderDiv"><button type="button" class="btn btn-success tab-data-refresh" rel="'+secTabId+'" tabid="'+tabId+'" tabTitleTxt="'+tabDefaultVal+'" style="padding:4px 8px !important;"><i class="fa fa fa-refresh"></i></button></div>';
        for(var secId in sectionData){
    		if(typeof secId !== 'undefined' || secId != "" || secId != null){
        		var secDataEach = sectionData[secId];
        		var fieldValue = "";
        		
        		if(typeof secDataEach['section_type'] !== 'undefined' && secDataEach['section_type'] == "F" && typeof secDataEach['fields'] !== 'undefined' && secDataEach['fields'].constructor === Array){
        			sectionHtml += '<div id="' +secId+ '" class="section"><div class="sec-fields">';
    
        			sectionHtml += '<form id="form_cinfo_' +secId+ '">';
        			sectionHtml += '<table width="100%" align="center" border="0" class="profile-details-tab" cellpadding="1" cellspacing="1" id="editable_tab_' +secId+ '">';
        			sectionHtml += '<tr class="p-head">';
        			sectionHtml += '<td colspan="3">' +secDataEach['section_title']+ '</td>';
    
        			if(secDataEach['is_editable'] == 'Y'){
        				crmRecordId = typeof secDataEach['crm_rec_id'] !== 'undefined' ? secDataEach['crm_rec_id'] : "";
            			sectionHtml += '<td align="right"><a class="btn btn-info btn-mini btn-info-editor" href="javascript:void(0)" rel="' +secId+ '" crm_rid="'+crmRecordId+'">';
            		    sectionHtml += '<i class="fa fa-pencil-square-o"></i> edit</a></td>';
        			}else{
            			sectionHtml += '<td align="right">&nbsp;</td>';
        			}
        			sectionHtml += '</tr>';
        			sectionHtml += '<tr><td class="p-head-br" colspan="4">&nbsp;</td></tr>';
    
        			var rowCount = 0;
    
        			for(var secFields in secDataEach['fields']){
            			var secFieldData = secDataEach['fields'][secFields];
            			
            			if (rowCount == 0 || rowCount % 2 == 0){
    			            sectionHtml += '<tr>';
    			        }
    			        rowCount++;
    			        fieldValue = typeof secFieldData['data_value'] != 'undefined' && secFieldData['data_value'] != null ? secFieldData['data_value'] : "";
    			        if (secFieldData['ftab_id'] != "" && fieldValue != ""){
    			        	fieldValue = "<a href='javascript:void(0)' class='add-contact tab-url' rel='"+secFieldData['ftab_id']+"' onclick='addTabContent(this);'>"+secFieldData['data_value']+"</a>";
    			        }
    			        
    			        sectionHtml += '<td width="20%">'+secFieldData['field_label']+'</td>';
    			        sectionHtml += '<td width="30%" class="p-value">&nbsp;<span class="contact-val" id="sp_'+secFieldData['field_key']+'">'+fieldValue+'</span>';
						if(typeof secFieldData['data_value'] != 'undefined' && secFieldData['data_value'] != null && secFieldData['data_value'] != ""){
							sectionHtml += '<span class="contact-inp"><input type="text" name="'+secFieldData['field_key']+'" id="'+secFieldData['field_key']+'" value="'+secFieldData['data_value']+'" size="30" /></span>';
						}else{
							sectionHtml += '<span class="contact-inp"><input type="text" name="'+secFieldData['field_key']+'" id="'+secFieldData['field_key']+'" value="" size="30" /></span>';
						}
    			        sectionHtml += '</td>';
    			
    			        if (rowCount != 0 && rowCount % 2 == 0){
    			            sectionHtml += '</tr>';
    			        }
        			}
        			if (rowCount != 0 && rowCount % 2 != 0){
        				sectionHtml += '</tr>';
    			    }
        			sectionHtml += '</table></form>';
            		sectionHtml += '</div><div class="sec-clear"></div></div>';
        		} else if(typeof secDataEach['section_type'] !== 'undefined' && secDataEach['section_type'] == "G" && typeof secDataEach['grid'] !== 'undefined' && secDataEach['grid'] != null && secDataEach['grid'].constructor === Object){
            		sectionHtml += '<div id="' +secId+ '" class="section"><div class="sec-head">' + secDataEach['section_title'] + '</div>'; 

            		//$is_searchable = isset($sec->is_searchable) && $sec->is_searchable == 'Y' ? 'Y' : 'N';
            		if(secDataEach['is_editable'] == 'Y'){
            			sectionHtml += '<div class="sec-filter">';
            			if(typeof secDataEach['filters'] !== 'undefined' && secDataEach['filters'].constructor === Array){
            				for(var secFilters in secDataEach['filters']){
                    			var secFilterData = secDataEach['filters'][secFilters];
            					sectionHtml += ' ' + secFilterData['field_label'] + ' ' + '<input type="text" name="'+secFilterData['field_key']+'"';
            					if (secFilterData['field_type'] == 'D') sectionHtml += ' class="date-pick flt"'; 
            					else sectionHtml += ' class="flt"';
            					sectionHtml += ' />';
            				}
            				sectionHtml += ' <input id="btn_search_'+secId+'" class="btn btn-success btn-sp-search" type="button" value="'+secDataEach['search_submit_label']+'" onclick="update_section(\''+secId+'\')" />';
            			}
            			sectionHtml += '</div>';
            		}
            		sectionHtml += '<div class="sec-fields">';
    
            		if(secDataEach['is_editable'] != 'Y'){
            			sectionHtml += '<br />';
            		}
            		
            		sectionHtml += '<div id="content_'+secId+'">';
            		sectionHtml += '<table class="report_table table">';
    
            	    var counI = 0;
            	    var tabColumn = new Array();
            	    for(var secGrids in secDataEach['grid']){
            	    	var secGridData = secDataEach['grid'][secGrids];
            	    	
            	    	if (secGrids === "tab_id"){
            	    		for(var secTabKey in secGridData){
                	    		if(typeof secGridData[secTabKey] !== 'undefined' && secGridData[secTabKey] != ""){
                	    		    tabColumn[secTabKey] = secGridData[secTabKey];
                	    		}
            	            }
        		    	}else if(secGrids === "column"){
            		    	sectionHtml += '<thead><tr class="report_row_head">';
            		    	for(i = 0; i < secGridData.length; i++) {
								if(typeof secGridData[i] != 'undefined' && secGridData[i] != null && secGridData[i] != ""){
									sectionHtml += '<td class="cntr">'+secGridData[i]+'</td>';
								}else{
									sectionHtml += '<td class="cntr">&nbsp;</td>';
								}
            	            }
            		    	sectionHtml += '</tr></thead>';
        		    	}
            	    }
            	    var $_class = 'report_row';
            	    sectionHtml += '<tbody>';
            	    for(var secGrids in secDataEach['grid']){
            	    	var secGridData = secDataEach['grid'][secGrids];
            	    	if (secGrids != "tab_id" && secGrids != "column"){
            	    		$_class = counI%2 == 1 ? 'report_row' : 'report_row_alt';
            	    		
            	    		sectionHtml += '<tr class="'+$_class+'">';
            		    	for(i = 0; i < secGridData.length; i++) {
								if(typeof secGridData[i] != 'undefined' && secGridData[i] != null && secGridData[i] != ""){
									sectionHtml += '<td class="cntr">'+secGridData[i]+'</td>';
								}else{
									sectionHtml += '<td class="cntr">&nbsp;</td>';
								}
            	            }
            		    	sectionHtml += '</tr>';
            		    	counI++;
            	    	}                                	    	
            	    }
            	    sectionHtml += '</tbody>';                                		
            		sectionHtml += '</table></div>';
            		sectionHtml += '</div><div class="sec-clear"></div></div>';
        		}
    		}
    	}
	}
	return sectionHtml;
}

function showHideErrMsg(isShow, hideAfterShow, msg){	
	if(isShow == 'Y' && !isErrMsgShowed){
		isErrMsgShowed = true;
		if(msg != ""){
			$('#fl_errmsg_show').html(msg);
		}
		$('#fl_errmsg_show').show();
		if(hideAfterShow == 'Y'){
			setTimeout(function(){ $('#fl_errmsg_show').fadeOut(400); isErrMsgShowed = false; }, 3000);
		}
	}
	if(isShow == 'N'){
		isErrMsgShowed = false;
		$('#fl_errmsg_show').fadeOut(400);
	}
}

function get_section_head(secid, title, sec, is_searchable)
{
	head = '<div id="' + secid + '" class="section"><div class="sec-head">' + title + '</div>'; 
	
	if (is_searchable == 'Y') {
		head += '<div class="sec-filter">';
		if (sec['filters'] !== 'undefined' && sec['filters'] instanceof Array) {
			var secFilters = sec['filters'];
			for (var i = 0; i < secFilters.length; i++) {
				head += ' ' + secFilters[i]['field_label'] + ' ' + '<input type="text" name="'+secFilters[i]['field_key']+'"';
				if (secFilters[i]['field_type'] == 'D') head += ' class="date-pick flt"'; 
				else head += ' class="flt"';
				head += ' />';
			}
			head += ' <input id="btn_search_'+secid+'" class="btn" type="button" value="'+sec['search_submit_label']+'" onclick="update_section(\''+secid+'\')" />';
		}
		head += '</div>';
	}
	
	return head + '<div class="sec-fields">';
}

function load_sels()
{
	var num_dids = <?php echo count($disposition_ids);?>;
	if (num_dids == 0) num_dids++;
	show_sels(num_dids);
}

function hide_sels(i)
{
	for (j=i+2; j < <?php echo $num_select_box;?>; j++) {
		$("#disposition_id" + j).hide();
	}
}

function show_sels(i)
{
	for (j=0; j < i; j++) {
		$("#disposition_id" + j).show();
	}
}

function reload_sels(i, val)
{
	if (i < <?php echo $num_select_box;?>) {
		hide_sels(i);
		var j = i+1;
		$select = $("#disposition_id" + j);
		$select.html('<option value="">Select</option>');
		$("#disposition").val(val);
		
		if (val.length > 0) {
			var jqxhr = $.ajax({
				type: "POST",
				url: "<?php echo $this->url("task=" . $request->getControllerName() . '&act=dispositionchildren');?>",
				data: { did: val },
				dataType: "json"
			})
			.done(function(data) {
				show_sels(j+1);
				if (data.length == 0) {
					//console.log('0');
					$select.hide();
			 	} else {
					$.each(data, function(key, val){
						$select.append('<option value="' + key + '">' + val + '</option>');
					})
				}
			})
			.fail(function() {
				//
			})
			.always(function() {
			});
		}
	
	
	}
}

function gen_tpin(agentid, callid, accid)
{
	$("#btn_tpin").attr('disabled', 'disabled');
	$("#btn_tpin").val('Pending..');
	
	$.post("<?php echo $this->url('task='.$request->getControllerName().'&act=tpin');?>", {agentid:agentid, callid:callid, accountid:accid})
		.done(function( resp ) {
			if (resp == 'Y') {
				$("#btn_tpin").removeAttr("disabled");
				$("#btn_tpin").val('Re-Generate TIN');
			} else if (resp == 'N') {
				$("#btn_tpin").removeAttr("disabled");
				$("#btn_tpin").val('Generate TIN');
			} else if (resp == 'P') {
			
			} else {
				$("#btn_tpin").val('Error..');
			}
	});
	
	
}

function update_section(secid)
{
	var data = {};
	$( "#"+secid + " .flt").each(function( index ) {
		data[$( this ).attr('name')] = $( this ).val();
	});
	//console.log(data);
	data['callid'] = '<?php if (!empty($crm_info->callid)) echo $crm_info->callid;?>';
	data['section_id'] = secid;
	data['template_id'] = '<?php if (!empty($crm_info->template_id)) echo $crm_info->template_id;?>';
	data['callerid'] = '<?php echo $crm_info->caller_id; ?>';
	
	$.ajax({
		type: "POST",
		url: "<?php echo $this->url('task='.$request->getControllerName()."&act=get_section_data&record_id=".$crm_info->crm_record_id);?>",
		data: data,
			beforeSend: function ( xhr ) {
				//$("#fl_msg_saving").show();
			}
		}).done(function( msg ) {

			//console.log("#content_"+secid);
			$("#content_"+secid).html(msg);
			//alert('1');
			//$("#fl_msg_saving").hide();
			/*
			if (msg > 0) {
				$("#fl_msg_success").show();
				$("#fl_msg_success").fadeOut(2000, function () {
					window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&act=details&param=".$param);?>";
				});
			} else {
				$("#fl_msg_err").show();
				$("#fl_msg_err").fadeOut(2000, function () {
					window.location.href = "<?php echo $this->url('task='.$request->getControllerName()."&act=details&param=".$param);?>";
				});
			}*/
	});
	
}

function playFile(evt)
{
	$.post('cc_web_agi.php', { 'page':'CDR', 'agent_id':'<?php echo UserAuth::getCurrentUser();?>', 'option':evt },
		function(data, status) {
			if (status == 'success') {
				if(data != 'Y') alert(data);
			} else {
				alert("Failed to communicate!");
			}
	});
}

<?php if ($display_disposition):?>
var dispositions = [<?php
if (is_array($dp_options)) {
	$i = 0;
	foreach ($dp_options as $row) {
		if ($i>0) echo ',';
		echo '{di:"'.$row->disposition_id.'", gi:"'.$row->group_id.'", t:"'.$row->title.'"}';
		$i++;
	}
}
?>];

function init_disposition(disp_code)
{
	var sgroup = '';
	var len = dispositions.length;
	for(var i = 0; i < len; i++) {
		var obj = dispositions[i];
		if (obj.di == disp_code) {
			$("#dgroup").val(obj.gi);
			break;
		}
	}
	
	adjust_disposition();
	$("#disposition").val(disp_code);
}

function adjust_disposition()
{
	/* var len = dispositions.length;
	var dgroup = document.getElementById('dgroup').value;
	var targetBox = document.getElementById('disposition');
	//targetBox.options.length = 0;
	$('#disposition')
		.append($("<option></option>")
		.attr("value",'')
         .text('Select'));
	for(var i = 0; i < len; i++) {
		var obj = dispositions[i];
		if (dgroup == obj.gi) {
			console.log(dgroup+obj.di);
			$('#disposition')
				.append($("<option></option>")
				.attr("value",obj.di)
		         .text(obj.t));
		}
	} */

	var templateID = "<?php echo $crm_info->template_id; ?>";
	var serviceID = $('#dgroup option:selected').val();
	var useEmModule = $('#dgroup option:selected').attr('emailmod');
	var ticketPageURL = "<?php echo $this->url("task=crm_in&act=ticket-submit&callid=".$crm_info->callid."&agentid=".$crm_info->agent_id);?>&tid="+templateID+"&sid="+serviceID+"&emailmod="+useEmModule;
	//console.log(ticketPageURL);
	var isServiceTicket = $('#dgroup option:selected').attr('isticket');
	var fromEmail = $('#dgroup option:selected').attr('femail');
	var toEmail = $('#dgroup option:selected').attr('temail');
	var ticketIssue = $('#dgroup option:selected').text();
	
	if(isServiceTicket == 'Y' && typeof fromEmail != 'undefined' && fromEmail != '' && typeof toEmail != 'undefined' && toEmail != ''){
		$.colorbox({href:ticketPageURL, iframe:true, width:950, height:600, escKey:false, overlayClose:false});
	}
}
<?php endif;?>
</script>
<?php } ?>
<span class="flash-left" id="fl_msg_success">Information saved successfully !!</span>
<span class="flash-left" id="fl_msg_err">Failed to save information !!</span>
<span class="flash-left" id="fl_msg_saving">Saving information ...</span>
<span class="flash-left" id="fl_msg_exists">Already exists...</span>
<span class="flash-left" id="fl_msg_del">Deleted Successfully...</span>
<div id="fl_errmsg_show"></div>
<?php
	$is_authenticated = isset($crm_info->caller_auth_status) && $crm_info->caller_auth_status =='Y';
?>
<div class="auth-msg <?php echo $is_authenticated ? 'authentic' : 'unauthentic';?>" style=""><?php echo empty($crm_info->caller_auth_msg) ? '-' : $crm_info->caller_auth_msg;?><?php 
if (!$is_authenticated && !empty($crm_info->account_id) && !empty($crm_info->callid)):?> <a id="btn_auth" href="#">Mark Verified</a><?php endif;?></div>

<?php
if (!empty($errMsg)) {
?>

<table width="100%" align="center" border="0" class="profile-details" cellpadding="1">
<tr class="p-head"><td colspan="4">Error</td></tr><tr><td colspan="4" class="p-head-br">&nbsp;</td></tr>
<tr><td colspan="4" align="center"><span style=" font-size:18px; font-weight:bold; background-color:brown; color:white; padding: 5px 50px;"><?php if (!empty($errMsg)) echo $errMsg; else echo 'No profile found !!';?></span></td></tr>
</table>
<?php 
} else {
?>

<table width="100%"><tr>
        <td valign="top" style="padding-left: 10px;">
<div class="container">
    <ul class="nav nav-tabs" role="tablist">
        <li class="active"><a href="#contact_1" data-toggle="tab">Main Tab</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="contact_1">


<?php

//var_dump($crm_info);

$sections = $crm_info->section;

if (is_array($sections)) {
	/*
	$flds = array();
	if (is_array($fields)) {
		foreach ($fields as $fld) {
			$flds[$fld->section_id][] = $fld;
		}
	}
	*/
	$skillid = empty($crm_info->skill_name) ? '' : $crm_info->skill_name;
	$lang = empty($crm_info->caller_prefered_language) ? '' : $crm_info->caller_prefered_language;
	
	if ($lang =='EN') $lang = 'English';
	else if ($lang == 'BN') $lang = 'Bangla';
		
	$leftCol = '<div class="section"><div class="sec-head">Call Information</div><div class="sec-fields">
<div class="sec-field"><div class="sec-fld-label">Skill</div><div class="sec-fld-val">'.$skillid.'</div></div>
<div class="sec-field"><div class="sec-fld-label">Language</div><div class="sec-fld-val">'.$lang.'</div></div>
<div class="sec-field"><div class="sec-fld-label">Caller ID</div><div class="sec-fld-val">'.$crm_info->caller_id.'</div></div>
</div><div class="sec-clear"></div></div>';
	$rightCol = '';
	$col = 1;
	//var_dump($sections);
	foreach ($sections as $secid=>$sec) {
		//$sec_data = isset($crm_info->section[$sec->section_id]) ? $crm_info->section[$sec->section_id] : array();
		$_cls = '';
		/*
		if ($sec->section_type == 'F' || $sec->section_type == 'T') {
			$_cls = ' rightCol';
		}
		*/
		
		$is_searchable = isset($sec->is_searchable) && $sec->is_searchable == 'Y' ? 'Y' : 'N';
		$section_head = get_section_head($secid, $sec->section_title, $sec, $is_searchable);
		$section_foot = get_section_foot();

		if ($sec->section_type == 'F') {
            /* 
			if (isset($sec->fields) && is_array($sec->fields)) {
				foreach ($sec->fields as $fld) {
					$section_head .= '<div class="sec-field"><div class="sec-fld-label">' . $fld->field_label .
					'</div><div class="sec-fld-val">' . $fld->data_value . '</div></div>';
				}
			}
			if ($col == 0) {
				$leftCol .= $section_head . $section_foot;
				$col = 1;
			} else {
				$col = 0;
				$rightCol .= $section_head . $section_foot;
			}
			 */
			//Changeable area
		    $customerData = '';
		    flush_two_cols($leftCol, $rightCol);
		    $leftCol = '';
		    $rightCol = '';
		    $col = 0;
		    $crm_record_id = !empty($sec->crm_rec_id) ? $sec->crm_rec_id : "";
		    
		    echo '<div id="' . $secid . '" class="section"><div class="sec-fields">';
		    //var_dump($sec);
			if (isset($sec->fields) && is_array($sec->fields)) {
			    $customerData .= '<form id="form_cinfo_'.$secid.'">';
			    $customerData .= '<table width="100%" align="center" border="0" class="profile-details-tab" cellpadding="1" cellspacing="1" id="editable_tab_'.$secid.'">';
			    $customerData .= '<tr class="p-head">';
			    $customerData .= '<td colspan="3">'.$sec->section_title.'</td>';
			    if ($sec->is_editable == 'Y'){
    			    $customerData .= '<td align="right"><a class="btn btn-info btn-mini btn-info-editor" href="javascript:void(0)" rel="'.$secid.'" crm_rid="'.$crm_record_id.'">';
    			    $customerData .= '<i class="fa fa-pencil-square-o"></i> edit</a></td>';
			    }else {
			        $customerData .= '<td align="right">&nbsp;</td>';
			    }
			    $customerData .= '</tr>';
			    $customerData .= '<tr><td class="p-head-br" colspan="4">&nbsp;</td></tr>';
			
			    $rowCount = 0;
			    $tabUniqueId = '';
			    foreach ($sec->fields as $fld) {
			        if ($rowCount == 0 || $rowCount % 2 == 0){
			            $customerData .= '<tr>';
			        }
			        $rowCount++;
			        $fieldValue = $fld->data_value;
			        if (!empty($fld->ftab_id)){
			            $tabUniqueId = uniqueIdGenerate($fld->ftab_id);
			            $fieldValue = "<a href='javascript:void(0)' class='add-contact tab-url' rel='".$fld->ftab_id."' unique_id='".$tabUniqueId."' onclick='addTabContent(this);'>".$fld->data_value."</a>";
			        }
			        
			        $customerData .= '<td width="20%">'.$fld->field_label.'</td>';
			        $customerData .= '<td width="30%" class="p-value">&nbsp;<span class="contact-val" id="sp_'.$fld->field_key.'">'.$fieldValue.'</span>';
			
			        $customerData .= '<span class="contact-inp"><input type="text" name="'.$fld->field_key.'" id="'.$fld->field_key.'" value="'.$fld->data_value.'" size="30" /></span>';
			        if (!empty($fld->ftab_id)){
			            $customerData .= "<span class='contact-tab-sp' id='tabk_url_".$fld->field_key."'><a href='javascript:void(0)' class='add-contact tab-url' rel='".$fld->ftab_id."' unique_id='".$tabUniqueId."' onclick='addTabContent(this);'>".$fld->data_value."</a></span>";
			        }
			        $customerData .= '</td>';
			        $customerData .= '';
			
			        if ($rowCount != 0 && $rowCount % 2 == 0){
			            $customerData .= '</tr>';
			        }
			    }
			    if ($rowCount != 0 && $rowCount % 2 != 0){
			        $customerData .= '</tr>';
			    }
			    $customerData .= '</table></form>';
			}
			echo $customerData;
			echo $section_foot;

		} else if ($sec->section_type == 'T') {

			$section_head .= '<div style="text-align:center; padding:10px 10px 25px 10px;"><br /><input type="button" id="btn_tpin" ';
			$section_head .= $sec->tpin_status == 'P' || empty($sec->tpin_status) || !$is_authenticated ? "disabled='disabled'":"";
			$section_head .= ' value="';
			if ($sec->tpin_status == 'Y') $section_head .= 'Re-Generate TIN'; else if ($sec->tpin_status == 'P') $section_head .= 'Pending..'; else $section_head .= 'Generate TIN';
			$section_head .= '" onclick="gen_tpin(\'';
			if (isset($crm_info->agent_id)) $section_head .= $crm_info->agent_id;
			$section_head .= '\', \'';
			if (isset($crm_info->callid)) $section_head .= $crm_info->callid;
			$section_head .= '\', \'';
			if (isset($crm_info->account_id)) $section_head .= $crm_info->account_id;
			$section_head .= '\');" /></div>';
			if ($col == 0) {
				$leftCol .= $section_head . $section_foot;
				$col = 1;
			} else {
				$col = 0;
				$rightCol .= $section_head . $section_foot;
			}

		} else if ($sec->section_type == 'G') {

	flush_two_cols($leftCol, $rightCol);
	$leftCol = '';
	$rightCol = '';
	$col = 0;

	echo $section_head;
	
if (isset($sec->grid) && is_array($sec->grid)) {
	if ($is_searchable != 'Y') echo '<br />';
?>
<div id="content_<?php echo $secid;?>" class="table-responsive">
<table class="report_table table">
<?php
	$i = 0;
	$tabUniqueId = '';
	$tabColumn = array();
	
	$column_indexes = array();
	
	foreach ($sec->grid as $rowid=>$grid_row) {
    	if ($rowid === "tab_id"){
    	    if (is_array($grid_row)) {
    	        foreach ($grid_row as $colKey=>$colVal) {
    	            $tabColumn[$colKey] = $colVal;
    	        }
    	    }
    	    continue;
    	} elseif ($rowid === "settings") {
    	        continue;
    	}
    	if($i == 0) {
    	    $_class = 'report_row_head';
    	    echo '<thead>';
    	    $column_indexes = array();
    	    foreach ($grid_row as $colKey=>$colVal) {
    	            $column_indexes[] = $colKey;
    	    }
    	} elseif ($i == 1){
            echo '<tbody>';
        }
        if ($i > 0) {
                $_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
        }
		
        echo '<tr class="'.$_class.'">';
        if (is_array($grid_row)) {
                        //print_r($column_indexes);
			foreach ($grid_row as $colKey=>$colVal) {
			    if (!empty($tabColumn[$colKey])){
			        $tabUniqueId = uniqueIdGenerate($tabColumn[$colKey]);
			        echo "<td class='cntr'><span class='contact-val'><a href='javascript:void(0)' class='add-contact tab-url' rel='".$tabColumn[$colKey]."' unique_id='".$tabUniqueId."' onclick='addTabContent(this);'>".$colVal."</a></span></td>";
			    } else {
			            //print_r($colKey);
			            //print_r($column_indexes);
			            if (in_array($colKey, $column_indexes, true)) {
							$cardSessData = '';
							if (!empty($colVal) && $i > 0 && isset($sec->grid['settings'][$colKey]) && !empty($sec->grid['settings'][$colKey])) {
								if (!empty($sec->grid['settings'][$colKey]['save_in_session_field'])) {
									$_colname = $sec->grid['settings'][$colKey]['save_in_session_field'];
									$_sessname = $sec->grid['settings'][$colKey]['save_in_session_name'];
									//echo $grid_row[$_colname];
									$_sessvar = $_sessname . '|' . $grid_row[$_colname];

									/*if (in_array($_sessvar, $InCallSessionVars)) {
										echo "<br>SESS-VAR=".$_sessvar." ## CallSessionVars=";
										print_r($InCallSessionVars);
									}*/

									$cardSessData = ' <input type="checkbox" class="cls_' . $_sessname . '" id="id_' . $_sessname . $i . '" name="' . $_sessname . '" value="' . $grid_row[$_colname] . '" onclick="saveInSession(this, ' . $i . ');" ';
									if (in_array($_sessvar, $InCallSessionVars)) $cardSessData .= 'checked="checked" ';
									$cardSessData .= '/> ';
								}
							}
							if (!empty($cardSessData)) {
								echo '<td class="cntr" nowrap="nowrap">' . $cardSessData . $colVal . '</td>';
							} else{
								echo '<td class="cntr">' . $cardSessData . $colVal . '</td>';
							}
				    }
			    }
			}
        }
        echo '</tr>';
        if ($i == 0) {
                echo '</thead>';
        }
		$i++;
	}
	echo '</tbody>';
?>
</table>
</div>
<?php
}

	echo $section_foot;

} else if ($sec->section_type == 'D') {
	
	flush_two_cols($leftCol, $rightCol);
	$leftCol = '';
	$rightCol = '';
	$col = 0;
	
	$pagination->base_link = $this->url("task=" . $request->getControllerName() . "&act=details" . $url_cond);
	$pagination->rows_per_page = 10;
	//$pagination->base_link = '';
	if (empty($crm_info->crm_record_id)) {
		$pagination->num_records = 0;
		$records = null;
		$pagination->num_current_records = 0;
	} else {
		$pagination->num_records = $crm_model->numCrmDispositions($crm_info->crm_record_id);
		$records = $pagination->num_records > 0 ? 
			$crm_model->getCrmDispositions($crm_info->crm_record_id, $pagination->getOffset(), $pagination->rows_per_page) : null;
		$pagination->num_current_records = is_array($records) ? count($records) : 0;
	}
	
	echo $section_head;
	show_disposition_history($records, $pagination, $this->voice_logger_path, $this->is_voice_file_downloadable);
	echo $section_foot;

} else if ($sec->section_type == 'I') {
	flush_two_cols($leftCol, $rightCol);
	$leftCol = '';
	$rightCol = '';
	$col = 0;
	$accid = empty($crm_info->account_id) ? '' : $crm_info->account_id;
	$pending_records = $crm_model->getCrmIVRPendingServices($accid, 0, 5);
	$served_records = $crm_model->getCrmIVRServedServices($accid, 0, 5);
		
	echo $section_head;
	show_ivr_services($pending_records, $served_records);
	echo $section_foot;
	
} else {


}	//else

	}
}

flush_two_cols($leftCol, $rightCol);

?>




<!-- tab content end here -->
        </div>
        <!-- <div class="tab-pane" id="contact_02">Information Form: Information will be added here</div> -->
    </div>
</div>
</td></tr></table>

<?php } ?>
<br />
<?php

function flush_two_cols($leftCol, $rightCol)
{
	if (!empty($leftCol) || !empty($rightCol)) {
		echo '<div class="columnContainer"><div class="leftCol">'.$leftCol.'</div><div class="rightCol">'.$rightCol.'</div></div>';
	}
}

function get_section_foot()
{
	return '</div><div class="sec-clear"></div></div>';
}

function get_section_head($secid, $title, $sec, $is_searchable)
{
	$head = '<div id="' . $secid . '" class="section"><div class="sec-head">' . $title . '</div>'; 

	if ($is_searchable == 'Y') {
		$head .= '<div class="sec-filter">';
		if (isset($sec->filters) && is_array($sec->filters)) {
			foreach ($sec->filters as $fld) {
				$head .= ' ' . $fld->field_label . ' ' . '<input type="text" name="'.$fld->field_key.'"';
				if ($fld->field_type == 'D') $head .= ' class="date-pick flt"'; 
				else $head .= ' class="flt"';
				$head .= ' />';
			}
			$head .= ' <input id="btn_search_'.$secid.'" class="btn btn-success btn-sp-search" type="button" value="'.$sec->search_submit_label.'" onclick="update_section(\''.$secid.'\')" />';
		}
		$head .= '</div>';
	}
	
	return $head . '<div class="sec-fields">';
}

function show_ivr_services($pending_records, $served_records)
{
	if (empty($pending_records) && empty($served_records)) {
		echo '<br /><b>No record found !!</b>';
		return;
	}
	
	if (is_array($pending_records)) {
?>
Pending Services:<br />
<table class="report_table">
<tr class="report_row_head"><td class="cntr">SL</td><td class="cntr">Time</td><td>Service</td><td>Service Type</td><td>Caller ID</td></tr>
<?php
$i = 0;
foreach ($pending_records as $rec) {
$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
?>
<tr class="<?php echo $_class;?>"><td class="cntr"><?php echo $i;?></td><td class="cntr"><?php echo date("Y-m-d H:i:s", $rec->tstamp);?></td><td align="left">&nbsp;<?php echo $rec->service_title . ' ['.$rec->disposition_code.']';?></td><td><?php echo $rec->service_type == 'M' ? 'Manual' : 'Auto';?></td><td align="left">&nbsp;<?php echo $rec->caller_id;?></td></tr>
<?php
}
?>
</table>
<?php	
	}

if (is_array($served_records)) {
?>
Served Services:<br />
<table class="report_table">
<tr class="report_row_head"><td class="cntr">SL</td><td class="cntr">Time</td><td class="cntr">Served Time</td><td>Service</td><td>Service Type</td><td>Caller ID</td><td>Agent</td></tr>
<?php
$i = 0;
foreach ($served_records as $rec) {
$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
?>
<tr class="<?php echo $_class;?>"><td class="cntr"><?php echo $i;?></td><td class="cntr"><?php echo date("Y-m-d H:i:s", $rec->tstamp);?></td><td class="cntr"><?php echo date("Y-m-d H:i:s", $rec->served_time);?></td><td align="left">&nbsp;<?php echo $rec->service_title . ' ['.$rec->disposition_code.']';?></td><td><?php echo $rec->service_type == 'M' ? 'Manual' : 'Auto';?></td><td align="left">&nbsp;<?php echo $rec->caller_id;?></td><td align="left">&nbsp;<?php echo $rec->agent_id . ' ['.$rec->nick.']';?></td></tr>
<?php
}
?>
</table>
<?php	
	}

?>

<?php
}


function show_disposition_history($records, $pagination, $voice_logger_path, $is_voice_file_downloadable)
{
?>
<table width="100%" align="center" border="0" class="" cellpadding="1">
<?php if (!is_array($records)) { ?><tr><td colspan="4">&nbsp;</td></tr><?php } ?>
<tr><td colspan="4" align="left">
<?php
if (is_array($records)) {
?>
<table class="report_extra_info" style="margin-bottom: 5px;">
<tr>
	<td>
		<?php
        	echo 'Record(s) ' . $pagination->getCurrentRecordsIndex() . ' of <b>' . $pagination->num_records . '</b> &nbsp;::&nbsp; ' . 
				'Page <b>' . $pagination->current_page . '</b> of <b>' . $pagination->getTotalPageCount() . '</b>';
		?>
	</td>
</tr>
</table>

<table class="report_table" style="margin-bottom: 5px;">
<tr class="report_row_head"><td class="cntr">SL</td><td class="cntr">Time</td><td>Disposition</td><td>Agent</td><td>Auth by</td><td>Note</td><td class="cntr">Audio</td></tr>
<?php
$i = $pagination->getOffset();
foreach ($records as $rec) {
$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
		$logAudio = "NONE";
?>
<tr class="<?php echo $_class;?>"><td class="cntr"><?php echo $i;?></td><td class="cntr"><?php echo date("Y-m-d H:i:s", $rec->tstamp);?></td><td align="left">&nbsp;<?php echo $rec->title;?></td><td class="cntr"><?php echo empty($rec->agent_id) ? '-' : $rec->agent_id . ' ['.$rec->nick.']';?></td><td class="cntr"><?php if ($rec->caller_auth_by == 'I') echo 'IVR'; else if ($rec->caller_auth_by == 'A') echo 'Agent'; else echo '-';?></td><td align="left">&nbsp;<?php echo $rec->note;?></td><td class="cntr"><?php

		if (!empty($rec->callid)) {
		
			$file_timestamp = substr($rec->callid, 0, 10);
			$yyyy = date("Y", $file_timestamp);
			$yyyy_mm_dd = date("Y_m_d", $file_timestamp);
			$sound_file_gsm = $voice_logger_path . "vlog/$yyyy/$yyyy_mm_dd/" . $rec->callid . ".wav";
			if (file_exists($sound_file_gsm) && filesize($sound_file_gsm) > 0) {
				$playUrl = $this->url("task=cdr&act=playbrowser&cid=".$rec->callid."&cdr_page=".$pageTitle);
                //if ($is_voice_file_downloadable) {
                	$logAudio = " <a title='Audio' href=\"$playUrl\"><i class=\"fa fa-file-audio-o\"></i></a> &nbsp;";
                //}                                
                //$logAudio = 'A';
			}
		}
		
    	echo $logAudio;

	?>
	</td></tr>
<?php
}
?>
</table>
<table class="report_extra_info">
<tr>
	<td>
	<?php echo $pagination->createLinks();?>
	</td>
</tr>
</table>
<?php
} else {
	echo '<b>No record found !!</b>';
}
?>
</td></tr>

</table>
<?php
}
?>
