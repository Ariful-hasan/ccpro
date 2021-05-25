<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>gPlex Contact Centre Dashboard</title>

    <script src="assets/js/jquery-1.11.2.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">
    <!--    <link rel="stylesheet" href="assets/css/email_agent_dashboard.css">-->
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.js"></script>

    <!-- Include the plugin's CSS and JS: -->
    <script src="js/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
    <link rel="stylesheet" href="js/css/bootstrap-multiselect/css/bootstrap-multiselect.css" type="text/css"/>
    <link rel="stylesheet" href="assets/css/email_activity_dashboard.css" type="text/css"/>
    <script src="js/Chart.min.js"></script>

    <?php
    $client_img = !empty($suffix) ? $suffix : "aa";
    $client_img .= "_client_logo.png";
    ?>
</head>
<body>

<div class="container-fluid" id="dashboard">
    <div class="col-md-12 pl-0 pr-0">
        <div class="col-md-2 pl-0"><a href="<?php echo base_url(); ?>"><img src="assets/images/logo.png" class="img-responsive pull-left" height="0" width="50%"></a></div>
        <div class="col-md-2 col-md-offset-8"><a href="<?php echo base_url(); ?>"><img  src="assets/images/<?php echo $client_img?>" class="img-responsive pull-right mt-m10" height="0" width="50%"></a></div>
    </div>
    <div class="col-md-12 mt-m2">
        <div class="row">

            <div class="col-lg-12 col-md-12 pl-0 pr-0">
                <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 pl-0" id="sl_panel">
                    <div class="panel panel-default pb-0">
                        <div class="panel-heading center txt-22 font-bolder bg-mid-red clr-white pb-3 pt-3">Service Level</div>
                        <div class="panel-body pb-0">
                            <div class="row">
                                <div class="col-md-12 pl-5 pr-5" id="sl"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 pl-0 pr-0" id="aht_panel">
                    <div class="panel panel-default pb-0">
                        <div class="panel-heading center txt-22 font-bolder bg-mid-red clr-white pb-3 pt-3">AHT</div>
                        <div class="panel-body pb-0">
                            <div class="row" >
                                <div class="col-md-12 pl-5 pr-5" id="aht"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 col-md-12 pl-0 pr-0">
                <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12 pl-0">
                    <div class="panel panel-default pb-0" id="swkpi_panel">
                        <div class="panel-heading center txt-22 font-bolder bg-mid-red clr-white pb-3 pt-3">Skill KPI</div>
                        <div class="panel-body pb-0">
                            <div class="row">
                                <div class="col-md-12 pr-0 pl-5 pr-5" id="swkpi"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-12 col-sm-12 col-xs-12 pr-0 pl-0">
                    <div class="panel panel-default pl-0 pr-0" id="kpi_panel">
                        <div class="panel-heading center txt-22 font-bolder bg-mid-red clr-white pb-3 pt-3">KPI</div>
                        <div class="panel-body pl-0 pr-0">
                            <!--total in and out kpi chart-->
<!--                            <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 pl-0 pr-0">-->
<!--                                <canvas id="chart_total" class="chartjs pl-0 pr-0"></canvas>-->
<!--                            </div>-->
                            <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 pl-0 pr-0">
                                <canvas id="chart_total_percentage" class="chartjs pl-0 pr-0"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>


<script>

    let resp_data = "";
    let skill_names = '<?php echo json_encode($skill_names)?>';
    skill_names = JSON.parse(skill_names);
    let color_palet = [
                         ["#fbbb3a", "#2b347a", "#ff3366"],
                         ["#00b2dd", "#f1792f", "#368e1f"],
                         ["#7b49bd", "#8161d0", "#d7a8ff"],
                         ["#f69420", "#a32b6a", "#385a7c"],
                         ["#3455cd", "#5cd8f1", "#8f1789"],
                         ["#cc003b", "#b49d9d", "#4bbcbc"],
                         ["#80175a", "#b4d455", "#f69420"],
                         ["#4bbcbc", "#385a7c", "#8f1789"],
                         ["#43ae70", "#ff9a00", "#43aea6"],
                         ["#f3c030", "#7bbbb5", "#544661"],
                         ["#f695aa", "#f8acbd", "#fac4cf"],
                         ["#000000", "#d61a1f", "#f8b71d"],
                         ["#2d6a9f", "#27a6ae", "#5ef7f1"],
                         ["#19006b", "#7800bd", "#d400d6"],
                         ["#ffd245", "#f67000", "#d400d6"],
                         ["#4085c6", "#f04f45", "#c7c7c7"],
                    ];

    $(document).ready(function() {
        getData();
        setInterval(function () {
            getData();
        },600000);
    });

    function getData(){
        url = "<?php echo site_url().$this->url('task='.$request->getControllerName().'&act=email-activity-dashboard')?>";
        getXHRresponse(url);
        resp_data.done(function (res) {
            if (typeof res !== 'undefined' && res != null && Object.keys(res).length > 0) {
                setData(res);
            }
        });
        return null;
    }

    function setData(data){
        const serviceLevel = {
            data            : data.sl,
            parentElement   : "#sl",
            childElement    : "sl_",
            backgroundColor : "bg-white",
            fontSize        : "txt-responsive-2"
        };
        const averageHandlingTime = {
            data            : data.aht,
            parentElement   : "#aht",
            childElement    : "aht_",
            backgroundColor : "bg-white",
            fontSize        : "txt-responsive-2"
        };

        updateView(serviceLevel);
        updateView(averageHandlingTime);
        updateSWKPI(data.swkpi);
        // updateTotalKPI(data);
        updateTotalKPIPercentage(data);
        adjustHeight();
        $("#aht").height($("#sl").height());
    }

    let adjustHeight = (divIDs) => {
        let maxHeight = 0;
        if (typeof divIDs !== 'undefined' && divIDs.length > 0){
            for (let index in divIDs){
                let divHeight = $("#"+divIDs[index]).height();
                maxHeight = maxHeight > divHeight ? maxHeight : divHeight;
            }
            for (let index in divIDs){
                $("#"+divIDs[index]).height(maxHeight);
            }
        }
    };

    let updateSWKPI = (data) => {
        if (typeof data !== 'undefined' && data != null && Object.keys(data).length > 0){
            let swkpi = data;
            let divIDs = [];
            for (let key in swkpi) {

                let color = color_palet[Math.floor(Math.random()*Object.keys(color_palet).length)];
                let labels = ["In ("+swkpi[key].total_in_kpi+")", "Out ("+swkpi[key].total_out_kpi+")"];
                let dataset_data = [swkpi[key].total_in_kpi, swkpi[key].total_out_kpi];
                let backgroundColor = [color[0], color[1]];
                let skill = skill_names[key] || key;
                divIDs.push("panel_kpi_"+key);
                if ($("#swkpi_"+key).length == 0){
                    let canvas_html = '';
                    canvas_html += '<div class="col-lg-2 col-md-4 col-sm-6 col-xs-12 pl-3 pr-3 pb-0" id="swkpi_'+key+'">';
                    canvas_html += '<div class="panel panel-default pl-0 pr-0 pb-0" id="panel_kpi_'+key+'">';
                    canvas_html += '<div class="panel-heading pl-0 pr-0 center bg-robi-ash clr-dark-ash txt-responsive-1 pb-3 pt-3">'+skill+'</div>';
                    canvas_html += '<div class="panel-body pl-0 pr-0 pb-0 pt-0">';
                    canvas_html += '<canvas id="chart_'+key+'" class="chartjs pl-0 pr-0"></canvas>';
                    canvas_html += '</div>';
                    canvas_html += '</div>';
                    canvas_html += '</div>';
                    $("#swkpi").append(canvas_html);
                    draw_donut("chart_"+key, labels, dataset_data, backgroundColor, '', swkpi[key].total_closed, "");
                } else {
                    draw_donut("chart_"+key, labels, dataset_data, backgroundColor, '', swkpi[key].total_closed, "");
                }
            }
            adjustHeight(divIDs)
        }
    };

    async function createDiv(parentElem, div_id, heading, value, div_bg_clr="bg-dark-blue", text_size="txt-30"){
        let div = '';
        div += '<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 pl-3 pr-3 pb-0">';
        div += '<div class="panel panel-default pr-0 pr-0" id="panel_'+div_id+'">';
        div += '<div class="panel-heading center bg-robi-ash clr-dark-ash pl-0 pr-0 txt-responsive-1 pb-3 pt-3">'+heading+'</div>';
        div += '<div class="panel-body clr-red center font-bolder pl-0 pr-0 pb-0 pt-0 '+text_size+' '+div_bg_clr+'" id="'+div_id+'">';
        div += value;
        div += '</div>';
        div += '</div>';
        div += '</div>';
        $(parentElem).append(div);
    }

    let updateView = (dataObj) => {
        let data = dataObj.data;
        let parentElement = dataObj.parentElement;
        let childElement = dataObj.childElement;
        let backgroundColor = dataObj.bg || "";
        let fontSize = dataObj.fontSize || "";
        let div_ids = [];

        if (typeof data !== 'undefined' && data != null && Object.keys(data).length > 0) {
            for (let item in data){
                let divID = $("#"+childElement+item);
                div_ids.push("panel_"+childElement+item);
                let heading = skill_names[item] || item;
                if (divID.length == 0){
                    createDiv(parentElement, childElement+item, heading, data[item], backgroundColor, fontSize);
                } else {
                    $(childElement+item).html(data[item]);
                }
            }
        }
        adjustHeight(div_ids);
    };

    let updateTotalKPI = (data) => {
        if (typeof data.total !== 'undefined' && data.total != null && Object.keys(data.total).length > 0){
            let all_labels = [ "In ("+data.total.total_in_kpi+")", "Out ("+data.total.total_out_kpi+")"];
            let all_dataset_data = [ data.total.total_in_kpi, data.total.total_out_kpi];
            let all_backgroundColor = ["#008000", "#ff0000"];
            let all_hoverBorderColor = ["#99d8a7", "#f99bad"];
            draw_donut("chart_total", all_labels, all_dataset_data, all_backgroundColor, all_hoverBorderColor, data.total.total_closed, " Email", "right", $("#swkpi_panel").height());
        }
    };

    let updateTotalKPIPercentage = (data) => {
        if (typeof data.total !== 'undefined' && data.total != null && Object.keys(data.total).length > 0){

            let total = data.total.total_in_kpi + data.total.total_out_kpi;
            let all_labels = [ "Total ("+total+")", "In ("+data.total.total_in_kpi+")"];
            let all_dataset_data = [ total, data.total.total_in_kpi];
            let all_backgroundColor = ["#f8ba03", "#476096"];
            let all_hoverBorderColor = ["#dddddd", "#e7e5e5"];
            let center_label = (data.total.total_in_kpi*100)/total;
            center_label = center_label.toFixed(1)+"%";
            draw_donut("chart_total_percentage", all_labels, all_dataset_data, all_backgroundColor, all_hoverBorderColor, center_label, " Email", "right", $("#swkpi_panel").height());
        }
    };

    async function getXHRresponse(url, data='') {
        resp_data = "";
        resp_data = $.ajax({
            dataType  : "JSON",
            type      : "POST",
            url       : url,
            data      : { data:data},
            success : function (res) {
                return res;
            }
        });
    }

    function draw_donut(canvas_id, inbox_labels, inbox_dataset_data, inbox_color_data, hoverBorderColor, center_text="", tooltiptext="", labelPosition='right', height='') {
        Chart.pluginService.register({
            beforeDraw: function (chart) {
                if (chart.config.options.elements.center) {
                    //Get ctx from string
                    var ctx = chart.chart.ctx;

                    if (height !='') ctx.height = height;
                    //Get options from the center object in options
                    var centerConfig = chart.config.options.elements.center;
                    var fontStyle = centerConfig.fontStyle || 'Arial';
                    var txt = centerConfig.text;
                    var color = centerConfig.color || '#000';
                    var sidePadding = centerConfig.sidePadding || 20;
                    var sidePaddingCalculated = (sidePadding/100) * (chart.innerRadius * 2);
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
                maintainAspectRatio: true,
                legend: {
                    display: true,
                    position: labelPosition,
                    labels: {
                        fontColor: '#282a3c',
                        usePointStyle: true,
                        fontSize: 13
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
                        color: '#444444',
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

</body>
</html>