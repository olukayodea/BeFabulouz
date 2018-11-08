<?php
	//ini_set('error_reporting', E_ALL);
    include_once("../includes/functions.php");
    
	$date = file_get_contents('php://input');
	echo $data = trim($api->prep($_REQUEST['request'], $date));
?>