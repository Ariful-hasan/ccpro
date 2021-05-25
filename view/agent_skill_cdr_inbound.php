<?php 
include_once "lib/jqgrid.php";
$grid = new jQGrid();
//$grid->caption = "Agent List";
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
//$grid->hidecaption=true;
$grid->shrinkToFit=false;
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->ShowReloadButtonInTitle=true;
if (isset($sms_enabled) && $sms_enabled) {
    $grid->ShowSMSBtnWithReload = true;
}

//$grid->AddModel("Join Date", "account_open_date", 120,"center","date","Y-m-d H:i:s","Y-m-d H:i:s",true,"date");
$grid->AddModel("Start time", "start_time", 100, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
if (!empty($date_stime) && !empty($date_etime)){
    $grid->SetDefaultValue("start_time", $date_stime, $date_etime);
}else{
    $grid->SetDefaultValue("start_time", date("Y-m-d 00:00", strtotime("-1 day")), date("Y-m-d 23:59"));
}
$grid->AddModelNonSearchable("Stop time", "cdr_stop_time", 100, "center");
if ($sms_enabled){
    $grid->AddModelNonSearchable('<input type="checkbox" name="cidSelectorAll" id="cidSelectorAll" value="Y">', "chkbox_sndsms", 30, "center");
}
$grid->AddModel("Caller ID", "cli", 80, "center");
$grid->SetDefaultValue("cli", $cli);
$grid->AddModel("DID", "did", 80, "center");
$grid->SetDefaultValue("did", $did);

$grid->AddModelNonSearchable("IVR enter time", "ivr_enter_time", 100,"center");
$grid->AddModelNonSearchable("IVR", "ivr_name", 80,"center");
$grid->AddModelNonSearchable("Time in IVR", "time_in_ivr", 80,"center");
$grid->AddModelNonSearchable("IVR&nbsp;lang", "ivr_language", 80,"center");
$grid->AddModelNonSearchable("Skill&nbsp;enter&nbsp;time", "skill_enter_time", 100,"center");
$grid->AddModelNonSearchable("Skill", "skill_name", 80,"center");
$grid->AddModelNonSearchable("Hold in queue", "hold_in_q", 80,"center");
$grid->AddModelNonSearchable("Status", "skill_status", 80,"center");
$grid->AddModelNonSearchable("Service time", "service_time", 80,"center");
$grid->AddModelNonSearchable("Agent", "agent_id", 80,"center");
$grid->AddModelNonSearchable("Total time", "total_time", 80,"center");
$grid->AddModelNonSearchable("Alarm", "alarm", 80,"center");
//$grid->AddModelNonSearchable("Disposition", "disposition", 80,"center");

$grid->show("#searchBtn");
?>	
<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);

    <?php if ($sms_enabled){ ?>
    try{
        $('#cidSelectorAll').click(function (event) {
            event.stopPropagation();
            if ($(this).is(":checked")){
                $(this).prop('checked', true);
                $('.cid-selector-chk').prop('checked', true);
            }else {
                $(this).prop('checked', false);
                $('.cid-selector-chk').prop('checked', false);
            }
        });

        $('#send_sms_btn').click(function(){
            if($(".ui-jqgrid-btable input[name='cid_selector[]']:checked").length) {
                $("#template_chose").click();
            }else {
                alert('Please select Caller ID');
            }
        });

        $(".smstplate").colorbox({
            iframe:true, width:"800", height:"450"
        });
    }catch(e){
        console.log(e.message);
    }
    <?php } ?>
});
</script>
<?php if ($sms_enabled){ ?>
    <a id="template_chose" href="index.php?task=agent&act=smstemplate" class="smstplate" style="display: none;">0</a>
<?php } ?>