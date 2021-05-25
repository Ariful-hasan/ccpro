<?php

    $action = "add-route&gw_id=".$mainobj->gw_id;

    $value = !empty($request['prefix']) ? $request['prefix'] : ( !empty($mainobj->prefix) ? $mainobj->prefix : "");

    $selected = !empty($request['status']) ? $request['status'] : ( !empty($mainobj->status) ? $mainobj->status : "");
?>

    <form  method="post" class="form" action="<?php echo $this->url('task=sms&act='.$action);?>">
        <div class="col-sm-12 col-md-12">

            <div class="form-group form-group-sm">
                <label for="prefix" class="control-label"> Prefix (* = common, ** = fallback)</label>
                <input type="text" name="prefix" maxlength="5" required id="prefix" class="form-control" value="<?php echo $value; ?>">
            </div>

            <div class="form-group form-group-sm">
                <label for="status" class="control-label"> Status</label>
                <select name="status" id="status" class="form-control" required>
                    <?php
                        GetHTMLOption('','---Select---',$selected);
                        GetHTMLOption('A','Active',$selected);
                        GetHTMLOption('I','Inactive',$selected);
                    ?>
                </select>
            </div>

            <div class="text-right">
                <button class=" btn btn-success" type="submit"> <i class="fa fa-save"></i> <?php echo !empty($mainobj->gw_id) ? 'Update' : 'Add'; ?> Route &nbsp;</button> &nbsp;
                <a class=" btn btn-danger" href="#" onclick="parent.$.colorbox.close();" > <i class="fa fa-times"></i> Close </a>
            </div>

        </div>

    </form>
