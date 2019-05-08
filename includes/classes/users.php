<?php
    class users extends common {
		function checkAccount($email) {
			global $db;
			try {
				$sql = $db->prepare("SELECT `users_id` FROM `users` WHERE `email` = :email AND `status` != 'DELETED'");
				$sql->execute(array(':email' => $email));
			} catch(PDOException $ex) {
				echo "An Error occured! ".$ex->getMessage(); 
			}
			
			return $sql->rowCount();
		}
		function login($array) {
			$email = $this->mysql_prep($array['email']);
			$password = $this->mysql_prep($array['password']);
			
			global $db;
			try {
				$sql = $db->prepare("SELECT * FROM `users` WHERE `email` = :email AND password = :password AND `status` != 'DELETED'");
				$sql->execute(array(':email' => $email,
									':password' => sha1($password)));
			} catch(PDOException $ex) {
				echo "An Error occured! ".$ex->getMessage(); 
			}
			if ($sql->rowCount() == 1) {
				$row = $sql->fetch(PDO::FETCH_ASSOC);
				$status = $row['status'];
				if ($status == "NEW") {
					return 1;
				} else if ($status == "INACTIVE") {
					return 3;
				} else {
					return $row;
				}
			} else {
				return 0;
			}
		}
		
		function create($array) {
			global $alerts;
			global $db;

			$password = $this->mysql_prep($array['password']);
			$last_name = ucfirst(strtolower($this->mysql_prep($array['last_name'])));
			$other_names = ucwords(strtolower($this->mysql_prep($array['other_names'])));
			$email = $this->mysql_prep($array['email']);
			$gender = $this->mysql_prep($array['gender']);
			$date_time = date("Y-m-d h:i:s", time());
			$modify_time = date("Y-m-d h:i:s", time());
			
			if ($this->checkAccount($email) == 0) {
				try {
					$sql = $db->prepare("INSERT INTO `users` (`last_name`, `password`, `other_names`, `email`, `gender`, `status`, `date_time`, `modify_time`) VALUES (:last_name, :password, :other_names, :email, :gender, :status, :date_time, :modify_time)");
					$sql->execute(
						array(	':last_name' => $last_name,
								':password' => sha1($password),
								':other_names' => $other_names,
								':email' => $email,
								':gender' => $gender,
								':status' => "NEW",
								':date_time' => $date_time,
								':modify_time' => $modify_time)
							);
				} catch(PDOException $ex) {
					echo "An Error occured! ".$ex->getMessage(); 
				}
				$ref = $db->lastInsertId();
				
				$client = $last_name." ".$other_names." <".$email.">";
				$subjectToClient = "BeFabuloz User Account";
				
				$contact = "BeFabuloz <".replyMail.">";
					
				$fields = 'subject='.urlencode($subjectToClient).
					'&last_name='.urlencode($last_name).
					'&other_names='.urlencode($other_names).
					'&email='.urlencode($email).
					'&password='.urlencode($password);
				$mailUrl = URL."includes/emails/welcome.php?".$fields;
				$messageToClient = $this->curl_file_get_contents($mailUrl);
				
				$mail['from'] = $contact;
				$mail['to'] = $client;
				$mail['subject'] = $subjectToClient;
				$mail['body'] = $messageToClient;
				
				$alerts->sendEmail($mail);
	
				return $ref;
			} else {
				return false;
			}
		}
		
		function update ($array) {
			$ref = $this->mysql_prep($array['users_id']);
			$last_name = $this->mysql_prep($array['last_name']);
			$other_names = $this->mysql_prep($array['other_names']);
			$email = $this->mysql_prep($array['email']);
			$gender = $this->mysql_prep($array['gender']);
			$modify_time = date("Y-m-d h:i:s", time());
			
			global $db;
			try {
				$sql = $db->prepare("UPDATE `users` SET  last_name = :last_name, `other_names`=:other_names, `email`=:email, `phone`=:phone, `preference`=:preference, `ageRange` = :ageRange, `gender` = :gender, `modify_time`=:modify_time WHERE `users_id`=:ref");
				$sql->execute(
					array(':modify_time' => time(), 
					':last_name' => $last_name,
					':other_names' => $other_names,
					':email' => $email,
					':gender' => $gender,
					':ref' => $ref)
				);
			} catch(PDOException $ex) {
				echo "An Error occured! ".$ex->getMessage(); 
			}
			
			if ($sql) {
				return true;
			} else {
				return false;
			}
		}
		
		function updatePassword($array) {
			$ref = $this->mysql_prep($array['users_id']);
			$password = $this->mysql_prep(trim($array['password']));
			$old_password = $this->mysql_prep(trim($array['old_password']));
			$modify_time = date("Y-m-d h:i:s", time());
			
			global $db;

			try {
				$sql = $db->query("SELECT * FROM users WHERE `password` = '".sha1($old_password)."' AND `users_id` = '".$ref."'");
			} catch(PDOException $ex) {
				echo "An Error occured! ".$ex->getMessage(); 
			}
			
			if ($sql->rowCount() == 1) {
				try {
					$sql = $db->prepare("UPDATE `users` SET  password = :password,`modify_time`=:modify_time WHERE `users_id`=:ref");
					$sql->execute(
						array(':modify_time' => time(), 
						':password' => sha1($password),
						':ref' => $ref)
					);
				} catch(PDOException $ex) {
					echo "An Error occured! ".$ex->getMessage(); 
				}
							
				if ($sql) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
		
		function listAll($limit=false) {
			global $db;
			
			if ($limit == true) {
				$add = " LIMIT ".$limit;
			} else {
				$add = "";
			}
			
			try {
				$sql = $db->query("SELECT * FROM `users` WHERE status != 'DELETED' ORDER BY `users_id` ASC".$add);
			} catch(PDOException $ex) {
				echo "An Error occured! ".$ex->getMessage(); 
			}
			
			$row = $sql->fetchAll(PDO::FETCH_ASSOC);
				
			return $this->out_prep($row);
		}
		
		function modifyOne($tag, $value, $id, $title='users_id') {
			$value = $this->mysql_prep($value);
			$id = $this->mysql_prep($id);
			$modDate = date("Y-m-d h:i:s", time());
			
			global $db;
			try {
				$sql = $db->prepare("UPDATE `users` SET `".$tag."` = :value, `modify_time`=:modify_time WHERE `".$title."`=:users_id");
				$sql->execute(
					array(':modify_time' => time(), 
					':value' => $value,
					':ref' => $id)
				);
			} catch(PDOException $ex) {
				echo "An Error occured! ".$ex->getMessage(); 
			}
			
			if ($sql) {
				return true;
			} else {
				return false;
			}
		}
		
		function sortAll($id, $tag, $tag2=false, $id2=false, $tag3=false, $id3=false, $order='users_id') {
			$token = array(':id' => $id);
			if ($tag2 != false) {
				$sqlTag = " AND `".$tag2."` = :id2";
				$token[':id2'] = $id2;
			} else {
				$sqlTag = "";
			}
			if ($tag3 != false) {
				$sqlTag = " AND `".$tag3."` = :id3";
				$token[':id3'] = $id3;
			} else {
				$sqlTag .= "";
			}
			
			global $db;
			try {
				$sql = $db->prepare("SELECT * FROM `users` WHERE `status` != 'deleted' AND `".$tag."` = :id".$sqlTag." ORDER BY `".$order."` ASC");
				
				$sql->execute($token);
			} catch(PDOException $ex) {
				echo "An Error occured! ".$ex->getMessage(); 
			}
			
			$row = $sql->fetchAll(PDO::FETCH_ASSOC);
			return $this->out_prep($row);
		}
		
		function listOne($ref, $tag='users_id') {
			
			global $db;
			try {
				$sql = $db->prepare("SELECT * FROM users WHERE `".$tag."` = :id");
				$sql->execute(
					array(
					':id' => $ref)
				);
			} catch(PDOException $ex) {
				echo "An Error occured! ".$ex->getMessage(); 
			}
			
			$result = array();
			$row = $sql->fetch(PDO::FETCH_ASSOC);
				
			return $this->out_prep($row);
		}
		
		function getOneField($id, $tag="users_id", $ref="last_name") {
			$data = $this->listOne($id, $tag);
			return $data[$ref];
		}
    }
?>