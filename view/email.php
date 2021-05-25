<link rel="stylesheet" href="js/toastr/toastr.min.css">
<script src="js/toastr/toastr.min.js"></script>
<script src="assets/plugins/floating-scrollbar/jquery.floatingscroll.js"></script>
<?php
include_once "lib/jqgrid.php";
$grid = new jQGrid();
//$grid->caption = "Agent List";
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 200;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
//$grid->hidecaption=true;
if ($show_inbox_color == "Y"){
    $grid->afterInsertRow="AfterInsertRow";
}
$grid->CustomSearchOnTopGrid=true;
$grid->multisearch=true;
$grid->multiselect = true;
$grid->ShowReloadButtonInTitle=true;
$grid->shrinkToFit=false;
$grid->searchID="src_all_emails";
$grid->resetsrcID="rst_all_emails";
$grid->floatingScrollBar=true;

$grid->AddTitleRightHtml('<button class="btn btn-xs btn-primary dd-btn" >Mark as Closed</button>');
$grid->AddHiddenProperty("is_inbox");
//$grid->colModel['name']="comments";

$grid->ShowDownloadButtonInTitle=true;
$grid->CookieArray = 'create_time,ticket_id,customer_id,last_name,created_for,subject,did,phone,skill_name,mail_to';

$grid->AddModel("Arrival DateTime", "create_time", 130, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
$grid->SetDefaultValue("create_time", $_COOKIE['crt_sdate'], $_COOKIE['crt_edate']);

$grid->AddModel("Email Ref#", "ticket_id", 100, "center");
$grid->SetDefaultValue("ticket_id", $_COOKIE['ticket_id']);

$grid->AddModel("Email To", "mail_to", 160,"center");
$grid->SetDefaultValue("mail_to", $_COOKIE['mail_to']);

$grid->AddModel("Customer Name", "last_name", 100,"center");
$grid->SetDefaultValue("last_name", $_COOKIE['last_name']);

$grid->AddModel("Email Address", "created_for", 150,"center");
$grid->SetDefaultValue("created_for", $_COOKIE['created_for']);

$grid->AddModel("Subject", "subject", 160,"center");
$grid->SetDefaultValue("subject", $_COOKIE['subject']);


//$grid->AddSearhProperty("Email", "email");
//$grid->AddSearhProperty("Number", "created_for");
$grid->AddSearhProperty("Disposition", "did", "select", $did_options);
$grid->SetDefaultValue("did", $_COOKIE['did']);

$grid->AddSearhProperty("Status", "status", "select", $status_options);
$grid->SetDefaultValue("status", $_COOKIE['status']);

$grid->AddSearhProperty("Phone", "phone");
$grid->SetDefaultValue("phone", $_COOKIE['phone']);

$grid->AddModelCustomSearchable("Skill", "skill_name", 100, "center",'select', $skill_list);
$grid->SetDefaultValue("skill_name", $_COOKIE['skill_name']);

if (isset($todaysDate) && !empty($todaysDate)){
    //$grid->SetDefaultValue("create_time", date("Y-m-d"), $todaysDate);
}
$grid->AddModel("Last Update Time", "last_update_time", 130, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
$grid->SetDefaultValue("last_update_time", $_COOKIE['lut_sdate'], $_COOKIE['lut_edate']);
//$grid->SetDefaultValue("last_update_time",date("Y-m-d H:i", strtotime("-2 month")), date("Y-m-d H:i"));


$grid->AddModelNonSearchable("Disposition", "disposition_id", 100,"center");
$grid->AddModelNonSearchable("Total Count", "num_mails", 100,"center");
$grid->AddModelNonSearchable("Assigned", "assigned_to", 80,"center");

$grid->AddModelNonSearchable(" Status", "emailStatus", 100,"center");
$grid->AddModelNonSearchable("Last Update", "last_agent", 100,"center");


if (empty($callid)) {
    //$grid->AddModelNonSearchable("Details", "detailsUrl", 100,"center");
}

$grid->AddModelNonSearchable("Incoming Count", "email_count", 100,"center");
$grid->AddDownloadHiddenProperty('Comments');

$grid->AddModel("Customer ID", "customer_id", 100, "center");
$grid->SetDefaultValue("customer_id", $_COOKIE['customer_id']);

$grid->AddModelHidden("fetch_box_email");

$grid->show("#searchBtn");
//$grid->ShowDownloadButtonInBottom=true;


?>


<script type="text/javascript">
    window.onerror = function (msg, url, line, column, error) {
        $.ajax({
            url: "<?php echo site_url().$this->url('task=email&act=log-javascript-error'); ?>",
            type: "post",
            dataType: 'json',
            data: {msg: msg, url: url, line: line, column: column, error: error},
            success: function (result) {
            }
        });
    };

$(function(){
	AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
});


var top_menu_ids = '<?php echo json_encode($menu_ids)?>';
var menu_emails = '<?php echo json_encode($menu_emails)?>';
var parsed_menu_ids = JSON.parse(top_menu_ids);
var lut_info = {sdate:$('input[name="ms[last_update_time][from]"]').val(), edate:$('input[name="ms[last_update_time][to]"]').val()};
var email_row_color = <?php echo json_encode($email_row_color)?>;
var skill_list = <?php echo json_encode($skill_list)?>;
var current_table_data_count = 0;
var previous_table_data_count = 0;
var unmatch_count = 0;


function AfterInsertRow(rowid, rowData, rowelem) {
    if(rowData.is_inbox=="Y"){
        if (typeof email_row_color[rowData.fetch_box_email] !== "undefined" && email_row_color[rowData.fetch_box_email].length != ""){
            $('tr#' + rowid).addClass(email_row_color[rowData.fetch_box_email]);
        }else {
            $('tr#' + rowid).addClass('email_inbox');
        }
    }
}

$(document).ready(function() {

    $(document).ajaxComplete(function( event, request, settings ) {
        setTimeout('', 3000);
        if (request.status != '200')
            location.reload();
    });

    $("#src_all_emails").on('click', function () {
        var crt_sdate = $('input[name="ms[create_time][from]"]').val();
        setCookie("crt_sdate", crt_sdate);
        var crt_edate = $('input[name="ms[create_time][to]"]').val();
        setCookie("crt_edate", crt_edate);

        var customer_id = $("#customer_id").val();
        setCookie("customer_id", customer_id);

        var lut_sdate = $('input[name="ms[last_update_time][from]"]').val();
        setCookie("lut_sdate", lut_sdate);
        var lut_edate =$('input[name="ms[last_update_time][to]"]').val();
        setCookie("lut_edate", lut_edate);

        var created_for = $("#created_for").val();
        setCookie("created_for", created_for);

        var did = $("#did").val();
        setCookie("did", did);

        var phone = $("#phone").val();
        setCookie("phone", phone);

        var ticket_id = $("#ticket_id").val();
        setCookie("ticket_id", ticket_id);

        var last_name = $("#last_name").val();
        setCookie("last_name", last_name);

        var subject = $("#subject").val();
        setCookie("subject", subject);

        var status =  $("#status").val();
        setCookie("status", status);

        var skill_name = $("#skill_name").val();
        setCookie("skill_name", skill_name);

        var mail_to = $("#mail_to").val();
        setCookie("mail_to", mail_to);
    });
    
    $("#rst_all_emails").on('click', function () {
        $('input[name="ms[create_time][from]"]').val('');
        $('input[name="ms[create_time][to]"]').val('');
        $("#customer_id").val('');
        $("#created_for").val('');
        $("#did").val('');
        $("#phone").val('');
        $("#ticket_id").val('');
        $("#last_name").val('');
        $("#subject").val('');
        $("#skill_name").val('');
        $("#mail_to").val('');
        $('input[name="ms[last_update_time][from]"]').val("<?php echo date('Y-m-d H:i', strtotime('-2 month'))?>");
        $('input[name="ms[last_update_time][to]"]').val("<?php echo date('Y-m-d H:i')?>");

        setCookie("crt_sdate", '');
        setCookie("crt_edate", '');
        setCookie("customer_id", '');
        setCookie("lut_sdate", "<?php echo date('Y-m-d H:i', strtotime('-2 month'))?>");
        setCookie("lut_edate", "<?php echo date('Y-m-d H:i')?>");
        setCookie("created_for", '');
        setCookie("did", '');
        setCookie("phone", '');
        setCookie("ticket_id", '');
        setCookie("last_name", '');
        setCookie("subject", '');
        setCookie("status", 'O');
        setCookie("skill_name", '');
        setCookie("mail_to", '');
    });
    lut_info = {sdate:$('input[name="ms[last_update_time][from]"]').val(), edate:$('input[name="ms[last_update_time][to]"]').val()};

    $("body").on("click",".dd-btn",function(e){
        e.preventDefault();
        var gridId="<?php echo $grid->GetGridId();?>";
        var s= jQuery(gridId).jqGrid('getGridParam','selarrrow');
        s+="";
        var row_ids = s.split(',');
        var data = [];
        if (typeof row_ids !== 'undefined' && row_ids.length > 0){
            var column_name = gridId+"_ticket_id";
            column_name = column_name.replace('#', '');
            $.each(row_ids, function (i , j) {
                var ticket_id = $(gridId+" #"+j).find("[aria-describedby='"+column_name+"']").html();
                data.push(ticket_id);
            });
            //console.log(JSON.stringify(data));
            if (typeof data !== 'undefined' && data.length > 0){
                if (confirm("Are you sure that you want to close this emails?")) {

                } else {
                    return;
                }
                $.ajax({
                    dataType    :   "JSON",
                    type        :   "POST",
                    url         :   "<?php echo site_url().$this->url('task=email-confirm-response&act=mark-as-closed&pro=update') ?>",
                    data        :   {tids: JSON.stringify(data)},
                    success     : function (res) {
                        if (typeof res !== 'undefined' && res !== null && res.status == true) {
                            toastr.success(res.msg);
                            $('#src_all_emails').trigger('click');
                        }else {
                            toastr.success("Failed to Update");
                        }
                    }
                });
            }
        }
    });

    autoRefreshList();
    updateTopMenuCount();

    //autoRefreshTopMenu();
});
function autoRefreshList() {
    setInterval(function () {
        $('#src_all_emails').trigger('click');
        reloadPage();
        updateTopMenuCount();
    },30000);
}

let reloadPage = () => {
    current_table_data_count = $('table tbody tr').length;
    previous_table_data_count = previous_table_data_count == 0 ? current_table_data_count : previous_table_data_count;
    if (current_table_data_count == previous_table_data_count && unmatch_count == 3) {
        location.reload();
    } else {
        unmatch_count++;
        previous_table_data_count = current_table_data_count;
    }
};


let updatePriorityEmail = () => {
    if ($("#is_priority").length > 0) {
        $.ajax({
            dataType :   "JSON",
            type     :   "POST",
            url      :   "<?php echo site_url().$this->url('task=email&act=get-priority-topmenu-count') ?>",
            data     :   {skill_list: skill_list, last_update_info: lut_info},
            success  :   function (res) {
                if (typeof res !== 'undefined' && res !== null && res.length !== 0) {
                    var html = $("#is_priority").html();
                    if (html !== 'undefined') {
                        var start_pos = html.lastIndexOf("(");
                        var replace_text = start_pos > 0 ? html.substring(start_pos, html.length) : "";
                        html = html.replace(replace_text,"")+" ("+res+")";
                        $("#is_priority").html(html);
                    }
                } else {
                    var html = $("#is_priority").html();
                    if (html !== 'undefined') {
                        var start_pos = html.lastIndexOf("(");
                        var replace_text = start_pos > 0 ? html.substring(start_pos, html.length) : "";
                        html = html.replace(replace_text,"")+" (0)";
                        $("#is_priority").html(html);
                    }
                }
            }
        });
    }
};


function updateTopMenuCount() {
    if (typeof parsed_menu_ids !== 'undefined' && Object.keys(parsed_menu_ids).length > 0){
        $.ajax({
            dataType    :   "JSON",
            type        :   "POST",
            url         :   "<?php echo site_url().$this->url('task=email&act=get-topmenu-count') ?>",
            data        :   {email: menu_emails, last_update_info: lut_info},
            success     :   function (res) {
                if (typeof res !== 'undefined' && res !== null && res.length !== 0) {
                    $.each(parsed_menu_ids, function (idx, val){
                        if (typeof res[val] !== 'undefined'){
                            var html = $("#"+idx).html();
                            if (html !== 'undefined') {
                                var start_pos = html.lastIndexOf("(");
                                var replace_text = start_pos > 0 ? html.substring(start_pos, html.length) : "";
                                html = html.replace(replace_text,"")+" ("+res[val]+")";
                                $("#"+idx).html(html);
                            }
                        } else {
                            var html = $("#"+idx).html();
                            if (html !== 'undefined') {
                                var start_pos = html.lastIndexOf("(");
                                var replace_text = start_pos > 0 ? html.substring(start_pos, html.length) : "";
                                html = html.replace(replace_text,"")+" (0)";
                                $("#"+idx).html(html);
                            }
                        }
                    })
                } else {
                    $.each(parsed_menu_ids, function (idx, val) {
                        var html = $("#"+idx).html();
                        if (html !== 'undefined') {
                            var start_pos = html.lastIndexOf("(");
                            var replace_text = start_pos > 0 ? html.substring(start_pos, html.length) : "";
                            html = html.replace(replace_text,"")+" (0)";
                            $("#"+idx).html(html);
                        }
                    })
                }
            }
        });
    }
    updatePriorityEmail();
}
</script>