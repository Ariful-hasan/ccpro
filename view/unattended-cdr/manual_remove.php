<link rel="stylesheet" href="css/bootstrap.min.css" />
<style>
</style>

<div class="dispositionForm">
    <form method="post" action="<?php echo $this->url("task=unattended-cdr&act=manually-remove"); ?>">
        <div class="form-group" >
            <label for="dispositionId">Disposition:</label>
            <select class="form-control" id="dispositionId" name="dispositionId" required>
                <?php
                foreach ($dispositionList as $data) {
                    echo "<option value='{$data->disposition_id}'> {$data->title} </option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="comment">Comment:</label>
            <textarea class="form-control" name="comment" id="comment" rows="2"></textarea>
        </div>
        <div class="form-group">
            <input type="hidden" name="callid" value="<?php echo $callId ?>" />
            <input class="btn btn-success" type="submit" value="Submit" />
        </div>
    </form>
</div>


<?php
if ($error == 'success') {
    echo "<div class='alert alert-success error'>Disposition Updated Successfully!</div>";
} else if ($error == 'failed') {
    echo "<div class='alert alert-danger error'>Disposition Update Failed!</div>";
}
?>

<script>
    $(function () {
        setTimeout(ResizeWindow, 500);
        if ($('div.error').length) {
            $(".dispositionForm").hide();
        }
    });

    function ResizeWindow(){
        try{
            parent.$.colorbox.resize({
                innerHeight: '480px',
                innerWidth: '480px'
            });
        }catch(e){}
    }
</script>

