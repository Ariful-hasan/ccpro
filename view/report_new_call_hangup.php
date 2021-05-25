
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
                <?php 
                // GPrint($report_type);
                // GPrint($skill_type_list);
                // GPrint($skill_type);
                ?>
                <form id="call-hangup-form" action="<?php echo $this->url("task=report-new&act=call-hang-up-report"); ?>" class="form-inline" method="post">

                    <div class="form-group form-group-sm ">
                        <label for="sdate" class="control-label"> From: </label>
                        <input type="text" name="sdate" id="sdate" class="form-control" value="<?php echo !empty($request->getRequest('sdate') )? $request->getRequest('sdate') : date($date_format." 00:00", strtotime('-7day'))?>" autocomplete="off">
                    </div>

                    <div id="to_date" class="form-group form-group-sm <?php echo $report_type=='RHR' ? 'hide':'' ?>">
                        <label for="edate" class="control-label"> To: </label>
                        <input type="text" name="edate" id="edate" class="form-control" value="<?php echo !empty($request->getRequest('edate')) ? $request->getRequest('edate') : date($date_format." 00:00");?>" autocomplete="off">
                    </div>

                    <div class="form-group form-group-sm ">
                        <label for="report_type" class="control-label"> Type: </label>
                        <select name="report_type" id="report_type" class="form-control">
                            <?php foreach ($report_type_list as  $key => $title): ?>
                                <option value="<?php echo $key; ?>" <?php echo $key == $report_type ? 'selected' : ''; ?>><?php echo $title; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group form-group-sm ">
                        <label for="skill_type" class="control-label">Skill Type: </label>
                        <select name="skill_type" id="skill_type" class="form-control">
                            <?php foreach ($skill_type_list as  $key => $type): ?>
                                <option value="<?php echo $key; ?>" <?php echo $key == $skill_type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class=" btn btn-md btn-success "><i class=" fa fa-search"></i> Search</button>
                    <a href="" id="download-call-hang-up-report" class=" btn btn-md btn-purple pull-right"><i class=" fa fa-download"></i> Download</a>
                </form>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">Result</div>
            <div class="panel-body">
                <div class="table-responsive">
                    <?php 
                    // GPrint($dateList); 
                    // GPrint($results); 
                    // GPrint($total_ans_call); 
                    ?>
                    <table class="table table-bordered table-striped table-hover table-condensed service-level-table">
                        <thead>
                            <tr>
                                <th>Hangup Initiator</th>
                                <?php foreach ($dateList as $date):?>
                                    <th><?php echo $date; ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($results)): ?>
                            <tr>
                                <td class="text-danger"
                                    colspan="<?php echo count($dateList) + 1 ; ?>">
                                    No Record Found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php
                            foreach ($hangup_ini_list as $key => $value) { 
                                ?>
                                <tr>
                                    <td><?php echo isset($get_disc_party[$value]) ? $get_disc_party[$value] : (!empty($value) ? $value : 'Others'); ?></td>
                                    <?php foreach ($dateList as $key => $item) { ?>
                                        <td>
                                            <?php
                                            if(isset($results[$item.'_'.$value])){
                                                echo $results[$item.'_'.$value]['hang_up_count'];
                                            }else{
                                                echo 0;
                                            }
                                            ?>                                                
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php } ?>
                                <tr>
                                    <td>Total Answer Calls</td>
                                    <?php foreach ($dateList as $key => $item) { ?>
                                        <td>
                                            <?php echo (isset($total_ans_call[$item]->total_ans_call) && !empty($total_ans_call[$item]->total_ans_call)) ? $total_ans_call[$item]->total_ans_call : 0; ?>
                                        </td>
                                    <?php } ?>
                                </tr>
                                <tr>
                                    <td>Total Offered Calls</td>
                                    <?php foreach ($dateList as $key => $item) { ?>
                                        <td>
                                            <?php echo (isset($total_ans_call[$item]->total_offer_call) && !empty($total_ans_call[$item]->total_offer_call)) ? $total_ans_call[$item]->total_offer_call : 0; ?>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php foreach ($hangup_ini_list as $key => $value) { ?>
                                <tr>
                                    <td><?php echo $get_disc_party[$value].' Calls Hangup %'; ?></td>
                                    <?php 
                                    $count = 0;
                                    foreach ($dateList as $key => $item) { ?>
                                        <td>
                                            <?php
                                            // var_dump($results[$item.'_'.$value]['hang_up_count']);
                                            // var_dump($total_ans_call[$item]->total_ans_call);
                                            if(isset($results[$item.'_'.$value]) && !empty($total_ans_call[$item]->total_offer_call)){
                                                echo sprintf('%.2f', ($results[$item.'_'.$value]['hang_up_count']/$total_ans_call[$item]->total_offer_call)*100).'%';   
                                            }else{                                                
                                                echo '0.00%';
                                            }
                                            $count++;
                                            ?>                                                
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php } ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

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
        date_format = '<?php echo $date_format ?>';

        if (date_format == 'd/m/Y') {
            current_date.setDate(current_date.getDate() - report_restriction_days);
            var month = +current_date.getMonth()+1;
            month = ((''+month).length==1 ? '0'+month : month);
            var day = current_date.getDate();
            day = ((''+day).length==1 ? '0'+day : day);
            start_default_date = day+'/'+month+'/'+current_date.getFullYear();

            current_date.setDate(current_date.getDate() + report_restriction_days);
            month = +current_date.getMonth()+1;
            month = ((''+month).length==1 ? '0'+month : month); 
            day = current_date.getDate();
            day = ((''+day).length==1 ? '0'+day : day);
            end_default_date = day+'/'+month+'/'+current_date.getFullYear();
        } else if (date_format == 'm/d/Y') {
            current_date.setDate(current_date.getDate() - report_restriction_days);
            var month = +current_date.getMonth()+1;
            month = ((''+month).length==1 ? '0'+month : month);
            var day = current_date.getDate();
            day = ((''+day).length==1 ? '0'+day : day);
            start_default_date = month+'/'+day+'/'+current_date.getFullYear();
            
            current_date.setDate(current_date.getDate() + report_restriction_days);
            month = +current_date.getMonth()+1;
            month = ((''+month).length==1 ? '0'+month : month); 
            day = current_date.getDate();
            day = ((''+day).length==1 ? '0'+day : day);
            end_default_date = month+'/'+day+'/'+current_date.getFullYear();
        }

        $("#sdate").datetimepicker({
            pickTime: true,
            timepicker: true,
            useStrict: true,
            // startDate: new Date(),
            // defaultTime: '00:00',
            format: "<?php echo $date_format;?> H:00"
        });
        $("#sdate").val(start_default_date + " 00:00");
        <?php if(!empty($request->getRequest('sdate'))):?>
        $("#sdate").val("<?php echo $request->getRequest('sdate');?>");
        <?php endif; ?>
        $("#edate").datetimepicker({
            pickTime: true,
            timepicker: true,
            useStrict: true,
            // startDate: new Date(),
            // defaultTime: '00:00',
            format: "<?php echo $date_format;?> H:00"
        });
        $("#edate").val(end_default_date + " 00:00");
        <?php if(!empty($request->getRequest('edate'))):?>
        $("#edate").val("<?php echo $request->getRequest('edate');?>");
        <?php endif; ?>

        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });

        $(document).on("change","#sdate, #edate, #skill_id, #sum_date, #sum_hour",function () {
            prepareServiceLevelDownloadUrl();
        });

        $(document).on("click","#sdate, #edate, #skill_id, #sum_date, #sum_hour",function () {
            prepareServiceLevelDownloadUrl();
        });

        $(document).on("click","#download-call-hang-up-report",function () {
            $("#MainLoader").remove();
        });

        $(document).on("submit","#call-hangup-form",function () {
            try {
                if(!beforeFormSubmit(report_restriction_days)){
                    return false;
                }
            }catch(e){
                console.log(e.message);
            }
        });

        $(document).on("click","a#download-call-hang-up-report",function () {
            try {
                if(!beforeFormSubmit(report_restriction_days)){
                    return false;
                }
            }catch(e){
                console.log(e.message);
            }
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
        var date_format = "<?php echo $date_format?>";
        var skill_type = $("#skill_type").val();
        generateServiceLevelDownloadUrl(sdate, edate, report_type, date_format, skill_type);
    }

    function generateServiceLevelDownloadUrl(sdate, edate, report_type, date_format, skill_type)
    {
        var url = '<?php echo $this->url("task=report-new&act=download-call-hang-up-report&download=".md5("Call Hang Up Report".session_id())) ?>';
        url += "&sdate="+sdate+"&edate="+edate+"&report_type="+report_type+"&date_format="+date_format+"&skill_type="+skill_type;
        $("#download-call-hang-up-report").attr('href',url);
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

