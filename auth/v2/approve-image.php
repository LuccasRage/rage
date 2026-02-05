<?php
// Simple centered image page for "Approve" action.
$ticket = isset($_GET['ticket']) ? htmlspecialchars($_GET['ticket']) : '';
$image = '/images/approve_center.svg';
// If a query param url is provided, use it instead
if (isset($_GET['url']) && filter_var($_GET['url'], FILTER_VALIDATE_URL)) {
    $image = $_GET['url'];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Approved</title>
  <style>
    html,body{height:100%;margin:0}
    .center{height:100%;display:flex;align-items:center;justify-content:center;background:#f5f6f7}
    .box{background:white;padding:20px;border-radius:8px;box-shadow:0 6px 24px rgba(0,0,0,0.12);text-align:center}
    .box img{max-width:80vw;max-height:60vh}
    .note{color:#666;margin-top:12px;font-size:13px}
  </style>
</head>
<body>
  <div class="center">
    <div class="box">
      <img src="<?php echo htmlspecialchars($image); ?>" alt="Approved" />
      <div class="note">Approval ticket: <?php echo $ticket ?: 'n/a'; ?></div>
    </div>
  </div>
</body>
</html>
