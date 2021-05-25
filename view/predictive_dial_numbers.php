<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
<style>
    table{font-size: 12px !important;  white-space: nowrap;width: 1%;}
</style>

<script type="text/javascript">
function deleteNumber(id, skillid, num1)
{
    var ctext = 'Are you sure to delete number : ' + num1 + '?';
    if (confirm(ctext)) {
        window.location = "<?php echo $this->url('task='.$request->getControllerName()."&act=delnumber&page=".$pagination->current_page);?>"+"&numid="+id+"&num="+num1+"&sid="+skillid;
    }
	return false;
}
</script>
<?php
$colspan = 17;
?>


<table class="table" style="margin-bottom: 0px">
<tr>
	<td class="col-md-4" style="padding-top: 24px">
		<?php
            echo 'Record(s) ' . $pagination->getCurrentRecordsIndex() . ' of <b>' . $pagination->num_records . '</b> &nbsp;::&nbsp; ' .
                'Page <b>' . $pagination->current_page . '</b> of <b>' . $pagination->getTotalPageCount() . '</b>';
        ?>
	</td>
    <td>
        <div class="form-group col-md-6 " style="margin-bottom: 0px">
            <form action="" class="from" method="post">
                <div class="input-group">
                    <input type="search" id="search"  name="number_1" class="search form-control" value="<?php echo $request->getRequest('number_1');?>" placeholder="Search Number">
                    <span class="input-group-btn">
                    <button type="submit" class="icon btn-success btn "><i class="fa fa-search"></i></button>
                </span>
                </div>
            </form>
        </div>
    </td>
</tr>
</table>


<div class="table-responsive">
<table class=" table table-bordered table-hover table-striped table-condensed">
<tr class="">
	<th class="text-center">SL</th>
	<th class="text-center">Customer ID</th>
	<th class="text-center">Agent ID</th>
	<th class="text-center">Skill</th>
	<th class="text-center">Number 1</th>
	<th class="text-center">Number 2</th>
	<th class="text-center">Number 3</th>
	<th class="text-center">Name</th>
	<th class="text-center">Street</th>
	<th class="text-center">City</th>
	<th class="text-center">State</th>
	<th class="text-center">Zip</th>
	<th class="text-center">Custom 1</th>
	<th class="text-center">Custom 2</th>
	<th class="text-center">Custom 3</th>
	<th class="text-center">Custom 4</th>
    <th class="text-center">Try Cnt.</th>
	<th class="text-center">Status</th>
	<th class="text-center">Disp.</th>
	<th class="text-center">Action</th>

</tr>

<?php if (is_array($numbers)):?>

<?php
    $i = $pagination->getOffset();
    foreach ($numbers as $num):
?>

<tr class="">
	<td class="cntr">&nbsp;<?php echo $i;?></td>
	<td align="left">&nbsp;<?php echo $num->customer_id;?></td>
	<td align="left"> <?php echo $num->agent_id; if (!empty($num->agent_altid)) {
    echo ' - ' . $num->agent_altid;
} ?></td>
	<td align="left"> <?php echo !empty($skills[$num->skill_id]) ? $skills[$num->skill_id] : $num->skill_id;?></td>
	<td align="left">&nbsp;<?php echo $num->number_1;?></td>
	<td align="left">&nbsp;<?php echo $num->number_2;?></td>
	<td align="left">&nbsp;<?php echo $num->number_3;?></td>
	<td align="left">&nbsp;<?php echo $num->title . ' ' . $num->first_name . ' ' . $num->last_name;?></td>
	<td align="left">&nbsp;<?php echo $num->street;?></td>
	<td align="left">&nbsp;<?php echo $num->city;?></td>
	<td align="left">&nbsp;<?php echo $num->state;?></td>
	<td align="left">&nbsp;<?php echo $num->zip;?></td>
	<td align="left">&nbsp;<?php echo $num->custom_value_1;?></td>
	<td align="left">&nbsp;<?php echo $num->custom_value_2;?></td>
	<td align="left">&nbsp;<?php echo $num->custom_value_3;?></td>
	<td align="left">&nbsp;<?php echo $num->custom_value_4;?></td>
    <td align="left">&nbsp;<?php echo $num->retry_count;?></td>
	<td align="left">&nbsp;<?php echo $dial_status_options[$num->dial_status];?></td>
	<td align="left">&nbsp;<?php echo $num->disposition;?></td>
	<td class="cntr" style="text-align: center;">&nbsp;
	    <a href="#" onClick="return deleteNumber('<?php echo $num->id;?>', '<?php echo $num->skill_id;?>', '<?php echo $num->number_1;?>');"><img src="image/cancel.png" class="bottom" border="0" width="14" height="14" title="Delete" /></a>
    </td>
</tr>
<?php endforeach;?>
</table>
    
<table class="report_extra_info">
<tr>
	<td><?php echo $pagination->createLinks();?></td>
</tr>
<?php else:?>
<tr class="report_row_empty">
	<td colspan="<?php echo $colspan;?>" style="font-weight: normal;">No number found!</td>
</tr>
<?php endif;?>
</table>
</div>

<script type="text/javascript">
    $(function () {
        $(document).on('click','.panel_actions',function () {
            $("#MainLoader").remove();
        });
    });
	var luser = '<?php echo UserAuth::getCurrentUser();?>';
</script>