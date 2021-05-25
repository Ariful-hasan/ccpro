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
        <form id="workcode-form" action="<?php echo $this->url("task=report-new&act=vivr-service-summary-report"); ?>" class="form-inline" method="post" autocomplete="off">

            <div class="form-group form-group-sm ">
                <label for="sdate" class="control-label"> From: </label>
                <input type="text" name="sdate" id="sdate" class="form-control" value="<?php echo $request->getRequest('sdate') ? $request->getRequest('sdate') : date(get_report_date_format()." 00:00",strtotime(" -7 days"))?>">
            </div>

            <div class="form-group form-group-sm ">
                <label for="sdate" class="control-label"> To: </label>
                <input type="text" name="edate" id="edate" class="form-control" value="<?php echo $request->getRequest('edate') ? $request->getRequest('edate'): date(get_report_date_format()." 00:00")?>">
            </div>

            <div class="form-group form-group-sm ">
                <label for="skill_id" class="control-label"> IVR ID: </label>
                <select name="ivr_id" id="ivr_id" class="form-control select2">
                    <?php foreach ($ivrs as $id => $type): ?>
                        <option value="<?php echo $id; ?>" <?php echo $request->getRequest('ivr_id') == $id ? 'selected' : '' ?>><?php echo $type; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group form-group-sm ">
                <label for="skill_id" class="control-label"> Report Type: </label>
                <select name="report_type" id="report_type" class="form-control select2">
                    <?php foreach ($report_types as $id => $type): ?>
                        <option value="<?php echo $id; ?>" <?php echo $request->getRequest('report_type') == $id ? 'selected' : '' ?>><?php echo $type; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class=" btn btn-md btn-success "><i class=" fa fa-search"></i> Search </button>
            <a href="<?php echo $this->url("task=report-new&act=vivr-service-summary-report&download=".md5("CC Workcode Report".session_id())) ?>" id="download-workcode-report" class=" btn btn-md btn-purple"><i class=" fa fa-download"></i> Download</a>
        </form>
        <div class="row">
            <br/>
        </div>

        <div class="table-responsive m-t-10">
            <table class="table table-bordered table-striped table-hover table-condensed service-level-table">
                <thead>
                <tr>
                    <td>Vivr Service</td>
                    <?php foreach( $date_list as  $date){
                        if($report_type == REPORT_DAILY){
                            echo "<td>" . report_date_format($date) . "</td>";
                        }elseif ($report_type == "Weekly"){
                            echo "<td> Week " . $date . "</td>";
                        }
                    }
                    ?>
                </tr>
                </thead>
                <tbody>
                <?php
                if (empty($results)) {
                    echo "<tr>
                        <td class='text-danger' colspan='" . (count($date_list) + 1) . "'> No Record Found </td>
                        </tr>";
                } else {
                    foreach ($results as $result) {
                        echo "<tr>
                        <td class='text-success' > $result->service_name </td>";
                        foreach ($date_list as $date) {
                            echo '<td>' . $result->{$date} . '</td>';
                        }
                        echo "</tr>";
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script type="text/javascript">
    var report_date_format = '<?php echo $report_date_format; ?>';

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
        $(document).on('change','#report_type',function () {
            prepareDownloadUrl();
        });

        $(document).on('click','#download-workcode-report',function () {
            $("#MainLoader").remove();
        });

        $("#workcode-form").on("submit", function (e) {
            try {
                if(!beforeFormSubmit(15)){
                    return false;
                }
            }catch(e){
                console.log(e.message);
            }
        });
    });

    function prepareDownloadUrl(){
        $downloadButton = $("#download-workcode-report");
        var url = window.location.href;
        var sdate = $("#sdate").val();
        var edate = $("#edate").val();
        var ivr_id = $("#ivr_id").val();
        var report_type = $("#report_type").val();
        var download = "<?php echo md5("CC Workcode Report".session_id()) ?>";

        url += '&sdate='+encodeURIComponent(sdate)+ '&edate='+ encodeURIComponent(edate) +
            '&ivr_id='+encodeURIComponent(ivr_id)+'&report_type=' + report_type +'&download=' + download;
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

        if ((d2.getTime() - d.getTime()) / (1000*60*60*day*24) > 1) {
            alert('Provide date range within '+day+' days');
            return false;
        }
        return true;
    }


</script>

