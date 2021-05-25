<?php
$startDate = strtotime("-1 day");
$startDate = date("Y-m-d H:i", $startDate);
$endDate = date("Y-m-d H:i");
$toDate = date(REPORT_DATE_FORMAT ,strtotime($endDate));
$fromDate = date(REPORT_DATE_FORMAT, strtotime('-30 days'));
?>
<script src="js/Chart.min.js"></script>
<link rel="stylesheet" href="assets/css/AdminLTE.min.css" type="text/css"/>
<link rel="stylesheet" href="assets/css/email_dashboard.css?v=1.0.0" type="text/css"/>

<form method="post" enctype="form-data" name="e_dashboard" id="e_dashboard">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="panel panel-default box-panel">
                        <div class="panel-heading" ><i class="fa fa-envelope no-hover icn" aria-hidden="true"></i> Last 7 Days Emails</div>
                        <div class="panel-body">
                            <canvas id="all-email" class="chartjs"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel panel-default box-panel">
                        <div class="panel-heading"><i class="fa fa-envelope no-hover icn" aria-hidden="true"></i> Last 24 Hour Emails</div>
                        <div class="panel-body">
                            <canvas id="last-24" class="chartjs"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="panel panel-default box-panel">
                        <div class="panel-heading"><i class="fa fa-envelope no-hover icn" aria-hidden="true"></i> Last 7 Days My Emails</div>
                        <div class="panel-body">
                            <canvas id="my-email" class="chartjs"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="panel panel-default box-panel">
                        <div class="panel-heading"><i class="fa fa-envelope no-hover icn" aria-hidden="true"></i> Today SL</div>
                        <div class="panel-body">
                            <div class="col-md-6">
                                <img src="assets/images/rising.svg" alt="Average Wait Time" width="100%" height="100%">
                            </div>
                            <div class="col-md-6"><div class="wait-time"><?php echo $sl."%"?></div></div>
                            <div class="col-md-12"><div class="legend">Service Level</div></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="panel panel-default box-panel">
                        <div class="panel-heading"><i class="fa fa-envelope no-hover icn" aria-hidden="true"></i> Today AWT</div>
                        <div class="panel-body">
                            <div class="col-md-6">
                                <img src="assets/images/clock.svg" alt="Average Wait Time" width="100%" height="100%">
                            </div>
                            <div class="col-md-6"><div class="wait-time"><?php echo $awt?></div></div>
                            <div class="col-md-12"><div class="legend">Average Wait Time</div></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="panel panel-default box-panel">
                        <div class="panel-heading"><i class="fa fa-envelope no-hover icn" aria-hidden="true"></i> Today AHT</div>
                        <div class="panel-body">
                            <div class="col-md-6">
                                <img src="assets/images/client.svg" alt="Average Wait Time" width="100%" height="100%">
                            </div>
                            <div class="col-md-6"><div class="wait-time"><?php echo $aht?></div></div>
                            <div class="col-md-12"><div class="legend">Average Handling Time</div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading"><i class="fa fa-envelope no-hover icn" aria-hidden="true"></i> Today Inbound Emails</div>
                        <div class="panel-body">
                            <canvas id="today-inbox-emails" class="chartjs"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading"><i class="fa fa-envelope no-hover icn" aria-hidden="true"></i> Today Outbound Emails</div>
                        <div class="panel-body">
                            <canvas id="today-outbound-emails" class="chartjs"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</form>

<script type="text/javascript">
    $(document).ready(function(){

        let all_new_count = "<?php echo empty($alljob->num_news)?0:$alljob->num_news;?>";
        let all_pending_count = "<?php echo empty($alljob->num_pendings)?0:$alljob->num_pendings;?>";
        let all_cl_pending_count = "<?php echo empty($alljob->num_client_pendings)?0:$alljob->num_client_pendings;?>";
        let all_served_count = "<?php echo empty($alljob->num_serves)?0:$alljob->num_serves;?>";
        let all_labels = ["New ("+all_new_count+")", "Pending ("+all_pending_count+")", "Client-Pending ("+all_cl_pending_count+")", "Served ("+all_served_count+")"];
        let all_dataset_data = [all_new_count, all_pending_count, all_cl_pending_count, all_served_count];
        let all_backgroundColor = ["#fd397a", "#2a4eff", "#ffb822", "#0abb87"];
        let all_hoverBorderColor = ["#ff9d9d", "#d3c3fb", "#ffe4ab", "#bcdad7"];
        draw_donut("all-email", all_labels, all_dataset_data, all_backgroundColor, all_hoverBorderColor, "", " Email");

        let recn_new_count = "<?php echo empty($recentjob->num_news)?0:$recentjob->num_news;?>";
        let recn_pending_count = "<?php echo empty($recentjob->num_pendings)?0:$recentjob->num_pendings;?>";
        let recn_cl_pending_count = "<?php echo empty($recentjob->num_client_pendings)?0:$recentjob->num_client_pendings;?>";
        let recn_served_count = "<?php echo empty($recentjob->num_serves)?0:$recentjob->num_serves;?>";
        let recn_labels = ["New ("+recn_new_count+")", "Pending ("+recn_pending_count+")", "Client-Pending ("+recn_cl_pending_count+")", "Served ("+recn_served_count+")"];
        let recn_dataset_data = [recn_new_count, recn_pending_count, recn_cl_pending_count, recn_served_count];
        let recn_backgroundColor = ["#fd397a", "#2a4eff", "#ffb822", "#0abb87"];
        let recn_hoverBorderColor = ["#ff9d9d", "#d3c3fb", "#ffe4ab", "#bcdad7"];
        draw_donut("last-24", recn_labels, recn_dataset_data, recn_backgroundColor, recn_hoverBorderColor, "", " Email");

        let my_new_count = "<?php echo empty($myjob->num_news)?0:$myjob->num_news;?>";
        let my_pending_count = "<?php echo empty($myjob->num_pendings)?0:$myjob->num_pendings;?>";
        let my_cl_pending_count = "<?php echo empty($myjob->num_client_pendings)?0:$myjob->num_client_pendings;?>";
        let my_served_count = "<?php echo empty($myjob->num_serves)?0:$myjob->num_serves;?>";
        let my_labels = ["New ("+my_new_count+")", "Pending ("+my_pending_count+")", "Client-Pending ("+my_cl_pending_count+")", "Served ("+my_served_count+")"];
        let my_dataset_data = [my_new_count, my_pending_count, my_cl_pending_count, my_served_count];
        let my_backgroundColor = ["#fd397a", "#2a4eff", "#ffb822", "#0abb87"];
        let my_hoverBorderColor = ["#ff9d9d", "#d3c3fb", "#ffe4ab", "#bcdad7"];
        draw_donut("my-email", my_labels, my_dataset_data, my_backgroundColor, my_hoverBorderColor, "", " Email");

        let inbox_data = <?php echo !empty($inbox_emails_count) ? json_encode($inbox_emails_count) : "''"?>;
        let inbox_color_data = <?php echo !empty($inbox_emails_color) ? json_encode($inbox_emails_color) : "''"?>;
        let inbox_labels = [];
        let inbox_dataset_data= [];
        let inbox_hoverBorderColor = [];
        let total_inbox_count = 0;
        if (typeof inbox_data !== 'undefined' && inbox_data !== null){
            $.each(inbox_data, function (key, val) {
                inbox_labels.push(key+" ("+val+")");
                inbox_dataset_data.push(val);
                total_inbox_count = total_inbox_count + parseInt(val);
            })
        }
        draw_donut("today-inbox-emails", inbox_labels, inbox_dataset_data, inbox_color_data, inbox_hoverBorderColor, "Total "+total_inbox_count, " Inbound");


        let outbound_data = <?php echo !empty($outbound_emails_count) ? json_encode($outbound_emails_count) : "''"?>;
        let outbound_color_data = <?php echo !empty($outbound_emails_color) ? json_encode($outbound_emails_color) : "''"?>;
        let outbound_labels = [];
        let outbound_dataset_data= [];
        let outbound_hoverBorderColor = [];
        let total_outbound_count = 0;
        if (typeof outbound_data !== 'undefined' && outbound_data !== null){
            $.each(outbound_data, function (key, val) {
                outbound_labels.push(key+" ("+val+")");
                outbound_dataset_data.push(val);
                total_outbound_count = total_outbound_count + parseInt(val);
            })
        }
        draw_donut("today-outbound-emails", outbound_labels, outbound_dataset_data, outbound_color_data, outbound_hoverBorderColor, "Total "+total_outbound_count, " Outbound");
    });

    function draw_donut(canvas_id, inbox_labels, inbox_dataset_data, inbox_color_data, hoverBorderColor, center_text="", tooltiptext="") {
        Chart.pluginService.register({
            beforeDraw: function (chart) {
                if (chart.config.options.elements.center) {
                    //Get ctx from string
                    var ctx = chart.chart.ctx;

                    //Get options from the center object in options
                    var centerConfig = chart.config.options.elements.center;
                    var fontStyle = centerConfig.fontStyle || 'Arial';
                    var txt = centerConfig.text;
                    var color = centerConfig.color || '#000';
                    var sidePadding = centerConfig.sidePadding || 20;
                    var sidePaddingCalculated = (sidePadding/100) * (chart.innerRadius * 2)
                    //Start with a base font of 30px
                    ctx.font = "30px " + fontStyle;

                    //Get the width of the string and also the width of the element minus 10 to give it 5px side padding
                    var stringWidth = ctx.measureText(txt).width;
                    var elementWidth = (chart.innerRadius * 2) - sidePaddingCalculated;

                    // Find out how much the font can grow in width.
                    var widthRatio = elementWidth / stringWidth;
                    var newFontSize = Math.floor(30 * widthRatio);
                    var elementHeight = (chart.innerRadius * 2);

                    // Pick a new font size so it will not be larger than the height of label.
                    var fontSizeToUse = Math.min(newFontSize, elementHeight);

                    //Set font settings to draw it correctly.
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    var centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
                    var centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 2);
                    ctx.font = fontSizeToUse+"px " + fontStyle;
                    ctx.fillStyle = color;

                    //Draw text in center
                    ctx.fillText(txt, centerX, centerY);
                }
            }
        });
        let config = {
            type: 'doughnut',
            data: {
                labels: inbox_labels,
                datasets: [{
                    // "label": "All Emails",
                    "data": inbox_dataset_data,
                    "backgroundColor": inbox_color_data,
                    "hoverBorderColor": hoverBorderColor,
                    "hoverBorderWidth": 5
                }]
            },
            options: {
                responsive: true,
                legend: {
                    display: true,
                    position: 'right',
                    labels: {
                        fontColor: '#282a3c',
                        usePointStyle: true,
                        fontSize: 15
                    }
                },
                animation: {
                    easing: 'easeOutElastic'
                },
                tooltips: {
                    callbacks: {
                        title: function(tooltipItem, data) {
                            let item = data['labels'][tooltipItem[0]['index']];
                            item = item.substr(0,item.indexOf("(")) + tooltiptext;
                            return item;
                        },
                        label: function(tooltipItem, data) {
                            return data['datasets'][0]['data'][tooltipItem['index']];
                        }
                    }
                },
                elements: {
                    center: {
                        text: center_text,
                        color: '#FF6384',
                        fontStyle: 'Arial',
                        // sidePadding: 20 // Defualt is 20 (as a percentage)
                    }
                }
            }
        };
        let ctx = document.getElementById(canvas_id).getContext("2d");
        new Chart(ctx, config);
    }
</script>
