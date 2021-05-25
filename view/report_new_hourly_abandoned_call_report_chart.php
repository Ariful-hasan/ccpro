<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.min.js"></script>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Search Area</div>
            <div class="panel-body">
                <form action="<?php echo $this->url("task=report-new&act=hourly-abandoned-call-chart"); ?>" class="form-horizontal" method="post">

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sdate" class="control-label col-md-5">Start Date</label>
                            <div class="col-md-7">
                                <input type="text" name="sdate" class="gs-report-date-picker from-date form-control" value="<?php echo $request->getRequest('sdate') ? $request->getRequest('sdate') : date($report_date_format); ?>" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sdate" class="control-label col-md-5">End Date</label>
                            <div class="col-md-7">
                                <input type="text" name="edate" class="gs-report-date-picker to-date form-control" value="<?php echo $request->getRequest('edate') ? $request->getRequest('edate') : date($report_date_format,strtotime("+1 day")); ?>" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="sdate" class="control-label col-md-4">Skill</label>
                            <div class="col-md-8">
                                <select name="skill_id" id="skill_id" class="form-control">
                                    <option value="*">All</option>
                                    <?php foreach ($skills as  $key => $title): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $key == $request->getRequest('skill_id') ? 'selected' : ''; ?>><?php echo $title; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-1">
                        <button type="submit" class="btn btn-md btn-success"><i class=" fa fa-search"></i> Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <br>
    <div class="col-md-12 ">
        <div class="col-md-12 chart-wrapper text-center">
            <canvas id="hourly-abandoned-calls-chart"></canvas>
        </div>
    </div>
</div>
<?php  ?>
<script>
    var callinfo = <?php echo json_encode($abandoned_calls) ?>;
    var offered_calls = [];
    var abandoned_calls = [];
    var label = [];
    
    if (callinfo != 'undefined' && callinfo != null && callinfo.length > 0)

    $.each(callinfo,function (index, call) {
        offered_calls.push(call.calls_offered);
        abandoned_calls.push(call.calls_abandoned);
        var am_pm =  call.shour > 11 ? "PM" : "AM";
        var hour = call.shour % 12 ;
        hour = hour == 0 ? 12 : hour ;
        label.push(hour + " " +am_pm);
    });





    var lineChartData = {
        labels: label,
        datasets: [{
            label: "Offered Calls ",
            borderColor: "blue",
            backgroundColor: "blue",
            fill: false,
            data: offered_calls
        },
        {
            label: "Abandoned Calls",
            borderColor: "red",
            backgroundColor: "red",
            fill: false,
            data:abandoned_calls
        }]
    };



    if (offered_calls != "undefined" && offered_calls != null && offered_calls.length > 0)
    {
        var ctx = document.getElementById("hourly-abandoned-calls-chart").getContext("2d");
        window.myLine = Chart.Line(ctx, {
            data: lineChartData,
            options: {
                hoverMode: 'index',
                stacked: false,
                title:{
                    display: true,
                    text:'Abandoned Calls',
                    position:"bottom",
                    fontSize:15
                },
                scales: {
                    yAxes :[
                        {
                            scaleLabel:{
                                display :true,
                                labelString: "Calls"
                            }
                        }
                    ]
                }
            }
        });
    }
    else {
        $("#abandoned-calls-chart").remove();
        $(".chart-wrapper").html("<p class='text-center text-danger'>No data found for chart</p>");
    }
    $(document).ready(function () {
        setChartDateFormat('<?php echo $report_date_format;?>', '<?php echo $request->getRequest('sdate');?>', '<?php echo $request->getRequest('edate');?>');
    });
</script>

<?php ?>
