<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.min.js"></script>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Search Area</div>
            <div class="panel-body">
                <form action="<?php echo $this->url("task=report-new&act=agent-idle-time-chart"); ?>" class="form-horizontal" method="post">


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
                            <label for="sdate" class="control-label col-md-4">Agents</label>
                            <div class="col-md-8">
                                <select name="agent_id" id="agent_id" class="form-control">
                                    <option value="*">All</option>
                                    <?php foreach ($agents as  $id => $name): ?>
                                        <option value="<?php echo $id; ?>" <?php echo $id == $request->getRequest('agent_id') ? 'selected' : ''; ?>><?php echo $name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-1 pull-right">
                        <button type="submit" class="btn btn-md btn-success pull-right"><i class=" fa fa-search"></i> Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <br>
    <div class="col-md-12 ">
        <div class="col-md-12 chart-wrapper text-center">
            <canvas id="agent-idle-time"></canvas>
        </div>
    </div>
</div>
<?php  ?>
<script>
    var idle_time = <?php echo json_encode($idle_time) ?>;
    var available_time = <?php echo json_encode($available_time) ?>;
    var calls_in_time = <?php echo json_encode($calls_in_time) ?>;
    var calls_out_time = <?php echo json_encode($calls_out_time) ?>;
    var label = <?php echo json_encode($labels) ?>;


    var lineChartData = {
        labels: label,
        datasets: [{
            label: "Available Time",
            borderColor: "blue",
            backgroundColor: "blue",
            fill: false,
            data: available_time
        },
        {
            label: "Idle Time",
            borderColor: "red",
            backgroundColor: "red",
            fill: false,
            data:idle_time
        }]
    };



    if (idle_time != "undefined" && idle_time != null && idle_time.length > 0)
    {
        var ctx = document.getElementById("agent-idle-time").getContext("2d");
        window.myLine = Chart.Line(ctx, {
            data: lineChartData,
            options: {
                scaleOverride: true,
                scaleStepWidth: 60,
                scaleStartValue : 0,
                hoverMode: 'index',
                stacked: false,
                title:{
                    display: true,
                    text:'Agent Idle Time',
                    position:"top",
                    fontSize:15
                },
                tooltips: {
                    enabled: true,
                    mode: 'single',
                    callbacks: {
                        label: function(tooltipItems, data) {
                            var date = new Date(null);
                            date.setSeconds(tooltipItems.yLabel);
                            return date.toISOString().substr(11, 8);
                        }
                    }
                },
                scales: {
                    xAxes:[{
                        scaleLabel: {
                            display: true,
                            labelString: 'Date Range'
                        }
                    }],

                    yAxes: [{
                        ticks: {
                            // Include a dollar sign in the ticks
                            callback: function(value, index, values) {
                                var date = new Date(null);
                                date.setSeconds(value); // specify value for SECONDS here
                                console.log(date.toISOString());
                                return date.toISOString().substr(11, 8);

                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Time (HH:MM:SS)'
                        }
                    }]
                }
            }
        });
    }
    else {
        $("#agent-idle-time").remove();
        $(".chart-wrapper").html("<p class='text-center text-danger'>No data found for chart</p>");
    }
    $(document).ready(function () {
        setChartDateFormat('<?php echo $report_date_format;?>', '<?php echo $request->getRequest('sdate');?>', '<?php echo $request->getRequest('edate');?>');
    });
</script>

<?php ?>
