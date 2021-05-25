<?php

if (UserAuth::isLoggedIn()) {
	$menu_list = UserAuth::getCurrentUserMenu();
	$menu_list = json_decode($menu_list, false);
	include('model/MMenuMyJobs.php');
	$myJobModel = new MMenuMyJobs();
	$myjob_count_list = $myJobModel->getMyJobs(UserAuth::getCurrentUser());

	$isLoginAgentEmailSkill = false;
	$isLoginAgentChatSkill = false;
	if(UserAuth::getRoleID() == 'A'){
		include_once('model/MSkill.php');
		$skillModel = new MSkill();
		$email_skill_agent_list = $skillModel->getAgentAndSkill(UserAuth::getCurrentUser(), 'E');
		$chat_skill_agent_list = $skillModel->getAgentAndSkill(UserAuth::getCurrentUser(), 'C');
		
		// var_dump($email_skill_agent_list);
		// var_dump($chat_skill_agent_list);

		if(!empty($email_skill_agent_list)){
			$isLoginAgentEmailSkill = true;
		}
		if(!empty($chat_skill_agent_list)){
			$isLoginAgentChatSkill = true;
		}
	}
?>
	<ul class="wraplist" style="height: auto;">
		<?php echo Helper::LeftMenu($menu_list, $this->url(), $myjob_count_list, $isLoginAgentEmailSkill, $isLoginAgentChatSkill); ?>
	</ul>
<?php } ?>