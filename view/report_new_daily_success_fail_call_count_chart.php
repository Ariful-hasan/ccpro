<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.min.js"></script>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Search Area</div>
            <div class="panel-body">
                <form action="<?php echo $this->url("task=report-new&act=pd-daily-success-fail-call"); ?>" class="form-horizontal" method="post">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="year" class="control-label col-md-4">Start Date</label>
                            <div class="col-md-8">
                                <?php $value = !empty($request->getRequest('start_date')) ? $request->getRequest('start_date') : date("Y-m-d",strtotime("- 30 Days")); ?>
                                <input type="text" name="start_date" class="form-control gs-date-picker" value="<?php echo $value; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="year" class="control-label col-md-4">End Date</label>
                            <div class="col-md-8">
                                <?php $value = !empty($request->getRequest('end_date')) ? $request->getRequest('end_date') : date("Y-m-d"); ?>
                                <input type="text" name="end_date" class="form-control gs-date-picker" value="<?php echo $value; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <button type="submit btn btn-success"><i class=" fa fa-search"></i> Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <br>

    <div class="col-md-12 chart-wrapper text-center" >
        <div class="chart">
            <canvas id="line-chart" style="min-width: 100%;"></canvas>
        </div>
    </div>
</div>

<script>
    var chart_data = <?php echo $data; ?>;

    var labels = [];
    var success_call = [];
    var attempted_call = [];
    var dynamicColors = function() {
        var r = Math.floor(Math.random() * 255);
        var g = Math.floor(Math.random() * 255);
        var b = Math.floor(Math.random() * 255);
        return "rgb(" + r + "," + g + "," + b + ")";
    };

    if (chart_data != "undefined" && chart_data != null && chart_data != "")
    {
        $.each(chart_data, function(index,call) {
            labels.push(call.call_date);
            success_call.push(Number(call.success_call));
            attempted_call.push(Number(call.attempted_call));
            //attempted_call.push(Number(call.fail_call)+Number(call.success_call));
        });

        var ctx = document.getElementById("line-chart").getContext('2d');
        pieChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                        label: "Attempted Call",
                        data: attempted_call,
                        fill:false,
                        borderColor:"red",
                        pointBorderWidth:10,
                        backgroundColor: "red",
                        borderDash: [5, 5],
                    },
                    {
                    label: "Success Call",
                    data: success_call,
                    fill:false,
                    borderColor:"green",
                    pointBorderWidth:10,
                    backgroundColor: "green",
                }]
            },
            options: {
                title: {
                    display: true,
                    text: 'Daily Attempted vs Success Calls for Auto Dial',
                    position:'bottom',
                },
                legend: { reverse: true,
                    position: "right"
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                animationSteps: 1500,
                responsive: true,
            }
        });
    }
    else {
        $("#line-chart").remove();
        $(".chart-wrapper").html("<p class='text-center text-danger'>No data found for chart</p>");
    }


</script>

<script type="text/javascript">
    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
    });
</script>

