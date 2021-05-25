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
    $grid->multisearch=false;   

    $createUrl = $this->url("task=sticky-note&act=create-sticky-note");
    // $grid->AddTitleRightHtml('<a class="btn btn-xs btn-info" href="'." $createUrl ".'" ><i class="fa fa-plus"></i>Create</a>');

    $grid->AddModel("Id", "id", 80,"center");
    $grid->AddModel("Title", "title", 80,"center");
    $grid->AddModelNonSearchable("Status", "status", 80,"center");  
    $grid->AddModelNonSearchable("Created date", "created_at", 80,"center");  
    $grid->AddModelNonSearchable("", "action", 80,"center"); 
    
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
