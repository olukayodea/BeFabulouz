<?php
	class config {
		function connect() {
			$db = new PDO('mysql:host='.servername.';dbname='.dbname.';charset=utf8mb4', dbusername, dbpassword);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			return $db;
		}
	}
?>