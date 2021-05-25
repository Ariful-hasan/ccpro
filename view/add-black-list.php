
<?php if (!empty($errMsg)):?>

    <div class="alert <?php echo ($errType === 0) ? 'alert-error' : 'alert-success' ?> ">

        <?php echo $errMsg;?>

    </div>

<?php endif;?>


<div class="col-md-12 col-sm-12">
    <div class="row">
        <form name="frm_add_black_list" method="post" action="<?php echo $this->url('task=black-white&act=add-black-list');?>" enctype="multipart/form-data">

            <div class="col-md-12 col-sm-12 col-lg-12">

                <div class="form-group form-group-sm">
                    <label for="acd_mode" class="control-label"> Upload File </label>

                </div>
                <input id="black_list" name="black_list" type="file" required/>

                <div class="pull-right">
                    <button class=" btn btn-success" type="submit"> <i class="fa fa-save"></i> Add List </button> &nbsp;
                    <a class=" btn btn-danger" href="#" onclick="parent.$.colorbox.close();" > <i class="fa fa-times"></i> Close </a>
                </div>
            </div>
        </form>
    </div>

    <br/>
    <?php
    if (count($errData) > 0 || count($duplicateData) > 0) { ?>
        <table class="table table-bordered">
            <thead>
            <tr class="text-center">
                <td style="padding: 5px; background-color: #ff6039">
                    <b style="color:white">Faulty Data</b>
                </td>
                <td style="padding: 5px; background-color: #ffa418">
                    <b style="color:white ">Removed Duplicate Data</b>
                </td>
            </tr>
            </thead>
            <tbody>
            <?php
            $limit = max(count($duplicateData), count($errData));
            $counter = 0;
            while ($counter < $limit) {
                ?>
                <tr class="text-center text-sm">
                    <td style="padding: 5px;">
                        <small><?php echo $counter < count($errData) ? $errData[$counter] : "" ?></small>
                    </td>
                    <td style="padding: 5px;">
                        <small><?php echo $counter < count($duplicateData) ? $duplicateData[$counter] : "" ?></small>
                    </td>
                </tr>
                <?php
                $counter++;
            }
            ?>
            </tbody>
        </table>
        <?php
    } ?>

</div>