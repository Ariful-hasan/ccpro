<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.min.js"></script>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Search Area</div>
            <div class="panel-body">
                <form action="<?php echo $this->url("task=report-new&act=daily-skill-summery"); ?>" class="form-horizontal" method="post">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="sdate" class="control-label col-md-4">Start Date</label>
                            <div class="col-md-8">
                                <input type="text" name="sdate" class="gs-date-picker form-control" value="<?php echo $request->getRequest('sdate') ? $request->getRequest('sdate') : date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="skill_id" class="control-label col-md-4">Skill</label>
                            <div class="col-md-8">
                                <select name="skill_id" id="skill_id" class="form-control">
                                    <option value="*">All</option>
                                    <?php if(!empty($skills)): foreach ($skills as $skill_id => $skill_title): ?>
                                        <option value="<?php echo $skill_id; ?>" <?php echo $skill_id == $request->getRequest('skill_id') ? 'selected' : ''; ?> > <?php echo $skill_title; ?></option>
                                    <?php endforeach; endif;?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit btn btn-success"><i class=" fa fa-search"></i> Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <br>
    <div class="col-md-12">
        <div class="col-md-12 chart-wrapper">
            <canvas id="daily-call-summery"></canvas>
        </div>
    </div>
</div>

<script>
    var skills = <?php echo json_encode($skills); ?>;
    skills = JSON.stringify(skills);
    skills = JSON.parse(skills);

    var chart_data = <?php echo $data; ?>;

    var labels = [];
    var agents_worked = [];
    var calls_answered = [];
    var calls_abandoned = [];
    if (chart_data != "undefined" && chart_data != null)
    {
        $.each(chart_data, function(index,skill_info) {
            sid = skill_info.skill_id;
            if (skills[sid])
            {
                labels.push(skills[sid]);
            }else {
                labels.push(sid);
            }
            agents_worked.push(skill_info.agents_worked)
            calls_answered.push(skill_info.calls_answerd)
            calls_abandoned.push(skill_info.calls_abandoned)
        });
    }else {
        $("#daily-call-summery").remove();
        $(".chart-wrapper").html("<p class='text-center text-danger'>No data found for chart</p>");
    }



    var agents = {
        label: 'Agents Worked',
        data: agents_worked,
        backgroundColor: '#028487',
        borderWidth: 1,
    };
    var answered = {
        label: 'Call Answered',
        data: calls_answered,
        backgroundColor: '#2f6b3c',
        borderWidth: 1,
    };
    var abandoned = {
        label: 'Call Abandoned',
        data: calls_abandoned,
        backgroundColor: '#aa0238',
        borderWidth: 0,
    };
    var daily_call_summery = {
        labels: labels,
        datasets: [agents, answered, abandoned]
    };

    var ctx = document.getElementById("daily-call-summery").getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        labels: ['Agents Worked', 'Call Answered', 'Call Abandoned'],
        data: daily_call_summery,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                xAxes: [{
                    ticks: {
                        beginAtZero:true
                    }
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero:true
                    }
                }]
            }
        }
    });
</script>

<script type="text/javascript">
    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
    });
</script>

