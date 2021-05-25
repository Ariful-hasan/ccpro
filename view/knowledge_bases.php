<link rel="stylesheet" href="css/report.css" type="text/css">
<link rel="stylesheet" href="js/treeview/jquery.treeview.css" type="text/css">

<script src="js/jquery.cookie.js" type="text/javascript"></script>
<script src="js/treeview/jquery.treeview.js" type="text/javascript"></script>
<script src="js/treeview/jquery.treeview.async.js" type="text/javascript"></script>


<script>
function delCode(did)
{
	if (did.length > 0) {
		if (confirm('Are you sure to delete disposition code : ' + did + '?')) {
			window.location = "<?php echo $this->url('task='.$request->getControllerName()."&act=del&page=".$pagination->current_page."&sid=$sid&dcode=");?>" + did;
		}
	}
}

function initTree() {
	$("#knowledges").treeview({
		url: "<?php echo $this->url('task='.$request->getControllerName()."&act=kbchildren");?>"
	});
}

$(document).ready(function(){
	initTree();

	$("#knowledges").on('contextmenu', 'li span', function( event ) {
		event.preventDefault();
		var offset = $(this).offset();
		
		$("#myMenu1").css({ left:event.pageX-60, top:event.pageY - 110})
		console.log(event.pageX + ' ' + event.pageY);
		$("#myMenu1").toggle();


		/*
		var $a = $(this).children("a.edit");

		if ($a.length == 0) {
			$a = $(this).append('<a href="#" class="edit">edit</a>');
		} else {
			$a.html('edit');
		}
*/
		
		//$a.html('<a href="javascript:void(0);" onClick="editrow('+$(this).attr("id")+')">Edit</a>&nbsp;&nbsp;<a href="javascript:void(0);" onClick="deleterow('+$(this).attr("id")+')">Delete</a>');    
		
		//alert('1');
		//$("#myMenu1").contextMenu();
		/*
		$.contextMenu({
	        selector: $(this), 
	        trigger: 'none',
	        callback: function(key, options) {
	            var m = "clicked: " + key;
	            window.console && console.log(m) || alert(m); 
	        },
	        items: {
	            "edit": {name: "Edit", icon: "edit"},
	            "cut": {name: "Cut", icon: "cut"},
	            "copy": {name: "Copy", icon: "copy"},
	            "paste": {name: "Paste", icon: "paste"},
	            "delete": {name: "Delete", icon: "delete"},
	            "sep1": "---------",
	            "quit": {name: "Quit", icon: "quit"}
	        }
	    });
	    */
	});

	$("#myMenu1").click(function (e) {
		alert('e');
	});
	
	$("#knowledges").on('click', 'li span', function( event ) {
		event.preventDefault();
		$("#myMenu1").hide();
/*
		var $a = $(this).children("a.edit");
		if ($a.length > 0) {
			$a.html('');
		}
		*/
	});
	/*
	$('li span').contextMenu('myMenu1', {
		bindings: {
			'tn_edit': function(t) {
				alert('1');
			},
			'tn_delete': function(t) {

				if(confirm('Deleting branch will delete this node with all its children.\n\nAre you sure to delete this branch?')) {
					$.getJSON("<?php echo $this->url('task=ivrs&act=deletenode');?>", { menu: t.id.substring(8), ivrname:ivr_old_name },
						function(json){
							if(json.success == true) {
								window.location.reload();
							} else {
								alert("Failed to delete tree!!");
							}
					});
				}
			}
		}
	});
	*/
});
</script>
<style>
#knowledges li span {
	font-weight: bold;
	font-size: 13px;
	padding: 0 0px 10px 20px;
	background: url("js/treeview/images/file.gif") no-repeat scroll 0 0 rgba(0, 0, 0, 0);
}

#knowledges li span p {
	font-weight: normal;
	font-size: 12px;
	padding: 4px 0px 10px 24px;
}
</style>
<?php
/*
$colspan = 3;
?>
<?php if (is_array($knowledges)):?>
<table class="report_extra_info">
<tr>
	<td>
		<?php
        	echo 'Record(s) ' . $pagination->getCurrentRecordsIndex() . ' of <b>' . $pagination->num_records . '</b> &nbsp;::&nbsp; ' . 
				'Page <b>' . $pagination->current_page . '</b> of <b>' . $pagination->getTotalPageCount() . '</b>';
		?>
	</td>
</tr>
</table>
<?php endif;?>
<table class="report_table" width="60%" border="0" align="center" cellpadding="1" cellspacing="1">
<tr class="report_row_head">
	<td class="cntr">SL</td>
	<td>Title</td>
	<td class="cntr">Action</td>
</tr>

<?php if (is_array($knowledges)):?>

<?php
	$i = $pagination->getOffset();
	$parents = array();
	foreach ($knowledges as $service):
		$i++;
		$_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
		
		//	<td>&nbsp;<?php echo isset($disposition_options[$service->parent_id]) ? $disposition_options[$service->parent_id] : '';</td>
		$descendants = ($service->rgt - $service->lft - 1) / 2;
?>
<tr class="<?php echo $_class;?>">
	<td class="cntr">&nbsp;<?php echo $i;?></td>
	<td align="left">&nbsp;
		<a href="<?php echo $this->url('task='.$request->getControllerName()."&act=update&sid=$sid&did=".$service->kbase_id);?>"><?php if (!empty($parent)) echo $parent . ' -> ';?><?php echo $service->title;?></a>
		<?php if ($descendants > 0) echo ' ['.$descendants.']';?>
	</td>

	<td class="cntr" class="report_row_del"><a href="#" onClick="delCode('<?php echo $service->kbase_id;?>');"><img src="image/cancel.png" class="bottom" border="0" width="14" height="14" title="Delete" /></a>
	</td>
</tr>
<?php endforeach;?>
</table>
<table class="report_extra_info">
<tr>
	<td><?php echo $pagination->createLinks();?></td>
</tr>
<?php else:?>
<tr class="report_row_empty">
	<td colspan="<?php echo $colspan;?>">No record found!</td>
</tr>
<?php endif;?>
</table>
<?php */ ?>
<ul id="knowledges" class="filetree treeview-famfamfam">
<?php
/*
if (is_array($knowledges)) {
	foreach ($knowledges as $service) {
		$descendants = ($service->rgt - $service->lft - 1) / 2;
		echo '<li id="'. $service->kbase_id .'"';
		if ($descendants > 0) echo ' class="hasChildren"';
		echo '><span>'. $service->title;
		if ($descendants > 0) echo ' [' . $descendants . ']';
		if (!empty($service->description)) echo '<p>'.$service->description.'</p>';
		echo '</span>';
		if ($descendants > 0) {
			$kb_model->getKnowledges('', $sid);
			echo '<ul></ul>';
		}
		echo '</li>';
	}
}
*/
draw_tree($knowledges, 0, $sid, $kb_model);
function draw_tree($knowledges, $level, $sid, $kb_model) {
	if (is_array($knowledges)) {
	foreach ($knowledges as $service) {
		$descendants = ($service->rgt - $service->lft - 1) / 2;
		echo '<li id="'. $service->kbase_id .'"';
		if ($descendants > 0) echo ' class="hasChildren"';
		echo '><span>'. $service->title;
		if ($descendants > 0) echo ' [' . $descendants . ']';
		if (!empty($service->description)) echo '<p>'.$service->description.'</p>';
		echo '</span>';
		if ($descendants > 0 && $level <=5) {
			echo '<ul>';
			$_knowledges = $kb_model->getKnowledges($service->kbase_id, $sid);
			$level++;
			draw_tree($_knowledges, $level, $sid, $kb_model);
			echo '</ul>';
		}
		echo '</li>';
	}
	}
}
?>	
</ul>

<style>
.contextMenu ul {
	display: inline;
    border-collapse: collapse;
    border-radius: 0.417em;
    box-shadow: 0 0 5px #091b21;
    padding: 5px;
    background-color: #ECFBD4;
}
.contextMenu ul li {
	display: inline;
	padding: 5px 10px;
}
.contextMenu ul li:hover {
	background-color: #EEEEEE;
	cursor: pointer;
}
</style>
<div class="contextMenu" id="myMenu1" style="display:none;position:absolute;margin-right:5px;">
	<ul>
		<li id="tn_edit"><img src="image/tn_edit.gif" style="vertical-align:text-top;" /> Edit </li>
		<li id="tn_delete"><img src="image/tn_delete.gif" style="vertical-align:text-top;" /> Delete Tree</li>
	</ul>
</div>