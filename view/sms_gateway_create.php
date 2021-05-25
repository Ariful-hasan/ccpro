<?php

    $action = $isUpdate ? "update-api&gw_id=".$mainobj->gw_id : "add-api";

    $value = !empty($request['api']) ? $request['api'] : $mainobj->api;

?>

    <form  method="post" class="form" action="<?php echo $this->url('task=sms&act='.$action);?>">
        <div class="form-group form-group-sm">
            <label for="gw_id" class="control-label"> Prefix</label>
            <select name="gw_id" <?php echo $isUpdate ? 'readonly' : ''; ?> id="gw_id" class="form-control">
                <?php
                    GetHTMLOption("","---Select---",$mainobj->gw_id);
                    foreach ($gateways as $gateway_id => $api)
                    {
                        GetHTMLOption($gateway_id,$api,$mainobj->gw_id);
                    }

                ?>
            </select>
        </div>

        <div class="form-group form-group-sm">
            <label for="api" class="control-label"> Api</label>
            <input type="text" name="api" maxlength="255" required id="api" class="form-control" value="<?php echo $value; ?>">
        </div>

        <div class="form-group form-group-sm">
            <label for="status" class="control-label"> Status</label>
            <select name="status"  id="status" class="form-control">
                <?php
                    GetHTMLOption("","---Select---",$mainobj->gw_id);
                    GetHTMLOption("A","Active",$mainobj->status);
                    GetHTMLOption("I","Inactive",$mainobj->status);
                ?>
            </select>
        </div>

        <div class="text-right">
            <button class=" btn btn-success" type="submit"> <i class="fa fa-save"></i> <?php echo !empty($mainobj->gw_id) ? 'Update' : 'Add'; ?> API &nbsp;</button> &nbsp;
            <a class=" btn btn-danger" href="#" onclick="parent.$.colorbox.close();" > <i class="fa fa-times"></i> Close </a>
        </div>
    </form>
