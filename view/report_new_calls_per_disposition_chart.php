<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.min.js"></script>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Search Area</div>
            <div class="panel-body">
                <form action="<?php echo $this->url("task=report-new&act=calls-per-disposition-chart"); ?>" class="form-horizontal" method="post">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sdate" class="control-label col-md-5">Start Date</label>
                            <div class="col-md-7">
                                <input type="text" name="sdate" class="gs-date-picker form-control" value="<?php echo $request->getRequest('sdate') ? $request->getRequest('sdate') : date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="sdate" class="control-label col-md-5">End Date</label>
                            <div class="col-md-7">
                                <input type="text" name="edate" class="gs-date-picker form-control" value="<?php echo $request->getRequest('edate') ? $request->getRequest('edate') : date('Y-m-d'); ?>">
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

                    <div class="col-md-2">
                        <button type="submit btn btn-md btn-success"><i class=" fa fa-search"></i> Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <br>
    <div class="col-md-12 ">
        <div class="col-md-12 chart-wrapper text-center">
            <canvas id="pie-chart"></canvas>
        </div>
    </div>
</div>

<script>
    var chart_data = <?php echo $data; ?>;

    var labels = [];
    var call_data = [];
    var color = [];

    var dynamicColors = function() {
        var r = Math.floor(Math.random() * 255);
        var g = Math.floor(Math.random() * 255);
        var b = Math.floor(Math.random() * 255);
        return "rgb(" + r + "," + g + "," + b + ")";
    };
console.log(chart_data);
    if (chart_data != "undefined" && chart_data != null && chart_data != "")
    {
        $.each(chart_data, function(index,call) {
            labels.push(index);
            call_data.push(call);
            index == 200 ? color.push("green") : color.push(dynamicColors());
        });

        var ctx = document.getElementById("pie-chart").getContext('2d');
        pieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    backgroundColor: color,
                    data: call_data
                }]
            },
            options: {
                title: {
                    display: true,
                    text: 'Calls Per Disposition',
                    position:'bottom',
                },
                legend: {
                    reverse: false,
                    position: "right"
                },
                animationSteps: 600,
                responsive: true,
            }
        });
    }
    else {
        $("#pie-chart").remove();
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

