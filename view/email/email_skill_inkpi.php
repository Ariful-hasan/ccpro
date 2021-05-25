<style>
    .center{text-align: center!important;}
    .font-25{font-size: 25px}
</style>
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>
<form  class="form-horizontal" method="post" action="<?php echo $this->url('task='.$request->getControllerName()."&act=".$this->getActionName());?>">
    <div class="panel panel-info">
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-12 form-group">
                    <label for="skill_inkpi_time" class="col-md-4 control-label">In Kpi Time (sec)</label>
                    <div class="col-md-6">
                        <input placeholder="seconds only" autocomplete="off" class="form-control" type="text" step="any" min="1" id="skill_inkpi_time" name="skill_inkpi_time" value="<?php echo $inkpi?>">
                        <span id="helpBlock" class="help-block text-danger" style="color: red">if you skill need a specific in-kpi time. If empty it takes global In Kpi time</span>
                    </div>

                    <div class="col-md-2">
                        <input class="form-control bold center font-25" disabled type="text" id="tstamp_to_time" value="<?php echo gmdate("H:i:s", $inkpi)?>" >
                        <span id="helpBlock" class="help-block text-danger center" style="color: red">hh:mm:ss</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12 text-center">
                    <input class="btn btn-success form_submit_button" type="submit" value="<?php echo empty($inkpi) ? 'Save' : 'Update' ?>">
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="old_inkpi" value="<?php echo $inkpi?>">
    <input type="hidden" name="sid" value="<?php echo $sid?>">

</form>

<script>
    $(document).ready(function () {
        var intRegex = /^\d+$/;
        $("form").submit(function (e) {
            let in_kpi = $("#skill_inkpi_time").val();
            if(in_kpi.length > 0 && !intRegex.test(in_kpi)) {
                alert("Only Digits!!!!");
                return false;
            }
        });

        $("#skill_inkpi_time").on('keyup', function () {
            let totalSeconds = $(this).val();

            let hours = Math.floor(totalSeconds / 3600);
            hours = hours < 10 ? '0'+hours : hours;
            totalSeconds %= 3600;

            let minutes = Math.floor(totalSeconds / 60);
            minutes = minutes < 10 ? '0'+minutes : minutes;

            let seconds = totalSeconds % 60;
            seconds = seconds < 10 ? '0'+seconds : seconds;

            let formated_time = hours+':'+minutes+':'+seconds;
            $("#tstamp_to_time").val(formated_time);
            console.log(formated_time);
        });

        // Allow digits only, using a RegExp
        $("#skill_inkpi_time").inputFilter(function(value) {
            return /^\d*$/.test(value);
        });
    });

    (function($) {
        $.fn.inputFilter = function(inputFilter) {
            return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
                if (inputFilter(this.value)) {
                    this.oldValue = this.value;
                    this.oldSelectionStart = this.selectionStart;
                    this.oldSelectionEnd = this.selectionEnd;
                } else if (this.hasOwnProperty("oldValue")) {
                    this.value = this.oldValue;
                    this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
                } else {
                    this.value = "";
                }
            });
        };
    }(jQuery));

</script>

