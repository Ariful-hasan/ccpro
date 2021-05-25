

function NatPing()
{
    obj.Ping();
    natPingTimes++;
    natPingTimeout = setTimeout(NatPing, 20000);
}

function timerFunction(rowIdentifier, extraIdentifier)
{
    extraIdentifier = typeof extraIdentifier !== 'undefined' ? extraIdentifier : '';

    if (rowIdentifier.length >= 3){
        Vue.set(app.seats[rowIdentifier], "time_in_state",app.seats[rowIdentifier].time_in_state+1);
        $(".human-readable-duration", "#"+ rowIdentifier +" "+ extraIdentifier)
            .text(secondsToMMSS(app.seats[rowIdentifier].time_in_state));
    }
    if (rowIdentifier.length === 2){
        Vue.set(app.skills[rowIdentifier], "max_time",app.skills[rowIdentifier].max_time + 1);
        Vue.set(app.skills[rowIdentifier], "avg_time",app.skills[rowIdentifier].avg_time + 1);
    }
    if (rowIdentifier === 'ivr'){
        Vue.set(app.ivr_summary, "max_time",sts);
        Vue.set(app.ivr_summary, "avg_time",sts);
    }
    if (rowIdentifier === 'outboundSkill'){
        Vue.set(app.outbound_summary, "max_time",sts);
        Vue.set(app.outbound_summary, "avg_time",sts);
    }
}

var  _DashBoard =  {
    Login: function() {
        obj.Login(dashboarduser);
    },

    Close: function(ts) {
        obj.Close();
        window.close();
    },

    PopulateIvr: function(data) {
        Vue.set(app.ivr_summary, 'call_count', data.call_count);
        Vue.set(app.ivr_summary, 'max_time', secondsToMMSS(data.max_time));
        Vue.set(app.ivr_summary, 'avg_time', secondsToMMSS(data.avg_time));
    },

    PopulateSkill: function(skill_id, skill) {

        Vue.set(app.skills[skill_id], 'calls_in_queue', skill.ciq_call_count_0 +  skill.ciq_call_count_1);
        Vue.set(app.skills[skill_id], 'calls_in_service', skill.cis_call_count_0 + skill.cis_call_count_1);
        Vue.set(app.skills[skill_id], 'max_time', secondsToMMSS(skill.max_time));
        Vue.set(app.skills[skill_id], 'avg_time', secondsToMMSS(skill.avg_time));

        // Find out total Calls in Queue...
        var total_calls_in_queue =  app.sortedSkills.reduce( function(a, b){
                var calls_in_queue = 0;
                if (b.hasOwnProperty('calls_in_queue')){
                    calls_in_queue = b['calls_in_queue'];
                }
                return a + calls_in_queue;
        }, 0);

        Vue.set(app, 'total_calls_in_queue', total_calls_in_queue);
    },

    PopulateOBSkill: function(data) {
        Vue.set(app.outbound_summary, 'call_count', data.call_count);
        Vue.set(app.outbound_summary, 'max_time', secondsToMMSS(data.max_time));
        Vue.set(app.outbound_summary, 'avg_time', secondsToMMSS(data.avg_time));
    },

    PopulateSeat: function(seatId, seat) {

            var answer_skill = seat.srv_id ? app.skills[seat.srv_id].short_name : '';
            var status = seat.status === "B" ? app.aux_messages[seat.aux_code].message : app.seat_status[seat.status];

            app.seats[seat.seat_id] = _.assign({},  seat, app.agents[seat.agent_id]);
            app.seats[seat.seat_id]['skill'] = answer_skill;
            app.seats[seat.seat_id].time_in_state = seat.event_change_time;
            app.seats[seat.seat_id].status = status;

            /*=============Before setting interval, clear if any exists============*/
           if (intervals.hasOwnProperty(seat.seat_id)){
               clearInterval(intervals[seat.seat_id]);
           }
           /*=========If there is no agent in seat, then no need to set timer ======*/
           if (seat.agent_id !== '0' && seat.agent_id !== ''){
               $(".duration", "#"+seat.seat_id).html(seat.event_change_time);
               intervals[seat.seat_id] = setInterval(timerFunction, 1000, seat.seat_id);
           }


        /* Find out currently logged In, idle and agents in break number....*/
        Vue.set(app, 'total_logged_agents',0);
        Vue.set(app, 'total_in_break_agents',0);
        Vue.set(app, 'total_idle_agents',0);
        Vue.set(app, 'total_in_acw_agents',0);

        app.filterAgents.forEach (function(a){

            Vue.set(app, 'total_logged_agents', app.total_logged_agents + 1);

            if (a.status === 'Idle'){
                Vue.set(app, 'total_idle_agents', app.total_idle_agents + 1);
            }

            if (a.status === 'ACW'){
                Vue.set(app, 'total_in_acw_agents', app.total_in_acw_agents + 1);
            }

            if (a.status !== "ACW" && app.aux_only_messages.includes(a.status)){
                Vue.set(app, 'total_in_break_agents', app.total_in_break_agents + 1);
            }
        });
    }

};


$(document).ready(function(){

    var dashboarduser = current_user_id;
    var natPingTimes = 0;
    var maxRefreshTimer = 60*60;
    var maxWebPingTime = 4*60;
    var curWebPingTime = maxWebPingTime;
    var refreshBy = '';
    var natPingTimeout = null;
    var curTs = Date.now();
    var refreshTimer = 0;
    var netErrTry = 0;

    try {
        obj = new gPlexCCDashboardWSApi();
        obj.loadSettings(wsUri, dashboarduser, token, curSuffix);
        obj.setNumLoggedIn(num_db_logged_in);
        obj.Connect();
    } catch (ex) {
        alert("Warning1: " + ex.message);
    }
    enableMultiSelect();
});



function isAgentAllowed(skillSet){
    var allowed = false;

    if (app.filter_skill_id.includes('multiselect-all') || app.filter_skill_id.length === 0 ){
        return true;
    }
    skillSet.forEach(function (a) {
        if (app.filter_skill_id.includes(a)){
            allowed = true;
        } 
    });

    return allowed;
}


var app = new Vue({
        el:'#dashboard',

        data : {
            order: 'asc',
            perPage : 20,
            pageNumber : 1,
            sortKey: 'agent_id',
            time_in_state_order: 'desc',

            filter_skill_id : [],
            total_calls_in_queue: 0,
            total_logged_agents: 0,
            total_in_break_agents: 0,
            total_idle_agents: 0,
            total_in_acw_agents: 0,

            skillSortKey: 'short_name',
            skillSortOrder: 'asc',
            skillPageNumber:1,
            skillPerPage:20,


            ivr_summary: { call_count : 0, max_time : 0, avg_time : 0 },

            outbound_summary: { call_count : 0, max_time : 0, avg_time : 0 },

            skills : _.keyBy(skill_list, 'skill_id'),

            seats : _.keyBy(seats, 'seat_id'),

            agents : _.keyBy(agents, 'agent_id'),

            aux_messages : _.keyBy(aux_messages,'aux_code'),

            aux_only_messages: aux_only_messages,

            seat_status : {"H":"Hold", "W": "ACW", "D": "Dialing...", "I":"Idle", "R":"Ringing...", "N":"Unreachable", "X":"Enable...", "T": "No Agent Talk", "S":"Serving", "O":"No Agent Talk", "B":"Move Out"},

            skills_table_show_columns: [
                {key:'short_name', name:'Skill', formatAsTime:false, default: ''},
                {key:'calls_in_queue', name:'In Queue', formatAsTime:false, default: 0},
                {key:'calls_in_service', name:'In Service', formatAsTime:false, default: 0},
                {key:'max_time', name:'Maximum Time', formatAsTime:true, default: '0:0'},
                {key:'avg_time', name:'Average Time', formatAsTime:true, default: '0:0'}
            ],

            agents_table_show_columns: [
                {key:'seat_id', name:'Seat', formatAsTime:false, default: ''},
                {key:'agent_id', name:'Agent ID', formatAsTime:false, default: ''},
                {key:'name', name:'Agent Name', formatAsTime:false, default: ''},
                {key:'nick', name:'Agent Nick', formatAsTime:false, default: ''},
                {key:'supervisor_name', name:'Supervisor Name', formatAsTime:false, default: ''},
                {key:'supervisor_nick', name:'Supervisor Nick', formatAsTime:false, default: ''},
                {key:'ans_skill', name:'Skill', formatAsTime:false, default: ''},
                {key:'status', name:'In Contact Status', formatAsTime:false, default: ''},
                //{key:'inbound_number', name:'Inbound No.', formatAsTime:false, default: ''},
                //{key:'outbound_number', name:'Outbound No.', formatAsTime:false, default: ''},
                {key:'time_in_state', name:'Time in State', formatAsTime:true, default: ''}
            ],

            ivr_outbound_table_show_columns: [
                {key:'call_count', name:'Total Calls', formatAsTime:false, default: ''},
                {key:'max_time', name:'Maximum Time', formatAsTime:true, default: '0:0'},
                {key:'avg_time', name:'Average Time', formatAsTime:true, default: '0:0'},
            ]
        },

        computed: {
            orderedAgents: function () {
                return _.orderBy(this.seats, [this.sortKey, 'time_in_state'], [this.order, this.time_in_state_order])
            },

            filterAgents: function(){
                return _.filter(this.orderedAgents, function (s) {
                    return (s.hasOwnProperty('agent_id') && s.agent_id !== '0' && s.agent_id !== '')
                        && s.hasOwnProperty('skill_set') && isAgentAllowed(s.skill_set);
                });

            },


            pageCount: function(){
                var l = this.filterAgents.length;
                var s = this.perPage;

                return  Math.ceil(l/s) > 0 ? Math.ceil(l/s) : 1;
            },

            pages:function(){
                return _.range(1, this.pageCount+1)
            },

            skillPageCount: function(){
                var l = this.sortedSkills.length;
                var s = this.skillPerPage;

                return  Math.ceil(l/s) > 0 ? Math.ceil(l/s) : 1;
            },

            skillPages:function(){
                return _.range(1, this.skillPageCount+1)
            },

            paginatedSeats: function(){
                if (this.pageNumber > this.pageCount ) {
                    this.pageNumber = 1;
                }
                var start = (this.pageNumber - 1) * this.perPage;
                var end   = start + this.perPage;

                return this.filterAgents.slice(start, end);
            },

            sortedSkills: function () {
                return _.orderBy(this.skills, this.skillSortKey, this.skillSortOrder)
            },

            paginatedSkills: function(){
                if (this.skillPageNumber > this.skillPageCount ) {
                    this.skillPageNumber = 1;
                }

                var start = (this.skillPageNumber -1) * this.skillPerPage,
                    end   = start + this.skillPerPage;
                return this.sortedSkills.slice(start, end);
            }
        },

        methods:{
            sortBy: function (sortKey) {

                if (sortKey === 'time_in_state') {
                    this.time_in_state_order = this.time_in_state_order === 'asc' ? 'desc' : 'asc';
                }else{
                    if (sortKey === this.sortKey){
                        this.order = this.order === 'asc' ? 'desc' : 'asc';
                    }
                    this.sortKey = sortKey;
                }
            },

            sortSkillsBy: function(skillSortKey){
                if (skillSortKey === this.skillSortKey){
                    this.skillSortOrder = this.skillSortOrder === 'asc' ? 'desc' : 'asc';
                }
                this.skillSortKey = skillSortKey;
            },

            nextPage:function(){
                this.pageNumber++;
            },

            prevPage:function(){
                this.pageNumber--;
            },

            skillNextPage:function(){
                this.skillPageNumber++;
            },

            skillPrevPage:function(){
                this.skillPageNumber--;
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

        }

    });