<?php
header('content-type: application/json; charset=utf-8');
include('../../../../function.php');
if(!isset($_SERVER['HTTP_REFERER'])){
    http_response_code(400);
    die(json_encode(["errors" => [["code" => 400,'message' => 'BadRequest']]]));
} else {
    $request_body = file_get_contents('php://input');
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://apis.roblox.com/product-experimentation-platform/v1/projects/1/values');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ''.$request_body.''
        );

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Accept: application/json';
        $headers[] =   'User-Agent: Unknown';
        $headers[] =    'Referer: https://www.roblox.com/';
        $headers[] = 'Origin: https://www.roblox.com';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        echo $result;
}
?>