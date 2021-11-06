<?php

session_start( [ 'read_and_close' => true ] );
require('bin/php/index.php');
require(PROJECT_ROOT.'php/element/navigation/index.php');
 require('bin/css/index.php');
 require('bin/js/index.php');
$types = array('image/jpeg', 'image/png','image/jpg');
$array = explode('.', $_FILES['uploadedfile']['name']);
$extension = end($array);
$target_path = "bin/uploads/";

$target_path = $target_path . $_SESSION['User'] . "." . $extension; 



if(file_exists("bin/php/uploads" . $_SESSION['User'] . ".jpg")) {
	unlink("bin/php/uploads" . $_SESSION['User'] . ".jpg");
}
if(file_exists("bin/php/uploads" . $_SESSION['User'] . ".png")) {
	unlink("bin/php/uploads" . $_SESSION['User'] . ".png");
}
if(file_exists("bin/php/uploads" . $_SESSION['User'] . ".jpeg")) {
	unlink("bin/php/uploads" . $_SESSION['User'] . ".jpeg");
}
if(isset($_FILES['uploadedfile'])){
	if($_FILES['uploadedfile']['size'] > 150000) {
		?> <h4>Exeeded File Size. Max File Size is 150 kb.</h4> <?php
	}
	else{
		?> <h4>Size within restriction. </h4> <?php
	}
}
else{
	?> <h4>whattttt</h4> <?php
	}


if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
    ?><h4>The file <?php echo basename( $_FILES['uploadedfile']['name']) ?> 
	has been uploaded. It will reflect on the website within 10 minutes. </h4> <?php
}
$previous = "javascript:history.go(-1)";
if(isset($_SERVER['HTTP_REFERER'])) {
    $previous = $_SERVER['HTTP_REFERER'];
}
?>
<a href="<?= $previous ?>"><button type="button" class="btn btn-primary">Click Here to go Back</button>
</a> <?php

?>