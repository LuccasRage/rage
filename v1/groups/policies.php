<?php
header('content-type: application/json; charset=utf-8');
include('../../function.php');
if(!isset($_SERVER['HTTP_REFERER'])){
    http_response_code(400);
    die(json_encode(["errors" => [["code" => 400,'message' => 'BadRequest']]]));
    }else{
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $request_body = file_get_contents('php://input');
        $ch = curl_init();
            
        curl_setopt($ch, CURLOPT_URL, 'https://groups.roblox.com/v1/groups/policies');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ''.$request_body.''
        );

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Accept: application/json';
        $headers[] =   'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.163 Safari/537.36';
        $headers[] =    'Referer: https://www.roblox.com/';
        $headers[] = 'Origin: https://www.roblox.com';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        echo $result;
    } else {
        http_response_code(400);
        die(json_encode(["errors" => [["code" => 400,'message' => 'BadRequest']]]));
    }
}
?>