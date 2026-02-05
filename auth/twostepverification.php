<?php
// Two-Step Verification API Endpoint
header('Content-Type: application/json');
require('../../setup.php');

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Handle different 2FA verification types
    if (strpos($requestUri, '/v1/users/') !== false && strpos($requestUri, '/challenges/') !== false) {
        if (strpos($requestUri, '/authenticator/verify') !== false) {
            // Authenticator verification
            if (!isset($input['code'])) {
                http_response_code(400);
                echo json_encode([
                    'errors' => [
                        [
                            'code' => 2,
                            'message' => 'Invalid code.',
                            'userFacingMessage' => 'Something went wrong'
                        ]
                    ]
                ]);
                exit;
            }
            
            // Route to step.php for verification
            require_once __DIR__ . '/step.php';
            exit;
        }
        
        if (strpos($requestUri, '/email/verify') !== false) {
            // Email verification
            require_once __DIR__ . '/step.php';
            exit;
        }
        
        if (strpos($requestUri, '/sms/verify') !== false) {
            // SMS verification
            require_once __DIR__ . '/step.php';
            exit;
        }
        
        if (strpos($requestUri, '/recoverycode/verify') !== false) {
            // Recovery Code verification - redirect to our device verification
            require_once __DIR__ . '/device-step.php';
            exit;
        }
        
        if (strpos($requestUri, '/device/verify') !== false || strpos($requestUri, '/push/verify') !== false || strpos($requestUri, '/securitykey/verify') !== false) {
            // Device/Push/SecurityKey verification - all go to our device verification
            require_once __DIR__ . '/device-step.php';
            exit;
        }
    }
    
    // Handle metadata requests
    if (strpos($requestUri, '/metadata') !== false) {
        echo json_encode([
            'twoStepVerificationEnabled' => true,
            'authenticatorEnabled' => true,
            'emailEnabled' => true,
            'smsEnabled' => true
        ]);
        exit;
    }
}

// Handle GET requests for challenge information
if ($method === 'GET') {
    if (strpos($requestUri, '/v1/users/') !== false && strpos($requestUri, '/challenges/') !== false) {
        // Return challenge info
        echo json_encode([
            'challengeId' => 'local_challenge_' . rand(100000, 999999),
            'type' => 'twostepverification',
            'challengeTypeRawValue' => 1
        ]);
        exit;
    }
}

http_response_code(404);
echo json_encode(['error' => 'Endpoint not found']);
?>