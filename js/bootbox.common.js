var bootBoxScript = document.createElement('script');
bootBoxScript.src = 'js/bootbox/bootbox.min.js';
document.getElementsByTagName("head")[0].appendChild(bootBoxScript);
var toastrScript = document.createElement('script');
toastrScript.src = 'js/toastr/toastr.min.js';
document.getElementsByTagName("head")[0].appendChild(toastrScript);

function confirm_status(event) {
    var msg = $(event.currentTarget).attr('data-msg');
    var href = $(event.currentTarget).attr('data-href');

    bootbox.confirm({
        message: msg,
        buttons: {
            confirm: {
                label: 'Yes',
                className: 'btn-success'
            },
            cancel: {
                label: 'No',
                className: 'btn-danger'
            }
        },
        callback: function (result) {
            if (result) {
                $.ajax({
                    type: "POST",
                    url: href,
                    dataType: "text",
                    success: function (resp) {
                        console.log(resp);
                        var data = JSON.parse(resp);
                        if (data.status) {
                            toastr.success(data.msg);
                        } else {
                            toastr.error(data.msg);
                        }
                        ReloadAll();
                    }
                });
            }
        }
    });
};