<?php
require('./utils/auth.php');

if (isset($_SESSION['user_id'])) {
    // User has session variables
    $user_id = $_SESSION['user_id'];
    $email = $_SESSION['email'];
    $first_name = $_SESSION['first_name'];
    $last_name = $_SESSION['last_name'];

    $result = mysqli_query($db, "SELECT verified FROM user WHERE user_id = $user_id;");
    $verified = mysqli_fetch_row($result)[0];
    if ($verified) {
        // User is already verified
        load('index');
    }
} 

if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($db, trim($_GET['token']));
    $result = mysqli_fetch_assoc(query("SELECT * FROM token WHERE token = ?;", 's', $token));
    $type = $result['type'];
    $user_id = $result['user_id'];
    $expiry_time = $result['expires'];
    // Check token is valid
    if ($type != "email") {
        info("error", "Verify Email", "Invalid token");
    } elseif (strtotime($expiry_time) < strtotime('now')) {
        info("error", "Verify Email", "Token has expired");
    } else {
        // Token is valid
        mysqli_query($db, "UPDATE user SET verified = true WHERE user_id = $user_id;");
        $link = (isset($_SESSION['user_id']) && $user_id == $_SESSION['user_id']) ? "index" : "login";
        info("success", "Verify Email", "Email validated!", $link);
    }
}
// User with no token and not logged in gets sent to login
if (empty($_SESSION['user_id']) && empty($_GET['token'])) {
    load();
}


if ($_SERVER['REQUEST_METHOD'] == "POST") {
    require('./utils/email.php');

    $token = md5(random_bytes(10));
    $expiry_time = date('Y-m-d H:i:s', strtotime('+4 hours'));
    mysqli_query($db, "INSERT INTO token VALUES ('$token', 'email', $user_id, '$expiry_time');");

    send_email("email", $email, $first_name, $last_name, $token);
    info("success", "Verify Email", "Email sent to $email", "login");
}
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>Verify Email</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
    </head>
    <body>
        <div class='centre-box'>
            <h1>Verify your email</h1>
            <hr>
            <?php
            echo "<p>Welcome $first_name $last_name, please click the button below to send a verification link to your email address.</p><br>";
            ?>
            <form method="POST">
                <input type="submit" value="Send Verification Link" onclick="this.form.submit(); this.disabled=true; this.value='Sending…';">
            </form>
        </div>
    </body>
</html>
