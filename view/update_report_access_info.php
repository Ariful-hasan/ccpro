<div class="col-md-12 col-sm-12">
    <div class="row">
        <form name="reportAccessInfo" method="post"
              action="<?php echo $this->url("task=roles&act=update-page-access-info&pageId=".$pageId."&roleId=".$roleId);?>">
            <div class="col-md-12 col-sm-12 col-lg-12">
                <label for="dateRange">Date Range:</label>
                <input type="number" name="dateRange" id="dateRange" class="form-control" placeholder="Enter Date Range" value="<?php echo $dateRange ?>" />
                <input type="hidden" name="path" id="path"  value="<?php echo $path ?>" />
                <input type="hidden" name="ajaxPath" id="ajaxPath" value="<?php echo $ajaxPath ?>" />
                <br />
                <label for="dateRange">Hide Columns:</label>
                <table class="table table-condensed table-responsive table-bordered">
                    <?php
                    $counter = 0;
                    foreach ($hideColumnData as $data) {
                        if (($counter % 2) == 0) {
                            ?>
                            <tr>
                            <td>
                            <span class="button-checkbox ">
                                <button type="button" class="btn" data-color="primary"><?php echo $data->name; ?></button>
                                <input class="hidden" type="checkbox" name="hideColumns[]"
                                       value="<?php echo $data->name; ?>" <?php echo $data->isHidden == true ? "checked" : ""; ?> />
                            </span>
                            </td>
                        <?php
                        } else {
                        ?>
                            <td>
                                <span class="button-checkbox">
                                    <button type="button" class="btn"
                                            data-color="primary"><?php echo $data->name; ?></button>
                                    <input class="hidden" type="checkbox" name="hideColumns[]"
                                           value="<?php echo $data->name; ?>" <?php echo $data->isHidden == true ? "checked" : ""; ?> />
                                </span>
                            </td>
                            </tr>
                    <?php
                        }
                        $counter++;
                    }
                    if($counter == 0){
                        echo "<tr><td class='danger text-center' colspan='2'> This Page Has No Hide Column. </td></tr>";
                    }
                    ?>
                </table>
                <div class="submit_div">
                    <br />
                    <button class=" btn btn-primary" type="submit"> <i class="fa fa-save"></i> Update Access </button> &nbsp;
                    <a class=" btn btn-danger" href="#" onclick="parent.$.colorbox.close();" > <i class="fa fa-times"></i> Close </a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
if (!empty($postResponse)) {
    if ($postResponse->status) {
        echo "<div class='alert alert-success error'>{$postResponse->msg}</div>";
    } else {
        echo "<div class='alert alert-danger error'>{$postResponse->msg}</div>";
    }
}
?>

<script>
    function ResizeWindow(){
        try{
            parent.$.colorbox.resize({
                innerHeight: '550px',
                innerWidth: '650px'
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

