<?php
	//ini_set('error_reporting', E_ALL);
    include_once("../includes/functions.php");
	
	//$api_secret = "420312f587ffcfebdf7b3336ebbdf0330c2ffe91";

	$username = $_SERVER['HTTP_USERNAME'];
	$secret = $_SERVER['HTTP_SECRET'];
	$link = $_REQUEST['request'];

	$arrayData = $username."/".$secret."/".$link;
	$date = file_get_contents('php://input');
	echo $data = trim($api->prep($arrayData, $date));
?>