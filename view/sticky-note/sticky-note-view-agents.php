  <link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
  <!-- <script src="assets/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>  -->

  <?php 
  if (!empty($errMsg)):?>
    <br />
    <?php if ($errType === 0):?>
        <div class="alert alert-success">
    <?php else:?>
        <div class="alert alert-error">
    <?php endif;?> 
    <?php echo $errMsg;?>        
    </div>
<?php endif;?>

<?php if(!empty($sticky_note_agents)){ ?>
<table class="table table-bordered" width="50%">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Id</th>
            <th scope="col">Name</th>
            <th scope="col">Acknowledge</th>
            <th scope="col">Acknowledge Time</th>
        </tr>
    </thead>
    <tbody>
    	<?php      
        foreach ($sticky_note_agents as $key=>$item){
        ?>
        <tr>
            <th scope="row"><?php echo $key+1; ?></th>
            <td><?php echo $item->agent_id; ?></td>
            <td><?php echo $item->name; ?></td>
            <td><?php echo ($item->acknowledged==MSG_YES) ? 'Yes':'No'; ?></td>
            <td><?php echo (!empty($item->acknowledged_at) && $item->acknowledged_at!='0000-00-00 00:00:00') ? $item->acknowledged_at : ''; ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<?php }else{ ?>
    <div class="">
        For this note, there are no agent assign
    </div>
<?php } ?>
