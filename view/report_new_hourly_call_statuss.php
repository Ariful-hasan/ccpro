<div class="table-responsive">
    <form action="<?php echo $this->url("task=report-new&act=hourly-call-status"); ?>" class="form" method="post">
        <table class="table">
            <tr>
                <td><div><label class="control-label" for="sdate">Start Date</label><input type="text" class="form-control gs-date-picker" name="sdate" value="<?php echo date('Y-m-d')?>"></div></td>
                <td><label class="control-label" for="edate">End Date</label><input type="text" class="form-control gs-date-picker" name="edate" value="<?php echo date('Y-m-d')?>"></td>
                <td><button style="margin-top: 25px" type="submit" class="btn btn-md btn-success"><i class="fa fa-search"> Search</button></td>
            </tr>
        </table>
    </form>


<table class="table table-bordered " style="table-layout: auto">
    <tr >
        <td >Date</td>
        <td>Hourly status</td>
        <td>12:00:00 PM</td>
        <td>1:00:00 AM</td>
        <td>2:00:00 AM</td>
        <td>3:00:00 AM</td>
        <td>4:00:00 AM</td>
        <td>5:00:00 AM</td>
        <td>6:00:00 AM</td>
        <td>7:00:00 AM</td>
        <td>8:00:00 AM</td>
        <td>9:00:00 AM</td>
        <td>10:00:00 AM</td>
        <td>11:00:00 AM</td>
        <td>12:00:00 PM</td>
        <td>1:00:00 PM</td>
        <td>2:00:00 PM</td>
        <td>3:00:00 PM</td>
        <td>4:00:00 PM</td>
        <td>5:00:00 PM</td>
        <td>6:00:00 PM</td>
        <td>7:00:00 PM</td>
        <td>8:00:00 PM</td>
        <td>9:00:00 PM</td>
        <td>10:00:00 PM</td>
        <td>11:00:00 PM</td>
    </tr>
    <?php if (!empty($hourly_situations)): ?>
        <?php foreach ($hourly_situations as $date => $situations): ?>
            <?php foreach ($situations as $situation => $hours): ?>
                <tr <?php echo $situation == "Abandoned" ? "style=color:red" : "";  ?> >
                    <?php if ($situation == "Offered") : ?>
                        <td style="vertical-align: middle" <?php echo $situation == "Offered" ? "rowspan='3'" : '' ?>><?php echo $situation == "Offered" ? $date : '' ?></td>
                    <?php endif; ?>
                    <td ><?php echo $situation; ?></td>
                    <?php for ($i=1; $i<=24; $i++) : $i = sprintf("%02d", $i) ?>
                    <td ><?php echo array_key_exists($i,$hours) ? $hours[$i] : 0; ?></td>
                    <?php endfor; ?>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="27" class="text-danger text-center">No Data Found</td></tr>
    <?php endif; ?>
</table>

</div>

<script type="text/javascript">
    $(function(){
        $(document).on("click","#cboxClose",function () {
            location.reload(true);
        });
    });
</script>

