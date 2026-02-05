<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    // Code to send a one-time code to the email
    // Store the code in the session
    $_SESSION['one_time_code'] = $code;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <form method='post'>
        <input type='email' name='email' required placeholder='Enter your email'>
        <input type='submit' value='Email Me a One-Time Code'>
    </form>
</body>
</html>