<link rel="stylesheet" href="js/treeview/jquery.treeview.css" />
<link href="css/report.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="js/lightbox/css/colorbox.css" />

<script src="js/jquery.cookie.js" type="text/javascript"></script>
<script src="js/treeview/jquery.treeview.js" type="text/javascript"></script>
<script src="js/jquery.contextmenu.r2.packed.js" type="text/javascript"></script>

<link href="css/form.css" rel="stylesheet" type="text/css">


<?php

function displayRow($label_name, $value)
{
    echo "<tr class='form_row'>
        <td>
            <label> $label_name :</label>
        </td>
        <td>
            <label> $value </label>
        </td>
     </tr>";
}


foreach ($page_elements as $page_element){

    echo '<table class="form_table" border="1" align="center" cellspacing="0" cellpadding="6">';
    echo "<tr class='form_row_alt'>       
            <td class='form-header'> Element Information </td>              
            <td class='form-header'> 
                <button class='btn btn-sm' id='$page_element->element_id' onclick='editElement(this.id)'><span></span>
                    <img src='image/tn_edit.gif' /> Edit 
                </button>
                <button class='btn btn-sm' id='$page_element->element_id' onclick='uploadElementMedia(this.id)'>
                    <img src='image/icon/music.png' /> Upload Audio 
                </button>
                <button class='btn btn-sm' id='$page_element->element_id' onclick='deleteElement(this.id)'>
                    <img src='image/tn_delete.gif' /> Delete 
                </button>                
            </td>              
    </tr>";
    displayRow("Element Type", $page_element->type);
    displayRow("Element Order", $page_element->element_order);
    displayRow("Text (EN)", $page_element->display_name_en);
    displayRow("Text (BN)", $page_element->display_name_bn);
    displayRow("Text Color", $page_element->text_color);
    displayRow("Background Color", $page_element->background_color);
    displayRow("Element Name", $page_element->name);
    displayRow("Element Value", $page_element->value);
    displayRow("No Of Rows", $page_element->rows);
    displayRow("No Of Columns", $page_element->columns);
    if($page_element->is_visible == 'Y'){
        displayRow("Element Visiblity", "Visible");
    }else{
        displayRow("Element Visiblity", "Not Visible");
    }

    displayRow("API", htmlspecialchars($page_element->data_provider_function));
    displayRow("Custom Audio", $page_element->custom2);
    echo '</table>';
    echo '<br /><br />';
}

?>

<a class="btn" href="<?php echo $this->url('task=vivr-panel&act=add-element&vivrid='.$vivr_id.'&page_id='.$page_id) ;?>" > <img  src='image/tn_add.gif' />  Add New Element  </a>
<br /><br />


<script>

    function addElement(page_id) {
        window.location.href = "<?php echo $this->url('task=vivr-panel&act=delete-element&page_id=');?>"+ page_id;
    }

    function deleteElement(element_id) {
        if (confirm('Are you sure to delete this element?')) {
            $.getJSON("<?php echo $this->url('task=vivr-panel&act=delete-element');?>", {element_id: element_id},
                function (json) {
                    if (json.success == true) {
                        window.location.reload();
                    } else {
                        alert("Failed to delete tree!!");
                    }
                });
        }
    }

    function editElement(element_id) {
        window.location.href = "<?php echo $this->url('task=vivr-panel&act=edit-element&element_id=');?>" + element_id;
    }

    function uploadElementMedia(element_id) {
        window.location.href = "<?php echo $this->url('task=vivr-panel&act=upload-element-media-file&vivrid=' . $vivr_id . '&page_id=' . $page_id . '&element_id=');?>" + element_id;
    }

</script>