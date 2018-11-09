<?php
    class common {
		function curlPost($url, $fields) {
			//extract data from the post
			extract($_POST);
			foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
			rtrim($fields_string,'&');
			
			//open connection
			$ch = curl_init();
			
			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_POST,count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			
			
			//execute post
			$result = curl_exec($ch);
			//close connection
			curl_close($ch);
			return $result;
		}
		function curl_file_get_contents($url) {
			if(strstr($url, "https") == 0) {
				return self::curl_file_get_contents_https($url);
			}
			else {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$data = curl_exec($ch);
				echo $err = curl_error($ch);
				curl_close($ch);
				return $data;
			}
		}
		
		function curl_file_get_contents_https($url) {
			$res = curl_init();
			curl_setopt($res, CURLOPT_URL, $url);
			curl_setopt($res,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($res, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($res, CURLOPT_SSL_VERIFYPEER, false);
			$out = curl_exec($res);
			echo curl_error($res);
			curl_close($res);
			return $out;
		}
				
		function get_prep($value) {
			$value = urldecode(htmlentities(strip_tags($value)));
			
			return $value;
		}
		
		function get_prep2(&$item) {
			$item = htmlentities($item);
			return $item;
		}
		
		function out_prep($array) {
			/*if (is_array) {
				if (count($array) > 0) {
					array_walk_recursive($array, array($this, 'get_prep2'));
				}
			}*/
			return $array;
		}
		
		function mysql_prep($value) {
			return $value;
		}
		
		function createRandomPassword($len = 7) { 
			$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890"; 
			srand((double)microtime()*1000000); 
			$i = 0; 
			$pass = '' ; 
			$count = strlen($chars);
			while ($i <= $len) { 
				$num = rand() % $count; 
				$tmp = substr($chars, $num, 1); 
				$pass = $pass . $tmp; 
				$i++; 
			} 
			return $pass; 
		}
		
		function send_mail($from,$to,$subject,$body,$name=true) {
			$from_data = explode("<", trim($from, ">"));
			$to_data = explode("<", trim($to, ">"));
			$to_email = $to_data[1];
			$to_name = $to_data[0];
			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->SMTPAuth = true; // authentication enabled
			$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
			$mail->Host = "smtp.mail.us-west-2.awsapps.com";
			$mail->Port = 465; // or 587

			$mail->SMTPDebug = 2;
			
			$mail->Username = "do-not-reply@skrinad.me";  // SMTP username
			$mail->Password = "Professiona1"; // SMTP password
			
			$mail->From = $from_data[1];
			$mail->FromName = $from_data[0];
			$mail->AddAddress($to_email,$to_name);                  // name is optional
			$mail->AddReplyTo($from_data[1], $from_data[0]);  
			
			$mail->WordWrap = 50;                                 // set word wrap to 50 characters
			$mail->IsHTML(true);                                  // set email format to HTML
			
			$mail->Subject = $subject;
			$mail->Body    = $body;
			$mail->AltBody = "This is email is readable only ini an HTML enabled browser or reader";
			
			if(!$mail->Send()) {
				return false;
			} else {
				return true;
			}
		}
    }
?>