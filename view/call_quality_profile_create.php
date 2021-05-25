
<?php $action_url = $this->url("task={$request->_controller_name}&act={$request->_action_name}&id={$request->getRequest('id')}"); ?>

<div class="panel panel-default">
    <div class="panel-heading">Profile Information</div>
    <div class="panel-body">
        <form class="form" method="post" action="<?php echo $action_url; ?>">
            <div class="form-group">
                <label for="label" class="control-label">Title</label>
                <input type="text" name="label" id="label" class="form-control" value="<?php echo !empty($mainobj->label) ? $mainobj->label : ""; ?>">
            </div>
            <div class="form-group">
                <label for="label" class="control-label">Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="" <?php echo $mainobj->status == "" ? 'selected' : ''; ?> >---Select---</option>
                    <option value="Y" <?php echo $mainobj->status == "Y" ? 'selected' : ''; ?> >Active</option>
                    <option value="N" <?php echo $mainobj->status == "N" ? 'selected' : ''; ?> >Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success pull-right"><i class="fa fa-save"></i> Add</button>
        </form>
    </div>
</div>
