<link rel="stylesheet" href="css/form.css" type="text/css">
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form name="frm_ivr_service" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
<input type="hidden" name="pid" value="<?php if (isset($page_id)) echo $page_id;?>" />
<table class="form_table" border="0" width="550" align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2>Chat Page Information</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" width="20%"><span class="required">*</span> Page ID:</td>
		<td width="80%">
			<input type="text" name="page_id" size="30" maxlength="11" value="<?php echo $service->page_id;?>" />
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><span class="required">*</span> Domain:</td>
		<td>
			<input type="text" name="www_domain" size="30" maxlength="30" value="<?php echo $service->www_domain;?>" />
     	</td>
	</tr>
    <tr class="form_row_alt">
		<td class="form_column_caption"><span class="required">*</span> IP:</td>
		<td>
			<input type="text" name="www_ip" size="30" maxlength="15" value="<?php echo $service->www_ip;?>" />
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><span class="required">*</span> Site Key:</td>
		<td>
			<input type="text" name="site_key" size="30" maxlength="32" value="<?php echo $service->site_key;?>" />
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"><span class="required">*</span> Max Session per Agent:</td>
		<td>
			<input type="text" name="max_session_per_agent" size="30" maxlength="2" value="<?php echo $service->max_session_per_agent;?>" />
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"> Skill:</td>
		<td>
			<select name="skill_id" id="skill_id">
				<?php if (is_array($skills)) {
					foreach ($skills as $skill) {
						echo '<option value="' . $skill->skill_id . '"';
						if (isset($service->skill_id) && $service->skill_id == $skill->skill_id) echo ' selected="selected"';
						echo '>' . $skill->skill_name . '</option>';
					}
				}
				?>
			</select>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"> Language:</td>
		<td>
			<select name="language" id="language">
				<?php if (is_array($languages)) {
					foreach ($languages as $langKey=>$language) {
						echo '<option value="' . $langKey . '"';
						if (isset($service->language) && $service->language == $langKey) echo ' selected="selected"';
						echo '>' . $language . '</option>';
					}
				}
				?>
			</select>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Status:</td>
		<td>
			<select id="active" name="active">
				<option value="N"<?php if ($service->active != 'Y') echo ' selected="selected"';?>>Disable</option>
				<option value="Y"<?php if ($service->active == 'Y') echo ' selected="selected"';?>>Enable</option>
			</select>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption"> Theme:</td>
		<td>
			<select name="theme" id="theme">
				<?php if (is_array($themes)) {
					foreach ($themes as $themeKey=>$themeVal) {
						echo '<option value="' . $themeKey . '"';
						if (isset($service->theme) && $service->theme == $themeKey) echo ' selected="selected"';
						echo '>' . $themeVal . '</option>';
					}
				}
				?>
			</select>
		</td>
	</tr>

	<tr class="form_row_alt">
		<td colspan="2" class="form_column_submit">
			<input class="form_submit_button" type="submit" value="  <?php if (!empty($page_id)):?>Update<?php else:?>Add<?php endif;?>  " name="submitservice"> <br><br>
		</td>
	</tr>
</tbody>
</table>
</form>
