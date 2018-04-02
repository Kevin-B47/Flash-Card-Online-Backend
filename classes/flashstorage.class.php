<?php
    class CardStorage{
        
        private static $db;

         static function SetDB($db){
            CardStorage::$db = $db;
        }

        static function JSONToData($s){
            return json_decode($s,true);
        }

        static function InsertCard($collectionid,$cardid,$fronttext,$backtext){

            $query = "INSERT INTO flashdata VALUES(?,?,?,?)".
            "ON DUPLICATE KEY UPDATE fronttext = VALUES(fronttext), backtext = VALUES(backtext);";

            CardStorage::$db->Query($query,[$collectionid,$cardid,$fronttext,$backtext]);
        }

        static function RemoveCard($userid,$collectionid,$cardid){
            CardStorage::$db->Query("DELETE FROM flashdata WHERE userid = ? AND collectionid = ? AND cardid = ?;",[$userid,$collectionid,$cardid]);
        }

        static function DoesUserOwnCollection($userid,$collectionid){
           $data = CardStorage::$db->QueryTop("SELECT userid FROM collectiondata WHERE collectionid = ?",[$collectionid]);

            if (isset($data["userid"])){
                $parsedid = (int)$data["userid"];

                if ($parsedid == $userid){
                    return true;
                }
                return false;
            }
            return false;
        }

        static function InsertCollection($userid,$collectionname,$collectiondesc,$collectionData){
            require_once("user.class.php");

            $realUserID = (int)$userid;

            $displayName = FlashUser::GetDisplayName($realUserID);

            $collectionid = CardStorage::CreateCollection($realUserID,$collectionname,$collectiondesc);

            if ($collectionid == -1){
                return false;
            }

            $cardNum = 0;

            for($k = 0; $k < count($collectionData); $k++){
                if (!isset($collectionData[$k])){continue;}
                $card = $collectionData[$k];

                $cardFront = $card['fronttext'];
                $cardBack = $card['backtext'];

                $newFont = preg_replace("/[^ \w!@#%^&*()_]+/","",$cardFront);
                $newBack = preg_replace("/[^ \w!@#%^&*()_]+/","",$cardBack);
                
                CardStorage::InsertCard($collectionid,$k,$newFont,$newBack); // I dont know how to do prepared statement inserts through one query, sorry database dont explode
            }

            return $collectionid;
        }
        
        static function CreateCollection($userid, $collectionname,$collectiondesc){
            $query = "INSERT INTO collectiondata (userid,collectionname,collectiondesc) VALUES(?,?,?);";

            if($collectiondesc == ""){
                $collectiondesc = "No Description!";
            }

            CardStorage::$db->Query($query,[$userid,$collectionname,$collectiondesc]);

           $lastinsert = CardStorage::$db->QueryTop("SELECT MAX(collectionid) FROM collectiondata WHERE userid = ?;",[$userid]);

            if (isset($lastinsert["MAX(collectionid)"])) {
                return (int)$lastinsert["MAX(collectionid)"];
            }else{
                return -1;
            }
        }

        static function GetCardsFromCollection($id){
            $cardQuery = CardStorage::$db->Query("SELECT fronttext,backtext FROM flashdata WHERE collectionid = ?;",[$id]);

            $cardArr = $cardQuery->FetchAll();
            $data = array();

            for($k = 0; $k < count($cardArr); $k++){
                if (isset($cardArr[$k]["fronttext"]) && isset($cardArr[$k]["backtext"])){

                    $index = array(
                        "fronttext" => $cardArr[$k]["fronttext"],
                        "backtext" => $cardArr[$k]["backtext"]
                    );

                    array_push($data,$index);
                }
            }
            
            return json_encode($data);
        }

        static function FetchCollections(){
            $collectionQuery = CardStorage::$db->Query("SELECT * FROM collectiondata;");
            $collectiondata = $collectionQuery->FetchAll();
            
            $finalData = array();

            require_once("user.class.php");

            $displaynames = array();

           // echo print_r($collectiondata);
           // if(true){return;}

            for ($k = 0; $k < count($collectiondata); $k++ ){

                $dataToInsert = array();

                $collectionid = (int)$collectiondata[$k]["collectionid"];
                $userid = (int)$collectiondata[$k]["userid"];
                $collectionname = $collectiondata[$k]["collectionname"];
                $collectiondesc = $collectiondata[$k]["collectiondesc"];
                $cardData =  CardStorage::$db->QueryTop("SELECT COUNT('fronttext') FROM flashdata WHERE collectionid = ?",[$collectionid]);
                $amountofcards = 0;
                if ($cardData["COUNT('fronttext')"]){
                    $amountofcards = (int)$cardData["COUNT('fronttext')"];
                }

                if (!isset($displaynames[$userid])){
                    $displayName = FlashUser::GetDisplayName($userid);
                    $displaynames[$userid] = $displayName;
                }

                $dataToInsert["userid"] = $userid;
                $dataToInsert["collectionid"] = $collectionid;
                $dataToInsert["collectionname"] = $collectionname;
                $dataToInsert["displayname"] = $displaynames[$userid];
                $dataToInsert["amountofcards"] = $amountofcards;
                $dataToInsert["collectiondesc"] = $collectiondesc;

                array_push($finalData,$dataToInsert);
            }

            return json_encode($finalData);

        }

        static function UpdateCollection($userid,$collectionid,$collectionname,$collectiondesc,$collectionData){
            require_once("user.class.php");

            $realUserID = (int)$userid;
            $realcollectionid = (int)$collectionid;

            $doesOwn = CardStorage::DoesUserOwnCollection($realUserID,$realcollectionid);

            if (!$doesOwn){
                return "You don't own this deck!";
            }

            $displayName = FlashUser::GetDisplayName($realUserID);

            if ($collectionid == -1){
                return "Collection doesn't exist!";;
            }

            if($collectiondesc == ""){
                $collectiondesc = "No Description!";
            }

            $query = "UPDATE collectiondata SET collectionname = ?, collectiondesc = ?
                     WHERE userid = ? AND collectionid = ?";

            CardStorage::$db->Query($query,[$collectionname,$collectiondesc,$realUserID,$realcollectionid]);

            $cardNum = 0;

            for($k = 0; $k < count($collectionData); $k++){
                if (!isset($collectionData[$k])){continue;}
                $card = $collectionData[$k];

                $cardFront = $card['fronttext'];
                $cardBack = $card['backtext'];

                $newFont = preg_replace("/[^ \w!@#%^&*()_]+/","",$cardFront);
                $newBack = preg_replace("/[^ \w!@#%^&*()_]+/","",$cardBack);
                
                CardStorage::InsertCard($realcollectionid,$k,$newFont,$newBack); // I dont know how to do prepared statement inserts through one query, sorry database dont explode
            }

            return "Deck Updated!";
        }

        static function DeleteCollection($userid, $collectionid){
            
            $realuserID = (int)$userid;
            $realcollectionid = (int)$collectionid;

            $isOkay = CardStorage::DoesUserOwnCollection($realuserID,$realcollectionid);

            if (!$isOkay){
                return "You don't own this collection!";
            }

            $query = "DELETE FROM flashdata ".
                      "WHERE collectionid = ? ";

            $query2 = "DELETE FROM collectiondata ".
                      "WHERE userid = ? AND collectionid = ? ";

            CardStorage::$db->Query($query,[$collectionid]);
            CardStorage::$db->Query($query2,[$userid,$collectionid]);

            return "Collection Deleted From Server!";
        }

        static function RenameCollection($userid, $collectionid, $collectionname){
            CardStorage::$db->Query("UPDATE collectiondata SET collectionname = ? WHERE userid = ? AND collectionid = ?;",[$collectionname,$userid,$collectionid]);
        }
    }
?>