<?php
$startDate = strtotime("-1 day");
$startDate = date("Y-m-d H:i", $startDate);
$endDate = date("Y-m-d H:i");
$toDate = date(REPORT_DATE_FORMAT ,strtotime($endDate));
$fromDate = date(REPORT_DATE_FORMAT, strtotime('-30 days'));

//GPrint($startDate);
//GPrint($endDate);die;
?>
<link rel="stylesheet" href="assets/css/AdminLTE.min.css" type="text/css"/>
<style>
    .icon-mt{
        margin-top: 16% !important;
    }
    .icon-sz{
        font-size: 40% !important;
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
            <!--NEW   START-->
            <div class="row">
                <div class=" col-lg-9">

                    <div class=" col-lg-3 col-md-3 col-xs-6" >
                        <div class="small-box bg-aqua">
                            <div class="icon icon-mt"><i class="fa fa-file icon-sz"></i></div>
                            <div class="inner">
                                <p>VIVR Pages</p>
                            </div>
                            <a href="<?php echo $this->url("task=vivr-dashboard&act=vivr-pages");?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    <div class=" col-lg-3 col-md-3 col-xs-6" >
                        <div class="small-box bg-green">
                            <div class="icon icon-mt"><i class="fa fa-sitemap icon-sz"></i></div>
                            <div class="inner">
                                <p>VIVR Page Elements</p>
                            </div>
                            <a href="<?php echo $this->url("task=vivr-dashboard&act=vivr-page-elements");?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                </div>
            </div>
    </section>

</form>

<script type="text/javascript">
    $(document).ready(function(){

    });
</script>
