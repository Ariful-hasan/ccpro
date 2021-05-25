<link rel="stylesheet" href="js/treeview/jquery.treeview.css" />
<link href="css/report.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="js/lightbox/css/colorbox.css" />

<script src="js/jquery.cookie.js" type="text/javascript"></script>
<script src="js/treeview/jquery.treeview.js" type="text/javascript"></script>
<script src="js/jquery.contextmenu.r2.packed.js" type="text/javascript"></script>


<script type="text/javascript">
var isInitialScroll = true;
jQuery(document).ready(function($) {
	$(".conf-panel").colorbox({
		onClosed:function(){ window.location = window.location.href;},
		iframe:true, width:"530", height:"360"
	});

	if ( $.cookie("ivrScrollPosition") !== null) {
		var scrollPos = parseInt($.cookie("ivrScrollPosition"));
		$("html, body").stop().animate({scrollTop:scrollPos}, '800', 'swing', function() { 
			isInitialScroll = false;
		});
	    $.cookie("ivrScrollPosition", '0');
	}else{
		isInitialScroll = false;
	}

	$(window).scroll(function() {
		if(!isInitialScroll){
    		var scrollPos = $(window).scrollTop();
    		$.cookie("ivrScrollPosition", scrollPos);
		}
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

		var ivr_old_name = "<?php echo $ivr->ivr_name;?>";

	<?php if (!empty($ivr_details)):?>

		$('li a').contextMenu('myMenu1', {
			bindings: { 
				'tn_edit': function(t) {
					var nd = t.id.split("_");
					requestNewWindow('<?php echo $this->url('task=ivrs&act=updatenode&ivrname=');?>'+ivr_old_name+'&menu='+ encodeURIComponent(nd[3]) +'&dtmfsign='+encodeURIComponent(nd[1]), 530, 300);
				},
	
				'tn_add': function(t) {
					var nd = t.id.split("_");
					requestNewWindow('<?php echo $this->url('task=ivrs&act=addnode&ivrname=');?>'+ivr_old_name+'&menu='+ encodeURIComponent(nd[3])+'&pevent='+encodeURIComponent(nd[2]), 530, 300);
				},
				'tn_upload': function(t) {
					var nd = t.id.split("_");
					requestNewWindow('<?php echo $this->url('task=ivrs&act=upload-voice-file&ivrname=');?>'+ivr_old_name+'&branch='+ encodeURIComponent(nd[3])+'&pevent='+encodeURIComponent(nd[2]), 530, 300);
				},
				'tn_add_asr': function(t) {
					var nd = t.id.split("_");
					requestNewWindow('<?php echo $this->url('task=ivrs&act=add-ivr-asr-word&ivrname=');?>'+ivr_old_name+'&branch='+ encodeURIComponent(nd[3])+'&pevent='+encodeURIComponent(nd[2]), 530, 300);
				},
				'tn_delete_only': function(t) {

					if(confirm('Are you sure to delete only this node?')) {
						var nd = t.id.split("_");
						$.getJSON("<?php echo $this->url('task=ivrs&act=deleteonlynode');?>", { menu: nd[3], ivrname:ivr_old_name },
							function(json){
								if(json.success == true) {
									window.location.reload();
								} else {
									alert("Failed to delete node!!");
								}
						});
					}
				},
				
				
				'tn_delete': function(t) {

					if(confirm('Deleting branch will delete this node with all its children.\n\nAre you sure to delete this branch?')) {
						var nd = t.id.split("_");
						$.getJSON("<?php echo $this->url('task=ivrs&act=deletenode');?>", { menu: nd[3], ivrname:ivr_old_name },
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

				var nd = $(e.target).attr('id').split("_");
				var event_name = nd[2];
				var event_dtmf = nd[1];
				var branch_depth = nd[3].length;
//console.log($(e.target).parent('li').siblings('li').length);

				//if(event_name != 'PF' && event_name != 'DT' && event_name != 'AN' && event_name != 'UI' && event_name != 'SR' && event_name != 'MC' && event_name != 'CA' && event_name != 'RV' && event_name != 'SV' && event_name != 'SY' && event_name != 'GO' && event_name != 'IF' && event_name != 'SL' && event_name != 'FP' && event_name != 'AU') {
					//$('#tn_add', menu).remove();
				//}

				if (branch_depth >= <?php echo MAX_BRANCH + 1;?>) {//$(e.target).text() != 'Play File'
					$('#tn_add', menu).remove();
				}

				if(event_name == 'AN' || event_name == 'ID') {
					if($(e.target).next().is('ul')) {
						$('#tn_add', menu).remove();
					}
				}
				if(event_name != 'PF' && event_name  != 'AN' && event_name != 'UI' && event_name != 'AT') {
					
						$('#tn_upload', menu).remove();
					
				}
				if(branch_depth < 3) {
					$('#tn_add_asr', menu).remove();
				}
				
				if ($(e.target).parent('li').siblings('li').length > 1 || branch_depth == 1) {
					$('#tn_delete_only', menu).remove();
				}
				

				//if(event_dtmf == 's' || event_dtmf == 'f') $('#tn_delete', menu).remove();
				
				return menu;
			}

		});

	<?php else:?>
		$('li a').contextMenu('myMenu1', {
	
			bindings: {

				'tn_add': function(t) {
					var nd = t.id.split("_");
					requestNewWindow('<?php echo $this->url('task=ivrs&act=addroot&ivrname=');?>'+ivr_old_name+'&menu='+ encodeURIComponent(nd[3]), 500, 300);
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
#tn_edit, #tn_delete, #tn_add,#tn_upload,#tn_add_asr,#tn_delete_only{
	font-size:11px;
}
</style>


<table class="form_table table" width="90%" border="0" align="center" cellpadding="3" cellspacing="1" bordercolor="#D2A652">
	<tr class="form_row_alt">
		<td>
		<ul id="ivr">
		<?php if (!empty($ivr_details)) {
			$branch_length = 0;
			$ul_remains_open = 0;
			$li_remains_open = 0;

			$num_lis = array();
			$priv_length = 0;
			
			foreach ($ivr_details as $_ivr) {
				/*
				$cur_branch_length = strlen($_ivr->branch);
				$dtmf_index = $cur_branch_length-1;
				$ivr_dtmf = $_ivr->branch[$dtmf_index];
				
				switch ($_ivr->event) {
					case 'PF':
						$txt_event = 'Get DTMF';
						break;
					case 'SQ':
						$txt_event = 'Service Queue';
						break;
					case 'MC':
						$txt_event = 'Module Call';
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
					case 'GV':
						$txt_event = 'Get Value';
						break;
					case 'DH':
						$txt_event = 'Day Hour';
						break;
					case 'AN':
						$txt_event = 'Announcement';
						break;
					case 'ID':
						$txt_event = 'Get ID';
						break;
					default:
						$txt_event = $_ivr->event;
						break;
				}
				
				if ($_ivr->event == 'PF' || $_ivr->event == 'GV' || $_ivr->event == 'AN' || $_ivr->event == 'ID') {
					$txt_event_val = empty($_ivr->event_key) ? 'No' : 'Yes';
				} else if ($_ivr->event == 'DH') {
					$txt_event_val = '-';
				} else if ($_ivr->event == 'SQ') {
					$txt_event_val = isset($skill_options[$_ivr->event_key]) ? $skill_options[$_ivr->event_key] : $_ivr->event_key;
				} else if ($_ivr->event == 'GO') {
					$txt_event_val_length = strlen($_ivr->event_key);
					$txt_event_val = $txt_event_val_length == 1 ? 'Root Node' : 'Previous Node';
				} else if ($_ivr->event == 'DD') {
					$txt_event_val = $_ivr->event_key;
				}  else if ($_ivr->event == 'ED') {
					$txt_event_val = $_ivr->event_key;
				} else {
					$txt_event_val = $_ivr->event_key;
				}

				if ($branch_length != $cur_branch_length) {
					if ($cur_branch_length > $branch_length) {
						if ($branch_length != 0) {
							echo '<ul><li>[' . $ivr_dtmf . '] <a id="cl_' . $ivr_dtmf . '_' . $_ivr->event . '_' . $_ivr->branch . '">'.$txt_event.'</a> ('.$txt_event_val.') : '.$_ivr->text;
						} else {
							echo '<ul><li><a href="#" id="cl_'.$ivr_dtmf.'_'.$_ivr->event.'_'.$_ivr->branch.'">'.$txt_event.'</a> ('.$txt_event_val.') : '.$_ivr->text;
						}
						$ul_remains_open = $ul_remains_open+1;
						$li_remains_open = $li_remains_open+1;
					} else {
						$num_of_loop = $branch_length-$cur_branch_length;
						for ($ul_li = 0; $ul_li <= $num_of_loop; $ul_li++) {
							echo '</li></ul>';
							$li_remains_open--;
							$ul_remains_open--;
						}
						echo '<li>['.$ivr_dtmf.'] <a id="cl_'.$ivr_dtmf.'_'.$_ivr->event.'_'.$_ivr->branch.'">'.$txt_event.'</a> ('.$txt_event_val.') : '.$_ivr->text;
						$li_remains_open++;
					}
				} else {
					if ($li_remains_open > 0) {
						echo '</li>';
						$li_remains_open = $li_remains_open-1;
					}
					echo '<li>[' . $ivr_dtmf . '] <a id="cl_' . $ivr_dtmf . '_' . $_ivr->event . '_' . $_ivr->branch . '">'.$txt_event.'</a> ('.$txt_event_val.') : '.$_ivr->text;
					$li_remains_open = $li_remains_open+1;
				}	

				$branch_length = $cur_branch_length;
				*/
				$cur_branch_length = strlen($_ivr->branch);
				$dtmf_index = $cur_branch_length-1;
				//$ivr_dtmf = $_ivr->branch[$dtmf_index];
				$ivr_dtmf = strlen($_ivr->branch) == 2 ? $_ivr->branch : $_ivr->branch[$dtmf_index];
				
				switch ($_ivr->event) {
					case 'PF':
						$txt_event = 'Get DTMF';
						break;
					case 'SQ':
						$txt_event = 'Service Queue';
						break;
					case 'VM':
						$txt_event = 'Voice Mail';
						break;
					case 'SR':
						$txt_event = 'Service Request';
						break;
					case 'SL':
						$txt_event = 'Service Log';
						break;
					case 'FP':
						$txt_event = 'Set Footprint';
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
						$txt_event = 'Dial to Agent';
						break;
					case 'CP':
						$txt_event = 'Marked as Priority Caller';
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
					case 'LN':
						$txt_event = 'Change Language';
						break;
					case 'BY':
						$txt_event = 'Hangup';
						break;
					case 'AN':
						$txt_event = 'Announcement';
						break;
					case 'UI':
						$txt_event = 'User Input';
						break;
					case 'XF':
						$txt_event = 'External Transfer';
						break;
                    case 'VT':
                        $txt_event = 'Voice Tap';
                        break;
					default:
						$txt_event = $_ivr->event;
						break;
				}
				
				if ($_ivr->event == 'PF' || $_ivr->event == 'CA' || $_ivr->event == 'AU' || $_ivr->event == 'AN' || $_ivr->event == 'UI') {
					$txt_event_val = empty($_ivr->event_key) && empty($_ivr->arg2) ? 'No' : 'Yes';
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
				} else if ($_ivr->event == 'VT') {
                    $txt_event_val = $_ivr->event_key;
                } else if ($_ivr->event == 'ED') {
					$txt_event_val = $_ivr->event_key;
				} else {
					$txt_event_val = $_ivr->event_key;
				}
				
				if (empty($txt_event_val)) $txt_event_val = '-';



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
				
				echo '<a href="#" id="cl_'.$ivr_dtmf.'_'.$_ivr->event.'_'.$_ivr->branch.'" onclick="return false;">'.$txt_event.'</a> ('.htmlspecialchars($txt_event_val).') : '.(!empty($_ivr->arg)?"[".htmlspecialchars($_ivr->arg)."] ":"").htmlspecialchars($_ivr->text);
				if(!empty($asr_data[$_ivr->branch]) && isset($asr_data[$_ivr->branch]->words) && !empty($asr_data[$_ivr->branch]->words)){
				?>
					<br /><span><small><?php echo $asr_data[$_ivr->branch]->words; ?></small></span>
				<?php
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

<?php if (!empty($ivr_details)):?>
<div class="contextMenu" id="myMenu1">
	<ul>
		<li id="tn_edit"><img src="image/tn_edit.gif" /> Edit </li>
		<li id="tn_add"><img src="image/tn_add.gif" /> Add Branch </li>
		<li id="tn_upload"><img src="image/tn_add.gif" /> Upload File</li>
		<li id="tn_add_asr"><img src="image/tn_add.gif" /> Add ASR</li>
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
