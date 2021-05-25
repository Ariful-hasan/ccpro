<link rel="stylesheet" href="ccd/select2/select2.min.css">

    <?php if(!empty($msg)) : ?>
        <div class="alert <?php echo $msgType ? 'alert-success' : 'alert-danger'?>">
            <?php echo $msg ?>
        </div>
    <?php endif; ?>


    <div class="panel panel-default">
        <div class="panel-heading">Update Agent Priority</div>
        <div class="panel-body">
            <form action="" method="post">
                <div class="form-group">
                    <label for="agent_id" class="control-label">Agent</label>
                    <select name="agent_id" id="agent_id" class="form-control select2">
                        <?php if (!empty($agents)){
                            $requested_agent_id = $request->getRequest('agent_id','*');
                            foreach ($agents as $agent_id => $agent_name){ ?>
                                <option value="<?php echo $agent_id?>" <?php echo $agent_id == $requested_agent_id ? 'selected' : '' ?>> <?php echo $agent_name ?></option>
                            <?php
                            }
                        }?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="skill_priority" class="control-label"> Skill Priority</label>
                    <textarea name="skill_priority" id="skill_priority" class="form-control" cols="30" rows="10"><?php

                        $requested_skill_priority = $request->getRequest('skill_priority');

                        echo !empty($requested_skill_priority) ? $requested_skill_priority :
                            (!empty($skill_priority[$agent]->skill_priority) ? $skill_priority[$agent]->skill_priority : '') ;

                        ?></textarea>
                    <span class="text-danger"><?php echo !empty($error) ? 'Skill(s) '. $error .' are not found in system.' : ''?></span>
                </div>
                <button type="submit" class="btn btn-success pull-right"><i class="fa fa-save"></i> Add</button>
            </form>
        </div>
    </div>


    <script src="ccd/select2/select2.min.js"></script>
    <script>
        $(function () {
            $(".select2").select2();

            $(document).on('change', '#agent_id', function(){
                $.ajax({
                    url: "<?php echo $this->url('task=priority&act=get-agent-skill-priority')?>",
                    data: {agent_id: $("#agent_id").val()},
                    dataType:'JSON',
                    type: 'POST',
                    success: function(result){
                        $("#skill_priority").html(result.skill_priority);
                    }
                });
            });
        });
    </script>
