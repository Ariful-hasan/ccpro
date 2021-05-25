<link rel="stylesheet" href="js/huebee/huebee.min.css">
<script src="js/huebee/huebee.pkgd.min.js"></script>

<link rel="stylesheet" href="js/treeview/jquery.treeview.css" />

<link href="css/report.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="js/lightbox/css/colorbox.css" />

<script src="js/jquery.cookie.js" type="text/javascript"></script>
<script src="js/treeview/jquery.treeview.js" type="text/javascript"></script>
<script src="js/jquery.contextmenu.r2.packed.js" type="text/javascript"></script>

<link href="css/form.css" rel="stylesheet" type="text/css">

<form method="post" action="<?php  echo $this->url('task=' . $request->getControllerName() . '&act=add-element&vivrid=' . $request->getRequest("vivrid") .'&page_id=' . $request->getRequest("page_id"));?>">
    <table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">
        <tr class="form_row">
            <td width="35%" class="form_column_caption" valign="top">
                <span> Element Order :</span>
            </td>
            <td>
                <input type="number" name="element_order" min="1" max="100" placeholder="Element Will Be Displayed In This Order" >
            </td>
        </tr>
    </table>

    <table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">
        <tr class="form_row">
            <td width="45%" class="form_column_caption" valign="top">
                <span> Display Text (EN) :</span>
            </td>
            <td>
                <textarea name="display_name_en" cols="83" rows="3" placeholder="This Text Will Be Displayed For English"></textarea>
            </td>
        </tr>
    </table>

    <table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">
        <tr class="form_row">
            <td width="45%" class="form_column_caption" valign="top">
                <span> Display Text (BN) :</span>
            </td>
            <td>
                <textarea name="display_name_bn" cols="83" rows="3" placeholder="This Text Will Be Displayed For Bangla"></textarea>
            </td>
        </tr>
    </table>

    <table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">
        <tr class="form_row">
            <td width="35%" class="form_column_caption" valign="top">
                <span> Background Color :</span>
            </td>
            <td>
                <input name="background_color" value="#DF1E26" class="color-input button" data-huebee placeholder="Background Color Code" autocomplete="off" />
            </td>
        </tr>
    </table>

    <table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">
        <tr class="form_row">
            <td width="35%" class="form_column_caption" valign="top">
                <span> Text Color :</span>
            </td>
            <td>
                <input name="text_color" value="#FFFFFF" class="color-input txt" data-huebee placeholder="Text Color Code" autocomplete="off" />
            </td>
        </tr>
    </table>

    <table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">
        <tr class="form_row">
            <td width="35%" class="form_column_caption" valign="top">
                <span> Element Name :</span>
            </td>
            <td>
                <input type="text" name="element_name" />
            </td>
        </tr>
    </table>

    <table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">
        <tr class="form_row">
            <td width="35%" class="form_column_caption" valign="top">
                <span> Number Of Rows :</span>
            </td>
            <td>
                <input name="number_of_rows" id="table_rows" type="number"  min="0" max="100" value="0" placeholder="Row Number Works With Table" />
            </td>
        </tr>
    </table>

    <table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">
        <tr class="form_row">
            <td width="35%" class="form_column_caption" valign="top">
                <span> Number Of Columns :</span>
            </td>
            <td>
                <input name="number_of_columns" id="table_columns" type="number"  min="0" max="100" value="0" placeholder="Column Number Works With Table" />
            </td>
        </tr>
    </table>

    <table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">
        <tr class="form_row">
            <td width="35%" class="form_column_caption" valign="top">
                <span> Visibility :</span>
            </td>
            <td>
                <select name="is_visible">
                    <option value="Y">Visible</option>
                    <option value="N">Not Visible</option>
                </select>
            </td>
        </tr>
    </table>

    <table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">
        <tr class="form_row">
            <td width="35%" class="form_column_caption" valign="top">
                <span> API :</span>
            </td>
            <td>
                <input type="text" name="api">
            </td>
        </tr>
    </table>

    <table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">
        <tr class="form_row">
            <td width="35%" class="form_column_caption" valign="top">
                <span> Element Type :</span>
            </td>
            <td>
                <select name="element_type" class="element_type" >
                    <?php
                    foreach ($element_types as $key => $value) {
                        echo "<option value='$key' > $value </option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
    </table>

    <div id="element_wise_value">

    </div>

    <table class="form_table submit" border="0" align="center" cellspacing="0" cellpadding="6">
        <tr class="form_row">
            <td width="45%"></td>
            <td >
                <input id="submit_btn" type="submit" value="Submit"/>
                <a class="btn btn-lg" href="<?php echo $this->url('task=vivr-panel&act=node-elements&vivrid=' . $vivr_id . '&page_id=' . $page_id); ?>"> Back </a>
            </td>

        </tr>
    </table>

    <br /><br />
</form>

<script>

    var elem = $('.color-input')[0];
    var hueb = new Huebee(elem, {
        notation: 'hex',
        saturations: 2,
    });

    var txt = $('.color-input')[1];
    var hueb = new Huebee(txt, {
        notation: 'hex',
        saturations: 2,
    });

    function getHtmlForButton(){
        var html_content =
            '<table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">' +
            '<tr class="form_row">' +
            '<td width="35%" class="form_column_caption" valign="top">' +
            '<span> Value :</span>' +
            '</td>' +
            '<td>' +
            '<input type="text" name="value">' +
            '</td>' +
            '</tr>' +
            '</table>'+

            '<table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">' +
            '<tr class="form_row">' +
            '<td width="35%" class="form_column_caption" valign="top">' +
            '<span> API Keys: </span>' +
            '</td>' +
            '<td>' +
            '<input type="text" name="api_key">' +
            '</td>' +
            '</tr>' +
            '</table>'+

            '<table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">' +
            '<tr class="form_row">' +
            '<td width="35%" class="form_column_caption" valign="top">' +
            '<span> API Data Comparison :</span>' +
            '</td>' +
            '<td>' +
            '<input type="text" name="api_comparison">' +
            '</td>' +
            '</tr>' +
            '</table>'+

            '<table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">' +
            '<tr class="form_row">' +
            '<td width="35%" class="form_column_caption" valign="top">' +
            '<span> API Data Calculation :</span>' +
            '</td>' +
            '<td>' +
            '<input type="text" name="api_calculation">' +
            '</td>' +
            '</tr>' +
            '</table>'+

            '<table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">' +
            '<tr class="form_row">' +
            '<td width="35%" class="form_column_caption" valign="top">' +
            '<span> Redirect To  :</span>' +
            '</td>' +
            '<td>' +
            '<select name="redirect_page_id">' +
            '<?php
                foreach ($child_pages as $child_page){
                    echo '<option value="' . $child_page->page_id . '">' . $child_page->title . '</option>';
                }
                ?>' +
            '</td>' +
            '</tr>' +
            '</table>';

        return html_content;
    }

    function getHtmlForLink() {
        var html_content =
            '<table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">' +
            '<tr class="form_row">' +
            '<td width="35%" class="form_column_caption" valign="top">' +
            '<span> Web Address (BN) :</span>' +
            '</td>' +
            '<td>' +
            '<input type="text" name="web_bn">' +
            '</td>' +
            '</tr>' +
            '</table>' +

            '<table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">' +
            '<tr class="form_row">' +
            '<td width="35%" class="form_column_caption" valign="top">' +
            '<span> Web Address (EN) :</span>' +
            '</td>' +
            '<td>' +
            '<input type="text" name="web_en">' +
            '</td>' +
            '</tr>' +
            '</table>';

        return html_content;
    }

    function getHtmlForParagraph(){
        var html_content =
            '<table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">' +
            '<tr class="form_row">' +
            '<td width="35%" class="form_column_caption" valign="top">' +
            '<span> API Keys: </span>' +
            '</td>' +
            '<td>' +
            '<input type="text" name="api_key">' +
            '</td>' +
            '</tr>' +
            '</table>'+

            '<table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">' +
            '<tr class="form_row">' +
            '<td width="35%" class="form_column_caption" valign="top">' +
            '<span> API Data Comparison :</span>' +
            '</td>' +
            '<td>' +
            '<input type="text" name="api_comparison">' +
            '</td>' +
            '</tr>' +
            '</table>'+

            '<table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">' +
            '<tr class="form_row">' +
            '<td width="35%" class="form_column_caption" valign="top">' +
            '<span> API Data Calculation :</span>' +
            '</td>' +
            '<td>' +
            '<input type="text" name="api_calculation">' +
            '</td>' +
            '</tr>' +
            '</table>';

        return html_content;
    }

    function getHtmlForTable() {
        var html_content =
            '<table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">' +
            '<tr class="form_row">' +
                '<td width="35%" class="form_column_caption" valign="top">' +
                    '<span> Table Type: </span>' +
                '</td>' +
                '<td>' +
                    '<select name="table_type" id="table_type">' +
                        '<option value="static"> Static </option>' +
                        '<option value="dynamic"> Dynamic </option>' +
                    '</select>' +
                '</td>' +
            '</tr>' +
            '</table>' +
            '<div id="table_element"></div>';

        return html_content;
    }

    function getHtmlForStaticTable(number_of_rows) {
        var html_content = '';

        html_content += '<table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">' +
            '<tr class="form_row">' +
            '<td width="35%" class="form_column_caption" valign="top">' +
            '<span> Table heading (EN) : </span>' +
            '</td>' +
            '<td>' +

            '<input type="text" name = "table_heading_en" placeholder=" Use Semicolon (;) for Multiple Heading ">' +

            '</td>' +
            '</tr>' +

            '<tr class="form_row">' +
            '<td width="35%" class="form_column_caption" valign="top">' +
            '<span> Table heading (BN) : </span>' +
            '</td>' +
            '<td>' +

            '<input type="text" name = "table_heading_bn" placeholder = " Use Semicolon (;) for Multiple Heading " >' +

            '</td>' +
            '</tr>' +
            '</table>';

        var count = 1;
        while (count <= number_of_rows) {
            html_content += '<table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">' +
                '<tr class="form_row">' +
                '<td width="35%" class="form_column_caption" valign="top">' +
                '<span> Table Row (EN) ' + count + ': </span>' +
                '</td>' +
                '<td>' +

                '<input type="text" name = "table_row_en[' + (count - 1) + ']" placeholder=" Use Semicolon (;) for Multiple Column Data ">' +

                '</td>' +
                '</tr>' +

                '<tr class="form_row">' +
                '<td width="35%" class="form_column_caption" valign="top">' +
                '<span> Table Row (BN) ' + count + ' : </span>' +
                '</td>' +
                '<td>' +

                '<input type="text" name = "table_row_bn[' + (count - 1) +']" placeholder = " Use Semicolon (;) for Multiple Column Data " >' +

                '</td>' +
                '</tr>' +
                '</table>';

            count++;
        }

        return html_content;
    }

    function getHtmlForDynamicTable(no_of_rows, no_of_columns) {
        var html_content = '';

        html_content += '<table class="form_table" border="0" align="center" cellspacing="0" cellpadding="6">' +
            '<tr class="form_row">' +
            '<td width="35%" class="form_column_caption" valign="top">' +
            '<span> Table heading (EN) : </span>' +
            '</td>' +
            '<td>' +

            '<input type="text" name = "table_heading_en" placeholder=" Use Semicolon (;) for Multiple Heading ">' +

            '</td>' +
            '</tr>' +

            '<tr class="form_row">' +
            '<td width="35%" class="form_column_caption" valign="top">' +
            '<span> Table heading (BN) : </span>' +
            '</td>' +
            '<td>' +

            '<input type="text" name = "table_heading_bn" placeholder = " Use Semicolon (;) for Multiple Heading " >' +

            '</td>' +
            '</tr>' +

            '<tr class="form_row">' +
            '<td width="35%" class="form_column_caption" valign="top">' +
            '<span> Key ID : </span>' +
            '</td>' +
            '<td>' +

            '<input type="text" name = "table_key_id" placeholder = " Use Semicolon (;) for Multiple Key IDs " >' +

            '</td>' +
            '</tr>' +

            '<tr class="form_row">' +
            '<td width="35%" class="form_column_caption" valign="top">' +
            '<span> Key Comparison : </span>' +
            '</td>' +
            '<td>' +

            '<input type="text" name = "table_key_comparison" placeholder = " Use Semicolon (;) for Multiple Key Comparisons " >' +

            '</td>' +
            '</tr>' +

            '<tr class="form_row">' +
            '<td width="35%" class="form_column_caption" valign="top">' +
            '<span> Key Calculation : </span>' +
            '</td>' +
            '<td>' +

            '<input type="text" name = "table_key_calculation" placeholder = " Use Semicolon (;) for Multiple Key Comparisons " >' +

            '</td>' +
            '</tr>' +

            '</table>';

        return html_content;
    }

    $("#element_wise_value").html(getHtmlForButton());

    $(".element_type").on("change", function () {
            var html_element = this.value;

            if (html_element == "button") {
                $("#element_wise_value").html(getHtmlForButton());
            } else if (html_element == "table") {

                var table_rows = $("#table_rows").val();
                var table_columns = $("#table_columns").val();


                $("#element_wise_value").html(getHtmlForTable());
                $("#table_element").html(getHtmlForStaticTable(table_rows, table_columns));

                $("#table_type").on("change", function () {
                    var table_type = this.value;
                    if(table_type == "static"){
                        $("#table_element").html(getHtmlForStaticTable(table_rows, table_columns));
                    }else{
                        $("#table_element").html(getHtmlForDynamicTable(table_rows, table_columns));
                    }
                });

            } else if (html_element == "paragraph") {
                $("#element_wise_value").html(getHtmlForParagraph());
            } else if (html_element == "a") {
                $("#element_wise_value").html(getHtmlForLink());
            }
        }
    );

    var success = '<?php echo $success; ?>' ;

    if (success != '') {
        if (success == '100') {
            $(".form_table").hide();
            var success_element = "<div class='alert alert-success'>Element Added Successfully.</div>"+
            "<a class='btn btn-lg' href='<?php echo $this->url('task=vivr-panel&act=node-elements&vivrid=' . $vivr_id . '&page_id=' . $page_id); ?>' > Back </a>";

            $("#element_wise_value").html(success_element);
        }
        if (success == '0') {
            $(".form_table").hide();
            var success_element = "<div class='alert alert-danger'>Failed To Add Element.</div>"+
                "<a class='btn btn-lg' href='<?php echo $this->url('task=vivr-panel&act=node-elements&vivrid=' . $vivr_id . '&page_id=' . $page_id); ?>' > Back </a>";

            $("#element_wise_value").html(success_element);
        }
    }

</script>
