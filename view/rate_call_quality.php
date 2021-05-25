
<?php
    $action_url = $this->url("task=cdr&act=rate-call-quality");//var_dump($current_ratings);
?>
<style>
    .pos_rel{position:relative; float:left;}
    .counter {
        background-color: rgba(0, 0, 0, 0.8);  color: white;  font-size: 15px;  height: 30px;
        display:none;  line-height: 12px;  text-align:center;  margin-top: -20px;
        min-width: 20px;  padding: 5px;  position: absolute;  right: -36px;  top: 50%;
    }
    .pos_rel:hover .counter{display:block;}
    .counter:before {
        border-color: transparent rgba(0, 0, 0, 0.8) transparent transparent;  border-style: solid;
        border-width: 5px 5px 5px 0;  content: "";  display: block;  height: 0;
        left: -10px;  margin-top: -5px;  position: relative;  top: 50%;  width: 0;
    }
</style>


<link rel="stylesheet" href="js/rating/jquery.rateyo.min.css">
<script src="js/rating/jquery.rateyo.min.js"></script>

<div class="panel panel-default">
    <div class="panel-heading">Rating Profiles</div>
    <div class="panel-body">
        <form class="form" method="post" action="<?php echo $action_url; ?>">
            <input type="hidden" name="callid" value="<?php echo $request->getRequest('callid'); ?>">
            <input type="hidden" name="agent_id" value="<?php echo $request->getRequest('agent_id'); ?>">
            <input type="hidden" name="skill_id" value="<?php echo $request->getRequest('skill_id'); ?>">
            <input type="hidden" name="call_time" value="<?php echo $request->getRequest('call_time'); ?>">

            <div class="col-md-12 col-sm-12 col-lg-12">
                    <div class="col-md-5 col-sm-5 col-lg-5">
                        <label for="" style="margin-top: 7px; font-size: 17px">Call From</label>
                    </div>
                    <div class="col-md-7 col-sm-7 col-lg-7">
                        (972) 740-7203
                    </div>
            </div>
            <div class="col-md-12 col-sm-12 col-lg-12">
                    <div class="col-md-5 col-sm-5 col-lg-5">
                        <label for="" style="margin-top: 7px; font-size: 17px">Served by</label>
                    </div>
                    <div class="col-md-7 col-sm-7 col-lg-7">
                        <?php echo $request->getRequest('agent_id'); ?>  - Agent One
                    </div>
            </div>
            <?php foreach ($quality_profiles as $profile) : ?>
                <div class="col-md-12 col-sm-12 col-lg-12">
                    <div class="col-md-5 col-sm-5 col-lg-5">
                        <label for="" style="margin-top: 7px; font-size: 17px"><?php echo $profile->label; ?></label>
                    </div>
                    <div class="col-md-7 col-sm-7 col-lg-7">
                        <div class="pos_rel">
                        <div id="rateYo-<?php echo $profile->rating_id; ?>" class="col-md-12 col-sm-12"></div>
                        <div class="counter"></div>
                        </div>
                        <input type="hidden" class="rateYo-<?php echo $profile->rating_id; ?>" id="rate-input-<?php echo $profile->rating_id; ?>" name="rating[rating_<?php echo $profile->rating_id?>]" value=""><br><br>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="col-md-12">
                <button type="submit" class="btn btn-success pull-right"><i class="fa fa-save"></i> Save</button>
            </div>
        </form>
    </div>
</div>
<script>
    data = <?php echo json_encode($current_ratings)?>;
    var quality_profiles_data = <?php echo json_encode($quality_profiles)?>;
    $(function () {
        $.each(quality_profiles_data,function (index, item) {
            var id = "rating_"+item.rating_id;
            var rating = 0;
            if (data) {
                $(".rateYo-"+item.rating_id).val(data[id]);
                rating = data[id]/10;
            }
            $("#rateYo-"+item.rating_id).rateYo({
                numStars: 10,
                maxValue: 1,
                fullStar: true,
                rating: rating,
                precision: 1,
                onSet: function (rating, rateYoInstance) {
                    var id = $(this).attr('id');
                    $("."+id).val(rating*10);
                },
                onChange: function (rating, rateYoInstance) {
                    $(this).next().text(rating*10);
                }
            });
        });
    });
</script>

