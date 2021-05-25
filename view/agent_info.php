<?php
$image_src = '';

if (isset($agent->agent_id) && !empty($agent->agent_id)) {
	if (file_exists('agents_picture/'. $agent->agent_id.'.png')) {
		$image_src = 'agents_picture/'.$agent->agent_id.'.png?t=' . time();
	}
}

?>
<link href="css/form.css" rel="stylesheet" type="text/css">
<table class="form_table table" border="0"  align="center" cellpadding="6" cellspacing="0">
<tbody>
	<tr class="form_row_head">
	  <td colspan=2><?php echo UserAuth::hasRole('agent') ? "Agent" : "User"; ?> Information</td>
	</tr>
<?php if (!empty($image_src)):?>
<tr>
<td width="200">
	<img id="agentImageView" class="img-thumbnail" src="<?php echo $image_src;?>" style="width:190px; height:260px;" alt="No image found" />
</td>
<td><table width="100%">
<?php endif;?>
	<tr class="form_row">
		<td class="form_column_caption"><b><?php echo UserAuth::hasRole('agent') ? "Agent" : "User"; ?> ID:</b></td>
		<td>
			<?php echo $agent->agent_id;?>
     	</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption" width="33%">Name:</td>
		<td>
			<?php echo $agent->name;?>
		</td>
	</tr>

	<tr class="form_row">
		<td class="form_column_caption">Nick Name:</td>
		<td>
			<?php echo $agent->nick;?>
		</td>
	</tr>

	<tr class="form_row_alt">
		<td class="form_column_caption" width="33%">Skills:</td>
		<td><?php
		$skills = $agent_model->getAgentSkill($agent->agent_id);
		$num_skills = 0;
		if (is_array($skills)) {
			foreach ($skills as $skill) {
				if ($num_skills > 0) echo ', ';
				echo isset($skill_options[$skill->skill_id]) ? $skill_options[$skill->skill_id] : $skill->skill_id;
				$num_skills++;
			}
		} else {
			echo 'No Skill';
		}
		?>&nbsp;
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Birth Day:</td>
		<td>
			<?php if (empty($agent->birth_day) || $agent->birth_day =='0000' || strlen($agent->birth_day) != 4) {
				echo '-';
			} else {
				$timestamp = mktime(0, 0, 0, substr($agent->birth_day, 0, 2), substr($agent->birth_day, 2, 2), 2013);
				echo date("dS F", $timestamp);
			}?>
		</td>
	</tr>
	<tr class="form_row_alt">
		<td class="form_column_caption">Telephone:</td>
		<td>
			<?php echo $agent->telephone;?>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Email:</td>
		<td>
			<?php echo $agent->email;?>
		</td>
	</tr>
	<tr class="form_row">
		<td class="form_column_caption">Login PIN:</td>
		<td>
			***** <a class="lightboxWIF btn btn-xs btn-info" data-w="500" data-h="300" href="<?php echo $this->url("task=agent&act=reset-login-pin");?>">Reset</a>
		</td>
	</tr>
<?php if (!empty($image_src)):?></table></td></tr><?php endif;?>
</tbody>
</table>
<?php //print_r($_SESSION);?>
