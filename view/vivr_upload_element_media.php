<link href="js/mini-music-player/css/styles.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="js/mini-music-player/js/musicplayer.js"></script>

<form method="post"
      action="<?php echo $this->url('task=vivr-panel&act=upload-element-media-file&vivrid=' . $vivr_id . '&page_id=' . $page_id. '&element_id=' . $element_id); ?>"
      enctype="multipart/form-data">
    <div>
        <div class="row">
            <div class="col-sm-6">
                <label> Bangla: </label>
                <?php
                if ($audio_file_bn != '') {
                    echo " <label>$audio_file_bn</label> ";
                    echo " <a href=". $this->url('task=vivr-panel&act=delete-vivr-element-file&language=BN&element_id=' . $element_id). " attr-lang-title='$audio_file_bn' oncompleted='DeleteResponse' msg='Are you sure to delete?' class='ConfirmAjaxWR btn btn-xs btn-danger delete-url' > <i class='fa fa-times'></i> </a> ";
                }
                ?>
                <input type="file" name="audio_file_bn"/>
            </div>
            <div class="col-sm-6">
                <label>English:</label>
                <?php
                if ($audio_file_en != '') {
                    echo "<label>$audio_file_en</label> ";
                    echo "<a href=". $this->url('task=vivr-panel&act=delete-vivr-element-file&language=EN&element_id=' . $element_id). "  attr-lang-title='$audio_file_en' oncompleted='DeleteResponse' msg='Are you sure to delete?' class='ConfirmAjaxWR btn btn-xs btn-danger delete-url' > <i class='fa fa-times'></i> </a>";
                }
                ?>
                <input type="file" name="audio_file_en"/>
            </div>
        </div>
        <hr/>
        <div style="padding: 10px">
            <button type="submit" class="btn btn-success" name="submit_file"> Upload <?php echo $buttonTitle; ?></button>
            <a class="btn btn-info" href="<?php echo $this->url('task=vivr-panel&act=node-elements&vivrid=' . $vivr_id . '&page_id=' . $page_id); ?>"> Back </a>
        </div>

        <div style="padding: 10px">
            <?php //error msg ?>
        </div>
</form>
<?php
if ($audio_file_bn != '' || $audio_file_en != '') {?>
    <div class="music-player col-sm-12">
        <ul class="playlist">
            <?php
            $isFileExists = isFileExists($audio_file_bn, $file_path);
            if ($isFileExists) {
                ?>
                <li class="<?php echo $audio_file_bn; ?>-media-file"
                    data-cover="js/mini-music-player/images/music.png" data-artist="<?php echo $audio_file_bn; ?>">
                    <a href="<?php echo $file_path . $audio_file_bn; ?>"><?php echo $audio_file_bn; ?></a>
                </li>
                <?php
            }
            $isFileExists = isFileExists($audio_file_en, $file_path);
            if ($isFileExists) {
                ?>
                <li class="<?php echo $audio_file_en; ?>-media-file"
                    data-cover="js/mini-music-player/images/music.png" data-artist="<?php echo $audio_file_en; ?>">
                    <a href="<?php echo $file_path . $audio_file_en; ?>"><?php echo $audio_file_en; ?></a>
                </li>
                <?php
            }
            ?>
        </ul>
    </div>

    <?php
}
?>
<script>
    function DeleteResponse(rdta,obj){
        if(rdta.status){
            var langKey = obj.context.getAttribute('attr-lang');
            var langTitle = obj.context.getAttribute('attr-lang-title');

            // jQuery("."+langKey+"-media-file-name").text(langTitle);
            jQuery(".playlist li."+langTitle+"-media-file").remove();
            obj.remove();
            jQuery(".player .controls div.fwd").click();
            jQuery(".player .controls div.stop").click();
        }
        alert(rdta.msg);
        // history.back()
        // window.location.reload(true);
    }
    $(function () {
        setTimeout(ResizeWindow, 500);
    });

    function ResizeWindow() {
        try {
            parent.$.colorbox.resize({
                innerHeight: '480px',
                innerWidth: '800px'
            });
        } catch (e) {
        }
    }

    $(".music-player").musicPlayer({
        //volume: 10,
        //elements: ['artwork', 'controls', 'progress', 'time', 'volume'],
        //playerAbovePlaylist: false,
        onLoad: function () {
            //Add Audio player
            plElem = "<div class='pl'></div>";
            $('.music-player').find('.player').append(plElem);
            // show playlist
            $('.pl').click(function (e) {
                e.preventDefault();
                $('.music-player').find('.playlist').toggleClass("hidden");
            });
        },

    });
</script>