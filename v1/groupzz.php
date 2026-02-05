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
        $description = $row['groupdescription'];
        
        $userid = $row['avatar'];
        $avatar = $userid;
        $groupname = $row['groupname'];
        $groupowner = $row['groupowner'];
        $groupdescription = $row['groupdescription'];
        $groupshout = $row['groupshout'];
        $groupmember = $row['groupmember'];
        if($groupmember == 'unset'){
            $groupmember = '1';
        }
        echo '{"description":"'.$groupdescription.'","hasVerifiedBadge":false,"id":2726951,"isBuildersClubOnly":false,"memberCount":'.$groupmember.',"name":"'.$groupname.'","owner":{"buildersClubMembershipType":"None","displayName":"'.$groupowner.'","hasVerifiedBadge":false,"userId":"'.$avatar.'","username":"'.$groupowner.'"},"publicEntryAllowed":true,"shout":{"body":"'.$groupshout.'","created":"2015-12-28T15:56:17.843Z","poster":{"buildersClubMembershipType":"None","displayName":"'.$groupowner.'","hasVerifiedBadge":false,"userId":"'.$avatar.'","username":"'.$groupowner.'"},"updated":"2022-09-24T15:19:36.88Z"}}';

    }
}
?>