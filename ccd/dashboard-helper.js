function secondsToMMSS(totalSeconds)
{

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
}

function interval_function(){
    netErrRetryTime--;
    if (netErrRetryTime < 0){
        netErrRetryTime = 5;
    }
    if (refreshBy !== 'SYSTEM') {
        var $overlay = $("#overlay");
        $overlay.find("span").html("Connection lost, Trying to establish a connection in " + netErrRetryTime + ' sec');
        $overlay.css('display','block');
    }
}

function timer(retryInterval) {
    setTimeout(function(){
        try {
            var _cws = new WebSocket(wsUri);
            _cws.onopen = function () {
                window.location.reload();
            };
            _cws.onerror = function (error) {
                WebSockConnectError();
            };
            _cws.onclose = function (event) {};
        }
        catch (evt) {}
    }, retryInterval * 1000);
}

function WebSockConnectError()
{
    netErrTry++;
    var retryTime =  netErrTry * 5;
    netErrRetryTime = retryTime;
    clearInterval(intervalId);
    intervalId = setInterval(interval_function, 1000);
    natPingTimeout = timer(retryTime);
}


function enableMultiSelect(){
    $('select[multiple]').multiselect({
    // http://wenzhixin.net.cn/p/multiple-select/docs/#multiple-select
    enableFiltering: true,
    includeSelectAllOption: true,
    disableIfEmpty: true,
    disabledText: 'Disabled ...',
    buttonWidth: '200px',
    placeholder: "Select to Filter",
    onChange: function(option, checked, select) {
        var selectedValue = option.val();

        if (selectedValue === 'multiselect-all'){
            app.filter_skill_id = [];
            if (checked){
                var $multiselectSkills = $('#filter_skill_id option');
                $multiselectSkills.each(function (k,v) {
                    if ('multiselect-all' !== v.value) {
                        app.filter_skill_id.push(v.value);
                    }
                });
            }
        }

        if (checked && selectedValue !== 'multiselect-all' ){
            app.filter_skill_id.push(selectedValue);
        }else{
            var index = app.filter_skill_id.indexOf(selectedValue);
            if (index > -1) {
                app.filter_skill_id.splice(index, 1);
            }
        }
    }
});
}