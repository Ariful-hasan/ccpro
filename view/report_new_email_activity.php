<?php
include_once "lib/jqgrid_report.php";
$grid = new jQGridReport();
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width = "auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->shrinkToFit = true;
$grid->footerRow = TRUE;
$grid->CustomSearchOnTopGrid = true;
$grid->multisearch = true;
$grid->ShowReloadButtonInTitle = true;
$grid->ShowDownloadButtonInTitle = true;
$grid->DownloadFileName = $pageTitle;
$grid->floatingScrollBar=true;

/*$grid->AddModel("Date Time", "activity_time", 130, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
$grid->SetDefaultValue("activity_time", date("Y-m-d", strtotime("-1 month")), date("Y-m-d 23:59"));*/
$grid->AddModelCustomSearchable('Date&Time', "activity_time", 130, "center", "report-datetime");
$grid->SetDefaultValue("activity_time", date($report_date_format . " 00:00", strtotime("-1 month")), date($report_date_format . " 00:00", strtotime('+1day')));

$grid->AddModel("Email ID", "ticket_id", 150,"center");
if (empty($isAgent)){
    $grid->AddSearhProperty("Agent", "agent_id", "select", $agent_list);
    $grid->SetDefaultValue("agent_id", $agent_id);
}
$grid->AddModelNonSearchable("Agent", "agent_id", 150,"center");
$grid->AddSearhProperty("Status", "status", "select", $status_options);
$grid->SetDefaultValue("status", $status);
$grid->AddModelNonSearchable("Status", "emailStatus", 100,"center");

$grid->show("#searchBtn");

$previous_month_date = date("Y-m-d", strtotime("-1 month"));
list($year, $month, $date) = explode("-", $previous_month_date);
?>

<script type="text/javascript">
    $(function () {
        $(document).on("click", "#cboxClose", function () {
            location.reload(true);
        });
        SetEmailReportDateTimePicker('<?php echo $report_date_format ?>');
        function SetEmailReportDateTimePicker(format, selector_prefix='.gs-report'){
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
                start_default_date = '<?php echo $date . "/" . $month . "/" . $year?>';
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
                start_default_date = '<?php echo $month . "/" . $date . "/" . $year?>';

                current_date.setDate(current_date.getDate() + 1);
                month = +current_date.getMonth()+1;
                month = ((''+month).length==1 ? '0'+month : month);
                day = current_date.getDate();
                day = ((''+day).length==1 ? '0'+day : day);
                end_default_date = month+'/'+day+'/'+current_date.getFullYear();
            }

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
    });
</script>

