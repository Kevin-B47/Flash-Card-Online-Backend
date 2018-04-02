<?php
    class FlashUser{

        private static $db;

        static function SetDB($db){
            FlashUser::$db = $db;
        }

        static function FetchUserData($userid){
            $userdata = array();

            $collections = FlashUser::$db->QueryTop("SELECT collectionid, collectionname WHERE userid = ?",[$userid]);
            $cards = FlashUser::$db->QueryTop("SELECT collectionid, cardid, fronttext, backtext WHERE userid = ?",[$userid]);

            return json_encode(array(
                "collections" => json_encode($collections),
                "cards" => json_encode($cards),
            ));
        }

        static function GetUserID($email){
            $userid = FlashUser::$db->QueryTop("SELECT userid FROM userdata WHERE email = ?",[$email]);

            if (isset($userid["userid"])){
                return $userid["userid"];
            }
            return 0;
        }

        static function GetDisplayName($id){
            $userid = FlashUser::$db->QueryTop("SELECT displayname FROM userdata WHERE userid = ?",[$id]);

            if (isset($userid["displayname"])){
                return $userid["displayname"];
            }
            return "Unkown";
        }

        static function GetUserDataWithEmail($email){
            $data = FlashUser::$db->QueryTop("SELECT userid,displayname,token FROM userdata WHERE email = ?",[$email]);

            $displayname = $data["displayname"];
            $token = $data["token"];
            $serverid = $data["userid"];

            $arr = array(
                "success"=>1,
                "displayname"=>$displayname,
                "email"=>$email,
                "token"=>$token,
                "serverid"=>$serverid,
            );
            return json_encode($arr);
        }

        static function GetUserData($email,$pass){
            $data = S_HPass::ValidateUser($email,$pass);

            //print_r($data);

            if (is_array($data) &&isset($data["displayname"]) && isset($data["token"]) && isset($data["serverid"])){
                $displayname = $data["displayname"];
                $token = $data["token"];
                $serverid = $data["serverid"];
    
                $arr = array(
                    "success"=>1,
                    "displayname"=>$displayname,
                    "email"=>$email,
                    "token"=>$token,
                    "serverid"=>$serverid,
                );
                return json_encode($arr);
            }else{
                $arr = array(
                    "success"=>-1,
                    "msg"=>"Error when getting user data"
                );
                return json_encode($arr);
            }
        }
    }
?>