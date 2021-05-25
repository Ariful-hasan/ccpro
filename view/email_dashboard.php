<?php 
$startDate = strtotime("-1 day");
$startDate = date("Y-m-d", $startDate);
$endDate = date("Y-m-d");
$toDate = date(REPORT_DATE_FORMAT ,strtotime($endDate));
$fromDate = date(REPORT_DATE_FORMAT, strtotime('-30 days'));
?>

<form method="post" enctype="form-data" name="e_dashboard" id="e_dashboard">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<!--<div class="panel-heading">Search</div>-->
				<div class="panel-body">
					<div class="form-group">
						<label for="search" class="col-md-1 control-label">Source</label>
						<div class="col-md-4">
							<select class="form-control" name="source" id="source">
								<option value="">All</option>
								<?php if (!empty($source_option)){?>
									<?php foreach ($source_option as $key => $value){?>
										<option value="<?php echo $key?>" <?php echo $source==$key?"selected='selected'":""?>> <?php echo $value?> </option>
									<?php }?>
								<?php }?>
							</select>
						</div>

                        <div class="col-md-1">
                            <label> StartDate </label>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group date date-to gs-date-custom-picker-grid-options" >
                                <input autocomplete="off" class="gs-report-date-picker form-control" type="text" name="from_date" value="<?php echo !empty($request->getRequest('from_date')) ? $request->getRequest('from_date') : $fromDate; ?>" placeholder="From Date" />
                                <span class="input-group-addon" style="height: 24px !important; padding:2px 2px 0px 1px ; line-height: 4px !important;"><span style="font-size: 12px !important;" class="fa  fa-calendar "></span></span>
                            </div>
                        </div>

                        <div class="col-md-1">
                            <label> EndDate </label>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group date date-to gs-date-custom-picker-grid-options" >
                                <input autocomplete="off" class="gs-report-date-picker form-control" type="text" name="to_date" value="<?php echo !empty($request->getRequest('to_date')) ? $request->getRequest('to_date') : $toDate; ?>" placeholder="To Date" />
                                <span class="input-group-addon" style="height: 24px !important; padding:2px 2px 0px 1px ; line-height: 4px !important;"><span style="font-size: 12px !important;" class="fa  fa-calendar "></span></span>
                            </div>
                        </div>
                                                
						<div class="col-md-1">
							<button class="btn btn-warning" type="submit"><i class="fa fa-search"> Search</i></button>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
	<?php if (!empty($myjob)):?>
	<div class="box-container col-md-12">
		<h1 class="box-title">My Email</h1>
		<div class="emailbox new-ticket" id="newMyTicket">
			<div class="box-image"><i class="fa fa-star-o fa-5x"></i></div>
			<div class="box-body">
				<div class="statistics"><?php echo empty($myjob->num_news) ? 0 : $myjob->num_news;?></div>
				<div class="stat-title">New<br />Email</div>
			</div>
		</div>
		<div class="emailbox pending-ticket" id="penMyTicket">
			<div class="box-image"><i class="fa fa-tasks fa-5x"></i></div>
			<div class="box-body">
				<div class="statistics"><?php echo empty($myjob->num_pendings) ? 0 : $myjob->num_pendings;?></div>
				<div class="stat-title">Pending<br />Emails</div>
			</div>
		</div>
		<div class="emailbox pending-client" id="cliMyTicket">
			<div class="box-image"><i class="fa fa-users fa-5x"></i></div>
			<div class="box-body">
				<div class="statistics"><?php echo empty($myjob->num_client_pendings) ? 0 : $myjob->num_client_pendings;?></div>
				<div class="stat-title">Client<br />Pendings</div>
			</div>
		</div>
	</div>
	<?php endif;?>

	<?php if (!empty($recentjob)):?>
	<div class="box-container">
		<h1 class="box-title">Updates of Last 24 Hour</h1>
		<div class="emailbox new-ticket" id="todayNewTicket">
			<div class="box-image"><i class="fa fa-star-o fa-5x"></i></div>
			<div class="box-body">
				<div class="statistics"><?php echo empty($recentjob->num_news) ? 0 : $recentjob->num_news;?></div>
				<div class="stat-title">New<br />Emails</div>
			</div>
		</div>
		<div class="emailbox pending-ticket" id="todayPenTicket">
			<div class="box-image"><i class="fa fa-tasks fa-5x"></i></div>
			<div class="box-body">
				<div class="statistics"><?php echo empty($recentjob->num_pendings) ? 0 : $recentjob->num_pendings;?></div>
				<div class="stat-title">Pending<br />Emails</div>
			</div>
		</div>
		<div class="emailbox pending-client" id="todayCliTicket">
			<div class="box-image"><i class="fa fa-users fa-5x"></i></div>
			<div class="box-body">
				<div class="statistics"><?php echo empty($recentjob->num_client_pendings) ? 0 : $recentjob->num_client_pendings;?></div>
				<div class="stat-title">Client<br />Pendings</div>
			</div>
		</div>
		<div class="emailbox served-ticket" id="todaySerTicket">
			<div class="box-image"><i class="fa fa-check fa-5x"></i></div>
			<div class="box-body">
				<div class="statistics"><?php echo empty($recentjob->num_serves) ? 0 : $recentjob->num_serves;?></div>
				<div class="stat-title">Served<br />Emails</div>
			</div>
		</div>
	</div>
	<?php endif;?>

	<?php if (!empty($alljob)):?>
	<div class="box-container">
		<h1 class="box-title">All Emails</h1>
		<div class="emailbox new-ticket" id="allNewTicket">
			<div class="box-image"><i class="fa fa-star-o fa-5x"></i></div>
			<div class="box-body">
				<div class="statistics"><?php echo empty($alljob->num_news) ? 0 : $alljob->num_news;?></div>
				<div class="stat-title">New<br />Emails</div>
			</div>
		</div>
		<div class="emailbox pending-ticket" id="allPenTicket">
			<div class="box-image"><i class="fa fa-tasks fa-5x"></i></div>
			<div class="box-body">
				<div class="statistics"><?php echo empty($alljob->num_pendings) ? 0 : $alljob->num_pendings;?></div>
				<div class="stat-title">Pending<br />Emails</div>
			</div>
		</div>
		<div class="emailbox pending-client" id="allCliTicket">
			<div class="box-image"><i class="fa fa-users fa-5x"></i></div>
			<div class="box-body">
				<div class="statistics"><?php echo empty($alljob->num_client_pendings) ? 0 : $alljob->num_client_pendings;?></div>
				<div class="stat-title">Client<br />Pendings</div>
			</div>
		</div>
		<div class="emailbox served-ticket" id="allSerTicket">
			<div class="box-image"><i class="fa fa-check fa-5x"></i></div>
			<div class="box-body">
				<div class="statistics"><?php echo empty($alljob->num_serves) ? 0 : $alljob->num_serves;?></div>
				<div class="stat-title">Served<br />Emails</div>
			</div>
		</div>
	</div>
	<?php endif;?>
</form>

<script type="text/javascript">
	$(document).ready(function(){
		$("#newMyTicket").click(function(){
			window.location = "<?php echo $this->url("task=email&type=myjob&status=O&newall=Y");?>";		
		});
		$("#penMyTicket").click(function(){
			window.location = "<?php echo $this->url("task=email&type=myjob&status=P&newall=Y");?>";		
		});
		$("#cliMyTicket").click(function(){
			window.location = "<?php echo $this->url("task=email&type=myjob&status=C&newall=Y");?>";		
		});
		
		$("#todayNewTicket").click(function(){
			window.location = "<?php echo $this->url("task=email&status=O&tfield=update&newall=24&sdate=$startDate&edate=$endDate");?>";		
		});
		$("#todayPenTicket").click(function(){
			window.location = "<?php echo $this->url("task=email&status=P&tfield=update&newall=24&sdate=$startDate&edate=$endDate");?>";		
		});
		$("#todayCliTicket").click(function(){
			window.location = "<?php echo $this->url("task=email&status=C&tfield=update&newall=24&sdate=$startDate&edate=$endDate");?>";		
		});
		$("#todaySerTicket").click(function(){
			window.location = "<?php echo $this->url("task=email&status=S&tfield=update&newall=24&sdate=$startDate&edate=$endDate");?>";		
		});

		$("#allNewTicket").click(function(){
			window.location = "<?php echo $this->url("task=email&status=O&newall=Y");?>";		
		});
		$("#allPenTicket").click(function(){
			window.location = "<?php echo $this->url("task=email&status=P&newall=Y");?>";		
		});
		$("#allCliTicket").click(function(){
			window.location = "<?php echo $this->url("task=email&status=C&newall=Y");?>";		
		});
		$("#allSerTicket").click(function(){
			window.location = "<?php echo $this->url("task=email&status=S&newall=Y");?>";
		});
	});
</script>
