<div class="panel panel-default">
    <div class="panel-heading">Date Format</div>
    <div class="panel-body">
        <div class="form-group">
            <label class="radio-inline"><input type="radio" name="date_format" class="date-format" value="d/m/Y">DD/MM/YYYY</label>
            <label class="radio-inline"><input type="radio" name="date_format" class="date-format" value="m/d/Y">MM/DD/YYYY</label>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function(){
        var date_format = getCookie('report_date_format');
        if(date_format == '')
                date_format = 'd/m/Y';
        
        $.each($(".date-format"), function(idx, item){
            if($(item).val() == date_format){
                $(item).attr('checked', 'checked');
            }
        });

        $(".date-format").on('click', function(){
            var date_format = $(this).val();
            if($(this).is(':checked')){
                setCookie('report_date_format', date_format, 30, '/');
            }
        });
    });
</script>
