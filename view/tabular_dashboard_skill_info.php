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
<div class="container-fluid" id="dashboard" v-cloak>
  <div class="header-summary">
        <div class="col-md-2"><a href="<?php echo base_url(); ?>"><img src="assets/images/logo.png"></a></div>
        <div class="col-md-9"><h3 class="text-center text-info">Today's Skill Wise Performance Record</h3></div>
        <div class="col-md-1">
            <button type="button" id="LogoutBtn" class="btn btn-md pull-right" title="Logout" style="margin-top: 10px">
                <img  src="image/logout.png" alt="Logout" >
            </button>
        </div>
  </div>


  <div class="panel panel-default">
       <!-- <div class="panel-heading clearfix">
            <h4 class="panel-title pull-left">Skill Summary</h4>
            <div class="pull-right">
                <div class="form form-inline">
                    <div class="form-group">
                        <label for="" class="control-label"><button @click="prepareDownloadData(skill_summary_data, skill_summary_show_columns)" type="button" class="btn btn-primary btn-xs pull-right">Download</button></label>
                        <label for="Skill_id" class="control-label">Skills</label>
                        <select name="filter_skill_id" id="filter_skill_id" class="form-control" v-model="filter_skill_id">
                            <option value="*" >All</option>
                            <option :value="skill.skill_id" v-for="(skill, index) in skills">{{ skill.short_name }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>-->
      <div class="panel-heading clearfix">
          <h4 class="panel-title pull-left">Skill Summary</h4>
          <div class="pull-right">
              <div class="form form-inline">
                  <div class="form-group">
                      <label for="" class="control-label"><button @click="prepareDownloadData(skill_summary, skill_summary_show_columns)" type="button" class="btn btn-primary btn-xs pull-right">Download</button></label>
                      <label for="Skill_id" class="control-label">Skills</label>
                      <select name="filter_skill_id" id="filter_skill_id" class="form-control" v-model="filter_skill_id"  multiple="multiple">
                          <option :value="skill.skill_id" v-for="(skill, index) in skills">{{ skill.short_name }}</option>
                      </select>
                  </div>
              </div>
          </div>
      </div>
        <div class="panel-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered small text-center m-b-0">
                    <thead>
                        <tr>
                            <th  class="text-center">#</th>
                            <th  @click="sortBy('skill_name')" class="text-center">Skill <span class="glyphicon glyphicon-sort"></span></th>
                            <th  @click="sortBy('service_duration')" class="text-center">Service Duration (HH:MM:SS) <span class="glyphicon glyphicon-sort"></span></th>
                            <th  @click="sortBy('calls_offered')" class="text-center">Offered <span class="glyphicon glyphicon-sort"></span></th>
                            <th  @click="sortBy('calls_answerd')" class="text-center">Answered <span class="glyphicon glyphicon-sort"></span></th>
                            <th  @click="sortBy('service_level')" class="text-center">Service Level (%) <span class="glyphicon glyphicon-sort"></span></th>
                            <th  @click="sortBy('calls_in_service')" class="text-center">Calls In Service <span class="glyphicon glyphicon-sort"></span></th>
                            <th  @click="sortBy('calls_in_queue')" class="text-center">Calls In Queue <span class="glyphicon glyphicon-sort"></span></th>
                            <th  @click="sortBy('answerd_within_service_level')" class="text-center">Ans. Within SL. <span class="glyphicon glyphicon-sort"></span></th>
                            <th  @click="sortBy('avg_ans_delay')" class="text-center">Avg. Ans. Delay (MM:SS) <span class="glyphicon glyphicon-sort"></span></th>
                            <th  @click="sortBy('calls_repeated')" class="text-center">Repeated <span class="glyphicon glyphicon-sort"></span></th>
                            <th  @click="sortBy('calls_abandoned')" class="text-center">Abandoned <span class="glyphicon glyphicon-sort"></span></th>
                            <th  @click="sortBy('abandoned_after_threshold')" class="text-center">Aban. After Th. <span class="glyphicon glyphicon-sort"></span></th>
                            <th  @click="sortBy('hold_time_in_queue')" class="text-center">Hold Time in Queue (MM:SS) <span class="glyphicon glyphicon-sort"></span></th>
                            <th  @click="sortBy('max_hold_time_in_queue')" class="text-center">Max Hold Time in Queue (MM:SS) <span class="glyphicon glyphicon-sort"></span></th>
                            <th  @click="sortBy('min_hold_time_in_queue')" class="text-center">Min Hold Time in Queue (MM:SS) <span class="glyphicon glyphicon-sort"></span></th>
                        </tr>
                    </thead>
                    <tbody v-if="Object.keys(paginatedSummary).length > 0">
                    <tr v-for="(summary, index) in paginatedSummary" :key="summary.skill_id">
                        <td> {{ index+1 }}</td>
                        <td> {{ summary.skill_name}}</td>
                        <td> {{ secondsToHHMMSS(summary.service_duration) }}</td>
                        <td> {{ summary.calls_offered }}</td>
                        <td> {{ summary.calls_answerd }}</td>
                        <td> {{ summary.service_level }} </td>
                        <td> {{ summary.calls_in_service }} </td>
                        <td> {{ summary.calls_in_queue }} </td>
                        <td> {{ summary.answerd_within_service_level }}</td>
                        <td> {{ secondsToMMSS(summary.avg_ans_delay) }}</td>
                        <td> {{ summary.calls_repeated }}</td>
                        <td> {{ summary.calls_abandoned }}</td>
                        <td> {{ summary.abandoned_after_threshold }}</td>
                        <td> {{ secondsToMMSS(summary.hold_time_in_queue) }}</td>
                        <td> {{ secondsToMMSS(summary.max_hold_time_in_queue) }}</td>
                        <td> {{ secondsToMMSS(summary.min_hold_time_in_queue) }}</td>
                    </tr>

                    </tbody>
                    <tbody v-else>
                        <tr><td colspan="16" class="text-center text-danger">No record Found</td></tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="16">
                            <div class="text-left col-md-4"><p>Showing {{ ((this.pagination.pageNumber - 1) * this.pagination.perPage) + 1 }} - {{((this.pagination.pageNumber -1) * this.pagination.perPage)+ this.paginatedSummary.length}} records</p></div>
                            <div class="navigation text-center col-md-4">
                                <button type="button" class="btn btn-default btn-xs" :disabled="pagination.pageNumber <= 1" @click="prevPage()"> << Previous </button>
                                <select name="pageNumber" id="pageNumber" class="btn btn-default btn-xs" v-model="pagination.pageNumber" style="max-width: 100px; text-align: center">
                                    <option v-for="page in pages" :value="page">Page-{{ page }}</option>
                                </select> of {{ pageCount}} || Per page

                                <select name="perPage" id="perPage" class="btn btn-default btn-xs" v-model="pagination.perPage" style="max-width: 100px; text-align: center">
                                    <option :value="10">10</option>
                                    <option :value="20">20</option>
                                    <option :value="30">30</option>
                                    <option :value="50">50</option>
                                    <option :value="100">100</option>
                                    <option :value="200">200</option>
                                </select>
                                <button type="button" class="btn btn-default btn-xs" :disabled="pagination.pageNumber >= pageCount" @click="nextPage()"> Next >></button>
                            </div>
                            <div class="text-right col-md-4"><p>Total : {{ this.skill_summary.length}} records</p></div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-left">Interval-to-Date, refreshing every 2 second</td>
                        <td colspan="6" class="text-left"> <span v-if="show_sl_formula">***{{ service_level_calculation_formula }}</span></td>
                        <td colspan="5" class="text-right">Last Updated: {{ last_update_time }}</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
  </div>

</div>
<!--<script src="ccd/jquery.min.js"></script>-->
<script src="assets/js/lodash.min.js"></script>
<script src="ccd/axios.min.js"></script>
<script src="ccd/dashboard-helper.js"></script>


<script>

    var skills = <?php echo !empty($skills) ? json_encode($skills) : [] ?>;
    var service_level_calculation_method = "<?php echo $service_level_calculation_method; ?>";
    var dataUrl = "<?php echo $this->url('task=report&act=current-day-skill-summary'); ?>";
    var show_sl_formula = "<?php echo $show_sl_formula ?>";

    var app = new Vue({
        el:'#dashboard',

        data : {
            skill_summary: [],
            skills : _.orderBy(skills,'short_name', 'asc'),
            last_update_time: '',
            has_record: false,
            filter_skill_id: [],
            service_level_calculation_formula: '(answerd_within_service_level / summary.calls_offered) * 100',
            show_sl_formula: show_sl_formula,
            sort: {
                order: 'asc',
                sortKey: 'skill_name'
            },
            pagination: {
                perPage : 20,
                pageNumber : 1
            },
            skill_summary_show_columns: [
                {key:'skill_name', name:'Skill', formatAsTime:false, default: ''},
                {key:'service_duration', name:'Service Duration', formatAsTime:true, default: '0:0'},
                {key:'calls_offered', name:'Offered', formatAsTime:false, default: '0'},
                {key:'calls_answerd', name:'Answered', formatAsTime:false, default: '0'},
                {key:'service_level', name:'Service Level', formatAsTime:false, default: '0.00'},
                {key:'calls_in_service', name:'Calls in Service', formatAsTime:false, default: '0'},
                {key:'calls_in_queue', name:'Calls in Queue', formatAsTime:false, default: '0'},
                {key:'answerd_within_service_level', name:'Ans. Within Service Level', formatAsTime:false, default: '0'},
                {key:'avg_ans_delay', name:'Avg. Ans. Delay', formatAsTime:true, default: '0:00'},
                {key:'calls_repeated', name:'Repeated', formatAsTime:false, default: '0'},
                {key:'calls_abandoned', name:'Abandoned', formatAsTime:false, default: '0'},
                {key:'abandoned_after_threshold', name:'Aban. After Th.', formatAsTime:false, default: '0'},
                {key:'hold_time_in_queue', name:'Hold Time in Queue', formatAsTime:true, default: '0:00'},
                {key:'max_hold_time_in_queue', name:'Max Hold Time in Queue', formatAsTime:true, default: '0:00'},
                {key:'min_hold_time_in_queue', name:'Min Hold Time in Queue', formatAsTime:true, default: '0:00'}
            ]
        },
        computed: {
            hasRecord: function(){
                if (Object.keys(this.filteredSummary).length > 0) {
                   return  this.has_record = true;
                }
            },

            pageCount: function(){
                var l = this.filteredSummary.length;
                var s = this.pagination.perPage;

                return  Math.ceil(l/s) > 0 ? Math.ceil(l/s) : 1;
            },
            pages:function(){
                return _.range(1, this.pageCount+1)
            },
            sortedSummary: function () {
                return _.orderBy(this.skill_summary, this.sort.sortKey, this.sort.order)
            },

            filteredSummary: function(){
                return _.filter(this.sortedSummary, function (a) {
                    return typeof app.filter_skill_id != 'undefined' && ((app.filter_skill_id.length == 0) || (app.filter_skill_id.includes(a.skill_id)));
                });
            },

            paginatedSummary: function(){
                if (this.pagination.pageNumber > this.pageCount ) {
                    this.pagination.pageNumber = 1;
                }
                var start = (this.pagination.pageNumber - 1) * this.pagination.perPage;
                var end   = start + this.pagination.perPage;

                return this.filteredSummary.slice(start, end);
            }
        },

        methods: {

            loadData: function () {
                axios.get(dataUrl)
                    .then(function (response) {
                        response.data.forEach(function (summary) {
                            var service_level = service_level_calculation_method == 'A' ?
                                (parseInt(summary.answerd_within_service_level) / parseInt(summary.calls_offered)) * 100
                                : ( (parseInt(summary.answerd_within_service_level) / (parseInt(summary.calls_answerd) + parseInt(summary.abandoned_after_threshold))) * 100);

                            if (!Number(service_level)){
                                service_level = 0;
                            }

                            summary['service_level'] = service_level == 100 ? 100 : service_level.toPrecision(4);
                            summary['avg_ans_delay'] = summary.calls_answerd > 0 ? Math.round(summary.hold_time_in_queue / summary.calls_answerd ) : 0;

                        });

                        app.$data.skill_summary = response.data;
                       // app.filteredBySkill();
                    })
                    .catch(function (error) {
                        app.$data.skill_summary = [];
                    })
                    .then(function () {
                        app.$data.last_update_time = new Date().toLocaleString();
                        app.$data.service_level_calculation_formula = service_level_calculation_method === "A" ? '(answered_within_service_level / summary.calls_offered) * 100 ' :  '[answered_within_service_level / (calls_answered + abandoned_after_threshold)) * 100]'
                    });
            },

            prevPage:function(){
                this.pagination.pageNumber--;
            },

            NextPage:function(){
                this.pagination.pageNumber++;
            },

            sortBy: function (sortKey) {

                if (sortKey === this.sort.sortKey){
                    this.sort.order = this.sort.order === 'asc' ? 'desc' : 'asc';
                }
                this.sort.sortKey = sortKey;
            },

            secondsToMMSS: function(second){
                if(second === 0) return second;

                let secs_diff = second % 60;
                secs_diff = secs_diff<10 ? "0" + secs_diff : secs_diff;
                second = Math.floor(second / 60);

                let mins_diff = second;
                mins_diff = mins_diff < 10 ? "0" + mins_diff : mins_diff;

                return mins_diff + ":" +secs_diff;
            },
            secondsToHHMMSS: function(totalSeconds){
                totalSeconds = parseInt(totalSeconds);
                if (!((typeof totalSeconds === "number") && (Math.floor(totalSeconds) === totalSeconds))){
                    totalSeconds = 0;
                }

                var hours   = Math.floor(totalSeconds / 3600);
                var minutes = Math.floor((totalSeconds - (hours * 3600)) / 60);
                var seconds = totalSeconds - (hours * 3600) - (minutes * 60);

                // round seconds
                seconds = Math.round(seconds * 100) / 100;

                var result = (hours < 10 ? "0" + hours : hours);
                result += ":" + (minutes < 10 ? "0" + minutes : minutes);
                result += ":" + (seconds  < 10 ? "0" + seconds : seconds);

                return result;
            },

            download: function(content) {
                var a = document.createElement('a');
                var mimeType = 'text/csv;encoding:utf-8';
                var fileName = 'dashboard-summary.csv';

                if (navigator.msSaveBlob) { // IE10
                    navigator.msSaveBlob(new Blob([content], {
                        type: mimeType
                    }), fileName);
                } else if (URL && 'download' in a) { //html5 A[download]
                    a.href = URL.createObjectURL(new Blob([content], {
                        type: mimeType
                    }));
                    a.setAttribute('download', fileName);
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                } else {
                    location.href = 'data:application/octet-stream,' + encodeURIComponent(content); // only this mime type is supported
                }
            },

            prepareDownloadData : function (data, columns) {
                var content = '';
                var header = [];
                var count = 1;

                if (Array.isArray(data)){
                    data.forEach(function(row, index) {
                        var result = [];
                        columns.forEach(function(column, i) {
                            if (count == 1){ // Add header Column
                                header.push(column.name);
                            }

                            if(row.hasOwnProperty(column.key)){
                                column.formatAsTime == true ? result.push(secondsToMMSS(row[column.key])) : result.push(row[column.key]);
                            }
                            else {
                                result.push(column.default);
                            }
                        });
                        if (count == 1){
                            content += header.join(',') +'\n';
                            count++;
                        }

                        var rowString = result.join(',');
                        content += index < data.length ? rowString + '\n' : rowString;
                    });
                }else{
                    var result = [];
                    columns.forEach(function(column, i) {
                        // Add header Column
                        header.push(column.name);

                        if(data.hasOwnProperty(column.key)){
                            column.formatAsTime == true ? result.push(secondsToMMSS(data[column.key])) : result.push(data[column.key]);
                        }
                        else {
                            result.push(column.default);
                        }
                    });

                    content +=  header.join(',') + '\n';
                    var rowString = result.join(',');
                    content +=  rowString + '\n';
                }

                this.download(content);
            }


        },

        created: function () {
            this.loadData();

            setInterval(function () {
                this.loadData();
            }.bind(this), 2000);
        }
    });




    $("#LogoutBtn").click(function(){
        console.log("Logout Button Clicked");
        window.close();
    });
</script>

<script>
    $(function () {
        enableMultiSelect();
    });
</script>

</body>
</html>