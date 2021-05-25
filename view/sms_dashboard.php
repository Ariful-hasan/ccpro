<link rel="stylesheet" href="assets/css/AdminLTE.min.css" type="text/css"/>
<style>
    .icon-mt{
        margin-top: 16% !important;
    }
    .icon-sz{
        font-size: 60% !important;
    }

    .bg-aqua {
        background-color: #00c0ef !important;
    }
    .bg-green {
        background-color: #39CCCC !important;
    }
    .bg-yellow {
        background-color: #ffd199 !important;
    }
    .bg-red {
        background-color:#ff7f7f !important;
    }
    .ml-2{
        margin-left: 16px;
    }
    h4{
        font-size: 22px;
    }
</style>
<form method="post" enctype="form-data" name="e_dashboard" id="e_dashboard">

    <section class="content">

        <?php if (true):?>
            <div class="row">
                <div class=" col-lg-9">
                    <h4 class="box-title ml-2 ">Updates of Last 24 Hour</h4>
                    <div class=" col-lg-3 col-md-3 col-xs-6" id="todayNewTicket">
                        <div class="small-box bg-aqua">
                            <div class="inner">
                                <h3><?php echo empty($last_day_data->total_sms) ? 0 : $last_day_data->total_sms;?></h3>
                                <p>Total SMS</p>
                            </div>
                            <div class="icon icon-mt"><i class="fa fa-star icon-sz"></i></div>
                            <a href="<?php echo $this->url("task=smslogreport");?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6" id="todayPenTicket">
                        <div class="small-box bg-red">
                            <div class="inner">
                                <h3><?php echo empty($last_day_data->pending_sms) ? 0 : $last_day_data->pending_sms;?></h3>
                                <p>Pending SMS</p>
                            </div>
                            <div class="icon icon-mt"><i class="fa fa-bars icon-sz"></i></div>
                            <a href="<?php echo $this->url("task=smslogreport");?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6" id="todaySerTicket">
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3><?php echo empty($last_day_data->served_sms) ? 0 : $recentjob->served_sms;?></h3>
                                <p>Served SMS</p>
                            </div>
                            <div class="icon icon-mt" ><i class="fa fa-check icon-sz"></i></div>
                            <a href="<?php echo $this->url("task=smslogreport");?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif;?>

        <?php if (true):?>
            <div class="row">
                <div class=" col-lg-9">
                    <h4 class="box-title ml-2 ">All SMS</h4>
                    <div class=" col-lg-3 col-md-3 col-xs-6 " id="allNewTicket">
                        <div class="small-box bg-aqua">
                            <div class="inner">
                                <h3><?php echo empty($all_data->total_sms) ? 0 : $all_data->total_sms;?></h3>
                                <p>Total SMS</p>
                            </div>
                            <div class="icon icon-mt"><i class="fa fa-star icon-sz"></i></div>
                            <a href="<?php echo $this->url("task=smslogreport");?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-xs-6" id="allPenTicket">
                        <div class="small-box bg-red">
                            <div class="inner">
                                <h3><?php echo empty($all_data->pending_sms) ? 0 : $all_data->pending_sms;?></h3>
                                <p>Pending SMS</p>
                            </div>
                            <div class="icon icon-mt"><i class="fa fa-bars icon-sz"></i></div>
                            <a href="<?php echo $this->url("task=smslogreport");?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6" id="allSerTicket">
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3><?php echo empty($all_data->served_sms) ? 0 : $all_data->served_sms;?></h3>
                                <p>Served SMS</p>
                            </div>
                            <div class="icon icon-mt" ><i class="fa fa-check icon-sz"></i></div>
                            <a href="<?php echo $this->url("task=smslogreport");?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
        <?php endif;?>
    </section>

</form>

<script type="text/javascript">
    $(document).ready(function(){
        $("#newMyTicket").click(function(){
            window.location = "<?php echo $this->url("task=smslogreport"); ?>";
        });
        $("#penMyTicket").click(function(){
            window.location = "<?php echo $this->url("task=smslogreport"); ?>";
        });
        $("#cliMyTicket").click(function(){
            window.location = "<?php echo $this->url("task=smslogreport"); ?>";
        });

        $("#todayNewTicket").click(function(){
            window.location = "<?php echo $this->url("task=smslogreport"); ?>";
        });
        $("#todayPenTicket").click(function(){
            window.location = "<?php echo $this->url("task=smslogreport");?>";
        });
        $("#todayCliTicket").click(function(){
            window.location = "<?php echo $this->url("task=smslogreport");?>";
        });
        $("#todaySerTicket").click(function(){
            window.location = "<?php echo $this->url("task=smslogreport");?>";
        });

        $("#allNewTicket").click(function(){
            window.location = "<?php echo $this->url("task=smslogreport");?>";
        });
        $("#allPenTicket").click(function(){
            window.location = "<?php echo $this->url("task=smslogreport");?>";
        });
        $("#allCliTicket").click(function(){
            window.location = "<?php echo $this->url("task=smslogreport");?>";
        });
        $("#allSerTicket").click(function(){
            window.location = "<?php echo $this->url("task=smslogreport");?>";
        });
    });
</script>
