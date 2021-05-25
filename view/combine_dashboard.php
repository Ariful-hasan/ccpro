<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>gPlex Web Wallboard</title>

    <link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">
    <script src="assets/js/vue.min.js"></script>
    <script src="js/d3.js"></script>
    <script src="js/thermometer.js"></script>

    <style>
        [v-cloak] {display: none;}
        th:hover{cursor: pointer}
        img {vertical-align: middle;}

        .header-summary{display: block; min-height: 30px;margin-left: -15px}
        .grid{border: 1px solid #fff; font-size: 20px;
            border-radius: 10px; text-align: center;
            background: whitesmoke;color: #000;}
        .real-time-data{font-size: 40px}
        .title{text-align: center; font-size: 20px; background: #e82433;
            color: #fff;border-radius: 10px; margin-top: 10px}
        .grid > h2{padding: 0; margin: 0}
        div{padding:0; margin: 0}
        .legend{font-size: 20px; font-weight: bold; color: #5B3256; font-family: "Helvetica Neue", Helvetica, Arial, sans-serif }
        h3{color: #e82433; font-size: 200%; font-weight: bolder}
        .wait-time{
            text-align: left;
            font-size: 200%;
            text-shadow: 2px 2px 2px #ccc;
            color: #5B3256;
            margin-top: 0;
            padding-bottom: 0;
        }
        .wait-time-text{
            position: absolute;
            bottom: 0;
            font-size: 20px;
            padding-bottom: 10px;
            font-family:'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
        }

    </style>

</head>
<body>
<div class="container-fluid" id="dashboard">
  <div class="header-summary row">
        <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2"><a href="<?php echo base_url(); ?>"><img src="assets/images/logo.png"></a></div>
        <div class="col-xs-8 col-sm-9 col-md-9 col-lg-9"><h3 class="text-center">Wallboard</h3></div>
        <div class="col-xs-4 col-sm-1 col-md-1 col-lg-1">
            <button type="button" id="LogoutBtn" class="btn btn-md pull-right" title="Logout" style="margin-top: 10px">
                <img  src="image/logout.png" alt="Logout" >
            </button>
        </div>
  </div>

    <div class="row" id="dashboard-777-and-786">
        <div class="col-md-6">
            <div class="title">Dashboard 777</div>
            <div class="col-sm-6 col-md-6 col-lg-6 grid">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    Agents Logged In
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak>
                    {{ agents_logged_in_777 }}
                </div>
            </div>
            <div class="col-sm-6 col-md-6 col-lg-6 grid">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    Calls in Waiting
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak>
                    {{ calls_in_queue_777 }}
                </div>
            </div>

            <div class="col-sm-6 col-md-6 col-lg-6 grid">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    Not Ready Agents
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak>
                    {{ agents_in_break_777 }}
                </div>
            </div>
            <div class="col-sm-6 col-md-6 col-lg-6 grid">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    Idle/ACW Agents
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak>
                    {{ agents_in_idle_777 }}
                </div>
            </div>

            <div class="col-sm-6 col-md-6 col-lg-6 grid">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    Avg. Handling Time
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak>
                    {{ average_handling_time_777 }}
                </div>
            </div>
            <div class="col-sm-6 col-md-6 col-lg-6 grid">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    Service Level
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak>
                    {{ service_level_777 }}
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="title">Dashboard 786</div>
            <div class="col-sm-6 col-md-6 col-lg-6 grid">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    Agents Logged In
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak>
                    {{ agents_logged_in_786 }}
                </div>
            </div>
            <div class="col-sm-6 col-md-6 col-lg-6 grid">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    Calls in Waiting
                </div>
              <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak>
                  {{ calls_in_queue_786 }}
                </div>
            </div>

            <div class="col-sm-6 col-md-6 col-lg-6 grid">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    Not Ready Agents
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak>
                    {{ agents_in_break_786 }}
                </div>
            </div>
            <div class="col-sm-6 col-md-6 col-lg-6 grid">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    Idle/ACW Agents
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak>
                    {{ agents_in_idle_786 }}
                </div>
            </div>

            <div class="col-sm-6 col-md-6 col-lg-6 grid">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    Avg. Handling Time
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak>
                    {{ average_handling_time_786 }}
                </div>
            </div>
            <div class="col-sm-6 col-md-6 col-lg-6 grid">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    Service Level
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak>
                    {{ service_level_786 }}
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="daily-snap-shot">
        <div class="col-md-12" id="snap-shot-header-title">
            <div class="title">Daily Snap Shot</div>
        </div>

        <div class="col-md-12">
            <div class="col-sm-4 col-md-4 col-lg-4 grid " >
                <canvas id="daily-snap-shot-service-level"></canvas>
            </div>

            <div class="col-sm-4 col-md-4 col-lg-4 grid ">
                <canvas id="daily-snap-shot-abandoned-ratio"></canvas>
            </div>

            <div class="col-sm-4 col-md-4 col-lg-4 grid">
                <div class="col-sm-12 col-md-12">
                    <div class="col-sm-6 col-md-6">
                        <img id="img-awt" src="assets/images/icon-timer.png" alt="Average Wait Time">
                    </div>
                    <div class="col-sm-6 col-md-6">
                        <div class="wait-time">{{ daily_snap_shot_average_wait_time }} <span class="wait-time-text">Sec</span></div>

                    </div>
                </div>
                <div class="col-sm-12 col-md-12 text-center legend">Average Wait Time</div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="col-sm-4 col-md-4 col-lg-4 grid">
              <!--  <canvas id="daily-snap-shot-average-handling-time"></canvas>-->
                <div class="col-sm-12 col-md-12">
                    <div class="col-sm-6 col-md-6">
                        <img id="img-aht" src="assets/images/callcentre.png?v=1" alt="Average handling Time">
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="wait-time">{{ daily_snap_shot_average_handling_time}}<span class="wait-time-text">Sec</span></div>
                    </div>
                    <div class="col-sm-12 col-md-12 text-center legend">Average Handling Time</div>
                </div>
            </div>

            <div class="col-sm-4 col-md-4 col-lg-4 grid">
               <!--  <canvas id="daily-snap-shot-work-code-save-percent"></canvas>-->
                <div class="col-sm-12 col-md-12">
                    <div class="col-md-6 col-sm-6">
                        <div id="daily-snap-shot-work-code-save-percent"></div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="wait-time">{{ daily_snap_shot_work_code_save_percent}}<span class="wait-time-text">%</span></div>
                    </div>


                </div>
                <div class="col-sm-12 col-md-12 text-center legend">Work Code Saved</div>
            </div>

          <div class="col-sm-4 col-md-4col-lg-4 grid">
              <img id='logo' src="assets/images/airtel.svg" width="50%">
          </div>
        </div>
    </div>
</div>

<?php
    include('conf.extras.php');
    $wss = 'ws';
    $ws_port = $account_info->ws_port + 1;

    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
        $wss = 'wss';
        $ws_port = $account_info->ws_port;
    }

    $token = UserAuth::getOCXToken();

    if(empty($token)) {
        AddModel('MAgent');
        $user = UserAuth::getCurrentUser();
        $agent_model = new MAgent();
        $token=$agent_model->GenerateToken($user);
    }

?>
<script>

    var num_db_logged_in = "<?php echo UserAuth::numDBLoggedIn();?>";
    var current_user_id = "<?php echo UserAuth::getCurrentUser();?>";
    var wsUri = "ws://<?php echo $CCSettings->switch_ip.":".$ws_port."/chat" ?>";
    var token = "<?php echo $token;?>";
    var curSuffix = "<?php echo UserAuth::getDBSuffix();?>";
    var skills = <?php echo !empty($skills) ? json_encode($skills) : json_encode([])?>;
    var active_seats = <?php echo !empty($active_seats) ? json_encode($active_seats) : json_encode([]) ?>;
    var aux_messages = <?php echo !empty($aux_messages) ? json_encode($aux_messages) : json_encode([])?>;
    var aux_only_messages = [];

    var agents = <?php echo !empty($agents) ? json_encode($agents) : json_encode(array()); ?>;

    var skill777 = ['AG', 'AQ', 'AU'];
    var skill786 = ['AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AH','AK', 'AL', 'AM','AN','AO', 'AP', 'AR', 'AS'];

    var dashboard_user_logout_url = "<?php echo $this->url("task=agent&act=add-dashboard-logged-out");?>";
    aux_messages.forEach(function (aux) {
        aux_only_messages.push(aux.message);
    });

    var service_level_calculation_method = "<?php echo !empty($extra->sl_method) ? $extra->sl_method : 'A'; ?>";

</script>
<script src="assets/js/lodash.min.js"></script>
<script src="ccd/jquery.min.js"></script>
<script src="ccd/axios.min.js"></script>
<script src="ccd/wss.gPlexCCDashboardWS.js"></script>


<script>
    function is777Agent(skill_list = []){
        var is777skill = skill_list.filter(function (s) {
            return skill777.indexOf(s) !== -1;
        });

        return is777skill.length > 0;
    }

    function is786Agent(skill_list = []){
        var is786skill = skill_list.filter(function (s) {
            return skill786.indexOf(s) !== -1;
        });

        return is786skill.length > 0;
    }

    function getClientTime(offset)
    {
        var i = parseInt(Date.now());
        return i - (offset * 1000);
    }

    function NatPing()
    {
        obj.Ping();
        natPingTimes++;
        natPingTimeout = setTimeout(NatPing, 20000);
    }


    var  _DashBoard =  {
        Login: function() { obj.Login(dashboarduser); },
        Close: function(ts) {
            obj.Close();
            window.close();
        },


        PopulateSkill: function(skill_id, skill) {

            if(skill777.includes(skill_id)){

                var calls_in_queue = skill.ciq_call_count_0 +  skill.ciq_call_count_1;
                Vue.set(app.skills[skill_id], 'calls_in_queue', calls_in_queue);

                // Find out total Calls in Queue...
                var total_calls_in_queue =  app.sortedSkills.reduce( function(a, b){
                    var calls_in_queue = 0;
                    if (skill777.includes(skill_id) && b.hasOwnProperty('calls_in_queue')){
                        calls_in_queue = b['calls_in_queue'];
                    }
                    return a + calls_in_queue;
                }, 0);

                Vue.set(app, 'calls_in_queue_777', 0);
                Vue.set(app, 'calls_in_queue_777', total_calls_in_queue);
            }
        },

        PopulateSeat: function(seatId, seat) {
            console.log("Populating Seat: "+ seatId);
            var status = seat.status === "B" ? app.aux_messages[seat.aux_code].message : app.seat_status[seat.status];

            Vue.set(app.seats, seat.seat_id, _.assign({}, seat, app.agents[seat.agent_id]));
            Vue.set(app.seats[seat.seat_id], 'status', status);

            /* Find out currently logged In, idle and agents in break number for 777 category....*/
            Vue.set(app, 'agents_logged_in_777',0);
            Vue.set(app, 'agents_in_break_777',0);
            Vue.set(app, 'agents_in_idle_777',0);


            app.loggedAgents_777.forEach (function(a, b){

                Vue.set(app, 'agents_logged_in_777', app.agents_logged_in_777 + 1);

                if (a.status === 'Idle'){
                    Vue.set(app, 'agents_in_idle_777', app.agents_in_idle_777 + 1);
                }

                if (app.aux_only_messages.includes(a.status)){
                    Vue.set(app, 'agents_in_break_777', app.agents_in_break_777 + 1);
                }

            });


            /* Find out currently logged In, idle and agents in break number for 786 category....*/
            Vue.set(app, 'agents_logged_in_786',0);
            Vue.set(app, 'agents_in_break_786',0);
            Vue.set(app, 'agents_in_idle_786',0);


            app.loggedAgents_786.forEach (function(a, b){

                Vue.set(app, 'agents_logged_in_786', app.agents_logged_in_786 + 1);

                if (a.status === 'Idle'){
                    Vue.set(app, 'agents_in_idle_786', app.agents_in_idle_786 + 1);
                }

                if (app.aux_only_messages.includes(a.status)){
                    Vue.set(app, 'agents_in_break_786', app.agents_in_break_786 + 1);
                }

            });
        }

    };


    $(document).ready(function(){

        var remaining_height = $(window).outerHeight() - ($(".header-summary").outerHeight() + $("#dashboard-777-and-786").outerHeight());
        var graph_height = Math.floor((remaining_height - $("#snap-shot-header-title").outerHeight()) /2);
        $('canvas').attr('height',graph_height);
        $('#daily-snap-shot img').css('height',graph_height);
        $('#img-awt, #img-aht').css('height',graph_height - 28);
        $('#daily-snap-shot .grid').css({padding:0, margin:0});

        $(window).resize(function () {
            // Get the current height of the div and save it as a variable.

        });


        // Set the font-size and line-height of the text within the div according to the current height.
        $('.wait-time').css({
            'font-size': ((graph_height -28 )/2) + 'px',
            'line-height': graph_height -28 + 'px'
        });



        var dashboarduser = current_user_id;
        natPingTimes = 0;
        var maxRefreshTimer = 60*60;
        var maxWebPingTime = 4*60;
        var curWebPingTime = maxWebPingTime;
        var refreshBy = '';
        var natPingTimeout = null;
        var curTs = Date.now();
        refreshTimer = 0;
        netErrTry = 0;

        try {
            obj = new gPlexCCDashboardWSApi();
            obj.loadSettings(wsUri, dashboarduser, token, curSuffix);
            obj.setNumLoggedIn(num_db_logged_in);
            obj.Connect();
        } catch (ex) {
            alert("Warning1: " + ex.message);
        }

        refreshTimer++;
        if(refreshTimer >= maxRefreshTimer) {
            refreshTimer=0;
            if (obj.isRetryNeeded()) {
                refreshBy = 'SYSTEM';
                window.location.reload();
            }
            return;
        }
        if(refreshTimer >= curWebPingTime) {
            curWebPingTime = curWebPingTime + maxWebPingTime;
            $.ajax({
                type: "POST",
                url: "web_ping.php"
            });
        }

        if (netErrTry > 0) {
            $("#overlay").find("span").html(GetNetRetryMsg(curTs, netErrRetryTime));
            if (curTs > netErrRetryTime) {
                try {
                    var _cws = new WebSocket(wsUri);
                    _cws.onopen = function () {
                        window.location.reload();
                    };
                    _cws.onerror = function (error) {
                        WebSockConnectError();
                    };
                    _ws.onclose = function (event) {
                    };
                } catch (evt) {
                }
            }
            setTimeout("startTimer()",1000);
            return;
        }

    });



    function WebSockConnectError()
    {
        netErrTry++;
        netErrRetryTime = 0;
        var retryTime = 0;
        var curTs = Date.now();

        if (netErrTry <= 3) {
            //
            retryTime = 6;
        } else {
            retryTime = 20;
        }

        netErrRetryTime = curTs + retryTime*1000;

        if (refreshBy != 'SYSTEM') {
            $("#overlay").find("span").html(GetNetRetryMsg(curTs, netErrRetryTime));
            $("#overlay").show();
        }
    }



    var app = new Vue({
        el:'#dashboard',

        data : {

            agents_logged_in_777: 0,
            agents_logged_in_786: 0,

            agents_in_idle_777: 0,
            agents_in_idle_786: 0,

            agents_in_break_777: 0,
            agents_in_break_786: 0,

            calls_in_queue_777: 0,
            calls_in_queue_786: 0,

            average_handling_time_777: 0,
            average_handling_time_786: 0,

            service_level_777 : 0,
            service_level_786 : 0,

            daily_snap_shot_average_handling_time:0,

            daily_snap_shot_average_wait_time:0,

            daily_snap_shot_work_code_save_percent:0,

            skills : _.keyBy(skills, 'skill_id'),

            agents : _.keyBy(agents,'agent_id'),

            seats : _.keyBy(active_seats,'seat_id'),

            aux_messages : _.keyBy(aux_messages,'aux_code'),

            aux_only_messages: aux_only_messages,

            seat_status : {"I":"Idle", "R":"Ringing...", "N":"Unreachable", "U":"Unreachable", "X":"Enable...", "T": "No Agent Talk", "S":"Serving", "O":"No Agent Talk", "B":"Move Out"}
        },

        computed: {
            sortedSkills: function () {
                return _.orderBy(this.skills, 'skill_id', 'asc')
            },

            loggedAgents_777: function () {
                return _.filter(this.seats, function (a,b) {
                    return a.hasOwnProperty('seat_id')
                        && a.seat_id !== '0' && a.hasOwnProperty('agent_id') && a.agent_id !== '0'
                        && a.hasOwnProperty('skillSet') && is777Agent(a.skillSet);
                });
            },

            loggedAgents_786: function () {
                return _.filter(this.seats, function (a,b) {
                    return a.hasOwnProperty('seat_id')
                        && a.seat_id !== '0' && a.hasOwnProperty('agent_id') && a.agent_id !== '0'
                        && a.hasOwnProperty('skillSet') && is786Agent(a.skillSet);
                });
            }
        },
        methods:{
            getAverageHandlingTime: function () {
                axios.get('<?php echo $this->url('task=aht&act=for777'); ?>')
                    .then(function (response) {

                        var aht = parseInt(response.data.aht) ? parseInt(response.data.aht) : 0;

                        Vue.set(app, 'average_handling_time_777', aht);

                        /*===============Calculating Service Level=======================*/
                        var service_level = service_level_calculation_method == 'A' ?
                            (parseInt( response.data.answerd_within_service_level) / parseInt( response.data.calls_offered)) * 100
                            : ( (parseInt( response.data.answerd_within_service_level) /
                                (parseInt( response.data.calls_answerd) + parseInt( response.data.abandoned_after_threshold))) * 100);

                        if (!Number(service_level)){
                            service_level = 0;
                        }

                        Vue.set(app, 'service_level_777', service_level.toPrecision(3));

                    })
                    .catch(function (error) {})
                    .then(function () {});

                axios.get('<?php echo $this->url('task=aht&act=for786'); ?>')
                    .then(function (response) {

                        var aht = parseInt(response.data.aht) ? parseInt(response.data.aht) : 0;

                        Vue.set(app, 'average_handling_time_786', aht);

                        /*===============Calculating Service Level=======================*/
                        var service_level = service_level_calculation_method == 'A' ?
                            (parseInt( response.data.answerd_within_service_level) / parseInt( response.data.calls_offered)) * 100
                            : ( (parseInt( response.data.answerd_within_service_level) /
                                (parseInt( response.data.calls_answerd) + parseInt( response.data.abandoned_after_threshold))) * 100);

                        if (!Number(service_level)){
                            service_level = 0;
                        }

                        Vue.set(app, 'service_level_786', service_level.toPrecision(3));

                    })
                    .catch(function (error) {})
                    .then(function () {});
            },

            getDailySnapShotData: function () {
                axios.get('<?php echo $this->url('task=report&act=get-daily-snap-shot-fot-the-day'); ?>')
                    .then(function (response) {

                         if (response.data.service_level !== 'undefined' && response.data.service_level != null) {
                             generateDoughnutChart(response.data.service_level,"daily-snap-shot-service-level", "Service Level");
                         }else{
                             generateDoughnutChart(0,"daily-snap-shot-service-level", "Service Level");
                         }

                      /*  $("#daily-snap-shot-average-wait-time ,#daily-snap-shot-average-handling-time, #logo")
                            .css("height", $("#daily-snap-shot-service-level").attr('height') );

                        $("#daily-snap-shot-work-code-save-percent")
                            .css("height", $("#daily-snap-shot-service-level").attr('height') - 50);*/


                        if (response.data.abandoned_ratio !== 'undefined' && response.data.abandoned_ratio != null) {
                            generateDoughnutChart(response.data.abandoned_ratio,"daily-snap-shot-abandoned-ratio", "Abandoned Ratio");
                        }else{
                            generateDoughnutChart(0,"daily-snap-shot-abandoned-ratio", "Abandoned Ratio");
                        }

                        Vue.set(app, 'daily_snap_shot_average_handling_time', response.data.aht || 0);
                        Vue.set(app, 'daily_snap_shot_average_wait_time', response.data.awt || 0);
                        Vue.set(app, 'daily_snap_shot_work_code_save_percent', response.data.word_code || 0);

                        if (response.data.word_code !== 'undefined' && response.data.word_code != null) {
                            //generateDoughnutChart(response.data.word_code,"daily-snap-shot-work-code-save-percent", "Work Code Saved");
                            generateThermometerChart(response.data.word_code, 'daily-snap-shot-work-code-save-percent', 'Work Code Saved', $("#daily-snap-shot-service-level").attr('height') - 35);

                        }else{
                            generateThermometerChart(0, 'daily-snap-shot-work-code-save-percent', 'Work Code Saved', $("#daily-snap-shot-service-level").attr('height') - 35);
                        }

                       // generateThermometerChart(110, 'daily-snap-shot-work-code-save-percent', 'Work Code Saved',$("#daily-snap-shot-service-level").attr('height'));

                    })
                    .catch(function (error) {})
                    .then(function () {});
            }
        },
        created: function () {
            this.getAverageHandlingTime();
            this.getDailySnapShotData();

            setInterval(function () {
                this.getAverageHandlingTime();
            }.bind(this), 60000);

            setInterval(function () {
                this.getDailySnapShotData();
            }.bind(this), 100000);
        }

    });

    $("#LogoutBtn").click(function(){
        console.log("Logout Button Clicked");
        window.close();
    });


</script>
<script src="js/Chart.min.js"></script>

<script>
    function generateDoughnutChart(service_level, element, title){
        Chart.pluginService.register({
            beforeDraw: function (chart) {
                if (chart.config.options.elements.center) {
                    //Get ctx from string
                    var ctx = chart.chart.ctx;

                    //Get options from the center object in options
                    var centerConfig = chart.config.options.elements.center;
                    var fontStyle = centerConfig.fontStyle || 'Arial';
                    var txt = centerConfig.text;
                    var color = centerConfig.color || "#5B3256";
                    var sidePadding = centerConfig.sidePadding || 10;
                    var sidePaddingCalculated = (sidePadding/100) * (chart.innerRadius * 2);
                    //Start with a base font of 30px
                    ctx.font = "30px " + fontStyle;

                    //Get the width of the string and also the width of the element minus 10 to give it 5px side padding
                    var stringWidth = ctx.measureText(txt).width;
                    var elementWidth = (chart.innerRadius * 2) - sidePaddingCalculated;

                    // Find out how much the font can grow in width.
                    var widthRatio = elementWidth / stringWidth;
                    var newFontSize = Math.floor(30 * widthRatio);
                    var elementHeight = (chart.innerRadius * 2);

                    // Pick a new font size so it will not be larger than the height of label.
                    var fontSizeToUse = Math.min(newFontSize, elementHeight);

                    //Set font settings to draw it correctly.
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    var centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
                    var centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 2);
                    ctx.font = fontSizeToUse+"px " + fontStyle;
                    ctx.fillStyle = color;

                    //Draw text in center
                    ctx.fillText(txt, centerX, centerY);
                }
            }
        });

       var chart =  new Chart(document.getElementById(element), {

            type: 'doughnut',
            data: {
                datasets: [{
                    data: [100 - service_level, service_level > 0 ? service_level : 0.00000000000000000000001]
                }]
            },
            options: {
                maintainAspectRatio:false,
                title: {
                    display: true,
                    position: 'bottom',
                    text: title,
                    fontSize: 20,
                    fontColor: "#5B3256"
                },
                elements: {
                    center: {
                        text: service_level+"%",
                        sidePadding: 20,
                        fontColor: "#5B3256"
                    }
                },
                legend: {
                    labels: {
                        fontSize: 20,
                        fontColor: "#5B3256"
                    }
                }
            }
        });
    }

    function generateThermometerChart(data, element, title, g_height) {
        var thermometer = new Thermometer({
            height: g_height
        });
        var container = document.getElementById(element);
        thermometer.render(container, data, 0, data+20 );

    }
</script
</body>
</html>