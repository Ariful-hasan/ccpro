<script src="js/jquery.min.js"></script>
<script src="js/datetimepicker/jquery.datetimepicker.js"></script>
<link rel="stylesheet" href="js/datetimepicker/jquery.datetimepicker.css" type="text/css"/>
<script type="text/javascript" src="js/bootstrap-multiselect/js/bootstrap-3.1.1.min.js"></script>
<link rel="stylesheet" href="js/bootstrap-multiselect/css/bootstrap-3.1.1.min.css" type="text/css"/>


<link href="css/form.css" rel="stylesheet" type="text/css">

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

    <form name="frm_agent" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
        <input type="hidden" name="tid" value="<?php echo $tid;?>" />
        <input type="hidden" name="msl" value="<?php echo !empty($msl) ? $msl : "";?>" />
        <table class="form_table" style="padding-top: 5%!important;">
            <tr class="form_row_alt" style="margin-top: 10px!important;">
                <td class="form_column_caption" style="text-align:center;"><b>Status:</b>
                    <select name="skill" id="skill">
                        <option value="">Select</option>
                        <?php if (!empty($skills)) {
                            foreach ($skills as $key) { ?>
                                <option value="<?php echo $key->skill_id ?>" <?php echo $ticket_info->skill_id==$key->skill_id?"selected='selected'":"" ?> > <?php echo $key->skill_name ?> </option>
                            <?php }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr class="form_row">
                <td class="form_column_submit" style="text-align:center;padding:20px 0;">&nbsp; &nbsp;
                    <input class="form_submit_button" type="submit" value="Save" name="submitagent" />  &nbsp; &nbsp;
                    <input class="form_submit_button" type="button" value="Cancel" name="submitcancel" onclick="parent.$.colorbox.close();" />

                </td>
            </tr>
        </table>
    </form>

