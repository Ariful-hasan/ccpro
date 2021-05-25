<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>gPlex Contact Centre Dashboard</title>

    <link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">
<!--    <link rel="stylesheet" href="assets/css/email_agent_dashboard.css">-->
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.js"></script>

    <!-- Include the plugin's CSS and JS: -->
    <script src="js/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
    <link rel="stylesheet" href="js/css/bootstrap-multiselect/css/bootstrap-multiselect.css" type="text/css"/>

    <?php
        $client_img = !empty($suffix) ? $suffix : "aa";
        $client_img .= "_client_logo.png";
    ?>

    <style>
        .pl-0 { padding-left: 0px!important; }
        .pr-0 { padding-right: 0px!important; }
        .center{ text-align: center!important; }
        .txt-22{font-size: 22px;}
        .bg-white{background-color: white}
        .bg-sky-blue{background-color: #63ace5}
        .bg-dark-blue{background-color: #005b96}
        .bg-navy-blue{background-color: #03396c}
        .bg-gray-blue{background-color: #e7eff6}
        .clr-white{color: white;}
        .clr-red{color: #ff1a1a;}
        .bb-1{border: 2px solid #adcbe3}
        .margin-top-17{margin-top: -17%}
        .mt-0{margin-top: 0%}
        .mt-10{margin-top: 10%}
        .mb-0{margin-bottom: 0%}
        .margin-top-8{margin-top: -8%}
        .font-bold{font-weight: bold}
        .font-bolder{font-weight: bolder}
    </style>
</head>
<body>

<div class="container-fluid" id="dashboard">
    <div class="col-md-12 pl-0 pr-0">
        <div class="col-md-2 pl-0"><a href="<?php echo base_url(); ?>"><img src="assets/images/logo.png"></a></div>
        <div class="col-md-2 col-md-offset-8"><a href="<?php echo base_url(); ?>"><img  src="assets/images/<?php echo $client_img?>" class="img-responsive img-circle pull-right" height="0" width="50%"></a></div>
    </div>
    <div class="col-md-12">
        <div class="row">

            <div class="col-lg-4 col-md-6">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="col-md-12">
                                <div class="col-md-10"><span class="txt-22"> <b>E</b>mail <b>I</b>n <b>S</b>ervice: </span></div>
                                <div class="col-md-2"><b id="total" class="font-bolder txt-22">0</b></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="col-md-12">
                                <div class="col-md-10"><span class="txt-22"><b>A</b>gent <b>I</b>n <b>S</b>ervice:</span></div>
                                <div class="col-md-2"><b id="total_agent" class="font-bolder txt-22">0</b></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="col-md-12">
                                <div class="col-md-8">
                                    <span class="txt-22">Maximum Pull Time</span>
                                </div>
                                <div class="col-md-4"><div class="bg-sky-blue center clr-white font-bold">MPT</div></div>
                            </div>
                            <div class="col-md-12">
                                <div class="col-md-8">
                                    <span class="txt-22">Average Pull Time</span>
                                </div>
                                <div class="col-md-4"><div class="bg-dark-blue center clr-white font-bold">APT</div></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-8 col-md-6">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading center txt-22 font-bolder">Skill Info</div>
                        <div class="panel-body">
                            <div class="row" id="skill"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading center txt-22 font-bolder">Agent Info</div>
            <div class="panel-body">
                <div class="row" id="agent"></div>
            </div>
        </div>
    </div>

</div>


<script>
    let socket = "ws://<?php echo $ip?>:<?php echo $port?>";
    let img = '<img src="assets/images/client.svg" alt="Agent" class="img-responsive" width="100%" height="100%">';
    let total = 0;
    let agentStore = [];
    let skillStore = [];
    let skill_list = '<?php echo !empty($skills) ? json_encode($skills) : ""?>';

    skill_list = JSON.parse(skill_list);
    websocket = new WebSocket(socket);
    websocket.onopen = function(event) {};
    websocket.onmessage = function(event) {
        var res = JSON.parse(event.data);
        processData(res);
    };
    websocket.onerror = function(event){
        console.log(event);
    };
    websocket.onclose = function(event){
        console.log(event);
    };

    let createDiv = (elem_id, div_id, name, value, icon="", store, timeObj="") => {

        let div = "";
        div = icon.length > 0 ? '<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12 panel-default" id="'+div_id+'">' : '<div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 panel-default" id="'+div_id+'">';
        //let div = '<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12 panel-default" id="'+div_id+'">';
        div += '<div class="panel-body pl-0 pr-0">';
        div += '<div class="col-xs-12 bb-1 pr-0 pl-0 bg-gray-blue">';
        if (icon){
            div += '<div class="col-xs-5 pl-0">'+icon+'</div>';
            div += '<div class="col-xs-7 pr-0 pl-0">';
            div += '<div class="col-xs-12">';
            div += '<h4 class="agnt_name pull-right font-bold">'+name+'</h4>';
            div += '</div>';
            div += '<div class="col-xs-12"><h2 class="pull-right bold clr-red font-bolder mt-0 mb-0" id="'+div_id+'_total">'+value+'</h2></div>';
            div += '</div>';
        } else {
            div += '<div class="col-xs-12 pl-0 pr-0 center">';
            div += '<b class="agnt_name txt-22">'+name+'</b>';
            div += '</div>';
        }

        div += '</div>';
        if (icon.length == 0) {
            div += '<div class="col-xs-12 center bg-navy-blue"><h1 class="agnt_total clr-white" id="'+div_id+'_total">'+value+'</h1></div>';
        }
        if (timeObj){
            div += '<div class="col-xs-12 bt-1 pl-0 pr-0">';
            div += '<div class="col-xs-12 center bg-sky-blue clr-white"><div class="col-xs-12 center"><h5 class="">MPT</h5></div><div class="col-xs-12 center margin-top-8"><h4 class="font-bold" id="'+div_id+'_mpt">'+timeObj.mpt+'</h4></div></div>';
            div += '<div class="col-xs-12 center bg-dark-blue clr-white"><div class="col-xs-12 center"><h5 class="">APT</h5></div><div class="col-xs-12 center margin-top-8"><h4 class="font-bold" id="'+div_id+'_apt">'+timeObj.apt+'</h4></div></div>';
            div += '</div>';
        }
        div += '</div>';
        div += '</div>';
        $(elem_id).append(div);
        store.push(div_id);
    };

    let getTimeFormat = (seconds) => {
        let date = new Date(null);
        date.setSeconds(seconds);
        return date.toISOString().substr(11, 8);
    };
    let getMaxPullTime = (pullData) => {
        let temp = 0;
        let avg = 0;
        if (typeof pullData !== 'undefined'){
            for (let key in pullData){
                if (pullData[key] > temp){
                    temp = pullData[key];
                }
                avg = avg + pullData[key];
            }

            avg = avg/(Object.keys(pullData).length);
            temp = getTimeFormat(temp);
            avg = getTimeFormat(avg);
            console.log(avg);
        }
        return {"mpt":temp, "apt":avg};
    };

    let calculateAgent = (data, elem_id, pullData) => {
        let exists_agent = [];
        $.each(data, function (name, value) {
            if (name != "message" && name != "message_type"){
                let PullTimeResult = getMaxPullTime(pullData[name]);
                if ($("#"+name).length == 0){
                    createDiv(elem_id, name, name, value, img, agentStore, PullTimeResult);
                }else {
                    if ($("#"+name+"_total").html() != value){
                        $("#"+name+"_total").html(value);
                    }

                    if ($("#"+name+"_mpt").html() != PullTimeResult.mpt){
                        $("#"+name+"_mpt").html(PullTimeResult.mpt);
                    }
                    if ($("#"+name+"_apt").html() != PullTimeResult.apt){
                        $("#"+name+"_apt").html(PullTimeResult.apt);
                    }
                }
                total += value;
                if (exists_agent.includes(name) === false){
                    exists_agent.push(name);
                }
            }
        });
        if (!data.message && !data.message_type){
            deleteDiv(agentStore, exists_agent);
            $("#total").html(total);
            $("#total_agent").html(exists_agent.length);
        }
    };

    let calculateSkill = (data, elem_id) => {
        let exists_skill = [];
        $.each(data, function (name, value) {
            if (typeof name!=='undefined' && name != "message" && name != "message_type"){
                if ($("#"+name).length == 0){
                    createDiv(elem_id, name, skill_list[name], value, "", skillStore);
                }else {
                    if ($("#"+name+"_total").html() != value){
                        $("#"+name+"_total").html(value);
                    }
                }
                total += value;
                if (exists_skill.includes(name) === false){
                    exists_skill.push(name);
                }
            }
        });

        if (!data.message && !data.message_type){
            deleteDiv(skillStore, exists_skill);
        }
    };

    let processData = (res) => {
        if (typeof res!=='undefined' && res!=null && res!=''){
            total = 0;
            let exists_agent = [];
            let exists_skill = [];
            if (typeof res.AGENT !== 'undefined') {
                calculateAgent(res.AGENT, "#agent", res.PT);
            }
            if (typeof res.SKILL !== 'undefined'){
                calculateSkill(res.SKILL, "#skill");
            }
        } else {
            if ($("#agent").children().length > 0){
                $("#agent").empty();
                $("#total").html("0");
                $("#total_agent").html("0");
            }
            if ($("#skill").children().length > 0){
                $("#skill").empty();
            }
        }
    };



    let deleteDiv = (store, exists_data) => {
        for (let i = 0; i < store.length; i++) {
            if (exists_data.includes(store[i]) === false){
                $("#"+store[i]).remove();
            }
        }
    };

</script>


</body>
</html>