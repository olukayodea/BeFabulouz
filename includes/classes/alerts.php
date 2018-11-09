<?php
	class alerts extends common {
		function sendEmail($array) {
			$from = $array['from'];
			$to = $array['to'];
			$subject = $array['subject'];
			$body = $array['body'];
			$send = $this->send_mail($from,$to,$subject,$body);
			
			if ($send) {
				return true;
			} else {
				return false;
			}
		}
		
		function addToMailSpool($array) {
			$subject = $this->mysql_prep($array['subject']);
			$body = $this->mysql_prep($array['body']);
			$user = $this->mysql_prep($array['user']);
			$email = $this->mysql_prep($array['email']);
			$schedule_time = $this->mysql_prep($array['schedule_time']);
			$create_time = $modify_time = time();
			
			global $db;
			try {
				$sql = $db->prepare("INSERT INTO `email_spool` (`subject`, `body`, `user`,`email`, `create_time`, `modify_time`, `schedule_time`) VALUES (:subject, :body, :user,:email, :create_time, :modify_time, :schedule_time)");
				$sql->execute(
					array(	':subject' => $subject,
							':body' => $body,
							':user' => $user,
							':email' => $email,
							':create_time' => $create_time,
							':modify_time' => $modify_time,
							':schedule_time' => $schedule_time)
						);
			} catch(PDOException $ex) {
				echo "An Error occured! ".$ex->getMessage(); 
			}
			$id = $db->lastInsertId();
			return true;
		}
		
		function updateOneSpool($tag, $value, $id) {
			$id = $this->mysql_prep($id);
			$value = $this->mysql_prep($value);
			
			global $db;
			try {
				$sql = $db->prepare("UPDATE `email_spool` SET  `".$tag."` = :value WHERE `ref`=:id");
				$sql->execute(
					array(
					':value' => $value,
					':id' => $id)
				);
			} catch(PDOException $ex) {
				echo "An Error occured! ".$ex->getMessage(); 
			}
		}
		
		function sendBulkSystem() {
			global $db;
			global $users;
			$data = $this->spoolBatch(75);
			
			for ($i = 0; $i < count($data); $i++) {
				$array['from'] = "SkrinAd <".replyMail.">";
				$array['subject'] = $data[$i]['subject'];
				if (intval($data[$i]['user']) > 0) {
					$user_data	= $users->listOne($data[$i]['user']);
					$array['to']= $user_data['other_names']." ".$user_data['last_name']." <".$user_data['email'].">";

					$ref 		= $user_data['ref'];
					$last_name	= $user_data['last_name'];
					$other_names= $user_data['other_names'];
					$email		=$user_data['email'];
					$name = true;
				} else {
					$array['to']= $data[$i]['email'];

					$ref		= 0;
					$last_name	= "";
					$other_names= "";
					$email		= " <".$data[$i]['email'].">";

					$name = false;
				}
				
				$fields = 'subject='.urlencode($data[$i]['subject']).
					'&ref='.urlencode($ref).
					'&last_name='.urlencode($last_name).
					'&other_names='.urlencode($other_names).
					'&email='.urlencode($email).
					'&html_body='.urlencode($data[$i]['ref']);
				$mailUrl = URL."includes/emails/campaign.php?".$fields;
				$body = htmlspecialchars_decode(file_get_contents($mailUrl));
				$array['body'] = $body;
				$send = $this->sendEmail($array, $name);
				//if ($send) {
					$this->updateOneSpool("status", "SENT", $data[$i]['ref']);
					$this->updateOneSpool("sent_time", time(), $data[$i]['ref']);
				//}
			}
			
			return true;
		}
		
		function spoolBatch($limit) {
			global $db;
			try {
				$sql = $db->query("SELECT * FROM `email_spool` WHERE `status` = 'NEW' AND `schedule_time` < '".time()."' LIMIT ".$limit);
			} catch(PDOException $ex) {
				echo "An Error occured! ".$ex->getMessage(); 
			}
			
			$row = $sql->fetchAll(PDO::FETCH_ASSOC);
				
			return $this->out_prep($row);
		}
		
		function listAll() {
			global $db;
			try {
				$sql = $db->query("SELECT * FROM `email_spool` ORDER BY `ref` DESC");
			} catch(PDOException $ex) {
				echo "An Error occured! ".$ex->getMessage(); 
			}
			
			$row = $sql->fetchAll(PDO::FETCH_ASSOC);
				
			return $this->out_prep($row);
		}
		
		function getOne($id, $tag='ref') {
			global $db;
			try {
				$sql = $db->prepare("SELECT * FROM email_spool WHERE `".$tag."` = :id");
				$sql->execute(
					array(
					':id' => $id)
				);
			} catch(PDOException $ex) {
				echo "An Error occured! ".$ex->getMessage(); 
			}
			
			$row = $sql->fetch(PDO::FETCH_ASSOC);
				
			return $this->out_prep($row);
		}
		
		//get one field from the dtails of one application
		function getOneField($id, $tag='ref', $ref='body') {
			$data = $this->getOne($id, $tag);
			return $data[$ref];
		}

		function sendAppNotification($array) {
			$title = $array['title'];
			$body = $array['body'];
			$token= $array['token'];

			  $msg = array(
				'body' 	=> $body,
				'title'	=> $title,
				'action'	=> $title
			  );
		 
			 $fields = array(
			   'to' => $token,
			   'notification'	=> $msg,
			   'collapse_key'	=> 'type_a'
			 );
		 
			 $headers = array(
			   'Authorization: key=' . API_ACCESS_KEY,
			   'Content-Type: application/json'
			 );
		 
			 $ch = curl_init();
			 curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
			 curl_setopt( $ch,CURLOPT_POST, true );
			 curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
			 curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
			 curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
			 curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
		 
			 $result = curl_exec($ch );
		}
	}
?>