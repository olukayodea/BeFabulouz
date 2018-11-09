<?php
include_once("includes/functions.php");
if (isset($_REQUEST['token'])) {
	$token = $common->get_prep($_REQUEST['token']);
	$token = base64_decode($token);
	$token = explode("+", $token);
	
	$name = $token[0];
	$email = $token[1];
	
	$modify = $users->modifyOne("status", "ACTIVE", $email, "email");
	if ($modify) {


        echo "<html>";
        echo "<head>";
        echo '<meta http-equiv="refresh" content="3; url=\' https://befabulouz.com\'">';
        echo "</head>";
        echo "<body>";
		echo "Thanks ".$name.", your accont is now active, you can login via the app now";
        echo "</body>";
        echo "</html>";
	} else {
		echo "you can not activate this account at this time";
	}
} else {
	echo "this is not a valid activation link";
}
?>