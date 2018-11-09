<?php
	include_once("../functions.php");
	$last_name = $common->get_prep($_REQUEST['last_name']);		
	$email = $common->get_prep($_REQUEST['email']);
	$password = $common->get_prep($_REQUEST['password']);
	$other_names = $common->get_prep($_REQUEST['other_names']);
	$password = $common->get_prep($_REQUEST['password']);
	
  $getname = explode(" ", $other_names);
  
  $token = base64_encode($getname[0]."+".$email);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Page Title</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <p>Dear <?php echo $getname[0]; ?>, </p>
    <p>Please <a href='<?php echo URL."activate?token=".$token; ?>'>click on this link</a> to validate your email and activate your account or copy and paste the following into your browser address bar<br>
    <?php echo URL."activate?token=".$token; ?></p>
    <p>Please save this mail for future use. Your account enables you to login to the system, and also stores any reward points you may have earned. Your details are now saved as below:</p>
    <p><strong>Login Details</strong>
    <hr>
    <p>Mail.: <?php echo $email; ?><br>
    Password: <?php echo $common->hashPass($password); ?></p>
    <p><strong>Your saved details (You can change this anytime within the aplication) </strong></p>
    <hr>
    <p><strong>Contact Information</strong><br>
    Full Names: <?php echo $last_name; ?> <?php echo $other_names; ?><br>
    Mail.: <?php echo $email; ?><br>
    Phone: <?php echo $phone; ?></p>
    <hr>
    <p><strong>How it works</strong></p>
    <p>Once signed in, close application and wait for periodic advert pop-ups when device is not being used. Once Advert is shown, you can dismiss the advert by clicking on the close button, or tap the advert to open the Advert details</p>
    <p>Enjoy your new application where <strong>Everyday is Payday!!!</strong></p>
    <p>Regards,<br> <span class="signature">Jimmy</span></p>
    
</body>
</html>