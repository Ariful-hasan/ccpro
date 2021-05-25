<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>gPlex Contact Centre Dashboard</title>

    <link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="ccd/tabular-dashboard.css">
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.js"></script>

    <!-- Include the plugin's CSS and JS: -->
    <script src="js/bootstrap-multiselect/js/bootstrap-multiselect.js"></script>
    <link rel="stylesheet" href="js/css/bootstrap-multiselect/css/bootstrap-multiselect.css" type="text/css"/>
    <script src="assets/js/vue.min.js"></script>
</head>
<body>

<div class="container-fluid" id="dashboard">
    <div id="overlay" style="position: absolute;display: none; top: 0;left: 0;right: 0;bottom: 0;background-color: #000; opacity:0.2; z-index: 29999;cursor: pointer;">
        <span style="position: absolute;top: 50%;left: 50%;font-size: 20px;color: white;transform: translate(-50%,-50%)">Connection to the server is lost, Trying ...</span>
    </div>
  <div class="header-summary">
        <div class="col-md-2"><a href="<?php echo base_url(); ?>"><img src="assets/images/logo.png"></a></div>
        <div class="col-md-9"></div>
        <div class="col-md-1">
            <button type="button" id="LogoutBtn" class="btn btn-md pull-right" title="Logout" style="margin-top: 10px">
                <img  src="image/logout.png" alt="Logout" >
            </button>
        </div>
  </div>

  <div class="text-center clearfix">
        <div class="col-md-3 header-item"><h2>Logged In Agents: {{ total_logged_agents }}</h2></div>
        <div class="col-md-3 header-item"><h2>Calls In Queue: {{ total_calls_in_queue }}</h2> </div>
        <div class="col-md-2 header-item"><h2>Break: {{ total_in_break_agents }}</h2></div>
        <div class="col-md-2 header-item"><h2>ACW: {{ total_in_acw_agents }}</h2></div>
        <div class="col-md-2 header-item"><h2>Idle: {{ total_idle_agents}} </h2></div>
  </div>

  <?php if (!empty($request->getRequest('panel')) && $request->getRequest('panel')== 'ivr'): ?>

  <div class="panel panel-default">
        <div class="panel-heading">IVR Call Summary <button @click="prepareDownloadData(ivr_summary, ivr_outbound_table_show_columns)" type="button" class="btn btn-primary btn-xs pull-right">Download</button></div>
        <div class="panel-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered small text-center m-b-0">
                    <thead>
                    <tr>
                        <th class="text-center"> Total Calls</th>
                        <th class="text-center">Maximum Time</th>
                        <th class="text-center">Average Time</th>
                    </tr>
                    <tr id="ivr">
                        <td>{{ ivr_summary.call_count}}</td>
                        <td>{{ ivr_summary.max_time}}</td>
                        <td>{{ ivr_summary.avg_time}}</td>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
  </div>

  <?php endif; ?>
  <?php if (!empty($request->getRequest('panel')) && $request->getRequest('panel') == 'outbound'): ?>

  <div class="panel panel-default">
        <div class="panel-heading">Outbound Call Summary <button @click="prepareDownloadData(outbound_summary, ivr_outbound_table_show_columns)" type="button" class="btn btn-primary btn-xs pull-right">Download</button></div>
        <div class="panel-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered small text-center m-b-0">
                    <thead>
                    <tr>
                        <th class="text-center"> Total Calls</th>
                        <th class="text-center">Maximum Time</th>
                        <th class="text-center">Average Time</th>
                    </tr>
                    <tr id="outboundSkill">
                        <td>{{ outbound_summary.call_count}}</td>
                        <td>{{ outbound_summary.max_time}}</td>
                        <td>{{ outbound_summary.avg_time}}</td>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
  </div>

  <?php endif; ?>

  <?php if (!empty($request->getRequest('panel')) && $request->getRequest('panel')== 'skill'): ?>

  <div class="panel panel-default">
      <div class="panel-heading clearfix">
          <h4 class="panel-title pull-left">Skill Summary</h4>
          <div class="pull-right">
              <div class="form form-inline">
                  <div class="form-group">
                      <label  class="control-label"><button @click="prepareDownloadData(sortedSkills, skills_table_show_columns)" type="button" class="btn btn-primary btn-xs pull-right">Download</button></label>
                  </div>
              </div>
          </div>
      </div>
        <div class="panel-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered small text-center m-b-0">
                    <thead>
                    <tr>
                        <th @click="sortSkillsBy('short_name')" class="text-center">Skill <span class="glyphicon glyphicon-sort"></span></th>
                        <th @click="sortSkillsBy('calls_in_queue')" class="text-center">In Queue <span class="glyphicon glyphicon-sort"></span></th>
                        <th @click="sortSkillsBy('calls_in_service')" class="text-center">In Service <span class="glyphicon glyphicon-sort"></span></th>
                        <th @click="sortSkillsBy('max_time')" class="text-center">Maximum Time <span class="glyphicon glyphicon-sort"></span></th>
                        <th @click="sortSkillsBy('avg_time')" class="text-center">Average Time <span class="glyphicon glyphicon-sort"></span></th>
                    </tr>
                    </thead>
                    <tbody v-if="Object.keys(paginatedSkills).length > 0">
                        <tr :id="skill.skill_id" v-for="(skill, index) in paginatedSkills" :key="skill.skill_id">
                            <td>{{ skill.short_name}}</td>
                            <td>{{ skill.calls_in_queue || 0 }}</td>
                            <td>{{ skill.calls_in_service || 0}}</td>
                            <td>{{ skill.max_time || '00:00:00'}}</td>
                            <td>{{ skill.avg_time || '00:00:00'}}</td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr><td colspan="5" class="text-center text-danger">No record Found!</td></tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5">
                                <div class="text-left col-md-4"><p>Showing {{ ((this.skillPageNumber -1 )* this.skillPerPage) + 1 }} - {{((this.skillPageNumber -1) * this.skillPerPage)+ this.paginatedSkills.length}} records</p></div>
                                <div class="navigation text-center col-md-4">
                                    <button type="button" class="btn btn-default btn-xs" :disabled="skillPageNumber <= 1" @click="skillPrevPage"> << Previous </button>
                                    <select name="skillPageNumber" id="skillPageNumber" class="btn btn-default btn-xs" v-model="skillPageNumber" style="max-width: 100px; text-align: center">
                                        <option v-for="page in skillPages" :value="page">Page-{{ page }}</option>
                                    </select> of {{ skillPageCount}} || Per page

                                    <select name="skillPerPage" id="skillPerPage" class="btn btn-default btn-xs" v-model="skillPerPage" style="max-width: 100px; text-align: center">
                                        <option :value="10">10</option>
                                        <option :value="20">20</option>
                                        <option :value="30">30</option>
                                        <option :value="50">50</option>
                                        <option :value="100">100</option>
                                        <option :value="200">200</option>
                                    </select>
                                    <button type="button" class="btn btn-default btn-xs" :disabled="skillPageNumber >= skillPageCount" @click="skillNextPage"> Next >></button>
                                </div>
                                <div class="text-right col-md-4"><p>Total : {{ this.sortedSkills.length}} records</p></div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
  </div>

  <?php endif; ?>

  <?php if (empty($request->getRequest('panel')) || $request->getRequest('panel')== 'agents'): ?>

    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <h4 class="panel-title pull-left">Agent Status</h4>
            <div class="pull-right">
                <div class="form form-inline">
                    <div class="form-group">
                        <label for="Skill_id" class="control-label"><button @click="prepareDownloadData(filterAgents, agents_table_show_columns)" type="button" class="btn btn-primary btn-xs pull-right">Download</button></label>
                        <label for="Skill_id" class="control-label">Skills</label>
                         <select name="filter_skill_id" id="filter_skill_id" class="form-control" v-model="filter_skill_id"  multiple="multiple">
                            <option :value="skill.skill_id" v-for="(skill, index) in sortedSkills">{{ skill.short_name }}</option>
                         </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-body p-0">
            <div class="table-responsive">
                <table class="table table-fixed table-bordered small text-center m-b-0">
                    <thead>
                    <tr>
                        <th  class="text-center">#</th>
                        <th  @click="sortBy('seat_id')" class="text-center" > Seat ID  <span class="glyphicon glyphicon-sort"></span></th>
                        <th  @click="sortBy('agent_id')" class="text-center" > Agent ID  <span class="glyphicon glyphicon-sort"></span></th>
                        <th  @click="sortBy('name')" class="text-center" > Agent Name  <span class="glyphicon glyphicon-sort"></span></th>
                        <th  @click="sortBy('nick')" class="text-center" > Agent Nick  <span class="glyphicon glyphicon-sort"></span></th>
                        <th  class="text-center" > Agent Skills</th>
                        <th  @click="sortBy('supervisor_name')" class="text-center" > Supervisor Name  <span class="glyphicon glyphicon-sort"></span></th>
                        <th  @click="sortBy('supervisor_nick')" class="text-center" > Supervisor Nick  <span class="glyphicon glyphicon-sort"></span></th>
                        <th  @click="sortBy('skill')" class="text-center" > Ans Skill  <span class="glyphicon glyphicon-sort"></span></th>
                        <th  @click="sortBy('status')" class="text-center" > In Contact Status  <span class="glyphicon glyphicon-sort"></span></th>
                       <!-- <th  @click="sortBy('inbound_number')" class="text-center" > Inbound No.  <span class="glyphicon glyphicon-sort"></span></th>
                        <th  @click="sortBy('outbound_number')" class="text-center" > Outbound No.  <span class="glyphicon glyphicon-sort"></span></th>-->
                        <th  @click="sortBy('time_in_state')" class="text-center" > Time In State  <span class="glyphicon glyphicon-sort"></span></th>
                    </tr>
                    </thead>


                    <tbody v-if="Object.keys(paginatedSeats).length > 0">
                        <tr :id="agent.seat_id" v-for="(agent, index) in paginatedSeats" :key="agent.agent_id"  >
                            <td> {{ index + 1 }} </td>
                            <td> {{ agent.seat_id }}</td>
                            <td> {{ agent.agent_id }}</td>
                            <td> {{ agent.name }} </td>
                            <td> {{ agent.nick }}</td>
                            <td> {{ agent.skill_names }}</td>
                            <td> {{ agent.supervisor_name }}</td>
                            <td> {{ agent.supervisor_nick }}</td>
                            <td> {{ agent.skill }}</td>
                            <td> {{ agent.status }}</td>
                          <!--  <td> {{ agent.inbound_number }}</td>
                            <td> {{ agent.outbound_number }}</td>-->
                            <td class="last-change-time" :class ="(agent.time_in_state > highlight_row_threshold && agent.status == 'Serving') ? 'highlight':'' ">
                                <span  class="hide duration">{{ agent.time_in_state }} </span>
                                <span class="human-readable-duration">0</span>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr > <td colspan="11" class="text-danger"> No Record Found!</td></tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="11">
                                <div class="text-left col-md-4"><p>Showing {{ ((this.pageNumber -1) * this.perPage) +1 }} - {{ ((this.pageNumber -1) * this.perPage)+this.paginatedSeats.length}} records</p></div>
                                <div class="navigation text-center col-md-4">
                                    <button type="button" class="btn btn-default btn-xs" :disabled="pageNumber <= 1" @click="prevPage"> << Previous </button>

                                    <select name="pageNumber" id="pageNumber" class="btn btn-default btn-xs" v-model="pageNumber" style="max-width: 100px; text-align: center">
                                        <option v-for="page in pages" :value="page">Page-{{ page }}</option>
                                    </select> of {{ pageCount}} || Per page

                                    <select name="perPage" id="perPage" class="btn btn-default btn-xs" v-model="perPage" style="max-width: 100px; text-align: center">
                                        <option :value="10">10</option>
                                        <option :value="20">20</option>
                                        <option :value="30">30</option>
                                        <option :value="50">50</option>
                                        <option :value="100">100</option>
                                        <option :value="200">200</option>
                                    </select>

                                    <button type="button" class="btn btn-default btn-xs" :disabled="pageNumber >= pageCount" @click="nextPage"> Next >></button>
                                </div>
                                <div class="text-right col-md-4"><p>Total : {{ this.filterAgents.length}} records</p></div>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

  <?php endif;?>
</div>

    <?php
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
    var refreshBy = '';
    var netErrTry = 0;
    var netErrRetryTime = 5;
    var intervalId = 0; // to store connection lost time create interval id
    var natPingTimeout = null;
    natPingTimes = 0;
    var agents = <?php echo json_encode($agents, JSON_PARTIAL_OUTPUT_ON_ERROR)?>;
    var skill_list = <?php echo json_encode($skills, JSON_PARTIAL_OUTPUT_ON_ERROR)?>;
    var seats = <?php echo json_encode($seats, JSON_PARTIAL_OUTPUT_ON_ERROR)?>;
    var aux_messages = <?php echo json_encode($aux_messages, JSON_PARTIAL_OUTPUT_ON_ERROR)?>;
    var aux_only_messages = [];
    var intervals = {}; // to store agents set interval id

    var wsUri = "ws://<?php echo $CCSettings->switch_ip.":".$ws_port."/chat" ?>";
    var token = "<?php echo $token;?>";
    var curSuffix = "<?php echo UserAuth::getDBSuffix();?>";

    aux_messages.forEach(function (aux) {
        aux_only_messages.push(aux.message);
    });

    var highlight_row_threshold = <?php echo $highlight_row_threshold ?>;
    var dashboard_user_logout_url = "<?php echo $this->url("task=agent&act=add-dashboard-logged-out");?>";



</script>
<script src="assets/js/lodash.min.js"></script>
<script src="ccd/wss.gPlexCCDashboardWS.js"></script>
<script src="ccd/dashboard-helper.js"></script>
<script src="ccd/tabularDashboardCommon.js"></script>

<script>

    $("#LogoutBtn").click(function(){
        console.log("Logout Button Clicked");
        obj.Logout();
        $.ajax({
            type: "POST",
            url: dashboard_user_logout_url
        }).done(function (data) {
            window.close();
        });
    });
</script>
</body>
</html>