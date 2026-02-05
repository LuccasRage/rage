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
            $limit = $_GET['limit'];
            $id = $_GET['id'];
            echo file_get_contents("https://badges.roblox.com/v1/universes/$id/badges?cursor=$cursor&limit=$limit&sortOrder=$sortOrder");
        }
    }
?>