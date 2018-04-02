<?php
 
    if ($_SERVER["REQUEST_METHOD"] != "POST"){
        $arr = array(
            "error"=>-1
        );
        echo json_encode($arr);
        return;
    }

    require_once("classes/funcs.class.php");

    $isUsernameSet = isset($_POST["username"]);
    $isTokenSet = isset($_POST["token"]);
    $isTextSet = isset($_POST["fronttext"]) && isset($_POST["backtext"]);
    $isCollectionSet = isset($_POST["collection"]);
    $isRemovingCard = isset($_POST["removecard"]);
    $hasCardID = isset($_POST["cardid"]);
    
    if (!$isUsernameSet || !$isTokenSet){
        echo GFuncs::ReturnError("Username or token didn't exist in request");
        return;
    }

    if (!$isTextSet || !$isCollectionSet){
        echo GFuncs::ReturnError("Text or collectionid didn't exist in request");
        return;
    }

    $email = $_POST["username"];
    $token = $_POST["token"];
    $frontText = "";
    $backText = "";
    $cardid = 0;

    if ($isTextSet){
        $frontText = $_POST["fronttext"];
        $backText = $_POST["backtext"];
    }

    if ($hasCardID){
        $cardid = $_POST["cardid"];
    }

    require_once("classes/salt.class.php");
    require_once("classes/db.class.php");
    require_once("classes/flashstorage.class.php");

    $db = new db_conn();
    S_HPass::SetDB($db);
    CardStorage::SetDB($db);

    $isUserValid = S_HPass::IsValidUser($email,$token);

    if (!$isUserValid){
        echo GFuncs::ReturnError("Token did not match user");
        return;
    }

    //TODO: just put the functions you want to do below

?>