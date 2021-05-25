function Initialize_Summer_note(elem, omited_tools, img_up_url, img_del_url, attachment_path='') {
    $(elem).summernote({
        height: 200, // set editor height
        minHeight: null, // set minimum height of editor
        maxHeight: null, // set maximum height of editor
        focus: true,
        callbacks: {
            onImageUpload: function (files) {
                //INSTA.App.blockUI({animate: true});
                var data = new FormData();
                var summernote = this;
                data.append("file", files[0]);
                data.append("attachment_save_path", attachment_path);
                $.ajax({
                    data: data,
                    type: "POST",
                    url: img_up_url,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(data) {
                        if(data.result==true){
                            var image = $('<img>').attr('src', data.url).attr('style','width:100%').attr('alt', data.name);
                            $(elem).summernote("insertNode", image[0]);
                        }else{
                            if (data.msg != '') alert(data.msg);
                        }
                    }
                });
            },
            onMediaDelete : function(target) {
                var img_src = target[0].src;
                $.ajax({
                    data: {img_src: img_src},
                    type: "POST",
                    url:img_del_url,
                    cache: false,
                    //contentType: false,
                    //processData: false,
                    dataType: 'json',
                    success: function(data) {
                        if(data!==true){
                            alert('Not Delete From Server.');
                        }
                    }
                });
            }

        },
        toolbar: CustomToolBar(omited_tools)
    });

    $('.summernote-area .note-codable').unbind('keyup');
    $('.summernote-area .note-codable').keyup(function(){
        $(this).closest('.note-editing-area').find('.note-editable').html(this.value);
        $(this).closest('.summernote-area').find('.summernote-mini').html(this.value);
        //$('textarea#message').val(this.value);
        $(elem).val(this.value);/////22-07-18///////
    });

    //////22/07/18//////
    // var font_falg_html = '';
    // font_falg_html +='<div class="note-btn-group btn-group note-codeview" style="background-color: #eaeaea;margin-top: 5px; border-radius: 2px;"><div class="checkbox-inline language-check temp-sm-btn" style="margin-top: 4px;margin-left: -12px;margin-right: -7px;margin-bottom: -4px"><label class="radio-container" style="margin-top: 2px;">Bangla';
    // font_falg_html +='<input id="language_bn" name="language" type="radio" value="bn" onclick="setBangla()">';
    // font_falg_html +='<span class="radio-checkmark"></span></label>';
    // font_falg_html +='<label class="radio-container" style="margin-top: 2px;">English';
    // font_falg_html +='<input id="language_en" name="language" type="radio" value="en" onclick="setEnglish()"  checked="checked">';
    // font_falg_html +='<span class="radio-checkmark"></span></label></div></div>';
    // $('.note-toolbar').append(font_falg_html);

    //////22/07/18//////
}
//////22/07/18//////
function setEnglish(){
    $('.summernote-area .note-codable').unbind();
    $('.note-editable').unbind();
}
// function setBangla(){
//     $('.summernote-area .note-codable').bnKb({
//         'switchkey': {"webkit":"k","mozilla":"y","safari":"k","chrome":"k","msie":"y"},
//         'driver': phonetic,
//         'writingMode': 'b'
//     });
//     $('.note-editable').bnKb({
//         'switchkey': {"webkit":"k","mozilla":"y","safari":"k","chrome":"k","msie":"y"},
//         'driver': phonetic,
//         'writingMode': 'b'
//     });
// }
//////22/07/18//////

function CustomToolBar (omittedToolBarItemArr) {
    var tools = FullToolBar();
    var customTool = " [ ";
    if (omittedToolBarItemArr.length != ''){
        $.each(omittedToolBarItemArr, function (index, value) {
            delete tools[value];
        })
    }
    if (tools.length != ''){
        $.each(tools, function (index, value) {
            customTool += value+",";
        })
    }
    customTool = customTool.slice(0,-1);
    customTool += " ]";
    return JSON.parse(customTool);
}

function FullToolBar() {
    var toolbar = {
        style: '["style", ["style"]]',
        font: '["font", ["bold", "underline", "clear"]]',
        fontname: '["fontname", ["fontname"]]',
        color: '["color", ["color"]]',
        para: '["para", ["ul", "ol", "paragraph"]]',
        table: '["table", ["table"]]',
        insert: '["insert", ["link", "picture", "video"]]',
        view: '["view", ["fullscreen", "codeview", "help"]]',
        height: '["height", ["height"]]',
    };
    return toolbar;
}

// function set_language_bar_in_summernote() {
//     var font_falg_html = '';
//     font_falg_html +='<div class="note-btn-group btn-group note-codeview" style="background-color: #eaeaea;margin-top: 5px; border-radius: 2px;"><div class="checkbox-inline language-check temp-sm-btn" style="margin-top: 4px;margin-left: -12px;margin-right: -7px;margin-bottom: -4px"><label class="radio-container" style="margin-top: 2px;">Bangla';
//     font_falg_html +='<input id="language_bn" name="language" type="radio" value="bn" onclick="setBangla()">';
//     font_falg_html +='<span class="radio-checkmark"></span></label>';
//     font_falg_html +='<label class="radio-container" style="margin-top: 2px;">English';
//     font_falg_html +='<input id="language_en" name="language" type="radio" value="en" onclick="setEnglish()"  checked="checked">';
//     font_falg_html +='<span class="radio-checkmark"></span></label></div></div>';
//     $('.note-toolbar').append(font_falg_html);
// }

