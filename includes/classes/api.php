<?php
	class api extends common {
		function prep($array, $raw_data) {
			// get all api url variables
			$data = explode("/", $array);
			$key = $data[0];
			$response = strtolower($data[1]);
			$mode = strtolower($data[2]);
			$action = strtolower($data[3]);
			$string = @strtolower($data[4]);
			
			//get additional data			
			$return = false;
			if ($response == "xml") {
				$app_data = simplexml_load_string($raw_data);
				$product_key =(string) $app_data->product_key;
				$product_id =(string) $app_data->product_id;
				$product_ver =(string) $app_data->product_ver;
			} else if ($response == "json") {
				$app_data = json_decode($raw_data, true);
				$product_key = $app_data['product_key'];
				$product_id = $app_data['product_id'];
				$product_ver = $app_data['product_ver'];
			} else {
				$return['header']['status'] = "ERROR";
				$return['header']['code'] = "120";
				$return = true;
			}

			global $users;
			global $payments;
			global $payouts;
			global $advert_refresh;
			global $advert_stat;
			global $reward;
			global $refer;
			global $country;
			global $categories;
			
			//check product version
			//if (product_ver <= $product_ver) {
				//authenticate user
				if ($return == false) {	
					if ($this->authenticate($key, $product_key, $product_id)) {
						switch ($mode) {
							case "users":
								switch ($action) {
									case "login":
										if ($response == "xml") {
											$array_data['email'] = (string) $app_data->user->email;
											$array_data['password'] = (string) $app_data->user->password;
										} else if ($response == "json") {
											$array_data = $app_data['user'];
										}
										$login = $users->login($array_data);
										if ($login == 0) {
											$return['header']['status'] = "ERROR";
											$return['header']['code'] = "107";
										} else if ($login == 1) {
											$return['header']['status'] = "ERROR";
											$return['header']['code'] = "108";
										} else if ($login == 3) {
											$return['header']['status'] = "ERROR";
											$return['header']['code'] = "109";
										} else {
											$return['header']['status'] = 'DONE';
											$return['header']['code'] = "200";
											$return['header']['completedTime'] = date('l jS \of F Y h:i:s A');
											$getDetails = $users->listAccount($login['id']);
											$curent_balance = ceil($payments->currentEarnings($login['id']));
											$total_earnings = ceil($payments->totalEarnings($login['id']));
											$total_payout =  ceil($payouts->total($login['id']));
											if ($getDetails == false) {
												$getDetails['ref'] = "";
												$getDetails['user'] = "";
												$getDetails['bank'] = "";
												$getDetails['account'] = "";
												$getDetails['sort_code'] = "";
												$getDetails['account_name'] = "";
												$getDetails['pay_stack_token'] = "";
												$getDetails['validated'] = "";
												$getDetails['skip'] = "";
											}
											$return['body']['id'] = $login;
											$return['body']['curent_balance'] = round($curent_balance, 2);
											$return['body']['total_earnings'] = round($total_earnings, 2);
											$return['body']['total_payout'] = round($total_payout, 2);
											$return['body']['account'] = $getDetails;
										}
										break;
									case "register":
										if ($response == "xml") {
											$array_data['last_name'] = (string) $app_data->user->last_name;
											$array_data['other_names'] = (string) $app_data->user->other_names;
											$array_data['email'] = (string) $app_data->user->email;
											$array_data['password'] = (string) $app_data->user->password;
											$array_data['phone'] =  (string) $app_data->user->phone;
											$array_data['preference'] = (string) $app_data->user->preference;
											$array_data['ageRange'] = (string) $app_data->user->ageRange;
											$array_data['gender'] = (string) $app_data->user->gender;
										} else if ($response == "json") {
											$array_data = $app_data['user'];
										}
										
										$register = $users->create($array_data);
										if ($register) {
											$return['header']['status'] = 'DONE';
											$return['header']['code'] = "200";
											$return['header']['completedTime'] = date('l jS \of F Y h:i:s A');
											$return['body']['id'] = $register;
										} else {
											$return['header']['status'] = "ERROR";
											$return['header']['code'] = "106";
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
											$return['header']['code'] = "116";
										}
										break;	
									case "updatedetails":
										if ($response == "xml") {
											$array_data['id'] = (string) $app_data->user->id;
											$array_data['last_name'] = (string) $app_data->user->last_name;
											$array_data['other_names'] = (string) $app_data->user->other_names;
											$array_data['email'] = (string) $app_data->user->email;
											$array_data['password'] = (string) $app_data->user->password;
											$array_data['phone'] =  (string) $app_data->user->phone;
											$array_data['preference'] = (string) $app_data->user->preference;
											$array_data['ageRange'] = (string) $app_data->user->ageRange;
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
											$return['header']['code'] = "110";
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
											$return['header']['code'] = "118";
										}
										break;
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
						}
					} else {
						$return['header']['status'] = "ERROR";
						$return['header']['code'] = "101";
					}
				}
			//} else {
			//	$return['header']['status'] = "ERROR";
			//	$return['header']['code'] = "131";
			//}
			if ($response == "json") {
				$this->dumpData($raw_data, json_encode($return));
				return $this->convert_to_json($return);
			} else if ($response == "xml") {
				return $this->convert_to_xml($return);
			} else {
				return  "WELCOME TO THE SKRINAD API, YOU HAVE DONE MANY THINGS WRONG THATS WHY YOU ARE SEEING THIS MESSAGE< PLEASE CHECK OUR API DOCUMENTATION OR CONTACT <a href=\"mailTo:info@SkrinAd.com\">SkrinAd Admin</a>";
			}
		}
		
		function authenticate($key, $hash, $product_id) {
			$keyHash = $hash+$product_id;
			$hash_key = hash("sha256", $keyHash);
			if ($hash_key == $key) {
				return true;
			} else {
				return false;
			}
		}
		
		function input($data, $type="JSON") {
		}
		
		function output($data, $type="JSON") {
			$type = strtolower($type);
		}
		
		function convert_to_json($data) {
			header('Content-type: application/json');
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
		
		function convert_to_xml($data) {
			header('Content-type: application/xml');
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