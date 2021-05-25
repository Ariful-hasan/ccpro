






    <link href="css/form.css" rel="stylesheet" type="text/css">
    <?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

        <form name="frm_disposition" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
            <input type="hidden" name="sid" value="<?php if (isset($dis_code)) echo $dis_code;?>" />

            <div class="panel panel-default">
                <div class="panel-heading">Disposition Code Information</div>
                <div class="panel-body">
                    <div class="form-group form-group-sm">
                        <label for="campaign_id">Campaign:</label>
                        <div class="">
                            <?php if (empty($dis_code)):?>
                                <select name="campaign_id" id="campaign_id" class="form-control">
                                    <?php
                                    if (is_array($campaign_options)) {
                                        foreach ($campaign_options as $key=>$val) {
                                            echo '<option value="'.$key.'"';
                                            if ($key == $service->campaign_id) echo ' selected';
                                            echo '>'.$val.'</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            <?php else :
                                if (isset($campaign_options[$service->campaign_id])) echo $campaign_options[$service->campaign_id];
                                else echo $service->campaign_id; ?>
                                <input type="hidden" name="campaign_id" value="<?php echo $service->campaign_id;?>"  />
                            <?php endif;?>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <label for="title" class="control-label">Disposition Title:</label>
                        <div class="">
                            <input type="text" name="title" class="form-control" maxlength="20" value="<?php echo $service->title;?>" />
                        </div>
                    </div>

                    <div class="pull-right">
                        <input class="form_submit_button " type="submit" value="  <?php if (!empty($dis_code)):?>Update<?php else:?>Add<?php endif;?>  " name="submitservice"> <br><br>
                    </div>
                </div>
            </div>
        </form>

