<link rel="stylesheet" href="css/bootstrap.min.css"/>
<link rel="stylesheet" href="css/form.css" type="text/css">
<div class="moduleForm">
    <form method="post"
          action="<?php echo $this->url("task=module-settings&act=update-module-settings&settingsId=" . $settingsId); ?>">
        <table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
            <tr>
                <td class="form_column_caption">Settings Type:</td>
                <td><input type="text" name="type" class="form-control" value="<?php echo $type ?>" placeholder="Max 20 Character"/></td>
            </tr>
            <tr>
                <td class="form_column_caption">Settings Title:</td>
                <td><input type="text" name="title" class="form-control" value="<?php echo $title ?>" placeholder="Max 30 Character"/></td>
            </tr>
            <tr>
                <td class="form_column_caption">Settings Name:</td>
                <td><input type="text" name="name" class="form-control" value="<?php echo $name ?>" placeholder="Max 30 Character"/></td>
            </tr>
            <tr>
                <td class="form_column_caption">Settings Value:</td>
                <td><input type="text" name="value" class="form-control" value="<?php echo $value ?>" placeholder="Enter Settings Value"/></td>
            </tr>
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

