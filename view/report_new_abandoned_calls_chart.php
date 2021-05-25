
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.min.js"></script>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Search Area</div>
            <div class="panel-body">
                <form action="<?php echo $this->url("task=report-new&act=abandoned-calls-chart"); ?>" class="form-horizontal" method="post">

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
                                <select name="skill_id" id="skill_id" class="form-control select2">
                                    <option value="*">All</option>
                                    <?php foreach ($skills as  $key => $title): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $key == $request->getRequest('skill_id') ? 'selected' : ''; ?>><?php echo $title; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-1">
                        <button type="submit" class="btn btn-md btn-success" style="margin-right: -10px!important;"><i class=" fa fa-search"></i> Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <br>
    <div class="col-md-12 ">
        <div class="col-md-12 chart-wrapper text-center">
            <canvas id="abandoned-calls-chart"></canvas>
        </div>
    </div>
</div>

<script>
    var abandoned_calls_percent = <?php echo $percent; ?>;
    var abandoned_calls_fixed = <?php echo $fixed; ?>;
    var label = <?php echo $labels; ?>;



    var lineChartData = {
        labels: label,
        datasets: [{
            label: "Abandoned Calls(%) ",
            borderColor: "red",
            backgroundColor: "red",
            fill: false,
            data: abandoned_calls_percent,
            yAxisID: "y-axis-1",
        }, {
            label: "Actual Abandoned Calls",
            borderColor: "blue",
            backgroundColor: "blue",
            fill: false,
            data:abandoned_calls_fixed,
            yAxisID: "y-axis-2"
        }]
    };



    if (abandoned_calls_percent != "undefined" && abandoned_calls_percent != null && abandoned_calls_percent != "")
    {
        var ctx = document.getElementById("abandoned-calls-chart").getContext("2d");
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
                    yAxes: [{
                        type: "linear",
                        display: true,
                        position: "left",
                        id: "y-axis-1",
                        ticks: {
                            beginAtZero: true,
                            stepSize:5,
                            max:100
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Abandoned Calls in Percent (%)'
                        }
                    }, {
                        type: "linear",
                        display: true,
                        position: "right",
                        id: "y-axis-2",
                        ticks: {
                            beginAtZero: true,
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Total Abandoned Calls'
                        },

                        // grid line settings
                        gridLines: {
                            drawOnChartArea: false, // only want the grid lines for one axis to show up
                        },
                    }],
                },
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


