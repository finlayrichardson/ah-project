<?php
require('./auth.php');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_REQUEST['token'])) {
        $token = $_REQUEST['token'];
        $result = mysqli_fetch_assoc(query("SELECT * FROM token WHERE token = ?;", 's', $token));
        $type = $result['type'];
        $user_id = $result['user_id'];
        $expiry_time = $result['expires'];
        // Check token is valid
        if ($type != "password") {
            echo "<p>Token is invalid</p>";
            exit();
        } elseif (strtotime($expiry_time) < strtotime('now')) {
            echo "<p>Token has expired</p>";
            exit();
        } else { // Token is valid
            // Validate password
            if (empty($_POST['password1'])) {
                $errors[] = "Please enter a password";
            } elseif (empty($_POST['password2'])) {
                $errors[] = "Please confirm password";
            } elseif ($_POST['password1'] !== $_POST['password2']) {
                $errors[] = "Passwords must match";
            } else {
                $password = password_hash(trim($_POST['password1']), PASSWORD_BCRYPT);
            }

            // Change password or display errors
            if (empty($errors)) {
                // Change password
                mysqli_query($db, "UPDATE user SET password = '$password' WHERE user_id = $user_id;");
                echo "<p>Password successfully changed!</p><br>";
                echo "<a href='login.php'>Login</a>";
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
    } else {
        load('./forgot-password.php');
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Reset Password</title>
</head>

<body>
    <h1>Reset Password</h1>
    <p>Please enter your new password below to change it.</p>
    <form method="POST">
        <input type="password" name="password1" required placeholder="Password" value="<?php if (isset($_POST['password1'])) echo $_POST['password1']; ?>"><br>
        <input type="password" name="password2" required placeholder="Confirm Password" value="<?php if (isset($_POST['password2'])) echo $_POST['password2']; ?>"><br>
        <input type="submit" value="Reset Password">
    </form>
</body>

</html>
