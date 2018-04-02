<?php
     if ($_SERVER["REQUEST_METHOD"] != "POST"){
        $arr = array(
            "error"=>-1
        );
        echo json_encode($arr);
        return;
    }

    $isUserIDSet = isset($_POST["userid"]);
    $isTokenSet = isset($_POST["token"]);
    $isCollectionDataSet = isset($_POST["collectiondata"]);
    $isCollectionNameSet = isset($_POST["collectionname"]);
    $isCollectionDescSet = isset($_POST["collectiondesc"]);
    $isCollectionIDSet = isset($_POST["collectionid"]);
    $isCollectionDeletionSet = isset($_POST["delete"]);

    require_once("classes/db.class.php");
    require_once("classes/salt.class.php");
    require_once("classes/funcs.class.php");
    require_once("classes/flashstorage.class.php");
    require_once("classes/user.class.php");

    $db = new db_conn();
    S_HPass::SetDB($db);
    CardStorage::SetDB($db);
    FlashUser::SetDB($db);


    if (!$isUserIDSet || !$isTokenSet){
        echo GFuncs::ReturnError("No data was sent");
        return;
    }

    $collectionDesc = "";

    if ($isCollectionDescSet){
        $collectionDesc = $_POST["collectiondesc"];
    }

    $isUserValidated = S_HPass::ValidateUserID($_POST["userid"],$_POST["token"]);


    if($isUserValidated && $isCollectionDeletionSet && $isCollectionIDSet){
        $msg = CardStorage::DeleteCollection($_POST["userid"],$_POST["collectionid"]);

        $arr = array(
            "success"=>1,
            "msg"=>$msg,
        );

        echo json_encode($arr);
        return;
    }
    
    if (!$isUserValidated || !$isCollectionDataSet || !$isCollectionNameSet){
        echo GFuncs::ReturnError("Your session is invalid ". $_POST["userid"]);
        return;
    }


    $cardData = CardStorage::JSONToData($_POST["collectiondata"]);

    $msg = "";

    if ($isCollectionIDSet && !$isCollectionDeletionSet){
        $msg = CardStorage::UpdateCollection($_POST["userid"],$_POST["collectionid"],$_POST["collectionname"],$collectionDesc,$cardData);

        $arr = array(
            "success"=>1,
            "msg"=>$msg,
        );

        echo json_encode($arr); 
    }else{
        $collectionid = (int)CardStorage::InsertCollection($_POST["userid"],$_POST["collectionname"],$collectionDesc,$cardData);

        $help = (int)$collectionid;

        $arr = array(
            "success"=>1,
            "msg"=>"Deck uploaded!",
            "deckid"=>$help,
        );
        
        $msg = json_encode($arr);
    
        if ($collectionid == -1){
            $msg = GFuncs::ReturnError("There was an error inserting your collection!");
        }
    
        echo $msg;
    }
     //TODO: just put the functions you want to do below




?>