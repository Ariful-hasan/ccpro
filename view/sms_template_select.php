<link rel="stylesheet" href="css/report.css" type="text/css">
<style type="text/css">
    .tipr_content
    {
        font: 13px/1.7 'Helvetica Neue', Helvetica, Arial, sans-serif;
        color: #333;
        background-color: #fff;
        color: #333;
        padding: 6px 17px;
        width: 540px;
    }
    .tipr_container_bottom
    {
        display: none;
        position: absolute;
        margin-top: 13px;
        z-index: 1000;
    }
    .tipr_container_top
    {
        display: none;
        position: absolute;
        margin-top: -75px;
        z-index: 1000;
    }
    .tipr_point_top, .tipr_point_bottom
    {
        position: relative;
        background: #fff;
        border: 1px solid #dcdcdc;
    }
    .tipr_point_top:after, .tipr_point_top:before
    {
        position: absolute;
        pointer-events: none;
        border: solid transparent;
        top: 100%;
        content: "";
        height: 0;
        width: 0;
    }
    .tipr_point_top:after
    {
        border-top-color: #fff;
        border-width: 8px;
        left: 20%;
        margin-left: -8px;
    }
    .tipr_point_top:before
    {
        border-top-color: #dcdcdc;
        border-width: 9px;
        left: 20%;
        margin-left: -9px;
    }
    .tipr_point_bottom:after, .tipr_point_bottom:before
    {
        position: absolute;
        pointer-events: none;
        border: solid transparent;
        bottom: 100%;
        content: "";
        height: 0;
        width: 0;
    }
    .tipr_point_bottom:after
    {
        border-bottom-color: #fff;
        border-width: 8px;
        left: 20%;
        margin-left: -8px;
    }
    .tipr_point_bottom:before
    {
        border-bottom-color: #dcdcdc;
        border-width: 9px;
        left: 20%;
        margin-left: -9px;
    }
</style>
<script src="js/jquery.min.js" type="text/javascript"></script>
<script>
    function addText(tstamp)
    {
        parent._tstamp = tstamp;
        parent.$.colorbox.close();
        return false;
    }

    (function($){$.fn.tipr=function(options){var set=$.extend({"speed":200,"mode":"bottom"},options);return this.each(function(){var tipr_cont=".tipr_container_"+set.mode;$(this).hover(function(){var out='<div class="tipr_container_'+set.mode+'"><div class="tipr_point_'+set.mode+'"><div class="tipr_content">'+$(this).attr("data-tip")+"</div></div></div>";$(this).append(out);var w_t=$(tipr_cont).outerWidth();var w_e=$(this).width();var m_l=w_e/2-w_t/2-50;$(tipr_cont).css("margin-left",m_l+"px");$(this).removeAttr("title");
        $(tipr_cont).fadeIn(set.speed)},function(){$(tipr_cont).remove()})})}})(jQuery);

    $(document).ready(function() {
        $('.tip').tipr();
    });
</script>

<table class="report_table" width="60%" border="0" align="center" cellpadding="1" cellspacing="1">

    <?php if (is_array($sms_templates)):?>

        <?php
        $i = 0;
        foreach ($sms_templates as $sms_template):
            $i++;
            $_class = $i%2 == 1 ? 'report_row' : 'report_row_alt';
            $smsBody = $sms_template->sms_body;
            ?>
            <tr class="<?php echo $_class;?>">
                <td class="cntr">&nbsp;<?php echo $i;?></td>
                <td align="left" class="tip" data-tip="<?php echo $smsBody; ?>">&nbsp;
                    <a href="#" onclick="return addText('<?php echo $sms_template->tstamp;?>');">
                        <?php echo $sms_template->title;?>
                    </a>
                </td>
            </tr>
        <?php endforeach;?>
    <?php endif;?>
</table>

