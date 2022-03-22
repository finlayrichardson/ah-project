<?php
session_name('id');
session_start();
if (isset($_SESSION['user_id'])) {
    // Logout user
    session_destroy();
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    require('./utils/connect-db.php');
    require('./utils/tools.php');
    $errors = array();

    // Check an email has been entered
    if (empty($_POST['email'])) {
        $errors['email'] = "⚠ Please enter an email";
    } else {
        $email = mysqli_real_escape_string($db, trim($_POST['email']));
    }

    // Check a password has been entered
    if (empty($_POST['password'])) {
        $errors['password'] = "⚠ Please enter a password";
    } else {
        $password = trim($_POST['password']);
    }

    if (empty($errors)) {
        $result = query("SELECT * FROM user WHERE email = ?;", 's', $email);
        $user = mysqli_fetch_assoc($result);
        // Check if user is in database
        if (mysqli_num_rows($result) == 0) {
            $errors['email'] = "⚠ User does not exist";
        }
    }

    if (empty($errors)) {
        // Check if password is correct
        if (!password_verify($password, $user['password'])) {
            // Password is incorrect
            $errors['password'] = "⚠ Incorrect password";
        } else {
            // Password is correct
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['verified'] = $user['verified'];

            // Check if user needs to be returned to a page
            if (isset($_GET['return'])) {
                load($_GET['return']);
            }
            // Send user to index
            load('index');
        }
    }
}
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <title>Login</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
    </head>
    <body>
        <div class='centre-box'>
            <h1>Login</h1>
            <hr>
            <form method="POST" novalidate>
                <input type="text" autocorrect="off" autocapitalize="none" name="email" required autofocus placeholder="Email" value="<?php if (isset($_POST['email'])) echo $_POST['email'];?>">
                <?php
                if (isset($errors['email'])) {
                    $error = $errors['email'];
                    echo "<p class='error'>$error</p>";
                }
                ?>
                <input type="password" name="password" required placeholder="Password" value="<?php if (isset($_POST['password'])) echo $_POST['password'];?>"><br>
                <?php
                if (isset($errors['password'])) {
                    $error = $errors['password'];
                    echo "<p class='error'>$error</p>";
                }
                ?>
                <a href="forgot-password<?php if (isset($_POST['email'])) echo '?email=' . $_POST['email'];?>">Forgotten your password?</a><br>
                <input type="submit" value="Login">
            </form>
            <hr>
            <span>Don't have an account? <a href='/register' class='button'>Register</a></span>
        </div>
        <script>
            function validate() {
                // Remove existing errors
                while (document.getElementsByClassName('error')[0]) {
                    document.getElementsByClassName('error')[0].remove();
                }

                let valid = true;
                const email = document.getElementsByTagName("input")['email'];
                const password = document.getElementsByTagName("input")['password'];

                // Validate email
                if (email.validity.valueMissing) {
                    email.insertAdjacentHTML('afterend', '<p class="error">⚠ Please enter an email</p>');
                    valid = false;
                }

                // Validate password
                if (password.validity.valueMissing) {
                    password.insertAdjacentHTML('afterend', '<p class="error">⚠ Please enter a password</p>');
                    valid = false;
                }
                return valid;
            }

            document.getElementsByTagName('form')[0].onsubmit = validate;
        </script>
    </body>
</html>
