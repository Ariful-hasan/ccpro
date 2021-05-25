<script type="text/javascript" src="js/bootbox/bootbox.min.js"></script>
<script type="text/javascript" src="js/toastr/toastr.min.js"></script>
<script type="text/javascript" src="js/Chart.min.js"></script>

<div id="chart_canvas_div" style="display: none;"></div>

<?php
	include_once "lib/jqgrid.php";
	$grid = new jQGrid();
	//$grid->caption = "Agent List";
	$grid->url = isset($dataUrl) ? $dataUrl : "";
	$grid->width="auto";//$grid->minWidth = 800;
	$grid->height = "auto";//390;
	$grid->rowNum = 20;
	$grid->pager = "#pagerb";
	$grid->container = ".content-body";
	$grid->hidecaption=false;
	$grid->afterInsertRow="AfterInsertRow";
	$grid->ShowReloadButtonInTitle=true;
	$grid->ShowDownloadButtonInTitle=true;
	$grid->DownloadFileName = $pageTitle;
	$grid->ShowDownloadButtonInBottom=false;
	$grid->CustomSearchOnTopGrid=true;
	$grid->multisearch=true;
	$grid->ShowReloadButtonInTitle=true;
	$grid->ShowDownloadButtonInTitle=true;
	$grid->DownloadFileName=$pageTitle;

	$grid->AddModel("Date", "sdate", 120, "center", ".date", "d/m/Y", "d/m/Y", true, "date");
	$grid->AddSearhProperty("Service Type", "service_type", "select", $service_type_list);
	$grid->AddSearhProperty("Skill Name", "skill_id", "select", $selected_skill_list);
	
    $grid->AddModelNonSearchable('Hour', "shour", 80,"center");
	$grid->AddModel('Service Type', "service_type", 80,"center");
	$grid->AddModel('Skill Name', "skill_id", 80, "center");
	$grid->AddModelNonSearchable('Call Count', "count", 80,"center");

	include('view/grid-tool-tips.php');
	$grid->addModelTooltips($tooltips);	
?>
<?php $grid->show("#searchBtn");?>	

<script type="text/javascript">
$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);

	$(".gs-date-from-picker-grid-options input, .gs-date-to-picker-grid-options input").datetimepicker({
        timepicker:false,
        format:'d/m/Y'
    });
    var label_data = <?php echo json_encode($chart_labels); ?>;
    var chart_data = <?php echo json_encode($chart_data); ?>;
    his_chart_show(label_data, chart_data);

    $("a.btn-show-chart").on('click', function(e){
        $("#chart_canvas_div").slideToggle();
        return false;
    });
    
    $("a.btn-warning").on('click', function(e){        
        var skill_name = $("#skill_id option:selected").text();
        $.ajax({
            method: "POST",
            url: "<?php echo $chartDataUrl; ?>",
            dataType: "json",
            data: $('.grid-search-panel form').serialize()+'&skill_name='+skill_name,
            success: function(resp){                
                his_chart_show(resp.labels, resp.data)
                return false;
            }
        });
        return false;
    })
});

function getRandomColor() {
    var letters = '0123456789ABCDEF';
    var color = '#';
    for (var i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

function his_chart_show(labels, data){
    $("#chart_canvas_div").html('<canvas id="his_data_chart"></canvas>');
    var ctx = document.getElementById("his_data_chart").getContext("2d");
    var label_data = labels;
    var chart_data = data;
    var chart_datasets= [];

    $.each(chart_data, function(idx, item){        
        var color = getRandomColor();
        var set_data = {
            label: idx,
            borderColor: color,
            backgroundColor: color,
            fill: false,
            data: item
        }
        chart_datasets.push(set_data);
    });
    
    var lineChartData = {
        labels: label_data,
        datasets: chart_datasets
    };
    
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
                text:'Skill Wise Historical Data',
                position:"top",
                fontSize:15
            },
            tooltips: {
                enabled: true,
                mode: 'single',
                callbacks: {
                    // label: function(tooltipItems, data) {
                    //     var date = new Date(null);
                    //     date.setSeconds(tooltipItems.yLabel);
                    //     return date.toISOString().substr(11, 8);
                    // }
                }
            },
            scales: {
                xAxes:[{
                    scaleLabel: {
                        display: true,
                        labelString: 'Date'
                    }
                }],

                yAxes: [{
                    ticks: {
                    //     callback: function(value, index, values) {
                    //         var date = new Date(null);
                    //         date.setSeconds(value); // specify value for SECONDS here
                    //         return date.toISOString().substr(11, 8);
                    //     }
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'Call'
                    }
                }]
            }
        }
    });
}
function AfterInsertRow(rowid, rowData, rowelem) {		 
	if(rowData.usertypemain=="S"){
		$('tr#' + rowid).addClass('bg-supervisor');    		
	}
}
</script>
