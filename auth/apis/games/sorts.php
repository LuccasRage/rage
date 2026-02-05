<?php
header('content-type: application/json; charset=utf-8');
include('../../function.php');

if(!isset($_SERVER['HTTP_REFERER'])){
    http_response_code(400);
    die(json_encode(["errors" => [["code" => 400,'message' => 'BadRequest']]]));
    }elseif(isset($_GET['gameSortsContext'])){
$contect = $_GET['gameSortsContext'];
$url = "https://games.roblox.com/v1/games/sorts?gameSortsContext=$contect";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
   "Accept: application/json",
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//for debug only!
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
echo $resp;
    }
?>
