<link href="css/form.css" rel="stylesheet" type="text/css">

<form name="ivr_form" enctype="multipart/form-data" method="post" action="<?php echo $this->url('task=' . $request->getControllerName() . '&act=edit-node&vivrid=' . $request->getRequest("vivrid") .'&page_id=' . $request->getRequest("page_id")); ?>" onsubmit="return checkForm();">

    <input type="hidden" name="vivr_id" value="<?php echo $vivr_id; ?>"/>
    <input type="hidden" name="page_id" value="<?php echo $page_id; ?>"/>

    <table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">
        <tbody>
        <tr class="form_row_alt">
            <td class="form-header" colspan=2>Item Information</td>
        </tr>

        <tr class="form_row">
            <td width="35%" class="form_column_caption" valign="top">
                <span> Report Title: </span>
            </td>

            <td>
                <select name="vivr_title">
                    <?php
                    foreach ($vivr_page_titles as $vivr_page_title) {
                        if ($vivr_page->title_id == $vivr_page_title->disposition_code) {
                            echo "<option value='$vivr_page_title->disposition_code' selected >$vivr_page_title->service_title</option>";
                        } else {
                            echo "<option value='$vivr_page_title->disposition_code' >$vivr_page_title->service_title</option>";
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr class="form_row">
            <td width="35%" class="form_column_caption" valign="top">
                <span> Page Heading (EN):   </span>
            </td>
            <td>
                <input type="text" name="heading_en" value="<?php echo $vivr_page->page_heading_en ?>"/>
            </td>
        </tr>

        <tr class="form_row">
            <td width="35%" class="form_column_caption" valign="top">
                <span> Page Heading (BN):   </span>
            </td>
            <td>
                <input type="text" name="heading_bn" value="<?php echo $vivr_page->page_heading_ban ?>" />
            </td>
        </tr>

        <tr class="form_row">
            <td width="35%" class="form_column_caption" valign="top">
                <span> Task: </span>
            </td>

            <td>
                <select name="task" required>
                    <?php
                    foreach ($tasks as $key => $value) {
                        if($vivr_page->task == $key){
                            echo "<option value='$key' selected >$value</option>";
                        }else{
                            echo "<option value='$key'>$value</option>";
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr class="form_row">
            <td width="35%" class="form_column_caption" valign="top">
                <span> Navigate To Main Page: </span>
            </td>
            <td>
                <select name="hase_main_page" required>
                    <option value="Y" <?php echo ($vivr_page->has_main_menu == "Y") ? "selected" : "" ?> >Yes</option>
                    <option value="N" <?php echo ($vivr_page->has_main_menu == "N") ? "selected" : "" ?> >No</option>
                </select>
            </td>
        </tr>

        <tr class="form_row">
            <td width="35%" class="form_column_caption" valign="top">
                <span> Navigate To Previous Page: </span>

            </td>
            <td>
                <select name="has_previous_page" required>
                    <option value="Y" <?php echo ($vivr_page->has_previous_menu == "Y") ? "selected" : "" ?> >Yes</option>
                    <option value="N" <?php echo ($vivr_page->has_previous_menu == "N") ? "selected" : "" ?> >No</option>
                </select>
            </td>
        </tr>

        <?php
        if (isset($error_data)) {
            if ($error_data->error_code == 0) {
                echo "<tr class='form_row'>
                    <td colspan='2'>
                        <div class='alert alert-success'>
                            <strong>$error_data->error_msg</strong>
                        </div>
                    </td>
                </tr>";

            } else if ($error_data->error_code == 101) {
                echo "<tr class='form_row'>
                    <td colspan='2'>
                        <div class='alert alert-danger'>
                            <strong>$error_data->error_msg</strong>
                        </div>
                    </td>
                </tr>";
            }
        }
        ?>

        <tr class="form_row">
            <td colspan="2">
                <input type="submit" value="Submit"/>
            </td>
        </tr>

        </tbody>
    </table>
</form>