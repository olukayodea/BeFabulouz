<?php
	session_start();
	date_default_timezone_set("Africa/Lagos");
	
	//error_reporting(E_ALL & ~E_DEPRECATED);
	//error_reporting(E_ALL ^ E_NOTICE); 
	
	$pageUR1 = $_SERVER["SERVER_NAME"];
	$curdomain = str_replace("www.", "", $pageUR1);

	  if (($curdomain == "befabulouz.com/") || ($curdomain == "befabulouz.com")) {
		ini_set("session.cookie_domain", ".befabulouz.com/");
		define("URL", "https://befabulouz.com/", true);
		define("servername", "localhost", true);
		define("dbusername", "api_access", true);
		define("dbpassword", "n%).*6CBlBBu", true);
		define("dbname", "befabulouz", true);
	} else { 
		define("URL", "http://127.0.0.1/skrinad/", true);
		define("servername", "localhost", true);
		define("dbusername", "root", true);
		define("dbpassword", "mysql", true);
		define("dbname", "befabulouz", true);
	}
	
	define("limit", 20, true);
	
	include_once("classes/config.php");
	
	$config = new config;
	$db = $config->connect();
	
	define("replyMail", "do-not-reply@skrinad.me", true);
	
	include_once("classes/mailer/class.phpmailer.php");
	include_once("classes/common.php");
	$common = new common;
?>