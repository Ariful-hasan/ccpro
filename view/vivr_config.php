<link rel="stylesheet" href="js/treeview/jquery.treeview.css" />
<link href="css/report.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="js/lightbox/css/colorbox.css" />

<script src="js/jquery.cookie.js" type="text/javascript"></script>
<script src="js/treeview/jquery.treeview.js" type="text/javascript"></script>
<script src="js/jquery.contextmenu.r2.packed.js" type="text/javascript"></script>

<style>

    body {
        font-family: Arial;
    }

    ul.tree li {
        list-style-type: none;
        position: relative;
    }

    ul.tree li ul {
        display: none;
    }

    ul.tree li.open > ul {
        display: block;
    }

    ul.tree li a {
        color: black;
        text-decoration: none;
    }

    ul.tree li a:before {
        height: 1em;
        padding:0 .1em;
        font-size: .8em;
        display: block;
        position: absolute;
        left: -1.3em;
        top: .2em;
    }

    ul.tree li > a:not(:last-child):before {
        content: '+';
    }

    ul.tree li.open > a:not(:last-child):before {
        content: '-';
    }

</style>

<?php //GPrint($root_page);die; ?>
<ul class="tree">
    <?php

//    dd($this->url('task=' . $request->getControllerName() . '&act=add-root-page&vivrid=' . $request->getRequest('vivrid')));
//    die("tets");
    if (sizeof($vivr_tree) == 0) {
        echo "<button class='btn btn-primary' id='add_root'> Add Root Node </button>";
    }
    printNodeElement($vivr_tree);
    ?>
</ul>


<?php
function printNodeElement($node)
{
    if ($node->child_node == 0) {
        echo "<li>";
        printNode($node);
        echo "</li>";
        return;
    } else {
        echo "<li>";
        printNode($node);
        echo "<ul>";
        foreach ($node->child_node as $child_node) {
            printNodeElement($child_node);
        }
        echo "</ul>";
        echo "</li>";
    }
}

function printNode($node)
{
    echo "<a href='#' id='$node->page_id'>$node->page_heading_en</a>";
}
?>
<div class="contextMenu" id="myMenu1">
    <ul>
        <li id="tn_edit"><img src="image/tn_edit.gif"/> Edit</li>
        <li id="tn_add"><img src="image/tn_add.gif"/> Add Branch</li>
        <li id="tn_upload"><img src="image/tn_add.gif"/> Upload File</li>
        <li id="tn_element"><img src="image/i-3.png"/> Node Element </li>
        <li id="tn_delete"><img src="image/tn_delete.gif"/> Delete Tree</li>
    </ul>
</div>

<script>

    function requestNewWindow(str_URL) {
        $.colorbox({
            href: str_URL, iframe: true, width: "900", height: "650", onClosed: function () {
                window.location = window.location.href;
            }
        });
    }

    var vivr_id = '<?php echo $vivr_id;?>';

    var tree = document.querySelectorAll('ul.tree a:not(:last-child)');
    for(var i = 0; i < tree.length; i++){
        tree[i].addEventListener('click', function(e) {
            var parent = e.target.parentElement;
            var classList = parent.classList;
            if(classList.contains("open")) {
                classList.remove('open');
                var opensubs = parent.querySelectorAll(':scope .open');
                for(var i = 0; i < opensubs.length; i++){
                    opensubs[i].classList.remove('open');
                }
            } else {
                classList.add('open');
            }
            e.preventDefault();
        });
    }

    $('.tree li a').contextMenu('myMenu1', {
        bindings: {
            'tn_edit': function (t) {
                var page_id = t.id;
                requestNewWindow('<?php echo $this->url('task=vivr-panel&act=edit-node&vivrid=');?>' + vivr_id + '&page_id=' + page_id);
            },
            'tn_add': function(t) {
                var page_id = t.id;
                requestNewWindow('<?php echo $this->url('task=vivr-panel&act=add-node&vivrid=');?>' + vivr_id + '&page_id=' + page_id);
            },
            'tn_upload': function(t) {
                var page_id = t.id;
                requestNewWindow('<?php echo $this->url('task=vivr-panel&act=upload-page-media-file&vivrid=');?>' + vivr_id + '&page_id=' + page_id);
            },

            'tn_element': function(t) {
                var page_id = t.id;
                requestNewWindow('<?php echo $this->url('task=vivr-panel&act=node-elements&vivrid=');?>' + vivr_id + '&page_id=' + page_id);
            },

            'tn_delete': function(t) {

                if(confirm('Deleting branch will delete this node with all its children.\n\nAre you sure to delete this branch?')) {
                    var page_id = t.id;
                    $.getJSON("<?php echo $this->url('task=vivr-panel&act=delete-tree');?>", { page_id: page_id, vivrid: vivr_id },
                        function(json){
                            if(json.success == true) {
                                window.location.reload();
                            } else {
                                alert("Failed to delete tree!!");
                            }
                        });
                }
            }
        },

    });

    $('#add_root').click(function(){
        requestNewWindow('<?php echo $this->url('task=vivr-panel&act=add-root-page&vivrid='.$vivr_id);?>') ;
    });

</script>