<link href="css/form.css" rel="stylesheet" type="text/css">

<?php if($errMsg){
    echo $errType == 1 ? "<div class='alert alert-success alert-dismissable'> $errMsg </div>" :
        "<div class='alert alert-success alert-dismissable'> $errMsg </div>";
}
?>



<form name="agentsessiondata" method="post" action="<?php echo $form_submit_url; ?>">

    <table class="form_table" border="0" align="center" cellpadding="6" cellspacing="0">
        <tbody>
        <tr class="form_row">
            <td class="form_column_caption" width="40%">Login Time:</td>
            <td>
                <input type="text" class="gs-datetime-picker" name="first_login" size="30" maxlength="19" value="<?php echo $shift->first_login;?>" />
            </td>
        </tr>


        <tr class="form_row">
            <td valign=top class="form_column_caption"><b>Logout Time:</b></td>
            <td >
                <input type="text" class="gs-datetime-picker" name="last_logout" size="10" maxlength="19" value="<?php echo $shift->last_logout;?>">
            </td>
        </tr>
        <tr class="" id="form_row">
            <td colspan="2" class="">
                <input style="float: right" class="form_submit_button" type="submit" value="  Submit  " name="submitagentsession"> <br><br>
            </td>
        </tr>
        </tbody>
    </table>
</form>
