

<?php if (!empty($errMsg)):?>

    <div class="alert <?php echo ($errType === 0) ? 'alert-success' : 'alert-error' ?> ">

        <?php echo $errMsg;?>

    </div>

<?php endif;?>

<div class="col-md-12 col-sm-12">
    <div class="row">


        <form name="frm_pass_reset" method="post" class="small" action="<?php echo $this->url('task=agents&act=resetPassword&type='.$uType);?>">

            <?php if (!$resetted): ?>
                <input type="hidden" name="agentid" value="<?php echo $agentId;?>" />

                <div class="form-group form-group-sm">
                    <label for="" class="control-label"> Do you confirm that you want to reset the password of <?php echo $agentName; ?>?</label>
                    <div class="pull-right">
                        <button class="btn btn-success" type="submit" > <i class="fa fa-save"></i> Confirmed</button>
                        <button class="btn btn-danger" type="button" onclick="parent.$.colorbox.close();" > <i class="fa fa-times"></i> Cancel</button>
                    </div>
                </div>


            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered small">

                        <tr>
                            <th><?php echo $uType; ?> ID</th>
                            <td><?php echo $agentId; ?></td>
                        </tr>

                        <tr>
                            <th><?php echo $uType; ?> Name</th>
                            <td><?php echo $agentName; ?></td>
                        </tr>

                        <tr>
                            <th>New password</th>
                            <td><?php echo $curPass; ?></td>
                        </tr>

                    </table>
                </div>

                <button class="pull-right btn btn-danger" onclick="parent.$.colorbox.close();"> <i class="fa fa-times"></i> Close</button>

            <?php endif; ?>
        </form>

    </div>
</div>


