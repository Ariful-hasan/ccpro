
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
            <div class="panel-heading">Search Area</div>
            <div class="panel-body">
                <form action="<?php echo $this->url("task=report-new&act=service-level"); ?>" class="form-inline" method="post">

                    <div class="form-group form-group-sm ">
                        <label for="sdate" class="control-label">From: </label>
<!--                        <input type="text" name="sdate" id="sdate" class="form-control" value="--><?php //echo $request->getRequest('sdate') ? date("Y-m-d H:00",strtotime($request->getRequest('sdate'))) : date("Y-m-d 00:00")?><!--">-->
                        <input type="text" name="sdate" id="sdate" class="form-control" value="<?php echo $request->getRequest('sdate') ? $request->getRequest('sdate') : date($date_format." 00:00")?>" autocomplete="off">
                    </div>

                    <div class="form-group form-group-sm ">
                        <label for="sdate" class="control-label">To: </label>
<!--                        <input type="text" name="edate" id="edate" class="form-control" value="--><?php //echo $request->getRequest('edate') ? date("Y-m-d H:00",strtotime($request->getRequest('edate'))) : date("Y-m-d 23:00")?><!--">-->
                        <input type="text" name="edate" id="edate" class="form-control" value="<?php echo $request->getRequest('edate') ? $request->getRequest('edate') : date($date_format." 00:00",strtotime("+1 day"))?>" autocomplete="off">
                    </div>

                    <div class="form-group form-group-sm ">
                        <label for="sdate" class="control-label">Skill</label>
                        <select name="skill_id" id="skill_id" class="form-control">
                            <option value="*">All</option>
                            <?php foreach ($skills as  $key => $title): ?>
                                <option value="<?php echo $key; ?>" <?php echo $key == $request->getRequest('skill_id') ? 'selected' : ''; ?>><?php echo $title; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="" style="display: inline-block">
                        <label class="control-label"><input type="hidden" name="sum_date" class="" value="N"><input type="checkbox" id="sum_date" name="sum_date" class="gs-check-box" value="Y" <?php echo $request->getRequest('sum_date') == "Y" ? 'checked' : '';?>  > <span class="check-box-title">Average Date</span></label>
                    </div>

                    <div class="" style="display: inline-block">
                        <label class="control-label"><input type="hidden" name="sum_hour" class="" value="N"><input type="checkbox" id="sum_hour" name="sum_hour" class="gs-check-box" value="Y" <?php echo $request->getRequest('sum_hour') == "Y"  ? 'checked' : '';?>  > <span class="check-box-title">Average Hour</span></label>
                    </div>


                    <button type="submit" class=" btn btn-md btn-success "><i class=" fa fa-search"></i> Search</button>
                    <a href="" id="download-service-level" class=" btn btn-md btn-purple pull-right"><i class=" fa fa-download"></i> Download</a>

                </form>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">Result Area</div>
            <div class="panel-body">
                <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-condensed service-level-table">
                    <thead>
                    <tr>
                        <?php
                        $col_span = 0;
                        if ($request->getRequest('sum_date') == "N"):
                            $col_span = 1;
                        ?>
                            <th>Date</th>
                        <?php endif; ?>

                        <?php if ($request->getRequest("sum_hour") == "Y" ): ?>
                            <th>Service Level</th>
                        <?php else: ?>
                            <?php foreach (range($dateinfo->stime,$dateinfo->etime,1) as $hour): ?>
                                <th><?php echo $hours['h-'.sprintf("%02d",$hour)]; ?></th>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <?php if (empty($result)): ?>
                                <td class="text-danger" colspan="<?php echo ($dateinfo->etime - $dateinfo->stime)+1 + $col_span; ?>">No Record Found</td>
                            <?php endif;?>
                            <?php foreach ($result as $item):?>
                                <?php $item = (array) $item; ksort($item); ?>
                                <tr>
                                    <?php if (!empty($item['sdate'])) : ?>
                                        <td><?php echo date($date_format, strtotime($item['sdate']));?></td>
                                    <?php endif; ?>
                                    <?php  unset($item['sdate']); ?>
                                    <?php foreach ($item as $hourly_service_level): ?>
                                        <td><?php echo $hourly_service_level; ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
                </div>
                <kbd><?php echo $service_level_calculation_method; ?></kbd> <br>
            </div>
        </div>
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
            current_date.setDate(current_date.getDate() );
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

        $(document).on("click","#download-service-level",function () {
            $("#MainLoader").remove();
        });
    });


    function prepareServiceLevelDownloadUrl(){
        var sdate = $("#sdate").val();
        var edate = $("#edate").val();
        var skill_id = $("#skill_id").val();
        var sum_date = $("#sum_date").is(":checked") ? "Y" : "N";
        var sum_hour = $("#sum_hour").is(":checked") ? "Y" : "N";
        var date_format = $("#date_format").val();
        generateServiceLevelDownloadUrl(sdate, edate, sum_date, sum_hour, skill_id, date_format);
    }

    function generateServiceLevelDownloadUrl(sdate, edate, sum_date, sum_hour, skill_id, date_format)
    {
        var url = '<?php echo $this->url("task=report-new&act=download-service-level&download=".md5("Service Level".session_id())) ?>';
        url += "&sdate="+sdate+"&edate="+edate+"&sum_date="+sum_date+"&sum_hour="+sum_hour+"&skill_id="+skill_id;
        $("#download-service-level").attr('href',url);
    }
</script>

