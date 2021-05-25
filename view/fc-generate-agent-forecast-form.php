<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>
<script type="text/javascript" src="assets/plugins/jquery.blockUI/jquery.blockUI.js"></script>
<script type="text/javascript" src="js/Chart.min.js"></script>

<?php 
$form_url = $this->url('task='.$request->getControllerName()."&act=generate-data");
?>
<form id="frm_agent_generate_forecast" class="form-horizontal" enctype="multipart/form-data" class="form" action="<?=$form_url?>" autocomplete="off" method="post">
	<div class="panel-body">
        <div class="row">
            <div id="resp_msg_block" class="col-md-12 col-sm-12">                
                
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 col-sm-8">
                <div class="form-group form-group-sm mb-15">
                    <label for="model" class="control-label col-md-2 col-sm-2 pr-0">
                        Model:<span class='error'>*</span>
                    </label>
                    <div class="col-md-4 col-sm-4">
                        <select id="model" name="model" class="form-control" 
                            data-rule-required='true' 
                            data-msg-required='Model is required!'
                        >
                            <option value="">---Select---</option>
                            <?php foreach ($model_list as $key => $value) { ?>
                                <option value="<?php echo $key; ?>" <?php echo (isset($fc_generate_data->model) && $fc_generate_data->model == $key) ? 'selected="selected"' : ''; ?> ><?php echo $value; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <label for="type" class="control-label col-md-2 col-sm-2 pr-0">
                        Type:<span class='error'>*</span>
                    </label>
                    <div class="col-md-4 col-sm-4">
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
                    <label for="avg_call_duration" class="control-label col-md-2 col-sm-2 pr-0">
                        Avg. Call Duration(s):<span class='error'>*</span>
                    </label>
                    <div class="col-md-4 col-sm-4">
                        <input type="text" id="avg_call_duration" name="avg_call_duration" class="form-control" data-rule-required='true' data-msg-required='Avg. Call Duration is required!' />
                    </div>
                    <label for="type" class="control-label col-md-2 col-sm-2 pr-0">
                        Total Interval Length(s):<span class='error'>*</span>
                    </label>
                    <div class="col-md-4 col-sm-4">
                        <input type="text" id="total_interval_length" name="total_interval_length" class="form-control" data-rule-required='true' data-msg-required='Total Interval Length is required!' />
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15">
                    <label for="model" class="control-label col-md-2 col-sm-2 pr-0">
                        Agent Occupancy(%):<span class='error'>*</span>
                    </label>
                    <div class="col-md-4 col-sm-4">
                        <input type="text" id="agent_occupancy" name="agent_occupancy" class="form-control" data-rule-required='true' data-msg-required='Agent Occupancy is required!' />
                    </div>
                    <label for="type" class="control-label col-md-2 col-sm-2 pr-0">
                        Wait Time(s):<span class='error'>*</span>
                    </label>
                    <div class="col-md-4 col-sm-4">
                        <input type="text" id="wait_time" name="wait_time" class="form-control" data-rule-required='true' data-msg-required='Wait Time is required!' />
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15">
                    <label for="model" class="control-label col-md-2 col-sm-2 pr-0">
                        Service Level (%):<span class='error'>*</span>
                    </label>
                    <div class="col-md-4 col-sm-4">
                        <input type="text" id="service_level" name="service_level" class="form-control" data-rule-required='true' data-msg-required='Service Level is required!' />
                    </div>
                    <label for="type" class="control-label col-md-2 col-sm-2 pr-0">
                        Shrinkage:<span class='error'>*</span>
                    </label>
                    <div class="col-md-4 col-sm-4">
                        <input type="text" id="shrinkage" name="shrinkage" class="form-control" data-rule-required='true' data-msg-required='Shrinkage is required!' />
                    </div>
                </div>
                <div class="form-group form-group-sm mb-15">
                    <label for="service_type" class="control-label col-md-2 col-sm-2 pr-0">
                        Service Type:<span class='error'>*</span>
                    </label>
                    <div class="col-md-4 col-sm-4">
                        <select id="service_type" name="service_type" class="form-control" 
                            data-rule-required='true' 
                            data-msg-required='Service Type is required!'
                        >
                            <!-- <option value="">---Select---</option> -->
                            <?php foreach ($service_type_list as $key => $value) { ?>
                                <option value="<?php echo $key; ?>" <?php echo (isset($fc_generate_data->service_type) && $fc_generate_data->service_type == $key) ? 'selected="selected"' : ''; ?> ><?php echo $value; ?></option>
                            <?php } ?>
                        </select>
                        <?php 
                        if(isset($error_data['service_type']) && isset($error_data['service_type'][0])){ 
                        ?>
                            <span class="form-error"><?=$error_data['service_type'][0]?></span>
                        <?php } ?>
                    </div>
                    <label for="skill_id" class="control-label col-md-2 col-sm-2 pr-0">
                        Skill:<span class='error'>*</span>
                    </label>
                    <div id="skill_id_div" class="col-md-4 col-sm-4">

                    </div>
                </div>                
                <div class="form-group form-group-sm mb-15 text-center">
                	<div class="col-md-3 col-sm-3"></div>
                	<div class="col-md-9 col-sm-9">
                		<button class="btn btn-success fc-generate-button" id="generate" name="action" value="Generate" >Generate</button>
                	</div>
                </div>
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
                        <button class="btn btn-success fc-generate-button" id="save" name="action" value="Save" >Save</button>
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
    var fc_skill_id = '<?php echo (isset($fc_generate_data->skill_id) && !empty($fc_generate_data->skill_id)) ? $fc_generate_data->skill_id : ''; ?>';       
    var loading = 0;
    var min_forecast_date_h = '<?php echo $min_forecast_date_h; ?>';
    var min_forecast_date_d = '<?php echo $min_forecast_date_d; ?>';

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
        var minDate = '';
        jQuery('#sdate').val('');
        jQuery('#edate').val('');

        if(val=='H'){
            minDate = min_forecast_date_h;
        }else{
            minDate = min_forecast_date_d;
        }

        jQuery('#sdate').datetimepicker({
            timepicker:false,
            format:'d/m/Y',
            minDate: minDate,
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
            minDate: minDate, 
        });
    });

    $("#type").trigger('change');
    $("#service_type").trigger('change');

    $('#frm_agent_generate_forecast').validate({
        errorClass: "form-error",
        errorElement: "span",
    });

    $('.fc-generate-button').on('click', function(e){            
        var button_val = $(this).val();
        var download_link = '<?php echo $this->url('task='.$request->getControllerName()."&act=forecast-data-download"); ?>';
        var sdate = $("#sdate").val();
        var edate = $("#edate").val();
        var type = $("#type").val();
        var skill_ids = $("#frm_agent_generate_forecast input:checkbox:checked").map(function(){
            return $(this).val();
        }).get();

        if($('#frm_agent_generate_forecast').valid()){
            // $("#loading_data").removeClass("hide");
            $('form#frm_agent_generate_forecast').block({ 
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
                $('form#frm_agent_generate_forecast').block({ 
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
                $("button#generate").removeClass("hide");
                $("button#view").removeClass("hide");
                $("#resp_msg_block .alert").remove();

                $('form#frm_agent_generate_forecast').unblock();
            }else{
                $.ajax({
                    method: "POST",
                    url: "<?=$form_url?>",
                    dataType: "json",
                    // dataType: "html",
                    data: $('#frm_agent_generate_forecast').serialize()+'&action='+button_val,
                    success: function(resp){
                        // console.log(respD);
                        // var resp = JSON.parse(respD);
                        console.log(resp);
                        // $("#loading_data").addClass("hide");
                        if(resp.result==1){
                            $('span.form-error').remove();
                            $('.form-control').removeClass('form-error');
                            $("#fc_download").attr('href', download_link+"&sdate="+sdate+"&edate="+edate+"&skill_ids="+skill_ids.join(',')+"&type="+type);

                            if(loading==0){
                                $("button#generate").addClass("hide");
                                $("button#view").addClass("hide");
                            }
                            var resp_msg_block = '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Success!</strong> '+resp.message+' </div>';
                            $("#resp_msg_block").html(resp_msg_block);
                            loading = +loading +1;

                            // forecast list data
                            if(resp.data.length > 0){
                                var list_dom = "<h3>Forecast Data for "+resp.skill_name+" </h3>";
                                if(typeof resp.his_data !='undefined'){
                                    list_dom += '<p style="color:red;">**Historical data from '+resp.his_data.his_sdate+' to '+resp.his_data.his_edate+'</p>';
                                }
                                list_dom += '<div class="table-responsive"><table class="table table-bordered"><thead>';
                                var skill_name_arr = resp.skill_name.split(",");

                                //main header
                                list_dom += "<tr>";
                                list_dom += '<th>&nbsp;</th>';
                                if(type=='H')
                                    list_dom += '<th>&nbsp;</th>';                                
                                $.each(skill_name_arr, function(idx, item){
                                    list_dom += '<th class="text-center" colspan="'+resp.colspan+'">'+item+'</th>';
                                });
                                list_dom += "</tr>";

                                //subheader
                                list_dom += "<tr>";
                                $.each(resp.data[0], function(idx, item){
                                    list_dom += '<th>'+item+'</th>';
                                });
                                list_dom += "</tr>";

                                // list_dom += '<th>'+resp.data[0].date+'</th>';
                                // if(typeof resp.data[0].hour !='undefined'){
                                //     list_dom += '<th>'+resp.data[0].hour+'</th>';
                                // }
                                // list_dom += '<th class="text-center">'+resp.data[0].count+'</th>';

                                // if(typeof resp.data[0].increase !='undefined')
                                //     list_dom += '<th class="text-center">'+resp.data[0].increase+'</th>';
                                // if(typeof resp.data[0].decrease !='undefined')
                                //     list_dom += '<th class="text-center">'+resp.data[0].decrease+'</th>';

                                // list_dom += '<th class="text-center">'+resp.data[0].agents+'</th>';

                                list_dom += '</thead><tbody>';                                   

                                $.each(resp.data, function(idx, items){
                                    if(idx==0) return true; 

                                    list_dom += "<tr>";
                                    $.each(items, function(i, item){
                                        list_dom += "<td>"+item+"</td>";                                        
                                    });
                                    list_dom += "</tr>";
                                });

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
                                            text:'Skill Wise Forecast',
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
                            var resp_msg_block = '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Error!</strong> '+resp.message+' </div>';
                            $("#resp_msg_block").html(resp_msg_block);
                            if(resp.error_data.length != 0){
                                $('span.form-error').remove();
                                $.each(resp.error_data, function(idx, item){
                                    $("#"+idx).after('<span class="form-error">'+item[0]+'</span>');
                                    $("#"+idx).addClass("form-error");
                                });
                            }
                        }                   

                        $('form#frm_agent_generate_forecast').unblock();
                        return false;
                    }
                });
            }
        }

        return false;
    });
    
    $("a#fc_download").on('click', function(){
        $("#MainLoader").remove();
    });

    jQuery('#sdate').datetimepicker({
        timepicker:false,
        format:'d/m/Y',
        minDate: min_forecast_date,            
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
        minDate: min_forecast_date, 
    });
});
</script>