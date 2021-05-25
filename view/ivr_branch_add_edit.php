<div class="col-sm-12 col-md-12">
    <?php /*if ($msg) : ?>
        <div class="alert <?php echo $msg_type ? 'alert-success' : 'alert-danger'; ?> text-center alert-dismissable" >
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <strong><?php echo $msg; ?></strong>
        </div>
    <?php endif;  */ ?>

    <?php
    $action_url =  empty($mainobj->title_id) ? $this->url('task=ivr-branch&act=create') :
            $this->url('task=ivr-branch&act=update&title_id='.$mainobj->title_id);
    ?>

    <div class="row">
        <div class="col-sm-12 col-md-12">
            <form class="form" method="post" action="<?php echo $action_url; ?>">
                <div class="form-group">
                    <label for="title" class="control-label">IVR Branch Title</label>
                    <input type="text" name="title" maxlength="50" class="form-control" value="<?php echo !empty($request->title) ? $request->title : !empty($mainobj->title) ? $mainobj->title : ''; ?>">
                </div>
                <button type="submit" class="btn btn-success pull-right"><i class="fa fa-save"></i><?php echo empty($mainobj) ? ' Create' : ' Update'; ?></button>
            </form>
        </div>
    </div>
</div>