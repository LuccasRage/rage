<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

echo json_encode([
    'twoStepVerificationEnabled' => true,
    'authenticatorEnabled' => true,
    'emailEnabled' => true,
    'smsEnabled' => false,
    'recoveryCodeEnabled' => false
]);
?>