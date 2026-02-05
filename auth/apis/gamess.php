<?php
header('content-type: application/json; charset=utf-8');
include('../function.php');
if(!isset($_SERVER['HTTP_REFERER'])){
    http_response_code(400);
    die(json_encode(["errors" => [["code" => 400,'message' => 'BadRequest']]]));
    }elseif(isset($_GET['id'])){
    if(is_numeric($_GET['id'])){
        $id = $_GET['id'];
        $row = $db->query("SELECT * FROM profile WHERE siteid=$id")->fetch();
        // code to fetch place visits from db by siteid as $id
        $placevisits = $row['placevisits'];
        echo '{"data":[{"placeVisits":'.$placevisits.'}]}';
    }
}
?>