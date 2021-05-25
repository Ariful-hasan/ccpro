<?php if ($msg) : ?>
    <div class="alert <?php echo $msg_type ? 'alert-success' : 'alert-danger'; ?> text-center alert-dismissable" style="padding: 15px 0px">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <strong><?php echo $msg; ?></strong>
    </div>
<?php endif; ?>

<?php
    $action_url = empty($mainobj->record_id) ? $this->url('task=report-partition&act=add-partition-records') :
        $this->url('task=report-partition&act=record-update&id='.$mainobj->record_id);
?>

<div class="panel panel-default">
    <div class="panel-heading">Partition Record Information</div>
    <div class="panel-body">
        <form class="form" method="post" action="<?php echo $action_url; ?>">
            <div class="form-group">
                <label for="partition_id" class="control-label">Partition</label>
                <select name="partition_id" id="partition_id" class="form-control">
                    <option value="">---Select---</option>
                    <?php  $partition_selected = !empty($request->getRequest('pid')) ? $request->getRequest('pid') : ''; ?>
                    <?php foreach ($partitions as $id => $label): ?>
                        <option value="<?php echo $id; ?>"  <?php echo $id == $partition_selected ? 'selected' : ''; ?> > <?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php /* ?>       <div class="form-group">
                <label for="type" class="control-label">Type</label>
                <select name="type" id="type" class="form-control">
                    <option value="">---Select---</option>
                    <?php $type_selected = !empty($request->getRequest('type')) ? $request->getRequest('type') : !empty($mainobj->type) ? $mainobj->type : ''; ?>
                    <?php foreach ($types as $id => $title): ?>
                        <option value="<?php echo $id; ?>" <?php echo $id == $type_selected ? 'selected' : ''; ?>> <?php echo $title; ?></option>
                    <?php endforeach; ?>
                </select>
            </div> <?php */ ?>
            <style>
                .list-group-item{padding: 5px 15px}
                #available_skills, #selected_skills,#selected_ivrs,#available_ivrs{
                    width: 100%;
                    height: 95% !important;
                    min-height: 150px !important;
                    border: 1px solid #dddddd;
                    margin-bottom: 20px;
                }
                #hint-container{margin-bottom: 25px}
                .col {
                    flex: 1; /* additionally, equal width */
                    border: solid;
                }

            </style>
            <div class="row" id="hint-container">
                <div class="col-md-12">
                    <tt class="text-danger title lead">* Drag and drop the skills to select/deselect</tt>
                </div>
            </div>
            <div class="row" id="skill-container" style="display: flex; margin-bottom: 25px">
                <div class="col-md-6">
                    <label for="" class="control-label">Available Skill(s)</label>
                    <div class="form-group col" id="available_skills" ondrop="drop(event)" ondragover="allowDrop(event)">
                    </div>
                </div>

                <div class="col-md-6" >
                    <label for="" class="control-label">Selected Skill(s)</label>
                    <div class="form-group col" id="selected_skills" ondrop="drop(event)" ondragover="allowDrop(event)">

                    </div>
                </div>
            </div>

            <div class="row" id="ivr-container" style="display: flex; margin-bottom: 25px">
                <div class="col-md-6">
                    <label for="" class="control-label">Available IVR(s)</label>
                    <div class="form-group col" id="available_ivrs" ondrop="drop(event)" ondragover="allowDrop(event)">
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="" class="control-label">Selected IVR(s)</label>
                    <div class="form-group col" id="selected_ivrs" ondrop="drop(event)" ondragover="allowDrop(event)">

                    </div>
                </div>
            </div>


            <button type="submit" class="btn btn-success pull-right"><i class="fa fa-save"></i><?php echo empty($mainobj) ? ' Create' : ' Update'; ?></button>
        </form>
    </div>
</div>



<script>
    function allowDrop(ev) {
        ev.preventDefault();
    }

    function drag(ev) {
        ev.dataTransfer.setData("text", ev.target.id);
    }

    function drop(ev) {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("text");
        console.log($("#"+ev.target.id));
        if(ev.target.id == "selected_skills" || ev.target.id == "selected_ivrs" || ev.target.id == "available_skills" || ev.target.id == "available_ivrs" ){

            ev.target.append(document.getElementById(data));
        }else {
            ev.target.after(document.getElementById(data));
        }

        if(ev.target.id == "selected_skills")
        {
            $("#"+data +" input").attr("name","selected_skills[]");
        }
        else if (ev.target.id == "selected_ivrs")
        {
            $("#"+data +" input").attr("name","selected_ivrs[]");
        }
        else {
            $("#"+data +" input").removeAttr("name");
        }
    }
    $(function () {
        //$("#ivr-container,#skill-container").hide();

        var exixting_skill_and_ivrs = '<?php echo json_encode($exixting_skill_and_ivrs)?>';
            exixting_skill_and_ivrs = JSON.parse(exixting_skill_and_ivrs);

        var available_skills = '<?php echo json_encode($available_skills); ?>';
            available_skills = JSON.parse(available_skills);
            var available_skills_copy =  JSON.parse(JSON.stringify(available_skills));

        var available_ivrs = '<?php echo json_encode($available_ivrs); ?>';
            available_ivrs = JSON.parse(available_ivrs);
        var available_ivrs_copy = JSON.parse(JSON.stringify(available_ivrs));

        if(exixting_skill_and_ivrs)
        {
            $.each(exixting_skill_and_ivrs,function (index, single_record) {
                var record_id = single_record.record_id;
                if(single_record.type == "SQ" && available_skills.hasOwnProperty(record_id))
                {
                    delete available_skills[record_id];
                    $("#selected_skills").append("<div draggable='true' ondragstart='drag(event)' id='skill-option-"+record_id+"' class='list-group-item' data-skill_id='"+ record_id +"'>"+ available_skills_copy[record_id] + "<input type='hidden' name='selected_skills[]' value='"+record_id+"'></div>");
                }
                if(single_record.type == "IV" && available_ivrs.hasOwnProperty(record_id))
                {
                    delete available_ivrs[record_id];
                    $("#selected_ivrs").append("<div draggable='true' ondragstart='drag(event)' id='ivr-option-"+record_id+"' class='list-group-item' data-skill_id='"+ record_id +"'>"+ available_ivrs_copy[record_id] + "<input type='hidden' name='selected_ivrs[]' value='"+record_id+"'></div>");
                }
            });

        }



        $.each(available_skills,function (index,value) {
            $("#available_skills").append("<div draggable='true' ondragstart='drag(event)' id='skill-option-"+index+"' class='list-group-item' data-skill_id='"+ index +"'>"+ value + "<input type='hidden'  value='"+index+"'></div>");
        });

        $.each(available_ivrs,function (index,value) {
            $("#available_ivrs").append("<div draggable='true' ondragstart='drag(event)' id='ivr-option-"+index+"' class='list-group-item' data-skill_id='"+ index +"'>"+ value + "<input type='hidden'  value='"+index+"'></div>");
        });

     /*   $(document).on("change","#type",function () {
            var type = $(this).val();
            console.log(type);
            if (type == "IV")
            {
                $("#skill-container").hide();
                $("#ivr-container").show();
            }else if(type == "SQ")
            {
                $("#ivr-container").hide();
                $("#skill-container").show();
            }
            else if(type == '')
            {
                $("#skill-container,#ivr-container").hide();
            }
        }); */


    });
</script>

