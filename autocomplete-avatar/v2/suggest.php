<?php
header('content-type: application/json; charset=utf-8');
include('../../function.php');
if(!isset($_SERVER['HTTP_REFERER'])){
    http_response_code(400);
    die(json_encode(["errors" => [["code" => 400,'message' => 'BadRequest']]]));
    } else {
        $prefix = $_GET['prefix'];
        $limit = $_GET['limit'];
        $lang = $_GET['lang'];
        echo file_get_contents('https://apis.rbxcdn.com/autocomplete-avatar/v2/suggest?prefix='.$prefix.'&limit='.$limit.'&lang='.$lang.'');
    }