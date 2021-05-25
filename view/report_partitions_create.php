<div class="col-sm-12 col-md-12">
    <?php if ($msg) : ?>
        <div class="alert <?php echo $msg_type ? 'alert-success' : 'alert-danger'; ?> text-center alert-dismissable" >
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <strong><?php echo $msg; ?></strong>
        </div>
    <?php endif; ?>

    <?php
        $action_url = empty($mainobj->partition_id) ? $this->url('task=report-partition&act=create') :
            $this->url('task=report-partition&act=update&id='.$mainobj->partition_id);
    ?>

    <div class="row">
        <div class="col-sm-12 col-md-12">
            <form class="form" method="post" action="<?php echo $action_url; ?>">
                <div class="form-group">
                    <label for="label" class="control-label">Partition Label</label>
                    <input type="text" name="label" maxlength="20" class="form-control" value="<?php echo $request->label ? $request->label : !empty($mainobj->label) ? $mainobj->label : ''; ?>">
                </div>
                <button type="submit" class="btn btn-success pull-right"><i class="fa fa-save"></i><?php echo empty($mainobj) ? ' Create' : ' Update'; ?></button>
            </form>
        </div>
    </div>
</div>