<div class="col-md-6 col-sm-6 col-lg-6">
    <form action="<?php echo $this->url("task=scrmdc&act=change-partition&tid={$request->getRequest('tid')}&dcode={$request->getRequest('dcode')}"); ?>" method="post" class="disposition-partition-change">
        <div class="form-group">
            <label for="partition-id" class="control-label">Partition</label>
            <select name="partition_id" id="partition-id" class="form-control">
              <?php if (!empty($partitions)) : ?>
                  <?php foreach ($partitions as $partition_key => $partition_title): ?>
                      <option value="<?php echo $partition_key; ?>" <?php echo $mainobj->partition_id == $partition_key ? 'selected' : ''; ?> > <?php echo $partition_title; ?></option>
                  <?php endforeach;  ?>
              <?php endif; ?>
            </select>
        </div>

        <div class="text-right">
            <button type="submit" class="btn btn-primary "><i class="fa fa-save"></i> Change</button>
        </div>
    </form>
</div>