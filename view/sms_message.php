<!--<link rel="stylesheet" href="css/bootstrap.min.css" />-->
<style>

    .chat_container{
        height: 280px;
        overflow: auto;
    }

    .client {
        width: 100%;
        float: left;

    }

    .client_chat_block {
        float: left;
        padding: 5px;
        margin: 5px;
        border-radius: 10px;
        background-color: rgba(0, 63, 255, 0.08);
    }

    .client_time {
        float: left;
        padding-top: 5px;
        font-size: 12px;
    }

    .client_chat {
        float: left;
        padding: 10px 10px;
        /*font-size: 12px;*/
    }

    .chat_client_logo {
        float: left;
        /*padding-right: 10px;*/
        /*padding-bottom: 5px;*/
    }

    .agent_name{
        font-size: 15px;
        margin-right: 5px;
        margin-left: 15px;
    }
    .client_name {
        font-size: 15px;
        margin-right: 15px;
        margin-left: 10px;
    }


    .agent {
        width: 100%;
        float: right;

    }

    .agent_chat_block {
        float: right;
        padding: 5px;
        margin: 5px;
        border-radius: 10px;
        background-color: rgba(255, 0, 134, 0.1);
    }

    .agent_time {
        float: right;
        padding-top: 5px;
    }

    .agent_chat {
        float: right;
        padding: 10px 10px;
    }

    .chat_agent_logo {
        float: right;
        /*padding-right: 10px;*/
        /*padding-bottom: 5px;*/
        margin-left: 5px;
    }

    .reply-container{
        width: 100%;
        /*position: fixed;*/
        bottom: 0px;
        background-color: white;

    }

    /*.reply_area{*/
        /*width: 100%;*/
        /*height: 80px;*/
        /*font-size: 13px;*/
        /*padding: 10px;*/
    /*}*/
    .reply_btn{
        margin-top: 10px;
    }

</style>
<div class="chat_container">

    <div class="chat_info" style="background-color: rgba(221,250,255,0.31); ">
        <p> <label> Session ID : </label> <?php echo $session_id; ?> </p>
        <p> <label> Call ID : </label> <?php echo $call_id ?> </p>
        <p> <label> Agent ID : </label> <?php echo $agent_id; ?> </p>
    </div>
    <?php
        foreach ($sms_messages as $sms){
            if($sms->agent_id != ''){
                echo '
                 <div class="agent">
                    <div class="agent_chat_block">
                        <img class="chat_agent_logo" src="assets/images/chat_agent_logo.png" width="25px" height="25px">
                        <p class="agent_time"> ' . date("d/M/Y h:i A",strtotime($sms->msg_time))  . ' <b class="agent_name">' . $sms->agent_id . '</b> </p>
                        <br >
                        <p class="agent_chat">' . $sms->message . '</p>
                    </div>
                </div>
                ';
            }else{
                echo '
                  <div class="client">
                    <div class="client_chat_block">
                        <img class="chat_client_logo" src="assets/images/chat_customer.png" width="25px" height="25px">
                        <p class="client_time"> <b class="client_name"> '. $sms->phone_number .' </b> ' . date("d/M/Y h:i A",strtotime($sms->msg_time)) . ' </p>
                        <br >
                        <p class="client_chat"> '. $sms->message .' </p>
                    </div>
                  </div>
                ';
            }
        }
    ?>
</div>

<?php
if($source != 'report'){
?>
<div class="reply-container">
    <div class="input-group">
        <form method="post" action="<?php echo $this->url("task=smslogreport&act=sms-messages&session_id=". $session_id); ?>">
            <textarea type="text" name="reply_msg" class="form-control reply_area" cols="100" placeholder="Type Here To Reply..." ></textarea>
            <input type="hidden" name="session_id" value="<?php echo $session_id; ?>" />
            <button type="submit" class="btn btn-primary reply_btn">Send</button>
        </form>
    </div>

</div>
<?php } ?>

<script>

    $(function () {
        setTimeout(ResizeWindow, 500);
    });

    function ResizeWindow(){
        try{
            parent.$.colorbox.resize({
                innerHeight: '480px',
                innerWidth: '400px'
            });
        }catch(e){}
    }
</script>

