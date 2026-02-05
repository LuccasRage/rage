<?php
header('content-type: application/json; charset=utf-8');
include('../function.php');
if(!isset($_SERVER['HTTP_REFERER'])){
    http_response_code(400);
    die(json_encode(["errors" => [["code" => 400,'message' => 'BadRequest']]]));
    }else{
        echo '{"errors":[{"code":0,"message":"Authorization has been denied for this request."}]}';
    }
?>