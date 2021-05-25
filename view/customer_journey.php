
<link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.css">
<script src="assets/plugins/bootstrap/js/bootstrap.js"></script>
<link rel="stylesheet" href="assets/fonts/font-awesome/css/font-awesome.css">
<link rel="stylesheet" href="assets/css/customer_journey.css">

<link href="js/datetimepicker/jquery.datetimepicker.css" rel="stylesheet" media="screen">
<script src="js/datetimepicker/jquery.datetimepicker.js" type="text/javascript"></script>

<script src="assets/plugins/leader-line/leader-line.min.js"></script>

<?php
    $journey_element_ids = [];
    if (count($journey_data) > 0) {
?>

<div class="col-md-12 mt-5">
    <div class="col-md-2">

        <?php foreach ($all_module as $key => $_mod){ ?>
            <div class="col-md-12"><div class="<?php echo strtolower($key);?> mod-div"><b class="mod-name"><?php echo $_mod?></b> <div class="pull-right mod-icon"><?php echo $module_icon[$key]?></div></div></div>
        <?php } ?>
    </div>
    <div class="col-md-10 mrl-2">
        <?php if (count($journey_data) > 0 && !empty($position_array)){
            $n = 0;
            ?>
            <?php
            $n=0;
            foreach ($all_module as $key => $_mod){ ?>
                <div class="col-md-12 data-row" style="<?php echo $n==0?'border-top-left-radius:10px; border-top-right-radius:10px;':''?><?php echo $n==count($module_presidency)-1?'border-bottom-left-radius:10px; border-bottom-right-radius:10px;':''?> ;padding: <?php echo in_array($_mod, $not_exist_mod_type) ? '18':'4'?>px; ">
                    <?php foreach ($journey_data as $value){ ?>
                        <?php if ($value->module_type == $key) { ?>
                            <div data-jid="<?php echo $value->journey_id?>" data-jtype="<?php echo $value->module_type?>" data-stype="<?php echo $value->module_sub_type?>" data-phone="<?php echo $phone_number?>" class=" br-<?php echo strtolower($value->module_type);?> data-icon"  id="pvr_<?php echo $value->journey_id?>"  style="margin-left: <?php echo $position_array[$value->journey_id] ?>px; ">
                                <?php echo $icon_array[$value->journey_id];?>
                                <div class="info well" id="inf_<?php echo $value->journey_id?>" style="display: none;"></div>
                                <sup class="sup-serial"><?php echo $serial_arr[$value->journey_id]?></sup>
                                <div class="l-time"><?php $log_time = strtotime($value->log_time);  echo $today==date("Y-m-d", $log_time) ? date(' H:i A, d M', $log_time) : date('d M', $log_time) ?></div>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
                <?php
                $n++;
            } ?>
        <?php } ?>
    </div>


    <?php if (!empty($last_dispositions)){?>
        <div class=" col-md-6 col-md-offset-3  mt-3 brdr-1 mb-5">
            <div class="col-md-12 mb-10"><label for="" class="semi-header">Latest Wrapup</label></div>
            <div class="col-md-12">
                <?php if ($last_dispositions) {?>
                    <div class="row brdr-1">
                        <div class="col-md-2 dis-mod"></div>
                        <div class="col-md-4 brdrl-1 br-1" style=""><b class="fnt-15">Disposition</b></div>
                        <div class="col-md-4 brdrl-1 br-1"><b class="fnt-15">Type</b></div>
                        <div class="col-md-2 brdrl-1 "><b class="fnt-15">ICE</b></div>
                    </div>
                    <?php foreach ($last_dispositions as $key => $val){ ?>
                        <?php $type_arr = explode("_",$key);?>
                        <?php if (!empty($val->title)) {?>
                            <div class="row brdr-1">
                                <div class="col-md-2 dis-mod <?php echo $type_arr[0]?>  <?php echo strtolower($type_arr[0]).'-sdo'?>">
                                    <?php  echo !empty($type_arr[0]) ? $all_module[strtoupper($type_arr[0])]:""?>
                                </div>
                                <div class="col-md-4 brdrl-1 br-1" style="">
                                    <div class="fnt-12"><?php echo !empty($val->title) ? $val->title  : ""?></div>
                                </div>
                                <div class="col-md-4 brdrl-1 br-1">
                                    <div class="fnt-12"><?php echo !empty($val->dis_type) ? $val->dis_type : "" ?></div>
                                </div>
                                <div class="col-md-2 brdrl-1 ">
                                    <div class="fnt-12"><?php echo !empty($val->ice) ? $val->ice : "" ?></div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    <?php } ?>


</div>
<?php } else {  ?>
<div class="col-md-12 mt-5">
    <div class="col-md-12">
        <b class="center rc fnt-16">No Data Found! For this user.</b>
    </div>
</div>
<?php } ?>


<script>
    let cache_data = [];
    let resp_data = "";
    let clicked_element = "";

    function getInfo(url, type, id, sub_type, phone_num) {
        resp_data = "";
        resp_data = $.ajax({
            dataType  : "JSON",
            type      : "POST",
            url       : url,
            data      : { type:type, id:id, sub_type:sub_type, phone_num:phone_num},
            success : function (res) {
                return res;
            }
        });
    }
    $(document).ready(function () {

        jQuery('#edate').datetimepicker({
            //timepicker:false,
            format:'d/m/Y',
            // minDate: last_his_data,
        });
        jQuery('#sdate').datetimepicker({
            timepicker:true,
            format:'d/m/Y H:i:s',
            // minDate: last_his_data,
            onSelectDate:function(ct,$i){
                jQuery('#edate').datetimepicker({
                    timepicker:true,
                    format:'d/m/Y H:i:s',
                    minDate: ct.dateFormat('Y/m/d'),
                });
            }
        });

        // jQuery('#edate').datetimepicker({
        //     timepicker:false,
        //     format:'d/m/Y',
        // });


        // $(".data-icon .out-call").hover(function () {
        //     console.log($(this));
        //     $(this).addClass('out-call-white');
        //     $(this).removeClass('out-call');
        // },function () {
        //     $(this).addClass('out-call');
        //     $(this).removeClass('out-call-white');
        // });


        // $(window).click(function() {
        //     $(".info").hide();
        // });

        $(".data-icon").hover(function () {
            event.stopPropagation();
            var jid = $(this).data('jid');
            var jtype = $(this).data('jtype');
            var phone_number = $(this).data('phone');
            var stype = $(this).data('stype');

            if ($("#inf_"+jid).is(':visible')){
                $(".info").hide();
            } else {
                $(".info").hide();
                $("#inf_"+jid).toggle(300);
            }

            if (typeof cache_data[jid] === 'undefined') {
                getInfo("<?php echo site_url().$this->url('task=customer-journey&act=get-info');?>", jtype, jid, stype, phone_number);
                resp_data.done(function( data ) {
                    cache_data[jid] = data;
                    $("#inf_"+jid).empty();
                    $("#inf_"+jid).append(data);
                });
            } else {
                $("#inf_"+jid).empty();
                $("#inf_"+jid).append(cache_data[jid]);
            }
        }
        , function () {
            $(".info").hide();
        }
        );
    });

    window.onload = function() {
        setTimeout(setJourneyPath(), 2000);
    };

    function setJourneyPath() {
        <?php foreach ($position_array as $key => $value){
            $journey_element_ids[] = "$key";
        } ?>

        let journey_element_ids = <?php echo !empty($journey_element_ids) ? json_encode($journey_element_ids) : "''" ?>;
        let element_count = 0;
        if (typeof journey_element_ids !== 'undefined' && journey_element_ids.length !==null && journey_element_ids.length > 0){
            $.each(journey_element_ids, function (index, value) {
                if (element_count > 0){
                    let start = document.getElementById("pvr_"+journey_element_ids[index-1]);
                    let end =   document.getElementById("pvr_"+value);
                    let start_label = element_count.toString();
                    if (typeof start !== "undefined" && start !== null){
                        new LeaderLine(start, end, {
                            size: 3,
                            //middleLabel: LeaderLine.captionLabel(start_label, {color: '#0000ff', fontSize: 15, fontWeight: 'bold'}),
                            path: 'straight',
                            startPlug: 'square',
                            endPlug: 'arrow1',
                            startPlugColor: '#b8bec4',
                            endPlugColor: '#606e7b',
                            gradient: true,
                            dash: {animation: true}
                        });
                    }
                }
                element_count++;
            });
        }
    }
</script>

