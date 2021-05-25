<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>gPlex Contact Centre Dashboard V3.0</title>

    <link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">
    <script src="assets/js/vue.min.js"></script>
    <style>
        [v-cloak] {display: none;}
        th:hover{cursor: pointer}
        .p-0{padding: 0; }
        .m-b-0{ margin-bottom: 0}
        .header-summary{display: block; min-height: 35px;margin-left: -15px}
        .grid{min-height: 30vh !important;border: 1px solid #fff;
            border-radius: 10px; text-align: center; background: #176577; color: #fff;}
        .grid h2{margin-top: 30px; font-size: 50px; text-shadow: 3px 3px 3px #0a0a0a}
        .grid .real-time-data{font-size: 150px; line-height: 150px; font-weight: 400; font-optical-sizing: auto; text-shadow: 5px 5px 5px #000}
    </style>

</head>
<body>
<div class="container-fluid" id="dashboard">
  <div class="header-summary row">
        <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2"><a href="<?php echo base_url(); ?>"><img src="assets/images/logo.png"></a></div>
        <div class="col-xs-8 col-sm-9 col-md-9 col-lg-9"><h3 class="text-center text-info">Dashboard 786</h3></div>
        <div class="col-xs-4 col-sm-1 col-md-1 col-lg-1">
            <button type="button" id="LogoutBtn" class="btn btn-md pull-right" title="Logout" style="margin-top: 10px">
                <img  src="image/logout.png" alt="Logout" >
            </button>
        </div>
  </div>

        <div class="col-sm-4 col-md-4 col-lg-4 grid">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <h2>Agents Logged In</h2>
            </div>
            <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak >
                {{ this.agents_logged_in }}
            </div>
        </div>

        <div class="col-sm-4 col-md-4 col-lg-4 grid">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <h2>Not Ready</h2>
            </div>
            <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak >
                {{ this.agents_in_break }}
            </div>
        </div>

        <div class="col-sm-4 col-md-4 col-lg-4 grid">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <h2>Idle Agents</h2>
            </div>
            <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak >
                {{ this.agents_in_idle }}
            </div>
        </div>

        <div class="col-sm-4 col-md-4 col-lg-4 grid">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <h2>ACW</h2>
            </div>
            <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak >
                {{ total_in_acw_agents }}
            </div>
        </div>

        <div class="col-sm-4 col-md-4 col-lg-4 grid">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <h2>Calls Waiting</h2>
            </div>
            <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak >
                {{ this.calls_in_queue }}
            </div>
        </div>
        <?php /* ?>
        <div class="col-sm-4 col-md-4 col-lg-4 grid">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <h2>Calls Offered</h2>
            </div>
            <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak >
                {{ this.calls_offered }}
            </div>
        </div>
        <?php */ ?>
        <div class="col-sm-4 col-md-4 col-lg-4 grid">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <h2>Calls in Service</h2>
            </div>
            <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak>
                {{ calls_in_service }}
            </div>
        </div>

        <div class="col-sm-6 col-md-6 col-lg-6 grid">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <h2>AHT</h2>
            </div>
            <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak>
                {{ average_handling_time }}
            </div>
        </div>

        <div class="col-sm-6 col-md-6 col-lg-6 grid">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <h2>Service Level</h2>
            </div>
            <div class="col-sm-12 col-md-12 col-lg-12 real-time-data" v-cloak>
                {{ service_level }}
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
    include_once('model/MAgent.php');
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
    var skills = <?php echo !empty($skills) ? json_encode($skills) : json_encode([]) ?>;
    var active_seats = <?php echo !empty($active_seats) ? json_encode($active_seats) : json_encode([]) ?>;
    var aux_messages = <?php echo !empty($aux_messages) ? json_encode($aux_messages) : json_encode([]) ?>;
    var aux_only_messages = [];
    var agents_except_777_category = <?php echo !empty($agents_except_777_category) ? json_encode($agents_except_777_category) : json_encode(array()); ?>;

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

    function is786Agent(skill_list = []){
        var is768skill = skill_list.filter(function (s) {
            return skill786.indexOf(s) !== -1;
        });

        return is768skill.length > 0;
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

            if(skill786.includes(skill_id)){

                var calls_in_queue = skill.ciq_call_count_0 +  skill.ciq_call_count_1;
                Vue.set(app.skills[skill_id], 'calls_in_queue', calls_in_queue);

                // Find out total Calls in Queue...
                var total_calls_in_queue =  app.sortedSkills.reduce( function(a, b){
                    var calls_in_queue = 0;
                    if (skill786.includes(skill_id) && b.hasOwnProperty('calls_in_queue')){
                        calls_in_queue = b['calls_in_queue'];
                    }
                    return a + calls_in_queue;
                }, 0);

                Vue.set(app, 'calls_in_queue', 0);
                Vue.set(app, 'calls_in_queue', total_calls_in_queue);
            }
        },

        PopulateSeat: function(seatId, seat) {

            var status = seat.status === "B" ? app.aux_messages[seat.aux_code].message : app.seat_status[seat.status];

            Vue.set(app.seats, seat.seat_id, _.assign({}, seat, app.agents[seat.agent_id]));
            Vue.set(app.seats[seat.seat_id], 'status', status);

            /* Find out currently logged In, idle and agents in break number....*/
            Vue.set(app, 'agents_logged_in',0);
            Vue.set(app, 'agents_in_break',0);
            Vue.set(app, 'agents_in_idle',0);
            Vue.set(app, 'total_in_acw_agents',0);
            Vue.set(app, 'calls_in_service',0);

            app.loggedAgents.forEach (function(a, b){

                    Vue.set(app, 'agents_logged_in', app.agents_logged_in + 1);

                    if (a.status === 'Idle'){
                        Vue.set(app, 'agents_in_idle', app.agents_in_idle + 1);
                    }

                    if (a.status === "Serving"){
                        Vue.set(app, 'calls_in_service', app.calls_in_service + 1);
                    }

                    if (a.status === 'ACW'){
                        Vue.set(app, 'total_in_acw_agents', app.total_in_acw_agents + 1);
                    }

                    if (a.status !== "ACW" && app.aux_only_messages.includes(a.status)){
                        Vue.set(app, 'agents_in_break', app.agents_in_break + 1);
                    }
            });
        }

    };


    $(document).ready(function(){

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

            agents_logged_in: 0,

            agents_in_idle: 0,

            agents_in_break: 0,

            total_in_acw_agents: 0,

            calls_in_queue: 0,

            calls_offered: 0,

            calls_in_service: 0,

            average_handling_time: 0,

            service_level : 0,

            skills : _.keyBy(skills, 'skill_id'),

            agents : _.keyBy(agents_except_777_category,'agent_id'),

            seats : _.keyBy(active_seats,'seat_id'),

            aux_messages : _.keyBy(aux_messages,'aux_code'),

            aux_only_messages: aux_only_messages,

            seat_status : {"I":"Idle", "W": "ACW", "R":"Ringing...", "N":"Unreachable", "U":"Unreachable", "X":"Enable...", "T": "No Agent Talk", "S":"Serving", "O":"No Agent Talk", "B":"Move Out"},
        },

        computed: {
            sortedSkills: function () {
                return _.orderBy(this.skills, 'skill_id', 'asc')
            },

            loggedAgents: function () {
                return _.filter(this.seats, function (a,b) {
                    return a.hasOwnProperty('seat_id')
                        && a.seat_id !== '0' && a.hasOwnProperty('agent_id') && a.agent_id !== '0'
                        && a.hasOwnProperty('skillSet') && is786Agent(a.skillSet);
                });
            }
        },
        methods:{
            getAverageHandlingTime: function () {
                axios.get('<?php echo $this->url('task=aht&act=for786'); ?>')
                    .then(function (response) {

                        Vue.set(app, 'average_handling_time', parseInt(response.data.aht) || 0);
                        Vue.set(app, 'calls_offered', response.data.calls_offered);

                        /*===============Calculating Service Level=======================*/
                        var service_level = service_level_calculation_method == 'A' ?
                            (parseInt( response.data.answerd_within_service_level) / parseInt( response.data.calls_offered)) * 100
                            : ( (parseInt( response.data.answerd_within_service_level) /
                                (parseInt( response.data.calls_answerd) + parseInt( response.data.abandoned_after_threshold))) * 100);

                        if (!Number(service_level)){
                            service_level = 0;
                        }

                        Vue.set(app, 'service_level', parseInt(service_level) || 0);
                    })
                    .catch(function (error) {
                        console.error(error);
                    })
                    .then(function () {});
            }
        },
        created: function () {
            this.getAverageHandlingTime();

            setInterval(function () {
                this.getAverageHandlingTime();
            }.bind(this), 20000);
        }

    });

    $("#LogoutBtn").click(function(){
        console.log("Logout Button Clicked");
        window.close();
    });

</script>

</body>
</html>