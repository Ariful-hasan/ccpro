
<style>
    .service-level-table thead tr{background-color: #f1f1f1;}
    .service-level-table td,.service-level-table th{min-width: 90px !important;text-align: center; padding: 5px !important;}
    .control-label{position: relative}
    .gs-check-box{width: 28px !important; height: 28px !important; }
    .check-box-title{position: relative; top: -10px;  vertical-align: middle; font-weight: 700}
    .table td.fit, .table th.fit {white-space: nowrap; width: auto; vertical-align: middle}
    .m-t-10{margin-top: 10px}
</style>


<div class="row">
    <div class="col-md-12 small">
        <form id="workcode-form" action="<?php echo $this->url("task=report-new&act=workcode"); ?>" class="form-inline" method="post" autocomplete="off">

         <!--   <div class="form-group form-group-sm ">
                <label for="sdate" class="control-label">Date Format: </label>
                <?php /*echo date_format_option($request->getRequest('date_format', REPORT_DATE_FORMAT." H:i") ); */?>
            </div>-->

            <div class="form-group form-group-sm ">
                <label for="sdate" class="control-label">From: </label>
                <input type="text" name="sdate" id="sdate" class="form-control" value="<?php echo $request->getRequest('sdate') ? $request->getRequest('sdate'): date(get_report_date_format()." 00:00",strtotime(" -".($report_restriction_days-1)." day")); ?>">
            </div>

            <div class="form-group form-group-sm ">
                <label for="sdate" class="control-label">To: </label>
                <input type="text" name="edate" id="edate" class="form-control" value="<?php echo $request->getRequest('edate') ? $request->getRequest('edate'): date(get_report_date_format()." 00:00",strtotime(" +1 day")); ?>">
            </div>

            <div class="form-group form-group-sm ">
                <label for="interval" class="control-label">Type: </label>
                <select name="interval" id="interval" class="form-control select2">
                   <!-- <option value="quarter-hourly" <?php /*echo $request->getRequest('interval','daily') == 'quarter-hourly' ? 'selected' : '' */?>>15 Minute</option>
                    <option value="half-hourly" <?php /*echo $request->getRequest('interval','daily') == 'half-hourly' ? 'selected' : '' */?>>Half Hourly</option>-->
                    <!-- <option value="hourly" <?php //echo $request->getRequest('interval','daily') == 'hourly' ? 'selected' : '' ?>>Hourly</option> -->
                    <option value="daily" <?php echo $request->getRequest('interval','daily') == 'daily' ? 'selected' : '' ?>>Daily</option>
                    <!-- <option value="monthly" <?php //echo $request->getRequest('interval','daily') == 'monthly' ? 'selected' : '' ?>>Monthly</option> -->
                    <?php /*<option value="quarterly" <?php echo $request->getRequest('interval','daily') == 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
                    <option value="yearly" <?php echo $request->getRequest('interval','daily') == 'yearly' ? 'selected' : '' ?>>Yearly</option> */ ?>
                </select>
            </div>

            <div class="form-group form-group-sm ">
                <label for="skill_id" class="control-label">Skill Type:</label>
                <select name="skill_type" id="skill_type" class="form-control select2">
                    <?php foreach ($skill_type_list as $id => $type): ?>
                        <option value="<?php echo $id; ?>" <?php echo $request->getRequest('skill_type','*') == $id ? 'selected' : '' ?>><?php echo $type; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group form-group-sm hide">
                <label for="skill_id" class="control-label">Skill:</label>
                <select name="skill_id" id="skill_id" class="form-control select2">
                    <?php foreach ($skills as $id => $skill): ?>
                        <option value="<?php echo $id; ?>" <?php echo $request->getRequest('skill_id','*') == $id ? 'selected' : '' ?>><?php echo $skill; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class=" btn btn-md btn-success "><i class=" fa fa-search"></i> Search </button>
            <a href="<?php echo $this->url("task=report-new&act=workcode&download=".md5("CC Workcode Report".session_id())) ?>" id="download-workcode-report" class=" btn btn-md btn-purple"><i class=" fa fa-download"></i> Download</a>
        </form>
        <div class="row">
            <br/>
            <!-- <code class="text-left text-danger">***For Hourly interval end date will be the same as start date and end time will be 23:59:59</code> -->
        </div>


        <div class="table-responsive m-t-10">
            <table class="table table-bordered table-striped table-hover table-condensed service-level-table">
                <thead>
                <tr>
                    <td class="text-nowrap">Workcode</td>
                    <?php $column_count = 1; foreach ($dateinfo as $key => $value) { $column_count++ ?>
                        <td class="text-nowrap">
                            <?php if ($interval == 'quarterly'){
                                $quarter = ceil($value->format('n') /3);
                                echo "Quarter ".$quarter.", ".$value->format(REPORT_YEAR_FORMAT);
                            }else{
                                echo $value->format($date_format_for_header);
                            }  ?>
                        </td>
                    <?php }?>

                </tr>
                </thead>
                <tbody>
                <tr>
                    <?php if (empty($result)): ?>
                        <td class="text-danger" colspan="<?php echo $column_count ?>">No Record Found</td>
                    <?php endif;?>

                </tr>
                <?php
                $answeredCall = "";
                $workcode_count = "";
                $percent = "";
                $count = 0;

                foreach ($result as $data):  ?>
                    <tr>
                        <td class="text-nowrap"><?php echo $data->disposition_id; ?></td>
                        <?php  foreach ($dateinfo as $key => $value): ?>
                            <td class="text-nowrap">
                                <?php
                                //$d = $value->format('Y-m-d');
                                $d = $value->format($date_format_for_row_access);
                                if ($interval == 'quarterly'){
                                    $d .= ceil((int) $value->format('n') / 3);
                                }
                                if ($interval == 'quarter-hourly'){
                                    $d .= str_pad(ceil(((int) $value->format('i') / 15) * 15),2,0);
                                }if ($interval == 'half-hourly'){
                                    $d .= str_pad(ceil(((int) $value->format('i') / 30) * 30),2,0);
                                }

                                echo !empty($data->{$d}->call_count) ? $data->{$d}->call_count : 0;


                                if ($count < 1){
                                    $answeredCall .= !empty($summary[$d]->calls_answered) ? "<th class='text-nowrap'>{$summary[$d]->calls_answered}</th>" : "<th class='text-nowrap'>0</th>";
                                    $workcode_count .= !empty($summary[$d]->disposition_count) ? "<th class='text-nowrap'>{$summary[$d]->disposition_count}</th>" : "<th class='text-nowrap'>0</th>";
                                    $percent .= !empty($summary[$d]->disposition_percent) ? "<th class='text-nowrap'>{$summary[$d]->disposition_percent}</th>" : "<th class='text-nowrap'>0</th>";
                                }


                                ?>
                            </td>
                        <?php endforeach; $count++; ?>
                    </tr>
                <?php endforeach; ?>
                <tr><th class="text-nowrap">Workcode Count</th><?php echo $workcode_count; ?></tr>
                <tr><th class="text-nowrap">Answered Call</th> <?php echo $answeredCall; ?></tr>
                <tr><th class="text-nowrap">Percent(%)</th><?php echo $percent; ?></tr>
                </tbody>
            </table>
        </div>
    </div>

    </div>
</div>

<script type="text/javascript">
    var report_date_format = '<?php echo $report_date_format; ?>';
    var report_restriction_days = parseInt(<?php echo empty($report_restriction_days) ? '' : $report_restriction_days; ?>);

    $(function(){
        prepareDownloadUrl();
        var current_date = new Date();
        var default_date = '';
        default_date = current_date.getDate()+'/0'+(+current_date.getMonth()+1)+'/'+current_date.getFullYear();

        checkToDisableEndDate();
        $("#sdate").datetimepicker({
            defaultDate: new Date(),
            format: report_date_format+" H:00"
        });
        $("#edate").datetimepicker({
            defaultDate: new Date(),
            format: report_date_format+" H:00"
        });

        $(document).on('change','#interval',function () {
            checkToDisableEndDate();
            prepareDownloadUrl();
        });
        $(document).on('change','#date_format',function () {
            prepareDownloadUrl();
        });

        $(document).on('change','#sdate',function () {
            prepareDownloadUrl();
        });
        $(document).on('change','#edate',function () {
            prepareDownloadUrl();
        });
        $(document).on('change','#skill_id',function () {
            prepareDownloadUrl();
        });

        $(document).on('click','#download-workcode-report',function () {
            $("#MainLoader").remove();
        });

        $("#workcode-form").on("submit", function (e) {
            // return false;
            try {
                if(!beforeFormSubmit(report_restriction_days)){
                    return false;
                }
            }catch(e){
                console.log(e.message);
            }
            // return false;
        });

        $(document).on("click","a#download-workcode-report",function () {
            try {
                if(!beforeFormSubmit(report_restriction_days)){
                    return false;
                }
            }catch(e){
                console.log(e.message);
            }
        });

     /*  $("#date_format").on('change', function(){
            var df = $(this).val();
            var current_date = '';
            current_date = new Date();
            var default_date = '';

            if(df == 'd/m/Y'){
                default_date = current_date.getDate()+'/0'+(+current_date.getMonth()+1)+'/'+current_date.getFullYear();
            }else if(df == 'm/d/Y'){
                default_date = '0'+(+current_date.getMonth()+1)+'/'+current_date.getDate()+'/'+current_date.getFullYear();
            }

            try{
                //class for report date time--------------
                $("#sdate").each(function(e){
                    $(this).datetimepicker({
                        pickTime: true,
                        timepicker: true,
                        useStrict: true,
                        startDate: new Date(),
                        defaultTime:'00:00',
                        format: df+" H:00"
                    });
                    $(this).val(default_date+" 00:00");
                });
                $("#edate").each(function(e){
                    $(this).datetimepicker({
                        pickTime: true,
                        timepicker:true,
                        useStrict:true,
                        startDate: new Date(),
                        defaultTime:'23:00',
                        format: df+" H:00",
                        allowTimes:['00:00', '01:00', '02:00','03:00', '04:00', '05:00', '06:00', '07:00','08:00', '09:00', '10:00','11:00', '12:00', '13:00', '14:00', '15:00','16:00', '17:00','18:00', '19:00', '20:00', '22:00', '23:00']
                        // allowTimes:['00:59', '01:59', '02:59','03:59', '04:59', '05:59', '06:59', '07:59','08:59', '09:59', '10:59','11:59', '12:59', '13:59', '14:59', '15:59','16:59', '17:59','18:59', '19:59', '20:59', '22:59', '23:59']
                    });
                    $(this).val(default_date+" 23:00");
                });
                //----------------
            }catch(e){
                gcl(e.message,true);
            }
        });*/

    });

    function prepareDownloadUrl(){
        $downloadButton = $("#download-workcode-report");
        var url = window.location.href;
        var interval =  $("#interval").val();
        var date_format = report_date_format; //$("#date_format option:selected").val();
        var sdate = $("#sdate").val();
        var edate = $("#edate").val();
        var skill_id = $("#skill_id").val();
        var skill_type = $("#skill_type").val();
        var download = "<?php echo md5("CC Workcode Report".session_id()) ?>";

         url += '&interval='+ interval + '&date_format='+encodeURIComponent(date_format)+
             '&sdate='+encodeURIComponent(sdate)+ '&edate='+ encodeURIComponent(edate) +
             '&skill_id='+encodeURIComponent(skill_id)+'&skill_type='+encodeURIComponent(skill_type)+'&download=' + download;
        $downloadButton.attr('href',url);
    }

    function checkToDisableEndDate(){
        var restricted = ['quarter-hourly','half-hourly','hourly'];
        var selected =  $("#interval").val();
        if (restricted.includes(selected)){
            $("#edate").hide();
        } else{
            $("#edate").show();
        }
    }

    function beforeFormSubmit(day){
        var start_date = $('#sdate').val();
        var end_date = $('#edate').val();

        var start = start_date.split(' ');
        var start_date = start[0];
        var start_time = (typeof start[1] == 'undefined' ) ? '' : start[1];

        if ('d/m/Y' == report_date_format){
            var start_day = start_date.split('/')[0];
            var start_month = start_date.split('/')[1];
            var start_year = start_date.split('/')[2];
        }else if ('m/d/Y' == report_date_format){
            var start_day = start_date.split('/')[1];
            var start_month = start_date.split('/')[0];
            var start_year = start_date.split('/')[2];
        }

        var d = '';
        if(start_time){
            d = new Date(start_year, start_month -1 , start_day, start_time.split(':')[0], start_time.split(':')[1]);
        }else{
            d = new Date(start_year, start_month -1 , start_day);
        }
        // console.log(d);

        var end = end_date.split(' ');
        var end_date = end[0];
        var end_time = (typeof end[1] == 'undefined' ) ? '' : end[1];

        if ('d/m/Y' == report_date_format){
            var end_day = end_date.split('/')[0];
            var end_month = end_date.split('/')[1];
            var end_year = end_date.split('/')[2];
        }else if ('m/d/Y' == report_date_format){
            var end_day = end_date.split('/')[1];
            var end_month = end_date.split('/')[0];
            var end_year = end_date.split('/')[2];
        }

        var d2 = '';
        if(start_time){
            d2 = new Date(end_year, end_month -1 , end_day, end_time.split(':')[0], end_time.split(':')[1]);
        }else{
            d2 = new Date(end_year, end_month -1 , end_day);
        }
        // console.log(d2);
        // console.log(day);
        // console.log(1000*60*60*parseInt(day)*24);
        if ((d2.getTime() - d.getTime()) / (1000*60*60*day*24) > 1) {
            alert('Provide date range within '+day+' days');
            return false;
        }

        return true;
    }


</script>

