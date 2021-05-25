<script type="text/javascript" src="js/bootbox/bootbox.min.js"></script>
<script type="text/javascript" src="js/toastr/toastr.min.js"></script>

<?php
include_once "lib/jqgrid.php";
$grid = new jQGrid();
//$grid->caption = "Agent List";
$grid->url = isset($dataUrl) ? $dataUrl : "";
$grid->width="auto";//$grid->minWidth = 800;
$grid->height = "auto";//390;
$grid->rowNum = 20;
$grid->pager = "#pagerb";
$grid->container = ".content-body";
$grid->hidecaption=false;
$grid->CustomSearchOnTopGrid=false;
$grid->afterInsertRow="AfterInsertRow";
$grid->multisearch=false;
$grid->ShowReloadButtonInTitle=true;
$grid->ShowDownloadButtonInTitle=true;
//$grid->AddHiddenProperty("usertypemain");

$grid->AddModelNonSearchable("Title", "dispTitle", 180, "left");
$grid->AddModelNonSearchable("Type", "disposition_type", 50, "center");
$grid->AddModelNonSearchable("Disposition Code", "disposition_id", 80, "center");
$grid->AddModelNonSearchable("Status", "status", 80,"center");
$grid->AddModelNonSearchable("Action", "actUrl", 80,"center");
$grid->show("#searchBtn");
$grid->AddHiddenProperty("usertypemain");
include('view/grid-tool-tips.php');
$grid->addModelTooltips($tooltips);
?>
<script type="text/javascript">
    $(function(){
        AddOnCloseMethod(<?php echo $grid->ReloadMethod();?>);
        $(document).on('click', '#cboxClose', function () {
            $("#refresh_<?php echo $grid->GetGridId(true)?>").trigger('click');
        });

    });

    function AfterInsertRow(rowid, rowData, rowelem) {
        if(rowData.usertypemain=="S"){
            $('tr#' + rowid).addClass('bg-supervisor');
        }
    }

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
