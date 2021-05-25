
<?php if (!empty($errMsg)):?>

    <div class="alert <?php echo ($errType === 0) ? 'alert-error' : 'alert-success' ?> ">

        <?php echo $errMsg;?>

    </div>

<?php endif;?>


<div class="col-md-12 col-sm-12">
    <div class="row">
        <form name="frm_add_black_list" method="post" action="<?php echo $this->url('task=forcast-rgb&act=add-forcast-rgb-data&list_type='.$list_type);?>" enctype="multipart/form-data">

            <div class="col-md-12 col-sm-12 col-lg-12">

                <div class="form-group form-group-sm">
                    <label for="acd_mode" class="control-label"> Upload File </label>
                    <input id="forcast_rgb_list" name="forcast_rgb_list" type="file" required/>
                </div>

                <div class="pull-right">
                    <button class=" btn btn-success" type="submit"> <i class="fa fa-save"></i> Add List </button> &nbsp;
                    <a class=" btn btn-danger" href="#" onclick="parent.$.colorbox.close();" > <i class="fa fa-times"></i> Close </a>
                </div>
            </div>
        </form>
    </div>

    <br/>

    <?php
    if (count($errData) > 0) { ?>
        <label>Faulty Data:</label>
        <table class="table table-bordered">
            <thead>
            <tr class="text-center" style=" background-color: #ff6039;">
                <td><b style="color:white">Date</b></td>
                <td><b style="color:white">Skill Name</b></td>
                <td><b style="color:white">Value</b></td>
                <td><b style="color:white">Error</b></td>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($errData as $error_data) {
                ?>
                <tr>
                    <td style="padding: 5px;">
                        <small><?php echo $error_data->date ?></small>
                    </td>
                    <td style="padding: 5px;">
                        <small><?php echo $error_data->skill_name ?></small>
                    </td>
                    <td style="padding: 5px;">
                        <small><?php echo $error_data->value ?></small>
                    </td>
                    <td style="padding: 5px;">
                        <small><?php echo $error_data->error ?></small>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php
    } ?>

</div>