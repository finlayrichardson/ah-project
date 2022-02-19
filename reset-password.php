<?php
require('./utils/auth.php');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_REQUEST['token'])) {
        $token = trim($_REQUEST['token']);
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
                $errors['password1'] = "Please enter a password";
            } elseif (empty($_POST['password2'])) {
                $errors['password2'] = "Please confirm password";
            } elseif ($_POST['password1'] !== $_POST['password2']) {
                $errors['password2'] = "Passwords must match";
            } else {
                $password = password_hash(trim($_POST['password1']), PASSWORD_BCRYPT);
            }

            // Change password or display errors
            if (empty($errors)) {
                // Change password
                mysqli_query($db, "UPDATE user SET password = '$password' WHERE user_id = $user_id;");
                echo "<p>Password successfully changed!</p><br>";
                echo "<a href='/login'>Login</a>";
                exit();
            }
        }
    } else {
        load('forgot-password');
    }
}
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>Reset Password</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
    </head>
    <body>
        <h1>Reset Password</h1>
        <p>Please enter your new password below to change it.</p>
        <form method="POST" novalidate>
            <input type="password" name="password1" required placeholder="Password" value="<?php if (isset($_POST['password1'])) echo $_POST['password1']; ?>">
            <?php
                if (isset($errors['password1'])) {
                    $error = $errors['password1'];
                    echo "<p class='error'>$error</p>";
                }
                ?>
            <input type="password" name="password2" required placeholder="Confirm Password" value="<?php if (isset($_POST['password2'])) echo $_POST['password2']; ?>">
            <?php
                if (isset($errors['password2'])) {
                    $error = $errors['password2'];
                    echo "<p class='error'>$error</p>";
                }
                ?>
            <input type="submit" value="Reset Password">
        </form>
        <script>
            function validate() {
                // Remove existing errors
                while (document.getElementsByClassName('error')[0]) {
                    document.getElementsByClassName('error')[0].remove();
                }

                let valid = true;
                const password1 = document.getElementsByTagName("input")['password1'];
                const password2 = document.getElementsByTagName("input")['password2'];

                // Validate password
                if (password1.validity.valueMissing) {
                    password1.insertAdjacentHTML('afterend', '<p class="error">⚠ Please enter a password</p>');
                    valid = false;
                } else if (password2.validity.valueMissing) {
                    password2.insertAdjacentHTML('afterend', '<p class="error">⚠ Please confirm password</p>');
                    valid = false;
                } else if (password1.value !== password2.value) {
                    password2.insertAdjacentHTML('afterend', '<p class="error">⚠ Passwords must match</p>');
                    valid = false;
                }
                return valid;
            }

            document.getElementsByTagName('form')[0].onsubmit = validate;
        </script>
    </body>
</html>
