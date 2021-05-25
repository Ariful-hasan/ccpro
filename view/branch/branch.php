<script type="text/javascript" src="js/bootbox/bootbox.min.js"></script>
<script type="text/javascript" src="js/toastr/toastr.min.js"></script>

<?php

include_once "lib/jqgrid.php";
$grid = new jQGrid();
// $grid->caption = "Sticky Notes";
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";
$grid->height = "auto";
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->hidecaption=false;
$grid->CustomSearchOnTopGrid=true;

$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
$grid->DownloadFileName = $pageTitle;

$grid->ShowDownloadButtonInBottom=false;
$grid->multisearch=true;

$createUrl = $this->url("task=branch&act=create");
// $grid->AddTitleRightHtml('<a class="btn btn-xs btn-info" href="'." $createUrl ".'" ><i class="fa fa-plus"></i>Create</a>');
$grid->AddModel("Created date & Time", "created_at", 80, "center", ".date", "Y-m-d H:i:s", "Y-m-d H:i:s", true, "datetime");
$grid->SetDefaultValue("created_at", date("Y-m-d", strtotime("-1 week")), date("Y-m-d 23:59"));
$grid->AddModel("Branch Name", "branch_name", 50,"center");
$grid->AddModel("Branch Code", "branch_code", 50,"center");
//$grid->AddModelNonSearchable("Notes", "note", 150,"center");
//$grid->AddModelNonSearchable("Status", "status", 30,"center");
$grid->AddModelNonSearchable("Created By", "created_by", 80,"center");
$grid->AddModelNonSearchable("Updated By", "updated_by", 80,"center");
$grid->AddModelNonSearchable("", "action", 40,"center");

?>

<?php $grid->show("#searchBtn"); ?>

<script type="text/javascript">
    $(function(){
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
    });
    function confirm_status(event){
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
                if(result){
                    $.ajax({
                        type:"POST",
                        url:href,
                        dataType:"text",
                        success:function(resp) {
                            console.log(resp);
                            var data = JSON.parse(resp);

                            if(data.result){
                                toastr.success(data.message);
                                ReloadAll();
                            }else{
                                toastr.error(data.message);
                            }
                        }
                    });
                }
            }
        });
    };
</script>
