<?php 
	
	class S_HPass{
		
		private static $secret_key = "rNEXCi9go4ZjTyu1pIwE1JHwRyiBmNZIvvvNvlW0i0UImOTquj9ulaGSqINQzRdpS"; //Append this to a password hash for +secrutiy
		private static $min_passLenghh = 4; // Minimum required password length
		private static $db;
		
		public static function SetDB($dbObj){
			S_HPass::$db = $dbObj;
        }
        
        public static function GetDB(){
            return S_HPass::$db;
        }
	
		private static function isPassAlright($pass){
			return (isset($pass) && is_string($pass) && strlen($pass) >= S_HPass::$min_passLenghh);
		}
		
		private static function returnError($e){
			print "MySQL Error:" . $e->getMessage() . "<br>";
			die('');
		}
		
		private static function ComputeHash($pass){
            $escape = (string)htmlspecialchars_decode($pass);
			return password_hash($escape.S_HPass::$secret_key,PASSWORD_BCRYPT,array("cost"=>12));
		}
		
		public static function isCorrect($pass,$hash){
            $escape = htmlspecialchars_decode($pass);
			
			if (isset($hash) && S_HPASS::isPassAlright($escape) && password_verify($escape.S_HPass::$secret_key,$hash)){
				return true;
			}else{
				return false;
			}
		}
		
		static function DoesEmailExist($email){
			try{
                $data = S_HPass::$db->QueryTop("SELECT 1 FROM userdata WHERE email = ?;",[$email]);
                if (isset($data[1])){
                    return true;
                }else{
                    return false;
                }
			}catch(PDOException $e) {
				returnError($e);
			}
		}
		
		static function InsertInfo($email,$pass,$displayname){
            $escape = htmlspecialchars_decode($pass);
			try{
				
				if (S_HPASS::isPassAlright($escape)){
					
                    $doesExist = S_HPASS::DoesEmailExist($email);
                    if ($doesExist){
                        return "ERROR: Username already exists";
                    }
                    $hashpass = S_HPASS::ComputeHash($escape);
                    $token = S_HPASS::GenNewToken();
                    $data = S_HPass::$db->Query("INSERT INTO userdata(email,displayname,hash,token) VALUES (?, ?, ?, ?);",[$email,$displayname,$hashpass,$token]);
					return $token;
				}else{
					return "ERROR: password failed check";
				}
			}catch(PDOException $e) {
				returnError($e);
			}
		}
		
		static function GetHash($email,$pass){
            $escape = htmlspecialchars_decode($pass);
			try{
                $data = S_HPass::$db->QueryTop("SELECT hash FROM userdata WHERE email = ?;",[$email]);
                
                if (isset($data["hash"])){
                    return S_HPASS::isCorrect($escape,$data["hash"]);
                }else{
                    return false;
                }
			}catch(PDOException $e) {
				returnError($e);
			}
        }

        static function GetToken($email){
			try{
                $data = S_HPass::$db->QueryTop("SELECT token FROM userdata WHERE email = ?;",[$email]);
                
                if (isset($data["token"])){
                    return $data["token"];
                }else{
                    return "ERROR: Token did not exist!";
                }

				return $data;
			}catch(PDOException $e) {
				returnError($e);
			}
        }

        static function GetUserData($email){
            try{
                $data = S_HPass::$db->QueryTop("SELECT userid,displayname,token FROM userdata WHERE email = ?;",[$email]);
                
                if (!isset($data["token"]) || !isset($data["displayname"]) || !isset($data["userid"])){
                    return false;
                }

                $arr = array(
                    "token" => $data["token"],
                    "displayname"=>$data["displayname"],
                    "serverid"=>$data["userid"],
                );

				return $arr;
			}catch(PDOException $e) {
				returnError($e);
			}
        }

        static function GenNewToken(){
            return bin2Hex(random_bytes(75));
        }

        static function CreateNewTokenReplace($email){
            $token = bin2Hex(random_bytes(75));
            S_HPass::$db->Query("REPLACE INTO userdata(token) VALUES(?) WHERE email = ?;",[$token,$email]);
            return $token;
        }
        
        static function ValidateUser($email,$pass){
            $doesExist = S_HPASS::DoesEmailExist($email);
            $escape = htmlspecialchars_decode($pass);
            if (!$doesExist){
                return "ERROR: Email doesn't exist";
            }else{
                $isValidated = S_HPASS::GetHash($email,$escape);
                if ($isValidated){
                    return S_HPass::GetUserData($email);
                }else{
                    return "ERROR: Password did not match!";
                }
                /*
                if ($isValidated){
                    return S_HPASS::CreateNewTokenReplace($email);
                }*/
            }
        }

        static function ValidateUserID($userid,$token){
            $data = S_HPass::$db->QueryTop("SELECT token FROM userdata WHERE userid = ?",[$userid]);
            if (isset($data["token"])){
                if (strcmp($token,$data["token"]) == 0){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }

        static function IsValidUser($email,$token){
            $escape = htmlspecialchars_decode($token);
            if (!S_HPASS::DoesEmailExist($email)){
                return false;
            }else{
                $data = S_HPass::$db->QueryTop("SELECT token FROM userdata WHERE email = ?",[$email]);

                if (isset($data["token"])){
                    if (strcmp($token,$data["token"]) == 0){
                        return true;
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }
            }
        }
		
	}
	
 ?>