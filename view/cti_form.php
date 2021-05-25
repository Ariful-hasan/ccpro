<link href="css/form.css" rel="stylesheet" type="text/css">
<script>
function loadNumberField()
{
	if (document.getElementById("service").value == 'SQ' || document.getElementById("service").value == 'VM') {
		document.getElementById("service_queue").style.display = 'block';
		document.getElementById("skill_id").style.display = 'block';
		document.getElementById("ivr_text").style.display = 'none';
		document.getElementById("xf_text").style.display = 'none';
		document.getElementById("ivr_id").style.display = 'none';
		document.getElementById("param_id").style.display = 'none';
	} else if (document.getElementById("service").value == 'XF') {
	        document.getElementById("service_queue").style.display = 'none';
		document.getElementById("skill_id").style.display = 'none';
		document.getElementById("ivr_text").style.display = 'none';
		document.getElementById("ivr_id").style.display = 'none';
		document.getElementById("xf_text").style.display = 'block';
		document.getElementById("param_id").style.display = 'block';
	} else {
		document.getElementById("service_queue").style.display = 'none';
		document.getElementById("skill_id").style.display = 'none';
		document.getElementById("ivr_text").style.display = 'block';
		document.getElementById("ivr_id").style.display = 'block';
		document.getElementById("xf_text").style.display = 'none';
		document.getElementById("param_id").style.display = 'block';
	}
}
</script>
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>

<form name="frm_trunk" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="ctiid" value="<?php if (isset($ctiid)) echo $ctiid;?>" />

<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>CTI Information</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption" width="33%"><span class="required">*</span> Name:</td>
		<td>
			<input type="text" name="cti_name" size="30" maxlength="25" value="<?php echo $cti->cti_name;?>" /> 
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Action type: </td>
		<td>
			<?php $this->html_options('action_type', 'service', $action_type_options, $cti->action_type, "loadNumberField();");?>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">
			<div id="service_queue" style="display:none;font-size:15px;"><b>Skill name : </b></div>
			<div id="ivr_text" style="display:block;font-size:15px;"><b>IVR name : </b></div>
			<div id="xf_text" style="display:block;font-size:15px;"><b>Forward Number : </b></div>
		</td>
		<td>
			<select name="skill_id" id="skill_id" style="display: none;">
				<option value="">Select</option>
				<?php
					if (is_array($skill_options)) {
						foreach ($skill_options as $key=>$value) {
							echo '<option value="'.$key.'"';
							if ($key == $cti->skill_id) echo ' selected';
							echo '>'.$value.'</option>';
						}
					}
				?>
			</select>
			<select name="ivr_id" id="ivr_id" style="display: block;">
				<option value="">Select</option>
				<?php
					if (is_array($ivr_options)) {
						foreach ($ivr_options as $key=>$value) {
							echo '<option value="'.$key.'"';
							if ($key == $cti->ivr_id) echo ' selected';
							echo '>'.$value.'</option>';
						}
					}
				?>
			</select>
			<input style="margin-top: 10px;" type="text" id="param_id" name="param" size="30" maxlength="15" value="<?php echo (!empty($cti->param)) ? $cti->ivr_id.str_replace($cti->ivr_id, '', $cti->param) : '';?>" />
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" valign="top">DID(s): </td>
		<td>
			<!-- <textarea type="text" id="did" name="did" style="width:100%;height:80px;" warp><?php echo $cti->did;?></textarea> -->
			<?php 
			$did = !empty($cti->did) ? explode(',', $cti->did) : '';
			$label = !empty($cti->label) ? explode(',', $cti->label) : '';
			?>
			<div class="row">
				<div id="did_from_elm" class="col-md-12 form-inline">
					<?php
					if(!empty($did)){
						foreach ($did as $key => $item) {
					?>
						<div id="did_from_elm_<?php echo $key; ?>" data-elm-num='<?php echo $key; ?>' class="from-elm">
							<div class="form-group mb-2">
						    	<input type="text" class="form-control" id="did_<?php echo $key; ?>" name="did_option[<?php echo $key; ?>][did]" placeholder="Number" value="<?php echo trim($item); ?>">
						  	</div>
						  	<div class="form-group mx-sm-3 mb-2">
						    	<input type="text" class="form-control" id="label_<?php echo $key; ?>" name="did_option[<?php echo $key; ?>][label]" placeholder="Label" value="<?php echo isset($label[$key]) ? trim($label[$key]) : ''; ?>">
						  	</div>
						  	<a href="javascript:void(0)" class="btn btn-danger <?php echo $key > 0 ? '' : 'hide'; ?> did-delete-item"><i class="fa fa-remove"></i></a>
						</div>
					<?php }	}else{ ?>
						<div id="did_from_elm_0" data-elm-num='0' class="from-elm">
							<div class="form-group mb-2">
						    	<input type="text" class="form-control" id="did_0" name="did_option[0][did]" placeholder="Number">
						  	</div>
						  	<div class="form-group mx-sm-3 mb-2">
						    	<input type="text" class="form-control" id="label_0" name="did_option[0][label]" placeholder="Label">
						  	</div>
						  	<a href="javascript:void(0)" class="btn btn-danger hide did-delete-item"><i class="fa fa-remove"></i></a>
						</div>
					<?php }	?>
				</div>
				<div class="col-md-12">					
					<a href="javascript:void(0)" class="btn btn-primary" id="did_add_more">Add More</a>
				</div>
			</div>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Default Language: </td>
		<td>
			<?php 	$this->html_options('language', 'language',$language_option,$cti->language);?>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Status : </td>
		<td>
        	<?php $this->html_options('active', 'active', $status_options, $cti->active);?>
		</td>
	</tr>
<?php /*
	<tr class="form_row">
		<td class="form_column_caption" valign="top">CLI length: </td>
		<td>
			<input style="width:30%; margin-right: 20px;" type="text" name="cli_length" size="5" maxlength="2" value="<?php echo $cti->cli_length;?>" /> 
			CLI padding prefix <input style="width:30%" type="text" name="cli_padding_prefix" size="5" maxlength="1" value="<?php echo $cti->cli_padding_prefix;?>" /> 
		</td>
	</tr>
*/ ?>
	<tr class="form_row_alt">
		<td colspan="2" class="form_column_submit">
			<input class="pull-right form_submit_button" type="submit" value=" Update  " name="submitcti"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>

<script type="text/javascript">
loadNumberField();
elementAddMore('did_add_more', 'did_from_elm', 'did_from_elm', 'from-elm', 'did_option', ['did', 'label'], 'a.did-delete-item');
removeMoreOptios('a.did-delete-item');
</script>