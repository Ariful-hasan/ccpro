
<?php if (!empty($errMsg)):?><br /><?php if ($errType === 0):?><div class="alert alert-success"><?php else:?><div class="alert alert-error"><?php endif;?> <?php echo $errMsg;?></div><?php endif;?>



    <div class="row">
        <div class="col-md-12">
            <div class="col-md-6">
                <table class="table table-bordered table-striped table-hover">
                    <caption><b>Basic Information</b><span class="pull-right"><a title="Predictive Dial Profile" class="btn btn-sm btn-primary m-b-2" href="<?php echo $this->url( "task=predictive-dial&act=add-profile&sid=".$request->getRequest('sid')."&sname=".$request->getRequest('sname'))?>"><?php echo !empty($profile[0]->skill_id) ? '<i class="fa fa-edit"></i> Update ' : '<i class="fa fa-plus"></i> Add '; ?> Info</a></span></caption>
                    <!--<tr> <th><b>Skill :</b></th> <td> <?php /*echo !empty($skills[$request->getRequest('sid')]) ? $skills[$request->getRequest('sid')] : $request->getRequest('sid'); */?></td></tr>-->
                    <tr class=""><th><b>Dial Engine: </b> </th> <td><?php echo !empty($dial_engines[$profile[0]->dial_engine]) ? $dial_engines[$profile[0]->dial_engine] : $profile[0]->dial_engine; ?></td></tr>
                    <tr>
                        <th><b>Try Count for Each Number: </b></th>
                        <td><?php echo $profile[0]->retry_count; ?></td>
                    </tr>
                    <tr>
                        <th><b>Retry Interval:</b></th>
                        <td><?php echo $profile[0]->retry_interval_noanswer; ?> seconds</td>
                    </tr>
                    <tr class=""><th><b>Active Time:</b> </th>
                        <td><?php
                        $stopTimeTxt = '-';
                        $startTimeTxt = '-';
                   	if ($profile[0]->status == 'A') {
                           $startTimeTxt = !empty($profile[0]->start_time) && $profile[0]->start_time != '0000-00-00 00:00:00' ? $profile[0]->start_time : '-';
                           $stopTimeTxt = !empty($profile[0]->stop_time) && $profile[0]->stop_time != '0000-00-00 00:00:00' ? $profile[0]->stop_time : '';
                    	}
			echo $startTimeTxt;
			if (strlen($stopTimeTxt) > 1) {
                           $stopTimeTxt = "<a data-w='600' class='lightboxWIF' href='" . $this->url("task=predictive-dial&act=setetime&sid=".$request->getRequest('sid')) . "'>" .
                               $stopTimeTxt . "</a>";
			   echo ' <br>--- to ---<br> ' . $stopTimeTxt;
			}
                        ?></td>
                    </tr>
                    <tr class=""><th><b>Status:</b> </th> <td><?php
                        $statusTxt = '';
			if (empty($profile[0]->status) || $profile[0]->status == 'N') {
                            $statusTxt = '<a class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure that you want to activate the dialer?\')" ';
                            $statusTxt .= 'href="' . $this->url( "task=predictive-dial&act=start-pd&sid=".$request->getRequest('sid')) . '">'.
                                '<span>Inactive</span></a>';
                    	} elseif ($profile[0]->status == 'A') {
                            $statusTxt = '<a class="btn btn-sm btn-success" onclick="return confirm(\'Are you sure that you want to deactivate the dialer?\')" ';
                            $statusTxt .= 'href="' . $this->url( "task=predictive-dial&act=stop-pd&sid=".$request->getRequest('sid')) . '">'.
                                '<span>Active</span></a>';
                    	} else {
                            $dataStatus = isset($status[$profile[0]->status]) ? $status[$profile[0]->status] : $profile[0]->status;
                            $statusTxt = '<a class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure that you want to activate the dialer?\'" ';
                            $statusTxt .= 'href="' . $this->url( "task=predictive-dial&act=start-pd&sid=".$request->getRequest('sid')) . '">'.
                                "$dataStatus</a>";
                    	}

/*
                    $data->stopTimeTxt = '-';
                                $data->startTimeTxt = '-';
                    if ($data->status == 'A') {
                                        $data->startTimeTxt = !empty($data->start_time) && $data->start_time != '0000-00-00 00:00:00' ? $data->start_time : '-';
                        //$data->stopTimeTxt = "<a data-w='600' class='lightboxWIF' href='" . $this->url("task=campaign&act=setetime&cid=".$data->campaign_id) . "'>";
                        //$data->stopTimeTxt .= $data->stop_time . '</a>';
                        $data->stopTimeTxt = $data->stop_time;
                    }

*/

                    //echo !empty($status[$profile[0]->status]) ? $status[$profile[0]->status] : $profile[0]->status;
                    echo $statusTxt;

                    ?></td></tr>
                    <?php /*
                    <tr class=""><th><b>Action:</b> </th> <td>
                            <a title="Start PD" class="btn btn-sm btn-success m-b-2" href="<?php
                            echo $this->url( "task=predictive-dial&act=start-pd&sid=".$request->getRequest('sid'))?>"> <i class="fa fa-play"></i> Start</a> &nbsp;
                            <a title="Stop PD" class="btn btn-sm btn-danger m-b-2" href="<?php
                            echo $this->url( "task=predictive-dial&act=stop-pd&sid=".$request->getRequest('sid'))?>"> <i class="fa fa-stop"></i> Stop</a>
                        </td></tr>
                    */ ?>
                </table>
            </div>
            
            <div class="col-md-6">
                <table class="table table-bordered table-striped table-hover">
                    <caption><b>Lead Statistics</b><span class="pull-right"><a title="Upload Predictive Dial Numbers" class="btn btn-sm btn-primary m-b-2" href="<?php echo $this->url( "task=predictive-dial&act=add&sid=".$request->getRequest('sid')."&sname=".$request->getRequest('sname'))?>"><i class="fa fa-upload"></i> Upload Numbers</a></span></caption>
                    <?php $total = 0;
                    if (!empty($total_records)) :
                        foreach ($total_records as $record):
                            $total += $record->total;
                            ?>
                            <tr class=""><th><b><?php echo $dial_status_options[$record->dial_status]; ?> Record(s): </b> </th> <td><?php echo $record->total; ?></td></tr>
                            <?php
                        endforeach;
                    endif;
                    ?>
                    <tr class=""><th><b>Total Record(s):</b> </th> <td><?php echo $total; ?> <a title="Predictive Dial Number List" class="btn btn-sm btn-link" href="<?php echo $this->url( "task=predictive-dial&act=numbers&sid=".$request->getRequest('sid')."&sname=".$request->getRequest('sname'))?>"><i class="fa fa-external-link" aria-hidden="true"></i> Numbers</a> <a class="btn btn-sm btn-link download" href="<?php echo $this->url( "task=predictive-dial&act=download&sid=".$request->getRequest('sid')."&sname=".$request->getRequest('sname')."&download=".md5('Predictive Dial:: Numbers'.session_id()))?>"><i class="fa fa-download" aria-hidden="true"></i> Download</a></td></tr>
                    <tr class=""><th>Action:</th><td>
                        <a title="Restart Lead" class="btn btn-sm btn-success m-b-2" onclick="return confirm('Are you sure that you want to restart the lead?');" href="<?php
                            echo $this->url( "task=predictive-dial&act=restart-lead&sid=".$request->getRequest('sid'))?>"> <i class="fa fa-play-circle"></i> Restart Lead</a> &nbsp;
                    </td></tr>
                </table>
            </div>
            
<?php /*
            <div class="col-md-6">
                <table class="table table-bordered table-striped table-hover">
                    <caption><b>Fallback Information</b><span class="pull-right"><a title="Predictive Dial Profile" class="btn btn-sm btn-primary m-b-2" href="<?php echo $this->url( "task=predictive-dial&act=add-profile&sid=".$request->getRequest('sid')."&sname=".$request->getRequest('sname'))?>"><?php echo !empty($profile[0]->skill_id) ? '<i class="fa fa-edit"></i> Update ' : '<i class="fa fa-plus"></i> Add '; ?> Info</a></span></caption>
                    <tr>
                        <th><b>Retry Count: </b></th>
                        <td><?php echo $profile[0]->retry_count; ?></td>
                    </tr>
                    <tr>
                        <th><b>Drop Call Action:</b></th>
                        <td><?php echo !empty($drop_call_action[$profile[0]->drop_call_action]) ? $drop_call_action[$profile[0]->drop_call_action] : $profile[0]->drop_call_action; ?></td>
                    </tr>
                    <tr>
                        <th><b>Voice Mail Action:</b></th>
                        <td><?php echo !empty($vm_action[$profile[0]->vm_action]) ? $vm_action[$profile[0]->vm_action] : $profile[0]->vm_action; ?></td>
                    </tr>
                </table>
            </div>
*/
?>
        </div>
<?php /*
        <div class="col-md-12">

            <div class="col-md-6">
                <table class="table table-bordered table-striped table-hover">
                    <caption><b>Date Time Information </b></caption>
                    <tr>
                        <th><b>Time Zone:</b></th>
                        <td><?php echo $profile[0]->timezone; ?></td>
                    </tr>
                    <tr>
                        <th><b>Start Time: </b></th>
                        <td><?php echo $profile[0]->start_time; ?></td>
                    </tr>
                    <tr>
                        <th><b>Stop Time:</b></th>
                        <td><?php echo $profile[0]->stop_time; ?></td>
                    </tr>
                </table>
            </div>


            <div class="col-md-6">
                <table class="table table-bordered table-striped table-hover">
                    <caption><b>Lead Statistics</b><span class="pull-right"><a title="Upload Predictive Dial Numbers" class="btn btn-sm btn-primary m-b-2" href="<?php echo $this->url( "task=predictive-dial&act=add&sid=".$request->getRequest('sid')."&sname=".$request->getRequest('sname'))?>"><i class="fa fa-upload"></i> Upload Numbers</a></span></caption>
                    <?php $total = 0;
                    if (!empty($total_records)) :
                        foreach ($total_records as $record):
                            $total += $record->total;
                            ?>
                            <tr class=""><th><b><?php echo $dial_status_options[$record->dial_status]; ?> Record(s): </b> </th> <td><?php echo $record->total; ?></td></tr>
                            <?php
                        endforeach;
                    endif;
                    ?>
                    <tr class=""><th><b>Total Record(s):</b> </th> <td><?php echo $total; ?> <a title="Predictive Dial Number List" class="btn btn-sm btn-link" href="<?php echo $this->url( "task=predictive-dial&act=numbers&sid=".$request->getRequest('sid')."&sname=".$request->getRequest('sname'))?>"><i class="fa fa-external-link" aria-hidden="true"></i> Numbers</a> <a class="btn btn-sm btn-link download" href="<?php echo $this->url( "task=predictive-dial&act=download&sid=".$request->getRequest('sid')."&sname=".$request->getRequest('sname')."&download=".md5('Predictive Dial:: Numbers'.session_id()))?>"><i class="fa fa-download" aria-hidden="true"></i> Download</a></td></tr>
                    <tr class=""><th>Action:</th><td>
                        <a title="Restart Lead" class="btn btn-sm btn-success m-b-2" onclick="return confirm('Are you sure that you want to restart the lead?');" href="<?php
                            echo $this->url( "task=predictive-dial&act=restart-lead&sid=".$request->getRequest('sid'))?>"> <i class="fa fa-play-circle"></i> Restart Lead</a> &nbsp;
                    </td></tr>
                </table>
            </div>
            
        </div>
*/
?>
    </div>

    <script>
        $(function () {
            $(document).on('click','.download',function () {
                $("#MainLoader").remove();
            });
        });
    </script>


