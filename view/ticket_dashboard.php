<?php
/**
 * Created by PhpStorm.
 * User: arif
 * Date: 12/6/2018
 * Time: 1:20 PM
 */
?>


<style>
    .box-container {
        text-align:center;
        margin-bottom: 30px;
    }
    .box-container .box-title {
        font-size:20px; font-weight:800;color:#2583AD; text-align:center; margin-bottom:10px;
    }
    .box-container .emailbox {
        width: 150px;
        display:inline-block;
        margin: 5px 15px 5px 0;
        border-collapse: collapse;
        border-radius: 6px;
        box-shadow: 0 0 3px #333333;
        cursor:pointer;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
    }
    .box-container .head {
        height: 27px;
        background: linear-gradient(to bottom, #4A98AF 44%, #328AA4 45%) repeat scroll 0 0 rgba(0, 0, 0, 0);
        color:#ffffff;
        vertical-align:middle;
        font-weight:400;
        font-size:20px;
        font-family:Geneva, Arial, Helvetica, sans-serif;
    }
    .box-container .statistics {
        padding: 5px;
        display: block;
        font-family: BebasNeueRegular;
        font-size: 2.2em;
        line-height: 40px;
        height: 40px;

    }

    .box-container .box-body {
        /*float:right;*/
    }
    .stat-title {
        padding: 2px 2px 2px 45px;
        font-size: 1.2em;
        font-family:Geneva, Arial, Helvetica, sans-serif;
        vertical-align:middle;
        font-weight:500;
        color: #777777;
        height: 50px;
        text-align: center;
    }
    .box-container .fa-5x {
        font-size: 5em;
    }
    .box-container .box-image {
        display:inline-block;
        float:left;
        padding: 32px 15px 24px 15px;
        font-size:5px;
    }
    .box-container .emailbadge {
        font-size: 12px;
        font-weight: normal;
        line-height: 35px;
        padding-bottom: 3px;
        padding-top: 1px;
        text-shadow: none;
        border-radius: 9px;
    }

    .box-container .label, .box-container .emailbadge {
        /*background-color: #999999;*/
        color: #FFFFFF;
        display: inline-block;
        font-size: 41.844px;
        font-weight: bold;
        line-height: 74px;
        padding: 2px 8px;
        text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
        vertical-align: baseline;
        white-space: nowrap;
        margin-bottom:2px;
    }

    .box-container .label-important, .box-container .badge-pending {
        background-color: #B94A48;
    }

    .box-container .pending-ticket {
        background-color:#F2DEDF;color:#B44B45;
        background: #f2dedf;
        background: -moz-linear-gradient(top,  #f2dedf 0%, #edb4b5 50%, #f2dedf 100%);
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f2dedf), color-stop(50%,#edb4b5), color-stop(100%,#f2dedf));
        background: -webkit-linear-gradient(top,  #f2dedf 0%,#edb4b5 50%,#f2dedf 100%);
        background: -o-linear-gradient(top,  #f2dedf 0%,#edb4b5 50%,#f2dedf 100%);
        background: -ms-linear-gradient(top,  #f2dedf 0%,#edb4b5 50%,#f2dedf 100%);
        background: linear-gradient(to bottom,  #f2dedf 0%,#edb4b5 50%,#f2dedf 100%);
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f2dedf', endColorstr='#f2dedf',GradientType=0 );
    }
    .box-container .pending-client {
        background-color:#FDF8E4;color:#C0985A;
        background: #fcfff4;
        background: -moz-linear-gradient(top,  #fcfff4 0%, #e9e9ce 50%, #fcfff4 100%);
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#fcfff4), color-stop(50%,#e9e9ce), color-stop(100%,#fcfff4));
        background: -webkit-linear-gradient(top,  #fcfff4 0%,#e9e9ce 50%,#fcfff4 100%);
        background: -o-linear-gradient(top,  #fcfff4 0%,#e9e9ce 50%,#fcfff4 100%);
        background: -ms-linear-gradient(top,  #fcfff4 0%,#e9e9ce 50%,#fcfff4 100%);
        background: linear-gradient(to bottom,  #fcfff4 0%,#e9e9ce 50%,#fcfff4 100%);
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fcfff4', endColorstr='#fcfff4',GradientType=0 );
    }
    .box-container .new-ticket {
        background-color:#DAECF8;color:#3987AD;
        background: #ebf1f6;
        background: -moz-linear-gradient(top,  #ebf1f6 0%, #abd3ee 50%, #d5ebfb 100%);
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ebf1f6), color-stop(50%,#abd3ee), color-stop(100%,#d5ebfb));
        background: -webkit-linear-gradient(top,  #ebf1f6 0%,#abd3ee 50%,#d5ebfb 100%);
        background: -o-linear-gradient(top,  #ebf1f6 0%,#abd3ee 50%,#d5ebfb 100%);
        background: -ms-linear-gradient(top,  #ebf1f6 0%,#abd3ee 50%,#d5ebfb 100%);
        background: linear-gradient(to bottom,  #ebf1f6 0%,#abd3ee 50%,#d5ebfb 100%);
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ebf1f6', endColorstr='#d5ebfb',GradientType=0 );
    }
    .box-container .served-ticket {
        background-color:#E0EFD8;color:#468848;
        background: #e0efd8;
        background: -moz-linear-gradient(top,  #e0efd8 0%, #b0d3af 50%, #e0efd8 100%);
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#e0efd8), color-stop(50%,#b0d3af), color-stop(100%,#e0efd8));
        background: -webkit-linear-gradient(top,  #e0efd8 0%,#b0d3af 50%,#e0efd8 100%);
        background: -o-linear-gradient(top,  #e0efd8 0%,#b0d3af 50%,#e0efd8 100%);
        background: -ms-linear-gradient(top,  #e0efd8 0%,#b0d3af 50%,#e0efd8 100%);
        background: linear-gradient(to bottom,  #e0efd8 0%,#b0d3af 50%,#e0efd8 100%);
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#e0efd8', endColorstr='#e0efd8',GradientType=0 );
    }
</style>
<?php
$startDate = strtotime("-1 day");
$startDate = date("Y-m-d", $startDate);
$endDate = date("Y-m-d");
?>
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



<form method="post" enctype="form-data" name="e_dashboard" id="e_dashboard">
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
                        <button class="btn btn-warning" type="submit"><i class="fa fa-search"> Search</i></button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php if (!empty($myjob)):?>
        <div class="box-container col-md-12">
            <h1 class="box-title">My Tickets</h1>
            <div class="emailbox new-ticket" id="newMyTicket">
                <div class="box-image"><i class="fa fa-star-o fa-5x"></i></div>
                <div class="box-body">
                    <div class="statistics"><?php echo empty($myjob->num_news) ? 0 : $myjob->num_news;?></div>
                    <div class="stat-title">New<br />Tickets</div>
                </div>
            </div>

            <div class="emailbox pending-ticket" id="penMyTicket">
                <div class="box-image"><i class="fa fa-tasks fa-5x"></i></div>
                <div class="box-body">
                    <div class="statistics"><?php echo empty($myjob->num_pendings) ? 0 : $myjob->num_pendings;?></div>
                    <div class="stat-title">Pending<br />Tickets</div>
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
                    <div class="stat-title">New<br />Tickets</div>
                </div>
            </div>

            <div class="emailbox pending-ticket" id="todayPenTicket">
                <div class="box-image"><i class="fa fa-tasks fa-5x"></i></div>
                <div class="box-body">
                    <div class="statistics"><?php echo empty($recentjob->num_pendings) ? 0 : $recentjob->num_pendings;?></div>
                    <div class="stat-title">Pending<br />Tickets</div>
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
                    <div class="stat-title">Served<br />Tickets</div>
                </div>
            </div>

        </div>
    <?php endif;?>

    <?php if (!empty($alljob)):?>
        <div class="box-container">
            <h1 class="box-title">All Tickets</h1>

            <div class="emailbox new-ticket" id="allNewTicket">
                <div class="box-image"><i class="fa fa-star-o fa-5x"></i></div>
                <div class="box-body">
                    <div class="statistics"><?php echo empty($alljob->num_news) ? 0 : $alljob->num_news;?></div>
                    <div class="stat-title">New<br />Tickets</div>
                </div>
            </div>

            <div class="emailbox pending-ticket" id="allPenTicket">
                <div class="box-image"><i class="fa fa-tasks fa-5x"></i></div>
                <div class="box-body">
                    <div class="statistics"><?php echo empty($alljob->num_pendings) ? 0 : $alljob->num_pendings;?></div>
                    <div class="stat-title">Pending<br />Tickets</div>
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
                    <div class="stat-title">Served<br />Tickets</div>
                </div>
            </div>

        </div>
    <?php endif;?>

</form>

