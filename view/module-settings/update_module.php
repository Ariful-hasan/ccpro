<link rel="stylesheet" href="css/bootstrap.min.css"/>
<link rel="stylesheet" href="css/form.css" type="text/css">
<div class="moduleForm">
    <form method="post" action="<?php echo $this->url("task=module-settings&act=update-module"); ?>">
        <table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
            <tr>
                <td class="form_column_caption">Module Code:</td>
                <td><input type="text" name="moduleCode" class="form-control" placeholder="Max Length 10 Character" value="<?php echo $moduleCode ?>"/></td>
            </tr>
            <tr>
                <td class="form_column_caption">Module Title:</td>
                <td><input type="text" name="moduleTitle" class="form-control" placeholder="Max Length 30 Character" value="<?php echo $moduleTitle ?>"/>
                </td>
            </tr>
            <input type="hidden" name="moduleId" value="<?php echo $moduleId ?>"/>
        </table>
        <br/>
        <div class="form-group">
            <input class="btn btn-success" type="submit" value="Submit"/>
        </div>
    </form>
</div>

<?php
if (!empty($errorData)) {
    if ($errorData->status) {
        echo "<div class='alert alert-success error'>{$errorData->msg}</div>";
    } else {
        echo "<div class='alert alert-danger error'>{$errorData->msg}</div>";
    }
}
?>

<script>
    $(function () {
        if ($('div.error').length) {
            $(".moduleForm").hide();
        }
    });

</script>

