<?php
header('content-type: application/json; charset=utf-8');
include('../function.php');

if(!isset($_SERVER['HTTP_REFERER'])){
    http_response_code(400);
    die(json_encode(["errors" => [["code" => 400,'message' => 'BadRequest']]]));
    }elseif(isset($_GET['id'])){
    if(is_numeric($_GET['id'])){
        echo '{"errors":[{"code":1,"message":"Group is invalid or does not exist.","userFacingMessage":"The group is invalid or does not exist."}]}';
    }
}
?>