
<?php
    $action_url = $this->url("task={$request->_controller_name}&act={$request->_action_name}");
    $selected = $request->getRequest('partition_id') ? $request->getRequest('partition_id') :
        !empty($mainobj['partition_id']) ? $mainobj['partition_id'] : "";
?>

<div class="panel panel-default">
    <div class="panel-heading">Choose Partition</div>
    <div class="panel-body">
        <form class="form" method="post" action="<?php echo $action_url; ?>">
            <div class="form-group">
                <?php foreach ($partitions as $id => $label) : ?>
                    <label class="radio-inline"><input type="radio" name="partition_id" value="<?php echo $id; ?>" <?php echo ($id == $selected || $id == "*") ? 'checked' :''; ?> ><?php echo $label; ?></label>
                <?php endforeach; ?>
            </div>
            <div class="col-md-12" style="margin-top: 20px">
                <button type="submit" class="btn btn-success pull-right"><i class="fa fa-save"></i> Change Partition</button>
            </div>
        </form>
    </div>
</div>
