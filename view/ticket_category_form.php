<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<link href="css/form.css" rel="stylesheet" type="text/css">

<link rel="stylesheet" type="text/css" media="screen" href="css/datePicker.css">
<script src="js/date.js" type="text/javascript"></script>
<script src="js/jquery.datePicker.js" type="text/javascript"></script>
<?php $isUpdate?>


<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

    <form name="frm_shift_profile" id="frm_shift_profile" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName()."&id={$request->getRequest('id')}");?>">

        <table class="form_table table">
            <tbody>
            <tr class="form_row_head">
                <td colspan=3>Email Category Information</td>
            </tr>

            <?php /*if (!$isUpdate): */?><!--
                <tr class="form_row_alt">
                    <td class="form_column_caption">Shift Code:</td>
                    <td>
                        <input type="hidden"  maxlength="4" id="shift_code" name="shift_code" value="<?php /*echo $ticket_category->category_id; */?>">
                    </td>
                </tr>
            --><?php /*endif; */?>

            <tr class="form_row">
                <td class="form_column_caption">Title:</td>
                <td>
                    <input type="text" maxlength="40" id="title" name="title" value="<?php echo $ticket_category->title; ?>">
                </td>
            </tr>

            <tr class="form_row">
                <td class="form_column_caption">Status</td>
                <td>
                    <select  name="status" maxlength="1">
                        <option <?php echo $ticket_category->status == "A" ?' selected="selected"':"";?> value="A">Active</option>
                        <option <?php echo $ticket_category->status == "I" ?' selected="selected"':"";?> value="I">Inactive</option>
                    </select>
                </td>
            </tr>


            <tr class="form_row">
                <td colspan="2" class="form_column_submit">
                    <input class="form_submit_button btn btn-success" type="submit" value="  <?php if (!empty($isUpdate)):?>Update<?php else:?>Add<?php endif;?>  " name="submit"> <br><br>
                </td>
            </tr>
            </tbody>
        </table>
    </form>

    <script>
        $(document).ready(function () {
            $(".only-time").datetimepicker({
                datepicker:false,
                format: "H:i",
            });
        });
        /*$(function () {
         $(document).on('change',"#shift_code",function () {
         var start_time = $(this).find(':selected').data('start-time');
         $("#shift_start").empty();
         $("#shift_start").append("<option value='"+ start_time +"'>"+ start_time +"</option><option value='N'>Now</option>");
         });
         });*/
    </script>

