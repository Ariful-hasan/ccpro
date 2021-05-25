<link href="css/form.css" rel="stylesheet" type="text/css">

<form name="ivr_form" enctype="multipart/form-data" method="post"
      action="<?php echo $this->url('task=' . $request->getControllerName() . '&act=add-node&vivrid=' . $request->getRequest("vivrid") .'&page_id=' . $request->getRequest("page_id")); ?>">

        <input type="hidden" name="vivr_id" value="<?php echo $vivr_id; ?>"/>
        <input type="hidden" name="parent_page_id" value="<?php echo $parent_page_id; ?>"/>

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
                    <select name="vivr_title" >
                        <?php
                        foreach ($vivr_page_titles as $vivr_page_title) {
                            echo "<option value='$vivr_page_title->disposition_code'>$vivr_page_title->service_title</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>

            <tr class="form_row">
                <td width="35%" class="form_column_caption" valign="top">
                    <span> Page Heading (EN):</span>
                </td>
                <td>
                    <input type="text" name="heading_en" value="" required />
                </td>
            </tr>

            <tr class="form_row">
                <td width="35%" class="form_column_caption" valign="top">
                    <span> Page Heading (BN):</span>
                </td>
                <td>
                    <input type="text" name="heading_bn" value="" required/>
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
                            echo "<option value='$key'>$value</option>";
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
                        <option value="Y">Yes</option>
                        <option value="N">No</option>
                    </select>
                </td>
            </tr>

            <tr class="form_row">
                <td width="35%" class="form_column_caption" valign="top">
                    <span> Navigate To Previous Page: </span>

                </td>
                <td>
                    <select name="has_previous_page" required>
                        <option value="Y">Yes</option>
                        <option value="N">No</option>
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