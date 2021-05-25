
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.min.js"></script>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Search Area</div>
            <div class="panel-body">
                <form action="<?php echo $this->url("task=report-new&act=call-volume-chart"); ?>" class="form-horizontal" method="post">

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
            <canvas id="abandoned-calls-chart"></canvas>
        </div>
    </div>
</div>

<script>
    var callVolumes = <?php echo json_encode($callVolumes); ?>;
    var skills = <?php echo json_encode($skills); ?>;

    var calls_offered = [];
    var calls_answered = [];
    var calls_repeted = [];
    var calls_ans_within_service_level = [];
    var label = [];

    if (callVolumes){
        $.each(callVolumes, function (index, call) {
            label.push(skills[call.skill_id]);
            calls_offered.push(call.calls_offered);
            calls_answered.push(call.calls_answerd);
            calls_repeted.push(call.calls_repeated);
            calls_ans_within_service_level.push(call.answerd_within_service_level);
        });
    }


    var lineChartData = {
        labels: label,
        datasets: [{
            label: "Offered Calls",
            borderColor: "blue",
            backgroundColor: "blue",
            fill: false,
            data: calls_offered
        },
        {
            label: "Answered Calls",
            borderColor: "teal",
            backgroundColor: "teal",
            fill: false,
            data:calls_answered
        },
        {
            label: "Repeated Calls",
            borderColor: "red",
            backgroundColor: "red",
            fill: false,
            data: calls_repeted
        },
        {
            label: "Answered Within Service Level Calls",
            borderColor: "darkgreen",
            backgroundColor: "darkgreen",
            fill: false,
            data:calls_ans_within_service_level
        }]
    };



    if (calls_offered != "undefined" && calls_offered != null && calls_offered != "")
    {
        var ctx = document.getElementById("abandoned-calls-chart").getContext("2d");
        window.myLine = Chart.Line(ctx, {
            data: lineChartData,
            options: {
                hoverMode: 'index',
                stacked: false,
                title:{
                    display: true,
                    text:'Call Volume',
                    position:"top",
                    fontSize:15
                },
                scales: {
                    yAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Calls'
                        }
                    }],
                    xAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Skills'
                        }
                    }]
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


