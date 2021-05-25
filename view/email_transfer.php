<?php
/**
 * Created by PhpStorm.
 * User: arif
 * Date: 9/19/2018
 * Time: 12:16 PM
 */
?>

<script src="js/jquery.min.js"></script>
<script src="js/datetimepicker/jquery.datetimepicker.js"></script>
<link rel="stylesheet" href="js/datetimepicker/jquery.datetimepicker.css" type="text/css"/>
<script type="text/javascript" src="js/bootstrap-multiselect/js/bootstrap-3.1.1.min.js"></script>
<link rel="stylesheet" href="js/bootstrap-multiselect/css/bootstrap-3.1.1.min.css" type="text/css"/>
<?php
//GPrint($trns_val);die;
?>

<link href="css/form.css" rel="stylesheet" type="text/css">

<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

    <form name="frm_agent" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
        <input type="hidden" name="tid" value="<?php echo $tid;?>" />
        <table class="form_table" style="padding-top: 5%!important;">
            <tr class="form_row_alt" style="margin-top: 10px!important;">
                <td class="form_column_caption" style="text-align:center;">
                    <div id="parent_div" style="margin-bottom: 1%">
                        <input type="radio" name="transfer" value="S" <?php echo $trns_val=='S'?"checked='checked'":''?> > Skill</input>
                        <input type="radio" name="transfer" value="A" <?php echo $trns_val=='A'?"checked='checked'":''?>>Agent</input>
                    </div>

                    <div style="display: <?php echo $trns_val=='S'?'block':'none'?>" id="skill">
                        <select name="skill" id="">
                            <option value="">Select</option>
                            <?php if (!empty($skills)) {
                                foreach ($skills as $key) { ?>
                                    <option value="<?php echo $key->skill_id ?>" <?php echo $ticket_info->skill_id==$key->skill_id?"selected='selected'":"" ?> > <?php echo $key->skill_name ?> </option>
                                <?php }
                            }
                            ?>
                        </select>
                    </div>

                    <div style="display: <?php echo $trns_val=='A'?'block':'none'?>" id="agent_id">
                        <select name="agent_id" id="">
                            <option value="">Select</option>
                            <?php if (is_array($agents)) {
                                foreach ($agents as $agent) {
                                    echo '<option value="' . $agent->agent_id . '"';
                                    if ($ticket_info->assigned_to == $agent->agent_id) echo ' selected';
                                    echo '>' . $agent->agent_id . ' - ' . $agent->nick . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                </td>
            </tr>
            <tr class="form_row">
                <td class="form_column_submit" style="text-align:center;padding:20px 0;">&nbsp; &nbsp;
                    <input class="form_submit_button" type="submit" value="Transfer" name="submitagent" />  &nbsp; &nbsp;
                    <input class="form_submit_button" type="button" value="Cancel" name="submitcancel" onclick="parent.$.colorbox.close();" />

                </td>
            </tr>
        </table>
    </form>

    <script>
        $(document).ready(function () {
            $("#parent_div input[name='transfer']").click(function(){
                var rdval = $(this).val();
                if (rdval == 'A'){
                    $("#agent_id").css('display','block');
                    $("#skill").css('display','none');
                } else {
                    $("#agent_id").css('display','none');
                    $("#skill").css('display','block');
                }
            });
        });
    </script>

