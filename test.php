<?php
	$username = "api_access";
	$secret = "420312f587ffcfebdf7b3336ebbdf0330c2ffe91";
	$u = "http://127.0.0.1/BeFabulouz/";
	//$u = "https://conextgroup.com/park/";
	
	$array['username'] = $username;
	$array['secret'] = $secret;
    $array['product_ver'] = $product_ver;

	$array['last_name'] = "adebiyi";
	$array['other_names'] = "olukayode";
	$array['password'] = "lolade";
	$array['email'] = "olukayode.adebiyi@hotmail.co.uk";
	$array['gender'] = "male";
	
	$xml_data = json_encode($array);
	$URL = $u."api/json/users/login";
	
	$headers[] = 'Accept: application/json';
	$headers[] = 'Content-Type: application/json';
	$headers[] = 'username: '.$username;
	$headers[] = 'secret: '.$secret;
	
	$ch = curl_init($URL);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$output = curl_exec($ch);
	curl_close($ch);
	
	echo $output;
?>