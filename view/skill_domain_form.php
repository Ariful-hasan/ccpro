<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<link href="css/form.css" rel="stylesheet" type="text/css">

<link rel="stylesheet" type="text/css" media="screen" href="css/datePicker.css">
<script src="js/date.js" type="text/javascript"></script>
<script src="js/jquery.datePicker.js" type="text/javascript"></script>
<?php $isUpdate = empty($isUpdate)?false:true;
$dmn_url = !empty($dmn_url)?$dmn_url:"";
?>


<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

    <form name="skill_domain_form" id="skill_domain_form" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName().'&sid='.$sid.'&dmn='.$dmn_url);?>">

        <table class="form_table table">
            <tbody>
            <tr class="form_row_head">
                <td colspan=3>Skill Domain</td>
            </tr>

            <?php //if (!$isUpdate): ?>
                <tr class="form_row_alt">
                    <td class="form_column_caption">Domain Name:</td>
                    <td>
                        <input type="text"  maxlength="30" id="domain" name="domain" value='<?php echo $domain?>'>
                    </td>
                </tr>
            <?php //endif; ?>

            <tr class="form_row">
                <td colspan="2" class="form_column_submit">
                    <input class="form_submit_button btn btn-success" type="submit" value="  <?php if (!empty($isUpdate)):?>Update<?php else:?>Add<?php endif;?>  " > <br><br>
                </td>
            </tr>
            </tbody>
        </table>
    </form>

    <script>
        $(document).ready(function () {
            $( "#skill_domain_form" ).validate({
                rules: {
                    domain: {
                        required: true,
                        maxlength: 30
                    }
                },
                messages: {
                    domain: {
                        required: "Empty Domain"
                    }
                }
            });
        });
    </script>

