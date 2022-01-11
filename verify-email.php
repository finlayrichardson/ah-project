<?php
require('./connect-db.php');
require('./tools.php');
// Session variables
session_start();

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $result = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM token WHERE token = '$token';"));
    $type = $result['type'];
    $user_id = $result['user_id'];
    $expiry_time = $result['expires'];
    // Check token is valid
    if ($type != "email") {
        echo "<p>Token is invalid</p>";
        exit();
    } elseif (strtotime($expiry_time) < strtotime('now')) {
        echo "<p>Token has expired</p>";
        exit();
    } else {
        // Token is valid
        mysqli_query($db, "UPDATE user SET verified = true WHERE user_id = '$user_id';");
        echo "<p>Email validated!</p><br>";
        if (isset($_SESSION['user_id']) && $user_id == $_SESSION['user_id']) {
            echo "<a href='index.php'>Home</a>";
        } else {
            echo "<a href='login.php'>Login</a>";
        }
        exit();
    }
}

if (isset($_SESSION['user_id'])) {
    // User has session variables
    $user_id = $_SESSION['user_id'];
    $email = $_SESSION['email'];
    $first_name = $_SESSION['first_name'];
    $last_name = $_SESSION['last_name'];

    $result = mysqli_query($db, "SELECT verified FROM user WHERE user_id = '$user_id';");
    $verified = mysqli_fetch_row($result)[0];
    if ($verified == 1) {
        // User is already verified
        load('index.php');
    }
} else {
    // User with no token and not logged in gets sent to login
    load();
}


if ($_SERVER['REQUEST_METHOD'] == "POST") {
    require('./email.php');

    $host = $_SERVER['HTTP_HOST'];
    $token = md5(random_bytes(10));
    $expiry_time = date('Y-m-d H:i:s', strtotime('+4 hours'));
    mysqli_query($db, "INSERT INTO token VALUES ('$token', 'email', $user_id, '$expiry_time');");

    $mail->addAddress($email, $first_name . '' . $last_name);
    $mail->Subject = "Verify Email";
    $mail->Body = "<html>
    <p>Please click the button below to verify your email.</p><br>
    <a href='http://$host/verify-email.php?token=$token' class='button'>Verify Email</a>
    </html>";
    $mail->AltBody = "Please visit http://$host/verify-email.php?token=$token to verify your email.";
    $mail->send();

    echo "<p>Email sent to $email</p>";
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Verify Email</title>
</head>

<body>
    <?php
    echo "<h1>Verify your email</h1>";
    echo "<p>Welcome $first_name $last_name, please click the button below to send a verification link to your email address.</p><br>";
    ?>
    <form method="POST" action="">
        <input type="submit" value="Send Verification Link">
    </form>
</body>

</html>
