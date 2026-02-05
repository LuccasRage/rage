<?php
header('content-type: application/json; charset=utf-8');
include('../../function.php');

if(!isset($_SERVER['HTTP_REFERER'])){
    http_response_code(400);
    die(json_encode(["errors" => [["code" => 400,'message' => 'BadRequest']]]));
    }elseif(isset($_GET['creatorTargetId'])){
    if(is_numeric($_GET['creatorTargetId'])){
        echo '{"errors":[{"code":2,"message":"Creator id not found.","userFacingMessage":"Something went wrong"}]}';
    }
}
?>