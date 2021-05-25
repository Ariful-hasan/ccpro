<?php if (!empty($msg)) : ?>
    <div class="col-md-12" style="margin: 0px; padding: 5px">
        <div class="alert <?php echo $msg_type ? 'alert-success' : 'alert-danger'; ?> text-center alert-dismissable">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <strong><?php echo $msg; ?></strong>
        </div>
    </div>
<?php endif; ?>


<form method="post" class="form" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$request->getActionName());?>">
    <input type="hidden" name="mobile" value="<?php echo $mobile ?>">
    <input type="hidden" name="callid" value="<?php echo $callid ?>">
    <input type="hidden" name="skill_id" value="<?php echo $skill_id ?>">

    <div class="form-group form-group-sm">
        <label for="title" class="control-label">Template</label>
        <select id="title" name="title" class="form-control">
            <option value="" data-sms="">Select</option>
            <?php if (!empty($templates)) :
                     foreach ($templates as $smstmp) { ?>
                            <option value="<?php echo $smstmp->tstamp ?>" data-sms="<?php echo $smstmp->sms_body ?>"
                                <?php echo $request->getRequest('title') == $smstmp->tstamp ? 'selected': '' ?> > <?php echo $smstmp->title ?></option>
            <?php } endif;  ?>
        </select>
    </div>

    <div class="form-group">
        <textarea class="form-control" name="sms" id="sms" rows="6"></textarea>
    </div>
    <div class="text-right">
        <button type="submit" id="send-sms-btn" class="btn btn-success ">Send</button>
    </div>





<script type="text/javascript">
    $(function() {
        $("#sms").val($('option:selected', this).data('sms'));

        $('#title').change(function(){
            var smsText = $('option:selected', this).data('sms');
            $("#sms").val(smsText);
        });

        $(document).on('click','#send-sms-btn',function (e) {
            e.preventDefault();
            if (!$("#sms").val()){
                alert('Select a SMS Template');
            }else {
                $('.form').submit();
            }
        });
    });

</script>