
<?php
header('content-type: application/json; charset=utf-8');
include('../function.php');

if(!isset($_SERVER['HTTP_REFERER'])){
    http_response_code(400);
    die(json_encode(["errors" => [["code" => 400,'message' => 'BadRequest']]]));
    }elseif(isset($_GET['id'])){
    if(is_numeric($_GET['id'])){
        $id = $_GET['id'];
        echo file_get_contents('https://games.roblox.com/v2/games/'.$id.'/media');
    }
}
?>