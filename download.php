<?php

    // You don't really need validation for this stage as its not uploading data

    $isDownloading = isset($_GET["id"]);


    require_once("classes/db.class.php");
    require_once("classes/funcs.class.php");
    require_once("classes/flashstorage.class.php");
    require_once("classes/user.class.php");

    $db = new db_conn();
    CardStorage::SetDB($db);
    FlashUser::SetDB($db);


    if (!$isDownloading){
        echo CardStorage::FetchCollections();
    }else{
        echo CardStorage::GetCardsFromCollection($_GET["id"]);
    }
?>