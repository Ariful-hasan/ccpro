<?php
// var_dump($_FILES);
// var_dump($_POST);
if(!empty($_FILES)){
	// var_dump(is_dir($_POST['dir']));
	if (!is_dir($_POST['dir'])) 
		mkdir($_POST['dir'], 0750, true);

	if(move_uploaded_file($_FILES['file_contents']['tmp_name'], $_POST['dir'].$_POST['new_filename'])){
		die(json_encode(['result'=>true]));	
	}
}
die(json_encode(['result'=>false]));