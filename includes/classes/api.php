<?php
	class api extends common {
		function prep($array, $raw_data) {
			// get all api url variables
			$data = explode("/", $array);
			$http_username = $data[0];
			$http_secret = $data[1];
			$response = strtolower($data[2]);
			$mode = strtolower($data[3]);
			$action = strtolower($data[4]);
			$string = @strtolower($data[5]);

			global $users;
			
			//get additional data			
			$returnApi = false;
			if ($response == "xml") {
				$app_data = simplexml_load_string($raw_data);
				$content_username =(string) $app_data->username;
				$content_secret =(string) $app_data->secret;
				$product_ver =(string) $app_data->product_ver;
			} else if ($response == "json") {
				$app_data = json_decode($raw_data, true);
				$content_username = $app_data['username'];
				$content_secret = $app_data['secret'];
				$product_ver = $app_data['product_ver'];
			} else {
				$return['header']['status'] = "ERROR";
				$return['header']['description'] = "Bad Request";
				$return['header']['code'] = "400";
				$returnApi = true;
			}
			
			//check product version
			//if (product_ver <= $product_ver) {
				//authenticate user
				if ($returnApi == false) {	
					if ($this->authenticate($http_username, $http_secret, $content_username, $content_secret)) {
						switch ($mode) {
							case "users":
								switch ($action) {
									case "login":
										if ($response == "xml") {
											$array_data['email'] = (string) $app_data->user->email;
											$array_data['password'] = (string) $app_data->user->password;
										} else if ($response == "json") {
											$array_data = $app_data;
										}
										$login = $users->login($array_data);
										if ($login == 0) {
											$return['header']['status'] = "ERROR";
											$return['header']['description'] = "Login failed";
											$return['header']['code'] = "451";
										} else if ($login == 1) {
											$return['header']['status'] = "ERROR";
											$return['header']['description'] = "Account inactive";
											$return['header']['code'] = "452";
										} else if ($login == 3) {
											$return['header']['status'] = "ERROR";
											$return['header']['description'] = "Account Suspended";
											$return['header']['code'] = "453";
										} else {
											$return['header']['status'] = 'DONE';
											$return['header']['code'] = "200";
											$return['header']['completedTime'] = date('l jS \of F Y h:i:s A');
											$return['body'] = $login;
										}
										break;
									case "register":
										if ($response == "xml") {
											$array_data['last_name'] = (string) $app_data->user->last_name;
											$array_data['other_names'] = (string) $app_data->user->other_names;
											$array_data['email'] = (string) $app_data->user->email;
											$array_data['password'] = (string) $app_data->user->password;
											$array_data['gender'] = (string) $app_data->user->gender;
										} else if ($response == "json") {
											$array_data = $app_data;
										}
										
										$register = $users->create($array_data);
										if ($register) {
											$return['header']['status'] = 'DONE';
											$return['header']['code'] = "200";
											$return['header']['completedTime'] = date('l jS \of F Y h:i:s A');
											$return['header']['message'] = "Account created, registration email sent to user";
											$return['body']['id'] = $register;
										} else {
											$return['header']['status'] = "ERROR";
											$return['header']['description'] = "Registration failed";
											$return['header']['code'] = "450";
										}
										break;
									case "getdetails":
										if ($response == "xml") {
											$array_data['ref'] = (string) $app_data->user->id;
										} else if ($response == "json") {
											$array_data['ref'] = $app_data['user']['id'];
										}
										$getDetails = $users->listOne($array_data['ref']);

										if ($getDetails) {
											$return['header']['status'] = 'DONE';
											$return['header']['code'] = "200";
											$return['header']['completedTime'] = date('l jS \of F Y h:i:s A');
											$return['body']['userData'] = $getDetails;
										} else {
											$return['header']['status'] = "ERROR";
											$return['header']['description'] = "User not found";
											$return['header']['code'] = "454";
										}
										break;	
									case "updatedetails":
										if ($response == "xml") {
											$array_data['users_id'] = (string) $app_data->user->users_id;
											$array_data['last_name'] = (string) $app_data->user->last_name;
											$array_data['other_names'] = (string) $app_data->user->other_names;
											$array_data['email'] = (string) $app_data->user->email;
											$array_data['password'] = (string) $app_data->user->password;
											$array_data['gender'] = (string) $app_data->user->gender;
										} else if ($response == "json") {
											$array_data = $app_data['user'];
										}
										
										$register = $users->update($array_data);
										if ($register) {
											$return['header']['status'] = 'DONE';
											$return['header']['code'] = "200";
											$return['header']['completedTime'] = date('l jS \of F Y h:i:s A');
											$return['body']['id'] = $array_data['id'];
										} else {
											$return['header']['status'] = "ERROR";
											$return['header']['description'] = "User details not updated";
											$return['header']['code'] = "455";
										}
										break;
									case "updatepassword":
										if ($response == "xml") {
											$array_data['ref'] = (string) $app_data->user->id;
											$array_data['password'] = (string) $app_data->user->password;
											$array_data['old_password'] = (string) $app_data->user->old_password;
										} else if ($response == "json") {
											$array_data['ref'] = $app_data['user']['id'];
											$array_data['password'] = $app_data['user']['password'];
											$array_data['old_password'] = $app_data['user']['old_password'];
										}
										
										$register = $users->updatePassword($array_data);
										if ($register) {
											$return['header']['status'] = 'DONE';
											$return['header']['code'] = "200";
											$return['header']['completedTime'] = date('l jS \of F Y h:i:s A');
											$return['body']['id'] = $array_data['ref'];
										} else {
											$return['header']['status'] = "ERROR";
											$return['header']['description'] = "User password not updated";
											$return['header']['code'] = "456";
										}
										break;
									default:
										$return['header']['status'] = "ERROR";
										$return['header']['description'] = "Action unacceptable";
										$return['header']['code'] = "406";
								}
								break;
							case "report":
								switch ($action) {
									case "earnings":
										if ($response == "xml") {
											$array_data['ref'] = (string) $app_data->user->id;
										} else if ($response == "json") {
											$array_data['ref'] = $app_data['user']['id'];
										}
										
										$getDetails = $payments->listEarnings($array_data['ref'], 100);
		
										if ($getDetails) {
											$return['header']['status'] = 'DONE';
											$return['header']['code'] = "200";
											$return['header']['completedTime'] = date('l jS \of F Y h:i:s A');
											$return['body']['userData'] = $getDetails;
										} else {
											$return['header']['status'] = "ERROR";
											$return['header']['code'] = "117";
										}
										break;
									break;
								}
							default:
								$return['header']['status'] = "ERROR";
								$return['header']['description'] = "Mode unacceptable";
								$return['header']['code'] = "406";

						}
					} else {
						$return['header']['status'] = "ERROR";
						$return['header']['description'] = "Unauthorized";
						$return['header']['code'] = "401";
					}
				}
			//} else {
			//	$return['header']['status'] = "ERROR";
			//	$return['header']['code'] = "131";
			//}
			if ($response == "json") {
				return $this->convert_to_json($return, $return['header']['code']);
			} else if ($response == "xml") {
				return $this->convert_to_xml($return, $return['header']['code']);
			} else {
				return $this->convert_to_json($return, $return['header']['code']);
			}
		}
		
		function authenticate($username, $secret, $content_username, $content_secret) {
			global $api_secret;
			global $admin;
			
			if (($api_secret == $secret) && ($username == $content_username) && ($secret == $content_secret)) {
				return $admin->authenticate($username);
			} else {
				return false;
			}
		}
		
		function input($data, $type="JSON") {
		}
		
		function output($data, $type="JSON") {
			$type = strtolower($type);
		}
		
		function convert_to_json($data, $status) {
			header('Content-type: application/json');
			http_response_code($status);
			echo json_encode($data);
		}
		
		function array_to_xml(array $arr, SimpleXMLElement $xml) {
			foreach( $arr as $key => $value ) {
				if( is_array($value) ) {
					if( is_numeric($key) ){
						$key = 'item'.($key+1); //dealing with <0/>..<n/> issues
					}
					$subnode = $xml->addChild($key);
					$this->array_to_xml($value, $subnode);
				} else {
					$xml->addChild("$key",htmlspecialchars("$value"));
				}
			 }
			 
			 return $xml;
		}
		
		function convert_to_xml($data, $status) {
			header('Content-type: application/xml');
			http_response_code($status);
			header('Pragma: public');
			header('Cache-control: private');
			header('Expires: -1');
			echo $this->array_to_xml($data, new SimpleXMLElement('<skrinAd/>'))->asXML();
		}
		
		function strToArray($val, $delimiter=":") {
			return explode($delimiter, $val);
		}
		
		function arrayToString($val, $delimiter=":") {
			return implode($delimiter, $val);
		}	
		
		public static function simpleXmlToArray($xmlObject) {
			$array = array();
	
			foreach ($xmlObject->children() as $node) {
				if (is_array($node)) {
					$array[$node->getName()] = simplexml_to_array($node);
				} else {
					$array[$node->getName()] = (string) $node;
				}
			}
	
			return $array;
		}
	}