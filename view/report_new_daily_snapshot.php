<style type="text/css">
.service-level-table thead tr{background-color: #f1f1f1;}
.service-level-table td,.service-level-table th{min-width: 90px !important;text-align: center; padding: 5px !important;}
img{margin-top: 20px}
.progress{position: relative; top:100px;border: 1px solid #ddd;}
.panel{ text-align: center; padding:0;}
.table{margin-bottom: 0}
.table tr:last-child{border-bottom: 1px solid #ddd}
.legend{font-size: 12px; font-weight: bold; height: 30px}
.wait-time{
    text-align: left;
    font-size: 13em;
    line-height: 280px;
    text-shadow: 2px 2px 2px #ccc;
    color: #369bc2;
}
.wait-time-text{
    font-size: 50px;
    margin-top: -25px;
    text-align: left;
    margin-left: 28px;
    margin-bottom: 10px;
}
.gs-check-box{width: 28px !important; height: 28px !important; }
.box-panel{height: 404px;}
</style>
<script src="js/Chart.min.js"></script>

    <div class="panel panel-default">
        <div class="panel-body">

            <div class="col-md-12 col-lg-12 col-sm-12">
               <!-- <div class="panel panel-default">-->
                   <!-- <div class="panel-heading" style="text-align: center">Tabular Results</div>-->
                    <!--<div class="panel-body">-->
                        <div class="table-responsive" style="margin-bottom: 1%;">
                            <table class="table table-bordered table-striped table-hover  service-level-table"><!---->
                                <thead >
                                    <tr style="height:2px;">
                                        <th>Criterion</th>
                                        <th>Target</th>
                                        <!--<th>YTD</th>-->
                                        <th>MTD</th>
                                        <th>FTD</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Service Level</td>
                                        <td><?php echo '-'?></td>
                                        <!--<td><?php /*echo round($ytd[0]->service_level*100, 1)*/?>%</td>-->
                                        <td><?php echo sprintf('%.2f', $mtd[0]->service_level); ?>%</td>
                                        <td><?php echo sprintf('%.2f', $ftd[0]->service_level); ?>%</td>
                                    </tr>
                                    <!-- <tr>
                                        <td>Forecast Accuracy</td>
                                        <td><?php //echo '-'; ?></td>
                                        <td><?php //echo 0; ?>%</td>
                                        <td><?php //echo 0; ?>%</td>
                                        <td><?php //echo 0; ?>%</td>
                                    </tr> -->
                                    <tr>
                                        <td>Abandoned Ratio</td>
                                        <td><?php echo '-'?></td>
                                        <!-- <td><?php //echo 0; ?>%</td> -->
                                        <td><?php echo sprintf('%.2f', $mtd[0]->abandoned_ratio); ?>%</td>
                                        <td><?php echo sprintf('%.2f', $ftd[0]->abandoned_ratio); ?>%</td>
                                    </tr>
                                    <tr>
                                        <td>Average Wait Time</td>
                                        <td><?php echo '-'?></td>
                                        <!--<td><?php//*/ echo  round($ytd[0]->awt)*/?></td>-->
                                        <td><?php echo sprintf('%.2f', $mtd[0]->awt)?></td>
                                        <td><?php echo sprintf('%.2f', $ftd[0]->awt)?></td>
                                    </tr>
                                    <tr>
                                        <td>AHT</td>
                                        <td><?php echo '-'?></td>
                                       <!-- <td><?php /*//echo  round($ytd[0]->aht)*/?></td>-->
                                        <td><?php echo sprintf('%.2f', $mtd[0]->aht)?></td>
                                        <td><?php echo sprintf('%.2f', $ftd[0]->aht)?></td>
                                    </tr>
                                    <tr>
                                        <td>Workcode Saved %</td>
                                        <td><?php echo '-'; ?></td>
                                        <!-- <td><?php //echo round($ytd[0]->word_code*100,2).'%'; ?></td> -->
                                        <td><?php echo sprintf('%.2f', $mtd[0]->word_code).'%'; ?></td>
                                        <td><?php echo sprintf('%.2f', $ftd[0]->word_code).'%'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Repeat Calls %</td>
                                        <td><?php echo '-'?></td>
                                        <!-- <td><?php //echo  round($ytd[0]->repeat_call_per).'%'; ?></td> -->
                                        <td><?php echo sprintf('%.2f', $mtd[0]->repeat_call_per).'%'?></td>
                                        <td><?php echo sprintf('%.2f', $ftd[0]->repeat_call_per).'%'?></td>
                                    </tr>
                                    <!-- <tr>
                                        <td>Call per customer</td>
                                        <td><?php /*echo '0'*/?></td>
                                        <td><?php /*//echo  round($ytd[0]->cpc*100).'%'*/?></td>
                                        <td><?php /*echo round($mtd[0]->cpc*100).'%'*/?></td>
                                        <td><?php /*echo round($ftd[0]->cpc*100).'%'*/?></td>
                                    </tr> -->
                                </tbody>
                            </table>
                        </div>
                   <!-- </div>-->
                <!--</div>-->
            </div>

            <div class="col-md-12 col-lg-12 col-sm-12">
                <div class="col-md-12 " >
                    <!--Service Level-->
                    <div class="col-md-4 panel panel-default box-panel">
                        <div class="col-sm-12 col-md-12" style="padding: 0">
                            <?php echo generateDIV('0', round($ytd[0]->service_level, 1), round($mtd[0]->service_level, 1), round($ftd[0]->service_level, 1), 'service-level');?>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-12 service-level-container">
                            <div class="progress">
                                <div class="progress-bar <?php echo round($ftd[0]->service_level) < 60 ? 'progress-bar-danger' : 'progress-bar-success' ?> progress-bar-striped active" role="progressbar" aria-valuenow="<?php echo round($ftd[0]->service_level) ?>"
                                     aria-valuemin="0" aria-valuemax="100" style="width:<?php echo round($ftd[0]->service_level) ?>%">
                                    <?php echo round($ftd[0]->service_level) ?>%
                                </div>
                            </div>

                        </div>
                        <div class=" col-md-12 col-sm-12 text-center legend" >Service Level</div>

                    </div>
                    <!--Service Level END-->

                    <!--Forecast Acuracy-->
                    <!--<div class="col-md-3 panel panel-default">
                        <?php /*echo generateDIV('90-100%', '70%', '90%', '100%', 'forecast-acuracy');*/?>
                    </div>-->
                    <!--Forecast Acuracy END-->

                     <!--Abandoned Ratio START-->
                    <div class="col-md-4 panel panel-default box-panel">
                        <?php echo generateDIV('0', round($ytd[0]->abandoned_ratio, 1).'%', round($mtd[0]->abandoned_ratio, 1).'%', round($ftd[0]->abandoned_ratio, 1).'%', 'abandoned-ration');?>
                    </div>
                    <!--Abandoned Ratio END-->

                    <!--AVG Wait Time-->
                    <div class="col-md-4 panel panel-default box-panel">
                        <div class="col-sm-12 col-md-12" style="padding: 0">
                            <?php echo generateDIV('0', round($ytd[0]->awt,1), round($mtd[0]->awt,1), round($ftd[0]->awt,1), 'avg-wait-time');?>
                        </div>
                        <div class="col-sm-12 col-md-12">
                            <div class="col-sm-6 col-md-6">
                                <img src="assets/images/icon-timer.png" alt="Average Wait Time" width="100%" height="100%">
                            </div>
                            <div class="col-sm-6 col-md-6"> 
                                <div class="wait-time"><?php echo round($ftd[0]->awt)?></div>
                                <div class="wait-time-text">Sec</div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12 text-center legend">Average Wait Time</div>
                    </div>
                    <!--AVG Wait Time END-->
                </div>

                <div class="col-md-12">
                    <!--Average Hnadling Time-->
                    <div class="col-md-4 panel panel-default box-panel">
                        <div class="col-sm-12 col-md-12" style="padding: 0">
                            <?php echo generateDIV('0%', round($ytd[0]->aht,1), round($mtd[0]->aht,1), round($ftd[0]->aht,1), 'avg-handling');?>
                        </div>
                        <div class="col-sm-12 col-md-12" id="handling-time">
                            <div class="col-sm-6 col-md-6">
                                <img src="assets/images/callcentre.png?v=1" alt="Average handling Time" width="100%" height="100%">
                            </div>
                            <div class="col-sm-6 col-md-6 aht" style="font-size: 4em;line-height: 258px;text-align: left;"> <?php echo round($ftd[0]->aht,1)?> </div>
                        </div>
                        <div class="col-sm-12 col-md-12 text-center legend">Average Handling Time</div>
                    </div>
                    <!--Average Hnadling END-->

                    <!--WORK CODE START-->
                    <div class="col-md-4 panel panel-default box-panel">
                        <?php echo generateDIV('0%', round($ytd[0]->word_code,1).'%', round($mtd[0]->word_code,1).'%', round($ftd[0]->word_code,1).'%', 'work-code');?>
                    </div>
                    <!--WORK CODE END-->

                    <!--repeat_calls-->
                    <div class="col-md-4 panel panel-default box-panel">
                        <?php echo generateDIV('0%', round($ytd[0]->repeat_call_per,1).'%', round($mtd[0]->repeat_call_per,1).'%', round($ftd[0]->repeat_call_per,1).'%', 'repeat-calls');?>
                    </div>
                    <!--repeat_calls END-->

                    <!--CPC START-->
                  <!--  <div class="col-md-3 panel panel-default">
                        <?php /*echo generateDIV('0%', round($ytd[0]->cpc*100).'%', round($mtd[0]->cpc*100).'%', round($ftd[0]->cpc*100).'%', 'cpc');*/?>
                   </div>-->
                    <!--CPC END-->
                </div>
            </div>

        </div>
    </div>

<?php
function generateDIV($target, $ytd, $mtd, $ftd, $element){
    $html = "<table class='table small table table-bordered text-center' >";
    $html .= "<tr><th class='text-center'>Target</th>";
    //$html .= "<th class='text-center'>YTD</th>";
    $html .= "<th class='text-center'>MTD</th><th class='text-center'>FTD</th></tr>";
    // $html .= "<tr><td>{$target}</td>";
    $html .= "<tr><td>-</td>";
    //$html .= "<td>{$ytd}</td>";
    $html .= "<td>{$mtd}</td><td>{$ftd}</td></tr>";
    $html .= "</table>";
    $html .= '<canvas id="'.$element.'" width="800" height="450"></canvas>';
    return $html;
}
?>

<script type="text/javascript">
    $( document ).ready(function() {

        /////////Sservice level START//////////
        generateDonut("service-level", <?php echo json_encode(round($ytd[0]->service_level,1)); ?> ,<?php echo json_encode(round($mtd[0]->service_level,1)); ?> ,<?php echo json_encode(round($ftd[0]->service_level,1)); ?>, "#ff633b", "#c53bff","#baec16", "Overall Service Level", 100);
        /////////Sservice level  END//////////

        /////////Forecast Acuraqcy START//////////
       // generateDonut("forecast-acuracy", <?php //echo json_encode(0)?> ,<?php //echo json_encode(0)?> ,<?php //echo json_encode(0)?>, "#002857", "#a2dfc7","#c74812", "Forecast Acuracy");
        /////////Sservice level  END//////////

        /////////ABANDOED RATIO START//////////
        generateDonut("abandoned-ration", <?php echo json_encode(round($ytd[0]->abandoned_ratio,1)); ?> ,<?php echo json_encode(round($mtd[0]->abandoned_ratio,1)); ?> ,<?php echo json_encode(round($ftd[0]->abandoned_ratio,1)); ?>, "#997623", "#f1f1f1","#ffc53b", "Abandoned Ration", 100);
        /////////ABANDOED END//////////


        /////////WORK CODE START//////////
        generateDonut("work-code", <?php echo json_encode(round($ytd[0]->word_code,1)); ?> ,<?php echo json_encode(round($mtd[0]->word_code,1)); ?> ,<?php echo json_encode(round($ftd[0]->word_code,1)); ?>, "#997623", "#f1f1f1","#ffc53b", "Workcode Saved %", 100);
        /////////AHT  END//////////

        /////////REPEAT CALLS START//////////
        generateDonut("repeat-calls", <?php echo json_encode(round($ytd[0]->repeat_call_per,1)); ?> ,<?php echo json_encode(round($mtd[0]->repeat_call_per,1)); ?> ,<?php echo json_encode(round($ftd[0]->repeat_call_per,1)); ?>, "#997623", "#f1f1f1","#ffc53b", "Repeat Calls %", 100);
        /////////REPEAT CALLS  END//////////

        /////////CPC START//////////
       // generateDonut("cpc", <?php //echo json_encode(round($ytd[0]->cpc*100))?> ,<?php //echo json_encode(round($mtd[0]->cpc*100))?> ,<?php //echo json_encode(round($ftd[0]->cpc*100))?>, "#95ff20", "#e5004f","#102e1f", "CPC");
        /////////AHT  END//////////

        $("#wait-time, .service-level-container").css("height", $("#abandoned-ration").attr('height') - 30);
        $("#handling-time").css("height", $("#work-code").attr('height') - 30);
        $("#wait-time").css("width", $("#abandoned-ration").attr('width'));

        $("#avg-wait-time, #avg-handling, #service-level").remove();
    });

    function generateDonut(element, year,  month, day, bgcolor1, bgcolor2, bgcolor3, text, target=100) {
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
        console.log(day);
        new Chart(document.getElementById(element), {
            type: 'doughnut',
            data: {
                labels: ['',"FTD"],
                datasets: [{
                    backgroundColor: [bgcolor2, bgcolor3],
                    data: [target, day > 0 ? day : 0.00000000000000000000001]
                }]
            },
            options: {
                title: {
                    display: true,
                    position: 'bottom',
                    text: text
                },
                elements: {
                    center: {
                        text: day+"%",
                        color: '#FF6384', // Default is #000000
                        fontStyle: 'Arial', // Default is Arial
                        sidePadding: 20 // Defualt is 20 (as a percentage)
                    }
                }
            }
        });
    }

</script>
