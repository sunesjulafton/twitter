<?php

	include 'database/connection.php';
	include 'classes/user.php';
	include 'classes/tweet.php';
	include 'classes/follow.php';

	global $pdo;

	session_start();

	$getFromU = new User($pdo);
	$getFromT = new Tweet($pdo);
	$getFromF = new Follow($pdo);

	function console_log( $data ){
	  echo '<script>';
	  echo 'console.log('. json_encode( $data ) .')';
	  echo '</script>';
	}

	define("BASE_URL", "http://localhost/twitter/");


?>