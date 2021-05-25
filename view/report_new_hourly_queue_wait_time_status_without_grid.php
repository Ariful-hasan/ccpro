<style type="text/css">

    .service-level-table thead tr{background-color: #f1f1f1;}
    .service-level-table td,.service-level-table th{min-width: 90px !important;text-align: center; padding: 5px !important;}
    .control-label{position: relative}
    .gs-check-box{width: 28px !important; height: 28px !important; }
    .check-box-title{position: relative; top: -10px;  vertical-align: middle; font-weight: 700}
</style>

<?php
//GPrint(date("Y-m-d H:00",strtotime($request->getRequest('sdate'))));die;
?>

<div class="panel panel-default">
    <div class="panel-heading">Search Area
        <div class="pull-right">
            <button class="btn btn-sm btn-info" id="refresh" style="margin-right: 5px"><i class="fa fa-refresh" aria-hidden="true"></i> Refresh</button>
            <a target="" href="" id="download-hourly-queue-wait-time" class=" btn btn-md btn-purple pull-right" style="padding-top: 1px"><i class=" fa fa-download"></i> Download</a>
        </div>
    </div>
    <div class="panel-body">
        <form action="<?php echo $this->url("task=report-new&act=hourly-queue-wait-time-new"); ?>" class="form-inline" method="post">


            <div class="form-group form-group-sm ">
                <label for="sdate" class="control-label">From: </label>
                <!--<input type="text" name="sdate" id="sdate" class="form-control" value="<?php /*echo $request->getRequest('sdate') ? date("Y-m-d H:00",strtotime($request->getRequest('sdate'))) : date("Y-m-d 00:00")*/?>">-->
                <input type="text" name="sdate" id="sdate" class="form-control" value="<?php echo $from?>" autocomplete="off">
            </div>

            <div class="form-group form-group-sm ">
                <label for="sdate" class="control-label">To: </label>
                <!--<input type="text" name="edate" id="edate" class="form-control" value="<?php /*echo $request->getRequest('edate') ? date("Y-m-d H:00",strtotime($request->getRequest('edate'))) : date("Y-m-d 23:00")*/?>">-->
                <input type="text" name="edate" id="edate" class="form-control" value="<?php echo $to?>" autocomplete="off">
            </div>

            <div class="form-group form-group-sm ">
                <label for="sdate" class="control-label">Skill</label>
                <select name="skill_id" id="skill_id" class="form-control">
                    <option value="*">All</option>
                    <?php foreach ($skills as  $key => $title): ?>
                        <option value="<?php echo $key; ?>" <?php echo $key == $skill_id ? 'selected' : ''; ?>><?php echo $title; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group form-group-sm ">
                <label for="sdate" class="control-label">Average</label>
                <select name="average_condition" id="average_condition" class="form-control">
                    <option value="">Select</option>
                    <?php foreach ($average_combo as  $key => $title): ?>
                        <option value="<?php echo $key; ?>" <?php echo $key == $average_condition ? 'selected' : ''; ?>><?php echo $title; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>


            <div class="" style="display: inline-block">
                <label class="control-label"><input type="radio" class="gs-check-box" name="avg_method" value="CO" <?php echo $avg_method == "CO" ? 'checked' : '';?> > <span class="check-box-title">Calls Offered</span></label>
                <label class="control-label"><input type="radio" class="gs-check-box" name="avg_method" value="CA" <?php echo $avg_method == "CA" ? 'checked' : '';?> > <span class="check-box-title">Calls Answered</span></label>
            </div>

            <button type="submit" class=" btn btn-md btn-success pull-right " style="margin-left: 10px"><i class=" fa fa-search"></i> Search</button>
            <!--<a href="" id="download-service-level" class=" btn btn-md btn-purple pull-right"><i class=" fa fa-download"></i> Download</a>-->

        </form>
    </div>
</div>

<?php if ($average_condition!="HA" && $average_condition!="HD"){?>
<table class="table" style="margin-bottom: 0px">
    <tr>
        <td class="col-md-4">
            <?php
            echo 'Record(s) ' . $pagination->getCurrentRecordsIndex() . ' of <b>' . $pagination->num_records . '</b> &nbsp;::&nbsp; ' .
                'Page <b>' . $pagination->current_page . '</b> of <b>' . $pagination->getTotalPageCount() . '</b>';
            ?>
        </td>
    </tr>
</table>
<?php }?>

<?php
function setHourHead($start_time, $end_time){
    foreach (range($start_time,$end_time,1) as $hour){
        $str = $hour < 12 ? " AM" : " PM";
        $hour = $hour < 10 ? "0" . $hour. $str : $hour. $str;
        echo "<th>$hour</th>";
    }
}
?>

<div class="panel panel-default">
    <div class="panel-heading">Result Area</div>
    <div class="panel-body">
        <div class="table-responsive" style="margin-bottom: 1%">
            <table class="table table-bordered table-striped table-hover table-condensed service-level-table">
                <thead>
                <tr>
                    <?php $col_span = 2; ?>
                    <?php if (!empty($average_condition) && $average_condition=="SA"){ ?>
                        <th>Skill</th>
                        <?php setHourHead($dateinfo->stime,$dateinfo->etime)?>
                    <?php } elseif (!empty($average_condition) && $average_condition=="DA"){?>
                        <th>Date</th>
                        <th>Skill</th>
                        <th>Average</th>
                    <?php } elseif (!empty($average_condition) && $average_condition=="HA"){?>
                        <?php setHourHead($dateinfo->stime,$dateinfo->etime)?>
                    <?php } elseif (!empty($result) && !empty($average_condition) && $average_condition=="HD"){?>
                        <th>Average</th>
                    <?php } elseif (!empty($average_condition) && $average_condition=="SH"){?>
                        <th>Skill</th>
                        <th>Average</th>
                    <?php } else { ?>
                        <th>Date</th>
                        <th>Skill</th>
                        <?php setHourHead($dateinfo->stime,$dateinfo->etime)?>
                    <?php } ?>

                </tr>
                </thead>
                <tbody>

                <?php if (!empty($result) && !empty($average_condition) && $average_condition=="SA"){ ?>
                    <?php foreach ($result as $key => $value){?>
                        <tr>
                        <td><?php echo $key?></td>
                        <?php foreach (range($dateinfo->stime,$dateinfo->etime,1) as $hour):
                            $hour = $hour < 10 ? "shour_0" . $hour : "shour_".$hour;
                            ?>
                            <td><?php echo $value[$hour]?></td>
                        <?php endforeach;?>
                        </tr>
                    <?php }?>
                <?php } elseif (!empty($result) && !empty($average_condition) && $average_condition=="DA"){?>
                    <?php foreach ($result as $key => $value):
                         list($sdate, $skill) = explode("_",$key);
                        ?>
                        <tr>
                            <td><?php echo $sdate?></td>
                            <td><?php echo $skill?></td>
                            <td><?php echo $value?></td>
                        </tr>
                    <?php endforeach;?>
                <?php } elseif (!empty($result) && !empty($average_condition) && $average_condition=="HA") {?>
                    <tr>
                        <?php foreach (range($dateinfo->stime,$dateinfo->etime,1) as $hour):
                            $hour = $hour < 10 ? "shour_0" . $hour : "shour_".$hour;//shour_00
                            ?>
                            <td><?php echo !empty($result)?$result[$hour]:""?></td>
                        <?php endforeach;?>
                    </tr>
                <?php } elseif (!empty($result) && !empty($average_condition) && $average_condition=="HD"){?>
                    <tr>
                        <td><?php echo $result?></td>
                    </tr>
                <?php } elseif (!empty($result) && !empty($average_condition) && $average_condition=="SH"){?>
                    <?php foreach ($result as $key => $value):?>
                        <tr>
                            <td><?php echo $key?></td>
                            <td><?php echo $value?></td>
                        </tr>
                    <?php endforeach;?>
                <?php } elseif (!empty($result) ) {?>
                    <?php foreach ($result as $key):?>
                        <tr>
                            <td><?php echo $key->sdate?></td>
                            <td><?php echo $key->skill_id?></td>
                            <?php foreach (range($dateinfo->stime,$dateinfo->etime,1) as $hour):
                                $hour = $hour < 10 ? "shour_0" . $hour : "shour_".$hour;
                            ?>
                            <td><?php echo $key->$hour?></td>
                            <?php endforeach;?>
                        </tr>
                    <?php endforeach;?>
                <?php }?>


                <tr>
                    <?php if ( empty($result)): ?>
                        <td class="text-danger" colspan="<?php echo ($dateinfo->etime - $dateinfo->stime)+1 + $col_span; ?>">No Record Found</td>
                    <?php endif;?>
                </tr>
                </tbody>
            </table>
        </div>
        <?php
            if ($average_condition!="HA" && $average_condition!="HD")
            echo $pagination->createLinks();
        ?>
        <!--<kbd><?php /*echo $service_level_calculation_method; */?></kbd> <br>-->
    </div>
</div>


<script type="text/javascript">
    $(function(){
        prepareServiceLevelDownloadUrl();

        var current_date = new Date();
        var start_default_date = '';
        var end_default_date = '';
        date_format = '<?php echo $date_format ?>';

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
            timepicker: true,
            useStrict: true,
            // startDate: new Date(),
            // defaultTime: '00:00',
            format: "<?php echo $date_format;?> 00:00"
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
            format: "<?php echo $date_format;?> 00:00"
        });
        $("#edate").val(end_default_date + " 00:00");
        <?php if(!empty($request->getRequest('edate'))):?>
        $("#edate").val("<?php echo $request->getRequest('edate');?>");
        <?php endif; ?>

        $("#refresh").on('click', function (e) {
            window.location.href = "<?php echo $this->url("task=report-new&act=hourly-queue-wait-time-new"); ?>";
        });

        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });

        $(document).on("change","#sdate, #edate, #skill_id, #sum_date, #sum_hour",function () {
            //prepareServiceLevelDownloadUrl();
        });

        $(document).on("click","#download-hourly-queue-wait-time",function () {
            $("#MainLoader").remove();
        });
    });

    function prepareServiceLevelDownloadUrl() {
        var sdate = $("#sdate").val();
        var edate = $("#edate").val();
        var skill_id = $("#skill_id").val();
        var sum_date = $("#sum_date").is(":checked") ? "Y" : "N";
        var sum_hour = $("#sum_hour").is(":checked") ? "Y" : "N";
        var date_format = $("#date_format").val();

        generateServiceLevelDownloadUrl(sdate, edate, sum_date, sum_hour, skill_id, date_format);
    }

    function generateServiceLevelDownloadUrl(sdate,edate,sum_date,sum_hour,skill_id,date_format)
    {

        var url = '<?php echo $this->url("task=report-new&act=download-hourly-queue-wait-time&download=".md5("Hourly Queue Wait Time".session_id())."&param=".$param_link) ?>';
        $("#download-hourly-queue-wait-time").attr('href', url );

        //var url = '<?php //echo $this->url("task=report-new&act=download-service-level&download=".md5("Service Level".session_id())) ?>';
        //url += "&sdate="+sdate+"&edate="+edate+"&sum_date="+sum_date+"&sum_hour="+sum_hour+"&skill_id="+skill_id;
        //$("#download-service-level").attr('href',url);
    }

</script>
