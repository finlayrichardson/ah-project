<?php
session_name("id");
session_start();
if (isset($_SESSION['user_id'])) {
    // Logout user
    session_destroy();
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    require('./connect-db.php');
    require('./tools.php');
    $errors = array();

    // Check an email has been entered
    if (empty($_POST['email'])) {
        $errors[] = "Please enter an email";
    } else {
        $email = mysqli_real_escape_string($db, trim($_POST['email']));
    }

    // Check a password has been entered
    if (empty($_POST['password'])) {
        $errors[] = "Please enter a password";
    } else {
        $password = trim($_POST['password']);
    }

    if (empty($errors)) {
        $result = query("SELECT * FROM user WHERE email = ?;", 's', $email);
        $user = mysqli_fetch_assoc($result);
        // Check if user is in database
        if (mysqli_num_rows($result) == 0) {
            $errors[] = "User does not exist";
        }
    }

    if (empty($errors)) {
        // Check if password is correct
        if (!password_verify($password, $user['password'])) {
            // Password is incorrect
            $errors[] = "Incorrect password";
        }
    }

    if (empty($errors)) {
        // Password is correct
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['role'] = $user['role'];

        // Check if user needs to be returned to a page
        if (isset($_GET['return'])) {
            load($_GET['return']);
        }
        // Send user to index
        load('index');
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
<html lang='en'>
    <head>
        <title>Login</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/resources/style.css">
        <script type="text/javascript" src="https://livejs.com/live.js"></script>
    </head>
    <body>
        <div class="user-form">
            <h1>Login</h1>
            <hr>
            <form method="POST">
                <input type="text" name="email" required autofocus placeholder="Email" value="<?php if (isset($_POST['email'])) echo $_POST['email'];?>"><br>
                <input type="password" name="password" required placeholder="Password" value="<?php if (isset($_POST['password'])) echo $_POST['password'];?>"><br>
                <a href="forgot-password<?php if (isset($_POST['email'])) echo '?email=' . $_POST['email'];?>">Forgotten your password?</a><br>
                <input type="submit" value="Login">
            </form>
            <hr>
            <span>Don't have an account?<a href="register" class="button">Register</a></span>
        </div>
    </body>
</html>
