<!doctype html>
 
<head>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>HTML5 MediaElement</title>	
    <link rel="stylesheet" type="text/css" href="js/flowplayer/skin/minimalist.css">

<style type="text/css">
   body { font: 12px "Myriad Pro", "Lucida Grande", sans-serif; text-align: center; padding: 0; }
   .flowplayer { width: 100%; }
   </style>

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script type="text/javascript" src="js/flowplayer/flowplayer.min.js"></script>

</head>
<body>
<?php
	$source_type = '';
	$ext = end(explode(".", $source));
	if (strtolower($ext) == 'flv') $source_type = 'flash';
	else $source_type = $ext;
?>
<div class="flowplayer" data-swf="js/flowplayer/flowplayer.swf" data-ratio="0.4167">
<video>
<source type="video/flash" src="http://localhost/p/cc/wallboard/video/<?php echo $source;?>">
</video>
</div>
 
</body>
