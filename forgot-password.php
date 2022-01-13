<?php
require('auth.php');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    require('connect-db.php');
    $errors = array();
    // Check an email has been entered
    if (empty($_POST['email'])) {
        $errors[] = "Please enter an email";
    } else {
        $email = mysqli_real_escape_string($db, trim($_POST['email']));
    }
    // Check if user is in database
    if (empty($errors)) {
        $result = query("SELECT * FROM user WHERE email = ?;", 's', $email);
        $user = mysqli_fetch_assoc($result);
        if (mysqli_num_rows($result) == 0) {
            $errors[] = "User does not exist";
        }
    }
    // No errors
    if (empty($errors)) {
        require('./email.php');
        // Set user details
        $user_id = $user['user_id'];
        $first_name = $user['first_name'];
        $last_name = $user['last_name'];
        // Send email
        $host = $_SERVER['HTTP_HOST'];
        $token = md5(random_bytes(10));
        $expiry_time = date('Y-m-d H:i:s', strtotime('+4 hours'));
        mysqli_query($db, "INSERT INTO token VALUES ('$token', 'password', $user_id, '$expiry_time');");

        $mail->addAddress($email, $first_name . '' . $last_name);
        $mail->Subject = "Reset Login Details"; //change to something that works
        $mail->Body = "<html>
        <p>Please click the button below to reset your password.</p><br>
        <a href='http://$host/reset-password.php?token=$token' class='button'>Reset Password</a>
        </html>";
        $mail->AltBody = "Please visit http://$host/reset-password.php?token=$token to reset your password.";
        $mail->send();

        echo "<p>Email sent to $email</p>";
        exit();
    } else {
        // Display errors
        echo "<h1>Error!</h1>
        <p>The following error(s) occured:<br>";
        foreach ($errors as $error) {
            echo "- $error<br>";
        }
        echo "<p>Please try again.</p>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Forgot Password</title>
</head>

<body>
    <h1>Forgot Password</h1>
    <p>Please enter your email below and a link will be sent to change your password.</p>
    <form method="POST" action="">
        <input type="text" name="email" required autofocus placeholder="Email" value="<?php if (isset($_GET['email'])) echo $_GET['email']; ?>"><br>
        <input type="submit" value="Send Reset Link">
    </form>
</body>

</html>
