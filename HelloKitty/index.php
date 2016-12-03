<?php
    ini_set('display_errors', 'on'); error_reporting(-1);
	echo "Hello Kitty v1.0";
	exec("ffmpeg -version", $op);
	print_r( $op );
?>
