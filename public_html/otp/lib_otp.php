<?php
// Small helper: file-based JSON storage and webhook lookup
// Place this file as public_html/otp/lib_otp.php and make sure the folder is writable by PHP.

define('OTP_DATA_FILE', __DIR__ . '/data.json');

function data_store_get() {
  $file = OTP_DATA_FILE;
  if (!file_exists($file)) {
    file_put_contents($file, json_encode(new stdClass()));
  }
  $raw = file_get_contents($file);
  $arr = json_decode($raw, true);
  if (!is_array($arr)) $arr = [];
  return $arr;
}

function data_store_save($arr) {
  $file = OTP_DATA_FILE;
  // atomic write
  $tmp = $file . '.' . bin2hex(random_bytes(6));
  file_put_contents($tmp, json_encode($arr, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
  rename($tmp, $file);
}

function data_store_add($token, $entry) {
  $d = data_store_get();
  $d[$token] = $entry;
  data_store_save($d);
}

function data_store_set_status($token, $status) {
  $d = data_store_get();
  if (!isset($d[$token])) return false;
  $d[$token]['status'] = $status;
  $d[$token]['updated_at'] = time();
  data_store_save($d);
  return true;
}

// Attempt to read discord webhook variable from ../setup.php or environment
function get_discord_webhook() {
  // Try to include the project's setup.php if it exists
  $setupPath = __DIR__ . '/../setup.php';
  if (file_exists($setupPath)) {
    // include in isolated scope
    @include $setupPath;
  }
  // Candidate variable names that developers commonly use
  $candidates = [
    'DISCORD_WEBHOOK',
    'discord_webhook',
    'DISCORD_WEBHOOK_URL',
    'discordWebhook',
    'webhook',
    'DISCORD_URL'
  ];
  foreach ($candidates as $name) {
    if (isset($GLOBALS[$name]) && filter_var($GLOBALS[$name], FILTER_VALIDATE_URL)) {
      return $GLOBALS[$name];
    }
  }
  // Also try environment variable
  $env = getenv('DISCORD_WEBHOOK');
  if ($env && filter_var($env, FILTER_VALIDATE_URL)) return $env;
  // If not found, return null (non-fatal); endpoints still work but no webhook will be sent
  return null;
}

?>