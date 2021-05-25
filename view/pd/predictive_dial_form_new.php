<!--<link rel="stylesheet" type="text/css" media="screen" href="assets/css/AdminLTE.min.css">-->
<link rel="stylesheet" href="assets/css/predictive-dial.css">
<link rel="stylesheet" href="assets/css/custom-checkbox.css">
<link rel="stylesheet" href="assets/css/custom-fileupload.css">
<script defer src="assets/js/custom-fileupload.js"></script>

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>


    <div class="panel panel-default">
        <div class="panel-body">
            <form name="frm_trunk" enctype="multipart/form-data" method="post" action="<?php echo $this->url('task=' . $request->getControllerName() . "&act=" . $this->getActionName() . "&sid=" . $request->getRequest('sid') . "&sname=" . $request->getRequest('sname')); ?>">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="col-md-4" id="upload_div">
                                    <div class="panle panel-info common-bdr">
                                        <div class="panel-heading">Upload File</div>
                                        <div class="panel-body" id="up_div">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" readonly>
                                                    <div class="input-group-btn">
                                              <span class="fileUpload btn btn-success">
                                                  <span class="upl" id="upload">Upload</span>
                                                  <input type="file" class="upload up" id="up" onchange="readFile();" accept=".csv" />
                                                </span>
                                                    </div>
                                                </div>
                                                <div id="progress-wrp" class="hide">
                                                    <div class="progress-bar" role="progressbar"></div>
                                                    <!-- <div class="status hide">0%</div>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="panle panel-info common-bdr">
                                        <div class="panel-heading">Delete</div>
                                        <div class="panel-body">
                                            <div class="inputGroup">
                                                <input id="is_delete" name="is_delete" type="checkbox"/>
                                                <label for="is_delete">Delete Previous Numbers</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button id="btn_submit" class="button hide" type="submit"><span>Submit </span></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 hide mt-15" id="tbl_panel">
                        <div class="panel panel-info">
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-condensed table-bordered" id="frm_tab">
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="fname" name="fname">
            </form>
        </div>
    </div>

    <script>
        var upload_div = $("#upload_div");
        var file = undefined;
        const urlParams = new URLSearchParams(window.location.search);
        const skill_id = urlParams.get('sid');
        var tbl = $("#frm_tab");
        var head_data = <?php echo json_encode($heading)?>;
        var select_option = '<option value="">select</option>';
        const validate_ary = [];
        var previous_selected_value = '';
        var bind_ids = [];
        var upload_url = "<?php echo site_url().$this->url("task=" . $request->getControllerName() . '&act=file-upload'); ?>";

        $(document).on('change','.up', function(){
            customFileUploadDesign();
            createHeaderSelectOption();
        });
    </script>