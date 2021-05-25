<?php

if($isError) {
	$bgColor = 'red';
} else {
	$bgColor = 'green';
}

if (isset($redirectUri)) {
	echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"1;URL=$redirectUri\">";
}
?>
<style>
.alert {
	font-weight:bold;
}
</style>
<?php if (!empty($msg)):?><br /><?php if (!$isError):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $msg;?></div><?php endif;?>
