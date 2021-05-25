    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Template Information</div>
                <div class="panel-body">
                    <?php if (!empty($errMsg)):?>
                        <div class="alert <?php echo $errType === 0 ? 'alert-success' : 'alert-error' ?>">
                            <?php echo $errMsg;?>
                        </div>
                    <?php endif;?>
                    <form method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>" class="form">
                        <div class="form-group form-group-sm">
                            <label for="template_id" class="control-label"> ID</label>
                            <input type="hidden" name="tid" value="<?php if (isset($tstamp_code)) echo $tstamp_code;?>" />
                            <input type="text" name="template_id" id="template_id" maxlength="10" class="form-control" value="<?php echo $service->template_id;?>">
                        </div>

                        <div class="form-group form-group-sm">
                            <label for="title" class="control-label"> Title</label>
                            <input type="text" name="title" maxlength="100" id="title" class="form-control" value="<?php echo $service->title;?>">
                        </div>

                        <div class="form-group">
                            <label for="sms_body" class="control-label"> Text</label>
                            <textarea  name="sms_body" id="sms_body" class="form-control" maxlength="512" rows="3"><?php echo $service->sms_body;?></textarea>
                        </div>

                        <div class="form-group form-group-sm">
                            <label for="status" class="control-label"> Template for</label>
                            <select name="type" id="type" class="form-control">
                                <option value="I" <?php echo $service->type == 'I' ?  'selected' : '' ?>>IVR</option>
                                <option value="C" <?php echo $service->type == 'C' ?  'selected' : '' ?>>Chat</option>
                            </select>
                        </div>

                        <div class="form-group form-group-sm">
                            <label for="status" class="control-label"> Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="Y" <?php echo $service->status == 'Y' ?  'selected' : '' ?>>Enable</option>
                                <option value="N" <?php echo $service->status == 'N' ?  'selected' : '' ?>>Disable</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success pull-right">Add</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
