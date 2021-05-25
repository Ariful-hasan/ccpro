<style>
    /*.service-level-table thead tr{background-color: #f1f1f1;}
    .service-level-table td,.service-level-table th{min-width: 90px !important;text-align: center; padding: 5px !important;}
    .control-label{position: relative}
    .gs-check-box{width: 28px !important; height: 28px !important; }
    .check-box-title{position: relative; top: -10px;  vertical-align: middle; font-weight: 700}
    .table td.fit, .table th.fit {white-space: nowrap; width: auto; vertical-align: middle}
    .m-t-10{margin-top: 10px}*/
</style>
<?php
// var_dump($sdate);
// var_dump($edate);
?>

<div class="row">
    <div class="col-md-12 small">
        <form id="email-activity-form" action="<?php echo $this->url("task=report-new&act=daily-email-activity-report"); ?>" class="form-inline" method="post" autocomplete="off">
            <div class="form-group form-group-sm ">
                <label for="sdate" class="control-label">From: </label>
                <input type="text" name="sdate" id="sdate" class="form-control" value="<?php echo date($date_format, strtotime($sdate));  ?>">
            </div>
            <div class="form-group form-group-sm ">
                <label for="sdate" class="control-label">To: </label>
                <input type="text" name="edate" id="edate" class="form-control" value="<?php echo date($date_format, strtotime('+1 day', strtotime($edate))); ?>">
            </div> 
            <?php /*          
            <div class="form-group form-group-sm">
                <label for="skill_id" class="control-label">Skill:</label>
                <select name="skill_id" id="skill_id" class="form-control select2">
                    <?php foreach ($skills as $id => $skill): ?>
                        <option value="<?php echo $id; ?>" <?php echo $request->getRequest('skill_id','*') == $id ? 'selected' : '' ?>><?php echo $skill; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>       
            */ ?>                 
            <div class="form-group form-group-sm">
                <label for="agent_id" class="control-label">Agent:</label>
                <select name="agent_id" id="agent_id" class="form-control select2">
                    <?php foreach ($agents as $id => $agent): ?>
                        <option value="<?php echo $id; ?>" <?php echo $request->getRequest('agent_id','*') == $id ? 'selected' : '' ?>><?php echo $agent; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class=" btn btn-md btn-success "><i class=" fa fa-search"></i> Search </button>
            <a href="<?php echo $this->url("task=report-new&act=daily-email-activity-report&download=".md5("CC Daily Email Activity Report".session_id())) ?>" id="download-daily-email-activity-report" class=" btn btn-md btn-purple"><i class=" fa fa-download"></i> Download</a>
        </form>
        <br>
        <div class="table-responsive m-t-10">
            <table class="table table-bordered table-striped table-hover table-condensed service-level-table">
                <thead>
                	<tr>
                		<th>Date</th>
                		<th>Agent ID</th>
                        <th>Agent Name</th>
                		<?php
                		foreach ($email_activity_list as $key => $item) {
                		?>
                			<th><?php echo $item; ?></th>
                		<?php } ?>
                	<tr/>
                </thead>
                <tbody>
                	<?php 
                	$idx_date = $sdate;
                	while (true) {
	                	foreach($result_agent_list as $key=>$item){ 
	                ?>
	                	<tr>
	                		<td><?php echo date($date_format, strtotime($idx_date)); ?></td>
            				<td><?php echo $item->agent_id; ?></td>
                            <td><?php echo $item->agent_name; ?></td>
            				<?php 
            				foreach($email_activity_list as $idx=>$email_item){  
            					$idx_str = $idx_date.'_'.$item->agent_id.'_'.$idx;
            				?>
            					<td style="<?php if(isset($new_result[$idx_str]) && !empty($new_result[$idx_str])){ ?>background-color: rgba(102, 189, 120, 1.0); color: #fff; font-weight: bold; <?php } ?>text-align:center;" >
            						<?php echo isset($new_result[$idx_str]) ? $new_result[$idx_str] : 0; ?>
            					</td>
            				<?php } ?>
            			</tr>
                	<?php
                		}

                		$idx_date = date('Y-m-d', strtotime('+1 day', strtotime($idx_date)));
                		if($idx_date > $edate)
                			break;
                	}
                	?>
                </tbody>
            </table>
        </div>
    </div>

    </div>
</div>

<script type="text/javascript">
    var report_date_format = '<?php echo $report_date_format; ?>';    
    var report_restriction_days = parseInt(<?php echo empty($report_restriction_days) ? '' : $report_restriction_days; ?>);

    $(function(){
        prepareDownloadUrl();
        var current_date = new Date();
        var default_date = '';
        default_date = current_date.getDate()+'/0'+(+current_date.getMonth()+1)+'/'+current_date.getFullYear();

        $("#sdate").datetimepicker({
            defaultDate: new Date(),
            pickTime: false,
            timepicker:false,
            useStrict:true,
            format: report_date_format
        });
        $("#edate").datetimepicker({
            defaultDate: new Date(),
            pickTime: false,
            timepicker:false,
            useStrict:true,
            format: report_date_format
        });                

        $(document).on('change','#sdate',function () {
            prepareDownloadUrl();
        });
        $(document).on('change','#edate',function () {
            prepareDownloadUrl();
        });
        $(document).on('change','#skill_id',function () {
            prepareDownloadUrl();
        });
        $(document).on('change','#agent_id',function () {
            prepareDownloadUrl();
        });

        $(document).on('click','#download-daily-email-activity-report',function () {
            $("#MainLoader").remove();
        });

        
        $("#email-activity-form").on("submit", function (e) {
            try {
                if(!beforeFormSubmit(report_restriction_days)){
                    return false;
                }
            }catch(e){
                console.log(e.message);
            }
            // return false;
        });

        $("a#download-daily-email-activity-report").on("click", function (e) {
            try {
                if(!beforeFormSubmit(report_restriction_days)){
                    return false;
                }
            }catch(e){
                console.log(e.message);
            }
            // return false;
        });        
    });

    function prepareDownloadUrl(){
        $downloadButton = $("#download-daily-email-activity-report");
        var url = window.location.href;
        var date_format = report_date_format; //$("#date_format option:selected").val();
        var sdate = $("#sdate").val();
        var edate = $("#edate").val();
        var agent_id = $("#agent_id").val();
        var download = "<?php echo md5("CC Daily Email Activity Report".session_id()) ?>";

         url += '&date_format='+encodeURIComponent(date_format)+
             '&sdate='+encodeURIComponent(sdate)+ '&edate='+ encodeURIComponent(edate) +
             '&agent_id='+encodeURIComponent(agent_id)+'&download=' + download;
        $downloadButton.attr('href',url);
    }

    function beforeFormSubmit(day){
        var start_date = $('#sdate').val();
        var end_date = $('#edate').val();

        var start = start_date.split(' ');
        var start_date = start[0];
        var start_time = (typeof start[1] == 'undefined' ) ? '' : start[1];

        if ('d/m/Y' == report_date_format){
            var start_day = start_date.split('/')[0];
            var start_month = start_date.split('/')[1];
            var start_year = start_date.split('/')[2];
        }else if ('m/d/Y' == report_date_format){
            var start_day = start_date.split('/')[1];
            var start_month = start_date.split('/')[0];
            var start_year = start_date.split('/')[2];
        }

        var d = '';
        if(start_time){
            d = new Date(start_year, start_month -1 , start_day, start_time.split(':')[0], start_time.split(':')[1]);
        }else{
            d = new Date(start_year, start_month -1 , start_day);
        }
        // console.log(d);

        var end = end_date.split(' ');
        var end_date = end[0];
        var end_time = (typeof end[1] == 'undefined' ) ? '' : end[1];

        if ('d/m/Y' == report_date_format){
            var end_day = end_date.split('/')[0];
            var end_month = end_date.split('/')[1];
            var end_year = end_date.split('/')[2];
        }else if ('m/d/Y' == report_date_format){
            var end_day = end_date.split('/')[1];
            var end_month = end_date.split('/')[0];
            var end_year = end_date.split('/')[2];
        }

        var d2 = '';
        if(start_time){
            d2 = new Date(end_year, end_month -1 , end_day, end_time.split(':')[0], end_time.split(':')[1]);
        }else{
            d2 = new Date(end_year, end_month -1 , end_day);
        }
        // console.log(d2);
        // console.log(day);
        // console.log(1000*60*60*parseInt(day)*24);
        if ((d2.getTime() - d.getTime()) / (1000*60*60*day*24) > 1) {
            alert('Provide date range within '+day+' days');
            return false;
        }

        return true;
    }
</script>

