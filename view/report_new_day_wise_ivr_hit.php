<style>
    .service-level-table thead tr{background-color: #f1f1f1;}
    .service-level-table td,.service-level-table th{min-width: 90px !important;text-align: center; padding: 5px !important;}
    .control-label{position: relative}
    .gs-check-box{width: 28px !important; height: 28px !important; }
    .check-box-title{position: relative; top: -10px;  vertical-align: middle; font-weight: 700}
</style>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Search</div>
            <div class="panel-body">

                <form id="ivr-hit-form" action="<?php echo $this->url("task=report-new&act=day-wise-ivr-hit-report"); ?>" class="form-inline" method="post">

                    <div class="form-group ">
                        <label for="sdate" class="control-label"> From: </label>
                        <input type="text" name="sdate" id="sdate" class="form-control" value="<?php echo !empty($request->getRequest('sdate')) ? $request->getRequest('sdate') : date($report_date_format) ?>" autocomplete="off">
                    </div>

                    <div class="form-group ">
                        <label for="edate" class="control-label"> To: </label>
                        <input type="text" name="edate" id="edate" class="form-control" value="<?php echo !empty($request->getRequest('edate')) ? $request->getRequest('edate') : date($report_date_format, strtotime("+1day")) ?>" autocomplete="off">
                    </div>


                    <div class="form-group ">
                        <label for="skill_type" class="control-label">IVR: </label>
                        <select name="ivr_id" id="ivr_id" class="form-control">
                            <?php foreach ($ivr_list as $key => $ivr): ?>
                                <option value="<?php echo $key ?>" <?php echo ($key === $ivr_id) ? 'selected' : '';?>> <?php echo $ivr; ?> </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class=" btn btn-md btn-success "><i class=" fa fa-search"></i> Search</button>
                    <a href="" id="download-daily-ivr-hit-report" class=" btn btn-md btn-purple pull-right"><i class=" fa fa-download"></i> Download</a>
                </form>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">Result</div>
            <div class="panel-body">
                <div class="table-responsive">

                    <table class="table table-bordered table-striped table-hover table-condensed service-level-table">
                        <thead>
                        <tr>
                            <?php foreach ($table_headings as $heading): ?>
                                <td><b><?php echo $heading; ?></b></td>
                            <?php endforeach; ?>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($results as $result): ?>
                            <tr>
                                <td><?php echo $result->ivr_node; ?></td>
                                <td><?php echo $result->integration_info; ?></td>
                                <td><?php echo $result->parent_node; ?></td>
                                <?php foreach ($dates as $date): ?>
                                    <td><?php echo $result->{$date}; ?></td>
                                <?php endforeach; ?>

                            </tr>
                        <?php endforeach; ?>
                        </tbody>

                    </table>

                </div>
                <p class="text-danger text-center"><?php echo $input_err_msg ;?></p>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">    
    var report_restriction_days = parseInt(<?php echo empty($report_restriction_days) ? '' : $report_restriction_days; ?>);

    $(function () {
        prepareServiceLevelDownloadUrl();

        var current_date = new Date();
        var start_default_date = '';
        var end_default_date = '';
        date_format = '<?php echo $report_date_format ?>';
        if (date_format == 'd/m/Y') {
            current_date.setDate(current_date.getDate());
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

        } else if (date_format == 'm/d/Y') {
            current_date.setDate(current_date.getDate());
            var month = +current_date.getMonth()+1;
            month = ((''+month).length==1 ? '0'+month : month);
            var day = current_date.getDate();
            day = ((''+day).length==1 ? '0'+day : day);
            start_default_date = month+'/'+day+'/'+current_date.getFullYear();

            current_date.setDate(current_date.getDate() + 1);
            month = +current_date.getMonth()+1;
            month = ((''+month).length==1 ? '0'+month : month);
            day = current_date.getDate();
            day = ((''+day).length==1 ? '0'+day : day);
            end_default_date = month+'/'+day+'/'+current_date.getFullYear();

        }

        $("#sdate").datetimepicker({
            pickTime: true,
            timepicker: false,
            useStrict: true,
            format: "<?php echo $report_date_format;?>"
        });
        $("#sdate").val(start_default_date);
        <?php if(!empty($request->getRequest('sdate'))):?>
        $("#sdate").val("<?php echo $request->getRequest('sdate');?>");
        <?php endif; ?>

        $("#edate").datetimepicker({
            pickTime: true,
            timepicker: false,
            useStrict: true,
            format: "<?php echo $report_date_format;?>"
        });
        $("#edate").val(end_default_date);
        <?php if(!empty($request->getRequest('edate'))):?>
        $("#edate").val("<?php echo $request->getRequest('edate');?>");
        <?php endif; ?>

        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });

        $(document).on("change","#sdate, #edate, #skill_id, #sum_date, #sum_hour,#ivr_id",function () {
            prepareServiceLevelDownloadUrl();
        });

        $(document).on("click","#sdate, #edate, #skill_id, #sum_date, #sum_hour, #ivr_id",function () {
            prepareServiceLevelDownloadUrl();
        });

        $(document).on("click","#download-daily-ivr-hit-report",function () {
            $("#MainLoader").remove();
        });

        $("#ivr-hit-form").on("submit", function (e) {
            try {
                if(!beforeFormSubmit(report_restriction_days)){
                    return false;
                }
            }catch(e){
                console.log(e.message);
            }
            // return false;
        });

        $("a#download-daily-ivr-hit-report").on("click", function (e) {
            try {
                if(!beforeFormSubmit(report_restriction_days)){
                    return false;
                }
            }catch(e){
                console.log(e.message);
            }
            // return false;
        });

        $("#report_type").on('change', function(){
            var val = $(this).val();
            if(val == 'RHR'){
                $("div#to_date").addClass('hide');
            }else{
                $("div#to_date").removeClass('hide');
            }
        });
    });

    function prepareServiceLevelDownloadUrl(){
        var sdate = $("#sdate").val();
        var edate = $("#edate").val();
        var report_type = $("#report_type").val();
        var date_format = "<?php echo $report_date_format?>";
        var skill_type = $("#skill_type").val();
        var ivr_id = $("#ivr_id ").val();
        generateServiceLevelDownloadUrl(sdate, edate, report_type, date_format, skill_type, ivr_id);
    }

    function generateServiceLevelDownloadUrl(sdate, edate, report_type, date_format, skill_type, ivr_id)
    {
        var url = '<?php echo $this->url("task=report-new&act=day-wise-ivr-hit-report&download=".md5("Daily Ivr Hit Report".session_id())) ?>';
        url += "&sdate="+sdate+"&edate="+edate+"&report_type="+report_type+"&date_format="+date_format+"&skill_type="+skill_type+"&ivr_id="+ivr_id;
        $("#download-daily-ivr-hit-report").attr('href',url);
    }

    function beforeFormSubmit(day){
        var start_date = $('#sdate').val();
        var end_date = $('#edate').val();

        var start = start_date.split(' ');
        var start_date = start[0];
        var start_time = (typeof start[1] == 'undefined' ) ? '' : start[1];

        if ('d/m/Y' == date_format){
            var start_day = start_date.split('/')[0];
            var start_month = start_date.split('/')[1];
            var start_year = start_date.split('/')[2];
        }else if ('m/d/Y' == date_format){
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

        if ('d/m/Y' == date_format){
            var end_day = end_date.split('/')[0];
            var end_month = end_date.split('/')[1];
            var end_year = end_date.split('/')[2];
        }else if ('m/d/Y' == date_format){
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
