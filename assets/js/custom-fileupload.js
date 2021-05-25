
function customFileUploadDesign() {
    $(document).on('change','.up', function() {
        var names = [];
        var length = $(this).get(0).files.length;
        for (var i = 0; i < $(this).get(0).files.length; ++i) {
            names.push($(this).get(0).files[i].name);
        }
        if(length>2){
            var fileName = names.join(', ');
            $(this).closest('.form-group').find('.form-control').attr("value",length+" files selected");
        }
        else{
            $(this).closest('.form-group').find('.form-control').attr("value",names);
        }
    });
}

async function createHeaderSelectOption(selectValue='') {
    if (typeof head_data !== 'undefined'  && Object.keys(head_data).length > 0) {
        // head_data.forEach(key => {
        //     select_option += '<option value="'+key+'">'+key+'</option>';
        // });

        select_option = '<option value="">Select</option>';
        for (const [key, value] of Object.entries(head_data)) {
            let selected = (selectValue!= '' && selectValue == key) ? " selected='selected' " : "";
            select_option += '<option value="'+key+'" '+selected+'>'+value+'</option>';
        }
    }
}

function getval(e, val=null) {
    let id = getPlainString(val).toLowerCase();

    if (typeof bind_ids !== 'undefined' && bind_ids.length > 0){
        let val_count = 0;
        for (let i in bind_ids) {
            if (e.value!= "" && $('#'+bind_ids[i]).val() == e.value) {
                val_count++;
            }
            if (val_count > 1){
                alert("This field is already selected!!");
                $("#"+id).val('');
                return false;
            }
        }
    }

    let th_selector = $("#"+id).prepend().parent().parent().parent();

    if (e.value == '') {
        th_selector.removeClass('active-select');
    } else {
        th_selector.addClass('active-select');
    }

    //console.log(bind_ids);
    //console.log(previous_selected_value);
    // if (typeof previous_selected_value !== 'undefined' && previous_selected_value != '' && previous_selected_value != 'select' && e.value == 'select') {
    //     if (validate_ary.includes(previous_selected_value)){
    //         const index = validate_ary.indexOf(previous_selected_value);
    //         validate_ary.splice(index, 1);
    //     }
    // }
    // let id = getPlainString(val).toLowerCase();
    // let th_selector = $("#"+id).prepend().parent().parent().parent();

    // if (e.value == 'select') {
    //     th_selector.removeClass('active-select');
    // } else {
    //     if (validate_ary.includes(e.value) === false) {
    //         th_selector.addClass('active-select');
    //         validate_ary.push(e.value);
    //     } else {
    //         alert("This field is already selected!!");
    //         $("#"+id).val('select');
    //     }
    // }
}

var setTableData = async (res) => {
    if (typeof res !== 'undefined' && Object.keys(res).length > 0) {

        //console.table(res);
        if (true || res.status == 200) {
            $("#tbl_panel").removeClass('hide');
            $("#tbl_panel").show();
            $("#fname").val(res.fname);
            FILE_NAME = res.fname;
            //clear table
            //$(upload_div).hide(3000);
            //set header


            let table_header = '<thead><tr>';
            for(let [key, value] of Object.entries(res.header))
            {
                let active_class = await set_pd_settings_header(value);
                table_header += '<th class="center'+active_class+'"> <span class="th-txt">'+value+'</span>'+header_select_option(value)+'</th>';
                bind_ids.push(getPlainString(value).toLowerCase());
            }
            table_header += '</tr></thead>';
            $(table_header).appendTo(tbl);

            for (let i in bind_ids) {
                $('#'+bind_ids[i]).bind('click', function() {
                    previous_selected_value = $(this).val();
                });
            }

            //set tbody
            let table_body_data = '<tbody>';
            res.data.forEach(item => {
                let tbody_row = '<tr>';
                for (let [key, value] of Object.entries(item)) {
                    tbody_row += '<td>'+value+'</td>';
                }
                tbody_row += '</tr>';
                $(tbody_row).appendTo(tbl);
            });
            ///hide upload div
            //$(upload_div).hide(1500);
        } else {
            alert(res.error);
        }
    }
};


function getPlainString(str) {
    str = str.replace(/[- )(.]/g,'');
    return str;
}

function readFile() {
    let file = document.getElementById('up').files[0];
    let ext = file.name.substr(-3);

    if (ext == 'csv' && file.type == "application/vnd.ms-excel") {
        var upload = new Upload(file);
        upload.doUpload();
    } else {
        alert("Only CSV file is allowed");
        document.getElementById('up').value='';
    }
}

let header_select_option = (header_html) => {
    let selid = getPlainString(header_html).toLowerCase();
    let html = '';
    html += '<div class="panel panel-default p-0">';
    html += '<div class="panel-body p-0">';
    html += '<select name="head_option[]" id="'+selid+'" onchange="getval(this, `'+header_html+'`)">';
    html += this.select_option;
    html += '</select>';
    html += '</div>';
    html += '</div>';
    return html;
};


var set_pd_settings_header = async (csv_header) => {
    let active_class = "";
    let selected_value = "";
    if (typeof PD_SETTINGS_HEADER !== 'undefined' && Object.keys(PD_SETTINGS_HEADER).length > 0) {
        for (const [idx, itm] of Object.entries(PD_SETTINGS_HEADER)) {
            if (itm.trim() === csv_header.trim()) {
                active_class = " active-select";
                selected_value = idx;
            }
        }
    }
    await createHeaderSelectOption(selected_value);
    return active_class;
};


var Upload = function (file) {
    this.file = file;
};

Upload.prototype.getType = function() {
    return this.file.type;
};
Upload.prototype.getSize = function() {
    return this.file.size;
};
Upload.prototype.getName = function() {
    return this.file.name;
};
Upload.prototype.doUpload = function () {

    let uploaded_file_name = typeof FILE_NAME !== 'undefined' && FILE_NAME.length > 0 ? FILE_NAME : "pd_file";
    $("#progress-wrp").removeClass('hide');
    $("#progress-wrp").show();
    $("#up_div").addClass('pb-0');
    // $(".status").removeClass('hide');
    // $(".status").show();
    $("#btn_submit").removeClass('hide');
    $("#btn_submit").show();

    var that = this;
    var formData = new FormData();

    // add assoc key values, this will be posts values
    formData.append(uploaded_file_name, this.file, this.getName());
    //formData.append("skill_id", skill_id);
    formData.append("upload_file", true);

    if (typeof APPENDED_VALUES !== 'undefined' && APPENDED_VALUES.length > 0) {
        APPENDED_VALUES.forEach(element => {
            formData.append(element, element);
        });
    }


    $.ajax({
        type: "POST",
        url: upload_url,
        xhr: function () {
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) {
                myXhr.upload.addEventListener('progress', that.progressHandling, false);
            }
            return myXhr;
        },
        success: function (data) {
            // your callback here
            data = JSON.parse(data);
            setTableData(data);
        },
        error: function (error) {
            // handle error
        },
        async: true,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        timeout: 60000
    });
};

Upload.prototype.progressHandling = function (event) {
    var percent = 0;
    var position = event.loaded || event.position;
    var total = event.total;
    var progress_bar_id = "#progress-wrp";
    if (event.lengthComputable) {
        percent = Math.ceil(position / total * 100);
    }
    // update progressbars classes so it fits your code
    $(progress_bar_id + " .progress-bar").css("width", +percent + "%");
    $(progress_bar_id + " .status").text(percent + "%");
};


var uploadNumber = async (valid_head='', count=0, success_count=0) => {
    let fname = $("#fname").val();
    fname = fname != '' ? fname : FILE_NAME;

    let head_option = [];

    $('select').map(await function() {
        head_option.push( this.value);
    });

    $("#upload_div").hide();
    $("#btn_submit").hide();
    $("#count_div").removeClass('hide');
    $("#count_div").addClass('show');
    $("#count_msg").addClass('msg-box');

    $.ajax({
        dataType    :   "JSON",
        type        :   "POST",
        url         :   upload_number_url,
        data        :   {fname:fname, head_option:head_option, valid_head:valid_head, count:count, success_count:success_count},
        success : function (res) {
            if (typeof res!=='undefined' && res!=null && res.is_success === true ){
                is_uploaded = true;
                $("#count_msg").addClass('bg-success');
                $("#count_msg").html(res.success_count+" data have been uploaded.");
                uploadNumber(res.valid_head, res.count, res.success_count);
            }  else {
                $("#upload_img").remove();
                if (is_uploaded.length == 0) {
                    $("#count_msg").addClass('bg-error');
                }

                $("#count_msg").parent().closest('div').removeClass('col-md-11');

                if (typeof res!=='undefined' && res!=null && res.err_msg.length>0 ) {
                    $("#count_msg").html(res.err_msg);
                }
            }
        }
    });
};

$(document).on('change','.up', function(){
    customFileUploadDesign();
    //createHeaderSelectOption();
});