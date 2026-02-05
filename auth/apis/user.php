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
        $displayname = $row['displayname'];
        $description = $row['descriptions'];
        $newDate = date("Y-m-d", strtotime($row['joindate']));
        
        $userid = $row['avatar'];
        $username = $row['username'];
        echo '{"created":"'.$newDate.'","description":"'.$description.'","displayName":"'.$displayname.'","externalAppDisplayName":null,"hasVerifiedBadge":false,"id":"'.$userid.'","isBanned":false,"name":"'.$username.'"}';
        // echo '{"created":"'.$newDate.'", "description":"'.$description.'", "isBanned":false,"externalAppDisplayName":null,"hasVerifiedBadge":true,"id":'.$userid.',"name":"'.$username.'","displayName":"'.$displayname.'"}';
        //echo request("https://users.roblox.com/v1/users/1");
    }
}
?>