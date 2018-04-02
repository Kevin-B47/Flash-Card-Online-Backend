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

    if (!$isTokenSet || !$isUsernameSet){
        echo GFuncs::ReturnError("No username or token was received");
        return;
    }

    $email = $_POST["username"];
    $token = $_POST["token"];

    require_once("classes/db.class.php");
    require_once("classes/salt.class.php");
    require_once("classes/flashstorage.class.php");

    $db = new db_conn();
    S_HPass::SetDB($db);
    FlashUser::SetDB($db);

    $isUserValid = S_HPass::IsValidUser($email,$token);

    if (!$isUserValid){
        echo GFuncs::ReturnError("Token did not match user");
        return;
    }

    $userid = FlashUser::GetUserID($email);

    if ($userid == 0){
        echo GFuncs::ReturnError("Userid didn't exist");
        return;
    }

    return FlashUser::FetchUserData($usderid);
?>