
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

                <form action="<?php echo $this->url("task=report-new&act=daily-ivr-hit-report"); ?>" class="form-inline" method="post">

                    <div class="form-group ">
                        <label for="sdate" class="control-label"> Date: </label>
                        <input type="text" name="sdate" id="sdate" class="form-control" value="<?php echo !empty($request->getRequest('sdate') )? $request->getRequest('sdate') : date($report_date_format)?>" autocomplete="off">
                    </div>

                    <div class="form-group ">
                        <label for="skill_type" class="control-label">IVR: </label>
                        <select name="ivr_id" id="ivr_id" class="form-control">
                            <?php foreach ($ivr_list as $key => $ivr): ?>
                                <option value="<?php echo $key ?>" <?php echo ($key === $ivr_id) ? 'selected' : ''; ?>> <?php echo $ivr; ?> </option>
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
                            <td></td>
                            <td></td>
                            <td></td>
                            <td colspan="2"><?php echo $sdate ?></td>
                            <td colspan="2"><?php echo $previous_date ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>IVR Nodes</td>
                            <td>Integration info of Node</td>
                            <td>Node No.</td>
                            <td>Count</td>
                            <td>Rank</td>
                            <td>Count</td>
                            <td>Rank</td>
                            <td>Comparison <br/>(+ increase & - decrease)</td>
                            <td>Based on Count: <br/> (+ increase & - decrease)</td>
                            <td>% on Total <br/> IVR Info Hits</td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($results as $result): ?>
                            <tr>
                                <td><?php echo $result->service_title; ?></td>
                                <td><?php echo $result->integration_info; ?></td>
                                <td><?php echo $result->parent_node; ?></td>
                                <td><?php echo $result->current_hit_count; ?></td>
                                <td><?php echo $result->current_rank; ?></td>
                                <td><?php echo $result->previous_hit_count; ?></td>
                                <td><?php echo $result->previous_rank; ?></td>
                                <td><?php echo $result->comparison; ?></td>
                                <td><?php echo $result->based_on_count; ?></td>
                                <td><?php echo $result->percentage_of_total_ivr_hit; ?></td>
                            </tr>
                        <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
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

        } else if (date_format == 'm/d/Y') {
            current_date.setDate(current_date.getDate());
            var month = +current_date.getMonth()+1;
            month = ((''+month).length==1 ? '0'+month : month);
            var day = current_date.getDate();
            day = ((''+day).length==1 ? '0'+day : day);
            start_default_date = month+'/'+day+'/'+current_date.getFullYear();
        }

        $("#sdate").datetimepicker({
            pickTime: true,
            timepicker: false,
            useStrict: true,
            format: "<?php echo $report_date_format;?>"
        });
        $("#sdate").val(start_default_date );
        <?php if(!empty($request->getRequest('sdate'))):?>
        $("#sdate").val("<?php echo $request->getRequest('sdate');?>");
        <?php endif; ?>

        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });

        $(document).on("change","#sdate, #edate, #skill_id, #sum_date, #sum_hour, #ivr_id",function () {
            prepareServiceLevelDownloadUrl();
        });

        $(document).on("click","#sdate, #edate, #skill_id, #sum_date, #sum_hour, #ivr_id",function () {
            prepareServiceLevelDownloadUrl();
        });

        $(document).on("click","#download-daily-ivr-hit-report",function () {
            $("#MainLoader").remove();
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
        var url = '<?php echo $this->url("task=report-new&act=daily-ivr-hit-report&download=".md5("Daily Ivr Hit Report".session_id())) ?>';
        url += "&sdate="+sdate+"&edate="+edate+"&report_type="+report_type+"&date_format="+date_format+"&skill_type="+skill_type+"&ivr_id="+ivr_id;
        $("#download-daily-ivr-hit-report").attr('href',url);
    }
</script>

