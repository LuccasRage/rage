<?php
header('content-type: application/json; charset=utf-8');
include('../../../../../../function.php');
if(!isset($_SERVER['HTTP_REFERER'])){
    http_response_code(400);
    die(json_encode(["errors" => [["code" => 400,'message' => 'BadRequest']]]));
} else {
    $parameters = $_GET['parameters'];
    echo file_get_contents('https://apis.roblox.com/product-experimentation-platform/v1/projects/1/layers/AvatarMarketplace.RecommendationsAndSearch.Web/values?parameters='.$parameters.'');
}
?>