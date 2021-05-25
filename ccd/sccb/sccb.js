    function get_customer_lms_info(caller_id){

        populateSecondaryCallControlBar(caller_id, {});

      /*  let uli = sessionStorage.getItem("user_lms_info_" + caller_id);
        if(uli) {
            populateSecondaryCallControlBar(caller_id, JSON.parse(uli));
        }else{
            data = {};
            sessionStorage.setItem("user_lms_info_" + caller_id, JSON.stringify(data));
            populateSecondaryCallControlBar(caller_id, data);
            $.ajax({
                type: "POST",
                url: user_lms_info_url,
                data: {"cli": caller_id},
                dataType: "json"
            }).done(function (data) {
                if (typeof(data) == 'object') {
                    sessionStorage.setItem("user_lms_info_" + caller_id, JSON.stringify(data));
                    populateSecondaryCallControlBar(caller_id, data);
                }
            });
        }*/
    }

    function populateSecondaryCallControlBar(caller_id, data) {
        let html = '<div id="secondary-call-control-bar">';
        /* for (let [property, val] of Object.entries(data)) {
            html += "<div>"+ property +": "+ val +"</div>";
        } */
        html += "<div id='last-month-disposition' data-cli='"+ caller_id +"'>Latest Work Code</div>";
        html += '<div>';
        let sccb = document.getElementById("secondary-call-control-bar");
        if (sccb){
            document.getElementById("secondary-call-control-bar").remove();
        }
        let ccd = document.getElementsByClassName('ccd');
        ccd[0].insertAdjacentHTML("afterend", html);
    }

    function get_customer_last_month_disposition(caller_id){

        $.ajax({
            type: "POST",
            url: user_last_month_disposition_url,
            data: {"cli": caller_id},
            dataType: "json"
        }).done(function (data) {
            if (data.constructor === Array && data.length > 0) {
                data.sort(function(a,b){
                    return new Date(b.tstamp * 1000) - new Date(a.tstamp * 1000);
                });
                /* sessionStorage.setItem("last_month_disposition_"+ caller_id, JSON.stringify(data)); */
                populateLastMonthDisposition(data);
            }else{
                populateLastMonthDisposition("No record Found!");
            }
        });
    /*
        let lmd = sessionStorage.getItem("last_month_disposition_"+ caller_id);
        if (lmd) {
            populateLastMonthDisposition(JSON.parse(lmd));
        }else{
            $.ajax({
                type: "POST",
                url: user_last_month_disposition_url,
                data: {"cli": caller_id},
                dataType: "json"
            }).done(function (data) {
                if (data.constructor === Array && data.length > 0) {
                    data.sort(function(a,b){
                        return new Date(b.tstamp * 1000) - new Date(a.tstamp * 1000);
                    });
                    sessionStorage.setItem("last_month_disposition_"+ caller_id, JSON.stringify(data));
                    populateLastMonthDisposition(data);
                }
            });
        } */
    }


    function populateLastMonthDisposition(dispositions) {
        if($("#last-month-disposition-window").length === 0) {

            let html = "<div class='call-log-window' id='last-month-disposition-window'>";
            html += "<div class='tab-call-log'>";
            html += "<div id='last-month-disposition-close' class='btn-close'>&times;</div>";
            html += "<div class='clear'></div>";
            html += "</div>";
            html += "<div class='call-log-container'>";
            html += "<table class='table table-bordered small' style='margin-bottom: 0'>";
            html +=" <tr> <th>Date </th><th> Agent </th> <th>Module</th> <th>Disposition</th></tr>";

            if (typeof dispositions == 'string'){
                html += "<tr> ";
                html += "<th colspan='4' class='text-center text-danger'>"+ dispositions +" </th> ";
                html += "</tr>";
            } else{
                dispositions.forEach(function (disposition) {
                    let disposition_date = new Date(disposition.tstamp * 1000);
                    let options = {day: 'numeric', month: 'short', year: 'numeric',
                        hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: true
                    };
                    html += "<tr> ";
                    html += "<th>"+ disposition_date.toLocaleString('en-US', options) +" </th> ";
                    html += "<th>"+ disposition.agent_id +"</th>";
                    html += "<th>"+ disposition.module +"</th>";
                    html += "<th>"+ disposition.title +"</th>";
                    html += "</tr>";
                });
            }


            html += "</table>";
            html += "</div>";
            html += "</div>";
            $('body').append(html);
        }
    }
	
	
	
	
	
	
	
	/*============================Last Month Disposition Window=======================*/
	$(function(){
		
		$(document).on("click", "#last-month-disposition", function (e) {
            console.log("LMD CLICKED");
            let caller_id = $("#last-month-disposition").data('cli');
            get_customer_last_month_disposition(caller_id);
        });
        $(document).on("click", "#last-month-disposition-close", function (e) {
            $(this).closest('#last-month-disposition-window').remove();
        });
		
	});
        
		
		
		
		
		
	/*========================= in #tabs a.tab click ================================
	
	   try {
			let cli = $(this).data("cli");
			get_customer_lms_info(cli);
		}catch(e){
			console.error(e);
		}
		
	=================================================================================*/