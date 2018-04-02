<?php 
    if ($_SERVER["REQUEST_METHOD"] != "POST"){
        $arr = array(
            "error"=>-1
        );
        echo json_encode($arr);
        return;
    }

    require_once("classes/funcs.class.php");

    $isPassSet = isset($_POST["password"]);
    $isUsernameSet = isset($_POST["username"]);
    $isTokenSet = isset($_POST["token"]);
    $isNewUser = isset($_POST["newuser"]) && isset($_POST["displayname"]);

    if ((!$isUsernameSet || !$isPassSet) && !$isTokenSet){
        echo GFuncs::ReturnError("Username, password or token didn't exist in request");
        return;
    }

    if ($isPassSet){
        $allMatches = array();
        $match = preg_match("/[a-zA-Z0-9!@#_=]+/",htmlspecialchars_decode($_POST["password"]),$allMatches);

        if(!isset($allMatches[0]) || strlen($allMatches[0]) != strlen($_POST["password"])){
            echo GFuncs::ReturnError("Password is not allowed you had " . htmlspecialchars_decode($_POST["password"]));
            return;
        }
    }

    require_once("classes/db.class.php");
    require_once("classes/salt.class.php");
    require_once("classes/user.class.php");

    $username = htmlspecialchars_decode($_POST["username"]);
    $pass = "";

    if (isset($_POST["password"])){
        $pass = htmlspecialchars_decode($_POST["password"]);
    }

    $db = new db_conn();
	$salt_method = new S_HPass();
	
    S_HPass::SetDB($db);
    FlashUser::SetDB($db);
    
    if ($isNewUser){

        if (!filter_var($_POST["username"],FILTER_VALIDATE_EMAIL)){
            echo GFuncs::ReturnError("Not a valid email address!");
            return;
        }
        
        $newUserInsert = $salt_method->InsertInfo($username,$pass,$_POST["displayname"]);

        if (substr($newUserInsert,0,5) == "ERROR"){
            echo GFuncs::ReturnError($newUserInsert);
        }else{
            echo FlashUser::GetUserData($username,$pass);
        }
    }else if($isTokenSet){
        $isGood = S_HPass::IsValidUser($username,$_POST["token"]);

        if ($isGood){
            echo FlashUser::GetUserDataWithEmail($username);
        }else{
            echo GFuncs::ReturnError("Token did not match user");
            return;
        }
    }else{

        $data = S_HPass::ValidateUser($username,$pass);

        if (is_bool($data) && !$data){
            echo GFuncs::ReturnError($token);
        }else{
            echo FlashUser::GetUserData($username,$pass);
        }
    }

?>