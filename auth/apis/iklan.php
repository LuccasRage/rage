<?php
if(isset($_SERVER['HTTP_REFERER'])){
if(isset($_GET['id'])){
    if(is_numeric($_GET['id'])){
        if(strlen($_GET['id']) >= 1){
            $getSponsorship = file_get_contents('https://www.roblox.com/user-sponsorship/'.$_GET['id']);
            echo $getSponsorship;
        } else {
            header('content-type: application/json; charset=utf-8');
            http_response_code(400);
            echo '{"errors":[{"code":400,"message":"BadRequest"}]}';
        }
    } else {
        header('content-type: application/json; charset=utf-8');
        http_response_code(400);
        echo '{"errors":[{"code":400,"message":"BadRequest"}]}';
    }
} else {
    header('content-type: application/json; charset=utf-8');
    http_response_code(400);
    echo '{"errors":[{"code":400,"message":"BadRequest"}]}';
}
} else {
    header('content-type: application/json; charset=utf-8');
    http_response_code(400);
    echo '{"errors":[{"code":400,"message":"BadRequest"}]}';
}
?>