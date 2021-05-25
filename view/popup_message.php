<script type="text/javascript">
function closeWindow() {
    console.log('<?php echo $redirectURL?>');
    <?php if (!empty($redirectURL)){?>
        parent.location.href = '<?php echo $redirectURL?>';
    <?php } else {?>

        <?php if (isset($refreshParent)):?>
        parent.location.href = parent.location.href;
        //if (window.opener.progressWindow) {
            //window.opener.progressWindow.close();
        //}
        <?php endif;?>
        parent.location.href = parent.location.href;
        //parent.$.colorbox.close();

    <?php }?>
}
</script>
<style>
div.alert {
	text-align:center;
	background-image:none;
}
</style>
<?php if (!empty($message)):?><br /><?php if ($msgType == 'success'):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $message;?></div><?php endif;?>
<p align="center">
	<a href="javascript:closeWindow()" class="btn">Close</a>
</p>
