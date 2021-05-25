<link href="assets/plugins/jplayer/skin/blue.monday/css/jplayer.blue.monday.min.css" rel="stylesheet" type="text/css" />
<link href="css/form.css" rel="stylesheet" type="text/css">
<script src="js/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript" src="assets/plugins/jplayer/jplayer/jquery.jplayer.min.js"></script>
<script type="text/javascript">

jQuery(document).ready(function($) {
	ProcessVideo('<?php echo $cid;?>');
});

function ProcessVideo(cid)
{
	// $.post('<?php //echo $this->url('task='.$request->getControllerName()."&act=processvideo"); ?>', { 'cid':cid },
	$.post('<?php echo $video_path; ?>', { 'callid':cid, 'type': 'video' },
		function(data, status) {
			var resp_data = JSON.parse(data);
			// console.log(data);
			// console.log(status);

			if (status == 'success') {
				if(resp_data.result==true && resp_data.url != '') {
					$("#pre_loading").hide();
					$("#jp_container_1").show();

					$("#jquery_jplayer_1").jPlayer({
						ready: function () {
							$(this).jPlayer("setMedia", {
								title: cid,
								m4v: resp_data.url,
							});
						},
						swfPath: "assets/plugins/jplayer/jplayer",
						supplied: "webmv, ogv, m4v",
						size: {
							width: "480px",
							height: "360px",
							cssClass: "jp-video-270p"
						},
						useStateClassSkin: true,
						autoBlur: false,
						smoothPlayBar: true,
						keyEnabled: true,
						remainingDuration: true,
						toggleDuration: true
					});
				}
			} else {
				alert("Failed to communicate!");
			}
		}
	);
}

</script>


<table class="form_table">
	<!-- <tr class="form_row_head"><td>Priority 1:</td></tr> -->
	<tr class="form_row">
		<td colspan="2">
			<div id="pre_loading" style="text-align: center;">	
				<img src="assets/images/5.gif">				
			</div>
		 	<!-- <video id="video" width="320" height="240"></video> -->
		 	<div id="jp_container_1" class="jp-video jp-video-270p" role="application" aria-label="media player" style="margin-left: 37%; display: none;">
				<div class="jp-type-single">
					<div id="jquery_jplayer_1" class="jp-jplayer"></div>
					<div class="jp-gui">
						<div class="jp-video-play">
							<button class="jp-video-play-icon" role="button" tabindex="0">play</button>
						</div>
						<div class="jp-interface">
							<div class="jp-progress">
								<div class="jp-seek-bar">
									<div class="jp-play-bar"></div>
								</div>
							</div>
							<div class="jp-current-time" role="timer" aria-label="time">&nbsp;</div>
							<div class="jp-duration" role="timer" aria-label="duration">&nbsp;</div>
							<div class="jp-controls-holder">
								<div class="jp-controls">
									<button class="jp-play" role="button" tabindex="0">play</button>
									<button class="jp-stop" role="button" tabindex="0">stop</button>
								</div>
								<div class="jp-volume-controls">
									<button class="jp-mute" role="button" tabindex="0">mute</button>
									<button class="jp-volume-max" role="button" tabindex="0">max volume</button>
									<div class="jp-volume-bar">
										<div class="jp-volume-bar-value"></div>
									</div>
								</div>
								<div class="jp-toggles">
									<button class="jp-repeat" role="button" tabindex="0">repeat</button>
									<button class="jp-full-screen" role="button" tabindex="0">full screen</button>
								</div>
							</div>
							<div class="jp-details">
								<div class="jp-title" aria-label="title">&nbsp;</div>
							</div>
						</div>
					</div>
					<div class="jp-no-solution">
						<span>Update Required</span>
						To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
					</div>
				</div>
			</div>
		</td>
	</tr>
</table>
