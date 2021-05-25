<style>
    .submit_div{
        padding: 10px;
    }
    .cbox{
        padding: 10px;
    }
</style>
<?php if (!empty($errMsg)):?>

    <div class="alert <?php echo ($errType === 0) ? 'alert-error' : 'alert-success' ?> ">

        <?php echo $errMsg;?>

    </div>

<?php endif;?>

<div class="col-md-12 col-sm-12">
    <div class="row">
        <form name="frm_add_black_list" method="post" action="<?php echo $this->url('task=ev-portal-access&act=update-access&agent_id=' . $agent_id);?>" enctype="multipart/form-data">

            <div class="col-md-12 col-sm-12 col-lg-12">
                <input type="hidden" name="agent_id" value="<?php echo $agent_id; ?>" />
                <label>Agent : </label> <?php echo $agent_id; ?>
                <br />
                <label>Privileges: </label>
                <br />
                <table class="table table-condensed table-responsive table-bordered">
                    <?php
                    $counter = 0;
                    foreach ($privileges as $privilege) {
                        if (($counter % 2) == 0) {
                            ?>
                            <tr>
                            <td>
                            <span class="button-checkbox">
                                <button type="button" class="btn" data-color="primary"><?php echo $privilege->privilege_name; ?></button>
                                <input class="hidden" type="checkbox" name="privileges[]"
                                       value="<?php echo $privilege->privilege_id; ?>" <?php echo $privilege->checked == true ? "checked" : ""; ?> />
                            </span>
                            </td>
                        <?php } else {
                            ?>
                            <td>
                                <span class="button-checkbox">
                                    <button type="button" class="btn"
                                            data-color="primary"><?php echo $privilege->privilege_name; ?>
                                    </button>
                                    <input class="hidden" type="checkbox" name="privileges[]"
                                           value="<?php echo $privilege->privilege_id; ?>" <?php echo $privilege->checked == true ? "checked" : ""; ?> />
                                </span>
                            </td>
                            </tr>

                            <?php
                        }
                        $counter++;
                    }
                    ?>
                </table>
                <div class="submit_div">
                    <button class=" btn btn-success" type="submit"> <i class="fa fa-save"></i> Update Access </button> &nbsp;
                    <a class=" btn btn-danger" href="#" onclick="parent.$.colorbox.close();" > <i class="fa fa-times"></i> Close </a>
                </div>
            </div>
        </form>
    </div>
</div>
<script>

     function ResizeWindow(){
        try{
            parent.$.colorbox.resize({
                innerHeight: '500px',
                innerWidth: '450px'
            });
        }catch(e){}
    }

    $(function () {
        setTimeout(ResizeWindow, 500);

        $('.button-checkbox').each(function () {

            // Settings
            var $widget = $(this),
                $button = $widget.find('button'),
                $checkbox = $widget.find('input:checkbox'),
                color = $button.data('color'),
                settings = {
                    on: {
                        icon: 'glyphicon glyphicon-check'
                    },
                    off: {
                        icon: 'glyphicon glyphicon-unchecked'
                    }
                };

            // Event Handlers
            $button.on('click', function () {
                $checkbox.prop('checked', !$checkbox.is(':checked'));
                $checkbox.triggerHandler('change');
                updateDisplay();
            });
            $checkbox.on('change', function () {
                updateDisplay();
            });
            // Actions
            function updateDisplay() {
                var isChecked = $checkbox.is(':checked');

                // Set the button's state
                $button.data('state', (isChecked) ? "on" : "off");

                // Set the button's icon
                $button.find('.state-icon')
                    .removeClass()
                    .addClass('state-icon ' + settings[$button.data('state')].icon);

                // Update the button's color
                if (isChecked) {
                    $button
                        .removeClass('btn-default')
                        .addClass('btn-' + color + ' active');
                }
                else {
                    $button
                        .removeClass('btn-' + color + ' active')
                        .addClass('btn-default');
                }
            }
            // Initialization
            function init() {
                updateDisplay();
                // Inject the icon if applicable
                if ($button.find('.state-icon').length == 0) {
                    $button.prepend('<i class="state-icon ' + settings[$button.data('state')].icon + '"></i> ');
                }
            }
            init();
        });
    });

</script>