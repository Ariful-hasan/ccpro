

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.min.js"></script>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Search Area</div>
            <div class="panel-body">
                <form action="<?php echo $this->url("task=report-new&act=queue-wait-time-chart"); ?>" class="form-horizontal" method="post">


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
            <canvas id="canvas"></canvas>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {

        var result = <?php echo json_encode($result) ?>;
        //format_data = JSON.parse(result);
        var labels = <?php echo $labels;?>;
        var backgroundColor = <?php echo $backgroundColor;?>;
        var queuedata = <?php echo $queuedata;?>;
        console.log(result);

        window.onload = function() {
            if ( result != null && result.length != "" && result != "undefined" ){

                new Chart(document.getElementById("canvas"), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: "Queue Wait Time (Seconds)",
                                backgroundColor: backgroundColor,
                                data: queuedata
                            }
                        ]
                    },
                    options: {
                        legend: { display: false },
                        title: {
                            display: true,
                            text: 'Maximum Queue Wait Time'
                        },
                        scales: {
                            xAxes: [{

                                scaleLabel: {
                                    display: true,
                                    labelString: 'Skills'
                                }
                            }, ],
                            yAxes: [{
                                ticks: {
                                    min: 0,
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Seconds'
                                }
                            }]
                        },
                    }
                });

            }else {
                $("#canvas").remove();
                $(".chart-wrapper").html("<p class='text-center text-danger'>No data found for chart</p>");
            }
        };
    });

    $(document).ready(function () {
        setChartDateFormat('<?php echo $report_date_format;?>', '<?php echo $request->getRequest('sdate');?>', '<?php echo $request->getRequest('edate');?>');
    });

</script>



