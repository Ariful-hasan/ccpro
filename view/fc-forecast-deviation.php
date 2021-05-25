<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>
<script type="text/javascript" src="assets/plugins/jquery.blockUI/jquery.blockUI.js"></script>
<script type="text/javascript" src="js/Chart.min.js"></script>

<?php 
$form_url = $this->url('task='.$request->getControllerName()."&act=forecast-deviation-data");
?>
<form id="frm_deviation_forecast" class="form-horizontal" enctype="multipart/form-data" class="form" action="<?=$form_url?>" autocomplete="off" method="post">
	<div class="panel-body">
        <div class="row">
            <div id="resp_msg_block" class="col-md-12 col-sm-12">                
                
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-sm-6">  
                <div class="form-group form-group-sm mb-15">
                    <label for="sdate" class="control-label col-md-2 col-sm-2 pr-0">
                        Type:<span class='error'>*</span>
                    </label>
                    <div class="col-md-5 col-sm-5">
                        <select id="type" name="type" class="form-control" 
                            data-rule-required='true' 
                            data-msg-required='Type is required!'
                        >
                            <option value="">---Select---</option>
                            <?php foreach ($generate_type_list as $key => $value) { ?>
                                <option value="<?php echo $key; ?>" <?php echo (isset($fc_generate_data->type) && $fc_generate_data->type == $key) ? 'selected="selected"' : ''; ?> ><?php echo $value; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-5 col-sm-5">
                        &nbsp;
                    </div>
                </div>

                <div class="form-group form-group-sm mb-15">
                    <label for="sdate" class="control-label col-md-2 col-sm-2 pr-0">
                        Date:<span class='error'>*</span>
                    </label>
                    <div class="col-md-5 col-sm-5">
                        <input id="sdate" type="text" name="sdate"  
                            class="form-control"
                            data-rule-required='true' 
                            data-msg-required='Date is required!'
                            value="<?php echo (isset($fc_generate_data->sdate) && !empty($fc_generate_data->sdate)) ? $fc_generate_data->sdate : ''; ?>"
                            placeholder="From: DD/MM/YYYY" 
                        >
                    </div>
                    <div class="col-md-5 col-sm-5">
                        <input id="edate" type="text" name="edate"  
                            class="form-control"
                            data-rule-required='true' 
                            data-msg-required='Date is required!'
                            value="<?php echo (isset($fc_generate_data->edate) && !empty($fc_generate_data->edate)) ? $fc_generate_data->edate : ''; ?>"
                            placeholder="To: DD/MM/YYYY" 
                        >
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15">
                    <label for="service_type" class="control-label col-md-2 col-sm-2 pr-0">
                        Service Type:<span class='error'>*</span>
                    </label>
                    <div class="col-md-5 col-sm-5">
                        <select id="service_type" name="service_type" class="form-control" 
                            data-rule-required='true' 
                            data-msg-required='Service Type is required!'
                        >
                            <?php foreach ($service_type_list as $key => $value) { ?>
                                <option value="<?php echo $key; ?>" <?php echo (isset($fc_generate_data->service_type) && $fc_generate_data->service_type == $key) ? 'selected="selected"' : ''; ?> ><?php echo $value; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <label for="skill_id" class="control-label col-md-1 col-sm-1 pr-0">
                        Skill:<span class='error'>*</span>
                    </label>
                    <div id="skill_id_div" class="col-md-4 col-sm-4">
                        
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15 text-center">
                    <div class="col-md-3 col-sm-3"></div>
                    <div class="col-md-9 col-sm-9">
                        <button class="btn btn-primary fc-generate-button" id="view" name="action" value="View" >View</button>
                    </div>
                </div>
            </div>   
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                
                    <!-- <div class="alert alert-danger alert-dismissible" role="alert"> -->
                        <!-- <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button> -->
                        <!-- <strong>Sorry!</strong> There are no forecast data. That's why we can not show you forecast deviation. -->
                    <!-- </div> -->
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div id="loading_data" class="text-center hide">
                    <br/><br/>
                    <img src="assets/images/5.gif">
                    <br/><br/>
                </div>
            </div>
            <div id="list_chart" class="col-md-12 col-sm-12 hide">
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#list_tab">List</a></li>
                    <li><a data-toggle="tab" href="#chart_tab">Chart</a></li>
                    <li class="pull-right">
                        <a href="<?php echo $this->url('task='.$request->getControllerName()."&act=forecast-data-download"); ?>" class="btn btn-primary fc-download" id="fc_download">Download</a>
                        <button class="btn btn-warning fc-generate-button" id="re_generate" name="action" value="Re-generate" >Re-generate</button>
                        <button class="btn btn-danger fc-generate-button" id="cancel" name="action" value="Cancel" >Cancel</button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div id="list_tab" class="tab-pane fade in active">
                        
                    </div>
                    <div id="chart_tab" class="tab-pane fade">
                        <canvas id="fc_skill_chart_tab"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
function getRandomColor() {
    var letters = '0123456789ABCDEF';
    var color = '#';
    for (var i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

$( document ).ready(function() {
    var fc_skill_list = <?php echo json_encode($fc_skill_list); ?>;
    var fc_skill_id = '<?php echo (isset($fc_deviation_data->skill_id) && !empty($fc_deviation_data->skill_id)) ? $fc_deviation_data->skill_id : ''; ?>'; 
    var min_date = '<?php echo $min_date; ?>';
    var min_date_hourly = '<?php echo $min_date_hourly; ?>';
    var loading = 0;

    $("#service_type").on("change", function(){
        var val = $(this).val();

        if(val && typeof(fc_skill_list[val]) !='undefined'){
            var str = '<ul class="list-inline">';
            var count = 0;
            $.each(fc_skill_list[val], function(idx, item){
                var checked_str = '';
                var required_str = '';
                if(fc_skill_id==idx)
                    checked_str = 'checked="checked"';
                if(count==0){
                    count = 1;
                    required_str = 'data-rule-required="true" data-msg-required="Skill is required!"';
                }

                str += '<li><input name="skill_id[]" type="checkbox" value="'+idx+'" '+checked_str+' '+required_str+' >'+item.name+'</li>';
            });
            str += '</ul>';

            $("#skill_id_div").html(str);
        }else{
            $("#skill_id_div").html('');
        }
    });

    $("#type").on("change", function(){
        var val = $(this).val();
        if(val=='H'){
            jQuery('#sdate').val('');
            jQuery('#edate').val('');

            jQuery('#sdate').datetimepicker({
                timepicker:false,
                format:'d/m/Y',
                minDate: min_date_hourly,            
                onSelectDate:function(ct,$i){
                    jQuery('#edate').datetimepicker({
                        timepicker:false,
                        format:'d/m/Y',
                        minDate: ct.dateFormat('Y/m/d'),
                    });
                }
            });

            jQuery('#edate').datetimepicker({
                timepicker:false,
                format:'d/m/Y',            
                minDate: min_date_hourly, 
            });
        }else{
            jQuery('#sdate').val('');
            jQuery('#edate').val('');
            
            jQuery('#sdate').datetimepicker({
                timepicker:false,
                format:'d/m/Y',
                minDate: min_date,            
                onSelectDate:function(ct,$i){
                    jQuery('#edate').datetimepicker({
                        timepicker:false,
                        format:'d/m/Y',
                        minDate: ct.dateFormat('Y/m/d'),
                    });
                }
            });

            jQuery('#edate').datetimepicker({
                timepicker:false,
                format:'d/m/Y',            
                minDate: min_date, 
            });
        }
    });

    $("#type").trigger('change');
    $("#service_type").trigger('change');

    $('#frm_deviation_forecast').validate({
        errorClass: "form-error",
        errorElement: "span",
    });

    $('.fc-generate-button').on('click', function(e){            
        var button_val = $(this).val();
        var download_link = '<?php echo $this->url('task='.$request->getControllerName()."&act=forecast-deviation-download"); ?>';
        var sdate = $("#sdate").val();
        var edate = $("#edate").val();
        var type = $("#type").val();
        var service_type = $("#service_type").val();
        var skill_ids = $("#frm_deviation_forecast input:checkbox:checked").map(function(){
            return $(this).val();
        }).get();

        if($('#frm_deviation_forecast').valid()){
            $('form#frm_deviation_forecast').block({ 
                message: '<h2 style="color:#fff;">Processing</h2>',
                css: { 
                    border: 'none', 
                    padding: '15px', 
                    background: 'none',
                    color: '#fff' 
                }
            });
            if(loading==0){
                $("#list_chart").addClass("hide");
            }

            if(button_val=='Cancel'){
                // $("#loading_data").addClass("hide");
                $('form#frm_deviation_forecast').block({ 
                    message: '<h2 style="color:#fff;">Processing</h2>',
                    css: { 
                        border: 'none', 
                        padding: '15px', 
                        background: 'none',
                        color: '#fff' 
                    }
                });

                $("#list_chart").addClass("hide");
                loading = 0;
                $("#list_chart #list_tab").html("");
                $("#list_chart #chart_tab").html("");
                $("#list_chart #chart_tab").html('<canvas id="fc_skill_chart_tab"></canvas>');
                $("button#view").removeClass("hide");
                $("#resp_msg_block .alert").remove();
                $('form#frm_deviation_forecast').unblock();
            }else{
                $.ajax({
                    method: "POST",
                    url: "<?=$form_url?>",
                    dataType: "json",
                    data: $('#frm_deviation_forecast').serialize()+'&action='+button_val,
                    success: function(resp){
                        console.log(resp);
                        if(resp.result==1){
                            $('span.form-error').remove();
                            $('.form-control').removeClass('form-error');
                            $("#fc_download").attr('href', download_link+"&sdate="+sdate+"&edate="+edate+"&skill_ids="+skill_ids.join(',')+"&type="+type+"&service_type="+service_type);

                            if(loading==0){
                                $("button#generate").addClass("hide");
                                $("button#view").addClass("hide");
                            }
                            var resp_msg_block = '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Success!</strong> '+resp.message+' </div>';
                            $("#resp_msg_block").html(resp_msg_block);
                            loading = +loading +1;
                            
                            // forecast list data
                            if(resp.data.length > 0){
                                var list_dom = "<h3>Forecast Deviation for "+resp.header_col.join(',')+" </h3>";
                                // if(typeof resp.avg_deviation !='undefined' && resp.avg_deviation != ''){
                                //     list_dom += '<p style="color:red;">**Average deviation '+resp.avg_deviation+'</p>';
                                // }
                                
                                list_dom += '<div class="table-responsive"><table class="table table-bordered"><thead>';
                                //main header
                                list_dom += "<tr>";
                                list_dom += '<th>&nbsp;</th>';
                                if(type=='H')
                                    list_dom += '<th>&nbsp;</th>';
                                $.each(resp.header_col, function(idx, item){
                                    list_dom += '<th class="text-center" colspan="3">'+item+'</th>';
                                });
                                list_dom += "</tr>";

                                //subheader
                                list_dom += "<tr>";
                                $.each(resp.sub_header_col, function(idx, item){
                                    list_dom += '<th>'+item+'</th>';
                                });
                                list_dom += "</tr>";
                                list_dom += '</thead><tbody>';

                                $.each(resp.data, function(idx, item_list){
                                    list_dom += "<tr>";
                                    $.each(item_list, function(i, item){
                                        list_dom += "<td>"+item+"</td>";
                                    });  
                                    list_dom += "</tr>";                              
                                });

                                //main header
                                list_dom += "<tr>";
                                list_dom += '<td>&nbsp;</td>';
                                if(type=='H')
                                    list_dom += '<td>&nbsp;</td>';
                                $.each(resp.deviation_total, function(idx, item){
                                    list_dom += '<td class="text-center" colspan="2" style="color:red;">Total Deviation</td>';
                                    list_dom += '<th class="text-center" style="color:red;">'+item+'</th>';
                                });
                                list_dom += "</tr>";

                                list_dom += "</tbody></table></div>";
                                $("#list_tab").html(list_dom);
                            }

                            // forecast chart
                            if(typeof resp.chart_data !='undefined'){
                                $("#list_chart #chart_tab").html('<canvas id="fc_skill_chart_tab"></canvas>');
                                var ctx = document.getElementById("fc_skill_chart_tab").getContext("2d");
                                var label_data = resp.chart_data.labels;
                                var chart_data = resp.chart_data.data;
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
                                console.log(lineChartData);

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
                                            text:'Forecast Deviation',
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

                                $("#list_chart").removeClass("hide");
                            }
                        }else{
                            var resp_msg_block = '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>'+resp.type+'!</strong> '+resp.message+' </div>';
                            $("#resp_msg_block").html(resp_msg_block);
                            if(resp.error_data.length != 0){
                                $('span.form-error').remove();
                                $.each(resp.error_data, function(idx, item){
                                    $("#"+idx).after('<span class="form-error">'+item[0]+'</span>');
                                    $("#"+idx).addClass("form-error");
                                });
                            }

                            if(resp.type='Sorry'){
                                $("#list_chart").addClass("hide");
                                loading = 0;
                                $("#list_chart #list_tab").html("");
                                $("#list_chart #chart_tab").html("");
                                $("#list_chart #chart_tab").html('<canvas id="fc_skill_chart_tab"></canvas>');
                                $("button#view").removeClass("hide");
                            }
                        }                   

                        $('form#frm_deviation_forecast').unblock();
                        return false;
                    }
                });
            }
            return false;
        }

        return false;
    });
        
    $("a#fc_download").on('click', function(){
        $("#MainLoader").remove();
    });

    
});
</script>