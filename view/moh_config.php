<link rel="stylesheet" href="js/treeview/jquery.treeview.css" />
<link href="css/report.css" rel="stylesheet" type="text/css">
<script src="js/jquery.cookie.js" type="text/javascript"></script>
<script src="js/treeview/jquery.treeview.js" type="text/javascript"></script>
<script src="js/jquery.contextmenu.r2.packed.js" type="text/javascript"></script>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$(".conf-panel").colorbox({
		onClosed:function(){ window.location = window.location.href;},
		iframe:true, width:"530", height:"360"
	});
});
</script>

<script type="text/javascript">

	function requestNewWindow( str_URL, width, height )	{
		$.colorbox({href:str_URL, iframe:true, width:"780", height:"450", onClosed:function(){ window.location = window.location.href;}});
	}

	$(document).ready(function(){

		$("#ivr").treeview({
			persist: "location",
			collapsed: false,
			unique: true
		});

		var ivr_old_name = "<?php echo $skill->skill_name;?>";

	<?php if (!empty($tree_details)):?>

		$('li a').contextMenu('myMenu1', {
			bindings: {
				'tn_edit': function(t) {
					var type=$(t).data('type');							
					requestNewWindow('<?php echo $this->url('task=moh-filler&act=upload-moh-filler&e=1&skill='.$skill->skill_id."&");?>'+ivr_old_name+'&type='+type+'&branch='+ t.id.substring(8)+'&dtmfsign='+t.id.substring(5, 7), 530, 300);
				},
	
				'tn_add': function(t) {					
					var type=$(t).data('type');				
					requestNewWindow('<?php echo $this->url('task=moh-filler&act=upload-moh-filler&skill='.$skill->skill_id."&");?>'+ivr_old_name+'&type='+type+'&branch='+ t.id.substring(8)+'&dtmfsign='+t.id.substring(5, 7), 530, 300);
				},

				'tn_delete_only': function(t) {

					if(confirm('Are you sure to delete only this node?')) {
						$.getJSON("<?php echo $this->url('task=confirm-response&act=delete-mohf-data&pro=dn');?>", { branch: t.id.substring(8), ivrname:ivr_old_name },
							function(json){
								alert(json.msg);
								if(json.status == true) {
									window.location.reload();
								} else {
									alert("Failed to delete node!!");
								}
						});
					}
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
			},

			onShowMenu: function(e, menu) {

				var event_name = $(e.target).attr('id').substring(5, 7);
				var event_dtmf = $(e.target).attr('id').substring(3, 4);
				var branch_depth = $(e.target).attr('id').substring(8).length;
//console.log($(e.target).parent('li').siblings('li').length);
//console.log(event_name);
				if(event_name != '--' && event_name != 'PF' && event_name != 'AN' && event_name != 'UI' && event_name != 'SR' && event_name != 'MC' && event_name != 'CA' && event_name != 'RV' && event_name != 'SV' && event_name != 'SY' && event_name != 'GO' && event_name != 'IF' && event_name != 'SL' && event_name != 'AU') {
					$('#tn_add', menu).remove();
				}

				if (branch_depth >= <?php echo MAX_BRANCH + 1;?>) {//$(e.target).text() != 'Play File'
					$('#tn_add', menu).remove();
				}

				if(event_name == 'AN' || event_name == 'ID') {
					if($(e.target).next().is('ul')) {
						$('#tn_add', menu).remove();
					}
				}
				
				
				if($(e.target).next().is('ul')) {
					$('#tn_delete_only', menu).remove();
				}
				//console.log('Name='+event_name+';');				
				if (event_name == '--'){
					$('#tn_edit', menu).remove();
					$('#tn_delete', menu).remove();
					$('#tn_delete_only', menu).remove();
				}
				/* temporary*/
				//$('#tn_edit', menu).remove();
				$('#tn_delete', menu).remove();

				//if(event_dtmf == 's' || event_dtmf == 'f') $('#tn_delete', menu).remove();
				
				return menu;
			}

		});

	<?php else:?>
		$('li a').contextMenu('myMenu1', {
	
			bindings: {

				'tn_add': function(t) {
					requestNewWindow('<?php echo $this->url('task=ivrs&act=addroot&ivrname=');?>'+ivr_old_name+'&menu='+ t.id.substring(8), 500, 300);
				}
			}
		});

	<?php endif;?>
	});


</script>



<style type="text/css">
#ivr {
	text-align:left;
}
#ivr a {
	text-decoration:none;
}
#tn_edit, #tn_delete, #tn_add , #tn_delete,#tn_delete_only{
	font-size:11px;
}
</style>


<table class="form_table table" width="90%" border="0" align="center" cellpadding="3" cellspacing="1" bordercolor="#D2A652">
	<tr class="form_row_alt">
		<td>
		<ul id="ivr">
		<?php if (!empty($tree_details)) {
			$branch_length = 0;
			$ul_remains_open = 0;
			$li_remains_open = 0;

			$num_lis = array();
			$priv_length = 0;
			//echo '$ivr_dtmf'.$ivr_dtmf.;
			foreach ($tree_details as $_ivr) {				
				$cur_branch_length = strlen($_ivr->branch);
				$dtmf_index = $cur_branch_length-1;
				$ivr_dtmf = strlen($_ivr->branch) == 2 ? $_ivr->branch : $_ivr->branch[$dtmf_index];
				
				switch ($_ivr->event) {
					case 'PF':
						$txt_event = 'Get DTMF';
						break;
					case 'SQ':
						$txt_event = 'Service Queue';
						break;
					case 'SR':
						$txt_event = 'Service Request';
						break;
					case 'SL':
						$txt_event = 'Service Log';
						break;
					case 'AU':
						$txt_event = 'Caller Authenticated';
						break;

					case 'MC':
						$txt_event = 'Module Call';
						break;
					case 'RV':
						$txt_event = 'Read Value';
						break;
					case 'GO':
						$txt_event = 'Go';
						break;
					case 'DD':
						$txt_event = 'Direct Dial';
						break;
					case 'ED':
						$txt_event = 'External Dial';
						break;
					case 'CA':
						$txt_event = 'Call API';
						break;
					case 'SV':
						$txt_event = 'Set Value';
						break;
					case 'SY':
						$txt_event = 'Synth Voice';
						break;

					case 'IF':
						$txt_event = 'Compare';
						break;
					case 'DH':
						$txt_event = 'Day Hour';
						break;
					case 'AN':
						$txt_event = 'Announcement';
						break;
					case 'UI':
						$txt_event = 'User Input';
						break;
					default:
						$txt_event = $_ivr->event;
						break;
				}
				
				if ($_ivr->event == 'PF' || $_ivr->event == 'CA' || $_ivr->event == 'AU' || $_ivr->event == 'AN' || $_ivr->event == 'UI') {
					$txt_event_val = empty($_ivr->event_key) ? 'No' : 'Yes';
				} else if ($_ivr->event == 'DH' || $_ivr->event == 'SV' || $_ivr->event == 'SY' || $_ivr->event == 'IF') {
					$txt_event_val = '-';
				} else if ($_ivr->event == 'SQ') {
					$txt_event_val = isset($skill_options[$_ivr->event_key]) ? $skill_options[$_ivr->event_key] : $_ivr->event_key;
				} else if ($_ivr->event == 'SR' || $_ivr->event == 'SL') {
					$txt_event_val = isset($sr_options[$_ivr->event_key]) ? $sr_options[$_ivr->event_key] : $_ivr->event_key;
				} else if ($_ivr->event == 'GO') {
					$txt_event_val_length = strlen($_ivr->event_key);
					$txt_previous_branch = substr($_ivr->branch, 0, -2);
					if ($txt_event_val_length == 2 && $_ivr->event_key == substr($_ivr->branch, 0, 2)) {
						$txt_event_val = 'Root Node';
					} else if ($txt_previous_branch == $_ivr->event_key) {
						$txt_event_val = 'Previous Node';
					} else {
						$txt_event_val = 'The Node - ' . $_ivr->event_key;
					}
					//$txt_event_val = $txt_event_val_length == 1 ? 'Root Node' : 'Previous Node';
				} else if ($_ivr->event == 'DD') {
					$txt_event_val = $_ivr->event_key;
				}  else if ($_ivr->event == 'ED') {
					$txt_event_val = $_ivr->event_key;
				} else {
					$txt_event_val = $_ivr->event_key;
				}



				if ($cur_branch_length != $priv_length) {
					
					if ($cur_branch_length < $priv_length) {
						
						for ($i = $priv_length; $i >= $cur_branch_length; $i--) {
							if (isset($num_lis[$i])) {
								for ($j = $num_lis[$i]; $j > 0 ; $j--) {
									echo '</li>';
								}
								$num_lis[$i] = 0;
							}
							if ($i != $cur_branch_length) echo '</ul>';
						}
					} else if ($cur_branch_length > $priv_length) {
						echo '<ul>';
					}
				} else {
					$num_lis[$cur_branch_length]--;
					echo '</li>';
				}
				
				echo '<li>';

				if (isset($num_lis[$cur_branch_length])) {
					$num_lis[$cur_branch_length]++;
				} else {
					$num_lis[$cur_branch_length] = 1;
				}

				//if ($cur_branch_length > 1) echo '['.$ivr_dtmf.'] ';
				echo '['.$ivr_dtmf.'] ';
				
				if($_ivr->event=="--"){
					if(strlen($_ivr->branch)==2){
						echo '<string data-type="'.$_ivr->filler_type.'" href="#" id="cl_'.$ivr_dtmf.'_'.$_ivr->event.'_'.$_ivr->branch.'" class="text-success" onclick="return false;">'.htmlspecialchars($_ivr->text).'</string>';
					}else{
					//echo $_ivr->text;
					echo '<a href="#" data-type="'.$_ivr->filler_type.'" id="cl_'.$ivr_dtmf.'_'.$_ivr->event.'_'.$_ivr->branch.'" class="text-info" onclick="return false;">'.htmlspecialchars($_ivr->text).'</a>';
					}
				}else{
					echo '<a href="#" data-type="'.$_ivr->filler_type.'" id="cl_'.$ivr_dtmf.'_'.$_ivr->event.'_'.$_ivr->branch.'" onclick="return false;">'.$txt_event.'</a> ('.htmlspecialchars($txt_event_val).') : '.(!empty($_ivr->arg)?"[".htmlspecialchars($_ivr->arg)."] ":"").htmlspecialchars($_ivr->text);
				}
				
				
				$priv_length = $cur_branch_length;
			}

/*
			$branch_length = 0;
			$num_of_loop = $cur_branch_length-$branch_length;
			
			for ($ul_li_last = 0; $ul_li_last <= $num_of_loop; $ul_li_last++) {
				echo '</li></ul>';
				$li_remains_open--;
				$ul_remains_open--;
			}
*/
			$cur_branch_length = 0;
			if ($cur_branch_length != $priv_length) {
				if ($cur_branch_length < $priv_length) {
					for ($i = $priv_length; $i >= $cur_branch_length; $i--) {
						if (isset($num_lis[$i])) {
							for ($j = $num_lis[$i]; $j > 0 ; $j--) {
								echo '</li>';
							}
							$num_lis[$i] = 0;
						}
						if ($i != $cur_branch_length) echo '</ul>';
					}
				} else if ($cur_branch_length > $priv_length) {
					echo '<ul>';
				}
			}
		} else {
			echo '<li><a href="#" id="rt_0_PF_'.$ivrid.'">IVR Root</a></li>';
		}
		
		?>

		</ul>

		</td>
	</tr>
</table>

<?php if (!empty($tree_details)):?>
<div class="contextMenu" id="myMenu1">
	<ul>
		<li id="tn_edit"><img src="image/tn_edit.gif" /> Edit </li>
		<li id="tn_add"><img src="image/tn_add.gif" /> Add Branch </li>
		<li id="tn_delete_only"><img src="image/tn_delete.gif" /> Delete Node</li>
		<li id="tn_delete"><img src="image/tn_delete.gif" /> Delete Tree</li>
	</ul>
</div>
<?php else:?>
<div class="contextMenu" id="myMenu1">
	<ul>
		<li id="tn_add"><img src="image/tn_add.gif" /> Add Root </li>
	</ul>
</div>
<?php endif;?>
