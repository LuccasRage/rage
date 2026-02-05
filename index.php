<?php
session_start();
session_destroy();
include('function.php');
if(!isset($_SERVER['HTTP_REFERER'])){
header('location: '.$discord);
} else {
header('location: login');
}
?>