<?php
require('resources/auth.php');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    require('./resources/connect-db.php');
    $errors = array();
    // Check an email has been entered
    if (empty($_POST['email'])) {
        $errors['email'] = "⚠ Please enter an email";
    } else {
        $email = mysqli_real_escape_string($db, trim($_POST['email']));
    }
    // Check if user is in database
    if (empty($errors)) {
        $result = query("SELECT * FROM user WHERE email = ?;", 's', $email);
        $user = mysqli_fetch_assoc($result);
        if (mysqli_num_rows($result) == 0) {
            $errors['email'] = "⚠ User does not exist";
        }
    }
    // No errors
    if (empty($errors)) {
        require('./resources/email.php');
        // Set user details
        $user_id = $user['user_id'];
        $first_name = $user['first_name'];
        $last_name = $user['last_name'];
        // Send email
        $host = $_SERVER['HTTP_HOST'];
        $token = md5(random_bytes(10));
        $expiry_time = date('Y-m-d H:i:s', strtotime('+4 hours'));
        mysqli_query($db, "INSERT INTO token VALUES ('$token', 'password', $user_id, '$expiry_time');");

        $mail->addAddress($email, $first_name . ' ' . $last_name);
        $mail->Subject = "Reset Password";
        $mail->Body = "<html lang='en'>
        <p>Please click the button below to reset your password.</p><br>
        <a href='http://$host/reset-password/$token' class='button'>Reset Password</a>
        </html>";
        $mail->AltBody = "Please visit http://$host/reset-password/$token to reset your password.";
        $mail->send();

        echo "<p>Email sent to $email</p>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>Forgot Password</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
    </head>
    <body>
        <h1>Forgot Password</h1>
        <p>Please enter your email below and a link will be sent to change your password.</p>
        <form method="POST" novalidate>
            <input type="text" autocorrect="off" autocapitalize="none" name="email" required autofocus placeholder="Email" value="<?php if (isset($_GET['email'])) echo $_GET['email']; ?>">
            <?php
            if (isset($errors['email'])) {
                $error = $errors['email'];
                echo "<p class='error'>$error</p>";
            }
            ?>
            <input type="submit" value="Send Reset Link">
        </form>
        <script>
            function validate() {
                // Remove existing errors
                while (document.getElementsByClassName('error')[0]) {
                    document.getElementsByClassName('error')[0].remove();
                }

                let valid = true;
                const email = document.getElementsByTagName("input")['email'];

                // Validate email
                if (email.validity.valueMissing) {
                    email.insertAdjacentHTML('afterend', '<p class="error">⚠ Please enter an email</p>');
                    valid = false;
                }
                return valid;
            }

            document.getElementsByTagName('form')[0].onsubmit = validate;
        </script>
    </body>
</html>
