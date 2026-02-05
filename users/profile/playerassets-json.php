<?php
header('content-type: application/json; charset=utf-8');
include('../../function.php');
if(!isset($_SERVER['HTTP_REFERER'])){
    http_response_code(400);
    die(json_encode(["errors" => [["code" => 400,'message' => 'BadRequest']]]));
} else {
if(isset($_GET["assetTypeId"])){
    if(is_numeric($_GET['userId'])){
        if(is_numeric($_GET['assetTypeId'])){
            $assetTypeId = $_GET["assetTypeId"];
            $userId = $_GET["userId"];
            echo request("https://www.roblox.com/users/profile/playerassets-json?assetTypeId=$assetTypeId&userId=$userId");
        } else {
            http_response_code(400);
            die(json_encode(["errors" => [["code" => 400,'message' => 'BadRequest']]]));
        }
    } else {
        http_response_code(400);
        die(json_encode(["errors" => [["code" => 400,'message' => 'BadRequest']]]));
    }
} else {
    http_response_code(400);
    die(json_encode(["errors" => [["code" => 400,'message' => 'BadRequest']]]));
}
}
?>