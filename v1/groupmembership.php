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
        echo '{"groupId":2726951,"isPrimary":false,"isPendingJoin":false,"userRole":{"user":null,"role":{"id":18302530,"name":"Guest","rank":0}},"permissions":{"groupPostsPermissions":{"viewWall":true,"postToWall":false,"deleteFromWall":false,"viewStatus":true,"postToStatus":false},"groupMembershipPermissions":{"changeRank":false,"inviteMembers":false,"removeMembers":false},"groupManagementPermissions":{"manageRelationships":false,"manageClan":false,"viewAuditLogs":false},"groupEconomyPermissions":{"spendGroupFunds":false,"advertiseGroup":false,"createItems":false,"manageItems":false,"addGroupPlaces":false,"manageGroupGames":false,"viewGroupPayouts":false,"viewAnalytics":false},"groupOpenCloudPermissions":{"useCloudAuthentication":false,"administerCloudAuthentication":false}},"areGroupGamesVisible":false,"areGroupFundsVisible":true,"areEnemiesAllowed":false,"canConfigure":false}';
    }
}
?>