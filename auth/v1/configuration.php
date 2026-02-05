<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require('../../setup.php');

// Return user-specific 2FA configuration as proper JSON
echo json_encode([
    "primaryMediaType" => $twoStepVerificationType ?? "Authenticator",
    "methods" => [
        [
            "mediaType" => "Email",
            "enabled" => true,
            "updated" => null
        ],
        [
            "mediaType" => "Authenticator", 
            "enabled" => true,
            "updated" => null
        ],
        [
            "mediaType" => "RecoveryCode",
            "enabled" => true,
            "updated" => null
        ]
    ]
]);
?>