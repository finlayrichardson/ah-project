<?php
require('./auth.php');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $errors = array();
    // Validate First Name
    if (empty($_POST['first_name'])) {
        $errors[] = "Please enter a first name";
    } elseif (!preg_match("/[a-zA-ZäöüßÄÖÜ ]/", $_POST['first_name'])) {
        $errors[] = "First name must contain only letters";
    } elseif (strlen(trim($_POST['first_name'])) > 20) {
        $errors[] = "First name must be max 20 characters";
    } else {
        $first_name = mysqli_real_escape_string($db, trim($_POST['first_name']));
    }
    // Validate Last Name
    if (empty($_POST['last_name'])) {
        $errors[] = "Please enter a last name";
    } elseif (!preg_match('/[a-zA-ZäöüßÄÖÜ ]/', $_POST['last_name'])) {
        $errors[] = "Last name must contain only letters";
    } elseif (strlen(trim($_POST['last_name'])) > 20) {
        $errors[] = "Last name must be max 20 characters";
    } else {
        $last_name = mysqli_real_escape_string($db, trim($_POST['last_name']));
    }
    // Validate Email
    if (empty($_POST['email'])) {
        $errors[] = "Please enter an email";
    } elseif (!preg_match('/^.+?@esms\.org\.uk *?$/', $_POST['email'])) {
        $errors[] = "Please use your school email";
    } elseif (strlen(trim($_POST['email'])) > 30) {
        $errors[] = "Email must be max 30 characters";
    } else {
        $email = mysqli_real_escape_string($db, trim($_POST['email']));
    }
    // Validate password
    if (empty($_POST['password1'])) {
        $errors[] = "Please enter a password";
    } elseif (empty($_POST['password2'])) {
        $errors[] = "Please confirm password";
    } elseif ($_POST['password1'] !== $_POST['password2']) {
        $errors[] = "Passwords must match";
    } else {
        $password = password_hash(mysqli_real_escape_string($db, trim($_POST['password1'])), PASSWORD_BCRYPT);
    }
    // Check email isn't already registered
    if (empty($errors)) {
        $result = query("SELECT user_id FROM user WHERE email = ?;", 's', $email);
        if (mysqli_num_rows($result) != 0) {
            $errors[] = "Email already registered";
        }
    }
    // Check if errors should be displayed or user should be inserted
    if (empty($errors)) {
        // Insert user into database
        query("INSERT INTO user (email, password, first_name, last_name, role) VALUES (?, ?, ?, ?, 'student');", 'ssss', $email, $password, $first_name, $last_name);

        // Setup session and go to verify-email.php
        session_name("id");
        session_start();
        $result = query("SELECT user_id FROM user WHERE email = ?;", 's', $email);
        $user_id = mysqli_fetch_row($result)[0];
        $_SESSION['user_id'] = $user_id;
        $_SESSION['email'] = $email;
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;

        load('verify-email.php');
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
    <title>Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Register</h1>
    <form method="POST">
        <input type="text" name="first_name" required autofocus pattern="[a-zA-ZäöüßÄÖÜ ]+" maxlength="20" placeholder="First Name" value="<?php if (isset($_POST['first_name'])) echo $_POST['first_name'];?>"><br>
        <input type="text" name="last_name" required pattern="[a-zA-ZäöüßÄÖÜ ]+" maxlength="20" placeholder="Last Name" value="<?php if (isset($_POST['last_name'])) echo $_POST['last_name'];?>"><br>
        <input type="text" name="email" required pattern="^.+?@esms\.org\.uk *?$" maxlength="30" placeholder="Email" value="<?php if (isset($_POST['email'])) echo $_POST['email'];?>"><br>
        <input type="password" name="password1" required placeholder="Password" value="<?php if (isset($_POST['password1'])) echo $_POST['password1'];?>"><br>
        <input type="password" name="password2" required placeholder="Confirm Password" value="<?php if (isset($_POST['password2'])) echo $_POST['password2'];?>"><br>
        <input type="submit" value="Register">
    </form>
    <a href="login.php" class="button">Login</a>
</body>
</html>
