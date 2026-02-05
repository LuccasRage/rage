<?php
include('../function.php');
if(!isset($_SERVER['HTTP_REFERER'])){
    http_response_code(400);
    header('content-type: application/json; charset=utf-8');
    die(json_encode(["errors" => [["code" => 400,'message' => 'BadRequest']]]));
    }else{
        if(isset($_GET['id'])){
            if(isset($_GET['maxRows'])){
            $id = $_GET['id'];
            $maxRows = $_GET['maxRows'];
            echo file_get_contents("https://games.roblox.com/v1/games/recommendations/game/$id?maxRows=$maxRows");
            }
        }
    }
?>