
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.min.js"></script>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Search Area</div>
            <div class="panel-body">
                <form action="<?php echo $this->url("task=report-new&act=daily-service-level-chart"); ?>" class="form-horizontal" method="post">

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
                        <button type="submit" class="btn btn-md btn-success" style="margin-right: -10px!important;"><i class=" fa fa-search"></i> Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <br>
    <div class="col-md-12 ">
        <div class="col-md-12 chart-wrapper text-center">
            <canvas id="daily-service-level-chart"></canvas>
        </div>
    </div>
</div>

<script>

    var skill_services = <?php echo json_encode($services); ?>;
    var skills = <?php echo json_encode($skills); ?>;

    var service_level_dataset = [];

    $.each(skill_services,function (index, object) {
        arr = $.map(sortObject(object), function(k) { return k });
        color = getRandomColor();

        single_skill_service = {
            label: window.skills[index],
            data:arr,
            fill: false,
            backgroundColor: color,
            borderColor: color
        }
        service_level_dataset.push(single_skill_service);
    });

    function getRandomColor() {
        var letters = '0123456789ABCDEF';
        var color = '#';
        for (var i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }



   /* function sortObjectKeys(obj){
        return Object.keys(obj).sort().reduce((acc,key)=>{
                acc[key]=obj[key];
        return acc;
    },{});
    } */


    function sortObject(object){
        var sortedObj = {},
            keys = Object.keys(object);

        keys.sort(function(key1, key2){
            key1 = key1.toLowerCase(), key2 = key2.toLowerCase();
            if(key1 < key2) return -1;
            if(key1 > key2) return 1;
            return 0;
        });

        for(var index in keys){
            var key = keys[index];
            sortedObj[key] = object[key];
        }
        return sortedObj;
    }


    var hours = ["12:00 AM", "1:00 AM", "2:00 AM","3:00 AM","4:00 AM","5:00 AM","6:00 AM","7:00 AM","8:00 AM","9:00 AM","10:00 AM","11:00 AM","12:00 PM","1:00 PM","2:00 PM","3:00 PM","4:00 PM","5:00 PM","6:00 PM","7:00 PM","8:00 PM","9:00 PM","10:00 PM","11:00 PM"];
    var config = {
        type: 'line',
        data: {
            labels: hours,
            datasets: service_level_dataset,
        },
        options: {
            responsive: true,
            title:{
                display:true,
                text:'Daily Service Level'
            },
            tooltips: {
                mode: 'index',
                intersect: false,
            },
            hover: {
                mode: 'nearest',
                intersect: true
            },
            scales: {
                xAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Hour'
                    }
                }],
                yAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Service Level'
                    }
                }]
            }
        }
    };


    if (skill_services != "undefined" && skill_services != null && skill_services != "")
    {
        var ctx = document.getElementById("daily-service-level-chart").getContext("2d");
        window.myLine = new Chart(ctx, config);
    }
    else {
        $("#abandoned-calls-chart").remove();
        $(".chart-wrapper").html("<p class='text-center text-danger'>No data found for chart</p>");
    }

    $(document).ready(function () {
        setChartDateFormat('<?php echo $report_date_format;?>', '<?php echo $request->getRequest('sdate');?>', '<?php echo $request->getRequest('edate');?>');
    });

</script>


