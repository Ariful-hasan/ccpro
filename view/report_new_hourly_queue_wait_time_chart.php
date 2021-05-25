<style>
    /*.select2-selection--single {
        border-radius: 0px!important;
        height: 34px !important;
        !*line-height: 33px !important;*!
    }*/
    /*.select2-selection--single .select2-selection__rendered {
        color: #444;
        line-height: 31px;
    }*/
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.min.js"></script>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Search Area</div>
            <div class="panel-body">
                <form action="<?php echo $this->url("task=report-new&act=hourly-queue-wait-time-chart"); ?>" class="form-horizontal" method="post">
                  <!--<div class="col-md-12">-->
                      <div class="col-md-4">
                          <div class="form-group">
                              <label for="sdate" class="control-label col-md-5">Start Date</label>
                              <div class="col-md-7">
                                  <input type="text" name="sdate" class="gs-report-date-picker from-date form-control" value="<?php echo !empty($request->getRequest('sdate')) ? $request->getRequest('sdate') : date($report_date_format); ?>" autocomplete="off">
                              </div>
                          </div>
                      </div>
                      <div class="col-md-4">
                          <div class="form-group">
                              <label for="sdate" class="control-label col-md-5">End Date</label>
                              <div class="col-md-7">
                                  <input type="text" name="edate" class="gs-report-date-picker to-date form-control" value="<?php echo !empty($request->getRequest('edate')) ? $request->getRequest('edate') : date($report_date_format,strtotime("+1 day")); ?>" autocomplete="off">
                              </div>
                          </div>
                      </div>
                      <div class="col-md-3">
                          <div class="form-group">
                              <label for="sdate" class="control-label col-md-4">Skill</label>
                              <div class="col-md-8">
                                  <select name="skill_id" id="skill_id" class="form-control select2">
                                      <?php foreach ($skills as  $key => $title): ?>
                                          <option value="<?php echo $key; ?>" <?php echo $key == $request->getRequest('skill_id') ? 'selected' : ''; ?>><?php echo $title; ?></option>
                                      <?php endforeach; ?>
                                  </select>
                              </div>
                          </div>
                      </div>
                      <div class="col-md-1" >
                          <button type="submit" class="btn btn-success" style="margin-right: -10px!important;"><i class=" fa fa-search"></i> Search</button>
                      </div>
                  <!--</div>-->
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
        //var total_skill = <?php //echo count($skillCount)?>;
        //var skill_data = <?php //echo json_encode($skillCount)?>;
        var label = <?php echo $labels; ?>;
        var format_data = <?php echo json_encode($format_data) ?>;
        format_data = JSON.parse(format_data);

        var max_value = <?php echo !empty($max_value)?$max_value:'""';?>;
        var min_value = <?php echo !empty($min_value)?$min_value:'""';?>;


        window.onload = function() {
            if (max_value.length != "" && min_value.length != "" && format_data!= "undefined" && format_data != null){

                var color = Chart.helpers.color;
                var config = {
                    type: 'line',
                    data: {
                        labels: label,
                        datasets: format_data
                    },
                    options: {
                        title:{
                            //display: true,
                            //text: "Chart.js Time Scale"
                        },
                        scales: {
                            xAxes: [{
                                type: "time",
                                time: {
                                    format: "HH:mm",
                                    unit: 'hour',
                                    unitStepSize: 1,
                                    displayFormats: {
                                        'minute': 'HH:mm',
                                        'hour': 'HH:mm',
                                    },
                                    tooltipFormat: 'HH:mm'
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Time'
                                }
                            }, ],
                            yAxes: [{
                                ticks: {
                                    min: min_value,
                                    max:max_value,
                                    stepSize: 20,

                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Avg Seconds'
                                }
                            }]
                        },
                    }
                };

                var ctx = document.getElementById("canvas").getContext("2d");
                window.myLine = new Chart(ctx, config);
            }else {
                $("#canvas").remove();
                $(".chart-wrapper").html("<p class='text-center text-danger'>No data found for chart</p>");
            }
        };

        ////////   DATE FORMAT  ////////////
        // setDateFormat();
        setChartDateFormat('<?php echo $report_date_format;?>', '<?php echo $request->getRequest('sdate');?>', '<?php echo $request->getRequest('edate');?>');
    });

</script>



