<script src="js/jquery.min.js" type="text/javascript"></script>
<link href="css/form.css" rel="stylesheet" type="text/css">
<link href="ccd/select2/select2.min.css" rel="stylesheet" />
<script src="ccd/select2/select2.min.js"></script>

<style>
    select {
        margin: 2px;
    }
</style>
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

    <form name="frm_agent" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
        <input type="hidden" name="tid" value="<?php echo $tid;?>" />
        <input type="hidden" name="msl" value="<?php echo !empty($msl)?$msl:"";?>" />
        <table class="form_table">
            <tr class="form_row_alt">
                <td class="form_column_caption" style="text-align:center;"><b>Select Disposition:</b>
                </td>
            </tr>
            <tr class="form_row">
                <td class="form_column_caption" style="text-align:center;">
                    <select class="select2" name="disposition_id" id="disposition_id">
                        <option value="">Select</option>
                        <?php if (!empty($dispositions)) { ?>
                            <?php foreach ($dispositions as $key => $value) { ?>
                                <option value="<?php echo $key ?>" <?php echo $ticket_info->disposition_id == $key ? "selected" : "" ?>><?php echo $value ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr class="form_row_alt">
                <td class="form_column_submit" style="text-align:center;padding:20px 0;">
                    <input class="form_submit_button" type="submit" value="Save" name="submitagent" />  &nbsp; &nbsp;
                    <input class="form_submit_button" type="button" value="Cancel" name="submitcancel" onclick="parent.$.colorbox.close();" />
                </td>
            </tr>
        </table>
    </form>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
