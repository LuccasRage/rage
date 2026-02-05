<?php
header('content-type: application/json; charset=utf-8');
include('../function.php');
if(!isset($_SERVER['HTTP_REFERER'])){
    http_response_code(400);
    die(json_encode(["errors" => [["code" => 400,'message' => 'BadRequest']]]));
    }else{
        if(isset($_GET['cursor'])){
            $cursor = $_GET['cursor'];
            $sortOrder = $_GET['sortOrder'];
            $excludeFullGames = $_GET['excludeFullGames'];
            $id = $_GET['id'];
            echo file_get_contents('https://games.roblox.com/v1/games/'.$id.'/servers/VIP?cursor='.$cursor.'&sortOrder='.$sortOrder.'&excludeFullGames='.$excludeFullGames);
        }
    }
?>