<div class="col-md-6 col-sm-6 col-lg-6">
    <form action="<?php echo $this->url("task=scrmdc&act=change-status&tid={$request->getRequest('tid')}&dcode={$request->getRequest('dcode')}"); ?>" method="post" class="disposition-partition-change">
        <div class="form-group">
            <label for="status-id" class="control-label">Status</label>
            <select name="status" id="status-id" class="form-control">
                <option value="Y" <?php echo $mainobj->status == 'Y'? 'selected' : ''; ?> > Active</option>
                <option value="N" <?php echo $mainobj->status == 'N'? 'selected' : ''; ?> > Inactive</option>
            </select>
        </div>

        <div class="text-right">
            <button type="submit" class="btn btn-primary "><i class="fa fa-save"></i> Change</button>
        </div>
    </form>
</div>